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
    /* 0072 · photo rotation — pick one of the spot's images at random so the
       same pin doesn't always show the identical hero shot. */
    var pool = (p.imgs && p.imgs.length) ? p.imgs.slice() : (p.img ? [p.img] : []);
    if (pool.length) {
      var shot = pool[Math.floor(Math.random() * pool.length)];
      html += '<div style="margin:-14px -20px 10px;height:110px;overflow:hidden;position:relative">'
            + '<img src="' + shot + '" alt="" style="width:100%;height:100%;object-fit:cover;display:block">'
            + (pool.length > 1 ? '<span style="position:absolute;right:6px;bottom:6px;background:rgba(11,11,11,.72);color:#fff;font:700 9px/1 ' + f + ';letter-spacing:.08em;padding:3px 6px;border-radius:2px">1/' + pool.length + '</span>' : '')
            + '</div>';
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
    // Cluster 01 · curated attribute chips + light hint + seasons
    html += attrChips(p);
    // 0019 · tour list — saved in this browser only
    if (p.id && p.url) {
      html += ' <a href="#" class="vpg-tour-add" data-id="' + p.id + '" data-title="' + (p.title || '').replace(/"/g, '&quot;') + '" data-url="' + p.url + '" style="font-family:' + f + ';font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#6A6A6A;margin-left:10px;text-decoration:none">☆ Save</a>';
    }
    // 0023 · standort-verlauf · mark this spot visited, in this browser only
    if (p.id) {
      var seen = visited().indexOf(String(p.id)) !== -1;
      html += ' <a href="#" class="vpg-visited" data-id="' + p.id + '" style="font-family:' + f + ';font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:' + (seen ? '#1A7A3C' : '#6A6A6A') + ';margin-left:10px;text-decoration:none">' + (seen ? '✓ visited' : '○ visited?') + '</a>';
    }
    return html;
  }

  /* 0023 · visited set (localStorage) */
  function visited() { try { return JSON.parse(localStorage.getItem('vpg_visited')) || []; } catch (e) { return []; } }
  function toggleVisited(id) {
    var v = visited(), i = v.indexOf(String(id));
    if (i === -1) v.push(String(id)); else v.splice(i, 1);
    try { localStorage.setItem('vpg_visited', JSON.stringify(v)); } catch (e) {}
    return v.indexOf(String(id)) !== -1;
  }

  /* 0001 / 0007 · rough sun compass now — sunrise≈E, noon≈S, sunset≈W (Vienna) */
  var COMPASS = { N: 0, NE: 45, E: 90, SE: 135, S: 180, SW: 225, W: 270, NW: 315 };
  function sunAzimuthNow() {
    var h = new Date().getHours() + new Date().getMinutes() / 60;
    if (h < 6 || h > 21) return null;            // dark · no useful sun
    return 90 + (h - 6) / 15 * 180;              // 6:00→90°(E) … 21:00→270°(W)
  }
  function lightHint(facing) {
    var sun = sunAzimuthNow();
    if (sun === null || !(facing in COMPASS)) return '';
    var diff = Math.abs(((COMPASS[facing] - sun + 540) % 360) - 180); // 180=aligned
    if (diff < 55)  return '<span style="color:#1A7A3C">☀ front-lit now</span>';
    if (diff > 125) return '<span style="color:#E5341F">⚠ back-lit now</span>';
    return '<span style="color:#6A6A6A">◐ side light now</span>';
  }

  /* Curated attribute chips from pin.attrs */
  function attrChips(p) {
    var a = p.attrs; if (!a || typeof a !== 'object') return '';
    var f = "'Archivo','Helvetica Neue',Arial,sans-serif";
    var chips = [];
    var lbl = {
      tripod: { ok: '△ tripod ok', tolerated: '△ tripod tolerated', no: '△ no tripod' },
      indoor: '☂ rain-safe', night: '☾ night', stepfree: '♿ step-free', winter: '❄ winter',
      toilets: '🚻 WC', drone: '🚁 drone restricted'
    };
    if (a.tripod && lbl.tripod[a.tripod]) chips.push(lbl.tripod[a.tripod]);
    ['indoor', 'night', 'stepfree', 'winter', 'toilets', 'drone'].forEach(function (k) { if (a[k]) chips.push(lbl[k]); });
    if (a.facing) chips.push('➤ faces ' + a.facing);
    if (a.elev) chips.push('⛰ ' + a.elev + ' m');
    if (a.station) chips.push('Ⓤ ' + a.station);
    if (a.parking) chips.push('🅿 ' + a.parking);
    (a.themes || []).forEach(function (t) { chips.push('#' + t); });
    var out = '';
    if (chips.length) {
      out += '<span style="font-family:' + f + ';display:flex;flex-wrap:wrap;gap:4px;margin:.15rem 0 .5rem">';
      chips.forEach(function (c) { out += '<span style="border:1px solid #E6E5E1;padding:2px 7px;font-size:10px;font-weight:700;color:#2C2C2C">' + c + '</span>'; });
      out += '</span>';
    }
    var lh = a.facing ? lightHint(a.facing) : '';
    if (lh) out += '<span style="font-family:' + f + ';font-size:11px;font-weight:700;display:block;margin-bottom:.4rem">' + lh + '</span>';
    if (a.seasons && a.seasons.length) out += '<span style="font-family:' + f + ';font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#6A6A6A;display:block;margin-bottom:.4rem">▦ ' + a.seasons.length + ' seasons on the page</span>';
    return out;
  }

  /* 0024 · cluster ring shows its category mix as a conic gradient */
  var TYPE_COLOR = { location: '#E5341F', studio: '#0B0B0B', shop: '#6A6A6A' };
  function clusterIcon(cluster) {
    var n = cluster.getChildCount();
    var size = n < 10 ? 36 : (n < 50 ? 44 : 52);
    var ring = '';
    try {
      var mix = {};
      cluster.getAllChildMarkers().forEach(function (m) { var t = m._vpgType || 'location'; mix[t] = (mix[t] || 0) + 1; });
      var stops = [], acc = 0;
      Object.keys(mix).forEach(function (t) {
        var from = acc / n * 360; acc += mix[t]; var to = acc / n * 360;
        stops.push((TYPE_COLOR[t] || '#9C9A95') + ' ' + from.toFixed(1) + 'deg ' + to.toFixed(1) + 'deg');
      });
      if (stops.length > 1) ring = 'background:conic-gradient(' + stops.join(',') + ');';
    } catch (e) {}
    return L.divIcon({
      html: '<span style="' + ring + '">' + n + '</span>',
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
      marker._vpgPin = p;
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

    // ── Unified client filtering · type + attribute + theme + year ──
    var allTypes = Object.keys(byType);
    var active   = new Set(allTypes);                 // type filter (existing)
    var fAttr    = new Set();                          // attribute flags (AND)
    var fTheme   = new Set();                          // themes (OR)
    var fDist    = new Set();                          // 0017 compare · districts (OR)
    var yMin = null, yMax = null;
    pins.forEach(function (p) { if (p.year) { yMin = yMin === null ? p.year : Math.min(yMin, p.year); yMax = yMax === null ? p.year : Math.max(yMax, p.year); } });
    var yLo = yMin, yHi = yMax;

    function passes(m) {
      if (!active.has(m._vpgType)) return false;
      var a = (m._vpgPin && m._vpgPin.attrs) || {};
      var ok = true;
      fAttr.forEach(function (k) { if (!a[k]) ok = false; });
      if (!ok) return false;
      if (fTheme.size) { var th = a.themes || [], any = false; fTheme.forEach(function (t) { if (th.indexOf(t) !== -1) any = true; }); if (!any) return false; }
      if (fDist.size) { var d = m._vpgPin && m._vpgPin.district; if (!d || !fDist.has(d)) return false; }
      var yr = m._vpgPin && m._vpgPin.year;
      if (yLo !== null && yr && (yr < yLo || yr > yHi)) return false;
      return true;
    }

    function renderMarkers() {
      rootGroup.clearLayers();
      allTypes.forEach(function (t) { byType[t].forEach(function (m) { if (passes(m)) rootGroup.addLayer(m); }); });
    }

    // ── Type filter bar (existing server-rendered toolbar) ──
    var selector = el.id ? '#' + el.id : '.vpg-map';
    var filters  = document.querySelectorAll('.vpg-map-filter[data-target="' + selector + '"], .vpg-map-filter[data-target="' + el.id + '"]');
    if (filters.length) {
      var fromUrl = readUrlTypes();
      if (fromUrl) { var valid = fromUrl.filter(function (t) { return allTypes.indexOf(t) !== -1; }); if (valid.length) active = new Set(valid); }
      var paint = function () {
        filters.forEach(function (bar) {
          bar.querySelectorAll('button[data-type]').forEach(function (b) {
            var t = b.getAttribute('data-type');
            b.classList.toggle('is-active', t === 'all' ? active.size === allTypes.length : active.has(t));
          });
        });
      };
      filters.forEach(function (bar) {
        bar.addEventListener('click', function (e) {
          var btn = e.target.closest('button[data-type]'); if (!btn) return;
          var type = btn.getAttribute('data-type');
          if (type === 'all') active = new Set(allTypes);
          else if (active.has(type) && active.size > 1) active.delete(type);
          else active.add(type);
          renderMarkers(); paint(); writeUrlTypes(active, allTypes.length);
        });
      });
      paint();
    }

    renderMarkers();

    // ── The cluster-01 tools · additive, each guarded ──
    try { addAttrFilters(el, pins, fAttr, fTheme, renderMarkers); } catch (e) {}
    try { if (yMin !== null && yMax !== null && yMax > yMin) addYearSlider(el, yMin, yMax, function (lo, hi) { yLo = lo; yHi = hi; renderMarkers(); }); } catch (e) {}
    try { addMapTools(el, map, byType, byId, pins); } catch (e) {}
    try { addGoldenTimeline(el); } catch (e) {}
    try { addShadowDial(el); } catch (e) {}
    try { addHeatToggle(el, map, pins); } catch (e) {}
    try { addCompareSplit(el, pins, fDist, renderMarkers); } catch (e) {}
    try { addMeetups(el, map); } catch (e) {}
    try { addWeatherBadge(el); } catch (e) {}
    try { runMapOnboarding(el); } catch (e) {}
    try {
      // 0012 · spot combos · nearest three others, injected on popup open
      map.on('popupopen', function (ev) {
        var pin = ev.popup._source && ev.popup._source._vpgPin; if (!pin) return;
        var node = ev.popup.getElement && ev.popup.getElement(); if (!node || node.querySelector('.vpg-kombi')) return;
        var near = nearestPins(pin, pins, 3);
        if (!near.length) return;
        var box = document.createElement('div');
        box.className = 'vpg-kombi';
        box.style.cssText = 'margin-top:.5rem;font-family:\'Archivo\',sans-serif;font-size:10px;line-height:1.5';
        box.innerHTML = '<span style="font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#6A6A6A">◈ Combine with</span><br>' +
          near.map(function (n) { return '<a href="' + n.url + '" style="color:#0B0B0B">' + n.title + ' · ' + n.dist + '</a>'; }).join('<br>');
        var content = node.querySelector('.leaflet-popup-content'); if (content) content.appendChild(box);
      });
    } catch (e) {}
  }

  /* Haversine metres → a friendly "450 m · 6 min walk" string */
  function haversine(a, b, c, d) {
    var R = 6371000, r = Math.PI / 180;
    var dLat = (c - a) * r, dLng = (d - b) * r;
    var s = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(a * r) * Math.cos(c * r) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return 2 * R * Math.asin(Math.sqrt(s));
  }
  function distLabel(m) {
    var walk = Math.max(1, Math.round(m / 1000 / 4.8 * 60)); // 0011 · 4.8 km/h
    return (m < 1000 ? Math.round(m) + ' m' : (m / 1000).toFixed(1) + ' km') + ' · ' + walk + ' min';
  }
  function nearestPins(pin, pins, k) {
    return pins.filter(function (p) { return p.id !== pin.id && typeof p.lat === 'number'; })
      .map(function (p) { return { title: p.title, url: p.url, d: haversine(pin.lat, pin.lng, p.lat, p.lng) }; })
      .sort(function (a, b) { return a.d - b.d; })
      .slice(0, k)
      .map(function (p) { return { title: p.title, url: p.url, dist: distLabel(p.d) }; });
  }

  /* 0004/0005/0027/0028/0034/0037 · attribute + theme filter chips */
  function addAttrFilters(el, pins, fAttr, fTheme, render) {
    var flags = [
      ['indoor', '☂ Rain-safe'], ['night', '☾ Night'], ['stepfree', '♿ Step-free'],
      ['winter', '❄ Winter'], ['toilets', '🚻 WC'], ['drone', '🚁 Drone-free view']
    ];
    var haveFlag = {}, haveTheme = {};
    pins.forEach(function (p) {
      var a = p.attrs || {};
      flags.forEach(function (f) { if (a[f[0]]) haveFlag[f[0]] = true; });
      (a.themes || []).forEach(function (t) { haveTheme[t] = true; });
    });
    var avail = flags.filter(function (f) { return haveFlag[f[0]]; });
    var themes = Object.keys(haveTheme);
    if (!avail.length && !themes.length) return;

    var bar = document.createElement('div');
    bar.className = 'vpg-attr-filter';
    bar.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px;margin:10px 0 0;font-family:\'Archivo\',sans-serif';
    function chip(label, on) {
      var b = document.createElement('button');
      b.type = 'button'; b.textContent = label;
      b.style.cssText = 'border:1px solid #0B0B0B;background:#fff;color:#0B0B0B;font-size:11px;font-weight:700;padding:5px 11px;cursor:pointer';
      b.addEventListener('click', function () { on(b); render(); });
      return b;
    }
    avail.forEach(function (f) {
      bar.appendChild(chip(f[1], function (b) {
        if (fAttr.has(f[0])) { fAttr.delete(f[0]); paintChip(b, false); }
        else { fAttr.add(f[0]); paintChip(b, true); }
      }));
    });
    themes.forEach(function (t) {
      bar.appendChild(chip('#' + t, function (b) {
        if (fTheme.has(t)) { fTheme.delete(t); paintChip(b, false); }
        else { fTheme.add(t); paintChip(b, true); }
      }));
    });
    function paintChip(b, on) { b.style.background = on ? '#E5341F' : '#fff'; b.style.borderColor = on ? '#E5341F' : '#0B0B0B'; b.style.color = on ? '#fff' : '#0B0B0B'; }
    el.insertAdjacentElement('afterend', bar);
  }

  /* 0018 · Zeitreise · a year range slider under the map */
  function addYearSlider(el, min, max, onChange) {
    var wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;align-items:center;gap:10px;margin:10px 0 0;font-family:\'Archivo\',sans-serif;font-size:11px;font-weight:700;color:#6A6A6A';
    var lo = document.createElement('input'), hi = document.createElement('input');
    [lo, hi].forEach(function (s) { s.type = 'range'; s.min = min; s.max = max; s.step = 1; s.style.flex = '1'; });
    lo.value = min; hi.value = max;
    var out = document.createElement('span');
    function upd() {
      var a = Math.min(+lo.value, +hi.value), b = Math.max(+lo.value, +hi.value);
      out.textContent = a === b ? a : a + '–' + b;
      onChange(a, b);
    }
    lo.addEventListener('input', upd); hi.addEventListener('input', upd);
    wrap.appendChild(document.createTextNode('⏱ ')); wrap.appendChild(lo); wrap.appendChild(hi); wrap.appendChild(out);
    upd();
    el.insertAdjacentElement('afterend', wrap);
  }

  /* 0016/0011 measure · 0030 radius · 0015 print — a small tool box */
  function addMapTools(el, map, byType, byId, pins) {
    ensureControlCss();
    var box = document.createElement('div');
    box.className = 'vpg-map-ctl'; box.style.top = '104px';

    // Measure (distance + walking time)
    var measuring = false, mPts = [], mLayer = L.layerGroup().addTo(map);
    var measure = toolBtn('△', 'Measure distance');
    measure.addEventListener('click', function () {
      measuring = !measuring; measure.classList.toggle('is-on', measuring);
      if (!measuring) { mLayer.clearLayers(); mPts = []; }
    });
    map.on('click', function (e) {
      if (!measuring) return;
      mPts.push(e.latlng);
      var total = 0;
      for (var i = 1; i < mPts.length; i++) total += haversine(mPts[i - 1].lat, mPts[i - 1].lng, mPts[i].lat, mPts[i].lng);
      mLayer.clearLayers();
      L.polyline(mPts, { color: '#E5341F', weight: 3, dashArray: '5,6' }).addTo(mLayer);
      mPts.forEach(function (pt) { L.circleMarker(pt, { radius: 4, color: '#E5341F', fillColor: '#fff', fillOpacity: 1 }).addTo(mLayer); });
      if (mPts.length > 1) L.marker(mPts[mPts.length - 1], { icon: L.divIcon({ className: 'vpg-measure-lbl', html: '<span style="background:#0B0B0B;color:#fff;padding:2px 7px;font:700 11px/1 sans-serif;white-space:nowrap">' + distLabel(total) + '</span>', iconSize: null }) }).addTo(mLayer);
    });

    // Radius search
    var radMode = false, radLayer = L.layerGroup().addTo(map);
    var radius = toolBtn('◯', 'Find within a radius');
    radius.addEventListener('click', function () { radMode = !radMode; radius.classList.toggle('is-on', radMode); if (!radMode) radLayer.clearLayers(); });
    map.on('click', function (e) {
      if (!radMode) return;
      var r = 500;
      radLayer.clearLayers();
      L.circle(e.latlng, { radius: r, color: '#E5341F', weight: 1, fillOpacity: 0.06 }).addTo(radLayer);
      var hits = pins.filter(function (p) { return typeof p.lat === 'number' && haversine(e.latlng.lat, e.latlng.lng, p.lat, p.lng) <= r; });
      L.marker(e.latlng, { icon: L.divIcon({ html: '<span style="background:#0B0B0B;color:#fff;padding:2px 7px;font:700 11px/1 sans-serif;white-space:nowrap">' + hits.length + ' within ' + r + ' m</span>' }) }).addTo(radLayer);
    });

    // Print / snapshot (0015) — browser print of the map view
    var pr = toolBtn('⎙', 'Print this view');
    pr.addEventListener('click', function () { window.print(); });

    box.appendChild(measure); box.appendChild(radius); box.appendChild(pr);
    el.appendChild(box);
    function toolBtn(txt, title) { var b = document.createElement('button'); b.type = 'button'; b.textContent = txt; b.title = title; b.setAttribute('aria-label', title); return b; }
  }

  /* 0029 · Golden-hour timeline · today's light bands for Vienna */
  function addGoldenTimeline(el) {
    var t = sunTimes(new Date(), 48.2082, 16.3738);
    if (!t) return;
    function pct(h) { return Math.max(0, Math.min(100, h / 24 * 100)); }
    var bar = document.createElement('div');
    bar.style.cssText = 'position:relative;height:26px;margin:10px 0 0;border:1px solid #E6E5E1;font-family:\'Archivo\',sans-serif';
    function band(a, b, col, lbl) {
      var d = document.createElement('span');
      d.style.cssText = 'position:absolute;top:0;bottom:0;left:' + pct(a) + '%;width:' + (pct(b) - pct(a)) + '%;background:' + col;
      if (lbl) { d.title = lbl; }
      bar.appendChild(d);
    }
    band(t.sunrise, t.sunrise + 1, 'rgba(229,52,31,.35)', 'golden');       // morning golden
    band(t.sunset - 1, t.sunset, 'rgba(229,52,31,.35)', 'golden');          // evening golden
    band(t.sunset, t.dusk, 'rgba(11,11,60,.30)', 'blue');                   // blue hour
    var now = new Date().getHours() + new Date().getMinutes() / 60;
    var nl = document.createElement('span');
    nl.style.cssText = 'position:absolute;top:-2px;bottom:-2px;left:' + pct(now) + '%;width:2px;background:#0B0B0B';
    bar.appendChild(nl);
    var lbl = document.createElement('span');
    lbl.style.cssText = 'position:absolute;left:6px;top:5px;font-size:9.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#6A6A6A';
    lbl.textContent = '☀ ' + fmtH(t.sunrise) + ' · golden · ' + fmtH(t.sunset) + ' · blue';
    bar.appendChild(lbl);
    el.insertAdjacentElement('afterend', bar);
  }
  function fmtH(h) { var hh = Math.floor(h), mm = Math.round((h - hh) * 60); return (hh < 10 ? '0' : '') + hh + ':' + (mm < 10 ? '0' : '') + mm; }
  /* Compact NOAA sunrise/sunset (hours, local) — good enough for a timeline */
  function sunTimes(date, lat, lng) {
    try {
      var rad = Math.PI / 180, day = Math.floor((date - new Date(date.getFullYear(), 0, 0)) / 864e5);
      var decl = -23.44 * Math.cos(rad * (360 / 365) * (day + 10));
      var ha = Math.acos(-Math.tan(lat * rad) * Math.tan(decl * rad)) / rad;
      var tz = -date.getTimezoneOffset() / 60;
      var solarNoon = 12 - lng / 15 + tz;
      return { sunrise: solarNoon - ha / 15, sunset: solarNoon + ha / 15, dusk: solarNoon + (ha + 6) / 15 };
    } catch (e) { return null; }
  }

  /* 0003 · shadow dial · where shadows fall right now (sun opposite) */
  function addShadowDial(el) {
    var sun = sunAzimuthNow();
    var dial = document.createElement('div');
    dial.style.cssText = 'position:absolute;left:10px;bottom:10px;z-index:800;width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.94);border:1px solid #0B0B0B;font:700 8px/1 sans-serif';
    if (sun === null) {
      dial.innerHTML = '<span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#6A6A6A">☾ dark</span>';
    } else {
      var shadow = (sun + 180) % 360; // shadows point away from the sun
      dial.title = 'Sun ' + Math.round(sun) + '° · shadows ' + Math.round(shadow) + '°';
      dial.innerHTML =
        '<span style="position:absolute;top:2px;left:50%;transform:translateX(-50%);color:#6A6A6A">N</span>' +
        '<span style="position:absolute;top:50%;left:50%;width:2px;height:22px;background:#E5341F;transform-origin:top center;transform:translate(-50%,0) rotate(' + sun + 'deg)" title="sun"></span>' +
        '<span style="position:absolute;top:50%;left:50%;width:2px;height:22px;background:#0B0B0B;transform-origin:top center;transform:translate(-50%,0) rotate(' + shadow + 'deg)" title="shadow"></span>' +
        '<span style="position:absolute;left:50%;top:50%;width:4px;height:4px;background:#0B0B0B;border-radius:50%;transform:translate(-50%,-50%)"></span>';
    }
    el.appendChild(dial);
  }

  /* 0013 · upload heatmap · translucent discs darken where pins cluster */
  function addHeatToggle(el, map, pins) {
    ensureControlCss();
    var box = el.querySelector('.vpg-map-ctl'); // reuse the last tool box if present
    var btn = document.createElement('button');
    btn.type = 'button'; btn.textContent = '❖'; btn.title = 'Upload density';
    var layer = null;
    btn.addEventListener('click', function () {
      if (layer) { map.removeLayer(layer); layer = null; btn.classList.remove('is-on'); return; }
      layer = L.layerGroup();
      pins.forEach(function (p) { if (typeof p.lat === 'number') L.circle([p.lat, p.lng], { radius: 180, stroke: false, fillColor: '#E5341F', fillOpacity: 0.12 }).addTo(layer); });
      layer.addTo(map); btn.classList.add('is-on');
    });
    if (box) box.appendChild(btn);
    else { var b2 = document.createElement('div'); b2.className = 'vpg-map-ctl'; b2.style.top = '150px'; b2.appendChild(btn); el.appendChild(b2); }
  }

  /* 0017 · compare split · pick two districts, show only those */
  function addCompareSplit(el, pins, fDist, render) {
    var dists = {};
    pins.forEach(function (p) { if (p.district) dists[p.district] = true; });
    var list = Object.keys(dists).sort();
    if (list.length < 2) return;
    var wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;gap:8px;align-items:center;margin:10px 0 0;font-family:\'Archivo\',sans-serif;font-size:11px;font-weight:700;color:#6A6A6A;flex-wrap:wrap';
    function sel() { var s = document.createElement('select'); s.style.cssText = 'padding:5px 8px;border:1px solid #0B0B0B'; s.innerHTML = '<option value="">—</option>' + list.map(function (d) { return '<option>' + d + '</option>'; }).join(''); return s; }
    var a = sel(), b = sel();
    function upd() { fDist.clear(); if (a.value) fDist.add(a.value); if (b.value) fDist.add(b.value); render(); }
    a.addEventListener('change', upd); b.addEventListener('change', upd);
    wrap.appendChild(document.createTextNode('⇆ compare')); wrap.appendChild(a); wrap.appendChild(b);
    el.insertAdjacentElement('afterend', wrap);
  }

  /* 0025 · meetups near me · upcoming events from data-events */
  function addMeetups(el, map) {
    var events; try { events = JSON.parse(el.getAttribute('data-events') || '[]'); } catch (e) { events = []; }
    if (!events.length || !navigator.geolocation) return;
    ensureControlCss();
    var btn = document.createElement('button');
    btn.type = 'button'; btn.textContent = '⚑'; btn.title = 'Meetups near me';
    var layer = null;
    btn.addEventListener('click', function () {
      if (layer) { map.removeLayer(layer); layer = null; btn.classList.remove('is-on'); return; }
      btn.textContent = '…';
      navigator.geolocation.getCurrentPosition(function (pos) {
        btn.textContent = '⚑'; btn.classList.add('is-on');
        layer = L.layerGroup();
        events.forEach(function (ev) {
          var d = haversine(pos.coords.latitude, pos.coords.longitude, ev.lat, ev.lng);
          L.marker([ev.lat, ev.lng], { icon: L.divIcon({ className: '', html: '<span style="background:#0B0B0B;color:#fff;padding:3px 8px;font:700 11px/1 sans-serif;white-space:nowrap;border:2px solid #E5341F">⚑ ' + ev.title + '</span>' }) })
            .bindPopup('<b>' + ev.title + '</b><br>' + (ev.date || '') + ' · ' + distLabel(d) + '<br><a href="' + ev.url + '">→</a>').addTo(layer);
        });
        layer.addTo(map);
      }, function () { btn.textContent = '⚑'; });
    });
    var box = document.createElement('div'); box.className = 'vpg-map-ctl'; box.style.top = '196px'; box.appendChild(btn); el.appendChild(box);
  }

  /* 0039 · live weather badge · reuses the server-side vpg_weather()
     result (Open-Meteo, cached 30 min) passed in via data-weather — no
     separate per-visitor fetch, one cached source shared with the singles. */
  function addWeatherBadge(el) {
    var wx; try { wx = JSON.parse(el.getAttribute('data-weather') || 'null'); } catch (e) { wx = null; }
    if (!wx || !wx.temp) return;
    var badge = document.createElement('span');
    badge.style.cssText = 'position:absolute;top:10px;left:10px;z-index:800;background:#fff;border:1px solid #0B0B0B;padding:5px 10px;font:700 11px/1 sans-serif;color:#0B0B0B';
    badge.textContent = '☁ ' + wx.temp + (wx.label ? ' · ' + wx.label : '');
    el.appendChild(badge);
  }

  /* 0040 · onboarding · a one-time 60-second nudge for first visitors */
  function runMapOnboarding(el) {
    var KEY = 'vpg_map_tour';
    try { if (localStorage.getItem(KEY)) return; } catch (e) { return; }
    var tip = document.createElement('div');
    tip.style.cssText = 'position:absolute;left:50%;top:14px;transform:translateX(-50%);z-index:850;background:#0B0B0B;color:#fff;padding:12px 18px;max-width:320px;font:13px/1.5 \'Archivo\',sans-serif;box-shadow:0 6px 24px rgba(0,0,0,.3)';
    var steps = [
      '👋 Welcome to the map. Click a pin for light notes, tripod rules and access — everything a member noticed on-site.',
      '🧭 Use the chips under the map to filter: rain-safe, night, step-free, by theme.',
      '🛠 Top-right tools: measure a walk, search a radius, go fullscreen. Bottom-left dial shows where shadows fall now.',
      '☆ Save spots to a private tour, mark them visited — all in your browser only. Happy shooting.'
    ];
    var i = 0;
    function show() {
      tip.innerHTML = '<div>' + steps[i] + '</div><div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center"><span style="font-size:11px;color:#A5A29C">' + (i + 1) + ' / ' + steps.length + '</span><button style="background:#E5341F;color:#fff;border:0;padding:6px 14px;font-weight:800;cursor:pointer">' + (i < steps.length - 1 ? 'Next →' : 'Got it') + '</button></div>';
      tip.querySelector('button').addEventListener('click', function () {
        i++; if (i >= steps.length) { try { localStorage.setItem(KEY, '1'); } catch (e) {} tip.remove(); } else show();
      });
    }
    show(); el.appendChild(tip);
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
      '.vpg-map-ctl button.is-on{background:#E5341F;border-color:#E5341F;color:#fff}' +
      '.vpg-map--fs{position:fixed!important;inset:0!important;width:auto!important;height:auto!important;z-index:9999;margin:0!important}' +
      '@media print{body *{visibility:hidden}.vpg-map,.vpg-map *{visibility:visible}.vpg-map{position:absolute;left:0;top:0;width:100%;height:80vh}.vpg-map-ctl,.vpg-attr-filter{display:none!important}}';
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


/* ── 0019 · Tour list tray · localStorage, this browser only ─────── */
(function () {
  var mapEl = document.getElementById('vpg-map');
  if (!mapEl) return;
  var KEY = 'vpg_tour';
  function load() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
  function save(list) { try { localStorage.setItem(KEY, JSON.stringify(list.slice(0, 30))); } catch (e) {} }

  var tray = document.createElement('div');
  tray.innerHTML = '<button type="button" id="vpg-tour-btn" hidden style="position:fixed;left:16px;bottom:16px;z-index:600;background:#0B0B0B;color:#fff;border:0;padding:10px 16px;font:700 12px/1 Archivo,sans-serif;letter-spacing:.1em;text-transform:uppercase;cursor:pointer">⚑ Tour · <span id="vpg-tour-n">0</span></button>' +
    '<div id="vpg-tour-panel" hidden style="position:fixed;left:16px;bottom:60px;z-index:600;background:#fff;border:2px solid #0B0B0B;padding:16px 18px;max-width:300px;max-height:50vh;overflow-y:auto;font-family:Archivo,sans-serif"></div>';
  document.body.appendChild(tray);
  var btn = document.getElementById('vpg-tour-btn'), panel = document.getElementById('vpg-tour-panel'), num = document.getElementById('vpg-tour-n');

  function render() {
    var list = load();
    btn.hidden = !list.length;
    num.textContent = list.length;
    panel.innerHTML = list.map(function (it, i) {
      return '<div style="display:flex;gap:10px;align-items:baseline;padding:6px 0;border-top:1px solid #E6E5E1">' +
        '<b style="color:#E5341F;font-size:11px">' + (i + 1) + '</b>' +
        '<a href="' + it.url + '" style="flex:1;font-size:13px;font-weight:600;color:#0B0B0B;text-decoration:none">' + it.title + '</a>' +
        '<button type="button" data-rm="' + i + '" style="background:none;border:0;color:#E5341F;cursor:pointer;font-weight:700">×</button></div>';
    }).join('') + (list.length ? '<button type="button" id="vpg-tour-clear" style="margin-top:10px;background:none;border:1px solid #E6E5E1;padding:6px 10px;font:700 10px/1 Archivo,sans-serif;letter-spacing:.1em;text-transform:uppercase;cursor:pointer">Clear tour</button>' : '');
    if (!list.length) panel.hidden = true;
  }

  document.addEventListener('click', function (e) {
    // 0023 · toggle "visited" from a popup link
    var vis = e.target.closest('.vpg-visited');
    if (vis) {
      e.preventDefault();
      var on = toggleVisited(vis.dataset.id);
      vis.textContent = on ? '✓ visited' : '○ visited?';
      vis.style.color = on ? '#1A7A3C' : '#6A6A6A';
      return;
    }
    var add = e.target.closest('.vpg-tour-add');
    if (add) {
      e.preventDefault();
      var list = load();
      if (!list.some(function (it) { return it.id === add.dataset.id; })) {
        list.push({ id: add.dataset.id, title: add.dataset.title, url: add.dataset.url });
        save(list); render();
        add.textContent = '★ Saved';
      }
      return;
    }
    if (e.target === btn || btn.contains(e.target)) { panel.hidden = !panel.hidden; return; }
    if (e.target.dataset && e.target.dataset.rm !== undefined && panel.contains(e.target)) {
      var list = load(); list.splice(+e.target.dataset.rm, 1); save(list); render(); if (list.length) panel.hidden = false;
      return;
    }
    if (e.target.id === 'vpg-tour-clear') { save([]); render(); }
  });
  render();
})();
