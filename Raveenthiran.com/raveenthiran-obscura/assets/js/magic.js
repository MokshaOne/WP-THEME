/* =========================================================
   Magic — the award-grade motion layer for the Studio theme.
   Vanilla, dependency-free, CSP-safe. Everything here is pure
   enhancement: it is skipped entirely under prefers-reduced-motion
   and on touch, and the site is fully usable with JS disabled.
     · intro loader (counter + curtain)
     · inertia (smooth) scroll  → drives parallax + skew
     · magnetic contextual cursor
     · clip-path image reveals + scroll parallax
     · velocity-skewed marquee
     · mask-wipe heading reveals
   ========================================================= */
(function () {
  'use strict';
  var root = document.documentElement;
  var mq = function (q) { return window.matchMedia && window.matchMedia(q).matches; };
  var reduce = mq('(prefers-reduced-motion: reduce)');
  var finePointer = mq('(pointer:fine)');
  var isTouch = mq('(pointer:coarse)') || 'ontouchstart' in window;
  var lerp = function (a, b, n) { return (1 - n) * a + n * b; };
  var clamp = function (v, min, max) { return Math.max(min, Math.min(max, v)); };
  var raf = window.requestAnimationFrame.bind(window);

  if (reduce) { root.classList.add('magic-off'); return; }
  root.classList.add('has-magic');

  /* ══════════════════════════════════════════════════════════
     1 · Intro loader — a counter climbs to 100, then a curtain
     lifts to reveal the page. Once per session.
     ══════════════════════════════════════════════════════════ */
  var loaderDone = false;
  (function loader() {
    if (sessionStorage.getItem('st_intro') === '1' || !document.body) { loaderDone = true; return; }
    var el = document.createElement('div');
    el.className = 'st-loader';
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML =
      '<div class="st-loader__inner">' +
        '<span class="st-loader__mark">Raveenthiran</span>' +
        '<span class="st-loader__num"><i>0</i><b>100</b></span>' +
      '</div><span class="st-loader__bar"></span>';
    document.body.appendChild(el);
    root.classList.add('is-loading');
    var num = el.querySelector('.st-loader__num i');
    var bar = el.querySelector('.st-loader__bar');
    var p = 0, t0 = performance.now(), DUR = 1400;
    (function tick(now) {
      var k = clamp((now - t0) / DUR, 0, 1);
      // ease-out for a confident finish
      p = Math.round((1 - Math.pow(1 - k, 2.2)) * 100);
      if (num) num.textContent = p;
      if (bar) bar.style.transform = 'scaleX(' + (p / 100) + ')';
      if (k < 1) { raf(tick); return; }
      el.classList.add('is-done');
      root.classList.remove('is-loading');
      root.classList.add('intro-reveal');
      sessionStorage.setItem('st_intro', '1');
      setTimeout(function () { el.remove(); loaderDone = true; kickReveals(); }, 900);
    })(t0);
  })();

  /* ══════════════════════════════════════════════════════════
     2 · Inertia scroll (Lenis-lite) — smooths the wheel while
     keeping native scroll position, so position:fixed / sticky
     (header, enquire aside) keep working. Bails on touch, when a
     modal/drawer is open, or over opt-out regions.
     ══════════════════════════════════════════════════════════ */
  var smooth = { cur: window.scrollY || 0, tgt: window.scrollY || 0, vel: 0, on: !isTouch };
  var setBy = smooth.cur;
  function maxScroll() { return Math.max(0, document.documentElement.scrollHeight - window.innerHeight); }

  if (smooth.on) {
    root.classList.add('has-smooth');
    window.addEventListener('wheel', function (e) {
      if (document.body.classList.contains('is-modal-open')) return;
      if (e.ctrlKey) return;                    // pinch-zoom
      if (e.target.closest && e.target.closest('[data-native-scroll], .nr-modal__panel, textarea')) return;
      var d = e.deltaY;
      if (e.deltaMode === 1) d *= 16;           // lines → px
      else if (e.deltaMode === 2) d *= window.innerHeight;
      smooth.tgt = clamp(smooth.tgt + d, 0, maxScroll());
      e.preventDefault();
    }, { passive: false });

    // external scroll (keyboard, scrollbar, anchor, focus) → resync
    window.addEventListener('scroll', function () {
      if (Math.abs(window.scrollY - setBy) > 3) { smooth.tgt = smooth.cur = window.scrollY; }
    }, { passive: true });
    window.addEventListener('resize', function () { smooth.tgt = clamp(smooth.tgt, 0, maxScroll()); });
  }

  /* ══════════════════════════════════════════════════════════
     3 · Contextual magnetic cursor (fine pointer only)
     ══════════════════════════════════════════════════════════ */
  var cursor = null, cx = 0, cy = 0, tx = 0, ty = 0, cScale = 1, cLabel = '';
  if (finePointer && !isTouch) {
    root.classList.add('has-cursor');
    cursor = document.createElement('div');
    cursor.className = 'st-cursor';
    cursor.innerHTML = '<span class="st-cursor__dot"></span><span class="st-cursor__label"></span>';
    document.body && document.body.appendChild(cursor);
    var cLabelEl = cursor.querySelector('.st-cursor__label');
    window.addEventListener('mousemove', function (e) { tx = e.clientX; ty = e.clientY; }, { passive: true });
    window.addEventListener('mousedown', function () { cursor.classList.add('is-down'); });
    window.addEventListener('mouseup', function () { cursor.classList.remove('is-down'); });

    var hoverSel = 'a, button, .st-work__item, .st-card, .st-jcard, .st-hero__caption, .st-next, [data-cursor]';
    document.addEventListener('mouseover', function (e) {
      var t = e.target.closest && e.target.closest(hoverSel);
      if (!t) return;
      var label = t.getAttribute('data-cursor');
      if (!label) {
        if (t.matches('.st-work__item, .st-card, .st-jcard, .st-hero__caption, .st-next')) label = 'View';
        else label = '';
      }
      cScale = label ? 3.4 : 2.2;
      cLabel = label;
      cursor.classList.toggle('has-label', !!label);
      cursor.classList.add('is-hover');
      if (cLabelEl) cLabelEl.textContent = label;
    });
    document.addEventListener('mouseout', function (e) {
      if (e.target.closest && e.target.closest(hoverSel)) {
        cScale = 1; cLabel = ''; cursor.classList.remove('is-hover', 'has-label');
      }
    });
  }

  /* magnetic pull on buttons + nav (fine pointer) */
  var magnets = [];
  if (finePointer && !isTouch) {
    magnets = [].slice.call(document.querySelectorAll('.st-btn, .st-head__cta, .st-cta__title, .st-link, [data-magnetic]'));
    magnets.forEach(function (m) {
      m._mx = 0; m._my = 0; m._tx = 0; m._ty = 0;
      m.addEventListener('mousemove', function (e) {
        var r = m.getBoundingClientRect();
        var str = m.classList.contains('st-cta__title') ? 0.18 : 0.35;
        m._tx = (e.clientX - (r.left + r.width / 2)) * str;
        m._ty = (e.clientY - (r.top + r.height / 2)) * str;
      });
      m.addEventListener('mouseleave', function () { m._tx = 0; m._ty = 0; });
    });
  }

  /* ══════════════════════════════════════════════════════════
     4 · Mask-wipe reveals — headings clip up, media clip in,
     blocks rise. Marked here, animated by IntersectionObserver.
     ══════════════════════════════════════════════════════════ */
  function tag(list, cls) { [].slice.call(document.querySelectorAll(list)).forEach(function (el) { el.classList.add(cls); }); }
  tag('.st-hero__caption-t, .st-sec-title, .st-cta__title, .st-arc-title, .st-about__title, .st-project__title, .st-enquire__title, .st-jpost__title, .st-404__title, .st-intro__lead, .st-voice__q', 'st-mask');
  tag('.st-work__frame, .st-card__frame, .st-journal__frame, .st-jcard__frame, .st-project__figure, .st-about__portrait, .st-enquire__frame', 'st-clip');

  var revEls = [].slice.call(document.querySelectorAll('.st-mask, .st-clip'));
  function kickReveals() {
    if (!('IntersectionObserver' in window)) { revEls.forEach(function (el) { el.classList.add('is-shown'); }); return; }
    var io = new IntersectionObserver(function (ents) {
      ents.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        var sibs = el.parentElement ? [].slice.call(el.parentElement.children) : [];
        var idx = Math.max(0, sibs.indexOf(el));
        el.style.transitionDelay = Math.min(idx * 80, 400) + 'ms';
        el.classList.add('is-shown');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.12 });
    revEls.forEach(function (el) { io.observe(el); });
  }
  // if the loader is skipped (revisit), kick immediately on load
  if (loaderDone || sessionStorage.getItem('st_intro') === '1') {
    if (document.readyState !== 'loading') kickReveals();
    else document.addEventListener('DOMContentLoaded', kickReveals);
  }
  // safety floor — never leave masked content hidden if anything stalls
  setTimeout(function () { revEls.forEach(function (el) { el.classList.add('is-shown'); }); }, 2600);

  /* ══════════════════════════════════════════════════════════
     5 · Parallax targets (data-speed) + hero + marquee handles
     ══════════════════════════════════════════════════════════ */
  var heroImg = document.querySelector('.st-hero__img, .st-project__hero-img, .st-jpost__hero-img');
  // parallax only elements that have no hover-scale of their own, to avoid
  // transform conflicts (grid cards get a clip+scale reveal instead).
  var paraImgs = [].slice.call(document.querySelectorAll('.st-project__figure img'));
  var marquees = [].slice.call(document.querySelectorAll('.nr-clients'));
  var progressBar = document.createElement('div');
  progressBar.className = 'st-progress';
  document.body && document.body.appendChild(progressBar);

  /* ══════════════════════════════════════════════════════════
     6 · One rAF loop drives smooth scroll + cursor + magnets +
     parallax + marquee skew + progress.
     ══════════════════════════════════════════════════════════ */
  function frame() {
    // smooth scroll
    if (smooth.on && !document.body.classList.contains('is-modal-open')) {
      var prev = smooth.cur;
      smooth.cur = lerp(smooth.cur, smooth.tgt, 0.095);
      if (Math.abs(smooth.tgt - smooth.cur) < 0.35) smooth.cur = smooth.tgt;
      smooth.vel = smooth.cur - prev;
      if (Math.abs(smooth.cur - window.scrollY) > 0.4) { setBy = smooth.cur; window.scrollTo(0, smooth.cur); }
    } else {
      smooth.vel = window.scrollY - smooth.cur;
      smooth.cur = window.scrollY;
      smooth.tgt = window.scrollY;
    }

    // cursor
    if (cursor) {
      cx = lerp(cx, tx, 0.2); cy = lerp(cy, ty, 0.2);
      var s = lerp(parseFloat(cursor._s || 1), cScale, 0.15); cursor._s = s;
      cursor.style.transform = 'translate3d(' + cx + 'px,' + cy + 'px,0) translate(-50%,-50%) scale(' + s + ')';
    }
    // magnets
    for (var i = 0; i < magnets.length; i++) {
      var m = magnets[i];
      m._mx = lerp(m._mx, m._tx, 0.2); m._my = lerp(m._my, m._ty, 0.2);
      m.style.transform = (Math.abs(m._mx) < 0.05 && Math.abs(m._my) < 0.05) ? '' : 'translate(' + m._mx.toFixed(2) + 'px,' + m._my.toFixed(2) + 'px)';
    }
    // parallax (only near viewport)
    var vh = window.innerHeight;
    if (heroImg) {
      var hr = heroImg.getBoundingClientRect();
      if (hr.bottom > 0 && hr.top < vh) heroImg.style.transform = 'scale(1.12) translate3d(0,' + (smooth.cur * 0.12) + 'px,0)';
    }
    for (var j = 0; j < paraImgs.length; j++) {
      var im = paraImgs[j];
      var r = im.getBoundingClientRect();
      if (r.bottom > -80 && r.top < vh + 80) {
        var mid = r.top + r.height / 2 - vh / 2;
        im.style.transform = 'translate3d(0,' + (-mid * 0.06).toFixed(1) + 'px,0)';
      }
    }
    // marquee velocity skew (applied to the container, not the animated track)
    var skew = clamp(smooth.vel * 0.32, -7, 7);
    for (var q = 0; q < marquees.length; q++) {
      marquees[q].style.setProperty('--skew', skew.toFixed(2) + 'deg');
    }
    // progress
    var mx = maxScroll();
    progressBar.style.transform = 'scaleX(' + (mx ? smooth.cur / mx : 0) + ')';

    raf(frame);
  }
  raf(frame);

  /* Anchor links → smooth target sync so in-page jumps feel native. */
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = a.getAttribute('href');
    if (id.length < 2) return;
    var tgtEl = document.querySelector(id);
    if (!tgtEl) return;
    e.preventDefault();
    var y = tgtEl.getBoundingClientRect().top + window.scrollY - 90;
    smooth.tgt = clamp(y, 0, maxScroll());
    if (!smooth.on) window.scrollTo({ top: y, behavior: 'smooth' });
  });
})();
