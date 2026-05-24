<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gw-main">
  <header class="gw-page-hero">
    <p class="gw-eyebrow">— <?php the_archive_title(); ?></p>
    <h1 class="gw-page-head"><?php single_post_title(); ?></h1>
    <?php the_archive_description( '<p class="' . esc_attr( 'gw-page-lede' ) . '">', '</p>' ); ?>
  </header>
  <section class="gw-archive-grid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <a class="gw-archive-card" href="<?php the_permalink(); ?>">
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="gw-archive-media"><?php the_post_thumbnail( 'BAUHAUS-card' ); ?></div>
        <?php endif; ?>
        <h3><?php the_title(); ?></h3>
        <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
      </a>
    <?php endwhile; endif; ?>
  </section>
</main>
<?php get_footer();
