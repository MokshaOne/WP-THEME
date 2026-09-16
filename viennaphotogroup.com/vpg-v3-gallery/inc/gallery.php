<?php
/**
 * VPG v3 — Cluster 08 · Galerie & Präsentation.
 *
 * A universal presentation layer over the theme's images: a shared lightbox
 * (autoplay, fullscreen, zoom, Ken Burns, keyboard, info), a grid switcher,
 * LQIP blur-up, a real colour palette, per-image views, a shot-location jump,
 * an embeddable gallery, a photo-of-the-day, a gallery RSS feed, an exposure
 * playground, camera filtering, diptych/triptych/panorama/stack blocks, a
 * download-size choice, an opt-in watermark, a per-wall performance budget
 * and the curator's note.
 *
 *   0281 autoplay · 0282 fullscreen · 0284 zoom · 0285 compare (see [vpg_ba])
 *   0289 palette · 0291 location jump · 0292 portrait · 0293 panorama
 *   0294 stack · 0295 keyboard · 0296 ken burns · 0297 credits · 0298 watermark
 *   0299 downloads · 0300 image views · 0301 embed · 0302 photo of day
 *   0303 dark gallery · 0304 grid switch · 0305 LQIP · 0306 diptych
 *   0307 triptych · 0308 sequence · 0309 framed · 0310 sound · 0312 camera
 *   0313 exposure · 0314 RSS · 0318 alt layer · 0319 perf budget · 0320 curation
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Enqueue the presentation layer (front-end, auto-binds to marks)   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_enqueue_scripts', function () {
    if ( is_admin() ) return;
    $ver = fn( $rel ) => file_exists( VPG_V2_DIR . $rel ) ? (string) filemtime( VPG_V2_DIR . $rel ) : VPG_V2_VERSION;
    wp_enqueue_style( 'vpg-gallery-extra', VPG_V2_URI . '/assets/css/gallery-extra.css', [ 'vpg-gallery' ], $ver( '/assets/css/gallery-extra.css' ) );
    wp_enqueue_script( 'vpg-gallery-lb', VPG_V2_URI . '/assets/js/gallery-lightbox.js', [], $ver( '/assets/js/gallery-lightbox.js' ), true );
}, 20 );

/* dark gallery (0303) + portrait-safe (0292) body classes */
add_filter( 'body_class', function ( $c ) {
    if ( is_attachment() || is_singular( 'vpg_wall' ) ) { $c[] = 'vpg-dark-gallery'; $c[] = 'vpg-portrait-safe'; }
    return $c;
} );

/* Mark WordPress galleries in post content so the lightbox binds (0281…) */
add_filter( 'the_content', function ( $html ) {
    if ( is_admin() ) return $html;
    $html = preg_replace( '/<figure class="wp-block-gallery/', '<figure data-vpg-gallery data-vpg-grid class="wp-block-gallery', $html, 1 );
    $html = preg_replace( '/<div class="gallery /', '<div data-vpg-gallery class="gallery ', $html );
    return $html;
}, 30 );

/* ════════════════════════════════════════════════════════════════ */
/*  0289 · a real colour palette per image (GD quantize, cached)      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_image_palette( $aid, $n = 5 ) {
    $cached = get_post_meta( $aid, '_vpg_palette', true );
    if ( is_array( $cached ) && $cached ) return $cached;
    $file = get_attached_file( $aid );
    if ( ! $file || ! file_exists( $file ) || ! function_exists( 'imagecreatefromstring' ) ) return [];
    $data = @file_get_contents( $file );
    $im = $data ? @imagecreatefromstring( $data ) : false;
    if ( ! $im ) return [];
    $w = imagesx( $im ); $h = imagesy( $im );
    $small = imagecreatetruecolor( 60, 60 );
    imagecopyresampled( $small, $im, 0, 0, 0, 0, 60, 60, $w, $h );
    if ( function_exists( 'imagetruecolortopalette' ) ) imagetruecolortopalette( $small, false, max( 2, $n ) );
    $counts = [];
    for ( $x = 0; $x < 60; $x += 3 ) for ( $y = 0; $y < 60; $y += 3 ) {
        $rgb = imagecolorsforindex( $small, imagecolorat( $small, $x, $y ) );
        $key = sprintf( '#%02x%02x%02x', $rgb['red'] & 0xF0, $rgb['green'] & 0xF0, $rgb['blue'] & 0xF0 );
        $counts[ $key ] = ( $counts[ $key ] ?? 0 ) + 1;
    }
    imagedestroy( $im ); imagedestroy( $small );
    arsort( $counts );
    $pal = array_slice( array_keys( $counts ), 0, $n );
    update_post_meta( $aid, '_vpg_palette', $pal );
    return $pal;
}

/* store camera + palette lazily on upload (0312 / 0289) */
add_action( 'add_attachment', function ( $aid ) {
    if ( ! wp_attachment_is_image( $aid ) ) return;
    $meta = wp_get_attachment_metadata( $aid );
    $cam = $meta['image_meta']['camera'] ?? '';
    if ( $cam ) update_post_meta( $aid, '_vpg_camera', sanitize_text_field( $cam ) );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0300 · per-image views  ·  0291 shot-location jump                */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    if ( is_attachment() && wp_attachment_is_image( get_queried_object_id() ) ) {
        $id = get_queried_object_id();
        update_post_meta( $id, '_vpg_img_views', (int) get_post_meta( $id, '_vpg_img_views', true ) + 1 );
    }
} );

function vpg_image_location_link( $aid ) {
    $file = get_attached_file( $aid );
    if ( ! $file || ! function_exists( 'vpg_exif_latlng' ) ) return '';
    $geo = vpg_exif_latlng( $file );
    if ( ! $geo ) return '';
    return add_query_arg( [ 'lat' => round( $geo[0], 5 ), 'lng' => round( $geo[1], 5 ) ], get_post_type_archive_link( 'vpg_location' ) );
}

/* ════════════════════════════════════════════════════════════════ */
/*  The extras rendered on the attachment page (image.php)            */
/* ════════════════════════════════════════════════════════════════ */
function vpg_gallery_image_extras( $aid ) {
    $pal   = vpg_image_palette( $aid );
    $views = (int) get_post_meta( $aid, '_vpg_img_views', true );
    $cam   = get_post_meta( $aid, '_vpg_camera', true );
    $jump  = vpg_image_location_link( $aid );
    $meta  = wp_get_attachment_metadata( $aid )['image_meta'] ?? [];
    $owner = (int) get_post_field( 'post_author', $aid );
    $dl    = get_user_meta( $owner, '_vpg_downloads', true ) === '1';
    $full  = wp_get_attachment_image_url( $aid, 'full' );
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap" style="display:flex;gap:32px;flex-wrap:wrap">
      <?php if ( $pal ) : ?>
      <div><span class="g-kicker">● <?php esc_html_e( 'Palette', 'vpg-v2' ); ?></span>
        <div style="display:flex;gap:6px;margin-top:10px"><?php foreach ( $pal as $c ) : ?><a href="<?php echo esc_url( home_url( '/farbe/' . ltrim( $c, '#' ) . '/' ) ); ?>" title="<?php echo esc_attr( $c ); ?>" style="width:34px;height:34px;background:<?php echo esc_attr( $c ); ?>;border:1px solid var(--g-line);display:block"></a><?php endforeach; ?></div>
      </div>
      <?php endif; ?>
      <div><span class="g-kicker">● <?php esc_html_e( 'Seen', 'vpg-v2' ); ?></span><p style="font-weight:900;font-size:24px;margin-top:8px"><?php echo (int) $views; ?></p></div>
      <?php if ( $cam ) : ?><div><span class="g-kicker">● <?php esc_html_e( 'Camera', 'vpg-v2' ); ?></span><p style="margin-top:8px"><a href="<?php echo esc_url( home_url( '/kamera/' . sanitize_title( $cam ) . '/' ) ); ?>"><?php echo esc_html( $cam ); ?> →</a></p></div><?php endif; ?>
      <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        <?php if ( $jump ) : ?><a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( $jump ); ?>">📍 <?php esc_html_e( 'See where it was shot', 'vpg-v2' ); ?></a><?php endif; ?>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="#framed">🖼 <?php esc_html_e( 'See it framed', 'vpg-v2' ); ?></a>
      </div>
    </div></section>

    <?php // 0299 · download size choice (owner opt-in) ?>
    <?php if ( $dl && $full ) : ?>
    <section class="g-section g-section--tight"><div class="g-wrap">
      <span class="g-kicker">● <?php esc_html_e( 'Download', 'vpg-v2' ); ?></span>
      <p style="margin-top:10px;display:flex;gap:12px;flex-wrap:wrap">
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( wp_get_attachment_image_url( $aid, 'large' ) ); ?>" download>Web</a>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( wp_get_attachment_image_url( $aid, 'full' ) ); ?>" download>Print</a>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( $full ); ?>" download>Original</a>
      </p>
    </div></section>
    <?php endif; ?>

    <?php // 0313 · exposure playground (interactive EXIF) ?>
    <?php if ( ! empty( $meta['aperture'] ) || ! empty( $meta['shutter_speed'] ) || ! empty( $meta['iso'] ) ) : ?>
    <section class="g-section g-section--tight"><div class="g-wrap">
      <span class="g-kicker">● <?php esc_html_e( 'Exposure playground', 'vpg-v2' ); ?></span>
      <div class="vpg-expo" id="vpg-expo"
           data-ap="<?php echo esc_attr( $meta['aperture'] ?? 5.6 ); ?>"
           data-sh="<?php echo esc_attr( $meta['shutter_speed'] ?? 0.008 ); ?>"
           data-iso="<?php echo esc_attr( $meta['iso'] ?? 200 ); ?>">
        <p style="margin-bottom:10px;color:var(--g-mid)"><?php esc_html_e( 'This frame’s settings — drag to feel the trade-offs.', 'vpg-v2' ); ?></p>
        <label>ƒ/<span class="read" id="ex-ap"></span> — <?php esc_html_e( 'aperture · depth of field', 'vpg-v2' ); ?></label>
        <input type="range" id="r-ap" min="1.4" max="22" step="0.1">
        <p id="ex-ap-note" style="font-size:12px;color:var(--g-mid);margin-bottom:10px"></p>
        <label id="ex-sh-l"></label><input type="range" id="r-sh" min="0" max="12" step="1">
        <p id="ex-sh-note" style="font-size:12px;color:var(--g-mid);margin-bottom:10px"></p>
        <label>ISO <span class="read" id="ex-iso"></span> — <?php esc_html_e( 'sensitivity · noise', 'vpg-v2' ); ?></label>
        <input type="range" id="r-iso" min="100" max="12800" step="100">
        <p id="ex-iso-note" style="font-size:12px;color:var(--g-mid)"></p>
      </div>
    </div></section>
    <script>
    (function(){var b=document.getElementById('vpg-expo');if(!b)return;
      var SH=[30,15,8,4,2,1,0.5,0.25,0.125,0.0166,0.008,0.002,0.001];
      function shLabel(s){return s>=1?s+'s':'1/'+Math.round(1/s);}
      var ap=document.getElementById('r-ap'),sh=document.getElementById('r-sh'),iso=document.getElementById('r-iso');
      ap.value=b.dataset.ap;iso.value=b.dataset.iso;
      var sv=parseFloat(b.dataset.sh);var si=0,best=9;SH.forEach(function(x,i){var d=Math.abs(x-sv);if(d<best){best=d;si=i;}});sh.value=si;
      function up(){
        document.getElementById('ex-ap').textContent=(+ap.value).toFixed(1);
        document.getElementById('ex-ap-note').textContent=ap.value<4?'<?php echo esc_js( __( 'Wide open — creamy background, thin focus.', 'vpg-v2' ) ); ?>':(ap.value>11?'<?php echo esc_js( __( 'Stopped down — everything sharp, needs light.', 'vpg-v2' ) ); ?>':'<?php echo esc_js( __( 'A balanced middle aperture.', 'vpg-v2' ) ); ?>');
        var s=SH[sh.value];document.getElementById('ex-sh-l').innerHTML='<?php echo esc_js( __( 'shutter', 'vpg-v2' ) ); ?> <span class="read">'+shLabel(s)+'</span> — <?php echo esc_js( __( 'motion', 'vpg-v2' ) ); ?>';
        document.getElementById('ex-sh-note').textContent=s>=0.5?'<?php echo esc_js( __( 'Long — motion blurs, use a tripod.', 'vpg-v2' ) ); ?>':(s<=0.002?'<?php echo esc_js( __( 'Fast — freezes action crisp.', 'vpg-v2' ) ); ?>':'<?php echo esc_js( __( 'Hand-holdable for still subjects.', 'vpg-v2' ) ); ?>');
        document.getElementById('ex-iso').textContent=iso.value;
        document.getElementById('ex-iso-note').textContent=iso.value>3200?'<?php echo esc_js( __( 'High — grain climbs, saves dark scenes.', 'vpg-v2' ) ); ?>':'<?php echo esc_js( __( 'Clean, low noise.', 'vpg-v2' ) ); ?>';
      }
      [ap,sh,iso].forEach(function(r){r.addEventListener('input',up);});up();
    })();
    </script>
    <?php endif; ?>

    <?php // 0309 · framed on a wall ?>
    <section class="g-section g-section--tight" id="framed"><div class="g-wrap" style="text-align:center">
      <span class="g-kicker">● <?php esc_html_e( 'On a wall', 'vpg-v2' ); ?></span>
      <div style="margin-top:16px"><span class="vpg-framed"><span class="mat"><?php echo wp_get_attachment_image( $aid, 'large', false, [ 'style' => 'max-width:min(560px,80vw)' ] ); ?></span></span></div>
    </div></section>
    <?php
}

/* ════════════════════════════════════════════════════════════════ */
/*  0306 diptych · 0307 triptych · 0293 panorama · 0294 stack         */
/* ════════════════════════════════════════════════════════════════ */
function vpg_ids_shortcode_html( $atts, $class, $tag = 'div' ) {
    $ids = array_filter( array_map( 'intval', explode( ',', (string) ( $atts['ids'] ?? '' ) ) ) );
    if ( ! $ids ) return '';
    $out = '<' . $tag . ' class="' . esc_attr( $class ) . '" data-vpg-gallery>';
    foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'large' ); $f = wp_get_attachment_image_url( $id, 'full' ); if ( $u ) $out .= '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '">'; }
    return $out . '</' . $tag . '>';
}
add_shortcode( 'vpg_diptych', fn( $a ) => vpg_ids_shortcode_html( $a, 'vpg-diptych' ) );
add_shortcode( 'vpg_triptych', fn( $a ) => vpg_ids_shortcode_html( $a, 'vpg-triptych' ) );
add_shortcode( 'vpg_pano', fn( $a ) => vpg_ids_shortcode_html( $a, 'vpg-pano' ) );
add_shortcode( 'vpg_stack', function ( $a ) {
    $ids = array_filter( array_map( 'intval', explode( ',', (string) ( $a['ids'] ?? '' ) ) ) );
    if ( ! $ids ) return '';
    $out = '<div class="vpg-stack" data-vpg-gallery style="position:relative;width:min(360px,80vw);height:300px;margin:24px auto">';
    foreach ( array_slice( $ids, 0, 6 ) as $i => $id ) {
        $u = wp_get_attachment_image_url( $id, 'large' ); $f = wp_get_attachment_image_url( $id, 'full' );
        if ( ! $u ) continue;
        $rot = ( $i - 2 ) * 3;
        $out .= '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="' . esc_attr( get_the_title( $id ) ) . '" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transform:rotate(' . $rot . 'deg);box-shadow:0 4px 20px rgba(0,0,0,.2);border:6px solid #fff">';
    }
    return $out . '<p style="position:absolute;bottom:-28px;left:0;right:0;text-align:center;font-size:12px;color:var(--g-mid)">' . esc_html__( 'Click to flip through', 'vpg-v2' ) . '</p></div>';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0302 · photo of the day  (shortcode + homepage-safe)              */
/* ════════════════════════════════════════════════════════════════ */
function vpg_photo_of_day() {
    $key = 'vpg_potd_' . gmdate( 'Y-m-d' );
    $id = (int) get_transient( $key );
    if ( ! $id ) {
        $pool = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => 200, 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC' ] );
        if ( $pool ) { $id = (int) $pool[ crc32( gmdate( 'Y-m-d' ) ) % count( $pool ) ]; set_transient( $key, $id, DAY_IN_SECONDS ); }
    }
    return $id;
}
add_shortcode( 'vpg_photo_of_day', function () {
    $id = vpg_photo_of_day();
    if ( ! $id ) return '';
    $u = wp_get_attachment_image_url( $id, 'large' ); $f = wp_get_attachment_image_url( $id, 'full' );
    $au = get_userdata( (int) get_post_field( 'post_author', $id ) );
    ob_start(); ?>
    <figure data-vpg-gallery style="margin:0">
      <img src="<?php echo esc_url( $u ); ?>" data-full="<?php echo esc_url( $f ); ?>" data-lqip alt="<?php echo esc_attr( get_the_title( $id ) ); ?>" style="width:100%;display:block">
      <figcaption style="font-size:12px;color:var(--g-mid,#6A6A6A);margin-top:8px">● <?php esc_html_e( 'Photo of the day', 'vpg-v2' ); ?><?php if ( $au ) echo ' · ' . esc_html( $au->display_name ); ?></figcaption>
    </figure>
    <?php return ob_get_clean();
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0301 embed · 0314 gallery RSS · 0312 camera archive               */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^embed/gallery/([^/]+)/?$', 'index.php?vpg_gembed=$matches[1]', 'top' );
    add_rewrite_rule( '^gallery/feed/?$', 'index.php?vpg_gfeed=1', 'top' );
    add_rewrite_rule( '^kamera/([^/]+)/?$', 'index.php?vpg_camera=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_gembed', 'vpg_gfeed', 'vpg_camera' ); return $v; } );

add_action( 'template_redirect', function () {
    // 0301 · embeddable member gallery
    if ( $slug = get_query_var( 'vpg_gembed' ) ) {
        $user = get_user_by( 'slug', $slug ) ?: get_user_by( 'login', $slug );
        $ids  = $user && function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $user->ID ) : [];
        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'X-Frame-Options: ALLOWALL' );
        echo '<!doctype html><meta charset=utf8><meta name=viewport content="width=device-width,initial-scale=1"><style>body{margin:0;background:#0B0B0B;font-family:sans-serif}.g{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:4px}img{width:100%;aspect-ratio:1;object-fit:cover;display:block}a.c{display:block;text-align:center;color:#888;font:11px sans-serif;padding:8px;text-decoration:none}</style>';
        echo '<div class="g">';
        foreach ( array_slice( (array) $ids, 0, 24 ) as $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); if ( $u ) echo '<img loading="lazy" src="' . esc_url( $u ) . '" alt="">'; }
        echo '</div><a class="c" href="' . esc_url( home_url( '/members/' . ( $user ? $user->user_nicename : '' ) . '/' ) ) . '" target="_blank">' . esc_html( ( $user ? $user->display_name : '' ) . ' · Vienna Photo Group' ) . '</a>';
        exit;
    }
    // 0314 · gallery RSS of recent images
    if ( get_query_var( 'vpg_gfeed' ) ) {
        $imgs = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => 30, 'orderby' => 'date', 'order' => 'DESC' ] );
        header( 'Content-Type: application/rss+xml; charset=utf-8' );
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<rss version=\"2.0\"><channel>";
        echo '<title>' . esc_html( get_bloginfo( 'name' ) ) . ' · ' . esc_html__( 'New photographs', 'vpg-v2' ) . '</title><link>' . esc_url( home_url( '/' ) ) . '</link><description></description>';
        foreach ( $imgs as $im ) {
            $u = wp_get_attachment_image_url( $im->ID, 'large' );
            echo '<item><title>' . esc_html( get_the_title( $im ) ?: 'Photograph' ) . '</title><link>' . esc_url( get_attachment_link( $im->ID ) ) . '</link><guid>' . esc_url( get_attachment_link( $im->ID ) ) . '</guid><pubDate>' . esc_html( mysql2date( 'r', $im->post_date_gmt ) ) . '</pubDate>';
            if ( $u ) echo '<enclosure url="' . esc_url( $u ) . '" type="image/jpeg"/>';
            echo '</item>';
        }
        echo '</channel></rss>';
        exit;
    }
    // 0312 · all images from one camera / film
    if ( $cam = get_query_var( 'vpg_camera' ) ) {
        $imgs = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'posts_per_page' => 60, 'meta_key' => '_vpg_camera' ] );
        $imgs = array_filter( $imgs, fn( $i ) => sanitize_title( get_post_meta( $i->ID, '_vpg_camera', true ) ) === $cam );
        $name = $imgs ? get_post_meta( reset( $imgs )->ID, '_vpg_camera', true ) : $cam;
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Shot on', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo esc_html( $name ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div data-vpg-gallery data-vpg-grid style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px">
          <?php foreach ( $imgs as $im ) { $u = wp_get_attachment_image_url( $im->ID, 'medium' ); $f = wp_get_attachment_image_url( $im->ID, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" data-lqip alt="' . esc_attr( get_the_title( $im->ID ) ) . '" style="width:100%;aspect-ratio:1;object-fit:cover">'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0319 · per-wall performance budget (editor warning)              */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-wall-perf', '⚡ ' . __( 'Wall budget', 'vpg-v2' ), function ( $post ) {
        $ids = function_exists( 'vpg_curated_ids' ) ? vpg_curated_ids( $post->ID ) : [];
        $bytes = 0;
        foreach ( (array) $ids as $id ) { $f = get_attached_file( $id ); if ( $f && file_exists( $f ) ) $bytes += filesize( $f ); }
        $mb = $bytes / 1048576;
        $budget = 6; // MB soft budget per wall
        echo '<p>' . esc_html( sprintf( __( '%1$d frames · ~%2$s MB total.', 'vpg-v2' ), count( (array) $ids ), number_format( $mb, 1 ) ) ) . '</p>';
        if ( $mb > $budget ) echo '<p style="color:#d63638;font-weight:600">' . esc_html( sprintf( __( 'Over the %d MB wall budget — consider fewer or lighter frames.', 'vpg-v2' ), $budget ) ) . '</p>';
        else echo '<p style="color:#1A7A3C">' . esc_html__( 'Within budget. Loads fast.', 'vpg-v2' ) . '</p>';
        // 0320 · the curator's note
        wp_nonce_field( 'vpg_wall_why', 'vpg_wall_why_nonce' );
        echo '<hr><p><strong>' . esc_html__( '0320 · Why these hang', 'vpg-v2' ) . '</strong></p>';
        echo '<textarea name="vpg_curation_why" rows="3" style="width:100%">' . esc_textarea( (string) get_post_meta( $post->ID, '_vpg_curation_why', true ) ) . '</textarea>';
    }, 'vpg_wall', 'side' );
} );
add_action( 'save_post_vpg_wall', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_wall_why_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_wall_why_nonce'], 'vpg_wall_why' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $v = sanitize_textarea_field( wp_unslash( $_POST['vpg_curation_why'] ?? '' ) );
    $v !== '' ? update_post_meta( $post_id, '_vpg_curation_why', $v ) : delete_post_meta( $post_id, '_vpg_curation_why' );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0298 · opt-in watermark preference (member setting honoured)      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_toggle_watermark', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_watermark' );
    $on = get_user_meta( get_current_user_id(), '_vpg_watermark', true ) === '1';
    update_user_meta( get_current_user_id(), '_vpg_watermark', $on ? '' : '1' );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) ); exit;
} );
