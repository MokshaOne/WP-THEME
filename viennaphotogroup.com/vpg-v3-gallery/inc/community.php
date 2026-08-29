<?php
/**
 * VPG v3 — Community.
 *
 *   - Event RSVP · "I'm coming" with attendee list + day-before reminder
 *   - Notification center · per-member inbox on the dashboard
 *   - Activity feed · what's new since the member's last visit
 *   - Monthly member digest · one mail when the month turns
 *   - Photo of the week · one member vote per week
 *   - Photowalk trails · ordered location stops on a vpg_trail
 *   - Competitions · photo entries on a vpg_competition, editors pick
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Notifications · tiny per-user inbox in usermeta                  */
/* ════════════════════════════════════════════════════════════════ */
function vpg_notify_user( $uid, $text, $url = '' ) {
    $list = get_user_meta( $uid, '_vpg_notifications', true );
    $list = is_array( $list ) ? $list : [];
    array_unshift( $list, [
        'text' => sanitize_text_field( $text ),
        'url'  => esc_url_raw( $url ),
        'time' => time(),
        'read' => false,
    ] );
    update_user_meta( $uid, '_vpg_notifications', array_slice( $list, 0, 30 ) );
}

function vpg_get_notifications( $uid = null ) {
    $uid  = $uid ?: get_current_user_id();
    $list = get_user_meta( $uid, '_vpg_notifications', true );
    return is_array( $list ) ? $list : [];
}

add_action( 'admin_post_vpg_notifications_read', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_notifications_read' );
    $uid  = get_current_user_id();
    $list = vpg_get_notifications( $uid );
    foreach ( $list as &$n ) $n['read'] = true;
    unset( $n );
    update_user_meta( $uid, '_vpg_notifications', $list );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) );
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Event RSVP · toggle, attendee list, day-before reminder          */
/* ════════════════════════════════════════════════════════════════ */
function vpg_event_rsvps( $event_id ) {
    $ids = get_post_meta( $event_id, '_vpg_rsvps', true );
    return is_array( $ids ) ? array_values( array_filter( array_map( 'intval', $ids ) ) ) : [];
}

add_action( 'admin_post_vpg_rsvp', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_rsvp' );

    $event_id = (int) ( $_POST['event'] ?? 0 );
    $event    = get_post( $event_id );
    if ( ! $event || $event->post_type !== 'vpg_event' || $event->post_status !== 'publish' ) {
        wp_die( 'Event not found', 404 );
    }

    $uid  = get_current_user_id();
    $list = vpg_event_rsvps( $event_id );
    if ( in_array( $uid, $list, true ) ) {
        $list = array_values( array_diff( $list, [ $uid ] ) );
    } else {
        $list[] = $uid;
        // Day-before reminder · scheduled once per event, when the date parses
        $date = get_post_meta( $event_id, '_vpg_event_date', true );
        $ts   = $date ? strtotime( $date . ' 09:00' ) : false;
        if ( $ts && $ts - DAY_IN_SECONDS > time() && ! get_post_meta( $event_id, '_vpg_rsvp_reminder', true ) ) {
            wp_schedule_single_event( $ts - DAY_IN_SECONDS, 'vpg_event_reminder', [ $event_id ] );
            update_post_meta( $event_id, '_vpg_rsvp_reminder', '1' );
        }
    }
    update_post_meta( $event_id, '_vpg_rsvps', $list );
    wp_safe_redirect( get_permalink( $event_id ) . '#rsvp' );
    exit;
} );

add_action( 'vpg_event_reminder', function ( $event_id ) {
    $event = get_post( $event_id );
    if ( ! $event || $event->post_status !== 'publish' ) return;
    $venue = get_post_meta( $event_id, '_vpg_event_venue', true );

    // 0817 · Current Vienna weather as a hint in the reminder mail
    $wx_line = '';
    if ( function_exists( 'vpg_weather' ) ) {
        $wx_lat = (float) ( get_post_meta( $event_id, '_vpg_event_lat', true ) ?: 48.2082 );
        $wx_lng = (float) ( get_post_meta( $event_id, '_vpg_event_lng', true ) ?: 16.3738 );
        $wx     = vpg_weather( $wx_lat, $wx_lng );
        if ( $wx ) {
            $wx_line = "\n" . sprintf( __( 'Right now at the spot: %1$s, %2$s — check the forecast before you pack.', 'vpg-v2' ), $wx['temp'], $wx['label'] );
        }
    }

    // 0816 · the reminder carries its own calendar file
    $ics_file = '';
    $ev_date  = get_post_meta( $event_id, '_vpg_event_date', true );
    $ev_ts    = $ev_date ? strtotime( $ev_date . ' 18:00' ) : false;
    if ( $ev_ts ) {
        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Vienna Photo Group//Event//EN\r\nBEGIN:VEVENT\r\n"
             . 'UID:vpg-event-' . $event_id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n"
             . 'DTSTART:' . gmdate( 'Ymd\THis\Z', $ev_ts ) . "\r\n"
             . 'DTEND:' . gmdate( 'Ymd\THis\Z', $ev_ts + 2 * HOUR_IN_SECONDS ) . "\r\n"
             . 'SUMMARY:' . str_replace( [ ',', ';' ], [ '\,', '\;' ], $event->post_title ) . "\r\n"
             . ( $venue ? 'LOCATION:' . str_replace( [ ',', ';' ], [ '\,', '\;' ], $venue ) . "\r\n" : '' )
             . 'URL:' . get_permalink( $event_id ) . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
        $ics_file = wp_tempnam( 'vpg-event.ics' );
        if ( $ics_file ) file_put_contents( $ics_file, $ics );
    }

    foreach ( vpg_event_rsvps( $event_id ) as $uid ) {
        $user = get_userdata( $uid );
        if ( ! $user ) continue;
        wp_mail( $user->user_email,
            sprintf( __( '[VPG] Tomorrow · %s', 'vpg-v2' ), $event->post_title ),
            sprintf(
                /* translators: 1: name, 2: event title, 3: venue, 4: permalink */
                __( "Hello %1\$s,\n\nQuick reminder — \"%2\$s\" is tomorrow%3\$s.%5\$s\n\nDetails: %4\$s\n\nBring a camera.\n\n— Vienna Photo Group", 'vpg-v2' ),
                $user->display_name,
                $event->post_title,
                $venue ? ' · ' . $venue : '',
                get_permalink( $event_id ),
                $wx_line
            ),
            [],
            $ics_file ? [ $ics_file ] : []
        );
    }
    if ( $ics_file ) @unlink( $ics_file );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Monthly digest · first day of the month, 08:00 site time         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_monthly_digest' ) ) {
        wp_schedule_single_event( strtotime( 'first day of next month 08:00' ), 'vpg_monthly_digest' );
    }
} );

add_action( 'vpg_monthly_digest', function () {
    // Re-arm for the following month first · a failed send never kills the loop
    wp_schedule_single_event( strtotime( 'first day of next month 08:00' ), 'vpg_monthly_digest' );

    $last  = strtotime( 'first day of last month' );
    $year  = (int) gmdate( 'Y', $last );
    $month = (int) gmdate( 'n', $last );
    $label = date_i18n( 'F Y', $last );

    $issue     = get_posts( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 1, 'post_status' => 'publish', 'date_query' => [ [ 'year' => $year, 'month' => $month ] ] ] );
    $locations = get_posts( [ 'post_type' => 'vpg_location', 'posts_per_page' => 5, 'post_status' => 'publish', 'date_query' => [ [ 'year' => $year, 'month' => $month ] ] ] );
    $upcoming  = get_posts( [ 'post_type' => 'vpg_event', 'posts_per_page' => 5, 'post_status' => 'publish', 'meta_key' => '_vpg_event_date', 'orderby' => 'meta_value', 'order' => 'ASC', 'meta_query' => [ [ 'key' => '_vpg_event_date', 'value' => gmdate( 'Y-m-d' ), 'compare' => '>=' ] ] ] );

    $body = sprintf( __( "The month at Vienna Photo Group — %s.\n\n", 'vpg-v2' ), $label );
    if ( $issue ) {
        $body .= __( "New issue:\n", 'vpg-v2' ) . '  ' . $issue[0]->post_title . ' — ' . get_permalink( $issue[0] ) . "\n\n";
    }
    if ( $locations ) {
        $body .= __( "New on the map:\n", 'vpg-v2' );
        foreach ( $locations as $l ) $body .= '  · ' . $l->post_title . ' — ' . get_permalink( $l ) . "\n";
        $body .= "\n";
    }
    if ( $upcoming ) {
        $body .= __( "Coming up:\n", 'vpg-v2' );
        foreach ( $upcoming as $e ) {
            $body .= '  · ' . ( get_post_meta( $e->ID, '_vpg_event_date', true ) ?: '' ) . ' ' . $e->post_title . ' — ' . get_permalink( $e ) . "\n";
        }
        $body .= "\n";
    }
    if ( ! $issue && ! $locations && ! $upcoming ) return; // nothing worth a mail

    $body .= __( "— Vienna Photo Group · member-run, ad-free\nManage emails: ", 'vpg-v2' ) . home_url( '/dashboard/#profile' ) . "\n";

    $members = get_users( [ 'role__in' => [ 'vpg_member', 'administrator', 'editor', 'author' ], 'fields' => [ 'ID', 'user_email', 'display_name' ] ] );
    foreach ( $members as $m ) {
        if ( get_user_meta( $m->ID, '_vpg_pref_digest', true ) === '0' ) continue;
        if ( ! vpg_is_verified( $m->ID ) ) continue;
        wp_mail( $m->user_email, sprintf( __( '[VPG] The month — %s', 'vpg-v2' ), $label ), "Hello {$m->display_name},\n\n" . $body );
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Photo of the week · one vote per member per ISO week             */
/* ════════════════════════════════════════════════════════════════ */
function vpg_potw_week_key() {
    return 'vpg_potw_' . current_time( 'o-\WW' ); // e.g. vpg_potw_2026-W35
}

function vpg_potw_candidates( $limit = 8 ) {
    return get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
}

add_action( 'wp_ajax_vpg_potw_vote', function () {
    check_ajax_referer( 'vpg_potw' );
    $uid = get_current_user_id();
    if ( ! $uid ) wp_send_json_error( 'login', 403 );

    $photo = (int) ( $_POST['photo'] ?? 0 );
    if ( ! $photo || ! wp_attachment_is_image( $photo ) ) wp_send_json_error( 'bad photo' );

    $week = vpg_potw_week_key();
    if ( get_user_meta( $uid, '_' . $week, true ) ) wp_send_json_error( 'already voted' );

    $votes = get_option( $week, [] );
    $votes = is_array( $votes ) ? $votes : [];
    $votes[ $photo ] = (int) ( $votes[ $photo ] ?? 0 ) + 1;
    update_option( $week, $votes, false );
    update_user_meta( $uid, '_' . $week, $photo );

    wp_send_json_success( [ 'votes' => $votes[ $photo ] ] );
} );

function vpg_potw_leader() {
    $votes = get_option( vpg_potw_week_key(), [] );
    if ( ! is_array( $votes ) || ! $votes ) return 0;
    arsort( $votes );
    return (int) array_key_first( $votes );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Photowalk trails · ordered location stops on vpg_trail           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-trail-stops', __( 'Trail stops', 'vpg-v2' ), function ( $post ) {
        $stops = get_post_meta( $post->ID, '_vpg_trail_stops', true );
        wp_nonce_field( 'vpg_trail_stops', 'vpg_trail_stops_nonce' );
        $places = get_posts( [ 'post_type' => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ] );
        ?>
        <p style="margin-bottom:6px"><label><?php esc_html_e( 'Difficulty', 'vpg-v2' ); ?>
            <select name="vpg_trail_difficulty">
                <?php foreach ( [ 'easy' => __( 'Easy', 'vpg-v2' ), 'moderate' => __( 'Moderate', 'vpg-v2' ), 'sporty' => __( 'Sporty', 'vpg-v2' ) ] as $dv => $dl ) : ?>
                    <option value="<?php echo esc_attr( $dv ); ?>" <?php selected( get_post_meta( $post->ID, '_vpg_trail_difficulty', true ), $dv ); ?>><?php echo esc_html( $dl ); ?></option>
                <?php endforeach; ?>
            </select></label></p>
        <p class="description"><?php esc_html_e( 'Comma-separated location IDs, in walking order. The list below shows every pinned place.', 'vpg-v2' ); ?></p>
        <input type="text" name="vpg_trail_stops" value="<?php echo esc_attr( $stops ); ?>" style="width:100%" placeholder="12, 87, 43">
        <div style="max-height:180px;overflow-y:auto;margin-top:8px;font-size:12px;color:#646970">
            <?php foreach ( $places as $pl ) : ?>
                <div><code><?php echo (int) $pl->ID; ?></code> <?php echo esc_html( $pl->post_title ); ?></div>
            <?php endforeach; ?>
        </div>
        <?php
    }, 'vpg_trail', 'normal', 'high' );
} );

add_action( 'save_post_vpg_trail', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_trail_stops_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_trail_stops_nonce'], 'vpg_trail_stops' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $raw = sanitize_text_field( wp_unslash( $_POST['vpg_trail_stops'] ?? '' ) );
    $ids = implode( ',', array_filter( array_map( 'intval', explode( ',', $raw ) ) ) );
    update_post_meta( $post_id, '_vpg_trail_stops', $ids );
    $diff = sanitize_key( $_POST['vpg_trail_difficulty'] ?? '' );
    if ( in_array( $diff, [ 'easy', 'moderate', 'sporty' ], true ) ) {
        update_post_meta( $post_id, '_vpg_trail_difficulty', $diff );
    }
} );

function vpg_trail_stops( $trail_id ) {
    $raw = get_post_meta( $trail_id, '_vpg_trail_stops', true );
    $out = [];
    foreach ( array_filter( array_map( 'intval', explode( ',', (string) $raw ) ) ) as $pid ) {
        $post = get_post( $pid );
        if ( ! $post || $post->post_status !== 'publish' ) continue;
        $coords = function_exists( 'vpg_get_coords' ) ? vpg_get_coords( $pid ) : null;
        $out[] = [ 'post' => $post, 'coords' => $coords ];
    }
    return $out;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Competitions · photo entries, editors pick the winner            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_competition_enter', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_competition_enter' );
    if ( function_exists( 'vpg_is_verified' ) && ! vpg_is_verified() ) {
        wp_safe_redirect( add_query_arg( 'vpg_status', 'verify', wp_get_referer() ?: home_url() ) );
        exit;
    }

    $comp_id = (int) ( $_POST['competition'] ?? 0 );
    $comp    = get_post( $comp_id );
    if ( ! $comp || $comp->post_type !== 'vpg_competition' || $comp->post_status !== 'publish' ) {
        wp_die( 'Competition not found', 404 );
    }
    if ( get_post_meta( $comp_id, '_vpg_comp_closed', true ) === '1' ) {
        wp_safe_redirect( get_permalink( $comp_id ) );
        exit;
    }

    if ( empty( $_FILES['entry']['name'] ) || (int) $_FILES['entry']['error'] !== UPLOAD_ERR_OK ) {
        wp_safe_redirect( add_query_arg( 'vpg_status', 'invalid', get_permalink( $comp_id ) ) );
        exit;
    }
    if ( (int) $_FILES['entry']['size'] > 8 * MB_IN_BYTES ) {
        wp_safe_redirect( add_query_arg( 'vpg_status', 'invalid', get_permalink( $comp_id ) ) );
        exit;
    }
    $check = wp_check_filetype_and_ext( $_FILES['entry']['tmp_name'], sanitize_file_name( $_FILES['entry']['name'] ) );
    if ( empty( $check['ext'] ) || ! in_array( strtolower( $check['ext'] ), [ 'jpg', 'jpeg', 'png', 'webp' ], true ) ) {
        wp_safe_redirect( add_query_arg( 'vpg_status', 'invalid', get_permalink( $comp_id ) ) );
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $att = media_handle_upload( 'entry', $comp_id );
    if ( is_wp_error( $att ) ) {
        wp_safe_redirect( add_query_arg( 'vpg_status', 'fail', get_permalink( $comp_id ) ) );
        exit;
    }
    update_post_meta( $att, '_vpg_competition', $comp_id );

    wp_safe_redirect( add_query_arg( 'vpg_status', 'ok', get_permalink( $comp_id ) ) . '#entries' );
    exit;
} );

function vpg_competition_entries( $comp_id ) {
    return get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 60,
        'meta_key'       => '_vpg_competition',
        'meta_value'     => (int) $comp_id,
        'orderby'        => 'date',
        'order'          => 'ASC',
    ] );
}

/* Winner picker · editors see a "make winner" link under each entry */
add_action( 'admin_post_vpg_competition_winner', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_competition_winner' );
    $comp  = (int) ( $_GET['competition'] ?? 0 );
    $entry = (int) ( $_GET['entry'] ?? 0 );
    if ( $comp && $entry && (int) get_post_meta( $entry, '_vpg_competition', true ) === $comp ) {
        update_post_meta( $comp, '_vpg_comp_winner', $entry );
        update_post_meta( $comp, '_vpg_comp_closed', '1' );
        $author = get_post_field( 'post_author', $entry );
        if ( $author ) {
            vpg_notify_user( $author, __( 'Your photo won a competition!', 'vpg-v2' ), get_permalink( $comp ) );
        }
    }
    wp_safe_redirect( get_permalink( $comp ) );
    exit;
} );

/* 0457 · The jury owes two sentences — why this picture won */
add_action( 'admin_post_vpg_winner_reason', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_winner_reason' );
    $comp = get_post( (int) ( $_POST['competition'] ?? 0 ) );
    if ( $comp && $comp->post_type === 'vpg_competition' ) {
        update_post_meta( $comp->ID, '_vpg_comp_reason', sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) ) );
    }
    wp_safe_redirect( $comp ? get_permalink( $comp ) : home_url() );
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Comments as feedback threads · members-only on member CPTs       */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    foreach ( [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ] as $cpt ) {
        add_post_type_support( $cpt, 'comments' );
    }
}, 20 );

/* Only members write feedback · guests see the gate in the template */
add_filter( 'comments_open', function ( $open, $post_id ) {
    $type = get_post_type( $post_id );
    if ( in_array( $type, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ], true ) ) {
        return is_user_logged_in();
    }
    return $open;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Moderation · members report a note, editors review in one queue  */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_report_comment', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_report_comment' );
    $cid     = (int) ( $_GET['comment'] ?? 0 );
    $comment = $cid ? get_comment( $cid ) : null;
    if ( $comment ) {
        $reporters = get_comment_meta( $cid, '_vpg_reports', true );
        $reporters = is_array( $reporters ) ? $reporters : [];
        $uid       = get_current_user_id();
        if ( ! in_array( $uid, $reporters, true ) ) {
            $reporters[] = $uid;
            update_comment_meta( $cid, '_vpg_reports', $reporters );
        }
    }
    wp_safe_redirect( ( wp_get_referer() ?: home_url() ) . '#comment-' . $cid );
    exit;
} );

add_action( 'admin_menu', function () {
    $reported = get_comments( [ 'meta_key' => '_vpg_reports', 'count' => true ] );
    $badge    = $reported ? ' <span class="awaiting-mod"><span class="pending-count">' . (int) $reported . '</span></span>' : '';
    add_submenu_page( 'edit.php?post_type=vpg_event', __( 'Reported notes', 'vpg-v2' ), __( '⚑ Reports', 'vpg-v2' ) . $badge, 'moderate_comments', 'vpg-reports', function () {
        if ( ! current_user_can( 'moderate_comments' ) ) wp_die( 'Forbidden' );

        if ( ! empty( $_GET['vpg_act'] ) && ! empty( $_GET['comment'] ) && check_admin_referer( 'vpg_report_action' ) ) {
            $cid = (int) $_GET['comment'];
            if ( $_GET['vpg_act'] === 'hide' )    wp_set_comment_status( $cid, 'hold' );
            if ( $_GET['vpg_act'] === 'dismiss' ) delete_comment_meta( $cid, '_vpg_reports' );
            if ( $_GET['vpg_act'] === 'trash' )   wp_trash_comment( $cid );
            wp_safe_redirect( admin_url( 'edit.php?post_type=vpg_event&page=vpg-reports' ) );
            exit;
        }

        $reported = get_comments( [ 'meta_key' => '_vpg_reports' ] );
        ?>
        <div class="wrap">
            <h1>⚑ <?php esc_html_e( 'Reported notes', 'vpg-v2' ); ?></h1>
            <?php if ( ! $reported ) : ?>
                <p><?php esc_html_e( 'Nothing reported. The wall is civil.', 'vpg-v2' ); ?></p>
            <?php else : ?>
            <table class="widefat striped" style="margin-top:1rem">
                <thead><tr><th style="width:180px"><?php esc_html_e( 'Author', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Note', 'vpg-v2' ); ?></th><th style="width:90px"><?php esc_html_e( 'Reports', 'vpg-v2' ); ?></th><th style="width:260px"><?php esc_html_e( 'Actions', 'vpg-v2' ); ?></th></tr></thead>
                <tbody>
                <?php foreach ( $reported as $c ) :
                    $n = count( (array) get_comment_meta( $c->comment_ID, '_vpg_reports', true ) );
                    $mk = function ( $act ) use ( $c ) {
                        return wp_nonce_url( admin_url( 'edit.php?post_type=vpg_event&page=vpg-reports&vpg_act=' . $act . '&comment=' . $c->comment_ID ), 'vpg_report_action' );
                    };
                ?>
                    <tr>
                        <td><?php echo esc_html( $c->comment_author ); ?></td>
                        <td><a href="<?php echo esc_url( get_comment_link( $c ) ); ?>" target="_blank"><?php echo esc_html( wp_trim_words( $c->comment_content, 24 ) ); ?></a></td>
                        <td><?php echo (int) $n; ?></td>
                        <td>
                            <a class="button button-small" href="<?php echo esc_url( $mk( 'dismiss' ) ); ?>"><?php esc_html_e( 'Dismiss', 'vpg-v2' ); ?></a>
                            <a class="button button-small" href="<?php echo esc_url( $mk( 'hide' ) ); ?>"><?php esc_html_e( 'Unapprove', 'vpg-v2' ); ?></a>
                            <a class="button button-small button-link-delete" href="<?php echo esc_url( $mk( 'trash' ) ); ?>"><?php esc_html_e( 'Trash', 'vpg-v2' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    } );
}, 17 );

/* ════════════════════════════════════════════════════════════════ */
/*  0370 · Thanks — one quiet thank-you per member per piece         */
/*  No likes, no counters on the page: the author gets a             */
/*  notification, the sender gets a toast, nobody gets a scoreboard. */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || ! is_user_logged_in() ) return $content;
    $types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [];
    $post  = get_post();
    if ( ! $post || ! in_array( $post->post_type, $types, true ) ) return $content;
    if ( (int) $post->post_author === get_current_user_id() ) return $content;

    $thanked = in_array( get_current_user_id(), array_map( 'intval', (array) get_post_meta( $post->ID, '_vpg_thanks', true ) ), true );
    ob_start(); ?>
    <div style="margin-top:32px;padding-top:18px;border-top:1px solid var(--g-line,#E6E5E1)">
        <?php if ( $thanked ) : ?>
            <span style="font-size:13px;font-weight:700;color:var(--g-mid,#6A6A6A)">☺ <?php esc_html_e( 'You thanked the author for this.', 'vpg-v2' ); ?></span>
        <?php else : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                <?php wp_nonce_field( 'vpg_thanks' ); ?>
                <input type="hidden" name="action" value="vpg_thanks">
                <input type="hidden" name="post" value="<?php echo (int) $post->ID; ?>">
                <button type="submit" style="background:none;border:1px solid var(--g-line-2,#D9D7D2);padding:8px 16px;cursor:pointer;font:inherit;font-size:13px;font-weight:700">☺ <?php esc_html_e( 'Say thanks — quietly, to the author', 'vpg-v2' ); ?></button>
            </form>
        <?php endif; ?>
    </div>
    <?php
    return $content . ob_get_clean();
}, 30 );

add_action( 'admin_post_vpg_thanks', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_thanks' );
    $post = get_post( (int) ( $_POST['post'] ?? 0 ) );
    $uid  = get_current_user_id();
    if ( $post && $post->post_status === 'publish' && (int) $post->post_author !== $uid ) {
        $list = array_map( 'intval', (array) get_post_meta( $post->ID, '_vpg_thanks', true ) );
        if ( ! in_array( $uid, $list, true ) ) {
            $list[] = $uid;
            update_post_meta( $post->ID, '_vpg_thanks', $list );
            $sender = wp_get_current_user();
            vpg_notify_user( $post->post_author,
                sprintf( __( '%1$s says thanks for “%2$s”.', 'vpg-v2' ), $sender->display_name, $post->post_title ),
                get_permalink( $post )
            );
        }
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', 'thanked', $post ? get_permalink( $post ) : home_url() ) );
    exit;
} );

add_action( 'wp_footer', function () {
    if ( sanitize_key( $_GET['vpg_status'] ?? '' ) !== 'thanked' ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-thx-toast"><?php esc_html_e( 'Thanks delivered — quietly, to the author.', 'vpg-v2' ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-thx-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0441 · Monthly challenge — a draft competition on the 1st        */
/*  Editorial fills in the theme and publishes; nothing goes live    */
/*  automatically.                                                   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_monthly_challenge' ) ) {
        wp_schedule_single_event( strtotime( 'first day of next month 07:00' ), 'vpg_monthly_challenge' );
    }
} );

add_action( 'vpg_monthly_challenge', function () {
    wp_schedule_single_event( strtotime( 'first day of next month 07:00' ), 'vpg_monthly_challenge' );

    $month_key = current_time( 'Y-m' );
    $existing  = get_posts( [ 'post_type' => 'vpg_competition', 'post_status' => 'any', 'meta_key' => '_vpg_challenge_month', 'meta_value' => $month_key, 'posts_per_page' => 1, 'fields' => 'ids' ] );
    if ( $existing ) return;

    $id = wp_insert_post( [
        'post_type'    => 'vpg_competition',
        'post_status'  => 'draft',
        'post_title'   => sprintf( __( 'Monthly challenge · %s', 'vpg-v2' ), date_i18n( 'F Y' ) ),
        'post_content' => __( "Theme goes here — one line, open enough for every genre.\n\nDeadline: last Sunday of the month, midnight.", 'vpg-v2' ),
    ] );
    if ( $id && ! is_wp_error( $id ) ) {
        update_post_meta( $id, '_vpg_challenge_month', $month_key );
        wp_mail( get_theme_mod( 'vpg_email', get_option( 'admin_email' ) ),
            '[VPG] ' . __( 'Monthly challenge draft is waiting', 'vpg-v2' ),
            __( "The new month's challenge draft is created — add the theme and publish:\n", 'vpg-v2' ) . get_edit_post_link( $id, '' )
        );
    }
} );
