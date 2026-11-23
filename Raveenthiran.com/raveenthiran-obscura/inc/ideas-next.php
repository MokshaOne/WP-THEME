<?php
/**
 * inc/ideas-next.php — the post-50-review "IDEAS-NEXT" batch (v4.41.0).
 *
 * One self-contained module that ships the open backlog items grouped by theme:
 *   Conversion : #6 recently-viewed · #7 newsletter capture · #8 testimonials band · #9 availability dates
 *   SEO        : #11 aggregateRating · #12 lastmod + IndexNow · #13 feed polish · #14 speculation rules · #15 OG audit
 *   Editorial  : #16 process section · #19 client logos · #20 series cover pages
 *   A11y       : #21 prefers-contrast / Save-Data
 *   Ops        : #25 settings import/export · #26 backup reminder · #27 content-health report · #28 assistant role
 *
 * Everything visitor-facing is opt-in (nr_fx_* default "0") so a live site never
 * changes until the owner previews and approves. Heavy / niche items (#10 lookbook
 * PDF, #17 scroll-video hero, #18 diptych) are intentionally deferred — see
 * docs/IDEAS-NEXT.md for the rationale.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   Shared helper — Save-Data / reduced-data body class (#21).
   The browser sends `Save-Data: on` when the user opted into a
   data-saver. We expose it as a body class so CSS can skip heavy
   backgrounds and JS can skip non-essential effects.
   ============================================================= */
add_filter( 'body_class', function ( $classes ) {
	$sd = isset( $_SERVER['HTTP_SAVE_DATA'] ) && stripos( (string) $_SERVER['HTTP_SAVE_DATA'], 'on' ) !== false;
	if ( $sd ) $classes[] = 'nr-savedata';
	return $classes;
} );

/* Tell caches the response varies by the Save-Data header. */
add_filter( 'wp_headers', function ( $h ) {
	$h['Vary'] = empty( $h['Vary'] ) ? 'Save-Data' : $h['Vary'] . ', Save-Data';
	return $h;
} );

/* #6 "recently viewed" — removed v4.68.0 (toggle retired in v4.65). */

/* =============================================================
   #7 — Newsletter / "new work" capture (owned audience).
   Stores each confirmed email as a private nr_subscriber post.
   If a Brevo API key is configured (ACF), the address is also
   forwarded to the list — but local storage is the source of truth.
   ============================================================= */
add_action( 'init', function () {
	register_post_type( 'nr_subscriber', [
		'labels'          => [
			'name'          => __( 'Subscribers', 'raveenthiran' ),
			'singular_name' => __( 'Subscriber', 'raveenthiran' ),
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=nr_enquiry',
		'capability_type' => 'post',
		'capabilities'    => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'    => true,
		'supports'        => [ 'title' ],
		'menu_icon'       => 'dashicons-email',
	] );
} );

/* nr_newsletter_form_markup() — removed v4.68.0 (footer capture retired in v4.65).
   The nr_subscriber CPT + subscribe handler are kept so any existing addresses
   stay viewable, but no new sign-up form is rendered. */

add_action( 'admin_post_nopriv_nr_news_subscribe', 'nr_news_subscribe_handler' );
add_action( 'admin_post_nr_news_subscribe', 'nr_news_subscribe_handler' );
function nr_news_subscribe_handler() {
	$back = wp_get_referer() ?: home_url( '/' );
	if ( ! isset( $_POST['nr_news_nonce'] ) || ! wp_verify_nonce( $_POST['nr_news_nonce'], 'nr_news_subscribe' ) ) {
		wp_safe_redirect( add_query_arg( 'nr_news', '0', $back ) ); exit;
	}
	// Honeypot — a bot fills hidden fields.
	if ( ! empty( $_POST['nr_news_hp'] ) ) { wp_safe_redirect( add_query_arg( 'nr_news', '1', $back ) ); exit; }

	$email = sanitize_email( wp_unslash( $_POST['nr_news_email'] ?? '' ) );
	if ( ! is_email( $email ) ) { wp_safe_redirect( add_query_arg( 'nr_news', '0', $back ) ); exit; }

	// De-dupe by title.
	$exists = get_posts( [ 'post_type' => 'nr_subscriber', 'title' => $email, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ] );
	if ( empty( $exists ) ) {
		$id = wp_insert_post( [ 'post_type' => 'nr_subscriber', 'post_status' => 'private', 'post_title' => $email ] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, 'nr_news_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '' );
			update_post_meta( $id, 'nr_news_src', esc_url_raw( $back ) );
		}
	}
	nr_newsletter_forward_brevo( $email ); // best-effort; never blocks the redirect
	wp_safe_redirect( add_query_arg( 'nr_news', '1', $back ) ); exit;
}

/* Optional: forward to Brevo if the ACF key + list are configured. */
function nr_newsletter_forward_brevo( $email ) {
	if ( ! function_exists( 'get_field' ) ) return;
	$key  = get_field( 'brevo_api_key', 'option' );
	$list = (int) get_field( 'brevo_list_id', 'option' );
	if ( ! $key ) return;
	$body = [ 'email' => $email, 'updateEnabled' => true ];
	if ( $list ) $body['listIds'] = [ $list ];
	wp_remote_post( 'https://api.brevo.com/v3/contacts', [
		'timeout'  => 6,
		'blocking' => false,
		'headers'  => [ 'api-key' => $key, 'Content-Type' => 'application/json', 'accept' => 'application/json' ],
		'body'     => wp_json_encode( $body ),
	] );
}

/* #8 testimonials band — removed v4.68.0 (toggle retired in v4.65). The
   nr_testimonial CPT remains; use [nr_testimonial_videos] or a custom layout. */

/* Aggregate rating data shared by the band + schema (#11). */
function nr_testimonial_rating_data() {
	$cache = wp_cache_get( 'nr_testi_rating' );
	if ( is_array( $cache ) ) return $cache;
	$q = new WP_Query( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 100, 'no_found_rows' => true, 'fields' => 'ids' ] );
	$sum = 0; $n = 0; $reviews = [];
	foreach ( $q->posts as $tid ) {
		$r = (int) get_post_meta( $tid, 'rating', true );
		if ( $r < 1 || $r > 5 ) $r = 5;
		$sum += $r; $n++;
		if ( count( $reviews ) < 5 ) {
			$reviews[] = [
				'@type'         => 'Review',
				'reviewRating'  => [ '@type' => 'Rating', 'ratingValue' => (string) $r, 'bestRating' => '5' ],
				'author'        => [ '@type' => 'Person', 'name' => get_the_title( $tid ) ],
				'reviewBody'    => wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $tid ) ), 40 ),
			];
		}
	}
	$data = $n ? [ 'avg' => number_format( $sum / $n, 1 ), 'count' => $n, 'reviews' => $reviews ] : [ 'avg' => 0, 'count' => 0, 'reviews' => [] ];
	wp_cache_set( 'nr_testi_rating', $data, '', 600 );
	return $data;
}

/* =============================================================
   #9 — "Next open dates" availability line + #19 client logos.
   Both render in the footer-extras region from Theme Settings.
   ============================================================= */
function nr_availability_markup() {
	$txt = trim( (string) nr_opt( 'nr_avail_dates', '' ) );
	if ( $txt === '' || is_singular( 'nr_project' ) ) return '';
	return '<p class="nr-availability"><span class="tick"></span>'
		. '<b>' . esc_html__( 'Next open dates', 'raveenthiran' ) . '</b> · '
		. esc_html( $txt ) . '</p>';
}

function nr_client_logos_markup() {
	$raw = trim( (string) nr_opt( 'nr_clients_logos', '' ) );
	if ( $raw === '' || is_singular( 'nr_project' ) ) return '';
	$lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $lines ) return '';
	ob_start();
	echo '<aside class="nr-clients" aria-label="' . esc_attr__( 'Trusted by', 'raveenthiran' ) . '">';
	// #70 — count badge (animated client-side via [data-count]).
	printf(
		'<span class="nr-clients__label">%s <b class="nr-clients__count" data-count="%d">%d</b></span><div class="nr-clients__row">',
		esc_html__( 'Trusted by', 'raveenthiran' ), count( $lines ), count( $lines )
	);
	foreach ( $lines as $ln ) {
		// line forms: "img-url" · "img-url | Name" · "img-url | Name | link-url" · "Name | link-url" · "Name"
		$parts = array_map( 'trim', explode( '|', $ln ) );
		$img = $name = $link = '';
		foreach ( $parts as $p ) {
			if ( filter_var( $p, FILTER_VALIDATE_URL ) ) { if ( preg_match( '/\.(svg|png|jpe?g|webp|gif)$/i', $p ) ) $img = $p; else $link = $p; }
			elseif ( $p !== '' ) $name = $name === '' ? $p : $name;
		}
		$inner = $img
			? sprintf( '<img src="%s" alt="%s" loading="lazy" decoding="async" height="28">', esc_url( $img ), esc_attr( $name ) )
			: ( $name !== '' ? sprintf( '<span class="nr-clients__name">%s</span>', esc_html( $name ) ) : '' );
		if ( $inner === '' ) continue;
		echo $link
			? '<a class="nr-clients__item" href="' . esc_url( $link ) . '" target="_blank" rel="noopener">' . $inner . '</a>'
			: $inner;
	}
	echo '</div></aside>';
	return ob_get_clean();
}

/* The single footer-extras region — composes the conversion widgets in order
   and is called once from footer.php on scrolling pages. */
function nr_render_footer_extras() {
	if ( is_front_page() || is_singular( 'nr_project' ) || is_post_type_archive( 'nr_project' ) ) return;
	// Recently-viewed, Testimonials band and Newsletter were removed in v4.65.0.
	$blocks = array_filter( [
		nr_client_logos_markup(),
		nr_availability_markup(),
	] );
	if ( ! $blocks ) return;
	echo '<section class="nr-extras" aria-label="' . esc_attr__( 'More from the studio', 'raveenthiran' ) . '">';
	echo implode( "\n", $blocks );
	echo '</section>';
	// subscribe confirmation
	if ( isset( $_GET['nr_news'] ) ) {
		$ok = $_GET['nr_news'] === '1';
		printf(
			'<div class="nr-status %s" role="status" aria-live="polite">%s</div><script>setTimeout(function(){var s=document.querySelector(".nr-status");if(s)s.remove();},5000);</script>',
			$ok ? 'nr-status--ok' : 'nr-status--error',
			esc_html( $ok ? __( 'Thanks — you’re on the list.', 'raveenthiran' ) : __( 'That didn’t work — please check the address.', 'raveenthiran' ) )
		);
	}
}

/* =============================================================
   #15 — OG audit: article:published_time / modified_time + section
   for journal & projects (complements inc/seo.php's og:* tags).
   ============================================================= */
add_action( 'wp_head', function () {
	if ( ! ( is_singular( 'nr_journal' ) || is_singular( 'nr_project' ) ) ) return;
	$id = get_queried_object_id();
	printf( '<meta property="article:published_time" content="%s">' . "\n", esc_attr( get_the_date( 'c', $id ) ) );
	printf( '<meta property="article:modified_time" content="%s">' . "\n", esc_attr( get_the_modified_date( 'c', $id ) ) );
	$tax   = is_singular( 'nr_journal' ) ? 'nr_journal_cat' : 'nr_project_cat';
	$terms = wp_get_post_terms( $id, $tax, [ 'fields' => 'names' ] );
	if ( ! is_wp_error( $terms ) && $terms ) {
		printf( '<meta property="article:section" content="%s">' . "\n", esc_attr( $terms[0] ) );
		foreach ( array_slice( $terms, 0, 6 ) as $t ) printf( '<meta property="article:tag" content="%s">' . "\n", esc_attr( $t ) );
	}
	printf( '<meta property="article:author" content="%s">' . "\n", esc_attr( nr_opt( 'nr_about_name', 'Nishuthan Raveenthiran' ) ) );
}, 8 );

/* =============================================================
   #14 — Speculation Rules API (opt-in). Prerender same-origin
   links on moderate hover — instant nav on supporting browsers,
   silently ignored elsewhere. Replaces manual prefetch heuristics.
   ============================================================= */
add_action( 'wp_footer', function () {
	if ( nr_opt( 'nr_fx_speculation', '0' ) !== '1' ) return;
	$rules = [
		// #31 — prerender project pages on hover (instant), only prefetch journal.
		'prerender' => [ [
			'source'    => 'document',
			'where'     => [ 'and' => [
				[ 'href_matches' => '/project/*' ],
				[ 'not' => [ 'selector_matches' => '[rel~="nofollow"], [data-no-prerender]' ] ],
			] ],
			'eagerness' => 'moderate',
		] ],
		'prefetch' => [ [
			'source'    => 'document',
			'where'     => [ 'and' => [
				[ 'href_matches' => '/*' ],
				[ 'not' => [ 'href_matches' => '/wp-admin/*' ] ],
				[ 'not' => [ 'href_matches' => '/wp-login.php' ] ],
				[ 'not' => [ 'href_matches' => '/project/*' ] ],
				[ 'not' => [ 'selector_matches' => '[rel~="nofollow"], [data-no-prerender], .nr-footer__email' ] ],
			] ],
			'eagerness' => 'moderate',
		] ],
	];
	echo '<script type="speculationrules">' . wp_json_encode( $rules, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 20 );

/* =============================================================
   #12 — Per-post freshness + IndexNow ping on publish/update.
   IndexNow (Bing/Yandex/Seznam) gets an instant "this URL changed"
   hint. Needs a key (Theme Settings → set any 8–128 hex chars);
   the matching key file is served from /<key>.txt virtually.
   ============================================================= */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( $new !== 'publish' ) return;
	if ( ! in_array( $post->post_type, [ 'nr_project', 'nr_journal', 'page' ], true ) ) return;
	if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) return;
	nr_indexnow_ping( get_permalink( $post ) );
}, 10, 3 );

function nr_indexnow_key() {
	$k = preg_replace( '/[^a-f0-9]/i', '', (string) nr_opt( 'nr_indexnow_key', '' ) );
	return ( strlen( $k ) >= 8 && strlen( $k ) <= 128 ) ? $k : '';
}

function nr_indexnow_ping( $url ) {
	$key = nr_indexnow_key();
	if ( ! $key || ! $url ) return;
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	wp_remote_post( 'https://api.indexnow.org/IndexNow', [
		'timeout'  => 6,
		'blocking' => false,
		'headers'  => [ 'Content-Type' => 'application/json; charset=utf-8' ],
		'body'     => wp_json_encode( [
			'host'        => $host,
			'key'         => $key,
			'keyLocation' => home_url( '/' . $key . '.txt' ),
			'urlList'     => [ $url ],
		] ),
	] );
}

/* Virtual key file: /<key>.txt → echoes the key (IndexNow ownership proof). */
add_action( 'template_redirect', function () {
	$key = nr_indexnow_key();
	if ( ! $key ) return;
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
	if ( $path === $key . '.txt' ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo $key;
		exit;
	}
}, 0 );

/* =============================================================
   #13 — Feed polish: branded description + a dedicated /journal/feed
   that carries only nr_journal posts.
   ============================================================= */
add_filter( 'get_wp_title_rss', function ( $t ) { return $t; } );
add_filter( 'wp_title_rss', function ( $t ) { return $t; } );
add_filter( 'bloginfo_rss', function ( $info, $show ) {
	if ( $show === 'description' && trim( (string) $info ) === '' ) {
		return wp_strip_all_tags( (string) nr_opt( 'nr_tagline', '' ) );
	}
	return $info;
}, 10, 2 );

add_action( 'init', function () {
	add_feed( 'journal-feed', 'nr_journal_feed_render' );
} );
add_action( 'wp_head', function () {
	if ( is_admin() ) return;
	printf(
		'<link rel="alternate" type="application/rss+xml" title="%s" href="%s">' . "\n",
		esc_attr( get_bloginfo( 'name' ) . ' — ' . __( 'Journal', 'raveenthiran' ) ),
		esc_url( home_url( '/feed/journal-feed/' ) )
	);
}, 3 );
function nr_journal_feed_render() {
	query_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => 20, 'ignore_sticky_posts' => true ] );
	load_template( ABSPATH . WPINC . '/feed-rss2.php' );
}

/* =============================================================
   #16 — Render the project "process" section (field added in
   inc/acf-fields.php). Called from single-nr_project.php.
   ============================================================= */
function nr_project_process_markup( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$txt = function_exists( 'nr_field_str' ) ? nr_field_str( 'project_process', $post_id ) : '';
	if ( $txt === '' ) return '';
	ob_start(); ?>
	<section class="nr-process">
		<span class="nr-eyebrow nr-eyebrow--xs"><?php esc_html_e( 'Behind the frame', 'raveenthiran' ); ?></span>
		<div class="nr-process__body"><?php echo wp_kses_post( wpautop( $txt ) ); ?></div>
	</section>
	<?php
	return ob_get_clean();
}

/* =============================================================
   #20 — Series cover pages. nr_project_series terms get a
   statement + cover image (term meta), shown atop taxonomy.php.
   ============================================================= */
add_action( 'nr_project_series_add_form_fields', 'nr_series_cover_add_fields' );
function nr_series_cover_add_fields() {
	wp_nonce_field( 'nr_series_meta', 'nr_series_meta_nonce' ); ?>
	<div class="form-field">
		<label for="nr_series_statement"><?php esc_html_e( 'Series statement', 'raveenthiran' ); ?></label>
		<textarea name="nr_series_statement" id="nr_series_statement" rows="3"></textarea>
		<p><?php esc_html_e( 'A short intro shown on the series archive cover.', 'raveenthiran' ); ?></p>
	</div>
	<div class="form-field">
		<label for="nr_series_cover"><?php esc_html_e( 'Cover image ID', 'raveenthiran' ); ?></label>
		<input type="number" name="nr_series_cover" id="nr_series_cover" value="">
		<p><?php esc_html_e( 'Media-library attachment ID for the cover hero (Media → click image → see URL/ID).', 'raveenthiran' ); ?></p>
	</div>
	<?php
}
add_action( 'nr_project_series_edit_form_fields', 'nr_series_cover_edit_fields', 10, 1 );
function nr_series_cover_edit_fields( $term ) {
	$st = get_term_meta( $term->term_id, 'nr_series_statement', true );
	$cv = get_term_meta( $term->term_id, 'nr_series_cover', true );
	wp_nonce_field( 'nr_series_meta', 'nr_series_meta_nonce' ); ?>
	<tr class="form-field">
		<th><label for="nr_series_statement"><?php esc_html_e( 'Series statement', 'raveenthiran' ); ?></label></th>
		<td><textarea name="nr_series_statement" id="nr_series_statement" rows="3"><?php echo esc_textarea( $st ); ?></textarea>
			<p class="description"><?php esc_html_e( 'A short intro shown on the series archive cover.', 'raveenthiran' ); ?></p></td>
	</tr>
	<tr class="form-field">
		<th><label for="nr_series_cover"><?php esc_html_e( 'Cover image ID', 'raveenthiran' ); ?></label></th>
		<td><input type="number" name="nr_series_cover" id="nr_series_cover" value="<?php echo esc_attr( $cv ); ?>">
			<p class="description"><?php esc_html_e( 'Media-library attachment ID for the cover hero.', 'raveenthiran' ); ?></p></td>
	</tr>
	<?php
}
add_action( 'created_nr_project_series', 'nr_series_cover_save' );
add_action( 'edited_nr_project_series', 'nr_series_cover_save' );
function nr_series_cover_save( $term_id ) {
	if ( ! isset( $_POST['nr_series_meta_nonce'] ) || ! wp_verify_nonce( $_POST['nr_series_meta_nonce'], 'nr_series_meta' ) ) return;
	if ( ! current_user_can( 'manage_categories' ) ) return;
	if ( isset( $_POST['nr_series_statement'] ) ) update_term_meta( $term_id, 'nr_series_statement', sanitize_textarea_field( wp_unslash( $_POST['nr_series_statement'] ) ) );
	if ( isset( $_POST['nr_series_cover'] ) )     update_term_meta( $term_id, 'nr_series_cover', (int) $_POST['nr_series_cover'] );
}

/* Rendered from taxonomy.php for nr_project_series terms. */
function nr_series_cover_header( $term ) {
	if ( ! $term || $term->taxonomy !== 'nr_project_series' ) return;
	$st = trim( (string) get_term_meta( $term->term_id, 'nr_series_statement', true ) );
	$cv = (int) get_term_meta( $term->term_id, 'nr_series_cover', true );
	if ( $st === '' && ! $cv ) return;
	echo '<div class="nr-series-cover">';
	if ( $cv ) echo wp_get_attachment_image( $cv, 'nr-hero', false, [ 'class' => 'nr-series-cover__img', 'loading' => 'eager', 'decoding' => 'async', 'alt' => esc_attr( $term->name ) ] );
	if ( $st ) echo '<p class="nr-series-cover__statement">' . esc_html( $st ) . '</p>';
	echo '</div>';
}

/* =============================================================
   #25 — Settings import / export (JSON). Move config between
   staging and live. Export streams a JSON of every Theme Setting;
   import restores the known keys (unknown keys are ignored).
   ============================================================= */
add_action( 'admin_post_nr_settings_export', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_settings_export' ) ) wp_die( 'no' );
	$out = [];
	foreach ( array_keys( nr_settings_defaults() ) as $k ) $out[ $k ] = get_option( $k );
	$out['__meta'] = [ 'theme' => 'raveenthiran-obscura', 'version' => NR_THEME_VERSION, 'exported' => gmdate( 'c' ) ];
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="obscura-settings-' . gmdate( 'Ymd-His' ) . '.json"' );
	echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
} );

add_action( 'admin_post_nr_settings_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_settings_import' ) ) wp_die( 'no' );
	$ok = 0;
	if ( ! empty( $_FILES['nr_settings_file']['tmp_name'] ) && is_uploaded_file( $_FILES['nr_settings_file']['tmp_name'] ) ) {
		$json = file_get_contents( $_FILES['nr_settings_file']['tmp_name'] );
		$data = json_decode( (string) $json, true );
		if ( is_array( $data ) ) {
			$defaults = nr_settings_defaults();
			foreach ( $data as $k => $v ) {
				if ( ! array_key_exists( $k, $defaults ) ) continue;       // only known keys
				if ( is_scalar( $v ) ) { update_option( $k, (string) $v ); $ok++; }
			}
		}
	}
	wp_safe_redirect( admin_url( 'themes.php?page=nr-theme-settings&nr_import=' . $ok ) );
	exit;
} );

/* =============================================================
   #26 + #27 — "Content health" dashboard widget.
   Reports missing featured images, empty galleries, projects
   without a category, and a gentle backup reminder.
   ============================================================= */
add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_pages' ) ) return;
	wp_add_dashboard_widget( 'nr_content_health', __( 'Obscura — content health', 'raveenthiran' ), 'nr_content_health_widget' );
} );
function nr_content_health_widget() {
	$missing_thumb = []; $empty_gallery = []; $no_cat = [];
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 300, 'no_found_rows' => true, 'fields' => 'ids' ] );
	foreach ( $q->posts as $pid ) {
		if ( ! has_post_thumbnail( $pid ) ) $missing_thumb[] = $pid;
		$g = function_exists( 'nr_field' ) ? nr_field( 'project_gallery', $pid ) : [];
		if ( empty( $g ) ) $empty_gallery[] = $pid;
		$terms = wp_get_post_terms( $pid, 'nr_project_cat', [ 'fields' => 'ids' ] );
		if ( empty( $terms ) || is_wp_error( $terms ) ) $no_cat[] = $pid;
	}
	$jq = new WP_Query( [ 'post_type' => 'nr_journal', 'posts_per_page' => 300, 'no_found_rows' => true, 'fields' => 'ids' ] );
	foreach ( $jq->posts as $jid ) if ( ! has_post_thumbnail( $jid ) ) $missing_thumb[] = $jid;

	$row = function ( $label, $ids ) {
		$n = count( $ids );
		$color = $n ? '#c0392b' : '#2a7';
		echo '<li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #eee">';
		echo '<span>' . esc_html( $label ) . '</span><strong style="color:' . $color . '">' . (int) $n . '</strong></li>';
		if ( $n ) {
			echo '<li style="padding:2px 0 8px;font-size:12px">';
			$links = array_slice( $ids, 0, 8 );
			$out = [];
			foreach ( $links as $id ) $out[] = '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: ( '#' . $id ) ) . '</a>';
			echo wp_kses_post( implode( ' · ', $out ) );
			if ( $n > 8 ) echo ' …';
			echo '</li>';
		}
	};
	echo '<ul style="margin:0">';
	$row( __( 'Missing featured image', 'raveenthiran' ), $missing_thumb );
	$row( __( 'Projects with an empty gallery', 'raveenthiran' ), $empty_gallery );
	$row( __( 'Projects without a category', 'raveenthiran' ), $no_cat );
	echo '</ul>';

	echo '<p style="margin-top:12px;padding-top:10px;border-top:1px solid #ddd;font-size:12px;color:#555">';
	echo '<strong>' . esc_html__( 'Backup reminder', 'raveenthiran' ) . '</strong> — ';
	$tools = admin_url( 'export.php' );
	printf(
		/* translators: %s = export tool link */
		esc_html__( 'Export your content monthly from %s, and keep an off-site copy of /wp-content/uploads. Database backups belong with your host (easyname) or a backup plugin.', 'raveenthiran' ),
		'<a href="' . esc_url( $tools ) . '">' . esc_html__( 'Tools → Export', 'raveenthiran' ) . '</a>'
	);
	echo '</p>';

	// #188 — autoloaded-options size (perf hygiene).
	if ( function_exists( 'nr_autoload_report' ) ) {
		$ar = nr_autoload_report();
		$kb = round( $ar['bytes'] / 1024 );
		$warn = $kb > 800 ? '#c0392b' : '#2a7';
		echo '<p style="margin-top:10px;font-size:12px;color:#555"><strong>' . esc_html__( 'Autoloaded options', 'raveenthiran' ) . '</strong> — ';
		printf( '<span style="color:%s">%d KB</span>. %s', $warn, (int) $kb, esc_html__( 'Under ~800 KB is healthy.', 'raveenthiran' ) );
		echo '</p>';
	}

	// #112 — outbound broken-link report (on-demand scan).
	$lc = get_transient( 'nr_linkcheck' );
	$lcu = wp_nonce_url( admin_url( 'admin-post.php?action=nr_linkcheck' ), 'nr_linkcheck' );
	echo '<p style="margin-top:8px;font-size:12px"><strong>' . esc_html__( 'Outbound links', 'raveenthiran' ) . '</strong> — ';
	if ( is_array( $lc ) ) {
		printf( '<span style="color:%s">%d broken</span> · ', $lc ? '#c0392b' : '#2a7', count( $lc ) );
		foreach ( array_slice( $lc, 0, 5, true ) as $u => $info ) printf( '<a href="%s">%s</a> (%d) ', esc_url( get_edit_post_link( $info['post'] ) ), esc_html( wp_parse_url( $u, PHP_URL_HOST ) ), (int) $info['code'] );
	} else {
		echo esc_html__( 'not scanned yet. ', 'raveenthiran' );
	}
	echo '<a class="button button-small" href="' . esc_url( $lcu ) . '">' . esc_html__( 'Check now', 'raveenthiran' ) . '</a></p>';

	// #113 — IndexNow bulk resubmit (only useful once a key is set).
	if ( function_exists( 'nr_indexnow_key' ) && nr_indexnow_key() ) {
		$u = wp_nonce_url( admin_url( 'admin-post.php?action=nr_indexnow_bulk' ), 'nr_indexnow_bulk' );
		echo '<p style="margin-top:8px"><a class="button button-small" href="' . esc_url( $u ) . '">' . esc_html__( 'Re-ping all URLs to IndexNow', 'raveenthiran' ) . '</a></p>';
	}
}

/* =============================================================
   #28 — Assistant role. An editor/assistant who can manage
   Projects, Journal, Testimonials and read Enquiries, but not
   touch theme settings, plugins, or users.
   ============================================================= */
add_action( 'after_switch_theme', 'nr_register_assistant_role' );
function nr_register_assistant_role() {
	if ( get_role( 'nr_assistant' ) ) return;
	add_role( 'nr_assistant', __( 'Studio Assistant', 'raveenthiran' ), [
		'read'                   => true,
		'upload_files'           => true,
		'edit_posts'             => true,
		'edit_published_posts'   => true,
		'publish_posts'          => true,
		'delete_posts'           => true,
		'edit_others_posts'      => true,
		'delete_published_posts' => true,
	] );
}
/* Keep the role's label/caps fresh on load (cheap, idempotent-ish). */
add_action( 'admin_init', function () {
	if ( ! get_role( 'nr_assistant' ) ) nr_register_assistant_role();
} );
