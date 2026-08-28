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
  <?php
  // Edit mode · a member may rework their own submission while it is pending.
  $edit_post = null;
  if ( ! empty( $_GET['edit'] ) ) {
      $maybe = get_post( (int) $_GET['edit'] );
      if ( $maybe
          && (int) $maybe->post_author === get_current_user_id()
          && in_array( $maybe->post_status, [ 'pending', 'draft' ], true )
          && in_array( $maybe->post_type, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ], true ) ) {
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
              <label for="submit_type"><?php esc_html_e( 'What are you submitting?', 'vpg-v2' ); ?></label>
              <select class="g-select" id="submit_type" name="submit_type" required <?php disabled( (bool) $edit_post ); ?>>
                <option value="vpg_location" <?php selected( $edit_post && $edit_post->post_type === 'vpg_location' ); ?>><?php esc_html_e( 'Location · a shooting spot', 'vpg-v2' ); ?></option>
                <option value="vpg_studio" <?php selected( $edit_post && $edit_post->post_type === 'vpg_studio' ); ?>><?php esc_html_e( 'Studio · a rental space', 'vpg-v2' ); ?></option>
                <option value="vpg_shop" <?php selected( $edit_post && $edit_post->post_type === 'vpg_shop' ); ?>><?php esc_html_e( 'Shop · a camera shop / lab / supplier', 'vpg-v2' ); ?></option>
                <option value="vpg_review" <?php selected( $edit_post && $edit_post->post_type === 'vpg_review' ); ?>><?php esc_html_e( 'Gear review · with /10 scores', 'vpg-v2' ); ?></option>
                <option value="vpg_tutorial" <?php selected( $edit_post && $edit_post->post_type === 'vpg_tutorial' ); ?>><?php esc_html_e( 'Tutorial pitch · we’ll discuss', 'vpg-v2' ); ?></option>
              </select>
              <?php if ( $edit_post ) : ?><input type="hidden" name="submit_type" value="<?php echo esc_attr( $edit_post->post_type ); ?>"><?php endif; ?>
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

            <div class="g-field">
              <label for="district"><?php esc_html_e( 'District / area', 'vpg-v2' ); ?></label>
              <input class="g-input" id="district" type="text" name="district" value="<?php echo esc_attr( $ev( 'district' ) ); ?>" placeholder="<?php esc_attr_e( '1010 · Innere Stadt', 'vpg-v2' ); ?>">
            </div>

            <div class="g-field">
              <label for="photos"><?php esc_html_e( 'Photos · up to 4', 'vpg-v2' ); ?></label>
              <input class="g-input" id="photos" type="file" name="photos[]" multiple accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif">
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
