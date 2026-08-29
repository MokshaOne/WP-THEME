<?php
/** VPG v2 · single · Event (Gallery) */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="vpg-main">
<?php while ( have_posts() ) : the_post();
    $meta = '_vpg_event_date' ? vpg_field( '_vpg_event_date' ) : '';
?>

    <section class="g-phero">
      <div class="g-wrap">
        <div class="g-phero__grid">
          <div>
            <p class="g-kicker" style="margin-bottom:16px">Event</p>
            <h1 class="g-display g-phero__title" style="max-width:20ch"><?php the_title(); ?></h1>
            <?php if ( get_the_excerpt() ) : ?>
              <p class="g-lede g-phero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>
            <div class="g-byline" style="margin-top:20px">
              <span><?php echo esc_html( get_the_author() ); ?></span><span>·</span>
              <span><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
            </div>
          </div>
          <?php if ( $meta ) : ?>
            <dl class="g-phero__aside">
              <dt>Date</dt><dd><?php echo esc_html( $meta ); ?></dd>
              <dt><?php esc_html_e( 'Calendar', 'vpg-v2' ); ?></dt>
              <dd><a href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_event_ics&event=' . get_the_ID() ) ); ?>"><?php esc_html_e( 'Download .ics ↓', 'vpg-v2' ); ?></a></dd>
            </dl>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php
    // Meeting point · pinned by the proposer in the submit form
    $ev_lat = get_post_meta( get_the_ID(), '_vpg_event_lat', true );
    $ev_lng = get_post_meta( get_the_ID(), '_vpg_event_lng', true );
    if ( $ev_lat && $ev_lng ) : ?>
    <section class="g-wrap" style="margin:clamp(24px,4vw,40px) auto">
      <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Meeting point', 'vpg-v2' ); ?><?php
        $ev_venue = get_post_meta( get_the_ID(), '_vpg_event_venue', true );
        if ( $ev_venue ) echo ' · ' . esc_html( $ev_venue );
      ?></p>
      <div id="vpg-event-map" style="height:280px;border:1px solid var(--g-line, #E6E5E1)"></div>
      <p class="g-meta" style="margin-top:8px"><a href="https://www.openstreetmap.org/?mlat=<?php echo esc_attr( $ev_lat ); ?>&mlon=<?php echo esc_attr( $ev_lng ); ?>#map=17/<?php echo esc_attr( $ev_lat ); ?>/<?php echo esc_attr( $ev_lng ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open in OSM for directions ↗', 'vpg-v2' ); ?></a>
        · <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr( $ev_lat . ',' . $ev_lng ); ?>&travelmode=transit" target="_blank" rel="noopener"><?php esc_html_e( 'Public transport route ↗', 'vpg-v2' ); ?></a></p>
      <script>
      document.addEventListener('DOMContentLoaded', function () {
        if (typeof L === 'undefined') return;
        var lat = <?php echo (float) $ev_lat; ?>, lng = <?php echo (float) $ev_lng; ?>;
        var m = L.map('vpg-event-map', { scrollWheelZoom: false }).setView([lat, lng], 16);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(m);
        L.marker([lat, lng]).addTo(m);
      });
      </script>
    </section>
    <?php endif; ?>

    <?php if ( has_post_thumbnail() ) : ?>
      <figure class="g-wrap" style="margin:clamp(24px,4vw,48px) auto">
        <div class="g-fig g-fig--3x2"><?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
      </figure>
    <?php endif; ?>

    <section class="g-section" style="padding-top:clamp(32px,4vw,56px)">
      <div class="g-wrap">
        <div class="g-prose" style="margin:0 auto"><?php the_content(); ?></div>
      </div>
    </section>

    <?php // 0125 · what to bring, from the host
    $checklist = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( get_the_ID(), '_vpg_event_checklist', true ) ) ) );
    if ( $checklist ) : ?>
    <section class="g-wrap" style="margin:0 auto clamp(24px,4vw,40px)">
      <p class="g-kicker" style="margin-bottom:12px">● <?php esc_html_e( 'Bring along', 'vpg-v2' ); ?></p>
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:6px;max-width:60ch">
        <?php foreach ( $checklist as $ci ) : ?>
          <li style="padding:8px 0;border-top:1px solid var(--g-line,#E6E5E1);font-weight:600">□ <?php echo esc_html( $ci ); ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php // 0155 · live weather box on the day itself
    if ( function_exists( 'vpg_event_is_today' ) && vpg_event_is_today( get_the_ID() ) && function_exists( 'vpg_weather' ) ) :
        $wlat = (float) ( get_post_meta( get_the_ID(), '_vpg_event_lat', true ) ?: 48.2082 );
        $wlng = (float) ( get_post_meta( get_the_ID(), '_vpg_event_lng', true ) ?: 16.3738 );
        $wx   = vpg_weather( $wlat, $wlng );
        if ( $wx ) : ?>
    <section class="g-wrap" style="margin:0 auto clamp(24px,4vw,40px)">
      <p style="border:1px solid var(--g-ink,#0B0B0B);padding:12px 18px;font-weight:700;font-size:14px;display:inline-block">☂ <?php printf( esc_html__( 'Right now at the meeting point: %1$s · %2$s', 'vpg-v2' ), esc_html( $wx['temp'] ), esc_html( $wx['label'] ) ); ?></p>
    </section>
    <?php endif; endif; ?>

    <!-- RSVP · members say they're coming -->
    <section class="g-section g-section--alt" id="rsvp">
      <div class="g-wrap">
        <?php
        $rsvps    = function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( get_the_ID() ) : [];
        $is_going = is_user_logged_in() && in_array( get_current_user_id(), $rsvps, true );
        $ev_cap   = (int) get_post_meta( get_the_ID(), '_vpg_event_cap', true );
        $ev_wait  = array_values( array_filter( array_map( 'intval', (array) get_post_meta( get_the_ID(), '_vpg_waitlist', true ) ) ) );
        $on_wait  = is_user_logged_in() && in_array( get_current_user_id(), $ev_wait, true );
        $is_full  = $ev_cap > 0 && count( $rsvps ) >= $ev_cap;
        ?>
        <div class="g-head">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'Who\'s coming', 'vpg-v2' ); ?></span>
            <h2 class="g-head__t"><?php echo (int) count( $rsvps ); ?><?php if ( $ev_cap ) echo '<span style="color:var(--g-faint);font-size:.6em"> / ' . (int) $ev_cap . '</span>'; ?> <em><?php echo esc_html( _n( 'member', 'members', count( $rsvps ), 'vpg-v2' ) ); ?></em></h2>
            <?php if ( $ev_wait ) : ?><p class="g-meta" style="margin-top:6px"><?php printf( esc_html( _n( '%d on the waitlist', '%d on the waitlist', count( $ev_wait ), 'vpg-v2' ) ), count( $ev_wait ) ); ?></p><?php endif; ?>
          </div>
          <?php if ( is_user_logged_in() ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
            <?php wp_nonce_field( 'vpg_rsvp' ); ?>
            <input type="hidden" name="action" value="vpg_rsvp">
            <input type="hidden" name="event" value="<?php echo (int) get_the_ID(); ?>">
            <button class="g-btn <?php echo ( $is_going || $on_wait ) ? 'g-btn--ghost' : 'g-btn--red'; ?>" type="submit">
              <?php
              if ( $is_going )     esc_html_e( '✓ You\'re coming · cancel', 'vpg-v2' );
              elseif ( $on_wait )  esc_html_e( '⏳ On the waitlist · leave', 'vpg-v2' );
              elseif ( $is_full )  esc_html_e( 'Full — join the waitlist', 'vpg-v2' );
              else                 esc_html_e( 'I\'m coming', 'vpg-v2' );
              ?>
            </button>
          </form>
          <?php else : ?>
            <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free to RSVP', 'vpg-v2' ); ?> →</a>
          <?php endif; ?>
        </div>
        <?php if ( $rsvps && is_user_logged_in() ) : ?>
        <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center">
          <?php foreach ( array_slice( $rsvps, 0, 24 ) as $rid ) : $ru = get_userdata( $rid ); if ( ! $ru ) continue; ?>
            <a href="<?php echo esc_url( home_url( '/members/' . $ru->user_nicename . '/' ) ); ?>" title="<?php echo esc_attr( $ru->display_name ); ?>" style="display:flex;align-items:center;gap:8px;text-decoration:none">
              <?php echo get_avatar( $rid, 36, '', esc_attr( $ru->display_name ), [ 'style' => 'display:block;width:36px;height:36px;object-fit:cover' ] ); ?>
              <span style="font-size:12px;font-weight:600"><?php echo esc_html( $ru->display_name ); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
        <?php elseif ( $rsvps ) : ?>
          <p class="g-meta"><?php esc_html_e( 'Members see who — log in.', 'vpg-v2' ); ?></p>
        <?php endif; ?>
        <?php if ( $is_going ) : ?>
          <p class="g-form__note" style="margin-top:16px"><?php esc_html_e( 'We\'ll email you a reminder the day before.', 'vpg-v2' ); ?></p>
        <?php endif; ?>

        <?php
        // Live check-in · only on the event day
        if ( function_exists( 'vpg_event_is_today' ) && vpg_event_is_today( get_the_ID() ) ) :
            $checked = function_exists( 'vpg_event_checkins' ) ? vpg_event_checkins( get_the_ID() ) : [];
            $is_here = is_user_logged_in() && in_array( get_current_user_id(), $checked, true );
        ?>
        <div style="margin-top:28px;border:2px solid var(--g-red);padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
          <div>
            <span class="g-kicker"><?php esc_html_e( 'Today · live', 'vpg-v2' ); ?></span>
            <p style="margin:6px 0 0;font-weight:800;font-size:20px"><span id="vpg-checkin-n"><?php echo (int) count( $checked ); ?></span> <?php esc_html_e( 'checked in', 'vpg-v2' ); ?></p>
          </div>
          <?php if ( $is_going && ! $is_here ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
            <?php wp_nonce_field( 'vpg_checkin' ); ?>
            <input type="hidden" name="action" value="vpg_checkin">
            <input type="hidden" name="event" value="<?php echo (int) get_the_ID(); ?>">
            <button class="g-btn g-btn--red" type="submit"><?php esc_html_e( 'I\'m here', 'vpg-v2' ); ?></button>
          </form>
          <?php elseif ( $is_here ) : ?>
            <span style="font-weight:800;color:var(--g-red)">✓ <?php esc_html_e( 'You\'re checked in', 'vpg-v2' ); ?></span>
          <?php endif; ?>
        </div>
        <script>
        (function () {
            var n = document.getElementById('vpg-checkin-n');
            if (!n) return;
            setInterval(function () {
                fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>?action=vpg_checkin_count&event=<?php echo (int) get_the_ID(); ?>', { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (res) { if (res && res.success) n.textContent = res.data.count; })
                    .catch(function () {});
            }, 10000);
        }());
        </script>
        <?php endif; ?>
      </div>
    </section>

    <section class="g-section g-section--dark" style="text-align:center">
      <div class="g-wrap">
        <a class="g-btn g-btn--red g-btn--lg" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_event' ) ); ?>"><?php esc_html_e( 'All events', 'vpg-v2' ); ?> <span class="a">&rarr;</span></a>
      </div>
    </section>

<?php endwhile; ?>
</main>

<?php get_footer();
