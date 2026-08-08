/* Still — theme interactions: cinematic intro (front page), dock nav,
   3D card tilt, scroll reveals. Vanilla, no dependencies. */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion:reduce)').matches;
	var coarse = window.matchMedia && matchMedia('(pointer:coarse)').matches;
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return [].slice.call((c || document).querySelectorAll(s)); };
	function after(ms, fn) { setTimeout(fn, reduce ? Math.min(ms, 120) : ms); }

	var intro = $('#intro');
	var home = $('#home');
	var dock = $('#dock');
	var panels = $$('.panel');

	/* ── scroll reveals (works on any page) ── */
	(function reveals() {
		var els = $$('[data-rise]');
		if (!els.length) return;
		if (!('IntersectionObserver' in window) || reduce) { els.forEach(function (e) { e.classList.add('in'); }); return; }
		var root = (home && document.body.classList.contains('intro')) ? home : null;
		var io = new IntersectionObserver(function (es) {
			es.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
		}, { root: root, threshold: 0.15 });
		els.forEach(function (e) { io.observe(e); });
	})();

	/* ── 3D tilt on cards (skip reduced-motion / touch) ── */
	(function tilt() {
		if (reduce || coarse) return;
		$$('.tilt-wrap').forEach(function (w) {
			var c = w.querySelector('.tilt-el');
			if (!c) return;
			w.addEventListener('mousemove', function (e) {
				var r = w.getBoundingClientRect();
				var px = (e.clientX - r.left) / r.width - 0.5, py = (e.clientY - r.top) / r.height - 0.5;
				c.style.transform = 'rotateY(' + (px * 8).toFixed(2) + 'deg) rotateX(' + (-py * 8).toFixed(2) + 'deg) translateZ(6px)';
			});
			w.addEventListener('mouseleave', function () { c.style.transform = ''; });
		});
	})();

	/* ── dock (global nav) ── */
	function dockScrollNav() {
		if (!dock) return;
		// On the home, dock items smooth-scroll to the matching teaser panel.
		dock.querySelectorAll('a[data-key]').forEach(function (a) {
			a.addEventListener('click', function (e) {
				var target = document.getElementById('panel-' + a.getAttribute('data-key'));
				if (target) { e.preventDefault(); target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' }); }
			});
		});
		// active state follows the visible panel
		if ('IntersectionObserver' in window && panels.length) {
			var io = new IntersectionObserver(function (es) {
				es.forEach(function (en) {
					if (en.isIntersecting) {
						var key = en.target.getAttribute('data-key');
						dock.querySelectorAll('a[data-key]').forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-key') === key); });
					}
				});
			}, { root: (document.body.classList.contains('intro') ? home : null), threshold: 0.55 });
			panels.forEach(function (p) { io.observe(p); });
		}
	}

	/* ── if not the cinematic home, just show the dock and wire nav ── */
	if (!intro) {
		if (home) dockScrollNav();   // static home fallback (no intro)
		return;
	}

	/* ══ CINEMATIC INTRO (front page) ══ */
	var boot = $('#boot'), fill = $('#fill'), pct = $('#pct'),
		term = $('#term'), titlewrap = $('#titlewrap'),
		bigtitle = $('#bigtitle'), enterbig = $('#enterbig');
	var state = 'boot';

	function boot1() {
		requestAnimationFrame(function () { if (fill) fill.style.width = '100%'; });
		var t0 = performance.now(), dur = reduce ? 150 : 2800;
		(function tick() {
			var p = Math.min(1, (performance.now() - t0) / dur);
			if (pct) pct.textContent = Math.round(p * 100) + '%';
			if (p < 1) requestAnimationFrame(tick); else after(520, boot2);
		})();
	}

	function boot2() {
		if (boot) boot.classList.add('hide');
		if (term) term.classList.add('show');
		var prompt = 'visitor@' + (location.hostname || 'raveenthiran') + ':~$ ';
		var typed = 'portfolio';
		var logs = [
			'<span class="muted">› resolving archive…</span>',
			'<span class="muted">› compositing interface…</span>',
			'<span class="ok">✓ ready</span>'
		];
		term.innerHTML = '<div><span class="muted">' + prompt + '</span><span id="typed"></span><span class="cursor"></span></div>';
		var tEl = $('#typed'), i = 0;
		(function type() {
			if (i <= typed.length) { tEl.textContent = typed.slice(0, i++); after(120, type); }
			else after(620, runLogs);
		})();
		function runLogs() {
			term.innerHTML = '<div><span class="muted">' + prompt + '</span>portfolio</div>';
			var j = 0;
			(function next() {
				if (j < logs.length) { var d = document.createElement('div'); d.innerHTML = logs[j++]; term.appendChild(d); after(500, next); }
				else after(850, boot3);
			})();
		}
	}

	function epicTitle(done) {
		var target = (bigtitle.getAttribute('data-text') || 'RAVEENTHIRAN').toUpperCase();
		var pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#%&/<>*+';
		var spans = [];
		bigtitle.innerHTML = '';
		for (var k = 0; k < target.length; k++) {
			var s = document.createElement('span');
			s.className = 'ch';
			s.textContent = target[k] === ' ' ? ' ' : pool[(Math.random() * pool.length) | 0];
			bigtitle.appendChild(s); spans.push(s);
		}
		if (reduce) {
			spans.forEach(function (s, k) { s.textContent = target[k] === ' ' ? ' ' : target[k]; s.classList.add('lock'); });
			titlewrap.classList.add('decoded'); done && done(); return;
		}
		var start = performance.now(), settle = 420, per = 140;
		(function run(now) {
			var t = now - start, allDone = true;
			for (var k = 0; k < target.length; k++) {
				var lockAt = settle + k * per;
				if (t >= lockAt) {
					if (!spans[k].classList.contains('lock')) { spans[k].textContent = target[k] === ' ' ? ' ' : target[k]; spans[k].classList.add('lock'); }
				} else { allDone = false; spans[k].textContent = pool[(Math.random() * pool.length) | 0]; }
			}
			if (!allDone) requestAnimationFrame(run);
			else { titlewrap.classList.add('decoded'); done && done(); }
		})(start);
	}

	function boot3() {
		if (term) term.classList.remove('show');
		after(240, function () {
			titlewrap.classList.add('show');
			epicTitle(function () {
				after(520, function () { enterbig.classList.add('show'); state = 'ready'; arm(); });
			});
		});
	}

	var evs = ['wheel', 'touchstart', 'keydown', 'click'];
	function arm() { evs.forEach(function (ev) { window.addEventListener(ev, tryEnter, { passive: true }); }); }
	function tryEnter(e) {
		if (state !== 'ready') return;
		if (e.type === 'keydown' && ['Enter', ' ', 'ArrowDown', 'PageDown'].indexOf(e.key) === -1) return;
		state = 'entering';
		evs.forEach(function (ev) { window.removeEventListener(ev, tryEnter); });
		home.classList.add('in'); intro.classList.add('gone');
		after(1000, function () {
			intro.style.display = 'none';
			document.body.classList.add('entered');
			state = 'home';
			dockScrollNav();
			armDockReveal();
		});
	}

	function armDockReveal() {
		if (!dock) return;
		var shown = false;
		function show() { if (!shown) { shown = true; dock.classList.add('show'); } }
		home.addEventListener('scroll', function () { if (home.scrollTop > 40) show(); });
		after(1400, show); // discoverable even without scroll
	}

	/* ══ VIDEO-SEQUENCE INTRO ══ — plays the intro video, reveals the
	   "scroll right" cue, then the same rightward slide into home. Falls back
	   to the animated sequence if the video is missing or can't play. */
	function videoIntro() {
		var v = document.getElementById('introvid');
		function ready() { if (state === 'boot') { state = 'ready'; enterbig.classList.add('show'); arm(); } }
		if (!v) { ready(); return; }
		var p = v.play && v.play();
		if (p && p.catch) { p.catch(function () {}); }
		v.addEventListener('ended', ready);
		v.addEventListener('error', ready);
		// let the viewer scroll on past the video at any point…
		after(1400, ready);
		// …and a hard safety net in case 'ended' never fires
		after(reduce ? 300 : 12000, ready);
	}

	// GO
	document.body.classList.add('js-intro');
	if (intro.getAttribute('data-mode') === 'video') { videoIntro(); }
	else { boot1(); }
})();

/* ── price engine (Enquire) — isolated + guarded: only runs if the form is on
   the page, so it can never affect the intro / home / work. Computes a live
   estimate and writes it (plus a breakdown) into hidden fields for the email. */
(function () {
	'use strict';
	var form = document.querySelector('[data-price-engine]');
	if (!form) return;
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion:reduce)').matches;
	var cur = form.getAttribute('data-currency') || '€';
	// Every estimate readout on the page (the inline one + the sticky sidebar mirror).
	var outs = [].slice.call(document.querySelectorAll('[data-estimate]'));
	function money(n) { return cur + Math.round(n).toLocaleString(); }
	function num(el, attr) { return el ? (parseFloat(el.getAttribute(attr)) || 0) : 0; }

	// Count-up: tween the displayed number to a new total (skipped for reduced-motion).
	var shown = 0, raf = null;
	function paint(v) { var t = money(v); outs.forEach(function (o) { o.textContent = t; }); }
	function animateTo(total) {
		if (reduce || shown === total) { shown = total; paint(total); return; }
		if (raf) cancelAnimationFrame(raf);
		var from = shown, delta = total - from, t0 = performance.now(), dur = 420;
		(function step(now) {
			var p = Math.min(1, (now - t0) / dur);
			var e = 1 - Math.pow(1 - p, 3); // easeOutCubic
			paint(from + delta * e);
			if (p < 1) raf = requestAnimationFrame(step); else { shown = total; paint(total); }
		})(t0);
	}

	function compute() {
		var parts = [], total = 0;
		var type = form.querySelector('input[name="project_type"]:checked');
		if (type) { total += num(type, 'data-base'); parts.push(type.value); }
		[].forEach.call(form.querySelectorAll('input[name="addons[]"]:checked'), function (x) {
			total += num(x, 'data-price'); parts.push(x.value);
		});
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
})();

/* ── horizontal subpages — wheel / trackpad drives the filmstrip sideways.
   Isolated + guarded: only runs where an .hx__track exists. Tall panels
   (prose / form / faq) consume vertical intent until they bottom out, then
   hand back to the sideways scroll. Touch swipe is native; reduced-motion
   and phones-in-reduced-motion fall back to the vertical stack (see CSS). ── */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion:reduce)').matches;
	var tracks = [].slice.call(document.querySelectorAll('.hx__track'));
	if (!tracks.length) return;
	function active() { return window.innerWidth > 820 && !reduce; }

	tracks.forEach(function (track) {
		track.addEventListener('wheel', function (e) {
			if (!active()) return;              // touch/reduced-motion: native behaviour
			if (e.ctrlKey) return;              // pinch-zoom
			// let a tall inner panel take the vertical scroll until it reaches an edge
			var panel = e.target.closest && e.target.closest('.hx-panel--wide, .hx-panel--form, .hx-panel--faq');
			if (panel && panel.scrollHeight > panel.clientHeight + 1) {
				var atTop = panel.scrollTop <= 0;
				var atBottom = panel.scrollTop + panel.clientHeight >= panel.scrollHeight - 1;
				if ((e.deltaY < 0 && !atTop) || (e.deltaY > 0 && !atBottom)) return;
			}
			var d = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
			if (!d) return;
			track.scrollLeft += d;
			e.preventDefault();
		}, { passive: false });

		// keyboard paging when the filmstrip has focus
		track.setAttribute('tabindex', '-1');
		track.addEventListener('keydown', function (e) {
			if (!active()) return;
			var step = window.innerWidth;
			if (e.key === 'ArrowRight' || e.key === 'PageDown') { track.scrollBy({ left: step, behavior: 'smooth' }); e.preventDefault(); }
			else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { track.scrollBy({ left: -step, behavior: 'smooth' }); e.preventDefault(); }
		});
	});
})();
