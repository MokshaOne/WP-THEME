<?php
/**
 * Cal.com popup trigger. Rendered via nr_cal_button() / [nr_cal_button].
 * Args: link (calLink), label.
 * A real <button>; cal-embed.js preloads embed.js on hover/focus and opens the
 * Cal modal on click. <noscript> falls back to the hosted booking page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_link  = isset( $args['link'] ) ? (string) $args['link'] : '';
$nr_label = isset( $args['label'] ) && $args['label'] !== '' ? (string) $args['label'] : __( 'Book a time', 'raveenthiran' );
if ( $nr_link === '' ) return;
?>
<span class="nr-cal-cta">
	<button type="button" class="nr-btn nr-btn--primary" data-nr-cal-popup data-cal-link="<?php echo esc_attr( $nr_link ); ?>">
		<span><?php echo esc_html( $nr_label ); ?></span> <span aria-hidden="true">→</span>
	</button>
	<noscript>
		<a class="nr-btn" href="<?php echo esc_url( nr_cal_origin() . '/' . ltrim( $nr_link, '/' ) ); ?>" target="_blank" rel="noopener">
			<span><?php echo esc_html( $nr_label ); ?></span> <span aria-hidden="true">↗</span>
		</a>
	</noscript>
</span>
