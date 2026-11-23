<?php
/** VPG v3 · single · Location (Gallery). */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = 'location_district' ? vpg_field( 'location_district' ) : '';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px"><?php esc_html_e( 'Location', 'vpg-v2' ); ?><?php if ( $meta ) echo ' · ' . esc_html( $meta ); ?></p>
        <h1 class="g-display g-phero__title" style="max-width:20ch"><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?><p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
      </div>
    </section>

    <?php if ( has_post_thumbnail() ) : ?>
      <figure class="g-wrap" style="margin:clamp(24px,4vw,48px) auto">
        <div class="g-fig g-fig--3x2"><?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
      </figure>
    <?php endif; ?>

    <section class="g-section">
      <div class="g-wrap">
        <div class="g-twocol">
          <div class="g-prose"><?php the_content(); ?></div>
          <aside>
            <dl class="g-phero__aside">
              <dt><?php esc_html_e( 'District', 'vpg-v2' ); ?></dt>
              <dd><?php echo $meta ? esc_html( $meta ) : '—'; ?></dd>
              <?php
              // Today's light at this exact spot · computed locally, no API.
              $lat = (float) get_post_meta( get_the_ID(), 'location_lat', true );
              $lng = (float) get_post_meta( get_the_ID(), 'location_lng', true );
              if ( $lat && $lng ) :
                  $sun = date_sun_info( current_time( 'timestamp' ), $lat, $lng );
                  if ( ! empty( $sun['sunrise'] ) && ! empty( $sun['sunset'] ) ) :
                      $tz      = wp_timezone();
                      $sunrise = wp_date( 'H:i', $sun['sunrise'], $tz );
                      $sunset  = wp_date( 'H:i', $sun['sunset'],  $tz );
                      $gh_eve  = wp_date( 'H:i', $sun['sunset'] - HOUR_IN_SECONDS, $tz );
                      $gh_morn = wp_date( 'H:i', $sun['sunrise'] + HOUR_IN_SECONDS, $tz );
              ?>
              <dt><?php esc_html_e( 'Light today', 'vpg-v2' ); ?></dt>
              <dd><?php printf( esc_html__( '↑ %1$s · ↓ %2$s', 'vpg-v2' ), esc_html( $sunrise ), esc_html( $sunset ) ); ?></dd>
              <dt><?php esc_html_e( 'Golden hour', 'vpg-v2' ); ?></dt>
              <dd><?php printf( esc_html__( '%1$s–%2$s · %3$s–%4$s', 'vpg-v2' ), esc_html( $sunrise ), esc_html( $gh_morn ), esc_html( $gh_eve ), esc_html( $sunset ) ); ?></dd>
              <?php
                  $wx = function_exists( 'vpg_weather' ) ? vpg_weather( $lat, $lng ) : null;
                  if ( $wx ) :
              ?>
              <dt><?php esc_html_e( 'Right now', 'vpg-v2' ); ?></dt>
              <dd><?php echo esc_html( trim( $wx['temp'] . ' · ' . $wx['label'], ' ·' ) ); ?></dd>
              <?php endif; ?>
              <?php endif; endif; ?>
              <?php $best = get_post_meta( get_the_ID(), 'location_best_time', true ); if ( $best ) : ?>
              <dt><?php esc_html_e( 'Best time', 'vpg-v2' ); ?></dt>
              <dd><?php echo esc_html( $best ); ?></dd>
              <?php endif; ?>
            </dl>
            <a class="g-link" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>" style="margin-top:18px"><?php esc_html_e( 'All locations', 'vpg-v2' ); ?> <span class="a">→</span></a>
          </aside>
        </div>
      </div>
    </section>

        <?php
        $coords = vpg_get_coords( get_the_ID() );
        if ( $coords ) :
            $pin = [ [ 'lat' => $coords[0], 'lng' => $coords[1], 'title' => get_the_title(), 'url' => '', 'lede' => '' ] ];
        ?>
            <section class="g-section g-section--tight">
                <div class="g-wrap">
                    <div class="g-head"><div><span class="g-kicker"><?php esc_html_e( 'Where', 'vpg-v2' ); ?></span><h2 class="g-head__t"><?php esc_html_e( 'On the', 'vpg-v2' ); ?> <em><?php esc_html_e( 'map', 'vpg-v2' ); ?></em></h2></div></div>
                    <div id="vpg-map" class="vpg-map" data-pins="<?php echo esc_attr( wp_json_encode( $pin ) ); ?>" style="height:420px"></div>
                </div>
            </section>
        <?php endif; ?>

    <section class="g-section g-section--alt" style="text-align:center">
        <div class="g-wrap">
            <a class="g-btn g-btn--red" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>"><?php esc_html_e( 'All locations', 'vpg-v2' ); ?> <span class="a">→</span></a>
        </div>
    </section>

<?php comments_template(); ?>
<?php endwhile; ?>
</main>

<?php get_footer();
