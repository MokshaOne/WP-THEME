<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', 'nr_register_feature_acf_fields' );

function nr_register_feature_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    /* The "Pricing & Quote" options page (nr-site-settings) now holds ONLY the
       Quote Generator + License Calculator (below). The old Hero / About / CTA /
       FAQ / Availability groups were unused (0 reads — content lives in Theme
       Settings → Obscura), so they were removed in v4.65.0. */

    /* =========================================================
       TESTIMONIALS CPT
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_nr_testimonial',
        'title'    => 'Testimonial Details',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_testimonial' ] ] ],
        'fields'   => [
            [ 'key' => 'field_t_client_role',    'label' => 'Role / Company',          'name' => 'client_role',      'type' => 'text' ],
            [ 'key' => 'field_t_client_avatar',  'label' => 'Avatar',                  'name' => 'client_avatar',    'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ],
            [ 'key' => 'field_t_project_link',   'label' => 'Linked Project',          'name' => 'project_link',     'type' => 'post_object', 'post_type' => [ 'nr_project' ], 'return_format' => 'id', 'allow_null' => 1 ],
            [ 'key' => 'field_t_rating',         'label' => 'Rating (1–5)',            'name' => 'rating',           'type' => 'number', 'min' => 1, 'max' => 5, 'default_value' => 5 ],
            [ 'key' => 'field_t_homepage',       'label' => 'Show on Homepage',        'name' => 'show_on_homepage', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1 ],
        ],
    ] );


    /* =========================================================
       PROJECT VIDEO PREVIEW
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_nr_project_video',
        'title'    => 'Project Video Preview',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
        'fields'   => [
            [ 'key' => 'field_project_video_preview', 'label' => 'Video Preview (.mp4)', 'name' => 'project_video_preview', 'type' => 'file',
              'return_format' => 'array', 'library' => 'all',
              'instructions' => 'Short .mp4 (3–8 sec, max 5MB). Plays on hover over the portfolio card.' ],
        ],
    ] );

    /* =========================================================
       PROJECT DETAILS + GALLERY — every nr_project gets these
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_nr_project_details',
        'title'    => 'Project Details',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
        'menu_order' => -10,
        'position'   => 'normal',
        'fields'   => [
            [ 'key' => 'field_project_gallery', 'label' => 'Gallery', 'name' => 'project_gallery', 'type' => 'gallery',
              'return_format' => 'array', 'library' => 'all', 'preview_size' => 'medium', 'insert' => 'append',
              'instructions' => 'Rendered as a horizontal scroll rail on the single-project page. Each image keeps its own aspect (4:5 portrait / 5:4 landscape).' ],
            [ 'key' => 'field_project_cover', 'label' => 'Cover Image', 'name' => 'project_cover', 'type' => 'image',
              'return_format' => 'array', 'library' => 'all', 'preview_size' => 'medium',
              'instructions' => 'Optional. Used in portfolio grid + schema. Falls back to the featured image.' ],
            [ 'key' => 'field_project_number',   'label' => 'Project Number',  'name' => 'project_number',   'type' => 'number', 'instructions' => 'Two-digit display number (01, 02, …). Falls back to menu_order.' ],
            [ 'key' => 'field_project_year',     'label' => 'Year',            'name' => 'project_year',     'type' => 'text',   'instructions' => 'e.g. 2024' ],
            [ 'key' => 'field_project_client',   'label' => 'Client',          'name' => 'project_client',   'type' => 'text' ],
            [ 'key' => 'field_project_location', 'label' => 'Location',        'name' => 'project_location', 'type' => 'text',   'instructions' => 'City / country. Falls back to the studio base location.' ],
            [ 'key' => 'field_project_category', 'label' => 'Category (slug)', 'name' => 'project_category', 'type' => 'text',
              'instructions' => 'Optional override slug used by the portfolio filter. Leave blank to use the Projects taxonomy term instead.' ],
            [ 'key' => 'field_project_frames',   'label' => 'Frames',          'name' => 'project_frames',   'type' => 'text',   'instructions' => 'Number of final selects (e.g. 24).' ],
            [ 'key' => 'field_project_process',  'label' => 'Process / behind-the-scenes', 'name' => 'project_process', 'type' => 'textarea', 'rows' => 4,
              'instructions' => 'Optional. A short note on how the work was made — shown as a "Behind the frame" section on the project page. Juries love this.' ],
            [ 'key' => 'field_project_gear',     'label' => 'Setup / gear',    'name' => 'project_gear',     'type' => 'text',
              'instructions' => 'Optional. Camera, lens, lighting — e.g. "Leica M6 · 35mm Summicron · available light". Shown in the project meta.' ],
            [ 'key' => 'field_featured_on_homepage', 'label' => 'Featured on Homepage', 'name' => 'featured_on_homepage', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0,
              'instructions' => 'Show this project in the fullscreen hero slider on the home page.' ],
        ],
    ] );

    /* =========================================================
       QUOTE GENERATOR (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_quote_config',
        'title'    => 'Quote Generator Configuration',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [
                'key' => 'field_quote_base_prices', 'label' => 'Base Prices by Shooting Type', 'name' => 'quote_base_prices',
                'type' => 'repeater', 'layout' => 'table',
                'sub_fields' => [
                    [ 'key' => 'field_qbp_type',  'label' => 'Type',       'name' => 'type',       'type' => 'text' ],
                    [ 'key' => 'field_qbp_price',  'label' => 'Base / setup fee (€)', 'name' => 'base_price',  'type' => 'number', 'instructions' => 'Legacy estimate popover uses this. The booking picker bills hourly (below).' ],
                    [ 'key' => 'field_qbp_hours',  'label' => 'Min hours',  'name' => 'hours',       'type' => 'number', 'default_value' => 2, 'instructions' => 'Starting / minimum hours in the booking picker.' ],
                    [ 'key' => 'field_qbp_rate',   'label' => 'Hourly rate (€/h)', 'name' => 'hourly_rate', 'type' => 'number', 'instructions' => 'Drives the booking price. Leave blank to derive from Base ÷ Min hours.' ],
                    [ 'key' => 'field_qbp_max',    'label' => 'Max hours',  'name' => 'max_hours',   'type' => 'number', 'instructions' => 'Booking picker ceiling. Blank = 10.' ],
                ],
            ],
            [
                'key' => 'field_quote_extras', 'label' => 'Extras', 'name' => 'quote_extras',
                'type' => 'repeater', 'layout' => 'table',
                'sub_fields' => [
                    [ 'key' => 'field_qe_label',  'label' => 'Name',       'name' => 'label',  'type' => 'text' ],
                    [ 'key' => 'field_qe_price',  'label' => 'Add-on (€)', 'name' => 'price',  'type' => 'number' ],
                ],
            ],
            [ 'key' => 'field_quote_travel_per_km', 'label' => 'Travel cost per km (€)', 'name' => 'travel_per_km', 'type' => 'number', 'default_value' => 0.42 ],
        ],
    ] );

    /* =========================================================
       LICENSE CALCULATOR (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_license_config',
        'title'    => 'License Calculator',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_lic_base', 'label' => 'Base License Price (€)', 'name' => 'license_base_price', 'type' => 'number', 'default_value' => 150 ],
        ],
    ] );

    /* Instagram Basic Display API was deprecated by Meta (Dec 2024), so the
       auto-feed fields were removed. Use a manually-curated grid instead. */

    /* =========================================================
       LOCAL SEO (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_local_seo',
        'title'    => 'Local SEO / Schema',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-business-seo' ] ] ],
        'fields'   => [
            [ 'key' => 'field_seo_phone',       'label' => 'Phone',         'name' => 'seo_phone',       'type' => 'text' ],
            [ 'key' => 'field_seo_street',      'label' => 'Street',        'name' => 'seo_street',      'type' => 'text' ],
            [ 'key' => 'field_seo_postal_code', 'label' => 'Postal Code',   'name' => 'seo_postal_code', 'type' => 'text', 'default_value' => '1010' ],
            [ 'key' => 'field_seo_city',        'label' => 'City',          'name' => 'seo_city',        'type' => 'text', 'default_value' => 'Wien' ],
            [ 'key' => 'field_seo_lat',         'label' => 'GPS Latitude',  'name' => 'seo_lat',         'type' => 'text', 'default_value' => '48.2082' ],
            [ 'key' => 'field_seo_lng',         'label' => 'GPS Longitude', 'name' => 'seo_lng',         'type' => 'text', 'default_value' => '16.3738' ],
            [
                'key' => 'field_seo_opening_hours', 'label' => 'Availability / Opening Hours', 'name' => 'seo_opening_hours',
                'type' => 'repeater', 'layout' => 'table',
                'sub_fields' => [
                    [ 'key' => 'field_soh_days',  'label' => 'Days',  'name' => 'days',  'type' => 'text', 'placeholder' => 'Mo–Fr' ],
                    [ 'key' => 'field_soh_hours', 'label' => 'Hours', 'name' => 'hours', 'type' => 'text', 'placeholder' => '09:00–18:00' ],
                ],
            ],
        ],
    ] );

    /* Newsletter/Brevo, Awards and Press groups were removed in v4.65.0:
       the newsletter feature was retired, and Awards/Press are managed in
       Theme Settings → Obscura (the About page reads those). */
}
