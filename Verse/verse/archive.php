<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="kv-main">
  <header class="kv-page-hero">
    <p class="kv-eyebrow">— <?php the_archive_title(); ?></p>
    <h1 class="kv-page-head"><?php single_post_title(); ?></h1>
  </header>
  <section class="kv-archive-grid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <a class="kv-archive-card" href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ) : ?><div class="kv-archive-media"><?php the_post_thumbnail( 'VERSE-card' ); ?></div><?php endif; ?>
        <h3><?php the_title(); ?></h3>
        <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
      </a>
    <?php endwhile; endif; ?>
  </section>
</main>
<?php get_footer();
