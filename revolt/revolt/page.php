<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gd-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gd-page">
      <header class="gd-page-hero">
        <p class="gd-eyebrow">— <?php esc_html_e( 'Page', 'REVOLT' ); ?></p>
        <h1 class="gd-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="gd-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
