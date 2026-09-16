<?php
/** VPG v2 · single.php · default single-post template. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
    <?php while ( have_posts() ) : the_post(); ?>
        <header class="vpg-page-hero">
            <span class="vpg-caps">— <?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_post_type() ); ?></span>
            <h1><?php the_title(); ?></h1>
            <?php if ( has_excerpt() ) : ?><p class="vpg-lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        </header>
        <?php if ( has_post_thumbnail() ) : ?>
            <figure style="max-width:var(--vpg-max-magazine);margin:2rem auto;padding:0 var(--vpg-sp-5)">
                <?php the_post_thumbnail( 'vpg-hero', [ 'style' => 'width:100%;border-radius:var(--vpg-radius-lg)' ] ); ?>
            </figure>
        <?php endif; ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap--prose">
                <div class="vpg-prose"><?php the_content(); ?></div>
            </div>
        </section>
        <?php if ( comments_open() || get_comments_number() ) : ?>
            <section class="vpg-section vpg-section--surface"><div class="vpg-wrap--prose"><?php comments_template(); ?></div></section>
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php get_footer();
