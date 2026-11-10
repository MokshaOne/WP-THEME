<?php
/** Template Name: Transparency
 *
 * 0962 · Where the money goes — the collective's open ledger. The
 * numbers live in the page content (editorial keeps them current);
 * the principles are structural and render themselves.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Transparency', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Open <em>books</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'A member-run collective owes its members the numbers. What running this costs, where any support goes, and the promises that never change.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Membership', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · forever', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Advertising', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'None · ever', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Updated', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( get_the_modified_date() ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <?php while ( have_posts() ) : the_post(); if ( trim( get_the_content() ) ) : ?>
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div>
    </div>
  </section>
  <?php endif; endwhile; ?>

  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'The constants', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'What never <em>changes</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-grid3">
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Free', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'No paywall, ever', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Every feature that exists today stays free. Future supporter perks add — they never subtract.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Ad-free', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'No ads, no tracking', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'No banners, no sponsored posts, no data sold. Analytics are cookie-free and self-hosted.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Member-run', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'Owned by nobody', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'No investor, no parent company. The collective decides, the books stay open, this page stays current.', 'vpg-v2' ); ?></p></article>
      </div>
    </div>
  </section>

</main>
<?php get_footer();