<?php
/**
 * Newsletter — plain and self-hosted. A sign-up simply emails the studio the
 * new subscriber's address through the site's own wp_mail (i.e. the Mail /
 * SMTP settings under Theme Settings). No third-party service, no API keys.
 *
 * Render it with the [nr_newsletter] shortcode or nr_newsletter_form(); it also
 * appears in the footer when "Show signup in footer" is on (ACF Newsletter).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Where sign-ups are delivered — the Newsletter field, else the studio email. */
function nr_newsletter_to() {
	$to = function_exists( 'get_field' ) ? trim( (string) get_field( 'newsletter_notify', 'option' ) ) : '';
	if ( $to === '' || ! is_email( $to ) ) $to = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	return $to;
}

/** Render the sign-up form. $compact = single-line variant (footer). */
function nr_newsletter_form( $compact = false ) {
	$heading = function_exists( 'get_field' ) ? (string) get_field( 'newsletter_heading', 'option' ) : '';
	$note    = function_exists( 'get_field' ) ? (string) get_field( 'newsletter_note', 'option' ) : '';
	$heading = $heading !== '' ? $heading : __( 'Stay in the loop', 'raveenthiran' );

	ob_start(); ?>
	<form class="nr-news<?php echo $compact ? ' nr-news--compact' : ''; ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="nr_newsletter_subscribe">
		<?php wp_nonce_field( 'nr_newsletter', '_nr_nl' ); ?>
		<input type="hidden" name="nr_ref" value="<?php echo esc_attr( home_url( add_query_arg( [] ) ) ); ?>">
		<!-- honeypot -->
		<input type="text" name="nr_company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="nr-hp" style="position:absolute;left:-9999px">
		<?php if ( ! $compact ) : ?>
			<span class="void-label-luxury nr-news__h"><?php echo esc_html( $heading ); ?></span>
			<?php if ( $note ) : ?><p class="nr-news__note"><?php echo esc_html( $note ); ?></p><?php endif; ?>
		<?php endif; ?>
		<div class="nr-news__row">
			<label class="screen-reader-text" for="nr-news-email"><?php esc_html_e( 'Email address', 'raveenthiran' ); ?></label>
			<input id="nr-news-email" type="email" name="nr_news_email" required inputmode="email" autocomplete="email" placeholder="<?php echo $compact ? esc_attr( $heading ) : esc_attr__( 'you@studio.com', 'raveenthiran' ); ?>">
			<button type="submit" class="void-btn void-btn-gold"><?php echo $compact ? '→' : esc_html__( 'Subscribe', 'raveenthiran' ) . ' →'; ?></button>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'nr_newsletter', function () { return nr_newsletter_form( false ); } );

/** Handle the sign-up: validate, then email the studio via wp_mail (SMTP). */
function nr_newsletter_handle() {
	$back = wp_get_referer() ?: home_url( '/' );

	if ( ! isset( $_POST['_nr_nl'] ) || ! wp_verify_nonce( $_POST['_nr_nl'], 'nr_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'nr_nl', '0', $back ) ); exit;
	}
	if ( ! empty( $_POST['nr_company'] ) ) {                 // honeypot tripped
		wp_safe_redirect( add_query_arg( 'nr_nl', '1', $back ) ); exit; // pretend success
	}
	$email = isset( $_POST['nr_news_email'] ) ? sanitize_email( wp_unslash( $_POST['nr_news_email'] ) ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'nr_nl', '0', $back ) ); exit;
	}

	$site    = get_bloginfo( 'name' );
	$to      = nr_newsletter_to();
	$subject = sprintf( __( '[%s] New newsletter subscriber', 'raveenthiran' ), $site );
	$body    = sprintf(
		"%s\n\n%s: %s\n%s: %s\n",
		__( 'A visitor subscribed to the newsletter.', 'raveenthiran' ),
		__( 'Email', 'raveenthiran' ), $email,
		__( 'From page', 'raveenthiran' ), esc_url_raw( wp_unslash( $_POST['nr_ref'] ?? $back ) )
	);
	$headers = [ 'Reply-To: ' . $email ];               // reply straight to the subscriber
	$ok = wp_mail( $to, $subject, $body, $headers );    // → routed through the SMTP settings

	wp_safe_redirect( add_query_arg( 'nr_nl', $ok ? '1' : '0', $back ) );
	exit;
}
add_action( 'admin_post_nr_newsletter_subscribe', 'nr_newsletter_handle' );
add_action( 'admin_post_nopriv_nr_newsletter_subscribe', 'nr_newsletter_handle' );
