<?php
/** Skin: Coterie · cream community fantasy · cream + red */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="vp-nav">
    <a class="vp-nav__brand" data-skin-link="home">⬮ on1.agency</a>
    <ul class="vp-nav__links"><li><a data-skin-link="home" class="is-active">Home</a></li><li><a data-skin-link="about">About</a></li><li><a data-skin-link="work">Work</a></li><li><a data-skin-link="pricing">Pricing</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">Contact</a></li></ul>
    <a class="vp-nav__cta" data-skin-link="contact">Join Studio</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="vp-hero"><p class="vp-eyebrow">— Vienna · Editorial Studio</p><h1 class="vp-hero__head">A quiet digital<br>atelier in <em>Wien</em>.</h1><p class="vp-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p><div class="vp-hero__row"><a class="vp-btn" data-skin-link="contact">Join the studio</a><a class="vp-link" data-skin-link="work">View work →</a></div></header>
    <section class="vp-members"><p class="vp-eyebrow">— Studio projects</p><h2 class="vp-section-head">Recent builds.</h2><div class="vp-grid"><?php foreach ( array_slice( $on1_content['work'], 0, 4 ) as $i => $w ) : ?><div class="vp-card"><div class="vp-card__photo<?php echo $i % 2 ? ' vp-card__photo--alt' : ''; ?>"></div><h4><?php echo esc_html( $w['title'] ); ?></h4><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></div></section>
    <section class="vp-cta"><h2>Ship a site that looks like <em>your</em> studio.</h2><a class="vp-btn" data-skin-link="contact">Start a brief →</a></section>
    <footer class="vp-foot"><span>⬮ on1.agency</span><span>Wien · 2026</span></footer>
</div>
<div data-skin-page="about"><header class="vp-hero"><p class="vp-eyebrow">— Founder</p><h1 class="vp-hero__head">A one-person<br><em>atelier</em>.</h1><p class="vp-hero__lede">Nishuthan Raveenthiran. Eight years building editorial WordPress sites.</p></header>
    <section class="vp-members"><p class="vp-eyebrow">— Principles</p><div class="vp-grid"><?php foreach ( $on1_content['principles'] as $p ) : ?><div class="vp-card"><h4><?php echo esc_html( $p[0] . ' ' . $p[1] ); ?></h4></div><?php endforeach; ?></div></section>
</div>
<div data-skin-page="work"><header class="vp-hero"><p class="vp-eyebrow">— 14 sites · since 2018</p><h1 class="vp-hero__head">Selected <em>works</em>.</h1></header>
    <section class="vp-members"><div class="vp-grid"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><div class="vp-card"><div class="vp-card__photo<?php echo $i % 2 ? ' vp-card__photo--alt' : ''; ?>"></div><h4><?php echo esc_html( $w['title'] ); ?></h4><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></div></section>
</div>
<div data-skin-page="pricing"><header class="vp-hero"><p class="vp-eyebrow">— Three engagements</p><h1 class="vp-hero__head"><em>Pricing</em>.</h1></header>
    <section class="vp-members"><div class="vp-grid" style="grid-template-columns:repeat(3,1fr)"><?php foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="vp-card"<?php echo $f ? ' style="border-color:#C44536"' : ''; ?>><p class="vp-eyebrow"><?php echo esc_html( strtoupper( $t['name'] ) ); ?></p><h4 style="font-size:1.8rem;color:#C44536"><?php echo esc_html( $t['price'] ); ?></h4><p><?php echo esc_html( $t['sub'] ); ?></p></div><?php endforeach; ?></div></section>
</div>
<div data-skin-page="faq"><header class="vp-hero"><p class="vp-eyebrow">— Frequently asked</p><h1 class="vp-hero__head"><em>FAQ</em>.</h1></header>
    <section class="vp-members"><div class="vp-grid"><?php foreach ( $on1_content['faq'] as $f ) : ?><div class="vp-card"><h4><?php echo esc_html( $f['q'] ); ?></h4><p><?php echo esc_html( $f['a'] ); ?></p></div><?php endforeach; ?></div></section>
</div>
<div data-skin-page="contact"><header class="vp-hero"><p class="vp-eyebrow">— Send a brief · 72h</p><h1 class="vp-hero__head"><em>Contact</em>.</h1></header>
    <section class="vp-members"><div class="vp-grid" style="grid-template-columns:repeat(4,1fr)"><div class="vp-card"><p class="vp-eyebrow">— Email</p><h4><?php echo esc_html( $on1_content['identity']['email'] ); ?></h4></div><div class="vp-card"><p class="vp-eyebrow">— Location</p><h4>Wien · UTC+1</h4></div><div class="vp-card"><p class="vp-eyebrow">— Response</p><h4>72 hours</h4></div><div class="vp-card"><p class="vp-eyebrow">— Booking</p><h4>Q3 · open</h4></div></div></section>
</div>
