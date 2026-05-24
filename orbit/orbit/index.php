<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="dk-main">
  <header class="dk-page-hero">
    <p class="dk-eyebrow">— <?php echo esc_html( is_search() ? __( 'Search results', 'ORBIT' ) : __( 'Journal', 'ORBIT' ) ); ?></p>
    <h1 class="dk-page-head"><?php echo esc_html( is_search() ? sprintf( __( 'Results for "%s"', 'ORBIT' ), get_search_query() ) : __( 'Recent posts', 'ORBIT' ) ); ?></h1>
  </header>
  <section class="dk-posts">
    <?php if ( have_posts() ) : ?>
      <ul class="dk-post-list">
        <?php while ( have_posts() ) : the_post(); ?>
          <li><a href="<?php the_permalink(); ?>"><span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span><strong><?php the_title(); ?></strong></a></li>
        <?php endwhile; ?>
      </ul>
      <div class="dk-pagination"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
    <?php else : ?>
      <p class="dk-empty"><?php esc_html_e( 'Nothing here yet.', 'ORBIT' ); ?></p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer();
