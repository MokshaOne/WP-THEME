/* Obscura awwwards.js (v4.55.0) — Batch 6 "$10K" interaction layer.
   Loaded only when the GPU-effects toggle is on. Each effect is opt-in + has a
   guaranteed fallback (the normal rail/hero is left untouched if anything fails).
   #1 plane-grid (CSS-3D) · #13 infinite draggable canvas · #14 scrollytelling
   ([nr_scrolly]) · #123 scroll-scrubbed video hero ([data-scroll-video]). */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;

  /* ── #13 + #1 — draggable canvas / plane-grid alternate archive view ──
     Adds a "canvas" toggle to the portfolio archive. Pan by drag (momentum);
     wheel to pan; click a card to open. #1 = the same with CSS perspective so
     cards read as tilted planes. Pure 2D transforms — robust, no WebGL. */
  (function () {
    var rail = document.querySelector('.nr-portfolio-rail[data-portfolio]');
    var archive = document.querySelector('.nr-portfolio-archive');
    if (!rail || !archive || !hover) return;
    var cards = Array.prototype.slice.call(rail.querySelectorAll('.nr-card'));
    if (cards.length < 4) return;

    var toggle = document.createElement('button');
    toggle.type = 'button'; toggle.className = 'nr-canvas-toggle nr-chip';
    toggle.textContent = '⊞ canvas';
    var chips = archive.querySelector('.nr-chips') || archive.querySelector('.nr-page__head');
    if (chips) chips.appendChild(toggle);

    var canvas, on = false, view = { x: 0, y: 0, vx: 0, vy: 0 }, raf;
    function build() {
      canvas = document.createElement('div'); canvas.className = 'nr-canvas';
      var plane = document.createElement('div'); plane.className = 'nr-canvas__plane';
      // lay cards on a loose grid (clone the existing anchors so links keep working)
      var cols = Math.ceil(Math.sqrt(cards.length)), cw = 320, ch = 360, i = 0;
      cards.forEach(function (c) {
        var clone = c.cloneNode(true);
        clone.classList.add('nr-canvas__card');
        var col = i % cols, row = Math.floor(i / cols);
        var jx = (Math.random() - 0.5) * 40, jy = (Math.random() - 0.5) * 40;
        clone.style.left = (col * cw + jx) + 'px';
        clone.style.top = (row * ch + jy) + 'px';
        plane.appendChild(clone); i++;
      });
      canvas.appendChild(plane);
      archive.appendChild(canvas);
      // centre
      view.x = -(cols * cw) / 2 + innerWidth / 2;
      view.y = -(Math.ceil(cards.length / cols) * ch) / 2 + innerHeight / 2;
      apply(plane);
      drag(canvas, plane);
    }
    function apply(plane) { plane.style.transform = 'translate(' + view.x.toFixed(1) + 'px,' + view.y.toFixed(1) + 'px)'; }
    function drag(el, plane) {
      var down = false, lx = 0, ly = 0;
      el.addEventListener('pointerdown', function (e) { down = true; lx = e.clientX; ly = e.clientY; view.vx = view.vy = 0; el.classList.add('is-drag'); });
      addEventListener('pointerup', function () { down = false; el.classList.remove('is-drag'); });
      el.addEventListener('pointermove', function (e) {
        if (!down) return;
        view.vx = e.clientX - lx; view.vy = e.clientY - ly; lx = e.clientX; ly = e.clientY;
        view.x += view.vx; view.y += view.vy; apply(plane);
      });
      el.addEventListener('wheel', function (e) { e.preventDefault(); view.x -= e.deltaX; view.y -= e.deltaY; apply(plane); }, { passive: false });
      (function inertia() { raf = requestAnimationFrame(inertia); if (down || (Math.abs(view.vx) < 0.1 && Math.abs(view.vy) < 0.1)) return; view.vx *= 0.94; view.vy *= 0.94; view.x += view.vx; view.y += view.vy; apply(plane); })();
      // a click that didn't drag should still open the card
      el.querySelectorAll('a.nr-card').forEach(function (a) {
        var sx, sy; a.addEventListener('pointerdown', function (e) { sx = e.clientX; sy = e.clientY; });
        a.addEventListener('click', function (e) { if (Math.abs(e.clientX - sx) > 6 || Math.abs(e.clientY - sy) > 6) e.preventDefault(); });
      });
    }
    toggle.addEventListener('click', function () {
      on = !on;
      archive.classList.toggle('nr-canvas-on', on);
      toggle.classList.toggle('is-on', on);
      toggle.textContent = on ? '☰ rail' : '⊞ canvas';
      if (on && !canvas) build();
      if (canvas) canvas.style.display = on ? '' : 'none';
    });
  })();

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
