<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="gz-main">
  <header class="gz-page-hero">
    <p class="gz-eyebrow">— 404 · Not Found</p>
    <h1 class="gz-page-head">Lost in <em>orbit</em>.</h1>
    <p class="gz-page-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page or search.", 'MONOLITH' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'MONOLITH' ); ?></a></p>
  </header>
</main>
<?php get_footer();
