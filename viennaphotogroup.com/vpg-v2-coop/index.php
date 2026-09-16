<?php
/** VPG v2 · index.php · blog index / fallback. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">
    <header class="vpg-page-hero">
        <span class="vpg-caps">— <?php echo is_search() ? esc_html__( 'Search results', 'vpg-v2' ) : esc_html__( 'Journal', 'vpg-v2' ); ?></span>
        <h1><?php echo is_search() ? sprintf( esc_html__( 'Results for "%s"', 'vpg-v2' ), esc_html( get_search_query() ) ) : esc_html__( 'Recent posts', 'vpg-v2' ); ?></h1>
    </header>
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap--narrow">
            <?php if ( have_posts() ) : ?>
                <ul style="list-style:none;padding:0;margin:0">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <li style="border-top:1px solid var(--vpg-rule);padding:1.4rem 0">
                            <a href="<?php the_permalink(); ?>" style="display:grid;grid-template-columns:120px 1fr;gap:1.5rem;align-items:baseline">
                                <span class="vpg-caps"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
                                <span><strong style="font-family:var(--vpg-font-display);font-size:var(--vpg-fs-xl);font-weight:600"><?php the_title(); ?></strong>
                                <?php if ( has_excerpt() ) echo '<br><span style="color:var(--vpg-muted);font-size:var(--vpg-fs-md)">' . esc_html( wp_trim_words( get_the_excerpt(), 22 ) ) . '</span>'; ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <div class="vpg-pagination" style="margin-top:3rem;text-align:center"><?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?></div>
            <?php else : ?>
                <div class="vpg-quote"><blockquote><?php esc_html_e( "Nothing here yet.", 'vpg-v2' ); ?></blockquote></div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php get_footer();
