<?php
/**
 * inc/admin-hub.php — one home for the theme backend (v4.59.0).
 *  - A top-level "Obscura" menu (registered in theme-settings.php) holds Settings.
 *  - This adds a "Tools" hub page that links every backend feature in one place.
 *  - It also upgrades the Settings screen from accordion <details> to real TABS
 *    with full-width, two-column fields (enhancement layer — the save form and
 *    field markup are untouched, so options.php still saves everything).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Tools hub: Obscura → Tools ─────────────────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'nr-theme-settings', __( 'Tools', 'raveenthiran' ), __( 'Tools', 'raveenthiran' ), 'manage_options', 'nr-tools', 'nr_tools_hub_page' );
}, 11 );

function nr_tools_hub_page() {
	// slug → admin URL (handles whichever parent each page was registered under)
	$u = function ( $slug, $fallback ) { $url = menu_page_url( $slug, false ); return $url ?: admin_url( $fallback ); };
	$groups = [
		__( 'Enquiries & clients', 'raveenthiran' ) => [
			[ __( 'Pipeline', 'raveenthiran' ), __( 'Drag enquiries New → Quoted → Booked → Delivered.', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_enquiry&page=nr-pipeline' ), 'dashicons-screenoptions' ],
			[ __( 'All enquiries', 'raveenthiran' ), __( 'Every brief that came in, with lead score + CSV export.', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_enquiry' ), 'dashicons-email-alt' ],
			[ __( 'Subscribers', 'raveenthiran' ), __( 'Newsletter sign-ups (double-opt-in).', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_subscriber' ), 'dashicons-groups' ],
		],
		__( 'Content', 'raveenthiran' ) => [
			[ __( 'Projects', 'raveenthiran' ), __( 'The portfolio CPT.', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_project' ), 'dashicons-camera' ],
			[ __( 'Journal', 'raveenthiran' ), __( 'Essays & field notes.', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_journal' ), 'dashicons-edit-page' ],
			[ __( 'Editorial calendar', 'raveenthiran' ), __( 'Journal by month + status.', 'raveenthiran' ), admin_url( 'edit.php?post_type=nr_journal&page=nr-edcal' ), 'dashicons-calendar-alt' ],
			[ __( 'Tag clusters', 'raveenthiran' ), __( 'Tags by use; spot orphans/duplicates.', 'raveenthiran' ), admin_url( 'themes.php?page=nr-tags' ), 'dashicons-tag' ],
			[ __( 'Series grid', 'raveenthiran' ), __( 'Compare frames across each series.', 'raveenthiran' ), admin_url( 'themes.php?page=nr-series-grid' ), 'dashicons-grid-view' ],
			[ __( 'Alt-text editor', 'raveenthiran' ), __( 'Fix images with missing alt text in bulk.', 'raveenthiran' ), admin_url( 'tools.php?page=nr-alts' ), 'dashicons-universal-access-alt' ],
		],
		__( 'SEO & ops', 'raveenthiran' ) => [
			[ __( 'Redirects', 'raveenthiran' ), __( '301 / 410 map for moved or gone URLs.', 'raveenthiran' ), admin_url( 'tools.php?page=nr-redirects' ), 'dashicons-randomize' ],
			[ __( 'Error log', 'raveenthiran' ), __( 'Theme-scoped lines from debug.log.', 'raveenthiran' ), admin_url( 'tools.php?page=nr-log' ), 'dashicons-warning' ],
			[ __( 'Feature flags', 'raveenthiran' ), __( 'Every nr_fx_* toggle and its current state.', 'raveenthiran' ), admin_url( 'themes.php?page=nr-flags' ), 'dashicons-controls-play' ],
			[ __( 'Components', 'raveenthiran' ), __( 'Live render of every UI block (visual QA).', 'raveenthiran' ), admin_url( 'themes.php?page=nr-components' ), 'dashicons-layout' ],
		],
		__( 'Settings & data', 'raveenthiran' ) => [
			[ __( 'Theme Settings', 'raveenthiran' ), __( 'All copy, colours, effects, SEO, mail, security.', 'raveenthiran' ), admin_url( 'admin.php?page=nr-theme-settings' ), 'dashicons-admin-settings' ],
			[ __( 'Export enquiries (CSV / 🔒)', 'raveenthiran' ), __( 'Plain or AES-encrypted export.', 'raveenthiran' ), function_exists( 'nr_enquiry_export_url' ) ? nr_enquiry_export_url() : admin_url(), 'dashicons-download' ],
			[ __( 'Press kit (.zip)', 'raveenthiran' ), __( 'Bio + logo + featured images, one download.', 'raveenthiran' ), home_url( '/nr-presskit.zip' ), 'dashicons-portfolio' ],
		],
	];
	echo '<div class="wrap"><h1>' . esc_html__( 'Obscura — tools', 'raveenthiran' ) . '</h1>';
	echo '<p class="description" style="max-width:760px">' . esc_html__( 'Everything the theme adds to the back end, in one place. Day-to-day content lives in Projects/Journal; this is the control room.', 'raveenthiran' ) . '</p>';
	echo '<style>.nr-hub{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;margin-top:8px}.nr-hub a{display:flex;gap:12px;align-items:flex-start;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px 16px;text-decoration:none;color:#1d2327;transition:border-color .15s,box-shadow .15s}.nr-hub a:hover{border-color:#F2A03D;box-shadow:0 2px 10px rgba(0,0,0,.06)}.nr-hub .dashicons{color:#F2A03D;font-size:22px;width:22px;height:22px;flex:0 0 auto}.nr-hub b{display:block;font-size:14px}.nr-hub small{color:#646970;line-height:1.4}.nr-hubgroup{margin:24px 0 6px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#646970}</style>';
	foreach ( $groups as $title => $items ) {
		echo '<h2 class="nr-hubgroup">' . esc_html( $title ) . '</h2><div class="nr-hub">';
		foreach ( $items as $it ) {
			printf(
				'<a href="%s"><span class="dashicons %s"></span><span><b>%s</b><small>%s</small></span></a>',
				esc_url( $it[2] ), esc_attr( $it[3] ), esc_html( $it[0] ), esc_html( $it[1] )
			);
		}
		echo '</div>';
	}
	echo '</div>';
}

/* ── Settings screen → tabs + full-width 2-column fields ────────── */
add_action( 'admin_footer', function () {
	$s = get_current_screen();
	if ( ! $s || $s->id !== 'toplevel_page_nr-theme-settings' ) return;
	?>
	<style id="nr-settings-tabs">
		.nr-settings .nr-settings__group{border:0!important;background:transparent!important;padding:0!important;margin:0!important}
		.nr-settings .nr-settings__group > summary{display:none!important}
		.nr-tabbar{position:sticky;top:32px;z-index:10;display:flex;flex-wrap:wrap;gap:2px;margin:0 0 20px;padding:6px 0;border-bottom:1px solid #dcdcde;background:#f0f0f1}
		.nr-tabbar button{background:none;border:0;border-radius:5px 5px 0 0;padding:9px 14px;cursor:pointer;font-size:13px;color:#50575e;border-bottom:2px solid transparent}
		.nr-tabbar button:hover{color:#1d2327;background:#fff}
		.nr-tabbar button.is-on{color:#1d2327;font-weight:600;border-bottom-color:#F2A03D;background:#fff}
		.nr-settings .form-table{max-width:none}
		.nr-settings .form-table th{width:220px}
		@media (min-width:1500px){
			.nr-settings__group .form-table{display:block}
			.nr-settings__group .form-table > tbody{display:grid;grid-template-columns:1fr 1fr;gap:0 48px}
			.nr-settings__group .form-table > tbody > tr{display:grid;grid-template-columns:200px minmax(0,1fr);align-items:start;gap:0 16px;padding:12px 0;border-bottom:1px solid #f0f0f1}
			.nr-settings__group .form-table > tbody > tr > th,
			.nr-settings__group .form-table > tbody > tr > td{display:block;width:auto;padding:0}
			.nr-settings__group .form-table textarea{width:100%}
			.nr-settings__group .form-table input[type=text],
			.nr-settings__group .form-table input[type=email],
			.nr-settings__group .form-table input[type=url]{width:100%;max-width:none}
		}
	</style>
	<script>
	(function(){
		var form = document.querySelector('.nr-settings-form'); if(!form) return;
		var groups = [].slice.call(form.querySelectorAll('.nr-settings__group')); if(!groups.length) return;
		var bar = document.createElement('div'); bar.className = 'nr-tabbar'; bar.setAttribute('role','tablist');
		groups.forEach(function(g,i){
			g.setAttribute('open','open'); g.classList.add('nr-tabpanel'); g.dataset.i = i;
			var h = g.querySelector('summary h2, h2');
			var label = (h ? h.textContent : 'Section '+(i+1)).replace(/^[§\s]+/,'').trim();
			var b = document.createElement('button'); b.type='button'; b.textContent = label; b.dataset.i = i;
			b.addEventListener('click', function(){ sel(i); try{ localStorage.setItem('nr_settings_tab', i); }catch(e){} });
			bar.appendChild(b);
		});
		form.insertBefore(bar, form.firstChild);
		function sel(i){
			groups.forEach(function(g){ g.style.display = (+g.dataset.i===i)?'':'none'; });
			[].forEach.call(bar.children, function(b){ b.classList.toggle('is-on', +b.dataset.i===i); });
		}
		var start = 0; try{ start = parseInt(localStorage.getItem('nr_settings_tab')||'0',10)||0; }catch(e){}
		if(start<0||start>=groups.length) start = 0;
		sel(start);
	})();
	</script>
	<?php
} );
