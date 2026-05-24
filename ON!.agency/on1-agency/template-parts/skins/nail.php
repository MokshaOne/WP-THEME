<?php
/** Skin: nailfluence.studio · noir + chrome editorial · Italiana + Bodoni Moda */
if ( ! defined( 'ABSPATH' ) ) exit;
$id = $on1_content['identity'];
?>
<nav class="nf-nav">
    <a class="nf-brand nf-chrome" data-skin-link="home">on1.agency<small>®</small></a>
    <ul class="nf-nav__links">
        <li><a data-skin-link="home" class="is-active">Index</a></li>
        <li><a data-skin-link="work">Portfolio</a></li>
        <li><a data-skin-link="about">About</a></li>
        <li><a data-skin-link="pricing">Services</a></li>
        <li><a data-skin-link="faq">FAQ</a></li>
        <li><a data-skin-link="contact">Contact</a></li>
    </ul>
    <div class="nf-nav__actions"><a class="nf-link">Bag · 00</a><a class="nf-cta" data-skin-link="contact">Book now</a></div>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="nf-hero">
        <p class="nf-eyebrow">— Atelier · Vienna · Est. 2018</p>
        <h1 class="nf-display nf-chrome">on1<em>.</em>agency</h1>
        <p class="nf-lede"><?php echo esc_html( $on1_content['lede'] ); ?></p>
        <div class="nf-actions"><a class="nf-cta nf-cta--lg" data-skin-link="contact">Book a consultation</a><a class="nf-link nf-link--ul" data-skin-link="work">View portfolio →</a></div>
    </header>
    <section class="nf-strip"><span>EDITORIAL · WORDPRESS</span><span class="nf-dot">●</span><span>DESIGN · SYSTEMS</span><span class="nf-dot">●</span><span>BRAND · IDENTITY</span><span class="nf-dot">●</span><span>FRONT-END · DEV</span></section>
    <section class="nf-portfolio">
        <p class="nf-eyebrow">— Recent portfolio sets</p>
        <h2 class="nf-section-head">Selected <em>work</em>.</h2>
        <div class="nf-grid"><?php foreach ( array_slice( $on1_content['work'], 0, 3 ) as $i => $w ) : ?><article class="nf-card"><div class="nf-media<?php echo $i % 2 ? ' nf-media--alt' : ''; ?>"></div><span class="nf-tag"><?php echo esc_html( $w['year'] ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] ); ?></p></article><?php endforeach; ?></div>
    </section>
    <footer class="nf-foot"><h3 class="nf-chrome">on1.agency</h3><p>© MMXXVI · Wien · Booking Q3 open</p></footer>
</div>
<div data-skin-page="about">
    <header class="nf-hero"><p class="nf-eyebrow">— Founder · Solo Practice</p><h1 class="nf-display nf-chrome">about<em>.</em></h1><p class="nf-lede">Nishuthan Raveenthiran. A one-person Vienna atelier. Eight years building editorial WordPress sites.</p></header>
    <section class="nf-portfolio"><p class="nf-eyebrow">— Working principles</p><h2 class="nf-section-head">Four <em>principles</em>.</h2><div class="nf-grid"><?php foreach ( $on1_content['principles'] as $p ) : ?><article class="nf-card nf-card--text"><span class="nf-tag"><?php echo esc_html( $p[0] ); ?></span><h3><?php echo esc_html( $p[1] ); ?></h3></article><?php endforeach; ?></div></section>
</div>
<div data-skin-page="work">
    <header class="nf-hero"><p class="nf-eyebrow">— 14 sites · since MMXVIII</p><h1 class="nf-display nf-chrome">portfolio<em>.</em></h1></header>
    <section class="nf-portfolio"><div class="nf-grid"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><article class="nf-card"><div class="nf-media<?php echo $i % 2 ? ' nf-media--alt' : ''; ?>"></div><span class="nf-tag"><?php echo esc_html( $w['year'] ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] ); ?></p></article><?php endforeach; ?></div></section>
</div>
<div data-skin-page="pricing">
    <header class="nf-hero"><p class="nf-eyebrow">— All-inclusive engagements</p><h1 class="nf-display nf-chrome">services<em>.</em></h1></header>
    <section class="nf-tiers"><?php $rom = [ 'I.', 'II.', 'III.' ]; foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="nf-tier<?php echo $f ? ' nf-tier--feat' : ''; ?>"><p class="nf-tag"><?php echo esc_html( ( $rom[ $i ] ?? '·' ) . ' ' . strtoupper( $t['name'] ) ); ?></p><h3 class="nf-chrome"><?php echo esc_html( $t['price'] ); ?></h3><p class="nf-italic"><?php echo esc_html( $t['sub'] ); ?></p><ul><?php foreach ( $t['features'] as $feat ) : ?><li><?php echo esc_html( $feat ); ?></li><?php endforeach; ?></ul></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq">
    <header class="nf-hero"><p class="nf-eyebrow">— Frequently asked</p><h1 class="nf-display nf-chrome">faq<em>.</em></h1></header>
    <section class="nf-faq"><?php $rom = [ 'i.', 'ii.', 'iii.', 'iv.', 'v.' ]; foreach ( $on1_content['faq'] as $i => $f ) : ?><details<?php echo $i === 0 ? ' open' : ''; ?>><summary><span class="nf-tag"><?php echo esc_html( $rom[ $i ] ?? '·' ); ?></span><?php echo esc_html( $f['q'] ); ?></summary><p><?php echo esc_html( $f['a'] ); ?></p></details><?php endforeach; ?></section>
</div>
<div data-skin-page="contact">
    <header class="nf-hero"><p class="nf-eyebrow">— Send a brief · 72h response</p><h1 class="nf-display nf-chrome">contact<em>.</em></h1></header>
    <section class="nf-contact">
        <form class="nf-form" onsubmit="event.preventDefault();"><label>Your name<input type="text"></label><label>Email<input type="email"></label><label>Studio<input type="text"></label><label>Brief<textarea rows="4"></textarea></label><button class="nf-cta nf-cta--lg" type="submit">Send brief →</button></form>
        <ul class="nf-info"><li><span class="nf-tag">— EMAIL</span><p><?php echo esc_html( $id['email'] ); ?></p></li><li><span class="nf-tag">— LOCATION</span><p>Wien, Austria · UTC+1</p></li><li><span class="nf-tag">— RESPONSE</span><p>Within 72 hours</p></li><li><span class="nf-tag">— BOOKING</span><p>Q3 · slots open</p></li></ul>
    </section>
</div>
