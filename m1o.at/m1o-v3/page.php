<?php
/**
 * M1O Transmission · default page (Impressum, Datenschutz, …)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="main" class="page-shell">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<div class="eyebrow">// M1O.AT</div>
			<h1><?php the_title(); ?><em>.</em></h1>
			<div class="prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
