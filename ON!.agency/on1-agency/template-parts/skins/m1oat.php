<?php
/** Skin: m1o.at · Brutalist link hub · black + signal green + JetBrains Mono */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="moa-systembar">
    <span class="moa-sig">●</span> <span>on1.agency · LIVE</span> <span class="moa-faint">· vienna · utc+1 · 2026</span>
</div>
<nav class="moa-nav">
    <a class="moa-nav__brand" data-skin-link="home">on1<span class="moa-sig">.</span>agency</a>
    <ul class="moa-nav__links">
        <li><a data-skin-link="home" class="is-active">home</a></li>
        <li><a data-skin-link="about">about</a></li>
        <li><a data-skin-link="work">work</a></li>
        <li><a data-skin-link="pricing">pricing</a></li>
        <li><a data-skin-link="faq">faq</a></li>
        <li><a data-skin-link="contact">contact</a></li>
    </ul>
</nav>
<div data-skin-page="home" class="is-active">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ digital atelier</p>
        <h1 class="moa-hero__head">on1.agency<br><span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede"><?php echo esc_html( $on1_content['lede'] ); ?></p>
    </header>
    <section class="moa-channels">
        <p class="moa-upper moa-faint">— channels</p>
        <ul class="moa-channel-list">
            <?php $i = 1; foreach ( $on1_content['services'] as $s ) : ?>
                <li class="moa-channel"><span class="moa-channel__id"><?php printf( '%02d', $i++ ); ?></span><span class="moa-channel__name">→ <?php echo esc_html( strtolower( $s['name'] ) ); ?></span><span class="moa-channel__meta"></span><span class="moa-sig">●</span></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="moa-stats">
        <div><strong>14</strong><span>sites_shipped</span></div>
        <div><strong>72</strong><span>reply_hours</span></div>
        <div><strong>3</strong><span>avg_months</span></div>
        <div><strong>0</strong><span>page_builders</span></div>
    </section>
    <footer class="moa-foot"><span class="moa-sig">●</span> <span class="moa-faint">© 2026 · on1.agency · vienna · q3 booking open</span></footer>
</div>
<div data-skin-page="about">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ /about</p>
        <h1 class="moa-hero__head">Nishuthan<br>Raveenthiran <span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede">// founder · solo practitioner · vienna · since 2018</p>
        <pre class="moa-output">$ whoami
nishuthan@on1.agency

$ uptime
8 years · 14 sites · 0 page builders

$ ls /principles
- quiet-design.txt
- one-person.txt
- editorial-first.txt
- lasting-code.txt</pre>
    </header>
    <section class="moa-channels">
        <p class="moa-upper moa-faint">— /principles</p>
        <ul class="moa-channel-list">
            <?php $i = 1; foreach ( $on1_content['principles'] as $p ) : ?>
                <li class="moa-channel"><span class="moa-channel__id"><?php printf( '%02d', $i++ ); ?></span><span class="moa-channel__name">→ <?php echo esc_html( strtolower( $p[1] ) ); ?></span><span class="moa-channel__meta"></span><span class="moa-sig">●</span></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
<div data-skin-page="work">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ /work</p>
        <h1 class="moa-hero__head">$ ls -la /sites <span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede">// 14 sites shipped · 2018–2026 · selected</p>
    </header>
    <section class="moa-channels">
        <ul class="moa-channel-list">
            <?php foreach ( $on1_content['work'] as $w ) : ?>
                <li class="moa-channel"><span class="moa-channel__id"><?php echo esc_html( $w['year'] ); ?></span><span class="moa-channel__name">→ <?php echo esc_html( strtolower( $w['title'] ) ); ?></span><span class="moa-channel__meta"><?php echo esc_html( $w['lede'] ); ?></span><span class="moa-sig">●</span></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
<div data-skin-page="pricing">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ /pricing</p>
        <h1 class="moa-hero__head">$ cat /tiers.json <span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede">// strategy always included · always one person responsible</p>
    </header>
    <section class="moa-tiers">
        <?php foreach ( $on1_content['tiers'] as $i => $t ) : $f = ! empty( $t['featured'] ); ?>
            <div class="moa-tier<?php echo $f ? ' moa-tier--feat' : ''; ?>">
                <p class="moa-upper<?php echo $f ? '' : ' moa-faint'; ?>">// tier_<?php printf( '%02d', $i + 1 ); ?><?php echo $f ? ' · selected' : ''; ?></p>
                <h3 class="moa-tier__name"><?php echo esc_html( strtolower( $t['name'] ) ); ?></h3>
                <h4 class="moa-tier__price"><?php echo esc_html( $t['price'] ); ?></h4>
                <pre class="moa-tier__feat"><?php foreach ( $t['features'] as $feat ) echo '+ ' . esc_html( strtolower( $feat ) ) . "\n"; ?></pre>
                <a class="moa-btn<?php echo $f ? ' moa-btn--feat' : ''; ?>">[ select <?php echo esc_html( strtolower( $t['name'] ) ); ?> ]</a>
            </div>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="faq">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ /faq</p>
        <h1 class="moa-hero__head">$ man on1 <span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede">// frequently asked · response time 72h</p>
    </header>
    <section class="moa-faq">
        <?php foreach ( $on1_content['faq'] as $i => $f ) : ?>
            <details<?php echo $i === 0 ? ' open' : ''; ?>>
                <summary><span class="moa-sig">▸</span> <?php echo esc_html( strtolower( $f['q'] ) ); ?></summary>
                <pre>// <?php echo esc_html( $f['a'] ); ?></pre>
            </details>
        <?php endforeach; ?>
    </section>
</div>
<div data-skin-page="contact">
    <header class="moa-hero">
        <p class="moa-upper moa-faint">→ /contact</p>
        <h1 class="moa-hero__head">$ open brief <span class="moa-cursor">_</span></h1>
        <p class="moa-hero__lede">// send a brief · always one person reads · 72h reply</p>
    </header>
    <section class="moa-contact">
        <form class="moa-form" onsubmit="event.preventDefault();">
            <label><span class="moa-upper moa-faint">// name</span><input type="text"></label>
            <label><span class="moa-upper moa-faint">// email</span><input type="email"></label>
            <label><span class="moa-upper moa-faint">// studio</span><input type="text"></label>
            <label><span class="moa-upper moa-faint">// brief</span><textarea rows="5"></textarea></label>
            <button class="moa-btn">[ send brief ]</button>
        </form>
        <ul class="moa-info">
            <li><span class="moa-upper moa-faint">→ email</span><p><?php echo esc_html( $on1_content['identity']['email'] ?? 'hello@on1.agency' ); ?></p></li>
            <li><span class="moa-upper moa-faint">→ location</span><p>vienna · austria · utc+1</p></li>
            <li><span class="moa-upper moa-faint">→ response</span><p>72h window · always one person</p></li>
            <li><span class="moa-upper moa-faint">→ booking</span><p>q3 · slots open</p></li>
        </ul>
    </section>
</div>
