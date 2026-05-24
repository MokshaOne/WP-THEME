<?php
/**
 * on1.agency · skin registry
 *
 * Defines the 17 visitor-facing design skins shipped with the theme.
 * Each skin renders on1's existing content (services, projects, pricing,
 * FAQ etc. from wp_options) in its own visual language.
 *
 * Per-site customisation lives in wp_options['on1_skins'] — admin can
 * enable/disable each skin, set its label in the picker, override color
 * tokens, and reorder. See inc/skin-admin.php for the editor UI.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The complete skin registry.
 * Returns: [ slug => [ label, swatch, tokens, fonts, default_enabled, group ] ]
 *
 * group is purely organisational — 'site' for the 5 real-site skins
 * (the user's actual WordPress sites), 'fantasy' for the 12 invented ones.
 *
 * tokens is the default color palette per skin. Admin can override any
 * of these in wp_options['on1_skins'][slug]['tokens'].
 *
 * fonts lists Google Fonts the skin needs enqueued.
 */
function on1_skin_registry() {
    return [
        // ─── Real-site skins · faithful to actual on1 WordPress themes ───
        'jrp' => [
            'label'  => 'jrpoetry.com',
            'group'  => 'site',
            'swatch' => [ '#F0E7CC', '#6B1414', '#8B5A0F' ],
            'fonts'  => [ 'EB Garamond' ],
            'tokens' => [
                'bg'       => '#F0E7CC',
                'bg-2'     => '#E8DEC0',
                'bg-3'     => '#F4ECD4',
                'fg'       => '#1A0E04',
                'mute'     => 'rgba(26,14,4,0.55)',
                'accent'   => '#6B1414',
                'accent-l' => '#8B2020',
                'warm'     => '#8B5A0F',
                'rule'     => 'rgba(110,86,56,0.22)',
            ],
            'default_enabled' => true,
        ],
        'm1oat' => [
            'label'  => 'm1o.at',
            'group'  => 'site',
            'swatch' => [ '#0A0A0A', '#00FF66' ],
            'fonts'  => [ 'JetBrains Mono', 'Space Grotesk' ],
            'tokens' => [
                'bg'       => '#0A0A0A',
                'bg-2'     => '#131313',
                'bg-3'     => '#1A1A1A',
                'fg'       => '#FFFFFF',
                'mute'     => 'rgba(255,255,255,0.50)',
                'accent'   => '#00FF66',
                'accent-l' => '#5EFF99',
                'warm'     => '#00FF66',
                'rule'     => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'raveen' => [
            'label'  => 'raveenthiran.com',
            'group'  => 'site',
            'swatch' => [ '#0B0C10', '#F2A03D' ],
            'fonts'  => [ 'Inter Tight', 'JetBrains Mono' ],
            'tokens' => [
                'bg'       => '#0B0C10',
                'bg-2'     => '#13141A',
                'bg-3'     => '#181A20',
                'fg'       => '#F2EFE9',
                'mute'     => 'rgba(242,239,233,0.62)',
                'accent'   => '#F2A03D',
                'accent-l' => '#FFB870',
                'warm'     => '#F2A03D',
                'rule'     => 'rgba(242,239,233,0.14)',
            ],
            'default_enabled' => true,
        ],
        'vienna' => [
            'label'  => 'viennaphotogroup.com',
            'group'  => 'site',
            // Warm-cream paper · burnt sienna · forest-green member tag · oxblood declarative
            'swatch' => [ '#F4EFE3', '#C8601A', '#2D4A3E', '#7A1F0E' ],
            'fonts'  => [ 'Playfair Display', 'Inter', 'Instrument Serif' ],
            'tokens' => [
                'bg'       => '#F4EFE3',   // warm paper · page background
                'bg-2'     => '#ECE3D1',   // darker paper · alt section bg
                'bg-3'     => '#F9F5EA',   // lifted card · brighter than bg
                'fg'       => '#0F0A04',   // deep ink · stronger photo contrast
                'mute'     => 'rgba(15,10,4,0.55)',
                'accent'   => '#C8601A',   // burnt sienna · signature
                'accent-l' => '#E58040',   // lighter hover variant
                'warm'     => '#2D4A3E',   // forest green · member tag
                'rule'     => 'rgba(42,31,18,0.16)',
            ],
            'default_enabled' => true,
        ],
        'on1' => [
            'label'  => 'on1.agency',
            'group'  => 'site',
            'swatch' => [ '#0A0A0A', '#FF1F8C' ],
            'fonts'  => [ 'Inter', 'Instrument Serif' ],
            'tokens' => [
                'bg'       => '#0A0A0A',
                'bg-2'     => '#111111',
                'bg-3'     => '#131313',
                'fg'       => '#FFFFFF',
                'mute'     => 'rgba(255,255,255,0.55)',
                'accent'   => '#FF1F8C',
                'accent-l' => '#FF61AE',
                'warm'     => '#A35AFF',
                'rule'     => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'nail' => [
            'label'  => 'nailfluence.studio',
            'group'  => 'site',
            'swatch' => [ '#0A0908', '#E8C5A8' ],
            'fonts'  => [ 'Italiana', 'Bodoni Moda', 'Manrope', 'JetBrains Mono' ],
            'tokens' => [
                'bg'       => '#0A0908',
                'bg-2'     => '#15130F',
                'bg-3'     => '#1A1815',
                'fg'       => '#E8E5E0',
                'mute'     => 'rgba(232,229,224,0.55)',
                'accent'   => '#E8C5A8',
                'accent-l' => '#F5DCC0',
                'warm'     => '#C0BCB6',
                'rule'     => 'rgba(232,229,224,0.12)',
            ],
            'default_enabled' => true,
        ],

        // ─── Fantasy skins · invented design directions ───
        'kavithai' => [
            'label'  => 'Verse',
            'group'  => 'fantasy',
            'swatch' => [ '#F0E7CC', '#6B1414' ],
            'fonts'  => [ 'EB Garamond', 'Instrument Serif' ],
            'tokens' => [
                'bg' => '#F0E7CC', 'bg-2' => '#E8DEC0', 'bg-3' => '#F4ECD4',
                'fg' => '#1A0E04', 'mute' => 'rgba(26,14,4,0.55)',
                'accent' => '#6B1414', 'accent-l' => '#8B2020', 'warm' => '#8B5A0F',
                'rule' => 'rgba(110,86,56,0.18)',
            ],
            'default_enabled' => true,
        ],
        'm1o' => [
            'label'  => 'Phosphor',
            'group'  => 'fantasy',
            'swatch' => [ '#050505', '#00FF66' ],
            'fonts'  => [ 'JetBrains Mono' ],
            'tokens' => [
                'bg' => '#050505', 'bg-2' => '#0C0C0C', 'bg-3' => '#131313',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#00FF66', 'accent-l' => '#5EFF99', 'warm' => '#00FF66',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'raveenthiran' => [
            'label'  => 'Tableau',
            'group'  => 'fantasy',
            'swatch' => [ '#1B1A17', '#E8C56C' ],
            'fonts'  => [ 'Instrument Serif', 'Inter' ],
            'tokens' => [
                'bg' => '#1B1A17', 'bg-2' => '#232220', 'bg-3' => '#2C2A27',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#E8C56C', 'accent-l' => '#F5DCA0', 'warm' => '#C97B3B',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'vpg' => [
            'label'  => 'Coterie',
            'group'  => 'fantasy',
            'swatch' => [ '#FAF8F3', '#C44536' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#FAF8F3', 'bg-2' => '#F2EFE7', 'bg-3' => '#EAE5D9',
                'fg' => '#2A2520', 'mute' => 'rgba(42,37,32,0.55)',
                'accent' => '#C44536', 'accent-l' => '#E26854', 'warm' => '#C44536',
                'rule' => 'rgba(42,37,32,0.15)',
            ],
            'default_enabled' => true,
        ],
        'darkyn' => [
            'label'  => 'Orbit',
            'group'  => 'fantasy',
            'swatch' => [ '#0A0A0A', '#E8651A' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#0A0A0A', 'bg-2' => '#111111', 'bg-3' => '#181818',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#E8651A', 'accent-l' => '#FF8A3D', 'warm' => '#E8651A',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'gaudy' => [
            'label'  => 'Revolt',
            'group'  => 'fantasy',
            'swatch' => [ '#0B0B0B', '#E5174A' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#0B0B0B', 'bg-2' => '#131313', 'bg-3' => '#1B1B1B',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#E5174A', 'accent-l' => '#FF3D6C', 'warm' => '#E5174A',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'geroz' => [
            'label'  => 'Monolith',
            'group'  => 'fantasy',
            'swatch' => [ '#7A0E0E' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#7A0E0E', 'bg-2' => '#5C0808', 'bg-3' => '#4A0606',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.72)',
                'accent' => '#FFFFFF', 'accent-l' => '#FFE5E5', 'warm' => '#FFC9C9',
                'rule' => 'rgba(255,255,255,0.18)',
            ],
            'default_enabled' => true,
        ],
        'growla' => [
            'label'  => 'Bauhaus',
            'group'  => 'fantasy',
            'swatch' => [ '#050505', '#F2D200' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#050505', 'bg-2' => '#0E0E0E', 'bg-3' => '#161616',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#F2D200', 'accent-l' => '#FFEB4D', 'warm' => '#F2D200',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'maxreach' => [
            'label'  => 'Aurora',
            'group'  => 'fantasy',
            'swatch' => [ '#0B0710', '#E5176B', '#A35AFF' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#0B0710', 'bg-2' => '#150C1E', 'bg-3' => '#1F132B',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#E5176B', 'accent-l' => '#FF4F92', 'warm' => '#A35AFF',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'pixelon' => [
            'label'  => 'Eclipse',
            'group'  => 'fantasy',
            'swatch' => [ '#0A0707', '#FF6B35' ],
            'fonts'  => [ 'Inter' ],
            'tokens' => [
                'bg' => '#0A0707', 'bg-2' => '#120D0D', 'bg-3' => '#1A1313',
                'fg' => '#FFFFFF', 'mute' => 'rgba(255,255,255,0.55)',
                'accent' => '#FF6B35', 'accent-l' => '#FF9A6F', 'warm' => '#FF6B35',
                'rule' => 'rgba(255,255,255,0.10)',
            ],
            'default_enabled' => true,
        ],
        'revolix' => [
            'label'  => 'Salon',
            'group'  => 'fantasy',
            'swatch' => [ '#F4F1EE', '#FF1F8C' ],
            'fonts'  => [ 'Instrument Serif', 'Inter' ],
            'tokens' => [
                'bg' => '#F4F1EE', 'bg-2' => '#ECE7E1', 'bg-3' => '#E2DBD3',
                'fg' => '#0F0F12', 'mute' => 'rgba(15,15,18,0.55)',
                'accent' => '#FF1F8C', 'accent-l' => '#FF61AE', 'warm' => '#FF1F8C',
                'rule' => 'rgba(15,15,18,0.12)',
            ],
            'default_enabled' => true,
        ],
    ];
}

/**
 * Get the saved skin config from wp_options, merged with registry defaults.
 * Each skin has: enabled, label, swatch, tokens, order.
 */
function on1_get_skins() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $registry = on1_skin_registry();
    $saved    = get_option( 'on1_skins', [] );
    $merged   = [];
    $order    = 0;

    foreach ( $registry as $slug => $def ) {
        $s = isset( $saved[ $slug ] ) && is_array( $saved[ $slug ] ) ? $saved[ $slug ] : [];

        $merged[ $slug ] = [
            'slug'    => $slug,
            'group'   => $def['group'],
            'label'   => isset( $s['label'] ) && $s['label'] !== '' ? $s['label'] : $def['label'],
            'swatch'  => isset( $s['swatch'] ) && is_array( $s['swatch'] ) ? $s['swatch'] : $def['swatch'],
            'fonts'   => $def['fonts'],
            'tokens'  => isset( $s['tokens'] ) && is_array( $s['tokens'] )
                ? array_merge( $def['tokens'], $s['tokens'] )
                : $def['tokens'],
            'enabled' => isset( $s['enabled'] ) ? (bool) $s['enabled'] : (bool) $def['default_enabled'],
            'order'   => isset( $s['order'] ) ? (int) $s['order'] : $order,
        ];
        $order += 10;
    }

    uasort( $merged, function ( $a, $b ) { return $a['order'] <=> $b['order']; } );

    $cache = $merged;
    return $merged;
}

/** Return only enabled skins (for picker + render). */
function on1_get_enabled_skins() {
    return array_filter( on1_get_skins(), function ( $s ) { return $s['enabled']; } );
}

/** Get a single skin by slug (returns merged config or null). */
function on1_get_skin( $slug ) {
    $all = on1_get_skins();
    return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}
