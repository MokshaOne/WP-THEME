/* Global UI behaviour. Every piece is guarded by its root element, and the
   whole thing re-initialises on `astro:page-load` so it survives Astro View
   Transitions. Document-level listeners are registered once at module scope. */
(function () {
	'use strict';
	var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
	var heroTimer = null, revealIO = null;

	function focusables(c) {
		return [].slice.call(c.querySelectorAll('a[href],button:not([disabled]),input,textarea,select,[tabindex]:not([tabindex="-1"])'))
			.filter(function (el) { return el.offsetParent !== null; });
	}
	function trapFocus(c, e) {
		var f = focusables(c); if (!f.length) return;
		var first = f[0], last = f[f.length - 1];
		if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
	}
	var LB = { open: false, i: 0, figs: [], el: null, stage: null, count: null, last: null };

	/* ── language switch (UI strings) ── */
	function initI18n() {
		var data = document.getElementById('i18n-data');
		if (!data) return;
		var dict = {};
		try { dict = JSON.parse(data.textContent); } catch (e) { return; }
		var lang = 'en';
		try { lang = localStorage.getItem('lang') || 'en'; } catch (e) {}
		if (!dict[lang]) lang = 'en';
		function apply(l) {
			var d = dict[l] || dict.en || {};
			[].forEach.call(document.querySelectorAll('[data-i18n]'), function (n) {
				var k = n.getAttribute('data-i18n'); if (d[k] != null) n.textContent = d[k];
			});
			document.documentElement.lang = l;
			[].forEach.call(document.querySelectorAll('[data-lang]'), function (b) {
				var on = b.getAttribute('data-lang') === l;
				b.classList.toggle('is-on', on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}
		[].forEach.call(document.querySelectorAll('[data-lang]'), function (b) {
			if (b._i18n) return; b._i18n = true;
			b.addEventListener('click', function () { var l = b.getAttribute('data-lang'); try { localStorage.setItem('lang', l); } catch (e) {} apply(l); });
		});
		apply(lang);
	}

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
		var toggles = [].slice.call(document.querySelectorAll('[data-ovl-toggle]'));
		var ovl = document.querySelector('[data-ovl]');
		var hdrBtn = document.querySelector('.site-header [data-ovl-toggle]');
		if (!toggles.length || !ovl) return;
		function set(open) {
			document.body.classList.toggle('ovl-open', open);
			if (hdrBtn) hdrBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
			ovl.setAttribute('aria-hidden', open ? 'false' : 'true');
			if (open) { ovl._last = document.activeElement; var f = focusables(ovl); if (f[1]) setTimeout(function () { f[1].focus(); }, 60); }
			else if (ovl._last && ovl._last.focus) ovl._last.focus();
		}
		toggles.forEach(function (b) { b.addEventListener('click', function () { set(!document.body.classList.contains('ovl-open')); }); });
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
		if (LB.count) LB.count.textContent = (LB.i + 1) + ' / ' + LB.figs.length;
		var img = LB.stage.querySelector('.lb__img');
		if (img) {
			img.addEventListener('click', function () { img.classList.toggle('zoomed'); if (!img.classList.contains('zoomed')) img.style.transformOrigin = 'center'; });
			img.addEventListener('mousemove', function (e) {
				if (!img.classList.contains('zoomed')) return;
				var r = img.getBoundingClientRect();
				img.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
			});
		}
		[LB.i + 1, LB.i - 1].forEach(function (n) { var g = LB.figs[(n + LB.figs.length) % LB.figs.length]; var s = g && g.getAttribute('data-src'); if (s) { var im = new Image(); im.src = s; } });
	}
	function lbStep(d) { LB.i = (LB.i + d + LB.figs.length) % LB.figs.length; lbRender(); }
	function lbOpen(n) {
		LB.i = n; LB.last = document.activeElement; LB.open = true;
		lbRender(); LB.el.classList.add('is-open'); LB.el.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden'; document.body.classList.add('lb-open');
		var c = LB.el.querySelector('[data-lb-close]'); if (c) setTimeout(function () { c.focus(); }, 60);
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
		var endpoint = form.getAttribute('data-endpoint');
		var statusEl = form.querySelector('[data-form-status]');
		var submitBtn = form.querySelector('[data-submit]');
		function g(n) { var el = form.querySelector('[name="' + n + '"]'); return el ? el.value : ''; }
		function fields() {
			var type = (form.querySelector('input[name="project_type"]:checked') || {}).value || '';
			var addons = [].slice.call(form.querySelectorAll('input[name="addons[]"]:checked')).map(function (x) { return x.value; }).join(', ');
			return { name: g('name'), email: g('email'), project_type: type, addons: addons, estimate: g('estimate'), preferred_date: g('preferred_date'), notes: g('notes'), company: g('company') };
		}
		function setStatus(msg, ok) {
			if (!statusEl) return;
			statusEl.hidden = false; statusEl.textContent = msg;
			statusEl.classList.toggle('is-ok', !!ok); statusEl.classList.toggle('is-err', ok === false);
		}
		function mailtoFallback(f) {
			var body = ['Name: ' + f.name, 'Email: ' + f.email, 'Preferred date: ' + f.preferred_date, 'Project type: ' + f.project_type, 'Add-ons: ' + f.addons, 'Estimate: ' + f.estimate, '', f.notes].join('\n');
			window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent('Project enquiry — ' + (f.name || '')) + '&body=' + encodeURIComponent(body);
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var f = fields();
			if (!f.name || !f.email) { setStatus('Please add your name and email.', false); return; }
			if (!endpoint) { mailtoFallback(f); return; }
			if (submitBtn) { submitBtn.disabled = true; }
			setStatus('Sending…', null);
			fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(f) })
				.then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
				.then(function () {
					setStatus('Thank you — your enquiry is in. I’ll reply within 24 hours. Check your inbox for a confirmation.', true);
					form.reset(); var c = form.querySelector('input[name="project_type"]'); if (c) c.checked = true; compute();
				})
				.catch(function () { setStatus('Couldn’t reach the studio just now — opening your mail app instead…', false); setTimeout(function () { mailtoFallback(f); }, 900); })
				.then(function () { if (submitBtn) submitBtn.disabled = false; });
		});
	}

	/* ── stat count-up ── */
	function initCountup() {
		var groups = [].slice.call(document.querySelectorAll('[data-countup]'));
		if (!groups.length) return;
		function run(group) {
			[].forEach.call(group.querySelectorAll('[data-count]'), function (el) {
				var raw = el.getAttribute('data-count') || '';
				var m = raw.match(/^(\D*)(\d[\d,\.]*)(.*)$/);
				if (!m) return;
				var pre = m[1], target = parseFloat(m[2].replace(/,/g, '')), suf = m[3];
				if (reduce || !isFinite(target)) { el.textContent = raw; return; }
				var t0 = performance.now(), dur = 1200;
				(function step(now) {
					var p = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - p, 3);
					var v = Math.round(target * e);
					el.textContent = pre + v.toLocaleString('en-US') + suf;
					if (p < 1) requestAnimationFrame(step);
				})(t0);
			});
		}
		if (reduce || !('IntersectionObserver' in window)) { groups.forEach(run); return; }
		var io = new IntersectionObserver(function (ents) {
			ents.forEach(function (en) { if (en.isIntersecting) { run(en.target); io.unobserve(en.target); } });
		}, { threshold: 0.4 });
		groups.forEach(function (g) { io.observe(g); });
	}

	/* ── magnetic hover ── */
	function initMagnetic() {
		if (reduce || matchMedia('(hover:none)').matches) return;
		[].forEach.call(document.querySelectorAll('[data-magnetic]'), function (el) {
			if (el._mag) return; el._mag = true;
			el.addEventListener('mousemove', function (e) {
				var r = el.getBoundingClientRect();
				var x = e.clientX - (r.left + r.width / 2), y = e.clientY - (r.top + r.height / 2);
				el.style.transform = 'translate(' + x * 0.25 + 'px,' + y * 0.25 + 'px)';
			});
			el.addEventListener('mouseleave', function () { el.style.transform = ''; });
		});
	}

	/* ── document-level (once) ── */
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Tab') {
			if (document.body.classList.contains('ovl-open')) { var o = document.querySelector('[data-ovl]'); if (o) trapFocus(o, e); }
			else if (LB.open && LB.el) trapFocus(LB.el, e);
		}
		if (e.key === 'Escape' && document.body.classList.contains('ovl-open')) {
			var b = document.querySelector('.site-header [data-ovl-toggle]'); if (b) b.click();
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

	function boot() { initI18n(); initTheme(); initOverlay(); initReveal(); initCine(); initProgress(); initWork(); initLightbox(); initPrice(); initCountup(); initMagnetic(); }
	document.addEventListener('astro:page-load', boot);
})();
