<?php
/**
 * #74 — Cloudflare Turnstile on the enquiry form.
 *
 * A privacy-friendly, no-puzzle CAPTCHA that fits this site's all-Cloudflare
 * setup. Add the Site key + Secret key under Theme Settings → § Security.
 * When both are present:
 *   • the widget renders on the Enquire form (above the submit button), and
 *   • sl_handle_contact_send() (functions.php) calls sl_turnstile_passes()
 *     to verify the token server-side before processing.
 * When the keys are empty the form behaves exactly as before — the existing
 * honeypot + per-IP rate-limit still apply, so nothing breaks without setup.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* The two keys (sl_turnstile_site / sl_turnstile_secret) are declared in
   sl_settings_defaults() so they're whitelisted, saveable, and read by sl_opt(). */

function sl_turnstile_keys() {
	return [
		'site'   => trim( (string) sl_opt( 'sl_turnstile_site', '' ) ),
		'secret' => trim( (string) sl_opt( 'sl_turnstile_secret', '' ) ),
	];
}
function sl_turnstile_enabled() {
	$k = sl_turnstile_keys();
	return $k['site'] !== '' && $k['secret'] !== '';
}

/* Load the Turnstile API only on pages that actually carry the form. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! sl_turnstile_enabled() ) return;
	if ( ! ( is_page_template( 'page-enquire.php' ) || is_page() ) ) return;
	wp_enqueue_script(
		'cf-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js',
		[],
		null,
		true
	);
} );

/**
 * Echo the widget. Called from page-enquire.php inside the form.
 * Uses the site's accent for the (dark) theme to match the noir palette.
 */
function sl_turnstile_field() {
	if ( ! sl_turnstile_enabled() ) return;
	$k = sl_turnstile_keys();
	printf(
		'<div class="cf-turnstile sl-turnstile" data-sitekey="%s" data-theme="dark" data-size="flexible"></div>',
		esc_attr( $k['site'] )
	);
}

/**
 * Server-side verification. Returns true when Turnstile is NOT configured
 * (so the form keeps working) or when the token verifies successfully.
 */
function sl_turnstile_passes() {
	if ( ! sl_turnstile_enabled() ) return true;

	$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
	if ( $token === '' ) return false;

	$k  = sl_turnstile_keys();
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	$res = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
		'timeout' => 8,
		'body'    => [
			'secret'   => $k['secret'],
			'response' => $token,
			'remoteip' => $ip,
		],
	] );
	if ( is_wp_error( $res ) ) return true; // fail open on network error so genuine users aren't blocked
	$data = json_decode( wp_remote_retrieve_body( $res ), true );
	return ! empty( $data['success'] );
}
