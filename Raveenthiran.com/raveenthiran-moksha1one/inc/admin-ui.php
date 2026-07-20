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

/* =============================================================
   Per-CPT horizontal filmstrip — every theme post type gets a front-end-style
   slider of its entries at the top of its list screen (the native list table
   stays below for search / bulk / filters). Reflects the site's own look.
   ============================================================= */
function nr_cpt_strip_types() {
	return [
		'nr_project'     => [ 'icon' => 'dashicons-camera',        'label' => __( 'Project', 'raveenthiran' ) ],
		'nr_journal'     => [ 'icon' => 'dashicons-book',          'label' => __( 'Journal entry', 'raveenthiran' ) ],
		'nr_testimonial' => [ 'icon' => 'dashicons-format-quote',  'label' => __( 'Testimonial', 'raveenthiran' ) ],
		'nr_enquiry'     => [ 'icon' => 'dashicons-email',         'label' => __( 'Enquiry', 'raveenthiran' ) ],
	];
}

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
		'posts_per_page' => 40,
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
		'no_found_rows'  => true,
	] );

	$plural = $obj ? $obj->labels->name : $pt;
	?>
	<div class="nr-cpt-strip" data-nr-strip>
		<div class="nr-cpt-strip__head">
			<span class="nr-adm-eyebrow"><span class="nr-adm-rule"></span>FILMSTRIP // <?php echo esc_html( strtoupper( $plural ) ); ?></span>
			<span class="nr-cpt-strip__hint"><?php echo esc_html( $q->post_count ); ?> <?php esc_html_e( 'shown', 'raveenthiran' ); ?> · <?php esc_html_e( 'scroll sideways', 'raveenthiran' ); ?> →</span>
		</div>
		<div class="nr-cpt-rail">
			<a class="nr-cpt-card nr-cpt-card--add" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $pt ) ); ?>">
				<span class="nr-cpt-add__plus">+</span>
				<span class="nr-cpt-add__label"><?php printf( esc_html__( 'New %s', 'raveenthiran' ), esc_html( $cfg['label'] ) ); ?></span>
			</a>
			<?php while ( $q->have_posts() ) : $q->the_post(); $id = get_the_ID();
				$status = get_post_status( $id );
				$edit   = get_edit_post_link( $id );
				// meta line: first relevant term, else the date
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
		var rail=document.querySelector('[data-nr-strip] .nr-cpt-rail');
		if(!rail)return;
		// vertical wheel travels sideways — the front-end filmstrip feeling
		rail.addEventListener('wheel',function(e){
			if(Math.abs(e.deltaY)<=Math.abs(e.deltaX))return;
			if((e.deltaY<0&&rail.scrollLeft===0)||(e.deltaY>0&&Math.ceil(rail.scrollLeft+rail.clientWidth)>=rail.scrollWidth))return;
			e.preventDefault();rail.scrollLeft+=e.deltaY;
		},{passive:false});
	})();
	</script>
	<?php
} );
