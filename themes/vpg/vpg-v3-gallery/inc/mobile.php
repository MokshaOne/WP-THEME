<?php
/**
 * VPG v3 — Q7 · phone-first submission.
 *
 *   0601  Quick pin · one screen: photo, position, done (handler)
 *   0604  Offline drafts · submit form autosaves to localStorage
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 0601 · Quick-pin handler · creates a pending location ──────── */
add_action( 'admin_post_vpg_quickpin', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_quickpin' );
    if ( function_exists( 'vpg_is_verified' ) && ! vpg_is_verified() ) wp_die( 'Verify your email first.', 403 );

    $title = mb_substr( sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ), 0, 120 );
    $note  = sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) );
    $lat   = (float) ( $_POST['pin_lat'] ?? 0 );
    $lng   = (float) ( $_POST['pin_lng'] ?? 0 );
    if ( ! $title ) { wp_safe_redirect( wp_get_referer() ?: home_url() ); exit; }

    $post_id = wp_insert_post( [
        'post_type'    => 'vpg_location',
        'post_status'  => 'pending',                 // quick never skips review
        'post_title'   => $title,
        'post_content' => $note,
        'post_author'  => get_current_user_id(),
    ] );
    if ( ! $post_id || is_wp_error( $post_id ) ) { wp_safe_redirect( wp_get_referer() ?: home_url() ); exit; }

    if ( $lat && $lng && $lat > 47 && $lat < 49 && $lng > 15 && $lng < 17.5 ) {   // greater Vienna
        update_post_meta( $post_id, 'location_lat', round( $lat, 6 ) );
        update_post_meta( $post_id, 'location_lng', round( $lng, 6 ) );
    }
    update_post_meta( $post_id, '_vpg_submitted_at', current_time( 'mysql' ) );
    update_post_meta( $post_id, '_vpg_quickpin', '1' );

    // The one photo becomes the featured image.
    if ( ! empty( $_FILES['photo']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        $check = wp_check_filetype( $_FILES['photo']['name'] );
        if ( in_array( strtolower( (string) $check['ext'] ), [ 'jpg', 'jpeg', 'png', 'webp', 'avif', 'heic' ], true )
            && (int) $_FILES['photo']['size'] <= 12 * MB_IN_BYTES ) {
            $att = media_handle_upload( 'photo', $post_id );
            if ( ! is_wp_error( $att ) ) {
                set_post_thumbnail( $post_id, $att );
                // 1020 · no device pin? read the photo's own GPS before we strip it
                if ( ! get_post_meta( $post_id, 'location_lat', true ) ) {
                    $geo = vpg_exif_latlng( get_attached_file( $att ) );
                    if ( $geo && $geo[0] > 47 && $geo[0] < 49 && $geo[1] > 15 && $geo[1] < 17.5 ) {
                        update_post_meta( $post_id, 'location_lat', round( $geo[0], 6 ) );
                        update_post_meta( $post_id, 'location_lng', round( $geo[1], 6 ) );
                    }
                }
                if ( function_exists( 'vpg_strip_and_credit_photo' ) ) vpg_strip_and_credit_photo( $att );
            }
        }
    }

    if ( function_exists( 'vpg_redirect_with_status' ) ) {
        vpg_redirect_with_status( 'dashboard', 'submitted' );
    }
    wp_safe_redirect( home_url( '/dashboard/' ) );
    exit;
} );

/* ─── 0604 · Offline drafts · autosave the submit form locally ───── */
add_action( 'wp_footer', function () {
    if ( ! is_page_template( 'templates/page-submit.php' ) ) return;
    ?>
    <script>
    /* 0604 · nothing typed on a windy Fototour gets lost — drafts live
       in this browser only and clear once the submission goes through. */
    (function () {
        var form = document.querySelector('form[enctype], form[method="post"]');
        if (!form) return;
        var KEY = 'vpg_draft_submit', t = 0, restored = false;

        function fields() {
            return Array.prototype.filter.call(form.querySelectorAll('input, textarea, select'), function (el) {
                return el.name && ['hidden', 'file', 'submit', 'password'].indexOf(el.type) === -1;
            });
        }
        function save() {
            var data = {};
            fields().forEach(function (el) {
                if (el.type === 'checkbox' || el.type === 'radio') { if (el.checked) data[el.name + '::' + el.value] = '1'; }
                else if (el.value) data[el.name] = el.value;
            });
            try { localStorage.setItem(KEY, JSON.stringify(data)); } catch (e) {}
        }
        function restore() {
            var data;
            try { data = JSON.parse(localStorage.getItem(KEY)); } catch (e) {}
            if (!data) return;
            var used = false;
            fields().forEach(function (el) {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (data[el.name + '::' + el.value]) { el.checked = true; used = true; }
                } else if (!el.value && data[el.name]) { el.value = data[el.name]; used = true; }
            });
            if (used) {
                restored = true;
                var note = document.createElement('p');
                note.style.cssText = 'position:fixed;left:16px;bottom:16px;z-index:70;background:#0B0B0B;color:#fff;padding:10px 16px;font-size:12px;font-weight:700;margin:0';
                note.textContent = <?php echo wp_json_encode( __( '✎ Draft restored from this device.', 'vpg-v2' ) ); ?>;
                document.body.appendChild(note);
                setTimeout(function () { note.remove(); }, 5000);
            }
        }
        form.addEventListener('input', function () { clearTimeout(t); t = setTimeout(save, 400); });
        form.addEventListener('submit', function () { try { localStorage.removeItem(KEY); } catch (e) {} });
        restore();

        // Honest offline banner — the form still accepts typing.
        function connectivity() {
            var b = document.getElementById('vpg-offline-note');
            if (!navigator.onLine) {
                if (!b) {
                    b = document.createElement('p');
                    b.id = 'vpg-offline-note';
                    b.style.cssText = 'position:fixed;left:0;right:0;top:0;z-index:80;background:#E5341F;color:#fff;padding:8px 16px;text-align:center;font-size:12px;font-weight:800;margin:0';
                    b.textContent = <?php echo wp_json_encode( __( 'Offline — keep writing, your draft is saved on this device.', 'vpg-v2' ) ); ?>;
                    document.body.appendChild(b);
                }
            } else if (b) { b.remove(); }
        }
        addEventListener('online', connectivity);
        addEventListener('offline', connectivity);
        connectivity();
    })();
    </script>
    <?php
}, 20 );


/* ─── 1020 · pull decimal lat/lng from a JPEG's EXIF GPS block ───── */
function vpg_exif_latlng( $file ) {
    if ( ! $file || ! function_exists( 'exif_read_data' ) ) return null;
    if ( ! in_array( strtolower( (string) pathinfo( $file, PATHINFO_EXTENSION ) ), [ 'jpg', 'jpeg', 'tiff' ], true ) ) return null;
    $exif = @exif_read_data( $file );
    if ( ! $exif || empty( $exif['GPSLatitude'] ) || empty( $exif['GPSLongitude'] ) ) return null;

    $dms = function ( $parts, $ref ) {
        $frac = function ( $v ) {
            if ( strpos( (string) $v, '/' ) === false ) return (float) $v;
            [ $n, $d ] = explode( '/', $v );
            return $d ? (float) $n / (float) $d : 0.0;
        };
        $deg = $frac( $parts[0] ) + $frac( $parts[1] ) / 60 + $frac( $parts[2] ) / 3600;
        return in_array( strtoupper( (string) $ref ), [ 'S', 'W' ], true ) ? -$deg : $deg;
    };
    $lat = $dms( $exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N' );
    $lng = $dms( $exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E' );
    if ( ! $lat || ! $lng ) return null;
    return [ $lat, $lng ];
}
