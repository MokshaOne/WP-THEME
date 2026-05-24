<?php
/**
 * Bauhaus — front-page.php
 * Pulls all content from inc/helpers.php (Customizer-backed),
 * then renders the homepage in the Bauhaus design language.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$identity   = bauhaus_get_identity();
$hero       = bauhaus_get_hero();
$on1_content = [
    'identity'   => $identity,
    'lede'       => $hero['lede'],
    'principles' => bauhaus_get_principles(),
    'services'   => bauhaus_get_services(),
    'work'       => bauhaus_get_work(),
    'tiers'      => bauhaus_get_tiers(),
    'faq'        => bauhaus_get_faq(),
];

get_header();
?>
<main id="main" class="gw-main">

<nav class="gw-nav">
    <a class="gw-nav__brand" data-skin-link="home">BAUHAUS<sup>·on1</sup></a>
    <ul class="gw-nav__links"><li><a data-skin-link="home" class="is-active">HOME</a></li><li><a data-skin-link="about">ABOUT</a></li><li><a data-skin-link="work">WORK</a></li><li><a data-skin-link="pricing">PRICING</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">CONTACT</a></li></ul>
    <a class="gw-nav__cta" data-skin-link="contact">⏷ LET'S TALK</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="gw-hero"><p class="gw-eyebrow">/ DIGITAL AGENCY · STUDIO</p><h1 class="gw-hero__head">DIGITAL<br>EXPERIENCES<br><span class="gw-y">THROUGH</span> CRAFT<br>&amp; <em>CODE.</em></h1><div class="gw-hero__bottom"><div><span class="gw-eyebrow">DIGITAL AGENCY</span></div><div class="gw-hero__nav"><span>PREV ←</span><span>→ NEXT</span></div></div></header>
    <section class="gw-stats"><div class="gw-stat"><h3>14<sup>+</sup></h3><p>SITES SHIPPED</p></div><div class="gw-stat gw-stat--y"><h3>72<sup>h</sup></h3><p>REPLY WINDOW</p></div><div class="gw-stat"><h3>3<sup>mo</sup></h3><p>AVG ENGAGEMENT</p></div><div class="gw-stat"><h3>2018</h3><p>EST.</p></div></section>
    <section class="gw-services"><h2 class="gw-section-head">WHAT WE BUILD <span class="gw-y">/</span></h2><div class="gw-grid"><?php foreach ( $on1_content['services'] as $i => $s ) : ?><div class="gw-card"><span class="gw-num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $s['name'] ) ); ?></h3><p><?php echo esc_html( $s['lede'] ); ?></p></div><?php endforeach; ?></div></section>
    <footer class="gw-foot"><h3>BAUHAUS<sup>·on1</sup></h3><span>WIEN · 2026</span><span class="gw-y">⏵ BOOKING Q3</span></footer>
</div>
<div data-skin-page="about"><header class="gw-hero"><p class="gw-eyebrow">/ FOUNDER · SOLO PRACTICE</p><h1 class="gw-hero__head">ABOUT THE<br><span class="gw-y">STUDIO</span><em>.</em></h1></header><section class="gw-services"><h2 class="gw-section-head">PRINCIPLES <span class="gw-y">/</span></h2><div class="gw-grid"><?php foreach ( $on1_content['principles'] as $i => $p ) : ?><div class="gw-card"><span class="gw-num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $p[1] ) ); ?></h3></div><?php endforeach; ?></div></section></div>
<div data-skin-page="work"><header class="gw-hero"><p class="gw-eyebrow">/ 14 SITES · SINCE 2018</p><h1 class="gw-hero__head">RECENT<br><span class="gw-y">BUILDS</span><em>.</em></h1></header><section class="gw-services"><div class="gw-grid"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><div class="gw-card"><span class="gw-num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $w['title'] ) ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="pricing"><header class="gw-hero"><p class="gw-eyebrow">/ THREE ENGAGEMENTS</p><h1 class="gw-hero__head">PRICING<br><span class="gw-y">TIERS</span><em>.</em></h1></header><section class="gw-services"><div class="gw-grid" style="grid-template-columns:repeat(3,1fr)"><?php foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="gw-card"<?php echo $f ? ' style="background:#F2D200;color:#050505"' : ''; ?>><span class="gw-num"<?php echo $f ? ' style="color:#050505"' : ''; ?>><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $t['name'] ) ); ?></h3><p><?php echo esc_html( $t['price'] . ' · ' . $t['sub'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="faq"><header class="gw-hero"><p class="gw-eyebrow">/ FREQUENTLY ASKED</p><h1 class="gw-hero__head">FAQ<em>.</em></h1></header><section class="gw-services"><div class="gw-grid"><?php foreach ( $on1_content['faq'] as $i => $f ) : ?><div class="gw-card"><span class="gw-num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $f['q'] ) ); ?></h3><p><?php echo esc_html( $f['a'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="contact"><header class="gw-hero"><p class="gw-eyebrow">/ SEND A BRIEF · 72H</p><h1 class="gw-hero__head">CONTACT<em>.</em></h1></header><section class="gw-services"><div class="gw-grid" style="grid-template-columns:repeat(4,1fr)"><div class="gw-card"><span class="gw-num">EMAIL</span><h3><?php echo esc_html( $on1_content['identity']['email'] ); ?></h3></div><div class="gw-card"><span class="gw-num">LOC</span><h3>WIEN · UTC+1</h3></div><div class="gw-card"><span class="gw-num">REPLY</span><h3>72H</h3></div><div class="gw-card" style="background:#F2D200;color:#050505"><span class="gw-num" style="color:#050505">Q3</span><h3>BOOKING OPEN</h3></div></div></section></div>

</main>
<?php get_footer();
