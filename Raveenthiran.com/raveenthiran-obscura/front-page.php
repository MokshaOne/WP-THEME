<?php
/**
 * Front page — fullscreen hero, no scroll.
 * Pulls up to 6 featured projects from the nr_project CPT.
 * Falls back to placeholder slides when none exist yet.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'home';

// Preload the LCP hero image (first featured project) with a responsive
// imagesrcset, so the browser fetches the right-sized hero early — the single
// biggest lever on mobile Largest Contentful Paint.
$nr_lcp_q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1', 'fields' => 'ids', 'no_found_rows' => true ] );
if ( ! $nr_lcp_q->have_posts() ) {
	$nr_lcp_q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
}
$nr_lcp_id = $nr_lcp_q->have_posts() ? (int) get_post_thumbnail_id( $nr_lcp_q->posts[0] ) : 0;
wp_reset_postdata();
if ( $nr_lcp_id ) {
	add_action( 'wp_head', function () use ( $nr_lcp_id ) {
		$src = wp_get_attachment_image_url( $nr_lcp_id, 'nr-hero' );
		if ( ! $src ) return;
		$srcset = wp_get_attachment_image_srcset( $nr_lcp_id, 'nr-hero' );
		printf(
			'<link rel="preload" as="image" href="%s"%s imagesizes="(max-width:900px) 96vw, 46vw" fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : ''
		);
	}, 2 );
}

get_header();

$nr_hero_max = max( 1, min( 24, (int) nr_opt( 'nr_hero_max', 6 ) ) );

$q = new WP_Query( [
	'post_type'      => 'nr_project',
	'posts_per_page' => $nr_hero_max,
	'meta_key'       => 'featured_on_homepage',
	'meta_value'     => '1',
	'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
] );
if ( ! $q->have_posts() ) {
	$q = new WP_Query( [
		'post_type'      => 'nr_project',
		'posts_per_page' => $nr_hero_max,
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
	] );
}

$slides = [];
if ( $q->have_posts() ) {
	while ( $q->have_posts() ) {
		$q->the_post();
		$m = nr_project_meta();
		$slides[] = [
			'id'       => get_the_ID(),
			'title'    => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
			'url'      => get_permalink(),
			'cat'      => $m['cat'] ?: 'Editorial',
			'yr'       => $m['yr'],
			'client'   => $m['client'] ?: '—',
			'loc'      => $m['loc'],
			'thumb'    => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'nr-hero' ) : '',
			'thumb_id' => has_post_thumbnail() ? (int) get_post_thumbnail_id( get_the_ID() ) : 0,
			'label'    => strtolower( wp_strip_all_tags( get_the_title() ) ),
			'edition'  => function_exists( 'nr_field' ) ? ( nr_field( 'project_edition' ) ?: '' ) : '',
		];
	}
	wp_reset_postdata();
} else {
	$samples = [
		[ 'Nachtdienst',          'Editorial',    2024, 'SZ Magazin' ],
		[ 'A House in Mariahilf', 'Editorial',    2024, 'Apartamento' ],
		[ 'The Vienna Notebook',  'Portrait',     2024, 'NYT Magazine' ],
		[ 'Naschmarkt at Dawn',   'Street',       2023, 'Personal' ],
		[ 'Belvedere After Hours','Architecture', 2023, 'Belvedere' ],
		[ 'Rote Bar',             'Event',        2023, 'Hotel Sacher' ],
	];
	foreach ( $samples as $s ) {
		$slides[] = [
			'id' => 0, 'title' => $s[0], 'url' => '#',
			'cat' => $s[1], 'yr' => $s[2], 'client' => $s[3],
			'loc' => nr_opt( 'nr_location', 'Wien' ),
			'thumb' => '', 'thumb_id' => 0, 'label' => strtolower( $s[0] ),
			'edition' => '',
		];
	}
}

$studio       = nr_opt( 'nr_studio', 'Studio Krautwald · Gumpendorfer Str. 81 · 1060 Wien' );
$coords       = nr_opt( 'nr_coords', '48.20°N · 16.36°E' );
$show_eyebrow = nr_opt( 'nr_hero_eyebrow', __( 'Featured work', 'raveenthiran' ) );
$first        = $slides[0] ?? [];
?>

<section class="nr-page nr-hero" data-hero data-slides="<?php echo esc_attr( count( $slides ) ); ?>" data-i="0">

	<div class="nr-hero__frame">
		<span class="nr-reg tl"></span><span class="nr-reg tr"></span>
		<span class="nr-reg bl"></span><span class="nr-reg br"></span>

		<?php foreach ( $slides as $i => $s ) : ?>
			<div class="nr-hero__plate<?php echo $i === 0 ? ' is-active' : ''; ?>" data-slide="<?php echo (int) $i; ?>" aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
				<?php if ( $s['thumb_id'] ) :
					echo wp_get_attachment_image( $s['thumb_id'], 'nr-hero', false, [
						'alt'           => $s['title'],
						'sizes'         => '(max-width:900px) 96vw, 46vw',
						'loading'       => $i === 0 ? 'eager' : 'lazy',
						'decoding'      => 'async',
						'fetchpriority' => $i === 0 ? 'high' : 'auto',
					] );
				elseif ( $s['thumb'] ) : ?>
					<img src="<?php echo esc_url( $s['thumb'] ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" width="2400" height="1600" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"<?php echo $i === 0 ? ' fetchpriority="high"' : ''; ?> decoding="async">
				<?php elseif ( function_exists( 'nr_placeholder' ) ) : ?>
					<?php echo nr_placeholder( $s['label'], true, 'auto' ); ?>
				<?php endif; ?>
				<div class="nr-hero__gradient"></div>
			</div>
		<?php endforeach; ?>

		<div class="nr-hero__meta nr-hero__meta--tl">
			<div><span class="tick"></span><b data-meta-cat><?php echo esc_html( $first['cat'] ?? '' ); ?></b></div>
			<div data-meta-loc><?php echo esc_html( ( $first['loc'] ?? '' ) . ' · ' . $coords ); ?></div>
			<?php if ( ! empty( $first['edition'] ) ) : ?>
				<div data-meta-ed><?php echo esc_html( $first['edition'] ); ?></div>
			<?php endif; ?>
		</div>

		<div class="nr-hero__meta nr-hero__meta--br">
			<div><b data-meta-client><?php echo esc_html( $first['client'] ?? '' ); ?></b> · <span data-meta-year><?php echo esc_html( (string) ( $first['yr'] ?? '' ) ); ?></span></div>
			<div>plate <b data-meta-plate>01</b> of <?php echo esc_html( str_pad( (string) count( $slides ), 2, '0', STR_PAD_LEFT ) ); ?> · <?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></div>
		</div>

		<div class="nr-hero__center">
			<h1 class="nr-hero__title" data-hero-title>
				<?php
				$parts = explode( ' ', $first['title'] ?? '' );
				foreach ( $parts as $i => $p ) {
					$em = $i % 2 === 1;
					printf( '<span class="nr-hero__word%s">%s</span> ', $em ? ' is-em' : '', esc_html( $p ) );
				}
				?>
			</h1>
			<div class="nr-hero__actions">
				<a class="nr-btn" href="<?php echo esc_url( $first['url'] ?? '#' ); ?>" data-hero-link><span><?php echo esc_html( nr_opt( 'nr_cta_view', __( 'View project', 'raveenthiran' ) ) ); ?></span> <span>→</span></a>
			</div>
		</div>
	</div>

	<div class="nr-hero__bottom">
		<div class="nr-hero__counter">
			<span class="nr-hero__counter-n" data-hero-curr-big>01</span>
			<span class="nr-hero__counter-total">of <?php echo esc_html( str_pad( (string) count( $slides ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="nr-hero__counter-meta"><?php echo esc_html( $show_eyebrow ); ?> · <span data-meta-year><?php echo esc_html( (string) ( $first['yr'] ?? '' ) ); ?></span></span>
		</div>

		<div class="nr-hero__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Featured projects', 'raveenthiran' ); ?>">
			<?php foreach ( $slides as $i => $s ) : ?>
				<button type="button" role="tab" data-hero-thumb="<?php echo (int) $i; ?>" class="nr-hero__thumb<?php echo $i === 0 ? ' is-on' : ''; ?>" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $s['title'] ); ?>">
					<span class="nr-hero__thumb-n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="nr-hero__thumb-t"><?php echo esc_html( $s['title'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="nr-hero__nav">
			<button type="button" class="nr-hero__arrow" data-hero-prev aria-label="<?php esc_attr_e( 'Previous', 'raveenthiran' ); ?>"><span>←</span></button>
			<button type="button" class="nr-hero__arrow" data-hero-next aria-label="<?php esc_attr_e( 'Next', 'raveenthiran' ); ?>"><span>→</span></button>
		</div>
	</div>

	<script type="application/json" id="nr-hero-data"><?php echo wp_json_encode( $slides ); ?></script>
</section>

<?php get_footer(); ?>
