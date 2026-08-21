/* =============================================================================
   Raveenthiran v3 — page behaviours. Boots the motion engine on every Astro
   page-load (survives View Transitions) and wires the interactive modules that
   are present on the current page: mobile menu, home hero slider, RAW/FINAL
   compare, testimonial rotator, Work filter, Enquire calculator + submit.
   ============================================================================= */
import { initFX } from './fx.js';

let fxOff = null;
let pageCleanups = [];

function teardown() {
	if (fxOff) { try { fxOff(); } catch (e) {} fxOff = null; }
	pageCleanups.forEach((f) => { try { f(); } catch (e) {} });
	pageCleanups = [];
}

function boot() {
	teardown();
	const isHome = document.body.dataset.page === 'home';
	let preloader = '';
	if (isHome) {
		try {
			if (!sessionStorage.getItem('rvn-pre')) { preloader = 'RAVEENTHIRAN'; sessionStorage.setItem('rvn-pre', '1'); }
		} catch (e) { preloader = 'RAVEENTHIRAN'; }
	}
	fxOff = initFX({ preloader });
	initMobileMenu();
	initHeroSlider();
	initCinema();
	initCompare();
	initQuotes();
	initFilter();
	initEnquire();
}

/* ── mobile overlay menu ─────────────────────────────────────────────────── */
function initMobileMenu() {
	const burger = document.querySelector('[data-burger]');
	const ovl = document.querySelector('[data-mobile-ovl]');
	if (!burger || !ovl) return;
	const toggle = (open) => {
		ovl.classList.toggle('open', open);
		burger.setAttribute('aria-expanded', String(open));
		document.body.style.overflow = open ? 'hidden' : '';
	};
	const onBurger = () => toggle(!ovl.classList.contains('open'));
	burger.addEventListener('click', onBurger);
	ovl.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => toggle(false)));
	const onKey = (e) => { if (e.key === 'Escape') toggle(false); };
	addEventListener('keydown', onKey);
	pageCleanups.push(() => { burger.removeEventListener('click', onBurger); removeEventListener('keydown', onKey); document.body.style.overflow = ''; });
}

/* ── home hero slider ────────────────────────────────────────────────────── */
function initHeroSlider() {
	const stage = document.querySelector('[data-hero]');
	if (!stage) return;
	const slides = [...stage.querySelectorAll('.slide')];
	if (slides.length < 2) return;
	const capEl = stage.querySelector('[data-cap]');
	const curEl = stage.querySelector('[data-count]');
	let i = slides.findIndex((s) => s.classList.contains('on'));
	if (i < 0) i = 0;
	const total = slides.length;
	let timer = 0;
	const render = () => {
		slides.forEach((s, k) => s.classList.toggle('on', k === i));
		if (capEl) capEl.textContent = slides[i].dataset.cap || '';
		if (curEl) curEl.textContent = String(i + 1).padStart(2, '0');
	};
	const go = (d) => { i = (i + d + total) % total; render(); start(); };
	const start = () => { clearInterval(timer); timer = setInterval(() => go(1), 6000); };
	const prev = stage.querySelector('[data-prev]');
	const next = stage.querySelector('[data-next]');
	prev && prev.addEventListener('click', () => go(-1));
	next && next.addEventListener('click', () => go(1));
	const onKey = (e) => { if (e.key === 'ArrowRight') go(1); if (e.key === 'ArrowLeft') go(-1); };
	addEventListener('keydown', onKey);
	render(); start();
	pageCleanups.push(() => { clearInterval(timer); removeEventListener('keydown', onKey); });
}

/* ── Cinema home slider (crossfade) ──────────────────────────────────────── */
function initCinema() {
	const stage = document.querySelector('[data-cinema]');
	if (!stage) return;
	const slides = [...stage.querySelectorAll('.hv-cin__img')];
	if (slides.length < 2) return;
	const kEl = stage.querySelector('[data-cin-kicker]');
	const tEl = stage.querySelector('[data-cin-title]');
	const cEl = stage.querySelector('[data-cin-count]');
	let i = 0, timer = 0;
	const render = () => {
		slides.forEach((s, k) => s.classList.toggle('on', k === i));
		if (kEl) kEl.textContent = slides[i].dataset.kicker || '';
		if (tEl) tEl.textContent = slides[i].dataset.title || '';
		if (cEl) cEl.textContent = String(i + 1).padStart(2, '0');
	};
	const go = (d) => { i = (i + d + slides.length) % slides.length; render(); start(); };
	const start = () => { clearInterval(timer); timer = setInterval(() => go(1), 5000); };
	stage.querySelector('[data-cin-prev]')?.addEventListener('click', () => go(-1));
	stage.querySelector('[data-cin-next]')?.addEventListener('click', () => go(1));
	const onKey = (e) => { if (e.key === 'ArrowRight') go(1); if (e.key === 'ArrowLeft') go(-1); };
	addEventListener('keydown', onKey);
	render(); start();
	pageCleanups.push(() => { clearInterval(timer); removeEventListener('keydown', onKey); });
}

/* ── RAW / FINAL compare ─────────────────────────────────────────────────── */
function initCompare() {
	const el = document.querySelector('[data-compare]');
	if (!el) return;
	const raw = el.querySelector('.raw');
	const div = el.querySelector('[data-div]');
	const knob = el.querySelector('[data-knob]');
	const set = (e) => {
		const r = el.getBoundingClientRect();
		const pct = Math.max(2, Math.min(98, ((e.clientX - r.left) / r.width) * 100));
		if (raw) raw.style.clipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
		if (div) div.style.left = pct + '%';
		if (knob) knob.style.left = pct + '%';
	};
	const down = (e) => set(e);
	const move = (e) => { if (e.buttons === 1) set(e); };
	el.addEventListener('pointerdown', down);
	el.addEventListener('pointermove', move);
	pageCleanups.push(() => { el.removeEventListener('pointerdown', down); el.removeEventListener('pointermove', move); });
}

/* ── testimonial rotator ─────────────────────────────────────────────────── */
function initQuotes() {
	const box = document.querySelector('[data-quotes]');
	const data = document.querySelector('#quotes-data');
	if (!box || !data) return;
	let quotes = [];
	try { quotes = JSON.parse(data.textContent || '[]'); } catch (e) { return; }
	if (!quotes.length) return;
	const tEl = box.querySelector('[data-quote-text]');
	const bEl = box.querySelector('[data-quote-by]');
	const cEl = box.querySelector('[data-quote-count]');
	let q = 0;
	const render = () => {
		if (tEl) tEl.textContent = '“' + quotes[q].text + '”';
		if (bEl) bEl.textContent = quotes[q].by;
		if (cEl) cEl.textContent = String(q + 1).padStart(2, '0');
	};
	const go = (d) => { q = (q + d + quotes.length) % quotes.length; render(); };
	box.querySelector('[data-q-prev]')?.addEventListener('click', () => go(-1));
	box.querySelector('[data-q-next]')?.addEventListener('click', () => go(1));
	render();
}

/* ── Work filter chips ───────────────────────────────────────────────────── */
function initFilter() {
	const bar = document.querySelector('[data-chips]');
	if (!bar) return;
	const chips = [...bar.querySelectorAll('.chip')];
	const cards = [...document.querySelectorAll('[data-work-grid] .card')];
	const countEl = document.querySelector('[data-count]');
	const apply = (cat) => {
		let shown = 0;
		cards.forEach((c) => {
			const match = cat === 'ALL' || (c.dataset.cat || '').toUpperCase() === cat;
			c.classList.toggle('hide', !match);
			if (match) shown++;
		});
		chips.forEach((ch) => ch.setAttribute('aria-pressed', String(ch.dataset.cat === cat)));
		if (countEl) countEl.textContent = String(shown).padStart(2, '0');
	};
	chips.forEach((ch) => ch.addEventListener('click', () => apply(ch.dataset.cat)));
	apply('ALL');
}

/* ── Enquire calculator + submit ─────────────────────────────────────────── */
function initEnquire() {
	const root = document.querySelector('[data-enquire]');
	if (!root) return;
	const svcs = [...root.querySelectorAll('.svc')];
	const fmt = (n) => n.toLocaleString('de-AT');
	let sel = 0, extra = 0;
	const els = {
		name: root.querySelector('[data-est-name]'),
		meta: root.querySelector('[data-est-meta]'),
		baseHrs: root.querySelector('[data-base-hrs]'),
		baseFmt: root.querySelector('[data-base-fmt]'),
		extraN: [...root.querySelectorAll('[data-extra-hours]')],
		extraFmt: root.querySelector('[data-extra-fmt]'),
		rate: root.querySelector('[data-extra-rate]'),
		total: root.querySelector('[data-total-fmt]'),
		note: root.querySelector('[data-est-note]'),
	};
	const svc = () => svcs[sel];
	const d = (el, k) => Number(el.dataset[k]);
	const render = () => {
		const s = svc();
		const base = d(s, 'base'), hrs = d(s, 'hrs'), rate = d(s, 'extra');
		const extraCost = extra * rate, total = base + extraCost;
		svcs.forEach((b, k) => b.setAttribute('aria-pressed', String(k === sel)));
		els.name && (els.name.textContent = s.dataset.name);
		els.meta && (els.meta.textContent = s.dataset.meta);
		els.baseHrs && (els.baseHrs.textContent = hrs);
		els.baseFmt && (els.baseFmt.textContent = fmt(base));
		els.extraN.forEach((el) => (el.textContent = extra));
		els.extraFmt && (els.extraFmt.textContent = fmt(extraCost));
		els.rate && (els.rate.textContent = rate);
		els.total && (els.total.textContent = fmt(total));
		if (els.note) { const n = s.dataset.note || ''; els.note.textContent = n; els.note.style.display = n ? '' : 'none'; }
		// stash for submit
		root.dataset.selService = s.dataset.name;
		root.dataset.selTotal = String(total);
	};
	svcs.forEach((b, k) => b.addEventListener('click', () => { sel = k; render(); }));
	root.querySelector('[data-minus]')?.addEventListener('click', () => { extra = Math.max(0, extra - 1); render(); });
	root.querySelector('[data-plus]')?.addEventListener('click', () => { extra = Math.min(8, extra + 1); render(); });
	render();

	const form = root.querySelector('form');
	if (form) {
		const msg = root.querySelector('[data-est-msg]');
		const onSubmit = async (e) => {
			e.preventDefault();
			const endpoint = form.getAttribute('action');
			const fd = new FormData(form);
			fd.set('service', root.dataset.selService || '');
			fd.set('estimate', root.dataset.selTotal || '');
			const btn = form.querySelector('[type="submit"]');
			btn && (btn.disabled = true);
			if (msg) { msg.className = 'est__msg'; msg.textContent = 'Sending…'; }
			try {
				const ts = form.querySelector('[name="cf-turnstile-response"]');
				const res = await fetch(endpoint, { method: 'POST', body: fd });
				if (!res.ok) throw new Error('HTTP ' + res.status);
				if (msg) { msg.className = 'est__msg ok'; msg.textContent = 'Sent. You will have a written offer within 24 hours.'; }
				form.reset();
			} catch (err) {
				// graceful fallback to a prefilled mail draft
				const to = form.dataset.mailto || 'hello@raveenthiran.com';
				const subj = encodeURIComponent('Shoot enquiry — ' + (root.dataset.selService || ''));
				const body = encodeURIComponent(
					'Name: ' + (fd.get('name') || '') + '\nEmail: ' + (fd.get('email') || '') +
					'\nDate: ' + (fd.get('date') || '') + '\nLocation: ' + (fd.get('location') || '') +
					'\nService: ' + (root.dataset.selService || '') + '\nEstimate: € ' + (root.dataset.selTotal || '') +
					'\n\n' + (fd.get('message') || ''));
				if (msg) { msg.className = 'est__msg err'; msg.textContent = 'Opening your mail app instead…'; }
				location.href = 'mailto:' + to + '?subject=' + subj + '&body=' + body;
			} finally {
				btn && (btn.disabled = false);
			}
		};
		form.addEventListener('submit', onSubmit);
		pageCleanups.push(() => form.removeEventListener('submit', onSubmit));
	}
}

/* ── Easter egg: type "raveen" anywhere → a quick luminance flip ─────────── */
(() => {
	let buf = '';
	addEventListener('keydown', (e) => {
		if (!e.key || e.key.length !== 1) return;
		const t = e.target;
		if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA')) return;
		buf = (buf + e.key.toLowerCase()).slice(-6);
		if (buf === 'raveen') {
			const h = document.documentElement;
			h.classList.add('rvn-flash');
			setTimeout(() => h.classList.remove('rvn-flash'), 460);
		}
	});
})();

/* ── boot ────────────────────────────────────────────────────────────────── */
document.addEventListener('astro:page-load', boot);
document.addEventListener('astro:before-swap', teardown);
if (document.readyState !== 'loading') { /* first load handled by astro:page-load */ }

/* service worker */
if ('serviceWorker' in navigator) {
	addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
