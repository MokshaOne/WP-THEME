<?php
/**
 * Silence — custom post types & taxonomies.
 * sl_project (public, /work), sl_journal (public, /journal),
 * sl_enquiry (private, auto-logged form submissions).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {

	register_post_type( sl_pt(), [
		'labels' => [
			'name'          => __( 'Projects', 'raveenthiran-silence' ),
			'singular_name' => __( 'Project', 'raveenthiran-silence' ),
			'add_new_item'  => __( 'Add project', 'raveenthiran-silence' ),
			'edit_item'     => __( 'Edit project', 'raveenthiran-silence' ),
		],
		'public'       => true,
		'menu_icon'    => 'dashicons-camera',
		'menu_position'=> 5,
		'supports'     => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
		'has_archive'  => 'work',
		'rewrite'      => [ 'slug' => 'work', 'with_front' => false ],
		'show_in_rest' => true,
	] );

	register_taxonomy( sl_tax(), sl_pt(), [
		'labels' => [
			'name'          => __( 'Categories', 'raveenthiran-silence' ),
			'singular_name' => __( 'Category', 'raveenthiran-silence' ),
		],
		'hierarchical' => true,
		'public'       => true,
		'rewrite'      => [ 'slug' => 'work-category', 'with_front' => false ],
		'show_in_rest' => true,
	] );

	register_post_type( sl_jt(), [
		'labels' => [
			'name'          => __( 'Journal', 'raveenthiran-silence' ),
			'singular_name' => __( 'Entry', 'raveenthiran-silence' ),
		],
		'public'       => true,
		'menu_icon'    => 'dashicons-edit-large',
		'menu_position'=> 6,
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'has_archive'  => 'journal',
		'rewrite'      => [ 'slug' => 'journal', 'with_front' => false ],
		'show_in_rest' => true,
	] );

	register_post_type( 'sl_enquiry', [
		'labels' => [
			'name'          => __( 'Enquiries', 'raveenthiran-silence' ),
			'singular_name' => __( 'Enquiry', 'raveenthiran-silence' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'menu_icon'           => 'dashicons-email',
		'menu_position'       => 7,
		'supports'            => [ 'title', 'editor' ],
		'capability_type'     => 'post',
		'capabilities'        => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'        => true,
		'exclude_from_search' => true,
	] );
} );

// Flush rewrites once on theme switch so /work and /journal resolve.
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

// Order the /work archive by menu_order (drag order in admin), then date.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( $q->is_post_type_archive( sl_pt() ) || $q->is_tax( sl_tax() ) ) {
		$q->set( 'posts_per_page', -1 ); // the index is a text list — show the whole collection
		$q->set( 'orderby', [ 'menu_order' => 'ASC', 'date' => 'DESC' ] );
	}
} );
