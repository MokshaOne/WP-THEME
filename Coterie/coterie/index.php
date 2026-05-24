<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="vp-main">
  <header class="vp-page-hero">
    <p class="vp-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'COTERIE' ) : __( 'Journal', 'COTERIE' ) ); ?></p>
    <h1 class="vp-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'COTERIE' ), get_search_query() ) : __( 'Recent posts', 'COTERIE' ) ); ?></h1>
  </header>
  <section class="vp-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="vp-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="vp-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="vp-empty"><?php esc_html_e( 'Nothing here yet.', 'COTERIE' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
