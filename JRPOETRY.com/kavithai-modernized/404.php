<?php
/**
 * 404 — v2.1 Relief.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<section class="fourohfour">
    <?php echo kv_relief( '௪௦௪', '404' ); ?>

    <div class="fourohfour__in">
        <p class="smallcaps" style="color:var(--ochre); margin-bottom:2.5rem;">Error · Nº <span class="la">404</span></p>

        <p class="fourohfour__verse">
            தேடியதை இங்கே கண்டடைய முடியவில்லை —<br>
            ஆனால் கண்டடையாததும்<br>
            ஒரு கவிதையே.
        </p>

        <p style="font-family:var(--serif-la); font-style:italic; font-size:0.95rem; color:var(--ink-mute); margin-bottom:3rem; letter-spacing:0.03em;">
            What you sought is not here — but the not-finding<br>is also a kind of poem.
        </p>

        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fourohfour__back">முகப்புக்குத் திரும்பு <span class="la">→</span></a>
    </div>
</section>

<?php get_footer(); ?>
