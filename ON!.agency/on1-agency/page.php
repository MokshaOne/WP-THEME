<?php
/**
 * page.php — Default page template.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) : the_post(); ?>
  <section class="page-shell">
    <h1><?php the_title(); ?></h1>
    <div class="prose">
      <?php the_content(); ?>
    </div>
  </section>
<?php endwhile;
get_footer();
