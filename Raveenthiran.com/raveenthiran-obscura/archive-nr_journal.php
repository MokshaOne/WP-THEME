<?php
/**
 * Journal archive — Studio: scrolling editorial grid + category filter.
 * Keeps the theme.js filter hooks (.nr-chip / [data-portfolio] .nr-card).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();

$nr_jcats = get_terms( [ 'taxonomy' => 'nr_journal_cat', 'hide_empty' => true ] );
?>
<section class="st-page nr-journal-archive">
	<header class="st-wrap st-arc-head">
		<div class="st-arc-head__text">
			<span class="st-eyebrow"><?php echo esc_html( nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ); ?></span>
			<h1 class="st-arc-title"><?php echo wp_kses( nr_opt( 'nr_journal_title', __( 'Notes &amp; <em>field work</em>', 'raveenthiran' ) ), [ 'em' => [], 'b' => [], 'strong' => [] ] ); ?></h1>
		</div>
		<?php if ( ! is_wp_error( $nr_jcats ) && count( $nr_jcats ) > 1 ) : ?>
			<div class="st-filters">
				<div class="nr-chips" data-group="main" role="tablist" aria-label="<?php esc_attr_e( 'Filter journal', 'raveenthiran' ); ?>">
					<button type="button" class="nr-chip is-on" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'All', 'raveenthiran' ); ?></button>
					<?php foreach ( $nr_jcats as $c ) : ?>
						<button type="button" class="nr-chip" data-filter="<?php echo esc_attr( $c->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $c->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</header>

	<div class="st-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="st-journal-grid st-grid--journal" data-portfolio data-reveal-stagger>
				<?php while ( have_posts() ) : the_post();
					$terms   = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'slugs' ] );
					$tn      = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
					$catname = ( ! is_wp_error( $tn ) && $tn ) ? $tn[0] : __( 'Journal', 'raveenthiran' );
				?>
					<a class="nr-card st-jcard" href="<?php the_permalink(); ?>" data-cats="<?php echo esc_attr( implode( ' ', $terms ?: [] ) ); ?>">
						<div class="st-jcard__frame">
							<?php if ( has_post_thumbnail() ) :
								echo get_the_post_thumbnail( get_the_ID(), 'nr-card', [ 'alt' => get_the_title(), 'loading' => 'lazy', 'decoding' => 'async', 'class' => 'st-jcard__img', 'sizes' => '(max-width:700px) 100vw, 32vw' ] );
							else :
								echo nr_placeholder( strtolower( get_the_title() ), false, '4/3' );
							endif; ?>
						</div>
						<span class="st-jcard__meta"><?php echo esc_html( get_the_date( 'M Y' ) . ' · ' . $catname ); ?></span>
						<h2 class="st-jcard__t"><?php the_title(); ?></h2>
						<p class="st-jcard__x"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						<span class="st-link st-jcard__more"><?php esc_html_e( 'Read', 'raveenthiran' ); ?> →</span>
					</a>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => __( '← Newer', 'raveenthiran' ), 'next_text' => __( 'Older →', 'raveenthiran' ), 'class' => 'st-pager' ] ); ?>
		<?php else : ?>
			<p class="st-muted st-empty"><?php esc_html_e( 'No journal entries yet.', 'raveenthiran' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
