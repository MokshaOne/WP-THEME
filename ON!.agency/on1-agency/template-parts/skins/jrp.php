<?php
/** Skin: jrpoetry.com · Kavithai Relief · paper + oxblood + EB Garamond */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="jpw-nav">
    <a class="jpw-nav__brand" data-skin-link="home">on1.agency <em>· studio</em></a>
    <ul class="jpw-nav__links">
        <li><a data-skin-link="home" class="is-active">Home</a></li>
        <li><a data-skin-link="about">About</a></li>
        <li><a data-skin-link="work">Works</a></li>
        <li><a data-skin-link="pricing">Pricing</a></li>
        <li><a data-skin-link="faq">FAQ</a></li>
        <li><a data-skin-link="contact">Contact</a></li>
    </ul>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">An Atelier · Wien · Established 2018</p>
        <h1 class="jpw-hero__head">A quiet digital<br><em>atelier</em>.</h1>
        <p class="jpw-hero__lede">Editorial WordPress sites for studios who want to look like themselves, not like a template. Strategy, design, build — and the senior pair of eyes you need at the end.</p>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-essay">
        <p class="jpw-smallcaps jpw-smallcaps--center">— Practice</p>
        <article class="jpw-prose">
            <p><span class="jpw-dropcap">W</span>e work in one mode and one mode only — quiet, generous, editorial. Each WordPress site is hand-built around the studio's voice, not a template kit. Strategy is always included. You always work with one person — me.</p>
            <p>Four core services: editorial themes, design systems, brand &amp; visual identity, front-end development. Eight years of practice, fourteen sites shipped.</p>
        </article>
        <div class="jpw-asterism">⁂</div>
    </section>
    <section class="jpw-work">
        <p class="jpw-smallcaps jpw-smallcaps--center">— Selected work</p>
        <ul class="jpw-work-list">
            <li><span class="jpw-num">i.</span><span class="jpw-name">Kavithai · Tamil poetry archive</span><span class="jpw-year">MMXXIV</span></li>
            <li><span class="jpw-num">ii.</span><span class="jpw-name">M1O Hub · Electronic musician hub</span><span class="jpw-year">MMXXIV</span></li>
            <li><span class="jpw-num">iii.</span><span class="jpw-name">Vienna Photo Group · Community archive</span><span class="jpw-year">MMXXIII</span></li>
            <li><span class="jpw-num">iv.</span><span class="jpw-name">Raveenthiran · Photography portfolio</span><span class="jpw-year">MMXXIII</span></li>
        </ul>
    </section>
    <footer class="jpw-foot">
        <p class="jpw-asterism">⁂</p>
        <p>on1.agency · Wien · MMXXVI</p>
        <p class="jpw-italic">Quiet design is the most generous kind of work.</p>
    </footer>
</div>
<div data-skin-page="about">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">Founder · Solo Practice · Wien</p>
        <h1 class="jpw-hero__head">Nishuthan<br><em>Raveenthiran</em>.</h1>
        <p class="jpw-hero__lede">A one-person digital atelier in Wien. Eight years building editorial WordPress sites and design systems.</p>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-essay">
        <p class="jpw-smallcaps jpw-smallcaps--center">— Principles</p>
        <article class="jpw-prose">
            <p><span class="jpw-dropcap">Q</span>uiet design is the most generous kind of work. The studio holds four working principles: one person responsible from brief to ship, editorial over template always, code that lasts a decade rather than a quarter, strategy included in every engagement.</p>
        </article>
        <div class="jpw-asterism">⁂</div>
    </section>
</div>
<div data-skin-page="work">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">Selected work · since MMXVIII</p>
        <h1 class="jpw-hero__head">Works<em>.</em></h1>
        <p class="jpw-hero__lede">Fourteen sites shipped since MMXVIII. Each one hand-built around the studio's voice.</p>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-work">
        <ul class="jpw-work-list">
            <?php $i = 1; foreach ( $on1_content['work'] as $w ) : $rom = [ '', 'i.', 'ii.', 'iii.', 'iv.', 'v.', 'vi.', 'vii.', 'viii.' ]; ?>
                <li><span class="jpw-num"><?php echo esc_html( $rom[ $i ] ?? '·' ); ?></span><span class="jpw-name"><?php echo esc_html( $w['title'] . ' · ' . $w['lede'] ); ?></span><span class="jpw-year"><?php echo esc_html( $w['year'] ); ?></span></li>
            <?php $i++; endforeach; ?>
        </ul>
    </section>
</div>
<div data-skin-page="pricing">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">Three engagements · all-inclusive</p>
        <h1 class="jpw-hero__head">Pricing<em>.</em></h1>
        <p class="jpw-hero__lede">Strategy is always included. You always work with one person. No upsells, no surprises.</p>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-tiers">
        <?php $rom = [ 'i.', 'ii.', 'iii.' ]; foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?>
            <div class="jpw-tier<?php echo $f ? ' jpw-tier--feat' : ''; ?>">
                <p class="jpw-smallcaps"><?php echo esc_html( ( $rom[ $i ] ?? '·' ) . ' ' . $t['name'] ); ?></p>
                <h3 class="jpw-tier__price"><?php echo esc_html( $t['price'] ); ?></h3>
                <p class="jpw-italic"><?php echo esc_html( $t['sub'] ); ?></p>
                <ul><?php foreach ( $t['features'] as $feat ) : ?><li><?php echo esc_html( $feat ); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="faq">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">Common questions</p>
        <h1 class="jpw-hero__head">FAQ<em>.</em></h1>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-faq">
        <?php $rom = [ 'i.', 'ii.', 'iii.', 'iv.', 'v.' ]; foreach ( $on1_content['faq'] as $i => $f ) : ?>
            <details<?php echo $i === 0 ? ' open' : ''; ?>>
                <summary><span class="jpw-num"><?php echo esc_html( $rom[ $i ] ?? '·' ); ?></span> <?php echo esc_html( $f['q'] ); ?></summary>
                <p><?php echo esc_html( $f['a'] ); ?></p>
            </details>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="contact">
    <header class="jpw-hero">
        <div class="jpw-asterism">⁂</div>
        <p class="jpw-smallcaps">Send a brief · response within 72h</p>
        <h1 class="jpw-hero__head">Contact<em>.</em></h1>
        <div class="jpw-asterism">⁂</div>
    </header>
    <section class="jpw-contact">
        <form class="jpw-form" onsubmit="event.preventDefault();">
            <label>Your name<input type="text"></label>
            <label>Email<input type="email"></label>
            <label>Studio / company<input type="text"></label>
            <label>Brief<textarea rows="4"></textarea></label>
            <button type="submit">Send brief →</button>
        </form>
        <ul class="jpw-info">
            <li><p class="jpw-smallcaps">— Email</p><p><?php echo esc_html( $on1_content['identity']['email'] ?? 'hello@on1.agency' ); ?></p></li>
            <li><p class="jpw-smallcaps">— Location</p><p><?php echo esc_html( $on1_content['identity']['location'] ?? 'Wien · UTC+1' ); ?></p></li>
            <li><p class="jpw-smallcaps">— Response</p><p>Within 72 hours</p></li>
            <li><p class="jpw-smallcaps">— Booking</p><p><?php echo esc_html( $on1_content['identity']['booking'] ?? 'Q3 · slots open' ); ?></p></li>
        </ul>
    </section>
</div>
