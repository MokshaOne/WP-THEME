<?php if ( ! defined( 'ABSPATH' ) ) exit; get_header(); ?>
<main id="vpg-main">
    <header class="vpg-page-hero" style="padding-top:8rem;padding-bottom:8rem">
        <span class="vpg-caps">— <?php esc_html_e( '404 · Not found', 'vpg-v2' ); ?></span>
        <h1><?php esc_html_e( 'Lost in the', 'vpg-v2' ); ?> <em><?php esc_html_e( 'archive', 'vpg-v2' ); ?></em>.</h1>
        <p class="vpg-lede"><?php esc_html_e( "The page you're looking for isn't here. Try the front page, the magazine archive, or search.", 'vpg-v2' ); ?></p>
        <p style="margin-top:2rem"><a class="vpg-btn" href="<?php echo esc_url( home_url('/') ); ?>"><?php esc_html_e( 'Back home', 'vpg-v2' ); ?></a></p>
    </header>
</main>
<?php get_footer();
