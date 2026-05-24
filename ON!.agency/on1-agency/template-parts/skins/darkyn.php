<?php
/** Skin: Orbit · dark + orange planet · big display */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="dk-nav">
    <a class="dk-nav__brand" data-skin-link="home">ON1.</a>
    <ul class="dk-nav__links"><li><a data-skin-link="home" class="is-active">HOME</a></li><li><a data-skin-link="about">ABOUT</a></li><li><a data-skin-link="work">WORK</a></li><li><a data-skin-link="pricing">PRICING</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">CONTACT</a></li></ul>
    <a class="dk-nav__cart" data-skin-link="contact">🛒 0</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="dk-hero">
        <div class="dk-hero__planet"></div>
        <div class="dk-hero__grid">
            <h1 class="dk-hero__word dk-hero__word--left">AIM</h1>
            <div class="dk-hero__mid"><p class="dk-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p><a class="dk-hero__link" data-skin-link="work">Projects <span>→</span></a></div>
            <h1 class="dk-hero__word dk-hero__word--right">SKY</h1>
        </div>
        <div class="dk-hero__statement"><span class="dk-hero__bar"></span><p>WHERE CRAFT<br>MEETS CODE.</p></div>
    </header>
    <section class="dk-clients"><span>⚡ Boltshift</span><span>+ Clandestine</span><span>% Biosynthesis</span><span>⊞ BuildingBlocks</span><span>≋ Alt+Shift</span><span>◐ CloudWatch</span></section>
    <section class="dk-stats">
        <div class="dk-stat dk-stat--orange"><div class="dk-stat__dot"></div><div class="dk-stat__label">Numbers and Facts</div></div>
        <div class="dk-stat"><div class="dk-stat__dot"></div><div class="dk-stat__num">14+</div><div class="dk-stat__sub">Sites shipped<br>since 2018</div></div>
        <div class="dk-stat"><div class="dk-stat__dot"></div><div class="dk-stat__num">72h</div><div class="dk-stat__sub">Reply<br>window</div></div>
        <div class="dk-stat dk-stat--light"><div class="dk-stat__dot"></div><div class="dk-stat__num">3<small>mo</small></div><div class="dk-stat__sub">Avg.<br>engagement</div></div>
    </section>
    <section class="dk-services"><h2 class="dk-services__heading">SERVICES</h2><div class="dk-services__divider"></div><ol class="dk-services__list"><?php foreach ( $on1_content['services'] as $i => $s ) : ?><li><span class="dk-num"><?php printf( '%02d', $i + 1 ); ?></span><span class="dk-name"><?php echo esc_html( strtoupper( $s['name'] ) ); ?></span><span class="dk-arrow">↗</span></li><?php endforeach; ?></ol></section>
    <footer class="dk-foot"><span>ON1. © 2026</span><span class="dk-foot__center">VIENNA · ATELIER</span><span>BOOKING Q3 — SLOTS OPEN</span></footer>
</div>
<div data-skin-page="about">
    <section class="dk-page-hero"><p class="dk-eyebrow">— ATELIER · VIENNA</p><h1 class="dk-page-hero__head">ABOUT<span class="dk-acc">.</span></h1><p class="dk-page-hero__lede">One-person digital atelier. Strategy, design, build — and the senior pair of eyes you need at the end.</p></section>
    <section class="dk-services"><p class="dk-eyebrow">— PRINCIPLES</p><h2 class="dk-services__heading">WHAT WE BELIEVE</h2><div class="dk-services__divider"></div><ol class="dk-services__list"><?php foreach ( $on1_content['principles'] as $i => $p ) : ?><li><span class="dk-num"><?php printf( '%02d', $i + 1 ); ?></span><span class="dk-name"><?php echo esc_html( strtoupper( $p[1] ) ); ?></span><span class="dk-arrow">↗</span></li><?php endforeach; ?></ol></section>
</div>
<div data-skin-page="work">
    <section class="dk-page-hero"><p class="dk-eyebrow">— 14 SITES · SINCE 2018</p><h1 class="dk-page-hero__head">WORK<span class="dk-acc">.</span></h1></section>
    <section class="dk-work"><div class="dk-work__grid"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><div class="dk-work-card"><div class="dk-work-card__media<?php echo $i % 2 ? ' dk-work-card__media--alt' : ''; ?>"></div><div class="dk-work-card__num"><?php printf( '%02d', $i + 1 ); ?> · <?php echo esc_html( $w['year'] ); ?></div><h3 class="dk-work-card__title"><?php echo esc_html( $w['title'] ); ?></h3><p class="dk-work-card__lede"><?php echo esc_html( $w['lede'] ); ?></p></div><?php endforeach; ?></div></section>
</div>
<div data-skin-page="pricing">
    <section class="dk-page-hero"><p class="dk-eyebrow">— FOUR TIERS</p><h1 class="dk-page-hero__head">PRICING<span class="dk-acc">.</span></h1><p class="dk-page-hero__lede">Strategy always included. One person. No surprises.</p></section>
    <section class="dk-tiers"><?php foreach ( $on1_content['tiers'] as $t ) : $f = ! empty( $t['featured'] ); ?><div class="dk-tier<?php echo $f ? ' dk-tier--featured' : ''; ?>"><p class="dk-tier__name"><?php echo esc_html( strtoupper( $t['name'] ) ); ?></p><h3 class="dk-tier__price"><?php echo esc_html( $t['price'] ); ?></h3><p class="dk-tier__sub"><?php echo esc_html( $t['sub'] ); ?></p><ul class="dk-tier__feat"><?php foreach ( $t['features'] as $feat ) : ?><li><?php echo esc_html( $feat ); ?></li><?php endforeach; ?></ul><a class="dk-tier__btn">CHOOSE <?php echo esc_html( strtoupper( $t['name'] ) ); ?> <span>↗</span></a></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq">
    <section class="dk-page-hero"><p class="dk-eyebrow">— COMMON QUESTIONS</p><h1 class="dk-page-hero__head">FAQ<span class="dk-acc">.</span></h1></section>
    <section class="dk-faq"><?php foreach ( $on1_content['faq'] as $i => $f ) : ?><details class="dk-faq__item"<?php echo $i === 0 ? ' open' : ''; ?>><summary><span class="dk-num"><?php printf( '%02d', $i + 1 ); ?></span><span><?php echo esc_html( strtoupper( $f['q'] ) ); ?></span><span class="dk-arrow">+</span></summary><div class="dk-faq__a"><?php echo esc_html( $f['a'] ); ?></div></details><?php endforeach; ?></section>
</div>
<div data-skin-page="contact">
    <section class="dk-page-hero"><p class="dk-eyebrow">— GET IN TOUCH</p><h1 class="dk-page-hero__head">CONTACT<span class="dk-acc">.</span></h1></section>
    <section class="dk-contact">
        <div class="dk-contact__form"><p class="dk-eyebrow">— START A BRIEF</p><form class="dk-form" onsubmit="event.preventDefault();"><label class="dk-field"><span>YOUR NAME</span><input type="text"></label><label class="dk-field"><span>EMAIL</span><input type="email"></label><label class="dk-field"><span>STUDIO</span><input type="text"></label><label class="dk-field"><span>BRIEF</span><textarea rows="4"></textarea></label><button class="dk-cta__btn" type="submit">SEND BRIEF <span>↗</span></button></form></div>
        <div class="dk-contact__info"><div class="dk-contact-block"><span class="dk-contact-block__bar"></span><p class="dk-eyebrow">— EMAIL</p><p><?php echo esc_html( $on1_content['identity']['email'] ); ?></p></div><div class="dk-contact-block"><span class="dk-contact-block__bar"></span><p class="dk-eyebrow">— LOCATION</p><p>Wien · UTC+1</p></div><div class="dk-contact-block"><span class="dk-contact-block__bar"></span><p class="dk-eyebrow">— RESPONSE</p><p>Within 72h</p></div><div class="dk-contact-block"><span class="dk-contact-block__bar"></span><p class="dk-eyebrow">— BOOKING</p><p>Q3 · slots open</p></div></div>
    </section>
</div>
