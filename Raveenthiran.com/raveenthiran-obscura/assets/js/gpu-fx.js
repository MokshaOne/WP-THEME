/* Obscura gpu-fx (v4.50.0) — Batch 6 GPU/canvas effects. Loaded ONLY when the
   "GPU effects" toggle is on. Everything degrades silently: no WebGL → no
   heat-haze; reduced-motion → only the static dither remains; calm level → off.
   #4 heat-haze idle shader · #5 animated dither field · #8 metaball cursor
   trail · #9 aperture-iris page reveal. */
(function () {
  'use strict';
  var body = document.body;
  if (!body.classList.contains('nr-gpu')) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hover = window.matchMedia('(hover:hover)').matches;
  function level() { return body.getAttribute('data-nr-motion') || 'standard'; }

  /* ── #9 aperture-iris reveal on first paint (clip-path; cheap + robust) ── */
  if (!reduce && !sessionStorage.getItem('nr_iris')) {
    try { sessionStorage.setItem('nr_iris', '1'); } catch (e) {}
    var iris = document.createElement('div');
    iris.className = 'nr-iris'; iris.setAttribute('aria-hidden', 'true');
    body.appendChild(iris);
    requestAnimationFrame(function () { requestAnimationFrame(function () { iris.classList.add('is-open'); }); });
    setTimeout(function () { iris.remove(); }, 1400);
  }

  /* ── #5 animated Bayer-dither field (2D canvas, very low cost) ── */
  if (!reduce) {
    var dc = document.createElement('canvas');
    dc.className = 'nr-dither'; dc.width = 160; dc.height = 90;
    body.appendChild(dc);
    var dx = dc.getContext('2d');
    var B = [0, 8, 2, 10, 12, 4, 14, 6, 3, 11, 1, 9, 15, 7, 13, 5]; // 4x4 Bayer
    var t = 0;
    (function dither() {
      if (level() === 'calm' || document.hidden) { setTimeout(dither, 800); return; }
      t += 0.012;
      var img = dx.createImageData(160, 90);
      for (var y = 0; y < 90; y++) {
        for (var x = 0; x < 160; x++) {
          var v = (Math.sin(x * 0.06 + t) + Math.cos(y * 0.08 - t * 0.7)) * 0.25 + 0.5;
          var on = v * 16 > B[(y % 4) * 4 + (x % 4)] ? 7 : 0;
          var i = (y * 160 + x) * 4;
          img.data[i] = img.data[i + 1] = img.data[i + 2] = 242; img.data[i + 3] = on;
        }
      }
      dx.putImageData(img, 0, 0);
      setTimeout(function () { requestAnimationFrame(dither); }, 90); // ~11fps is the look
    })();
  }

  /* ── #8 metaball cursor trail (canvas, blur+contrast threshold) ── */
  if (hover && !reduce) {
    var tc = document.createElement('canvas');
    tc.className = 'nr-blob'; tc.width = innerWidth; tc.height = innerHeight;
    body.appendChild(tc);
    var tx = tc.getContext('2d');
    addEventListener('resize', function () { tc.width = innerWidth; tc.height = innerHeight; });
    var pts = [], mx = -100, my = -100;
    addEventListener('mousemove', function (e) { mx = e.clientX; my = e.clientY; }, { passive: true });
    (function trail() {
      requestAnimationFrame(trail);
      if (level() !== 'cinematic') { tx.clearRect(0, 0, tc.width, tc.height); return; }
      pts.push({ x: mx, y: my, r: 14, a: 1 });
      if (pts.length > 22) pts.shift();
      tx.clearRect(0, 0, tc.width, tc.height);
      tx.filter = 'blur(8px) contrast(18)';
      tx.fillStyle = 'rgba(242,160,61,.85)';
      pts.forEach(function (p) {
        p.a *= 0.92; p.r *= 0.965;
        tx.globalAlpha = p.a;
        tx.beginPath(); tx.arc(p.x, p.y, p.r, 0, Math.PI * 2); tx.fill();
      });
      tx.globalAlpha = 1; tx.filter = 'none';
    })();
  }

  /* ── #4 heat-haze idle shader (real WebGL, hero only, after 18s idle) ── */
  var heroEl = document.querySelector('.nr-hero__frame');
  if (heroEl && !reduce) {
    var gl, hc, raf, idleT, active = false, start = 0;
    function arm() { clearTimeout(idleT); stop(); idleT = setTimeout(begin, 18000); }
    ['mousemove', 'keydown', 'touchstart', 'click', 'scroll'].forEach(function (ev) { addEventListener(ev, arm, { passive: true }); });
    function begin() {
      if (level() !== 'cinematic' || document.hidden || active) return;
      hc = document.createElement('canvas'); hc.className = 'nr-haze';
      var r = heroEl.getBoundingClientRect();
      hc.width = Math.min(720, r.width | 0); hc.height = Math.min(480, r.height | 0);
      heroEl.appendChild(hc);
      gl = hc.getContext('webgl', { premultipliedAlpha: true });
      if (!gl) { hc.remove(); return; }
      var vs = 'attribute vec2 p;void main(){gl_Position=vec4(p,0.,1.);}';
      var fs = 'precision mediump float;uniform float t;uniform vec2 res;' +
        'void main(){vec2 uv=gl_FragCoord.xy/res;' +
        'float w=sin(uv.y*40.+t*1.4)*0.5+sin(uv.y*23.-t*0.9)*0.5;' +
        'float a=smoothstep(0.55,1.0,uv.y)*0.05*(0.5+0.5*w);' + // shimmer rises from below
        'gl_FragColor=vec4(vec3(0.95,0.93,0.9)*a, a);}';
      function sh(type, src) { var s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return s; }
      var pr = gl.createProgram();
      gl.attachShader(pr, sh(gl.VERTEX_SHADER, vs)); gl.attachShader(pr, sh(gl.FRAGMENT_SHADER, fs));
      gl.linkProgram(pr);
      if (!gl.getProgramParameter(pr, gl.LINK_STATUS)) { hc.remove(); return; }
      gl.useProgram(pr);
      var buf = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, buf);
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(pr, 'p');
      gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
      var ut = gl.getUniformLocation(pr, 't'), ur = gl.getUniformLocation(pr, 'res');
      gl.uniform2f(ur, hc.width, hc.height);
      gl.enable(gl.BLEND); gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);
      active = true; start = performance.now();
      hc.classList.add('is-on');
      (function frame(now) {
        if (!active) return;
        gl.uniform1f(ut, (now - start) / 1000);
        gl.drawArrays(gl.TRIANGLES, 0, 3);
        raf = requestAnimationFrame(frame);
      })(start);
    }
    function stop() {
      if (!active) return;
      active = false; cancelAnimationFrame(raf);
      if (hc) { hc.remove(); hc = null; }
      gl = null;
    }
    arm();
  }

  /* ── #3 particle dispersion on card hover (canvas; reform-on-enter) ──
     Samples the card's loaded image into a coarse particle grid and lets it
     "assemble" once on hover. Same-origin uploads only; any taint/error just
     removes the overlay and restores the image. Cinematic + desktop only. */
  if (hover && !reduce) {
    var busy = false;
    document.querySelectorAll('.nr-card').forEach(function (card) {
      card.addEventListener('pointerenter', function () {
        if (level() !== 'cinematic' || busy || card.dataset.pd) return;
        var img = card.querySelector('img');
        if (!img || !(img.currentSrc || img.src) || !img.complete || !img.naturalWidth) return;
        var w = card.clientWidth, h = card.clientHeight;
        if (!w || !h) return;
        busy = true; card.dataset.pd = '1';
        var done = false;
        var cv = document.createElement('canvas');
        cv.className = 'nr-pd'; cv.width = w; cv.height = h;
        var cx = cv.getContext('2d');
        var safety = setTimeout(finish, 1200);
        function finish() {
          if (done) return; done = true;
          clearTimeout(safety); busy = false;
          img.style.opacity = ''; if (cv.parentNode) cv.remove();
          setTimeout(function () { delete card.dataset.pd; }, 400); // debounce re-trigger
        }
        try {
          // sample a coarse grid from a downscaled draw of the image
          var GX = 46, GY = Math.max(8, Math.round(GX * h / w));
          var off = document.createElement('canvas'); off.width = GX; off.height = GY;
          var ox = off.getContext('2d');
          ox.drawImage(img, 0, 0, GX, GY);
          var data = ox.getImageData(0, 0, GX, GY).data; // throws if tainted
          var cellW = w / GX, cellH = h / GY, parts = [];
          for (var gy = 0; gy < GY; gy++) for (var gx = 0; gx < GX; gx++) {
            var i = (gy * GX + gx) * 4;
            parts.push({
              tx: gx * cellW, ty: gy * cellH,
              x: gx * cellW + (Math.random() - 0.5) * 60, y: gy * cellH + (Math.random() - 0.5) * 60,
              r: data[i], g: data[i + 1], b: data[i + 2]
            });
          }
          card.style.position = card.style.position || 'relative';
          card.appendChild(cv);
          img.style.opacity = '0';
          var t0 = 0, DUR = 520;
          (function step(t) {
            if (done) return;
            if (!t0) t0 = t;
            var p = Math.min(1, (t - t0) / DUR), e = 1 - Math.pow(1 - p, 3);
            cx.clearRect(0, 0, w, h);
            for (var k = 0; k < parts.length; k++) {
              var q = parts[k];
              var px = q.x + (q.tx - q.x) * e, py = q.y + (q.ty - q.y) * e;
              cx.fillStyle = 'rgba(' + q.r + ',' + q.g + ',' + q.b + ',' + (0.35 + 0.65 * e) + ')';
              cx.fillRect(px, py, cellW + 1, cellH + 1);
            }
            if (p < 1) requestAnimationFrame(step); else finish();
          })(0);
        } catch (e) { finish(); }
      });
    });
  }
})();