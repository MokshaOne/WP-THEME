<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="px-main">
  <header class="px-page-hero">
    <p class="px-eyebrow">— 404 · Not Found</p>
    <h1 class="px-page-head">Lost in <em>orbit</em>.</h1>
    <p class="px-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'ECLIPSE' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'ECLIPSE' ); ?></a></p>
  </header>
</main>
<?php get_footer();
