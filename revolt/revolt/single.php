<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gd-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gd-post">
      <header class="gd-page-hero">
        <p class="gd-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="gd-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="gd-hero-image"><?php the_post_thumbnail( 'REVOLT-hero' ); ?></figure>
      <?php endif; ?>
      <div class="gd-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
