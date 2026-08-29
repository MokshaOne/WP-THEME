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
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Members feed the site from here — the map first, then reviews, journal stories, events and trails as your rank grows. Editorial reviews within 72 hours; higher ranks publish instantly.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Access', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Members only', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Review', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( '72h editorial turnaround', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Submit', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Map · reviews · journal · events · trails', 'vpg-v2' ); ?></dd>
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
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Already a member? Log in', 'vpg-v2' ); ?></a>
      </div>
    </div>
  </section>

<?php elseif ( function_exists( 'vpg_is_verified' ) && ! vpg_is_verified() ) : ?>

  <!-- Email not yet confirmed · submissions locked -->
  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'One step left', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:18ch"><?php echo wp_kses_post( __( 'Confirm your <em>email</em> first.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'We sent you a confirmation link when you joined. One click and submissions unlock.', 'vpg-v2' ); ?></p>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block">
        <?php wp_nonce_field( 'vpg_resend_verify' ); ?>
        <input type="hidden" name="action" value="vpg_resend_verify">
        <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Resend the email', 'vpg-v2' ); ?> <span class="a">→</span></button>
      </form>
    </div>
  </section>

<?php else : ?>

  <!-- What can I submit -->
  <section class="g-section">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'What you can submit', 'vpg-v2' ); ?></span>
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
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Journal', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A story', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Writing for the weekly journal — a walk, a theme, a portrait of a place. Unlocks at Contributor.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Event', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A photowalk', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Propose a meetup with date and meeting point — RSVP, reminders and check-in come built in. Unlocks at Documentarian.', 'vpg-v2' ); ?></p></article>
        <article class="g-card"><span class="g-cat"><?php esc_html_e( 'Trail', 'vpg-v2' ); ?></span><h3 class="g-card__title"><?php esc_html_e( 'A walking route', 'vpg-v2' ); ?></h3><p class="g-row__lede"><?php esc_html_e( 'Chain map spots into a route worth an afternoon. Unlocks at Documentarian.', 'vpg-v2' ); ?></p></article>
      </div>
    </div>
  </section>

  <!-- The submission form -->
  <?php
  // Edit mode · drafts and pending always; live pieces with the
  // Documentarian+ edit_live rank privilege.
  $edit_post = null;
  if ( ! empty( $_GET['edit'] ) ) {
      $maybe    = get_post( (int) $_GET['edit'] );
      $statuses = ( function_exists( 'vpg_can_edit_live' ) && vpg_can_edit_live() )
          ? [ 'pending', 'draft', 'publish' ]
          : [ 'pending', 'draft' ];
      if ( $maybe
          && (int) $maybe->post_author === get_current_user_id()
          && in_array( $maybe->post_status, $statuses, true )
          && in_array( $maybe->post_type, function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'vpg_location' ], true ) ) {
          $edit_post = $maybe;
      }
  }
  $ev = function ( $field ) use ( $edit_post ) {
      if ( ! $edit_post ) return '';
      if ( $field === 'district' ) {
          $key = ( $edit_post->post_type === 'vpg_shop' ) ? 'shop_district' : 'location_district';
          return (string) get_post_meta( $edit_post->ID, $key, true );
      }
      return (string) ( $edit_post->{$field} ?? '' );
  };
  ?>
  <section class="g-section g-section--alt">
    <div class="g-wrap">

      <?php // The ladder strip · where you stand, what the next rank needs
      $sub_rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( get_current_user_id() ) : null;
      if ( $sub_rank ) : ?>
      <div class="g-ladder">
        <span class="g-ladder__rank">● <?php echo esc_html( $sub_rank['label'] ); ?></span>
        <?php if ( $sub_rank['next'] ) : ?>
          <span class="g-ladder__bar" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d %3$s', 'vpg-v2' ), $sub_rank['next_have'], $sub_rank['next_need'], $sub_rank['next_goal'] ) ); ?>"><i style="width:<?php echo esc_attr( min( 100, round( $sub_rank['next_have'] / max( 1, $sub_rank['next_need'] ) * 100 ) ) ); ?>%"></i></span>
          <span class="g-ladder__next"><?php printf( esc_html__( '%1$d / %2$d %3$s → %4$s', 'vpg-v2' ), (int) $sub_rank['next_have'], (int) $sub_rank['next_need'], esc_html( $sub_rank['next_goal'] ), esc_html( $sub_rank['next'] ) ); ?></span>
        <?php else : ?>
          <span class="g-ladder__next">★ <?php esc_html_e( 'Resident — everything you submit goes live instantly.', 'vpg-v2' ); ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div class="g-twocol">
        <div>
          <span class="g-kicker"><?php echo $edit_post ? esc_html__( 'Edit submission · still in review', 'vpg-v2' ) : esc_html__( 'New submission', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t" style="margin:14px 0 22px"><?php echo $edit_post ? esc_html( $edit_post->post_title ) : wp_kses_post( __( 'Tell us what you’ve <em>found</em>.', 'vpg-v2' ) ); ?></h2>

          <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:none">
            <?php wp_nonce_field( 'vpg_submit' ); ?>
            <input type="hidden" name="action" value="vpg_submit">
            <?php if ( $edit_post ) : ?><input type="hidden" name="edit_id" value="<?php echo esc_attr( $edit_post->ID ); ?>"><?php endif; ?>
            <?php echo vpg_antispam_fields(); ?>

            <div class="g-field">
              <label><?php esc_html_e( 'What are you submitting?', 'vpg-v2' ); ?></label>
              <?php
              // Locked types stay visible but disabled — everyone sees the
              // ladder: the map first, the editorial formats as you grow.
              $type_cards = [
                  'vpg_location' => [ '◈', __( 'Location', 'vpg-v2' ), __( 'a shooting spot', 'vpg-v2' ) ],
                  'vpg_studio'   => [ '▣', __( 'Studio', 'vpg-v2' ), __( 'a rental space', 'vpg-v2' ) ],
                  'vpg_shop'     => [ '◧', __( 'Shop', 'vpg-v2' ), __( 'shop · lab · supplier', 'vpg-v2' ) ],
                  'vpg_review'   => [ '★', __( 'Gear review', 'vpg-v2' ), __( 'scored /10', 'vpg-v2' ) ],
                  'vpg_tutorial' => [ '✎', __( 'Tutorial pitch', 'vpg-v2' ), __( 'we’ll discuss', 'vpg-v2' ) ],
                  'post'         => [ '¶', __( 'Journal story', 'vpg-v2' ), __( 'for the weekly journal', 'vpg-v2' ) ],
                  'vpg_event'    => [ '◷', __( 'Photowalk / event', 'vpg-v2' ), __( 'propose a meetup', 'vpg-v2' ) ],
                  'vpg_trail'    => [ '⇝', __( 'Photo trail', 'vpg-v2' ), __( 'a walking route', 'vpg-v2' ) ],
              ];
              $unlock_hint = [
                  'vpg_review'   => __( 'Contributor · 25 locations', 'vpg-v2' ),
                  'vpg_tutorial' => __( 'Contributor · 25 locations', 'vpg-v2' ),
                  'post'         => __( 'Contributor · 25 locations', 'vpg-v2' ),
                  'vpg_event'    => __( 'Documentarian · 50 editorial works', 'vpg-v2' ),
                  'vpg_trail'    => __( 'Documentarian · 50 editorial works', 'vpg-v2' ),
              ];
              $my_types  = function_exists( 'vpg_types_for_rank' ) ? vpg_types_for_rank() : array_keys( $type_cards );
              $cur_type  = $edit_post ? $edit_post->post_type : 'vpg_location';
              ?>
              <div class="g-typegrid" role="radiogroup" aria-label="<?php esc_attr_e( 'Submission type', 'vpg-v2' ); ?>">
                <?php foreach ( $type_cards as $tt => $tc ) :
                    $open = in_array( $tt, $my_types, true );
                    $sel  = $cur_type === $tt; ?>
                  <label class="g-type<?php echo $open ? '' : ' is-locked'; echo $sel ? ' is-active' : ''; ?>">
                    <input type="radio" name="submit_type_pick" value="<?php echo esc_attr( $tt ); ?>" <?php checked( $sel ); disabled( ! $open || (bool) $edit_post ); ?>>
                    <span class="g-type__ico" aria-hidden="true"><?php echo esc_html( $open ? $tc[0] : '🔒' ); ?></span>
                    <span class="g-type__name"><?php echo esc_html( $tc[1] ); ?></span>
                    <span class="g-type__desc"><?php echo $open
                        ? esc_html( $tc[2] )
                        : '<b>' . esc_html( $unlock_hint[ $tt ] ?? '' ) . '</b>'; ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <input type="hidden" id="submit_type" name="submit_type" value="<?php echo esc_attr( $cur_type ); ?>">
            </div>

            <div class="g-field">
              <label for="title"><?php esc_html_e( 'Title', 'vpg-v2' ); ?></label>
              <input class="g-input" id="title" type="text" name="title" required value="<?php echo esc_attr( $ev( 'post_title' ) ); ?>" placeholder="<?php esc_attr_e( 'The Stephansdom rooftop · golden hour', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="lede"><?php esc_html_e( 'One-line description', 'vpg-v2' ); ?></label>
              <input class="g-input" id="lede" type="text" name="lede" value="<?php echo esc_attr( $ev( 'post_excerpt' ) ); ?>" placeholder="<?php esc_attr_e( 'South-facing terrace · 35m elevation · free access via café', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="body"><?php esc_html_e( 'Body / notes', 'vpg-v2' ); ?></label>
              <textarea class="g-textarea" id="body" name="body" rows="8" required placeholder="<?php esc_attr_e( 'Light direction, best time of day, what to bring, access notes, anything you wish you’d known before going.', 'vpg-v2' ); ?>"><?php echo esc_textarea( $ev( 'post_content' ) ); ?></textarea>
            </div>

            <div class="g-field" data-for-types="vpg_location vpg_studio vpg_shop">
              <label for="district"><?php esc_html_e( 'District / area', 'vpg-v2' ); ?></label>
              <input class="g-input" id="district" type="text" name="district" value="<?php echo esc_attr( $ev( 'district' ) ); ?>" placeholder="<?php esc_attr_e( '1010 · Innere Stadt', 'vpg-v2' ); ?>">
            </div>

            <!-- Pin picker · the exact spot, set by the person who found it -->
            <?php
            $pin_keys = [
                'vpg_location' => [ 'location_lat', 'location_lng' ],
                'vpg_studio'   => [ 'studio_lat', 'studio_lng' ],
                'vpg_shop'     => [ 'shop_lat', 'shop_lng' ],
                'vpg_event'    => [ '_vpg_event_lat', '_vpg_event_lng' ],
            ];
            $pin_lat = $pin_lng = '';
            if ( $edit_post && isset( $pin_keys[ $edit_post->post_type ] ) ) {
                $pin_lat = get_post_meta( $edit_post->ID, $pin_keys[ $edit_post->post_type ][0], true );
                $pin_lng = get_post_meta( $edit_post->ID, $pin_keys[ $edit_post->post_type ][1], true );
            }
            ?>
            <div class="g-field" data-for-types="vpg_location vpg_studio vpg_shop vpg_event" hidden>
              <label for="pin-search"><?php esc_html_e( 'Where exactly? · search or click the map', 'vpg-v2' ); ?></label>
              <div style="display:flex;gap:10px">
                <input class="g-input" id="pin-search" type="text" placeholder="<?php esc_attr_e( 'Karlsplatz 4, Wien', 'vpg-v2' ); ?>" autocomplete="off" style="flex:1">
                <button class="g-btn" type="button" id="pin-go"><?php esc_html_e( 'Find', 'vpg-v2' ); ?></button>
                <button class="g-btn g-btn--ghost" type="button" id="pin-locate" title="<?php esc_attr_e( 'Use my current position', 'vpg-v2' ); ?>">◎</button>
              </div>
              <div id="vpg-pin-map" style="height:300px;border:1px solid var(--g-line-2);margin-top:10px"></div>
              <input type="hidden" name="pin_lat" id="pin-lat" value="<?php echo esc_attr( $pin_lat ); ?>">
              <input type="hidden" name="pin_lng" id="pin-lng" value="<?php echo esc_attr( $pin_lng ); ?>">
              <p class="g-form__note" style="margin-top:6px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <span id="pin-state"><?php echo $pin_lat
                    ? esc_html( sprintf( __( 'Pin set · %s, %s', 'vpg-v2' ), $pin_lat, $pin_lng ) )
                    : esc_html__( 'No pin yet — photo GPS fills it automatically, or leave it to editorial.', 'vpg-v2' ); ?></span>
                <button type="button" id="pin-clear" style="background:none;border:0;padding:0;cursor:pointer;font:inherit;color:var(--g-red);font-weight:700" <?php echo $pin_lat ? '' : 'hidden'; ?>><?php esc_html_e( 'Remove pin', 'vpg-v2' ); ?></button>
              </p>
            </div>

            <script>
            (function () {
              var mapEl = document.getElementById('vpg-pin-map');
              if (!mapEl) return;
              var latIn = document.getElementById('pin-lat'), lngIn = document.getElementById('pin-lng');
              var state = document.getElementById('pin-state'), clearBtn = document.getElementById('pin-clear');
              var searchIn = document.getElementById('pin-search'), searchBtn = document.getElementById('pin-go');
              var distIn = document.getElementById('district');
              var map = null, marker = null;

              function showState() {
                if (latIn.value) {
                  state.textContent = <?php echo wp_json_encode( __( 'Pin set · ', 'vpg-v2' ) ); ?> + latIn.value + ', ' + lngIn.value;
                  clearBtn.hidden = false;
                } else {
                  state.textContent = <?php echo wp_json_encode( __( 'No pin yet — photo GPS fills it automatically, or leave it to editorial.', 'vpg-v2' ) ); ?>;
                  clearBtn.hidden = true;
                }
              }
              function setPin(lat, lng) {
                latIn.value = lat.toFixed(6); lngIn.value = lng.toFixed(6);
                if (marker) marker.setLatLng([lat, lng]);
                else marker = L.marker([lat, lng], { draggable: true }).addTo(map).on('dragend', function (e) {
                  var p = e.target.getLatLng();
                  latIn.value = p.lat.toFixed(6); lngIn.value = p.lng.toFixed(6);
                  showState();
                });
                showState();
              }
              function initMap() {
                if (map || typeof L === 'undefined') return;
                var lat = parseFloat(latIn.value) || 48.2082, lng = parseFloat(lngIn.value) || 16.3738;
                map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], latIn.value ? 16 : 12);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                if (latIn.value && lngIn.value) setPin(lat, lng);
                map.on('click', function (e) { setPin(e.latlng.lat, e.latlng.lng); });
              }

              // Init when the field group is (or becomes) visible — Leaflet
              // can't measure a hidden container.
              function maybeInit() {
                if (!mapEl.closest('[data-for-types]').hidden) {
                  initMap();
                  if (map) setTimeout(function () { map.invalidateSize(); }, 60);
                }
              }
              document.querySelectorAll('input[name="submit_type_pick"]').forEach(function (r) {
                r.addEventListener('change', function () { setTimeout(maybeInit, 0); });
              });
              if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', maybeInit);
              else setTimeout(maybeInit, 0);
              window.addEventListener('load', maybeInit);

              clearBtn.addEventListener('click', function () {
                latIn.value = ''; lngIn.value = '';
                if (marker && map) { map.removeLayer(marker); marker = null; }
                showState();
              });

              function search() {
                var q = searchIn.value.trim();
                if (!q || !map) return;
                searchBtn.disabled = true;
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q + ', Vienna'), { headers: { 'Accept': 'application/json' } })
                  .then(function (r) { return r.json(); })
                  .then(function (res) {
                    if (res && res[0]) {
                      var lat = parseFloat(res[0].lat), lng = parseFloat(res[0].lon);
                      map.setView([lat, lng], 17);
                      setPin(lat, lng);
                      if (distIn && !distIn.value && res[0].display_name) {
                        var d = res[0].display_name.split(',').map(function (s) { return s.trim(); })
                          .find(function (p) { return /^\d{4}/.test(p) || /Bezirk/.test(p); });
                        if (d) distIn.value = d;
                      }
                    } else {
                      state.textContent = <?php echo wp_json_encode( __( 'Address not found — click the map to drop the pin.', 'vpg-v2' ) ); ?>;
                    }
                  })
                  .catch(function () { state.textContent = <?php echo wp_json_encode( __( 'Search failed — click the map to drop the pin.', 'vpg-v2' ) ); ?>; })
                  .finally(function () { searchBtn.disabled = false; });
              }
              searchBtn.addEventListener('click', search);
              searchIn.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); search(); } });

              // 0603 · one tap puts the pin where the phone is
              document.getElementById('pin-locate').addEventListener('click', function () {
                if (!navigator.geolocation || !map) return;
                navigator.geolocation.getCurrentPosition(function (pos) {
                  map.setView([pos.coords.latitude, pos.coords.longitude], 17);
                  setPin(pos.coords.latitude, pos.coords.longitude);
                }, function () {
                  state.textContent = <?php echo wp_json_encode( __( 'Position unavailable — search or click the map instead.', 'vpg-v2' ) ); ?>;
                });
              });
            })();
            </script>

            <!-- Event proposals · date + meeting point -->
            <div class="g-field g-field--row" data-for-types="vpg_event" hidden>
              <div>
                <label for="event_date"><?php esc_html_e( 'When?', 'vpg-v2' ); ?></label>
                <input class="g-input" id="event_date" type="date" name="event_date" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_vpg_event_date', true ) : '' ); ?>">
              </div>
              <div>
                <label for="event_venue"><?php esc_html_e( 'Meeting point', 'vpg-v2' ); ?></label>
                <input class="g-input" id="event_venue" type="text" name="event_venue" value="<?php echo esc_attr( $edit_post ? get_post_meta( $edit_post->ID, '_vpg_event_venue', true ) : '' ); ?>" placeholder="<?php esc_attr_e( 'Karlsplatz · by the fountain', 'vpg-v2' ); ?>">
              </div>
            </div>

            <!-- Event proposals · what to bring -->
            <div class="g-field" data-for-types="vpg_event" hidden>
              <label for="event_checklist"><?php esc_html_e( 'Bring along · one item per line', 'vpg-v2' ); ?></label>
              <textarea class="g-textarea" id="event_checklist" name="event_checklist" rows="3" placeholder="<?php esc_attr_e( "Tripod\nND filter\nGood shoes", 'vpg-v2' ); ?>"><?php echo esc_textarea( $edit_post ? get_post_meta( $edit_post->ID, '_vpg_event_checklist', true ) : '' ); ?></textarea>
            </div>

            <!-- Trail proposals · how demanding is the walk -->
            <div class="g-field" data-for-types="vpg_trail" hidden>
              <label for="trail_difficulty"><?php esc_html_e( 'Difficulty', 'vpg-v2' ); ?></label>
              <select class="g-select" id="trail_difficulty" name="trail_difficulty">
                <?php foreach ( [ 'easy' => __( 'Easy · flat, any shoes', 'vpg-v2' ), 'moderate' => __( 'Moderate · some climbs or length', 'vpg-v2' ), 'sporty' => __( 'Sporty · bring stamina', 'vpg-v2' ) ] as $dv => $dl ) : ?>
                  <option value="<?php echo esc_attr( $dv ); ?>" <?php selected( $edit_post && get_post_meta( $edit_post->ID, '_vpg_trail_difficulty', true ) === $dv ); ?>><?php echo esc_html( $dl ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Trail proposals · pick the stops from the published map -->
            <div class="g-field" data-for-types="vpg_trail" hidden>
              <label><?php esc_html_e( 'Stops · pick from the map (tick in walking order)', 'vpg-v2' ); ?></label>
              <?php
              $trail_locs   = get_posts( [ 'post_type' => 'vpg_location', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ] );
              $trail_chosen = $edit_post ? array_map( 'intval', array_filter( explode( ',', (string) get_post_meta( $edit_post->ID, '_vpg_trail_stops', true ) ) ) ) : [];
              ?>
              <div style="max-height:260px;overflow-y:auto;border:1px solid var(--g-line);padding:12px 16px;display:grid;gap:6px">
                <?php if ( ! $trail_locs ) : ?>
                  <p class="g-form__note" style="margin:0"><?php esc_html_e( 'No published locations yet — the map fills up first.', 'vpg-v2' ); ?></p>
                <?php else : foreach ( $trail_locs as $tl ) : ?>
                  <label style="display:flex;gap:10px;align-items:baseline;font-size:14px;font-weight:500">
                    <input type="checkbox" name="trail_stops[]" value="<?php echo (int) $tl->ID; ?>" <?php checked( in_array( $tl->ID, $trail_chosen, true ) ); ?>>
                    <span><?php echo esc_html( $tl->post_title ); ?><span style="color:var(--g-mid)"><?php
                        $tl_d = get_post_meta( $tl->ID, 'location_district', true );
                        echo $tl_d ? ' · ' . esc_html( $tl_d ) : '';
                    ?></span></span>
                  </label>
                <?php endforeach; endif; ?>
              </div>
              <p class="g-form__note" style="margin-top:6px"><?php esc_html_e( 'Up to 12 stops · editorial fine-tunes the order before publishing.', 'vpg-v2' ); ?></p>
            </div>

            <script>
            (function () {
              var hidden = document.getElementById('submit_type');
              var radios = document.querySelectorAll('input[name="submit_type_pick"]');
              if (!hidden || !radios.length) return;
              function syncFields() {
                var t = hidden.value;
                document.querySelectorAll('[data-for-types]').forEach(function (el) {
                  el.hidden = el.getAttribute('data-for-types').split(' ').indexOf(t) === -1;
                });
              }
              radios.forEach(function (r) {
                r.addEventListener('change', function () {
                  if (!r.checked) return;
                  hidden.value = r.value;
                  document.querySelectorAll('.g-type').forEach(function (c) { c.classList.remove('is-active'); });
                  r.closest('.g-type').classList.add('is-active');
                  syncFields();
                });
              });
              syncFields();
            })();
            </script>

            <div class="g-field">
              <label for="photos"><?php esc_html_e( 'Photos · up to 4', 'vpg-v2' ); ?></label>
              <div class="g-drop">
                <span class="g-drop__hint" aria-hidden="true">⇣ <?php esc_html_e( 'Drop photos here or browse', 'vpg-v2' ); ?></span>
                <input id="photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif">
              </div>
              <div id="photo-preview" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px"></div>
              <p class="g-form__note" style="margin-top:6px"><?php esc_html_e( 'JPG, PNG, WebP or AVIF · max 8 MB each · the first photo becomes the cover. Your photos stay yours, credited by name.', 'vpg-v2' ); ?></p>
              <script>
              (function () {
                  var input = document.getElementById('photos');
                  var prev  = document.getElementById('photo-preview');
                  if (!input || !prev || typeof DataTransfer === 'undefined') return;
                  function render() {
                      prev.innerHTML = '';
                      Array.prototype.forEach.call(input.files, function (file, idx) {
                          if (!file.type || file.type.indexOf('image/') !== 0) return;
                          var cell = document.createElement('div');
                          cell.style.cssText = 'position:relative;width:84px;height:84px';
                          var img = document.createElement('img');
                          img.alt = file.name;
                          img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block';
                          img.src = URL.createObjectURL(file);
                          img.onload = function () { URL.revokeObjectURL(img.src); };
                          var rm = document.createElement('button');
                          rm.type = 'button';
                          rm.textContent = '×';
                          rm.setAttribute('aria-label', 'Remove ' + file.name);
                          rm.style.cssText = 'position:absolute;top:-8px;right:-8px;width:22px;height:22px;border:0;background:#0B0B0B;color:#fff;cursor:pointer;line-height:1';
                          rm.addEventListener('click', function () {
                              var dt = new DataTransfer();
                              Array.prototype.forEach.call(input.files, function (f, i) { if (i !== idx) dt.items.add(f); });
                              input.files = dt.files;
                              render();
                          });
                          cell.appendChild(img); cell.appendChild(rm); prev.appendChild(cell);
                      });
                  }
                  input.addEventListener('change', render);
              }());
              </script>
            </div>

            <div style="display:flex;gap:12px;flex-wrap:wrap">
              <button class="g-btn g-btn--lg g-btn--red" type="submit" name="save_action" value="submit"><?php esc_html_e( 'Submit for review', 'vpg-v2' ); ?> <span class="a">→</span></button>
              <button class="g-btn g-btn--lg g-btn--ghost" type="submit" name="save_action" value="draft" formnovalidate><?php esc_html_e( 'Save as draft', 'vpg-v2' ); ?></button>
            </div>
            <div id="vpg-dupe-hint" hidden style="margin-top:14px;border:1px solid var(--g-red);padding:12px 16px;font-size:13px"></div>
            <script>
            (function () {
                var title = document.getElementById('title');
                var type  = document.getElementById('submit_type');
                var hint  = document.getElementById('vpg-dupe-hint');
                if (!title || !hint) return;
                var t;
                function check() {
                    var v = title.value.trim();
                    if (v.length < 4) { hint.hidden = true; return; }
                    var url = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?> +
                        '?action=vpg_dupe_check&_ajax_nonce=<?php echo esc_js( wp_create_nonce( 'vpg_dupe_check' ) ); ?>' +
                        '&title=' + encodeURIComponent(v) + '&type=' + encodeURIComponent(type ? type.value : '');
                    fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
                        if (!res || !res.success || !res.data.length) { hint.hidden = true; return; }
                        hint.innerHTML = '<strong><?php echo esc_js( __( 'Heads up — similar entries exist:', 'vpg-v2' ) ); ?></strong> ' +
                            res.data.map(function (h) {
                                var a = document.createElement('a');
                                a.href = h.url; a.textContent = h.title; a.target = '_blank'; a.rel = 'noopener';
                                return a.outerHTML;
                            }).join(' · ') +
                            ' — <?php echo esc_js( __( 'submitting anyway is fine if yours adds something new.', 'vpg-v2' ) ); ?>';
                        hint.hidden = false;
                    }).catch(function () {});
                }
                title.addEventListener('blur', check);
                title.addEventListener('input', function () { clearTimeout(t); t = setTimeout(check, 900); });
            }());
            </script>
            <p class="g-form__note">
              <?php esc_html_e( 'Submissions are queued for editorial review. You’ll receive an email when your entry is approved and published. For map entries, drop the pin right in the form — or let your photo’s GPS set it.', 'vpg-v2' ); ?>
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
