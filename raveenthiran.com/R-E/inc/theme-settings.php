<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_theme_page(
        __('R-E Theme Settings', 'r-e'),
        __('Theme Settings', 'r-e'),
        'manage_options',
        're-settings',
        're_render_settings_page'
    );
});

add_action('admin_init', function () {
    $sections = [
        'branding'   => __('Branding', 'r-e'),
        'colors'     => __('Colors & Mode', 'r-e'),
        'contact'    => __('Contact', 'r-e'),
        'social'     => __('Social Links', 'r-e'),
        'effects'    => __('Visual Effects & 3D', 'r-e'),
        'hero'       => __('Hero Slider', 'r-e'),
        'copy'       => __('Page Copy', 'r-e'),
        'booking'    => __('Booking', 'r-e'),
        'seo'        => __('SEO & Verification', 'r-e'),
    ];

    foreach ($sections as $id => $title) {
        add_settings_section('re_' . $id, $title, '__return_false', 're-settings');
    }

    $fields = [
        // Branding
        ['re_logo_text', __('Logo Text', 'r-e'), 'branding', 'Raveenthiran'],
        ['re_logo_sub', __('Logo Subline', 'r-e'), 'branding', 'Photography'],
        ['re_accent', __('Accent Color', 'r-e'), 'branding', '#F2A03D'],
        ['re_book_label', __('Book Button Label', 'r-e'), 'branding', 'Book a Session'],

        // Colors
        ['re_color_bg', __('Background', 'r-e'), 'colors', '#0B0C10'],
        ['re_color_ink', __('Text Color', 'r-e'), 'colors', '#F2EFE9'],
        ['re_color_mode', __('Color Mode', 'r-e'), 'colors', 'dark'],

        // Contact
        ['re_email', __('Email', 'r-e'), 'contact', ''],
        ['re_phone', __('Phone', 'r-e'), 'contact', ''],
        ['re_address', __('Studio Address', 'r-e'), 'contact', ''],
        ['re_city', __('City', 'r-e'), 'contact', ''],
        ['re_availability', __('Availability Text', 'r-e'), 'contact', 'Available · 2026'],

        // Social
        ['re_instagram', __('Instagram URL', 'r-e'), 'social', ''],
        ['re_behance', __('Behance URL', 'r-e'), 'social', ''],
        ['re_vimeo', __('Vimeo URL', 'r-e'), 'social', ''],
        ['re_linkedin', __('LinkedIn URL', 'r-e'), 'social', ''],

        // Effects
        ['re_fx_cursor', __('Custom Cursor', 'r-e'), 'effects', '1'],
        ['re_fx_grain', __('Film Grain', 'r-e'), 'effects', '1'],
        ['re_fx_wipe', __('Page Wipe', 'r-e'), 'effects', '1'],
        ['re_fx_ken', __('Ken Burns Hero', 'r-e'), 'effects', '1'],
        ['re_fx_3d', __('3D Card Tilt', 'r-e'), 'effects', '1'],
        ['re_fx_particles', __('WebGL Particles', 'r-e'), 'effects', '1'],
        ['re_fx_parallax', __('Parallax Depth', 'r-e'), 'effects', '1'],
        ['re_fx_magnetic', __('Magnetic Buttons', 'r-e'), 'effects', '1'],
        ['re_fx_morph', __('Text Morphing', 'r-e'), 'effects', '0'],
        ['re_fx_glitch', __('Glitch Effect', 'r-e'), 'effects', '0'],

        // Hero
        ['re_hero_auto', __('Auto-play', 'r-e'), 'hero', '1'],
        ['re_hero_interval', __('Interval (ms)', 'r-e'), 'hero', '9000'],
        ['re_hero_max', __('Max Slides', 'r-e'), 'hero', '6'],
        ['re_hero_3d_transition', __('3D Slide Transition', 'r-e'), 'hero', '1'],

        // Copy
        ['re_hero_eyebrow', __('Hero Eyebrow', 'r-e'), 'copy', 'Selected Work'],
        ['re_portfolio_title', __('Portfolio Title', 'r-e'), 'copy', 'Portfolio'],
        ['re_about_title', __('About Title', 'r-e'), 'copy', 'About'],
        ['re_contact_title', __('Contact Title', 'r-e'), 'copy', 'Get in Touch'],
        ['re_faq_title', __('FAQ Title', 'r-e'), 'copy', 'FAQ'],
        ['re_footer_text', __('Footer Text', 'r-e'), 'copy', ''],
        ['re_awards', __('Awards (year · title · org · url)', 'r-e'), 'copy', ''],
        ['re_press', __('Press (year · title · org · url)', 'r-e'), 'copy', ''],
        ['re_clients_marquee', __('Clients (one per line)', 'r-e'), 'copy', ''],

        // Booking
        ['re_booking_url', __('Booking URL', 'r-e'), 'booking', ''],
        ['re_latepoint_shortcode', __('LatePoint Shortcode', 'r-e'), 'booking', ''],

        // SEO
        ['re_twitter', __('Twitter Handle', 'r-e'), 'seo', ''],
        ['re_google_verify', __('Google Verification', 'r-e'), 'seo', ''],
        ['re_tracking_script', __('Custom Tracking', 'r-e'), 'seo', ''],
    ];

    foreach ($fields as [$id, $label, $section, $default]) {
        register_setting('re_settings', $id, ['default' => $default]);
        add_settings_field($id, $label, function () use ($id, $default) {
            $val = get_option($id, $default);
            if (in_array($id, ['re_fx_cursor', 're_fx_grain', 're_fx_wipe', 're_fx_ken', 're_fx_3d', 're_fx_particles', 're_fx_parallax', 're_fx_magnetic', 're_fx_morph', 're_fx_glitch', 're_hero_auto', 're_hero_3d_transition'])) {
                echo '<input type="checkbox" name="' . esc_attr($id) . '" value="1" ' . checked($val, '1', false) . '>';
            } elseif (in_array($id, ['re_awards', 're_press', 're_clients_marquee', 're_tracking_script', 're_footer_text'])) {
                echo '<textarea name="' . esc_attr($id) . '" rows="5" class="large-text">' . esc_textarea($val) . '</textarea>';
            } elseif ($id === 're_color_mode') {
                echo '<select name="' . esc_attr($id) . '">';
                foreach (['dark' => 'Dark', 'light' => 'Light', 'system' => 'System'] as $k => $v) {
                    echo '<option value="' . $k . '" ' . selected($val, $k, false) . '>' . $v . '</option>';
                }
                echo '</select>';
            } elseif (strpos($id, 'color') !== false || $id === 're_accent') {
                echo '<input type="color" name="' . esc_attr($id) . '" value="' . esc_attr($val) . '">';
            } else {
                echo '<input type="text" name="' . esc_attr($id) . '" value="' . esc_attr($val) . '" class="regular-text">';
            }
        }, 're-settings', 're_' . $section);
    }
});

function re_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('R-E Theme Settings', 'r-e'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('re_settings');
            do_settings_sections('re-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
