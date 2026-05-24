<?php
/**
 * Orbit — Portfolio CPT.
 * One CPT named ORBIT_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'ORBIT_project', [
        'label'         => __( 'Projects', 'ORBIT' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'ORBIT' ),
            'singular_name' => __( 'Project',  'ORBIT' ),
            'add_new'       => __( 'Add project', 'ORBIT' ),
            'add_new_item'  => __( 'Add new project', 'ORBIT' ),
            'edit_item'     => __( 'Edit project', 'ORBIT' ),
            'view_item'     => __( 'View project', 'ORBIT' ),
            'all_items'     => __( 'All projects', 'ORBIT' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'ORBIT_project_meta', __( 'Project details', 'ORBIT' ), function ( $post ) {
        wp_nonce_field( 'ORBIT_project_meta', 'ORBIT_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'ORBIT_year', true );
        $lede = get_post_meta( $post->ID, 'ORBIT_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'ORBIT' ) . '</strong><br>';
        echo '<input type="text" name="ORBIT_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'ORBIT' ) . '</strong><br>';
        echo '<input type="text" name="ORBIT_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'ORBIT_project', 'side', 'high' );
} );

add_action( 'save_post_ORBIT_project', function ( $post_id ) {
    if ( ! isset( $_POST['ORBIT_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['ORBIT_project_meta_nonce'], 'ORBIT_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['ORBIT_year'] ) ) update_post_meta( $post_id, 'ORBIT_year', sanitize_text_field( wp_unslash( $_POST['ORBIT_year'] ) ) );
    if ( isset( $_POST['ORBIT_lede'] ) ) update_post_meta( $post_id, 'ORBIT_lede', sanitize_text_field( wp_unslash( $_POST['ORBIT_lede'] ) ) );
} );
