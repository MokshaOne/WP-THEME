<?php
/** VPG v2 · archive · Events */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged = max( 1, get_query_var( 'paged' ) );
$q = new WP_Query( [ 'post_type' => 'vpg_event', 'posts_per_page' => 12, 'post_status' => 'publish', 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC' ] );
$total = (int) $q->found_posts;
?>

<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--event"><span class="vpg-chip__dot"></span> Events</span>
    <h1>Photo <em>events</em>.</h1>
    <p class="vpg-lede">Workshops, openings, walks and meetups for photographers in Vienna. Members get early access and discounts.</p>
    <p class="vpg-caps" style="margin-top:1.5rem"><?php printf( esc_html__( '%d entries', 'vpg-v2' ), $total ); ?> · Schedule · workshops · walks</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">

        <?php if ( $q->have_posts() ) : ?>
            <div class="vpg-grid vpg-grid--auto">
                <?php while ( $q->have_posts() ) : $q->the_post();
                    $thumb = get_the_post_thumbnail_url( get_the_ID(), 'vpg-card' );
                    $meta_val = vpg_field( '_vpg_event_date' );
                ?>
                    <article class="vpg-card">
                        <a href="<?php the_permalink(); ?>">
                            <div class="vpg-card__media vpg-card__media--landscape <?php echo $thumb ? '' : 'vpg-card__media--placeholder'; ?>">
                                <?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"><?php endif; ?>
                            </div>
                            <span class="vpg-chip vpg-chip--event" style="margin-bottom:.8rem"><span class="vpg-chip__dot"></span> Events</span>
                            <h3><?php the_title(); ?></h3>
                            <?php if ( has_excerpt() ) : ?>
                                <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                            <?php endif; ?>
                            <div class="vpg-card__meta" style="display:flex;justify-content:space-between;margin-top:1.2rem;border-top:1px solid var(--vpg-rule);padding-top:1rem">
                                <span><?php echo $meta_val ? esc_html( 'Date · ' . $meta_val ) : esc_html( get_the_date( 'M Y' ) ); ?></span>
                                <span style="font-family:var(--vpg-font-display);color:var(--vpg-accent);font-size:var(--vpg-fs-lg)">→</span>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>

            <nav class="vpg-pagination" style="margin-top:4rem;text-align:center">
                <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
            </nav>

        <?php else : ?>
            <div class="vpg-quote" style="margin:6rem auto">
                <span class="vpg-asterism vpg-asterism--mark"></span>
                <blockquote>No events scheduled.</blockquote>
                <cite><?php esc_html_e( 'Members can submit from the dashboard.', 'vpg-v2' ); ?></cite>
            </div>
        <?php endif; ?>

    </div>
</section>

</main>

<?php wp_reset_postdata(); get_footer();
