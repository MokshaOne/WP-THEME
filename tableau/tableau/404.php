<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rt-main">
  <header class="rt-page-hero">
    <p class="rt-eyebrow">— 404 · Not Found</p>
    <h1 class="rt-page-head">Lost.</h1>
    <p class="rt-page-lede"><?php esc_html_e( "The page you're looking for isn't here.", 'TABLEAU' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'TABLEAU' ); ?></a></p>
  </header>
</main>
<?php get_footer();
