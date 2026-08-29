<?php
/**
 * VPG v3 — Q7 · 0924 public JSON API, read-only, versioned.
 *
 *   GET /api/v1/            self-describing index
 *   GET /api/v1/locations   published spots · title, url, geo, district
 *   GET /api/v1/events      upcoming events · title, url, date, venue
 *
 * Open data, CC BY — the map belongs to the city. Light per-IP rate
 * limit, 5-minute server cache, CORS open.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    add_rewrite_rule( '^api/v1/?$', 'index.php?vpg_api=index', 'top' );
    add_rewrite_rule( '^api/v1/docs/?$', 'index.php?vpg_api=docs', 'top' );                       // 1022
    add_rewrite_rule( '^api/v1/locations\.geojson$', 'index.php?vpg_api=locations.geojson', 'top' ); // 1019
    add_rewrite_rule( '^api/v1/(locations|events)/?$', 'index.php?vpg_api=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_api'; return $v; } );

add_action( 'template_redirect', function () {
    $route = get_query_var( 'vpg_api' );
    if ( ! $route ) return;

    nocache_headers();
    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Access-Control-Allow-Origin: *' );
    header( 'X-VPG-API-Version: 1' );

    // 120 requests per hour per IP is plenty for reading a city.
    $ip   = substr( md5( $_SERVER['REMOTE_ADDR'] ?? '' ), 0, 12 );
    $hits = (int) get_transient( 'vpg_api_' . $ip );
    if ( $hits >= 120 ) {
        status_header( 429 );
        echo wp_json_encode( [ 'error' => 'rate_limited', 'retry_after' => 3600 ] );
        exit;
    }
    set_transient( 'vpg_api_' . $ip, $hits + 1, HOUR_IN_SECONDS );

    if ( $route === 'index' ) {
        echo wp_json_encode( [
            'name'    => get_bloginfo( 'name' ) . ' API',
            'version' => 1,
            'license' => 'CC BY 4.0 — credit “Vienna Photo Group” with a link',
            'routes'  => [
                '/api/v1/locations'         => 'published photo spots · id, title, url, lat, lng, district, type, checked_at',
                '/api/v1/locations.geojson' => 'the same spots as a GeoJSON FeatureCollection (QGIS, Leaflet, Mapbox)',
                '/api/v1/events'            => 'upcoming events · id, title, url, date, venue, lat, lng',
                '/api/v1/docs'              => 'human-readable documentation with curl examples',
            ],
            'contact'  => get_option( 'admin_email' ),
            'stability'=> 'IDs are stable forever; fields are only ever added, never renamed within v1.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        exit;
    }

    // 1022 · a page a human can read, not just an index blob
    if ( $route === 'docs' ) {
        header( 'Content-Type: text/html; charset=utf-8' );
        $base = home_url( '/api/v1' );
        $ex   = fn( $p ) => 'curl -s ' . esc_html( $base . $p );
        ?><!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1">
        <title>VPG API v1 · docs</title>
        <style>body{font:15px/1.6 -apple-system,system-ui,sans-serif;max-width:720px;margin:40px auto;padding:0 20px;color:#0B0B0B}
        h1{font-weight:800}code,pre{font-family:ui-monospace,monospace;background:#F5F4F1;padding:.1em .3em}
        pre{padding:12px 14px;overflow:auto}a{color:#E5341F}section{border-top:1px solid #E6E5E1;padding:18px 0}</style>
        <h1>Vienna Photo Group · API v1</h1>
        <p>Read-only, open, versioned. Licensed <strong>CC BY 4.0</strong> — credit “Vienna Photo Group” with a link. IDs never change; fields are only ever added within v1. Rate limit: 120 requests/hour per IP. CORS is open.</p>
        <section><h2>Locations</h2><p>Every published photo spot, studio and shop.</p><pre><?php echo $ex( '/locations' ); ?></pre>
        <p>As GeoJSON for mapping tools:</p><pre><?php echo $ex( '/locations.geojson' ); ?></pre></section>
        <section><h2>Events</h2><p>Upcoming events, soonest first.</p><pre><?php echo $ex( '/events' ); ?></pre></section>
        <section><h2>Index</h2><p>The machine-readable route list:</p><pre><?php echo $ex( '' ); ?></pre></section>
        <p style="color:#6A6A6A">Questions: <a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"><?php echo esc_html( get_option( 'admin_email' ) ); ?></a></p>
        <?php
        exit;
    }

    $cached = get_transient( 'vpg_api_body_' . $route );
    if ( $cached ) { echo $cached; exit; }

    $out = [];
    if ( $route === 'locations' ) {
        foreach ( get_posts( [ 'post_type' => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], 'post_status' => 'publish', 'posts_per_page' => 500 ] ) as $p ) {
            $pre   = $p->post_type === 'vpg_shop' ? 'shop' : ( $p->post_type === 'vpg_studio' ? 'studio' : 'location' );
            $lat   = get_post_meta( $p->ID, $pre . '_lat', true );
            $lng   = get_post_meta( $p->ID, $pre . '_lng', true );
            $out[] = [
                'id'         => $p->ID,
                'title'      => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
                'url'        => get_permalink( $p ),
                'type'       => str_replace( 'vpg_', '', $p->post_type ),
                'lat'        => $lat !== '' ? (float) $lat : null,
                'lng'        => $lng !== '' ? (float) $lng : null,
                'district'   => (string) ( get_post_meta( $p->ID, 'location_district', true ) ?: get_post_meta( $p->ID, 'shop_district', true ) ),
                'checked_at' => (string) get_post_meta( $p->ID, '_vpg_checked_at', true ),
            ];
        }
    } elseif ( $route === 'events' ) {
        foreach ( get_posts( [ 'post_type' => 'vpg_event', 'post_status' => 'publish', 'posts_per_page' => 100,
            'meta_key' => '_vpg_event_date', 'orderby' => 'meta_value', 'order' => 'ASC',
            'meta_query' => [ [ 'key' => '_vpg_event_date', 'value' => gmdate( 'Y-m-d' ), 'compare' => '>=' ] ] ] ) as $p ) {
            $out[] = [
                'id'    => $p->ID,
                'title' => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
                'url'   => get_permalink( $p ),
                'date'  => (string) get_post_meta( $p->ID, '_vpg_event_date', true ),
                'venue' => (string) get_post_meta( $p->ID, '_vpg_event_venue', true ),
                'lat'   => ( $v = get_post_meta( $p->ID, '_vpg_event_lat', true ) ) !== '' ? (float) $v : null,
                'lng'   => ( $v = get_post_meta( $p->ID, '_vpg_event_lng', true ) ) !== '' ? (float) $v : null,
            ];
        }
    }

    if ( $route === 'locations.geojson' ) {                          // 1019 · GeoJSON FeatureCollection
        $features = [];
        foreach ( get_posts( [ 'post_type' => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], 'post_status' => 'publish', 'posts_per_page' => 500 ] ) as $p ) {
            $pre = $p->post_type === 'vpg_shop' ? 'shop' : ( $p->post_type === 'vpg_studio' ? 'studio' : 'location' );
            $lat = get_post_meta( $p->ID, $pre . '_lat', true );
            $lng = get_post_meta( $p->ID, $pre . '_lng', true );
            if ( $lat === '' || $lng === '' ) continue;
            $features[] = [
                'type'       => 'Feature',
                'geometry'   => [ 'type' => 'Point', 'coordinates' => [ (float) $lng, (float) $lat ] ],
                'properties' => [
                    'id'       => $p->ID,
                    'title'    => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
                    'type'     => str_replace( 'vpg_', '', $p->post_type ),
                    'url'      => get_permalink( $p ),
                    'district' => (string) ( get_post_meta( $p->ID, 'location_district', true ) ?: get_post_meta( $p->ID, 'shop_district', true ) ),
                ],
            ];
        }
        header( 'Content-Type: application/geo+json; charset=utf-8' );
        $body = wp_json_encode( [ 'type' => 'FeatureCollection', 'license' => 'CC BY 4.0', 'features' => $features ], JSON_UNESCAPED_SLASHES );
        set_transient( 'vpg_api_body_' . $route, $body, 5 * MINUTE_IN_SECONDS );
        echo $body;
        exit;
    }

    $body = wp_json_encode( [ 'version' => 1, 'count' => count( $out ), 'generated' => gmdate( 'c' ), 'items' => $out ], JSON_UNESCAPED_SLASHES );
    set_transient( 'vpg_api_body_' . $route, $body, 5 * MINUTE_IN_SECONDS );
    echo $body;
    exit;
} );
