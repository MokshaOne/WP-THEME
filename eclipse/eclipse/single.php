<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="px-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="px-post">
      <header class="px-page-hero">
        <p class="px-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="px-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="px-hero-image"><?php the_post_thumbnail( 'ECLIPSE-hero' ); ?></figure>
      <?php endif; ?>
      <div class="px-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
