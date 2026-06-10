<?php
/**
 * inc/lib.php — pure, WordPress-free helpers (unit-testable; loaded first).
 * Keep anything here free of WP calls so tests/php can require it directly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* AES-256-CBC blob: base64( iv[16] . ciphertext ), key = sha256(passphrase). */
if ( ! function_exists( 'nr_encrypt_blob' ) ) {
	function nr_encrypt_blob( $data, $pass ) {
		if ( ! function_exists( 'openssl_encrypt' ) ) return '';
		$iv = openssl_random_pseudo_bytes( 16 );
		$ct = openssl_encrypt( $data, 'aes-256-cbc', hash( 'sha256', $pass, true ), OPENSSL_RAW_DATA, $iv );
		return $ct === false ? '' : base64_encode( $iv . $ct );
	}
}
if ( ! function_exists( 'nr_decrypt_blob' ) ) {
	function nr_decrypt_blob( $b64, $pass ) {
		$raw = base64_decode( (string) $b64, true );
		if ( $raw === false || strlen( $raw ) <= 16 ) return '';
		$iv = substr( $raw, 0, 16 ); $ct = substr( $raw, 16 );
		$pt = openssl_decrypt( $ct, 'aes-256-cbc', hash( 'sha256', $pass, true ), OPENSSL_RAW_DATA, $iv );
		return $pt === false ? '' : $pt;
	}
}

/* Split "a | b | c" lines into trimmed arrays (skips blank lines). */
if ( ! function_exists( 'nr_parse_pipe_lines' ) ) {
	function nr_parse_pipe_lines( $raw, $limit = 0 ) {
		$out = [];
		foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $raw ) ) ) as $line ) {
			$out[] = array_map( 'trim', explode( '|', $line ) );
			if ( $limit && count( $out ) >= $limit ) break;
		}
		return $out;
	}
}

/* Validate an IndexNow-style key (8–128 hex). */
if ( ! function_exists( 'nr_is_hex_key' ) ) {
	function nr_is_hex_key( $v ) {
		$k = preg_replace( '/[^a-f0-9]/i', '', (string) $v );
		return ( strlen( $k ) >= 8 && strlen( $k ) <= 128 ) ? $k : '';
	}
}
