/* ============================================================
   Silence III — silence.js · v3.2.0 · the scroll edition
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

  /* ---------- entrance counter (preloader pattern) ---------- */
  (function loader() {
    const el = $('.nr-loader');
    if (!el) return;
    // the <head> bootstrap set html.nr-load only for first-time, motion-on visits
    if (!document.documentElement.classList.contains('nr-load') ||
        document.body.classList.contains('nr-no-loader')) { el.remove(); return; }
    try { sessionStorage.setItem('nr_seen', '1'); } catch (e) {}
    const nEl = $('[data-loader-n]', el);
    const t0 = performance.now();
    const MIN = 1000, MAX = 2600;
    let assetsReady = false;
    Promise.all([
      (document.fonts && document.fonts.ready) || Promise.resolve(),
      new Promise((res) => (document.readyState === 'complete') ? res() : window.addEventListener('load', res, { once: true })),
    ]).then(() => { assetsReady = true; });

    (function tick(now) {
      const t = (now || performance.now()) - t0;
      // ease toward 90 on a clock; the last 10% waits for real assets
      let p = Math.min(90, Math.round((t / MIN) * 72 + Math.sqrt(t) * 0.6));
      if (assetsReady) p = Math.min(100, Math.max(p, Math.round((t / (MIN * 0.9)) * 100)));
      if (t > MAX) p = 100;
      if (nEl) nEl.textContent = p;
      if (p >= 100) {
        el.classList.add('is-done');
        setTimeout(() => { el.remove(); document.documentElement.classList.remove('nr-load'); }, 1050);
      } else {
        requestAnimationFrame(tick);
      }
    })();
  })();

  /* ---------- inertial scroll (Lenis pattern) ----------
     Wheel input is eased through a lerp; keyboard, anchors, and the
     native scrollbar stay untouched (we resync from scrollY whenever
     the loop is idle). Desktop + motion-on + opt-out only. */
  (function smoothScroll() {
    if (mobile || !finePointer || reduceMotion) return;
    if (document.body.classList.contains('nr-no-smooth')) return;

    let target = window.scrollY, current = target, raf = null;
    const max = () => document.documentElement.scrollHeight - window.innerHeight;
    // the lerp IS the easing — native smooth scrolling would compound it
    document.documentElement.style.scrollBehavior = 'auto';
    const jump = (y) => window.scrollTo({ top: y, behavior: 'instant' });

    function loop() {
      current += (target - current) * 0.095;
      if (Math.abs(target - current) < 0.5) {
        current = target;
        jump(current);
        raf = null;
        return;
      }
      jump(current);
      raf = requestAnimationFrame(loop);
    }
    window.addEventListener('wheel', (e) => {
      if (e.ctrlKey || e.metaKey) return;             // pinch-zoom etc.
      if (e.target.closest('.nr-strip__pin') && mobile) return;
      e.preventDefault();
      if (!raf) { target = current = window.scrollY; } // resync after native scrolls
      target = Math.max(0, Math.min(max(), target + e.deltaY));
      if (!raf) raf = requestAnimationFrame(loop);
    }, { passive: false });
  })();

  /* ---------- scroll-scrubbed statement (TextGradientScroll pattern) ---------- */
  const scrubs = [];
  $$('.nr-statement p').forEach((par) => {
    if (reduceMotion) return;
    par.classList.remove('nr-rise');
    par.classList.add('nr-scrub');
    const walk = (node) => {
      Array.from(node.childNodes).forEach((child) => {
        if (child.nodeType === 3) {
          const frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach((piece) => {
            if (/^\s+$/.test(piece) || piece === '') { frag.appendChild(document.createTextNode(piece)); return; }
            const w = document.createElement('span');
            w.className = 'nr-sw';
            w.textContent = piece;
            frag.appendChild(w);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1) walk(child);
      });
    };
    walk(par);
    scrubs.push({ el: par, words: $$('.nr-sw', par) });
  });

  /* ---------- magnetic elements (Magnet pattern) ---------- */
  if (finePointer && !reduceMotion) {
    $$('.nr-nav a, .nr-filters button, .nr-cta a, .nr-enquire__send button, .nr-back').forEach((el) => {
      el.classList.add('nr-mag');
      el.addEventListener('mousemove', (e) => {
        const r = el.getBoundingClientRect();
        el.classList.add('is-magnet');
        el.style.transform = `translate(${(e.clientX - r.left - r.width / 2) * 0.18}px, ${(e.clientY - r.top - r.height / 2) * 0.3}px)`;
      });
      el.addEventListener('mouseleave', () => {
        el.classList.remove('is-magnet');
        el.style.transform = '';
      });
    });
  }

  /* ---------- CTA letter stagger ---------- */
  $$('.nr-cta__t').forEach((t) => {
    if (reduceMotion) return;
    const text = t.textContent;
    t.textContent = '';
    text.split('').forEach((ch, i) => {
      if (ch === ' ') { t.appendChild(document.createTextNode(' ')); return; }
      const sp = document.createElement('span');
      sp.className = 'nr-ch';
      sp.style.transitionDelay = (i * 22) + 'ms';
      sp.textContent = ch;
      t.appendChild(sp);
    });
  });

  /* ---------- stat count-up (About) ---------- */
  (function countUp() {
    const nums = $$('[data-count-to]');
    if (!nums.length || !('IntersectionObserver' in window)) return;
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        const el = en.target; io.unobserve(el);
        const to = parseInt(el.dataset.countTo || '0', 10);
        const raw = el.dataset.countRaw || String(to);
        if (reduceMotion || !to) { el.textContent = raw; return; }
        // preserve any non-numeric prefix/suffix around the number (e.g. "+", "2020")
        const m = raw.match(/^(\D*)(\d[\d.,]*)(\D*)$/);
        const pre = m ? m[1] : '', post = m ? m[3] : '';
        const t0 = performance.now(), dur = 1100;
        (function step(now) {
          const p = Math.min(1, (now - t0) / dur);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = pre + Math.round(to * eased).toLocaleString() + post;
          if (p < 1) requestAnimationFrame(step); else el.textContent = raw;
        })(t0);
      });
    }, { threshold: 0.5 });
    nums.forEach((n) => io.observe(n));
  })();

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

    scrubs.forEach(({ el, words }) => {
      const r = el.getBoundingClientRect();
      if (r.bottom < 0 || r.top > vh) return;
      // progress 0→1 while the paragraph crosses the middle band of the viewport
      const p = Math.min(1, Math.max(0, (vh * 0.82 - r.top) / (vh * 0.6)));
      const lit = Math.round(p * words.length);
      words.forEach((w, k) => w.classList.toggle('is-lit', k < lit));
    });
  }
  function onScroll() { if (!ticking) { ticking = true; requestAnimationFrame(paint); } }
  if (parallaxes.length || (strip && stripTrack) || pageBar || scrubs.length) {
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
