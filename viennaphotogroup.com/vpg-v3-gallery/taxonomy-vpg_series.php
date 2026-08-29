<?php
/** VPG v3 · series archive (0241/0481) — a learning path or story arc, in order. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$term  = get_queried_object();
$parts = get_posts( [
    'post_type'      => [ 'post', 'vpg_tutorial' ],
    'post_status'    => 'publish',
    'posts_per_page' => 30,
    'orderby'        => 'date',
    'order'          => 'ASC',
    'tax_query'      => [ [ 'taxonomy' => 'vpg_series', 'terms' => $term->term_id ] ],
] );
?>
<main id="vpg-main">
  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Series', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo esc_html( $term->name ); ?><span style="color:var(--g-red)">.</span></h1>
      <?php if ( $term->description ) : ?><p class="g-lede g-phero__lede"><?php echo esc_html( $term->description ); ?></p><?php endif; ?>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Parts', 'vpg-v2' ); ?></dt><dd><?php echo count( $parts ); ?></dd>
      <dt><?php esc_html_e( 'Order', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Start at 01', 'vpg-v2' ); ?></dd>
    </dl>
  </div></div></section>
  <section class="g-section"><div class="g-wrap">
    <div style="display:grid;gap:0">
      <?php foreach ( $parts as $i => $pp ) : ?>
        <a href="<?php echo esc_url( get_permalink( $pp ) ); ?>" style="display:grid;grid-template-columns:52px minmax(0,1fr) auto;gap:20px;align-items:center;padding:18px 0;border-top:1px solid var(--g-line)">
          <span class="g-display" style="font-size:26px;color:var(--g-red)"><?php printf( '%02d', $i + 1 ); ?></span>
          <span>
            <strong style="display:block;font-size:19px"><?php echo esc_html( $pp->post_title ); ?></strong>
            <span class="g-meta"><?php echo esc_html( get_the_author_meta( 'display_name', (int) $pp->post_author ) ); ?> · <?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( $pp->post_content ) . ' min' : get_the_date( '', $pp ) ); ?></span>
          </span>
          <span style="color:var(--g-faint);font-weight:800">→</span>
        </a>
      <?php endforeach; ?>
    </div>
  </div></section>
</main>
<?php get_footer();
