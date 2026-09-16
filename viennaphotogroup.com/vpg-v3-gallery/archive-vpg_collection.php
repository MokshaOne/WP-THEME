<?php
/** VPG v3 · archive · Collections (1002) — cross-type reading lists, curated. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_collection', 'posts_per_page' => 14, 'post_status' => 'publish', 'paged' => $paged ] );
?>
<main id="vpg-main">

  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Collections', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Curated <em>threads</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede"><?php esc_html_e( 'Editorial collections across every content type — spots, stories, tutorials and frames tied together by one idea.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Collections', 'vpg-v2' ); ?></dt><dd><?php echo (int) $q->found_posts; ?></dd>
      <dt><?php esc_html_e( 'Scope', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Every content type', 'vpg-v2' ); ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section g-section--alt"><div class="g-wrap">
    <?php if ( $q->have_posts() ) : ?>
      <div style="display:grid;gap:0">
        <?php $n = ( $paged - 1 ) * 14; while ( $q->have_posts() ) : $q->the_post(); $n++;
            $ids = function_exists( 'vpg_curated_ids' ) ? vpg_curated_ids( get_the_ID() ) : [];
        ?>
          <a href="<?php the_permalink(); ?>" style="display:grid;grid-template-columns:52px minmax(0,1fr) auto;gap:20px;align-items:center;padding:18px 0;border-top:1px solid var(--g-line)">
            <span class="g-display" style="font-size:26px;color:var(--g-red)"><?php printf( '%02d', $n ); ?></span>
            <span>
              <strong style="display:block;font-size:19px"><?php the_title(); ?></strong>
              <span class="g-meta"><?php printf( esc_html( _n( '%d piece', '%d pieces', count( $ids ), 'vpg-v2' ) ), count( $ids ) ); ?><?php if ( has_excerpt() ) : ?> · <?php echo esc_html( wp_trim_words( get_the_excerpt(), 14 ) ); ?><?php endif; ?></span>
            </span>
            <span style="color:var(--g-faint);font-weight:800">→</span>
          </a>
        <?php endwhile; ?>
      </div>
      <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
        <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
      </div>
    <?php else : ?>
      <p class="g-lede"><?php esc_html_e( 'No collections yet — editorial ties the first thread soon.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
  </div></section>

</main>
<?php wp_reset_postdata(); get_footer();
