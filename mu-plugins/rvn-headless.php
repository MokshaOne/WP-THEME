<?php
/**
 * Plugin Name: Raveenthiran — Headless bridge
 * Description: Exposes the Work project fields (Client, Role, Year, Location,
 *              Website) on the WordPress REST API so the headless Astro
 *              frontend can read them. The `work` CPT itself is already
 *              REST-enabled by the theme; only its custom meta needs surfacing.
 * Author:      Raveenthiran
 * Version:     1.0.0
 *
 * Install: drop this file into  wp-content/mu-plugins/rvn-headless.php  on the
 * WordPress server (the NAS). mu-plugins auto-activate — no activation needed.
 * After install, GET https://wp.m1o.at/wp-json/wp/v2/work returns a `project`
 * object on every item.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * The project fields to expose. Keys match the theme's `_still_<key>` meta
 * (see still theme functions.php → still_project_fields()). Filterable so the
 * set can be extended without editing this file.
 */
function rvn_headless_project_fields() {
	return apply_filters( 'rvn_headless_project_fields', array(
		'client',
		'role',
		'year',
		'location',
		'website',
	) );
}

/**
 * Register a read-only `project` field on the `work` REST resource. Each value
 * is pulled from the protected `_still_<key>` post meta; empty fields are
 * omitted so the frontend can rely on presence.
 */
add_action( 'rest_api_init', function () {
	register_rest_field( 'work', 'project', array(
		'get_callback' => function ( $post ) {
			$out = array();
			foreach ( rvn_headless_project_fields() as $key ) {
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
