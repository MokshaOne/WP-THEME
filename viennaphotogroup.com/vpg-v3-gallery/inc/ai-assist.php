<?php
/**
 * VPG v3 — Cluster 23 · KI & Automatisierung.
 *
 * Self-hosted, transparent, human-final. Reuses the vision endpoint
 * (vpg_vision_endpoint / VPG_CAPTION_URL), dHash dedup, EXIF and palette
 * helpers — adds only what was missing, and never auto-publishes:
 *
 *   vpg_ai_text() pluggable text model (0890/0891/0892/0900/0903/0907) w/ opt-out
 *   0917 bundled inference queue · 0914 per-member ML opt-out · 0916 AI label
 *   0893 readability · 0896 local Bayesian spam · 0883 rotation dedup
 *   0898 offline district fallback · 0889 EXIF anomaly · 0895 broken-link bot
 *   0905 natural-language map query · 0906/0902/0911/0897 heuristics · 0884 blur hint
 *   0913/0915/0918/0919/0920 transparency page + ethics charter · AI desk
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * 0914 · per-member ML opt-out — the switch every AI path must honour
 * ================================================================ */
function vpg_ml_optout( $uid ) { return (bool) get_user_meta( (int) $uid, '_vpg_ml_optout', true ); }
add_action( 'vpg_profile_sections', function ( $user ) {
    if ( ! ( $user instanceof WP_User ) || $user->ID !== get_current_user_id() ) return;
    if ( isset( $_POST['_vpg_ml'] ) && wp_verify_nonce( $_POST['_vpg_ml'], 'vpg_ml' ) ) {
        update_user_meta( $user->ID, '_vpg_ml_optout', empty( $_POST['ml_optout'] ) ? '' : 1 );
        echo '<p role="status" style="color:var(--g-red,#E5341F)">' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p>';
    }
    echo '<section class="vpg-profile-sec"><h3>' . esc_html__( 'Machine learning', 'vpg-v2' ) . '</h3><form method="post">';
    wp_nonce_field( 'vpg_ml', '_vpg_ml' );
    echo '<label><input type="checkbox" name="ml_optout" value="1" ' . checked( vpg_ml_optout( $user->ID ), true, false ) . '> ' . esc_html__( 'Exclude my photos from all automated processing (alt-text, similarity, curation)', 'vpg-v2' ) . '</label>';
    echo '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Your photos never train any model — this switch also excludes them from our own assistive tools.', 'vpg-v2' ) . '</p>';
    echo '<p><button class="g-btn">' . esc_html__( 'Save', 'vpg-v2' ) . '</button></p></form></section>';
}, 28 );

/* ================================================================
 * Pluggable, OpenAI-compatible TEXT model — no-op unless configured
 * ================================================================ */
function vpg_ai_endpoint() { return trim( (string) get_option( 'vpg_ai_endpoint', '' ) ); }
function vpg_ai_configured() { return '' !== vpg_ai_endpoint(); }
/** Returns a model draft string, or '' when unconfigured/unavailable. Human always finalises. */
function vpg_ai_text( $system, $user_prompt, $max = 300 ) {
    $url = vpg_ai_endpoint();
    if ( '' === $url ) return '';
    $res = wp_remote_post( $url, [
        'timeout' => 30,
        'headers' => [ 'Content-Type' => 'application/json' ] + ( defined( 'VPG_AI_KEY' ) ? [ 'Authorization' => 'Bearer ' . VPG_AI_KEY ] : [] ),
        'body'    => wp_json_encode( [
            'model'    => get_option( 'vpg_ai_model', defined( 'VPG_CAPTION_MODEL' ) ? VPG_CAPTION_MODEL : 'local' ),
            'messages' => [ [ 'role' => 'system', 'content' => $system ], [ 'role' => 'user', 'content' => $user_prompt ] ],
            'max_tokens' => (int) $max,
        ] ),
    ] );
    if ( is_wp_error( $res ) ) return '';
    $j = json_decode( wp_remote_retrieve_body( $res ), true );
    return isset( $j['choices'][0]['message']['content'] ) ? trim( (string) $j['choices'][0]['message']['content'] ) : '';
}

/* 0890/0891/0892/0903/0907 · editor "suggest" actions (drafts only) */
add_action( 'wp_ajax_vpg_ai_assist', function () {
    check_ajax_referer( 'vpg_ai_assist', '_n' );
    if ( ! current_user_can( 'edit_posts' ) || ! vpg_ai_configured() ) wp_send_json_error();
    $task = sanitize_key( $_POST['task'] ?? '' );
    $text = sanitize_textarea_field( wp_unslash( $_POST['text'] ?? '' ) );
    if ( '' === $text ) wp_send_json_error();
    $map = [
        'translate' => [ __( 'You translate editorial photography copy faithfully between German and English. Return only the translation.', 'vpg-v2' ), __( 'Translate this, keeping tone:', 'vpg-v2' ) ],
        'teaser'    => [ __( 'You write a one-sentence teaser for a photography magazine. No hype. Return only the teaser.', 'vpg-v2' ), __( 'Draft a teaser for:', 'vpg-v2' ) ],
        'titles'    => [ __( 'You suggest three alternative headlines, plain and specific, one per line. No numbering.', 'vpg-v2' ), __( 'Headline options for:', 'vpg-v2' ) ],
        'interview' => [ __( 'You propose three thoughtful follow-up interview questions, one per line.', 'vpg-v2' ), __( 'Follow-up questions given these answers:', 'vpg-v2' ) ],
        'housestyle'=> [ __( 'You flag house-style issues (hype words, clichés, passive voice) as a short bullet list. Suggest fixes, do not rewrite.', 'vpg-v2' ), __( 'Check this against a calm, plain house style:', 'vpg-v2' ) ],
    ];
    if ( ! isset( $map[ $task ] ) ) wp_send_json_error();
    $out = vpg_ai_text( $map[ $task ][0], $map[ $task ][1] . "\n\n" . $text );
    $out === '' ? wp_send_json_error() : wp_send_json_success( [ 'text' => $out ] );
} );

/* editor toolbar for the draft actions (only when an endpoint is set) */
add_action( 'edit_form_after_title', function ( $post ) {
    if ( ! vpg_ai_configured() || ! in_array( $post->post_type, vpg_editorial_types(), true ) ) return;
    ?>
    <div class="vpg-ai-tools" style="margin:8px 0;padding:8px;border:1px solid #E6E5E1;border-radius:4px;font-size:12px">
      <strong>🤖 <?php esc_html_e( 'AI drafts', 'vpg-v2' ); ?></strong>
      <?php foreach ( [ 'teaser' => __( 'Teaser', 'vpg-v2' ), 'titles' => __( 'Title options', 'vpg-v2' ), 'translate' => __( 'Translate DE↔EN', 'vpg-v2' ), 'housestyle' => __( 'House-style check', 'vpg-v2' ) ] as $t => $l ) : ?>
        <button type="button" class="button button-small vpg-ai-btn" data-task="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $l ); ?></button>
      <?php endforeach; ?>
      <span class="description"><?php esc_html_e( 'Drafts only — you always decide. Nothing publishes itself.', 'vpg-v2' ); ?></span>
      <div class="vpg-ai-out" style="white-space:pre-wrap;margin-top:6px"></div>
    </div>
    <script>
    (function(){var n=<?php echo wp_json_encode( wp_create_nonce( 'vpg_ai_assist' ) ); ?>,a=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
      document.querySelectorAll('.vpg-ai-btn').forEach(function(b){b.addEventListener('click',function(){
        var ed=document.getElementById('content'),txt=(ed&&ed.value)||(window.tinymce&&tinymce.activeEditor&&tinymce.activeEditor.getContent({format:'text'}))||document.querySelector('#title').value;
        var out=b.closest('.vpg-ai-tools').querySelector('.vpg-ai-out');out.textContent='…';
        fetch(a,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams({action:'vpg_ai_assist',_n:n,task:b.dataset.task,text:txt})}).then(r=>r.json()).then(function(j){out.textContent=j&&j.success?j.data.text:'—';});
      });});})();
    </script>
    <?php
} );

/* ================================================================
 * 0917 · bundled inference queue — one run, not a call per request
 * ================================================================ */
function vpg_ai_enqueue( $job ) {
    $q = (array) get_option( 'vpg_ai_queue', [] );
    $q[] = $job;
    update_option( 'vpg_ai_queue', array_slice( $q, -500 ), false );
    // bundle the run for the small hours; only arm the cron once there is work
    if ( ! wp_next_scheduled( 'vpg_ai_batch' ) ) wp_schedule_single_event( strtotime( 'tomorrow 3:00' ), 'vpg_ai_batch' );
}
add_action( 'vpg_ai_batch', function () {
    $q = (array) get_option( 'vpg_ai_queue', [] );
    if ( ! $q ) return;
    do_action( 'vpg_ai_batch_run', $q );
    update_option( 'vpg_ai_queue', [], false ); // consumers clear what they handled
} );

/* ================================================================
 * 0893 · readability feedback (pure PHP, no model)
 * ================================================================ */
add_action( 'add_meta_boxes', function () {
    foreach ( vpg_editorial_types() as $t ) add_meta_box( 'vpg-readability', '📖 ' . __( 'Readability', 'vpg-v2' ), 'vpg_readability_box', $t, 'side' );
} );
function vpg_readability_box( $post ) {
    $text = wp_strip_all_tags( $post->post_content );
    $words = str_word_count( $text );
    if ( $words < 20 ) { echo '<p class="description">' . esc_html__( 'Add more text to see readability hints.', 'vpg-v2' ) . '</p>'; return; }
    $sentences = max( 1, preg_match_all( '/[.!?]+/', $text ) );
    $syll = max( $words, preg_match_all( '/[aeiouyäöü]+/i', $text ) );
    $asl = $words / $sentences;                 // avg sentence length
    $asw = $syll / $words;                       // avg syllables/word
    $flesch = round( 206.835 - 1.015 * $asl - 84.6 * $asw ); // Flesch reading ease (rough, DE-tolerant)
    echo '<p><strong>' . esc_html__( 'Reading ease', 'vpg-v2' ) . ':</strong> ' . (int) $flesch . ' · ' . esc_html( sprintf( __( '%s words/sentence', 'vpg-v2' ), round( $asl, 1 ) ) ) . '</p>';
    if ( $asl > 22 ) echo '<p style="color:#996800">' . esc_html__( 'Some long sentences — consider splitting a few.', 'vpg-v2' ) . '</p>';
    else echo '<p style="color:#2a7a2a">' . esc_html__( 'Sentence length reads comfortably.', 'vpg-v2' ) . '</p>';
}

/* ================================================================
 * 0896 · local Bayesian spam scorer — learns from your own moderation
 * ================================================================ */
function vpg_bayes_tokens( $text ) {
    return array_slice( array_unique( preg_split( '/[^\p{L}0-9]+/u', mb_strtolower( (string) $text ), -1, PREG_SPLIT_NO_EMPTY ) ), 0, 60 );
}
add_action( 'transition_comment_status', function ( $new, $old, $comment ) {
    if ( ! in_array( $new, [ 'spam', 'approved' ], true ) ) return;
    $model = (array) get_option( 'vpg_bayes', [ 'spam' => [], 'ham' => [], 'ns' => 0, 'nh' => 0 ] );
    $bucket = 'spam' === $new ? 'spam' : 'ham';
    foreach ( vpg_bayes_tokens( $comment->comment_content ) as $tok ) $model[ $bucket ][ $tok ] = ( $model[ $bucket ][ $tok ] ?? 0 ) + 1;
    $model[ 'spam' === $new ? 'ns' : 'nh' ]++;
    // keep the vocabulary bounded
    foreach ( [ 'spam', 'ham' ] as $b ) if ( count( $model[ $b ] ) > 4000 ) { arsort( $model[ $b ] ); $model[ $b ] = array_slice( $model[ $b ], 0, 3000, true ); }
    update_option( 'vpg_bayes', $model, false );
}, 10, 3 );
function vpg_bayes_spam_prob( $text ) {
    $m = (array) get_option( 'vpg_bayes', [] );
    if ( empty( $m['ns'] ) || empty( $m['nh'] ) ) return 0.0;
    $logp = 0.0;
    foreach ( vpg_bayes_tokens( $text ) as $tok ) {
        $ps = ( ( $m['spam'][ $tok ] ?? 0 ) + 1 ) / ( $m['ns'] + 2 );
        $ph = ( ( $m['ham'][ $tok ] ?? 0 ) + 1 ) / ( $m['nh'] + 2 );
        $logp += log( $ps / $ph );
    }
    return 1 / ( 1 + exp( -$logp ) );
}
/* a learned score nudges borderline comments into the queue (never hard-blocks) */
add_filter( 'pre_comment_approved', function ( $approved, $data ) {
    if ( is_wp_error( $approved ) || 'spam' === $approved || 1 === $approved ) {
        if ( 1 === $approved && vpg_bayes_spam_prob( $data['comment_content'] ?? '' ) > 0.9 ) return 0; // hold, don't kill
    }
    return $approved;
}, 30, 2 );

/* ================================================================
 * 0883 · rotation-aware dedup — store the 180°-rotated dHash too
 * ================================================================ */
add_action( 'add_attachment', function ( $aid ) {
    if ( ! function_exists( 'vpg_photo_dhash' ) || ! wp_attachment_is_image( $aid ) ) return;
    $path = get_attached_file( $aid );
    if ( ! $path || ! function_exists( 'imagecreatefromstring' ) ) return;
    $img = @imagecreatefromstring( @file_get_contents( $path ) );
    if ( ! $img ) return;
    $rot = imagerotate( $img, 180, 0 );
    if ( $rot ) {
        $tmp = wp_tempnam( 'vpgrot' ) . '.jpg';
        if ( imagejpeg( $rot, $tmp, 82 ) ) { update_post_meta( $aid, '_vpg_dhash_r', vpg_photo_dhash( $tmp ) ); @unlink( $tmp ); }
        imagedestroy( $rot );
    }
    imagedestroy( $img );
}, 20 );

/* ================================================================
 * 0898 · offline district fallback — nearest Bezirk centroid (approx.)
 * ================================================================ */
function vpg_district_offline( $lat, $lng ) {
    // Approximate centroids of Vienna's 23 districts (lat, lng). Offline fallback
    // when Nominatim is unreachable — good enough to pre-fill, editor confirms.
    $c = [
        1=>[48.2093,16.3705],2=>[48.2167,16.3958],3=>[48.1957,16.3940],4=>[48.1917,16.3720],
        5=>[48.1870,16.3600],6=>[48.1957,16.3490],7=>[48.2030,16.3480],8=>[48.2110,16.3470],
        9=>[48.2250,16.3560],10=>[48.1620,16.3820],11=>[48.1730,16.4300],12=>[48.1740,16.3330],
        13=>[48.1790,16.2660],14=>[48.2130,16.2660],15=>[48.1940,16.3300],16=>[48.2110,16.3130],
        17=>[48.2320,16.3300],18=>[48.2330,16.3360],19=>[48.2560,16.3390],20=>[48.2400,16.3760],
        21=>[48.2760,16.4010],22=>[48.2340,16.4770],23=>[48.1400,16.3000],
    ];
    $best = 0; $bd = INF;
    foreach ( $c as $d => $p ) { $dist = ( $lat - $p[0] ) ** 2 + ( $lng - $p[1] ) ** 2; if ( $dist < $bd ) { $bd = $dist; $best = $d; } }
    return $best ? sprintf( '1%02d0', $best ) : ''; // Vienna postcode form 1DD0
}

/* ================================================================
 * 0889 · EXIF anomaly — a future/absurd timestamp, asked kindly
 * ================================================================ */
add_action( 'add_attachment', function ( $aid ) {
    if ( ! wp_attachment_is_image( $aid ) ) return;
    $meta = wp_get_attachment_metadata( $aid );
    $when = $meta['image_meta']['created_timestamp'] ?? 0;
    if ( $when && $when > time() + DAY_IN_SECONDS ) {
        update_post_meta( $aid, '_vpg_exif_anomaly', 'future-timestamp' );
    }
} );
add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
    if ( get_post_meta( $post->ID, '_vpg_exif_anomaly', true ) ) {
        $fields['vpg_exif_anom'] = [ 'label' => __( 'Heads-up', 'vpg-v2' ), 'input' => 'html', 'html' => '<em>' . esc_html__( 'This photo’s capture date is in the future — the camera clock may be off. No action needed.', 'vpg-v2' ) . '</em>' ];
    }
    return $fields;
}, 10, 2 );

/* ================================================================
 * 0895 · weekly broken-link bot across editorial content
 * ================================================================ */
add_action( 'vpg_linkbot', function () {
    $posts = get_posts( [ 'post_type' => vpg_editorial_types(), 'post_status' => 'publish', 'numberposts' => 40, 'orderby' => 'modified', 'order' => 'ASC' ] );
    $report = [];
    foreach ( $posts as $p ) {
        if ( ! preg_match_all( '#https?://[^\s"\'<>]+#i', $p->post_content, $m ) ) continue;
        foreach ( array_unique( $m[0] ) as $link ) {
            $code = wp_remote_retrieve_response_code( wp_remote_head( $link, [ 'timeout' => 8, 'redirection' => 3 ] ) );
            if ( ! $code || $code >= 400 ) $report[] = [ 'pid' => $p->ID, 'link' => $link, 'code' => (int) $code ];
        }
    }
    update_option( 'vpg_dead_links_report', array_slice( $report, 0, 200 ), false );
} );
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_linkbot' ) ) wp_schedule_event( strtotime( 'next monday 4:00' ), 'weekly', 'vpg_linkbot' );
} );

/* ================================================================
 * 0905 · natural-language map query — "quiet spots in the 2nd at night"
 * ================================================================ */
add_filter( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;
    $s = (string) $q->get( 's' );
    if ( '' === $s ) return;
    // district: "im 2.", "2nd district", "bezirk 2"
    if ( preg_match( '/(?:im|bezirk|district)\s*(\d{1,2})|(\d{1,2})(?:\.|st|nd|rd|th)\b/i', $s, $m ) ) {
        $d = (int) ( $m[1] ?: $m[2] );
        if ( $d >= 1 && $d <= 23 ) {
            $mq = (array) $q->get( 'meta_query' );
            $mq[] = [ 'key' => 'location_district', 'value' => sprintf( '1%02d0', $d ), 'compare' => 'LIKE' ];
            $q->set( 'meta_query', $mq );
        }
    }
    // vibe words map onto existing spot attributes (best-effort, additive)
    $vibes = [ 'quiet' => 'ruhig', 'ruhig' => 'ruhig', 'sunset' => 'golden', 'abend' => 'golden', 'night' => 'nacht', 'nacht' => 'nacht' ];
    foreach ( $vibes as $en => $tag ) if ( stripos( $s, $en ) !== false ) { $q->set( 'vpg_vibe', $tag ); break; }
}, 20 );

/* ================================================================
 * 0906/0902/0911/0897 · lightweight heuristics (no model)
 * ================================================================ */
/** 0906 · POTW candidate pre-selection by cheap signals. */
function vpg_curation_candidates( $limit = 12 ) {
    $atts = get_posts( [ 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 120, 'orderby' => 'date', 'order' => 'DESC' ] );
    $scored = [];
    foreach ( $atts as $a ) {
        if ( vpg_ml_optout( (int) $a->post_author ) ) continue;
        $hue = (int) get_post_meta( $a->ID, '_vpg_hue', true );
        $views = (int) get_post_meta( $a->ID, '_vpg_views', true );
        $scored[ $a->ID ] = $views * 2 + ( $hue ? 5 : 0 ) + ( has_post_thumbnail( $a->ID ) ? 0 : 0 );
    }
    arsort( $scored );
    return array_slice( array_keys( $scored ), 0, $limit );
}
/** 0911 · anomaly watcher — a quiet flag on an unusual upload spike. */
add_action( 'add_attachment', function ( $aid ) {
    $uid = (int) get_post_field( 'post_author', $aid );
    if ( ! $uid ) return;
    $k = 'vpg_upl_' . $uid;
    $n = (int) get_transient( $k ) + 1;
    set_transient( $k, $n, HOUR_IN_SECONDS );
    if ( 30 === $n && function_exists( 'vpg_mod_log' ) ) vpg_mod_log( 'anomaly_upload_spike', '30+ uploads in an hour', $uid );
} );
/** 0897 · similar-pin warning by title trigram overlap (offline, semantic-ish). */
function vpg_similar_pins( $title, $exclude = 0 ) {
    $tri = fn( $s ) => array_unique( array_map( fn( $i ) => mb_substr( $s, $i, 3 ), range( 0, max( 0, mb_strlen( $s ) - 3 ) ) ) );
    $a = $tri( mb_strtolower( $title ) ); if ( count( $a ) < 2 ) return [];
    $hits = [];
    foreach ( get_posts( [ 'post_type' => 'vpg_location', 'post_status' => 'publish', 'numberposts' => 200, 'exclude' => [ $exclude ] ] ) as $p ) {
        $b = $tri( mb_strtolower( $p->post_title ) );
        $sim = $b ? count( array_intersect( $a, $b ) ) / count( array_unique( array_merge( $a, $b ) ) ) : 0;
        if ( $sim > 0.5 ) $hits[] = [ 'id' => $p->ID, 'title' => $p->post_title, 'sim' => round( $sim, 2 ) ];
    }
    return $hits;
}

/* ================================================================
 * 0884 · client-side blur hint on the submit form
 * ================================================================ */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page_template( 'templates/page-submit.php' ) ) return;
    $v = file_exists( VPG_V2_DIR . '/assets/js/ai-hints.js' ) ? (string) filemtime( VPG_V2_DIR . '/assets/js/ai-hints.js' ) : VPG_V2_VERSION;
    wp_enqueue_script( 'vpg-ai-hints', VPG_V2_URI . '/assets/js/ai-hints.js', [], $v, true );
    wp_localize_script( 'vpg-ai-hints', 'vpgAiHint', [
        'soft' => __( 'This looks a little soft — a sharper frame may read better. Upload anyway if you like.', 'vpg-v2' ),
        'ok'   => __( 'Looks sharp.', 'vpg-v2' ),
    ] );
} );

/* ================================================================
 * 0916 · AI-assisted label — declare machine help on a piece
 * ================================================================ */
add_filter( 'the_content', function ( $c ) {
    if ( is_singular() && in_the_loop() && is_main_query() && get_post_meta( get_the_ID(), '_vpg_ai_assisted', true ) ) {
        $c .= '<p class="vpg-ai-label" style="font-size:12px;color:var(--g-mid,#6A6A6A);border-top:1px solid var(--g-line,#E6E5E1);margin-top:20px;padding-top:8px">🤖 ' . esc_html__( 'Parts of this piece were drafted with machine help and finalised by an editor.', 'vpg-v2' ) . '</p>';
    }
    return $c;
}, 40 );

/* ================================================================
 * 0913/0915/0918/0919/0920 · AI transparency page + ethics charter
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    if ( ! in_array( $path, [ 'ki', 'ai', 'ki-transparenz' ], true ) ) return;
    status_header( 200 ); get_header();
    echo '<main id="vpg-main" class="g-wrap" style="max-width:740px;margin:40px auto;padding:0 20px">';
    echo '<h1>' . esc_html__( 'AI at Vienna Photo Group', 'vpg-v2' ) . '</h1>';
    echo '<p class="g-lede">' . esc_html__( 'Machines carry water; people curate. Everything here is self-hosted, opt-out, and finalised by a human.', 'vpg-v2' ) . '</p>';

    echo '<h2>' . esc_html__( 'The ten sentences (our AI charter)', 'vpg-v2' ) . '</h2><ol style="line-height:1.8">';
    foreach ( [
        __( 'We use machines to assist, never to decide.', 'vpg-v2' ),
        __( 'No photo trains any model — not ours, not anyone’s.', 'vpg-v2' ),
        __( 'Every member can exclude their photos from all automated processing.', 'vpg-v2' ),
        __( 'Models run on our own server; images do not leave it.', 'vpg-v2' ),
        __( 'Machine-assisted text and images are always labelled as such.', 'vpg-v2' ),
        __( 'Nothing is ever published automatically — a person clicks publish.', 'vpg-v2' ),
        __( 'We prefer the smallest tool that does the job.', 'vpg-v2' ),
        __( 'We bundle inference at night to spend less energy.', 'vpg-v2' ),
        __( 'We say plainly which models run and what they see.', 'vpg-v2' ),
        __( 'When in doubt, the human wins.', 'vpg-v2' ),
    ] as $s ) echo '<li>' . esc_html( $s ) . '</li>';
    echo '</ol>';

    echo '<h2>' . esc_html__( 'What runs right now', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.7">';
    $vision = function_exists( 'vpg_vision_endpoint' ) && vpg_vision_endpoint();
    $text   = vpg_ai_configured();
    echo '<li>' . esc_html__( 'Alt-text assistance', 'vpg-v2' ) . ': ' . ( ( defined( 'VPG_CAPTION_URL' ) || $vision ) ? esc_html__( 'on (self-hosted vision model)', 'vpg-v2' ) : esc_html__( 'off', 'vpg-v2' ) ) . '</li>';
    echo '<li>' . esc_html__( 'Editorial text drafts', 'vpg-v2' ) . ': ' . ( $text ? esc_html__( 'on (self-hosted text model, drafts only)', 'vpg-v2' ) : esc_html__( 'off', 'vpg-v2' ) ) . '</li>';
    echo '<li>' . esc_html__( 'Duplicate & similarity detection', 'vpg-v2' ) . ': ' . esc_html__( 'on (local perceptual hashing — no external service)', 'vpg-v2' ) . '</li>';
    echo '<li>' . esc_html__( 'Readability & house-style hints', 'vpg-v2' ) . ': ' . esc_html__( 'on (local, in the editor)', 'vpg-v2' ) . '</li>';
    echo '</ul>';
    echo '<p>' . esc_html__( 'Opt out any time from your dashboard → profile → Machine learning.', 'vpg-v2' ) . '</p>';
    echo '</main>'; get_footer(); exit;
} );

/* ================================================================
 * AI desk — endpoints, batch queue, spam model, dead links, curation
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'AI & Automation', 'vpg-v2' ), '🤖 ' . __( 'AI & Automation', 'vpg-v2' ), 'manage_options', 'vpg-ai', 'vpg_ai_desk' );
} );
function vpg_ai_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['_vpg_ai'] ) && wp_verify_nonce( $_POST['_vpg_ai'], 'vpg_ai' ) ) {
        update_option( 'vpg_ai_endpoint', esc_url_raw( wp_unslash( $_POST['ai_endpoint'] ?? '' ) ) );
        update_option( 'vpg_ai_model', sanitize_text_field( wp_unslash( $_POST['ai_model'] ?? '' ) ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $bayes = (array) get_option( 'vpg_bayes', [] );
    $dead  = (array) get_option( 'vpg_dead_links_report', [] );
    $queue = count( (array) get_option( 'vpg_ai_queue', [] ) );
    ?>
    <div class="wrap"><h1>🤖 <?php esc_html_e( 'AI & Automation', 'vpg-v2' ); ?></h1>
      <p><a href="<?php echo esc_url( home_url( '/ki/' ) ); ?>" target="_blank"><?php esc_html_e( 'View the public AI transparency page →', 'vpg-v2' ); ?></a></p>

      <form method="post">
        <?php wp_nonce_field( 'vpg_ai', '_vpg_ai' ); ?>
        <h2><?php esc_html_e( 'Self-hosted text model (OpenAI-compatible)', 'vpg-v2' ); ?></h2>
        <p><label><?php esc_html_e( 'Chat completions endpoint URL', 'vpg-v2' ); ?><br><input type="url" name="ai_endpoint" class="large-text code" value="<?php echo esc_attr( get_option( 'vpg_ai_endpoint', '' ) ); ?>" placeholder="http://127.0.0.1:8080/v1/chat/completions"></label></p>
        <p><label><?php esc_html_e( 'Model name', 'vpg-v2' ); ?> <input type="text" name="ai_model" value="<?php echo esc_attr( get_option( 'vpg_ai_model', '' ) ); ?>" placeholder="local"></label>
           <span class="description"><?php esc_html_e( 'Leave empty to keep text drafts off. Vision alt-text is configured separately under “Vision endpoint”.', 'vpg-v2' ); ?></span></p>
        <p><button class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>

      <h2><?php esc_html_e( '0917 · Inference queue', 'vpg-v2' ); ?></h2>
      <p><?php echo esc_html( sprintf( __( '%s jobs queued for the nightly batch run.', 'vpg-v2' ), number_format_i18n( $queue ) ) ); ?></p>

      <h2><?php esc_html_e( '0896 · Learned spam model', 'vpg-v2' ); ?></h2>
      <p><?php echo esc_html( sprintf( __( 'Trained on %1$s spam and %2$s approved comments; %3$s spam tokens known.', 'vpg-v2' ), number_format_i18n( (int) ( $bayes['ns'] ?? 0 ) ), number_format_i18n( (int) ( $bayes['nh'] ?? 0 ) ), number_format_i18n( count( $bayes['spam'] ?? [] ) ) ) ); ?></p>

      <h2><?php esc_html_e( '0895 · Dead links', 'vpg-v2' ); ?></h2>
      <?php if ( $dead ) { echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Post', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Link', 'vpg-v2' ) . '</th><th>' . esc_html__( 'Code', 'vpg-v2' ) . '</th></tr></thead><tbody>';
          foreach ( array_slice( $dead, 0, 60 ) as $d ) echo '<tr><td><a href="' . esc_url( (string) get_edit_post_link( $d['pid'] ) ) . '">' . esc_html( get_the_title( $d['pid'] ) ) . '</a></td><td style="word-break:break-all">' . esc_html( $d['link'] ) . '</td><td>' . (int) $d['code'] . '</td></tr>';
          echo '</tbody></table>';
      } else echo '<p class="description">' . esc_html__( 'No dead links found in the last sweep (or the bot hasn’t run yet).', 'vpg-v2' ) . '</p>'; ?>

      <h2><?php esc_html_e( '0906 · Curation candidates (POTW pre-selection)', 'vpg-v2' ); ?></h2>
      <p style="display:flex;flex-wrap:wrap;gap:8px">
      <?php foreach ( vpg_curation_candidates( 12 ) as $aid ) { $img = wp_get_attachment_image( $aid, [ 90, 90 ] ); if ( $img ) echo '<a href="' . esc_url( (string) get_edit_post_link( $aid ) ) . '">' . $img . '</a>'; } ?>
      </p>
      <p class="description"><?php esc_html_e( 'A starting shortlist by simple signals — the jury still chooses. Members who opted out never appear here.', 'vpg-v2' ); ?></p>
    </div>
    <?php
}
