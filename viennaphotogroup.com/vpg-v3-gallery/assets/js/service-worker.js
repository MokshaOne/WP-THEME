/**
 * VPG Service Worker
 * Strategy: Cache shell only (CSS, JS, fonts, offline page)
 * Content always fetched fresh from the network.
 */

var CACHE_NAME    = 'vpg-shell-v2';
var ISSUE_CACHE   = 'vpg-issues-v1';
var OFFLINE_URL   = '/offline/';

// The SW lives at <theme>/assets/js/service-worker.js — derive the theme base
// so the shell list survives theme-directory renames.
var THEME_BASE = self.location.pathname.replace(/assets\/js\/service-worker\.js$/, '');

// Files to precache on install — the app shell
var SHELL_ASSETS = [
    '/',
    '/offline/',
    THEME_BASE + 'assets/css/gallery.css',
    THEME_BASE + 'assets/css/base.css',
    THEME_BASE + 'assets/css/layout.css',
    THEME_BASE + 'assets/css/components.css',
    THEME_BASE + 'assets/css/fonts.css',
    THEME_BASE + 'assets/js/main.js',
];

// ── Install: cache the shell (tolerant · one 404 must not kill the SW) ───────
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return Promise.all(SHELL_ASSETS.map(function (asset) {
                return cache.add(asset).catch(function () {});
            }));
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// ── Offline issues · the page sends a list of URLs to keep ───────────────────
self.addEventListener('message', function (event) {
    var data = event.data || {};
    if (data.type === 'VPG_CACHE_ISSUE' && Array.isArray(data.urls)) {
        event.waitUntil(
            caches.open(ISSUE_CACHE).then(function (cache) {
                return Promise.all(data.urls.map(function (u) {
                    return cache.add(u).catch(function () {});
                }));
            }).then(function () {
                if (event.source) event.source.postMessage({ type: 'VPG_ISSUE_CACHED', key: data.key || '' });
            })
        );
    }
    if (data.type === 'VPG_DROP_ISSUE' && Array.isArray(data.urls)) {
        event.waitUntil(
            caches.open(ISSUE_CACHE).then(function (cache) {
                return Promise.all(data.urls.map(function (u) { return cache.delete(u); }));
            })
        );
    }
});

// ── Activate: remove old caches ───────────────────────────────────────────────
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (key) { return key !== CACHE_NAME && key !== ISSUE_CACHE; })
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

    // Everything else: network first · offline falls back to a saved issue,
    // then the offline page for HTML navigations
    event.respondWith(
        fetch(request).catch(function () {
            return caches.match(request).then(function (cached) {
                if (cached) return cached;
                if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
                    return caches.match(OFFLINE_URL);
                }
            });
        })
    );
});


/* 0607 · Web push — payloads are encrypted server-side (RFC 8291) */
self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (e) {}
    event.waitUntil(self.registration.showNotification(data.title || 'Viennaphotogroup.', {
        body: data.body || '',
        icon: '/wp-content/themes/vpg-v3-gallery/assets/img/icon-192.png',
        badge: '/wp-content/themes/vpg-v3-gallery/assets/img/icon-192.png',
        data: { url: data.url || '/' }
    }));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
        for (var i = 0; i < list.length; i++) {
            if (list[i].url === url && 'focus' in list[i]) return list[i].focus();
        }
        return clients.openWindow(url);
    }));
});
