<?php
/**
 * on1.agency · v4
 * Custom WordPress theme bootstrap.
 * No ACF dependency. Everything editable via wp-admin → On1 Hub.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'ON1_VERSION', '4.0.0' );
define( 'ON1_DIR',     get_template_directory() );
define( 'ON1_URI',     get_template_directory_uri() );

/* ─── Includes ───────────────────────────────────────────── */
require_once ON1_DIR . '/inc/helpers.php';
require_once ON1_DIR . '/inc/cpts.php';
require_once ON1_DIR . '/inc/admin-panel.php';
require_once ON1_DIR . '/inc/skins.php';
require_once ON1_DIR . '/inc/skin-render.php';
require_once ON1_DIR . '/inc/skin-admin.php';

/* ─── Theme setup ─────────────────────────────────────────── */
add_action( 'after_setup_theme', function () {
    load_theme_textdomain( 'on1-agency', ON1_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'automatic-feed-links' );

    add_image_size( 'on1-hero',    1920, 1080, true );
    add_image_size( 'on1-project',  1400,  900, true );
    add_image_size( 'on1-card',      900,  700, true );

    register_nav_menus( [
        'primary' => __( 'Primary navigation', 'on1-agency' ),
        'footer'  => __( 'Footer navigation',  'on1-agency' ),
        'legal'   => __( 'Legal footer links', 'on1-agency' ),
    ] );
} );

/* ─── Enqueue (cache-bust via filemtime) ─────────────────── */
add_action( 'wp_enqueue_scripts', function () {
    $css_ver   = (string) @filemtime( ON1_DIR . '/assets/css/main.css' );
    $skins_ver = (string) @filemtime( ON1_DIR . '/assets/css/skins.css' );
    $js_ver    = (string) @filemtime( ON1_DIR . '/assets/js/main.js' );
    $router_ver = (string) @filemtime( ON1_DIR . '/assets/js/skin-router.js' );

    // Build the Google Fonts request from every enabled skin's font list.
    $fonts = [
        'Inter:wght@300;400;500;600;700',
        'Instrument+Serif:ital@0;1',
    ];
    foreach ( on1_get_enabled_skins() as $skin ) {
        foreach ( $skin['fonts'] as $font ) {
            $slug = str_replace( ' ', '+', $font );
            // crude family-axis presets per family; safe defaults
            if ( $font === 'EB Garamond' )      $fonts[] = $slug . ':ital,wght@0,400;0,500;1,400';
            elseif ( $font === 'JetBrains Mono' ) $fonts[] = $slug . ':wght@300;400;500';
            elseif ( $font === 'Space Grotesk' )  $fonts[] = $slug . ':wght@400;500;600';
            elseif ( $font === 'Inter Tight' )    $fonts[] = $slug . ':wght@300;400;500;700';
            elseif ( $font === 'Playfair Display' ) $fonts[] = $slug . ':ital,wght@0,400;0,500;1,400';
            elseif ( $font === 'Bodoni Moda' )    $fonts[] = $slug . ':ital,opsz,wght@0,6..96,400;0,6..96,500;1,6..96,400';
            elseif ( $font === 'Italiana' )       $fonts[] = $slug;
            elseif ( $font === 'Manrope' )        $fonts[] = $slug . ':wght@300;400;500;600';
            else                                  $fonts[] = $slug;
        }
    }
    $fonts = array_values( array_unique( $fonts ) );
    $fonts_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $fonts ) . '&display=swap';

    wp_enqueue_style( 'on1-fonts', $fonts_url, [], ON1_VERSION );
    wp_enqueue_style( 'on1-main',  ON1_URI . '/assets/css/main.css',  [ 'on1-fonts' ], $css_ver );
    wp_enqueue_style( 'on1-skins', ON1_URI . '/assets/css/skins.css', [ 'on1-main' ], $skins_ver );

    wp_enqueue_script( 'on1-router', ON1_URI . '/assets/js/skin-router.js', [], $router_ver, true );

    // Inject palette tokens from admin (only if explicitly set).
    $accent = on1_opt( 'accent_color', '' );
    if ( $accent && preg_match( '/^#[0-9a-fA-F]{6}$/', $accent ) ) {
        wp_add_inline_style( 'on1-main', ":root { --accent: {$accent}; }" );
    }

    // Per-skin token overrides (admin-editable colors)
    $skins_css = on1_render_skin_tokens_css();
    if ( $skins_css ) wp_add_inline_style( 'on1-skins', $skins_css );

    // Skin registry passed to JS for picker pills
    $skins_for_js = on1_skins_for_js();
    wp_add_inline_script( 'on1-router', 'window.ON1_SKINS=' . wp_json_encode( $skins_for_js ) . ';', 'before' );
} );

/* ─── Performance / cleanup ──────────────────────────────── */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/* ─── JSON-LD (Organization, replaces the ACF-driven Person) */
add_action( 'wp_head', function() {
    $identity = on1_get_identity();
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => $identity['name'] ?: 'on1.agency',
        'url'         => home_url( '/' ),
        'email'       => $identity['email'],
        'description' => 'A one-person digital atelier in Wien. Editorial WordPress sites + design systems.',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $identity['location'] ?: 'Vienna',
            'addressCountry'  => 'AT',
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . PHP_EOL;
}, 5 );
