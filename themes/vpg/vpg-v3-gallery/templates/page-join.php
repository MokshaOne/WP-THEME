<?php
/** Template Name: Join VPG */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Membership · free', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Join the <em>collective</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'A free membership: contribute to the map and journal, build your public portfolio, and download every issue as a PDF. No ads, no fees — member-run.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Price', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · takes two minutes', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Includes', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Submissions · portfolio · PDFs · events', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Your photos', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Stay yours · credited by name', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'No', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Ads · trackers · upsells', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

<?php if ( is_user_logged_in() ) : ?>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Already in', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'You&rsquo;re a <em>member</em>.', 'vpg-v2' ) ); ?></h2>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Open your dashboard', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Submit something', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

<?php else : ?>

  <!-- ── SIGN-UP FORM ── -->
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Sign up', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Put your name <em>down</em>.', 'vpg-v2' ) ); ?></h2>

          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:none">
            <?php wp_nonce_field( 'vpg_join' ); ?>
            <input type="hidden" name="action" value="vpg_join">
            <?php echo vpg_antispam_fields(); ?>

            <div class="g-field">
              <label for="join-name"><?php esc_html_e( 'Name', 'vpg-v2' ); ?></label>
              <input class="g-input" id="join-name" type="text" name="name" required autocomplete="name" placeholder="<?php esc_attr_e( 'The name under your photographs', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="join-email"><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label>
              <input class="g-input" id="join-email" type="email" name="email" required autocomplete="email" inputmode="email" placeholder="you@example.com">
            </div>

            <div class="g-field">
              <label for="join-password"><?php esc_html_e( 'Password', 'vpg-v2' ); ?></label>
              <input class="g-input" id="join-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="<?php esc_attr_e( 'At least 8 characters', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label><?php esc_html_e( 'What do you shoot? · optional', 'vpg-v2' ); ?></label>
              <div style="display:flex;gap:8px;flex-wrap:wrap">
                <?php foreach ( [ 'street' => __( 'Street', 'vpg-v2' ), 'architecture' => __( 'Architecture', 'vpg-v2' ), 'portrait' => __( 'Portrait', 'vpg-v2' ), 'analog' => __( 'Analog', 'vpg-v2' ), 'macro' => __( 'Macro', 'vpg-v2' ), 'night' => __( 'Night', 'vpg-v2' ) ] as $gv => $gl ) : ?>
                  <label style="display:inline-flex;gap:6px;align-items:center;border:1px solid var(--g-line-2);padding:8px 12px;cursor:pointer;font-size:13px;font-weight:600">
                    <input type="checkbox" name="genres[]" value="<?php echo esc_attr( $gv ); ?>"> <?php echo esc_html( $gl ); ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Become a member', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note">
              <?php esc_html_e( 'Free forever · you\'re logged in right away · we send one confirmation email to unlock submissions. No spam, leave anytime.', 'vpg-v2' ); ?>
            </p>
          </form>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'Your photograph. Your name under it. <em>Every time.</em>', 'vpg-v2' ) ); ?></p>
        </aside>
      </div>
    </div>
  </section>

  <!-- ── WHAT MEMBERS GET ── -->
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'What members get', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'All of it, <em>free</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-grid3">
        <article class="g-card"><span class="g-cat">01</span><h3 class="g-card__title"><?php esc_html_e( 'Get published', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Submit to the map and the journal — your work in the magazine, your name under it.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">02</span><h3 class="g-card__title"><?php esc_html_e( 'A public portfolio', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Your own member page — a quiet place to show your best frames.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">03</span><h3 class="g-card__title"><?php esc_html_e( 'The Map', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( '118+ curated Vienna locations with light notes and best times — and the right to add your own.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">04</span><h3 class="g-card__title"><?php esc_html_e( 'Every issue · PDF', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Download every issue of the magazine as a PDF. Forever-keepable.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">05</span><h3 class="g-card__title"><?php esc_html_e( 'Events', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Photowalks and meetups across the 23 Bezirke — shoot with people who look.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat">06</span><h3 class="g-card__title"><?php esc_html_e( 'Competitions', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Member competitions with honest feedback — the kind likes can\'t give you.', 'vpg-v2' ); ?></p></article>
      </div>
    </div>
  </section>

  <!-- ── LATER · SUPPORTER TIERS ── -->
  <section class="g-section--dark g-section" id="supporter">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Later · optional', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:18ch"><?php echo wp_kses_post( __( 'Paid supporter tiers come <em>later</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center">
        <?php esc_html_e( 'Membership stays free. Optional supporter tiers — print extras, a colophon credit — arrive once the community has found its feet. Nothing you get today ever moves behind a paywall.', 'vpg-v2' ); ?>
      </p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="#vpg-main"><?php esc_html_e( 'Join free now', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Read the magazine first', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

<?php endif; ?>

</main>
<?php get_footer();
