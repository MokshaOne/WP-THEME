<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="dk-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="dk-page">
      <header class="dk-page-hero">
        <p class="dk-eyebrow">— <?php esc_html_e( 'Page', 'ORBIT' ); ?></p>
        <h1 class="dk-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="dk-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
