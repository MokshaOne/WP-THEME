/**
 * #1 — WebGL hero transitions (progressive enhancement).
 *
 * Draws the homepage hero slides into a single <canvas> over the existing
 * .nr-hero__frame and dissolves between them with a displacement + chromatic
 * "melt" shader. Driven by the `nr:hero` event that theme.js fires on every
 * slide change, so it stays perfectly in sync with the slider, thumbnails,
 * auto-advance, swipe and keyboard nav.
 *
 * Hard rules — this layer must never make the hero worse:
 *   • opt-in only (enqueued solely when Theme Settings → WebGL is on);
 *   • bails on prefers-reduced-motion, coarse/low-power fallbacks, or any
 *     WebGL failure — leaving the native CSS crossfade untouched;
 *   • the native <img> in plate 0 paints the LCP; we only hide the plates
 *     (class nr-gl-on) AFTER the first texture is uploaded and drawn, so the
 *     swap to the canvas is visually seamless and costs nothing up front.
 *
 * Raw WebGL, zero dependencies. Procedural noise — no extra image requests.
 */
(function () {
  'use strict';

  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  // Desktop enhancement only — the mobile hero uses object-fit:cover + native crossfade.
  if (window.matchMedia && window.matchMedia('(max-width: 900px)').matches) return;
  if (!window.WebGLRenderingContext) return;

  var hero = document.querySelector('[data-hero]');
  if (!hero) return;
  var frame = hero.querySelector('.nr-hero__frame');
  if (!frame) return;

  var plates = Array.prototype.slice.call(frame.querySelectorAll('.nr-hero__plate'));
  var imgs = plates.map(function (p) { return p.querySelector('img'); });
  if (imgs.length < 2 || imgs.some(function (im) { return !im; })) return; // need real photos

  /* ---- GL context ------------------------------------------------ */
  var canvas = document.createElement('canvas');
  canvas.className = 'nr-hero__gl';
  var gl = canvas.getContext('webgl', { antialias: false, alpha: false, premultipliedAlpha: false, powerPreference: 'high-performance' })
        || canvas.getContext('experimental-webgl');
  if (!gl) return; // no WebGL → keep native crossfade

  frame.insertBefore(canvas, frame.firstChild);
  // gradient overlay above the canvas so titles stay legible
  var grad = document.createElement('div');
  grad.className = 'nr-hero__gradient nr-hero__gl-grad';
  frame.insertBefore(grad, canvas.nextSibling);

  /* ---- shaders --------------------------------------------------- */
  var VERT =
    'attribute vec2 p;varying vec2 vUv;void main(){vUv=p*0.5+0.5;gl_Position=vec4(p,0.0,1.0);}';

  var FRAG = [
    'precision highp float;',
    'varying vec2 vUv;',
    'uniform sampler2D uA;uniform sampler2D uB;',
    'uniform float uProg;uniform float uRA;uniform float uRB;uniform float uPlane;',
    'const float PI=3.14159265;',
    'float hash(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}',
    'float noise(vec2 p){vec2 i=floor(p),f=fract(p);f=f*f*(3.0-2.0*f);',
    'float a=hash(i),b=hash(i+vec2(1.0,0.0)),c=hash(i+vec2(0.0,1.0)),d=hash(i+vec2(1.0,1.0));',
    'return mix(mix(a,b,f.x),mix(c,d,f.x),f.y);}',
    // object-fit: contain mapping (matches the native .nr-hero__plate look)
    'vec2 containUv(vec2 uv,float r){float a=r/uPlane;vec2 s=a>1.0?vec2(1.0,a):vec2(1.0/a,1.0);return (uv-0.5)*s+0.5;}',
    'float inBox(vec2 uv){vec2 b=step(vec2(0.0),uv)*step(uv,vec2(1.0));return b.x*b.y;}',
    'vec3 sampleCA(sampler2D t,vec2 uv,float amt){',
    'vec2 c=clamp(uv,0.0,1.0);',
    'float r=texture2D(t,clamp(uv+vec2(amt,0.0),0.0,1.0)).r;',
    'float g=texture2D(t,c).g;',
    'float b=texture2D(t,clamp(uv-vec2(amt,0.0),0.0,1.0)).b;',
    'return vec3(r,g,b);}',
    'void main(){',
    'vec3 bg=vec3(0.043,0.047,0.063);',                // #0B0C10 frame canvas
    'float p=smoothstep(0.0,1.0,uProg);',
    'float bump=sin(uProg*PI);',                       // 0 -> 1 -> 0 across the transition
    'vec2 n=vec2(noise(vUv*3.0+1.0),noise(vUv*3.0+5.0))-0.5;',
    'vec2 warp=n*0.13*bump;',
    'float ca=0.012*bump;',
    'vec2 uvA=containUv(vUv+warp*(1.0-p),uRA);',
    'vec2 uvB=containUv(vUv-warp*p,uRB);',
    'vec3 a=mix(bg,sampleCA(uA,uvA,ca),inBox(uvA));',
    'vec3 b=mix(bg,sampleCA(uB,uvB,ca),inBox(uvB));',
    'vec3 col=mix(a,b,p);',
    // match the CSS plate grade: contrast 1.06 · saturate .94 · brightness .86
    'col=(col-0.5)*1.06+0.5;',
    'col=mix(vec3(dot(col,vec3(0.299,0.587,0.114))),col,0.94);',
    'col*=0.86;',
    'col*=1.0-0.14*bump;',                             // brief shutter darken at mid-point
    'gl_FragColor=vec4(col,1.0);}'
  ].join('\n');

  function compile(type, src) {
    var s = gl.createShader(type);
    gl.shaderSource(s, src); gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) { gl.deleteShader(s); return null; }
    return s;
  }
  var vs = compile(gl.VERTEX_SHADER, VERT), fs = compile(gl.FRAGMENT_SHADER, FRAG);
  if (!vs || !fs) { cleanup(); return; }
  var prog = gl.createProgram();
  gl.attachShader(prog, vs); gl.attachShader(prog, fs); gl.linkProgram(prog);
  if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { cleanup(); return; }
  gl.useProgram(prog);

  var buf = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buf);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
  var loc = gl.getAttribLocation(prog, 'p');
  gl.enableVertexAttribArray(loc);
  gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

  var U = {
    uA: gl.getUniformLocation(prog, 'uA'),
    uB: gl.getUniformLocation(prog, 'uB'),
    uProg: gl.getUniformLocation(prog, 'uProg'),
    uRA: gl.getUniformLocation(prog, 'uRA'),
    uRB: gl.getUniformLocation(prog, 'uRB'),
    uPlane: gl.getUniformLocation(prog, 'uPlane')
  };
  gl.uniform1i(U.uA, 0);
  gl.uniform1i(U.uB, 1);

  /* ---- textures (lazy, per slide) -------------------------------- */
  var texCache = {}; // index -> {tex, ratio, ready}

  function makeTex() {
    var t = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, t);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array([14, 15, 18, 255]));
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
    return t;
  }

  function ensureTex(i, cb) {
    if (texCache[i] && texCache[i].ready) { cb && cb(texCache[i]); return; }
    if (!texCache[i]) texCache[i] = { tex: makeTex(), ratio: 1.5, ready: false };
    var entry = texCache[i];

    var srcImg = imgs[i];
    var url = srcImg.currentSrc || srcImg.src;
    if (!url) { cb && cb(entry); return; }

    var loader = new Image();
    loader.decoding = 'async';
    // same-origin uploads — no crossOrigin needed; set anonymous defensively for CDNs
    try { loader.crossOrigin = 'anonymous'; } catch (e) {}
    loader.onload = function () {
      try {
        gl.bindTexture(gl.TEXTURE_2D, entry.tex);
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
        gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, false);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, loader);
        entry.ratio = (loader.naturalWidth || 3) / (loader.naturalHeight || 2);
        entry.ready = true;
      } catch (e) { entry.ready = false; }
      cb && cb(entry);
    };
    loader.onerror = function () { cb && cb(entry); };
    loader.src = url;
  }

  /* ---- sizing ---------------------------------------------------- */
  var planeAspect = 1.5;
  function resize() {
    var r = frame.getBoundingClientRect();
    if (!r.width || !r.height) return;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(r.width * dpr);
    canvas.height = Math.round(r.height * dpr);
    planeAspect = canvas.width / canvas.height;
    gl.viewport(0, 0, canvas.width, canvas.height);
    if (!transitioning) draw(curIndex, curIndex, 0);
  }

  /* ---- render ---------------------------------------------------- */
  function draw(fromI, toI, progress) {
    var A = texCache[fromI], B = texCache[toI];
    if (!A || !B) return;
    gl.activeTexture(gl.TEXTURE0); gl.bindTexture(gl.TEXTURE_2D, A.tex);
    gl.activeTexture(gl.TEXTURE1); gl.bindTexture(gl.TEXTURE_2D, B.tex);
    gl.uniform1f(U.uProg, progress);
    gl.uniform1f(U.uRA, A.ratio);
    gl.uniform1f(U.uRB, B.ratio);
    gl.uniform1f(U.uPlane, planeAspect);
    gl.drawArrays(gl.TRIANGLES, 0, 3);
  }

  /* ---- transition state machine ---------------------------------- */
  var curIndex = 0;
  var transitioning = false;
  var DURATION = 900;

  function runTransition(fromI, toI) {
    ensureTex(fromI, function () {
      ensureTex(toI, function () {
        if (!texCache[fromI].ready || !texCache[toI].ready) { curIndex = toI; return; }
        transitioning = true;
        var start = performance.now();
        (function step(now) {
          var t = Math.min(1, (now - start) / DURATION);
          draw(fromI, toI, t);
          if (t < 1) {
            requestAnimationFrame(step);
          } else {
            transitioning = false;
            curIndex = toI;
            draw(toI, toI, 0);
          }
        })(start);
      });
    });
  }

  /* ---- boot ------------------------------------------------------ */
  resize();
  if ('ResizeObserver' in window) { new ResizeObserver(resize).observe(frame); }
  else window.addEventListener('resize', resize, { passive: true });

  // Upload slide 0, draw it, THEN reveal the canvas (protects LCP).
  ensureTex(0, function () {
    if (!texCache[0] || !texCache[0].ready) { cleanup(); return; } // tainted/failed → native
    draw(0, 0, 0);
    frame.classList.add('nr-gl-on');
    // warm the next slide so the first transition is instant
    if (imgs[1]) ensureTex(1);
  });

  hero.addEventListener('nr:hero', function (e) {
    if (!frame.classList.contains('nr-gl-on')) return; // not active → native handles it
    var d = e.detail || {};
    var from = typeof d.from === 'number' ? d.from : curIndex;
    var to = typeof d.to === 'number' ? d.to : curIndex;
    if (from === to) return;
    runTransition(from, to);
  });

  // Pause work when the hero scrolls out of view.
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (ents) {
      ents.forEach(function (en) { if (en.isIntersecting && !transitioning) draw(curIndex, curIndex, 0); });
    }, { threshold: 0 }).observe(frame);
  }

  function cleanup() {
    try {
      if (canvas && canvas.parentNode) canvas.parentNode.removeChild(canvas);
      if (grad && grad.parentNode) grad.parentNode.removeChild(grad);
      frame.classList.remove('nr-gl-on');
    } catch (e) {}
  }
})();
