<?php
/**
 * VPG v3 — Q9 · image intelligence (all local, no cloud).
 *
 *   0569  Similar-image search · dHash neighbours, /aehnliche/{id}/
 *   0568  Colour search · average colour on upload, /farbe/{hex}/
 *   0887  Face/region anonymisation · manual box, GD pixelation
 *   0881  Alt-text suggestions · pluggable self-hosted endpoint
 *   0882  Tag suggestions · same endpoint, same honest "off" default
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  0568 · Dominant colour · computed once, on upload                */
/* ════════════════════════════════════════════════════════════════ */
function vpg_image_avg_color( $file ) {
    if ( ! $file || ! file_exists( $file ) || ! function_exists( 'imagecreatefromstring' ) ) return null;
    $data = @file_get_contents( $file );
    if ( ! $data ) return null;
    $img = @imagecreatefromstring( $data );
    if ( ! $img ) return null;
    $w = imagesx( $img ); $h = imagesy( $img );
    $small = imagecreatetruecolor( 16, 16 );
    imagecopyresampled( $small, $img, 0, 0, 0, 0, 16, 16, $w, $h );
    imagedestroy( $img );
    $r = $g = $b = 0;
    for ( $y = 0; $y < 16; $y++ ) for ( $x = 0; $x < 16; $x++ ) {
        $c = imagecolorat( $small, $x, $y );
        $r += ( $c >> 16 ) & 0xFF; $g += ( $c >> 8 ) & 0xFF; $b += $c & 0xFF;
    }
    imagedestroy( $small );
    return [ (int) round( $r / 256 ), (int) round( $g / 256 ), (int) round( $b / 256 ) ];
}

function vpg_rgb_to_hue( $r, $g, $b ) {
    $r /= 255; $g /= 255; $b /= 255;
    $max = max( $r, $g, $b ); $min = min( $r, $g, $b ); $d = $max - $min;
    if ( $d == 0 ) return -1;                        // greyscale · no meaningful hue
    if ( $max == $r )      $h = fmod( ( $g - $b ) / $d, 6 );
    elseif ( $max == $g )  $h = ( $b - $r ) / $d + 2;
    else                   $h = ( $r - $g ) / $d + 4;
    $h = (int) round( $h * 60 );
    return ( $h + 360 ) % 360;
}

add_action( 'add_attachment', function ( $att_id ) {
    if ( ! wp_attachment_is_image( $att_id ) ) return;
    $rgb = vpg_image_avg_color( get_attached_file( $att_id ) );
    if ( ! $rgb ) return;
    update_post_meta( $att_id, '_vpg_color', sprintf( '#%02x%02x%02x', ...$rgb ) );
    update_post_meta( $att_id, '_vpg_hue', vpg_rgb_to_hue( ...$rgb ) );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Rewrites · /aehnliche/{id}/ and /farbe/{hex}/                    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^aehnliche/(\d+)/?$', 'index.php?vpg_similar=$matches[1]', 'top' );
    add_rewrite_rule( '^farbe/([0-9a-fA-F]{6})/?$', 'index.php?vpg_color=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_similar'; $v[] = 'vpg_color'; return $v; } );

add_action( 'template_redirect', function () {
    $sim = (int) get_query_var( 'vpg_similar' );
    $col = get_query_var( 'vpg_color' );
    if ( ! $sim && ! $col ) return;

    if ( $sim ) {
        $seed = get_post( $sim );
        if ( ! $seed || ! wp_attachment_is_image( $sim ) ) { global $wp_query; $wp_query->set_404(); status_header( 404 ); return; }
        $hits = function_exists( 'vpg_similar_images' ) ? vpg_similar_images( $sim, 24, 26 ) : [];
        $kick = __( 'Visually similar', 'vpg-v2' );
        $head = get_the_title( $sim ) ?: __( 'this frame', 'vpg-v2' );
    } else {
        $hue  = vpg_rgb_to_hue( hexdec( substr( $col, 0, 2 ) ), hexdec( substr( $col, 2, 2 ) ), hexdec( substr( $col, 4, 2 ) ) );
        $hits = vpg_images_near_hue( $hue, 24 );
        $kick = __( 'By colour', 'vpg-v2' );
        $head = '#' . strtolower( $col );
    }

    get_header(); ?>
    <main id="vpg-main">
      <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:16px"><?php echo $col ? '<span style="display:inline-block;width:12px;height:12px;background:#' . esc_attr( $col ) . ';border:1px solid var(--g-line);vertical-align:-1px"></span> ' : '● '; ?><?php echo esc_html( $kick ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo esc_html( $head ); ?><span style="color:var(--g-red)">.</span></h1>
          <p class="g-lede g-phero__lede"><?php echo esc_html( sprintf( _n( '%d frame from the member archive.', '%d frames from the member archive.', count( $hits ), 'vpg-v2' ), count( $hits ) ) ); ?></p>
        </div>
      </div></div></section>
      <section class="g-section"><div class="g-wrap">
        <?php if ( $hits ) : ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
          <?php foreach ( $hits as $hid ) : $u = wp_get_attachment_image_url( $hid, 'medium_large' ); if ( ! $u ) continue; ?>
            <figure style="margin:0">
              <a href="<?php echo esc_url( get_attachment_link( $hid ) ); ?>" style="display:block;aspect-ratio:1;overflow:hidden;background:var(--g-bg)">
                <img src="<?php echo esc_url( $u ); ?>" alt="<?php echo esc_attr( get_the_title( $hid ) ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
              </a>
              <figcaption class="g-meta" style="margin-top:5px;font-size:10px"><a href="<?php echo esc_url( home_url( '/aehnliche/' . $hid . '/' ) ); ?>"><?php esc_html_e( 'more like this →', 'vpg-v2' ); ?></a></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
          <p class="g-lede"><?php esc_html_e( 'Nothing close enough yet — the archive grows with every upload.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </div></section>
    </main>
    <?php get_footer();
    exit;
} );

function vpg_images_near_hue( $hue, $limit = 24 ) {
    if ( $hue < 0 ) return [];
    $ids = get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 400,
        'fields'         => 'ids',
        'meta_key'       => '_vpg_hue',
    ] );
    $scored = [];
    foreach ( $ids as $id ) {
        $h = (int) get_post_meta( $id, '_vpg_hue', true );
        if ( $h < 0 ) continue;
        $d = min( abs( $h - $hue ), 360 - abs( $h - $hue ) );   // circular distance
        if ( $d <= 25 ) $scored[ $id ] = $d;
    }
    asort( $scored );
    return array_slice( array_keys( $scored ), 0, $limit );
}

/* Tie it together on the single-image page · a "more like this" +
   colour chip appear under the existing "visually related" block. */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_attachment() || ! wp_attachment_is_image( get_the_ID() ) ) return $content;
    $id  = get_the_ID();
    $col = get_post_meta( $id, '_vpg_color', true );
    ob_start(); ?>
    <p style="margin:18px 0 0;display:flex;gap:14px;flex-wrap:wrap;align-items:center">
      <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( home_url( '/aehnliche/' . $id . '/' ) ); ?>"><?php esc_html_e( '⁂ Find visually similar', 'vpg-v2' ); ?></a>
      <?php if ( $col ) : ?>
        <a style="display:inline-flex;gap:8px;align-items:center;text-decoration:none;font-size:12px;font-weight:700" href="<?php echo esc_url( home_url( '/farbe/' . ltrim( $col, '#' ) . '/' ) ); ?>">
          <span style="width:16px;height:16px;background:<?php echo esc_attr( $col ); ?>;border:1px solid var(--g-line)"></span><?php esc_html_e( 'More in this colour', 'vpg-v2' ); ?>
        </a>
      <?php endif; ?>
    </p>
    <?php
    return $content . ob_get_clean();
}, 22 );

/* ════════════════════════════════════════════════════════════════ */
/*  0887 · Manual face/region anonymisation · GD pixelation          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_blur_region', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_blur_region' );
    $att = (int) ( $_POST['att'] ?? 0 );
    $own = (int) get_post_field( 'post_author', $att );
    if ( ! wp_attachment_is_image( $att ) || ( $own !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) ) {
        wp_die( 'Not your image.', 403 );
    }
    // Box comes in as fractions of the full image (0..1)
    $fx = min( 1, max( 0, (float) ( $_POST['x'] ?? 0 ) ) );
    $fy = min( 1, max( 0, (float) ( $_POST['y'] ?? 0 ) ) );
    $fw = min( 1, max( 0, (float) ( $_POST['w'] ?? 0 ) ) );
    $fh = min( 1, max( 0, (float) ( $_POST['h'] ?? 0 ) ) );

    $file = get_attached_file( $att );
    if ( $file && $fw > 0.01 && $fh > 0.01 && vpg_pixelate_region( $file, $fx, $fy, $fw, $fh ) ) {
        // Regenerate the sized copies + refresh dominant colour
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $file ) );
        if ( $rgb = vpg_image_avg_color( $file ) ) {
            update_post_meta( $att, '_vpg_color', sprintf( '#%02x%02x%02x', ...$rgb ) );
            update_post_meta( $att, '_vpg_hue', vpg_rgb_to_hue( ...$rgb ) );
        }
    }
    wp_safe_redirect( get_attachment_link( $att ) );
    exit;
} );

function vpg_pixelate_region( $file, $fx, $fy, $fw, $fh ) {
    if ( ! function_exists( 'imagecreatefromstring' ) ) return false;
    $type = @exif_imagetype( $file );
    if ( ! in_array( $type, [ IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP ], true ) ) return false;
    $img = @imagecreatefromstring( (string) file_get_contents( $file ) );
    if ( ! $img ) return false;
    $W = imagesx( $img ); $H = imagesy( $img );
    $x = (int) floor( $fx * $W ); $y = (int) floor( $fy * $H );
    $w = (int) ceil( $fw * $W );  $h = (int) ceil( $fh * $H );
    $w = min( $w, $W - $x ); $h = min( $h, $H - $y );
    if ( $w < 4 || $h < 4 ) { imagedestroy( $img ); return false; }
    // Mosaic: blocks ~1/12 of the box's short side, min 6px
    $block = max( 6, (int) round( min( $w, $h ) / 12 ) );
    if ( function_exists( 'imagefilter' ) ) {
        // pixelate just the region by cropping, filtering, pasting back
        $crop = imagecrop( $img, [ 'x' => $x, 'y' => $y, 'width' => $w, 'height' => $h ] );
        if ( $crop ) {
            imagefilter( $crop, IMG_FILTER_PIXELATE, $block, true );
            imagecopy( $img, $crop, $x, $y, 0, 0, $w, $h );
            imagedestroy( $crop );
        }
    }
    $ok = false;
    if ( $type === IMAGETYPE_JPEG )      $ok = imagejpeg( $img, $file, 88 );
    elseif ( $type === IMAGETYPE_PNG )   $ok = imagepng( $img, $file );
    elseif ( $type === IMAGETYPE_WEBP )  $ok = function_exists( 'imagewebp' ) && imagewebp( $img, $file, 88 );
    imagedestroy( $img );
    return $ok;
}

/* The box-picker overlay on the owner's own image page */
add_action( 'wp_footer', function () {
    if ( ! is_attachment() || ! is_user_logged_in() || ! wp_attachment_is_image( get_the_ID() ) ) return;
    $id  = get_the_ID();
    $own = (int) get_post_field( 'post_author', $id );
    if ( $own !== get_current_user_id() && ! current_user_can( 'edit_others_posts' ) ) return;
    $src = wp_get_attachment_image_url( $id, 'large' );
    if ( ! $src ) return;
    ?>
    <div id="vpg-anon" hidden style="position:fixed;inset:0;z-index:90;background:rgba(11,11,11,.92);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:14px;padding:20px">
      <p style="color:#fff;font-size:13px;font-weight:700;margin:0"><?php esc_html_e( 'Drag a box over a face or plate — release to pixelate. This overwrites the original.', 'vpg-v2' ); ?></p>
      <div style="position:relative;max-width:90vw;max-height:70vh"><img id="vpg-anon-img" src="<?php echo esc_url( $src ); ?>" alt="" style="display:block;max-width:90vw;max-height:70vh;user-select:none" draggable="false"><div id="vpg-anon-box" hidden style="position:absolute;border:2px solid #E5341F;background:rgba(229,52,31,.25)"></div></div>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="vpg-anon-form" style="display:flex;gap:10px">
        <?php wp_nonce_field( 'vpg_blur_region' ); ?>
        <input type="hidden" name="action" value="vpg_blur_region">
        <input type="hidden" name="att" value="<?php echo (int) $id; ?>">
        <input type="hidden" name="x" id="vpg-anon-x"><input type="hidden" name="y" id="vpg-anon-y">
        <input type="hidden" name="w" id="vpg-anon-w"><input type="hidden" name="h" id="vpg-anon-h">
        <button class="g-btn g-btn--red" type="submit" id="vpg-anon-go" disabled><?php esc_html_e( 'Pixelate this box', 'vpg-v2' ); ?></button>
        <button class="g-btn g-btn--ghost" type="button" id="vpg-anon-cancel"><?php esc_html_e( 'Close', 'vpg-v2' ); ?></button>
      </form>
    </div>
    <p style="margin:14px 0 0"><button type="button" class="g-btn g-btn--ghost" id="vpg-anon-open"><?php esc_html_e( '◐ Anonymise a face / plate', 'vpg-v2' ); ?></button></p>
    <script>
    (function () {
      var open = document.getElementById('vpg-anon-open'), ov = document.getElementById('vpg-anon'),
          img = document.getElementById('vpg-anon-img'), box = document.getElementById('vpg-anon-box'),
          go = document.getElementById('vpg-anon-go'), sx = 0, sy = 0, drawing = false;
      var fx = document.getElementById('vpg-anon-x'), fy = document.getElementById('vpg-anon-y'),
          fw = document.getElementById('vpg-anon-w'), fh = document.getElementById('vpg-anon-h');
      open.addEventListener('click', function () { ov.hidden = false; });
      document.getElementById('vpg-anon-cancel').addEventListener('click', function () { ov.hidden = true; });
      function rel(e) { var r = img.getBoundingClientRect(); var p = e.touches ? e.touches[0] : e; return { x: p.clientX - r.left, y: p.clientY - r.top, r: r }; }
      img.addEventListener('pointerdown', function (e) { e.preventDefault(); var p = rel(e); sx = p.x; sy = p.y; drawing = true; box.hidden = false; box.style.left = sx + 'px'; box.style.top = sy + 'px'; box.style.width = '0'; box.style.height = '0'; });
      window.addEventListener('pointermove', function (e) {
        if (!drawing) return; var p = rel(e);
        var x = Math.min(sx, p.x), y = Math.min(sy, p.y), w = Math.abs(p.x - sx), h = Math.abs(p.y - sy);
        box.style.left = x + 'px'; box.style.top = y + 'px'; box.style.width = w + 'px'; box.style.height = h + 'px';
        fx.value = (x / p.r.width).toFixed(4); fy.value = (y / p.r.height).toFixed(4);
        fw.value = (w / p.r.width).toFixed(4); fh.value = (h / p.r.height).toFixed(4);
        go.disabled = (w < 8 || h < 8);
      });
      window.addEventListener('pointerup', function () { drawing = false; });
    })();
    </script>
    <?php
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0881 / 0882 · Alt-text & tag suggestions · pluggable, off by     */
/*  default. Point vpg_vision_endpoint at a self-hosted captioning    */
/*  service (BLIP, llava, …) to make suggestions live. Nothing leaves */
/*  the server until an endpoint is set — local-first by default.     */
/* ════════════════════════════════════════════════════════════════ */
function vpg_vision_endpoint() {
    return trim( (string) get_option( 'vpg_vision_endpoint', '' ) );
}

/** Returns [ 'alt' => string, 'tags' => [..] ] or null when off/failed. */
function vpg_vision_suggest( $att_id ) {
    $endpoint = vpg_vision_endpoint();
    if ( ! $endpoint || ! wp_attachment_is_image( $att_id ) ) return null;
    $url = wp_get_attachment_image_url( $att_id, 'medium_large' );
    if ( ! $url ) return null;
    $res = wp_remote_post( $endpoint, [
        'timeout' => 20,
        'headers' => [ 'Content-Type' => 'application/json' ],
        'body'    => wp_json_encode( [ 'image_url' => $url, 'want' => [ 'alt', 'tags' ] ] ),
    ] );
    if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) return null;
    $j = json_decode( wp_remote_retrieve_body( $res ), true );
    if ( ! is_array( $j ) ) return null;
    return [
        'alt'  => sanitize_text_field( (string) ( $j['alt'] ?? '' ) ),
        'tags' => array_slice( array_map( 'sanitize_text_field', (array) ( $j['tags'] ?? [] ) ), 0, 12 ),
    ];
}

/* Setting field in the existing VPG settings page (Hub cluster) */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Vision endpoint', 'vpg-v2' ), __( '🔎 Vision endpoint', 'vpg-v2' ), 'manage_options', 'vpg-vision', function () {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_vision_endpoint'] ) && check_admin_referer( 'vpg_vision_save' ) ) {
            update_option( 'vpg_vision_endpoint', esc_url_raw( trim( wp_unslash( $_POST['vpg_vision_endpoint'] ) ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        ?>
        <div class="wrap">
          <h1>🔎 <?php esc_html_e( 'Vision endpoint', 'vpg-v2' ); ?></h1>
          <p class="description" style="max-width:640px"><?php esc_html_e( 'Optional. A self-hosted image-captioning service (BLIP, llava, …) that accepts POST {image_url, want:["alt","tags"]} and returns {alt, tags:[…]}. When set, editors get alt-text and tag suggestions on member photos. Left empty, nothing is sent anywhere — the site stays local-first.', 'vpg-v2' ); ?></p>
          <form method="post">
            <?php wp_nonce_field( 'vpg_vision_save' ); ?>
            <input type="url" name="vpg_vision_endpoint" value="<?php echo esc_attr( vpg_vision_endpoint() ); ?>" class="regular-text" placeholder="https://vision.internal/caption" style="width:520px">
            <p><button class="button button-primary"><?php esc_html_e( 'Save endpoint', 'vpg-v2' ); ?></button>
            <span class="description"><?php echo vpg_vision_endpoint() ? esc_html__( 'Connected — suggestions are live.', 'vpg-v2' ) : esc_html__( 'Not connected — suggestions are off.', 'vpg-v2' ); ?></span></p>
          </form>
        </div>
        <?php
    } );
}, 19 );

/* Alt-text suggestion button in the attachment editor */
add_action( 'wp_ajax_vpg_vision_suggest', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error( 'forbidden', 403 );
    check_ajax_referer( 'vpg_vision' );
    $s = vpg_vision_suggest( (int) ( $_GET['att'] ?? 0 ) );
    if ( ! $s ) wp_send_json_error( 'off-or-failed' );
    wp_send_json_success( $s );
} );

add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
    if ( ! vpg_vision_endpoint() || ! wp_attachment_is_image( $post->ID ) ) return $fields;
    $nonce = wp_create_nonce( 'vpg_vision' );
    $fields['vpg_vision'] = [
        'label' => __( 'AI suggestion', 'vpg-v2' ),
        'input' => 'html',
        'html'  => '<button type="button" class="button vpg-vision-btn" data-att="' . (int) $post->ID . '" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( 'Suggest alt-text & tags', 'vpg-v2' ) . '</button> <span class="vpg-vision-out" style="display:block;margin-top:6px;color:#50575e"></span>'
             . '<script>(function(){var b=document.querySelector(\'.vpg-vision-btn[data-att="' . (int) $post->ID . '"]\');if(!b||b.dataset.wired)return;b.dataset.wired=1;b.addEventListener("click",function(){var o=b.parentNode.querySelector(".vpg-vision-out");o.textContent="…";fetch(ajaxurl+"?action=vpg_vision_suggest&_ajax_nonce="+b.dataset.nonce+"&att="+b.dataset.att).then(function(r){return r.json()}).then(function(res){if(res&&res.success){o.textContent="Alt: "+res.data.alt+(res.data.tags.length?" · Tags: "+res.data.tags.join(", "):"")}else{o.textContent="No suggestion (endpoint off or failed)."}})})})();</script>',
    ];
    return $fields;
}, 10, 2 );
