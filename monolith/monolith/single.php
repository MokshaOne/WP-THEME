<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gz-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gz-post">
      <header class="gz-page-hero">
        <p class="gz-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="gz-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="gz-hero-image"><?php the_post_thumbnail( 'MONOLITH-hero' ); ?></figure>
      <?php endif; ?>
      <div class="gz-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
