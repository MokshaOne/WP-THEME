<?php
/**
 * Still — theme functions.
 *
 * Still is the cinematic DESIGN layer. The ENGINE (content types, ACF, Theme
 * Settings, SMTP, PWA, WebP, OG cards, PDF, map, SEO, security, shortcodes …)
 * is cloned from moksha1one and loaded from inc/engine.php. It registers the
 * nr_* content model (nr_project = Work, nr_journal = Journal, nr_testimonial,
 * nr_enquiry). Still's templates render that content in the Still look; we
 * dequeue moksha's own skin so this design owns the front end.
 *
 * @package Still
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STILL_VER', '1.0.0' );

/* ── The moksha1one engine (CPTs, ACF, features, Theme Settings) ────── */
require_once get_template_directory() . '/inc/engine.php';

/* ── Still design setup (additive to the engine's supports) ─────────── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_image_size( 'still-card', 900, 1125, true );
	add_image_size( 'still-hero', 2000, 1333, false );
	register_nav_menus( array( 'primary' => __( 'Primary (dock)', 'still' ) ) );
}, 20 );

/* ── Still design assets ────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'still', get_template_directory_uri() . '/assets/css/main.css', array(), STILL_VER );
	wp_enqueue_style( 'still-base', get_stylesheet_uri(), array( 'still' ), STILL_VER );
	wp_enqueue_script( 'still', get_template_directory_uri() . '/assets/js/main.js', array(), STILL_VER, true );
}, 20 );

/* ── Dequeue moksha1one's VISUAL layer — keep its features, drop its skin ── */
add_action( 'wp_enqueue_scripts', function () {
	foreach ( array( 'nr-theme', 'void-skin', 'mk-skin', 'nr-fonts', 'nr-features', 'nr-fixes' ) as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( array( 'nr-theme', 'void-skin', 'mk-scroll', 'nr-studio', 'nr-magic', 'nr-webgl-hero', 'nr-features' ) as $h ) {
		wp_dequeue_script( $h );
	}
}, 100 );

/* Flush rewrites once after switching to Still. */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'still_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'still_flushed', '1' );
	}
} );
add_action( 'switch_theme', function () { delete_option( 'still_flushed' ); } );

/* ── URL helpers → point at the engine's content model ─────────────── */
function still_work_url() {
	return get_post_type_archive_link( 'nr_project' ) ?: home_url( '/project' );
}
function still_journal_url() {
	return get_post_type_archive_link( 'nr_journal' ) ?: home_url( '/journal' );
}
function still_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . ( $fallback ?: $slug ) );
}
function still_pad( $n ) {
	return str_pad( (string) (int) $n, 2, '0', STR_PAD_LEFT );
}

/** Minimal pagination markup matching the theme's .pagination styles. */
function still_pagination( $query = null ) {
	$args = array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→', 'type' => 'plain' );
	if ( $query instanceof WP_Query ) {
		$args['total']   = (int) $query->max_num_pages;
		$args['current'] = max( 1, (int) get_query_var( 'paged' ) );
	}
	$total = $args['total'] ?? ( $GLOBALS['wp_query']->max_num_pages ?? 1 );
	if ( $total < 2 ) {
		return;
	}
	$links = paginate_links( $args );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'still' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}

/**
 * Navigation model — drives the dock and the home teaser panels.
 * Each panel links OUT to a real page; it never replaces it.
 */
function still_nav_items() {
	$items = array(
		array( 'key' => 'work',    'label' => __( 'Work', 'still' ),    'url' => still_work_url(),                       'desc' => __( 'Portraiture, architecture and the quiet spaces between. Selected projects — new work added continually.', 'still' ) ),
		array( 'key' => 'studio',  'label' => __( 'Studio', 'still' ),  'url' => still_page_url( 'about', 'about' ),     'desc' => __( 'A practice of looking slowly — and keeping only what lasts. Based in Vienna, working across Europe.', 'still' ) ),
		array( 'key' => 'journal', 'label' => __( 'Journal', 'still' ), 'url' => still_journal_url(),                    'desc' => __( 'Notes from the field — process, film, and the occasional long essay.', 'still' ) ),
		array( 'key' => 'contact', 'label' => __( 'Contact', 'still' ), 'url' => still_page_url( 'contact', 'contact' ), 'desc' => __( 'Studio details, availability and the ways to reach me.', 'still' ) ),
		array( 'key' => 'enquire', 'label' => __( 'Enquire', 'still' ), 'url' => still_page_url( 'enquire', 'enquire' ), 'desc' => __( 'Commissions, editorial and personal series. Start a project.', 'still' ) ),
	);
	return apply_filters( 'still_nav_items', $items );
}

/**
 * Render a project/journal cover using the engine's smart image helper when
 * present (handles featured image, ACF cover and gallery fallbacks), else the
 * plain featured image.
 */
function still_cover( $id, $size = 'still-card' ) {
	if ( function_exists( 'nr_image_or_placeholder' ) ) {
		nr_image_or_placeholder( $id, $size, get_the_title( $id ) );
	} elseif ( has_post_thumbnail( $id ) ) {
		echo get_the_post_thumbnail( $id, $size, array( 'alt' => esc_attr( get_the_title( $id ) ) ) );
	} else {
		echo '<span class="ph">' . esc_html__( 'Photo', 'still' ) . '</span>';
	}
}
