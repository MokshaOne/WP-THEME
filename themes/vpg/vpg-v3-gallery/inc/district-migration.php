<?php
/**
 * VPG v2 — District derivation migration.
 *
 * Uses OSM Nominatim reverse-geocoding to fill the *_district meta key for
 * locations, studios, and shops that have lat/lng but no district yet.
 *
 * Politeness · 1.1 s between live API calls (Nominatim usage policy: max 1
 * req/sec). Batched: 20 posts per click → ~22 s runtime per batch · user
 * clicks the button repeatedly until 0 remain.
 *
 * Coord-keyed cache · 7-day transient per (lat,lng) so re-runs don't hit
 * the API again for the same pin.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_DISTRICT_BATCH_SIZE  = 20;
const VPG_NOMINATIM_DELAY_MS   = 1100;
const VPG_NOMINATIM_UA         = 'VPG-v2 District Migration (viennaphotogroup.com)';

/** Per-CPT lat/lng/district meta-key map · matches location-meta.php */
function vpg_district_meta_map() {
    return [
        'vpg_location' => [ 'lat' => 'location_lat', 'lng' => 'location_lng', 'district' => 'location_district' ],
        'vpg_studio'   => [ 'lat' => 'studio_lat',   'lng' => 'studio_lng',   'district' => 'location_district' ],
        'vpg_shop'     => [ 'lat' => 'shop_lat',     'lng' => 'shop_lng',     'district' => 'shop_district' ],
    ];
}

/* ════════════════════════════════════════════════════════════════ */
/*  Status — counts per CPT for the admin UI                         */
/* ════════════════════════════════════════════════════════════════ */
function vpg_district_status() {
    $out = [];
    foreach ( vpg_district_meta_map() as $cpt => $keys ) {
        $todo_ids = vpg_district_find_todo( $cpt, $keys, -1 );
        $out[ $cpt ] = [
            'todo'  => count( $todo_ids ),
            'done'  => vpg_district_count_done( $cpt, $keys ),
            'label' => ( $pto = get_post_type_object( $cpt ) ) ? $pto->labels->name : $cpt,
        ];
    }
    return $out;
}

function vpg_district_find_todo( $cpt, $keys, $per_page = VPG_DISTRICT_BATCH_SIZE ) {
    $q = new WP_Query( [
        'post_type'      => $cpt,
        'post_status'    => 'any',
        'posts_per_page' => $per_page,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [ 'key' => $keys['lat'], 'compare' => 'EXISTS' ],
            [ 'key' => $keys['lng'], 'compare' => 'EXISTS' ],
            [
                'relation' => 'OR',
                [ 'key' => $keys['district'], 'compare' => 'NOT EXISTS' ],
                [ 'key' => $keys['district'], 'value' => '', 'compare' => '=' ],
            ],
        ],
    ] );
    return $q->posts;
}

function vpg_district_count_done( $cpt, $keys ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT p.ID)
         FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
         WHERE p.post_type = %s
           AND m.meta_key = %s
           AND m.meta_value <> ''",
        $cpt, $keys['district']
    ) );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Run one batch · returns counts + log                             */
/* ════════════════════════════════════════════════════════════════ */
function vpg_district_run_batch() {
    $filled = 0; $skipped = 0; $failed = 0; $log = [];

    foreach ( vpg_district_meta_map() as $cpt => $keys ) {
        $ids = vpg_district_find_todo( $cpt, $keys );
        foreach ( $ids as $pid ) {
            $lat = (float) get_post_meta( $pid, $keys['lat'], true );
            $lng = (float) get_post_meta( $pid, $keys['lng'], true );
            if ( ! $lat || ! $lng ) { $skipped++; continue; }

            $postcode = vpg_nominatim_reverse( $lat, $lng );
            if ( ! $postcode ) {
                $failed++;
                $log[] = sprintf( '#%d (%s) · no postcode returned',
                    $pid, get_post_type_object( $cpt )->labels->singular_name );
                continue;
            }
            $district = vpg_postcode_to_vienna_district( $postcode );
            if ( ! $district ) {
                $failed++;
                $log[] = sprintf( '#%d (%s) · outside Vienna · pc %s',
                    $pid, get_post_type_object( $cpt )->labels->singular_name, esc_html( $postcode ) );
                continue;
            }
            // Store as 4-digit Wien postcode form: 1 + 2-digit-district + 0
            //   district  1 → 1010 (Innere Stadt) · district 23 → 1230 (Liesing)
            $stored = sprintf( '1%02d0', $district );
            update_post_meta( $pid, $keys['district'], $stored );
            $filled++;
            $log[] = sprintf( '#%d (%s) · %s',
                $pid, get_post_type_object( $cpt )->labels->singular_name, $stored );
        }
    }

    // Invalidate caches so the public map + filter reflect new districts
    delete_transient( 'vpg_location_pins' );
    delete_transient( 'vpg_location_districts' );

    return [ 'filled' => $filled, 'skipped' => $skipped, 'failed' => $failed, 'log' => $log ];
}

/* ════════════════════════════════════════════════════════════════ */
/*  Nominatim reverse-geocode · postcode string or null              */
/* ════════════════════════════════════════════════════════════════ */
function vpg_nominatim_reverse( $lat, $lng ) {
    // Cache per coordinate (5dp ≈ 1 m granularity)
    $coord_key = sprintf( '%.5f_%.5f', $lat, $lng );
    $tr_key    = 'vpg_nomi_' . substr( md5( $coord_key ), 0, 32 );

    $cached = get_transient( $tr_key );
    if ( $cached !== false ) return $cached ?: null;

    // Politeness throttle (only between LIVE calls in this batch)
    static $last_call_ms = 0;
    $elapsed = ( microtime( true ) * 1000 ) - $last_call_ms;
    if ( $last_call_ms > 0 && $elapsed < VPG_NOMINATIM_DELAY_MS ) {
        usleep( (int) ( VPG_NOMINATIM_DELAY_MS - $elapsed ) * 1000 );
    }
    $last_call_ms = microtime( true ) * 1000;

    $url = add_query_arg( [
        'lat'            => $lat,
        'lon'            => $lng,
        'format'         => 'json',
        'zoom'           => 18,
        'addressdetails' => 1,
    ], 'https://nominatim.openstreetmap.org/reverse' );

    $resp = wp_remote_get( $url, [
        'timeout'    => 8,
        'user-agent' => VPG_NOMINATIM_UA,
        'headers'    => [ 'Accept-Language' => 'de,en' ],
    ] );

    if ( is_wp_error( $resp ) || (int) wp_remote_retrieve_response_code( $resp ) !== 200 ) {
        set_transient( $tr_key, '', HOUR_IN_SECONDS ); // negative cache 1 h
        return null;
    }

    $data = json_decode( wp_remote_retrieve_body( $resp ), true );
    $pc   = isset( $data['address']['postcode'] ) ? (string) $data['address']['postcode'] : '';
    if ( preg_match( '/(\d{4})/', $pc, $m ) ) $pc = $m[1];

    set_transient( $tr_key, $pc, 7 * DAY_IN_SECONDS );
    return $pc ?: null;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Postcode → Vienna district number (1–23) · null if non-Vienna   */
/* ════════════════════════════════════════════════════════════════ */
function vpg_postcode_to_vienna_district( $postcode ) {
    $pc = trim( (string) $postcode );
    if ( ! preg_match( '/^1(\d{2})0$/', $pc, $m ) ) return null;
    $d = (int) $m[1];
    return ( $d >= 1 && $d <= 23 ) ? $d : null;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Repair · re-derives ALL districts from coords using the          */
/*  Nominatim coord-cache. Use this once after fixing the sprintf    */
/*  bug to overwrite buggy values (1001-1023 → 1010-1230).           */
/*  Idempotent · safe to re-run · cache makes it instant.            */
/* ════════════════════════════════════════════════════════════════ */
function vpg_district_repair_format() {
    @set_time_limit( 180 );
    @ini_set( 'memory_limit', '256M' );

    $fixed = 0;
    foreach ( vpg_district_meta_map() as $cpt => $keys ) {
        $ids = get_posts( [
            'post_type'      => $cpt,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_query'     => [
                [ 'key' => $keys['lat'], 'compare' => 'EXISTS' ],
                [ 'key' => $keys['lng'], 'compare' => 'EXISTS' ],
            ],
        ] );
        foreach ( $ids as $pid ) {
            $lat = (float) get_post_meta( $pid, $keys['lat'], true );
            $lng = (float) get_post_meta( $pid, $keys['lng'], true );
            if ( ! $lat || ! $lng ) continue;
            // Mostly cache-hits since previous batch already populated transients
            $pc = vpg_nominatim_reverse( $lat, $lng );
            $d  = vpg_postcode_to_vienna_district( $pc );
            if ( $d ) {
                update_post_meta( $pid, $keys['district'], sprintf( '1%02d0', $d ) );
                $fixed++;
            }
        }
    }
    delete_transient( 'vpg_location_pins' );
    delete_transient( 'vpg_location_districts' );

    return $fixed;
}

add_action( 'admin_post_vpg_repair_districts', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden', 403 );
    check_admin_referer( 'vpg_repair_districts' );

    $fixed = vpg_district_repair_format();
    set_transient( 'vpg_district_last_repair', $fixed, 10 * MINUTE_IN_SECONDS );

    wp_safe_redirect( admin_url( 'tools.php?page=vpg-setup&vpg_repair=done#vpg-derive' ) );
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin-post handler · "Derive next batch"                         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_derive_districts', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden', 403 );
    check_admin_referer( 'vpg_derive_districts' );

    @set_time_limit( 90 );
    @ini_set( 'memory_limit', '256M' );

    $result = vpg_district_run_batch();
    set_transient( 'vpg_district_last_run', $result, 10 * MINUTE_IN_SECONDS );

    wp_safe_redirect( admin_url( 'tools.php?page=vpg-setup&vpg_derive=done#vpg-derive' ) );
    exit;
} );
