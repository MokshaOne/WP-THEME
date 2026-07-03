/* ============================================================
   Silence III — silence.js · v3.0.0 · the scroll edition
   - load intro (serif words rise, opening plate settles)
   - scroll reveals (.nr-rise)
   - chapter parallax + pinned horizontal film strip
   - page progress hairline (project pages)
   - index: cursor-trailing preview + filters
   - enquire: live price calculator
   - the signature: chrome falls silent on idle
   ============================================================ */
(function () {
  'use strict';

  const $  = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer  = window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  const mobile       = window.matchMedia('(max-width:900px)').matches;
  const pad2 = (n) => String(n).padStart(2, '0');

  document.documentElement.classList.add('nr-js'); // gates reveal-hiding to JS-on

  /* ---------- load intro ---------- */
  if (!reduceMotion) {
    document.body.classList.add('nr-intro');
    const reveal = () => requestAnimationFrame(() =>
      setTimeout(() => document.body.classList.remove('nr-intro'), 120));
    (document.fonts && document.fonts.ready) ? document.fonts.ready.then(reveal) : reveal();
  }

  /* ---------- scroll reveals ---------- */
  const risers = $$('.nr-rise');
  if (risers.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    risers.forEach((el) => io.observe(el));
  } else {
    risers.forEach((el) => el.classList.add('is-in'));
  }

  /* ---------- one rAF-throttled scroll pass drives everything ---------- */
  const parallaxes = reduceMotion || mobile ? [] :
    $$('[data-parallax]').map((el) => ({ el, img: el.querySelector('img') })).filter((p) => p.img);
  const strip      = $('[data-strip]');
  const stripTrack = strip && $('[data-strip-track]', strip);
  const pageBar    = $('[data-page-bar]');

  let ticking = false;
  function paint() {
    ticking = false;
    const vh = window.innerHeight;

    parallaxes.forEach(({ el, img }) => {
      const r = el.getBoundingClientRect();
      if (r.bottom < 0 || r.top > vh) return;
      const p = (r.top + r.height / 2 - vh / 2) / (vh + r.height); // -0.5 … 0.5
      img.style.transform = `translateY(${(p * -8).toFixed(2)}%)`;
    });

    if (strip && stripTrack && !mobile) {
      const r = strip.getBoundingClientRect();
      const span = r.height - vh;
      if (span > 0) {
        const p = Math.min(1, Math.max(0, -r.top / span));
        const max = stripTrack.scrollWidth - strip.clientWidth;
        stripTrack.style.transform = `translateX(${(-p * Math.max(0, max)).toFixed(1)}px)`;
      }
    }

    if (pageBar) {
      const doc = document.documentElement;
      const max = doc.scrollHeight - vh;
      pageBar.style.width = max > 0 ? ((doc.scrollTop || window.scrollY) / max * 100) + '%' : '0%';
    }
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(paint); } }
  if (parallaxes.length || (strip && stripTrack) || pageBar) {
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    paint();
  }

  /* ---------- the signature: chrome falls silent ---------- */
  (function fallSilent() {
    if (document.body.classList.contains('nr-no-fade')) return;
    if (!finePointer || reduceMotion) return;
    if (!$('.nr-open') && !$('.nr-project')) return;

    const DELAY = 3500;
    let t = null;

    function hush() {
      const focused = document.activeElement;
      if (focused && focused.closest && focused.closest('.nr-ui, .nr-top')) return;
      document.body.classList.add('is-quiet');
    }
    function wake() {
      document.body.classList.remove('is-quiet');
      if (t) clearTimeout(t);
      t = setTimeout(hush, DELAY);
    }
    ['mousemove', 'mousedown', 'keydown', 'touchstart', 'wheel', 'scroll'].forEach((ev) =>
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

  /* ---------- enquire: live price calculator ---------- */
  const quote = $('[data-quote]');
  if (quote) {
    const out    = $('[data-quote-total]', quote);
    const perKm  = parseFloat(quote.dataset.perKm || '0');
    const cur    = quote.dataset.currency || '€';
    const kmIn   = quote.querySelector('input[name="quote_km"]');

    function calc() {
      if (!out) return;
      let sum = 0;
      const type = quote.querySelector('input[name="quote_type"]:checked');
      if (type) sum += parseFloat(type.dataset.base || '0');
      $$('input[name="quote_extras[]"]:checked', quote).forEach((x) => { sum += parseFloat(x.dataset.price || '0'); });
      const km = kmIn ? Math.max(0, parseInt(kmIn.value || '0', 10) || 0) : 0;
      sum += km * perKm;
      out.textContent = cur + Math.round(sum).toLocaleString();
    }
    quote.addEventListener('change', calc);
    if (kmIn) kmIn.addEventListener('input', calc);
    calc();
  }
})();
