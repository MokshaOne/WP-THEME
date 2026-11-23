<?php
/**
 * VPG v3 — Q6 · Nachzieher (round 1).
 *
 *   1006  Gallery moderation · event photos reportable, editors remove
 *   1010  District index · /bezirke/ overview of all 23 landing pages
 *   1013  Glossary autolinks · terms in tutorials link to the glossary
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 1006 · Event-gallery moderation ────────────────────────────── */
add_action( 'admin_post_vpg_gallery_report', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_gallery_report' );
    $att = get_post( (int) ( $_POST['photo'] ?? 0 ) );
    if ( $att && $att->post_type === 'attachment' && get_post_meta( $att->ID, '_vpg_event_gallery', true ) ) {
        $reports = array_filter( array_map( 'intval', (array) get_post_meta( $att->ID, '_vpg_gallery_reports', true ) ) );
        if ( ! in_array( get_current_user_id(), $reports, true ) ) {
            $reports[] = get_current_user_id();
            update_post_meta( $att->ID, '_vpg_gallery_reports', $reports );
        }
        // Editors hear about it once, on the first report.
        if ( count( $reports ) === 1 && function_exists( 'vpg_notify_user' ) ) {
            foreach ( get_users( [ 'role__in' => [ 'administrator', 'editor' ], 'fields' => 'ID' ] ) as $eid ) {
                vpg_notify_user( (int) $eid, sprintf( __( 'A gallery photo was reported: “%s”.', 'vpg-v2' ), get_the_title( $att->ID ) ),
                    get_permalink( (int) get_post_meta( $att->ID, '_vpg_event_gallery', true ) ) . '#gallery' );
            }
        }
    }
    wp_safe_redirect( ( wp_get_referer() ?: home_url() ) . '#gallery' );
    exit;
} );

add_action( 'admin_post_vpg_gallery_remove', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden', 403 );
    check_admin_referer( 'vpg_gallery_remove' );
    $att = get_post( (int) ( $_POST['photo'] ?? 0 ) );
    if ( $att && $att->post_type === 'attachment' && get_post_meta( $att->ID, '_vpg_event_gallery', true ) ) {
        wp_delete_attachment( $att->ID, true );
    }
    wp_safe_redirect( ( wp_get_referer() ?: home_url() ) . '#gallery' );
    exit;
} );

/* ─── 1010 · District index · /bezirke/ ──────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^bezirke/?$', 'index.php?vpg_districts=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_districts'; return $v; } );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_districts' ) ) return;

    $names = [
        '1010' => 'Innere Stadt', '1020' => 'Leopoldstadt', '1030' => 'Landstraße', '1040' => 'Wieden',
        '1050' => 'Margareten', '1060' => 'Mariahilf', '1070' => 'Neubau', '1080' => 'Josefstadt',
        '1090' => 'Alsergrund', '1100' => 'Favoriten', '1110' => 'Simmering', '1120' => 'Meidling',
        '1130' => 'Hietzing', '1140' => 'Penzing', '1150' => 'Rudolfsheim-Fünfhaus', '1160' => 'Ottakring',
        '1170' => 'Hernals', '1180' => 'Währing', '1190' => 'Döbling', '1200' => 'Brigittenau',
        '1210' => 'Floridsdorf', '1220' => 'Donaustadt', '1230' => 'Liesing',
    ];

    // One query, counted in PHP — 23 meta queries would hurt on shared hosting.
    $counts = array_fill_keys( array_keys( $names ), 0 );
    $spots  = get_posts( [
        'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ],
        'post_status'    => 'publish',
        'posts_per_page' => 500,
        'fields'         => 'ids',
    ] );
    foreach ( $spots as $sid ) {
        $d = get_post_meta( $sid, 'location_district', true ) ?: get_post_meta( $sid, 'shop_district', true );
        if ( $d && preg_match( '/1\d{2}0/', (string) $d, $m ) && isset( $counts[ $m[0] ] ) ) $counts[ $m[0] ]++;
    }

    add_filter( 'pre_get_document_title', fn() => __( 'Photography by district — Vienna Photo Group', 'vpg-v2' ) );

    get_header(); ?>
    <main id="vpg-main">
      <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Districts', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( '23 <em>districts</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Every Bezirk has its own page — spots, studios and shops the members have pinned there.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Pinned places', 'vpg-v2' ); ?></dt><dd><?php echo (int) array_sum( $counts ); ?></dd>
          <dt><?php esc_html_e( 'Gaps?', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Pin the first', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div></div></section>
      <section class="g-section"><div class="g-wrap">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:0 32px">
          <?php foreach ( $names as $code => $name ) : ?>
            <a href="<?php echo esc_url( home_url( '/bezirk/' . $code . '/' ) ); ?>" style="display:grid;grid-template-columns:52px 1fr auto;gap:12px;align-items:baseline;padding:12px 0;border-top:1px solid var(--g-line);text-decoration:none">
              <span class="g-display" style="font-size:17px;color:var(--g-red)"><?php echo esc_html( substr( $code, 1, 2 ) ); ?>.</span>
              <span style="font-weight:700"><?php echo esc_html( $name ); ?></span>
              <span class="g-meta" style="color:<?php echo $counts[ $code ] ? 'var(--g-mid)' : 'var(--g-red)'; ?>"><?php echo $counts[ $code ] ? (int) $counts[ $code ] : '—'; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div></section>
    </main>
    <?php get_footer();
    exit;
} );

/* ─── 1013 · Glossary autolinks · first mention links quietly ────── */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'vpg_tutorial', 'post' ] ) || ! in_the_loop() || ! is_main_query() ) return $content;
    if ( ! function_exists( 'vpg_glossary_terms' ) ) return $content;
    $terms = vpg_glossary_terms();
    if ( ! $terms ) return $content;

    $glossary = home_url( '/glossary/' );
    $linked   = [];
    // Only text nodes outside tags and outside <a>…</a> get touched.
    $chunks = preg_split( '/(<a\b.*?<\/a>|<[^>]+>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
    foreach ( $chunks as $ci => $chunk ) {
        if ( $chunk === '' || $chunk[0] === '<' ) continue;
        foreach ( $terms as $term => $def ) {
            if ( isset( $linked[ $term ] ) || count( $linked ) >= 6 ) continue;
            $pattern = '/\b(' . preg_quote( $term, '/' ) . ')\b/u';
            $new     = preg_replace(
                $pattern,
                '<a href="' . esc_url( $glossary . '#' . sanitize_title( $term ) ) . '" style="text-decoration-style:dotted" title="' . esc_attr( wp_trim_words( $def, 16 ) ) . '">$1</a>',
                $chunk, 1, $hits
            );
            if ( $hits ) { $chunk = $new; $linked[ $term ] = true; }
        }
        $chunks[ $ci ] = $chunk;
    }
    return implode( '', $chunks );
}, 30 );
