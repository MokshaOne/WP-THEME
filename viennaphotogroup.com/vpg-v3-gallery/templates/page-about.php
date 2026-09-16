<?php
/**
 * Template Name: About / Editorial
 *
 * The VPG origin story · genesis → transition → pivot · plus the
 * architectural principles that define the current platform.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'About', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'We archive the city. We document the <em>light</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Vienna Photo Group · founded December 2019 · a decentralised, data-driven reference hub for photographers shooting Wien.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Founded', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'December 2019 · Wien', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Structure', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Decentralised · member-run', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Frontend', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Public · read-only', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Produced by', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Raveenthiran × on1.agency', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Chapter 1', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Origin &amp; <em>evolution</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta">2019 → 2024</div>
      </div>
      <div class="g-twocol">
        <div class="g-prose">
          <p class="g-prose__drop"><?php esc_html_e( 'VPG was founded by a collective of local photographers. The initial phase was an algorithmic feature page on Instagram, curating visual work within the city\'s creative ecosystem.', 'vpg-v2' ); ?></p>
          <p><?php esc_html_e( 'In summer 2023 came the transition — the first iteration of the dedicated VPG web platform, a static bridge hosting tutorials and archiving the imagery pulled from the Instagram network. The first step off borrowed land.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'The pivot', 'vpg-v2' ); ?></h3>
          <p><?php esc_html_e( 'Meta API changes exposed the fragility of building on borrowed infrastructure. A complete philosophical and technical restructuring followed. VPG became an independent, decentralised, data-driven community platform and location index.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'System &amp; architecture', 'vpg-v2' ); ?></h3>
          <p><?php esc_html_e( 'VPG functions as a highly structured, decentralised repository. Custom-coded · clean architecture · advanced data-mapping and location intelligence · optimised for seamless utility. To protect the integrity of the index, the frontend stays read-only for the public — a pure, uncompromised reference hub.', 'vpg-v2' ); ?></p>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'Visual mastery requires execution, not <em>validation</em>.', 'vpg-v2' ) ); ?><span class="g-pull__cite"><?php esc_html_e( '— The Philosophy', 'vpg-v2' ); ?></span></p>
        </aside>
      </div>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap">
      <div class="g-stats">
        <div class="g-stat"><div class="g-stat__n"><em>2019</em></div><div class="g-stat__l" style="color:rgba(255,255,255,.6)"><?php esc_html_e( 'Founded in Wien', 'vpg-v2' ); ?></div></div>
        <div class="g-stat"><div class="g-stat__n">340<sup>+</sup></div><div class="g-stat__l" style="color:rgba(255,255,255,.6)"><?php esc_html_e( 'Members', 'vpg-v2' ); ?></div></div>
        <div class="g-stat"><div class="g-stat__n">30</div><div class="g-stat__l" style="color:rgba(255,255,255,.6)"><?php esc_html_e( 'Issues published', 'vpg-v2' ); ?></div></div>
        <div class="g-stat"><div class="g-stat__n">118<sup>+</sup></div><div class="g-stat__l" style="color:rgba(255,255,255,.6)"><?php esc_html_e( 'Locations mapped', 'vpg-v2' ); ?></div></div>
      </div>
    </div>
  </section>

  <?php if ( have_posts() ) : while ( have_posts() ) : the_post();
      $extra = trim( strip_shortcodes( wp_strip_all_tags( get_the_content(), true ) ) );
      if ( $extra ) : ?>
        <section class="g-section g-section--alt">
          <div class="g-wrap">
            <div class="g-head">
              <div>
                <span class="g-kicker"><?php esc_html_e( 'Editor\'s note', 'vpg-v2' ); ?></span>
                <h2 class="g-head__t"><?php echo wp_kses_post( __( 'From the <em>desk</em>.', 'vpg-v2' ) ); ?></h2>
              </div>
            </div>
            <div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div>
          </div>
        </section>
  <?php endif; endwhile; endif; ?>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Inner circle', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Feed the <em>matrix</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'Active creators contribute coordinates, structural checks and visual notes. The platform stays uncompromised because the people who feed it actually walk the city.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Apply to contribute', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>"><?php esc_html_e( 'Browse the index', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

</main>

<?php get_footer();
