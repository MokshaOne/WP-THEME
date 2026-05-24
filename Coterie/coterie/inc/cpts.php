<?php
/**
 * Coterie — Portfolio CPT.
 * One CPT named COTERIE_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'COTERIE_project', [
        'label'         => __( 'Projects', 'COTERIE' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'COTERIE' ),
            'singular_name' => __( 'Project',  'COTERIE' ),
            'add_new'       => __( 'Add project', 'COTERIE' ),
            'add_new_item'  => __( 'Add new project', 'COTERIE' ),
            'edit_item'     => __( 'Edit project', 'COTERIE' ),
            'view_item'     => __( 'View project', 'COTERIE' ),
            'all_items'     => __( 'All projects', 'COTERIE' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'COTERIE_project_meta', __( 'Project details', 'COTERIE' ), function ( $post ) {
        wp_nonce_field( 'COTERIE_project_meta', 'COTERIE_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'COTERIE_year', true );
        $lede = get_post_meta( $post->ID, 'COTERIE_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'COTERIE' ) . '</strong><br>';
        echo '<input type="text" name="COTERIE_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'COTERIE' ) . '</strong><br>';
        echo '<input type="text" name="COTERIE_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'COTERIE_project', 'side', 'high' );
} );

add_action( 'save_post_COTERIE_project', function ( $post_id ) {
    if ( ! isset( $_POST['COTERIE_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['COTERIE_project_meta_nonce'], 'COTERIE_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['COTERIE_year'] ) ) update_post_meta( $post_id, 'COTERIE_year', sanitize_text_field( wp_unslash( $_POST['COTERIE_year'] ) ) );
    if ( isset( $_POST['COTERIE_lede'] ) ) update_post_meta( $post_id, 'COTERIE_lede', sanitize_text_field( wp_unslash( $_POST['COTERIE_lede'] ) ) );
} );
