<?php
/** Template Name: Submit content */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Submit', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Feed the <em>matrix</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Members submit locations, studios, shops, reviews and tutorial pitches directly to the index. Editorial reviews within 72 hours.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Access', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Members only', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Review', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( '72h editorial turnaround', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Submit', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Locations · studios · shops · reviews · tutorials', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

<?php if ( ! is_user_logged_in() ) : ?>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Members only', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:18ch"><?php echo wp_kses_post( __( 'Submissions are <em>members-only</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'Frontend submission · members of the inner circle contribute directly to the matrix.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/membership/' ) ); ?>"><?php esc_html_e( 'Join the wait-list', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Already a member? Log in', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

<?php else : ?>

  <!-- What can I submit -->
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Five things you can submit', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'What feeds the <em>index</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php esc_html_e( '72h editorial review', 'vpg-v2' ); ?></div>
      </div>
      <div class="g-grid3">
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Location', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A shooting spot', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'An address, GPS pin, light direction, optimal time of day, access notes.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Studio', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A rental space', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Studio size, rate, daylight or strobe, vehicle access, booking link.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Shop', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A supplier', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Camera shop, film lab, gear retailer · district, what they stock, opening hours.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Review', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'Gear scored', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'A camera, lens, accessory you actually own · scored on Design / Performance / Price /10.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Tutorial', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A how-to pitch', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'A skill, technique or workflow you can write up. We’ll edit collaboratively.', 'vpg-v2' ); ?></p></article>
      </div>
    </div>
  </section>

  <!-- The submission form -->
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'New submission', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo wp_kses_post( __( 'Tell us what you’ve <em>found</em>.', 'vpg-v2' ) ); ?></h2>

          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:none">
            <?php wp_nonce_field( 'vpg_submit' ); ?>
            <input type="hidden" name="action" value="vpg_submit">

            <div class="g-field">
              <label for="submit_type"><?php esc_html_e( 'What are you submitting?', 'vpg-v2' ); ?></label>
              <select class="g-select" id="submit_type" name="submit_type" required>
                <option value="vpg_location"><?php esc_html_e( 'Location · a shooting spot', 'vpg-v2' ); ?></option>
                <option value="vpg_studio"><?php esc_html_e( 'Studio · a rental space', 'vpg-v2' ); ?></option>
                <option value="vpg_shop"><?php esc_html_e( 'Shop · a camera shop / lab / supplier', 'vpg-v2' ); ?></option>
                <option value="vpg_review"><?php esc_html_e( 'Gear review · with /10 scores', 'vpg-v2' ); ?></option>
                <option value="vpg_tutorial"><?php esc_html_e( 'Tutorial pitch · we’ll discuss', 'vpg-v2' ); ?></option>
              </select>
            </div>

            <div class="g-field">
              <label for="title"><?php esc_html_e( 'Title', 'vpg-v2' ); ?></label>
              <input class="g-input" id="title" type="text" name="title" required placeholder="<?php esc_attr_e( 'The Stephansdom rooftop · golden hour', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="lede"><?php esc_html_e( 'One-line description', 'vpg-v2' ); ?></label>
              <input class="g-input" id="lede" type="text" name="lede" placeholder="<?php esc_attr_e( 'South-facing terrace · 35m elevation · free access via café', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="body"><?php esc_html_e( 'Body / notes', 'vpg-v2' ); ?></label>
              <textarea class="g-textarea" id="body" name="body" rows="8" required placeholder="<?php esc_attr_e( 'Light direction, best time of day, what to bring, access notes, anything you wish you’d known before going.', 'vpg-v2' ); ?>"></textarea>
            </div>

            <div class="g-field">
              <label for="district"><?php esc_html_e( 'District / area', 'vpg-v2' ); ?></label>
              <input class="g-input" id="district" type="text" name="district" placeholder="<?php esc_attr_e( '1010 · Innere Stadt', 'vpg-v2' ); ?>">
            </div>

            <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Submit for review', 'vpg-v2' ); ?> <span class="a">→</span></button>
            <p class="g-form__note">
              <?php esc_html_e( 'Submissions are queued for editorial review. You’ll receive an email when your entry is approved and published. Coordinates can be added by the editor or by you later from the post edit screen · the map-picker is on the post editor.', 'vpg-v2' ); ?>
            </p>
          </form>
        </div>
        <aside>
          <p class="g-pull"><?php echo wp_kses_post( __( 'Specific beats vague. You shot there, you bought there, you <em>own</em> the gear.', 'vpg-v2' ) ); ?></p>
        </aside>
      </div>
    </div>
  </section>

  <!-- Editorial guidelines -->
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Editorial guidelines', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'What gets <em>accepted</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-prose" style="margin:0 auto">
        <ul>
          <li><strong><?php esc_html_e( 'Specific', 'vpg-v2' ); ?></strong> · <?php esc_html_e( '"rooftop of Café X with south view" beats "central Vienna".', 'vpg-v2' ); ?></li>
          <li><strong><?php esc_html_e( 'First-hand', 'vpg-v2' ); ?></strong> · <?php esc_html_e( 'you shot there, you bought from there, you actually own the gear.', 'vpg-v2' ); ?></li>
          <li><strong><?php esc_html_e( 'Verifiable', 'vpg-v2' ); ?></strong> · <?php esc_html_e( 'the place exists, the shop is still open, the lens is currently on sale.', 'vpg-v2' ); ?></li>
          <li><strong><?php esc_html_e( 'Safe', 'vpg-v2' ); ?></strong> · <?php esc_html_e( 'no trespass, no endangered listings, no recommending hostile spaces.', 'vpg-v2' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Rejected submissions are not deleted from the queue · the editor will message you with feedback so you can revise. Most submissions go through with light edits.', 'vpg-v2' ); ?></p>
      </div>
    </div>
  </section>

<?php endif; ?>

</main>
<?php get_footer();
