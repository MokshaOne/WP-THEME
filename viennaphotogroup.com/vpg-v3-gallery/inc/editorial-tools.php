<?php
/**
 * VPG v3 — Cluster 22 · Redaktions-Tools & Admin.
 *
 * The editorial workbench — net-new only. Reuses the submission queue,
 * vpg_preview_url(), vpg_mod_log(), vpg_notify_user() and the magazine
 * calendar rather than rebuilding them:
 *
 *   0848 internal notes · 0849 pre-publish checklist · 0845/0847 claim + lock
 *   0856 publish log · 0858 bulk metadata · 0860 inventory · 0870 ressort export
 *   0861/0862 admin command palette · 0863 mobile admin · 0865 milestone alerts
 *   0866 member card · 0867 follow-up snooze · 0868 trash retention · 0874 away
 *   0876 holiday SLA · 0877 preview v2 (expiry+hits) · 0878 undo window
 *   0879 admin dark mode · 0880 queue-zero · 0855 curator role · handbook desk
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** The content types that flow through the editorial workbench. */
function vpg_editorial_types() {
    return apply_filters( 'vpg_editorial_types', [ 'post', 'vpg_location', 'vpg_review', 'vpg_magazine', 'vpg_event', 'vpg_tutorial', 'vpg_project', 'vpg_wall', 'vpg_studio', 'vpg_shop', 'vpg_collection' ] );
}

/* ================================================================
 * assets — palette + admin CSS (dark mode, mobile, wellness)
 * ================================================================ */
add_action( 'admin_enqueue_scripts', function () {
    if ( ! current_user_can( 'edit_posts' ) ) return;
    $v = fn( $r ) => file_exists( VPG_V2_DIR . $r ) ? (string) filemtime( VPG_V2_DIR . $r ) : VPG_V2_VERSION;
    wp_enqueue_style( 'vpg-admin-tools', VPG_V2_URI . '/assets/css/admin-tools.css', [], $v( '/assets/css/admin-tools.css' ) );
    wp_enqueue_script( 'vpg-admin-palette', VPG_V2_URI . '/assets/js/admin-palette.js', [], $v( '/assets/js/admin-palette.js' ), true );
    wp_localize_script( 'vpg-admin-palette', 'vpgAdmCmd', [
        'ajax'        => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'vpg_adm_search' ),
        'placeholder' => __( 'Search the workbench…', 'vpg-v2' ),
        'pages'       => array_values( array_map( fn( $p ) => [ 'label' => $p[0], 'url' => admin_url( $p[1] ) ], [
            [ __( 'Submissions queue', 'vpg-v2' ), 'edit.php?post_type=vpg_event&page=vpg-submissions' ],
            [ __( 'Reports', 'vpg-v2' ), 'edit.php?post_type=vpg_event&page=vpg-reports' ],
            [ __( 'Editorial calendar', 'vpg-v2' ), 'admin.php?page=vpg-magazine-calendar' ],
            [ __( 'Member card', 'vpg-v2' ), 'admin.php?page=vpg-member-card' ],
            [ __( 'Workbench', 'vpg-v2' ), 'admin.php?page=vpg-workbench' ],
            [ __( 'Trust & Safety', 'vpg-v2' ), 'tools.php?page=vpg-trust' ],
            [ __( 'Mail & Notifications', 'vpg-v2' ), 'tools.php?page=vpg-mail' ],
        ] ) ),
    ] );
} );
/* 0879 · admin dark mode — per-user opt-in body class */
add_filter( 'admin_body_class', function ( $c ) {
    if ( get_user_meta( get_current_user_id(), '_vpg_admin_dark', true ) ) $c .= ' vpg-admin-dark';
    return $c;
} );
add_action( 'personal_options', function ( $user ) {
    ?>
    <tr class="user-vpg-dark-wrap"><th scope="row"><?php esc_html_e( 'VPG workbench', 'vpg-v2' ); ?></th>
      <td><label><input type="checkbox" name="vpg_admin_dark" value="1" <?php checked( get_user_meta( $user->ID, '_vpg_admin_dark', true ) ); ?>> <?php esc_html_e( 'Dark mode for wp-admin (night shifts)', 'vpg-v2' ); ?></label><br>
      <label><input type="checkbox" name="vpg_away" value="1" <?php checked( get_user_meta( $user->ID, '_vpg_away', true ) ); ?>> <?php esc_html_e( 'Away / out of office — release my claimed pieces so nothing stalls', 'vpg-v2' ); ?></label></td></tr>
    <?php
} );
add_action( 'personal_options_update', 'vpg_save_admin_dark' );
add_action( 'edit_user_profile_update', 'vpg_save_admin_dark' );
function vpg_save_admin_dark( $uid ) {
    if ( ! current_user_can( 'edit_user', $uid ) ) return;
    update_user_meta( $uid, '_vpg_admin_dark', empty( $_POST['vpg_admin_dark'] ) ? '' : 1 );
    $away = empty( $_POST['vpg_away'] ) ? '' : 1;
    update_user_meta( $uid, '_vpg_away', $away );
    if ( $away ) { // 0874 · release everything this editor was reviewing
        foreach ( get_posts( [ 'post_type' => vpg_editorial_types(), 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_vpg_claimed_by', 'meta_value' => $uid, 'fields' => 'ids' ] ) as $pid ) delete_post_meta( $pid, '_vpg_claimed_by' );
    }
}

/* 0861/0862 · admin command-palette search endpoint */
add_action( 'wp_ajax_vpg_admin_search', function () {
    check_ajax_referer( 'vpg_adm_search', '_n' );
    if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();
    $q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
    $out = [];
    $posts = get_posts( [ 's' => $q, 'post_type' => vpg_editorial_types(), 'post_status' => [ 'publish', 'pending', 'draft', 'future', 'private' ], 'numberposts' => 8, 'suppress_filters' => false ] );
    foreach ( $posts as $p ) $out[] = [ 'label' => get_the_title( $p ) ?: '(' . $p->post_type . ')', 'url' => get_edit_post_link( $p->ID, '' ), 'k' => $p->post_status ];
    if ( current_user_can( 'list_users' ) ) {
        $users = get_users( [ 'search' => '*' . $q . '*', 'search_columns' => [ 'user_login', 'user_email', 'display_name' ], 'number' => 5 ] );
        foreach ( $users as $u ) $out[] = [ 'label' => $u->display_name . ' · ' . $u->user_email, 'url' => admin_url( 'admin.php?page=vpg-member-card&user=' . $u->ID ), 'k' => 'member' ];
    }
    wp_send_json_success( $out );
} );

/* ================================================================
 * 0848 · internal editorial notes · 0845 · claim · 0849 · checklist (metabox)
 * ================================================================ */
add_action( 'add_meta_boxes', function () {
    foreach ( vpg_editorial_types() as $t ) {
        add_meta_box( 'vpg-editorial', '🗒 ' . __( 'Editorial', 'vpg-v2' ), 'vpg_editorial_metabox', $t, 'side', 'high' );
    }
} );
function vpg_editorial_metabox( $post ) {
    wp_nonce_field( 'vpg_editorial_box', '_vpg_ed_box' );
    // 0845 · claim
    $claim = (int) get_post_meta( $post->ID, '_vpg_claimed_by', true );
    echo '<p><strong>' . esc_html__( 'Reviewing', 'vpg-v2' ) . ':</strong> ';
    if ( $claim && $claim !== get_current_user_id() ) echo esc_html( get_the_author_meta( 'display_name', $claim ) ) . ' <label style="display:block"><input type="checkbox" name="vpg_claim" value="1"> ' . esc_html__( 'Take over', 'vpg-v2' ) . '</label>';
    elseif ( $claim ) echo esc_html__( 'You', 'vpg-v2' ) . ' <label style="display:block"><input type="checkbox" name="vpg_unclaim" value="1"> ' . esc_html__( 'Release', 'vpg-v2' ) . '</label>';
    else echo '<label><input type="checkbox" name="vpg_claim" value="1"> ' . esc_html__( 'Claim this for review', 'vpg-v2' ) . '</label>';
    echo '</p>';
    // 0849 · pre-publish checklist (advisory; enforced if the option is on)
    $chk = vpg_prepublish_check( $post );
    echo '<p><strong>' . esc_html__( 'Before publishing', 'vpg-v2' ) . ':</strong></p><ul style="margin:0 0 6px">';
    foreach ( $chk as $label => $ok ) echo '<li>' . ( $ok ? '✅' : '⬜' ) . ' ' . esc_html( $label ) . '</li>';
    echo '</ul>';
    if ( in_array( false, $chk, true ) ) echo '<label style="display:block;font-size:12px"><input type="checkbox" name="vpg_chk_override" value="1"> ' . esc_html__( 'Publish anyway (override)', 'vpg-v2' ) . '</label>';
    // 0867 · follow-up snooze
    $fu = (int) get_post_meta( $post->ID, '_vpg_followup_at', true );
    echo '<p style="margin-top:10px"><label>' . esc_html__( 'Look again on', 'vpg-v2' ) . '<br><input type="date" name="vpg_followup" value="' . esc_attr( $fu ? wp_date( 'Y-m-d', $fu ) : '' ) . '"></label></p>';
    // 0848 · notes (private, threaded)
    $notes = (array) get_post_meta( $post->ID, '_vpg_editorial_notes', true );
    echo '<p><strong>' . esc_html__( 'Internal notes', 'vpg-v2' ) . '</strong> <span style="color:#888">' . esc_html__( '(never public)', 'vpg-v2' ) . '</span></p>';
    if ( $notes ) { echo '<div style="max-height:140px;overflow:auto;border:1px solid #eee;padding:6px;font-size:12px">'; foreach ( array_slice( $notes, -12 ) as $n ) echo '<p style="margin:0 0 6px"><strong>' . esc_html( get_the_author_meta( 'display_name', $n['by'] ) ) . '</strong> <span style="color:#999">' . esc_html( wp_date( 'j.n H:i', $n['t'] ) ) . '</span><br>' . esc_html( $n['text'] ) . '</p>'; echo '</div>'; }
    echo '<textarea name="vpg_note_new" rows="2" style="width:100%" placeholder="' . esc_attr__( 'Add a note…', 'vpg-v2' ) . '"></textarea>';
}
/** 0849 · returns [label => bool] readiness checks. */
function vpg_prepublish_check( $post ) {
    $out = [ __( 'Cover image set', 'vpg-v2' ) => (bool) get_post_thumbnail_id( $post->ID ) ];
    if ( has_post_thumbnail( $post->ID ) ) $out[ __( 'Cover has alt text', 'vpg-v2' ) ] = (bool) trim( (string) get_post_meta( get_post_thumbnail_id( $post->ID ), '_wp_attachment_image_alt', true ) );
    if ( 'vpg_location' === $post->post_type ) $out[ __( 'Map pin (coordinates)', 'vpg-v2' ) ] = ( '' !== get_post_meta( $post->ID, 'location_lat', true ) );
    $out[ __( 'Has a title', 'vpg-v2' ) ] = ( '' !== trim( $post->post_title ) );
    return $out;
}
add_action( 'save_post', function ( $pid, $post ) {
    if ( ! isset( $_POST['_vpg_ed_box'] ) || ! wp_verify_nonce( $_POST['_vpg_ed_box'], 'vpg_editorial_box' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $pid ) ) return;
    $me = get_current_user_id();
    if ( ! empty( $_POST['vpg_claim'] ) )   update_post_meta( $pid, '_vpg_claimed_by', $me );
    if ( ! empty( $_POST['vpg_unclaim'] ) ) delete_post_meta( $pid, '_vpg_claimed_by' );
    if ( isset( $_POST['vpg_followup'] ) ) {
        $d = sanitize_text_field( wp_unslash( $_POST['vpg_followup'] ) );
        $d ? update_post_meta( $pid, '_vpg_followup_at', strtotime( $d . ' 09:00' ) ) : delete_post_meta( $pid, '_vpg_followup_at' );
    }
    $note = trim( sanitize_textarea_field( wp_unslash( $_POST['vpg_note_new'] ?? '' ) ) );
    if ( '' !== $note ) {
        $notes = (array) get_post_meta( $pid, '_vpg_editorial_notes', true );
        $notes[] = [ 'by' => $me, 't' => time(), 'text' => $note ];
        update_post_meta( $pid, '_vpg_editorial_notes', array_slice( $notes, -60 ) );
    }
    if ( ! empty( $_POST['vpg_chk_override'] ) ) update_post_meta( $pid, '_vpg_chk_override', 1 );
}, 10, 2 );

/* 0849 · optional hard gate — block publish while the checklist is unmet */
add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {
    if ( 'publish' !== $data['post_status'] ) return $data;
    if ( ! get_option( 'vpg_enforce_checklist' ) ) return $data;
    if ( ! in_array( $data['post_type'], vpg_editorial_types(), true ) ) return $data;
    $pid = (int) ( $postarr['ID'] ?? 0 );
    if ( ! $pid || ! empty( $postarr['_vpg_chk_override'] ) || get_post_meta( $pid, '_vpg_chk_override', true ) ) return $data;
    $obj = (object) [ 'ID' => $pid, 'post_type' => $data['post_type'], 'post_title' => $data['post_title'] ];
    if ( in_array( false, vpg_prepublish_check( $obj ), true ) ) {
        $data['post_status'] = 'pending'; // hold it back rather than publish incomplete
        set_transient( 'vpg_chk_block_' . get_current_user_id(), 1, 30 );
    }
    return $data;
}, 10, 2 );
add_action( 'admin_notices', function () {
    if ( get_transient( 'vpg_chk_block_' . get_current_user_id() ) ) {
        delete_transient( 'vpg_chk_block_' . get_current_user_id() );
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Held as pending: the pre-publish checklist isn’t complete. Tick “Publish anyway” in the Editorial box to override.', 'vpg-v2' ) . '</p></div>';
    }
} );

/* ================================================================
 * 0856 · publish log · 0865 · milestone alerts · 0878 · undo window
 * ================================================================ */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( ! in_array( $post->post_type, vpg_editorial_types(), true ) ) return;
    if ( 'publish' === $new && 'publish' !== $old ) {
        if ( function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'publish', $post->post_type . ' · ' . get_the_title( $post ), $post->ID );
        update_post_meta( $post->ID, '_vpg_published_at', time() );
        update_post_meta( $post->ID, '_vpg_prev_status', $old ); // 0878 · for the undo window
        set_transient( 'vpg_undo_' . get_current_user_id(), $post->ID, 60 );
    }
}, 10, 3 );
/* 0878 · a 60-second "undo publish" notice + handler */
add_action( 'admin_notices', function () {
    $pid = (int) get_transient( 'vpg_undo_' . get_current_user_id() );
    if ( ! $pid ) return;
    $url = wp_nonce_url( admin_url( 'admin-post.php?action=vpg_undo_publish&pid=' . $pid ), 'vpg_undo_' . $pid );
    echo '<div class="notice notice-info vpg-undo-note is-dismissible"><p>' . esc_html( sprintf( __( 'Published “%s”.', 'vpg-v2' ), get_the_title( $pid ) ) ) . ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Undo (revert to pending)', 'vpg-v2' ) . '</a></p></div>';
} );
add_action( 'admin_post_vpg_undo_publish', function () {
    $pid = (int) ( $_GET['pid'] ?? 0 );
    if ( ! $pid || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'vpg_undo_' . $pid ) || ! current_user_can( 'edit_post', $pid ) ) wp_die( 'no' );
    $prev = get_post_meta( $pid, '_vpg_prev_status', true ) ?: 'pending';
    wp_update_post( [ 'ID' => $pid, 'post_status' => $prev ] );
    delete_transient( 'vpg_undo_' . get_current_user_id() );
    if ( function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'undo_publish', '', $pid );
    wp_safe_redirect( get_edit_post_link( $pid, '' ) ); exit;
} );
/* 0865 · site-wide milestone alerts — member count crossing a round number */
add_action( 'vpg_milestone_check', function () {
    $n = (int) count_users()['total_users'];
    $marks = [ 50, 100, 150, 200, 250, 300, 400, 500, 750, 1000 ];
    $seen = (array) get_option( 'vpg_milestones_seen', [] );
    foreach ( $marks as $m ) {
        if ( $n >= $m && ! in_array( $m, $seen, true ) ) {
            $seen[] = $m;
            foreach ( get_users( [ 'role__in' => [ 'administrator', 'editor' ], 'fields' => 'ID' ] ) as $uid )
                if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, sprintf( __( '🎉 Milestone: we just passed %s members.', 'vpg-v2' ), number_format_i18n( $m ) ), admin_url(), 'growth' );
        }
    }
    update_option( 'vpg_milestones_seen', $seen, false );
} );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_milestone_check' ) ) wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vpg_milestone_check' );
} );

/* ================================================================
 * 0868 · trash retention — force-delete posts trashed > N days
 * ================================================================ */
add_action( 'vpg_trash_purge', function () {
    $days = (int) apply_filters( 'vpg_trash_days', 30 );
    $old  = get_posts( [ 'post_status' => 'trash', 'post_type' => 'any', 'numberposts' => 100, 'fields' => 'ids', 'date_query' => [ [ 'column' => 'post_modified_gmt', 'before' => $days . ' days ago' ] ] ] );
    foreach ( $old as $id ) wp_delete_post( $id, true );
    if ( $old && function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'trash_purge', (string) count( $old ) );
} );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_trash_purge' ) ) wp_schedule_event( time() + 2 * HOUR_IN_SECONDS, 'daily', 'vpg_trash_purge' );
} );

/* ================================================================
 * 0867 · follow-up + 0853 · gap warning + 0874 away nudge (daily)
 * ================================================================ */
add_action( 'vpg_editorial_daily', function () {
    // 0867 · surface posts due for another look
    $due = get_posts( [ 'post_type' => vpg_editorial_types(), 'post_status' => 'any', 'numberposts' => 50, 'meta_query' => [ [ 'key' => '_vpg_followup_at', 'value' => time(), 'compare' => '<=', 'type' => 'NUMERIC' ] ] ] );
    foreach ( $due as $p ) {
        $who = (int) get_post_meta( $p->ID, '_vpg_claimed_by', true );
        if ( $who && function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $who, sprintf( __( 'Follow-up due: “%s”.', 'vpg-v2' ), get_the_title( $p ) ), get_edit_post_link( $p->ID, '' ), 'general' );
        delete_post_meta( $p->ID, '_vpg_followup_at' );
    }
    // 0853 · a week with no journal post → nudge editors
    $recent = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'date_query' => [ [ 'after' => '7 days ago' ] ] ] );
    if ( ! $recent ) foreach ( get_users( [ 'role__in' => [ 'administrator', 'editor' ], 'fields' => 'ID' ] ) as $uid )
        if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, __( 'No journal post in the last 7 days — a gap is opening.', 'vpg-v2' ), admin_url( 'edit.php' ), 'general' );
} );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_editorial_daily' ) ) wp_schedule_event( strtotime( 'tomorrow 7:30' ), 'daily', 'vpg_editorial_daily' );
} );

/* ================================================================
 * 0876 · Viennese holidays pause SLA clocks (helper other desks can use)
 * ================================================================ */
function vpg_is_holiday( $ts = null ) {
    $ts  = $ts ?: time();
    $md  = wp_date( 'm-d', $ts );
    $fixed = [ '01-01', '01-06', '05-01', '08-15', '10-26', '11-01', '12-08', '12-25', '12-26' ]; // AT public holidays (fixed-date subset)
    $extra = (array) get_option( 'vpg_holidays', [] ); // movable feasts / editorial closures, admin-set as Y-m-d
    return in_array( $md, $fixed, true ) || in_array( wp_date( 'Y-m-d', $ts ), $extra, true );
}

/* ================================================================
 * 0855 · curator role — curate without deleting
 * ================================================================ */
add_action( 'after_switch_theme', function () {
    if ( get_role( 'vpg_curator' ) ) return;
    $ed = get_role( 'editor' );
    $caps = $ed ? $ed->capabilities : [ 'read' => true, 'edit_posts' => true, 'edit_others_posts' => true, 'publish_posts' => true, 'moderate_comments' => true ];
    unset( $caps['delete_posts'], $caps['delete_others_posts'], $caps['delete_published_posts'], $caps['delete_private_posts'] );
    add_role( 'vpg_curator', 'VPG Curator', $caps );
} );

/* 0877 · preview links v2 — expiry + access counter, layered on vpg_preview_url */
add_filter( 'the_posts', function ( $posts ) {
    if ( empty( $_GET['vpg_preview'] ) || empty( $posts ) ) return $posts;
    $pid = $posts[0]->ID;
    $exp = (int) get_post_meta( $pid, '_vpg_preview_exp', true );
    if ( $exp && $exp < time() ) { wp_die( esc_html__( 'This preview link has expired.', 'vpg-v2' ), 410 ); }
    $hits = (int) get_post_meta( $pid, '_vpg_preview_hits', true ) + 1;
    update_post_meta( $pid, '_vpg_preview_hits', $hits );
    return $posts;
}, 9 );

/* ================================================================
 * 0866 · member card — everything about one person, one admin page
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Member card', 'vpg-v2' ), '🪪 ' . __( 'Member card', 'vpg-v2' ), 'list_users', 'vpg-member-card', 'vpg_member_card_page' );
    add_submenu_page( 'vpg-hub', __( 'Workbench', 'vpg-v2' ), '🛠 ' . __( 'Workbench', 'vpg-v2' ), 'edit_others_posts', 'vpg-workbench', 'vpg_workbench_page' );
} );
function vpg_member_card_page() {
    if ( ! current_user_can( 'list_users' ) ) wp_die( 'Forbidden' );
    $uid = (int) ( $_GET['user'] ?? 0 );
    echo '<div class="wrap"><h1>🪪 ' . esc_html__( 'Member card', 'vpg-v2' ) . '</h1>';
    echo '<form method="get"><input type="hidden" name="page" value="vpg-member-card"><input type="search" name="user" placeholder="' . esc_attr__( 'User ID', 'vpg-v2' ) . '" value="' . esc_attr( $uid ?: '' ) . '"> <button class="button">' . esc_html__( 'Open', 'vpg-v2' ) . '</button> <span class="description">' . esc_html__( 'Tip: reach this instantly via ⌘K → a member’s name.', 'vpg-v2' ) . '</span></form>';
    $u = $uid ? get_userdata( $uid ) : null;
    if ( ! $u ) { echo '<p class="description">' . esc_html__( 'Pick a member to see their full card.', 'vpg-v2' ) . '</p></div>'; return; }
    $posts = count_user_posts( $uid, 'any', true );
    $level = function_exists( 'vpg_mod_level' ) ? vpg_mod_level( $uid ) : 0;
    $rank  = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $uid ) : '';
    echo '<h2>' . esc_html( $u->display_name ) . ' <span style="color:#888;font-weight:400">· ' . esc_html( $u->user_email ) . '</span></h2>';
    echo '<table class="widefat striped" style="max-width:640px"><tbody>';
    printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Joined', 'vpg-v2' ), esc_html( date_i18n( 'j.n.Y', strtotime( $u->user_registered ) ) ) );
    printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Roles', 'vpg-v2' ), esc_html( implode( ', ', $u->roles ) ) );
    printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Published items', 'vpg-v2' ), esc_html( number_format_i18n( $posts ) ) );
    if ( $rank ) printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Rank', 'vpg-v2' ), esc_html( $rank ) );
    printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Moderation level', 'vpg-v2' ), $level ? 'L' . (int) $level : '—' );
    printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Hidden profile', 'vpg-v2' ), get_user_meta( $uid, '_vpg_hidden', true ) ? esc_html__( 'yes', 'vpg-v2' ) : '—' );
    echo '</tbody></table>';
    echo '<p><a class="button" href="' . esc_url( get_edit_user_link( $uid ) ) . '">' . esc_html__( 'Edit user', 'vpg-v2' ) . '</a> ';
    echo '<a class="button" href="' . esc_url( admin_url( 'edit.php?author=' . $uid ) ) . '">' . esc_html__( 'Their posts', 'vpg-v2' ) . '</a></p>';
    // escalation controls
    if ( current_user_can( 'moderate_comments' ) && function_exists( 'vpg_set_mod_level' ) ) {
        if ( isset( $_POST['_vpg_mc'] ) && wp_verify_nonce( $_POST['_vpg_mc'], 'vpg_mc' ) ) { vpg_set_mod_level( $uid, (int) $_POST['level'] ); echo '<div class="notice notice-success"><p>' . esc_html__( 'Updated.', 'vpg-v2' ) . '</p></div>'; }
        echo '<form method="post"><input type="hidden" name="user" value="' . (int) $uid . '">';
        wp_nonce_field( 'vpg_mc', '_vpg_mc' );
        echo '<label>' . esc_html__( 'Set moderation level', 'vpg-v2' ) . ' <select name="level">';
        foreach ( [ 0 => 'none', 1 => 'notice', 2 => 'warning', 3 => 'pause', 4 => 'ban' ] as $lv => $lb ) echo '<option value="' . $lv . '"' . selected( $level, $lv, false ) . '>' . esc_html( $lb ) . '</option>';
        echo '</select> <button class="button">' . esc_html__( 'Apply', 'vpg-v2' ) . '</button></form>';
    }
    echo '</div>';
}

/* ================================================================
 * 0858 bulk metadata · 0860 inventory · 0870 export · 0871 demo · handbook
 * ================================================================ */
function vpg_workbench_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    $msg = '';
    if ( isset( $_POST['_vpg_wb'] ) && wp_verify_nonce( $_POST['_vpg_wb'], 'vpg_wb' ) ) {
        if ( ! empty( $_POST['bulk_ids'] ) && ! empty( $_POST['bulk_key'] ) ) {
            $ids = array_filter( array_map( 'intval', preg_split( '/[\s,]+/', wp_unslash( $_POST['bulk_ids'] ) ) ) );
            $key = sanitize_key( $_POST['bulk_key'] ); $val = sanitize_text_field( wp_unslash( $_POST['bulk_val'] ?? '' ) );
            $n = 0; foreach ( $ids as $id ) if ( current_user_can( 'edit_post', $id ) ) { update_post_meta( $id, $key, $val ); $n++; }
            $msg = sprintf( __( 'Set %1$s on %2$d posts.', 'vpg-v2' ), $key, $n );
            vpg_mod_log( 'bulk_meta', $key . '=' . $val . ' ×' . $n );
        }
        update_option( 'vpg_enforce_checklist', ! empty( $_POST['enforce_checklist'] ) );
        if ( ! empty( $_POST['demo_make'] ) ) { vpg_demo_content( true ); $msg = __( 'Demo content created.', 'vpg-v2' ); }
        if ( ! empty( $_POST['demo_clear'] ) ) { vpg_demo_content( false ); $msg = __( 'Demo content removed.', 'vpg-v2' ); }
    }
    // 0870 · per-ressort JSON export
    if ( ! empty( $_GET['export'] ) && current_user_can( 'edit_others_posts' ) ) {
        $pt = sanitize_key( $_GET['export'] );
        $rows = get_posts( [ 'post_type' => $pt, 'post_status' => 'any', 'numberposts' => -1 ] );
        header( 'Content-Type: application/json' ); header( 'Content-Disposition: attachment; filename="vpg-' . $pt . '.json"' );
        echo wp_json_encode( array_map( fn( $p ) => [ 'id' => $p->ID, 'title' => $p->post_title, 'status' => $p->post_status, 'date' => $p->post_date, 'author' => (int) $p->post_author ], $rows ) );
        exit;
    }
    ?>
    <div class="wrap"><h1>🛠 <?php esc_html_e( 'Editorial workbench', 'vpg-v2' ); ?></h1>
      <?php if ( $msg ) echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>'; ?>

      <h2><?php esc_html_e( '0858 · Bulk metadata', 'vpg-v2' ); ?></h2>
      <form method="post">
        <?php wp_nonce_field( 'vpg_wb', '_vpg_wb' ); ?>
        <p><label><?php esc_html_e( 'Post IDs (comma/space separated)', 'vpg-v2' ); ?><br><textarea name="bulk_ids" rows="2" class="large-text code" placeholder="12 44 91"></textarea></label></p>
        <p><label><?php esc_html_e( 'Meta key', 'vpg-v2' ); ?> <input type="text" name="bulk_key" placeholder="location_district"></label>
           <label><?php esc_html_e( 'Value', 'vpg-v2' ); ?> <input type="text" name="bulk_val"></label>
           <button class="button button-primary"><?php esc_html_e( 'Apply to all', 'vpg-v2' ); ?></button></p>

        <h2><?php esc_html_e( '0849 · Enforce the pre-publish checklist', 'vpg-v2' ); ?></h2>
        <p><label><input type="checkbox" name="enforce_checklist" <?php checked( get_option( 'vpg_enforce_checklist' ) ); ?>> <?php esc_html_e( 'Hold posts as pending until cover, alt text and pin are present (override per post).', 'vpg-v2' ); ?></label> <button class="button"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>

        <h2><?php esc_html_e( '0871 · Staging demo content', 'vpg-v2' ); ?></h2>
        <p><button class="button" name="demo_make" value="1"><?php esc_html_e( 'Create demo posts', 'vpg-v2' ); ?></button>
           <button class="button" name="demo_clear" value="1"><?php esc_html_e( 'Remove demo posts', 'vpg-v2' ); ?></button>
           <span class="description"><?php esc_html_e( 'Demo posts are tagged and safe to bulk-remove — use on staging only.', 'vpg-v2' ); ?></span></p>
      </form>

      <h2><?php esc_html_e( '0860 · Content inventory', 'vpg-v2' ); ?></h2>
      <table class="widefat striped" style="max-width:560px"><thead><tr><th><?php esc_html_e( 'Type', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Published', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Pending', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Export', 'vpg-v2' ); ?></th></tr></thead><tbody>
      <?php foreach ( vpg_editorial_types() as $pt ) {
          $obj = get_post_type_object( $pt ); if ( ! $obj ) continue;
          $pub = wp_count_posts( $pt )->publish ?? 0; $pend = wp_count_posts( $pt )->pending ?? 0;
          echo '<tr><td>' . esc_html( $obj->labels->name ) . '</td><td>' . esc_html( number_format_i18n( $pub ) ) . '</td><td>' . esc_html( number_format_i18n( $pend ) ) . '</td><td><a href="' . esc_url( admin_url( 'admin.php?page=vpg-workbench&export=' . $pt ) ) . '">JSON</a></td></tr>';
      } ?>
      </tbody></table>

      <h2><?php esc_html_e( '0872 / 0873 · Editor handbook', 'vpg-v2' ); ?></h2>
      <ol style="padding-left:20px;line-height:1.8">
        <li><?php esc_html_e( 'Review: open ⌘K → Submissions, claim a piece, read notes, run the checklist, approve.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( 'Plan: the calendar shows gaps; a week without a journal post nudges the team automatically.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0874 Away: set your profile out-of-office and release claimed pieces so nothing stalls.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0875 Escalate: send a knotty case to the collective mailbox (Trust & Safety → abuse mailbox).', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0868 Trash empties itself after 30 days; 0878 a publish can be undone for 60 seconds.', 'vpg-v2' ); ?></li>
      </ol>
    </div>
    <?php
}
/** 0871 · demo content — tagged so it can be removed cleanly. */
function vpg_demo_content( $make ) {
    if ( $make ) {
        for ( $i = 1; $i <= 3; $i++ ) {
            $id = wp_insert_post( [ 'post_title' => 'DEMO · Sample journal ' . $i, 'post_status' => 'draft', 'post_type' => 'post', 'post_content' => 'Demo content for staging.' ] );
            if ( $id ) update_post_meta( $id, '_vpg_demo', 1 );
        }
    } else {
        foreach ( get_posts( [ 'post_type' => 'any', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_vpg_demo', 'fields' => 'ids' ] ) as $id ) wp_delete_post( $id, true );
    }
}
