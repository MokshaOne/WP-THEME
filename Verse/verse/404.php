<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="main" class="kv-main">
  <header class="kv-page-hero">
    <p class="kv-eyebrow">— 404 · Not Found</p>
    <h1 class="kv-page-head">Lost.</h1>
    <p class="kv-page-lede"><?php esc_html_e( "The page you're looking for isn't here.", 'VERSE' ); ?></p>
    <p style="margin-top:2rem"><a href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( '← Back home', 'VERSE' ); ?></a></p>
  </header>
</main>
<?php get_footer();
