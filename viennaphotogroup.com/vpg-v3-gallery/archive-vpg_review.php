<?php
/** VPG v2 · archive · Buying guide */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_review', 'posts_per_page' => 12, 'post_status' => 'publish', 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC' ] );
$total = (int) $q->found_posts;
?>

<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Buying guide', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Gear, <em>reviewed</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Cameras, lenses, analog equipment and accessories — scored on design, performance and price by members who actually own them.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Reviewed', 'vpg-v2' ); ?></dt><dd><?php printf( esc_html__( '%d entries', 'vpg-v2' ), $total ); ?></dd>
          <dt><?php esc_html_e( 'Scoring', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Scored /10', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'By', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Members who own the gear', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Gear', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'The <em>reviews</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>

      <?php if ( $q->have_posts() ) : ?>
        <div class="g-grid3">
          <?php while ( $q->have_posts() ) : $q->the_post();
            $meta_val = '' ? vpg_field( '' ) : '';
          ?>
            <a class="g-card" href="<?php the_permalink(); ?>">
              <div class="g-fig g-fig--4x3"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <span class="g-cat"><?php esc_html_e( 'Buying guide', 'vpg-v2' ); ?></span>
              <h3 class="g-card__title"><?php the_title(); ?></h3>
              <div class="g-byline">
                <span><?php echo $meta_val ? esc_html( ' · ' . $meta_val ) : esc_html( get_the_date( 'M Y' ) ); ?></span>
              </div>
            </a>
          <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
          <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
        </div>

      <?php else : ?>
        <p class="g-lede"><?php esc_html_e( 'The buying guide is being written. Members can submit from the dashboard.', 'vpg-v2' ); ?></p>
      <?php endif; ?>

    </div>
  </section>

</main>

<?php wp_reset_postdata(); get_footer();
