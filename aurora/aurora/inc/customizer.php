<?php
/**
 * Aurora — Customizer.
 * One panel (Aurora) · sections for Identity, Hero, Principles, Services, Work, Pricing, FAQ, Contact, Palette.
 * Every helper in inc/helpers.php pulls its value from one of these settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'customize_register', function ( $wp ) {

    $wp->add_panel( 'AURORA_panel', [
        'title'    => 'Aurora · ' . __( 'Site content', 'AURORA' ),
        'priority' => 30,
    ] );

    $sections = [
        'identity'   => __( 'Identity',  'AURORA' ),
        'hero'       => __( 'Hero',      'AURORA' ),
        'principles' => __( 'Principles','AURORA' ),
        'services'   => __( 'Services',  'AURORA' ),
        'work'       => __( 'Work',      'AURORA' ),
        'tiers'      => __( 'Pricing',   'AURORA' ),
        'faq'        => __( 'FAQ',       'AURORA' ),
        'contact'    => __( 'Contact',   'AURORA' ),
        'palette'    => __( 'Palette',   'AURORA' ),
    ];
    foreach ( $sections as $id => $title ) {
        $wp->add_section( 'AURORA_' . $id, [ 'title' => $title, 'panel' => 'AURORA_panel' ] );
    }

    $add_text = function ( $id, $section, $label_, $default = '', $multi = false ) use ( $wp ) {
        $wp->add_setting( 'AURORA_' . $id, [
            'default'           => $default,
            'sanitize_callback' => $multi ? 'wp_kses_post' : 'sanitize_text_field',
            'transport'         => 'refresh',
        ] );
        $wp->add_control( 'AURORA_' . $id, [
            'label'   => $label_,
            'section' => 'AURORA_' . $section,
            'type'    => $multi ? 'textarea' : 'text',
        ] );
    };

    // Identity
    $add_text( 'email',    'identity', __( 'Email', 'AURORA' ),    get_option( 'admin_email' ) );
    $add_text( 'location', 'identity', __( 'Location', 'AURORA' ), 'Wien · UTC+1' );
    $add_text( 'booking',  'identity', __( 'Booking status', 'AURORA' ), 'Q3 · slots open' );
    $add_text( 'since',    'identity', __( 'Founded year', 'AURORA' ),   '2018' );
    $add_text( 'sites',    'identity', __( 'Sites shipped count', 'AURORA' ), '14' );
    $add_text( 'reply',    'identity', __( 'Reply window (hours)', 'AURORA' ), '72' );

    // Hero
    $add_text( 'hero_eyebrow', 'hero', __( 'Eyebrow', 'AURORA' ),       'An atelier · Wien · since MMXVIII' );
    $add_text( 'hero_line1',   'hero', __( 'Headline · line 1', 'AURORA' ), 'where craft' );
    $add_text( 'hero_line2',   'hero', __( 'Headline · line 2', 'AURORA' ), 'meets code' );
    $add_text( 'hero_lede',    'hero', __( 'Lede paragraph', 'AURORA' ), 'A one-person digital atelier crafting editorial WordPress sites and design systems.', true );
    $add_text( 'hero_cta',     'hero', __( 'Primary CTA label', 'AURORA' ), 'Start a project' );
    $add_text( 'hero_cta_url', 'hero', __( 'Primary CTA URL', 'AURORA' ),   '#brief' );

    // Repeater-style textareas (one item per line, |-separated columns).
    $add_text( 'principles', 'principles', __( 'Principles · one per line · oman | text', 'AURORA' ),
        "i. | Quiet design is the most generous kind of work
iv. | Code that lasts a decade, not a quarter", true );

    $add_text( 'services', 'services', __( 'Services · one per line · 
ame | one-line description', 'AURORA' ),
        "Editorial WordPress themes | Hand-built themes, no page builders.
Design systems | Token libraries that scale across years.", true );

    $add_text( 'work', 'work', __( 'Work · one per line · year | title | description. (CPT entries override this.)', 'AURORA' ),
        "2024 | Kavithai | Tamil poetry archive", true );

    $add_text( 'tiers', 'tiers', __( 'Pricing tiers · one per line · 
ame | price | sub | features (;-separated) | featured (0/1)', 'AURORA' ),
        "Studio | €6,800 | Custom WP · 4 weeks | Strategy + 2 directions; Custom theme; 4 weeks aftercare | 1", true );

    $add_text( 'faq', 'faq', __( 'FAQ · one per line · question | answer', 'AURORA' ),
        "How long does a project take? | 4–8 weeks kickoff to launch.", true );

    // Palette — pure color overrides
    foreach ( [
        'color_bg'     => [ __( 'Background', 'AURORA' ),  '#0B0710' ],
        'color_fg'     => [ __( 'Text', 'AURORA' ),        '#FFFFFF' ],
        'color_accent' => [ __( 'Accent', 'AURORA' ),      '#E5176B' ],
    ] as $id => $row ) {
        $wp->add_setting( 'AURORA_' . $id, [ 'default' => $row[1], 'sanitize_callback' => 'sanitize_hex_color' ] );
        $wp->add_control( new WP_Customize_Color_Control( $wp, 'AURORA_' . $id, [
            'label'   => $row[0],
            'section' => 'AURORA_palette',
        ] ) );
    }
} );

// Selective refresh helper (optional, no-op fallback)
add_action( 'customize_preview_init', function () {
    // hook here later if you want partial refreshes for hero fields
} );
