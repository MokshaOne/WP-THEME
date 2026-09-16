<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="vpg-main">
  <section class="g-section" style="padding:clamp(72px,10vw,140px) 0;text-align:center">
    <div class="g-wrap">
      <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( '404 · Not found', 'vpg-v2' ); ?></p>
      <h1 class="g-display g-cta__title" style="margin:0 auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Lost in the <em>archive</em>.', 'vpg-v2' ) ); ?></h1>
      <p class="g-lede" style="margin:0 auto 36px;text-align:center"><?php esc_html_e( "The page you're looking for isn't here. Search the whole index, or take one of the doors below.", 'vpg-v2' ); ?></p>

      <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;max-width:560px;margin:0 auto 40px">
        <label class="g-skip" for="s404"><?php esc_html_e( 'Search', 'vpg-v2' ); ?></label>
        <input class="g-input" id="s404" type="search" name="s" placeholder="<?php esc_attr_e( 'Search locations, articles, issues…', 'vpg-v2' ); ?>" style="flex:1;min-width:220px">
        <button class="g-btn g-btn--red" type="submit"><?php esc_html_e( 'Search', 'vpg-v2' ); ?> <span class="a">→</span></button>
      </form>

      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>"><?php esc_html_e( 'Open the map', 'vpg-v2' ); ?></a>
        <?php
        $latest_issue = get_posts( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 1, 'post_status' => 'publish' ] );
        if ( $latest_issue ) :
        ?>
        <a class="g-btn g-btn--lg g-btn--ghost" href="<?php echo esc_url( get_permalink( $latest_issue[0] ) ); ?>"><?php esc_html_e( 'Current issue', 'vpg-v2' ); ?></a>
        <?php endif; ?>
        <a class="g-btn g-btn--lg g-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
