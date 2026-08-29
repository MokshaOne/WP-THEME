/* VPG v3 · map-engine.js
 * Initialises Leaflet on every #vpg-map / .vpg-map element.
 *
 * Per-pin data shape:
 *   { lat, lng, title, url, lede, type }
 *     type · location | studio | shop | event | review | tutorial | magazine | member
 *            controls the marker colour & glyph (see CSS .vpg-pin--<type>)
 *
 * Behaviour:
 *   - Lazy init · the map (and its tile requests) only boots when the element
 *     approaches the viewport (IntersectionObserver, 400px margin)
 *   - Custom teardrop divIcon per type · colour from token palette
 *   - Clusters when count > 4 (and Leaflet.markercluster is loaded)
 *   - Optional filter bar · any .vpg-map-filter[data-target="#vpg-map"] in DOM
 *     toggles pin types on/off
 *   - Filter state lives in the URL (?types=studio,shop) · shareable map views;
 *     restored on load, written with history.replaceState (no reload)
 *   - fitBounds with 18% padding and maxZoom 16 so single pins don't slam in
 *   - Wheel-zoom only after first click (prevents page-scroll hijack)
 */
(function () {
  'use strict';
  if (typeof L === 'undefined') return;

  /* Stroke SVG icons per pin type · 14px grid, drawn in currentColor */
  var ICON_SVG = {
    location: '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="7" cy="7" r="2.2"/><circle cx="7" cy="7" r="5.4"/></svg>',
    studio:   '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="4" width="10" height="7"/><circle cx="7" cy="7.5" r="2"/><path d="M5 4l1-1.5h2L9 4"/></svg>',
    shop:     '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M2.5 5.5h9l-.8 6h-7.4z"/><path d="M5 5.5V4a2 2 0 014 0v1.5"/></svg>',
    event:    '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="2" y="3" width="10" height="9"/><path d="M2 6h10M5 1.5V4M9 1.5V4"/></svg>',
    review:   '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M7 1.8l1.6 3.3 3.6.5-2.6 2.5.6 3.6L7 10l-3.2 1.7.6-3.6L1.8 5.6l3.6-.5z"/></svg>',
    tutorial: '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M2 3.5h4a2 2 0 012 2v6a2 2 0 00-2-1.5H2zM12 3.5H8a2 2 0 00-2 2v6a2 2 0 012-1.5h4z"/></svg>',
    magazine: '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="2" width="8" height="10"/><path d="M5 5h4M5 7.5h4"/></svg>',
    member:   '<svg viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.4"><circle cx="7" cy="5" r="2.4"/><path d="M2.5 12a4.5 4.5 0 019 0"/></svg>'
  };

  function makeIcon(type) {
    return L.divIcon({
      className: 'vpg-pin vpg-pin--' + (type || 'location'),
      html: '<div class="vpg-pin__inner"><span class="vpg-pin__glyph" style="display:flex;width:14px;height:14px">' + (ICON_SVG[type] || ICON_SVG.location) + '</span></div>',
      iconSize:   [32, 42],
      iconAnchor: [16, 42],
      popupAnchor: [0, -38]
    });
  }

  /* Gallery-styled popup · photo, wall-label meta, open-now badge */
  function popupHtml(p) {
    var f = "'Archivo','Helvetica Neue',Arial,sans-serif";
    var html = '';
    if (p.img) {
      html += '<div style="margin:-14px -20px 10px;height:110px;overflow:hidden"><img src="' + p.img + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block"></div>';
    }
    html += '<strong style="font-family:' + f + ';font-weight:800;font-size:15px;letter-spacing:-.01em;text-transform:uppercase;display:block;margin-bottom:.35rem;color:#0B0B0B">' + (p.title || '') + '</strong>';
    var meta = [];
    if (p.type)     meta.push(p.type);
    if (p.district) meta.push(p.district);
    if (p.best)     meta.push('best: ' + p.best);
    if (p.open === true)  meta.push('<span style="color:#1A7A3C;font-weight:700">open now</span>');
    if (p.open === false) meta.push('<span style="color:#E5341F;font-weight:700">closed</span>');
    if (meta.length) {
      html += '<span style="font-family:' + f + ';font-size:10px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#6A6A6A;display:block;margin-bottom:.35rem">' + meta.join(' · ') + '</span>';
    }
    if (p.lede) html += '<span style="font-family:' + f + ';color:#2C2C2C;font-size:13px;line-height:1.5;display:block;margin-bottom:.5rem">' + p.lede + '</span>';
    if (p.url)  html += '<a href="' + p.url + '" style="font-family:' + f + ';font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#0B0B0B;border-bottom:2px solid #E5341F;text-decoration:none">View →</a>';
    return html;
  }

  function clusterIcon(cluster) {
    var n = cluster.getChildCount();
    var size = n < 10 ? 36 : (n < 50 ? 44 : 52);
    return L.divIcon({
      html: '<span>' + n + '</span>',
      className: 'vpg-cluster',
      iconSize: L.point(size, size)
    });
  }

  /* ── URL filter state · ?types=a,b,c ── */
  function readUrlTypes() {
    try {
      var v = new URLSearchParams(window.location.search).get('types');
      if (!v) return null;
      var list = v.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
      return list.length ? list : null;
    } catch (e) { return null; }
  }

  function writeUrlTypes(active, allCount) {
    try {
      var params = new URLSearchParams(window.location.search);
      if (active.size === allCount) params.delete('types');
      else params.set('types', Array.from(active).join(','));
      var qs = params.toString();
      history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : '') + window.location.hash);
    } catch (e) { /* URL stays as-is · filtering still works */ }
  }

  function initMap(el) {
    el.classList.add('is-ready'); // clears the CSS loading skeleton
    var raw = el.getAttribute('data-pins') || '[]';
    var pins;
    try { pins = JSON.parse(raw); } catch (e) { pins = []; }

    var map = L.map(el, { scrollWheelZoom: false, zoomControl: true, maxZoom: 18 });

    // Keyless OSM tiles; the museum look comes from a grayscale filter on
    // the tile pane (gallery.css), so markers and popups keep their red.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    if (!pins.length) {
      map.setView([48.2082, 16.3738], 12);
      return;
    }

    // Track markers per type so the filter bar can toggle them
    var byType = {};

    var useCluster = (typeof L.markerClusterGroup === 'function') && pins.length > 4;
    var rootGroup = useCluster
      ? L.markerClusterGroup({
          showCoverageOnHover: false,
          spiderfyOnMaxZoom: true,
          maxClusterRadius: 48,
          disableClusteringAtZoom: 17,
          iconCreateFunction: clusterIcon
        })
      : L.featureGroup();

    var byId = {};
    pins.forEach(function (p) {
      if (typeof p.lat !== 'number' || typeof p.lng !== 'number') return;
      var t = p.type || 'location';
      var marker = L.marker([p.lat, p.lng], { icon: makeIcon(t), title: p.title || '' });
      marker.bindPopup(popupHtml(p), { closeButton: true, autoPan: true });
      marker._vpgType = t;
      (byType[t] = byType[t] || []).push(marker);
      if (p.id) byId[p.id] = marker;
      rootGroup.addLayer(marker);
    });

    map.addLayer(rootGroup);

    // Deep link · /locations/#pin-123 centers the map and opens that popup
    var pinMatch = (window.location.hash || '').match(/^#pin-(\d+)$/);
    if (pinMatch && byId[pinMatch[1]]) {
      var target = byId[pinMatch[1]];
      setTimeout(function () {
        map.setView(target.getLatLng(), 16);
        if (rootGroup.zoomToShowLayer) rootGroup.zoomToShowLayer(target, function () { target.openPopup(); });
        else target.openPopup();
      }, 250);
    }

    try {
      var bounds = rootGroup.getBounds();
      if (bounds.isValid()) map.fitBounds(bounds.pad(0.18), { maxZoom: 16 });
    } catch (e) {
      map.setView([48.2082, 16.3738], 12);
    }

    el.addEventListener('click', function () { map.scrollWheelZoom.enable(); }, { once: true });

    // Optional · expose total count via data-count for the corner chip
    el.setAttribute('data-count', pins.length + ' pinned');

    addMapControls(el, map);

    // ── Filter-bar wiring ──
    // Looks for siblings or doc-wide `.vpg-map-filter[data-target="#vpg-map"]` and
    // any buttons with [data-type="<type>"] or [data-type="all"]. Click toggles.
    var selector = el.id ? '#' + el.id : '.vpg-map';
    var filters  = document.querySelectorAll('.vpg-map-filter[data-target="' + selector + '"], .vpg-map-filter[data-target="' + el.id + '"]');
    if (!filters.length) return;

    var allTypes = Object.keys(byType);
    var active   = new Set(allTypes);

    // Restore a shared view · keep only URL types that exist on this map
    var fromUrl = readUrlTypes();
    if (fromUrl) {
      var valid = fromUrl.filter(function (t) { return allTypes.indexOf(t) !== -1; });
      if (valid.length) active = new Set(valid);
    }

    function paintButtons(bar) {
      bar.querySelectorAll('button[data-type]').forEach(function (b) {
        var t = b.getAttribute('data-type');
        b.classList.toggle('is-active', t === 'all' ? active.size === allTypes.length : active.has(t));
      });
    }

    function applyFilter() {
      rootGroup.clearLayers();
      allTypes.forEach(function (t) {
        if (!active.has(t)) return;
        byType[t].forEach(function (m) { rootGroup.addLayer(m); });
      });
      filters.forEach(paintButtons);
      writeUrlTypes(active, allTypes.length);
    }

    filters.forEach(function (bar) {
      bar.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-type]');
        if (!btn) return;
        var type = btn.getAttribute('data-type');
        if (type === 'all') {
          active = new Set(allTypes);
        } else if (active.has(type) && active.size > 1) {
          active.delete(type);
        } else {
          active.add(type);
        }
        applyFilter();
      });
    });

    // Initial state · applies a restored URL filter and paints the buttons
    applyFilter();
  }

  /* ── Corner controls · fullscreen + "near me" ── */
  var controlCssDone = false;
  function ensureControlCss() {
    if (controlCssDone) return;
    controlCssDone = true;
    var s = document.createElement('style');
    s.textContent =
      '.vpg-map-ctl{position:absolute;top:10px;right:10px;z-index:800;display:flex;flex-direction:column;gap:6px}' +
      '.vpg-map-ctl button{width:38px;height:38px;border:1px solid #0B0B0B;background:#fff;color:#0B0B0B;' +
      'font-size:16px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}' +
      '.vpg-map-ctl button:hover{background:#E5341F;border-color:#E5341F;color:#fff}' +
      '.vpg-map--fs{position:fixed!important;inset:0!important;width:auto!important;height:auto!important;z-index:9999;margin:0!important}';
    document.head.appendChild(s);
  }

  function addMapControls(el, map) {
    ensureControlCss();
    var box = document.createElement('div');
    box.className = 'vpg-map-ctl';

    // Fullscreen toggle · native API with a fixed-position fallback
    var fs = document.createElement('button');
    fs.type = 'button';
    fs.title = 'Fullscreen';
    fs.setAttribute('aria-label', 'Toggle fullscreen map');
    fs.textContent = '⤢';
    fs.addEventListener('click', function () {
      if (document.fullscreenElement === el) {
        document.exitFullscreen();
      } else if (el.requestFullscreen) {
        el.requestFullscreen().catch(function () { el.classList.toggle('vpg-map--fs'); resize(); });
      } else {
        el.classList.toggle('vpg-map--fs');
        resize();
      }
    });
    function resize() { setTimeout(function () { map.invalidateSize(); }, 60); }
    document.addEventListener('fullscreenchange', resize);

    // Near me · pan to the visitor's position, draw an accuracy circle
    if (navigator.geolocation) {
      var loc = document.createElement('button');
      loc.type = 'button';
      loc.title = 'Near me';
      loc.setAttribute('aria-label', 'Show my position');
      loc.textContent = '◎';
      var youLayer = null;
      loc.addEventListener('click', function () {
        loc.textContent = '…';
        navigator.geolocation.getCurrentPosition(function (pos) {
          loc.textContent = '◎';
          var ll = [pos.coords.latitude, pos.coords.longitude];
          if (youLayer) map.removeLayer(youLayer);
          youLayer = L.layerGroup([
            L.circle(ll, { radius: Math.min(pos.coords.accuracy || 50, 300), color: '#E5341F', weight: 1, fillOpacity: 0.08 }),
            L.circleMarker(ll, { radius: 7, color: '#fff', weight: 2, fillColor: '#E5341F', fillOpacity: 1 })
          ]).addTo(map);
          map.setView(ll, Math.max(map.getZoom(), 14));
        }, function () {
          loc.textContent = '◎';
          window.alert('Position not available.');
        }, { enableHighAccuracy: true, timeout: 8000 });
      });
      box.appendChild(loc);
    }

    box.appendChild(fs);
    el.appendChild(box);
  }

  /* ── Lazy boot · init each map when it approaches the viewport ── */
  var maps = document.querySelectorAll('#vpg-map, .vpg-map');
  if (!maps.length) return;

  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        io.unobserve(entry.target);
        initMap(entry.target);
      });
    }, { rootMargin: '400px 0px' });
    maps.forEach(function (el) { io.observe(el); });
  } else {
    maps.forEach(initMap);
  }
}());
