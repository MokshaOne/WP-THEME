<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gw-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gw-page">
      <header class="gw-page-hero">
        <p class="gw-eyebrow">— <?php esc_html_e( 'Page', 'BAUHAUS' ); ?></p>
        <h1 class="gw-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="gw-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
