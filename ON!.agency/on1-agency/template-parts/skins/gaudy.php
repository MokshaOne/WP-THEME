<?php
/** Skin: Revolt · dark + crimson · heavy display */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="gd-nav">
    <a class="gd-nav__brand" data-skin-link="home">on1<span class="gd-r">.</span>agency</a>
    <ul class="gd-nav__links"><li><a data-skin-link="home" class="gd-active is-active">Home</a></li><li><a data-skin-link="about">About</a></li><li><a data-skin-link="work">Work</a></li><li><a data-skin-link="pricing">Pricing</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">Contact</a></li></ul>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">We</span> Design,<br><span class="gd-r">We</span> Create,<br><span class="gd-r">We Are</span><br>One Studio<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><div class="gd-card"><h3>Concept<br>Design</h3><p><?php echo esc_html( $on1_content['services'][0]['lede'] ?? '' ); ?></p></div><div class="gd-card gd-card--accent"><h3>Editorial<br>Design</h3><p><?php echo esc_html( $on1_content['services'][1]['lede'] ?? '' ); ?></p></div><div class="gd-card"><h3>Brand<br>Identity</h3><p><?php echo esc_html( $on1_content['services'][2]['lede'] ?? '' ); ?></p></div></section>
    <section class="gd-story"><div class="gd-story__img"></div><div><p class="gd-eyebrow">— What we do for you</p><h2 class="gd-section-head">A little story about our studio</h2><p><?php echo esc_html( $on1_content['lede'] ); ?></p><a class="gd-btn gd-btn--accent" data-skin-link="about">LEARN MORE</a></div></section>
    <footer class="gd-foot"><h3>on1<span class="gd-r">.</span>agency</h3><span>©2026 · WIEN</span></footer>
</div>
<div data-skin-page="about"><header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">about</span><br>the studio<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><?php foreach ( array_slice( $on1_content['principles'], 0, 3 ) as $i => $p ) : ?><div class="gd-card<?php echo $i === 1 ? ' gd-card--accent' : ''; ?>"><h3><?php echo esc_html( $p[1] ); ?></h3></div><?php endforeach; ?></section>
</div>
<div data-skin-page="work"><header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">recent</span><br>works<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><?php foreach ( array_slice( $on1_content['work'], 0, 3 ) as $i => $w ) : ?><div class="gd-card<?php echo $i === 1 ? ' gd-card--accent' : ''; ?>"><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></section>
    <section class="gd-cards" style="margin-top:1rem"><?php foreach ( array_slice( $on1_content['work'], 3, 3 ) as $w ) : ?><div class="gd-card"><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></section>
</div>
<div data-skin-page="pricing"><header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">three</span><br>engagements<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><?php foreach ( $on1_content['tiers'] as $t ) : $f = ! empty( $t['featured'] ); ?><div class="gd-card<?php echo $f ? ' gd-card--accent' : ''; ?>"><h3><?php echo esc_html( $t['name'] ); ?></h3><p style="color:#E5174A;font-size:2rem;font-weight:800;margin:0.5rem 0"><?php echo esc_html( $t['price'] ); ?></p><p><?php echo esc_html( $t['sub'] ); ?></p></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq"><header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">frequently</span><br>asked<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><?php foreach ( array_slice( $on1_content['faq'], 0, 3 ) as $i => $f ) : ?><div class="gd-card<?php echo $i === 1 ? ' gd-card--accent' : ''; ?>"><h3><?php echo esc_html( $f['q'] ); ?></h3><p><?php echo esc_html( $f['a'] ); ?></p></div><?php endforeach; ?></section>
</div>
<div data-skin-page="contact"><header class="gd-hero"><h1 class="gd-hero__head"><span class="gd-r">send</span><br>a brief<span class="gd-r">.</span></h1></header>
    <section class="gd-cards"><div class="gd-card"><h3>Email</h3><p><?php echo esc_html( $on1_content['identity']['email'] ); ?></p></div><div class="gd-card gd-card--accent"><h3>Location</h3><p>Wien · UTC+1</p></div><div class="gd-card"><h3>Response</h3><p>72 hours</p></div></section>
</div>
