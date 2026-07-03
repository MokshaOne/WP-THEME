<?php
/**
 * Silence — enquiry form handler.
 * Nonce + honeypot + rate limit + optional Turnstile. Recomputes the
 * calculator estimate server-side (never trusts the client), saves a
 * private nr_enquiry post, mails the studio a breakdown, and answers
 * the visitor with an auto-reply carrying the branded PDF estimate
 * (inc/pdf.php) when a price was calculated. No plugins.
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
	$date    = sanitize_text_field( wp_unslash( $_POST['shoot_date'] ?? '' ) );
	if ( ! $name || ! is_email( $email ) || ! $message ) $fail();

	/* ── Recompute the estimate from the trusted pricing config ── */
	$type_label = '';
	$est        = 0.0;
	$breakdown  = [];
	if ( function_exists( 'nr_quote_data' ) && ! empty( $_POST['quote_type'] ) ) {
		$quote     = nr_quote_data();
		$type_slug = sanitize_title( wp_unslash( $_POST['quote_type'] ) );
		foreach ( $quote['types'] as $t ) {
			if ( $t['slug'] === $type_slug ) {
				$type_label  = $t['label'];
				$est        += $t['base'];
				$breakdown[] = sprintf( '%s — %s%s', $t['label'], $quote['currency'], number_format_i18n( $t['base'] ) );
				break;
			}
		}
		if ( $type_label ) {
			$picked = array_map( 'sanitize_title', (array) ( $_POST['quote_extras'] ?? [] ) );
			foreach ( $quote['extras'] as $x ) {
				if ( in_array( $x['slug'], $picked, true ) ) {
					$est        += $x['price'];
					$breakdown[] = sprintf( '%s — +%s%s', $x['label'], $quote['currency'], number_format_i18n( $x['price'] ) );
				}
			}
			$km = max( 0, min( 5000, (int) ( $_POST['quote_km'] ?? 0 ) ) );
			if ( $km > 0 && $quote['per_km'] > 0 ) {
				$travel      = $km * $quote['per_km'];
				$est        += $travel;
				$breakdown[] = sprintf( __( 'Travel %d km — +%s%s', 'raveenthiran-silence' ), $km, $quote['currency'], number_format_i18n( round( $travel ) ) );
			}
		}
	}
	$est_str = $type_label ? nr_opt( 'nr_currency', '€' ) . number_format_i18n( round( $est ) ) : '';

	/* ── Log as a private enquiry (nr_enquiry — shared with Obscura) ── */
	$body_lines = [ "From: {$name} <{$email}>" ];
	if ( $date )       $body_lines[] = __( 'Preferred date:', 'raveenthiran-silence' ) . ' ' . $date;
	if ( $type_label ) $body_lines[] = __( 'Estimate:', 'raveenthiran-silence' ) . ' ' . $est_str . ' (' . implode( ' · ', $breakdown ) . ')';
	$body_lines[] = '';
	$body_lines[] = $message;

	$enquiry_id = wp_insert_post( [
		'post_type'    => 'nr_enquiry',
		'post_status'  => 'private',
		'post_title'   => $name . ' — ' . wp_date( 'Y-m-d H:i' ) . ( $est_str ? ' · ' . $est_str : '' ),
		'post_content' => implode( "\n", $body_lines ),
	] );
	if ( $enquiry_id && ! is_wp_error( $enquiry_id ) ) {
		update_post_meta( $enquiry_id, '_nr_enquiry_estimate', $est_str );
		update_post_meta( $enquiry_id, '_nr_enquiry_type', $type_label );
	}

	/* ── Mail the studio ── */
	$to = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	wp_mail(
		$to,
		sprintf( '[%s] %s%s', wp_parse_url( home_url(), PHP_URL_HOST ), __( 'New enquiry', 'raveenthiran-silence' ), $est_str ? ' · ' . $est_str : '' ),
		implode( "\n", $body_lines ),
		[ 'Reply-To: ' . $name . ' <' . $email . '>' ]
	);

	/* ── Auto-reply to the visitor (+ PDF estimate when calculated) ── */
	$studio_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$reply       = sprintf( __( "Hello %s,\n\nthank you for your enquiry — it arrived safely. I read every message personally and reply within a day.", 'raveenthiran-silence' ), $name );
	$attachments = [];
	$pdf_path    = '';
	if ( $est_str && function_exists( 'nr_quote_pdf_file' ) ) {
		$pdf_path = nr_quote_pdf_file( [
			'name'     => $name,
			'type'     => $type_label,
			'date'     => $date ?: date_i18n( 'j M Y' ),
			'estimate' => $est_str,
			'ref'      => $enquiry_id && ! is_wp_error( $enquiry_id ) ? 'ENQ-' . $enquiry_id : '',
		] );
		if ( $pdf_path ) {
			$attachments[] = $pdf_path;
			$reply .= "\n\n" . __( 'Attached you will find a non-binding PDF estimate based on the details you selected.', 'raveenthiran-silence' );
		}
	}
	$reply .= "\n\n— " . $studio_name;
	wp_mail( $email, sprintf( __( 'Your enquiry — %s', 'raveenthiran-silence' ), $studio_name ), $reply, [], $attachments );
	if ( $pdf_path ) @unlink( $pdf_path );

	wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
	exit;
} );
