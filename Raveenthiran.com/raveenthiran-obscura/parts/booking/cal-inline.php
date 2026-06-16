<?php
/**
 * Cal.com inline embed. Rendered via nr_cal_inline() / [nr_cal_inline].
 * Args: link (calLink, e.g. "raveenthiran/portrait"), label.
 * The container reserves min-height (cal-booking.css) → no layout shift; the
 * actual calendar is mounted by assets/js/cal-embed.js when scrolled near.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_link  = isset( $args['link'] ) ? (string) $args['link'] : '';
$nr_label = isset( $args['label'] ) ? (string) $args['label'] : '';
if ( $nr_link === '' ) return;
?>
<div class="nr-cal-inline"
	data-nr-cal-inline
	data-cal-link="<?php echo esc_attr( $nr_link ); ?>"
	data-loading="<?php esc_attr_e( 'Loading calendar…', 'raveenthiran' ); ?>"
	role="region"
	aria-label="<?php echo esc_attr( $nr_label ? sprintf( __( 'Book: %s', 'raveenthiran' ), $nr_label ) : __( 'Booking calendar', 'raveenthiran' ) ); ?>">
	<noscript>
		<a class="nr-btn" href="<?php echo esc_url( nr_cal_origin() . '/' . ltrim( $nr_link, '/' ) ); ?>" target="_blank" rel="noopener">
			<span><?php esc_html_e( 'Open the booking calendar', 'raveenthiran' ); ?></span> <span>↗</span>
		</a>
	</noscript>
</div>
