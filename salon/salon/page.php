<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rv-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="rv-page">
      <header class="rv-page-hero">
        <p class="rv-eyebrow">— <?php esc_html_e( 'Page', 'SALON' ); ?></p>
        <h1 class="rv-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="rv-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
