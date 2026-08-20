<?php
/**
 * Raveenthiran Headless — content model + normalized REST contract.
 *
 * This theme renders no front end (see index.php). It registers the Work CPT,
 * the Album (work_category) and Service (work_service) taxonomies, and exposes
 * `project`, `gallery` and `seo` REST fields that read ACF first and fall back
 * to legacy meta / attached media. The ACF field group auto-loads from the
 * theme's acf-json/ folder.
 *
 * @package RaveenthiranHeadless
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Post type + taxonomies ─────────────────────────────────────────────── */
add_action( 'init', function () {

	if ( ! post_type_exists( 'work' ) ) {
		register_post_type( 'work', array(
			'labels' => array(
				'name'          => __( 'Work', 'rvn' ),
				'singular_name' => __( 'Project', 'rvn' ),
				'add_new_item'  => __( 'Add New Project', 'rvn' ),
				'edit_item'     => __( 'Edit Project', 'rvn' ),
				'all_items'     => __( 'All Work', 'rvn' ),
				'menu_name'     => __( 'Work', 'rvn' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-camera-alt',
			'menu_position' => 5,
			'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		) );
	}

	if ( ! taxonomy_exists( 'work_category' ) ) {
		register_taxonomy( 'work_category', 'work', array(
			'labels'            => array( 'name' => __( 'Albums', 'rvn' ), 'singular_name' => __( 'Album', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'album', 'with_front' => false ),
		) );
	}

	if ( ! taxonomy_exists( 'work_service' ) ) {
		register_taxonomy( 'work_service', 'work', array(
			'labels'            => array( 'name' => __( 'Services', 'rvn' ), 'singular_name' => __( 'Service', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service', 'with_front' => false ),
		) );
	}
} );

/* Flush rewrite rules once when the theme is activated (so /work resolves). */
add_action( 'after_switch_theme', function () { flush_rewrite_rules(); } );

/* ── Helpers ────────────────────────────────────────────────────────────── */

/** Read an ACF field if ACF is active, else the legacy `_still_<key>` meta. */
function rvn_field( $post_id, $acf_key, $legacy_key = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $acf_key, $post_id );
		if ( $v !== null && $v !== '' && $v !== false ) { return $v; }
	}
	if ( $legacy_key ) {
		$v = get_post_meta( $post_id, $legacy_key, true );
		if ( $v !== '' ) { return $v; }
	}
	return '';
}

/** Normalize credits into [{role,name,url}]. Accepts an ACF repeater (array of
 *  rows) OR a textarea with one "Role — Name" per line (ACF-free friendly). */
function rvn_credits( $post_id ) {
	$raw = function_exists( 'get_field' ) ? get_field( 'credits', $post_id ) : '';
	$out = array();
	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			$role = isset( $row['role'] ) ? trim( (string) $row['role'] ) : '';
			$name = isset( $row['name'] ) ? trim( (string) $row['name'] ) : '';
			$url  = isset( $row['url'] ) ? trim( (string) $row['url'] ) : '';
			if ( $role || $name ) { $out[] = array( 'role' => $role, 'name' => $name, 'url' => $url ); }
		}
	} elseif ( is_string( $raw ) && $raw !== '' ) {
		foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
			$line = trim( $line );
			if ( $line === '' ) { continue; }
			$parts = preg_split( '/\s*(?:—|–|-|:)\s*/u', $line, 2 );
			$out[] = array( 'role' => trim( $parts[0] ), 'name' => isset( $parts[1] ) ? trim( $parts[1] ) : '', 'url' => '' );
		}
	}
	return $out;
}

/** Build a responsive image record from an attachment id. */
function rvn_image( $id, $size = 'large' ) {
	$src = wp_get_attachment_image_src( $id, $size );
	if ( ! $src ) { return null; }
	return array(
		'src'    => $src[0],
		'w'      => (int) $src[1],
		'h'      => (int) $src[2],
		'srcset' => (string) wp_get_attachment_image_srcset( $id, $size ),
		'alt'    => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
	);
}

/* ── REST contract: project / gallery / seo ─────────────────────────────── */
add_action( 'rest_api_init', function () {

	register_rest_field( 'work', 'project', array(
		'get_callback' => function ( $post ) {
			$id = $post['id'];
			$services = array();
			$terms = wp_get_post_terms( $id, 'work_service', array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $terms ) ) { $services = array_values( $terms ); }
			return array(
				'client'        => (string) rvn_field( $id, 'client',   '_still_client' ),
				'year'          => (string) rvn_field( $id, 'year',     '_still_year' ),
				'location'      => (string) rvn_field( $id, 'location', '_still_location' ),
				'website'       => (string) rvn_field( $id, 'website',  '_still_website' ),
				'services'      => $services,
				'credits'       => rvn_credits( $id ),
				'featured_home' => (bool) ( function_exists( 'get_field' ) ? get_field( 'featured_home', $id ) : false ),
			);
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'gallery', array(
		'get_callback' => function ( $post ) {
			$featured = (int) get_post_thumbnail_id( $post['id'] );
			$images   = get_attached_media( 'image', $post['id'] );
			$out = array();
			foreach ( $images as $img ) {
				if ( (int) $img->ID === $featured ) { continue; }
				$rec = rvn_image( $img->ID, 'large' );
				if ( $rec ) { $out[] = $rec; }
			}
			return $out;
		},
		'schema' => array( 'type' => 'array', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );

	register_rest_field( 'work', 'seo', array(
		'get_callback' => function ( $post ) {
			$desc = (string) rvn_field( $post['id'], 'seo_description' );
			$ogid = function_exists( 'get_field' ) ? get_field( 'og_image', $post['id'] ) : 0;
			if ( is_array( $ogid ) && isset( $ogid['id'] ) ) { $ogid = $ogid['id']; }
			$og = '';
			if ( $ogid ) { $rec = rvn_image( (int) $ogid, 'large' ); $og = $rec ? $rec['src'] : ''; }
			return array( 'description' => $desc, 'og_image' => $og );
		},
		'schema' => array( 'type' => 'object', 'context' => array( 'view', 'edit', 'embed' ) ),
	) );
} );

/* ── Tidy admin: no theme customizer/front-end noise for a headless install ── */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
} );
