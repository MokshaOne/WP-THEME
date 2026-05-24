<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gw-main">
  <header class="gw-page-hero">
    <p class="gw-eyebrow">— 404 · Not Found</p>
    <h1 class="gw-page-head">Lost in <em>orbit</em>.</h1>
    <p class="gw-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'BAUHAUS' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'BAUHAUS' ); ?></a></p>
  </header>
</main>
<?php get_footer();
