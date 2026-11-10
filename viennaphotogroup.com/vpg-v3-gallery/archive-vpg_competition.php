<?php
/** VPG v3 · archive · Competitions (0455) — the chronicle of winners. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_competition', 'posts_per_page' => 10, 'post_status' => 'publish', 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC' ] );
?>
<main id="vpg-main">

  <section class="g-phero"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Competitions', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>chronicle</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede g-phero__lede"><?php esc_html_e( 'Every competition the collective has run — winners, jury words, and the open calls you can still enter. No leaderboards, just moments.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside">
      <dt><?php esc_html_e( 'Held so far', 'vpg-v2' ); ?></dt><dd><?php echo (int) $q->found_posts; ?></dd>
      <dt><?php esc_html_e( 'Jury duty', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Two sentences, always', 'vpg-v2' ); ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section g-section--alt"><div class="g-wrap">
    <?php if ( $q->have_posts() ) : ?>
      <div style="display:grid;gap:0">
      <?php while ( $q->have_posts() ) : $q->the_post();
          $closed = get_post_meta( get_the_ID(), '_vpg_comp_closed', true ) === '1';
          $winner = (int) get_post_meta( get_the_ID(), '_vpg_comp_winner', true );
          $reason = get_post_meta( get_the_ID(), '_vpg_comp_reason', true );
          $wthumb = $winner ? wp_get_attachment_image_url( $winner, 'medium_large' ) : '';
          $wname  = $winner ? get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $winner ) ) : '';
      ?>
        <article style="border-top:1px solid var(--g-line);padding:28px 0;display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:32px;align-items:start">
          <div>
            <p class="g-meta" style="margin-bottom:6px">
              <?php echo esc_html( get_the_date( 'M Y' ) ); ?> ·
              <span style="color:<?php echo $closed ? 'var(--g-mid)' : 'var(--g-red)'; ?>;font-weight:800"><?php echo $closed ? esc_html__( 'decided', 'vpg-v2' ) : esc_html__( '● open — enter now', 'vpg-v2' ); ?></span>
            </p>
            <h2 style="font-size:24px;margin-bottom:8px"><a href="<?php the_permalink(); ?>" style="text-decoration:none"><?php the_title(); ?></a></h2>
            <?php if ( $wname ) : ?>
              <p style="font-weight:700;margin-bottom:6px">🏆 <?php echo esc_html( $wname ); ?></p>
            <?php endif; ?>
            <?php if ( $reason ) : ?>
              <blockquote style="border-left:3px solid var(--g-red);margin:10px 0 0;padding:4px 0 4px 16px;font-size:14px;color:var(--g-mid);font-style:italic">„<?php echo esc_html( $reason ); ?>“<br><cite style="font-style:normal;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase"><?php esc_html_e( '— the jury', 'vpg-v2' ); ?></cite></blockquote>
            <?php endif; ?>
          </div>
          <?php if ( $wthumb ) : ?>
            <a href="<?php the_permalink(); ?>" style="display:block"><img src="<?php echo esc_url( $wthumb ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Winning photo by %s', 'vpg-v2' ), $wname ) ); ?>" loading="lazy" style="width:100%;height:auto;display:block"></a>
          <?php elseif ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>" style="display:block"><?php the_post_thumbnail( 'medium_large', [ 'style' => 'width:100%;height:auto;display:block' ] ); ?></a>
          <?php endif; ?>
        </article>
      <?php endwhile; ?>
      </div>
      <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
        <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
      </div>
    <?php else : ?>
      <p class="g-lede"><?php esc_html_e( 'No competitions yet — the first monthly challenge starts the chronicle.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
  </div></section>

</main>
<?php wp_reset_postdata(); get_footer();
