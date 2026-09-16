<?php
/**
 * ACF polyfills.
 *
 * Loaded first by functions.php so ported modules that read ACF
 * fields (inc/seo.php, parts/*) keep working when Advanced Custom
 * Fields isn't installed. Each polyfill is guarded by function_exists
 * — when ACF is active its real functions take precedence and these
 * stubs are never defined.
 *
 * The fallback for option-pages maps get_field('foo', 'option') to
 * get_option('options_foo'), matching the convention used by
 * inc/admin-panel.php when it persists settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'get_field' ) ) {
	function get_field( $selector, $post_id = false, $format_value = true ) {
		if ( $post_id === 'option' || $post_id === 'options' ) {
			$v = get_option( 'options_' . $selector, null );
			return $v === false ? null : $v;
		}
		$pid = $post_id ?: get_the_ID();
		if ( ! $pid ) return null;
		$v = get_post_meta( $pid, $selector, true );
		return $v === '' ? null : $v;
	}
}

if ( ! function_exists( 'the_field' ) ) {
	function the_field( $selector, $post_id = false, $format_value = true ) {
		$v = get_field( $selector, $post_id, $format_value );
		echo is_scalar( $v ) ? esc_html( $v ) : '';
	}
}

if ( ! function_exists( 'update_field' ) ) {
	function update_field( $selector, $value, $post_id = false ) {
		if ( $post_id === 'option' || $post_id === 'options' ) {
			return update_option( 'options_' . $selector, $value );
		}
		$pid = $post_id ?: get_the_ID();
		return $pid ? update_post_meta( $pid, $selector, $value ) : false;
	}
}

if ( ! function_exists( 'have_rows' ) ) {
	function have_rows( $selector, $post_id = false ) { return false; }
}

if ( ! function_exists( 'the_row' ) ) {
	function the_row( $format = false ) { return null; }
}

if ( ! function_exists( 'get_sub_field' ) ) {
	function get_sub_field( $selector, $format_value = true ) { return null; }
}

if ( ! function_exists( 'the_sub_field' ) ) {
	function the_sub_field( $selector, $format_value = true ) {}
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	function acf_add_local_field_group( $field_group ) { return false; }
}

if ( ! function_exists( 'acf_add_options_page' ) ) {
	function acf_add_options_page( $page = '' ) { return false; }
}
