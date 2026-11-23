<?php
/**
 * Medium-tier features (single release):
 *   #60 Journal / blog CPT + taxonomy
 *   helper for the journal archive URL
 * Loaded by functions.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
	register_post_type( 'nr_journal', [
		'label'        => __( 'Journal', 'raveenthiran' ),
		'labels'       => [ 'singular_name' => __( 'Journal Entry', 'raveenthiran' ), 'add_new_item' => __( 'Add Entry', 'raveenthiran' ) ],
		'public'       => true,
		'has_archive'  => 'journal',
		'rewrite'      => [ 'slug' => 'journal' ],
		'menu_icon'    => 'dashicons-edit',
		'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'show_in_rest' => true,
	] );
	register_taxonomy( 'nr_journal_cat', 'nr_journal', [
		'label'        => __( 'Journal Categories', 'raveenthiran' ),
		'hierarchical' => true,
		'rewrite'      => [ 'slug' => 'journal-category' ],
		'show_in_rest' => true,
	] );
} );

function nr_journal_url() {
	return get_post_type_archive_link( 'nr_journal' ) ?: home_url( '/journal' );
}

/* Rail archives have no pagination UI, so the default 10-per-page limit would
   silently hide older entries. Raise the cap on every rail-rendered archive. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( $q->is_post_type_archive( 'nr_journal' ) || $q->is_tax( 'nr_journal_cat' ) ) {
		$q->set( 'posts_per_page', 60 );
	}
	if ( $q->is_tax( 'nr_project_series' ) || $q->is_tax( 'nr_project_tag' ) || $q->is_tax( 'nr_project_cat' ) ) {
		$q->set( 'posts_per_page', 48 );
	}
} );

/* Distinct project years (newest first) — used by the archive year filter (#61). */
function nr_project_years() {
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ] );
	$years = [];
	foreach ( $ids as $id ) {
		$y = function_exists( 'nr_field' ) ? nr_field( 'project_year', $id ) : get_post_meta( $id, 'project_year', true );
		$y = $y ?: get_the_date( 'Y', $id );
		if ( $y ) $years[ (string) $y ] = true;
	}
	$years = array_keys( $years );
	rsort( $years );
	return $years;
}
