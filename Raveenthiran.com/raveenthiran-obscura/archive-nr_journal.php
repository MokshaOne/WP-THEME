<?php
/**
 * Journal archive — horizontal card rail (mirrors the portfolio: cards,
 * desktop prev/next arrows via theme.js, category chips).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();

$nr_jcats = get_terms( [ 'taxonomy' => 'nr_journal_cat', 'hide_empty' => true ] );

/** Orientation from the featured image (defaults to landscape for journal). */
$nr_j_orient = function ( $post_id ) {
	$tid = get_post_thumbnail_id( $post_id );
	if ( ! $tid ) return 'landscape';
	$m = wp_get_attachment_metadata( $tid );
	if ( ! is_array( $m ) || empty( $m['width'] ) || empty( $m['height'] ) ) return 'landscape';
	return ( (int) $m['height'] > (int) $m['width'] ) ? 'portrait' : 'landscape';
};
?>
<section class="nr-page nr-fullscreen nr-journal-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo wp_kses( nr_opt( 'nr_journal_title', __( 'Notes &amp; <em>field work.</em>', 'raveenthiran' ) ), [ 'em' => [], 'b' => [], 'strong' => [] ] ); ?></h1>
		</div>

		<?php if ( ! is_wp_error( $nr_jcats ) && count( $nr_jcats ) > 1 ) : ?>
			<div class="nr-chips nr-chips--center" data-group="main" role="tablist" aria-label="<?php esc_attr_e( 'Filter journal', 'raveenthiran' ); ?>">
				<button type="button" class="nr-chip is-on" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'All', 'raveenthiran' ); ?></button>
				<?php foreach ( $nr_jcats as $c ) : ?>
					<button type="button" class="nr-chip" data-filter="<?php echo esc_attr( $c->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $c->name ); ?><sup class="nr-chip__n"><?php echo (int) $c->count; ?></sup></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="nr-portfolio-rail nr-journal-rail" data-portfolio data-h-rail>
			<?php $i = 0; while ( have_posts() ) : the_post();
				$terms   = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'slugs' ] );
				$catname = '';
				$tn = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
				if ( ! is_wp_error( $tn ) && $tn ) $catname = $tn[0];
				$orient = $nr_j_orient( get_the_ID() );
			?>
				<a class="nr-card nr-card--journal is-<?php echo esc_attr( $orient ); ?>" href="<?php the_permalink(); ?>" data-cats="<?php echo esc_attr( implode( ' ', $terms ?: [] ) ); ?>" style="--i:<?php echo (int) $i; ?>">
					<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true, $i === 0 ); ?>
					<div class="nr-card__shade"></div>
					<div class="nr-card__head">
						<span><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
						<?php if ( $catname ) : ?><span><?php echo esc_html( $catname ); ?></span><?php endif; ?>
					</div>
					<div class="nr-card__foot">
						<span class="nr-card__cat"><?php esc_html_e( 'Journal', 'raveenthiran' ); ?></span>
						<span class="nr-card__title<?php echo ( $i % 3 === 1 ) ? ' is-em' : ''; ?>"><?php the_title(); ?></span>
						<span class="nr-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></span>
					</div>
				</a>
			<?php $i++; endwhile; ?>
		</div>
	<?php else : ?>
		<div class="nr-portfolio-rail nr-journal-rail">
			<p class="nr-journal__empty"><?php esc_html_e( 'No journal entries yet.', 'raveenthiran' ); ?></p>
		</div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
