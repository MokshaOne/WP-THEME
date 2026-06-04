<?php
/**
 * Theme-side WebP delivery (no plugin).
 *
 * The site uploads AVIF originals, but the server can't write AVIF sub-sizes,
 * so WordPress generates JPEG sub-sizes — and those JPEGs get served, costing
 * ~100-370 KiB on the hero (see PageSpeed). This module:
 *
 *   1. makes a `.webp` twin next to every jpg/png sub-size (at upload, and
 *      on-demand for already-uploaded media, capped per request);
 *   2. centrally wraps every wp_get_attachment_image() / get_the_post_thumbnail()
 *      output in <picture> with a <source type="image/webp">, so supporting
 *      browsers fetch WebP and everyone else falls back to the JPEG <img>.
 *
 * Cache-safe (real <picture>, no Accept-header trickery) and requires no
 * template changes — get_the_post_thumbnail() routes through the same filter.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function nr_webp_ok() {
	static $ok = null;
	if ( $ok === null ) $ok = function_exists( 'imagewebp' ) && function_exists( 'imagecreatefromjpeg' );
	return $ok;
}

/* Create a .webp twin at $webp from a jpg/png $path. */
function nr_webp_make( $path, $webp ) {
	if ( ! nr_webp_ok() || ! file_exists( $path ) ) return false;
	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$img = false;
	if ( $ext === 'jpg' || $ext === 'jpeg' ) {
		$img = @imagecreatefromjpeg( $path );
	} elseif ( $ext === 'png' ) {
		$img = @imagecreatefrompng( $path );
		if ( $img ) { @imagepalettetotruecolor( $img ); @imagealphablending( $img, false ); @imagesavealpha( $img, true ); }
	}
	if ( ! $img ) return false;
	$ok = @imagewebp( $img, $webp, 82 );
	imagedestroy( $img );
	return $ok && file_exists( $webp );
}

/* For an uploads JPG/PNG URL, return its .webp twin URL (generating it on
 * demand, capped per request to avoid timeouts), or '' if not available. */
function nr_webp_twin_url( $url ) {
	if ( ! nr_webp_ok() || ! is_string( $url ) || $url === '' ) return '';
	if ( ! preg_match( '/\.(jpe?g|png)$/i', $url ) ) return '';
	$up = wp_get_upload_dir();
	if ( strpos( $url, $up['baseurl'] ) !== 0 ) return '';          // uploads only
	$path = $up['basedir'] . substr( $url, strlen( $up['baseurl'] ) );
	if ( ! file_exists( $path ) ) return '';
	$webp = $path . '.webp';
	if ( file_exists( $webp ) ) return $url . '.webp';

	static $made = 0;
	if ( $made >= 8 ) return '';                                    // cap on-demand work
	if ( nr_webp_make( $path, $webp ) ) { $made++; return $url . '.webp'; }
	return '';
}

/* Swap every candidate of a srcset string to its webp twin (dropping any
 * candidate that has no twin). Returns '' if none could be converted. */
function nr_webp_swap_srcset( $srcset ) {
	$out = [];
	foreach ( explode( ',', (string) $srcset ) as $part ) {
		$part = trim( $part );
		if ( $part === '' ) continue;
		$sp   = preg_split( '/\s+/', $part );
		$twin = nr_webp_twin_url( $sp[0] );
		if ( $twin ) { $sp[0] = $twin; $out[] = implode( ' ', $sp ); }
	}
	return implode( ', ', $out );
}

/* Generate twins for a new upload's sub-sizes (regardless of original format,
 * since the sub-sizes are the JPEGs that actually get served). */
add_filter( 'wp_generate_attachment_metadata', function ( $metadata, $attachment_id ) {
	if ( ! nr_webp_ok() ) return $metadata;
	$file = get_attached_file( $attachment_id );
	if ( ! $file ) return $metadata;
	$dir = trailingslashit( dirname( $file ) );
	if ( preg_match( '/\.(jpe?g|png)$/i', $file ) && file_exists( $file ) ) {
		nr_webp_make( $file, $file . '.webp' );
	}
	if ( ! empty( $metadata['sizes'] ) ) {
		foreach ( $metadata['sizes'] as $s ) {
			if ( empty( $s['file'] ) ) continue;
			$p = $dir . $s['file'];
			if ( preg_match( '/\.(jpe?g|png)$/i', $p ) && file_exists( $p ) ) {
				nr_webp_make( $p, $p . '.webp' );
			}
		}
	}
	return $metadata;
}, 10, 2 );

/* Central delivery: wrap the <img> in <picture> with a webp <source>. */
add_filter( 'wp_get_attachment_image', function ( $html, $attachment_id, $size, $icon, $attr ) {
	if ( is_admin() || is_feed() || ! $html || strpos( $html, '<picture' ) !== false ) return $html;
	if ( ! nr_webp_ok() ) return $html;

	$srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
	if ( $srcset ) {
		$webpset = nr_webp_swap_srcset( $srcset );
	} else {
		$u   = wp_get_attachment_image_url( $attachment_id, $size );
		$twin = $u ? nr_webp_twin_url( $u ) : '';
		$webpset = $twin ?: '';
	}
	if ( ! $webpset ) return $html;

	$sizes = '';
	if ( preg_match( '/sizes="([^"]+)"/', $html, $m ) ) $sizes = $m[1];
	$source = '<source type="image/webp" srcset="' . esc_attr( $webpset ) . '"'
		. ( $sizes ? ' sizes="' . esc_attr( $sizes ) . '"' : '' ) . '>';
	return '<picture>' . $source . $html . '</picture>';
}, 10, 5 );
