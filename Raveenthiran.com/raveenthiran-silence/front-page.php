<?php
/**
 * Front page — full-bleed plate slideshow. No frame, no border:
 * the photograph is the interface.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Preload the LCP plate (first featured project, WebP-aware).
$sl_lcp = new WP_Query( [ 'post_type' => sl_pt(), 'posts_per_page' => 1, 'meta_key' => sl_featured_key(), 'meta_value' => '1', 'fields' => 'ids', 'no_found_rows' => true ] );
if ( ! $sl_lcp->have_posts() ) {
	$sl_lcp = new WP_Query( [ 'post_type' => sl_pt(), 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
}
$sl_lcp_id = $sl_lcp->have_posts() ? (int) get_post_thumbnail_id( $sl_lcp->posts[0] ) : 0;
wp_reset_postdata();
if ( $sl_lcp_id ) {
	add_action( 'wp_head', function () use ( $sl_lcp_id ) {
		$src    = wp_get_attachment_image_url( $sl_lcp_id, 'sl-plate' );
		if ( ! $src ) return;
		$srcset = wp_get_attachment_image_srcset( $sl_lcp_id, 'sl-plate' );
		$type   = '';
		if ( function_exists( 'sl_webp_swap_srcset' ) ) {
			$w_set = $srcset ? sl_webp_swap_srcset( $srcset ) : '';
			$w_src = function_exists( 'sl_webp_twin_url' ) ? sl_webp_twin_url( $src ) : '';
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
	'post_type'      => sl_pt(),
	'posts_per_page' => 8,
	'meta_key'       => sl_featured_key(),
	'meta_value'     => '1',
	'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
] );
if ( ! $q->have_posts() ) {
	$q = new WP_Query( [ 'post_type' => sl_pt(), 'posts_per_page' => 8, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
}

$slides = [];
if ( $q->have_posts() ) {
	while ( $q->have_posts() ) {
		$q->the_post();
		$m        = sl_project_meta();
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
?>

<section class="sl-hero" data-hero data-interval="<?php echo esc_attr( max( 3, (int) sl_opt( 'sl_hero_interval', '7' ) ) ); ?>">

	<?php foreach ( $slides as $i => $s ) : ?>
		<div class="sl-plate<?php echo $i === 0 ? ' is-on' : ''; ?>" data-plate="<?php echo (int) $i; ?>" aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
			<?php if ( $s['thumb'] ) :
				echo wp_get_attachment_image( $s['thumb'], 'sl-plate', false, [
					'alt'           => $s['title'],
					'sizes'         => '100vw',
					'loading'       => $i === 0 ? 'eager' : 'lazy',
					'decoding'      => 'async',
					'fetchpriority' => $i === 0 ? 'high' : 'auto',
				] );
			else :
				echo sl_placeholder( $s['title'] );
			endif; ?>
		</div>
	<?php endforeach; ?>

	<div class="sl-hero__foot sl-ui">
		<div class="sl-hero__id">
			<a class="sl-hero__title" href="<?php echo esc_url( $first['url'] ?? '#' ); ?>" data-hero-link><?php echo esc_html( $first['title'] ?? '' ); ?></a>
			<span class="sl-hero__meta" data-hero-meta><?php echo esc_html( $first['meta'] ?? '' ); ?></span>
		</div>
		<div class="sl-hero__nav">
			<span class="sl-hero__count"><b data-hero-i>01</b> — <?php echo esc_html( str_pad( (string) count( $slides ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<button type="button" data-hero-prev aria-label="<?php esc_attr_e( 'Previous', 'raveenthiran-silence' ); ?>">←</button>
			<button type="button" data-hero-next aria-label="<?php esc_attr_e( 'Next', 'raveenthiran-silence' ); ?>">→</button>
		</div>
	</div>

	<script type="application/json" id="sl-hero-data"><?php echo wp_json_encode( $slides ); ?></script>
</section>

<?php get_footer(); ?>
