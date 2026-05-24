<?php
/**
 * Orbit — functions.php
 * From-scratch WordPress theme · pure PHP/CSS/JS · no page builder.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ORBIT_VERSION', '1.0.0' );
define( 'ORBIT_DIR',     get_template_directory() );
define( 'ORBIT_URI',     get_template_directory_uri() );

require_once ORBIT_DIR . '/inc/helpers.php';
require_once ORBIT_DIR . '/inc/cpts.php';
require_once ORBIT_DIR . '/inc/customizer.php';

add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'ORBIT', ORBIT_DIR . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ] );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    add_image_size( 'ORBIT-hero',    1920, 1080, true );
    add_image_size( 'ORBIT-card',     900,  700, true );
    add_image_size( 'ORBIT-square',   800,  800, true );

    register_nav_menus( [
        'primary' => __( 'Primary navigation', 'ORBIT' ),
        'footer'  => __( 'Footer navigation', 'ORBIT' ),
    ] );
} );

add_action( 'wp_enqueue_scripts', function () {
    $css = (string) @filemtime( ORBIT_DIR . '/assets/css/main.css' );
    $js  = (string) @filemtime( ORBIT_DIR . '/assets/js/main.js' );

    wp_enqueue_style(
        'ORBIT-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
        [],
        ORBIT_VERSION
    );
    wp_enqueue_style( 'ORBIT-main', ORBIT_URI . '/assets/css/main.css', [ 'ORBIT-fonts' ], $css );
    wp_enqueue_script( 'ORBIT-main', ORBIT_URI . '/assets/js/main.js', [], $js, true );

    // Inject token overrides set in Customizer (palette section)
    $accent = get_theme_mod( 'ORBIT_color_accent', '' );
    $bg     = get_theme_mod( 'ORBIT_color_bg', '' );
    $fg     = get_theme_mod( 'ORBIT_color_fg', '' );
    $css_overrides = '';
    if ( $accent && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $accent ) ) $css_overrides .= ":root{--accent:{$accent}}";
    if ( $bg     && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $bg ) )     $css_overrides .= ":root{--bg:{$bg}}";
    if ( $fg     && preg_match( '/^#[0-9a-fA-F]{3,6}$/', $fg ) )     $css_overrides .= ":root{--fg:{$fg}}";
    if ( $css_overrides ) wp_add_inline_style( 'ORBIT-main', $css_overrides );
} );

// Lean head
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Body class · expose the active subpage to CSS
add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'theme-ORBIT';
    return $classes;
} );
