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

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Members', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Member <em>login</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Sign in to access your dashboard, your submissions, and member-only resources.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Reader access', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · no signup needed', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Forgot password?', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Reset it here', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Sign in', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Welcome <em>back</em>.', 'vpg-v2' ) ); ?></h2>
          <div class="g-form" style="display:block">
            <?php wp_login_form( [
                'redirect'       => home_url( '/dashboard/' ),
                'label_username' => __( 'Email or username', 'vpg-v2' ),
                'label_password' => __( 'Password', 'vpg-v2' ),
                'label_remember' => __( 'Stay logged in', 'vpg-v2' ),
                'label_log_in'   => __( 'Log in', 'vpg-v2' ),
                'form_id'        => 'vpg-loginform',
                'remember'       => true,
            ] ); ?>
            <p class="g-form__note">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'vpg-v2' ); ?></a>
                &middot;
                <a href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Not a member? Join free', 'vpg-v2' ); ?></a>
            </p>
          </div>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'Your dashboard, your submissions, your <em>archive</em>.', 'vpg-v2' ) ); ?></p>
        </aside>
      </div>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Not a member yet?', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Membership is <em>free</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center">
        <?php esc_html_e( 'Two minutes, no fees. Contribute to the map and journal, build your public portfolio, download every issue as a PDF.', 'vpg-v2' ); ?>
      </p>
      <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>

</main>
<?php get_footer();
