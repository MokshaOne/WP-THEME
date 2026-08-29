<?php
/**
 * M1O Hub theme · v2.0 · link-list direction
 * by On1 Agency
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'M1O_VERSION', '2.0.0' );

// ============================================================
// 1. THEME SETUP
// ============================================================
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'automatic-feed-links' );
} );


// ============================================================
// 2. ENQUEUE (cache-bust via filemtime so CSS updates land)
// ============================================================
add_action( 'wp_enqueue_scripts', function () {
    $css_ver = (string) @filemtime( get_template_directory() . '/style.css' );

    // Fonts (Google Fonts CDN — todo: self-host in v2.1)
    wp_enqueue_style(
        'm1o-fonts',
        'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@400;500;700&display=swap',
        [], M1O_VERSION
    );
    wp_enqueue_style( 'm1o-main-style', get_stylesheet_uri(), [ 'm1o-fonts' ], $css_ver );
} );


// ============================================================
// 3. CLEANUP
// ============================================================
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );


// ============================================================
// 4. CHANNELS — the data model for the link list
// ============================================================
// Storage: single wp_option 'm1o_channels' holding an array of channel rows.
// Each row: [ 'group' => 'work|social|projects', 'title' => string, 'host' => string, 'url' => string, 'status' => 'active|idle|live|current' ]

function m1o_get_channels() {
    $channels = get_option( 'm1o_channels', null );
    if ( ! is_array( $channels ) || empty( $channels ) ) {
        return m1o_default_channels();
    }
    return $channels;
}

function m1o_default_channels() {
    return [
        [ 'group' => 'work',     'title' => 'ON1 Agency',         'host' => 'on1.agency · design studio',         'url' => 'https://on1.agency',           'status' => 'active' ],
        [ 'group' => 'work',     'title' => 'Raveenthiran',       'host' => 'raveenthiran.com · photography',     'url' => 'https://raveenthiran.com',     'status' => 'active' ],
        [ 'group' => 'work',     'title' => 'Vienna Photo Group', 'host' => 'viennaphotogroup.com · community',   'url' => 'https://viennaphotogroup.com', 'status' => 'active' ],
        [ 'group' => 'social',   'title' => 'Instagram',          'host' => '@moksha1one',                         'url' => 'https://instagram.com/moksha1one', 'status' => 'active' ],
        [ 'group' => 'social',   'title' => 'GitHub',             'host' => 'github.com/MokshaOne',               'url' => 'https://github.com/MokshaOne',  'status' => 'active' ],
        [ 'group' => 'social',   'title' => 'LinkedIn',           'host' => 'nishuthan-raveenthiran',              'url' => 'https://www.linkedin.com/in/nishuthan-raveenthiran', 'status' => 'active' ],
        [ 'group' => 'projects', 'title' => 'M1O Hub',            'host' => 'm1o.at · this page',                  'url' => 'https://m1o.at',                'status' => 'current' ],
    ];
}

/**
 * Group labels — extend here if you want new groups.
 */
function m1o_group_labels() {
    return [
        'work'     => 'Work',
        'social'   => 'Social',
        'projects' => 'Projects',
    ];
}

function m1o_status_labels() {
    return [
        'active'  => '● Active',
        'idle'    => '○ Idle',
        'live'    => '▶ Live',
        'current' => '● Current',
    ];
}


// ============================================================
// 5. OPTION HELPERS
// ============================================================
function m1o_opt( $key, $default = '' ) {
    return get_option( $key, $default );
}

function m1o_get_identity() {
    return [
        'name'     => m1o_opt( 'm1o_identity_name',     'Moksha 1one' ),
        'tagline'  => m1o_opt( 'm1o_identity_tagline',  "Designer · Photographer · Builder\nWorking between Vienna and the open web." ),
        'location' => m1o_opt( 'm1o_identity_location', 'Vienna, AT' ),
        'status'   => m1o_opt( 'm1o_identity_status',   'Online · Available' ),
        'est'      => m1o_opt( 'm1o_identity_est',      'MMXVIII' ),
        'email'    => m1o_opt( 'm1o_identity_email',    'hello@m1o.at' ),
        'cta'      => m1o_opt( 'm1o_cta_text',          'Send a signal' ),
    ];
}


// ============================================================
// 6. JSON-LD (Person, not MusicGroup)
// ============================================================
add_action( 'wp_head', function() {
    $id   = m1o_get_identity();
    $url  = home_url( '/' );
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Person',
        'name'        => $id['name'],
        'url'         => $url,
        'email'       => $id['email'],
        'jobTitle'    => 'Designer & Photographer',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $id['location'],
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . PHP_EOL;
}, 5 );


// ============================================================
// 7. ADMIN PANEL
// ============================================================
require_once get_template_directory() . '/inc/admin-panel.php';
