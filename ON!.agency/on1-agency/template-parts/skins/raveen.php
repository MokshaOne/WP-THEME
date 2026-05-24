<?php
/** Skin: raveenthiran.com · Catalogue Noir · warm dark + amber + Inter Tight */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="rcn-topbar">
    <a class="rcn-brand" data-skin-link="home">on1<span class="rcn-amber">.</span>agency</a>
    <div class="rcn-dots">
        <a data-skin-link="home" class="is-active" title="Home"><span></span></a>
        <a data-skin-link="about" title="About"><span></span></a>
        <a data-skin-link="work" title="Work"><span></span></a>
        <a data-skin-link="pricing" title="Pricing"><span></span></a>
        <a data-skin-link="faq" title="FAQ"><span></span></a>
        <a data-skin-link="contact" title="Contact"><span></span></a>
    </div>
    <span class="rcn-meta">VIE / 2026</span>
</nav>
<div data-skin-page="home" class="is-active">
    <main class="rcn-stage">
        <p class="rcn-caps">— Atelier · Vienna · since 2018</p>
        <h1 class="rcn-display">on1<span class="rcn-amber">.</span>agency</h1>
        <p class="rcn-lede"><?php echo esc_html( $on1_content['lede'] ); ?></p>
    </main>
    <div class="rcn-strip">
        <span class="rcn-caps">i. EDITORIAL WP</span><span class="rcn-caps">ii. DESIGN SYSTEMS</span><span class="rcn-caps">iii. IDENTITY</span><span class="rcn-caps">iv. FRONT-END</span><span class="rcn-amber">●</span>
    </div>
</div>
<div data-skin-page="about">
    <div class="rcn-page-hero">
        <p class="rcn-caps">— Founder / Solo Practice</p>
        <h1 class="rcn-page-head">about<span class="rcn-amber">.</span></h1>
        <p class="rcn-page-lede">Nishuthan Raveenthiran. A one-person Vienna atelier. Eight years building editorial WordPress sites and design systems.</p>
    </div>
    <ul class="rcn-list">
        <?php foreach ( $on1_content['principles'] as $i => $p ) : ?>
            <li><span class="rcn-id"><?php printf( '%02d', $i + 1 ); ?></span><span class="rcn-name"><?php echo esc_html( $p[1] ); ?></span><span class="rcn-meta"><?php echo $i === 0 ? 'CORE' : ($i === 1 ? 'ALWAYS' : ($i === 2 ? 'NO EXCEPTIONS' : 'CRAFT')); ?></span></li>
        <?php endforeach; ?>
    </ul>
</div>
<div data-skin-page="work">
    <div class="rcn-page-hero">
        <p class="rcn-caps">— 14 sites / since MMXVIII</p>
        <h1 class="rcn-page-head">work<span class="rcn-amber">.</span></h1>
        <p class="rcn-page-lede">Selected case studies. Each hand-built around the studio's voice.</p>
    </div>
    <ul class="rcn-list">
        <?php foreach ( $on1_content['work'] as $w ) : ?>
            <li><span class="rcn-id"><?php echo esc_html( $w['year'] ); ?></span><span class="rcn-name"><?php echo esc_html( $w['title'] . ' — ' . $w['lede'] ); ?></span><span class="rcn-meta">●</span></li>
        <?php endforeach; ?>
    </ul>
</div>
<div data-skin-page="pricing">
    <div class="rcn-page-hero">
        <p class="rcn-caps">— Three engagements / all-inclusive</p>
        <h1 class="rcn-page-head">pricing<span class="rcn-amber">.</span></h1>
    </div>
    <section class="rcn-tiers">
        <?php $rom = [ 'i', 'ii', 'iii' ]; foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?>
            <div class="rcn-tier<?php echo $f ? ' rcn-tier--feat' : ''; ?>">
                <p class="rcn-caps"<?php echo $f ? ' style="color:#F2A03D"' : ''; ?>><?php echo esc_html( ( $rom[ $i ] ?? '·' ) . ' / ' . strtoupper( $t['name'] ) ); ?></p>
                <h3><?php echo esc_html( $t['price'] ); ?></h3>
                <p class="rcn-caps" style="opacity:0.5"><?php echo esc_html( $t['sub'] ); ?></p>
                <ul><?php foreach ( $t['features'] as $feat ) : ?><li><?php echo esc_html( $feat ); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="faq">
    <div class="rcn-page-hero">
        <p class="rcn-caps">— Frequently asked</p>
        <h1 class="rcn-page-head">faq<span class="rcn-amber">.</span></h1>
    </div>
    <section class="rcn-faq">
        <?php foreach ( $on1_content['faq'] as $i => $f ) : ?>
            <details<?php echo $i === 0 ? ' open' : ''; ?>>
                <summary><span class="rcn-amber"><?php printf( '%02d', $i + 1 ); ?></span> <?php echo esc_html( $f['q'] ); ?></summary>
                <p><?php echo esc_html( $f['a'] ); ?></p>
            </details>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="contact">
    <div class="rcn-page-hero">
        <p class="rcn-caps">— Send a brief / 72h response</p>
        <h1 class="rcn-page-head">contact<span class="rcn-amber">.</span></h1>
    </div>
    <section class="rcn-contact">
        <form class="rcn-form" onsubmit="event.preventDefault();">
            <label>Name<input type="text"></label>
            <label>Email<input type="email"></label>
            <label>Studio<input type="text"></label>
            <label>Brief<textarea rows="4"></textarea></label>
            <button class="rcn-btn" type="submit">SEND BRIEF →</button>
        </form>
        <ul class="rcn-info">
            <li><p class="rcn-caps">— EMAIL</p><p><?php echo esc_html( $on1_content['identity']['email'] ?? 'hello@on1.agency' ); ?></p></li>
            <li><p class="rcn-caps">— LOCATION</p><p>Vienna · UTC+1</p></li>
            <li><p class="rcn-caps">— RESPONSE</p><p>72 hours</p></li>
            <li><p class="rcn-caps">— BOOKING</p><p>Q3 / slots open</p></li>
        </ul>
    </section>
</div>
