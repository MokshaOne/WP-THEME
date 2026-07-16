<?php
/**
 * #67 — Before / after comparison slider.
 *
 * A reusable shortcode for journal posts and project write-ups (e.g. a
 * straight-out-of-camera frame vs the final grade). Markup-only here; the
 * drag interaction lives in assets/js/theme.js (.nr-compare) and the styling
 * in assets/css/theme.css, so no extra requests are added.
 *
 * Usage:
 *   [nr_compare before="123" after="456"]                      (attachment IDs)
 *   [nr_compare before="https://…/raw.jpg" after="https://…/final.jpg"]
 *   [nr_compare before="456" after="789" before_label="Film" after_label="Graded"]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'nr_compare', 'nr_compare_shortcode' );

function nr_compare_shortcode( $atts ) {
	$a = shortcode_atts( [
		'before'       => '',
		'after'        => '',
		'before_label' => __( 'Before', 'raveenthiran' ),
		'after_label'  => __( 'After', 'raveenthiran' ),
		'start'        => '50', // initial handle position, %
	], $atts, 'nr_compare' );

	$before = nr_compare_resolve( $a['before'] );
	$after  = nr_compare_resolve( $a['after'] );
	if ( ! $before || ! $after ) return '';

	$start = max( 0, min( 100, (int) $a['start'] ) );

	ob_start();
	?>
	<div class="nr-compare" data-compare style="--nr-compare-start:<?php echo esc_attr( $start ); ?>%">
		<figure class="nr-compare__media nr-compare__media--after">
			<img src="<?php echo esc_url( $after['url'] ); ?>" alt="<?php echo esc_attr( $after['alt'] ); ?>"
				width="<?php echo esc_attr( $after['w'] ); ?>" height="<?php echo esc_attr( $after['h'] ); ?>" loading="lazy" decoding="async">
			<?php if ( $a['after_label'] ) : ?><figcaption class="nr-compare__label nr-compare__label--after"><?php echo esc_html( $a['after_label'] ); ?></figcaption><?php endif; ?>
		</figure>
		<figure class="nr-compare__media nr-compare__media--before" data-compare-clip>
			<img src="<?php echo esc_url( $before['url'] ); ?>" alt="<?php echo esc_attr( $before['alt'] ); ?>"
				width="<?php echo esc_attr( $before['w'] ); ?>" height="<?php echo esc_attr( $before['h'] ); ?>" loading="lazy" decoding="async">
			<?php if ( $a['before_label'] ) : ?><figcaption class="nr-compare__label nr-compare__label--before"><?php echo esc_html( $a['before_label'] ); ?></figcaption><?php endif; ?>
		</figure>
		<input type="range" class="nr-compare__range" min="0" max="100" value="<?php echo esc_attr( $start ); ?>"
			aria-label="<?php esc_attr_e( 'Reveal before / after', 'raveenthiran' ); ?>" data-compare-range>
		<span class="nr-compare__handle" aria-hidden="true"><span class="nr-compare__grip"></span></span>
	</div>
	<?php
	return ob_get_clean();
}

/* Accept an attachment ID or a raw URL; return url/alt/width/height. */
function nr_compare_resolve( $ref ) {
	$ref = trim( (string) $ref );
	if ( $ref === '' ) return null;

	if ( ctype_digit( $ref ) ) {
		$id  = (int) $ref;
		$src = wp_get_attachment_image_src( $id, 'large' );
		if ( ! $src ) return null;
		return [
			'url' => $src[0],
			'w'   => $src[1],
			'h'   => $src[2],
			'alt' => trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ),
		];
	}

	if ( filter_var( $ref, FILTER_VALIDATE_URL ) ) {
		return [ 'url' => $ref, 'w' => '', 'h' => '', 'alt' => '' ];
	}
	return null;
}
