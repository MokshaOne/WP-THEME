/* Still — headless WordPress bridge.
   Pulls the `work` custom post type from the WordPress REST API and fills the
   home teasers, the /work/ archive, and the dynamic project page (view.html).
   Fully progressive: if the API is unreachable (NAS offline, CORS, etc.) the
   pages keep their built-in sample content — never a blank page.

   Requirements on the WordPress side:
     • the `work` CPT registered with  show_in_rest => true
     • CORS: Access-Control-Allow-Origin for this frontend's origin
   Change WP_BASE below to your WordPress REST root. */
(function () {
	'use strict';
	var WP_BASE = 'https://wp.m1o.at/wp-json/wp/v2';   // ← your WordPress (Cloudflare Tunnel)

	function api(path) {
		return fetch(WP_BASE + path, { mode: 'cors', credentials: 'omit' })
			.then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
	}
	function feat(p) { try { return p._embedded['wp:featuredmedia'][0].source_url; } catch (e) { return ''; } }
	function cat(p)  { try { return p._embedded['wp:term'][0][0].name; } catch (e) { return ''; } }
	function dec(s)  { var t = document.createElement('textarea'); t.innerHTML = s || ''; return t.value; }
	function pad(n)  { n = String(n); return n.length < 2 ? '0' + n : n; }
	function year(p) { return (p.date || '').slice(0, 4); }
	function esc(s)  { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
	function title(p){ return dec(p.title && p.title.rendered); }

	/* ── home: fill the Work teaser thumbnails ── */
	function initHome(el) {
		var wrap = document.getElementById('wp-thumbs');
		if (!wrap) return;
		var view = el.getAttribute('data-view') || 'work/view.html';
		api('/work?per_page=4&_embed&orderby=menu_order&order=asc').then(function (items) {
			if (!items || !items.length) return; // keep static fallback
			wrap.innerHTML = items.map(function (p) {
				var img = feat(p), t = title(p);
				var inner = img
					? '<span class="frame"><img src="' + esc(img) + '" alt="' + esc(t) + '" loading="lazy"></span>'
					: '<span class="frame"><span class="ph">Photo</span></span>';
				return '<a href="' + view + '?slug=' + encodeURIComponent(p.slug) + '">' + inner + '</a>';
			}).join('');
		}).catch(function () {});
	}

	/* ── /work/ archive: replace sample media panels with live projects ── */
	function initArchive(el) {
		var track = document.getElementById('wp-track');
		var head = track && track.querySelector('.hx-panel--head');
		if (!track || !head) return;
		var view = el.getAttribute('data-view') || 'view.html';
		api('/work?per_page=24&_embed&orderby=menu_order&order=asc').then(function (items) {
			if (!items || !items.length) return;
			[].slice.call(track.querySelectorAll('.hx-panel--media')).forEach(function (n) { n.remove(); });
			var html = items.map(function (p, i) {
				var img = feat(p), c = cat(p), t = title(p);
				var media = img
					? '<img src="' + esc(img) + '" alt="' + esc(t) + '" loading="lazy">'
					: '<span class="ph-plate"><span class="ph-plate__t">' + esc(t.toLowerCase()) + '</span></span>';
				return '<a class="hx-panel hx-panel--media" href="' + view + '?slug=' + encodeURIComponent(p.slug) + '">' +
					'<figure>' + media + '<figcaption><span class="idx">' + pad(i + 1) + '</span><span>' + esc(t) + '</span>' +
					(c ? '<span>·&nbsp; ' + esc(c) + '</span>' : '') + '</figcaption></figure></a>';
			}).join('');
			head.insertAdjacentHTML('afterend', html);
		}).catch(function () {});
	}

	/* ── view.html: render one project by ?slug= ── */
	function panelMsg(text) {
		return '<section class="hx-panel hx-panel--head"><div class="page-head">' +
			'<span class="label">Work</span><h1 class="page-title">' + esc(text) + '</h1>' +
			'<div class="page-lead"><a href="./" style="border-bottom:1px solid var(--line)">← All work</a></div></div></section>';
	}
	function renderSingle(host, p) {
		var t = title(p), c = cat(p), y = year(p), img = feat(p);
		document.title = t + ' — Raveenthiran';
		var html = '';
		html += '<section class="hx-panel hx-panel--head"><div class="page-head">' +
			'<span class="label">' + esc(c || 'Project') + '</span>' +
			'<h1 class="page-title">' + esc(t) + '</h1>' +
			'<div class="single-meta"><span>' + esc(y) + '</span>' + (c ? '<span>' + esc(c) + '</span>' : '') + '</div>' +
			'</div></section>';
		if (img) html += '<div class="hx-panel hx-panel--media"><figure><img src="' + esc(img) + '" alt="' + esc(t) + '"></figure></div>';
		var rows = (y ? '<div class="row"><span class="k">Year</span><span>' + esc(y) + '</span></div>' : '') +
			(c ? '<div class="row"><span class="k">Category</span><span>' + esc(c) + '</span></div>' : '');
		if (rows) html += '<section class="hx-panel hx-panel--contact"><dl class="contact-list">' + rows + '</dl></section>';
		var content = p.content && p.content.rendered ? p.content.rendered : '';
		if (content.trim()) html += '<section class="hx-panel hx-panel--wide"><div class="prose">' + content + '</div></section>';
		html += '<section class="hx-panel hx-panel--end"><nav class="entry-nav">' +
			'<a href="./">← All work</a><a href="../enquire/index.html">Enquire about a shoot →</a></nav></section>';
		host.innerHTML = html;
	}
	function initSingle() {
		var host = document.getElementById('wp-single');
		if (!host) return;
		var slug = new URLSearchParams(location.search).get('slug');
		if (!slug) { host.innerHTML = panelMsg('No project selected.'); return; }
		api('/work?slug=' + encodeURIComponent(slug) + '&_embed').then(function (items) {
			var p = items && items[0];
			host.innerHTML && (host.innerHTML = '');
			if (!p) { host.innerHTML = panelMsg('Project not found.'); return; }
			renderSingle(host, p);
		}).catch(function () { host.innerHTML = panelMsg('Content is offline right now — try again later.'); });
	}

	function boot() {
		var home = document.querySelector('[data-wp="home"]');
		var arch = document.querySelector('[data-wp="work-archive"]');
		var one  = document.querySelector('[data-wp="work-single"]');
		if (home) initHome(home);
		if (arch) initArchive(arch);
		if (one)  initSingle();
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
