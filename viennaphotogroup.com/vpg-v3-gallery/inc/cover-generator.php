<?php
/**
 * VPG v3 — auto-cover-Mockup · Gallery edition.
 *
 * When a magazine issue has no featured image, generate a typographic
 * placeholder cover on the fly using GD. Cached in /uploads/vpg-pdf/covers/.
 * Returns a URL that single-magazine + archive can drop into the cover slot.
 *
 * Style: the site's own look — white ground, near-black Archivo Expanded
 * uppercase title, one red square + red rules, wall-label meta line.
 * Uses the theme's Archivo TTFs (assets/fonts) when present; falls back to
 * GD's built-in fonts otherwise.
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

    $file = $dir . '/cover-' . $issue_id . '-' . md5( $issue->post_title . $issue->post_modified . 'g3' ) . '.jpg';
    if ( file_exists( $file ) ) return str_replace( $dir, $url, $file );

    $w = 800; $h = 1100;
    $im = imagecreatetruecolor( $w, $h );

    // Gallery palette · white ground, near-black ink, one red, grey meta
    $bg    = imagecolorallocate( $im, 0xFF, 0xFF, 0xFF );
    $ink   = imagecolorallocate( $im, 0x0B, 0x0B, 0x0B );
    $red   = imagecolorallocate( $im, 0xE5, 0x34, 0x1F );
    $muted = imagecolorallocate( $im, 0x6A, 0x6A, 0x6A );
    imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

    $title    = $issue->post_title ?: 'Untitled';
    $issue_no = get_post_meta( $issue_id, '_vpg_issue_number', true ) ?: ( 'No. ' . $issue_id );
    $issue_dt = get_post_meta( $issue_id, '_vpg_issue_date',   true ) ?: get_the_date( 'F Y', $issue_id );

    $display = VPG_V2_DIR . '/assets/fonts/ArchivoExpanded-900.ttf';
    $label   = VPG_V2_DIR . '/assets/fonts/Archivo-500.ttf';
    $use_ttf = function_exists( 'imagettftext' ) && file_exists( $display ) && file_exists( $label );

    // Red square top-left · the brand mark
    imagefilledrectangle( $im, 60, 60, 84, 84, $red );

    if ( $use_ttf ) {
        // Masthead strip · red brand period
        imagettftext( $im, 15, 0, 104, 80, $ink, $label, 'VIENNAPHOTOGROUP' );
        $mb = imagettfbbox( 15, 0, $label, 'VIENNAPHOTOGROUP' );
        imagettftext( $im, 15, 0, 104 + ( $mb[2] - $mb[0] ) + 2, 80, $red, $label, '.' );
        imagettftext( $im, 13, 0, 60, 130, $muted, $label, strtoupper( $issue_no . '  ·  ' . $issue_dt ) );
        // Ink rule under the strip
        imagefilledrectangle( $im, 60, 150, $w - 60, 152, $ink );

        // Display title · Archivo Expanded 900 uppercase, wrapped to width
        $size   = 64;
        $lines  = vpg_wrap_ttf( strtoupper( $title ), $display, $size, $w - 120 );
        while ( count( $lines ) > 6 && $size > 34 ) { // very long titles step down
            $size -= 8;
            $lines = vpg_wrap_ttf( strtoupper( $title ), $display, $size, $w - 120 );
        }
        $line_h = (int) round( $size * 1.12 );
        $y      = 150 + 90;
        foreach ( $lines as $line ) {
            $y += $line_h;
            imagettftext( $im, $size, 0, 58, $y, $ink, $display, $line );
        }
        // Red period after the last line
        $bb = imagettfbbox( $size, 0, $display, end( $lines ) );
        imagettftext( $im, $size, 0, 58 + ( $bb[2] - $bb[0] ) + 6, $y, $red, $display, '.' );

        // Footer · hairline + wall label
        imagesetthickness( $im, 1 );
        imageline( $im, 60, $h - 110, $w - 60, $h - 110, $ink );
        imagettftext( $im, 12, 0, 60, $h - 74, $muted, $label, 'A MEMBER-RUN PHOTOGRAPHY MAGAZINE · WIEN' );
        imagettftext( $im, 12, 0, $w - 260, $h - 74, $muted, $label, 'VIENNAPHOTOGROUP.COM' );
    } else {
        // GD built-in fallback · same composition, humbler type
        imagestring( $im, 5, 104, 62, 'VIENNAPHOTOGROUP.', $ink );
        imagestring( $im, 3, 60, 110, strtoupper( $issue_no . '  .  ' . $issue_dt ), $muted );
        imagefilledrectangle( $im, 60, 140, $w - 60, 142, $ink );
        $lines  = vpg_wrap_text( strtoupper( $title ), 22 );
        $y_base = 220;
        foreach ( $lines as $i => $line ) {
            imagestring( $im, 5, 60, $y_base + ( $i * 40 ), $line, $ink );
            imagestring( $im, 5, 61, $y_base + ( $i * 40 ), $line, $ink );
        }
        imageline( $im, 60, $h - 110, $w - 60, $h - 110, $ink );
        imagestring( $im, 3, 60, $h - 90, 'A MEMBER-RUN PHOTOGRAPHY MAGAZINE . WIEN', $muted );
    }

    imagejpeg( $im, $file, 90 );
    imagedestroy( $im );

    return str_replace( $dir, $url, $file );
}

/* Wrap uppercase text to a pixel width for a given TTF + size */
function vpg_wrap_ttf( $text, $font, $size, $max_px ) {
    $words = preg_split( '/\s+/', trim( $text ) ) ?: [];
    $lines = [];
    $cur   = '';
    foreach ( $words as $word ) {
        $try = $cur === '' ? $word : $cur . ' ' . $word;
        $bb  = imagettfbbox( $size, 0, $font, $try );
        if ( ( $bb[2] - $bb[0] ) > $max_px && $cur !== '' ) {
            $lines[] = $cur;
            $cur     = $word;
        } else {
            $cur = $try;
        }
    }
    if ( $cur !== '' ) $lines[] = $cur;
    return $lines ?: [ '' ];
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

/* ════════════════════════════════════════════════════════════════ */
/*  OG share cards · 1200×630 branded card for any singular content  */
/*  without a featured image. Same Gallery composition, landscape.   */
/* ════════════════════════════════════════════════════════════════ */
function vpg_og_card_url( $post_id ) {
    if ( ! function_exists( 'imagecreatetruecolor' ) ) return '';
    $post = get_post( $post_id );
    if ( ! $post ) return '';

    $upload = wp_upload_dir();
    $dir    = $upload['basedir'] . '/vpg-pdf/og';
    $url    = $upload['baseurl'] . '/vpg-pdf/og';
    if ( ! is_dir( $dir ) ) wp_mkdir_p( $dir );

    $file = $dir . '/og-' . $post_id . '-' . md5( $post->post_title . $post->post_modified ) . '.jpg';
    if ( file_exists( $file ) ) return str_replace( $dir, $url, $file );

    $w = 1200; $h = 630;
    $im = imagecreatetruecolor( $w, $h );
    $bg    = imagecolorallocate( $im, 0xFF, 0xFF, 0xFF );
    $ink   = imagecolorallocate( $im, 0x0B, 0x0B, 0x0B );
    $red   = imagecolorallocate( $im, 0xE5, 0x34, 0x1F );
    $muted = imagecolorallocate( $im, 0x6A, 0x6A, 0x6A );
    imagefilledrectangle( $im, 0, 0, $w, $h, $bg );

    $display = VPG_V2_DIR . '/assets/fonts/ArchivoExpanded-900.ttf';
    $label   = VPG_V2_DIR . '/assets/fonts/Archivo-500.ttf';

    $type_obj  = get_post_type_object( $post->post_type );
    $type_name = $type_obj ? $type_obj->labels->singular_name : '';

    imagefilledrectangle( $im, 70, 64, 94, 88, $red );
    if ( function_exists( 'imagettftext' ) && file_exists( $display ) && file_exists( $label ) ) {
        imagettftext( $im, 17, 0, 114, 86, $ink, $label, 'VIENNAPHOTOGROUP' );
        $mb = imagettfbbox( 17, 0, $label, 'VIENNAPHOTOGROUP' );
        imagettftext( $im, 17, 0, 114 + ( $mb[2] - $mb[0] ) + 2, 86, $red, $label, '.' );
        if ( $type_name ) imagettftext( $im, 13, 0, 70, 132, $muted, $label, strtoupper( $type_name ) );
        imagefilledrectangle( $im, 70, 152, $w - 70, 154, $ink );

        $size  = 52;
        $lines = vpg_wrap_ttf( strtoupper( $post->post_title ?: 'Untitled' ), $display, $size, $w - 150 );
        while ( count( $lines ) > 4 && $size > 30 ) {
            $size -= 6;
            $lines = vpg_wrap_ttf( strtoupper( $post->post_title ), $display, $size, $w - 150 );
        }
        $line_h = (int) round( $size * 1.14 );
        $y      = 190;
        foreach ( array_slice( $lines, 0, 4 ) as $line ) {
            $y += $line_h;
            imagettftext( $im, $size, 0, 68, $y, $ink, $display, $line );
        }
        imageline( $im, 70, $h - 76, $w - 70, $h - 76, $ink );
        imagettftext( $im, 12, 0, 70, $h - 44, $muted, $label, 'A MEMBER-RUN PHOTOGRAPHY MAGAZINE · WIEN' );
        imagettftext( $im, 12, 0, $w - 300, $h - 44, $muted, $label, 'VIENNAPHOTOGROUP.COM' );
    } else {
        imagestring( $im, 5, 114, 66, 'VIENNAPHOTOGROUP.', $ink );
        $lines = vpg_wrap_text( strtoupper( $post->post_title ?: 'Untitled' ), 34 );
        foreach ( array_slice( $lines, 0, 5 ) as $i => $line ) {
            imagestring( $im, 5, 70, 180 + $i * 34, $line, $ink );
        }
    }

    imagejpeg( $im, $file, 90 );
    imagedestroy( $im );
    return str_replace( $dir, $url, $file );
}
