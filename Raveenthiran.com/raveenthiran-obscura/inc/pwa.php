<?php
/**
 * #46 — Progressive Web App: installable + offline shell.
 *
 * Serves a dynamic web-app manifest and a service worker from the SITE ROOT
 * (so the SW scope is the whole site) without needing rewrite-rule flushes:
 * we intercept /nr-manifest.json and /nr-sw.js on template_redirect. The SW
 * uses a cache-first strategy for the theme's static assets (fonts, CSS, JS)
 * and a network-first strategy for pages, with a lightweight offline fallback
 * so the site stays navigable on flaky mobile connections.
 *
 * Icons come from the WordPress Site Icon (Customizer → Site Identity). If no
 * Site Icon is set the manifest still validates — it just won't be installable
 * until one exists, so SETUP.md asks the user to set one.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Bump when the SW logic or precache list changes so clients pick up a new SW. */
if ( ! defined( 'NR_PWA_VERSION' ) ) {
	define( 'NR_PWA_VERSION', defined( 'NR_THEME_VERSION' ) ? NR_THEME_VERSION : '1' );
}

/* ── Intercept the two virtual endpoints ──────────────────────── */
add_action( 'template_redirect', function () {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = trim( (string) $path, '/' );
	if ( $path === 'nr-manifest.json' ) { nr_pwa_serve_manifest(); }
	if ( $path === 'nr-sw.js' )         { nr_pwa_serve_sw(); }
}, 0 );

function nr_pwa_serve_manifest() {
	nocache_headers();
	header( 'Content-Type: application/manifest+json; charset=utf-8' );

	$name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$bg    = nr_opt( 'nr_color_bg', '#0B0C10' );
	$theme = nr_opt( 'nr_color_bg', '#0B0C10' );

	$icons = [];
	foreach ( [ 192, 512 ] as $size ) {
		$url = function_exists( 'get_site_icon_url' ) ? get_site_icon_url( $size ) : '';
		if ( $url ) {
			$icons[] = [
				'src'     => $url,
				'sizes'   => $size . 'x' . $size,
				'type'    => 'image/png',
				'purpose' => 'any maskable',
			];
		}
	}

	$manifest = [
		'name'             => $name,
		'short_name'       => mb_substr( $name, 0, 18 ),
		'description'      => nr_opt( 'nr_tagline', get_bloginfo( 'description' ) ),
		'start_url'        => home_url( '/?utm_source=pwa' ),
		'scope'            => home_url( '/' ),
		'display'          => 'standalone',
		'orientation'      => 'portrait-primary',
		'background_color' => $bg,
		'theme_color'      => $theme,
		'lang'             => get_bloginfo( 'language' ),
		'categories'       => [ 'photography', 'portfolio', 'art' ],
		'icons'            => $icons,
	];

	echo wp_json_encode( $manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	exit;
}

function nr_pwa_serve_sw() {
	header( 'Content-Type: application/javascript; charset=utf-8' );
	header( 'Service-Worker-Allowed: /' );
	nocache_headers(); // never cache the SW script itself

	$ver      = NR_PWA_VERSION;
	$assets   = get_template_directory_uri() . '/assets/';
	$home     = home_url( '/' );
	$offline  = home_url( '/?nr_offline=1' );

	/* Precache only the always-needed shell pieces. Images are cached at runtime. */
	$precache = wp_json_encode( array_values( array_filter( [
		$assets . 'css/theme.css?ver=' . $ver,
		$assets . 'css/fonts.css?ver=' . $ver,
		$assets . 'js/theme.js?ver=' . $ver,
		$home,
	] ) ), JSON_UNESCAPED_SLASHES );

	$cache_name = 'nr-shell-' . $ver;
	?>
const CACHE = '<?php echo esc_js( $cache_name ); ?>';
const READER = 'nr-reader';   // #25 — opened journal articles, kept across updates
const PRECACHE = <?php echo $precache; ?>;
const OFFLINE = '<?php echo esc_js( $offline ); ?>';

self.addEventListener('install', (e) => {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE).catch(() => {})));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE && k !== READER).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;          // skip cross-origin (CDNs, analytics)
  if (url.pathname.startsWith('/wp-admin')) return;          // never touch the dashboard
  if (url.pathname.startsWith('/wp-login')) return;
  if (url.search.indexOf('preview=true') !== -1) return;

  const isAsset = /\.(?:css|js|woff2?|ttf|otf|avif|webp|jpe?g|png|gif|svg|ico)$/i.test(url.pathname);

  if (isAsset) {
    // Cache-first for static assets.
    e.respondWith(
      caches.match(req).then((hit) => hit || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((c) => c.put(req, copy)).catch(() => {});
        return res;
      }).catch(() => hit))
    );
    return;
  }

  // Network-first for navigations, with cached shell / offline fallback.
  if (req.mode === 'navigate') {
    // #25 — journal articles go to a persistent cache so they're readable
    // offline (a tunnel/flight) and survive theme updates.
    const bucket = url.pathname.indexOf('/journal') === 0 ? READER : CACHE;
    e.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(bucket).then((c) => c.put(req, copy)).catch(() => {});
        return res;
      }).catch(() => caches.match(req).then((hit) => hit || caches.match(OFFLINE) || caches.match('<?php echo esc_js( $home ); ?>')))
    );
  }
});
	<?php
	exit;
}

/* ── Head: manifest link + theme-color + SW registration ──────── */
add_action( 'wp_head', function () {
	$manifest = home_url( '/nr-manifest.json' );
	echo '<link rel="manifest" href="' . esc_url( $manifest ) . '">' . "\n";
	if ( function_exists( 'get_site_icon_url' ) && ( $ic = get_site_icon_url( 180 ) ) ) {
		echo '<link rel="apple-touch-icon" href="' . esc_url( $ic ) . '">' . "\n";
	}
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
}, 2 );

add_action( 'wp_footer', function () {
	$sw = home_url( '/nr-sw.js' );
	?>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('<?php echo esc_js( $sw ); ?>', { scope: '/' }).catch(function () {});
  });
}
</script>
	<?php
}, 99 );

/* ── Minimal offline notice injected when ?nr_offline=1 shell is shown ── */
add_action( 'wp_body_open', function () {
	if ( empty( $_GET['nr_offline'] ) ) return;
	echo '<div style="position:fixed;inset:auto 0 0 0;z-index:9999;background:#0B0C10;color:#F2EFE9;'
		. 'font:500 13px/1.4 system-ui,sans-serif;padding:12px 18px;text-align:center;border-top:1px solid rgba(242,160,61,.4)">'
		. esc_html__( 'You appear to be offline — showing a cached version. Reconnect to load the latest.', 'raveenthiran' )
		. '</div>';
} );
