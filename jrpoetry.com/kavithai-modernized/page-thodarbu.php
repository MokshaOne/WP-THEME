<?php
/**
 * Template Name: தொடர்பு
 * Contact page — v2.1 Relief.
 * Preserves the existing POST handler (nonce + sanitize + wp_mail).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); the_post();

$email = kv_email();
$status = '';
if ( isset( $_POST['kv_submit'] ) && isset( $_POST['kv_nonce'] ) && wp_verify_nonce( $_POST['kv_nonce'], 'kv_contact' ) ) {
    $name = sanitize_text_field( wp_unslash( $_POST['kv_name'] ?? '' ) );
    $from = sanitize_email( wp_unslash( $_POST['kv_email'] ?? '' ) );
    $msg  = sanitize_textarea_field( wp_unslash( $_POST['kv_msg'] ?? '' ) );
    if ( $name && $from && $msg && is_email( $from ) && $email ) {
        $status = wp_mail(
            $email,
            'கவிதை: ' . $name . ' அவர்களிடம் இருந்து',
            "பெயர்: $name\nமின்னஞ்சல்: $from\n\n$msg",
            [ 'Reply-To: ' . $name . ' <' . $from . '>' ]
        ) ? 'ok' : 'err';
    } else {
        $status = 'inv';
    }
}
?>

<header class="page-head">
    <?php echo kv_relief( 'தொடர்பு', 'contact' ); ?>
    <div class="page-head__in">
        <p class="page-head__crumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">முகப்பு</a>
            <span class="sep">·</span>தொடர்பு
        </p>
        <h1 class="page-head__title">ஒரு வார்த்தை<br>எழுதுங்கள்</h1>
        <p class="page-head__sub">Write a word <span style="color:var(--ochre);margin:0 0.5em;">·</span> A question, a thank-you, or a poem — all are welcome</p>
    </div>
</header>

<section class="contact-box">
    <p class="contact-box__intro">
        நான் ஒவ்வொரு கடிதத்தையும் வாசிக்கிறேன். ஒரு சில நாட்களில் பதில் எழுதுகிறேன். <br>
        தமிழ், ஆங்கிலம், ஜெர்மன் — எந்த மொழியிலும் எழுதலாம்.
    </p>

    <?php if ( $email ) : ?>
        <a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-box__email"><?php echo esc_html( $email ); ?></a>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:3rem; margin:3rem 0; text-align:left; padding-top:2rem; border-top:1px solid var(--rule);">
        <div>
            <p class="smallcaps" style="color:var(--ochre); margin-bottom:0.8rem;">இருப்பிடம் · Location</p>
            <p style="font-family:var(--serif-ta); font-size:1.05rem; color:var(--ink); line-height:1.8;"><?php echo esc_html( kv_location() ); ?></p>
        </div>
        <div>
            <p class="smallcaps" style="color:var(--ochre); margin-bottom:0.8rem;">பதில் நேரம் · Reply time</p>
            <p style="font-family:var(--serif-ta); font-size:1.05rem; color:var(--ink); line-height:1.8;">
                பெரும்பாலும் <span class="la">3 — 5</span> நாட்களில்.
            </p>
        </div>
    </div>

    <?php if ( $status === 'ok' ) : ?>
        <div style="background:#F0FEF4; border-left:3px solid #1A6B3A; padding:1.2rem 1.6rem; margin:2rem 0; font-family:var(--serif-ta); color:#1A4A1A; text-align:left;">
            ✓ உங்கள் செய்தி வந்தடைந்தது. நன்றி!
        </div>
    <?php elseif ( $status ) : ?>
        <div style="background:#FEF0F0; border-left:3px solid var(--oxblood); padding:1.2rem 1.6rem; margin:2rem 0; font-family:var(--serif-ta); color:var(--oxblood-deep); text-align:left;">
            மன்னிக்கவும், மீண்டும் முயற்சிக்கவும்.
        </div>
    <?php endif; ?>

    <form class="contact-form" method="POST" action="<?php echo esc_url( get_permalink() ); ?>" style="display: flex; flex-direction: column; gap: 0.8rem; text-align: left; margin-top: 3rem; padding-top: 3rem; border-top: 1px solid var(--rule);">
        <?php wp_nonce_field( 'kv_contact', 'kv_nonce' ); ?>

        <p class="smallcaps" style="color:var(--ochre); margin-bottom:1.5rem;">தொடர்பு படிவம் · Or send a message here</p>

        <label class="smallcaps-sm" for="kv-name">உங்கள் பெயர் · Your name</label>
        <input id="kv-name" type="text" name="kv_name" required value="<?php echo esc_attr( wp_unslash( $_POST['kv_name'] ?? '' ) ); ?>"
               style="font-family:var(--serif-ta); font-size:1.05rem; padding:0.9rem 1.1rem; border:1px solid var(--rule); background:var(--paper-warm); color:var(--ink);">

        <label class="smallcaps-sm" for="kv-email">மின்னஞ்சல் · Your email</label>
        <input id="kv-email" type="email" name="kv_email" required value="<?php echo esc_attr( wp_unslash( $_POST['kv_email'] ?? '' ) ); ?>"
               style="font-family:var(--serif-ta); font-size:1.05rem; padding:0.9rem 1.1rem; border:1px solid var(--rule); background:var(--paper-warm); color:var(--ink);">

        <label class="smallcaps-sm" for="kv-msg">செய்தி · Message</label>
        <textarea id="kv-msg" name="kv_msg" rows="6" required
                  style="font-family:var(--serif-ta); font-size:1.05rem; padding:0.9rem 1.1rem; border:1px solid var(--rule); background:var(--paper-warm); color:var(--ink); resize: vertical;"><?php echo esc_textarea( wp_unslash( $_POST['kv_msg'] ?? '' ) ); ?></textarea>

        <button type="submit" name="kv_submit"
                style="font-family:var(--serif-la); font-size:0.78rem; letter-spacing:0.32em; text-transform:uppercase; color:var(--paper); background:var(--ink); border:none; padding:1.1rem 1.6rem; cursor:pointer; align-self:flex-start; margin-top:1rem; transition:background .25s;">
            அனுப்பவும் <span class="la" style="margin-left:0.4em;">→</span>
        </button>
    </form>
</section>

<?php get_footer(); ?>
