<?php
/**
 * VPG v3 — Q4 · power features.
 *
 *   0561/0580  Live search + command palette (one component, ⌘K)
 *   0573       Saved searches · daily check, notification on new matches
 *   0241       Series · taxonomy + part navigation on posts/tutorials
 *   0681       District landing pages · /bezirk/1070/
 *   0703       Image sitemap · /image-sitemap.xml
 *   0288       Photo permalinks · attachment pages re-enabled, styled
 *   0083/0081  Trail GPX export + time/distance estimate
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 0561 · Live search endpoint · public content only ─────────── */
add_action( 'wp_ajax_vpg_live_search', 'vpg_live_search' );
add_action( 'wp_ajax_nopriv_vpg_live_search', 'vpg_live_search' );
function vpg_live_search() {
    $q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
    if ( mb_strlen( $q ) < 2 ) wp_send_json_success( [] );

    $query = new WP_Query( [
        's'              => $q,
        'post_type'      => [ 'post', 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'vpg_event', 'vpg_trail', 'vpg_magazine', 'vpg_project', 'vpg_wall', 'vpg_collection' ],
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'no_found_rows'  => true,
    ] );
    $out = [];
    foreach ( $query->posts as $p ) {
        $obj   = get_post_type_object( $p->post_type );
        $out[] = [
            'id'    => $p->ID,
            'title' => html_entity_decode( get_the_title( $p ), ENT_QUOTES ),
            'type'  => $obj ? $obj->labels->singular_name : '',
            'url'   => get_permalink( $p ),
        ];
    }
    wp_send_json_success( $out );
}

/* ─── 0573 · Saved searches · watch a term, hear about new matches ─ */
add_action( 'admin_post_vpg_save_search', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_save_search' );
    $term = mb_substr( sanitize_text_field( wp_unslash( $_POST['term'] ?? '' ) ), 0, 60 );
    if ( $term !== '' ) {
        $uid   = get_current_user_id();
        $saved = (array) get_user_meta( $uid, '_vpg_saved_searches', true );
        $saved = array_filter( $saved, fn( $r ) => is_array( $r ) && ( $r['term'] ?? '' ) !== $term );
        $saved[] = [ 'term' => $term, 'since' => time() ];
        update_user_meta( $uid, '_vpg_saved_searches', array_slice( array_values( $saved ), -5 ) );
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', 'search_saved', wp_get_referer() ?: home_url() ) );
    exit;
} );

add_action( 'admin_post_vpg_drop_search', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_drop_search' );
    $term  = sanitize_text_field( wp_unslash( $_GET['term'] ?? '' ) );
    $uid   = get_current_user_id();
    $saved = (array) get_user_meta( $uid, '_vpg_saved_searches', true );
    update_user_meta( $uid, '_vpg_saved_searches', array_values( array_filter( $saved, fn( $r ) => ( $r['term'] ?? '' ) !== $term ) ) );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) );
    exit;
} );

add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_check_saved_searches' ) ) {
        wp_schedule_event( strtotime( 'tomorrow 08:30' ), 'daily', 'vpg_check_saved_searches' );
    }
} );

add_action( 'vpg_check_saved_searches', function () {
    $users = get_users( [ 'meta_key' => '_vpg_saved_searches', 'fields' => 'ID' ] );
    foreach ( $users as $uid ) {
        $saved   = (array) get_user_meta( $uid, '_vpg_saved_searches', true );
        $changed = false;
        foreach ( $saved as &$row ) {
            if ( ! is_array( $row ) || empty( $row['term'] ) ) continue;
            $fresh = new WP_Query( [
                's'              => $row['term'],
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'no_found_rows'  => true,
                'date_query'     => [ [ 'after' => gmdate( 'Y-m-d H:i:s', (int) $row['since'] ) ] ],
            ] );
            if ( $fresh->posts ) {
                vpg_notify_user( $uid,
                    sprintf( __( 'New on the site for your saved search “%1$s”: %2$s', 'vpg-v2' ), $row['term'], get_the_title( $fresh->posts[0] ) ),
                    get_permalink( $fresh->posts[0] )
                );
                $row['since'] = time();
                $changed      = true;
            }
        }
        unset( $row );
        if ( $changed ) update_user_meta( $uid, '_vpg_saved_searches', $saved );
    }
} );

/* ─── 0241 · Series · a taxonomy and a part navigator ───────────── */
add_action( 'init', function () {
    register_taxonomy( 'vpg_series', [ 'post', 'vpg_tutorial' ], [
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical'      => false,
        'rewrite'           => [ 'slug' => 'series' ],
        'labels'            => [
            'name'          => __( 'Series', 'vpg-v2' ),
            'singular_name' => __( 'Series', 'vpg-v2' ),
        ],
    ] );
}, 11 );

add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'post', 'vpg_tutorial' ] ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $terms = get_the_terms( get_the_ID(), 'vpg_series' );
    if ( ! $terms || is_wp_error( $terms ) ) return $content;
    $series = $terms[0];

    $parts = get_posts( [
        'post_type'      => [ 'post', 'vpg_tutorial' ],
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'tax_query'      => [ [ 'taxonomy' => 'vpg_series', 'terms' => $series->term_id ] ],
    ] );
    if ( count( $parts ) < 2 ) return $content;

    $ids = wp_list_pluck( $parts, 'ID' );
    $pos = array_search( get_the_ID(), $ids, true );

    ob_start(); ?>
    <div style="margin:0 0 28px;border:1px solid var(--g-ink,#0B0B0B);padding:14px 20px">
        <p style="margin:0;font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase"><span style="color:var(--g-red,#E5341F)">●</span> <?php
            printf( esc_html__( '%1$s · part %2$d of %3$d', 'vpg-v2' ), esc_html( $series->name ), (int) $pos + 1, count( $parts ) );
        ?></p>
        <p style="margin:8px 0 0;display:flex;gap:18px;flex-wrap:wrap;font-size:13px;font-weight:700">
            <?php if ( $pos > 0 ) : ?><a href="<?php echo esc_url( get_permalink( $ids[ $pos - 1 ] ) ); ?>">← <?php echo esc_html( get_the_title( $ids[ $pos - 1 ] ) ); ?></a><?php endif; ?>
            <?php if ( $pos < count( $ids ) - 1 ) : ?><a href="<?php echo esc_url( get_permalink( $ids[ $pos + 1 ] ) ); ?>" style="margin-left:auto"><?php echo esc_html( get_the_title( $ids[ $pos + 1 ] ) ); ?> →</a><?php endif; ?>
        </p>
    </div>
    <?php
    return ob_get_clean() . $content;
}, 8 );

/* ─── 0681 · District landing pages · /bezirk/{code}/ ───────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^bezirk/(1\d{2}0)/?$', 'index.php?vpg_district=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_district'; return $v; } );

add_action( 'template_redirect', function () {
    $code = get_query_var( 'vpg_district' );
    if ( ! $code ) return;

    $names = [
        '1010' => 'Innere Stadt', '1020' => 'Leopoldstadt', '1030' => 'Landstraße', '1040' => 'Wieden',
        '1050' => 'Margareten', '1060' => 'Mariahilf', '1070' => 'Neubau', '1080' => 'Josefstadt',
        '1090' => 'Alsergrund', '1100' => 'Favoriten', '1110' => 'Simmering', '1120' => 'Meidling',
        '1130' => 'Hietzing', '1140' => 'Penzing', '1150' => 'Rudolfsheim-Fünfhaus', '1160' => 'Ottakring',
        '1170' => 'Hernals', '1180' => 'Währing', '1190' => 'Döbling', '1200' => 'Brigittenau',
        '1210' => 'Floridsdorf', '1220' => 'Donaustadt', '1230' => 'Liesing',
    ];
    $name = $names[ $code ] ?? '';
    if ( ! $name ) { global $wp_query; $wp_query->set_404(); status_header( 404 ); return; }

    $spots = get_posts( [
        'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ],
        'post_status'    => 'publish',
        'posts_per_page' => 60,
        'meta_query'     => [ 'relation' => 'OR',
            [ 'key' => 'location_district', 'value' => $code, 'compare' => 'LIKE' ],
            [ 'key' => 'shop_district', 'value' => $code, 'compare' => 'LIKE' ],
        ],
    ] );

    add_filter( 'pre_get_document_title', fn() => sprintf( __( 'Photography in %1$s · %2$s — Vienna Photo Group', 'vpg-v2' ), $code, $name ) );

    get_header(); ?>
    <main id="vpg-main">
      <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:16px">● <?php printf( esc_html__( 'District %s', 'vpg-v2' ), esc_html( $code ) ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo esc_html( $name ); ?><span style="color:var(--g-red)">.</span></h1>
          <p class="g-lede g-phero__lede"><?php printf( esc_html( _n( '%d member-curated place to photograph in the %s.', '%d member-curated places to photograph in the %s.', count( $spots ), 'vpg-v2' ) ), count( $spots ), esc_html( $code ) ); ?></p>
          <?php /* 0066 · editable district character text */
          $profile = function_exists( 'vpg_district_text' ) ? vpg_district_text( $code ) : '';
          if ( $profile ) : ?><p style="font-size:15px;line-height:1.6;color:var(--g-mid);max-width:44ch"><?php echo esc_html( $profile ); ?></p><?php endif; ?>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'On the map', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( add_query_arg( 'district', $code, get_post_type_archive_link( 'vpg_location' ) ) ); ?>"><?php esc_html_e( 'Open filtered map', 'vpg-v2' ); ?></a></dd>
          <dt><?php esc_html_e( 'Know a spot?', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Add it', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div></div></section>
      <section class="g-section"><div class="g-wrap">
        <?php if ( $spots ) : ?>
        <div class="g-grid3">
          <?php foreach ( $spots as $sp ) : ?>
            <a class="g-card" href="<?php echo esc_url( get_permalink( $sp ) ); ?>">
              <?php if ( has_post_thumbnail( $sp ) ) : ?><div class="g-fig g-fig--3x2"><?php echo get_the_post_thumbnail( $sp, 'medium_large' ); ?></div><?php endif; ?>
              <span class="g-cat"><?php echo esc_html( get_post_type_object( $sp->post_type )->labels->singular_name ); ?></span>
              <h3 class="g-card__title"><?php echo esc_html( $sp->post_title ); ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
          <p class="g-lede"><?php esc_html_e( 'Nothing pinned here yet — this district is waiting for its first member find.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </div></section>

      <?php // 0243 · district long-reads from the journal
      $vpg_reads = function_exists( 'vpg_district_reads' ) ? vpg_district_reads( $code ) : [];
      if ( $vpg_reads ) : ?>
      <section class="g-section g-section--tight"><div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Long-reads from this district', 'vpg-v2' ); ?></p>
        <div class="g-grid3">
          <?php foreach ( $vpg_reads as $r ) : ?>
            <a class="g-card" href="<?php echo esc_url( get_permalink( $r ) ); ?>">
              <?php if ( has_post_thumbnail( $r ) ) : ?><div class="g-fig g-fig--3x2"><?php echo get_the_post_thumbnail( $r, 'medium_large' ); ?></div><?php endif; ?>
              <h3 class="g-card__title"><?php echo esc_html( get_the_title( $r ) ); ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div></section>
      <?php endif; ?>
    </main>
    <?php get_footer();
    exit;
} );

/* ─── 0703 · Image sitemap · /image-sitemap.xml ─────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^image-sitemap\.xml$', 'index.php?vpg_imgmap=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_imgmap'; return $v; } );

add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_imgmap' ) ) return;
    header( 'Content-Type: application/xml; charset=utf-8' );
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    $posts = get_posts( [ 'post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => 500, 'meta_key' => '_thumbnail_id' ] );
    foreach ( $posts as $p ) {
        $img = wp_get_attachment_image_url( get_post_thumbnail_id( $p ), 'large' );
        if ( ! $img ) continue;
        printf( "<url><loc>%s</loc><image:image><image:loc>%s</image:loc><image:title>%s</image:title></image:image></url>\n",
            esc_url( get_permalink( $p ) ), esc_url( $img ), esc_xml( get_the_title( $p ) ) );
    }
    echo '</urlset>';
    exit;
} );

add_filter( 'robots_txt', function ( $output ) {
    return $output . "\nSitemap: " . home_url( '/image-sitemap.xml' ) . "\n";
}, 11 );

/* ─── 0288 · Photo permalinks · attachment pages, styled ────────── */
add_filter( 'wp_attachment_pages_enabled', '__return_true' );

/* ─── 0081/0083 · Trail distance, time and GPX ──────────────────── */
function vpg_trail_geo( $trail_id ) {
    $stops  = function_exists( 'vpg_trail_stops' ) ? vpg_trail_stops( $trail_id ) : [];
    $keys   = [ 'vpg_location' => 'location_lat', 'vpg_studio' => 'studio_lat', 'vpg_shop' => 'shop_lat' ];
    $points = [];
    foreach ( $stops as $sid ) {
        $k   = $keys[ get_post_type( $sid ) ] ?? 'location_lat';
        $lat = (float) get_post_meta( $sid, $k, true );
        $lng = (float) get_post_meta( $sid, str_replace( '_lat', '_lng', $k ), true );
        if ( $lat && $lng ) $points[] = [ 'id' => $sid, 'lat' => $lat, 'lng' => $lng, 'title' => get_the_title( $sid ) ];
    }
    $dist = 0.0;
    for ( $i = 1; $i < count( $points ); $i++ ) {
        $a = $points[ $i - 1 ]; $b = $points[ $i ];
        $dlat = deg2rad( $b['lat'] - $a['lat'] ); $dlng = deg2rad( $b['lng'] - $a['lng'] );
        $h    = sin( $dlat / 2 ) ** 2 + cos( deg2rad( $a['lat'] ) ) * cos( deg2rad( $b['lat'] ) ) * sin( $dlng / 2 ) ** 2;
        $dist += 6371 * 2 * asin( min( 1, sqrt( $h ) ) );
    }
    // Walking at 4.5 km/h plus ten photo-minutes per stop
    $minutes = (int) round( $dist / 4.5 * 60 + count( $points ) * 10 );
    return [ 'points' => $points, 'km' => round( $dist, 1 ), 'minutes' => $minutes ];
}

add_action( 'admin_post_nopriv_vpg_trail_gpx', 'vpg_trail_gpx' );
add_action( 'admin_post_vpg_trail_gpx',        'vpg_trail_gpx' );
function vpg_trail_gpx() {
    $trail = get_post( (int) ( $_GET['trail'] ?? 0 ) );
    if ( ! $trail || $trail->post_type !== 'vpg_trail' || $trail->post_status !== 'publish' ) wp_die( 'Not found', 404 );
    $geo = vpg_trail_geo( $trail->ID );
    nocache_headers();
    header( 'Content-Type: application/gpx+xml; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="vpg-trail-' . $trail->ID . '.gpx"' );
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<gpx version="1.1" creator="Vienna Photo Group" xmlns="http://www.topografix.com/GPX/1/1">' . "\n";
    printf( "<trk><name>%s</name><trkseg>\n", esc_xml( $trail->post_title ) );
    foreach ( $geo['points'] as $pt ) {
        printf( "<trkpt lat=\"%F\" lon=\"%F\"><name>%s</name></trkpt>\n", $pt['lat'], $pt['lng'], esc_xml( $pt['title'] ) );
    }
    echo "</trkseg></trk>\n</gpx>";
    exit;
}

add_action( 'wp_footer', function () {
    if ( sanitize_key( $_GET['vpg_status'] ?? '' ) !== 'search_saved' ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-ss-toast"><?php esc_html_e( 'Search saved — we’ll notify you about new matches.', 'vpg-v2' ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-ss-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );
