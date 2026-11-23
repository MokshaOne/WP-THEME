<?php
/** Template Name: Quick Pin
 * 0601 · one screen on the phone: photo, position, done. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( ! is_user_logged_in() ) : ?>
<main id="vpg-main"><section class="g-section"><div class="g-wrap" style="text-align:center">
  <h1 class="g-display" style="font-size:clamp(32px,6vw,64px)"><?php echo wp_kses_post( __( 'Members <em>pin</em>.', 'vpg-v2' ) ); ?></h1>
  <p class="g-lede" style="margin:16px auto 0"><a class="g-link" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log in', 'vpg-v2' ); ?></a> · <a class="g-link" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a></p>
</div></section></main>
<?php get_footer(); return; endif; ?>

<main id="vpg-main">
  <section class="g-section" style="padding-top:clamp(28px,5vw,48px)"><div class="g-wrap" style="max-width:520px">
    <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Quick pin', 'vpg-v2' ); ?></p>
    <h1 class="g-display" style="font-size:clamp(34px,7vw,56px);margin-bottom:10px"><?php echo wp_kses_post( __( 'Seen it? <em>Pin it</em>.', 'vpg-v2' ) ); ?></h1>
    <p class="g-lede" style="font-size:15px;margin-bottom:26px"><?php esc_html_e( 'One photo, your position, a name — editorial fills in the rest with you. Goes straight to the review desk.', 'vpg-v2' ); ?></p>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="display:grid;gap:16px">
      <?php wp_nonce_field( 'vpg_quickpin' ); ?>
      <input type="hidden" name="action" value="vpg_quickpin">
      <input type="hidden" name="pin_lat" id="qp-lat" value="">
      <input type="hidden" name="pin_lng" id="qp-lng" value="">

      <div class="g-field">
        <label for="qp-photo"><?php esc_html_e( 'The photo', 'vpg-v2' ); ?></label>
        <input class="g-input" id="qp-photo" type="file" name="photo" accept="image/*" capture="environment" style="padding:14px">
      </div>

      <div class="g-field">
        <label for="qp-title"><?php esc_html_e( 'What is it?', 'vpg-v2' ); ?></label>
        <input class="g-input" id="qp-title" type="text" name="title" required maxlength="120" placeholder="<?php esc_attr_e( 'Rooftop line at Sophienbrücke', 'vpg-v2' ); ?>">
      </div>

      <div class="g-field">
        <label for="qp-note"><?php esc_html_e( 'One line for editorial · optional', 'vpg-v2' ); ?></label>
        <textarea class="g-textarea" id="qp-note" name="note" rows="2" placeholder="<?php esc_attr_e( 'Best after rain, tripod ok.', 'vpg-v2' ); ?>"></textarea>
      </div>

      <p id="qp-geo" class="g-meta" style="margin:0">◌ <?php esc_html_e( 'Reading your position…', 'vpg-v2' ); ?></p>

      <button class="g-btn g-btn--red" type="submit" style="padding:16px"><?php esc_html_e( 'Pin it → review desk', 'vpg-v2' ); ?></button>
    </form>

    <script>
    (function () {
      var out = document.getElementById('qp-geo');
      if (!navigator.geolocation) { out.textContent = <?php echo wp_json_encode( __( 'No GPS — editorial will place the pin from your note.', 'vpg-v2' ) ); ?>; return; }
      navigator.geolocation.getCurrentPosition(function (pos) {
        document.getElementById('qp-lat').value = pos.coords.latitude.toFixed(6);
        document.getElementById('qp-lng').value = pos.coords.longitude.toFixed(6);
        out.textContent = '◉ ' + <?php echo wp_json_encode( __( 'Position locked:', 'vpg-v2' ) ); ?> + ' ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4);
      }, function () {
        out.textContent = <?php echo wp_json_encode( __( 'Position unavailable — editorial will place the pin from your note.', 'vpg-v2' ) ); ?>;
      }, { enableHighAccuracy: true, timeout: 8000 });
    })();
    </script>
  </div></section>
</main>
<?php get_footer();
