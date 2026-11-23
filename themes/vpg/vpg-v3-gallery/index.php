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
      <?php // 0311 · years as facet chips (syear is honoured by the faceted search)
      $vpg_years = array_slice( array_unique( array_map( fn( $y ) => $y->year, (array) $GLOBALS['wpdb']->get_results( "SELECT DISTINCT YEAR(post_date) AS year FROM {$GLOBALS['wpdb']->posts} WHERE post_status='publish' AND post_type='post' ORDER BY year DESC" ) ) ), 0, 8 );
      $vpg_cur_y = (int) ( $_GET['syear'] ?? 0 );
      if ( count( $vpg_years ) > 1 ) : ?>
      <div class="vpg-map-filter" style="margin-bottom:14px">
        <span class="vpg-map-filter__label">— <?php esc_html_e( 'Year', 'vpg-v2' ); ?></span>
        <a href="<?php echo esc_url( remove_query_arg( 'syear' ) ); ?>" class="<?php if ( ! $vpg_cur_y ) echo 'is-active'; ?>"><button type="button"><?php esc_html_e( 'All', 'vpg-v2' ); ?></button></a>
        <?php foreach ( $vpg_years as $y ) : ?>
          <a href="<?php echo esc_url( add_query_arg( 'syear', (int) $y ) ); ?>" class="<?php if ( $vpg_cur_y === (int) $y ) echo 'is-active'; ?>"><button type="button"><?php echo (int) $y; ?></button></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <p style="text-align:right;margin:0 0 14px"><a class="g-link" style="font-size:12px" href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_random_frame' ) ); ?>">⚄ <?php esc_html_e( 'Random frame', 'vpg-v2' ); ?></a></p>
      <?php if ( is_search() && is_user_logged_in() && get_search_query() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0 0 14px;text-align:right">
        <?php wp_nonce_field( 'vpg_save_search' ); ?>
        <input type="hidden" name="action" value="vpg_save_search">
        <input type="hidden" name="term" value="<?php echo esc_attr( get_search_query() ); ?>">
        <button type="submit" style="background:none;border:1px solid var(--g-line-2);padding:7px 12px;cursor:pointer;font:700 12px/1 var(--g-sans);letter-spacing:.06em">☆ <?php esc_html_e( 'Watch this search', 'vpg-v2' ); ?></button>
      </form>
      <?php endif; ?>
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
                <?php if ( is_search() ) : // 0581 · hover expands the preview to the full first passage ?>
                  <p class="g-row__lede g-row__lede--peek"><?php echo esc_html( wp_trim_words( get_the_excerpt() ?: get_the_content(), 70 ) ); ?></p>
                <?php else : ?>
                  <p class="g-row__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
                <?php endif; ?>
                <div class="g-byline"><span><?php echo esc_html( get_the_author() ); ?></span><span>·</span><span><?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( get_the_content() ) . ' ' . __( 'min', 'vpg-v2' ) : get_the_date() ); ?></span></div>
              </div>
              <span class="g-row__when"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
            </a>
          <?php endwhile; ?>
        </div>
        <?php // 0317 · quiet weekly chart — three pieces, no like circus
        $seen = ! is_search() && function_exists( 'vpg_most_seen_week' ) ? vpg_most_seen_week( 3 ) : [];
        if ( count( $seen ) >= 2 ) : ?>
        <div style="margin-top:40px;border-top:2px solid var(--g-ink);padding-top:18px">
          <span class="g-kicker"><?php esc_html_e( 'Most seen this week', 'vpg-v2' ); ?></span>
          <div style="display:flex;gap:28px;flex-wrap:wrap;margin-top:12px">
            <?php $seen_i = 1; foreach ( $seen as $sid => $n ) : ?>
              <a href="<?php echo esc_url( get_permalink( $sid ) ); ?>" style="font-weight:700;font-size:14px"><span style="color:var(--g-red);font-weight:900"><?php echo (int) $seen_i++; ?></span> <?php echo esc_html( get_the_title( $sid ) ); ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

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
