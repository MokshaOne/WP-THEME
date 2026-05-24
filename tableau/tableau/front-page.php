<?php
/**
 * Tableau — front-page.php
 * Pulls all content from inc/helpers.php (Customizer-backed),
 * then renders the homepage in the Tableau design language.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$identity   = tableau_get_identity();
$hero       = tableau_get_hero();
$on1_content = [
    'identity'   => $identity,
    'lede'       => $hero['lede'],
    'principles' => tableau_get_principles(),
    'services'   => tableau_get_services(),
    'work'       => tableau_get_work(),
    'tiers'      => tableau_get_tiers(),
    'faq'        => tableau_get_faq(),
];

get_header();
?>
<main id="main" class="rt-main">

<nav class="rt-nav">
    <a class="rt-nav__brand" data-skin-link="home">Nishuthan</a>
    <ul class="rt-nav__links"><li><a data-skin-link="home" class="is-active">Home</a></li><li><a data-skin-link="about">About</a></li><li><a data-skin-link="work">Work</a></li><li><a data-skin-link="pricing">Pricing</a></li><li><a data-skin-link="faq">FAQ</a></li><li><a data-skin-link="contact">Contact</a></li></ul>
    <a class="rt-nav__cta" data-skin-link="contact">Get in touch</a>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="rt-hero">
        <div class="rt-hero__image"></div>
        <div class="rt-hero__overlay"><p class="rt-eyebrow">PHOTOGRAPHY · STUDIO</p><h1 class="rt-hero__head">on1<em>.</em>agency</h1><p class="rt-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p></div>
    </header>
    <section class="rt-grid">
        <?php foreach ( array_slice( $on1_content['work'], 0, 4 ) as $i => $w ) : $cls = $i === 0 ? ' rt-grid-card--lg' : ($i === 3 ? ' rt-grid-card--wide' : ''); ?>
            <div class="rt-grid-card<?php echo $cls; ?>"><span class="rt-tag"><?php echo esc_html( $w['year'] . ' · ' . strtoupper( explode( '·', $w['lede'] )[0] ?? '' ) ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3></div>
        <?php endforeach; ?>
    </section>
    <section class="rt-quote"><p class="rt-eyebrow">— On practice</p><blockquote>"Quiet design is the most generous kind of work. One person responsible, start to ship."</blockquote><cite>— Nishuthan</cite></section>
    <footer class="rt-foot"><span>© 2026 on1.agency</span><span>Wien · UTC+1</span><span>Booking Q3</span></footer>
</div>
<div data-skin-page="about">
    <div class="rt-hero" style="aspect-ratio:auto;padding:4rem 2rem"><div class="rt-hero__overlay" style="position:relative;padding:0;background:none"><p class="rt-eyebrow">FOUNDER · SOLO PRACTICE</p><h1 class="rt-hero__head">About <em>.</em></h1><p class="rt-hero__lede">Nishuthan Raveenthiran. A one-person Vienna atelier. Eight years building editorial WordPress sites.</p></div></div>
    <section class="rt-quote"><p class="rt-eyebrow">— Principles</p><blockquote>"Quiet design · one person responsible · editorial over template · code that lasts a decade."</blockquote></section>
</div>
<div data-skin-page="work">
    <div class="rt-hero" style="aspect-ratio:auto;padding:4rem 2rem"><div class="rt-hero__overlay" style="position:relative;padding:0;background:none"><p class="rt-eyebrow">14 SITES · SINCE 2018</p><h1 class="rt-hero__head">Work <em>.</em></h1></div></div>
    <section class="rt-grid"><?php foreach ( $on1_content['work'] as $w ) : ?><div class="rt-grid-card"><span class="rt-tag"><?php echo esc_html( $w['year'] ); ?></span><h3><?php echo esc_html( $w['title'] ); ?></h3></div><?php endforeach; ?></section>
</div>
<div data-skin-page="pricing">
    <div class="rt-hero" style="aspect-ratio:auto;padding:4rem 2rem"><div class="rt-hero__overlay" style="position:relative;padding:0;background:none"><p class="rt-eyebrow">THREE ENGAGEMENTS</p><h1 class="rt-hero__head">Pricing <em>.</em></h1></div></div>
    <section class="rt-grid" style="grid-template-columns:repeat(3,1fr)"><?php $rom = [ 'i.', 'ii.', 'iii.' ]; foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?><div class="rt-grid-card" style="aspect-ratio:auto;padding:2rem<?php echo $f ? ';border:1px solid #E8C56C' : ''; ?>"><span class="rt-tag"><?php echo esc_html( ( $rom[ $i ] ?? '·' ) . ' ' . strtoupper( $t['name'] ) ); ?></span><h3 style="font-size:2.4rem"><?php echo esc_html( $t['price'] ); ?></h3><p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin:0.5rem 0 0"><?php echo esc_html( $t['sub'] ); ?></p></div><?php endforeach; ?></section>
</div>
<div data-skin-page="faq">
    <div class="rt-hero" style="aspect-ratio:auto;padding:4rem 2rem"><div class="rt-hero__overlay" style="position:relative;padding:0;background:none"><p class="rt-eyebrow">FREQUENTLY ASKED</p><h1 class="rt-hero__head">FAQ <em>.</em></h1></div></div>
    <section class="rt-quote"><blockquote style="font-size:1rem;text-align:left;font-style:normal"><?php foreach ( $on1_content['faq'] as $f ) : ?>• <?php echo esc_html( $f['q'] ); ?> — <?php echo esc_html( $f['a'] ); ?><br><?php endforeach; ?></blockquote></section>
</div>
<div data-skin-page="contact">
    <div class="rt-hero" style="aspect-ratio:auto;padding:4rem 2rem"><div class="rt-hero__overlay" style="position:relative;padding:0;background:none"><p class="rt-eyebrow">SEND A BRIEF · 72H RESPONSE</p><h1 class="rt-hero__head">Contact <em>.</em></h1></div></div>
    <section class="rt-grid" style="grid-template-columns:repeat(4,1fr)"><div class="rt-grid-card" style="aspect-ratio:auto;padding:1.5rem"><span class="rt-tag">EMAIL</span><h3 style="font-size:1.1rem"><?php echo esc_html( $on1_content['identity']['email'] ); ?></h3></div><div class="rt-grid-card" style="aspect-ratio:auto;padding:1.5rem"><span class="rt-tag">LOCATION</span><h3 style="font-size:1.1rem">Wien · UTC+1</h3></div><div class="rt-grid-card" style="aspect-ratio:auto;padding:1.5rem"><span class="rt-tag">RESPONSE</span><h3 style="font-size:1.1rem">72 hours</h3></div><div class="rt-grid-card" style="aspect-ratio:auto;padding:1.5rem"><span class="rt-tag">BOOKING</span><h3 style="font-size:1.1rem">Q3 · open</h3></div></section>
</div>

</main>
<?php get_footer();
