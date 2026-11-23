/* VPG v3 · learn-tools.js — Cluster 13 · interactive learning widgets.
 *   0487 histogram · 0488 exposure triangle · 0489 focal comparison
 */
(function () {
  'use strict';

  /* ── 0487 · interactive histogram from an image ─────────────────── */
  document.querySelectorAll('[data-vpg-histogram]').forEach(function (box) {
    var src = box.getAttribute('data-vpg-histogram');
    var img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function () {
      var w = 256, h = 120, c = document.createElement('canvas');
      c.width = w; c.height = h; c.style.width = '100%'; c.style.maxWidth = '420px'; c.style.border = '1px solid #E6E5E1';
      var g = c.getContext('2d');
      var tmp = document.createElement('canvas'); tmp.width = 128; tmp.height = 128;
      var tg = tmp.getContext('2d'); tg.drawImage(img, 0, 0, 128, 128);
      var d; try { d = tg.getImageData(0, 0, 128, 128).data; } catch (e) { box.textContent = 'Histogram needs a same-origin image.'; return; }
      var lum = new Array(256).fill(0), max = 1;
      for (var i = 0; i < d.length; i += 4) { var l = Math.round(0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2]); lum[l]++; if (lum[l] > max) max = lum[l]; }
      g.fillStyle = '#fff'; g.fillRect(0, 0, w, h);
      g.fillStyle = '#0B0B0B';
      for (var x = 0; x < 256; x++) { var bh = Math.round(lum[x] / max * h); g.fillRect(x, h - bh, 1, bh); }
      box.innerHTML = '';
      box.appendChild(c);
      var lbl = document.createElement('p'); lbl.style.cssText = 'font:11px monospace;color:#6A6A6A;margin:4px 0 0';
      lbl.textContent = 'shadows ← tones → highlights';
      box.appendChild(lbl);
    };
    img.onerror = function () { box.textContent = 'Could not load the image.'; };
    img.src = src;
  });

  /* ── 0488 · exposure-triangle simulator ─────────────────────────── */
  document.querySelectorAll('[data-vpg-triangle]').forEach(function (box) {
    var SH = [30, 15, 8, 4, 2, 1, 0.5, 0.25, 0.125, 0.0166, 0.008, 0.002, 0.001];
    function shl(s) { return s >= 1 ? s + 's' : '1/' + Math.round(1 / s); }
    box.innerHTML =
      '<div style="border:1px solid #E6E5E1;padding:16px">' +
      '<div id="vt-prev" style="height:120px;border:1px solid #E6E5E1;margin-bottom:12px;background:#888;position:relative;overflow:hidden"><div id="vt-dof" style="position:absolute;inset:0;background:linear-gradient(90deg,#bbb,#888);"></div><div id="vt-blur" style="position:absolute;left:40%;top:30%;width:40px;height:40px;background:#E5341F;border-radius:50%"></div></div>' +
      '<label>ƒ/<b id="vt-ap">5.6</b> · aperture</label><input id="vt-ra" type="range" min="1.4" max="22" step="0.1" value="5.6" style="width:100%;accent-color:#E5341F">' +
      '<label>shutter <b id="vt-sh">1/125</b></label><input id="vt-rs" type="range" min="0" max="12" step="1" value="9" style="width:100%;accent-color:#E5341F">' +
      '<label>ISO <b id="vt-iso">200</b></label><input id="vt-ri" type="range" min="100" max="12800" step="100" value="200" style="width:100%;accent-color:#E5341F">' +
      '<p id="vt-ev" style="font:12px monospace;color:#6A6A6A;margin:8px 0 0"></p></div>';
    var ap = box.querySelector('#vt-ra'), sh = box.querySelector('#vt-rs'), iso = box.querySelector('#vt-ri');
    function up() {
      var a = +ap.value, s = SH[sh.value], i = +iso.value;
      box.querySelector('#vt-ap').textContent = a.toFixed(1);
      box.querySelector('#vt-sh').textContent = shl(s);
      box.querySelector('#vt-iso').textContent = i;
      // EV proxy brightness
      var ev = Math.log2((a * a) / s) - Math.log2(i / 100);
      var bright = Math.max(20, Math.min(230, 128 - ev * 18));
      box.querySelector('#vt-prev').style.filter = 'brightness(' + (bright / 128).toFixed(2) + ')';
      box.querySelector('#vt-dof').style.filter = 'blur(' + Math.max(0, (8 - a) * 0.6).toFixed(1) + 'px)';
      box.querySelector('#vt-blur').style.filter = 'blur(' + Math.max(0, (s - 0.02) * 30).toFixed(1) + 'px)';
      box.querySelector('#vt-prev').style.opacity = i > 3200 ? '0.9' : '1';
      box.querySelector('#vt-ev').textContent = 'EV ≈ ' + ev.toFixed(1) + (i > 3200 ? ' · watch the noise' : '');
    }
    [ap, sh, iso].forEach(function (r) { r.addEventListener('input', up); }); up();
  });

  /* ── 0489 · focal-length comparison ─────────────────────────────── */
  document.querySelectorAll('[data-vpg-focal]').forEach(function (box) {
    var imgs = (box.getAttribute('data-vpg-focal') || '').split('|');
    var labels = (box.getAttribute('data-labels') || '').split(',');
    if (!imgs[0]) return;
    var bar = document.createElement('div'); bar.style.cssText = 'display:flex;gap:6px;margin-bottom:8px';
    var view = document.createElement('img'); view.style.cssText = 'width:100%;display:block';
    view.src = imgs[0];
    imgs.forEach(function (src, i) {
      var b = document.createElement('button'); b.type = 'button'; b.textContent = (labels[i] || (i + 1)) + 'mm';
      b.style.cssText = 'border:1px solid #E6E5E1;background:none;padding:6px 10px;cursor:pointer;font-weight:700';
      b.addEventListener('click', function () { view.src = src; });
      bar.appendChild(b);
    });
    box.appendChild(bar); box.appendChild(view);
  });
})();
