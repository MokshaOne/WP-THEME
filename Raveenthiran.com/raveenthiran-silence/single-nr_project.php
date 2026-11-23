<?php
/**
 * Single project — Silence III. Vertical plate flow with alternating
 * alignment, a serif head with ghost numeral, scroll-progress hairline,
 * project text, and a giant next-project handoff. Supports video plates.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$m      = nr_project_meta();
	$plates = nr_project_gallery( get_the_ID() );
	$total  = max( 1, count( $plates ) );

	$all  = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => [ 'menu_order' => 'ASC', 'date' => 'DESC' ] ] );
	$pos  = array_search( get_the_ID(), $all, true );
	$next = ( $pos !== false && $pos < count( $all ) - 1 ) ? $all[ $pos + 1 ] : ( $all[0] ?? null );
	if ( $next === get_the_ID() ) $next = null;
?>

<article class="nr-project">
	<span class="nr-bar" aria-hidden="true"><i data-page-bar></i></span>

	<header class="nr-project__head">
		<span class="nr-ghost" aria-hidden="true"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
		<a class="nr-back" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>">← <?php esc_html_e( 'Index', 'raveenthiran-silence' ); ?></a>
		<h1 class="nr-project__title"><?php the_title(); ?></h1>
		<span class="nr-project__meta">
			<?php if ( $m['client'] ) : ?><em><?php esc_html_e( 'for', 'raveenthiran-silence' ); ?> <?php echo esc_html( $m['client'] ); ?></em> · <?php endif; ?>
			<?php echo esc_html( trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'], $m['loc'], sprintf( _n( '%d plate', '%d plates', $total, 'raveenthiran-silence' ), $total ) ] ) ), ' ·' ) ); ?>
		</span>
	</header>

	<div class="nr-plates">
		<?php if ( $plates ) : foreach ( $plates as $i => $id ) : ?>
			<figure class="nr-plates__item nr-rise">
				<?php if ( wp_attachment_is( 'video', $id ) ) :
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
						'sizes'    => '(max-width:900px) 100vw, 92vw',
					] );
				endif; ?>
				<?php $cap = wp_get_attachment_caption( $id ); if ( $cap ) : ?>
					<figcaption><?php echo esc_html( $cap ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; else : ?>
			<figure class="nr-plates__item"><?php echo nr_placeholder( get_the_title() ); ?></figure>
		<?php endif; ?>
	</div>

	<?php if ( get_the_content() ) : ?>
		<div class="nr-project__text nr-prose nr-rise"><?php the_content(); ?></div>
	<?php endif; ?>

	<?php if ( $next ) : ?>
		<a class="nr-project__next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
			<span class="nr-eyebrow"><?php esc_html_e( 'Next project', 'raveenthiran-silence' ); ?></span>
			<span class="nr-project__next-t"><?php echo esc_html( get_the_title( $next ) ); ?> →</span>
		</a>
	<?php endif; ?>
</article>

<?php endwhile; get_footer(); ?>
