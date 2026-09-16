<?php
/** VPG v2 · single · Review */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = '' ? vpg_field( '' ) : '';
?>

    <header class="vpg-page-hero">
        <span class="vpg-chip vpg-chip--review"><span class="vpg-chip__dot"></span> Review</span>
        <h1><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?><p class="vpg-lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
        <?php if ( $meta ) : ?>
            <p class="vpg-caps" style="margin-top:1.5rem"> · <?php echo esc_html( $meta ); ?></p>
        <?php endif; ?>
            <?php
            $s1 = (float) vpg_field( 'score_design' );
            $s2 = (float) vpg_field( 'score_performance' );
            $s3 = (float) vpg_field( 'score_price' );
            $score = ( $s1 + $s2 + $s3 ) ? round( ( $s1 + $s2 + $s3 ) / 3, 1 ) : 0;
            ?>
            <?php if ( $score ) : ?>
                <div style="display:flex;gap:1rem;justify-content:center;align-items:baseline;margin-top:2rem;flex-wrap:wrap">
                    <span class="vpg-score vpg-score--featured"><?php echo esc_html( $score ); ?><small>/10</small></span>
                    <span class="vpg-caps" style="color:var(--vpg-muted)"><?php echo esc_html__( 'Design', 'vpg-v2' ); ?>: <?php echo esc_html( $s1 ); ?></span>
                    <span class="vpg-caps" style="color:var(--vpg-muted)"><?php echo esc_html__( 'Performance', 'vpg-v2' ); ?>: <?php echo esc_html( $s2 ); ?></span>
                    <span class="vpg-caps" style="color:var(--vpg-muted)"><?php echo esc_html__( 'Price', 'vpg-v2' ); ?>: <?php echo esc_html( $s3 ); ?></span>
                </div>
            <?php endif; ?>
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



    <section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
        <div class="vpg-wrap--narrow">
            <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_review' ) ); ?>"><?php esc_html_e( 'All reviews', 'vpg-v2' ); ?> →</a>
        </div>
    </section>

<?php endwhile; ?>
</main>

<?php get_footer();
