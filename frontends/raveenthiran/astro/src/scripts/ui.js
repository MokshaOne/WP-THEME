/* =============================================================================
   Raveenthiran v5 — page behaviours. Boots the motion engine on every Astro
   page-load (survives View Transitions) and wires the interactive modules that
   are present on the current page: Vienna clock, cursor-following row preview,
   RAW/FINAL compare, Work filter/view, Enquire calculator + submit.
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
	fxOff = initFX();
	initClock();
	initPreview();
	initCompare();
	initWorkView();
	initEnquire();
}

/* ── Before / after compare ([rvn_compare] shortcode in journal/project) ──── */
function initCompare() {
	document.querySelectorAll('.rvn-compare').forEach((el) => {
		const range = el.querySelector('[data-compare-range]');
		const set = (v) => el.style.setProperty('--start', Math.max(0, Math.min(100, v)) + '%');
		if (range) { const on = () => set(+range.value); range.addEventListener('input', on); on(); }
		const drag = (e) => {
			const r = el.getBoundingClientRect();
			const pct = ((e.clientX - r.left) / r.width) * 100;
			set(pct); if (range) range.value = String(Math.round(pct));
		};
		const down = (e) => {
			drag(e);
			const mv = (ev) => drag(ev);
			const up = () => { removeEventListener('pointermove', mv); removeEventListener('pointerup', up); };
			addEventListener('pointermove', mv); addEventListener('pointerup', up);
		};
		el.addEventListener('pointerdown', down);
	});
}

/* ── Home clock: Vienna time, HH:MM:SS ───────────────────────────────────── */
function initClock() {
	const els = [...document.querySelectorAll('[data-clock]')];
	if (!els.length) return;
	let f;
	try { f = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Vienna', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }); } catch (e) { return; }
	const tick = () => { const t = f.format(new Date()); els.forEach((el) => { el.textContent = t; }); };
	tick();
	const id = setInterval(tick, 1000);
	pageCleanups.push(() => clearInterval(id));
}

/* ── Cursor-following preview on index rows (Home / Work / Journal) ─────────
   Desktop only: a hover-capable fine pointer and ≥ 720px. The row's data-img
   is shown in the list's .preview, offset so it floats beside the cursor. */
function initPreview() {
	const canHover = matchMedia('(hover: hover) and (pointer: fine)');
	document.querySelectorAll('[data-preview]').forEach((list) => {
		const img = list.querySelector('.preview');
		if (!img) return;
		const on = () => canHover.matches && innerWidth >= 720;
		const move = (e) => {
			if (!on()) return;
			const r = list.getBoundingClientRect();
			const w = img.offsetWidth || 230;
			const x = Math.max(8, Math.min(e.clientX - r.left - w / 2, r.width - w - 8));
			const y = Math.max(e.clientY - r.top - w * 0.66, 8);
			img.style.transform = `translate(${x}px, ${y}px)`;
		};
		const enter = (e) => {
			const row = e.target.closest && e.target.closest('[data-img]');
			if (!row || !list.contains(row) || !on()) { if (!row) img.classList.remove('on'); return; }
			if (img.getAttribute('src') !== row.dataset.img) img.src = row.dataset.img;
			img.classList.add('on');
		};
		const leave = () => img.classList.remove('on');
		list.addEventListener('mousemove', move, { passive: true });
		list.addEventListener('mouseover', enter);
		list.addEventListener('mouseleave', leave);
		pageCleanups.push(() => {
			list.removeEventListener('mousemove', move);
			list.removeEventListener('mouseover', enter);
			list.removeEventListener('mouseleave', leave);
		});
	});
}

/* ── Work: category chips, Index / Grid view, S/M/L tile size ────────────────
   Chips filter rows and tiles together and update the count. View and size
   persist for the tab (sessionStorage). Deep links /work/?cat=<cat> and
   ?series=<slug> (from project pages) pre-narrow the archive. */
function initWorkView() {
	const root = document.querySelector('[data-work]');
	if (!root) return;
	const items = [...root.querySelectorAll('.row[data-cat], .tile[data-cat]')];
	const chips = [...root.querySelectorAll('[data-filter]')];
	const viewBtns = [...root.querySelectorAll('[data-view-btn]')];
	const sizeBtns = [...root.querySelectorAll('[data-size-btn]')];
	const countEl = root.querySelector('[data-count]');
	const press = (btns, fn) => btns.forEach((b) => b.setAttribute('aria-pressed', String(fn(b))));
	const apply = (match) => {
		let shown = 0;
		items.forEach((el) => { const m = match(el); el.classList.toggle('hide', !m); if (m && el.classList.contains('row')) shown++; });
		if (countEl) countEl.textContent = String(shown).padStart(3, '0');
	};
	const byCat = (cat) => (el) => !cat || (el.dataset.cat || '') === cat;
	const setCat = (cat) => { press(chips, (b) => (b.dataset.filter || '') === cat); apply(byCat(cat)); };
	chips.forEach((b) => b.addEventListener('click', () => setCat(b.dataset.filter || '')));

	const KEY = 'rvn-work-view';
	let st = { view: 'index', size: 'M' };
	try { st = { ...st, ...JSON.parse(sessionStorage.getItem(KEY) || '{}') }; } catch (e) {}
	const render = () => {
		root.dataset.view = st.view; root.dataset.size = st.size;
		press(viewBtns, (b) => b.dataset.viewBtn === st.view);
		press(sizeBtns, (b) => b.dataset.sizeBtn === st.size);
		try { sessionStorage.setItem(KEY, JSON.stringify(st)); } catch (e) {}
	};
	viewBtns.forEach((b) => b.addEventListener('click', () => { st.view = b.dataset.viewBtn; render(); }));
	sizeBtns.forEach((b) => b.addEventListener('click', () => { st.size = b.dataset.sizeBtn; st.view = 'grid'; render(); }));
	render();

	const params = new URLSearchParams(location.search);
	const ser = (params.get('series') || '').toLowerCase();
	const cat = (params.get('cat') || '').toUpperCase();
	if (ser) {
		const m = (el) => (el.dataset.series || '').split(/\s+/).includes(ser);
		if (items.some(m)) { press(chips, () => false); apply(m); }
	} else if (cat && items.some(byCat(cat))) {
		setCat(cat);
	}
}

/* ── Enquire calculator + submit ─────────────────────────────────────────── */
function initEnquire() {
	const root = document.querySelector('[data-enquire]');
	if (!root) return;
	const svcs = [...root.querySelectorAll('.svc')];
	const addonBtns = [...root.querySelectorAll('[data-addon]')];
	const usageBtns = [...root.querySelectorAll('[data-usage]')];
	const kmInput = root.querySelector('[data-km-input]');
	const perKm = Number(root.dataset.perkm) || 0;
	const licencePrice = Number(root.dataset.licence) || 0;
	const commercialExtra = Number(root.dataset.commx) || 0;
	const fmt = (n) => n.toLocaleString('de-AT');
	let sel = 0, extra = 0, usage = 'private';
	const els = {
		name: root.querySelector('[data-est-name]'),
		meta: root.querySelector('[data-est-meta]'),
		baseHrs: root.querySelector('[data-base-hrs]'),
		baseFmt: root.querySelector('[data-base-fmt]'),
		extraN: [...root.querySelectorAll('[data-extra-hours]')],
		extraFmt: root.querySelector('[data-extra-fmt]'),
		rate: root.querySelector('[data-extra-rate]'),
		addonsFmt: root.querySelector('[data-addons-fmt]'),
		kmEcho: root.querySelector('[data-km-echo]'),
		travelFmt: root.querySelector('[data-travel-fmt]'),
		licenceFmt: root.querySelector('[data-licence-fmt]'),
		total: [...root.querySelectorAll('[data-total-fmt]')],
		note: root.querySelector('[data-est-note]'),
	};
	const svc = () => svcs[sel];
	const d = (el, k) => Number(el.dataset[k]);
	const render = () => {
		const s = svc();
		const base = d(s, 'base'), hrs = d(s, 'hrs'), rate = d(s, 'extra');
		const extraCost = extra * rate;
		// Add-ons, round-trip travel from Vienna and the commercial usage licence
		// complete the studio's real formula: type × duration × distance + extras.
		// TFP is a collaboration — no extras: the add-ons step locks and clears.
		const isTfp = usage === 'tfp';
		if (isTfp) { addonBtns.forEach((b) => b.setAttribute('aria-pressed', 'false')); }
		addonBtns.forEach((b) => { b.disabled = isTfp; });
		const lock = root.querySelector('[data-addons-lock]');
		lock && (lock.style.display = isTfp ? '' : 'none');
		const picked = addonBtns.filter((b) => b.getAttribute('aria-pressed') === 'true');
		const addonsCost = picked.reduce((sum, b) => sum + (Number(b.dataset.price) || 0), 0);
		const km = Math.max(0, Math.min(2000, Number(kmInput?.value) || 0));
		const travelCost = Math.round(km * 2 * perKm);
		// Usage: TFP zeroes the balance (collaborative — no fee); commercial adds
		// the licence PLUS the commercial surcharge on top.
		const licenceCost = usage === 'commercial' ? licencePrice + commercialExtra : 0;
		const subtotal = base + extraCost + addonsCost + travelCost + licenceCost;
		const total = usage === 'tfp' ? 0 : subtotal;
		svcs.forEach((b, k) => b.setAttribute('aria-pressed', String(k === sel)));
		usageBtns.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.usage === usage)));
		els.name && (els.name.textContent = s.dataset.name);
		els.meta && (els.meta.textContent = s.dataset.meta);
		els.baseHrs && (els.baseHrs.textContent = hrs);
		els.baseFmt && (els.baseFmt.textContent = fmt(base));
		els.extraN.forEach((el) => (el.textContent = extra));
		els.extraFmt && (els.extraFmt.textContent = fmt(extraCost));
		els.rate && (els.rate.textContent = rate);
		els.addonsFmt && (els.addonsFmt.textContent = fmt(addonsCost));
		els.kmEcho && (els.kmEcho.textContent = km);
		els.travelFmt && (els.travelFmt.textContent = fmt(travelCost));
		els.licenceFmt && (els.licenceFmt.textContent = fmt(licenceCost));
		root.querySelectorAll('[data-tfp-line]').forEach((el) => { el.style.display = usage === 'tfp' ? '' : 'none'; });
		const tfpFmt = root.querySelector('[data-tfp-fmt]');
		tfpFmt && (tfpFmt.textContent = fmt(subtotal));
		els.total.forEach((el) => { el.textContent = fmt(total); });
		if (els.note) { const n = s.dataset.note || ''; els.note.textContent = n; els.note.style.display = n ? '' : 'none'; }
		// stash for submit
		root.dataset.selService = s.dataset.name;
		root.dataset.selHours = String(extra);
		root.dataset.selAddons = picked.map((b) => b.dataset.label).join(', ');
		root.dataset.selKm = String(km);
		root.dataset.selUsage = usage;
		root.dataset.selTotal = String(total);
	};
	svcs.forEach((b, k) => b.addEventListener('click', () => { sel = k; render(); }));
	root.querySelector('[data-minus]')?.addEventListener('click', () => { extra = Math.max(0, extra - 1); render(); });
	root.querySelector('[data-plus]')?.addEventListener('click', () => { extra = Math.min(8, extra + 1); render(); });
	addonBtns.forEach((b) => b.addEventListener('click', () => { b.setAttribute('aria-pressed', String(b.getAttribute('aria-pressed') !== 'true')); render(); }));
	usageBtns.forEach((b) => b.addEventListener('click', () => { usage = b.dataset.usage || 'private'; render(); }));
	kmInput?.addEventListener('input', render);

	// /enquire/?for=brands|private — the quick entries above preconfigure the
	// instrument: brands = the editorial session + commercial usage; private is
	// the default spelled out.
	const preset = new URLSearchParams(location.search).get('for');
	if (preset === 'brands') {
		const k = svcs.findIndex((b) => /fashion|editorial|commercial|brand/i.test(b.dataset.name || ''));
		if (k >= 0) { sel = k; }
		usage = 'commercial';
	} else if (preset === 'private') {
		sel = 0; usage = 'private';
	}

	// /enquire/?session=portrait&ref=<project-slug> — arriving from a project
	// page: open on the matching session and attach the project as reference.
	// The title comes from the build-time slug map, never from the URL.
	const qs = new URLSearchParams(location.search);
	const want = (qs.get('session') || '').toLowerCase();
	if (want) {
		const k = svcs.findIndex((b) => (b.dataset.name || '').toLowerCase().includes(want));
		if (k >= 0) { sel = k; }
	}
	let refs = {};
	try { refs = JSON.parse(document.getElementById('enquire-refs')?.textContent || '{}'); } catch (e) {}
	const refBox = root.querySelector('[data-ref]');
	const setRef = (slug) => {
		const title = slug && Object.prototype.hasOwnProperty.call(refs, slug) ? refs[slug] : '';
		root.dataset.refSlug = title ? slug : '';
		root.dataset.refTitle = title;
		if (!refBox) return;
		refBox.hidden = !title;
		const a = refBox.querySelector('[data-ref-link]');
		if (a && title) { a.textContent = title; a.setAttribute('href', '/work/' + encodeURIComponent(slug) + '/'); }
	};
	const onRefClear = () => { setRef(''); save(); };
	refBox?.querySelector('[data-ref-clear]')?.addEventListener('click', onRefClear);

	// Draft: the configuration and the typed details survive a reload or a trip
	// to a project page and back (this tab only — sessionStorage, nothing leaves
	// the browser). An explicit ?for / ?session / ?ref link wins over the draft.
	const form0 = root.querySelector('form');
	const KEY = 'rvn-enquiry-draft';
	const fieldNames = ['name', 'email', 'date', 'location', 'message'];
	const save = () => {
		try {
			const f = {};
			fieldNames.forEach((n) => { f[n] = form0?.elements[n]?.value || ''; });
			sessionStorage.setItem(KEY, JSON.stringify({
				sel, extra, usage, km: kmInput?.value || '0', ref: root.dataset.refSlug || '',
				addons: addonBtns.filter((b) => b.getAttribute('aria-pressed') === 'true').map((b) => b.dataset.label), f,
			}));
		} catch (e) {}
	};
	let draft = null;
	try { draft = JSON.parse(sessionStorage.getItem(KEY) || 'null'); } catch (e) {}
	const linked = preset || want || qs.get('ref');
	if (draft) {
		if (!linked) {
			if (draft.sel >= 0 && draft.sel < svcs.length) { sel = draft.sel; }
			extra = Math.max(0, Math.min(8, Number(draft.extra) || 0));
			if (['tfp', 'private', 'commercial'].includes(draft.usage)) { usage = draft.usage; }
			if (kmInput) { kmInput.value = String(Math.max(0, Math.min(2000, Number(draft.km) || 0))); }
			addonBtns.forEach((b) => b.setAttribute('aria-pressed', String((draft.addons || []).includes(b.dataset.label))));
		}
		fieldNames.forEach((n) => { const el = form0?.elements[n]; if (el && draft.f && typeof draft.f[n] === 'string') { el.value = draft.f[n]; } });
	}
	setRef(qs.get('ref') || (draft && !linked ? draft.ref : '') || '');
	render();
	const onDraft = () => save();
	root.addEventListener('click', onDraft);
	root.addEventListener('input', onDraft);
	pageCleanups.push(() => { root.removeEventListener('click', onDraft); root.removeEventListener('input', onDraft); });

	// Preferred date: a plain field until it is used, then the native date picker
	// (keeps the placeholder readable; no past dates).
	const dateEl = root.querySelector('[data-date]');
	if (dateEl) {
		const toDate = () => { dateEl.type = 'date'; dateEl.min = new Date().toISOString().slice(0, 10); try { dateEl.showPicker && dateEl.showPicker(); } catch (e) {} };
		const toText = () => { if (!dateEl.value) { dateEl.type = 'text'; } };
		if (/^\d{4}-\d{2}-\d{2}$/.test(dateEl.value)) { dateEl.type = 'date'; }
		dateEl.addEventListener('focus', toDate);
		dateEl.addEventListener('blur', toText);
		pageCleanups.push(() => { dateEl.removeEventListener('focus', toDate); dateEl.removeEventListener('blur', toText); });
	}

	const form = root.querySelector('form');
	if (form) {
		const msg = root.querySelector('[data-est-msg]');
		const onSubmit = async (e) => {
			e.preventDefault();
			const endpoint = form.getAttribute('action');
			const fd = new FormData(form);
			fd.set('service', root.dataset.selService || '');
			fd.set('hours', root.dataset.selHours || '0');
			fd.set('addons', root.dataset.selAddons || '');
			fd.set('km', root.dataset.selKm || '0');
			fd.set('usage', root.dataset.selUsage || 'private');
			fd.set('estimate', root.dataset.selTotal || '');
			// The reference project travels inside the message, so it reaches the
			// stored enquiry and both mails without a backend change.
			const refLine = root.dataset.refTitle
				? 'Reference: ' + root.dataset.refTitle + ' — ' + location.origin + '/work/' + root.dataset.refSlug + '/\n\n'
				: '';
			fd.set('message', refLine + (fd.get('message') || ''));
			const btn = form.querySelector('[type="submit"]') || (form.id && document.querySelector(`[type="submit"][form="${form.id}"]`));
			btn && (btn.disabled = true);
			if (msg) { msg.className = 'est__msg'; msg.textContent = 'Sending…'; }
			try {
				// Turnstile's hidden [name=cf-turnstile-response] is already in FormData.
				const res = await fetch(endpoint, { method: 'POST', body: fd });
				if (!res.ok) throw new Error('HTTP ' + res.status);
				if (msg) { msg.className = 'est__msg ok'; msg.textContent = 'Sent. You will have a written offer within 24 hours.'; }
				form.reset();
				try { sessionStorage.removeItem('rvn-enquiry-draft'); } catch (e) {}
				setRef('');
			} catch (err) {
				// graceful fallback to a prefilled mail draft
				const to = form.dataset.mailto || 'hello@raveenthiran.com';
				const subj = encodeURIComponent('Shoot enquiry — ' + (root.dataset.selService || ''));
				const body = encodeURIComponent(
					'Name: ' + (fd.get('name') || '') + '\nEmail: ' + (fd.get('email') || '') +
					'\nDate: ' + (fd.get('date') || '') + '\nLocation: ' + (fd.get('location') || '') +
					'\nService: ' + (root.dataset.selService || '') + '\nExtra hours: ' + (root.dataset.selHours || '0') +
					'\nAdd-ons: ' + (root.dataset.selAddons || '—') + '\nDistance: ' + (root.dataset.selKm || '0') + ' km' +
					'\nUsage: ' + (root.dataset.selUsage || 'private') + '\nIndication: € ' + (root.dataset.selTotal || '') +
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

/* Remove any previously-registered PWA service worker + its caches — the old
   offline layer was serving stale builds, making the live site look reverted.
   The /sw.js kill-switch cleans clients that update it; this handles the rest. */
if ('serviceWorker' in navigator) {
	navigator.serviceWorker.getRegistrations().then((rs) => rs.forEach((r) => r.unregister())).catch(() => {});
}
if (typeof caches !== 'undefined' && caches.keys) {
	caches.keys().then((ks) => ks.forEach((k) => caches.delete(k))).catch(() => {});
}
