<?php
/** Skin: viennaphotogroup.com · editorial community magazine · cream + sienna + Playfair */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<nav class="vpc-nav">
    <a class="vpc-brand" data-skin-link="home"><span class="vpc-mark">⬮</span> on1<span class="vpc-amp">.</span>agency</a>
    <ul class="vpc-nav__links">
        <li><a data-skin-link="home" class="is-active">Home</a></li>
        <li><a data-skin-link="about">About</a></li>
        <li><a data-skin-link="work">Work</a></li>
        <li><a data-skin-link="pricing">Pricing</a></li>
        <li><a data-skin-link="faq">FAQ</a></li>
        <li><a data-skin-link="contact">Contact</a></li>
    </ul>
    <a class="vpc-join" data-skin-link="contact">Become a member</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="vpc-hero">
        <span class="vpc-badge">— editorial · vienna · 2018</span>
        <h1 class="vpc-hero__head">A quiet digital<br>atelier in <em>Vienna</em>.</h1>
        <p class="vpc-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p>
        <div class="vpc-hero__row"><a class="vpc-btn" data-skin-link="contact">Start a brief →</a><a class="vpc-link" data-skin-link="work">View work</a></div>
    </header>
    <section class="vpc-categories">
        <a class="vpc-chip vpc-chip--mag">◐ Magazine</a>
        <a class="vpc-chip vpc-chip--loc">● Locations</a>
        <a class="vpc-chip vpc-chip--studio">○ Studios</a>
        <a class="vpc-chip vpc-chip--shop">◇ Shop</a>
        <a class="vpc-chip vpc-chip--event">◆ Events</a>
        <a class="vpc-chip vpc-chip--review">⊛ Buying guide</a>
        <a class="vpc-chip vpc-chip--tut">⁂ Tutorials</a>
    </section>
    <div class="vpc-asterism" aria-hidden="true">⁕</div>
    <section class="vpc-mag">
        <p class="vpc-eyebrow">— Recent issues</p>
        <h2 class="vpc-section-head">The Atelier <em>Quarterly</em>.</h2>
        <div class="vpc-mag-grid">
            <?php foreach ( array_slice( $on1_content['work'], 0, 3 ) as $i => $w ) : ?>
                <article class="vpc-card<?php echo $i === 0 ? ' vpc-card--lg' : ''; ?>"><div class="vpc-card__photo<?php echo $i % 2 ? ' vpc-card__photo--alt' : ''; ?>"></div><span class="vpc-tag vpc-tag--studio">STUDIO</span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></article>
            <?php endforeach; ?>
        </div>
    </section>
    <footer class="vpc-foot">
        <span class="vpc-brand"><span class="vpc-mark">⬮</span> on1<span class="vpc-amp">.</span>agency</span>
        <span>Wien · 2026 · Booking Q3 open</span>
        <span class="vpc-member">● Member-run · est. 2018</span>
    </footer>
</div>
<div data-skin-page="about">
    <header class="vpc-hero"><span class="vpc-badge">— Founder · since 2018</span><h1 class="vpc-hero__head">A one-person<br><em>atelier</em>.</h1><p class="vpc-hero__lede">Nishuthan Raveenthiran. Eight years building editorial WordPress sites.</p></header>
    <section class="vpc-mag"><p class="vpc-eyebrow">— Principles</p><h2 class="vpc-section-head">Four <em>principles</em>.</h2><div class="vpc-mag-grid" style="grid-template-columns:repeat(2,1fr)"><?php foreach ( $on1_content['principles'] as $p ) : ?><article class="vpc-card"><span class="vpc-tag vpc-tag--studio"><?php echo esc_html( strtoupper( $p[0] ) ); ?></span><h3><?php echo esc_html( $p[1] ); ?></h3></article><?php endforeach; ?></div></section>
</div>
<div data-skin-page="work">
    <header class="vpc-hero"><span class="vpc-badge">— 14 sites · since 2018</span><h1 class="vpc-hero__head">Selected <em>works</em>.</h1></header>
    <section class="vpc-mag"><div class="vpc-mag-grid" style="grid-template-columns:repeat(3,1fr)"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><article class="vpc-card"><div class="vpc-card__photo<?php echo $i % 2 ? ' vpc-card__photo--alt' : ''; ?>"></div><span class="vpc-tag vpc-tag--studio">STUDIO</span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></article><?php endforeach; ?></div></section>
</div>
<div data-skin-page="pricing">
    <header class="vpc-hero"><span class="vpc-badge">— Three engagements</span><h1 class="vpc-hero__head"><em>Pricing</em>.</h1></header>
    <section class="vpc-tiers"><?php foreach ( (array) ( $on1_content['tiers'] ?? [] ) as $i => $t ) :
        $f    = ! empty( $t['featured'] ) && $t['featured'] !== '0';
        $name = wp_strip_all_tags( (string) ( $t['name']  ?? '' ) );
        $sub  = (string) ( $t['period'] ?? $t['sub']  ?? '' );
        $price= (string) ( $t['price']  ?? '' );
        $feats= (array)  ( $t['features'] ?? [] );
    ?><div class="vpc-tier<?php echo $f ? ' vpc-tier--feat' : ''; ?>"><p class="vpc-eyebrow"><?php echo esc_html( strtoupper( $name ) ); ?></p><h3><?php echo esc_html( $price ); ?></h3><p style="color:#6B5A48;margin:0 0 0.5rem"><?php echo esc_html( $sub ); ?></p><ul><?php foreach ( $feats as $feat ) : ?><li><?php echo wp_kses( (string) $feat, [ 'em' => [], 'strong' => [] ] ); ?></li><?php endforeach; ?></ul></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq">
    <header class="vpc-hero"><span class="vpc-badge">— Frequently asked</span><h1 class="vpc-hero__head">FAQ<em>.</em></h1></header>
    <section class="vpc-faq"><?php foreach ( $on1_content['faq'] as $i => $f ) : ?><details<?php echo $i === 0 ? ' open' : ''; ?>><summary><strong><?php printf( '%02d', $i + 1 ); ?>.</strong> <?php echo esc_html( $f['q'] ); ?></summary><p><?php echo esc_html( $f['a'] ); ?></p></details><?php endforeach; ?></section>
</div>
<div data-skin-page="contact">
    <header class="vpc-hero"><span class="vpc-badge">— Send a brief · 72h</span><h1 class="vpc-hero__head">Contact<em>.</em></h1></header>
    <section class="vpc-contact">
        <form class="vpc-form" onsubmit="event.preventDefault();"><label>Your name<input type="text"></label><label>Email<input type="email"></label><label>Studio<input type="text"></label><label>Brief<textarea rows="4"></textarea></label><button class="vpc-btn" type="submit">Send brief →</button></form>
        <ul class="vpc-info"><li><p class="vpc-eyebrow">— Email</p><p><?php echo esc_html( $on1_content['identity']['email'] ); ?></p></li><li><p class="vpc-eyebrow">— Location</p><p>Wien · UTC+1</p></li><li><p class="vpc-eyebrow">— Response</p><p>72 hours</p></li><li><p class="vpc-eyebrow">— Booking</p><p>Q3 · open</p></li></ul>
    </section>
</div>
