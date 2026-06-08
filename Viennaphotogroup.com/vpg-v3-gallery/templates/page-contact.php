<?php
/** Template Name: Contact */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$id = vpg_identity();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Contact', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Send a <em>message</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Editorial enquiries, submissions, press, partnerships — one inbox, one editor reading every message.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Direct', 'vpg-v2' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a></dd>
          <dt><?php esc_html_e( 'Response', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Within 72 hours', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Location', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $id['location'] ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Get in touch', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Write to the <em>desk</em>.', 'vpg-v2' ) ); ?></h2>

          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_contact' ); ?>
            <input type="hidden" name="action" value="vpg_contact">
            <div class="g-field--row">
              <div class="g-field"><label for="vpg-name"><?php esc_html_e( 'Your name', 'vpg-v2' ); ?></label><input class="g-input" id="vpg-name" type="text" name="name" required></div>
              <div class="g-field"><label for="vpg-email"><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label><input class="g-input" id="vpg-email" type="email" name="email" required></div>
            </div>
            <div class="g-field">
              <label for="vpg-topic"><?php esc_html_e( 'Topic', 'vpg-v2' ); ?></label>
              <select class="g-select" id="vpg-topic" name="topic">
                <option>Editorial enquiry</option>
                <option>Location submission</option>
                <option>Studio / shop listing</option>
                <option>Membership question</option>
                <option>Press / interview</option>
                <option>Partnership</option>
                <option>Bug / typo report</option>
                <option>Other</option>
              </select>
            </div>
            <div class="g-field"><label for="vpg-message"><?php esc_html_e( 'Message', 'vpg-v2' ); ?></label><textarea class="g-textarea" id="vpg-message" name="message" rows="6" required placeholder="<?php esc_attr_e( 'Be specific. We read everything.', 'vpg-v2' ); ?>"></textarea></div>
            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Send message', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note"><?php printf( wp_kses_post( __( 'By submitting you agree to our <a href="%s">privacy policy</a>. We never share your email.', 'vpg-v2' ) ), esc_url( home_url('/privacy/') ) ); ?></p>
          </form>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'One editor reads every <em>message</em>. We answer in the order received.', 'vpg-v2' ) ); ?></p>
          <dl class="g-phero__aside" style="margin-top:28px">
            <dt><?php esc_html_e( 'Direct', 'vpg-v2' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $id['email'] ); ?>"><?php echo esc_html( $id['email'] ); ?></a> · <?php esc_html_e( 'cleanest path for URLs, attachments, longer threads.', 'vpg-v2' ); ?></dd>
            <dt><?php esc_html_e( 'Response', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Within 72 hours · weekday mornings, Vienna time.', 'vpg-v2' ); ?></dd>
            <dt><?php esc_html_e( 'Location', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $id['location'] ); ?> · <?php esc_html_e( 'meetings by appointment only.', 'vpg-v2' ); ?></dd>
          </dl>
        </aside>
      </div>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Members', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:18ch"><?php echo wp_kses_post( __( 'Want to <em>submit</em> a location or studio?', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'Use the member submission flow · faster review · auto-mapped · proper data capture.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url('/submit/') ); ?>"><?php esc_html_e( 'Open submission form', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>
    </div>
  </section>

</main>
<?php get_footer();
