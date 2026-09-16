<?php
/** VPG v3 · archive · Gallery walls (1002) — the curated rooms of the museum. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_wall', 'posts_per_page' => 12, 'post_status' => 'publish', 'paged' => $paged ] );
?>
<main id="vpg-main">

  <section class="g-phero g-phero--dark" style="background:var(--g-ink,#0B0B0B);color:#fff"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:18px;color:var(--g-red,#E5341F)">● <?php esc_html_e( 'Gallery walls', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title" style="color:#fff"><?php echo wp_kses_post( __( 'The <em>walls</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede" style="color:rgba(255,255,255,.72)"><?php esc_html_e( 'Curated hangings from member uploads — each wall is one theme, one room, one walk through the collective eye.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside" style="color:#fff">
      <dt style="color:rgba(255,255,255,.5)"><?php esc_html_e( 'Walls', 'vpg-v2' ); ?></dt><dd><?php echo (int) $q->found_posts; ?></dd>
      <dt style="color:rgba(255,255,255,.5)"><?php esc_html_e( 'Curation', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Editorial, from uploads', 'vpg-v2' ); ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section"><div class="g-wrap">
    <?php if ( $q->have_posts() ) : ?>
      <div class="g-grid3">
        <?php while ( $q->have_posts() ) : $q->the_post();
            $ids = function_exists( 'vpg_curated_ids' ) ? vpg_curated_ids( get_the_ID() ) : [];
        ?>
          <a class="g-card" href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) : ?><div class="g-fig g-fig--3x2"><?php the_post_thumbnail( 'medium_large' ); ?></div>
            <?php elseif ( $ids ) : ?><div class="g-fig g-fig--3x2"><?php echo wp_get_attachment_image( $ids[0], 'medium_large' ); ?></div><?php endif; ?>
            <span class="g-cat"><?php printf( esc_html( _n( '%d frame', '%d frames', count( $ids ), 'vpg-v2' ) ), count( $ids ) ); ?></span>
            <h3 class="g-card__title"><?php the_title(); ?></h3>
          </a>
        <?php endwhile; ?>
      </div>
      <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
        <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
      </div>
    <?php else : ?>
      <p class="g-lede"><?php esc_html_e( 'No walls hung yet — editorial curates them from member uploads.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
  </div></section>

</main>
<?php wp_reset_postdata(); get_footer();
