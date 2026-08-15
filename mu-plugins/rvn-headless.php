<?php
/**
 * Plugin Name: Raveenthiran — Headless CMS
 * Description: Self-contained content model for the headless setup: registers
 *              the Work custom post type, the Work Categories taxonomy, the
 *              "Project details" meta box, and exposes the project fields on
 *              the REST API. Theme-independent — the WordPress install on the
 *              NAS can run any theme (or the default one); the frontend lives
 *              in the separate Astro project.
 * Author:      Raveenthiran
 * Version:     2.0.0
 *
 * Install: drop this file into  wp-content/mu-plugins/rvn-headless.php  on the
 * NAS. mu-plugins auto-activate. After installing, switch the site theme to a
 * default/minimal theme and save Settings → Permalinks once (rewrite flush).
 *
 * Meta keys stay `_still_<field>` so existing project data entered under the
 * Still theme is preserved.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Custom post type: Work (portfolio) + Categories taxonomy ── */
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
			'labels'            => array( 'name' => __( 'Categories', 'rvn' ), 'singular_name' => __( 'Category', 'rvn' ) ),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work-category', 'with_front' => false ),
		) );
	}
} );

/* ── Project fields (native meta box, no ACF). Keys => labels. ── */
function rvn_project_fields() {
	return apply_filters( 'rvn_project_fields', array(
		'client'   => __( 'Client', 'rvn' ),
		'role'     => __( 'Role', 'rvn' ),
		'year'     => __( 'Year', 'rvn' ),
		'location' => __( 'Location', 'rvn' ),
		'website'  => __( 'Website', 'rvn' ),
	) );
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'rvn_project', __( 'Project details', 'rvn' ), 'rvn_project_box', 'work', 'side', 'high' );
} );

function rvn_project_box( $post ) {
	wp_nonce_field( 'rvn_project_save', 'rvn_project_nonce' );
	echo '<div style="display:grid;gap:.7rem">';
	foreach ( rvn_project_fields() as $key => $label ) {
		$val = get_post_meta( $post->ID, '_still_' . $key, true );
		printf(
			'<label style="display:block;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#777">%s<input type="text" name="rvn_%s" value="%s" style="width:100%%;margin-top:4px"></label>',
			esc_html( $label ), esc_attr( $key ), esc_attr( $val )
		);
	}
	echo '</div>';
}

add_action( 'save_post_work', function ( $post_id ) {
	if ( ! isset( $_POST['rvn_project_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['rvn_project_nonce'] ) ), 'rvn_project_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	foreach ( array_keys( rvn_project_fields() ) as $key ) {
		$raw = isset( $_POST[ 'rvn_' . $key ] ) ? wp_unslash( $_POST[ 'rvn_' . $key ] ) : '';
		$val = ( 'website' === $key ) ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
		if ( '' === $val ) { delete_post_meta( $post_id, '_still_' . $key ); }
		else { update_post_meta( $post_id, '_still_' . $key, $val ); }
	}
} );

/* ── Expose the project fields on the REST API for the headless frontend.
   GET /wp-json/wp/v2/work returns a `project` object on every item. ── */
add_action( 'rest_api_init', function () {
	register_rest_field( 'work', 'project', array(
		'get_callback' => function ( $post ) {
			$out = array();
			foreach ( array_keys( rvn_project_fields() ) as $key ) {
				$value = get_post_meta( $post['id'], '_still_' . $key, true );
				if ( '' !== $value && null !== $value ) {
					$out[ $key ] = $value;
				}
			}
			return $out;
		},
		'schema' => array(
			'description' => 'Structured project details (client, role, year, location, website).',
			'type'        => 'object',
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );
} );
