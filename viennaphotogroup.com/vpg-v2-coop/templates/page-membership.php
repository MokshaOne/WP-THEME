<?php
/** Template Name: Membership tiers */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> Membership</span>
    <h1>Three ways to <em>belong</em>.</h1>
    <p class="vpg-lede">VPG is member-run, ad-free, and built on people who actually walk the city. Pick the tier that fits how deeply you want to feed the matrix.</p>

    <!-- Beta banner -->
    <p style="display:inline-block;margin-top:2rem;background:var(--vpg-oxblood);color:var(--vpg-bg);padding:.5rem 1.2rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:var(--vpg-fs-xs);letter-spacing:.22em;text-transform:uppercase">
        ◐ Beta phase &middot; paid tiers open soon
    </p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">

        <p class="vpg-lede" style="max-width:62ch;margin:0 auto var(--vpg-sp-7);text-align:center">
            VPG is currently in <em>open beta</em>. The free <strong>Reader</strong> tier is fully active. The paid tiers
            (<strong>Member</strong> and <strong>Sustaining</strong>) open when we leave beta · expected
            shortly. You can join the wait-list now.
        </p>

        <div class="vpg-grid vpg-grid--3">

            <!-- ─── READER · available ─── -->
            <article class="vpg-card vpg-card--featured">
                <span style="position:absolute;top:1rem;right:1rem;background:var(--vpg-member);color:var(--vpg-bg);padding:.2rem .6rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:9px;letter-spacing:.22em;text-transform:uppercase">Active</span>
                <p class="vpg-caps">— Reader</p>
                <h3 style="font-family:var(--vpg-font-display);font-size:clamp(40px,4.5vw,56px);font-weight:var(--vpg-fw-bold);margin:.5rem 0;color:var(--vpg-accent)">Free</h3>
                <p class="vpg-card__lede" style="margin-bottom:1.5rem">Browse the magazine, the location index, all reviews and tutorials — open access, no signup.</p>
                <ul style="list-style:none;padding:0;margin:1rem 0 0;border-top:1px solid var(--vpg-rule);padding-top:1rem;display:flex;flex-direction:column;gap:.6rem;font-size:var(--vpg-fs-md);color:var(--vpg-ink-mid)">
                    <li>✓ Read every magazine issue online</li>
                    <li>✓ Browse the full location map</li>
                    <li>✓ Studio and shop directory</li>
                    <li>✓ Buying guide &amp; tutorials</li>
                    <li>✓ Newsletter when an issue ships</li>
                </ul>
                <p style="margin-top:1.5rem"><a class="vpg-btn vpg-btn--accent vpg-btn--block" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>">Start reading <span class="vpg-btn__arrow">→</span></a></p>
            </article>

            <!-- ─── MEMBER · grayed during beta ─── -->
            <article class="vpg-card" style="opacity:.55;filter:grayscale(0.6);position:relative">
                <span style="position:absolute;top:1rem;right:1rem;background:var(--vpg-card-deep);color:var(--vpg-muted);padding:.2rem .6rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:9px;letter-spacing:.22em;text-transform:uppercase">Beta · soon</span>
                <p class="vpg-caps">— Member</p>
                <h3 style="font-family:var(--vpg-font-display);font-size:clamp(40px,4.5vw,56px);font-weight:var(--vpg-fw-bold);margin:.5rem 0;color:var(--vpg-muted)">€60<small style="font-size:.4em;color:var(--vpg-muted)"> / year</small></h3>
                <p class="vpg-card__lede" style="margin-bottom:1.5rem">For active creators who shoot Wien regularly and want to contribute to the index.</p>
                <ul style="list-style:none;padding:0;margin:1rem 0 0;border-top:1px solid var(--vpg-rule);padding-top:1rem;display:flex;flex-direction:column;gap:.6rem;font-size:var(--vpg-fs-md);color:var(--vpg-ink-mid)">
                    <li>○ Everything in Reader</li>
                    <li>○ <strong>PDF download</strong> of every magazine issue</li>
                    <li>○ <strong>Submit locations</strong> directly to the map</li>
                    <li>○ Studio-share directory access</li>
                    <li>○ Event discounts</li>
                    <li>○ Early access to new issues</li>
                    <li>○ Member profile page</li>
                </ul>
                <p style="margin-top:1.5rem"><button class="vpg-btn vpg-btn--block" disabled style="cursor:not-allowed;background:var(--vpg-card-deep);color:var(--vpg-muted);border-color:var(--vpg-rule)">Available after beta</button></p>
            </article>

            <!-- ─── SUSTAINING · grayed during beta ─── -->
            <article class="vpg-card" style="opacity:.55;filter:grayscale(0.6);position:relative">
                <span style="position:absolute;top:1rem;right:1rem;background:var(--vpg-card-deep);color:var(--vpg-muted);padding:.2rem .6rem;border-radius:999px;font-family:var(--vpg-font-mono);font-size:9px;letter-spacing:.22em;text-transform:uppercase">Beta · soon</span>
                <p class="vpg-caps">— Sustaining</p>
                <h3 style="font-family:var(--vpg-font-display);font-size:clamp(40px,4.5vw,56px);font-weight:var(--vpg-fw-bold);margin:.5rem 0;color:var(--vpg-muted)">€180<small style="font-size:.4em;color:var(--vpg-muted)"> / year</small></h3>
                <p class="vpg-card__lede" style="margin-bottom:1.5rem">For photographers who want to support the platform structurally · keeps VPG ad-free forever.</p>
                <ul style="list-style:none;padding:0;margin:1rem 0 0;border-top:1px solid var(--vpg-rule);padding-top:1rem;display:flex;flex-direction:column;gap:.6rem;font-size:var(--vpg-fs-md);color:var(--vpg-ink-mid)">
                    <li>○ Everything in Member</li>
                    <li>○ Name listed in every issue&rsquo;s colophon</li>
                    <li>○ Signed annual print collection</li>
                    <li>○ Priority editorial review</li>
                    <li>○ Invitation to the yearly editorial dinner</li>
                </ul>
                <p style="margin-top:1.5rem"><button class="vpg-btn vpg-btn--block" disabled style="cursor:not-allowed;background:var(--vpg-card-deep);color:var(--vpg-muted);border-color:var(--vpg-rule)">Available after beta</button></p>
            </article>
        </div>
    </div>
</section>

<span class="vpg-asterism vpg-asterism--break"></span>

<!-- Wait-list signup -->
<section class="vpg-section vpg-section--surface vpg-section--tight">
    <div class="vpg-wrap--narrow" style="text-align:center">
        <p class="vpg-caps">— Wait-list</p>
        <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0 1.5rem">Be first when paid tiers <em>open</em>.</h2>
        <p style="margin-bottom:2rem;color:var(--vpg-ink-mid);max-width:48ch;margin-left:auto;margin-right:auto">
            One email when we leave beta · founding-member rate locked in for the first 100 sign-ups · then full price applies.
        </p>
        <form class="vpg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:420px;margin:0 auto">
            <?php wp_nonce_field( 'vpg_contact' ); ?>
            <input type="hidden" name="action" value="vpg_contact">
            <input type="hidden" name="topic" value="Wait-list · paid tiers">
            <input type="hidden" name="message" value="Wait-list signup for paid tier launch">
            <label><span>Your name</span><input type="text" name="name" required></label>
            <label><span>Email</span><input type="email" name="email" required></label>
            <label><span>Which tier interests you?</span>
                <select name="topic" onchange="this.form.elements['message'].value='Wait-list · '+this.value">
                    <option value="Wait-list · Member tier (€60/yr)">Member · €60/yr</option>
                    <option value="Wait-list · Sustaining tier (€180/yr)">Sustaining · €180/yr</option>
                    <option value="Wait-list · undecided">Not sure yet</option>
                </select>
            </label>
            <p><button type="submit" class="vpg-btn vpg-btn--lg">Join the wait-list <span class="vpg-btn__arrow">→</span></button></p>
            <p class="vpg-form__hint">No commitment, no payment requested. Just an email when paid tiers open.</p>
        </form>
    </div>
</section>

<span class="vpg-asterism vpg-asterism--break"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap--narrow">
        <div class="vpg-section-head" style="grid-template-columns:1fr">
            <div>
                <p class="vpg-caps">— Why member-run</p>
                <h2>No ads, no investors, no <em>acquisition target</em>.</h2>
            </div>
        </div>
        <div class="vpg-prose">
            <p>VPG will run on member fees alone — once we exit beta. There are no advertisers to please, no growth-at-all-costs investor expectations, no plan to flip to a content network. Membership will keep the index uncompromised, the magazine ad-free, and the editorial calendar in the hands of people who actually shoot Vienna.</p>
            <p>If membership ever stops being affordable for you, the Reader tier stays free. We&rsquo;d rather you keep reading than feel locked out.</p>
        </div>
    </div>
</section>

</main>
<?php get_footer();
