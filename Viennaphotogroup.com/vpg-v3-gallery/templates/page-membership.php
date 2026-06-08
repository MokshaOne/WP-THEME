<?php
/** Template Name: Membership tiers */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Membership', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Three ways to <em>belong</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'VPG is member-run, ad-free, and built on people who actually walk the city. Pick the tier that fits how deeply you want to feed the matrix.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Status', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( '◐ Beta phase · paid tiers open soon', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Reader', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · fully active', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Paid', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Member & Sustaining · wait-list open', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Choose your tier', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Three ways <em>in</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php esc_html_e( 'Open beta · Reader active', 'vpg-v2' ); ?></div>
      </div>

      <p class="g-lede" style="max-width:62ch;margin:0 auto clamp(36px,5vw,56px);text-align:center">
        <?php echo wp_kses_post( __( 'VPG is currently in <em>open beta</em>. The free <strong>Reader</strong> tier is fully active. The paid tiers (<strong>Member</strong> and <strong>Sustaining</strong>) open when we leave beta · expected shortly. You can join the wait-list now.', 'vpg-v2' ) ); ?>
      </p>

      <div class="g-price">

        <!-- ─── READER · available ─── -->
        <div class="g-plan g-plan--feat">
          <span class="g-plan__name"><?php esc_html_e( 'Reader · Active', 'vpg-v2' ); ?></span>
          <div class="g-plan__price"><?php esc_html_e( 'Free', 'vpg-v2' ); ?></div>
          <p style="margin:0 0 6px;color:rgba(255,255,255,.7);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'Browse the magazine, the location index, all reviews and tutorials — open access, no signup.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php esc_html_e( 'Read every magazine issue online', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Browse the full location map', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Studio and shop directory', 'vpg-v2' ); ?></li>
            <li><?php echo wp_kses_post( __( 'Buying guide &amp; tutorials', 'vpg-v2' ) ); ?></li>
            <li><?php esc_html_e( 'Newsletter when an issue ships', 'vpg-v2' ); ?></li>
          </ul>
          <a class="g-btn g-btn--red" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>"><?php esc_html_e( 'Start reading', 'vpg-v2' ); ?> <span class="a">→</span></a>
        </div>

        <!-- ─── MEMBER · grayed during beta ─── -->
        <div class="g-plan" style="opacity:.6">
          <span class="g-plan__name"><?php esc_html_e( 'Member · Beta · soon', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€60<small> / year</small></div>
          <p style="margin:0 0 6px;color:var(--g-mid);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'For active creators who shoot Wien regularly and want to contribute to the index.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php esc_html_e( 'Everything in Reader', 'vpg-v2' ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>PDF download</strong> of every magazine issue', 'vpg-v2' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>Submit locations</strong> directly to the map', 'vpg-v2' ) ); ?></li>
            <li><?php esc_html_e( 'Studio-share directory access', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Event discounts', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Early access to new issues', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Member profile page', 'vpg-v2' ); ?></li>
          </ul>
          <button class="g-btn g-btn--ghost" disabled style="cursor:not-allowed"><?php esc_html_e( 'Available after beta', 'vpg-v2' ); ?></button>
        </div>

        <!-- ─── SUSTAINING · grayed during beta ─── -->
        <div class="g-plan" style="opacity:.6">
          <span class="g-plan__name"><?php esc_html_e( 'Sustaining · Beta · soon', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€180<small> / year</small></div>
          <p style="margin:0 0 6px;color:var(--g-mid);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'For photographers who want to support the platform structurally · keeps VPG ad-free forever.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php esc_html_e( 'Everything in Member', 'vpg-v2' ); ?></li>
            <li><?php echo wp_kses_post( __( 'Name listed in every issue&rsquo;s colophon', 'vpg-v2' ) ); ?></li>
            <li><?php esc_html_e( 'Signed annual print collection', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Priority editorial review', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Invitation to the yearly editorial dinner', 'vpg-v2' ); ?></li>
          </ul>
          <button class="g-btn g-btn--ghost" disabled style="cursor:not-allowed"><?php esc_html_e( 'Available after beta', 'vpg-v2' ); ?></button>
        </div>
      </div>
    </div>
  </section>

  <!-- Wait-list signup -->
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Wait-list', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Be first when paid tiers <em>open</em>.', 'vpg-v2' ) ); ?></h2>
          <p class="g-lede" style="margin-bottom:28px"><?php esc_html_e( 'One email when we leave beta · founding-member rate locked in for the first 100 sign-ups · then full price applies.', 'vpg-v2' ); ?></p>
          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_contact' ); ?>
            <input type="hidden" name="action" value="vpg_contact">
            <input type="hidden" name="topic" value="Wait-list · paid tiers">
            <input type="hidden" name="message" value="Wait-list signup for paid tier launch">
            <div class="g-field--row">
              <div class="g-field"><label for="wl-name"><?php esc_html_e( 'Your name', 'vpg-v2' ); ?></label><input class="g-input" id="wl-name" type="text" name="name" required></div>
              <div class="g-field"><label for="wl-email"><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label><input class="g-input" id="wl-email" type="email" name="email" required></div>
            </div>
            <div class="g-field">
              <label for="wl-tier"><?php esc_html_e( 'Which tier interests you?', 'vpg-v2' ); ?></label>
              <select class="g-select" id="wl-tier" name="topic" onchange="this.form.elements['message'].value='Wait-list · '+this.value">
                <option value="Wait-list · Member tier (€60/yr)">Member · €60/yr</option>
                <option value="Wait-list · Sustaining tier (€180/yr)">Sustaining · €180/yr</option>
                <option value="Wait-list · undecided">Not sure yet</option>
              </select>
            </div>
            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Join the wait-list', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note"><?php esc_html_e( 'No commitment, no payment requested. Just an email when paid tiers open.', 'vpg-v2' ); ?></p>
          </form>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'No ads, no investors, no <em>acquisition target</em>.', 'vpg-v2' ) ); ?></p>
        </aside>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Why member-run', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'No ads, no investors, no <em>acquisition target</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-prose" style="margin:0 auto">
        <p class="g-prose__drop"><?php esc_html_e( 'VPG will run on member fees alone — once we exit beta. There are no advertisers to please, no growth-at-all-costs investor expectations, no plan to flip to a content network. Membership will keep the index uncompromised, the magazine ad-free, and the editorial calendar in the hands of people who actually shoot Vienna.', 'vpg-v2' ); ?></p>
        <p><?php echo wp_kses_post( __( 'If membership ever stops being affordable for you, the Reader tier stays free. We&rsquo;d rather you keep reading than feel locked out.', 'vpg-v2' ) ); ?></p>
      </div>
    </div>
  </section>

</main>
<?php get_footer();
