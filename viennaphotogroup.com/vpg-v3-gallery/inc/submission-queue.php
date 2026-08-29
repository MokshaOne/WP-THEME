<?php
/**
 * VPG v2 — Submission queue admin.
 *
 * Aggregates pending posts from all member-submittable CPTs into one screen.
 * Editorial can Approve · Reject · Edit from a single dashboard rather than
 * navigating each CPT separately. Shows: title, type, submitter, date.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Lives in the 👥 Community cluster (events menu). */
add_action( 'admin_menu', function () {
    $count = vpg_pending_submission_count();
    $badge = $count ? ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>' : '';
    add_submenu_page(
        'edit.php?post_type=vpg_event',
        __( 'Submissions', 'vpg-v2' ),
        '✉ ' . __( 'Submissions', 'vpg-v2' ) . $badge,
        'edit_others_posts',
        'vpg-submissions',
        'vpg_render_submission_queue'
    );
}, 14 );

/* Pending-count bubble on the Community top-level item, like Comments. */
add_filter( 'add_menu_classes', function ( $menu ) {
    $count = vpg_pending_submission_count();
    if ( ! $count ) return $menu;
    foreach ( $menu as $k => $item ) {
        if ( isset( $item[2] ) && $item[2] === 'edit.php?post_type=vpg_event' ) {
            $menu[ $k ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $count . '</span></span>';
        }
    }
    return $menu;
} );

/**
 * Feedback mail to the member whose submission was approved or rejected.
 * "Feedback beats likes" — no submission disappears silently.
 */
function vpg_notify_submitter( $post_id, $verdict, $reason = '' ) {
    $post   = get_post( $post_id );
    if ( ! $post ) return;
    $author = get_userdata( $post->post_author );
    if ( ! $author || ! is_email( $author->user_email ) ) return;

    // In-app notification always; email honours the member's preference
    if ( function_exists( 'vpg_notify_user' ) ) {
        vpg_notify_user(
            $author->ID,
            $verdict === 'approve'
                ? sprintf( __( 'Your submission "%s" is live.', 'vpg-v2' ), $post->post_title )
                : sprintf( __( 'Feedback on your submission "%s".', 'vpg-v2' ), $post->post_title ),
            $verdict === 'approve' ? get_permalink( $post ) : home_url( '/dashboard/' ),
            'review'
        );
    }
    if ( get_user_meta( $author->ID, '_vpg_pref_feedback', true ) === '0' ) return; // member opted out of email

    $sw = function_exists( 'vpg_switch_mail_locale' ) ? vpg_switch_mail_locale( $author->ID ) : false;   // 1025
    $title = $post->post_title;

    if ( $verdict === 'approve' ) {
        $subject = sprintf( __( '[VPG] Your work is live · %s', 'vpg-v2' ), $title );
        $body    = sprintf(
            /* translators: 1: display name, 2: post title, 3: permalink */
            __( "Hello %1\$s,\n\nYour submission \"%2\$s\" was approved and is now live — your name under it:\n\n%3\$s\n\nThank you for feeding the index.\n\n— Vienna Photo Group", 'vpg-v2' ),
            $author->display_name, $title, get_permalink( $post_id )
        );
    } else {
        $subject = sprintf( __( '[VPG] About your submission · %s', 'vpg-v2' ), $title );
        $body    = sprintf(
            /* translators: 1: display name, 2: post title */
            __( "Hello %1\$s,\n\nWe couldn't publish \"%2\$s\" this time.", 'vpg-v2' ),
            $author->display_name, $title
        );
        if ( $reason ) {
            $body .= "\n\n" . __( 'Editorial feedback:', 'vpg-v2' ) . "\n" . $reason;
        }
        $body .= "\n\n" . __( "Revise and resubmit any time — most submissions go through with light edits.\n\n— Vienna Photo Group", 'vpg-v2' );
    }

    wp_mail( $author->user_email, $subject, $body );
    if ( function_exists( 'vpg_restore_mail_locale' ) ) vpg_restore_mail_locale( $sw );
}

function vpg_pending_submission_count() {
    static $c = null;
    if ( $c !== null ) return $c;
    $types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ];
    $q = new WP_Query( [ 'post_type' => $types, 'post_status' => 'pending', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false ] );
    return $c = (int) $q->found_posts;
}

function vpg_render_submission_queue() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    /* Handle quick actions */
    if ( ! empty( $_GET['vpg_act'] ) && ! empty( $_GET['id'] ) && check_admin_referer( 'vpg_submission_action' ) ) {
        $id     = (int) $_GET['id'];
        $act    = sanitize_key( $_GET['vpg_act'] );
        $reason = sanitize_textarea_field( wp_unslash( $_GET['reason'] ?? '' ) );
        if ( current_user_can( 'edit_post', $id ) ) {
            if ( $act === 'approve' ) {
                wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ] );
                vpg_notify_submitter( $id, 'approve' );
            }
            if ( $act === 'reject' ) {
                vpg_notify_submitter( $id, 'reject', $reason ); // mail before trash · permalink still resolves
                wp_update_post( [ 'ID' => $id, 'post_status' => 'trash' ] );
            }
        }
        wp_safe_redirect( admin_url( 'edit.php?post_type=vpg_event&page=vpg-submissions&done=' . $act ) );
        exit;
    }

    $types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ];
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
                    $approve   = wp_nonce_url( admin_url( 'edit.php?post_type=vpg_event&page=vpg-submissions&vpg_act=approve&id=' . get_the_ID() ), 'vpg_submission_action' );
                    $reject    = wp_nonce_url( admin_url( 'edit.php?post_type=vpg_event&page=vpg-submissions&vpg_act=reject&id='  . get_the_ID() ), 'vpg_submission_action' );
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
                    <td><?php
                        echo esc_html( get_the_date( 'M j, Y · H:i' ) );
                        // 0846 · SLA clock — 72h promise, colour shifts as it ages
                        $age_h = ( time() - get_post_time( 'U', true ) ) / HOUR_IN_SECONDS;
                        $sla   = $age_h >= 72 ? '#d63638' : ( $age_h >= 48 ? '#996800' : '#646970' );
                        printf( ' <span style="color:%s;font-weight:700;font-size:11px">· %dh</span>', esc_attr( $sla ), (int) $age_h );
                    ?></td>
                    <td>
                        <a class="button button-primary" href="<?php echo esc_url( $approve ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Approve and publish? The member gets a &quot;your work is live&quot; email.', 'vpg-v2' ) ); ?>')">✓ <?php esc_html_e( 'Approve', 'vpg-v2' ); ?></a>
                        <a class="button" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'vpg-v2' ); ?></a>
                        <a class="button" href="<?php echo esc_url( vpg_preview_url( get_the_ID() ) ); ?>" target="_blank" title="<?php esc_attr_e( 'Shareable preview link · works without login', 'vpg-v2' ); ?>">👁</a>
                        <select class="vpg-canned" style="max-width:150px;vertical-align:middle" title="<?php esc_attr_e( 'Canned feedback · picked text prefills the reject note', 'vpg-v2' ); ?>">
                            <option value=""><?php esc_html_e( 'Feedback…', 'vpg-v2' ); ?></option>
                            <option><?php esc_html_e( 'Great spot — needs a photo before we can publish. Resubmit with one?', 'vpg-v2' ); ?></option>
                            <option><?php esc_html_e( 'This place is already on the map — add your notes to the existing entry instead.', 'vpg-v2' ); ?></option>
                            <option><?php esc_html_e( 'Needs more detail: access, best time, what to expect on site.', 'vpg-v2' ); ?></option>
                            <option><?php esc_html_e( 'Reads like promotion — we publish member experience, not listings.', 'vpg-v2' ); ?></option>
                        </select>
                        <a class="button button-link-delete" href="<?php echo esc_url( $reject ); ?>" onclick="var c=this.parentNode.querySelector('.vpg-canned'); var r=prompt('<?php echo esc_js( __( 'Feedback for the member (sent by email · leave empty for none):', 'vpg-v2' ) ); ?>', c ? c.value : ''); if (r===null) return false; this.href += '&reason=' + encodeURIComponent(r); return true;">✕</a>
                    </td>
                </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}
