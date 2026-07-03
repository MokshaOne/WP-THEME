<?php
/**
 * Silence — ACF Pro field group (optional).
 * The exact same "Project Details" group Obscura registers, so a site
 * with ACF Pro gets the identical editing UI in both themes and the
 * data lives under the identical keys (project_gallery, project_year,
 * project_client, project_location, featured_on_homepage, …).
 * Without ACF, the native meta boxes in inc/gallery-meta.php take over.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	acf_add_local_field_group( [
		'key'      => 'group_nr_project_details',
		'title'    => 'Project Details',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
		'menu_order' => -10,
		'position'   => 'normal',
		'fields'   => [
			[ 'key' => 'field_project_gallery', 'label' => 'Gallery', 'name' => 'project_gallery', 'type' => 'gallery',
			  'return_format' => 'array', 'library' => 'all', 'preview_size' => 'medium', 'insert' => 'append',
			  'instructions' => 'Rendered as the horizontal plate rail on the single-project page. Images and videos keep their own aspect.' ],
			[ 'key' => 'field_project_cover', 'label' => 'Cover Image', 'name' => 'project_cover', 'type' => 'image',
			  'return_format' => 'array', 'library' => 'all', 'preview_size' => 'medium',
			  'instructions' => 'Optional. Used in schema. Falls back to the featured image.' ],
			[ 'key' => 'field_project_number',   'label' => 'Project Number',  'name' => 'project_number',   'type' => 'number', 'instructions' => 'Two-digit display number (01, 02, …). Falls back to menu_order.' ],
			[ 'key' => 'field_project_year',     'label' => 'Year',            'name' => 'project_year',     'type' => 'text',   'instructions' => 'e.g. 2024' ],
			[ 'key' => 'field_project_client',   'label' => 'Client',          'name' => 'project_client',   'type' => 'text' ],
			[ 'key' => 'field_project_location', 'label' => 'Location',        'name' => 'project_location', 'type' => 'text',   'instructions' => 'City / country. Falls back to the studio base location.' ],
			[ 'key' => 'field_project_category', 'label' => 'Category (slug)', 'name' => 'project_category', 'type' => 'text',
			  'instructions' => 'Optional override slug used by filters. Leave blank to use the Projects taxonomy term instead.' ],
			[ 'key' => 'field_project_frames',   'label' => 'Frames',          'name' => 'project_frames',   'type' => 'text',   'instructions' => 'Number of final selects (e.g. 24).' ],
			[ 'key' => 'field_featured_on_homepage', 'label' => 'Featured on Homepage', 'name' => 'featured_on_homepage', 'type' => 'true_false', 'ui' => 1, 'default_value' => 0,
			  'instructions' => 'Show this project in the fullscreen hero slideshow on the home page.' ],
		],
	] );

	/* Pricing & Quote — identical keys to Obscura, so the calculator
	   config is shared between the two themes. Targets the same
	   "Pricing & Quote" options page inc/quote.php registers. */
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
					[ 'key' => 'field_qbp_price', 'label' => 'Price (€)',  'name' => 'base_price', 'type' => 'number' ],
					[ 'key' => 'field_qbp_hours', 'label' => 'Hours',      'name' => 'hours',      'type' => 'number' ],
				],
			],
			[
				'key' => 'field_quote_extras', 'label' => 'Extras', 'name' => 'quote_extras',
				'type' => 'repeater', 'layout' => 'table',
				'sub_fields' => [
					[ 'key' => 'field_qe_label', 'label' => 'Name',       'name' => 'label', 'type' => 'text' ],
					[ 'key' => 'field_qe_price', 'label' => 'Add-on (€)', 'name' => 'price', 'type' => 'number' ],
				],
			],
			[ 'key' => 'field_quote_travel_per_km', 'label' => 'Travel cost per km (€)', 'name' => 'travel_per_km', 'type' => 'number', 'default_value' => 0.42 ],
		],
	] );

	acf_add_local_field_group( [
		'key'      => 'group_license_config',
		'title'    => 'License Calculator',
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-site-settings' ] ] ],
		'fields'   => [
			[ 'key' => 'field_lic_base', 'label' => 'Base License Price (€)', 'name' => 'license_base_price', 'type' => 'number', 'default_value' => 150 ],
		],
	] );

	/* FAQ — question/answer repeater on the Enquire page (same field
	   name quote.php reads: faq_items). */
	acf_add_local_field_group( [
		'key'      => 'group_faq_items',
		'title'    => 'FAQ',
		'location' => [ [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-enquire.php' ] ] ],
		'fields'   => [
			[
				'key' => 'field_faq_items', 'label' => 'Questions', 'name' => 'faq_items',
				'type' => 'repeater', 'layout' => 'block',
				'sub_fields' => [
					[ 'key' => 'field_faq_q', 'label' => 'Question', 'name' => 'question', 'type' => 'text' ],
					[ 'key' => 'field_faq_a', 'label' => 'Answer',   'name' => 'answer',   'type' => 'textarea', 'rows' => 3 ],
				],
			],
		],
	] );
} );
