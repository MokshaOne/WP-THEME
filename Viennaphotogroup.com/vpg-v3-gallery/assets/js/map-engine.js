/* VPG v2 · map-engine.js
 * Initialises Leaflet on every #vpg-map / .vpg-map element.
 *
 * Per-pin data shape:
 *   { lat, lng, title, url, lede, type }
 *     type · location | studio | shop | event | review | tutorial | magazine | member
 *            controls the marker colour & glyph (see CSS .vpg-pin--<type>)
 *
 * Behaviour:
 *   - Custom teardrop divIcon per type · colour from token palette
 *   - Clusters when count > 4 (and Leaflet.markercluster is loaded)
 *   - Optional filter bar · any .vpg-map-filter[data-target="#vpg-map"] in DOM
 *     toggles pin types on/off
 *   - fitBounds with 18% padding and maxZoom 16 so single pins don't slam in
 *   - Wheel-zoom only after first click (prevents page-scroll hijack)
 */
(function () {
  'use strict';
  if (typeof L === 'undefined') return;

  var GLYPH = {
    location: '◉', studio: '⌗', shop: '$', event: '◆',
    review:   '★', tutorial: '?', magazine: 'M', member:  '●'
  };

  function makeIcon(type) {
    return L.divIcon({
      className: 'vpg-pin vpg-pin--' + (type || 'location'),
      html: '<div class="vpg-pin__inner"><span class="vpg-pin__glyph">' + (GLYPH[type] || '◉') + '</span></div>',
      iconSize:   [32, 42],
      iconAnchor: [16, 42],
      popupAnchor: [0, -38]
    });
  }

  function popupHtml(p) {
    return '<strong style="font-family:Playfair Display,serif;font-size:16px;display:block;margin-bottom:.3rem">' +
           (p.title || '') + '</strong>' +
           (p.type ? '<span style="font-family:JetBrains Mono,monospace;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#6B5A48;display:block;margin-bottom:.3rem">' + p.type + '</span>' : '') +
           (p.lede ? '<span style="color:#6B5A48;font-size:13px;display:block;margin-bottom:.5rem">' + p.lede + '</span>' : '') +
           (p.url  ? '<a href="' + p.url + '" style="font-family:JetBrains Mono,monospace;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C8601A">View →</a>' : '');
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

  document.querySelectorAll('#vpg-map, .vpg-map').forEach(function (el) {
    var raw = el.getAttribute('data-pins') || '[]';
    var pins;
    try { pins = JSON.parse(raw); } catch (e) { pins = []; }

    var map = L.map(el, { scrollWheelZoom: false, zoomControl: true, maxZoom: 18 });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://carto.com/">CARTO</a> · &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
      subdomains: 'abcd'
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

    pins.forEach(function (p) {
      if (typeof p.lat !== 'number' || typeof p.lng !== 'number') return;
      var t = p.type || 'location';
      var marker = L.marker([p.lat, p.lng], { icon: makeIcon(t), title: p.title || '' });
      marker.bindPopup(popupHtml(p), { closeButton: true, autoPan: true });
      marker._vpgType = t;
      (byType[t] = byType[t] || []).push(marker);
      rootGroup.addLayer(marker);
    });

    map.addLayer(rootGroup);

    try {
      var bounds = rootGroup.getBounds();
      if (bounds.isValid()) map.fitBounds(bounds.pad(0.18), { maxZoom: 16 });
    } catch (e) {
      map.setView([48.2082, 16.3738], 12);
    }

    el.addEventListener('click', function () { map.scrollWheelZoom.enable(); }, { once: true });

    // Optional · expose total count via data-count for the corner chip
    el.setAttribute('data-count', pins.length + ' pinned');

    // ── Filter-bar wiring ──
    // Looks for siblings or doc-wide `.vpg-map-filter[data-target="#vpg-map"]` and
    // any buttons with [data-type="<type>"] or [data-type="all"]. Click toggles.
    var selector = el.id ? '#' + el.id : '.vpg-map';
    var filters  = document.querySelectorAll('.vpg-map-filter[data-target="' + selector + '"], .vpg-map-filter[data-target="' + el.id + '"]');
    filters.forEach(function (bar) {
      var active = new Set( Object.keys( byType ) );
      bar.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-type]');
        if (!btn) return;
        var type = btn.getAttribute('data-type');
        if (type === 'all') {
          active = new Set( Object.keys( byType ) );
        } else if (active.has(type) && active.size > 1) {
          active.delete(type);
        } else {
          active.add(type);
        }
        // Visual state
        bar.querySelectorAll('button[data-type]').forEach(function (b) {
          var t = b.getAttribute('data-type');
          b.classList.toggle('is-active', t === 'all' ? active.size === Object.keys(byType).length : active.has(t));
        });
        // Layer updates
        rootGroup.clearLayers();
        Object.keys(byType).forEach(function (t) {
          if (!active.has(t)) return;
          byType[t].forEach(function (m) { rootGroup.addLayer(m); });
        });
      });
      // Initialise visual state
      bar.querySelectorAll('button[data-type]').forEach(function (b) {
        var t = b.getAttribute('data-type');
        b.classList.toggle('is-active', t === 'all' || active.has(t));
      });
    });
  });
}());
