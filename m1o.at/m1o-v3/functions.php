<?php
/**
 * M1O Transmission · v3.0
 * The transmission console — compact brutalist successor to M1O Hub v2.
 * Reads the same wp_options as v2 so switching themes keeps all content.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'M1O_VERSION', '3.0.0' );

// ============================================================
// 1. THEME SETUP
// ============================================================
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( [
		'legal' => __( 'Legal (footer)', 'm1o' ),
	] );
} );


// ============================================================
// 2. ENQUEUE
//    GSAP from cdnjs (pinned, deferred) — todo v3.1: self-host.
// ============================================================
add_action( 'wp_enqueue_scripts', function () {
	$dir     = get_template_directory();
	$uri     = get_template_directory_uri();
	$css_ver = (string) @filemtime( $dir . '/assets/css/main.css' );
	$js_ver  = (string) @filemtime( $dir . '/assets/js/main.js' );

	wp_enqueue_style(
		'm1o-fonts',
		'https://fonts.googleapis.com/css2?family=Syne:wght@500;700;800&family=Inter+Tight:wght@300;400&family=JetBrains+Mono:wght@400;500;700&display=swap',
		[], M1O_VERSION
	);
	wp_enqueue_style( 'm1o-main', $uri . '/assets/css/main.css', [ 'm1o-fonts' ], $css_ver );

	if ( m1o_motion_on() ) {
		wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', [], '3.12.5', true );
		wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', [ 'gsap' ], '3.12.5', true );
		wp_enqueue_script( 'm1o-main', $uri . '/assets/js/main.js', [ 'gsap-st' ], $js_ver, true );
		foreach ( [ 'gsap', 'gsap-st', 'm1o-main' ] as $h ) {
			wp_script_add_data( $h, 'strategy', 'defer' );
		}
	}
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );


// ============================================================
// 3. CLEANUP + HARDENING
// ============================================================
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'login_errors', fn() => __( 'Invalid credentials.', 'm1o' ) );


// ============================================================
// 4. OPTIONS — same data model as M1O Hub v2 (drop-in switch)
// ============================================================
function m1o_opt( $key, $default = '' ) {
	return get_option( $key, $default );
}

function m1o_get_identity() {
	return [
		'name'        => m1o_opt( 'm1o_identity_name',     'Moksha 1one' ),
		'tagline'     => m1o_opt( 'm1o_identity_tagline',  "Designer, photographer and builder in Vienna. One practice, three channels: photography, community, sound." ),
		'location'    => m1o_opt( 'm1o_identity_location', 'Vienna, AT' ),
		'status'      => m1o_opt( 'm1o_identity_status',   'Online · Available' ),
		'est'         => m1o_opt( 'm1o_identity_est',      'MMXVIII' ),
		'email'       => m1o_opt( 'm1o_identity_email',    'hello@m1o.at' ),
		'cta'         => m1o_opt( 'm1o_cta_text',          'Send a signal' ),
		'music_url'   => m1o_opt( 'm1o_music_embed',       '' ),
		'music_title' => m1o_opt( 'm1o_music_title',       '' ),
	];
}

function m1o_get_channels() {
	$channels = get_option( 'm1o_channels', null );
	if ( ! is_array( $channels ) || empty( $channels ) ) {
		return m1o_default_channels();
	}
	return $channels;
}

function m1o_default_channels() {
	return [
		[ 'group' => 'work',   'title' => 'Raveenthiran',       'host' => 'Photography · projects, journal, bookings', 'url' => 'https://raveenthiran.com',     'status' => 'active' ],
		[ 'group' => 'work',   'title' => 'Vienna Photo Group', 'host' => 'Community · galleries, magazine, city map', 'url' => 'https://viennaphotogroup.com', 'status' => 'active' ],
		[ 'group' => 'social', 'title' => 'Instagram',          'host' => '@moksha1one',                               'url' => 'https://instagram.com/moksha1one', 'status' => 'active' ],
		[ 'group' => 'social', 'title' => 'GitHub',             'host' => 'github.com/MokshaOne',                      'url' => 'https://github.com/MokshaOne',  'status' => 'active' ],
		[ 'group' => 'social', 'title' => 'LinkedIn',           'host' => 'linkedin.com/in/nishuthan-raveenthiran',    'url' => 'https://www.linkedin.com/in/nishuthan-raveenthiran', 'status' => 'active' ],
	];
}

function m1o_group_labels() {
	return [
		'work'     => __( 'Work', 'm1o' ),
		'social'   => __( 'Social', 'm1o' ),
		'projects' => __( 'Projects', 'm1o' ),
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

/** Index rows = every non-social channel, in stored order. */
function m1o_index_channels() {
	return array_values( array_filter( m1o_get_channels(), fn( $ch ) => ( $ch['group'] ?? 'work' ) !== 'social' ) );
}

/** Social row = social-group channels. */
function m1o_social_channels() {
	return array_values( array_filter( m1o_get_channels(), fn( $ch ) => ( $ch['group'] ?? '' ) === 'social' ) );
}

function m1o_motion_on() {
	return m1o_opt( 'm1o_show_motion', '1' ) !== '0';
}


// ============================================================
// 5. MUSIC EMBED — Spotify / YouTube URL → iframe src
// ============================================================
/**
 * Returns [ src, kind ] for a supported embed URL, or null.
 * Spotify: open.spotify.com/{track|album|playlist|artist|episode|show}/{id}
 * YouTube: youtube.com/watch?v= | youtu.be/{id} | youtube.com/embed/{id}
 */
function m1o_music_embed_src( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) return null;

	if ( preg_match( '~open\.spotify\.com/(intl-[a-z]+/)?(track|album|playlist|artist|episode|show)/([A-Za-z0-9]+)~', $url, $m ) ) {
		return [ 'https://open.spotify.com/embed/' . $m[2] . '/' . $m[3], 'spotify' ];
	}
	if ( preg_match( '~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $url, $m ) ) {
		return [ 'https://www.youtube-nocookie.com/embed/' . $m[1], 'youtube' ];
	}
	return null;
}


// ============================================================
// 6. JSON-LD (Person)
// ============================================================
add_action( 'wp_head', function () {
	if ( ! is_front_page() ) return;
	$id     = m1o_get_identity();
	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'Person',
		'name'     => $id['name'],
		'url'      => home_url( '/' ),
		'email'    => $id['email'],
		'jobTitle' => 'Designer & Photographer',
		'address'  => [
			'@type'           => 'PostalAddress',
			'addressLocality' => $id['location'],
		],
		'sameAs'   => array_values( array_filter( array_map( fn( $c ) => $c['url'] ?? '', m1o_social_channels() ) ) ),
	];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . PHP_EOL;
}, 5 );


// ============================================================
// 7. BODY CLASSES for the motion layer
// ============================================================
add_filter( 'body_class', function ( $classes ) {
	if ( m1o_motion_on() ) $classes[] = 'm1o-motion';
	return $classes;
} );


// ============================================================
// 8. ADMIN PANEL
// ============================================================
require_once get_template_directory() . '/inc/admin-panel.php';
