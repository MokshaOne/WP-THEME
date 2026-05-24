<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mo-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="mo-post">
      <header class="mo-page-hero">
        <p class="mo-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="mo-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="mo-hero-image"><?php the_post_thumbnail( 'PHOSPHOR-hero' ); ?></figure>
      <?php endif; ?>
      <div class="mo-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
