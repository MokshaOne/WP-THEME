<?php
/**
 * Tier 1 improvements (ideas #1–#40, minus #5/#7/#10 which moved to Tier 3).
 * Logic that can live in hooks/filters sits here; template-level bits are wired
 * in the individual templates. Loaded by functions.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────────────────────────────
 * #1 LQIP blur-up  +  #17 auto alt-text
 * Applies to every wp_get_attachment_image() call (cards, plates, hero…).
 * ───────────────────────────────────────────────────────────── */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	// #17 — fill empty alt from the image's own alt, else its title / parent title.
	if ( empty( $attr['alt'] ) ) {
		$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
		if ( ! $alt ) $alt = $attachment->post_parent ? get_the_title( $attachment->post_parent ) : $attachment->post_title;
		if ( $alt ) $attr['alt'] = $alt;
	}
	// #1 — blurred low-quality placeholder behind the image until it loads.
	$lqip = get_post_meta( $attachment->ID, '_nr_lqip', true );
	if ( $lqip ) {
		$attr['style'] = ( $attr['style'] ?? '' ) . 'background-image:url(' . $lqip . ');background-size:cover;background-position:center;';
		$attr['class'] = trim( ( $attr['class'] ?? '' ) . ' nr-lqip' );
		$attr['onload'] = "this.classList.add('nr-lqip--done')";
	}
	return $attr;
}, 10, 3 );

/* ─────────────────────────────────────────────────────────────
 * #22 Store enquiries as a CPT (the form handler in functions.php saves here).
 * #40 Admin columns for Projects + Enquiries.
 * ───────────────────────────────────────────────────────────── */
add_action( 'init', function () {
	register_post_type( 'nr_enquiry', [
		'label'    => __( 'Enquiries', 'raveenthiran' ),
		'public'   => false,
		'show_ui'  => true,
		'menu_icon'=> 'dashicons-email-alt',
		'supports' => [ 'title' ],
		'capabilities' => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap' => true,
	] );
} );

add_filter( 'manage_nr_project_posts_columns', function ( $cols ) {
	$out = [];
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( $k === 'title' ) { $out['nr_plates'] = __( 'Plates', 'raveenthiran' ); $out['nr_feat'] = __( 'Featured', 'raveenthiran' ); }
	}
	return $out;
} );
add_action( 'manage_nr_project_posts_custom_column', function ( $col, $post_id ) {
	if ( $col === 'nr_plates' ) {
		$g = function_exists( 'nr_field' ) ? nr_field( 'project_gallery', $post_id ) : get_post_meta( $post_id, 'project_gallery', true );
		echo (int) ( is_array( $g ) ? count( $g ) : ( has_post_thumbnail( $post_id ) ? 1 : 0 ) );
	} elseif ( $col === 'nr_feat' ) {
		echo nr_field( 'featured_on_homepage', $post_id ) ? '★' : '—';
	}
}, 10, 2 );

add_filter( 'manage_nr_enquiry_posts_columns', function ( $cols ) {
	return [ 'cb' => $cols['cb'] ?? '', 'title' => __( 'Name', 'raveenthiran' ), 'nr_email' => __( 'Email', 'raveenthiran' ), 'nr_type' => __( 'Type', 'raveenthiran' ), 'nr_est' => __( 'Estimate', 'raveenthiran' ), 'date' => __( 'Received', 'raveenthiran' ) ];
} );
add_action( 'manage_nr_enquiry_posts_custom_column', function ( $col, $post_id ) {
	$map = [ 'nr_email' => '_nr_email', 'nr_type' => '_nr_type', 'nr_est' => '_nr_est' ];
	if ( isset( $map[ $col ] ) ) echo esc_html( get_post_meta( $post_id, $map[ $col ], true ) );
}, 10, 2 );

/* Read-only "Enquiry details" box on the edit screen — surfaces everything the
   form captured (the post body alone showed nothing useful to the owner). */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nr-enquiry-details', __( 'Enquiry details', 'raveenthiran' ), 'nr_enquiry_details_box', 'nr_enquiry', 'normal', 'high' );
} );
function nr_enquiry_details_box( $post ) {
	$g = function ( $k ) use ( $post ) { return trim( (string) get_post_meta( $post->ID, $k, true ) ); };
	$email = $g( '_nr_email' );

	$rows = [
		__( 'Email', 'raveenthiran' )          => $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—',
		__( 'Project type', 'raveenthiran' )    => esc_html( $g( '_nr_type' ) ?: '—' ),
		__( 'Preferred date', 'raveenthiran' )  => esc_html( $g( '_nr_date' ) ?: '—' ),
		__( 'Estimate', 'raveenthiran' )        => esc_html( $g( '_nr_est' ) ?: '—' ),
	];
	if ( $g( '_nr_breakdown' ) ) {
		// "label | amount ;; …" → readable "label — €amount" lines
		$bd = array_map( function ( $part ) {
			$bits = array_map( 'trim', explode( ' | ', $part ) );
			return esc_html( $bits[0] ) . ( isset( $bits[1] ) && is_numeric( $bits[1] ) ? ' — €' . esc_html( number_format( (float) $bits[1], 2, '.', ',' ) ) : '' );
		}, explode( ' ;; ', $g( '_nr_breakdown' ) ) );
		$rows[ __( 'Calculation', 'raveenthiran' ) ] = implode( '<br>', $bd );
	}
	if ( $g( '_nr_ref' ) )     $rows[ __( 'Referenced project', 'raveenthiran' ) ] = esc_html( $g( '_nr_ref' ) );
	if ( $g( '_nr_service' ) ) $rows[ __( 'Selected service', 'raveenthiran' ) ]   = esc_html( $g( '_nr_service' ) );
	if ( $g( '_nr_referrer' ) ) {
		$rf = $g( '_nr_referrer' );
		$rows[ __( 'Came from', 'raveenthiran' ) ] = '<a href="' . esc_url( $rf ) . '" target="_blank" rel="noopener">' . esc_html( $rf ) . '</a>';
	}

	echo '<style>.nr-enq{margin:0}.nr-enq dt{font-weight:600;color:#646970;font-size:11px;text-transform:uppercase;letter-spacing:.04em;margin-top:14px}.nr-enq dd{margin:3px 0 0;font-size:14px;color:#1d2327}.nr-enq__notes{white-space:pre-wrap;background:#f6f7f7;border:1px solid #dcdcde;padding:12px;border-radius:4px;margin:4px 0 0;font-size:14px;line-height:1.5}.nr-enq__refs{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}.nr-enq__refs a{line-height:0}.nr-enq__refs img{width:88px;height:88px;object-fit:cover;border-radius:4px;border:1px solid #dcdcde}</style>';
	echo '<dl class="nr-enq">';
	foreach ( $rows as $k => $v ) {
		echo '<dt>' . esc_html( $k ) . '</dt><dd>' . $v . '</dd>'; // values pre-escaped above
	}

	$notes = trim( (string) $post->post_content );
	echo '<dt>' . esc_html__( 'Brief / notes', 'raveenthiran' ) . '</dt>';
	echo $notes !== '' ? '<dd><div class="nr-enq__notes">' . esc_html( $notes ) . '</div></dd>' : '<dd>—</dd>';

	$refs = get_post_meta( $post->ID, '_nr_ref_images', true );
	if ( is_array( $refs ) && $refs ) {
		echo '<dt>' . esc_html__( 'Reference images', 'raveenthiran' ) . '</dt><dd><div class="nr-enq__refs">';
		foreach ( $refs as $aid ) {
			$full  = wp_get_attachment_url( (int) $aid );
			$thumb = wp_get_attachment_image( (int) $aid, [ 88, 88 ] );
			if ( $thumb ) echo $full ? '<a href="' . esc_url( $full ) . '" target="_blank" rel="noopener">' . $thumb . '</a>' : $thumb;
		}
		echo '</div></dd>';
	}
	echo '</dl>';
}

/* ─────────────────────────────────────────────────────────────
 * #28 Auto availability text ("Booking Q3/Q4 YYYY") when none is set.
 * ───────────────────────────────────────────────────────────── */
function nr_auto_avail() {
	$q = (int) ceil( (int) date_i18n( 'n' ) / 3 );
	$next = $q % 4 + 1;
	$year = (int) date_i18n( 'Y' ) + ( $q === 4 ? 1 : 0 );
	return sprintf( 'Booking Q%d/Q%d · %d', $q, $next, $year );
}
add_filter( 'default_option_nr_avail_text', fn( $d ) => nr_auto_avail() );

/* ─────────────────────────────────────────────────────────────
 * #14 Lightweight JSON feed of projects at /projects.json
 * ───────────────────────────────────────────────────────────── */
add_action( 'init', function () {
	add_rewrite_rule( '^projects\.json$', 'index.php?nr_projects_json=1', 'top' );
	add_rewrite_tag( '%nr_projects_json%', '1' );
} );
add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'nr_projects_json' ) ) return;
	header( 'Content-Type: application/json; charset=UTF-8' );
	header( 'Cache-Control: public, max-age=3600' ); // #32 — feed changes rarely
	$items = [];
	foreach ( get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 100, 'orderby' => 'date', 'order' => 'DESC' ] ) as $p ) {
		$items[] = [
			'title' => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $p ),
			'year'  => nr_field( 'project_year', $p->ID ) ?: get_the_date( 'Y', $p ),
			'image' => get_the_post_thumbnail_url( $p->ID, 'nr-hero' ) ?: '',
		];
	}
	$journal = [];
	foreach ( get_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => 60, 'orderby' => 'date', 'order' => 'DESC' ] ) as $p ) {
		$journal[] = [
			'title' => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $p ),
			'year'  => get_the_date( 'Y', $p ),
		];
	}
	echo wp_json_encode( [ 'site' => home_url( '/' ), 'projects' => $items, 'journal' => $journal ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	exit;
} );

/* ─────────────────────────────────────────────────────────────
 * #11 Related projects (same category) — helper used by single-nr_project.php.
 * ───────────────────────────────────────────────────────────── */
function nr_related_projects( $post_id, $limit = 3 ) {
	$terms = wp_get_post_terms( $post_id, 'nr_project_cat', [ 'fields' => 'ids' ] );
	$args  = [
		'post_type'      => 'nr_project',
		'posts_per_page' => $limit,
		'post__not_in'   => [ $post_id ],
		'orderby'        => 'rand',
	];
	if ( $terms ) $args['tax_query'] = [ [ 'taxonomy' => 'nr_project_cat', 'field' => 'term_id', 'terms' => $terms ] ];
	$q = get_posts( $args );
	if ( count( $q ) < $limit ) { // top up with recent if the category is thin
		$more = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => $limit, 'post__not_in' => array_merge( [ $post_id ], wp_list_pluck( $q, 'ID' ) ), 'orderby' => 'date', 'order' => 'DESC' ] );
		$q = array_slice( array_merge( $q, $more ), 0, $limit );
	}
	return $q;
}

/* ─────────────────────────────────────────────────────────────
 * #26 Instagram — curated grid (Meta's API is dead; admin lists posts).
 * Option nr_ig_grid: one per line "image_url | post_url".
 * ───────────────────────────────────────────────────────────── */
function nr_instagram_grid() {
	$raw = trim( (string) nr_opt( 'nr_ig_grid', '' ) );
	if ( $raw === '' ) return;
	$rows = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $rows ) return;
	echo '<section class="nr-ig" aria-label="' . esc_attr__( 'Instagram', 'raveenthiran' ) . '">';
	echo '<span class="nr-eyebrow nr-eyebrow--xs">' . esc_html__( 'Instagram', 'raveenthiran' ) . '</span>';
	echo '<div class="nr-ig__grid">';
	foreach ( $rows as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		$img = $parts[0] ?? ''; $link = $parts[1] ?? ( nr_opt( 'nr_instagram', '#' ) );
		if ( ! $img ) continue;
		printf( '<a class="nr-ig__item" href="%s" target="_blank" rel="noopener"><img src="%s" alt="" loading="lazy" decoding="async"></a>',
			esc_url( $link ), esc_url( $img ) );
	}
	echo '</div></section>';
}

/* ─────────────────────────────────────────────────────────────
 * #27 Footer CTA — printed by footer.php on content pages.
 * ───────────────────────────────────────────────────────────── */
function nr_footer_cta() {
	// Don't print a "Start a project" CTA on the Enquire page itself — it would
	// just be a self-link sitting over the slider.
	if ( is_page_template( 'page-enquire.php' )
		|| ( function_exists( 'nr_enquire_page_id' ) && nr_enquire_page_id() && is_page( nr_enquire_page_id() ) ) ) {
		return;
	}
	$url = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
	printf(
		'<a class="nr-footer__cta nr-btn nr-btn--primary" href="%s"><span>%s</span> <span>→</span></a>',
		esc_url( $url ), esc_html( nr_opt( 'nr_footer_cta', __( 'Start a project', 'raveenthiran' ) ) )
	);
}

/* Flush rewrite rules once after a theme version change so new routes
   (/projects.json, /sitemap.xml) resolve without manually re-saving permalinks. */
add_action( 'init', function () {
	if ( get_option( 'nr_rewrite_v' ) !== NR_THEME_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'nr_rewrite_v', NR_THEME_VERSION );
	}
}, 99 );
