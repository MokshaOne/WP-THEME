<?php
/**
 * Front page — Silence III, the scrolling narrative.
 * Sticky opening plate → full-viewport work chapters with parallax →
 * pinned horizontal film strip → serif statement + stats → journal
 * teaser → giant CTA with marquee edge.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$featured = new WP_Query( [
	'post_type'      => 'nr_project',
	'posts_per_page' => 5,
	'meta_key'       => 'featured_on_homepage',
	'meta_value'     => '1',
	'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
] );
if ( ! $featured->have_posts() ) {
	$featured = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 5, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
}
$projects = [];
if ( $featured->have_posts() ) {
	while ( $featured->have_posts() ) {
		$featured->the_post();
		$m          = nr_project_meta();
		$projects[] = [
			'id'    => get_the_ID(),
			'title' => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink(),
			'meta'  => trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'], $m['loc'] ] ) ), ' ·' ),
			'thumb' => (int) get_post_thumbnail_id(),
		];
	}
	wp_reset_postdata();
}
$open     = $projects[0] ?? [ 'id' => 0, 'title' => 'Silence', 'url' => '#', 'meta' => 'Photography · Wien', 'thumb' => 0 ];
$chapters = array_slice( $projects, 1 );

// Preload the LCP plate (the opening image, WebP-aware).
if ( ! empty( $open['thumb'] ) ) {
	add_action( 'wp_head', function () use ( $open ) {
		$src = wp_get_attachment_image_url( $open['thumb'], 'nr-plate' );
		if ( ! $src ) return;
		$srcset = wp_get_attachment_image_srcset( $open['thumb'], 'nr-plate' );
		$type   = '';
		if ( function_exists( 'nr_webp_swap_srcset' ) ) {
			$w_set = $srcset ? nr_webp_swap_srcset( $srcset ) : '';
			$w_src = function_exists( 'nr_webp_twin_url' ) ? nr_webp_twin_url( $src ) : '';
			if ( $w_set ) { $srcset = $w_set; $type = ' type="image/webp"'; if ( $w_src ) $src = $w_src; }
			elseif ( $w_src ) { $src = $w_src; $type = ' type="image/webp"'; }
		}
		printf(
			'<link rel="preload" as="image" href="%s"%s imagesizes="100vw"%s fetchpriority="high">' . "\n",
			esc_url( $src ), $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '', $type
		);
	}, 2 );
}

get_header();

// strip cells — the wider recent selection (fills the film strip)
$strip = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 10, 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );

$stats = array_filter( [
	__( 'Projects', 'raveenthiran-silence' )     => nr_opt( 'nr_stats_proj', '' ),
	__( 'Countries', 'raveenthiran-silence' )    => nr_opt( 'nr_stats_cnty', '' ),
	__( 'Publications', 'raveenthiran-silence' ) => nr_opt( 'nr_stats_pubs', '' ),
	__( 'Awards', 'raveenthiran-silence' )       => nr_opt( 'nr_stats_awd', '' ),
] );
$journal = get_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => 3 ] );

$mq_terms = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => true, 'fields' => 'names' ] );
$mq_words = ( $mq_terms && ! is_wp_error( $mq_terms ) && count( $mq_terms ) )
	? $mq_terms : [ 'Photography', nr_opt( 'nr_location', 'Wien' ), nr_opt( 'nr_logo_text', 'raveenthiran' ) ];
$mq_line  = strtoupper( implode( '&ensp;—&ensp;', array_map( 'esc_html', $mq_words ) ) ) . '&ensp;—&ensp;';

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

<?php /* 1 · opening — sticky plate the story scrolls over */ ?>
<section class="nr-open">
	<div class="nr-open__stage">
		<?php if ( $open['thumb'] ) :
			echo wp_get_attachment_image( $open['thumb'], 'nr-plate', false, [
				'alt' => $open['title'], 'sizes' => '100vw',
				'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high',
			] );
		else : echo nr_placeholder( $open['title'] ); endif; ?>
		<div class="nr-open__shade"></div>
		<span class="nr-corner nr-corner--tl nr-ui"><?php esc_html_e( 'Selected work', 'raveenthiran-silence' ); ?></span>
		<span class="nr-corner nr-corner--tr nr-ui"><?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?> · 48.20°N 16.36°E</span>
		<div class="nr-open__foot nr-ui">
			<div class="nr-open__id">
				<span class="nr-open__meta"><?php echo esc_html( $open['meta'] ); ?></span>
				<a class="nr-open__title" href="<?php echo esc_url( $open['url'] ); ?>"><?php echo nr_hero_words( $open['title'] ); ?></a>
			</div>
			<span class="nr-open__hint"><?php esc_html_e( 'Scroll', 'raveenthiran-silence' ); ?></span>
		</div>
	</div>
</section>

<div class="nr-chapters">

	<?php /* 2 · work chapters */ ?>
	<?php foreach ( $chapters as $i => $p ) : ?>
		<section class="nr-chapter">
			<div class="nr-chapter__media" data-parallax>
				<?php if ( $p['thumb'] ) :
					echo wp_get_attachment_image( $p['thumb'], 'nr-plate', false, [
						'alt' => $p['title'], 'sizes' => '100vw', 'loading' => 'lazy', 'decoding' => 'async',
					] );
				else : echo nr_placeholder( $p['title'] ); endif; ?>
			</div>
			<div class="nr-chapter__shade"></div>
			<span class="nr-ghost" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 2 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<a class="nr-chapter__body" href="<?php echo esc_url( $p['url'] ); ?>">
				<span class="nr-chapter__id nr-rise">
					<span class="nr-chapter__meta"><?php echo esc_html( $p['meta'] ); ?></span>
					<span class="nr-chapter__title"><?php echo esc_html( $p['title'] ); ?></span>
				</span>
				<span class="nr-chapter__go"><?php esc_html_e( 'View project', 'raveenthiran-silence' ); ?> →</span>
			</a>
		</section>
	<?php endforeach; ?>

	<?php /* 3 · the film strip — pinned horizontal scroll */ ?>
	<?php if ( count( $strip ) > 2 ) : ?>
	<section class="nr-strip" data-strip>
		<div class="nr-strip__pin">
			<div class="nr-strip__head">
				<h2 class="nr-eyebrow"><?php esc_html_e( 'From the archive', 'raveenthiran-silence' ); ?></h2>
				<a class="nr-eyebrow" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>"><?php esc_html_e( 'Full index', 'raveenthiran-silence' ); ?> →</a>
			</div>
			<div class="nr-strip__track" data-strip-track>
				<?php foreach ( $strip as $k => $sp ) : $sm = nr_project_meta( $sp->ID ); ?>
					<figure class="nr-strip__cell">
						<a href="<?php echo esc_url( get_permalink( $sp ) ); ?>">
							<?php echo get_the_post_thumbnail( $sp, 'nr-index', [ 'loading' => 'lazy', 'decoding' => 'async' ] ) ?: nr_placeholder( get_the_title( $sp ) ); ?>
							<figcaption class="nr-strip__cap">
								<b><?php echo esc_html( get_the_title( $sp ) ); ?></b>
								<span><?php echo esc_html( trim( ( $sm['cat'] ? $sm['cat'] . ' · ' : '' ) . $sm['yr'], ' ·' ) ); ?></span>
							</figcaption>
						</a>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php /* 4 · statement + stats */ ?>
	<section class="nr-statement">
		<?php $lede = nr_opt( 'nr_about_lede', '' ); if ( $lede ) : ?>
			<p class="nr-rise"><?php echo esc_html( $lede ); ?></p>
		<?php else : ?>
			<p class="nr-rise"><?php esc_html_e( 'Quiet photographs for people and places that deserve a second look —', 'raveenthiran-silence' ); ?> <em><?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?></em>.</p>
		<?php endif; ?>
		<?php if ( $stats ) : ?>
			<dl class="nr-stats nr-rise">
				<?php foreach ( $stats as $label => $value ) : ?>
					<div><dd><?php echo esc_html( $value ); ?></dd><dt><?php echo esc_html( $label ); ?></dt></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</section>

	<?php /* 5 · journal teaser */ ?>
	<?php if ( $journal ) : ?>
	<section class="nr-jteaser">
		<h2 class="nr-eyebrow nr-rise"><?php esc_html_e( 'Journal', 'raveenthiran-silence' ); ?></h2>
		<ol class="nr-journal nr-rise">
			<?php foreach ( $journal as $j ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $j ) ); ?>">
					<time datetime="<?php echo esc_attr( get_the_date( 'c', $j ) ); ?>"><?php echo esc_html( get_the_date( 'Y · m', $j ) ); ?></time>
					<span><?php echo esc_html( get_the_title( $j ) ); ?></span>
				</a></li>
			<?php endforeach; ?>
		</ol>
	</section>
	<?php endif; ?>

	<?php /* 6 · CTA + marquee */ ?>
	<section class="nr-cta">
		<a href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>">
			<span class="nr-cta__t nr-rise"><?php esc_html_e( 'Let’s make something quiet.', 'raveenthiran-silence' ); ?></span>
			<span class="nr-cta__sub"><?php echo esc_html( nr_opt( 'nr_avail_text', '' ) ?: __( 'Enquire', 'raveenthiran-silence' ) ); ?> →</span>
		</a>
	</section>
	<div class="nr-marquee" aria-hidden="true">
		<div class="nr-marquee__track"><span><?php echo $mq_line; ?></span><span><?php echo $mq_line; ?></span></div>
	</div>

</div>

<?php get_footer(); ?>
