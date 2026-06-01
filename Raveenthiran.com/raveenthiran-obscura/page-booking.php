<?php
/**
 * Template Name: Booking
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'booking';
get_header();

$booking_url = nr_opt( 'nr_booking_url', '' );
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-page__head">
		<div>
			<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_booking_eyebrow', __( 'Booking · §04', 'raveenthiran' ) ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo wp_kses( nr_opt( 'nr_booking_title', __( 'Book a <em>session.</em>', 'raveenthiran' ) ), [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		</div>
		<span class="nr-eyebrow nr-eyebrow--right"><?php echo esc_html( nr_opt( 'nr_avail_text', 'Currently booking · Q3/Q4 2026' ) ); ?></span>
	</div>

	<div class="nr-booking">

		<div class="nr-booking__steps">
			<ol class="nr-steps">
				<?php $steps = [
					[ '01', nr_opt( 'nr_step_1_t', __( 'Signal',          'raveenthiran' ) ), nr_opt( 'nr_step_1_d', __( 'Fill in your project details and preferred shoot date.', 'raveenthiran' ) ) ],
					[ '02', nr_opt( 'nr_step_2_t', __( 'Briefing',        'raveenthiran' ) ), nr_opt( 'nr_step_2_d', __( 'A short call to align on vision, location, and logistics.',     'raveenthiran' ) ) ],
					[ '03', nr_opt( 'nr_step_3_t', __( 'Shoot & deliver', 'raveenthiran' ) ), nr_opt( 'nr_step_3_d', __( 'We meet, we create. Edits delivered within 5–7 business days.', 'raveenthiran' ) ) ],
				];
				foreach ( $steps as $s ) : ?>
					<li>
						<span class="nr-steps__n"><?php echo esc_html( $s[0] ); ?></span>
						<div>
							<span class="nr-steps__t"><?php echo esc_html( $s[1] ); ?></span>
							<span class="nr-steps__d"><?php echo esc_html( $s[2] ); ?></span>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>

			<dl class="nr-contact__info nr-booking__info">
				<div><dt><?php esc_html_e( 'Email', 'raveenthiran' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( nr_opt( 'nr_email', 'studio@raveenthiran.at' ) ); ?>"><?php echo esc_html( nr_opt( 'nr_email', 'studio@raveenthiran.at' ) ); ?></a></dd></div>
				<div><dt><?php esc_html_e( 'Response', 'raveenthiran' ); ?></dt><dd>&lt; 24h</dd></div>
				<div><dt><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></dt><dd><?php echo esc_html( nr_opt( 'nr_location', 'Wien, Austria' ) ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Travel', 'raveenthiran' ); ?></dt><dd><?php esc_html_e( 'Available worldwide', 'raveenthiran' ); ?></dd></div>
			</dl>
		</div>

		<div class="nr-booking__panel">
			<?php if ( $booking_url && filter_var( $booking_url, FILTER_VALIDATE_URL ) ) : ?>
				<iframe class="nr-booking__iframe" src="<?php echo esc_url( $booking_url ); ?>" loading="lazy" title="<?php esc_attr_e( 'Booking calendar', 'raveenthiran' ); ?>"></iframe>
			<?php else : ?>
				<form class="nr-quick-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="nr_contact_send">
					<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
					<label>
						<span class="nr-eyebrow"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
						<input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Your full name', 'raveenthiran' ); ?>">
					</label>
					<label>
						<span class="nr-eyebrow"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
						<input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required placeholder="<?php esc_attr_e( 'you@example.com', 'raveenthiran' ); ?>">
					</label>
					<label>
						<span class="nr-eyebrow"><?php esc_html_e( 'Project brief', 'raveenthiran' ); ?></span>
						<textarea name="notes" rows="4" autocomplete="off" placeholder="<?php esc_attr_e( 'A few sentences — a place, a person, an hour of the day.', 'raveenthiran' ); ?>"></textarea>
					</label>
					<button type="submit" class="nr-btn nr-btn--primary nr-btn--block">
						<?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send brief', 'raveenthiran' ) ) ); ?>
						<svg width="12" height="12" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</form>
			<?php endif; ?>
		</div>

	</div>
</section>
<?php get_footer(); ?>
