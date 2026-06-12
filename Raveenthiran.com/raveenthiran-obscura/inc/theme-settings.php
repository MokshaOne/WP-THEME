<?php
/**
 * Theme Settings — full admin control for Catalogue Noir.
 * Adds an "Theme Settings" page under Appearance with every visible
 * string, the accent color, and visual-effect toggles editable by the
 * site owner without touching code.
 *
 * Every value is persisted as its own wp_options row so the existing
 * `nr_opt()` helper (defined in inc/functions-additions.php) keeps
 * working — templates do not need to be updated to read a new bag.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   Defaults — used by both the form and any code calling nr_opt()
   ============================================================= */
if ( ! function_exists( 'nr_settings_defaults' ) ) {
function nr_settings_defaults() {
	return [
		/* Branding */
		'nr_logo_text'       => 'raveenthiran',
		'nr_logo_sub'        => 'studio · ' . date( 'Y' ),
		'nr_accent'          => '#F2A03D',
		'nr_book_label'      => __( 'Book a shoot', 'raveenthiran' ),

		/* Colors (in addition to accent above) */
		'nr_color_bg'        => '#0B0C10',
		'nr_color_ink'       => '#F2EFE9',

		/* Studio */
		'nr_studio'          => 'Studio Krautwald · Gumpendorfer Str. 81 · 1060 Wien',
		'nr_location'        => 'Wien, Austria',
		'nr_coords'          => '48.20°N · 16.36°E',
		'nr_email'           => 'studio@raveenthiran.at',
		'nr_phone'           => '+43 1 555 0142',
		'nr_legal_name'      => '',
		'nr_uid'             => '',
		'nr_gewerbe'         => 'Berufsfotografie (freies Gewerbe)',
		'nr_instagram'       => '',
		'nr_behance'         => '',
		'nr_vimeo'           => '',
		'nr_linkedin'        => '',
		'nr_available'       => '1',
		'nr_avail_text'      => __( 'Available · 2026', 'raveenthiran' ),
		'nr_booking_url'     => '',
		'nr_booking_embed'   => '',
		'nr_whatsapp'        => '',
		'nr_presskit_url'    => '',
		'nr_footer_cta'      => __( 'Start a project', 'raveenthiran' ),
		'nr_ig_grid'         => '',

		/* Stats */
		'nr_stats_proj'      => '142',
		'nr_stats_cnty'      => '18',
		'nr_stats_pubs'      => '36',
		'nr_stats_awd'       => '11',

		/* Page-specific copy */
		'nr_hero_eyebrow'    => __( 'Featured work', 'raveenthiran' ),

		'nr_work_eyebrow'    => __( 'Portfolio', 'raveenthiran' ),
		'nr_work_title'      => __( 'The complete <em>portfolio.</em>', 'raveenthiran' ),
		'nr_work_per_page'   => '24',

		'nr_about_eyebrow'   => __( 'Studio · §03', 'raveenthiran' ),
		'nr_about_title'     => __( 'A practice of <em>looking,</em> slowly.', 'raveenthiran' ),
		'nr_about_lede'      => __( 'Nishuthan Raveenthiran is a Vienna-based photographer working at the intersection of editorial reportage, architectural quiet, and portraits of working people.', 'raveenthiran' ),
		'nr_about_name'      => __( 'Nishuthan Raveenthiran', 'raveenthiran' ),
		'nr_about_role'      => __( 'Photographer & Web Architect · Vienna', 'raveenthiran' ),
		'nr_about_bio'       => __( "Originally from Jaffna; trained in Berlin; based in the 6th district since 2019. Shoots primarily on 35mm film for personal series and editorial assignments. Available for commissioned editorial, architectural, and portrait work worldwide.\n\nClients include SZ Magazin, NYT Magazine, Apartamento, the Belvedere, and the Hotel Sacher.", 'raveenthiran' ),

		'nr_booking_eyebrow' => __( 'Booking · §04', 'raveenthiran' ),
		'nr_booking_title'   => __( 'Book a <em>session.</em>', 'raveenthiran' ),
		'nr_step_1_t'        => __( 'Signal', 'raveenthiran' ),
		'nr_step_1_d'        => __( 'Fill in your project details and preferred shoot window.', 'raveenthiran' ),
		'nr_step_2_t'        => __( 'Briefing', 'raveenthiran' ),
		'nr_step_2_d'        => __( 'A short call to align on vision, location, and logistics.', 'raveenthiran' ),
		'nr_step_3_t'        => __( 'Shoot & deliver', 'raveenthiran' ),
		'nr_step_3_d'        => __( 'We meet, we create. Edits within 5–7 business days.', 'raveenthiran' ),

		'nr_contact_eyebrow' => __( 'Contact · §06', 'raveenthiran' ),
		'nr_contact_title'   => __( "Let's work <em>together.</em>", 'raveenthiran' ),
		'nr_contact_intro'   => __( 'Tell me about a place, a person, or an hour of the day. I will write back within the day.', 'raveenthiran' ),

		'nr_faq_eyebrow'     => __( 'FAQ · §05', 'raveenthiran' ),
		'nr_faq_title'       => __( 'Common <em>questions.</em>', 'raveenthiran' ),

		/* Recognition */
		'nr_awards_list'     => '',
		'nr_press_list'      => '',
		'nr_clients_list'    => '',

		/* Optional extras */
		'nr_tagline'             => __( 'Photographer based in Vienna. Capturing moments that matter.', 'raveenthiran' ),
		'nr_show_inquiry_fab'    => '0',
		'nr_fab_label'           => __( 'Book me', 'raveenthiran' ),
		'nr_show_cookie_notice'  => '1',

		/* SEO + verification */
		'nr_twitter_handle'      => '',
		'nr_verify_google'       => '',
		'nr_verify_bing'         => '',
		'nr_verify_yandex'       => '',
		'nr_tracking_script'     => '',

		/* Security — Cloudflare Turnstile (#74) */
		'nr_turnstile_site'      => '',
		'nr_turnstile_secret'    => '',

		/* Mail — SMTP (Google Workspace etc.). Password is stored separately
		   as nr_smtp_pass (registered in inc/smtp.php) so it isn't kses-filtered. */
		'nr_smtp_enable'     => '0',
		'nr_smtp_host'       => 'smtp.gmail.com',
		'nr_smtp_port'       => '587',
		'nr_smtp_secure'     => 'tls',
		'nr_smtp_user'       => '',
		'nr_smtp_from'       => '',
		'nr_smtp_fromname'   => '',

		/* Visual effects (toggles — string "1"|"0") */
		'nr_fx_cursor'       => '1',
		'nr_fx_grain'        => '1',
		'nr_fx_wipe'         => '1',
		'nr_fx_ken'          => '1',
		'nr_fx_anchors'      => '1',
		'nr_fx_preloader'    => '0',  // intro splash — off by default (revealed the hero mid-load → desktop CLS)
		'nr_fx_webgl'        => '0',
		'nr_fx_viewtrans'    => '0',
		'nr_fx_distort'      => '0',
		'nr_fx_lines'        => '0',
		'nr_fx_favicon'      => '0',
		'nr_fx_interlink'    => '0',
		'nr_fx_cinematic'    => '0',  // Batch 1 — GPU/motion craft layer + visitor motion switch

		/* Performance */
		'nr_perf_async_css'  => '0',
		'nr_signature'       => '',

		/* Conversion & editorial (IDEAS-NEXT, v4.41) — all opt-in */
		'nr_fx_speculation'  => '0',  // Speculation Rules prerender-on-hover
		'nr_avail_dates'     => '',   // "next open dates" line
		'nr_clients_logos'   => '',   // "url | name" per line
		'nr_indexnow_key'    => '',   // IndexNow key (8–128 hex chars)

		/* Conversion & SEO extras (IDEAS-200 Batch 2+3, v4.44) — opt-in / empty */
		'nr_fx_shortlist'    => '0',  // visitor "selection" hearts on cards
		'nr_fx_exit'         => '0',  // exit-intent soft offer
		'nr_exit_text'       => '',   // exit-intent line (blank → a default)
		'nr_trust_text'      => '',   // reassurance line under the Enquire form
		'nr_packages'        => '',   // [nr_packages] — "Name | €from | feat;feat"
		'nr_promo_text'      => '',   // seasonal banner (HTML <a>/<strong> ok)
		'nr_promo_until'     => '',   // YYYY-MM-DD — banner auto-hides after
		'nr_meta_title_tpl'  => '',   // #114 token <title> template for projects
		'nr_block_ai'        => '0',  // robots.txt: disallow AI crawlers
		'nr_clean_uninstall' => '0',  // #193 remove theme options when switching away
		'nr_vat_rate'        => '20', // #55 VAT % shown on estimates (0 = hide)
		'nr_glossary'        => '',   // #144 "term = definition" per line (journal)
		'nr_followup_days'   => '3',  // #66 remind the owner about enquiries unanswered after N days
		'nr_shows'           => '',   // #35 exhibitions: Title | Venue | YYYY-MM-DD | url (per line)
		'nr_busy_dates'      => '',   // #41 availability: busy YYYY-MM-DD per line
		'nr_admin_simplify'  => '1',  // declutter wp-admin (menus + one combined dashboard widget)
		'nr_fx_gpu'          => '0',  // Batch 6: GPU/canvas effects (haze, dither, blob trail, iris)
		'nr_csp_mode'        => 'off', // Content-Security-Policy: off | report | enforce
		'nr_csp_extra'       => '',   // extra allowed hosts (space-separated)
		'nr_fx_analytics'    => '0',  // self-hosted pageview analytics
		'nr_fx_sw'           => '0',  // service worker (offline + SWR)
		'nr_fx_vrails'       => '0',  // virtualised rails (content-visibility)
		'nr_cta_view_b'      => '',   // #48 A/B variant for the hero CTA label

		/* Color mode — dark (default) / light / system (honor prefers-color-scheme) */
		'nr_color_mode'      => 'dark',

		/* Hero slider */
		'nr_hero_auto'       => '1',
		'nr_hero_interval'   => '9000',
		'nr_hero_max'        => '6',

		/* Footer */
		'nr_footer_text'     => sprintf( '© %s · Nishuthan Raveenthiran — Wien, Austria', date( 'Y' ) ),
		'nr_footer_legal_dz' => __( 'Datenschutz', 'raveenthiran' ),
		'nr_footer_legal_ag' => __( 'AGB', 'raveenthiran' ),
		'nr_footer_legal_im' => __( 'Impressum', 'raveenthiran' ),

		/* Mobile tab labels */
		'nr_tab_1_label'     => __( 'Home', 'raveenthiran' ),
		'nr_tab_2_label'     => __( 'Work', 'raveenthiran' ),
		'nr_tab_3_label'     => __( 'Book', 'raveenthiran' ),
		'nr_tab_4_label'     => __( 'Hello', 'raveenthiran' ),

		/* CTA / button labels — pulled site-wide */
		'nr_cta_view'        => __( 'View project', 'raveenthiran' ),
		'nr_cta_commission'  => __( 'Commission similar', 'raveenthiran' ),
		'nr_cta_back'        => __( 'Back to work', 'raveenthiran' ),
		'nr_cta_send'        => __( 'Send brief', 'raveenthiran' ),
		'nr_cta_book_session'=> __( 'Book a session', 'raveenthiran' ),

		/* Project meta row labels */
		'nr_meta_client'     => __( 'Client', 'raveenthiran' ),
		'nr_meta_year'       => __( 'Year', 'raveenthiran' ),
		'nr_meta_location'   => __( 'Location', 'raveenthiran' ),
		'nr_meta_discipline' => __( 'Discipline', 'raveenthiran' ),
		'nr_meta_frames'     => __( 'Frames', 'raveenthiran' ),
		'nr_meta_format'     => __( 'Format', 'raveenthiran' ),
	];
}
}

/* =============================================================
   Make defaults respected by nr_opt() — filter the default arg
   ============================================================= */
add_filter( 'pre_option_nr_logo_text', 'nr_settings_default_filter', 10, 1 );
foreach ( array_keys( nr_settings_defaults() ) as $k ) {
	add_filter( 'default_option_' . $k, function ( $v, $opt ) {
		$defs = nr_settings_defaults();
		return ( $v === false || $v === '' ) && isset( $defs[ $opt ] ) ? $defs[ $opt ] : $v;
	}, 10, 2 );
}
function nr_settings_default_filter( $value ) { return $value; }

/* =============================================================
   Admin menu — top-level "Theme Settings" page
   ============================================================= */
add_action( 'admin_menu', function () {
	// Top-level "Obscura" hub next to Dashboard (was an Appearance sub-page).
	add_menu_page(
		__( 'Theme Settings — Catalogue Noir', 'raveenthiran' ),
		__( 'Obscura', 'raveenthiran' ),
		'manage_options',
		'nr-theme-settings',
		'nr_theme_settings_page',
		'dashicons-camera',
		3
	);
	// Relabel the auto-generated first sub-item.
	add_submenu_page( 'nr-theme-settings', __( 'Settings', 'raveenthiran' ), __( 'Settings', 'raveenthiran' ), 'manage_options', 'nr-theme-settings', 'nr_theme_settings_page' );
} );

/* register all options so they're saveable */
add_action( 'admin_init', function () {
	$raw_html_fields = [ 'nr_tracking_script' ]; // these may legitimately contain <script>
	foreach ( array_keys( nr_settings_defaults() ) as $k ) {
		register_setting( 'nr_theme_settings_group', $k, [
			'type'              => 'string',
			'sanitize_callback' => in_array( $k, $raw_html_fields, true )
				? 'nr_settings_sanitize_raw_html'
				: 'nr_settings_sanitize_field',
		] );
	}
} );

function nr_settings_sanitize_field( $v ) {
	if ( is_array( $v ) ) return array_map( 'wp_kses_post', $v );
	// allow basic inline HTML in title fields (<em>, <br>)
	return wp_kses( (string) $v, [
		'em'     => [],
		'br'     => [],
		'strong' => [],
		'b'      => [],
	] );
}

/* Permissive sanitize for tracking scripts. Trusts the user only if they
   already have unfiltered_html (admins on single-site, super-admins on
   multisite). Otherwise falls back to stripping <script> for safety. */
function nr_settings_sanitize_raw_html( $v ) {
	$v = (string) $v;
	if ( current_user_can( 'unfiltered_html' ) ) return $v; // pass through verbatim
	return wp_kses_post( $v );                              // strip <script> etc.
}

/* =============================================================
   Enqueue color picker on our settings page only
   ============================================================= */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( $hook !== 'toplevel_page_nr-theme-settings' ) return;
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	add_action( 'admin_footer', function () {
		?>
		<script>
		jQuery(function($){
			if ($.fn.wpColorPicker) $('.nr-color-picker').wpColorPicker();
		});
		</script>
		<?php
	} );
} );

/* =============================================================
   Helpers for the form
   ============================================================= */
function nr_field_text( $key, $size = 40 ) {
	$defs = nr_settings_defaults();
	$v    = get_option( $key, $defs[ $key ] ?? '' );
	printf(
		'<input type="text" name="%s" value="%s" size="%d" class="regular-text">',
		esc_attr( $key ), esc_attr( $v ), (int) $size
	);
}
function nr_field_textarea( $key, $rows = 4 ) {
	$defs = nr_settings_defaults();
	$v    = get_option( $key, $defs[ $key ] ?? '' );
	printf(
		'<textarea name="%s" rows="%d" class="large-text">%s</textarea>',
		esc_attr( $key ), (int) $rows, esc_textarea( $v )
	);
}

/* 3-column repeater (Year · Publisher · Link) for the Press list. The inputs
 * are NOT submitted; a hidden <textarea name="$key"> (the real option) is kept
 * in sync by JS as canonical "year | publisher | url" lines — so the existing
 * save pipeline and nr_recognition_list() parser are untouched. With JS off the
 * hidden textarea is still editable, so it degrades gracefully. */
function nr_field_press_rows( $key ) {
	$defs = nr_settings_defaults();
	$raw  = get_option( $key, $defs[ $key ] ?? '' );
	$rows = function_exists( 'nr_recognition_list' ) ? nr_recognition_list( $key ) : [];
	if ( ! $rows ) $rows = [ [ 'year' => '', 'title' => '', 'org' => '', 'url' => '' ] ];

	static $assets = false;
	if ( ! $assets ) {
		$assets = true;
		echo '<style id="nr-rep-css">'
			. '.nr-rep__t{border-collapse:collapse;margin:0 0 8px;max-width:760px;width:100%}'
			. '.nr-rep__t th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#646970;padding:0 8px 4px;font-weight:600}'
			. '.nr-rep__t td{padding:3px 8px 3px 0;vertical-align:middle}'
			. '.nr-rep__t input{width:100%}.nr-rep__t .nr-rep__y input{width:90px}'
			. '.nr-rep__del{color:#b32d2e;text-decoration:none;font-size:15px;line-height:1;cursor:pointer}'
			. '</style>';
	}
	?>
	<div class="nr-rep" data-rep>
		<table class="nr-rep__t">
			<thead><tr>
				<th class="nr-rep__y"><?php esc_html_e( 'Year', 'raveenthiran' ); ?></th>
				<th><?php esc_html_e( 'Publisher', 'raveenthiran' ); ?></th>
				<th><?php esc_html_e( 'Link', 'raveenthiran' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody data-rep-body>
				<?php foreach ( $rows as $r ) : $name = trim( $r['title'] ?: $r['org'] ); ?>
					<tr>
						<td class="nr-rep__y"><input type="text" data-f="year" value="<?php echo esc_attr( $r['year'] ); ?>" placeholder="2025" aria-label="<?php esc_attr_e( 'Year', 'raveenthiran' ); ?>"></td>
						<td><input type="text" data-f="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Publication', 'raveenthiran' ); ?>" aria-label="<?php esc_attr_e( 'Publisher', 'raveenthiran' ); ?>"></td>
						<td><input type="url" data-f="url" value="<?php echo esc_attr( $r['url'] ); ?>" placeholder="https://…" aria-label="<?php esc_attr_e( 'Link', 'raveenthiran' ); ?>"></td>
						<td><button type="button" class="nr-rep__del" data-rep-del aria-label="<?php esc_attr_e( 'Remove row', 'raveenthiran' ); ?>">✕</button></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-rep-add>＋ <?php esc_html_e( 'Add row', 'raveenthiran' ); ?></button></p>
		<textarea name="<?php echo esc_attr( $key ); ?>" data-rep-sync hidden><?php echo esc_textarea( $raw ); ?></textarea>
	</div>
	<?php if ( empty( $GLOBALS['nr_rep_js'] ) ) : $GLOBALS['nr_rep_js'] = true; ?>
	<script>
	(function () {
		function rebuild( rep ) {
			var lines = [];
			rep.querySelectorAll( '[data-rep-body] tr' ).forEach( function ( tr ) {
				var g = function ( f ) { var el = tr.querySelector( '[data-f="' + f + '"]' ); return el ? el.value.trim() : ''; };
				var y = g( 'year' ), n = g( 'name' ), u = g( 'url' );
				if ( ! y && ! n && ! u ) return;
				var parts = [ y, n ];
				if ( u ) parts.push( u );
				lines.push( parts.join( ' | ' ) );
			} );
			rep.querySelector( '[data-rep-sync]' ).value = lines.join( '\n' );
		}
		document.querySelectorAll( '[data-rep]' ).forEach( function ( rep ) {
			if ( rep.dataset.repInit ) return;
			rep.dataset.repInit = '1';
			var body = rep.querySelector( '[data-rep-body]' );
			var tmpl = body.querySelector( 'tr' );
			rep.addEventListener( 'input', function () { rebuild( rep ); } );
			rep.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( '[data-rep-add]' ) ) {
					e.preventDefault();
					var clone = tmpl.cloneNode( true );
					clone.querySelectorAll( 'input' ).forEach( function ( i ) { i.value = ''; } );
					body.appendChild( clone );
					var first = clone.querySelector( 'input' ); if ( first ) first.focus();
					rebuild( rep );
				} else if ( e.target.closest( '[data-rep-del]' ) ) {
					e.preventDefault();
					var rows = body.querySelectorAll( 'tr' );
					if ( rows.length > 1 ) { e.target.closest( 'tr' ).remove(); }
					else { e.target.closest( 'tr' ).querySelectorAll( 'input' ).forEach( function ( i ) { i.value = ''; } ); }
					rebuild( rep );
				}
			} );
			rebuild( rep );
		} );
	})();
	</script>
	<?php endif;
}
function nr_field_color( $key ) {
	$defs = nr_settings_defaults();
	$v    = get_option( $key, $defs[ $key ] ?? '' );
	printf(
		'<input type="text" name="%s" value="%s" class="nr-color-picker">',
		esc_attr( $key ), esc_attr( $v )
	);
}
function nr_field_toggle( $key, $label = '' ) {
	$defs = nr_settings_defaults();
	$v    = get_option( $key, $defs[ $key ] ?? '0' );
	printf(
		'<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
		esc_attr( $key ),
		checked( $v, '1', false ),
		esc_html( $label )
	);
	// ensure unchecked state still persists
	printf( '<input type="hidden" name="%s_present" value="1">', esc_attr( $key ) );
}

/**
 * Pre-save hook so unchecked toggles persist as "0" (HTML forms omit
 * unchecked checkboxes; the *_present hidden input lets us detect them).
 */
add_action( 'admin_init', function () {
	if ( empty( $_POST ) || empty( $_POST['option_page'] ) || $_POST['option_page'] !== 'nr_theme_settings_group' ) return;
	$toggle_keys = [ 'nr_available', 'nr_show_inquiry_fab', 'nr_show_cookie_notice', 'nr_smtp_enable', 'nr_perf_async_css', 'nr_block_ai', 'nr_clean_uninstall', 'nr_admin_simplify', 'nr_fx_gpu', 'nr_fx_analytics', 'nr_fx_sw', 'nr_fx_vrails', 'nr_fx_shortlist', 'nr_fx_exit' ];
	foreach ( array_keys( nr_settings_defaults() ) as $k ) {
		if ( strpos( $k, 'nr_fx_' ) !== 0 && ! in_array( $k, $toggle_keys, true ) ) continue;
		if ( isset( $_POST[ $k . '_present' ] ) && ! isset( $_POST[ $k ] ) ) {
			$_POST[ $k ] = '0';
		}
	}
}, 5 );

/* =============================================================
   The settings page
   ============================================================= */
function nr_theme_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap nr-settings">
		<h1><?php esc_html_e( 'Theme Settings — Catalogue Noir', 'raveenthiran' ); ?></h1>
		<p class="description" style="max-width:780px"><?php esc_html_e( 'Full control over visible site copy, brand color, and visual effects. Every field here maps to a value that templates read on render. Some fields support inline <em> tags for bold emphasis.', 'raveenthiran' ); ?></p>

		<form method="post" action="options.php" class="nr-settings-form">
			<?php settings_fields( 'nr_theme_settings_group' ); ?>

			<details open class="nr-settings__group">
				<summary><h2>§ Branding</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Wordmark text', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_logo_text', 30 ); ?>
							<p class="description"><?php esc_html_e( 'The studio name shown in the top bar.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Wordmark sub-label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_logo_sub', 30 ); ?>
							<p class="description"><?php esc_html_e( 'Small monospace text next to the wordmark, e.g. "studio · 2026".', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Accent color', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_color( 'nr_accent' ); ?>
							<p class="description"><?php esc_html_e( 'The single accent. Applied site-wide via the --amber CSS variable.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Book CTA label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_book_label', 30 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Colors</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Background', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_color( 'nr_color_bg' ); ?>
							<p class="description"><?php esc_html_e( 'Page canvas. Default: #0B0C10 (warm near-black).', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Ink (text)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_color( 'nr_color_ink' ); ?>
							<p class="description"><?php esc_html_e( 'Primary text color. Default: #F2EFE9 (bone).', 'raveenthiran' ); ?></p></td></tr>
				</table>
				<p class="description"><?php esc_html_e( 'Accent color is in the Branding section above.', 'raveenthiran' ); ?></p>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Studio</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Studio address', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_studio', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Location (short)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_location', 30 ); ?>
							<p class="description"><?php esc_html_e( 'e.g. "Wien, Austria".', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Coordinates', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_coords', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Email', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_email', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Phone', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_phone', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Legal name (Impressum)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_legal_name', 40 ); ?>
							<p class="description"><?php esc_html_e( 'Full legal/registered name if different from the site title. Used in Impressum, Datenschutz, AGB.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'VAT / USt-IdNr (UID)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_uid', 24 ); ?>
							<p class="description"><?php esc_html_e( 'e.g. ATU12345678. Leave blank to show “auf Anfrage”.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Trade / Gewerbe', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_gewerbe', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Signature (Studio page)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_signature', 30 ); ?>
							<p class="description"><?php esc_html_e( 'Your name in a handwritten-style script at the end of the Studio bio. Leave blank to hide.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Instagram URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_instagram', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Behance URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_behance', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Vimeo URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_vimeo', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'LinkedIn URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_linkedin', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Available?', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_available', __( 'Show the "Available" indicator in the top bar', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Availability text', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_avail_text', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Booking URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_booking_url', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Optional. If set, adds a "Book a time →" button on the Enquire page (e.g. a Cal.com link).', 'raveenthiran' ); ?></p></td></tr>
						<tr><th><label><?php esc_html_e( 'Booking embed (Google)', 'raveenthiran' ); ?></label></th>
							<td><?php nr_field_textarea( 'nr_booking_embed', 3 ); ?>
								<p class="description"><?php esc_html_e( 'Optional. Paste the embed code or link of a Google Calendar Appointment Schedule (a real booking page — NOT the read-only calendar view). Place [nr_booking] on any page to show a click-to-load booking calendar. Falls back to the enquiry form when empty.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'WhatsApp number', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_whatsapp', 30 ); ?>
							<p class="description"><?php esc_html_e( 'Optional. Digits only — adds a WhatsApp quick-contact button on the Enquire page.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Press-kit URL', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_presskit_url', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Optional. Link to a downloadable press kit / one-pager (shown in the footer).', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Footer CTA label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_footer_cta', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Instagram grid', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_ig_grid', 5 ); ?>
							<p class="description"><?php esc_html_e( 'One image per line: image_url | post_url — a curated grid on the Studio page (Meta\'s API no longer allows auto-feeds).', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Stats</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Projects', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_stats_proj', 10 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Countries', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_stats_cnty', 10 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Publications', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_stats_pubs', 10 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Awards', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_stats_awd', 10 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Showcase (home)</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_hero_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Auto-advance', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_hero_auto', __( 'Cycle through slides automatically', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Auto-advance interval (ms)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_hero_interval', 10 ); ?>
							<p class="description"><?php esc_html_e( 'How long each slide stays visible. 9000 = 9 seconds.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Max featured projects', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_hero_max', 10 ); ?>
							<p class="description"><?php esc_html_e( 'How many featured projects (featured_on_homepage = 1) to show on the hero. Default: 6.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Work (portfolio archive)</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_work_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Title', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_work_title', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Wrap the bold part in <em>like this</em>.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Projects per page', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_work_per_page', 10 ); ?>
							<p class="description"><?php esc_html_e( 'Min 4, max 96. Default 24. A pager appears at the bottom of the rail once more projects exist than fit on one page.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Studio (about)</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_about_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Title', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_about_title', 80 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Lede', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_about_lede', 3 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Name (byline)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_about_name', 40 ); ?>
							<p class="description"><?php esc_html_e( 'Shown between the lede and the bio as a small signature block. Blank to hide.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Role / location (byline)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_about_role', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Rendered in monospace caps under the name. Blank to hide.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Bio', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_about_bio', 8 ); ?>
							<p class="description"><?php esc_html_e( 'Plain text, blank lines separate paragraphs.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Booking</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_booking_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Title', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_booking_title', 60 ); ?></td></tr>
					<tr><th colspan="2"><h3 style="margin-top:18px"><?php esc_html_e( 'Three-step process', 'raveenthiran' ); ?></h3></th></tr>
					<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
						<tr><th><label><?php printf( esc_html__( 'Step %d title', 'raveenthiran' ), $i ); ?></label></th>
							<td><?php nr_field_text( 'nr_step_' . $i . '_t', 40 ); ?></td></tr>
						<tr><th><label><?php printf( esc_html__( 'Step %d description', 'raveenthiran' ), $i ); ?></label></th>
							<td><?php nr_field_textarea( 'nr_step_' . $i . '_d', 2 ); ?></td></tr>
					<?php endfor; ?>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Hello (contact)</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_contact_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Title', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_contact_title', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Intro', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_contact_intro', 3 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ FAQ</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Eyebrow', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_faq_eyebrow', 40 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Title', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_faq_title', 60 ); ?></td></tr>
				</table>
				<p class="description"><?php esc_html_e( 'FAQ rows are managed in the page content for the "FAQ" page using the block editor — each question becomes a heading, the body below is the answer.', 'raveenthiran' ); ?></p>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Awards &amp; Press</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Awards', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_awards_list', 5 ); ?>
							<p class="description"><?php esc_html_e( 'One per line as: year · title · organisation · url (url optional). Rendered as a list on the About page.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Press', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_press_rows( 'nr_press_list' ); ?>
							<p class="description"><?php esc_html_e( 'Year, publisher and an optional link. The publisher shows as a clickable link on the site (Press wall, “As featured in”, and the About page).', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Clients</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Client names', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_clients_list', 6 ); ?>
							<p class="description"><?php esc_html_e( 'One name per line — no logos. Rendered as a horizontal auto-scrolling marquee between Stats and Awards/Press on the About page.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Footer</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Footer text', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_footer_text', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Copyright / attribution line shown at the bottom of generic pages. Leave blank to hide.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Datenschutz label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_footer_legal_dz', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'AGB label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_footer_legal_ag', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Impressum label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_footer_legal_im', 20 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Mobile tab bar</h2></summary>
				<p class="description"><?php esc_html_e( 'Labels for the 4 tabs shown at the bottom of the screen on small devices.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Tab 1 (Home)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_tab_1_label', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Tab 2 (Portfolio)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_tab_2_label', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Tab 3 (Booking)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_tab_3_label', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Tab 4 (Contact)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_tab_4_label', 20 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ CTA labels</h2></summary>
				<p class="description"><?php esc_html_e( 'Customise the call-to-action buttons used across the theme.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'View project', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_view', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Commission similar', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_commission', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Back to work', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_back', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Send brief', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_send', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Book a session', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_book_session', 30 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Project meta labels</h2></summary>
				<p class="description"><?php esc_html_e( 'Row labels shown on the project detail page.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Client label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_client', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Year label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_year', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Location label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_location', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Discipline label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_discipline', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Frames label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_frames', 20 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Format label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_format', 20 ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Extras</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Tagline', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_tagline', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Short site tagline (used by SEO meta + occasional fallback in templates).', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Floating "Book me" button', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_show_inquiry_fab', __( 'Show on every page (mobile-friendly sticky inquiry CTA)', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( '"Book me" button label', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_fab_label', 30 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'GDPR cookie notice', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_show_cookie_notice', __( 'Show the cookie consent notice', 'raveenthiran' ) ); ?></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ SEO &amp; analytics</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Twitter / X handle', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_twitter_handle', 30 ); ?>
							<p class="description"><?php esc_html_e( 'Without the @. Appears in Twitter card meta for richer link previews.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Google Search Console verification', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_verify_google', 60 ); ?>
							<p class="description"><?php esc_html_e( 'The content="..." value from the HTML tag method.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Bing Webmaster verification', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_verify_bing', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Yandex verification', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_verify_yandex', 60 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Tracking script', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_tracking_script', 6 ); ?>
							<p class="description"><?php esc_html_e( 'Paste your full Plausible / Fathom / GA4 / Cloudflare script(s) — including the <script> tags. Injected into <head>. Logged-in admins are not tracked.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Security</h2></summary>
				<p class="description" style="max-width:780px"><?php esc_html_e( 'Cloudflare Turnstile — a free, privacy-friendly spam shield for the Enquire form (no puzzles for visitors). Create a widget at Cloudflare → Turnstile, then paste the two keys here. Leave blank to disable; the honeypot + rate-limit still run regardless.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Turnstile Site key', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_turnstile_site', 50 ); ?>
							<p class="description"><?php esc_html_e( 'Public key (starts with 0x…). Safe to expose — it renders in the page.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Turnstile Secret key', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_turnstile_secret', 50 ); ?>
							<p class="description"><?php esc_html_e( 'Private key — used server-side to verify submissions. Keep it secret.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Mail (SMTP)</h2></summary>
				<p class="description" style="max-width:820px"><?php esc_html_e( 'Send site email through an authenticated SMTP server (e.g. Google Workspace) so enquiries and auto-replies actually arrive instead of landing in spam. With Google: Username = the real mailbox you log in to (e.g. hq@m1o.at), Password = a 16-char App Password, From = a verified “Send mail as” address (e.g. office@raveenthiran.com).', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Enable SMTP', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_smtp_enable', __( 'Route wp_mail() through the SMTP server below', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Host', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_smtp_host', 40 ); ?>
							<p class="description"><?php esc_html_e( 'Google Workspace: smtp.gmail.com', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Port', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_smtp_port', 8 ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Encryption', 'raveenthiran' ); ?></label></th>
						<td>
							<?php $sec = get_option( 'nr_smtp_secure', 'tls' ); ?>
							<select name="nr_smtp_secure">
								<option value="tls" <?php selected( $sec, 'tls' ); ?>><?php esc_html_e( 'STARTTLS (port 587)', 'raveenthiran' ); ?></option>
								<option value="ssl" <?php selected( $sec, 'ssl' ); ?>><?php esc_html_e( 'SSL/TLS (port 465)', 'raveenthiran' ); ?></option>
							</select>
						</td></tr>
					<tr><th><label><?php esc_html_e( 'Username (real mailbox)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_smtp_user', 40 ); ?>
							<p class="description"><?php esc_html_e( 'The account you authenticate with — NOT the alias. e.g. hq@m1o.at', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'App Password', 'raveenthiran' ); ?></label></th>
						<td>
							<input type="password" name="nr_smtp_pass" value="" autocomplete="new-password"
								placeholder="<?php echo get_option( 'nr_smtp_pass' ) ? esc_attr__( '•••••••• (saved — leave blank to keep)', 'raveenthiran' ) : esc_attr__( '16-char Google App Password', 'raveenthiran' ); ?>"
								class="regular-text" size="40">
							<p class="description"><?php esc_html_e( 'Google Account → Security → 2-Step Verification → App passwords. Or define NR_SMTP_PASS in wp-config.php to keep it out of the database.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'From address', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_smtp_from', 40 ); ?>
							<p class="description"><?php esc_html_e( 'Must be a verified “Send mail as” address on the account. e.g. office@raveenthiran.com', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'From name', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_smtp_fromname', 40 ); ?></td></tr>
				</table>
				<p>
					<?php $test_url = wp_nonce_url( admin_url( 'admin-post.php?action=nr_smtp_test' ), 'nr_smtp_test' ); ?>
					<a href="<?php echo esc_url( $test_url ); ?>" class="button"><?php esc_html_e( 'Send a test email to me', 'raveenthiran' ); ?></a>
					<span class="description"><?php printf( esc_html__( 'Saves nothing — sends a test to %s using the saved settings.', 'raveenthiran' ), esc_html( wp_get_current_user()->user_email ) ); ?></span>
					<?php if ( isset( $_GET['nr_smtp_test'] ) ) :
						$okt = $_GET['nr_smtp_test'] === '1'; ?>
						<span style="margin-left:8px;font-weight:600;color:<?php echo $okt ? '#2a7' : '#c33'; ?>">
							<?php echo $okt ? esc_html__( '✓ Test email sent.', 'raveenthiran' ) : esc_html__( '✗ Send failed — check host/user/password.', 'raveenthiran' ); ?>
						</span>
					<?php endif; ?>
				</p>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Visual effects</h2></summary>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Color mode', 'raveenthiran' ); ?></label></th>
						<td>
							<?php $mode = get_option( 'nr_color_mode', 'dark' ); ?>
							<select name="nr_color_mode">
								<option value="dark"   <?php selected( $mode, 'dark' ); ?>><?php esc_html_e( 'Dark — Catalogue Noir (default)', 'raveenthiran' ); ?></option>
								<option value="light"  <?php selected( $mode, 'light' ); ?>><?php esc_html_e( 'Light — bone-on-cream inversion', 'raveenthiran' ); ?></option>
								<option value="system" <?php selected( $mode, 'system' ); ?>><?php esc_html_e( 'System — follow OS preference', 'raveenthiran' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Dark is the canonical noir look. Light flips the canvas to warm paper for editorial contexts. System honors prefers-color-scheme.', 'raveenthiran' ); ?></p>
						</td></tr>
					<tr><th><label><?php esc_html_e( 'Custom cursor', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_cursor', __( 'Show the custom amber-blob cursor on desktop', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Film grain', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_grain', __( 'Subtle grain + scan-line overlay', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Page-wipe transitions', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_wipe', __( 'Amber wipe between pages', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Ken Burns', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_ken', __( 'Slow scale on the active hero image', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Section anchors', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_anchors', __( 'Big faded §-numerals in corner of sub-pages', 'raveenthiran' ) ); ?></td></tr>
						<tr><th><label><?php esc_html_e( 'Intro preloader', 'raveenthiran' ); ?></label></th>
							<td><?php nr_field_toggle( 'nr_fx_preloader', __( 'Full-screen intro splash with a loading counter (once per session, desktop)', 'raveenthiran' ) ); ?>
								<p class="description"><?php esc_html_e( 'Off by default. It runs on a fixed timer and reveals the page while the hero is still loading, which caused a large layout shift (CLS) on desktop. Leave off unless you specifically want the intro.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'WebGL hero transitions', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_webgl', __( 'Shader-based displacement dissolve between hero slides (desktop, motion-on)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Adds a lightweight WebGL canvas over the homepage hero for a premium "melt" transition. Falls back to the normal crossfade on older devices, reduced-motion, or if WebGL is unavailable — so it is always safe to leave on.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Shared-element page morph', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_viewtrans', __( 'Morph a portfolio card into the project hero on navigation (View Transitions)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Uses the browser View Transitions API for an Awwwards-style card→page morph; browsers without support simply navigate normally. Try it on, click a project from the portfolio.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Card hover distortion', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_distort', __( 'Liquid SVG displacement on portfolio card images on hover (desktop, motion-on)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. A subtle ripple as the cursor enters a card. Pure SVG filter — no effect where unsupported.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Line-reveal headings', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_lines', __( 'Display headings rise line-by-line behind a mask as they enter (motion-on)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Headings still show normally when off or unsupported.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Auto internal linking', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_interlink', __( 'Auto-link the first mention of another project\'s title inside project text (SEO)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Links the first occurrence per project only; never touches headings or existing links.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Non-blocking stylesheet', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_perf_async_css', __( 'Load the main CSS asynchronously (faster mobile FCP/LCP)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Removes ~2s of render-blocking on slow mobile — but can cause a brief unstyled flash on the first paint. Turn it on, hard-reload a few times on mobile, and keep it only if the flash is acceptable.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Generative favicon', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_favicon', __( 'Draw an accent-colored monogram favicon at runtime (used when no Site Icon is set)', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'GPU effects', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_gpu', __( 'Heat-haze idle shader, animated dither field, metaball cursor trail, aperture-iris reveal (desktop, cinematic level)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Loads a separate small script only when on; no WebGL → effects silently skip. Pairs with the Cinematic motion toggle below.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Cinematic motion', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_cinematic', __( 'Enable the cinematic motion layer + a visitor "calm / standard / cinematic" switch (desktop)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. When on, adds card tilt + glare, chromatic-aberration hover, scroll-velocity image shear, decode-reveal eyebrows, drawn-in dividers, split-flap stat counters, and a film-frame scroll indicator — with a bottom-left switch so each visitor can pick calm/standard/cinematic. Honors reduced-motion (defaults to calm). Try it on and reload a portfolio page.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Conversion &amp; editorial</h2></summary>
				<p class="description" style="max-width:820px"><?php esc_html_e( 'Quiet, owned-audience features. Everything here is off by default and only appears on scrolling pages (never the fixed home hero or single-project view). Turn one on, preview a sub-page, keep what fits.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Next open dates', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_avail_dates', 50 ); ?>
							<p class="description"><?php esc_html_e( 'e.g. "Booking April–June 2026 · 2 windows left". Leave blank to hide.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Client logos', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_clients_logos', 5 ); ?>
							<p class="description"><?php esc_html_e( 'One per line as "https://logo.svg | Client name", or just a plain name. Renders a small "Trusted by" strip.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Speculation Rules', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_speculation', __( 'Prerender same-site links on hover for instant navigation (modern browsers)', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Uses the browser Speculation Rules API; ignored where unsupported.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'IndexNow key', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_indexnow_key', 40 ); ?>
							<p class="description"><?php esc_html_e( '8–128 hex characters. When set, publishing a project/journal/page instantly pings Bing & Yandex. A matching key file is served automatically at /<key>.txt.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Conversion &amp; SEO extras</h2></summary>
				<p class="description" style="max-width:820px"><?php esc_html_e( 'IDEAS-200 batch 2+3. Visitor-facing toggles are off by default. Packages/press render via the [nr_packages] and [nr_press] shortcodes (drop them on any page).', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Visitor shortlist', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_shortlist', __( 'Let visitors ♥ projects into a "selection" and send it as a brief (localStorage)', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Exit-intent offer', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_exit', __( 'Show a one-time soft prompt when the cursor leaves toward the tab bar (desktop)', 'raveenthiran' ) ); ?>
							<p><?php nr_field_text( 'nr_exit_text', 60 ); ?></p>
							<p class="description"><?php esc_html_e( 'The prompt line. Blank uses a sensible default. Never shown on single-project pages.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Trust line (Enquire)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_trust_text', 70 ); ?>
							<p class="description"><?php esc_html_e( 'Reassurance shown under the Enquire form, e.g. "Reply within 24h · no spam · your data stays private".', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Packages', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_textarea( 'nr_packages', 5 ); ?>
							<p class="description"><?php esc_html_e( 'One tier per line: "Name | €from 1,200 | half-day; 30 edits; web licence". Render with [nr_packages].', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Seasonal banner', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_promo_text', 70 ); ?>
							<p><?php esc_html_e( 'Auto-hide after', 'raveenthiran' ); ?> <input type="date" name="nr_promo_until" value="<?php echo esc_attr( get_option( 'nr_promo_until', '' ) ); ?>"></p>
							<p class="description"><?php esc_html_e( 'A dismissible bar. Basic <a>/<strong> allowed. Leave the date blank to show indefinitely.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Project <title> template', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_meta_title_tpl', 60 ); ?>
							<p class="description"><?php esc_html_e( 'Tokens: %title% %cat% %year% %site%. e.g. "%title% — %cat% photography, Vienna · %site%". Blank keeps the default title.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Block AI crawlers', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_block_ai', __( 'Disallow GPTBot / CCBot / Google-Extended / ClaudeBot etc. in robots.txt', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'A/B hero CTA (variant B)', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_text( 'nr_cta_view_b', 30 ); ?>
							<p class="description"><?php esc_html_e( 'Alternative label for the hero "View project" button. Blank = no test. Views/clicks are counted per variant (option nr_ab).', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Sample content', 'raveenthiran' ); ?></label></th>
						<td><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nr_sample_content' ), 'nr_sample_content' ) ); ?>"><?php esc_html_e( 'Create demo drafts', 'raveenthiran' ); ?></a>
							<span class="description"><?php esc_html_e( '3 projects + 2 journal entries as drafts — for a fresh install.', 'raveenthiran' ); ?></span></td></tr>
					<tr><th><label><?php esc_html_e( 'Simplified admin', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_admin_simplify', __( 'Hide Posts/Comments/ACF menus, merge the dashboard widgets into one, trim the Appearance menu', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'On by default. Turn off to restore the stock admin.', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Clean uninstall', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_clean_uninstall', __( 'Remove all of this theme’s settings when switching away from Obscura', 'raveenthiran' ) ); ?>
							<p class="description"><?php esc_html_e( 'Off by default. Only deletes Obscura’s own options/transients, only on theme switch.', 'raveenthiran' ); ?></p></td></tr>
				</table>
			</details>

			<details open class="nr-settings__group">
				<summary><h2>§ Security &amp; infrastructure</h2></summary>
				<p class="description" style="max-width:820px"><?php esc_html_e( 'All off by default. Turn CSP on as “report” first and watch the violation log before enforcing.', 'raveenthiran' ); ?></p>
				<table class="form-table" role="presentation">
					<tr><th><label><?php esc_html_e( 'Content-Security-Policy', 'raveenthiran' ); ?></label></th>
						<td><?php $cm = get_option( 'nr_csp_mode', 'off' ); ?>
							<select name="nr_csp_mode">
								<option value="off"     <?php selected( $cm, 'off' ); ?>><?php esc_html_e( 'Off', 'raveenthiran' ); ?></option>
								<option value="report"  <?php selected( $cm, 'report' ); ?>><?php esc_html_e( 'Report-only (safe — never blocks)', 'raveenthiran' ); ?></option>
								<option value="enforce" <?php selected( $cm, 'enforce' ); ?>><?php esc_html_e( 'Enforce', 'raveenthiran' ); ?></option>
							</select>
							<p><?php nr_field_text( 'nr_csp_extra', 50 ); ?></p>
							<p class="description"><?php esc_html_e( 'Extra allowed hosts (space-separated) for any analytics/embeds you add. Violations are logged (option nr_csp_log).', 'raveenthiran' ); ?></p></td></tr>
					<tr><th><label><?php esc_html_e( 'Self-hosted analytics', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_analytics', __( 'Count logged-out pageviews (cookieless, no PII) → dashboard widget', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Service worker', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_sw', __( 'Offline fallback page + stale-while-revalidate caching of theme assets', 'raveenthiran' ) ); ?></td></tr>
					<tr><th><label><?php esc_html_e( 'Virtualised rails', 'raveenthiran' ); ?></label></th>
						<td><?php nr_field_toggle( 'nr_fx_vrails', __( 'Skip rendering off-screen rail cards (content-visibility) — smoother huge archives', 'raveenthiran' ) ); ?></td></tr>
				</table>
			</details>

			<?php submit_button( __( 'Save settings', 'raveenthiran' ) ); ?>
		</form>

		<?php if ( isset( $_GET['nr_import'] ) ) : ?>
			<div class="notice notice-success" style="margin-top:16px"><p><?php printf( esc_html__( 'Imported %d settings.', 'raveenthiran' ), (int) $_GET['nr_import'] ); ?></p></div>
		<?php endif; ?>

		<div style="margin-top:24px;padding-top:16px;border-top:1px solid #ddd">
			<h2 style="font-size:16px"><?php esc_html_e( '§ Backup & migration', 'raveenthiran' ); ?></h2>
			<p class="description" style="max-width:780px"><?php esc_html_e( 'Export every Theme Settings value as a JSON file (e.g. to copy a staging configuration to the live site), or import one back. Only known theme keys are restored; passwords/secrets are included, so keep the file private.', 'raveenthiran' ); ?></p>
			<p>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=nr_settings_export' ), 'nr_settings_export' ) ); ?>"><?php esc_html_e( 'Export settings (JSON)', 'raveenthiran' ); ?></a>
			</p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px">
				<input type="hidden" name="action" value="nr_settings_diff">
				<?php wp_nonce_field( 'nr_settings_import' ); ?>
				<input type="file" name="nr_settings_file" accept="application/json,.json" required>
				<button type="submit" class="button"><?php esc_html_e( 'Import settings', 'raveenthiran' ); ?></button>
			</form>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:24px;padding-top:16px;border-top:1px solid #ddd"
			onsubmit="return confirm('<?php echo esc_js( __( 'Reset ALL theme settings to their defaults? This cannot be undone.', 'raveenthiran' ) ); ?>');">
			<input type="hidden" name="action" value="nr_reset_settings">
			<?php wp_nonce_field( 'nr_reset_settings' ); ?>
			<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Reset all settings to defaults', 'raveenthiran' ); ?></button>
			<span class="description" style="margin-left:8px"><?php esc_html_e( 'Restores every Theme Settings field to its default value.', 'raveenthiran' ); ?></span>
		</form>
	</div>

	<style>
	.nr-settings details{background:#fff;border:1px solid #ddd;border-radius:4px;margin-bottom:14px;padding:10px 18px}
	.nr-settings details[open]{border-color:#2c3338}
	.nr-settings summary{cursor:pointer;list-style:none;outline:0}
	.nr-settings summary h2{display:inline-block;margin:0;font-size:16px;letter-spacing:.04em}
	.nr-settings details summary::before{content:'▸';display:inline-block;margin-right:8px;transition:transform .2s}
	.nr-settings details[open] summary::before{transform:rotate(90deg)}
	.nr-settings .form-table th{width:240px}
	.nr-settings textarea{font-family:Consolas,monospace;font-size:13px}
	</style>
	<?php
}

/* =============================================================
   Auto-create the template pages on first activation / admin load
   so the nav actually leads somewhere on a fresh install.
   Idempotent — runs once, persists a flag in wp_options.
   ============================================================= */
function nr_autocreate_template_pages() {
	if ( ! current_user_can( 'edit_pages' ) ) return;
	if ( get_option( 'nr_pages_seeded' ) === '1' ) return;

	$seeds = [
		// title             => template file                    slug
		[ 'Studio',           'page-about.php',             'about'             ],
		[ 'Booking',          'page-booking.php',           'booking'           ],
		[ 'Hello',            'page-contact.php',           'contact'           ],
		[ 'FAQ',              'page-faq.php',               'faq'               ],
		[ 'Booking confirmed','page-booking-confirmed.php', 'booking-confirmed' ],
	];

	foreach ( $seeds as list( $title, $template, $slug ) ) {
		// Does a page using this template already exist? Skip if yes.
		$existing = get_pages( [
			'meta_key'   => '_wp_page_template',
			'meta_value' => $template,
			'number'     => 1,
			'post_status'=> 'any',
		] );
		if ( ! empty( $existing ) ) continue;

		// Avoid slug collision — use a uniquified slug
		$slug = wp_unique_post_slug( $slug, 0, 'publish', 'page', 0 );

		$id = wp_insert_post( [
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}
	}

	update_option( 'nr_pages_seeded', '1' );
}
add_action( 'admin_init', 'nr_autocreate_template_pages' );

/* If the user wants to re-run (e.g. after deleting a page) — clear the flag */
add_action( 'admin_post_nr_reseed_pages', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_reseed_pages' ) ) wp_die( 'no' );
	delete_option( 'nr_pages_seeded' );
	nr_autocreate_template_pages();
	wp_safe_redirect( admin_url( 'themes.php?page=nr-theme-settings&seeded=1' ) );
	exit;
} );

/* =============================================================
   Helper — find the URL of the first page using a given template.
   Used by the nav fallback so links resolve even if slugs differ.
   ============================================================= */
if ( ! function_exists( 'nr_template_page_url' ) ) {
function nr_template_page_url( $template, $fallback_slug = '' ) {
	$cache_key = 'nr_tpl_url_' . md5( $template );
	$url = wp_cache_get( $cache_key );
	if ( $url !== false ) return $url;

	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => $template,
		'number'     => 1,
		'post_status'=> 'publish',
	] );
	if ( ! empty( $pages ) ) {
		$url = get_permalink( $pages[0]->ID );
	} else {
		$url = home_url( '/' . ltrim( $fallback_slug, '/' ) );
	}
	wp_cache_set( $cache_key, $url, '', 60 );
	return $url;
}
}

/* =============================================================
   Body classes for visual-effect toggles
   ============================================================= */
add_filter( 'body_class', function ( $classes ) {
	if ( get_option( 'nr_fx_cursor',  '1' ) !== '1' ) $classes[] = 'nr-no-cursor';
	if ( get_option( 'nr_fx_grain',   '1' ) !== '1' ) $classes[] = 'nr-no-grain';
	if ( get_option( 'nr_fx_wipe',    '1' ) !== '1' ) $classes[] = 'nr-no-wipe';
	if ( get_option( 'nr_fx_ken',     '1' ) !== '1' ) $classes[] = 'nr-no-ken';
	if ( get_option( 'nr_fx_anchors', '1' ) !== '1' ) $classes[] = 'nr-no-anchors';
	if ( get_option( 'nr_fx_webgl',   '0' ) === '1' ) $classes[] = 'nr-has-webgl';
	if ( get_option( 'nr_fx_distort', '0' ) === '1' ) $classes[] = 'nr-has-distort';
	if ( get_option( 'nr_fx_lines',   '0' ) === '1' ) $classes[] = 'nr-has-lines';
	if ( get_option( 'nr_fx_favicon', '0' ) === '1' ) $classes[] = 'nr-has-favicon';
	if ( get_option( 'nr_fx_cinematic', '0' ) === '1' ) $classes[] = 'nr-cinematic';
	$mode = get_option( 'nr_color_mode', 'dark' );
	if ( $mode === 'light' )  $classes[] = 'nr-light';
	if ( $mode === 'system' ) $classes[] = 'nr-mode-system';
	return $classes;
} );
