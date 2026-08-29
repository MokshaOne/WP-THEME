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

/* ════════════════════════════════════════════════════════════════ */
/*  Login rate limiting · transient-based, no plugin                 */
/*  6 failed attempts per IP+username → 15 minutes lockout.          */
/* ════════════════════════════════════════════════════════════════ */
function vpg_login_throttle_key( $username ) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return 'vpg_login_fail_' . md5( $ip . '|' . strtolower( (string) $username ) );
}

add_filter( 'authenticate', function ( $user, $username ) {
    if ( ! $username ) return $user;
    $fails = (int) get_transient( vpg_login_throttle_key( $username ) );
    if ( $fails >= 6 ) {
        return new WP_Error(
            'vpg_locked',
            __( 'Too many failed attempts. Please wait 15 minutes and try again, or reset your password.', 'vpg-v2' )
        );
    }
    return $user;
}, 5, 2 );

add_action( 'wp_login_failed', function ( $username ) {
    $key   = vpg_login_throttle_key( $username );
    $fails = (int) get_transient( $key );
    set_transient( $key, $fails + 1, 15 * MINUTE_IN_SECONDS );
} );

add_action( 'wp_login', function ( $username ) {
    delete_transient( vpg_login_throttle_key( $username ) );
}, 10, 1 );

/* ════════════════════════════════════════════════════════════════ */
/*  Roles · finer than all-or-nothing                                */
/*    vpg_editor_circle — runs editorial: submissions, magazine,     */
/*      publishing, comment moderation. No plugins/themes/users.     */
/*    vpg_curator — assembles issues + reviews submissions, cannot   */
/*      publish other people's standalone posts.                     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! get_role( 'vpg_editor_circle' ) ) {
        add_role( 'vpg_editor_circle', __( 'VPG Editorial Circle', 'vpg-v2' ), [
            'read'                   => true,
            'edit_posts'             => true,
            'edit_others_posts'      => true,
            'edit_published_posts'   => true,
            'publish_posts'          => true,
            'delete_posts'           => true,
            'delete_others_posts'    => true,
            'delete_published_posts' => true,
            'upload_files'           => true,
            'moderate_comments'      => true,
            'edit_pages'             => false,
        ] );
    }
    if ( ! get_role( 'vpg_curator' ) ) {
        add_role( 'vpg_curator', __( 'VPG Curator', 'vpg-v2' ), [
            'read'              => true,
            'edit_posts'        => true,
            'edit_others_posts' => true,   // opens the magazine editor + queue
            'upload_files'      => true,
        ] );
    }
}, 5 );
