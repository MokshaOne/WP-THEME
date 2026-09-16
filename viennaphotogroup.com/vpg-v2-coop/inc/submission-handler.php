<?php
/**
 * VPG v2 — submission handlers.
 * Handles the three front-end forms · contact · join · submit.
 * Each is nonced and pushed to email + (for submit) a pending CPT post.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── /contact/ ─────────────────────────────────────────────────── */
add_action( 'admin_post_nopriv_vpg_contact', 'vpg_handle_contact' );
add_action( 'admin_post_vpg_contact',        'vpg_handle_contact' );
function vpg_handle_contact() {
    check_admin_referer( 'vpg_contact' );

    $name    = sanitize_text_field( wp_unslash( $_POST['name']    ?? '' ) );
    $email   = sanitize_email(      wp_unslash( $_POST['email']   ?? '' ) );
    $topic   = sanitize_text_field( wp_unslash( $_POST['topic']   ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

    if ( ! $name || ! is_email( $email ) || ! $message ) {
        vpg_redirect_with_status( 'contact', 'invalid' );
    }

    $to      = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
    $subject = sprintf( '[VPG · %s] %s', $topic ?: 'Contact', $name );
    $body    = "From: {$name} <{$email}>\nTopic: {$topic}\n\n{$message}";
    $headers = [ 'Reply-To: ' . $name . ' <' . $email . '>' ];

    wp_mail( $to, $subject, $body, $headers );
    vpg_redirect_with_status( 'contact', 'ok' );
}

/* ─── /join/ · creates a wp_user, sends payment instructions ────── */
add_action( 'admin_post_nopriv_vpg_join', 'vpg_handle_join' );
add_action( 'admin_post_vpg_join',        'vpg_handle_join' );
function vpg_handle_join() {
    check_admin_referer( 'vpg_join' );

    $tier     = in_array( $_POST['tier'] ?? '', [ 'member', 'sustaining' ], true ) ? $_POST['tier'] : 'member';
    $name     = sanitize_text_field( wp_unslash( $_POST['name']     ?? '' ) );
    $email    = sanitize_email(      wp_unslash( $_POST['email']    ?? '' ) );
    $password =                       wp_unslash( $_POST['password'] ?? '' );

    if ( ! $name || ! is_email( $email ) || strlen( $password ) < 8 ) {
        vpg_redirect_with_status( 'join', 'invalid' );
    }

    if ( email_exists( $email ) ) {
        vpg_redirect_with_status( 'join', 'exists' );
    }

    $username = sanitize_user( current( explode( '@', $email ) ) . wp_rand( 100, 999 ), true );
    $uid      = wp_create_user( $username, $password, $email );

    if ( is_wp_error( $uid ) ) {
        vpg_redirect_with_status( 'join', 'fail' );
    }

    wp_update_user( [ 'ID' => $uid, 'display_name' => $name, 'role' => 'subscriber' ] );

    // Ensure the vpg_member role exists; assign it.
    if ( ! get_role( 'vpg_member' ) ) {
        add_role( 'vpg_member', __( 'VPG Member', 'vpg-v2' ), get_role( 'subscriber' )->capabilities );
    }
    $user = new WP_User( $uid );
    $user->add_role( 'vpg_member' );

    update_user_meta( $uid, '_vpg_tier',        $tier );
    update_user_meta( $uid, '_vpg_tier_status', 'pending_payment' );

    // Notify editorial + welcome the new member
    $to_editor = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
    wp_mail( $to_editor, "[VPG] New {$tier} signup · {$name}", "Name: {$name}\nEmail: {$email}\nTier: {$tier}" );

    wp_mail( $email, __( 'Welcome to VPG · payment next', 'vpg-v2' ),
        "Hello {$name},\n\nThank you for joining VPG as a {$tier}.\n\nNext step · payment. Reply to this email and we'll send IBAN / payment-link instructions tailored to your tier.\n\nOnce confirmed, you'll get a member badge, PDF downloads and submission rights.\n\n— Vienna Photo Group" );

    vpg_redirect_with_status( 'join', 'ok' );
}

/* ─── /submit/ · members only · creates pending CPT post ────────── */
add_action( 'admin_post_vpg_submit', 'vpg_handle_submit' );
function vpg_handle_submit() {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_submit' );

    $allowed = [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ];
    $type    = in_array( $_POST['submit_type'] ?? '', $allowed, true ) ? $_POST['submit_type'] : '';
    $title   = sanitize_text_field( wp_unslash( $_POST['title']    ?? '' ) );
    $lede    = sanitize_text_field( wp_unslash( $_POST['lede']     ?? '' ) );
    $body    = wp_kses_post(        wp_unslash( $_POST['body']     ?? '' ) );
    $district= sanitize_text_field( wp_unslash( $_POST['district'] ?? '' ) );

    if ( ! $type || ! $title || ! $body ) {
        vpg_redirect_with_status( 'submit', 'invalid' );
    }

    $post_id = wp_insert_post( [
        'post_type'    => $type,
        'post_title'   => $title,
        'post_excerpt' => $lede,
        'post_content' => $body,
        'post_status'  => 'pending',
        'post_author'  => get_current_user_id(),
    ] );

    if ( is_wp_error( $post_id ) ) {
        vpg_redirect_with_status( 'submit', 'fail' );
    }

    if ( $district ) {
        $key = ( $type === 'vpg_shop' ) ? 'shop_district' : 'location_district';
        update_post_meta( $post_id, $key, $district );
    }
    update_post_meta( $post_id, '_vpg_submitted_at', current_time( 'mysql' ) );

    // Notify editorial
    $to     = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
    $author = wp_get_current_user();
    $review = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    wp_mail( $to,
        "[VPG · submission] {$title}",
        "Type: {$type}\nFrom: {$author->display_name} <{$author->user_email}>\n\nReview & approve:\n{$review}"
    );

    vpg_redirect_with_status( 'submit', 'ok' );
}

/* ─── Helper · redirect back to the form page with a status flag ─── */
function vpg_redirect_with_status( $slug, $status ) {
    $page = get_page_by_path( $slug );
    $url  = $page ? get_permalink( $page->ID ) : home_url( '/' . $slug . '/' );
    wp_safe_redirect( add_query_arg( 'vpg_status', $status, $url ) );
    exit;
}

/* ─── Render a toast on the page when status is in the URL ─────── */
add_action( 'wp_footer', function () {
    if ( empty( $_GET['vpg_status'] ) ) return;
    $status   = sanitize_key( $_GET['vpg_status'] );
    $messages = [
        'ok'      => [ 'success', __( 'Sent · thank you.', 'vpg-v2' ) ],
        'invalid' => [ 'error',   __( 'Please fill all required fields.', 'vpg-v2' ) ],
        'exists'  => [ 'error',   __( 'An account already exists for that email · please log in.', 'vpg-v2' ) ],
        'fail'    => [ 'error',   __( 'Something went wrong · please try again or send us a message.', 'vpg-v2' ) ],
    ];
    $row = $messages[ $status ] ?? null;
    if ( ! $row ) return;
    ?>
    <div class="vpg-toast vpg-toast--<?php echo esc_attr( $row[0] ); ?>" id="vpg-toast"><?php echo esc_html( $row[1] ); ?></div>
    <script>
    setTimeout(function () {
        var t = document.getElementById('vpg-toast');
        if (t) t.classList.add('is-visible');
        setTimeout(function () { if (t) t.classList.remove('is-visible'); }, 4000);
    }, 100);
    </script>
    <?php
} );
