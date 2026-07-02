/* ============================================================
   Silence — silence.js
   - hero slideshow (crossfade)
   - the signature: chrome falls silent on idle
   - index hover preview + category filter
   - project rail (wheel → horizontal, keys, counter)
   ============================================================ */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer  = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  const pad2 = (n) => String(n).padStart(2, '0');

  /* ---------- hero slideshow ---------- */
  const hero = $('[data-hero]');
  if (hero) {
    let slides = [];
    try { slides = JSON.parse(($('#sl-hero-data') || {}).textContent || '[]') || []; } catch (e) { slides = []; }

    const plates  = $$('.sl-plate', hero);
    const titleEl = $('[data-hero-link]', hero);
    const metaEl  = $('[data-hero-meta]', hero);
    const iEl     = $('[data-hero-i]', hero);
    const N       = plates.length || 1;
    let cur = 0, locked = false, timer = null;

    function go(i) {
      if (locked || N < 2) return;
      i = ((i % N) + N) % N;
      if (i === cur) return;
      locked = true;

      plates.forEach((p, k) => {
        p.classList.toggle('is-on', k === i);
        p.setAttribute('aria-hidden', k === i ? 'false' : 'true');
      });
      hero.classList.add('is-swapping');

      // swap the caption mid-crossfade, while it's invisible
      setTimeout(() => {
        const s = slides[i] || {};
        if (titleEl) { titleEl.textContent = s.title || ''; titleEl.href = s.url || '#'; }
        if (metaEl)  metaEl.textContent = s.meta || '';
        if (iEl)     iEl.textContent = pad2(i + 1);
        hero.classList.remove('is-swapping');
        cur = i;
        locked = false;
      }, reduceMotion ? 0 : 700);
    }

    const prev = $('[data-hero-prev]', hero);
    const next = $('[data-hero-next]', hero);
    if (prev) prev.addEventListener('click', () => { go(cur - 1); arm(); });
    if (next) next.addEventListener('click', () => { go(cur + 1); arm(); });

    window.addEventListener('keydown', (e) => {
      if (/INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '')) return;
      if (e.key === 'ArrowLeft')  { go(cur - 1); arm(); }
      if (e.key === 'ArrowRight') { go(cur + 1); arm(); }
    });

    // touch swipe
    let sx = 0, sy = 0;
    hero.addEventListener('touchstart', (e) => { sx = e.changedTouches[0].clientX; sy = e.changedTouches[0].clientY; }, { passive: true });
    hero.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.4) { go(cur + (dx < 0 ? 1 : -1)); arm(); }
    }, { passive: true });

    const interval = Math.max(3, parseInt(hero.dataset.interval || '7', 10)) * 1000;
    function arm() {
      if (timer) clearInterval(timer);
      if (!reduceMotion && N > 1) timer = setInterval(() => go(cur + 1), interval);
    }
    arm();
  }

  /* ---------- the signature: chrome falls silent ----------
     After a few idle seconds the .sl-ui chrome fades out and the
     photograph is all that remains. Any input wakes it. Keyboard
     focus inside the chrome always keeps it awake (a11y). */
  (function fallSilent() {
    if (document.body.classList.contains('sl-no-fade')) return;
    if (!finePointer || reduceMotion) return;
    // only where the image is the page: home + single project
    if (!$('.sl-hero') && !$('.sl-project')) return;

    const DELAY = 3500;
    let t = null;

    function hush() {
      const focused = document.activeElement;
      if (focused && focused.closest && focused.closest('.sl-ui')) return; // keep chrome for keyboard users
      document.body.classList.add('is-quiet');
    }
    function wake() {
      document.body.classList.remove('is-quiet');
      if (t) clearTimeout(t);
      t = setTimeout(hush, DELAY);
    }
    ['mousemove', 'mousedown', 'keydown', 'touchstart', 'wheel'].forEach((ev) =>
      window.addEventListener(ev, wake, { passive: true })
    );
    wake();
  })();

  /* ---------- index: hover preview + dimming ---------- */
  const index = $('[data-index]');
  if (index) {
    const list    = $('[data-index-list]', index);
    const preview = $('[data-index-preview]', index);

    if (list && preview && finePointer) {
      list.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a[data-preview]');
        if (!a) return;
        list.classList.add('is-hovering');
        const src = a.dataset.preview;
        if (src) {
          preview.style.backgroundImage = 'url("' + src + '")';
          index.classList.add('has-preview');
        } else {
          index.classList.remove('has-preview');
        }
      });
      list.addEventListener('mouseleave', () => {
        list.classList.remove('is-hovering');
        index.classList.remove('has-preview');
      });
    }

    // category filter (client-side, instant)
    $$('.sl-filters button', index).forEach((btn) => {
      btn.addEventListener('click', () => {
        $$('.sl-filters button', index).forEach((b) => b.classList.toggle('is-on', b === btn));
        const f = btn.dataset.filter || '';
        let n = 0;
        $$('li[data-cats]', list).forEach((li) => {
          const show = !f || (' ' + (li.dataset.cats || '') + ' ').includes(' ' + f + ' ');
          li.classList.toggle('is-hidden', !show);
          if (show) {
            n++;
            const num = li.querySelector('.sl-index__n');
            if (num) num.textContent = pad2(n);
          }
        });
      });
    });
  }

  /* ---------- project rail ---------- */
  const railPage = $('[data-rail-page]');
  if (railPage) {
    const rail   = $('[data-rail]', railPage);
    const iEl    = $('[data-rail-i]', railPage);
    const plates = $$('.sl-rail__plate', railPage);

    if (rail && finePointer) {
      // vertical wheel drives the horizontal rail (desktop)
      rail.addEventListener('wheel', (e) => {
        if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
        e.preventDefault();
        rail.scrollLeft += e.deltaY;
      }, { passive: false });
    }

    if (rail && iEl && plates.length && 'IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) iEl.textContent = pad2(parseInt(en.target.dataset.plateN || '1', 10));
        });
      }, { root: window.matchMedia('(max-width:900px)').matches ? null : rail, threshold: 0.6 });
      plates.forEach((p) => io.observe(p));
    }

    window.addEventListener('keydown', (e) => {
      if (!rail) return;
      if (/INPUT|TEXTAREA|SELECT/.test((document.activeElement || {}).tagName || '')) return;
      const step = Math.round(window.innerWidth * 0.6);
      if (e.key === 'ArrowRight') rail.scrollBy({ left: step,  behavior: reduceMotion ? 'auto' : 'smooth' });
      if (e.key === 'ArrowLeft')  rail.scrollBy({ left: -step, behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }
})();
