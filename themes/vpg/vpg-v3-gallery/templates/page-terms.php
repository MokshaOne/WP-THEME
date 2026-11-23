<?php
/** Template Name: Terms */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$id = vpg_identity();
?>
<main id="vpg-main">

<section class="g-phero">
    <div class="g-wrap">
        <p class="g-kicker" style="margin-bottom:16px">● Legal</p>
        <h1 class="g-display g-phero__title">Terms of <em>service</em>.</h1>
        <p class="g-lede g-phero__lede">Membership rules, content rights, editorial policy. Written plainly · effective on signup.</p>
    </div>
</section>

<section class="g-section g-section--tight">
    <div class="g-wrap">
        <div class="g-prose">

            <p style="font-style:italic;color:var(--g-mid)">Last updated: <?php echo esc_html( wp_date( 'F Y' ) ); ?></p>

            <h2>1 · Who runs this</h2>
            <p>Vienna Photo Group (VPG) is operated by the editorial circle, co-produced by Raveenthiran &times; on1.agency. Contact: <a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a>. Full imprint at <a href="<?php echo esc_url( home_url('/imprint/') ); ?>">/imprint/</a>.</p>

            <h2>2 · Public access</h2>
            <p>Browsing the website, the map, the magazine and the directories is free. No account required. Standard server logs apply · see <a href="<?php echo esc_url( home_url('/privacy/') ); ?>">/privacy/</a>.</p>

            <h2>3 · Member accounts</h2>
            <p>To submit content, download PDFs or appear in the member directory, you need to register as a Member. Membership is currently free; optional paid supporter tiers may be introduced later and will never be required for the features listed here. You agree to provide accurate information, keep your password secure, and not share your account.</p>

            <h2>4 · Submissions &amp; content rights</h2>
            <p>You retain full copyright to anything you submit (text, photos, location data, reviews). By submitting, you grant VPG a non-exclusive, worldwide, non-transferable licence to publish, archive and republish the work in the magazine and on the platform · indefinitely or until you ask us to remove it.</p>
            <p>You guarantee that submissions are your own work, do not infringe third-party rights, and do not endanger people or property. We reject submissions that promote trespass, illegal activity, or content we cannot verify.</p>

            <h2>5 · Editorial review</h2>
            <p>The editorial circle reviews submissions within 72 hours typically (max two weeks). We may edit for clarity, accuracy, or fit · we will not change your meaning without consulting you. Rejected submissions are deleted from the queue · feedback is offered when useful.</p>

            <h2>6 · Membership fees &amp; refunds</h2>
            <p>Membership is currently free of charge; no payment is requested or collected. If optional paid supporter tiers are introduced later, they will be annual with no automatic renewal, refundable within 14 days of payment (statutory cooling-off period); after 14 days no refunds, but the tier runs to the end of the paid year regardless. Free membership is never affected by supporter-tier changes.</p>

            <h2>7 · Acceptable use</h2>
            <p>You may not · scrape the site beyond the public RSS / sitemap · attempt to gain unauthorised access · submit spam, AI-generated junk, or impersonate other photographers · resell PDFs or location data commercially. We reserve the right to suspend accounts that violate these rules · no refund in cases of abuse.</p>

            <h2>8 · Magazine PDFs</h2>
            <p>Magazine PDFs are for personal, non-commercial use. You may print copies, archive them, share with friends. You may not redistribute commercially, repost in full on other websites, or claim authorship.</p>

            <h2>9 · Liability</h2>
            <p>VPG provides location, studio and shop information in good faith but does not guarantee accuracy, accessibility, or safety. Photograph at your own risk. We accept no liability for damages arising from use of the location index. External links are not endorsed.</p>

            <h2>10 · Changes</h2>
            <p>We may update these terms · material changes will be announced via newsletter and the next magazine issue · 30-day notice. Continued use after the notice period means acceptance.</p>

            <h2>11 · Governing law</h2>
            <p>Austrian law applies, exclusive jurisdiction Vienna (or your home jurisdiction if you are an EU consumer, per EU consumer protection law).</p>

        </div>
    </div>
</section>

</main>
<?php get_footer();
