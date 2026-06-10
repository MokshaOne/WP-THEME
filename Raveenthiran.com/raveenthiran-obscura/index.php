<?php
/**
 * Generic fallback — any archive/blog index without its own template.
 * Renders the same card rail as the portfolio/journal for visual consistency.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();
$nr_total = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
?>
<section class="nr-page nr-fullscreen nr-portfolio-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php
				if ( is_search() ) { printf( esc_html__( 'Search · %d', 'raveenthiran' ), $nr_total ); }
				elseif ( is_archive() ) { echo esc_html( get_the_archive_title() ); }
				else { esc_html_e( 'Index', 'raveenthiran' ); }
			?></span>
			<h1 class="nr-display nr-display--lg"><?php
				if ( is_search() ) { echo '&ldquo;' . esc_html( get_search_query() ) . '&rdquo;'; }
				elseif ( is_archive() ) { the_archive_title(); }
				else { esc_html_e( 'Latest', 'raveenthiran' ); }
			?></h1>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="nr-portfolio-rail" data-h-rail>
			<?php $i = 0; while ( have_posts() ) : the_post();
				$is_j  = get_post_type() === 'nr_journal';
				$pto   = get_post_type_object( get_post_type() );
				$label = $pto && isset( $pto->labels->singular_name ) ? $pto->labels->singular_name : ''; ?>
				<a class="nr-card is-portrait<?php echo $is_j ? ' nr-card--journal' : ''; ?>" href="<?php the_permalink(); ?>" style="--i:<?php echo (int) $i; ?>">
					<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true, $i === 0 ); ?>
					<div class="nr-card__shade"></div>
					<div class="nr-card__head"><span><?php echo esc_html( $label ); ?></span><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span></div>
					<div class="nr-card__foot">
						<span class="nr-card__title"><?php the_title(); ?></span>
					</div>
				</a>
			<?php $i++; endwhile; ?>
		</div>
	<?php else : ?>
		<div class="nr-portfolio-rail"><p class="nr-journal__empty"><?php esc_html_e( 'Nothing here yet.', 'raveenthiran' ); ?></p></div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
