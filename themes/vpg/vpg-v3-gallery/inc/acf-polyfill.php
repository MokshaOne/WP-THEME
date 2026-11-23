<?php
/**
 * VPG v3 — ACF polyfills.
 *
 * Loaded FIRST by functions.php so everything that reads ACF fields
 * (vpg_field(), inc/seo.php, the CPT templates) keeps working whether or
 * not Advanced Custom Fields is installed. Each stub is guarded by
 * function_exists — when ACF is active its real functions win and these
 * are never defined.
 *
 * Field VALUES are plain post meta, so the site renders identically with
 * or without the plugin; installing ACF (free) simply adds the editor UI
 * defined in inc/acf-fields.php. Option-page reads map
 * get_field('foo','option') → get_option('options_foo').
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

/* Is the real ACF plugin active? (used by the admin panel to show status) */
if ( ! function_exists( 'vpg_acf_active' ) ) {
    function vpg_acf_active() {
        return class_exists( 'ACF' ) || function_exists( 'acf_get_setting' );
    }
}
