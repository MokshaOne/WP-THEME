<?php
/**
 * VPG v3 — magazine archive · "Gallery" cover wall.
 * Current issue gets the lead block (cover + lede); the rest tile as a
 * back-issue grid. Query, issue meta and pagination are unchanged from v2.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged  = max( 1, get_query_var( 'paged' ) );
$issues = new WP_Query( [
    'post_type'      => 'vpg_magazine',
    'posts_per_page' => 13,
    'post_status'    => 'publish',
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
] );
$total = (int) $issues->found_posts;
?>

<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'The Magazine', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Every <em>issue</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'The monthly Vienna Photo Group magazine. Members get every issue as a print-ready PDF, free, forever-keepable. Older issues are open-access here.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Published', 'vpg-v2' ); ?></dt><dd><?php printf( esc_html( _n( '%d issue', '%d issues', $total, 'vpg-v2' ) ), $total ); ?></dd>
          <dt><?php esc_html_e( 'Cadence', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Monthly', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Formats', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'PDF for members · print 2× a year', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <?php if ( $issues->have_posts() ) : ?>

    <?php
    // ── Lead: the most recent issue (only on page 1) ──
    if ( $paged === 1 ) :
        $issues->the_post();
        $lead_no  = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
        $lead_dt  = get_post_meta( get_the_ID(), '_vpg_issue_date', true );
        $lead_pdf = get_post_meta( get_the_ID(), '_vpg_pdf_url', true );
    ?>
    <section class="g-section">
      <div class="g-wrap">
        <div class="g-head">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'Current issue', 'vpg-v2' ); ?> · <?php echo esc_html( get_the_date( 'F Y' ) ); ?></span>
            <h2 class="g-head__t"><?php echo esc_html( get_the_title() ); ?></h2>
          </div>
          <?php if ( $lead_pdf ) : ?><a class="g-link" href="<?php echo esc_url( $lead_pdf ); ?>" target="_blank" rel="noopener">PDF <span class="a">↓</span></a><?php endif; ?>
        </div>
        <div class="g-issue">
          <div class="g-issue__cover">
            <a class="g-fig" href="<?php the_permalink(); ?>" style="display:block;aspect-ratio:3/4">
              <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
            </a>
            <p class="g-meta g-issue__cap"><?php echo esc_html( ( $lead_no ?: __( 'Issue', 'vpg-v2' ) ) . ( $lead_dt ? ' · ' . $lead_dt : '' ) ); ?></p>
          </div>
          <div>
            <p class="g-lede" style="margin-bottom:28px"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 48 ) ); ?></p>
            <div class="g-hero__row">
              <a class="g-btn g-btn--lg g-btn--red" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read this issue', 'vpg-v2' ); ?> <span class="a">→</span></a>
              <a class="g-link" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Members read all', 'vpg-v2' ); ?> <span class="a">→</span></a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- ── Back issues ── -->
    <section class="g-section g-section--alt">
      <div class="g-wrap">
        <div class="g-head">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'The archive', 'vpg-v2' ); ?></span>
            <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Back <em>issues</em>.', 'vpg-v2' ) ); ?></h2>
          </div>
        </div>
        <div class="g-issuegrid">
          <?php while ( $issues->have_posts() ) : $issues->the_post();
            $issue_no = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
          ?>
            <a class="g-issuecard" href="<?php the_permalink(); ?>">
              <div class="g-fig"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium_large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <div class="g-issuecard__t"><?php echo esc_html( ( $issue_no ? $issue_no . ' — ' : '' ) . get_the_title() ); ?></div>
              <div class="g-issuecard__d"><?php echo esc_html( get_the_date( 'F Y' ) ); ?></div>
            </a>
          <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
          <?php the_posts_pagination( [ 'prev_text' => __( '← Previous', 'vpg-v2' ), 'next_text' => __( 'Next →', 'vpg-v2' ), 'mid_size' => 2 ] ); ?>
        </div>
      </div>
    </section>

  <?php else : ?>
    <section class="g-section">
      <div class="g-wrap"><p class="g-lede"><?php esc_html_e( 'The first issue is being assembled. Subscribe to be notified when it ships.', 'vpg-v2' ); ?></p></div>
    </section>
  <?php endif; ?>

</main>

<?php wp_reset_postdata(); get_footer();
