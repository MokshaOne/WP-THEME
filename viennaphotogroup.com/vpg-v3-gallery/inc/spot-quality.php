<?php
/**
 * VPG v3 — Cluster 02 · Spot-Daten & Qualität.
 *
 * Everything that turns a pin into a small, current, reliable encyclopedia:
 * verification, voting, completeness, decay, changelog, EXIF summary,
 * colour character, similar spots, neighbour districts, an audio note, a
 * best-time matrix, editorial merge/import/sync tools and a quality desk.
 *
 *   0042 best-time matrix · 0046 second-member verification
 *   0052 example-image requirement · 0053 EXIF summary · 0054 merge
 *   0055 internal voting · 0056 decay detection · 0058 audio note
 *   0059 colour character · 0062 spot paten · 0063 mass import
 *   0064 coord precision check · 0065 address normalisation
 *   0066 district profiles · 0067 hours sync · 0069 contact check
 *   0070 changelog · 0071 completeness score · 0072 photo rotation
 *   0073 season pair labels · 0075 similar spots · 0077 donation hint
 *   0078 neighbour districts · 0080 quality dashboard
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_SQ_TYPES = [ 'vpg_location', 'vpg_studio', 'vpg_shop' ];

/* ════════════════════════════════════════════════════════════════ */
/*  0071 · Completeness score — the shared quality signal            */
/* ════════════════════════════════════════════════════════════════ */
function vpg_spot_completeness( $id ) {
    $have = 0; $fields = [];
    // core
    $fields['photo']   = (bool) get_post_thumbnail_id( $id );
    $fields['body']    = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) ) >= 20;
    $pre = get_post_type( $id ) === 'vpg_shop' ? 'shop' : ( get_post_type( $id ) === 'vpg_studio' ? 'studio' : 'location' );
    $fields['coords']  = (bool) get_post_meta( $id, $pre . '_lat', true );
    $fields['district']= (bool) ( get_post_meta( $id, 'location_district', true ) ?: get_post_meta( $id, 'shop_district', true ) );
    // curated attributes worth having
    $attrs = function_exists( 'vpg_spot_attrs' ) ? vpg_spot_attrs( $id ) : [];
    foreach ( [ 'best', 'tripod', 'facing', 'safety', 'genres', 'station' ] as $k ) {
        $fields[ $k ] = $k === 'best' ? (bool) get_post_meta( $id, 'location_best_time', true ) : ! empty( $attrs[ $k ] );
    }
    foreach ( $fields as $ok ) if ( $ok ) $have++;
    $total = count( $fields );
    return [ 'score' => (int) round( $have / $total * 100 ), 'have' => $have, 'total' => $total, 'fields' => $fields ];
}

/* ════════════════════════════════════════════════════════════════ */
/*  Second metabox · best-time matrix + audio note                   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    foreach ( VPG_SQ_TYPES as $t ) {
        add_meta_box( 'vpg_spot_quality', '⏱ ' . __( 'Best-time matrix & audio', 'vpg-v2' ), 'vpg_render_spot_quality_box', $t, 'normal', 'default' );
    }
} );

function vpg_render_spot_quality_box( $post ) {
    wp_nonce_field( 'vpg_spot_quality', 'vpg_spot_quality_nonce' );
    $grid  = (array) get_post_meta( $post->ID, '_vpg_besttime', true );   // [weekday][slot] => 0..2
    $slots = [ 'dawn' => __( 'Dawn', 'vpg-v2' ), 'day' => __( 'Day', 'vpg-v2' ), 'dusk' => __( 'Dusk', 'vpg-v2' ), 'night' => __( 'Night', 'vpg-v2' ) ];
    $days  = [ __( 'Mon', 'vpg-v2' ), __( 'Tue', 'vpg-v2' ), __( 'Wed', 'vpg-v2' ), __( 'Thu', 'vpg-v2' ), __( 'Fri', 'vpg-v2' ), __( 'Sat', 'vpg-v2' ), __( 'Sun', 'vpg-v2' ) ];
    echo '<p class="description">' . esc_html__( '0042 · click cells to cycle: — / okay / great. Rendered as a heat table on the spot page.', 'vpg-v2' ) . '</p>';
    echo '<table class="vpg-bt" style="border-collapse:collapse"><tr><th></th>';
    foreach ( $slots as $sl ) echo '<th style="padding:4px 8px;font-size:11px">' . esc_html( $sl ) . '</th>';
    echo '</tr>';
    foreach ( $days as $di => $dl ) {
        echo '<tr><th style="padding:4px 8px;font-size:11px;text-align:right">' . esc_html( $dl ) . '</th>';
        foreach ( array_keys( $slots ) as $sk ) {
            $val = (int) ( $grid[ $di ][ $sk ] ?? 0 );
            echo '<td><button type="button" class="vpg-bt-cell" data-d="' . $di . '" data-s="' . esc_attr( $sk ) . '" data-v="' . $val . '" style="width:34px;height:26px;border:1px solid #c3c4c7;cursor:pointer;background:' . [ '#fff', '#ffe6b3', '#8ed6a3' ][ $val ] . '"></button>'
               . '<input type="hidden" name="vpg_bt[' . $di . '][' . esc_attr( $sk ) . ']" value="' . $val . '"></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    // 0058 audio note
    $aid = (int) get_post_meta( $post->ID, '_vpg_spot_audio', true );
    echo '<p style="margin-top:14px"><strong>' . esc_html__( '0058 · Audio note (30s voice memo)', 'vpg-v2' ) . '</strong></p>';
    echo '<input type="hidden" id="vpg-audio-id" name="vpg_spot_audio" value="' . $aid . '">';
    if ( $aid ) echo '<audio controls src="' . esc_url( wp_get_attachment_url( $aid ) ) . '" style="max-width:100%"></audio><br>';
    echo '<button type="button" class="button" id="vpg-audio-pick">' . esc_html__( 'Pick / upload audio', 'vpg-v2' ) . '</button>';

    // 0073 · winter/summer pair — two mandatory views for outdoor location spots
    if ( $post->post_type === 'vpg_location' ) {
        $wid = (int) get_post_meta( $post->ID, '_vpg_img_winter', true );
        $sid = (int) get_post_meta( $post->ID, '_vpg_img_summer', true );
        echo '<hr style="margin:16px 0"><p><strong>' . esc_html__( '0073 · Season pair (outdoor)', 'vpg-v2' ) . '</strong><br><span class="description">' . esc_html__( 'A winter and a summer view of the same spot — the light and mood change completely.', 'vpg-v2' ) . '</span></p>';
        echo '<div style="display:flex;gap:16px;flex-wrap:wrap">';
        foreach ( [ 'winter' => [ $wid, __( '❄ Winter view', 'vpg-v2' ) ], 'summer' => [ $sid, __( '☀ Summer view', 'vpg-v2' ) ] ] as $season => $pair ) {
            list( $iid, $lbl ) = $pair;
            echo '<div style="flex:1;min-width:150px"><label style="font-weight:600;display:block;margin-bottom:4px">' . esc_html( $lbl ) . '</label>';
            echo '<input type="hidden" class="vpg-season-id" data-season="' . esc_attr( $season ) . '" name="vpg_img_' . esc_attr( $season ) . '" value="' . $iid . '">';
            echo '<div class="vpg-season-prev" data-season="' . esc_attr( $season ) . '" style="min-height:60px;margin-bottom:6px">' . ( $iid ? '<img src="' . esc_url( wp_get_attachment_image_url( $iid, 'thumbnail' ) ) . '" style="max-width:100%;border:1px solid #dcdcde">' : '' ) . '</div>';
            echo '<button type="button" class="button button-small vpg-season-pick" data-season="' . esc_attr( $season ) . '">' . esc_html__( 'Choose image', 'vpg-v2' ) . '</button></div>';
        }
        echo '</div>';
    }

    // 0067 · shop hours sync from OpenStreetMap (one click)
    if ( $post->post_type === 'vpg_shop' ) {
        $lat = (float) get_post_meta( $post->ID, 'shop_lat', true );
        $lng = (float) get_post_meta( $post->ID, 'shop_lng', true );
        echo '<hr style="margin:16px 0"><p><strong>' . esc_html__( '0067 · Opening hours', 'vpg-v2' ) . '</strong></p>';
        if ( $lat && $lng ) {
            echo '<p><button type="button" class="button" id="vpg-hours-sync" data-lat="' . esc_attr( $lat ) . '" data-lng="' . esc_attr( $lng ) . '" data-nonce="' . esc_attr( wp_create_nonce( 'vpg_hours_sync' ) ) . '">' . esc_html__( '⟳ Fetch hours from OpenStreetMap', 'vpg-v2' ) . '</button> <span id="vpg-hours-out" style="font-size:12px;color:#6A6A6A"></span></p>';
            echo '<p class="description">' . esc_html__( 'Looks for a matching shop node near this pin and suggests its opening_hours for the Hours field.', 'vpg-v2' ) . '</p>';
        } else {
            echo '<p class="description">' . esc_html__( 'Set the shop coordinates first, then hours can be synced from OSM.', 'vpg-v2' ) . '</p>';
        }
    }
    ?>
    <script>
    (function(){
      document.querySelectorAll('.vpg-bt-cell').forEach(function(b){
        b.addEventListener('click',function(){
          var v=(parseInt(b.dataset.v,10)+1)%3; b.dataset.v=v;
          b.style.background=['#fff','#ffe6b3','#8ed6a3'][v];
          b.parentNode.querySelector('input').value=v;
        });
      });
      var ab=document.getElementById('vpg-audio-pick'),af=document.getElementById('vpg-audio-id');
      if(ab&&window.wp&&wp.media){ab.addEventListener('click',function(){
        var fr=wp.media({library:{type:'audio'},multiple:false});
        fr.on('select',function(){var a=fr.state().get('selection').first().toJSON();af.value=a.id;ab.insertAdjacentHTML('beforebegin','<audio controls src="'+a.url+'" style="max-width:100%;display:block;margin:6px 0"></audio>');});
        fr.open();
      });}
      /* 0073 · season pair pickers */
      document.querySelectorAll('.vpg-season-pick').forEach(function(btn){
        if(!(window.wp&&wp.media))return;
        btn.addEventListener('click',function(){
          var s=btn.dataset.season;
          var fr=wp.media({library:{type:'image'},multiple:false});
          fr.on('select',function(){
            var a=fr.state().get('selection').first().toJSON();
            document.querySelector('.vpg-season-id[data-season="'+s+'"]').value=a.id;
            var url=(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url);
            document.querySelector('.vpg-season-prev[data-season="'+s+'"]').innerHTML='<img src="'+url+'" style="max-width:100%;border:1px solid #dcdcde">';
          });
          fr.open();
        });
      });
      /* 0067 · fetch hours from OSM via admin-ajax */
      var hs=document.getElementById('vpg-hours-sync'),ho=document.getElementById('vpg-hours-out');
      if(hs){hs.addEventListener('click',function(){
        hs.disabled=true;ho.textContent='…';
        var fd=new FormData();fd.append('action','vpg_hours_sync');fd.append('_ajax_nonce',hs.dataset.nonce);fd.append('lat',hs.dataset.lat);fd.append('lng',hs.dataset.lng);
        fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
          hs.disabled=false;
          if(j.success&&j.data.hours){
            ho.innerHTML='';
            var f=document.querySelector('input[name="shop_hours"],textarea[name="shop_hours"],#shop_hours');
            if(f){f.value=j.data.hours;ho.textContent='✓ '+(j.data.name||'')+' → filled Hours field ('+j.data.hours+')';}
            else{ho.textContent='OSM: '+j.data.hours+' ('+(j.data.name||'')+')';}
          }else{ho.textContent=(j.data&&j.data.msg)||'No match near this pin.';}
        }).catch(function(){hs.disabled=false;ho.textContent='Network error.';});
      });}
    })();
    </script>
    <?php
}

add_action( 'save_post', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_spot_quality_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_spot_quality_nonce'], 'vpg_spot_quality' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $grid = [];
    foreach ( (array) ( $_POST['vpg_bt'] ?? [] ) as $d => $slots ) {
        foreach ( (array) $slots as $s => $v ) { $v = max( 0, min( 2, (int) $v ) ); if ( $v ) $grid[ (int) $d ][ sanitize_key( $s ) ] = $v; }
    }
    $grid ? update_post_meta( $post_id, '_vpg_besttime', $grid ) : delete_post_meta( $post_id, '_vpg_besttime' );
    $aid = (int) ( $_POST['vpg_spot_audio'] ?? 0 );
    $aid ? update_post_meta( $post_id, '_vpg_spot_audio', $aid ) : delete_post_meta( $post_id, '_vpg_spot_audio' );
    // 0073 · season pair
    foreach ( [ 'winter', 'summer' ] as $s ) {
        $iid = (int) ( $_POST[ 'vpg_img_' . $s ] ?? 0 );
        $iid ? update_post_meta( $post_id, '_vpg_img_' . $s, $iid ) : delete_post_meta( $post_id, '_vpg_img_' . $s );
    }
} );

add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && in_array( $s->post_type ?? '', VPG_SQ_TYPES, true ) ) wp_enqueue_media();
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0070 · Changelog · 0064 coord check · 0065 address normalise      */
/*  0052 example-image flag — all on save                            */
/* ════════════════════════════════════════════════════════════════ */
foreach ( VPG_SQ_TYPES as $cpt ) {
    add_action( 'save_post_' . $cpt, function ( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post_id ) ) return;

        // 0065 · normalise the district field to a bare 4-digit Vienna code where possible
        foreach ( [ 'location_district', 'shop_district' ] as $dk ) {
            $d = (string) get_post_meta( $post_id, $dk, true );
            if ( $d && preg_match( '/(1\d{2}0)/', $d, $m ) && $d !== $m[1] . ' · ' . vpg_district_name( $m[1] ) ) {
                update_post_meta( $post_id, $dk, $m[1] . ' · ' . vpg_district_name( $m[1] ) );
            }
        }

        // 0064 · pin far from the featured photo's own GPS?
        $tid = get_post_thumbnail_id( $post_id );
        $pre = $cpt === 'vpg_shop' ? 'shop' : ( $cpt === 'vpg_studio' ? 'studio' : 'location' );
        $lat = (float) get_post_meta( $post_id, $pre . '_lat', true );
        $lng = (float) get_post_meta( $post_id, $pre . '_lng', true );
        if ( $tid && $lat && function_exists( 'vpg_exif_latlng' ) ) {
            $geo = vpg_exif_latlng( get_attached_file( $tid ) );
            if ( $geo ) {
                $d = vpg_haversine_m( $lat, $lng, $geo[0], $geo[1] );
                $d > 50 ? update_post_meta( $post_id, '_vpg_coord_warn', (int) $d ) : delete_post_meta( $post_id, '_vpg_coord_warn' );
            }
        }

        // 0070 · a compact changelog
        $log = array_slice( (array) get_post_meta( $post_id, '_vpg_spot_log', true ), -19 );
        $log[] = [ 't' => time(), 'u' => get_current_user_id(), 'a' => $post->post_status ];
        update_post_meta( $post_id, '_vpg_spot_log', $log );

        delete_transient( 'vpg_location_pins_v4' );
    }, 20, 2 );
}

// 0052 · gentle example-image requirement (never blocks, just warns)
add_action( 'admin_notices', function () {
    $s = get_current_screen();
    if ( ! $s || ! in_array( $s->post_type ?? '', VPG_SQ_TYPES, true ) || $s->base !== 'post' ) return;
    global $post; if ( ! $post || get_post_thumbnail_id( $post->ID ) ) return;
    echo '<div class="notice notice-warning"><p>📷 ' . esc_html__( 'No example photo yet — a spot without a picture rarely helps anyone. Add a featured image.', 'vpg-v2' ) . '</p></div>';
} );

function vpg_haversine_m( $a, $b, $c, $d ) {
    $r = 6371000; $x = deg2rad( $c - $a ); $y = deg2rad( $d - $b );
    $s = sin( $x / 2 ) ** 2 + cos( deg2rad( $a ) ) * cos( deg2rad( $c ) ) * sin( $y / 2 ) ** 2;
    return 2 * $r * asin( min( 1, sqrt( $s ) ) );
}
function vpg_district_name( $code ) {
    $n = [ '1010'=>'Innere Stadt','1020'=>'Leopoldstadt','1030'=>'Landstraße','1040'=>'Wieden','1050'=>'Margareten','1060'=>'Mariahilf','1070'=>'Neubau','1080'=>'Josefstadt','1090'=>'Alsergrund','1100'=>'Favoriten','1110'=>'Simmering','1120'=>'Meidling','1130'=>'Hietzing','1140'=>'Penzing','1150'=>'Rudolfsheim-Fünfhaus','1160'=>'Ottakring','1170'=>'Hernals','1180'=>'Währing','1190'=>'Döbling','1200'=>'Brigittenau','1210'=>'Floridsdorf','1220'=>'Donaustadt','1230'=>'Liesing' ];
    return $n[ $code ] ?? '';
}

/* ════════════════════════════════════════════════════════════════ */
/*  0055 voting · 0046 verification · 0062 paten — member actions     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_spot_action', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_spot_action' );
    $id  = (int) ( $_POST['spot'] ?? 0 );
    $act = sanitize_key( $_POST['act'] ?? '' );
    $uid = get_current_user_id();
    if ( in_array( get_post_type( $id ), VPG_SQ_TYPES, true ) && get_post_status( $id ) === 'publish' ) {
        if ( $act === 'vote' ) {                                    // 0055
            $v = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_vpg_spot_votes', true ) ) );
            in_array( $uid, $v, true ) ? $v = array_diff( $v, [ $uid ] ) : $v[] = $uid;
            update_post_meta( $id, '_vpg_spot_votes', array_values( $v ) );
        } elseif ( $act === 'verify' ) {                            // 0046
            $v = array_filter( array_map( 'intval', (array) get_post_meta( $id, '_vpg_verified_by', true ) ) );
            if ( ! in_array( $uid, $v, true ) ) { $v[] = $uid; update_post_meta( $id, '_vpg_verified_by', array_values( $v ) ); }
        } elseif ( $act === 'adopt' && current_user_can( 'edit_others_posts' ) === false ) {  // 0062 · members adopt
            get_post_meta( $id, '_vpg_paten', true ) ? null : update_post_meta( $id, '_vpg_paten', $uid );
        } elseif ( $act === 'adopt' ) {
            update_post_meta( $id, '_vpg_paten', $uid );
        }
    }
    wp_safe_redirect( get_permalink( $id ) ?: home_url() );
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Front-of-house · everything rendered under the spot content       */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( VPG_SQ_TYPES ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $id = get_the_ID(); $uid = get_current_user_id();
    ob_start();

    // 0059 · colour character (from featured image)
    $tid = get_post_thumbnail_id( $id );
    $col = $tid ? get_post_meta( $tid, '_vpg_color', true ) : '';
    if ( ! $col && $tid && function_exists( 'vpg_image_avg_color' ) ) {
        $rgb = vpg_image_avg_color( get_attached_file( $tid ) );
        if ( $rgb ) { $col = vsprintf( '#%02x%02x%02x', $rgb ); update_post_meta( $tid, '_vpg_color', $col ); }
    }

    // 0071 completeness · 0055 votes · 0046 verify · 0062 paten
    $cmp = vpg_spot_completeness( $id );
    $votes = array_filter( (array) get_post_meta( $id, '_vpg_spot_votes', true ) );
    $verif = array_filter( (array) get_post_meta( $id, '_vpg_verified_by', true ) );
    $paten = (int) get_post_meta( $id, '_vpg_paten', true );
    $warn  = (int) get_post_meta( $id, '_vpg_coord_warn', true );
    ?>
    <div style="border-top:1px solid var(--g-line);margin-top:20px;padding-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <?php if ( $col ) : ?><a href="<?php echo esc_url( home_url( '/farbe/' . ltrim( $col, '#' ) . '/' ) ); ?>" style="display:inline-flex;gap:7px;align-items:center;text-decoration:none;font-size:12px;font-weight:700"><span style="width:15px;height:15px;background:<?php echo esc_attr( $col ); ?>;border:1px solid var(--g-line)"></span><?php esc_html_e( 'Colour character', 'vpg-v2' ); ?></a><?php endif; ?>
      <span style="font-size:12px;font-weight:700;color:<?php echo $cmp['score'] >= 70 ? 'var(--g-mid)' : 'var(--g-red)'; ?>">◕ <?php printf( esc_html__( '%d%% complete', 'vpg-v2' ), $cmp['score'] ); ?></span>
      <?php if ( count( $verif ) >= 2 ) : ?><span style="font-size:12px;font-weight:700;color:#1A7A3C">✓ <?php printf( esc_html( _n( 'verified by %d member', 'verified by %d members', count( $verif ), 'vpg-v2' ) ), count( $verif ) ); ?></span><?php endif; ?>
      <?php if ( $votes ) : ?><span style="font-size:12px;font-weight:700">♥ <?php echo count( $votes ); ?></span><?php endif; ?>
      <?php if ( $paten && ( $pu = get_userdata( $paten ) ) ) : ?><span style="font-size:12px;color:var(--g-mid)">🛡 <?php printf( esc_html__( 'kept current by %s', 'vpg-v2' ), esc_html( $pu->display_name ) ); ?></span><?php endif; ?>
      <?php if ( $warn && current_user_can( 'edit_others_posts' ) ) : ?><span style="font-size:12px;color:var(--g-red)">⚠ <?php printf( esc_html__( 'pin %d m from photo GPS', 'vpg-v2' ), $warn ); ?></span><?php endif; ?>
    </div>

    <?php if ( $uid ) : ?>
    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
      <?php foreach ( [ 'vote' => in_array( $uid, $votes, true ) ? esc_html__( '♥ Useful ✓', 'vpg-v2' ) : esc_html__( '♡ Useful', 'vpg-v2' ),
                        'verify' => in_array( $uid, $verif, true ) ? esc_html__( '✓ Verified', 'vpg-v2' ) : esc_html__( '⊙ I was there — verify', 'vpg-v2' ),
                        'adopt' => $paten === $uid ? esc_html__( '🛡 You keep this current', 'vpg-v2' ) : esc_html__( '🛡 Adopt this pin', 'vpg-v2' ) ] as $act => $label ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
          <?php wp_nonce_field( 'vpg_spot_action' ); ?>
          <input type="hidden" name="action" value="vpg_spot_action">
          <input type="hidden" name="spot" value="<?php echo (int) $id; ?>">
          <input type="hidden" name="act" value="<?php echo esc_attr( $act ); ?>">
          <button type="submit" style="background:none;border:1px solid var(--g-line);padding:6px 12px;font-size:11px;font-weight:700;cursor:pointer"><?php echo $label; ?></button>
        </form>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php // 0058 audio note
    $aid = (int) get_post_meta( $id, '_vpg_spot_audio', true );
    if ( $aid ) : ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'A word from whoever found it', 'vpg-v2' ); ?></p>
      <audio controls preload="none" src="<?php echo esc_url( wp_get_attachment_url( $aid ) ); ?>" style="width:100%;max-width:420px"></audio>
    <?php endif; ?>

    <?php // 0042 best-time matrix
    $grid = (array) get_post_meta( $id, '_vpg_besttime', true );
    if ( $grid ) :
      $slots = [ 'dawn' => __( 'Dawn', 'vpg-v2' ), 'day' => __( 'Day', 'vpg-v2' ), 'dusk' => __( 'Dusk', 'vpg-v2' ), 'night' => __( 'Night', 'vpg-v2' ) ];
      $days  = [ 'Mo','Tu','We','Th','Fr','Sa','Su' ]; ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'Best time', 'vpg-v2' ); ?></p>
      <table style="border-collapse:collapse;font-size:11px"><tr><th></th><?php foreach ( $slots as $sl ) echo '<th style="padding:2px 8px;font-weight:700">' . esc_html( $sl ) . '</th>'; ?></tr>
      <?php foreach ( $days as $di => $dl ) : ?><tr><th style="padding:2px 8px;text-align:right;font-weight:700"><?php echo esc_html( $dl ); ?></th>
        <?php foreach ( array_keys( $slots ) as $sk ) { $v = (int) ( $grid[ $di ][ $sk ] ?? 0 ); echo '<td style="width:30px;height:20px;background:' . [ 'transparent','#ffe6b3','#8ed6a3' ][ $v ] . ';border:1px solid var(--g-line)"></td>'; } ?>
      </tr><?php endforeach; ?></table>
    <?php endif; ?>

    <?php // 0073 season pair — winter / summer view of the same spot
    $win = (int) get_post_meta( $id, '_vpg_img_winter', true );
    $sum = (int) get_post_meta( $id, '_vpg_img_summer', true );
    if ( $win && $sum ) : ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'The same spot, two seasons', 'vpg-v2' ); ?></p>
      <div class="g-grid2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <figure style="margin:0"><?php echo wp_get_attachment_image( $win, 'large', false, [ 'style' => 'width:100%;height:auto;display:block' ] ); ?><figcaption style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--g-mid);margin-top:4px">❄ <?php esc_html_e( 'Winter', 'vpg-v2' ); ?></figcaption></figure>
        <figure style="margin:0"><?php echo wp_get_attachment_image( $sum, 'large', false, [ 'style' => 'width:100%;height:auto;display:block' ] ); ?><figcaption style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--g-mid);margin-top:4px">☀ <?php esc_html_e( 'Summer', 'vpg-v2' ); ?></figcaption></figure>
      </div>
    <?php elseif ( ( $win || $sum ) && current_user_can( 'edit_post', $id ) ) : ?>
      <p style="font-size:12px;color:var(--g-red);margin-top:16px">⚠ <?php esc_html_e( 'Season pair incomplete — add the missing winter or summer view.', 'vpg-v2' ); ?></p>
    <?php endif; ?>

    <?php // 0053 EXIF summary from the spot's own images
    $exif = vpg_spot_exif_summary( $id );
    if ( $exif ) : ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'What members shot here', 'vpg-v2' ); ?></p>
      <p style="font-size:13px;color:var(--g-mid)"><?php echo esc_html( $exif ); ?></p>
    <?php endif; ?>

    <?php // 0078 neighbour districts
    $code = '';
    if ( preg_match( '/(1\d{2}0)/', (string) ( get_post_meta( $id, 'location_district', true ) ?: get_post_meta( $id, 'shop_district', true ) ), $mm ) ) $code = $mm[1];
    $nb = $code ? vpg_district_neighbours( $code ) : [];
    if ( $nb ) : ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'Adjacent districts', 'vpg-v2' ); ?></p>
      <p style="display:flex;gap:10px;flex-wrap:wrap"><?php foreach ( $nb as $nc ) : ?><a href="<?php echo esc_url( home_url( '/bezirk/' . $nc . '/' ) ); ?>" style="font-size:12px;font-weight:700;border:1px solid var(--g-line);padding:4px 10px;text-decoration:none"><?php echo esc_html( $nc . ' · ' . vpg_district_name( $nc ) ); ?></a><?php endforeach; ?></p>
    <?php endif; ?>

    <?php // 0075 similar spots
    $sim = vpg_similar_spots( $id, 3 );
    if ( $sim ) : ?>
      <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'If you like this spot', 'vpg-v2' ); ?></p>
      <div class="g-grid3"><?php foreach ( $sim as $sid ) : ?>
        <a class="g-card" href="<?php echo esc_url( get_permalink( $sid ) ); ?>">
          <?php if ( has_post_thumbnail( $sid ) ) : ?><div class="g-fig g-fig--3x2"><?php echo get_the_post_thumbnail( $sid, 'medium_large' ); ?></div><?php endif; ?>
          <h3 class="g-card__title"><?php echo esc_html( get_the_title( $sid ) ); ?></h3>
        </a>
      <?php endforeach; ?></div>
    <?php endif; ?>

    <?php // 0070 changelog (editors)
    if ( current_user_can( 'edit_others_posts' ) ) :
      $log = array_slice( (array) get_post_meta( $id, '_vpg_spot_log', true ), -5 );
      if ( $log ) : ?>
        <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'Changelog', 'vpg-v2' ); ?></p>
        <ul style="font-size:11px;color:var(--g-mid);margin:0;padding-left:16px">
          <?php foreach ( array_reverse( $log ) as $e ) : $eu = get_userdata( (int) ( $e['u'] ?? 0 ) ); ?>
            <li><?php echo esc_html( wp_date( 'j M Y H:i', (int) ( $e['t'] ?? 0 ) ) . ' · ' . ( $eu ? $eu->display_name : '—' ) . ' · ' . ( $e['a'] ?? '' ) ); ?></li>
          <?php endforeach; ?>
        </ul>
    <?php endif; endif; ?>
    <?php
    return $content . ob_get_clean();
}, 26 );

/* 0072 · the image pool for map-popup rotation (featured + season pair +
   up to 3 attached images), medium size, deduped, capped at 5. */
function vpg_spot_gallery_urls( $id ) {
    $ids = [ get_post_thumbnail_id( $id ), (int) get_post_meta( $id, '_vpg_img_winter', true ), (int) get_post_meta( $id, '_vpg_img_summer', true ) ];
    $ids = array_merge( $ids, get_posts( [ 'post_type' => 'attachment', 'post_parent' => $id, 'post_mime_type' => 'image', 'posts_per_page' => 3, 'fields' => 'ids' ] ) );
    $urls = [];
    foreach ( array_unique( array_filter( $ids ) ) as $aid ) {
        $u = wp_get_attachment_image_url( $aid, 'medium' );
        if ( $u ) $urls[] = $u;
    }
    return array_slice( $urls, 0, 5 );
}

/* 0053 · aggregate EXIF from a spot's featured + attached images */
function vpg_spot_exif_summary( $id ) {
    $ids = array_filter( array_merge( [ get_post_thumbnail_id( $id ) ], get_posts( [ 'post_type' => 'attachment', 'post_parent' => $id, 'post_mime_type' => 'image', 'posts_per_page' => 20, 'fields' => 'ids' ] ) ) );
    if ( ! $ids ) return '';
    $ap = []; $fl = []; $n = 0;
    foreach ( $ids as $aid ) {
        $m = wp_get_attachment_metadata( $aid )['image_meta'] ?? [];
        if ( ! empty( $m['aperture'] ) ) { $ap[] = (float) $m['aperture']; }
        if ( ! empty( $m['focal_length'] ) ) { $fl[] = (int) round( (float) $m['focal_length'] ); }
        $n++;
    }
    $parts = [];
    if ( $ap ) { sort( $ap ); $parts[] = 'ƒ/' . $ap[ (int) floor( count( $ap ) / 2 ) ]; }
    if ( $fl ) { $c = array_count_values( $fl ); arsort( $c ); $parts[] = array_key_first( $c ) . 'mm'; }
    return $parts ? sprintf( __( 'Often shot around %s (from %d member photos).', 'vpg-v2' ), implode( ' · ', $parts ), $n ) : '';
}

/* 0075 · spots sharing genres or themes */
function vpg_similar_spots( $id, $limit = 3 ) {
    $a = function_exists( 'vpg_spot_attrs' ) ? vpg_spot_attrs( $id ) : [];
    $tags = array_merge( (array) ( $a['genres'] ?? [] ), (array) ( $a['themes'] ?? [] ) );
    if ( ! $tags ) return [];
    $cands = get_posts( [ 'post_type' => VPG_SQ_TYPES, 'post_status' => 'publish', 'posts_per_page' => 120, 'post__not_in' => [ $id ], 'fields' => 'ids' ] );
    $scored = [];
    foreach ( $cands as $cid ) {
        $ca = vpg_spot_attrs( $cid );
        $ct = array_merge( (array) ( $ca['genres'] ?? [] ), (array) ( $ca['themes'] ?? [] ) );
        $overlap = count( array_intersect( $tags, $ct ) );
        if ( $overlap ) $scored[ $cid ] = $overlap;
    }
    arsort( $scored );
    return array_slice( array_keys( $scored ), 0, $limit );
}

/* 0078 · a small adjacency map of Vienna's 23 districts */
function vpg_district_neighbours( $code ) {
    $adj = [
        '1010'=>['1020','1030','1040','1060','1070','1080','1090'], '1020'=>['1010','1200','1220'],
        '1030'=>['1010','1040','1110'], '1040'=>['1010','1030','1050','1060'], '1050'=>['1040','1060','1100','1120'],
        '1060'=>['1010','1040','1050','1070','1150'], '1070'=>['1010','1060','1080','1150','1160'],
        '1080'=>['1010','1070','1090','1160','1170'], '1090'=>['1010','1080','1180','1190','1200'],
        '1100'=>['1050','1110','1120'], '1110'=>['1030','1100'], '1120'=>['1050','1100','1130','1140','1150'],
        '1130'=>['1120','1140'], '1140'=>['1120','1130','1150','1160'], '1150'=>['1060','1070','1120','1140','1160'],
        '1160'=>['1070','1080','1140','1150','1170'], '1170'=>['1080','1160','1180'], '1180'=>['1090','1170','1190'],
        '1190'=>['1090','1180'], '1200'=>['1020','1090','1210'], '1210'=>['1200','1220'], '1220'=>['1020','1210'], '1230'=>['1120','1130'],
    ];
    return $adj[ $code ] ?? [];
}

/* ════════════════════════════════════════════════════════════════ */
/*  0077 · donation hint on the submit form                           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', function () {
    if ( ! is_page_template( 'templates/page-submit.php' ) ) return;
    ?>
    <script>
    (function(){
      var box=document.querySelector('[data-for-types="vpg_location vpg_studio vpg_shop"]');
      if(!box||document.getElementById('vpg-donate-hint'))return;
      var p=document.createElement('p');p.id='vpg-donate-hint';
      p.style.cssText='font-size:12px;color:var(--g-mid,#6A6A6A);border-left:3px solid var(--g-red,#E5341F);padding:6px 0 6px 12px;margin:10px 0';
      p.textContent=<?php echo wp_json_encode( __( 'What helps most: an exact pin, a photo from the spot, the best time and light, and honest access notes — tripod, entry, safety.', 'vpg-v2' ) ); ?>;
      box.parentNode.insertBefore(p,box);
    })();
    </script>
    <?php
}, 21 );

/* ════════════════════════════════════════════════════════════════ */
/*  0080 quality dashboard · 0054 merge · 0056 decay · 0063 import     */
/*  0067 hours sync · 0069 contact check — the editorial desk         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_location', __( 'Quality desk', 'vpg-v2' ), '◕ ' . __( 'Quality desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-quality', 'vpg_quality_desk_page' );
}, 20 );

function vpg_quality_desk_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    // 0063 · mass import (GeoJSON or "title,lat,lng" CSV) → pending drafts
    if ( isset( $_POST['vpg_import'] ) && check_admin_referer( 'vpg_quality' ) ) {
        $raw = trim( wp_unslash( $_POST['vpg_import'] ) );
        $made = 0; $rows = [];
        $j = json_decode( $raw, true );
        if ( is_array( $j ) && ! empty( $j['features'] ) ) {
            foreach ( $j['features'] as $f ) {
                $c = $f['geometry']['coordinates'] ?? null;
                if ( $c ) $rows[] = [ $f['properties']['title'] ?? $f['properties']['name'] ?? 'Imported spot', (float) $c[1], (float) $c[0] ];
            }
        } else {
            foreach ( preg_split( '/\r?\n/', $raw ) as $line ) {
                $p = str_getcsv( $line );
                if ( count( $p ) >= 3 && is_numeric( $p[1] ) ) $rows[] = [ trim( $p[0] ), (float) $p[1], (float) $p[2] ];
            }
        }
        foreach ( array_slice( $rows, 0, 200 ) as $r ) {
            if ( $r[1] < 47 || $r[1] > 49 ) continue;
            $pid = wp_insert_post( [ 'post_type' => 'vpg_location', 'post_status' => 'pending', 'post_title' => sanitize_text_field( $r[0] ) ] );
            if ( $pid && ! is_wp_error( $pid ) ) { update_post_meta( $pid, 'location_lat', round( $r[1], 6 ) ); update_post_meta( $pid, 'location_lng', round( $r[2], 6 ) ); update_post_meta( $pid, '_vpg_imported', 1 ); $made++; }
        }
        echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( '%d spots imported as pending drafts for review.', 'vpg-v2' ), $made ) ) . '</p></div>';
        delete_transient( 'vpg_location_pins_v4' );
    }

    $all = get_posts( [ 'post_type' => VPG_SQ_TYPES, 'post_status' => 'publish', 'posts_per_page' => 500 ] );
    $scored = [];
    foreach ( $all as $p ) $scored[ $p->ID ] = vpg_spot_completeness( $p->ID )['score'];
    asort( $scored );
    $weakest = array_slice( $scored, 0, 20, true );

    // 0056 · decay — no update / no confirm in 18 months
    $stale = [];
    foreach ( $all as $p ) {
        $last = max( strtotime( $p->post_modified_gmt ), (int) get_post_meta( $p->ID, '_vpg_checked_at', true ) );
        if ( $last && $last < strtotime( '-18 months' ) ) $stale[ $p->ID ] = $last;
    }
    asort( $stale );

    // 0054 · duplicate candidates (same type within 40 m)
    $dupes = [];
    $pts = [];
    foreach ( $all as $p ) {
        $pre = $p->post_type === 'vpg_shop' ? 'shop' : ( $p->post_type === 'vpg_studio' ? 'studio' : 'location' );
        $la = (float) get_post_meta( $p->ID, $pre . '_lat', true ); $lo = (float) get_post_meta( $p->ID, $pre . '_lng', true );
        if ( $la ) $pts[] = [ $p->ID, $p->post_type, $la, $lo, get_the_title( $p ) ];
    }
    for ( $i = 0; $i < count( $pts ); $i++ ) for ( $k = $i + 1; $k < count( $pts ); $k++ ) {
        if ( $pts[$i][1] === $pts[$k][1] && vpg_haversine_m( $pts[$i][2], $pts[$i][3], $pts[$k][2], $pts[$k][3] ) < 40 ) {
            $dupes[] = [ $pts[$i], $pts[$k] ];
        }
    }
    ?>
    <div class="wrap">
      <h1>◕ <?php esc_html_e( 'Quality desk', 'vpg-v2' ); ?></h1>

      <h2><?php esc_html_e( 'Weakest 20 pins (complete them first)', 'vpg-v2' ); ?></h2>
      <table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Spot', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Score', 'vpg-v2' ); ?></th><th></th></tr></thead><tbody>
        <?php foreach ( $weakest as $pid => $sc ) : ?>
          <tr><td><?php echo esc_html( get_the_title( $pid ) ); ?></td><td><strong style="color:<?php echo $sc < 50 ? '#d63638' : '#996800'; ?>"><?php echo (int) $sc; ?>%</strong></td><td><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php esc_html_e( 'Edit', 'vpg-v2' ); ?></a></td></tr>
        <?php endforeach; ?>
      </tbody></table>

      <h2 style="margin-top:24px"><?php esc_html_e( 'Stale — not touched in 18 months', 'vpg-v2' ); ?></h2>
      <?php if ( ! $stale ) : ?><p><?php esc_html_e( 'Nothing stale. The index is fresh.', 'vpg-v2' ); ?></p><?php else : ?>
      <ul><?php foreach ( $stale as $pid => $t ) : ?><li><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a> — <?php echo esc_html( wp_date( 'M Y', $t ) ); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <h2 style="margin-top:24px"><?php esc_html_e( 'Possible duplicates (same type, <40 m)', 'vpg-v2' ); ?></h2>
      <?php if ( ! $dupes ) : ?><p><?php esc_html_e( 'No obvious duplicates.', 'vpg-v2' ); ?></p><?php else : ?>
      <ul><?php foreach ( array_slice( $dupes, 0, 30 ) as $d ) : ?>
        <li><a href="<?php echo esc_url( get_edit_post_link( $d[0][0] ) ); ?>"><?php echo esc_html( $d[0][4] ); ?></a> ↔ <a href="<?php echo esc_url( get_edit_post_link( $d[1][0] ) ); ?>"><?php echo esc_html( $d[1][4] ); ?></a>
          — <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_merge_spot&keep=' . $d[0][0] . '&drop=' . $d[1][0] ), 'vpg_merge_spot' ) ); ?>" onclick="return confirm('Merge?')"><?php esc_html_e( 'merge → keep first', 'vpg-v2' ); ?></a></li>
      <?php endforeach; ?></ul>
      <?php endif; ?>

      <?php // 0069 · dead contact links from the weekly sweep
      $linkbad = [];
      foreach ( $all as $p ) { $d = (array) get_post_meta( $p->ID, '_vpg_dead_links', true ); if ( $d ) $linkbad[ $p->ID ] = $d; } ?>
      <h2 style="margin-top:24px"><?php esc_html_e( 'Contact links to fix', 'vpg-v2' ); ?></h2>
      <?php if ( ! $linkbad ) : ?><p><?php esc_html_e( 'No dead links found in the last sweep.', 'vpg-v2' ); ?></p><?php else : ?>
      <ul><?php foreach ( $linkbad as $pid => $dl ) : ?><li><a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a> — <?php echo esc_html( implode( ', ', array_map( function ( $x ) { return $x['u'] . ' (' . ( $x['c'] ?: 'no resp.' ) . ')'; }, array_slice( $dl, 0, 4 ) ) ) ); ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <h2 style="margin-top:24px"><?php esc_html_e( 'Mass import', 'vpg-v2' ); ?></h2>
      <form method="post"><?php wp_nonce_field( 'vpg_quality' ); ?>
        <p class="description"><?php esc_html_e( 'Paste GeoJSON (FeatureCollection) or CSV lines "Title,lat,lng". Creates pending location drafts for review. Max 200.', 'vpg-v2' ); ?></p>
        <textarea name="vpg_import" rows="6" style="width:100%;max-width:760px;font-family:monospace"></textarea>
        <p><button class="button button-primary"><?php esc_html_e( 'Import as pending', 'vpg-v2' ); ?></button></p>
      </form>
    </div>
    <?php
}

/* 0054 · merge · redirect the dropped pin to the kept one */
add_action( 'admin_post_vpg_merge_spot', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_merge_spot' );
    $keep = (int) ( $_GET['keep'] ?? 0 ); $drop = (int) ( $_GET['drop'] ?? 0 );
    if ( $keep && $drop && in_array( get_post_type( $keep ), VPG_SQ_TYPES, true ) && get_post_type( $drop ) === get_post_type( $keep ) ) {
        // carry the dropped pin's changelog + votes note, then trash + 301
        update_post_meta( $keep, '_vpg_merged_from', array_merge( (array) get_post_meta( $keep, '_vpg_merged_from', true ), [ get_the_title( $drop ) ] ) );
        update_post_meta( $drop, '_vpg_redirect_to', get_permalink( $keep ) );
        wp_trash_post( $drop );
        delete_transient( 'vpg_location_pins_v4' );
    }
    wp_safe_redirect( admin_url( 'edit.php?post_type=vpg_location&page=vpg-quality' ) );
    exit;
} );

/* honour a merge redirect (301) */
add_action( 'template_redirect', function () {
    if ( is_singular( VPG_SQ_TYPES ) ) {
        $to = get_post_meta( get_the_ID(), '_vpg_redirect_to', true );
        if ( $to ) { wp_redirect( $to, 301 ); exit; }
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0066 · District profiles — editable character text per Bezirk     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_location', __( 'District profiles', 'vpg-v2' ), '📍 ' . __( 'District profiles', 'vpg-v2' ), 'edit_others_posts', 'vpg-districts', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        $texts = (array) get_option( 'vpg_district_texts', [] );
        if ( isset( $_POST['vpg_dt'] ) && check_admin_referer( 'vpg_district_texts' ) ) {
            $clean = [];
            foreach ( (array) $_POST['vpg_dt'] as $code => $txt ) {
                $code = preg_replace( '/\D/', '', $code );
                if ( vpg_district_name( $code ) && trim( $txt ) !== '' ) $clean[ $code ] = sanitize_textarea_field( wp_unslash( $txt ) );
            }
            update_option( 'vpg_district_texts', $clean, false );
            $texts = $clean;
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        echo '<div class="wrap"><h1>📍 ' . esc_html__( 'District profiles', 'vpg-v2' ) . '</h1><p class="description">' . esc_html__( 'A short character text per district, shown on its /bezirk/ landing page.', 'vpg-v2' ) . '</p><form method="post">';
        wp_nonce_field( 'vpg_district_texts' );
        foreach ( [ '1010','1020','1030','1040','1050','1060','1070','1080','1090','1100','1110','1120','1130','1140','1150','1160','1170','1180','1190','1200','1210','1220','1230' ] as $code ) {
            echo '<p><label style="font-weight:600">' . esc_html( $code . ' · ' . vpg_district_name( $code ) ) . '</label><br><textarea name="vpg_dt[' . esc_attr( $code ) . ']" rows="2" style="width:100%;max-width:720px">' . esc_textarea( $texts[ $code ] ?? '' ) . '</textarea></p>';
        }
        echo '<p><button class="button button-primary">' . esc_html__( 'Save profiles', 'vpg-v2' ) . '</button></p></form></div>';
    } );
}, 21 );

// expose the district text so the /bezirk/ page can render it (0066)
function vpg_district_text( $code ) {
    $t = (array) get_option( 'vpg_district_texts', [] );
    return $t[ $code ] ?? '';
}

/* ════════════════════════════════════════════════════════════════ */
/*  0067 · Opening-hours sync — query OSM Overpass near a shop pin     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_vpg_hours_sync', function () {
    check_ajax_referer( 'vpg_hours_sync' );
    if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( [ 'msg' => 'Forbidden' ], 403 );
    $lat = round( (float) ( $_POST['lat'] ?? 0 ), 6 );
    $lng = round( (float) ( $_POST['lng'] ?? 0 ), 6 );
    if ( $lat < 47 || $lat > 49 ) wp_send_json_error( [ 'msg' => 'Bad coordinates.' ] );

    // ~60 m radius, only nodes/ways that actually carry opening_hours
    $ql = '[out:json][timeout:12];(node(around:60,' . $lat . ',' . $lng . ')["opening_hours"];way(around:60,' . $lat . ',' . $lng . ')["opening_hours"];);out center 8;';
    $res = wp_remote_post( 'https://overpass-api.de/api/interpreter', [
        'timeout' => 15,
        'body'    => [ 'data' => $ql ],
        'headers' => [ 'Accept' => 'application/json' ],
    ] );
    if ( is_wp_error( $res ) ) wp_send_json_error( [ 'msg' => $res->get_error_message() ] );
    $data = json_decode( wp_remote_retrieve_body( $res ), true );
    $els  = $data['elements'] ?? [];
    if ( ! $els ) wp_send_json_error( [ 'msg' => 'No OSM node with hours near this pin.' ] );

    // nearest element wins
    usort( $els, function ( $a, $b ) use ( $lat, $lng ) {
        $ax = $a['lat'] ?? ( $a['center']['lat'] ?? 0 ); $ay = $a['lon'] ?? ( $a['center']['lon'] ?? 0 );
        $bx = $b['lat'] ?? ( $b['center']['lat'] ?? 0 ); $by = $b['lon'] ?? ( $b['center']['lon'] ?? 0 );
        return vpg_haversine_m( $lat, $lng, $ax, $ay ) <=> vpg_haversine_m( $lat, $lng, $bx, $by );
    } );
    $top = $els[0];
    wp_send_json_success( [
        'hours' => sanitize_text_field( $top['tags']['opening_hours'] ?? '' ),
        'name'  => sanitize_text_field( $top['tags']['name'] ?? '' ),
    ] );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0069 · Contact verification — weekly sweep for dead links         */
/*  Scans each spot's body for http links, HEAD-checks them, and       */
/*  flags the dead ones in _vpg_dead_links for the editorial desk.     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'vpg_contact_check', 'vpg_run_contact_check' );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_contact_check' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', 'vpg_contact_check' );
    }
} );
add_filter( 'cron_schedules', function ( $s ) {
    if ( empty( $s['weekly'] ) ) $s['weekly'] = [ 'interval' => WEEK_IN_SECONDS, 'display' => 'Once weekly' ];
    return $s;
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_contact_check' ); } );

function vpg_run_contact_check() {
    $spots = get_posts( [ 'post_type' => VPG_SQ_TYPES, 'post_status' => 'publish', 'posts_per_page' => 120, 'orderby' => 'modified', 'order' => 'ASC' ] );
    foreach ( $spots as $p ) {
        // only re-check a spot at most every ~6 days
        if ( (int) get_post_meta( $p->ID, '_vpg_links_checked', true ) > time() - 6 * DAY_IN_SECONDS ) continue;
        preg_match_all( '#https?://[^\s"\'<>)]+#i', $p->post_content, $m );
        $urls = array_slice( array_unique( $m[0] ), 0, 8 );
        $dead = [];
        foreach ( $urls as $u ) {
            $r = wp_remote_head( $u, [ 'timeout' => 8, 'redirection' => 3, 'user-agent' => 'VPG-linkcheck/1.0' ] );
            $code = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
            // some servers reject HEAD → confirm with a light GET before condemning
            if ( $code === 0 || $code === 405 || $code >= 400 ) {
                $r2 = wp_remote_get( $u, [ 'timeout' => 8, 'redirection' => 3, 'user-agent' => 'VPG-linkcheck/1.0' ] );
                $code = is_wp_error( $r2 ) ? 0 : (int) wp_remote_retrieve_response_code( $r2 );
            }
            if ( $code === 0 || $code >= 400 ) $dead[] = [ 'u' => $u, 'c' => $code ];
        }
        $dead ? update_post_meta( $p->ID, '_vpg_dead_links', $dead ) : delete_post_meta( $p->ID, '_vpg_dead_links' );
        update_post_meta( $p->ID, '_vpg_links_checked', time() );
    }
}

/* Dead-link warning in the editor + on the spot page for editors (0069) */
add_action( 'admin_notices', function () {
    $s = get_current_screen();
    if ( ! $s || ! in_array( $s->post_type ?? '', VPG_SQ_TYPES, true ) || $s->base !== 'post' ) return;
    $dead = (array) get_post_meta( (int) ( $_GET['post'] ?? 0 ), '_vpg_dead_links', true );
    if ( ! $dead ) return;
    echo '<div class="notice notice-warning"><p><strong>' . esc_html__( '0069 · Contact check:', 'vpg-v2' ) . '</strong> ' . esc_html( sprintf( _n( '%d link looks dead.', '%d links look dead.', count( $dead ), 'vpg-v2' ), count( $dead ) ) ) . '</p><ul style="margin:0 0 6px 18px;list-style:disc">';
    foreach ( array_slice( $dead, 0, 8 ) as $d ) echo '<li><code>' . esc_html( $d['u'] ) . '</code> — ' . esc_html( $d['c'] ? 'HTTP ' . $d['c'] : 'no response' ) . '</li>';
    echo '</ul></div>';
} );
