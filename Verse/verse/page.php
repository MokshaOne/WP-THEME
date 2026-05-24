<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="kv-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="kv-page">
      <header class="kv-page-hero">
        <p class="kv-eyebrow">— <?php esc_html_e( 'Page', 'VERSE' ); ?></p>
        <h1 class="kv-page-head"><?php the_title(); ?></h1>
      </header>
      <div class="kv-content"><?php the_content(); wp_link_pages(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer();
