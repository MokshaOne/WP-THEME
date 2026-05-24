<?php
/**
 * Verse — Portfolio CPT.
 * One CPT named VERSE_project · archive at /portfolio.
 * Two custom meta fields per project: year + lede.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
    register_post_type( 'VERSE_project', [
        'label'         => __( 'Projects', 'VERSE' ),
        'public'        => true,
        'show_in_rest'  => true,
        'menu_position' => 22,
        'menu_icon'     => 'dashicons-portfolio',
        'has_archive'   => 'portfolio',
        'rewrite'       => [ 'slug' => 'project', 'with_front' => false ],
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
        'labels'        => [
            'name'          => __( 'Projects', 'VERSE' ),
            'singular_name' => __( 'Project',  'VERSE' ),
            'add_new'       => __( 'Add project', 'VERSE' ),
            'add_new_item'  => __( 'Add new project', 'VERSE' ),
            'edit_item'     => __( 'Edit project', 'VERSE' ),
            'view_item'     => __( 'View project', 'VERSE' ),
            'all_items'     => __( 'All projects', 'VERSE' ),
        ],
    ] );
} );

// Simple meta box · year + lede (no ACF required)
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'VERSE_project_meta', __( 'Project details', 'VERSE' ), function ( $post ) {
        wp_nonce_field( 'VERSE_project_meta', 'VERSE_project_meta_nonce' );
        $year = get_post_meta( $post->ID, 'VERSE_year', true );
        $lede = get_post_meta( $post->ID, 'VERSE_lede', true );
        echo '<p><label><strong>' . esc_html__( 'Year', 'VERSE' ) . '</strong><br>';
        echo '<input type="text" name="VERSE_year" value="' . esc_attr( $year ) . '" style="width:100%" placeholder="2024"></label></p>';
        echo '<p><label><strong>' . esc_html__( 'One-line description', 'VERSE' ) . '</strong><br>';
        echo '<input type="text" name="VERSE_lede" value="' . esc_attr( $lede ) . '" style="width:100%" placeholder="Tamil poetry archive · South Asian editorial"></label></p>';
    }, 'VERSE_project', 'side', 'high' );
} );

add_action( 'save_post_VERSE_project', function ( $post_id ) {
    if ( ! isset( $_POST['VERSE_project_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['VERSE_project_meta_nonce'], 'VERSE_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['VERSE_year'] ) ) update_post_meta( $post_id, 'VERSE_year', sanitize_text_field( wp_unslash( $_POST['VERSE_year'] ) ) );
    if ( isset( $_POST['VERSE_lede'] ) ) update_post_meta( $post_id, 'VERSE_lede', sanitize_text_field( wp_unslash( $_POST['VERSE_lede'] ) ) );
} );
