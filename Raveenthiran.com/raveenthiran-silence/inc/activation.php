<?php
/**
 * Silence — activation routine.
 * On theme switch: create the two template pages (About, Enquire) and
 * import settings from an existing Obscura install. Idempotent and
 * non-destructive: existing pages are reused, existing sl_* values are
 * never overwritten, and Obscura's own options are only read.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_switch_theme', function () {
	$made = [ 'pages' => [], 'imported' => 0 ];

	/* ── 1. Pages ─────────────────────────────────────────────── */
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

	/* ── 2. Import Obscura settings (fill empties only) ───────── */
	$map = [
		/* identity */
		'sl_studio'          => 'nr_logo_text',
		'sl_tagline'         => 'nr_tagline',
		'sl_location'        => 'nr_location',
		'sl_availability'    => 'nr_avail_text',
		'sl_accent'          => 'nr_accent',
		/* contact & socials */
		'sl_email'           => 'nr_email',
		'sl_instagram'       => 'nr_instagram',
		'sl_behance'         => 'nr_behance',
		'sl_vimeo'           => 'nr_vimeo',
		'sl_linkedin'        => 'nr_linkedin',
		'sl_twitter_handle'  => 'nr_twitter_handle',
		/* seo */
		'sl_seo_phone'       => 'nr_phone',
		'sl_verify_google'   => 'nr_verify_google',
		'sl_verify_bing'     => 'nr_verify_bing',
		'sl_verify_yandex'   => 'nr_verify_yandex',
		'sl_tracking_script' => 'nr_tracking_script',
		/* mail (password included — stored outside the kses bag on both sides) */
		'sl_smtp_enable'     => 'nr_smtp_enable',
		'sl_smtp_host'       => 'nr_smtp_host',
		'sl_smtp_port'       => 'nr_smtp_port',
		'sl_smtp_secure'     => 'nr_smtp_secure',
		'sl_smtp_user'       => 'nr_smtp_user',
		'sl_smtp_from'       => 'nr_smtp_from',
		'sl_smtp_fromname'   => 'nr_smtp_fromname',
		'sl_smtp_pass'       => 'nr_smtp_pass',
		/* security */
		'sl_turnstile_site'   => 'nr_turnstile_site',
		'sl_turnstile_secret' => 'nr_turnstile_secret',
	];
	/* Obscura's about-lede + ACF-style option rows (options_*) */
	$fallback_map = [
		'sl_about'      => [ 'nr_about_lede', 'nr_about_bio' ],
		'sl_seo_phone'  => [ 'nr_phone', 'options_seo_phone' ],
		'sl_seo_street' => [ 'options_seo_street' ],
		'sl_seo_postal' => [ 'options_seo_postal_code' ],
		'sl_seo_city'   => [ 'options_seo_city' ],
		'sl_seo_lat'    => [ 'options_seo_lat' ],
		'sl_seo_lng'    => [ 'options_seo_lng' ],
	];
	foreach ( $fallback_map as $sl => $sources ) {
		unset( $map[ $sl ] ); // handled below with multi-source fallback
	}

	$import = function ( $sl_key, $sources ) use ( &$made ) {
		$current = get_option( $sl_key, '' );
		if ( $current !== '' && $current !== false ) return; // never overwrite
		foreach ( (array) $sources as $src ) {
			$v = get_option( $src, '' );
			if ( is_scalar( $v ) && (string) $v !== '' ) {
				update_option( $sl_key, (string) $v );
				$made['imported']++;
				return;
			}
		}
	};

	foreach ( $map as $sl => $nr )            $import( $sl, $nr );
	foreach ( $fallback_map as $sl => $srcs ) $import( $sl, $srcs );

	/* hero interval: Obscura stores milliseconds ('9000'), Silence seconds */
	if ( get_option( 'sl_hero_interval', '' ) === '' ) {
		$ms = (int) get_option( 'nr_hero_interval', 0 );
		if ( $ms > 0 ) {
			update_option( 'sl_hero_interval', (string) max( 3, (int) round( $ms > 100 ? $ms / 1000 : $ms ) ) );
			$made['imported']++;
		}
	}

	/* ── 3. One-time summary notice ───────────────────────────── */
	set_transient( 'sl_activation_notice', $made, 5 * MINUTE_IN_SECONDS );
} );

add_action( 'admin_notices', function () {
	$made = get_transient( 'sl_activation_notice' );
	if ( ! $made || ! current_user_can( 'manage_options' ) ) return;
	delete_transient( 'sl_activation_notice' );

	$bits = [];
	$bits[] = $made['pages']
		? sprintf( __( 'Pages ready: %s.', 'raveenthiran-silence' ), implode( ', ', array_map( 'esc_html', $made['pages'] ) ) )
		: __( 'About & Enquire pages were already set up.', 'raveenthiran-silence' );
	$bits[] = $made['imported']
		? sprintf( _n( '%d setting imported from Obscura.', '%d settings imported from Obscura.', $made['imported'], 'raveenthiran-silence' ), $made['imported'] )
		: __( 'No Obscura settings to import (none found, or Silence values already set).', 'raveenthiran-silence' );
	if ( function_exists( 'sl_bridge_active' ) && sl_bridge_active() ) {
		$bits[] = __( 'Obscura content bridge is active — your existing projects and journal are live at /work and /journal.', 'raveenthiran-silence' );
	}
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>Silence</strong> — %s %s</p></div>',
		implode( ' ', $bits ),
		esc_html__( 'If /work does not resolve yet: Settings → Permalinks → Save.', 'raveenthiran-silence' )
	);
} );
