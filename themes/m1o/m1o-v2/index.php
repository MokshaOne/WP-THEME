<?php
/**
 * Fallback — used by archives / search that don't have their own template.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="st-page st-index">
	<div class="st-wrap">
		<?php if ( have_posts() ) : ?>
			<header class="st-arc-head__text">
				<span class="st-eyebrow"><?php
					if ( is_archive() ) post_type_archive_title();
					elseif ( is_search() ) printf( esc_html__( 'Search · %s', 'raveenthiran' ), esc_html( get_search_query() ) );
					else esc_html_e( 'Latest', 'raveenthiran' );
				?></span>
				<h1 class="st-arc-title"><?php is_archive() ? post_type_archive_title( '', false ) : esc_html_e( 'Index', 'raveenthiran' ); ?></h1>
			</header>
			<ul class="st-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><span class="st-list__t"><?php the_title(); ?></span><span class="st-list__d"><?php echo esc_html( get_the_date() ); ?></span></a></li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<h1 class="st-arc-title"><?php esc_html_e( 'Nothing here yet', 'raveenthiran' ); ?></h1>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
