<?php
/**
 * Generic page template — used when no specialised template matches.
 * Legal pages (Impressum, AGB, Datenschutz) use this template.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="nr-page nr-page-generic">
	<?php while ( have_posts() ) : the_post(); ?>
		<span class="nr-eyebrow"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? '' ); ?></span>
		<h1><?php the_title(); ?></h1>
		<div class="nr-prose"><?php the_content(); ?></div>
	<?php endwhile; ?>
</section>
<?php get_footer(); ?>
