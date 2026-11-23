<?php
/** Template Name: Newsletter signup */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> Newsletter</span>
    <h1>One email when <em>the issue ships</em>.</h1>
    <p class="vpg-lede">No marketing. No drip campaigns. One short letter per month when the new issue is live · cover, contents, a 30-word editor&rsquo;s line. That&rsquo;s it.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap--narrow">
        <form class="vpg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_contact' ); ?>
            <input type="hidden" name="action" value="vpg_contact">
            <input type="hidden" name="topic" value="Newsletter signup">
            <input type="hidden" name="message" value="Newsletter signup request">
            <label><span>Your name</span><input type="text" name="name" required></label>
            <label><span>Email</span><input type="email" name="email" required placeholder="you@studio.com"></label>
            <p><button type="submit" class="vpg-btn vpg-btn--lg">Subscribe <span class="vpg-btn__arrow">→</span></button></p>
            <p class="vpg-form__hint">We use your email only to send issue notifications. Unsubscribe with one click in any email. See <a href="<?php echo esc_url( home_url('/privacy/') ); ?>">privacy</a>.</p>
        </form>
    </div>
</section>

<section class="vpg-section vpg-section--surface">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— What you get</p>
                <h2>Three lines, <em>one link</em>.</h2>
            </div>
            <div class="vpg-section-head__meta">Monthly · ~1KB</div>
        </div>
        <div class="vpg-grid vpg-grid--3">
            <article class="vpg-card"><span class="vpg-caps">i.</span><h4 style="margin-top:.4rem">Issue cover</h4><p class="vpg-card__lede">A small JPG of the cover · so you remember what month it is.</p></article>
            <article class="vpg-card"><span class="vpg-caps">ii.</span><h4 style="margin-top:.4rem">Three article titles</h4><p class="vpg-card__lede">The headlines of the lead pieces. No clickbait, no curiosity gaps.</p></article>
            <article class="vpg-card"><span class="vpg-caps">iii.</span><h4 style="margin-top:.4rem">One link</h4><p class="vpg-card__lede">Straight to the issue. Members get the PDF link in the same email · readers get the web link.</p></article>
        </div>
    </div>
</section>

<section class="vpg-section vpg-section--tight" style="text-align:center">
    <p class="vpg-caps">— RSS</p>
    <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0">Prefer <em>RSS</em>?</h2>
    <p style="color:var(--vpg-ink-mid);max-width:48ch;margin:0 auto 2rem">Subscribe to <code style="font-family:var(--vpg-font-mono);background:var(--vpg-card-deep);padding:.2rem .5rem;border-radius:4px">/feed/magazine</code> in your reader. Cover, PDF and issue text as enclosures.</p>
    <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( home_url( '/feed/magazine' ) ); ?>" target="_blank">Open RSS feed →</a>
</section>

</main>
<?php get_footer();
