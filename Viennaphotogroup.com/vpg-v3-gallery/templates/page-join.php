<?php
/** Template Name: Join VPG */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Membership', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Become a <em>member</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( '€60 per year · PDF magazine, submission rights to the map, event discounts, and a profile page.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Price', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( '€60 / year · cancel anytime', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Includes', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Magazine · map · journal · events', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Status', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Beta · paid signup opens soon', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'No', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Ads · trackers · upsells', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <!-- ── PRICING ── -->
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Choose your tier', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Three ways <em>in</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-price">
        <div class="g-plan">
          <span class="g-plan__name"><?php esc_html_e( 'Reader', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€0<small> <?php esc_html_e( '/ forever', 'vpg-v2' ); ?></small></div>
          <ul>
            <li><?php esc_html_e( 'The full journal, free to read', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'The monthly newsletter', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Public map locations', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Event listings', 'vpg-v2' ); ?></li>
          </ul>
          <a class="g-btn g-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Start reading', 'vpg-v2' ); ?></a>
        </div>
        <div class="g-plan g-plan--feat">
          <span class="g-plan__name"><?php esc_html_e( 'Member', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€60<small> <?php esc_html_e( '/ year', 'vpg-v2' ); ?></small></div>
          <ul>
            <li><?php esc_html_e( 'Every monthly issue in PDF', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Twice-yearly print run, posted', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Submission rights — map &amp; journal', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'All member-only location notes', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Event discounts &amp; studio-share directory', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'The complete back-issue archive', 'vpg-v2' ); ?></li>
          </ul>
          <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/membership/#wait-list' ) ); ?>"><?php esc_html_e( 'Join wait-list', 'vpg-v2' ); ?> <span class="a">→</span></a>
        </div>
        <div class="g-plan">
          <span class="g-plan__name"><?php esc_html_e( 'Studio', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€180<small> <?php esc_html_e( '/ year', 'vpg-v2' ); ?></small></div>
          <ul>
            <li><?php esc_html_e( 'Everything in Member, ×4 seats', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'A studio listing in the directory', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Priority commission consideration', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'A page in the print annual', 'vpg-v2' ); ?></li>
          </ul>
          <a class="g-btn g-btn--ghost" href="<?php echo esc_url( home_url( '/membership/#wait-list' ) ); ?>"><?php esc_html_e( 'Enquire', 'vpg-v2' ); ?></a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── WAIT-LIST (dark CTA) ── -->
  <section class="g-section--dark g-section" id="wait-list">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( '— During beta', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Join the <em>wait-list</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center">
        <?php esc_html_e( 'Paid membership opens when we leave beta. Sign up here · one email when it opens · founding-member rate locked in for the first 100.', 'vpg-v2' ); ?>
      </p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/membership/#wait-list' ) ); ?>"><?php esc_html_e( 'Join wait-list', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Read the magazine free', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

</main>
<?php get_footer();
