<?php
/**
 * Search results — mixed projects/journal/pages as a card rail.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();

$nr_q     = get_search_query();
$nr_total = (int) $GLOBALS['wp_query']->found_posts;
?>
<section class="nr-page nr-fullscreen nr-portfolio-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php echo esc_html( sprintf( _n( 'Search · %d result', 'Search · %d results', $nr_total, 'raveenthiran' ), $nr_total ) ); ?></span>
			<h1 class="nr-display nr-display--lg">&ldquo;<?php echo esc_html( $nr_q ); ?>&rdquo;</h1>
			<form class="nr-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" name="s" value="<?php echo esc_attr( $nr_q ); ?>" placeholder="<?php esc_attr_e( 'Search projects & notes…', 'raveenthiran' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'raveenthiran' ); ?>">
				<button type="submit" class="nr-btn"><span><?php esc_html_e( 'Search', 'raveenthiran' ); ?></span></button>
			</form>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="nr-portfolio-rail" data-h-rail>
			<?php $i = 0; while ( have_posts() ) : the_post();
				$type_label = get_post_type() === 'nr_project' ? __( 'Work', 'raveenthiran' )
					: ( get_post_type() === 'nr_journal' ? __( 'Journal', 'raveenthiran' ) : __( 'Page', 'raveenthiran' ) ); ?>
				<a class="nr-card is-portrait<?php echo get_post_type() === 'nr_journal' ? ' nr-card--journal' : ''; ?>" href="<?php the_permalink(); ?>" style="--i:<?php echo (int) $i; ?>">
					<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true, $i === 0 ); ?>
					<div class="nr-card__shade"></div>
					<div class="nr-card__head"><span><?php echo esc_html( $type_label ); ?></span><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span></div>
					<div class="nr-card__foot">
						<span class="nr-card__cat"><?php echo esc_html( $type_label ); ?></span>
						<span class="nr-card__title"><?php the_title(); ?></span>
					</div>
				</a>
			<?php $i++; endwhile; ?>
		</div>
	<?php else : ?>
		<div class="nr-portfolio-rail">
			<p class="nr-journal__empty"><?php esc_html_e( 'No results — try a different word, or browse the portfolio.', 'raveenthiran' ); ?></p>
				<?php if ( function_exists( 'nr_random_testimonial_markup' ) ) echo nr_random_testimonial_markup(); // #158 ?>
		</div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
