<?php
/**
 * VPG v2 — theme setup.
 * Theme supports, image sizes, nav menus.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_setup_theme', function () {

    load_theme_textdomain( 'vpg-v2', VPG_V2_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'post-formats', [ 'aside' ] ); // 0266 · short journal notes
    add_theme_support( 'editor-styles' );
    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );

    // Image sizes used across the theme
    add_image_size( 'vpg-cover',  1600, 2100, true );  // magazine cover · 3:4
    add_image_size( 'vpg-hero',   1920, 1080, true );
    add_image_size( 'vpg-card',    900,  600, true );
    add_image_size( 'vpg-square',  800,  800, true );
    add_image_size( 'vpg-thumb',   400,  300, true );

    register_nav_menus( [
        'primary'      => __( 'Primary navigation', 'vpg-v2' ),
        'footer-col-1' => __( 'Footer · Explore', 'vpg-v2' ),
        'footer-col-2' => __( 'Footer · Members', 'vpg-v2' ),
        'footer-col-3' => __( 'Footer · Community', 'vpg-v2' ),
        'legal'        => __( 'Legal links', 'vpg-v2' ),
    ] );

    // Editor style matches the front-end body styles
    add_editor_style( 'assets/css/editor.css' );
} );

/* Lean head · drop noise */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* JSON-LD · Organisation schema */
add_action( 'wp_head', function () {
    if ( ! is_front_page() ) return;
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => get_bloginfo( 'name' ),
        'url'      => home_url( '/' ),
        'logo'     => get_theme_mod( 'vpg_logo_url', '' ),
        'sameAs'   => array_filter( [
            get_theme_mod( 'vpg_social_instagram', '' ),
            get_theme_mod( 'vpg_social_x', '' ),
        ] ),
    ];
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . PHP_EOL;
}, 5 );
