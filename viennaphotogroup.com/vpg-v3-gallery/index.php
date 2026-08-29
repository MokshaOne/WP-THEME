<?php
/** VPG v3 · index.php · journal index / search results / fallback (Gallery). */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$is_search = is_search();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php echo $is_search ? esc_html__( 'Search', 'vpg-v2' ) : esc_html__( 'The Journal', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php
            if ( $is_search ) {
              printf( wp_kses_post( __( 'Results for <em>%s</em>.', 'vpg-v2' ) ), esc_html( get_search_query() ) );
            } else {
              echo wp_kses_post( __( 'Writing, free to <em>read</em>.', 'vpg-v2' ) );
            }
          ?></h1>
          <?php if ( ! $is_search ) : ?>
            <p class="g-lede g-phero__lede"><?php esc_html_e( 'Essays, field notes, portfolios and gear writing — published by members. The slow, finished pieces go to the magazine; this is where the conversation lives.', 'vpg-v2' ); ?></p>
          <?php endif; ?>
        </div>
        <div class="g-phero__aside" style="align-content:end">
          <?php get_search_form(); ?>
        </div>
      </div>
    </div>
  </section>

  <?php if ( $is_search ) :
      $s_current = sanitize_key( $_GET['stype'] ?? '' );
      $s_types = [
          ''             => __( 'All', 'vpg-v2' ),
          'post'         => __( 'Journal', 'vpg-v2' ),
          'vpg_location' => __( 'Locations', 'vpg-v2' ),
          'vpg_studio'   => __( 'Studios', 'vpg-v2' ),
          'vpg_shop'     => __( 'Shops', 'vpg-v2' ),
          'vpg_review'   => __( 'Reviews', 'vpg-v2' ),
          'vpg_tutorial' => __( 'Tutorials', 'vpg-v2' ),
          'vpg_event'    => __( 'Events', 'vpg-v2' ),
      ];
  ?>
  <!-- Search facets · type chips (year/district also honoured via syear/sdistrict) -->
  <section class="g-section--tight" style="padding-top:0">
    <div class="g-wrap">
      <div class="vpg-map-filter" role="toolbar" aria-label="<?php esc_attr_e( 'Filter results by type', 'vpg-v2' ); ?>">
        <span class="vpg-map-filter__label">— <?php esc_html_e( 'Type', 'vpg-v2' ); ?></span>
        <?php foreach ( $s_types as $t => $label ) :
            $url = add_query_arg( array_filter( [ 's' => get_search_query(), 'stype' => $t ] ), home_url( '/' ) );
        ?>
          <a href="<?php echo esc_url( $url ); ?>" class="<?php if ( $s_current === $t ) echo 'is-active'; ?>"><button type="button"><?php echo esc_html( $label ); ?></button></a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="g-section">
    <div class="g-wrap">
      <p style="text-align:right;margin:0 0 14px"><a class="g-link" style="font-size:12px" href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_random_frame' ) ); ?>">⚄ <?php esc_html_e( 'Random frame', 'vpg-v2' ); ?></a></p>
      <?php if ( have_posts() ) : ?>
        <div class="g-list">
          <?php while ( have_posts() ) : the_post(); ?>
            <?php if ( get_post_format() === 'aside' ) : // 0266 · a note — no image, no excerpt theatre ?>
            <a class="g-row" href="<?php the_permalink(); ?>" style="grid-template-columns:1fr auto;border-left:3px solid var(--g-red);padding-left:18px">
              <div>
                <span class="g-cat"><?php esc_html_e( 'Note', 'vpg-v2' ); ?></span>
                <h3 class="g-row__title" style="font-size:19px"><?php the_title(); ?></h3>
                <div class="g-byline"><span><?php echo esc_html( get_the_author() ); ?></span></div>
              </div>
              <span class="g-row__when"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
            </a>
            <?php continue; endif; ?>
            <a class="g-row" href="<?php the_permalink(); ?>">
              <div class="g-fig"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium_large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <div>
                <span class="g-cat"><?php
                  $cat = get_the_category();
                  echo esc_html( $cat ? $cat[0]->name : get_post_type_object( get_post_type() )->labels->singular_name );
                ?></span>
                <h3 class="g-row__title"><?php the_title(); ?></h3>
                <p class="g-row__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
                <div class="g-byline"><span><?php echo esc_html( get_the_author() ); ?></span><span>·</span><span><?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( get_the_content() ) . ' ' . __( 'min', 'vpg-v2' ) : get_the_date() ); ?></span></div>
              </div>
              <span class="g-row__when"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
            </a>
          <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
          <?php the_posts_pagination( [ 'mid_size' => 2, 'prev_text' => __( '← Newer', 'vpg-v2' ), 'next_text' => __( 'Older →', 'vpg-v2' ) ] ); ?>
        </div>
      <?php else : ?>
        <p class="g-lede"><?php esc_html_e( 'Nothing here yet.', 'vpg-v2' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

</main>
<?php get_footer();
