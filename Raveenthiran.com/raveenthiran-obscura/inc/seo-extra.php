<?php
/**
 * inc/seo-extra.php — IDEAS-200 Batch 3 (SEO · distribution · schema · perf).
 *
 * Additive, low-risk wins layered on top of inc/seo.php (multiple wp_head
 * callbacks + virtual endpoints compose cleanly). Nothing here changes the
 * front-end look; it's all <head>, feeds, sitemaps and schema.
 *
 * Shipped here: #96 ImageObject · #97 VideoObject · #98 image sitemap ·
 * #100 WebSub · #101 JSON Feed · #102 og:video · #104 Discover author/dates ·
 * #105 canonical consolidation · #110 search-term capture · #113 IndexNow bulk ·
 * #114 title templates · #116 author @id · #117 speakable · #119 robots/AI policy ·
 * #120 hreflang en-AT · #90 generalised LCP preload.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   #105 — canonical consolidation. Filtered / sorted / paged
   archive permutations (?filter, ?sort, ?paged query args) get
   noindex,follow so only the clean archive is indexed.
   ============================================================= */
add_action( 'wp_head', function () {
	if ( is_admin() || is_singular() ) return;
	$noisy = [ 'filter', 'sort', 'orderby', 'order', 'view', 's', 'cat', 'tag' ];
	$has = false;
	foreach ( $noisy as $k ) { if ( isset( $_GET[ $k ] ) && $_GET[ $k ] !== '' ) { $has = true; break; } }
	if ( $has && ! is_search() ) echo '<meta name="robots" content="noindex,follow">' . "\n";
}, 5 );

/* =============================================================
   #120 — hreflang region tuning. Adds en-AT alongside the
   en + x-default that inc/seo.php already emits (DACH market).
   ============================================================= */
add_action( 'wp_head', function () {
	if ( is_admin() ) return;
	$canonical = is_singular() ? get_permalink()
		: ( is_post_type_archive() ? get_post_type_archive_link( get_post_type() ) : home_url( '/' ) );
	if ( $canonical ) printf( '<link rel="alternate" hreflang="en-AT" href="%s">' . "\n", esc_url( $canonical ) );
}, 8 );

/* =============================================================
   #116 / #104 — Person @id + Discover signals. A single Person
   node with a stable @id every Article/VisualArtwork can resolve
   to (Knowledge-Panel hygiene), plus author + dates so Google
   Discover has clean freshness/authorship.
   ============================================================= */
add_action( 'wp_head', function () {
	if ( ! ( is_singular( 'nr_project' ) || is_singular( 'nr_journal' ) ) ) return;
	$id   = get_queried_object_id();
	$site = get_site_url();
	$node = [
		'@context' => 'https://schema.org',
		'@graph'   => [
			[
				'@type' => 'Person',
				'@id'   => $site . '/#person',
				'name'  => nr_opt( 'nr_about_name', 'Nishuthan Raveenthiran' ),
				'url'   => $site,
			],
			[
				'@type'         => 'WebPage',
				'@id'           => get_permalink( $id ) . '#webpage',
				'url'           => get_permalink( $id ),
				'name'          => wp_strip_all_tags( get_the_title( $id ) ),
				'datePublished' => get_the_date( 'c', $id ),
				'dateModified'  => get_the_modified_date( 'c', $id ),
				'author'        => [ '@id' => $site . '/#person' ],
				/* #117 — speakable: let assistants read the title + lede aloud. */
				'speakable'     => [
					'@type'       => 'SpeakableSpecification',
					'cssSelector' => [ 'h1', '.nr-project__lede', '.nr-jpost__lede' ],
				],
			],
		],
	];
	echo '<script type="application/ld+json">' . wp_json_encode( $node, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 9 );

/* =============================================================
   #96 / #97 / #102 — ImageObject + VideoObject for project media,
   plus og:video for projects that carry a motion plate.
   ============================================================= */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'nr_project' ) ) return;
	$id      = get_queried_object_id();
	$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery', $id ) : [];
	if ( ! is_array( $gallery ) ) $gallery = [];

	$images = []; $videos = [];
	$n = 0;
	foreach ( $gallery as $g ) {
		if ( $n >= 12 ) break;
		$aid = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : ( is_numeric( $g ) ? (int) $g : 0 );
		if ( ! $aid ) continue;
		if ( wp_attachment_is( 'video', $aid ) ) {
			$vurl = wp_get_attachment_url( $aid );
			if ( $vurl ) $videos[] = [
				'@type'        => 'VideoObject',
				'name'         => get_the_title( $id ) . ' — motion',
				'description'  => wp_strip_all_tags( get_the_excerpt( $id ) ) ?: get_the_title( $id ),
				'contentUrl'   => $vurl,
				'thumbnailUrl' => (string) get_the_post_thumbnail_url( $id, 'nr-hero' ),
				'uploadDate'   => get_the_date( 'c', $id ),
			];
			continue;
		}
		$src = wp_get_attachment_image_url( $aid, 'nr-hero' );
		if ( ! $src ) continue;
		$cap = wp_get_attachment_caption( $aid );
		$io = [ '@type' => 'ImageObject', 'contentUrl' => $src, 'creator' => [ '@id' => get_site_url() . '/#person' ] ];
		if ( $cap ) $io['caption'] = $cap;
		$images[] = $io; $n++;
	}

	$graph = array_merge( $images, $videos );
	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			[ '@context' => 'https://schema.org', '@graph' => $graph ],
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		) . '</script>' . "\n";
	}
	// #102 — og:video for the first motion plate.
	if ( $videos ) {
		printf( '<meta property="og:video" content="%s">' . "\n", esc_url( $videos[0]['contentUrl'] ) );
		printf( '<meta property="og:video:type" content="%s">' . "\n", 'video/mp4' );
		echo '<meta name="twitter:card" content="player">' . "\n";
	}
}, 9 );

/* =============================================================
   #114 — token title templates. Author writes a template in
   Theme Settings (%title% %cat% %year% %site%); applied to the
   <title> of single projects.
   ============================================================= */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( ! is_singular( 'nr_project' ) ) return $parts;
	$tpl = trim( (string) nr_opt( 'nr_meta_title_tpl', '' ) );
	if ( $tpl === '' ) return $parts;
	$id   = get_queried_object_id();
	$cat  = '';
	$terms = wp_get_post_terms( $id, 'nr_project_cat', [ 'fields' => 'names' ] );
	if ( ! is_wp_error( $terms ) && $terms ) $cat = $terms[0];
	$year = get_post_meta( $id, 'project_year', true ) ?: get_the_date( 'Y', $id );
	$parts['title'] = strtr( $tpl, [
		'%title%' => wp_strip_all_tags( get_the_title( $id ) ),
		'%cat%'   => $cat,
		'%year%'  => (string) $year,
		'%site%'  => get_bloginfo( 'name' ),
	] );
	return $parts;
}, 20 );

/* =============================================================
   #119 — robots / AI-crawler policy. Appends a documented block
   to robots.txt; a Theme Setting decides whether AI crawlers
   (GPTBot, CCBot, Google-Extended, anthropic-ai…) are allowed.
   ============================================================= */
add_filter( 'robots_txt', function ( $out ) {
	$out .= "\n# Obscura — sitemaps\n";
	$out .= 'Sitemap: ' . home_url( '/sitemap.xml' ) . "\n";
	$out .= 'Sitemap: ' . home_url( '/sitemap-images.xml' ) . "\n";
	if ( nr_opt( 'nr_block_ai', '0' ) === '1' ) {
		$out .= "\n# AI crawlers (owner opted out)\n";
		foreach ( [ 'GPTBot', 'CCBot', 'Google-Extended', 'anthropic-ai', 'ClaudeBot', 'Bytespider', 'PerplexityBot', 'Amazonbot' ] as $bot ) {
			$out .= "User-agent: $bot\nDisallow: /\n";
		}
	}
	return $out;
}, 20 );

/* =============================================================
   Virtual endpoints — JSON Feed (#101) + image sitemap (#98).
   Matched on the path (like inc/og-cards.php) so no rewrite flush
   is required.
   ============================================================= */
add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );

	if ( $path === 'feed/json' || $path === 'feed/json.json' ) { nr_render_json_feed(); }
	if ( $path === 'sitemap-images.xml' ) { nr_render_image_sitemap(); }
}, 0 );

/* #101 — JSON Feed 1.1 of recent projects + journal. */
function nr_render_json_feed() {
	$items = [];
	$q = new WP_Query( [ 'post_type' => [ 'nr_project', 'nr_journal' ], 'posts_per_page' => 25, 'no_found_rows' => true ] );
	while ( $q->have_posts() ) { $q->the_post();
		$it = [
			'id'             => get_permalink(),
			'url'            => get_permalink(),
			'title'          => wp_strip_all_tags( get_the_title() ),
			'date_published' => get_the_date( 'c' ),
			'date_modified'  => get_the_modified_date( 'c' ),
			'summary'        => wp_strip_all_tags( get_the_excerpt() ),
			'authors'        => [ [ 'name' => nr_opt( 'nr_about_name', 'Nishuthan Raveenthiran' ) ] ],
		];
		$img = get_the_post_thumbnail_url( get_the_ID(), 'nr-hero' );
		if ( $img ) $it['image'] = $img;
		$items[] = $it;
	}
	wp_reset_postdata();
	$feed = [
		'version'       => 'https://jsonfeed.org/version/1.1',
		'title'         => get_bloginfo( 'name' ),
		'home_page_url' => home_url( '/' ),
		'feed_url'      => home_url( '/feed/json' ),
		'description'   => wp_strip_all_tags( (string) nr_opt( 'nr_tagline', '' ) ),
		'items'         => $items,
	];
	nocache_headers();
	header( 'Content-Type: application/feed+json; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	echo wp_json_encode( $feed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
}

/* #98 — image sitemap with captions. */
function nr_render_image_sitemap() {
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
	$q = new WP_Query( [ 'post_type' => [ 'nr_project', 'nr_journal' ], 'posts_per_page' => 500, 'no_found_rows' => true ] );
	while ( $q->have_posts() ) { $q->the_post();
		$imgs = [];
		$thumb = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		if ( $thumb ) $imgs[] = [ $thumb, get_the_title() ];
		$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : [];
		if ( is_array( $gallery ) ) {
			foreach ( array_slice( $gallery, 0, 20 ) as $g ) {
				$aid = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : ( is_numeric( $g ) ? (int) $g : 0 );
				if ( ! $aid || wp_attachment_is( 'video', $aid ) ) continue;
				$u = wp_get_attachment_image_url( $aid, 'full' );
				$cap = wp_get_attachment_caption( $aid );
				if ( $u ) $imgs[] = [ $u, $cap ?: get_the_title() ];
			}
		}
		if ( ! $imgs ) continue;
		echo '  <url>' . "\n    <loc>" . esc_url( get_permalink() ) . "</loc>\n";
		foreach ( $imgs as $im ) {
			echo '    <image:image><image:loc>' . esc_url( $im[0] ) . '</image:loc>';
			if ( $im[1] ) echo '<image:caption>' . htmlspecialchars( wp_strip_all_tags( $im[1] ), ENT_XML1 ) . '</image:caption>';
			echo "</image:image>\n";
		}
		echo "  </url>\n";
	}
	wp_reset_postdata();
	echo '</urlset>';
	exit;
}
/* Advertise both feeds. */
add_action( 'wp_head', function () {
	if ( is_admin() ) return;
	printf( '<link rel="alternate" type="application/feed+json" title="%s" href="%s">' . "\n",
		esc_attr( get_bloginfo( 'name' ) . ' (JSON Feed)' ), esc_url( home_url( '/feed/json' ) ) );
}, 3 );

/* =============================================================
   #100 — WebSub: ping the public hub when something is published
   so feed subscribers update instantly.
   ============================================================= */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( $new !== 'publish' || ! in_array( $post->post_type, [ 'nr_project', 'nr_journal' ], true ) ) return;
	if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) return;
	foreach ( [ get_bloginfo( 'rss2_url' ), home_url( '/feed/journal-feed/' ) ] as $feed ) {
		wp_remote_post( 'https://pubsubhubbub.appspot.com/', [
			'timeout' => 6, 'blocking' => false,
			'body'    => [ 'hub.mode' => 'publish', 'hub.url' => $feed ],
		] );
	}
}, 10, 3 );

/* =============================================================
   #113 — on-demand IndexNow bulk resubmit (admin-post). Pings
   every project/journal/page URL via nr_indexnow_ping (defined
   in inc/ideas-next.php). Linked from the content-health widget.
   ============================================================= */
add_action( 'admin_post_nr_indexnow_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_indexnow_bulk' ) ) wp_die( 'no' );
	$n = 0;
	if ( function_exists( 'nr_indexnow_ping' ) && function_exists( 'nr_indexnow_key' ) && nr_indexnow_key() ) {
		$q = new WP_Query( [ 'post_type' => [ 'nr_project', 'nr_journal', 'page' ], 'posts_per_page' => 300, 'no_found_rows' => true, 'fields' => 'ids' ] );
		foreach ( $q->posts as $pid ) { nr_indexnow_ping( get_permalink( $pid ) ); $n++; }
	}
	wp_safe_redirect( admin_url( 'index.php?nr_indexnow=' . $n ) );
	exit;
} );
add_action( 'admin_notices', function () {
	if ( isset( $_GET['nr_indexnow'] ) ) {
		printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf( __( 'IndexNow: pinged %d URLs.', 'raveenthiran' ), (int) $_GET['nr_indexnow'] ) ) );
	}
} );

/* =============================================================
   #110 — search-term capture. The ⌘K palette beacons queries to
   a REST route; we keep a capped tally so the owner can see what
   visitors look for (content-gap signal). No personal data.
   ============================================================= */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/search-log', [
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( $req ) {
			$q = sanitize_text_field( (string) $req->get_param( 'q' ) );
			$q = trim( mb_strtolower( $q ) );
			if ( $q === '' || mb_strlen( $q ) > 60 ) return new WP_REST_Response( [ 'ok' => false ], 200 );
			$log = get_option( 'nr_search_log', [] );
			if ( ! is_array( $log ) ) $log = [];
			$log[ $q ] = ( $log[ $q ] ?? 0 ) + 1;
			if ( count( $log ) > 500 ) { arsort( $log ); $log = array_slice( $log, 0, 300, true ); }
			update_option( 'nr_search_log', $log, false );
			return new WP_REST_Response( [ 'ok' => true ], 200 );
		},
	] );
} );

/* =============================================================
   #90 — generalised LCP preload. front-page.php already preloads
   its hero; do the same for single projects/journal/pages with a
   featured image (the most common LCP element elsewhere).
   ============================================================= */
add_action( 'wp_head', function () {
	if ( is_front_page() || is_admin() ) return;
	if ( ! is_singular() || ! has_post_thumbnail() ) return;
	$tid = get_post_thumbnail_id();
	$src = wp_get_attachment_image_url( $tid, 'nr-hero' );
	if ( ! $src ) return;
	$srcset = wp_get_attachment_image_srcset( $tid, 'nr-hero' );
	$type = '';
	if ( function_exists( 'nr_webp_twin_url' ) ) {
		$w = nr_webp_twin_url( $src );
		if ( $w ) { $src = $w; $type = ' type="image/webp"'; }
		if ( $srcset && function_exists( 'nr_webp_swap_srcset' ) ) $srcset = nr_webp_swap_srcset( $srcset );
	}
	printf(
		'<link rel="preload" as="image" href="%s"%s%s imagesizes="(max-width:900px) 100vw, 70vw" fetchpriority="high">' . "\n",
		esc_url( $src ),
		$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
		$type
	);
}, 2 );
