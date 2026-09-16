<?php
/**
 * Plugin Name:       Direction Manual
 * Plugin URI:        https://github.com/MokshaOne/WP-THEME
 * Description:       Embeds the 108-volume photography Direction Manual anywhere on your site. Choose where it shows: a shortcode, a block, auto-append/replace a page, or a standalone URL.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            On1 Agency
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       direction-manual
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'DM_VERSION', '1.0.0' );
define( 'DM_FILE', __FILE__ );
define( 'DM_DIR', plugin_dir_path( __FILE__ ) );
define( 'DM_URL', plugin_dir_url( __FILE__ ) );
define( 'DM_ASSET', DM_DIR . 'assets/manual.html' );
define( 'DM_OPTION', 'dm_settings' );

/**
 * Default settings.
 */
function dm_defaults() {
	return array(
		'mode'         => 'shortcode', // shortcode | append | replace
		'page_id'      => 0,
		'height'       => '85vh',
		'enable_route' => 1,
		'route_slug'   => 'direction-manual',
	);
}

/**
 * Merged, sanitized settings.
 */
function dm_options() {
	$saved = get_option( DM_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, dm_defaults() );
}

/**
 * Public URL of the manual asset.
 */
function dm_asset_url() {
	return DM_URL . 'assets/manual.html';
}

/**
 * Sanitize a CSS length such as 85vh, 900px, 100%.
 */
function dm_sanitize_height( $value ) {
	$value = trim( (string) $value );
	if ( preg_match( '/^\d{1,5}(vh|px|%)$/', $value ) ) {
		return $value;
	}
	return '85vh';
}

/* ------------------------------------------------------------------ *
 *  Rendering
 * ------------------------------------------------------------------ */

/**
 * Render the embedded manual (isolated iframe).
 *
 * @param array $atts Optional overrides (height, title).
 * @return string
 */
function dm_render_embed( $atts = array() ) {
	$o = dm_options();

	$atts = shortcode_atts(
		array(
			'height' => $o['height'],
			'title'  => __( 'Photography Direction Manual', 'direction-manual' ),
		),
		is_array( $atts ) ? $atts : array(),
		'direction_manual'
	);

	$height = dm_sanitize_height( $atts['height'] );
	$src    = esc_url( dm_asset_url() );
	$title  = esc_attr( $atts['title'] );

	$html  = '<div class="dm-embed" style="position:relative;width:100%;margin:0 auto;">';
	$html .= '<iframe src="' . $src . '" title="' . $title . '" loading="lazy" ';
	$html .= 'style="display:block;width:100%;height:' . esc_attr( $height ) . ';border:0;" ';
	$html .= 'allow="fullscreen" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>';
	$html .= '<p style="margin:8px 0 0;font:400 12px/1.4 system-ui,sans-serif;opacity:.7;">';
	$html .= '<a href="' . $src . '" target="_blank" rel="noopener">' . esc_html__( 'Open the manual full screen', 'direction-manual' ) . '</a></p>';
	$html .= '</div>';

	return $html;
}

/**
 * Shortcode: [direction_manual height="85vh"]
 */
function dm_shortcode( $atts ) {
	return dm_render_embed( $atts );
}
add_shortcode( 'direction_manual', 'dm_shortcode' );

/**
 * Auto-append / replace the manual on a chosen page.
 */
function dm_filter_content( $content ) {
	if ( is_admin() ) {
		return $content;
	}
	$o = dm_options();
	if ( empty( $o['page_id'] ) || 'shortcode' === $o['mode'] ) {
		return $content;
	}
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( (int) get_the_ID() !== (int) $o['page_id'] ) {
		return $content;
	}
	$embed = dm_render_embed();
	if ( 'replace' === $o['mode'] ) {
		return $embed;
	}
	return $content . $embed;
}
add_filter( 'the_content', 'dm_filter_content' );

/* ------------------------------------------------------------------ *
 *  Gutenberg block (no build step; dynamic, rendered by PHP)
 * ------------------------------------------------------------------ */

function dm_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	wp_register_script(
		'dm-block',
		DM_URL . 'assets/block.js',
		array( 'wp-blocks', 'wp-element' ),
		DM_VERSION,
		true
	);
	register_block_type(
		'direction-manual/embed',
		array(
			'api_version'     => 2,
			'editor_script'   => 'dm-block',
			'render_callback' => 'dm_render_embed',
		)
	);
}
add_action( 'init', 'dm_register_block' );

/* ------------------------------------------------------------------ *
 *  Standalone URL, e.g. /direction-manual/
 * ------------------------------------------------------------------ */

function dm_add_rewrite() {
	$o = dm_options();
	if ( empty( $o['enable_route'] ) ) {
		return;
	}
	$slug = sanitize_title( $o['route_slug'] );
	if ( '' === $slug ) {
		$slug = 'direction-manual';
	}
	add_rewrite_rule( '^' . preg_quote( $slug, '#' ) . '/?$', 'index.php?dm_manual=1', 'top' );
}
add_action( 'init', 'dm_add_rewrite' );

function dm_query_vars( $vars ) {
	$vars[] = 'dm_manual';
	return $vars;
}
add_filter( 'query_vars', 'dm_query_vars' );

function dm_serve_standalone() {
	if ( ! get_query_var( 'dm_manual' ) ) {
		return;
	}
	if ( ! is_readable( DM_ASSET ) ) {
		status_header( 404 );
		wp_die( esc_html__( 'Direction Manual asset not found.', 'direction-manual' ) );
	}
	status_header( 200 );
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( DM_ASSET );
	exit;
}
add_action( 'template_redirect', 'dm_serve_standalone' );

/**
 * Pretty URL of the standalone page (or the raw asset when the route is off).
 */
function dm_standalone_url() {
	$o = dm_options();
	if ( ! empty( $o['enable_route'] ) ) {
		$slug = sanitize_title( $o['route_slug'] );
		if ( '' === $slug ) {
			$slug = 'direction-manual';
		}
		return home_url( '/' . $slug . '/' );
	}
	return dm_asset_url();
}

/* ------------------------------------------------------------------ *
 *  Settings screen
 * ------------------------------------------------------------------ */

function dm_admin_menu() {
	add_options_page(
		__( 'Direction Manual', 'direction-manual' ),
		__( 'Direction Manual', 'direction-manual' ),
		'manage_options',
		'direction-manual',
		'dm_settings_page'
	);
}
add_action( 'admin_menu', 'dm_admin_menu' );

function dm_register_settings() {
	register_setting(
		'dm_group',
		DM_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'dm_sanitize_settings',
			'default'           => dm_defaults(),
		)
	);
}
add_action( 'admin_init', 'dm_register_settings' );

function dm_sanitize_settings( $input ) {
	$out              = dm_defaults();
	$modes            = array( 'shortcode', 'append', 'replace' );
	$out['mode']      = ( isset( $input['mode'] ) && in_array( $input['mode'], $modes, true ) ) ? $input['mode'] : 'shortcode';
	$out['page_id']   = isset( $input['page_id'] ) ? absint( $input['page_id'] ) : 0;
	$out['height']    = isset( $input['height'] ) ? dm_sanitize_height( $input['height'] ) : '85vh';
	$out['enable_route'] = empty( $input['enable_route'] ) ? 0 : 1;
	$slug             = isset( $input['route_slug'] ) ? sanitize_title( $input['route_slug'] ) : 'direction-manual';
	$out['route_slug'] = '' === $slug ? 'direction-manual' : $slug;

	// Rewrite rules change with the slug/route: refresh them.
	dm_add_rewrite();
	flush_rewrite_rules( false );

	return $out;
}

function dm_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o        = dm_options();
	$pages    = get_pages();
	$asset    = esc_url( dm_asset_url() );
	$standalone = esc_url( dm_standalone_url() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Direction Manual', 'direction-manual' ); ?></h1>
		<p><?php esc_html_e( 'Decide where the manual appears. You can use more than one method.', 'direction-manual' ); ?></p>

		<form action="options.php" method="post">
			<?php settings_fields( 'dm_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Auto display', 'direction-manual' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="<?php echo esc_attr( DM_OPTION ); ?>[mode]" value="shortcode" <?php checked( $o['mode'], 'shortcode' ); ?>> <?php esc_html_e( 'Shortcode / block only (place it yourself)', 'direction-manual' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( DM_OPTION ); ?>[mode]" value="append" <?php checked( $o['mode'], 'append' ); ?>> <?php esc_html_e( 'Append to a page', 'direction-manual' ); ?></label><br>
							<label><input type="radio" name="<?php echo esc_attr( DM_OPTION ); ?>[mode]" value="replace" <?php checked( $o['mode'], 'replace' ); ?>> <?php esc_html_e( 'Replace a page&#8217;s content', 'direction-manual' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dm_page_id"><?php esc_html_e( 'Target page', 'direction-manual' ); ?></label></th>
					<td>
						<select id="dm_page_id" name="<?php echo esc_attr( DM_OPTION ); ?>[page_id]">
							<option value="0"><?php esc_html_e( '&mdash; Select a page &mdash;', 'direction-manual' ); ?></option>
							<?php foreach ( $pages as $p ) : ?>
								<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( (int) $o['page_id'], (int) $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Used by the Append / Replace options above.', 'direction-manual' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="dm_height"><?php esc_html_e( 'Embed height', 'direction-manual' ); ?></label></th>
					<td>
						<input type="text" id="dm_height" name="<?php echo esc_attr( DM_OPTION ); ?>[height]" value="<?php echo esc_attr( $o['height'] ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'A CSS length, e.g. 85vh, 900px or 100%.', 'direction-manual' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Standalone URL', 'direction-manual' ); ?></th>
					<td>
						<label><input type="checkbox" name="<?php echo esc_attr( DM_OPTION ); ?>[enable_route]" value="1" <?php checked( $o['enable_route'], 1 ); ?>> <?php esc_html_e( 'Serve a full-screen page', 'direction-manual' ); ?></label>
						<p>
							<input type="text" name="<?php echo esc_attr( DM_OPTION ); ?>[route_slug]" value="<?php echo esc_attr( $o['route_slug'] ); ?>" class="regular-text">
							<span class="description"><?php esc_html_e( 'URL slug (default: direction-manual).', 'direction-manual' ); ?></span>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'How to place it', 'direction-manual' ); ?></h2>
		<ul style="list-style:disc;margin-left:20px;">
			<li><?php esc_html_e( 'Shortcode:', 'direction-manual' ); ?> <code>[direction_manual]</code> <?php esc_html_e( '(add height="900px" to override).', 'direction-manual' ); ?></li>
			<li><?php esc_html_e( 'Block editor: add the', 'direction-manual' ); ?> <strong>&#8220;Direction Manual&#8221;</strong> <?php esc_html_e( 'block, or a Shortcode block with the code above.', 'direction-manual' ); ?></li>
			<li><?php esc_html_e( 'Standalone page:', 'direction-manual' ); ?> <a href="<?php echo $standalone; ?>" target="_blank" rel="noopener"><?php echo esc_html( dm_standalone_url() ); ?></a></li>
			<li><?php esc_html_e( 'Direct asset:', 'direction-manual' ); ?> <a href="<?php echo $asset; ?>" target="_blank" rel="noopener"><?php esc_html_e( 'open manual.html', 'direction-manual' ); ?></a></li>
		</ul>

		<h2><?php esc_html_e( 'Preview', 'direction-manual' ); ?></h2>
		<?php
		// Admin-side preview.
		echo '<iframe src="' . $asset . '" title="Direction Manual preview" style="width:100%;height:70vh;border:1px solid #ccd0d4;"></iframe>';
		?>
	</div>
	<?php
}

/**
 * Settings link on the Plugins screen.
 */
function dm_action_links( $links ) {
	$url  = admin_url( 'options-general.php?page=direction-manual' );
	$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'direction-manual' ) . '</a>';
	array_unshift( $links, $link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dm_action_links' );

/* ------------------------------------------------------------------ *
 *  Activation / deactivation
 * ------------------------------------------------------------------ */

function dm_activate() {
	$existing = get_option( DM_OPTION, null );
	if ( null === $existing ) {
		add_option( DM_OPTION, dm_defaults() );
	}
	dm_add_rewrite();
	flush_rewrite_rules( false );
}
register_activation_hook( __FILE__, 'dm_activate' );

function dm_deactivate() {
	flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'dm_deactivate' );
