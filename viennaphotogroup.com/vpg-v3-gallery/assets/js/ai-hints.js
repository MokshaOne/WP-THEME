/* VPG v3 · ai-hints.js — Cluster 23 · client-side quality pre-check (0884).
 * A local, no-upload sharpness estimate: variance of a Laplacian on a
 * downscaled copy of the chosen photo. Purely advisory, runs in the browser. */
(function () {
  'use strict';
  var input = document.querySelector('input[type=file]');
  if (!input) return;
  input.addEventListener('change', function () {
    var f = input.files && input.files[0];
    if (!f || !/^image\//.test(f.type)) return;
    var url = URL.createObjectURL(f), img = new Image();
    img.onload = function () {
      try { hint(sharpness(img)); } catch (e) {}
      URL.revokeObjectURL(url);
    };
    img.src = url;
  });

  function sharpness(img) {
    var W = 256, H = Math.max(1, Math.round(W * img.height / img.width));
    var c = document.createElement('canvas'); c.width = W; c.height = H;
    var ctx = c.getContext('2d'); ctx.drawImage(img, 0, 0, W, H);
    var d = ctx.getImageData(0, 0, W, H).data, g = new Float32Array(W * H), i, p;
    for (i = 0, p = 0; i < d.length; i += 4, p++) g[p] = 0.299 * d[i] + 0.587 * d[i + 1] + 0.114 * d[i + 2];
    var sum = 0, sum2 = 0, n = 0;
    for (var y = 1; y < H - 1; y++) for (var x = 1; x < W - 1; x++) {
      var o = y * W + x;
      var lap = -4 * g[o] + g[o - 1] + g[o + 1] + g[o - W] + g[o + W];
      sum += lap; sum2 += lap * lap; n++;
    }
    var mean = sum / n; return sum2 / n - mean * mean; // variance
  }

  function hint(v) {
    var box = document.getElementById('vpg-ai-hint');
    if (!box) { box = document.createElement('p'); box.id = 'vpg-ai-hint'; box.style.cssText = 'font-size:13px;margin:8px 0'; input.parentNode.appendChild(box); }
    var S = window.vpgAiHint || {};
    if (v < 60) { box.textContent = '📷 ' + (S.soft || 'This looks a little soft — a sharper frame may read better. (Just a hint, upload anyway if you like.)'); box.style.color = '#996800'; }
    else { box.textContent = '✅ ' + (S.ok || 'Looks sharp.'); box.style.color = '#2a7a2a'; }
  }
})();
