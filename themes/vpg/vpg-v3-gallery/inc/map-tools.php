<?php
/**
 * VPG v3 — Cluster 01 · Karte als Werkzeug (pass 3 · server side).
 *
 *   0020  Mini-map everywhere · [vpg_map] shortcode + auto on geo singles
 *   0021  Pin QR poster · /poster/{id}/ printable, QR via cdnjs (that page only)
 *   0032  Spot load · an "often shot" hint from the view counter
 *   0035  Printable legend · /karte-legende/ A6 symbol key
 *   0038  Map embeds · /embed/map/ iframe target + a copy snippet on the archive
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Rewrites ───────────────────────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^poster/(\d+)/?$', 'index.php?vpg_poster=$matches[1]', 'top' );
    add_rewrite_rule( '^karte-legende/?$', 'index.php?vpg_maplegend=1', 'top' );
    add_rewrite_rule( '^embed/map/?$', 'index.php?vpg_mapembed=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_poster'; $v[] = 'vpg_maplegend'; $v[] = 'vpg_mapembed'; return $v; } );

add_action( 'template_redirect', function () {
    /* 0021 · a printable poster for one spot, with a QR to its page */
    if ( $pid = (int) get_query_var( 'vpg_poster' ) ) {
        $p = get_post( $pid );
        if ( ! $p || ! in_array( $p->post_type, [ 'vpg_location', 'vpg_studio', 'vpg_shop' ], true ) || $p->post_status !== 'publish' ) {
            status_header( 404 ); exit;
        }
        $url  = get_permalink( $p );
        $dist = get_post_meta( $pid, 'location_district', true ) ?: get_post_meta( $pid, 'shop_district', true );
        header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1">
        <title><?php echo esc_html( $p->post_title ); ?> · <?php bloginfo( 'name' ); ?></title>
        <style>
          body{font-family:"Archivo",system-ui,sans-serif;margin:0;color:#0B0B0B;background:#fff}
          .poster{max-width:520px;margin:0 auto;padding:56px 40px;text-align:center}
          .k{font-size:11px;font-weight:800;letter-spacing:.3em;text-transform:uppercase;color:#E5341F;margin-bottom:20px}
          h1{font-size:clamp(30px,7vw,52px);font-weight:900;text-transform:uppercase;line-height:.95;margin:0 0 10px}
          .d{font-size:13px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#6A6A6A;margin-bottom:32px}
          #qr{display:inline-block;padding:14px;border:1px solid #E6E5E1}
          .u{font-family:ui-monospace,monospace;font-size:12px;color:#6A6A6A;margin-top:18px;word-break:break-all}
          .b{margin-top:34px;font-size:11px;font-weight:800;letter-spacing:.2em;text-transform:uppercase}
          .b i{color:#E5341F;font-style:normal}
          @media print{.noprint{display:none}}
        </style>
        <div class="poster">
          <p class="k">● <?php esc_html_e( 'Scan to open the spot', 'vpg-v2' ); ?></p>
          <h1><?php echo esc_html( $p->post_title ); ?></h1>
          <?php if ( $dist ) : ?><p class="d"><?php echo esc_html( $dist ); ?></p><?php endif; ?>
          <div id="qr"></div>
          <p class="u"><?php echo esc_html( $url ); ?></p>
          <p class="b">VIENNAPHOTOGROUP<i>.</i></p>
          <p class="noprint" style="margin-top:24px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:10px 20px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print', 'vpg-v2' ); ?></button></p>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>new QRCode(document.getElementById('qr'),{text:<?php echo wp_json_encode( $url ); ?>,width:220,height:220,colorDark:'#0B0B0B',colorLight:'#ffffff'});</script>
        <?php
        exit;
    }

    /* 0035 · printable legend — what the symbols mean */
    if ( get_query_var( 'vpg_maplegend' ) ) {
        $schema = function_exists( 'vpg_spot_attr_schema' ) ? vpg_spot_attr_schema() : [];
        header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1">
        <title><?php esc_html_e( 'Map legend', 'vpg-v2' ); ?> · <?php bloginfo( 'name' ); ?></title>
        <style>body{font-family:"Archivo",system-ui,sans-serif;max-width:420px;margin:0 auto;padding:40px 28px;color:#0B0B0B}
        h1{font-size:26px;font-weight:900;text-transform:uppercase}.k{font-size:10px;font-weight:800;letter-spacing:.24em;text-transform:uppercase;color:#E5341F;margin-bottom:8px}
        .row{display:flex;gap:12px;align-items:baseline;padding:7px 0;border-top:1px solid #E6E5E1}.c{font-size:18px;width:28px;text-align:center}
        .marks span{display:inline-flex;gap:6px;align-items:center;margin-right:16px;font-size:12px;font-weight:700}
        .marks i{width:12px;height:12px;border-radius:50%;display:inline-block}@media print{.noprint{display:none}}</style>
        <p class="k">● <?php bloginfo( 'name' ); ?> · <?php esc_html_e( 'Map legend', 'vpg-v2' ); ?></p>
        <h1><?php esc_html_e( 'Symbols', 'vpg-v2' ); ?></h1>
        <p class="marks" style="margin:12px 0 18px">
          <span><i style="background:#E5341F"></i><?php esc_html_e( 'Location', 'vpg-v2' ); ?></span>
          <span><i style="background:#0B0B0B"></i><?php esc_html_e( 'Studio', 'vpg-v2' ); ?></span>
          <span><i style="background:#6A6A6A"></i><?php esc_html_e( 'Shop', 'vpg-v2' ); ?></span>
        </p>
        <?php foreach ( $schema as $def ) : if ( empty( $def['chip'] ) ) continue; ?>
          <div class="row"><span class="c"><?php echo esc_html( $def['chip'] ); ?></span><span><?php echo esc_html( $def['label'] ); ?></span></div>
        <?php endforeach; ?>
        <p class="noprint" style="margin-top:24px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:10px 20px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print A6', 'vpg-v2' ); ?></button></p>
        <?php
        exit;
    }

    /* 0038 · a chrome-free map for embedding in blogs */
    if ( get_query_var( 'vpg_mapembed' ) ) {
        $pins = get_transient( 'vpg_location_pins_v4' );
        if ( ! is_array( $pins ) ) $pins = [];
        header( 'Content-Type: text/html; charset=utf-8' );
        $leaflet_css = get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.css';
        $leaflet_js  = get_template_directory_uri() . '/assets/vendor/leaflet/leaflet.js';
        ?><!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1">
        <title><?php bloginfo( 'name' ); ?> · <?php esc_html_e( 'Map', 'vpg-v2' ); ?></title>
        <link rel="stylesheet" href="<?php echo esc_url( $leaflet_css ); ?>">
        <style>html,body{margin:0;height:100%}#m{height:100%}.cr{position:fixed;bottom:6px;right:8px;z-index:500;font:700 10px/1 sans-serif;background:#fff;padding:4px 8px;border:1px solid #0B0B0B;text-decoration:none;color:#0B0B0B}</style>
        <div id="m"></div>
        <a class="cr" href="<?php echo esc_url( home_url( '/locations/' ) ); ?>" target="_blank">VIENNAPHOTOGROUP.</a>
        <script src="<?php echo esc_url( $leaflet_js ); ?>"></script>
        <script>
          var pins=<?php echo wp_json_encode( $pins ); ?>;
          var m=L.map('m').setView([48.2082,16.3738],12);
          L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap'}).addTo(m);
          var col={location:'#E5341F',studio:'#0B0B0B',shop:'#6A6A6A'},g=L.featureGroup();
          pins.forEach(function(p){if(typeof p.lat!=='number')return;L.circleMarker([p.lat,p.lng],{radius:6,color:col[p.type]||'#E5341F',fillColor:col[p.type]||'#E5341F',fillOpacity:.85,weight:1}).bindPopup('<b>'+(p.title||'')+'</b><br><a href="'+p.url+'" target="_blank">→</a>').addTo(g);});
          g.addTo(m);try{m.fitBounds(g.getBounds().pad(0.15));}catch(e){}
        </script>
        <?php
        exit;
    }
} );

/* ─── 0020 · mini-map · shortcode + auto on any singular with coords ── */
function vpg_mini_map( $lat, $lng, $height = 240 ) {
    if ( ! $lat || ! $lng ) return '';
    $pin = [ [ 'lat' => (float) $lat, 'lng' => (float) $lng, 'type' => 'location' ] ];
    return '<div class="vpg-map vpg-map--mini" data-pins="' . esc_attr( wp_json_encode( $pin ) ) . '" style="height:' . (int) $height . 'px;margin:18px 0"></div>';
}

add_shortcode( 'vpg_map', function ( $atts ) {
    $a = shortcode_atts( [ 'lat' => '', 'lng' => '', 'height' => 240 ], $atts );
    return vpg_mini_map( $a['lat'], $a['lng'], (int) $a['height'] );
} );

// Auto-append a mini-map to any singular that carries coordinates but has
// no full map already (events/trails render their own).
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) return $content;
    if ( is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_event', 'vpg_trail' ] ) ) return $content; // own maps
    $id = get_the_ID();
    foreach ( [ 'location_lat', 'studio_lat', 'shop_lat', '_vpg_lat', '_vpg_event_lat' ] as $k ) {
        $lat = get_post_meta( $id, $k, true );
        if ( $lat ) {
            $lng = get_post_meta( $id, str_replace( 'lat', 'lng', $k ), true );
            if ( $lng ) return $content . vpg_mini_map( $lat, $lng );
        }
    }
    return $content;
}, 24 );

/* ─── 0032 · spot load · a gentle "often shot" hint ──────────────── */
function vpg_spot_load( $post_id ) {
    return (int) get_post_meta( $post_id, '_vpg_views', true ) >= 200;   // threshold, filterable below
}
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] ) || ! in_the_loop() || ! is_main_query() ) return $content;
    if ( ! apply_filters( 'vpg_spot_is_busy', vpg_spot_load( get_the_ID() ), get_the_ID() ) ) return $content;
    $note = '<p style="border-left:3px solid var(--g-red);padding:6px 0 6px 14px;margin:18px 0;font-size:13px;color:var(--g-mid)">'
          . esc_html__( '◑ A much-photographed spot. Worth looking for the frame everyone else misses.', 'vpg-v2' ) . '</p>';
    return $content . $note;
}, 23 );

/* ─── Poster + legend links on the single-spot page ──────────────── */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $id = get_the_ID();
    $links = '<p style="display:flex;gap:14px;flex-wrap:wrap;margin:18px 0 0">'
        . '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/poster/' . $id . '/' ) ) . '" target="_blank">⧉ ' . esc_html__( 'Print poster (QR)', 'vpg-v2' ) . '</a>'
        . '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/karte-legende/' ) ) . '" target="_blank">▤ ' . esc_html__( 'Map legend', 'vpg-v2' ) . '</a>'
        . '</p>';
    return $content . $links;
}, 25 );
