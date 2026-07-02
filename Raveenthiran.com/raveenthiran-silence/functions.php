<?php
/**
 * Silence — functions.php
 * Monochrome, single-screen photography portfolio.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SL_THEME_VERSION', '1.0.0' );

/* =============================================================
   Option helper — every setting is a wp_options row, defaults
   centralised in sl_settings_defaults() (inc/settings.php).
   ============================================================= */
function sl_opt( $key, $default = '' ) {
	$v = get_option( $key, null );
	if ( $v === null || $v === '' ) return $default;
	return $v;
}

/* =============================================================
   Setup
   ============================================================= */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( [ 'primary' => __( 'Primary', 'raveenthiran-silence' ) ] );

	// Plates keep their native aspect (no crop); index preview is capped.
	add_image_size( 'sl-plate', 2400, 2400, false );
	add_image_size( 'sl-index', 1400, 1400, false );
} );

/* =============================================================
   Assets
   ============================================================= */
add_action( 'wp_enqueue_scripts', function () {
	// @font-face only — tiny, inlined into <head> by the style loader below.
	wp_enqueue_style( 'sl-fonts', get_template_directory_uri() . '/assets/css/fonts.css', [], SL_THEME_VERSION );
	wp_enqueue_style( 'sl-theme', get_template_directory_uri() . '/assets/css/silence.css', [ 'sl-fonts' ], SL_THEME_VERSION );
	wp_enqueue_script( 'sl-theme', get_template_directory_uri() . '/assets/js/silence.js', [], SL_THEME_VERSION, true );
} );

// Inline the small @font-face sheet with absolute URLs (removes one
// render-blocking request; same trick proven in Obscura).
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( $handle !== 'sl-fonts' ) return $tag;
	$file = get_template_directory() . '/assets/css/fonts.css';
	if ( ! is_readable( $file ) ) return $tag;
	$css = file_get_contents( $file );
	$css = str_replace( '../fonts/', esc_url( get_template_directory_uri() . '/assets/fonts/' ), $css );
	return '<style id="sl-fonts-inline">' . $css . '</style>' . "\n";
}, 10, 2 );

/* =============================================================
   Feature modules
   ============================================================= */
$includes = [
	'inc/acf-polyfill.php',  // get_field() etc. keep working without ACF
	'inc/post-types.php',    // sl_project / sl_journal / sl_enquiry CPTs
	'inc/gallery-meta.php',  // native gallery + project-details meta boxes
	'inc/settings.php',      // Appearance → Silence
	'inc/enquiry.php',       // enquiry form handler + mail
	'inc/security.php',      // Cloudflare Turnstile spam shield (optional keys)
	'inc/smtp.php',          // SMTP delivery + test button
	'inc/seo.php',           // meta/OG/Twitter + JSON-LD schema + verification
	'inc/og-cards.php',      // generated 1200×630 share cards at /sl-og/<id>.jpg
	'inc/webp.php',          // WebP twins for every sub-size + <picture> delivery
	'inc/pwa.php',           // installable / offline shell (virtual sw + manifest)
];
foreach ( $includes as $inc ) {
	$path = get_template_directory() . '/' . $inc;
	if ( is_readable( $path ) ) require_once $path;
}

/* =============================================================
   Template helpers
   ============================================================= */

/** Meta bundle for a project (client / year / location / category). */
function sl_project_meta( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$cats    = get_the_terms( $post_id, 'sl_project_cat' );
	return [
		'client' => (string) get_post_meta( $post_id, '_sl_client', true ),
		'yr'     => (string) get_post_meta( $post_id, '_sl_year', true ),
		'loc'    => (string) get_post_meta( $post_id, '_sl_location', true ),
		'cat'    => ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '',
	];
}

/** Gallery attachment IDs for a project (featured image prepended as plate 1). */
function sl_project_gallery( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$ids     = array_filter( array_map( 'absint', (array) get_post_meta( $post_id, '_sl_gallery', true ) ) );
	$thumb   = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb && ! in_array( $thumb, $ids, true ) ) array_unshift( $ids, $thumb );
	return array_values( $ids );
}

/** URL of the page using a given template, cached briefly. */
function sl_template_page_url( $template, $fallback_slug ) {
	$key = 'sl_tpl_' . md5( $template );
	$url = wp_cache_get( $key );
	if ( $url ) return $url;
	$pages = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => $template, 'number' => 1 ] );
	$url   = ! empty( $pages ) ? get_permalink( $pages[0]->ID ) : home_url( '/' . ltrim( $fallback_slug, '/' ) );
	wp_cache_set( $key, $url, '', 60 );
	return $url;
}

/** Neutral SVG placeholder plate (used before any photos exist). */
function sl_placeholder( $label = '' ) {
	$label = strtoupper( $label ?: 'silence' );
	$svg   = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1600 1067'>"
	       . "<rect width='1600' height='1067' fill='#141416'/>"
	       . "<text x='800' y='545' fill='#3A3A3E' font-family='monospace' font-size='26' letter-spacing='14' text-anchor='middle'>{$label}</text>"
	       . "</svg>";
	return '<img src="data:image/svg+xml;utf8,' . rawurlencode( $svg ) . '" alt="" width="1600" height="1067" loading="lazy" decoding="async">';
}

/* Body classes for the two effect opt-outs (defaults on). */
add_filter( 'body_class', function ( $classes ) {
	if ( sl_opt( 'sl_fx_fade',  '1' ) !== '1' ) $classes[] = 'sl-no-fade';
	if ( sl_opt( 'sl_fx_drift', '1' ) !== '1' ) $classes[] = 'sl-no-drift';
	return $classes;
} );

/* Kill emoji bloat — the theme is monochrome type + photographs. */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
} );
