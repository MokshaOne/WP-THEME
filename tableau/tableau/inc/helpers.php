<?php
/**
 * Tableau — helpers.php
 * Tiny content layer · every helper reads from the Customizer with sensible defaults.
 * Edit defaults below to ship a different sample site; everything is also
 * overridable per-install in WP admin → Appearance → Customize.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function TABLEAU_mod( $key, $default = '' ) {
    $v = get_theme_mod( 'TABLEAU_' . $key, $default );
    return $v !== '' ? $v : $default;
}

function TABLEAU_em( $html ) {
    return wp_kses( $html, [ 'em' => [], 'strong' => [], 'br' => [], 'span' => [ 'class' => [] ], 'small' => [], 'sup' => [] ] );
}

function TABLEAU_get_identity() {
    return [
        'brand'    => get_bloginfo( 'name' ),
        'tagline'  => get_bloginfo( 'description' ),
        'email'    => TABLEAU_mod( 'email',    get_option( 'admin_email' ) ),
        'location' => TABLEAU_mod( 'location', 'Wien · UTC+1' ),
        'booking'  => TABLEAU_mod( 'booking',  'Q3 · slots open' ),
        'since'    => TABLEAU_mod( 'since',    '2018' ),
        'sites'    => TABLEAU_mod( 'sites',    '14' ),
        'reply'    => TABLEAU_mod( 'reply',    '72' ),
    ];
}

function TABLEAU_get_hero() {
    return [
        'eyebrow' => TABLEAU_mod( 'hero_eyebrow', 'An atelier · Wien · since MMXVIII' ),
        'line1'   => TABLEAU_mod( 'hero_line1',   'where craft' ),
        'line2'   => TABLEAU_mod( 'hero_line2',   'meets code' ),
        'lede'    => TABLEAU_mod( 'hero_lede',    'A one-person digital atelier crafting editorial WordPress sites and design systems for studios who want to look like themselves, not like a template.' ),
        'cta'     => TABLEAU_mod( 'hero_cta',     'Start a project' ),
        'cta_url' => TABLEAU_mod( 'hero_cta_url', '#brief' ),
    ];
}

function TABLEAU_get_principles() {
    $raw = TABLEAU_mod( 'principles', "i. | Quiet design is the most generous kind of work\nii. | One person responsible — brief to ship\niii. | Editorial over template, always\niv. | Code that lasts a decade, not a quarter" );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    $out = [];
    foreach ( $lines as $line ) {
        if ( trim( $line ) === '' ) continue;
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $out[] = [ $parts[0] ?? '·', $parts[1] ?? $parts[0] ];
    }
    return $out;
}

function TABLEAU_get_services() {
    $raw = TABLEAU_mod( 'services', "Editorial WordPress themes | Hand-built themes, no page builders.\nDesign systems | Token libraries that scale across years.\nBrand & visual identity | Brand systems for studios.\nFront-end development | Semantic, accessible, fast." );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    $out = [];
    foreach ( $lines as $line ) {
        if ( trim( $line ) === '' ) continue;
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $out[] = [ 'name' => $parts[0], 'lede' => $parts[1] ?? '' ];
    }
    return $out;
}

function TABLEAU_get_work() {
    // Prefer custom-post-type entries if any are published; otherwise fall back to Customizer text.
    $query = new WP_Query( [ 'post_type' => 'TABLEAU_project', 'posts_per_page' => 12, 'orderby' => 'menu_order date', 'order' => 'DESC' ] );
    if ( $query->have_posts() ) {
        $out = [];
        while ( $query->have_posts() ) {
            $query->the_post();
            $out[] = [
                'year'  => get_post_meta( get_the_ID(), 'TABLEAU_year',  true ) ?: get_the_date( 'Y' ),
                'title' => get_the_title(),
                'lede'  => get_the_excerpt() ?: get_post_meta( get_the_ID(), 'TABLEAU_lede', true ),
                'url'   => get_permalink(),
            ];
        }
        wp_reset_postdata();
        return $out;
    }
    $raw = TABLEAU_mod( 'work', "2024 | Kavithai | Tamil poetry archive\n2024 | M1O Hub | Electronic musician link hub\n2023 | Vienna Photo Group | Community archive\n2023 | Raveenthiran | Photography portfolio\n2022 | Atelier Holzwerk | Furniture studio\n2022 | Studio Mahesh | Independent magazine" );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    $out = [];
    foreach ( $lines as $line ) {
        if ( trim( $line ) === '' ) continue;
        $parts = array_map( 'trim', explode( '|', $line, 3 ) );
        $out[] = [ 'year' => $parts[0] ?? '', 'title' => $parts[1] ?? '', 'lede' => $parts[2] ?? '', 'url' => '#' ];
    }
    return $out;
}

function TABLEAU_get_tiers() {
    $raw = TABLEAU_mod( 'tiers', "Essay   | €2,400  | One-page editorial · 1 week   | Strategy + 1 direction; One-page editorial; Hand-coded HTML/CSS; Hosting setup                  | 0\nStudio  | €6,800  | Custom WordPress · 4 weeks    | Strategy + 2 directions; Custom WP theme; Up to 6 pages + CPTs; Admin training; 4 weeks aftercare | 1\nAtelier | €14,400 | Design system · 8 weeks       | Strategy + 3 directions; Design system + tokens; Full site + admin; 3 months aftercare           | 0" );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    $out = [];
    foreach ( $lines as $line ) {
        if ( trim( $line ) === '' ) continue;
        $parts = array_map( 'trim', explode( '|', $line, 5 ) );
        $out[] = [
            'name'     => $parts[0] ?? '',
            'price'    => $parts[1] ?? '',
            'sub'      => $parts[2] ?? '',
            'features' => isset( $parts[3] ) ? array_map( 'trim', explode( ';', $parts[3] ) ) : [],
            'featured' => ! empty( $parts[4] ) && trim( $parts[4] ) === '1',
        ];
    }
    return $out;
}

function TABLEAU_get_faq() {
    $raw = TABLEAU_mod( 'faq', "How long does a project take? | 4–8 weeks kickoff to launch. Essay ships in 1 week.\nDo you work with agencies?    | Yes — happy to white-label or co-design.\nWhat is your stack?           | WordPress with custom themes, hand-coded HTML/CSS/JS.\nWho owns the code?            | You do. From day one. No lock-in.\nDo you offer aftercare?       | Yes — every tier includes free aftercare." );
    $lines = preg_split( '/\r\n|\r|\n/', $raw );
    $out = [];
    foreach ( $lines as $line ) {
        if ( trim( $line ) === '' ) continue;
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $out[] = [ 'q' => $parts[0] ?? '', 'a' => $parts[1] ?? '' ];
    }
    return $out;
}
