<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mo-main">
  <header class="mo-page-hero">
    <p class="mo-eyebrow">— 404 · Not Found</p>
    <h1 class="mo-page-head">Lost.</h1>
    <p class="mo-page-lede"><?php esc_html_e( "The page you're looking for isn't here.", 'PHOSPHOR' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'PHOSPHOR' ); ?></a></p>
  </header>
</main>
<?php get_footer();
