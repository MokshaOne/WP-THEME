/* VPG v3 · a11y.js — Cluster 19 · Barrierefreiheit & i18n.
 * Per-user preference toggles (simple mode / dyslexia), a global aria-live
 * announcer, a "?" keyboard-shortcut help overlay, and form-input survival.
 * No framework; degrades to nothing without JS (0561/0639 no-JS core stays intact).
 */
(function () {
  'use strict';
  var root = document.documentElement;
  var LS = 'vpg_a11y_prefs';

  /* ---- 0746 / 0758 · per-user preferences, remembered locally ---- */
  function readPrefs() {
    try { return JSON.parse(localStorage.getItem(LS)) || {}; } catch (e) { return {}; }
  }
  function writePrefs(p) { try { localStorage.setItem(LS, JSON.stringify(p)); } catch (e) {} }
  function applyPrefs(p) {
    root.classList.toggle('vpg-simple', !!p.simple);
    root.classList.toggle('vpg-dyslexia', !!p.dyslexia);
  }
  var prefs = readPrefs();
  applyPrefs(prefs);

  /* ---- 0729 · one global polite announcer for validation & status ---- */
  var live = document.createElement('div');
  live.className = 'vpg-sr-only';
  live.setAttribute('aria-live', 'polite');
  live.setAttribute('aria-atomic', 'true');
  document.addEventListener('DOMContentLoaded', function () { document.body.appendChild(live); });
  window.vpgAnnounce = function (msg) {
    live.textContent = '';
    setTimeout(function () { live.textContent = String(msg || ''); }, 60);
  };
  // Announce native validation failures on any form submit.
  document.addEventListener('invalid', function (e) {
    var el = e.target;
    if (el && el.validationMessage) window.vpgAnnounce(el.validationMessage);
  }, true);

  /* ---- the a11y launcher + panel ---- */
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.createElement('button');
    btn.className = 'vpg-a11y-btn';
    btn.type = 'button';
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-controls', 'vpg-a11y-panel');
    btn.title = (window.vpgA11yStr && vpgA11yStr.title) || 'Accessibility';
    btn.textContent = '♿';

    var panel = document.createElement('div');
    panel.className = 'vpg-a11y-panel';
    panel.id = 'vpg-a11y-panel';
    panel.hidden = true;
    var S = window.vpgA11yStr || {};
    panel.innerHTML =
      '<h3>' + (S.heading || 'Reading comfort') + '</h3>' +
      '<label><input type="checkbox" data-pref="simple"' + (prefs.simple ? ' checked' : '') + '> ' + (S.simple || 'Simple mode') + '</label>' +
      '<label><input type="checkbox" data-pref="dyslexia"' + (prefs.dyslexia ? ' checked' : '') + '> ' + (S.dyslexia || 'Dyslexia-friendly text') + '</label>' +
      '<p style="margin:10px 0 0"><a href="' + (S.stmtUrl || '/accessibility/') + '">' + (S.stmtLink || 'Accessibility statement') + '</a></p>';

    function toggle(open) {
      panel.hidden = !open;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) { var f = panel.querySelector('input'); if (f) f.focus(); }
    }
    btn.addEventListener('click', function () { toggle(panel.hidden); });
    panel.addEventListener('change', function (e) {
      var p = e.target.getAttribute && e.target.getAttribute('data-pref');
      if (!p) return;
      prefs[p] = e.target.checked;
      writePrefs(prefs); applyPrefs(prefs);
      window.vpgAnnounce((e.target.checked ? (S.on || 'on') : (S.off || 'off')) + ': ' + e.target.parentNode.textContent.trim());
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) { toggle(false); btn.focus(); } });

    document.body.appendChild(btn);
    document.body.appendChild(panel);
  });

  /* ---- 0741 · "?" opens a keyboard-shortcut help overlay ---- */
  document.addEventListener('DOMContentLoaded', function () {
    var S = window.vpgA11yStr || {};
    var ov = document.createElement('div');
    ov.className = 'vpg-help-ov'; ov.hidden = true; ov.setAttribute('role', 'dialog'); ov.setAttribute('aria-modal', 'true'); ov.setAttribute('aria-label', S.helpTitle || 'Keyboard shortcuts');
    var rows = [
      ['?', S.kHelp || 'Show this help'],
      ['Ctrl / ⌘ + K', S.kSearch || 'Open search palette'],
      ['Tab', S.kTab || 'Move to next control'],
      ['Esc', S.kEsc || 'Close dialogs & overlays'],
      ['← →', S.kArrows || 'Previous / next photo in the lightbox'],
      ['i', S.kInfo || 'Toggle photo info in the lightbox']
    ].map(function (r) { return '<dt><kbd>' + r[0] + '</kbd></dt><dd>' + r[1] + '</dd>'; }).join('');
    ov.innerHTML = '<div class="vpg-help-ov__box"><h2>' + (S.helpTitle || 'Keyboard shortcuts') + '</h2><dl>' + rows + '</dl>' +
      '<p style="margin:16px 0 0;text-align:right"><button type="button" class="g-btn" data-close>' + (S.close || 'Close') + '</button></p></div>';
    function open(o) { ov.hidden = !o; if (o) { var b = ov.querySelector('[data-close]'); if (b) b.focus(); } }
    ov.addEventListener('click', function (e) { if (e.target === ov || e.target.hasAttribute('data-close')) open(false); });
    document.addEventListener('keydown', function (e) {
      var t = e.target, tag = t && t.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
      if (e.key === '?' || (e.key === '/' && e.shiftKey)) { e.preventDefault(); open(ov.hidden); }
      else if (e.key === 'Escape' && !ov.hidden) open(false);
    });
    document.body.appendChild(ov);
  });

  /* ---- 0744 · form inputs survive an accidental Back / reload ---- */
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-vpg-keep]').forEach(function (form) {
      var key = 'vpg_form_' + (form.getAttribute('data-vpg-keep') || form.action || location.pathname);
      var saved; try { saved = JSON.parse(sessionStorage.getItem(key)) || {}; } catch (e) { saved = {}; }
      form.querySelectorAll('input, textarea, select').forEach(function (el) {
        if (!el.name || el.type === 'password' || el.type === 'file' || el.type === 'hidden') return;
        if (saved[el.name] != null && !el.value) el.value = saved[el.name];
        el.addEventListener('input', function () {
          var cur; try { cur = JSON.parse(sessionStorage.getItem(key)) || {}; } catch (e) { cur = {}; }
          cur[el.name] = el.value;
          try { sessionStorage.setItem(key, JSON.stringify(cur)); } catch (e) {}
        });
      });
      form.addEventListener('submit', function () { try { sessionStorage.removeItem(key); } catch (e) {} });
    });
  });
})();
