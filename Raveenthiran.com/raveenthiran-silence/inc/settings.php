<?php
/**
 * Silence — theme settings (Appearance → Silence).
 * Every shared option uses Obscura's key (nr_logo_text, nr_email,
 * nr_smtp_*, …), so both themes read and write the same rows: change a
 * value here and it's already set when you switch back to Obscura.
 * Only the two Silence-specific motion toggles use their own keys.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function nr_settings_defaults() {
	return [
		/* Identity — shared with Obscura */
		'nr_logo_text'      => 'raveenthiran',
		'nr_tagline'        => 'photography · wien',
		'nr_location'       => 'Wien',
		'nr_avail_text'     => 'Available · ' . date_i18n( 'Y' ),
		'nr_about_lede'     => '',
		'nr_about_bio'      => '',
		'nr_studio'         => '', // studio address line (About + PDF estimate footer)
		'nr_currency'       => '€',
		'nr_stats_proj'     => '',
		'nr_stats_cnty'     => '',
		'nr_stats_pubs'     => '',
		'nr_stats_awd'      => '',
		'nr_clients_list'   => '',
		'nr_accent'         => '#EBEAE6', // used only by OG share cards — the site itself is monochrome

		/* Contact & elsewhere — shared */
		'nr_email'          => get_option( 'admin_email' ),
		'nr_instagram'      => '',
		'nr_behance'        => '',
		'nr_vimeo'          => '',
		'nr_linkedin'       => '',
		'nr_twitter_handle' => '',
		'nr_phone'          => '',

		/* SEO — shared (options_seo_* matches Obscura's ACF option rows) */
		'options_seo_street'      => '',
		'options_seo_postal_code' => '1010',
		'options_seo_city'        => 'Wien',
		'options_seo_lat'         => '48.2082',
		'options_seo_lng'         => '16.3738',
		'nr_verify_google'  => '',
		'nr_verify_bing'    => '',
		'nr_verify_yandex'  => '',
		'nr_tracking_script'=> '',

		/* Mail — SMTP, shared (password stored separately as nr_smtp_pass) */
		'nr_smtp_enable'    => '0',
		'nr_smtp_host'      => 'smtp.gmail.com',
		'nr_smtp_port'      => '587',
		'nr_smtp_secure'    => 'tls',
		'nr_smtp_user'      => '',
		'nr_smtp_from'      => '',
		'nr_smtp_fromname'  => '',

		/* Security — Cloudflare Turnstile, shared */
		'nr_turnstile_site'   => '',
		'nr_turnstile_secret' => '',

		/* Motion — Silence-specific; hero interval shared (Obscura stores ms) */
		'nr_hero_interval'  => '7',
		'nr_fx_fade'        => '1', // chrome falls silent after idle
		'nr_fx_drift'       => '1', // slow drift on the active hero plate
	];
}

/** Checkbox-style keys whose unchecked state must persist as "0". */
function nr_settings_toggles() {
	return [ 'nr_fx_fade', 'nr_fx_drift', 'nr_smtp_enable' ];
}

add_action( 'admin_init', function () {
	foreach ( array_keys( nr_settings_defaults() ) as $key ) {
		$sanitize = 'sanitize_text_field';
		if ( in_array( $key, [ 'nr_about_lede', 'nr_about_bio', 'nr_clients_list' ], true ) ) $sanitize = 'sanitize_textarea_field';
		if ( $key === 'nr_tracking_script' ) {
			// Analytics snippets need raw <script>; settings are already
			// manage_options-gated, mirror WP's unfiltered_html rule.
			$sanitize = function ( $v ) {
				return current_user_can( 'unfiltered_html' ) ? (string) $v : wp_kses_post( (string) $v );
			};
		}
		register_setting( 'nr_settings_group', $key, [ 'sanitize_callback' => $sanitize ] );
	}
} );

// Unchecked checkboxes are absent from POST — the *_present hidden
// field lets us persist "0" (same convention as Obscura).
add_action( 'admin_init', function () {
	if ( empty( $_POST['option_page'] ) || $_POST['option_page'] !== 'nr_settings_group' ) return;
	foreach ( nr_settings_toggles() as $k ) {
		if ( isset( $_POST[ $k . '_present' ] ) && ! isset( $_POST[ $k ] ) ) $_POST[ $k ] = '0';
	}
}, 5 );

add_action( 'admin_menu', function () {
	add_theme_page( 'Silence', 'Silence', 'manage_options', 'nr-settings', 'nr_sil_settings_page' );
} );

function nr_setting_field( $key, $type = 'text' ) {
	$d = nr_settings_defaults();
	$v = nr_opt( $key, $d[ $key ] ?? '' );
	if ( $type === 'textarea' ) {
		printf( '<textarea class="large-text" rows="5" name="%s">%s</textarea>', esc_attr( $key ), esc_textarea( $v ) );
	} else {
		printf( '<input type="%s" class="regular-text" name="%s" value="%s">', esc_attr( $type ), esc_attr( $key ), esc_attr( $v ) );
	}
}

function nr_setting_toggle( $key, $label ) {
	$d = nr_settings_defaults();
	printf(
		'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label><input type="hidden" name="%1$s_present" value="1">',
		esc_attr( $key ), checked( nr_opt( $key, $d[ $key ] ?? '0' ), '1', false ), esc_html( $label )
	);
}

function nr_sil_settings_page() {
	?>
	<div class="wrap nr-settings">
		<h1>Silence</h1>
		<p class="description"><?php esc_html_e( 'Shared with Obscura: identity, contact, SEO, mail, and security values are the same options both themes read — change them once, they apply to whichever theme is active.', 'raveenthiran-silence' ); ?></p>
		<?php if ( isset( $_GET['nr_smtp_test'] ) ) : ?>
			<div class="notice notice-<?php echo $_GET['nr_smtp_test'] === '1' ? 'success' : 'error'; ?> is-dismissible"><p>
				<?php echo $_GET['nr_smtp_test'] === '1'
					? esc_html__( 'Test email sent — check your inbox.', 'raveenthiran-silence' )
					: esc_html__( 'Test email failed — check host, username, and app password.', 'raveenthiran-silence' ); ?>
			</p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'nr_settings_group' ); ?>

			<h2><?php esc_html_e( '§ Identity', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Wordmark', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_logo_text' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Tagline', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_tagline' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Location', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_location' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Availability note', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_avail_text' ); ?>
					<p class="description"><?php esc_html_e( 'Shown top right. Leave empty to hide.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'About lede', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_about_lede', 'textarea' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'About bio', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_about_bio', 'textarea' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Studio address', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_studio' ); ?>
					<p class="description"><?php esc_html_e( 'One line, e.g. Studio Krautwald · Gumpendorfer Str. 81 · 1060 Wien. Shown on About + the PDF estimate.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Stats — projects / countries / publications / awards', 'raveenthiran-silence' ); ?></th>
					<td><?php nr_setting_field( 'nr_stats_proj' ); ?> <?php nr_setting_field( 'nr_stats_cnty' ); ?><br><br><?php nr_setting_field( 'nr_stats_pubs' ); ?> <?php nr_setting_field( 'nr_stats_awd' ); ?>
					<p class="description"><?php esc_html_e( 'Shown as the stats row on About. Leave any empty to hide it.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Selected clients', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_clients_list', 'textarea' ); ?>
					<p class="description"><?php esc_html_e( 'One client per line — the About page clients wall.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Currency', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_currency' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Share-card accent', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_accent' ); ?>
					<p class="description"><?php esc_html_e( 'Hex color used only on generated Open Graph share cards. The site itself stays monochrome.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( '§ Contact & elsewhere', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Enquiry email', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_email', 'email' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Phone', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_phone' ); ?></td></tr>
				<tr><th>Instagram</th><td><?php nr_setting_field( 'nr_instagram', 'url' ); ?></td></tr>
				<tr><th>Behance</th><td><?php nr_setting_field( 'nr_behance', 'url' ); ?></td></tr>
				<tr><th>Vimeo</th><td><?php nr_setting_field( 'nr_vimeo', 'url' ); ?></td></tr>
				<tr><th>LinkedIn</th><td><?php nr_setting_field( 'nr_linkedin', 'url' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'X / Twitter handle', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_twitter_handle' ); ?></td></tr>
			</table>

			<h2><?php esc_html_e( '§ SEO', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Street', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'options_seo_street' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Postal code', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'options_seo_postal_code' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'City', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'options_seo_city' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Latitude', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'options_seo_lat' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Longitude', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'options_seo_lng' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Google verification', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_verify_google' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Bing verification', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_verify_bing' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Yandex verification', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_verify_yandex' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Tracking snippet', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_tracking_script', 'textarea' ); ?>
					<p class="description"><?php esc_html_e( 'Injected consent-gated into <head> (e.g. Cloudflare Web Analytics). Never shown to logged-in users.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2 id="mail"><?php esc_html_e( '§ Mail (SMTP)', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Enable SMTP', 'raveenthiran-silence' ); ?></th>
					<td><?php nr_setting_toggle( 'nr_smtp_enable', __( 'Route all site mail through the SMTP server below', 'raveenthiran-silence' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Host', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_smtp_host' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Port', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_smtp_port', 'number' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Encryption', 'raveenthiran-silence' ); ?></th>
					<td><select name="nr_smtp_secure">
						<option value="tls" <?php selected( nr_opt( 'nr_smtp_secure', 'tls' ), 'tls' ); ?>>STARTTLS (587)</option>
						<option value="ssl" <?php selected( nr_opt( 'nr_smtp_secure', 'tls' ), 'ssl' ); ?>>SSL (465)</option>
					</select></td></tr>
				<tr><th><?php esc_html_e( 'Username', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_smtp_user' ); ?>
					<p class="description"><?php esc_html_e( 'Google Workspace: the REAL login mailbox, not the alias.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'App password', 'raveenthiran-silence' ); ?></th>
					<td><input type="password" class="regular-text" name="nr_smtp_pass" value="" autocomplete="new-password" placeholder="<?php echo get_option( 'nr_smtp_pass' ) ? '••••••••••••••••' : ''; ?>">
					<p class="description"><?php esc_html_e( 'Left blank = keep the stored password. Or define NR_SMTP_PASS in wp-config.php. Same credential Obscura uses.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'From address', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_smtp_from', 'email' ); ?>
					<p class="description"><?php esc_html_e( 'Must be a verified "Send mail as" alias on the login mailbox.', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'From name', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_smtp_fromname' ); ?></td></tr>
				<tr><th></th><td>
					<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nr_smtp_test' ), 'nr_smtp_test' ) ); ?>">
						<?php esc_html_e( 'Send a test email to me', 'raveenthiran-silence' ); ?>
					</a>
					<p class="description"><?php esc_html_e( 'Save the settings first, then test.', 'raveenthiran-silence' ); ?></p>
				</td></tr>
			</table>

			<h2><?php esc_html_e( '§ Security', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Turnstile site key', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_turnstile_site' ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Turnstile secret key', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_turnstile_secret' ); ?>
					<p class="description"><?php esc_html_e( 'Create a widget at Cloudflare → Turnstile. Leave blank to keep honeypot + rate-limit only.', 'raveenthiran-silence' ); ?></p></td></tr>
			</table>

			<h2><?php esc_html_e( '§ Motion', 'raveenthiran-silence' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr><th><?php esc_html_e( 'Hero interval', 'raveenthiran-silence' ); ?></th><td><?php nr_setting_field( 'nr_hero_interval', 'number' ); ?>
					<p class="description"><?php esc_html_e( 'Seconds. Values over 100 are treated as milliseconds (Obscura saves ms — the shared value just works).', 'raveenthiran-silence' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Interface falls silent', 'raveenthiran-silence' ); ?></th>
					<td><?php nr_setting_toggle( 'nr_fx_fade', __( 'Fade the chrome away after a few idle seconds, leaving only the photograph', 'raveenthiran-silence' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Plate drift', 'raveenthiran-silence' ); ?></th>
					<td><?php nr_setting_toggle( 'nr_fx_drift', __( 'Very slow scale drift on the active hero plate', 'raveenthiran-silence' ) ); ?></td></tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
