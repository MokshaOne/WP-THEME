<?php
/**
 * Monolith — front-page.php
 * Pulls all content from inc/helpers.php (Customizer-backed),
 * then renders the homepage in the Monolith design language.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$identity   = monolith_get_identity();
$hero       = monolith_get_hero();
$on1_content = [
    'identity'   => $identity,
    'lede'       => $hero['lede'],
    'principles' => monolith_get_principles(),
    'services'   => monolith_get_services(),
    'work'       => monolith_get_work(),
    'tiers'      => monolith_get_tiers(),
    'faq'        => monolith_get_faq(),
];

get_header();
?>
<main id="main" class="gz-main">

<nav class="gz-nav">
    <ul class="gz-nav__links"><li><a data-skin-link="home" class="is-active">HOME</a></li><li><a data-skin-link="about">ABOUT</a></li><li><a data-skin-link="work">WORK</a></li><li><a data-skin-link="pricing">PRICING</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">CONTACT</a></li></ul>
    <a class="gz-nav__brand" data-skin-link="home">⁕ ON1</a>
    <a class="gz-nav__cta" data-skin-link="contact">START PROJECT</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— ATELIER · VIENNA · SINCE 2018</p><h1 class="gz-hero__head">on1.<br>agency</h1><p class="gz-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p><a class="gz-btn" data-skin-link="contact">START A PROJECT →</a></header>
    <section class="gz-stats"><div class="gz-stat"><h3>14<sup>+</sup></h3><p>Sites shipped</p></div><div class="gz-stat"><h3>72<sup>h</sup></h3><p>Reply window</p></div><div class="gz-stat"><h3>3<sup>mo</sup></h3><p>Avg. engagement</p></div><div class="gz-stat"><h3>1</h3><p>Person responsible</p></div></section>
    <section class="gz-services"><div class="gz-asterism">⁕ ⁕ ⁕</div><h2 class="gz-section-head">SELECTED WORK</h2><div class="gz-work"><?php foreach ( array_slice( $on1_content['work'], 0, 3 ) as $i => $w ) : ?><div class="gz-work-card"><span class="gz-work-card__num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] ); ?></p></div><?php endforeach; ?></div></section>
    <footer class="gz-foot"><div class="gz-asterism">⁕</div><p>ON1.AGENCY · WIEN · 2026</p></footer>
</div>
<div data-skin-page="about"><header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— FOUNDER · SOLO PRACTICE</p><h1 class="gz-hero__head">ABOUT</h1><p class="gz-hero__lede">Nishuthan Raveenthiran. One-person Vienna atelier since MMXVIII.</p></header><section class="gz-services"><div class="gz-asterism">⁕ ⁕ ⁕</div><h2 class="gz-section-head">PRINCIPLES</h2><div class="gz-work"><?php foreach ( array_slice( $on1_content['principles'], 0, 3 ) as $p ) : ?><div class="gz-work-card"><span class="gz-work-card__num"><?php echo esc_html( $p[0] ); ?></span><h3><?php echo esc_html( $p[1] ); ?></h3></div><?php endforeach; ?></div></section></div>
<div data-skin-page="work"><header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— SINCE MMXVIII</p><h1 class="gz-hero__head">WORK</h1></header><section class="gz-services"><div class="gz-work"><?php foreach ( $on1_content['work'] as $i => $w ) : ?><div class="gz-work-card"><span class="gz-work-card__num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3><p><?php echo esc_html( $w['lede'] . ' · ' . $w['year'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="pricing"><header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— THREE ENGAGEMENTS</p><h1 class="gz-hero__head">PRICING</h1></header><section class="gz-services"><div class="gz-work"><?php foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="gz-work-card"<?php echo $f ? ' style="border-color:#fff;background:rgba(255,255,255,0.05)"' : ''; ?>><span class="gz-work-card__num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( strtoupper( $t['name'] ) . ' · ' . $t['price'] ); ?></h3><p><?php echo esc_html( $t['sub'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="faq"><header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— FREQUENTLY ASKED</p><h1 class="gz-hero__head">FAQ</h1></header><section class="gz-services"><div class="gz-work"><?php foreach ( array_slice( $on1_content['faq'], 0, 3 ) as $i => $f ) : ?><div class="gz-work-card"><span class="gz-work-card__num"><?php printf( '%02d', $i + 1 ); ?></span><h3><?php echo esc_html( $f['q'] ); ?></h3><p><?php echo esc_html( $f['a'] ); ?></p></div><?php endforeach; ?></div></section></div>
<div data-skin-page="contact"><header class="gz-hero"><div class="gz-hero__mark">⁕</div><p class="gz-eyebrow">— SEND A BRIEF · 72H</p><h1 class="gz-hero__head">CONTACT</h1></header><section class="gz-services"><div class="gz-work" style="grid-template-columns:repeat(4,1fr)"><div class="gz-work-card"><span class="gz-work-card__num">EMAIL</span><h3><?php echo esc_html( $on1_content['identity']['email'] ); ?></h3></div><div class="gz-work-card"><span class="gz-work-card__num">LOC</span><h3>Wien · UTC+1</h3></div><div class="gz-work-card"><span class="gz-work-card__num">REPLY</span><h3>72 hours</h3></div><div class="gz-work-card"><span class="gz-work-card__num">Q3</span><h3>Slots open</h3></div></div></section></div>

</main>
<?php get_footer();
