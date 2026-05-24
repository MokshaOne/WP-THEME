<?php
/**
 * on1.agency · v4 · front-page.php
 * Composes the 9-section homepage from wp_options + Project CPT.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$id       = on1_get_identity();
$hero     = on1_get_hero();
$stats    = on1_get_stats();
$services = on1_get_services();
$pricing  = on1_get_pricing();
$quotes   = on1_get_quotes();
$faq      = on1_get_faq();
$brief    = on1_get_brief();
?>

<main id="main">

<!-- The default on1-agency homepage. Hidden when a skin is active
     (body[data-preset="X"] · see assets/css/skins.css). -->
<div class="on1-default">

<!-- ────────  HERO  ──────── -->
<header class="hero" id="top">
    <div class="hero__planet" aria-hidden="true"></div>

    <div class="hero__in">
        <div class="hero__lede-row">
            <div class="hero__lede">
                <strong><?php echo on1_em( $hero['manifesto_lead'] ); ?></strong>
                <?php echo wp_kses_post( $hero['manifesto_body'] ); ?>
                <div class="hero__signoff">
                    <?php echo esc_html( $hero['signoff_name'] ); ?>
                    <small><?php echo on1_em( $hero['signoff_role'] ); ?></small>
                </div>
            </div>
            <div></div>
        </div>

        <h1 class="hero__head">
            <span class="word-1"><?php echo on1_em( $hero['headline_line_1'] ); ?></span>
            <span class="word-2"><?php echo on1_em( $hero['headline_line_2'] ); ?></span>
        </h1>

        <div class="hero__bottom">
            <p class="hero__tag"><?php echo on1_em( $hero['tag_line'] ); ?></p>
            <a href="<?php echo esc_url( $hero['cta_url'] ); ?>" class="pill pill--accent">
                <?php echo esc_html( $hero['cta_label'] ); ?>
                <span class="pill__arrow">↗</span>
            </a>
        </div>
    </div>
</header>

<!-- ────────  STAT BAR  ──────── -->
<?php if ( on1_show( 'stats' ) && ! empty( $stats ) ) : ?>
<div class="stats-bar">
    <div class="stats-bar__in">
        <?php foreach ( $stats as $stat ) : ?>
            <div class="stat">
                <div class="stat__num"><?php echo wp_kses( $stat['num'] ?? '', [ 'span' => [ 'class' => [] ], 'small' => [ 'style' => [] ] ] ); ?></div>
                <div class="stat__label"><?php echo nl2br( esc_html( $stat['label'] ?? '' ) ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ────────  PRACTICE / SERVICES  ──────── -->
<?php if ( on1_show( 'services' ) && ! empty( $services ) ) : ?>
<section class="section" id="practice">
    <div class="section__in">
        <header class="section__head">
            <div>
                <p class="eyebrow">What we do</p>
                <h2 class="section__title">empowering studios to <em>look like themselves</em>.</h2>
            </div>
            <p class="section__sub">Four core services. Strategy is always included. You always work with one person.</p>
        </header>

        <ul class="services-list">
            <?php foreach ( $services as $i => $s ) : $num = sprintf( '%02d', $i + 1 ); ?>
                <li><a class="service-row" href="<?php echo esc_url( $s['url'] ?? '#brief' ); ?>">
                    <span class="service-row__num">↗ <?php echo esc_html( $num ); ?></span>
                    <div><h3 class="service-row__name"><?php echo on1_em( $s['name'] ?? '' ); ?></h3></div>
                    <p class="service-row__desc"><?php echo wp_kses( $s['desc'] ?? '', [ 'em' => [], 'strong' => [], 'br' => [] ] ); ?></p>
                    <span class="service-row__cta">→</span>
                </a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- ────────  FEATURED CASE + WORK GRID  ──────── -->
<?php if ( on1_show( 'work' ) ) :
    $featured = on1_get_featured_project();
    $others   = on1_get_other_projects( $featured ? $featured->ID : 0, 3 );
?>
<section class="section" id="work" style="padding-top: 0;">
    <div class="section__in">
        <header class="section__head">
            <div>
                <p class="eyebrow">Featured case</p>
                <h2 class="section__title">the work that <em>got</em> us here.</h2>
            </div>
            <p class="section__sub">Selected case studies from across the studio's range — from heritage projects to brutalist hubs.</p>
        </header>

        <?php if ( $featured ) :
            $f_lede = on1_project_meta( $featured->ID, 'lede' );
            $f_year = on1_project_meta( $featured->ID, 'year' );
            $f_for  = on1_project_meta( $featured->ID, 'for' );
            $f_url  = on1_project_meta( $featured->ID, 'live_url' );
            $f_tag  = on1_project_meta( $featured->ID, 'tag', 'Live' );
            $f_handle = on1_project_meta( $featured->ID, 'handle' );
            $f_meta = get_post_meta( $featured->ID, '_on1_meta', true );
            if ( ! is_array( $f_meta ) ) $f_meta = [];
        ?>
            <a class="featured-case" href="<?php echo esc_url( $f_url ?: get_permalink( $featured->ID ) ); ?>" <?php if ( $f_url ) echo 'target="_blank" rel="noopener"'; ?>>
                <div class="featured-case__text">
                    <p class="featured-case__num">Case 001 · <?php echo esc_html( $f_year ); ?></p>
                    <div>
                        <h3 class="featured-case__title"><?php echo on1_em( get_the_title( $featured->ID ) ); ?></h3>
                        <?php if ( $f_lede ) : ?>
                            <p class="featured-case__lede"><?php echo on1_em( $f_lede ); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ( ! empty( $f_meta ) ) : ?>
                    <dl class="featured-case__meta">
                        <?php foreach ( $f_meta as $row ) : ?>
                            <div><dt><?php echo esc_html( $row['label'] ?? '' ); ?></dt><dd><?php echo on1_em( $row['value'] ?? '' ); ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                    <?php endif; ?>
                    <span class="pill" style="align-self: flex-start;">View case <span class="pill__arrow">↗</span></span>
                </div>
                <div class="featured-case__media">
                    <?php if ( has_post_thumbnail( $featured->ID ) ) {
                        echo get_the_post_thumbnail( $featured->ID, 'on1-hero', [ 'alt' => esc_attr( get_the_title( $featured->ID ) ) ] );
                    } ?>
                    <?php if ( $f_tag ) : ?>
                        <span class="featured-case__tag"><?php echo esc_html( $f_tag ); ?></span>
                    <?php endif; ?>
                    <?php if ( $f_handle ) : ?>
                        <span class="featured-case__media-handle"><?php echo esc_html( $f_handle ); ?></span>
                    <?php endif; ?>
                </div>
            </a>
        <?php else : ?>
            <p style="font-family: var(--serif); font-style: italic; color: var(--mute); text-align: center; padding: 3rem 0;">
                No case studies yet. Add one from <em>Case studies → New case</em> in WP admin.
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $others ) ) : ?>
        <div class="work-grid">
            <?php $i = 2; foreach ( $others as $p ) :
                $p_lede = on1_project_meta( $p->ID, 'lede' );
                $p_year = on1_project_meta( $p->ID, 'year' );
                $p_url  = on1_project_meta( $p->ID, 'live_url' );
                $p_tag  = on1_project_meta( $p->ID, 'tag' );
                $p_handle = on1_project_meta( $p->ID, 'handle' );
                $alt = ( $i % 2 ) ? '' : ' case-card__media--alt';
            ?>
                <a class="case-card" href="<?php echo esc_url( $p_url ?: get_permalink( $p->ID ) ); ?>" <?php if ( $p_url ) echo 'target="_blank" rel="noopener"'; ?>>
                    <div class="case-card__media<?php echo $alt; ?>">
                        <?php if ( has_post_thumbnail( $p->ID ) ) echo get_the_post_thumbnail( $p->ID, 'on1-card', [ 'alt' => esc_attr( get_the_title( $p->ID ) ) ] ); ?>
                        <?php if ( $p_tag ) : ?>
                            <span class="case-card__tag"><?php echo esc_html( $p_tag ); ?></span>
                        <?php endif; ?>
                        <?php if ( $p_handle ) : ?>
                            <span class="case-card__media-handle"><?php echo esc_html( $p_handle ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="case-card__body">
                        <p class="case-card__nº">Case <?php echo sprintf( '%03d', $i ); ?> · <?php echo esc_html( $p_year ); ?></p>
                        <h3 class="case-card__title"><?php echo on1_em( get_the_title( $p->ID ) ); ?></h3>
                        <?php if ( $p_lede ) : ?>
                            <p class="case-card__lede"><?php echo on1_em( $p_lede ); ?></p>
                        <?php endif; ?>
                        <div class="case-card__foot">
                            <span class="live"><?php echo esc_html( $p_url ? parse_url( $p_url, PHP_URL_HOST ) : 'View case' ); ?></span>
                            <span class="case-card__arrow">↗</span>
                        </div>
                    </div>
                </a>
            <?php $i++; endforeach; ?>

            <a class="case-card case-card--cta" href="#brief">
                <div class="case-card__media" style="background: var(--bg);">
                    <span class="case-card__media-handle" style="color: var(--accent);">[ Open · <?php echo esc_html( $id['status'] ); ?> ]</span>
                </div>
                <div class="case-card__body">
                    <p class="case-card__nº">Next case · MMXXVI</p>
                    <h3 class="case-card__title">Your studio, here.</h3>
                    <p class="case-card__lede">One slot open this quarter. We design, we build, we ship.</p>
                    <div class="case-card__foot">
                        <span class="live" style="color: var(--accent);">Booking open</span>
                        <span class="case-card__arrow" style="background: var(--accent);">↗</span>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ────────  PRICING  ──────── -->
<?php if ( on1_show( 'pricing' ) && ! empty( $pricing ) ) : ?>
<section class="section section--alt" id="pricing">
    <div class="section__in">
        <header class="section__head">
            <div>
                <p class="eyebrow">Investment</p>
                <h2 class="section__title">prices that <em>don't hide</em>.</h2>
            </div>
            <p class="section__sub">Indicative ranges. Final scope agreed after the brief. Most projects land in the <em>premium</em> tier.</p>
        </header>

        <div class="pricing">
            <?php foreach ( $pricing as $tier ) :
                $is_feat = ( $tier['featured'] ?? '' ) === '1';
            ?>
                <div class="tier<?php echo $is_feat ? ' tier--featured' : ''; ?>">
                    <h3 class="tier__name"><?php echo on1_em( $tier['name'] ?? '' ); ?></h3>
                    <div class="tier__price"><?php echo on1_em( $tier['price'] ?? '' ); ?><?php if ( ! empty( $tier['period'] ) ) echo '<small>' . esc_html( $tier['period'] ) . '</small>'; ?></div>
                    <ul class="tier__features">
                        <?php foreach ( ( $tier['features'] ?? [] ) as $f ) : ?>
                            <li><?php echo on1_em( $f ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?php echo esc_url( $tier['url'] ?? '#brief' ); ?>" class="pill pill--sm tier__cta<?php echo $is_feat ? ' pill--accent' : ''; ?>">
                        <?php echo esc_html( $tier['cta'] ?? 'Choose' ); ?>
                        <span class="pill__arrow">↗</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ────────  TESTIMONIALS  ──────── -->
<?php if ( on1_show( 'quotes' ) && ! empty( $quotes ) ) : ?>
<section class="section">
    <div class="section__in">
        <header class="section__head">
            <div>
                <p class="eyebrow">What they say</p>
                <h2 class="section__title">studios we've <em>worked with</em>.</h2>
            </div>
            <p class="section__sub">A small portfolio. The work is the testimonial. Words from a few who let us speak.</p>
        </header>

        <div class="quotes">
            <?php foreach ( $quotes as $q ) : ?>
                <div class="quote">
                    <p class="quote__text"><?php echo esc_html( $q['text'] ?? '' ); ?></p>
                    <div class="quote__author">
                        <div class="quote__avatar"><?php echo esc_html( $q['initials'] ?? '·' ); ?></div>
                        <div class="quote__by"><?php echo esc_html( $q['author'] ?? '' ); ?><small><?php echo esc_html( $q['role'] ?? '' ); ?></small></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ────────  FAQ  ──────── -->
<?php if ( on1_show( 'faq' ) && ! empty( $faq ) ) : ?>
<section class="section section--alt" id="faq">
    <div class="section__in">
        <header class="section__head">
            <div>
                <p class="eyebrow">Common questions</p>
                <h2 class="section__title">things people <em>ask first</em>.</h2>
            </div>
            <p class="section__sub">If you have a question that isn't here, write it in the brief.</p>
        </header>

        <div class="faq">
            <?php foreach ( $faq as $i => $item ) :
                $open = ( $i === 0 ) ? ' is-open' : '';
            ?>
                <div class="faq__item<?php echo $open; ?>">
                    <span class="faq__num"><?php echo sprintf( '%02d', $i + 1 ); ?></span>
                    <h4 class="faq__q"><?php echo esc_html( $item['q'] ?? '' ); ?></h4>
                    <span class="faq__icon">+</span>
                    <p class="faq__a"><?php echo esc_html( $item['a'] ?? '' ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ────────  BRIEF CTA  ──────── -->
<section class="brief" id="brief">
    <div class="brief__in">
        <p class="eyebrow" style="justify-content: center; display: inline-flex;"><?php echo esc_html( $brief['eyebrow'] ?? 'Send a signal' ); ?></p>
        <h2 class="brief__lede">
            <?php echo on1_em( $brief['lede_line_1'] ?? '' ); ?><br>
            <?php echo on1_em( $brief['lede_line_2'] ?? '' ); ?>
        </h2>
        <p class="brief__sub"><?php echo esc_html( $brief['sub'] ?? '' ); ?></p>
        <div class="brief__cta-row">
            <?php if ( ! empty( $brief['primary_label'] ) ) : ?>
                <a href="<?php echo esc_url( $brief['primary_url'] ?? 'mailto:' . $id['email'] ); ?>" class="pill pill--accent">
                    <?php echo esc_html( $brief['primary_label'] ); ?>
                    <span class="pill__arrow">↗</span>
                </a>
            <?php endif; ?>
            <?php if ( ! empty( $brief['secondary_label'] ) ) : ?>
                <a href="<?php echo esc_url( $brief['secondary_url'] ?? '#' ); ?>" class="pill">
                    <?php echo esc_html( $brief['secondary_label'] ); ?>
                    <span class="pill__arrow">→</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

</div><!-- /.on1-default -->

<?php
// Render every enabled skin's full markup (hidden by default).
// CSS in skins.css shows one when body[data-preset="X"] is set.
// JS in skin-router.js wires the picker pills and in-skin nav.
on1_render_all_skins();
?>

</main>

<?php get_footer(); ?>
