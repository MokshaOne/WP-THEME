<?php
/**
 * Raveenthiran Headless — content model + normalized REST contract.
 *
 * This theme renders no front end (see index.php). It registers the Work CPT,
 * the Album (work_category) and Service (work_service) taxonomies, and exposes
 * `project`, `gallery` and `seo` REST fields that read ACF first and fall back
 * to legacy meta / attached media. The ACF field group auto-loads from the
 * theme's acf-json/ folder.
 *
 * @package RaveenthiranHeadless
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Post type + taxonomies ─────────────────────────────────────────────── */
add_action( 'init', function () {

	if ( ! post_type_exists( 'work' ) ) {
		register_post_type( 'work', array(
			'labels' => array(
				'name'          => __( 'Work', 'rvn' ),
				'singular_name' => __( 'Project', 'rvn' ),
				'add_new_item'  => __( 'Add New Project', 'rvn' ),
				'edit_item'     => __( 'Edit Project', 'rvn' ),
				'all_items'     => __( 'All Work', 'rvn' ),
				'menu_name'     => __( 'Work', 'rvn' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-camera-alt',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		) );
	}

	if ( ! taxonomy_exists( 'work_category' ) ) {
		register_taxonomy( 'work_category', 'work', array(
			'labels'            => array( 'name' => __( 'Albums', 'rvn' ), 'singular_name' => __( 'Album', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'album', 'with_front' => false ),
		) );
	}

	if ( ! taxonomy_exists( 'work_service' ) ) {
		register_taxonomy( 'work_service', 'work', array(
			'labels'            => array( 'name' => __( 'Services', 'rvn' ), 'singular_name' => __( 'Service', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service', 'with_front' => false ),
		) );
	}

	if ( ! taxonomy_exists( 'work_series' ) ) {
		register_taxonomy( 'work_series', 'work', array(
			'labels'            => array( 'name' => __( 'Series', 'rvn' ), 'singular_name' => __( 'Series', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'series', 'with_front' => false ),
		) );
	}
} );

/* Flush rewrite rules once when the theme is activated (so /work resolves). */
add_action( 'after_switch_theme', function () { flush_rewrite_rules(); } );

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Read an ACF field if ACF is active, else the legacy `_still_<key>` meta. */
function rvn_field( $post_id, $acf_key, $legacy_key = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $acf_key, $post_id );
		if ( $v !== null && $v !== '' && $v !== false ) { return $v; }
	}
	if ( $legacy_key ) {
		$v = get_post_meta( $post_id, $legacy_key, true );
		if ( $v !== '' ) { return $v; }
	}
	return '';
}

/** Normalize credits into [{role,name,url}]. Accepts an ACF repeater (array of
 *  rows) OR a textarea with one "Role — Name" per line (ACF-free friendly). */
function rvn_credits( $post_id ) {
	$raw = function_exists( 'get_field' ) ? get_field( 'credits', $post_id ) : '';
	$out = array();
	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			$role = isset( $row['role'] ) ? trim( (string) $row['role'] ) : '';
			$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
			$url  = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';
			if ( $role || $name ) { $out[] = array( 'role' => $role, 'name' => $name, 'url' => $url ); }
		}
	} elseif ( is_string( $raw ) && $raw !== '' ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( $line === '' ) { continue; }
			$parts = preg_split( '/\s*(?:—|–|-|:)\s*/u', $line, 2 );
			$out[] = array( 'role' => trim( $parts[0] ), 'name' => isset( $parts[1] ) ? trim( $parts[1] ) : '', 'url' => '' );
		}
	}
	return $out;
}

/** Build a responsive image record from an attachment id. */
function rvn_image( $id, $size = 'large' ) {
	$src = wp_get_attachment_image_src( $id, $size );
	if ( ! $src ) { return null; }
	return array(
		'src'    => $src[0],
		'w'      => (int) $src[1],
		'h'      => (int) $src[2],
		'srcset' => (string) wp_get_attachment_image_srcset( $id, $size ),
		'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
	);
}

/** Series terms for a project as [{name,slug}]. */
function rvn_series_terms( $post_id ) {
	$out = array();
	$terms = wp_get_post_terms( $post_id, 'work_series' );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) { $out[] = array( 'name' => $t->name, 'slug' => $t->slug ); }
	}
	return $out;
}

/* ── REST contract: project / gallery / seo ─────────────────────────────── */
add_action( 'rest_api_init', function () {

	register_rest_field( 'work', 'project', array(
		'get_callback' => function ( $post ) {
			$id = $post['id'];
			$services = array();
			$terms = wp_get_post_terms( $id, 'work_service', array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $terms ) ) { $services = array_values( $terms ); }
			return array(
				'client'        => (string) rvn_field( $id, 'client',   '_still_client' ),
				'year'          => (string) rvn_field( $id, 'year',     '_still_year' ),
				'location'      => (string) rvn_field( $id, 'location', '_still_location' ),
				'website'       => (string) rvn_field( $id, 'website',  '_still_website' ),
				'services'      => $services,
				'series'        => rvn_series_terms( $id ),
				'credits'       => rvn_credits( $id ),
				'featured_home' => (bool) ( function_exists( 'get_field' ) ? get_field( 'featured_home', $id ) : false ),
			);
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'gallery', array(
		'get_callback' => function ( $post ) {
			$out = array();

			// 1) ACF Gallery field (curated + ordered). return_format = id.
			if ( function_exists( 'get_field' ) ) {
				$g = get_field( 'gallery', $post['id'] );
				if ( is_array( $g ) ) {
					foreach ( $g as $item ) {
						$aid = is_array( $item ) ? (int) ( $item['ID'] ?? $item['id'] ?? 0 ) : (int) $item;
						$rec = $aid ? rvn_image( $aid, 'large' ) : null;
						if ( $rec ) { $out[] = $rec; }
					}
				}
			}

			// 2) Fallback: images attached to the post (excluding the featured one).
			if ( empty( $out ) ) {
				$featured = (int) get_post_thumbnail_id( $post['id'] );
				foreach ( get_attached_media( 'image', $post['id'] ) as $img ) {
					if ( (int) $img->ID === $featured ) { continue; }
					$rec = rvn_image( $img->ID, 'large' );
					if ( $rec ) { $out[] = $rec; }
				}
			}

			return $out;
		},
		'schema' => array( 'type' => 'array', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'seo', array(
		'get_callback' => function ( $post ) {
			$desc = (string) rvn_field( $post['id'], 'seo_description' );
			$ogid = function_exists( 'get_field' ) ? get_field( 'og_image', $post['id'] ) : 0;
			if ( is_array( $ogid ) && isset( $ogid['id'] ) ) { $ogid = $ogid['id']; }
			$og = '';
			if ( $ogid ) { $rec = rvn_image( (int) $ogid, 'large' ); $og = $rec ? $rec['src'] : ''; }
			return array( 'description' => $desc, 'og_image' => $og );
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );
} );

/* ── Tidy admin: no theme customizer/front-end noise for a headless install ── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
} );

/* ══════════════════════════════════════════════════════════════════════
   SITE CONTROL — a single branded admin hub. One place to run the whole
   site instead of hunting through the default WordPress dashboard. The ACF
   "Site settings" become a sub-page of it, and Enquiries nest under it too.
   ══════════════════════════════════════════════════════════════════════ */

/* Top-level "Site Control" menu with a branded overview page. */
add_action( 'admin_menu', function () {
	add_menu_page(
		'Site Control',
		'Site Control',
		'edit_posts',
		'site-control',
		'rvn_site_control_page',
		'dashicons-screenoptions',
		2
	);
}, 9 );

/* Site settings → a sub-page of Site Control (edit Home layout, Studio, Pricing,
   FAQ, Mail, Security, Booking) + the REST endpoint the Astro build reads. */
add_action( 'acf/init', function () {
	if ( function_exists( 'acf_add_options_sub_page' ) ) {
		acf_add_options_sub_page( array(
			'page_title'  => 'Site settings',
			'menu_title'  => 'Site settings',
			'menu_slug'   => 'rvn-site',
			'parent_slug' => 'site-control',
			'capability'  => 'edit_posts',
			'redirect'    => false,
		) );
	} elseif ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array(
			'page_title' => 'Site settings', 'menu_title' => 'Site settings',
			'menu_slug' => 'rvn-site', 'capability' => 'edit_posts', 'redirect' => false,
		) );
	}
} );

/* Branded styles for the Site Control page only. */
add_action( 'admin_head', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || strpos( (string) $screen->id, 'site-control' ) === false ) { return; }
	echo '<style>
	.rvn-sc { max-width: 1100px; }
	.rvn-sc__hero { background:#121110; color:#efece6; padding:34px 34px 30px; margin:20px 20px 0 0; border:1px solid #2b2925; }
	.rvn-sc__wm { font-family:"Arial Narrow",Arial,sans-serif; font-weight:800; letter-spacing:.22em; font-size:15px; }
	.rvn-sc__hero h1 { color:#efece6; font-size:44px; line-height:1; margin:14px 0 6px; font-weight:800; letter-spacing:-.5px; }
	.rvn-sc__hero p { color:#8a847a; margin:0; letter-spacing:.02em; }
	.rvn-sc__stats { display:flex; gap:34px; margin-top:24px; flex-wrap:wrap; }
	.rvn-sc__stat b { display:block; font-size:30px; line-height:1; color:#efece6; font-weight:800; }
	.rvn-sc__stat span { font-size:11px; letter-spacing:.14em; color:#6f6a60; text-transform:uppercase; }
	.rvn-sc__grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:16px; margin:16px 20px 0 0; }
	.rvn-sc__card { display:block; background:#fff; border:1px solid #dcd7cf; border-left:3px solid #121110; padding:18px 20px; text-decoration:none; transition:box-shadow .15s, transform .15s; }
	.rvn-sc__card:hover { box-shadow:0 6px 22px rgba(0,0,0,.08); transform:translateY(-1px); }
	.rvn-sc__card h3 { margin:0 0 6px; font-size:15px; color:#121110; }
	.rvn-sc__card p { margin:0; color:#787268; font-size:12.5px; line-height:1.5; }
	.rvn-sc__card .dashicons { color:#8a847a; }
	.rvn-sc__panel { background:#fff; border:1px solid #dcd7cf; padding:20px 24px; margin:16px 20px 0 0; }
	.rvn-sc__panel h2 { margin-top:0; }
	.rvn-sc__check { list-style:none; margin:0; padding:0; }
	.rvn-sc__check li { padding:7px 0; border-bottom:1px solid #eee; font-size:13.5px; }
	.rvn-sc__check li:last-child { border-bottom:none; }
	.rvn-sc__ok { color:#2b7a2b; font-weight:700; } .rvn-sc__todo { color:#b26a00; font-weight:700; }
	.rvn-sc__foot { color:#8a847a; font-size:12px; margin:18px 20px 30px 0; }
	</style>';
} );

function rvn_site_control_page() {
	$theme    = wp_get_theme();
	$ver      = $theme->get( 'Version' );
	$work     = wp_count_posts( 'work' );
	$work_n   = isset( $work->publish ) ? (int) $work->publish : 0;
	$enq      = wp_count_posts( 'rvn_enquiry' );
	$enq_n    = ( isset( $enq->private ) ? (int) $enq->private : 0 ) + ( isset( $enq->publish ) ? (int) $enq->publish : 0 );
	$posts_n  = (int) wp_count_posts( 'post' )->publish;
	$acf      = function_exists( 'get_field' );
	$variant  = $acf ? (string) rvn_site_opt( 'home_variant', 'raveenthiran' ) : 'raveenthiran';
	$email    = (string) rvn_site_opt( 'contact_email', '' );
	$smtp     = (int) rvn_site_opt( 'smtp_enable', 0 ) === 1;
	$settings = admin_url( 'admin.php?page=rvn-site' );

	$card = function ( $href, $icon, $title, $desc ) {
		printf(
			'<a class="rvn-sc__card" href="%s"><h3><span class="dashicons %s"></span> %s</h3><p>%s</p></a>',
			esc_url( $href ), esc_attr( $icon ), esc_html( $title ), esc_html( $desc )
		);
	};
	$row = function ( $ok, $text ) {
		printf( '<li><span class="%s">%s</span> %s</li>', $ok ? 'rvn-sc__ok' : 'rvn-sc__todo', $ok ? '✓' : '›', wp_kses_post( $text ) );
	};
	?>
	<div class="wrap rvn-sc">
		<div class="rvn-sc__hero">
			<div class="rvn-sc__wm">RAVEENTHIRAN</div>
			<h1>Site Control</h1>
			<p>Everything that runs raveenthiran.com — in one place.</p>
			<div class="rvn-sc__stats">
				<div class="rvn-sc__stat"><b><?php echo esc_html( $work_n ); ?></b><span>Projects</span></div>
				<div class="rvn-sc__stat"><b><?php echo esc_html( $enq_n ); ?></b><span>Enquiries</span></div>
				<div class="rvn-sc__stat"><b><?php echo esc_html( $posts_n ); ?></b><span>Journal</span></div>
				<div class="rvn-sc__stat"><b>v<?php echo esc_html( $ver ); ?></b><span>Theme</span></div>
				<div class="rvn-sc__stat"><b><?php echo esc_html( ucfirst( $variant ) ); ?></b><span>Home layout</span></div>
			</div>
		</div>

		<div class="rvn-sc__grid">
			<?php
			$card( $settings . '#acf-field_rvn_home_variant', 'dashicons-layout', 'Homepage', 'Pick the start-page layout, hero text, marquee, awards & testimonials.' );
			$card( admin_url( 'edit.php?post_type=work' ), 'dashicons-camera-alt', 'Projects', 'Add & edit albums — cover, gallery, credits, album, series, featured.' );
			$card( admin_url( 'edit.php?post_type=rvn_enquiry' ), 'dashicons-email-alt', 'Enquiries', 'Every enquiry received from the website, newest first.' );
			$card( admin_url( 'edit.php' ), 'dashicons-edit-page', 'Journal', 'Write journal entries (native WordPress posts).' );
			$card( $settings, 'dashicons-store', 'Studio & Pricing', 'Studio statement, spec, and the Enquire calculator sessions.' );
			$card( $settings, 'dashicons-email', 'Mail (SMTP)', 'Delivery for enquiry emails so they actually arrive.' );
			$card( $settings, 'dashicons-shield', 'Security & Booking', 'Turnstile spam shield and the availability / booking link.' );
			$card( admin_url( 'upload.php' ), 'dashicons-format-image', 'Media', 'Your image library — upload AVIF, manage files.' );
			?>
		</div>

		<div class="rvn-sc__panel">
			<h2>Publishing — how changes go live</h2>
			<p>The website is a fast static build that pulls this content automatically. After you edit here, the site rebuilds on its schedule (nightly) or on the next push. To see a change immediately, trigger the deploy in GitHub → Actions → “Deploy frontend”, then hard-refresh (Ctrl/Cmd+Shift+R). The live footer shows the running version.</p>
			<ul class="rvn-sc__check">
				<?php
				$row( $acf, $acf ? 'ACF is active — all settings available.' : 'ACF Pro is not active — install/activate it to edit settings.' );
				$row( $work_n > 0, $work_n > 0 ? esc_html( $work_n ) . ' project(s) published.' : 'No projects yet — add your first album under <b>Projects</b>.' );
				$row( $email !== '', $email !== '' ? 'Contact email set: <b>' . esc_html( $email ) . '</b>.' : 'Set your contact email in <b>Site settings → Contact</b>.' );
				$row( $smtp, $smtp ? 'SMTP delivery is on.' : 'Configure <b>Mail (SMTP)</b> so enquiry emails reliably arrive.' );
				?>
			</ul>
		</div>

		<p class="rvn-sc__foot">Raveenthiran Headless · theme v<?php echo esc_html( $ver ); ?> · after activating a new theme version, ACF may prompt <b>Sync</b>, then save <b>Settings → Permalinks</b> once.</p>
	</div>
	<?php
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'rvn/v1', '/site', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'rvn_site_payload',
	) );
} );

/** Read an ACF options value with a fallback. */
function rvn_site_opt( $key, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) { return $default; }
	$v = get_field( $key, 'option' );
	return ( $v === null || $v === false || $v === '' ) ? $default : $v;
}

/** Normalized site payload for the headless frontend. */
function rvn_site_payload() {
	$rows_kv = function ( $key, $a = 'label', $b = 'value' ) {
		$out = array();
		foreach ( (array) rvn_site_opt( $key, array() ) as $r ) {
			$la = isset( $r[ $a ] ) ? trim( (string) $r[ $a ] ) : '';
			$lb = isset( $r[ $b ] ) ? $r[ $b ] : '';
			if ( $la !== '' ) { $out[] = array( $a => $la, $b => $lb ); }
		}
		return $out;
	};
	$lines = function ( $key ) {
		$out = array();
		$raw = rvn_site_opt( $key, '' );
		if ( is_string( $raw ) && $raw !== '' ) {
			foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $l ) { $l = trim( $l ); if ( $l !== '' ) { $out[] = $l; } }
		}
		return $out;
	};

	// Generic repeater reader → array of associative rows (only rows with a non-empty first key).
	$rows = function ( $key, $keys ) {
		$out = array();
		foreach ( (array) rvn_site_opt( $key, array() ) as $r ) {
			$first = isset( $r[ $keys[0] ] ) ? trim( (string) $r[ $keys[0] ] ) : '';
			if ( $first === '' ) { continue; }
			$row = array();
			foreach ( $keys as $k ) { $row[ $k ] = isset( $r[ $k ] ) ? $r[ $k ] : ''; }
			$out[] = $row;
		}
		return $out;
	};

	$types = array();
	foreach ( $rows_kv( 'project_types', 'label', 'base' ) as $r ) { $types[] = array( 'label' => $r['label'], 'base' => (float) $r['base'] ); }
	$addons = array();
	foreach ( $rows_kv( 'addons', 'label', 'price' ) as $r ) { $addons[] = array( 'label' => $r['label'], 'price' => (float) $r['price'] ); }

	// Redesign calculator sessions: [{name, base, hrs, extra, note}].
	$sessions = array();
	foreach ( $rows( 'price_sessions', array( 'name', 'base', 'hrs', 'extra', 'note' ) ) as $r ) {
		$sessions[] = array(
			'name'  => (string) $r['name'],
			'base'  => (float) $r['base'],
			'hrs'   => (float) $r['hrs'],
			'extra' => (float) $r['extra'],
			'note'  => (string) $r['note'],
		);
	}

	// Studio spec [{label, value}] + statement lines.
	$spec = array();
	foreach ( $rows_kv( 'studio_spec', 'label', 'value' ) as $r ) { $spec[] = array( 'label' => (string) $r['label'], 'value' => (string) $r['value'] ); }

	// Home recognition + testimonials.
	$honors = array();
	foreach ( $rows( 'home_honors', array( 'year', 'title', 'tag' ) ) as $r ) { $honors[] = array( 'year' => (string) $r['year'], 'title' => (string) $r['title'], 'tag' => (string) $r['tag'] ); }
	$testimonials = array();
	foreach ( $rows( 'home_testimonials', array( 'text', 'author' ) ) as $r ) { $testimonials[] = array( 'text' => (string) $r['text'], 'by' => (string) $r['author'] ); }
	$stats = array();
	foreach ( $rows_kv( 'studio_stats', 'label', 'value' ) as $r ) { $stats[] = array( 'label' => $r['label'], 'value' => (string) $r['value'] ); }
	$faq = array();
	foreach ( (array) rvn_site_opt( 'faq', array() ) as $r ) {
		$q = isset( $r['question'] ) ? trim( (string) $r['question'] ) : '';
		if ( $q === '' ) { continue; }
		$faq[] = array( 'q' => $q, 'a' => isset( $r['answer'] ) ? (string) $r['answer'] : '' );
	}
	$portrait = rvn_site_opt( 'studio_portrait', '' );
	if ( is_array( $portrait ) ) { $portrait = $portrait['url'] ?? ''; }

	return array(
		'studio'  => array(
			'lede'      => (string) rvn_site_opt( 'studio_lede', '' ),
			'bio'       => (string) rvn_site_opt( 'studio_bio', '' ),
			'portrait'  => (string) $portrait,
			'statement' => $lines( 'studio_statement' ),
			'spec'      => $spec,
			'stats'     => $stats,
			'clients'   => $lines( 'studio_clients' ),
		),
		'home'    => array(
			'variant'      => (string) rvn_site_opt( 'home_variant', 'raveenthiran' ),
			'hero_lines'   => $lines( 'home_hero_lines' ),
			'hero_lede'    => (string) rvn_site_opt( 'home_hero_lede', '' ),
			'marquee'      => (string) rvn_site_opt( 'home_marquee', '' ),
			'honors'       => $honors,
			'testimonials' => $testimonials,
		),
		'contact' => array(
			'email'     => (string) rvn_site_opt( 'contact_email', '' ),
			'location'  => (string) rvn_site_opt( 'contact_location', '' ),
			'response'  => (string) rvn_site_opt( 'contact_response', '' ),
			'instagram' => (string) rvn_site_opt( 'contact_instagram', '' ),
			'behance'   => (string) rvn_site_opt( 'contact_behance', '' ),
			'linkedin'  => (string) rvn_site_opt( 'contact_linkedin', '' ),
		),
		'pricing' => array(
			'currency' => (string) rvn_site_opt( 'currency', '€' ),
			'types'    => $types,
			'addons'   => $addons,
			'sessions' => $sessions,
			'licence'  => (float) rvn_site_opt( 'licence_price', 0 ),
			'per_km'   => (float) rvn_site_opt( 'per_km', 0 ),
		),
		'faq'      => $faq,
		'security' => array(
			'turnstile_site' => (string) rvn_site_opt( 'turnstile_site', '' ),
		),
	);
}

/**
 * Cloudflare Turnstile verification (adapted from Obscura). Returns true when
 * Turnstile is not configured (form keeps working) or the token verifies.
 */
function rvn_turnstile_secret() {
	if ( defined( 'RVN_TURNSTILE_SECRET' ) && RVN_TURNSTILE_SECRET ) { return RVN_TURNSTILE_SECRET; }
	return trim( (string) rvn_site_opt( 'turnstile_secret', '' ) );
}
function rvn_turnstile_passes( $token ) {
	$secret = rvn_turnstile_secret();
	if ( $secret === '' ) { return true; } // not configured → don't block
	$token = trim( (string) $token );
	if ( $token === '' ) { return false; }
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$res = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 8,
		'body'    => array( 'secret' => $secret, 'response' => $token, 'remoteip' => $ip ),
	) );
	if ( is_wp_error( $res ) ) { return true; } // fail open on network error
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	return ! empty( $data['success'] );
}

/* ══════════════════════════════════════════════════════════════════════
   Enquiry endpoint — receives the frontend enquiry form, stores it as a
   private CPT, and emails the studio + an auto-reply to the client.
   ══════════════════════════════════════════════════════════════════════ */

add_action( 'init', function () {
	register_post_type( 'rvn_enquiry', array(
		'labels'       => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry' ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'site-control',
		'menu_icon'    => 'dashicons-email-alt',
		'menu_position'=> 26,
		'supports'     => array( 'title', 'editor' ),
		'capability_type' => 'post',
	) );
} );

/* CORS for the rvn/v1 namespace so the static frontend (another origin) can POST. */
add_action( 'rest_api_init', function () {
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter( 'rest_pre_serve_request', function ( $value ) {
		$origin  = get_http_origin();
		$allowed = array(
			'https://raveenthiran.com',
			'https://www.raveenthiran.com',
			'http://localhost:4321',
			'http://localhost:4322',
		);
		if ( $origin && in_array( $origin, $allowed, true ) ) {
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Content-Type' );
			header( 'Vary: Origin' );
		}
		return $value;
	} );
}, 15 );

add_action( 'rest_api_init', function () {
	register_rest_route( 'rvn/v1', '/enquiry', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'rvn_enquiry_submit',
	) );
} );

function rvn_enquiry_submit( WP_REST_Request $request ) {
	$p = $request->get_json_params();
	if ( ! is_array( $p ) ) { $p = $request->get_params(); }

	// Honeypot — bots fill hidden "company"; drop silently as success.
	if ( ! empty( $p['company'] ) ) { return new WP_REST_Response( array( 'ok' => true ), 200 ); }

	// Cloudflare Turnstile (only enforced when configured).
	if ( ! rvn_turnstile_passes( $p['cf-turnstile-response'] ?? '' ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Spam check failed — please try again.' ), 400 );
	}

	$name  = sanitize_text_field( $p['name'] ?? '' );
	$email = sanitize_email( $p['email'] ?? '' );
	if ( $name === '' || ! is_email( $email ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Please add your name and a valid email.' ), 400 );
	}

	$type     = sanitize_text_field( $p['project_type'] ?? '' );
	$addons   = sanitize_text_field( $p['addons'] ?? '' );
	$estimate = sanitize_text_field( $p['estimate'] ?? '' );
	$date     = sanitize_text_field( $p['preferred_date'] ?? '' );
	$notes    = sanitize_textarea_field( $p['notes'] ?? '' );

	$body  = "Name: {$name}\nEmail: {$email}\nProject type: {$type}\nAdd-ons: {$addons}\n";
	$body .= "Estimate: {$estimate}\nPreferred date: {$date}\n\n{$notes}";

	wp_insert_post( array(
		'post_type'    => 'rvn_enquiry',
		'post_status'  => 'private',
		'post_title'   => trim( $name . ' — ' . ( $type ?: 'Enquiry' ) . ( $estimate ? ' (' . $estimate . ')' : '' ) ),
		'post_content' => $body,
		'meta_input'   => array(
			'_rvn_type'     => $type,
			'_rvn_estimate' => $estimate,
			'_rvn_email'    => $email,
		),
	) );

	$studio  = rvn_site_opt( 'contact_email', get_option( 'admin_email' ) );
	wp_mail( $studio, 'New enquiry — ' . $name, $body, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	) );

	$reply  = "Hi {$name},\n\nThank you for your enquiry — I've received it and will reply within 24 hours ";
	$reply .= "with availability and a firm quote.\n\n";
	if ( $estimate ) { $reply .= "A non-binding estimate is attached as a PDF.\n\n"; }
	$reply .= "Here's what you sent:\n\n{$body}\n\n";
	$reply .= "— Nishuthan Raveenthiran\nraveenthiran.com";

	// Branded PDF estimate (attached only when there's a calculated figure).
	$attachments = array();
	$pdf_path = '';
	if ( $estimate && function_exists( 'rvn_quote_pdf_file' ) ) {
		$pdf_path = rvn_quote_pdf_file( array(
			'name'     => $name,
			'type'     => $type,
			'estimate' => $estimate,
			'ref'      => 'RVN-' . gmdate( 'ymd' ) . '-' . wp_rand( 100, 999 ),
		) );
		if ( $pdf_path ) { $attachments[] = $pdf_path; }
	}

	wp_mail( $email, 'Thanks for your enquiry — Raveenthiran', $reply, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $studio,
	), $attachments );

	if ( $pdf_path && file_exists( $pdf_path ) ) { @unlink( $pdf_path ); }

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/* ══════════════════════════════════════════════════════════════════════
   SMTP delivery — routes wp_mail() through an authenticated SMTP server so
   enquiries + auto-replies actually arrive. Adapted from the Obscura theme.
   Configured under Site settings → Mail (SMTP). The password may instead be
   defined as RVN_SMTP_PASS in wp-config.php (kept out of the database).
   ══════════════════════════════════════════════════════════════════════ */

function rvn_smtp_password() {
	if ( defined( 'RVN_SMTP_PASS' ) && RVN_SMTP_PASS ) { return RVN_SMTP_PASS; }
	return (string) rvn_site_opt( 'smtp_pass', '' );
}

add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return; }

	$host = trim( (string) rvn_site_opt( 'smtp_host', '' ) );
	$user = trim( (string) rvn_site_opt( 'smtp_user', '' ) );
	$pass = rvn_smtp_password();
	if ( $host === '' || $user === '' || $pass === '' ) { return; } // not configured → leave default mail()

	$phpmailer->isSMTP();
	$phpmailer->Host       = $host;
	$phpmailer->Port       = (int) ( rvn_site_opt( 'smtp_port', 587 ) ?: 587 );
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = $user;
	$phpmailer->Password   = $pass;
	$phpmailer->SMTPSecure = rvn_site_opt( 'smtp_secure', 'tls' ) === 'ssl' ? 'ssl' : 'tls';

	$from = trim( (string) rvn_site_opt( 'smtp_from', '' ) );
	if ( $from === '' || ! is_email( $from ) ) { $from = $user; }
	$name = (string) ( rvn_site_opt( 'smtp_fromname', '' ) ?: get_bloginfo( 'name' ) );
	try {
		$phpmailer->setFrom( $from, $name, false );
		$phpmailer->Sender = $from; // envelope-from for SPF alignment
	} catch ( \Exception $e ) {
		// keep whatever From WP already set
	}
}, 20 );

add_filter( 'wp_mail_from', function ( $email ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return $email; }
	$from = trim( (string) rvn_site_opt( 'smtp_from', '' ) );
	return ( $from && is_email( $from ) ) ? $from : $email;
}, 20 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( (int) rvn_site_opt( 'smtp_enable', 0 ) !== 1 ) { return $name; }
	$n = trim( (string) rvn_site_opt( 'smtp_fromname', '' ) );
	return $n ?: $name;
}, 20 );

/* ══════════════════════════════════════════════════════════════════════
   Quote → branded PDF estimate (adapted from Obscura). A tiny dependency-free
   single-page PDF writer using the standard Helvetica fonts. Attached to the
   enquiry auto-reply the visitor receives.
   ══════════════════════════════════════════════════════════════════════ */

function rvn_quote_pdf_file( $args ) {
	$a = wp_parse_args( $args, array( 'name' => '', 'type' => '', 'date' => '', 'estimate' => '', 'ref' => '' ) );

	$studio = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$mark   = 'Raveenthiran';
	$addr   = (string) rvn_site_opt( 'contact_location', '' );
	$email  = (string) rvn_site_opt( 'contact_email', get_option( 'admin_email' ) );
	$accent = rvn_hex_rgb01( '#c8a253' );

	$pdf   = new RVN_PDF();
	$ink   = array( 0.10, 0.10, 0.09 );
	$muted = array( 0.42, 0.42, 0.40 );

	$pdf->text( 72, 770, strtoupper( $mark ), 'B', 22, $ink );
	$pdf->text( 523, 772, 'ESTIMATE', 'B', 11, $muted, 'right' );
	$pdf->rect( 72, 752, 451, 2, $accent );

	$y = 712;
	$rows = array(
		array( 'PREPARED FOR', $a['name'] ?: '—' ),
		array( 'DATE',         $a['date'] ?: date_i18n( 'j M Y' ) ),
		array( 'PROJECT TYPE', $a['type'] ?: '—' ),
	);
	if ( $a['ref'] ) { $rows[] = array( 'REFERENCE', $a['ref'] ); }
	foreach ( $rows as $r ) {
		$pdf->text( 72, $y, $r[0], 'B', 9, $muted );
		$pdf->text( 200, $y, (string) $r[1], '', 12, $ink );
		$y -= 26;
	}

	$pdf->text( 72, $y - 36, 'ESTIMATED INVESTMENT', 'B', 10, $muted );
	$pdf->text( 72, $y - 86, $a['estimate'] ?: '—', 'B', 44, $ink );

	$note = 'This is a non-binding estimate based on the brief you submitted. The final quote is confirmed after a short call to align on scope, locations, usage rights, and delivery. Valid for 30 days.';
	$ny = $y - 140;
	foreach ( rvn_pdf_wrap( $note, 92 ) as $line ) { $pdf->text( 72, $ny, $line, '', 11, $muted ); $ny -= 17; }

	$pdf->rect( 72, 96, 451, 1, array( 0.85, 0.84, 0.80 ) );
	$pdf->text( 72, 78, $studio, 'B', 9, $ink );
	if ( $addr )  { $pdf->text( 72, 64, $addr, '', 9, $muted ); }
	if ( $email ) { $pdf->text( 72, 50, $email, '', 9, $muted ); }

	$bytes = $pdf->output();
	if ( ! $bytes ) { return ''; }
	$dir  = trailingslashit( get_temp_dir() );
	$name = wp_unique_filename( $dir, 'Estimate-Raveenthiran.pdf' );
	$path = $dir . $name;
	if ( @file_put_contents( $path, $bytes ) === false ) { return ''; }
	return $path;
}

function rvn_pdf_wrap( $text, $max ) {
	$out = array(); $cur = '';
	foreach ( preg_split( '/\s+/', trim( $text ) ) as $w ) {
		$try = $cur === '' ? $w : $cur . ' ' . $w;
		if ( strlen( $try ) > $max && $cur !== '' ) { $out[] = $cur; $cur = $w; } else { $cur = $try; }
	}
	if ( $cur !== '' ) { $out[] = $cur; }
	return $out;
}

function rvn_hex_rgb01( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( strlen( $hex ) === 3 ) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) { $hex = 'c8a253'; }
	return array( hexdec( substr( $hex, 0, 2 ) ) / 255, hexdec( substr( $hex, 2, 2 ) ) / 255, hexdec( substr( $hex, 4, 2 ) ) / 255 );
}

class RVN_PDF {
	private $ops = array();
	private function esc( $s ) {
		$s = wp_specialchars_decode( (string) $s, ENT_QUOTES );
		if ( function_exists( 'mb_convert_encoding' ) ) { $s = @mb_convert_encoding( $s, 'Windows-1252', 'UTF-8' ); }
		elseif ( function_exists( 'iconv' ) ) { $s = @iconv( 'UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s ); }
		else { $s = remove_accents( $s ); }
		return str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), (string) $s );
	}
	public function text( $x, $y, $str, $weight = '', $size = 12, $rgb = array( 0, 0, 0 ), $align = 'left' ) {
		$font = $weight === 'B' ? '/F2' : '/F1';
		if ( $align === 'right' ) { $x -= $this->width( $str, $size, $weight ); }
		$this->ops[] = sprintf( '%.3f %.3f %.3f rg', $rgb[0], $rgb[1], $rgb[2] );
		$this->ops[] = sprintf( 'BT %s %d Tf %.2f %.2f Td (%s) Tj ET', $font, $size, $x, $y, $this->esc( $str ) );
	}
	public function rect( $x, $y, $w, $h, $rgb ) {
		$this->ops[] = sprintf( '%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f', $rgb[0], $rgb[1], $rgb[2], $x, $y, $w, $h );
	}
	private function width( $str, $size, $weight ) {
		return strlen( remove_accents( (string) $str ) ) * $size * ( $weight === 'B' ? 0.56 : 0.52 );
	}
	public function output() {
		$content = implode( "\n", $this->ops );
		$objs = array();
		$objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
		$objs[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
		$objs[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>';
		$objs[4] = "<< /Length " . strlen( $content ) . " >>\nstream\n" . $content . "\nendstream";
		$objs[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
		$objs[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
		$pdf = "%PDF-1.4\n"; $offsets = array();
		foreach ( $objs as $n => $body ) { $offsets[ $n ] = strlen( $pdf ); $pdf .= $n . " 0 obj\n" . $body . "\nendobj\n"; }
		$xref_pos = strlen( $pdf ); $count = count( $objs ) + 1;
		$pdf .= "xref\n0 " . $count . "\n0000000000 65535 f \n";
		for ( $i = 1; $i < $count; $i++ ) { $pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] ); }
		$pdf .= "trailer\n<< /Size " . $count . " /Root 1 0 R >>\nstartxref\n" . $xref_pos . "\n%%EOF";
		return $pdf;
	}
}

/* ══════════════════════════════════════════════════════════════════════
   Enquiry insights — a small dashboard widget over the stored enquiries.
   No third-party analytics: answers "how many enquiries, of what kind".
   ══════════════════════════════════════════════════════════════════════ */

add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_posts' ) ) { return; }
	wp_add_dashboard_widget( 'rvn_insights', 'Enquiry insights', 'rvn_insights_widget' );
} );

function rvn_insights_widget() {
	$ids = get_posts( array(
		'post_type'      => 'rvn_enquiry',
		'post_status'    => array( 'private', 'publish' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	$total = count( $ids );
	$since = strtotime( '-30 days' );
	$recent = 0; $types = array(); $sum = 0.0; $cur = '';
	foreach ( $ids as $id ) {
		if ( get_post_time( 'U', true, $id ) >= $since ) { $recent++; }
		$t = get_post_meta( $id, '_rvn_type', true );
		if ( $t ) { $types[ $t ] = ( $types[ $t ] ?? 0 ) + 1; }
		$e = (string) get_post_meta( $id, '_rvn_estimate', true );
		if ( $e !== '' ) {
			if ( $cur === '' && preg_match( '/^\D+/', $e, $m ) ) { $cur = trim( $m[0] ); }
			$sum += (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '', $e ) );
		}
	}
	arsort( $types );

	echo '<div style="display:flex;gap:1.5rem;margin-bottom:1rem">';
	printf( '<div><div style="font-size:2rem;font-weight:600;line-height:1">%d</div><div style="color:#777">total enquiries</div></div>', $total );
	printf( '<div><div style="font-size:2rem;font-weight:600;line-height:1">%d</div><div style="color:#777">last 30 days</div></div>', $recent );
	if ( $sum > 0 ) { printf( '<div><div style="font-size:2rem;font-weight:600;line-height:1">%s%s</div><div style="color:#777">pipeline (est.)</div></div>', esc_html( $cur ), number_format( $sum ) ); }
	echo '</div>';

	if ( $types ) {
		echo '<table class="widefat striped" style="margin-top:.5rem"><thead><tr><th>Project type</th><th style="text-align:right">Enquiries</th></tr></thead><tbody>';
		foreach ( $types as $t => $n ) { printf( '<tr><td>%s</td><td style="text-align:right">%d</td></tr>', esc_html( $t ), $n ); }
		echo '</tbody></table>';
	}
	printf( '<p style="margin-top:1rem"><a href="%s">View all enquiries →</a></p>', esc_url( admin_url( 'edit.php?post_type=rvn_enquiry' ) ) );
}

/* ══════════════════════════════════════════════════════════════════════
   Before / after comparison slider (adapted from Obscura). A shortcode for
   journal posts + project write-ups; the drag interaction lives in the
   Astro frontend (ui.js .rvn-compare). Markup only here.
     [rvn_compare before="123" after="456"]                 (attachment IDs)
     [rvn_compare before="https://…/raw.avif" after="https://…/final.avif"
                  before_label="Raw" after_label="Graded" start="50"]
   ══════════════════════════════════════════════════════════════════════ */

add_shortcode( 'rvn_compare', 'rvn_compare_shortcode' );

function rvn_compare_shortcode( $atts ) {
	$a = shortcode_atts( array(
		'before' => '', 'after' => '', 'before_label' => 'Before', 'after_label' => 'After', 'start' => '50',
	), $atts, 'rvn_compare' );

	$before = rvn_compare_resolve( $a['before'] );
	$after  = rvn_compare_resolve( $a['after'] );
	if ( ! $before || ! $after ) { return ''; }
	$start = max( 0, min( 100, (int) $a['start'] ) );

	ob_start(); ?>
	<div class="rvn-compare" data-compare style="--start:<?php echo esc_attr( $start ); ?>%">
		<figure class="rvn-compare__media rvn-compare__after">
			<img src="<?php echo esc_url( $after['url'] ); ?>" alt="<?php echo esc_attr( $after['alt'] ); ?>" width="<?php echo esc_attr( $after['w'] ); ?>" height="<?php echo esc_attr( $after['h'] ); ?>" loading="lazy" decoding="async">
			<?php if ( $a['after_label'] ) : ?><figcaption class="rvn-compare__label rvn-compare__label--after"><?php echo esc_html( $a['after_label'] ); ?></figcaption><?php endif; ?>
		</figure>
		<figure class="rvn-compare__media rvn-compare__before" data-compare-clip>
			<img src="<?php echo esc_url( $before['url'] ); ?>" alt="<?php echo esc_attr( $before['alt'] ); ?>" width="<?php echo esc_attr( $before['w'] ); ?>" height="<?php echo esc_attr( $before['h'] ); ?>" loading="lazy" decoding="async">
			<?php if ( $a['before_label'] ) : ?><figcaption class="rvn-compare__label rvn-compare__label--before"><?php echo esc_html( $a['before_label'] ); ?></figcaption><?php endif; ?>
		</figure>
		<input type="range" class="rvn-compare__range" min="0" max="100" value="<?php echo esc_attr( $start ); ?>" aria-label="Reveal before / after" data-compare-range>
		<span class="rvn-compare__handle" aria-hidden="true"></span>
	</div>
	<?php
	return ob_get_clean();
}

function rvn_compare_resolve( $ref ) {
	$ref = trim( (string) $ref );
	if ( $ref === '' ) { return null; }
	if ( ctype_digit( $ref ) ) {
		$rec = rvn_image( (int) $ref, 'large' );
		return $rec ? array( 'url' => $rec['src'], 'alt' => $rec['alt'], 'w' => $rec['w'], 'h' => $rec['h'] ) : null;
	}
	if ( filter_var( $ref, FILTER_VALIDATE_URL ) ) {
		return array( 'url' => esc_url_raw( $ref ), 'alt' => '', 'w' => '', 'h' => '' );
	}
	return null;
}
