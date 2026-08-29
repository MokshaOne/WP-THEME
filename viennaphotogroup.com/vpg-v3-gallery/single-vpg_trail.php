<?php
/** VPG v3 · single · Photowalk trail — numbered stops on one map. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $stops = function_exists( 'vpg_trail_stops' ) ? vpg_trail_stops( get_the_ID() ) : [];
    $pins  = [];
    foreach ( $stops as $i => $s ) {
        if ( ! $s['coords'] ) continue;
        $pins[] = [
            'id'    => (int) $s['post']->ID,
            'lat'   => $s['coords'][0],
            'lng'   => $s['coords'][1],
            'title' => sprintf( '%02d · %s', $i + 1, get_the_title( $s['post'] ) ),
            'url'   => get_permalink( $s['post'] ),
            'lede'  => wp_trim_words( get_the_excerpt( $s['post'] ), 14 ),
            'type'  => 'location',
        ];
    }
?>

    <section class="g-phero">
      <div class="g-wrap">
        <div class="g-phero__grid">
          <div>
            <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Photowalk trail', 'vpg-v2' ); ?></p>
            <h1 class="g-display g-phero__title"><?php the_title(); ?></h1>
            <?php if ( get_the_excerpt() ) : ?><p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
          </div>
          <dl class="g-phero__aside">
            <dt><?php esc_html_e( 'Stops', 'vpg-v2' ); ?></dt><dd><?php echo count( $stops ); ?></dd>
            <dt><?php esc_html_e( 'Curated by', 'vpg-v2' ); ?></dt><dd><?php the_author(); ?></dd>
          </dl>
        </div>
      </div>
    </section>

    <?php if ( get_the_content() ) : ?>
    <section class="g-section g-section--tight">
      <div class="g-wrap"><div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div></div>
    </section>
    <?php endif; ?>

    <?php if ( $pins ) : ?>
    <section class="g-section g-section--tight">
      <div class="g-wrap">
        <div id="vpg-map" class="vpg-map vpg-map--tall" data-pins="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>"></div>
      </div>
    </section>
    <?php endif; ?>

    <!-- The stops, in walking order -->
    <section class="g-section">
      <div class="g-wrap">
        <div class="g-head">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'The route', 'vpg-v2' ); ?></span>
            <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Stop by <em>stop</em>.', 'vpg-v2' ) ); ?></h2>
          </div>
        </div>
        <?php if ( $stops ) : ?>
        <div class="g-list">
          <?php foreach ( $stops as $i => $s ) :
              $best = get_post_meta( $s['post']->ID, 'location_best_time', true );
          ?>
          <a class="g-row" style="grid-template-columns:60px 1fr auto" href="<?php echo esc_url( get_permalink( $s['post'] ) ); ?>#pin-<?php echo (int) $s['post']->ID; ?>">
            <span style="font-weight:900;font-stretch:125%;font-size:26px;color:var(--g-red)"><?php printf( '%02d', $i + 1 ); ?></span>
            <div>
              <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $s['post'] ) ); ?></h3>
              <div class="g-byline"><span><?php echo esc_html( wp_trim_words( get_the_excerpt( $s['post'] ), 16 ) ); ?></span></div>
            </div>
            <span class="g-row__when"><?php echo $best ? esc_html( $best ) : ''; ?></span>
          </a>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
          <p class="g-lede" style="color:var(--g-mid)"><?php esc_html_e( 'No stops pinned yet.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <section class="g-section g-section--dark" style="text-align:center">
      <div class="g-wrap">
        <a class="g-btn g-btn--red g-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_trail' ) ); ?>"><?php esc_html_e( 'All trails', 'vpg-v2' ); ?> <span class="a">&rarr;</span></a>
      </div>
    </section>

<?php endwhile; ?>
</main>
<?php get_footer();
