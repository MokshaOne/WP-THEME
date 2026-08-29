<?php
/**
 * VPG v3 — Backlog (1029–1040): the deferred ideas, now built.
 *
 * Small, self-contained finishers that lean on infrastructure the clusters
 * already shipped. The federation-actor icon (1036), inbox Announce/Like
 * counting (1037), the annual cover (1035), the TTS controls (1038) and the
 * backup-codes .txt (1039) live in their home files; this file holds:
 *
 *   1029 follower list + federation switch · 1031 format buttons on the archive
 *   1032 vision stack (queue the whole library + the nightly batch consumer)
 *   1033 discover-by-image landing · 1034 keep the original before an edit
 *   1037 boosts/likes shown on the post · 1040 count surfaced (see open-data.php)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * 1029 · federation on/off switch (honoured by vpg_federation_enabled)
 * ================================================================ */
add_filter( 'vpg_federation_enabled', fn( $on ) => get_option( 'vpg_fed_off' ) ? false : $on );

/* ================================================================
 * 1031 · EPUB / pocket-zine buttons on the magazine archive too
 * ================================================================ */
add_filter( 'the_excerpt', function ( $excerpt ) {
    if ( ! is_post_type_archive( 'vpg_magazine' ) || ! in_the_loop() ) return $excerpt;
    $id = get_the_ID();
    $epub = admin_url( 'admin-post.php?action=vpg_epub&issue=' . $id );
    $zine = admin_url( 'admin-post.php?action=vpg_zine&issue=' . $id );
    $btns = '<p class="vpg-mag-formats" style="margin:8px 0 0;display:flex;gap:8px;flex-wrap:wrap">'
          . '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( $epub ) . '">📖 ' . esc_html__( 'EPUB', 'vpg-v2' ) . '</a>'
          . '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( $zine ) . '">🗞 ' . esc_html__( 'Pocket zine', 'vpg-v2' ) . '</a></p>';
    return $excerpt . $btns;
}, 20 );

/* ================================================================
 * 1037 · Fediverse resonance — show boosts & likes on the post
 * ================================================================ */
add_filter( 'the_content', function ( $c ) {
    if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) return $c;
    $boosts = (int) get_post_meta( get_the_ID(), '_vpg_fed_boosts', true );
    $likes  = (int) get_post_meta( get_the_ID(), '_vpg_fed_likes', true );
    if ( ! $boosts && ! $likes ) return $c;
    $bits = [];
    if ( $boosts ) $bits[] = '🔁 ' . esc_html( sprintf( _n( '%s boost', '%s boosts', $boosts, 'vpg-v2' ), number_format_i18n( $boosts ) ) );
    if ( $likes )  $bits[] = '⭐ ' . esc_html( sprintf( _n( '%s like', '%s likes', $likes, 'vpg-v2' ), number_format_i18n( $likes ) ) );
    return $c . '<p class="vpg-fed-resonance" style="font-size:12px;color:var(--g-mid,#6A6A6A);margin-top:14px">' . esc_html__( 'From the Fediverse:', 'vpg-v2' ) . ' ' . implode( ' · ', $bits ) . '</p>';
}, 42 );

/* ================================================================
 * 1032 · vision stack — process the whole library in the nightly batch
 * ================================================================ */
/* consumer for the bundled queue (fed by vpg_ai_enqueue in ai-assist.php) */
add_action( 'vpg_ai_batch_run', function ( $queue ) {
    if ( ! function_exists( 'vpg_vision_suggest' ) || ! vpg_vision_endpoint() ) return;
    $done = 0;
    foreach ( (array) $queue as $job ) {
        if ( ( $job['type'] ?? '' ) !== 'alt' || $done >= 60 ) continue; // cap one night's run
        $aid = (int) ( $job['id'] ?? 0 );
        if ( ! $aid || trim( (string) get_post_meta( $aid, '_wp_attachment_image_alt', true ) ) !== '' ) continue;
        if ( function_exists( 'vpg_ml_optout' ) && vpg_ml_optout( (int) get_post_field( 'post_author', $aid ) ) ) continue;
        $s = vpg_vision_suggest( $aid );
        if ( $s && ! empty( $s['alt'] ) ) { update_post_meta( $aid, '_wp_attachment_image_alt', $s['alt'] ); $done++; }
    }
    if ( $done && function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'vision_batch', $done . ' alt texts' );
} );

/* ================================================================
 * Backlog tools desk — follower list, federation switch, vision stack
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Fediverse & batch', 'vpg-v2' ), '🛰 ' . __( 'Fediverse & batch', 'vpg-v2' ), 'manage_options', 'vpg-backlog', 'vpg_backlog_desk' );
} );
function vpg_backlog_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['_vpg_bk'] ) && wp_verify_nonce( $_POST['_vpg_bk'], 'vpg_bk' ) ) {
        update_option( 'vpg_fed_off', ! empty( $_POST['fed_off'] ) );
        if ( ! empty( $_POST['queue_alt'] ) ) {
            $missing = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'numberposts' => 500, 'fields' => 'ids', 'meta_query' => [ [ 'key' => '_wp_attachment_image_alt', 'compare' => 'NOT EXISTS' ] ] ] );
            $n = 0;
            if ( function_exists( 'vpg_ai_enqueue' ) ) foreach ( $missing as $aid ) { vpg_ai_enqueue( [ 'type' => 'alt', 'id' => (int) $aid ] ); $n++; }
            echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Queued %d images for the nightly vision batch.', 'vpg-v2' ), $n ) ) . '</p></div>';
        }
    }
    $followers = (array) get_option( 'vpg_fed_followers', [] );
    $queued    = count( (array) get_option( 'vpg_ai_queue', [] ) );
    ?>
    <div class="wrap"><h1>🛰 <?php esc_html_e( 'Fediverse & batch', 'vpg-v2' ); ?></h1>
      <form method="post">
        <?php wp_nonce_field( 'vpg_bk', '_vpg_bk' ); ?>

        <h2><?php esc_html_e( '1029 · Federation', 'vpg-v2' ); ?></h2>
        <p><label><input type="checkbox" name="fed_off" <?php checked( get_option( 'vpg_fed_off' ) ); ?>> <?php esc_html_e( 'Turn federation off (stop ActivityPub actor, delivery and inbox)', 'vpg-v2' ); ?></label></p>
        <p><?php echo esc_html( sprintf( _n( '%s follower in the Fediverse.', '%s followers in the Fediverse.', count( $followers ), 'vpg-v2' ), number_format_i18n( count( $followers ) ) ) ); ?></p>
        <?php if ( $followers ) { echo '<ul style="max-height:220px;overflow:auto">'; foreach ( array_slice( $followers, 0, 200 ) as $f ) echo '<li><code>' . esc_html( is_string( $f ) ? $f : ( $f['actor'] ?? '' ) ) . '</code></li>'; echo '</ul>'; } ?>

        <h2><?php esc_html_e( '1032 · Vision stack — alt text over the whole library', 'vpg-v2' ); ?></h2>
        <?php if ( function_exists( 'vpg_vision_endpoint' ) && vpg_vision_endpoint() ) : ?>
          <p><button class="button button-primary" name="queue_alt" value="1"><?php esc_html_e( 'Queue all images missing alt text', 'vpg-v2' ); ?></button>
             <span class="description"><?php echo esc_html( sprintf( __( '%d jobs currently queued; they run bundled in the small hours.', 'vpg-v2' ), $queued ) ); ?></span></p>
        <?php else : ?>
          <p class="description"><?php esc_html_e( 'Set a vision endpoint first (Hub → Vision endpoint). Members who opted out of ML are always skipped.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </form>
    </div>
    <?php
}

/* ================================================================
 * 1033 · discover-by-image landing (colour + similarity in one place)
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( 'entdecken-bild' !== $path && 'discover-by-image' !== $path ) return;
    status_header( 200 ); get_header();
    echo '<main id="vpg-main" class="g-wrap" style="max-width:1000px;margin:40px auto;padding:0 20px">';
    echo '<h1>' . esc_html__( 'Discover by image', 'vpg-v2' ) . '</h1>';
    echo '<p class="g-lede">' . esc_html__( 'Two ways in: pick a colour, or start from a photo and find its visual neighbours.', 'vpg-v2' ) . '</p>';

    // colour row — common hues found in the library
    echo '<h2>' . esc_html__( 'By colour', 'vpg-v2' ) . '</h2><p style="display:flex;flex-wrap:wrap;gap:8px">';
    foreach ( [ 'E5341F' => 'Rot', 'F2A100' => 'Gold', '2E7D32' => 'Grün', '1565C0' => 'Blau', '6A1B9A' => 'Violett', '111111' => 'Schwarz', 'FFFFFF' => 'Weiß', '8D6E63' => 'Braun' ] as $hex => $name ) {
        echo '<a href="' . esc_url( home_url( '/farbe/' . $hex . '/' ) ) . '" title="' . esc_attr( $name ) . '" style="width:40px;height:40px;border-radius:6px;border:1px solid var(--g-line,#E6E5E1);background:#' . esc_attr( $hex ) . '"></a>';
    }
    echo '</p>';

    // similarity row — recent images, each linking into the existing /aehnliche/ route
    echo '<h2>' . esc_html__( 'By similarity', 'vpg-v2' ) . '</h2>';
    $imgs = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'post_status' => 'inherit', 'numberposts' => 24, 'orderby' => 'date', 'order' => 'DESC' ] );
    if ( $imgs ) {
        echo '<div class="g-grid3" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">';
        foreach ( $imgs as $im ) {
            $t = wp_get_attachment_image( $im->ID, [ 200, 200 ], false, [ 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover' ] );
            if ( $t ) echo '<a href="' . esc_url( home_url( '/aehnliche/' . $im->ID . '/' ) ) . '" title="' . esc_attr__( 'Find visually similar', 'vpg-v2' ) . '">' . $t . '</a>';
        }
        echo '</div>';
    } else {
        echo '<p class="description">' . esc_html__( 'No images yet.', 'vpg-v2' ) . '</p>';
    }
    echo '</main>';
    get_footer();
    exit;
}, 9 );

/* ================================================================
 * 1034 · keep the original before a destructive edit (WP image editor)
 * ================================================================ */
add_filter( 'wp_save_image_editor_file', function ( $override, $filename, $image, $mime, $post_id ) {
    // Runs just before WordPress overwrites an edited image — snapshot the
    // pre-edit original once so pixelation/crops are never one-way.
    $orig = get_attached_file( $post_id );
    if ( $orig && file_exists( $orig ) && ! get_post_meta( $post_id, '_vpg_original_backup', true ) ) {
        $backup = preg_replace( '/(\.[^.]+)$/', '-vpgorig$1', $orig );
        if ( $backup && $backup !== $orig && @copy( $orig, $backup ) ) {
            update_post_meta( $post_id, '_vpg_original_backup', wp_basename( $backup ) );
        }
    }
    return $override; // null → let WordPress save normally
}, 10, 5 );
