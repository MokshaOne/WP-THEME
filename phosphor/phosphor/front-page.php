<?php
/**
 * Phosphor — front-page.php
 * Pulls all content from inc/helpers.php (Customizer-backed),
 * then renders the homepage in the Phosphor design language.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$identity   = phosphor_get_identity();
$hero       = phosphor_get_hero();
$on1_content = [
    'identity'   => $identity,
    'lede'       => $hero['lede'],
    'principles' => phosphor_get_principles(),
    'services'   => phosphor_get_services(),
    'work'       => phosphor_get_work(),
    'tiers'      => phosphor_get_tiers(),
    'faq'        => phosphor_get_faq(),
];

get_header();
?>
<main id="main" class="mo-main">

<nav class="mo-nav">
    <span class="mo-prompt">root@on1:~$</span>
    <ul class="mo-nav__links"><li><a data-skin-link="home" class="is-active">./home</a></li><li><a data-skin-link="about">./about</a></li><li><a data-skin-link="work">./work</a></li><li><a data-skin-link="pricing">./pricing</a></li><li><a data-skin-link="faq">./faq</a></li><li><a data-skin-link="contact">./contact</a></li></ul>
    <span class="mo-status">● ONLINE</span>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="mo-hero">
        <pre class="mo-ascii">    ▓▓▓▓▓▓▓  ▓▓    ▓▓  ▓▓▓
   ▓▓     ▓▓ ▓▓▓   ▓▓  ▓▓▓
   ▓▓     ▓▓ ▓▓ ▓▓ ▓▓  ▓▓▓
   ▓▓     ▓▓ ▓▓  ▓▓▓▓  ▓▓▓
    ▓▓▓▓▓▓▓  ▓▓    ▓▓  ▓▓▓</pre>
        <p class="mo-tag">&gt; on1.agency · v4.0.1 · vienna · 2026</p>
        <h1 class="mo-hero__head">// digital atelier<br>// hand-coded since 2018</h1>
        <pre class="mo-output">$ ls -la /sites
total 14
<?php foreach ( $on1_content['work'] as $w ) printf( "drwxr-xr-x  %-12s %s  %s\n", strtolower( str_replace( ' ', '-', $w['title'] ) ), $w['year'], $w['lede'] ); ?>$ _</pre>
        <a class="mo-btn" data-skin-link="contact">[ START A PROJECT ]</a>
    </header>
    <section class="mo-stats"><div class="mo-stat"><h3>14+</h3><p>// sites_shipped</p></div><div class="mo-stat"><h3>72h</h3><p>// reply_window</p></div><div class="mo-stat"><h3>3mo</h3><p>// avg_engagement</p></div><div class="mo-stat"><h3>0</h3><p>// page_builders</p></div></section>
    <section class="mo-services"><h2 class="mo-section-head">// services</h2><ul class="mo-service-list"><?php foreach ( $on1_content['services'] as $i => $s ) : ?><li><span><?php printf( '%02d', $i + 1 ); ?></span> <?php echo esc_html( strtolower( str_replace( ' ', '-', $s['name'] ) ) ); ?> <span class="mo-arrow">→</span></li><?php endforeach; ?></ul></section>
    <footer class="mo-foot"><span>© 2026 on1.agency</span><span>vienna · UTC+1</span><span class="mo-prompt mo-blink">_</span></footer>
</div>
<div data-skin-page="about">
    <header class="mo-hero"><p class="mo-tag">&gt; /about</p><h1 class="mo-hero__head">// founder<br>// nishuthan raveenthiran</h1><pre class="mo-output">$ whoami\nnishuthan@on1.agency\n$ uptime · 8y · 14 sites · 0 page-builders</pre></header>
    <section class="mo-services"><h2 class="mo-section-head">// principles</h2><ul class="mo-service-list"><?php foreach ( $on1_content['principles'] as $i => $p ) : ?><li><span><?php printf( '%02d', $i + 1 ); ?></span> <?php echo esc_html( strtolower( $p[1] ) ); ?> <span class="mo-arrow">→</span></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="work">
    <header class="mo-hero"><p class="mo-tag">&gt; /work</p><h1 class="mo-hero__head">$ ls /sites</h1></header>
    <section class="mo-services"><ul class="mo-service-list"><?php foreach ( $on1_content['work'] as $w ) : ?><li><span><?php echo esc_html( $w['year'] ); ?></span> <?php echo esc_html( strtolower( $w['title'] ) . ' · ' . strtolower( $w['lede'] ) ); ?> <span class="mo-arrow">→</span></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="pricing">
    <header class="mo-hero"><p class="mo-tag">&gt; /pricing</p><h1 class="mo-hero__head">$ cat tiers.json</h1></header>
    <section class="mo-services"><ul class="mo-service-list"><?php foreach ( $on1_content['tiers'] as $i => $t ) : ?><li><span><?php printf( '%02d', $i + 1 ); ?></span> <?php echo esc_html( strtolower( $t['name'] ) . ' · ' . $t['price'] . ' · ' . strtolower( $t['sub'] ) ); ?> <span class="mo-arrow">→</span></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="faq">
    <header class="mo-hero"><p class="mo-tag">&gt; /faq</p><h1 class="mo-hero__head">$ man on1</h1></header>
    <section class="mo-services"><ul class="mo-service-list"><?php foreach ( $on1_content['faq'] as $i => $f ) : ?><li><span><?php printf( '%02d', $i + 1 ); ?></span> <?php echo esc_html( strtolower( $f['q'] ) . ' · ' . strtolower( $f['a'] ) ); ?></li><?php endforeach; ?></ul></section>
</div>
<div data-skin-page="contact">
    <header class="mo-hero"><p class="mo-tag">&gt; /contact</p><h1 class="mo-hero__head">$ open brief</h1></header>
    <section class="mo-services"><ul class="mo-service-list"><li><span>email</span> <?php echo esc_html( $on1_content['identity']['email'] ); ?> <span class="mo-arrow">→</span></li><li><span>loc</span> vienna · utc+1 <span class="mo-arrow">→</span></li><li><span>reply</span> 72 hours <span class="mo-arrow">→</span></li><li><span>q3</span> booking open <span class="mo-arrow">→</span></li></ul></section>
</div>

</main>
<?php get_footer();
