<?php
/**
 * VPG v2 — auto-cover-Mockup.
 *
 * When a magazine issue has no featured image, generate a typographic
 * placeholder cover on the fly using GD. Cached in /uploads/vpg-pdf/covers/.
 * Returns a URL that single-magazine + archive can drop into the cover slot.
 *
 * Style: warm-paper bg + big italic Playfair title + issue number in mono caps + ⁕.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function vpg_auto_cover_url( $issue_id ) {
    if ( ! function_exists( 'imagecreatetruecolor' ) ) return ''; // no GD on host
    $issue = get_post( $issue_id );
    if ( ! $issue || $issue->post_type !== 'vpg_magazine' ) return '';

    $upload = wp_upload_dir();
    $dir    = $upload['basedir'] . '/vpg-pdf/covers';
    $url    = $upload['baseurl'] . '/vpg-pdf/covers';
    if ( ! is_dir( $dir ) ) wp_mkdir_p( $dir );

    $file = $dir . '/cover-' . $issue_id . '-' . md5( $issue->post_title . $issue->post_modified ) . '.jpg';
    if ( file_exists( $file ) ) return str_replace( $dir, $url, $file );

    $w = 800; $h = 1100;
    $im = imagecreatetruecolor( $w, $h );

    // Colors · warm cream paper + ink + sienna
    $bg     = imagecolorallocate( $im, 0xF4, 0xEF, 0xE3 );
    $ink    = imagecolorallocate( $im, 0x0F, 0x0A, 0x04 );
    $accent = imagecolorallocate( $im, 0xC8, 0x60, 0x1A );
    $muted  = imagecolorallocate( $im, 0x6B, 0x5A, 0x48 );
    imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

    // Top rule
    imagefilledrectangle( $im, 60, 60, $w - 60, 62, $accent );

    // Subtle noise · scatter dots
    for ( $i = 0; $i < 220; $i++ ) {
        $x = mt_rand( 0, $w - 1 );
        $y = mt_rand( 0, $h - 1 );
        imagesetpixel( $im, $x, $y, imagecolorallocatealpha( $im, 0x6B, 0x5A, 0x48, mt_rand( 105, 125 ) ) );
    }

    $title    = $issue->post_title ?: 'Untitled';
    $issue_no = get_post_meta( $issue_id, '_vpg_issue_number', true ) ?: ( 'No. ' . $issue_id );
    $issue_dt = get_post_meta( $issue_id, '_vpg_issue_date',   true ) ?: get_the_date( 'F Y', $issue_id );

    // Built-in GD fonts only · we don't ship a TTF
    // Issue number top
    imagestring( $im, 4, 60, 80, strtoupper( $issue_no . '  ·  ' . $issue_dt ), $muted );

    // Title block · wrap to lines of ~22 chars, render with imagestring
    $lines  = vpg_wrap_text( strtoupper( $title ), 22 );
    $y_base = 280;
    foreach ( $lines as $i => $line ) {
        imagestring( $im, 5, 60, $y_base + ( $i * 56 ), $line, $ink );
        // double-strike for bold-feel
        imagestring( $im, 5, 61, $y_base + ( $i * 56 ), $line, $ink );
    }

    // Large ⁕ glyph (use 5px font, big block)
    $glyph = '*';
    $gx = ( $w / 2 ) - 6;
    $gy = $h - 260;
    imagestring( $im, 5, $gx, $gy, $glyph, $accent );
    imagestring( $im, 5, $gx + 1, $gy, $glyph, $accent );

    // Footer band
    imagefilledrectangle( $im, 60, $h - 90, $w - 60, $h - 88, $accent );
    imagestring( $im, 3, 60, $h - 80, 'VIENNA PHOTO GROUP', $muted );
    imagestring( $im, 3, $w - 200, $h - 80, 'VIENNAPHOTOGROUP.COM', $muted );

    imagejpeg( $im, $file, 88 );
    imagedestroy( $im );

    return str_replace( $dir, $url, $file );
}

function vpg_wrap_text( $text, $width ) {
    return explode( "\n", wordwrap( $text, $width, "\n", false ) );
}

/* ─── Hook · serve auto-cover when the issue has no thumbnail ── */
add_filter( 'post_thumbnail_html', function ( $html, $post_id ) {
    if ( $html ) return $html;
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'vpg_magazine' ) return $html;
    $url = vpg_auto_cover_url( $post_id );
    if ( ! $url ) return $html;
    return '<img src="' . esc_url( $url ) . '" alt="" class="vpg-auto-cover" loading="lazy">';
}, 10, 2 );

add_filter( 'get_the_post_thumbnail_url', function ( $url, $post, $size ) {
    if ( $url ) return $url;
    if ( ! $post ) return $url;
    $p = get_post( $post );
    if ( ! $p || $p->post_type !== 'vpg_magazine' ) return $url;
    return vpg_auto_cover_url( $p->ID ) ?: $url;
}, 10, 3 );
