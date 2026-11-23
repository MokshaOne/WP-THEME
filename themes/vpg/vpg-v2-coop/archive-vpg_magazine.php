<?php
/**
 * VPG v2 — magazine archive · cover wall.
 *
 * Renders all published issues as a grid of covers. The most recent issue
 * gets the oversized featured slot at top-left. Each card shows issue number,
 * date, title, lede, and a PDF-download arrow when available.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged  = max( 1, get_query_var( 'paged' ) );
$issues = new WP_Query( [
    'post_type'      => 'vpg_magazine',
    'posts_per_page' => 12,
    'post_status'    => 'publish',
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
] );
$total = (int) $issues->found_posts;
?>

<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--mag"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Magazine', 'vpg-v2' ); ?></span>
    <h1><?php esc_html_e( 'Every', 'vpg-v2' ); ?> <em><?php esc_html_e( 'issue', 'vpg-v2' ); ?></em>.</h1>
    <p class="vpg-lede"><?php esc_html_e( 'The monthly Vienna Photo Group magazine. Members get every issue as a print-ready PDF, free, forever-keepable. Older issues are open-access here.', 'vpg-v2' ); ?></p>
    <p class="vpg-caps" style="margin-top:1.5rem"><?php printf( esc_html( _n( '%d issue published', '%d issues published', $total, 'vpg-v2' ) ), $total ); ?></p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <?php if ( $issues->have_posts() ) : ?>
            <div class="vpg-mag-wall">
                <?php $idx = 0; while ( $issues->have_posts() ) : $issues->the_post(); $idx++;
                    $issue_no = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
                    $issue_dt = get_post_meta( get_the_ID(), '_vpg_issue_date', true );
                    $pdf_url  = get_post_meta( get_the_ID(), '_vpg_pdf_url', true );
                ?>
                    <article class="vpg-card vpg-mag-cover-card<?php echo $idx === 1 ? ' vpg-mag-cover-card--lead vpg-card--featured' : ''; ?>">
                        <a href="<?php the_permalink(); ?>">
                            <div class="vpg-card__media vpg-card__media--cover <?php echo has_post_thumbnail() ? '' : 'vpg-card__media--placeholder'; ?>">
                                <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'vpg-cover' ); ?>
                                <?php if ( $idx === 1 ) : ?>
                                    <span style="position:absolute;top:.8rem;left:.8rem;background:var(--vpg-accent);color:var(--vpg-bg);padding:.3rem .7rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:var(--vpg-fs-xs);letter-spacing:.2em;text-transform:uppercase"><?php esc_html_e( 'Current', 'vpg-v2' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="vpg-card__meta">
                                <?php echo esc_html( $issue_no ?: get_the_date( 'F Y' ) ); ?>
                                <?php if ( $issue_dt ) echo ' · ' . esc_html( $issue_dt ); ?>
                            </p>
                            <h3><?php the_title(); ?></h3>
                            <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $idx === 1 ? 32 : 18 ) ); ?></p>
                        </a>
                        <?php if ( $pdf_url ) : ?>
                            <p style="margin-top:1rem"><a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank">PDF ↓</a></p>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>

            <nav class="vpg-pagination" style="margin-top:4rem;text-align:center">
                <?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?>
            </nav>
        <?php else : ?>
            <div class="vpg-quote" style="margin:6rem auto">
                <span class="vpg-asterism vpg-asterism--mark"></span>
                <blockquote><?php esc_html_e( 'The first issue is being assembled.', 'vpg-v2' ); ?></blockquote>
                <cite><?php esc_html_e( 'Subscribe to be notified when it ships.', 'vpg-v2' ); ?></cite>
            </div>
        <?php endif; ?>
    </div>
</section>

</main>

<?php wp_reset_postdata(); get_footer();
