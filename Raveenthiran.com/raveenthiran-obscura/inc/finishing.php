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

/* ── #45 — testimonial video field + [nr_testimonial_videos] embed band ── */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
	acf_add_local_field_group( [
		'key' => 'group_nr_testi_video', 'title' => 'Testimonial video',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_testimonial' ] ] ],
		'fields' => [ [ 'key' => 'field_t_video', 'name' => 'client_video', 'label' => 'Video URL (YouTube/Vimeo/.mp4)', 'type' => 'url' ] ],
	] );
	acf_add_local_field_group( [
		'key' => 'group_nr_longdesc', 'title' => 'Long description (a11y)',
		'location' => [ [ [ 'param' => 'attachment', 'operator' => '==', 'value' => 'all' ] ] ],
		'fields' => [ [ 'key' => 'field_longdesc', 'name' => 'nr_longdesc', 'label' => 'Long description', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Detailed description for complex editorial frames (screen readers).' ] ],
	] );
} );
add_shortcode( 'nr_testimonial_videos', function () {
	$q = new WP_Query( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 12, 'no_found_rows' => true ] );
	$out = '';
	while ( $q->have_posts() ) { $q->the_post();
		$u = function_exists( 'get_field' ) ? trim( (string) get_field( 'client_video' ) ) : '';
		if ( ! $u ) continue;
		$embed = wp_oembed_get( $u );
		if ( ! $embed && preg_match( '/\.mp4$/i', $u ) ) $embed = '<video controls preload="metadata" playsinline src="' . esc_url( $u ) . '"></video>';
		if ( ! $embed ) continue;
		$out .= '<figure class="nr-testi-vid">' . $embed . '<figcaption>' . esc_html( get_the_title() ) . '</figcaption></figure>';
	}
	wp_reset_postdata();
	return $out ? '<div class="nr-testi-vids">' . $out . '</div>' : '';
} );

/* ── #47 — apply per-image long description via aria-describedby ── */
add_filter( 'wp_get_attachment_image', function ( $html, $att_id ) {
	$ld = function_exists( 'get_field' ) ? trim( (string) get_field( 'nr_longdesc', $att_id ) ) : '';
	if ( $ld === '' || strpos( $html, 'aria-describedby' ) !== false ) return $html;
	$id = 'nr-ld-' . $att_id;
	$html = preg_replace( '/<img /', '<img aria-describedby="' . esc_attr( $id ) . '" ', $html, 1 );
	return $html . '<span id="' . esc_attr( $id ) . '" class="nr-sr-only">' . esc_html( $ld ) . '</span>';
}, 13, 2 );

/* ── #40 — [nr_featured] "as featured in" press-logo band ── */
add_shortcode( 'nr_featured', function () {
	if ( ! function_exists( 'get_field' ) ) return '';
	$rows = get_field( 'press_list', 'option' );
	if ( ! is_array( $rows ) ) return '';
	$logos = [];
	foreach ( $rows as $r ) {
		$logo = $r['pr_logo'] ?? null; $url = $r['pr_url'] ?? '';
		$src = is_array( $logo ) ? ( $logo['sizes']['medium'] ?? $logo['url'] ?? '' ) : '';
		if ( ! $src ) continue;
		$img = '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $r['medium'] ?? '' ) . '" loading="lazy" decoding="async" height="30">';
		$logos[] = $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $img . '</a>' : $img;
	}
	if ( ! $logos ) return '';
	return '<div class="nr-featured"><span class="nr-featured__label">' . esc_html__( 'As featured in', 'raveenthiran' ) . '</span><div class="nr-featured__row">' . implode( '', $logos ) . '</div></div>';
} );
