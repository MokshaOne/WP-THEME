<?php
/** Template Name: Editorial team */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> Team</span>
    <h1>Who runs the <em>magazine</em>.</h1>
    <p class="vpg-lede">A small editorial circle. Member-run, no hierarchy beyond the four roles required to ship an issue every month.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <div class="vpg-grid vpg-grid--2">

            <article class="vpg-card">
                <p class="vpg-caps">— Founding · Photography</p>
                <h3 style="margin-top:.4rem">Nishuthan Raveenthiran</h3>
                <p class="vpg-card__lede">Photographer based in Wien. Co-produced the VPG v2 rebuild. Operates raveenthiran.com.</p>
                <p style="margin-top:1rem"><a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="https://raveenthiran.com" target="_blank">raveenthiran.com →</a></p>
            </article>

            <article class="vpg-card">
                <p class="vpg-caps">— Founding · Platform &amp; editorial systems</p>
                <h3 style="margin-top:.4rem">on1.agency</h3>
                <p class="vpg-card__lede">One-person digital atelier in Wien. Built the v2 platform · maintains the WordPress stack, magazine editor and PDF pipeline.</p>
                <p style="margin-top:1rem"><a class="vpg-btn vpg-btn--ghost vpg-btn--sm" href="https://on1.agency" target="_blank">on1.agency →</a></p>
            </article>
        </div>
    </div>
</section>

<section class="vpg-section vpg-section--surface">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— Roles</p>
                <h2>Four <em>chairs</em>, rotating.</h2>
            </div>
            <div class="vpg-section-head__meta">Monthly cycle</div>
        </div>
        <div class="vpg-grid vpg-grid--4">
            <article class="vpg-card"><span class="vpg-caps">i.</span><h4 style="margin-top:.4rem">Editor</h4><p class="vpg-card__lede">Curates the monthly issue, commissions pitches, writes the editor&rsquo;s letter.</p></article>
            <article class="vpg-card"><span class="vpg-caps">ii.</span><h4 style="margin-top:.4rem">Cartographer</h4><p class="vpg-card__lede">Reviews and approves location submissions, verifies coordinates, maintains the index.</p></article>
            <article class="vpg-card"><span class="vpg-caps">iii.</span><h4 style="margin-top:.4rem">Reviewer</h4><p class="vpg-card__lede">Tests gear, writes shop and studio reviews, scores entries on Design / Performance / Price.</p></article>
            <article class="vpg-card"><span class="vpg-caps">iv.</span><h4 style="margin-top:.4rem">Producer</h4><p class="vpg-card__lede">Layouts, PDF, print coordination, member communications. The boring-but-essential chair.</p></article>
        </div>
        <p class="vpg-caps" style="text-align:center;margin-top:2.5rem">— Sustaining members are invited to rotate through any chair · application via membership.</p>
    </div>
</section>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-quote">
        <span class="vpg-asterism vpg-asterism--mark"></span>
        <blockquote>The editorial circle stays small on purpose · so every issue still has a person responsible for every line.</blockquote>
        <cite>— Editorial principle</cite>
    </div>
</section>

</main>
<?php get_footer();
