<?php
/**
 * VPG v3 — Q9 · a good neighbour in the open web.
 *
 *   0921  ActivityPub · WebFinger + Actor + Outbox + Inbox (Follow),
 *         new journal posts are delivered to followers, signed
 *   0922  Fediverse replies · inbox Create(Note) with inReplyTo to one
 *         of our posts becomes a pending (moderated) comment
 *   0936  Webmentions · receive, verify, hold for moderation
 *
 * One account represents the magazine: acct:vpg@<host>. Pure openssl,
 * no packages. Federation is opt-out via the vpg_federation_enabled filter.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function vpg_fed_on() { return (bool) apply_filters( 'vpg_federation_enabled', true ); }
function vpg_fed_user() { return sanitize_key( apply_filters( 'vpg_federation_username', 'vpg' ) ); }
function vpg_fed_host() { return wp_parse_url( home_url(), PHP_URL_HOST ); }
function vpg_fed_actor_url() { return home_url( '/activitypub/actor' ); }

/* ─── RSA keypair · one option, created once ─────────────────────── */
function vpg_fed_keys() {
    $k = get_option( 'vpg_fed_keys' );
    if ( is_array( $k ) && ! empty( $k['private'] ) ) return $k;
    $res = openssl_pkey_new( [ 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA ] );
    if ( ! $res ) return [];
    openssl_pkey_export( $res, $priv );
    $pub = openssl_pkey_get_details( $res )['key'];
    $k   = [ 'private' => $priv, 'public' => $pub ];
    update_option( 'vpg_fed_keys', $k, false );
    return $k;
}

/* ─── Rewrites ───────────────────────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^\.well-known/webfinger$', 'index.php?vpg_fed=webfinger', 'top' );
    add_rewrite_rule( '^activitypub/actor/?$',    'index.php?vpg_fed=actor',    'top' );
    add_rewrite_rule( '^activitypub/inbox/?$',    'index.php?vpg_fed=inbox',    'top' );
    add_rewrite_rule( '^activitypub/outbox/?$',   'index.php?vpg_fed=outbox',   'top' );
    add_rewrite_rule( '^webmention/?$',           'index.php?vpg_fed=webmention', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_fed'; return $v; } );

/* ─── Discovery hints in the document head + headers ─────────────── */
add_action( 'wp_head', function () {
    if ( ! vpg_fed_on() ) return;
    printf( '<link rel="webmention" href="%s">' . "\n", esc_url( home_url( '/webmention' ) ) );
    if ( is_front_page() ) {
        printf( '<link rel="alternate" type="application/activity+json" href="%s">' . "\n", esc_url( vpg_fed_actor_url() ) );
    }
}, 3 );
add_filter( 'wp_headers', function ( $h ) {
    if ( vpg_fed_on() ) $h['Link'] = ( isset( $h['Link'] ) ? $h['Link'] . ', ' : '' ) . '<' . home_url( '/webmention' ) . '>; rel="webmention"';
    return $h;
} );

/* ─── The routes ─────────────────────────────────────────────────── */
add_action( 'template_redirect', function () {
    $route = get_query_var( 'vpg_fed' );
    if ( ! $route ) return;
    if ( ! vpg_fed_on() ) { status_header( 404 ); exit; }

    if ( $route === 'webfinger' ) {
        $res  = sanitize_text_field( $_GET['resource'] ?? '' );
        $want = 'acct:' . vpg_fed_user() . '@' . vpg_fed_host();
        if ( $res !== $want ) { status_header( 404 ); exit; }
        header( 'Content-Type: application/jrd+json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        echo wp_json_encode( [
            'subject' => $want,
            'links'   => [ [ 'rel' => 'self', 'type' => 'application/activity+json', 'href' => vpg_fed_actor_url() ] ],
        ] );
        exit;
    }

    if ( $route === 'actor' ) {
        $keys = vpg_fed_keys();
        header( 'Content-Type: application/activity+json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        echo wp_json_encode( [
            '@context'          => [ 'https://www.w3.org/ns/activitystreams', 'https://w3id.org/security/v1' ],
            'id'                => vpg_fed_actor_url(),
            'type'              => 'Service',
            'preferredUsername' => vpg_fed_user(),
            'name'              => get_bloginfo( 'name' ),
            'summary'           => get_bloginfo( 'description' ),
            'url'               => home_url( '/' ),
            // 1036 · the actor carries the site icon so it shows an avatar in the Fediverse
            'icon'              => ( $ico = get_site_icon_url( 512 ) ) ? [ 'type' => 'Image', 'url' => $ico ] : null,
            'inbox'             => home_url( '/activitypub/inbox' ),
            'outbox'            => home_url( '/activitypub/outbox' ),
            'publicKey'         => [
                'id'           => vpg_fed_actor_url() . '#main-key',
                'owner'        => vpg_fed_actor_url(),
                'publicKeyPem' => $keys['public'] ?? '',
            ],
        ], JSON_UNESCAPED_SLASHES );
        exit;
    }

    if ( $route === 'outbox' ) {
        $posts = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 20 ] );
        $items = [];
        foreach ( $posts as $p ) $items[] = vpg_fed_create_activity( $p );
        header( 'Content-Type: application/activity+json; charset=utf-8' );
        echo wp_json_encode( [
            '@context'     => 'https://www.w3.org/ns/activitystreams',
            'id'           => home_url( '/activitypub/outbox' ),
            'type'         => 'OrderedCollection',
            'totalItems'   => count( $items ),
            'orderedItems' => $items,
        ], JSON_UNESCAPED_SLASHES );
        exit;
    }

    if ( $route === 'inbox' ) {
        vpg_fed_handle_inbox();
        exit;
    }

    if ( $route === 'webmention' ) {
        vpg_fed_handle_webmention();
        exit;
    }
} );

/* ─── Build a Create(Note) activity for a post ───────────────────── */
function vpg_fed_create_activity( $p ) {
    $url  = get_permalink( $p );
    $note = [
        'id'           => $url,
        'type'         => 'Note',
        'attributedTo' => vpg_fed_actor_url(),
        'content'      => '<p><strong>' . esc_html( get_the_title( $p ) ) . '</strong></p>' . '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( $p->post_content ), 60 ) ) . '</p><p><a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a></p>',
        'published'    => gmdate( 'c', strtotime( $p->post_date_gmt ) ),
        'url'          => $url,
        'to'           => [ 'https://www.w3.org/ns/activitystreams#Public' ],
    ];
    return [
        '@context'  => 'https://www.w3.org/ns/activitystreams',
        'id'        => $url . '#create',
        'type'      => 'Create',
        'actor'     => vpg_fed_actor_url(),
        'published' => $note['published'],
        'to'        => $note['to'],
        'object'    => $note,
    ];
}

/* ─── HTTP Signatures ────────────────────────────────────────────── */
function vpg_fed_sign_headers( $url, $body ) {
    $keys   = vpg_fed_keys();
    $u      = wp_parse_url( $url );
    $date   = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
    $digest = 'SHA-256=' . base64_encode( hash( 'sha256', $body, true ) );
    $target = '(request-target): post ' . $u['path'];
    $string = $target . "\nhost: " . $u['host'] . "\ndate: " . $date . "\ndigest: " . $digest;
    openssl_sign( $string, $sig, $keys['private'], OPENSSL_ALGO_SHA256 );
    $header = sprintf(
        'keyId="%s",algorithm="rsa-sha256",headers="(request-target) host date digest",signature="%s"',
        vpg_fed_actor_url() . '#main-key', base64_encode( $sig )
    );
    return [
        'Host'          => $u['host'],
        'Date'          => $date,
        'Digest'        => $digest,
        'Signature'     => $header,
        'Content-Type'  => 'application/activity+json',
        'Accept'        => 'application/activity+json',
    ];
}

/** Verify an incoming request's HTTP signature against its actor's key. */
function vpg_fed_verify_signature( $body ) {
    $sig_header = $_SERVER['HTTP_SIGNATURE'] ?? '';
    if ( ! $sig_header ) return false;
    $parts = [];
    foreach ( explode( ',', $sig_header ) as $kv ) {
        if ( preg_match( '/(\w+)="(.*)"/', trim( $kv ), $m ) ) $parts[ $m[1] ] = $m[2];
    }
    if ( empty( $parts['keyId'] ) || empty( $parts['signature'] ) || empty( $parts['headers'] ) ) return false;

    // Digest must match the body we received
    if ( ! empty( $_SERVER['HTTP_DIGEST'] ) ) {
        $want = 'SHA-256=' . base64_encode( hash( 'sha256', $body, true ) );
        if ( ! hash_equals( $want, $_SERVER['HTTP_DIGEST'] ) ) return false;
    }

    $actor_url = preg_replace( '/#.*$/', '', $parts['keyId'] );
    $pem       = vpg_fed_fetch_key( $actor_url );
    if ( ! $pem ) return false;

    $lines = [];
    foreach ( explode( ' ', $parts['headers'] ) as $h ) {
        if ( $h === '(request-target)' ) {
            $lines[] = '(request-target): post ' . wp_parse_url( home_url( '/activitypub/inbox' ), PHP_URL_PATH );
        } else {
            $val = $_SERVER[ 'HTTP_' . strtoupper( str_replace( '-', '_', $h ) ) ] ?? '';
            $lines[] = $h . ': ' . $val;
        }
    }
    $signing = implode( "\n", $lines );
    return openssl_verify( $signing, base64_decode( $parts['signature'] ), $pem, OPENSSL_ALGO_SHA256 ) === 1;
}

function vpg_fed_fetch_key( $actor_url ) {
    $cached = get_transient( 'vpg_fed_key_' . md5( $actor_url ) );
    if ( $cached ) return $cached;
    $res = wp_remote_get( $actor_url, [ 'timeout' => 8, 'headers' => [ 'Accept' => 'application/activity+json' ] ] );
    if ( is_wp_error( $res ) ) return '';
    $j = json_decode( wp_remote_retrieve_body( $res ), true );
    $pem = $j['publicKey']['publicKeyPem'] ?? '';
    if ( $pem ) set_transient( 'vpg_fed_key_' . md5( $actor_url ), $pem, DAY_IN_SECONDS );
    return $pem;
}

function vpg_fed_actor_name( $actor_url ) {
    $res = wp_remote_get( $actor_url, [ 'timeout' => 8, 'headers' => [ 'Accept' => 'application/activity+json' ] ] );
    if ( is_wp_error( $res ) ) return $actor_url;
    $j = json_decode( wp_remote_retrieve_body( $res ), true );
    $h = wp_parse_url( $actor_url, PHP_URL_HOST );
    $n = $j['preferredUsername'] ?? ( $j['name'] ?? 'someone' );
    return '@' . $n . '@' . $h;
}

/* ─── Inbox · Follow, Undo, Create(reply) ────────────────────────── */
function vpg_fed_handle_inbox() {
    $body = file_get_contents( 'php://input' );
    $act  = json_decode( $body, true );
    if ( ! is_array( $act ) || ! vpg_fed_verify_signature( $body ) ) { status_header( 401 ); exit; }

    $type = $act['type'] ?? '';

    if ( $type === 'Follow' ) {
        $followers = (array) get_option( 'vpg_fed_followers', [] );
        $who = is_string( $act['actor'] ) ? $act['actor'] : '';
        if ( $who ) {
            $followers[ $who ] = time();
            update_option( 'vpg_fed_followers', $followers, false );
            vpg_fed_send_accept( $who, $act );
        }
        status_header( 202 ); exit;
    }

    if ( $type === 'Undo' && ( $act['object']['type'] ?? '' ) === 'Follow' ) {
        $followers = (array) get_option( 'vpg_fed_followers', [] );
        unset( $followers[ is_string( $act['actor'] ) ? $act['actor'] : '' ] );
        update_option( 'vpg_fed_followers', $followers, false );
        status_header( 202 ); exit;
    }

    // 0922 · a reply to one of our notes becomes a moderated comment
    if ( $type === 'Create' && ( $act['object']['type'] ?? '' ) === 'Note' ) {
        $obj      = $act['object'];
        $inReply  = $obj['inReplyTo'] ?? '';
        $post_id  = $inReply ? url_to_postid( $inReply ) : 0;
        if ( $post_id ) {
            $author = vpg_fed_actor_name( is_string( $act['actor'] ) ? $act['actor'] : '' );
            $text   = trim( wp_strip_all_tags( $obj['content'] ?? '' ) );
            if ( $text !== '' ) {
                wp_insert_comment( [
                    'comment_post_ID'      => $post_id,
                    'comment_author'       => $author,
                    'comment_author_url'   => is_string( $act['actor'] ) ? $act['actor'] : '',
                    'comment_content'      => $text,
                    'comment_approved'     => 0,                      // held for moderation
                    'comment_type'         => 'comment',
                    'comment_meta'         => [ '_vpg_fediverse' => 1, '_vpg_fed_id' => $obj['id'] ?? '' ],
                ] );
            }
        }
        status_header( 202 ); exit;
    }

    // 1037 · Fediverse resonance — count boosts (Announce) and likes (Like) per post
    if ( $type === 'Announce' || $type === 'Like' ) {
        $obj = $act['object'] ?? '';
        $ref = is_array( $obj ) ? ( $obj['id'] ?? '' ) : $obj;
        $pid = $ref ? url_to_postid( (string) $ref ) : 0;
        if ( $pid ) {
            $key = 'Announce' === $type ? '_vpg_fed_boosts' : '_vpg_fed_likes';
            update_post_meta( $pid, $key, (int) get_post_meta( $pid, $key, true ) + 1 );
        }
        status_header( 202 ); exit;
    }

    status_header( 202 );
    exit;
}

function vpg_fed_send_accept( $actor_url, $follow ) {
    $res = wp_remote_get( $actor_url, [ 'timeout' => 8, 'headers' => [ 'Accept' => 'application/activity+json' ] ] );
    if ( is_wp_error( $res ) ) return;
    $inbox = json_decode( wp_remote_retrieve_body( $res ), true )['inbox'] ?? '';
    if ( ! $inbox ) return;
    $accept = wp_json_encode( [
        '@context' => 'https://www.w3.org/ns/activitystreams',
        'id'       => home_url( '/activitypub/accept/' . md5( wp_json_encode( $follow ) ) ),
        'type'     => 'Accept',
        'actor'    => vpg_fed_actor_url(),
        'object'   => $follow,
    ], JSON_UNESCAPED_SLASHES );
    wp_remote_post( $inbox, [ 'timeout' => 8, 'headers' => vpg_fed_sign_headers( $inbox, $accept ), 'body' => $accept ] );
}

/* ─── Deliver new journal posts to followers (async via cron) ────── */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( ! vpg_fed_on() || $post->post_type !== 'post' ) return;
    if ( $new === 'publish' && $old !== 'publish' ) {
        wp_schedule_single_event( time() + 30, 'vpg_fed_deliver', [ $post->ID ] );
    }
}, 10, 3 );

add_action( 'vpg_fed_deliver', function ( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_status !== 'publish' ) return;
    $followers = (array) get_option( 'vpg_fed_followers', [] );
    if ( ! $followers ) return;
    $activity = wp_json_encode( vpg_fed_create_activity( $post ), JSON_UNESCAPED_SLASHES );

    $inboxes = [];
    foreach ( array_keys( $followers ) as $actor_url ) {
        $res = wp_remote_get( $actor_url, [ 'timeout' => 8, 'headers' => [ 'Accept' => 'application/activity+json' ] ] );
        if ( is_wp_error( $res ) ) continue;
        $j = json_decode( wp_remote_retrieve_body( $res ), true );
        $inbox = $j['endpoints']['sharedInbox'] ?? ( $j['inbox'] ?? '' );
        if ( $inbox ) $inboxes[ $inbox ] = true;
    }
    foreach ( array_keys( $inboxes ) as $inbox ) {
        wp_remote_post( $inbox, [ 'timeout' => 8, 'headers' => vpg_fed_sign_headers( $inbox, $activity ), 'body' => $activity ] );
    }
} );

/* Fediverse replies wear a small badge in the comment list */
add_filter( 'comment_text', function ( $text, $comment = null ) {
    if ( $comment && get_comment_meta( $comment->comment_ID, '_vpg_fediverse', true ) ) {
        $text .= ' <span style="font-size:11px;color:var(--g-faint,#9C9A95)">· ' . esc_html__( 'via the Fediverse', 'vpg-v2' ) . '</span>';
    }
    return $text;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  0936 · Webmentions · receive, verify, hold for moderation        */
/* ════════════════════════════════════════════════════════════════ */
function vpg_fed_handle_webmention() {
    if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'POST' ) {
        status_header( 405 );
        header( 'Allow: POST' );
        exit( 'Send POST source=&target=' );
    }
    $source = esc_url_raw( wp_unslash( $_POST['source'] ?? '' ) );
    $target = esc_url_raw( wp_unslash( $_POST['target'] ?? '' ) );
    if ( ! $source || ! $target || $source === $target ) { status_header( 400 ); exit( 'source and target required' ); }

    $post_id = url_to_postid( $target );
    if ( ! $post_id ) { status_header( 400 ); exit( 'target is not a post here' ); }

    // Verify the source actually links to the target
    $res = wp_remote_get( $source, [ 'timeout' => 8 ] );
    if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) { status_header( 400 ); exit( 'source unreachable' ); }
    $html = wp_remote_retrieve_body( $res );
    if ( strpos( $html, $target ) === false ) { status_header( 400 ); exit( 'source does not mention target' ); }

    // Pull a title for the mention
    $name = $source;
    if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $m ) ) $name = trim( html_entity_decode( wp_strip_all_tags( $m[1] ) ) );

    // Idempotent · one mention per source→target
    $existing = get_comments( [ 'post_id' => $post_id, 'meta_key' => '_vpg_webmention_source', 'meta_value' => $source, 'count' => true ] );
    if ( ! $existing ) {
        wp_insert_comment( [
            'comment_post_ID'    => $post_id,
            'comment_author'     => mb_substr( $name, 0, 100 ),
            'comment_author_url' => $source,
            'comment_content'    => sprintf( __( 'Mentioned this: %s', 'vpg-v2' ), $source ),
            'comment_approved'   => 0,
            'comment_type'       => 'comment',
            'comment_meta'       => [ '_vpg_webmention_source' => $source ],
        ] );
    }
    status_header( 202 );
    exit( 'Accepted' );
}
