<?php
/**
 * #63 — Series / collections.
 *
 * A flat `nr_project_series` taxonomy that groups projects into named bodies
 * of work (e.g. "The Vienna Notebook", "Nightshift"). Surfaces as:
 *   • a meta row + a "More from this series" nav on the project page,
 *   • a /series/<slug> archive (the standard taxonomy archive),
 *   • a CollectionPage hint is left to WP's default archive.
 *
 * Helpers are guarded so templates can call them safely.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
	register_taxonomy( 'nr_project_series', 'nr_project', [
		'labels'       => [
			'name'          => __( 'Series', 'raveenthiran' ),
			'singular_name' => __( 'Series', 'raveenthiran' ),
			'add_new_item'  => __( 'Add series', 'raveenthiran' ),
			'menu_name'     => __( 'Series', 'raveenthiran' ),
		],
		'hierarchical'      => false,
		'public'            => true,
		'rewrite'           => [ 'slug' => 'series' ],
		'show_in_rest'      => true,
		'show_admin_column' => true,
	] );
} );

/* The (first) series term for a project, or null. */
function nr_project_series( $post_id ) {
	$terms = get_the_terms( $post_id, 'nr_project_series' );
	return ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : null;
}

/* Sibling projects in the same series (excluding the current one). */
function nr_series_siblings( $post_id, $limit = 6 ) {
	$term = nr_project_series( $post_id );
	if ( ! $term ) return [];
	return get_posts( [
		'post_type'      => 'nr_project',
		'posts_per_page' => (int) $limit,
		'post__not_in'   => [ (int) $post_id ],
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'ASC' ],
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'tax_query'      => [ [
			'taxonomy' => 'nr_project_series',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		] ],
	] );
}
