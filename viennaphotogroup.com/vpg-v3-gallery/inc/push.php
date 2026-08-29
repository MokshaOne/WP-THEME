<?php
/**
 * VPG v3 — Q7 · 0607 Web Push, self-hosted.
 *
 * VAPID (RFC 8292) + aes128gcm payload encryption (RFC 8291/8188)
 * implemented directly on openssl — no composer packages, works on
 * easyname shared hosting. Every vpg_notify_user() also lands as a
 * push for members who opted in on their dashboard.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── VAPID keypair · created once, kept in one option ───────────── */
function vpg_vapid_keys() {
    $keys = get_option( 'vpg_vapid' );
    if ( is_array( $keys ) && ! empty( $keys['pem'] ) ) return $keys;

    $res = openssl_pkey_new( [ 'curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC ] );
    if ( ! $res ) return [];
    openssl_pkey_export( $res, $pem );
    $det = openssl_pkey_get_details( $res );
    $pub = "\x04" . str_pad( $det['ec']['x'], 32, "\0", STR_PAD_LEFT ) . str_pad( $det['ec']['y'], 32, "\0", STR_PAD_LEFT );

    $keys = [ 'pem' => $pem, 'pub' => rtrim( strtr( base64_encode( $pub ), '+/', '-_' ), '=' ) ];
    update_option( 'vpg_vapid', $keys, false );
    return $keys;
}

/* ─── DER ECDSA signature → raw r||s (JWT wants raw) ─────────────── */
function vpg_der_to_raw64( $der ) {
    $pos = 2;
    if ( ord( $der[1] ) & 0x80 ) $pos += ord( $der[1] ) & 0x7F;      // long-form length
    $out = '';
    for ( $i = 0; $i < 2; $i++ ) {
        $pos++;                                                      // 0x02 INTEGER
        $len = ord( $der[ $pos++ ] );
        $int = substr( $der, $pos, $len );
        $pos += $len;
        $int = ltrim( $int, "\0" );
        $out .= str_pad( $int, 32, "\0", STR_PAD_LEFT );
    }
    return $out;
}

/* ─── VAPID JWT (ES256) for one push endpoint's origin ───────────── */
function vpg_vapid_header( $endpoint ) {
    $keys = vpg_vapid_keys();
    if ( ! $keys ) return '';
    $aud    = wp_parse_url( $endpoint, PHP_URL_SCHEME ) . '://' . wp_parse_url( $endpoint, PHP_URL_HOST );
    $b64    = fn( $s ) => rtrim( strtr( base64_encode( $s ), '+/', '-_' ), '=' );
    $head   = $b64( wp_json_encode( [ 'typ' => 'JWT', 'alg' => 'ES256' ] ) );
    $claims = $b64( wp_json_encode( [ 'aud' => $aud, 'exp' => time() + 12 * HOUR_IN_SECONDS, 'sub' => 'mailto:' . get_option( 'admin_email' ) ] ) );
    if ( ! openssl_sign( $head . '.' . $claims, $der, $keys['pem'], OPENSSL_ALGO_SHA256 ) ) return '';
    return 'vapid t=' . $head . '.' . $claims . '.' . $b64( vpg_der_to_raw64( $der ) ) . ', k=' . $keys['pub'];
}

/* ─── RFC 8291 · encrypt a payload for one subscription ──────────── */
function vpg_push_encrypt( $payload, $p256dh_b64u, $auth_b64u ) {
    $ua_pub = base64_decode( strtr( $p256dh_b64u, '-_', '+/' ) );
    $auth   = base64_decode( strtr( $auth_b64u, '-_', '+/' ) );
    if ( strlen( $ua_pub ) !== 65 || strlen( $auth ) !== 16 ) return false;

    // Ephemeral server key + ECDH shared secret with the browser's key
    $eph = openssl_pkey_new( [ 'curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC ] );
    if ( ! $eph ) return false;
    $det     = openssl_pkey_get_details( $eph );
    $as_pub  = "\x04" . str_pad( $det['ec']['x'], 32, "\0", STR_PAD_LEFT ) . str_pad( $det['ec']['y'], 32, "\0", STR_PAD_LEFT );
    $ua_pem  = "-----BEGIN PUBLIC KEY-----\n"
             . chunk_split( base64_encode( hex2bin( '3059301306072a8648ce3d020106082a8648ce3d030107034200' ) . $ua_pub ), 64, "\n" )
             . "-----END PUBLIC KEY-----\n";
    $shared  = openssl_pkey_derive( openssl_pkey_get_public( $ua_pem ), $eph, 32 );
    if ( ! $shared ) return false;

    $hkdf = function ( $salt, $ikm, $info, $len ) {
        $prk = hash_hmac( 'sha256', $ikm, $salt, true );
        return substr( hash_hmac( 'sha256', $info . "\x01", $prk, true ), 0, $len );
    };

    // key_info = "WebPush: info" || 0x00 || ua_public || as_public
    $ikm   = $hkdf( $auth, $shared, "WebPush: info\x00" . $ua_pub . $as_pub, 32 );
    $salt  = random_bytes( 16 );
    $cek   = $hkdf( $salt, $ikm, "Content-Encoding: aes128gcm\x00", 16 );
    $nonce = $hkdf( $salt, $ikm, "Content-Encoding: nonce\x00", 12 );

    $padded = $payload . "\x02";                                   // last-record delimiter
    $tag    = '';
    $cipher = openssl_encrypt( $padded, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag );
    if ( $cipher === false ) return false;

    // aes128gcm header: salt(16) | rs(4) | idlen(1) | keyid(as_public)
    return $salt . pack( 'N', 4096 ) . chr( 65 ) . $as_pub . $cipher . $tag;
}

/* ─── Send one push to every subscription a member holds ─────────── */
function vpg_push_send( $uid, $title, $body, $url = '' ) {
    $subs = array_filter( (array) get_user_meta( $uid, '_vpg_push_subs', true ), 'is_array' );
    if ( ! $subs ) return;

    $payload = wp_json_encode( [ 'title' => $title, 'body' => $body, 'url' => $url ?: home_url( '/dashboard/' ) ] );
    $changed = false;

    foreach ( $subs as $hash => $sub ) {
        $endpoint = $sub['endpoint'] ?? '';
        $cipher   = $endpoint ? vpg_push_encrypt( $payload, $sub['keys']['p256dh'] ?? '', $sub['keys']['auth'] ?? '' ) : false;
        $vapid    = $cipher ? vpg_vapid_header( $endpoint ) : '';
        if ( ! $vapid ) continue;

        $res  = wp_remote_post( $endpoint, [
            'timeout' => 8,
            'headers' => [
                'TTL'              => (string) DAY_IN_SECONDS,
                'Content-Encoding' => 'aes128gcm',
                'Content-Type'     => 'application/octet-stream',
                'Urgency'          => 'normal',
                'Authorization'    => $vapid,
            ],
            'body' => $cipher,
        ] );
        $code = is_wp_error( $res ) ? 0 : wp_remote_retrieve_response_code( $res );
        if ( in_array( $code, [ 404, 410 ], true ) ) {              // browser dropped the subscription
            unset( $subs[ $hash ] );
            $changed = true;
        }
    }
    if ( $changed ) update_user_meta( $uid, '_vpg_push_subs', $subs );
}

/* Every in-app notification can also knock on the lockscreen */
add_action( 'vpg_notified', function ( $uid, $text, $url ) {
    vpg_push_send( (int) $uid, get_bloginfo( 'name' ), $text, $url );
}, 10, 3 );

/* ─── Subscribe / unsubscribe · dashboard AJAX ───────────────────── */
add_action( 'wp_ajax_vpg_push_subscribe', function () {
    check_ajax_referer( 'vpg_push' );
    $sub = json_decode( wp_unslash( $_POST['sub'] ?? '' ), true );
    if ( ! is_array( $sub ) || empty( $sub['endpoint'] ) || ! str_starts_with( $sub['endpoint'], 'https://' ) ) {
        wp_send_json_error( 'bad subscription' );
    }
    $uid  = get_current_user_id();
    $subs = array_filter( (array) get_user_meta( $uid, '_vpg_push_subs', true ), 'is_array' );
    $subs[ substr( md5( $sub['endpoint'] ), 0, 12 ) ] = [
        'endpoint' => esc_url_raw( $sub['endpoint'] ),
        'keys'     => [
            'p256dh' => sanitize_text_field( $sub['keys']['p256dh'] ?? '' ),
            'auth'   => sanitize_text_field( $sub['keys']['auth'] ?? '' ),
        ],
    ];
    update_user_meta( $uid, '_vpg_push_subs', array_slice( $subs, -3, null, true ) );
    wp_send_json_success();
} );

add_action( 'wp_ajax_vpg_push_unsubscribe', function () {
    check_ajax_referer( 'vpg_push' );
    delete_user_meta( get_current_user_id(), '_vpg_push_subs' );
    wp_send_json_success();
} );
