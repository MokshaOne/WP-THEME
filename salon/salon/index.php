<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rv-main">
  <header class="rv-page-hero">
    <p class="rv-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'SALON' ) : __( 'Journal', 'SALON' ) ); ?></p>
    <h1 class="rv-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'SALON' ), get_search_query() ) : __( 'Recent posts', 'SALON' ) ); ?></h1>
  </header>
  <section class="rv-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="rv-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="rv-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="rv-empty"><?php esc_html_e( 'Nothing here yet.', 'SALON' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
