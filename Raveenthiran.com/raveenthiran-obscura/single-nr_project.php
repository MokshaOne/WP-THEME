<?php
/**
 * Single project — Studio: full-bleed hero, editorial header + meta,
 * a vertical stack of large plates, related work, and a next project.
 * Non-Obscura: this scrolls. Data (ACF gallery, EXIF, meta) unchanged.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();
the_post();

$m       = nr_project_meta();
$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : [];
if ( ! is_array( $gallery ) ) $gallery = [];

$plates = [];
foreach ( $gallery as $g ) {
	$id = 0; $url = ''; $w = 0; $h = 0;
	if ( is_array( $g ) ) {
		$id  = (int) ( $g['ID'] ?? $g['id'] ?? 0 );
		$url = $g['url'] ?? '';
		$w   = (int) ( $g['width']  ?? 0 );
		$h   = (int) ( $g['height'] ?? 0 );
	} elseif ( is_numeric( $g ) ) {
		$id = (int) $g;
	}
	$is_video = $id && wp_attachment_is( 'video', $id );
	if ( $is_video ) {
		$meta  = wp_get_attachment_metadata( $id );
		$url   = wp_get_attachment_url( $id ) ?: $url;
		$w     = $w ?: (int) ( $meta['width']  ?? 0 );
		$h     = $h ?: (int) ( $meta['height'] ?? 0 );
		$poster = (string) get_the_post_thumbnail_url( $id, 'nr-hero' );
		if ( ! $poster ) {
			$pimg = get_post_meta( $id, '_thumbnail_id', true );
			if ( $pimg ) $poster = (string) wp_get_attachment_image_url( (int) $pimg, 'nr-hero' );
		}
		if ( ! $url ) continue;
		$plates[] = [ 'id' => $id, 'type' => 'video', 'url' => $url, 'mime' => get_post_mime_type( $id ) ?: 'video/mp4', 'poster' => $poster, 'w' => $w, 'h' => $h, 'orient' => ( $h > $w ) ? 'portrait' : 'landscape' ];
		continue;
	}
	if ( $id && ( ! $url || ! $w || ! $h ) ) {
		$src  = wp_get_attachment_image_src( $id, 'nr-hero' );
		$meta = wp_get_attachment_metadata( $id );
		if ( $src ) { $url = $url ?: $src[0]; $w = $w ?: (int) $src[1]; $h = $h ?: (int) $src[2]; }
		if ( $meta ) { $w = $w ?: (int) ( $meta['width'] ?? 0 ); $h = $h ?: (int) ( $meta['height'] ?? 0 ); }
	}
	if ( ! $url ) continue;
	$plates[] = [ 'id' => $id, 'type' => 'image', 'url' => $url, 'w' => $w, 'h' => $h, 'orient' => ( $h > $w ) ? 'portrait' : 'landscape' ];
}

// Hero = featured image if present, else first plate.
$hero_id = has_post_thumbnail() ? (int) get_post_thumbnail_id() : ( $plates[0]['id'] ?? 0 );
$lede    = get_the_excerpt();
?>
<article class="st-project">

	<?php /* ── hero ─────────────────────────────────────────── */ ?>
	<div class="st-project__hero" style="view-transition-name:vt-project-<?php the_ID(); ?>">
		<?php if ( $hero_id ) :
			echo wp_get_attachment_image( $hero_id, 'nr-hero', false, [
				'alt'           => get_the_title(),
				'sizes'         => '100vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'class'         => 'st-project__hero-img',
			] );
		else :
			echo nr_placeholder( strtolower( get_the_title() ), false, '3/2' );
		endif; ?>
	</div>

	<?php /* ── header + meta ───────────────────────────────── */ ?>
	<header class="st-wrap st-project__head">
		<div class="st-project__intro">
			<nav class="st-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'raveenthiran' ); ?>">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>"><?php esc_html_e( 'Work', 'raveenthiran' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( $m['cat'] ) : ?><span><?php echo esc_html( $m['cat'] ); ?></span><span aria-hidden="true">/</span><?php endif; ?>
				<span class="is-current"><?php the_title(); ?></span>
			</nav>
			<h1 class="st-project__title"><?php the_title(); ?></h1>
			<?php if ( $lede ) : ?><p class="st-project__lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
		</div>

		<aside class="st-project__meta">
			<?php
			$rows = [
				[ nr_opt( 'nr_meta_client',     __( 'Client',     'raveenthiran' ) ), $m['client'] ?: '—' ],
				[ nr_opt( 'nr_meta_year',       __( 'Year',       'raveenthiran' ) ), (string) $m['yr'] ],
				[ nr_opt( 'nr_meta_location',   __( 'Location',   'raveenthiran' ) ), $m['loc'] ?: '—' ],
				[ nr_opt( 'nr_meta_discipline', __( 'Discipline', 'raveenthiran' ) ), $m['cat'] ?: 'Editorial' ],
				[ nr_opt( 'nr_meta_frames',     __( 'Frames',     'raveenthiran' ) ), function_exists( 'nr_field' ) ? ( nr_field( 'project_frames' ) ?: '—' ) : '—' ],
			];
			$format = function_exists( 'nr_field' ) ? nr_field( 'project_format' ) : '';
			if ( $format ) $rows[] = [ nr_opt( 'nr_meta_format', __( 'Format', 'raveenthiran' ) ), $format ];
			$nr_series = function_exists( 'nr_project_series' ) ? nr_project_series( get_the_ID() ) : null;
			if ( $nr_series ) $rows[] = [ __( 'Series', 'raveenthiran' ), $nr_series->name ];
			foreach ( $rows as $r ) : ?>
				<div class="st-project__meta-row"><span><?php echo esc_html( $r[0] ); ?></span><span class="st-project__meta-v"><?php echo esc_html( $r[1] ); ?></span></div>
			<?php endforeach; ?>
			<a class="st-btn st-btn--primary st-project__commission" href="<?php echo esc_url( add_query_arg( [ 'service' => sanitize_title( $m['cat'] ?: 'editorial' ), 'ref' => get_the_title() ], function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ) ); ?>"><?php echo esc_html( nr_opt( 'nr_cta_commission', __( 'Commission similar', 'raveenthiran' ) ) ); ?> →</a>
		</aside>
	</header>

	<?php /* ── plates ──────────────────────────────────────── */ ?>
	<?php if ( ! empty( $plates ) ) : ?>
	<div class="st-project__gallery">
		<?php foreach ( $plates as $i => $p ) :
			$alt = sprintf( __( '%1$s — plate %2$s', 'raveenthiran' ), get_the_title(), $i + 1 );
			$exif_str = '';
			if ( $p['id'] && function_exists( 'nr_get_exif' ) ) {
				$ex   = nr_get_exif( $p['id'] );
				$bits = array_filter( [ $ex['camera'] ?? '', $ex['focal'] ?? '', $ex['aperture'] ?? '', $ex['shutter'] ?? '', $ex['iso'] ?? '' ] );
				$exif_str = implode( '  ·  ', $bits );
			}
		?>
			<figure class="st-project__figure is-<?php echo esc_attr( $p['orient'] ); ?>" data-reveal>
				<?php if ( ( $p['type'] ?? '' ) === 'video' ) : ?>
					<video <?php echo $p['poster'] ? 'poster="' . esc_url( $p['poster'] ) . '"' : ''; ?> autoplay muted loop playsinline preload="metadata"<?php echo $p['w'] && $p['h'] ? ' width="' . (int) $p['w'] . '" height="' . (int) $p['h'] . '"' : ''; ?>>
						<source src="<?php echo esc_url( $p['url'] ); ?>" type="<?php echo esc_attr( $p['mime'] ); ?>">
					</video>
				<?php elseif ( $p['id'] ) :
					echo wp_get_attachment_image( $p['id'], 'nr-hero', false, [ 'alt' => $alt, 'sizes' => $p['orient'] === 'portrait' ? '(max-width:900px) 100vw, 60vw' : '(max-width:900px) 100vw, 88vw', 'loading' => $i === 0 ? 'eager' : 'lazy', 'decoding' => 'async' ] );
				else : ?>
					<img src="<?php echo esc_url( $p['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="<?php echo (int) $p['w']; ?>" height="<?php echo (int) $p['h']; ?>" loading="lazy" decoding="async">
				<?php endif; ?>
				<?php if ( $exif_str ) : ?><figcaption class="st-project__cap"><?php echo esc_html( $exif_str ); ?></figcaption><?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php /* ── series / related ────────────────────────────── */ ?>
	<?php
	$nr_sib = ( function_exists( 'nr_series_siblings' ) && $nr_series ) ? nr_series_siblings( get_the_ID(), 4 ) : [];
	$nr_rel = function_exists( 'nr_related_projects' ) ? nr_related_projects( get_the_ID(), 3 ) : [];
	if ( $nr_sib || $nr_rel ) : ?>
	<section class="st-wrap st-related">
		<?php if ( $nr_sib && $nr_series ) : ?>
			<div class="st-related__group">
				<span class="st-eyebrow"><?php printf( esc_html__( 'Series · %s', 'raveenthiran' ), esc_html( $nr_series->name ) ); ?></span>
				<div class="st-related__links"><?php foreach ( $nr_sib as $sp ) printf( '<a href="%s" data-thumb="%s">%s</a>', esc_url( get_permalink( $sp ) ), esc_url( (string) get_the_post_thumbnail_url( $sp, 'nr-thumb' ) ), esc_html( get_the_title( $sp ) ) ); ?></div>
			</div>
		<?php endif; ?>
		<?php if ( $nr_rel ) : ?>
			<div class="st-related__group">
				<span class="st-eyebrow"><?php esc_html_e( 'More work', 'raveenthiran' ); ?></span>
				<div class="st-related__links"><?php foreach ( $nr_rel as $rp ) printf( '<a href="%s" data-thumb="%s">%s</a>', esc_url( get_permalink( $rp ) ), esc_url( (string) get_the_post_thumbnail_url( $rp, 'nr-thumb' ) ), esc_html( get_the_title( $rp ) ) ); ?></div>
			</div>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php /* ── next project ────────────────────────────────── */ ?>
	<?php
	$next = get_adjacent_post( false, '', false );
	if ( ! $next ) {
		$nq = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'post__not_in' => [ get_the_ID() ], 'orderby' => 'rand', 'no_found_rows' => true ] );
		if ( $nq->have_posts() ) { $nq->the_post(); $next = get_post(); wp_reset_postdata(); }
	}
	if ( $next && $next->post_type === 'nr_project' ) :
		$nid = (int) get_post_thumbnail_id( $next->ID );
	?>
	<a class="st-next" href="<?php echo esc_url( get_permalink( $next ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Next project: %s', 'raveenthiran' ), get_the_title( $next ) ) ); ?>">
		<div class="st-next__media">
			<?php if ( $nid ) echo wp_get_attachment_image( $nid, 'nr-hero', false, [ 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '100vw', 'class' => 'st-next__img' ] ); ?>
			<div class="st-next__veil"></div>
		</div>
		<div class="st-wrap st-next__in">
			<span class="st-eyebrow st-eyebrow--light"><?php esc_html_e( 'Next project', 'raveenthiran' ); ?></span>
			<span class="st-next__t"><?php echo esc_html( get_the_title( $next ) ); ?> <span aria-hidden="true">→</span></span>
		</div>
	</a>
	<?php endif; ?>

</article>

<?php get_footer(); ?>
