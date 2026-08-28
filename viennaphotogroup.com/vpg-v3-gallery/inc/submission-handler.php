<?php
/**
 * VPG v3 — submission handlers.
 * Handles the front-end forms · contact · join · submit · email verification.
 *
 * Membership model: joining is FREE and active immediately (auto-login).
 * The tier fields (`_vpg_tier`, `_vpg_tier_status`) stay in place so optional
 * paid supporter tiers can be added later without a data migration.
 *
 * Anti-spam: every public form carries a honeypot field (`vpg_hp`, must stay
 * empty) and a timestamp (`vpg_ts`, the form must be at least 3 s old). Bots
 * that trip either are redirected to the success state without side effects.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Anti-spam helpers ─────────────────────────────────────────────
 * Render inside any front-end <form>: echo vpg_antispam_fields();
 * Check first thing in the handler: vpg_antispam_passed().
 */
function vpg_antispam_fields() {
    return sprintf(
        '<div style="position:absolute;left:-9999px;top:auto" aria-hidden="true">' .
        '<label for="vpg_hp">%s</label><input type="text" id="vpg_hp" name="vpg_hp" value="" tabindex="-1" autocomplete="off"></div>' .
        '<input type="hidden" name="vpg_ts" value="%d">',
        esc_html__( 'Leave this field empty', 'vpg-v2' ),
        time()
    );
}

function vpg_antispam_passed() {
    if ( ! empty( $_POST['vpg_hp'] ) ) return false;               // honeypot filled → bot
    $ts  = (int) ( $_POST['vpg_ts'] ?? 0 );
    $age = time() - $ts;
    if ( $ts && ( $age < 3 || $age > DAY_IN_SECONDS ) ) return false; // instant submit → bot
    return true;
}

/* ─── Email verification state ──────────────────────────────────────
 * `_vpg_email_verified` is set to '0' at signup and '1' after the user
 * clicks the link we mail them. Accounts that pre-date this feature have
 * no meta at all and count as verified (the founding members stay valid).
 */
function vpg_is_verified( $uid = null ) {
    $uid  = $uid ?: get_current_user_id();
    $meta = get_user_meta( $uid, '_vpg_email_verified', true );
    return $meta === '' || $meta === '1';
}

function vpg_send_verification_mail( $uid ) {
    $user  = get_userdata( $uid );
    if ( ! $user ) return;
    $token = wp_generate_password( 32, false, false );
    update_user_meta( $uid, '_vpg_verify_token', $token );

    $url = add_query_arg( [
        'action' => 'vpg_verify',
        'uid'    => $uid,
        'token'  => $token,
    ], admin_url( 'admin-post.php' ) );

    wp_mail( $user->user_email,
        __( 'Confirm your VPG email', 'vpg-v2' ),
        sprintf(
            /* translators: 1: display name, 2: verification URL */
            __( "Hello %1\$s,\n\nOne click and your Viennaphotogroup membership is fully active:\n\n%2\$s\n\nUntil you confirm, you can browse everything but not submit to the map or journal.\n\n— Vienna Photo Group", 'vpg-v2' ),
            $user->display_name,
            $url
        )
    );
}

/* ─── /contact/ ─────────────────────────────────────────────────── */
add_action( 'admin_post_nopriv_vpg_contact', 'vpg_handle_contact' );
add_action( 'admin_post_vpg_contact',        'vpg_handle_contact' );
function vpg_handle_contact() {
    check_admin_referer( 'vpg_contact' );
    if ( ! vpg_antispam_passed() ) vpg_redirect_with_status( 'contact', 'ok' ); // silent drop

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

/* ─── /join/ · free membership · active immediately ─────────────── */
add_action( 'admin_post_nopriv_vpg_join', 'vpg_handle_join' );
add_action( 'admin_post_vpg_join',        'vpg_handle_join' );
function vpg_handle_join() {
    check_admin_referer( 'vpg_join' );
    if ( ! vpg_antispam_passed() ) vpg_redirect_with_status( 'join', 'ok' ); // silent drop

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

    // Free membership · active from the first second. Paid supporter
    // tiers come later; the fields are already here for them.
    update_user_meta( $uid, '_vpg_tier',           'member' );
    update_user_meta( $uid, '_vpg_tier_status',    'active' );
    update_user_meta( $uid, '_vpg_email_verified', '0' );

    vpg_send_verification_mail( $uid );

    // Notify editorial + welcome the new member
    $to_editor = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
    wp_mail( $to_editor, "[VPG] New member · {$name}", "Name: {$name}\nEmail: {$email}" );

    wp_mail( $email, __( 'Welcome to Viennaphotogroup', 'vpg-v2' ),
        sprintf(
            /* translators: %s: display name */
            __( "Hello %s,\n\nWelcome — you're a member. Membership is free: no ads, no fees, member-run.\n\nYou can now contribute to the map and the journal, build your public portfolio, and download every issue as a PDF. Confirm your email (separate message in your inbox) to unlock submissions.\n\nYour dashboard: %s\n\n— Vienna Photo Group", 'vpg-v2' ),
            $name,
            home_url( '/dashboard/' )
        )
    );

    // Log the new member straight in — the moment after signup is not
    // the moment to show them a login form.
    wp_set_current_user( $uid );
    wp_set_auth_cookie( $uid, true );

    $dash = get_page_by_path( 'dashboard' );
    $url  = $dash ? get_permalink( $dash->ID ) : home_url( '/dashboard/' );
    wp_safe_redirect( add_query_arg( 'vpg_status', 'welcome', $url ) );
    exit;
}

/* ─── Email verification · link target + resend ─────────────────── */
add_action( 'admin_post_nopriv_vpg_verify', 'vpg_handle_verify' );
add_action( 'admin_post_vpg_verify',        'vpg_handle_verify' );
function vpg_handle_verify() {
    $uid   = (int) ( $_GET['uid'] ?? 0 );
    $token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
    $saved = $uid ? (string) get_user_meta( $uid, '_vpg_verify_token', true ) : '';

    if ( ! $uid || ! $token || ! $saved || ! hash_equals( $saved, $token ) ) {
        vpg_redirect_with_status( 'dashboard', 'verify_fail' );
    }

    update_user_meta( $uid, '_vpg_email_verified', '1' );
    delete_user_meta( $uid, '_vpg_verify_token' );
    vpg_redirect_with_status( 'dashboard', 'verified' );
}

add_action( 'admin_post_vpg_resend_verify', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_resend_verify' );
    if ( ! vpg_is_verified() ) vpg_send_verification_mail( get_current_user_id() );
    vpg_redirect_with_status( 'dashboard', 'verify_sent' );
} );

/* ─── /submit/ · members only · creates pending CPT post ────────── */
add_action( 'admin_post_vpg_submit', 'vpg_handle_submit' );
function vpg_handle_submit() {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_submit' );
    if ( ! vpg_antispam_passed() )  vpg_redirect_with_status( 'submit', 'ok' ); // silent drop
    if ( ! vpg_is_verified() )      vpg_redirect_with_status( 'submit', 'verify' );

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

    $photo_note = vpg_attach_submission_photos( $post_id );

    // Notify editorial
    $to     = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );
    $author = wp_get_current_user();
    $review = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    wp_mail( $to,
        "[VPG · submission] {$title}",
        "Type: {$type}\nFrom: {$author->display_name} <{$author->user_email}>\n{$photo_note}\nReview & approve:\n{$review}"
    );

    vpg_redirect_with_status( 'submit', 'ok' );
}

/**
 * Attach uploaded photos (photos[]) to a submission.
 * Up to 4 images · jpg/png/webp/avif · max 8 MB each · first becomes the
 * featured image. Returns a one-line summary for the editorial email.
 */
function vpg_attach_submission_photos( $post_id ) {
    if ( empty( $_FILES['photos']['name'] ) || ! is_array( $_FILES['photos']['name'] ) ) {
        return 'Photos: none';
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $allowed_ext = [ 'jpg', 'jpeg', 'png', 'webp', 'avif' ];
    $max_bytes   = 8 * MB_IN_BYTES;
    $attached    = 0;
    $skipped     = 0;

    $count = min( count( $_FILES['photos']['name'] ), 4 );
    for ( $i = 0; $i < $count; $i++ ) {
        if ( empty( $_FILES['photos']['name'][ $i ] ) )                       continue;
        if ( (int) $_FILES['photos']['error'][ $i ] !== UPLOAD_ERR_OK )       { $skipped++; continue; }
        if ( (int) $_FILES['photos']['size'][ $i ] > $max_bytes )             { $skipped++; continue; }

        $check = wp_check_filetype_and_ext(
            $_FILES['photos']['tmp_name'][ $i ],
            sanitize_file_name( $_FILES['photos']['name'][ $i ] )
        );
        if ( empty( $check['ext'] ) || ! in_array( strtolower( $check['ext'] ), $allowed_ext, true ) ) {
            $skipped++;
            continue;
        }

        // media_handle_upload() reads one flat $_FILES entry · remap slot $i.
        $_FILES['vpg_photo_single'] = [
            'name'     => sanitize_file_name( $_FILES['photos']['name'][ $i ] ),
            'type'     => $_FILES['photos']['type'][ $i ],
            'tmp_name' => $_FILES['photos']['tmp_name'][ $i ],
            'error'    => $_FILES['photos']['error'][ $i ],
            'size'     => $_FILES['photos']['size'][ $i ],
        ];
        $att_id = media_handle_upload( 'vpg_photo_single', $post_id );
        unset( $_FILES['vpg_photo_single'] );

        if ( is_wp_error( $att_id ) ) { $skipped++; continue; }

        if ( ! $attached && ! has_post_thumbnail( $post_id ) ) {
            set_post_thumbnail( $post_id, $att_id );
        }
        $attached++;
    }

    return sprintf( 'Photos: %d attached%s', $attached, $skipped ? " · {$skipped} skipped (type/size)" : '' );
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
        'ok'          => [ 'success', __( 'Sent · thank you.', 'vpg-v2' ) ],
        'welcome'     => [ 'success', __( 'Welcome — you\'re a member. Check your inbox to confirm your email.', 'vpg-v2' ) ],
        'verified'    => [ 'success', __( 'Email confirmed · submissions are unlocked.', 'vpg-v2' ) ],
        'verify_sent' => [ 'success', __( 'Confirmation email sent · check your inbox.', 'vpg-v2' ) ],
        'verify'      => [ 'error',   __( 'Please confirm your email first · check your inbox or resend from the dashboard.', 'vpg-v2' ) ],
        'verify_fail' => [ 'error',   __( 'That confirmation link is invalid or expired · resend it from your dashboard.', 'vpg-v2' ) ],
        'invalid'     => [ 'error',   __( 'Please fill all required fields.', 'vpg-v2' ) ],
        'exists'      => [ 'error',   __( 'An account already exists for that email · please log in.', 'vpg-v2' ) ],
        'fail'        => [ 'error',   __( 'Something went wrong · please try again or send us a message.', 'vpg-v2' ) ],
    ];
    $row = $messages[ $status ] ?? null;
    if ( ! $row ) return;
    ?>
    <div class="vpg-toast vpg-toast--<?php echo esc_attr( $row[0] ); ?>" id="vpg-toast"><?php echo esc_html( $row[1] ); ?></div>
    <script>
    setTimeout(function () {
        var t = document.getElementById('vpg-toast');
        if (t) t.classList.add('is-visible');
        setTimeout(function () { if (t) t.classList.remove('is-visible'); }, 6000);
    }, 100);
    </script>
    <?php
} );
