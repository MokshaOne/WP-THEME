<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="rv-main">
  <header class="rv-page-hero">
    <p class="rv-eyebrow">— 404 · Not Found</p>
    <h1 class="rv-page-head">Lost in <em>orbit</em>.</h1>
    <p class="rv-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'SALON' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'SALON' ); ?></a></p>
  </header>
</main>
<?php get_footer();
