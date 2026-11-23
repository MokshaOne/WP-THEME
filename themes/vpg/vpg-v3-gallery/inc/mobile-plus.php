<?php
/**
 * VPG v3 — Cluster 16 · Mobile & PWA.
 *
 * Enqueues the mobile behaviour layer, adds a thumb-zone bottom bar, a GPS
 * keep/strip choice on upload, mobile shooting tips on a spot, an offline
 * emergency contact card for a walk, a one-tap broken-view report, and the
 * mobile performance-budget + device-test-matrix docs.
 *
 *   0606 shortcuts (manifest) · 0611 thumb zones · 0612 one-hand · 0620 GPS choice
 *   0635 spot tips · 0636 emergency card · 0637 perf budget · 0638 test matrix
 *   0639 no-JS core · 0640 error report  (+ behaviours in assets/js/mobile.js)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function () {
    if ( is_admin() ) return;
    $v = fn( $r ) => file_exists( VPG_V2_DIR . $r ) ? (string) filemtime( VPG_V2_DIR . $r ) : VPG_V2_VERSION;
    wp_enqueue_style( 'vpg-mobile-extra', VPG_V2_URI . '/assets/css/mobile-extra.css', [ 'vpg-gallery' ], $v( '/assets/css/mobile-extra.css' ) );
    wp_enqueue_script( 'vpg-mobile', VPG_V2_URI . '/assets/js/mobile.js', [], $v( '/assets/js/mobile.js' ), true );
    wp_add_inline_script( 'vpg-mobile', 'window.vpgAjax=' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';', 'before' );
}, 20 );

/* 0611/0612/0633 · thumb-zone bottom bar + one-hand toggle (mobile only via CSS) */
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    $unread = is_user_logged_in() ? count( array_filter( (array) get_user_meta( get_current_user_id(), '_vpg_notifications', true ), fn( $n ) => empty( $n['read'] ) ) ) : 0;
    ?>
    <nav class="vpg-thumbbar" data-unread="<?php echo (int) $unread; ?>" aria-label="<?php esc_attr_e( 'Quick actions', 'vpg-v2' ); ?>">
      <a href="<?php echo esc_url( home_url( '/locations/' ) ); ?>">🗺<br><?php esc_html_e( 'Map', 'vpg-v2' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/?s=' ) ); ?>">🔎<br><?php esc_html_e( 'Search', 'vpg-v2' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/submit/' ) ); ?>" data-haptic>＋<br><?php esc_html_e( 'Add', 'vpg-v2' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">☰<br><?php esc_html_e( 'Me', 'vpg-v2' ); ?><?php if ( $unread ) echo ' <span style="color:var(--g-red)">●</span>'; ?></a>
      <a href="#" onclick="document.documentElement.classList.toggle('vpg-onehand');return false" title="<?php esc_attr_e( 'One-hand mode', 'vpg-v2' ); ?>">⇕</a>
    </nav>
    <?php
} );

/* 0620 · GPS keep/strip choice on the submit form */
add_action( 'wp_footer', function () {
    if ( ! is_page_template( 'templates/page-submit.php' ) ) return;
    ?>
    <script>
    (function(){var f=document.querySelector('input[type=file]');if(!f)return;
      f.addEventListener('change',function(){
        if(document.getElementById('vpg-gps-choice'))return;
        var w=document.createElement('label');w.id='vpg-gps-choice';w.style.cssText='display:block;font-size:13px;margin:8px 0';
        w.innerHTML='<input type="checkbox" name="strip_gps" value="1"> <?php echo esc_js( __( 'Strip GPS location from my photo before upload', 'vpg-v2' ) ); ?>';
        f.parentNode.appendChild(w);
      });
    })();
    </script>
    <?php
} );

/* 0635 · mobile shooting tips on a spot (appended for small screens) */
add_filter( 'the_content', function ( $c ) {
    if ( ! is_singular( [ 'vpg_location' ] ) || ! in_the_loop() || ! is_main_query() ) return $c;
    $best = get_post_meta( get_the_ID(), 'location_best_time', true );
    $tip = '<aside class="vpg-mobile-tip" style="display:none;border:1px solid var(--g-line,#E6E5E1);padding:12px 14px;margin:16px 0;font-size:13px"><strong>📱 ' . esc_html__( 'On the spot', 'vpg-v2' ) . '</strong><br>' . esc_html( $best ? sprintf( __( 'Best light here: %s. Tap-lock exposure, keep horizons level, shoot a few frames.', 'vpg-v2' ), $best ) : __( 'Tap-lock exposure, keep horizons level, shoot a few frames.', 'vpg-v2' ) ) . '</aside><style>@media(max-width:640px){.vpg-mobile-tip{display:block!important}}</style>';
    return $c . $tip;
}, 27 );

/* 0636 · offline emergency contact card on an event (cached by the SW) */
add_filter( 'the_content', function ( $c ) {
    if ( ! is_singular( 'vpg_event' ) || ! in_the_loop() || ! is_main_query() ) return $c;
    $host = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', get_the_ID() ) );
    $venue = get_post_meta( get_the_ID(), '_vpg_event_venue', true );
    $card = '<aside style="border:2px solid var(--g-ink,#0B0B0B);padding:14px 16px;margin:16px 0"><strong>🆘 ' . esc_html__( 'Walk card (works offline)', 'vpg-v2' ) . '</strong><br>' . esc_html( sprintf( __( 'Host: %1$s · Meeting point: %2$s', 'vpg-v2' ), $host, $venue ?: '—' ) ) . '<br><span style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Save this page offline (↓ button) so it’s there without signal.', 'vpg-v2' ) . '</span></aside>';
    return $c . $card;
}, 27 );

/* 0640 · one-tap broken-view report + a tiny report button in the footer */
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    echo '<button id="vpg-report-view" style="position:fixed;right:8px;bottom:70px;z-index:600;background:none;border:1px solid var(--g-line,#E6E5E1);border-radius:50%;width:32px;height:32px;font-size:14px;opacity:.5;cursor:pointer" title="' . esc_attr__( 'Something broken here?', 'vpg-v2' ) . '">⚠</button>';
} );
add_action( 'wp_ajax_vpg_view_report', 'vpg_view_report' );
add_action( 'wp_ajax_nopriv_vpg_view_report', 'vpg_view_report' );
function vpg_view_report() {
    $r = (array) get_option( 'vpg_view_reports', [] );
    $r[] = [ 'url' => esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) ), 'ua' => sanitize_text_field( wp_unslash( $_POST['ua'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_view_reports', array_slice( $r, -200 ), false );
    wp_die( 'ok' );
}

/* 0637/0638 · mobile perf budget + device test matrix (admin doc) */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'Mobile & PWA', 'vpg-v2' ), '📱 ' . __( 'Mobile & PWA', 'vpg-v2' ), 'manage_options', 'vpg-mobile', function () {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
        $reports = array_reverse( (array) get_option( 'vpg_view_reports', [] ) );
        ?>
        <div class="wrap"><h1>📱 <?php esc_html_e( 'Mobile & PWA', 'vpg-v2' ); ?></h1>
          <h2><?php esc_html_e( '0637 · Mobile performance budget', 'vpg-v2' ); ?></h2>
          <ul style="list-style:disc;padding-left:20px">
            <li><?php esc_html_e( 'HTML ≤ 40 KB · CSS ≤ 60 KB · JS ≤ 90 KB per mobile view.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Largest Contentful Paint ≤ 2.0 s on a mid-range phone / slow-4G.', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Any image over 250 KB must justify itself; hero images ship WebP.', 'vpg-v2' ); ?></li>
          </ul>
          <h2><?php esc_html_e( '0638 · Device test matrix', 'vpg-v2' ); ?></h2>
          <ol style="padding-left:20px">
            <li>iPhone SE (small, iOS Safari, notch-free safe areas)</li>
            <li>iPhone 14/15 (Dynamic Island safe areas)</li>
            <li>Pixel 6a (Chrome Android, mid-range)</li>
            <li>Samsung Galaxy A-series (Samsung Internet)</li>
            <li>An old Android on 3G (feature-phone grace: core works without JS)</li>
          </ol>
          <h2><?php esc_html_e( '0640 · Reported broken views', 'vpg-v2' ); ?></h2>
          <?php if ( $reports ) { echo '<ul>'; foreach ( array_slice( $reports, 0, 40 ) as $r ) echo '<li><a href="' . esc_url( $r['url'] ) . '">' . esc_html( $r['url'] ) . '</a> <span style="color:#888">· ' . esc_html( $r['ua'] ) . '</span></li>'; echo '</ul>'; } else echo '<p class="description">' . esc_html__( 'No reports.', 'vpg-v2' ) . '</p>'; ?>
        </div>
        <?php
    } );
} );

/* 0639 · feature-phone grace — a <noscript> path to the essentials */
add_action( 'wp_body_open', function () {
    echo '<noscript><div style="background:#0B0B0B;color:#fff;padding:8px;text-align:center;font:12px sans-serif">' . esc_html__( 'Works without JavaScript:', 'vpg-v2' ) . ' <a href="' . esc_url( home_url( '/locations/' ) ) . '" style="color:#fff">Map</a> · <a href="' . esc_url( home_url( '/magazine/' ) ) . '" style="color:#fff">Magazine</a> · <a href="' . esc_url( home_url( '/?s=' ) ) . '" style="color:#fff">Search</a></div></noscript>';
} );
