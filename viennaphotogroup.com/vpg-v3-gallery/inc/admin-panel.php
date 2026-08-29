<?php
/**
 * VPG v3 — Admin panel.
 *
 * A single top-level "Vienna Photo Group" hub:
 *   • Dashboard — setup status, ACF status, content counts (+ quick add),
 *     and one-click links to the Customizer, setup wizard, submissions
 *     queue and magazine editor.
 *   • Settings  — identity + social, saved to the SAME theme-mods the
 *     Customizer uses, so the two stay in sync (vpg_identity() reads them).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Menu ────────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
    add_menu_page(
        __( 'Vienna Photo Group', 'vpg-v2' ),
        __( 'Vienna Photo Group', 'vpg-v2' ),
        'edit_posts',
        'vpg-hub',
        'vpg_hub_dashboard_page',
        'dashicons-camera',
        3
    );
    add_submenu_page( 'vpg-hub', __( 'Dashboard', 'vpg-v2' ), __( 'Dashboard', 'vpg-v2' ), 'edit_posts',      'vpg-hub',          'vpg_hub_dashboard_page' );
    add_submenu_page( 'vpg-hub', __( 'Settings', 'vpg-v2' ),  __( 'Settings', 'vpg-v2' ),  'manage_options',  'vpg-hub-settings', 'vpg_hub_settings_page' );
} );

/* ── Settings save (theme-mods · shared with the Customizer) ─────────── */
add_action( 'admin_post_vpg_hub_save', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_hub_save' );

    $map = [
        'vpg_email'            => 'sanitize_email',
        'vpg_location'         => 'sanitize_text_field',
        'vpg_booking'          => 'sanitize_text_field',
        'vpg_since'            => 'sanitize_text_field',
        'vpg_social_instagram' => 'esc_url_raw',
        'vpg_social_x'         => 'esc_url_raw',
    ];
    foreach ( $map as $key => $cb ) {
        $raw = wp_unslash( $_POST[ $key ] ?? '' );
        set_theme_mod( $key, call_user_func( $cb, $raw ) );
    }
    wp_safe_redirect( admin_url( 'admin.php?page=vpg-hub-settings&updated=1' ) );
    exit;
} );

/* ── Small helpers ───────────────────────────────────────────────────── */
function vpg_hub_card( $label, $value, $ok = null ) {
    $color = $ok === null ? '#1d2327' : ( $ok ? '#1f7a38' : '#b32d2e' );
    echo '<div style="background:#fff;border:1px solid #dcdcde;padding:16px 18px;min-width:150px">';
    echo '<div style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#646970;margin-bottom:6px">' . esc_html( $label ) . '</div>';
    echo '<div style="font-size:22px;font-weight:700;color:' . esc_attr( $color ) . '">' . wp_kses_post( $value ) . '</div>';
    echo '</div>';
}

/* ── Dashboard ───────────────────────────────────────────────────────── */
function vpg_hub_dashboard_page() {
    $setup_done = (bool) get_option( 'vpg_setup_complete' );
    $acf        = function_exists( 'vpg_acf_active' ) && vpg_acf_active();
    $cpts = [
        'vpg_magazine' => __( 'Magazine', 'vpg-v2' ),
        'vpg_event'    => __( 'Events', 'vpg-v2' ),
        'vpg_location' => __( 'Locations', 'vpg-v2' ),
        'vpg_studio'   => __( 'Studios', 'vpg-v2' ),
        'vpg_shop'     => __( 'Shops', 'vpg-v2' ),
        'vpg_review'   => __( 'Reviews', 'vpg-v2' ),
        'vpg_tutorial' => __( 'Tutorials', 'vpg-v2' ),
    ];
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-camera" style="font-size:28px;width:28px;height:28px;margin-right:6px"></span> <?php esc_html_e( 'Vienna Photo Group', 'vpg-v2' ); ?></h1>
        <p style="max-width:760px;color:#50575e"><?php esc_html_e( 'Your control centre — status, content and the tools that run the site. The look is managed in the Customizer; pages & menus by the setup wizard.', 'vpg-v2' ); ?></p>

        <h2><?php esc_html_e( 'Status', 'vpg-v2' ); ?></h2>
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:8px">
            <?php
            vpg_hub_card( __( 'Setup wizard', 'vpg-v2' ), $setup_done ? esc_html__( 'Complete', 'vpg-v2' ) : esc_html__( 'Not run', 'vpg-v2' ), $setup_done );
            vpg_hub_card( 'ACF', $acf ? esc_html__( 'Active', 'vpg-v2' ) : esc_html__( 'Native fields', 'vpg-v2' ), $acf );
            vpg_hub_card( __( 'Front page', 'vpg-v2' ), 'page' === get_option( 'show_on_front' ) ? esc_html__( 'Static (Home)', 'vpg-v2' ) : esc_html__( 'Posts', 'vpg-v2' ), 'page' === get_option( 'show_on_front' ) );
            vpg_hub_card( __( 'Journal', 'vpg-v2' ), get_option( 'page_for_posts' ) ? esc_html__( 'Wired', 'vpg-v2' ) : esc_html__( 'Unset', 'vpg-v2' ), (bool) get_option( 'page_for_posts' ) );
            ?>
        </div>
        <?php if ( ! $acf ) : ?>
            <p style="color:#646970;max-width:760px"><em><?php esc_html_e( 'Tip: install the free Advanced Custom Fields plugin for a richer editing UI on the CPTs. The theme works without it — native field boxes are used as a fallback.', 'vpg-v2' ); ?></em></p>
        <?php endif; ?>

        <h2 style="margin-top:24px"><?php esc_html_e( 'Content', 'vpg-v2' ); ?></h2>
        <table class="widefat striped" style="max-width:760px">
            <thead><tr><th><?php esc_html_e( 'Type', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Published', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Actions', 'vpg-v2' ); ?></th></tr></thead>
            <tbody>
            <?php foreach ( $cpts as $slug => $label ) :
                $count = (int) ( wp_count_posts( $slug )->publish ?? 0 ); ?>
                <tr>
                    <td><strong><?php echo esc_html( $label ); ?></strong></td>
                    <td><?php echo esc_html( $count ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $slug ) ); ?>"><?php esc_html_e( 'Manage', 'vpg-v2' ); ?></a> ·
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $slug ) ); ?>"><?php esc_html_e( 'Add new', 'vpg-v2' ); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2 style="margin-top:24px"><?php esc_html_e( 'Quick links', 'vpg-v2' ); ?></h2>
        <p>
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=vpg_v3' ) ); ?>"><?php esc_html_e( 'Customize design', 'vpg-v2' ); ?></a>
            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-hub-settings' ) ); ?>"><?php esc_html_e( 'Settings', 'vpg-v2' ); ?></a>
            <a class="button" href="<?php echo esc_url( admin_url( 'tools.php?page=vpg-setup' ) ); ?>"><?php esc_html_e( 'Setup & tools', 'vpg-v2' ); ?></a>
            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-submissions' ) ); ?>"><?php esc_html_e( 'Submissions', 'vpg-v2' ); ?></a>
            <a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-magazine' ) ); ?>"><?php esc_html_e( 'Magazine editor', 'vpg-v2' ); ?></a>
        </p>
    </div>
    <?php
}

/* ── Settings ────────────────────────────────────────────────────────── */
function vpg_hub_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $fields = [
        'vpg_email'            => [ __( 'Contact email', 'vpg-v2' ),   get_option( 'admin_email' ) ],
        'vpg_location'         => [ __( 'Location', 'vpg-v2' ),        'Wien · UTC+1' ],
        'vpg_booking'          => [ __( 'Booking status', 'vpg-v2' ),  'Q3 · slots open' ],
        'vpg_since'            => [ __( 'Founded year', 'vpg-v2' ),    '2019' ],
        'vpg_social_instagram' => [ __( 'Instagram URL', 'vpg-v2' ),   '' ],
        'vpg_social_x'         => [ __( 'X / Twitter URL', 'vpg-v2' ), '' ],
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'VPG · Settings', 'vpg-v2' ); ?></h1>
        <?php if ( ! empty( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'vpg-v2' ); ?></p></div>
        <?php endif; ?>
        <p style="max-width:680px;color:#50575e"><?php esc_html_e( 'Identity & social details. These share storage with the Customizer (Identity & social) — edit in either place.', 'vpg-v2' ); ?></p>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_hub_save' ); ?>
            <input type="hidden" name="action" value="vpg_hub_save">
            <table class="form-table" role="presentation">
                <?php foreach ( $fields as $key => $row ) :
                    $val = get_theme_mod( $key, $row[1] ); ?>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $row[0] ); ?></label></th>
                        <td><input name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" type="text" class="regular-text" value="<?php echo esc_attr( $val ); ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button( __( 'Save settings', 'vpg-v2' ) ); ?>
        </form>

        <hr>
        <p><a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=vpg_palette' ) ); ?>"><?php esc_html_e( 'Edit colours in the Customizer', 'vpg-v2' ); ?></a></p>
    </div>
    <?php
}
