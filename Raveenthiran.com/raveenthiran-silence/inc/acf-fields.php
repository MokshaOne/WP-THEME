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
} );
