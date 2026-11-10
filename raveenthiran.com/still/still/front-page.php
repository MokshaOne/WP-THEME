<?php
/**
 * Front page — the cinematic home. After the intro (in header.php), this is a
 * vertical snap-scroll of TEASER panels, one per menu item. Each panel links
 * out to its real, full page; it never replaces it. Content is pulled live so
 * the Work teaser always shows the newest projects.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_items = still_nav_items();
$still_cta   = array(
	'work'    => __( 'Open portfolio →', 'still' ),
	'studio'  => __( 'Read more →', 'still' ),
	'journal' => __( 'Latest entries →', 'still' ),
	'contact' => __( 'Get in touch →', 'still' ),
	'enquire' => __( 'Start a project →', 'still' ),
);
$still_i = 0;
?>
<div id="home">
<?php foreach ( $still_items as $it ) : $still_i++; ?>
	<section class="panel" id="panel-<?php echo esc_attr( $it['key'] ); ?>" data-key="<?php echo esc_attr( $it['key'] ); ?>">
		<span class="panel__ghost" aria-hidden="true"><?php echo esc_html( still_pad( $still_i ) ); ?></span>
		<span class="panel__idx"><?php echo esc_html( still_pad( $still_i ) . ' — ' . $it['label'] ); ?></span>
		<h2 class="panel__name"><?php echo esc_html( $it['label'] ); ?><?php echo 'enquire' === $it['key'] ? '<em>.</em>' : ''; ?></h2>
		<div class="panel__row">
			<p class="panel__desc"><?php echo esc_html( $it['desc'] ); ?></p>
			<a class="panel__go" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $still_cta[ $it['key'] ] ?? __( 'Open →', 'still' ) ); ?></a>
		</div>

		<?php if ( 'work' === $it['key'] ) :
			$still_q = new WP_Query( array(
				'post_type'      => 'work',
				'posts_per_page' => 4,
				'no_found_rows'  => true,
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			) );
			if ( $still_q->have_posts() ) : ?>
			<div class="thumbs">
				<?php while ( $still_q->have_posts() ) : $still_q->the_post(); ?>
					<a href="<?php the_permalink(); ?>"><span class="frame"><?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'still-card', array( 'alt' => esc_attr( get_the_title() ) ) );
						} else {
							echo '<span class="ph">' . esc_html__( 'Photo', 'still' ) . '</span>';
						}
					?></span></a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<?php endif;
		endif; ?>

		<?php if ( 'journal' === $it['key'] ) :
			$still_j = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true ) );
			if ( $still_j->have_posts() ) : ?>
			<div class="thumbs" style="display:block;margin-top:2rem">
				<?php while ( $still_j->have_posts() ) : $still_j->the_post(); ?>
					<a href="<?php the_permalink(); ?>" style="display:block;width:auto;margin:.3rem 0;color:var(--mut)"><span class="label" style="color:inherit"><?php echo esc_html( get_the_date() ); ?></span> &nbsp; <?php the_title(); ?></a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<?php endif;
		endif; ?>

		<?php if ( 'enquire' === $it['key'] ) : ?>
			<div class="mini-stage" aria-hidden="true"><div class="cube"><f></f><f></f><f></f><f></f><f></f><f></f></div></div>
		<?php endif; ?>
	</section>
<?php endforeach; ?>
</div>
<?php get_footer(); ?>
