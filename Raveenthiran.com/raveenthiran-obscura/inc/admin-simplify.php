<?php
/**
 * inc/admin-simplify.php — declutter wp-admin for a one-person studio (v4.48).
 *
 * Toggleable (nr_admin_simplify, default ON — revert in Theme Settings):
 *  - Hides menus the theme doesn't use: Posts (journal replaces it) and Comments.
 *  - Removes the stock dashboard widgets (Quick Draft, WP Events & News,
 *    Activity, At a Glance — "Studio overview" covers it).
 *  - Merges the four theme widgets (Theme health · Content health · Self-test ·
 *    Field metrics) into ONE "Obscura — health & metrics" widget with sections.
 *  - Trims the Appearance submenu: Feature flags / Tag clusters / Series grid
 *    move out of the menu (still reachable via links on Theme Settings) and the
 *    Theme File Editor entry is removed.
 *  - Hides the ACF menu (fields are registered in code; UI invites accidents).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function nr_admin_simple() { return get_option( 'nr_admin_simplify', '1' ) === '1'; }

/* ── menus ── */
add_action( 'admin_menu', function () {
	if ( ! nr_admin_simple() ) return;
	remove_menu_page( 'edit.php' );            // Posts — the Journal CPT is the blog
	remove_menu_page( 'edit-comments.php' );   // Comments — portfolio has none
	remove_menu_page( 'edit.php?post_type=acf-field-group' ); // ACF UI (fields live in code)
	remove_submenu_page( 'themes.php', 'theme-editor.php' );
	// utility pages out of the Appearance menu (pages stay registered + reachable)
	remove_submenu_page( 'themes.php', 'nr-flags' );
	remove_submenu_page( 'themes.php', 'nr-tags' );
	remove_submenu_page( 'themes.php', 'nr-series-grid' );
}, 999 );
add_action( 'wp_before_admin_bar_render', function () {
	if ( ! nr_admin_simple() ) return;
	global $wp_admin_bar;
	$wp_admin_bar->remove_node( 'comments' );
	$wp_admin_bar->remove_node( 'new-post' );
} );

/* ── dashboard ── */
add_action( 'wp_dashboard_setup', function () {
	if ( ! nr_admin_simple() ) return;
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	// fold the four theme widgets into one
	remove_meta_box( 'nr_theme_health', 'dashboard', 'normal' );
	remove_meta_box( 'nr_content_health', 'dashboard', 'normal' );
	remove_meta_box( 'nr_self_test', 'dashboard', 'normal' );
	remove_meta_box( 'nr_field_metrics', 'dashboard', 'normal' );
	wp_add_dashboard_widget( 'nr_combined', __( 'Obscura — health & metrics', 'raveenthiran' ), 'nr_combined_widget' );
}, 99 );

function nr_combined_widget() {
	$sections = [
		[ __( 'Theme health', 'raveenthiran' ),   'nr_theme_health_render',  true  ],
		[ __( 'Content health', 'raveenthiran' ), 'nr_content_health_widget', false ],
		[ __( 'Self-test', 'raveenthiran' ),      null,                       false ],
		[ __( 'Field metrics (CWV · funnel)', 'raveenthiran' ), null,         false ],
	];
	echo '<style>.nr-acc{border:1px solid #e2e4e7;border-radius:4px;margin-bottom:8px}.nr-acc summary{cursor:pointer;padding:8px 10px;font-weight:600;outline:0}.nr-acc>div{padding:4px 12px 10px}</style>';
	foreach ( $sections as $i => $s ) {
		printf( '<details class="nr-acc"%s><summary>%s</summary><div>', $s[2] ? ' open' : '', esc_html( $s[0] ) );
		if ( $i === 2 ) { // self-test (closure in smallwins — re-render inline)
			if ( function_exists( 'nr_self_test' ) ) {
				echo '<ul style="margin:0">';
				foreach ( nr_self_test() as $k => $v ) printf( '<li style="display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:3px 0"><span>%s</span><strong style="color:%s">%s</strong></li>', esc_html( $k ), $v ? '#2a7' : '#c0392b', $v ? '✓' : '—' );
				if ( function_exists( 'nr_alt_issues' ) ) { $a = nr_alt_issues(); printf( '<li style="display:flex;justify-content:space-between;padding:5px 0"><span>%s</span><strong style="color:%s">%d / %d</strong></li>', esc_html__( 'Weak/empty alt', 'raveenthiran' ), $a['bad'] ? '#c0392b' : '#2a7', $a['bad'], $a['total'] ); }
				echo '</ul>';
			}
		} elseif ( $i === 3 ) { // field metrics (closure in mediumwins — re-render inline)
			$cwv = get_option( 'nr_cwv', [] );
			echo '<ul style="margin:0">';
			foreach ( [ 'LCP' => 'ms', 'INP' => 'ms', 'FCP' => 'ms', 'TTFB' => 'ms', 'CLS' => '' ] as $m => $u ) {
				$r = $cwv[ $m ] ?? null; $avg = $r && $r['n'] ? round( $r['sum'] / $r['n'], $u ? 0 : 3 ) : '—';
				printf( '<li style="display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:3px 0"><span>%s</span><strong>%s%s</strong></li>', esc_html( $m ), esc_html( (string) $avg ), esc_html( $avg === '—' ? '' : $u ) );
			}
			echo '</ul>';
			$f = get_option( 'nr_funnel', [] );
			printf( '<p style="font-size:12px;color:#555;margin:8px 0 0">%s</p>', esc_html( sprintf( 'Home %d → Portfolio %d → Enquire %d → %d sent',
				(int) ( $f['home'] ?? 0 ), (int) ( $f['portfolio'] ?? 0 ), (int) ( $f['enquire'] ?? 0 ), (int) ( wp_count_posts( 'nr_enquiry' )->publish ?? 0 ) ) ) );
		} elseif ( $s[1] && function_exists( $s[1] ) ) {
			call_user_func( $s[1] );
		}
		echo '</div></details>';
	}
	// quick links to the tucked-away utility pages
	printf( '<p style="margin:6px 0 0;font-size:12px"><a href="%s">%s</a> · <a href="%s">%s</a> · <a href="%s">%s</a> · <a href="%s">%s</a></p>',
		esc_url( admin_url( 'themes.php?page=nr-flags' ) ), esc_html__( 'Feature flags', 'raveenthiran' ),
		esc_url( admin_url( 'themes.php?page=nr-tags' ) ), esc_html__( 'Tag clusters', 'raveenthiran' ),
		esc_url( admin_url( 'themes.php?page=nr-series-grid' ) ), esc_html__( 'Series grid', 'raveenthiran' ),
		esc_url( admin_url( 'tools.php?page=nr-redirects' ) ), esc_html__( 'Redirects', 'raveenthiran' ) );
}

/* Quick links on the Theme Settings page header too. */
add_action( 'admin_notices', function () {
	$s = get_current_screen();
	if ( ! $s || $s->id !== 'toplevel_page_nr-theme-settings' || ! nr_admin_simple() ) return;
	printf( '<div class="notice notice-info" style="padding:8px 12px">%s <a href="%s">%s</a> · <a href="%s">%s</a> · <a href="%s">%s</a></div>',
		esc_html__( 'Utility pages:', 'raveenthiran' ),
		esc_url( admin_url( 'themes.php?page=nr-flags' ) ), esc_html__( 'Feature flags', 'raveenthiran' ),
		esc_url( admin_url( 'themes.php?page=nr-tags' ) ), esc_html__( 'Tag clusters', 'raveenthiran' ),
		esc_url( admin_url( 'themes.php?page=nr-series-grid' ) ), esc_html__( 'Series grid', 'raveenthiran' ) );
} );
