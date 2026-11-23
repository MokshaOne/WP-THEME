<?php
/**
 * VPG v2 — front-page.php · editorial-magazine homepage.
 *
 * Sections, in order of appearance:
 *   1. Hero · big serif headline + lede + dual CTA
 *   2. Marquee strip · scrolling category names
 *   3. Current issue · cover + lede + "Read this issue"
 *   4. Magazine back-catalogue · oversized grid of recent issues
 *   5. Three pillars · Events / Locations / Studios with glow auras
 *   6. Buying-guide preview · 3 top-scored reviews
 *   7. Editorial quote
 *   8. Stats row
 *   9. Join CTA · with oxblood bar accent
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$current_issue = new WP_Query( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ] );
$latest_issues = new WP_Query( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 5, 'offset' => 1, 'orderby' => 'date', 'order' => 'DESC' ] );
$top_reviews   = new WP_Query( [ 'post_type' => 'vpg_review',   'posts_per_page' => 3, 'orderby' => 'date', 'order' => 'DESC' ] );
?>

<main id="vpg-main">

<!-- ────────  HERO  ──────── -->
<section class="vpg-hero">
    <div class="vpg-hero__in">
        <p class="vpg-hero__eyebrow">▼ Wien · Community since 2018</p>
        <h1 class="vpg-hero__head">
            The <em>quiet</em><br>
            magazine for<br>
            photographers<br>
            who <em>look</em>.
        </h1>
        <p class="vpg-lede" style="margin-bottom:2rem">
            Vienna Photo Group is a member-run editorial magazine. Locations, studios, shops, tutorials, reviews — written by photographers, for photographers in Wien and the diaspora.
        </p>
        <div class="vpg-hero__row">
            <a class="vpg-btn vpg-btn--lg" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Join the community', 'vpg-v2' ); ?><span class="vpg-btn__arrow">→</span></a>
            <a class="vpg-btn vpg-btn--ghost vpg-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Read the magazine', 'vpg-v2' ); ?></a>
        </div>
    </div>
</section>

<!-- ────────  MARQUEE  ──────── -->
<div class="vpg-marquee" aria-hidden="true">
    <div class="vpg-marquee__track">
        <?php for ( $i = 0; $i < 2; $i++ ) : ?>
            <span>Studios</span><span class="vpg-accent">/</span>
            <span class="vpg-accent">Locations.</span><span class="vpg-accent">/</span>
            <span>Shops</span><span class="vpg-accent">/</span>
            <span class="vpg-accent">Buying guide.</span><span class="vpg-accent">/</span>
            <span>Tutorials</span><span class="vpg-accent">/</span>
            <span class="vpg-accent">Events.</span><span class="vpg-accent">/</span>
            <span>Magazine</span><span class="vpg-accent">/</span>
        <?php endfor; ?>
    </div>
</div>

<!-- ────────  CURRENT ISSUE  ──────── -->
<?php if ( $current_issue->have_posts() ) : $current_issue->the_post(); ?>
<section class="vpg-section vpg-section--surface">
    <div class="vpg-wrap--mag">
        <span class="vpg-asterism vpg-asterism--mark"></span>
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php esc_html_e( 'Current issue', 'vpg-v2' ); ?> · <?php echo esc_html( get_the_date( 'F Y' ) ); ?></p>
                <h2><?php esc_html_e( 'This', 'vpg-v2' ); ?> <em><?php esc_html_e( 'month', 'vpg-v2' ); ?></em>.</h2>
            </div>
            <div class="vpg-section-head__meta">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'All issues', 'vpg-v2' ); ?> →</a>
            </div>
        </div>

        <article class="vpg-split">
            <a href="<?php the_permalink(); ?>" style="display:block">
                <div class="vpg-card__media vpg-card__media--cover <?php echo has_post_thumbnail() ? '' : 'vpg-card__media--placeholder'; ?>">
                    <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'vpg-cover' ); ?>
                </div>
            </a>
            <div>
                <p class="vpg-caps">— <?php
                    $issue_no = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
                    echo esc_html( $issue_no ?: ( 'Issue · ' . get_the_date( 'F Y' ) ) );
                ?></p>
                <h2 style="margin:1rem 0 1.5rem;font-size:var(--vpg-fs-h2)"><?php the_title(); ?></h2>
                <p class="vpg-lede" style="margin-bottom:1.5rem"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 38 ) ); ?></p>

                <?php
                $articles = vpg_get_articles( get_the_ID() );
                if ( $articles ) :
                ?>
                    <ul class="vpg-caps" style="list-style:none;padding:0;margin:0 0 2rem;display:grid;gap:.4rem">
                        <?php foreach ( array_slice( $articles, 0, 4 ) as $i => $a ) : ?>
                            <li style="display:grid;grid-template-columns:30px 1fr;gap:.8rem;font-size:var(--vpg-fs-md);text-transform:none;letter-spacing:0;color:var(--vpg-ink-mid)">
                                <span style="font-family:var(--vpg-font-mono);color:var(--vpg-accent);font-size:11px;letter-spacing:.12em">#<?php printf( '%02d', $i + 1 ); ?></span>
                                <span><strong><?php echo esc_html( $a['title'] ?? '' ); ?></strong><?php if ( ! empty( $a['author'] ) ) echo ' · <em>' . esc_html( $a['author'] ) . '</em>'; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="vpg-hero__row">
                    <a class="vpg-btn vpg-btn--accent" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read this issue', 'vpg-v2' ); ?><span class="vpg-btn__arrow">→</span></a>
                    <?php $pdf = get_post_meta( get_the_ID(), '_vpg_pdf_url', true ); if ( $pdf ) : ?>
                        <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( $pdf ); ?>" target="_blank">PDF ↓</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<!-- ────────  BACK CATALOGUE  ──────── -->
<?php if ( $latest_issues->have_posts() ) : ?>
<section class="vpg-section">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php esc_html_e( 'Back catalogue', 'vpg-v2' ); ?></p>
                <h2><?php esc_html_e( 'Recent', 'vpg-v2' ); ?> <em><?php esc_html_e( 'issues', 'vpg-v2' ); ?></em>.</h2>
            </div>
            <div class="vpg-section-head__meta">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Browse all', 'vpg-v2' ); ?> →</a>
            </div>
        </div>

        <div class="vpg-mag-grid">
            <?php $idx = 0; while ( $latest_issues->have_posts() ) : $latest_issues->the_post(); $idx++; ?>
                <article class="vpg-card<?php echo $idx === 1 ? ' vpg-card--featured' : ''; ?>">
                    <a href="<?php the_permalink(); ?>">
                        <div class="vpg-card__media vpg-card__media--cover <?php echo has_post_thumbnail() ? '' : 'vpg-card__media--placeholder'; ?>">
                            <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'vpg-card' ); ?>
                        </div>
                        <p class="vpg-card__meta"><?php echo esc_html( get_the_date( 'F Y' ) ); ?> · ISSUE</p>
                        <h3><?php the_title(); ?></h3>
                        <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<span class="vpg-asterism vpg-asterism--break"></span>

<!-- ────────  THREE PILLARS  ──────── -->
<section class="vpg-section vpg-section--grid">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php esc_html_e( 'Pillars', 'vpg-v2' ); ?></p>
                <h2><?php esc_html_e( 'Three', 'vpg-v2' ); ?> <em><?php esc_html_e( 'archives', 'vpg-v2' ); ?></em>.</h2>
            </div>
            <div class="vpg-section-head__meta"><?php esc_html_e( 'Updated weekly', 'vpg-v2' ); ?></div>
        </div>
        <div class="vpg-grid vpg-grid--3">
            <?php
            $pillars = [
                [ 'vpg_event',    'event',   __( 'Events',    'vpg-v2' ), __( 'Workshops, openings, walks, and meetups across Vienna.',                'vpg-v2' ) ],
                [ 'vpg_location', 'loc',     __( 'Locations', 'vpg-v2' ), __( 'A member-curated map of shooting locations, light, and access notes.', 'vpg-v2' ) ],
                [ 'vpg_studio',   'studio',  __( 'Studios',   'vpg-v2' ), __( 'Rental studios, daylight floors, darkrooms and members who share.',    'vpg-v2' ) ],
            ];
            foreach ( $pillars as $p ) : list( $type, $cls, $label, $desc ) = $p; $count = (int) ( wp_count_posts( $type )->publish ?? 0 ); ?>
                <a class="vpg-card" href="<?php echo esc_url( get_post_type_archive_link( $type ) ); ?>">
                    <div class="vpg-card__media vpg-card__media--placeholder vpg-card__media--landscape"></div>
                    <span class="vpg-chip vpg-chip--<?php echo esc_attr( $cls ); ?>"><span class="vpg-chip__dot"></span> <?php echo esc_html( $label ); ?></span>
                    <h3 style="margin-top:1rem"><?php
                        if ( $type === 'vpg_event' ) printf( esc_html__( '%d upcoming.', 'vpg-v2' ), $count );
                        elseif ( $type === 'vpg_location' ) printf( esc_html__( '%d places.', 'vpg-v2' ), $count );
                        else printf( esc_html__( '%d studios.', 'vpg-v2' ), $count );
                    ?></h3>
                    <p class="vpg-card__lede"><?php echo esc_html( $desc ); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ────────  BUYING GUIDE TEASER  ──────── -->
<?php if ( $top_reviews->have_posts() ) : ?>
<section class="vpg-section">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php esc_html_e( 'Buying guide', 'vpg-v2' ); ?></p>
                <h2><?php esc_html_e( 'Gear,', 'vpg-v2' ); ?> <em><?php esc_html_e( 'reviewed', 'vpg-v2' ); ?></em>.</h2>
            </div>
            <div class="vpg-section-head__meta">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'vpg_review' ) ); ?>"><?php esc_html_e( 'All reviews', 'vpg-v2' ); ?> →</a>
            </div>
        </div>
        <div class="vpg-grid vpg-grid--3">
            <?php while ( $top_reviews->have_posts() ) : $top_reviews->the_post();
                $s1 = (float) vpg_field( 'score_design' );
                $s2 = (float) vpg_field( 'score_performance' );
                $s3 = (float) vpg_field( 'score_price' );
                $score = ( $s1 + $s2 + $s3 ) ? round( ( $s1 + $s2 + $s3 ) / 3, 1 ) : 0;
            ?>
                <article class="vpg-card">
                    <a href="<?php the_permalink(); ?>">
                        <div class="vpg-card__media vpg-card__media--landscape <?php echo has_post_thumbnail() ? '' : 'vpg-card__media--placeholder'; ?>">
                            <?php if ( has_post_thumbnail() ) the_post_thumbnail( 'vpg-card' ); ?>
                            <?php if ( $score ) : ?>
                                <span class="vpg-score" style="position:absolute;top:.8rem;right:.8rem"><?php echo esc_html( $score ); ?><small>/10</small></span>
                            <?php endif; ?>
                        </div>
                        <span class="vpg-chip vpg-chip--review"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Review', 'vpg-v2' ); ?></span>
                        <h3 style="margin-top:.8rem"><?php the_title(); ?></h3>
                        <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; wp_reset_postdata(); ?>

<!-- ────────  EDITORIAL QUOTE  ──────── -->
<section class="vpg-section vpg-section--tight">
    <div class="vpg-quote vpg-reveal">
        <blockquote>
            The point of a community magazine is not to be loud, but to be patient — to <span class="vpg-mark">notice</span> the city slowly enough that we know when something is worth photographing.
        </blockquote>
        <cite>— <?php esc_html_e( "Editor's letter · 2026 · Vol. III", 'vpg-v2' ); ?></cite>
    </div>
</section>

<!-- ────────  STATS  ──────── -->
<section class="vpg-section">
    <div class="vpg-wrap">
        <div class="vpg-stats">
            <div class="vpg-stat">
                <div class="vpg-stat__num"><em><?php echo esc_html( get_theme_mod( 'vpg_since', '2018' ) ); ?></em></div>
                <div class="vpg-stat__label"><?php esc_html_e( 'Founded', 'vpg-v2' ); ?></div>
            </div>
            <div class="vpg-stat">
                <div class="vpg-stat__num"><?php echo (int) ( wp_count_posts( 'vpg_location' )->publish ?? 0 ); ?><sup>+</sup></div>
                <div class="vpg-stat__label"><?php esc_html_e( 'Locations mapped', 'vpg-v2' ); ?></div>
            </div>
            <div class="vpg-stat">
                <div class="vpg-stat__num"><?php echo (int) ( wp_count_posts( 'vpg_magazine' )->publish ?? 0 ); ?></div>
                <div class="vpg-stat__label"><?php esc_html_e( 'Issues shipped', 'vpg-v2' ); ?></div>
            </div>
            <div class="vpg-stat">
                <?php
                // Cache count_users() · expensive on big sites
                $u_count = get_transient( 'vpg_user_count' );
                if ( false === $u_count ) {
                    $u = count_users();
                    $u_count = (int) ( $u['total_users'] ?? 0 );
                    set_transient( 'vpg_user_count', $u_count, HOUR_IN_SECONDS );
                }
                ?>
                <div class="vpg-stat__num"><?php echo esc_html( $u_count ); ?><sup>+</sup></div>
                <div class="vpg-stat__label"><?php esc_html_e( 'Members', 'vpg-v2' ); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- ────────  JOIN CTA  ──────── -->
<section class="vpg-section vpg-section--surface vpg-section--loose">
    <div class="vpg-wrap--narrow vpg-reveal" style="text-align:center">
        <p class="vpg-caps">— <?php esc_html_e( 'Membership', 'vpg-v2' ); ?></p>
        <h2 style="font-size:var(--vpg-fs-h2);margin:1rem 0 1.5rem"><span class="vpg-bar"><?php esc_html_e( 'Join the studio.', 'vpg-v2' ); ?></span></h2>
        <p class="vpg-lede" style="margin:0 auto 2.5rem">
            <?php esc_html_e( 'Members get the monthly magazine as a PDF, submission rights to the location map, event discounts and access to the studio-share directory. €60 a year. Forever-keepable.', 'vpg-v2' ); ?>
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a class="vpg-btn vpg-btn--accent vpg-btn--lg" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Become a member', 'vpg-v2' ); ?><span class="vpg-btn__arrow">→</span></a>
            <a class="vpg-btn vpg-btn--ghost vpg-btn--lg" href="<?php echo esc_url( home_url('/about/') ); ?>"><?php esc_html_e( 'What we publish', 'vpg-v2' ); ?></a>
        </div>
    </div>
</section>

</main>

<?php get_footer();
