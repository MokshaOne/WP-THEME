<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rt-main">
  <header class="rt-page-hero">
    <p class="rt-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'TABLEAU' ) : __( 'Journal', 'TABLEAU' ) ); ?></p>
    <h1 class="rt-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'TABLEAU' ), get_search_query() ) : __( 'Recent posts', 'TABLEAU' ) ); ?></h1>
  </header>
  <section class="rt-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="rt-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="rt-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="rt-empty"><?php esc_html_e( 'Nothing here yet.', 'TABLEAU' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
