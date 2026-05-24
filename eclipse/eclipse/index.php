<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="px-main">
  <header class="px-page-hero">
    <p class="px-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'ECLIPSE' ) : __( 'Journal', 'ECLIPSE' ) ); ?></p>
    <h1 class="px-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'ECLIPSE' ), get_search_query() ) : __( 'Recent posts', 'ECLIPSE' ) ); ?></h1>
  </header>
  <section class="px-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="px-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="px-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="px-empty"><?php esc_html_e( 'Nothing here yet.', 'ECLIPSE' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
