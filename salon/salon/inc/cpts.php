<?php
/**
 * Salon — Portfolio CPT.
 * One CPT named SALON_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'SALON_project', [
        'label'         => __( 'Projects', 'SALON' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'SALON' ),
            'singular_name' => __( 'Project',  'SALON' ),
            'add_new'       => __( 'Add project', 'SALON' ),
            'add_new_item'  => __( 'Add new project', 'SALON' ),
            'edit_item'     => __( 'Edit project', 'SALON' ),
            'view_item'     => __( 'View project', 'SALON' ),
            'all_items'     => __( 'All projects', 'SALON' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'SALON_project_meta', __( 'Project details', 'SALON' ), function ( $post ) {
        wp_nonce_field( 'SALON_project_meta', 'SALON_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'SALON_year', true );
        $lede = get_post_meta( $post->ID, 'SALON_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'SALON' ) . '</strong><br>';
        echo '<input type="text" name="SALON_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'SALON' ) . '</strong><br>';
        echo '<input type="text" name="SALON_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'SALON_project', 'side', 'high' );
} );

add_action( 'save_post_SALON_project', function ( $post_id ) {
    if ( ! isset( $_POST['SALON_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['SALON_project_meta_nonce'], 'SALON_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['SALON_year'] ) ) update_post_meta( $post_id, 'SALON_year', sanitize_text_field( wp_unslash( $_POST['SALON_year'] ) ) );
    if ( isset( $_POST['SALON_lede'] ) ) update_post_meta( $post_id, 'SALON_lede', sanitize_text_field( wp_unslash( $_POST['SALON_lede'] ) ) );
} );
