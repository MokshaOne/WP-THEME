<?php
/**
 * VPG v2 — Submission queue admin.
 *
 * Aggregates pending posts from all member-submittable CPTs into one screen.
 * Editorial can Approve · Reject · Edit from a single dashboard rather than
 * navigating each CPT separately. Shows: title, type, submitter, date.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
    add_menu_page(
        '✉ ' . __( 'Submissions', 'vpg-v2' ),
        '✉ ' . __( 'Submissions', 'vpg-v2' ),
        'edit_others_posts',
        'vpg-submissions',
        'vpg_render_submission_queue',
        'dashicons-email-alt',
        19
    );
}, 14 );

/* Add count bubble to menu like comments do */
add_filter( 'add_menu_classes', function ( $menu ) {
    $count = vpg_pending_submission_count();
    if ( ! $count ) return $menu;
    foreach ( $menu as $k => $item ) {
        if ( isset( $item[2] ) && $item[2] === 'vpg-submissions' ) {
            $menu[ $k ][0] = '✉ ' . __( 'Submissions', 'vpg-v2' ) . ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>';
        }
    }
    return $menu;
} );

function vpg_pending_submission_count() {
    static $c = null;
    if ( $c !== null ) return $c;
    $types = [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ];
    $q = new WP_Query( [ 'post_type' => $types, 'post_status' => 'pending', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ] );
    return $c = (int) $q->found_posts;
}

function vpg_render_submission_queue() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    /* Handle quick actions */
    if ( ! empty( $_GET['vpg_act'] ) && ! empty( $_GET['id'] ) && check_admin_referer( 'vpg_submission_action' ) ) {
        $id  = (int) $_GET['id'];
        $act = sanitize_key( $_GET['vpg_act'] );
        if ( current_user_can( 'edit_post', $id ) ) {
            if ( $act === 'approve' ) wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
            if ( $act === 'reject' )  wp_update_post( [ 'ID' => $id, 'post_status' => 'trash' ] );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=vpg-submissions&done=' . $act ) );
        exit;
    }

    $types = [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ];
    $pending = new WP_Query( [
        'post_type'      => $types,
        'post_status'    => 'pending',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'ASC',
    ] );
    ?>
    <div class="wrap">
        <h1>✉ <?php esc_html_e( 'Submission queue', 'vpg-v2' ); ?>
            <small style="color:#646970;font-weight:normal">— <?php echo (int) $pending->found_posts; ?> <?php esc_html_e( 'pending', 'vpg-v2' ); ?></small>
        </h1>

        <?php if ( isset( $_GET['done'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html( ucfirst( sanitize_key( $_GET['done'] ) ) ); ?>.</p></div>
        <?php endif; ?>

        <?php if ( ! $pending->have_posts() ) : ?>
            <p style="background:#fff;padding:3rem;text-align:center;border:1px solid #ccd0d4;border-radius:6px;margin-top:1rem">
                <span style="font-size:2rem">⁂</span><br>
                <?php esc_html_e( 'Inbox zero. Nothing waiting for editorial.', 'vpg-v2' ); ?>
            </p>
        <?php else : ?>
        <table class="widefat striped" style="margin-top:1rem">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Title', 'vpg-v2' ); ?></th>
                    <th style="width:140px"><?php esc_html_e( 'Type', 'vpg-v2' ); ?></th>
                    <th style="width:180px"><?php esc_html_e( 'Submitted by', 'vpg-v2' ); ?></th>
                    <th style="width:160px"><?php esc_html_e( 'Date', 'vpg-v2' ); ?></th>
                    <th style="width:280px"><?php esc_html_e( 'Actions', 'vpg-v2' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ( $pending->have_posts() ) : $pending->the_post();
                    $author    = get_the_author_meta( 'display_name' );
                    $type      = get_post_type();
                    $edit_url  = get_edit_post_link();
                    $approve   = wp_nonce_url( admin_url( 'admin.php?page=vpg-submissions&vpg_act=approve&id=' . get_the_ID() ), 'vpg_submission_action' );
                    $reject    = wp_nonce_url( admin_url( 'admin.php?page=vpg-submissions&vpg_act=reject&id='  . get_the_ID() ), 'vpg_submission_action' );
                ?>
                <tr>
                    <td>
                        <strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php the_title(); ?></a></strong>
                        <?php if ( has_excerpt() ) : ?>
                            <div style="color:#646970;font-size:12px;margin-top:.3em"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><code><?php echo esc_html( str_replace( 'vpg_', '', $type ) ); ?></code></td>
                    <td><?php echo esc_html( $author ); ?></td>
                    <td><?php echo esc_html( get_the_date( 'M j, Y · H:i' ) ); ?></td>
                    <td>
                        <a class="button button-primary" href="<?php echo esc_url( $approve ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Approve and publish?', 'vpg-v2' ) ); ?>')">✓ <?php esc_html_e( 'Approve', 'vpg-v2' ); ?></a>
                        <a class="button" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'vpg-v2' ); ?></a>
                        <a class="button button-link-delete" href="<?php echo esc_url( $reject ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Move to trash?', 'vpg-v2' ) ); ?>')">✕</a>
                    </td>
                </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}
