<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="px-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="px-page">
      <header class="px-page-hero">
        <p class="px-eyebrow">— <?php esc_html_e( 'Page', 'ECLIPSE' ); ?></p>
        <h1 class="px-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="px-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
