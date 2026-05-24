<?php
/**
 * Kavithai (Modernized) — main functions
 * Tamil only. Larger, calmer type. Photo support throughout.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ─────────────────────────────────────────────  THEME SETUP
add_action( 'after_setup_theme', function() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption' ] );

    // Image sizes
    add_image_size( 'book-cover',     400, 600, true );
    add_image_size( 'poet-portrait',  720, 900, true );
    add_image_size( 'poem-hero',     1600, 900, true );
    add_image_size( 'poem-card',      640, 360, true );

    register_nav_menus( [
        'primary' => 'முதன்மை மெனு',
        'footer'  => 'அடி மெனு',
    ] );
} );

// ─────────────────────────────────────────────  SETTINGS
// Merged into the custom panel — inc/settings-page.php (slug: kavithai-settings)
// kv_opt() falls back to get_theme_mod() which that panel writes to.


// ─────────────────────────────────────────────  ONE-SHOT v2.0 -> v2.1 PALETTE MIGRATION
// If the saved Customizer values are the OLD v2.0 defaults
// (#8B2020 + #F6EFDD), upgrade them to the v2.1 Relief palette
// (#6B1414 + #F0E7CC). Only touches values that match the old
// defaults exactly — if the user customized to those colors on purpose,
// they re-pick them via the settings page. The migration runs once,
// gated by an option flag.
add_action( 'after_setup_theme', function() {
    if ( get_option( 'kv_palette_migrated_v21', '' ) === '1' ) return;

    $primary = get_theme_mod( 'kv_primary' );
    if ( is_string( $primary ) && strcasecmp( $primary, '#8B2020' ) === 0 ) {
        set_theme_mod( 'kv_primary', '#6B1414' );
    }
    $bg = get_theme_mod( 'kv_bg' );
    if ( is_string( $bg ) && strcasecmp( $bg, '#F6EFDD' ) === 0 ) {
        set_theme_mod( 'kv_bg', '#F0E7CC' );
    }

    update_option( 'kv_palette_migrated_v21', '1' );
} );

// ─────────────────────────────────────────────  ENQUEUE
add_action( 'wp_enqueue_scripts', function() {
    // Cache-bust on file change. filemtime returns the last-modified
    // unix timestamp; the URL becomes style.css?ver=1716483847 and
    // changes whenever the file changes — forcing browsers to refetch.
    // (Previously version was hardcoded '2.1.0' and never changed, so
    // every CSS update was invisible to anyone with a warm browser cache.)
    $css_ver   = (string) @filemtime( get_template_directory() . '/style.css' );
    $fonts_ver = (string) @filemtime( get_template_directory() . '/assets/fonts.css' );
    $js_ver    = (string) @filemtime( get_template_directory() . '/assets/js/main.js' );

    // EB Garamond — Latin sidekick for numerals, dates, small caps.
    // TODO (v2.2): self-host via assets/fonts.css to remove the Google Fonts call (GDPR).
    wp_enqueue_style(
        'kavithai-eb-garamond',
        'https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap',
        [], '2.1.0'
    );
    wp_enqueue_style(
        'kavithai-fonts',
        get_template_directory_uri() . '/assets/fonts.css',
        [], $fonts_ver
    );
    wp_enqueue_style( 'kavithai-style', get_stylesheet_uri(), [ 'kavithai-fonts', 'kavithai-eb-garamond' ], $css_ver );
    wp_enqueue_script( 'kavithai-js', get_template_directory_uri() . '/assets/js/main.js', [], $js_ver, true );

    // Identity values. The static palette in style.css IS the design
    // spec. We only inject saved Customizer values if they're a
    // deliberate user choice — never the stale v2.0 defaults that
    // got auto-saved on older installs.
    $primary  = kv_opt( 'kv_primary', '' );
    $bg       = kv_opt( 'kv_bg', '' );
    $fontsize = (int) kv_opt( 'kv_fontsize', 19 );
    $fx       = (int) kv_opt( 'kv_portrait_focal_x', 50 );
    $fy       = (int) kv_opt( 'kv_portrait_focal_y', 35 );

    // Reject stale v2.0 defaults — if the saved value matches the old
    // default exactly, treat as unset so the static palette wins.
    $stale_v20 = [ '#8B2020', '#8b2020', '#F6EFDD', '#f6efdd' ];
    $primary_s = sanitize_hex_color( $primary );
    $bg_s      = sanitize_hex_color( $bg );

    $rules = [];
    if ( $primary_s && ! in_array( $primary_s, $stale_v20, true ) ) {
        $rules[] = "--oxblood: {$primary_s};";
    }
    if ( $bg_s && ! in_array( $bg_s, $stale_v20, true ) ) {
        $rules[] = "--paper: {$bg_s};";
    }
    $rules[] = "--t-base: {$fontsize}px;";
    $rules[] = "--focal-x: {$fx}%;";
    $rules[] = "--focal-y: {$fy}%;";

    wp_add_inline_style( 'kavithai-style', ":root { " . implode( ' ', $rules ) . " }" );
} );

// ─────────────────────────────────────────────  CUSTOM POST TYPES
add_action( 'init', function() {

    // POEM
    register_post_type( 'kavithai_gedicht', [
        'labels' => [
            'name'          => 'கவிதைகள்',
            'singular_name' => 'கவிதை',
            'add_new'       => 'புதிய கவிதை',
            'add_new_item'  => 'புதிய கவிதை சேர்க்க',
            'edit_item'     => 'கவிதை திருத்தவும்',
            'view_item'     => 'கவிதை காண்க',
            'all_items'     => 'எல்லா கவிதைகளும்',
            'search_items'  => 'கவிதைகளை தேடவும்',
            'not_found'     => 'கவிதை இல்லை',
            'not_found_in_trash' => 'குப்பையில் கவிதை இல்லை',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'kavithaigal' ],
        'menu_icon'     => 'dashicons-edit-large',
        'menu_position' => 5,
        // ★ 'thumbnail' added — poems can now have featured images
        'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
        'show_in_rest'  => true,
    ] );

    // BOOK
    register_post_type( 'kavithai_nul', [
        'labels' => [
            'name'          => 'நூல்கள்',
            'singular_name' => 'நூல்',
            'add_new'       => 'புதிய நூல்',
            'add_new_item'  => 'புதிய நூல் சேர்க்க',
            'edit_item'     => 'நூல் திருத்தவும்',
            'all_items'     => 'எல்லா நூல்களும்',
            'search_items'  => 'நூல்களை தேடவும்',
        ],
        'public'        => true,
        // has_archive disabled in v2.1: the slug 'nulgal' is freed for
        // a WP page (assign Template = நூல்கள்). Individual book URLs
        // at /nulgal/<slug>/ still resolve via the rewrite rule below.
        'has_archive'   => false,
        'rewrite'       => [ 'slug' => 'nulgal', 'with_front' => false ],
        'menu_icon'     => 'dashicons-book',
        'menu_position' => 6,
        'supports'      => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'show_in_rest'  => true,
    ] );

    // AWARD
    register_post_type( 'kavithai_virudu', [
        'labels' => [
            'name'          => 'விருதுகள்',
            'singular_name' => 'விருது',
            'add_new'       => 'புதிய விருது',
            'add_new_item'  => 'விருது சேர்க்க',
            'edit_item'     => 'விருது திருத்தவும்',
            'all_items'     => 'எல்லா விருதுகளும்',
        ],
        'public'        => false,
        'show_ui'       => true,
        'menu_icon'     => 'dashicons-awards',
        'menu_position' => 7,
        // ★ 'thumbnail' added — awards can show a photo / certificate
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
    ] );

    register_taxonomy( 'kavithai_cat', 'kavithai_gedicht', [
        'labels' => [
            'name'         => 'வகைகள்',
            'singular_name'=> 'வகை',
            'add_new_item' => 'புதிய வகை',
            'all_items'    => 'எல்லா வகைகளும்',
        ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => [ 'slug' => 'vagai' ],
        'show_in_rest' => true,
    ] );
} );

// ─────────────────────────────────────────────  META BOXES
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'kv_gedicht_meta', 'கவிதை விவரங்கள்', 'kv_gedicht_meta_cb', 'kavithai_gedicht', 'side', 'high' );
    add_meta_box( 'kv_nul_meta',     'நூல் விவரங்கள்',  'kv_nul_meta_cb',     'kavithai_nul',     'side', 'high' );
    add_meta_box( 'kv_virudu_meta',  'விருது விவரங்கள்','kv_virudu_meta_cb',  'kavithai_virudu',  'side', 'high' );

    // Extras for poems (dedication, audio, backstory, location)
    add_meta_box( 'kv_poem_extra', 'கூடுதல் விவரங்கள்', 'kv_poem_extra_cb', 'kavithai_gedicht', 'normal', 'default' );

    // Reactions viewer (read-only)
    add_meta_box( 'kv_reactions', 'வாசகர் கருத்துகள்', 'kv_reactions_box', 'kavithai_gedicht', 'normal', 'low' );
} );

function kv_field( $label, $name, $value, $type = 'text', $options = [] ) {
    echo '<p style="margin:14px 0 5px;font-size:14px;color:#5C4530;font-family:\'Noto Serif Tamil\',Georgia,serif;">' . esc_html( $label ) . '</p>';
    $base_style = 'width:100%;font-size:15px;padding:8px 10px;font-family:\'Noto Serif Tamil\',Georgia,serif;border:1px solid #d4c4a8;background:#FBF6E9;border-radius:4px;';

    if ( $type === 'select' ) {
        echo '<select name="' . esc_attr($name) . '" style="' . $base_style . '">';
        foreach ( $options as $v => $l ) {
            echo '<option value="' . esc_attr($v) . '"' . selected($value,$v,false) . '>' . esc_html($l) . '</option>';
        }
        echo '</select>';
    } elseif ( $type === 'color' ) {
        echo '<input type="color" name="' . esc_attr($name) . '" value="' . esc_attr($value ?: '#8B2020') . '" style="width:100%;height:40px;border:1px solid #d4c4a8;padding:2px;cursor:pointer;border-radius:4px;background:#fff;">';
    } elseif ( $type === 'textarea' ) {
        echo '<textarea name="' . esc_attr($name) . '" style="' . $base_style . 'min-height:90px;line-height:1.8;resize:vertical;">' . esc_textarea($value) . '</textarea>';
    } else {
        $extra = $type === 'number' ? ' min="1900" max="2099"' : '';
        echo '<input type="' . $type . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" style="' . $base_style . '"' . $extra . '>';
    }
}

function kv_gedicht_meta_cb( $post ) {
    wp_nonce_field( 'kv_meta', 'kv_nonce' );
    kv_field( 'ஆண்டு', 'kv_year', get_post_meta($post->ID,'_kv_year',true), 'number' );
    kv_field( 'குறிப்பு (சிறு)', 'kv_note', get_post_meta($post->ID,'_kv_note',true) );
}

function kv_nul_meta_cb( $post ) {
    wp_nonce_field( 'kv_meta', 'kv_nonce' );
    kv_field( 'ஆண்டு', 'kv_year', get_post_meta($post->ID,'_kv_year',true), 'number' );
    kv_field( 'பதிப்பகம்', 'kv_publisher', get_post_meta($post->ID,'_kv_publisher',true) );
    kv_field( 'வகை', 'kv_type', get_post_meta($post->ID,'_kv_type',true), 'select', [
        'kavithai' => 'கவிதை தொகுப்பு',
        'kathai'   => 'சிறுகதைகள்',
        'novel'    => 'நாவல்',
        'katture'  => 'கட்டுரைகள்',
        'other'    => 'மற்றவை',
    ] );
    kv_field( 'அட்டை நிறம் (படம் இல்லாதபோது)', 'kv_color', get_post_meta($post->ID,'_kv_color',true), 'color' );
}

function kv_virudu_meta_cb( $post ) {
    wp_nonce_field( 'kv_meta', 'kv_nonce' );
    kv_field( 'ஆண்டு', 'kv_year', get_post_meta($post->ID,'_kv_year',true), 'number' );
    kv_field( 'அமைப்பு', 'kv_org', get_post_meta($post->ID,'_kv_org',true) );
}

// Poem extras: dedication, location, backstory, audio
function kv_poem_extra_cb( $post ) {
    wp_nonce_field( 'kv_extra', 'kv_extra_nonce' );
    $ded   = get_post_meta( $post->ID, '_kv_dedication',  true );
    $loc   = get_post_meta( $post->ID, '_kv_origin_loc',  true );
    $back  = get_post_meta( $post->ID, '_kv_backstory',   true );
    $audio = get_post_meta( $post->ID, '_kv_audio_url',   true );
    $feat  = get_post_meta( $post->ID, '_kv_featured',    true );

    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">';

    echo '<div>';
    kv_field( '💝 யாருக்கு அர்ப்பணம்', 'kv_dedication', $ded );
    kv_field( '📍 எங்கே எழுதப்பட்டது', 'kv_origin_loc', $loc );
    kv_field( '🎵 ஒலிப்பதிவு இணைப்பு (audio URL)', 'kv_audio_url', $audio, 'url' );
    echo '<label style="display:flex;align-items:center;gap:10px;font-family:\'Noto Serif Tamil\',Georgia,serif;font-size:14px;color:#1A0E06;margin-top:14px;cursor:pointer;">';
    echo '<input type="checkbox" name="kv_featured" value="1"' . checked($feat,'1',false) . '> ⭐ முகப்பில் காட்டு';
    echo '</label>';
    echo '</div>';

    echo '<div>';
    kv_field( '📖 இந்த கவிதையின் கதை', 'kv_backstory', $back, 'textarea' );
    echo '</div>';

    echo '</div>';
}

// Save handlers
add_action( 'save_post', function( $id ) {
    if ( ! isset($_POST['kv_nonce']) || ! wp_verify_nonce($_POST['kv_nonce'],'kv_meta') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;  // hardened in CP6
    $map = [
        'kv_year'=>'_kv_year', 'kv_note'=>'_kv_note',
        'kv_publisher'=>'_kv_publisher', 'kv_type'=>'_kv_type',
        'kv_org'=>'_kv_org',
    ];
    foreach ( $map as $k => $m ) {
        if ( isset($_POST[$k]) ) update_post_meta( $id, $m, sanitize_text_field($_POST[$k]) );
    }
    if ( isset($_POST['kv_color']) ) {
        update_post_meta( $id, '_kv_color', sanitize_hex_color($_POST['kv_color']) ?: '#6B1414' );
    }
} );

add_action( 'save_post_kavithai_gedicht', function($id) {
    if ( !isset($_POST['kv_extra_nonce']) || !wp_verify_nonce($_POST['kv_extra_nonce'],'kv_extra') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;  // hardened in CP6
    $map = [
        'kv_dedication' => '_kv_dedication',
        'kv_origin_loc' => '_kv_origin_loc',
    ];
    foreach ($map as $k=>$m) {
        if (isset($_POST[$k])) update_post_meta($id, $m, sanitize_text_field($_POST[$k]));
    }
    if (isset($_POST['kv_audio_url'])) update_post_meta($id, '_kv_audio_url', esc_url_raw($_POST['kv_audio_url']));
    if (isset($_POST['kv_backstory'])) update_post_meta($id, '_kv_backstory', sanitize_textarea_field($_POST['kv_backstory']));
    update_post_meta($id, '_kv_featured', isset($_POST['kv_featured']) ? '1' : '0');
});

// Reactions display in admin (read-only)
function kv_reactions_box( $post ) {
    $reactions = get_post_meta( $post->ID, '_kv_reactions', true ) ?: [];
    if ( empty($reactions) ) {
        echo '<p style="color:#7D6448;font-style:italic;font-size:14px;font-family:\'Noto Serif Tamil\',serif;">இன்னும் கருத்துகள் இல்லை.</p>';
        return;
    }
    foreach ( array_reverse($reactions) as $r ) {
        echo '<div style="border-bottom:1px solid rgba(125,100,72,.12);padding:12px 0;font-family:\'Noto Serif Tamil\',serif;">';
        echo '<strong style="font-size:15px;">' . esc_html($r['name']) . '</strong>';
        echo '<span style="font-size:12px;color:#aaa;margin-left:10px;">' . esc_html($r['date']) . '</span>';
        echo '<p style="margin:5px 0 0;font-size:14px;color:#2B1A0C;line-height:1.7;">' . esc_html($r['msg']) . '</p>';
        echo '</div>';
    }
}

// ─────────────────────────────────────────────  CUSTOMIZER
add_action( 'customize_register', function( $wpc ) {

    $wpc->add_panel( 'kv_panel', [ 'title' => '🌸 தளம் — அமைப்பு', 'priority' => 10 ] );

    // Colors
    $wpc->add_section( 'kv_colors', [ 'title' => '🎨 வண்ணங்கள்', 'panel' => 'kv_panel' ] );
    $wpc->add_setting( 'kv_primary', [ 'default' => '#6B1414', 'sanitize_callback' => 'sanitize_hex_color' ] );
    $wpc->add_control( new WP_Customize_Color_Control( $wpc, 'kv_primary', [
        'label' => 'முக்கிய நிறம் (Akzent)', 'section' => 'kv_colors' ] ) );
    $wpc->add_setting( 'kv_bg', [ 'default' => '#F0E7CC', 'sanitize_callback' => 'sanitize_hex_color' ] );
    $wpc->add_control( new WP_Customize_Color_Control( $wpc, 'kv_bg', [
        'label' => 'பின்னணி நிறம்', 'section' => 'kv_colors' ] ) );

    // Type size
    $wpc->add_section( 'kv_typo', [ 'title' => '🔠 எழுத்து அளவு', 'panel' => 'kv_panel' ] );
    $wpc->add_setting( 'kv_fontsize', [ 'default' => 19, 'sanitize_callback' => 'absint' ] );
    $wpc->add_control( 'kv_fontsize', [
        'label' => 'எழுத்து அளவு (px)',
        'description' => '17 = சிறியது · 19 = சாதாரணம் · 22 = பெரியது',
        'section' => 'kv_typo', 'type' => 'range',
        'input_attrs' => [ 'min' => 16, 'max' => 24, 'step' => 1 ] ] );

    // Poet info
    $wpc->add_section( 'kv_poet', [ 'title' => '👤 கவிஞர் தகவல்', 'panel' => 'kv_panel' ] );
    foreach ( [
        'kv_name'        => 'பெயர்',
        'kv_subname'     => 'துணை பெயர் / தலைப்பு',
        'kv_location'    => 'இருப்பிடம்',
        'kv_tagline'     => 'மேற்கோள் (footer)',
    ] as $s => $l ) {
        $wpc->add_setting( $s, [ 'sanitize_callback' => 'sanitize_text_field', 'default' => '' ] );
        $wpc->add_control( $s, [ 'label' => $l, 'section' => 'kv_poet', 'type' => 'text' ] );
    }
    $wpc->add_setting( 'kv_email', [ 'sanitize_callback' => 'sanitize_email', 'default' => '' ] );
    $wpc->add_control( 'kv_email', [ 'label' => 'மின்னஞ்சல்', 'section' => 'kv_poet', 'type' => 'email' ] );

    // ★ POET PORTRAIT — Customizer media upload
    $wpc->add_section( 'kv_portrait', [ 'title' => '📷 கவிஞர் புகைப்படம்', 'panel' => 'kv_panel' ] );
    $wpc->add_setting( 'kv_portrait_id', [ 'default' => 0, 'sanitize_callback' => 'absint' ] );
    $wpc->add_control( new WP_Customize_Media_Control( $wpc, 'kv_portrait_id', [
        'label' => 'முகப்பு புகைப்படம் (4:5 ratio recommended)',
        'description' => 'முகப்புப் பக்கத்தில் கீழ்-வலதில் காட்டப்படும்.',
        'section' => 'kv_portrait',
        'mime_type' => 'image',
    ] ) );

    // Focal point sliders
    $wpc->add_setting( 'kv_portrait_focal_x', [ 'default' => 50, 'sanitize_callback' => 'absint' ] );
    $wpc->add_control( 'kv_portrait_focal_x', [
        'label' => 'புகைப்பட மையம் — இடம் ↔ வலம் (%)',
        'description' => 'முகம் எங்கே காட்ட வேண்டும் என்பதை தேர்வு செய்யுங்கள். 50 = மையம்.',
        'section' => 'kv_portrait', 'type' => 'range',
        'input_attrs' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ] );
    $wpc->add_setting( 'kv_portrait_focal_y', [ 'default' => 35, 'sanitize_callback' => 'absint' ] );
    $wpc->add_control( 'kv_portrait_focal_y', [
        'label' => 'புகைப்பட மையம் — மேல் ↕ கீழ் (%)',
        'description' => '35 = முகம் சிறிது மேலே. 50 = மையம்.',
        'section' => 'kv_portrait', 'type' => 'range',
        'input_attrs' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ] );

    // Hero verse + about + diaspora quote
    $wpc->add_section( 'kv_texts', [ 'title' => '✍ முகப்பு வாக்கியங்கள்', 'panel' => 'kv_panel' ] );
    foreach ( [
        'kv_hero_verse'      => 'முகப்பில் கவிதை வரிகள் (Hero)',
        'kv_bio_text'        => 'என்னை பற்றி (Bio)',
        'kv_diaspora_quote'  => 'சிறப்பு மேற்கோள் (Pull quote)',
    ] as $s => $l ) {
        $wpc->add_setting( $s, [ 'sanitize_callback' => 'sanitize_textarea_field', 'default' => '' ] );
        $wpc->add_control( $s, [ 'label' => $l, 'section' => 'kv_texts', 'type' => 'textarea' ] );
    }
} );

// ─────────────────────────────────────────────  HELPERS

/**
 * Single source of truth: theme_mods.
 * (Earlier versions fell back to ACF; we removed that path because the
 * settings page only writes to theme_mods, so ACF values would silently
 * shadow what the user just saved — a bug, not a feature.)
 */
function kv_opt( $key, $default = '' ) {
    return get_theme_mod( $key, $default );
}

function kv_name()     { return kv_opt( 'kv_name',     'கமலா செல்வராசா' ); }
function kv_subname()  { return kv_opt( 'kv_subname',  '' ); }
function kv_location() { return kv_opt( 'kv_location', 'யாழ்ப்பாணம்' ); }
function kv_tagline()  { return kv_opt( 'kv_tagline',  'வார்த்தைகளில் வாழுங்கள்' ); }

// ─────────────────────────────────────────────  TAMIL SEO

add_action( 'wp_head', function() {
    $url = esc_url( is_singular() ? get_permalink() : home_url( '/' ) );
    echo '<link rel="alternate" hreflang="ta" href="' . $url . '" />' . "\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( home_url( '/' ) ) . '" />' . "\n";
} );

function kv_transliterate( $str ) {
    $map = [
        // Vowel signs (must come before consonants to avoid partial matches)
        "\u{0BBE}" => 'aa', "\u{0BBF}" => 'i',  "\u{0BC0}" => 'ii',
        "\u{0BC1}" => 'u',  "\u{0BC2}" => 'uu',
        "\u{0BC6}" => 'e',  "\u{0BC7}" => 'ee', "\u{0BC8}" => 'ai',
        "\u{0BCA}" => 'o',  "\u{0BCB}" => 'oo', "\u{0BCC}" => 'au',
        "\u{0BCD}" => '',   // pulli — removes inherent vowel
        // Independent vowels
        'அ' => 'a',  'ஆ' => 'aa', 'இ' => 'i',  'ஈ' => 'ii',
        'உ' => 'u',  'ஊ' => 'uu', 'எ' => 'e',  'ஏ' => 'ee',
        'ஐ' => 'ai', 'ஒ' => 'o',  'ஓ' => 'oo', 'ஔ' => 'au',
        // Consonants
        'க' => 'k',  'ங' => 'ng', 'ச' => 'ch', 'ஞ' => 'nj',
        'ட' => 't',  'ண' => 'n',  'த' => 'th', 'ந' => 'n',
        'ப' => 'p',  'ம' => 'm',  'ய' => 'y',  'ர' => 'r',
        'ல' => 'l',  'வ' => 'v',  'ழ' => 'zh', 'ள' => 'll',
        'ற' => 'rr', 'ன' => 'n',
        // Grantha
        'ஜ' => 'j',  'ஷ' => 'sh', 'ஸ' => 's',  'ஹ' => 'h',
        'ஶ' => 'sh', 'ஃ' => 'ah',
    ];
    return strtr( $str, $map );
}

add_filter( 'wp_insert_post_data', function( $data ) {
    if ( $data['post_type'] !== 'kavithai_gedicht' ) return $data;
    if ( ! empty( $data['post_name'] ) && ! preg_match( '/\p{Tamil}/u', $data['post_name'] ) ) return $data;
    $roman = kv_transliterate( $data['post_title'] );
    if ( $roman !== $data['post_title'] ) {
        $data['post_name'] = sanitize_title( $roman );
    }
    return $data;
} );
function kv_email()    { return kv_opt( 'kv_email',    '' ); }

function kv_portrait_url( $size = 'poet-portrait' ) {
    $id = (int) get_theme_mod( 'kv_portrait_id', 0 );
    if ( ! $id ) return '';
    $src = wp_get_attachment_image_src( $id, $size );
    return $src ? $src[0] : '';
}

function kv_hero_verse() {
    $v = kv_opt( 'kv_hero_verse', '' );
    if ( ! $v ) return "நான் விட்டுவந்த மண் —\nஅதன் நிறம் இன்னும் என் கைகளில்.";
    return esc_html( $v );
}
function kv_bio_text() {
    $v = kv_opt( 'kv_bio_text', '' );
    if ( ! $v ) return '<p>இலங்கையின் மண்ணில் பிறந்து, தமிழ் மொழியின் அழகோடு வளர்ந்தார்.</p>';
    return wpautop( esc_html( $v ) );
}
function kv_diaspora_quote() {
    $v = kv_opt( 'kv_diaspora_quote', '' );
    if ( ! $v ) return "இரண்டு மண்கள் என்னை வளர்த்தன —\nஒன்று என்னைப் பெற்றது,\nமற்றொன்று என்னை வளர்த்தது.";
    return nl2br( esc_html( $v ) );
}

function kv_book_css( $post_id ) {
    $raw = get_post_meta( $post_id, '_kv_color', true );
    $hex = sanitize_hex_color( $raw ) ?: '#8B2020';
    list($r,$g,$b) = sscanf($hex,'#%02x%02x%02x');
    $dark = sprintf('#%02x%02x%02x', max(0,$r-50), max(0,$g-50), max(0,$b-50));
    $mid  = sprintf('#%02x%02x%02x', max(0,$r-25), max(0,$g-25), max(0,$b-25));
    return "background:linear-gradient(160deg,{$mid},{$hex},{$dark});";
}

// ─────────────────────────────────────────────  REACTION FORM (AJAX)
add_action('wp_ajax_nopriv_kv_reaction','kv_save_reaction');
add_action('wp_ajax_kv_reaction',       'kv_save_reaction');
function kv_save_reaction() {
    check_ajax_referer( 'kv_react_nonce', 'nonce' );

    // Honeypot — bots fill all fields including hidden ones.
    if ( ! empty( $_POST['kv_hp'] ) ) {
        wp_send_json_success(); // pretend it worked, silently drop.
        return;
    }

    // Per-IP rate limit — max 3 reactions per hour.
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
    $key = 'kv_react_' . md5( $ip );
    $cnt = (int) get_transient( $key );
    if ( $cnt >= 3 ) {
        wp_send_json_error( 'அதிக கருத்துகள். ஒரு சில மணி நேரத்தில் முயற்சிக்கவும்.' );
    }
    set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );

    $post_id = absint( $_POST['post_id'] ?? 0 );
    $name    = mb_substr( sanitize_text_field( wp_unslash( $_POST['react_name'] ?? '' ) ), 0, 80 );
    $msg     = mb_substr( sanitize_textarea_field( wp_unslash( $_POST['react_msg'] ?? '' ) ), 0, 800 );

    if ( ! $post_id || ! $name || ! $msg ) {
        wp_send_json_error( 'தகவல் குறைவாக உள்ளது' );
    }

    // Only accept reactions on actual poems.
    if ( get_post_type( $post_id ) !== 'kavithai_gedicht' ) {
        wp_send_json_error( 'இல்லாத கவிதை' );
    }

    $reactions = get_post_meta( $post_id, '_kv_reactions', true ) ?: [];

    // Cap stored reactions per post (keep newest 200) so a single page
    // can't accumulate unbounded array storage.
    if ( count( $reactions ) >= 200 ) {
        $reactions = array_slice( $reactions, -199 );
    }

    $reactions[] = [
        'name' => $name,
        'msg'  => $msg,
        'date' => current_time( 'd.m.Y H:i' ),
        'ip'   => substr( md5( $ip ), 0, 8 ), // hashed IP for moderation reference
    ];
    update_post_meta( $post_id, '_kv_reactions', $reactions );

    $email = kv_email();
    if ( $email ) {
        $title = get_the_title( $post_id );
        wp_mail(
            $email,
            "கவிதைக்கு கருத்து: $title",
            "$name எழுதினார்:\n\n$msg\n\nகவிதை: " . get_permalink( $post_id )
        );
    }
    wp_send_json_success();
}

// Disable comments on poems (we use reactions instead)
add_action('init', function() {
    remove_post_type_support('kavithai_gedicht','comments');
    remove_post_type_support('kavithai_gedicht','trackbacks');
});

// ─────────────────────────────────────────────  VIEW COUNTER
add_action('template_redirect', function() {
    if (!is_singular('kavithai_gedicht') || is_preview()) return;
    $id = get_the_ID();
    $current = (int) get_post_meta($id,'_kv_views',true);
    update_post_meta($id,'_kv_views',$current+1);
    update_option('kv_total_views', (int)get_option('kv_total_views',0) + 1);
});

// ─────────────────────────────────────────────  RANDOM POEM
add_action( 'wp_ajax_nopriv_kv_random', 'kv_random_poem' );
add_action( 'wp_ajax_kv_random',        'kv_random_poem' );
function kv_random_poem() {
    $q = new WP_Query([
        'post_type' => 'kavithai_gedicht',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'publish',
    ]);
    if ($q->have_posts()) { $q->the_post(); wp_send_json_success(['url'=>get_permalink()]); }
    wp_send_json_error();
}

// Register page templates
add_filter('theme_page_templates', function($templates) {
    $templates['page-kalavarisai.php']  = 'காலவரிசை';
    $templates['page-ennai-parri.php']  = 'என்னை பற்றி';
    $templates['page-thodarbu.php']     = 'தொடர்பு';
    $templates['page-nulgal.php']       = 'நூல்கள்';
    return $templates;
});

add_action( 'after_switch_theme', 'flush_rewrite_rules' );

// ─────────────────────────────────────────────  LOAD MODULES
require_once get_template_directory() . '/inc/seal.php';              // Seal / relief / asterism / bilingual eyebrow helpers
require_once get_template_directory() . '/inc/admin-dashboard.php';   // Dashboard widgets
require_once get_template_directory() . '/inc/admin-features.php';    // Autosave, schedule, search, bulk, trash, proof, upload
require_once get_template_directory() . '/inc/settings-page.php';     // 'என் தளம்' settings page
require_once get_template_directory() . '/inc/importer.php';          // Word import
require_once get_template_directory() . '/inc/pdf-export.php';        // PDF book export
require_once get_template_directory() . '/inc/admin-style.php';       // Admin CSS for everything above

// ─────────────────────────────────────────────  JSON-LD STRUCTURED DATA
add_action( 'wp_head', function() {
    $schema = [
        '@context'   => 'https://schema.org',
        '@type'      => 'Person',
        'name'       => kv_name(),
        'url'        => home_url( '/' ),
        'jobTitle'   => 'கவிஞர்',
        'inLanguage' => 'ta',
        'address'    => [
            '@type'           => 'PostalAddress',
            'addressLocality' => kv_location(),
        ],
    ];
    $email = kv_email();
    if ( $email ) $schema['email'] = $email;
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . PHP_EOL;
}, 5 );
