/* Global UI behaviour. Every piece is guarded by its root element, and the
   whole thing re-initialises on `astro:page-load` so it survives Astro View
   Transitions. Document-level listeners are registered once at module scope. */
(function () {
	'use strict';
	var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
	var heroTimer = null, revealIO = null;
	var LB = { open: false, i: 0, figs: [], el: null, stage: null, count: null, last: null };

	/* ── theme toggle ── */
	function initTheme() {
		var btn = document.querySelector('[data-theme-toggle]');
		if (!btn) return;
		btn.addEventListener('click', function () {
			var cur = document.documentElement.dataset.theme;
			var dark = cur ? cur === 'dark' : matchMedia('(prefers-color-scheme:dark)').matches;
			var next = dark ? 'light' : 'dark';
			document.documentElement.dataset.theme = next;
			try { localStorage.setItem('theme', next); } catch (e) {}
		});
	}

	/* ── overlay menu ── */
	function initOverlay() {
		var btn = document.querySelector('[data-ovl-toggle]');
		var ovl = document.querySelector('[data-ovl]');
		if (!btn || !ovl) return;
		function set(open) {
			document.body.classList.toggle('ovl-open', open);
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			ovl.setAttribute('aria-hidden', open ? 'false' : 'true');
		}
		btn.addEventListener('click', function () { set(!document.body.classList.contains('ovl-open')); });
		[].forEach.call(ovl.querySelectorAll('a'), function (a) { a.addEventListener('click', function () { set(false); }); });
	}

	/* ── scroll reveal ── */
	function initReveal() {
		var els = [].slice.call(document.querySelectorAll('[data-reveal]'));
		if (!els.length) return;
		if (reduce || !('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
		if (revealIO) revealIO.disconnect();
		revealIO = new IntersectionObserver(function (ents) {
			ents.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); revealIO.unobserve(en.target); } });
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
		els.forEach(function (e) { revealIO.observe(e); });
	}

	/* ── cinematic home hero (master) ── */
	function initCine() {
		if (heroTimer) { clearInterval(heroTimer); heroTimer = null; }
		var hero = document.querySelector('[data-cine]');
		if (!hero) return;
		var slides = [].slice.call(hero.querySelectorAll('.cine__slide'));
		var titleEl = hero.querySelector('[data-cine-title]');
		var eyebrowEl = hero.querySelector('[data-cine-eyebrow]');
		var linkEl = hero.querySelector('[data-cine-link]');
		var curEl = hero.querySelector('[data-cine-cur]');
		var progEl = hero.querySelector('[data-cine-prog]');
		var prev = hero.querySelector('[data-cine-prev]');
		var next = hero.querySelector('[data-cine-next]');
		if (!slides.length) return;
		var i = 0;
		function pad(n) { return n < 10 ? '0' + n : '' + n; }
		function preload(n) { var s = slides[(n + slides.length) % slides.length]; var im = s && s.querySelector('img'); if (im && im.dataset.src) { var x = new Image(); x.src = im.dataset.src; } }
		function go(n) {
			i = (n + slides.length) % slides.length;
			slides.forEach(function (s, k) { s.classList.toggle('on', k === i); });
			var s = slides[i];
			if (titleEl) titleEl.textContent = s.getAttribute('data-title') || '';
			if (eyebrowEl) eyebrowEl.textContent = s.getAttribute('data-cat') || '';
			if (linkEl) linkEl.setAttribute('href', '/work/' + s.getAttribute('data-slug') + '/');
			if (curEl) curEl.textContent = pad(i + 1);
			if (progEl) { progEl.classList.remove('run'); void progEl.offsetWidth; if (!reduce && slides.length > 1) progEl.classList.add('run'); }
			preload(i + 1);
		}
		function arm() { if (heroTimer) clearInterval(heroTimer); if (!reduce && slides.length > 1) heroTimer = setInterval(function () { go(i + 1); }, 6000); }
		if (prev) prev.addEventListener('click', function () { go(i - 1); arm(); });
		if (next) next.addEventListener('click', function () { go(i + 1); arm(); });
		hero._cineNav = function (d) { go(i + d); arm(); };
		go(0); arm();
	}

	/* ── scroll progress + header-light over cine hero ── */
	function initProgress() {
		var bar = document.querySelector('[data-progress]');
		var cine = document.querySelector('[data-cine]');
		if (!cine) document.body.classList.remove('header-light');
		function onScroll() {
			var h = document.documentElement;
			var max = h.scrollHeight - h.clientHeight;
			var y = window.scrollY || h.scrollTop;
			if (bar) bar.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
			if (cine) document.body.classList.toggle('header-light', y < cine.offsetHeight - 80);
		}
		window.removeEventListener('scroll', onScroll);
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ── work: album filter + grid/index view ── */
	function initWork() {
		var root = document.querySelector('[data-work]');
		if (!root) return;
		var filterBtns = [].slice.call(root.querySelectorAll('.filter-btn'));
		var viewBtns = [].slice.call(root.querySelectorAll('.view-toggle button'));
		var grid = root.querySelector('[data-grid]');
		var index = root.querySelector('[data-index]');
		var empty = root.querySelector('[data-empty]');
		var current = '*';
		function apply() {
			var shown = 0;
			[grid, index].forEach(function (c) {
				if (!c) return;
				[].slice.call(c.children).forEach(function (item) {
					var match = current === '*' || item.getAttribute('data-cat') === current;
					item.hidden = !match;
					if (match && c === grid) shown++;
				});
			});
			if (empty) empty.hidden = shown !== 0;
		}
		filterBtns.forEach(function (b) {
			b.addEventListener('click', function () {
				current = b.getAttribute('data-filter');
				filterBtns.forEach(function (x) { x.classList.toggle('is-on', x === b); });
				apply();
				try { history.replaceState(null, '', current === '*' ? location.pathname : '#' + current); } catch (e) {}
			});
		});
		viewBtns.forEach(function (b) {
			b.addEventListener('click', function () {
				var v = b.getAttribute('data-view');
				viewBtns.forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
				if (grid) grid.hidden = v !== 'grid';
				if (index) index.hidden = v !== 'index';
			});
		});
		var hash = (location.hash || '').replace('#', '');
		if (hash) { var m = filterBtns.find(function (b) { return b.getAttribute('data-filter') === hash; }); if (m) m.click(); }
	}

	/* ── lightbox ── */
	function lbRender() {
		var f = LB.figs[LB.i];
		var src = f.getAttribute('data-src');
		var label = f.getAttribute('data-label') || '';
		LB.stage.innerHTML = src ? '<img class="lb__img" src="' + src + '" alt="">' : '<div class="lb__ph">' + (label || (LB.i + 1)) + '</div>';
		LB.count.textContent = (LB.i + 1) + ' / ' + LB.figs.length;
		[LB.i + 1, LB.i - 1].forEach(function (n) { var g = LB.figs[(n + LB.figs.length) % LB.figs.length]; var s = g && g.getAttribute('data-src'); if (s) { var im = new Image(); im.src = s; } });
	}
	function lbStep(d) { LB.i = (LB.i + d + LB.figs.length) % LB.figs.length; lbRender(); }
	function lbOpen(n) {
		LB.i = n; LB.last = document.activeElement; LB.open = true;
		lbRender(); LB.el.classList.add('is-open'); LB.el.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden'; document.body.classList.add('lb-open');
	}
	function lbClose() {
		LB.open = false; LB.el.classList.remove('is-open'); LB.el.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = ''; document.body.classList.remove('lb-open');
		if (LB.last && LB.last.focus) LB.last.focus();
	}
	function initLightbox() {
		var gallery = document.querySelector('[data-gallery]');
		var el = document.querySelector('[data-lb]');
		LB.open = false; LB.figs = []; LB.el = el;
		if (!gallery || !el) return;
		LB.figs = [].slice.call(gallery.querySelectorAll('figure'));
		LB.stage = el.querySelector('[data-lb-stage]');
		LB.count = el.querySelector('[data-lb-count]');
		LB.figs.forEach(function (f, n) {
			f.setAttribute('tabindex', '0');
			f.addEventListener('click', function () { lbOpen(n); });
			f.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); lbOpen(n); } });
		});
		el.querySelector('[data-lb-close]').addEventListener('click', lbClose);
		el.querySelector('[data-lb-prev]').addEventListener('click', function () { lbStep(-1); });
		el.querySelector('[data-lb-next]').addEventListener('click', function () { lbStep(1); });
		el.addEventListener('click', function (e) { if (e.target === el) lbClose(); });
		var x0 = null;
		el.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
		el.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) lbStep(dx < 0 ? 1 : -1); x0 = null; }, { passive: true });
	}

	/* ── enquire price engine ── */
	function initPrice() {
		var form = document.querySelector('[data-price-engine]');
		if (!form) return;
		var cur = form.getAttribute('data-currency') || '€';
		var outs = [].slice.call(document.querySelectorAll('[data-estimate]'));
		function money(n) { return cur + Math.round(n).toLocaleString('en-US'); }
		function num(el, a) { return el ? (parseFloat(el.getAttribute(a)) || 0) : 0; }
		var shown = 0, raf = null;
		function paint(v) { var t = money(v); outs.forEach(function (o) { o.textContent = t; }); }
		function animateTo(total) {
			if (reduce || shown === total) { shown = total; paint(total); return; }
			if (raf) cancelAnimationFrame(raf);
			var from = shown, delta = total - from, t0 = performance.now();
			(function step(now) { var p = Math.min(1, (now - t0) / 420), e = 1 - Math.pow(1 - p, 3); paint(from + delta * e); if (p < 1) raf = requestAnimationFrame(step); else { shown = total; paint(total); } })(t0);
		}
		function compute() {
			var parts = [], total = 0;
			var type = form.querySelector('input[name="project_type"]:checked');
			if (type) { total += num(type, 'data-base'); parts.push(type.value); }
			[].forEach.call(form.querySelectorAll('input[name="addons[]"]:checked'), function (x) { total += num(x, 'data-price'); parts.push(x.value); });
			var lic = form.querySelector('input[name="license"]');
			if (lic && lic.checked) { total += num(lic, 'data-price'); parts.push('Commercial licence'); }
			var km = form.querySelector('input[name="travel_km"]');
			if (km) { var k = parseFloat(km.value) || 0; if (k > 0) { total += k * num(km, 'data-per-km'); parts.push(Math.round(k) + ' km'); } }
			animateTo(total);
			var hid = form.querySelector('[data-estimate-input]'); if (hid) hid.value = money(total);
			var bd = form.querySelector('[data-breakdown]'); if (bd) bd.value = parts.join(' · ');
		}
		form.addEventListener('change', compute);
		form.addEventListener('input', function (e) { if (e.target && e.target.name === 'travel_km') compute(); });
		compute();
		var to = form.getAttribute('data-mailto');
		if (to) form.addEventListener('submit', function (e) {
			e.preventDefault();
			var g = function (n) { var el = form.querySelector('[name="' + n + '"]'); return el ? el.value : ''; };
			var type = (form.querySelector('input[name="project_type"]:checked') || {}).value || '';
			var addons = [].slice.call(form.querySelectorAll('input[name="addons[]"]:checked')).map(function (x) { return x.value; }).join(', ');
			var body = ['Name: ' + g('name'), 'Email: ' + g('email'), 'Preferred date: ' + g('preferred_date'), 'Project type: ' + type, 'Add-ons: ' + addons, 'Estimate: ' + g('estimate'), '', g('notes')].join('\n');
			window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent('Project enquiry — ' + (g('name') || '')) + '&body=' + encodeURIComponent(body);
		});
	}

	/* ── document-level (once) ── */
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && document.body.classList.contains('ovl-open')) {
			var b = document.querySelector('[data-ovl-toggle]'); if (b) b.click();
		}
		if (!LB.open) {
			if (document.body.classList.contains('ovl-open')) return;
			var hero = document.querySelector('[data-cine]');
			if (hero && hero._cineNav && window.scrollY < hero.offsetHeight) {
				if (e.key === 'ArrowLeft') hero._cineNav(-1);
				else if (e.key === 'ArrowRight') hero._cineNav(1);
			}
			return;
		}
		if (e.key === 'Escape') lbClose();
		else if (e.key === 'ArrowLeft') lbStep(-1);
		else if (e.key === 'ArrowRight') lbStep(1);
	});

	/* preloader — reveal once the first page is ready (only shown on hard load) */
	function hidePreloader() { setTimeout(function () { document.documentElement.classList.remove('preloading'); }, 550); }
	if (document.readyState === 'complete') hidePreloader();
	else window.addEventListener('load', hidePreloader);
	// keep theme across view transitions (before paint)
	document.addEventListener('astro:after-swap', function () {
		try { var t = localStorage.getItem('theme'); if (t) document.documentElement.dataset.theme = t; } catch (e) {}
	});

	function boot() { initTheme(); initOverlay(); initReveal(); initCine(); initProgress(); initWork(); initLightbox(); initPrice(); }
	document.addEventListener('astro:page-load', boot);
})();
