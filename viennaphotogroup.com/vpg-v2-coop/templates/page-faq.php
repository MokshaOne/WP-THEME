<?php
/** Template Name: FAQ */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> FAQ</span>
    <h1>Common <em>questions</em>.</h1>
    <p class="vpg-lede">Membership, the location index, the magazine, submissions. If your question is not here, send a note · we read every message.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap--narrow">
        <div class="vpg-faq">

            <p class="vpg-caps" style="margin-bottom:1rem">— About VPG</p>
            <details open><summary><span>01</span><span>Who runs Vienna Photo Group?</span></summary><p>A small editorial circle of photographers based in Wien. Founded December 2019. Member-run · ad-free · no investors. The platform is co-produced by Raveenthiran and on1.agency.</p></details>
            <details><summary><span>02</span><span>Is VPG a non-profit?</span></summary><p>Not formally registered as one — practically, yes. Membership fees cover hosting, print services and small honorariums. No commercial profit, no equity, no acquisition target.</p></details>

            <p class="vpg-caps" style="margin:2.5rem 0 1rem">— Membership</p>
            <details><summary><span>03</span><span>What does my membership get me?</span></summary><p>PDF download of every magazine issue · submission rights to the location map and studio directory · event discounts · early access to new issues · a member profile page. See the <a href="<?php echo esc_url( home_url('/membership/') ); ?>">full membership comparison</a>.</p></details>
            <details><summary><span>04</span><span>How do I pay?</span></summary><p>After signup you receive an email with payment instructions (SEPA / IBAN for EU members, PayPal for international). Membership activates within 24 hours of payment confirmation.</p></details>
            <details><summary><span>05</span><span>Can I cancel?</span></summary><p>Membership is annual · no auto-renewal. If you don&rsquo;t renew, your Member tier downgrades to Reader at the end of the year — you still see everything, just no submissions or PDFs.</p></details>

            <p class="vpg-caps" style="margin:2.5rem 0 1rem">— The magazine</p>
            <details><summary><span>06</span><span>How often does the magazine ship?</span></summary><p>Monthly · usually around the first of the month. Late only when the city gives us a reason to wait (special exhibitions, light conditions, members travelling).</p></details>
            <details><summary><span>07</span><span>Can I write for the magazine?</span></summary><p>Yes · members can pitch articles via the submission form. Editorial reviews every pitch within two weeks. Approved pitches land in a future issue with full credit and a member profile link.</p></details>
            <details><summary><span>08</span><span>Who owns submitted content?</span></summary><p>You do. Members retain full copyright to text, photos and data. By submitting you grant VPG a non-exclusive, non-transferable licence to publish and archive the work indefinitely. Pull anytime.</p></details>

            <p class="vpg-caps" style="margin:2.5rem 0 1rem">— Locations &amp; submissions</p>
            <details><summary><span>09</span><span>How do I add a location to the map?</span></summary><p>Log in &rarr; <a href="<?php echo esc_url( home_url('/submit/') ); ?>">/submit/</a> &rarr; pick "Location" &rarr; type the address (the form auto-pins it on the map) &rarr; add notes about light, access and best time of day. Editorial reviews within 72h.</p></details>
            <details><summary><span>10</span><span>What gets rejected?</span></summary><p>Locations that are clearly private / trespassing · entries we cannot verify on the ground · duplicates of existing pins · anything that endangers the photographer or the place. Most submissions are accepted with light edits.</p></details>

            <p class="vpg-caps" style="margin:2.5rem 0 1rem">— Technical</p>
            <details><summary><span>11</span><span>Why is the public site read-only?</span></summary><p>To protect the integrity of the index. Public write-access invites spam, AI-generated junk and astroturfed listings. Submissions go through members who walked the city · the data stays clean.</p></details>
            <details><summary><span>12</span><span>Is there an API?</span></summary><p>Not yet. The data is REST-readable via WordPress&rsquo;s default endpoints for logged-in users · a proper public API with rate limits is on the 2026 roadmap.</p></details>

        </div>
    </div>
</section>

<section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
    <div class="vpg-wrap--narrow">
        <p class="vpg-caps">— Still unsure</p>
        <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0 2rem">Ask us <em>directly</em>.</h2>
        <a class="vpg-btn vpg-btn--accent" href="<?php echo esc_url( home_url('/contact/') ); ?>">Send a message <span class="vpg-btn__arrow">→</span></a>
    </div>
</section>

</main>
<?php get_footer();
