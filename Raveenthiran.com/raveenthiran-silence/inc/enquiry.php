<?php
/**
 * Silence — enquiry form handler.
 * Nonce + honeypot + rate limit, saves a private nr_enquiry post,
 * mails the studio. No plugins.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'template_redirect', function () {
	if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset( $_POST['nr_enquiry_nonce'] ) ) return;

	$fail = function () {
		wp_safe_redirect( add_query_arg( 'sent', '0', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	};

	if ( ! wp_verify_nonce( $_POST['nr_enquiry_nonce'], 'nr_enquiry' ) ) $fail();
	// Honeypot — real visitors never fill "website".
	if ( ! empty( $_POST['website'] ) ) $fail();
	// Turnstile (only when keys are configured — see inc/security.php).
	if ( function_exists( 'nr_turnstile_passes' ) && ! nr_turnstile_passes() ) $fail();
	// Rate limit — one submission per IP per minute.
	$ip_key = 'nr_enq_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
	if ( get_transient( $ip_key ) ) $fail();
	set_transient( $ip_key, 1, MINUTE_IN_SECONDS );

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) || ! $message ) $fail();

	wp_insert_post( [
		'post_type'    => 'nr_enquiry',
		'post_status'  => 'private',
		'post_title'   => $name . ' — ' . wp_date( 'Y-m-d H:i' ),
		'post_content' => "From: {$name} <{$email}>\n\n{$message}",
	] );

	$to = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	wp_mail(
		$to,
		sprintf( '[%s] %s', wp_parse_url( home_url(), PHP_URL_HOST ), __( 'New enquiry', 'raveenthiran-silence' ) ),
		"{$name}\n{$email}\n\n{$message}",
		[ 'Reply-To: ' . $name . ' <' . $email . '>' ]
	);

	wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
} );
