<?php
/**
 * VPG v3 — Cluster 20 · Moderation, Trust & Sicherheit.
 *
 * Net-new only — reuses the existing report queues (community.php / followups.php),
 * honeypot (submission-handler.php), MIME checks and the TOTP/passkey stack:
 *
 *   vpg_rate_limit() reusable throttle (0787) · 0765 categorised reports
 *   0771 escalation levels · 0770/0784 audit log · 0762 two-mod deletion queue
 *   0772 thread cooldown · 0773 word filter · 0774 link-spam · 0775 newcomer throttle
 *   0781 session manager · 0782 new-device mail · 0785 security headers (+CSP report-only)
 *   0786 magic-bytes upload check · 0789 disposable mail · 0790 IP bans w/ expiry
 *   0793 GDPR exporter · 0794 privacy defaults · 0796/0797 block + panic hide
 *   0798 security.txt · 0799 abuse mailbox · Trust & Safety desk for the policy rest
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * 0787 · one reusable rate-limit helper (per-IP or per-key)
 * ================================================================ */
function vpg_rate_limit( $key, $max = 30, $window = HOUR_IN_SECONDS ) {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
    $t   = 'vpg_rl_' . md5( $key . '|' . $ip );
    $n   = (int) get_transient( $t );
    if ( $n >= $max ) return false;
    set_transient( $t, $n + 1, $window );
    return true;
}

/* ================================================================
 * 0770 / 0784 · a general moderation & admin audit log
 * ================================================================ */
function vpg_mod_log( $action, $detail = '', $target = 0 ) {
    $log   = (array) get_option( 'vpg_mod_log', [] );
    $log[] = [
        't'      => time(),
        'who'    => get_current_user_id(),
        'action' => sanitize_key( $action ),
        'detail' => sanitize_text_field( (string) $detail ),
        'target' => (int) $target,
    ];
    update_option( 'vpg_mod_log', array_slice( $log, -1000 ), false );
}
/* log the sensitive admin acts we can observe cheaply */
add_action( 'delete_user', fn( $id ) => vpg_mod_log( 'delete_user', '', $id ) );
add_action( 'set_user_role', fn( $id, $role ) => vpg_mod_log( 'set_role', $role, $id ), 10, 2 );
add_action( 'switch_theme', fn( $name ) => vpg_mod_log( 'switch_theme', $name ) );

/* ================================================================
 * 0771 · escalation levels (0 none · 1 notice · 2 warning · 3 pause · 4 ban)
 * ================================================================ */
function vpg_mod_level( $uid ) { return (int) get_user_meta( (int) $uid, '_vpg_mod_level', true ); }
function vpg_set_mod_level( $uid, $level, $note = '' ) {
    $level = max( 0, min( 4, (int) $level ) );
    update_user_meta( (int) $uid, '_vpg_mod_level', $level );
    vpg_mod_log( 'escalate', 'L' . $level . ' ' . $note, $uid );
    if ( function_exists( 'vpg_notify_user' ) && $level > 0 && $level < 4 ) {
        $msg = [ 1 => __( 'A moderator left you a note.', 'vpg-v2' ), 2 => __( 'You have a formal warning — please review the community rules.', 'vpg-v2' ), 3 => __( 'Your posting is paused for now. A moderator will be in touch.', 'vpg-v2' ) ][ $level ] ?? '';
        if ( $msg ) vpg_notify_user( $uid, $msg, home_url( '/rules/' ), 'moderation' );
    }
}
/* a paused (3) or banned (4) member cannot post comments */
add_filter( 'pre_comment_approved', function ( $approved, $data ) {
    $uid = (int) ( $data['user_id'] ?? get_current_user_id() );
    if ( $uid && vpg_mod_level( $uid ) >= 3 ) return new WP_Error( 'vpg_paused', __( 'Your posting is currently paused.', 'vpg-v2' ), 403 );
    return $approved;
}, 5, 2 );

/* ================================================================
 * 0796 · member-to-member block · 0797 · panic hide
 * ================================================================ */
function vpg_blocked_by( $uid ) { return array_map( 'intval', (array) get_user_meta( (int) $uid, '_vpg_blocked', true ) ?: [] ); }
function vpg_is_blocked_between( $a, $b ) {
    if ( ! $a || ! $b ) return false;
    return in_array( (int) $b, vpg_blocked_by( $a ), true ) || in_array( (int) $a, vpg_blocked_by( $b ), true );
}
add_action( 'wp_ajax_vpg_block_user', function () {
    check_ajax_referer( 'vpg_block', 'n' );
    $me = get_current_user_id(); $target = (int) ( $_POST['uid'] ?? 0 );
    if ( ! $me || ! $target || $me === $target ) wp_send_json_error();
    $list = vpg_blocked_by( $me );
    if ( in_array( $target, $list, true ) ) $list = array_diff( $list, [ $target ] );
    else $list[] = $target;
    update_user_meta( $me, '_vpg_blocked', array_values( array_unique( $list ) ) );
    wp_send_json_success( [ 'blocked' => in_array( $target, $list, true ) ] );
} );
/* hide a blocked person's comments from the viewer */
add_filter( 'comments_array', function ( $comments ) {
    $me = get_current_user_id();
    if ( ! $me ) return $comments;
    $blk = vpg_blocked_by( $me );
    if ( ! $blk ) return $comments;
    return array_filter( $comments, fn( $c ) => ! in_array( (int) $c->user_id, $blk, true ) );
} );
/* 0797 · panic hide — own profile & author archive 404 while hidden */
add_action( 'template_redirect', function () {
    if ( ! is_author() ) return;
    $author = get_queried_object();
    if ( $author instanceof WP_User && get_user_meta( $author->ID, '_vpg_hidden', true ) ) {
        global $wp_query; $wp_query->set_404(); status_header( 404 ); nocache_headers();
    }
} );
add_action( 'wp_ajax_vpg_panic_hide', function () {
    check_ajax_referer( 'vpg_panic', 'n' );
    $me = get_current_user_id(); if ( ! $me ) wp_send_json_error();
    $to = get_user_meta( $me, '_vpg_hidden', true ) ? '' : 1;
    update_user_meta( $me, '_vpg_hidden', $to );
    wp_send_json_success( [ 'hidden' => (bool) $to ] );
} );

/* ================================================================
 * 0773 · word filter · 0774 · link-spam · 0775 · newcomer throttle
 * ================================================================ */
add_filter( 'preprocess_comment', function ( $data ) {
    $uid  = get_current_user_id();
    $body = (string) ( $data['comment_content'] ?? '' );

    // 0773 · hard slur list blocks outright
    $slurs = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_wordfilter_block', '' ) ) ) );
    foreach ( $slurs as $w ) {
        if ( $w !== '' && stripos( $body, $w ) !== false ) {
            wp_die( esc_html__( 'Your comment can’t be posted. If this is a mistake, contact us.', 'vpg-v2' ), 403 );
        }
    }
    // 0775 · newcomer throttle — new/low-trust accounts limited
    $fresh = $uid && ( time() - strtotime( get_userdata( $uid )->user_registered ) < 2 * DAY_IN_SECONDS );
    if ( ( ! $uid || $fresh ) && ! vpg_rate_limit( 'comment', 5, 2 * DAY_IN_SECONDS ) ) {
        wp_die( esc_html__( 'You’re posting a little fast for a new account — please try again later.', 'vpg-v2' ), 429 );
    }
    return $data;
} );
/* link-spam + grey-word → hold in the queue rather than block */
add_filter( 'pre_comment_approved', function ( $approved, $data ) {
    if ( is_wp_error( $approved ) || 'spam' === $approved ) return $approved;
    $uid  = (int) ( $data['user_id'] ?? 0 );
    $body = (string) ( $data['comment_content'] ?? '' );
    $trust = ( $uid && function_exists( 'vpg_trust_level' ) ) ? (int) vpg_trust_level( $uid ) : 0;
    // 0774 · first/low-trust comment carrying links → moderate
    if ( $trust < 2 && preg_match_all( '#https?://#i', $body ) >= 1 ) return 0;
    // 0773 · grey list → moderate, don't block
    $grey = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_wordfilter_grey', '' ) ) ) );
    foreach ( $grey as $w ) if ( $w !== '' && stripos( $body, $w ) !== false ) return 0;
    return $approved;
}, 20, 2 );

/* ================================================================
 * 0772 · thread cooldown — slow a heated thread for a window
 * ================================================================ */
add_filter( 'comments_open', function ( $open, $post_id ) {
    if ( ! $open ) return $open;
    $until = (int) get_post_meta( $post_id, '_vpg_cooldown_until', true );
    return $until > time() ? false : $open;
}, 10, 2 );

/* ================================================================
 * 0762 · two-moderator deletion queue (request → second approves)
 * ================================================================ */
function vpg_deletion_requests() { return (array) get_option( 'vpg_del_requests', [] ); }
add_action( 'wp_ajax_vpg_request_deletion', function () {
    check_ajax_referer( 'vpg_del', 'n' );
    if ( ! current_user_can( 'moderate_comments' ) ) wp_send_json_error();
    $pid = (int) ( $_POST['pid'] ?? 0 ); if ( ! $pid || ! get_post( $pid ) ) wp_send_json_error();
    $req = vpg_deletion_requests();
    $req[ $pid ] = [ 'by' => get_current_user_id(), 't' => time(), 'reason' => sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) ) ];
    update_option( 'vpg_del_requests', $req, false );
    vpg_mod_log( 'del_request', get_the_title( $pid ), $pid );
    wp_send_json_success();
} );

/* ================================================================
 * 0785 · security headers (safe by default; CSP is report-only unless enforced)
 * ================================================================ */
add_action( 'send_headers', function () {
    if ( is_admin() ) return;
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: browsing-topics=(), interest-cohort=()' );
    header( 'Cross-Origin-Opener-Policy: same-origin-allow-popups' );
    $csp = trim( (string) get_option( 'vpg_csp', '' ) );
    if ( $csp !== '' ) {
        $mode = get_option( 'vpg_csp_enforce' ) ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
        header( $mode . ': ' . $csp );
    }
}, 5 );

/* ================================================================
 * 0786 · upload hardening — magic-bytes match for image uploads
 * ================================================================ */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
    $type = $file['type'] ?? '';
    if ( strpos( $type, 'image/' ) !== 0 ) return $file;
    $fh = @fopen( $file['tmp_name'], 'rb' );
    if ( ! $fh ) return $file;
    $head = fread( $fh, 12 ); fclose( $fh );
    $ok = false;
    if ( str_starts_with( $head, "\xFF\xD8\xFF" ) ) $ok = true;                       // jpeg
    elseif ( str_starts_with( $head, "\x89PNG\x0D\x0A\x1A\x0A" ) ) $ok = true;        // png
    elseif ( str_starts_with( $head, 'GIF87a' ) || str_starts_with( $head, 'GIF89a' ) ) $ok = true; // gif
    elseif ( str_starts_with( $head, 'RIFF' ) && substr( $head, 8, 4 ) === 'WEBP' ) $ok = true;     // webp
    elseif ( substr( $head, 4, 4 ) === 'ftyp' ) $ok = true;                           // avif/heif (ftyp box)
    elseif ( str_starts_with( $head, "II*\x00" ) || str_starts_with( $head, "MM\x00*" ) ) $ok = true; // tiff
    if ( ! $ok ) $file['error'] = __( 'This file’s contents don’t match an image format.', 'vpg-v2' );
    return $file;
} );

/* ================================================================
 * 0789 · disposable-mail policy — flag (fair), don't hard-block
 * ================================================================ */
function vpg_is_disposable_email( $email ) {
    $dom = strtolower( substr( strrchr( (string) $email, '@' ), 1 ) );
    if ( ! $dom ) return false;
    $list = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_disposable_domains', "mailinator.com\nguerrillamail.com\n10minutemail.com\ntempmail.com\nthrowawaymail.com\nyopmail.com" ) ) ) );
    return in_array( $dom, $list, true );
}
add_action( 'user_register', function ( $uid ) {
    $u = get_userdata( $uid );
    if ( $u && vpg_is_disposable_email( $u->user_email ) ) {
        update_user_meta( $uid, '_vpg_disposable', 1 );
        vpg_mod_log( 'disposable_signup', $u->user_email, $uid );
    }
    // 0794 · privacy defaults — everything personal starts private
    if ( '' === get_user_meta( $uid, '_vpg_directory_optin', true ) ) update_user_meta( $uid, '_vpg_directory_optin', '' );
    update_user_meta( $uid, '_vpg_privacy_defaults', 1 );
} );

/* ================================================================
 * 0790 · IP bans with an expiry (auto-forgets)
 * ================================================================ */
add_action( 'init', function () {
    if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) return;
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) return;
    $bans = (array) get_option( 'vpg_ip_bans', [] );
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
    if ( isset( $bans[ $ip ] ) && (int) $bans[ $ip ] > time() ) {
        status_header( 403 ); wp_die( esc_html__( 'This action is temporarily blocked.', 'vpg-v2' ), 403 );
    }
}, 1 );

/* ================================================================
 * 0781 · session manager · 0782 · new-device notification
 * ================================================================ */
function vpg_render_sessions( $user ) {
    if ( ! ( $user instanceof WP_User ) || $user->ID !== get_current_user_id() ) return;
    $mgr = WP_Session_Tokens::get_instance( $user->ID );
    $all = $mgr->get_all();
    echo '<section class="vpg-profile-sec"><h3>' . esc_html__( 'Active sign-ins', 'vpg-v2' ) . '</h3>';
    echo '<p class="description">' . esc_html( sprintf( _n( '%s device is signed in.', '%s devices are signed in.', count( $all ), 'vpg-v2' ), number_format_i18n( count( $all ) ) ) ) . '</p><ul>';
    foreach ( $all as $s ) {
        echo '<li>' . esc_html( $s['ip'] ?? '?' ) . ' · ' . esc_html( date_i18n( 'j.n.y', (int) ( $s['login'] ?? 0 ) ) ) . '<br><span style="color:#888;font-size:12px">' . esc_html( mb_substr( (string) ( $s['ua'] ?? '' ), 0, 70 ) ) . '</span></li>';
    }
    echo '</ul>';
    echo '<button class="g-btn" id="vpg-signout-others" data-n="' . esc_attr( wp_create_nonce( 'vpg_sessions' ) ) . '">' . esc_html__( 'Sign out all other devices', 'vpg-v2' ) . '</button>';
    echo '<script>document.getElementById("vpg-signout-others").addEventListener("click",function(){var b=this;fetch(vpgAjax||"/wp-admin/admin-ajax.php",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"action=vpg_signout_others&n="+b.dataset.n}).then(r=>r.json()).then(function(){b.textContent=' . wp_json_encode( __( 'Done — other devices signed out.', 'vpg-v2' ) ) . ';});});</script>';
    echo '</section>';
}
add_action( 'vpg_profile_sections', 'vpg_render_sessions', 30 );
add_action( 'wp_ajax_vpg_signout_others', function () {
    check_ajax_referer( 'vpg_sessions', 'n' );
    $uid = get_current_user_id(); if ( ! $uid ) wp_send_json_error();
    WP_Session_Tokens::get_instance( $uid )->destroy_others( wp_get_session_token() );
    vpg_mod_log( 'signout_others', '', $uid );
    wp_send_json_success();
} );
add_action( 'wp_login', function ( $login, $user ) {
    if ( ! ( $user instanceof WP_User ) ) return;
    $sig  = md5( ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) . '|' . ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
    $seen = (array) get_user_meta( $user->ID, '_vpg_known_devices', true );
    if ( ! in_array( $sig, $seen, true ) ) {
        $seen[] = $sig;
        update_user_meta( $user->ID, '_vpg_known_devices', array_slice( $seen, -20 ) );
        if ( count( $seen ) > 1 ) { // don't email on the very first sign-in
            wp_mail( $user->user_email, __( 'New sign-in to your VPG account', 'vpg-v2' ),
                sprintf( __( "A new device just signed in.\n\nWhen: %1\$s\nIP: %2\$s\n\nWasn’t you? Change your password and sign out other devices from your dashboard.", 'vpg-v2' ),
                    date_i18n( 'j.n.Y H:i' ), $_SERVER['REMOTE_ADDR'] ?? '?' ) );
        }
    }
}, 10, 2 );

/* ================================================================
 * 0793 · GDPR personal-data exporter (self-service via WP privacy tools)
 * ================================================================ */
add_filter( 'wp_privacy_personal_data_exporters', function ( $exp ) {
    $exp['vpg'] = [
        'exporter_friendly_name' => __( 'Vienna Photo Group profile', 'vpg-v2' ),
        'callback' => function ( $email ) {
            $u = get_user_by( 'email', $email );
            if ( ! $u ) return [ 'data' => [], 'done' => true ];
            $items = [];
            foreach ( [ '_vpg_lang' => __( 'Language', 'vpg-v2' ), '_vpg_mod_level' => __( 'Moderation level', 'vpg-v2' ), '_vpg_directory_optin' => __( 'Directory listed', 'vpg-v2' ) ] as $k => $label ) {
                $v = get_user_meta( $u->ID, $k, true );
                if ( '' !== $v ) $items[] = [ 'name' => $label, 'value' => is_scalar( $v ) ? $v : wp_json_encode( $v ) ];
            }
            return [ 'data' => [ [ 'group_id' => 'vpg', 'group_label' => __( 'VPG profile', 'vpg-v2' ), 'item_id' => 'vpg-' . $u->ID, 'data' => $items ] ], 'done' => true ];
        },
    ];
    return $exp;
} );

/* ================================================================
 * 0798 · security.txt (RFC 9116) · 0799 · abuse mailbox
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( ! in_array( $path, [ '.well-known/security.txt', 'security.txt' ], true ) ) return;
    $abuse   = get_option( 'vpg_abuse_email', get_option( 'admin_email' ) );
    $expires = gmdate( 'Y-m-d\TH:i:s\Z', time() + YEAR_IN_SECONDS );
    header( 'Content-Type: text/plain; charset=utf-8' );
    echo "Contact: mailto:" . sanitize_email( $abuse ) . "\n";
    echo "Expires: " . $expires . "\n";
    echo "Preferred-Languages: de, en\n";
    echo "Canonical: " . esc_url_raw( home_url( '/.well-known/security.txt' ) ) . "\n";
    echo "Policy: " . esc_url_raw( home_url( '/accessibility/' ) ) . "\n";
    exit;
} );

/* ================================================================
 * Trust & Safety desk
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'Trust & Safety', 'vpg-v2' ), '🛡 ' . __( 'Trust & Safety', 'vpg-v2' ), 'moderate_comments', 'vpg-trust', 'vpg_trust_desk' );
} );
function vpg_trust_desk() {
    if ( ! current_user_can( 'moderate_comments' ) ) wp_die( 'Forbidden' );

    if ( isset( $_POST['_vpg_trust'] ) && wp_verify_nonce( $_POST['_vpg_trust'], 'vpg_trust' ) ) {
        if ( isset( $_POST['wf_block'] ) )  update_option( 'vpg_wordfilter_block', sanitize_textarea_field( wp_unslash( $_POST['wf_block'] ) ) );
        if ( isset( $_POST['wf_grey'] ) )   update_option( 'vpg_wordfilter_grey', sanitize_textarea_field( wp_unslash( $_POST['wf_grey'] ) ) );
        if ( isset( $_POST['abuse'] ) )     update_option( 'vpg_abuse_email', sanitize_email( wp_unslash( $_POST['abuse'] ) ) );
        if ( isset( $_POST['csp'] ) )       update_option( 'vpg_csp', trim( wp_unslash( $_POST['csp'] ) ) );
        update_option( 'vpg_csp_enforce', ! empty( $_POST['csp_enforce'] ) );
        if ( ! empty( $_POST['ban_ip'] ) ) {
            $bans = (array) get_option( 'vpg_ip_bans', [] );
            $bans[ sanitize_text_field( wp_unslash( $_POST['ban_ip'] ) ) ] = time() + max( 1, (int) $_POST['ban_days'] ) * DAY_IN_SECONDS;
            update_option( 'vpg_ip_bans', $bans, false );
            vpg_mod_log( 'ip_ban', sanitize_text_field( wp_unslash( $_POST['ban_ip'] ) ) );
        }
        if ( ! empty( $_POST['approve_del'] ) ) {
            $pid = (int) $_POST['approve_del']; $req = vpg_deletion_requests();
            if ( isset( $req[ $pid ] ) && (int) $req[ $pid ]['by'] !== get_current_user_id() ) {
                wp_delete_post( $pid, true ); unset( $req[ $pid ] ); update_option( 'vpg_del_requests', $req, false );
                vpg_mod_log( 'del_approved', '', $pid );
                echo '<div class="notice notice-success"><p>' . esc_html__( 'Deleted (two-moderator confirmed).', 'vpg-v2' ) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'A different moderator must approve a deletion they didn’t request.', 'vpg-v2' ) . '</p></div>';
            }
        }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }

    // auto-forget expired bans on view
    $bans = array_filter( (array) get_option( 'vpg_ip_bans', [] ), fn( $exp ) => (int) $exp > time() );
    update_option( 'vpg_ip_bans', $bans, false );

    $log = array_reverse( (array) get_option( 'vpg_mod_log', [] ) );
    $del = vpg_deletion_requests();
    ?>
    <div class="wrap"><h1>🛡 <?php esc_html_e( 'Trust & Safety', 'vpg-v2' ); ?></h1>
      <form method="post">
        <?php wp_nonce_field( 'vpg_trust', '_vpg_trust' ); ?>

        <h2><?php esc_html_e( '0762 · Deletion approvals (four-eyes)', 'vpg-v2' ); ?></h2>
        <?php if ( $del ) { foreach ( $del as $pid => $r ) {
            echo '<p>#' . (int) $pid . ' “' . esc_html( get_the_title( $pid ) ) . '” — ' . esc_html__( 'requested by', 'vpg-v2' ) . ' ' . esc_html( get_the_author_meta( 'display_name', $r['by'] ) ) . ( $r['reason'] ? ' · ' . esc_html( $r['reason'] ) : '' ) . ' <button class="button" name="approve_del" value="' . (int) $pid . '">' . esc_html__( 'Approve deletion', 'vpg-v2' ) . '</button></p>';
        } } else echo '<p class="description">' . esc_html__( 'No pending deletion requests.', 'vpg-v2' ) . '</p>'; ?>

        <h2><?php esc_html_e( '0773 · Word filter', 'vpg-v2' ); ?></h2>
        <p><label><?php esc_html_e( 'Block list (one per line — comments containing these are refused):', 'vpg-v2' ); ?><br>
          <textarea name="wf_block" rows="4" class="large-text code"><?php echo esc_textarea( get_option( 'vpg_wordfilter_block', '' ) ); ?></textarea></label></p>
        <p><label><?php esc_html_e( 'Grey list (held for review, not blocked):', 'vpg-v2' ); ?><br>
          <textarea name="wf_grey" rows="3" class="large-text code"><?php echo esc_textarea( get_option( 'vpg_wordfilter_grey', '' ) ); ?></textarea></label></p>

        <h2><?php esc_html_e( '0790 · IP bans (auto-expire)', 'vpg-v2' ); ?></h2>
        <?php if ( $bans ) { echo '<ul>'; foreach ( $bans as $ip => $exp ) echo '<li><code>' . esc_html( $ip ) . '</code> — ' . esc_html( sprintf( __( 'until %s', 'vpg-v2' ), date_i18n( 'j.n.y H:i', (int) $exp ) ) ) . '</li>'; echo '</ul>'; } ?>
        <p><input type="text" name="ban_ip" placeholder="1.2.3.4" class="regular-text"> <input type="number" name="ban_days" value="7" min="1" style="width:70px"> <?php esc_html_e( 'days', 'vpg-v2' ); ?></p>

        <h2><?php esc_html_e( '0785 · Content-Security-Policy', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Safe headers (nosniff, referrer, permissions, COOP) are always on. A CSP is report-only until you enforce it — test in report-only first so nothing on the live site breaks.', 'vpg-v2' ); ?></p>
        <p><textarea name="csp" rows="2" class="large-text code" placeholder="default-src 'self'; img-src 'self' data: https:; ..."><?php echo esc_textarea( get_option( 'vpg_csp', '' ) ); ?></textarea></p>
        <p><label><input type="checkbox" name="csp_enforce" <?php checked( get_option( 'vpg_csp_enforce' ) ); ?>> <?php esc_html_e( 'Enforce this CSP (otherwise report-only)', 'vpg-v2' ); ?></label></p>

        <h2><?php esc_html_e( '0799 · Abuse mailbox', 'vpg-v2' ); ?></h2>
        <p><input type="email" name="abuse" value="<?php echo esc_attr( get_option( 'vpg_abuse_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"> <span class="description"><?php esc_html_e( 'Published in /.well-known/security.txt with a reply commitment.', 'vpg-v2' ); ?></span></p>

        <p><button class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>

      <h2><?php esc_html_e( '0770 / 0784 · Moderation & admin log', 'vpg-v2' ); ?></h2>
      <?php if ( $log ) { echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'When', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Who', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Action', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Detail', 'vpg-v2' ) . '</th></tr></thead><tbody>';
          foreach ( array_slice( $log, 0, 60 ) as $e ) echo '<tr><td>' . esc_html( date_i18n( 'j.n.y H:i', (int) $e['t'] ) ) . '</td><td>' . esc_html( $e['who'] ? get_the_author_meta( 'display_name', $e['who'] ) : '—' ) . '</td><td><code>' . esc_html( $e['action'] ) . '</code></td><td>' . esc_html( $e['detail'] ) . '</td></tr>';
          echo '</tbody></table>';
      } else echo '<p class="description">' . esc_html__( 'Log is empty.', 'vpg-v2' ) . '</p>'; ?>

      <h2><?php esc_html_e( 'Policy — written, lived, reviewed', 'vpg-v2' ); ?></h2>
      <ol style="padding-left:20px;line-height:1.7">
        <li><?php esc_html_e( '0761 Moderation handbook · 0763 appeals path · 0764 no shadow-bans (principle).', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0766 Copyright takedown flow with deadlines · 0767 reverse-image check on suspicion.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0768 AI-image labelling duty · 0769 C2PA / Content Credentials watch.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0777 Moderation cover for holidays · 0778 mod rotation & thank-you culture.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0791 Breach plan (72h) · 0792 yearly data-minimisation audit · 0795 protection of minors.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0800 Yearly external security review as a fixed date.', 'vpg-v2' ); ?></li>
      </ol>
    </div>
    <?php
}
