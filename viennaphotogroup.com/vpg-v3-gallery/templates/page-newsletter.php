<?php
/** Template Name: Newsletter signup */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Newsletter', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'One email when <em>the issue ships</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'No marketing. No drip campaigns. One short letter per month when the new issue is live · cover, contents, a 30-word editor’s line. That’s it.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Cadence', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Monthly · ~1KB', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Contents', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Cover · three titles · one link', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Unsubscribe', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'One click, any email', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Sign up', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Get the <em>letter</em>.', 'vpg-v2' ) ); ?></h2>

          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_newsletter' ); ?>
            <input type="hidden" name="action" value="vpg_newsletter">
            <?php if ( function_exists( 'vpg_antispam_fields' ) ) echo vpg_antispam_fields(); ?>
            <div class="g-field">
              <label for="name"><?php esc_html_e( 'Your name', 'vpg-v2' ); ?></label>
              <input class="g-input" id="name" type="text" name="name" required autocomplete="name">
            </div>
            <div class="g-field">
              <label for="email"><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label>
              <input class="g-input" id="email" type="email" name="email" required autocomplete="email" inputmode="email" placeholder="you@studio.com">
            </div>
            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Subscribe', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note"><?php
              printf(
                wp_kses_post( __( 'We use your email only to send issue notifications. Unsubscribe with one click in any email. See <a href="%s">privacy</a>.', 'vpg-v2' ) ),
                esc_url( home_url( '/privacy/' ) )
              );
            ?></p>
          </form>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'Three lines, <em>one link</em>. No clickbait, no curiosity gaps.', 'vpg-v2' ) ); ?></p>
        </aside>
      </div>
    </div>
  </section>

  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'What you get', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Three lines, <em>one link</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php esc_html_e( 'Monthly · ~1KB', 'vpg-v2' ); ?></div>
      </div>
      <div class="g-grid3">
        <article class="g-card"><span class="g-cat">i.</span><h3 class="g-card__title"><?php esc_html_e( 'Issue cover', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'A small JPG of the cover · so you remember what month it is.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">ii.</span><h3 class="g-card__title"><?php esc_html_e( 'Three article titles', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'The headlines of the lead pieces. No clickbait, no curiosity gaps.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">iii.</span><h3 class="g-card__title"><?php esc_html_e( 'One link', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Straight to the issue. Members get the PDF link in the same email · readers get the web link.', 'vpg-v2' ); ?></p></article>
      </div>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'RSS', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:14ch"><?php echo wp_kses_post( __( 'Prefer <em>RSS</em>?', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php
        printf(
          wp_kses_post( __( 'Subscribe to %s in your reader. Cover, PDF and issue text as enclosures.', 'vpg-v2' ) ),
          '<code style="font-family:ui-monospace,monospace;background:rgba(255,255,255,.1);padding:.2rem .5rem">/feed/magazine</code>'
        );
      ?></p>
      <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( home_url( '/feed/magazine' ) ); ?>" target="_blank"><?php esc_html_e( 'Open RSS feed', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>

</main>
<?php get_footer();
