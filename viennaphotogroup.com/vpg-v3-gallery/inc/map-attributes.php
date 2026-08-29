<?php
/**
 * VPG v3 — Cluster 01 · Karte als Werkzeug (pass 1).
 *
 * Curated spot attributes — member-entered, one coherent system that
 * feeds the metabox, the submit form, the pin data, the popup and the
 * map's attribute filters. Covers, in one place:
 *
 *   0001  Light layer (facing → sun-vs-view, client-side)
 *   0004  Rain alternatives   (indoor / covered)
 *   0005  Night layer         (night-suitable)
 *   0006  Four-seasons photos (four image ids in the popup)
 *   0007  Backlight warning    (facing vs sun azimuth)
 *   0008  Tripod traffic-light (allowed / tolerated / forbidden)
 *   0009  Drone zones          (restricted flag)
 *   0010  U-Bahn context       (nearest station)
 *   0027  Accessibility layer  (step-free)
 *   0028  Winter access        (open in winter)
 *   0031  Elevation            (metres, for viewpoint hunters)
 *   0032  Spot load            (over-photographed hint)
 *   0033  Parking notes
 *   0034  Toilets nearby
 *   0036  Pin relations        ("from here you see…")
 *   0037  Theme tags           (brutalism / jugendstil / neon / …)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_SPOT_ATTR_TYPES = [ 'vpg_location', 'vpg_studio', 'vpg_shop' ];

/** The single source of truth for every curated attribute. */
function vpg_spot_attr_schema() {
    return apply_filters( 'vpg_spot_attr_schema', [
        'tripod'   => [ 'type' => 'select', 'label' => __( 'Tripod', 'vpg-v2' ), 'chip' => '△',
                        'options' => [ '' => __( 'unknown', 'vpg-v2' ), 'ok' => __( 'allowed', 'vpg-v2' ), 'tolerated' => __( 'tolerated', 'vpg-v2' ), 'no' => __( 'forbidden', 'vpg-v2' ) ] ],
        'facing'   => [ 'type' => 'select', 'label' => __( 'View faces', 'vpg-v2' ), 'chip' => '➤',
                        'options' => [ '' => __( '—', 'vpg-v2' ), 'N' => 'N', 'NE' => 'NE', 'E' => 'E', 'SE' => 'SE', 'S' => 'S', 'SW' => 'SW', 'W' => 'W', 'NW' => 'NW' ] ],
        'indoor'   => [ 'type' => 'bool', 'label' => __( 'Indoor / covered (rain-safe)', 'vpg-v2' ), 'chip' => '☂', 'filter' => __( 'Rain-safe', 'vpg-v2' ) ],
        'night'    => [ 'type' => 'bool', 'label' => __( 'Works after dark', 'vpg-v2' ), 'chip' => '☾', 'filter' => __( 'Night', 'vpg-v2' ) ],
        'stepfree' => [ 'type' => 'bool', 'label' => __( 'Step-free / wheelchair', 'vpg-v2' ), 'chip' => '♿', 'filter' => __( 'Step-free', 'vpg-v2' ) ],
        'winter'   => [ 'type' => 'bool', 'label' => __( 'Open in winter', 'vpg-v2' ), 'chip' => '❄', 'filter' => __( 'Winter', 'vpg-v2' ) ],
        'toilets'  => [ 'type' => 'bool', 'label' => __( 'Toilets nearby', 'vpg-v2' ), 'chip' => '🚻' ],
        'drone'    => [ 'type' => 'bool', 'label' => __( 'Drone restricted / no-fly', 'vpg-v2' ), 'chip' => '🚁' ],
        'elev'     => [ 'type' => 'int',  'label' => __( 'Elevation (m)', 'vpg-v2' ), 'chip' => '⛰' ],
        'parking'  => [ 'type' => 'text', 'label' => __( 'Parking notes', 'vpg-v2' ), 'chip' => '🅿' ],
        'station'  => [ 'type' => 'text', 'label' => __( 'Nearest U-Bahn / tram', 'vpg-v2' ), 'chip' => 'Ⓤ' ],
        'focal'    => [ 'type' => 'text', 'label' => __( 'Recommended focal length(s)', 'vpg-v2' ), 'chip' => '⌖' ],
        'crowd'    => [ 'type' => 'select', 'label' => __( 'Crowding', 'vpg-v2' ), 'chip' => '☺',
                        'options' => [ '' => __( 'unknown', 'vpg-v2' ), 'empty' => __( 'usually empty', 'vpg-v2' ), 'ok' => __( 'okay', 'vpg-v2' ), 'busy' => __( 'often crowded', 'vpg-v2' ) ] ],
        'entry'    => [ 'type' => 'select', 'label' => __( 'Entry', 'vpg-v2' ), 'chip' => '⌸',
                        'options' => [ '' => __( 'unknown', 'vpg-v2' ), 'free' => __( 'free', 'vpg-v2' ), 'ticket' => __( 'ticket', 'vpg-v2' ), 'register' => __( 'registration', 'vpg-v2' ) ] ],
        'price'    => [ 'type' => 'select', 'label' => __( 'Price level', 'vpg-v2' ), 'chip' => '€',
                        'options' => [ '' => __( 'unknown', 'vpg-v2' ), '1' => '€', '2' => '€€', '3' => '€€€' ] ],
        'safety'   => [ 'type' => 'text', 'label' => __( 'Safety notes (honest)', 'vpg-v2' ), 'chip' => '⚠' ],
        'standpoint' => [ 'type' => 'text', 'label' => __( 'Exact standpoint', 'vpg-v2' ), 'chip' => '⊹' ],
        'angle'    => [ 'type' => 'text', 'label' => __( 'Anti-cliché angle', 'vpg-v2' ), 'chip' => '∡' ],
        'history'  => [ 'type' => 'text', 'label' => __( 'History in three sentences', 'vpg-v2' ), 'chip' => '🕰' ],
        'genres'   => [ 'type' => 'multi', 'label' => __( 'Genres', 'vpg-v2' ), 'options' => 'vpg_spot_genres' ],
        'equip'    => [ 'type' => 'multi', 'label' => __( 'Equipment warnings', 'vpg-v2' ), 'options' => 'vpg_spot_equip' ],
        'construction' => [ 'type' => 'bool', 'label' => __( 'Temporarily blocked (construction)', 'vpg-v2' ), 'chip' => '🚧', 'filter' => __( 'Blocked', 'vpg-v2' ) ],
        'closeduntil' => [ 'type' => 'text', 'label' => __( 'Blocked until (YYYY-MM-DD)', 'vpg-v2' ), 'chip' => '⏳' ],
        'themes'   => [ 'type' => 'themes', 'label' => __( 'Themes', 'vpg-v2' ) ],
        'related'  => [ 'type' => 'text', 'label' => __( 'Related pin IDs (comma-sep · “from here you see…”)', 'vpg-v2' ) ],
    ] );
}

function vpg_spot_themes() {
    return apply_filters( 'vpg_spot_themes', [
        'brutalism' => __( 'Brutalism', 'vpg-v2' ), 'jugendstil' => __( 'Jugendstil', 'vpg-v2' ),
        'neon' => __( 'Neon', 'vpg-v2' ), 'water' => __( 'Water', 'vpg-v2' ), 'green' => __( 'Green / parks', 'vpg-v2' ),
        'transit' => __( 'Transit', 'vpg-v2' ), 'rooftop' => __( 'Rooftops', 'vpg-v2' ), 'market' => __( 'Markets', 'vpg-v2' ),
    ] );
}
function vpg_spot_genres() {
    return apply_filters( 'vpg_spot_genres', [
        'street' => __( 'Street', 'vpg-v2' ), 'architecture' => __( 'Architecture', 'vpg-v2' ), 'portrait' => __( 'Portrait', 'vpg-v2' ),
        'macro' => __( 'Macro', 'vpg-v2' ), 'landscape' => __( 'Landscape', 'vpg-v2' ), 'analog' => __( 'Analog', 'vpg-v2' ),
        'night' => __( 'Night', 'vpg-v2' ), 'abstract' => __( 'Abstract', 'vpg-v2' ),
    ] );
}
function vpg_spot_equip() {
    return apply_filters( 'vpg_spot_equip', [
        'nd' => __( 'ND filter helps', 'vpg-v2' ), 'wide' => __( 'Wide-angle', 'vpg-v2' ), 'tele' => __( 'Telephoto', 'vpg-v2' ),
        'tripod' => __( 'Tripod needed', 'vpg-v2' ), 'noflash' => __( 'No flash', 'vpg-v2' ), 'fast' => __( 'Fast lens / low light', 'vpg-v2' ),
    ] );
}
/** Resolve a multi field's vocab (options is a callable name). */
function vpg_attr_vocab( $def ) { $o = $def['options'] ?? []; return is_string( $o ) && function_exists( $o ) ? $o() : (array) $o; }

/** Read one spot's attributes as a clean array (only set values). */
function vpg_spot_attrs( $post_id ) {
    $out = [];
    foreach ( vpg_spot_attr_schema() as $key => $def ) {
        $v = get_post_meta( $post_id, '_vpg_a_' . $key, true );
        if ( $def['type'] === 'themes' || $def['type'] === 'multi' ) { $v = array_filter( (array) $v ); if ( $v ) $out[ $key ] = array_values( $v ); }
        elseif ( $def['type'] === 'bool' ) { if ( $v === '1' ) $out[ $key ] = true; }
        elseif ( $v !== '' && $v !== null ) { $out[ $key ] = $def['type'] === 'int' ? (int) $v : $v; }
    }
    $seasons = array_filter( array_map( 'intval', (array) get_post_meta( $post_id, '_vpg_a_seasons', true ) ) );
    if ( $seasons ) $out['seasons'] = array_values( array_map( fn( $id ) => wp_get_attachment_image_url( $id, 'medium' ), $seasons ) );
    return $out;
}

/* ─── Metabox ────────────────────────────────────────────────────── */
add_action( 'add_meta_boxes', function () {
    foreach ( VPG_SPOT_ATTR_TYPES as $t ) {
        add_meta_box( 'vpg_spot_attrs', '🧭 ' . __( 'Spot attributes', 'vpg-v2' ), 'vpg_render_spot_attrs_box', $t, 'normal', 'default' );
    }
} );

add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && in_array( $s->post_type ?? '', VPG_SPOT_ATTR_TYPES, true ) ) wp_enqueue_media();
} );

function vpg_render_spot_attrs_box( $post ) {
    wp_nonce_field( 'vpg_spot_attrs', 'vpg_spot_attrs_nonce' );
    $themes  = vpg_spot_themes();
    $set_th  = (array) get_post_meta( $post->ID, '_vpg_a_themes', true );
    $seasons = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, '_vpg_a_seasons', true ) ) );
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px 20px">';
    foreach ( vpg_spot_attr_schema() as $key => $def ) {
        $v = get_post_meta( $post->ID, '_vpg_a_' . $key, true );
        echo '<label style="display:block"><span style="display:block;font-weight:600;font-size:12px;color:#646970;margin-bottom:3px">' . esc_html( $def['label'] ) . '</span>';
        if ( $def['type'] === 'select' ) {
            echo '<select name="vpg_a_' . esc_attr( $key ) . '" style="width:100%">';
            foreach ( $def['options'] as $ov => $ol ) echo '<option value="' . esc_attr( $ov ) . '"' . selected( $v, $ov, false ) . '>' . esc_html( $ol ) . '</option>';
            echo '</select>';
        } elseif ( $def['type'] === 'bool' ) {
            echo '<input type="checkbox" name="vpg_a_' . esc_attr( $key ) . '" value="1"' . checked( $v, '1', false ) . '> ' . esc_html__( 'yes', 'vpg-v2' );
        } elseif ( $def['type'] === 'themes' ) {
            echo '<span style="display:flex;flex-wrap:wrap;gap:6px 12px">';
            foreach ( $themes as $tk => $tl ) echo '<label style="font-weight:400"><input type="checkbox" name="vpg_a_themes[]" value="' . esc_attr( $tk ) . '"' . checked( in_array( $tk, $set_th, true ), true, false ) . '> ' . esc_html( $tl ) . '</label>';
            echo '</span>';
        } elseif ( $def['type'] === 'multi' ) {
            $set_m = (array) get_post_meta( $post->ID, '_vpg_a_' . $key, true );
            echo '<span style="display:flex;flex-wrap:wrap;gap:6px 12px">';
            foreach ( vpg_attr_vocab( $def ) as $mk => $ml ) echo '<label style="font-weight:400"><input type="checkbox" name="vpg_a_' . esc_attr( $key ) . '[]" value="' . esc_attr( $mk ) . '"' . checked( in_array( $mk, $set_m, true ), true, false ) . '> ' . esc_html( $ml ) . '</label>';
            echo '</span>';
        } else {
            echo '<input type="text" name="vpg_a_' . esc_attr( $key ) . '" value="' . esc_attr( $v ) . '" style="width:100%">';
        }
        echo '</label>';
    }
    echo '</div>';
    // 0006 · four-seasons images
    echo '<p style="margin-top:14px"><strong>' . esc_html__( 'Four-seasons photos (same spot across the year)', 'vpg-v2' ) . '</strong></p>';
    echo '<input type="hidden" id="vpg-seasons-ids" name="vpg_a_seasons" value="' . esc_attr( implode( ',', $seasons ) ) . '">';
    echo '<div id="vpg-seasons-prev" style="display:flex;gap:8px;flex-wrap:wrap;margin:6px 0">';
    foreach ( $seasons as $sid ) { $u = wp_get_attachment_image_url( $sid, 'thumbnail' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" style="width:56px;height:56px;object-fit:cover">'; }
    echo '</div><button type="button" class="button" id="vpg-seasons-pick">' . esc_html__( 'Pick seasons photos', 'vpg-v2' ) . '</button>';
    ?>
    <script>
    jQuery(function () {
        var btn = document.getElementById('vpg-seasons-pick'), field = document.getElementById('vpg-seasons-ids'), prev = document.getElementById('vpg-seasons-prev');
        if (!btn || !window.wp || !wp.media) return;
        btn.addEventListener('click', function () {
            var frame = wp.media({ multiple: 'add', library: { type: 'image' } });
            frame.on('select', function () {
                var ids = field.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                frame.state().get('selection').forEach(function (a) { if (ids.indexOf(String(a.id)) === -1) { ids.push(String(a.id)); var img = document.createElement('img'); img.src = a.attributes.sizes && a.attributes.sizes.thumbnail ? a.attributes.sizes.thumbnail.url : a.attributes.url; img.style.cssText = 'width:56px;height:56px;object-fit:cover'; prev.appendChild(img); } });
                field.value = ids.slice(0, 4).join(',');
            });
            frame.open();
        });
    });
    </script>
    <?php
}

/* ─── Save (metabox) ─────────────────────────────────────────────── */
add_action( 'save_post', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_spot_attrs_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_spot_attrs_nonce'], 'vpg_spot_attrs' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    vpg_save_spot_attrs_from_post( $post_id, $_POST );
} );

/** Shared save used by the metabox and the frontend submit handler. */
function vpg_save_spot_attrs_from_post( $post_id, $src ) {
    foreach ( vpg_spot_attr_schema() as $key => $def ) {
        $mk = '_vpg_a_' . $key;
        if ( $def['type'] === 'bool' ) {
            empty( $src[ 'vpg_a_' . $key ] ) ? delete_post_meta( $post_id, $mk ) : update_post_meta( $post_id, $mk, '1' );
        } elseif ( $def['type'] === 'themes' ) {
            $valid = array_keys( vpg_spot_themes() );
            $sel   = array_values( array_intersect( $valid, array_map( 'sanitize_key', (array) ( $src['vpg_a_themes'] ?? [] ) ) ) );
            $sel ? update_post_meta( $post_id, $mk, $sel ) : delete_post_meta( $post_id, $mk );
        } elseif ( $def['type'] === 'multi' ) {
            $valid = array_keys( vpg_attr_vocab( $def ) );
            $sel   = array_values( array_intersect( $valid, array_map( 'sanitize_key', (array) ( $src[ 'vpg_a_' . $key ] ?? [] ) ) ) );
            $sel ? update_post_meta( $post_id, $mk, $sel ) : delete_post_meta( $post_id, $mk );
        } elseif ( $def['type'] === 'select' ) {
            $v = sanitize_text_field( wp_unslash( $src[ 'vpg_a_' . $key ] ?? '' ) );
            $v && isset( $def['options'][ $v ] ) ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
        } elseif ( $def['type'] === 'int' ) {
            $v = (int) ( $src[ 'vpg_a_' . $key ] ?? 0 );
            $v ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
        } else {
            $v = sanitize_text_field( wp_unslash( $src[ 'vpg_a_' . $key ] ?? '' ) );
            $v !== '' ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
        }
    }
    if ( isset( $src['vpg_a_seasons'] ) ) {
        $ids = array_slice( array_filter( array_map( 'intval', explode( ',', (string) $src['vpg_a_seasons'] ) ) ), 0, 4 );
        $ids ? update_post_meta( $post_id, '_vpg_a_seasons', $ids ) : delete_post_meta( $post_id, '_vpg_a_seasons' );
    }
    delete_transient( 'vpg_location_pins_v4' );
}

/* Bust the attribute-aware pin cache on any spot save */
foreach ( VPG_SPOT_ATTR_TYPES as $cpt ) {
    add_action( 'save_post_' . $cpt, function () { delete_transient( 'vpg_location_pins_v4' ); } );
}

/* ─── Popup enrichment · attribute chips + seasons, appended to the
       map popup by the client from pin.attrs (see map-engine.js). Here
       we also render them on the single-spot page via the_content. ── */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( VPG_SPOT_ATTR_TYPES ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $attrs = vpg_spot_attrs( get_the_ID() );
    if ( ! $attrs ) return $content;
    $schema = vpg_spot_attr_schema();
    ob_start(); ?>
    <div style="border-top:1px solid var(--g-line);margin-top:24px;padding-top:18px">
      <p class="g-kicker" style="margin-bottom:10px">● <?php esc_html_e( 'Good to know', 'vpg-v2' ); ?></p>
      <div style="display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ( $attrs as $k => $v ) :
            if ( $k === 'seasons' || $k === 'related' ) continue;
            $def = $schema[ $k ] ?? null; if ( ! $def ) continue;
            if ( $k === 'themes' ) { foreach ( $v as $tk ) { $tl = vpg_spot_themes()[ $tk ] ?? $tk; echo '<span style="border:1px solid var(--g-line);padding:5px 11px;font-size:12px;font-weight:700">#' . esc_html( $tl ) . '</span>'; } continue; }
            if ( $def['type'] === 'multi' ) { $vocab = vpg_attr_vocab( $def ); foreach ( $v as $mk ) echo '<span style="border:1px solid var(--g-line);padding:5px 11px;font-size:12px;font-weight:700">' . esc_html( ( $def['chip'] ?? '' ) . ' ' . ( $vocab[ $mk ] ?? $mk ) ) . '</span>'; continue; }
            $chip = $def['chip'] ?? '·';
            $txt  = $v === true ? ( $def['filter'] ?? $def['label'] ) : ( isset( $def['options'] ) ? ( $def['options'][ $v ] ?? $v ) : ( $def['label'] . ': ' . $v ) );
        ?>
          <span style="border:1px solid var(--g-line);padding:5px 11px;font-size:12px;font-weight:700"><?php echo esc_html( $chip . ' ' . $txt ); ?></span>
        <?php endforeach; ?>
      </div>
      <?php if ( ! empty( $attrs['seasons'] ) ) : ?>
        <p class="g-kicker" style="margin:18px 0 8px">● <?php esc_html_e( 'Across the seasons', 'vpg-v2' ); ?></p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
          <?php foreach ( $attrs['seasons'] as $su ) : if ( ! $su ) continue; ?><img src="<?php echo esc_url( $su ); ?>" alt="" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover"><?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if ( ! empty( $attrs['related'] ) ) :
        $rel = array_filter( array_map( 'intval', explode( ',', $attrs['related'] ) ), fn( $id ) => get_post_status( $id ) === 'publish' );
        if ( $rel ) : ?>
        <p class="g-kicker" style="margin:18px 0 8px">● <?php esc_html_e( 'From here you can also shoot', 'vpg-v2' ); ?></p>
        <ul style="margin:0;padding-left:18px">
          <?php foreach ( $rel as $rid ) : ?><li><a href="<?php echo esc_url( get_permalink( $rid ) ); ?>"><?php echo esc_html( get_the_title( $rid ) ); ?></a></li><?php endforeach; ?>
        </ul>
      <?php endif; endif; ?>
    </div>
    <?php
    return $content . ob_get_clean();
}, 21 );


/* ─── Frontend submit fields · the finder sets what they know on-site ── */
function vpg_render_submit_attrs( $edit_post = null ) {
    $get = fn( $k, $d = '' ) => $edit_post ? get_post_meta( $edit_post->ID, '_vpg_a_' . $k, true ) : $d;
    $schema = vpg_spot_attr_schema();
    // The subset a member can honestly answer standing at the spot.
    $show = [ 'tripod', 'facing', 'crowd', 'entry', 'price', 'focal', 'indoor', 'night', 'stepfree', 'winter', 'toilets', 'drone', 'construction', 'parking', 'station', 'safety', 'standpoint', 'angle', 'history' ];
    ?>
    <div class="g-field" data-for-types="vpg_location vpg_studio vpg_shop" hidden>
      <label><?php esc_html_e( 'Spot attributes · what you noticed there', 'vpg-v2' ); ?></label>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px 18px;margin-top:6px">
        <?php foreach ( $show as $key ) : $def = $schema[ $key ]; $v = $get( $key ); ?>
          <label style="display:block;font-size:13px">
            <span style="display:block;font-weight:600;color:var(--g-mid);margin-bottom:3px;font-size:12px"><?php echo esc_html( ( $def['chip'] ?? '' ) . ' ' . $def['label'] ); ?></span>
            <?php if ( $def['type'] === 'select' ) : ?>
              <select class="g-select" name="vpg_a_<?php echo esc_attr( $key ); ?>" style="width:100%">
                <?php foreach ( $def['options'] as $ov => $ol ) : ?><option value="<?php echo esc_attr( $ov ); ?>" <?php selected( $v, $ov ); ?>><?php echo esc_html( $ol ); ?></option><?php endforeach; ?>
              </select>
            <?php elseif ( $def['type'] === 'bool' ) : ?>
              <label style="font-weight:400"><input type="checkbox" name="vpg_a_<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $v, '1' ); ?>> <?php esc_html_e( 'yes', 'vpg-v2' ); ?></label>
            <?php elseif ( $def['type'] === 'multi' ) : $set_m = (array) $v; ?>
              <span style="display:flex;flex-wrap:wrap;gap:4px 10px">
              <?php foreach ( vpg_attr_vocab( $def ) as $mk => $ml ) : ?><label style="font-weight:400;font-size:12px"><input type="checkbox" name="vpg_a_<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $mk ); ?>" <?php checked( in_array( $mk, $set_m, true ) ); ?>> <?php echo esc_html( $ml ); ?></label><?php endforeach; ?>
              </span>
            <?php else : ?>
              <input class="g-input" type="text" name="vpg_a_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $v ); ?>">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <label style="display:block;margin-top:12px;font-size:13px"><span style="display:block;font-weight:600;color:var(--g-mid);margin-bottom:4px;font-size:12px"><?php esc_html_e( 'Themes', 'vpg-v2' ); ?></span>
        <span style="display:flex;flex-wrap:wrap;gap:6px 14px">
        <?php $set = (array) ( $edit_post ? get_post_meta( $edit_post->ID, '_vpg_a_themes', true ) : [] );
        foreach ( vpg_spot_themes() as $tk => $tl ) : ?>
          <label style="font-weight:400"><input type="checkbox" name="vpg_a_themes[]" value="<?php echo esc_attr( $tk ); ?>" <?php checked( in_array( $tk, $set, true ) ); ?>> <?php echo esc_html( $tl ); ?></label>
        <?php endforeach; ?>
        </span>
      </label>
      <?php foreach ( [ 'genres', 'equip' ] as $mkey ) : $mdef = $schema[ $mkey ]; $mset = (array) $get( $mkey, [] ); ?>
      <label style="display:block;margin-top:12px;font-size:13px"><span style="display:block;font-weight:600;color:var(--g-mid);margin-bottom:4px;font-size:12px"><?php echo esc_html( $mdef['label'] ); ?></span>
        <span style="display:flex;flex-wrap:wrap;gap:6px 14px">
        <?php foreach ( vpg_attr_vocab( $mdef ) as $mk => $ml ) : ?><label style="font-weight:400"><input type="checkbox" name="vpg_a_<?php echo esc_attr( $mkey ); ?>[]" value="<?php echo esc_attr( $mk ); ?>" <?php checked( in_array( $mk, $mset, true ) ); ?>> <?php echo esc_html( $ml ); ?></label><?php endforeach; ?>
        </span>
      </label>
      <?php endforeach; ?>
    </div>
    <?php
}
