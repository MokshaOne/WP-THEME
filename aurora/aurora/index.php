<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mx-main">
  <header class="mx-page-hero">
    <p class="mx-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'AURORA' ) : __( 'Journal', 'AURORA' ) ); ?></p>
    <h1 class="mx-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'AURORA' ), get_search_query() ) : __( 'Recent posts', 'AURORA' ) ); ?></h1>
  </header>
  <section class="mx-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="mx-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="mx-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="mx-empty"><?php esc_html_e( 'Nothing here yet.', 'AURORA' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
