<?php
/**
 * Booking modal — desktop dialog + mobile sheet.
 * Trigger via [data-modal="nr-booking"] on any link/button.
 * If LatePoint is installed, embeds its calendar; otherwise renders
 * the inline fallback form.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$booking_url = nr_opt( 'nr_booking_url', '' );
?>
<div id="nr-booking" class="nr-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="nr-booking-title">

	<div class="nr-modal__panel">
		<button type="button" class="nr-modal__close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'raveenthiran' ); ?>">✕</button>

		<div class="nr-modal__intro">
			<span class="nr-eyebrow"><?php esc_html_e( 'Ready when you are', 'raveenthiran' ); ?></span>
			<h2 id="nr-booking-title"><?php esc_html_e( 'Book a', 'raveenthiran' ); ?> <em><?php esc_html_e( 'session.', 'raveenthiran' ); ?></em></h2>
			<p><?php esc_html_e( "Select your preferred service, choose a date, and let's create something together. Typical response within 24 hours.", 'raveenthiran' ); ?></p>

			<ul>
				<?php
				$steps = [
					nr_opt( 'nr_step_1_t', __( 'Signal',          'raveenthiran' ) ),
					nr_opt( 'nr_step_2_t', __( 'Briefing',        'raveenthiran' ) ),
					nr_opt( 'nr_step_3_t', __( 'Shoot & deliver', 'raveenthiran' ) ),
				];
				foreach ( $steps as $i => $t ) {
					printf( '<li><b>0%d.</b>&nbsp;%s</li>', $i + 1, esc_html( $t ) );
				}
				?>
			</ul>

			<p class="nr-modal__foot">
				<?php
				printf(
					/* translators: 1: response window, 2: location */
					esc_html__( 'Response &lt; 24h · %1$s · Available for travel', 'raveenthiran' ),
					esc_html( nr_opt( 'nr_location', 'Wien' ) )
				);
				?>
			</p>
		</div>

		<div class="nr-modal__host">
			<?php
			$nr_latepoint = nr_latepoint_embed( [
				'caption' => __( 'Open booking calendar', 'raveenthiran' ),
			] );
			?>
			<?php if ( $booking_url && filter_var( $booking_url, FILTER_VALIDATE_URL ) ) : ?>
				<iframe src="<?php echo esc_url( $booking_url ); ?>" loading="lazy" title="<?php esc_attr_e( 'Booking calendar', 'raveenthiran' ); ?>"></iframe>
			<?php elseif ( $nr_latepoint !== '' ) : ?>
				<div class="nr-latepoint-mount"><?php echo $nr_latepoint; ?></div>
			<?php else : ?>
				<form class="nr-quick-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="nr_contact_send">
					<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>

					<label>
						<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
						<input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Your full name', 'raveenthiran' ); ?>">
					</label>
					<label>
						<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
						<input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required placeholder="<?php esc_attr_e( 'you@example.com', 'raveenthiran' ); ?>">
					</label>
					<label>
						<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Project brief', 'raveenthiran' ); ?></span>
						<textarea name="notes" rows="3" placeholder="<?php esc_attr_e( 'A few sentences — a place, a person, an hour of the day.', 'raveenthiran' ); ?>"></textarea>
					</label>

					<button type="submit" class="nr-btn nr-btn--primary nr-btn--block">
						<span><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send brief', 'raveenthiran' ) ) ); ?></span> <span>→</span>
					</button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>
