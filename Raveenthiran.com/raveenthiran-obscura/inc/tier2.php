<?php
/**
 * Tier 2 — back-end features that are safe to ship blind (no settings UI,
 * no external keys, no re-platform). Front-end pieces (command palette,
 * contact-sheet index, hero parallax) live in assets/js + assets/css.
 * Loaded by functions.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────────────────────────────
 * #43 Auto-fill project_year from a photo's EXIF date on upload.
 * Best-effort: only when the upload is attached to a project and the
 * year isn't already set.
 * ───────────────────────────────────────────────────────────── */
add_action( 'add_attachment', function ( $att_id ) {
	$parent = (int) get_post_field( 'post_parent', $att_id );
	if ( ! $parent || get_post_type( $parent ) !== 'nr_project' ) return;
	$existing = function_exists( 'nr_field' ) ? nr_field( 'project_year', $parent ) : get_post_meta( $parent, 'project_year', true );
	if ( $existing ) return;
	$file = get_attached_file( $att_id );
	if ( ! $file || ! function_exists( 'exif_read_data' ) || get_post_mime_type( $att_id ) !== 'image/jpeg' ) return;
	$e  = @exif_read_data( $file );
	$dt = $e['DateTimeOriginal'] ?? ( $e['DateTime'] ?? '' );
	$yr = $dt ? substr( $dt, 0, 4 ) : '';
	if ( ctype_digit( $yr ) ) update_post_meta( $parent, 'project_year', $yr );
} );

/* ─────────────────────────────────────────────────────────────
 * #45 Admin dashboard widget — counts + latest enquiries + quick links.
 * ───────────────────────────────────────────────────────────── */
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'nr_studio_overview', __( 'Studio overview', 'raveenthiran' ), function () {
		$proj = (int) ( wp_count_posts( 'nr_project' )->publish ?? 0 );
		$enq  = post_type_exists( 'nr_enquiry' ) ? (int) ( wp_count_posts( 'nr_enquiry' )->publish ?? 0 ) : 0;
		echo '<p style="font-size:13px"><strong style="font-size:22px">' . esc_html( $proj ) . '</strong> ' . esc_html__( 'projects', 'raveenthiran' )
		   . ' &nbsp;·&nbsp; <strong style="font-size:22px">' . esc_html( $enq ) . '</strong> ' . esc_html__( 'enquiries', 'raveenthiran' ) . '</p>';
		$recent = get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 5 ] );
		if ( $recent ) {
			echo '<ul style="margin:8px 0">';
			foreach ( $recent as $r ) {
				printf(
					'<li style="display:flex;justify-content:space-between;gap:10px;border-bottom:1px solid #eee;padding:5px 0"><a href="%s">%s</a><span style="color:#888">%s</span></li>',
					esc_url( get_edit_post_link( $r->ID ) ),
					esc_html( get_the_title( $r ) ?: get_post_meta( $r->ID, '_nr_email', true ) ),
					esc_html( get_the_date( 'M j', $r ) )
				);
			}
			echo '</ul>';
		}
		printf( '<p><a class="button" href="%s">%s</a> <a class="button" href="%s">%s</a></p>',
			esc_url( admin_url( 'edit.php?post_type=nr_project' ) ), esc_html__( 'Projects', 'raveenthiran' ),
			esc_url( admin_url( 'edit.php?post_type=nr_enquiry' ) ), esc_html__( 'Enquiries', 'raveenthiran' ) );
	} );
} );

/* ─────────────────────────────────────────────────────────────
 * #76 Login-attempt limiter — 5 tries per IP per 15 minutes.
 * ───────────────────────────────────────────────────────────── */
function nr_login_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'nr_la_' . md5( $ip );
}
add_filter( 'authenticate', function ( $user, $username ) {
	if ( $username === '' ) return $user;
	if ( (int) get_transient( nr_login_key() ) >= 5 ) {
		return new WP_Error( 'nr_locked', __( 'Too many failed attempts. Try again in 15 minutes.', 'raveenthiran' ) );
	}
	return $user;
}, 30, 2 );
add_action( 'wp_login_failed', function () {
	$k = nr_login_key();
	set_transient( $k, ( (int) get_transient( $k ) ) + 1, 15 * MINUTE_IN_SECONDS );
} );
add_action( 'wp_login', function () { delete_transient( nr_login_key() ); } );

/* ─────────────────────────────────────────────────────────────
 * #78 Review schema from testimonials (honest — no fabricated ratings).
 * Linked to the existing Person @id from inc/seo.php.
 * ───────────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! ( is_front_page() || is_page() ) ) return;
	$ts = get_posts( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 10 ] );
	$reviews = [];
	foreach ( $ts as $p ) {
		$body = trim( wp_strip_all_tags( get_post_field( 'post_content', $p ) ) );
		if ( $body === '' ) continue;
		$reviews[] = [ '@type' => 'Review', 'reviewBody' => $body, 'author' => [ '@type' => 'Person', 'name' => get_the_title( $p ) ] ];
	}
	if ( ! $reviews ) return;
	$schema = [ '@context' => 'https://schema.org', '@type' => 'Person', '@id' => get_site_url() . '/#person', 'review' => $reviews ];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 9 );

/* ─────────────────────────────────────────────────────────────
 * #79 ImageGallery schema on single project pages.
 * ───────────────────────────────────────────────────────────── */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'nr_project' ) ) return;
	$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : [];
	$urls = [];
	if ( is_array( $gallery ) ) {
		foreach ( $gallery as $g ) {
			if ( is_array( $g ) && ! empty( $g['url'] ) ) $urls[] = $g['url'];
			elseif ( is_numeric( $g ) ) { $u = wp_get_attachment_image_url( (int) $g, 'nr-hero' ); if ( $u ) $urls[] = $u; }
		}
	}
	if ( ! $urls && has_post_thumbnail() ) $urls[] = get_the_post_thumbnail_url( get_the_ID(), 'nr-hero' );
	if ( ! $urls ) return;
	$schema = [ '@context' => 'https://schema.org', '@type' => 'ImageGallery', 'name' => get_the_title(), 'url' => get_permalink(), 'image' => array_values( array_unique( $urls ) ) ];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 9 );
