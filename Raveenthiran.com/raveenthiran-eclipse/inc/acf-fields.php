<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', 'nr_register_feature_acf_fields' );

function nr_register_feature_acf_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    /* =========================================================
       HERO — all fields for parts/hero.php
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_hero_settings',
        'title'    => 'Hero Section',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_hero_headline',       'label' => 'Hero Headline (H1)',       'name' => 'hero_headline',       'type' => 'text',
              'instructions' => 'Main title. Use <em>word</em> for italic accent. HTML br tag allowed for line breaks.' ],
            [ 'key' => 'field_hero_overline',       'label' => 'Hero Overline',            'name' => 'hero_overline',       'type' => 'text' ],
            [ 'key' => 'field_hero_subline',        'label' => 'Hero Subline / Tagline',   'name' => 'hero_subline',        'type' => 'text' ],
            [ 'key' => 'field_hero_cta_label',      'label' => 'Hero CTA Button Label',    'name' => 'hero_cta_label',      'type' => 'text' ],
            [ 'key' => 'field_hero_cta_url',        'label' => 'Hero CTA Button URL',      'name' => 'hero_cta_url',        'type' => 'url' ],
            [ 'key' => 'field_hero_available_text', 'label' => '"Currently Available" Text','name' => 'hero_available_text', 'type' => 'text' ],
            [ 'key' => 'field_stat_projects',       'label' => 'Stat — Projects',          'name' => 'stat_projects',       'type' => 'text' ],
            [ 'key' => 'field_stat_countries',      'label' => 'Stat — Countries',         'name' => 'stat_countries',      'type' => 'text' ],
            [ 'key' => 'field_stat_since',          'label' => 'Stat — Since (Year)',      'name' => 'stat_since',          'type' => 'text' ],
            [
                'key'        => 'field_hero_images',
                'label'      => 'Hero Slide Images',
                'name'       => 'hero_images',
                'type'       => 'repeater',
                'layout'     => 'block',
                'instructions' => 'Add images for the hero carousel. First image loads eagerly.',
                'sub_fields' => [
                    [ 'key' => 'field_hero_slide_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ],
                ],
            ],
        ],
    ] );

    /* =========================================================
       ABOUT — extra fields beyond functions.php base group
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_about_extended',
        'title'    => 'About — Extended',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_about_statement', 'label' => 'About Statement / Pull Quote', 'name' => 'about_statement', 'type' => 'text',
              'instructions' => 'Short italic quote shown in the about section (optional).' ],
            [ 'key' => 'field_about_phone',     'label' => 'Phone Number',                 'name' => 'phone',           'type' => 'text' ],
        ],
    ] );

    /* =========================================================
       CTA BLOCK — fields for parts/contact-cta.php
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_cta_block',
        'title'    => 'CTA Block (Bottom Section)',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_cta_overline',          'label' => 'CTA Overline',        'name' => 'cta_overline',          'type' => 'text' ],
            [ 'key' => 'field_cta_headline',          'label' => 'CTA Headline',         'name' => 'cta_headline',          'type' => 'text',
              'instructions' => 'Use <em>word</em> for italic/accent.' ],
            [ 'key' => 'field_cta_subline',           'label' => 'CTA Subline',          'name' => 'cta_subline',           'type' => 'text' ],
            [ 'key' => 'field_cta_btn_label',         'label' => 'CTA Primary Button',   'name' => 'cta_btn_label',         'type' => 'text' ],
            [ 'key' => 'field_cta_btn_url',           'label' => 'CTA Primary URL',      'name' => 'cta_btn_url',           'type' => 'url' ],
            [ 'key' => 'field_cta_link_text',         'label' => 'CTA Secondary Link',   'name' => 'cta_link_text',         'type' => 'text' ],
            [ 'key' => 'field_cta_background_image',  'label' => 'CTA Background Image', 'name' => 'cta_background_image',  'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ],
        ],
    ] );

    /* =========================================================
       FAQ — repeater for templates/page-faq.php
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_faq',
        'title'    => 'FAQ Items',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [
                'key'        => 'field_faq_items',
                'label'      => 'FAQ Items',
                'name'       => 'faq_items',
                'type'       => 'repeater',
                'layout'     => 'block',
                'sub_fields' => [
                    [ 'key' => 'field_faq_question', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ],
                    [ 'key' => 'field_faq_answer',   'label' => 'Answer',   'name' => 'answer',   'type' => 'textarea', 'rows' => 3 ],
                ],
            ],
        ],
    ] );

    /* =========================================================
       AVAILABILITY — for header badge
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_availability',
        'title'    => 'Availability',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_nr_available_for_work', 'label' => 'Available for Work', 'name' => 'nr_available_for_work', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1 ],
        ],
    ] );

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
                    [ 'key' => 'field_qbp_price',  'label' => 'Price (€)', 'name' => 'base_price',  'type' => 'number' ],
                    [ 'key' => 'field_qbp_hours',  'label' => 'Hours',     'name' => 'hours',       'type' => 'number' ],
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
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
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

    /* =========================================================
       NEWSLETTER / BREVO (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_newsletter_config',
        'title'    => 'Newsletter / Brevo',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [ 'key' => 'field_nl_api_key',    'label' => 'Brevo API Key',     'name' => 'brevo_api_key',            'type' => 'password' ],
            [ 'key' => 'field_nl_list_id',    'label' => 'Brevo List ID',     'name' => 'brevo_list_id',            'type' => 'number' ],
            [ 'key' => 'field_nl_show_footer','label' => 'Show in Footer',    'name' => 'newsletter_show_footer',   'type' => 'true_false', 'default_value' => 1, 'ui' => 1 ],
        ],
    ] );

    /* =========================================================
       AWARDS (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_awards',
        'title'    => 'Awards & Recognition',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [
                'key' => 'field_awards_list', 'label' => 'Awards', 'name' => 'awards_list',
                'type' => 'repeater', 'layout' => 'table',
                'sub_fields' => [
                    [ 'key' => 'field_aw_year',  'label' => 'Year',         'name' => 'year',         'type' => 'text' ],
                    [ 'key' => 'field_aw_title', 'label' => 'Award',        'name' => 'award_title',  'type' => 'text' ],
                    [ 'key' => 'field_aw_org',   'label' => 'Organisation', 'name' => 'organisation', 'type' => 'text' ],
                    [ 'key' => 'field_aw_url',   'label' => 'Link',         'name' => 'award_url',    'type' => 'url' ],
                ],
            ],
        ],
    ] );

    /* =========================================================
       PRESS (Site Settings)
       ========================================================= */
    acf_add_local_field_group( [
        'key'      => 'group_press',
        'title'    => 'Press & Publications',
        'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
        'fields'   => [
            [
                'key' => 'field_press_list', 'label' => 'Publications', 'name' => 'press_list',
                'type' => 'repeater', 'layout' => 'table',
                'sub_fields' => [
                    [ 'key' => 'field_pr_medium', 'label' => 'Publication', 'name' => 'medium',   'type' => 'text' ],
                    [ 'key' => 'field_pr_year',   'label' => 'Year',        'name' => 'year',     'type' => 'text' ],
                    [ 'key' => 'field_pr_title',  'label' => 'Article',     'name' => 'pr_title', 'type' => 'text' ],
                    [ 'key' => 'field_pr_url',    'label' => 'Link',        'name' => 'pr_url',   'type' => 'url' ],
                    [ 'key' => 'field_pr_logo',   'label' => 'Logo',        'name' => 'pr_logo',  'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail' ],
                ],
            ],
        ],
    ] );
}
