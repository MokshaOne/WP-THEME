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

/* ── Expose project fields + gallery on the REST API for the headless frontend.
   GET /wp-json/wp/v2/work returns `project` (details) and `gallery` (images). ── */
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

	register_rest_field( 'work', 'gallery', array(
		'get_callback' => function ( $post ) {
			$featured = (int) get_post_thumbnail_id( $post['id'] );
			$images   = get_attached_media( 'image', $post['id'] );
			$out = array();
			foreach ( $images as $img ) {
				if ( (int) $img->ID === $featured ) { continue; } // featured shown as hero
				$src = wp_get_attachment_image_src( $img->ID, 'large' );
				if ( ! $src ) { continue; }
				$out[] = array(
					'src'    => $src[0],
					'w'      => (int) $src[1],
					'h'      => (int) $src[2],
					'srcset' => (string) wp_get_attachment_image_srcset( $img->ID, 'large' ),
				);
			}
			return $out;
		},
		'schema' => array(
			'description' => 'Additional images attached to the project (excludes the featured image).',
			'type'        => 'array',
			'context'     => array( 'view', 'edit', 'embed' ),
		),
	) );
} );

/* ── GitHub <-> WordPress: rebuild the Astro site when content changes ───────
 *
 * The frontend is built by GitHub Actions
 * (.github/workflows/deploy-frontend.yml), which pulls this site's REST API at
 * build time. Without a hook, new content waits for the nightly 04:00 UTC
 * rebuild; this fires a repository_dispatch instead, so a publish goes live in
 * a couple of minutes.
 *
 * Setup, once:
 *   1. GitHub -> Settings -> Developer settings -> Fine-grained token, limited
 *      to this repository, permission "Contents: Read and write".
 *   2. Store it on the WordPress side, either in wp-config.php:
 *          define( 'RVN_GITHUB_TOKEN', 'github_pat_...' );
 *      or in the database:
 *          wp option update rvn_github_token 'github_pat_...'
 *   3. Optional overrides: RVN_GITHUB_REPO (default MokshaOne/WP-THEME) and
 *      RVN_GITHUB_EVENT (default wp-content-updated).
 *
 * No token configured -> every call is a silent no-op and the cron still runs.
 */

function rvn_github_option( $const, $option, $default ) {
	if ( defined( $const ) && constant( $const ) ) { return constant( $const ); }
	$value = get_option( $option );
	return $value ? $value : $default;
}

function rvn_dispatch_github_rebuild( $reason = '' ) {
	$token = rvn_github_option( 'RVN_GITHUB_TOKEN', 'rvn_github_token', '' );
	if ( ! $token ) { return false; }

	/* At most one rebuild per minute: a bulk save would otherwise fire dozens
	   of dispatches that cancel each other through the workflow concurrency. */
	if ( get_transient( 'rvn_github_dispatch' ) ) { return false; }
	set_transient( 'rvn_github_dispatch', 1, 60 );

	$repo  = rvn_github_option( 'RVN_GITHUB_REPO', 'rvn_github_repo', 'MokshaOne/WP-THEME' );
	$event = rvn_github_option( 'RVN_GITHUB_EVENT', 'rvn_github_event', 'wp-content-updated' );

	$response = wp_remote_post(
		'https://api.github.com/repos/' . $repo . '/dispatches',
		array(
			'timeout'  => 5,
			'blocking' => false,
			'headers'  => array(
				'Authorization'        => 'Bearer ' . $token,
				'Accept'               => 'application/vnd.github+json',
				'X-GitHub-Api-Version' => '2022-11-28',
				'User-Agent'           => 'rvn-headless-wordpress',
				'Content-Type'         => 'application/json',
			),
			'body'     => wp_json_encode(
				array(
					'event_type'     => $event,
					'client_payload' => array(
						'source' => 'wordpress',
						'site'   => home_url(),
						'reason' => $reason,
					),
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		error_log( '[rvn] GitHub dispatch failed: ' . $response->get_error_message() );
		return false;
	}
	return true;
}

/* Publishing or unpublishing anything the site renders triggers a rebuild. */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( ! $post instanceof WP_Post ) { return; }
	if ( ! in_array( $post->post_type, array( 'work', 'post', 'page' ), true ) ) { return; }
	if ( ( 'publish' === $old_status ) === ( 'publish' === $new_status ) ) { return; }
	rvn_dispatch_github_rebuild( $post->post_type . ':' . $post->ID . ' ' . $old_status . '->' . $new_status );
}, 10, 3 );

/* Deleting live content changes the site too. */
add_action( 'before_delete_post', function ( $post_id ) {
	$post = get_post( $post_id );
	if ( $post && in_array( $post->post_type, array( 'work', 'post', 'page' ), true ) ) {
		rvn_dispatch_github_rebuild( $post->post_type . ':' . $post_id . ' deleted' );
	}
} );
