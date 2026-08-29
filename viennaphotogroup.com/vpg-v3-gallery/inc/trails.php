<?php
/**
 * VPG v3 — Cluster 03 · Trails & Routen.
 *
 * Builds on the existing vpg_trail CPT (stops, difficulty, vpg_trail_geo,
 * GPX export) and turns a route into a full photowalk format: rich
 * attributes, stages & rest stops, a stat bar, photo album, start-time
 * optimiser, weather window, live mode, printable A6 heft + QR, bingo card,
 * cover generator, member walk/check-in/remix/feedback, duets, series,
 * archiving, guest routes and trail of the month.
 *
 *   0082 gain · 0084 stages · 0085 direction · 0086 rest stops
 *   0088 night · 0089 rain · 0090 cover · 0091 walked · 0092 check-ins
 *   0093 album · 0094 season · 0095 stroller · 0096 transit · 0097 duets
 *   0098 blind · 0099 one-lens · 0100 bingo · 0101 audio · 0102 of-the-month
 *   0103 guest · 0104 stat bar · 0105 QR start · 0106 live · 0107 remix
 *   0108 barriers · 0109 weather window · 0110 missed stops · 0111 series
 *   0112 film limit · 0113 sponsor-free · 0114 meetup · 0115 feedback
 *   0116 start-time · 0117 winter · 0118 print heft · 0119 archiving
 *   0120 no ranking
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Attribute schema — the curated trail format fields               */
/* ════════════════════════════════════════════════════════════════ */
function vpg_trail_attr_schema() {
    return apply_filters( 'vpg_trail_attr_schema', [
        'night'    => [ 'type' => 'bool',   'label' => __( 'Night trail', 'vpg-v2' ),       'icon' => '🌙', 'hint' => __( 'Built for long exposures after dark.', 'vpg-v2' ) ],      // 0088
        'rain'     => [ 'type' => 'bool',   'label' => __( 'Rain-proof', 'vpg-v2' ),        'icon' => '☔', 'hint' => __( 'Covered passages & museums — a bad-weather route.', 'vpg-v2' ) ], // 0089
        'stroller' => [ 'type' => 'bool',   'label' => __( 'Stroller-friendly', 'vpg-v2' ), 'icon' => '👶', 'hint' => __( 'No steps that block a pram.', 'vpg-v2' ) ],                 // 0095
        'winter'   => [ 'type' => 'bool',   'label' => __( 'Winter caution', 'vpg-v2' ),    'icon' => '❄', 'hint' => __( 'Icy passages possible in the cold months.', 'vpg-v2' ) ],   // 0117
        'blind'    => [ 'type' => 'bool',   'label' => __( 'Blind trail', 'vpg-v2' ),       'icon' => '🎯', 'hint' => __( 'Coordinates only — no motif hints. A training format.', 'vpg-v2' ) ], // 0098
        'film'     => [ 'type' => 'bool',   'label' => __( '36-frame format', 'vpg-v2' ),   'icon' => '🎞', 'hint' => __( '36 exposures for the whole walk — the film simulation.', 'vpg-v2' ) ], // 0112
        'lens'     => [ 'type' => 'text',   'label' => __( 'One-lens rule', 'vpg-v2' ),     'icon' => '📷', 'hint' => __( 'A fixed focal length for the whole route, e.g. 50 mm.', 'vpg-v2' ) ], // 0099
        'dir'      => [ 'type' => 'select', 'label' => __( 'Light direction', 'vpg-v2' ),   'icon' => '🧭', 'options' => [ '' => __( 'Either way', 'vpg-v2' ), 'as' => __( 'As numbered', 'vpg-v2' ), 'rev' => __( 'Reversed in the afternoon', 'vpg-v2' ) ], 'hint' => __( 'Which way the light favours.', 'vpg-v2' ) ], // 0085
        'gain'     => [ 'type' => 'int',    'label' => __( 'Elevation gain (m)', 'vpg-v2' ),'icon' => '⛰', 'hint' => __( 'Total climb — matters with a tripod in the bag.', 'vpg-v2' ) ], // 0082
        'season'   => [ 'type' => 'select', 'label' => __( 'Season window', 'vpg-v2' ),     'icon' => '🌸', 'options' => [ '' => __( 'Any time', 'vpg-v2' ), 'cherry' => __( 'Cherry blossom', 'vpg-v2' ), 'autumn' => __( 'Autumn leaves', 'vpg-v2' ), 'advent' => __( 'Advent markets', 'vpg-v2' ), 'summer' => __( 'High summer', 'vpg-v2' ) ], 'hint' => __( 'When this route is at its best.', 'vpg-v2' ) ], // 0094
        'barriers' => [ 'type' => 'multi',  'label' => __( 'Barriers', 'vpg-v2' ),          'icon' => '⚠', 'options' => [ 'steps' => __( 'Steps', 'vpg-v2' ), 'cobbles' => __( 'Cobblestones', 'vpg-v2' ), 'gradient' => __( 'Steep gradient', 'vpg-v2' ), 'gravel' => __( 'Gravel / unpaved', 'vpg-v2' ) ], 'hint' => __( 'Access notes for wheels and knees.', 'vpg-v2' ) ], // 0108
        'transit_start' => [ 'type' => 'text', 'label' => __( 'Start · nearest stop', 'vpg-v2' ), 'icon' => '🚇', 'hint' => __( 'U-Bahn / tram at the start.', 'vpg-v2' ) ], // 0096
        'transit_end'   => [ 'type' => 'text', 'label' => __( 'End · nearest stop', 'vpg-v2' ),   'icon' => '🚉', 'hint' => __( 'U-Bahn / tram at the finish.', 'vpg-v2' ) ], // 0096
    ] );
}

function vpg_trail_attrs( $id ) {
    $out = [];
    foreach ( vpg_trail_attr_schema() as $k => $def ) {
        $v = get_post_meta( $id, '_vpg_t_' . $k, true );
        if ( $def['type'] === 'multi' )      $out[ $k ] = array_filter( (array) $v );
        elseif ( $def['type'] === 'bool' )   $out[ $k ] = (bool) $v;
        elseif ( $def['type'] === 'int' )    $out[ $k ] = (int) $v;
        else                                 $out[ $k ] = (string) $v;
    }
    return $out;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Metabox · trail attributes, format, stages, series & sources     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-trail-format', '🧭 ' . __( 'Trail format & attributes', 'vpg-v2' ), 'vpg_render_trail_format_box', 'vpg_trail', 'normal', 'default' );
} );

function vpg_render_trail_format_box( $post ) {
    wp_nonce_field( 'vpg_trail_format', 'vpg_trail_format_nonce' );
    $a = vpg_trail_attrs( $post->ID );
    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px">';
    foreach ( vpg_trail_attr_schema() as $k => $def ) {
        echo '<div>';
        if ( $def['type'] === 'bool' ) {
            echo '<label><input type="checkbox" name="vpg_t[' . esc_attr( $k ) . ']" value="1"' . checked( $a[ $k ], true, false ) . '> ' . esc_html( $def['icon'] . ' ' . $def['label'] ) . '</label>';
        } elseif ( $def['type'] === 'select' ) {
            echo '<label style="font-weight:600;display:block">' . esc_html( $def['icon'] . ' ' . $def['label'] ) . '</label><select name="vpg_t[' . esc_attr( $k ) . ']">';
            foreach ( $def['options'] as $ov => $ol ) echo '<option value="' . esc_attr( $ov ) . '"' . selected( $a[ $k ], $ov, false ) . '>' . esc_html( $ol ) . '</option>';
            echo '</select>';
        } elseif ( $def['type'] === 'multi' ) {
            echo '<label style="font-weight:600;display:block">' . esc_html( $def['icon'] . ' ' . $def['label'] ) . '</label>';
            foreach ( $def['options'] as $ov => $ol ) echo '<label style="display:inline-block;margin-right:10px;font-weight:400"><input type="checkbox" name="vpg_t[' . esc_attr( $k ) . '][]" value="' . esc_attr( $ov ) . '"' . checked( in_array( $ov, $a[ $k ], true ), true, false ) . '> ' . esc_html( $ol ) . '</label>';
        } elseif ( $def['type'] === 'int' ) {
            echo '<label style="font-weight:600;display:block">' . esc_html( $def['icon'] . ' ' . $def['label'] ) . '</label><input type="number" min="0" name="vpg_t[' . esc_attr( $k ) . ']" value="' . esc_attr( $a[ $k ] ) . '" style="width:110px">';
        } else {
            echo '<label style="font-weight:600;display:block">' . esc_html( $def['icon'] . ' ' . $def['label'] ) . '</label><input type="text" name="vpg_t[' . esc_attr( $k ) . ']" value="' . esc_attr( $a[ $k ] ) . '" style="width:100%">';
        }
        if ( ! empty( $def['hint'] ) ) echo '<p class="description" style="margin:2px 0 0">' . esc_html( $def['hint'] ) . '</p>';
        echo '</div>';
    }
    echo '</div><hr style="margin:16px 0">';

    // 0084 stages · 0086 rest stops — comma IDs referencing the stop list
    $rest   = (string) get_post_meta( $post->ID, '_vpg_t_rest', true );
    $stages = (string) get_post_meta( $post->ID, '_vpg_t_stages', true );
    echo '<p><label style="font-weight:600">☕ ' . esc_html__( '0086 · Rest / café stops', 'vpg-v2' ) . '</label><br><input type="text" name="vpg_t_rest" value="' . esc_attr( $rest ) . '" style="width:100%" placeholder="87, 43"><span class="description">' . esc_html__( 'Stop IDs that are a coffee or Gasthaus break.', 'vpg-v2' ) . '</span></p>';
    echo '<p><label style="font-weight:600">🚏 ' . esc_html__( '0084 · Stage breaks after these stops', 'vpg-v2' ) . '</label><br><input type="text" name="vpg_t_stages" value="' . esc_attr( $stages ) . '" style="width:100%" placeholder="43"><span class="description">' . esc_html__( 'A half-day break splits the route into stages after each listed stop ID.', 'vpg-v2' ) . '</span></p>';

    // 0101 audio companion
    $aud = (int) get_post_meta( $post->ID, '_vpg_t_audio', true );
    echo '<p style="margin-top:10px"><strong>🎙 ' . esc_html__( '0101 · Audio companion (mini-podcast)', 'vpg-v2' ) . '</strong><br>';
    echo '<input type="hidden" id="vpg-taudio-id" name="vpg_t_audio" value="' . $aud . '">';
    if ( $aud ) echo '<audio controls src="' . esc_url( wp_get_attachment_url( $aud ) ) . '" style="max-width:100%;display:block;margin:6px 0"></audio>';
    echo '<button type="button" class="button" id="vpg-taudio-pick">' . esc_html__( 'Pick / upload audio', 'vpg-v2' ) . '</button></p>';

    // 0111 series · 0119 successor · 0103 guest
    $series = (string) get_post_meta( $post->ID, '_vpg_t_series', true );
    $sord   = (int) get_post_meta( $post->ID, '_vpg_t_series_ord', true );
    $succ   = (int) get_post_meta( $post->ID, '_vpg_t_successor', true );
    $guest  = (string) get_post_meta( $post->ID, '_vpg_t_guest', true );
    echo '<hr style="margin:16px 0"><div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px">';
    echo '<div><label style="font-weight:600">🎞 ' . esc_html__( '0111 · Series name', 'vpg-v2' ) . '</label><br><input type="text" name="vpg_t_series" value="' . esc_attr( $series ) . '" style="width:100%" placeholder="Ringstraße in 5 Teilen"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Series part #', 'vpg-v2' ) . '</label><br><input type="number" min="0" name="vpg_t_series_ord" value="' . esc_attr( $sord ) . '" style="width:90px"></div>';
    echo '<div><label style="font-weight:600">📦 ' . esc_html__( '0119 · Superseded by (trail ID)', 'vpg-v2' ) . '</label><br><input type="number" min="0" name="vpg_t_successor" value="' . esc_attr( $succ ) . '" style="width:110px"><span class="description">' . esc_html__( 'Marks this route archived and points to its replacement.', 'vpg-v2' ) . '</span></div>';
    echo '<div><label style="font-weight:600">🤝 ' . esc_html__( '0103 · Guest route by (collective)', 'vpg-v2' ) . '</label><br><input type="text" name="vpg_t_guest" value="' . esc_attr( $guest ) . '" style="width:100%" placeholder="Partner collective"></div>';
    echo '</div>';
    ?>
    <script>
    (function(){
      var b=document.getElementById('vpg-taudio-pick'),f=document.getElementById('vpg-taudio-id');
      if(b&&window.wp&&wp.media){b.addEventListener('click',function(){
        var fr=wp.media({library:{type:'audio'},multiple:false});
        fr.on('select',function(){var a=fr.state().get('selection').first().toJSON();f.value=a.id;b.insertAdjacentHTML('beforebegin','<audio controls src="'+a.url+'" style="max-width:100%;display:block;margin:6px 0"></audio>');});
        fr.open();
      });}
    })();
    </script>
    <?php
}

add_action( 'save_post_vpg_trail', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_trail_format_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_trail_format_nonce'], 'vpg_trail_format' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $in = (array) ( $_POST['vpg_t'] ?? [] );
    foreach ( vpg_trail_attr_schema() as $k => $def ) {
        $key = '_vpg_t_' . $k;
        if ( $def['type'] === 'bool' ) {
            empty( $in[ $k ] ) ? delete_post_meta( $post_id, $key ) : update_post_meta( $post_id, $key, 1 );
        } elseif ( $def['type'] === 'multi' ) {
            $vals = array_values( array_intersect( array_keys( $def['options'] ), (array) ( $in[ $k ] ?? [] ) ) );
            $vals ? update_post_meta( $post_id, $key, $vals ) : delete_post_meta( $post_id, $key );
        } elseif ( $def['type'] === 'select' ) {
            $v = sanitize_key( $in[ $k ] ?? '' );
            ( $v && isset( $def['options'][ $v ] ) ) ? update_post_meta( $post_id, $key, $v ) : delete_post_meta( $post_id, $key );
        } elseif ( $def['type'] === 'int' ) {
            $v = max( 0, (int) ( $in[ $k ] ?? 0 ) );
            $v ? update_post_meta( $post_id, $key, $v ) : delete_post_meta( $post_id, $key );
        } else {
            $v = sanitize_text_field( wp_unslash( $in[ $k ] ?? '' ) );
            $v !== '' ? update_post_meta( $post_id, $key, $v ) : delete_post_meta( $post_id, $key );
        }
    }
    foreach ( [ '_vpg_t_rest' => 'vpg_t_rest', '_vpg_t_stages' => 'vpg_t_stages' ] as $mk => $field ) {
        $ids = implode( ',', array_filter( array_map( 'intval', explode( ',', (string) ( $_POST[ $field ] ?? '' ) ) ) ) );
        $ids ? update_post_meta( $post_id, $mk, $ids ) : delete_post_meta( $post_id, $mk );
    }
    $aud = (int) ( $_POST['vpg_t_audio'] ?? 0 );
    $aud ? update_post_meta( $post_id, '_vpg_t_audio', $aud ) : delete_post_meta( $post_id, '_vpg_t_audio' );
    foreach ( [ '_vpg_t_series' => [ 'vpg_t_series', 'text' ], '_vpg_t_guest' => [ 'vpg_t_guest', 'text' ], '_vpg_t_series_ord' => [ 'vpg_t_series_ord', 'int' ], '_vpg_t_successor' => [ 'vpg_t_successor', 'int' ] ] as $mk => $sp ) {
        $raw = $_POST[ $sp[0] ] ?? '';
        if ( $sp[1] === 'int' ) { $v = max( 0, (int) $raw ); $v ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk ); }
        else { $v = sanitize_text_field( wp_unslash( $raw ) ); $v !== '' ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk ); }
    }
} );

add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && ( $s->post_type ?? '' ) === 'vpg_trail' ) wp_enqueue_media();
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Computed helpers                                                 */
/* ════════════════════════════════════════════════════════════════ */

/* 0104 · genres gathered from the stops' curated attributes */
function vpg_trail_genres( $trail_id ) {
    $g = [];
    foreach ( vpg_trail_stops( $trail_id ) as $s ) {
        $a = function_exists( 'vpg_spot_attrs' ) ? vpg_spot_attrs( $s['post']->ID ) : [];
        foreach ( (array) ( $a['genres'] ?? [] ) as $x ) $g[ $x ] = ( $g[ $x ] ?? 0 ) + 1;
    }
    arsort( $g );
    return array_slice( array_keys( $g ), 0, 4 );
}

/* 0093 · every member photo along the route (stops' featured + attached) */
function vpg_trail_photo_album( $trail_id, $limit = 24 ) {
    $ids = [];
    foreach ( vpg_trail_stops( $trail_id ) as $s ) {
        $pid = $s['post']->ID;
        if ( has_post_thumbnail( $pid ) ) $ids[] = get_post_thumbnail_id( $pid );
        foreach ( get_posts( [ 'post_type' => 'attachment', 'post_parent' => $pid, 'post_mime_type' => 'image', 'posts_per_page' => 4, 'fields' => 'ids' ] ) as $aid ) $ids[] = $aid;
    }
    return array_slice( array_values( array_unique( array_filter( $ids ) ) ), 0, $limit );
}

/* Vienna sunset (local, minutes from midnight) by month — approx 48.2°N */
function vpg_vienna_sunset_min( $month ) {
    $tab = [ 1 => 980, 2 => 1030, 3 => 1090, 4 => 1190, 5 => 1245, 6 => 1275, 7 => 1265, 8 => 1210, 9 => 1135, 10 => 1075, 11 => 985, 12 => 960 ];
    return $tab[ (int) $month ] ?? 1140;
}

/* 0116 · start-time optimiser — start so the last stop lands in evening light */
function vpg_trail_start_time( $trail_id ) {
    $geo = function_exists( 'vpg_trail_geo' ) ? vpg_trail_geo( $trail_id ) : null;
    if ( ! $geo || empty( $geo['minutes'] ) ) return '';
    $sunset = vpg_vienna_sunset_min( (int) wp_date( 'n' ) );
    // aim to arrive at the final stop ~20 min before sunset (into blue hour)
    $start = $sunset - 20 - (int) $geo['minutes'];
    if ( $start < 8 * 60 ) $start = 8 * 60; // never suggest before 08:00
    return sprintf( '%02d:%02d', intdiv( $start, 60 ), $start % 60 );
}

/* 0109 · weather window — 7-day precip outlook (cached), best days first */
function vpg_trail_weather_window( $trail_id ) {
    $stops = vpg_trail_stops( $trail_id );
    $c = null;
    foreach ( $stops as $s ) { if ( $s['coords'] ) { $c = $s['coords']; break; } }
    if ( ! $c ) return [];
    $key = 'vpg_tww_' . md5( round( $c[0], 2 ) . ',' . round( $c[1], 2 ) );
    $cached = get_transient( $key );
    if ( is_array( $cached ) ) return $cached;
    $url = add_query_arg( [
        'latitude'  => round( $c[0], 3 ), 'longitude' => round( $c[1], 3 ),
        'daily'     => 'precipitation_probability_mean', 'timezone' => 'Europe/Vienna', 'forecast_days' => 7,
    ], 'https://api.open-meteo.com/v1/forecast' );
    $res = wp_remote_get( $url, [ 'timeout' => 4 ] );
    if ( is_wp_error( $res ) || wp_remote_retrieve_response_code( $res ) !== 200 ) { set_transient( $key, [], HOUR_IN_SECONDS ); return []; }
    $d = json_decode( wp_remote_retrieve_body( $res ), true );
    $days = $d['daily']['time'] ?? []; $pop = $d['daily']['precipitation_probability_mean'] ?? [];
    $out = [];
    foreach ( $days as $i => $day ) $out[] = [ 'date' => $day, 'pop' => (int) ( $pop[ $i ] ?? 0 ) ];
    set_transient( $key, $out, 3 * HOUR_IN_SECONDS );
    return $out;
}

/* stop-id lists as int arrays */
function vpg_trail_idlist( $trail_id, $meta ) {
    return array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $trail_id, $meta, true ) ) ) );
}

/* archived? (0119) */
function vpg_trail_successor( $trail_id ) {
    $s = (int) get_post_meta( $trail_id, '_vpg_t_successor', true );
    return ( $s && get_post_type( $s ) === 'vpg_trail' && get_post_status( $s ) === 'publish' ) ? $s : 0;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Front-end render — called from single-vpg_trail.php               */
/* ════════════════════════════════════════════════════════════════ */

/* 0104 stat bar + 0088/0089/… attribute chips + principles */
function vpg_trail_render_statbar( $id ) {
    $geo    = function_exists( 'vpg_trail_geo' ) ? vpg_trail_geo( $id ) : null;
    $stops  = vpg_trail_stops( $id );
    $genres = vpg_trail_genres( $id );
    $a      = vpg_trail_attrs( $id );
    $succ   = vpg_trail_successor( $id );
    $guest  = get_post_meta( $id, '_vpg_t_guest', true );
    ?>
    <?php if ( $succ ) : ?>
      <div style="background:var(--g-off,#F5F4F1);border-left:3px solid var(--g-red);padding:10px 14px;margin:0 0 16px;font-size:13px">
        📦 <?php printf( wp_kses_post( __( 'This route is archived. Its up-to-date successor is <a href="%1$s"><strong>%2$s</strong></a>.', 'vpg-v2' ) ), esc_url( get_permalink( $succ ) ), esc_html( get_the_title( $succ ) ) ); ?>
      </div>
    <?php endif; ?>
    <div class="vpg-trail-stats" style="display:flex;flex-wrap:wrap;gap:8px 18px;align-items:center;font-size:12px;font-weight:700;letter-spacing:.02em;border-top:1px solid var(--g-line);border-bottom:1px solid var(--g-line);padding:12px 0;margin:0 0 4px">
      <span>📍 <?php printf( esc_html( _n( '%d stop', '%d stops', count( $stops ), 'vpg-v2' ) ), count( $stops ) ); ?></span>
      <?php if ( $geo && $geo['km'] > 0 ) : ?><span>📏 <?php echo esc_html( number_format_i18n( $geo['km'], 1 ) ); ?> km</span><?php endif; ?>
      <?php if ( $geo && $geo['minutes'] > 0 ) : ?><span>⏱ <?php printf( '~%dh %02dmin', intdiv( $geo['minutes'], 60 ), $geo['minutes'] % 60 ); ?></span><?php endif; ?>
      <?php if ( ! empty( $a['gain'] ) ) : ?><span>⛰ <?php echo (int) $a['gain']; ?> m</span><?php endif; ?>
      <?php foreach ( $genres as $g ) : ?><span style="color:var(--g-mid);font-weight:600"><?php echo esc_html( ucfirst( $g ) ); ?></span><?php endforeach; ?>
    </div>
    <?php $chips = vpg_trail_chip_list( $a );
    if ( $chips || $guest ) : ?>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin:10px 0 0">
      <?php if ( $guest ) : ?><span style="font-size:11px;font-weight:700;background:var(--g-ink,#0B0B0B);color:var(--g-paper,#fff);padding:4px 10px">🤝 <?php echo esc_html( sprintf( __( 'Guest route · %s', 'vpg-v2' ), $guest ) ); ?></span><?php endif; ?>
      <?php foreach ( $chips as $c ) : ?><span title="<?php echo esc_attr( $c['hint'] ); ?>" style="font-size:11px;font-weight:700;border:1px solid var(--g-line);padding:4px 10px"><?php echo esc_html( $c['icon'] . ' ' . $c['label'] ); ?></span><?php endforeach; ?>
    </div>
    <?php endif;
}

function vpg_trail_chip_list( $a ) {
    $chips = [];
    foreach ( vpg_trail_attr_schema() as $k => $def ) {
        if ( $def['type'] === 'bool' && ! empty( $a[ $k ] ) ) $chips[] = [ 'icon' => $def['icon'], 'label' => $def['label'], 'hint' => $def['hint'] ?? '' ];
    }
    if ( ! empty( $a['lens'] ) )   $chips[] = [ 'icon' => '📷', 'label' => sprintf( __( '%s only', 'vpg-v2' ), $a['lens'] ), 'hint' => __( 'A one-lens route.', 'vpg-v2' ) ];
    if ( ! empty( $a['season'] ) ) { $so = vpg_trail_attr_schema()['season']['options']; $chips[] = [ 'icon' => '🌸', 'label' => $so[ $a['season'] ] ?? $a['season'], 'hint' => __( 'Best in this season.', 'vpg-v2' ) ]; }
    foreach ( (array) ( $a['barriers'] ?? [] ) as $b ) { $bo = vpg_trail_attr_schema()['barriers']['options']; $chips[] = [ 'icon' => '⚠', 'label' => $bo[ $b ] ?? $b, 'hint' => __( 'Access note.', 'vpg-v2' ) ]; }
    return $chips;
}

/* The rich block appended to the single template (everything else) */
function vpg_trail_render_extras( $id ) {
    $a       = vpg_trail_attrs( $id );
    $uid     = get_current_user_id();
    $walked  = (int) get_post_meta( $id, '_vpg_t_walked', true );
    $walkers = array_filter( (array) get_post_meta( $id, '_vpg_t_walkers', true ) );
    $aud     = (int) get_post_meta( $id, '_vpg_t_audio', true );
    $series  = get_post_meta( $id, '_vpg_t_series', true );
    $start   = vpg_trail_start_time( $id );
    $ww      = vpg_trail_weather_window( $id );
    $album   = vpg_trail_photo_album( $id );
    $transit = [ get_post_meta( $id, '_vpg_t_transit_start', true ), get_post_meta( $id, '_vpg_t_transit_end', true ) ];
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap">

      <!-- 0096 transit · 0116 start-time · 0109 weather window -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:8px">
        <?php if ( $transit[0] || $transit[1] ) : ?>
        <div><p class="g-kicker" style="margin-bottom:6px">● <?php esc_html_e( 'Getting there', 'vpg-v2' ); ?></p>
          <p style="font-size:13px;margin:0"><?php if ( $transit[0] ) printf( esc_html__( 'Start: %s', 'vpg-v2' ), '🚇 ' . esc_html( $transit[0] ) ); ?><?php if ( $transit[0] && $transit[1] ) echo '<br>'; ?><?php if ( $transit[1] ) printf( esc_html__( 'End: %s', 'vpg-v2' ), '🚉 ' . esc_html( $transit[1] ) ); ?></p></div>
        <?php endif; ?>
        <?php if ( $start ) : ?>
        <div><p class="g-kicker" style="margin-bottom:6px">● <?php esc_html_e( 'Best start time today', 'vpg-v2' ); ?></p>
          <p style="font-size:22px;font-weight:900;margin:0"><?php echo esc_html( $start ); ?></p>
          <p style="font-size:12px;color:var(--g-mid);margin:2px 0 0"><?php esc_html_e( 'so the last stop lands in evening light', 'vpg-v2' ); ?></p></div>
        <?php endif; ?>
        <?php if ( $ww ) : ?>
        <div><p class="g-kicker" style="margin-bottom:6px">● <?php esc_html_e( 'Rain outlook · 7 days', 'vpg-v2' ); ?></p>
          <div style="display:flex;gap:4px;align-items:flex-end;height:40px">
            <?php foreach ( $ww as $d ) : $h = max( 3, (int) round( $d['pop'] * 0.36 ) ); ?>
              <div title="<?php echo esc_attr( $d['date'] . ' · ' . $d['pop'] . '%' ); ?>" style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center">
                <span style="width:100%;height:<?php echo $h; ?>px;background:<?php echo $d['pop'] < 30 ? 'var(--g-red)' : 'var(--g-line)'; ?>"></span>
                <span style="font-size:9px;color:var(--g-mid);margin-top:3px"><?php echo esc_html( wp_date( 'D', strtotime( $d['date'] ) )[0] ); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <p style="font-size:11px;color:var(--g-mid);margin:4px 0 0"><?php esc_html_e( 'Shorter = drier. Red days are your window.', 'vpg-v2' ); ?></p></div>
        <?php endif; ?>
      </div>

      <!-- 0101 audio companion -->
      <?php if ( $aud ) : ?>
        <p class="g-kicker" style="margin:20px 0 6px">● <?php esc_html_e( 'Walk it with the maker', 'vpg-v2' ); ?></p>
        <audio controls preload="none" src="<?php echo esc_url( wp_get_attachment_url( $aud ) ); ?>" style="width:100%;max-width:520px"></audio>
      <?php endif; ?>

      <!-- 0091 walked counter · 0097 duets · 0092 live/check-in · 0107 remix · 0114 event -->
      <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:22px 0 0">
        <span style="font-size:13px;font-weight:700"><?php printf( esc_html( _n( '%d member has walked this', '%d members have walked this', $walked, 'vpg-v2' ) ), $walked ); ?></span>
        <?php if ( count( $walkers ) >= 2 ) : ?><span style="font-size:12px;color:var(--g-mid)">— <?php esc_html_e( 'incl. a walked-together duet', 'vpg-v2' ); ?> 🤝</span><?php endif; ?>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 0">
        <button type="button" class="g-btn g-btn--ghost" id="vpg-trail-live" style="font-size:12px">▶ <?php esc_html_e( 'Live mode', 'vpg-v2' ); ?></button>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( home_url( '/trail-print/' . $id . '/' ) ); ?>" target="_blank">⧉ <?php esc_html_e( 'Print heft + QR', 'vpg-v2' ); ?></a>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( home_url( '/trail-bingo/' . $id . '/' ) ); ?>" target="_blank">▦ <?php esc_html_e( 'Bingo card', 'vpg-v2' ); ?></a>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( home_url( '/print/leporello/' . $id . '/' ) ); ?>" target="_blank">📐 <?php esc_html_e( 'Leporello', 'vpg-v2' ); ?></a>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_trail_gpx&trail=' . $id ) ); ?>">↓ GPX</a>
        <?php if ( $uid ) : ?>
          <?php foreach ( [ 'walk' => __( '✓ I walked this', 'vpg-v2' ), 'remix' => __( '⑃ Remix this route', 'vpg-v2' ), 'event' => __( '⚑ Make it a meetup', 'vpg-v2' ) ] as $act => $lbl ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
            <?php wp_nonce_field( 'vpg_trail_member' ); ?>
            <input type="hidden" name="action" value="vpg_trail_member">
            <input type="hidden" name="trail" value="<?php echo (int) $id; ?>">
            <input type="hidden" name="act" value="<?php echo esc_attr( $act ); ?>">
            <button type="submit" class="g-btn g-btn--ghost" style="font-size:12px"><?php echo esc_html( $lbl ); ?></button>
          </form>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- 0093 photo album -->
      <?php if ( count( $album ) >= 2 ) : ?>
        <p class="g-kicker" style="margin:26px 0 8px">● <?php esc_html_e( 'Shot along the way', 'vpg-v2' ); ?></p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:6px">
          <?php foreach ( $album as $aid ) : $u = wp_get_attachment_image_url( $aid, 'medium' ); if ( ! $u ) continue; ?>
            <img loading="lazy" src="<?php echo esc_url( $u ); ?>" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;display:block">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- 0115 feedback -->
      <?php if ( $uid ) : ?>
        <p class="g-kicker" style="margin:26px 0 8px">● <?php esc_html_e( 'How was the walk?', 'vpg-v2' ); ?></p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
          <?php wp_nonce_field( 'vpg_trail_feedback' ); ?>
          <input type="hidden" name="action" value="vpg_trail_feedback">
          <input type="hidden" name="trail" value="<?php echo (int) $id; ?>">
          <select name="rating" style="padding:8px">
            <option value="5"><?php esc_html_e( '★★★★★ Loved it', 'vpg-v2' ); ?></option>
            <option value="4">★★★★</option><option value="3">★★★</option><option value="2">★★</option>
            <option value="1"><?php esc_html_e( '★ Needs work', 'vpg-v2' ); ?></option>
          </select>
          <input type="text" name="note" maxlength="180" placeholder="<?php esc_attr_e( 'One line for the maker (optional)', 'vpg-v2' ); ?>" style="flex:1;min-width:200px;padding:8px;border:1px solid var(--g-line)">
          <button type="submit" class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Send feedback', 'vpg-v2' ); ?></button>
        </form>
      <?php endif; ?>

      <!-- 0111 series nav -->
      <?php if ( $series ) :
        $sib = get_posts( [ 'post_type' => 'vpg_trail', 'post_status' => 'publish', 'posts_per_page' => 12, 'post__not_in' => [ $id ], 'meta_key' => '_vpg_t_series', 'meta_value' => $series ] );
        if ( $sib ) : ?>
        <p class="g-kicker" style="margin:26px 0 8px">● <?php echo esc_html( sprintf( __( 'Part of the series · %s', 'vpg-v2' ), $series ) ); ?></p>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php foreach ( $sib as $st ) : ?><a href="<?php echo esc_url( get_permalink( $st ) ); ?>" style="font-size:12px;font-weight:700;border:1px solid var(--g-line);padding:6px 12px;text-decoration:none"><?php echo esc_html( get_the_title( $st ) ); ?></a><?php endforeach; ?>
        </div>
      <?php endif; endif; ?>

      <!-- 0113 sponsor-free · 0120 no ranking — the principles -->
      <p style="font-size:11px;color:var(--g-mid);margin:26px 0 0;border-top:1px solid var(--g-line);padding-top:12px">
        <?php esc_html_e( 'Sponsor-free by principle — no brand pays for a stop here. And no leaderboard: a route is a walk, not a race.', 'vpg-v2' ); ?>
      </p>
    </div></section>

    <script>
    (function(){
      /* 0092 check-ins + 0110 missed stops (this browser) */
      var rows=document.querySelectorAll('.g-row[data-stop]');
      if(rows.length){
        var TK='vpg_trail_'+<?php echo (int) $id; ?>;
        var done={};try{done=JSON.parse(localStorage.getItem(TK))||{};}catch(e){}
        rows.forEach(function(r){
          var sid=r.getAttribute('data-stop');
          var box=document.createElement('button');box.type='button';
          box.textContent=done[sid]?'✓':'○';
          box.title='<?php echo esc_js( __( 'Check off / skip', 'vpg-v2' ) ); ?>';
          box.style.cssText='margin-left:8px;border:1px solid var(--g-line,#E6E5E1);background:none;width:28px;height:28px;cursor:pointer;font-weight:700';
          if(done[sid])r.style.opacity=.55;
          box.addEventListener('click',function(e){e.preventDefault();e.stopPropagation();
            done[sid]=!done[sid];box.textContent=done[sid]?'✓':'○';r.style.opacity=done[sid]?.55:1;
            try{localStorage.setItem(TK,JSON.stringify(done));}catch(e){}});
          r.appendChild(box);
        });
        /* missed stops → the tour tray watchlist (vpg_tour) */
        var mb=document.getElementById('vpg-trail-missed');
        if(mb)mb.addEventListener('click',function(){
          var tour=[];try{tour=JSON.parse(localStorage.getItem('vpg_tour'))||[];}catch(e){}
          rows.forEach(function(r){var sid=r.getAttribute('data-stop');
            if(!done[sid]&&!tour.some(function(t){return String(t.id)===String(sid);}))
              tour.push({id:sid,title:r.getAttribute('data-title')||'',url:r.getAttribute('href')||''});});
          try{localStorage.setItem('vpg_tour',JSON.stringify(tour.slice(0,30)));}catch(e){}
          mb.textContent='<?php echo esc_js( __( 'Missed stops saved ✓', 'vpg-v2' ) ); ?>';
        });
      }
      /* 0106 live mode hand-off to map-engine */
      var lb=document.getElementById('vpg-trail-live');
      if(lb)lb.addEventListener('click',function(){ if(window.vpgTrailLive)window.vpgTrailLive(); else lb.textContent='<?php echo esc_js( __( 'Live needs the map above', 'vpg-v2' ) ); ?>'; });
    })();
    </script>
    <?php
}

/* ════════════════════════════════════════════════════════════════ */
/*  Member actions · walk / remix / meetup / feedback                */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_trail_member', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_trail_member' );
    $id  = (int) ( $_POST['trail'] ?? 0 );
    $act = sanitize_key( $_POST['act'] ?? '' );
    if ( get_post_type( $id ) !== 'vpg_trail' || get_post_status( $id ) !== 'publish' ) wp_die( 'Not found', 404 );
    $uid = get_current_user_id();

    if ( $act === 'walk' ) { // 0091 + 0097
        $walkers = array_filter( (array) get_post_meta( $id, '_vpg_t_walkers', true ) );
        if ( ! in_array( $uid, $walkers, true ) ) {
            $walkers[] = $uid;
            update_post_meta( $id, '_vpg_t_walkers', array_slice( $walkers, -500 ) );
            update_post_meta( $id, '_vpg_t_walked', (int) get_post_meta( $id, '_vpg_t_walked', true ) + 1 );
        }
        wp_safe_redirect( get_permalink( $id ) . '#walked' ); exit;
    }

    if ( $act === 'remix' ) { // 0107
        $new = wp_insert_post( [
            'post_type'    => 'vpg_trail',
            'post_status'  => 'draft',
            'post_author'  => $uid,
            'post_title'   => sprintf( __( '%s — remix', 'vpg-v2' ), get_the_title( $id ) ),
            'post_content' => get_post_field( 'post_content', $id ),
        ] );
        if ( $new && ! is_wp_error( $new ) ) {
            foreach ( [ '_vpg_trail_stops', '_vpg_trail_difficulty', '_vpg_t_rest', '_vpg_t_stages' ] as $mk ) {
                $v = get_post_meta( $id, $mk, true );
                if ( $v !== '' ) update_post_meta( $new, $mk, $v );
            }
            update_post_meta( $new, '_vpg_t_remix_of', $id );
            wp_safe_redirect( get_edit_post_link( $new, 'url' ) ?: get_permalink( $id ) ); exit;
        }
        wp_safe_redirect( get_permalink( $id ) ); exit;
    }

    if ( $act === 'event' ) { // 0114 — editors/authors only
        if ( ! current_user_can( 'edit_posts' ) ) { wp_safe_redirect( get_permalink( $id ) ); exit; }
        $ev = wp_insert_post( [
            'post_type'   => 'vpg_event',
            'post_status' => 'draft',
            'post_author' => $uid,
            'post_title'  => sprintf( __( 'Photowalk · %s', 'vpg-v2' ), get_the_title( $id ) ),
            'post_content'=> sprintf( __( 'A group walk of the trail “%1$s”. Route: %2$s', 'vpg-v2' ), get_the_title( $id ), get_permalink( $id ) ),
        ] );
        if ( $ev && ! is_wp_error( $ev ) ) {
            update_post_meta( $ev, '_vpg_event_venue', (string) get_post_meta( $id, '_vpg_t_transit_start', true ) );
            update_post_meta( $ev, '_vpg_event_trail', $id );
            wp_safe_redirect( get_edit_post_link( $ev, 'url' ) ?: get_permalink( $id ) ); exit;
        }
        wp_safe_redirect( get_permalink( $id ) ); exit;
    }
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );

add_action( 'admin_post_vpg_trail_feedback', function () { // 0115
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_trail_feedback' );
    $id = (int) ( $_POST['trail'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_trail' ) wp_die( 'Not found', 404 );
    $fb = array_filter( (array) get_post_meta( $id, '_vpg_t_feedback', true ) );
    $fb[] = [ 'u' => get_current_user_id(), 'r' => max( 1, min( 5, (int) ( $_POST['rating'] ?? 0 ) ) ), 'n' => sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ), 't' => time() ];
    update_post_meta( $id, '_vpg_t_feedback', array_slice( $fb, -80 ) );
    wp_safe_redirect( get_permalink( $id ) . '#walked' ); exit;
} );

/* editors see the feedback on the edit screen */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-trail-feedback', '★ ' . __( 'Walk feedback', 'vpg-v2' ), function ( $post ) {
        $fb = array_reverse( array_filter( (array) get_post_meta( $post->ID, '_vpg_t_feedback', true ) ) );
        if ( ! $fb ) { echo '<p class="description">' . esc_html__( 'No feedback yet.', 'vpg-v2' ) . '</p>'; return; }
        $avg = array_sum( array_column( $fb, 'r' ) ) / count( $fb );
        echo '<p><strong>' . esc_html( number_format( $avg, 1 ) ) . '/5</strong> · ' . esc_html( sprintf( _n( '%d walk', '%d walks', count( $fb ), 'vpg-v2' ), count( $fb ) ) ) . '</p><ul style="margin:0;font-size:12px">';
        foreach ( array_slice( $fb, 0, 12 ) as $f ) { $u = get_userdata( (int) ( $f['u'] ?? 0 ) ); echo '<li>' . str_repeat( '★', (int) $f['r'] ) . ' ' . ( ! empty( $f['n'] ) ? '— ' . esc_html( $f['n'] ) : '' ) . ' <span style="color:#888">' . esc_html( $u ? $u->display_name : '' ) . '</span></li>'; }
        echo '</ul>';
    }, 'vpg_trail', 'side', 'default' );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Printable endpoints · A6 heft + QR (0105/0118) · bingo (0100)     */
/*                        · cover generator (0090)                    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^trail-print/(\d+)/?$', 'index.php?vpg_trailprint=$matches[1]', 'top' );
    add_rewrite_rule( '^trail-bingo/(\d+)/?$', 'index.php?vpg_trailbingo=$matches[1]', 'top' );
    add_rewrite_rule( '^trail-cover/(\d+)/?$', 'index.php?vpg_trailcover=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_trailprint'; $v[] = 'vpg_trailbingo'; $v[] = 'vpg_trailcover'; return $v; } );

/* Flush rewrite rules once when the rule set changes (registers the new
   trail endpoints — and any earlier cluster's rules — on an existing site). */
add_action( 'init', function () {
    $want = 'c10-recognition-1';
    if ( get_option( 'vpg_rw_ver' ) !== $want ) {
        flush_rewrite_rules( false );
        update_option( 'vpg_rw_ver', $want, false );
    }
}, 99 );

add_action( 'template_redirect', function () {
    $pid = (int) get_query_var( 'vpg_trailprint' );
    $bid = (int) get_query_var( 'vpg_trailbingo' );
    $cid = (int) get_query_var( 'vpg_trailcover' );
    $id  = $pid ?: $bid ?: $cid;
    if ( ! $id ) return;
    $trail = get_post( $id );
    if ( ! $trail || $trail->post_type !== 'vpg_trail' || $trail->post_status !== 'publish' ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
    $url   = get_permalink( $trail );
    $stops = vpg_trail_stops( $id );
    $geo   = function_exists( 'vpg_trail_geo' ) ? vpg_trail_geo( $id ) : null;
    nocache_headers();
    header( 'Content-Type: text/html; charset=utf-8' );

    if ( $pid ) { // 0105 + 0118 · A6 folding heft with a QR to the trail
        ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html( get_the_title( $trail ) ); ?> · Print</title>
        <style>*{box-sizing:border-box;margin:0;padding:0}@page{size:A6;margin:8mm}body{font-family:'Helvetica Neue',Arial,sans-serif;color:#0B0B0B;background:#fff;padding:14mm 10mm;max-width:420px;margin:0 auto}.k{font-size:9px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}h1{font-size:22px;font-weight:900;text-transform:uppercase;line-height:1;margin:6px 0 4px}.m{font-size:11px;color:#6A6A6A;margin-bottom:12px}ol{margin:0 0 12px 18px;font-size:12px;line-height:1.6}#qr{margin:10px 0}.u{font-family:monospace;font-size:10px;color:#6A6A6A;word-break:break-all}@media print{.noprint{display:none}}</style></head><body>
        <p class="k">Vienna Photo Group · Photowalk</p><h1><?php echo esc_html( get_the_title( $trail ) ); ?></h1>
        <p class="m"><?php echo esc_html( count( $stops ) ); ?> <?php esc_html_e( 'stops', 'vpg-v2' ); ?><?php if ( $geo && $geo['km'] > 0 ) echo ' · ' . esc_html( number_format_i18n( $geo['km'], 1 ) ) . ' km · ~' . intdiv( $geo['minutes'], 60 ) . 'h ' . ( $geo['minutes'] % 60 ) . 'min'; ?></p>
        <ol><?php foreach ( $stops as $s ) echo '<li>' . esc_html( get_the_title( $s['post'] ) ) . '</li>'; ?></ol>
        <div id="qr"></div><p class="u"><?php echo esc_html( $url ); ?></p>
        <p class="noprint" style="margin-top:14px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:8px 16px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print A6', 'vpg-v2' ); ?></button></p>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>new QRCode(document.getElementById('qr'),{text:<?php echo wp_json_encode( $url ); ?>,width:150,height:150,colorDark:'#0B0B0B',colorLight:'#fff'});</script>
        </body></html><?php exit;
    }

    if ( $bid ) { // 0100 · bingo motif card
        $tasks = [ __( 'A reflection', 'vpg-v2' ), __( 'Leading lines', 'vpg-v2' ), __( 'A stranger’s hands', 'vpg-v2' ), __( 'Something red', 'vpg-v2' ), __( 'Shadow as subject', 'vpg-v2' ), __( 'A doorway', 'vpg-v2' ), __( 'Motion blur', 'vpg-v2' ), __( 'Looking up', 'vpg-v2' ), __( 'Texture close-up', 'vpg-v2' ), __( 'A pattern of three', 'vpg-v2' ), __( 'Negative space', 'vpg-v2' ), __( 'A sign or letter', 'vpg-v2' ), __( 'Framed by nature', 'vpg-v2' ), __( 'The golden light', 'vpg-v2' ), __( 'Someone waiting', 'vpg-v2' ), __( 'A pop of blue', 'vpg-v2' ) ];
        ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php esc_html_e( 'Trail bingo', 'vpg-v2' ); ?></title>
        <style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Helvetica Neue',Arial,sans-serif;color:#0B0B0B;background:#fff;padding:24px;max-width:520px;margin:0 auto}.k{font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}h1{font-size:24px;font-weight:900;text-transform:uppercase;margin:6px 0 16px}.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}.c{border:1.5px solid #0B0B0B;aspect-ratio:1;display:flex;align-items:center;justify-content:center;text-align:center;font-size:11px;font-weight:600;padding:6px}@media print{.noprint{display:none}}</style></head><body>
        <p class="k">Vienna Photo Group · <?php echo esc_html( get_the_title( $trail ) ); ?></p><h1><?php esc_html_e( 'Photo bingo', 'vpg-v2' ); ?></h1>
        <div class="grid"><?php foreach ( $tasks as $t ) echo '<div class="c">' . esc_html( $t ) . '</div>'; ?></div>
        <p class="noprint" style="margin-top:18px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:8px 16px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print', 'vpg-v2' ); ?></button></p>
        </body></html><?php exit;
    }

    // 0090 · cover generator — a shareable 1200×630 title card
    $thumb = has_post_thumbnail( $id ) ? get_the_post_thumbnail_url( $id, 'large' ) : '';
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=1200"><title><?php echo esc_html( get_the_title( $trail ) ); ?> · Cover</title>
    <style>*{box-sizing:border-box;margin:0;padding:0}body{background:#111}.cover{width:1200px;height:630px;position:relative;overflow:hidden;background:#0B0B0B;color:#fff;font-family:'Helvetica Neue',Arial,sans-serif}.cover .bg{position:absolute;inset:0;background:<?php echo $thumb ? 'url(' . esc_url( $thumb ) . ') center/cover' : '#0B0B0B'; ?>;filter:grayscale(1) contrast(1.05);opacity:.55}.cover .in{position:absolute;inset:0;padding:64px;display:flex;flex-direction:column;justify-content:flex-end}.k{font-size:18px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;color:#E5341F}h1{font-size:76px;font-weight:900;text-transform:uppercase;line-height:.95;margin:14px 0}.m{font-size:22px;font-weight:700}</style></head><body>
    <div class="cover"><div class="bg"></div><div class="in"><p class="k">Vienna Photo Group · Photowalk</p><h1><?php echo esc_html( get_the_title( $trail ) ); ?></h1><p class="m"><?php echo esc_html( count( $stops ) ); ?> <?php esc_html_e( 'stops', 'vpg-v2' ); ?><?php if ( $geo && $geo['km'] > 0 ) echo ' · ' . esc_html( number_format_i18n( $geo['km'], 1 ) ) . ' km'; ?></p></div></div>
    </body></html><?php exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0102 · Trail of the month — a curated pick on the homepage        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_location', __( 'Trail of the month', 'vpg-v2' ), '★ ' . __( 'Trail of month', 'vpg-v2' ), 'edit_others_posts', 'vpg-trail-month', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_totm'] ) && check_admin_referer( 'vpg_totm' ) ) {
            update_option( 'vpg_trail_of_month', (int) $_POST['vpg_totm'], false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        $cur = (int) get_option( 'vpg_trail_of_month', 0 );
        $trails = get_posts( [ 'post_type' => 'vpg_trail', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC' ] );
        echo '<div class="wrap"><h1>★ ' . esc_html__( 'Trail of the month', 'vpg-v2' ) . '</h1><form method="post"><p class="description">' . esc_html__( 'Featured on the homepage via the [vpg_trail_of_month] shortcode.', 'vpg-v2' ) . '</p>';
        wp_nonce_field( 'vpg_totm' );
        echo '<select name="vpg_totm" style="min-width:320px"><option value="0">' . esc_html__( '— none —', 'vpg-v2' ) . '</option>';
        foreach ( $trails as $t ) echo '<option value="' . (int) $t->ID . '"' . selected( $cur, $t->ID, false ) . '>' . esc_html( $t->post_title ) . '</option>';
        echo '</select> <button class="button button-primary">' . esc_html__( 'Save', 'vpg-v2' ) . '</button></form></div>';
    } );
}, 22 );

add_shortcode( 'vpg_trail_of_month', function () {
    $id = (int) get_option( 'vpg_trail_of_month', 0 );
    if ( ! $id || get_post_type( $id ) !== 'vpg_trail' || get_post_status( $id ) !== 'publish' ) return '';
    $geo = function_exists( 'vpg_trail_geo' ) ? vpg_trail_geo( $id ) : null;
    ob_start(); ?>
    <a href="<?php echo esc_url( get_permalink( $id ) ); ?>" style="display:block;text-decoration:none;border:1px solid var(--g-line,#E6E5E1)">
      <?php if ( has_post_thumbnail( $id ) ) : ?><div class="g-fig g-fig--3x2"><?php echo get_the_post_thumbnail( $id, 'large' ); ?></div><?php endif; ?>
      <div style="padding:18px">
        <p class="g-kicker" style="color:var(--g-red)">★ <?php esc_html_e( 'Trail of the month', 'vpg-v2' ); ?></p>
        <h3 style="font-weight:900;text-transform:uppercase;margin:6px 0"><?php echo esc_html( get_the_title( $id ) ); ?></h3>
        <?php if ( $geo && $geo['km'] > 0 ) : ?><p style="font-size:12px;color:var(--g-mid)"><?php echo esc_html( count( vpg_trail_stops( $id ) ) ); ?> <?php esc_html_e( 'stops', 'vpg-v2' ); ?> · <?php echo esc_html( number_format_i18n( $geo['km'], 1 ) ); ?> km</p><?php endif; ?>
      </div>
    </a>
    <?php return ob_get_clean();
} );

/* 0119 · keep archived (superseded) trails out of the main archive loop */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( $q->is_post_type_archive( 'vpg_trail' ) ) {
        $mq = (array) $q->get( 'meta_query' );
        $mq[] = [ 'key' => '_vpg_t_successor', 'compare' => 'NOT EXISTS' ];
        $q->set( 'meta_query', $mq );
    }
} );
