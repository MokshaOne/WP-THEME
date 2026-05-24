<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="vp-main">
  <header class="vp-page-hero">
    <p class="vp-eyebrow">— 404 · Not Found</p>
    <h1 class="vp-page-head">Lost.</h1>
    <p class="vp-page-lede"><?php esc_html_e( "The page you're looking for isn't here.", 'COTERIE' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'COTERIE' ); ?></a></p>
  </header>
</main>
<?php get_footer();
