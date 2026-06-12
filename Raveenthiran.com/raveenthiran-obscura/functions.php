<?php
/**
 * Raveenthiran — Single-Screen Theme
 * functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'NR_THEME_VERSION', '4.71.0' );

/* ─────────────────────────────────────────────────────────────
 * Setup
 * ───────────────────────────────────────────────────────────── */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'raveenthiran', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', [ 'width' => 320, 'height' => 80, 'flex-width' => true, 'flex-height' => true ] );
	// Block editor reflects the Obscura look (dark canvas + theme fonts).
	add_theme_support( 'editor-styles' );
	add_theme_support( 'dark-editor-style' );
	add_editor_style( 'assets/css/editor-style.css' );

	register_nav_menus( [
		'primary'   => __( 'Primary Menu (top bar + sidebar)', 'raveenthiran' ),
		'secondary' => __( 'Secondary (sidebar legal links)',  'raveenthiran' ),
		'social'    => __( 'Social Links (sidebar)',           'raveenthiran' ),
	] );

	add_image_size( 'nr-hero',     2400, 1600, false );
	add_image_size( 'nr-hero-2x',  3600, 2400, false ); // retina hero (uncropped)
	add_image_size( 'nr-card',     1200, 1600, true );
	add_image_size( 'nr-card-2x',  1800, 2400, true ); // retina card
	add_image_size( 'nr-thumb',     600,  800, true );
	add_image_size( 'nr-og',       1200,  630, true ); // Open Graph share card
} );

/* ─────────────────────────────────────────────────────────────
 * Enqueue assets
 * ───────────────────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
	// fonts.css (~2.5KB of @font-face) is inlined into <head> below to drop a
	// render-blocking request; only the main stylesheet loads externally.
	wp_enqueue_style( 'nr-theme', get_template_directory_uri() . '/assets/css/theme.css', [], NR_THEME_VERSION );
	// Preload above-the-fold weights: 300 (big hero display title, was reflowing on
		// font-swap = large desktop CLS), 500 (body), 700 (display em).
	add_action( 'wp_head', function () {
		$u = get_template_directory_uri() . '/assets/fonts/';
		echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( $u . 'inter-tight-300.woff2' ) . '">' . "\n";
			echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( $u . 'inter-tight-500.woff2' ) . '">' . "\n";
		echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( $u . 'inter-tight-700.woff2' ) . '">' . "\n";
	}, 1 );

	wp_enqueue_script(
		'nr-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		[],
		NR_THEME_VERSION,
		true
	);

	// Batch 6 — GPU/canvas effects bundle (off unless enabled in Theme Settings).
	if ( nr_opt( 'nr_fx_gpu', '0' ) === '1' ) {
		wp_enqueue_script( 'nr-gpu-fx', get_template_directory_uri() . '/assets/js/gpu-fx.js', [ 'nr-theme' ], NR_THEME_VERSION, true );
		wp_enqueue_script( 'nr-awwwards', get_template_directory_uri() . '/assets/js/awwwards.js', [ 'nr-theme' ], NR_THEME_VERSION, true );
	}

	// #1 — optional WebGL hero transitions (off unless enabled in Theme Settings).
	if ( is_front_page() && nr_opt( 'nr_fx_webgl', '0' ) === '1' ) {
		wp_enqueue_script(
			'nr-webgl-hero',
			get_template_directory_uri() . '/assets/js/webgl-hero.js',
			[ 'nr-theme' ],
			NR_THEME_VERSION,
			true
		);
	}

	wp_localize_script( 'nr-theme', 'NR', [
		'home'     => home_url( '/' ),
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'nr_nonce' ),
		'hero'     => [
			'auto'     => nr_opt( 'nr_hero_auto', '1' ) === '1',
			'interval' => max( 2000, (int) nr_opt( 'nr_hero_interval', 9000 ) ),
		],
		'i18n'    => [
			'sending' => __( 'Sending…', 'raveenthiran' ),
			'sent'    => __( 'Sent — I will reply within 24 hours.', 'raveenthiran' ),
			'error'   => __( 'Network error.', 'raveenthiran' ),
		],
	] );
} );

/* Inline the small fonts.css (@font-face only) so it isn't a render-blocking
 * request. Relative font URLs are rewritten to absolute since the CSS now
 * lives in the document, not in /assets/css/. */
add_action( 'wp_head', function () {
	$file = get_template_directory() . '/assets/css/fonts.css';
	if ( ! is_readable( $file ) ) {
		// Fallback: enqueue externally if the file can't be read.
		wp_enqueue_style( 'nr-fonts', get_template_directory_uri() . '/assets/css/fonts.css', [], NR_THEME_VERSION );
		return;
	}
	$css = (string) file_get_contents( $file );
	$css = str_replace( '../fonts/', get_template_directory_uri() . '/assets/fonts/', $css );
	echo "<style id=\"nr-fonts\">" . $css . "</style>\n";
}, 2 );

/* Optional: load the main stylesheet non-render-blocking (preload + onload).
 * Off by default — it shaves the render-blocking time but can cause a brief
 * flash of unstyled content, so it's a Theme Settings opt-in. */
add_filter( 'style_loader_tag', function ( $tag, $handle, $href, $media ) {
	if ( $handle !== 'nr-theme' || is_admin() ) return $tag;
	if ( ! function_exists( 'nr_opt' ) || nr_opt( 'nr_perf_async_css', '0' ) !== '1' ) return $tag;
	return '<link rel="preload" as="style" href="' . esc_url( $href ) . '" onload="this.onload=null;this.rel=\'stylesheet\'">'
		. '<noscript><link rel="stylesheet" href="' . esc_url( $href ) . '"></noscript>' . "\n";
}, 10, 4 );

/* #55 — activate cross-document View Transitions when the toggle is on.
 * Emitted on every page so the morph works both leaving and arriving; the
 * matching view-transition-name on the card + hero is set in the templates. */
add_action( 'wp_head', function () {
	if ( nr_opt( 'nr_fx_viewtrans', '0' ) !== '1' ) return;
	// #55 morph + #82 choreography: the shared image morphs while the rest of
	// the page does a subtle scale/fade cross-dissolve.
	echo "<style id=\"nr-vt\">@media (prefers-reduced-motion: no-preference){"
		. "@view-transition{navigation:auto}"
		. "::view-transition-group(*){animation-duration:.5s;animation-timing-function:cubic-bezier(.7,0,.2,1)}"
		. "::view-transition-old(root){animation:nr-vt-out .4s both}"
		. "::view-transition-new(root){animation:nr-vt-in .5s both}"
		. "@keyframes nr-vt-out{to{opacity:0;transform:scale(.985)}}"
		. "@keyframes nr-vt-in{from{opacity:0;transform:scale(1.014)}}"
		. "}</style>\n";
}, 3 );

/* ─────────────────────────────────────────────────────────────
 * Custom Post Types — Projects, Services, Journal, Testimonials
 * ───────────────────────────────────────────────────────────── */
add_action( 'init', function () {

	register_post_type( 'nr_project', [
		'label'        => __( 'Projects', 'raveenthiran' ),
		'labels'       => [ 'singular_name' => __( 'Project', 'raveenthiran' ), 'add_new_item' => __( 'Add Project', 'raveenthiran' ) ],
		'public'       => true,
		'has_archive'  => 'portfolio',
		'rewrite'      => [ 'slug' => 'project' ],
		'menu_icon'    => 'dashicons-camera',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'show_in_rest' => true,
	] );

	register_taxonomy( 'nr_project_cat', 'nr_project', [
		'label'        => __( 'Categories', 'raveenthiran' ),
		'hierarchical' => true,
		'rewrite'      => [ 'slug' => 'category' ],
		'show_in_rest' => true,
	] );

	// #64 — keyword tags: a flat taxonomy for cross-cutting filters (location,
	// subject, technique…) that AND with categories on the portfolio archive.
	register_taxonomy( 'nr_project_tag', 'nr_project', [
		'labels'       => [
			'name'          => __( 'Tags', 'raveenthiran' ),
			'singular_name' => __( 'Tag', 'raveenthiran' ),
			'add_new_item'  => __( 'Add tag', 'raveenthiran' ),
			'menu_name'     => __( 'Tags', 'raveenthiran' ),
		],
		'hierarchical' => false,
		'rewrite'      => [ 'slug' => 'tag' ],
		'show_in_rest' => true,
		'show_admin_column' => true,
	] );

	register_post_type( 'nr_testimonial', [
		'label'    => __( 'Testimonials', 'raveenthiran' ),
		'public'   => false,
		'show_ui'  => true,
		'menu_icon'=> 'dashicons-format-quote',
		'supports' => [ 'title', 'editor' ],
	] );
} );

/* ─────────────────────────────────────────────────────────────
 * Theme settings live in inc/theme-settings.php (Appearance → Theme Settings).
 * The legacy "Site Settings" top-level menu was removed in v3.0.0 — every
 * value (nr_location, nr_email, social URLs, stats, etc.) now persists as
 * its own wp_options row, read by templates via nr_opt('nr_*').
 * The helper below is kept because it parses the awards/press textareas
 * (also editable via the Theme Settings page in v3.1+).
 * ───────────────────────────────────────────────────────────── */

/**
 * Parse the Recognition textareas into structured rows.
 * Format per line: year · title · organisation · url    (url optional)
 */
function nr_recognition_list( $option_key ) {
	$raw = (string) get_option( $option_key, '' );
	$out = [];
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( $line === '' ) continue;
		if ( preg_match( '/[·|]/u', $line ) ) {
			// Explicit columns: "year · name · org · url" (url optional, any order).
			$parts = array_map( 'trim', preg_split( '/\s*·\s*|\s*\|\s*/u', $line ) );
		} else {
			// No delimiter (e.g. "2025 Heute.at https://…"): pull the URL, take a
			// leading 4-digit year, treat the remaining words as the name.
			$tokens = preg_split( '/\s+/', $line );
			$u0 = ''; $kept = [];
			foreach ( $tokens as $t ) {
				if ( $u0 === '' && preg_match( '#^(https?://|www\.)#i', $t ) ) { $u0 = $t; continue; }
				$kept[] = $t;
			}
			$y0    = ( isset( $kept[0] ) && preg_match( '/^(19|20)\d{2}$/', $kept[0] ) ) ? array_shift( $kept ) : '';
			$parts = [ $y0, trim( implode( ' ', $kept ) ) ];
			if ( $u0 !== '' ) $parts[] = $u0;
		}
		// A URL can sit in any position — pull it out as the link and never show
		// it as text, so "year · name · url" works as well as "year · name · org · url".
		$url  = '';
		$rest = [];
		foreach ( $parts as $p ) {
			if ( $url === '' && preg_match( '#^(https?://|www\.)#i', $p ) ) {
				$url = ( stripos( $p, 'http' ) === 0 ) ? $p : 'https://' . $p;
				continue;
			}
			$rest[] = $p;
		}
		$row = [
			'year'  => $rest[0] ?? '',
			'title' => $rest[1] ?? '',
			'org'   => $rest[2] ?? '',
			'url'   => $url,
		];
		// Skip ghost rows (blank / punctuation-only lines) so an empty
		// Awards or Press list hides cleanly instead of showing a stray dash.
		if ( $row['year'] === '' && $row['title'] === '' && $row['org'] === '' ) continue;
		$out[] = $row;
	}
	return $out;
}

/* ─────────────────────────────────────────────────────────────
 * Helpers
 * ───────────────────────────────────────────────────────────── */
function nr_opt( $key, $default = '' ) {
	$v = get_option( $key, $default );
	return $v === '' ? $default : $v;
}

function nr_field( $key, $post_id = false ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $key, $post_id );
	}
	return get_post_meta( $post_id ?: get_the_ID(), $key, true );
}

function nr_project_meta() {
	$id = get_the_ID();
	return [
		'n'      => str_pad( (string) absint( nr_field( 'project_number', $id ) ?: get_post_field( 'menu_order', $id ) ?: 0 ), 2, '0', STR_PAD_LEFT ),
		'cat'    => wp_get_post_terms( $id, 'nr_project_cat', [ 'fields' => 'names' ] )[0] ?? '',
		'yr'     => nr_field( 'project_year', $id ) ?: get_the_date( 'Y', $id ),
		'client' => nr_field( 'project_client', $id ) ?: '',
		'loc'    => nr_field( 'project_location', $id ) ?: nr_opt( 'nr_location', 'Vienna' ),
	];
}

/**
 * Striped photo placeholder — used when a project / journal entry has no
 * featured image yet. Matches the prototype's NRImg.
 */
function nr_placeholder( $label = 'photo', $dark = true, $aspect = '3/4' ) {
	$bg1 = $dark ? '#1a1510' : '#E4E1DC';
	$bg2 = $dark ? '#0e0b07' : '#D8D5CF';
	$ink = $dark ? 'rgba(237,232,223,0.14)' : 'rgba(13,13,13,0.18)';
	$style = sprintf(
		'aspect-ratio:%s;background:repeating-linear-gradient(135deg,%s 0 18px,%s 18px 19px);position:relative;overflow:hidden;width:100%%;height:100%%',
		esc_attr( $aspect ), esc_attr( $bg1 ), esc_attr( $bg2 )
	);
	ob_start(); ?>
	<div class="nr-ph" style="<?php echo esc_attr( $style ); ?>">
		<span style="position:absolute;top:10px;left:12px;font-family:'JetBrains Mono',ui-monospace,monospace;font-size:9px;letter-spacing:.18em;color:<?php echo esc_attr( $ink ); ?>;text-transform:uppercase">[ <?php echo esc_html( $label ); ?> ]</span>
		<?php foreach ( [
			'top:6px;left:6px;width:10px;height:1px',
			'top:6px;left:6px;width:1px;height:10px',
			'top:6px;right:6px;width:10px;height:1px',
			'top:6px;right:6px;width:1px;height:10px',
			'bottom:6px;left:6px;width:10px;height:1px',
			'bottom:6px;left:6px;width:1px;height:10px',
			'bottom:6px;right:6px;width:10px;height:1px',
			'bottom:6px;right:6px;width:1px;height:10px',
		] as $tick ) : ?>
			<span style="position:absolute;background:<?php echo esc_attr( $ink ); ?>;<?php echo esc_attr( $tick ); ?>"></span>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Print thumbnail OR placeholder if missing.
 */
function nr_image_or_placeholder( $post_id, $size = 'nr-card', $label = '', $dark = true, $eager = false, $sizes = '(max-width:900px) 90vw, 42vw' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$attr = [
			'class'    => 'nr-img',
			'style'    => 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover',
			'alt'      => esc_attr( get_the_title( $post_id ) ),
			'loading'  => $eager ? 'eager' : 'lazy',
			'decoding' => 'async',
		];
		// Accurate sizes so the browser picks a card-width candidate instead of
		// WordPress' default full-width guess (cuts mobile image payload).
		if ( $sizes ) $attr['sizes'] = $sizes;
		if ( $eager ) $attr['fetchpriority'] = 'high';
		echo get_the_post_thumbnail( $post_id, $size, $attr );
	} else {
		echo '<div style="position:absolute;inset:0">';
		echo nr_placeholder( $label ?: get_the_title( $post_id ), $dark );
		echo '</div>';
	}
}

/* ─────────────────────────────────────────────────────────────
 * Feature modules (ported from claude/full-redesign)
 *
 * Loaded in order:
 *   acf-polyfill        — ACF stubs so the theme runs without the plugin
 *   functions-additions — helpers, sticky CTA, shortcodes, asset enqueue
 *   acf-fields          — registers ACF field groups (no-op without ACF)
 *   admin-panel         — Site Control admin dashboard
 *   performance         — WebP / LQIP on upload
 *   seo                 — schema.org, sitemap.xml, robots.txt
 *
 * Define NR_DISABLE_FEATURES in wp-config.php to skip all modules and
 * fall back to a clean rav_single-only theme.
 * ───────────────────────────────────────────────────────────── */
if ( ! defined( 'NR_DISABLE_FEATURES' ) || ! NR_DISABLE_FEATURES ) {
	foreach ( [
		'lib.php',
		'acf-polyfill.php',
		'functions-additions.php',
		'acf-fields.php',
		'performance.php',
		'seo.php',
		'theme-settings.php',
		'quote.php',
		'tier1.php',
		'tier2.php',
		'medium.php',
		'importer.php',
		'security.php',
		'pwa.php',
		'compare.php',
		'og-cards.php',
		'pdf.php',
		'series.php',
		'interlink.php',
		'map.php',
		'smtp.php',
		'insights.php',
		'webp.php',
		'admin-extras.php',
		'ideas-next.php',
		'seo-extra.php',
		'conversion-extra.php',
		'quickwins.php',
		'smallwins.php',
		'mediumwins.php',
		'admin-simplify.php',
		'mediumwins2.php',
		'infra.php',
		'studio-ops.php',
		'finishing.php',
		'medium-next.php',
		'medium2.php',
		'leftovers.php',
		'medium3.php',
		'districts.php',
		'medium4.php',
		'preshoot.php',
			'booking.php',
		'admin-hub.php',
	] as $nr_inc_file ) {
		$nr_inc_path = get_template_directory() . '/inc/' . $nr_inc_file;
		if ( ! file_exists( $nr_inc_path ) ) continue;
		try {
			require_once $nr_inc_path;
		} catch ( \Throwable $e ) {
			error_log( '[NR inc] ' . $nr_inc_file . ' failed: ' . $e->getMessage() );
		}
	}
	unset( $nr_inc_file, $nr_inc_path );
}

/* ─────────────────────────────────────────────────────────────
 * Front-end form handler — page-enquire.php / parts/inquiry-modal.php
 * all POST here. Forwards the message to
 * the studio email and redirects back with a status query arg.
 * Hooks: admin_post_nr_contact_send (logged-out users use the
 * _nopriv variant).
 * ───────────────────────────────────────────────────────────── */
function nr_handle_contact_send() {
	$nonce = $_POST['_nr_nonce'] ?? '';
	if ( ! wp_verify_nonce( $nonce, 'nr_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'nr_sent', '0', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}
	// #23 — honeypot: bots fill the hidden "company" field; humans never see it.
	if ( ! empty( $_POST['nr_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'nr_sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}
	// #23 — basic rate-limit: one submission per IP per 30s.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rk = 'nr_rl_' . md5( $ip );
	if ( $ip && get_transient( $rk ) ) {
		wp_safe_redirect( add_query_arg( 'nr_sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}
	if ( $ip ) set_transient( $rk, 1, 30 );

	// #74 — Cloudflare Turnstile (only enforced when keys are configured).
	if ( function_exists( 'nr_turnstile_passes' ) && ! nr_turnstile_passes() ) {
		wp_safe_redirect( add_query_arg( 'nr_sent', '0', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name']    ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email']   ?? '' ) );
	$notes   = sanitize_textarea_field( wp_unslash( $_POST['notes']   ?? '' ) );
	$type    = sanitize_text_field( wp_unslash( $_POST['project_type']   ?? '' ) );
	$date    = sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) );
	$est     = sanitize_text_field( wp_unslash( $_POST['estimate']       ?? '' ) );
	// Attribution — which project / source drove the enquiry.
	$ref_in   = sanitize_text_field( wp_unslash( $_POST['nr_ref']      ?? '' ) );
	$service  = sanitize_text_field( wp_unslash( $_POST['nr_service']  ?? '' ) );
	$referrer = esc_url_raw( wp_unslash( $_POST['nr_referrer']        ?? '' ) );
	$site    = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf( '[%s] New enquiry from %s', $site, $name ?: $email );
	$to      = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$lines   = [ "Name: {$name}", "Email: {$email}" ];
	if ( $type ) $lines[] = "Project type: {$type}";
	if ( $date ) $lines[] = "Preferred date: {$date}";
	if ( $est )  $lines[] = "Estimate: {$est}";
	$body    = implode( "\n", $lines ) . "\n\n{$notes}";
	$headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
	if ( $email ) $headers[] = 'Reply-To: ' . $email;
	$ok = wp_mail( $to, $subject, $body, $headers );

	// #22 — log the enquiry as a private CPT entry.
	if ( post_type_exists( 'nr_enquiry' ) ) {
		$eid = wp_insert_post( [
			'post_type'   => 'nr_enquiry',
			'post_status' => 'publish',
			'post_title'  => $name ?: $email ?: __( 'Enquiry', 'raveenthiran' ),
			'post_content'=> $notes,
		] );
		if ( $eid && ! is_wp_error( $eid ) ) {
			update_post_meta( $eid, '_nr_email', $email );
			update_post_meta( $eid, '_nr_type',  $type );
			update_post_meta( $eid, '_nr_est',   $est );
			update_post_meta( $eid, '_nr_date',  $date );
			if ( $ref_in )   update_post_meta( $eid, '_nr_ref',      $ref_in );
			if ( $service )  update_post_meta( $eid, '_nr_service',  $service );
			if ( $referrer ) update_post_meta( $eid, '_nr_referrer', $referrer );
			// #42 — optional reference images: validate (image, ≤5MB, ≤4) and
			// attach to the enquiry. Skips silently on any problem.
			if ( ! empty( $_FILES['nr_refs'] ) && is_array( $_FILES['nr_refs']['name'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$ref_ids = [];
				$count   = count( $_FILES['nr_refs']['name'] );
				for ( $fi = 0; $fi < min( 4, $count ); $fi++ ) {
					if ( empty( $_FILES['nr_refs']['name'][ $fi ] ) ) continue;
					if ( (int) ( $_FILES['nr_refs']['error'][ $fi ] ?? 1 ) !== UPLOAD_ERR_OK ) continue;
					if ( (int) ( $_FILES['nr_refs']['size'][ $fi ] ?? 0 ) > 5 * 1024 * 1024 ) continue;
					$ft = wp_check_filetype( (string) $_FILES['nr_refs']['name'][ $fi ] );
					if ( strpos( (string) $ft['type'], 'image/' ) !== 0 ) continue;
					$file = [
						'name'     => $_FILES['nr_refs']['name'][ $fi ],
						'type'     => $_FILES['nr_refs']['type'][ $fi ],
						'tmp_name' => $_FILES['nr_refs']['tmp_name'][ $fi ],
						'error'    => $_FILES['nr_refs']['error'][ $fi ],
						'size'     => $_FILES['nr_refs']['size'][ $fi ],
					];
					$_FILES['nr_ref_single'] = $file;
					$aid = media_handle_upload( 'nr_ref_single', $eid );
					if ( ! is_wp_error( $aid ) ) $ref_ids[] = (int) $aid;
				}
				unset( $_FILES['nr_ref_single'] );
				if ( $ref_ids ) update_post_meta( $eid, '_nr_ref_images', $ref_ids );
			}
		}
	}

	// #21 — branded auto-reply to the enquirer so they know it landed.
	if ( $email && is_email( $email ) ) {
		$from   = nr_opt( 'nr_email', get_option( 'admin_email' ) );
		$ack_s  = sprintf( __( 'Thank you — %s', 'raveenthiran' ), $site );

		// #70 — attach a branded PDF estimate when the brief carried a number.
		$pdf_path = '';
		if ( $est && function_exists( 'nr_quote_pdf_file' ) ) {
			$pdf_path = nr_quote_pdf_file( [
				'name'     => $name,
				'type'     => $type,
				'date'     => $date,
				'estimate' => $est,
			] );
		}
		$est_note = $pdf_path ? __( "\n\nI've attached a non-binding estimate as a PDF for your records.", 'raveenthiran' ) : '';

		$ack_b  = sprintf(
			/* translators: 1: name, 2: studio/site name, 3: optional estimate note */
			__( "Hi %1\$s,\n\nThank you for reaching out — your enquiry has arrived and I'll reply personally within 24 hours.%3\$s\n\nIf anything is urgent before then, just reply to this email.\n\n— %2\$s", 'raveenthiran' ),
			$name ?: __( 'there', 'raveenthiran' ), $site, $est_note
		);
		$ack_headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $from ];
		$ack_attach  = $pdf_path ? [ $pdf_path ] : [];
		wp_mail( $email, $ack_s, $ack_b, $ack_headers, $ack_attach );
		if ( $pdf_path ) @unlink( $pdf_path );
	}

	wp_safe_redirect( add_query_arg( 'nr_sent', $ok ? '1' : '0', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
}
add_action( 'admin_post_nr_contact_send', 'nr_handle_contact_send' );
add_action( 'admin_post_nopriv_nr_contact_send', 'nr_handle_contact_send' );

/* AJAX handler used by parts/inquiry-modal.php */
function nr_handle_contact_form_ajax() {
	check_ajax_referer( 'nr_nonce', 'nonce' );
	if ( ! empty( $_POST['nr_website'] ) ) wp_send_json_error( __( 'Spam detected.', 'raveenthiran' ) );
	$name    = sanitize_text_field( wp_unslash( $_POST['nr_name']    ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['nr_email']   ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['nr_message'] ?? '' ) );
	if ( ! $name || ! $email || ! $message ) {
		wp_send_json_error( __( 'Please fill in every field.', 'raveenthiran' ) );
	}
	$to      = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$subject = sprintf( '[%s] Quick enquiry from %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $name );
	$headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ];
	$ok      = wp_mail( $to, $subject, "Name: {$name}\nEmail: {$email}\n\n{$message}", $headers );
	if ( $ok ) {
		wp_send_json_success( __( 'Sent — I will reply within 24 hours.', 'raveenthiran' ) );
	}
	wp_send_json_error( __( 'Sending failed. Email me directly:', 'raveenthiran' ) . ' ' . $to );
}
add_action( 'wp_ajax_nr_contact_form',        'nr_handle_contact_form_ajax' );
add_action( 'wp_ajax_nopriv_nr_contact_form', 'nr_handle_contact_form_ajax' );
