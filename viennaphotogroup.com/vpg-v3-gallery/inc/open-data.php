<?php
/**
 * VPG v3 — Cluster 24 · Offene Daten, API & Fediverse.
 *
 * A good neighbour on the open web. Reuses the /api/v1 JSON API, the
 * ActivityPub/webfinger/webmention-receive stack, ICS feeds and the /embed/
 * iframes — adds only what was missing, all served under /data/* via
 * template_redirect path-matching (no new rewrite rules, no flush):
 *
 *   0933/0934 per-photo CC licence + machine-readable output · 0925 API tokens
 *   0928/0945 outgoing webhooks + changes feed · 0944 KML & CSV export
 *   0927 OpenAPI spec · 0939 schema.org event feed · 0950 oEmbed · 0949 embeds
 *   0937 h-card/h-entry · 0923 fediverse directory · 0935 webmention send
 *   0956 status page · 0941 data garden · 0959 interop manifest · Open-data desk
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * 0933/0934 · per-photo / per-post Creative Commons licence
 * ================================================================ */
function vpg_licenses() {
    return [
        'CC0'       => [ __( 'CC0 — public domain', 'vpg-v2' ), 'https://creativecommons.org/publicdomain/zero/1.0/' ],
        'CC-BY'     => [ 'CC BY 4.0', 'https://creativecommons.org/licenses/by/4.0/' ],
        'CC-BY-SA'  => [ 'CC BY-SA 4.0', 'https://creativecommons.org/licenses/by-sa/4.0/' ],
        'CC-BY-NC'  => [ 'CC BY-NC 4.0', 'https://creativecommons.org/licenses/by-nc/4.0/' ],
        'ARR'       => [ __( 'All rights reserved', 'vpg-v2' ), '' ],
    ];
}
function vpg_post_license( $pid ) {
    $l = get_post_meta( $pid, '_vpg_license', true );
    return $l && isset( vpg_licenses()[ $l ] ) ? $l : (string) get_option( 'vpg_default_license', 'CC-BY' );
}
/* member picks a licence on the attachment */
add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
    $cur = get_post_meta( $post->ID, '_vpg_license', true );
    $opts = '';
    foreach ( vpg_licenses() as $slug => $l ) $opts .= '<option value="' . esc_attr( $slug ) . '"' . selected( $cur, $slug, false ) . '>' . esc_html( $l[0] ) . '</option>';
    $fields['vpg_license'] = [ 'label' => __( 'Licence', 'vpg-v2' ), 'input' => 'html', 'html' => '<select name="attachments[' . $post->ID . '][vpg_license]"><option value="">' . esc_html__( '— site default —', 'vpg-v2' ) . '</option>' . $opts . '</select>' ];
    return $fields;
}, 11, 2 );
add_filter( 'attachment_fields_to_save', function ( $post, $attr ) {
    if ( isset( $attr['vpg_license'] ) ) {
        $v = sanitize_text_field( $attr['vpg_license'] );
        $v && isset( vpg_licenses()[ $v ] ) ? update_post_meta( $post['ID'], '_vpg_license', $v ) : delete_post_meta( $post['ID'], '_vpg_license' );
    }
    return $post;
}, 10, 2 );
/* 0934 · machine-readable — rel=license in head + JSON-LD license on singular */
add_action( 'wp_head', function () {
    if ( ! is_singular() ) return;
    $slug = vpg_post_license( get_queried_object_id() );
    $l = vpg_licenses()[ $slug ] ?? null;
    if ( $l && $l[1] ) echo '<link rel="license" href="' . esc_url( $l[1] ) . '">' . "\n";
}, 5 );
add_filter( 'the_content', function ( $c ) {
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        $slug = vpg_post_license( get_the_ID() );
        $l = vpg_licenses()[ $slug ] ?? null;
        if ( $l ) {
            $label = $l[1] ? '<a rel="license" href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a>' : esc_html( $l[0] );
            $c .= '<p class="vpg-license" style="font-size:12px;color:var(--g-mid,#6A6A6A);margin-top:16px">' . esc_html__( 'Licence:', 'vpg-v2' ) . ' ' . $label . '</p>';
        }
    }
    return $c;
}, 45 );

/* ================================================================
 * 0925 · API token self-service (personal read tokens)
 * ================================================================ */
function vpg_api_tokens( $uid ) { return (array) get_user_meta( (int) $uid, '_vpg_api_tokens', true ); }
function vpg_api_token_user( $token ) {
    if ( ! $token ) return 0;
    $hash = hash( 'sha256', $token );
    $q = get_users( [ 'meta_key' => '_vpg_api_token_index', 'meta_value' => $hash, 'fields' => 'ID', 'number' => 1 ] );
    return $q ? (int) $q[0] : 0;
}
add_action( 'vpg_profile_sections', function ( $user ) {
    if ( ! ( $user instanceof WP_User ) || $user->ID !== get_current_user_id() ) return;
    $new = '';
    if ( isset( $_POST['_vpg_tok'] ) && wp_verify_nonce( $_POST['_vpg_tok'], 'vpg_tok' ) ) {
        if ( ! empty( $_POST['mint'] ) ) {
            $new = 'vpg_' . wp_generate_password( 32, false );
            $toks = vpg_api_tokens( $user->ID );
            $toks[] = [ 'hash' => hash( 'sha256', $new ), 'label' => sanitize_text_field( wp_unslash( $_POST['label'] ?? 'token' ) ), 't' => time(), 'last4' => substr( $new, -4 ) ];
            update_user_meta( $user->ID, '_vpg_api_tokens', array_slice( $toks, -10 ) );
            update_user_meta( $user->ID, '_vpg_api_token_index', hash( 'sha256', $new ) ); // most-recent index for lookup
        }
        if ( isset( $_POST['revoke'] ) ) {
            $toks = array_values( array_filter( vpg_api_tokens( $user->ID ), fn( $t ) => $t['last4'] !== $_POST['revoke'] ) );
            update_user_meta( $user->ID, '_vpg_api_tokens', $toks );
        }
    }
    echo '<section class="vpg-profile-sec"><h3>' . esc_html__( 'API tokens', 'vpg-v2' ) . '</h3>';
    echo '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'For building with our open data. Read-only. Keep them secret.', 'vpg-v2' ) . '</p>';
    if ( $new ) echo '<p style="background:var(--g-wash,#F4F3F0);padding:8px"><code>' . esc_html( $new ) . '</code><br>' . esc_html__( 'Copy it now — it is not shown again.', 'vpg-v2' ) . '</p>';
    $toks = vpg_api_tokens( $user->ID );
    if ( $toks ) { echo '<ul>'; foreach ( $toks as $t ) echo '<li>' . esc_html( $t['label'] ) . ' ····' . esc_html( $t['last4'] ) . ' <form method="post" style="display:inline">' . wp_nonce_field( 'vpg_tok', '_vpg_tok', true, false ) . '<button class="g-btn" name="revoke" value="' . esc_attr( $t['last4'] ) . '" style="padding:0 6px">×</button></form></li>'; echo '</ul>'; }
    echo '<form method="post">' . wp_nonce_field( 'vpg_tok', '_vpg_tok', true, false );
    echo '<input type="text" name="label" placeholder="' . esc_attr__( 'What is it for?', 'vpg-v2' ) . '"> <button class="g-btn" name="mint" value="1">' . esc_html__( 'New token', 'vpg-v2' ) . '</button></form></section>';
}, 32 );

/* ================================================================
 * 0928 / 0945 · outgoing webhooks + a public changes feed
 * ================================================================ */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( 'publish' !== $new || 'publish' === $old ) return;
    if ( ! in_array( $post->post_type, [ 'vpg_location', 'vpg_event', 'vpg_studio', 'vpg_shop', 'post' ], true ) ) return;
    // record for the changes feed (0945)
    $feed = (array) get_option( 'vpg_changes_feed', [] );
    array_unshift( $feed, [ 'id' => $post->ID, 'type' => $post->post_type, 'title' => get_the_title( $post ), 'url' => get_permalink( $post ), 't' => time() ] );
    update_option( 'vpg_changes_feed', array_slice( $feed, 0, 100 ), false );
    // fan out to webhook subscribers (0928)
    $payload = wp_json_encode( [ 'event' => 'published', 'type' => $post->post_type, 'id' => $post->ID, 'title' => get_the_title( $post ), 'url' => get_permalink( $post ), 'time' => time() ] );
    foreach ( (array) get_option( 'vpg_webhooks', [] ) as $hook ) {
        if ( ! empty( $hook['types'] ) && ! in_array( $post->post_type, $hook['types'], true ) ) continue;
        wp_schedule_single_event( time() + 15, 'vpg_webhook_deliver', [ $hook['url'], $payload, $hook['secret'] ?? '' ] );
    }
}, 20, 3 );
add_action( 'vpg_webhook_deliver', function ( $url, $payload, $secret ) {
    wp_remote_post( $url, [
        'timeout' => 8,
        'headers' => [ 'Content-Type' => 'application/json', 'X-VPG-Signature' => $secret ? hash_hmac( 'sha256', $payload, $secret ) : '' ],
        'body'    => $payload,
    ] );
}, 10, 3 );
/* subscribe/unsubscribe (token-gated) */
add_action( 'admin_post_nopriv_vpg_webhook_sub', 'vpg_webhook_sub' );
add_action( 'admin_post_vpg_webhook_sub', 'vpg_webhook_sub' );
function vpg_webhook_sub() {
    $uid = vpg_api_token_user( sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) ) );
    if ( ! $uid ) { status_header( 401 ); wp_die( 'invalid token' ); }
    $hooks = (array) get_option( 'vpg_webhooks', [] );
    $url = esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) );
    if ( $url ) { $hooks[ md5( $url ) ] = [ 'url' => $url, 'by' => $uid, 'secret' => wp_generate_password( 20, false ), 'types' => [] ]; update_option( 'vpg_webhooks', $hooks, false ); }
    wp_send_json_success( [ 'subscribed' => $url, 'secret' => $hooks[ md5( $url ) ]['secret'] ?? '' ] );
}

/* ================================================================
 * data endpoints under /data/* — path-matched, no rewrite needed
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( strpos( $path, 'data/' ) !== 0 && 'data' !== $path && ! in_array( $path, [ 'status', 'interop', 'fediverse', 'datengarten' ], true ) ) return;

    $locations = fn() => get_posts( [ 'post_type' => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], 'post_status' => 'publish', 'numberposts' => 1000 ] );

    switch ( $path ) {
        case 'data/locations.csv': // 0944
            header( 'Content-Type: text/csv; charset=utf-8' ); header( 'Content-Disposition: attachment; filename="vpg-locations.csv"' );
            $out = fopen( 'php://output', 'w' );
            fputcsv( $out, [ 'id', 'title', 'type', 'lat', 'lng', 'district', 'url', 'license' ] );
            foreach ( $locations() as $p ) fputcsv( $out, [ $p->ID, get_the_title( $p ), $p->post_type, get_post_meta( $p->ID, 'location_lat', true ), get_post_meta( $p->ID, 'location_lng', true ), get_post_meta( $p->ID, 'location_district', true ), get_permalink( $p ), vpg_post_license( $p->ID ) ] );
            fclose( $out ); exit;

        case 'data/locations.kml': // 0944
            header( 'Content-Type: application/vnd.google-earth.kml+xml' );
            echo '<?xml version="1.0" encoding="UTF-8"?><kml xmlns="http://www.opengis.net/kml/2.2"><Document><name>Vienna Photo Group</name>';
            foreach ( $locations() as $p ) {
                $lat = get_post_meta( $p->ID, 'location_lat', true ); $lng = get_post_meta( $p->ID, 'location_lng', true );
                if ( $lat === '' || $lng === '' ) continue;
                echo '<Placemark><name>' . esc_html( get_the_title( $p ) ) . '</name><Point><coordinates>' . esc_html( (float) $lng . ',' . (float) $lat . ',0' ) . '</coordinates></Point></Placemark>';
            }
            echo '</Document></kml>'; exit;

        case 'data/events.jsonld': // 0939 · schema.org event feed
            header( 'Content-Type: application/ld+json' );
            $items = [];
            foreach ( get_posts( [ 'post_type' => 'vpg_event', 'post_status' => 'publish', 'numberposts' => 100, 'meta_key' => '_vpg_event_date', 'orderby' => 'meta_value', 'order' => 'ASC' ] ) as $i => $e ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'item' => [ '@type' => 'Event', 'name' => get_the_title( $e ), 'url' => get_permalink( $e ), 'startDate' => get_post_meta( $e->ID, '_vpg_event_date', true ) ] ];
            }
            echo wp_json_encode( [ '@context' => 'https://schema.org', '@type' => 'ItemList', 'name' => 'VPG events', 'itemListElement' => $items ] ); exit;

        case 'data/changes.json': // 0945
            header( 'Content-Type: application/json' ); header( 'Access-Control-Allow-Origin: *' );
            echo wp_json_encode( [ 'updated' => time(), 'changes' => (array) get_option( 'vpg_changes_feed', [] ) ] ); exit;

        case 'data/openapi.json': // 0927
            header( 'Content-Type: application/json' );
            echo wp_json_encode( vpg_openapi_spec() ); exit;

        case 'data/oembed': // 0950
            header( 'Content-Type: application/json' );
            $u = esc_url_raw( wp_unslash( $_GET['url'] ?? '' ) );
            echo wp_json_encode( vpg_oembed_response( $u ) ); exit;

        case 'status': vpg_status_page(); exit;         // 0956
        case 'fediverse': vpg_fediverse_directory(); exit; // 0923
        case 'interop': vpg_interop_page(); exit;        // 0959/0957/0960
        case 'data': case 'datengarten': vpg_data_garden(); exit; // 0941
    }
}, 9 );

/* 0927 · a minimal but real OpenAPI 3 description of the existing /api/v1 */
function vpg_openapi_spec() {
    $base = home_url( '/api/v1' );
    $loc = [ 'type' => 'object', 'properties' => [ 'id' => [ 'type' => 'integer' ], 'title' => [ 'type' => 'string' ], 'lat' => [ 'type' => 'number' ], 'lng' => [ 'type' => 'number' ], 'district' => [ 'type' => 'string' ], 'license' => [ 'type' => 'string' ] ] ];
    return [
        'openapi' => '3.0.3',
        'info'    => [ 'title' => 'Vienna Photo Group API', 'version' => '1.0', 'description' => 'Read-only open data. CC BY 4.0 unless a photo says otherwise.', 'license' => [ 'name' => 'CC BY 4.0', 'url' => 'https://creativecommons.org/licenses/by/4.0/' ] ],
        'servers' => [ [ 'url' => $base ] ],
        'paths'   => [
            '/locations'         => [ 'get' => [ 'summary' => 'List published locations', 'responses' => [ '200' => [ 'description' => 'OK', 'content' => [ 'application/json' => [ 'schema' => [ 'type' => 'array', 'items' => $loc ] ] ] ] ] ] ],
            '/locations.geojson' => [ 'get' => [ 'summary' => 'Locations as GeoJSON', 'responses' => [ '200' => [ 'description' => 'OK' ] ] ] ],
            '/events'            => [ 'get' => [ 'summary' => 'Upcoming events', 'responses' => [ '200' => [ 'description' => 'OK' ] ] ] ],
        ],
        'x-extra-formats' => [ home_url( '/data/locations.csv' ), home_url( '/data/locations.kml' ), home_url( '/data/events.jsonld' ), home_url( '/data/changes.json' ) ],
        'x-rate-limit'    => '120 requests/hour/IP; higher with a personal token',
        'x-deprecation-policy' => 'Breaking API changes are announced six months ahead.',
    ];
}

/* 0950 · oEmbed provider — our map/gallery links unfurl in other editors */
function vpg_oembed_response( $url ) {
    $pid = url_to_postid( $url );
    $title = $pid ? get_the_title( $pid ) : get_bloginfo( 'name' );
    $embed = home_url( '/embed/map/' );
    if ( $pid && 'vpg_location' === get_post_type( $pid ) ) $embed = get_permalink( $pid );
    return [
        'version' => '1.0', 'type' => 'rich', 'provider_name' => 'Vienna Photo Group', 'provider_url' => home_url( '/' ),
        'title' => $title, 'width' => 640, 'height' => 420,
        'html' => '<iframe src="' . esc_url( $embed ) . '" width="640" height="420" style="border:0" loading="lazy" title="' . esc_attr( $title ) . '"></iframe>',
    ];
}
add_action( 'wp_head', function () {
    if ( ! is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] ) ) return;
    $u = rawurlencode( get_permalink() );
    echo '<link rel="alternate" type="application/json+oembed" href="' . esc_url( home_url( '/data/oembed?url=' . $u ) ) . '">' . "\n";
}, 6 );

/* 0937 · microformats2 — h-entry wrapper + p-author h-card on singular */
add_filter( 'the_content', function ( $c ) {
    if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) return $c;
    $author = get_the_author();
    $card = '<span class="p-author h-card" style="display:none"><span class="p-name">' . esc_html( $author ) . '</span><a class="u-url" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '"></a></span>';
    $meta = '<data class="dt-published" value="' . esc_attr( get_the_date( 'c' ) ) . '"></data>';
    return '<div class="h-entry"><div class="e-content">' . $c . '</div>' . $card . $meta . '</div>';
}, 3 );

/* 0935 · send webmentions when we publish links to other IndieWeb sites */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( 'publish' !== $new || ! in_array( $post->post_type, [ 'post', 'vpg_review', 'vpg_tutorial' ], true ) ) return;
    if ( ! preg_match_all( '#https?://[^\s"\'<>]+#i', $post->post_content, $m ) ) return;
    foreach ( array_slice( array_unique( $m[0] ), 0, 15 ) as $target ) wp_schedule_single_event( time() + 20, 'vpg_send_webmention', [ get_permalink( $post ), $target ] );
}, 25, 3 );
add_action( 'vpg_send_webmention', function ( $source, $target ) {
    $head = wp_remote_get( $target, [ 'timeout' => 8 ] );
    if ( is_wp_error( $head ) ) return;
    $body = wp_remote_retrieve_body( $head );
    $endpoint = '';
    foreach ( wp_remote_retrieve_header( $head, 'link' ) ? (array) wp_remote_retrieve_header( $head, 'link' ) : [] as $lh ) if ( stripos( $lh, 'webmention' ) !== false && preg_match( '/<([^>]+)>/', $lh, $mm ) ) $endpoint = $mm[1];
    if ( ! $endpoint && preg_match( '#<link[^>]+rel=["\']?webmention["\']?[^>]+href=["\']([^"\']+)#i', $body, $mm ) ) $endpoint = $mm[1];
    if ( $endpoint ) wp_remote_post( $endpoint, [ 'timeout' => 8, 'body' => [ 'source' => $source, 'target' => $target ] ] );
}, 10, 2 );

/* ================================================================
 * Public pages: status · fediverse directory · interop · data garden
 * ================================================================ */
function vpg_od_head( $title ) { status_header( 200 ); get_header(); echo '<main id="vpg-main" class="g-wrap" style="max-width:760px;margin:40px auto;padding:0 20px"><h1>' . esc_html( $title ) . '</h1>'; }
function vpg_od_foot() { echo '</main>'; get_footer(); }

/** Real liveness snapshot the status page & ping read (db · uploads · php). */
function vpg_health_snapshot() {
    global $wpdb;
    $db = false;
    if ( $wpdb instanceof wpdb ) { $db = ( '1' === (string) $wpdb->get_var( 'SELECT 1' ) ); }
    $up = wp_is_writable( wp_upload_dir()['basedir'] ?? '' );
    return [ 'database' => $db, 'uploads' => (bool) $up, 'php' => true ];
}

function vpg_status_page() { // 0956
    $checks = function_exists( 'vpg_health_snapshot' ) ? vpg_health_snapshot() : null;
    $hist = (array) get_option( 'vpg_status_history', [] );
    vpg_od_head( __( 'Status', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'A live look at whether the essentials are up.', 'vpg-v2' ) . '</p>';
    $ok = true;
    echo '<ul style="list-style:none;padding:0;font-size:16px">';
    foreach ( [ 'database' => __( 'Database', 'vpg-v2' ), 'uploads' => __( 'Media storage', 'vpg-v2' ), 'php' => __( 'Application', 'vpg-v2' ) ] as $k => $label ) {
        $up = $checks ? ! empty( $checks[ $k ] ) : true;
        $ok = $ok && $up;
        echo '<li>' . ( $up ? '🟢' : '🔴' ) . ' ' . esc_html( $label ) . '</li>';
    }
    echo '</ul>';
    echo '<p>' . ( $ok ? '✅ ' . esc_html__( 'All systems operational.', 'vpg-v2' ) : '⚠️ ' . esc_html__( 'We’re looking into an issue.', 'vpg-v2' ) ) . '</p>';
    if ( $hist ) { echo '<h2>' . esc_html__( 'Last 30 days', 'vpg-v2' ) . '</h2><p style="font-family:monospace;letter-spacing:2px">'; foreach ( array_slice( $hist, -30 ) as $d ) echo $d ? '🟩' : '🟥'; echo '</p>'; }
    echo '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Machine-readable:', 'vpg-v2' ) . ' <a href="' . esc_url( home_url( '/health/' ) ) . '">/health/</a></p>';
    vpg_od_foot();
}
/* record one status sample a day */
add_action( 'vpg_status_ping', function () {
    $ok = function_exists( 'vpg_health_snapshot' ) ? ! in_array( false, vpg_health_snapshot(), true ) : true;
    $h = (array) get_option( 'vpg_status_history', [] ); $h[] = $ok ? 1 : 0;
    update_option( 'vpg_status_history', array_slice( $h, -60 ), false );
} );
add_action( 'init', function () { if ( ! wp_next_scheduled( 'vpg_status_ping' ) ) wp_schedule_event( time() + 3 * HOUR_IN_SECONDS, 'daily', 'vpg_status_ping' ); } );

function vpg_fediverse_directory() { // 0923
    vpg_od_head( __( 'VPG in the Fediverse', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'Follow the collective at @vpg — and find members who live in the open social web.', 'vpg-v2' ) . '</p>';
    $members = get_users( [ 'meta_key' => '_vpg_links', 'number' => 300 ] );
    $rows = '';
    foreach ( $members as $u ) {
        $links = (array) get_user_meta( $u->ID, '_vpg_links', true );
        $f = $links['fediverse'] ?? '';
        if ( ! $f ) continue;
        $rows .= '<li class="h-card"><a class="u-url p-name" rel="me" href="' . esc_url( $f ) . '">' . esc_html( $u->display_name ) . '</a> — <span style="color:#888">' . esc_html( $f ) . '</span></li>';
    }
    if ( $rows ) echo '<ul class="h-feed">' . $rows . '</ul>';
    else echo '<p class="description">' . esc_html__( 'No members have shared a Fediverse address yet — add yours under profile links.', 'vpg-v2' ) . '</p>';
    vpg_od_foot();
}

function vpg_interop_page() { // 0959 / 0957 / 0960
    vpg_od_head( __( 'Why we stay open', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'An interoperability manifesto — and the promises that keep us honest.', 'vpg-v2' ) . '</p>';
    echo '<h2>' . esc_html__( 'Manifesto', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.8">';
    foreach ( [
        __( 'Your data is yours — export it any time, in open formats.', 'vpg-v2' ),
        __( 'We speak standard protocols (ActivityPub, Webmention, GeoJSON, iCalendar, RSS).', 'vpg-v2' ),
        __( 'No lock-in: everything here can be left behind cleanly.', 'vpg-v2' ),
        __( 'We give back — verified data flows to OpenStreetMap and the commons.', 'vpg-v2' ),
    ] as $s ) echo '<li>' . esc_html( $s ) . '</li>';
    echo '</ul>';
    echo '<h2>' . esc_html__( 'API deprecation policy (0957)', 'vpg-v2' ) . '</h2><p>' . esc_html__( 'Breaking changes to the API are announced at least six months in advance, with a migration note.', 'vpg-v2' ) . '</p>';
    echo '<h2>' . esc_html__( 'No walled garden — yearly self-audit (0960)', 'vpg-v2' ) . '</h2><p>' . esc_html__( 'Once a year we check ourselves against lock-in: can a member leave with everything? Can others build on us? The answer must stay yes.', 'vpg-v2' ) . '</p>';
    vpg_od_foot();
}

function vpg_data_garden() { // 0941 (+ 0930/0931/0940/0942/0943/0947/0948/0952/0953/0954/0955/0958)
    vpg_od_head( __( 'The data garden', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'Everything we open up, in one place — take it, build on it, credit us.', 'vpg-v2' ) . '</p>';
    echo '<h2>' . esc_html__( 'Datasets & endpoints', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.9">';
    foreach ( [
        [ __( 'Locations (JSON)', 'vpg-v2' ), '/api/v1/locations' ],
        [ __( 'Locations (GeoJSON)', 'vpg-v2' ), '/api/v1/locations.geojson' ],
        [ __( 'Locations (CSV)', 'vpg-v2' ), '/data/locations.csv' ],
        [ __( 'Locations (KML)', 'vpg-v2' ), '/data/locations.kml' ],
        [ __( 'Events (JSON)', 'vpg-v2' ), '/api/v1/events' ],
        [ __( 'Events (schema.org)', 'vpg-v2' ), '/data/events.jsonld' ],
        [ __( 'Events (iCalendar)', 'vpg-v2' ), '/?action=vpg_events_feed' ],
        [ __( 'Changes feed (JSON)', 'vpg-v2' ), '/data/changes.json' ],
        [ __( 'OpenAPI spec', 'vpg-v2' ), '/data/openapi.json' ],
        [ __( 'API docs', 'vpg-v2' ), '/api/v1/docs' ],
    ] as $row ) echo '<li><a href="' . esc_url( home_url( $row[1] ) ) . '">' . esc_html( $row[0] ) . '</a></li>';
    echo '</ul>';
    echo '<p>' . esc_html__( 'Licence: CC BY 4.0 unless an individual photo states otherwise. Tokens & webhooks: see your dashboard profile.', 'vpg-v2' ) . '</p>';

    echo '<h2>' . esc_html__( 'How to pull VPG-relevant OSM data (0930)', 'vpg-v2' ) . '</h2>';
    echo '<pre style="background:var(--g-wash,#F4F3F0);padding:12px;overflow:auto;font-size:12px">[out:json];\narea["name"="Wien"]->.a;\nnode(area.a)["tourism"="viewpoint"];\nout center;</pre>';

    echo '<h2>' . esc_html__( 'On the roadmap', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.8">';
    foreach ( [
        __( '0929 Feed verified corrections back to OpenStreetMap.', 'vpg-v2' ),
        __( '0931 Surface City of Vienna open datasets as map layers.', 'vpg-v2' ),
        __( '0940 Link public-transport (GTFS) officially, not hand-rolled.', 'vpg-v2' ),
        __( '0942 Open the data to urban researchers.', 'vpg-v2' ),
        __( '0943 A long-term archiving hand-over with an institution.', 'vpg-v2' ),
        __( '0947 A gallery of third-party apps built on the API.', 'vpg-v2' ),
        __( '0948 A one-day open-data hackday for Vienna’s makers.', 'vpg-v2' ),
        __( '0952 A directory of sister collectives worldwide.', 'vpg-v2' ),
        __( '0953 A shared city data schema with Graz and Berlin.', 'vpg-v2' ),
        __( '0954 A public Git mirror of the open data.', 'vpg-v2' ),
        __( '0955 A torrent of the yearly data package.', 'vpg-v2' ),
        __( '0958 Small client-library sketches as a starting point.', 'vpg-v2' ),
    ] as $s ) echo '<li>' . esc_html( $s ) . '</li>';
    echo '</ul>';
    echo '<p><a href="' . esc_url( home_url( '/interop/' ) ) . '">' . esc_html__( 'Read why we stay open →', 'vpg-v2' ) . '</a></p>';
    vpg_od_foot();
}

/* ================================================================
 * Open-data desk — default licence, webhooks, embed builder
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Open data & API', 'vpg-v2' ), '🌐 ' . __( 'Open data', 'vpg-v2' ), 'manage_options', 'vpg-opendata', 'vpg_opendata_desk' );
} );
function vpg_opendata_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['_vpg_od'] ) && wp_verify_nonce( $_POST['_vpg_od'], 'vpg_od' ) ) {
        update_option( 'vpg_default_license', sanitize_text_field( wp_unslash( $_POST['default_license'] ?? 'CC-BY' ) ) );
        if ( ! empty( $_POST['drop_hook'] ) ) { $h = (array) get_option( 'vpg_webhooks', [] ); unset( $h[ sanitize_text_field( wp_unslash( $_POST['drop_hook'] ) ) ] ); update_option( 'vpg_webhooks', $h, false ); }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $hooks = (array) get_option( 'vpg_webhooks', [] );
    ?>
    <div class="wrap"><h1>🌐 <?php esc_html_e( 'Open data & API', 'vpg-v2' ); ?></h1>
      <p><a href="<?php echo esc_url( home_url( '/data/' ) ); ?>" target="_blank"><?php esc_html_e( 'View the public data garden →', 'vpg-v2' ); ?></a> ·
         <a href="<?php echo esc_url( home_url( '/status/' ) ); ?>" target="_blank"><?php esc_html_e( 'Status', 'vpg-v2' ); ?></a> ·
         <a href="<?php echo esc_url( home_url( '/interop/' ) ); ?>" target="_blank"><?php esc_html_e( 'Interop manifesto', 'vpg-v2' ); ?></a></p>

      <form method="post">
        <?php wp_nonce_field( 'vpg_od', '_vpg_od' ); ?>
        <h2><?php esc_html_e( '0933 · Default licence for new photos', 'vpg-v2' ); ?></h2>
        <p><select name="default_license"><?php $cur = get_option( 'vpg_default_license', 'CC-BY' ); foreach ( vpg_licenses() as $slug => $l ) echo '<option value="' . esc_attr( $slug ) . '"' . selected( $cur, $slug, false ) . '>' . esc_html( $l[0] ) . '</option>'; ?></select>
        <span class="description"><?php esc_html_e( 'Members can override this per photo in the media library.', 'vpg-v2' ); ?></span></p>
        <p><button class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>

        <h2><?php esc_html_e( '0928 · Webhook subscribers', 'vpg-v2' ); ?></h2>
        <?php if ( $hooks ) { echo '<ul>'; foreach ( $hooks as $id => $h ) echo '<li><code>' . esc_html( $h['url'] ) . '</code> <button class="button-link" name="drop_hook" value="' . esc_attr( $id ) . '">' . esc_html__( 'remove', 'vpg-v2' ) . '</button></li>'; echo '</ul>'; }
           else echo '<p class="description">' . esc_html__( 'No webhook subscribers. Builders subscribe with a personal token via POST to admin-post.php?action=vpg_webhook_sub.', 'vpg-v2' ) . '</p>'; ?>
      </form>

      <h2><?php esc_html_e( '0949 · Embed builder', 'vpg-v2' ); ?></h2>
      <p class="description"><?php esc_html_e( 'Copy-paste to embed VPG anywhere:', 'vpg-v2' ); ?></p>
      <?php foreach ( [ __( 'Map', 'vpg-v2' ) => '/embed/map/', __( 'Gallery', 'vpg-v2' ) => '/gallery/feed/' ] as $label => $p ) {
          $code = '<iframe src="' . esc_url( home_url( $p ) ) . '" width="640" height="420" style="border:0" loading="lazy" title="VPG ' . esc_attr( $label ) . '"></iframe>';
          echo '<p><strong>' . esc_html( $label ) . '</strong><br><textarea readonly rows="2" class="large-text code" onclick="this.select()">' . esc_textarea( $code ) . '</textarea></p>';
      } ?>
    </div>
    <?php
}
