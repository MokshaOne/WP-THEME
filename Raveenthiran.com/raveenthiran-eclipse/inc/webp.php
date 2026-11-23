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

/* ── Bulk pre-generate: Tools → Generate WebP ──────────────────────────────
 * On-demand conversion is capped per request, so galleries end up with partial
 * WebP sources. This admin page bakes a .webp twin for every jpg/png sub-size
 * of every image (batched via AJAX so it never times out on shared hosting). */
add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Generate WebP', 'raveenthiran' ),
		__( 'Generate WebP', 'raveenthiran' ),
		'manage_options',
		'nr-webp',
		'nr_webp_admin_page'
	);
} );

function nr_webp_admin_page() {
	$total = (int) ( new WP_Query( [
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] ) )->found_posts;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Generate WebP', 'raveenthiran' ); ?></h1>
		<p style="max-width:680px"><?php esc_html_e( 'Creates a smaller WebP version of every image size so the theme can serve WebP to supporting browsers (with a JPEG/AVIF fallback). Safe to run repeatedly — it skips files that already have a WebP twin.', 'raveenthiran' ); ?></p>
		<?php if ( ! nr_webp_ok() ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'GD with WebP support is not available on this server — cannot generate WebP.', 'raveenthiran' ); ?></p></div>
		<?php else : ?>
			<p><strong><?php printf( esc_html__( '%d image attachments found.', 'raveenthiran' ), $total ); ?></strong></p>
			<p><button id="nr-webp-go" class="button button-primary"><?php esc_html_e( 'Start generating', 'raveenthiran' ); ?></button></p>
			<div style="max-width:520px;height:18px;background:#e5e5e5;border-radius:9px;overflow:hidden;margin:10px 0">
				<div id="nr-webp-bar" style="height:100%;width:0;background:#F2A03D;transition:width .3s"></div>
			</div>
			<p id="nr-webp-status" style="font-weight:600"></p>
			<p class="description"><?php esc_html_e( 'When it finishes, purge your page cache (W3TC) and Cloudflare so the new WebP images are served.', 'raveenthiran' ); ?></p>
			<script>
			(function () {
				var btn = document.getElementById('nr-webp-go');
				var bar = document.getElementById('nr-webp-bar');
				var st  = document.getElementById('nr-webp-status');
				var nonce = '<?php echo esc_js( wp_create_nonce( 'nr_webp_bulk' ) ); ?>';
				var madeTotal = 0;
				function run(offset) {
					fetch(ajaxurl + '?action=nr_webp_bulk&nonce=' + nonce + '&offset=' + offset, { credentials: 'same-origin' })
						.then(function (r) { return r.json(); })
						.then(function (d) {
							if (!d || d.success === false) { st.textContent = 'Error — please reload and try again.'; btn.disabled = false; return; }
							madeTotal += (d.images || 0);
							var pct = d.total ? Math.round(d.processed / d.total * 100) : 100;
							bar.style.width = pct + '%';
							st.textContent = d.processed + ' / ' + d.total + ' attachments · ' + madeTotal + ' WebP files';
							if (d.more) { run(d.processed); }
							else { st.textContent = '✓ Done — ' + madeTotal + ' WebP files created. Now purge W3TC + Cloudflare cache.'; btn.disabled = false; }
						})
						.catch(function () { st.textContent = 'Network error — click Start to resume.'; btn.disabled = false; });
				}
				btn.addEventListener('click', function () { btn.disabled = true; madeTotal = 0; run(0); });
			})();
			</script>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'wp_ajax_nr_webp_bulk', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['nonce'] ?? '', 'nr_webp_bulk' ) ) {
		wp_send_json_error();
	}
	if ( ! nr_webp_ok() ) wp_send_json_error();
	@set_time_limit( 0);
	$batch  = 20;
	$offset = max( 0, (int) ( $_GET['offset'] ?? 0 ) );

	$q = new WP_Query( [
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => 'image',
		'posts_per_page' => $batch,
		'offset'         => $offset,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'fields'         => 'ids',
	] );

	$images = 0;
	foreach ( $q->posts as $id ) {
		$file = get_attached_file( $id );
		if ( ! $file ) continue;
		$dir  = trailingslashit( dirname( $file ) );
		$todo = [];
		if ( preg_match( '/\.(jpe?g|png)$/i', $file ) ) $todo[] = $file;
		$meta = wp_get_attachment_metadata( $id );
		if ( ! empty( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $s ) {
				if ( ! empty( $s['file'] ) ) $todo[] = $dir . $s['file'];
			}
		}
		foreach ( $todo as $p ) {
			if ( ! preg_match( '/\.(jpe?g|png)$/i', $p ) || ! file_exists( $p ) ) continue;
			if ( file_exists( $p . '.webp' ) ) continue;        // skip already done
			if ( nr_webp_make( $p, $p . '.webp' ) ) $images++;
		}
	}

	$processed = $offset + count( $q->posts );
	wp_send_json( [
		'processed' => $processed,
		'total'     => (int) $q->found_posts,
		'images'    => $images,
		'more'      => $processed < (int) $q->found_posts,
	] );
} );
