<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="kv-main">
  <header class="kv-page-hero">
    <p class="kv-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'VERSE' ) : __( 'Journal', 'VERSE' ) ); ?></p>
    <h1 class="kv-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'VERSE' ), get_search_query() ) : __( 'Recent posts', 'VERSE' ) ); ?></h1>
  </header>
  <section class="kv-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="kv-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="kv-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="kv-empty"><?php esc_html_e( 'Nothing here yet.', 'VERSE' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
