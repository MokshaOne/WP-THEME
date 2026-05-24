<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mo-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="mo-page">
      <header class="mo-page-hero">
        <p class="mo-eyebrow">— <?php esc_html_e( 'Page', 'PHOSPHOR' ); ?></p>
        <h1 class="mo-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="mo-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
