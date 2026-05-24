<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gd-main">
  <header class="gd-page-hero">
    <p class="gd-eyebrow">— 404 · Not Found</p>
    <h1 class="gd-page-head">Lost in <em>orbit</em>.</h1>
    <p class="gd-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'REVOLT' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'REVOLT' ); ?></a></p>
  </header>
</main>
<?php get_footer();
