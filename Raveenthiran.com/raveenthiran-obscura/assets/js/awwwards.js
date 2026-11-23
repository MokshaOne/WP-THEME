/* Obscura awwwards.js (v4.55.0) — Batch 6 "$10K" interaction layer.
   Loaded only when the GPU-effects toggle is on. Each effect is opt-in + has a
   guaranteed fallback (the normal rail/hero is left untouched if anything fails).
   #14 scrollytelling ([nr_scrolly]) · #123 scroll-scrubbed video hero
   ([data-scroll-video]). */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ── #14 — pinned scrollytelling: [nr_scrolly] with .nr-scrolly__step blocks ──
     The media pins while step captions scroll past; the active step gets .is-on. */
  document.querySelectorAll('.nr-scrolly').forEach(function (sc) {
    var steps = sc.querySelectorAll('.nr-scrolly__step');
    if (!steps.length || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (en) { if (en.isIntersecting) { steps.forEach(function (s) { s.classList.remove('is-on'); }); en.target.classList.add('is-on'); sc.dataset.step = en.target.dataset.step || ''; } });
    }, { threshold: 0.6 });
    steps.forEach(function (s) { io.observe(s); });
  });

  /* ── #123 — scroll-scrubbed video hero: [data-scroll-video] wraps a <video> ── */
  document.querySelectorAll('[data-scroll-video] video').forEach(function (v) {
    if (reduce) { v.removeAttribute('autoplay'); return; }
    v.pause(); v.removeAttribute('autoplay'); v.muted = true;
    var wrap = v.closest('[data-scroll-video]');
    var seekable = false;
    v.addEventListener('loadedmetadata', function () { seekable = isFinite(v.duration) && v.duration > 0; });
    var tick = function () {
      if (!seekable) return;
      var r = wrap.getBoundingClientRect();
      var total = r.height + innerHeight;
      var p = Math.min(1, Math.max(0, (innerHeight - r.top) / total));
      var t = p * v.duration;
      if (Math.abs(t - v.currentTime) > 0.05) { try { v.currentTime = t; } catch (e) {} }
    };
    addEventListener('scroll', tick, { passive: true }); addEventListener('resize', tick); tick();
  });
})();
