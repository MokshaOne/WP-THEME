<?php
/**
 * VPG v3 — Q2 quick wins · small levers on existing machinery.
 *
 *   0044/0045  Spot check · "still accurate" / "something changed"
 *   0076       Spot of the week · weekly pick, editorial can override
 *   0316       Random frame · jump to a random photographed piece
 *   0345       Private profile view counter
 *   0653       Expired-transient sweep · daily
 *   0654       Cron heartbeat · warns when wp_cron stopped beating
 *   0678       Maintenance drop-in · the 503 wears the design system
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 0044 + 0045 · Spot check box on map-type singles ──────────── */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] ) || ! in_the_loop() || ! is_main_query() || ! is_user_logged_in() ) {
        return $content;
    }
    $id      = get_the_ID();
    $checked = (int) get_post_meta( $id, '_vpg_checked_at', true );
    ob_start(); ?>
    <div style="margin-top:32px;padding:16px 20px;border:1px solid var(--g-line,#E6E5E1);display:flex;gap:16px;align-items:center;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:700;color:var(--g-mid,#6A6A6A)"><?php
            echo $checked
                ? esc_html( sprintf( __( 'Last confirmed accurate: %s', 'vpg-v2' ), date_i18n( get_option( 'date_format' ), $checked ) ) )
                : esc_html__( 'Not yet confirmed by a member on site.', 'vpg-v2' );
        ?></span>
        <span style="display:flex;gap:10px;margin-left:auto">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                <?php wp_nonce_field( 'vpg_spot_check' ); ?>
                <input type="hidden" name="action" value="vpg_spot_confirm">
                <input type="hidden" name="post" value="<?php echo (int) $id; ?>">
                <button type="submit" style="background:none;border:1px solid var(--g-line-2,#D9D7D2);padding:7px 12px;cursor:pointer;font:inherit;font-size:12px;font-weight:700">✓ <?php esc_html_e( 'Still accurate', 'vpg-v2' ); ?></button>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                <?php wp_nonce_field( 'vpg_spot_check' ); ?>
                <input type="hidden" name="action" value="vpg_spot_flag">
                <input type="hidden" name="post" value="<?php echo (int) $id; ?>">
                <button type="submit" style="background:none;border:1px solid var(--g-red,#E5341F);color:var(--g-red,#E5341F);padding:7px 12px;cursor:pointer;font:inherit;font-size:12px;font-weight:700">⚑ <?php esc_html_e( 'Something changed', 'vpg-v2' ); ?></button>
            </form>
        </span>
    </div>
    <?php
    return $content . ob_get_clean();
}, 28 );

add_action( 'admin_post_vpg_spot_confirm', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_spot_check' );
    $post = get_post( (int) ( $_POST['post'] ?? 0 ) );
    if ( $post && $post->post_status === 'publish' ) {
        update_post_meta( $post->ID, '_vpg_checked_at', time() );
        update_post_meta( $post->ID, '_vpg_checked_by', get_current_user_id() );
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', 'spot_confirmed', $post ? get_permalink( $post ) : home_url() ) );
    exit;
} );

add_action( 'admin_post_vpg_spot_flag', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_spot_check' );
    $post = get_post( (int) ( $_POST['post'] ?? 0 ) );
    if ( $post && $post->post_status === 'publish' ) {
        update_post_meta( $post->ID, '_vpg_flagged_at', time() );
        $who = wp_get_current_user();
        wp_mail( get_theme_mod( 'vpg_email', get_option( 'admin_email' ) ),
            '[VPG] ' . sprintf( __( 'Outdated info flagged · %s', 'vpg-v2' ), $post->post_title ),
            sprintf( __( "%1\$s says something changed at \"%2\$s\" — access, hours or the place itself.\n\nCheck and update:\n%3\$s", 'vpg-v2' ),
                $who->display_name, $post->post_title, admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) )
        );
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', 'spot_flagged', $post ? get_permalink( $post ) : home_url() ) );
    exit;
} );

add_action( 'wp_footer', function () {
    $status = sanitize_key( $_GET['vpg_status'] ?? '' );
    $map    = [
        'spot_confirmed' => __( 'Thank you — the spot now carries today as its confirmed date.', 'vpg-v2' ),
        'spot_flagged'   => __( 'Flag received — editorial will check and update the entry.', 'vpg-v2' ),
    ];
    if ( ! isset( $map[ $status ] ) ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-qw-toast"><?php echo esc_html( $map[ $status ] ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-qw-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ─── 0076 · Spot of the week · picked Monday, override via filter ─ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_pick_sotw' ) ) {
        wp_schedule_event( strtotime( 'next monday 06:00' ), 'weekly', 'vpg_pick_sotw' );
    }
} );

add_action( 'vpg_pick_sotw', function () {
    $ids = get_posts( [ 'post_type' => 'vpg_location', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 200, 'meta_key' => '_thumbnail_id' ] );
    if ( $ids ) {
        update_option( 'vpg_sotw', [ 'id' => $ids[ array_rand( $ids ) ], 'week' => gmdate( 'o-W' ) ], false );
    }
} );

function vpg_spot_of_week() {
    $sotw = get_option( 'vpg_sotw' );
    $id   = (int) apply_filters( 'vpg_spot_of_week', $sotw['id'] ?? 0 );
    return $id && get_post_status( $id ) === 'publish' ? $id : 0;
}

/* ─── 0316 · Random frame · any photographed published piece ────── */
add_action( 'admin_post_nopriv_vpg_random_frame', 'vpg_random_frame' );
add_action( 'admin_post_vpg_random_frame',        'vpg_random_frame' );
function vpg_random_frame() {
    $atts = get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 300,
        'fields'         => 'ids',
        'post_parent__not_in' => [ 0 ],
    ] );
    shuffle( $atts );
    foreach ( $atts as $aid ) {
        $parent = get_post_parent( $aid );
        if ( $parent && $parent->post_status === 'publish' ) {
            wp_safe_redirect( get_permalink( $parent ) );
            exit;
        }
    }
    wp_safe_redirect( home_url() );
    exit;
}

/* ─── 0345 · Profile views · counted quietly, shown only to you ─── */
add_action( 'template_redirect', function () {
    $login = get_query_var( 'vpg_member' );
    if ( ! $login ) return;
    $member = get_user_by( 'slug', sanitize_title( $login ) ) ?: get_user_by( 'login', sanitize_user( $login ) );
    if ( ! $member || get_current_user_id() === $member->ID ) return;
    update_user_meta( $member->ID, '_vpg_profile_views', (int) get_user_meta( $member->ID, '_vpg_profile_views', true ) + 1 );
} );

/* ─── 0653 · Expired transients swept daily ─────────────────────── */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_sweep_transients' ) ) {
        wp_schedule_event( strtotime( 'tomorrow 04:00' ), 'daily', 'vpg_sweep_transients' );
    }
} );
add_action( 'vpg_sweep_transients', function () {
    if ( function_exists( 'delete_expired_transients' ) ) delete_expired_transients( true );
} );

/* ─── 0654 · Cron heartbeat · the watchdog for the watchdogs ────── */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_heartbeat' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vpg_heartbeat' );
    }
} );
add_action( 'vpg_heartbeat', function () {
    update_option( 'vpg_heartbeat_at', time(), false );
} );
add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $beat = (int) get_option( 'vpg_heartbeat_at' );
    if ( $beat && time() - $beat > 2 * DAY_IN_SECONDS ) {
        printf(
            '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
            esc_html__( 'wp_cron has stopped beating.', 'vpg-v2' ),
            esc_html( sprintf(
                __( 'Last heartbeat: %s. Digest, reminders and challenges depend on it — check the easyname cronjob (see docs/runbooks/vpg-deploy-easyname.md).', 'vpg-v2' ),
                date_i18n( 'j. M Y H:i', $beat )
            ) )
        );
    }
} );

/* ─── 0678 · Maintenance drop-in · installed once, never overwritten ─ */
add_action( 'admin_init', function () {
    if ( get_option( 'vpg_maintenance_dropin' ) ) return;
    update_option( 'vpg_maintenance_dropin', 1, false );
    $src = VPG_V2_DIR . '/templates/maintenance-dropin.php';
    $dst = WP_CONTENT_DIR . '/maintenance.php';
    if ( file_exists( $src ) && ! file_exists( $dst ) && wp_is_writable( WP_CONTENT_DIR ) ) {
        copy( $src, $dst );
    }
} );
