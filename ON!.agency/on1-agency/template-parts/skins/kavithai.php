<?php
/** Skin: Verse · paper editorial fantasy · serif + asterism + oxblood */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="kv-nav">
    <a class="kv-nav__brand" data-skin-link="home">on1.agency</a><span class="kv-nav__dot">·</span>
    <ul class="kv-nav__links">
        <li><a data-skin-link="home" class="is-active">Home</a></li><li><a data-skin-link="about">About</a></li><li><a data-skin-link="work">Works</a></li><li><a data-skin-link="pricing">Pricing</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">Contact</a></li>
    </ul>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">An atelier · Wien</p><h1 class="kv-hero__head">on1.<br><em>agency</em></h1><p class="kv-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p><div class="kv-asterism">⁂</div></header>
    <section class="kv-essay"><p class="kv-eyebrow kv-eyebrow--center">— Practice</p><h2 class="kv-section-head">What we do</h2><article class="kv-prose"><p><span class="kv-dropcap">W</span>e work in one mode and one mode only — quiet, generous, editorial. Each WordPress site is hand-built around the studio's voice, not a template kit.</p></article><div class="kv-asterism">⁂</div></section>
    <section class="kv-work"><p class="kv-eyebrow kv-eyebrow--center">— Selected work</p><ul class="kv-work-list"><?php $rom=['','i.','ii.','iii.','iv.','v.','vi.']; foreach ( array_slice( $on1_content['work'], 0, 4 ) as $i => $w ) : ?><li><span class="kv-work-num"><?php echo esc_html( $rom[$i+1] ?? '·' ); ?></span><span class="kv-work-name"><?php echo esc_html( $w['title'] . ' · ' . $w['lede'] ); ?></span><span class="kv-work-year"><?php echo esc_html( $w['year'] ); ?></span></li><?php endforeach; ?></ul></section>
    <footer class="kv-foot"><p>⁂</p><p>on1.agency · Wien · 2026</p><p class="kv-faint">Quiet design is the most generous kind of work.</p></footer>
</div>
<div data-skin-page="about">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">Founder · Solo Practice</p><h1 class="kv-hero__head">about.<br><em>nishuthan</em></h1><p class="kv-hero__lede">A one-person Vienna atelier. Eight years building editorial WordPress sites.</p><div class="kv-asterism">⁂</div></header>
    <section class="kv-work"><p class="kv-eyebrow kv-eyebrow--center">— Principles</p><ul class="kv-work-list"><?php foreach ( $on1_content['principles'] as $p ) : ?><li><span class="kv-work-num"><?php echo esc_html( $p[0] ); ?></span><span class="kv-work-name"><?php echo esc_html( $p[1] ); ?></span><span class="kv-work-year">—</span></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="work">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">14 sites · since MMXVIII</p><h1 class="kv-hero__head">works.</h1><div class="kv-asterism">⁂</div></header>
    <section class="kv-work"><ul class="kv-work-list"><?php $rom=['i.','ii.','iii.','iv.','v.','vi.']; foreach ( $on1_content['work'] as $i => $w ) : ?><li><span class="kv-work-num"><?php echo esc_html( $rom[$i] ?? '·' ); ?></span><span class="kv-work-name"><?php echo esc_html( $w['title'] . ' · ' . $w['lede'] ); ?></span><span class="kv-work-year"><?php echo esc_html( $w['year'] ); ?></span></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="pricing">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">Three engagements</p><h1 class="kv-hero__head">pricing.</h1><div class="kv-asterism">⁂</div></header>
    <section class="kv-tiers"><?php foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="kv-tier<?php echo $f ? ' kv-tier--feat' : ''; ?>"><p class="kv-eyebrow"><?php echo esc_html( $t['name'] ); ?></p><h3><?php echo esc_html( $t['price'] ); ?></h3><p class="kv-faint"><?php echo esc_html( $t['sub'] ); ?></p><ul><?php foreach ( $t['features'] as $feat ) : ?><li><?php echo esc_html( $feat ); ?></li><?php endforeach; ?></ul></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">Common questions</p><h1 class="kv-hero__head">faq.</h1><div class="kv-asterism">⁂</div></header>
    <section class="kv-faq"><?php $rom=['i.','ii.','iii.','iv.','v.']; foreach ( $on1_content['faq'] as $i => $f ) : ?><details<?php echo $i === 0 ? ' open' : ''; ?>><summary><span class="kv-work-num"><?php echo esc_html( $rom[$i] ?? '·' ); ?></span> <?php echo esc_html( $f['q'] ); ?></summary><p><?php echo esc_html( $f['a'] ); ?></p></details><?php endforeach; ?></section>
</div>
<div data-skin-page="contact">
    <header class="kv-hero"><div class="kv-asterism">⁂</div><p class="kv-eyebrow">Send a brief · 72h</p><h1 class="kv-hero__head">contact.</h1><div class="kv-asterism">⁂</div></header>
    <section class="kv-contact"><form class="kv-form" onsubmit="event.preventDefault();"><label>Name<input type="text"></label><label>Email<input type="email"></label><label>Studio<input type="text"></label><label>Brief<textarea rows="4"></textarea></label><button type="submit">Send brief →</button></form>
        <ul class="kv-info"><li><p class="kv-eyebrow">— Email</p><p><?php echo esc_html( $on1_content['identity']['email'] ); ?></p></li><li><p class="kv-eyebrow">— Location</p><p>Wien · UTC+1</p></li><li><p class="kv-eyebrow">— Response</p><p>72 hours</p></li><li><p class="kv-eyebrow">— Booking</p><p>Q3 · slots open</p></li></ul>
    </section>
</div>
