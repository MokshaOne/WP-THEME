<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="mx-main">
  <header class="mx-page-hero">
    <p class="mx-eyebrow">— 404 · Not Found</p>
    <h1 class="mx-page-head">Lost in <em>orbit</em>.</h1>
    <p class="mx-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'AURORA' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'AURORA' ); ?></a></p>
  </header>
</main>
<?php get_footer();
