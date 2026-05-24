<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mx-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="mx-post">
      <header class="mx-page-hero">
        <p class="mx-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="mx-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="mx-hero-image"><?php the_post_thumbnail( 'AURORA-hero' ); ?></figure>
      <?php endif; ?>
      <div class="mx-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
