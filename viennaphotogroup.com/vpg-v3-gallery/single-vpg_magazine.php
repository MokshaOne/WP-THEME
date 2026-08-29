<?php
/**
 * VPG v2 — single magazine issue.
 *
 * Three-part reading experience:
 *   1. Full-bleed cover hero · big serif title + lede + ⁕
 *   2. Table of contents · numbered chapters with author + page-break info
 *   3. The articles themselves · each in its own editorial block,
 *      with optional image, body and "page break" treatment between them
 *
 * PDF link appears in the top right of the cover if a PDF has been built
 * in the magazine editor (see inc/pdf-generator.php).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();

$issue_no  = get_post_meta( get_the_ID(), '_vpg_issue_number', true );
$issue_dt  = get_post_meta( get_the_ID(), '_vpg_issue_date',   true );
$cover_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'vpg-cover' ) : '';
$articles  = vpg_get_articles( get_the_ID() );
$pdf_url   = get_post_meta( get_the_ID(), '_vpg_pdf_url', true );
$is_print  = ! empty( $_GET['vpg_print'] );
?>

<main id="vpg-main" class="vpg-mag-issue<?php echo $is_print ? ' vpg-mag-issue--print' : ''; ?>">

<?php if ( ! $is_print ) : ?>
<!-- Reading progress · thin red bar pinned to the top of the issue -->
<div id="vpg-readbar" aria-hidden="true" style="position:fixed;top:0;left:0;height:3px;width:0;background:var(--g-red,#E5341F);z-index:90"></div>
<script>
(function () {
    var bar = document.getElementById('vpg-readbar');
    if (!bar) return;
    var ticking = false;
    function paint() {
        ticking = false;
        var doc = document.documentElement;
        var max = doc.scrollHeight - window.innerHeight;
        bar.style.width = (max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0) + '%';
    }
    window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(paint); }
    }, { passive: true });
    paint();

    // Continue reading · remembers the scroll position per issue (this browser only)
    try {
        var key = 'vpg-read-<?php echo (int) get_the_ID(); ?>';
        var saved = parseInt(localStorage.getItem(key) || '0', 10);
        if (saved > 800 && window.scrollY < 200) {
            var chip = document.createElement('div');
            chip.className = 'vpg-continue';
            chip.innerHTML = '<span><?php echo esc_js( __( 'Continue where you left off?', 'vpg-v2' ) ); ?></span>' +
                '<button type="button"><?php echo esc_js( __( 'Continue', 'vpg-v2' ) ); ?></button>' +
                '<button type="button" class="x" aria-label="Dismiss">×</button>';
            document.body.appendChild(chip);
            chip.querySelector('button').addEventListener('click', function () {
                window.scrollTo({ top: saved, behavior: 'smooth' });
                chip.remove();
            });
            chip.querySelector('.x').addEventListener('click', function () { chip.remove(); });
            setTimeout(function () { if (chip.parentNode) chip.remove(); }, 12000);
        }
        var saveT;
        window.addEventListener('scroll', function () {
            clearTimeout(saveT);
            saveT = setTimeout(function () {
                try { localStorage.setItem(key, String(Math.round(window.scrollY))); } catch (e) {}
            }, 400);
        }, { passive: true });
    } catch (e) {}

    // Read offline · saves this issue's page + images into the PWA cache
    if ('serviceWorker' in navigator && window.isSecureContext) {
        var offBtn = document.createElement('button');
        offBtn.type = 'button';
        offBtn.textContent = '↓ <?php echo esc_js( __( 'Read offline', 'vpg-v2' ) ); ?>';
        offBtn.style.cssText = 'position:fixed;right:18px;bottom:18px;z-index:80;background:#0B0B0B;color:#fff;border:0;padding:12px 16px;font:700 11px/1 inherit;letter-spacing:.14em;text-transform:uppercase;cursor:pointer';
        var offKey = 'vpg-offline-<?php echo (int) get_the_ID(); ?>';
        try { if (localStorage.getItem(offKey)) offBtn.textContent = '✓ <?php echo esc_js( __( 'Saved offline', 'vpg-v2' ) ); ?>'; } catch (e) {}
        document.body.appendChild(offBtn);
        offBtn.addEventListener('click', function () {
            navigator.serviceWorker.ready.then(function (reg) {
                var urls = [window.location.pathname];
                document.querySelectorAll('main img[src]').forEach(function (img) {
                    try { if (new URL(img.src).origin === window.location.origin) urls.push(img.src); } catch (e) {}
                });
                document.querySelectorAll('link[rel="stylesheet"][href]').forEach(function (l) {
                    try { if (new URL(l.href).origin === window.location.origin) urls.push(l.href); } catch (e) {}
                });
                navigator.serviceWorker.addEventListener('message', function onMsg(ev) {
                    if (ev.data && ev.data.type === 'VPG_ISSUE_CACHED') {
                        navigator.serviceWorker.removeEventListener('message', onMsg);
                        offBtn.textContent = '✓ <?php echo esc_js( __( 'Saved offline', 'vpg-v2' ) ); ?>';
                        try { localStorage.setItem(offKey, '1'); } catch (e) {}
                    }
                });
                if (reg.active) reg.active.postMessage({ type: 'VPG_CACHE_ISSUE', key: offKey, urls: urls });
            });
        });
    }
}());
</script>
<?php endif; ?>

<!-- ────────  COVER HERO  ──────── -->
<header class="vpg-cover">
    <div class="vpg-cover__bg<?php echo $cover_url ? '' : ' vpg-cover__bg--placeholder'; ?>" style="<?php echo $cover_url ? 'background-image:url(' . esc_url( $cover_url ) . ')' : ''; ?>"></div>
    <div class="vpg-cover__in">
        <p class="vpg-cover__issue">
            <span><?php echo esc_html( $issue_no ?: vpg_roman_date( get_the_date( 'U' ) ) ); ?></span>
            <?php if ( $issue_dt ) : ?>  ·  <span><?php echo esc_html( $issue_dt ); ?></span><?php endif; ?>
        </p>
        <h1 class="vpg-cover__title"><?php the_title(); ?></h1>
        <?php if ( get_the_excerpt() ) : ?>
            <p class="vpg-cover__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
        <?php endif; ?>
        <?php if ( $pdf_url ) : ?>
            <p style="margin-top:var(--vpg-sp-6)">
                <a class="g-btn g-btn--red" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank"><?php esc_html_e( 'Download PDF', 'vpg-v2' ); ?> ↓</a>
            </p>
        <?php endif; ?>
    </div>
</header>

<!-- ────────  TABLE OF CONTENTS  ──────── -->
<?php if ( $articles ) :
    $total_read = vpg_issue_reading_time( get_the_ID() );
?>
<section class="vpg-section vpg-section--surface vpg-mag-toc" id="vpg-mag-toc">
    <div class="vpg-wrap--mag">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php esc_html_e( 'Contents', 'vpg-v2' ); ?></p>
                <h2><?php printf( esc_html( _n( '%d article', '%d articles', count( $articles ), 'vpg-v2' ) ), count( $articles ) ); ?> · <span style="font-family:var(--vpg-font-mono);font-size:.5em;letter-spacing:.18em;color:var(--vpg-muted)"><?php printf( esc_html__( '%d MIN READ', 'vpg-v2' ), $total_read ); ?></span></h2>
            </div>
            <?php if ( $pdf_url ) : ?>
            <div class="vpg-section-head__meta"><a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank"><?php esc_html_e( 'Read as PDF', 'vpg-v2' ); ?> ↓</a></div>
            <?php endif; ?>
        </div>

        <ol class="vpg-mag-toc__list">
            <?php foreach ( $articles as $i => $a ) :
                $anchor   = 'article-' . ( $i + 1 );
                $art_read = vpg_reading_time( $a['body'] ?? '' );
            ?>
                <li>
                    <a href="#<?php echo esc_attr( $anchor ); ?>" data-vpg-toc="<?php echo esc_attr( $anchor ); ?>">
                        <span class="vpg-mag-toc__num"><?php printf( '%02d', $i + 1 ); ?></span>
                        <span class="vpg-mag-toc__title"><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></span>
                        <span class="vpg-mag-toc__author"><?php echo $a['author'] ? esc_html( '— ' . $a['author'] ) : ''; ?> · <?php echo (int) $art_read; ?>m</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if ( is_user_logged_in() ) : ?>
            <p style="text-align:center;margin-top:2rem"><?php echo vpg_bookmark_button( get_the_ID() ); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Sticky mini-TOC · floats in the corner once you scroll past the main ToC -->
<aside class="vpg-mag-sticky-toc" id="vpg-mag-sticky-toc" aria-hidden="true">
    <p class="vpg-mag-sticky-toc__label">— <?php esc_html_e( 'Reading', 'vpg-v2' ); ?></p>
    <ol>
        <?php foreach ( $articles as $i => $a ) : $anchor = 'article-' . ( $i + 1 ); ?>
            <li><a href="#<?php echo esc_attr( $anchor ); ?>" data-vpg-sticky="<?php echo esc_attr( $anchor ); ?>"><?php printf( '%02d', $i + 1 ); ?> · <?php echo esc_html( wp_trim_words( $a['title'] ?: 'Untitled', 6 ) ); ?></a></li>
        <?php endforeach; ?>
    </ol>
</aside>
<?php endif; ?>

<!-- ────────  ARTICLES  ──────── -->
<?php if ( $articles ) : ?>
    <?php foreach ( $articles as $i => $a ) :
        $anchor   = 'article-' . ( $i + 1 );
        $img_url  = ! empty( $a['image_id'] ) ? wp_get_attachment_image_url( (int) $a['image_id'], 'vpg-hero' ) : '';
        $is_break = ! empty( $a['page_break_after'] ) && $i < count( $articles ) - 1;
    ?>
    <article id="<?php echo esc_attr( $anchor ); ?>" class="vpg-mag-article">
        <div class="vpg-wrap--prose">
            <header class="vpg-mag-article__head">
                <p class="vpg-caps">— <?php printf( esc_html__( 'Article %02d', 'vpg-v2' ), $i + 1 ); ?></p>
                <h2 class="vpg-mag-article__title"><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></h2>
                <?php if ( ! empty( $a['author'] ) ) : ?>
                    <p class="vpg-mag-article__byline"><?php esc_html_e( 'By', 'vpg-v2' ); ?> <em><?php echo esc_html( $a['author'] ); ?></em></p>
                <?php endif; ?>
            </header>
        </div>

        <?php if ( $img_url ) : ?>
            <figure class="vpg-mag-article__hero">
                <img src="<?php echo esc_url( $img_url ); ?>" alt="">
            </figure>
        <?php endif; ?>

        <div class="vpg-wrap--prose">
            <div class="vpg-prose vpg-mag-article__body">
                <?php echo wpautop( wp_kses_post( $a['body'] ?? '' ) ); ?>
            </div>
        </div>

        <?php if ( $is_break ) : ?>
            <span class="vpg-asterism vpg-asterism--break"></span>
        <?php endif; ?>
    </article>
    <?php endforeach; ?>
<?php else : ?>
    <!-- No articles yet · render post_content as fallback -->
    <section class="vpg-section">
        <div class="vpg-wrap--prose">
            <div class="vpg-prose"><?php the_content(); ?></div>
        </div>
    </section>
<?php endif; ?>

<!-- ────────  COLOPHON  ──────── -->
<section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
    <div class="vpg-wrap--narrow">
        <span class="vpg-asterism vpg-asterism--mark"></span>
        <p class="vpg-caps">— <?php esc_html_e( 'Colophon', 'vpg-v2' ); ?></p>
        <p class="vpg-lede" style="margin:1rem auto 2rem;text-align:center"><?php
            printf(
                esc_html__( 'Vienna Photo Group · %s · %s · Member-run, ad-free, forever-keepable.', 'vpg-v2' ),
                esc_html( $issue_no ?: 'Issue' ),
                esc_html( $issue_dt ?: get_the_date( 'F Y' ) )
            );
        ?></p>
        <?php if ( $pdf_url ) : ?>
            <a class="g-btn g-btn--red" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank"><?php esc_html_e( 'Download this issue', 'vpg-v2' ); ?> ↓</a>
        <?php endif; ?>
        <a class="g-btn g-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'All issues', 'vpg-v2' ); ?> →</a>
    </div>
</section>

</main>

<?php endwhile; get_footer();
