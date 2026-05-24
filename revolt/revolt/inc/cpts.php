<?php
/**
 * Revolt — Portfolio CPT.
 * One CPT named REVOLT_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'REVOLT_project', [
        'label'         => __( 'Projects', 'REVOLT' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'REVOLT' ),
            'singular_name' => __( 'Project',  'REVOLT' ),
            'add_new'       => __( 'Add project', 'REVOLT' ),
            'add_new_item'  => __( 'Add new project', 'REVOLT' ),
            'edit_item'     => __( 'Edit project', 'REVOLT' ),
            'view_item'     => __( 'View project', 'REVOLT' ),
            'all_items'     => __( 'All projects', 'REVOLT' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'REVOLT_project_meta', __( 'Project details', 'REVOLT' ), function ( $post ) {
        wp_nonce_field( 'REVOLT_project_meta', 'REVOLT_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'REVOLT_year', true );
        $lede = get_post_meta( $post->ID, 'REVOLT_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'REVOLT' ) . '</strong><br>';
        echo '<input type="text" name="REVOLT_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'REVOLT' ) . '</strong><br>';
        echo '<input type="text" name="REVOLT_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'REVOLT_project', 'side', 'high' );
} );

add_action( 'save_post_REVOLT_project', function ( $post_id ) {
    if ( ! isset( $_POST['REVOLT_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['REVOLT_project_meta_nonce'], 'REVOLT_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['REVOLT_year'] ) ) update_post_meta( $post_id, 'REVOLT_year', sanitize_text_field( wp_unslash( $_POST['REVOLT_year'] ) ) );
    if ( isset( $_POST['REVOLT_lede'] ) ) update_post_meta( $post_id, 'REVOLT_lede', sanitize_text_field( wp_unslash( $_POST['REVOLT_lede'] ) ) );
} );
