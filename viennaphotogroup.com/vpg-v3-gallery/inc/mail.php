<?php
/**
 * VPG v3 — Mail · SMTP transport + delivery log.
 *
 * Configure SMTP in wp-config.php (never in the database):
 *   define( 'VPG_SMTP_HOST', 'smtp.example.com' );
 *   define( 'VPG_SMTP_PORT', 587 );
 *   define( 'VPG_SMTP_USER', 'hallo@viennaphotogroup.com' );
 *   define( 'VPG_SMTP_PASS', '…' );
 *   define( 'VPG_SMTP_FROM', 'hallo@viennaphotogroup.com' );   // optional
 *   define( 'VPG_SMTP_SECURE', 'tls' );                        // tls|ssl
 *
 * Without the constants nothing changes — wp_mail() uses PHP mail() as
 * before. The log keeps the last 50 sends/failures either way, visible
 * under Magazine → Mail log. DNS (SPF/DKIM/DMARC) is documented in
 * docs/runbooks/vpg-mail-deliverability.md.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── SMTP transport ────────────────────────────────────────────── */
add_action( 'phpmailer_init', function ( $phpmailer ) {
    if ( ! defined( 'VPG_SMTP_HOST' ) || ! VPG_SMTP_HOST ) return;
    $phpmailer->isSMTP();
    $phpmailer->Host       = VPG_SMTP_HOST;
    $phpmailer->Port       = defined( 'VPG_SMTP_PORT' ) ? (int) VPG_SMTP_PORT : 587;
    $phpmailer->SMTPSecure = defined( 'VPG_SMTP_SECURE' ) ? VPG_SMTP_SECURE : 'tls';
    if ( defined( 'VPG_SMTP_USER' ) && VPG_SMTP_USER ) {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = VPG_SMTP_USER;
        $phpmailer->Password = defined( 'VPG_SMTP_PASS' ) ? VPG_SMTP_PASS : '';
    }
    if ( defined( 'VPG_SMTP_FROM' ) && VPG_SMTP_FROM ) {
        $phpmailer->setFrom( VPG_SMTP_FROM, get_bloginfo( 'name' ), false );
    }
} );

/* From-address defaults · a real mailbox beats wordpress@host */
add_filter( 'wp_mail_from', function ( $from ) {
    if ( defined( 'VPG_SMTP_FROM' ) && VPG_SMTP_FROM ) return VPG_SMTP_FROM;
    if ( strpos( $from, 'wordpress@' ) === 0 ) {
        $admin = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
        if ( is_email( $admin ) ) return $admin;
    }
    return $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
    return $name === 'WordPress' ? get_bloginfo( 'name' ) : $name;
} );

/* ─── Delivery log · ring buffer of the last 50 sends ───────────── */
function vpg_mail_log_push( $row ) {
    $log = get_option( 'vpg_mail_log', [] );
    $log = is_array( $log ) ? $log : [];
    array_unshift( $log, $row );
    update_option( 'vpg_mail_log', array_slice( $log, 0, 50 ), false );
}

add_filter( 'wp_mail', function ( $atts ) {
    $to = is_array( $atts['to'] ?? '' ) ? implode( ', ', (array) $atts['to'] ) : (string) ( $atts['to'] ?? '' );
    vpg_mail_log_push( [
        'time'    => current_time( 'mysql' ),
        'to'      => $to,
        'subject' => (string) ( $atts['subject'] ?? '' ),
        'status'  => 'queued',
    ] );
    return $atts;
} );

add_action( 'wp_mail_failed', function ( $error ) {
    $log = get_option( 'vpg_mail_log', [] );
    if ( is_array( $log ) && isset( $log[0] ) ) {
        $log[0]['status'] = 'FAILED: ' . $error->get_error_message();
        update_option( 'vpg_mail_log', $log, false );
    }
} );

add_action( 'wp_mail_succeeded', function () {
    $log = get_option( 'vpg_mail_log', [] );
    if ( is_array( $log ) && isset( $log[0] ) && $log[0]['status'] === 'queued' ) {
        $log[0]['status'] = 'sent';
        update_option( 'vpg_mail_log', $log, false );
    }
} );

/* ─── Admin view ────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-magazine', __( 'Mail log', 'vpg-v2' ), __( '📮 Mail log', 'vpg-v2' ), 'manage_options', 'vpg-mail-log', function () {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
        $log  = get_option( 'vpg_mail_log', [] );
        $smtp = defined( 'VPG_SMTP_HOST' ) && VPG_SMTP_HOST;
        ?>
        <div class="wrap">
            <h1>📮 <?php esc_html_e( 'Mail log', 'vpg-v2' ); ?></h1>
            <p class="description">
                <?php echo $smtp
                    ? esc_html( sprintf( __( 'Transport: SMTP via %s', 'vpg-v2' ), VPG_SMTP_HOST ) )
                    : esc_html__( 'Transport: PHP mail() — set the VPG_SMTP_* constants in wp-config.php for reliable delivery (see docs/runbooks/vpg-mail-deliverability.md).', 'vpg-v2' ); ?>
            </p>
            <table class="widefat striped" style="margin-top:1rem">
                <thead><tr><th style="width:160px"><?php esc_html_e( 'Time', 'vpg-v2' ); ?></th><th style="width:240px"><?php esc_html_e( 'To', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Subject', 'vpg-v2' ); ?></th><th style="width:220px"><?php esc_html_e( 'Status', 'vpg-v2' ); ?></th></tr></thead>
                <tbody>
                <?php if ( ! is_array( $log ) || ! $log ) : ?>
                    <tr><td colspan="4"><?php esc_html_e( 'No mail sent yet.', 'vpg-v2' ); ?></td></tr>
                <?php else : foreach ( $log as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( $row['time'] ); ?></td>
                        <td><?php echo esc_html( $row['to'] ); ?></td>
                        <td><?php echo esc_html( $row['subject'] ); ?></td>
                        <td style="<?php echo strpos( $row['status'], 'FAILED' ) === 0 ? 'color:#b32d2e;font-weight:600' : ''; ?>"><?php echo esc_html( $row['status'] ); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    } );
}, 16 );
