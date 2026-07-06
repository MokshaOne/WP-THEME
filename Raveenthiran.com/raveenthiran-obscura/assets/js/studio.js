/* =========================================================
   Studio — scroll interactions for the light editorial theme.
   Dependency-free. Shared behaviours (mobile drawer, modals,
   data-copy, portfolio filter, quote, compare, map) stay in
   theme.js; this only adds what the scrolling layout needs:
     · header solid-on-scroll
     · IntersectionObserver reveals
     · scroll-to-top
   ========================================================= */
(function () {
  'use strict';
  var doc = document;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- header: solid + hide-on-scroll-down ---------- */
  var head = doc.querySelector('[data-head]');
  if (head) {
    var lastY = window.pageYOffset || 0;
    var solidAt = head.classList.contains('st-head--over') ? 40 : 8;
    var onScroll = function () {
      var y = window.pageYOffset || doc.documentElement.scrollTop || 0;
      head.classList.toggle('is-solid', y > solidAt);
      // hide when scrolling down past the hero, show when scrolling up
      if (y > 320 && y > lastY + 4) head.classList.add('is-hidden');
      else if (y < lastY - 4 || y < 320) head.classList.remove('is-hidden');
      lastY = y;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- reveals ----------
     Any [data-reveal], plus a sensible default set, fades/rises in
     once. Staggered children via [data-reveal-stagger]. */
  var targets = [].slice.call(doc.querySelectorAll(
    '[data-reveal], .st-work__item, .st-feature, .st-approach__item, ' +
    '.st-journal__item, .st-strip__item, .nr-card, .st-project__figure'
  ));
  targets.forEach(function (el) { el.classList.add('st-r'); });

  if (reduce || !('IntersectionObserver' in window)) {
    targets.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        var stagger = el.parentElement && el.parentElement.hasAttribute('data-reveal-stagger');
        if (stagger) {
          var kids = [].slice.call(el.parentElement.children).indexOf(el);
          el.style.transitionDelay = Math.min(kids * 70, 420) + 'ms';
        }
        el.classList.add('is-in');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    targets.forEach(function (el) { io.observe(el); });
  }

  /* ---------- scroll to top ---------- */
  doc.addEventListener('click', function (e) {
    var t = e.target.closest && e.target.closest('[data-scroll-top]');
    if (!t) return;
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
  });

  /* ---------- gold pixel-dither side rails ----------
     A tiny canvas paints an ordered-dither (8×8 Bayer) gradient —
     deep gold at the outer edge dissolving pixel by pixel into the
     paper toward the center. Upscaled with image-rendering:pixelated
     for a chunky pixel-art read. Sits at z-index:-1, so photography
     and dark sections pass over it. Static here; magic.js adds the
     scroll drift. Runs for everyone (it's decoration, not motion). */
  (function rails() {
    if (!doc.body) return;
    var left = doc.createElement('div');
    var right = doc.createElement('div');
    left.className = 'st-rail st-rail--l';
    right.className = 'st-rail st-rail--r';
    left.setAttribute('aria-hidden', 'true');
    right.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(left); doc.body.appendChild(right);

    var W = 28, H = 56;
    var c = doc.createElement('canvas');
    c.width = W; c.height = H;
    var ctx = c.getContext('2d');
    if (!ctx) return;
    var B = [
      [ 0,32, 8,40, 2,34,10,42],[48,16,56,24,50,18,58,26],
      [12,44, 4,36,14,46, 6,38],[60,28,52,20,62,30,54,22],
      [ 3,35,11,43, 1,33, 9,41],[51,19,59,27,49,17,57,25],
      [15,47, 7,39,13,45, 5,37],[63,31,55,23,61,29,53,21]
    ];
    var deep = [168, 118, 34], gold = [201, 156, 61], pale = [246, 243, 236];
    var mix = function (a, b, t) { return Math.round(a + (b - a) * t); };
    var img = ctx.createImageData(W, H);
    for (var y = 0; y < H; y++) {
      for (var x = 0; x < W; x++) {
        var t = x / (W - 1);                       // 0 outer edge → 1 center
        var th = (B[y % 8][x % 8] + 0.5) / 64;
        var alpha = Math.pow(1 - t, 1.6);          // dissolve density
        var on = alpha > th;
        var i = (y * W + x) * 4;
        var col;
        if (t < 0.35) { var k = t / 0.35; col = [mix(deep[0], gold[0], k), mix(deep[1], gold[1], k), mix(deep[2], gold[2], k)]; }
        else { var k2 = (t - 0.35) / 0.65; col = [mix(gold[0], pale[0], k2), mix(gold[1], pale[1], k2), mix(gold[2], pale[2], k2)]; }
        img.data[i] = col[0]; img.data[i + 1] = col[1]; img.data[i + 2] = col[2];
        img.data[i + 3] = on ? 235 : 0;
      }
    }
    ctx.putImageData(img, 0, 0);
    var url = 'url(' + c.toDataURL('image/png') + ')';
    [left, right].forEach(function (r) {
      r.style.backgroundImage = url;
      r.style.backgroundSize = '100% 224px';       // 4px blocks at max width
      r.style.backgroundRepeat = 'repeat-y';
    });
  })();

  /* ---------- mark ready (enables CSS transitions post-load) ---------- */
  requestAnimationFrame(function () { doc.documentElement.classList.add('st-ready'); });
})();
