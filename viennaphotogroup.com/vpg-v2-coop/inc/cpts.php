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
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
        'has_archive'   => true,
        'rewrite'       => [ 'with_front' => false ],
    ];

    $types = [
        'vpg_magazine' => [
            'label_plural'   => __( 'Magazine issues', 'vpg-v2' ),
            'label_singular' => __( 'Issue', 'vpg-v2' ),
            'slug'           => 'magazine',
            'menu_icon'      => 'dashicons-book-alt',
            'has_archive'    => 'magazine',
            'supports'       => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ],
        ],
        'vpg_event' => [
            'label_plural'   => __( 'Events', 'vpg-v2' ),
            'label_singular' => __( 'Event', 'vpg-v2' ),
            'slug'           => 'event',
            'menu_icon'      => 'dashicons-calendar-alt',
            'has_archive'    => 'events',
        ],
        'vpg_location' => [
            'label_plural'   => __( 'Locations', 'vpg-v2' ),
            'label_singular' => __( 'Location', 'vpg-v2' ),
            'slug'           => 'location',
            'menu_icon'      => 'dashicons-location-alt',
            'has_archive'    => 'locations',
        ],
        'vpg_studio' => [
            'label_plural'   => __( 'Studios', 'vpg-v2' ),
            'label_singular' => __( 'Studio', 'vpg-v2' ),
            'slug'           => 'studio',
            'menu_icon'      => 'dashicons-camera-alt',
            'has_archive'    => 'studios',
        ],
        'vpg_shop' => [
            'label_plural'   => __( 'Shops', 'vpg-v2' ),
            'label_singular' => __( 'Shop', 'vpg-v2' ),
            'slug'           => 'shop',
            'menu_icon'      => 'dashicons-store',
            'has_archive'    => 'shops',
        ],
        'vpg_review' => [
            'label_plural'   => __( 'Reviews', 'vpg-v2' ),
            'label_singular' => __( 'Review', 'vpg-v2' ),
            'slug'           => 'review',
            'menu_icon'      => 'dashicons-star-filled',
            'has_archive'    => 'buying-guide',
        ],
        'vpg_tutorial' => [
            'label_plural'   => __( 'Tutorials', 'vpg-v2' ),
            'label_singular' => __( 'Tutorial', 'vpg-v2' ),
            'slug'           => 'tutorial',
            'menu_icon'      => 'dashicons-welcome-learn-more',
            'has_archive'    => 'tutorials',
        ],
    ];

    foreach ( $types as $slug => $cfg ) {
        $args = array_merge( $defaults, [
            'label'        => $cfg['label_plural'],
            'rewrite'      => [ 'slug' => $cfg['slug'], 'with_front' => false ],
            'menu_icon'    => $cfg['menu_icon'],
            'has_archive'  => $cfg['has_archive'],
            'supports'     => $cfg['supports'] ?? $defaults['supports'],
            'labels' => [
                'name'          => $cfg['label_plural'],
                'singular_name' => $cfg['label_singular'],
                'add_new'       => sprintf( __( 'Add %s', 'vpg-v2' ), $cfg['label_singular'] ),
                'add_new_item'  => sprintf( __( 'Add new %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'edit_item'     => sprintf( __( 'Edit %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'view_item'     => sprintf( __( 'View %s', 'vpg-v2' ), strtolower( $cfg['label_singular'] ) ),
                'all_items'     => sprintf( __( 'All %s', 'vpg-v2' ), strtolower( $cfg['label_plural'] ) ),
                'search_items'  => sprintf( __( 'Search %s', 'vpg-v2' ), strtolower( $cfg['label_plural'] ) ),
                'menu_name'     => $cfg['label_plural'],
            ],
        ] );
        register_post_type( $slug, $args );
    }

    // ─── Taxonomies ───
    register_taxonomy( 'gear_type', 'vpg_review', [
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'gear' ],
        'labels'       => [
            'name'          => __( 'Gear types', 'vpg-v2' ),
            'singular_name' => __( 'Gear type', 'vpg-v2' ),
        ],
    ] );

    register_taxonomy( 'location_type', 'vpg_location', [
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'location-type' ],
        'labels'       => [
            'name'          => __( 'Location types', 'vpg-v2' ),
            'singular_name' => __( 'Location type', 'vpg-v2' ),
        ],
    ] );

    register_taxonomy( 'event_kind', 'vpg_event', [
        'public'       => true,
        'show_in_rest' => true,
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
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite'      => [ 'slug' => 'level' ],
        'labels'       => [
            'name'          => __( 'Levels', 'vpg-v2' ),
            'singular_name' => __( 'Level', 'vpg-v2' ),
        ],
    ] );
} );
