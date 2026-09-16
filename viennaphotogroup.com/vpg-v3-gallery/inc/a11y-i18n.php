<?php
/**
 * VPG v3 — Cluster 19 · Barrierefreiheit & i18n.
 *
 * Reuses the existing skip link (header.php), language switcher and
 * translation watch (inc/i18n.php, inc/followups2.php) and the AI alt-text
 * backfill (inc/advanced.php) — this file adds only what was missing:
 *
 *   0729 aria-live announcer · 0731/0732/0734/0735 a11y CSS (assets/css/a11y.css)
 *   0737 alt-text coverage % · 0741 shortcut help · 0742 extended skip links
 *   0744 form survival · 0746 dyslexia + 0758 simple mode (assets/js/a11y.js)
 *   0748 accessibility statement · 0749 barrier report · 0759 sign-language link
 *   0753/0754 locale date & number helpers · A11y desk (checklist for the rest)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---- assets, loaded globally like vpg-main ---- */
add_action( 'wp_enqueue_scripts', function () {
    if ( is_admin() ) return;
    $v = fn( $r ) => file_exists( VPG_V2_DIR . $r ) ? (string) filemtime( VPG_V2_DIR . $r ) : VPG_V2_VERSION;
    wp_enqueue_style( 'vpg-a11y', VPG_V2_URI . '/assets/css/a11y.css', [ 'vpg-gallery' ], $v( '/assets/css/a11y.css' ) );
    wp_enqueue_script( 'vpg-a11y', VPG_V2_URI . '/assets/js/a11y.js', [], $v( '/assets/js/a11y.js' ), true );
    wp_localize_script( 'vpg-a11y', 'vpgA11yStr', [
        'title'     => __( 'Accessibility', 'vpg-v2' ),
        'heading'   => __( 'Reading comfort', 'vpg-v2' ),
        'simple'    => __( 'Simple mode', 'vpg-v2' ),
        'dyslexia'  => __( 'Dyslexia-friendly text', 'vpg-v2' ),
        'on'        => __( 'On', 'vpg-v2' ),
        'off'       => __( 'Off', 'vpg-v2' ),
        'stmtUrl'   => esc_url( home_url( '/accessibility/' ) ),
        'stmtLink'  => __( 'Accessibility statement', 'vpg-v2' ),
        'helpTitle' => __( 'Keyboard shortcuts', 'vpg-v2' ),
        'close'     => __( 'Close', 'vpg-v2' ),
        'kHelp'     => __( 'Show this help', 'vpg-v2' ),
        'kSearch'   => __( 'Open search palette', 'vpg-v2' ),
        'kTab'      => __( 'Move to next control', 'vpg-v2' ),
        'kEsc'      => __( 'Close dialogs & overlays', 'vpg-v2' ),
        'kArrows'   => __( 'Previous / next photo in the lightbox', 'vpg-v2' ),
        'kInfo'     => __( 'Toggle photo info in the lightbox', 'vpg-v2' ),
    ] );
}, 20 );

/* ---- 0742 · extended skip links (map, search, content) after the body opens ---- */
add_action( 'wp_body_open', function () {
    echo '<nav class="vpg-skips" aria-label="' . esc_attr__( 'Skip links', 'vpg-v2' ) . '">'
       . '<a href="#vpg-main">' . esc_html__( 'Skip to content', 'vpg-v2' ) . '</a>'
       . '<a href="' . esc_url( home_url( '/locations/' ) ) . '">' . esc_html__( 'Skip to map', 'vpg-v2' ) . '</a>'
       . '<a href="' . esc_url( home_url( '/?s=' ) ) . '">' . esc_html__( 'Skip to search', 'vpg-v2' ) . '</a>'
       . '</nav>';
}, 1 );

/* ---- 0753 · locale-aware date · 0754 · locale-aware number (thin, honest wrappers) ---- */
function vpg_i18n_date( $ts, $fmt = '' ) {
    $ts  = is_numeric( $ts ) ? (int) $ts : strtotime( (string) $ts );
    $fmt = $fmt ?: ( get_option( 'date_format' ) ?: 'j. F Y' );
    return function_exists( 'wp_date' ) ? wp_date( $fmt, $ts ) : date_i18n( $fmt, $ts );
}
function vpg_i18n_num( $n, $dec = 0 ) {
    return number_format_i18n( (float) $n, (int) $dec );
}

/* ================================================================
 * 0748 / 0749 / 0759 · Accessibility statement + barrier report
 * ================================================================ */
add_action( 'init', function () {
    add_rewrite_rule( '^accessibility/?$',      'index.php?vpg_a11y=1', 'top' );
    add_rewrite_rule( '^barrierefreiheit/?$',   'index.php?vpg_a11y=1', 'top' );
} );
add_filter( 'query_vars', fn( $v ) => array_merge( $v, [ 'vpg_a11y' ] ) );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_a11y' ) ) return;
    status_header( 200 );
    $sign = get_option( 'vpg_a11y_sign_url', '' );      // 0759 · optional sign-language video contact
    $sent = isset( $_GET['gemeldet'] );
    get_header();
    echo '<main id="vpg-main" class="g-wrap" style="max-width:760px;margin:40px auto;padding:0 20px">';
    echo '<h1>' . esc_html__( 'Accessibility statement', 'vpg-v2' ) . '</h1>';
    echo '<p class="g-lede">' . esc_html__( 'Vienna Photo Group is a free, member-run collective. We want everyone — in any language, with any device, for any eye — to be able to read the map, the magazine and each other’s work.', 'vpg-v2' ) . '</p>';

    echo '<h2>' . esc_html__( 'What we commit to', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.7">';
    foreach ( [
        __( 'Keyboard reachable — every core flow works without a mouse (press ? for shortcuts).', 'vpg-v2' ),
        __( 'Visible focus, honoured reduced-motion, reduced-data and high-contrast preferences.', 'vpg-v2' ),
        __( 'Meaningful alt text on editorial images; categories never coded by colour alone.', 'vpg-v2' ),
        __( 'A simple mode and a dyslexia-friendly reading mode, both one tap away (♿ button, lower left).', 'vpg-v2' ),
        __( 'Readable at 200 % zoom without horizontal scrolling; layouts survive raised text spacing.', 'vpg-v2' ),
    ] as $c ) echo '<li>' . esc_html( $c ) . '</li>';
    echo '</ul>';

    echo '<h2>' . esc_html__( 'Standard & status', 'vpg-v2' ) . '</h2>';
    echo '<p>' . esc_html__( 'We aim at WCAG 2.1 AA. We are not perfect yet — this is a living effort and your reports move it forward.', 'vpg-v2' ) . '</p>';

    if ( $sign ) {
        echo '<h2>' . esc_html__( 'Contact in sign language', 'vpg-v2' ) . '</h2>';
        echo '<p><a href="' . esc_url( $sign ) . '">' . esc_html__( 'Reach us with a sign-language video message', 'vpg-v2' ) . '</a></p>';
    }

    echo '<h2 id="report">' . esc_html__( 'Report a barrier (two clicks)', 'vpg-v2' ) . '</h2>';
    if ( $sent ) {
        echo '<p role="status" style="border-left:3px solid var(--g-red,#E5341F);padding-left:12px">' . esc_html__( 'Thank you — your report reached us. We read every one.', 'vpg-v2' ) . '</p>';
    }
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-vpg-keep="barrier" style="display:grid;gap:10px;max-width:520px">';
    echo '<input type="hidden" name="action" value="vpg_barrier_report">';
    wp_nonce_field( 'vpg_barrier', 'vpg_barrier_nonce' );
    echo '<label>' . esc_html__( 'Where did you hit a barrier?', 'vpg-v2' ) . '<br><input class="g-input" type="text" name="where" value="' . esc_attr( wp_get_referer() ?: '' ) . '" style="width:100%"></label>';
    echo '<label>' . esc_html__( 'What went wrong?', 'vpg-v2' ) . ' <span style="color:var(--g-red,#E5341F)">*</span><br><textarea class="g-input" name="what" required rows="4" style="width:100%"></textarea></label>';
    echo '<label>' . esc_html__( 'Email for a reply (optional)', 'vpg-v2' ) . '<br><input class="g-input" type="email" name="email" style="width:100%"></label>';
    echo '<p><button class="g-btn" type="submit">' . esc_html__( 'Send report', 'vpg-v2' ) . '</button></p>';
    echo '</form>';
    echo '</main>';
    get_footer();
    exit;
} );

add_action( 'admin_post_nopriv_vpg_barrier_report', 'vpg_handle_barrier_report' );
add_action( 'admin_post_vpg_barrier_report', 'vpg_handle_barrier_report' );
function vpg_handle_barrier_report() {
    if ( ! isset( $_POST['vpg_barrier_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_barrier_nonce'], 'vpg_barrier' ) ) {
        wp_safe_redirect( home_url( '/accessibility/' ) ); exit;
    }
    $rep = [
        'where' => esc_url_raw( wp_unslash( $_POST['where'] ?? '' ) ),
        'what'  => sanitize_textarea_field( wp_unslash( $_POST['what'] ?? '' ) ),
        'email' => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
        't'     => time(),
    ];
    if ( $rep['what'] !== '' ) {
        $all = (array) get_option( 'vpg_barrier_reports', [] );
        $all[] = $rep;
        update_option( 'vpg_barrier_reports', array_slice( $all, -300 ), false );
        $to = get_option( 'admin_email' );
        if ( $to ) {
            wp_mail(
                $to,
                __( '[VPG] A barrier was reported', 'vpg-v2' ),
                sprintf( "%s\n\n%s: %s\n%s: %s", $rep['what'], __( 'Where', 'vpg-v2' ), $rep['where'] ?: '—', __( 'Reply to', 'vpg-v2' ), $rep['email'] ?: '—' )
            );
        }
    }
    wp_safe_redirect( home_url( '/accessibility/?gemeldet=1#report' ) );
    exit;
}

/* ================================================================
 * 0737 · Alt-text coverage — computed, cached daily
 * ================================================================ */
function vpg_alt_coverage() {
    $c = get_transient( 'vpg_alt_coverage' );
    if ( false !== $c ) return $c;
    global $wpdb;
    $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='attachment' AND post_mime_type LIKE 'image/%'" );
    $withalt = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key='_wp_attachment_image_alt' AND TRIM(meta_value)<>''" );
    $pct = $total ? round( $withalt / $total * 100 ) : 100;
    $c = [ 'total' => $total, 'withalt' => $withalt, 'pct' => $pct ];
    set_transient( 'vpg_alt_coverage', $c, DAY_IN_SECONDS );
    return $c;
}

/* ================================================================
 * A11y desk — coverage, barrier inbox, translation watch, checklist
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'Accessibility & i18n', 'vpg-v2' ), '♿ ' . __( 'Accessibility', 'vpg-v2' ), 'manage_options', 'vpg-a11y', function () {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );

        if ( isset( $_POST['vpg_sign_url'], $_POST['_vpg_a11y_desk'] ) && wp_verify_nonce( $_POST['_vpg_a11y_desk'], 'vpg_a11y_desk' ) ) {
            update_option( 'vpg_a11y_sign_url', esc_url_raw( wp_unslash( $_POST['vpg_sign_url'] ) ) );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }

        $cov = vpg_alt_coverage();
        $reports = array_reverse( (array) get_option( 'vpg_barrier_reports', [] ) );
        $untr = function_exists( 'vpg_untranslated_strings' ) ? vpg_untranslated_strings( 400 ) : [];
        ?>
        <div class="wrap"><h1>♿ <?php esc_html_e( 'Accessibility & i18n', 'vpg-v2' ); ?></h1>

          <h2><?php esc_html_e( '0737 · Alt-text coverage', 'vpg-v2' ); ?></h2>
          <p style="font-size:15px"><strong><?php echo (int) $cov['pct']; ?>%</strong>
            — <?php echo esc_html( sprintf( __( '%1$s of %2$s image attachments have alt text.', 'vpg-v2' ), number_format_i18n( $cov['withalt'] ), number_format_i18n( $cov['total'] ) ) ); ?></p>
          <div style="background:#eee;height:14px;max-width:400px"><div style="background:var(--g-red,#E5341F);height:14px;width:<?php echo (int) $cov['pct']; ?>%"></div></div>
          <p class="description"><?php esc_html_e( 'Recomputed daily. The AI backfill cron fills gaps automatically; editorial images still deserve a human sentence.', 'vpg-v2' ); ?></p>

          <h2><?php esc_html_e( 'Translation watch', 'vpg-v2' ); ?></h2>
          <p><?php echo esc_html( sprintf( _n( '%s string still needs a German translation.', '%s strings still need a German translation.', count( $untr ), 'vpg-v2' ), number_format_i18n( count( $untr ) ) ) ); ?>
            <?php if ( $untr ) : ?><br><span class="description"><?php echo esc_html( implode( ' · ', array_slice( $untr, 0, 8 ) ) ); ?> …</span><?php endif; ?></p>

          <h2><?php esc_html_e( '0749 · Reported barriers', 'vpg-v2' ); ?></h2>
          <?php if ( $reports ) { echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'When', 'vpg-v2' ) . '</th><th>' . esc_html__( 'What', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Where', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Reply', 'vpg-v2' ) . '</th></tr></thead><tbody>';
              foreach ( array_slice( $reports, 0, 50 ) as $r ) {
                  echo '<tr><td>' . esc_html( vpg_i18n_date( (int) ( $r['t'] ?? 0 ), 'j.n.y H:i' ) ) . '</td><td>' . esc_html( $r['what'] ?? '' ) . '</td><td>' . ( ! empty( $r['where'] ) ? '<a href="' . esc_url( $r['where'] ) . '">' . esc_html__( 'link', 'vpg-v2' ) . '</a>' : '—' ) . '</td><td>' . ( ! empty( $r['email'] ) ? '<a href="mailto:' . esc_attr( $r['email'] ) . '">' . esc_html( $r['email'] ) . '</a>' : '—' ) . '</td></tr>';
              }
              echo '</tbody></table>';
          } else echo '<p class="description">' . esc_html__( 'No barriers reported. Good — but keep asking.', 'vpg-v2' ) . '</p>'; ?>

          <h2><?php esc_html_e( '0759 · Sign-language contact link', 'vpg-v2' ); ?></h2>
          <form method="post">
            <?php wp_nonce_field( 'vpg_a11y_desk', '_vpg_a11y_desk' ); ?>
            <input type="url" class="regular-text" name="vpg_sign_url" value="<?php echo esc_attr( get_option( 'vpg_a11y_sign_url', '' ) ); ?>" placeholder="https://…">
            <button class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button>
            <p class="description"><?php esc_html_e( 'A link where deaf members can reach us with a signed video (e.g. a relay service or a booking form). Shown on the statement page when set.', 'vpg-v2' ); ?></p>
          </form>

          <h2><?php esc_html_e( 'Human review — the parts code can’t finish', 'vpg-v2' ); ?></h2>
          <ol style="padding-left:20px;line-height:1.7">
            <li><?php esc_html_e( '0725 Screenreader test day — half-yearly pass with a real user (NVDA / VoiceOver).', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0726 Focus-order audit — document the tab path of every core flow.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0730 Contrast sweep — every grey checked against both backgrounds (a11y.css adds prefers-contrast).', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0736 Alt-text guide — describe, don’t decorate; decorative images stay alt="".', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0750 Third-party audit — budget an external review as a yearly goal.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0751 Axe checks in CI — block a11y regressions before merge.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0752 Component a11y notes — every UI component carries an accessibility note.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0756 PDF tags — export magazine PDFs with a tagged reading order.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( '0760 Inclusion council — affected members as a standing voice.', 'vpg-v2' ); ?></li>
          </ol>
        </div>
        <?php
    } );
} );

/* Recompute coverage after any alt text is saved. */
add_action( 'updated_post_meta', function ( $mid, $pid, $key ) {
    if ( '_wp_attachment_image_alt' === $key ) delete_transient( 'vpg_alt_coverage' );
}, 10, 3 );
