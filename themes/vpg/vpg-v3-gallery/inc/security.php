<?php
/**
 * VPG v3 — Q7 · account security.
 *
 *   0779  TOTP two-factor · pure-PHP RFC 6238, no plugin, no cloud
 *   0780  Passkeys · WebAuthn (ES256, attestation "none"), openssl only
 *
 * Shared-hosting rules: no composer packages, everything in this file.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  0779 · TOTP                                                      */
/* ════════════════════════════════════════════════════════════════ */

function vpg_base32_encode( $bin ) {
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $out   = '';
    $bits  = '';
    foreach ( str_split( $bin ) as $c ) $bits .= str_pad( decbin( ord( $c ) ), 8, '0', STR_PAD_LEFT );
    foreach ( str_split( $bits, 5 ) as $chunk ) $out .= $alpha[ bindec( str_pad( $chunk, 5, '0' ) ) ];
    return $out;
}

function vpg_base32_decode( $b32 ) {
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits  = '';
    foreach ( str_split( strtoupper( preg_replace( '/[^A-Za-z2-7]/', '', $b32 ) ) ) as $c ) {
        $v = strpos( $alpha, $c );
        if ( $v === false ) continue;
        $bits .= str_pad( decbin( $v ), 5, '0', STR_PAD_LEFT );
    }
    $out = '';
    foreach ( str_split( $bits, 8 ) as $byte ) {
        if ( strlen( $byte ) === 8 ) $out .= chr( bindec( $byte ) );
    }
    return $out;
}

function vpg_totp_code( $secret_b32, $slot ) {
    $key  = vpg_base32_decode( $secret_b32 );
    $hash = hash_hmac( 'sha1', pack( 'N*', 0, $slot ), $key, true );
    $off  = ord( substr( $hash, -1 ) ) & 0x0F;
    $int  = ( unpack( 'N', substr( $hash, $off, 4 ) )[1] ) & 0x7FFFFFFF;
    return str_pad( (string) ( $int % 1000000 ), 6, '0', STR_PAD_LEFT );
}

function vpg_totp_verify( $secret_b32, $code, $uid = 0 ) {
    $code = preg_replace( '/\D/', '', (string) $code );
    if ( strlen( $code ) !== 6 ) return false;
    $now = (int) floor( time() / 30 );
    foreach ( [ 0, -1, 1 ] as $drift ) {
        $slot = $now + $drift;
        if ( hash_equals( vpg_totp_code( $secret_b32, $slot ), $code ) ) {
            if ( $uid ) {
                // A code is a key that only turns once.
                if ( (int) get_user_meta( $uid, '_vpg_totp_last_slot', true ) >= $slot ) return false;
                update_user_meta( $uid, '_vpg_totp_last_slot', $slot );
            }
            return true;
        }
    }
    return false;
}

/* Enrollment · dashboard flow: setup → confirm one code → armed */
add_action( 'admin_post_vpg_totp_setup', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_totp' );
    update_user_meta( get_current_user_id(), '_vpg_totp_pending', vpg_base32_encode( random_bytes( 20 ) ) );
    wp_safe_redirect( home_url( '/dashboard/#security' ) );
    exit;
} );

add_action( 'admin_post_vpg_totp_confirm', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_totp' );
    $uid     = get_current_user_id();
    $pending = get_user_meta( $uid, '_vpg_totp_pending', true );
    if ( $pending && vpg_totp_verify( $pending, $_POST['code'] ?? '', $uid ) ) {
        update_user_meta( $uid, '_vpg_totp_secret', $pending );
        update_user_meta( $uid, '_vpg_totp_on', '1' );
        delete_user_meta( $uid, '_vpg_totp_pending' );
    }
    wp_safe_redirect( home_url( '/dashboard/#security' ) );
    exit;
} );

add_action( 'admin_post_vpg_totp_disable', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_totp' );
    $uid = get_current_user_id();
    if ( vpg_totp_verify( (string) get_user_meta( $uid, '_vpg_totp_secret', true ), $_POST['code'] ?? '', $uid ) ) {
        delete_user_meta( $uid, '_vpg_totp_on' );
        delete_user_meta( $uid, '_vpg_totp_secret' );
    }
    wp_safe_redirect( home_url( '/dashboard/#security' ) );
    exit;
} );

/* Login · the extra field only bites for members who armed it */
add_action( 'login_form', function () {
    ?>
    <p>
        <label for="vpg_totp"><?php esc_html_e( 'One-time code · only if you use 2FA', 'vpg-v2' ); ?></label>
        <input type="text" name="vpg_totp" id="vpg_totp" class="input" size="20"
               inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]*" placeholder="000000">
    </p>
    <?php
} );

add_filter( 'wp_authenticate_user', function ( $user ) {
    if ( is_wp_error( $user ) || ! $user instanceof WP_User ) return $user;
    if ( get_user_meta( $user->ID, '_vpg_totp_on', true ) !== '1' ) return $user;
    $secret = (string) get_user_meta( $user->ID, '_vpg_totp_secret', true );
    if ( ! $secret ) return $user;
    $code = (string) ( $_POST['vpg_totp'] ?? '' );
    if ( vpg_totp_verify( $secret, $code, $user->ID ) ) return $user;
    if ( vpg_consume_backup_code( $user->ID, $code ) ) return $user;   // 1017 · recovery path
    return new WP_Error( 'vpg_totp', __( '<strong>Error:</strong> your one-time code is missing or wrong.', 'vpg-v2' ) );
}, 30 );

/* Editors run the presses · 2FA is not optional for them, so we nag. */
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) return;
    if ( get_user_meta( get_current_user_id(), '_vpg_totp_on', true ) === '1' ) return;
    printf(
        '<div class="notice notice-warning"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
        esc_html__( 'Editorial accounts need two-factor auth.', 'vpg-v2' ),
        esc_html__( 'You publish other people’s work — protect the keys.', 'vpg-v2' ),
        esc_url( home_url( '/dashboard/#security' ) ),
        esc_html__( 'Set it up now →', 'vpg-v2' )
    );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0780 · Passkeys (WebAuthn · ES256 · attestation "none")          */
/* ════════════════════════════════════════════════════════════════ */

function vpg_b64url_encode( $bin ) { return rtrim( strtr( base64_encode( $bin ), '+/', '-_' ), '=' ); }
function vpg_b64url_decode( $str ) { return base64_decode( strtr( $str, '-_', '+/' ) ); }

/** Minimal CBOR decoder · enough for WebAuthn attestation objects. */
function vpg_cbor_decode( $data, &$pos = 0 ) {
    if ( $pos >= strlen( $data ) ) return null;
    $byte  = ord( $data[ $pos++ ] );
    $major = $byte >> 5;
    $info  = $byte & 0x1F;

    $len = $info;
    if ( $info === 24 ) { $len = ord( $data[ $pos ] ); $pos += 1; }
    elseif ( $info === 25 ) { $len = unpack( 'n', substr( $data, $pos, 2 ) )[1]; $pos += 2; }
    elseif ( $info === 26 ) { $len = unpack( 'N', substr( $data, $pos, 4 ) )[1]; $pos += 4; }
    elseif ( $info === 27 ) { $len = unpack( 'J', substr( $data, $pos, 8 ) )[1]; $pos += 8; }

    switch ( $major ) {
        case 0: return $len;                       // unsigned int
        case 1: return -1 - $len;                  // negative int
        case 2: $v = substr( $data, $pos, $len ); $pos += $len; return $v;          // bytes
        case 3: $v = substr( $data, $pos, $len ); $pos += $len; return $v;          // text
        case 4: $out = [];                          // array
            for ( $i = 0; $i < $len; $i++ ) $out[] = vpg_cbor_decode( $data, $pos );
            return $out;
        case 5: $out = [];                          // map
            for ( $i = 0; $i < $len; $i++ ) {
                $k = vpg_cbor_decode( $data, $pos );
                $out[ is_string( $k ) || is_int( $k ) ? $k : json_encode( $k ) ] = vpg_cbor_decode( $data, $pos );
            }
            return $out;
        case 6: return vpg_cbor_decode( $data, $pos ); // tag · unwrap
        default: return null;                          // simple/float · unused here
    }
}

/** COSE EC2 P-256 key → PEM (SubjectPublicKeyInfo). */
function vpg_cose_to_pem( $cose ) {
    if ( ! is_array( $cose ) || ( $cose[3] ?? 0 ) !== -7 ) return '';   // alg must be ES256
    $x = $cose[-2] ?? ''; $y = $cose[-3] ?? '';
    if ( strlen( $x ) !== 32 || strlen( $y ) !== 32 ) return '';
    $der = hex2bin( '3059301306072a8648ce3d020106082a8648ce3d030107034200' ) . "\x04" . $x . $y;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $der ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";
}

function vpg_passkeys( $uid ) {
    return array_values( array_filter( (array) get_user_meta( $uid, '_vpg_passkeys', true ), 'is_array' ) );
}

function vpg_webauthn_challenge( $slot ) {
    $c = vpg_b64url_encode( random_bytes( 32 ) );
    set_transient( 'vpg_wa_' . $slot, $c, 5 * MINUTE_IN_SECONDS );
    return $c;
}

/* Registration options · logged-in members add a passkey */
add_action( 'wp_ajax_vpg_pk_reg_options', function () {
    check_ajax_referer( 'vpg_passkey' );
    $u = wp_get_current_user();
    wp_send_json_success( [
        'challenge' => vpg_webauthn_challenge( 'reg_' . $u->ID ),
        'rp'        => [ 'id' => wp_parse_url( home_url(), PHP_URL_HOST ), 'name' => get_bloginfo( 'name' ) ],
        'user'      => [ 'id' => vpg_b64url_encode( 'vpg-uid-' . $u->ID ), 'name' => $u->user_login, 'displayName' => $u->display_name ],
        'pubKeyCredParams' => [ [ 'type' => 'public-key', 'alg' => -7 ] ],
        'authenticatorSelection' => [ 'residentKey' => 'preferred', 'userVerification' => 'preferred' ],
        'timeout'   => 60000,
        'attestation' => 'none',
        'excludeCredentials' => array_map( fn( $k ) => [ 'type' => 'public-key', 'id' => $k['id'] ], vpg_passkeys( $u->ID ) ),
    ] );
} );

add_action( 'wp_ajax_vpg_pk_reg_verify', function () {
    check_ajax_referer( 'vpg_passkey' );
    $uid    = get_current_user_id();
    $client = json_decode( vpg_b64url_decode( sanitize_text_field( $_POST['clientDataJSON'] ?? '' ) ), true );
    $attRaw = vpg_b64url_decode( $_POST['attestationObject'] ?? '' );
    $stored = get_transient( 'vpg_wa_reg_' . $uid );
    delete_transient( 'vpg_wa_reg_' . $uid );

    if ( ! $client || ( $client['type'] ?? '' ) !== 'webauthn.create'
        || ! $stored || ! hash_equals( $stored, (string) ( $client['challenge'] ?? '' ) )
        || wp_parse_url( (string) ( $client['origin'] ?? '' ), PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
        wp_send_json_error( 'client data mismatch' );
    }

    $att = vpg_cbor_decode( $attRaw );
    $authData = $att['authData'] ?? '';
    if ( strlen( $authData ) < 55 || ! ( ord( $authData[32] ) & 0x40 ) ) wp_send_json_error( 'no credential data' );

    $idLen  = unpack( 'n', substr( $authData, 53, 2 ) )[1];
    $credId = substr( $authData, 55, $idLen );
    $cosePos = 0;
    $cose    = vpg_cbor_decode( substr( $authData, 55 + $idLen ), $cosePos );
    $pem     = vpg_cose_to_pem( $cose );
    if ( ! $pem ) wp_send_json_error( __( 'This authenticator uses an unsupported key type.', 'vpg-v2' ) );

    $keys   = vpg_passkeys( $uid );
    $keys[] = [
        'id'    => vpg_b64url_encode( $credId ),
        'pem'   => $pem,
        'label' => mb_substr( sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) ), 0, 40 ) ?: gmdate( 'M Y' ),
        'added' => current_time( 'mysql' ),
    ];
    update_user_meta( $uid, '_vpg_passkeys', array_slice( $keys, -5 ) );
    wp_send_json_success( [ 'count' => count( $keys ) ] );
} );

add_action( 'admin_post_vpg_pk_remove', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_passkey_remove' );
    $uid  = get_current_user_id();
    $drop = sanitize_text_field( wp_unslash( $_POST['key_id'] ?? '' ) );
    update_user_meta( $uid, '_vpg_passkeys', array_values( array_filter( vpg_passkeys( $uid ), fn( $k ) => $k['id'] !== $drop ) ) );
    wp_safe_redirect( home_url( '/dashboard/#security' ) );
    exit;
} );

/* Login options · usernameless, discoverable credentials */
add_action( 'wp_ajax_nopriv_vpg_pk_login_options', 'vpg_pk_login_options' );
add_action( 'wp_ajax_vpg_pk_login_options', 'vpg_pk_login_options' );
function vpg_pk_login_options() {
    $token = vpg_b64url_encode( random_bytes( 16 ) );
    wp_send_json_success( [
        'token'            => $token,
        'challenge'        => vpg_webauthn_challenge( 'log_' . $token ),
        'rpId'             => wp_parse_url( home_url(), PHP_URL_HOST ),
        'userVerification' => 'preferred',
        'timeout'          => 60000,
    ] );
}

add_action( 'wp_ajax_nopriv_vpg_pk_login_verify', 'vpg_pk_login_verify' );
add_action( 'wp_ajax_vpg_pk_login_verify', 'vpg_pk_login_verify' );
function vpg_pk_login_verify() {
    $token  = sanitize_text_field( $_POST['token'] ?? '' );
    $stored = $token ? get_transient( 'vpg_wa_log_' . $token ) : false;
    if ( $token ) delete_transient( 'vpg_wa_log_' . $token );

    $client = json_decode( vpg_b64url_decode( sanitize_text_field( $_POST['clientDataJSON'] ?? '' ) ), true );
    if ( ! $client || ( $client['type'] ?? '' ) !== 'webauthn.get'
        || ! $stored || ! hash_equals( $stored, (string) ( $client['challenge'] ?? '' ) )
        || wp_parse_url( (string) ( $client['origin'] ?? '' ), PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
        wp_send_json_error( 'client data mismatch' );
    }

    // The authenticator hands back our user handle — that's the account.
    $handle = vpg_b64url_decode( sanitize_text_field( $_POST['userHandle'] ?? '' ) );
    if ( ! preg_match( '/^vpg-uid-(\d+)$/', $handle, $m ) ) wp_send_json_error( 'unknown account' );
    $uid  = (int) $m[1];
    $user = get_userdata( $uid );
    if ( ! $user ) wp_send_json_error( 'unknown account' );

    $credId = sanitize_text_field( $_POST['credentialId'] ?? '' );
    $pem    = '';
    foreach ( vpg_passkeys( $uid ) as $k ) {
        if ( hash_equals( $k['id'], $credId ) ) { $pem = $k['pem']; break; }
    }
    if ( ! $pem ) wp_send_json_error( 'unknown passkey' );

    $authData = vpg_b64url_decode( $_POST['authenticatorData'] ?? '' );
    $sig      = vpg_b64url_decode( $_POST['signature'] ?? '' );
    if ( strlen( $authData ) < 37 || ! ( ord( $authData[32] ) & 0x01 ) ) wp_send_json_error( 'not verified' );

    $signed = $authData . hash( 'sha256', vpg_b64url_decode( sanitize_text_field( $_POST['clientDataJSON'] ?? '' ) ), true );
    if ( openssl_verify( $signed, $sig, $pem, OPENSSL_ALGO_SHA256 ) !== 1 ) wp_send_json_error( 'bad signature' );

    wp_set_current_user( $uid );
    wp_set_auth_cookie( $uid, true );
    do_action( 'wp_login', $user->user_login, $user );
    wp_send_json_success( [ 'redirect' => home_url( '/dashboard/' ) ] );
}

/* The login screen offers the passkey path under the form */
add_action( 'login_footer', function () {
    $ajax = admin_url( 'admin-ajax.php' );
    ?>
    <div style="text-align:center;margin:16px 0">
        <button type="button" id="vpg-pk-login" class="button button-large" style="width:270px">🔑 <?php esc_html_e( 'Sign in with a passkey', 'vpg-v2' ); ?></button>
        <p id="vpg-pk-msg" style="font-size:12px;color:#646970"></p>
    </div>
    <script>
    (function () {
        var btn = document.getElementById('vpg-pk-login'), msg = document.getElementById('vpg-pk-msg');
        if (!window.PublicKeyCredential) { btn.parentNode.hidden = true; return; }
        function b64uToBuf(s) { s = s.replace(/-/g, '+').replace(/_/g, '/'); var b = atob(s), a = new Uint8Array(b.length); for (var i = 0; i < b.length; i++) a[i] = b.charCodeAt(i); return a.buffer; }
        function bufToB64u(b) { var a = new Uint8Array(b), s = ''; for (var i = 0; i < a.length; i++) s += String.fromCharCode(a[i]); return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''); }
        btn.addEventListener('click', function () {
            msg.textContent = '…';
            fetch(<?php echo wp_json_encode( $ajax ); ?> + '?action=vpg_pk_login_options').then(function (r) { return r.json(); }).then(function (res) {
                if (!res || !res.success) throw 0;
                var o = res.data;
                return navigator.credentials.get({ publicKey: {
                    challenge: b64uToBuf(o.challenge), rpId: o.rpId,
                    userVerification: o.userVerification, timeout: o.timeout
                } }).then(function (cred) {
                    var body = new URLSearchParams();
                    body.set('action', 'vpg_pk_login_verify');
                    body.set('token', o.token);
                    body.set('credentialId', cred.id);
                    body.set('clientDataJSON', bufToB64u(cred.response.clientDataJSON));
                    body.set('authenticatorData', bufToB64u(cred.response.authenticatorData));
                    body.set('signature', bufToB64u(cred.response.signature));
                    body.set('userHandle', cred.response.userHandle ? bufToB64u(cred.response.userHandle) : '');
                    return fetch(<?php echo wp_json_encode( $ajax ); ?>, { method: 'POST', body: body, credentials: 'same-origin' });
                });
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success) { location.href = res.data.redirect; }
                else { msg.textContent = <?php echo wp_json_encode( __( 'Passkey sign-in failed — use your password.', 'vpg-v2' ) ); ?>; }
            }).catch(function () {
                msg.textContent = <?php echo wp_json_encode( __( 'Passkey sign-in cancelled.', 'vpg-v2' ) ); ?>;
            });
        });
    })();
    </script>
    <?php
} );


/* ════════════════════════════════════════════════════════════════ */
/*  1017 · Backup codes · ten single-use recovery codes for 2FA      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_generate_backup_codes( $uid ) {
    $plain = [];
    $hashed = [];
    for ( $i = 0; $i < 10; $i++ ) {
        // groups of 4 from an unambiguous alphabet, e.g. 7Q4K-9F2M
        $a = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ( $j = 0; $j < 8; $j++ ) $code .= $a[ random_int( 0, strlen( $a ) - 1 ) ];
        $show      = substr( $code, 0, 4 ) . '-' . substr( $code, 4 );
        $plain[]   = $show;
        $hashed[]  = hash( 'sha256', strtoupper( $code ) );
    }
    update_user_meta( $uid, '_vpg_backup_codes', $hashed );
    return $plain;
}

function vpg_backup_codes_left( $uid ) {
    return count( array_filter( (array) get_user_meta( $uid, '_vpg_backup_codes', true ) ) );
}

function vpg_consume_backup_code( $uid, $code ) {
    $norm = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $code ) );
    if ( strlen( $norm ) !== 8 ) return false;
    $want   = hash( 'sha256', $norm );
    $hashed = array_filter( (array) get_user_meta( $uid, '_vpg_backup_codes', true ) );
    foreach ( $hashed as $k => $h ) {
        if ( hash_equals( $h, $want ) ) {
            unset( $hashed[ $k ] );                            // single use
            update_user_meta( $uid, '_vpg_backup_codes', array_values( $hashed ) );
            return true;
        }
    }
    return false;
}

add_action( 'admin_post_vpg_totp_backup', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_totp' );
    $uid = get_current_user_id();
    if ( get_user_meta( $uid, '_vpg_totp_on', true ) === '1' ) {
        $codes = vpg_generate_backup_codes( $uid );
        set_transient( 'vpg_backup_show_' . $uid, $codes, 5 * MINUTE_IN_SECONDS );  // shown once
    }
    wp_safe_redirect( home_url( '/dashboard/#security' ) );
    exit;
} );
