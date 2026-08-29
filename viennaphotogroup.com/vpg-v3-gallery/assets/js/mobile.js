/* VPG v3 · mobile.js — Cluster 16 · Mobile & PWA behaviours.
 *   0608 badge · 0609 pull-to-refresh · 0613 dark auto · 0614 data-saver
 *   0615 offline banner · 0617 vibration · 0618 upload progress · 0623 install
 *   0627 QR scanner · 0631 battery saver · 0640 one-tap error report
 */
(function () {
  'use strict';
  var mob = matchMedia('(max-width: 640px)').matches;

  /* 0615 · honest offline banner */
  var bar = document.createElement('div');
  bar.style.cssText = 'position:fixed;left:0;right:0;top:0;z-index:1000;background:#996800;color:#fff;text-align:center;padding:8px;font:700 12px/1 sans-serif;transform:translateY(-100%);transition:transform .3s';
  bar.textContent = 'Offline — showing what’s saved.';
  document.body.appendChild(bar);
  function net() { bar.style.transform = navigator.onLine ? 'translateY(-100%)' : 'translateY(0)'; }
  addEventListener('online', net); addEventListener('offline', net); net();

  /* 0614 · data-saver — swap to smaller images on slow / save-data */
  try {
    var c = navigator.connection;
    if (c && (c.saveData || /2g/.test(c.effectiveType || ''))) {
      document.documentElement.classList.add('vpg-datasaver');
      document.querySelectorAll('img[srcset]').forEach(function (i) { i.removeAttribute('srcset'); i.removeAttribute('sizes'); });
    }
  } catch (e) {}

  /* 0631 · battery saver — throttle animations on low battery */
  if (navigator.getBattery) navigator.getBattery().then(function (b) {
    function chk() { document.documentElement.classList.toggle('vpg-lowpower', b.level < 0.2 && !b.charging); }
    b.addEventListener('levelchange', chk); b.addEventListener('chargingchange', chk); chk();
  });

  /* 0609 · pull-to-refresh on list views */
  if (mob) (function () {
    var sy = 0, pulling = false, ind = document.createElement('div');
    ind.style.cssText = 'position:fixed;top:0;left:50%;transform:translateX(-50%);z-index:999;font:700 12px/1 sans-serif;color:#E5341F;padding:8px;opacity:0;transition:opacity .2s';
    ind.textContent = '↻';
    document.body.appendChild(ind);
    addEventListener('touchstart', function (e) { if (scrollY === 0) sy = e.touches[0].clientY; }, { passive: true });
    addEventListener('touchmove', function (e) { if (sy && scrollY === 0) { var d = e.touches[0].clientY - sy; if (d > 60) { pulling = true; ind.style.opacity = '1'; } } }, { passive: true });
    addEventListener('touchend', function () { if (pulling) location.reload(); sy = 0; pulling = false; ind.style.opacity = '0'; });
  })();

  /* 0617 · haptic on check-in / vote buttons */
  document.addEventListener('click', function (e) {
    var b = e.target.closest && e.target.closest('[data-haptic], .vpg-checkin, button[name]');
    if (b && navigator.vibrate) navigator.vibrate(12);
  }, true);

  /* 0608 · app-icon badge from unread notifications count */
  var badge = document.querySelector('[data-unread]');
  if (badge && navigator.setAppBadge) {
    var n = parseInt(badge.getAttribute('data-unread'), 10) || 0;
    if (n > 0) navigator.setAppBadge(n).catch(function () {}); else navigator.clearAppBadge && navigator.clearAppBadge();
  }

  /* 0623 · discreet install prompt — offered once */
  var deferred = null;
  addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault(); deferred = e;
    try { if (localStorage.getItem('vpg_install_seen')) return; } catch (x) {}
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;left:16px;right:16px;bottom:16px;z-index:1000;background:#0B0B0B;color:#fff;padding:14px 16px;display:flex;gap:12px;align-items:center;font:14px sans-serif';
    t.innerHTML = '<span style="flex:1">Add Vienna Photo Group to your home screen?</span><button style="background:#E5341F;color:#fff;border:0;padding:8px 14px;font-weight:700">Add</button><button aria-label="close" style="background:none;border:0;color:#aaa;font-size:20px">×</button>';
    document.body.appendChild(t);
    t.querySelector('button').addEventListener('click', function () { deferred.prompt(); t.remove(); try { localStorage.setItem('vpg_install_seen', '1'); } catch (x) {} });
    t.querySelectorAll('button')[1].addEventListener('click', function () { t.remove(); try { localStorage.setItem('vpg_install_seen', '1'); } catch (x) {} });
  });

  /* 0618 · real upload progress on submit forms */
  document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!form.querySelector('input[type=file]') || form.dataset.xhr) return;
      var file = form.querySelector('input[type=file]');
      if (!file.files || !file.files.length) return;
      e.preventDefault(); form.dataset.xhr = '1';
      var xhr = new XMLHttpRequest(), fd = new FormData(form);
      var pb = document.createElement('div');
      pb.style.cssText = 'height:6px;background:#E6E5E1;margin:10px 0';
      pb.innerHTML = '<div style="height:6px;width:0;background:#E5341F;transition:width .2s"></div>';
      form.appendChild(pb);
      xhr.upload.addEventListener('progress', function (ev) { if (ev.lengthComputable) pb.firstChild.style.width = Math.round(ev.loaded / ev.total * 100) + '%'; });
      xhr.addEventListener('load', function () { document.open(); document.write(xhr.responseText); document.close(); });
      xhr.addEventListener('error', function () { form.dataset.xhr = ''; form.submit(); });
      xhr.open(form.method || 'POST', form.action); xhr.send(fd);
    });
  });

  /* 0627 · QR scanner (BarcodeDetector) opens a pin */
  var qb = document.getElementById('vpg-qr-scan');
  if (qb && 'BarcodeDetector' in window) qb.addEventListener('click', function () {
    var v = document.createElement('video'); v.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;object-fit:cover;z-index:1000;background:#000';
    document.body.appendChild(v);
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (s) {
      v.srcObject = s; v.play();
      var det = new window.BarcodeDetector({ formats: ['qr_code'] });
      var iv = setInterval(function () {
        det.detect(v).then(function (codes) { if (codes.length) { clearInterval(iv); s.getTracks().forEach(function (t) { t.stop(); }); v.remove(); location.href = codes[0].rawValue; } }).catch(function () {});
      }, 500);
      v.addEventListener('click', function () { clearInterval(iv); s.getTracks().forEach(function (t) { t.stop(); }); v.remove(); });
    }).catch(function () { v.remove(); });
  });

  /* 0640 · one-tap error report on a broken view */
  var er = document.getElementById('vpg-report-view');
  if (er) er.addEventListener('click', function () {
    var fd = new FormData(); fd.append('action', 'vpg_view_report'); fd.append('url', location.href); fd.append('ua', navigator.userAgent);
    fetch(window.vpgAjax || '/wp-admin/admin-ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    er.textContent = '✓ Thanks — reported.';
  });
})();
