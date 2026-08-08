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

/**
 * Intro video source, if any. Drop `intro.mp4` (or .webm) into
 * assets/video/ and the cinematic intro plays it, then scrolls right into
 * home. With no file present the intro falls back to the animated boot /
 * terminal / name-decode sequence. Override the URL via the filter.
 */
function still_intro_video() {
	$url = '';
	foreach ( array( 'intro.mp4', 'intro.webm', 'intro.mov' ) as $f ) {
		if ( file_exists( get_template_directory() . '/assets/video/' . $f ) ) {
			$url = get_template_directory_uri() . '/assets/video/' . $f;
			break;
		}
	}
	return apply_filters( 'still_intro_video', $url );
}

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

/* Larger archives so galleries breathe; flush rewrites once. Also make sure an
   "Enquire" page exists so the dock link + the price-engine template resolve. */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'still_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'still_flushed', '1' );
	}
	if ( ! get_page_by_path( 'enquire' ) ) {
		wp_insert_post( array(
			'post_title'   => __( 'Enquire', 'still' ),
			'post_name'    => 'enquire',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );
	}
} );
add_action( 'switch_theme', function () { delete_option( 'still_flushed' ); } );

/* ── Enquiry: pricing model, FAQ, and the mail handler ──────────────────
   All three are LIGHTWEIGHT + optional: the front-end price engine and the
   enquire template read these; nothing here runs unless the Enquire page /
   form is used, so it cannot affect the intro, home, or work pages. Every
   value is filterable, so prices/FAQ can be tuned without touching markup. */

/** Pricing config for the live estimate. Filter 'still_quote_data' to edit. */
function still_quote_data() {
	return apply_filters( 'still_quote_data', array(
		'currency' => '€',
		'types'    => array(
			array( 'slug' => 'portrait',   'label' => __( 'Portrait', 'still' ),   'base' => 450 ),
			array( 'slug' => 'editorial',  'label' => __( 'Editorial', 'still' ),  'base' => 1200 ),
			array( 'slug' => 'event',      'label' => __( 'Event', 'still' ),      'base' => 900 ),
			array( 'slug' => 'commercial', 'label' => __( 'Commercial', 'still' ), 'base' => 1800 ),
		),
		'extras'   => array(
			array( 'slug' => 'retouch', 'label' => __( 'Advanced retouching', 'still' ), 'price' => 180 ),
			array( 'slug' => 'express', 'label' => __( 'Express delivery', 'still' ),    'price' => 240 ),
			array( 'slug' => 'second',  'label' => __( 'Second shooter', 'still' ),      'price' => 300 ),
			array( 'slug' => 'makeup',  'label' => __( 'Hair & makeup', 'still' ),       'price' => 350 ),
		),
		'license'  => 150,   // commercial usage licence, flat
		'per_km'   => 0.42,  // travel, per km
	) );
}

/** FAQ shown under the enquiry form. Filter 'still_faq_items' to edit. */
function still_faq_items() {
	return apply_filters( 'still_faq_items', array(
		array( 'q' => __( 'How do we get started?', 'still' ),          'a' => __( 'Send an enquiry with a few details about your project. I reply within 24 hours with availability and a firm quote.', 'still' ) ),
		array( 'q' => __( 'Is the estimate final?', 'still' ),          'a' => __( 'It is indicative — a fast way to gauge scope. The final quote is confirmed by email once we have talked through the details.', 'still' ) ),
		array( 'q' => __( 'Do you travel?', 'still' ),                  'a' => __( 'Yes. I am based in Vienna and work across Europe. Travel beyond the city is added per kilometre.', 'still' ) ),
		array( 'q' => __( 'When do I receive the images?', 'still' ),   'a' => __( 'Edited galleries are delivered within two to three weeks. Express delivery is available as an add-on.', 'still' ) ),
		array( 'q' => __( 'How does licensing work?', 'still' ),        'a' => __( 'Personal use is included. Commercial usage is a flat licence added to the project.', 'still' ) ),
	) );
}

/** Handle the enquiry form (admin-post.php?action=still_enquiry). */
function still_handle_enquiry() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	// Nonce.
	if ( empty( $_POST['still_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['still_nonce'] ) ), 'still_enquiry' ) ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'error', $back ) );
		exit;
	}
	// Honeypot — bots fill this; pretend success and drop it.
	if ( ! empty( $_POST['still_company'] ) ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'sent', $back ) );
		exit;
	}

	$name     = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$notes    = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );
	$type     = sanitize_text_field( wp_unslash( $_POST['project_type'] ?? '' ) );
	$date     = sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) );
	$estimate = sanitize_text_field( wp_unslash( $_POST['estimate'] ?? '' ) );
	$break    = sanitize_text_field( wp_unslash( $_POST['breakdown'] ?? '' ) );

	if ( '' === $name || ! is_email( $email ) || '' === $notes ) {
		wp_safe_redirect( add_query_arg( 'enquiry', 'error', $back ) );
		exit;
	}

	$rows = array( __( 'Name', 'still' ) => $name, __( 'Email', 'still' ) => $email );
	if ( $type )     { $rows[ __( 'Project', 'still' ) ]        = $type; }
	if ( $date )     { $rows[ __( 'Preferred date', 'still' ) ] = $date; }
	if ( $estimate ) { $rows[ __( 'Estimate', 'still' ) ]       = $estimate; }
	if ( $break )    { $rows[ __( 'Breakdown', 'still' ) ]      = $break; }

	$body = '';
	foreach ( $rows as $k => $v ) { $body .= $k . ': ' . $v . "\n"; }
	$body .= "\n" . __( 'Message', 'still' ) . ":\n" . $notes . "\n";

	$subject = sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), __( 'New enquiry', 'still' ) );
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
	$ok      = wp_mail( get_option( 'admin_email' ), $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'enquiry', $ok ? 'sent' : 'error', $back ) );
	exit;
}
add_action( 'admin_post_still_enquiry', 'still_handle_enquiry' );
add_action( 'admin_post_nopriv_still_enquiry', 'still_handle_enquiry' );

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
