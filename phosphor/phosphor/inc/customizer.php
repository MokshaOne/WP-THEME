<?php
/**
 * Phosphor — Customizer.
 * One panel (Phosphor) · sections for Identity, Hero, Principles, Services, Work, Pricing, FAQ, Contact, Palette.
 * Every helper in inc/helpers.php pulls its value from one of these settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'customize_register', function ( $wp ) {

    $wp->add_panel( 'PHOSPHOR_panel', [
        'title'    => 'Phosphor · ' . __( 'Site content', 'PHOSPHOR' ),
        'priority' => 30,
    ] );

    $sections = [
        'identity'   => __( 'Identity',  'PHOSPHOR' ),
        'hero'       => __( 'Hero',      'PHOSPHOR' ),
        'principles' => __( 'Principles','PHOSPHOR' ),
        'services'   => __( 'Services',  'PHOSPHOR' ),
        'work'       => __( 'Work',      'PHOSPHOR' ),
        'tiers'      => __( 'Pricing',   'PHOSPHOR' ),
        'faq'        => __( 'FAQ',       'PHOSPHOR' ),
        'contact'    => __( 'Contact',   'PHOSPHOR' ),
        'palette'    => __( 'Palette',   'PHOSPHOR' ),
    ];
    foreach ( $sections as $id => $title ) {
        $wp->add_section( 'PHOSPHOR_' . $id, [ 'title' => $title, 'panel' => 'PHOSPHOR_panel' ] );
    }

    $add_text = function ( $id, $section, $label_, $default = '', $multi = false ) use ( $wp ) {
        $wp->add_setting( 'PHOSPHOR_' . $id, [
            'default'           => $default,
            'sanitize_callback' => $multi ? 'wp_kses_post' : 'sanitize_text_field',
            'transport'         => 'refresh',
        ] );
        $wp->add_control( 'PHOSPHOR_' . $id, [
            'label'   => $label_,
            'section' => 'PHOSPHOR_' . $section,
            'type'    => $multi ? 'textarea' : 'text',
        ] );
    };

    // Identity
    $add_text( 'email',    'identity', __( 'Email', 'PHOSPHOR' ),    get_option( 'admin_email' ) );
    $add_text( 'location', 'identity', __( 'Location', 'PHOSPHOR' ), 'Wien · UTC+1' );
    $add_text( 'booking',  'identity', __( 'Booking status', 'PHOSPHOR' ), 'Q3 · slots open' );
    $add_text( 'since',    'identity', __( 'Founded year', 'PHOSPHOR' ),   '2018' );
    $add_text( 'sites',    'identity', __( 'Sites shipped count', 'PHOSPHOR' ), '14' );
    $add_text( 'reply',    'identity', __( 'Reply window (hours)', 'PHOSPHOR' ), '72' );

    // Hero
    $add_text( 'hero_eyebrow', 'hero', __( 'Eyebrow', 'PHOSPHOR' ),       'An atelier · Wien · since MMXVIII' );
    $add_text( 'hero_line1',   'hero', __( 'Headline · line 1', 'PHOSPHOR' ), 'where craft' );
    $add_text( 'hero_line2',   'hero', __( 'Headline · line 2', 'PHOSPHOR' ), 'meets code' );
    $add_text( 'hero_lede',    'hero', __( 'Lede paragraph', 'PHOSPHOR' ), 'A one-person digital atelier crafting editorial WordPress sites and design systems.', true );
    $add_text( 'hero_cta',     'hero', __( 'Primary CTA label', 'PHOSPHOR' ), 'Start a project' );
    $add_text( 'hero_cta_url', 'hero', __( 'Primary CTA URL', 'PHOSPHOR' ),   '#brief' );

    // Repeater-style textareas (one item per line, |-separated columns).
    $add_text( 'principles', 'principles', __( 'Principles · one per line · oman | text', 'PHOSPHOR' ),
        "i. | Quiet design is the most generous kind of work
iv. | Code that lasts a decade, not a quarter", true );

    $add_text( 'services', 'services', __( 'Services · one per line · 
ame | one-line description', 'PHOSPHOR' ),
        "Editorial WordPress themes | Hand-built themes, no page builders.
Design systems | Token libraries that scale across years.", true );

    $add_text( 'work', 'work', __( 'Work · one per line · year | title | description. (CPT entries override this.)', 'PHOSPHOR' ),
        "2024 | Kavithai | Tamil poetry archive", true );

    $add_text( 'tiers', 'tiers', __( 'Pricing tiers · one per line · 
ame | price | sub | features (;-separated) | featured (0/1)', 'PHOSPHOR' ),
        "Studio | €6,800 | Custom WP · 4 weeks | Strategy + 2 directions; Custom theme; 4 weeks aftercare | 1", true );

    $add_text( 'faq', 'faq', __( 'FAQ · one per line · question | answer', 'PHOSPHOR' ),
        "How long does a project take? | 4–8 weeks kickoff to launch.", true );

    // Palette — pure color overrides
    foreach ( [
        'color_bg'     => [ __( 'Background', 'PHOSPHOR' ),  '#050505' ],
        'color_fg'     => [ __( 'Text', 'PHOSPHOR' ),        '#FFFFFF' ],
        'color_accent' => [ __( 'Accent', 'PHOSPHOR' ),      '#00FF66' ],
    ] as $id => $row ) {
        $wp->add_setting( 'PHOSPHOR_' . $id, [ 'default' => $row[1], 'sanitize_callback' => 'sanitize_hex_color' ] );
        $wp->add_control( new WP_Customize_Color_Control( $wp, 'PHOSPHOR_' . $id, [
            'label'   => $row[0],
            'section' => 'PHOSPHOR_palette',
        ] ) );
    }
} );

// Selective refresh helper (optional, no-op fallback)
add_action( 'customize_preview_init', function () {
    // hook here later if you want partial refreshes for hero fields
} );
