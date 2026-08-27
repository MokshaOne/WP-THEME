<?php
/** Template Name: Join VPG */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> Membership</span>
    <h1>Become a <em>member</em>.</h1>
    <p class="vpg-lede">€60 per year &middot; PDF magazine, submission rights to the map, event discounts, and a profile page.</p>

    <p style="display:inline-block;margin-top:2rem;background:var(--vpg-oxblood);color:var(--vpg-bg);padding:.5rem 1.2rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:var(--vpg-fs-xs);letter-spacing:.22em;text-transform:uppercase">
        ◐ Beta phase &middot; paid signup opens soon
    </p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap--narrow" style="text-align:center">
        <p class="vpg-caps">— During beta</p>
        <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0 1.5rem">Join the <em>wait-list</em>.</h2>
        <p class="vpg-lede" style="margin:0 auto 2rem">
            Paid membership opens when we leave beta. Sign up here · one email when it opens · founding-member rate locked in for the first 100.
        </p>
        <a class="vpg-btn vpg-btn--accent vpg-btn--lg" href="<?php echo esc_url( home_url('/membership/#wait-list') ); ?>">Join wait-list <span class="vpg-btn__arrow">→</span></a>
        <p style="margin-top:1rem;color:var(--vpg-muted);font-size:var(--vpg-fs-md)">Or just <a href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>">read the magazine for free</a> in the meantime.</p>
    </div>
</section>

</main>
<?php get_footer();
