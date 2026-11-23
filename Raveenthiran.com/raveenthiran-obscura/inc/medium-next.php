<?php
/**
 * inc/medium-next.php — IDEAS-50-NEXT, Medium batch 1 (v4.58.0).
 * #33 weekly studio digest · #35 exhibition/Event schema + [nr_shows] ·
 * #38 press-kit auto-zip · #41 availability heat-calendar · #50 component gallery.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── #33 — weekly studio digest (owner only) ── */
add_action( 'init', function () { if ( ! wp_next_scheduled( 'nr_weekly_digest' ) ) wp_schedule_event( strtotime( 'next monday 8:00' ), 'weekly', 'nr_weekly_digest' ); } );
add_action( 'nr_weekly_digest', function () {
	$since = gmdate( 'Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS );
	$enq = post_type_exists( 'nr_enquiry' ) ? get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 50, 'fields' => 'ids', 'date_query' => [ [ 'after' => $since ] ] ] ) : [];
	$lines = [ sprintf( __( 'Last 7 days at %s', 'raveenthiran' ), get_bloginfo( 'name' ) ), str_repeat( '─', 28 ), '' ];
	$lines[] = sprintf( __( 'New enquiries: %d', 'raveenthiran' ), count( $enq ) );
	foreach ( array_slice( $enq, 0, 8 ) as $id ) $lines[] = '  • ' . get_the_title( $id ) . ' — ' . get_post_meta( $id, '_nr_type', true );
	// top projects by attribution
	$by = [];
	foreach ( $enq as $id ) { $r = trim( (string) get_post_meta( $id, '_nr_ref', true ) ); if ( $r ) $by[ $r ] = ( $by[ $r ] ?? 0 ) + 1; }
	if ( $by ) { arsort( $by ); $lines[] = ''; $lines[] = __( 'Driving enquiries:', 'raveenthiran' ); foreach ( array_slice( $by, 0, 5, true ) as $p => $n ) $lines[] = "  • $p ($n)"; }
	// CWV + pageviews if collected
	$cwv = get_option( 'nr_cwv', [] );
	if ( is_array( $cwv ) && $cwv ) { $lines[] = ''; $lines[] = __( 'Core Web Vitals (field avg):', 'raveenthiran' ); foreach ( $cwv as $m => $r ) if ( ! empty( $r['n'] ) ) $lines[] = sprintf( '  %s: %s', $m, round( $r['sum'] / $r['n'], $m === 'CLS' ? 3 : 0 ) ); }
	$pv = get_option( 'nr_pv', [] ); $days = $pv['days'] ?? [];
	if ( $days ) { $tot = 0; $i = 0; krsort( $days ); foreach ( $days as $n ) { if ( $i++ < 7 ) $tot += $n; } $lines[] = ''; $lines[] = sprintf( __( 'Pageviews (7d, logged-out): %s', 'raveenthiran' ), number_format_i18n( $tot ) ); }
	$lines[] = ''; $lines[] = admin_url( 'edit.php?post_type=nr_enquiry&page=nr-pipeline' );
	wp_mail( nr_opt( 'nr_email', get_option( 'admin_email' ) ), sprintf( '[%s] Weekly studio digest', get_bloginfo( 'name' ) ), implode( "\n", $lines ) );
} );

/* ── #35 — exhibitions: setting nr_shows + [nr_shows] + Event schema ──
   Lines: "Title | Venue | YYYY-MM-DD | https://url" */
add_shortcode( 'nr_shows', function () {
	$rows = function_exists( 'nr_parse_pipe_lines' ) ? nr_parse_pipe_lines( nr_opt( 'nr_shows', '' ) ) : [];
	if ( ! $rows ) return '';
	$out = '<ul class="nr-shows">'; $events = [];
	foreach ( $rows as $p ) {
		$title = $p[0] ?? ''; $venue = $p[1] ?? ''; $date = $p[2] ?? ''; $url = $p[3] ?? '';
		if ( $title === '' ) continue;
		$ts = $date ? strtotime( $date ) : 0;
		$when = $ts ? date_i18n( get_option( 'date_format' ), $ts ) : '';
		$inner = '<span class="nr-shows__t">' . esc_html( $title ) . '</span>'
			. ( $venue ? '<span class="nr-shows__v">' . esc_html( $venue ) . '</span>' : '' )
			. ( $when ? '<span class="nr-shows__d">' . esc_html( $when ) . '</span>' : '' );
		$out .= '<li>' . ( $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $inner . '</a>' : $inner ) . '</li>';
		$ev = [ '@type' => 'Event', 'name' => $title, 'eventStatus' => 'https://schema.org/EventScheduled' ];
		if ( $ts ) $ev['startDate'] = gmdate( 'Y-m-d', $ts );
		if ( $venue ) $ev['location'] = [ '@type' => 'Place', 'name' => $venue ];
		if ( $url ) $ev['url'] = $url;
		$ev['performer'] = [ '@type' => 'Person', 'name' => nr_opt( 'nr_about_name', 'Nishuthan Raveenthiran' ) ];
		$events[] = $ev;
	}
	$out .= '</ul>';
	if ( $events ) $out .= '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@graph' => $events ], JSON_UNESCAPED_SLASHES ) . '</script>';
	return $out;
} );

/* ── #38 — press-kit auto-zip at /nr-presskit.zip (web-res, capped) ── */
add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path !== 'nr-presskit.zip' || ! class_exists( 'ZipArchive' ) ) return;
	$tmp = wp_tempnam( 'nr-presskit' );
	$zip = new ZipArchive();
	if ( $zip->open( $tmp, ZipArchive::OVERWRITE ) !== true ) { status_header( 500 ); exit; }
	$bio = wp_strip_all_tags( (string) nr_opt( 'nr_about_bio', '' ) ) ?: wp_strip_all_tags( (string) nr_opt( 'nr_tagline', '' ) );
	$zip->addFromString( 'bio.txt', get_bloginfo( 'name' ) . "\n\n" . $bio . "\n\n" . nr_opt( 'nr_email', '' ) . ' · ' . home_url( '/' ) );
	// site icon / logo
	$icon = get_site_icon_url( 512 );
	if ( $icon ) { $r = wp_remote_get( $icon, [ 'timeout' => 8 ] ); if ( ! is_wp_error( $r ) && wp_remote_retrieve_response_code( $r ) === 200 ) $zip->addFromString( 'logo.png', wp_remote_retrieve_body( $r ) ); }
	// up to 8 featured project images (web-res 'large')
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 8, 'fields' => 'ids', 'no_found_rows' => true, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1' ] );
	if ( ! $ids ) $ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 8, 'fields' => 'ids', 'no_found_rows' => true ] );
	$n = 0;
	foreach ( $ids as $pid ) {
		$f = get_attached_file( get_post_thumbnail_id( $pid ) );
		// prefer the 'large' sub-size to keep the zip light
		$meta = wp_get_attachment_metadata( get_post_thumbnail_id( $pid ) );
		if ( $f && ! empty( $meta['sizes']['large']['file'] ) ) $f = trailingslashit( dirname( $f ) ) . $meta['sizes']['large']['file'];
		if ( $f && file_exists( $f ) ) { $zip->addFile( $f, sprintf( 'images/%02d-%s', ++$n, sanitize_file_name( basename( $f ) ) ) ); }
	}
	$zip->close();
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_title( get_bloginfo( 'name' ) ) . '-press-kit.zip"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	readfile( $tmp );
	@unlink( $tmp );
	exit;
}, 0 );

/* ── #41 — [nr_availability] heat-calendar (busy from nr_busy_dates) ── */
add_shortcode( 'nr_availability', function ( $a ) {
	$a = shortcode_atts( [ 'months' => 2 ], $a );
	$busy = [];
	foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) nr_opt( 'nr_busy_dates', '' ) ) ) ) as $d ) {
		$ts = strtotime( $d ); if ( $ts ) $busy[ gmdate( 'Y-m-d', $ts ) ] = 1;
	}
	$out = '<div class="nr-cal">';
	$cur = strtotime( gmdate( 'Y-m-01' ) );
	for ( $mi = 0; $mi < max( 1, min( 4, (int) $a['months'] ) ); $mi++ ) {
		$first = strtotime( "+$mi month", $cur );
		$days  = (int) gmdate( 't', $first );
		$lead  = ( (int) gmdate( 'N', $first ) ) - 1; // Mon=0
		$out  .= '<div class="nr-cal__m"><div class="nr-cal__h">' . esc_html( date_i18n( 'F Y', $first ) ) . '</div><div class="nr-cal__grid">';
		foreach ( [ 'M', 'T', 'W', 'T', 'F', 'S', 'S' ] as $d ) $out .= '<span class="nr-cal__dow">' . esc_html( $d ) . '</span>';
		for ( $i = 0; $i < $lead; $i++ ) $out .= '<span class="nr-cal__pad"></span>';
		for ( $d = 1; $d <= $days; $d++ ) {
			$ymd = gmdate( 'Y-m-d', strtotime( "+" . ( $d - 1 ) . " day", $first ) );
			$cls = isset( $busy[ $ymd ] ) ? ' is-busy' : ( strtotime( $ymd ) < strtotime( gmdate( 'Y-m-d' ) ) ? ' is-past' : ' is-open' );
			$out .= '<span class="nr-cal__d' . $cls . '">' . $d . '</span>';
		}
		$out .= '</div></div>';
	}
	$out .= '<p class="nr-cal__key"><span class="is-open"></span> ' . esc_html__( 'Open', 'raveenthiran' ) . ' <span class="is-busy"></span> ' . esc_html__( 'Booked', 'raveenthiran' ) . '</p>';
	return $out . '</div>';
} );

/* ── #50 — component gallery (Appearance → Components, admin QA) ── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'nr-theme-settings', __( 'Components', 'raveenthiran' ), __( 'Components', 'raveenthiran' ), 'edit_theme_options', 'nr-components', function () {
		$blocks = [
			'Buttons' => '<a class="nr-btn nr-btn--primary"><span>Primary</span> <span>→</span></a> <a class="nr-btn"><span>Default</span></a> <a class="nr-btn nr-btn--ghost"><span>Ghost</span></a>',
			'Eyebrow' => '<span class="nr-eyebrow">Featured work · §01</span>',
			'Chips'   => '<span class="nr-chip">All</span> <span class="nr-chip nr-chip--sm">#tag</span>',
			'Packages [nr_packages]' => do_shortcode( '[nr_packages]' ),
			'Press [nr_press]'       => do_shortcode( '[nr_press]' ),
			'Timeline [nr_timeline]' => do_shortcode( '[nr_timeline]' ),
			'Shows [nr_shows]'       => do_shortcode( '[nr_shows]' ),
			'Availability [nr_availability]' => do_shortcode( '[nr_availability]' ),
		];
		echo '<div class="wrap"><h1>' . esc_html__( 'Components', 'raveenthiran' ) . '</h1><p class="description">' . esc_html__( 'Live render of the theme’s reusable blocks (front-end CSS not loaded here — for structure/QA).', 'raveenthiran' ) . '</p>';
		foreach ( $blocks as $name => $html ) {
			echo '<h2 style="margin:22px 0 6px;font-size:14px">' . esc_html( $name ) . '</h2>';
			echo '<div style="background:#0B0C10;color:#F2EFE9;padding:18px;border-radius:6px;overflow:auto">' . wp_kses_post( $html ?: '<em style="color:#888">empty / no data</em>' ) . '</div>';
		}
		echo '</div>';
	} );
} );
