<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gd-main">
  <header class="gd-page-hero">
    <p class="gd-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'REVOLT' ) : __( 'Journal', 'REVOLT' ) ); ?></p>
    <h1 class="gd-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'REVOLT' ), get_search_query() ) : __( 'Recent posts', 'REVOLT' ) ); ?></h1>
  </header>
  <section class="gd-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="gd-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="gd-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="gd-empty"><?php esc_html_e( 'Nothing here yet.', 'REVOLT' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
