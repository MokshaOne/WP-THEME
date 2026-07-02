<?php
/**
 * SMTP delivery (e.g. Google Workspace).
 *
 * Routes wp_mail() through an authenticated SMTP server so enquiries and
 * auto-replies actually arrive. Configured under Theme Settings → § Mail (SMTP).
 *
 * Google Workspace note: authenticate with the REAL mailbox (e.g. hq@m1o.at)
 * using a 16-char App Password; the "From" may be any verified "Send mail as"
 * address on that account (e.g. office@raveenthiran.com). The password is kept
 * out of the kses-filtered settings bag and can also be supplied via the
 * SL_SMTP_PASS constant in wp-config.php for extra safety.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Register the password as a saveable option in the theme settings group,
   without HTML filtering, and preserve it when the field is submitted blank. */
add_action( 'admin_init', function () {
	register_setting( 'sl_settings_group', 'sl_smtp_pass', [
		'type'              => 'string',
		'sanitize_callback' => 'sl_smtp_sanitize_pass',
		'default'           => '',
	] );
} );

function sl_smtp_sanitize_pass( $val ) {
	$val = is_string( $val ) ? trim( $val ) : '';
	// Blank submission = keep the existing stored password (masked field).
	if ( $val === '' ) return (string) get_option( 'sl_smtp_pass', '' );
	return $val;
}

function sl_smtp_password() {
	if ( defined( 'SL_SMTP_PASS' ) && SL_SMTP_PASS ) return SL_SMTP_PASS;
	return (string) get_option( 'sl_smtp_pass', '' );
}

/* Configure PHPMailer for outgoing mail. */
add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( sl_opt( 'sl_smtp_enable', '0' ) !== '1' ) return;

	$host = trim( (string) sl_opt( 'sl_smtp_host', '' ) );
	$user = trim( (string) sl_opt( 'sl_smtp_user', '' ) );
	$pass = sl_smtp_password();
	if ( $host === '' || $user === '' || $pass === '' ) return; // not configured → leave default mail()

	$phpmailer->isSMTP();
	$phpmailer->Host       = $host;
	$phpmailer->Port       = (int) ( sl_opt( 'sl_smtp_port', '587' ) ?: 587 );
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Username   = $user;
	$phpmailer->Password   = $pass;
	$phpmailer->SMTPSecure = sl_opt( 'sl_smtp_secure', 'tls' ) === 'ssl' ? 'ssl' : 'tls';

	$from = trim( (string) sl_opt( 'sl_smtp_from', '' ) );
	if ( $from === '' || ! is_email( $from ) ) $from = $user;
	$name = (string) ( sl_opt( 'sl_smtp_fromname', '' ) ?: get_bloginfo( 'name' ) );
	try {
		$phpmailer->setFrom( $from, $name, false );
		$phpmailer->Sender = $from; // envelope-from for SPF alignment
	} catch ( \Exception $e ) {
		// ignore — keep whatever From WP already set
	}
}, 20 );

/* Keep the visible From in sync when SMTP is on (before phpmailer_init). */
add_filter( 'wp_mail_from', function ( $email ) {
	if ( sl_opt( 'sl_smtp_enable', '0' ) !== '1' ) return $email;
	$from = trim( (string) sl_opt( 'sl_smtp_from', '' ) );
	return ( $from && is_email( $from ) ) ? $from : $email;
}, 20 );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( sl_opt( 'sl_smtp_enable', '0' ) !== '1' ) return $name;
	$n = trim( (string) sl_opt( 'sl_smtp_fromname', '' ) );
	return $n ?: $name;
}, 20 );

/* "Send a test email to me" button handler. */
add_action( 'admin_post_sl_smtp_test', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'sl_smtp_test' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'raveenthiran-silence' ) );
	}
	$to  = wp_get_current_user()->user_email;
	$ok  = false;
	if ( $to && is_email( $to ) ) {
		$ok = wp_mail(
			$to,
			sprintf( __( 'SMTP test — %s', 'raveenthiran-silence' ), get_bloginfo( 'name' ) ),
			__( "This is a test email from your site.\n\nIf you received it, SMTP is working.", 'raveenthiran-silence' ),
			[ 'Content-Type: text/plain; charset=UTF-8' ]
		);
	}
	wp_safe_redirect( add_query_arg(
		[ 'page' => 'sl-settings', 'sl_smtp_test' => $ok ? '1' : '0' ],
		admin_url( 'themes.php' )
	) . '#mail' );
	exit;
} );
