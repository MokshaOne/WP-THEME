<?php
/**
 * Bauhaus — Portfolio CPT.
 * One CPT named BAUHAUS_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'BAUHAUS_project', [
        'label'         => __( 'Projects', 'BAUHAUS' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'BAUHAUS' ),
            'singular_name' => __( 'Project',  'BAUHAUS' ),
            'add_new'       => __( 'Add project', 'BAUHAUS' ),
            'add_new_item'  => __( 'Add new project', 'BAUHAUS' ),
            'edit_item'     => __( 'Edit project', 'BAUHAUS' ),
            'view_item'     => __( 'View project', 'BAUHAUS' ),
            'all_items'     => __( 'All projects', 'BAUHAUS' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'BAUHAUS_project_meta', __( 'Project details', 'BAUHAUS' ), function ( $post ) {
        wp_nonce_field( 'BAUHAUS_project_meta', 'BAUHAUS_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'BAUHAUS_year', true );
        $lede = get_post_meta( $post->ID, 'BAUHAUS_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'BAUHAUS' ) . '</strong><br>';
        echo '<input type="text" name="BAUHAUS_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'BAUHAUS' ) . '</strong><br>';
        echo '<input type="text" name="BAUHAUS_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'BAUHAUS_project', 'side', 'high' );
} );

add_action( 'save_post_BAUHAUS_project', function ( $post_id ) {
    if ( ! isset( $_POST['BAUHAUS_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['BAUHAUS_project_meta_nonce'], 'BAUHAUS_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['BAUHAUS_year'] ) ) update_post_meta( $post_id, 'BAUHAUS_year', sanitize_text_field( wp_unslash( $_POST['BAUHAUS_year'] ) ) );
    if ( isset( $_POST['BAUHAUS_lede'] ) ) update_post_meta( $post_id, 'BAUHAUS_lede', sanitize_text_field( wp_unslash( $_POST['BAUHAUS_lede'] ) ) );
} );
