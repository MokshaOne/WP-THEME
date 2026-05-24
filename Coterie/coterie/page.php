<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="vp-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="vp-page">
      <header class="vp-page-hero">
        <p class="vp-eyebrow">— <?php esc_html_e( 'Page', 'COTERIE' ); ?></p>
        <h1 class="vp-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="vp-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
