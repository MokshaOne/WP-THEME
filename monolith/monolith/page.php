<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gz-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gz-page">
      <header class="gz-page-hero">
        <p class="gz-eyebrow">— <?php esc_html_e( 'Page', 'MONOLITH' ); ?></p>
        <h1 class="gz-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="gz-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
