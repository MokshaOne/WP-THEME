<?php
/**
 * Generic page — used for Studio (About), Contact, Enquire and any other page.
 * Minimal: title + content, styled as prose. Build these pages in the editor.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
<?php while ( have_posts() ) : the_post(); ?>
	<article>
		<header class="page-head">
			<span class="label"><?php echo esc_html( get_the_title() ); ?></span>
			<h1 class="page-title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-hero"><?php the_post_thumbnail( 'still-hero' ); ?></div>
		<?php endif; ?>

		<div class="prose"><?php the_content(); ?></div>
	</article>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
