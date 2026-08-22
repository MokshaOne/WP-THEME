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
	let peekOn = false; // read by the cursor loop; the hover-peek element is created later

	/* grain + vignette atmosphere */
	const atmo = document.createElement('div');
	atmo.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9998;mix-blend-mode:overlay;opacity:.18;' +
		"background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22140%22 height=%22140%22><filter id=%22n%22><feTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%222%22/><feColorMatrix type=%22saturate%22 values=%220%22/></filter><rect width=%22140%22 height=%22140%22 filter=%22url(%23n)%22 opacity=%22.4%22/></svg>')";
	const vig = document.createElement('div');
	vig.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9997;background:radial-gradient(120% 90% at 50% 10%, rgba(0,0,0,0) 55%, rgba(0,0,0,.42) 100%)';
	document.body.append(vig, atmo);
	cleanups.push(() => { atmo.remove(); vig.remove(); });

	/* preloader (home, first visit per session) */
	let introDelay = 120;
	if (opts.preloader && !reduce) {
		introDelay = 1750;
		const mech = 'cubic-bezier(.9,0,.12,1)';
		const pre = document.createElement('div');
		pre.style.cssText = 'position:fixed;inset:0;z-index:10010;background:#0B0B0B;display:flex;align-items:center;justify-content:center;transition:transform .7s ' + mech;
		const mask = document.createElement('div');
		mask.style.cssText = 'overflow:hidden';
		const word = document.createElement('div');
		word.style.cssText = "font-family:'Anton',sans-serif;font-size:min(10vw,110px);letter-spacing:.02em;color:#F4F2ED;transform:translateY(110%);transition:transform .7s " + mech;
		word.textContent = opts.preloader;
		mask.append(word);
		const boot = document.createElement('div');
		boot.style.cssText = "position:fixed;left:28px;bottom:26px;font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.2em;color:#6f6a63;line-height:2;text-transform:uppercase;white-space:pre";
		const rail = document.createElement('div');
		rail.style.cssText = 'position:fixed;left:0;bottom:0;height:2px;width:0;background:#FF3B1D;transition:width 1.15s ' + mech;
		pre.append(mask, boot, rail);
		document.body.append(pre);
		// instrument boot readout — typed, mechanical
		const LINES = ['RVN / OS — BOOT', '50MM · f/1.4 · READY', '48.2082 N · 16.3738 E — VIENNA'];
		let li = 0, ci = 0, out = '';
		const type = () => {
			if (li >= LINES.length) return;
			out += LINES[li][ci] || '';
			boot.textContent = out;
			if (++ci > LINES[li].length - 1) { li++; ci = 0; out += '\n'; }
			setTimeout(type, 14);
		};
		void word.offsetHeight; // force a layout so the transition actually fires
		// timeout, not rAF: throttled/occluded pages may produce no frames, but the
		// transition still has to arm — timers fire regardless
		setTimeout(() => { word.style.transform = 'none'; rail.style.width = '100%'; }, 50);
		type();
		setTimeout(() => { pre.style.transform = 'translateY(-101%)'; setTimeout(() => pre.remove(), 800); }, 1450);
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
		ring.style.cssText = 'position:fixed;left:0;top:0;width:34px;height:34px;border-radius:50%;border:1.5px solid #efece6;mix-blend-mode:difference;pointer-events:none;z-index:10001;opacity:0;transform:translate(-100px,-100px);transition:width .3s cubic-bezier(.16,1,.3,1),height .3s cubic-bezier(.16,1,.3,1),border-radius .3s,opacity .3s';
		document.body.append(ring);
		let mx = -100, my = -100, rx = -100, ry = -100, seen = false;
		// Magnetic targeting: the ring snaps to and wraps a hovered control, and
		// deliberate buttons drift a few px toward the pointer for a tactile pull.
		let hotEl = null, magEl = null;
		const isMag = (el) => el && el.matches && el.matches('.btn,.cta,.rbtn,[data-magnetic]');
		const clearMag = () => { if (magEl) { magEl.style.transform = ''; magEl = null; } };
		const onMove = e => { mx = e.clientX; my = e.clientY; if (!seen) { seen = true; ring.style.opacity = '1'; } };
		const onOver = e => {
			const hot = e.target.closest && e.target.closest('a,button,[data-zoom]');
			hotEl = hot || null;
			if (hot !== magEl) clearMag();
			if (isMag(hot)) magEl = hot;
			if (!hot) { ring.style.width = '34px'; ring.style.height = '34px'; ring.style.borderRadius = '50%'; }
		};
		const onLeave = () => { ring.style.opacity = '0'; hotEl = null; clearMag(); };
		const tick = () => {
			let tx = mx, ty = my, lerp = 0.18;
			if (hotEl && hotEl.isConnected) {
				const r = hotEl.getBoundingClientRect();
				const cx = r.left + r.width / 2, cy = r.top + r.height / 2;
				// Ring: snap toward the control's centre and wrap it (rounded rect).
				tx = mx + (cx - mx) * 0.35; ty = my + (cy - my) * 0.35; lerp = 0.28;
				const w = Math.min(r.width + 20, 260), h = Math.min(r.height + 20, 120);
				ring.style.width = w + 'px'; ring.style.height = h + 'px';
				ring.style.borderRadius = h > 70 ? '50%' : '10px';
				// Button: drift toward the pointer (magnetic pull), capped.
				if (magEl === hotEl) {
					const px = Math.max(-14, Math.min(14, (mx - cx) * 0.28));
					const py = Math.max(-14, Math.min(14, (my - cy) * 0.28));
					magEl.style.transform = 'translate(' + px.toFixed(1) + 'px,' + py.toFixed(1) + 'px)';
				}
			}
			rx += (tx - rx) * lerp; ry += (ty - ry) * lerp;
			ring.style.left = (rx - ring.offsetWidth / 2) + 'px';
			ring.style.top = (ry - ring.offsetHeight / 2) + 'px';
			ring.style.transform = 'translate(0,0)';
			if (peekOn) peek.style.transform = 'translate(' + (mx + 30) + 'px,' + (my - peek.offsetHeight / 2) + 'px) rotate(3deg)';
			vel *= 0.88;
			const sk = Math.max(-7, Math.min(7, vel * 0.10));
			sEls.forEach(el => { el.style.transform = 'skewX(' + sk.toFixed(2) + 'deg)'; });
			raf = requestAnimationFrame(tick);
		};
		addEventListener('mousemove', onMove, { passive: true });
		addEventListener('mouseover', onOver, { passive: true });
		document.addEventListener('mouseleave', onLeave);
		cleanups.push(() => { removeEventListener('mousemove', onMove); removeEventListener('mouseover', onOver); document.removeEventListener('mouseleave', onLeave); cancelAnimationFrame(raf); clearMag(); ring.remove(); });
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
			inner.style.cssText = 'display:inline-block;transform:translateY(110%);opacity:0;transition:transform .55s cubic-bezier(.9,0,.12,1), opacity .3s';
			inner.textContent = ch === ' ' ? ' ' : ch;
			outer.append(inner); el.append(outer);
		});
		lio.observe(el);
	});
	cleanups.push(() => lio.disconnect());

	/* lightbox (click any [data-zoom]) — full gallery mode: every [data-zoom] in
	   the same container becomes a navigable set (arrows, ←/→ keys, swipe,
	   counter, neighbour preload). Accessible: role=dialog + focus trap, focus
	   returned to the trigger on close. */
	const onZoom = e => {
		const img = e.target.closest && e.target.closest('[data-zoom]');
		if (!img) return;
		e.preventDefault(); e.stopPropagation();
		const scope = img.closest('.rail, [data-work-grid]') || document;
		const set = [...scope.querySelectorAll('[data-zoom]')];
		let idx = Math.max(0, set.indexOf(img));
		const prevFocus = document.activeElement;
		const pad2 = n => String(n).padStart(2, '0');
		const mono = "font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.2em;color:#efece6";

		const box = document.createElement('div');
		box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true'); box.setAttribute('aria-label', 'Image gallery');
		box.style.cssText = 'position:fixed;inset:0;z-index:10005;background:rgba(12,11,10,.94);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s';
		const big = document.createElement('img');
		big.style.cssText = 'max-width:92vw;max-height:88vh;object-fit:contain;transform:scale(.95);transition:transform .5s ' + ease + ',opacity .2s';
		const cap = document.createElement('div');
		cap.style.cssText = "position:fixed;left:28px;bottom:22px;" + mono;
		const count = document.createElement('div');
		count.style.cssText = 'position:fixed;left:28px;top:22px;' + mono + ';color:#8a847a';
		const mkBtn = (label, txt, css) => {
			const b = document.createElement('button');
			b.setAttribute('aria-label', label);
			b.style.cssText = 'background:none;border:1px solid #3a372f;color:#efece6;cursor:pointer;padding:10px 16px;' + mono + ';' + css;
			b.textContent = txt;
			b.addEventListener('click', ev => ev.stopPropagation());
			return b;
		};
		const prev = mkBtn('Previous image', '←', 'position:fixed;left:28px;top:50%;transform:translateY(-50%)');
		const next = mkBtn('Next image', '→', 'position:fixed;right:28px;top:50%;transform:translateY(-50%)');
		const x = mkBtn('Close', '✕ CLOSE', 'position:fixed;right:28px;top:16px;border:none;color:#8a847a');
		if (set.length < 2) { prev.style.display = next.style.display = 'none'; }

		const show = (i) => {
			idx = (i + set.length) % set.length;
			const im = set[idx];
			big.style.opacity = '0';
			const src = im.currentSrc || im.src;
			const swap = () => { big.src = src; big.style.opacity = '1'; };
			const pre = new Image(); pre.onload = swap; pre.onerror = swap; pre.src = src;
			cap.textContent = (im.alt || '').toUpperCase();
			count.textContent = pad2(idx + 1) + ' / ' + pad2(set.length);
			// warm the neighbours so arrows feel instant
			[set[(idx + 1) % set.length], set[(idx - 1 + set.length) % set.length]].forEach(n => {
				if (n && n !== im) { const w = new Image(); w.src = n.currentSrc || n.src; }
			});
		};
		prev.addEventListener('click', () => show(idx - 1));
		next.addEventListener('click', () => show(idx + 1));

		box.append(big, cap, count, prev, next, x);
		document.body.append(box);
		show(idx);
		requestAnimationFrame(() => { box.style.opacity = '1'; big.style.transform = 'scale(1)'; });

		const close = () => {
			box.style.opacity = '0'; setTimeout(() => box.remove(), 350);
			removeEventListener('keydown', onKey);
			if (prevFocus && prevFocus.focus) { prevFocus.focus(); }
		};
		const onKey = ev => {
			if (ev.key === 'Escape') { close(); return; }
			if (ev.key === 'ArrowLeft') { show(idx - 1); return; }
			if (ev.key === 'ArrowRight') { show(idx + 1); return; }
			if (ev.key === 'Tab') {
				// trap focus inside the dialog controls
				const f = [prev, next, x].filter(b => b.style.display !== 'none');
				const at = f.indexOf(document.activeElement);
				ev.preventDefault();
				f[(at + (ev.shiftKey ? -1 : 1) + f.length) % f.length].focus();
			}
		};
		let swiped = false;
		box.addEventListener('click', () => { if (swiped) { swiped = false; return; } close(); });
		x.addEventListener('click', close);
		addEventListener('keydown', onKey);
		x.focus();

		// swipe: a mostly-horizontal drag over ~40px flips the image (and must
		// not count as the backdrop click that closes the dialog)
		let tx = null, ty = null;
		box.addEventListener('pointerdown', ev => { tx = ev.clientX; ty = ev.clientY; });
		box.addEventListener('pointerup', ev => {
			if (tx === null) return;
			const dx = ev.clientX - tx, dy = ev.clientY - ty; tx = ty = null;
			if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.5) { swiped = true; show(idx + (dx < 0 ? 1 : -1)); }
		});
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
		// native image/link drag would hijack the pointer mid-gesture
		rail.addEventListener('dragstart', e => e.preventDefault());
	});

	/* Batch 11 — motion physics: inertial smooth scroll + scroll-velocity marquee.
	   Batch 12 — WebGL animated film grain (feature-gated, falls back to the SVG
	   grain). All modules are no-ops under prefers-reduced-motion / on touch. */
	cleanups.push(rvnSmoothScroll(reduce, fine));
	cleanups.push(rvnScrollFX(reduce));
	cleanups.push(rvnGrainGL(reduce, atmo));
	cleanups.push(rvnHoverDistort(reduce, fine));
	cleanups.push(rvnHeroMorphGL(reduce));
	cleanups.push(rvnScramble(reduce, fine));

	return () => cleanups.forEach(f => f());
}

/* ── Hero displacement morph — raw WebGL2, no library ─────────────────────────
   Slide changes in the poster hero are rendered as a GPU displacement morph:
   both frames become textures and a value-noise field tears one into the next.
   Pure progressive enhancement: without WebGL2 / textures / under reduced
   motion the CSS clip-path transition below keeps running untouched. */
function rvnHeroMorphGL(reduce) {
	if (reduce) return () => {};
	const stage = document.querySelector('.phero');
	if (!stage) return () => {};
	const imgs = [...stage.querySelectorAll('.slide')];
	if (imgs.length < 2) return () => {};

	let gl, canvas;
	try { canvas = document.createElement('canvas'); gl = canvas.getContext('webgl2', { alpha: false, antialias: false, depth: false }); } catch (e) {}
	if (!gl) return () => {};

	const vsrc = '#version 300 es\nin vec2 p;out vec2 vUv;void main(){vUv=vec2(p.x*0.5+0.5,1.0-(p.y*0.5+0.5));gl_Position=vec4(p,0.,1.);}';
	const fsrc = `#version 300 es
precision highp float;in vec2 vUv;out vec4 o;
uniform sampler2D uA;uniform sampler2D uB;uniform float uProg;uniform vec2 uRes;uniform vec2 uSizeA;uniform vec2 uSizeB;uniform vec2 uFocA;uniform vec2 uFocB;
float h21(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}
float vnoise(vec2 p){vec2 i=floor(p),f=fract(p);f=f*f*(3.-2.*f);
 return mix(mix(h21(i),h21(i+vec2(1,0)),f.x),mix(h21(i+vec2(0,1)),h21(i+vec2(1,1)),f.x),f.y);}
float fbm(vec2 p){return .62*vnoise(p)+.38*vnoise(p*2.3);}
vec2 cover(vec2 uv, vec2 img, vec2 f){float ca=uRes.x/uRes.y, ia=img.x/img.y;vec2 sc=ia>ca?vec2(ca/ia,1.):vec2(1.,ia/ca);return clamp(f+(uv-f)*sc,0.,1.);}
vec3 grade(vec3 c){float g=dot(c,vec3(.2126,.7152,.0722));return vec3(((g-.5)*1.06+.5)*.66);}
void main(){
 float d=fbm(vUv*2.1);
 float mask=mix(d,vUv.x,0.35);
 float w=0.19;
 float t=smoothstep(uProg*(1.+2.*w)-w-w, uProg*(1.+2.*w)-w+w, mask);
 float amp=sin(uProg*3.14159)*0.22;
 vec2 off=(vec2(d,vnoise(vUv*5.))-.5)*amp;
 float zo=1.+0.05*uProg, zi=1.06-0.06*uProg;
 vec2 uvA=(vUv-.5)*zo+.5+off*(1.-t);
 vec2 uvB=(vUv-.5)*zi+.5-off*t;
 vec3 a=grade(texture(uA,cover(uvA,uSizeA,uFocA)).rgb);
 vec3 b=grade(texture(uB,cover(uvB,uSizeB,uFocB)).rgb);
 o=vec4(mix(a,b,1.-t),1.);
}`;
	const mk = (t, src) => { const sh = gl.createShader(t); gl.shaderSource(sh, src); gl.compileShader(sh); return sh; };
	const prog = gl.createProgram();
	gl.attachShader(prog, mk(gl.VERTEX_SHADER, vsrc)); gl.attachShader(prog, mk(gl.FRAGMENT_SHADER, fsrc)); gl.linkProgram(prog);
	if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return () => {};
	gl.useProgram(prog);
	const buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
	const loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
	const U = (n) => gl.getUniformLocation(prog, n);
	const uA = U('uA'), uB = U('uB'), uProg = U('uProg'), uRes = U('uRes'), uSizeA = U('uSizeA'), uSizeB = U('uSizeB'), uFocA = U('uFocA'), uFocB = U('uFocB');
	gl.uniform1i(uA, 0); gl.uniform1i(uB, 1);
	gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false); // vUv already top-left origin

	canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:3;pointer-events:none;opacity:0;transition:opacity .18s';
	stage.appendChild(canvas);
	const dpr = Math.min(devicePixelRatio || 1, 1.5);
	const resize = () => {
		const r = stage.getBoundingClientRect();
		canvas.width = Math.max(2, Math.round(r.width * dpr));
		canvas.height = Math.max(2, Math.round(r.height * dpr));
		gl.viewport(0, 0, canvas.width, canvas.height);
		gl.uniform2f(uRes, canvas.width, canvas.height);
	};
	resize(); addEventListener('resize', resize);

	// Texture cache: same-origin uploads straight from the decoded <img>;
	// cross-origin goes through a CORS fetch. Failure → morph silently skips.
	const tex = new Map(); // index → {t, w, h} | null | 'pending'
	function makeTex(source, w, h) {
		const t = gl.createTexture();
		gl.activeTexture(gl.TEXTURE0);
		gl.bindTexture(gl.TEXTURE_2D, t);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, source);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		return { t, w, h };
	}
	function load(k) {
		if (tex.has(k)) return;
		const img = imgs[k];
		const src = img.currentSrc || img.src;
		if (!src) { tex.set(k, null); return; }
		let same = false;
		try { same = new URL(src, location.href).origin === location.origin; } catch (e) {}
		if (same) {
			if (!img.complete || !img.naturalWidth) { img.addEventListener('load', () => { tex.delete(k); load(k); }, { once: true }); tex.set(k, null); return; }
			try { tex.set(k, makeTex(img, img.naturalWidth, img.naturalHeight)); } catch (e) { tex.set(k, null); }
			return;
		}
		tex.set(k, 'pending');
		fetch(src, { mode: 'cors', credentials: 'omit' })
			.then((r) => (r.ok ? r.blob() : Promise.reject(0)))
			.then((b) => createImageBitmap(b))
			.then((bmp) => { try { tex.set(k, makeTex(bmp, bmp.width, bmp.height)); } catch (e) { tex.set(k, null); } if (bmp.close) bmp.close(); })
			.catch(() => tex.set(k, null));
	}
	imgs.forEach((_, k) => load(k));
	const focal = imgs.map((im) => {
		const m = /([\d.]+)%\s+([\d.]+)%/.exec(im.style.objectPosition || '');
		return m ? [Math.min(1, +m[1] / 100), Math.min(1, +m[2] / 100)] : [0.5, 0.3];
	});

	let raf = 0, alive = true;
	const onSlide = (e) => {
		if (!alive) return;
		const { from, to } = e.detail || {};
		const A = tex.get(from), B = tex.get(to);
		if (!A || A === 'pending' || !B || B === 'pending') return; // CSS fallback runs
		stage.classList.add('glm');            // slides snap; the GPU owns the motion
		cancelAnimationFrame(raf);
		gl.activeTexture(gl.TEXTURE0); gl.bindTexture(gl.TEXTURE_2D, A.t);
		gl.activeTexture(gl.TEXTURE1); gl.bindTexture(gl.TEXTURE_2D, B.t);
		gl.uniform2f(uSizeA, A.w, A.h); gl.uniform2f(uSizeB, B.w, B.h);
		gl.uniform2f(uFocA, focal[from][0], focal[from][1]); gl.uniform2f(uFocB, focal[to][0], focal[to][1]);
		canvas.style.opacity = '1';
		const t0 = performance.now(), dur = 1150;
		const ease = (x) => (x < .5 ? 16 * x * x * x * x * x : 1 - Math.pow(-2 * x + 2, 5) / 2); // quintic — hard, mechanical
		const frame = (now) => {
			if (!alive) return;
			const p = Math.min(1, (now - t0) / dur);
			gl.uniform1f(uProg, ease(p));
			gl.drawArrays(gl.TRIANGLES, 0, 3);
			if (p < 1) raf = requestAnimationFrame(frame);
			else { canvas.style.opacity = '0'; setTimeout(() => stage.classList.remove('glm'), 200); }
		};
		raf = requestAnimationFrame(frame);
	};
	stage.addEventListener('rvn:slide', onSlide);
	return () => {
		alive = false; cancelAnimationFrame(raf);
		stage.removeEventListener('rvn:slide', onSlide); removeEventListener('resize', resize);
		tex.forEach((v) => { if (v && v !== 'pending') gl.deleteTexture(v.t); });
		canvas.remove(); stage.classList.remove('glm');
	};
}

/* ── Scramble decode on technical labels (fine pointer, mechanical) ───────── */
function rvnScramble(reduce, fine) {
	if (reduce || !fine) return () => {};
	const els = [...document.querySelectorAll('.exif, .eyebrow, .sec__hint, .enq__step')];
	const CH = 'ABCDEFGHIKLMNOPRSTUVXYZ0123456789/·—';
	const handlers = [];
	els.forEach((el) => {
		if (el.children.length) return; // leaf text only
		const orig = el.textContent;
		let busy = false;
		const fn = () => {
			if (busy || !orig.trim()) return;
			busy = true;
			const t0 = performance.now(), dur = 420;
			const step = (now) => {
				const p = Math.min(1, (now - t0) / dur);
				const solid = Math.floor(orig.length * p);
				let out = orig.slice(0, solid);
				for (let k = solid; k < orig.length; k++) out += orig[k] === ' ' ? ' ' : CH[(Math.random() * CH.length) | 0];
				el.textContent = out;
				if (p < 1) requestAnimationFrame(step);
				else { el.textContent = orig; busy = false; }
			};
			requestAnimationFrame(step);
		};
		el.addEventListener('mouseenter', fn);
		handlers.push([el, fn]);
	});
	return () => handlers.forEach(([el, fn]) => el.removeEventListener('mouseenter', fn));
}

/* ── Awwwards tier: WebGL hover distortion on grid images ─────────────────────
   A shared WebGL2 canvas draws the hovered tile's photo with a cursor-driven
   ripple + subtle RGB split, exactly over the real <img>. It is pure progressive
   enhancement: at rest and on any failure (no WebGL2, cross-origin/tainted
   texture, weak hardware) it does nothing and the plain <img> shows through —
   the image is never replaced. Off under reduced motion / coarse pointers. */
function rvnHoverDistort(reduce, fine) {
	if (reduce || !fine || matchMedia('(pointer: coarse)').matches || 'ontouchstart' in window) return () => {};
	// Work/Series grid, every drag rail, and the About portrait.
	const plates = [...document.querySelectorAll('.masonry .card .plate, .rail .card .plate, .studio-hero__portrait')].filter((p) => p.querySelector('img'));
	if (!plates.length) return () => {};

	let gl, canvas;
	try { canvas = document.createElement('canvas'); gl = canvas.getContext('webgl2', { alpha: true, antialias: false, premultipliedAlpha: false }); } catch (e) {}
	if (!gl) return () => {};

	const vsrc = '#version 300 es\nin vec2 p;out vec2 vUv;void main(){vUv=vec2(p.x*0.5+0.5,1.0-(p.y*0.5+0.5));gl_Position=vec4(p,0.,1.);}';
	// uUvS/uUvO cover-map the texture into the plate rect (object-fit: cover),
	// so plates whose visible crop differs from the file's aspect (e.g. the
	// About portrait with its parallax overscan) render the same framing as
	// the real <img> underneath.
	const fsrc = '#version 300 es\nprecision highp float;in vec2 vUv;uniform sampler2D uTex;uniform vec4 uRect;uniform vec2 uMouse;uniform vec2 uUvS;uniform vec2 uUvO;uniform float uAmt;uniform float uTime;out vec4 o;' +
		'void main(){vec2 p=vUv;if(p.x<uRect.x||p.x>uRect.z||p.y<uRect.y||p.y>uRect.w)discard;' +
		'vec2 uvR=(p-uRect.xy)/(uRect.zw-uRect.xy);vec2 d=uvR-uMouse;float dist=length(d);' +
		'float ripple=sin(dist*20.0-uTime*3.0)*0.010*uAmt*smoothstep(0.55,0.0,dist);' +
		'vec2 off=normalize(d+1e-5)*ripple;float s=0.004*uAmt;' +
		'vec2 b0=uUvO+(uvR+off)*uUvS;vec2 bx=uUvO+(uvR+off+vec2(s,0.0))*uUvS;vec2 bn=uUvO+(uvR+off-vec2(s,0.0))*uUvS;' +
		'float r=texture(uTex,bx).r;float g=texture(uTex,b0).g;float b=texture(uTex,bn).b;' +
		'o=vec4(r,g,b,1.0);}';
	const mk = (t, s) => { const sh = gl.createShader(t); gl.shaderSource(sh, s); gl.compileShader(sh); return sh; };
	const prog = gl.createProgram();
	gl.attachShader(prog, mk(gl.VERTEX_SHADER, vsrc)); gl.attachShader(prog, mk(gl.FRAGMENT_SHADER, fsrc)); gl.linkProgram(prog);
	if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return () => {};
	gl.useProgram(prog);
	const buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
	const loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
	const uRect = gl.getUniformLocation(prog, 'uRect'), uMouse = gl.getUniformLocation(prog, 'uMouse'),
		uAmt = gl.getUniformLocation(prog, 'uAmt'), uTime = gl.getUniformLocation(prog, 'uTime'),
		uUvS = gl.getUniformLocation(prog, 'uUvS'), uUvO = gl.getUniformLocation(prog, 'uUvO');
	gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false); // vUv already uses a top-left origin

	canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:3;opacity:0;transition:opacity .25s';
	document.body.appendChild(canvas);
	const dpr = Math.min(devicePixelRatio || 1, 1.5);
	const resize = () => { canvas.width = Math.round(innerWidth * dpr); canvas.height = Math.round(innerHeight * dpr); gl.viewport(0, 0, canvas.width, canvas.height); };
	resize(); addEventListener('resize', resize);

	// Texture strategy that never risks the visible image:
	//  · same-origin  → upload from the already-decoded <img> (format-agnostic,
	//    reliable, no crossorigin attribute needed).
	//  · cross-origin → a separate CORS fetch + createImageBitmap; works only
	//    when the media server sends CORS headers, otherwise it fails and we fall
	//    back to the plain image. The displayed <img> is never touched either way.
	// Cache values: {t,w,h} (ready) · null (failed) · 'pending' (in flight).
	const texCache = new Map();
	function makeTex(source, w, h) {
		const t = gl.createTexture();
		gl.bindTexture(gl.TEXTURE_2D, t);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, source);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		return { t, w, h };
	}
	function texFor(img) {
		const src = img.currentSrc || img.src;
		if (!src) return null;
		const cached = texCache.get(src);
		if (cached && cached !== 'pending') return cached;
		if (cached === 'pending') return null;
		let sameOrigin = false;
		try { sameOrigin = new URL(src, location.href).origin === location.origin; } catch (e) {}
		if (sameOrigin) {
			if (!img.complete || !img.naturalWidth) return null; // decoded next frame
			try { const t = makeTex(img, img.naturalWidth, img.naturalHeight); texCache.set(src, t); return t; }
			catch (e) { texCache.set(src, null); return null; }
		}
		texCache.set(src, 'pending');
		fetch(src, { mode: 'cors', credentials: 'omit' })
			.then((r) => (r.ok ? r.blob() : Promise.reject(new Error('http'))))
			.then((b) => createImageBitmap(b))
			.then((bmp) => {
				try { texCache.set(src, makeTex(bmp, bmp.width, bmp.height)); if (active) start(); }
				catch (e) { texCache.set(src, null); }
				if (bmp.close) bmp.close();
			})
			.catch(() => texCache.set(src, null));
		return null;
	}

	let active = null, amt = 0, mouse = [0.5, 0.5], raf = 0, alive = true, dead = false;
	let frame = 0, slow = 0, last = performance.now();
	const start = () => { if (!raf && alive && !dead) { last = performance.now(); raf = requestAnimationFrame(loop); } };

	function loop(now) {
		if (!alive || dead) return;
		const dt = now - last; last = now;
		if (frame++ > 6 && dt > 45) { if (++slow > 24) { teardown(); return; } }
		const target = active ? 1 : 0;
		amt += (target - amt) * 0.12;
		if (amt < 0.004 && !active) { amt = 0; canvas.style.opacity = '0'; gl.clear(gl.COLOR_BUFFER_BIT); raf = 0; return; }
		const img = active && active.querySelector('img');
		const tex = img ? texFor(img) : null;
		if (!tex) { canvas.style.opacity = '0'; if (!active) { raf = 0; return; } raf = requestAnimationFrame(loop); return; }
		const r = active.getBoundingClientRect();
		if (r.bottom < 0 || r.top > innerHeight) { canvas.style.opacity = '0'; raf = requestAnimationFrame(loop); return; }
		gl.clear(gl.COLOR_BUFFER_BIT);
		gl.uniform4f(uRect, r.left / innerWidth, r.top / innerHeight, r.right / innerWidth, r.bottom / innerHeight);
		gl.uniform2f(uMouse, mouse[0], mouse[1]);
		// cover-map: same framing as the CSS object-fit underneath (centered)
		const cs = Math.max(r.width / tex.w, r.height / tex.h);
		const vw = r.width / (tex.w * cs), vh = r.height / (tex.h * cs);
		gl.uniform2f(uUvS, vw, vh); gl.uniform2f(uUvO, (1 - vw) / 2, (1 - vh) / 2);
		gl.uniform1f(uAmt, amt); gl.uniform1f(uTime, (now % 100000) * 0.001);
		gl.bindTexture(gl.TEXTURE_2D, tex.t);
		gl.drawArrays(gl.TRIANGLES, 0, 3);
		canvas.style.opacity = String(Math.min(1, amt * 1.3));
		raf = requestAnimationFrame(loop);
	}

	const onEnter = (e) => { const pl = e.currentTarget; active = pl; texFor(pl.querySelector('img')); start(); };
	const onMove = (e) => { if (!active) return; const r = active.getBoundingClientRect(); mouse = [(e.clientX - r.left) / r.width, (e.clientY - r.top) / r.height]; };
	const onLeave = (e) => { if (active === e.currentTarget) active = null; };
	plates.forEach((pl) => { pl.addEventListener('pointerenter', onEnter); pl.addEventListener('pointermove', onMove); pl.addEventListener('pointerleave', onLeave); });

	function teardown() {
		if (dead) return; dead = true; alive = false; cancelAnimationFrame(raf); raf = 0;
		removeEventListener('resize', resize);
		plates.forEach((pl) => { pl.removeEventListener('pointerenter', onEnter); pl.removeEventListener('pointermove', onMove); pl.removeEventListener('pointerleave', onLeave); });
		texCache.forEach((t) => { if (t && t !== 'pending') gl.deleteTexture(t.t); }); texCache.clear();
		canvas.remove();
	}
	return () => teardown();
}

/* ── Batch 11: inertial smooth scroll (mini-Lenis, no dependency) ─────────── */
function rvnSmoothScroll(reduce, fine) {
	if (reduce || !fine || matchMedia('(pointer: coarse)').matches || 'ontouchstart' in window) return () => {};
	let target = scrollY, current = scrollY, raf = 0, running = false, programmatic = false;
	const max = () => document.documentElement.scrollHeight - innerHeight;
	const clamp = (v) => Math.max(0, Math.min(max(), v));
	const loop = () => {
		current += (target - current) * 0.12;
		if (Math.abs(target - current) < 0.4) { current = target; running = false; }
		programmatic = true; window.scrollTo(0, current); programmatic = false;
		if (running) raf = requestAnimationFrame(loop);
	};
	const start = () => { if (!running) { running = true; raf = requestAnimationFrame(loop); } };
	const onWheel = (e) => {
		if (e.ctrlKey) return; // let pinch-zoom through
		const unit = e.deltaMode === 1 ? 16 : e.deltaMode === 2 ? innerHeight : 1;
		// A horizontal rail under the cursor owns the gesture: sideways deltas and
		// the vertical wheel both drive the rail until it hits its end — only then
		// does the page take over. Without this, the global preventDefault below
		// would make gallery rails unscrollable by wheel/trackpad entirely.
		const rail = e.target.closest && e.target.closest('[data-drag]');
		if (rail && rail.scrollWidth > rail.clientWidth + 1) {
			const d = (Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY) * unit;
			const maxL = rail.scrollWidth - rail.clientWidth;
			if ((d > 0 && rail.scrollLeft < maxL - 1) || (d < 0 && rail.scrollLeft > 1)) {
				e.preventDefault();
				rail.scrollLeft += d;
				return;
			}
		}
		e.preventDefault();
		target = clamp(target + e.deltaY * unit); start();
	};
	const resync = () => { if (!programmatic && !running) { target = current = scrollY; } };
	addEventListener('wheel', onWheel, { passive: false });
	addEventListener('scroll', resync, { passive: true });
	addEventListener('resize', resync);
	return () => { cancelAnimationFrame(raf); removeEventListener('wheel', onWheel); removeEventListener('scroll', resync); removeEventListener('resize', resync); };
}

/* ── Batch 11: marquee speed eases with scroll velocity ──────────────────── */
function rvnScrollFX(reduce) {
	if (reduce) return () => {};
	const marq = [...document.querySelectorAll('.marquee__row')];
	if (!marq.length) return () => {};
	let lastY = scrollY, vel = 0, raf = 0;
	const onScroll = () => { vel += (scrollY - lastY); lastY = scrollY; };
	addEventListener('scroll', onScroll, { passive: true });
	const loop = () => {
		vel *= 0.9;
		const boost = Math.min(2.6, Math.abs(vel) * 0.03);
		marq.forEach((m) => { m.style.animationDuration = (26 / (1 + boost)).toFixed(2) + 's'; });
		raf = requestAnimationFrame(loop);
	};
	raf = requestAnimationFrame(loop);
	return () => { cancelAnimationFrame(raf); removeEventListener('scroll', onScroll); };
}

/* ── Batch 12: WebGL2 animated film grain (shared canvas, perf auto-disable) ─ */
function rvnGrainGL(reduce, atmo) {
	if (reduce) return () => {};
	let gl, canvas;
	try { canvas = document.createElement('canvas'); gl = canvas.getContext('webgl2', { alpha: true, antialias: false, depth: false }); } catch (e) {}
	if (!gl) return () => {}; // no WebGL2 → keep the SVG grain
	const cssHide = () => { if (atmo) atmo.style.display = 'none'; };
	const cssShow = () => { if (atmo) atmo.style.display = ''; };
	cssHide();
	canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:9998;mix-blend-mode:overlay;opacity:.18';
	document.body.appendChild(canvas);
	const vsrc = '#version 300 es\nin vec2 p;void main(){gl_Position=vec4(p,0.,1.);}';
	const fsrc = '#version 300 es\nprecision highp float;out vec4 o;uniform float t;uniform vec2 r;' +
		'float h(vec2 x){return fract(sin(dot(x,vec2(12.9898,78.233)))*43758.5453);}' +
		'void main(){vec2 uv=gl_FragCoord.xy;float g=h(uv+t);vec2 c=gl_FragCoord.xy/r-0.5;' +
		'float v=smoothstep(0.95,0.35,length(c));float n=(g-0.5)*0.5;o=vec4(vec3(n)-(1.0-v)*0.05,0.5);}';
	const mk = (type, src) => { const s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return s; };
	const prog = gl.createProgram();
	gl.attachShader(prog, mk(gl.VERTEX_SHADER, vsrc)); gl.attachShader(prog, mk(gl.FRAGMENT_SHADER, fsrc));
	gl.linkProgram(prog);
	if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { canvas.remove(); cssShow(); return () => {}; }
	gl.useProgram(prog);
	const buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
	const loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
	const uT = gl.getUniformLocation(prog, 't'), uR = gl.getUniformLocation(prog, 'r');
	const resize = () => {
		const w = Math.min(innerWidth, 1200), h = Math.round(w * innerHeight / innerWidth);
		canvas.width = w; canvas.height = h; gl.viewport(0, 0, w, h); gl.uniform2f(uR, w, h);
	};
	resize(); addEventListener('resize', resize);
	let raf = 0, alive = true, frame = 0, slow = 0, last = performance.now();
	const loop = (now) => {
		if (!alive) return;
		// ~30fps + perf watchdog: if consistently slow, disable and restore SVG grain
		const dt = now - last; last = now;
		if (frame > 8 && dt > 40) { slow++; if (slow > 20) { teardown(); cssShow(); return; } }
		if (frame++ % 2 === 0) { gl.uniform1f(uT, (now % 100000) * 0.06); gl.drawArrays(gl.TRIANGLES, 0, 3); }
		raf = requestAnimationFrame(loop);
	};
	raf = requestAnimationFrame(loop);
	function teardown() { alive = false; cancelAnimationFrame(raf); removeEventListener('resize', resize); canvas.remove(); }
	return () => { teardown(); cssShow(); };
}
