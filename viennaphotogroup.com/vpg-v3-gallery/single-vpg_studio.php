<?php
/** VPG v3 · single · Studio (Gallery). */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = 'studio_hourly_rate' ? vpg_field( 'studio_hourly_rate' ) : '';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px"><?php esc_html_e( 'Studio', 'vpg-v2' ); ?><?php if ( $meta ) echo ' · ' . esc_html( $meta ); ?></p>
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
              <dt><?php esc_html_e( 'Rate', 'vpg-v2' ); ?></dt>
              <dd><?php echo $meta ? esc_html( $meta ) : '—'; ?></dd>
            </dl>
            <a class="g-link" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_studio' ) ); ?>" style="margin-top:18px"><?php esc_html_e( 'All studios', 'vpg-v2' ); ?> <span class="a">→</span></a>
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
            <a class="g-btn g-btn--red" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_studio' ) ); ?>"><?php esc_html_e( 'All studios', 'vpg-v2' ); ?> <span class="a">→</span></a>
        </div>
    </section>

<?php comments_template(); ?>
<?php endwhile; ?>
</main>

<?php get_footer();
