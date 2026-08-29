<?php
/**
 * VPG v2 — helpers · tiny utilities used across templates.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Convert  <em>Word</em>  in content fields safely without losing semantics */
function vpg_em( $html ) {
    return wp_kses( $html, [ 'em' => [], 'strong' => [], 'br' => [], 'span' => [ 'class' => [] ], 'small' => [], 'sup' => [] ] );
}

/* Current request URL (used by the Gallery header to flag the active nav item) */
function vpg_current_url() {
    $scheme = is_ssl() ? 'https://' : 'http://';
    $host   = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    return esc_url_raw( $scheme . $host . strtok( $uri, '?' ) );
}

/* Site-wide identity (used by header/footer/schema) */
function vpg_identity() {
    return [
        'brand'    => get_bloginfo( 'name' ),
        'tagline'  => get_bloginfo( 'description' ),
        'email'    => get_theme_mod( 'vpg_email',    get_option( 'admin_email' ) ),
        'location' => get_theme_mod( 'vpg_location', 'Wien · UTC+1' ),
        'booking'  => get_theme_mod( 'vpg_booking',  'Q3 · slots open' ),
        'since'    => get_theme_mod( 'vpg_since',    '2018' ),
    ];
}

/* Format a date like "Jun MMXXVI" → fancy month + Roman year. Used in single-magazine. */
function vpg_roman_date( $ts ) {
    $month = date_i18n( 'F', $ts );
    $year  = (int) date_i18n( 'Y', $ts );
    return $month . ' ' . vpg_roman( $year );
}

function vpg_roman( $n ) {
    $map = [ 1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I' ];
    $out = '';
    foreach ( $map as $v => $r ) {
        while ( $n >= $v ) { $out .= $r; $n -= $v; }
    }
    return $out;
}

/* Count helper · returns "n type" with proper pluralisation */
function vpg_count_label( $post_type, $singular = '', $plural = '' ) {
    $c = (int) ( wp_count_posts( $post_type )->publish ?? 0 );
    if ( ! $singular || ! $plural ) return $c;
    return sprintf( _n( $singular, $plural, $c, 'vpg-v2' ), $c );
}

/* Member badge · returns true if current user has the 'vpg_member' role */
function vpg_is_member() {
    if ( ! is_user_logged_in() ) return false;
    $u = wp_get_current_user();
    return in_array( 'vpg_member', (array) $u->roles, true ) || in_array( 'administrator', (array) $u->roles, true );
}

/* Safe ACF read · works whether ACF Pro is installed or not */
function vpg_field( $key, $post_id = null ) {
    if ( function_exists( 'get_field' ) ) return get_field( $key, $post_id );
    return get_post_meta( $post_id ?: get_the_ID(), $key, true );
}

/* Get lat/lng for a post · supports both the v2 split-field model
   (location_lat + location_lng) AND the v1 ACF "Lat, Lng" combined field
   (coordinates / studio_coordinates / shop_coordinates).
   Returns [lat, lng] floats, or null if nothing valid is set. */
function vpg_get_coords( $post_id ) {
    $type = get_post_type( $post_id );

    // v2 split fields
    $split_keys = [
        'vpg_location' => [ 'location_lat', 'location_lng', 'coordinates' ],
        'vpg_studio'   => [ 'studio_lat',   'studio_lng',   'studio_coordinates' ],
        'vpg_shop'     => [ 'shop_lat',     'shop_lng',     'shop_coordinates' ],
    ];
    if ( ! isset( $split_keys[ $type ] ) ) return null;

    list( $latKey, $lngKey, $comboKey ) = $split_keys[ $type ];

    $lat = get_post_meta( $post_id, $latKey, true );
    $lng = get_post_meta( $post_id, $lngKey, true );
    if ( $lat !== '' && $lng !== '' && is_numeric( $lat ) && is_numeric( $lng ) ) {
        return [ (float) $lat, (float) $lng ];
    }

    // v1 combined field · "lat, lng" or "lat,lng"
    $combo = function_exists( 'get_field' ) ? get_field( $comboKey, $post_id ) : get_post_meta( $post_id, $comboKey, true );
    if ( $combo && is_string( $combo ) && strpos( $combo, ',' ) !== false ) {
        $parts = array_map( 'trim', explode( ',', $combo, 2 ) );
        if ( count( $parts ) === 2 && is_numeric( $parts[0] ) && is_numeric( $parts[1] ) ) {
            $lat = (float) $parts[0];
            $lng = (float) $parts[1];
            if ( abs( $lat ) <= 90 && abs( $lng ) <= 180 ) return [ $lat, $lng ];
        }
    }

    return null;
}

/* Reading-time estimate in minutes · ~220 wpm by default */
function vpg_reading_time( $text, $wpm = 220 ) {
    $words = str_word_count( wp_strip_all_tags( (string) $text ) );
    return max( 1, (int) ceil( $words / max( 60, $wpm ) ) );
}

/* Reading-time for a magazine issue (sum of all article bodies) */
function vpg_issue_reading_time( $issue_id ) {
    $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $issue_id ) : [];
    $words    = 0;
    foreach ( $articles as $a ) $words += str_word_count( wp_strip_all_tags( $a['body'] ?? '' ) );
    return max( 1, (int) ceil( $words / 220 ) );
}

/* Render a single skin-style chip · used everywhere */
function vpg_chip( $post_type, $label = '' ) {
    $cls = [
        'vpg_event'    => 'event',
        'vpg_location' => 'loc',
        'vpg_magazine' => 'mag',
        'vpg_review'   => 'review',
        'vpg_shop'     => 'shop',
        'vpg_studio'   => 'studio',
        'vpg_tutorial' => 'tut',
    ];
    $modifier = $cls[ $post_type ] ?? '';
    $defaults = [
        'vpg_event'    => 'Event',
        'vpg_location' => 'Location',
        'vpg_magazine' => 'Magazine',
        'vpg_review'   => 'Review',
        'vpg_shop'     => 'Shop',
        'vpg_studio'   => 'Studio',
        'vpg_tutorial' => 'Tutorial',
    ];
    $label = $label ?: ( $defaults[ $post_type ] ?? ucfirst( str_replace( 'vpg_', '', $post_type ) ) );
    printf(
        '<span class="vpg-chip vpg-chip--%s"><span class="vpg-chip__dot"></span> %s</span>',
        esc_attr( $modifier ),
        esc_html( $label )
    );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Event → .ics download · "Add to calendar"                        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_nopriv_vpg_event_ics', 'vpg_event_ics' );
add_action( 'admin_post_vpg_event_ics',        'vpg_event_ics' );
function vpg_event_ics() {
    $id    = (int) ( $_GET['event'] ?? 0 );
    $event = $id ? get_post( $id ) : null;
    if ( ! $event || $event->post_type !== 'vpg_event' || $event->post_status !== 'publish' ) {
        wp_die( 'Event not found', 404 );
    }

    $date  = get_post_meta( $id, '_vpg_event_date',  true );
    $venue = get_post_meta( $id, '_vpg_event_venue', true );
    $start = $date ? strtotime( $date ) : false;
    if ( ! $start ) $start = strtotime( $event->post_date );

    // All-day event · the date meta is a plain date without a time
    $dtstart = gmdate( 'Ymd', $start );
    $dtend   = gmdate( 'Ymd', $start + DAY_IN_SECONDS );

    $esc = function ( $s ) {
        return str_replace( [ '\\', ';', ',', "\n" ], [ '\\\\', '\;', '\,', '\n' ], (string) $s );
    };

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Vienna Photo Group//Events//EN',
        'BEGIN:VEVENT',
        'UID:vpg-event-' . $id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
        'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
        'DTSTART;VALUE=DATE:' . $dtstart,
        'DTEND;VALUE=DATE:' . $dtend,
        'SUMMARY:' . $esc( $event->post_title ),
        'DESCRIPTION:' . $esc( wp_strip_all_tags( get_the_excerpt( $event ) ?: wp_trim_words( $event->post_content, 40 ) ) ),
        'LOCATION:' . $esc( $venue ?: 'Wien' ),
        'URL:' . esc_url_raw( get_permalink( $id ) ),
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    nocache_headers();
    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . sanitize_title( $event->post_title ) . '.ics"' );
    echo implode( "\r\n", $lines ) . "\r\n";
    exit;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Opening hours · tolerant parser for "Mon–Fri 10–18; Sat 10–14"   */
/*  Understands EN/DE day names, ranges, 10 / 10:30 / 1030 times.    */
/*  Returns true (open), false (closed) or null (couldn't parse).    */
/* ════════════════════════════════════════════════════════════════ */
function vpg_hours_open_now( $text ) {
    $days = [ 'mo' => 1, 'mon' => 1, 'tu' => 2, 'tue' => 2, 'di' => 2, 'we' => 3, 'wed' => 3, 'mi' => 3,
              'th' => 4, 'thu' => 4, 'do' => 4, 'fr' => 5, 'fri' => 5, 'sa' => 6, 'sat' => 6, 'su' => 7, 'sun' => 7, 'so' => 7 ];

    $now_day = (int) current_time( 'N' );          // 1 = Monday
    $now_min = (int) current_time( 'G' ) * 60 + (int) current_time( 'i' );

    $norm = strtolower( str_replace( [ '–', '—', 'bis' ], '-', $text ) );
    $segments = preg_split( '/[;,]/', $norm ) ?: [];
    $parsed_any = false;

    $to_min = function ( $t ) {
        $t = trim( $t );
        if ( preg_match( '/^(\d{1,2})[:.](\d{2})$/', $t, $m ) ) return (int) $m[1] * 60 + (int) $m[2];
        if ( preg_match( '/^(\d{1,2})(\d{2})$/', $t, $m ) && (int) $m[1] <= 24 ) return (int) $m[1] * 60 + (int) $m[2];
        if ( preg_match( '/^(\d{1,2})$/', $t, $m ) ) return (int) $m[1] * 60;
        return null;
    };

    foreach ( $segments as $seg ) {
        // day part · "mon-fri", "sat", "mo-fr" — letters before the first digit
        if ( ! preg_match( '/^\s*([a-zäöü.\-\s]+?)\s+([\d].*)$/', trim( $seg ), $m ) ) continue;
        $daypart  = preg_replace( '/[.\s]/', '', $m[1] );
        $timepart = $m[2];

        $d_from = $d_to = null;
        if ( preg_match( '/^([a-zäöü]+)-([a-zäöü]+)$/', $daypart, $dm ) ) {
            $d_from = $days[ substr( $dm[1], 0, 3 ) ] ?? $days[ substr( $dm[1], 0, 2 ) ] ?? null;
            $d_to   = $days[ substr( $dm[2], 0, 3 ) ] ?? $days[ substr( $dm[2], 0, 2 ) ] ?? null;
        } else {
            $d_from = $d_to = $days[ substr( $daypart, 0, 3 ) ] ?? $days[ substr( $daypart, 0, 2 ) ] ?? null;
        }
        if ( ! $d_from || ! $d_to ) continue;

        if ( ! preg_match( '/([\d:.]+)\s*-\s*([\d:.]+)/', $timepart, $tm ) ) continue;
        $t_from = $to_min( $tm[1] );
        $t_to   = $to_min( $tm[2] );
        if ( $t_from === null || $t_to === null ) continue;
        if ( $t_to <= $t_from ) $t_to += 24 * 60; // over midnight

        $parsed_any = true;
        $in_days = ( $d_from <= $d_to )
            ? ( $now_day >= $d_from && $now_day <= $d_to )
            : ( $now_day >= $d_from || $now_day <= $d_to );
        if ( $in_days && $now_min >= $t_from && $now_min < $t_to ) return true;
    }

    return $parsed_any ? false : null;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Weather · Open-Meteo current conditions, cached 30 min           */
/*  Free, no API key. Returns ['temp' => '18°', 'label' => …] or null */
/* ════════════════════════════════════════════════════════════════ */
function vpg_weather( $lat, $lng ) {
    $key   = 'vpg_wx_' . md5( round( $lat, 2 ) . ',' . round( $lng, 2 ) );
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) return $cached;
    if ( $cached === 'none' ) return null;

    $url = add_query_arg( [
        'latitude'        => round( $lat, 4 ),
        'longitude'       => round( $lng, 4 ),
        'current_weather' => 'true',
    ], 'https://api.open-meteo.com/v1/forecast' );

    $res = wp_remote_get( $url, [ 'timeout' => 4 ] );
    if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) {
        set_transient( $key, 'none', 10 * MINUTE_IN_SECONDS );
        return null;
    }
    $data = json_decode( wp_remote_retrieve_body( $res ), true );
    $cw   = $data['current_weather'] ?? null;
    if ( ! $cw || ! isset( $cw['temperature'] ) ) {
        set_transient( $key, 'none', 10 * MINUTE_IN_SECONDS );
        return null;
    }

    $codes = [
        0 => __( 'clear', 'vpg-v2' ), 1 => __( 'mostly clear', 'vpg-v2' ), 2 => __( 'partly cloudy', 'vpg-v2' ),
        3 => __( 'overcast', 'vpg-v2' ), 45 => __( 'fog', 'vpg-v2' ), 48 => __( 'fog', 'vpg-v2' ),
        51 => __( 'drizzle', 'vpg-v2' ), 53 => __( 'drizzle', 'vpg-v2' ), 55 => __( 'drizzle', 'vpg-v2' ),
        61 => __( 'rain', 'vpg-v2' ), 63 => __( 'rain', 'vpg-v2' ), 65 => __( 'heavy rain', 'vpg-v2' ),
        71 => __( 'snow', 'vpg-v2' ), 73 => __( 'snow', 'vpg-v2' ), 75 => __( 'snow', 'vpg-v2' ),
        80 => __( 'showers', 'vpg-v2' ), 81 => __( 'showers', 'vpg-v2' ), 82 => __( 'showers', 'vpg-v2' ),
        95 => __( 'thunderstorm', 'vpg-v2' ),
    ];

    $out = [
        'temp'  => round( (float) $cw['temperature'] ) . '°',
        'label' => $codes[ (int) ( $cw['weathercode'] ?? -1 ) ] ?? '',
    ];
    set_transient( $key, $out, 30 * MINUTE_IN_SECONDS );
    return $out;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Geo export · members download the map as GeoJSON or GPX          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_geo_export', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );

    $format = ( $_GET['format'] ?? '' ) === 'gpx' ? 'gpx' : 'geojson';
    $rows   = [];

    foreach ( [ 'location' => 'vpg_location', 'studio' => 'vpg_studio', 'shop' => 'vpg_shop' ] as $t => $cpt ) {
        $items = get_posts( [ 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        foreach ( $items as $p ) {
            $coords = vpg_get_coords( $p->ID );
            if ( ! $coords ) continue;
            $rows[] = [
                'lat'   => (float) $coords[0],
                'lng'   => (float) $coords[1],
                'name'  => get_the_title( $p ),
                'type'  => $t,
                'url'   => get_permalink( $p ),
            ];
        }
    }

    nocache_headers();
    if ( $format === 'gpx' ) {
        header( 'Content-Type: application/gpx+xml; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="vpg-map.gpx"' );
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<gpx version="1.1" creator="Vienna Photo Group" xmlns="http://www.topografix.com/GPX/1/1">' . "\n";
        foreach ( $rows as $r ) {
            printf(
                "  <wpt lat=\"%.6F\" lon=\"%.6F\"><name>%s</name><type>%s</type><link href=\"%s\"/></wpt>\n",
                $r['lat'], $r['lng'],
                esc_html( $r['name'] ), esc_html( $r['type'] ), esc_url( $r['url'] )
            );
        }
        echo '</gpx>';
    } else {
        header( 'Content-Type: application/geo+json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="vpg-map.geojson"' );
        $features = array_map( function ( $r ) {
            return [
                'type'       => 'Feature',
                'geometry'   => [ 'type' => 'Point', 'coordinates' => [ $r['lng'], $r['lat'] ] ],
                'properties' => [ 'name' => $r['name'], 'type' => $r['type'], 'url' => $r['url'] ],
            ];
        }, $rows );
        echo wp_json_encode( [ 'type' => 'FeatureCollection', 'features' => $features ] );
    }
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Shareable preview links · a pending/draft post gets a secret     */
/*  token URL the author (or anyone with the link) can open.         */
/* ════════════════════════════════════════════════════════════════ */
function vpg_preview_url( $post_id ) {
    $token = get_post_meta( $post_id, '_vpg_preview_token', true );
    if ( ! $token ) {
        $token = wp_generate_password( 24, false, false );
        update_post_meta( $post_id, '_vpg_preview_token', $token );
    }
    return add_query_arg( [
        'p'           => (int) $post_id,
        'post_type'   => get_post_type( $post_id ),
        'vpg_preview' => $token,
    ], home_url( '/' ) );
}

add_filter( 'posts_results', function ( $posts, $query ) {
    if ( is_admin() || ! $query->is_main_query() || $posts ) return $posts;
    if ( empty( $_GET['vpg_preview'] ) ) return $posts;

    $pid = (int) $query->get( 'p' );
    if ( ! $pid ) return $posts;
    $post = get_post( $pid );
    if ( ! $post || ! in_array( $post->post_status, [ 'pending', 'draft' ], true ) ) return $posts;

    $token = sanitize_text_field( wp_unslash( $_GET['vpg_preview'] ) );
    $saved = (string) get_post_meta( $pid, '_vpg_preview_token', true );
    if ( ! $saved || ! hash_equals( $saved, $token ) ) return $posts;

    // Valid token · surface the post and keep robots away
    add_action( 'wp_head', function () { echo '<meta name="robots" content="noindex,nofollow">' . "\n"; }, 1 );
    $post->post_status = 'publish'; // in-memory only · lets the template render
    return [ $post ];
}, 10, 2 );
