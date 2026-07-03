<?php
/**
 * Front page — Silence II. Full-bleed plate, giant serif headline with
 * an italic close, ghost numeral, corner meta, category marquee along
 * the bottom edge, curtain-wipe slide transitions.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Preload the LCP plate (first featured project, WebP-aware).
$nr_lcp = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1', 'fields' => 'ids', 'no_found_rows' => true ] );
if ( ! $nr_lcp->have_posts() ) {
	$nr_lcp = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
}
$nr_lcp_id = $nr_lcp->have_posts() ? (int) get_post_thumbnail_id( $nr_lcp->posts[0] ) : 0;
wp_reset_postdata();
if ( $nr_lcp_id ) {
	add_action( 'wp_head', function () use ( $nr_lcp_id ) {
		$src    = wp_get_attachment_image_url( $nr_lcp_id, 'nr-plate' );
		if ( ! $src ) return;
		$srcset = wp_get_attachment_image_srcset( $nr_lcp_id, 'nr-plate' );
		$type   = '';
		if ( function_exists( 'nr_webp_swap_srcset' ) ) {
			$w_set = $srcset ? nr_webp_swap_srcset( $srcset ) : '';
			$w_src = function_exists( 'nr_webp_twin_url' ) ? nr_webp_twin_url( $src ) : '';
			if ( $w_set ) { $srcset = $w_set; $type = ' type="image/webp"'; if ( $w_src ) $src = $w_src; }
			elseif ( $w_src ) { $src = $w_src; $type = ' type="image/webp"'; }
		}
		printf(
			'<link rel="preload" as="image" href="%s"%s imagesizes="100vw"%s fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			$type
		);
	}, 2 );
}

get_header();

$q = new WP_Query( [
	'post_type'      => 'nr_project',
	'posts_per_page' => 8,
	'meta_key'       => 'featured_on_homepage',
	'meta_value'     => '1',
	'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
] );
if ( ! $q->have_posts() ) {
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 8, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
}

$slides = [];
if ( $q->have_posts() ) {
	while ( $q->have_posts() ) {
		$q->the_post();
		$m        = nr_project_meta();
		$slides[] = [
			'id'    => get_the_ID(),
			'title' => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink(),
			'meta'  => trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'], $m['loc'] ] ) ), ' ·' ),
			'thumb' => (int) get_post_thumbnail_id(),
		];
	}
	wp_reset_postdata();
} else {
	foreach ( [ 'Nachtdienst', 'A House in Mariahilf', 'The Vienna Notebook' ] as $t ) {
		$slides[] = [ 'id' => 0, 'title' => $t, 'url' => '#', 'meta' => 'Editorial · Wien', 'thumb' => 0 ];
	}
}
$first = $slides[0] ?? [];

/* marquee copy — category names, falling back to a studio line */
$mq_terms = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => true, 'fields' => 'names' ] );
$mq_words = ( $mq_terms && ! is_wp_error( $mq_terms ) && count( $mq_terms ) )
	? $mq_terms
	: [ 'Photography', nr_opt( 'nr_location', 'Wien' ), nr_opt( 'nr_logo_text', 'raveenthiran' ) ];
$mq_line  = strtoupper( implode( '&ensp;—&ensp;', array_map( 'esc_html', $mq_words ) ) ) . '&ensp;—&ensp;';

/* headline: words in spans (last word italic) — JS rebuilds the same
   structure on every slide change for the stagger reveal */
function nr_hero_words( $title ) {
	$words = preg_split( '/\s+/', trim( (string) $title ) ) ?: [];
	$n     = count( $words );
	$out   = '';
	foreach ( $words as $i => $w ) {
		$cls  = 'nr-w' . ( $i === $n - 1 && $n > 1 ? ' nr-w--i' : '' );
		$out .= '<span class="' . $cls . '"><span class="nr-w__i" style="transition-delay:' . ( $i * 70 ) . 'ms">' . esc_html( $w ) . '</span></span> ';
	}
	return $out;
}
?>

<section class="nr-hero" data-hero data-interval="<?php $nr_iv = (int) nr_opt( 'nr_hero_interval', '7' ); if ( $nr_iv > 100 ) $nr_iv = (int) round( $nr_iv / 1000 ); echo esc_attr( max( 3, $nr_iv ) ); ?>">

	<?php foreach ( $slides as $i => $s ) : ?>
		<div class="nr-plate<?php echo $i === 0 ? ' is-on' : ''; ?>" data-plate="<?php echo (int) $i; ?>" aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
			<?php if ( $s['thumb'] ) :
				echo wp_get_attachment_image( $s['thumb'], 'nr-plate', false, [
					'alt'           => $s['title'],
					'sizes'         => '100vw',
					'loading'       => $i === 0 ? 'eager' : 'lazy',
					'decoding'      => 'async',
					'fetchpriority' => $i === 0 ? 'high' : 'auto',
				] );
			else :
				echo nr_placeholder( $s['title'] );
			endif; ?>
		</div>
	<?php endforeach; ?>

	<?php /* giant ghost numeral — outlined serif, sits behind the chrome */ ?>
	<span class="nr-ghost nr-ui" data-hero-ghost aria-hidden="true">01</span>

	<?php /* corner meta — contact-sheet frame */ ?>
	<span class="nr-corner nr-corner--tl nr-ui" aria-hidden="true"><?php esc_html_e( 'Selected work', 'raveenthiran-silence' ); ?></span>
	<span class="nr-corner nr-corner--tr nr-ui" aria-hidden="true"><?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?> · 48.20°N 16.36°E</span>

	<div class="nr-hero__foot nr-ui">
		<div class="nr-hero__id">
			<span class="nr-hero__meta" data-hero-meta><?php echo esc_html( $first['meta'] ?? '' ); ?></span>
			<a class="nr-hero__title" href="<?php echo esc_url( $first['url'] ?? '#' ); ?>" data-hero-link data-hero-title>
				<?php echo nr_hero_words( $first['title'] ?? '' ); ?>
			</a>
		</div>
		<div class="nr-hero__nav">
			<span class="nr-hero__count"><b data-hero-i>01</b><em>/<?php echo esc_html( str_pad( (string) count( $slides ), 2, '0', STR_PAD_LEFT ) ); ?></em></span>
			<button type="button" data-hero-prev aria-label="<?php esc_attr_e( 'Previous', 'raveenthiran-silence' ); ?>">←</button>
			<button type="button" data-hero-next aria-label="<?php esc_attr_e( 'Next', 'raveenthiran-silence' ); ?>">→</button>
			<span class="nr-hero__bar" aria-hidden="true"><i data-hero-bar></i></span>
		</div>
	</div>

	<?php /* category marquee — the bottom edge never stops moving (motion-on) */ ?>
	<div class="nr-marquee nr-ui" aria-hidden="true">
		<div class="nr-marquee__track"><span><?php echo $mq_line; ?></span><span><?php echo $mq_line; ?></span></div>
	</div>

	<script type="application/json" id="nr-hero-data"><?php echo wp_json_encode( $slides ); ?></script>
</section>

<?php get_footer(); ?>
