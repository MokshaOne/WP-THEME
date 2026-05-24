<?php
/**
 * on1.agency · v4 · admin-panel.php
 *
 * Single-screen control panel. No ACF dependency.
 * Edits every homepage section + identity + footer.
 *
 * Top-level menu "On1 Hub" with panels:
 *   Identity / Hero / Stats / Services / Pricing / Quotes / FAQ / Brief / Footer / Display
 *
 * Case studies are managed via the Projects CPT (separate edit screens).
 */
if ( ! defined( 'ABSPATH' ) ) exit;


// ─── 1. MENU ──────────────────────────────────────────────────
add_action( 'admin_menu', function() {
    add_menu_page(
        'On1 Hub',
        'On1 Hub',
        'manage_options',
        'on1-hub',
        'on1_render_admin_page',
        'dashicons-admin-customizer',
        3
    );
} );


// ─── 2. POST HANDLER ──────────────────────────────────────────
add_action( 'admin_init', function() {
    if ( ! isset( $_POST['on1_save'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( ! isset( $_POST['on1_nonce'] ) || ! wp_verify_nonce( $_POST['on1_nonce'], 'on1_save' ) ) return;

    $allowed_em = [ 'em' => [], 'br' => [], 'strong' => [] ];

    // Identity
    update_option( 'on1_identity', [
        'name'     => sanitize_text_field( wp_unslash( $_POST['identity_name']     ?? '' ) ),
        'tagline'  => sanitize_text_field( wp_unslash( $_POST['identity_tagline']  ?? '' ) ),
        'location' => sanitize_text_field( wp_unslash( $_POST['identity_location'] ?? '' ) ),
        'status'   => sanitize_text_field( wp_unslash( $_POST['identity_status']   ?? '' ) ),
        'email'    => sanitize_email( wp_unslash( $_POST['identity_email']         ?? '' ) ),
    ] );

    // Hero
    update_option( 'on1_hero', [
        'manifesto_lead'  => wp_kses( wp_unslash( $_POST['hero_manifesto_lead']  ?? '' ), $allowed_em ),
        'manifesto_body'  => wp_kses_post( wp_unslash( $_POST['hero_manifesto_body'] ?? '' ) ),
        'signoff_name'    => sanitize_text_field( wp_unslash( $_POST['hero_signoff_name'] ?? '' ) ),
        'signoff_role'    => wp_kses( wp_unslash( $_POST['hero_signoff_role']    ?? '' ), $allowed_em ),
        'headline_line_1' => wp_kses( wp_unslash( $_POST['hero_headline_line_1'] ?? '' ), $allowed_em ),
        'headline_line_2' => wp_kses( wp_unslash( $_POST['hero_headline_line_2'] ?? '' ), $allowed_em ),
        'tag_line'        => wp_kses( wp_unslash( $_POST['hero_tag_line']        ?? '' ), $allowed_em ),
        'cta_label'       => sanitize_text_field( wp_unslash( $_POST['hero_cta_label'] ?? '' ) ),
        'cta_url'         => esc_url_raw( wp_unslash( $_POST['hero_cta_url']      ?? '' ) ),
    ] );

    update_option( 'on1_stats',    on1_collect_repeater( 'stat',    [ 'num', 'label' ], $allowed_em ) );
    update_option( 'on1_services', on1_collect_repeater( 'service', [ 'name', 'desc', 'url' ], $allowed_em ) );

    // Pricing
    $pricing = [];
    if ( isset( $_POST['tier_name'] ) && is_array( $_POST['tier_name'] ) ) {
        $count = count( $_POST['tier_name'] );
        // tier_featured[] only carries values for CHECKED checkboxes, indexed by position with no gaps,
        // so we cannot zip by index. Use tier_featured_id[] hidden field per row as the position key.
        $featured_positions = array_map( 'intval', $_POST['tier_featured_pos'] ?? [] );
        for ( $i = 0; $i < $count; $i++ ) {
            $name  = wp_kses( wp_unslash( $_POST['tier_name'][ $i ] ?? '' ),  $allowed_em );
            $price = wp_kses( wp_unslash( $_POST['tier_price'][ $i ] ?? '' ), $allowed_em );
            if ( $name === '' && $price === '' ) continue;
            $features_str = wp_unslash( $_POST['tier_features'][ $i ] ?? '' );
            $features = array_filter( array_map( 'trim', explode( "\n", $features_str ) ) );
            $features = array_map( function( $f ) use ( $allowed_em ) { return wp_kses( $f, $allowed_em ); }, $features );
            $pricing[] = [
                'name'     => $name,
                'price'    => $price,
                'period'   => sanitize_text_field( wp_unslash( $_POST['tier_period'][ $i ] ?? '' ) ),
                'features' => array_values( $features ),
                'featured' => in_array( $i, $featured_positions, true ) ? '1' : '0',
                'cta'      => sanitize_text_field( wp_unslash( $_POST['tier_cta'][ $i ] ?? 'Choose' ) ),
                'url'      => esc_url_raw( wp_unslash( $_POST['tier_url'][ $i ] ?? '#brief' ) ),
            ];
        }
    }
    update_option( 'on1_pricing', $pricing );

    update_option( 'on1_quotes', on1_collect_repeater( 'quote', [ 'text', 'author', 'role', 'initials' ], [] ) );
    update_option( 'on1_faq',    on1_collect_repeater( 'faq',   [ 'q', 'a' ], [] ) );
    update_option( 'on1_sites',  on1_collect_repeater( 'site',  [ 'name', 'url', 'admin', 'note' ], [] ) );

    // Brief
    update_option( 'on1_brief', [
        'eyebrow'         => sanitize_text_field( wp_unslash( $_POST['brief_eyebrow'] ?? '' ) ),
        'lede_line_1'     => wp_kses( wp_unslash( $_POST['brief_lede_line_1'] ?? '' ), $allowed_em ),
        'lede_line_2'     => wp_kses( wp_unslash( $_POST['brief_lede_line_2'] ?? '' ), $allowed_em ),
        'sub'             => sanitize_textarea_field( wp_unslash( $_POST['brief_sub'] ?? '' ) ),
        'primary_label'   => sanitize_text_field( wp_unslash( $_POST['brief_primary_label'] ?? '' ) ),
        'primary_url'     => esc_url_raw( wp_unslash( $_POST['brief_primary_url'] ?? '' ) ),
        'secondary_label' => sanitize_text_field( wp_unslash( $_POST['brief_secondary_label'] ?? '' ) ),
        'secondary_url'   => esc_url_raw( wp_unslash( $_POST['brief_secondary_url'] ?? '' ) ),
    ] );

    // Footer
    update_option( 'on1_footer', [
        'brand_html' => wp_kses( wp_unslash( $_POST['footer_brand_html'] ?? '' ), [ 'em' => [], 'sup' => [], 'br' => [], 'span' => [ 'class' => [] ] ] ),
        'about_html' => wp_kses_post( wp_unslash( $_POST['footer_about_html'] ?? '' ) ),
        'colophon'   => wp_kses( wp_unslash( $_POST['footer_colophon'] ?? '' ), $allowed_em ),
        'copyright'  => sanitize_text_field( wp_unslash( $_POST['footer_copyright'] ?? '' ) ),
        'build_note' => wp_kses( wp_unslash( $_POST['footer_build_note'] ?? '' ), $allowed_em ),
    ] );

    // Accent color
    $accent = wp_unslash( $_POST['accent_color'] ?? '' );
    if ( $accent === '' || preg_match( '/^#[0-9a-fA-F]{6}$/', $accent ) ) {
        update_option( 'on1_accent_color', $accent );
    }

    // Display toggles
    $toggle_keys = [ 'stats', 'services', 'work', 'pricing', 'quotes', 'faq', 'picker' ];
    $toggles = [];
    foreach ( $toggle_keys as $k ) $toggles[ $k ] = isset( $_POST[ 'show_' . $k ] ) ? '1' : '0';
    update_option( 'on1_show', $toggles );

    wp_safe_redirect( add_query_arg( 'on1_saved', '1', remove_query_arg( 'on1_saved' ) ) );
    exit;
} );

/**
 * Generic repeater collector. Drops rows where all fields are empty.
 */
function on1_collect_repeater( $prefix, $fields, $allowed_em ) {
    $out = [];
    $first_key = $prefix . '_' . $fields[0];
    if ( ! isset( $_POST[ $first_key ] ) || ! is_array( $_POST[ $first_key ] ) ) return $out;
    $count = count( $_POST[ $first_key ] );
    for ( $i = 0; $i < $count; $i++ ) {
        $row = [];
        $has_value = false;
        foreach ( $fields as $f ) {
            $val = wp_unslash( $_POST[ $prefix . '_' . $f ][ $i ] ?? '' );
            if ( $f === 'url' ) {
                $val = esc_url_raw( $val );
            } elseif ( in_array( $f, [ 'desc', 'a', 'text' ], true ) ) {
                $val = sanitize_textarea_field( $val );
            } elseif ( ! empty( $allowed_em ) ) {
                $val = wp_kses( $val, $allowed_em );
            } else {
                $val = sanitize_text_field( $val );
            }
            $row[ $f ] = $val;
            if ( trim( $val ) !== '' ) $has_value = true;
        }
        if ( $has_value ) $out[] = $row;
    }
    return $out;
}


// ─── ADMIN BAR "My Sites" DROPDOWN ───────────────────────────
// Adds a quick-launch dropdown to the top admin bar listing every
// site from on1_get_sites(). Click = open that site's admin in a
// new tab. Falls back to the frontend URL if no admin URL is set.
add_action( 'admin_bar_menu', function( $bar ) {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $sites = on1_get_sites();
    if ( empty( $sites ) ) return;

    $bar->add_node( [
        'id'    => 'on1-my-sites',
        'title' => '<span class="ab-icon dashicons dashicons-admin-multisite" style="font-size:18px;line-height:1.6;"></span><span class="ab-label">My Sites · ' . count( $sites ) . '</span>',
        'href'  => admin_url( 'admin.php?page=on1-hub' ),
        'meta'  => [ 'title' => 'Sites I manage · edit list in On1 Hub' ],
    ] );

    foreach ( $sites as $i => $site ) {
        if ( empty( $site['name'] ) ) continue;
        $url = $site['admin'] ?: $site['url'];
        if ( empty( $url ) ) continue;
        $note = ! empty( $site['note'] ) ? ' — ' . $site['note'] : '';

        $bar->add_node( [
            'parent' => 'on1-my-sites',
            'id'     => 'on1-site-' . $i,
            'title'  => esc_html( $site['name'] ) . '<span style="color:#999;font-size:11px;display:block;line-height:1;margin-top:2px;">' . esc_html( ltrim( $note, ' —' ) ) . '</span>',
            'href'   => esc_url( $url ),
            'meta'   => [
                'target' => '_blank',
                'rel'    => 'noopener',
                'title'  => esc_attr( $site['url'] ?: $url ),
            ],
        ] );

        // Sub-items: frontend / admin
        if ( ! empty( $site['url'] ) && ! empty( $site['admin'] ) ) {
            $bar->add_node( [
                'parent' => 'on1-site-' . $i,
                'id'     => 'on1-site-' . $i . '-front',
                'title'  => '↗ View frontend',
                'href'   => esc_url( $site['url'] ),
                'meta'   => [ 'target' => '_blank', 'rel' => 'noopener' ],
            ] );
            $bar->add_node( [
                'parent' => 'on1-site-' . $i,
                'id'     => 'on1-site-' . $i . '-admin',
                'title'  => '↗ Open admin',
                'href'   => esc_url( $site['admin'] ),
                'meta'   => [ 'target' => '_blank', 'rel' => 'noopener' ],
            ] );
        }
    }

    // Add an "Edit list" item at the bottom.
    $bar->add_node( [
        'parent' => 'on1-my-sites',
        'id'     => 'on1-sites-edit',
        'title'  => '⚙ Edit list →',
        'href'   => admin_url( 'admin.php?page=on1-hub#sites' ),
    ] );
}, 80 );

// Style the My Sites dropdown a touch
add_action( 'admin_bar_init', function() {
    if ( ! is_admin_bar_showing() ) return;
    add_action( 'wp_head', 'on1_admin_bar_styles' );
    add_action( 'admin_head', 'on1_admin_bar_styles' );
} );
function on1_admin_bar_styles() {
    ?>
    <style>
    #wpadminbar #wp-admin-bar-on1-my-sites .ab-icon::before {
        color: #FF1F8C;
    }
    #wpadminbar #wp-admin-bar-on1-my-sites > .ab-item .ab-label {
        margin-left: 4px;
    }
    #wpadminbar #wp-admin-bar-on1-my-sites .ab-submenu .ab-item {
        line-height: 1.3 !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        height: auto !important;
    }
    #wpadminbar #wp-admin-bar-on1-my-sites .ab-submenu .ab-item span {
        color: rgba(255,255,255,0.55) !important;
    }
    </style>
    <?php
}


// ─── 3. ENQUEUE ADMIN FONTS ──────────────────────────────────
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'toplevel_page_on1-hub' ) return;
    wp_enqueue_style(
        'on1-admin-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap',
        [], ON1_VERSION
    );
} );


// ─── 4. RENDER ────────────────────────────────────────────────
function on1_render_admin_page() {
    $id       = on1_get_identity();
    $hero     = on1_get_hero();
    $stats    = on1_get_stats();
    $services = on1_get_services();
    $pricing  = on1_get_pricing();
    $quotes   = on1_get_quotes();
    $faq      = on1_get_faq();
    $brief    = on1_get_brief();
    $footer   = on1_get_footer();
    $sites    = on1_get_sites();
    $accent   = on1_opt( 'accent_color', '' );
    $toggles  = get_option( 'on1_show', [] );
    if ( ! is_array( $toggles ) ) $toggles = [];
    $just_saved = isset( $_GET['on1_saved'] );

    $toggle_keys = [
        'stats'    => 'Stat strip',
        'services' => 'Practice / services list',
        'work'     => 'Work · featured case + grid',
        'pricing'  => 'Pricing tiers',
        'quotes'   => 'Testimonials',
        'faq'      => 'FAQ accordion',
        'picker'   => 'Design preset picker (front-end widget)',
    ];
    ?>

    <style>
    :root {
        --on1-bg: #0A0A0A; --on1-bg-2: #131313; --on1-bg-3: #1A1A1A;
        --on1-fg: #FFFFFF;
        --on1-mute: rgba(255,255,255,0.55); --on1-faint: rgba(255,255,255,0.30);
        --on1-rule: rgba(255,255,255,0.10); --on1-rule-2: rgba(255,255,255,0.20);
        --on1-accent: #FF1F8C; --on1-accent-d: #C8095F;
        --on1-danger: #FF3344;
        --on1-sans: 'Inter', system-ui, sans-serif;
        --on1-serif: 'Instrument Serif', serif;
    }
    .toplevel_page_on1-hub #wpwrap,
    .toplevel_page_on1-hub #wpcontent,
    .toplevel_page_on1-hub #wpbody-content { background: var(--on1-bg) !important; }
    .toplevel_page_on1-hub .wrap { color: var(--on1-fg); padding-bottom: 120px; }
    .toplevel_page_on1-hub .update-nag,
    .toplevel_page_on1-hub .notice:not(.on1-saved),
    .toplevel_page_on1-hub .updated { display: none !important; }

    .on1-admin { max-width: 1080px; font-family: var(--on1-sans); font-size: 14px; }
    .on1-admin ::selection { background: var(--on1-accent); color: var(--on1-bg); }
    @keyframes on1-blink { 50% { opacity: 0; } }
    .on1-cursor::after { content: '_'; color: var(--on1-accent); animation: on1-blink 1s steps(2) infinite; margin-left: 4px; }
    .on1-signal { color: var(--on1-accent); }

    .on1-page-head {
        display: flex; justify-content: space-between; align-items: flex-end;
        gap: 2rem; flex-wrap: wrap;
        padding: 1rem 0 1.5rem; margin-bottom: 2rem;
        border-bottom: 1px solid var(--on1-rule);
    }
    .on1-page-head__title {
        font-family: var(--on1-sans);
        font-size: clamp(2rem, 5vw, 2.6rem);
        font-weight: 300;
        letter-spacing: -0.035em;
        line-height: 1; margin: 0;
        color: var(--on1-fg);
    }
    .on1-page-head__title em {
        font-family: var(--on1-serif);
        font-style: italic; font-weight: 400;
        color: var(--on1-accent);
    }
    .on1-page-head__title small {
        display: block;
        font-family: var(--on1-sans);
        font-size: 10px; letter-spacing: 0.32em;
        text-transform: uppercase;
        color: var(--on1-accent);
        margin-bottom: 0.5rem; font-weight: 500;
    }
    .on1-page-head__meta {
        font-family: var(--on1-sans);
        font-size: 10px; letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--on1-mute); text-align: right;
    }
    .on1-page-head__meta strong { display: block; color: var(--on1-accent); font-weight: 500; }

    .on1-saved {
        background: rgba(255, 31, 140, 0.08);
        border: 1px solid var(--on1-accent);
        color: var(--on1-accent);
        padding: 0.8rem 1.2rem;
        margin-bottom: 2rem;
        font-size: 11px;
        letter-spacing: 0.22em; text-transform: uppercase;
        font-weight: 500;
    }
    .on1-saved::before { content: '● '; }

    .on1-panel {
        border: 1px solid var(--on1-rule); border-radius: 14px;
        background: var(--on1-bg-2);
        margin-bottom: 1.6rem; overflow: hidden;
    }
    .on1-panel__head {
        display: flex; align-items: baseline; gap: 1rem;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--on1-rule);
    }
    .on1-panel__label {
        font-size: 11px; letter-spacing: 0.28em;
        text-transform: uppercase;
        color: var(--on1-fg); font-weight: 500;
    }
    .on1-panel__label::before { content: '●'; color: var(--on1-accent); margin-right: 0.5em; }
    .on1-panel__rule { flex: 1; height: 1px; background: var(--on1-rule); }
    .on1-panel__count {
        font-size: 10px; letter-spacing: 0.22em;
        text-transform: uppercase; color: var(--on1-mute);
    }
    .on1-panel__body { padding: 1.4rem; }

    /* Form fields */
    .on1-fields { display: grid; grid-template-columns: 180px 1fr; gap: 0; }
    .on1-field > label {
        font-size: 10px; letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--on1-mute); font-weight: 500;
        padding: 0.95rem 0.8rem 0.95rem 0;
        border-bottom: 1px dotted var(--on1-rule);
        display: flex; align-items: center;
    }
    .on1-field > label::before { content: '> '; color: var(--on1-accent); margin-right: 0.4em; }
    .on1-field > .on1-input-wrap { border-bottom: 1px dotted var(--on1-rule); padding: 0.45rem 0; }
    .on1-input, .on1-textarea {
        width: 100%; font-family: var(--on1-sans); font-size: 14px;
        background: transparent; border: none;
        color: var(--on1-fg) !important;
        padding: 0.5rem 0; outline: none;
        box-shadow: none !important;
        letter-spacing: 0; min-height: 0;
    }
    .on1-input::placeholder, .on1-textarea::placeholder { color: var(--on1-faint); }
    .on1-input:focus, .on1-textarea:focus { color: var(--on1-accent) !important; }
    .on1-textarea { resize: vertical; min-height: 70px; line-height: 1.6; }
    .on1-field-help {
        font-size: 10px; font-style: italic;
        color: var(--on1-faint); margin-top: 0.3rem;
    }

    /* Repeater rows */
    .on1-repeat { display: flex; flex-direction: column; }
    .on1-row {
        padding: 0.9rem 0.4rem;
        border-bottom: 1px dotted var(--on1-rule);
        display: grid; gap: 0.6rem;
        align-items: start;
        transition: background .12s;
    }
    .on1-row--stat    { grid-template-columns: 24px 1fr 2fr 28px; }
    .on1-row--service { grid-template-columns: 24px 1.2fr 2fr 1fr 28px; }
    .on1-row--quote   { grid-template-columns: 24px 2fr 1fr 1fr 60px 28px; }
    .on1-row--faq     { grid-template-columns: 24px 1.2fr 2fr 28px; }
    .on1-row--tier    { grid-template-columns: 24px 1fr 80px 80px 1.4fr auto 80px 28px; }
    .on1-row--site    { grid-template-columns: 24px 1fr 1.4fr 1.4fr 1fr 28px; }
    .on1-row:hover { background: rgba(255,31,140,0.03); }
    .on1-row.dragging { opacity: 0.4; }
    .on1-row.drag-over { box-shadow: inset 0 2px 0 var(--on1-accent); }
    .on1-row-input, .on1-row-textarea {
        background: transparent; border: none;
        color: var(--on1-fg) !important;
        font-family: var(--on1-sans); font-size: 13px;
        padding: 0.4rem 0.3rem;
        outline: none; box-shadow: none !important;
        width: 100%; letter-spacing: 0; min-height: 0;
    }
    .on1-row-input:focus, .on1-row-textarea:focus {
        color: var(--on1-accent) !important;
        background: rgba(255,31,140,0.05);
    }
    .on1-row-input::placeholder, .on1-row-textarea::placeholder { color: var(--on1-faint); }
    .on1-row-textarea { resize: vertical; min-height: 50px; line-height: 1.45; }
    .on1-drag {
        font-size: 14px; color: var(--on1-faint);
        cursor: grab; text-align: center;
        user-select: none; padding-top: 0.4rem;
    }
    .on1-drag:hover { color: var(--on1-accent); }
    .on1-delete {
        background: transparent;
        border: 1px solid transparent;
        color: var(--on1-mute);
        font-size: 14px;
        cursor: pointer;
        padding: 0.3rem;
        text-align: center;
        width: 100%;
        box-shadow: none !important;
        align-self: start;
    }
    .on1-delete:hover { color: var(--on1-danger); border-color: var(--on1-danger); }

    .on1-add-row {
        margin-top: 1rem; width: 100%;
        font-size: 11px; letter-spacing: 0.24em;
        text-transform: uppercase;
        background: transparent;
        border: 1px dashed var(--on1-rule);
        color: var(--on1-mute);
        padding: 0.95rem;
        cursor: pointer; box-shadow: none !important;
        border-radius: 8px;
        font-weight: 500;
    }
    .on1-add-row:hover { border-color: var(--on1-accent); color: var(--on1-accent); border-style: solid; }
    .on1-add-row::before { content: '+ '; color: var(--on1-accent); }

    .on1-toggle-row {
        display: grid; grid-template-columns: 1fr auto;
        gap: 1rem; align-items: center;
        padding: 0.9rem 0;
        border-bottom: 1px dotted var(--on1-rule);
    }
    .on1-toggle-row:last-child { border-bottom: none; }
    .on1-toggle-row__label { font-size: 13px; color: var(--on1-fg); }
    .on1-toggle {
        position: relative; width: 44px; height: 22px;
        background: var(--on1-bg-3);
        border: 1px solid var(--on1-rule);
        cursor: pointer; border-radius: 999px;
        transition: background .15s, border-color .15s;
    }
    .on1-toggle::after {
        content: ''; position: absolute;
        top: 1px; left: 1px; width: 18px; height: 18px;
        background: var(--on1-mute); border-radius: 50%;
        transition: left .18s, background .18s;
    }
    .on1-toggle.is-on { background: rgba(255,31,140,0.15); border-color: var(--on1-accent); }
    .on1-toggle.is-on::after { left: 23px; background: var(--on1-accent); box-shadow: 0 0 6px var(--on1-accent); }
    .on1-toggle input { display: none; }

    .on1-color-input {
        width: 60px; height: 40px;
        background: var(--on1-bg-3);
        border: 1px solid var(--on1-rule);
        cursor: pointer; padding: 4px;
        border-radius: 6px;
    }

    .on1-save-bar {
        position: fixed; bottom: 0; left: 160px; right: 0;
        background: var(--on1-bg);
        border-top: 1px solid var(--on1-accent);
        padding: 1rem 2rem;
        display: flex; justify-content: space-between;
        align-items: center; gap: 1rem; z-index: 90;
        flex-wrap: wrap;
    }
    @media (max-width: 960px) { .on1-save-bar { left: 36px; } }
    @media (max-width: 782px) { .on1-save-bar { left: 0; } }
    .on1-save-bar__status {
        font-size: 10px; letter-spacing: 0.22em;
        text-transform: uppercase; color: var(--on1-mute);
    }
    .on1-btn {
        font-size: 11px; letter-spacing: 0.24em;
        text-transform: uppercase;
        background: transparent;
        border: 1px solid var(--on1-rule);
        color: var(--on1-fg);
        padding: 0.85rem 1.4rem;
        cursor: pointer; box-shadow: none !important;
        text-decoration: none;
        display: inline-block;
        border-radius: 999px;
        font-weight: 500;
    }
    .on1-btn:hover { border-color: var(--on1-fg); }
    .on1-btn--primary { background: var(--on1-accent); color: var(--on1-bg) !important; border-color: var(--on1-accent); }
    .on1-btn--primary::before { content: '▶ '; }
    .on1-btn--primary:hover { background: var(--on1-accent-d); border-color: var(--on1-accent-d); }

    @media (max-width: 900px) {
        .on1-fields { grid-template-columns: 1fr; }
        .on1-field > label { padding-bottom: 0.3rem; border-bottom: none; }
        .on1-field > .on1-input-wrap { padding: 0 0 0.6rem; border-bottom: 1px dotted var(--on1-rule); margin-bottom: 0.5rem; }
        .on1-row--stat, .on1-row--service, .on1-row--quote, .on1-row--faq, .on1-row--tier {
            grid-template-columns: 24px 1fr 28px;
        }
        .on1-row > *:not(.on1-drag):not(.on1-delete) { grid-column: 2; }
    }
    </style>

    <div class="wrap on1-admin">

        <header class="on1-page-head">
            <h1 class="on1-page-head__title">
                <small>// Hub Control<span class="on1-cursor"></span></small>
                <em>on1</em>.agency settings.
            </h1>
            <div class="on1-page-head__meta">Theme<br><strong>v<?php echo esc_html( ON1_VERSION ); ?></strong></div>
        </header>

        <?php if ( $just_saved ) : ?>
            <div class="on1-saved">Settings saved · changes are live</div>
        <?php endif; ?>

        <form method="POST" action="" id="on1-form">
            <?php wp_nonce_field( 'on1_save', 'on1_nonce' ); ?>

            <!-- IDENTITY -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Identity</span><span class="on1-panel__rule"></span><span class="on1-panel__count">5 fields</span></div>
                <div class="on1-panel__body">
                    <div class="on1-fields">
                        <?php on1_render_field( 'Studio name',  'identity_name',     $id['name'] ); ?>
                        <?php on1_render_field( 'Short tagline','identity_tagline',  $id['tagline'] ); ?>
                        <?php on1_render_field( 'Location',     'identity_location', $id['location'] ); ?>
                        <?php on1_render_field( 'Status text',  'identity_status',   $id['status'] ); ?>
                        <?php on1_render_field( 'Email',        'identity_email',    $id['email'], 'email' ); ?>
                    </div>
                </div>
            </section>

            <!-- HERO -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Hero</span><span class="on1-panel__rule"></span><span class="on1-panel__count">9 fields · em allowed</span></div>
                <div class="on1-panel__body">
                    <div class="on1-fields">
                        <?php on1_render_field( 'Manifesto lead',    'hero_manifesto_lead', $hero['manifesto_lead'], 'text', 'The opening quote-marked line. <em> allowed.' ); ?>
                        <?php on1_render_field( 'Manifesto body',    'hero_manifesto_body', $hero['manifesto_body'], 'textarea', 'The rest of the lede paragraph. HTML allowed.' ); ?>
                        <?php on1_render_field( 'Sign-off name',     'hero_signoff_name',   $hero['signoff_name'] ); ?>
                        <?php on1_render_field( 'Sign-off role',     'hero_signoff_role',   $hero['signoff_role'] ); ?>
                        <?php on1_render_field( 'Headline line 1',   'hero_headline_line_1', $hero['headline_line_1'], 'text', 'Wrap accent words in <em>...</em>.' ); ?>
                        <?php on1_render_field( 'Headline line 2',   'hero_headline_line_2', $hero['headline_line_2'] ); ?>
                        <?php on1_render_field( 'Tag line',          'hero_tag_line',       $hero['tag_line'], 'text', 'e.g. "Booking <em>Q3</em> — slots open".' ); ?>
                        <?php on1_render_field( 'CTA label',         'hero_cta_label',      $hero['cta_label'] ); ?>
                        <?php on1_render_field( 'CTA URL',           'hero_cta_url',        $hero['cta_url'], 'url' ); ?>
                    </div>
                </div>
            </section>

            <!-- STATS -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Stats</span><span class="on1-panel__rule"></span><span class="on1-panel__count">4 recommended · drag to reorder</span></div>
                <div class="on1-panel__body">
                    <div class="on1-repeat" id="on1-repeat-stat">
                        <?php foreach ( $stats as $row ) on1_render_stat_row( $row ); ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="stat">Add stat</button>
                </div>
            </section>

            <!-- SERVICES -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Practice · services</span><span class="on1-panel__rule"></span><span class="on1-panel__count">3-5 recommended</span></div>
                <div class="on1-panel__body">
                    <div class="on1-repeat" id="on1-repeat-service">
                        <?php foreach ( $services as $row ) on1_render_service_row( $row ); ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="service">Add service</button>
                </div>
            </section>

            <!-- PRICING -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Pricing tiers</span><span class="on1-panel__rule"></span><span class="on1-panel__count">★ marks the featured tier · features one per line</span></div>
                <div class="on1-panel__body">
                    <div class="on1-repeat" id="on1-repeat-tier">
                        <?php $tier_idx = 0; foreach ( $pricing as $row ) { on1_render_tier_row( $row, $tier_idx++ ); } ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="tier">Add tier</button>
                </div>
            </section>

            <!-- QUOTES -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Testimonials</span><span class="on1-panel__rule"></span><span class="on1-panel__count">3 recommended</span></div>
                <div class="on1-panel__body">
                    <div class="on1-repeat" id="on1-repeat-quote">
                        <?php foreach ( $quotes as $row ) on1_render_quote_row( $row ); ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="quote">Add testimonial</button>
                </div>
            </section>

            <!-- FAQ -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">FAQ</span><span class="on1-panel__rule"></span><span class="on1-panel__count">first item shows open</span></div>
                <div class="on1-panel__body">
                    <div class="on1-repeat" id="on1-repeat-faq">
                        <?php foreach ( $faq as $row ) on1_render_faq_row( $row ); ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="faq">Add question</button>
                </div>
            </section>

            <!-- BRIEF CTA -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Brief CTA</span><span class="on1-panel__rule"></span><span class="on1-panel__count">8 fields</span></div>
                <div class="on1-panel__body">
                    <div class="on1-fields">
                        <?php on1_render_field( 'Eyebrow', 'brief_eyebrow', $brief['eyebrow'] ); ?>
                        <?php on1_render_field( 'Lede line 1', 'brief_lede_line_1', $brief['lede_line_1'] ); ?>
                        <?php on1_render_field( 'Lede line 2', 'brief_lede_line_2', $brief['lede_line_2'] ); ?>
                        <?php on1_render_field( 'Sub text', 'brief_sub', $brief['sub'], 'textarea' ); ?>
                        <?php on1_render_field( 'Primary label', 'brief_primary_label', $brief['primary_label'] ); ?>
                        <?php on1_render_field( 'Primary URL', 'brief_primary_url', $brief['primary_url'], 'url' ); ?>
                        <?php on1_render_field( 'Secondary label', 'brief_secondary_label', $brief['secondary_label'] ); ?>
                        <?php on1_render_field( 'Secondary URL', 'brief_secondary_url', $brief['secondary_url'], 'url' ); ?>
                    </div>
                </div>
            </section>

            <!-- FOOTER -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Footer</span><span class="on1-panel__rule"></span><span class="on1-panel__count">5 fields</span></div>
                <div class="on1-panel__body">
                    <div class="on1-fields">
                        <?php on1_render_field( 'Brand wordmark', 'footer_brand_html', $footer['brand_html'], 'text', '<em>, <sup>, <span> allowed.' ); ?>
                        <?php on1_render_field( 'About paragraph', 'footer_about_html', $footer['about_html'], 'textarea', 'HTML allowed.' ); ?>
                        <?php on1_render_field( 'Colophon line', 'footer_colophon', $footer['colophon'] ); ?>
                        <?php on1_render_field( 'Copyright line', 'footer_copyright', $footer['copyright'] ); ?>
                        <?php on1_render_field( 'Build note', 'footer_build_note', $footer['build_note'] ); ?>
                    </div>
                </div>
            </section>

            <!-- SITES I MANAGE -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Sites I manage</span><span class="on1-panel__rule"></span><span class="on1-panel__count">appears in the top admin-bar dropdown</span></div>
                <div class="on1-panel__body">
                    <p class="on1-field-help" style="margin-bottom: 1rem; padding-left: 0;">Quick-launch dashboard for all your WordPress sites. Each row appears in the <strong style="color: var(--on1-fg);">My Sites</strong> dropdown of the top admin bar — click any to jump straight to its admin in a new tab.</p>
                    <div class="on1-repeat" id="on1-repeat-site">
                        <?php foreach ( $sites as $row ) on1_render_site_row( $row ); ?>
                    </div>
                    <button type="button" class="on1-add-row" data-add="site">Add site</button>
                </div>
            </section>

            <!-- DISPLAY -->
            <section class="on1-panel">
                <div class="on1-panel__head"><span class="on1-panel__label">Display</span><span class="on1-panel__rule"></span><span class="on1-panel__count">6 toggles + accent</span></div>
                <div class="on1-panel__body">
                    <?php foreach ( $toggle_keys as $k => $label ) :
                        $is_on = ! isset( $toggles[ $k ] ) ? true : (bool) $toggles[ $k ];
                    ?>
                        <div class="on1-toggle-row">
                            <div class="on1-toggle-row__label"><?php echo esc_html( $label ); ?></div>
                            <label class="on1-toggle <?php echo $is_on ? 'is-on' : ''; ?>" data-toggle>
                                <input type="checkbox" name="show_<?php echo esc_attr( $k ); ?>" value="1"<?php checked( $is_on ); ?>>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <div class="on1-toggle-row" style="border-top: 1px solid var(--on1-rule); margin-top: 0.6rem; padding-top: 1.4rem;">
                        <div class="on1-toggle-row__label">
                            Accent color
                            <div class="on1-field-help">Override the default magenta (#FF1F8C).</div>
                        </div>
                        <input type="color" name="accent_color" class="on1-color-input" value="<?php echo esc_attr( $accent ?: '#FF1F8C' ); ?>">
                    </div>
                </div>
            </section>

            <div class="on1-save-bar">
                <div class="on1-save-bar__status">
                    <span class="on1-signal">●</span> Ready ·
                    <?php echo count( $services ); ?> services ·
                    <?php echo count( $pricing ); ?> tiers ·
                    <?php echo count( $quotes ); ?> quotes ·
                    <?php echo count( $faq ); ?> FAQ
                </div>
                <div style="display: flex; gap: 0.6rem;">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="on1-btn">View site ↗</a>
                    <button type="submit" name="on1_save" value="1" class="on1-btn on1-btn--primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>

    <template id="on1-tpl-stat"><?php on1_render_stat_row( [] ); ?></template>
    <template id="on1-tpl-service"><?php on1_render_service_row( [] ); ?></template>
    <template id="on1-tpl-quote"><?php on1_render_quote_row( [] ); ?></template>
    <template id="on1-tpl-faq"><?php on1_render_faq_row( [] ); ?></template>
    <template id="on1-tpl-tier"><?php on1_render_tier_row( [], 999 ); ?></template>
    <template id="on1-tpl-site"><?php on1_render_site_row( [] ); ?></template>

    <script>
    (function(){
        document.querySelectorAll('[data-toggle]').forEach(t => {
            t.addEventListener('click', e => {
                e.preventDefault();
                const cb = t.querySelector('input[type=checkbox]');
                cb.checked = !cb.checked;
                t.classList.toggle('is-on', cb.checked);
            });
        });

        document.querySelectorAll('[data-add]').forEach(btn => {
            btn.addEventListener('click', () => {
                const kind = btn.dataset.add;
                const tpl = document.getElementById('on1-tpl-' + kind);
                if (!tpl) return;
                const target = document.getElementById('on1-repeat-' + kind);
                target.appendChild(tpl.content.cloneNode(true));
                const lastRow = target.querySelector('.on1-row:last-child');
                attachRowEvents(lastRow);
                const firstInput = lastRow && lastRow.querySelector('input, textarea');
                if (firstInput) firstInput.focus();
                // For tiers: rebuild featured position values
                if (kind === 'tier') renumberTiers();
            });
        });

        function renumberTiers() {
            const rows = document.querySelectorAll('#on1-repeat-tier .on1-row');
            rows.forEach((row, i) => {
                const cb = row.querySelector('input[type=checkbox][name="tier_featured_at"]');
                if (cb) cb.value = i;
            });
        }

        // Capture featured tier positions on submit and inject as separate hidden inputs.
        const form = document.getElementById('on1-form');
        form.addEventListener('submit', () => {
            // Remove any leftover position carriers.
            form.querySelectorAll('input[name="tier_featured_pos[]"]').forEach(n => n.remove());
            document.querySelectorAll('#on1-repeat-tier .on1-row').forEach((row, i) => {
                const cb = row.querySelector('input[type=checkbox][name="tier_featured_at"]');
                if (cb && cb.checked) {
                    const h = document.createElement('input');
                    h.type = 'hidden';
                    h.name = 'tier_featured_pos[]';
                    h.value = String(i);
                    form.appendChild(h);
                }
            });
        });

        function attachRowEvents(row) {
            if (!row) return;
            const del = row.querySelector('.on1-delete');
            if (del) del.addEventListener('click', () => {
                if (confirm('Remove this row?')) row.remove();
            });
            row.setAttribute('draggable', 'true');
            row.addEventListener('dragstart', e => {
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            row.addEventListener('dragend', () => {
                row.classList.remove('dragging');
                row.parentNode.querySelectorAll('.on1-row').forEach(r => r.classList.remove('drag-over'));
                renumberTiers();
            });
            row.addEventListener('dragover', e => { e.preventDefault(); row.classList.add('drag-over'); });
            row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
            row.addEventListener('drop', e => {
                e.preventDefault();
                row.classList.remove('drag-over');
                const dragging = row.parentNode.querySelector('.dragging');
                if (dragging && dragging !== row) {
                    const all = Array.from(row.parentNode.querySelectorAll('.on1-row'));
                    if (all.indexOf(dragging) < all.indexOf(row)) row.parentNode.insertBefore(dragging, row.nextSibling);
                    else                                          row.parentNode.insertBefore(dragging, row);
                }
            });
        }

        document.querySelectorAll('.on1-row').forEach(attachRowEvents);
    })();
    </script>

    <?php
}


/* ─── 5. ROW RENDERERS ──────────────────────────────────────── */

function on1_render_field( $label, $name, $value, $type = 'text', $help = '' ) {
    echo '<div class="on1-field">';
    echo '<label for="f-' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label>';
    echo '<div class="on1-input-wrap">';
    if ( $type === 'textarea' ) {
        echo '<textarea class="on1-textarea" id="f-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" rows="3">' . esc_textarea( $value ) . '</textarea>';
    } else {
        echo '<input class="on1-input" id="f-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '">';
    }
    if ( $help ) echo '<div class="on1-field-help">' . wp_kses( $help, [ 'em' => [], 'strong' => [], 'code' => [] ] ) . '</div>';
    echo '</div></div>';
}

function on1_render_stat_row( $row = [] ) {
    $num   = esc_attr( $row['num']   ?? '' );
    $label = esc_attr( $row['label'] ?? '' );
    ?>
    <div class="on1-row on1-row--stat">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <input class="on1-row-input" type="text" name="stat_num[]"   value="<?php echo $num; ?>"   placeholder='Number — e.g. <span class="accent">14</span>+'>
        <input class="on1-row-input" type="text" name="stat_label[]" value="<?php echo $label; ?>" placeholder="Label (newline to wrap)">
        <button type="button" class="on1-delete">×</button>
    </div>
    <?php
}

function on1_render_service_row( $row = [] ) {
    $name = esc_attr( $row['name'] ?? '' );
    $desc = esc_textarea( $row['desc'] ?? '' );
    $url  = esc_attr( $row['url']  ?? '#brief' );
    ?>
    <div class="on1-row on1-row--service">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <input class="on1-row-input" type="text" name="service_name[]" value="<?php echo $name; ?>" placeholder="Name (em allowed)">
        <textarea class="on1-row-textarea" name="service_desc[]" placeholder="Description"><?php echo $desc; ?></textarea>
        <input class="on1-row-input" type="text" name="service_url[]" value="<?php echo $url; ?>" placeholder="URL">
        <button type="button" class="on1-delete">×</button>
    </div>
    <?php
}

function on1_render_quote_row( $row = [] ) {
    $text     = esc_textarea( $row['text']     ?? '' );
    $author   = esc_attr( $row['author']   ?? '' );
    $role     = esc_attr( $row['role']     ?? '' );
    $initials = esc_attr( $row['initials'] ?? '' );
    ?>
    <div class="on1-row on1-row--quote">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <textarea class="on1-row-textarea" name="quote_text[]" placeholder="Quote text"><?php echo $text; ?></textarea>
        <input class="on1-row-input" type="text" name="quote_author[]"   value="<?php echo $author; ?>"   placeholder="Author">
        <input class="on1-row-input" type="text" name="quote_role[]"     value="<?php echo $role; ?>"     placeholder="Role · Studio">
        <input class="on1-row-input" type="text" name="quote_initials[]" value="<?php echo $initials; ?>" placeholder="MR" maxlength="3" style="text-align:center;">
        <button type="button" class="on1-delete">×</button>
    </div>
    <?php
}

function on1_render_faq_row( $row = [] ) {
    $q = esc_attr( $row['q'] ?? '' );
    $a = esc_textarea( $row['a'] ?? '' );
    ?>
    <div class="on1-row on1-row--faq">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <input class="on1-row-input" type="text" name="faq_q[]" value="<?php echo $q; ?>" placeholder="Question">
        <textarea class="on1-row-textarea" name="faq_a[]" placeholder="Answer"><?php echo $a; ?></textarea>
        <button type="button" class="on1-delete">×</button>
    </div>
    <?php
}

function on1_render_site_row( $row = [] ) {
    $name  = esc_attr( $row['name']  ?? '' );
    $url   = esc_attr( $row['url']   ?? '' );
    $admin = esc_attr( $row['admin'] ?? '' );
    $note  = esc_attr( $row['note']  ?? '' );
    ?>
    <div class="on1-row on1-row--site">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <input class="on1-row-input" type="text" name="site_name[]"  value="<?php echo $name; ?>"  placeholder="Site name">
        <input class="on1-row-input" type="url"  name="site_url[]"   value="<?php echo $url; ?>"   placeholder="https://example.com">
        <input class="on1-row-input" type="url"  name="site_admin[]" value="<?php echo $admin; ?>" placeholder="https://example.com/wp-admin/">
        <input class="on1-row-input" type="text" name="site_note[]"  value="<?php echo $note; ?>"  placeholder="Theme · version · note">
        <button type="button" class="on1-delete">×</button>
    </div>
    <?php
}


function on1_render_tier_row( $row = [], $idx = 0 ) {
    $name     = esc_attr( $row['name']     ?? '' );
    $price    = esc_attr( $row['price']    ?? '' );
    $period   = esc_attr( $row['period']   ?? '' );
    $features = is_array( $row['features'] ?? null ) ? implode( "\n", $row['features'] ) : '';
    $featured = ( $row['featured'] ?? '' ) === '1';
    $cta      = esc_attr( $row['cta']      ?? 'Choose' );
    $url      = esc_attr( $row['url']      ?? '#brief' );
    ?>
    <div class="on1-row on1-row--tier">
        <span class="on1-drag" title="Drag to reorder">⋮⋮</span>
        <input class="on1-row-input" type="text" name="tier_name[]"   value="<?php echo $name; ?>"   placeholder="Tier name (em allowed)">
        <input class="on1-row-input" type="text" name="tier_price[]"  value="<?php echo $price; ?>"  placeholder="€1.8K">
        <input class="on1-row-input" type="text" name="tier_period[]" value="<?php echo $period; ?>" placeholder="/ 2 wks">
        <textarea class="on1-row-textarea" name="tier_features[]" placeholder="One feature per line"><?php echo esc_textarea( $features ); ?></textarea>
        <label style="display:flex;align-items:center;gap:6px;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:var(--on1-mute);cursor:pointer;padding:0.4rem 0.3rem;">
            <input type="checkbox" name="tier_featured_at" value="<?php echo (int) $idx; ?>"<?php checked( $featured ); ?> style="margin:0;"> ★
        </label>
        <input class="on1-row-input" type="text" name="tier_cta[]" value="<?php echo $cta; ?>" placeholder="CTA">
        <button type="button" class="on1-delete">×</button>
        <input type="hidden" name="tier_url[]" value="<?php echo $url; ?>">
    </div>
    <?php
}
