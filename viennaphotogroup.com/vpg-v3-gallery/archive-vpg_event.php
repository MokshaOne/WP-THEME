<?php
/** VPG v2 · archive · Events */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_event', 'posts_per_page' => 12, 'post_status' => 'publish', 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC' ] );
$total = (int) $q->found_posts;
?>

<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Events', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Photo <em>events</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Workshops, openings, walks and meetups for photographers in Vienna. Members get early access and discounts.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Listed', 'vpg-v2' ); ?></dt><dd><?php printf( esc_html__( '%d entries', 'vpg-v2' ), $total ); ?></dd>
          <dt><?php esc_html_e( 'Format', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Workshops · walks · meetups', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Access', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Members get early access', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Calendar', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_events_feed' ) ); ?>"><?php esc_html_e( 'Subscribe · .ics ↓', 'vpg-v2' ); ?></a></dd>
          <dt><?php esc_html_e( 'Competitions', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( get_post_type_archive_link( 'vpg_competition' ) ); ?>"><?php esc_html_e( 'The chronicle →', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Schedule', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'The <em>calendar</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>

      <?php if ( $q->have_posts() ) : ?>
        <div class="g-grid3">
          <?php while ( $q->have_posts() ) : $q->the_post();
            $meta_val = vpg_field( '_vpg_event_date' );
          ?>
            <a class="g-card" href="<?php the_permalink(); ?>">
              <div class="g-fig g-fig--4x3"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <span class="g-cat"><?php esc_html_e( 'Events', 'vpg-v2' ); ?></span>
              <h3 class="g-card__title"><?php the_title(); ?></h3>
              <div class="g-byline">
                <span><?php echo $meta_val ? esc_html( 'Date · ' . $meta_val ) : esc_html( get_the_date( 'M Y' ) ); ?></span>
              </div>
            </a>
          <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
          <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
        </div>

      <?php else : ?>
        <p class="g-lede"><?php esc_html_e( 'No events scheduled. Members can submit from the dashboard.', 'vpg-v2' ); ?></p>
      <?php endif; ?>

    </div>
  </section>

</main>

<?php wp_reset_postdata(); get_footer();
