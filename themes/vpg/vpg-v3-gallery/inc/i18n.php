<?php
/**
 * VPG v3 — Q7 · 0722 visible language switch (DE/EN).
 *
 * ?lang=de|en sets a year-long cookie (and user meta for members);
 * the `locale` filter serves the front end in that language. The
 * de_DE translation ships with the theme (0721).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_LANG_COOKIE = 'vpg_lang';

/* Capture the switch as early as possible, then bounce back clean. */
add_action( 'init', function () {
    if ( ! isset( $_GET['lang'] ) ) return;
    $lang = sanitize_key( $_GET['lang'] );
    if ( ! in_array( $lang, [ 'de', 'en' ], true ) ) return;

    setcookie( VPG_LANG_COOKIE, $lang, [
        'expires'  => time() + YEAR_IN_SECONDS,
        'path'     => '/',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    ] );
    $_COOKIE[ VPG_LANG_COOKIE ] = $lang;
    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), '_vpg_lang', $lang );
    }
    wp_safe_redirect( remove_query_arg( 'lang' ) );
    exit;
}, 1 );

function vpg_current_lang() {
    if ( is_user_logged_in() ) {
        $m = get_user_meta( get_current_user_id(), '_vpg_lang', true );
        if ( in_array( $m, [ 'de', 'en' ], true ) ) return $m;
    }
    $c = sanitize_key( $_COOKIE[ VPG_LANG_COOKIE ] ?? '' );
    return in_array( $c, [ 'de', 'en' ], true ) ? $c : 'en';
}

/* Front end only — wp-admin keeps the profile language. */
add_filter( 'locale', function ( $locale ) {
    if ( is_admin() ) return $locale;
    return vpg_current_lang() === 'de' ? 'de_DE' : 'en_US';
} );

/* The theme's own textdomain lives in the theme — load the right .mo. */
add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'vpg-v2', get_template_directory() . '/languages' );
}, 5 );

/* ─── 1025 · Mail in the member’s language ───────────────────────
   System mails build their strings at the send site, so we switch the
   locale around the whole build-and-send for one recipient. */
function vpg_user_locale( $uid ) {
    $m = get_user_meta( $uid, '_vpg_lang', true );
    return $m === 'de' ? 'de_DE' : ( $m === 'en' ? 'en_US' : '' );
}
function vpg_switch_mail_locale( $uid ) {
    $loc = vpg_user_locale( $uid );
    if ( $loc && $loc !== get_locale() && function_exists( 'switch_to_locale' ) ) {
        switch_to_locale( $loc );
        return true;
    }
    return false;
}
function vpg_restore_mail_locale( $switched ) {
    if ( $switched && function_exists( 'restore_previous_locale' ) ) restore_previous_locale();
}

/* hreflang hints for search engines · both languages, same URL space */
add_action( 'wp_head', function () {
    if ( ! is_singular() && ! is_front_page() ) return;
    $url = is_front_page() ? home_url( '/' ) : get_permalink();
    printf( '<link rel="alternate" hreflang="de" href="%s">' . "\n", esc_url( add_query_arg( 'lang', 'de', $url ) ) );
    printf( '<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url( add_query_arg( 'lang', 'en', $url ) ) );
}, 4 );
