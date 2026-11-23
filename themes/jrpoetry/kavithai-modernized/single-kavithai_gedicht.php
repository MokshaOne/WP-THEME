<?php
/**
 * Single poem — v2.1 Relief.
 * Features (all preserved from v2.0):
 *   audio player, marginalia, drop cap, asterism between stanzas,
 *   font-size +/- (persisted), print to PDF, random poem,
 *   reading progress, ambient mode, reactions AJAX, prev/next nav.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); the_post();

$pid    = get_the_ID();
$year   = get_post_meta( $pid, '_kv_year', true );
$note   = get_post_meta( $pid, '_kv_note', true );
$ded    = get_post_meta( $pid, '_kv_dedication', true );
$loc    = get_post_meta( $pid, '_kv_origin_loc', true );
$back   = get_post_meta( $pid, '_kv_backstory', true );
$audio  = get_post_meta( $pid, '_kv_audio_url', true );
$views  = (int) get_post_meta( $pid, '_kv_views', true );
$book   = get_post_meta( $pid, '_kv_book_title', true );
$cats   = get_the_terms( $pid, 'kavithai_cat' );
$cat    = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
$prev   = get_previous_post();
$next   = get_next_post();
$title_en = get_post_meta( $pid, '_kv_title_en', true );

// Split content into stanzas for asterism breaks.
$content_raw = wp_strip_all_tags( get_the_content() );
$content_raw = trim( $content_raw );
$stanzas = preg_split( '/\n\s*\n+/u', $content_raw );
?>

<div class="kv-progress" id="kv-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-hidden="true"></div>

<header class="page-head" style="padding-bottom: 1.5rem;">
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'kavithai_gedicht' ) ); ?>">கவிதைகள்</a>
            <?php if ( $cat ) : ?>
                <span class="sep">·</span><?php echo esc_html( $cat ); ?>
            <?php endif; ?>
        </p>
        <h1 class="page-head__title"><?php the_title(); ?></h1>
        <?php if ( $title_en || $year || $cat ) : ?>
            <p class="page-head__sub">
                <?php if ( $title_en ) echo esc_html( $title_en ); ?>
                <?php if ( $title_en && $year ) echo ' <span style="color:var(--ochre);margin:0 0.5em;">·</span> '; ?>
                <?php if ( $year ) echo '<span class="la">' . esc_html( $year ) . '</span>'; ?>
                <?php if ( $cat ) echo ' <span style="color:var(--ochre);margin:0 0.5em;">·</span> <span class="ta" style="font-style:italic;">' . esc_html( $cat ) . '</span>'; ?>
            </p>
        <?php endif; ?>
    </div>
</header>

<section class="featured" id="kv-poem-article">
    <?php echo kv_relief( mb_substr( get_the_title(), 0, 1 ), 'featured' ); ?>

    <div class="featured__in">

        <?php if ( has_post_thumbnail() ) : ?>
            <div style="margin-bottom: 3rem; position: relative; aspect-ratio: 16/9; overflow: hidden; border: 1px solid var(--rule);">
                <?php the_post_thumbnail( 'poem-hero', [
                    'style' => 'width:100%; height:100%; object-fit:cover; ' . kv_focal_style( $pid ),
                    'alt'   => esc_attr( get_the_title() ),
                ] ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $audio ) : ?>
            <div class="audio">
                <span class="audio__label">கவிஞரின் குரல்</span>
                <audio controls preload="metadata" src="<?php echo esc_url( $audio ); ?>"></audio>
            </div>
        <?php endif; ?>

        <div class="poem-grid">
            <aside class="poem-margin-left" aria-label="<?php esc_attr_e( 'Poem metadata', 'kavithai' ); ?>">
                <?php if ( $year ) : ?>
                    <span class="line"><span class="label">Written</span><span class="value"><?php echo esc_html( $year ); ?></span></span>
                <?php endif; ?>
                <?php if ( $loc ) : ?>
                    <span class="line"><span class="label">Place</span><span class="value"><?php echo esc_html( $loc ); ?></span></span>
                <?php endif; ?>
                <?php if ( $ded ) : ?>
                    <span class="line"><span class="label">For</span><span class="value"><?php echo esc_html( $ded ); ?></span></span>
                <?php endif; ?>
                <?php if ( $cat ) : ?>
                    <span class="line"><span class="label">Form</span><span class="value"><?php echo esc_html( $cat ); ?></span></span>
                <?php endif; ?>
                <?php if ( $book ) : ?>
                    <span class="line"><span class="label">From</span><span class="value"><?php echo esc_html( $book ); ?></span></span>
                <?php endif; ?>
                <?php if ( $views > 0 ) : ?>
                    <span class="line"><span class="label">Views</span><span class="value la"><?php echo number_format( $views ); ?></span></span>
                <?php endif; ?>
            </aside>

            <div class="poem-body" id="kv-poem-body">
                <?php
                $first = true;
                $total_stanzas = count( $stanzas );
                foreach ( $stanzas as $i => $stanza ) :
                    $stanza = trim( $stanza );
                    if ( $stanza === '' ) continue;
                    $lines = explode( "\n", $stanza );
                    ?>
                    <p>
                        <?php
                        $line_idx = 0;
                        $line_count = count( $lines );
                        foreach ( $lines as $line ) :
                            $line = trim( $line );
                            if ( $line === '' ) continue;
                            if ( $first && $line_idx === 0 ) {
                                // Drop cap on first 2 chars of first line (Tamil grapheme cluster).
                                $line_head = mb_substr( $line, 0, 2 );
                                $line_tail = mb_substr( $line, 2 );
                                echo '<span class="dropcap">' . esc_html( $line_head ) . '</span>' . esc_html( $line_tail );
                                $first = false;
                            } else {
                                echo esc_html( $line );
                            }
                            if ( $line_idx < $line_count - 1 ) echo '<br>';
                            $line_idx++;
                        endforeach;
                        ?>
                    </p>
                    <?php if ( $i < $total_stanzas - 1 ) echo kv_asterism( 'stanza' ); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="poem-end"><?php echo kv_seal( 'sm' ); ?></div>

        <div class="poem-footer">
            <p class="poem-footer__cite">
                <span class="ta"><?php echo esc_html( kv_name() ); ?></span><?php if ( $year ) echo '<span class="la">, ' . esc_html( $year ) . '</span>'; ?>
            </p>
            <?php if ( $book ) : ?>
                <p class="poem-footer__num">from <em><span class="ta"><?php echo esc_html( $book ); ?></span></em></p>
            <?php endif; ?>
        </div>

        <!-- Toolbar -->
        <div class="poem-toolbar">
            <div class="poem-toolbar__group" role="group" aria-label="எழுத்து அளவு">
                <button type="button" class="toolbar-btn toolbar-btn--icon" id="kv-font-down" aria-label="சிறியதாக்கு">−</button>
                <span class="toolbar-label">A</span>
                <button type="button" class="toolbar-btn toolbar-btn--icon" id="kv-font-up" aria-label="பெரியதாக்கு">+</button>
            </div>
            <div class="poem-toolbar__group">
                <button type="button" class="toolbar-btn" onclick="window.print()" aria-label="அச்சிடு / PDF"><span class="ta" style="font-family:var(--serif-ta);">அச்சிடு</span></button>
                <button type="button" class="toolbar-btn" onclick="kvRandomPoem()"><span class="ta" style="font-family:var(--serif-ta);">தற்செயல் கவிதை</span></button>
            </div>
            <div class="poem-toolbar__group">
                <button type="button" class="toolbar-btn" id="kv-ambient-btn" aria-pressed="false"><span class="ta" style="font-family:var(--serif-ta);">வாசிப்பு நிலை</span></button>
            </div>
        </div>
    </div>
</section>

<!-- Backstory -->
<?php if ( $back ) : ?>
<section style="max-width:720px; margin:0 auto; padding: var(--pad-section) var(--pad-x);">
    <?php echo kv_eyebrow_bi( 'இந்த கவிதையின் கதை', 'Behind the Poem' ); ?>
    <div style="font-family:var(--serif-ta); font-size:1.1rem; line-height:2; color:var(--ink-soft); font-style:italic;">
        <p><?php echo nl2br( esc_html( $back ) ); ?></p>
    </div>
</section>
<?php endif; ?>

<!-- Reaction form -->
<section class="reaction">
    <?php echo kv_eyebrow_bi( 'உங்கள் கருத்து', 'Your Reflection' ); ?>
    <form class="reaction__form" id="kv-reaction-form" novalidate>
        <input type="text" name="react_name" placeholder="உங்கள் பெயர் · Your name" required maxlength="80">
        <textarea name="react_msg" rows="4" placeholder="இந்த கவிதை பற்றி உங்கள் எண்ணங்கள்..." required maxlength="800"></textarea>
        <!-- Honeypot — invisible to humans, filled by bots. -->
        <input type="text" name="kv_hp" autocomplete="off" tabindex="-1" aria-hidden="true" style="position:absolute; left:-9999px; opacity:0; pointer-events:none;">
        <button type="submit">அனுப்பவும்</button>
    </form>
    <?php $rcount = count( get_post_meta( $pid, '_kv_reactions', true ) ?: [] ); ?>
    <?php if ( $rcount > 0 ) : ?>
        <p class="smallcaps-sm" style="margin-top:1.5rem; color:var(--ink-faint);"><?php echo $rcount; ?> reader<?php echo $rcount === 1 ? '' : 's'; ?> have shared their thoughts</p>
    <?php endif; ?>
</section>

<!-- Prev / next -->
<?php if ( $prev || $next ) : ?>
<nav class="poem-nav" aria-label="<?php esc_attr_e( 'Poem navigation', 'kavithai' ); ?>">
    <?php if ( $prev ) : ?>
        <a href="<?php echo esc_url( get_permalink( $prev->ID ) ); ?>" class="poem-nav__prev">
            <span class="poem-nav__dir">← Previous</span>
            <span class="poem-nav__title"><?php echo esc_html( $prev->post_title ); ?></span>
        </a>
    <?php else : ?>
        <span></span>
    <?php endif; ?>
    <?php if ( $next ) : ?>
        <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>" class="poem-nav__next">
            <span class="poem-nav__dir">Next →</span>
            <span class="poem-nav__title"><?php echo esc_html( $next->post_title ); ?></span>
        </a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<script>
(function(){
    // Reader font size +/- (persisted)
    var body = document.getElementById('kv-poem-body');
    var KEY  = 'kv_reader_scale';
    var scale = parseFloat(localStorage.getItem(KEY)) || 1;
    var label = document.querySelector('.poem-toolbar .toolbar-label');
    function apply() {
        document.documentElement.style.setProperty('--reader-scale', scale.toFixed(2));
        localStorage.setItem(KEY, scale.toFixed(2));
        if (label) label.textContent = Math.round(scale * 100) + '%';
    }
    apply();
    var up = document.getElementById('kv-font-up');
    var dn = document.getElementById('kv-font-down');
    if (up) up.addEventListener('click', function(){ scale = Math.min(1.5, scale + 0.1); apply(); });
    if (dn) dn.addEventListener('click', function(){ scale = Math.max(0.8, scale - 0.1); apply(); });

    // Reading progress
    var prog = document.getElementById('kv-progress');
    if (prog) window.addEventListener('scroll', function(){
        var s = document.documentElement;
        var scrolled = s.scrollTop || document.body.scrollTop;
        var total = s.scrollHeight - s.clientHeight;
        var pct = total > 0 ? Math.round((scrolled / total) * 100) : 0;
        prog.style.width = pct + '%';
        prog.setAttribute('aria-valuenow', pct);
    }, { passive: true });

    // Ambient mode
    var amb = document.getElementById('kv-ambient-btn');
    if (amb) amb.addEventListener('click', function(){
        var on = document.body.classList.toggle('kv-ambient');
        amb.setAttribute('aria-pressed', on ? 'true' : 'false');
    });

    // Reaction AJAX
    var form = document.getElementById('kv-reaction-form');
    if (form) form.addEventListener('submit', function(e){
        e.preventDefault();
        var btn  = form.querySelector('button[type=submit]');
        var name = form.querySelector('[name=react_name]').value.trim();
        var msg  = form.querySelector('[name=react_msg]').value.trim();
        var hp   = form.querySelector('[name=kv_hp]').value;  // honeypot
        if (!name || !msg) return;
        btn.disabled = true; btn.textContent = 'அனுப்புகிறது...';
        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=kv_reaction&nonce=<?php echo esc_js( wp_create_nonce( 'kv_react_nonce' ) ); ?>&post_id=<?php echo absint( $pid ); ?>&react_name=' + encodeURIComponent(name) + '&react_msg=' + encodeURIComponent(msg) + '&kv_hp=' + encodeURIComponent(hp)
        }).then(function(r){ return r.json(); }).then(function(d){
            if (d.success) {
                form.innerHTML = '<p style="font-family:var(--serif-ta); font-size:1.05rem; padding:1.4rem; background:var(--paper-warm); border-left:3px solid var(--ochre); color:var(--ink-soft); font-style:italic;">✓ உங்கள் கருத்திற்கு நன்றி.</p>';
            } else {
                btn.disabled = false; btn.textContent = 'அனுப்பவும்';
            }
        }).catch(function(){
            btn.disabled = false; btn.textContent = 'அனுப்பவும்';
        });
    });
})();

function kvRandomPoem() {
    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>?action=kv_random')
        .then(function(r){ return r.json(); })
        .then(function(d){ if (d.success) window.location = d.data.url; });
}
</script>

<?php get_footer(); ?>
