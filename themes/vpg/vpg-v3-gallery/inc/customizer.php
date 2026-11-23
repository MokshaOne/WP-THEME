<?php
/**
 * VPG v3 — Customizer · Gallery palette + identity overrides.
 *
 * The colour controls drive BOTH token systems so changes are live across
 * the whole site: the Gallery chrome/home (`--g-*`) and every inner CPT /
 * page template (`--vpg-*`). Defaults match assets/css/{gallery,tokens}.css.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Gallery palette · single source of defaults for controls + injection */
function vpg_palette_defaults() {
    return [
        'vpg_color_bg'      => [ 'label' => 'Background',     'hex' => '#FFFFFF', 'g' => [ '--g-bg' ],           'v' => [ '--vpg-bg' ] ],
        'vpg_color_surface' => [ 'label' => 'Surface (alt)',  'hex' => '#F5F4F1', 'g' => [ '--g-bg-2' ],         'v' => [ '--vpg-surface', '--vpg-card-deep' ] ],
        'vpg_color_ink'     => [ 'label' => 'Ink (text)',     'hex' => '#0B0B0B', 'g' => [ '--g-ink' ],          'v' => [ '--vpg-ink' ] ],
        'vpg_color_accent'  => [ 'label' => 'Accent (red)',   'hex' => '#E5341F', 'g' => [ '--g-red' ],          'v' => [ '--vpg-accent' ] ],
        'vpg_color_accent_deep' => [ 'label' => 'Accent (deep)', 'hex' => '#BE2410', 'g' => [ '--g-red-deep' ], 'v' => [ '--vpg-accent-deep', '--vpg-oxblood', '--vpg-gold' ] ],
    ];
}

add_action( 'customize_register', function ( $wp ) {

    $wp->add_panel( 'vpg_v3', [ 'title' => 'VPG · ' . __( 'Theme', 'vpg-v2' ), 'priority' => 30 ] );

    /* ── Identity ────────────────────────────────── */
    $wp->add_section( 'vpg_identity', [ 'title' => __( 'Identity & social', 'vpg-v2' ), 'panel' => 'vpg_v3' ] );
    foreach ( [
        'vpg_email'            => [ 'Contact email',  get_option( 'admin_email' ), 'sanitize_email' ],
        'vpg_location'         => [ 'Location',       'Wien · UTC+1',              'sanitize_text_field' ],
        'vpg_booking'          => [ 'Booking status', 'Q3 · slots open',           'sanitize_text_field' ],
        'vpg_since'            => [ 'Founded year',   '2019',                      'sanitize_text_field' ],
        'vpg_social_instagram' => [ 'Instagram URL',  '',                          'esc_url_raw' ],
        'vpg_social_x'         => [ 'X / Twitter URL', '',                         'esc_url_raw' ],
    ] as $id => $row ) {
        $wp->add_setting( $id, [ 'default' => $row[1], 'sanitize_callback' => $row[2] ] );
        $wp->add_control( $id, [ 'label' => $row[0], 'section' => 'vpg_identity', 'type' => 'text' ] );
    }

    /* ── Palette · Gallery color tokens ───────────── */
    $wp->add_section( 'vpg_palette', [ 'title' => __( 'Palette', 'vpg-v2' ), 'panel' => 'vpg_v3', 'description' => __( 'One accent does the pointing. Defaults are the Gallery system.', 'vpg-v2' ) ] );
    foreach ( vpg_palette_defaults() as $id => $row ) {
        $wp->add_setting( $id, [ 'default' => $row['hex'], 'sanitize_callback' => 'sanitize_hex_color' ] );
        $wp->add_control( new WP_Customize_Color_Control( $wp, $id, [ 'label' => $row['label'], 'section' => 'vpg_palette' ] ) );
    }

    /* ── Logo (or use the core Custom Logo) ───────── */
    $wp->add_section( 'vpg_logo', [ 'title' => __( 'Brand image', 'vpg-v2' ), 'panel' => 'vpg_v3' ] );
    $wp->add_setting( 'vpg_logo_url', [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp->add_control( new WP_Customize_Image_Control( $wp, 'vpg_logo_url', [ 'label' => 'Logo image', 'section' => 'vpg_logo' ] ) );
} );

/* Inject palette overrides as inline CSS · only writes vars that differ from
   default, and writes to BOTH --g-* (chrome/home) and --vpg-* (inner pages). */
add_action( 'wp_head', function () {
    $out = '';
    foreach ( vpg_palette_defaults() as $mod => $row ) {
        $val = get_theme_mod( $mod, $row['hex'] );
        if ( ! $val || strcasecmp( $val, $row['hex'] ) === 0 ) continue;
        foreach ( array_merge( $row['g'], $row['v'] ) as $token ) {
            $out .= $token . ':' . esc_attr( $val ) . ';';
        }
    }
    if ( $out ) echo '<style id="vpg-palette-overrides">:root{' . $out . '}</style>' . PHP_EOL;
}, 99 );
