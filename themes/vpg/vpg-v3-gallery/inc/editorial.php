<?php
/**
 * VPG v3 — Q5 · editorial power.
 *
 *   0842  Frontend review desk · /review/ for editors, no wp-admin
 *   0841  Hub work tiles · the numbers that need eyes, first
 *   0485  Glossary · option-managed terms, rendered A–Z
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 0842 · Frontend review actions · approve/reject from /review/ ─ */
add_action( 'admin_post_vpg_front_review', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden', 403 );
    check_admin_referer( 'vpg_front_review' );

    $post   = get_post( (int) ( $_POST['post'] ?? 0 ) );
    $act    = sanitize_key( $_POST['act'] ?? '' );
    $reason = sanitize_textarea_field( wp_unslash( $_POST['reason'] ?? '' ) );

    if ( $post && $post->post_status === 'pending' && current_user_can( 'edit_post', $post->ID ) ) {
        if ( $act === 'approve' ) {
            wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'publish' ] );
            vpg_notify_submitter( $post->ID, 'approve' );
        } elseif ( $act === 'reject' ) {
            vpg_notify_submitter( $post->ID, 'reject', $reason );
            wp_update_post( [ 'ID' => $post->ID, 'post_status' => 'trash' ] );
        }
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', $act === 'approve' ? 'published' : 'rejected', wp_get_referer() ?: home_url( '/review/' ) ) );
    exit;
} );

add_action( 'wp_footer', function () {
    $status = sanitize_key( $_GET['vpg_status'] ?? '' );
    $map    = [
        'published' => __( 'Published — the member got their “it’s live” mail.', 'vpg-v2' ),
        'rejected'  => __( 'Returned with feedback — nothing disappears silently.', 'vpg-v2' ),
    ];
    if ( ! isset( $map[ $status ] ) || ! current_user_can( 'edit_others_posts' ) ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-rev-toast"><?php echo esc_html( $map[ $status ] ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-rev-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ─── 0841 · Hub work tiles · what needs eyes, before everything ── */
function vpg_hub_work_tiles() {
    $types    = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ];
    $pending  = new WP_Query( [ 'post_type' => $types, 'post_status' => 'pending', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ] );
    $mod      = get_comments( [ 'status' => 'hold', 'count' => true ] );
    $reported = get_comments( [ 'meta_key' => '_vpg_reports', 'count' => true ] );
    $latest   = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => [ 'publish', 'draft', 'future' ], 'posts_per_page' => 1 ] );
    $issue    = $latest ? sprintf( '%s (%s)', get_the_title( $latest[0] ), get_post_status( $latest[0] ) ) : __( 'none yet', 'vpg-v2' );

    $tiles = [
        [ (int) $pending->found_posts, __( 'in the review queue', 'vpg-v2' ), admin_url( 'edit.php?post_type=vpg_event&page=vpg-submissions' ), $pending->found_posts > 0 ],
        [ (int) $mod, __( 'comments awaiting moderation', 'vpg-v2' ), admin_url( 'edit-comments.php?comment_status=moderated' ), $mod > 0 ],
        [ (int) $reported, __( 'reported notes', 'vpg-v2' ), admin_url( 'edit.php?post_type=vpg_event&page=vpg-reports' ), $reported > 0 ],
    ];
    ?>
    <h2><?php esc_html_e( 'Work', 'vpg-v2' ); ?></h2>
    <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:14px">
        <?php foreach ( $tiles as [ $n, $label, $url, $hot ] ) : ?>
            <a href="<?php echo esc_url( $url ); ?>" style="display:block;min-width:180px;background:#fff;border:1px solid <?php echo $hot ? '#E5341F' : '#E6E5E1'; ?>;padding:14px 18px;text-decoration:none">
                <span style="display:block;font-size:30px;font-weight:800;color:<?php echo $hot ? '#E5341F' : '#0B0B0B'; ?>"><?php echo (int) $n; ?></span>
                <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6A6A6A"><?php echo esc_html( $label ); ?></span>
            </a>
        <?php endforeach; ?>
        <span style="display:block;min-width:220px;background:#fff;border:1px solid #E6E5E1;padding:14px 18px">
            <span style="display:block;font-size:13px;font-weight:800;color:#0B0B0B">📖 <?php echo esc_html( $issue ); ?></span>
            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6A6A6A"><?php esc_html_e( 'current issue', 'vpg-v2' ); ?></span>
        </span>
    </div>
    <p style="margin-top:0"><a href="<?php echo esc_url( home_url( '/review/' ) ); ?>"><?php esc_html_e( '→ Review desk in the frontend (no wp-admin needed)', 'vpg-v2' ); ?></a></p>
    <?php
}

/* ─── 0485 · Glossary · one option, one admin box, one page ──────── */
function vpg_glossary_terms() {
    $raw = (string) get_option( 'vpg_glossary', '' );
    $out = [];
    foreach ( explode( "\n", $raw ) as $line ) {
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        if ( count( $parts ) === 2 && $parts[0] !== '' && $parts[1] !== '' ) {
            $out[ $parts[0] ] = $parts[1];
        }
    }
    uksort( $out, fn( $a, $b ) => strcasecmp( $a, $b ) );
    return $out;
}

add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Glossary', 'vpg-v2' ), __( '📖 Glossary', 'vpg-v2' ), 'edit_others_posts', 'vpg-glossary', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_glossary'] ) && check_admin_referer( 'vpg_glossary_save' ) ) {
            update_option( 'vpg_glossary', sanitize_textarea_field( wp_unslash( $_POST['vpg_glossary'] ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>📖 <?php esc_html_e( 'Glossary', 'vpg-v2' ); ?></h1>
            <p class="description" style="max-width:640px"><?php esc_html_e( 'One term per line, term and definition separated by a pipe: “Blende | Die Öffnung im Objektiv …”. Rendered A–Z on the page using the Glossary template.', 'vpg-v2' ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'vpg_glossary_save' ); ?>
                <textarea name="vpg_glossary" rows="20" style="width:100%;max-width:760px;font-family:ui-monospace,monospace;font-size:13px"><?php echo esc_textarea( get_option( 'vpg_glossary', '' ) ); ?></textarea>
                <p><button class="button button-primary"><?php esc_html_e( 'Save glossary', 'vpg-v2' ); ?></button>
                <span class="description"><?php printf( esc_html( _n( '%d term', '%d terms', count( vpg_glossary_terms() ), 'vpg-v2' ) ), count( vpg_glossary_terms() ) ); ?></span></p>
            </form>
        </div>
        <?php
    } );
}, 17 );
