<?php
/**
 * inc/quickwins.php — IDEAS-200 "quick wins" (v4.45.0).
 *
 * A bundle of tiny, additive, low-risk wins. Each is a single hook/helper.
 * #89 img attrs · #99 sitemap index · #103 og:updated_time · #136 tag chips ·
 * #138 per-series feed link · #158 quote-of-the-day · #173 hreflang filter ·
 * #175 locale date helper · #184 upload validation · #185 honeytoken ·
 * #188 autoload audit (helper) · #193 opt-in uninstall cleanup.
 * (#177 SRI is wired in inc/map.php; docs/config items ship as files.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* #89 — guarantee decoding=async on attachment images (loading is left as set
   so eager LCP images stay eager). A cheap correctness backstop. */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
	if ( empty( $attr['decoding'] ) ) $attr['decoding'] = 'async';
	return $attr;
}, 20 );

/* #103 — og:updated_time on single project/journal (Pinterest/OG freshness). */
add_action( 'wp_head', function () {
	if ( ! is_singular( [ 'nr_project', 'nr_journal' ] ) ) return;
	printf( '<meta property="og:updated_time" content="%s">' . "\n", esc_attr( get_the_modified_date( 'c' ) ) );
}, 8 );

/* #173 — hreflang alternates filter. Self-referencing today; a future
   translation layer can populate ['en-US'=>url, 'de'=>url, …] via this filter. */
add_action( 'wp_head', function () {
	$alts = apply_filters( 'nr_hreflang_alternates', [] );
	if ( ! is_array( $alts ) ) return;
	foreach ( $alts as $lang => $url ) {
		if ( $url ) printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $lang ), esc_url( $url ) );
	}
}, 9 );

/* #175 — locale-aware date helper. Templates can call nr_i18n_date($ts). */
if ( ! function_exists( 'nr_i18n_date' ) ) {
	function nr_i18n_date( $ts = null, $fmt = '' ) {
		$ts = $ts ?: time();
		$fmt = $fmt ?: ( get_option( 'date_format' ) ?: 'j M Y' );
		return date_i18n( $fmt, is_numeric( $ts ) ? (int) $ts : strtotime( (string) $ts ) );
	}
}

/* #138 — per-series feed: advertise the term feed on a series archive. */
add_action( 'wp_head', function () {
	if ( ! is_tax( 'nr_project_series' ) ) return;
	$t = get_queried_object();
	if ( ! $t ) return;
	printf( '<link rel="alternate" type="application/rss+xml" title="%s" href="%s">' . "\n",
		esc_attr( $t->name . ' — ' . get_bloginfo( 'name' ) ),
		esc_url( get_term_feed_link( $t->term_id, 'nr_project_series' ) )
	);
}, 3 );
/* Make sure series term feeds actually query projects. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_feed() && $q->is_tax( 'nr_project_series' ) ) {
		$q->set( 'post_type', 'nr_project' );
	}
} );

/* #136 — related-by-tag chips for a single project (rendered from the template). */
function nr_project_tag_chips( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$terms = get_the_terms( $post_id, 'nr_project_tag' );
	if ( ! $terms || is_wp_error( $terms ) ) return '';
	$out = '<nav class="nr-project__tags" aria-label="' . esc_attr__( 'Related tags', 'raveenthiran' ) . '">';
	foreach ( $terms as $t ) {
		$out .= '<a class="nr-chip nr-chip--sm" href="' . esc_url( get_term_link( $t ) ) . '">#' . esc_html( $t->name ) . '</a>';
	}
	return $out . '</nav>';
}

/* #158 — "quote of the day": a random testimonial for 404 / empty search. */
function nr_random_testimonial_markup() {
	$q = new WP_Query( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 1, 'orderby' => 'rand', 'no_found_rows' => true ] );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$q->the_post();
	$role = function_exists( 'get_field' ) ? get_field( 'client_role' ) : '';
	$html = '<figure class="nr-qotd"><blockquote>' . esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ?: get_the_excerpt() ), 34 ) ) . '</blockquote>'
		. '<figcaption>' . esc_html( get_the_title() ) . ( $role ? ' · <span>' . esc_html( $role ) . '</span>' : '' ) . '</figcaption></figure>';
	wp_reset_postdata();
	return $html;
}

/* #99 — sitemap index. Lists the page sitemap + the image sitemap so search
   engines (and >200-URL installs) get a clean index entry point. */
add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path !== 'sitemap-index.xml' ) return;
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	$now = gmdate( 'c' );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( [ home_url( '/sitemap.xml' ), home_url( '/sitemap-images.xml' ) ] as $s ) {
		echo '  <sitemap><loc>' . esc_url( $s ) . '</loc><lastmod>' . $now . "</lastmod></sitemap>\n";
	}
	echo '</sitemapindex>';
	exit;
}, 0 );

/* #184 — upload validation: reject oversized files early with a clear message. */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	$max = (int) apply_filters( 'nr_max_upload_mb', 25 ) * 1024 * 1024;
	if ( ! empty( $file['size'] ) && $file['size'] > $max ) {
		$file['error'] = sprintf(
			/* translators: %d = max MB */
			__( 'That file is over the %d MB limit for this site. Please export a smaller version.', 'raveenthiran' ),
			(int) ( $max / 1024 / 1024 )
		);
	}
	return $file;
} );

/* #185 — honeytoken: a bait path (disallowed in robots, linked only to bots)
   that briefly blocks any IP that requests it. Conservative: 30-min soft ban,
   front-end only, never trips logged-in users. */
const NR_TRAP = 'nr-no-entry-7f3a';
add_filter( 'robots_txt', function ( $o ) {
	$o .= "\nUser-agent: *\nDisallow: /" . NR_TRAP . "/\n";
	return $o;
}, 21 );
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() ) return;
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( ! $ip ) return;
	$key = 'nr_ban_' . md5( $ip );
	if ( get_transient( $key ) ) { status_header( 403 ); nocache_headers(); exit; }
	if ( $path === NR_TRAP ) { set_transient( $key, 1, 30 * MINUTE_IN_SECONDS ); status_header( 403 ); exit; }
}, 1 );
/* A hidden, nofollow bait link (humans never see or tab to it). */
add_action( 'wp_footer', function () {
	echo '<a href="' . esc_url( home_url( '/' . NR_TRAP . '/' ) ) . '" rel="nofollow" aria-hidden="true" tabindex="-1" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">do not follow</a>';
}, 99 );

/* #188 — autoloaded-options size (read-only helper for the health widget). */
function nr_autoload_report() {
	global $wpdb;
	$bytes = (int) $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'" );
	$top   = $wpdb->get_results( "SELECT option_name, LENGTH(option_value) AS len FROM {$wpdb->options} WHERE autoload='yes' ORDER BY len DESC LIMIT 5" );
	return [ 'bytes' => $bytes, 'top' => $top ];
}

/* #193 — opt-in uninstall cleanup. When the owner switches AWAY from Obscura
   AND has ticked the box, remove the theme's options/transients. Default off,
   so data is never destroyed unless explicitly chosen. */
add_action( 'switch_theme', function () {
	if ( get_option( 'nr_clean_uninstall', '0' ) !== '1' ) return;
	global $wpdb;
	$names = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'nr\_%' OR option_name LIKE '\_transient\_nr\_%'" );
	foreach ( (array) $names as $n ) delete_option( $n );
} );
