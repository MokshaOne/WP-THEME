<?php
/**
 * Template Name: Booking confirmed
 *
 * Styled landing shown after an enquiry / booking is submitted.
 * Reads optional booking_code / status query params when present,
 * but does not depend on them.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'booking';
get_header();

$booking_code = isset( $_GET['booking_code'] ) ? sanitize_text_field( $_GET['booking_code'] ) : '';
$status       = isset( $_GET['status'] )       ? sanitize_text_field( $_GET['status'] )       : '';
$ok           = $status === '' || in_array( $status, [ 'confirmed', 'approved', 'paid', 'pending_approval' ], true );

$title  = $ok
	? nr_opt( 'nr_booking_confirmed_title', __( "We're <em>set.</em>", 'raveenthiran' ) )
	: nr_opt( 'nr_booking_failed_title',    __( "Something's <em>amiss.</em>", 'raveenthiran' ) );
$body   = $ok
	? nr_opt( 'nr_booking_confirmed_body',  __( "I'll be in touch within 24 hours to confirm the brief, location, and lighting. If anything urgent comes up before then, email me directly.", 'raveenthiran' ) )
	: nr_opt( 'nr_booking_failed_body',     __( "Your booking didn't complete. Try again, or email me — I'll sort it out by hand.", 'raveenthiran' ) );
?>
<section class="nr-page nr-confirmed">
	<div class="nr-confirmed__panel">
		<span class="nr-eyebrow"><?php echo esc_html( $ok ? __( 'Booking received', 'raveenthiran' ) : __( 'Booking incomplete', 'raveenthiran' ) ); ?></span>
		<h1 class="nr-display nr-display--lg"><?php echo wp_kses( $title, [ 'em' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		<p class="nr-confirmed__body"><?php echo esc_html( $body ); ?></p>

		<?php if ( $booking_code ) : ?>
			<dl class="nr-confirmed__meta">
				<div>
					<dt><?php esc_html_e( 'Booking reference', 'raveenthiran' ); ?></dt>
					<dd><?php echo esc_html( $booking_code ); ?></dd>
				</div>
				<?php if ( $status ) : ?>
				<div>
					<dt><?php esc_html_e( 'Status', 'raveenthiran' ); ?></dt>
					<dd><?php echo esc_html( ucfirst( str_replace( '_', ' ', $status ) ) ); ?></dd>
				</div>
				<?php endif; ?>
			</dl>
		<?php endif; ?>

		<div class="nr-confirmed__actions">
			<a class="nr-btn" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ?: home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Browse the work', 'raveenthiran' ); ?></span> <span>→</span></a>
			<a class="nr-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back home', 'raveenthiran' ); ?></span></a>
		</div>
	</div>
</section>
<?php get_footer(); ?>
