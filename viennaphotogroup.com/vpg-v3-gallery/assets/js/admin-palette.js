/* VPG v3 · admin-palette.js — Cluster 22 · wp-admin command palette (0861/0862).
 * Cmd/Ctrl-K opens a search over admin pages, posts (any status) and members. */
(function () {
  'use strict';
  var cfg = window.vpgAdmCmd || {};
  var pages = cfg.pages || [];
  var box, input, list, sel = 0, rows = [];

  function build() {
    box = document.createElement('div');
    box.id = 'vpg-adm-cmdk';
    box.innerHTML = '<div class="box"><input type="text" placeholder="' + (cfg.placeholder || 'Search the workbench…') + '" aria-label="Command palette"><ul></ul></div>';
    document.body.appendChild(box);
    input = box.querySelector('input');
    list = box.querySelector('ul');
    box.addEventListener('click', function (e) { if (e.target === box) close(); });
    input.addEventListener('input', debounce(run, 160));
    input.addEventListener('keydown', nav);
    render(pages.map(function (p) { return { label: p.label, url: p.url, k: 'page' }; }));
  }
  function open() { box.classList.add('open'); input.value = ''; input.focus(); render(pages.map(function (p) { return { label: p.label, url: p.url, k: 'page' }; })); }
  function close() { box.classList.remove('open'); }
  function debounce(fn, ms) { var t; return function () { clearTimeout(t); t = setTimeout(fn, ms); }; }

  function run() {
    var q = input.value.trim().toLowerCase();
    var local = pages.filter(function (p) { return p.label.toLowerCase().indexOf(q) >= 0; }).map(function (p) { return { label: p.label, url: p.url, k: 'page' }; });
    if (q.length < 2) { render(local); return; }
    var body = new URLSearchParams({ action: 'vpg_admin_search', q: q, _n: cfg.nonce });
    fetch(cfg.ajax, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (res) { render(local.concat((res && res.data) || [])); })
      .catch(function () { render(local); });
  }
  function render(items) {
    rows = items.slice(0, 40); sel = 0;
    list.innerHTML = rows.map(function (r, i) {
      return '<li data-i="' + i + '" class="' + (i === 0 ? 'sel' : '') + '"><span>' + esc(r.label) + '</span><span class="k">' + esc(r.k || '') + '</span></li>';
    }).join('');
    Array.prototype.forEach.call(list.children, function (li) {
      li.addEventListener('click', function () { go(rows[+li.dataset.i]); });
    });
  }
  function nav(e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
    else if (e.key === 'Enter') { e.preventDefault(); if (rows[sel]) go(rows[sel]); }
    else if (e.key === 'Escape') { close(); }
  }
  function move(d) {
    var lis = list.children; if (!lis.length) return;
    lis[sel] && lis[sel].classList.remove('sel');
    sel = (sel + d + lis.length) % lis.length;
    lis[sel].classList.add('sel'); lis[sel].scrollIntoView({ block: 'nearest' });
  }
  function go(r) { if (r && r.url) window.location.href = r.url; }
  function esc(s) { return String(s || '').replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      if (!box) build();
      box.classList.contains('open') ? close() : open();
    }
  });
})();
