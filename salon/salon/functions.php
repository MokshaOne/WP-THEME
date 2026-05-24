<?php
/**
 * Salon — functions.php
 * From-scratch WordPress theme · pure PHP/CSS/JS · no page builder.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SALON_VERSION', '1.0.0' );
define( 'SALON_DIR',     get_template_directory() );
define( 'SALON_URI',     get_template_directory_uri() );

require_once SALON_DIR . '/inc/helpers.php';
require_once SALON_DIR . '/inc/cpts.php';
require_once SALON_DIR . '/inc/customizer.php';

add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'SALON', SALON_DIR . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ] );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    add_image_size( 'SALON-hero',    1920, 1080, true );
    add_image_size( 'SALON-card',     900,  700, true );
    add_image_size( 'SALON-square',   800,  800, true );

    register_nav_menus( [
        'primary' => __( 'Primary navigation', 'SALON' ),
        'footer'  => __( 'Footer navigation', 'SALON' ),
    ] );
} );

add_action( 'wp_enqueue_scripts', function () {
    $css = (string) @filemtime( SALON_DIR . '/assets/css/main.css' );
    $js  = (string) @filemtime( SALON_DIR . '/assets/js/main.js' );

    wp_enqueue_style(
        'SALON-fonts',
        'https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@300;400;500;600&display=swap',
        [],
        SALON_VERSION
    );
    wp_enqueue_style( 'SALON-main', SALON_URI . '/assets/css/main.css', [ 'SALON-fonts' ], $css );
    wp_enqueue_script( 'SALON-main', SALON_URI . '/assets/js/main.js', [], $js, true );

    // Inject token overrides set in Customizer (palette section)
    $accent = get_theme_mod( 'SALON_color_accent', '' );
    $bg     = get_theme_mod( 'SALON_color_bg', '' );
    $fg     = get_theme_mod( 'SALON_color_fg', '' );
    $css_overrides = '';
    if ( $accent && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $accent ) ) $css_overrides .= ":root{--accent:{$accent}}";
    if ( $bg     && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $bg ) )     $css_overrides .= ":root{--bg:{$bg}}";
    if ( $fg     && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $fg ) )     $css_overrides .= ":root{--fg:{$fg}}";
    if ( $css_overrides ) wp_add_inline_style( 'SALON-main', $css_overrides );
} );

// Lean head
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Body class · expose the active subpage to CSS
add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'theme-SALON';
    return $classes;
} );
