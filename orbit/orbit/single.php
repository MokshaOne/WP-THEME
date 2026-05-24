<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="dk-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="dk-post">
      <header class="dk-page-hero">
        <p class="dk-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="dk-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="dk-hero-image"><?php the_post_thumbnail( 'ORBIT-hero' ); ?></figure>
      <?php endif; ?>
      <div class="dk-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
