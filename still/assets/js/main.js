/* Still — cinematic intro (front page), austere dock, scroll reveals.
   Stoic build: slower, quieter, monumental. Vanilla, no dependencies. */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion:reduce)').matches;
	var $ = function (s) { return document.querySelector(s); };
	var $$ = function (s) { return [].slice.call(document.querySelectorAll(s)); };
	function after(ms, fn) { setTimeout(fn, reduce ? Math.min(ms, 140) : ms); }

	var intro = $('#intro'), home = $('#home'), dock = $('#dock');
	var panels = $$('.panel');

	/* ── scroll reveals (any page) ── */
	(function () {
		var els = $$('[data-rise]');
		if (!els.length) return;
		if (!('IntersectionObserver' in window) || reduce) { els.forEach(function (e) { e.classList.add('in'); }); return; }
		var root = (home && document.body.classList.contains('intro')) ? home : null;
		var io = new IntersectionObserver(function (es) {
			es.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
		}, { root: root, threshold: 0.15 });
		els.forEach(function (e) { io.observe(e); });
	})();

	/* ── dock nav (global) ── */
	function dockNav() {
		if (!dock) return;
		dock.querySelectorAll('a[data-key]').forEach(function (a) {
			a.addEventListener('click', function (e) {
				var t = document.getElementById('panel-' + a.getAttribute('data-key'));
				if (t) { e.preventDefault(); t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' }); }
			});
		});
		if ('IntersectionObserver' in window && panels.length) {
			var io = new IntersectionObserver(function (es) {
				es.forEach(function (en) {
					if (en.isIntersecting) {
						var k = en.target.getAttribute('data-key');
						dock.querySelectorAll('a[data-key]').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-key') === k); });
					}
				});
			}, { root: (document.body.classList.contains('intro') ? home : null), threshold: 0.6 });
			panels.forEach(function (p) { io.observe(p); });
		}
	}

	if (!intro) { if (home) dockNav(); return; }

	/* ══ CINEMATIC INTRO ══ */
	var boot = $('#boot'), fill = $('#fill'), pct = $('#pct'),
		term = $('#term'), titlewrap = $('#titlewrap'), bigtitle = $('#bigtitle'), enterbig = $('#enterbig');
	var state = 'boot';

	function b1() {
		requestAnimationFrame(function () { if (fill) fill.style.width = '100%'; });
		var t0 = performance.now(), dur = reduce ? 150 : 3400;
		(function tick() {
			var p = Math.min(1, (performance.now() - t0) / dur);
			if (pct) pct.textContent = Math.round(p * 100);
			if (p < 1) requestAnimationFrame(tick); else after(700, b2);
		})();
	}

	function b2() {
		if (boot) boot.classList.add('hide');
		if (term) term.classList.add('show');
		var pr = 'visitor@' + (location.hostname || 'raveenthiran') + ':~$ ';
		var typed = 'portfolio';
		term.innerHTML = '<div><span class="muted">' + pr + '</span><span id="ty"></span><span class="cursor"></span></div>';
		var ty = $('#ty'), i = 0;
		(function type() {
			if (i <= typed.length) { ty.textContent = typed.slice(0, i++); after(140, type); }
			else after(900, ok);
		})();
		function ok() {
			term.innerHTML = '<div><span class="muted">' + pr + '</span>portfolio</div>';
			after(700, function () {
				var d = document.createElement('div'); d.innerHTML = '<span class="muted">ready.</span>'; term.appendChild(d);
				after(1100, b3);
			});
		}
	}

	function decode(done) {
		var target = (bigtitle.getAttribute('data-text') || 'RAVEENTHIRAN').toUpperCase();
		var pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/#*';
		var sp = [];
		bigtitle.innerHTML = '';
		for (var k = 0; k < target.length; k++) {
			var s = document.createElement('span'); s.className = 'ch';
			s.textContent = target[k] === ' ' ? ' ' : pool[(Math.random() * pool.length) | 0];
			bigtitle.appendChild(s); sp.push(s);
		}
		if (reduce) { sp.forEach(function (s, k) { s.textContent = target[k] === ' ' ? ' ' : target[k]; s.classList.add('lock'); }); done && done(); return; }
		var start = performance.now(), settle = 560, per = 180;
		(function run(now) {
			var t = now - start, all = true;
			for (var k = 0; k < target.length; k++) {
				var la = settle + k * per;
				if (t >= la) { if (!sp[k].classList.contains('lock')) { sp[k].textContent = target[k] === ' ' ? ' ' : target[k]; sp[k].classList.add('lock'); } }
				else { all = false; sp[k].textContent = pool[(Math.random() * pool.length) | 0]; }
			}
			if (!all) requestAnimationFrame(run); else done && done();
		})(start);
	}

	function b3() {
		if (term) term.classList.remove('show');
		after(320, function () {
			titlewrap.classList.add('show');
			decode(function () { after(1100, function () { enterbig.classList.add('show'); state = 'ready'; arm(); }); });
		});
	}

	var evs = ['wheel', 'touchstart', 'keydown', 'click'];
	function arm() { evs.forEach(function (e) { window.addEventListener(e, go, { passive: true }); }); }
	function go(e) {
		if (state !== 'ready') return;
		if (e.type === 'keydown' && ['Enter', ' ', 'ArrowDown', 'PageDown'].indexOf(e.key) === -1) return;
		state = 'in';
		evs.forEach(function (ev) { window.removeEventListener(ev, go); });
		home.classList.add('in'); intro.classList.add('gone');
		after(1200, function () {
			intro.style.display = 'none';
			document.body.classList.add('entered');
			dockNav();
			var shown = false;
			function show() { if (!shown) { shown = true; dock.classList.add('show'); } }
			home.addEventListener('scroll', function () { if (home.scrollTop > 40) show(); });
			after(1600, show);
		});
	}

	b1();
})();

/* ── price engine (enquire) — runs on any page with [data-price-engine] ── */
(function () {
	'use strict';
	var form = document.querySelector('[data-price-engine]');
	if (!form) return;
	var cur = form.getAttribute('data-currency') || '€';
	function money(n) { return cur + Math.round(n).toLocaleString(); }
	function compute() {
		var t = form.querySelector('input[name="project_type"]:checked');
		var total = t ? (parseFloat(t.getAttribute('data-base')) || 0) : 0;
		var parts = [];
		if (t) { parts.push(t.value); }
		[].forEach.call(form.querySelectorAll('input[name="addons[]"]:checked'), function (x) {
			total += parseFloat(x.getAttribute('data-price')) || 0; parts.push(x.value);
		});
		var lic = form.querySelector('input[name="license"]');
		if (lic && lic.checked) { total += parseFloat(lic.getAttribute('data-price')) || 0; parts.push('Commercial license'); }
		var km = form.querySelector('input[name="travel_km"]');
		if (km) { var k = parseFloat(km.value) || 0; if (k > 0) { total += k * (parseFloat(km.getAttribute('data-per-km')) || 0); parts.push(Math.round(k) + ' km'); } }
		var out = form.querySelector('[data-estimate]'); if (out) out.textContent = money(total);
		var hid = form.querySelector('[data-estimate-input]'); if (hid) hid.value = money(total);
		var bd = form.querySelector('[data-breakdown]'); if (bd) bd.value = parts.join(' · ');
		var sl = form.querySelector('[data-slug]'); if (sl && t) sl.value = t.getAttribute('data-slug') || '';
	}
	form.addEventListener('change', compute);
	form.addEventListener('input', function (e) { if (e.target.name === 'travel_km') compute(); });
	compute();
})();

/* ── cookie consent — minimal, remembered in localStorage ── */
(function () {
	'use strict';
	var bar = document.getElementById('cookie-bar');
	if (!bar) return;
	var KEY = 'still_cookie';
	try { if (localStorage.getItem(KEY)) return; } catch (e) {}
	setTimeout(function () { bar.classList.add('show'); }, 800);
	bar.addEventListener('click', function (e) {
		var b = e.target.closest('button');
		if (!b) return;
		try { localStorage.setItem(KEY, b.classList.contains('accept') ? 'accepted' : 'declined'); } catch (e) {}
		bar.classList.remove('show');
	});
})();

/* ── horizontal subpages: vertical scroll drives sideways movement ── */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion:reduce)').matches;
	var hxs = [].slice.call(document.querySelectorAll('.hx'));
	if (!hxs.length) return;
	function active() { return window.innerWidth > 820 && !reduce; }
	function layout() {
		hxs.forEach(function (hx) {
			var track = hx.querySelector('.hx__track');
			if (!track) return;
			if (!active()) { hx.style.height = ''; track.style.transform = ''; return; }
			var dist = Math.max(0, track.scrollWidth - window.innerWidth);
			hx.style.height = (dist + window.innerHeight) + 'px';
		});
		render();
	}
	function render() {
		if (!active()) return;
		hxs.forEach(function (hx) {
			var track = hx.querySelector('.hx__track');
			if (!track) return;
			var dist = Math.max(0, track.scrollWidth - window.innerWidth);
			var top = -hx.getBoundingClientRect().top;
			var p = Math.min(Math.max(top, 0), dist);
			track.style.transform = 'translate3d(' + (-p) + 'px,0,0)';
		});
	}
	var ticking = false;
	window.addEventListener('scroll', function () {
		if (ticking) return; ticking = true;
		requestAnimationFrame(function () { render(); ticking = false; });
	}, { passive: true });
	var rt;
	window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(layout, 150); });
	window.addEventListener('load', layout);
	layout();
})();
