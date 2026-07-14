/**
 * Aurelius — WebGL 3D imagery, site-wide (progressive enhancement).
 *
 * Every hero photograph on the site — home, project, journal, the "next"
 * card, the about portrait, the enquiry frame — is rendered as a subdivided
 * plane in true perspective. A travelling wave plus a pointer-following
 * Gaussian bulge displace it in Z, and the plane tilts toward the cursor;
 * scroll eases the camera back for depth. The fragment stage adds a gold
 * rim-light around the pointer, edge chromatic aberration, animated film
 * grain and a vignette, matched to the theme's plate grade.
 *
 * Hard rules (never make the page worse):
 *   • DESKTOP ONLY — bails on coarse pointers and narrow viewports, so phones
 *     and tablets keep the plain, fast image (no 3D on mobile);
 *   • also bails on prefers-reduced-motion, Save-Data, low deviceMemory, or
 *     missing WebGL;
 *   • the native <img> paints the LCP; each canvas fades in only after its
 *     texture uploads, and any failure leaves the native image untouched.
 *
 * Raw WebGL, zero dependencies, procedural noise, CSP-safe.
 */
(function () {
  'use strict';

  var mq = function (q) { return window.matchMedia && window.matchMedia(q).matches; };
  if (mq('(prefers-reduced-motion: reduce)')) return;
  if (mq('(max-width: 900px)') || mq('(pointer: coarse)')) return;   // ← no 3D on mobile
  if (!window.WebGLRenderingContext) return;
  try {
    var conn = navigator.connection || {};
    if (conn.saveData) return;
    if (typeof navigator.deviceMemory === 'number' && navigator.deviceMemory < 4) return;
  } catch (e) {}

  /* ---- shared matrix helpers (mat4, column-major) --------------- */
  function mul(a, b) {
    var o = new Float32Array(16);
    for (var r = 0; r < 4; r++) for (var c = 0; c < 4; c++) {
      o[c * 4 + r] = a[0 * 4 + r] * b[c * 4 + 0] + a[1 * 4 + r] * b[c * 4 + 1] + a[2 * 4 + r] * b[c * 4 + 2] + a[3 * 4 + r] * b[c * 4 + 3];
    }
    return o;
  }
  function perspective(fovy, aspect, near, far) {
    var f = 1 / Math.tan(fovy / 2), nf = 1 / (near - far);
    return new Float32Array([f / aspect, 0, 0, 0, 0, f, 0, 0, 0, 0, (far + near) * nf, -1, 0, 0, 2 * far * near * nf, 0]);
  }
  function translate(x, y, z) { return new Float32Array([1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, x, y, z, 1]); }
  function rotX(a) { var c = Math.cos(a), s = Math.sin(a); return new Float32Array([1, 0, 0, 0, 0, c, s, 0, 0, -s, c, 0, 0, 0, 0, 1]); }
  function rotY(a) { var c = Math.cos(a), s = Math.sin(a); return new Float32Array([c, 0, -s, 0, 0, 1, 0, 0, s, 0, c, 0, 0, 0, 0, 1]); }
  function scale(x, y, z) { return new Float32Array([x, 0, 0, 0, 0, y, 0, 0, 0, 0, z, 0, 0, 0, 0, 1]); }

  var VERT = [
    'attribute vec2 aPos;attribute vec2 aUv;',
    'uniform mat4 uMVP;uniform float uTime;uniform vec2 uMouse;uniform float uAmp;uniform float uBulge;',
    'varying vec2 vUv;varying float vZ;varying float vRim;',
    'void main(){',
    '  vUv=aUv;',
    '  float wave=sin(aPos.x*3.0+uTime*0.6)*0.5+cos(aPos.y*2.4-uTime*0.5)*0.5;',
    '  float d=distance(aPos,uMouse);',
    '  float bulge=exp(-d*d*2.2)*uBulge;',
    '  float z=wave*uAmp + bulge;',
    '  vZ=z; vRim=exp(-d*d*3.0);',
    '  gl_Position=uMVP*vec4(aPos.x,aPos.y,z,1.0);',
    '}'
  ].join('\n');

  var FRAG = [
    'precision highp float;',
    'varying vec2 vUv;varying float vZ;varying float vRim;',
    'uniform sampler2D uTex;uniform float uTime;uniform float uRatio;uniform float uPlane;',
    'float hash(vec2 p){return fract(sin(dot(p,vec2(127.1,311.7)))*43758.5453);}',
    'vec2 coverUv(vec2 uvv){float a=uRatio/uPlane;vec2 s=a>1.0?vec2(1.0/a,1.0):vec2(1.0,a);return (uvv-0.5)*s+0.5;}',
    'vec3 sampleCA(vec2 uvv,float amt){',
    '  float r=texture2D(uTex,uvv+vec2(amt,0.0)).r;',
    '  float g=texture2D(uTex,uvv).g;',
    '  float b=texture2D(uTex,uvv-vec2(amt,0.0)).b;',
    '  return vec3(r,g,b);',
    '}',
    'void main(){',
    '  vec2 uvv=coverUv(vUv);',
    '  float edge=distance(vUv,vec2(0.5))*2.0;',
    '  float ca=0.004+edge*0.006;',
    '  vec3 col=sampleCA(uvv,ca);',
    '  col=(col-0.5)*1.05+0.5;',
    '  col=mix(vec3(dot(col,vec3(0.299,0.587,0.114))),col,0.96);',
    '  col*=0.9;',
    '  col*=1.0+vZ*0.35;',
    '  vec3 gold=vec3(0.86,0.78,0.41);',
    '  col+=gold*vRim*0.18;',
    '  float g=hash(vUv*vec2(920.0,540.0)+fract(uTime)*97.0)-0.5;',
    '  col+=g*0.045;',
    '  col*=1.0-edge*edge*0.28;',
    '  gl_FragColor=vec4(col,1.0);',
    '}'
  ].join('\n');

  var FOV = 42 * Math.PI / 180, CAM = 2.6;

  /* ---- one instance per hero photograph ------------------------- */
  function mount(media, img, opts) {
    var src = img && (img.currentSrc || img.src);
    if (!src) return;
    opts = opts || {};
    var amp = opts.amp || 0.05, bulge = opts.bulge || 0.14, tilt = opts.tilt || 1;

    // position the canvas correctly even inside statically-positioned frames
    var cs = window.getComputedStyle(media);
    if (cs.position === 'static') { media.style.position = 'relative'; media.style.overflow = 'hidden'; }

    var canvas = document.createElement('canvas');
    canvas.className = 'st-gl'; canvas.setAttribute('aria-hidden', 'true');
    var gl = canvas.getContext('webgl', { antialias: true, alpha: false, premultipliedAlpha: false, powerPreference: 'high-performance' })
          || canvas.getContext('experimental-webgl');
    if (!gl) return;
    media.insertBefore(canvas, media.firstChild);

    var COLS = 60, ROWS = 40, pos = [], uv = [], idx = [];
    for (var y = 0; y <= ROWS; y++) for (var x = 0; x <= COLS; x++) { pos.push((x / COLS) * 2 - 1, (y / ROWS) * 2 - 1); uv.push(x / COLS, y / ROWS); }
    var Wv = COLS + 1;
    for (var yy = 0; yy < ROWS; yy++) for (var xx = 0; xx < COLS; xx++) { var a = yy * Wv + xx, b = a + 1, c = a + Wv, d = c + 1; idx.push(a, b, c, b, d, c); }

    function compile(t, s) { var sh = gl.createShader(t); gl.shaderSource(sh, s); gl.compileShader(sh); return gl.getShaderParameter(sh, gl.COMPILE_STATUS) ? sh : null; }
    var vs = compile(gl.VERTEX_SHADER, VERT), fs = compile(gl.FRAGMENT_SHADER, FRAG);
    if (!vs || !fs) return cleanup();
    var prog = gl.createProgram(); gl.attachShader(prog, vs); gl.attachShader(prog, fs); gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return cleanup();
    gl.useProgram(prog);

    function buffer(data, attr, size) {
      var bf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, bf);
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(data), gl.STATIC_DRAW);
      var l = gl.getAttribLocation(prog, attr); gl.enableVertexAttribArray(l); gl.vertexAttribPointer(l, size, gl.FLOAT, false, 0, 0);
    }
    buffer(pos, 'aPos', 2); buffer(uv, 'aUv', 2);
    var ibo = gl.createBuffer(); gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, ibo);
    gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(idx), gl.STATIC_DRAW);

    var U = {};
    ['uMVP', 'uTime', 'uMouse', 'uAmp', 'uBulge', 'uTex', 'uRatio', 'uPlane'].forEach(function (n) { U[n] = gl.getUniformLocation(prog, n); });
    gl.uniform1i(U.uTex, 0);

    var planeAspect = 1.6, imgRatio = 1.6;
    function mvp(tiltX, tiltY, camZ) {
      var halfH = Math.tan(FOV / 2) * CAM * 1.14, halfW = halfH * planeAspect;
      var model = mul(mul(rotX(tiltX), rotY(tiltY)), scale(halfW, halfH, halfH));
      return mul(perspective(FOV, planeAspect, 0.1, 20), mul(translate(0, 0, -camZ), model));
    }

    var tex = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, tex);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, 1, 1, 0, gl.RGBA, gl.UNSIGNED_BYTE, new Uint8Array([20, 18, 14, 255]));
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);

    var ready = false;
    var loader = new Image(); loader.decoding = 'async';
    try { loader.crossOrigin = 'anonymous'; } catch (e) {}
    loader.onload = function () {
      try {
        gl.bindTexture(gl.TEXTURE_2D, tex);
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, loader);
        imgRatio = (loader.naturalWidth || 3) / (loader.naturalHeight || 2);
        ready = true;
        media.classList.add('st-gl-on');
        img.style.transition = 'opacity .7s ease'; img.style.opacity = '0';
      } catch (e) { cleanup(); }
    };
    loader.onerror = function () { cleanup(); };
    loader.src = src;

    function resize() {
      var r = media.getBoundingClientRect();
      if (!r.width || !r.height) return;
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(r.width * dpr); canvas.height = Math.round(r.height * dpr);
      planeAspect = canvas.width / canvas.height;
      gl.viewport(0, 0, canvas.width, canvas.height);
    }
    resize();
    if ('ResizeObserver' in window) new ResizeObserver(resize).observe(media);
    else window.addEventListener('resize', resize, { passive: true });

    var mx = 0, my = 0, tmx = 0, tmy = 0, inView = true;
    window.addEventListener('mousemove', function (e) {
      var r = media.getBoundingClientRect();
      tmx = ((e.clientX - r.left) / r.width) * 2 - 1;
      tmy = -(((e.clientY - r.top) / r.height) * 2 - 1);
    }, { passive: true });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (ents) { ents.forEach(function (en) { inView = en.isIntersecting; }); }, { threshold: 0 }).observe(media);
    }

    var t0 = performance.now(), raf = 0;
    function frame(now) {
      raf = requestAnimationFrame(frame);
      if (!ready || !inView) return;
      var t = (now - t0) / 1000;
      mx += (tmx - mx) * 0.06; my += (tmy - my) * 0.06;
      var scrollK = Math.min((window.scrollY || 0) / (window.innerHeight || 1), 1);
      gl.uniform1f(U.uTime, t);
      gl.uniform2f(U.uMouse, mx * 0.9, my * 0.9);
      gl.uniform1f(U.uAmp, amp); gl.uniform1f(U.uBulge, bulge);
      gl.uniform1f(U.uRatio, imgRatio); gl.uniform1f(U.uPlane, planeAspect);
      gl.uniformMatrix4fv(U.uMVP, false, mvp(my * 0.10 * tilt - scrollK * 0.06, mx * 0.12 * tilt, CAM + scrollK * 0.9));
      gl.bindTexture(gl.TEXTURE_2D, tex);
      gl.drawElements(gl.TRIANGLES, idx.length, gl.UNSIGNED_SHORT, 0);
    }
    raf = requestAnimationFrame(frame);

    function cleanup() {
      try {
        if (raf) cancelAnimationFrame(raf);
        if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
        media.classList.remove('st-gl-on');
        if (img) img.style.opacity = '';
        var ext = gl && gl.getExtension('WEBGL_lose_context'); if (ext) ext.loseContext();
      } catch (e) {}
    }
  }

  /* ---- mount on every hero photograph on the page --------------- */
  var TARGETS = [
    ['.st-hero__media',     '.st-hero__img',          { amp: 0.05,  bulge: 0.14, tilt: 1 }],
    ['.st-project__hero',   '.st-project__hero-img',  { amp: 0.045, bulge: 0.12, tilt: 1 }],
    ['.st-jpost__hero',     '.st-jpost__hero-img',    { amp: 0.045, bulge: 0.12, tilt: 1 }],
    ['.st-next__media',     '.st-next__img',          { amp: 0.04,  bulge: 0.10, tilt: 0.8 }],
    ['.st-about__portrait', '.st-about__portrait-img',{ amp: 0.03,  bulge: 0.09, tilt: 0.7 }],
    ['.st-enquire__frame',  'img',                    { amp: 0.03,  bulge: 0.09, tilt: 0.7 }]
  ];
  function boot() {
    TARGETS.forEach(function (t) {
      [].slice.call(document.querySelectorAll(t[0])).forEach(function (media) {
        var img = media.querySelector(t[1]);
        if (img) mount(media, img, t[2]);
      });
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
