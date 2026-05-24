<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gw-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="gw-post">
      <header class="gw-page-hero">
        <p class="gw-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="gw-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="gw-hero-image"><?php the_post_thumbnail( 'BAUHAUS-hero' ); ?></figure>
      <?php endif; ?>
      <div class="gw-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
