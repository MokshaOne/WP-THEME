<?php
/**
 * VPG v3 — Cluster 06 · Print & Physisches.
 *
 * The collective leaves the screen. A print studio of browser-printable
 * generators (posters, postcards, stickers, contact sheets, business cards,
 * exhibition labels, a calendar, a leporello, a broadsheet, a mailer, an
 * archive-box wrap), an editor-curated print knowledge base (POD, framing,
 * paper profiles, labs, ISBN, legal deposit, insurance, error culture…),
 * interest lists (group order, subscription, print swap, flea market, scan
 * day, touring show), a physical-programme board and a photo-walls map.
 *
 *   0203 poster · 0204 postcards · 0205 stickers · 0206 exhibition concept
 *   0207 POD · 0208 contact sheet · 0209 framing · 0210 riso · 0211 broadsheet
 *   0212 photobook templates · 0213 gallery evenings · 0214 shop windows
 *   0215 group order · 0216 business card · 0217 stamp · 0218 archive box
 *   0219 labels · 0220 photo walls · 0221 bookbinding · 0222 paper profiles
 *   0223 touring show · 0224 culture column · 0225 flea market · 0226 calendar
 *   0227 leporello · 0228 lab contacts · 0229 print swap · 0230 cyanotype
 *   0231 subscription list · 0232 packaging · 0233 ISBN · 0234 legal deposit
 *   0235 insurance runbook · 0236 QR in print · 0237 error culture
 *   0238 book launch · 0239 catalogue co-op · 0240 archive scan day
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Routing — one hub, one knowledge page, one generator dispatcher   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^print-studio/?$',            'index.php?vpg_printstudio=1', 'top' );
    add_rewrite_rule( '^print-wissen/?$',            'index.php?vpg_printkb=1', 'top' );
    add_rewrite_rule( '^print/([a-z-]+)/?$',         'index.php?vpg_print_kind=$matches[1]', 'top' );
    add_rewrite_rule( '^print/([a-z-]+)/([^/]+)/?$', 'index.php?vpg_print_kind=$matches[1]&vpg_print_arg=$matches[2]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) {
    array_push( $v, 'vpg_printstudio', 'vpg_printkb', 'vpg_print_kind', 'vpg_print_arg' );
    return $v;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Shared printable shell (White-Museum look, window.print, QR)      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_print_shell( $title, $inner, $qr_url = '', $page = 'A4' ) {
    nocache_headers();
    header( 'Content-Type: text/html; charset=utf-8' );
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo esc_html( $title ); ?> · VPG Print</title>
    <style>
      *{box-sizing:border-box;margin:0;padding:0}
      @page{size:<?php echo esc_html( $page ); ?>;margin:12mm}
      body{font-family:'Helvetica Neue',Arial,sans-serif;color:#0B0B0B;background:#e8e7e3;padding:20px}
      .sheet{background:#fff;max-width:900px;margin:0 auto 20px;padding:18mm;box-shadow:0 2px 20px rgba(0,0,0,.15)}
      .k{font-size:10px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:#E5341F}
      h1{font-size:34px;font-weight:900;text-transform:uppercase;line-height:1;margin:8px 0}
      .stamp{position:fixed;right:16px;top:16px;border:2px solid #E5341F;color:#E5341F;font:700 9px/1.2 Arial;letter-spacing:.12em;text-transform:uppercase;padding:6px 8px;transform:rotate(-6deg);text-align:center}
      .qr{margin-top:14px}
      .bar{position:fixed;left:0;right:0;bottom:0;background:#0B0B0B;color:#fff;text-align:center;padding:10px}
      .bar button{border:1px solid #fff;background:none;color:#fff;padding:8px 18px;font-weight:700;cursor:pointer}
      @media print{.bar,.stamp{display:none}body{background:#fff;padding:0}.sheet{box-shadow:none;margin:0;max-width:none;padding:0}}
    </style></head><body>
    <div class="stamp">Vienna<br>Photo<br>Group<br>· PRINT ·</div>
    <?php echo $inner; ?>
    <?php if ( $qr_url ) : ?>
      <div class="sheet qr" style="text-align:center"><p class="k"><?php esc_html_e( '0236 · Read the online version', 'vpg-v2' ); ?></p><div id="qr" style="margin:10px auto"></div><p style="font:12px monospace;color:#6A6A6A;word-break:break-all"><?php echo esc_html( $qr_url ); ?></p></div>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
      <script>new QRCode(document.getElementById('qr'),{text:<?php echo wp_json_encode( $qr_url ); ?>,width:130,height:130,colorDark:'#0B0B0B',colorLight:'#fff'});</script>
    <?php endif; ?>
    <div class="bar"><button onclick="window.print()"><?php esc_html_e( 'Print / Save as PDF', 'vpg-v2' ); ?></button></div>
    </body></html><?php
    exit;
}

/* POTW winners across a year (top-voted photo per stored week option) */
function vpg_potw_winners( $year = 0 ) {
    global $wpdb;
    $year = $year ?: (int) wp_date( 'o' );
    $rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", 'vpg_potw_' . $year . '-W%' ) );
    $winners = [];
    foreach ( $rows as $r ) {
        $v = maybe_unserialize( $r->option_value );
        if ( ! is_array( $v ) || ! $v ) continue;
        arsort( $v );
        $pid = (int) array_key_first( $v );
        if ( wp_attachment_is_image( $pid ) ) $winners[ $r->option_name ] = $pid;
    }
    ksort( $winners );
    return array_values( $winners );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Generators                                                        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    $kind = sanitize_key( get_query_var( 'vpg_print_kind' ) );
    if ( ! $kind ) return;
    $arg  = sanitize_text_field( get_query_var( 'vpg_print_arg' ) );

    switch ( $kind ) {

        case 'poster': // 0203 · a plate as a print-ready poster
            $img = wp_get_attachment_image_url( (int) $arg, 'full' );
            if ( ! $img ) $img = get_the_post_thumbnail_url( (int) $arg, 'full' );
            if ( ! $img ) { status_header( 404 ); wp_die( 'No image', 404 ); }
            $cap = get_post( (int) $arg ) ? get_the_title( (int) $arg ) : '';
            $inner = '<div class="sheet" style="padding:0"><img src="' . esc_url( $img ) . '" alt="" style="width:100%;display:block"><div style="padding:14mm"><p class="k">Vienna Photo Group</p>' . ( $cap ? '<h1 style="font-size:26px">' . esc_html( $cap ) . '</h1>' : '' ) . '</div></div>';
            vpg_print_shell( __( 'Poster', 'vpg-v2' ), $inner, home_url( '/' ), 'A3' );

        case 'sticker': // 0205 · the red square as a sticker sheet
            $cell = '<div style="border:1px dashed #ccc;aspect-ratio:1;display:flex;align-items:center;justify-content:center"><div style="width:60%;height:60%;background:#E5341F"></div></div>';
            $grid = '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">' . str_repeat( $cell, 20 ) . '</div>';
            vpg_print_shell( __( 'Sticker sheet', 'vpg-v2' ), '<div class="sheet"><p class="k">Vienna Photo Group · 0205</p><h1>' . esc_html__( 'Stickers', 'vpg-v2' ) . '</h1><p style="margin:8px 0 16px;color:#6A6A6A">' . esc_html__( 'The red square for your camera cap. Cut along the dashes.', 'vpg-v2' ) . '</p>' . $grid . '</div>' );

        case 'postcards': // 0204 · twelve motifs of the year as a card box
            $ids = array_slice( vpg_potw_winners( (int) ( $arg ?: wp_date( 'o' ) ) ), 0, 12 );
            if ( ! $ids ) { status_header( 404 ); wp_die( 'No winners yet', 404 ); }
            $cards = '';
            foreach ( $ids as $pid ) { $u = wp_get_attachment_image_url( $pid, 'large' ); if ( $u ) $cards .= '<div style="page-break-inside:avoid"><img src="' . esc_url( $u ) . '" alt="" style="width:100%;aspect-ratio:3/2;object-fit:cover;display:block"><p style="font:9px monospace;color:#6A6A6A;margin-top:2px">viennaphotogroup.com · ' . esc_html( wp_date( 'Y' ) ) . '</p></div>'; }
            vpg_print_shell( __( 'Postcard set', 'vpg-v2' ), '<div class="sheet"><p class="k">0204 · ' . esc_html( sprintf( __( 'Postcards of %s', 'vpg-v2' ), (int) ( $arg ?: wp_date( 'o' ) ) ) ) . '</p><div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px">' . $cards . '</div></div>' );

        case 'contact': // 0208 · a member's images as a classic contact sheet
            $user = is_numeric( $arg ) ? get_user_by( 'id', (int) $arg ) : get_user_by( 'slug', $arg );
            if ( ! $user ) { status_header( 404 ); wp_die( 'No member', 404 ); }
            $imgs = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'author' => $user->ID, 'posts_per_page' => 36, 'post_status' => 'inherit' ] );
            $cells = '';
            foreach ( $imgs as $im ) { $u = wp_get_attachment_image_url( $im->ID, 'medium' ); if ( $u ) $cells .= '<div style="page-break-inside:avoid"><img src="' . esc_url( $u ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;filter:grayscale(1);display:block"></div>'; }
            vpg_print_shell( __( 'Contact sheet', 'vpg-v2' ), '<div class="sheet"><p class="k">0208 · ' . esc_html( $user->display_name ) . '</p><h1 style="font-size:22px">' . esc_html__( 'Contact sheet', 'vpg-v2' ) . '</h1><div style="display:grid;grid-template-columns:repeat(6,1fr);gap:3px;margin-top:12px">' . ( $cells ?: '<p>' . esc_html__( 'No images.', 'vpg-v2' ) . '</p>' ) . '</div></div>' );

        case 'card': // 0216 · business card generator (from query params)
            $n = sanitize_text_field( $_GET['n'] ?? ( is_user_logged_in() ? wp_get_current_user()->display_name : '' ) );
            $h = sanitize_text_field( $_GET['h'] ?? '' ); // handle / role
            $c = sanitize_text_field( $_GET['c'] ?? '' ); // contact
            $card = '<div style="width:85mm;height:55mm;border:1px solid #0B0B0B;padding:8mm;display:flex;flex-direction:column;justify-content:space-between;page-break-inside:avoid"><div><span style="width:16px;height:16px;background:#E5341F;display:inline-block"></span></div><div><p style="font-weight:900;font-size:16px;text-transform:uppercase">' . esc_html( $n ?: 'Your Name' ) . '</p><p style="font-size:11px;color:#6A6A6A">' . esc_html( $h ?: 'Vienna Photo Group' ) . '</p><p style="font-size:10px;margin-top:4px">' . esc_html( $c ) . '</p></div></div>';
            $form = '<form style="margin:14px 0"><input name="n" placeholder="Name" value="' . esc_attr( $n ) . '" style="padding:6px;margin-right:6px"><input name="h" placeholder="Role" value="' . esc_attr( $h ) . '" style="padding:6px;margin-right:6px"><input name="c" placeholder="Contact" value="' . esc_attr( $c ) . '" style="padding:6px;margin-right:6px"><button style="padding:6px 12px">' . esc_html__( 'Update', 'vpg-v2' ) . '</button></form>';
            vpg_print_shell( __( 'Business card', 'vpg-v2' ), '<div class="sheet"><p class="k">0216 · ' . esc_html__( 'Member card', 'vpg-v2' ) . '</p>' . $form . '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">' . str_repeat( $card, 10 ) . '</div></div>' );

        case 'labels': // 0219 · exhibition labels from an issue's plates
            $issue = get_post( (int) $arg );
            $arts  = ( $issue && function_exists( 'vpg_get_articles' ) ) ? vpg_get_articles( $issue->ID ) : [];
            $rows  = '';
            foreach ( $arts as $a ) {
                if ( empty( $a['title'] ) ) continue;
                $rows .= '<div style="border:1px solid #0B0B0B;padding:10mm;page-break-inside:avoid;margin-bottom:6mm"><p style="font-weight:900;text-transform:uppercase;font-size:15px">' . esc_html( $a['title'] ) . '</p><p style="font-size:12px;color:#6A6A6A">' . esc_html( $a['author'] ?? '' ) . '</p></div>';
            }
            vpg_print_shell( __( 'Exhibition labels', 'vpg-v2' ), '<div class="sheet"><p class="k">0219 · ' . esc_html( $issue ? get_the_title( $issue ) : 'Labels' ) . '</p>' . ( $rows ?: '<p>' . esc_html__( 'No works.', 'vpg-v2' ) . '</p>' ) . '</div>' );

        case 'calendar': // 0226 · a wall calendar from POTW winners
            $year = (int) ( $arg ?: wp_date( 'o' ) + 1 );
            $ids  = vpg_potw_winners( $year - 1 );
            $months = [ 'January','February','March','April','May','June','July','August','September','October','November','December' ];
            $pages = '';
            for ( $m = 0; $m < 12; $m++ ) {
                $u = ! empty( $ids[ $m ] ) ? wp_get_attachment_image_url( $ids[ $m ], 'large' ) : '';
                $pages .= '<div class="sheet" style="page-break-after:always"><p class="k">' . esc_html( $year . ' · ' . $months[ $m ] ) . '</p>' . ( $u ? '<img src="' . esc_url( $u ) . '" alt="" style="width:100%;aspect-ratio:4/3;object-fit:cover;margin:8px 0">' : '<div style="aspect-ratio:4/3;background:#F5F4F1;margin:8px 0"></div>' ) . '<h1 style="font-size:20px">' . esc_html( $months[ $m ] ) . '</h1></div>';
            }
            vpg_print_shell( sprintf( __( 'Calendar %d', 'vpg-v2' ), $year ), $pages );

        case 'leporello': // 0227 · a trail as a folded panorama
            $trail = get_post( (int) $arg );
            if ( ! $trail || $trail->post_type !== 'vpg_trail' ) { status_header( 404 ); wp_die( 'No trail', 404 ); }
            $stops = function_exists( 'vpg_trail_stops' ) ? vpg_trail_stops( $trail->ID ) : [];
            $panels = '';
            foreach ( $stops as $i => $s ) {
                $u = has_post_thumbnail( $s['post']->ID ) ? get_the_post_thumbnail_url( $s['post']->ID, 'large' ) : '';
                $panels .= '<div style="flex:0 0 90mm;border-right:1px dashed #ccc;padding:6mm"><p class="k">' . sprintf( '%02d', $i + 1 ) . '</p>' . ( $u ? '<img src="' . esc_url( $u ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;margin:4px 0">' : '' ) . '<p style="font-weight:700;font-size:12px">' . esc_html( get_the_title( $s['post'] ) ) . '</p></div>';
            }
            vpg_print_shell( __( 'Leporello', 'vpg-v2' ), '<div class="sheet" style="max-width:none;overflow-x:auto"><p class="k">0227 · ' . esc_html( get_the_title( $trail ) ) . '</p><div style="display:flex;margin-top:10px">' . $panels . '</div></div>', get_permalink( $trail ), 'A4 landscape' );

        case 'broadsheet': // 0211 · a magazine issue as a broadsheet newspaper
            $issue = get_post( (int) $arg );
            $arts  = ( $issue && function_exists( 'vpg_get_articles' ) ) ? vpg_get_articles( $issue->ID ) : [];
            $cols  = '';
            foreach ( array_slice( $arts, 0, 8 ) as $a ) {
                $u = ! empty( $a['image_id'] ) ? wp_get_attachment_image_url( (int) $a['image_id'], 'medium' ) : '';
                $cols .= '<div style="break-inside:avoid;margin-bottom:8mm">' . ( $u ? '<img src="' . esc_url( $u ) . '" alt="" style="width:100%;margin-bottom:3mm">' : '' ) . '<h2 style="font-size:16px;text-transform:uppercase;font-weight:900">' . esc_html( $a['title'] ?? '' ) . '</h2><p style="font-size:11px;color:#6A6A6A">' . esc_html( $a['author'] ?? '' ) . '</p><p style="font-size:11px;line-height:1.4;margin-top:3px">' . esc_html( wp_trim_words( wp_strip_all_tags( $a['body'] ?? '' ), 60 ) ) . '</p></div>';
            }
            vpg_print_shell( __( 'Broadsheet', 'vpg-v2' ), '<div class="sheet" style="max-width:none"><p class="k">0211 · Vienna Photo Group Broadsheet</p><h1 style="font-size:44px;border-bottom:3px solid #0B0B0B;padding-bottom:6px">' . esc_html( $issue ? get_the_title( $issue ) : 'Issue' ) . '</h1><div style="columns:3;column-gap:10mm;margin-top:8mm">' . $cols . '</div></div>', $issue ? get_permalink( $issue ) : '', 'A3' );

        case 'mailer': // 0232 · a White-Museum shipping sleeve
            $inner = '<div class="sheet"><p class="k">0232 · ' . esc_html__( 'Shipping sleeve', 'vpg-v2' ) . '</p><div style="border:2px solid #0B0B0B;aspect-ratio:3/2;display:flex;flex-direction:column;justify-content:space-between;padding:14mm;margin-top:12px"><div><span style="width:22px;height:22px;background:#E5341F;display:inline-block"></span></div><div style="text-align:center"><p style="font-weight:900;font-size:30px;text-transform:uppercase;letter-spacing:.04em">Vienna Photo Group</p><p style="font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:#6A6A6A">member-run · ad-free · Wien</p></div><div style="border-top:1px solid #0B0B0B;padding-top:6mm;font-size:11px;color:#6A6A6A">' . esc_html__( 'Absender / Return:', 'vpg-v2' ) . ' _______________________</div></div></div>';
            vpg_print_shell( __( 'Mailer', 'vpg-v2' ), $inner );

        case 'archivebox': // 0218 · archive-box wrap + list of annual PDFs
            $year = (int) ( $arg ?: wp_date( 'o' ) );
            $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => 40 ] );
            $list = '';
            foreach ( $issues as $iss ) $list .= '<li>' . esc_html( get_the_title( $iss ) ) . '</li>';
            $inner = '<div class="sheet"><p class="k">0218 · ' . esc_html( sprintf( __( 'Archive box %d', 'vpg-v2' ), $year ) ) . '</p><div style="border:2px solid #0B0B0B;padding:16mm;text-align:center;margin:12px 0"><span style="width:26px;height:26px;background:#E5341F;display:inline-block"></span><p style="font-weight:900;font-size:34px;text-transform:uppercase;margin-top:10px">' . esc_html( $year ) . '</p><p style="letter-spacing:.2em;text-transform:uppercase;color:#6A6A6A;font-size:11px">' . esc_html__( 'The year on USB · Vienna Photo Group', 'vpg-v2' ) . '</p></div><p class="k">' . esc_html__( 'Contents', 'vpg-v2' ) . '</p><ul style="columns:2;font-size:12px">' . $list . '</ul></div>';
            vpg_print_shell( __( 'Archive box', 'vpg-v2' ), $inner );

        case 'stamp': // 0217 · a printable VPG stamp face
            $inner = '<div class="sheet" style="text-align:center"><p class="k">0217 · ' . esc_html__( 'The stamp', 'vpg-v2' ) . '</p><div style="display:inline-block;border:3px solid #E5341F;color:#E5341F;padding:18px 22px;transform:rotate(-6deg);margin:20px;font:900 14px/1.2 Arial;letter-spacing:.14em;text-transform:uppercase">Vienna<br>Photo Group<br><span style="font-size:10px">seen · stamped · Wien</span></div><p style="color:#6A6A6A;font-size:12px">' . esc_html__( 'Take this to a stamp maker, or print and cut for your prints.', 'vpg-v2' ) . '</p></div>';
            vpg_print_shell( __( 'Stamp', 'vpg-v2' ), $inner );
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Interest lists (group order, subscription, swap, flea, scan…)     */
/* ════════════════════════════════════════════════════════════════ */
function vpg_print_lists_def() {
    return [
        'group-order'  => __( '0215 · Joint printer order', 'vpg-v2' ),
        'subscription' => __( '0231 · Print subscription interest', 'vpg-v2' ),
        'print-swap'   => __( '0229 · Print swap · ring exchange', 'vpg-v2' ),
        'flea-market'  => __( '0225 · Photo flea market (proceeds → coffee fund)', 'vpg-v2' ),
        'scan-day'     => __( '0240 · Archive scan day', 'vpg-v2' ),
        'touring'      => __( '0223 · Touring exhibition through the districts', 'vpg-v2' ),
        'bookbinding'  => __( '0221 · Bookbinding workshop', 'vpg-v2' ),
    ];
}
add_action( 'admin_post_vpg_print_signup', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_print_signup' );
    $list = str_replace( '_', '-', sanitize_key( $_POST['list'] ?? '' ) );
    if ( ! array_key_exists( $list, vpg_print_lists_def() ) ) wp_die( 'Bad list', 400 );
    $u = wp_get_current_user();
    $all = (array) get_option( 'vpg_print_lists', [] );
    $entry = [ 'u' => $u->ID, 'by' => $u->display_name, 'email' => $u->user_email, 'note' => sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ), 't' => time() ];
    // one signup per member per list
    $all[ $list ] = array_values( array_filter( (array) ( $all[ $list ] ?? [] ), fn( $e ) => ( $e['u'] ?? 0 ) !== $u->ID ) );
    $all[ $list ][] = $entry;
    update_option( 'vpg_print_lists', $all, false );
    wp_safe_redirect( ( wp_get_referer() ?: home_url( '/print-studio/' ) ) . '#' . $list ); exit;
} );

function vpg_print_list_form( $slug, $label ) {
    $all = (array) get_option( 'vpg_print_lists', [] );
    $n = count( (array) ( $all[ $slug ] ?? [] ) );
    ob_start(); ?>
    <div id="<?php echo esc_attr( $slug ); ?>" style="border:1px solid var(--g-line,#E6E5E1);padding:16px;margin-bottom:12px">
      <p style="font-weight:700;margin:0 0 4px"><?php echo esc_html( $label ); ?></p>
      <p style="font-size:12px;color:var(--g-mid,#6A6A6A);margin:0 0 10px"><?php printf( esc_html( _n( '%d member interested', '%d members interested', $n, 'vpg-v2' ) ), $n ); ?></p>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap">
        <?php wp_nonce_field( 'vpg_print_signup' ); ?>
        <input type="hidden" name="action" value="vpg_print_signup"><input type="hidden" name="list" value="<?php echo esc_attr( $slug ); ?>">
        <input type="text" name="note" maxlength="120" placeholder="<?php esc_attr_e( 'Optional note (how many / what)', 'vpg-v2' ); ?>" style="flex:1;min-width:200px;padding:8px;border:1px solid var(--g-line)">
        <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'I’m in', 'vpg-v2' ); ?></button>
      </form>
      <?php else : ?><p style="font-size:12px;color:var(--g-mid)"><?php esc_html_e( 'Sign in to join the list.', 'vpg-v2' ); ?></p><?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

/* ════════════════════════════════════════════════════════════════ */
/*  Knowledge base (editor-curated) + physical programme + walls      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_print_kb_defaults() {
    return [
        [ 'title' => __( '0207 · Print-on-demand — services that leave you free', 'vpg-v2' ), 'body' => __( 'We stay independent: no exclusive deal. Documented options and their trade-offs live here so any member can order without lock-in.', 'vpg-v2' ) ],
        [ 'title' => __( '0209 · Framing & passepartout sizes', 'vpg-v2' ), 'body' => __( 'Standard frame sizes per plate ratio, so a print drops straight into an off-the-shelf frame. 30×40, 40×50, 50×70 cm with matching mat cut-outs.', 'vpg-v2' ) ],
        [ 'title' => __( '0210 · Riso experiment', 'vpg-v2' ), 'body' => __( 'A special series in a two-colour risograph look — notes on separations, spot colours and paper stock.', 'vpg-v2' ) ],
        [ 'title' => __( '0212 · Photobook templates', 'vpg-v2' ), 'body' => __( 'InDesign & Scribus starting templates for member books — grids, bleed, spine calculators.', 'vpg-v2' ) ],
        [ 'title' => __( '0222 · Paper & profile guide', 'vpg-v2' ), 'body' => __( 'Which paper for which look — matte, baryta, newsprint — with the ICC profiles our labs expect.', 'vpg-v2' ) ],
        [ 'title' => __( '0228 · Hand-print labs in Vienna', 'vpg-v2' ), 'body' => __( 'A cared-for list of darkroom & hand-print labs (Belichter) — who does silver gelatin, who does C-print.', 'vpg-v2' ) ],
        [ 'title' => __( '0230 · Cyanotype insert', 'vpg-v2' ), 'body' => __( 'One handmade insert per year in a tiny edition — the recipe, the timing, who coats.', 'vpg-v2' ) ],
        [ 'title' => __( '0233 · Does the annual need an ISBN?', 'vpg-v2' ), 'body' => __( 'Clarified once: when an ISBN helps (libraries, shops) and how to register in Austria.', 'vpg-v2' ) ],
        [ 'title' => __( '0234 · Legal deposit (Pflichtexemplar)', 'vpg-v2' ), 'body' => __( 'What the Austrian National Library requires of a printed run, and how many copies to send.', 'vpg-v2' ) ],
        [ 'title' => __( '0235 · Exhibition insurance runbook', 'vpg-v2' ), 'body' => __( 'What a hanging really needs — transport, nail-to-nail cover, who signs. A checklist before any wall goes up.', 'vpg-v2' ) ],
        [ 'title' => __( '0237 · Print error culture', 'vpg-v2' ), 'body' => __( 'A page for our misprints, with humour. Every issue’s best typo, kept on purpose.', 'vpg-v2' ) ],
        [ 'title' => __( '0239 · Catalogue co-operation', 'vpg-v2' ), 'body' => __( 'An exhibition catalogue made with a friendly club — how costs, credits and print split.', 'vpg-v2' ) ],
    ];
}

add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_location', __( 'Print studio', 'vpg-v2' ), '🖨 ' . __( 'Print studio', 'vpg-v2' ), 'edit_others_posts', 'vpg-print', 'vpg_print_admin_page' );
}, 24 );

function vpg_print_admin_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['vpg_print_save'] ) && check_admin_referer( 'vpg_print_admin' ) ) {
        // knowledge base
        $kb = [];
        foreach ( (array) ( $_POST['kb_title'] ?? [] ) as $i => $t ) {
            $t = sanitize_text_field( wp_unslash( $t ) );
            $b = sanitize_textarea_field( wp_unslash( $_POST['kb_body'][ $i ] ?? '' ) );
            if ( $t ) $kb[] = [ 'title' => $t, 'body' => $b ];
        }
        update_option( 'vpg_print_kb', $kb, false );
        // programme
        update_option( 'vpg_print_programme', sanitize_textarea_field( wp_unslash( $_POST['programme'] ?? '' ) ), false );
        // photo walls: "name | lat | lng | note" per line
        $walls = [];
        foreach ( array_filter( array_map( 'trim', explode( "\n", (string) wp_unslash( $_POST['walls'] ?? '' ) ) ) ) as $line ) {
            $p = array_map( 'trim', explode( '|', $line ) );
            if ( count( $p ) >= 3 && is_numeric( $p[1] ) ) $walls[] = [ 'name' => sanitize_text_field( $p[0] ), 'lat' => (float) $p[1], 'lng' => (float) $p[2], 'note' => sanitize_text_field( $p[3] ?? '' ) ];
        }
        update_option( 'vpg_photo_walls', $walls, false );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $kb = (array) get_option( 'vpg_print_kb', [] ); if ( ! $kb ) $kb = vpg_print_kb_defaults();
    $walls = (array) get_option( 'vpg_photo_walls', [] );
    $walls_txt = implode( "\n", array_map( fn( $w ) => $w['name'] . ' | ' . $w['lat'] . ' | ' . $w['lng'] . ' | ' . ( $w['note'] ?? '' ), $walls ) );
    ?>
    <div class="wrap"><h1>🖨 <?php esc_html_e( 'Print studio', 'vpg-v2' ); ?></h1>
      <p class="description"><?php printf( esc_html__( 'Public studio page: %s', 'vpg-v2' ), '<a href="' . esc_url( home_url( '/print-studio/' ) ) . '" target="_blank">' . esc_html( home_url( '/print-studio/' ) ) . '</a>' ); ?></p>
      <form method="post"><?php wp_nonce_field( 'vpg_print_admin' ); ?>
        <h2><?php esc_html_e( 'Knowledge base', 'vpg-v2' ); ?></h2>
        <div id="vpg-kb">
          <?php foreach ( array_merge( $kb, [ [ 'title' => '', 'body' => '' ] ] ) as $i => $row ) : ?>
            <p><input type="text" name="kb_title[<?php echo $i; ?>]" value="<?php echo esc_attr( $row['title'] ); ?>" placeholder="<?php esc_attr_e( 'Section title', 'vpg-v2' ); ?>" style="width:100%;font-weight:600">
            <textarea name="kb_body[<?php echo $i; ?>]" rows="2" style="width:100%"><?php echo esc_textarea( $row['body'] ); ?></textarea></p>
          <?php endforeach; ?>
        </div>
        <h2 style="margin-top:16px"><?php esc_html_e( '0206/0213/0214/0224/0238 · Physical programme', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'One per line — shown on the studio page (exhibition concept, gallery evenings, shop windows, culture column, book launch…).', 'vpg-v2' ); ?></p>
        <textarea name="programme" rows="5" style="width:100%;max-width:760px"><?php echo esc_textarea( get_option( 'vpg_print_programme', '' ) ); ?></textarea>
        <h2 style="margin-top:16px"><?php esc_html_e( '0220 · Photo walls in Vienna', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Where photography hangs permanently. One per line: “Name | lat | lng | note”.', 'vpg-v2' ); ?></p>
        <textarea name="walls" rows="4" style="width:100%;max-width:760px" placeholder="Foyer Café Prückel | 48.2100 | 16.3800 | rotating member wall"><?php echo esc_textarea( $walls_txt ); ?></textarea>
        <h2 style="margin-top:16px"><?php esc_html_e( 'Interest lists', 'vpg-v2' ); ?></h2>
        <?php $all = (array) get_option( 'vpg_print_lists', [] );
        foreach ( vpg_print_lists_def() as $slug => $label ) {
            $entries = (array) ( $all[ $slug ] ?? [] );
            echo '<p><strong>' . esc_html( $label ) . '</strong> — ' . count( $entries ) . '<br>';
            echo esc_html( implode( ', ', array_map( fn( $e ) => ( $e['by'] ?? '' ) . ( ! empty( $e['note'] ) ? ' (' . $e['note'] . ')' : '' ), array_slice( $entries, 0, 40 ) ) ) ) . '</p>';
        } ?>
        <p><button name="vpg_print_save" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>
    </div>
    <?php
}

/* ════════════════════════════════════════════════════════════════ */
/*  Public · the print studio hub + the knowledge page                */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    // Knowledge base page
    if ( get_query_var( 'vpg_printkb' ) ) {
        $kb = (array) get_option( 'vpg_print_kb', [] ); if ( ! $kb ) $kb = vpg_print_kb_defaults();
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap">
          <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Print knowledge', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'What we know about <em>print</em>.', 'vpg-v2' ) ); ?></h1>
        </div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:760px"><div class="g-prose">
          <?php foreach ( $kb as $row ) : ?>
            <h3><?php echo esc_html( $row['title'] ); ?></h3>
            <p><?php echo esc_html( $row['body'] ); ?></p>
          <?php endforeach; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }

    if ( ! get_query_var( 'vpg_printstudio' ) ) return;

    $programme = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_print_programme', '' ) ) ) );
    $walls     = (array) get_option( 'vpg_photo_walls', [] );
    $wall_pins = [];
    foreach ( $walls as $w ) if ( ! empty( $w['lat'] ) ) $wall_pins[] = [ 'lat' => $w['lat'], 'lng' => $w['lng'], 'title' => $w['name'], 'lede' => $w['note'] ?? '', 'type' => 'location' ];
    $gens = [
        [ 'sticker',    __( 'Sticker sheet', 'vpg-v2' ),  '0205' ],
        [ 'mailer',     __( 'Shipping sleeve', 'vpg-v2' ),'0232' ],
        [ 'stamp',      __( 'The stamp', 'vpg-v2' ),      '0217' ],
        [ 'card',       __( 'Business card', 'vpg-v2' ),  '0216' ],
        [ 'postcards',  __( 'Postcard set', 'vpg-v2' ),   '0204' ],
        [ 'calendar',   __( 'Wall calendar', 'vpg-v2' ),  '0226' ],
        [ 'archivebox', __( 'Archive box', 'vpg-v2' ),    '0218' ],
    ];
    if ( is_user_logged_in() ) $gens[] = [ 'contact/' . wp_get_current_user()->user_login, __( 'My contact sheet', 'vpg-v2' ), '0208' ];

    get_header(); ?>
    <main id="vpg-main">
      <section class="g-phero"><div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Off the screen', 'vpg-v2' ); ?></p>
        <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>print</em> studio.', 'vpg-v2' ) ); ?></h1>
        <p class="g-lede g-phero__lede"><?php esc_html_e( 'Posters, postcards, stickers, a calendar, a contact sheet — every plate wants paper. Generate, print, hang.', 'vpg-v2' ); ?></p>
      </div></section>

      <section class="g-section g-section--tight"><div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Generators', 'vpg-v2' ); ?></p>
        <div class="g-grid3">
          <?php foreach ( $gens as $g ) : ?>
            <a class="g-card" href="<?php echo esc_url( home_url( '/print/' . $g[0] . '/' ) ); ?>" target="_blank">
              <span class="g-cat"><?php echo esc_html( $g[2] ); ?></span>
              <h3 class="g-card__title"><?php echo esc_html( $g[1] ); ?> ↗</h3>
            </a>
          <?php endforeach; ?>
        </div>
        <p style="margin-top:12px"><a href="<?php echo esc_url( home_url( '/print-wissen/' ) ); ?>" style="font-weight:700">📚 <?php esc_html_e( 'Print knowledge base', 'vpg-v2' ); ?></a></p>
      </div></section>

      <?php if ( $wall_pins ) : ?>
      <section class="g-section g-section--tight"><div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( '0220 · Where photography hangs in Wien', 'vpg-v2' ); ?></p>
        <div id="vpg-map" class="vpg-map vpg-map--tall" data-pins="<?php echo esc_attr( wp_json_encode( $wall_pins ) ); ?>"></div>
      </div></section>
      <?php endif; ?>

      <?php if ( $programme ) : ?>
      <section class="g-section g-section--tight"><div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Physical programme', 'vpg-v2' ); ?></p>
        <ul class="g-list">
          <?php foreach ( $programme as $line ) : ?><li class="g-row" style="cursor:default"><span></span><h3 class="g-row__title" style="margin:0"><?php echo esc_html( $line ); ?></h3><span></span></li><?php endforeach; ?>
        </ul>
      </div></section>
      <?php endif; ?>

      <section class="g-section"><div class="g-wrap" style="max-width:640px">
        <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Count me in', 'vpg-v2' ); ?></p>
        <?php foreach ( vpg_print_lists_def() as $slug => $label ) echo vpg_print_list_form( $slug, $label ); ?>
      </div></section>
    </main>
    <?php get_footer(); exit;
} );

/* Rewrite flushing is handled by the single guard in inc/trails.php
   (option vpg_rw_ver) — its rule set is bumped whenever a cluster adds
   endpoints, so the print/* rules register on the same flush. */

/* 0236 · a QR to the online version on the magazine PDF cover already
   exists; expose a print link on single templates via a small helper. */
function vpg_print_link( $kind, $arg = '' ) {
    return home_url( '/print/' . $kind . ( $arg !== '' ? '/' . rawurlencode( $arg ) : '' ) . '/' );
}
