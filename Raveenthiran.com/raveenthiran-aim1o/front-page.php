<?php
/**
 * aim1o front page — the "genius" hero. A full-bleed, art-directed AI image
 * (Higgsfield) carries the opening panel: obsidian ground, a single gilt light,
 * the wordmark set into its negative space, a live spotlight + parallax over
 * it. Then the featured specimen and the system readout ride the same
 * horizontal filmstrip. Content comes from the shared nr_* engine.
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
$total   = $feat_id ? 3 : 2;

/* the AI hero image — priority: a pasted URL (Theme Settings → Home hero
   image), else a bundled file at assets/img/aim1o-hero.jpg, else nothing:
   the panel then falls back to a designed obsidian + gilt-light hero. */
$hero_url = trim( (string) nr_opt( 'nr_home_hero', '' ) );
$hero_img = '';
if ( $hero_url !== '' ) {
	$hero_img = esc_url( $hero_url );
} elseif ( file_exists( get_template_directory() . '/assets/img/aim1o-hero.jpg' ) ) {
	$hero_img = get_template_directory_uri() . '/assets/img/aim1o-hero.jpg';
}
$hero_cls = $hero_img ? 'aim-hero--art' : 'aim-hero--void';
?>

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<?php /* ── the genius hero ──────────────────────────────── */ ?>
		<section class="mk-panel aim-hero <?php echo esc_attr( $hero_cls ); ?>" data-aim-hero>
			<span class="mk-panel__index">01 / <?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<div class="aim-hero__bg"<?php echo $hero_img ? ' style="background-image:url(\'' . esc_url( $hero_img ) . '\')"' : ''; ?> aria-hidden="true"></div>
			<div class="aim-hero__scrim" aria-hidden="true"></div>
			<div class="aim-hero__light" aria-hidden="true"></div>
			<div class="aim-hero__in">
				<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( $status ); ?></p>
				<h1 class="void-display-hero aim-hero__title" style="text-align:left">
					<?php echo esc_html( $hero1 ); ?><br>
					<span class="void-gold-ital"><?php echo esc_html( $hero2 ); ?></span>
				</h1>
				<div class="aim-hero__cta">
					<a href="<?php echo esc_url( $arc_url ); ?>" class="void-btn void-btn-gold"><?php echo esc_html( nr_opt( 'nr_void_hero_cta', __( 'ENTER THE ARCHIVE', 'raveenthiran' ) ) ); ?> →</a>
					<span class="aim-hero__coords void-mono"><?php echo esc_html( $lat . ' / ' . $long ); ?></span>
				</div>
			</div>
		</section>

		<?php /* ── featured specimen ───────────────────────────── */ ?>
		<?php if ( $feat_id ) : $m = function_exists( 'nr_project_meta' ) ? nr_project_meta( $feat_id ) : []; ?>
		<section class="mk-panel">
			<span class="mk-panel__index">02 / <?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<div class="mk-work">
				<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" class="mk-work__frame">
					<?php
					if ( function_exists( 'nr_image_or_placeholder' ) ) {
						nr_image_or_placeholder( $feat_id, 'nr-card', get_the_title( $feat_id ) );
					} elseif ( has_post_thumbnail( $feat_id ) ) {
						echo get_the_post_thumbnail( $feat_id, 'nr-card', [ 'alt' => get_the_title( $feat_id ) ] );
					}
					?>
				</a>
				<div class="mk-work__meta">
					<span class="void-label-luxury"><?php esc_html_e( 'FEATURED_SPECIMEN', 'raveenthiran' ); ?></span>
					<h2 class="mk-work__title"><a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" style="color:inherit"><?php echo esc_html( get_the_title( $feat_id ) ); ?></a></h2>
					<?php if ( ! empty( $m['cat'] ) ) : ?><span class="void-mono"><?php echo esc_html( $m['cat'] . ( ! empty( $m['yr'] ) ? ' · ' . $m['yr'] : '' ) ); ?></span><?php endif; ?>
					<a href="<?php echo esc_url( get_permalink( $feat_id ) ); ?>" class="void-btn void-btn-outline" style="margin-top:22px;align-self:flex-start"><?php esc_html_e( 'OPEN SPECIMEN', 'raveenthiran' ); ?> →</a>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php /* ── system readout + enter ──────────────────────── */ ?>
		<section class="mk-panel">
			<span class="mk-panel__index"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) . ' / ' . str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'SYSTEM READOUT', 'raveenthiran' ); ?></p>
			<div class="void-home-stats" style="position:static;margin:26px 0 0">
				<div class="void-stat void-stat-gold">
					<span class="void-label-luxury"><?php esc_html_e( 'CORE_TEMP', 'raveenthiran' ); ?></span>
					<span class="void-stat-value" id="void-core-temp">2.912K</span>
				</div>
				<div class="void-stat">
					<span class="void-label-luxury"><?php esc_html_e( 'VECTOR_LOC', 'raveenthiran' ); ?></span>
					<span class="void-stat-sub"><?php echo esc_html( $lat . ' / ' . $long ); ?></span>
				</div>
			</div>
			<div class="void-home-cta" style="position:static;margin-top:34px">
				<a href="<?php echo esc_url( $arc_url ); ?>" class="void-btn void-btn-outline void-btn-wide"><?php echo esc_html( nr_opt( 'nr_void_hero_cta', __( 'ENTER THE ARCHIVE', 'raveenthiran' ) ) ); ?></a>
			</div>
		</section>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
