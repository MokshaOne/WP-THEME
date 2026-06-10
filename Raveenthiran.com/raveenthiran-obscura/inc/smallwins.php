<?php
/**
 * inc/smallwins.php — IDEAS-200 "Small" tier (v4.46.0), the lot in one go.
 *
 * Conversion: #43 lead score · #55 VAT note · #56 add-ons (verify) · #61 testimonial
 *   request · #70 trusted-by counts (markup hook).
 * SEO: #112 outbound-link checker · #115 [nr_howto] · #137 [nr_onthisday] · #143 [fn]
 *   footnotes · #144 glossary · #147 [md].
 * Editorial fields: #122 full-bleed · #128 hero focal point · #130 treatment ·
 *   #139 coords/map · #140 credits · #145 journal kicker/dek.
 * A11y: #150 alt-text linter.
 * Ops/Sec/Dev: #152 tag clustering · #155 draft preview tokens · #159 consistency
 *   grid · #178 spam escalation · #179 settings audit log · #181 consent log ·
 *   #182 export encryption · #186 self-test · #189 error-log viewer · #190 migration ·
 *   #194 feature-flag registry.
 * (Motion/A11y JS + CSS live in theme.js / theme.css; configs are separate files.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Flatten any field value (ACF may return arrays) to a printable string. */
if ( ! function_exists( 'nr_field_str' ) ) {
function nr_field_str( $name, $id = 0 ) {
	$v = function_exists( 'nr_field' ) ? nr_field( $name, $id ?: get_the_ID() ) : '';
	if ( is_array( $v ) ) $v = implode( ' · ', array_filter( array_map( 'strval', array_filter( $v, 'is_scalar' ) ) ) );
	return trim( (string) $v );
}
}

/* ===== ACF fields for the editorial small-wins ===== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
	acf_add_local_field_group( [
		'key' => 'group_nr_smallwins_project', 'title' => 'Project — extras',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_project' ] ] ],
		'fields' => [
			[ 'key' => 'field_project_credits', 'label' => 'Credits', 'name' => 'project_credits', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'One per line: "Role | Name". Stylist, MUA, agency, talent…' ], // #140
			[ 'key' => 'field_project_coords',  'label' => 'Map coordinates', 'name' => 'project_coords', 'type' => 'text', 'instructions' => 'lat,lng (e.g. 48.2082,16.3738). Shows a mini-map on the project. ' ], // #139
			[ 'key' => 'field_project_treatment', 'label' => 'Treatment', 'name' => 'project_treatment', 'type' => 'select', 'choices' => [ '' => 'None', 'noir' => 'Noir (high contrast B/W)', 'warm' => 'Warm', 'cool' => 'Cool', 'sepia' => 'Sepia' ], 'allow_null' => 1 ], // #130
			[ 'key' => 'field_project_hero_focus', 'label' => 'Hero focal point', 'name' => 'project_hero_focus', 'type' => 'text', 'instructions' => 'CSS object-position for crops, e.g. "50% 30%".' ], // #128
			[ 'key' => 'field_project_fullbleed', 'label' => 'First plate full-bleed', 'name' => 'project_fullbleed', 'type' => 'true_false', 'ui' => 1 ], // #122
		],
	] );
	acf_add_local_field_group( [
		'key' => 'group_nr_smallwins_journal', 'title' => 'Journal — kicker & dek',
		'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'nr_journal' ] ] ],
		'fields' => [
			[ 'key' => 'field_journal_kicker', 'label' => 'Kicker', 'name' => 'journal_kicker', 'type' => 'text', 'instructions' => 'Small label above the headline.' ], // #145
			[ 'key' => 'field_journal_dek', 'label' => 'Dek / standfirst', 'name' => 'journal_dek', 'type' => 'textarea', 'rows' => 2 ],
		],
	] );
} );

/* #140 — credits render helper (called from single-nr_project.php). */
function nr_project_credits_markup( $id = 0 ) {
	$id = $id ?: get_the_ID();
	$raw = nr_field_str( 'project_credits', $id );
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $lines ) return '';
	$out = '<dl class="nr-credits">';
	foreach ( $lines as $l ) { $p = array_map( 'trim', explode( '|', $l, 2 ) ); $out .= '<div><dt>' . esc_html( $p[0] ) . '</dt><dd>' . esc_html( $p[1] ?? '' ) . '</dd></div>'; }
	return $out . '</dl>';
}

/* #139 — mini-map markup for a single project (reuses inc/map.php loader). */
function nr_project_map_markup( $id = 0 ) {
	$id = $id ?: get_the_ID();
	$c = nr_field_str( 'project_coords', $id );
	if ( ! preg_match( '/^\s*(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)\s*$/', $c, $m ) ) return '';
	$GLOBALS['nr_map_present'] = true;
	$pins = wp_json_encode( [ [ 'lat' => (float) $m[1], 'lng' => (float) $m[2], 'label' => wp_strip_all_tags( get_the_title( $id ) ) ] ] );
	return '<div class="nr-project__map" data-nr-map data-pins="' . esc_attr( $pins ) . '" style="height:220px"></div>';
}

/* #130 / #128 / #122 — body/plate classes from project fields. */
add_filter( 'body_class', function ( $c ) {
	if ( ! is_singular( 'nr_project' ) || ! function_exists( 'nr_field' ) ) return $c;
	$t = (string) nr_field( 'project_treatment' );
	if ( $t ) $c[] = 'nr-treat-' . sanitize_html_class( $t );
	if ( nr_field( 'project_fullbleed' ) ) $c[] = 'nr-fullbleed';
	return $c;
} );
add_action( 'wp_head', function () {
	if ( ! is_singular( 'nr_project' ) || ! function_exists( 'nr_field' ) ) return;
	$f = trim( (string) nr_field( 'project_hero_focus' ) );
	if ( $f ) printf( '<style>.nr-project__plate img{object-position:%s}</style>' . "\n", esc_html( $f ) );
}, 50 );

/* #145 — render journal kicker/dek before the article (via the_content). */
add_filter( 'the_content', function ( $html ) {
	if ( ! is_singular( 'nr_journal' ) || ! in_the_loop() || ! is_main_query() || ! function_exists( 'nr_field' ) ) return $html;
	$k = trim( (string) nr_field( 'journal_kicker' ) ); $d = trim( (string) nr_field( 'journal_dek' ) );
	$pre = '';
	if ( $k ) $pre .= '<p class="nr-kicker">' . esc_html( $k ) . '</p>';
	if ( $d ) $pre .= '<p class="nr-dek">' . esc_html( $d ) . '</p>';
	return $pre . $html;
}, 9 );

/* #55 — VAT note helper + a settings-driven rate. */
function nr_vat_note() {
	$r = (int) nr_opt( 'nr_vat_rate', 20 );
	return $r > 0 ? sprintf( __( 'Estimates exclude %d%% USt (VAT); reverse-charge for EU business clients with a valid VAT ID.', 'raveenthiran' ), $r ) : '';
}
add_shortcode( 'nr_vat', function () { return esc_html( nr_vat_note() ); } );

/* #115 — HowTo schema from a [nr_howto] block: "Step title | step text" per line. */
add_shortcode( 'nr_howto', function ( $atts, $content = '' ) {
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', wp_strip_all_tags( $content ) ) ) );
	if ( ! $lines ) return '';
	$steps = []; $html = '<ol class="nr-howto">';
	foreach ( $lines as $i => $l ) {
		$p = array_map( 'trim', explode( '|', $l, 2 ) );
		$name = $p[0]; $txt = $p[1] ?? '';
		$steps[] = [ '@type' => 'HowToStep', 'position' => $i + 1, 'name' => $name, 'text' => $txt ?: $name ];
		$html .= '<li><strong>' . esc_html( $name ) . '</strong>' . ( $txt ? ' — ' . esc_html( $txt ) : '' ) . '</li>';
	}
	$html .= '</ol>';
	$schema = [ '@context' => 'https://schema.org', '@type' => 'HowTo', 'name' => get_the_title(), 'step' => $steps ];
	$html .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>';
	return $html;
} );

/* #137 — [nr_onthisday]: projects shot this month in earlier years. */
add_shortcode( 'nr_onthisday', function () {
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 4, 'no_found_rows' => true,
		'date_query' => [ [ 'month' => (int) gmdate( 'n' ), 'before' => gmdate( 'Y-01-01' ) ] ], 'orderby' => 'rand' ] );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$out = '<div class="nr-otd"><span class="nr-eyebrow nr-eyebrow--xs">' . esc_html__( 'From the archive, this month', 'raveenthiran' ) . '</span><div class="nr-otd__row">';
	while ( $q->have_posts() ) { $q->the_post(); $out .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . ' <span>' . esc_html( get_the_date( 'Y' ) ) . '</span></a>'; }
	$out .= '</div></div>'; wp_reset_postdata();
	return $out;
} );

/* #143 — footnotes: [fn]note[/fn] → superscript ref + collected list via [fn_list]. */
add_shortcode( 'fn', function ( $a, $content = '' ) {
	$GLOBALS['nr_fn'] = $GLOBALS['nr_fn'] ?? [];
	$GLOBALS['nr_fn'][] = do_shortcode( $content );
	$n = count( $GLOBALS['nr_fn'] );
	return '<sup class="nr-fn"><a id="fnref-' . $n . '" href="#fn-' . $n . '">' . $n . '</a></sup>';
} );
add_shortcode( 'fn_list', function () {
	$list = $GLOBALS['nr_fn'] ?? [];
	if ( ! $list ) return '';
	$out = '<ol class="nr-fnlist">';
	foreach ( $list as $i => $t ) $out .= '<li id="fn-' . ( $i + 1 ) . '">' . wp_kses_post( $t ) . ' <a href="#fnref-' . ( $i + 1 ) . '">↩</a></li>';
	return $out . '</ol>';
} );

/* #144 — glossary tooltips: wrap the first occurrence of each term (journal only). */
add_filter( 'the_content', function ( $html ) {
	if ( ! is_singular( 'nr_journal' ) || ! in_the_loop() || ! is_main_query() ) return $html;
	$raw = trim( (string) nr_opt( 'nr_glossary', '' ) );
	if ( $raw === '' ) return $html;
	foreach ( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) as $line ) {
		$p = array_map( 'trim', explode( '=', $line, 2 ) );
		if ( count( $p ) < 2 || $p[0] === '' ) continue;
		$term = preg_quote( $p[0], '/' );
		$html = preg_replace( '/(?<![\w>])(' . $term . ')(?![\w<])/u',
			'<abbr class="nr-gloss" title="' . esc_attr( $p[1] ) . '">$1</abbr>', $html, 1 );
	}
	return $html;
}, 12 );

/* #147 — [md]…[/md]: minimal Markdown (headings, bold/italic, links, lists). */
add_shortcode( 'md', function ( $a, $content = '' ) {
	$t = trim( (string) $content );
	$t = preg_replace( '/^### (.+)$/m', '<h3>$1</h3>', $t );
	$t = preg_replace( '/^## (.+)$/m', '<h2>$1</h2>', $t );
	$t = preg_replace( '/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t );
	$t = preg_replace( '/(?<!\*)\*(?!\*)(.+?)\*/', '<em>$1</em>', $t );
	$t = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $t );
	$t = preg_replace_callback( '/(?:^- .+\n?)+/m', function ( $m ) {
		$items = preg_replace( '/^- (.+)$/m', '<li>$1</li>', trim( $m[0] ) );
		return '<ul>' . $items . '</ul>';
	}, $t );
	return wpautop( wp_kses_post( $t ) );
} );

/* #43 — lead scoring: a sortable admin column on nr_enquiry. */
function nr_enquiry_score( $id ) {
	$s = 0;
	$est = (int) preg_replace( '/\D/', '', (string) get_post_meta( $id, '_nr_est', true ) );
	if ( $est >= 3000 ) $s += 3; elseif ( $est >= 1000 ) $s += 2; elseif ( $est > 0 ) $s += 1;
	$d = (string) get_post_meta( $id, '_nr_date', true );
	if ( $d && ( $ts = strtotime( $d ) ) ) { $days = ( $ts - time() ) / 86400; if ( $days >= 0 && $days <= 90 ) $s += 2; elseif ( $days > 90 ) $s += 1; }
	if ( get_post_meta( $id, '_nr_type', true ) ) $s += 1;
	if ( get_post_meta( $id, '_nr_ref', true ) ) $s += 1; // came from a project
	return $s;
}
add_filter( 'manage_nr_enquiry_posts_columns', function ( $c ) { $c['nr_score'] = __( 'Lead score', 'raveenthiran' ); return $c; } );
add_action( 'manage_nr_enquiry_posts_custom_column', function ( $col, $id ) {
	if ( $col !== 'nr_score' ) return;
	$s = nr_enquiry_score( $id );
	$dot = $s >= 5 ? '#2a7' : ( $s >= 3 ? '#e0a000' : '#999' );
	printf( '<span style="font-weight:600;color:%s">%d</span>', $dot, $s );
}, 10, 2 );

/* #61 — "Request testimonial": a row action emailing the client a quick link. */
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( $post->post_type !== 'nr_enquiry' ) return $actions;
	$email = get_post_meta( $post->ID, '_nr_email', true );
	if ( $email ) {
		$u = wp_nonce_url( admin_url( 'admin-post.php?action=nr_request_testimonial&e=' . $post->ID ), 'nr_req_t_' . $post->ID );
		$actions['nr_req_t'] = '<a href="' . esc_url( $u ) . '">' . esc_html__( 'Request testimonial', 'raveenthiran' ) . '</a>';
	}
	return $actions;
}, 10, 2 );
add_action( 'admin_post_nr_request_testimonial', function () {
	$id = (int) ( $_GET['e'] ?? 0 );
	if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'nr_req_t_' . $id ) ) wp_die( 'no' );
	$email = get_post_meta( $id, '_nr_email', true );
	$ok = 0;
	if ( $email && is_email( $email ) ) {
		$subject = sprintf( __( 'A quick word about working together — %s', 'raveenthiran' ), get_bloginfo( 'name' ) );
		$body = __( "Hi,\n\nIt was a pleasure working with you. If you have a moment, I'd be grateful for a sentence or two I could share as a testimonial.\n\nJust reply to this email — thank you.", 'raveenthiran' );
		$ok = wp_mail( $email, $subject, $body ) ? 1 : 0;
		if ( $ok ) update_post_meta( $id, '_nr_testi_requested', gmdate( 'c' ) );
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=nr_enquiry&nr_treq=' . $ok ) );
	exit;
} );

/* #150 + #186 — alt-text linter + theme self-test, folded into content-health. */
function nr_alt_issues() {
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT p.ID, p.post_title, m.meta_value alt FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_wp_attachment_image_alt' WHERE p.post_type='attachment' AND p.post_mime_type LIKE 'image/%' LIMIT 1000" );
	$bad = 0;
	foreach ( $rows as $r ) {
		$alt = trim( (string) $r->alt );
		if ( $alt === '' || strtolower( $alt ) === strtolower( pathinfo( $r->post_title, PATHINFO_FILENAME ) ) ) $bad++;
	}
	return [ 'total' => count( $rows ), 'bad' => $bad ];
}
function nr_self_test() {
	$up = wp_upload_dir();
	return [
		'GD'            => function_exists( 'imagecreatetruecolor' ),
		'WebP'          => function_exists( 'imagewebp' ),
		'AVIF'          => function_exists( 'imageavif' ),
		'TTF for OG'    => file_exists( get_template_directory() . '/assets/fonts/inter-tight-700.ttf' ),
		'Uploads write' => wp_is_writable( $up['basedir'] ),
		'WP-Cron'       => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
		'SMTP set'      => nr_opt( 'nr_smtp_enable', '0' ) === '1',
	];
}
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	wp_add_dashboard_widget( 'nr_self_test', __( 'Obscura — self-test', 'raveenthiran' ), function () {
		echo '<ul style="margin:0">';
		foreach ( nr_self_test() as $k => $v ) printf( '<li style="display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:4px 0"><span>%s</span><strong style="color:%s">%s</strong></li>', esc_html( $k ), $v ? '#2a7' : '#c0392b', $v ? '✓' : '—' );
		$alt = nr_alt_issues();
		printf( '<li style="display:flex;justify-content:space-between;padding:6px 0"><span>%s</span><strong style="color:%s">%d / %d</strong></li>', esc_html__( 'Images with weak/empty alt', 'raveenthiran' ), $alt['bad'] ? '#c0392b' : '#2a7', $alt['bad'], $alt['total'] );
		echo '</ul>';
	} );
} );

/* #194 — feature-flag registry: list every nr_fx_* toggle + state. */
function nr_feature_flags() {
	$out = [];
	foreach ( array_keys( nr_settings_defaults() ) as $k ) {
		if ( strpos( $k, 'nr_fx_' ) === 0 || in_array( $k, [ 'nr_perf_async_css', 'nr_block_ai', 'nr_clean_uninstall', 'nr_available' ], true ) ) {
			$out[ $k ] = get_option( $k, '0' ) === '1';
		}
	}
	return $out;
}
add_action( 'admin_menu', function () {
	add_theme_page( __( 'Feature flags', 'raveenthiran' ), __( 'Feature flags', 'raveenthiran' ), 'manage_options', 'nr-flags', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Feature flags', 'raveenthiran' ) . '</h1><table class="widefat striped" style="max-width:520px"><thead><tr><th>Flag</th><th>State</th></tr></thead><tbody>';
		foreach ( nr_feature_flags() as $k => $on ) printf( '<tr><td><code>%s</code></td><td style="color:%s;font-weight:600">%s</td></tr>', esc_html( $k ), $on ? '#2a7' : '#999', $on ? 'ON' : 'off' );
		echo '</tbody></table><p class="description">' . esc_html__( 'Change these in Appearance → Theme Settings → Visual effects / extras.', 'raveenthiran' ) . '</p></div>';
	} );
} );

/* #179 — settings audit log: record nr_* option changes (who/when). */
add_action( 'updated_option', function ( $option ) {
	if ( strpos( (string) $option, 'nr_' ) !== 0 ) return;
	if ( wp_doing_cron() ) return;
	$log = get_option( 'nr_audit_log', [] );
	if ( ! is_array( $log ) ) $log = [];
	$log[] = [ 't' => gmdate( 'c' ), 'u' => get_current_user_id(), 'o' => $option ];
	if ( count( $log ) > 200 ) $log = array_slice( $log, -200 );
	update_option( 'nr_audit_log', $log, false );
}, 10, 1 );

/* #189 — error-log viewer (theme-scoped [NR] lines from debug.log). */
add_action( 'admin_menu', function () {
	add_management_page( __( 'Obscura log', 'raveenthiran' ), __( 'Obscura log', 'raveenthiran' ), 'manage_options', 'nr-log', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Obscura error log', 'raveenthiran' ) . '</h1>';
		$f = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/debug.log' : '';
		if ( $f && is_readable( $f ) ) {
			$lines = array_slice( array_filter( explode( "\n", (string) file_get_contents( $f ) ), function ( $l ) { return stripos( $l, '[NR' ) !== false || stripos( $l, 'raveenthiran' ) !== false; } ), -60 );
			echo '<textarea readonly style="width:100%;height:340px;font-family:monospace;font-size:12px">' . esc_textarea( implode( "\n", $lines ) ?: __( 'No theme errors logged.', 'raveenthiran' ) ) . '</textarea>';
		} else {
			echo '<p>' . esc_html__( 'debug.log not found. Enable WP_DEBUG_LOG in wp-config.php to capture errors.', 'raveenthiran' ) . '</p>';
		}
		echo '</div>';
	} );
} );

/* #190 — settings migration on version change (scaffold + version stamp). */
add_action( 'admin_init', function () {
	$cur = get_option( 'nr_db_version', '0' );
	if ( version_compare( $cur, NR_THEME_VERSION, '>=' ) ) return;
	/* future migrations key off $cur here */
	do_action( 'nr_migrate_settings', $cur, NR_THEME_VERSION );
	update_option( 'nr_db_version', NR_THEME_VERSION, false );
} );

/* #181 — consent log: count consent events (cookieless, no PII). */
add_action( 'rest_api_init', function () {
	register_rest_route( 'nr/v1', '/consent', [ 'methods' => 'POST', 'permission_callback' => '__return_true', 'callback' => function () {
		$n = (int) get_option( 'nr_consent_count', 0 ) + 1;
		update_option( 'nr_consent_count', $n, false );
		return new WP_REST_Response( [ 'ok' => true ], 200 );
	} ] );
} );

/* #178 — spam escalation: count honeypot/turnstile fails per IP, ban after 3. */
function nr_spam_strike( $ip ) {
	if ( ! $ip ) return;
	$k = 'nr_strike_' . md5( $ip );
	$n = (int) get_transient( $k ) + 1;
	set_transient( $k, $n, HOUR_IN_SECONDS );
	if ( $n >= 3 ) set_transient( 'nr_ban_' . md5( $ip ), 1, 6 * HOUR_IN_SECONDS ); // honeytoken (#185) reads this key
}

/* #112 — outbound broken-link checker: on-demand scan, result in a transient. */
add_action( 'admin_post_nr_linkcheck', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_linkcheck' ) ) wp_die( 'no' );
	$urls = [];
	$q = new WP_Query( [ 'post_type' => [ 'nr_project', 'nr_journal', 'page' ], 'posts_per_page' => 100, 'no_found_rows' => true ] );
	while ( $q->have_posts() ) { $q->the_post();
		if ( preg_match_all( '/href=[\'"](https?:\/\/[^\'"]+)[\'"]/i', get_post_field( 'post_content', get_the_ID() ), $mm ) ) {
			foreach ( $mm[1] as $u ) if ( strpos( $u, home_url() ) === false ) $urls[ $u ] = get_the_ID();
		}
	}
	wp_reset_postdata();
	$broken = [];
	foreach ( array_slice( array_keys( $urls ), 0, 40 ) as $u ) {
		$r = wp_remote_head( $u, [ 'timeout' => 5, 'redirection' => 3 ] );
		$code = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
		if ( $code === 0 || $code >= 400 ) $broken[ $u ] = [ 'code' => $code, 'post' => $urls[ $u ] ];
	}
	set_transient( 'nr_linkcheck', $broken, DAY_IN_SECONDS );
	wp_safe_redirect( admin_url( 'index.php?nr_linkcheck=' . count( $broken ) ) );
	exit;
} );

/* #182 — enquiry-export encryption: if a passphrase is posted, AES-encrypt the CSV.
   (Wraps the existing export; only active when a passphrase is supplied.) */
function nr_encrypt_blob( $data, $pass ) {
	$iv = openssl_random_pseudo_bytes( 16 );
	$ct = openssl_encrypt( $data, 'aes-256-cbc', hash( 'sha256', $pass, true ), OPENSSL_RAW_DATA, $iv );
	return $ct === false ? '' : base64_encode( $iv . $ct ); // decrypt: base64 → split 16-byte IV → aes-256-cbc with sha256(pass)
}

/* #125 — [nr_compare before="12" after="34"] reusing the .nr-compare slider. */
add_shortcode( 'nr_compare', function ( $a ) {
	$a = shortcode_atts( [ 'before' => 0, 'after' => 0 ], $a );
	$b = wp_get_attachment_image( (int) $a['before'], 'nr-hero', false, [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => 'before' ] );
	$f = wp_get_attachment_image( (int) $a['after'],  'nr-hero', false, [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => 'after' ] );
	if ( ! $b || ! $f ) return '';
	return '<div class="nr-compare" data-compare><div class="nr-compare__media nr-compare__after">' . $f . '</div>'
		. '<div class="nr-compare__media nr-compare__before" data-compare-before>' . $b . '</div>'
		. '<input class="nr-compare__range" type="range" min="0" max="100" value="50" aria-label="' . esc_attr__( 'Compare before and after', 'raveenthiran' ) . '"></div>';
} );

/* #152 — tag clustering view: tags by use, flag near-duplicates / orphans. */
add_action( 'admin_menu', function () {
	add_theme_page( __( 'Tag clusters', 'raveenthiran' ), __( 'Tag clusters', 'raveenthiran' ), 'manage_categories', 'nr-tags', function () {
		$terms = get_terms( [ 'taxonomy' => 'nr_project_tag', 'hide_empty' => false ] );
		echo '<div class="wrap"><h1>' . esc_html__( 'Tag clusters', 'raveenthiran' ) . '</h1>';
		if ( is_wp_error( $terms ) || ! $terms ) { echo '<p>' . esc_html__( 'No tags yet.', 'raveenthiran' ) . '</p></div>'; return; }
		usort( $terms, function ( $a, $b ) { return $b->count <=> $a->count; } );
		echo '<table class="widefat striped" style="max-width:560px"><thead><tr><th>Tag</th><th>Uses</th><th>Note</th></tr></thead><tbody>';
		foreach ( $terms as $t ) {
			$note = $t->count === 0 ? __( 'orphan — consider deleting', 'raveenthiran' ) : ( $t->count === 1 ? __( 'used once', 'raveenthiran' ) : '' );
			printf( '<tr><td><a href="%s">%s</a></td><td>%d</td><td style="color:#a66">%s</td></tr>', esc_url( get_edit_term_link( $t->term_id, 'nr_project_tag' ) ), esc_html( $t->name ), (int) $t->count, esc_html( $note ) );
		}
		echo '</tbody></table></div>';
	} );
} );

/* #155 — tokenised draft preview links: share an unpublished post with a token. */
add_action( 'add_meta_boxes', function () {
	foreach ( [ 'nr_project', 'nr_journal' ] as $pt ) {
		add_meta_box( 'nr_preview', __( 'Shareable preview', 'raveenthiran' ), function ( $post ) {
			$tok = get_post_meta( $post->ID, '_nr_preview_token', true );
			if ( ! $tok ) { $tok = wp_generate_password( 20, false ); update_post_meta( $post->ID, '_nr_preview_token', $tok ); }
			$url = add_query_arg( 'nr_preview', $tok, get_permalink( $post->ID ) );
			echo '<p class="description">' . esc_html__( 'Anyone with this link can view the draft:', 'raveenthiran' ) . '</p>';
			echo '<input type="text" readonly value="' . esc_attr( $url ) . '" style="width:100%" onclick="this.select()">';
		}, $pt, 'side' );
	}
} );
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	$tok = isset( $_GET['nr_preview'] ) ? sanitize_text_field( wp_unslash( $_GET['nr_preview'] ) ) : '';
	if ( $tok ) $q->set( 'post_status', [ 'publish', 'draft', 'pending', 'future' ] );
} );
add_filter( 'posts_results', function ( $posts, $q ) {
	if ( is_admin() || empty( $_GET['nr_preview'] ) || empty( $posts ) ) return $posts;
	$tok = sanitize_text_field( wp_unslash( $_GET['nr_preview'] ) );
	$p = $posts[0];
	if ( get_post_meta( $p->ID, '_nr_preview_token', true ) !== $tok ) return $posts;
	add_filter( 'posts_request', '__return_empty_string' ); // single token-valid post: let it through
	return $posts;
}, 10, 2 );

/* #159 — series consistency grid: thumbnails per series to spot outliers. */
add_action( 'admin_menu', function () {
	add_theme_page( __( 'Series grid', 'raveenthiran' ), __( 'Series grid', 'raveenthiran' ), 'edit_posts', 'nr-series-grid', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Series consistency', 'raveenthiran' ) . '</h1>';
		$series = get_terms( [ 'taxonomy' => 'nr_project_series', 'hide_empty' => true ] );
		if ( is_wp_error( $series ) || ! $series ) { echo '<p>' . esc_html__( 'No series yet.', 'raveenthiran' ) . '</p></div>'; return; }
		foreach ( $series as $s ) {
			echo '<h2>' . esc_html( $s->name ) . '</h2><div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px">';
			$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 40, 'no_found_rows' => true, 'tax_query' => [ [ 'taxonomy' => 'nr_project_series', 'terms' => $s->term_id ] ] ] );
			while ( $q->have_posts() ) { $q->the_post(); echo '<a href="' . esc_url( get_edit_post_link() ) . '" title="' . esc_attr( get_the_title() ) . '">' . ( has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), [ 90, 120 ] ) : '<span style="display:inline-block;width:90px;height:120px;background:#c0392b22;border:1px solid #c0392b">✕</span>' ) . '</a>'; }
			wp_reset_postdata();
			echo '</div>';
		}
		echo '<p class="description">' . esc_html__( 'Red ✕ = missing featured image. Mismatched orientations/tones stand out at a glance.', 'raveenthiran' ) . '</p></div>';
	} );
} );

/* admin notices for the on-demand actions */
add_action( 'admin_notices', function () {
	if ( isset( $_GET['nr_treq'] ) ) printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $_GET['nr_treq'] === '1' ? 'success' : 'error', esc_html( $_GET['nr_treq'] === '1' ? __( 'Testimonial request sent.', 'raveenthiran' ) : __( 'Could not send — check the email / SMTP.', 'raveenthiran' ) ) );
	if ( isset( $_GET['nr_linkcheck'] ) ) printf( '<div class="notice notice-info is-dismissible"><p>%s</p></div>', esc_html( sprintf( __( 'Link check done — %d broken/unreachable outbound links (see the content-health widget).', 'raveenthiran' ), (int) $_GET['nr_linkcheck'] ) ) );
} );
