/**
 * M1O Transmission · v3.0 · motion layer
 * GSAP + ScrollTrigger (enqueued as deps). Degrades to a static,
 * fully readable page when GSAP is absent or reduced-motion is set.
 */
(function () {
	'use strict';

	var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var body = document.body;
	var loader = document.getElementById('m1o-loader');

	function showAll() {
		if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
		document.querySelectorAll('.rv').forEach(function (e) {
			e.style.opacity = 1;
			e.style.transform = 'none';
		});
	}

	if (!window.gsap || reduced || !body.classList.contains('m1o-motion')) {
		showAll();
		return;
	}

	gsap.registerPlugin(ScrollTrigger);

	/* split text nodes into chars; keep child elements whole */
	function splitChars(el) {
		var out = [];
		Array.prototype.slice.call(el.childNodes).forEach(function (n) {
			if (n.nodeType === 3) {
				var frag = document.createDocumentFragment();
				n.textContent.split('').forEach(function (c) {
					var s = document.createElement('span');
					s.className = 'ch';
					s.textContent = c;
					frag.appendChild(s);
					out.push(s);
				});
				el.replaceChild(frag, n);
			} else if (n.nodeType === 1) {
				n.classList.add('ch');
				out.push(n);
			}
		});
		return out;
	}

	/* decode/scramble — the transmission effect */
	var GLYPHS = 'M1O#/\\+01·×';
	function scramble(el, dur) {
		var final = el.textContent;
		var frames = Math.max(10, Math.round((dur || 0.7) * 30));
		var i = 0;
		var iv = setInterval(function () {
			i++;
			var reveal = Math.floor(final.length * (i / frames));
			var s = '';
			for (var j = 0; j < final.length; j++) {
				if (j < reveal || final[j] === ' ') s += final[j];
				else s += GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
			}
			el.textContent = s;
			if (i >= frames) { el.textContent = final; clearInterval(iv); }
		}, 33);
	}

	/* ───── preloader gate + hero cascade (front page only) ───── */
	var wm = document.getElementById('m1o-wm');
	if (loader && wm) {
		body.classList.add('m1o-locked');
		var chars = [];
		wm.querySelectorAll('.l1, .l2').forEach(function (l) { chars = chars.concat(splitChars(l)); });
		gsap.set(chars, { yPercent: 118 });

		var n = { v: 0 };
		var lnum = document.getElementById('m1o-lnum');
		var lscr = loader.querySelector('[data-scramble]');
		if (lscr) scramble(lscr, 1.2);

		gsap.timeline()
			.to(n, {
				v: 100, duration: 1.8, ease: 'power2.inOut',
				onUpdate: function () { lnum.textContent = String(Math.round(n.v)).padStart(2, '0'); }
			}, 0)
			.to('#m1o-lbar', { width: '100%', duration: 1.8, ease: 'power2.inOut' }, 0)
			.to(loader, { yPercent: -100, duration: 0.85, ease: 'power4.inOut' }, 1.95)
			.add(function () { body.classList.remove('m1o-locked'); }, 2.2)
			.to(chars, { yPercent: 0, duration: 1.0, ease: 'power4.out', stagger: 0.035 }, 2.15)
			.to('#m1o-eb', {
				opacity: 1, y: 0, duration: 0.5,
				onStart: function () {
					document.querySelectorAll('.sysbar [data-scramble], #m1o-eb').forEach(function (el) { scramble(el, 0.8); });
				}
			}, 2.4)
			.to('.hero .rv', { opacity: 1, y: 0, duration: 0.7, stagger: 0.12 }, 2.6)
			.add(function () { if (loader.parentNode) loader.parentNode.removeChild(loader); }, 3.1);

		/* wordmark shimmer */
		gsap.to('#m1o-l2', {
			textShadow: '0 0 48px rgba(242,202,80,0.45)', duration: 1.4, ease: 'sine.inOut',
			yoyo: true, repeat: -1, repeatDelay: 3.5, delay: 4
		});
	} else {
		/* inner pages: no gate, reveal chrome immediately */
		document.querySelectorAll('.hero .rv, .sysbar .rv').forEach(function (e) {
			e.style.opacity = 1; e.style.transform = 'none';
		});
	}

	/* scroll reveals + decode on arrival */
	document.querySelectorAll('.index .rv, .cta .rv, .colophon .rv, .page-shell .rv').forEach(function (el) {
		gsap.to(el, {
			opacity: 1, y: 0, duration: 0.8, ease: 'power3.out',
			scrollTrigger: {
				trigger: el, start: 'top 88%',
				onEnter: function () {
					el.querySelectorAll('[data-scramble]').forEach(function (t) { scramble(t, 0.7); });
					if (el.hasAttribute('data-scramble')) scramble(el, 0.7);
				}
			}
		});
	});

	/* scroll-velocity skew on index rows */
	if (document.querySelector('.sk')) {
		var proxy = { skew: 0 };
		var skewSetter = gsap.quickSetter('.sk', 'skewY', 'deg');
		var clampSkew = gsap.utils.clamp(-2.4, 2.4);
		ScrollTrigger.create({
			onUpdate: function (self) {
				var s = clampSkew(self.getVelocity() / -420);
				if (Math.abs(s) > Math.abs(proxy.skew)) {
					proxy.skew = s;
					gsap.to(proxy, {
						skew: 0, duration: 0.7, ease: 'power3', overwrite: true,
						onUpdate: function () { skewSetter(proxy.skew); }
					});
				}
			}
		});
	}

	/* magnetic chips */
	document.querySelectorAll('.mag').forEach(function (el) {
		var qx = gsap.quickTo(el, 'x', { duration: 0.35, ease: 'power3' });
		var qy = gsap.quickTo(el, 'y', { duration: 0.35, ease: 'power3' });
		el.addEventListener('mousemove', function (e) {
			var r = el.getBoundingClientRect();
			qx((e.clientX - r.left - r.width / 2) * 0.28);
			qy((e.clientY - r.top - r.height / 2) * 0.28);
		});
		el.addEventListener('mouseleave', function () { qx(0); qy(0); });
	});

	/* cursor dot + glow + sparks */
	var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	var dot = document.getElementById('m1o-dot');
	var glowEl = document.getElementById('m1o-glow');
	if (fine && dot && glowEl) {
		gsap.set([dot, glowEl], { opacity: 1 });
		var dx = gsap.quickTo(dot, 'left', { duration: 0.08 });
		var dy = gsap.quickTo(dot, 'top', { duration: 0.08 });
		var gx = gsap.quickTo(glowEl, 'left', { duration: 0.5, ease: 'power3' });
		var gy = gsap.quickTo(glowEl, 'top', { duration: 0.5, ease: 'power3' });
		var lastSpark = 0;
		window.addEventListener('mousemove', function (e) {
			dx(e.clientX); dy(e.clientY); gx(e.clientX); gy(e.clientY);
			var now = performance.now();
			if (now - lastSpark > 90) {
				lastSpark = now;
				var sp = document.createElement('span');
				sp.className = 'spark';
				sp.style.left = e.clientX + 'px';
				sp.style.top = e.clientY + 'px';
				document.body.appendChild(sp);
				gsap.to(sp, {
					x: (Math.random() - 0.5) * 40, y: (Math.random() - 0.5) * 40 + 16,
					opacity: 0, scale: 0.2, duration: 0.7, ease: 'power2.out',
					onComplete: function () { sp.remove(); }
				});
			}
		});
		document.querySelectorAll('a, button').forEach(function (el) {
			el.addEventListener('mouseenter', function () { dot.classList.add('hot'); });
			el.addEventListener('mouseleave', function () { dot.classList.remove('hot'); });
		});
	}

	/* gold particle field in the hero */
	var cv = document.getElementById('m1o-field');
	if (cv) {
		var ctx = cv.getContext('2d');
		var W, H, parts = [];
		function size() {
			W = cv.width = cv.offsetWidth * devicePixelRatio;
			H = cv.height = cv.offsetHeight * devicePixelRatio;
		}
		size();
		window.addEventListener('resize', size);
		for (var i = 0; i < 70; i++) {
			parts.push({
				x: Math.random(), y: Math.random(),
				r: Math.random() * 1.6 + 0.4,
				vy: Math.random() * 0.0007 + 0.0002,
				vx: (Math.random() - 0.5) * 0.0003,
				tw: Math.random() * Math.PI * 2
			});
		}
		var running = true;
		document.addEventListener('visibilitychange', function () { running = !document.hidden; });
		(function draw() {
			requestAnimationFrame(draw);
			if (!running) return;
			ctx.clearRect(0, 0, W, H);
			parts.forEach(function (p) {
				p.y -= p.vy; p.x += p.vx; p.tw += 0.03;
				if (p.y < -0.02) { p.y = 1.02; p.x = Math.random(); }
				var a = 0.25 + Math.sin(p.tw) * 0.2;
				ctx.beginPath();
				ctx.arc(p.x * W, p.y * H, p.r * devicePixelRatio, 0, 6.283);
				ctx.fillStyle = 'rgba(242,202,80,' + a.toFixed(3) + ')';
				ctx.fill();
			});
		})();
	}
})();
