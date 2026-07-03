<?php
/**
 * Single project — the plate rail, Silence II. Horizontal viewer with a
 * giant ghost counter behind the plates, a scroll progress hairline,
 * plate parallax, and a serif title block. Wheel, drag, swipe, keys.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$m      = nr_project_meta();
	$plates = nr_project_gallery( get_the_ID() );
	$total  = max( 1, count( $plates ) );

	// prev / next within the collection (menu_order flow, matches the Index)
	$all  = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
	$pos  = array_search( get_the_ID(), $all, true );
	$prev = ( $pos !== false && $pos > 0 ) ? $all[ $pos - 1 ] : null;
	$next = ( $pos !== false && $pos < count( $all ) - 1 ) ? $all[ $pos + 1 ] : null;
?>

<article class="nr-project" data-rail-page>

	<span class="nr-ghost nr-ghost--rail nr-ui" data-rail-ghost aria-hidden="true">01</span>

	<div class="nr-rail" data-rail tabindex="0" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<?php if ( $plates ) : foreach ( $plates as $i => $id ) : ?>
			<figure class="nr-rail__plate" data-plate-n="<?php echo (int) ( $i + 1 ); ?>">
				<?php if ( wp_attachment_is( 'video', $id ) ) :
					// video plates (Obscura galleries support these) — muted loop, poster from its featured image
					$poster = get_the_post_thumbnail_url( $id, 'nr-plate' );
					printf(
						'<video src="%s"%s muted loop autoplay playsinline preload="metadata"></video>',
						esc_url( wp_get_attachment_url( $id ) ),
						$poster ? ' poster="' . esc_url( $poster ) . '"' : ''
					);
				else :
					echo wp_get_attachment_image( $id, 'nr-plate', false, [
						'loading'  => $i < 2 ? 'eager' : 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width:900px) 96vw, 80vh',
					] );
				endif; ?>
				<?php $cap = wp_get_attachment_caption( $id ); if ( $cap ) : ?>
					<figcaption><?php echo esc_html( $cap ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; else : ?>
			<figure class="nr-rail__plate"><?php echo nr_placeholder( get_the_title() ); ?></figure>
		<?php endif; ?>

		<?php if ( get_the_content() || $next || $prev ) : ?>
			<aside class="nr-rail__end">
				<?php if ( get_the_content() ) : ?>
					<div class="nr-rail__text nr-prose"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<a class="nr-rail__next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
						<span class="nr-eyebrow"><?php esc_html_e( 'Next', 'raveenthiran-silence' ); ?></span>
						<span class="nr-rail__next-t"><?php echo esc_html( get_the_title( $next ) ); ?> →</span>
					</a>
				<?php elseif ( $prev ) : ?>
					<a class="nr-rail__next" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
						<span class="nr-eyebrow"><?php esc_html_e( 'Previous', 'raveenthiran-silence' ); ?></span>
						<span class="nr-rail__next-t">← <?php echo esc_html( get_the_title( $prev ) ); ?></span>
					</a>
				<?php endif; ?>
			</aside>
		<?php endif; ?>
	</div>

	<footer class="nr-project__foot nr-ui">
		<div class="nr-project__id">
			<a class="nr-project__back" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>">← <?php esc_html_e( 'Index', 'raveenthiran-silence' ); ?></a>
			<h1 class="nr-project__title"><?php the_title(); ?></h1>
			<span class="nr-project__meta">
				<?php if ( $m['client'] ) : ?><em><?php esc_html_e( 'for', 'raveenthiran-silence' ); ?> <?php echo esc_html( $m['client'] ); ?></em> · <?php endif; ?>
				<?php echo esc_html( trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'], $m['loc'] ] ) ), ' ·' ) ); ?>
			</span>
		</div>
		<span class="nr-project__count"><b data-rail-i>01</b><em>/<?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></em></span>
	</footer>

	<span class="nr-rail__bar" aria-hidden="true"><i data-rail-bar></i></span>
</article>

<?php endwhile; get_footer(); ?>
