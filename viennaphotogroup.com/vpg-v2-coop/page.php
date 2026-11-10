<?php
/** VPG v2 · page.php · default single-page template. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="vpg-page-hero">
            <span class="vpg-caps">— <?php esc_html_e( 'Page', 'vpg-v2' ); ?></span>
            <h1><?php the_title(); ?></h1>
        </header>
        <?php if ( has_post_thumbnail() ) : ?>
            <figure style="max-width:var(--vpg-max-magazine);margin:2rem auto;padding:0 var(--vpg-sp-5)">
                <?php the_post_thumbnail( 'vpg-hero', [ 'style' => 'width:100%;border-radius:var(--vpg-radius-lg)' ] ); ?>
            </figure>
        <?php endif; ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap--prose">
                <div class="vpg-prose"><?php the_content(); wp_link_pages(); ?></div>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php get_footer();
