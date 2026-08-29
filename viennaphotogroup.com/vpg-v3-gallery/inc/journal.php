<?php
/**
 * VPG v3 — Cluster 07 · Journal & Editorial-Formate.
 *
 * Gives the club journal (standard `post`) a shelf of editorial formats and
 * the craft around them: a rubric taxonomy, a before/after slider, one-image
 * essays, city-sound audio, translation pairs, co-authorship, district-linked
 * long-reads, per-series following, a story pitch, comment highlights,
 * correction transparency, a fact-check ritual, a calm long-form layout, a
 * journal filter, author archives, a public style guide, a chronicle, a
 * member-growable Vienna lexicon, data stories and a question of the week.
 *
 * Reuses: vpg_series taxonomy, the glossary (vpg_glossary), the TTS "Listen"
 * block (0280, already shipped in inc/formats.php), vpg_reading_time(),
 * vpg_notify_user().
 *
 *   0242 one-image · 0243 district portrait · 0244 external interview
 *   0245 law · 0246 tech myth · 0247 before/after · 0248 gear diary
 *   0249 failure · 0250 archive find · 0251 book review · 0252 exhibition
 *   0253 guest · 0254 theme week · 0256 series follow · 0257 comment picks
 *   0258 year read · 0259 translation · 0260 style guide · 0261 pitch
 *   0262 co-author · 0263 fact-check · 0264 long-form · 0265 WIP
 *   0267 caption art · 0268 data stories · 0269 seasonal · 0270 city sounds
 *   0271 portfolio critique · 0272 question of week · 0273 chronicle
 *   0274 obituary · 0275 first time · 0276 lexicon · 0277 corrections
 *   0278 author archive · 0279 journal filter
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Rubric taxonomy on `post` — the editorial formats                */
/* ════════════════════════════════════════════════════════════════ */
function vpg_journal_formats() {
    return [
        'one-image'      => __( 'One-image essay', 'vpg-v2' ),        // 0242
        'district'       => __( 'District portrait', 'vpg-v2' ),      // 0243
        'interview'      => __( 'Interview', 'vpg-v2' ),              // 0244
        'law'            => __( 'Law column', 'vpg-v2' ),             // 0245
        'tech-myth'      => __( 'Tech myth', 'vpg-v2' ),              // 0246
        'before-after'   => __( 'Before / after', 'vpg-v2' ),        // 0247
        'gear-diary'     => __( 'Gear diary', 'vpg-v2' ),            // 0248
        'failure'        => __( 'The failed shoot', 'vpg-v2' ),       // 0249
        'archive-find'   => __( 'Archive find', 'vpg-v2' ),          // 0250
        'book-review'    => __( 'Book review', 'vpg-v2' ),           // 0251
        'exhibition'     => __( 'Exhibition review', 'vpg-v2' ),      // 0252
        'guest'          => __( 'Guest voice', 'vpg-v2' ),           // 0253
        'wip'            => __( 'Work in progress', 'vpg-v2' ),       // 0265
        'caption'        => __( 'Caption art', 'vpg-v2' ),           // 0267
        'data'           => __( 'Data story', 'vpg-v2' ),            // 0268
        'seasonal'       => __( 'In season', 'vpg-v2' ),             // 0269
        'city-sounds'    => __( 'City sounds', 'vpg-v2' ),           // 0270
        'portfolio'      => __( 'Portfolio critique', 'vpg-v2' ),     // 0271
        'obituary'       => __( 'In memoriam', 'vpg-v2' ),           // 0274
        'first-time'     => __( 'First time', 'vpg-v2' ),            // 0275
        'feature'        => __( 'Year feature', 'vpg-v2' ),          // 0258
    ];
}

add_action( 'init', function () {
    register_taxonomy( 'journal_format', 'post', [
        'labels'       => [ 'name' => __( 'Formats', 'vpg-v2' ), 'singular_name' => __( 'Format', 'vpg-v2' ), 'menu_name' => __( 'Journal formats', 'vpg-v2' ) ],
        'public'       => true,
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'rubrik' ],
    ] );
    foreach ( vpg_journal_formats() as $slug => $name ) {
        if ( ! term_exists( $slug, 'journal_format' ) ) wp_insert_term( $name, 'journal_format', [ 'slug' => $slug ] );
    }
}, 9 );

function vpg_post_format_slug( $id ) {
    $t = get_the_terms( $id, 'journal_format' );
    return ( $t && ! is_wp_error( $t ) ) ? $t[0]->slug : '';
}
function vpg_post_format_label( $id ) {
    $s = vpg_post_format_slug( $id );
    return $s ? ( vpg_journal_formats()[ $s ] ?? '' ) : '';
}

/* ════════════════════════════════════════════════════════════════ */
/*  Post metabox · co-author, guest, translation, district, audio,   */
/*  before/after pair, corrections, fact-check                        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-journal', '✎ ' . __( 'Journal craft', 'vpg-v2' ), 'vpg_render_journal_box', 'post', 'normal', 'default' );
} );

function vpg_render_journal_box( $post ) {
    wp_nonce_field( 'vpg_journal', 'vpg_journal_nonce' );
    $co     = (int) get_post_meta( $post->ID, '_vpg_coauthor', true );
    $guest  = (string) get_post_meta( $post->ID, '_vpg_guest_author', true );
    $trans  = (int) get_post_meta( $post->ID, '_vpg_translation', true );
    $dist   = (string) get_post_meta( $post->ID, '_vpg_post_district', true );
    $audio  = (int) get_post_meta( $post->ID, '_vpg_post_audio', true );
    $ba     = (array) get_post_meta( $post->ID, '_vpg_ba', true );
    $fc     = get_post_meta( $post->ID, '_vpg_factchecked', true );
    $corr   = (string) get_post_meta( $post->ID, '_vpg_corrections', true );
    $users  = get_users( [ 'number' => 300, 'orderby' => 'display_name', 'fields' => [ 'ID', 'display_name' ] ] );
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px">
      <div><label style="font-weight:600">👥 <?php esc_html_e( '0262 · Co-author', 'vpg-v2' ); ?></label><br>
        <select name="vpg_coauthor"><option value="0">—</option><?php foreach ( $users as $u ) echo '<option value="' . (int) $u->ID . '"' . selected( $co, $u->ID, false ) . '>' . esc_html( $u->display_name ) . '</option>'; ?></select></div>
      <div><label style="font-weight:600">🙋 <?php esc_html_e( '0253 · Guest byline (non-member)', 'vpg-v2' ); ?></label><br>
        <input type="text" name="vpg_guest_author" value="<?php echo esc_attr( $guest ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Name of the guest writer', 'vpg-v2' ); ?>"></div>
      <div><label style="font-weight:600">🌐 <?php esc_html_e( '0259 · Translation (linked post)', 'vpg-v2' ); ?></label><br>
        <input type="number" name="vpg_translation" value="<?php echo $trans ?: ''; ?>" style="width:120px" placeholder="post ID"><span class="description"><?php esc_html_e( 'The DE/EN counterpart post.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">📍 <?php esc_html_e( '0243 · District (long-read)', 'vpg-v2' ); ?></label><br>
        <input type="text" name="vpg_post_district" value="<?php echo esc_attr( $dist ); ?>" style="width:120px" placeholder="1070"><span class="description"><?php esc_html_e( 'Shows on that /bezirk/ page.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">✅ <?php esc_html_e( '0263 · Places fact-checked vs map', 'vpg-v2' ); ?></label><br>
        <label style="font-weight:400"><input type="checkbox" name="vpg_factchecked" value="1"<?php checked( $fc, '1' ); ?>> <?php esc_html_e( 'Verified before publish', 'vpg-v2' ); ?></label></div>
      <div><label style="font-weight:600">🔊 <?php esc_html_e( '0270 · City-sound audio', 'vpg-v2' ); ?></label><br>
        <input type="hidden" id="vpg-postaudio-id" name="vpg_post_audio" value="<?php echo $audio; ?>">
        <?php if ( $audio ) echo '<audio controls src="' . esc_url( wp_get_attachment_url( $audio ) ) . '" style="max-width:100%;display:block;margin:4px 0"></audio>'; ?>
        <button type="button" class="button button-small" id="vpg-postaudio-pick"><?php esc_html_e( 'Pick audio', 'vpg-v2' ); ?></button></div>
    </div>
    <hr style="margin:12px 0">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px">
      <div><label style="font-weight:600">◐ <?php esc_html_e( '0247/0250 · Before / after images', 'vpg-v2' ); ?></label><br>
        <input type="number" name="vpg_ba[0]" value="<?php echo ! empty( $ba[0] ) ? (int) $ba[0] : ''; ?>" style="width:90px" placeholder="before ID">
        <input type="number" name="vpg_ba[1]" value="<?php echo ! empty( $ba[1] ) ? (int) $ba[1] : ''; ?>" style="width:90px" placeholder="after ID">
        <span class="description"><?php esc_html_e( 'Renders a drag slider. Or use [vpg_ba before=ID after=ID] in the body.', 'vpg-v2' ); ?></span></div>
      <div><label style="font-weight:600">✎ <?php esc_html_e( '0277 · Corrections (one per line)', 'vpg-v2' ); ?></label><br>
        <textarea name="vpg_corrections" rows="2" style="width:100%" placeholder="<?php esc_attr_e( 'e.g. 3 May: corrected the district of the rooftop shot.', 'vpg-v2' ); ?>"><?php echo esc_textarea( $corr ); ?></textarea></div>
    </div>
    <script>
    (function(){var b=document.getElementById('vpg-postaudio-pick'),f=document.getElementById('vpg-postaudio-id');
      if(b&&window.wp&&wp.media)b.addEventListener('click',function(){var fr=wp.media({library:{type:'audio'},multiple:false});
        fr.on('select',function(){var a=fr.state().get('selection').first().toJSON();f.value=a.id;b.insertAdjacentHTML('beforebegin','<audio controls src="'+a.url+'" style="max-width:100%;display:block;margin:4px 0"></audio>');});fr.open();});
    })();
    </script>
    <?php
}

add_action( 'save_post_post', function ( $post_id ) {
    if ( ! isset( $_POST['vpg_journal_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_journal_nonce'], 'vpg_journal' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $co = (int) ( $_POST['vpg_coauthor'] ?? 0 );
    $co ? update_post_meta( $post_id, '_vpg_coauthor', $co ) : delete_post_meta( $post_id, '_vpg_coauthor' );
    foreach ( [ '_vpg_guest_author' => 'vpg_guest_author', '_vpg_post_district' => 'vpg_post_district' ] as $mk => $f ) {
        $v = sanitize_text_field( wp_unslash( $_POST[ $f ] ?? '' ) );
        $v !== '' ? update_post_meta( $post_id, $mk, $v ) : delete_post_meta( $post_id, $mk );
    }
    $tr = (int) ( $_POST['vpg_translation'] ?? 0 );
    $tr ? update_post_meta( $post_id, '_vpg_translation', $tr ) : delete_post_meta( $post_id, '_vpg_translation' );
    $au = (int) ( $_POST['vpg_post_audio'] ?? 0 );
    $au ? update_post_meta( $post_id, '_vpg_post_audio', $au ) : delete_post_meta( $post_id, '_vpg_post_audio' );
    update_post_meta( $post_id, '_vpg_factchecked', empty( $_POST['vpg_factchecked'] ) ? '' : '1' );
    $ba = array_map( 'intval', (array) ( $_POST['vpg_ba'] ?? [] ) );
    ( ! empty( $ba[0] ) && ! empty( $ba[1] ) ) ? update_post_meta( $post_id, '_vpg_ba', [ $ba[0], $ba[1] ] ) : delete_post_meta( $post_id, '_vpg_ba' );
    $corr = sanitize_textarea_field( wp_unslash( $_POST['vpg_corrections'] ?? '' ) );
    $corr !== '' ? update_post_meta( $post_id, '_vpg_corrections', $corr ) : delete_post_meta( $post_id, '_vpg_corrections' );
} );

add_action( 'admin_enqueue_scripts', function () {
    $s = get_current_screen();
    if ( $s && ( $s->post_type ?? '' ) === 'post' ) wp_enqueue_media();
} );

/* 0263 · gentle reminder to fact-check place mentions before publishing */
add_action( 'admin_notices', function () {
    $s = get_current_screen();
    if ( ! $s || $s->base !== 'post' || ( $s->post_type ?? '' ) !== 'post' ) return;
    $pid = (int) ( $_GET['post'] ?? 0 );
    if ( ! $pid || get_post_meta( $pid, '_vpg_factchecked', true ) ) return;
    if ( preg_match( '/1\d{2}0/', (string) get_post_field( 'post_content', $pid ) ) ) {
        echo '<div class="notice notice-info"><p>' . esc_html__( '0263 · This article names a district. Verify the places against the map, then tick “fact-checked” in Journal craft.', 'vpg-v2' ) . '</p></div>';
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0247 · before / after slider (shortcode + meta render)           */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_ba', function ( $atts ) {
    $a = shortcode_atts( [ 'before' => 0, 'after' => 0 ], $atts );
    return vpg_ba_html( (int) $a['before'], (int) $a['after'] );
} );
function vpg_ba_html( $before, $after ) {
    $b = wp_get_attachment_image_url( $before, 'large' );
    $af = wp_get_attachment_image_url( $after, 'large' );
    if ( ! $b || ! $af ) return '';
    $id = 'ba' . $before . $after;
    ob_start(); ?>
    <div class="vpg-ba" id="<?php echo esc_attr( $id ); ?>" style="position:relative;max-width:100%;overflow:hidden;user-select:none;aspect-ratio:3/2">
      <img src="<?php echo esc_url( $af ); ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
      <div class="vpg-ba-top" style="position:absolute;inset:0;width:50%;overflow:hidden"><img src="<?php echo esc_url( $b ); ?>" alt="" style="width:100vw;max-width:none;height:100%;object-fit:cover;display:block"></div>
      <input type="range" min="0" max="100" value="50" class="vpg-ba-range" aria-label="<?php esc_attr_e( 'Reveal edit', 'vpg-v2' ); ?>" style="position:absolute;bottom:10px;left:5%;width:90%">
      <span style="position:absolute;left:8px;top:8px;background:#0B0B0B;color:#fff;font:700 10px/1 sans-serif;padding:4px 6px">RAW</span>
      <span style="position:absolute;right:8px;top:8px;background:#E5341F;color:#fff;font:700 10px/1 sans-serif;padding:4px 6px">EDIT</span>
    </div>
    <script>(function(){var w=document.getElementById('<?php echo esc_js( $id ); ?>');if(!w)return;var t=w.querySelector('.vpg-ba-top'),r=w.querySelector('.vpg-ba-range'),im=t.querySelector('img');function set(v){t.style.width=v+'%';im.style.width=w.offsetWidth+'px';}r.addEventListener('input',function(){set(r.value);});set(50);window.addEventListener('resize',function(){set(r.value);});})();</script>
    <?php return ob_get_clean();
}

/* ════════════════════════════════════════════════════════════════ */
/*  Single-post extras appended after the content                    */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $id = get_the_ID();
    $extra = '';

    // 0247/0250 · before/after from meta (if not already in body)
    $ba = (array) get_post_meta( $id, '_vpg_ba', true );
    if ( ! empty( $ba[0] ) && ! empty( $ba[1] ) && strpos( $content, 'vpg-ba' ) === false ) {
        $extra .= '<figure style="margin:24px 0">' . vpg_ba_html( (int) $ba[0], (int) $ba[1] ) . '</figure>';
    }
    // 0270 · city-sound audio
    $au = (int) get_post_meta( $id, '_vpg_post_audio', true );
    if ( $au ) $extra .= '<p style="margin:20px 0"><span style="font:700 11px/1 sans-serif;letter-spacing:.16em;text-transform:uppercase;color:var(--g-red,#E5341F);display:block;margin-bottom:6px">● ' . esc_html__( 'Listen to the place', 'vpg-v2' ) . '</span><audio controls preload="none" src="' . esc_url( wp_get_attachment_url( $au ) ) . '" style="width:100%;max-width:480px"></audio></p>';

    // 0259 · translation pair
    $tr = (int) get_post_meta( $id, '_vpg_translation', true );
    if ( $tr && get_post_status( $tr ) === 'publish' ) $extra .= '<p style="margin:16px 0"><a href="' . esc_url( get_permalink( $tr ) ) . '" style="font-weight:700">🌐 ' . esc_html__( 'Read this text in the other language', 'vpg-v2' ) . '</a></p>';

    // 0256 · follow this series
    $series = get_the_terms( $id, 'vpg_series' );
    if ( $series && ! is_wp_error( $series ) ) {
        $term = $series[0];
        $following = is_user_logged_in() && in_array( get_current_user_id(), vpg_series_followers( $term->term_id ), true );
        $extra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:20px 0">' . wp_nonce_field( 'vpg_series_follow', '_wpnonce', true, false )
            . '<input type="hidden" name="action" value="vpg_series_follow"><input type="hidden" name="term" value="' . (int) $term->term_id . '">'
            . '<button class="g-btn g-btn--ghost" style="font-size:12px">' . ( $following ? esc_html__( '✓ Following this series', 'vpg-v2' ) : esc_html( sprintf( __( '＋ Follow “%s” — mail me new parts', 'vpg-v2' ), $term->name ) ) ) . '</button></form>';
    }

    // 0257 · editors' picks from the comment thread
    $picks = vpg_comment_highlights( $id );
    if ( $picks ) {
        $extra .= '<aside style="border-left:3px solid var(--g-red,#E5341F);padding-left:16px;margin:24px 0"><p style="font:700 11px/1 sans-serif;letter-spacing:.16em;text-transform:uppercase;color:var(--g-red,#E5341F);margin-bottom:10px">● ' . esc_html__( 'From the thread', 'vpg-v2' ) . '</p>';
        foreach ( $picks as $c ) $extra .= '<blockquote style="margin:0 0 12px;font-size:15px">' . esc_html( $c->comment_content ) . '<footer style="color:var(--g-mid,#6A6A6A);font-size:12px">— ' . esc_html( $c->comment_author ) . '</footer></blockquote>';
        $extra .= '</aside>';
    }

    // 0277 · corrections
    $corr = trim( (string) get_post_meta( $id, '_vpg_corrections', true ) );
    if ( $corr ) {
        $extra .= '<div style="border-top:1px solid var(--g-line,#E6E5E1);margin-top:24px;padding-top:12px;font-size:12px;color:var(--g-mid,#6A6A6A)"><strong style="color:var(--g-ink,#0B0B0B)">' . esc_html__( 'Corrections', 'vpg-v2' ) . '.</strong><ul style="margin:6px 0 0;padding-left:16px">';
        foreach ( array_filter( array_map( 'trim', explode( "\n", $corr ) ) ) as $line ) $extra .= '<li>' . esc_html( $line ) . '</li>';
        $extra .= '</ul></div>';
    }

    return $content . $extra;
}, 24 );

/* 0262 · co-author + 0253 guest byline shown in the byline slot */
function vpg_post_byline_extra( $id ) {
    $out = [];
    $co = (int) get_post_meta( $id, '_vpg_coauthor', true );
    if ( $co && ( $u = get_userdata( $co ) ) ) $out[] = esc_html( sprintf( __( 'with %s', 'vpg-v2' ), $u->display_name ) );
    $guest = get_post_meta( $id, '_vpg_guest_author', true );
    if ( $guest ) $out[] = esc_html( sprintf( __( 'guest · %s', 'vpg-v2' ), $guest ) );
    return $out ? implode( ' · ', $out ) : '';
}

/* 0264 · calm long-form layout for 2000+ words */
add_filter( 'body_class', function ( $c ) {
    if ( is_singular( 'post' ) && str_word_count( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) ) >= 1500 ) $c[] = 'vpg-longform';
    return $c;
} );
add_action( 'wp_head', function () {
    if ( ! is_singular( 'post' ) ) return;
    echo '<style>.vpg-longform .g-prose{font-size:1.08rem;line-height:1.85;max-width:40rem}.vpg-longform .g-prose p{margin-bottom:1.4em}.vpg-ba-range{accent-color:#E5341F}</style>';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0256 · Series following                                          */
/* ════════════════════════════════════════════════════════════════ */
function vpg_series_followers( $term_id ) {
    return array_filter( array_map( 'intval', (array) get_term_meta( $term_id, '_vpg_followers', true ) ) );
}
add_action( 'admin_post_vpg_series_follow', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_series_follow' );
    $term = (int) ( $_POST['term'] ?? 0 );
    if ( ! get_term( $term, 'vpg_series' ) ) wp_die( 'Not found', 404 );
    $uid = get_current_user_id();
    $f = vpg_series_followers( $term );
    $i = array_search( $uid, $f, true );
    if ( $i !== false ) unset( $f[ $i ] ); else $f[] = $uid;
    update_term_meta( $term, '_vpg_followers', array_values( $f ) );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );
/* notify followers when a new part is published in a series */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( $post->post_type !== 'post' || $new !== 'publish' || $old === 'publish' ) return;
    $series = get_the_terms( $post->ID, 'vpg_series' );
    if ( ! $series || is_wp_error( $series ) ) return;
    foreach ( $series as $term ) {
        foreach ( vpg_series_followers( $term->term_id ) as $uid ) {
            if ( $uid === (int) $post->post_author ) continue;
            if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, sprintf( __( 'New in “%s”: %s', 'vpg-v2' ), $term->name, get_the_title( $post ) ), get_permalink( $post ), 'journal' );
        }
    }
}, 10, 3 );

/* ════════════════════════════════════════════════════════════════ */
/*  0257 · Comment highlights (editor marks a comment)               */
/* ════════════════════════════════════════════════════════════════ */
function vpg_comment_highlights( $post_id ) {
    return get_comments( [ 'post_id' => $post_id, 'status' => 'approve', 'meta_key' => '_vpg_highlight', 'meta_value' => '1', 'number' => 4 ] );
}
add_filter( 'comment_row_actions', function ( $actions, $comment ) {
    if ( ! current_user_can( 'moderate_comments' ) ) return $actions;
    $on = get_comment_meta( $comment->comment_ID, '_vpg_highlight', true );
    $url = wp_nonce_url( admin_url( 'admin-post.php?action=vpg_comment_highlight&c=' . $comment->comment_ID ), 'vpg_comment_highlight' );
    $actions['vpg_highlight'] = '<a href="' . esc_url( $url ) . '">' . ( $on ? esc_html__( '★ Un-highlight', 'vpg-v2' ) : esc_html__( '☆ Highlight', 'vpg-v2' ) ) . '</a>';
    return $actions;
}, 10, 2 );
add_action( 'admin_post_vpg_comment_highlight', function () {
    if ( ! current_user_can( 'moderate_comments' ) ) wp_die( 'Forbidden', 403 );
    check_admin_referer( 'vpg_comment_highlight' );
    $c = (int) ( $_GET['c'] ?? 0 );
    $on = get_comment_meta( $c, '_vpg_highlight', true );
    $on ? delete_comment_meta( $c, '_vpg_highlight' ) : update_comment_meta( $c, '_vpg_highlight', '1' );
    wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit-comments.php' ) ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0243 · district long-reads shown on the /bezirk/ page            */
/* ════════════════════════════════════════════════════════════════ */
function vpg_district_reads( $code ) {
    return get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 6, 'meta_key' => '_vpg_post_district', 'meta_value' => $code ] );
}

/* ════════════════════════════════════════════════════════════════ */
/*  0261 · Story pitch  ·  0272 · Question of the week               */
/*  0268 · Data stories ·  0276 · member-grown lexicon              */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_story_pitch', function () {
    if ( ! is_user_logged_in() ) return '<p>' . esc_html__( 'Sign in to pitch a story.', 'vpg-v2' ) . '</p>';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:8px;max-width:520px">
      <?php wp_nonce_field( 'vpg_story_pitch' ); ?><input type="hidden" name="action" value="vpg_story_pitch">
      <input type="text" name="headline" maxlength="90" required placeholder="<?php esc_attr_e( 'Working headline', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)">
      <input type="text" name="angle" maxlength="140" required placeholder="<?php esc_attr_e( 'The angle in one line', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)">
      <textarea name="why" rows="2" maxlength="300" placeholder="<?php esc_attr_e( 'Why now / why you (optional)', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)"></textarea>
      <button class="g-btn g-btn--red" style="font-size:12px;justify-self:start"><?php esc_html_e( 'Send pitch', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_story_pitch', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_story_pitch' );
    $u = wp_get_current_user();
    $p = (array) get_option( 'vpg_story_pitches', [] );
    $p[] = [ 'by' => $u->display_name, 'u' => $u->ID, 'headline' => sanitize_text_field( wp_unslash( $_POST['headline'] ?? '' ) ), 'angle' => sanitize_text_field( wp_unslash( $_POST['angle'] ?? '' ) ), 'why' => sanitize_textarea_field( wp_unslash( $_POST['why'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_story_pitches', array_slice( $p, -200 ), false );
    wp_safe_redirect( ( wp_get_referer() ?: home_url() ) . '?vpg_status=pitch_sent' ); exit;
} );

/* 0268 · a small platform data story */
add_shortcode( 'vpg_platform_stats', function () {
    $counts = [
        __( 'Locations', 'vpg-v2' ) => wp_count_posts( 'vpg_location' )->publish ?? 0,
        __( 'Trails', 'vpg-v2' )    => wp_count_posts( 'vpg_trail' )->publish ?? 0,
        __( 'Events', 'vpg-v2' )    => wp_count_posts( 'vpg_event' )->publish ?? 0,
        __( 'Issues', 'vpg-v2' )    => wp_count_posts( 'vpg_magazine' )->publish ?? 0,
        __( 'Members', 'vpg-v2' )   => count_users()['total_users'] ?? 0,
    ];
    $out = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(110px,1fr));gap:12px;margin:16px 0">';
    foreach ( $counts as $l => $n ) $out .= '<div style="border:1px solid var(--g-line,#E6E5E1);padding:14px;text-align:center"><div style="font-weight:900;font-size:28px">' . (int) $n . '</div><div style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--g-mid,#6A6A6A)">' . esc_html( $l ) . '</div></div>';
    return $out . '</div>';
} );

/* 0272 · question of the week — one question, many short answers */
add_shortcode( 'vpg_question_week', function () {
    $q = get_option( 'vpg_qotw', [] );
    if ( empty( $q['question'] ) ) return '';
    $answers = array_filter( (array) ( $q['answers'] ?? [] ) );
    ob_start(); ?>
    <div style="border:1px solid var(--g-line,#E6E5E1);padding:18px">
      <p style="font:700 11px/1 sans-serif;letter-spacing:.16em;text-transform:uppercase;color:var(--g-red,#E5341F)">● <?php esc_html_e( 'Question of the week', 'vpg-v2' ); ?></p>
      <p style="font-size:20px;font-weight:800;margin:6px 0 12px"><?php echo esc_html( $q['question'] ); ?></p>
      <?php foreach ( array_slice( array_reverse( $answers ), 0, 30 ) as $a ) : ?>
        <p style="margin:0 0 8px;font-size:14px"><?php echo esc_html( $a['text'] ?? '' ); ?> <span style="color:var(--g-mid,#6A6A6A);font-size:12px">— <?php echo esc_html( $a['by'] ?? '' ); ?></span></p>
      <?php endforeach; ?>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;margin-top:10px">
        <?php wp_nonce_field( 'vpg_qotw' ); ?><input type="hidden" name="action" value="vpg_qotw_answer">
        <input type="text" name="answer" maxlength="160" required placeholder="<?php esc_attr_e( 'Your short answer…', 'vpg-v2' ); ?>" style="flex:1;padding:8px;border:1px solid var(--g-line)">
        <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Answer', 'vpg-v2' ); ?></button>
      </form>
      <?php endif; ?>
    </div>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_qotw_answer', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_qotw' );
    $q = get_option( 'vpg_qotw', [] );
    if ( empty( $q['question'] ) ) wp_die( 'No question', 400 );
    $u = wp_get_current_user();
    $q['answers'] = array_filter( (array) ( $q['answers'] ?? [] ), fn( $a ) => ( $a['u'] ?? 0 ) !== $u->ID );
    $q['answers'][] = [ 'u' => $u->ID, 'by' => $u->display_name, 'text' => sanitize_text_field( wp_unslash( $_POST['answer'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_qotw', $q, false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* 0276 · let members suggest a Vienna-lexicon term (editor approves) */
add_shortcode( 'vpg_lexicon_suggest', function () {
    if ( ! is_user_logged_in() ) return '';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
      <?php wp_nonce_field( 'vpg_lexicon' ); ?><input type="hidden" name="action" value="vpg_lexicon_suggest">
      <input type="text" name="term" maxlength="60" required placeholder="<?php esc_attr_e( 'Term', 'vpg-v2' ); ?>" style="padding:8px;border:1px solid var(--g-line)">
      <input type="text" name="def" maxlength="200" required placeholder="<?php esc_attr_e( 'A short definition', 'vpg-v2' ); ?>" style="flex:1;min-width:200px;padding:8px;border:1px solid var(--g-line)">
      <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Suggest a term', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_lexicon_suggest', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_lexicon' );
    $s = (array) get_option( 'vpg_lexicon_pending', [] );
    $s[] = [ 'term' => sanitize_text_field( wp_unslash( $_POST['term'] ?? '' ) ), 'def' => sanitize_text_field( wp_unslash( $_POST['def'] ?? '' ) ), 'by' => wp_get_current_user()->display_name, 't' => time() ];
    update_option( 'vpg_lexicon_pending', array_slice( $s, -100 ), false );
    wp_safe_redirect( ( wp_get_referer() ?: home_url() ) . '?vpg_status=lexicon_sent' ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Editorial admin · style guide, chronicle, QOTW, pitches, lexicon */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php', __( 'Journal desk', 'vpg-v2' ), '✎ ' . __( 'Journal desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-journal-desk', 'vpg_journal_desk' );
} );
function vpg_journal_desk() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['vpg_jd'] ) && check_admin_referer( 'vpg_jd' ) ) {
        update_option( 'vpg_style_guide', sanitize_textarea_field( wp_unslash( $_POST['style_guide'] ?? '' ) ), false );
        update_option( 'vpg_chronicle', sanitize_textarea_field( wp_unslash( $_POST['chronicle'] ?? '' ) ), false );
        $q = get_option( 'vpg_qotw', [] );
        $newq = sanitize_text_field( wp_unslash( $_POST['qotw'] ?? '' ) );
        if ( $newq !== ( $q['question'] ?? '' ) ) $q = [ 'question' => $newq, 'answers' => [] ];
        update_option( 'vpg_qotw', $q, false );
        update_option( 'vpg_year_read', (int) ( $_POST['year_read'] ?? 0 ), false );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    if ( isset( $_GET['approve_term'] ) && check_admin_referer( 'vpg_lex_ok' ) ) {
        $pending = (array) get_option( 'vpg_lexicon_pending', [] );
        $k = (int) $_GET['approve_term'];
        if ( isset( $pending[ $k ] ) ) {
            $gl = (string) get_option( 'vpg_glossary', '' );
            $gl = trim( $gl . "\n" . $pending[ $k ]['term'] . ' | ' . $pending[ $k ]['def'] );
            update_option( 'vpg_glossary', $gl, false );
            unset( $pending[ $k ] );
            update_option( 'vpg_lexicon_pending', array_values( $pending ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Added to the glossary.', 'vpg-v2' ) . '</p></div>';
        }
    }
    ?>
    <div class="wrap"><h1>✎ <?php esc_html_e( 'Journal desk', 'vpg-v2' ); ?></h1>
      <form method="post"><?php wp_nonce_field( 'vpg_jd' ); ?>
        <h2><?php esc_html_e( '0260 · Public style guide', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Shown at /journal-stil/.', 'vpg-v2' ); ?></p>
        <textarea name="style_guide" rows="6" style="width:100%;max-width:760px"><?php echo esc_textarea( get_option( 'vpg_style_guide', '' ) ); ?></textarea>
        <h2 style="margin-top:16px"><?php esc_html_e( '0273 · Chronicle', 'vpg-v2' ); ?></h2>
        <p class="description"><?php esc_html_e( 'One entry per line: “YYYY-MM-DD — what happened”. Shown at /chronik/.', 'vpg-v2' ); ?></p>
        <textarea name="chronicle" rows="6" style="width:100%;max-width:760px"><?php echo esc_textarea( get_option( 'vpg_chronicle', '' ) ); ?></textarea>
        <h2 style="margin-top:16px"><?php esc_html_e( '0272 · Question of the week', 'vpg-v2' ); ?></h2>
        <input type="text" name="qotw" value="<?php echo esc_attr( ( get_option( 'vpg_qotw', [] )['question'] ?? '' ) ); ?>" style="width:100%;max-width:760px" placeholder="<?php esc_attr_e( 'This week’s question (changing it resets answers)', 'vpg-v2' ); ?>">
        <h2 style="margin-top:16px"><?php esc_html_e( '0258 · Year reading piece (post ID)', 'vpg-v2' ); ?></h2>
        <input type="number" name="year_read" value="<?php echo (int) get_option( 'vpg_year_read', 0 ) ?: ''; ?>" style="width:120px">
        <p style="margin-top:12px"><button name="vpg_jd" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>

      <h2 style="margin-top:20px"><?php esc_html_e( '0261 · Story pitches', 'vpg-v2' ); ?></h2>
      <?php $pit = array_reverse( (array) get_option( 'vpg_story_pitches', [] ) ); if ( $pit ) : ?>
        <ul><?php foreach ( array_slice( $pit, 0, 30 ) as $p ) : ?><li><strong><?php echo esc_html( $p['headline'] ?? '' ); ?></strong> — <?php echo esc_html( $p['angle'] ?? '' ); ?> <em><?php echo esc_html( $p['by'] ?? '' ); ?></em></li><?php endforeach; ?></ul>
      <?php else : ?><p class="description"><?php esc_html_e( 'No pitches yet. Embed [vpg_story_pitch] on a page.', 'vpg-v2' ); ?></p><?php endif; ?>

      <h2 style="margin-top:20px"><?php esc_html_e( '0276 · Lexicon suggestions', 'vpg-v2' ); ?></h2>
      <?php $lex = (array) get_option( 'vpg_lexicon_pending', [] ); if ( $lex ) :
        foreach ( $lex as $k => $t ) {
          $u = wp_nonce_url( admin_url( 'admin.php?page=vpg-journal-desk&approve_term=' . $k ), 'vpg_lex_ok' );
          echo '<p><strong>' . esc_html( $t['term'] ) . '</strong> — ' . esc_html( $t['def'] ) . ' <em>' . esc_html( $t['by'] ) . '</em> <a class="button button-small" href="' . esc_url( $u ) . '">' . esc_html__( 'Add to glossary', 'vpg-v2' ) . '</a></p>';
        }
      else : ?><p class="description"><?php esc_html_e( 'No suggestions. Embed [vpg_lexicon_suggest] on the glossary page.', 'vpg-v2' ); ?></p><?php endif; ?>
    </div>
    <?php
}

/* ════════════════════════════════════════════════════════════════ */
/*  Public pages · style guide + chronicle                           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^journal-stil/?$', 'index.php?vpg_styleguide=1', 'top' );
    add_rewrite_rule( '^chronik/?$',      'index.php?vpg_chronicle=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_styleguide'; $v[] = 'vpg_chronicle'; return $v; } );
add_action( 'template_redirect', function () {
    if ( get_query_var( 'vpg_styleguide' ) ) {
        $txt = (string) get_option( 'vpg_style_guide', '' );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'How we write', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>style</em> guide.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose"><?php echo $txt ? wp_kses_post( wpautop( $txt ) ) : '<p>' . esc_html__( 'The style guide is being written.', 'vpg-v2' ) . '</p>'; ?></div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_chronicle' ) ) {
        $lines = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_chronicle', '' ) ) ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'The collective, plainly', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>chronicle</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-list">
          <?php foreach ( $lines as $line ) { $p = array_map( 'trim', explode( '—', $line, 2 ) ); echo '<div class="g-row" style="cursor:default;grid-template-columns:130px 1fr"><span style="font-weight:700;color:var(--g-mid)">' . esc_html( $p[0] ) . '</span><span>' . esc_html( $p[1] ?? '' ) . '</span></div>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0279 · Journal filter — rubric / length / district on the archive */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( ! ( $q->is_home() || $q->is_category() || $q->is_tax( 'journal_format' ) ) ) return;
    $jf = isset( $_GET['jf'] ) ? sanitize_key( $_GET['jf'] ) : '';
    $jd = isset( $_GET['jd'] ) ? sanitize_text_field( $_GET['jd'] ) : '';
    $tax = (array) $q->get( 'tax_query' );
    if ( $jf && array_key_exists( $jf, vpg_journal_formats() ) ) $tax[] = [ 'taxonomy' => 'journal_format', 'field' => 'slug', 'terms' => $jf ];
    if ( $tax ) $q->set( 'tax_query', $tax );
    if ( $jd && preg_match( '/^1\d{2}0$/', $jd ) ) {
        $mq = (array) $q->get( 'meta_query' );
        $mq[] = [ 'key' => '_vpg_post_district', 'value' => $jd ];
        $q->set( 'meta_query', $mq );
    }
    // length filter handled post-query via the_posts (word count isn't a query field)
    if ( isset( $_GET['len'] ) ) $q->set( 'vpg_len', sanitize_key( $_GET['len'] ) );
} );
add_filter( 'the_posts', function ( $posts, $q ) {
    $len = $q->get( 'vpg_len' );
    if ( ! $len || is_admin() ) return $posts;
    return array_values( array_filter( $posts, function ( $p ) use ( $len ) {
        $w = str_word_count( wp_strip_all_tags( $p->post_content ) );
        return $len === 'long' ? $w >= 1200 : ( $len === 'short' ? $w < 400 : true );
    } ) );
}, 10, 2 );

/* the filter bar, injected at the top of the journal loop */
add_action( 'loop_start', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! ( $q->is_home() ) ) return;
    static $done = false; if ( $done ) return; $done = true;
    $cur = isset( $_GET['jf'] ) ? sanitize_key( $_GET['jf'] ) : '';
    echo '<section class="g-wrap" style="margin:16px auto"><form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;font-size:12px">';
    echo '<span style="font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Filter', 'vpg-v2' ) . '</span>';
    echo '<select name="jf" onchange="this.form.submit()" style="padding:6px"><option value="">' . esc_html__( 'All formats', 'vpg-v2' ) . '</option>';
    foreach ( vpg_journal_formats() as $slug => $name ) echo '<option value="' . esc_attr( $slug ) . '"' . selected( $cur, $slug, false ) . '>' . esc_html( $name ) . '</option>';
    echo '</select>';
    $len = isset( $_GET['len'] ) ? sanitize_key( $_GET['len'] ) : '';
    echo '<select name="len" onchange="this.form.submit()" style="padding:6px"><option value="">' . esc_html__( 'Any length', 'vpg-v2' ) . '</option>';
    foreach ( [ 'short' => __( 'Short', 'vpg-v2' ), 'long' => __( 'Long read', 'vpg-v2' ) ] as $lv => $ll ) echo '<option value="' . esc_attr( $lv ) . '"' . selected( $len, $lv, false ) . '>' . esc_html( $ll ) . '</option>';
    echo '</select>';
    echo '<input type="text" name="jd" value="' . esc_attr( isset( $_GET['jd'] ) ? sanitize_text_field( $_GET['jd'] ) : '' ) . '" placeholder="' . esc_attr__( 'District (1070)', 'vpg-v2' ) . '" style="padding:6px;width:110px">';
    echo '<button class="g-btn g-btn--ghost" style="font-size:12px">' . esc_html__( 'Go', 'vpg-v2' ) . '</button>';
    echo '</form></section>';
}, 5 );
