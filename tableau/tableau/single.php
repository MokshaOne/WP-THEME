<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rt-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="rt-post">
      <header class="rt-page-hero">
        <p class="rt-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="rt-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="rt-hero-image"><?php the_post_thumbnail( 'TABLEAU-hero' ); ?></figure>
      <?php endif; ?>
      <div class="rt-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
