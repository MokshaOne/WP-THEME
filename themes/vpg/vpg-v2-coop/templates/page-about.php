<?php
/**
 * Template Name: About / Editorial
 *
 * The VPG origin story · genesis → transition → pivot · plus the
 * architectural principles that define the current platform.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> About</span>
    <h1>We archive the city.<br>We document the <em>light</em>.</h1>
    <p class="vpg-lede">Vienna Photo Group · founded December 2019 · a decentralised, data-driven reference hub for photographers shooting Wien.</p>
</header>

<span class="vpg-asterism vpg-asterism--break"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— Chapter 1</p>
                <h2>Origin &amp; <em>evolution</em>.</h2>
            </div>
            <div class="vpg-section-head__meta">2019 → 2024</div>
        </div>

        <div class="vpg-grid vpg-grid--3">
            <article class="vpg-card">
                <span class="vpg-caps">i.  ·  December 2019</span>
                <h3 style="margin-top:.6rem">The Genesis</h3>
                <p class="vpg-card__lede">VPG was founded by a collective of local photographers. Initial phase: an algorithmic feature page on Instagram, curating visual work within the city's creative ecosystem.</p>
            </article>

            <article class="vpg-card">
                <span class="vpg-caps">ii.  ·  Summer 2023</span>
                <h3 style="margin-top:.6rem">The Transition</h3>
                <p class="vpg-card__lede">First iteration of the dedicated VPG web platform — a static bridge hosting tutorials and archiving the imagery pulled from the Instagram network. The first step off borrowed land.</p>
            </article>

            <article class="vpg-card vpg-card--featured">
                <span class="vpg-caps">iii.  ·  The pivot</span>
                <h3 style="margin-top:.6rem">Structural Restructure</h3>
                <p class="vpg-card__lede">Meta API changes exposed the fragility of building on borrowed infrastructure. A complete philosophical and technical restructuring followed. VPG became an independent, decentralised, data-driven community platform and location index.</p>
            </article>
        </div>
    </div>
</section>

<span class="vpg-asterism vpg-asterism--break"></span>

<section class="vpg-section vpg-section--surface">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— Chapter 2</p>
                <h2>System &amp; <em>architecture</em>.</h2>
            </div>
            <div class="vpg-section-head__meta">Read-only · decentralised</div>
        </div>

        <p class="vpg-lede" style="max-width:60ch;margin-bottom:var(--vpg-sp-7)">
            VPG functions as a highly structured, decentralised repository. Custom-coded · clean architecture · advanced data-mapping and location intelligence · optimised for seamless utility.
        </p>

        <div class="vpg-grid vpg-grid--3">
            <article class="vpg-card">
                <span class="vpg-chip vpg-chip--loc"><span class="vpg-chip__dot"></span> Location guide</span>
                <h3 style="margin-top:.8rem">Interactive index</h3>
                <p class="vpg-card__lede">A fully integrated, free interactive map engine. Indexes shooting locations, visual structures, and environmental data across the city.</p>
                <p style="margin-top:1rem"><a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>">Open map <span class="vpg-btn__arrow">→</span></a></p>
            </article>

            <article class="vpg-card">
                <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> Frontend submission</span>
                <h3 style="margin-top:.8rem">Active creators</h3>
                <p class="vpg-card__lede">Members of the inner circle contribute directly to the matrix — feeding raw data, coordinates and structural checks into the database. The index is built by the people who shoot it.</p>
                <p style="margin-top:1rem"><a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="<?php echo esc_url( home_url('/join/') ); ?>">Become a contributor <span class="vpg-btn__arrow">→</span></a></p>
            </article>

            <article class="vpg-card">
                <span class="vpg-chip"><span class="vpg-chip__dot"></span> Public read-only</span>
                <h3 style="margin-top:.8rem">Uncompromised hub</h3>
                <p class="vpg-card__lede">To protect the integrity and depth of the index, the frontend remains entirely read-only for the public — serving as a pure, uncompromised reference hub. No noise, no submissions queue, no spam.</p>
            </article>
        </div>
    </div>
</section>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-quote vpg-reveal" style="max-width:62ch">
        <span class="vpg-asterism vpg-asterism--mark"></span>
        <p class="vpg-caps">— Chapter 3 · The Philosophy</p>
        <blockquote style="font-size:clamp(24px,3.4vw,42px);margin-top:1rem">
            Visual mastery requires <span class="vpg-mark">execution</span>, not validation.
        </blockquote>
        <p style="font-family:var(--vpg-font-italic);font-style:italic;font-size:var(--vpg-fs-xl);line-height:1.55;color:var(--vpg-ink-mid);margin-top:var(--vpg-sp-5);max-width:48ch;margin-left:auto;margin-right:auto">
            The Vienna Photo Group exists to provide local creators with the technical foundation and geographical intelligence needed to plan, execute, and archive their work without friction.
        </p>
        <div class="vpg-asterism vpg-asterism--break"></div>
        <blockquote style="font-family:var(--vpg-font-display);font-style:normal;font-size:clamp(20px,2.6vw,32px);font-weight:var(--vpg-fw-bold);line-height:1.2;letter-spacing:var(--vpg-tr-tight);text-align:center">
            We <em>archive</em> the city.<br>
            We <em>document</em> the light.<br>
            We <em>eliminate</em> the noise.
        </blockquote>
    </div>
</section>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    $extra = trim( strip_shortcodes( wp_strip_all_tags( get_the_content(), true ) ) );
    if ( $extra ) : ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap--prose">
                <p class="vpg-caps" style="margin-bottom:var(--vpg-sp-4)">— Editor&rsquo;s note</p>
                <div class="vpg-prose"><?php the_content(); ?></div>
            </div>
        </section>
<?php endif; endwhile; endif; ?>

<section class="vpg-section vpg-section--surface vpg-section--loose">
    <div class="vpg-wrap--narrow" style="text-align:center">
        <p class="vpg-caps">— Inner circle</p>
        <h2 style="font-size:var(--vpg-fs-h2);margin:1rem 0 1.5rem"><span class="vpg-bar">Feed the matrix.</span></h2>
        <p class="vpg-lede" style="margin:0 auto 2.5rem">
            Active creators contribute coordinates, structural checks and visual notes. The platform stays uncompromised because the people who feed it actually walk the city.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a class="vpg-btn vpg-btn--accent vpg-btn--lg" href="<?php echo esc_url( home_url('/join/') ); ?>">Apply to contribute<span class="vpg-btn__arrow">→</span></a>
            <a class="vpg-btn vpg-btn--ghost vpg-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>">Browse the index</a>
        </div>
    </div>
</section>

</main>

<?php get_footer();
