<?php
/**
 * VPG v3 — Cluster 21 · Mail & Benachrichtigungen.
 *
 * Builds on inc/mail.php (SMTP, from-headers, HTML shell, vpg_mail_log) and
 * vpg_notify_user()/newsletter unsub — adds only the missing layers:
 *
 *   0808 global Reply-To · 0809/0812 List-Unsubscribe (+ one-click POST)
 *   0824 plain-text twin (AltBody) · 0823 dark-mode mail styles
 *   0805/0807/0833/0806/0834 notification digest queue (frequency, coalesce,
 *        quiet hours, send-time heuristic) with a per-user email-frequency UI
 *   0838/0811 throttled outbox for mass sends + hard-bounce suppression
 *   0810 deliverability watch · 0836 unsubscribe reason · 0837 yearly re-entry
 *   0820 web-viewable digest archive · 0835 minimal aggregate open metric
 *   0802/0822/0821/0825 mail desk: preview, test-send, variable & subject docs
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * 0808 · global Reply-To to the editorial inbox (never a black hole)
 * ================================================================ */
add_filter( 'wp_mail', function ( $atts ) {
    $headers = $atts['headers'] ?? [];
    if ( is_string( $headers ) ) $headers = array_filter( array_map( 'trim', explode( "\n", $headers ) ) );
    $has_reply = false; $has_unsub = false;
    foreach ( (array) $headers as $h ) {
        if ( stripos( $h, 'reply-to:' ) === 0 ) $has_reply = true;
        if ( stripos( $h, 'list-unsubscribe:' ) === 0 ) $has_unsub = true;
    }
    if ( ! $has_reply ) {
        $reply = get_option( 'vpg_reply_to', get_option( 'admin_email' ) );
        if ( $reply ) $headers[] = 'Reply-To: ' . $reply;
    }
    // 0809 · List-Unsubscribe (mailbox + one-click POST) for a single recipient
    if ( ! $has_unsub ) {
        $to = is_array( $atts['to'] ?? '' ) ? ( $atts['to'][0] ?? '' ) : ( $atts['to'] ?? '' );
        $to = is_string( $to ) ? trim( preg_replace( '/^.*<(.+)>.*$/', '$1', $to ) ) : '';
        if ( is_email( $to ) ) {
            $url   = vpg_mail_unsub_url( $to );
            $abuse = get_option( 'vpg_abuse_email', get_option( 'admin_email' ) );
            $headers[] = 'List-Unsubscribe: <' . $url . '>, <mailto:' . $abuse . '?subject=unsubscribe>';
            $headers[] = 'List-Unsubscribe-Post: List-Unsubscribe=One-Click';
        }
    }
    $atts['headers'] = $headers;
    return $atts;
}, 5 );

function vpg_mail_unsub_token( $email ) { return substr( hash_hmac( 'sha256', strtolower( $email ), wp_salt() ), 0, 24 ); }
function vpg_mail_unsub_url( $email ) {
    return add_query_arg( [ 'e' => rawurlencode( $email ), 't' => vpg_mail_unsub_token( $email ) ], home_url( '/mail-abmelden/' ) );
}

/* ================================================================
 * 0824 · plain-text twin · 0823 · dark-mode styles · suppression check
 * ================================================================ */
add_action( 'phpmailer_init', function ( $phpmailer ) {
    // 0824 · if the body is HTML and no AltBody was set, derive a clean text twin
    if ( $phpmailer->ContentType === 'text/html' && empty( $phpmailer->AltBody ) ) {
        $text = wp_strip_all_tags( preg_replace( '#<a[^>]+href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ($1)', $phpmailer->Body ) );
        $phpmailer->AltBody = trim( html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) );
    }
    // 0823 · inject a dark-scheme block once, so mail clients that honour it adapt
    if ( $phpmailer->ContentType === 'text/html' && strpos( $phpmailer->Body, 'vpg-darkmail' ) === false && stripos( $phpmailer->Body, '</head>' ) !== false ) {
        $css = '<style class="vpg-darkmail">@media (prefers-color-scheme:dark){body,.vpg-mail-bg{background:#0B0B0B!important}.vpg-mail-card{background:#151412!important;color:#F5F4F1!important}a{color:#E5341F!important}}</style>';
        $phpmailer->Body = str_ireplace( '</head>', $css . '</head>', $phpmailer->Body );
    }
}, 20 );

/* 0811 · a hard-bounce / complaint suppression list stops sending to dead addresses */
function vpg_mail_suppressed( $email ) {
    $s = (array) get_option( 'vpg_mail_suppress', [] );
    return isset( $s[ strtolower( $email ) ] );
}
function vpg_mail_suppress( $email, $why = 'bounce' ) {
    $s = (array) get_option( 'vpg_mail_suppress', [] );
    $s[ strtolower( $email ) ] = [ 'why' => $why, 't' => time() ];
    update_option( 'vpg_mail_suppress', $s, false );
}
add_filter( 'wp_mail', function ( $atts ) {
    $to = (array) ( $atts['to'] ?? [] );
    $to = array_filter( $to, function ( $addr ) {
        $addr = trim( preg_replace( '/^.*<(.+)>.*$/', '$1', (string) $addr ) );
        return ! ( is_email( $addr ) && vpg_mail_suppressed( $addr ) );
    } );
    $atts['to'] = array_values( $to );
    return $atts;
}, 4 );

/* ================================================================
 * 0810 · deliverability watch — count sends & failures, alarm on a bad ratio
 * ================================================================ */
add_action( 'wp_mail_succeeded', function () {
    $c = (array) get_option( 'vpg_mail_stats', [] );
    $c['sent'] = ( (int) ( $c['sent'] ?? 0 ) ) + 1;
    update_option( 'vpg_mail_stats', $c, false );
} );
add_action( 'wp_mail_failed', function ( $err ) {
    $c = (array) get_option( 'vpg_mail_stats', [] );
    $c['fail'] = ( (int) ( $c['fail'] ?? 0 ) ) + 1;
    update_option( 'vpg_mail_stats', $c, false );
    if ( function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'mail_fail', mb_substr( is_wp_error( $err ) ? $err->get_error_message() : '', 0, 120 ) );
    // 0811 · a hard bounce (address-shaped error) suppresses that recipient
    if ( is_wp_error( $err ) ) {
        $data = $err->get_error_data();
        foreach ( (array) ( $data['to'] ?? [] ) as $addr ) {
            $addr = trim( preg_replace( '/^.*<(.+)>.*$/', '$1', (string) $addr ) );
            if ( is_email( $addr ) && preg_match( '/(mailbox|user|recipient).*(unavailable|not exist|rejected)|550|554/i', $err->get_error_message() ) ) vpg_mail_suppress( $addr, 'hard-bounce' );
        }
    }
    $sent = (int) ( $c['sent'] ?? 0 ); $fail = (int) ( $c['fail'] ?? 0 );
    if ( $sent + $fail >= 50 && $fail / max( 1, $sent + $fail ) > 0.15 && ! get_transient( 'vpg_mail_alarm' ) ) {
        set_transient( 'vpg_mail_alarm', 1, DAY_IN_SECONDS );
        wp_mail( get_option( 'admin_email' ), __( '[VPG] Mail bounce rate is high', 'vpg-v2' ), sprintf( __( 'Failure ratio has crossed 15%% (%1$d failed of %2$d). Check deliverability.', 'vpg-v2' ), $fail, $sent + $fail ) );
    }
} );

/* ================================================================
 * 0805/0807/0833/0806/0834 · notification-mail digest queue
 * ================================================================ */
/** Route a member notification to email per their frequency choice. */
function vpg_mail_notify( $uid, $kind, $subject, $body, $priority = 'normal' ) {
    $u = get_userdata( $uid );
    if ( ! $u || vpg_mail_suppressed( $u->user_email ) ) return;
    $freq = vpg_mail_freq( $uid, $kind );
    if ( 'off' === $freq && 'critical' !== $priority ) return;

    // 0833/0806 · critical breaks through; otherwise honour quiet hours 22–7
    if ( 'critical' !== $priority && vpg_mail_in_quiet_hours( $uid ) && 'immediate' === $freq ) $freq = 'daily';

    if ( 'immediate' === $freq || 'critical' === $priority ) {
        wp_mail( $u->user_email, $subject, $body );
        return;
    }
    // 0807 · buffer so five events become one mail, not five
    $q = (array) get_user_meta( $uid, '_vpg_mail_queue', true );
    $q[] = [ 'kind' => $kind, 'subject' => $subject, 'body' => $body, 't' => time(), 'freq' => $freq ];
    update_user_meta( $uid, '_vpg_mail_queue', array_slice( $q, -50 ) );
}
function vpg_mail_freq( $uid, $kind ) {
    $prefs = (array) get_user_meta( $uid, '_vpg_mail_freq', true );
    return $prefs[ $kind ] ?? ( $prefs['*'] ?? 'immediate' );
}
function vpg_mail_in_quiet_hours( $uid ) {
    $h = (int) wp_date( 'G' ); // site timezone
    return ( $h >= 22 || $h < 7 );
}
/* record when a member typically reads, to time the digest (0834) */
add_action( 'wp_login', function ( $login, $user ) {
    if ( $user instanceof WP_User ) update_user_meta( $user->ID, '_vpg_active_hour', (int) wp_date( 'G' ) );
}, 10, 2 );

/* daily flush — sends 'daily' buffers, and 'weekly' buffers on Mondays */
add_action( 'vpg_mail_digest_flush', function () {
    $monday = ( '1' === wp_date( 'N' ) );
    $users  = get_users( [ 'meta_key' => '_vpg_mail_queue', 'fields' => [ 'ID', 'user_email' ] ] );
    foreach ( $users as $u ) {
        $q = (array) get_user_meta( $u->ID, '_vpg_mail_queue', true );
        if ( ! $q ) continue;
        $send = array_filter( $q, fn( $i ) => 'daily' === $i['freq'] || ( 'weekly' === $i['freq'] && $monday ) );
        $keep = array_filter( $q, fn( $i ) => ! ( 'daily' === $i['freq'] || ( 'weekly' === $i['freq'] && $monday ) ) );
        if ( ! $send ) continue;
        // send-time heuristic: only flush near the member's usual reading hour when known
        $hour = get_user_meta( $u->ID, '_vpg_active_hour', true );
        if ( '' !== $hour && (int) $hour !== (int) wp_date( 'G' ) && count( $send ) < 5 ) continue;
        $lines = array_map( fn( $i ) => '• ' . wp_strip_all_tags( $i['subject'] ), $send );
        $body  = __( 'Here’s what you missed on Vienna Photo Group:', 'vpg-v2' ) . "\n\n" . implode( "\n", $lines ) . "\n\n" . home_url( '/dashboard/' );
        wp_mail( $u->user_email, sprintf( _n( '%s update from VPG', '%s updates from VPG', count( $send ), 'vpg-v2' ), number_format_i18n( count( $send ) ) ), $body );
        update_user_meta( $u->ID, '_vpg_mail_queue', array_values( $keep ) );
    }
} );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_mail_digest_flush' ) ) wp_schedule_event( strtotime( 'tomorrow 8:00' ), 'daily', 'vpg_mail_digest_flush' );
} );

/* ================================================================
 * 0838 · throttled outbox for mass sends (kind to shared hosting)
 * ================================================================ */
function vpg_mail_enqueue( $to, $subject, $body ) {
    $box   = (array) get_option( 'vpg_mail_outbox', [] );
    foreach ( (array) $to as $addr ) $box[] = [ 'to' => $addr, 's' => $subject, 'b' => $body ];
    update_option( 'vpg_mail_outbox', $box, false );
    if ( ! wp_next_scheduled( 'vpg_mail_outbox_flush' ) ) wp_schedule_single_event( time() + 30, 'vpg_mail_outbox_flush' );
}
add_action( 'vpg_mail_outbox_flush', function () {
    $box = (array) get_option( 'vpg_mail_outbox', [] );
    $batch = (int) apply_filters( 'vpg_mail_outbox_batch', 20 ); // 20 per minute is gentle on shared hosting
    foreach ( array_splice( $box, 0, $batch ) as $m ) {
        if ( ! vpg_mail_suppressed( $m['to'] ) ) wp_mail( $m['to'], $m['s'], $m['b'] );
    }
    update_option( 'vpg_mail_outbox', array_values( $box ), false );
    if ( $box ) wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'vpg_mail_outbox_flush' );
} );

/* ================================================================
 * 0809/0812/0836/0837 · one-click unsubscribe + reason + yearly re-entry
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( 'mail-abmelden' !== $path ) return;
    $email = sanitize_email( wp_unslash( $_REQUEST['e'] ?? '' ) );
    $token = sanitize_text_field( wp_unslash( $_REQUEST['t'] ?? '' ) );
    $valid = $email && hash_equals( vpg_mail_unsub_token( $email ), $token );

    // RFC 8058 one-click POST — unsubscribe immediately, no page
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        if ( $valid ) vpg_mail_do_unsub( $email, 'one-click' );
        status_header( 200 ); header( 'Content-Type: text/plain' ); echo 'OK'; exit;
    }

    status_header( 200 );
    get_header();
    echo '<main id="vpg-main" class="g-wrap" style="max-width:620px;margin:40px auto;padding:0 20px">';
    if ( ! $valid ) {
        echo '<h1>' . esc_html__( 'Link expired', 'vpg-v2' ) . '</h1><p>' . esc_html__( 'Please manage your emails from your dashboard instead.', 'vpg-v2' ) . '</p>';
    } elseif ( isset( $_POST['confirm'] ) ) {
        vpg_mail_do_unsub( $email, sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) ) );
        $yearly = ! empty( $_POST['yearly'] );
        if ( $u = get_user_by( 'email', $email ) ) update_user_meta( $u->ID, '_vpg_yearly_only', $yearly ? 1 : 0 );
        echo '<h1>' . esc_html__( 'You’re unsubscribed', 'vpg-v2' ) . '</h1><p>' . esc_html__( 'No more routine emails. Thank you for telling us why.', 'vpg-v2' ) . '</p>';
        if ( $yearly ) echo '<p>' . esc_html__( 'We’ll send just one gentle note a year — nothing else.', 'vpg-v2' ) . '</p>';
    } else {
        echo '<h1>' . esc_html__( 'Unsubscribe', 'vpg-v2' ) . '</h1>';
        echo '<p>' . esc_html( sprintf( __( 'Stop routine emails to %s?', 'vpg-v2' ), $email ) ) . '</p>';
        echo '<form method="post" style="display:grid;gap:12px;max-width:460px">';
        echo '<label>' . esc_html__( 'One reason, if you like (optional):', 'vpg-v2' ) . '<br><select name="reason" class="g-input"><option value=""></option>'
           . '<option>' . esc_html__( 'Too many emails', 'vpg-v2' ) . '</option>'
           . '<option>' . esc_html__( 'Not relevant to me', 'vpg-v2' ) . '</option>'
           . '<option>' . esc_html__( 'I never signed up', 'vpg-v2' ) . '</option>'
           . '<option>' . esc_html__( 'Just taking a break', 'vpg-v2' ) . '</option></select></label>';
        echo '<label><input type="checkbox" name="yearly" value="1"> ' . esc_html__( 'Keep just one yearly note from VPG', 'vpg-v2' ) . '</label>';
        echo '<p><button class="g-btn" name="confirm" value="1">' . esc_html__( 'Unsubscribe', 'vpg-v2' ) . '</button></p></form>';
    }
    echo '</main>';
    get_footer();
    exit;
} );
function vpg_mail_do_unsub( $email, $reason = '' ) {
    if ( $u = get_user_by( 'email', $email ) ) {
        foreach ( [ '_vpg_pref_digest', '_vpg_pref_coffee', '_vpg_pref_event', '_vpg_pref_feedback' ] as $k ) update_user_meta( $u->ID, $k, '0' );
        update_user_meta( $u->ID, '_vpg_mail_freq', [ '*' => 'off' ] );
    }
    // also drop from the public newsletter list if present
    $list = (array) get_option( 'vpg_newsletter_list', [] );
    if ( isset( $list[ $email ] ) ) { unset( $list[ $email ] ); update_option( 'vpg_newsletter_list', $list ); }
    $reasons = (array) get_option( 'vpg_unsub_reasons', [] );
    if ( $reason ) { $reasons[] = [ 'r' => $reason, 't' => time() ]; update_option( 'vpg_unsub_reasons', array_slice( $reasons, -300 ), false ); }
    if ( function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'unsubscribe', $reason );
}

/* ================================================================
 * 0835 · minimal, aggregate-only open metric (no per-person tracking)
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( 'mail-open.gif' !== $path ) return;
    $c = (array) get_option( 'vpg_mail_opens', [] );
    $day = wp_date( 'Y-m-d' );
    $c[ $day ] = (int) ( $c[ $day ] ?? 0 ) + 1;
    update_option( 'vpg_mail_opens', array_slice( $c, -120, null, true ), false );
    header( 'Content-Type: image/gif' );
    echo base64_decode( 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' );
    exit;
} );

/* ================================================================
 * 0820 · web-viewable digest archive (past sends readable online)
 * ================================================================ */
function vpg_mail_archive_add( $subject, $html ) {
    $a = (array) get_option( 'vpg_mail_archive', [] );
    $a[] = [ 'subject' => $subject, 'html' => $html, 't' => time() ];
    update_option( 'vpg_mail_archive', array_slice( $a, -60 ), false );
}
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( 'mail-archiv' !== $path ) return;
    status_header( 200 ); get_header();
    echo '<main id="vpg-main" class="g-wrap" style="max-width:720px;margin:40px auto;padding:0 20px"><h1>' . esc_html__( 'Email archive', 'vpg-v2' ) . '</h1><p class="g-lede">' . esc_html__( 'Missed a digest? Read past sends here.', 'vpg-v2' ) . '</p>';
    $a = array_reverse( (array) get_option( 'vpg_mail_archive', [] ) );
    if ( $a ) foreach ( $a as $m ) echo '<article style="border-top:1px solid var(--g-line,#E6E5E1);padding:16px 0"><h2 style="font-size:18px">' . esc_html( $m['subject'] ) . '</h2><p style="color:#888;font-size:12px">' . esc_html( vpg_i18n_date( (int) $m['t'] ) ) . '</p><div>' . wp_kses_post( $m['html'] ) . '</div></article>';
    else echo '<p class="description">' . esc_html__( 'No archived emails yet.', 'vpg-v2' ) . '</p>';
    echo '</main>'; get_footer(); exit;
} );

/* ================================================================
 * 0805 · per-user email-frequency UI (on the dashboard profile hook)
 * ================================================================ */
add_action( 'vpg_profile_sections', function ( $user ) {
    if ( ! ( $user instanceof WP_User ) || $user->ID !== get_current_user_id() ) return;
    if ( isset( $_POST['_vpg_mailfreq'] ) && wp_verify_nonce( $_POST['_vpg_mailfreq'], 'vpg_mailfreq' ) ) {
        $prefs = [];
        foreach ( (array) ( $_POST['freq'] ?? [] ) as $k => $v ) $prefs[ sanitize_key( $k ) ] = in_array( $v, [ 'immediate', 'daily', 'weekly', 'off' ], true ) ? $v : 'immediate';
        update_user_meta( $user->ID, '_vpg_mail_freq', $prefs );
        echo '<p role="status" style="color:var(--g-red,#E5341F)">' . esc_html__( 'Email preferences saved.', 'vpg-v2' ) . '</p>';
    }
    $kinds = [ 'event' => __( 'Events & reminders', 'vpg-v2' ), 'community' => __( 'Community activity', 'vpg-v2' ), 'review' => __( 'Feedback on your work', 'vpg-v2' ), 'general' => __( 'General news', 'vpg-v2' ) ];
    $cur = (array) get_user_meta( $user->ID, '_vpg_mail_freq', true );
    echo '<section class="vpg-profile-sec"><h3>' . esc_html__( 'Email frequency', 'vpg-v2' ) . '</h3><form method="post">';
    wp_nonce_field( 'vpg_mailfreq', '_vpg_mailfreq' );
    echo '<table class="vpg-cardify"><tbody>';
    foreach ( $kinds as $k => $label ) {
        $v = $cur[ $k ] ?? ( $cur['*'] ?? 'immediate' );
        echo '<tr><td data-label="' . esc_attr__( 'Topic', 'vpg-v2' ) . '">' . esc_html( $label ) . '</td><td><select name="freq[' . esc_attr( $k ) . ']">';
        foreach ( [ 'immediate' => __( 'Right away', 'vpg-v2' ), 'daily' => __( 'Daily digest', 'vpg-v2' ), 'weekly' => __( 'Weekly digest', 'vpg-v2' ), 'off' => __( 'Off', 'vpg-v2' ) ] as $ov => $ol )
            echo '<option value="' . esc_attr( $ov ) . '"' . selected( $v, $ov, false ) . '>' . esc_html( $ol ) . '</option>';
        echo '</select></td></tr>';
    }
    echo '</tbody></table><p><button class="g-btn">' . esc_html__( 'Save email preferences', 'vpg-v2' ) . '</button></p></form>';
    echo '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Digests never arrive between 22:00 and 07:00. Critical account emails always come right away.', 'vpg-v2' ) . '</p></section>';
}, 25 );

/* ================================================================
 * Mail desk — preview, test-send, deliverability, docs
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'Mail & Notifications', 'vpg-v2' ), '📬 ' . __( 'Mail & Notifications', 'vpg-v2' ), 'manage_options', 'vpg-mail', 'vpg_mail_desk' );
} );
function vpg_mail_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['_vpg_mail'] ) && wp_verify_nonce( $_POST['_vpg_mail'], 'vpg_mail' ) ) {
        if ( isset( $_POST['reply_to'] ) ) update_option( 'vpg_reply_to', sanitize_email( wp_unslash( $_POST['reply_to'] ) ) );
        if ( ! empty( $_POST['test_send'] ) ) {
            $ok = wp_mail( wp_get_current_user()->user_email, __( 'VPG test email', 'vpg-v2' ), sanitize_textarea_field( wp_unslash( $_POST['test_body'] ?? 'Test.' ) ) );
            echo '<div class="notice notice-' . ( $ok ? 'success' : 'error' ) . '"><p>' . esc_html( $ok ? __( 'Test email sent to you.', 'vpg-v2' ) : __( 'Send failed — check the mail log.', 'vpg-v2' ) ) . '</p></div>';
        }
        if ( ! empty( $_POST['unsuppress'] ) ) {
            $s = (array) get_option( 'vpg_mail_suppress', [] ); unset( $s[ strtolower( sanitize_email( wp_unslash( $_POST['unsuppress'] ) ) ) ] ); update_option( 'vpg_mail_suppress', $s, false );
        }
    }
    $stats = (array) get_option( 'vpg_mail_stats', [] );
    $supp  = (array) get_option( 'vpg_mail_suppress', [] );
    $outbox = count( (array) get_option( 'vpg_mail_outbox', [] ) );
    $reasons = array_count_values( array_map( fn( $r ) => $r['r'], (array) get_option( 'vpg_unsub_reasons', [] ) ) );
    ?>
    <div class="wrap"><h1>📬 <?php esc_html_e( 'Mail & Notifications', 'vpg-v2' ); ?></h1>

      <h2><?php esc_html_e( '0810 · Deliverability', 'vpg-v2' ); ?></h2>
      <p><?php echo esc_html( sprintf( __( 'Sent %1$s · failed %2$s · outbox waiting %3$s', 'vpg-v2' ), number_format_i18n( (int) ( $stats['sent'] ?? 0 ) ), number_format_i18n( (int) ( $stats['fail'] ?? 0 ) ), number_format_i18n( $outbox ) ) ); ?></p>
      <?php if ( $supp ) { echo '<h3>' . esc_html__( '0811 · Suppressed (hard-bounced) addresses', 'vpg-v2' ) . '</h3><ul>'; foreach ( $supp as $addr => $meta ) echo '<li><code>' . esc_html( $addr ) . '</code> · ' . esc_html( $meta['why'] ?? '' ) . ' <form method="post" style="display:inline">' . wp_nonce_field( 'vpg_mail', '_vpg_mail', true, false ) . '<button class="button-link" name="unsuppress" value="' . esc_attr( $addr ) . '">' . esc_html__( 'restore', 'vpg-v2' ) . '</button></form></li>'; echo '</ul>'; } ?>

      <form method="post">
        <?php wp_nonce_field( 'vpg_mail', '_vpg_mail' ); ?>
        <h2><?php esc_html_e( '0808 · Reply-To', 'vpg-v2' ); ?></h2>
        <p><input type="email" name="reply_to" class="regular-text" value="<?php echo esc_attr( get_option( 'vpg_reply_to', get_option( 'admin_email' ) ) ); ?>"> <span class="description"><?php esc_html_e( 'Where replies to system mail should land.', 'vpg-v2' ); ?></span></p>

        <h2><?php esc_html_e( '0802 / 0822 · Preview & test-send', 'vpg-v2' ); ?></h2>
        <p><textarea name="test_body" rows="4" class="large-text code"><?php esc_html_e( 'Hello from Vienna Photo Group.', 'vpg-v2' ); ?></textarea></p>
        <p><button class="button button-primary" name="test_send" value="1"><?php esc_html_e( 'Send test to myself', 'vpg-v2' ); ?></button>
           <a class="button" href="<?php echo esc_url( home_url( '/mail-archiv/' ) ); ?>" target="_blank"><?php esc_html_e( 'View mail archive', 'vpg-v2' ); ?></a></p>
      </form>

      <?php if ( $reasons ) { echo '<h2>' . esc_html__( '0836 · Why people unsubscribe', 'vpg-v2' ) . '</h2><ul>'; arsort( $reasons ); foreach ( $reasons as $r => $n ) echo '<li>' . esc_html( $r ) . ' — ' . (int) $n . '</li>'; echo '</ul>'; } ?>

      <h2><?php esc_html_e( '0821 · Template variables', 'vpg-v2' ); ?></h2>
      <p class="description"><?php esc_html_e( 'Notification bodies support: the recipient display name, the dashboard URL, and the unsubscribe link (added automatically as a List-Unsubscribe header). Keep bodies plain text — the HTML shell and a dark-mode style are applied at send time, and a plain-text twin is generated for you.', 'vpg-v2' ); ?></p>

      <h2><?php esc_html_e( '0825 · Subject-line guidelines', 'vpg-v2' ); ?></h2>
      <ul style="list-style:disc;padding-left:20px">
        <li><?php esc_html_e( 'Say the thing — no “Newsletter #14”, no empty teasers.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( 'Under ~50 characters; front-load the meaning.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( 'No ALL CAPS, no “!!!”, no fake “Re:”.', 'vpg-v2' ); ?></li>
      </ul>

      <h2><?php esc_html_e( 'Assess once — 0827 BIMI · 0840 template versioning · 0828 preference import', 'vpg-v2' ); ?></h2>
      <p class="description"><?php esc_html_e( 'BIMI (logo in the inbox) needs DMARC at enforcement first — evaluate the effort once. Track mail-template changes in the repo like code. Keep the newsletter CSV export as the portable record of opt-outs so they survive a system change.', 'vpg-v2' ); ?></p>
    </div>
    <?php
}
