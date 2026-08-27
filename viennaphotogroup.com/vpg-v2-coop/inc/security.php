<?php
/**
 * VPG v2 — security · light hardening.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Disable XML-RPC (we do not need it; reduces attack surface)
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove the WP version from RSS/Atom feeds
add_filter( 'the_generator', '__return_empty_string' );

// Disable file editing from wp-admin (themes/plugins)
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) define( 'DISALLOW_FILE_EDIT', true );

// Disable user enumeration via ?author=N · except in the Customizer preview
add_action( 'init', function () {
    if ( is_admin() || function_exists( 'is_customize_preview' ) && is_customize_preview() ) return;
    if ( ! empty( $_GET['author'] ) ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
} );

// Strict X-Content-Type-Options + Referrer-Policy
add_action( 'send_headers', function () {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: interest-cohort=()' );
} );
