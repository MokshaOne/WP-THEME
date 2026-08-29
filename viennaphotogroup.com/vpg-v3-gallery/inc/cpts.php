<?php
/**
 * VPG v2 — Custom post types & taxonomies.
 * Seven CPTs for the seven editorial surfaces.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {

    $defaults = [
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author' ],
        'has_archive'   => true,
        'rewrite'       => [ 'with_front' => false ],
    ];

    /*
     * Admin menu clusters — four groups instead of nine top-level entries:
     *   📖 Magazine (vpg-magazine hub) · issues, reviews, tutorials
     *   📍 The Map (locations)         · studios, shops, trails
     *   👥 Community (events)          · competitions (+ submissions, reports)
     * 'show_in_menu' nests a CPT under a cluster head; heads set menu_name.
     */
    $types = [
        'vpg_magazine' => [
            'label_plural'   => __( 'Magazine issues', 'vpg-v2' ),
            'label_singular' => __( 'Issue', 'vpg-v2' ),
            'slug'           => 'magazine',
            'menu_icon'      => 'dashicons-book-alt',
            'has_archive'    => 'magazine',
            'supports'       => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ],
            'show_in_menu'   => 'vpg-magazine',
        ],
        'vpg_event' => [
            'label_plural'   => __( 'Events', 'vpg-v2' ),
            'label_singular' => __( 'Event', 'vpg-v2' ),
            'slug'           => 'event',
            'menu_icon'      => 'dashicons-groups',
            'has_archive'    => 'events',
            'menu_name'      => __( 'Community', 'vpg-v2' ),
            'menu_position'  => 20,
        ],
        'vpg_location' => [
            'label_plural'   => __( 'Locations', 'vpg-v2' ),
            'label_singular' => __( 'Location', 'vpg-v2' ),
            'slug'           => 'location',
            'menu_icon'      => 'dashicons-location-alt',
            'has_archive'    => 'locations',
            'menu_name'      => __( 'The Map', 'vpg-v2' ),
            'menu_position'  => 21,
        ],
        'vpg_studio' => [
            'label_plural'   => __( 'Studios', 'vpg-v2' ),
            'label_singular' => __( 'Studio', 'vpg-v2' ),
            'slug'           => 'studio',
            'menu_icon'      => 'dashicons-camera-alt',
            'has_archive'    => 'studios',
            'show_in_menu'   => 'edit.php?post_type=vpg_location',
        ],
        'vpg_shop' => [
            'label_plural'   => __( 'Shops', 'vpg-v2' ),
            'label_singular' => __( 'Shop', 'vpg-v2' ),
            'slug'           => 'shop',
            'menu_icon'      => 'dashicons-store',
            'has_archive'    => 'shops',
            'show_in_menu'   => 'edit.php?post_type=vpg_location',
        ],
        'vpg_review' => [
            'label_plural'   => __( 'Reviews', 'vpg-v2' ),
            'label_singular' => __( 'Review', 'vpg-v2' ),
            'slug'           => 'review',
            'menu_icon'      => 'dashicons-star-filled',
            'has_archive'    => 'buying-guide',
            'show_in_menu'   => 'vpg-magazine',
        ],
        'vpg_tutorial' => [
            'label_plural'   => __( 'Tutorials', 'vpg-v2' ),
            'label_singular' => __( 'Tutorial', 'vpg-v2' ),
            'slug'           => 'tutorial',
            'menu_icon'      => 'dashicons-welcome-learn-more',
            'has_archive'    => 'tutorials',
            'show_in_menu'   => 'vpg-magazine',
        ],
        'vpg_trail' => [
            'label_plural'   => __( 'Photowalk trails', 'vpg-v2' ),
            'label_singular' => __( 'Trail', 'vpg-v2' ),
            'slug'           => 'trail',
            'menu_icon'      => 'dashicons-randomize',
            'has_archive'    => 'trails',
            'show_in_menu'   => 'edit.php?post_type=vpg_location',
        ],
        'vpg_competition' => [
            'label_plural'   => __( 'Competitions', 'vpg-v2' ),
            'label_singular' => __( 'Competition', 'vpg-v2' ),
            'slug'           => 'competition',
            'menu_icon'      => 'dashicons-awards',
            'has_archive'    => 'competitions',
            'show_in_menu'   => 'edit.php?post_type=vpg_event',
        ],
    ];

    foreach ( $types as $slug => $cfg ) {
        $args = array_merge( $defaults, [
            'label'         => $cfg['label_plural'],
            'rewrite'       => [ 'slug' => $cfg['slug'], 'with_front' => false ],
            'menu_icon'     => $cfg['menu_icon'],
            'has_archive'   => $cfg['has_archive'],
            'supports'      => $cfg['supports'] ?? $defaults['supports'],
            'show_in_menu'  => $cfg['show_in_menu'] ?? true,
            'menu_position' => $cfg['menu_position'] ?? $defaults['menu_position'],
            'labels' => [
                'name'          => $cfg['label_plural'],
                'singular_name' => $cfg['label_singular'],
                'add_new'       => sprintf( __( 'Add %s', 'vpg-v2' ), $cfg['label_singular'] ),
                'add_new_item'  => sprintf( __( 'Add new %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'edit_item'     => sprintf( __( 'Edit %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'view_item'     => sprintf( __( 'View %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'all_items'     => sprintf( __( 'All %s', 'vpg-v2' ), strtolower( $cfg['label_plural'] ) ),
                'search_items'  => sprintf( __( 'Search %s', 'vpg-v2' ), strtolower( $cfg['label_plural'] ) ),
                'menu_name'     => $cfg['menu_name'] ?? $cfg['label_plural'],
            ],
        ] );
        register_post_type( $slug, $args );
    }

    // ─── Taxonomies ───
    register_taxonomy( 'gear_type', 'vpg_review', [
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'gear' ],
        'labels'       => [
            'name'          => __( 'Gear types', 'vpg-v2' ),
            'singular_name' => __( 'Gear type', 'vpg-v2' ),
        ],
    ] );

    register_taxonomy( 'location_type', 'vpg_location', [
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'location-type' ],
        'labels'       => [
            'name'          => __( 'Location types', 'vpg-v2' ),
            'singular_name' => __( 'Location type', 'vpg-v2' ),
        ],
    ] );

    register_taxonomy( 'event_kind', 'vpg_event', [
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'event-kind' ],
        'labels'       => [
            'name'          => __( 'Event kinds', 'vpg-v2' ),
            'singular_name' => __( 'Event kind', 'vpg-v2' ),
        ],
    ] );

    // ─── /studios/ and /shops/ archives redirect to the unified /locations/ map ───
    // Single-post permalinks (/studio/{slug}/, /shop/{slug}/) keep working.
    add_action( 'template_redirect', function () {
        if ( is_post_type_archive( 'vpg_studio' ) ) {
            wp_safe_redirect( add_query_arg( 'type', 'studio', get_post_type_archive_link( 'vpg_location' ) ), 301 );
            exit;
        }
        if ( is_post_type_archive( 'vpg_shop' ) ) {
            wp_safe_redirect( add_query_arg( 'type', 'shop', get_post_type_archive_link( 'vpg_location' ) ), 301 );
            exit;
        }
    } );

    register_taxonomy( 'tutorial_level', 'vpg_tutorial', [
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'rewrite'      => [ 'slug' => 'level' ],
        'labels'       => [
            'name'          => __( 'Levels', 'vpg-v2' ),
            'singular_name' => __( 'Level', 'vpg-v2' ),
        ],
    ] );
} );
