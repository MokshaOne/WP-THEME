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

	$work_url    = get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' );
	$enquire_url = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );

	$manifest = [
		'id'               => home_url( '/' ),
		'name'             => $name,
		'short_name'       => mb_substr( $name, 0, 18 ),
		'description'      => nr_opt( 'nr_tagline', get_bloginfo( 'description' ) ),
		'start_url'        => home_url( '/?utm_source=pwa' ),
		'scope'            => home_url( '/' ),
		'display'          => 'standalone',
		'display_override' => [ 'standalone', 'minimal-ui', 'browser' ],
		'orientation'      => 'portrait-primary',
		'background_color' => $bg,
		'theme_color'      => $theme,
		'lang'             => get_bloginfo( 'language' ),
		'dir'              => is_rtl() ? 'rtl' : 'ltr',
		'categories'       => [ 'photography', 'portfolio', 'art' ],
		'icons'            => $icons,
		'shortcuts'        => [
			[ 'name' => __( 'Work', 'raveenthiran' ),    'url' => $work_url ],
			[ 'name' => __( 'Enquire', 'raveenthiran' ), 'url' => $enquire_url ],
		],
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
const PRECACHE = <?php echo $precache; ?>;
const OFFLINE = '<?php echo esc_js( $offline ); ?>';

self.addEventListener('install', (e) => {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(PRECACHE).catch(() => {})));
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
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
    e.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((c) => c.put(req, copy)).catch(() => {});
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

	// Install affordance can be turned off per-site.
	$show_install = nr_opt( 'nr_pwa_install', '1' ) !== '0';
	$install_lbl  = nr_opt( 'nr_pwa_install_label', __( 'Install app', 'raveenthiran' ) );
	?>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('<?php echo esc_js( $sw ); ?>', { scope: '/' }).catch(function () {});
  });
}
</script>
<?php if ( $show_install ) : ?>
<style>
.nr-pwa-install{position:fixed;left:max(18px,env(safe-area-inset-left));bottom:calc(18px + env(safe-area-inset-bottom));z-index:70;display:none;align-items:center;gap:9px;
  padding:11px 16px;border-radius:100px;border:1px solid var(--accent,#DAC769);background:rgba(19,19,19,.72);
  color:var(--ink,#F2EFE9);font:600 11px/1 var(--ff-mono,ui-monospace,monospace);letter-spacing:.16em;text-transform:uppercase;
  cursor:pointer;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 10px 40px rgba(0,0,0,.5);
  transition:transform .3s cubic-bezier(.7,0,.2,1),opacity .3s,background .3s}
.nr-pwa-install.is-ready{display:inline-flex;animation:nrPwaIn .5s cubic-bezier(.7,0,.2,1) both}
.nr-pwa-install:hover{background:var(--accent,#DAC769);color:#131313;transform:translateY(-2px)}
.nr-pwa-install__i{width:15px;height:15px;flex:0 0 auto}
.nr-pwa-install__x{margin-left:2px;opacity:.6;font-size:13px;line-height:1}
.nr-pwa-install:hover .nr-pwa-install__x{opacity:1}
@keyframes nrPwaIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media (prefers-reduced-motion:reduce){.nr-pwa-install.is-ready{animation:none}}
@media (max-width:640px){.nr-pwa-install{bottom:calc(78px + env(safe-area-inset-bottom))}}
</style>
<button type="button" class="nr-pwa-install" aria-label="<?php echo esc_attr( $install_lbl ); ?>" hidden>
  <svg class="nr-pwa-install__i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
  <span><?php echo esc_html( $install_lbl ); ?></span>
  <span class="nr-pwa-install__x" data-pwa-dismiss aria-hidden="true">&times;</span>
</button>
<script>
(function () {
  var btn = document.querySelector('.nr-pwa-install');
  if (!btn) return;
  var deferred = null;
  var KEY = 'nr_pwa_dismissed';
  try { if (sessionStorage.getItem(KEY) === '1') return; } catch (e) {}
  // Already installed → never show.
  if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) return;

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    btn.hidden = false;
    requestAnimationFrame(function () { btn.classList.add('is-ready'); });
  });

  btn.addEventListener('click', function (e) {
    if (e.target.closest('[data-pwa-dismiss]')) {
      btn.classList.remove('is-ready'); btn.hidden = true;
      try { sessionStorage.setItem(KEY, '1'); } catch (err) {}
      return;
    }
    if (!deferred) return;
    deferred.prompt();
    deferred.userChoice.then(function () {
      deferred = null; btn.classList.remove('is-ready'); btn.hidden = true;
    });
  });

  window.addEventListener('appinstalled', function () {
    btn.classList.remove('is-ready'); btn.hidden = true;
    try { sessionStorage.setItem(KEY, '1'); } catch (err) {}
  });
})();
</script>
<?php endif;
}, 99 );

/* ── Minimal offline notice injected when ?nr_offline=1 shell is shown ── */
add_action( 'wp_body_open', function () {
	if ( empty( $_GET['nr_offline'] ) ) return;
	echo '<div style="position:fixed;inset:auto 0 0 0;z-index:9999;background:#0B0C10;color:#F2EFE9;'
		. 'font:500 13px/1.4 system-ui,sans-serif;padding:12px 18px;text-align:center;border-top:1px solid rgba(242,160,61,.4)">'
		. esc_html__( 'You appear to be offline — showing a cached version. Reconnect to load the latest.', 'raveenthiran' )
		. '</div>';
} );
