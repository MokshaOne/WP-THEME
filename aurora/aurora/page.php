<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mx-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="mx-page">
      <header class="mx-page-hero">
        <p class="mx-eyebrow">— <?php esc_html_e( 'Page', 'AURORA' ); ?></p>
        <h1 class="mx-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="mx-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
