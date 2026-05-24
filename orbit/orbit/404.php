<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="dk-main">
  <header class="dk-page-hero">
    <p class="dk-eyebrow">— 404 · Not Found</p>
    <h1 class="dk-page-head">Lost in <em>orbit</em>.</h1>
    <p class="dk-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'ORBIT' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'ORBIT' ); ?></a></p>
  </header>
</main>
<?php get_footer();
