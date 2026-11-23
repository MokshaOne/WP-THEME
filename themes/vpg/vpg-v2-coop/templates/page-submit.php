<?php
/** Template Name: Submit content */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> Submit</span>
    <h1>Feed the <em>matrix</em>.</h1>
    <p class="vpg-lede">Members submit locations, studios, shops, reviews and tutorial pitches directly to the index. Editorial reviews within 72 hours.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<?php if ( ! is_user_logged_in() ) : ?>
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap--narrow vpg-quote">
            <blockquote>Submissions are members-only.</blockquote>
            <cite>— Frontend submission · members of the inner circle contribute directly to the matrix.</cite>
            <p style="margin-top:2rem;display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                <a class="vpg-btn vpg-btn--accent" href="<?php echo esc_url( home_url('/membership/') ); ?>">Join the wait-list <span class="vpg-btn__arrow">→</span></a>
                <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Already a member? Log in</a>
            </p>
        </div>
    </section>
<?php else : ?>

    <!-- What can I submit -->
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap">
            <div class="vpg-section-head">
                <div>
                    <p class="vpg-caps">— Five things you can submit</p>
                    <h2>What feeds the <em>index</em>.</h2>
                </div>
                <div class="vpg-section-head__meta">72h editorial review</div>
            </div>
            <div class="vpg-grid vpg-grid--auto">
                <article class="vpg-card"><span class="vpg-chip vpg-chip--loc"><span class="vpg-chip__dot"></span> Location</span><h3 style="margin-top:.8rem">A shooting spot</h3><p class="vpg-card__lede">An address, GPS pin, light direction, optimal time of day, access notes.</p></article>
                <article class="vpg-card"><span class="vpg-chip vpg-chip--studio"><span class="vpg-chip__dot"></span> Studio</span><h3 style="margin-top:.8rem">A rental space</h3><p class="vpg-card__lede">Studio size, rate, daylight or strobe, vehicle access, booking link.</p></article>
                <article class="vpg-card"><span class="vpg-chip vpg-chip--shop"><span class="vpg-chip__dot"></span> Shop</span><h3 style="margin-top:.8rem">A supplier</h3><p class="vpg-card__lede">Camera shop, film lab, gear retailer · district, what they stock, opening hours.</p></article>
                <article class="vpg-card"><span class="vpg-chip vpg-chip--review"><span class="vpg-chip__dot"></span> Review</span><h3 style="margin-top:.8rem">Gear scored</h3><p class="vpg-card__lede">A camera, lens, accessory you actually own · scored on Design / Performance / Price /10.</p></article>
                <article class="vpg-card"><span class="vpg-chip vpg-chip--tut"><span class="vpg-chip__dot"></span> Tutorial</span><h3 style="margin-top:.8rem">A how-to pitch</h3><p class="vpg-card__lede">A skill, technique or workflow you can write up. We&rsquo;ll edit collaboratively.</p></article>
            </div>
        </div>
    </section>

    <span class="vpg-asterism vpg-asterism--break"></span>

    <!-- The submission form -->
    <section class="vpg-section vpg-section--surface">
        <div class="vpg-wrap--narrow">
            <p class="vpg-caps">— New submission</p>
            <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0 2rem">Tell us what you&rsquo;ve <em>found</em>.</h2>

            <form class="vpg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:none">
                <?php wp_nonce_field( 'vpg_submit' ); ?>
                <input type="hidden" name="action" value="vpg_submit">

                <label><span>What are you submitting?</span>
                    <select name="submit_type" required>
                        <option value="vpg_location">Location · a shooting spot</option>
                        <option value="vpg_studio">Studio · a rental space</option>
                        <option value="vpg_shop">Shop · a camera shop / lab / supplier</option>
                        <option value="vpg_review">Gear review · with /10 scores</option>
                        <option value="vpg_tutorial">Tutorial pitch · we&rsquo;ll discuss</option>
                    </select>
                </label>

                <label><span>Title</span><input type="text" name="title" required placeholder="The Stephansdom rooftop · golden hour"></label>

                <label><span>One-line description</span><input type="text" name="lede" placeholder="South-facing terrace · 35m elevation · free access via café"></label>

                <label><span>Body / notes</span>
                    <textarea name="body" rows="8" required placeholder="Light direction, best time of day, what to bring, access notes, anything you wish you&rsquo;d known before going."></textarea>
                </label>

                <label><span>District / area</span><input type="text" name="district" placeholder="1010 · Innere Stadt"></label>

                <p><button type="submit" class="vpg-btn vpg-btn--accent vpg-btn--lg">Submit for review <span class="vpg-btn__arrow">→</span></button></p>
                <p class="vpg-form__hint">
                    Submissions are queued for editorial review. You&rsquo;ll receive an email when your entry is approved and published.
                    Coordinates can be added by the editor or by you later from the post edit screen · the map-picker is on the post editor.
                </p>
            </form>
        </div>
    </section>

    <!-- Editorial guidelines -->
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap--prose">
            <p class="vpg-caps">— Editorial guidelines</p>
            <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0">What gets <em>accepted</em>.</h2>
            <div class="vpg-prose">
                <ul>
                    <li><strong>Specific</strong> · "rooftop of Café X with south view" beats "central Vienna".</li>
                    <li><strong>First-hand</strong> · you shot there, you bought from there, you actually own the gear.</li>
                    <li><strong>Verifiable</strong> · the place exists, the shop is still open, the lens is currently on sale.</li>
                    <li><strong>Safe</strong> · no trespass, no endangered listings, no recommending hostile spaces.</li>
                </ul>
                <p>Rejected submissions are not deleted from the queue · the editor will message you with feedback so you can revise. Most submissions go through with light edits.</p>
            </div>
        </div>
    </section>

<?php endif; ?>

</main>
<?php get_footer();
