<?php
/**
 * Booking — embed a Google Calendar **Appointment Schedule** (booking page) via
 * the [nr_booking] shortcode. Click-to-load: the external Google iframe is only
 * injected after the visitor clicks, so there is NO third-party request and no
 * LCP/CLS/privacy hit on page load (DSGVO-friendly). Off until a URL is set in
 * Theme Settings → Booking; falls back to the enquiry form when empty.
 *
 * The owner pastes Google's "embed" iframe code OR the booking-page URL; we
 * extract + validate the src ourselves (only Google Calendar hosts allowed).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pull a safe Google-Calendar booking URL out of the stored embed code or URL.
 * Returns '' if nothing valid is set.
 */
function nr_booking_src() {
	$raw = trim( (string) nr_opt( 'nr_booking_embed', '' ) );
	if ( $raw === '' ) return '';
	// Accept a full <iframe … src="…"> blob or a bare URL.
	if ( preg_match( '/src\s*=\s*["\']([^"\']+)["\']/i', $raw, $m ) ) {
		$url = $m[1];
	} else {
		$url = $raw;
	}
	$url  = html_entity_decode( $url, ENT_QUOTES );
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$ok   = [ 'calendar.google.com', 'calendar.app.google' ];
	if ( ! in_array( $host, $ok, true ) ) return '';
	return esc_url_raw( $url );
}

/**
 * [nr_booking] — Obscura-framed, click-to-load Google booking embed.
 * Attributes: title, intro, cta, height.
 */
add_shortcode( 'nr_booking', function ( $atts ) {
	$src = nr_booking_src();
	$a   = shortcode_atts( [
		'title'  => __( 'Book a slot', 'raveenthiran' ),
		'intro'  => __( 'Pick a time that suits you — you’ll get a confirmation and a reminder by email.', 'raveenthiran' ),
		'cta'    => __( 'Open the calendar', 'raveenthiran' ),
		'height' => '640',
	], $atts, 'nr_booking' );

	$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire/' );

	ob_start(); ?>
	<div class="nr-booking" data-booking<?php echo $src ? ' data-src="' . esc_attr( $src ) . '" data-h="' . (int) $a['height'] . '"' : ''; ?>>
		<div class="nr-booking__intro">
			<span class="nr-eyebrow nr-eyebrow--plain"><?php echo esc_html( $a['title'] ); ?></span>
			<?php if ( $a['intro'] ) : ?><p><?php echo esc_html( $a['intro'] ); ?></p><?php endif; ?>
		</div>
		<?php if ( $src ) : ?>
			<button type="button" class="nr-btn nr-btn--primary" data-booking-open>
				<span><?php echo esc_html( $a['cta'] ); ?></span> <span>→</span>
			</button>
			<noscript><a class="nr-btn" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( $a['cta'] ); ?></span></a></noscript>
			<p class="nr-booking__note"><?php esc_html_e( 'Opens Google Calendar. Prefer to write? ', 'raveenthiran' ); ?><a href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'Send an enquiry instead', 'raveenthiran' ); ?></a>.</p>
		<?php else : ?>
			<a class="nr-btn" href="<?php echo esc_url( $enquire ); ?>"><span><?php esc_html_e( 'Send an enquiry', 'raveenthiran' ); ?></span> <span>→</span></a>
			<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="nr-admin-hint"><?php esc_html_e( 'No booking calendar set. Add a Google Appointment Schedule link in Obscura → Settings → Booking.', 'raveenthiran' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
	// Print the click-to-load script once, only when a bookable embed is on the page.
	static $printed = false;
	if ( $src && ! $printed ) {
		$printed = true; ?>
		<script>
		(function () {
			document.querySelectorAll('[data-booking][data-src]').forEach(function (box) {
				var btn = box.querySelector('[data-booking-open]');
				if (!btn) return;
				btn.addEventListener('click', function () {
					var f = document.createElement('iframe');
					f.src = box.getAttribute('data-src');
					f.title = 'Booking calendar';
					f.loading = 'lazy';
					f.setAttribute('frameborder', '0');
					f.className = 'nr-booking__frame';
					f.style.height = (parseInt(box.getAttribute('data-h'), 10) || 640) + 'px';
					box.classList.add('is-loaded');
					btn.replaceWith(f);
				}, { once: true });
			});
		})();
		</script>
		<?php
	}
	return ob_get_clean();
} );
