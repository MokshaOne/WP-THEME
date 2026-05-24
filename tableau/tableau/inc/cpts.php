<?php
/**
 * Tableau — Portfolio CPT.
 * One CPT named TABLEAU_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'TABLEAU_project', [
        'label'         => __( 'Projects', 'TABLEAU' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'TABLEAU' ),
            'singular_name' => __( 'Project',  'TABLEAU' ),
            'add_new'       => __( 'Add project', 'TABLEAU' ),
            'add_new_item'  => __( 'Add new project', 'TABLEAU' ),
            'edit_item'     => __( 'Edit project', 'TABLEAU' ),
            'view_item'     => __( 'View project', 'TABLEAU' ),
            'all_items'     => __( 'All projects', 'TABLEAU' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'TABLEAU_project_meta', __( 'Project details', 'TABLEAU' ), function ( $post ) {
        wp_nonce_field( 'TABLEAU_project_meta', 'TABLEAU_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'TABLEAU_year', true );
        $lede = get_post_meta( $post->ID, 'TABLEAU_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'TABLEAU' ) . '</strong><br>';
        echo '<input type="text" name="TABLEAU_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'TABLEAU' ) . '</strong><br>';
        echo '<input type="text" name="TABLEAU_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'TABLEAU_project', 'side', 'high' );
} );

add_action( 'save_post_TABLEAU_project', function ( $post_id ) {
    if ( ! isset( $_POST['TABLEAU_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['TABLEAU_project_meta_nonce'], 'TABLEAU_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['TABLEAU_year'] ) ) update_post_meta( $post_id, 'TABLEAU_year', sanitize_text_field( wp_unslash( $_POST['TABLEAU_year'] ) ) );
    if ( isset( $_POST['TABLEAU_lede'] ) ) update_post_meta( $post_id, 'TABLEAU_lede', sanitize_text_field( wp_unslash( $_POST['TABLEAU_lede'] ) ) );
} );
