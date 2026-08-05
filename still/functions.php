<?php
/**
 * Still — theme functions. Lean classic theme: setup, assets, one content
 * type (Work) + one taxonomy, menus, and a couple of URL helpers. No ACF.
 *
 * @package Still
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STILL_VER', '1.0.0' );

/* ── Setup ─────────────────────────────────────────────────────────── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'width' => 40, 'flex-height' => true, 'flex-width' => true ) );

	add_image_size( 'still-card', 900, 1125, true );   // 4:5 portrait card
	add_image_size( 'still-hero', 2000, 1333, false );  // large hero

	register_nav_menus( array(
		'primary' => __( 'Primary (dock)', 'still' ),
	) );

	load_theme_textdomain( 'still', get_template_directory() . '/languages' );
} );

/* ── Assets ────────────────────────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
	// Version assets by file mtime so any edit busts the browser / plugin / CDN cache.
	$css  = get_template_directory() . '/assets/css/main.css';
	$js   = get_template_directory() . '/assets/js/main.js';
	$cssv = file_exists( $css ) ? filemtime( $css ) : STILL_VER;
	$jsv  = file_exists( $js )  ? filemtime( $js )  : STILL_VER;

	wp_enqueue_style( 'still', get_template_directory_uri() . '/assets/css/main.css', array(), $cssv );
	// keep style.css loaded too (theme header / any overrides)
	wp_enqueue_style( 'still-base', get_stylesheet_uri(), array( 'still' ), $cssv );

	wp_enqueue_script( 'still', get_template_directory_uri() . '/assets/js/main.js', array(), $jsv, true );
	wp_localize_script( 'still', 'STILL', array(
		'isHome' => ( is_front_page() && ! is_paged() ) ? 1 : 0,
	) );
} );

/* ── Content engine: Work (portfolio) — unlimited entries, native gallery ── */
add_action( 'init', function () {

	register_post_type( 'work', array(
		'labels' => array(
			'name'          => __( 'Work', 'still' ),
			'singular_name' => __( 'Project', 'still' ),
			'add_new_item'  => __( 'Add New Project', 'still' ),
			'edit_item'     => __( 'Edit Project', 'still' ),
			'all_items'     => __( 'All Work', 'still' ),
			'menu_name'     => __( 'Work', 'still' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-camera-alt',
		'menu_position' => 5,
		'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'work_category', 'work', array(
		'labels'            => array( 'name' => __( 'Categories', 'still' ), 'singular_name' => __( 'Category', 'still' ) ),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'work-category', 'with_front' => false ),
	) );
} );

/* Larger archives so galleries breathe; flush rewrites once. */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'still_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'still_flushed', '1' );
	}
} );
add_action( 'switch_theme', function () { delete_option( 'still_flushed' ); } );

/* ── URL helpers ───────────────────────────────────────────────────── */

/** URL of a Page by slug, or a sensible fallback path. */
function still_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/' . ( $fallback ?: $slug ) );
}

/** Journal (blog posts) URL — the assigned Posts page, else /journal. */
function still_journal_url() {
	$pid = (int) get_option( 'page_for_posts' );
	return $pid ? get_permalink( $pid ) : still_page_url( 'journal' );
}

/** Work archive URL. */
function still_work_url() {
	return get_post_type_archive_link( 'work' ) ?: home_url( '/work' );
}

/**
 * The navigation model. If the user built a "primary" menu we use it;
 * otherwise these defaults drive the dock and the home teaser panels.
 * Each item: key, label, url, description.
 */
function still_nav_items() {
	$items = array(
		array( 'key' => 'work',    'label' => __( 'Work', 'still' ),    'url' => still_work_url(),        'desc' => __( 'Portraiture, architecture and the quiet spaces between. Selected projects — new work added continually.', 'still' ) ),
		array( 'key' => 'studio',  'label' => __( 'Studio', 'still' ),  'url' => still_page_url( 'about', 'about' ), 'desc' => __( 'A practice of looking slowly — and keeping only what lasts. Based in Vienna, working across Europe.', 'still' ) ),
		array( 'key' => 'journal', 'label' => __( 'Journal', 'still' ), 'url' => still_journal_url(),     'desc' => __( 'Notes from the field — process, film, and the occasional long essay.', 'still' ) ),
		array( 'key' => 'contact', 'label' => __( 'Contact', 'still' ), 'url' => still_page_url( 'contact', 'contact' ), 'desc' => __( 'Studio details, availability and the ways to reach me.', 'still' ) ),
		array( 'key' => 'enquire', 'label' => __( 'Enquire', 'still' ), 'url' => still_page_url( 'enquire', 'enquire' ), 'desc' => __( 'Commissions, editorial and personal series. Start a project.', 'still' ) ),
	);
	return apply_filters( 'still_nav_items', $items );
}

/** Number the archive index the way the panels/cards read (01, 02 …). */
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
	if ( ( $args['total'] ?? ( $GLOBALS['wp_query']->max_num_pages ?? 1 ) ) < 2 ) {
		return;
	}
	$links = paginate_links( $args );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'still' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}
