<?php
/**
 * moksha1one — Journal / Chronicle (HORIZONTAL scroll). Intro panel → one
 * panel per entry → closing panel. Vertical wheel drives the horizontal
 * track (mk-scroll.js); native swipe on touch. Data via nr_journal.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();

$count = (int) ( wp_count_posts( 'nr_journal' )->publish ?? 0 );
?>

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<section class="mk-panel mk-panel--intro">
			<span class="mk-panel__index">00 / <?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( nr_opt( 'nr_journal_eyebrow', 'CHRONICLE' ) ); ?></p>
			<h1 class="void-display-hero" style="text-align:left"><?php echo esc_html( nr_opt( 'nr_journal_title', 'FIELD NOTES' ) ); ?></h1>
			<p class="void-mono" style="margin-top:18px;max-width:46ch"><?php echo esc_html( nr_opt( 'nr_journal_intro', 'Notes from the field — travel sideways through the chronicle.' ) ); ?></p>
		</section>

		<?php if ( have_posts() ) : $i = 0; while ( have_posts() ) : the_post(); $i++;
			$jcat = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
			$jcat = ( ! is_wp_error( $jcat ) && $jcat ) ? $jcat[0] : __( 'Journal', 'raveenthiran' );
		?>
			<section class="mk-panel">
				<span class="mk-panel__index"><?php echo esc_html( str_pad( (string) $i, 2, '0', STR_PAD_LEFT ) ); ?> / <?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<a class="mk-work" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
					<div class="mk-work__frame">
						<?php
						if ( has_post_thumbnail() ) the_post_thumbnail( 'nr-card', [ 'alt' => get_the_title() ] );
						elseif ( function_exists( 'nr_placeholder' ) ) echo nr_placeholder( strtolower( get_the_title() ), false, '4/5' );
						?>
					</div>
					<div class="mk-work__meta">
						<span class="void-label-luxury"><?php echo esc_html( get_the_date( 'M Y' ) . ' · ' . strtoupper( $jcat ) ); ?></span>
						<h2 class="mk-work__title"><?php the_title(); ?></h2>
						<p class="void-mono" style="text-transform:none;letter-spacing:0;line-height:1.6;color:var(--void-ink-2)"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
						<span class="void-btn void-btn-outline" style="margin-top:24px"><?php esc_html_e( 'READ ENTRY', 'raveenthiran' ); ?> →</span>
					</div>
				</a>
			</section>
		<?php endwhile; else : ?>
			<section class="mk-panel"><p class="void-empty"><?php esc_html_e( 'No journal entries yet.', 'raveenthiran' ); ?></p></section>
		<?php endif; ?>

		<section class="mk-panel mk-panel--intro">
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'END OF CHRONICLE', 'raveenthiran' ); ?></p>
			<h2 class="void-h1-huge void-h1-uncap"><?php esc_html_e( 'Let\'s make', 'raveenthiran' ); ?><br><span class="void-gold-ital"><?php esc_html_e( 'the next one.', 'raveenthiran' ); ?></span></h2>
			<a class="void-btn void-btn-gold" style="margin-top:28px;align-self:flex-start" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><?php esc_html_e( 'INITIATE ENQUIRY', 'raveenthiran' ); ?> →</a>
		</section>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
