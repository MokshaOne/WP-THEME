<?php
/**
 * VPG v3 — Q3 · discovery & community glue.
 *
 *   0014  Missing districts · which of the 23 have no pins yet
 *   0317  Most seen this week · quiet view counts, no like circus
 *   0423  Random coffee · monthly two-member pairings, opt-in
 *   0435  Idea box · anonymous suggestions to editorial
 *   0605  Share target · photos/text shared straight into /submit/
 *   0859  Broken media finder · admin scan for missing files
 *   0864  Weekly editorial stats mail
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 0014 · Districts still blank on the map ───────────────────── */
function vpg_missing_districts() {
    $cached = get_transient( 'vpg_missing_districts' );
    if ( is_array( $cached ) ) return $cached;

    global $wpdb;
    $rows = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('location_district','shop_district')" );
    $have = [];
    foreach ( $rows as $r ) {
        if ( preg_match( '/\b(1\d{2}0)\b/', (string) $r, $m ) ) $have[ $m[1] ] = true;
    }
    $missing = [];
    for ( $i = 1; $i <= 23; $i++ ) {
        $code = (string) ( 1000 + $i * 10 );
        if ( empty( $have[ $code ] ) ) $missing[] = $code;
    }
    set_transient( 'vpg_missing_districts', $missing, HOUR_IN_SECONDS );
    return $missing;
}

/* ─── 0317 · View counts · total + this week, bounded option ────── */
add_action( 'template_redirect', function () {
    if ( ! is_singular() || is_user_logged_in() && current_user_can( 'edit_others_posts' ) ) return;
    $types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [];
    if ( ! in_array( get_post_type(), $types, true ) ) return;

    $id = get_the_ID();
    update_post_meta( $id, '_vpg_views', (int) get_post_meta( $id, '_vpg_views', true ) + 1 );

    $wk   = gmdate( 'o-W' );
    $data = get_option( 'vpg_wk_views', [] );
    if ( ! isset( $data[ $wk ] ) ) $data = [ $wk => [] ]; // new week wipes the board
    $data[ $wk ][ $id ] = ( $data[ $wk ][ $id ] ?? 0 ) + 1;
    arsort( $data[ $wk ] );
    $data[ $wk ] = array_slice( $data[ $wk ], 0, 30, true );
    update_option( 'vpg_wk_views', $data, false );
} );

/**
 * Top pieces of the current week · [ post_id => views ], best first.
 */
function vpg_most_seen_week( $limit = 3 ) {
    $data = get_option( 'vpg_wk_views', [] );
    $week = $data[ gmdate( 'o-W' ) ] ?? [];
    $out  = [];
    foreach ( $week as $id => $n ) {
        if ( get_post_status( $id ) === 'publish' ) $out[ $id ] = $n;
        if ( count( $out ) >= $limit ) break;
    }
    return $out;
}

/* ─── 0423 · Random coffee · monthly pairs, strictly opt-in ─────── */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_random_coffee' ) ) {
        wp_schedule_single_event( strtotime( 'first day of next month 09:30' ), 'vpg_random_coffee' );
    }
} );

add_action( 'vpg_random_coffee', function () {
    wp_schedule_single_event( strtotime( 'first day of next month 09:30' ), 'vpg_random_coffee' );

    $members = get_users( [ 'meta_key' => '_vpg_pref_coffee', 'meta_value' => '1', 'fields' => [ 'ID', 'display_name', 'user_email', 'user_nicename' ] ] );
    $members = array_values( array_filter( $members, fn( $m ) => ! function_exists( 'vpg_is_verified' ) || vpg_is_verified( $m->ID ) ) );
    if ( count( $members ) < 2 ) return;
    shuffle( $members );

    while ( count( $members ) >= 2 ) {
        $a = array_pop( $members );
        $b = array_pop( $members );
        foreach ( [ [ $a, $b ], [ $b, $a ] ] as [ $me, $them ] ) {
            vpg_notify_user( $me->ID,
                sprintf( __( 'Random coffee ☕ — this month you’re paired with %s.', 'vpg-v2' ), $them->display_name ),
                home_url( '/members/' . $them->user_nicename . '/' )
            );
            wp_mail( $me->user_email,
                '[VPG] ' . __( 'Random coffee ☕', 'vpg-v2' ),
                sprintf(
                    /* translators: 1: own name, 2: partner name, 3: partner profile URL */
                    __( "Hello %1\$s,\n\nThe hat picked your coffee partner for this month: %2\$s.\n\n%3\$s\n\nWrite them, pick a café or a photowalk, talk pictures. No agenda, no protocol.\n\n— Vienna Photo Group", 'vpg-v2' ),
                    $me->display_name, $them->display_name, home_url( '/members/' . $them->user_nicename . '/' )
                )
            );
        }
    }
} );

/* ─── 0435 · Idea box · anonymous by design ─────────────────────── */
add_action( 'admin_post_vpg_idea_box', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_idea_box' );
    $idea = sanitize_textarea_field( wp_unslash( $_POST['idea'] ?? '' ) );
    if ( $idea !== '' ) {
        // Deliberately no name, no email, no user id — anonymity is the feature.
        wp_mail( get_theme_mod( 'vpg_email', get_option( 'admin_email' ) ),
            '[VPG] ' . __( 'Anonymous idea from the box', 'vpg-v2' ),
            $idea . "\n\n— " . __( 'Sent anonymously by a member via the dashboard idea box.', 'vpg-v2' )
        );
    }
    wp_safe_redirect( add_query_arg( 'vpg_status', 'idea_sent', wp_get_referer() ?: home_url( '/dashboard/' ) ) );
    exit;
} );

add_action( 'wp_footer', function () {
    if ( sanitize_key( $_GET['vpg_status'] ?? '' ) !== 'idea_sent' ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-idea-toast"><?php esc_html_e( 'Idea dropped in the box — anonymously. Thank you.', 'vpg-v2' ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-idea-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ─── 0859 · Broken media finder · on-demand scan ───────────────── */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Broken media', 'vpg-v2' ), __( '🖼 Broken media', 'vpg-v2' ), 'edit_others_posts', 'vpg-broken-media', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        $ran    = isset( $_POST['vpg_scan'] ) && check_admin_referer( 'vpg_broken_scan' );
        $broken = [];
        if ( $ran ) {
            // Featured images whose file is gone
            $with_thumb = get_posts( [ 'post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => 500, 'meta_key' => '_thumbnail_id', 'fields' => 'ids' ] );
            foreach ( $with_thumb as $pid ) {
                $file = get_attached_file( get_post_thumbnail_id( $pid ) );
                if ( ! $file || ! file_exists( $file ) ) {
                    $broken[] = [ $pid, __( 'featured image file missing', 'vpg-v2' ) ];
                }
            }
            // Local <img> sources in content that 404 on disk
            $uploads = wp_get_upload_dir();
            $recent  = get_posts( [ 'post_type' => 'any', 'post_status' => 'publish', 'posts_per_page' => 300 ] );
            foreach ( $recent as $p ) {
                if ( preg_match_all( '/<img[^>]+src="([^"]+)"/', $p->post_content, $m ) ) {
                    foreach ( $m[1] as $src ) {
                        if ( str_starts_with( $src, $uploads['baseurl'] ) ) {
                            $path = str_replace( $uploads['baseurl'], $uploads['basedir'], strtok( $src, '?' ) );
                            if ( ! file_exists( $path ) ) { $broken[] = [ $p->ID, basename( $path ) . ' ' . __( 'missing', 'vpg-v2' ) ]; break; }
                        }
                    }
                }
            }
        }
        ?>
        <div class="wrap">
            <h1>🖼 <?php esc_html_e( 'Broken media', 'vpg-v2' ); ?></h1>
            <form method="post" style="margin:1rem 0"><?php wp_nonce_field( 'vpg_broken_scan' ); ?>
                <button class="button button-primary" name="vpg_scan" value="1"><?php esc_html_e( 'Scan now', 'vpg-v2' ); ?></button>
                <span class="description" style="margin-left:8px"><?php esc_html_e( 'Checks featured images and local content images of the latest posts.', 'vpg-v2' ); ?></span>
            </form>
            <?php if ( $ran ) : ?>
                <?php if ( ! $broken ) : ?>
                    <p style="background:#fff;border:1px solid #E6E5E1;padding:2rem;max-width:520px">✓ <?php esc_html_e( 'Nothing broken — every checked image resolves to a file.', 'vpg-v2' ); ?></p>
                <?php else : ?>
                    <table class="widefat striped" style="max-width:760px">
                        <thead><tr><th><?php esc_html_e( 'Post', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Problem', 'vpg-v2' ); ?></th></tr></thead>
                        <tbody><?php foreach ( $broken as [ $pid, $note ] ) : ?>
                            <tr><td><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a></td><td><?php echo esc_html( $note ); ?></td></tr>
                        <?php endforeach; ?></tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    } );
}, 16 );

/* ─── 0864 · Weekly stats mail · Monday 07:30 to editorial ──────── */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_weekly_stats' ) ) {
        wp_schedule_event( strtotime( 'next monday 07:30' ), 'weekly', 'vpg_weekly_stats' );
    }
} );

add_action( 'vpg_weekly_stats', function () {
    $since = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
    $types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ];

    $new_members = count( get_users( [ 'date_query' => [ [ 'after' => '-7 days' ] ], 'fields' => 'ID' ] ) );
    $published   = new WP_Query( [ 'post_type' => $types, 'post_status' => 'publish', 'date_query' => [ [ 'after' => '-7 days' ] ], 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ] );
    $pending     = new WP_Query( [ 'post_type' => $types, 'post_status' => 'pending', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ] );
    $comments    = get_comments( [ 'date_query' => [ [ 'after' => '-7 days' ] ], 'count' => true ] );
    $top         = function_exists( 'vpg_most_seen_week' ) ? vpg_most_seen_week( 3 ) : [];

    $body  = __( "The week at Vienna Photo Group:\n\n", 'vpg-v2' );
    $body .= sprintf( "· %d %s\n", $new_members, __( 'new members', 'vpg-v2' ) );
    $body .= sprintf( "· %d %s\n", $published->found_posts, __( 'pieces published', 'vpg-v2' ) );
    $body .= sprintf( "· %d %s\n", $pending->found_posts, __( 'waiting in the review queue', 'vpg-v2' ) );
    $body .= sprintf( "· %d %s\n", $comments, __( 'notes on the walls', 'vpg-v2' ) );
    if ( $top ) {
        $body .= "\n" . __( "Most seen this week:\n", 'vpg-v2' );
        foreach ( $top as $tid => $n ) $body .= sprintf( "· %s (%d) — %s\n", get_the_title( $tid ), $n, get_permalink( $tid ) );
    }
    $body .= "\n" . admin_url( 'admin.php?page=vpg-hub' );

    wp_mail( get_theme_mod( 'vpg_email', get_option( 'admin_email' ) ),
        '[VPG] ' . __( 'The week in numbers', 'vpg-v2' ), $body );
} );
