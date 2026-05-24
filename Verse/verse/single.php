<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="kv-main">
  <?php while ( have_posts() ) : the_post(); ?>
    <article class="kv-post">
      <header class="kv-page-hero">
        <p class="kv-eyebrow">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></p>
        <h1 class="kv-page-head"><?php the_title(); ?></h1>
      </header>
      <?php if ( has_post_thumbnail() ) : ?>
        <figure class="kv-hero-image"><?php the_post_thumbnail( 'VERSE-hero' ); ?></figure>
      <?php endif; ?>
      <div class="kv-content"><?php the_content(); ?></div>
    </article>
    <?php if ( comments_open() || get_comments_number() ) comments_template(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer();
