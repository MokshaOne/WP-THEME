<?php
/**
 * moksha1one — Backend experience. The theme's "Obsidian & Gilt" feeling is
 * not only front end: this file dresses the whole wp-admin, turns the dashboard
 * home into a Windows-11-style tile mosaic built from the real admin menu, and
 * puts a "Back to dashboard" control on every screen. The native admin menu
 * stays as the contextual sidebar on sub-pages (styled by assets/css/admin.css).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   Load the whole-backend skin on every admin screen + login.
   ============================================================= */
function nr_admin_assets() {
	$ver = defined( 'NR_THEME_VERSION' ) ? NR_THEME_VERSION : null;
	// the real theme faces (Syne + JetBrains Mono + Inter Tight) so the backend
	// reads in the same type as the front end
	wp_enqueue_style( 'nr-admin-fonts', get_template_directory_uri() . '/assets/css/fonts.css', [], $ver );
	wp_enqueue_style( 'nr-admin-skin', get_template_directory_uri() . '/assets/css/admin.css', [ 'nr-admin-fonts' ], $ver );
}
add_action( 'admin_enqueue_scripts', 'nr_admin_assets' );
add_action( 'login_enqueue_scripts', 'nr_admin_assets' );

/* =============================================================
   Login screen — full VOID rebrand (logo → moksha wordmark, obsidian
   canvas + orb, gilt button). Styling lives in assets/css/admin.css.
   ============================================================= */
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );
add_action( 'login_message', function ( $msg ) {
	// a small mono eyebrow above the form, matching the site HUD
	return '<p class="nr-login-eyebrow">// ' . esc_html( strtoupper( nr_opt( 'nr_void_sub', 'MOKSHA·ONE' ) ) ) . '</p>' . $msg;
} );

/* =============================================================
   Admin bar — drop the WordPress logo, warm the greeting.
   ============================================================= */
add_action( 'admin_bar_menu', function ( $bar ) {
	$bar->remove_node( 'wp-logo' );
	$bar->remove_node( 'wp-logo-external' );
}, 999 );
/* "Howdy, X" → "Studio · X" (front-of-house tone) */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( is_admin() && $domain === 'default' && strpos( $text, 'Howdy' ) === 0 ) {
		return str_replace( 'Howdy', 'Studio', $translated );
	}
	return $translated;
}, 10, 3 );

/* =============================================================
   Admin footer — replace the WordPress credit with the studio's own.
   ============================================================= */
add_filter( 'admin_footer_text', function () {
	return '<span style="color:var(--v-ink3)">moksha1one <span style="color:var(--v-gold)">✦</span> ' . esc_html__( 'Studio control center', 'raveenthiran' ) . '</span>';
} );
add_filter( 'update_footer', function () {
	return 'v' . ( defined( 'NR_THEME_VERSION' ) ? NR_THEME_VERSION : '' );
}, 11 );

/* =============================================================
   No app store — the studio installs its own theme + plugins by zip, it
   doesn't browse the wordpress.org directory. Neutralise the store feeds
   (so nothing is fetched/listed) but keep the "Upload" forms working.
   ============================================================= */
$nr_empty_api = function () {
	return (object) [ 'info' => [ 'page' => 1, 'pages' => 0, 'results' => 0 ], 'themes' => [], 'plugins' => [] ];
};
add_filter( 'themes_api_result',  $nr_empty_api, 99 );
add_filter( 'plugins_api_result', $nr_empty_api, 99 );

/* trim the Appearance menu: drop the wp.org "Add New" screen and the surfaces
   the studio doesn't use (Patterns, Customize, Menus, Fonts). Themes and the
   Theme File Editor stay. */
add_action( 'admin_menu', function () {
	remove_submenu_page( 'themes.php', 'theme-install.php' );
	remove_submenu_page( 'plugins.php', 'plugin-install.php' );
	remove_submenu_page( 'themes.php', 'nav-menus.php' );                 // Menus
	remove_submenu_page( 'themes.php', 'edit.php?post_type=wp_block' );   // Patterns

	// Customize + Fonts carry dynamic query args, so match them by substring.
	global $submenu;
	if ( ! empty( $submenu['themes.php'] ) ) {
		foreach ( $submenu['themes.php'] as $i => $row ) {
			$slug = $row[2] ?? '';
			if ( strpos( $slug, 'customize.php' ) !== false            // Customize
			  || strpos( $slug, 'wp_block' ) !== false                 // Patterns (fallback)
			  || strpos( $slug, 'nav-menus.php' ) !== false            // Menus (fallback)
			  || stripos( $slug, 'font' ) !== false ) {                // Fonts
				unset( $submenu['themes.php'][ $i ] );
			}
		}
	}
}, 999 );

/* the store screens (if reached by URL) open straight on the Upload form */
add_action( 'admin_head', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) return;
	if ( $screen->id === 'theme-install' ) {
		echo '<script>document.addEventListener("DOMContentLoaded",function(){document.body.classList.add("show-upload-theme");});</script>';
	}
	if ( $screen->id === 'plugin-install' ) {
		echo '<script>document.addEventListener("DOMContentLoaded",function(){var b=document.querySelector(".upload-view-toggle");if(b&&b.getAttribute("aria-expanded")!=="true")b.click();});</script>';
	}
} );

/* Pull the uploader one level up: render the zip-upload form right on the
   Themes / Plugins list screens (as a VOID card), so there's no "Add New"
   detour. The native "Add New" title buttons are hidden via CSS. */
add_action( 'in_admin_header', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) return;

	if ( $screen->id === 'themes' && current_user_can( 'upload_themes' ) ) {
		?>
		<div class="nr-upload">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span><?php esc_html_e( 'INSTALL THEME (.ZIP)', 'raveenthiran' ); ?></span>
			<form method="post" enctype="multipart/form-data" class="nr-upload__form" action="<?php echo esc_url( admin_url( 'update.php?action=upload-theme' ) ); ?>">
				<?php wp_nonce_field( 'theme-upload' ); ?>
				<input type="file" name="themezip" accept=".zip" required>
				<?php submit_button( __( 'Upload &amp; install', 'raveenthiran' ), 'primary', 'install-theme-submit', false ); ?>
			</form>
		</div>
		<?php
	}

	if ( $screen->id === 'plugins' && current_user_can( 'upload_plugins' ) ) {
		?>
		<div class="nr-upload">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span><?php esc_html_e( 'INSTALL PLUGIN (.ZIP)', 'raveenthiran' ); ?></span>
			<form method="post" enctype="multipart/form-data" class="nr-upload__form" action="<?php echo esc_url( admin_url( 'update.php?action=upload-plugin' ) ); ?>">
				<?php wp_nonce_field( 'plugin-upload' ); ?>
				<input type="file" name="pluginzip" accept=".zip" required>
				<?php submit_button( __( 'Upload &amp; install', 'raveenthiran' ), 'primary', 'install-plugin-submit', false ); ?>
			</form>
		</div>
		<?php
	}
}, 15 );

/* =============================================================
   Comments — the studio doesn't run a comment section. Remove it
   everywhere: menu, toolbar, support, columns, feeds.
   ============================================================= */
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}, 999 );
add_action( 'admin_bar_menu', function ( $bar ) { $bar->remove_node( 'comments' ); }, 999 );
add_action( 'init', function () {
	foreach ( get_post_types() as $pt ) {
		if ( post_type_supports( $pt, 'comments' ) )  remove_post_type_support( $pt, 'comments' );
		if ( post_type_supports( $pt, 'trackbacks' ) ) remove_post_type_support( $pt, 'trackbacks' );
	}
}, 100 );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
// drop the "Comments" column from every list table
add_action( 'admin_init', function () {
	foreach ( [ 'edit-post', 'edit-page' ] as $sc ) {
		add_filter( 'manage_' . $sc . '_columns', function ( $cols ) { unset( $cols['comments'] ); return $cols; } );
	}
} );
// remove the dashboard "Recent Comments" + discussion widgets handled below

/* =============================================================
   Dashboard — clear the stock WordPress widgets so only the studio's
   own (Studio overview, Enquiry insights) sit under the tiles.
   ============================================================= */
add_action( 'wp_dashboard_setup', function () {
	$core = [
		'dashboard_activity','dashboard_right_now','dashboard_quick_press',
		'dashboard_primary','dashboard_secondary','dashboard_site_health',
		'dashboard_incoming_links','dashboard_plugins','dashboard_recent_drafts',
		'dashboard_recent_comments','welcome_panel',
	];
	foreach ( $core as $id ) {
		remove_meta_box( $id, 'dashboard', 'normal' );
		remove_meta_box( $id, 'dashboard', 'side' );
	}
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}, 20 );

/* =============================================================
   ONE control hub — a single top-level "Site Content" menu that is the
   place to steer the whole site. Its landing page is a tile board linking
   to every area; Theme Settings and the ACF content fields become its
   sub-pages (re-parented from Appearance / their own top level).
   ============================================================= */
add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'Site Content', 'raveenthiran' ),
		__( 'Site Content', 'raveenthiran' ),
		'manage_options',
		'nr-control',
		'nr_control_hub_render',
		'dashicons-layout',
		3
	);
	// rename the auto-created first sub-item to "Overview"
	global $submenu;
	if ( isset( $submenu['nr-control'][0][0] ) ) $submenu['nr-control'][0][0] = __( 'Overview', 'raveenthiran' );
}, 9 );

/* All theme CPTs move under the hub — one place to steer. The engine
   registrations stay untouched; this filter re-parents their menus. */
add_filter( 'register_post_type_args', function ( $args, $pt ) {
	if ( in_array( $pt, [ 'nr_project', 'nr_journal', 'nr_testimonial', 'nr_enquiry' ], true ) ) {
		$args['show_in_menu'] = 'nr-control';
	}
	return $args;
}, 10, 2 );

/* Fixed, sensible order inside the hub: Overview → content → collections → tools */
add_action( 'admin_menu', function () {
	global $submenu;
	if ( empty( $submenu['nr-control'] ) ) return;
	$order = [ 'nr-control', 'nr-site-settings', 'nr-theme-settings',
		'edit.php?post_type=nr_project', 'edit.php?post_type=nr_journal',
		'edit.php?post_type=nr_testimonial', 'edit.php?post_type=nr_enquiry',
		'nr-import', 'nr-webp' ];
	usort( $submenu['nr-control'], function ( $a, $b ) use ( $order ) {
		$ia = array_search( $a[2] ?? '', $order, true ); $ib = array_search( $b[2] ?? '', $order, true );
		return ( $ia === false ? 99 : $ia ) <=> ( $ib === false ? 99 : $ib );
	} );
}, 999 );

/** The steering board — grouped tiles to every content + system area. */
function nr_control_hub_render() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	$groups = [
		__( 'Content', 'raveenthiran' ) => [
			[ 'Content fields',  'admin.php?page=nr-site-settings',  'dashicons-layout',          __( 'Hero, About, CTA, FAQ, Awards, Press, SEO, Newsletter…', 'raveenthiran' ) ],
			[ 'Theme settings',  'admin.php?page=nr-theme-settings', 'dashicons-admin-generic',   __( 'Branding, colours, Magic Control, page copy, mail…', 'raveenthiran' ) ],
		],
		__( 'Collections', 'raveenthiran' ) => [
			[ 'Projects',     'edit.php?post_type=nr_project',     'dashicons-camera',        __( 'Portfolio specimens', 'raveenthiran' ) ],
			[ 'Journal',      'edit.php?post_type=nr_journal',     'dashicons-book',          __( 'Writing & entries', 'raveenthiran' ) ],
			[ 'Testimonials', 'edit.php?post_type=nr_testimonial', 'dashicons-format-quote',  __( 'Client voices', 'raveenthiran' ) ],
			[ 'Enquiries',    'edit.php?post_type=nr_enquiry',     'dashicons-email',         __( 'Incoming briefs', 'raveenthiran' ) ],
		],
		__( 'Library', 'raveenthiran' ) => [
			[ 'Pages', 'edit.php?post_type=page', 'dashicons-admin-page',  __( 'Standalone pages', 'raveenthiran' ) ],
			[ 'Posts', 'edit.php',                'dashicons-admin-post',  __( 'Blog posts', 'raveenthiran' ) ],
			[ 'Media', 'upload.php',              'dashicons-admin-media', __( 'Images & files', 'raveenthiran' ) ],
		],
		__( 'System', 'raveenthiran' ) => [
			[ 'Users',    'users.php',            'dashicons-admin-users',    __( 'Studio accounts', 'raveenthiran' ) ],
			[ 'Tools',    'tools.php',            'dashicons-admin-tools',    __( 'Import / export', 'raveenthiran' ) ],
			[ 'Settings', 'options-general.php',  'dashicons-admin-settings', __( 'WordPress core', 'raveenthiran' ) ],
		],
	];
	?>
	<div class="wrap nr-settings">
		<header class="nr-adm-head">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span>CONTROL CENTER // moksha·one</span>
			<h1 class="nr-adm-title">Site <span class="nr-adm-gold">Content</span></h1>
			<p class="nr-adm-lede"><?php esc_html_e( 'One place to steer everything — content, collections, settings and system. Pick a tile.', 'raveenthiran' ); ?></p>
		</header>
		<?php $gi = 0; foreach ( $groups as $label => $tiles ) : $gi++; ?>
			<h2 class="nr-chapter"><span class="nr-chapter__n"><?php echo esc_html( (string) $gi ); ?></span> <?php echo esc_html( $label ); ?></h2>
			<div class="nr-tiles">
				<?php foreach ( $tiles as $t ) : ?>
					<a class="nr-tile" href="<?php echo esc_url( admin_url( $t[1] ) ); ?>">
						<span class="nr-tile__ico"><span class="dashicons <?php echo esc_attr( $t[2] ); ?>"></span></span>
						<span class="nr-tile__label"><?php echo esc_html( $t[0] ); ?></span>
						<span class="nr-tile__sub"><?php echo esc_html( $t[3] ); ?></span>
						<span class="nr-tile__go" aria-hidden="true">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/* =============================================================
   Helper — resolve an admin menu slug to a real URL (mirrors core).
   ============================================================= */
function nr_admin_menu_url( $slug ) {
	if ( strpos( $slug, '://' ) !== false ) return $slug; // already absolute
	// A file that exists in admin (index.php, edit.php, edit.php?post_type=…, etc.)
	$file = preg_replace( '/\?.*$/', '', $slug );
	if ( $slug === '' ) return '';
	if ( strpos( $slug, '.php' ) !== false ) return admin_url( $slug );
	return admin_url( 'admin.php?page=' . $slug );
}

/* =============================================================
   Helper — the icon markup for a menu row (dashicon / image / fallback).
   ============================================================= */
function nr_admin_menu_icon( $icon ) {
	if ( is_string( $icon ) && strpos( $icon, 'dashicons-' ) === 0 ) {
		return '<span class="dashicons ' . esc_attr( $icon ) . '"></span>';
	}
	if ( is_string( $icon ) && ( strpos( $icon, 'data:' ) === 0 || strpos( $icon, 'http' ) === 0 ) ) {
		return '<img src="' . esc_url( $icon ) . '" alt="" aria-hidden="true">';
	}
	return '<span class="dashicons dashicons-admin-generic"></span>';
}

/* =============================================================
   Windows-11-style tile mosaic on the dashboard home, built from the
   real top-level admin menu so it always reflects what's installed.
   Printed above the default widgets (non-destructive).
   ============================================================= */
add_action( 'in_admin_header', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->id !== 'dashboard' ) return;

	global $menu;
	if ( empty( $menu ) || ! is_array( $menu ) ) return;

	// Which slugs deserve a larger "feature" tile (mosaic rhythm).
	$feature = [ 'edit.php?post_type=nr_project', 'edit.php?post_type=nr_journal', 'themes.php', 'upload.php' ];

	$tiles = [];
	$seen  = [];
	foreach ( $menu as $row ) {
		if ( empty( $row[0] ) ) continue;                                   // separators
		if ( ! empty( $row[4] ) && strpos( $row[4], 'wp-menu-separator' ) !== false ) continue;
		$cap  = $row[1] ?? 'read';
		if ( ! current_user_can( $cap ) ) continue;
		$slug = $row[2] ?? '';
		if ( $slug === 'index.php' || $slug === '' ) continue;              // skip Dashboard itself
		// strip the notification bubbles from the label
		$label = trim( preg_replace( '/<span[^>]*>.*?<\/span>/s', '', $row[0] ) );
		$label = wp_strip_all_tags( $label );
		if ( $label === '' ) continue;
		$seen[ $slug ] = true;
		$tiles[] = [
			'label' => $label,
			'url'   => nr_admin_menu_url( $slug ),
			'icon'  => nr_admin_menu_icon( $row[6] ?? '' ),
			'big'   => in_array( $slug, $feature, true ) || $slug === 'nr-control',
		];
	}

	// The hub's children (CPTs, Content fields, Theme Settings, tools) are
	// submenus now — surface them on the board too, with their own icons.
	global $submenu;
	$hub_icons = [
		'nr-site-settings'                   => 'dashicons-layout',
		'nr-theme-settings'                  => 'dashicons-admin-generic',
		'edit.php?post_type=nr_project'      => 'dashicons-camera',
		'edit.php?post_type=nr_journal'      => 'dashicons-book',
		'edit.php?post_type=nr_testimonial'  => 'dashicons-format-quote',
		'edit.php?post_type=nr_enquiry'      => 'dashicons-email',
		'nr-import'                          => 'dashicons-upload',
		'nr-webp'                            => 'dashicons-images-alt2',
	];
	if ( ! empty( $submenu['nr-control'] ) ) {
		foreach ( $submenu['nr-control'] as $row ) {
			$slug = $row[2] ?? '';
			if ( $slug === '' || $slug === 'nr-control' || isset( $seen[ $slug ] ) ) continue;
			if ( ! current_user_can( $row[1] ?? 'read' ) ) continue;
			$label = wp_strip_all_tags( trim( preg_replace( '/<span[^>]*>.*?<\/span>/s', '', $row[0] ) ) );
			if ( $label === '' ) continue;
			$seen[ $slug ] = true;
			$tiles[] = [
				'label' => $label,
				'url'   => nr_admin_menu_url( $slug ),
				'icon'  => nr_admin_menu_icon( $hub_icons[ $slug ] ?? '' ),
				'big'   => in_array( $slug, $feature, true ),
			];
		}
	}
	if ( ! $tiles ) return;

	$user = wp_get_current_user();
	?>
	<div class="nr-dash">
		<header class="nr-dash__head">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span>CONTROL CENTER // moksha·one</span>
			<h2 class="nr-dash__title"><?php printf( esc_html__( 'Welcome back, %s', 'raveenthiran' ), esc_html( $user->display_name ) ); ?><span class="nr-adm-gold">.</span></h2>
			<p class="nr-dash__lede"><?php esc_html_e( 'Everything in one board — pick a tile to jump in. The full menu stays on the left as the sidebar for each section.', 'raveenthiran' ); ?></p>
		</header>
		<div class="nr-tiles">
			<?php foreach ( $tiles as $t ) : ?>
				<a class="nr-tile<?php echo $t['big'] ? ' nr-tile--big' : ''; ?>" href="<?php echo esc_url( $t['url'] ); ?>">
					<span class="nr-tile__ico"><?php echo $t['icon']; // safe: dashicon span / esc'd img ?></span>
					<span class="nr-tile__label"><?php echo esc_html( $t['label'] ); ?></span>
					<span class="nr-tile__go" aria-hidden="true">→</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
} );

/* =============================================================
   "Back to dashboard" — a Toolbar node on every screen, plus a visible
   button at the top of every sub-page (skipped on the dashboard itself).
   ============================================================= */
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! is_admin() ) return;
	$bar->add_node( [
		'id'    => 'nr-back-dash',
		'title' => '⟵ ' . __( 'Dashboard', 'raveenthiran' ),
		'href'  => admin_url( 'index.php' ),
		'meta'  => [ 'title' => __( 'Back to the moksha1one control center', 'raveenthiran' ) ],
	] );
}, 8 );

add_action( 'in_admin_header', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->id === 'dashboard' ) return;          // not on the board itself
	if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) return; // ignore the block editor

	// section label for the eyebrow — post type, taxonomy, or the menu title
	$label = '';
	if ( ! empty( $screen->post_type ) && ( $pto = get_post_type_object( $screen->post_type ) ) ) {
		$label = $pto->labels->name;
	} elseif ( ! empty( $screen->taxonomy ) && ( $tx = get_taxonomy( $screen->taxonomy ) ) ) {
		$label = $tx->labels->name;
	} else {
		$label = wp_strip_all_tags( (string) ( $GLOBALS['title'] ?? $screen->id ) );
	}
	?>
	<div class="nr-ctxbar">
		<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span><?php echo esc_html( strtoupper( $label ) ); ?></span>
		<a class="nr-back-dash" href="<?php echo esc_url( admin_url( 'admin.php?page=nr-control' ) ); ?>">&larr; <?php esc_html_e( 'Site Content', 'raveenthiran' ); ?></a>
	</div>
	<?php
}, 20 );

/* =============================================================
   Per-CPT horizontal filmstrip — every theme post type gets a front-end-style
   slider of its entries at the top of its list screen (the native list table
   stays below for search / bulk / filters). Reflects the site's own look.
   ============================================================= */
function nr_cpt_strip_types() {
	return [
		'post'           => [ 'icon' => 'dashicons-admin-post',    'label' => __( 'Post', 'raveenthiran' ) ],
		'page'           => [ 'icon' => 'dashicons-admin-page',    'label' => __( 'Page', 'raveenthiran' ) ],
		'nr_project'     => [ 'icon' => 'dashicons-camera',        'label' => __( 'Project', 'raveenthiran' ) ],
		'nr_journal'     => [ 'icon' => 'dashicons-book',          'label' => __( 'Journal entry', 'raveenthiran' ) ],
		'nr_testimonial' => [ 'icon' => 'dashicons-format-quote',  'label' => __( 'Testimonial', 'raveenthiran' ) ],
		'nr_enquiry'     => [ 'icon' => 'dashicons-email',         'label' => __( 'Enquiry', 'raveenthiran' ) ],
	];
}

/* flag the strip screens so CSS can hide the native table by default */
add_filter( 'admin_body_class', function ( $classes ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && $screen->base === 'edit' && isset( nr_cpt_strip_types()[ $screen->post_type ] ) ) {
		$classes .= ' nr-strip-screen';
	}
	return $classes;
} );

add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->base !== 'edit' ) return;
	$pt    = $screen->post_type;
	$types = nr_cpt_strip_types();
	if ( ! isset( $types[ $pt ] ) ) return;

	$obj   = get_post_type_object( $pt );
	$cfg   = $types[ $pt ];
	$icon  = $cfg['icon'];

	$q = new WP_Query( [
		'post_type'      => $pt,
		'post_status'    => [ 'publish', 'future', 'draft', 'pending', 'private' ],
		'posts_per_page' => 60,
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		'no_found_rows'  => true,
	] );

	$plural = $obj ? $obj->labels->name : $pt;
	?>
	<div class="nr-cpt-strip" data-nr-strip data-nr-pt="<?php echo esc_attr( $pt ); ?>">
		<div class="nr-cpt-strip__head">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span>GALLERY // <?php echo esc_html( strtoupper( $plural ) ); ?></span>
			<div class="nr-cpt-strip__tools">
				<span class="nr-cpt-strip__hint"><?php echo esc_html( $q->post_count ); ?> <?php esc_html_e( 'shown', 'raveenthiran' ); ?></span>
				<div class="nr-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View', 'raveenthiran' ); ?>">
					<button type="button" class="nr-view-btn is-on" data-view="grid"><span class="dashicons dashicons-grid-view"></span> <?php esc_html_e( 'Cards', 'raveenthiran' ); ?></button>
					<button type="button" class="nr-view-btn" data-view="list"><span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'List', 'raveenthiran' ); ?></button>
				</div>
			</div>
		</div>
		<div class="nr-cpt-grid">
			<a class="nr-cpt-card nr-cpt-card--add" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $pt ) ); ?>">
				<span class="nr-cpt-add__plus">+</span>
				<span class="nr-cpt-add__label"><?php printf( esc_html__( 'New %s', 'raveenthiran' ), esc_html( $cfg['label'] ) ); ?></span>
			</a>
			<?php while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
				$status = get_post_status( $id );
				$edit   = get_edit_post_link( $id );
				// meta line: project category · year, else the date
				$meta = '';
				if ( $pt === 'nr_project' && function_exists( 'nr_project_meta' ) ) {
					$pm = nr_project_meta( $id );
					$meta = trim( ( $pm['cat'] ?? '' ) . ( ! empty( $pm['yr'] ) ? ' · ' . $pm['yr'] : '' ), ' ·' );
				}
				if ( $meta === '' ) $meta = get_the_date();
				?>
				<a class="nr-cpt-card" href="<?php echo esc_url( $edit ); ?>">
					<span class="nr-cpt-card__status nr-st-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status === 'publish' ? __( 'live', 'raveenthiran' ) : $status ); ?></span>
					<span class="nr-cpt-card__frame">
						<?php if ( has_post_thumbnail( $id ) ) : ?>
							<?php echo get_the_post_thumbnail( $id, 'nr-thumb', [ 'loading' => 'lazy', 'decoding' => 'async' ] ); ?>
						<?php else : ?>
							<span class="nr-cpt-card__ph"><span class="dashicons <?php echo esc_attr( $icon ); ?>"></span></span>
						<?php endif; ?>
					</span>
					<span class="nr-cpt-card__body">
						<span class="nr-cpt-card__title"><?php echo esc_html( get_the_title() ?: __( '(untitled)', 'raveenthiran' ) ); ?></span>
						<span class="nr-cpt-card__meta"><?php echo esc_html( $meta ); ?></span>
					</span>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
	<script>
	(function(){
		var strip=document.querySelector('[data-nr-strip]'); if(!strip)return;
		var pt=strip.getAttribute('data-nr-pt')||'x', key='nrView:'+pt, body=document.body;
		function apply(v){
			body.classList.toggle('nr-view-list', v==='list');
			strip.querySelectorAll('.nr-view-btn').forEach(function(b){ b.classList.toggle('is-on', b.getAttribute('data-view')===v); });
		}
		var saved=null; try{ saved=localStorage.getItem(key); }catch(e){}
		apply(saved==='list'?'list':'grid');
		strip.querySelectorAll('.nr-view-btn').forEach(function(b){
			b.addEventListener('click',function(){ var v=b.getAttribute('data-view'); apply(v); try{ localStorage.setItem(key,v); }catch(e){} });
		});
	})();
	</script>
	<?php
} );
