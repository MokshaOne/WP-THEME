<?php
/** Template Name: FAQ */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'FAQ', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Common <em>questions</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Membership, the location index, the magazine, submissions. If your question is not here, send a note · we read every message.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Topics', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Membership · map · magazine', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Response', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'We read every message', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">

      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'About VPG', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Who we <em>are</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-faq">
        <details open><summary><?php esc_html_e( 'Who runs Vienna Photo Group?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'A small editorial circle of photographers based in Wien. Founded December 2019. Member-run · ad-free · no investors. The platform is co-produced by Raveenthiran and on1.agency.', 'vpg-v2' ); ?></p></details>
        <details><summary><?php esc_html_e( 'Is VPG a non-profit?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Not formally registered as one — practically, yes. Membership fees cover hosting, print services and small honorariums. No commercial profit, no equity, no acquisition target.', 'vpg-v2' ); ?></p></details>
      </div>

      <div class="g-head" style="margin-top:clamp(36px,5vw,56px)">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Membership', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Joining <em>VPG</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-faq">
        <details><summary><?php esc_html_e( 'What does my membership get me?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php printf( wp_kses_post( __( 'PDF download of every magazine issue · submission rights to the location map and studio directory · event discounts · early access to new issues · a member profile page. See the <a href="%s">full membership comparison</a>.', 'vpg-v2' ) ), esc_url( home_url('/membership/') ) ); ?></p></details>
        <details><summary><?php esc_html_e( 'How do I pay?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'After signup you receive an email with payment instructions (SEPA / IBAN for EU members, PayPal for international). Membership activates within 24 hours of payment confirmation.', 'vpg-v2' ); ?></p></details>
        <details><summary><?php esc_html_e( 'Can I cancel?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Membership is annual · no auto-renewal. If you don\'t renew, your Member tier downgrades to Reader at the end of the year — you still see everything, just no submissions or PDFs.', 'vpg-v2' ); ?></p></details>
      </div>

      <div class="g-head" style="margin-top:clamp(36px,5vw,56px)">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'The magazine', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Every <em>issue</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-faq">
        <details><summary><?php esc_html_e( 'How often does the magazine ship?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Monthly · usually around the first of the month. Late only when the city gives us a reason to wait (special exhibitions, light conditions, members travelling).', 'vpg-v2' ); ?></p></details>
        <details><summary><?php esc_html_e( 'Can I write for the magazine?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Yes · members can pitch articles via the submission form. Editorial reviews every pitch within two weeks. Approved pitches land in a future issue with full credit and a member profile link.', 'vpg-v2' ); ?></p></details>
        <details><summary><?php esc_html_e( 'Who owns submitted content?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'You do. Members retain full copyright to text, photos and data. By submitting you grant VPG a non-exclusive, non-transferable licence to publish and archive the work indefinitely. Pull anytime.', 'vpg-v2' ); ?></p></details>
      </div>

      <div class="g-head" style="margin-top:clamp(36px,5vw,56px)">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Locations &amp; submissions', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'On the <em>map</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-faq">
        <details><summary><?php esc_html_e( 'How do I add a location to the map?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php printf( wp_kses_post( __( 'Log in &rarr; <a href="%s">/submit/</a> &rarr; pick "Location" &rarr; type the address (the form auto-pins it on the map) &rarr; add notes about light, access and best time of day. Editorial reviews within 72h.', 'vpg-v2' ) ), esc_url( home_url('/submit/') ) ); ?></p></details>
        <details><summary><?php esc_html_e( 'What gets rejected?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Locations that are clearly private / trespassing · entries we cannot verify on the ground · duplicates of existing pins · anything that endangers the photographer or the place. Most submissions are accepted with light edits.', 'vpg-v2' ); ?></p></details>
      </div>

      <div class="g-head" style="margin-top:clamp(36px,5vw,56px)">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Technical', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Under the <em>hood</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-faq">
        <details><summary><?php esc_html_e( 'Why is the public site read-only?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'To protect the integrity of the index. Public write-access invites spam, AI-generated junk and astroturfed listings. Submissions go through members who walked the city · the data stays clean.', 'vpg-v2' ); ?></p></details>
        <details><summary><?php esc_html_e( 'Is there an API?', 'vpg-v2' ); ?></summary><p class="g-faq__a"><?php esc_html_e( 'Not yet. The data is REST-readable via WordPress\'s default endpoints for logged-in users · a proper public API with rate limits is on the 2026 roadmap.', 'vpg-v2' ); ?></p></details>
      </div>

    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Still unsure', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Ask us <em>directly</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'If your question is not on this page, send a note. One editor reads every message.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url('/contact/') ); ?>"><?php esc_html_e( 'Send a message', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>
    </div>
  </section>

</main>
<?php get_footer();
