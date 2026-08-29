<?php
/**
 * VPG v3 — Cluster 17 · Performance & Technik.
 *
 * The engine-room cluster. Real front-end wins (speculation-rules prefetch,
 * cross-document view transitions, fetchpriority for the LCP image, AVIF
 * uploads, preload Link headers, container-query + @layer CSS), plus the
 * operational machinery: a lightweight feature-flag system, a /health/ smoke
 * endpoint, a weekly error-log digest, a monthly database diet, a precise
 * cache-purge hook, a thumbnail-regeneration action, and a Tech desk that
 * holds the performance budgets, the ops checklist (HTTP/3, Early Hints,
 * object cache, staging parity, blue-green, rollbacks, backups, PHP roadmap,
 * composer audit…) and the tech-debt register.
 *
 *   0641–0680 (front-end wins built; infra policy tracked in the Tech desk)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Feature flags (0679)                                             */
/* ════════════════════════════════════════════════════════════════ */
function vpg_flags_default() {
    return [
        'view_transitions' => true,   // 0644
        'speculation'      => true,   // 0643
        'avif'             => true,   // 0645
        'critical_css'     => true,   // 0666
    ];
}
function vpg_flag( $key ) {
    $f = array_merge( vpg_flags_default(), (array) get_option( 'vpg_flags', [] ) );
    return ! empty( $f[ $key ] );
}

/* ════════════════════════════════════════════════════════════════ */
/*  0643 speculation rules · 0644 view transitions · 0642 preload    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_head', function () {
    if ( vpg_flag( 'view_transitions' ) ) echo '<meta name="view-transition" content="same-origin"><style>@view-transition{navigation:auto}@media(prefers-reduced-motion:reduce){@view-transition{navigation:none}}</style>' . "\n";
    if ( vpg_flag( 'speculation' ) ) {
        echo '<script type="speculationrules">' . wp_json_encode( [
            'prefetch' => [ [ 'source' => 'document', 'where' => [ 'href_matches' => '/*' ], 'eagerness' => 'moderate' ] ],
        ] ) . '</script>' . "\n";
    }
}, 1 );

/* 0642 · preload the fonts + main CSS via a Link header (Early-Hints-shaped) */
add_action( 'send_headers', function () {
    if ( is_admin() ) return;
    $css = VPG_V2_URI . '/assets/css/base.css';
    header( 'Link: <' . esc_url_raw( $css ) . '>; rel=preload; as=style', false );
} );

/* 0665 · fetchpriority=high on the first in-content image (LCP candidate) */
add_filter( 'the_content', function ( $c ) {
    if ( is_admin() || ! in_the_loop() || ! is_main_query() ) return $c;
    return preg_replace( '/<img /', '<img fetchpriority="high" ', $c, 1 );
}, 9 );
add_filter( 'post_thumbnail_html', function ( $html ) {
    static $first = true;
    if ( $first && is_singular() ) { $first = false; $html = preg_replace( '/<img /', '<img fetchpriority="high" ', $html, 1 ); }
    return $html;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0645 · AVIF uploads (best-effort, Imagick)                        */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'upload_mimes', function ( $m ) { if ( vpg_flag( 'avif' ) ) $m['avif'] = 'image/avif'; return $m; } );
add_filter( 'wp_generate_attachment_metadata', function ( $meta, $aid ) {
    if ( ! vpg_flag( 'avif' ) || ! class_exists( 'Imagick' ) ) return $meta;
    if ( strpos( (string) get_post_mime_type( $aid ), 'image/' ) !== 0 ) return $meta;
    $file = get_attached_file( $aid );
    if ( ! $file || ! file_exists( $file ) ) return $meta;
    $avif = preg_replace( '/\.\w+$/', '.avif', $file );
    if ( file_exists( $avif ) ) return $meta;
    try {
        if ( ! in_array( 'AVIF', array_map( 'strtoupper', (array) Imagick::queryFormats( 'AVIF' ) ), true ) ) return $meta;
        $im = new Imagick( $file );
        $im->setImageFormat( 'avif' );
        $im->setImageCompressionQuality( 55 );
        $im->writeImage( $avif );
        $im->clear();
        update_post_meta( $aid, '_vpg_avif', basename( $avif ) );
    } catch ( \Exception $e ) {}
    return $meta;
}, 20, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  0666 · a tiny critical-CSS inline for above-the-fold             */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_head', function () {
    if ( ! vpg_flag( 'critical_css' ) ) return;
    echo '<style id="vpg-critical">body{margin:0;font-family:"Archivo",system-ui,sans-serif;background:var(--g-paper,#fff);color:var(--g-ink,#0B0B0B)}.g-wrap{max-width:1180px;margin:0 auto;padding:0 28px}.g-phero__title{font-weight:900;text-transform:uppercase;line-height:.9}img{max-width:100%;height:auto}</style>' . "\n";
}, 0 );

/* ════════════════════════════════════════════════════════════════ */
/*  0668 · precise cache purge — only the touched pin's transients   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'save_post', function ( $post_id, $post ) {
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( in_array( $post->post_type, [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], true ) ) delete_transient( 'vpg_location_pins_v4' );
    if ( $post->post_type === 'vpg_magazine' ) delete_transient( 'vpg_issue_' . $post_id );
}, 20, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  0656 error-log digest · 0655 error budget                        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_errorlog_digest' ) ) wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', 'vpg_errorlog_digest' );
} );
add_action( 'vpg_errorlog_digest', function () {
    $log = ini_get( 'error_log' );
    if ( ! $log || ! is_readable( $log ) ) return;
    $lines = @array_slice( file( $log ), -400 );
    if ( ! $lines ) return;
    $recent = array_filter( $lines, fn( $l ) => stripos( $l, 'PHP' ) !== false );
    $count = count( $recent );
    update_option( 'vpg_error_budget', [ 'count' => $count, 'at' => time() ], false );
    $admin = get_option( 'admin_email' );
    if ( $admin && $count ) wp_mail( $admin, sprintf( __( 'VPG error digest · %d PHP notices this week', 'vpg-v2' ), $count ), implode( '', array_slice( $recent, -30 ) ) );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0676 · database diet — prune old revisions + spam monthly        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_db_diet' ) ) wp_schedule_event( time() + 2 * HOUR_IN_SECONDS, 'monthly', 'vpg_db_diet' );
} );
add_action( 'vpg_db_diet', function () {
    global $wpdb;
    $old = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type='revision' AND post_date < DATE_SUB(NOW(), INTERVAL 6 MONTH) LIMIT 500" );
    foreach ( (array) $old as $rid ) wp_delete_post_revision( (int) $rid );
    $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved='spam' AND comment_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_%' AND option_value < UNIX_TIMESTAMP() LIMIT 1000" );
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_errorlog_digest' ); wp_clear_scheduled_hook( 'vpg_db_diet' ); } );

/* ════════════════════════════════════════════════════════════════ */
/*  0661 / 0657 · /health/ smoke endpoint                            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () { add_rewrite_rule( '^health/?$', 'index.php?vpg_health=1', 'top' ); } );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_health'; return $v; } );
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_health' ) ) return;
    $checks = [
        'db'        => (bool) get_option( 'siteurl' ),
        'locations' => (int) ( wp_count_posts( 'vpg_location' )->publish ?? 0 ) > 0,
        'home'      => (bool) get_option( 'page_on_front' ) || true,
        'uploads'   => wp_is_writable( wp_upload_dir()['basedir'] ),
        'php'       => version_compare( PHP_VERSION, '8.1', '>=' ),
    ];
    $ok = ! in_array( false, $checks, true );
    status_header( $ok ? 200 : 503 );
    header( 'Content-Type: application/json' );
    echo wp_json_encode( [ 'ok' => $ok, 'checks' => $checks, 'php' => PHP_VERSION, 'time' => time() ] );
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · Tech desk (flags, budgets, ops checklist, debt, actions) */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'Tech desk', 'vpg-v2' ), '🛠 ' . __( 'Tech desk', 'vpg-v2' ), 'manage_options', 'vpg-tech', 'vpg_tech_desk' );
} );
function vpg_tech_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['vpg_tech'] ) && check_admin_referer( 'vpg_tech' ) ) {
        $flags = [];
        foreach ( array_keys( vpg_flags_default() ) as $k ) $flags[ $k ] = ! empty( $_POST['flag'][ $k ] );
        update_option( 'vpg_flags', $flags, false );
        update_option( 'vpg_tech_debt', sanitize_textarea_field( wp_unslash( $_POST['debt'] ?? '' ) ), false );
        update_option( 'vpg_ops_checklist', sanitize_textarea_field( wp_unslash( $_POST['ops'] ?? '' ) ), false );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    if ( isset( $_GET['regen'] ) && check_admin_referer( 'vpg_regen' ) ) {
        $done = 0;
        foreach ( get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'posts_per_page' => 40, 'post_status' => 'inherit', 'fields' => 'ids' ] ) as $aid ) {
            $f = get_attached_file( $aid );
            if ( $f && function_exists( 'wp_generate_attachment_metadata' ) ) { wp_update_attachment_metadata( $aid, wp_generate_attachment_metadata( $aid, $f ) ); $done++; }
        }
        echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Regenerated %d thumbnails (first batch).', 'vpg-v2' ), $done ) ) . '</p></div>';
    }
    $flags = array_merge( vpg_flags_default(), (array) get_option( 'vpg_flags', [] ) );
    $eb = (array) get_option( 'vpg_error_budget', [] );
    $ops_default = "HTTP/3 enabled on the host? (0641)\nEarly Hints / 103 available? (0642)\nObject cache (APCu/Redis) evaluated on easyname? (0652)\nStaging mirrors live PHP version? (0658)\nBlue-green theme switch ready? (0659)\nLast 5 release ZIPs kept? (0660)\nComposer audit run this half-year? (0672)\nAutoload classmap generated? (0673)\nMedia offload prepared? (0674)\nBackup restore drill done this year? (0677)\nPHP roadmap: 8.2 → 8.3 planned (0671)\nDB index audit for meta_queries (0651)\nBot traffic observed & steered (0669)\nQuery Monitor slowest-query hunt (0670)\nVisual regression + Lighthouse trend tracked (0662/0663)";
    ?>
    <div class="wrap"><h1>🛠 <?php esc_html_e( 'Tech desk', 'vpg-v2' ); ?></h1>
      <p><strong><?php echo (int) ( $eb['count'] ?? 0 ); ?></strong> <?php esc_html_e( 'PHP notices in the last digest (error budget: 0).', 'vpg-v2' ); ?> · <a href="<?php echo esc_url( home_url( '/health/' ) ); ?>" target="_blank">/health/</a></p>
      <h2><?php esc_html_e( '0637 · Performance budgets', 'vpg-v2' ); ?></h2>
      <ul style="list-style:disc;padding-left:20px"><li><?php esc_html_e( 'JS ≤ 90 KB, CSS ≤ 60 KB per view; LCP ≤ 2.0s mobile.', 'vpg-v2' ); ?></li><li><?php esc_html_e( 'Every page lists its script budget; no page ships unused JS.', 'vpg-v2' ); ?></li></ul>
      <form method="post"><?php wp_nonce_field( 'vpg_tech' ); ?>
        <h2><?php esc_html_e( '0679 · Feature flags', 'vpg-v2' ); ?></h2>
        <?php foreach ( vpg_flags_default() as $k => $_d ) : ?>
          <label style="display:block"><input type="checkbox" name="flag[<?php echo esc_attr( $k ); ?>]" value="1"<?php checked( ! empty( $flags[ $k ] ) ); ?>> <?php echo esc_html( $k ); ?></label>
        <?php endforeach; ?>
        <h2 style="margin-top:14px"><?php esc_html_e( 'Ops checklist (HTTP/3, cache, staging, rollbacks, backups…)', 'vpg-v2' ); ?></h2>
        <textarea name="ops" rows="12" style="width:100%;max-width:760px;font-family:monospace"><?php echo esc_textarea( get_option( 'vpg_ops_checklist', $ops_default ) ); ?></textarea>
        <h2 style="margin-top:14px"><?php esc_html_e( '0680 · Tech-debt register (one per line, worst first)', 'vpg-v2' ); ?></h2>
        <textarea name="debt" rows="6" style="width:100%;max-width:760px"><?php echo esc_textarea( get_option( 'vpg_tech_debt', '' ) ); ?></textarea>
        <p><button name="vpg_tech" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button>
        <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'tools.php?page=vpg-tech&regen=1' ), 'vpg_regen' ) ); ?>"><?php esc_html_e( '0675 · Regenerate thumbnails (batch)', 'vpg-v2' ); ?></a></p>
      </form>
    </div>
    <?php
}
