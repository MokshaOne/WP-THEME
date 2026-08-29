<?php
/**
 * VPG v3 — Newsletter list · in-house, double-opt-in, no external service.
 *
 * Storage: option `vpg_newsletter_list` — [ email => [name, status, token,
 * added] ] with status pending|confirmed. The newsletter page form posts
 * vpg_newsletter; the confirm link flips the status; an admin screen under
 * the VPG hub lists + exports CSV.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function vpg_newsletter_list() {
    $list = get_option( 'vpg_newsletter_list', [] );
    return is_array( $list ) ? $list : [];
}

/* ─── Signup · sends the confirmation link ──────────────────────── */
add_action( 'admin_post_nopriv_vpg_newsletter', 'vpg_handle_newsletter' );
add_action( 'admin_post_vpg_newsletter',        'vpg_handle_newsletter' );
function vpg_handle_newsletter() {
    check_admin_referer( 'vpg_newsletter' );
    if ( function_exists( 'vpg_antispam_passed' ) && ! vpg_antispam_passed() ) {
        vpg_redirect_with_status( 'newsletter', 'ok' ); // silent drop
    }

    $name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    if ( ! is_email( $email ) ) vpg_redirect_with_status( 'newsletter', 'invalid' );

    $list  = vpg_newsletter_list();
    $entry = $list[ $email ] ?? null;
    if ( $entry && $entry['status'] === 'confirmed' ) {
        vpg_redirect_with_status( 'newsletter', 'ok' ); // already in · no re-mail
    }

    $token = wp_generate_password( 24, false, false );
    $list[ $email ] = [
        'name'   => $name,
        'status' => 'pending',
        'token'  => $token,
        'added'  => current_time( 'mysql' ),
    ];
    update_option( 'vpg_newsletter_list', $list, false );

    $confirm = add_query_arg( [
        'action' => 'vpg_newsletter_confirm',
        'email'  => rawurlencode( $email ),
        'token'  => $token,
    ], admin_url( 'admin-post.php' ) );

    wp_mail( $email,
        __( 'Confirm your VPG newsletter signup', 'vpg-v2' ),
        sprintf(
            /* translators: 1: name or 'there', 2: confirm URL */
            __( "Hello %1\$s,\n\nOne click and you're on the list — we write when an issue ships, not more:\n\n%2\$s\n\nNot you? Ignore this email and nothing happens.\n\n— Vienna Photo Group", 'vpg-v2' ),
            $name ?: __( 'there', 'vpg-v2' ),
            $confirm
        )
    );

    vpg_redirect_with_status( 'newsletter', 'confirm_sent' );
}

/* ─── Confirm link ──────────────────────────────────────────────── */
add_action( 'admin_post_nopriv_vpg_newsletter_confirm', 'vpg_handle_newsletter_confirm' );
add_action( 'admin_post_vpg_newsletter_confirm',        'vpg_handle_newsletter_confirm' );
function vpg_handle_newsletter_confirm() {
    $email = sanitize_email( rawurldecode( wp_unslash( $_GET['email'] ?? '' ) ) );
    $token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
    $list  = vpg_newsletter_list();

    if ( $email && isset( $list[ $email ] ) && $token && hash_equals( (string) $list[ $email ]['token'], $token ) ) {
        $list[ $email ]['status'] = 'confirmed';
        $list[ $email ]['token']  = '';
        update_option( 'vpg_newsletter_list', $list, false );
        vpg_redirect_with_status( 'newsletter', 'confirmed' );
    }
    vpg_redirect_with_status( 'newsletter', 'verify_fail' );
}

/* ─── Unsubscribe · one-click, no login ─────────────────────────── */
add_action( 'admin_post_nopriv_vpg_newsletter_out', 'vpg_handle_newsletter_out' );
add_action( 'admin_post_vpg_newsletter_out',        'vpg_handle_newsletter_out' );
function vpg_handle_newsletter_out() {
    $email = sanitize_email( rawurldecode( wp_unslash( $_GET['email'] ?? '' ) ) );
    $key   = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) );
    $list  = vpg_newsletter_list();
    if ( $email && isset( $list[ $email ] ) && hash_equals( md5( $email . wp_salt() ), $key ) ) {
        unset( $list[ $email ] );
        update_option( 'vpg_newsletter_list', $list, false );
    }
    vpg_redirect_with_status( 'newsletter', 'unsubscribed' );
}

function vpg_newsletter_unsub_url( $email ) {
    return add_query_arg( [
        'action' => 'vpg_newsletter_out',
        'email'  => rawurlencode( $email ),
        'key'    => md5( $email . wp_salt() ),
    ], admin_url( 'admin-post.php' ) );
}

/* ─── Toasts ────────────────────────────────────────────────────── */
add_action( 'wp_footer', function () {
    $status = sanitize_key( $_GET['vpg_status'] ?? '' );
    $map    = [
        'confirm_sent' => [ 'success', __( 'One more step — confirm the link we just mailed you.', 'vpg-v2' ) ],
        'confirmed'    => [ 'success', __( 'You\'re on the list. We write when an issue ships.', 'vpg-v2' ) ],
        'unsubscribed' => [ 'success', __( 'You\'re off the list. No hard feelings.', 'vpg-v2' ) ],
    ];
    if ( ! isset( $map[ $status ] ) ) return;
    ?>
    <div class="vpg-toast vpg-toast--<?php echo esc_attr( $map[ $status ][0] ); ?> is-visible" id="vpg-nl-toast"><?php echo esc_html( $map[ $status ][1] ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-nl-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ─── Admin screen · list + CSV export under the VPG hub ────────── */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-magazine', __( 'Newsletter', 'vpg-v2' ), __( '✉ Newsletter list', 'vpg-v2' ), 'edit_others_posts', 'vpg-newsletter', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        $list = vpg_newsletter_list();
        $csv  = wp_nonce_url( admin_url( 'admin-post.php?action=vpg_newsletter_csv' ), 'vpg_newsletter_csv' );
        ?>
        <div class="wrap">
            <h1>✉ <?php esc_html_e( 'Newsletter list', 'vpg-v2' ); ?>
                <a class="page-title-action" href="<?php echo esc_url( $csv ); ?>"><?php esc_html_e( 'Export CSV', 'vpg-v2' ); ?></a>
            </h1>
            <table class="widefat striped" style="margin-top:1rem;max-width:760px">
                <thead><tr><th><?php esc_html_e( 'Email', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Name', 'vpg-v2' ); ?></th><th style="width:120px"><?php esc_html_e( 'Status', 'vpg-v2' ); ?></th><th style="width:160px"><?php esc_html_e( 'Added', 'vpg-v2' ); ?></th></tr></thead>
                <tbody>
                <?php if ( ! $list ) : ?>
                    <tr><td colspan="4"><?php esc_html_e( 'Nobody yet.', 'vpg-v2' ); ?></td></tr>
                <?php else : foreach ( $list as $email => $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $email ); ?></td>
                        <td><?php echo esc_html( $row['name'] ); ?></td>
                        <td><?php echo esc_html( $row['status'] ); ?></td>
                        <td><?php echo esc_html( $row['added'] ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    } );
}, 15 );

add_action( 'admin_post_vpg_newsletter_csv', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_newsletter_csv' );
    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="vpg-newsletter.csv"' );
    $out = fopen( 'php://output', 'w' );
    fputcsv( $out, [ 'email', 'name', 'status', 'added' ] );
    foreach ( vpg_newsletter_list() as $email => $row ) {
        fputcsv( $out, [ $email, $row['name'], $row['status'], $row['added'] ] );
    }
    exit;
} );
