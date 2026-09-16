<?php
/**
 * VPG v3 — performance.
 *
 *   - WebP delivery · newly uploaded JPEG/PNG sub-sizes are generated as
 *     WebP (WP 6.1+ image_editor_output_format); originals stay untouched
 *   - Lazy media defaults · loading=lazy + decoding=async on attachment
 *     images that don't set their own
 *   - Emoji payload removed · the wp-emoji script/styles cost ~15 KB on
 *     every page and the theme never uses them
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── WebP sub-sizes for new uploads ────────────────────────────── */
add_filter( 'image_editor_output_format', function ( $formats ) {
    $formats['image/jpeg'] = 'image/webp';
    $formats['image/png']  = 'image/webp';
    return $formats;
} );

/* ─── Cap giant originals · 2560px is plenty for a 1440 layout ──── */
add_filter( 'big_image_size_threshold', function () { return 2560; } );

/* ─── Lazy defaults on attachment images ────────────────────────── */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
    if ( empty( $attr['loading'] ) )  $attr['loading']  = 'lazy';
    if ( empty( $attr['decoding'] ) ) $attr['decoding'] = 'async';
    return $attr;
} );

/* ─── Drop the emoji detection script + styles ──────────────────── */
add_action( 'init', function () {
    remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles',     'print_emoji_styles' );
    remove_action( 'admin_print_styles',  'print_emoji_styles' );
    remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
    remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', function ( $plugins ) {
        return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
    } );
} );
