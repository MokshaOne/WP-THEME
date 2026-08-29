<?php
/**
 * VPG v3 — Account · frontend self-service for members.
 *
 *   - Profile editor handler (display name · bio · links · avatar · prefs)
 *     — the form lives on the dashboard, nobody is sent to wp-admin
 *   - Local avatars · uploaded file replaces Gravatar (no email-hash
 *     requests to a third party)
 *   - Magic login link · passwordless sign-in by email, 15 min tokens
 *   - Account deletion · GDPR Art. 17 self-service with two modes
 *   - wp-login.php branding · login / lost-password / reset screens carry
 *     the Gallery look
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Profile editor · POST vpg_save_profile                           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_save_profile', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_save_profile' );

    $uid = get_current_user_id();

    $display = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
    $bio     = sanitize_textarea_field( wp_unslash( $_POST['bio'] ?? '' ) );
    $website = esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) );
    $insta   = sanitize_text_field( wp_unslash( $_POST['instagram'] ?? '' ) );
    $insta   = ltrim( $insta, '@' );

    if ( $display ) {
        wp_update_user( [ 'ID' => $uid, 'display_name' => $display, 'user_url' => $website ] );
    }
    update_user_meta( $uid, 'description',     $bio );
    update_user_meta( $uid, '_vpg_instagram',  $insta );

    // Email preferences · consumed by digest / feedback mails
    update_user_meta( $uid, '_vpg_pref_digest',   empty( $_POST['pref_digest'] )   ? '0' : '1' );
    update_user_meta( $uid, '_vpg_pref_feedback', empty( $_POST['pref_feedback'] ) ? '0' : '1' );
    update_user_meta( $uid, '_vpg_directory_optin', empty( $_POST['directory_optin'] ) ? '0' : '1' );

    // Buddy matching · opt-in, two roles
    $buddy = in_array( $_POST['buddy_role'] ?? '', [ 'off', 'mentor', 'looking' ], true ) ? $_POST['buddy_role'] : 'off';
    update_user_meta( $uid, '_vpg_buddy_role', $buddy );

    // Avatar upload · images only, 4 MB, stored as attachment
    if ( ! empty( $_FILES['avatar']['name'] ) && (int) $_FILES['avatar']['error'] === UPLOAD_ERR_OK ) {
        if ( (int) $_FILES['avatar']['size'] <= 4 * MB_IN_BYTES ) {
            $check = wp_check_filetype_and_ext( $_FILES['avatar']['tmp_name'], sanitize_file_name( $_FILES['avatar']['name'] ) );
            if ( ! empty( $check['ext'] ) && in_array( strtolower( $check['ext'] ), [ 'jpg', 'jpeg', 'png', 'webp' ], true ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                $att = media_handle_upload( 'avatar', 0 );
                if ( ! is_wp_error( $att ) ) {
                    $old = (int) get_user_meta( $uid, '_vpg_avatar', true );
                    update_user_meta( $uid, '_vpg_avatar', (int) $att );
                    if ( $old && $old !== (int) $att ) wp_delete_attachment( $old, true );
                }
            }
        }
    }

    vpg_redirect_with_status( 'dashboard', 'profile_saved' );
} );

/* Serve the local avatar wherever WordPress asks for one */
add_filter( 'pre_get_avatar_data', function ( $args, $id_or_email ) {
    $user = false;
    if ( is_numeric( $id_or_email ) )          $user = get_userdata( (int) $id_or_email );
    elseif ( is_string( $id_or_email ) )       $user = get_user_by( 'email', $id_or_email );
    elseif ( $id_or_email instanceof WP_User ) $user = $id_or_email;
    elseif ( $id_or_email instanceof WP_Comment && $id_or_email->user_id ) $user = get_userdata( (int) $id_or_email->user_id );

    if ( $user ) {
        $att = (int) get_user_meta( $user->ID, '_vpg_avatar', true );
        if ( $att ) {
            $url = wp_get_attachment_image_url( $att, [ (int) $args['size'], (int) $args['size'] ] )
                ?: wp_get_attachment_image_url( $att, 'thumbnail' );
            if ( $url ) {
                $args['url']          = $url;
                $args['found_avatar'] = true;
            }
        }
    }
    return $args;
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Magic login link · passwordless sign-in                          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_nopriv_vpg_magic_request', 'vpg_magic_request' );
add_action( 'admin_post_vpg_magic_request',        'vpg_magic_request' );
function vpg_magic_request() {
    check_admin_referer( 'vpg_magic_request' );
    if ( function_exists( 'vpg_antispam_passed' ) && ! vpg_antispam_passed() ) {
        vpg_redirect_with_status( 'login', 'magic_sent' ); // silent drop
    }

    // Per-IP throttle · 5 requests / 15 min
    $ip_key = 'vpg_magic_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' );
    $count  = (int) get_transient( $ip_key );
    if ( $count >= 5 ) vpg_redirect_with_status( 'login', 'magic_sent' );
    set_transient( $ip_key, $count + 1, 15 * MINUTE_IN_SECONDS );

    $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $user  = $email ? get_user_by( 'email', $email ) : false;

    if ( $user ) {
        $token = wp_generate_password( 32, false, false );
        update_user_meta( $user->ID, '_vpg_magic_token',   $token );
        update_user_meta( $user->ID, '_vpg_magic_expires', time() + 15 * MINUTE_IN_SECONDS );

        $url = add_query_arg( [
            'action' => 'vpg_magic_login',
            'uid'    => $user->ID,
            'token'  => $token,
        ], admin_url( 'admin-post.php' ) );

        wp_mail( $user->user_email,
            __( 'Your VPG login link', 'vpg-v2' ),
            sprintf(
                /* translators: 1: display name, 2: login URL */
                __( "Hello %1\$s,\n\nHere is your one-time login link — valid for 15 minutes:\n\n%2\$s\n\nIf you didn't request this, ignore this email; nothing happens without the link.\n\n— Vienna Photo Group", 'vpg-v2' ),
                $user->display_name,
                $url
            )
        );
    }

    // Same answer whether the account exists or not · no enumeration
    vpg_redirect_with_status( 'login', 'magic_sent' );
}

add_action( 'admin_post_nopriv_vpg_magic_login', 'vpg_magic_login' );
add_action( 'admin_post_vpg_magic_login',        'vpg_magic_login' );
function vpg_magic_login() {
    $uid   = (int) ( $_GET['uid'] ?? 0 );
    $token = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
    $saved = $uid ? (string) get_user_meta( $uid, '_vpg_magic_token', true ) : '';
    $exp   = (int) get_user_meta( $uid, '_vpg_magic_expires', true );

    if ( ! $uid || ! $token || ! $saved || ! hash_equals( $saved, $token ) || time() > $exp ) {
        vpg_redirect_with_status( 'login', 'magic_fail' );
    }

    delete_user_meta( $uid, '_vpg_magic_token' );
    delete_user_meta( $uid, '_vpg_magic_expires' );

    wp_set_current_user( $uid );
    wp_set_auth_cookie( $uid, true );
    do_action( 'wp_login', get_userdata( $uid )->user_login, get_userdata( $uid ) );

    wp_safe_redirect( home_url( '/dashboard/' ) );
    exit;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Account deletion · GDPR self-service                             */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_delete_account', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_delete_account' );

    $uid  = get_current_user_id();
    $user = wp_get_current_user();

    // Re-authenticate with the current password
    $password = (string) wp_unslash( $_POST['password'] ?? '' );
    if ( ! wp_check_password( $password, $user->user_pass, $uid ) ) {
        vpg_redirect_with_status( 'dashboard', 'delete_badpw' );
    }

    // Admins cannot self-delete here · too easy to orphan the site
    if ( user_can( $uid, 'manage_options' ) ) {
        vpg_redirect_with_status( 'dashboard', 'delete_admin' );
    }

    $mode      = ( $_POST['delete_mode'] ?? '' ) === 'erase' ? 'erase' : 'keep';
    $email     = $user->user_email;
    $to_editor = get_theme_mod( 'vpg_email', get_option( 'admin_email' ) );

    require_once ABSPATH . 'wp-admin/includes/user.php';

    if ( $mode === 'erase' ) {
        // Everything goes · posts, photos, account
        $avatar = (int) get_user_meta( $uid, '_vpg_avatar', true );
        if ( $avatar ) wp_delete_attachment( $avatar, true );
        wp_logout();
        wp_delete_user( $uid ); // no reassign → the user's posts are deleted
    } else {
        // Published work stays on the site, byline becomes the site itself.
        $editors  = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ] );
        $reassign = $editors ? (int) $editors[0] : null;
        $avatar   = (int) get_user_meta( $uid, '_vpg_avatar', true );
        if ( $avatar ) wp_delete_attachment( $avatar, true );
        wp_logout();
        wp_delete_user( $uid, $reassign );
    }

    wp_mail( $to_editor, '[VPG] Account deleted', "Member {$email} deleted their account (mode: {$mode})." );

    wp_safe_redirect( add_query_arg( 'vpg_status', 'deleted', home_url( '/' ) ) );
    exit;
} );

/* Goodbye toast on the front page */
add_action( 'wp_footer', function () {
    if ( ( $_GET['vpg_status'] ?? '' ) !== 'deleted' ) return;
    echo '<div class="vpg-toast vpg-toast--success is-visible" id="vpg-toast">'
        . esc_html__( 'Your account is deleted. Thank you for having been part of it.', 'vpg-v2' )
        . '</div>';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  wp-login.php · Gallery look for login / lost password / reset    */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'login_headerurl',  function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );

add_action( 'login_enqueue_scripts', function () {
    $fonts = VPG_V2_URI . '/assets/css/fonts.css';
    wp_enqueue_style( 'vpg-login-fonts', $fonts, [], VPG_V2_VERSION );
    ?>
    <style>
        body.login { background: #FFFFFF; font-family: 'Archivo', system-ui, sans-serif; }
        .login h1 a {
            background: none; text-indent: 0; width: auto; height: auto;
            font-family: 'Archivo', system-ui, sans-serif; font-weight: 800; font-stretch: 125%;
            font-size: 22px; letter-spacing: .02em; text-transform: uppercase; color: #0B0B0B;
        }
        .login h1 a::after { content: '.'; color: #E5341F; }
        .login form { border: 1px solid #0B0B0B; border-radius: 0; box-shadow: none; }
        .login input[type=text], .login input[type=password], .login input[type=email] {
            border: 1px solid #D2D1CC; border-radius: 0; font-size: 15px;
        }
        .login input:focus { border-color: #0B0B0B; box-shadow: 0 0 0 1px #0B0B0B; outline: none; }
        .wp-core-ui .button-primary {
            background: #E5341F; border: 1px solid #E5341F; border-radius: 0;
            text-transform: uppercase; letter-spacing: .12em; font-weight: 700; font-size: 12px;
            text-shadow: none; box-shadow: none;
        }
        .wp-core-ui .button-primary:hover { background: #BE2410; border-color: #BE2410; }
        .login #nav a, .login #backtoblog a { color: #6A6A6A; }
        .login #nav a:hover, .login #backtoblog a:hover { color: #E5341F; }
        .login .message, .login .success { border-left: 3px solid #0B0B0B; border-radius: 0; }
        #login_error { border-left: 3px solid #E5341F; border-radius: 0; }
    </style>
    <?php
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Profile toasts                                                   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', function () {
    $status = sanitize_key( $_GET['vpg_status'] ?? '' );
    $map    = [
        'profile_saved' => [ 'success', __( 'Profile saved.', 'vpg-v2' ) ],
        'magic_sent'    => [ 'success', __( 'If that address has an account, a login link is on its way.', 'vpg-v2' ) ],
        'magic_fail'    => [ 'error',   __( 'That login link is invalid or expired · request a fresh one.', 'vpg-v2' ) ],
        'delete_badpw'  => [ 'error',   __( 'Password incorrect · account unchanged.', 'vpg-v2' ) ],
        'delete_admin'  => [ 'error',   __( 'Administrators can\'t self-delete here.', 'vpg-v2' ) ],
    ];
    if ( ! isset( $map[ $status ] ) ) return;
    ?>
    <div class="vpg-toast vpg-toast--<?php echo esc_attr( $map[ $status ][0] ); ?>" id="vpg-acct-toast"><?php echo esc_html( $map[ $status ][1] ); ?></div>
    <script>
    setTimeout(function () {
        var t = document.getElementById('vpg-acct-toast');
        if (t) t.classList.add('is-visible');
        setTimeout(function () { if (t) t.classList.remove('is-visible'); }, 6000);
    }, 100);
    </script>
    <?php
} );
