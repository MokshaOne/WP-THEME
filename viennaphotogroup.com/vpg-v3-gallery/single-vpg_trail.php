<?php
/** VPG v3 · single · Photowalk trail — numbered stops on one map. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $stops = function_exists( 'vpg_trail_stops' ) ? vpg_trail_stops( get_the_ID() ) : [];
    $t_attr  = function_exists( 'vpg_trail_attrs' ) ? vpg_trail_attrs( get_the_ID() ) : [];
    $t_blind = ! empty( $t_attr['blind'] );                                             // 0098 · no motif hints
    $t_rest  = function_exists( 'vpg_trail_idlist' ) ? vpg_trail_idlist( get_the_ID(), '_vpg_t_rest' ) : [];   // 0086
    $t_stage = function_exists( 'vpg_trail_idlist' ) ? vpg_trail_idlist( get_the_ID(), '_vpg_t_stages' ) : []; // 0084
    $pins  = [];
    foreach ( $stops as $i => $s ) {
        if ( ! $s['coords'] ) continue;
        $pins[] = [
            'id'    => (int) $s['post']->ID,
            'lat'   => $s['coords'][0],
            'lng'   => $s['coords'][1],
            'title' => $t_blind ? sprintf( __( 'Stop %02d', 'vpg-v2' ), $i + 1 ) : sprintf( '%02d · %s', $i + 1, get_the_title( $s['post'] ) ),
            'url'   => $t_blind ? '' : get_permalink( $s['post'] ),
            'lede'  => $t_blind ? '' : wp_trim_words( get_the_excerpt( $s['post'] ), 14 ),
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
            <?php $tdiff = get_post_meta( get_the_ID(), '_vpg_trail_difficulty', true );
            $tlabels = [ 'easy' => __( 'Easy', 'vpg-v2' ), 'moderate' => __( 'Moderate', 'vpg-v2' ), 'sporty' => __( 'Sporty', 'vpg-v2' ) ];
            if ( isset( $tlabels[ $tdiff ] ) ) : ?>
            <dt><?php esc_html_e( 'Difficulty', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $tlabels[ $tdiff ] ); ?></dd>
            <?php endif; ?>
            <?php $tgeo = function_exists( 'vpg_trail_geo' ) ? vpg_trail_geo( get_the_ID() ) : null;
            if ( $tgeo && $tgeo['km'] > 0 ) : ?>
            <dt><?php esc_html_e( 'Distance', 'vpg-v2' ); ?></dt><dd><?php printf( esc_html__( '%1$s km · ~%2$dh %3$02dmin', 'vpg-v2' ), esc_html( number_format_i18n( $tgeo['km'], 1 ) ), intdiv( $tgeo['minutes'], 60 ), $tgeo['minutes'] % 60 ); ?></dd>
            <?php endif; ?>
            <dt><?php esc_html_e( 'GPX', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_trail_gpx&trail=' . get_the_ID() ) ); ?>"><?php esc_html_e( 'Download route ↓', 'vpg-v2' ); ?></a></dd>
            <dt><?php esc_html_e( 'Curated by', 'vpg-v2' ); ?></dt><dd><?php the_author(); ?></dd>
          </dl>
        </div>
      </div>
    </section>

    <?php if ( function_exists( 'vpg_trail_render_statbar' ) ) : ?>
    <section class="g-section g-section--tight"><div class="g-wrap"><?php vpg_trail_render_statbar( get_the_ID() ); ?></div></section>
    <?php endif; ?>

    <?php if ( get_the_content() ) : ?>
    <section class="g-section g-section--tight">
      <div class="g-wrap"><div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div></div>
    </section>
    <?php endif; ?>

    <?php if ( $pins ) : ?>
    <section class="g-section g-section--tight">
      <div class="g-wrap">
        <div id="vpg-map" class="vpg-map vpg-map--tall" data-trail="<?php echo (int) get_the_ID(); ?>" data-pins="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>"></div>
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
              $sid  = (int) $s['post']->ID;
              $best = get_post_meta( $sid, 'location_best_time', true );
              $is_rest = in_array( $sid, $t_rest, true );
          ?>
          <a class="g-row" data-stop="<?php echo $sid; ?>" data-title="<?php echo esc_attr( get_the_title( $s['post'] ) ); ?>" style="grid-template-columns:60px 1fr auto" href="<?php echo esc_url( get_permalink( $s['post'] ) ); ?>#pin-<?php echo $sid; ?>">
            <span style="font-weight:900;font-stretch:125%;font-size:26px;color:var(--g-red)"><?php printf( '%02d', $i + 1 ); ?></span>
            <div>
              <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $s['post'] ) ); ?><?php if ( $is_rest ) : ?> <span title="<?php esc_attr_e( 'Coffee / rest stop', 'vpg-v2' ); ?>" style="font-size:14px">☕</span><?php endif; ?></h3>
              <?php if ( ! $t_blind ) : ?><div class="g-byline"><span><?php echo esc_html( wp_trim_words( get_the_excerpt( $s['post'] ), 16 ) ); ?></span></div><?php endif; ?>
            </div>
            <span class="g-row__when"><?php echo $best && ! $t_blind ? esc_html( $best ) : ''; ?></span>
          </a>
          <?php if ( in_array( $sid, $t_stage, true ) && $i < count( $stops ) - 1 ) : ?>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;color:var(--g-mid);font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase"><span style="flex:1;height:1px;background:var(--g-line)"></span>🚏 <?php esc_html_e( 'Stage break — half-day mark', 'vpg-v2' ); ?><span style="flex:1;height:1px;background:var(--g-line)"></span></div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <p style="margin-top:14px"><button type="button" id="vpg-trail-missed" class="g-btn g-btn--ghost" style="font-size:12px">⚑ <?php esc_html_e( 'Save the stops I missed', 'vpg-v2' ); ?></button></p>
        <?php else : ?>
          <p class="g-lede" style="color:var(--g-mid)"><?php esc_html_e( 'No stops pinned yet.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
      </div>
    </section>

    <?php if ( function_exists( 'vpg_trail_render_extras' ) ) vpg_trail_render_extras( get_the_ID() ); ?>

    <section class="g-section g-section--dark" style="text-align:center">
      <div class="g-wrap">
        <a class="g-btn g-btn--red g-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_trail' ) ); ?>"><?php esc_html_e( 'All trails', 'vpg-v2' ); ?> <span class="a">&rarr;</span></a>
      </div>
    </section>

<?php endwhile; ?>
</main>
<?php get_footer();
