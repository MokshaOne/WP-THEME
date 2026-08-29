<?php
/**
 * VPG v3 — Q5 · collaboration & curation.
 *
 *   0401  Project rooms · shared series, Documentarian+ creates,
 *         members join and hang their own published works
 *   0136  Event galleries · members upload photos to a past walk
 *   0286  Gallery walls · curated attachment sets (vpg_wall)
 *   0576  Collections · curated cross-type sets (vpg_collection)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── CPTs · project rooms, walls, collections ──────────────────── */
add_action( 'init', function () {
    register_post_type( 'vpg_project', [
        'public'       => true,
        'show_in_rest' => true,
        'show_in_menu' => 'edit.php?post_type=vpg_event',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'author' ],
        'has_archive'  => 'projects',
        'rewrite'      => [ 'slug' => 'project', 'with_front' => false ],
        'labels'       => [ 'name' => __( 'Project rooms', 'vpg-v2' ), 'singular_name' => __( 'Project', 'vpg-v2' ) ],
    ] );
    register_post_type( 'vpg_wall', [
        'public'       => true,
        'show_in_rest' => true,
        'show_in_menu' => 'vpg-magazine',
        'supports'     => [ 'title', 'editor', 'thumbnail' ],
        'has_archive'  => 'walls',
        'rewrite'      => [ 'slug' => 'wall', 'with_front' => false ],
        'labels'       => [ 'name' => __( 'Gallery walls', 'vpg-v2' ), 'singular_name' => __( 'Wall', 'vpg-v2' ) ],
    ] );
    register_post_type( 'vpg_collection', [
        'public'       => true,
        'show_in_rest' => true,
        'show_in_menu' => 'vpg-magazine',
        'supports'     => [ 'title', 'editor', 'thumbnail' ],
        'has_archive'  => 'collections',
        'rewrite'      => [ 'slug' => 'collection', 'with_front' => false ],
        'labels'       => [ 'name' => __( 'Collections', 'vpg-v2' ), 'singular_name' => __( 'Collection', 'vpg-v2' ) ],
    ] );
}, 12 );

/* ─── Shared curated-ids metabox for walls (attachments) and
       collections (any published content) ────────────────────────── */
add_action( 'add_meta_boxes', function () {
    foreach ( [ 'vpg_wall' => __( 'Attachment IDs · comma-separated, hanging order', 'vpg-v2' ),
                'vpg_collection' => __( 'Post IDs · comma-separated, any public type', 'vpg-v2' ) ] as $cpt => $hint ) {
        add_meta_box( 'vpg-curated-ids', __( 'Curated set', 'vpg-v2' ), function ( $post ) use ( $hint ) {
            wp_nonce_field( 'vpg_curated_ids', 'vpg_curated_ids_nonce' );
            printf( '<p class="description">%s</p><input type="text" name="vpg_curated_ids" value="%s" style="width:100%%" placeholder="12, 87, 43">',
                esc_html( $hint ), esc_attr( get_post_meta( $post->ID, '_vpg_curated_ids', true ) ) );
        }, $cpt, 'normal', 'high' );
    }
} );

add_action( 'save_post', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_curated_ids_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_curated_ids_nonce'], 'vpg_curated_ids' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $ids = implode( ',', array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_POST['vpg_curated_ids'] ?? '' ) ) ) ) ) );
    update_post_meta( $post_id, '_vpg_curated_ids', $ids );
} );

function vpg_curated_ids( $post_id ) {
    return array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post_id, '_vpg_curated_ids', true ) ) ) );
}

/* ─── 0401 · Project rooms · create, join, hang works ───────────── */
function vpg_project_members( $pid ) {
    return array_values( array_filter( array_map( 'intval', (array) get_post_meta( $pid, '_vpg_project_members', true ) ) ) );
}

add_action( 'admin_post_vpg_project_create', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_project_create' );
    // Creating a room needs Documentarian standing — deep map knowledge first
    if ( ! current_user_can( 'edit_others_posts' ) && ( ! function_exists( 'vpg_member_rank' ) || vpg_member_rank( get_current_user_id() )['level'] < 2 ) ) {
        wp_die( esc_html__( 'Project rooms open at Documentarian.', 'vpg-v2' ), 403 );
    }
    $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $about = sanitize_textarea_field( wp_unslash( $_POST['about'] ?? '' ) );
    if ( ! $title ) { wp_safe_redirect( wp_get_referer() ?: home_url() ); exit; }

    $pid = wp_insert_post( [
        'post_type'    => 'vpg_project',
        'post_status'  => 'publish', // a room, not a publication — no review desk
        'post_title'   => $title,
        'post_content' => $about,
        'post_author'  => get_current_user_id(),
    ] );
    if ( $pid && ! is_wp_error( $pid ) ) {
        update_post_meta( $pid, '_vpg_project_members', [ get_current_user_id() ] );
        wp_safe_redirect( get_permalink( $pid ) );
        exit;
    }
    wp_safe_redirect( wp_get_referer() ?: home_url() );
    exit;
} );

add_action( 'admin_post_vpg_project_join', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_project_join' );
    $pid = (int) ( $_POST['project'] ?? 0 );
    if ( get_post_type( $pid ) === 'vpg_project' && get_post_status( $pid ) === 'publish' ) {
        $members = vpg_project_members( $pid );
        $uid     = get_current_user_id();
        if ( in_array( $uid, $members, true ) ) {
            if ( (int) get_post_field( 'post_author', $pid ) !== $uid ) { // the founder stays
                $members = array_values( array_diff( $members, [ $uid ] ) );
            }
        } else {
            $members[] = $uid;
            vpg_notify_user( (int) get_post_field( 'post_author', $pid ),
                sprintf( __( '%1$s joined your project “%2$s”.', 'vpg-v2' ), wp_get_current_user()->display_name, get_the_title( $pid ) ),
                get_permalink( $pid ) );
        }
        update_post_meta( $pid, '_vpg_project_members', $members );
    }
    wp_safe_redirect( get_permalink( $pid ) ?: home_url() );
    exit;
} );

add_action( 'admin_post_vpg_project_hang', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_project_hang' );
    $pid  = (int) ( $_POST['project'] ?? 0 );
    $work = get_post( (int) ( $_POST['work'] ?? 0 ) );
    $uid  = get_current_user_id();
    if ( get_post_type( $pid ) === 'vpg_project'
        && in_array( $uid, vpg_project_members( $pid ), true )
        && $work && $work->post_status === 'publish' && (int) $work->post_author === $uid ) {
        $works = array_filter( array_map( 'intval', (array) get_post_meta( $pid, '_vpg_project_works', true ) ) );
        if ( ! in_array( $work->ID, $works, true ) ) {
            $works[] = $work->ID;
            update_post_meta( $pid, '_vpg_project_works', array_slice( $works, -60 ) );
        }
    }
    wp_safe_redirect( get_permalink( $pid ) ?: home_url() );
    exit;
} );

/* ─── 0136 · Event gallery · photos of a walk, uploaded after ───── */
add_action( 'admin_post_vpg_event_photo', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_event_photo' );
    if ( function_exists( 'vpg_is_verified' ) && ! vpg_is_verified() ) wp_die( 'Verify your email first.', 403 );

    $event = get_post( (int) ( $_POST['event'] ?? 0 ) );
    if ( ! $event || $event->post_type !== 'vpg_event' || $event->post_status !== 'publish' ) wp_die( 'Event not found', 404 );

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    if ( ! empty( $_FILES['photo']['name'] ) ) {
        $check = wp_check_filetype( $_FILES['photo']['name'] );
        if ( in_array( strtolower( (string) $check['ext'] ), [ 'jpg', 'jpeg', 'png', 'webp', 'avif' ], true )
            && (int) $_FILES['photo']['size'] <= 8 * MB_IN_BYTES ) {
            $att = media_handle_upload( 'photo', 0 );
            if ( ! is_wp_error( $att ) ) {
                update_post_meta( $att, '_vpg_event_gallery', $event->ID );
                wp_update_post( [ 'ID' => $att, 'post_author' => get_current_user_id() ] );
                if ( function_exists( 'vpg_strip_and_credit_photo' ) ) vpg_strip_and_credit_photo( $att );
            }
        }
    }
    wp_safe_redirect( get_permalink( $event ) . '#gallery' );
    exit;
} );

function vpg_event_gallery( $event_id ) {
    return get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 48,
        'meta_key'       => '_vpg_event_gallery',
        'meta_value'     => $event_id,
    ] );
}
