<?php
/**
 * Default page — measured reading column.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
?>

<article class="sl-sheet">
	<div class="sl-sheet__inner sl-prose">
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
