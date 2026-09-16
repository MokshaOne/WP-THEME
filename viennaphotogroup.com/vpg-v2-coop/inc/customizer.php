<?php
/**
 * VPG v2 — Customizer · palette + identity overrides.
 * Front-page content lives in the magazine editor + CPT posts;
 * the Customizer only handles cross-site identity + colours.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'customize_register', function ( $wp ) {

    $wp->add_panel( 'vpg_v2', [ 'title' => 'VPG v2 · ' . __( 'Theme', 'vpg-v2' ), 'priority' => 30 ] );

    /* ── Identity ────────────────────────────────── */
    $wp->add_section( 'vpg_identity', [ 'title' => __( 'Identity', 'vpg-v2' ), 'panel' => 'vpg_v2' ] );
    foreach ( [
        'vpg_email'    => [ 'Email',         get_option( 'admin_email' ) ],
        'vpg_location' => [ 'Location',      'Wien · UTC+1' ],
        'vpg_booking'  => [ 'Booking',       'Q3 · slots open' ],
        'vpg_since'    => [ 'Founded year',  '2018' ],
        'vpg_social_instagram' => [ 'Instagram URL', '' ],
        'vpg_social_x'         => [ 'X / Twitter URL', '' ],
    ] as $id => $row ) {
        $wp->add_setting( $id, [ 'default' => $row[1], 'sanitize_callback' => 'sanitize_text_field' ] );
        $wp->add_control( $id, [ 'label' => $row[0], 'section' => 'vpg_identity', 'type' => 'text' ] );
    }

    /* ── Palette · color tokens ────────────────── */
    $wp->add_section( 'vpg_palette', [ 'title' => __( 'Palette', 'vpg-v2' ), 'panel' => 'vpg_v2' ] );
    foreach ( [
        'vpg_color_bg'        => [ 'Background',      '#F4EFE3' ],
        'vpg_color_surface'   => [ 'Surface',         '#ECE3D1' ],
        'vpg_color_ink'       => [ 'Ink (text)',      '#0F0A04' ],
        'vpg_color_accent'    => [ 'Accent',          '#C8601A' ],
        'vpg_color_oxblood'   => [ 'Declarative red', '#7A1F0E' ],
        'vpg_color_gold'      => [ 'Highlight gold',  '#B8860B' ],
        'vpg_color_member'    => [ 'Member green',    '#2D4A3E' ],
    ] as $id => $row ) {
        $wp->add_setting( $id, [ 'default' => $row[1], 'sanitize_callback' => 'sanitize_hex_color' ] );
        $wp->add_control( new WP_Customize_Color_Control( $wp, $id, [ 'label' => $row[0], 'section' => 'vpg_palette' ] ) );
    }

    /* ── Logo ──────────────────────────────────── */
    $wp->add_section( 'vpg_logo', [ 'title' => __( 'Brand image', 'vpg-v2' ), 'panel' => 'vpg_v2' ] );
    $wp->add_setting( 'vpg_logo_url', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp->add_control( new WP_Customize_Image_Control( $wp, 'vpg_logo_url', [ 'label' => 'Logo image', 'section' => 'vpg_logo' ] ) );
} );

/* Inject palette overrides as inline CSS · only writes vars that differ from default */
add_action( 'wp_head', function () {
    $map = [
        'vpg_color_bg'        => [ '--vpg-bg',        '#F4EFE3' ],
        'vpg_color_surface'   => [ '--vpg-surface',   '#ECE3D1' ],
        'vpg_color_ink'       => [ '--vpg-ink',       '#0F0A04' ],
        'vpg_color_accent'    => [ '--vpg-accent',    '#C8601A' ],
        'vpg_color_oxblood'   => [ '--vpg-oxblood',   '#7A1F0E' ],
        'vpg_color_gold'      => [ '--vpg-gold',      '#B8860B' ],
        'vpg_color_member'    => [ '--vpg-member',    '#2D4A3E' ],
    ];
    $out = '';
    foreach ( $map as $mod => $row ) {
        $val = get_theme_mod( $mod, $row[1] );
        if ( $val && strcasecmp( $val, $row[1] ) !== 0 ) $out .= $row[0] . ':' . esc_attr( $val ) . ';';
    }
    if ( $out ) echo '<style id="vpg-palette-overrides">:root{' . $out . '}</style>' . PHP_EOL;
}, 99 );
