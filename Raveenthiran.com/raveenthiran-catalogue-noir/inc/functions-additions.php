<?php
/**
 * Catalogue Noir — supplementary helpers used by templates.
 * Most legacy v2.x features (Related Projects, License Calculator,
 * Quote Layout, EXIF renderer, German Sticky CTA, language switcher)
 * were removed in v3.1.x — see audit C3.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =============================================================
   About field helper — surfaces options_about_bio (legacy ACF
   options-page bio). Used by page-about.php.
   ============================================================= */
if ( ! function_exists( 'nr_about_field' ) ) {
	function nr_about_field( $key, $fallback = '' ) {
		$v = function_exists( 'get_field' ) ? get_field( $key, 'option' ) : null;
		if ( $v !== null && $v !== '' && $v !== false ) return $v;
		$v2 = get_option( 'options_' . $key );
		if ( $v2 !== false && $v2 !== '' ) return $v2;
		return $fallback;
	}
}

/* =============================================================
   Conditional asset enqueue — loads features.css / features.js /
   fixes.css only if those files exist (they currently don't —
   v3 ships everything in assets/css/theme.css, but the check keeps
   the option open for site-specific overrides via a child theme).
   ============================================================= */
add_action( 'wp_enqueue_scripts', 'nr_enqueue_feature_assets' );
function nr_enqueue_feature_assets() {
	$ver      = wp_get_theme()->get( 'Version' );
	$base_uri = get_template_directory_uri();
	$base_dir = get_template_directory();

	if ( file_exists( $base_dir . '/assets/features.css' ) ) {
		wp_enqueue_style( 'nr-features', $base_uri . '/assets/features.css', [ 'nr-theme' ], $ver );
	}
	if ( file_exists( $base_dir . '/assets/features.js' ) ) {
		wp_enqueue_script( 'nr-features', $base_uri . '/assets/features.js', [ 'nr-theme' ], $ver, true );
	}
	if ( file_exists( $base_dir . '/assets/fixes.css' ) ) {
		wp_enqueue_style( 'nr-fixes', $base_uri . '/assets/fixes.css', [ 'nr-theme', 'nr-features' ], $ver );
	}

	/* LatePoint overrides — only when the plugin is active. Loaded LAST so
	   it can override LatePoint's own enqueued stylesheets. */
	if ( shortcode_exists( 'latepoint_book_button' ) || class_exists( 'OsBookingHelper' ) ) {
		wp_enqueue_style(
			'nr-latepoint',
			$base_uri . '/assets/css/latepoint.css',
			[ 'nr-theme' ],
			$ver
		);
	}
}

/* =============================================================
   Category label resolver — defensive utility used when a template
   needs the human-readable name for a taxonomy slug.
   ============================================================= */
if ( ! function_exists( 'nr_get_category_label' ) ) {
	function nr_get_category_label( $slug ) {
		if ( ! $slug ) return '';
		$term = get_term_by( 'slug', $slug, 'nr_project_cat' );
		if ( $term && ! is_wp_error( $term ) ) return $term->name;
		return ucwords( str_replace( [ '-', '_' ], ' ', (string) $slug ) );
	}
}

/* =============================================================
   LatePoint embed — renders the inline booking form when the
   plugin is active. Tries shortcode names in order of preference
   so it works across LatePoint versions: latepoint_book_form
   (newer inline embed) → latepoint_book_button with embedded view
   (older inline mode) → latepoint_book_button (button overlay
   fallback — opens the calendar in LatePoint's own overlay).
   Returns an empty string if LatePoint isn't active so the caller
   can render its own fallback form.
   ============================================================= */
if ( ! function_exists( 'nr_latepoint_embed' ) ) {
	function nr_latepoint_embed( $args = [] ) {
		$service = isset( $args['service'] ) ? sanitize_text_field( $args['service'] ) : '';
		$caption = isset( $args['caption'] ) ? sanitize_text_field( $args['caption'] ) : __( 'Open booking calendar', 'raveenthiran' );

		// 1. User-configured shortcode wins. Set under Appearance → Theme
		//    Settings → § Booking → "LatePoint shortcode". Paste exactly what
		//    works for your LatePoint version, including any attributes.
		$user_shortcode = trim( (string) get_option( 'nr_latepoint_shortcode', '' ) );
		if ( $user_shortcode !== '' ) {
			return do_shortcode( $user_shortcode );
		}

		// 2. Fallback: best-effort auto-detect. We default to
		//    [latepoint_book_button] because it's the most universally
		//    available across LatePoint versions and reliably opens the full
		//    booking flow (calendar → form → confirm) in LatePoint's overlay.
		//    If you want inline, set nr_latepoint_shortcode explicitly.
		if ( shortcode_exists( 'latepoint_book_button' ) ) {
			$attrs = sprintf(
				' caption="%s" hide_summary="no"%s',
				esc_attr( $caption ),
				$service ? sprintf( ' selected_service="%s"', esc_attr( $service ) ) : ''
			);
			return do_shortcode( '[latepoint_book_button' . $attrs . ']' );
		}
		if ( shortcode_exists( 'latepoint_book_form' ) ) {
			$attrs = $service ? sprintf( ' selected_service="%s"', esc_attr( $service ) ) : '';
			return do_shortcode( '[latepoint_book_form' . $attrs . ']' );
		}
		return '';
	}
}

/* =============================================================
   Project cover image — uses featured image first, then ACF
   project_cover field, then the striped placeholder. Defensive
   utility; not currently called by any template in v3 but useful
   from custom code/shortcodes.
   ============================================================= */
if ( ! function_exists( 'nr_get_project_cover' ) ) {
	function nr_get_project_cover( $post_id, $size = 'nr-card' ) {
		if ( has_post_thumbnail( $post_id ) ) {
			return get_the_post_thumbnail( $post_id, $size, [
				'class'   => 'nr-img',
				'loading' => 'lazy',
			] );
		}
		if ( function_exists( 'nr_field' ) ) {
			$cover = nr_field( 'project_cover', $post_id );
			$att_id = is_array( $cover ) ? (int) ( $cover['ID'] ?? 0 ) : ( is_numeric( $cover ) ? (int) $cover : 0 );
			if ( $att_id ) {
				return wp_get_attachment_image( $att_id, $size, false, [
					'class'    => 'nr-img',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'alt'      => esc_attr( get_the_title( $post_id ) ),
				] );
			}
		}
		if ( function_exists( 'nr_placeholder' ) ) {
			return nr_placeholder( get_the_title( $post_id ) );
		}
		return '';
	}
}
