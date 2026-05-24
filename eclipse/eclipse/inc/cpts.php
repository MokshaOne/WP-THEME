<?php
/**
 * Eclipse — Portfolio CPT.
 * One CPT named ECLIPSE_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'ECLIPSE_project', [
        'label'         => __( 'Projects', 'ECLIPSE' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'ECLIPSE' ),
            'singular_name' => __( 'Project',  'ECLIPSE' ),
            'add_new'       => __( 'Add project', 'ECLIPSE' ),
            'add_new_item'  => __( 'Add new project', 'ECLIPSE' ),
            'edit_item'     => __( 'Edit project', 'ECLIPSE' ),
            'view_item'     => __( 'View project', 'ECLIPSE' ),
            'all_items'     => __( 'All projects', 'ECLIPSE' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'ECLIPSE_project_meta', __( 'Project details', 'ECLIPSE' ), function ( $post ) {
        wp_nonce_field( 'ECLIPSE_project_meta', 'ECLIPSE_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'ECLIPSE_year', true );
        $lede = get_post_meta( $post->ID, 'ECLIPSE_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'ECLIPSE' ) . '</strong><br>';
        echo '<input type="text" name="ECLIPSE_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'ECLIPSE' ) . '</strong><br>';
        echo '<input type="text" name="ECLIPSE_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'ECLIPSE_project', 'side', 'high' );
} );

add_action( 'save_post_ECLIPSE_project', function ( $post_id ) {
    if ( ! isset( $_POST['ECLIPSE_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['ECLIPSE_project_meta_nonce'], 'ECLIPSE_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['ECLIPSE_year'] ) ) update_post_meta( $post_id, 'ECLIPSE_year', sanitize_text_field( wp_unslash( $_POST['ECLIPSE_year'] ) ) );
    if ( isset( $_POST['ECLIPSE_lede'] ) ) update_post_meta( $post_id, 'ECLIPSE_lede', sanitize_text_field( wp_unslash( $_POST['ECLIPSE_lede'] ) ) );
} );
