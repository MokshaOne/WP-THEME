<?php
/**
 * Admin UX & robustness (review batch 4).
 *   #41 Journal admin column (category + featured-image check)
 *   #45 Theme-health dashboard widget
 *   #47 Enquiry CSV export
 *   #43 Light settings normalisation (UID + URL fields)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── #41 — Journal list columns ───────────────────────────────── */
add_filter( 'manage_nr_journal_posts_columns', function ( $cols ) {
	$out = [];
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( $k === 'title' ) {
			$out['nr_jcat']   = __( 'Category', 'raveenthiran' );
			$out['nr_jthumb'] = __( 'Image', 'raveenthiran' );
		}
	}
	return $out;
} );
add_action( 'manage_nr_journal_posts_custom_column', function ( $col, $post_id ) {
	if ( $col === 'nr_jcat' ) {
		$t = get_the_terms( $post_id, 'nr_journal_cat' );
		echo ( $t && ! is_wp_error( $t ) ) ? esc_html( implode( ', ', wp_list_pluck( $t, 'name' ) ) ) : '—';
	} elseif ( $col === 'nr_jthumb' ) {
		echo has_post_thumbnail( $post_id )
			? '<span style="color:#2a7">✓</span>'
			: '<span style="color:#c33">— ' . esc_html__( 'missing', 'raveenthiran' ) . '</span>';
	}
}, 10, 2 );

/* ── #45 — Theme-health dashboard widget ──────────────────────── */
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	wp_add_dashboard_widget( 'nr_theme_health', __( 'Theme health', 'raveenthiran' ), 'nr_theme_health_render' );
} );

function nr_theme_health_render() {
	$rows = [];
	$rows[] = [ __( 'Pretty permalinks', 'raveenthiran' ), (bool) get_option( 'permalink_structure' ), __( 'Settings → Permalinks → Save once (needed for /portfolio, /journal, sitemap).', 'raveenthiran' ) ];
	$rows[] = [ __( 'SMTP email', 'raveenthiran' ), nr_opt( 'nr_smtp_enable', '0' ) === '1', __( 'Theme Settings → § Mail (SMTP) so enquiries don’t go to spam.', 'raveenthiran' ) ];
	$rows[] = [ __( 'WebP support (GD)', 'raveenthiran' ), function_exists( 'imagewebp' ), __( 'Server lacks GD WebP — images stay JPEG.', 'raveenthiran' ) ];
	$rows[] = [ __( 'Site Icon (PWA/favicon)', 'raveenthiran' ), ( function_exists( 'has_site_icon' ) && has_site_icon() ), __( 'Customizer → Site Identity → Site Icon.', 'raveenthiran' ) ];
	$rows[] = [ __( 'Turnstile spam shield', 'raveenthiran' ), trim( (string) nr_opt( 'nr_turnstile_site', '' ) ) !== '', __( 'Optional — add keys in § Security.', 'raveenthiran' ) ];
	foreach ( [ 'enquire' => 'Enquire', 'about' => 'Studio', 'impressum' => 'Impressum', 'datenschutz' => 'Datenschutz', 'agb' => 'AGB' ] as $slug => $lbl ) {
		$rows[] = [ sprintf( __( '%s page', 'raveenthiran' ), $lbl ), (bool) get_page_by_path( $slug ), sprintf( __( 'Create a page with slug “%s”.', 'raveenthiran' ), $slug ) ];
	}

	echo '<ul style="margin:0">';
	foreach ( $rows as $r ) {
		$ok = (bool) $r[1];
		printf(
			'<li style="display:flex;gap:8px;align-items:baseline;margin:5px 0;font-size:13px">'
			. '<span style="flex:0 0 16px;color:%s;font-weight:700">%s</span>'
			. '<span style="flex:1"><strong>%s</strong>%s</span></li>',
			$ok ? '#2a7' : '#d98300',
			$ok ? '✓' : '!',
			esc_html( $r[0] ),
			$ok ? '' : ' — <span style="color:#646970">' . esc_html( $r[2] ) . '</span>'
		);
	}
	echo '</ul>';
}

/* ── #47 — Enquiry CSV export ─────────────────────────────────── */
add_action( 'admin_post_nr_export_enquiries', function () {
	if ( ! current_user_can( 'edit_posts' ) || ! check_admin_referer( 'nr_export_enq' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'raveenthiran' ) );
	}
	if ( ! post_type_exists( 'nr_enquiry' ) ) wp_die( esc_html__( 'No enquiries.', 'raveenthiran' ) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=enquiries-' . date_i18n( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM for Excel
	fputcsv( $out, [ 'Date', 'Name', 'Email', 'Type', 'Estimate', 'Preferred date', 'From project', 'Source', 'Message' ] );

	$ids = get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => -1, 'post_status' => 'publish', 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC' ] );
	foreach ( $ids as $id ) {
		fputcsv( $out, [
			get_the_date( 'Y-m-d H:i', $id ),
			get_the_title( $id ),
			get_post_meta( $id, '_nr_email', true ),
			get_post_meta( $id, '_nr_type', true ),
			get_post_meta( $id, '_nr_est', true ),
			get_post_meta( $id, '_nr_date', true ),
			get_post_meta( $id, '_nr_ref', true ),
			get_post_meta( $id, '_nr_referrer', true ),
			wp_strip_all_tags( get_post_field( 'post_content', $id ) ),
		] );
	}
	fclose( $out );
	exit;
} );

/* Export URL for the insights widget / enquiry list. */
function nr_enquiry_export_url() {
	return wp_nonce_url( admin_url( 'admin-post.php?action=nr_export_enquiries' ), 'nr_export_enq' );
}

/* ── #50 — post-activation onboarding notice ──────────────────── */
add_action( 'after_switch_theme', function () { update_option( 'nr_onboard', 1 ); } );

add_action( 'admin_init', function () {
	if ( isset( $_GET['nr_dismiss_onboard'] ) && current_user_can( 'manage_options' )
		&& wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_onboard' ) ) {
		delete_option( 'nr_onboard' );
		wp_safe_redirect( remove_query_arg( [ 'nr_dismiss_onboard', '_wpnonce' ] ) );
		exit;
	}
} );

add_action( 'admin_notices', function () {
	if ( ! get_option( 'nr_onboard' ) || ! current_user_can( 'manage_options' ) ) return;
	$dismiss  = wp_nonce_url( add_query_arg( 'nr_dismiss_onboard', 1 ), 'nr_onboard' );
	$settings = admin_url( 'themes.php?page=nr-theme-settings' );
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Obscura — quick setup', 'raveenthiran' ) . '</strong></p><ol style="margin:.4em 0 .8em 1.4em">';
	echo '<li>' . wp_kses_post( __( 'Create pages with the slugs <code>enquire</code>, <code>about</code>, <code>impressum</code>, <code>datenschutz</code>, <code>agb</code>.', 'raveenthiran' ) ) . '</li>';
	echo '<li>' . esc_html__( 'Settings → Permalinks → Save once (flushes rewrites for /portfolio, /journal, sitemap).', 'raveenthiran' ) . '</li>';
	echo '<li>' . esc_html__( 'Appearance → Theme Settings → fill in studio info, email and SMTP.', 'raveenthiran' ) . '</li>';
	echo '<li>' . esc_html__( 'Tools → Generate WebP, then review the “Theme health” widget on the dashboard.', 'raveenthiran' ) . '</li>';
	echo '</ol><p><a href="' . esc_url( $settings ) . '" class="button button-primary">' . esc_html__( 'Theme Settings', 'raveenthiran' ) . '</a> '
		. '<a href="' . esc_url( $dismiss ) . '" class="button">' . esc_html__( 'Dismiss', 'raveenthiran' ) . '</a></p></div>';
} );

/* ── #43 — light settings normalisation ───────────────────────── */
add_filter( 'pre_update_option_nr_uid', function ( $v ) {
	return strtoupper( preg_replace( '/\s+/', '', (string) $v ) );
} );
foreach ( [ 'nr_booking_url', 'nr_presskit_url', 'nr_instagram', 'nr_behance', 'nr_vimeo', 'nr_linkedin' ] as $nr_url_key ) {
	add_filter( "pre_update_option_{$nr_url_key}", function ( $v ) {
		$v = trim( (string) $v );
		if ( $v !== '' && ! preg_match( '#^https?://#i', $v ) ) $v = 'https://' . ltrim( $v, '/' );
		return $v;
	} );
}
unset( $nr_url_key );
