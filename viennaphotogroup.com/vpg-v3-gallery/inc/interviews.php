<?php
/**
 * VPG v3 — Featured-artist interviews.
 *
 * The magazine's featured-artist section gets its editorial voice: a
 * standard question set every member can answer in the dashboard
 * (usermeta `_vpg_interview`, question-index => answer). Editorial can
 * request an interview from the compile picker; the artist article
 * builder turns answers into a Q&A block automatically.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── The house questions · filter vpg_interview_questions to edit ── */
function vpg_interview_questions() {
    return apply_filters( 'vpg_interview_questions', [
        __( 'Who are you, and what do you photograph?', 'vpg-v2' ),
        __( 'What does Wien look like through your viewfinder?', 'vpg-v2' ),
        __( 'One spot in the city you keep returning to — and why?', 'vpg-v2' ),
        __( 'What is in your bag right now?', 'vpg-v2' ),
        __( 'A photograph (yours or not) that changed how you look?', 'vpg-v2' ),
        __( 'What are you working on next?', 'vpg-v2' ),
        __( 'Advice for someone shooting their first roll in Wien?', 'vpg-v2' ),
    ] );
}

/**
 * Answered Q&A pairs for a member · [ ['q' => …, 'a' => …], … ].
 * Questions may change over time; answers are stored by index, so
 * unanswered or out-of-range slots simply drop out.
 */
function vpg_get_interview( $uid ) {
    $answers = get_user_meta( $uid, '_vpg_interview', true );
    if ( ! is_array( $answers ) ) return [];
    $out = [];
    foreach ( vpg_interview_questions() as $i => $q ) {
        $a = trim( (string) ( $answers[ $i ] ?? '' ) );
        if ( $a !== '' ) $out[] = [ 'q' => $q, 'a' => $a ];
    }
    return $out;
}

/* ─── Member saves answers from the dashboard ───────────────────── */
add_action( 'admin_post_vpg_save_interview', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_save_interview' );

    $raw     = (array) ( $_POST['interview'] ?? [] );
    $answers = [];
    foreach ( vpg_interview_questions() as $i => $q ) {
        $answers[ $i ] = sanitize_textarea_field( wp_unslash( $raw[ $i ] ?? '' ) );
    }
    update_user_meta( get_current_user_id(), '_vpg_interview', $answers );

    if ( function_exists( 'vpg_redirect_with_status' ) ) {
        vpg_redirect_with_status( 'dashboard', 'interview_saved' );
    }
    wp_safe_redirect( home_url( '/dashboard/#interview' ) );
    exit;
} );

add_action( 'wp_footer', function () {
    if ( sanitize_key( $_GET['vpg_status'] ?? '' ) !== 'interview_saved' ) return;
    ?>
    <div role="status" class="vpg-toast vpg-toast--success is-visible" id="vpg-iv-toast"><?php esc_html_e( 'Interview saved — editorial can feature you now.', 'vpg-v2' ); ?></div>
    <script>setTimeout(function(){var t=document.getElementById('vpg-iv-toast');if(t)t.classList.remove('is-visible');},6000);</script>
    <?php
} );

/* ─── Editorial requests an interview · from the compile picker ──── */
add_action( 'wp_ajax_vpg_interview_invite', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error( 'forbidden', 403 );
    check_ajax_referer( 'vpg_mag_pick' );

    $uid  = (int) ( $_GET['user'] ?? 0 );
    $user = get_userdata( $uid );
    if ( ! $user ) wp_send_json_error( 'no such member', 404 );

    update_user_meta( $uid, '_vpg_interview_invited', time() );

    if ( function_exists( 'vpg_notify_user' ) ) {
        vpg_notify_user( $uid,
            __( 'Editorial wants to feature you — answer your interview.', 'vpg-v2' ),
            home_url( '/dashboard/#interview' )
        );
    }
    wp_mail( $user->user_email,
        __( '[VPG] We want to feature you', 'vpg-v2' ),
        sprintf(
            /* translators: 1: display name, 2: dashboard URL */
            __( "Hello %1\$s,\n\nEditorial picked you as an upcoming featured artist. Answer the interview questions in your dashboard, in your own time:\n\n%2\$s\n\nShort answers are fine — honest beats polished.\n\n— Vienna Photo Group", 'vpg-v2' ),
            $user->display_name,
            home_url( '/dashboard/#interview' )
        )
    );

    wp_send_json_success( [ 'invited' => true ] );
} );
