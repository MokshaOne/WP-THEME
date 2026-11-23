<?php
/**
 * Generic page template — used when no specialised template matches.
 * Legal pages (Impressum, AGB, Datenschutz) may use this or their own.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="st-page st-static">
	<div class="st-wrap st-static__in">
		<?php while ( have_posts() ) : the_post(); ?>
			<span class="st-eyebrow"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? '' ); ?></span>
			<h1 class="st-static__title"><?php the_title(); ?></h1>
			<div class="st-prose"><?php the_content(); ?></div>
		<?php endwhile; ?>
	</div>
</section>
<?php get_footer(); ?>
