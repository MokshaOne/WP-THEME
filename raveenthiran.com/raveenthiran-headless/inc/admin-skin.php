<?php
/**
 * Admin skin — the whole WordPress panel in the live site's language.
 *
 * One stylesheet, assets/admin.css: the v5.4 "Archived Brutalism" paper / ink
 * palette with one accent, Archivo + Archivo Black + Space Mono, hard 2px
 * edges — layered over the stock (light) admin and ACF.
 *
 * Plus a leaner menu / toolbar for a headless install and a branded login.
 * The block editor is left stock on purpose: it ships its own design system
 * and half-skinning it would make the screen used most the least reliable.
 *
 * @package RaveenthiranHeadless
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function rvn_skin_enqueue() {
        $ver  = wp_get_theme()->get( 'Version' );
        $base = get_template_directory_uri() . '/assets/';
        wp_enqueue_style( 'rvn-admin', $base . 'admin.css', array(), $ver );
}

/** True on block-editor screens, where the skin stays off. */
function rvn_skin_is_block_editor() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        return $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
}

/* Late, so the skin follows core, ACF and plugin styles in the cascade. */
add_action( 'admin_enqueue_scripts', function () {
        if ( rvn_skin_is_block_editor() ) { return; }
        rvn_skin_enqueue();
}, 9999 );
add_action( 'login_enqueue_scripts', 'rvn_skin_enqueue', 9999 );

/* The skin replaces the colour schemes — pin the default one and drop the picker,
   otherwise a scheme's own stylesheet would repaint the menu on top of the skin. */
add_filter( 'get_user_option_admin_color', function () { return 'fresh'; } );
add_action( 'admin_init', function () {
        remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
} );

/* ── Login ── */
add_filter( 'login_headertext', function () { return 'RAVEENTHIRAN'; } );
add_filter( 'login_headerurl', function () { return 'https://raveenthiran.com'; } );

/* ── Menu: what a headless photo site actually uses, in working order ── */
add_filter( 'post_type_labels_post', function ( $labels ) {
        $labels->name          = 'Journal';
        $labels->menu_name     = 'Journal';
        $labels->all_items     = 'All posts';
        $labels->name_admin_bar = 'Journal post';
        return $labels;
} );

add_action( 'admin_menu', function () {
        remove_menu_page( 'index.php' );         // Dashboard redirects to Site Control anyway
        remove_menu_page( 'edit-comments.php' ); // the static frontend has no comments
}, 999 );

add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function ( $order ) {
        $first = array(
                'site-control',
                'edit.php?post_type=work',
                'edit.php',
                'upload.php',
                'separator1',
                'edit.php?post_type=page',
        );
        $rest = array_values( array_diff( (array) $order, $first ) );
        return array_merge( array_values( array_intersect( $first, (array) $order ) ), $rest );
} );

/* ── Toolbar: no WordPress logo, no comments bubble ── */
add_action( 'admin_bar_menu', function ( $bar ) {
        $bar->remove_node( 'wp-logo' );
        $bar->remove_node( 'comments' );
}, 999 );

/* ── Footer ── */
add_filter( 'admin_footer_text', function () {
        return 'Raveenthiran · Site Control';
} );
add_filter( 'update_footer', function ( $text ) {
        return 'Theme v' . esc_html( wp_get_theme()->get( 'Version' ) ) . ' · ' . $text;
}, 20 );
