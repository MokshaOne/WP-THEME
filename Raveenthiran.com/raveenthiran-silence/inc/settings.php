<?php
/**
 * Silence — theme settings (Appearance → Silence).
 * One quiet page: identity, contact, SEO, mail (SMTP), security, motion.
 * Every option is a wp_options row read via sl_opt().
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function sl_settings_defaults() {
	return [
		/* Identity */
		'sl_studio'         => 'raveenthiran',
		'sl_tagline'        => 'photography · wien',
		'sl_location'       => 'Wien',
		'sl_availability'   => 'Available · ' . date_i18n( 'Y' ),
		'sl_about'          => '',
		'sl_accent'         => '#EBEAE6', // used only by OG share cards — the site itself is monochrome

		/* Contact & elsewhere */
		'sl_email'          => get_option( 'admin_email' ),
		'sl_instagram'      => '',
		'sl_behance'        => '',
		'sl_vimeo'          => '',
		'sl_linkedin'       => '',
		'sl_twitter_handle' => '',

		/* SEO — LocalBusiness schema */
		'sl_seo_phone'      => '',
		'sl_seo_street'     => '',
		'sl_seo_postal'     => '1010',
		'sl_seo_city'       => 'Wien',
		'sl_seo_lat'        => '48.2082',
		'sl_seo_lng'        => '16.3738',
		'sl_verify_google'  => '',
		'sl_verify_bing'    => '',
		'sl_verify_yandex'  => '',
		'sl_tracking_script'=> '',

		/* Mail — SMTP (password stored separately as sl_smtp_pass, see inc/smtp.php) */
		'sl_smtp_enable'    => '0',
		'sl_smtp_host'      => 'smtp.gmail.com',
		'sl_smtp_port'      => '587',
		'sl_smtp_secure'    => 'tls',
		'sl_smtp_user'      => '',
		'sl_smtp_from'      => '',
		'sl_smtp_fromname'  => '',

		/* Security — Cloudflare Turnstile */
		'sl_turnstile_site'   => '',
		'sl_turnstile_secret' => '',

		/* Content — Obscura bridge: auto | 1 (force Obscura) | 0 (force Silence) */
		'sl_bridge'         => 'auto',

		/* Motion — string "1"|"0", opt-out */
		'sl_hero_interval'  => '7',
		'sl_fx_fade'        => '1', // chrome falls silent after idle
		'sl_fx_drift'       => '1', // slow drift on the active hero plate
	];
}

/** Checkbox-style keys whose unchecked state must persist as "0". */
function sl_settings_toggles() {
	return [ 'sl_fx_fade', 'sl_fx_drift', 'sl_smtp_enable' ];
}

add_action( 'admin_init', function () {
	foreach ( array_keys( sl_settings_defaults() ) as $key ) {
		$sanitize = 'sanitize_text_field';
		if ( $key === 'sl_about' ) $sanitize = 'sanitize_textarea_field';
		if ( $key === 'sl_tracking_script' ) {
			// Analytics snippets need raw <script>; settings are already
			// manage_options-gated, mirror WP's unfiltered_html rule.
			$sanitize = function ( $v ) {
				return current_user_can( 'unfiltered_html' ) ? (string) $v : wp_kses_post( (string) $v );
			};
		}
		register_setting( 'sl_settings_group', $key, [ 'sanitize_callback' => $sanitize ] );
	}
} );

// Unchecked checkboxes are absent from POST — the *_present hidden
// field lets us persist "0" (same convention as Obscura).
add_action( 'admin_init', function () {
	if ( empty( $_POST['option_page'] ) || $_POST['option_page'] !== 'sl_settings_group' ) return;
	foreach ( sl_settings_toggles() as $k ) {
		if ( isset( $_POST[ $k . '_present' ] ) && ! isset( $_POST[ $k ] ) ) $_POST[ $k ] = '0';
	}
}, 5 );

add_action( 'admin_menu', function () {
	add_theme_page( 'Silence', 'Silence', 'manage_options', 'sl-settings', 'sl_settings_page' );
} );

function sl_setting_field( $key, $type = 'text' ) {
	$d = sl_settings_defaults();
	$v = sl_opt( $key, $d[ $key ] ?? '' );
	if ( $type === 'textarea' ) {
		printf( '<textarea class="large-text" rows="5" name="%s">%s</textarea>', esc_attr( $key ), esc_textarea( $v ) );
	} else {
		printf( '<input type="%s" class="regular-text" name="%s" value="%s">', esc_attr( $type ), esc_attr( $key ), esc_attr( $v ) );
	}
}

function sl_setting_toggle( $key, $label ) {
	$d = sl_settings_defaults();
	printf(
		'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label><input type="hidden" name="%1$s_present" value="1">',
		esc_attr( $key ), checked( sl_opt( $key, $d[ $key ] ?? '0' ), '1', false ), esc_html( $label )
	);
}

function sl_settings_page() {
	?>
	<div class="wrap sl-settings">
		<h1>Silence</h1>
		<?php if ( isset( $_GET['sl_smtp_test'] ) ) : ?>
			<div class="notice notice-<?php echo $_GET['sl_smtp_test'] === '1' ? 'success' : 'error'; ?> is-dismissible"><p>
				<?php echo $_GET['sl_smtp_test'] === '1'
					? esc_html__( 'Test email sent — check your inbox.', 'raveenthiran-silence' )
					: esc_html__( 'Test email failed — check host, username, and app password.', 'raveenthiran-silence' ); ?>
			</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'sl_settings_group' ); ?>

			<h2><?php esc_html_e( '§ Identity', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Wordmark', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_studio' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Tagline', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_tagline' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Location', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_location' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Availability note', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_availability' ); ?>
					<p class="description"><?php esc_html_e( 'Shown top right. Leave empty to hide.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'About text', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_about', 'textarea' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Share-card accent', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_accent' ); ?>
					<p class="description"><?php esc_html_e( 'Hex color used only on generated Open Graph share cards. The site itself stays monochrome.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( '§ Contact & elsewhere', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Enquiry email', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_email', 'email' ); ?></td></tr>
				<tr><th>Instagram</th><td><?php sl_setting_field( 'sl_instagram', 'url' ); ?></td></tr>
				<tr><th>Behance</th><td><?php sl_setting_field( 'sl_behance', 'url' ); ?></td></tr>
				<tr><th>Vimeo</th><td><?php sl_setting_field( 'sl_vimeo', 'url' ); ?></td></tr>
				<tr><th>LinkedIn</th><td><?php sl_setting_field( 'sl_linkedin', 'url' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'X / Twitter handle', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_twitter_handle' ); ?></td></tr>
			</table>

			<h2><?php esc_html_e( '§ SEO', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Phone', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_phone' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Street', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_street' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Postal code', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_postal' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'City', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_city' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Latitude', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_lat' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Longitude', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_seo_lng' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Google verification', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_verify_google' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Bing verification', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_verify_bing' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Yandex verification', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_verify_yandex' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Tracking snippet', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_tracking_script', 'textarea' ); ?>
					<p class="description"><?php esc_html_e( 'Injected consent-gated into <head> (e.g. Cloudflare Web Analytics). Never shown to logged-in users.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2 id="mail"><?php esc_html_e( '§ Mail (SMTP)', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Enable SMTP', 'raveenthiran-silence' ); ?></th>
					<td><?php sl_setting_toggle( 'sl_smtp_enable', __( 'Route all site mail through the SMTP server below', 'raveenthiran-silence' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Host', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_smtp_host' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Port', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_smtp_port', 'number' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Encryption', 'raveenthiran-silence' ); ?></th>
					<td><select name="sl_smtp_secure">
						<option value="tls" <?php selected( sl_opt( 'sl_smtp_secure', 'tls' ), 'tls' ); ?>>STARTTLS (587)</option>
						<option value="ssl" <?php selected( sl_opt( 'sl_smtp_secure', 'tls' ), 'ssl' ); ?>>SSL (465)</option>
					</select></td></tr>
				<tr><th><?php esc_html_e( 'Username', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_smtp_user' ); ?>
					<p class="description"><?php esc_html_e( 'Google Workspace: the REAL login mailbox, not the alias.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'App password', 'raveenthiran-silence' ); ?></th>
					<td><input type="password" class="regular-text" name="sl_smtp_pass" value="" autocomplete="new-password" placeholder="<?php echo get_option( 'sl_smtp_pass' ) ? '••••••••••••••••' : ''; ?>">
					<p class="description"><?php esc_html_e( 'Left blank = keep the stored password. Or define SL_SMTP_PASS in wp-config.php.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'From address', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_smtp_from', 'email' ); ?>
					<p class="description"><?php esc_html_e( 'Must be a verified "Send mail as" alias on the login mailbox.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'From name', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_smtp_fromname' ); ?></td></tr>
				<tr><th></th><td>
					<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sl_smtp_test' ), 'sl_smtp_test' ) ); ?>">
						<?php esc_html_e( 'Send a test email to me', 'raveenthiran-silence' ); ?>
					</a>
					<p class="description"><?php esc_html_e( 'Save the settings first, then test.', 'raveenthiran-silence' ); ?></p>
				</td></tr>
			</table>

			<h2><?php esc_html_e( '§ Security', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Turnstile site key', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_turnstile_site' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Turnstile secret key', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_turnstile_secret' ); ?>
					<p class="description"><?php esc_html_e( 'Create a widget at Cloudflare → Turnstile. Leave blank to keep honeypot + rate-limit only.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( '§ Content', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Obscura content', 'raveenthiran-silence' ); ?></th>
					<td>
						<?php $bridge = sl_opt( 'sl_bridge', 'auto' ); ?>
						<select name="sl_bridge">
							<option value="auto" <?php selected( $bridge, 'auto' ); ?>><?php esc_html_e( 'Auto — use Obscura projects/journal if they exist and no Silence ones do (recommended)', 'raveenthiran-silence' ); ?></option>
							<option value="1"    <?php selected( $bridge, '1' ); ?>><?php esc_html_e( 'Always read Obscura content', 'raveenthiran-silence' ); ?></option>
							<option value="0"    <?php selected( $bridge, '0' ); ?>><?php esc_html_e( 'Always use Silence content only', 'raveenthiran-silence' ); ?></option>
						</select>
						<p class="description">
							<?php echo function_exists( 'sl_bridge_active' ) && sl_bridge_active()
								? esc_html__( 'Bridge is ACTIVE — Silence is showing your existing Obscura projects, journal, galleries, and fields. Nothing is converted; switching back to Obscura leaves everything untouched.', 'raveenthiran-silence' )
								: esc_html__( 'Bridge is inactive — Silence is using its own sl_ content. After changing this, visit Settings → Permalinks → Save once.', 'raveenthiran-silence' ); ?>
						</p>
					</td></tr>
			</table>

			<h2><?php esc_html_e( '§ Motion', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Hero interval (s)', 'raveenthiran-silence' ); ?></th><td><?php sl_setting_field( 'sl_hero_interval', 'number' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Interface falls silent', 'raveenthiran-silence' ); ?></th>
					<td><?php sl_setting_toggle( 'sl_fx_fade', __( 'Fade the chrome away after a few idle seconds, leaving only the photograph', 'raveenthiran-silence' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Plate drift', 'raveenthiran-silence' ); ?></th>
					<td><?php sl_setting_toggle( 'sl_fx_drift', __( 'Very slow scale drift on the active hero plate', 'raveenthiran-silence' ) ); ?></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
