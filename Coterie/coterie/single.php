<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="vp-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="vp-post">
      <header class="vp-page-hero">
        <p class="vp-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="vp-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="vp-hero-image"><?php the_post_thumbnail( 'COTERIE-hero' ); ?></figure>
      <?php endif; ?>
      <div class="vp-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
