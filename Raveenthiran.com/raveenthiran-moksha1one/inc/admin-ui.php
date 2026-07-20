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
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'nr-admin-skin', get_template_directory_uri() . '/assets/css/admin.css', [], defined( 'NR_THEME_VERSION' ) ? NR_THEME_VERSION : null );
} );
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'nr-admin-skin', get_template_directory_uri() . '/assets/css/admin.css', [], defined( 'NR_THEME_VERSION' ) ? NR_THEME_VERSION : null );
} );

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
		$tiles[] = [
			'label' => $label,
			'url'   => nr_admin_menu_url( $slug ),
			'icon'  => nr_admin_menu_icon( $row[6] ?? '' ),
			'big'   => in_array( $slug, $feature, true ),
		];
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
	if ( ! $screen || $screen->id === 'dashboard' ) return; // not on the board itself
	printf(
		'<a class="nr-back-dash" href="%s">%s %s</a>',
		esc_url( admin_url( 'index.php' ) ),
		'&larr;',
		esc_html__( 'Dashboard', 'raveenthiran' )
	);
}, 20 );
