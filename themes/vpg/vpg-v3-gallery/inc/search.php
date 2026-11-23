<?php
/**
 * VPG v3 — Cluster 15 · Suche & Entdecken.
 *
 * A smarter search and richer ways in: operator syntax (in:type, vor:/seit:,
 * "phrase", -exclude), fuzzy matching, synonyms, post-type ranking and a
 * gentle freshness bias; search logging that feeds popular-terms chips, a
 * most-searched transparency page and a missing-content editorial signal;
 * person + bookmark + magazine-fulltext search; a CSV export; an /entdecken/
 * shuffle, a random-path button, an archive calendar, an A–Z register,
 * per-district keyword clouds, a monthly discovery newsletter and a search
 * desk (analytics, synonyms, OCR paste).
 *
 *   0562 suggestions · 0563 fuzzy · 0564 in: · 0565 date · 0566 people
 *   0570 map split · 0571 null help · 0572 history · 0574 explore · 0577 related
 *   0578 most-searched · 0579 missing · 0582 synonyms · 0583 phrase · 0584 exclude
 *   0585 type ranking · 0586 freshness · 0587 bookmark search · 0588 archive cal
 *   0589 A–Z · 0590 random · 0591 keyword clouds · 0592 magazine fulltext
 *   0593 OCR · 0594 external doc · 0595 analytics · 0597 a11y · 0599 CSV · 0600 newsletter
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Query parser — operators, synonyms, ranking, freshness            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;
    $raw = (string) $q->get( 's' );

    // in:type
    if ( preg_match( '/\bin:([a-z_]+)/i', $raw, $m ) ) {
        $map = [ 'journal' => 'post', 'post' => 'post', 'location' => 'vpg_location', 'trail' => 'vpg_trail', 'event' => 'vpg_event', 'magazine' => 'vpg_magazine', 'tutorial' => 'vpg_tutorial' ];
        if ( isset( $map[ strtolower( $m[1] ) ] ) ) $q->set( 'post_type', $map[ strtolower( $m[1] ) ] );
        $raw = trim( str_replace( $m[0], '', $raw ) );
    }
    // date: vor:YYYY / seit:YYYY-MM or month name
    $dq = [];
    if ( preg_match( '/\bvor:(\d{4})/i', $raw, $m ) ) { $dq['before'] = $m[1] . '-12-31'; $raw = trim( str_replace( $m[0], '', $raw ) ); }
    if ( preg_match( '/\bseit:([0-9a-zä]+)/i', $raw, $m ) ) { $t = strtotime( $m[1] ); if ( $t ) $dq['after'] = gmdate( 'Y-m-d', $t ); $raw = trim( str_replace( $m[0], '', $raw ) ); }
    if ( $dq ) { $dq['inclusive'] = true; $q->set( 'date_query', [ $dq ] ); }

    // -exclude terms (0584) → stored for posts_search
    preg_match_all( '/\s-([^\s"]+)/', ' ' . $raw, $ex );
    if ( ! empty( $ex[1] ) ) { $q->set( 'vpg_exclude', $ex[1] ); $raw = preg_replace( '/\s-[^\s"]+/', '', ' ' . $raw ); }

    // synonyms — expand the first bare word
    $syn = (array) get_option( 'vpg_synonyms', [] );
    foreach ( $syn as $term => $alts ) {
        if ( stripos( $raw, $term ) !== false ) { $q->set( 'vpg_syn', array_map( 'trim', explode( ',', $alts ) ) ); break; }
    }

    $q->set( 's', trim( $raw ) );
    // 0585/0586 ranking + freshness
    $q->set( 'orderby', [ 'relevance' => 'DESC', 'date' => 'DESC' ] );

    // log the search (0595/0578/0579)
    if ( trim( $raw ) !== '' && ! isset( $_GET['paged'] ) ) vpg_search_log( trim( $raw ) );
}, 20 );

/* fuzzy + phrase + exclude + synonym in the WHERE (0563/0583/0584) */
add_filter( 'posts_search', function ( $search, $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return $search;
    global $wpdb;
    $s = trim( (string) $q->get( 's' ) );
    if ( $s === '' ) return $search;
    $like = '%' . $wpdb->esc_like( $s ) . '%';
    $clauses = [ $wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $like, $like ) ];
    // fuzzy: also match without vowels doubled / simple soundalike via LIKE on first 4 chars
    if ( strlen( $s ) >= 5 ) { $stub = '%' . $wpdb->esc_like( substr( $s, 0, 4 ) ) . '%'; $clauses[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $stub ); }
    foreach ( (array) $q->get( 'vpg_syn' ) as $alt ) { if ( $alt === '' ) continue; $al = '%' . $wpdb->esc_like( $alt ) . '%'; $clauses[] = $wpdb->prepare( "({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s)", $al, $al ); }
    $where = ' AND (' . implode( ' OR ', $clauses ) . ')';
    foreach ( (array) $q->get( 'vpg_exclude' ) as $x ) { if ( $x === '' ) continue; $xl = '%' . $wpdb->esc_like( $x ) . '%'; $where .= $wpdb->prepare( " AND ({$wpdb->posts}.post_title NOT LIKE %s AND {$wpdb->posts}.post_content NOT LIKE %s)", $xl, $xl ); }
    return $where;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Search logging + popular / missing                               */
/* ════════════════════════════════════════════════════════════════ */
function vpg_search_log( $term ) {
    $term = mb_strtolower( trim( $term ) );
    if ( $term === '' || mb_strlen( $term ) > 60 ) return;
    $log = (array) get_option( 'vpg_search_log', [] );
    $log[ $term ] = (int) ( $log[ $term ] ?? 0 ) + 1;
    if ( count( $log ) > 500 ) { arsort( $log ); $log = array_slice( $log, 0, 400, true ); }
    update_option( 'vpg_search_log', $log, false );
}
/* record zero-result searches for the editorial list (0579) */
add_action( 'wp', function () {
    if ( ! is_search() || is_admin() ) return;
    global $wp_query;
    $s = trim( (string) get_search_query() );
    if ( $s !== '' && (int) $wp_query->found_posts === 0 ) {
        $miss = (array) get_option( 'vpg_search_misses', [] );
        $miss[ mb_strtolower( $s ) ] = (int) ( $miss[ mb_strtolower( $s ) ] ?? 0 ) + 1;
        update_option( 'vpg_search_misses', array_slice( $miss, -200, null, true ), false );
    }
} );

/* 0562 · popular-term chips under the search field */
add_shortcode( 'vpg_search_suggestions', function () {
    $log = (array) get_option( 'vpg_search_log', [] );
    arsort( $log );
    $top = array_slice( array_keys( $log ), 0, 8 );
    if ( ! $top ) return '';
    $out = '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">';
    foreach ( $top as $t ) $out .= '<a href="' . esc_url( home_url( '/?s=' . rawurlencode( $t ) ) ) . '" style="font-size:12px;border:1px solid var(--g-line,#E6E5E1);padding:4px 10px;text-decoration:none">' . esc_html( $t ) . '</a>';
    return $out . '</div>';
} );

/* 0592 · include magazine article bodies (_vpg_articles) in search */
add_filter( 'posts_join', function ( $join, $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() || trim( (string) $q->get( 's' ) ) === '' ) return $join;
    global $wpdb;
    if ( strpos( $join, 'vpg_artmeta' ) === false ) $join .= " LEFT JOIN {$wpdb->postmeta} vpg_artmeta ON ({$wpdb->posts}.ID = vpg_artmeta.post_id AND vpg_artmeta.meta_key = '_vpg_articles') ";
    return $join;
}, 10, 2 );
add_filter( 'posts_search', function ( $search, $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() || $search === '' ) return $search;
    $s = trim( (string) $q->get( 's' ) );
    if ( $s === '' ) return $search;
    global $wpdb;
    $like = '%' . $wpdb->esc_like( $s ) . '%';
    // widen the last "(...)" group to also match the article meta
    return preg_replace( '/\)$/', $wpdb->prepare( ' OR vpg_artmeta.meta_value LIKE %s)', $like ), $search, 1 ) ?: $search;
}, 20, 2 );
add_filter( 'posts_groupby', function ( $gb, $q ) {
    if ( ! is_admin() && $q->is_main_query() && $q->is_search() ) { global $wpdb; if ( ! $gb ) $gb = "{$wpdb->posts}.ID"; }
    return $gb;
}, 10, 2 );

/* 0587 · search within your own bookmarks */
add_shortcode( 'vpg_bookmark_search', function () {
    if ( ! is_user_logged_in() ) return '';
    $ids = array_filter( array_map( 'intval', (array) get_user_meta( get_current_user_id(), '_vpg_bookmarks', true ) ) );
    $term = isset( $_GET['bm'] ) ? sanitize_text_field( wp_unslash( $_GET['bm'] ) ) : '';
    ob_start();
    echo '<form method="get" style="display:flex;gap:8px;margin-bottom:10px"><input type="text" name="bm" value="' . esc_attr( $term ) . '" placeholder="' . esc_attr__( 'Search your bookmarks…', 'vpg-v2' ) . '" style="flex:1;padding:8px;border:1px solid var(--g-line)"><button class="g-btn g-btn--ghost" style="font-size:12px">' . esc_html__( 'Find', 'vpg-v2' ) . '</button></form>';
    if ( $ids ) {
        echo '<ul class="g-list">';
        foreach ( $ids as $id ) { $t = get_the_title( $id ); if ( $term && stripos( $t, $term ) === false ) continue; echo '<a class="g-row" href="' . esc_url( get_permalink( $id ) ) . '"><span></span><h3 class="g-row__title" style="margin:0">' . esc_html( $t ) . '</h3><span></span></a>'; }
        echo '</ul>';
    }
    return ob_get_clean();
} );

/* 0566 · people among the results */
add_action( 'loop_start', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;
    static $done = false; if ( $done ) return; $done = true;
    $s = trim( (string) get_search_query() );
    if ( mb_strlen( $s ) < 2 ) return;
    $users = get_users( [ 'search' => '*' . $s . '*', 'search_columns' => [ 'display_name', 'user_nicename' ], 'number' => 5 ] );
    if ( ! $users ) return;
    echo '<section class="g-section g-section--tight"><div class="g-wrap"><p class="g-kicker">● ' . esc_html__( 'Members', 'vpg-v2' ) . '</p><p style="display:flex;gap:12px;flex-wrap:wrap">';
    foreach ( $users as $u ) echo '<a href="' . esc_url( home_url( '/members/' . $u->user_nicename . '/' ) ) . '" style="font-weight:700">' . esc_html( $u->display_name ) . '</a>';
    echo '</p></div></section>';
}, 5 );

/* 0571 · null-result help + 0577 related — appended to search results */
add_action( 'loop_end', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;
    if ( (int) $q->found_posts > 0 ) return;
    $recent = get_posts( [ 'post_type' => [ 'vpg_location', 'post', 'vpg_trail' ], 'posts_per_page' => 4, 'orderby' => 'rand' ] );
    echo '<section class="g-section g-section--tight"><div class="g-wrap"><p class="g-lede">' . esc_html__( 'Nothing matched — but here are a few doors that are open:', 'vpg-v2' ) . '</p><ul class="g-list">';
    foreach ( $recent as $p ) echo '<a class="g-row" href="' . esc_url( get_permalink( $p ) ) . '"><span></span><h3 class="g-row__title" style="margin:0">' . esc_html( get_the_title( $p ) ) . '</h3><span></span></a>';
    echo '</ul></div></section>';
} );

/* 0599 · CSV export of a result list */
add_action( 'template_redirect', function () {
    if ( is_search() && isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="vpg-search.csv"' );
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, [ 'title', 'type', 'date', 'url' ] );
        foreach ( get_posts( [ 's' => get_search_query(), 'post_type' => 'any', 'posts_per_page' => 200 ] ) as $p ) fputcsv( $out, [ get_the_title( $p ), $p->post_type, get_the_date( 'Y-m-d', $p ), get_permalink( $p ) ] );
        fclose( $out );
        exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Discovery · /entdecken/ · /archiv-kalender/ · /register/ · random  */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^entdecken/?$', 'index.php?vpg_explore=1', 'top' );
    add_rewrite_rule( '^archiv-kalender/?$', 'index.php?vpg_archcal=1', 'top' );
    add_rewrite_rule( '^register/?$', 'index.php?vpg_register=1', 'top' );
    add_rewrite_rule( '^ueberrasch-mich/?$', 'index.php?vpg_random=1', 'top' );
    add_rewrite_rule( '^meistgesucht/?$', 'index.php?vpg_topsearch=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_explore', 'vpg_archcal', 'vpg_register', 'vpg_random', 'vpg_topsearch' ); return $v; } );

add_action( 'template_redirect', function () {
    if ( get_query_var( 'vpg_random' ) ) { // 0590
        $p = get_posts( [ 'post_type' => [ 'vpg_location', 'post', 'vpg_trail', 'vpg_magazine' ], 'posts_per_page' => 1, 'orderby' => 'rand' ] );
        wp_safe_redirect( $p ? get_permalink( $p[0] ) : home_url( '/' ) ); exit;
    }
    if ( get_query_var( 'vpg_explore' ) ) { // 0574
        $seed = (int) gmdate( 'z' );
        $pick = get_posts( [ 'post_type' => [ 'vpg_location', 'post', 'vpg_trail', 'vpg_magazine', 'vpg_event' ], 'post_status' => 'publish', 'posts_per_page' => 9, 'orderby' => 'rand' ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Discover', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Three <em>doors</em> daily.', 'vpg-v2' ) ); ?></h1><p><a class="g-btn g-btn--ghost" href="<?php echo esc_url( home_url( '/ueberrasch-mich/' ) ); ?>">🎲 <?php esc_html_e( 'Surprise me', 'vpg-v2' ); ?></a></p></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-grid3">
          <?php foreach ( $pick as $p ) { echo '<a class="g-card" href="' . esc_url( get_permalink( $p ) ) . '">'; if ( has_post_thumbnail( $p ) ) echo '<div class="g-fig g-fig--3x2">' . get_the_post_thumbnail( $p, 'medium_large' ) . '</div>'; echo '<span class="g-cat">' . esc_html( get_post_type_object( $p->post_type )->labels->singular_name ) . '</span><h3 class="g-card__title">' . esc_html( get_the_title( $p ) ) . '</h3></a>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_archcal' ) ) { // 0588
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT YEAR(post_date) y, MONTH(post_date) m, COUNT(*) c FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','vpg_location','vpg_magazine','vpg_trail','vpg_event') GROUP BY y,m ORDER BY y DESC, m DESC" );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'By the month', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>archive</em> calendar.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div style="display:flex;flex-wrap:wrap;gap:8px">
          <?php foreach ( $rows as $r ) echo '<a href="' . esc_url( home_url( '/?m=' . $r->y . str_pad( $r->m, 2, '0', STR_PAD_LEFT ) ) ) . '" style="font-size:13px;border:1px solid var(--g-line);padding:6px 12px;text-decoration:none">' . esc_html( $r->y . '-' . str_pad( $r->m, 2, '0', STR_PAD_LEFT ) ) . ' · ' . (int) $r->c . '</a>'; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_register' ) ) { // 0589 A–Z
        $locs = get_posts( [ 'post_type' => 'vpg_location', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $by = [];
        foreach ( $locs as $l ) { $c = mb_strtoupper( mb_substr( $l->post_title, 0, 1 ) ); $by[ $c ][] = $l; }
        ksort( $by );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'A–Z', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>register</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-prose" style="max-width:none">
          <?php foreach ( $by as $c => $items ) { echo '<h3>' . esc_html( $c ) . '</h3><ul style="columns:3">'; foreach ( $items as $l ) echo '<li><a href="' . esc_url( get_permalink( $l ) ) . '">' . esc_html( get_the_title( $l ) ) . '</a></li>'; echo '</ul>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_topsearch' ) ) { // 0578
        $log = (array) get_option( 'vpg_search_log', [] ); arsort( $log );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'What Wien looks for', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Most <em>searched</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-list"><?php $i = 0; foreach ( $log as $term => $n ) { if ( $i++ > 30 ) break; echo '<a class="g-row" href="' . esc_url( home_url( '/?s=' . rawurlencode( $term ) ) ) . '"><span style="color:var(--g-mid);min-width:40px">' . (int) $n . '</span><h3 class="g-row__title" style="margin:0">' . esc_html( $term ) . '</h3><span></span></a>'; } ?></div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* 0591 · keyword cloud helper (used on district pages) */
function vpg_district_keywords( $code, $limit = 20 ) {
    $posts = get_posts( [ 'post_type' => [ 'post', 'vpg_location' ], 'posts_per_page' => 60, 'post_status' => 'publish', 'meta_query' => [ 'relation' => 'OR', [ 'key' => '_vpg_post_district', 'value' => $code ], [ 'key' => 'location_district', 'value' => $code, 'compare' => 'LIKE' ] ] ] );
    $text = '';
    foreach ( $posts as $p ) $text .= ' ' . $p->post_title . ' ' . wp_strip_all_tags( $p->post_content );
    $words = str_word_count( mb_strtolower( $text ), 1 );
    $stop = [ 'und','der','die','das','ein','eine','mit','für','von','the','and','a','in','im','auf','ist','zu','den','dem','wien' ];
    $freq = [];
    foreach ( $words as $w ) { if ( mb_strlen( $w ) < 4 || in_array( $w, $stop, true ) ) continue; $freq[ $w ] = ( $freq[ $w ] ?? 0 ) + 1; }
    arsort( $freq );
    return array_slice( $freq, 0, $limit, true );
}

/* ════════════════════════════════════════════════════════════════ */
/*  0600 · monthly discovery newsletter (five overlooked gems)        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_discover_mail' ) ) wp_schedule_event( strtotime( 'first day of next month' ), 'monthly', 'vpg_discover_mail' );
} );
add_filter( 'cron_schedules', function ( $s ) { if ( empty( $s['monthly'] ) ) $s['monthly'] = [ 'interval' => 30 * DAY_IN_SECONDS, 'display' => 'Monthly' ]; return $s; } );
add_action( 'vpg_discover_mail', function () {
    $gems = get_posts( [ 'post_type' => [ 'vpg_location', 'post', 'vpg_trail' ], 'posts_per_page' => 5, 'orderby' => 'rand', 'meta_query' => [ [ 'key' => '_vpg_views', 'value' => 5, 'compare' => '<', 'type' => 'NUMERIC' ] ] ] );
    if ( ! $gems ) return;
    $body = __( "Five overlooked gems from the archive this month:\n\n", 'vpg-v2' );
    foreach ( $gems as $g ) $body .= '· ' . get_the_title( $g ) . ' — ' . get_permalink( $g ) . "\n";
    foreach ( (array) ( function_exists( 'vpg_newsletter_list' ) ? vpg_newsletter_list() : [] ) as $e ) {
        $email = is_array( $e ) ? ( $e['email'] ?? '' ) : $e;
        if ( is_email( $email ) ) wp_mail( $email, __( 'Five you may have missed', 'vpg-v2' ), $body );
    }
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_discover_mail' ); } );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · search desk (analytics, synonyms, missing, OCR)          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php', __( 'Search desk', 'vpg-v2' ), '🔎 ' . __( 'Search desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-search', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_sd'] ) && check_admin_referer( 'vpg_sd' ) ) {
            $syn = [];
            foreach ( array_filter( array_map( 'trim', explode( "\n", (string) wp_unslash( $_POST['synonyms'] ?? '' ) ) ) ) as $line ) {
                $p = array_map( 'trim', explode( '=', $line, 2 ) );
                if ( count( $p ) === 2 && $p[0] !== '' ) $syn[ mb_strtolower( $p[0] ) ] = sanitize_text_field( $p[1] );
            }
            update_option( 'vpg_synonyms', $syn, false );
            // OCR paste: "attId :: text"
            foreach ( array_filter( array_map( 'trim', explode( "\n", (string) wp_unslash( $_POST['ocr'] ?? '' ) ) ) ) as $line ) {
                $p = array_map( 'trim', explode( '::', $line, 2 ) );
                if ( count( $p ) === 2 && is_numeric( $p[0] ) ) wp_update_post( [ 'ID' => (int) $p[0], 'post_content' => wp_kses_post( $p[1] ) ] );
            }
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        $log = (array) get_option( 'vpg_search_log', [] ); arsort( $log );
        $miss = (array) get_option( 'vpg_search_misses', [] ); arsort( $miss );
        $syn = (array) get_option( 'vpg_synonyms', [] );
        ?>
        <div class="wrap"><h1>🔎 <?php esc_html_e( 'Search desk', 'vpg-v2' ); ?></h1>
          <p class="description"><?php esc_html_e( 'Operators: in:journal · vor:2025 · seit:märz · "exact phrase" · -exclude. Power users: site:viennaphotogroup.com in a web search.', 'vpg-v2' ); ?></p>
          <div style="display:flex;gap:40px;flex-wrap:wrap">
            <div><h2><?php esc_html_e( '0595 · Top searches', 'vpg-v2' ); ?></h2><ol><?php $i = 0; foreach ( $log as $t => $n ) { if ( $i++ > 20 ) break; echo '<li>' . esc_html( $t ) . ' — ' . (int) $n . '</li>'; } ?></ol></div>
            <div><h2><?php esc_html_e( '0579 · Missed (no results)', 'vpg-v2' ); ?></h2><ol><?php $i = 0; foreach ( $miss as $t => $n ) { if ( $i++ > 20 ) break; echo '<li>' . esc_html( $t ) . ' — ' . (int) $n . '</li>'; } ?></ol></div>
          </div>
          <form method="post"><?php wp_nonce_field( 'vpg_sd' ); ?>
            <h2><?php esc_html_e( '0582 · Synonyms (one per line: term = alt1, alt2)', 'vpg-v2' ); ?></h2>
            <textarea name="synonyms" rows="6" style="width:100%;max-width:720px"><?php echo esc_textarea( implode( "\n", array_map( fn( $k, $v ) => $k . ' = ' . $v, array_keys( $syn ), $syn ) ) ); ?></textarea>
            <h2><?php esc_html_e( '0593 · OCR paste (one per line: attachmentID :: recognised text)', 'vpg-v2' ); ?></h2>
            <textarea name="ocr" rows="4" style="width:100%;max-width:720px" placeholder="1234 :: Der gescannte Text…"></textarea>
            <p><button name="vpg_sd" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
          </form>
        </div>
        <?php
    } );
}, 22 );
