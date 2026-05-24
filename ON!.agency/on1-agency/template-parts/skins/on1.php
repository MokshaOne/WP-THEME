<?php
/**
 * Skin: on1.agency — faithful clone of the current v4 theme design.
 * Receives:
 *   $skin        — merged skin config (slug, label, tokens, ...)
 *   $on1_content — shared content payload (identity, services, work, tiers, faq)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$id = $on1_content['identity'];
?>

<nav class="o1a-nav">
    <a class="o1a-brand" data-skin-link="home">on1.agency <sup>®</sup></a>
    <ul class="o1a-nav__links">
        <li><a data-skin-link="home" class="is-active">Home</a></li>
        <li><a data-skin-link="about">About</a></li>
        <li><a data-skin-link="work">Work<sup><?php echo esc_html( $id['sites'] ?? '14' ); ?></sup></a></li>
        <li><a data-skin-link="pricing">Pricing</a></li>
        <li><a data-skin-link="faq">FAQ</a></li>
        <li><a data-skin-link="contact">Brief</a></li>
    </ul>
    <a class="o1a-cta" data-skin-link="contact">Let's talk <span>↗</span></a>
</nav>

<!-- ── HOME ── -->
<div data-skin-page="home" class="is-active">
    <header class="o1a-hero">
        <div class="o1a-planet"></div>
        <p class="o1a-lede"><strong>"<?php echo esc_html( $on1_content['lede'] ); ?>"</strong></p>
        <h1 class="o1a-hero__head">
            <span>where <em>craft</em></span>
            <span>meets code<em>.</em></span>
        </h1>
        <div class="o1a-hero__bottom">
            <p class="o1a-tag">Booking <em>Q3</em> — slots open</p>
            <a class="o1a-cta" data-skin-link="contact">Start a project <span>↗</span></a>
        </div>
    </header>
    <section class="o1a-stats">
        <div><strong><?php echo esc_html( $id['sites'] ?? '14' ); ?>+</strong><p>Sites shipped<br>since <?php echo esc_html( $id['since'] ?? '2018' ); ?></p></div>
        <div><strong>3<small>mo</small></strong><p>Avg. engagement<br>length</p></div>
        <div><strong><?php echo esc_html( $id['reply'] ?? '72' ); ?>h</strong><p>Reply<br>window</p></div>
        <div><strong>1</strong><p>Person<br>responsible</p></div>
    </section>
</div>

<!-- ── ABOUT ── -->
<div data-skin-page="about">
    <div class="o1a-page-hero">
        <p class="o1a-eyebrow">— Founder · Solo Practice · <?php echo esc_html( $id['location'] ?? 'Wien' ); ?></p>
        <h1 class="o1a-page-head">Nishuthan <em>Raveenthiran</em>.</h1>
        <p class="o1a-page-lede"><?php echo esc_html( $on1_content['lede'] ); ?></p>
    </div>
</div>

<!-- ── WORK ── -->
<div data-skin-page="work">
    <div class="o1a-page-hero">
        <p class="o1a-eyebrow">— <?php echo esc_html( $id['sites'] ?? '14' ); ?> sites · since <?php echo esc_html( $id['since'] ?? 'MMXVIII' ); ?></p>
        <h1 class="o1a-page-head">Selected <em>work</em>.</h1>
    </div>
</div>

<!-- ── PRICING ── -->
<div data-skin-page="pricing">
    <div class="o1a-page-hero">
        <p class="o1a-eyebrow">— Three engagements · all-inclusive</p>
        <h1 class="o1a-page-head">Pricing<em>.</em></h1>
        <p class="o1a-page-lede">Strategy is always included. You always work with one person.</p>
    </div>
</div>

<!-- ── FAQ ── -->
<div data-skin-page="faq">
    <div class="o1a-page-hero">
        <p class="o1a-eyebrow">— Frequently asked</p>
        <h1 class="o1a-page-head">FAQ<em>.</em></h1>
    </div>
</div>

<!-- ── CONTACT ── -->
<div data-skin-page="contact">
    <div class="o1a-page-hero">
        <p class="o1a-eyebrow">— Send a brief · 72h response</p>
        <h1 class="o1a-page-head">Brief<em>.</em></h1>
        <p class="o1a-page-lede">Tell me what you are building.</p>
    </div>
</div>

<footer class="o1a-foot">
    <h3>on1<em>.</em>agency</h3>
    <p>© 2026 · <?php echo esc_html( $id['location'] ?? 'Vienna' ); ?> · <?php echo esc_html( $id['booking'] ?? 'Booking Q3' ); ?></p>
</footer>
