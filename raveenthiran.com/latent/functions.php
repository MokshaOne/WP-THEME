<?php
/**
 * Latent — a block (FSE) theme. Almost everything lives in theme.json and the
 * block templates; this file only does the few things PHP must: load the
 * stylesheet, register the minimal content engine, and declare supports.
 *
 * @package Latent
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end stylesheet + block-editor niceties.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'latent-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
} );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'post-thumbnails' );
	load_theme_textdomain( 'latent', get_template_directory() . '/languages' );
} );

/**
 * The whole content engine — one clean post type for the portfolio, one
 * taxonomy to group it. No ACF: use the block editor for the write-up and the
 * featured image for the cover. Everything is REST-enabled so it works in the
 * Site Editor and the block editor.
 */
add_action( 'init', function () {

	register_post_type( 'work', array(
		'labels' => array(
			'name'               => __( 'Work', 'latent' ),
			'singular_name'      => __( 'Work', 'latent' ),
			'add_new'            => __( 'Add New', 'latent' ),
			'add_new_item'       => __( 'Add New Work', 'latent' ),
			'edit_item'          => __( 'Edit Work', 'latent' ),
			'new_item'           => __( 'New Work', 'latent' ),
			'view_item'          => __( 'View Work', 'latent' ),
			'search_items'       => __( 'Search Work', 'latent' ),
			'not_found'          => __( 'No work found', 'latent' ),
			'all_items'          => __( 'All Work', 'latent' ),
			'menu_name'          => __( 'Work', 'latent' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-camera-alt',
		'menu_position' => 5,
		'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
		'show_in_rest'  => true,
		'taxonomies'    => array( 'work_category' ),
	) );

	register_taxonomy( 'work_category', 'work', array(
		'labels' => array(
			'name'          => __( 'Categories', 'latent' ),
			'singular_name' => __( 'Category', 'latent' ),
			'add_new_item'  => __( 'Add New Category', 'latent' ),
			'menu_name'     => __( 'Categories', 'latent' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'work-category', 'with_front' => false ),
	) );
} );

/**
 * A pattern category so the theme's patterns group together in the inserter.
 */
add_action( 'init', function () {
	register_block_pattern_category( 'latent', array(
		'label' => __( 'Latent', 'latent' ),
	) );
} );

/**
 * Flush rewrite rules once, on activation, so /work and /work-category resolve
 * without a manual Permalinks save.
 */
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'latent_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'latent_flushed', '1' );
	}
} );
add_action( 'switch_theme', function () {
	delete_option( 'latent_flushed' );
} );
