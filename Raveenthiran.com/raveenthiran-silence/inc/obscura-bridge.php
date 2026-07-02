<?php
/**
 * Obscura bridge — Silence reads existing Obscura content natively.
 *
 * Non-destructive: nothing in the database is converted. When active,
 * Silence registers Obscura's post type names (nr_project, nr_journal,
 * taxonomy nr_project_cat) under its own /work and /journal slugs and
 * the meta helpers fall back to Obscura's field keys (project_client,
 * project_year, project_location, project_gallery, featured_on_homepage).
 * Switch back to Obscura at any time — the content is exactly as it was.
 *
 * Modes (Appearance → Silence → § Content):
 *   auto (default) — bridge turns on when Obscura projects exist and
 *                    no Silence projects have been created yet
 *   1              — always read Obscura content
 *   0              — always use Silence's own sl_* content
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function sl_bridge_active() {
	static $on = null;
	if ( $on !== null ) return $on;
	$mode = sl_opt( 'sl_bridge', 'auto' );
	if ( $mode === '1' ) return $on = true;
	if ( $mode === '0' ) return $on = false;
	global $wpdb;
	$has_nr = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'nr_project' AND post_status NOT IN ('trash','auto-draft')" );
	$has_sl = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'sl_project' AND post_status NOT IN ('trash','auto-draft')" );
	return $on = ( $has_nr > 0 && $has_sl === 0 );
}

/** Post type / taxonomy names, bridge-aware. Use everywhere instead of literals. */
function sl_pt()  { return sl_bridge_active() ? 'nr_project' : 'sl_project'; }
function sl_jt()  { return sl_bridge_active() ? 'nr_journal' : 'sl_journal'; }
function sl_tax() { return sl_bridge_active() ? 'nr_project_cat' : 'sl_project_cat'; }

/** Meta key for the "featured on homepage" flag. */
function sl_featured_key() { return sl_bridge_active() ? 'featured_on_homepage' : '_sl_featured'; }

/* Bridged types keep Obscura's template-hierarchy names — route them
   to Silence's template files. */
add_filter( 'template_include', function ( $tpl ) {
	if ( ! sl_bridge_active() ) return $tpl;
	$map = '';
	if ( is_singular( 'nr_project' ) )                $map = 'single-sl_project.php';
	elseif ( is_post_type_archive( 'nr_project' ) )   $map = 'archive-sl_project.php';
	elseif ( is_tax( 'nr_project_cat' ) )             $map = 'taxonomy-sl_project_cat.php';
	elseif ( is_singular( 'nr_journal' ) )            $map = 'single-sl_journal.php';
	elseif ( is_post_type_archive( 'nr_journal' ) )   $map = 'archive-sl_journal.php';
	if ( $map && ( $t = locate_template( $map ) ) ) return $t;
	return $tpl;
} );

/* Changing the mode moves rewrite slugs — flush on save. */
add_action( 'update_option_sl_bridge', function () { flush_rewrite_rules(); } );
