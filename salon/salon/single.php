<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rv-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="rv-post">
      <header class="rv-page-hero">
        <p class="rv-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="rv-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="rv-hero-image"><?php the_post_thumbnail( 'SALON-hero' ); ?></figure>
      <?php endif; ?>
      <div class="rv-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
