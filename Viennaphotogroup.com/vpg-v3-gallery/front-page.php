<?php
/**
 * VPG v3 — front-page.php · "Gallery" editorial homepage.
 *
 * Same sections as the approved prototype index.html, driven by live data:
 *   1. Hero · headline + dual CTA + current-issue cover
 *   2. Ticker · scrolling section names
 *   3. Cover story · current magazine issue as the lead + recent journal minis
 *   4. Map band · live location count → the Leaflet map archive
 *   5. Latest journal · three recent posts
 *   6. Stats · founded / locations / articles / members
 *   7. Join CTA
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$current_issue = new WP_Query( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ] );
$mini_posts    = new WP_Query( [ 'post_type' => 'post', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true ] );
$journal_posts = new WP_Query( [ 'post_type' => 'post', 'posts_per_page' => 3, 'offset' => 4, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true ] );

$loc_count   = (int) ( wp_count_posts( 'vpg_location' )->publish ?? 0 );
$mag_count   = (int) ( wp_count_posts( 'vpg_magazine' )->publish ?? 0 );
$post_count  = (int) ( wp_count_posts( 'post' )->publish ?? 0 );
$mag_archive = get_post_type_archive_link( 'vpg_magazine' );
$loc_archive = get_post_type_archive_link( 'vpg_location' );
$journal_url = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/journal/' );
?>

<main id="vpg-main">

  <!-- ── HERO ── -->
  <section class="g-hero">
    <div class="g-wrap">
      <div class="g-hero__grid">
        <div class="g-rise">
          <p class="g-kicker" style="margin-bottom:20px">● <?php esc_html_e( 'Vienna · since 2018', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-hero__title"><?php
            echo wp_kses_post( __( 'The <em>quiet</em> magazine for photographers who <em>look</em>.', 'vpg-v2' ) );
          ?></h1>
          <p class="g-lede g-hero__lede"><?php esc_html_e( 'A member-run photography magazine. Locations, studios, reviews and a weekly journal — written by photographers, for photographers in Wien and the diaspora.', 'vpg-v2' ); ?></p>
          <div class="g-hero__row">
            <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join the community', 'vpg-v2' ); ?> <span class="a">→</span></a>
            <a class="g-link" href="<?php echo esc_url( $mag_archive ); ?>"><?php esc_html_e( 'Read the magazine', 'vpg-v2' ); ?> <span class="a">→</span></a>
          </div>
        </div>
        <?php if ( $current_issue->have_posts() ) : $current_issue->the_post(); ?>
        <figure class="g-hero__fig g-rise" style="margin:0;animation-delay:.08s">
          <a class="g-fig g-fig--3x4" href="<?php the_permalink(); ?>" style="display:block">
            <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); } ?>
          </a>
          <figcaption class="g-hero__cap"><?php
            $issue_no = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
            echo esc_html( ( $issue_no ?: get_the_date( 'F Y' ) ) . ' — ' . get_the_title() );
          ?></figcaption>
        </figure>
        <?php wp_reset_postdata(); endif; ?>
      </div>
    </div>
  </section>

  <!-- ── TICKER ── -->
  <div class="g-ticker" aria-hidden="true">
    <div class="g-ticker__track">
      <?php for ( $t = 0; $t < 2; $t++ ) : ?>
        <span>The Map</span><span class="x">✦</span><span>Magazine</span><span class="x">✦</span><span>Journal</span><span class="x">✦</span><span>Studios</span><span class="x">✦</span><span>Buying Guide</span><span class="x">✦</span><span>Tutorials</span><span class="x">✦</span>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ── COVER STORY / FEATURE ── -->
  <?php if ( $current_issue->have_posts() ) : ?>
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Cover story', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'This <em>month</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <a class="g-link" href="<?php echo esc_url( $mag_archive ); ?>"><?php esc_html_e( 'All issues', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>

      <div class="g-feature">
        <?php $current_issue->the_post(); ?>
        <a class="g-lead" href="<?php the_permalink(); ?>">
          <div class="g-fig g-fig--3x2">
            <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
          </div>
          <span class="g-cat"><?php echo esc_html( get_post_meta( get_the_ID(), '_vpg_issue_number', true ) ?: __( 'Feature', 'vpg-v2' ) ); ?></span>
          <h3 class="g-lead__title"><?php the_title(); ?></h3>
          <p class="g-lead__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 38 ) ); ?></p>
          <div class="g-byline">
            <span><?php echo esc_html( get_the_author() ); ?></span><span>·</span>
            <span><?php echo esc_html( function_exists( 'vpg_issue_reading_time' ) ? vpg_issue_reading_time( get_the_ID() ) . ' ' . __( 'min read', 'vpg-v2' ) : get_the_date( 'F Y' ) ); ?></span>
          </div>
        </a>
        <?php wp_reset_postdata(); ?>

        <div class="g-side">
          <?php if ( $mini_posts->have_posts() ) : while ( $mini_posts->have_posts() ) : $mini_posts->the_post(); ?>
            <a class="g-mini" href="<?php the_permalink(); ?>">
              <div class="g-fig"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'medium', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <div>
                <span class="g-cat"><?php $cat = get_the_category(); echo esc_html( $cat ? $cat[0]->name : __( 'Journal', 'vpg-v2' ) ); ?></span>
                <h4 class="g-mini__title"><?php the_title(); ?></h4>
                <div class="g-byline"><span><?php echo esc_html( get_the_author() ); ?></span></div>
              </div>
            </a>
          <?php endwhile; wp_reset_postdata(); else : ?>
            <p class="g-lede" style="font-size:16px;color:var(--g-mid)"><?php esc_html_e( 'Journal articles will appear here as members publish them.', 'vpg-v2' ); ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── MAP BAND ── -->
  <section class="g-section--dark">
    <div class="g-mapband">
      <div class="g-mapband__text">
        <span class="g-kicker"><?php esc_html_e( 'The Map · member-curated', 'vpg-v2' ); ?></span>
        <p class="g-mapband__big" style="margin:18px 0 20px"><?php echo esc_html( $loc_count ); ?> <em><?php esc_html_e( 'locations', 'vpg-v2' ); ?></em>.</p>
        <p class="g-lede" style="color:rgba(255,255,255,.78);margin-bottom:28px"><?php esc_html_e( 'Where Vienna is worth looking — every spot with light notes, best times and honest access. Pick a category, click a place, go shoot.', 'vpg-v2' ); ?></p>
        <div><a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( $loc_archive ); ?>"><?php esc_html_e( 'Open the map', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
      </div>
      <?php
      // Use a recent location's photo as the band image; fall back to a dark panel.
      $band = new WP_Query( [ 'post_type' => 'vpg_location', 'posts_per_page' => 1, 'meta_query' => [ [ 'key' => '_thumbnail_id' ] ] ] );
      if ( $band->have_posts() ) : $band->the_post(); ?>
        <div class="g-mapband__fig"><?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
      <?php wp_reset_postdata(); else : ?>
        <div class="g-mapband__fig" aria-hidden="true" style="background:#161616"></div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── LATEST FROM JOURNAL ── -->
  <?php if ( $journal_posts->have_posts() ) : ?>
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Journal · free to read', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Latest <em>writing</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <a class="g-link" href="<?php echo esc_url( $journal_url ); ?>"><?php esc_html_e( 'All articles', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>
      <div class="g-grid3">
        <?php while ( $journal_posts->have_posts() ) : $journal_posts->the_post(); ?>
          <a class="g-card" href="<?php the_permalink(); ?>">
            <div class="g-fig g-fig--4x3"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
            <span class="g-cat"><?php $cat = get_the_category(); echo esc_html( $cat ? $cat[0]->name : __( 'Journal', 'vpg-v2' ) ); ?></span>
            <h3 class="g-card__title"><?php the_title(); ?></h3>
            <div class="g-byline"><span><?php echo esc_html( function_exists( 'vpg_reading_time' ) ? vpg_reading_time( get_the_content() ) . ' ' . __( 'min read', 'vpg-v2' ) : get_the_date() ); ?></span></div>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ── STATS ── -->
  <section class="g-section--alt g-section--tight">
    <div class="g-wrap">
      <div class="g-stats">
        <div class="g-stat"><div class="g-stat__n"><em><?php echo esc_html( get_theme_mod( 'vpg_since', '2018' ) ); ?></em></div><div class="g-stat__l"><?php esc_html_e( 'Founded in Wien', 'vpg-v2' ); ?></div></div>
        <div class="g-stat"><div class="g-stat__n"><?php echo esc_html( $loc_count ); ?><sup>+</sup></div><div class="g-stat__l"><?php esc_html_e( 'Locations mapped', 'vpg-v2' ); ?></div></div>
        <div class="g-stat"><div class="g-stat__n"><?php echo esc_html( $post_count ?: $mag_count ); ?></div><div class="g-stat__l"><?php esc_html_e( 'Articles published', 'vpg-v2' ); ?></div></div>
        <div class="g-stat">
          <?php
          $u_count = get_transient( 'vpg_user_count' );
          if ( false === $u_count ) { $u = count_users(); $u_count = (int) ( $u['total_users'] ?? 0 ); set_transient( 'vpg_user_count', $u_count, HOUR_IN_SECONDS ); }
          ?>
          <div class="g-stat__n"><?php echo esc_html( $u_count ); ?><sup>+</sup></div><div class="g-stat__l"><?php esc_html_e( 'Members', 'vpg-v2' ); ?></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── JOIN CTA ── -->
  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Membership · €60 / year', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Join the <em>studio</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'Every monthly issue in PDF and print, submission rights to the map and journal, event discounts and the studio-share directory. Forever-keepable.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Become a member', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'What we publish', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

</main>

<?php get_footer();
