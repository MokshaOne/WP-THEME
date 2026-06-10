<?php
/**
 * inc/infra.php — Batch 6: security & infrastructure (v4.51.0).
 * #176 CSP header builder · #180 GDPR export/erase · #91 Early Hints ·
 * #106 self-hosted pageview analytics · #88/#93/#94 wired in JS (SW + virtual rails).
 * All opt-in / safe-by-default; CSP ships report-only first.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   #176 — Content-Security-Policy builder. Default OFF. Two modes:
   "report" (Content-Security-Policy-Report-Only — never blocks,
   logs violations) and "enforce". A nonce is exposed for inline
   theme scripts via nr_csp_nonce().
   ============================================================= */
function nr_csp_nonce() {
	static $n = null;
	if ( $n === null ) $n = wp_create_nonce( 'nr_csp' ) . substr( wp_generate_password( 8, false ), 0, 8 );
	return $n;
}
add_action( 'send_headers', function () {
	$mode = nr_opt( 'nr_csp_mode', 'off' );
	if ( $mode === 'off' || is_admin() ) return;
	$self = "'self'";
	// The theme inlines fonts.css + a little JSON/JS; allow self + the known CDNs
	// the optional features use (Leaflet, Turnstile, Google Calendar link target).
	$script = [ $self, "'unsafe-inline'", 'https://unpkg.com', 'https://challenges.cloudflare.com', 'https://calendar.google.com' ];
	$img    = [ $self, 'data:', 'https:', 'blob:' ];
	$style  = [ $self, "'unsafe-inline'", 'https://unpkg.com' ];
	$connect= [ $self, 'https://api.indexnow.org', 'https://pubsubhubbub.appspot.com', 'https://api.brevo.com' ];
	$frame  = [ $self, 'https://challenges.cloudflare.com', 'https://calendar.google.com' ];
	$extra  = trim( (string) nr_opt( 'nr_csp_extra', '' ) ); // owner can append hosts
	if ( $extra ) { foreach ( preg_split( '/\s+/', $extra ) as $h ) { $script[] = $h; $connect[] = $h; $frame[] = $h; } }
	$policy = implode( '; ', [
		"default-src $self",
		'script-src ' . implode( ' ', array_unique( $script ) ),
		'style-src ' . implode( ' ', array_unique( $style ) ),
		'img-src ' . implode( ' ', array_unique( $img ) ),
		'font-src ' . $self . ' data:',
		'connect-src ' . implode( ' ', array_unique( $connect ) ),
		'frame-src ' . implode( ' ', array_unique( $frame ) ),
		"object-src 'none'",
		"base-uri $self",
		'report-uri ' . esc_url_raw( home_url( '/?rest_route=/nr/v1/csp-report' ) ),
	] );
	$header = $mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
	header( $header . ': ' . $policy );
} );
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/csp-report', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ( $r ) {
		$b = json_decode( $r->get_body(), true );
		$blocked = $b['csp-report']['blocked-uri'] ?? ( $b['blocked-uri'] ?? '' );
		if ( $blocked ) {
			$log = get_option( 'nr_csp_log', [] ); if ( ! is_array( $log ) ) $log = [];
			$key = substr( (string) $blocked, 0, 120 );
			$log[ $key ] = ( $log[ $key ] ?? 0 ) + 1;
			if ( count( $log ) > 120 ) { arsort( $log ); $log = array_slice( $log, 0, 80, true ); }
			update_option( 'nr_csp_log', $log, false );
		}
		return new WP_REST_Response( null, 204 );
	} ] );
} );

/* =============================================================
   #180 — GDPR: register WP's native exporter + eraser for the
   theme's own data (enquiries + subscribers keyed by email).
   Works with Tools → Export/Erase Personal Data.
   ============================================================= */
add_filter( 'wp_privacy_personal_data_exporters', function ( $ex ) {
	$ex['obscura'] = [ 'exporter_friendly_name' => 'Obscura (enquiries & subscribers)', 'callback' => 'nr_gdpr_export' ];
	return $ex;
} );
function nr_gdpr_export( $email, $page = 1 ) {
	$items = [];
	$enq = get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 100, 'meta_key' => '_nr_email', 'meta_value' => $email ] );
	foreach ( $enq as $p ) {
		$items[] = [ 'group_id' => 'nr_enquiries', 'group_label' => 'Enquiries', 'item_id' => 'enq-' . $p->ID,
			'data' => [
				[ 'name' => 'Date', 'value' => get_the_date( 'c', $p ) ],
				[ 'name' => 'Message', 'value' => $p->post_content ],
				[ 'name' => 'Type', 'value' => get_post_meta( $p->ID, '_nr_type', true ) ],
				[ 'name' => 'Estimate', 'value' => get_post_meta( $p->ID, '_nr_est', true ) ],
			] ];
	}
	$sub = get_posts( [ 'post_type' => 'nr_subscriber', 'posts_per_page' => 10, 'title' => $email, 'post_status' => 'any' ] );
	foreach ( $sub as $p ) $items[] = [ 'group_id' => 'nr_subs', 'group_label' => 'Newsletter', 'item_id' => 'sub-' . $p->ID,
		'data' => [ [ 'name' => 'Email', 'value' => $p->post_title ], [ 'name' => 'Status', 'value' => $p->post_status ] ] ];
	return [ 'data' => $items, 'done' => true ];
}
add_filter( 'wp_privacy_personal_data_erasers', function ( $er ) {
	$er['obscura'] = [ 'eraser_friendly_name' => 'Obscura (enquiries & subscribers)', 'callback' => 'nr_gdpr_erase' ];
	return $er;
} );
function nr_gdpr_erase( $email, $page = 1 ) {
	$removed = 0;
	foreach ( get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 100, 'meta_key' => '_nr_email', 'meta_value' => $email ] ) as $p ) { wp_delete_post( $p->ID, true ); $removed++; }
	foreach ( get_posts( [ 'post_type' => 'nr_subscriber', 'posts_per_page' => 10, 'title' => $email, 'post_status' => 'any' ] ) as $p ) { wp_delete_post( $p->ID, true ); $removed++; }
	return [ 'items_removed' => $removed, 'items_retained' => false, 'messages' => [], 'done' => true ];
}

/* =============================================================
   #91 — HTTP 103 Early Hints (real, if the SAPI supports it).
   Sends preload hints for the inlined fonts before the body is
   built. Silently no-ops where headers_sent / unsupported.
   ============================================================= */
add_action( 'template_redirect', function () {
	if ( is_admin() || headers_sent() || ! function_exists( 'headers_send_early_hints' ) ) return;
	if ( ! is_singular() && ! is_front_page() ) return;
	$u = get_template_directory_uri() . '/assets/fonts/';
	@headers_send_early_hints( [
		'Link: <' . $u . 'inter-tight-500.woff2>; rel=preload; as=font; type=font/woff2; crossorigin',
		'Link: <' . $u . 'inter-tight-700.woff2>; rel=preload; as=font; type=font/woff2; crossorigin',
	] );
}, 0 );

/* =============================================================
   #106 — self-hosted pageview analytics (privacy-first, cookieless,
   admins excluded). Daily totals + top paths, shown in a widget.
   ============================================================= */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/pv', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function ( $r ) {
		$path = '/' . trim( sanitize_text_field( (string) $r->get_param( 'p' ) ), '/' );
		if ( strlen( $path ) > 80 ) $path = substr( $path, 0, 80 );
		$day = gmdate( 'Y-m-d' );
		$pv = get_option( 'nr_pv', [] ); if ( ! is_array( $pv ) ) $pv = [];
		$pv['days'][ $day ] = (int) ( $pv['days'][ $day ] ?? 0 ) + 1;
		$pv['paths'][ $path ] = (int) ( $pv['paths'][ $path ] ?? 0 ) + 1;
		// keep last 60 days + top 200 paths
		if ( isset( $pv['days'] ) && count( $pv['days'] ) > 60 ) { ksort( $pv['days'] ); $pv['days'] = array_slice( $pv['days'], -60, null, true ); }
		if ( isset( $pv['paths'] ) && count( $pv['paths'] ) > 200 ) { arsort( $pv['paths'] ); $pv['paths'] = array_slice( $pv['paths'], 0, 150, true ); }
		update_option( 'nr_pv', $pv, false );
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	} ] );
} );
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	wp_add_dashboard_widget( 'nr_analytics', __( 'Obscura — analytics (self-hosted)', 'raveenthiran' ), function () {
		$pv = get_option( 'nr_pv', [] );
		$days = $pv['days'] ?? []; $paths = $pv['paths'] ?? [];
		$total7 = 0; $i = 0; krsort( $days );
		foreach ( $days as $d => $n ) { if ( $i++ < 7 ) $total7 += $n; }
		printf( '<p style="margin:0 0 8px"><strong>%s</strong> %s</p>', esc_html( number_format_i18n( $total7 ) ), esc_html__( 'views · last 7 days (logged-out, cookieless)', 'raveenthiran' ) );
		arsort( $paths );
		echo '<ol style="margin:0;padding-left:18px;font-size:12px">';
		$j = 0; foreach ( $paths as $p => $n ) { if ( $j++ >= 6 ) break; printf( '<li><code>%s</code> — %s</li>', esc_html( $p ), esc_html( number_format_i18n( $n ) ) ); }
		echo '</ol>';
		if ( ! $paths ) echo '<p class="description">' . esc_html__( 'No data yet — visit the site logged-out.', 'raveenthiran' ) . '</p>';
	} );
} );

/* Expose the toggles/nonce the front-end scripts read. */
add_action( 'wp_head', function () {
	$flags = [
		'analytics' => nr_opt( 'nr_fx_analytics', '0' ) === '1' && ! is_user_logged_in(),
		'sw'        => nr_opt( 'nr_fx_sw', '0' ) === '1',
		'vrails'    => nr_opt( 'nr_fx_vrails', '0' ) === '1',
		'home'      => home_url( '/' ),
		'sw_url'    => home_url( '/nr-sw.js' ),
	];
	echo '<script>window.NRX=' . wp_json_encode( $flags ) . ';</script>' . "\n";
}, 4 );

/* =============================================================
   #93 / #94 — Service Worker served from a root-scoped virtual
   endpoint (/nr-sw.js) so it can control the whole origin. Offline
   fallback page + stale-while-revalidate for theme assets.
   ============================================================= */
add_action( 'template_redirect', function () {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path !== 'nr-sw.js' ) return;
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Service-Worker-Allowed: /' );
	header( 'Cache-Control: public, max-age=86400' );
	$ver  = NR_THEME_VERSION;
	$off  = esc_url_raw( home_url( '/?nr_offline=1' ) );
	$asset_base = get_template_directory_uri() . '/assets/';
	echo "/* Obscura SW v$ver */\n";
	?>
const V = 'nr-<?php echo esc_js( $ver ); ?>';
const OFFLINE = '<?php echo esc_js( $off ); ?>';
const ASSET = '<?php echo esc_js( $asset_base ); ?>';
self.addEventListener('install', e => { self.skipWaiting(); e.waitUntil(caches.open(V).then(c => c.add(OFFLINE).catch(()=>{}))); });
self.addEventListener('activate', e => { e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== V).map(k => caches.delete(k)))).then(() => self.clients.claim())); });
self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;
  // stale-while-revalidate for theme assets
  if (url.pathname.indexOf(new URL(ASSET).pathname) === 0) {
    e.respondWith(caches.open(V).then(c => c.match(req).then(hit => {
      const net = fetch(req).then(res => { if (res && res.ok) c.put(req, res.clone()); return res; }).catch(() => hit);
      return hit || net;
    })));
    return;
  }
  // navigations: network-first, fall back to the offline page
  if (req.mode === 'navigate') {
    e.respondWith(fetch(req).catch(() => caches.match(OFFLINE)));
  }
});
	<?php
	exit;
}, 0 );

/* A minimal offline page (served when SW falls back). */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['nr_offline'] ) ) return;
	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	$bg = '#0B0C10'; $ink = '#F2EFE9'; $amber = '#F2A03D';
	echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . esc_html__( 'Offline', 'raveenthiran' ) . '</title>';
	echo '<style>html,body{height:100%;margin:0;background:' . $bg . ';color:' . $ink . ';font-family:system-ui,sans-serif;display:grid;place-items:center;text-align:center}a{color:' . $amber . '}</style>';
	echo '<div><h1 style="font-weight:300">' . esc_html__( 'You’re offline', 'raveenthiran' ) . '</h1><p>' . esc_html__( 'The page isn’t cached. Reconnect and try again.', 'raveenthiran' ) . '</p><p><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'raveenthiran' ) . '</a></p></div>';
	exit;
}, 0 );
