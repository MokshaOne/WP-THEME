<?php
/**
 * Journal single — now a HORIZONTAL slider: lead image + title, the article in
 * a readable column (which scrolls vertically inside its own panel so long
 * reads stay comfortable), then a closing CTA. Vertical wheel travels sideways
 * on desktop (mk-scroll.js); natural vertical stack on touch.
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

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<?php /* ── lead ────────────────────────────────────────── */ ?>
		<section class="mk-panel mk-panel--intro mk-jlead">
			<span class="mk-panel__index">01 / 03</span>
			<div class="mk-work" style="grid-template-columns:auto min(46ch,46vw)">
				<?php if ( has_post_thumbnail() ) : ?>
				<figure class="mk-work__frame">
					<?php echo get_the_post_thumbnail( get_the_ID(), 'nr-hero', [ 'alt' => get_the_title(), 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ] ); ?>
				</figure>
				<?php endif; ?>
				<div class="mk-work__meta">
					<a class="void-mono mk-jlead__back" href="<?php echo esc_url( $nr_jurl ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran' ); ?></a>
					<span class="void-label-luxury" style="margin-top:14px"><?php echo esc_html( ( $nr_cat ?: nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ) . ' · ' . get_the_date() ); ?></span>
					<h1 class="mk-work__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?><p class="void-mono" style="text-transform:none;letter-spacing:0;line-height:1.6;color:var(--void-ink-2)"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>

		<?php /* ── article (inner vertical scroll) ─────────────── */ ?>
		<section class="mk-panel mk-article">
			<span class="mk-panel__index">02 / 03</span>
			<div class="mk-article__col">
				<div class="st-prose void-prose"><?php the_content(); ?></div>
			</div>
		</section>

		<?php /* ── closing ─────────────────────────────────────── */ ?>
		<section class="mk-panel mk-panel--intro">
			<span class="mk-panel__index">03 / 03</span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'NEXT', 'raveenthiran' ); ?></p>
			<h2 class="mk-work__title" style="max-width:18ch;margin-top:8px"><?php esc_html_e( 'Keep reading, or start a project.', 'raveenthiran' ); ?></h2>
			<div class="mk-contact__actions" style="margin-top:24px">
				<a class="void-btn void-btn-outline" href="<?php echo esc_url( $nr_jurl ); ?>"><?php esc_html_e( 'ALL ENTRIES', 'raveenthiran' ); ?></a>
				<a class="void-btn void-btn-gold" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><?php esc_html_e( 'START A PROJECT', 'raveenthiran' ); ?> →</a>
			</div>
		</section>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
