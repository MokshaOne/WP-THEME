<?php
/** Template Name: Member login */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/dashboard/' ) );
    exit;
}
?>
<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip"><span class="vpg-chip__dot"></span> Members</span>
    <h1>Member <em>login</em>.</h1>
    <p class="vpg-lede">Sign in to access your dashboard, your submissions, and member-only resources.</p>
</header>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap--narrow">
        <?php wp_login_form( [
            'redirect'       => home_url( '/dashboard/' ),
            'label_username' => __( 'Email or username', 'vpg-v2' ),
            'label_password' => __( 'Password', 'vpg-v2' ),
            'label_remember' => __( 'Stay logged in', 'vpg-v2' ),
            'label_log_in'   => __( 'Log in', 'vpg-v2' ),
            'form_id'        => 'vpg-loginform',
            'remember'       => true,
        ] ); ?>
        <p style="margin-top:1.5rem;font-size:var(--vpg-fs-sm);text-align:center;color:var(--vpg-muted)">
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'vpg-v2' ); ?></a>
            &middot;
            <a href="<?php echo esc_url( home_url('/membership/') ); ?>"><?php esc_html_e( 'Join the wait-list', 'vpg-v2' ); ?></a>
        </p>
    </div>
</section>

<section class="vpg-section vpg-section--surface vpg-section--tight" style="text-align:center">
    <div class="vpg-wrap--narrow">
        <p class="vpg-caps">— Not a member yet?</p>
        <h2 style="font-size:var(--vpg-fs-h3);margin:1rem 0">VPG is in <em>open beta</em>.</h2>
        <p style="color:var(--vpg-ink-mid);max-width:48ch;margin:0 auto 2rem">
            Reader access is free and open · no signup needed. Paid tiers (Member, Sustaining) open when we exit beta. Sign up for the wait-list to be notified.
        </p>
        <a class="vpg-btn vpg-btn--accent" href="<?php echo esc_url( home_url('/membership/') ); ?>">See tiers &amp; join wait-list <span class="vpg-btn__arrow">→</span></a>
    </div>
</section>

</main>
<?php get_footer();
