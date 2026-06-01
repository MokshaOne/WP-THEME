<?php
/**
 * Journal archive — scrollable list of entries (#60).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();
?>
<section class="nr-page nr-journal-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo wp_kses( nr_opt( 'nr_journal_title', __( 'Notes &amp; <em>field work.</em>', 'raveenthiran' ) ), [ 'em' => [], 'b' => [], 'strong' => [] ] ); ?></h1>
		</div>
	</div>

	<div class="nr-journal__list">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<a class="nr-journal__entry" href="<?php the_permalink(); ?>">
				<div class="nr-journal__media">
					<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'nr-card', [ 'loading' => 'lazy', 'decoding' => 'async' ] );
					elseif ( function_exists( 'nr_placeholder' ) ) : echo nr_placeholder( strtolower( get_the_title() ), true, '4/5' ); endif; ?>
				</div>
				<div class="nr-journal__text">
					<span class="nr-journal__date"><?php echo esc_html( get_the_date() ); ?></span>
					<h2 class="nr-journal__entry-title"><?php the_title(); ?></h2>
					<p class="nr-journal__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					<span class="nr-journal__more"><?php esc_html_e( 'Read', 'raveenthiran' ); ?> →</span>
				</div>
			</a>
		<?php endwhile;
			the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] );
		else : ?>
			<p class="nr-journal__empty"><?php esc_html_e( 'No journal entries yet.', 'raveenthiran' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
