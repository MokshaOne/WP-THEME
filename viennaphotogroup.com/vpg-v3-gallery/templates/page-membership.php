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
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'VPG is member-run, ad-free, and built on people who actually walk the city. Membership is free — optional supporter tiers come later.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Membership', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · active now', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Supporter tiers', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Optional · come later', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'No', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Ads · trackers · upsells', 'vpg-v2' ); ?></dd>
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
        <div class="g-meta"><?php esc_html_e( 'Membership free · active', 'vpg-v2' ); ?></div>
      </div>

      <p class="g-lede" style="max-width:62ch;margin:0 auto clamp(36px,5vw,56px);text-align:center">
        <?php echo wp_kses_post( __( '<strong>Membership is free</strong> and active now — everything below, no fees. Optional <strong>supporter tiers</strong> come later, for people who want to fund the print side. Nothing that is free today ever moves behind a paywall.', 'vpg-v2' ) ); ?>
      </p>

      <div class="g-price">

        <!-- ─── MEMBER · free · active ─── -->
        <div class="g-plan g-plan--feat">
          <span class="g-plan__name"><?php esc_html_e( 'Member · Active', 'vpg-v2' ); ?></span>
          <div class="g-plan__price"><?php esc_html_e( 'Free', 'vpg-v2' ); ?></div>
          <p style="margin:0 0 6px;color:rgba(255,255,255,.7);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'For everyone who takes looking seriously — two minutes to join, yours to keep.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php echo wp_kses_post( __( '<strong>Submit</strong> to the map and the journal', 'vpg-v2' ) ); ?></li>
            <li><?php echo wp_kses_post( __( '<strong>PDF download</strong> of every magazine issue', 'vpg-v2' ) ); ?></li>
            <li><?php esc_html_e( 'Your public member profile page', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Events, photowalks & competitions', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Newsletter when an issue ships', 'vpg-v2' ); ?></li>
          </ul>
          <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> <span class="a">→</span></a>
        </div>

        <!-- ─── SUPPORTER · later ─── -->
        <div class="g-plan" style="opacity:.6">
          <span class="g-plan__name"><?php esc_html_e( 'Supporter · Later', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€60<small> / year</small></div>
          <p style="margin:0 0 6px;color:var(--g-mid);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'Planned · for members who want to fund the print run and the platform.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php esc_html_e( 'Everything in Member', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Twice-yearly print run, posted', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Early access to new issues', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Event discounts', 'vpg-v2' ); ?></li>
          </ul>
          <button class="g-btn g-btn--ghost" disabled style="cursor:not-allowed"><?php esc_html_e( 'Not open yet', 'vpg-v2' ); ?></button>
        </div>

        <!-- ─── SUSTAINING · later ─── -->
        <div class="g-plan" style="opacity:.6">
          <span class="g-plan__name"><?php esc_html_e( 'Sustaining · Later', 'vpg-v2' ); ?></span>
          <div class="g-plan__price">€180<small> / year</small></div>
          <p style="margin:0 0 6px;color:var(--g-mid);font-size:14.5px;line-height:1.5"><?php esc_html_e( 'Planned · for photographers who want to support the platform structurally · keeps VPG ad-free forever.', 'vpg-v2' ); ?></p>
          <ul>
            <li><?php esc_html_e( 'Everything in Supporter', 'vpg-v2' ); ?></li>
            <li><?php echo wp_kses_post( __( 'Name listed in every issue&rsquo;s colophon', 'vpg-v2' ) ); ?></li>
            <li><?php esc_html_e( 'Signed annual print collection', 'vpg-v2' ); ?></li>
            <li><?php esc_html_e( 'Invitation to the yearly editorial dinner', 'vpg-v2' ); ?></li>
          </ul>
          <button class="g-btn g-btn--ghost" disabled style="cursor:not-allowed"><?php esc_html_e( 'Not open yet', 'vpg-v2' ); ?></button>
        </div>
      </div>
    </div>
  </section>

  <!-- Wait-list signup -->
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Supporter tiers · later', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'One email when supporter tiers <em>open</em>.', 'vpg-v2' ) ); ?></h2>
          <p class="g-lede" style="margin-bottom:28px"><?php esc_html_e( 'Membership stays free either way. Leave your address and we send exactly one email when the optional supporter tiers launch.', 'vpg-v2' ); ?></p>
          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'vpg_contact' ); ?>
            <input type="hidden" name="action" value="vpg_contact">
            <?php echo vpg_antispam_fields(); ?>
            <input type="hidden" name="topic" value="Supporter tiers · interest">
            <input type="hidden" name="message" value="Notify me when supporter tiers open">
            <div class="g-field--row">
              <div class="g-field"><label for="wl-name"><?php esc_html_e( 'Your name', 'vpg-v2' ); ?></label><input class="g-input" id="wl-name" type="text" name="name" required></div>
              <div class="g-field"><label for="wl-email"><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label><input class="g-input" id="wl-email" type="email" name="email" required></div>
            </div>
            <div class="g-field">
              <label for="wl-tier"><?php esc_html_e( 'Which tier interests you?', 'vpg-v2' ); ?></label>
              <select class="g-select" id="wl-tier" name="topic" onchange="this.form.elements['message'].value='Supporter interest · '+this.value">
                <option value="Supporter interest · Supporter (€60/yr planned)">Supporter · €60/yr planned</option>
                <option value="Supporter interest · Sustaining (€180/yr planned)">Sustaining · €180/yr planned</option>
                <option value="Supporter interest · undecided">Not sure yet</option>
              </select>
            </div>
            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Keep me posted', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note"><?php esc_html_e( 'No commitment, no payment requested. Just one email when supporter tiers open.', 'vpg-v2' ); ?></p>
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
        <p class="g-prose__drop"><?php esc_html_e( 'VPG runs on its members, not on advertisers. There are no advertisers to please, no growth-at-all-costs investor expectations, no plan to flip to a content network. When supporter tiers arrive, they fund the print run — they never decide the editorial calendar. That stays in the hands of people who actually shoot Vienna.', 'vpg-v2' ); ?></p>
        <p><?php echo wp_kses_post( __( 'And membership itself stays free. We&rsquo;d rather you join and contribute than feel locked out.', 'vpg-v2' ) ); ?></p>
      </div>
    </div>
  </section>

</main>
<?php get_footer();
