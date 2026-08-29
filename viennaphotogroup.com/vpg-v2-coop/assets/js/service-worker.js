/**
 * VPG Service Worker
 * Strategy: Cache shell only (CSS, JS, fonts, offline page)
 * Content always fetched fresh from the network.
 */

var CACHE_NAME    = 'vpg-shell-v1';
var OFFLINE_URL   = '/offline/';

// Files to precache on install — the app shell
var SHELL_ASSETS = [
    '/',
    '/offline/',
    '/wp-content/themes/vpg-final/assets/css/tokens.css',
    '/wp-content/themes/vpg-final/assets/css/base.css',
    '/wp-content/themes/vpg-final/assets/css/layout.css',
    '/wp-content/themes/vpg-final/assets/css/components.css',
    '/wp-content/themes/vpg-final/assets/js/main.js',
];

// ── Install: cache the shell ──────────────────────────────────────────────────
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(SHELL_ASSETS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// ── Activate: remove old caches ───────────────────────────────────────────────
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) { return key !== CACHE_NAME; })
                    .map(function (key) { return caches.delete(key); })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// ── Fetch: shell from cache, content from network ─────────────────────────────
self.addEventListener('fetch', function (event) {
    var request = event.request;

    // Only handle GET requests
    if (request.method !== 'GET') return;

    // Skip WordPress admin, API and non-http requests
    var url = new URL(request.url);
    if (
        url.pathname.startsWith('/wp-admin') ||
        url.pathname.startsWith('/wp-json') ||
        url.pathname.startsWith('/wp-login') ||
        url.pathname.includes('admin-ajax') ||
        url.pathname.includes('admin-post') ||
        url.protocol !== 'https:'
    ) return;

    // Shell assets: cache first
    var isShellAsset = SHELL_ASSETS.some(function (asset) {
        return url.pathname === asset || request.url.includes('/assets/css/') || request.url.includes('/assets/js/');
    });

    if (isShellAsset) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                return cached || fetch(request).then(function (response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(request, clone); });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Google Fonts: cache first (they rarely change)
    if (
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com'
    ) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                return cached || fetch(request).then(function (response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(request, clone); });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Everything else: network first, fallback to offline page for HTML
    event.respondWith(
        fetch(request).catch(function () {
            // If it's a page navigation and we're offline, show offline page
            if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
                return caches.match(OFFLINE_URL);
            }
        })
    );
});
