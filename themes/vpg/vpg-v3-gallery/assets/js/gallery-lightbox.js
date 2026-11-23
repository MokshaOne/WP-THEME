/* VPG v3 · gallery-lightbox.js — Cluster 08 · Galerie & Präsentation.
 *
 * A universal, dependency-free presentation layer bound to any container
 * marked [data-vpg-gallery]. Every <a>/<img> inside becomes a lightbox slide.
 *
 *   0281 autoplay + speed   0282 fullscreen black-room   0284 zoom / loupe
 *   0293 panorama scroll     0295 keyboard (←/→/Esc/i)    0296 Ken Burns
 *   0304 grid switcher       0305 LQIP blur-up            0308 sequence player
 *   0310 sound-of-place      0318 info layer (alt/EXIF/palette)
 */
(function () {
  'use strict';
  var F = "'Archivo','Helvetica Neue',Arial,sans-serif";

  /* ── LQIP blur-up (0305) ─────────────────────────────────────────── */
  function blurUp() {
    document.querySelectorAll('img[data-lqip]').forEach(function (img) {
      if (img.dataset.lqipDone) return;
      img.dataset.lqipDone = '1';
      img.style.filter = 'blur(14px)';
      img.style.transition = 'filter .5s';
      var done = function () { img.style.filter = 'none'; };
      if (img.complete && img.naturalWidth) done();
      else img.addEventListener('load', done, { once: true });
    });
  }

  /* ── Grid switcher (0304) ────────────────────────────────────────── */
  function gridSwitchers() {
    document.querySelectorAll('[data-vpg-grid]').forEach(function (grid) {
      if (grid.dataset.gridReady) return;
      grid.dataset.gridReady = '1';
      var KEY = 'vpg_grid_mode';
      var mode = (function () { try { return localStorage.getItem(KEY) || 'grid'; } catch (e) { return 'grid'; } })();
      var bar = document.createElement('div');
      bar.style.cssText = 'display:flex;gap:6px;justify-content:flex-end;margin-bottom:10px';
      ['grid', 'masonry', 'row'].forEach(function (m) {
        var b = document.createElement('button');
        b.type = 'button'; b.textContent = m === 'grid' ? '▦' : (m === 'masonry' ? '▨' : '▬');
        b.title = m;
        b.style.cssText = 'border:1px solid var(--g-line,#E6E5E1);background:none;width:32px;height:28px;cursor:pointer;font-size:13px';
        b.addEventListener('click', function () { apply(m); try { localStorage.setItem(KEY, m); } catch (e) {} });
        bar.appendChild(b);
      });
      grid.parentNode.insertBefore(bar, grid);
      function apply(m) {
        grid.style.columns = ''; grid.style.display = ''; grid.style.gridTemplateColumns = '';
        if (m === 'masonry') { grid.style.display = 'block'; grid.style.columns = '3 220px'; grid.querySelectorAll(':scope > *').forEach(function (c) { c.style.breakInside = 'avoid'; c.style.marginBottom = '8px'; }); }
        else if (m === 'row') { grid.style.display = 'flex'; grid.style.flexWrap = 'nowrap'; grid.style.overflowX = 'auto'; grid.querySelectorAll(':scope > *').forEach(function (c) { c.style.flex = '0 0 70%'; }); }
        else { grid.style.display = 'grid'; grid.style.gridTemplateColumns = 'repeat(auto-fill,minmax(200px,1fr))'; grid.querySelectorAll(':scope > *').forEach(function (c) { c.style.flex = ''; c.style.breakInside = ''; c.style.marginBottom = ''; }); }
      }
      apply(mode);
    });
  }

  /* ── The lightbox (0281/0282/0284/0293/0295/0296/0310/0318) ──────── */
  var ov, imgEl, capEl, infoEl, slides = [], idx = 0, playing = false, timer = null, speed = 4000, kb = false, zoomed = false, audioEl = null;

  function build() {
    if (ov) return;
    ov = document.createElement('div');
    ov.className = 'vpg-lb'; ov.setAttribute('role', 'dialog'); ov.setAttribute('aria-modal', 'true');
    ov.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(8,8,8,.97);display:none;align-items:center;justify-content:center';
    ov.innerHTML =
      '<button class="vpg-lb-x" aria-label="Close" style="position:absolute;top:14px;right:18px;background:none;border:0;color:#fff;font-size:30px;cursor:pointer;line-height:1">×</button>' +
      '<button class="vpg-lb-prev" aria-label="Previous" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);background:none;border:0;color:#fff;font-size:40px;cursor:pointer">‹</button>' +
      '<button class="vpg-lb-next" aria-label="Next" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:0;color:#fff;font-size:40px;cursor:pointer">›</button>' +
      '<figure class="vpg-lb-stage" style="margin:0;max-width:94vw;max-height:88vh;overflow:auto;cursor:zoom-in"><img class="vpg-lb-img" alt="" style="max-width:94vw;max-height:82vh;object-fit:contain;display:block;margin:0 auto"></figure>' +
      '<figcaption class="vpg-lb-cap" style="position:absolute;left:0;right:0;bottom:52px;text-align:center;color:#e8e7e3;font:600 13px/1.5 ' + F + ';padding:0 20px"></figcaption>' +
      '<div class="vpg-lb-info" hidden style="position:absolute;left:50%;transform:translateX(-50%);bottom:84px;max-width:560px;background:rgba(20,20,20,.9);color:#ddd;font:12px/1.5 ' + F + ';padding:12px 16px;border:1px solid #333"></div>' +
      '<div class="vpg-lb-bar" style="position:absolute;bottom:12px;left:50%;transform:translateX(-50%);display:flex;gap:8px;align-items:center;color:#fff;font:700 11px/1 ' + F + '">' +
        '<button class="vpg-lb-play" style="background:none;border:1px solid #555;color:#fff;padding:6px 10px;cursor:pointer">▶ auto</button>' +
        '<input class="vpg-lb-speed" type="range" min="1500" max="9000" step="500" value="4000" title="speed" style="width:90px">' +
        '<button class="vpg-lb-kb" style="background:none;border:1px solid #555;color:#fff;padding:6px 10px;cursor:pointer" title="Ken Burns">◇ pan</button>' +
        '<button class="vpg-lb-fs" style="background:none;border:1px solid #555;color:#fff;padding:6px 10px;cursor:pointer">⛶ full</button>' +
        '<button class="vpg-lb-i" style="background:none;border:1px solid #555;color:#fff;padding:6px 10px;cursor:pointer">i</button>' +
        '<span class="vpg-lb-n" style="opacity:.7"></span>' +
      '</div>';
    document.body.appendChild(ov);
    imgEl = ov.querySelector('.vpg-lb-img');
    capEl = ov.querySelector('.vpg-lb-cap');
    infoEl = ov.querySelector('.vpg-lb-info');
    ov.querySelector('.vpg-lb-x').addEventListener('click', close);
    ov.querySelector('.vpg-lb-prev').addEventListener('click', function () { go(-1); });
    ov.querySelector('.vpg-lb-next').addEventListener('click', function () { go(1); });
    ov.addEventListener('click', function (e) { if (e.target === ov) close(); });
    ov.querySelector('.vpg-lb-play').addEventListener('click', togglePlay);
    ov.querySelector('.vpg-lb-speed').addEventListener('input', function (e) { speed = +e.target.value; if (playing) { stop(); play(); } });
    ov.querySelector('.vpg-lb-kb').addEventListener('click', function () { kb = !kb; render(); });
    ov.querySelector('.vpg-lb-fs').addEventListener('click', fullscreen);
    ov.querySelector('.vpg-lb-i').addEventListener('click', toggleInfo);
    // zoom / loupe (0284)
    ov.querySelector('.vpg-lb-stage').addEventListener('click', function (e) {
      if (e.target !== imgEl) return;
      zoomed = !zoomed;
      imgEl.style.maxWidth = zoomed ? 'none' : '94vw';
      imgEl.style.maxHeight = zoomed ? 'none' : '82vh';
      e.currentTarget.style.cursor = zoomed ? 'zoom-out' : 'zoom-in';
    });
    document.addEventListener('keydown', function (e) {
      if (ov.style.display === 'none') return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') go(-1);
      else if (e.key === 'ArrowRight') go(1);
      else if (e.key === 'i' || e.key === 'I') toggleInfo();     // 0295 · i for info
    });
  }

  function open(list, start) { build(); slides = list; idx = start; ov.style.display = 'flex'; document.documentElement.style.overflow = 'hidden'; render(); }
  function close() { stop(); if (audioEl) { audioEl.pause(); audioEl = null; } ov.style.display = 'none'; document.documentElement.style.overflow = ''; if (document.fullscreenElement) document.exitFullscreen(); }
  function go(d) { idx = (idx + d + slides.length) % slides.length; zoomed = false; render(); }

  function render() {
    var s = slides[idx];
    imgEl.classList.remove('vpg-kb'); void imgEl.offsetWidth;
    imgEl.style.maxWidth = '94vw'; imgEl.style.maxHeight = '82vh';
    imgEl.src = s.full; imgEl.alt = s.alt || '';
    if (kb) imgEl.classList.add('vpg-kb');
    // panorama (0293): very wide images scroll horizontally in the stage
    var stage = ov.querySelector('.vpg-lb-stage');
    capEl.textContent = s.cap || '';
    ov.querySelector('.vpg-lb-n').textContent = (idx + 1) + ' / ' + slides.length;
    // info layer (0318)
    var bits = [];
    if (s.alt) bits.push('<strong>' + esc(s.alt) + '</strong>');
    if (s.exif) bits.push(esc(s.exif));
    if (s.palette && s.palette.length) bits.push('<span style="display:inline-flex;gap:4px;vertical-align:middle">' + s.palette.map(function (c) { return '<span style="width:14px;height:14px;background:' + esc(c) + ';display:inline-block;border:1px solid #444"></span>'; }).join('') + '</span>');
    infoEl.innerHTML = bits.join('<br>') || 'No details.';
    // sound-of-place (0310)
    if (audioEl) { audioEl.pause(); audioEl = null; }
    if (s.audio) { audioEl = new Audio(s.audio); audioEl.loop = true; audioEl.volume = .5; audioEl.play().catch(function () {}); }
    // preload neighbours
    [1, -1].forEach(function (d) { var n = slides[(idx + d + slides.length) % slides.length]; if (n) { var p = new Image(); p.src = n.full; } });
  }

  function toggleInfo() { infoEl.hidden = !infoEl.hidden; }
  function togglePlay() { playing ? stop() : play(); }
  function play() { playing = true; ov.querySelector('.vpg-lb-play').textContent = '⏸ auto'; timer = setInterval(function () { go(1); }, speed); }
  function stop() { playing = false; if (ov) ov.querySelector('.vpg-lb-play').textContent = '▶ auto'; clearInterval(timer); }
  function fullscreen() { if (!document.fullscreenElement) ov.requestFullscreen && ov.requestFullscreen(); else document.exitFullscreen(); }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  /* ── Bind galleries ──────────────────────────────────────────────── */
  function collect(container) {
    var out = [];
    container.querySelectorAll('a[href],img').forEach(function (el) {
      if (el.tagName === 'A') {
        var href = el.getAttribute('href') || '';
        var im = el.querySelector('img');
        var full = el.dataset.full || (im && im.dataset.full) || href;
        if (!el.dataset.full && !(im && im.dataset.full) && !/\.(jpe?g|png|webp|gif|avif)(\?|$)/i.test(href)) return;
        out.push({ full: full, alt: im ? im.alt : '', cap: el.dataset.cap || (im ? im.alt : ''), exif: el.dataset.exif || '', audio: el.dataset.audio || '', palette: el.dataset.palette ? el.dataset.palette.split(',') : [] });
      } else if (el.tagName === 'IMG' && !el.closest('a[href]')) {
        var full = el.dataset.full || el.currentSrc || el.src;
        out.push({ full: full, alt: el.alt, cap: el.dataset.cap || el.alt, exif: el.dataset.exif || '', audio: el.dataset.audio || '', palette: el.dataset.palette ? el.dataset.palette.split(',') : [] });
      }
    });
    return out;
  }

  function bind() {
    document.querySelectorAll('[data-vpg-gallery]').forEach(function (container) {
      if (container.dataset.lbReady) return;
      container.dataset.lbReady = '1';
      var items = collect(container);
      if (!items.length) return;
      var clickables = container.querySelectorAll('a[href],img');
      var map = [];
      clickables.forEach(function (el) {
        if (el.tagName === 'A') {
          var im = el.querySelector('img');
          if (!el.dataset.full && !(im && im.dataset.full) && !/\.(jpe?g|png|webp|gif|avif)(\?|$)/i.test(el.getAttribute('href') || '')) return;
        }
        if (el.tagName === 'IMG' && el.closest('a[href]')) return;
        map.push(el);
      });
      map.forEach(function (el, i) {
        el.addEventListener('click', function (e) { e.preventDefault(); open(collect(container), i); });
        el.style.cursor = 'zoom-in';
      });
      // sequence player (0308)
      if (container.dataset.sequence !== undefined) {
        open(items, 0); play();
      }
    });
  }

  function init() { blurUp(); gridSwitchers(); bind(); }
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
  window.vpgGalleryRefresh = init;
})();
