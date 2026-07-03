/* ============================================================
   Silence II — silence.js · v2.0.0
   - hero: curtain-wipe slides, serif word-stagger, ghost numeral,
     autoplay hairline
   - the signature: chrome falls silent on idle
   - index: cursor-trailing preview plate + row dimming + filters
   - rail: wheel → horizontal, counter + ghost, progress, parallax
   ============================================================ */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer  = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  const pad2 = (n) => String(n).padStart(2, '0');

  /* ---------- load intro — words rise, plate settles ---------- */
  if (!reduceMotion && ($('.nr-hero') || $('.nr-index'))) {
    document.body.classList.add('nr-intro');
    const reveal = () => requestAnimationFrame(() =>
      setTimeout(() => document.body.classList.remove('nr-intro'), 120));
    (document.fonts && document.fonts.ready) ? document.fonts.ready.then(reveal) : reveal();
  }

  /* ---------- hero ---------- */
  const hero = $('[data-hero]');
  if (hero) {
    let slides = [];
    try { slides = JSON.parse(($('#nr-hero-data') || {}).textContent || '[]') || []; } catch (e) { slides = []; }

    const plates  = $$('.nr-plate', hero);
    const titleEl = $('[data-hero-title]', hero);
    const metaEl  = $('[data-hero-meta]', hero);
    const iEl     = $('[data-hero-i]', hero);
    const ghostEl = $('[data-hero-ghost]', hero);
    const barEl   = $('[data-hero-bar]', hero);
    const N       = plates.length || 1;
    const interval = Math.max(3, parseInt(hero.dataset.interval || '7', 10)) * 1000;
    let cur = 0, locked = false, timer = null;

    // rebuild the word spans exactly as PHP renders them (last word italic)
    function renderTitle(title) {
      if (!titleEl) return;
      const words = String(title || '').trim().split(/\s+/).filter(Boolean);
      titleEl.innerHTML = words.map((w, k) => {
        const italic = (k === words.length - 1 && words.length > 1) ? ' nr-w--i' : '';
        const el = document.createElement('span');
        el.textContent = w;
        return `<span class="nr-w${italic}"><span class="nr-w__i" style="transition-delay:${k * 70}ms">${el.innerHTML}</span></span> `;
      }).join('');
    }

    function restartBar() {
      if (!barEl) return;
      barEl.classList.remove('is-running');
      barEl.style.setProperty('--nr-interval', (interval / 1000) + 's');
      void barEl.offsetWidth; // restart the CSS animation
      if (!reduceMotion && N > 1) barEl.classList.add('is-running');
    }

    function go(i) {
      if (locked || N < 2) return;
      i = ((i % N) + N) % N;
      if (i === cur) return;
      locked = true;

      const from = plates[cur], to = plates[i];

      // curtain wipe: old plate holds underneath, new one unveils over it
      from.classList.add('is-leaving');
      from.classList.remove('is-on');
      to.classList.add('is-entering', 'is-on');
      to.setAttribute('aria-hidden', 'false');
      from.setAttribute('aria-hidden', 'true');
      void to.offsetWidth;
      to.classList.remove('is-entering');

      // caption drops out, swaps, staggers back in
      hero.classList.add('is-swapping');
      const swapMs = reduceMotion ? 0 : 460;
      setTimeout(() => {
        const s = slides[i] || {};
        renderTitle(s.title);
        if (titleEl && s.url) titleEl.href = s.url;
        if (metaEl)  metaEl.textContent = s.meta || '';
        if (iEl)     iEl.textContent = pad2(i + 1);
        if (ghostEl) ghostEl.textContent = pad2(i + 1);
        requestAnimationFrame(() => requestAnimationFrame(() => hero.classList.remove('is-swapping')));
      }, swapMs);

      setTimeout(() => {
        from.classList.remove('is-leaving');
        cur = i;
        locked = false;
      }, reduceMotion ? 10 : 1050);

      restartBar();
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

    let sx = 0, sy = 0;
    hero.addEventListener('touchstart', (e) => { sx = e.changedTouches[0].clientX; sy = e.changedTouches[0].clientY; }, { passive: true });
    hero.addEventListener('touchend', (e) => {
      const dx = e.changedTouches[0].clientX - sx, dy = e.changedTouches[0].clientY - sy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.4) { go(cur + (dx < 0 ? 1 : -1)); arm(); }
    }, { passive: true });

    function arm() {
      if (timer) clearInterval(timer);
      if (!reduceMotion && N > 1) timer = setInterval(() => go(cur + 1), interval);
    }
    arm();
    restartBar();
  }

  /* ---------- the signature: chrome falls silent ---------- */
  (function fallSilent() {
    if (document.body.classList.contains('nr-no-fade')) return;
    if (!finePointer || reduceMotion) return;
    if (!$('.nr-hero') && !$('.nr-project')) return;

    const DELAY = 3500;
    let t = null;

    function hush() {
      const focused = document.activeElement;
      if (focused && focused.closest && focused.closest('.nr-ui')) return; // keyboard users keep the chrome
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

  /* ---------- index: cursor-trailing preview + filters ---------- */
  const index = $('[data-index]');
  if (index) {
    const list  = $('[data-index-list]', index);
    const float = $('[data-index-float]', index);
    const fImg  = float && float.querySelector('img');

    if (list && float && fImg && finePointer && !reduceMotion) {
      let mx = innerWidth / 2, my = innerHeight / 2, fx = mx, fy = my, vx = 0;
      window.addEventListener('mousemove', (e) => { mx = e.clientX; my = e.clientY; }, { passive: true });
      (function follow() {
        const nx = fx + (mx - fx) * 0.11;
        vx = nx - fx; fx = nx;
        fy += (my - fy) * 0.11;
        const rot = Math.max(-6, Math.min(6, vx * 0.35));
        float.style.transform = `translate(${fx}px, ${fy}px) translate(-50%, -50%) rotate(${rot}deg)`;
        requestAnimationFrame(follow);
      })();

      list.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a[data-preview]');
        if (!a) return;
        list.classList.add('is-hovering');
        const src = a.dataset.preview;
        if (src) {
          if (fImg.getAttribute('src') !== src) fImg.src = src;
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

    // category filter (client-side, instant, renumbers)
    $$('.nr-filters button', index).forEach((btn) => {
      btn.addEventListener('click', () => {
        $$('.nr-filters button', index).forEach((b) => b.classList.toggle('is-on', b === btn));
        const f = btn.dataset.filter || '';
        let n = 0;
        $$('li[data-cats]', list).forEach((li) => {
          const show = !f || (' ' + (li.dataset.cats || '') + ' ').includes(' ' + f + ' ');
          li.classList.toggle('is-hidden', !show);
          if (show) {
            n++;
            const num = li.querySelector('.nr-index__n');
            if (num) num.textContent = pad2(n);
          }
        });
      });
    });
  }

  /* ---------- project rail ---------- */
  const railPage = $('[data-rail-page]');
  if (railPage) {
    const rail    = $('[data-rail]', railPage);
    const iEl     = $('[data-rail-i]', railPage);
    const ghostEl = $('[data-rail-ghost]', railPage);
    const barEl   = $('[data-rail-bar]', railPage);
    const plates  = $$('.nr-rail__plate', railPage);
    const mobile  = window.matchMedia('(max-width:900px)').matches;

    if (rail && finePointer) {
      rail.addEventListener('wheel', (e) => {
        if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
        e.preventDefault();
        rail.scrollLeft += e.deltaY;
      }, { passive: false });
    }

    if (rail && plates.length && 'IntersectionObserver' in window) {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) {
            const n = pad2(parseInt(en.target.dataset.plateN || '1', 10));
            if (iEl)     iEl.textContent = n;
            if (ghostEl) ghostEl.textContent = n;
          }
        });
      }, { root: mobile ? null : rail, threshold: 0.6 });
      plates.forEach((p) => io.observe(p));
    }

    // progress hairline + plate parallax, one rAF-throttled scroll pass
    if (rail && !mobile) {
      let ticking = false;
      const media = plates.map((p) => p.querySelector('img, video')).filter(Boolean);
      function paint() {
        ticking = false;
        const max = rail.scrollWidth - rail.clientWidth;
        if (barEl && max > 0) barEl.style.width = ((rail.scrollLeft / max) * 100) + '%';
        if (!reduceMotion && finePointer) {
          const mid = rail.clientWidth / 2;
          media.forEach((m) => {
            const r = m.getBoundingClientRect();
            const off = (r.left + r.width / 2 - mid) / rail.clientWidth; // -0.5 … 0.5-ish
            m.style.transform = `translateX(${(-off * 26).toFixed(1)}px)`;
          });
        }
      }
      rail.addEventListener('scroll', () => {
        if (!ticking) { ticking = true; requestAnimationFrame(paint); }
      }, { passive: true });
      paint();
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
