/* =============================================================================
   Raveenthiran v3 — motion engine (vanilla, no deps).
   Preloader, intro stagger, scroll reveals, parallax, velocity skew,
   custom cursor, letter reveals, lightbox, hover-peek, drag rails,
   card choreography, live clock, grain + vignette atmosphere, cookie notice.
   initFX() returns a cleanup fn; boot re-runs it on every Astro page-load.
   ============================================================================= */
export function initFX(opts = {}) {
	const cleanups = [];
	const ease = 'cubic-bezier(.65,.05,.2,1)';
	const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
	const fine = matchMedia('(pointer: fine)').matches;

	/* grain + vignette atmosphere */
	const atmo = document.createElement('div');
	atmo.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9998;mix-blend-mode:overlay;opacity:.5;' +
		"background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22140%22 height=%22140%22><filter id=%22n%22><feTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%222%22/><feColorMatrix type=%22saturate%22 values=%220%22/></filter><rect width=%22140%22 height=%22140%22 filter=%22url(%23n)%22 opacity=%22.4%22/></svg>')";
	const vig = document.createElement('div');
	vig.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9997;background:radial-gradient(120% 90% at 50% 10%, rgba(0,0,0,0) 55%, rgba(0,0,0,.42) 100%)';
	document.body.append(vig, atmo);
	cleanups.push(() => { atmo.remove(); vig.remove(); });

	/* preloader (home, first visit per session) */
	let introDelay = 120;
	if (opts.preloader && !reduce) {
		introDelay = 1750;
		const pre = document.createElement('div');
		pre.style.cssText = 'position:fixed;inset:0;z-index:10000;background:#0c0b0a;display:flex;align-items:center;justify-content:center;transition:transform .8s ' + ease;
		const word = document.createElement('div');
		word.style.cssText = "font-family:'Anton',sans-serif;font-size:min(10vw,110px);letter-spacing:.02em;color:#efece6;opacity:0;transform:translateY(30px);transition:opacity .5s,transform .6s " + ease;
		word.textContent = opts.preloader;
		const pct = document.createElement('div');
		pct.style.cssText = "position:fixed;right:28px;bottom:22px;font-family:'Archivo',sans-serif;font-size:12px;letter-spacing:.24em;color:#8a847a";
		pre.append(word, pct);
		document.body.append(pre);
		requestAnimationFrame(() => { word.style.opacity = '1'; word.style.transform = 'none'; });
		const t0 = performance.now(), dur = 1200;
		const count = now => {
			const p = Math.min(1, (now - t0) / dur);
			pct.textContent = String(Math.round((1 - Math.pow(1 - p, 3)) * 100)).padStart(3, '0') + ' %';
			if (p < 1) requestAnimationFrame(count);
			else { pre.style.transform = 'translateY(-101%)'; setTimeout(() => pre.remove(), 900); }
		};
		requestAnimationFrame(count);
		cleanups.push(() => pre.remove());
	}

	/* intro stagger */
	document.querySelectorAll('[data-intro]').forEach((el, i) => {
		if (reduce) return;
		el.style.opacity = '0'; el.style.transform = 'translateY(52px)';
		setTimeout(() => {
			el.style.transition = 'opacity .9s ' + ease + ', transform 1s ' + ease;
			el.style.opacity = '1'; el.style.transform = 'none';
		}, introDelay + i * 130);
	});

	/* scroll reveals */
	const io = new IntersectionObserver(es => es.forEach(e => {
		if (!e.isIntersecting) return;
		const el = e.target;
		el.style.transition = 'opacity .9s ease, transform 1.1s ' + ease;
		el.style.opacity = '1'; el.style.transform = 'none';
		io.unobserve(el);
	}), { threshold: 0.12 });
	document.querySelectorAll('[data-reveal]').forEach(el => {
		if (reduce || el.getBoundingClientRect().top < innerHeight * 0.92) return;
		el.style.opacity = '0'; el.style.transform = 'translateY(64px)';
		io.observe(el);
	});
	cleanups.push(() => io.disconnect());

	/* parallax + velocity skew source */
	const pEls = [...document.querySelectorAll('[data-parallax]')];
	const sEls = [...document.querySelectorAll('[data-skew]')];
	let lastY = scrollY, vel = 0;
	const onScroll = () => {
		vel += (scrollY - lastY); lastY = scrollY;
		if (reduce) return;
		pEls.forEach(el => {
			const sp = parseFloat(el.dataset.parallax) || 0.1;
			const host = el.parentElement.getBoundingClientRect();
			const d = (host.top + host.height / 2 - innerHeight / 2) * sp;
			el.style.transform = 'translateY(' + d.toFixed(1) + 'px) scale(1.14)';
		});
	};
	addEventListener('scroll', onScroll, { passive: true }); onScroll();
	cleanups.push(() => removeEventListener('scroll', onScroll));

	/* cursor: keep the NATIVE cursor visible, add a trailing ring accent
	   (fine pointer only). The native pointer is never hidden. */
	let raf = 0;
	if (fine && !reduce) {
		const ring = document.createElement('div');
		ring.style.cssText = 'position:fixed;left:0;top:0;width:34px;height:34px;border-radius:50%;border:1.5px solid #efece6;mix-blend-mode:difference;pointer-events:none;z-index:10001;opacity:0;transform:translate(-100px,-100px);transition:width .25s,height .25s,opacity .3s';
		document.body.append(ring);
		let mx = -100, my = -100, rx = -100, ry = -100, seen = false;
		const onMove = e => { mx = e.clientX; my = e.clientY; if (!seen) { seen = true; ring.style.opacity = '1'; } };
		const onOver = e => {
			const hot = e.target.closest && e.target.closest('a,button,[data-zoom]');
			ring.style.width = hot ? '58px' : '34px'; ring.style.height = hot ? '58px' : '34px';
		};
		const onLeave = () => { ring.style.opacity = '0'; };
		const tick = () => {
			rx += (mx - rx) * 0.18; ry += (my - ry) * 0.18;
			const s = ring.offsetWidth / 2;
			ring.style.transform = 'translate(' + (rx - s) + 'px,' + (ry - s) + 'px)';
			if (peekOn) peek.style.transform = 'translate(' + (mx + 30) + 'px,' + (my - peek.offsetHeight / 2) + 'px) rotate(3deg)';
			vel *= 0.88;
			const sk = Math.max(-7, Math.min(7, vel * 0.10));
			sEls.forEach(el => { el.style.transform = 'skewX(' + sk.toFixed(2) + 'deg)'; });
			raf = requestAnimationFrame(tick);
		};
		addEventListener('mousemove', onMove, { passive: true });
		addEventListener('mouseover', onOver, { passive: true });
		document.addEventListener('mouseleave', onLeave);
		cleanups.push(() => { removeEventListener('mousemove', onMove); removeEventListener('mouseover', onOver); document.removeEventListener('mouseleave', onLeave); cancelAnimationFrame(raf); ring.remove(); });
		tick();
	} else {
		/* still decay velocity skew without a cursor */
		const tick = () => { vel *= 0.88; const sk = Math.max(-7, Math.min(7, vel * 0.10)); if (!reduce) sEls.forEach(el => { el.style.transform = 'skewX(' + sk.toFixed(2) + 'deg)'; }); raf = requestAnimationFrame(tick); };
		cleanups.push(() => cancelAnimationFrame(raf));
		tick();
	}

	/* scroll-progress hairline */
	const prog = document.createElement('div');
	prog.style.cssText = 'position:fixed;left:0;top:0;height:2px;width:0;background:#efece6;z-index:10002;transition:width .15s linear';
	document.body.append(prog);
	const onProg = () => {
		const max = document.documentElement.scrollHeight - innerHeight;
		prog.style.width = (max > 0 ? (scrollY / max) * 100 : 0) + 'vw';
	};
	addEventListener('scroll', onProg, { passive: true }); onProg();
	cleanups.push(() => { removeEventListener('scroll', onProg); prog.remove(); });

	/* live Vienna clock */
	const clocks = [...document.querySelectorAll('[data-clock]')];
	if (clocks.length) {
		const setT = () => {
			const t = new Date().toLocaleTimeString('de-AT', { timeZone: 'Europe/Vienna', hour12: false });
			clocks.forEach(el => { el.textContent = t + ' CET — VIENNA'; });
		};
		setT(); const ct = setInterval(setT, 1000);
		cleanups.push(() => clearInterval(ct));
	}

	/* letter-stagger heading reveals */
	const lio = new IntersectionObserver(es => es.forEach(e => {
		if (!e.isIntersecting) return;
		[...e.target.querySelectorAll('span[data-l]')].forEach((sp, i) => {
			setTimeout(() => { sp.style.transform = 'none'; sp.style.opacity = '1'; }, i * 28);
		});
		lio.unobserve(e.target);
	}), { threshold: 0.4 });
	document.querySelectorAll('[data-letters]').forEach(el => {
		if (el.dataset.lettersDone) return;
		el.dataset.lettersDone = '1';
		if (reduce) return;
		const text = el.textContent;
		el.textContent = '';
		[...text].forEach(ch => {
			const outer = document.createElement('span');
			outer.style.cssText = 'display:inline-block;overflow:hidden;vertical-align:bottom';
			const inner = document.createElement('span');
			inner.setAttribute('data-l', '');
			inner.style.cssText = 'display:inline-block;transform:translateY(110%);opacity:0;transition:transform .7s ' + ease + ', opacity .5s';
			inner.textContent = ch === ' ' ? ' ' : ch;
			outer.append(inner); el.append(outer);
		});
		lio.observe(el);
	});
	cleanups.push(() => lio.disconnect());

	/* lightbox (click any [data-zoom]) */
	const onZoom = e => {
		const img = e.target.closest && e.target.closest('[data-zoom]');
		if (!img) return;
		e.preventDefault(); e.stopPropagation();
		const box = document.createElement('div');
		box.style.cssText = 'position:fixed;inset:0;z-index:10005;background:rgba(12,11,10,.94);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s';
		const big = document.createElement('img');
		big.src = img.currentSrc || img.src;
		big.style.cssText = 'max-width:92vw;max-height:88vh;object-fit:contain;transform:scale(.95);transition:transform .5s ' + ease;
		const cap = document.createElement('div');
		cap.style.cssText = "position:fixed;left:28px;bottom:22px;font-family:'Archivo',sans-serif;font-size:11px;letter-spacing:.2em;color:#efece6";
		cap.textContent = (img.alt || '').toUpperCase();
		const x = document.createElement('div');
		x.style.cssText = "position:fixed;right:28px;top:22px;font-family:'Archivo',sans-serif;font-size:11px;letter-spacing:.2em;color:#8a847a";
		x.textContent = 'CLICK ANYWHERE TO CLOSE ✕';
		box.append(big, cap, x);
		document.body.append(box);
		requestAnimationFrame(() => { box.style.opacity = '1'; big.style.transform = 'scale(1)'; });
		const close = () => { box.style.opacity = '0'; setTimeout(() => box.remove(), 350); removeEventListener('keydown', onKey); };
		const onKey = ev => { if (ev.key === 'Escape') close(); };
		box.addEventListener('click', close);
		addEventListener('keydown', onKey);
	};
	document.addEventListener('click', onZoom, true);
	cleanups.push(() => document.removeEventListener('click', onZoom, true));

	/* EU cookie notice (dismissal remembered) */
	if (!localStorage.getItem('rvn-cookie-ok')) {
		const cb = document.createElement('div');
		cb.style.cssText = "position:fixed;left:28px;right:28px;bottom:24px;z-index:10004;background:#1a1815;border:1px solid #3a372f;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:18px 24px;font-family:'Archivo',sans-serif;transform:translateY(140%);transition:transform .7s " + ease;
		const txt = document.createElement('div');
		txt.style.cssText = 'font-size:11px;letter-spacing:.06em;line-height:1.6;color:#b5aea1;max-width:640px';
		txt.innerHTML = '<span style="letter-spacing:.2em;color:#efece6;font-weight:600">COOKIES.</span> This site uses only technically necessary cookies. Analytics and embeds load after your consent — see <a href="/legal/datenschutz/" style="text-decoration:underline">Datenschutz</a>.';
		const row = document.createElement('div');
		row.style.cssText = 'display:flex;gap:12px;flex:0 0 auto';
		const mk = (label, primary) => {
			const b = document.createElement('button');
			b.textContent = label;
			b.style.cssText = "font-family:'Archivo',sans-serif;font-size:10px;letter-spacing:.18em;font-weight:700;padding:12px 20px;cursor:pointer;" + (primary ? 'background:#efece6;color:#121110;border:none' : 'background:none;color:#8a847a;border:1px solid #3a372f');
			b.addEventListener('click', () => { localStorage.setItem('rvn-cookie-ok', primary ? 'all' : 'necessary'); cb.style.transform = 'translateY(140%)'; setTimeout(() => cb.remove(), 700); });
			return b;
		};
		row.append(mk('NECESSARY ONLY', false), mk('ACCEPT', true));
		cb.append(txt, row);
		document.body.append(cb);
		setTimeout(() => { cb.style.transform = 'none'; }, 1200);
		cleanups.push(() => cb.remove());
	}

	/* hover-peek floating preview */
	const peek = document.createElement('img');
	peek.style.cssText = 'position:fixed;left:0;top:0;width:300px;height:auto;pointer-events:none;z-index:10003;opacity:0;transform:translate(-150px,-50%) rotate(3deg);transition:opacity .3s;filter:contrast(1.05)';
	document.body.append(peek);
	let peekOn = false;
	document.querySelectorAll('[data-peek]').forEach(row => {
		const enter = () => { const im = row.dataset.img; if (!im || !fine) return; peek.src = im; peek.style.opacity = '1'; peekOn = true; };
		const leave = () => { peek.style.opacity = '0'; peekOn = false; };
		row.addEventListener('mouseenter', enter);
		row.addEventListener('mouseleave', leave);
	});
	cleanups.push(() => peek.remove());

	/* drag-to-scroll rails */
	document.querySelectorAll('[data-drag]').forEach(rail => {
		let down = false, sx = 0, sl = 0, moved = 0;
		const dn = e => { down = true; sx = e.clientX; sl = rail.scrollLeft; moved = 0; };
		const mv = e => { if (!down) return; const dx = e.clientX - sx; moved = Math.max(moved, Math.abs(dx)); rail.scrollLeft = sl - dx; };
		const up = () => { down = false; };
		const clk = e => { if (moved > 8) { e.preventDefault(); e.stopPropagation(); } };
		rail.addEventListener('pointerdown', dn);
		rail.addEventListener('pointermove', mv);
		rail.addEventListener('pointerup', up); rail.addEventListener('pointerleave', up);
		rail.addEventListener('click', clk, true);
	});

	/* card hover choreography (handled by CSS; JS kept for filter fallback) */
	tick_noop();
	function tick_noop() {}

	return () => cleanups.forEach(f => f());
}
