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
add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && in_array( $s->post_type ?? '', [ 'vpg_wall', 'vpg_collection' ], true ) ) wp_enqueue_media();
} );

add_action( 'add_meta_boxes', function () {
    foreach ( [ 'vpg_wall' => __( 'Attachment IDs · comma-separated, hanging order', 'vpg-v2' ),
                'vpg_collection' => __( 'Post IDs · comma-separated, any public type', 'vpg-v2' ) ] as $cpt => $hint ) {
        add_meta_box( 'vpg-curated-ids', __( 'Curated set', 'vpg-v2' ), function ( $post ) use ( $hint ) {
            wp_nonce_field( 'vpg_curated_ids', 'vpg_curated_ids_nonce' );
            printf( '<p class="description">%s</p><input type="text" id="vpg-curated-ids" name="vpg_curated_ids" value="%s" style="width:100%%" placeholder="12, 87, 43">',
                esc_html( $hint ), esc_attr( get_post_meta( $post->ID, '_vpg_curated_ids', true ) ) );
            // 1008 · nobody should have to hand-type IDs
            if ( $post->post_type === 'vpg_wall' ) : ?>
                <p><button type="button" class="button" id="vpg-curate-media">🖼 <?php esc_html_e( 'Pick images from the library', 'vpg-v2' ); ?></button></p>
                <script>
                jQuery(function () {
                    var btn = document.getElementById('vpg-curate-media'), field = document.getElementById('vpg-curated-ids');
                    if (!btn || !window.wp || !wp.media) return;
                    btn.addEventListener('click', function () {
                        var frame = wp.media({ multiple: 'add', library: { type: 'image' } });
                        frame.on('select', function () {
                            var ids = field.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                            frame.state().get('selection').forEach(function (a) {
                                if (ids.indexOf(String(a.id)) === -1) ids.push(String(a.id));
                            });
                            field.value = ids.join(', ');
                        });
                        frame.open();
                    });
                });
                </script>
            <?php else : ?>
                <p style="display:flex;gap:8px;align-items:center;max-width:520px">
                    <input type="search" id="vpg-curate-search" class="regular-text" placeholder="<?php esc_attr_e( 'Search the site, click to append…', 'vpg-v2' ); ?>" style="flex:1">
                </p>
                <div id="vpg-curate-results"></div>
                <script>
                (function () {
                    var q = document.getElementById('vpg-curate-search'), out = document.getElementById('vpg-curate-results'),
                        field = document.getElementById('vpg-curated-ids'), t = 0;
                    q.addEventListener('input', function () {
                        clearTimeout(t);
                        t = setTimeout(function () {
                            if (q.value.trim().length < 2) { out.innerHTML = ''; return; }
                            fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?> + '?action=vpg_live_search&q=' + encodeURIComponent(q.value.trim()), { credentials: 'same-origin' })
                                .then(function (r) { return r.json(); })
                                .then(function (res) {
                                    out.innerHTML = '';
                                    ((res && res.data) || []).forEach(function (it) {
                                        var b = document.createElement('button');
                                        b.type = 'button'; b.className = 'button button-small'; b.style.margin = '2px';
                                        b.textContent = '+ ' + it.title + ' (' + it.type + ')';
                                        b.addEventListener('click', function () {
                                            var ids = field.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                                            if (ids.indexOf(String(it.id)) === -1) ids.push(String(it.id));
                                            field.value = ids.join(', ');
                                        });
                                        out.appendChild(b);
                                    });
                                });
                        }, 250);
                    });
                })();
                </script>
            <?php endif;
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
            // 1005 · the room hears about a fresh hanging — except the hanger
            if ( function_exists( 'vpg_notify_user' ) ) {
                foreach ( array_diff( vpg_project_members( $pid ), [ $uid ] ) as $mid ) {
                    vpg_notify_user( (int) $mid,
                        sprintf( __( '%1$s hung “%2$s” in project “%3$s”.', 'vpg-v2' ), wp_get_current_user()->display_name, $work->post_title, get_the_title( $pid ) ),
                        get_permalink( $pid ) );
                }
            }
        }
    }
    wp_safe_redirect( get_permalink( $pid ) ?: home_url() );
    exit;
} );


/* 1003 · take a work down again — its owner or the founder decides */
add_action( 'admin_post_vpg_project_unhang', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_project_unhang' );
    $pid  = (int) ( $_POST['project'] ?? 0 );
    $wid  = (int) ( $_POST['work'] ?? 0 );
    $uid  = get_current_user_id();
    if ( get_post_type( $pid ) === 'vpg_project' ) {
        $is_founder = (int) get_post_field( 'post_author', $pid ) === $uid;
        $is_owner   = (int) get_post_field( 'post_author', $wid ) === $uid;
        if ( $is_founder || $is_owner || current_user_can( 'edit_others_posts' ) ) {
            $works = array_filter( array_map( 'intval', (array) get_post_meta( $pid, '_vpg_project_works', true ) ) );
            update_post_meta( $pid, '_vpg_project_works', array_values( array_diff( $works, [ $wid ] ) ) );
        }
    }
    wp_safe_redirect( get_permalink( $pid ) ?: home_url() );
    exit;
} );

/* 1004 · the founder declares the series finished — magazine-ready */
add_action( 'admin_post_vpg_project_finish', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_project_finish' );
    $pid = (int) ( $_POST['project'] ?? 0 );
    $uid = get_current_user_id();
    if ( get_post_type( $pid ) === 'vpg_project'
        && ( (int) get_post_field( 'post_author', $pid ) === $uid || current_user_can( 'edit_others_posts' ) ) ) {
        if ( get_post_meta( $pid, '_vpg_project_done', true ) ) {
            delete_post_meta( $pid, '_vpg_project_done' );
        } else {
            update_post_meta( $pid, '_vpg_project_done', current_time( 'mysql' ) );
            if ( function_exists( 'vpg_notify_user' ) ) {
                foreach ( get_users( [ 'role__in' => [ 'administrator', 'editor' ], 'fields' => 'ID' ] ) as $eid ) {
                    vpg_notify_user( (int) $eid, sprintf( __( 'Project “%s” is finished — a group series ready for the magazine.', 'vpg-v2' ), get_the_title( $pid ) ), get_permalink( $pid ) );
                }
            }
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

    // 1007 · five frames per member per walk keep the wall a selection
    $mine_here = get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'author'         => get_current_user_id(),
        'posts_per_page' => 6,
        'fields'         => 'ids',
        'meta_key'       => '_vpg_event_gallery',
        'meta_value'     => $event->ID,
    ] );
    if ( count( $mine_here ) >= 5 ) {
        wp_safe_redirect( get_permalink( $event ) . '#gallery' );
        exit;
    }

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
