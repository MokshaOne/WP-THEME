<?php
/**
 * #62 — Map archive view of project locations (opt-in via shortcode).
 *
 * Place [nr_map] on any page to render a dark Leaflet/OpenStreetMap map with a
 * pin per project that has coordinates. Coordinates are entered in a small
 * "Location (map)" meta box on the project editor (works with or without ACF;
 * stored as project_lat / project_lng post meta, which nr_field() also reads).
 *
 * Leaflet is loaded from a CDN and ONLY on pages that actually use the
 * shortcode, so it never weighs down the rest of the site.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Coordinate meta box ───────────────────────────────────────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nr_geo', __( 'Location (map)', 'raveenthiran' ), 'nr_geo_box', 'nr_project', 'side' );
} );

function nr_geo_box( $post ) {
	wp_nonce_field( 'nr_geo', 'nr_geo_nonce' );
	$lat = get_post_meta( $post->ID, 'project_lat', true );
	$lng = get_post_meta( $post->ID, 'project_lng', true );
	echo '<p><label>' . esc_html__( 'Latitude', 'raveenthiran' ) . '<br>'
		. '<input type="text" name="project_lat" value="' . esc_attr( $lat ) . '" class="widefat" placeholder="48.2082"></label></p>';
	echo '<p><label>' . esc_html__( 'Longitude', 'raveenthiran' ) . '<br>'
		. '<input type="text" name="project_lng" value="' . esc_attr( $lng ) . '" class="widefat" placeholder="16.3738"></label></p>';
	echo '<p class="description">' . esc_html__( 'Optional. Adds a pin on any [nr_map]. Tip: right-click a spot in Google Maps to copy lat, lng.', 'raveenthiran' ) . '</p>';
}

add_action( 'save_post_nr_project', function ( $post_id ) {
	if ( ! isset( $_POST['nr_geo_nonce'] ) || ! wp_verify_nonce( $_POST['nr_geo_nonce'], 'nr_geo' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	foreach ( [ 'project_lat', 'project_lng' ] as $k ) {
		if ( ! isset( $_POST[ $k ] ) ) continue;
		$v = trim( sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		if ( $v === '' || is_numeric( $v ) ) {
			$v === '' ? delete_post_meta( $post_id, $k ) : update_post_meta( $post_id, $k, $v );
		}
	}
} );

/* ── Register Leaflet (enqueued on demand by the shortcode) ────── */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
	wp_register_script( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );
} );

/* ── [nr_map height="520"] ─────────────────────────────────────── */
add_shortcode( 'nr_map', function ( $atts ) {
	$a = shortcode_atts( [ 'height' => '520' ], $atts, 'nr_map' );

	$pins = [];
	$q = get_posts( [
		'post_type'      => 'nr_project',
		'posts_per_page' => 300,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => 'project_lat', 'value' => '', 'compare' => '!=' ],
			[ 'key' => 'project_lng', 'value' => '', 'compare' => '!=' ],
		],
	] );
	foreach ( $q as $id ) {
		$lat = (float) get_post_meta( $id, 'project_lat', true );
		$lng = (float) get_post_meta( $id, 'project_lng', true );
		if ( ! $lat || ! $lng ) continue;
		$pins[] = [
			'lat'   => $lat,
			'lng'   => $lng,
			'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $id ),
			'thumb' => (string) get_the_post_thumbnail_url( $id, 'nr-thumb' ),
		];
	}

	if ( ! $pins ) {
		return '<p class="nr-map-empty">' . esc_html__( 'No project locations yet — add coordinates in the “Location (map)” box on a project.', 'raveenthiran' ) . '</p>';
	}

	// #31 — Leaflet is NOT enqueued here; the footer loader fetches it lazily
	// only when the map scrolls into view, so map-less visits pay nothing.
	$GLOBALS['nr_map_present'] = true;

	$h = max( 240, (int) $a['height'] );
	return sprintf(
		'<div class="nr-map" data-nr-map data-pins="%s" style="height:%dpx"></div>',
		esc_attr( wp_json_encode( $pins ) ),
		$h
	);
} );

/* ── Lazy initialiser: load Leaflet on demand (IntersectionObserver) ── */
add_action( 'wp_footer', function () {
	if ( empty( $GLOBALS['nr_map_present'] ) ) return;
	?>
<script>
(function () {
  var maps = document.querySelectorAll('[data-nr-map]');
  if (!maps.length) return;
  var loaded = false;
  function loadLeaflet(cb) {
    if (window.L) { cb(); return; }
    var css = document.createElement('link');
    css.rel = 'stylesheet'; css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(css);
    var js = document.createElement('script');
    js.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    js.onload = cb; document.head.appendChild(js);
  }
  function initMaps() {
  document.querySelectorAll('[data-nr-map]').forEach(function (el) {
    var pins;
    try { pins = JSON.parse(el.getAttribute('data-pins') || '[]'); } catch (e) { return; }
    if (!pins.length) return;
    var map = L.map(el, { scrollWheelZoom: false, attributionControl: true });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap &copy; CARTO', maxZoom: 19, subdomains: 'abcd'
    }).addTo(map);
    var amber = (getComputedStyle(document.documentElement).getPropertyValue('--amber') || '#F2A03D').trim();
    var group = [];
    pins.forEach(function (p) {
      var m = L.circleMarker([p.lat, p.lng], { radius: 7, color: amber, weight: 2, fillColor: amber, fillOpacity: .85 }).addTo(map);
      var html = '<a href="' + p.url + '" style="display:block;max-width:180px;text-decoration:none;color:inherit">' +
        (p.thumb ? '<img src="' + p.thumb + '" alt="" style="width:100%;height:auto;display:block;margin-bottom:6px">' : '') +
        '<strong>' + (p.title || '') + '</strong></a>';
      m.bindPopup(html);
      group.push([p.lat, p.lng]);
    });
    if (group.length > 1) map.fitBounds(group, { padding: [40, 40] });
    else map.setView(group[0], 12);
  });
  }
  function trigger() { if (loaded) return; loaded = true; if (io) io.disconnect(); loadLeaflet(initMaps); }
  var io = null;
  if ('IntersectionObserver' in window) {
    io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) trigger(); });
    }, { rootMargin: '300px' });
    maps.forEach(function (m) { io.observe(m); });
  } else {
    trigger(); // no IO support → just load
  }
})();
</script>
	<?php
}, 99 );
