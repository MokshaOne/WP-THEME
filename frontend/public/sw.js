/* Raveenthiran — service worker. Network-first for pages (always fresh online),
   cache as offline fallback; cache-first for hashed build assets. */
const CACHE = 'rvn-v1';
const CORE = ['/', '/work/', '/journal/', '/about/', '/enquire/', '/offline/', '/site.webmanifest', '/icon.svg'];

self.addEventListener('install', (e) => {
	e.waitUntil(caches.open(CACHE).then((c) => c.addAll(CORE.map((u) => new Request(u, { cache: 'reload' }))).catch(() => {})));
	self.skipWaiting();
});
self.addEventListener('activate', (e) => {
	e.waitUntil(caches.keys().then((ks) => Promise.all(ks.filter((k) => k !== CACHE).map((k) => caches.delete(k)))));
	self.clients.claim();
});
self.addEventListener('fetch', (e) => {
	const req = e.request;
	if (req.method !== 'GET') return;
	const url = new URL(req.url);
	if (url.origin !== location.origin) return; // leave WP images / cross-origin alone

	if (req.mode === 'navigate') {
		e.respondWith(
			fetch(req).then((r) => { const cp = r.clone(); caches.open(CACHE).then((c) => c.put(req, cp)); return r; })
				.catch(() => caches.match(req).then((r) => r || caches.match('/offline/')))
		);
		return;
	}
	e.respondWith(
		caches.match(req).then((r) => r || fetch(req).then((res) => { const cp = res.clone(); caches.open(CACHE).then((c) => c.put(req, cp)); return res; }))
	);
});
