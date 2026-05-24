<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gz-main">
  <header class="gz-page-hero">
    <p class="gz-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'MONOLITH' ) : __( 'Journal', 'MONOLITH' ) ); ?></p>
    <h1 class="gz-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'MONOLITH' ), get_search_query() ) : __( 'Recent posts', 'MONOLITH' ) ); ?></h1>
  </header>
  <section class="gz-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="gz-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="gz-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="gz-empty"><?php esc_html_e( 'Nothing here yet.', 'MONOLITH' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
