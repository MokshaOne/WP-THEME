<?php
/**
 * VPG v3 — Cluster 04 · Events & Photowalks.
 *
 * Builds on the existing RSVP/waitlist/check-in/reminder/gallery core
 * (inc/community.php, inc/advanced.php, inc/projects.php) and makes every
 * photowalk kinder to run and to join: co-hosts, weather decision, backup
 * date, meeting-point photo, host live location, group chat, icebreakers,
 * buddy pairing, photo task, recap, consent, guest tickets, workshop &
 * analog formats, language tags, photo swap, host feedback, cancellation,
 * theme months, host handbook, external-event layer, museum partners,
 * memory collage, club year review and one-click templates.
 *
 *   0124 co-hosts · 0126 weather decision · 0127 backup date
 *   0128 meeting photo · 0129 host live loc · 0130 latecomer · 0131 chat
 *   0132 icebreakers · 0133 buddies · 0134 photo task · 0135 recap
 *   0138 attendance history · 0139 no-show gentleness · 0140 no-photo
 *   0141 consent · 0142 guest ticket · 0144 theme months · 0145 workshop
 *   0146 analog · 0147 language · 0148 photo swap · 0149 host feedback
 *   0150 handbook · 0151 host rotation · 0152 external · 0153 museums
 *   0154 memory collage · 0157 route heatmap · 0158 year review
 *   0159 templates · 0160 cancellation
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Seed the event_kind taxonomy (empty until now) with the formats we use. */
add_action( 'init', function () {
    if ( ! taxonomy_exists( 'event_kind' ) ) return;
    foreach ( [ 'walk' => 'Photowalk', 'workshop' => 'Workshop', 'analog' => 'Analog walk', 'external' => 'External', 'exhibition' => 'Exhibition' ] as $slug => $name ) {
        if ( ! term_exists( $slug, 'event_kind' ) ) wp_insert_term( $name, 'event_kind', [ 'slug' => $slug ] );
    }
}, 20 );

/* ════════════════════════════════════════════════════════════════ */
/*  Metabox · host desk (everything the acf schema doesn't cover)     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-event-host', '🎪 ' . __( 'Host desk', 'vpg-v2' ), 'vpg_render_event_host_box', 'vpg_event', 'normal', 'default' );
} );

function vpg_render_event_host_box( $post ) {
    wp_nonce_field( 'vpg_event_host', 'vpg_event_host_nonce' );
    $co    = array_filter( (array) get_post_meta( $post->ID, '_vpg_event_cohosts', true ) );
    $backup= (string) get_post_meta( $post->ID, '_vpg_event_backup', true );
    $task  = (string) get_post_meta( $post->ID, '_vpg_event_phototask', true );
    $lang  = (string) get_post_meta( $post->ID, '_vpg_event_lang', true );
    $mpimg = (int) get_post_meta( $post->ID, '_vpg_event_mpphoto', true );
    $mat   = (string) get_post_meta( $post->ID, '_vpg_event_material', true );
    $dev   = (string) get_post_meta( $post->ID, '_vpg_event_devmeet', true );
    $ext   = (string) get_post_meta( $post->ID, '_vpg_event_external', true );
    $members = get_users( [ 'number' => 300, 'orderby' => 'display_name', 'fields' => [ 'ID', 'display_name' ] ] );
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 20px">
      <div><label style="font-weight:600">👥 <?php esc_html_e( '0124 · Co-hosts', 'vpg-v2' ); ?></label><br>
        <select name="vpg_event_cohosts[]" multiple size="4" style="width:100%">
          <?php foreach ( $members as $m ) : ?><option value="<?php echo (int) $m->ID; ?>"<?php echo in_array( $m->ID, $co, true ) ? ' selected' : ''; ?>><?php echo esc_html( $m->display_name ); ?></option><?php endforeach; ?>
        </select><span class="description"><?php esc_html_e( 'Both leads appear on the event.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">🌦 <?php esc_html_e( '0127 · Backup date (Plan B)', 'vpg-v2' ); ?></label><br>
        <input type="date" name="vpg_event_backup" value="<?php echo esc_attr( $backup ); ?>"><span class="description"><?php esc_html_e( 'Used if the weather forces a shift.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">🎯 <?php esc_html_e( '0134 · Photo task for the group', 'vpg-v2' ); ?></label><br>
        <input type="text" name="vpg_event_phototask" value="<?php echo esc_attr( $task ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'One theme, e.g. “reflections”', 'vpg-v2' ); ?>"></div>
      <div><label style="font-weight:600">🗣 <?php esc_html_e( '0147 · Language', 'vpg-v2' ); ?></label><br>
        <select name="vpg_event_lang"><?php foreach ( [ '' => __( '— any —', 'vpg-v2' ), 'de' => 'Deutsch', 'en' => 'English', 'both' => 'DE / EN' ] as $lv => $ll ) echo '<option value="' . esc_attr( $lv ) . '"' . selected( $lang, $lv, false ) . '>' . esc_html( $ll ) . '</option>'; ?></select></div>
      <div><label style="font-weight:600">📎 <?php esc_html_e( '0145 · Workshop material (URL)', 'vpg-v2' ); ?></label><br>
        <input type="url" name="vpg_event_material" value="<?php echo esc_attr( $mat ); ?>" style="width:100%" placeholder="https://…"><span class="description"><?php esc_html_e( 'Handout / slides for a workshop event.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">🎞 <?php esc_html_e( '0146 · Analog dev after-meeting', 'vpg-v2' ); ?></label><br>
        <input type="text" name="vpg_event_devmeet" value="<?php echo esc_attr( $dev ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'e.g. two weeks later, lab XY', 'vpg-v2' ); ?>"></div>
      <div><label style="font-weight:600">🔗 <?php esc_html_e( '0152 · External event link', 'vpg-v2' ); ?></label><br>
        <input type="url" name="vpg_event_external" value="<?php echo esc_attr( $ext ); ?>" style="width:100%" placeholder="https://…"><span class="description"><?php esc_html_e( 'Marks this an external listing (exhibition / fair).', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">📸 <?php esc_html_e( '0128 · Meeting-point photo', 'vpg-v2' ); ?></label><br>
        <input type="hidden" id="vpg-mp-id" name="vpg_event_mpphoto" value="<?php echo $mpimg; ?>">
        <span id="vpg-mp-prev"><?php if ( $mpimg ) echo '<img src="' . esc_url( wp_get_attachment_image_url( $mpimg, 'thumbnail' ) ) . '" style="max-width:120px;display:block;margin:4px 0">'; ?></span>
        <button type="button" class="button button-small" id="vpg-mp-pick"><?php esc_html_e( 'Choose photo', 'vpg-v2' ); ?></button></div>
    </div>
    <script>
    (function(){var b=document.getElementById('vpg-mp-pick'),f=document.getElementById('vpg-mp-id'),p=document.getElementById('vpg-mp-prev');
      if(b&&window.wp&&wp.media)b.addEventListener('click',function(){var fr=wp.media({library:{type:'image'},multiple:false});
        fr.on('select',function(){var a=fr.state().get('selection').first().toJSON();f.value=a.id;var u=(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url);p.innerHTML='<img src="'+u+'" style="max-width:120px;display:block;margin:4px 0">';});fr.open();});
    })();
    </script>
    <?php
}

add_action( 'save_post_vpg_event', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_event_host_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_event_host_nonce'], 'vpg_event_host' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $co = array_filter( array_map( 'intval', (array) ( $_POST['vpg_event_cohosts'] ?? [] ) ) );
    $co ? update_post_meta( $post_id, '_vpg_event_cohosts', array_values( $co ) ) : delete_post_meta( $post_id, '_vpg_event_cohosts' );
    foreach ( [ '_vpg_event_backup' => 'vpg_event_backup', '_vpg_event_phototask' => 'vpg_event_phototask', '_vpg_event_devmeet' => 'vpg_event_devmeet' ] as $mk => $f ) {
        $v = sanitize_text_field( wp_unslash( $_POST[ $f ] ?? '' ) );
        $v !== '' ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
    }
    foreach ( [ '_vpg_event_material' => 'vpg_event_material', '_vpg_event_external' => 'vpg_event_external' ] as $mk => $f ) {
        $v = esc_url_raw( wp_unslash( $_POST[ $f ] ?? '' ) );
        $v !== '' ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
    }
    $lang = sanitize_key( $_POST['vpg_event_lang'] ?? '' );
    in_array( $lang, [ 'de', 'en', 'both' ], true ) ? update_post_meta( $post_id, '_vpg_event_lang', $lang ) : delete_post_meta( $post_id, '_vpg_event_lang' );
    $mp = (int) ( $_POST['vpg_event_mpphoto'] ?? 0 );
    $mp ? update_post_meta( $post_id, '_vpg_event_mpphoto', $mp ) : delete_post_meta( $post_id, '_vpg_event_mpphoto' );
} );

add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && ( $s->post_type ?? '' ) === 'vpg_event' ) wp_enqueue_media();
} );

/* editor-side host feedback box (0149) */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-event-fb', '★ ' . __( 'Host-only feedback', 'vpg-v2' ), function ( $post ) {
        $fb = array_reverse( array_filter( (array) get_post_meta( $post->ID, '_vpg_event_feedback', true ) ) );
        if ( ! $fb ) { echo '<p class="description">' . esc_html__( 'No feedback yet.', 'vpg-v2' ) . '</p>'; return; }
        $avg = array_sum( array_column( $fb, 'r' ) ) / count( $fb );
        echo '<p><strong>' . esc_html( number_format( $avg, 1 ) ) . '/5</strong> · ' . count( $fb ) . '</p><ul style="margin:0;font-size:12px">';
        foreach ( array_slice( $fb, 0, 12 ) as $f ) echo '<li>' . str_repeat( '★', (int) $f['r'] ) . ' ' . ( ! empty( $f['n'] ) ? '— ' . esc_html( $f['n'] ) : '' ) . '</li>';
        echo '</ul>';
    }, 'vpg_event', 'side' );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Helpers                                                           */
/* ════════════════════════════════════════════════════════════════ */
function vpg_event_hosts( $id ) {
    $hosts = [ (int) get_post_field( 'post_author', $id ) ];
    foreach ( array_filter( (array) get_post_meta( $id, '_vpg_event_cohosts', true ) ) as $c ) $hosts[] = (int) $c;
    return array_values( array_unique( array_filter( $hosts ) ) );
}
function vpg_event_is_host( $id, $uid = 0 ) {
    $uid = $uid ?: get_current_user_id();
    return $uid && ( in_array( $uid, vpg_event_hosts( $id ), true ) || user_can( $uid, 'edit_others_posts' ) );
}
/* how many walks a member has actually attended (checked in), for buddy + rotation */
function vpg_member_walk_count( $uid ) {
    $n = (int) get_user_meta( $uid, '_vpg_walks_attended', true );
    return $n;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Front-end · status banner, meeting photo, consent (near top)      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_event_render_top( $id ) {
    $status = get_post_meta( $id, '_vpg_event_status', true ); // '', confirmed, postponed, cancelled
    $backup = get_post_meta( $id, '_vpg_event_backup', true );
    $lang   = get_post_meta( $id, '_vpg_event_lang', true );
    $task   = get_post_meta( $id, '_vpg_event_phototask', true );
    $mp     = (int) get_post_meta( $id, '_vpg_event_mpphoto', true );
    $hosts  = vpg_event_hosts( $id );
    if ( $status === 'cancelled' ) : ?>
      <section class="g-wrap" style="margin:20px auto"><p style="background:var(--g-red);color:#fff;font-weight:800;padding:14px 18px;font-size:15px">✕ <?php esc_html_e( 'This walk was cancelled.', 'vpg-v2' ); ?> <?php if ( $backup ) printf( esc_html__( 'A new date: %s.', 'vpg-v2' ), esc_html( $backup ) ); ?></p></section>
    <?php elseif ( $status === 'postponed' ) : ?>
      <section class="g-wrap" style="margin:20px auto"><p style="background:#996800;color:#fff;font-weight:800;padding:14px 18px;font-size:15px">⏳ <?php esc_html_e( 'Postponed for weather.', 'vpg-v2' ); ?> <?php if ( $backup ) printf( esc_html__( 'Backup date: %s.', 'vpg-v2' ), esc_html( $backup ) ); ?></p></section>
    <?php elseif ( $status === 'confirmed' ) : ?>
      <section class="g-wrap" style="margin:20px auto"><p style="border:2px solid #1A7A3C;color:#1A7A3C;font-weight:800;padding:12px 18px;font-size:14px;display:inline-block">✓ <?php esc_html_e( 'Confirmed by the host — it’s on.', 'vpg-v2' ); ?></p></section>
    <?php endif; ?>

    <?php if ( count( $hosts ) > 1 || $lang || $task ) : ?>
    <section class="g-wrap" style="margin:8px auto"><div style="display:flex;flex-wrap:wrap;gap:8px 16px;font-size:12px;font-weight:700;align-items:center">
      <?php if ( count( $hosts ) > 1 ) : $names = array_filter( array_map( function ( $h ) { $u = get_userdata( $h ); return $u ? $u->display_name : ''; }, $hosts ) ); ?>
        <span>👥 <?php echo esc_html( sprintf( __( 'Led by %s', 'vpg-v2' ), implode( ' & ', $names ) ) ); ?></span>
      <?php endif; ?>
      <?php if ( $lang ) : $ll = [ 'de' => 'Deutsch', 'en' => 'English', 'both' => 'DE / EN' ]; ?><span style="border:1px solid var(--g-line);padding:3px 10px">🗣 <?php echo esc_html( $ll[ $lang ] ?? $lang ); ?></span><?php endif; ?>
      <?php if ( $task ) : ?><span style="border:1px solid var(--g-red);color:var(--g-red);padding:3px 10px">🎯 <?php echo esc_html( sprintf( __( 'Task: %s', 'vpg-v2' ), $task ) ); ?></span><?php endif; ?>
    </div></section>
    <?php endif; ?>

    <?php if ( $mp ) : ?>
    <section class="g-wrap" style="margin:16px auto">
      <p class="g-kicker" style="margin-bottom:8px">● <?php esc_html_e( 'This is the exact meeting point', 'vpg-v2' ); ?></p>
      <?php echo wp_get_attachment_image( $mp, 'large', false, [ 'style' => 'width:100%;max-width:640px;height:auto;display:block' ] ); ?>
    </section>
    <?php endif;
}

/* Everything after the RSVP block */
function vpg_event_render_extras( $id ) {
    $uid    = get_current_user_id();
    $rsvps  = function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( $id ) : [];
    $going  = $uid && in_array( $uid, $rsvps, true );
    $is_host= vpg_event_is_host( $id );
    $today  = function_exists( 'vpg_event_is_today' ) && vpg_event_is_today( $id );
    $dev    = get_post_meta( $id, '_vpg_event_devmeet', true );
    $mat    = get_post_meta( $id, '_vpg_event_material', true );
    $recap  = get_post_meta( $id, '_vpg_event_recap', true );
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap">

      <!-- 0145 workshop material · 0146 analog dev meeting -->
      <?php if ( $mat || $dev ) : ?>
      <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px">
        <?php if ( $mat ) : ?><a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( $mat ); ?>" target="_blank" rel="noopener">📎 <?php esc_html_e( 'Workshop material', 'vpg-v2' ); ?></a><?php endif; ?>
        <?php if ( $dev ) : ?><span style="font-size:12px;font-weight:700;border:1px solid var(--g-line);padding:8px 12px">🎞 <?php echo esc_html( sprintf( __( 'Development meet-up: %s', 'vpg-v2' ), $dev ) ); ?></span><?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- 0135 host recap (published) -->
      <?php if ( $recap ) : ?>
        <p class="g-kicker" style="margin:0 0 6px">● <?php esc_html_e( 'The host’s recap', 'vpg-v2' ); ?></p>
        <blockquote style="font-size:16px;line-height:1.6;border-left:3px solid var(--g-red);padding-left:16px;margin:0 0 20px"><?php echo esc_html( $recap ); ?></blockquote>
      <?php endif; ?>

      <?php if ( $going || $is_host ) : ?>
        <!-- 0131 group chat · confirmed attendees only -->
        <?php $chat = array_filter( (array) get_post_meta( $id, '_vpg_event_chat', true ) ); ?>
        <p class="g-kicker" style="margin:12px 0 6px">● <?php esc_html_e( 'Group thread · attendees only', 'vpg-v2' ); ?></p>
        <div style="border:1px solid var(--g-line);padding:12px 14px;max-height:260px;overflow-y:auto;margin-bottom:8px">
          <?php if ( ! $chat ) : ?><p style="color:var(--g-mid);font-size:13px;margin:0"><?php esc_html_e( 'No messages yet — say hi.', 'vpg-v2' ); ?></p><?php else : ?>
            <?php foreach ( array_slice( $chat, -40 ) as $m ) : $mu = get_userdata( (int) ( $m['u'] ?? 0 ) ); ?>
              <p style="margin:0 0 8px;font-size:13px"><strong><?php echo esc_html( $mu ? $mu->display_name : '—' ); ?></strong> <span style="color:var(--g-faint);font-size:11px"><?php echo esc_html( human_time_diff( (int) ( $m['t'] ?? 0 ) ) ); ?></span><br><?php echo esc_html( $m['m'] ?? '' ); ?></p>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;margin-bottom:24px">
          <?php wp_nonce_field( 'vpg_event_chat' ); ?>
          <input type="hidden" name="action" value="vpg_event_chat"><input type="hidden" name="event" value="<?php echo (int) $id; ?>">
          <input type="text" name="msg" maxlength="240" required placeholder="<?php esc_attr_e( 'Message the group…', 'vpg-v2' ); ?>" style="flex:1;padding:9px;border:1px solid var(--g-line)">
          <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Send', 'vpg-v2' ); ?></button>
        </form>
      <?php endif; ?>

      <!-- 0133 buddy pairing (shown when there are newcomers) -->
      <?php $buddies = vpg_event_buddies( $id );
      if ( $buddies && ( $going || $is_host ) ) : ?>
        <p class="g-kicker" style="margin:12px 0 6px">● <?php esc_html_e( 'Buddies for the day', 'vpg-v2' ); ?></p>
        <ul style="font-size:13px;margin:0 0 20px;padding-left:18px">
          <?php foreach ( $buddies as $pair ) : ?><li><?php echo esc_html( $pair[0] . ' ↔ ' . $pair[1] ); ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <!-- 0148 photo swap ritual (round-robin gifts) -->
      <?php $swap = vpg_event_swap_pairs( $id );
      if ( $swap && ( $going || $is_host ) ) : ?>
        <p class="g-kicker" style="margin:12px 0 6px">● <?php esc_html_e( 'Photo swap — give one image to…', 'vpg-v2' ); ?></p>
        <ul style="font-size:13px;margin:0 0 20px;padding-left:18px">
          <?php foreach ( $swap as $g ) : ?><li><?php echo esc_html( $g[0] . ' → ' . $g[1] ); ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <!-- 0132 icebreaker cards -->
      <?php if ( $going || $is_host ) : ?>
        <p class="g-kicker" style="margin:12px 0 6px">● <?php esc_html_e( 'Icebreaker', 'vpg-v2' ); ?></p>
        <div id="vpg-icebreaker" style="border:1px dashed var(--g-line);padding:16px;font-size:15px;font-weight:600;margin-bottom:8px"></div>
        <button type="button" id="vpg-ice-next" class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Another question', 'vpg-v2' ); ?></button>
        <div style="margin-bottom:24px"></div>
      <?php endif; ?>

      <!-- 0129 host live location · 0130 latecomer -->
      <?php if ( $today && ( $going || $is_host ) ) : ?>
        <p class="g-kicker" style="margin:12px 0 6px">● <?php esc_html_e( 'Where’s the group?', 'vpg-v2' ); ?></p>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px">
          <?php if ( $is_host ) : ?><button type="button" id="vpg-hostloc-share" class="g-btn g-btn--red" style="font-size:12px" data-event="<?php echo (int) $id; ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'vpg_hostloc' ) ); ?>">📍 <?php esc_html_e( 'Share my location (15 min)', 'vpg-v2' ); ?></button><?php endif; ?>
          <button type="button" id="vpg-hostloc-find" class="g-btn g-btn--ghost" style="font-size:12px" data-event="<?php echo (int) $id; ?>">🧭 <?php esc_html_e( 'Catch up to the group', 'vpg-v2' ); ?></button>
        </div>
        <p id="vpg-hostloc-out" style="font-size:12px;color:var(--g-mid)"></p>
        <div style="margin-bottom:24px"></div>
      <?php endif; ?>

      <!-- 0135 recap prompt (host, after the day) · 0160 cancel · 0126 weather -->
      <?php if ( $is_host ) : ?>
        <div style="border-top:1px solid var(--g-line);padding-top:16px;margin-top:8px">
          <p class="g-kicker" style="margin:0 0 8px">● <?php esc_html_e( 'Host controls', 'vpg-v2' ); ?></p>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
            <?php foreach ( [ 'confirm' => __( '✓ Confirm it’s on', 'vpg-v2' ), 'postpone' => __( '⏳ Postpone (weather)', 'vpg-v2' ), 'cancel' => __( '✕ Cancel & notify', 'vpg-v2' ) ] as $act => $lbl ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0" onsubmit="return <?php echo $act === 'cancel' ? 'confirm(\'' . esc_js( __( 'Cancel this walk and notify everyone?', 'vpg-v2' ) ) . '\')' : 'true'; ?>">
              <?php wp_nonce_field( 'vpg_event_decide' ); ?>
              <input type="hidden" name="action" value="vpg_event_decide"><input type="hidden" name="event" value="<?php echo (int) $id; ?>"><input type="hidden" name="decision" value="<?php echo esc_attr( $act ); ?>">
              <button class="g-btn g-btn--ghost" style="font-size:12px"><?php echo esc_html( $lbl ); ?></button>
            </form>
            <?php endforeach; ?>
          </div>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start">
            <?php wp_nonce_field( 'vpg_event_recap' ); ?>
            <input type="hidden" name="action" value="vpg_event_recap"><input type="hidden" name="event" value="<?php echo (int) $id; ?>">
            <textarea name="recap" rows="2" maxlength="600" placeholder="<?php esc_attr_e( 'Three sentences on how the walk went (required after each event)…', 'vpg-v2' ); ?>" style="flex:1;min-width:260px;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( $recap ); ?></textarea>
            <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Save recap', 'vpg-v2' ); ?></button>
          </form>
          <p style="margin-top:10px"><a href="<?php echo esc_url( home_url( '/host-handbook/' ) ); ?>" style="font-size:12px;font-weight:700">📘 <?php esc_html_e( 'Host handbook', 'vpg-v2' ); ?></a> · <a href="<?php echo esc_url( home_url( '/event-collage/' . $id . '/' ) ); ?>" target="_blank" style="font-size:12px;font-weight:700">🖼 <?php esc_html_e( 'Memory collage', 'vpg-v2' ); ?></a></p>
        </div>
      <?php endif; ?>

      <!-- 0149 attendee feedback to the host -->
      <?php if ( $going && ! $is_host ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:16px">
          <?php wp_nonce_field( 'vpg_event_feedback' ); ?>
          <input type="hidden" name="action" value="vpg_event_feedback"><input type="hidden" name="event" value="<?php echo (int) $id; ?>">
          <span style="font-size:12px;font-weight:700"><?php esc_html_e( 'Rate for the host (private):', 'vpg-v2' ); ?></span>
          <select name="rating" style="padding:6px"><option value="5">★★★★★</option><option value="4">★★★★</option><option value="3">★★★</option><option value="2">★★</option><option value="1">★</option></select>
          <input type="text" name="note" maxlength="160" placeholder="<?php esc_attr_e( 'Optional note', 'vpg-v2' ); ?>" style="flex:1;min-width:180px;padding:6px;border:1px solid var(--g-line)">
          <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Send', 'vpg-v2' ); ?></button>
        </form>
      <?php endif; ?>

      <!-- 0141 consent / portrait etiquette · 0140 no-photo -->
      <div style="border-top:1px solid var(--g-line);margin-top:22px;padding-top:14px;font-size:12px;color:var(--g-mid)">
        <strong style="color:var(--g-ink)"><?php esc_html_e( 'Portrait etiquette', 'vpg-v2' ); ?>.</strong>
        <?php esc_html_e( 'Ask before you photograph a person. Anyone can opt out of being photographed for the whole walk — say so at the start, and it’s respected without a reason.', 'vpg-v2' ); ?>
        <?php if ( $going ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
            <?php wp_nonce_field( 'vpg_event_nophoto' ); ?>
            <input type="hidden" name="action" value="vpg_event_nophoto"><input type="hidden" name="event" value="<?php echo (int) $id; ?>">
            <?php $np = in_array( $uid, array_filter( (array) get_post_meta( $id, '_vpg_event_nophoto', true ) ), true ); ?>
            <button style="background:none;border:1px solid var(--g-line);padding:4px 10px;font-size:11px;font-weight:700;cursor:pointer;margin-left:6px"><?php echo $np ? esc_html__( '✓ I opted out of photos', 'vpg-v2' ) : esc_html__( 'Opt me out of photos', 'vpg-v2' ); ?></button>
          </form>
        <?php endif; ?>
      </div>
    </div></section>

    <script>
    (function(){
      /* 0132 icebreaker */
      var Q=<?php echo wp_json_encode( array_map( 'strval', vpg_event_icebreakers() ) ); ?>;
      var box=document.getElementById('vpg-icebreaker'),nx=document.getElementById('vpg-ice-next');
      function pick(){if(box)box.textContent=Q[Math.floor(Math.random()*Q.length)];}
      if(box){pick();if(nx)nx.addEventListener('click',pick);}
      /* 0129/0130 host live location */
      var share=document.getElementById('vpg-hostloc-share'),find=document.getElementById('vpg-hostloc-find'),out=document.getElementById('vpg-hostloc-out');
      if(share)share.addEventListener('click',function(){
        if(!navigator.geolocation){out.textContent='<?php echo esc_js( __( 'No geolocation.', 'vpg-v2' ) ); ?>';return;}
        out.textContent='<?php echo esc_js( __( 'Sharing for 15 minutes…', 'vpg-v2' ) ); ?>';
        var end=Date.now()+15*60*1000;
        var t=setInterval(function(){ if(Date.now()>end){clearInterval(t);out.textContent='<?php echo esc_js( __( 'Sharing ended.', 'vpg-v2' ) ); ?>';return;}
          navigator.geolocation.getCurrentPosition(function(p){
            var fd=new FormData();fd.append('action','vpg_hostloc');fd.append('_ajax_nonce',share.dataset.nonce);fd.append('event',share.dataset.event);fd.append('lat',p.coords.latitude);fd.append('lng',p.coords.longitude);
            fetch(ajaxurl_vpg,{method:'POST',body:fd,credentials:'same-origin'});
          });
        },20000);
      });
      if(find)find.addEventListener('click',function(){
        fetch(ajaxurl_vpg+'?action=vpg_hostloc&event='+find.dataset.event,{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
          if(j.success&&j.data.lat){out.innerHTML='<a target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat='+j.data.lat+'&mlon='+j.data.lng+'#map=17/'+j.data.lat+'/'+j.data.lng+'">📍 <?php echo esc_js( __( 'The group is about here — open map', 'vpg-v2' ) ); ?></a> <span style="opacity:.6">('+j.data.ago+')</span>';}
          else out.textContent='<?php echo esc_js( __( 'No live location shared right now.', 'vpg-v2' ) ); ?>';
        });
      });
    })();
    var ajaxurl_vpg=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
    </script>
    <?php
}

/* icebreaker question bank (0132) */
function vpg_event_icebreakers() {
    return [
        __( 'What’s in your bag today — and what did you leave at home?', 'vpg-v2' ),
        __( 'One photographer whose eye you’d steal for a day?', 'vpg-v2' ),
        __( 'The last frame that surprised you?', 'vpg-v2' ),
        __( 'Colour or black & white for this walk — and why?', 'vpg-v2' ),
        __( 'A Vienna corner you think nobody shoots?', 'vpg-v2' ),
        __( 'What made you pick up a camera in the first place?', 'vpg-v2' ),
        __( 'Prime or zoom person?', 'vpg-v2' ),
        __( 'The shot you keep failing to get right?', 'vpg-v2' ),
    ];
}

/* 0133 buddy pairing · newcomers (0 walks) ↔ experienced (>=3) */
function vpg_event_buddies( $id ) {
    $rsvps = function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( $id ) : [];
    $new = []; $exp = [];
    foreach ( $rsvps as $r ) {
        $u = get_userdata( $r ); if ( ! $u ) continue;
        if ( vpg_member_walk_count( $r ) >= 3 ) $exp[] = $u->display_name; else $new[] = $u->display_name;
    }
    $pairs = [];
    foreach ( $new as $i => $nm ) { if ( isset( $exp[ $i ] ) ) $pairs[] = [ $nm, $exp[ $i ] ]; }
    return $pairs;
}

/* 0148 round-robin photo-swap pairs */
function vpg_event_swap_pairs( $id ) {
    $rsvps = function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( $id ) : [];
    $names = array_values( array_filter( array_map( function ( $r ) { $u = get_userdata( $r ); return $u ? $u->display_name : ''; }, $rsvps ) ) );
    if ( count( $names ) < 2 ) return [];
    $out = [];
    for ( $i = 0; $i < count( $names ); $i++ ) $out[] = [ $names[ $i ], $names[ ( $i + 1 ) % count( $names ) ] ];
    return $out;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Handlers · chat, recap, decide, feedback, no-photo, host loc      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_event_chat', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_event_chat' );
    $id = (int) ( $_POST['event'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_event' ) wp_die( 'Not found', 404 );
    $uid = get_current_user_id();
    if ( ! vpg_event_is_host( $id ) && ! in_array( $uid, vpg_event_rsvps( $id ), true ) ) wp_die( 'Attendees only', 403 );
    $msg = sanitize_text_field( wp_unslash( $_POST['msg'] ?? '' ) );
    if ( $msg !== '' ) {
        $chat = array_filter( (array) get_post_meta( $id, '_vpg_event_chat', true ) );
        $chat[] = [ 'u' => $uid, 'm' => $msg, 't' => time() ];
        update_post_meta( $id, '_vpg_event_chat', array_slice( $chat, -200 ) );
    }
    wp_safe_redirect( get_permalink( $id ) . '#rsvp' ); exit;
} );

add_action( 'admin_post_vpg_event_recap', function () {
    check_admin_referer( 'vpg_event_recap' );
    $id = (int) ( $_POST['event'] ?? 0 );
    if ( ! vpg_event_is_host( $id ) ) wp_die( 'Hosts only', 403 );
    $r = sanitize_textarea_field( wp_unslash( $_POST['recap'] ?? '' ) );
    $r !== '' ? update_post_meta( $id, '_vpg_event_recap', $r ) : delete_post_meta( $id, '_vpg_event_recap' );
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );

add_action( 'admin_post_vpg_event_decide', function () {
    check_admin_referer( 'vpg_event_decide' );
    $id = (int) ( $_POST['event'] ?? 0 );
    if ( ! vpg_event_is_host( $id ) ) wp_die( 'Hosts only', 403 );
    $d = sanitize_key( $_POST['decision'] ?? '' );
    $map = [ 'confirm' => 'confirmed', 'postpone' => 'postponed', 'cancel' => 'cancelled' ];
    if ( isset( $map[ $d ] ) ) {
        update_post_meta( $id, '_vpg_event_status', $map[ $d ] );
        if ( in_array( $d, [ 'postpone', 'cancel' ], true ) ) vpg_event_notify_all( $id, $d ); // 0126 / 0160
    }
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );

/* 0126 / 0160 · notify every RSVP of a postpone or cancellation */
function vpg_event_notify_all( $id, $kind ) {
    $subj = $kind === 'cancel'
        ? sprintf( __( 'Cancelled: %s', 'vpg-v2' ), get_the_title( $id ) )
        : sprintf( __( 'Postponed: %s', 'vpg-v2' ), get_the_title( $id ) );
    $backup = get_post_meta( $id, '_vpg_event_backup', true );
    $body = $kind === 'cancel'
        ? __( 'The host has cancelled this walk. Thanks for understanding — we hope to see you at the next one.', 'vpg-v2' )
        : sprintf( __( 'The host postponed this walk for the weather.%s', 'vpg-v2' ), $backup ? ' ' . sprintf( __( 'New date: %s.', 'vpg-v2' ), $backup ) : '' );
    $body .= "\n\n" . get_permalink( $id );
    foreach ( vpg_event_rsvps( $id ) as $r ) {
        $u = get_userdata( $r ); if ( ! $u || ! $u->user_email ) continue;
        if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $r, $subj, get_permalink( $id ), 'event' );
        wp_mail( $u->user_email, $subj, $body );
    }
}

add_action( 'admin_post_vpg_event_feedback', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_event_feedback' );
    $id = (int) ( $_POST['event'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_event' ) wp_die( 'Not found', 404 );
    $fb = array_filter( (array) get_post_meta( $id, '_vpg_event_feedback', true ) );
    $fb[] = [ 'u' => get_current_user_id(), 'r' => max( 1, min( 5, (int) ( $_POST['rating'] ?? 0 ) ) ), 'n' => sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ), 't' => time() ];
    update_post_meta( $id, '_vpg_event_feedback', array_slice( $fb, -120 ) );
    wp_safe_redirect( get_permalink( $id ) . '#rsvp' ); exit;
} );

add_action( 'admin_post_vpg_event_nophoto', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_event_nophoto' );
    $id = (int) ( $_POST['event'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_event' ) wp_die( 'Not found', 404 );
    $uid = get_current_user_id();
    $list = array_filter( (array) get_post_meta( $id, '_vpg_event_nophoto', true ) );
    $i = array_search( $uid, $list, true );
    if ( $i !== false ) unset( $list[ $i ] ); else $list[] = $uid;
    update_post_meta( $id, '_vpg_event_nophoto', array_values( $list ) );
    wp_safe_redirect( get_permalink( $id ) . '#rsvp' ); exit;
} );

/* 0129 host live location — short-lived transient, event window only */
add_action( 'wp_ajax_vpg_hostloc', function () {
    $id = (int) ( $_REQUEST['event'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_event' ) wp_send_json_error();
    if ( isset( $_POST['lat'] ) ) { // host writing
        check_ajax_referer( 'vpg_hostloc' );
        if ( ! vpg_event_is_host( $id ) ) wp_send_json_error( [], 403 );
        set_transient( 'vpg_hostloc_' . $id, [ 'lat' => round( (float) $_POST['lat'], 5 ), 'lng' => round( (float) $_POST['lng'], 5 ), 't' => time() ], 20 * MINUTE_IN_SECONDS );
        wp_send_json_success();
    }
    $loc = get_transient( 'vpg_hostloc_' . $id ); // anyone reading
    if ( ! $loc ) wp_send_json_error();
    wp_send_json_success( [ 'lat' => $loc['lat'], 'lng' => $loc['lng'], 'ago' => human_time_diff( $loc['t'] ) . ' ' . __( 'ago', 'vpg-v2' ) ] );
} );
add_action( 'wp_ajax_nopriv_vpg_hostloc', '__return_false' );

/* Count a walk as attended once the day passes (feeds buddy + rotation). */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_tally_attendance' ) ) wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vpg_tally_attendance' );
} );
add_action( 'vpg_tally_attendance', function () {
    $past = get_posts( [ 'post_type' => 'vpg_event', 'post_status' => 'publish', 'posts_per_page' => 60, 'meta_key' => '_vpg_event_date', 'meta_value' => gmdate( 'Y-m-d' ), 'meta_compare' => '<' ] );
    foreach ( $past as $e ) {
        if ( get_post_meta( $e->ID, '_vpg_attendance_tallied', true ) ) continue;
        // prefer real check-ins, fall back to RSVPs
        $present = array_filter( (array) get_post_meta( $e->ID, '_vpg_checkins', true ) ) ?: vpg_event_rsvps( $e->ID );
        foreach ( $present as $uid ) {
            $uid = (int) ( is_array( $uid ) ? ( $uid['u'] ?? 0 ) : $uid );
            if ( $uid ) update_user_meta( $uid, '_vpg_walks_attended', (int) get_user_meta( $uid, '_vpg_walks_attended', true ) + 1 );
        }
        update_post_meta( $e->ID, '_vpg_attendance_tallied', 1 );
    }
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_tally_attendance' ); } );

/* ════════════════════════════════════════════════════════════════ */
/*  0150 host handbook · 0158 club year review · 0154 memory collage  */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^host-handbook/?$', 'index.php?vpg_hosthandbook=1', 'top' );
    add_rewrite_rule( '^jahr/(\d{4})/?$', 'index.php?vpg_eventyear=$matches[1]', 'top' );
    add_rewrite_rule( '^event-collage/(\d+)/?$', 'index.php?vpg_eventcollage=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_hosthandbook'; $v[] = 'vpg_eventyear'; $v[] = 'vpg_eventcollage'; return $v; } );

add_action( 'template_redirect', function () {
    // 0150 · host handbook
    if ( get_query_var( 'vpg_hosthandbook' ) ) {
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap">
          <p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'For hosts', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Lead your first <em>walk</em>.', 'vpg-v2' ) ); ?></h1>
        </div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px">
          <div class="g-prose">
            <h3><?php esc_html_e( 'Before', 'vpg-v2' ); ?></h3>
            <ol>
              <li><?php esc_html_e( 'Pick a meeting point that’s easy to find — add a photo of the exact spot.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Set a realistic cap and a backup date for bad weather.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Give the walk one photo task — a theme unites the group’s pictures.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Confirm the day before. If the sky looks wrong, postpone by noon.', 'vpg-v2' ); ?></li>
            </ol>
            <h3><?php esc_html_e( 'During', 'vpg-v2' ); ?></h3>
            <ol>
              <li><?php esc_html_e( 'Read the portrait etiquette aloud. Ask who wants to opt out of photos.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Pair newcomers with an experienced buddy. Share your live location for the first quarter hour.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Keep the pace gentle — a walk is not a race.', 'vpg-v2' ); ?></li>
            </ol>
            <h3><?php esc_html_e( 'After', 'vpg-v2' ); ?></h3>
            <ol>
              <li><?php esc_html_e( 'Open the shared gallery and invite everyone to add a few frames.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Write three sentences of recap. That’s the whole report.', 'vpg-v2' ); ?></li>
              <li><?php esc_html_e( 'Run the photo swap — each person gifts one image.', 'vpg-v2' ); ?></li>
            </ol>
          </div>
        </div></section></main>
        <?php get_footer(); exit;
    }

    // 0158 · club-wide year review
    if ( $yr = (int) get_query_var( 'vpg_eventyear' ) ) {
        $events = get_posts( [ 'post_type' => 'vpg_event', 'post_status' => 'publish', 'posts_per_page' => -1,
            'meta_query' => [ [ 'key' => '_vpg_event_date', 'value' => [ "$yr-01-01", "$yr-12-31" ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ] ] );
        $pins = []; $attend = 0;
        foreach ( $events as $e ) {
            $attend += count( vpg_event_rsvps( $e->ID ) );
            $la = (float) get_post_meta( $e->ID, '_vpg_event_lat', true ); $lo = (float) get_post_meta( $e->ID, '_vpg_event_lng', true );
            if ( $la ) $pins[] = [ 'id' => $e->ID, 'lat' => $la, 'lng' => $lo, 'title' => get_the_title( $e ), 'url' => get_permalink( $e ), 'type' => 'event' ];
        }
        add_filter( 'pre_get_document_title', fn() => sprintf( __( 'The %d walks · Vienna Photo Group', 'vpg-v2' ), $yr ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap">
          <p class="g-kicker" style="margin-bottom:16px">● <?php echo esc_html( $yr ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo esc_html( count( $events ) ); ?> <em><?php esc_html_e( 'walks', 'vpg-v2' ); ?></em>.</h1>
          <p class="g-lede g-phero__lede"><?php printf( esc_html__( '%1$d walks · %2$d seats filled · across Wien, a member-run year.', 'vpg-v2' ), count( $events ), $attend ); ?></p>
        </div></section>
        <?php if ( $pins ) : ?>
        <section class="g-section g-section--tight"><div class="g-wrap"><div id="vpg-map" class="vpg-map vpg-map--tall" data-pins="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>"></div></div></section>
        <?php endif; ?>
        <section class="g-section"><div class="g-wrap"><div class="g-list">
          <?php foreach ( $events as $e ) : ?>
            <a class="g-row" href="<?php echo esc_url( get_permalink( $e ) ); ?>"><span style="font-weight:700;color:var(--g-mid)"><?php echo esc_html( get_post_meta( $e->ID, '_vpg_event_date', true ) ); ?></span><h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $e ) ); ?></h3><span class="g-row__when"><?php echo count( vpg_event_rsvps( $e->ID ) ); ?> ✦</span></a>
          <?php endforeach; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }

    // 0154 · memory collage (host thank-you card from the gallery)
    if ( $cid = (int) get_query_var( 'vpg_eventcollage' ) ) {
        if ( get_post_type( $cid ) !== 'vpg_event' ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
        $shots = function_exists( 'vpg_event_gallery' ) ? array_slice( vpg_event_gallery( $cid ), 0, 9 ) : [];
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=1200"><title><?php echo esc_html( get_the_title( $cid ) ); ?> · Collage</title>
        <style>*{box-sizing:border-box;margin:0;padding:0}body{background:#0B0B0B;font-family:'Helvetica Neue',Arial,sans-serif}.c{width:1200px;background:#0B0B0B;color:#fff;padding:48px}.k{font-size:16px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}h1{font-size:52px;font-weight:900;text-transform:uppercase;margin:10px 0 24px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.grid img{width:100%;aspect-ratio:1;object-fit:cover;display:block}.noprint{margin-top:20px}@media print{.noprint{display:none}}</style></head><body>
        <div class="c"><p class="k">Vienna Photo Group · <?php esc_html_e( 'Thank you for walking', 'vpg-v2' ); ?></p><h1><?php echo esc_html( get_the_title( $cid ) ); ?></h1>
        <?php if ( $shots ) : ?><div class="grid"><?php foreach ( $shots as $sh ) { $u = is_array( $sh ) ? ( $sh['url'] ?? '' ) : wp_get_attachment_image_url( (int) $sh, 'medium' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" alt="">'; } ?></div>
        <?php else : ?><p><?php esc_html_e( 'No gallery photos yet.', 'vpg-v2' ); ?></p><?php endif; ?>
        <p class="noprint"><button onclick="window.print()" style="border:1px solid #fff;background:none;color:#fff;padding:8px 16px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Save / print', 'vpg-v2' ); ?></button></p></div>
        </body></html><?php exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · theme months (0144) · museum partners (0153)              */
/*        · event templates (0159) · host-rotation nudge (0151)       */
/*        · route heatmap suggestions (0157)                          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_event', __( 'Programme desk', 'vpg-v2' ), '🗓 ' . __( 'Programme desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-programme', 'vpg_programme_desk' );
}, 20 );

function vpg_programme_desk() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    // save theme months + museums + templates
    if ( isset( $_POST['vpg_prog'] ) && check_admin_referer( 'vpg_prog' ) ) {
        $months = array_map( 'sanitize_text_field', (array) ( $_POST['months'] ?? [] ) );
        update_option( 'vpg_event_theme_months', array_slice( $months, 0, 12 ), false );
        update_option( 'vpg_event_museums', sanitize_textarea_field( wp_unslash( $_POST['museums'] ?? '' ) ), false );
        update_option( 'vpg_event_templates', sanitize_textarea_field( wp_unslash( $_POST['templates'] ?? '' ) ), false );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $months = (array) get_option( 'vpg_event_theme_months', [] );
    $mn = [ __( 'January', 'vpg-v2' ), __( 'February', 'vpg-v2' ), __( 'March', 'vpg-v2' ), __( 'April', 'vpg-v2' ), __( 'May', 'vpg-v2' ), __( 'June', 'vpg-v2' ), __( 'July', 'vpg-v2' ), __( 'August', 'vpg-v2' ), __( 'September', 'vpg-v2' ), __( 'October', 'vpg-v2' ), __( 'November', 'vpg-v2' ), __( 'December', 'vpg-v2' ) ];
    // 0151 host-rotation nudge — long-standing members who never hosted
    $veterans = get_users( [ 'number' => 300, 'fields' => [ 'ID', 'display_name', 'user_registered' ] ] );
    $nudge = [];
    foreach ( $veterans as $v ) {
        if ( strtotime( $v->user_registered ) > strtotime( '-1 year' ) ) continue;
        $hosted = get_posts( [ 'post_type' => 'vpg_event', 'author' => $v->ID, 'posts_per_page' => 1, 'fields' => 'ids' ] );
        if ( ! $hosted && vpg_member_walk_count( $v->ID ) >= 3 ) $nudge[] = $v->display_name;
    }
    ?>
    <div class="wrap">
      <h1>🗓 <?php esc_html_e( 'Programme desk', 'vpg-v2' ); ?></h1>
      <form method="post"><?php wp_nonce_field( 'vpg_prog' ); ?>
        <h2><?php esc_html_e( '0144 · Theme months', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'A leitmotif per month — shown on the events archive. e.g. February = Fog, June = Blue hour.', 'vpg-v2' ); ?></p>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px 20px;max-width:720px">
          <?php foreach ( $mn as $i => $label ) : ?>
            <label><?php echo esc_html( $label ); ?> <input type="text" name="months[<?php echo $i; ?>]" value="<?php echo esc_attr( $months[ $i ] ?? '' ); ?>" style="width:60%"></label>
          <?php endforeach; ?>
        </div>
        <h2 style="margin-top:22px"><?php esc_html_e( '0153 · Museum & partner conditions', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'One per line: “Partner — condition”. Shown on the events archive.', 'vpg-v2' ); ?></p>
        <textarea name="museums" rows="4" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_event_museums', '' ) ); ?></textarea>
        <h2 style="margin-top:22px"><?php esc_html_e( '0159 · Event templates', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'One per line: “Name | default title | cap | language(de/en/both)”. Offered as one-click starters when creating an event.', 'vpg-v2' ); ?></p>
        <textarea name="templates" rows="4" style="width:100%;max-width:720px" placeholder="Classic evening walk | Evening walk · Wien | 12 | both"><?php echo esc_textarea( get_option( 'vpg_event_templates', '' ) ); ?></textarea>
        <p><button class="button button-primary vpg_prog"><?php esc_html_e( 'Save programme', 'vpg-v2' ); ?></button></p>
      </form>

      <h2 style="margin-top:22px"><?php esc_html_e( '0151 · Host rotation — gentle nudges', 'vpg-v2' ); ?></h2>
      <?php if ( $nudge ) : ?>
        <p><?php esc_html_e( 'Long-standing members who’ve walked a lot but never hosted — worth a friendly invitation to lead:', 'vpg-v2' ); ?></p>
        <p><?php echo esc_html( implode( ', ', array_slice( $nudge, 0, 30 ) ) ); ?></p>
      <?php else : ?><p class="description"><?php esc_html_e( 'Everyone eligible has hosted. Lovely.', 'vpg-v2' ); ?></p><?php endif; ?>

      <h2 style="margin-top:22px"><?php esc_html_e( '0157 · Most-walked routes (trail seeds)', 'vpg-v2' ); ?></h2>
      <p class="description"><?php esc_html_e( 'The best-attended walks — their meeting points are natural ground for a curated trail. Turn a popular route into a lasting trail.', 'vpg-v2' ); ?></p>
      <?php
      $seed = [];
      foreach ( get_posts( [ 'post_type' => 'vpg_event', 'posts_per_page' => 300, 'post_status' => 'publish' ] ) as $e ) {
          $n = count( vpg_event_rsvps( $e->ID ) );
          if ( $n ) $seed[ $e->ID ] = $n;
      }
      arsort( $seed );
      echo '<ul>';
      foreach ( array_slice( $seed, 0, 8, true ) as $eid => $n ) {
          $has_pin = (bool) get_post_meta( $eid, '_vpg_event_lat', true );
          echo '<li><a href="' . esc_url( get_edit_post_link( $eid ) ) . '">' . esc_html( get_the_title( $eid ) ) . '</a> — ' . esc_html( $n . ' ' . __( 'seats walked', 'vpg-v2' ) ) . ( $has_pin ? ' <span style="color:#1A7A3C">📍</span>' : '' ) . '</li>';
      }
      echo '</ul>';
      ?>
    </div>
    <?php
}

/* 0144 / 0153 · surface theme months + museum partners on the events archive */
add_action( 'loop_start', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! is_post_type_archive( 'vpg_event' ) ) return;
    static $done = false; if ( $done ) return; $done = true;
    $months = array_filter( (array) get_option( 'vpg_event_theme_months', [] ) );
    $museums = trim( (string) get_option( 'vpg_event_museums', '' ) );
    if ( ! $months && ! $museums ) return;
    echo '<section class="g-wrap" style="margin:20px auto"><div style="display:flex;flex-wrap:wrap;gap:20px">';
    if ( $months ) {
        $cur = (int) wp_date( 'n' ) - 1;
        echo '<div style="flex:1;min-width:240px"><p class="g-kicker" style="margin-bottom:8px">● ' . esc_html__( 'This month’s theme', 'vpg-v2' ) . '</p>';
        echo '<p style="font-size:20px;font-weight:900">' . esc_html( $months[ $cur ] ?? __( 'Open theme', 'vpg-v2' ) ) . '</p></div>';
    }
    if ( $museums ) {
        echo '<div style="flex:1;min-width:240px"><p class="g-kicker" style="margin-bottom:8px">● ' . esc_html__( 'Partner conditions', 'vpg-v2' ) . '</p><ul style="font-size:12px;margin:0;padding-left:16px">';
        foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", $museums ) ) ), 0, 6 ) as $line ) echo '<li>' . esc_html( $line ) . '</li>';
        echo '</ul></div>';
    }
    echo '</div></section>';
} );

/* 0159 · one-click template starters on the "new event" screen */
add_action( 'admin_notices', function () {
    $s = get_current_screen();
    if ( ! $s || $s->base !== 'post' || ( $s->post_type ?? '' ) !== 'vpg_event' || $s->action !== 'add' ) return;
    $tpl = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_event_templates', '' ) ) ) );
    if ( ! $tpl ) return;
    echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Start from a template:', 'vpg-v2' ) . '</strong> ';
    foreach ( $tpl as $line ) {
        $p = array_map( 'trim', explode( '|', $line ) );
        if ( count( $p ) < 2 ) continue;
        echo '<button type="button" class="button vpg-tpl" data-title="' . esc_attr( $p[1] ) . '" data-cap="' . esc_attr( $p[2] ?? '' ) . '" data-lang="' . esc_attr( $p[3] ?? '' ) . '" style="margin:2px">' . esc_html( $p[0] ) . '</button> ';
    }
    echo '</p></div>';
    ?><script>
    document.querySelectorAll('.vpg-tpl').forEach(function(b){b.addEventListener('click',function(){
      var t=document.getElementById('title');if(t){t.value=b.dataset.title;t.focus();}
      var cap=document.querySelector('[name="_vpg_event_cap"],#_vpg_event_cap');if(cap&&b.dataset.cap)cap.value=b.dataset.cap;
      var lang=document.querySelector('[name="vpg_event_lang"]');if(lang&&b.dataset.lang)lang.value=b.dataset.lang;
    });});
    </script><?php
} );
