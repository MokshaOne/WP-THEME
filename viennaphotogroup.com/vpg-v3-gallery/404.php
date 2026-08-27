<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="vpg-main">
  <section class="g-section" style="padding:clamp(72px,12vw,160px) 0;text-align:center">
    <div class="g-wrap">
      <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( '404 · Not found', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-cta__title" style="margin:0 auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Lost in the <em>archive</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede" style="margin:0 auto 32px"><?php esc_html_e( "The page you're looking for isn't here. Try the front page, the magazine, or search.", 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'The magazine', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
