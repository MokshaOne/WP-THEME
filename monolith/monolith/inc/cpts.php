<?php
/**
 * Monolith — Portfolio CPT.
 * One CPT named MONOLITH_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'MONOLITH_project', [
        'label'         => __( 'Projects', 'MONOLITH' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'MONOLITH' ),
            'singular_name' => __( 'Project',  'MONOLITH' ),
            'add_new'       => __( 'Add project', 'MONOLITH' ),
            'add_new_item'  => __( 'Add new project', 'MONOLITH' ),
            'edit_item'     => __( 'Edit project', 'MONOLITH' ),
            'view_item'     => __( 'View project', 'MONOLITH' ),
            'all_items'     => __( 'All projects', 'MONOLITH' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'MONOLITH_project_meta', __( 'Project details', 'MONOLITH' ), function ( $post ) {
        wp_nonce_field( 'MONOLITH_project_meta', 'MONOLITH_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'MONOLITH_year', true );
        $lede = get_post_meta( $post->ID, 'MONOLITH_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'MONOLITH' ) . '</strong><br>';
        echo '<input type="text" name="MONOLITH_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'MONOLITH' ) . '</strong><br>';
        echo '<input type="text" name="MONOLITH_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'MONOLITH_project', 'side', 'high' );
} );

add_action( 'save_post_MONOLITH_project', function ( $post_id ) {
    if ( ! isset( $_POST['MONOLITH_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['MONOLITH_project_meta_nonce'], 'MONOLITH_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['MONOLITH_year'] ) ) update_post_meta( $post_id, 'MONOLITH_year', sanitize_text_field( wp_unslash( $_POST['MONOLITH_year'] ) ) );
    if ( isset( $_POST['MONOLITH_lede'] ) ) update_post_meta( $post_id, 'MONOLITH_lede', sanitize_text_field( wp_unslash( $_POST['MONOLITH_lede'] ) ) );
} );
