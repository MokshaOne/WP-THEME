<?php
/**
 * Fallback — used by archives that don't have their own template.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="nr-page nr-fullscreen nr-page--generic">
	<div class="nr-page__container">
		<?php if ( have_posts() ) : ?>
			<span class="nr-eyebrow"><?php
				if ( is_archive() ) post_type_archive_title();
				elseif ( is_search() ) printf( esc_html__( 'Search · %s', 'raveenthiran' ), esc_html( get_search_query() ) );
				else esc_html_e( 'Latest', 'raveenthiran' );
			?></span>
			<h1 class="nr-display nr-display--lg"><?php is_archive() ? post_type_archive_title( '', false ) : esc_html_e( 'Index', 'raveenthiran' ); ?></h1>

			<ul class="nr-index">
				<?php while ( have_posts() ) : the_post(); ?>
					<li>
						<a href="<?php the_permalink(); ?>">
							<span><?php the_title(); ?></span>
							<span class="nr-index__date"><?php echo esc_html( get_the_date() ); ?></span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<h1 class="nr-display nr-display--lg"><?php esc_html_e( 'Nothing here yet.', 'raveenthiran' ); ?></h1>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
