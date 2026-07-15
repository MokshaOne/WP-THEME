<?php
/**
 * VOID front page — the Singularity. Orbiting core, centred display wordmark,
 * one featured specimen plate (from nr_project), and live HUD stats.
 * Content comes from the shared nr_* engine; falls back gracefully when empty.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'home';
get_header();

/* featured specimen — first homepage-featured nr_project, else most recent */
$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ], 'no_found_rows' => true ] );
if ( ! $q->have_posts() ) {
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ], 'no_found_rows' => true ] );
}
$feat_id = $q->have_posts() ? (int) $q->posts[0]->ID : 0;
wp_reset_postdata();

$hero1   = nr_opt( 'nr_void_hero1', 'SILENT' );
$hero2   = nr_opt( 'nr_void_hero2', 'LIGHT' );
$status  = nr_opt( 'nr_void_status', 'STATUS: ARCHIVE_ONLINE' );
$lat     = nr_opt( 'nr_void_lat', '48.2082° N' );
$long    = nr_opt( 'nr_void_long', '16.3738° E' );
$arc_url = get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' );
?>

<section class="void-screen void-home">

	<div class="void-home-orb" aria-hidden="true">
		<div class="void-orb-ring"></div>
		<div class="void-orb-core"></div>
	</div>

	<div class="void-home-center">
		<p class="void-eyebrow void-eyebrow-center"><span class="void-rule"></span><?php echo esc_html( $status ); ?><span class="void-rule"></span></p>
		<h1 class="void-display-hero">
			<?php echo esc_html( $hero1 ); ?><br>
			<span class="void-gold-ital"><?php echo esc_html( $hero2 ); ?></span>
		</h1>
	</div>

	<?php if ( $feat_id ) : $m = function_exists( 'nr_project_meta' ) ? nr_project_meta( $feat_id ) : []; ?>
	<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" class="void-home-plate">
		<div class="void-home-plate-img">
			<?php
			if ( function_exists( 'nr_image_or_placeholder' ) ) {
				nr_image_or_placeholder( $feat_id, 'nr-card', get_the_title( $feat_id ) );
			} elseif ( has_post_thumbnail( $feat_id ) ) {
				echo get_the_post_thumbnail( $feat_id, 'nr-card', [ 'alt' => get_the_title( $feat_id ) ] );
			}
			?>
		</div>
		<div class="void-home-plate-cap">
			<span class="void-label-luxury"><?php esc_html_e( 'FEATURED_SPECIMEN', 'raveenthiran' ); ?></span>
			<span class="void-home-plate-title"><?php echo esc_html( get_the_title( $feat_id ) ); ?></span>
			<?php if ( ! empty( $m['cat'] ) ) : ?><span class="void-mono"><?php echo esc_html( $m['cat'] . ( ! empty( $m['yr'] ) ? ' · ' . $m['yr'] : '' ) ); ?></span><?php endif; ?>
		</div>
	</a>
	<?php endif; ?>

	<div class="void-home-stats">
		<div class="void-stat void-stat-gold">
			<span class="void-label-luxury"><?php esc_html_e( 'CORE_TEMP', 'raveenthiran' ); ?></span>
			<span class="void-stat-value" id="void-core-temp">2.912K</span>
		</div>
		<div class="void-stat">
			<span class="void-label-luxury"><?php esc_html_e( 'VECTOR_LOC', 'raveenthiran' ); ?></span>
			<span class="void-stat-sub"><?php echo esc_html( $lat . ' / ' . $long ); ?></span>
		</div>
	</div>

	<div class="void-home-cta">
		<a href="<?php echo esc_url( $arc_url ); ?>" class="void-btn void-btn-outline void-btn-wide"><?php echo esc_html( nr_opt( 'nr_void_hero_cta', __( 'ENTER THE ARCHIVE', 'raveenthiran' ) ) ); ?></a>
	</div>

</section>

<?php get_footer(); ?>
