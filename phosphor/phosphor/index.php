<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mo-main">
  <header class="mo-page-hero">
    <p class="mo-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'PHOSPHOR' ) : __( 'Journal', 'PHOSPHOR' ) ); ?></p>
    <h1 class="mo-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'PHOSPHOR' ), get_search_query() ) : __( 'Recent posts', 'PHOSPHOR' ) ); ?></h1>
  </header>
  <section class="mo-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="mo-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="mo-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="mo-empty"><?php esc_html_e( 'Nothing here yet.', 'PHOSPHOR' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
