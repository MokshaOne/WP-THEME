<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Performance Features:
 * FEATURE 20 — LQIP (Low Quality Image Placeholders)
 * FEATURE 21 — WebP Auto-Konvertierung
 * FEATURE 19 — EXIF-Daten auslesen
 * FEATURE 24 — Object Cache Hinweise
 */

/* =============================================================================
   FEATURE 21 — WebP Auto-Konvertierung beim Upload
   ============================================================================= */

add_filter( 'wp_handle_upload', 'nr_convert_to_webp' );

function nr_convert_to_webp( $upload ) {
    // Nur JPEG und PNG
    if ( ! in_array( $upload['type'], [ 'image/jpeg', 'image/png' ], true ) ) {
        return $upload;
    }

    // GD oder Imagick prüfen
    if ( ! function_exists( 'imagecreatefromjpeg' ) && ! class_exists( 'Imagick' ) ) {
        return $upload;
    }

    $source   = $upload['file'];
    $webp_path = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $source );

    $converted = false;

    // Imagick bevorzugen (bessere Qualität)
    if ( class_exists( 'Imagick' ) ) {
        try {
            $imagick = new Imagick( $source );
            $imagick->setImageFormat( 'webp' );
            $imagick->setImageCompressionQuality( 82 );
            $imagick->writeImage( $webp_path );
            $imagick->clear();
            $converted = true;
        } catch ( Exception $e ) {
            // Fallback auf GD
        }
    }

    // GD Fallback
    if ( ! $converted && function_exists( 'imagewebp' ) ) {
        if ( $upload['type'] === 'image/jpeg' ) {
            $img = imagecreatefromjpeg( $source );
        } else {
            $img = imagecreatefrompng( $source );
            imagealphablending( $img, true );
            imagesavealpha( $img, true );
        }
        if ( $img ) {
            imagewebp( $img, $webp_path, 82 );
            imagedestroy( $img );
            $converted = true;
        }
    }

    // Wenn WebP kleiner ist → Original durch WebP ersetzen
    if ( $converted && file_exists( $webp_path ) && filesize( $webp_path ) < filesize( $source ) ) {
        unlink( $source );
        rename( $webp_path, preg_replace( '/\.(jpe?g|png)$/i', '.webp', $source ) );
        $upload['file'] = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $source );
        $upload['url']  = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $upload['url'] );
        $upload['type'] = 'image/webp';
    } elseif ( file_exists( $webp_path ) ) {
        // WebP war nicht kleiner → WebP trotzdem behalten als Alternative
        // (Originalformat bleibt, WebP als Zusatz)
        unlink( $webp_path );
    }

    return $upload;
}

/* =============================================================================
   FEATURE 20 — LQIP generieren beim Upload + in post_meta speichern
   ============================================================================= */

add_action( 'add_attachment', 'nr_generate_lqip' );

function nr_generate_lqip( $attachment_id ) {
    $mime = get_post_mime_type( $attachment_id );
    if ( ! in_array( $mime, [ 'image/jpeg', 'image/png', 'image/webp' ], true ) ) return;

    $file = get_attached_file( $attachment_id );
    if ( ! $file || ! file_exists( $file ) ) return;

    // Mit WP Image Editor auf 20×20 verkleinern
    $editor = wp_get_image_editor( $file );
    if ( is_wp_error( $editor ) ) return;

    $editor->resize( 20, 20, true );
    $tmp_file = sys_get_temp_dir() . '/nr_lqip_' . $attachment_id . '.jpg';
    $result   = $editor->save( $tmp_file, 'image/jpeg' );

    if ( is_wp_error( $result ) || ! file_exists( $tmp_file ) ) return;

    $base64 = 'data:image/jpeg;base64,' . base64_encode( file_get_contents( $tmp_file ) );
    unlink( $tmp_file );

    update_post_meta( $attachment_id, '_nr_lqip', $base64 );
}

/**
 * LQIP für ein Attachment abrufen
 */
function nr_get_lqip( $attachment_id ) {
    return get_post_meta( $attachment_id, '_nr_lqip', true );
}

/**
 * Bild-Tag mit LQIP-Blur-Up ausgeben
 */
function nr_image_with_lqip( $attachment_id, $size = 'large', $attrs = [] ) {
    $lqip = nr_get_lqip( $attachment_id );
    $src  = wp_get_attachment_image_src( $attachment_id, $size );
    if ( ! $src ) return wp_get_attachment_image( $attachment_id, $size, false, $attrs );

    $default_attrs = [
        'loading' => 'lazy',
        'decoding' => 'async',
    ];
    $attrs = array_merge( $default_attrs, $attrs );

    if ( $lqip ) {
        $attrs['style'] = ( $attrs['style'] ?? '' ) . 'background-image:url(' . $lqip . ');background-size:cover;';
        $attrs['class'] = ( $attrs['class'] ?? '' ) . ' nr-lqip-img';
    }

    return wp_get_attachment_image( $attachment_id, $size, false, $attrs );
}

/* =============================================================================
   FEATURE 19 — EXIF-Daten beim Upload auslesen
   ============================================================================= */

add_action( 'add_attachment', 'nr_extract_exif' );

function nr_extract_exif( $attachment_id ) {
    $mime = get_post_mime_type( $attachment_id );
    if ( ! in_array( $mime, [ 'image/jpeg' ], true ) ) return;

    $file = get_attached_file( $attachment_id );
    if ( ! $file || ! function_exists( 'exif_read_data' ) ) return;

    $exif = @exif_read_data( $file, 'EXIF', false );
    if ( ! $exif ) return;

    $data = [];

    if ( ! empty( $exif['Model'] ) )            $data['camera']   = sanitize_text_field( $exif['Model'] );
    if ( ! empty( $exif['FocalLength'] ) )       $data['focal']    = nr_exif_fraction( $exif['FocalLength'] ) . 'mm';
    if ( ! empty( $exif['FNumber'] ) )           $data['aperture'] = 'f/' . nr_exif_fraction( $exif['FNumber'] );
    if ( ! empty( $exif['ExposureTime'] ) )      $data['shutter']  = nr_exif_fraction( $exif['ExposureTime'] ) . 's';
    if ( ! empty( $exif['ISOSpeedRatings'] ) )   $data['iso']      = 'ISO ' . (int) ( is_array( $exif['ISOSpeedRatings'] ) ? $exif['ISOSpeedRatings'][0] : $exif['ISOSpeedRatings'] );
    if ( ! empty( $exif['LensModel'] ) )         $data['lens']     = sanitize_text_field( $exif['LensModel'] );

    if ( ! empty( $data ) ) {
        update_post_meta( $attachment_id, '_nr_exif', $data );
    }
}

function nr_exif_fraction( $value ) {
    if ( is_array( $value ) ) {
        $parts = explode( '/', $value );
    } else {
        $parts = explode( '/', (string) $value );
    }
    if ( count( $parts ) === 2 && (float) $parts[1] !== 0.0 ) {
        $result = (float) $parts[0] / (float) $parts[1];
        // Verschlusszeit als Bruch anzeigen wenn < 1s
        if ( $result < 1 && $result > 0 ) {
            return '1/' . round( 1 / $result );
        }
        return round( $result, 1 );
    }
    return $value;
}

function nr_get_exif( $attachment_id ) {
    return get_post_meta( $attachment_id, '_nr_exif', true ) ?: [];
}


