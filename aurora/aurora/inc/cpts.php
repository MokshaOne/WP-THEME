<?php
/**
 * Aurora — Portfolio CPT.
 * One CPT named AURORA_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'AURORA_project', [
        'label'         => __( 'Projects', 'AURORA' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'AURORA' ),
            'singular_name' => __( 'Project',  'AURORA' ),
            'add_new'       => __( 'Add project', 'AURORA' ),
            'add_new_item'  => __( 'Add new project', 'AURORA' ),
            'edit_item'     => __( 'Edit project', 'AURORA' ),
            'view_item'     => __( 'View project', 'AURORA' ),
            'all_items'     => __( 'All projects', 'AURORA' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'AURORA_project_meta', __( 'Project details', 'AURORA' ), function ( $post ) {
        wp_nonce_field( 'AURORA_project_meta', 'AURORA_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'AURORA_year', true );
        $lede = get_post_meta( $post->ID, 'AURORA_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'AURORA' ) . '</strong><br>';
        echo '<input type="text" name="AURORA_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'AURORA' ) . '</strong><br>';
        echo '<input type="text" name="AURORA_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'AURORA_project', 'side', 'high' );
} );

add_action( 'save_post_AURORA_project', function ( $post_id ) {
    if ( ! isset( $_POST['AURORA_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['AURORA_project_meta_nonce'], 'AURORA_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['AURORA_year'] ) ) update_post_meta( $post_id, 'AURORA_year', sanitize_text_field( wp_unslash( $_POST['AURORA_year'] ) ) );
    if ( isset( $_POST['AURORA_lede'] ) ) update_post_meta( $post_id, 'AURORA_lede', sanitize_text_field( wp_unslash( $_POST['AURORA_lede'] ) ) );
} );
