<?php
/**
 * Booking — surface a Google Calendar **Appointment Schedule** via [nr_booking].
 *
 * Two modes from one setting (Theme Settings → Booking embed):
 *   • A real EMBED url (…/appointments/schedules/…?gv=true, from Google's
 *     "embed on website") → click-to-load INLINE iframe.
 *   • A SHARE short link (calendar.app.google/…) → a one-click button that
 *     OPENS the booking page in a new tab (Google blocks framing short links).
 *
 * Either way it is click-to-load: no third-party request, no LCP/CLS/privacy
 * hit on page load (DSGVO-friendly). Falls back to the enquiry form when unset.
 * We extract + validate the URL ourselves (only Google Calendar hosts allowed).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Safe Google-Calendar booking URL from the stored embed code / link, or ''. */
function nr_booking_src() {
	$raw = trim( (string) nr_opt( 'nr_booking_embed', '' ) );
	if ( $raw === '' ) return '';
	if ( preg_match( '/src\s*=\s*["\']([^"\']+)["\']/i', $raw, $m ) ) {
		$url = $m[1];
	} else {
		$url = $raw;
	}
	$url  = html_entity_decode( $url, ENT_QUOTES );
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( ! in_array( $host, [ 'calendar.google.com', 'calendar.app.google' ], true ) ) return '';
	return esc_url_raw( $url );
}

/** True when the URL is a real embeddable schedule (vs a share short link). */
function nr_booking_embeddable( $url ) {
	return $url && ( strpos( $url, '/appointments/schedules/' ) !== false || strpos( $url, 'gv=true' ) !== false );
}

/**
 * [nr_booking] — Obscura-framed, click-to-load Google booking.
 * Attributes: title, intro, cta, height.
 */
add_shortcode( 'nr_booking', function ( $atts ) {
	$src        = nr_booking_src();
	$embeddable = nr_booking_embeddable( $src );
	$a          = shortcode_atts( [
		'title'  => __( 'Book a slot', 'raveenthiran' ),
		'intro'  => __( 'Pick a time that suits you — you’ll get a confirmation and a reminder by email.', 'raveenthiran' ),
		'cta'    => __( 'Open the calendar', 'raveenthiran' ),
		'height' => '640',
	], $atts, 'nr_booking' );

	$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire/' );

	ob_start(); ?>
	<div class="nr-booking" data-booking<?php echo ( $src && $embeddable ) ? ' data-src="' . esc_attr( $src ) . '" data-h="' . (int) $a['height'] . '"' : ''; ?>>
		<div class="nr-booking__intro">
			<span class="nr-eyebrow nr-eyebrow--plain"><?php echo esc_html( $a['title'] ); ?></span>
			<?php if ( $a['intro'] ) : ?><p><?php echo esc_html( $a['intro'] ); ?></p><?php endif; ?>
		</div>

		<?php if ( $src && $embeddable ) : // inline iframe (click-to-load) ?>
			<button type="button" class="nr-btn nr-btn--primary" data-booking-open>
				<span><?php echo esc_html( $a['cta'] ); ?></span> <span>→</span>
			</button>
			<noscript><a class="nr-btn" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( $a['cta'] ); ?></span></a></noscript>
			<p class="nr-booking__note"><?php esc_html_e( 'Prefer to write? ', 'raveenthiran' ); ?><a href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'Send an enquiry instead', 'raveenthiran' ); ?></a>.</p>

		<?php elseif ( $src ) : // share short link → open in a new tab ?>
			<a class="nr-btn nr-btn--primary" href="<?php echo esc_url( $src ); ?>" target="_blank" rel="noopener">
				<span><?php echo esc_html( $a['cta'] ); ?></span> <span>↗</span>
			</a>
			<p class="nr-booking__note"><?php esc_html_e( 'Opens the booking calendar in a new tab. Prefer to write? ', 'raveenthiran' ); ?><a href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'Send an enquiry instead', 'raveenthiran' ); ?></a>.</p>

		<?php else : // nothing set → enquiry fallback ?>
			<a class="nr-btn" href="<?php echo esc_url( $enquire ); ?>"><span><?php esc_html_e( 'Send an enquiry', 'raveenthiran' ); ?></span> <span>→</span></a>
			<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="nr-admin-hint"><?php esc_html_e( 'No booking calendar set. Add a Google Appointment Schedule link in Obscura → Settings → Booking.', 'raveenthiran' ); ?></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
	// Click-to-load script — printed once, only when an inline embed is on the page.
	static $printed = false;
	if ( $src && $embeddable && ! $printed ) {
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
