<?php
/**
 * Silence — activation routine.
 * On theme switch: create the About & Enquire pages with their templates
 * assigned. Idempotent — existing pages are reused, never duplicated.
 * (No settings import needed: Silence reads the same nr_* options and
 * the same project/journal content Obscura uses, so everything is
 * already there the moment the theme activates.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_switch_theme', function () {
	$made = [ 'pages' => [] ];

	$pages = [
		'page-about.php'   => [ 'title' => __( 'About', 'raveenthiran-silence' ),   'slug' => 'about' ],
		'page-enquire.php' => [ 'title' => __( 'Enquire', 'raveenthiran-silence' ), 'slug' => 'enquire' ],
	];
	foreach ( $pages as $template => $p ) {
		// a page already using this template? nothing to do.
		$existing = get_pages( [ 'meta_key' => '_wp_page_template', 'meta_value' => $template, 'number' => 1, 'post_status' => 'publish,draft' ] );
		if ( ! empty( $existing ) ) continue;

		// a page with the slug (e.g. created for Obscura)? just assign the template.
		$by_slug = get_page_by_path( $p['slug'] );
		if ( $by_slug instanceof WP_Post ) {
			update_post_meta( $by_slug->ID, '_wp_page_template', $template );
			$made['pages'][] = $p['title'] . ' (' . __( 'template assigned to existing page', 'raveenthiran-silence' ) . ')';
			continue;
		}

		$id = wp_insert_post( [
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $p['title'],
			'post_name'   => $p['slug'],
		] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $template );
			$made['pages'][] = $p['title'];
		}
	}

	set_transient( 'nr_sil_activation_notice', $made, 5 * MINUTE_IN_SECONDS );
} );

add_action( 'admin_notices', function () {
	$made = get_transient( 'nr_sil_activation_notice' );
	if ( ! $made || ! current_user_can( 'manage_options' ) ) return;
	delete_transient( 'nr_sil_activation_notice' );

	$bits   = [];
	$bits[] = $made['pages']
		? sprintf( __( 'Pages ready: %s.', 'raveenthiran-silence' ), implode( ', ', array_map( 'esc_html', $made['pages'] ) ) )
		: __( 'About & Enquire pages were already set up.', 'raveenthiran-silence' );
	$bits[] = __( 'Your Obscura projects, journal, galleries, and settings are shared — they are already live at /work and /journal.', 'raveenthiran-silence' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>Silence</strong> — %s %s</p></div>',
		implode( ' ', $bits ),
		esc_html__( 'If /work does not resolve yet: Settings → Permalinks → Save.', 'raveenthiran-silence' )
	);
} );
