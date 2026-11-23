/* Raveenthiran — service-worker KILL SWITCH.
   The previous PWA worker cached the app shell and kept serving stale builds,
   so the live site appeared to "revert" to an old version. This worker takes
   over from it, deletes every cache, unregisters itself, and reloads open
   tabs — then no service worker controls the site and the browser always
   fetches the freshest files from the server. */
self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
	event.waitUntil((async () => {
		try {
			const keys = await caches.keys();
			await Promise.all(keys.map((k) => caches.delete(k)));
		} catch (e) {}
		try { await self.registration.unregister(); } catch (e) {}
		try {
			const clients = await self.clients.matchAll({ type: 'window' });
			clients.forEach((c) => { try { c.navigate(c.url); } catch (e) {} });
		} catch (e) {}
	})());
});

/* Never intercept — let the browser fetch everything straight from the network. */
self.addEventListener('fetch', () => {});
