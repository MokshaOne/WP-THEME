<?php
/**
 * Phosphor — Portfolio CPT.
 * One CPT named PHOSPHOR_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'PHOSPHOR_project', [
        'label'         => __( 'Projects', 'PHOSPHOR' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'PHOSPHOR' ),
            'singular_name' => __( 'Project',  'PHOSPHOR' ),
            'add_new'       => __( 'Add project', 'PHOSPHOR' ),
            'add_new_item'  => __( 'Add new project', 'PHOSPHOR' ),
            'edit_item'     => __( 'Edit project', 'PHOSPHOR' ),
            'view_item'     => __( 'View project', 'PHOSPHOR' ),
            'all_items'     => __( 'All projects', 'PHOSPHOR' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'PHOSPHOR_project_meta', __( 'Project details', 'PHOSPHOR' ), function ( $post ) {
        wp_nonce_field( 'PHOSPHOR_project_meta', 'PHOSPHOR_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'PHOSPHOR_year', true );
        $lede = get_post_meta( $post->ID, 'PHOSPHOR_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'PHOSPHOR' ) . '</strong><br>';
        echo '<input type="text" name="PHOSPHOR_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'PHOSPHOR' ) . '</strong><br>';
        echo '<input type="text" name="PHOSPHOR_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'PHOSPHOR_project', 'side', 'high' );
} );

add_action( 'save_post_PHOSPHOR_project', function ( $post_id ) {
    if ( ! isset( $_POST['PHOSPHOR_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['PHOSPHOR_project_meta_nonce'], 'PHOSPHOR_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['PHOSPHOR_year'] ) ) update_post_meta( $post_id, 'PHOSPHOR_year', sanitize_text_field( wp_unslash( $_POST['PHOSPHOR_year'] ) ) );
    if ( isset( $_POST['PHOSPHOR_lede'] ) ) update_post_meta( $post_id, 'PHOSPHOR_lede', sanitize_text_field( wp_unslash( $_POST['PHOSPHOR_lede'] ) ) );
} );
