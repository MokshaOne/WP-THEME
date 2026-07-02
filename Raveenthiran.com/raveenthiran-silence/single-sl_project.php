<?php
/**
 * Single project — the horizontal plate viewer. Wheel, drag, swipe,
 * or arrow keys move through the gallery; a quiet counter keeps place.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$m      = sl_project_meta();
	$plates = sl_project_gallery( get_the_ID() );
	$total  = max( 1, count( $plates ) );

	// prev / next within the collection (menu_order flow, matches the Index)
	$all  = get_posts( [ 'post_type' => 'sl_project', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
	$pos  = array_search( get_the_ID(), $all, true );
	$prev = ( $pos !== false && $pos > 0 ) ? $all[ $pos - 1 ] : null;
	$next = ( $pos !== false && $pos < count( $all ) - 1 ) ? $all[ $pos + 1 ] : null;
?>

<article class="sl-project" data-rail-page>
	<div class="sl-rail" data-rail tabindex="0" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<?php if ( $plates ) : foreach ( $plates as $i => $id ) : ?>
			<figure class="sl-rail__plate" data-plate-n="<?php echo (int) ( $i + 1 ); ?>">
				<?php echo wp_get_attachment_image( $id, 'sl-plate', false, [
					'loading'  => $i < 2 ? 'eager' : 'lazy',
					'decoding' => 'async',
					'sizes'    => '(max-width:900px) 96vw, 80vh',
				] ); ?>
				<?php $cap = wp_get_attachment_caption( $id ); if ( $cap ) : ?>
					<figcaption><?php echo esc_html( $cap ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; else : ?>
			<figure class="sl-rail__plate"><?php echo sl_placeholder( get_the_title() ); ?></figure>
		<?php endif; ?>

		<?php if ( get_the_content() || $next || $prev ) : ?>
			<aside class="sl-rail__end">
				<?php if ( get_the_content() ) : ?>
					<div class="sl-rail__text"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<a class="sl-rail__next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
						<span class="sl-eyebrow"><?php esc_html_e( 'Next', 'raveenthiran-silence' ); ?></span>
						<span><?php echo esc_html( get_the_title( $next ) ); ?> →</span>
					</a>
				<?php elseif ( $prev ) : ?>
					<a class="sl-rail__next" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
						<span class="sl-eyebrow"><?php esc_html_e( 'Previous', 'raveenthiran-silence' ); ?></span>
						<span>← <?php echo esc_html( get_the_title( $prev ) ); ?></span>
					</a>
				<?php endif; ?>
			</aside>
		<?php endif; ?>
	</div>

	<footer class="sl-project__foot sl-ui">
		<div class="sl-project__id">
			<a class="sl-project__back" href="<?php echo esc_url( get_post_type_archive_link( 'sl_project' ) ); ?>">← <?php esc_html_e( 'Index', 'raveenthiran-silence' ); ?></a>
			<h1 class="sl-project__title"><?php the_title(); ?></h1>
			<span class="sl-project__meta"><?php echo esc_html( trim( implode( ' · ', array_filter( [ $m['cat'], $m['client'], $m['yr'], $m['loc'] ] ) ), ' ·' ) ); ?></span>
		</div>
		<span class="sl-project__count"><b data-rail-i>01</b> — <?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
	</footer>
</article>

<?php endwhile; get_footer(); ?>
