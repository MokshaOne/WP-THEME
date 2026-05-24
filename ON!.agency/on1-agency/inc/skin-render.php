<?php
/**
 * on1.agency · skin render helpers
 *
 * Outputs per-skin inline CSS variable overrides, includes skin
 * template parts, and exposes a small JSON config to JS for the picker.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Inject inline CSS that defines per-skin token overrides.
 * Each enabled skin gets a body[data-preset="slug"] { --x: ... } block.
 *
 * The base layout CSS for all skins lives in assets/css/skins.css —
 * this only writes the colour-token overrides that admin can change.
 */
function on1_render_skin_tokens_css() {
    $skins = on1_get_enabled_skins();
    if ( ! $skins ) return '';

    $css = '';
    foreach ( $skins as $skin ) {
        $css .= sprintf( "body[data-preset=\"%s\"]{", esc_attr( $skin['slug'] ) );
        foreach ( $skin['tokens'] as $name => $value ) {
            // CSS vars: --bg, --bg-2, --accent, etc.
            $css .= sprintf( "--%s:%s;", $name, $value );
        }
        $css .= "}";
    }
    return $css;
}

/**
 * Build the PRESETS array consumed by skin-router.js — slug, label, swatch.
 * Returns a JSON-safe array.
 */
function on1_skins_for_js() {
    $list = [];
    foreach ( on1_get_enabled_skins() as $skin ) {
        $list[] = [
            'id'     => $skin['slug'],
            'label'  => $skin['label'],
            'swatch' => $skin['swatch'],
        ];
    }
    return $list;
}

/**
 * Render an individual skin's full markup (all 6 subpages).
 * Loads template-parts/skins/{slug}.php.
 *
 * Each skin template receives the merged $skin array + $on1_content
 * (the studio's editable content from wp_options) as locals.
 */
function on1_render_skin( $slug ) {
    $skin = on1_get_skin( $slug );
    if ( ! $skin || ! $skin['enabled'] ) return;

    $template = get_theme_file_path( "template-parts/skins/{$slug}.php" );
    if ( ! file_exists( $template ) ) return;

    $on1_content = on1_get_skin_content();

    echo '<div class="on1-skin on1-skin--' . esc_attr( $slug ) . '" data-skin="' . esc_attr( $slug ) . '">';
    include $template;
    echo '</div>';
}

/**
 * Render all enabled skins (used by front-page.php).
 */
function on1_render_all_skins() {
    foreach ( on1_get_enabled_skins() as $skin ) {
        on1_render_skin( $skin['slug'] );
    }
}

/**
 * Shared content payload that every skin template can read from.
 * Pulls from the existing on1 wp_options the studio already edits in admin
 * (services, work, pricing, FAQ, identity).
 */
function on1_get_skin_content() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $cache = [
        'identity' => function_exists( 'on1_get_identity' ) ? on1_get_identity() : [
            'brand'    => 'on1.agency',
            'tagline'  => 'A one-person digital atelier in Wien',
            'email'    => 'hello@on1.agency',
            'location' => 'Wien · UTC+1',
            'booking'  => 'Q3 · slots open',
            'since'    => '2018',
            'sites'    => '14',
            'reply'    => '72',
        ],
        'lede'      => "A one-person digital atelier in Wien — building editorial WordPress sites and design systems for studios who want to look like themselves, not like a template.",
        'principles' => [
            [ 'i.',   'Quiet design is the most generous kind of work' ],
            [ 'ii.',  'One person responsible — brief to ship' ],
            [ 'iii.', 'Editorial over template, always' ],
            [ 'iv.',  'Code that lasts a decade, not a quarter' ],
        ],
        'services' => function_exists( 'on1_get_services' ) ? on1_get_services() : [
            [ 'name' => 'Editorial WordPress themes',  'lede' => 'Hand-built themes, no page builders.' ],
            [ 'name' => 'Design systems',              'lede' => 'Token libraries that scale across years.' ],
            [ 'name' => 'Brand & visual identity',     'lede' => 'Brand systems for studios.' ],
            [ 'name' => 'Front-end development',       'lede' => 'Semantic, accessible, fast.' ],
        ],
        'work' => function_exists( 'on1_get_work' ) ? on1_get_work() : [
            [ 'year' => '2024', 'title' => 'Kavithai',           'lede' => 'Tamil poetry archive · South Asian editorial' ],
            [ 'year' => '2024', 'title' => 'M1O Hub',            'lede' => 'Electronic musician link hub · brutalist' ],
            [ 'year' => '2023', 'title' => 'Vienna Photo Group', 'lede' => 'Community archive · magazine' ],
            [ 'year' => '2023', 'title' => 'Raveenthiran',       'lede' => 'Photography portfolio · stoic minimal' ],
            [ 'year' => '2022', 'title' => 'Atelier Holzwerk',   'lede' => 'Furniture studio · catalogue' ],
            [ 'year' => '2022', 'title' => 'Studio Mahesh',      'lede' => 'Independent magazine · long-form' ],
        ],
        'tiers' => function_exists( 'on1_get_pricing' ) ? on1_get_pricing() : [
            [ 'name' => 'Essay',   'price' => '€2,400',  'sub' => 'One-page editorial · 1 week',
              'features' => [ 'Strategy + 1 direction', 'One-page editorial', 'Hand-coded HTML/CSS', 'Hosting setup' ], 'featured' => false ],
            [ 'name' => 'Studio',  'price' => '€6,800',  'sub' => 'Custom WordPress · 4 weeks',
              'features' => [ 'Strategy + 2 directions', 'Custom WP theme', 'Up to 6 pages + CPTs', 'Admin training', '4 weeks aftercare' ], 'featured' => true ],
            [ 'name' => 'Atelier', 'price' => '€14,400', 'sub' => 'Design system · 8 weeks',
              'features' => [ 'Strategy + 3 directions', 'Design system + tokens', 'Full site + admin', '3 months aftercare' ], 'featured' => false ],
        ],
        'faq' => function_exists( 'on1_get_faq' ) ? on1_get_faq() : [
            [ 'q' => 'How long does a project take?', 'a' => '4–8 weeks kickoff to launch. Essay ships in 1 week.' ],
            [ 'q' => 'Do you work with agencies?',    'a' => 'Yes — happy to white-label or co-design.' ],
            [ 'q' => 'What is your stack?',           'a' => 'WordPress with custom themes, hand-coded HTML/CSS/JS.' ],
            [ 'q' => 'Who owns the code?',            'a' => 'You do. From day one. No lock-in.' ],
            [ 'q' => 'Do you offer aftercare?',       'a' => 'Yes — every tier includes free aftercare.' ],
        ],
    ];
    return $cache;
}

/**
 * Helper used inside skin templates to render the standard subpage wrapper.
 * Renders six <div data-skin-page="x"> containers; the skin template chooses
 * what to put in each via include calls in template-parts/skins/{slug}.php.
 */
function on1_skin_pages( $slug, callable $renderer ) {
    $pages = [ 'home', 'about', 'work', 'pricing', 'faq', 'contact' ];
    foreach ( $pages as $page ) {
        $active = ( $page === 'home' ) ? ' is-active' : '';
        echo '<div class="' . esc_attr( $slug ) . '-page' . $active . '" data-skin-page="' . esc_attr( $page ) . '">';
        call_user_func( $renderer, $page );
        echo '</div>';
    }
}
