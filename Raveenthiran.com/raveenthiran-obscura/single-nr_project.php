<?php
/**
 * Single project — split layout: title/meta panel + horizontal plate rail.
 * Non-scroll: rail scrolls horizontally; page stays still.
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
	if ( $id && ( ! $url || ! $w || ! $h ) ) {
		$src  = wp_get_attachment_image_src( $id, 'nr-hero' );
		$meta = wp_get_attachment_metadata( $id );
		if ( $src ) {
			$url = $url ?: $src[0];
			$w   = $w   ?: (int) $src[1];
			$h   = $h   ?: (int) $src[2];
		}
		if ( $meta ) {
			$w = $w ?: (int) ( $meta['width']  ?? 0 );
			$h = $h ?: (int) ( $meta['height'] ?? 0 );
		}
	}
	if ( ! $url ) continue;
	$plates[] = [
		'id'     => $id,
		'url'    => $url,
		'w'      => $w,
		'h'      => $h,
		'orient' => ( $h > $w ) ? 'portrait' : 'landscape',
	];
}

if ( empty( $plates ) && has_post_thumbnail() ) {
	$pid  = get_post_thumbnail_id();
	$src  = wp_get_attachment_image_src( $pid, 'nr-hero' );
	if ( $src ) {
		$plates[] = [
			'id'     => $pid,
			'url'    => $src[0],
			'w'      => (int) $src[1],
			'h'      => (int) $src[2],
			'orient' => ( $src[2] > $src[1] ) ? 'portrait' : 'landscape',
		];
	}
}

$lede = get_the_excerpt();
?>
<section class="nr-page nr-project">

	<div class="nr-project__crumbs">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>"><?php esc_html_e( 'Work', 'raveenthiran' ); ?></a>
		<span>/</span>
		<?php if ( $m['cat'] ) : ?><a href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>"><?php echo esc_html( $m['cat'] ); ?></a><span>/</span><?php endif; ?>
		<span class="is-current"><?php the_title(); ?></span>
		<a class="nr-project__close" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>">close ✕</a>
	</div>

	<div class="nr-project__layout">
		<aside class="nr-project__body">
			<div class="nr-project__title-block">
				<span class="nr-eyebrow"><?php
					printf( esc_html__( 'Project № %1$s · %2$s', 'raveenthiran' ),
						esc_html( $m['n'] ), esc_html( $m['cat'] ?: 'Editorial' ) );
				?></span>
				<h1 class="nr-display nr-display--md nr-project__title">
					<?php
					$parts = explode( ' ', html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) );
					foreach ( $parts as $i => $p ) {
						printf( '<span class="nr-hero__word%s">%s</span> ', $i % 2 === 1 ? ' is-em' : '', esc_html( $p ) );
					}
					?>
				</h1>

				<?php if ( $lede ) : ?>
					<p class="nr-project__lede"><?php echo esc_html( $lede ); ?></p>
				<?php endif; ?>
			</div>

			<ul class="nr-project__meta">
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
				foreach ( $rows as $r ) : ?>
					<li>
						<span><?php echo esc_html( $r[0] ); ?></span>
						<span class="nr-project__meta-v"><?php echo esc_html( $r[1] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="nr-project__actions">
				<a class="nr-btn nr-btn--primary"
				   href="<?php echo esc_url( add_query_arg( [ 'service' => sanitize_title( $m['cat'] ?: 'editorial' ), 'ref' => get_the_title() ], function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ) ); ?>">
					<span><?php echo esc_html( nr_opt( 'nr_cta_commission', __( 'Commission similar', 'raveenthiran' ) ) ); ?></span> <span>→</span>
				</a>
				<a class="nr-btn" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>"><span><?php echo esc_html( nr_opt( 'nr_cta_back', __( 'Back to work', 'raveenthiran' ) ) ); ?></span></a>
			</div>

				<?php $nr_rel = function_exists( 'nr_related_projects' ) ? nr_related_projects( get_the_ID(), 3 ) : []; if ( $nr_rel ) : ?>
				<nav class="nr-project__related" aria-label="<?php esc_attr_e( 'Related projects', 'raveenthiran' ); ?>">
					<span class="nr-eyebrow nr-eyebrow--xs"><?php esc_html_e( 'More work', 'raveenthiran' ); ?></span>
					<span class="nr-project__related-links"><?php foreach ( $nr_rel as $rp ) printf( '<a href="%s">%s</a>', esc_url( get_permalink( $rp ) ), esc_html( get_the_title( $rp ) ) ); ?></span>
				</nav>
				<?php endif; ?>
		</aside>

		<?php if ( ! empty( $plates ) ) : ?>
		<div class="nr-project__rail" role="region" aria-label="<?php esc_attr_e( 'Project plates', 'raveenthiran' ); ?>" data-h-rail>
			<div class="nr-project__counter" aria-live="polite" data-plate-counter>
				<span class="nr-project__counter-cur" data-plate-cur>01</span>
				<span class="nr-project__counter-sep">/</span>
				<span class="nr-project__counter-total"><?php echo esc_html( str_pad( (string) count( $plates ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			</div>
			<div class="nr-project__rail-track">
				<?php foreach ( $plates as $i => $p ) :
					$alt   = sprintf( __( '%1$s — plate %2$s', 'raveenthiran' ), get_the_title(), $i + 1 );
					// "Shot on" — built from EXIF captured on upload (inc/performance.php).
					$exif_str = '';
					if ( $p['id'] && function_exists( 'nr_get_exif' ) ) {
						$ex   = nr_get_exif( $p['id'] );
						$bits = array_filter( [ $ex['camera'] ?? '', $ex['focal'] ?? '', $ex['aperture'] ?? '', $ex['shutter'] ?? '', $ex['iso'] ?? '' ] );
						$exif_str = implode( '  ·  ', $bits );
					}
				?>
					<figure class="nr-project__plate is-<?php echo esc_attr( $p['orient'] ); ?>" data-plate="<?php echo (int) $i; ?>">
							<?php if ( $p['id'] ) :
								echo wp_get_attachment_image( $p['id'], 'nr-hero', false, [
									'alt'      => $alt,
									'sizes'    => '(max-width:900px) 100vw, 70vw',
									'loading'  => $i === 0 ? 'eager' : 'lazy',
									'decoding' => 'async',
								] );
							else : ?>
								<img src="<?php echo esc_url( $p['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" width="<?php echo (int) $p['w']; ?>" height="<?php echo (int) $p['h']; ?>" loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" decoding="async">
							<?php endif; ?>
						<?php if ( $exif_str ) : ?><figcaption class="nr-plate-cap"><?php echo esc_html( $exif_str ); ?></figcaption><?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
