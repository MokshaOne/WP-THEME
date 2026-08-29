<?php
/**
 * VPG v2 — Location meta box.
 *
 * Adds a "Location on the map" panel to vpg_location, vpg_studio and vpg_shop
 * post-edit screens. The editor types an address (or clicks the map) and the
 * lat/lng meta is filled automatically. District is a free text field.
 *
 * Keys written per post type:
 *   vpg_location → location_lat, location_lng, location_district
 *   vpg_studio   → studio_lat,   studio_lng,   location_district (shared key)
 *   vpg_shop     → shop_lat,     shop_lng,     shop_district
 *
 * Uses the Leaflet bundle we already ship + OSM Nominatim for address search
 * (no API key needed).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_LOC_META_TYPES = [
    'vpg_location' => [ 'lat' => 'location_lat', 'lng' => 'location_lng', 'district' => 'location_district' ],
    'vpg_studio'   => [ 'lat' => 'studio_lat',   'lng' => 'studio_lng',   'district' => 'location_district' ],
    'vpg_shop'     => [ 'lat' => 'shop_lat',     'lng' => 'shop_lng',     'district' => 'shop_district' ],
];

/* ─── One-shot migration · v1 combined "coordinates" → v2 split lat/lng ─────
   Runs once on theme activation. Reads every published location/studio/shop,
   parses the legacy ACF field, writes split lat/lng meta. Safe to re-run. */
add_action( 'after_switch_theme', 'vpg_migrate_legacy_coords', 20 );
add_action( 'admin_post_vpg_migrate_coords', 'vpg_migrate_legacy_coords_handler' );

function vpg_migrate_legacy_coords_handler() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_migrate_coords' );
    $n = vpg_migrate_legacy_coords();
    wp_safe_redirect( admin_url( 'tools.php?vpg_migration=done&n=' . $n ) );
    exit;
}

function vpg_migrate_legacy_coords() {
    // Try MANY possible legacy ACF/meta field names per CPT.
    // Different VPG installs used slightly different names.
    $candidate_keys = [
        'vpg_location' => [ 'coordinates', 'location_coordinates', 'loc_coords', 'coords', 'location_coords', 'geo', 'latlng' ],
        'vpg_studio'   => [ 'studio_coordinates', 'studio_coords', 'coords', 'coordinates', 'geo', 'latlng' ],
        'vpg_shop'     => [ 'shop_coordinates', 'shop_coords', 'coords', 'coordinates', 'geo', 'latlng' ],
    ];

    $migrated = 0;
    foreach ( $candidate_keys as $type => $fields ) {
        $posts = get_posts( [ 'post_type' => $type, 'posts_per_page' => -1, 'post_status' => [ 'publish', 'draft', 'pending' ] ] );
        $keys = VPG_LOC_META_TYPES[ $type ];
        foreach ( $posts as $p ) {
            $existing_lat = get_post_meta( $p->ID, $keys['lat'], true );
            $existing_lng = get_post_meta( $p->ID, $keys['lng'], true );
            if ( $existing_lat && $existing_lng ) continue; // already migrated

            // Walk candidate field names, take first that parses
            $combo = '';
            foreach ( $fields as $field ) {
                $val = function_exists( 'get_field' ) ? get_field( $field, $p->ID ) : '';
                if ( ! $val ) $val = get_post_meta( $p->ID, $field, true );
                if ( $val && is_string( $val ) && strpos( $val, ',' ) !== false ) { $combo = $val; break; }
            }
            if ( ! $combo ) continue;

            $parts = array_map( 'trim', explode( ',', $combo, 2 ) );
            if ( count( $parts ) !== 2 || ! is_numeric( $parts[0] ) || ! is_numeric( $parts[1] ) ) continue;

            $lat = (float) $parts[0];
            $lng = (float) $parts[1];
            if ( abs( $lat ) > 90 || abs( $lng ) > 180 ) continue;

            update_post_meta( $p->ID, $keys['lat'], $lat );
            update_post_meta( $p->ID, $keys['lng'], $lng );
            $migrated++;
        }
    }
    delete_transient( 'vpg_location_pins' );
    delete_transient( 'vpg_location_districts' );
    return $migrated;
}

/* ─── Diagnostic · scans a sample post per CPT and reports
       which meta keys carry coordinate-shaped values.
       Triggered via Tools page · admin-only. */
function vpg_diagnose_coords() {
    $report = [];
    foreach ( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] as $type ) {
        $posts = get_posts( [ 'post_type' => $type, 'posts_per_page' => 5, 'post_status' => 'publish' ] );
        $type_report = [
            'cpt'          => $type,
            'total'        => (int) ( wp_count_posts( $type )->publish ?? 0 ),
            'with_coords'  => 0,
            'sample_meta'  => [],
        ];
        foreach ( $posts as $p ) {
            $all_meta = get_post_meta( $p->ID );
            $coord_keys = [];
            foreach ( $all_meta as $k => $v ) {
                $val = is_array( $v ) ? ( $v[0] ?? '' ) : $v;
                if ( ! is_string( $val ) ) continue;
                // Match anything that looks like "<num>, <num>"
                if ( preg_match( '/^-?\d{1,3}\.\d+\s*,\s*-?\d{1,3}\.\d+$/', $val ) ) {
                    $coord_keys[ $k ] = $val;
                }
            }
            if ( $coord_keys ) {
                $type_report['with_coords']++;
                $type_report['sample_meta'][ $p->ID ] = [
                    'title' => get_the_title( $p ),
                    'keys'  => $coord_keys,
                ];
            }
        }
        $report[ $type ] = $type_report;
    }
    return $report;
}

add_action( 'admin_post_vpg_diagnose_coords', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_diagnose_coords' );
    set_transient( 'vpg_coord_diagnose', vpg_diagnose_coords(), 5 * MINUTE_IN_SECONDS );
    wp_safe_redirect( admin_url( 'tools.php?page=vpg-setup&diag=ok' ) );
    exit;
} );

/* Migration trigger in Tools page · alongside Setup wizard */
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( ! isset( $_GET['vpg_migration'] ) ) return;
    $n = (int) ( $_GET['n'] ?? 0 );
    ?>
    <div class="notice notice-success is-dismissible"><p><strong>VPG · Coordinate migration done.</strong> <?php printf( esc_html( _n( '%d entry migrated.', '%d entries migrated.', $n, 'vpg-v2' ) ), $n ); ?></p></div>
    <?php
} );

/* ─── Register meta box ───────────────────────────────────────── */
add_action( 'add_meta_boxes', function () {
    foreach ( VPG_LOC_META_TYPES as $type => $_keys ) {
        add_meta_box(
            'vpg_location_meta',
            '📍 ' . __( 'Location on the map', 'vpg-v2' ),
            'vpg_render_location_meta_box',
            $type,
            'normal',
            'high'
        );
    }
} );

/* ─── Render the meta box ─────────────────────────────────────── */
function vpg_render_location_meta_box( $post ) {
    $keys = VPG_LOC_META_TYPES[ $post->post_type ] ?? null;
    if ( ! $keys ) return;

    wp_nonce_field( 'vpg_save_location_meta', 'vpg_location_meta_nonce' );

    $lat      = get_post_meta( $post->ID, $keys['lat'], true );
    $lng      = get_post_meta( $post->ID, $keys['lng'], true );
    $district = get_post_meta( $post->ID, $keys['district'], true );

    // Legacy fallback · if the v1 combined "coordinates" / "studio_coordinates" /
    // "shop_coordinates" field is set but the new split fields aren't,
    // parse it so the editor sees a populated map on first load.
    if ( ( ! $lat || ! $lng ) ) {
        $coords = vpg_get_coords( $post->ID );
        if ( $coords ) {
            $lat = (string) $coords[0];
            $lng = (string) $coords[1];
        }
    }

    // Enqueue Leaflet for this admin screen (it's not normally loaded in admin)
    $leaflet_css = get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.css';
    $leaflet_js  = get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.js';
    ?>
    <link rel="stylesheet" href="<?php echo esc_url( $leaflet_css ); ?>" />
    <script src="<?php echo esc_url( $leaflet_js ); ?>" defer></script>

    <div class="vpg-loc-meta">
        <div class="vpg-loc-meta__row">
            <label>
                <span><?php esc_html_e( 'Search address', 'vpg-v2' ); ?></span>
                <div class="vpg-loc-meta__search">
                    <input type="text" id="vpg-loc-search" placeholder="<?php esc_attr_e( 'Karlsplatz 4, Wien', 'vpg-v2' ); ?>" autocomplete="off">
                    <button type="button" class="button" id="vpg-loc-go"><?php esc_html_e( 'Find', 'vpg-v2' ); ?></button>
                </div>
                <small class="description"><?php esc_html_e( 'Or click anywhere on the map to drop a pin.', 'vpg-v2' ); ?></small>
            </label>
        </div>

        <div id="vpg-loc-map" style="height:380px;border-radius:6px;border:1px solid #c3c4c7;margin:1rem 0"></div>

        <div class="vpg-loc-meta__row vpg-loc-meta__grid">
            <label>
                <span><?php esc_html_e( 'Latitude', 'vpg-v2' ); ?></span>
                <input type="text" name="<?php echo esc_attr( $keys['lat'] ); ?>" id="vpg-loc-lat" value="<?php echo esc_attr( $lat ); ?>" placeholder="48.2082" readonly>
            </label>
            <label>
                <span><?php esc_html_e( 'Longitude', 'vpg-v2' ); ?></span>
                <input type="text" name="<?php echo esc_attr( $keys['lng'] ); ?>" id="vpg-loc-lng" value="<?php echo esc_attr( $lng ); ?>" placeholder="16.3738" readonly>
            </label>
            <label>
                <span><?php esc_html_e( 'District', 'vpg-v2' ); ?></span>
                <input type="text" name="<?php echo esc_attr( $keys['district'] ); ?>" id="vpg-loc-district" value="<?php echo esc_attr( $district ); ?>" placeholder="<?php esc_attr_e( '1010 · Innere Stadt', 'vpg-v2' ); ?>">
            </label>
        </div>

        <p>
            <button type="button" class="button button-link-delete" id="vpg-loc-clear"><?php esc_html_e( 'Clear pin', 'vpg-v2' ); ?></button>
            <?php if ( $lat && $lng ) : ?>
                <a class="button-link" href="https://www.openstreetmap.org/?mlat=<?php echo esc_attr( $lat ); ?>&mlon=<?php echo esc_attr( $lng ); ?>#map=17/<?php echo esc_attr( $lat ); ?>/<?php echo esc_attr( $lng ); ?>" target="_blank"><?php esc_html_e( 'Open in OSM', 'vpg-v2' ); ?> ↗</a>
            <?php endif; ?>
        </p>
    </div>

    <style>
        .vpg-loc-meta__row { margin: 1rem 0; }
        .vpg-loc-meta__row label { display: block; }
        .vpg-loc-meta__row label > span { display: block; font-weight: 600; margin-bottom: .3rem; font-size: 12px; text-transform: uppercase; letter-spacing: .12em; color: #646970; }
        .vpg-loc-meta__row input { width: 100%; padding: .45rem .6rem; }
        .vpg-loc-meta__search { display: grid; grid-template-columns: 1fr auto; gap: .5rem; }
        .vpg-loc-meta__grid { display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 1rem; }
        @media (max-width: 700px) { .vpg-loc-meta__grid { grid-template-columns: 1fr; } }
        #vpg-loc-lat[readonly], #vpg-loc-lng[readonly] { background: #f6f7f7; cursor: default; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Wait for Leaflet (loaded async via `defer`)
        var tries = 0;
        var ready = function () {
            if (typeof L === 'undefined') { if (++tries < 40) return setTimeout(ready, 100); return; }
            init();
        };
        ready();

        function init() {
            var latInput = document.getElementById('vpg-loc-lat');
            var lngInput = document.getElementById('vpg-loc-lng');
            var distInput = document.getElementById('vpg-loc-district');
            var clearBtn = document.getElementById('vpg-loc-clear');
            var searchIn = document.getElementById('vpg-loc-search');
            var searchBtn = document.getElementById('vpg-loc-go');
            var mapEl    = document.getElementById('vpg-loc-map');
            if (!mapEl) return;

            var startLat = parseFloat(latInput.value) || 48.2082;
            var startLng = parseFloat(lngInput.value) || 16.3738;
            var startZ   = (latInput.value && lngInput.value) ? 16 : 12;

            var map = L.map(mapEl).setView([startLat, startLng], startZ);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker = null;
            function setPin(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                if (marker) marker.setLatLng([lat, lng]);
                else marker = L.marker([lat, lng], { draggable: true }).addTo(map).on('dragend', function (e) {
                    var p = e.target.getLatLng();
                    latInput.value = p.lat.toFixed(6);
                    lngInput.value = p.lng.toFixed(6);
                });
            }
            if (latInput.value && lngInput.value) setPin(parseFloat(latInput.value), parseFloat(lngInput.value));

            map.on('click', function (e) { setPin(e.latlng.lat, e.latlng.lng); });

            clearBtn.addEventListener('click', function () {
                latInput.value = ''; lngInput.value = '';
                if (marker) { map.removeLayer(marker); marker = null; }
            });

            // Address search via Nominatim
            function search() {
                var q = searchIn.value.trim();
                if (!q) return;
                searchBtn.disabled = true;
                searchBtn.textContent = '...';
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q + ', Vienna'), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (r) { return r.json(); })
                .then(function (results) {
                    if (results && results[0]) {
                        var lat = parseFloat(results[0].lat);
                        var lng = parseFloat(results[0].lon);
                        map.setView([lat, lng], 17);
                        setPin(lat, lng);
                        // Try to auto-fill district from address parts
                        if (!distInput.value && results[0].display_name) {
                            var parts = results[0].display_name.split(',').map(function (s) { return s.trim(); });
                            var dist = parts.find(function (p) { return /^\d{4}/.test(p) || /Bezirk/.test(p); });
                            if (dist) distInput.value = dist;
                        }
                    } else {
                        alert('<?php echo esc_js( __( 'Address not found · click the map instead.', 'vpg-v2' ) ); ?>');
                    }
                })
                .catch(function () { alert('<?php echo esc_js( __( 'Search failed · click the map to drop a pin.', 'vpg-v2' ) ); ?>'); })
                .finally(function () { searchBtn.disabled = false; searchBtn.textContent = '<?php echo esc_js( __( 'Find', 'vpg-v2' ) ); ?>'; });
            }
            searchBtn.addEventListener('click', search);
            searchIn.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); search(); } });

            // Fix map sizing when WP's metabox toggles
            setTimeout(function () { map.invalidateSize(); }, 300);
        }
    });
    </script>
    <?php
}

/* ─── Save handler ────────────────────────────────────────────── */
add_action( 'save_post', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_location_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['vpg_location_meta_nonce'], 'vpg_save_location_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $post_type = get_post_type( $post_id );
    $keys = VPG_LOC_META_TYPES[ $post_type ] ?? null;
    if ( ! $keys ) return;

    foreach ( $keys as $field => $meta_key ) {
        if ( ! isset( $_POST[ $meta_key ] ) ) continue;
        $val = sanitize_text_field( wp_unslash( $_POST[ $meta_key ] ) );

        // Validate lat/lng are numeric and in range
        if ( $field === 'lat' && ( $val === '' || ! is_numeric( $val ) || abs( (float) $val ) > 90 ) )  { delete_post_meta( $post_id, $meta_key ); continue; }
        if ( $field === 'lng' && ( $val === '' || ! is_numeric( $val ) || abs( (float) $val ) > 180 ) ) { delete_post_meta( $post_id, $meta_key ); continue; }

        if ( $val === '' ) delete_post_meta( $post_id, $meta_key );
        else                update_post_meta( $post_id, $meta_key, $val );
    }

    // Also write the legacy "lat, lng" combined field so anything that still
    // reads the v1 ACF key (coordinates / studio_coordinates / shop_coordinates)
    // stays in sync. Future code only needs the split fields, but old reports,
    // exports and the v1 archive plugin keep working.
    $combo_key = [
        'vpg_location' => 'coordinates',
        'vpg_studio'   => 'studio_coordinates',
        'vpg_shop'     => 'shop_coordinates',
    ][ $post_type ] ?? '';
    $sav_lat = get_post_meta( $post_id, $keys['lat'], true );
    $sav_lng = get_post_meta( $post_id, $keys['lng'], true );
    if ( $combo_key && $sav_lat && $sav_lng ) {
        update_post_meta( $post_id, $combo_key, $sav_lat . ', ' . $sav_lng );
    }

    // Bust the pin cache so the front-end map updates immediately
    delete_transient( 'vpg_location_pins' );
    delete_transient( 'vpg_location_districts' );
}, 10 );

/* Bust pin cache whenever any geo-coded CPT is saved — anywhere, not only
   from the meta box. Covers post-edit via Gutenberg, quick-edit, REST API. */
foreach ( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] as $cpt ) {
    add_action( 'save_post_' . $cpt, function () {
        delete_transient( 'vpg_location_pins' );
        delete_transient( 'vpg_location_districts' );
    } );
}
