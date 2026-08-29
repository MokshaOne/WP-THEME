<?php
/**
 * VPG v3 — Advanced.
 *
 *   - Live event check-in · "I'm here" on the day, count polls every 10 s
 *   - Photo fingerprints · dHash on upload, duplicate + resolution warnings
 *   - Trust levels · new → member → trusted → veteran from real behaviour
 *   - Bilingual search · DE/EN photography synonyms expand the query
 *   - AI alt text · integration point for a self-hosted captioning server
 *     (no-op until VPG_CAPTION_URL is defined in wp-config.php)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Live check-in · event day only, RSVP'd members                    */
/* ════════════════════════════════════════════════════════════════ */
function vpg_event_is_today( $event_id ) {
    $date = get_post_meta( $event_id, '_vpg_event_date', true );
    $ts   = $date ? strtotime( $date ) : false;
    return $ts && gmdate( 'Y-m-d', $ts ) === current_time( 'Y-m-d' );
}

function vpg_event_checkins( $event_id ) {
    $ids = get_post_meta( $event_id, '_vpg_checkins', true );
    return is_array( $ids ) ? array_values( array_filter( array_map( 'intval', $ids ) ) ) : [];
}

add_action( 'admin_post_vpg_checkin', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_checkin' );
    $event_id = (int) ( $_POST['event'] ?? 0 );
    if ( ! $event_id || get_post_type( $event_id ) !== 'vpg_event' || ! vpg_event_is_today( $event_id ) ) {
        wp_safe_redirect( wp_get_referer() ?: home_url() );
        exit;
    }
    $uid  = get_current_user_id();
    $list = vpg_event_checkins( $event_id );
    if ( ! in_array( $uid, $list, true ) ) {
        $list[] = $uid;
        update_post_meta( $event_id, '_vpg_checkins', $list );
    }
    wp_safe_redirect( get_permalink( $event_id ) . '#rsvp' );
    exit;
} );

/* Public count endpoint · the event page polls this while the walk runs */
add_action( 'wp_ajax_vpg_checkin_count', 'vpg_checkin_count' );
add_action( 'wp_ajax_nopriv_vpg_checkin_count', 'vpg_checkin_count' );
function vpg_checkin_count() {
    $event_id = (int) ( $_GET['event'] ?? 0 );
    wp_send_json_success( [ 'count' => count( vpg_event_checkins( $event_id ) ) ] );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Photo fingerprints · dHash (64-bit) + resolution sanity           */
/* ════════════════════════════════════════════════════════════════ */
function vpg_photo_dhash( $file ) {
    if ( ! function_exists( 'imagecreatefromstring' ) ) return '';
    $src = @imagecreatefromstring( (string) @file_get_contents( $file ) );
    if ( ! $src ) return '';
    $im = imagecreatetruecolor( 9, 8 );
    imagecopyresampled( $im, $src, 0, 0, 0, 0, 9, 8, imagesx( $src ), imagesy( $src ) );
    imagedestroy( $src );
    $bits = '';
    for ( $y = 0; $y < 8; $y++ ) {
        for ( $x = 0; $x < 8; $x++ ) {
            $l = imagecolorat( $im, $x, $y );
            $r = imagecolorat( $im, $x + 1, $y );
            $lum_l = ( ( $l >> 16 ) & 255 ) * 0.3 + ( ( $l >> 8 ) & 255 ) * 0.59 + ( $l & 255 ) * 0.11;
            $lum_r = ( ( $r >> 16 ) & 255 ) * 0.3 + ( ( $r >> 8 ) & 255 ) * 0.59 + ( $r & 255 ) * 0.11;
            $bits .= $lum_l > $lum_r ? '1' : '0';
        }
    }
    imagedestroy( $im );
    return $bits;
}

function vpg_hamming( $a, $b ) {
    if ( strlen( $a ) !== 64 || strlen( $b ) !== 64 ) return 64;
    $d = 0;
    for ( $i = 0; $i < 64; $i++ ) if ( $a[ $i ] !== $b[ $i ] ) $d++;
    return $d;
}

/**
 * Fingerprint one submission photo · returns a warning line for the
 * editorial email ('' when clean). Stores the hash for future checks.
 */
function vpg_check_photo_quality( $att_id ) {
    $file = get_attached_file( $att_id );
    if ( ! $file || ! file_exists( $file ) ) return '';

    $notes = [];

    // Resolution floor · a 1440 layout wants ≥1200px on the long edge
    $size = @getimagesize( $file );
    if ( $size && max( $size[0], $size[1] ) < 1200 ) {
        $notes[] = sprintf( 'low resolution (%dx%d)', $size[0], $size[1] );
    }

    // dHash duplicate check against recent member photos
    $hash = vpg_photo_dhash( $file );
    if ( $hash ) {
        update_post_meta( $att_id, '_vpg_dhash', $hash );
        $recent = get_posts( [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => 300,
            'exclude'        => [ $att_id ],
            'meta_key'       => '_vpg_dhash',
            'fields'         => 'ids',
        ] );
        foreach ( $recent as $rid ) {
            $other = (string) get_post_meta( $rid, '_vpg_dhash', true );
            $dist  = vpg_hamming( $hash, $other );
            if ( $dist <= 6 ) {
                $notes[] = sprintf( 'near-duplicate of attachment #%d (distance %d)', $rid, $dist );
                break;
            }
        }
    }

    return $notes ? "\nPhoto check #{$att_id}: " . implode( ' · ', $notes ) : '';
}

/* ════════════════════════════════════════════════════════════════ */
/*  Trust levels · earned, never bought                               */
/*    0 new · 1 member · 2 trusted · 3 veteran                        */
/* ════════════════════════════════════════════════════════════════ */
function vpg_trust_level( $uid = null ) {
    $uid = $uid ?: get_current_user_id();
    if ( ! $uid ) return 0;
    $user = get_userdata( $uid );
    if ( ! $user ) return 0;
    if ( user_can( $uid, 'edit_others_posts' ) ) return 3;

    $days      = ( time() - strtotime( $user->user_registered ) ) / DAY_IN_SECONDS;
    $published = (int) count_user_posts( $uid, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'post' ], true );
    $verified  = ! function_exists( 'vpg_is_verified' ) || vpg_is_verified( $uid );

    // Reports against this member's notes pull the level down
    $reported = get_comments( [ 'user_id' => $uid, 'meta_key' => '_vpg_reports', 'count' => true ] );
    if ( $reported >= 2 ) return $verified ? 1 : 0;

    if ( ! $verified ) return 0;
    if ( $days >= 180 && $published >= 10 ) return 3;
    if ( $days >= 30  && $published >= 3 )  return 2;
    return 1;
}

function vpg_trust_label( $level ) {
    $labels = [ __( 'New', 'vpg-v2' ), __( 'Member', 'vpg-v2' ), __( 'Trusted', 'vpg-v2' ), __( 'Veteran', 'vpg-v2' ) ];
    return $labels[ max( 0, min( 3, (int) $level ) ) ];
}

/* ─── Contribution ranks · display titles from published work count ─
 * Deliberately NOT WordPress roles: capabilities never change with
 * volume (everything stays pending → review desk, whatever the rank);
 * moderation privileges come from vpg_trust_level() above. Ranks are
 * recognition — the ladder every profile and dashboard shows.
 */
function vpg_member_ranks() {
    return apply_filters( 'vpg_member_ranks', [
        0   => __( 'Member', 'vpg-v2' ),        // 0–10
        11  => __( 'Contributor', 'vpg-v2' ),   // 11–50
        51  => __( 'Documentarian', 'vpg-v2' ), // 51–100
        101 => __( 'Resident', 'vpg-v2' ),      // 100+
    ] );
}

/**
 * Rank info for a member:
 * [ 'label', 'count', 'next' => label|null, 'next_at' => int|null ].
 */
function vpg_member_rank( $uid ) {
    $count = (int) count_user_posts(
        $uid,
        function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ],
        true
    );
    $ranks = vpg_member_ranks();
    ksort( $ranks );
    $label = __( 'Member', 'vpg-v2' );
    $next  = null;
    $at    = null;
    foreach ( $ranks as $min => $name ) {
        if ( $count >= $min ) {
            $label = $name;
        } elseif ( $next === null ) {
            $next = $name;
            $at   = $min;
        }
    }
    return [ 'label' => $label, 'count' => $count, 'next' => $next, 'next_at' => $at ];
}

/* Trusted members' notes publish without moderation · new members queue */
add_filter( 'pre_comment_approved', function ( $approved, $commentdata ) {
    $uid = (int) ( $commentdata['user_id'] ?? 0 );
    if ( ! $uid ) return $approved;
    $level = vpg_trust_level( $uid );
    if ( $level >= 2 ) return 1;   // trusted · straight through
    if ( $level === 0 ) return 0;  // new/unverified · always moderated
    return $approved;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Bilingual search · DE/EN photography synonyms                     */
/* ════════════════════════════════════════════════════════════════ */
function vpg_search_synonyms() {
    return [
        'bezirk'             => 'district',
        'objektiv'           => 'lens',
        'stativ'             => 'tripod',
        'blende'             => 'aperture',
        'belichtung'         => 'exposure',
        'langzeitbelichtung' => 'long exposure',
        'goldene stunde'     => 'golden hour',
        'blaue stunde'       => 'blue hour',
        'schwarzweiß'        => 'black and white',
        'schwarzweiss'       => 'black and white',
        'dunkelkammer'       => 'darkroom',
        'ausstellung'        => 'exhibition',
        'werkstatt'          => 'workshop',
        'gebraucht'          => 'used',
        'sonnenaufgang'      => 'sunrise',
        'sonnenuntergang'    => 'sunset',
        'straßenfotografie'  => 'street photography',
        'strassenfotografie' => 'street photography',
    ];
}

add_filter( 'posts_search', function ( $search, $query ) {
    global $wpdb;
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() || ! $search ) return $search;

    $term = strtolower( trim( $query->get( 's' ) ) );
    if ( ! $term ) return $search;

    $map = vpg_search_synonyms();
    $alt = $map[ $term ] ?? array_search( $term, $map, true );
    if ( ! $alt ) return $search;

    // OR the synonym into the existing WHERE fragment
    $like = '%' . $wpdb->esc_like( $alt ) . '%';
    $syn  = $wpdb->prepare(
        " OR ({$wpdb->posts}.post_title LIKE %s) OR ({$wpdb->posts}.post_content LIKE %s) OR ({$wpdb->posts}.post_excerpt LIKE %s)",
        $like, $like, $like
    );
    // Inject before the final closing parenthesis of the search clause
    $pos = strrpos( $search, ')' );
    if ( $pos !== false ) {
        $search = substr_replace( $search, $syn, $pos, 0 );
    }
    return $search;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  AI alt text · self-hosted captioning integration point            */
/*                                                                    */
/*  define( 'VPG_CAPTION_URL', 'http://127.0.0.1:8080/v1/chat/…' );   */
/*  Any OpenAI-compatible vision endpoint (llama.cpp + LLaVA works).  */
/*  A daily cron fills missing alt texts, 10 images per run.          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! defined( 'VPG_CAPTION_URL' ) || ! VPG_CAPTION_URL ) return;
    if ( ! wp_next_scheduled( 'vpg_caption_run' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vpg_caption_run' );
    }
} );

add_action( 'vpg_caption_run', function () {
    if ( ! defined( 'VPG_CAPTION_URL' ) || ! VPG_CAPTION_URL ) return;

    $missing = get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 10,
        'meta_query'     => [ [ 'key' => '_wp_attachment_image_alt', 'compare' => 'NOT EXISTS' ] ],
    ] );

    foreach ( $missing as $att ) {
        $file = get_attached_file( $att->ID );
        if ( ! $file || filesize( $file ) > 4 * MB_IN_BYTES ) continue;
        $mime = get_post_mime_type( $att->ID ) ?: 'image/jpeg';

        $payload = [
            'model'      => defined( 'VPG_CAPTION_MODEL' ) ? VPG_CAPTION_MODEL : 'default',
            'max_tokens' => 60,
            'messages'   => [ [
                'role'    => 'user',
                'content' => [
                    [ 'type' => 'text', 'text' => 'Describe this photograph in one concise sentence for use as alt text. No preamble.' ],
                    [ 'type' => 'image_url', 'image_url' => [ 'url' => 'data:' . $mime . ';base64,' . base64_encode( file_get_contents( $file ) ) ] ],
                ],
            ] ],
        ];

        $res = wp_remote_post( VPG_CAPTION_URL, [
            'timeout' => 30,
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $payload ),
        ] );
        if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) continue;

        $data = json_decode( wp_remote_retrieve_body( $res ), true );
        $alt  = trim( (string) ( $data['choices'][0]['message']['content'] ?? '' ) );
        if ( $alt ) {
            update_post_meta( $att->ID, '_wp_attachment_image_alt', sanitize_text_field( wp_trim_words( $alt, 30 ) ) );
        }
    }
} );
