<?php
/**
 * The seal — க monogram, the brand mark of the theme.
 *
 * Used in three places only:
 *   1. Top-left of the nav (with hover-reveal label)
 *   2. End-of-poem mark (small, centered)
 *   3. Footer (large, centered)
 *
 * Keep it sparse. The seal earns its presence by not being everywhere.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render the seal as a span. Sizes: '' (default), 'lg', 'sm'.
 */
function kv_seal( $size = '' ) {
    $class = 'seal' . ( $size ? ' seal--' . sanitize_html_class( $size ) : '' );
    return '<span class="' . $class . '">க</span>';
}

/**
 * Seal + revealed label on hover (used in the nav).
 * Defaults to the poet's name from settings.
 */
function kv_seal_set( $label = null ) {
    if ( $label === null ) $label = kv_name();
    ob_start(); ?>
    <span class="seal-set">
        <span class="seal">க</span>
        <span class="seal-set__label"><?php echo esc_html( $label ); ?></span>
    </span>
    <?php
    return ob_get_clean();
}

/**
 * Relief — outlined Tamil character used as backdrop graphic.
 * Variants: 'hero', 'featured', 'pull', 'about', 'footer', 'archive', 'contact', '404'.
 *
 * $letter: any Tamil character or short string to render as outline.
 */
function kv_relief( $letter, $variant ) {
    $class = 'relief relief--' . sanitize_html_class( $variant );
    return '<span class="' . $class . '" aria-hidden="true">' . esc_html( $letter ) . '</span>';
}

/**
 * The asterism mark — section break (and stanza break inside poems).
 * Variants: 'break' (large, between sections), 'stanza' (smaller, inside a poem).
 */
function kv_asterism( $variant = 'break' ) {
    $class = 'asterism asterism--' . sanitize_html_class( $variant );
    return '<div class="' . $class . '">⁂</div>';
}

/**
 * Bilingual section eyebrow: Tamil large + tracked Latin transliteration.
 */
function kv_eyebrow_bi( $tamil, $latin ) {
    ob_start(); ?>
    <div class="eyebrow-bi">
        <div class="eyebrow-bi__ta"><?php echo esc_html( $tamil ); ?></div>
        <div class="eyebrow-bi__la"><?php echo esc_html( $latin ); ?></div>
    </div>
    <?php
    return ob_get_clean();
}
