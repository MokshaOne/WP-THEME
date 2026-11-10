<?php
/**
 * VPG v3 — Q8 · Nachzieher (round 2).
 *
 *   1021  Circle rounds · a monthly image prompt wakes critique circles
 *   1023  XML-RPC hardening · close the auth bypass 2FA can't guard
 *   1027  Translation watch · untranslated strings surface in the hub
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── 1023 · XML-RPC hardening ───────────────────────────────────
   TOTP and passkeys guard wp-login, but XML-RPC's system.multicall
   authenticates with a bare username+password and bypasses both. We
   run no XML-RPC clients, so close it entirely and drop pingbacks. */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', function ( $methods ) {
    unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
    return $methods;
} );
add_filter( 'wp_headers', function ( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );
// Refuse the endpoint outright — a 403 before WordPress parses credentials.
add_action( 'init', function () {
    if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
        status_header( 403 );
        exit( 'XML-RPC is disabled on this site.' );
    }
} );

/* ─── 1021 · Circle rounds · monthly prompt for critique circles ── */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_circle_rounds' ) ) {
        wp_schedule_single_event( strtotime( 'first day of next month 09:00' ), 'vpg_circle_rounds' );
    }
} );

function vpg_circle_prompts() {
    // Filterable so editorial can curate the rotation over time.
    return apply_filters( 'vpg_circle_prompts', [
        __( 'Bring one frame where the light did the work, not you.', 'vpg-v2' ),
        __( 'A photograph you almost deleted — argue for it.', 'vpg-v2' ),
        __( 'Show a picture that breaks a rule you usually keep.', 'vpg-v2' ),
        __( 'One image, no crop — what would you reframe now?', 'vpg-v2' ),
        __( 'The quietest photo you made this month.', 'vpg-v2' ),
        __( 'A frame that only works because of what is left out.', 'vpg-v2' ),
    ] );
}

add_action( 'vpg_circle_rounds', function () {
    wp_schedule_single_event( strtotime( 'first day of next month 09:00' ), 'vpg_circle_rounds' );

    $circles = get_posts( [
        'post_type'      => 'vpg_project',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'meta_key'       => '_vpg_circle',
        'meta_value'     => '1',
    ] );
    if ( ! $circles ) return;

    $prompts = vpg_circle_prompts();
    foreach ( $circles as $c ) {
        if ( get_post_meta( $c->ID, '_vpg_project_done', true ) ) continue;
        $prompt = $prompts[ (int) gmdate( 'n' ) % count( $prompts ) ];
        update_post_meta( $c->ID, '_vpg_circle_prompt', $prompt );
        update_post_meta( $c->ID, '_vpg_circle_prompt_at', current_time( 'mysql' ) );
        if ( function_exists( 'vpg_project_members' ) && function_exists( 'vpg_notify_user' ) ) {
            foreach ( vpg_project_members( $c->ID ) as $mid ) {
                vpg_notify_user( (int) $mid,
                    sprintf( __( 'This month in “%1$s”: %2$s', 'vpg-v2' ), get_the_title( $c->ID ), $prompt ),
                    get_permalink( $c->ID ), 'circle' );
            }
        }
    }
} );

/* ─── 1027 · Translation watch · list untranslated theme strings ── */
function vpg_untranslated_strings( $limit = 400 ) {
    $pot = get_template_directory() . '/languages/vpg-v2.pot';
    $mo  = get_template_directory() . '/languages/de_DE.mo';
    if ( ! is_readable( $pot ) || ! is_readable( $mo ) ) return null;

    // msgids the POT knows about
    $ids = [];
    foreach ( preg_split( '/\r?\n/', (string) file_get_contents( $pot ) ) as $line ) {
        if ( strncmp( $line, 'msgid "', 7 ) === 0 ) {
            $raw = substr( $line, 7, -1 );
            if ( $raw !== '' ) $ids[ stripcslashes( $raw ) ] = true;
        }
    }
    // msgids the compiled .mo actually translates
    if ( ! class_exists( 'MO' ) ) require_once ABSPATH . WPINC . '/pomo/mo.php';
    $reader = new MO();
    if ( ! $reader->import_from_file( $mo ) ) return null;
    $have = [];
    foreach ( $reader->entries as $key => $entry ) {
        $t = is_array( $entry->translations ) ? implode( '', $entry->translations ) : '';
        if ( trim( $t ) !== '' ) $have[ $entry->singular ] = true;
    }

    $missing = [];
    foreach ( array_keys( $ids ) as $id ) {
        if ( ! isset( $have[ $id ] ) ) $missing[] = $id;
        if ( count( $missing ) >= $limit ) break;
    }
    return $missing;
}

add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Translations', 'vpg-v2' ), __( '🌐 Translations', 'vpg-v2' ), 'edit_others_posts', 'vpg-i18n', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        $missing = vpg_untranslated_strings();
        echo '<div class="wrap"><h1>🌐 ' . esc_html__( 'Translation watch', 'vpg-v2' ) . '</h1>';
        if ( $missing === null ) {
            echo '<p>' . esc_html__( 'Language files not found in the theme’s /languages folder.', 'vpg-v2' ) . '</p></div>';
            return;
        }
        if ( ! $missing ) {
            echo '<p class="notice notice-success" style="padding:10px">' . esc_html__( 'Every string the POT knows is translated to German. ✓', 'vpg-v2' ) . '</p></div>';
            return;
        }
        printf( '<p>%s</p>', esc_html( sprintf( _n( '%d string still needs a German translation:', '%d strings still need a German translation:', count( $missing ), 'vpg-v2' ), count( $missing ) ) ) );
        echo '<ol style="max-width:760px;font-family:ui-monospace,monospace;font-size:12px;line-height:1.7">';
        foreach ( $missing as $m ) echo '<li>' . esc_html( $m ) . '</li>';
        echo '</ol></div>';
    } );
}, 18 );
