<?php
/**
 * Journal single — fixed two-pane layout (image + scrollable article),
 * mirroring the single-project / about pages so it fits the no-scroll model.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();
the_post();

$nr_cat = '';
$nr_tn  = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
if ( ! is_wp_error( $nr_tn ) && $nr_tn ) $nr_cat = $nr_tn[0];
$nr_jurl = function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' );
?>
<section class="nr-jpost">

	<div class="nr-jpost__media">
		<?php if ( has_post_thumbnail() ) :
			echo wp_get_attachment_image( get_post_thumbnail_id(), 'nr-hero', false, [
				'alt'           => get_the_title(),
				'sizes'         => '(max-width:900px) 100vw, 44vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
			] );
		elseif ( function_exists( 'nr_placeholder' ) ) :
			echo nr_placeholder( strtolower( get_the_title() ), true, 'auto' );
		endif; ?>
		<div class="nr-jpost__media-foot">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<?php if ( $nr_cat ) : ?><span><?php echo esc_html( $nr_cat ); ?></span><?php endif; ?>
		</div>
	</div>

	<div class="nr-jpost__body">
		<div class="nr-jpost__inner">
			<a class="nr-jpost__back" href="<?php echo esc_url( $nr_jurl ); ?>"><span>←</span> <span><?php esc_html_e( 'Journal', 'raveenthiran' ); ?></span></a>
			<span class="nr-eyebrow"><?php echo esc_html( $nr_cat ?: nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ); ?></span>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="nr-jpost__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>

			<div class="nr-jpost__prose"><?php the_content(); ?></div>

			<?php
			// #20/#37 — "More notes": related journal entries (same category, else recent).
			$nr_rel_args = [
				'post_type'      => 'nr_journal',
				'posts_per_page' => 2,
				'post__not_in'   => [ get_the_ID() ],
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			];
			$nr_terms = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $nr_terms ) && $nr_terms ) {
				$nr_rel_args['tax_query'] = [ [ 'taxonomy' => 'nr_journal_cat', 'field' => 'term_id', 'terms' => $nr_terms ] ];
			}
			$nr_rel = get_posts( $nr_rel_args );
			if ( count( $nr_rel ) < 2 ) { // top up with recent entries
				$nr_rel = get_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => 2, 'post__not_in' => [ get_the_ID() ], 'no_found_rows' => true ] );
			}
			if ( $nr_rel ) : ?>
				<div class="nr-jpost__more">
					<span class="nr-eyebrow nr-eyebrow--xs"><?php esc_html_e( 'More notes', 'raveenthiran' ); ?></span>
					<div class="nr-jpost__more-grid">
						<?php foreach ( $nr_rel as $rp ) : ?>
							<a class="nr-jpost__more-card" href="<?php echo esc_url( get_permalink( $rp ) ); ?>">
								<span class="nr-jpost__more-date"><?php echo esc_html( get_the_date( 'M Y', $rp ) ); ?></span>
								<span class="nr-jpost__more-title"><?php echo esc_html( get_the_title( $rp ) ); ?></span>
							</a>
						<?php endforeach; ?>
						<a class="nr-jpost__more-all" href="<?php echo esc_url( $nr_jurl ); ?>"><?php esc_html_e( 'All entries', 'raveenthiran' ); ?> <span aria-hidden="true">↗</span></a>
					</div>
				</div>
			<?php endif; ?>

			<div class="nr-jpost__foot">
				<a class="nr-btn" href="<?php echo esc_url( $nr_jurl ); ?>"><span><?php esc_html_e( 'All entries', 'raveenthiran' ); ?></span></a>
				<a class="nr-btn nr-btn--primary" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><span><?php esc_html_e( 'Start a project', 'raveenthiran' ); ?></span> <span>→</span></a>
			</div>
		</div>
	</div>

</section>
<?php get_footer(); ?>
