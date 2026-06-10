<?php
/**
 * inc/mediumwins2.php — IDEAS-200 "Medium" tier, release 2 (v4.49.0).
 * #42 wizard (opt-in) · #48 A/B CTA · #60 delivery template support · #62 case
 * study · #72 double opt-in · #82 art-directed picture · #83/#10 variable font ·
 * #124 lookbook PDF (memory-safe) · #127 focal point · #131 story mode ·
 * #149 bulk alt · #153 editorial calendar · #160 sample content · #187 uptime
 * cron · #191 settings diff. (#34/#87 skipped — see IDEAS-200.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ===== fields ===== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
	acf_add_local_field_group( [
		'key' => 'group_nr_m2_project', 'title' => 'Project — case study',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
		'fields' => [
			[ 'key' => 'field_project_case', 'label' => 'Case study (Problem | Approach | Result)', 'name' => 'project_case', 'type' => 'textarea', 'rows' => 4,
			  'instructions' => 'Three lines: "Problem | …", "Approach | …", "Result | …". Shown as a structured section.' ], // #62
		],
	] );
	acf_add_local_field_group( [
		'key' => 'group_nr_delivery', 'title' => 'Delivery',
		'location' => [ [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-delivery.php' ] ] ],
		'fields' => [
			[ 'key' => 'field_delivery_gallery', 'label' => 'Files / images', 'name' => 'delivery_gallery', 'type' => 'gallery', 'return_format' => 'array' ],
			[ 'key' => 'field_delivery_expiry',  'label' => 'Expires (YYYY-MM-DD)', 'name' => 'delivery_expiry', 'type' => 'text' ],
			[ 'key' => 'field_delivery_note',    'label' => 'Note to client', 'name' => 'delivery_note', 'type' => 'textarea', 'rows' => 2 ],
		],
	] ); // #60 (pair with WP's native page password for access control)
} );

/* #62 — case-study section. */
function nr_project_case_markup( $id = 0 ) {
	$id = $id ?: get_the_ID();
	$raw = function_exists( 'nr_field_str' ) ? nr_field_str( 'project_case', $id ) : '';
	if ( $raw === '' ) return '';
	$out = '<section class="nr-case"><span class="nr-eyebrow nr-eyebrow--xs">' . esc_html__( 'Case study', 'raveenthiran' ) . '</span><dl>';
	foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) as $l ) {
		$p = array_map( 'trim', explode( '|', $l, 2 ) );
		if ( count( $p ) === 2 ) $out .= '<div><dt>' . esc_html( $p[0] ) . '</dt><dd>' . esc_html( $p[1] ) . '</dd></div>';
	}
	return $out . '</dl></section>';
}

/* #131 — story mode: add the choice to the rhythm select (filter ACF). */
add_filter( 'acf/load_field/name=project_rhythm', function ( $f ) {
	if ( is_array( $f['choices'] ?? null ) && ! isset( $f['choices']['story'] ) ) $f['choices']['story'] = 'Story (vertical, captioned)';
	return $f;
} );

/* #127 — focal point per attachment (media edit field) applied in the rail. */
add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
	$fields['nr_focal'] = [
		'label' => __( 'Focal point (Obscura)', 'raveenthiran' ),
		'input' => 'text',
		'value' => get_post_meta( $post->ID, '_nr_focal', true ),
		'helps' => __( 'CSS object-position, e.g. "50% 30%". Used for crops in rails/cards.', 'raveenthiran' ),
	];
	return $fields;
}, 10, 2 );
add_filter( 'attachment_fields_to_save', function ( $post, $att ) {
	if ( isset( $att['nr_focal'] ) ) update_post_meta( $post['ID'], '_nr_focal', sanitize_text_field( $att['nr_focal'] ) );
	return $post;
}, 10, 2 );
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $att ) {
	$f = get_post_meta( $att->ID, '_nr_focal', true );
	if ( $f ) $attr['style'] = ( $attr['style'] ?? '' ) . 'object-position:' . esc_attr( $f ) . ';';
	return $attr;
}, 10, 2 );

/* #82 — art-directed <picture>: portrait crop for narrow portrait screens on hero images. */
add_filter( 'wp_get_attachment_image', function ( $html, $id, $size ) {
	if ( $size !== 'nr-hero' || strpos( $html, '<picture' ) === false || strpos( $html, 'data-nr-art' ) !== false ) return $html;
	$src = wp_get_attachment_image_src( $id, 'nr-card' );
	if ( ! $src ) return $html;
	$s = '<source media="(max-width:600px) and (orientation:portrait)" srcset="' . esc_url( $src[0] ) . '" data-nr-art>';
	return preg_replace( '/<picture[^>]*>/', '$0' . $s, $html, 1 );
}, 12, 3 );

/* #83 / #10 — variable font: if assets/fonts/inter-tight-var.woff2 exists, declare
   it last (it wins all weights) + flag the body so the axis-morph CSS engages. */
function nr_varfont_path() { return get_template_directory() . '/assets/fonts/inter-tight-var.woff2'; }
add_action( 'wp_head', function () {
	if ( ! file_exists( nr_varfont_path() ) ) return;
	$u = get_template_directory_uri() . '/assets/fonts/inter-tight-var.woff2';
	echo '<style id="nr-varfont">@font-face{font-family:\'Inter Tight\';font-weight:100 900;font-display:swap;src:url(' . esc_url( $u ) . ') format(\'woff2\')}</style>' . "\n";
}, 3 );
add_filter( 'body_class', function ( $c ) { if ( file_exists( nr_varfont_path() ) ) $c[] = 'nr-varfont'; return $c; } );

/* #48 — A/B hero CTA: variant B label + impression/click counters. */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/ab', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ( $r ) {
		$k = sanitize_key( (string) $r->get_param( 'k' ) );
		if ( ! in_array( $k, [ 'a_view', 'a_click', 'b_view', 'b_click' ], true ) ) return new WP_REST_Response( [ 'ok' => false ], 200 );
		$d = get_option( 'nr_ab', [] ); if ( ! is_array( $d ) ) $d = [];
		$d[ $k ] = (int) ( $d[ $k ] ?? 0 ) + 1; update_option( 'nr_ab', $d, false );
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	} ] );
} );
add_action( 'wp_footer', function () {
	$b = trim( (string) nr_opt( 'nr_cta_view_b', '' ) );
	if ( $b === '' || ! is_front_page() ) return;
	echo '<script type="application/json" id="nr-ab">' . wp_json_encode( [ 'b' => $b ] ) . '</script>';
}, 5 );

/* #72 — newsletter double opt-in: pending until the confirm link is clicked. */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	// downgrade fresh private subscribers to pending + send the confirm email
	if ( $post->post_type !== 'nr_subscriber' || $new !== 'private' || $old === 'pending' ) return;
	if ( get_post_meta( $post->ID, '_nr_confirmed', true ) ) return;
	$tok = wp_generate_password( 24, false );
	update_post_meta( $post->ID, '_nr_confirm_token', $tok );
	wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'pending' ] );
	$link = admin_url( 'admin-post.php?action=nr_news_confirm&s=' . $post->ID . '&t=' . $tok );
	wp_mail( $post->post_title, sprintf( __( 'Please confirm — %s', 'raveenthiran' ), get_bloginfo( 'name' ) ),
		sprintf( __( "One click and you're in:\n\n%s\n\nIf you didn't request this, ignore this email.", 'raveenthiran' ), $link ) );
}, 10, 3 );
add_action( 'admin_post_nopriv_nr_news_confirm', 'nr_news_confirm' );
add_action( 'admin_post_nr_news_confirm', 'nr_news_confirm' );
function nr_news_confirm() {
	$id = (int) ( $_GET['s'] ?? 0 ); $t = sanitize_text_field( wp_unslash( $_GET['t'] ?? '' ) );
	$p = get_post( $id );
	if ( $p && $p->post_type === 'nr_subscriber' && $t && hash_equals( (string) get_post_meta( $id, '_nr_confirm_token', true ), $t ) ) {
		update_post_meta( $id, '_nr_confirmed', gmdate( 'c' ) );
		wp_update_post( [ 'ID' => $id, 'post_status' => 'private' ] );
		wp_mail( $p->post_title, sprintf( __( 'Welcome — %s', 'raveenthiran' ), get_bloginfo( 'name' ) ),
			__( "You're on the list. New work lands here a few times a year — quiet, no noise.\n\nUntil then.", 'raveenthiran' ) );
	}
	wp_safe_redirect( home_url( '/?nr_news=1' ) );
	exit;
}

/* #124 — lookbook PDF per series, memory-safe: JPEGs pass through untouched
   (DCTDecode), one file at a time — no GD decode, tiny peak memory. */
add_action( 'template_redirect', function () {
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#/nr-lookbook/([a-z0-9-]+)\.pdf$#', $path, $m ) ) return;
	$term = get_term_by( 'slug', $m[1], 'nr_project_series' );
	if ( ! $term ) { status_header( 404 ); exit; }
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 20, 'fields' => 'ids', 'no_found_rows' => true,
		'tax_query' => [ [ 'taxonomy' => 'nr_project_series', 'terms' => $term->term_id ] ] ] );
	$jpegs = [];
	foreach ( $ids as $pid ) {
		$f = get_attached_file( get_post_thumbnail_id( $pid ) );
		if ( $f && preg_match( '/\.jpe?g$/i', $f ) && file_exists( $f ) ) $jpegs[] = $f;
	}
	if ( ! $jpegs ) { status_header( 404 ); exit; }
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: inline; filename="' . $m[1] . '-lookbook.pdf"' );
	nr_stream_jpeg_pdf( array_slice( $jpegs, 0, 14 ), $term->name );
	exit;
}, 0 );

function nr_stream_jpeg_pdf( $files, $title ) {
	$w = 595; $h = 842; // A4 portrait pts
	$body = '';
	$objn = 3; $pageObjs = [];
	foreach ( $files as $f ) {
		[ $iw, $ih ] = getimagesize( $f ) ?: [ 1200, 800 ];
		$scale = min( ( $w - 60 ) / $iw, ( $h - 80 ) / $ih );
		$dw = (int) ( $iw * $scale ); $dh = (int) ( $ih * $scale );
		$x = (int) ( ( $w - $dw ) / 2 ); $y = (int) ( ( $h - $dh ) / 2 );
		$img = $objn++; $cont = $objn++; $page = $objn++;
		$pageObjs[] = $page;
		$data = file_get_contents( $f ); // one image in memory at a time
		$body .= "$img 0 obj\n<< /Type /XObject /Subtype /Image /Width $iw /Height $ih /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen( $data ) . " >>\nstream\n$data\nendstream\nendobj\n";
		unset( $data );
		$cs = "q $dw 0 0 $dh $x $y cm /I$img Do Q";
		$body .= "$cont 0 obj\n<< /Length " . strlen( $cs ) . " >>\nstream\n$cs\nendstream\nendobj\n";
		$body .= "$page 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $w $h] /Contents $cont 0 R /Resources << /XObject << /I$img $img 0 R >> >> >>\nendobj\n";
	}
	$kidsStr = implode( ' ', array_map( function ( $p ) { return "$p 0 R"; }, $pageObjs ) );
	$head = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [ $kidsStr ] /Count " . count( $pageObjs ) . " >>\nendobj\n";
	$pdf = $head . $body;
	// xref
	$max = $objn - 1;
	$positions = [];
	for ( $i = 1; $i <= $max; $i++ ) {
		$pos = strpos( $pdf, "\n$i 0 obj" );
		$positions[ $i ] = $pos === false ? strpos( $pdf, "$i 0 obj" ) : $pos + 1;
	}
	$xref = "xref\n0 " . ( $max + 1 ) . "\n0000000000 65535 f \n";
	for ( $i = 1; $i <= $max; $i++ ) $xref .= sprintf( "%010d 00000 n \n", $positions[ $i ] );
	$start = strlen( $pdf );
	$pdf .= $xref . "trailer\n<< /Size " . ( $max + 1 ) . " /Root 1 0 R >>\nstartxref\n$start\n%%EOF";
	echo $pdf;
}

/* Lookbook link on series archives (taxonomy.php shows it via this hook). */
add_action( 'nr_series_cover_after', function ( $term ) {
	if ( ! $term || $term->taxonomy !== 'nr_project_series' ) return;
	printf( '<p><a class="nr-btn" href="%s" target="_blank" rel="noopener"><span>%s</span> <span>↓</span></a></p>',
		esc_url( home_url( '/nr-lookbook/' . $term->slug . '.pdf' ) ), esc_html__( 'Download lookbook (PDF)', 'raveenthiran' ) );
} );

/* #149 — bulk alt editor (Tools page; saves on submit). */
add_action( 'admin_menu', function () {
	add_submenu_page( 'nr-theme-settings', __( 'Alt-text editor', 'raveenthiran' ), __( 'Alt texts', 'raveenthiran' ), 'upload_files', 'nr-alts', function () {
		if ( isset( $_POST['nr_alts'] ) && check_admin_referer( 'nr_alts' ) ) {
			foreach ( (array) $_POST['nr_alts'] as $id => $alt ) update_post_meta( (int) $id, '_wp_attachment_image_alt', sanitize_text_field( wp_unslash( $alt ) ) );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'raveenthiran' ) . '</p></div>';
		}
		$q = new WP_Query( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image', 'posts_per_page' => 40,
			'meta_query' => [ 'relation' => 'OR', [ 'key' => '_wp_attachment_image_alt', 'compare' => 'NOT EXISTS' ], [ 'key' => '_wp_attachment_image_alt', 'value' => '' ] ] ] );
		echo '<div class="wrap"><h1>' . esc_html__( 'Alt-text editor', 'raveenthiran' ) . '</h1><p class="description">' . esc_html__( 'Images with missing alt text. Describe the photo; the filename is shown as a hint.', 'raveenthiran' ) . '</p><form method="post">';
		wp_nonce_field( 'nr_alts' );
		echo '<table class="widefat striped"><tbody>';
		while ( $q->have_posts() ) { $q->the_post(); $id = get_the_ID();
			printf( '<tr><td style="width:90px">%s</td><td><input type="text" name="nr_alts[%d]" value="" style="width:100%%" placeholder="%s"></td></tr>',
				wp_get_attachment_image( $id, [ 80, 80 ] ), $id, esc_attr( get_the_title() ) );
		}
		wp_reset_postdata();
		echo '</tbody></table>';
		submit_button( __( 'Save alt texts', 'raveenthiran' ) );
		echo '</form></div>';
	} );
} );

/* #153 — editorial calendar: journal by month/status. */
add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=nr_journal', __( 'Calendar', 'raveenthiran' ), __( 'Calendar', 'raveenthiran' ), 'edit_posts', 'nr-edcal', function () {
		$q = new WP_Query( [ 'post_type' => 'nr_journal', 'post_status' => [ 'publish', 'future', 'draft', 'pending' ], 'posts_per_page' => 200, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ] );
		$by = [];
		while ( $q->have_posts() ) { $q->the_post(); $by[ get_the_date( 'F Y' ) ][] = [ get_the_ID(), get_post_status(), get_the_date( 'd' ) ]; }
		wp_reset_postdata();
		echo '<div class="wrap"><h1>' . esc_html__( 'Editorial calendar', 'raveenthiran' ) . '</h1>';
		$colors = [ 'publish' => '#2a7', 'future' => '#a06', 'draft' => '#999', 'pending' => '#e0a000' ];
		foreach ( $by as $month => $rows ) {
			echo '<h2 style="margin:18px 0 6px">' . esc_html( $month ) . '</h2><ul style="margin:0">';
			foreach ( $rows as $r ) printf( '<li style="padding:3px 0;border-bottom:1px solid #eee"><span style="display:inline-block;width:26px;color:#888">%s</span> <a href="%s">%s</a> <span style="color:%s;font-size:11px;text-transform:uppercase;letter-spacing:.05em">%s</span></li>',
				esc_html( $r[2] ), esc_url( get_edit_post_link( $r[0] ) ), esc_html( get_the_title( $r[0] ) ), $colors[ $r[1] ] ?? '#999', esc_html( $r[1] ) );
			echo '</ul>';
		}
		echo '</div>';
	} );
} );

/* #160 — sample-content importer (button on Theme Settings → admin-post). */
add_action( 'admin_post_nr_sample_content', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_sample_content' ) ) wp_die( 'no' );
	$made = 0;
	foreach ( [ [ 'Nachtdienst', 'Editorial', '2024' ], [ 'A House in Mariahilf', 'Architecture', '2024' ], [ 'Naschmarkt at Dawn', 'Street', '2023' ] ] as $s ) {
		if ( get_page_by_path( sanitize_title( $s[0] ), OBJECT, 'nr_project' ) ) continue;
		$id = wp_insert_post( [ 'post_type' => 'nr_project', 'post_status' => 'draft', 'post_title' => $s[0], 'post_excerpt' => 'Sample project — replace with real work.' ] );
		if ( $id && ! is_wp_error( $id ) ) { update_post_meta( $id, 'project_year', $s[2] ); wp_set_object_terms( $id, $s[1], 'nr_project_cat' ); $made++; }
	}
	foreach ( [ 'On photographing strangers', 'Notes from a quiet studio' ] as $t ) {
		if ( get_page_by_path( sanitize_title( $t ), OBJECT, 'nr_journal' ) ) continue;
		$id = wp_insert_post( [ 'post_type' => 'nr_journal', 'post_status' => 'draft', 'post_title' => $t, 'post_content' => 'Sample journal entry.' ] );
		if ( $id && ! is_wp_error( $id ) ) $made++;
	}
	wp_safe_redirect( admin_url( 'themes.php?page=nr-theme-settings&nr_sample=' . $made ) );
	exit;
} );
add_action( 'admin_notices', function () {
	if ( isset( $_GET['nr_sample'] ) ) printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( sprintf( __( 'Created %d sample drafts (Projects/Journal).', 'raveenthiran' ), (int) $_GET['nr_sample'] ) ) );
} );

/* #187 — daily self-test cron: loopback + SMTP flag; email the owner on failure. */
add_action( 'init', function () { if ( ! wp_next_scheduled( 'nr_daily_selftest' ) ) wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'nr_daily_selftest' ); } );
add_action( 'nr_daily_selftest', function () {
	$fail = [];
	$r = wp_remote_get( home_url( '/' ), [ 'timeout' => 10, 'sslverify' => false ] );
	if ( is_wp_error( $r ) || (int) wp_remote_retrieve_response_code( $r ) >= 500 ) $fail[] = 'Front page unreachable (' . ( is_wp_error( $r ) ? $r->get_error_message() : wp_remote_retrieve_response_code( $r ) ) . ')';
	if ( function_exists( 'nr_self_test' ) ) foreach ( nr_self_test() as $k => $ok ) if ( ! $ok && in_array( $k, [ 'GD', 'Uploads write' ], true ) ) $fail[] = $k . ' failed';
	$last = get_option( 'nr_selftest_last', '' );
	update_option( 'nr_selftest_last', $fail ? 'fail' : 'ok', false );
	if ( $fail && $last !== 'fail' ) { // alert once per state change, not daily spam
		wp_mail( nr_opt( 'nr_email', get_option( 'admin_email' ) ), sprintf( '[%s] Self-test failed', get_bloginfo( 'name' ) ), implode( "\n", $fail ) );
	}
} );

/* #191 — settings diff before import: upload → table of changes → apply. */
add_action( 'admin_post_nr_settings_diff', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_settings_import' ) ) wp_die( 'no' );
	$data = [];
	if ( ! empty( $_FILES['nr_settings_file']['tmp_name'] ) && is_uploaded_file( $_FILES['nr_settings_file']['tmp_name'] ) ) {
		$data = json_decode( (string) file_get_contents( $_FILES['nr_settings_file']['tmp_name'] ), true ) ?: [];
	}
	$defaults = nr_settings_defaults();
	$diff = [];
	foreach ( $data as $k => $v ) {
		if ( ! array_key_exists( $k, $defaults ) || ! is_scalar( $v ) ) continue;
		$cur = (string) get_option( $k, $defaults[ $k ] );
		if ( $cur !== (string) $v ) $diff[ $k ] = [ $cur, (string) $v ];
	}
	set_transient( 'nr_import_payload_' . get_current_user_id(), array_intersect_key( $data, $defaults ), 15 * MINUTE_IN_SECONDS );
	echo '<div class="wrap" style="max-width:900px;margin:30px"><h1>' . esc_html__( 'Settings diff', 'raveenthiran' ) . '</h1>';
	if ( ! $diff ) { echo '<p>' . esc_html__( 'No differences — nothing to import.', 'raveenthiran' ) . '</p>'; exit; }
	echo '<table class="widefat striped"><thead><tr><th>Key</th><th>Current</th><th>Incoming</th></tr></thead><tbody>';
	foreach ( $diff as $k => $pair ) printf( '<tr><td><code>%s</code></td><td>%s</td><td><strong>%s</strong></td></tr>', esc_html( $k ), esc_html( mb_strimwidth( $pair[0], 0, 60, '…' ) ), esc_html( mb_strimwidth( $pair[1], 0, 60, '…' ) ) );
	echo '</tbody></table><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:14px"><input type="hidden" name="action" value="nr_settings_apply">';
	wp_nonce_field( 'nr_settings_apply' );
	printf( '<button class="button button-primary">%s</button> <a class="button" href="%s">%s</a></form></div>',
		esc_html( sprintf( __( 'Apply %d changes', 'raveenthiran' ), count( $diff ) ) ),
		esc_url( admin_url( 'themes.php?page=nr-theme-settings' ) ), esc_html__( 'Cancel', 'raveenthiran' ) );
	exit;
} );
add_action( 'admin_post_nr_settings_apply', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_settings_apply' ) ) wp_die( 'no' );
	$payload = get_transient( 'nr_import_payload_' . get_current_user_id() );
	$ok = 0;
	if ( is_array( $payload ) ) { foreach ( $payload as $k => $v ) if ( is_scalar( $v ) ) { update_option( $k, (string) $v ); $ok++; } }
	delete_transient( 'nr_import_payload_' . get_current_user_id() );
	wp_safe_redirect( admin_url( 'themes.php?page=nr-theme-settings&nr_import=' . $ok ) );
	exit;
} );

/* #14 — [nr_scrolly media="ID"] step one | step two | step three [/nr_scrolly]
   Pinned media + scroll-scrubbed step captions (front-end in awwwards.js). */
add_shortcode( 'nr_scrolly', function ( $a, $content = '' ) {
	$a = shortcode_atts( [ 'media' => 0 ], $a );
	$steps = array_filter( array_map( 'trim', explode( '|', wp_strip_all_tags( (string) $content ) ) ) );
	if ( ! $steps ) return '';
	$mid = (int) $a['media'];
	$media = $mid ? wp_get_attachment_image( $mid, 'nr-hero', false, [ 'loading' => 'lazy', 'decoding' => 'async' ] ) : '';
	$out = '<div class="nr-scrolly">';
	$out .= '<div class="nr-scrolly__media">' . $media . '</div><div class="nr-scrolly__steps">';
	foreach ( $steps as $i => $s ) $out .= '<div class="nr-scrolly__step" data-step="' . ( $i + 1 ) . '"><p>' . esc_html( $s ) . '</p></div>';
	return $out . '</div></div>';
} );

/* #123 — [nr_scroll_video src="…mp4" poster="…jpg" height="220vh"] (scrolling pages). */
add_shortcode( 'nr_scroll_video', function ( $a ) {
	$a = shortcode_atts( [ 'src' => '', 'poster' => '', 'height' => '220vh' ], $a );
	if ( ! $a['src'] ) return '';
	$h = preg_match( '/^\d+(vh|px|%)$/', $a['height'] ) ? $a['height'] : '220vh';
	return sprintf(
		'<div class="nr-scrollvid" data-scroll-video style="height:%s"><div class="nr-scrollvid__sticky"><video muted playsinline preload="metadata" %s><source src="%s" type="video/mp4"></video></div></div>',
		esc_attr( $h ),
		$a['poster'] ? 'poster="' . esc_url( $a['poster'] ) . '"' : '',
		esc_url( $a['src'] )
	);
} );
