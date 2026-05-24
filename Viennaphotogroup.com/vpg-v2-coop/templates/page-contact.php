<?php
/** Template Name: Contact */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$id = vpg_identity();
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> Contact</span>
    <h1>Send a <em>message</em>.</h1>
    <p class="vpg-lede">Editorial enquiries, submissions, press, partnerships — one inbox, one editor reading every message.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <div class="vpg-split vpg-split--narrow">

            <form class="vpg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'vpg_contact' ); ?>
                <input type="hidden" name="action" value="vpg_contact">
                <label><span>Your name</span><input type="text" name="name" required></label>
                <label><span>Email</span><input type="email" name="email" required></label>
                <label><span>Topic</span>
                    <select name="topic">
                        <option>Editorial enquiry</option>
                        <option>Location submission</option>
                        <option>Studio / shop listing</option>
                        <option>Membership question</option>
                        <option>Press / interview</option>
                        <option>Partnership</option>
                        <option>Bug / typo report</option>
                        <option>Other</option>
                    </select>
                </label>
                <label><span>Message</span><textarea name="message" rows="6" required placeholder="Be specific. We read everything."></textarea></label>
                <p><button type="submit" class="vpg-btn vpg-btn--lg">Send message <span class="vpg-btn__arrow">→</span></button></p>
                <p class="vpg-form__hint">By submitting you agree to our <a href="<?php echo esc_url( home_url('/privacy/') ); ?>">privacy policy</a>. We never share your email.</p>
            </form>

            <aside style="display:flex;flex-direction:column;gap:var(--vpg-sp-5)">
                <article class="vpg-card">
                    <p class="vpg-caps">— Direct</p>
                    <h3 style="margin-top:.4rem"><a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a></h3>
                    <p class="vpg-card__lede">For URLs, attachments, longer threads — direct email is the cleanest path.</p>
                </article>
                <article class="vpg-card">
                    <p class="vpg-caps">— Response</p>
                    <h3 style="margin-top:.4rem">Within 72 hours</h3>
                    <p class="vpg-card__lede">One editor reads every message. We answer in the order received · weekday mornings, Vienna time.</p>
                </article>
                <article class="vpg-card">
                    <p class="vpg-caps">— Location</p>
                    <h3 style="margin-top:.4rem"><?php echo esc_html( $id['location'] ); ?></h3>
                    <p class="vpg-card__lede">We do not maintain a public street office. Meetings by appointment for editorial collaborations only.</p>
                </article>
            </aside>
        </div>
    </div>
</section>

<section class="vpg-section vpg-section--surface vpg-section--tight">
    <div class="vpg-wrap--narrow" style="text-align:center">
        <p class="vpg-caps">— Members</p>
        <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0 1.5rem">Want to <em>submit</em> a location or studio?</h2>
        <p style="margin-bottom:2rem;color:var(--vpg-ink-mid)">Use the member submission flow · faster review · auto-mapped · proper data capture.</p>
        <a class="vpg-btn vpg-btn--accent" href="<?php echo esc_url( home_url('/submit/') ); ?>">Open submission form <span class="vpg-btn__arrow">→</span></a>
    </div>
</section>

</main>
<?php get_footer();
