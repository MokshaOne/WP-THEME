/* VPG · command palette (0580) doubling as live search (0561).
 * Open with ⌘K / Ctrl+K or the 🔍 header button; type to search the
 * whole site via the vpg_live_search endpoint; arrows + Enter to go. */
(function () {
  'use strict';
  var cfg = window.vpgPalette || {};
  if (!cfg.ajax) return;

  var overlay = document.createElement('div');
  overlay.id = 'vpg-cmdk';
  overlay.hidden = true;
  overlay.innerHTML =
    '<div class="vpg-cmdk__box" role="dialog" aria-modal="true" aria-label="' + (cfg.i18nSearch || 'Search') + '">' +
    '<input type="search" id="vpg-cmdk-in" placeholder="' + (cfg.i18nPlaceholder || 'Search the site…') + '" autocomplete="off">' +
    '<div id="vpg-cmdk-list" role="listbox"></div>' +
    '<p class="vpg-cmdk__hint">↑↓ · Enter · Esc</p></div>';
  document.body.appendChild(overlay);

  var input = overlay.querySelector('#vpg-cmdk-in');
  var list = overlay.querySelector('#vpg-cmdk-list');
  var items = [];
  var active = -1;
  var timer = null;

  var statics = (cfg.links || []).map(function (l) {
    return { title: l[0], type: cfg.i18nGoto || 'Go to', url: l[1] };
  });

  function render() {
    list.innerHTML = items.map(function (it, i) {
      return '<a href="' + it.url + '" class="vpg-cmdk__item' + (i === active ? ' is-active' : '') + '" role="option">' +
        '<span>' + it.title.replace(/[<>&]/g, function (c) { return { '<': '&lt;', '>': '&gt;', '&': '&amp;' }[c]; }) + '</span>' +
        '<em>' + (it.type || '') + '</em></a>';
    }).join('');
  }

  function open() {
    overlay.hidden = false;
    items = statics.slice();
    active = -1;
    render();
    input.value = '';
    setTimeout(function () { input.focus(); }, 30);
    document.body.style.overflow = 'hidden';
  }
  function close() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  function search(q) {
    fetch(cfg.ajax + '?action=vpg_live_search&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (input.value.trim() !== q) return; // stale response
        items = (res && res.success && res.data.length) ? res.data : [];
        if (!items.length) items = [{ title: (cfg.i18nAll || 'Full search for') + ' “' + q + '”', type: '→', url: cfg.home + '?s=' + encodeURIComponent(q) }];
        active = 0;
        render();
      });
  }

  input.addEventListener('input', function () {
    var q = input.value.trim();
    clearTimeout(timer);
    if (q.length < 2) { items = statics.slice(); active = -1; render(); return; }
    timer = setTimeout(function () { search(q); }, 220);
  });

  document.addEventListener('keydown', function (e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); overlay.hidden ? open() : close(); return; }
    if (overlay.hidden) return;
    if (e.key === 'Escape') { close(); }
    if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(active + 1, items.length - 1); render(); }
    if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(active - 1, 0); render(); }
    if (e.key === 'Enter' && items[active]) { e.preventDefault(); window.location.href = items[active].url; }
  });
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

  var trigger = document.getElementById('vpg-cmdk-open');
  if (trigger) trigger.addEventListener('click', open);
})();
