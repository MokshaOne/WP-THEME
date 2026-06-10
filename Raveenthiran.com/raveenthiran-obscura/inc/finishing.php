<?php
/**
 * inc/finishing.php — IDEAS-50-NEXT, small batch 1 (v4.56.0).
 * #37 heuristic auto-alt · #5 per-project contact-sheet PDF · #39 image-sitemap
 * EXIF captions · #20 "surprise me" random project · #8/#13/#19/#46/#48 are
 * front-end (theme.js / theme.css). No payments, no external services.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── #37 — heuristic auto-alt when an image has none (no AI, no subscription) ──
   Uses the attachment title, falling back to the parent project's title, plus a
   camera hint from EXIF. Never overrides a real, human-written alt. */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $att ) {
	if ( isset( $attr['alt'] ) && trim( (string) $attr['alt'] ) !== '' ) return $attr;
	$title = trim( (string) get_the_title( $att->ID ) );
	// a filename-ish title ("DSC_1234", "img-3.jpg") is useless → use the parent
	if ( $title === '' || preg_match( '/(\.(jpe?g|png|webp|avif)$)|([a-z]{2,4}[_-]?\d{3,})/i', $title ) ) {
		$parent = wp_get_post_parent_id( $att->ID );
		$ptitle = $parent ? trim( (string) get_the_title( $parent ) ) : '';
		$title  = $ptitle ?: '';
	}
	$cam = '';
	if ( function_exists( 'nr_get_exif' ) ) { $ex = nr_get_exif( $att->ID ); $cam = trim( (string) ( $ex['camera'] ?? '' ) ); }
	$alt = $title !== '' ? $title : ( get_bloginfo( 'name' ) . ' — ' . __( 'photograph', 'raveenthiran' ) );
	if ( $cam && $title !== '' ) $alt .= ' · ' . $cam;
	$attr['alt'] = $alt;
	return $attr;
}, 9, 2 );

/* ── #5 — per-project contact-sheet PDF at /nr-contactsheet/<id>.pdf ──
   Reuses the memory-safe JPEG-passthrough writer (nr_stream_jpeg_pdf, mediumwins2). */
add_action( 'template_redirect', function () {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#/nr-contactsheet/(\d+)\.pdf$#', $path, $m ) ) return;
	$id = (int) $m[1];
	if ( get_post_type( $id ) !== 'nr_project' || ! function_exists( 'nr_stream_jpeg_pdf' ) ) { status_header( 404 ); exit; }
	$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery', $id ) : [];
	$jpegs = [];
	if ( is_array( $gallery ) ) {
		foreach ( $gallery as $g ) {
			$aid = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : ( is_numeric( $g ) ? (int) $g : 0 );
			if ( ! $aid || wp_attachment_is( 'video', $aid ) ) continue;
			$f = get_attached_file( $aid );
			if ( $f && preg_match( '/\.jpe?g$/i', $f ) && file_exists( $f ) ) $jpegs[] = $f;
		}
	}
	if ( ! $jpegs && has_post_thumbnail( $id ) ) {
		$f = get_attached_file( get_post_thumbnail_id( $id ) );
		if ( $f && preg_match( '/\.jpe?g$/i', $f ) ) $jpegs[] = $f;
	}
	if ( ! $jpegs ) { status_header( 404 ); exit; }
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: inline; filename="' . sanitize_title( get_the_title( $id ) ) . '-contact-sheet.pdf"' );
	nr_stream_jpeg_pdf( array_slice( $jpegs, 0, 24 ), get_the_title( $id ) );
	exit;
}, 0 );
function nr_contactsheet_url( $id = 0 ) { $id = $id ?: get_the_ID(); return home_url( '/nr-contactsheet/' . (int) $id . '.pdf' ); }

/* ── #20 — "surprise me": /?nr_random=1 → redirect to a random project ── */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['nr_random'] ) ) return;
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'orderby' => 'rand', 'fields' => 'ids', 'no_found_rows' => true ] );
	wp_safe_redirect( $ids ? get_permalink( $ids[0] ) : get_post_type_archive_link( 'nr_project' ) );
	exit;
} );
