<?php
/**
 * Catalogue Noir — Child theme functions
 *
 * Enqueues the parent stylesheet, then the child stylesheet on top.
 * Add any project-specific hooks, filters, or template overrides
 * below the enqueue block.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', 'nr_child_enqueue_styles', 20 );
function nr_child_enqueue_styles() {
	$parent = get_template_directory_uri() . '/style.css';
	$parent_ver = wp_get_theme( get_template() )->get( 'Version' );

	// Parent style.css is metadata-only (real CSS lives in assets/css/theme.css,
	// already enqueued by the parent's functions.php). We still register the
	// metadata file so this child can declare it as a dependency.
	wp_enqueue_style( 'nr-parent-style', $parent, [], $parent_ver );

	$child_ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_style(
		'nr-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[ 'nr-theme', 'nr-parent-style' ],
		$child_ver
	);
}

/* =============================================================
   Template overrides
   To override any parent template, copy it from
   wp-content/themes/raveenthiran-catalogue-noir/<file>.php into
   THIS directory at the same path. WordPress will use your child
   copy automatically. Common overrides:
     - page-about.php       (Studio page layout)
     - single-nr_project.php (project detail markup)
     - parts/booking-modal.php
   ============================================================= */

/* =============================================================
   Examples (uncomment to use):
   ============================================================= */

/* Filter: replace the default booking-modal heading globally */
// add_filter( 'gettext', function ( $translated, $original, $domain ) {
//     if ( $domain === 'raveenthiran' && $original === 'Book a' ) return 'Schedule a';
//     return $translated;
// }, 10, 3 );

/* Action: inject a project-wide CSS variable override on every page */
// add_action( 'wp_head', function () {
//     echo "<style>:root{--amber:#E83E2A}</style>\n";
// } );

/* Filter: add a custom body class for one specific project category */
// add_filter( 'body_class', function ( $classes ) {
//     if ( is_singular( 'nr_project' ) && has_term( 'editorial', 'nr_project_cat' ) ) {
//         $classes[] = 'nr-cat-editorial';
//     }
//     return $classes;
// } );
