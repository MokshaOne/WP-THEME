<?php
/**
 * inc/mediumwins.php — IDEAS-200 "Medium" tier, release 1 (v4.47.0).
 * The low-risk, self-contained Medium items. Heavier subsystems (booking
 * wizard, PDF-email, delivery, double-opt-in, importer, calendar) follow in r2.
 *
 * #81 AVIF twins · #107 CWV field data · #108 funnel · #111 redirect map ·
 * #121 diptych rhythm · #129 cover aspect · #132 series chapters · #133 timeline ·
 * #134 map archive · #170 video transcript.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ===== fields ===== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
	acf_add_local_field_group( [
		'key' => 'group_nr_medium_project', 'title' => 'Project — layout & media',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
		'fields' => [
			[ 'key' => 'field_project_rhythm', 'label' => 'Plate rhythm', 'name' => 'project_rhythm', 'type' => 'select', 'choices' => [ '' => 'Single rail (default)', 'diptych' => 'Diptych (pairs)', 'triptych' => 'Triptych (threes)' ], 'allow_null' => 1 ], // #121
			[ 'key' => 'field_project_cover_aspect', 'label' => 'Cover aspect', 'name' => 'project_cover_aspect', 'type' => 'select', 'choices' => [ '' => 'Auto', '4-5' => 'Portrait 4:5', '5-4' => 'Landscape 5:4', '16-9' => 'Wide 16:9', '1-1' => 'Square' ], 'allow_null' => 1 ], // #129
			[ 'key' => 'field_project_transcript', 'label' => 'Video transcript / captions note', 'name' => 'project_transcript', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Plain-text transcript for motion plates (a11y).' ], // #170
		],
	] );
} );

/* #121 — plate rhythm body class. */
add_filter( 'body_class', function ( $c ) {
	if ( is_singular( 'nr_project' ) && function_exists( 'nr_field' ) ) {
		$r = (string) nr_field( 'project_rhythm' );
		if ( $r ) $c[] = 'nr-rhythm-' . sanitize_html_class( $r );
		$a = (string) nr_field( 'project_cover_aspect' );
		if ( $a ) $c[] = 'nr-cover-' . sanitize_html_class( $a );
	}
	return $c;
} );

/* #170 — transcript + caption block under the project (a11y for motion plates). */
function nr_project_transcript_markup( $id = 0 ) {
	$id = $id ?: get_the_ID();
	$t = function_exists( 'nr_field_str' ) ? nr_field_str( 'project_transcript', $id ) : '';
	if ( $t === '' ) return '';
	return '<details class="nr-transcript"><summary>' . esc_html__( 'Transcript', 'raveenthiran' ) . '</summary><div>' . wp_kses_post( wpautop( $t ) ) . '</div></details>';
}

/* #132 — series chapters: prev/next within the project's series. */
function nr_series_chapter_nav( $id = 0 ) {
	$id = $id ?: get_the_ID();
	if ( ! function_exists( 'nr_project_series' ) ) return '';
	$s = nr_project_series( $id );
	if ( ! $s ) return '';
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'ASC' ], 'no_found_rows' => true,
		'tax_query' => [ [ 'taxonomy' => 'nr_project_series', 'terms' => $s->term_id ] ] ] );
	$i = array_search( $id, $ids, true );
	if ( $i === false || count( $ids ) < 2 ) return '';
	$prev = $ids[ $i - 1 ] ?? null; $next = $ids[ $i + 1 ] ?? null;
	$out = '<nav class="nr-chapters" aria-label="' . esc_attr( $s->name ) . '"><span class="nr-eyebrow nr-eyebrow--xs">' . sprintf( esc_html__( '%1$s · %2$d of %3$d', 'raveenthiran' ), esc_html( $s->name ), $i + 1, count( $ids ) ) . '</span><span class="nr-chapters__nav">';
	if ( $prev ) $out .= '<a href="' . esc_url( get_permalink( $prev ) ) . '">← ' . esc_html( get_the_title( $prev ) ) . '</a>';
	if ( $next ) $out .= '<a href="' . esc_url( get_permalink( $next ) ) . '">' . esc_html( get_the_title( $next ) ) . ' →</a>';
	return $out . '</span></nav>';
}

/* #133 — [nr_timeline]: projects grouped by year. */
add_shortcode( 'nr_timeline', function () {
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'no_found_rows' => true, 'orderby' => 'date', 'order' => 'DESC' ] );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$by = [];
	while ( $q->have_posts() ) { $q->the_post();
		$y = get_post_meta( get_the_ID(), 'project_year', true ) ?: get_the_date( 'Y' );
		$by[ $y ][] = [ 'u' => get_permalink(), 't' => get_the_title() ];
	}
	wp_reset_postdata();
	krsort( $by );
	$out = '<div class="nr-timeline">';
	foreach ( $by as $y => $items ) {
		$out .= '<div class="nr-timeline__year"><span class="nr-timeline__y">' . esc_html( $y ) . '</span><ul>';
		foreach ( $items as $it ) $out .= '<li><a href="' . esc_url( $it['u'] ) . '">' . esc_html( $it['t'] ) . '</a></li>';
		$out .= '</ul></div>';
	}
	return $out . '</div>';
} );

/* #134 — [nr_map_all]: every project with coords on one map (reuses inc/map.php). */
add_shortcode( 'nr_map_all', function () {
	if ( ! function_exists( 'nr_field' ) ) return '';
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'no_found_rows' => true ] );
	$pins = [];
	while ( $q->have_posts() ) { $q->the_post();
		$c = trim( (string) nr_field( 'project_coords' ) );
		if ( preg_match( '/^\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*$/', $c, $m ) ) {
			$pins[] = [ 'lat' => (float) $m[1], 'lng' => (float) $m[2], 'label' => wp_strip_all_tags( get_the_title() ), 'url' => get_permalink() ];
		}
	}
	wp_reset_postdata();
	if ( ! $pins ) return '';
	$GLOBALS['nr_map_present'] = true;
	return '<div class="nr-map-all" data-nr-map data-pins="' . esc_attr( wp_json_encode( $pins ) ) . '" style="height:520px"></div>';
} );

/* #81 — AVIF twins (parallel to inc/webp.php). */
function nr_avif_ok() { static $o = null; if ( $o === null ) $o = function_exists( 'imageavif' ) && function_exists( 'imagecreatefromjpeg' ); return $o; }
function nr_avif_make( $path, $avif ) {
	if ( ! nr_avif_ok() || ! file_exists( $path ) ) return false;
	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$img = ( $ext === 'png' ) ? @imagecreatefrompng( $path ) : ( in_array( $ext, [ 'jpg', 'jpeg' ], true ) ? @imagecreatefromjpeg( $path ) : false );
	if ( ! $img ) return false;
	$ok = @imageavif( $img, $avif, 50 );
	imagedestroy( $img );
	return $ok && file_exists( $avif );
}
function nr_avif_twin_url( $url ) {
	if ( ! nr_avif_ok() || ! preg_match( '/\.(jpe?g|png)$/i', (string) $url ) ) return '';
	$up = wp_get_upload_dir();
	if ( strpos( $url, $up['baseurl'] ) !== 0 ) return '';
	$path = $up['basedir'] . substr( $url, strlen( $up['baseurl'] ) );
	if ( ! file_exists( $path ) ) return '';
	$avif = $path . '.avif';
	if ( file_exists( $avif ) ) return $url . '.avif';
	static $made = 0; if ( $made >= 6 ) return '';
	if ( nr_avif_make( $path, $avif ) ) { $made++; return $url . '.avif'; }
	return '';
}
/* Generate AVIF twins for the common sizes on upload (best-effort, capped). */
add_filter( 'wp_generate_attachment_metadata', function ( $meta, $id ) {
	if ( ! nr_avif_ok() || ! is_array( $meta ) || empty( $meta['file'] ) ) return $meta;
	$dir = trailingslashit( wp_get_upload_dir()['basedir'] ) . trailingslashit( dirname( $meta['file'] ) );
	$n = 0;
	foreach ( (array) ( $meta['sizes'] ?? [] ) as $sz ) {
		if ( $n >= 4 ) break;
		$f = $dir . ( $sz['file'] ?? '' );
		if ( $f && file_exists( $f ) && ! file_exists( $f . '.avif' ) && nr_avif_make( $f, $f . '.avif' ) ) $n++;
	}
	return $meta;
}, 11, 2 );
/* Inject an <source type="image/avif"> into the webp <picture> when a twin exists. */
add_filter( 'wp_get_attachment_image', function ( $html, $id ) {
	if ( ! nr_avif_ok() || strpos( $html, '<picture' ) === false || strpos( $html, 'image/avif' ) !== false ) return $html;
	if ( ! preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/', $html, $m ) ) return $html;
	$avif = nr_avif_twin_url( $m[1] );
	if ( ! $avif ) return $html;
	// place the avif source first so browsers prefer it
	return preg_replace( '/<picture[^>]*>/', '$0<source type="image/avif" srcset="' . esc_url( $avif ) . '">', $html, 1 );
}, 11, 2 );

/* #107 — Core Web Vitals field collection (beacon → rolling averages). */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/cwv', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ( $r ) {
		$m = $r->get_param( 'm' ); $v = (float) $r->get_param( 'v' );
		if ( ! in_array( $m, [ 'LCP', 'CLS', 'INP', 'FCP', 'TTFB' ], true ) || $v < 0 || $v > 600000 ) return new WP_REST_Response( [ 'ok' => false ], 200 );
		$d = get_option( 'nr_cwv', [] ); if ( ! is_array( $d ) ) $d = [];
		$row = $d[ $m ] ?? [ 'n' => 0, 'sum' => 0 ];
		$row['n']++; $row['sum'] += $v; $d[ $m ] = $row;
		update_option( 'nr_cwv', $d, false );
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	} ] );
} );

/* #108 — funnel + #107 readout, folded into a dashboard widget. */
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	wp_add_dashboard_widget( 'nr_field_metrics', __( 'Obscura — field metrics', 'raveenthiran' ), function () {
		$cwv = get_option( 'nr_cwv', [] );
		echo '<p style="margin:0 0 8px"><strong>' . esc_html__( 'Core Web Vitals (field avg)', 'raveenthiran' ) . '</strong></p><ul style="margin:0">';
		foreach ( [ 'LCP' => 'ms', 'INP' => 'ms', 'FCP' => 'ms', 'TTFB' => 'ms', 'CLS' => '' ] as $m => $u ) {
			$r = $cwv[ $m ] ?? null; $avg = $r && $r['n'] ? round( $r['sum'] / $r['n'], $u ? 0 : 3 ) : '—';
			printf( '<li style="display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:3px 0"><span>%s</span><strong>%s%s</strong></li>', esc_html( $m ), esc_html( (string) $avg ), esc_html( $avg === '—' ? '' : $u ) );
		}
		echo '</ul>';
		$f = get_option( 'nr_funnel', [] );
		$home = (int) ( $f['home'] ?? 0 ); $port = (int) ( $f['portfolio'] ?? 0 ); $enq = (int) ( $f['enquire'] ?? 0 );
		$sent = (int) ( wp_count_posts( 'nr_enquiry' )->publish ?? 0 );
		echo '<p style="margin:10px 0 4px"><strong>' . esc_html__( 'Funnel (this install)', 'raveenthiran' ) . '</strong></p>';
		printf( '<p style="font-size:12px;color:#555">%s → %s → %s → %s</p>',
			esc_html( "Home $home" ), esc_html( "Portfolio $port" ), esc_html( "Enquire $enq" ), esc_html( sprintf( __( '%d sent', 'raveenthiran' ), $sent ) ) );
	} );
} );
/* count a funnel step (called by a tiny beacon). */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/funnel', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ( $r ) {
		$s = sanitize_key( (string) $r->get_param( 's' ) );
		if ( ! in_array( $s, [ 'home', 'portfolio', 'enquire' ], true ) ) return new WP_REST_Response( [ 'ok' => false ], 200 );
		$f = get_option( 'nr_funnel', [] ); if ( ! is_array( $f ) ) $f = [];
		$f[ $s ] = (int) ( $f[ $s ] ?? 0 ) + 1; update_option( 'nr_funnel', $f, false );
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	} ] );
} );

/* #111 — 404/410 + redirect-map UI. Stored as option, applied on template_redirect. */
add_action( 'admin_menu', function () {
	add_management_page( __( 'Redirects', 'raveenthiran' ), __( 'Redirects', 'raveenthiran' ), 'manage_options', 'nr-redirects', function () {
		if ( isset( $_POST['nr_redirects'] ) && check_admin_referer( 'nr_redirects' ) ) {
			update_option( 'nr_redirects', sanitize_textarea_field( wp_unslash( $_POST['nr_redirects'] ) ), false );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'raveenthiran' ) . '</p></div>';
		}
		$val = (string) get_option( 'nr_redirects', '' );
		echo '<div class="wrap"><h1>' . esc_html__( 'Redirects', 'raveenthiran' ) . '</h1><form method="post">';
		wp_nonce_field( 'nr_redirects' );
		echo '<p class="description">' . esc_html__( 'One per line: "/old-path => /new-path" (301). Use "/old => 410" to mark content permanently gone.', 'raveenthiran' ) . '</p>';
		echo '<textarea name="nr_redirects" rows="12" style="width:100%;font-family:monospace">' . esc_textarea( $val ) . '</textarea>';
		submit_button();
		echo '</form></div>';
	} );
} );
add_action( 'template_redirect', function () {
	if ( ! is_404() ) return;
	$map = (string) get_option( 'nr_redirects', '' );
	if ( $map === '' ) return;
	$req = '/' . trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $map ) ) ) as $line ) {
		$p = array_map( 'trim', explode( '=>', $line ) );
		if ( count( $p ) !== 2 ) continue;
		$from = '/' . trim( $p[0], '/' );
		if ( $from !== $req ) continue;
		if ( $p[1] === '410' ) { status_header( 410 ); nocache_headers(); exit; }
		wp_redirect( strpos( $p[1], 'http' ) === 0 ? $p[1] : home_url( $p[1] ), 301 );
		exit;
	}
}, 0 );
