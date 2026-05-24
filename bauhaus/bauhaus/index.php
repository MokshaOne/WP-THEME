<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gw-main">
  <header class="gw-page-hero">
    <p class="gw-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'BAUHAUS' ) : __( 'Journal', 'BAUHAUS' ) ); ?></p>
    <h1 class="gw-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'BAUHAUS' ), get_search_query() ) : __( 'Recent posts', 'BAUHAUS' ) ); ?></h1>
  </header>
  <section class="gw-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="gw-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="gw-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="gw-empty"><?php esc_html_e( 'Nothing here yet.', 'BAUHAUS' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
