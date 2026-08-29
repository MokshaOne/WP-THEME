<?php
/**
 * VPG v3 — Cluster 05 · Magazin & Heft.
 *
 * Extends the bespoke magazine editor (inc/magazine-editor.php), the reader
 * (single-vpg_magazine.php) and the mPDF pipeline (inc/pdf-generator.php)
 * with issue craft: a leitmotif, quote of the month, cover credit + voting,
 * column slots, dramaturgy scaffold, footnotes, cite links, double-spreads,
 * making-of boxes, playlists, letters, puzzle, classifieds, pitch board,
 * author briefings, contribution budget, deadline clock, proof round, sRGB
 * normalisation, metrics, PDF versioning and a publication handover ritual.
 *
 *   0162 theme · 0165 columns · 0166 dramaturgy · 0167 quote · 0168 letters
 *   0169 puzzle · 0170 contact sheet · 0174 double · 0175 special
 *   0176 reprint · 0177 cover vote · 0178 cover credit · 0179 typo polish
 *   0180 sRGB · 0181 proof · 0182 deadline · 0183 budget · 0184 briefings
 *   0185 pitch board · 0186 metrics · 0187 cite · 0188 footnotes
 *   0189 double-spread · 0190 serif accent · 0191 playlist · 0192 making-of
 *   0193 guest curator · 0194 swap pages · 0195 classifieds · 0196 versioning
 *   0200 handover ritual
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Issue metadata fields — rendered into the editor panel + saved    */
/* ════════════════════════════════════════════════════════════════ */
function vpg_mag_issue_fields() {
    return [
        'theme'      => [ 'type' => 'text', 'meta' => '_vpg_issue_theme',    'label' => __( '0162 · Issue leitmotif', 'vpg-v2' ),  'ph' => __( 'e.g. “Thresholds”', 'vpg-v2' ) ],
        'quote'      => [ 'type' => 'text', 'meta' => '_vpg_issue_quote',    'label' => __( '0167 · Quote of the month', 'vpg-v2' ), 'ph' => __( 'One sentence from the community', 'vpg-v2' ) ],
        'coverart'   => [ 'type' => 'text', 'meta' => '_vpg_cover_artist',   'label' => __( '0178 · Cover artist', 'vpg-v2' ),      'ph' => __( 'Name for the cover credit page', 'vpg-v2' ) ],
        'curator'    => [ 'type' => 'text', 'meta' => '_vpg_guest_curator',  'label' => __( '0193 · Guest curator', 'vpg-v2' ),     'ph' => __( 'If this issue is guest-curated', 'vpg-v2' ) ],
        'playlist'   => [ 'type' => 'url',  'meta' => '_vpg_issue_playlist', 'label' => __( '0191 · Issue playlist URL', 'vpg-v2' ),'ph' => 'https://…' ],
        'deadline'   => [ 'type' => 'date', 'meta' => '_vpg_issue_deadline', 'label' => __( '0182 · Editorial deadline', 'vpg-v2' ) ],
        'puzzle_ans' => [ 'type' => 'text', 'meta' => '_vpg_issue_puzzle_answer', 'label' => __( '0169 · Puzzle answer', 'vpg-v2' ), 'ph' => __( 'Where is the puzzle photo?', 'vpg-v2' ) ],
    ];
}

function vpg_mag_render_issue_fields( $issue_id ) {
    echo '<section class="vpg-mag-panel"><h2>' . esc_html__( 'Issue craft', 'vpg-v2' ) . '</h2>';
    foreach ( vpg_mag_issue_fields() as $k => $f ) {
        $val = $issue_id ? (string) get_post_meta( $issue_id, $f['meta'], true ) : '';
        echo '<label class="vpg-mag-row"><span>' . esc_html( $f['label'] ) . '</span>';
        printf( '<input type="%s" name="craft[%s]" value="%s" placeholder="%s">', esc_attr( $f['type'] ), esc_attr( $k ), esc_attr( $val ), esc_attr( $f['ph'] ?? '' ) );
        echo '</label>';
    }
    // flags
    $special = $issue_id ? get_post_meta( $issue_id, '_vpg_special_edition', true ) : '';
    $double  = $issue_id ? get_post_meta( $issue_id, '_vpg_double_issue', true ) : '';
    echo '<label class="vpg-mag-row"><span>' . esc_html__( 'Edition type', 'vpg-v2' ) . '</span><span>';
    echo '<label style="font-weight:400;margin-right:14px"><input type="checkbox" name="craft_special" value="1"' . checked( $special, '1', false ) . '> ' . esc_html__( '0175 · Special edition', 'vpg-v2' ) . '</label>';
    echo '<label style="font-weight:400"><input type="checkbox" name="craft_double" value="1"' . checked( $double, '1', false ) . '> ' . esc_html__( '0174 · Double issue', 'vpg-v2' ) . '</label></span></label>';

    // 0169 puzzle image + 0177 three cover options — media pickers
    $puzzle = $issue_id ? (int) get_post_meta( $issue_id, '_vpg_issue_puzzle_img', true ) : 0;
    $opts   = $issue_id ? array_map( 'intval', (array) get_post_meta( $issue_id, '_vpg_cover_options', true ) ) : [];
    $voting = $issue_id ? get_post_meta( $issue_id, '_vpg_cover_voting', true ) : '';
    echo '<label class="vpg-mag-row"><span>' . esc_html__( '0169 · Puzzle photo', 'vpg-v2' ) . '</span><span>';
    echo '<input type="hidden" class="vpg-mag-mediaid" data-name="craft_puzzle_img" name="craft_puzzle_img" value="' . $puzzle . '">';
    echo '<span class="vpg-mag-mediaprev">' . ( $puzzle ? '<img src="' . esc_url( wp_get_attachment_image_url( $puzzle, 'thumbnail' ) ) . '" style="max-width:90px">' : '' ) . '</span> ';
    echo '<button type="button" class="button button-small vpg-mag-mediapick" data-for="craft_puzzle_img">' . esc_html__( 'Choose', 'vpg-v2' ) . '</button></span></label>';

    echo '<label class="vpg-mag-row"><span>' . esc_html__( '0177 · Cover drafts (vote)', 'vpg-v2' ) . '</span><span style="display:flex;gap:8px;flex-wrap:wrap">';
    for ( $i = 0; $i < 3; $i++ ) {
        $oid = (int) ( $opts[ $i ] ?? 0 );
        echo '<span><input type="hidden" class="vpg-mag-mediaid" data-name="craft_cover_opt_' . $i . '" name="craft_cover_opt[' . $i . ']" value="' . $oid . '">';
        echo '<span class="vpg-mag-mediaprev">' . ( $oid ? '<img src="' . esc_url( wp_get_attachment_image_url( $oid, 'thumbnail' ) ) . '" style="max-width:72px;display:block">' : '' ) . '</span>';
        echo '<button type="button" class="button button-small vpg-mag-mediapick" data-for="craft_cover_opt_' . $i . '">' . esc_html( sprintf( __( 'Draft %d', 'vpg-v2' ), $i + 1 ) ) . '</button></span>';
    }
    echo '</span></label>';
    echo '<label class="vpg-mag-row"><span>' . esc_html__( 'Cover voting', 'vpg-v2' ) . '</span><label style="font-weight:400"><input type="checkbox" name="craft_cover_voting" value="1"' . checked( $voting, '1', false ) . '> ' . esc_html__( 'Open community vote on the drafts', 'vpg-v2' ) . '</label></label>';

    // 0181 proof round
    $proof = $issue_id ? get_post_meta( $issue_id, '_vpg_proof_status', true ) : '';
    echo '<label class="vpg-mag-row"><span>' . esc_html__( '0181 · Proof status', 'vpg-v2' ) . '</span><select name="craft_proof">';
    foreach ( [ '' => __( '— drafting —', 'vpg-v2' ), 'proofing' => __( 'In proof', 'vpg-v2' ), 'approved' => __( 'Approved', 'vpg-v2' ) ] as $pv => $pl ) echo '<option value="' . esc_attr( $pv ) . '"' . selected( $proof, $pv, false ) . '>' . esc_html( $pl ) . '</option>';
    echo '</select></label>';
    echo '</section>';
    ?>
    <script>
    (function(){
      document.querySelectorAll('.vpg-mag-mediapick').forEach(function(b){
        if(!(window.wp&&wp.media))return;
        b.addEventListener('click',function(){
          var name=b.dataset.for;var fr=wp.media({library:{type:'image'},multiple:false});
          fr.on('select',function(){var a=fr.state().get('selection').first().toJSON();
            var inp=document.querySelector('.vpg-mag-mediaid[data-name="'+name+'"]');if(inp)inp.value=a.id;
            var u=(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url);
            var prev=inp&&inp.parentNode.querySelector('.vpg-mag-mediaprev');if(prev)prev.innerHTML='<img src="'+u+'" style="max-width:90px">';
          });fr.open();
        });
      });
    })();
    </script>
    <?php
}

function vpg_mag_save_issue_fields( $issue_id ) {
    $craft = (array) ( $_POST['craft'] ?? [] );
    foreach ( vpg_mag_issue_fields() as $k => $f ) {
        $raw = $craft[ $k ] ?? '';
        $v = $f['type'] === 'url' ? esc_url_raw( wp_unslash( $raw ) ) : sanitize_text_field( wp_unslash( $raw ) );
        $v !== '' ? update_post_meta( $issue_id, $f['meta'], $v ) : delete_post_meta( $issue_id, $f['meta'] );
    }
    update_post_meta( $issue_id, '_vpg_special_edition', empty( $_POST['craft_special'] ) ? '' : '1' );
    update_post_meta( $issue_id, '_vpg_double_issue',   empty( $_POST['craft_double'] )  ? '' : '1' );
    update_post_meta( $issue_id, '_vpg_cover_voting',   empty( $_POST['craft_cover_voting'] ) ? '' : '1' );
    $pz = (int) ( $_POST['craft_puzzle_img'] ?? 0 );
    $pz ? update_post_meta( $issue_id, '_vpg_issue_puzzle_img', $pz ) : delete_post_meta( $issue_id, '_vpg_issue_puzzle_img' );
    $opts = array_values( array_filter( array_map( 'intval', (array) ( $_POST['craft_cover_opt'] ?? [] ) ) ) );
    $opts ? update_post_meta( $issue_id, '_vpg_cover_options', $opts ) : delete_post_meta( $issue_id, '_vpg_cover_options' );
    $proof = sanitize_key( $_POST['craft_proof'] ?? '' );
    in_array( $proof, [ 'proofing', 'approved' ], true ) ? update_post_meta( $issue_id, '_vpg_proof_status', $proof ) : delete_post_meta( $issue_id, '_vpg_proof_status' );

    // 0165 · column slots — fill an empty author from the recurring voice for its section
    $cols = (array) get_option( 'vpg_mag_columns', [] );
    if ( $cols ) {
        $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $issue_id ) : [];
        $changed = false;
        foreach ( $articles as &$a ) {
            $sec = $a['section'] ?? '';
            if ( $sec && empty( $a['author'] ) && ! empty( $cols[ $sec ] ) ) { $a['author'] = $cols[ $sec ]; $changed = true; }
        }
        unset( $a );
        if ( $changed ) update_post_meta( $issue_id, '_vpg_articles', wp_json_encode( $articles ) );
    }
}

/* ════════════════════════════════════════════════════════════════ */
/*  Article body craft — footnotes, double-spread, making-of, cite    */
/* ════════════════════════════════════════════════════════════════ */
function vpg_mag_render_body( $body, $issue_id, $ai ) {
    $footnotes = [];
    // [fn]text[/fn] → superscript refs collected at the article foot (0188)
    $body = preg_replace_callback( '/\[fn\](.+?)\[\/fn\]/s', function ( $m ) use ( &$footnotes ) {
        $footnotes[] = trim( $m[1] );
        $n = count( $footnotes );
        return '<sup class="vpg-fn-ref" id="fnref-' . $n . '"><a href="#fn-' . $n . '">' . $n . '</a></sup>';
    }, (string) $body );

    // [spread ids=12,13] → gutter-spanning panorama block (0189)
    $body = preg_replace_callback( '/\[spread ids?=([\d,\s]+)\]/i', function ( $m ) {
        $ids = array_filter( array_map( 'intval', explode( ',', $m[1] ) ) );
        $imgs = '';
        foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'large' ); if ( $u ) $imgs .= '<img src="' . esc_url( $u ) . '" alt="" style="width:100%;display:block">'; }
        return $imgs ? '<div class="vpg-spread" style="display:grid;grid-template-columns:1fr 1fr;gap:2px;margin:24px -8vw;max-width:none">' . $imgs . '</div>' : '';
    }, $body );

    // [makingof ids=1,2,3,4,5] → five-image making-of strip (0192)
    $body = preg_replace_callback( '/\[makingof ids?=([\d,\s]+)\]/i', function ( $m ) {
        $ids = array_slice( array_filter( array_map( 'intval', explode( ',', $m[1] ) ) ), 0, 5 );
        if ( ! $ids ) return '';
        $out = '<aside class="vpg-makingof" style="background:var(--g-off,#F5F4F1);padding:18px;margin:24px 0"><p class="vpg-caps" style="margin:0 0 10px">— ' . esc_html__( 'Making of', 'vpg-v2' ) . '</p><div style="display:grid;grid-template-columns:repeat(5,1fr);gap:6px">';
        foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); if ( $u ) $out .= '<img src="' . esc_url( $u ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">'; }
        return $out . '</div></aside>';
    }, $body );

    $html = wpautop( wp_kses_post( $body ) );

    if ( $footnotes ) {
        $html .= '<ol class="vpg-footnotes" style="margin-top:24px;padding-top:12px;border-top:1px solid var(--g-line,#E6E5E1);font-size:13px;color:var(--g-mid,#6A6A6A)">';
        foreach ( $footnotes as $i => $fn ) {
            $n = $i + 1;
            $html .= '<li id="fn-' . $n . '">' . wp_kses_post( $fn ) . ' <a href="#fnref-' . $n . '" style="text-decoration:none">↩</a></li>';
        }
        $html .= '</ol>';
    }
    return $html;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Front-end · cover meta (theme, badges, credit, quote)             */
/* ════════════════════════════════════════════════════════════════ */
function vpg_mag_render_cover_meta( $id ) {
    $theme   = get_post_meta( $id, '_vpg_issue_theme', true );
    $special = get_post_meta( $id, '_vpg_special_edition', true );
    $double  = get_post_meta( $id, '_vpg_double_issue', true );
    $curator = get_post_meta( $id, '_vpg_guest_curator', true );
    $fresh   = (int) get_post_meta( $id, '_vpg_published_at', true );
    ?>
    <?php if ( $fresh && $fresh > time() - 14 * DAY_IN_SECONDS ) : ?>
      <p style="display:inline-block;background:var(--g-red);color:#fff;font-weight:800;font-size:11px;letter-spacing:.16em;text-transform:uppercase;padding:5px 12px;margin-bottom:12px">✦ <?php esc_html_e( 'Fresh off the press', 'vpg-v2' ); ?></p>
    <?php endif; ?>
    <?php if ( $theme || $special || $double || $curator ) : ?>
    <p class="vpg-cover__theme" style="font-family:var(--vpg-font-mono);letter-spacing:.18em;text-transform:uppercase;font-size:.72rem;color:var(--vpg-muted,#9C9A95);margin-bottom:10px">
      <?php if ( $theme ) echo '● ' . esc_html( sprintf( __( 'Leitmotif · %s', 'vpg-v2' ), $theme ) ); ?>
      <?php if ( $special ) echo ' &nbsp;·&nbsp; ' . esc_html__( 'Special edition', 'vpg-v2' ); ?>
      <?php if ( $double ) echo ' &nbsp;·&nbsp; ' . esc_html__( 'Double issue', 'vpg-v2' ); ?>
      <?php if ( $curator ) echo ' &nbsp;·&nbsp; ' . esc_html( sprintf( __( 'Guest-curated by %s', 'vpg-v2' ), $curator ) ); ?>
    </p>
    <?php endif;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Front-end · the extras section (after the colophon-ish area)      */
/* ════════════════════════════════════════════════════════════════ */
function vpg_mag_render_extras( $id ) {
    $quote    = get_post_meta( $id, '_vpg_issue_quote', true );
    $playlist = get_post_meta( $id, '_vpg_issue_playlist', true );
    $coverart = get_post_meta( $id, '_vpg_cover_artist', true );
    $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $id ) : [];
    $plates   = [];
    foreach ( $articles as $a ) if ( ! empty( $a['image_id'] ) ) $plates[] = (int) $a['image_id'];
    ?>
    <!-- 0167 quote of the month -->
    <?php if ( $quote ) : ?>
    <section class="vpg-section" style="text-align:center"><div class="vpg-wrap--narrow">
      <blockquote style="font-family:var(--vpg-font-serif,Georgia),serif;font-size:clamp(24px,4vw,40px);line-height:1.25;font-style:italic;margin:0">“<?php echo esc_html( $quote ); ?>”</blockquote>
    </div></section>
    <?php endif; ?>

    <!-- 0170 contact sheet · the issue's plates as an index -->
    <?php if ( count( $plates ) >= 2 ) : ?>
    <section class="vpg-section vpg-section--surface"><div class="vpg-wrap--mag">
      <p class="vpg-caps">— <?php esc_html_e( 'Contact sheet', 'vpg-v2' ); ?></p>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:4px;margin-top:12px">
        <?php foreach ( $plates as $pi => $pid ) : $u = wp_get_attachment_image_url( $pid, 'medium' ); if ( ! $u ) continue; ?>
          <a href="#article-<?php echo $pi + 1; ?>" style="position:relative;display:block"><img src="<?php echo esc_url( $u ); ?>" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;filter:grayscale(1)"><span style="position:absolute;left:3px;top:3px;background:#0B0B0B;color:#fff;font:700 9px/1 monospace;padding:2px 4px"><?php printf( '%02d', $pi + 1 ); ?></span></a>
        <?php endforeach; ?>
      </div>
    </div></section>
    <?php endif; ?>

    <!-- 0191 playlist · 0178 cover credit -->
    <?php if ( $playlist || $coverart ) : ?>
    <section class="vpg-section"><div class="vpg-wrap--narrow" style="display:flex;gap:24px;flex-wrap:wrap;justify-content:center">
      <?php if ( $playlist ) : ?><a class="g-btn g-btn--ghost" href="<?php echo esc_url( $playlist ); ?>" target="_blank" rel="noopener">♫ <?php esc_html_e( 'The issue’s soundtrack', 'vpg-v2' ); ?></a><?php endif; ?>
      <?php if ( $coverart ) : ?><p style="font-size:13px;color:var(--vpg-muted,#6A6A6A);align-self:center"><?php printf( esc_html__( 'Cover photograph · %s', 'vpg-v2' ), '<strong>' . esc_html( $coverart ) . '</strong>' ); ?></p><?php endif; ?>
    </div></section>
    <?php endif; ?>

    <?php vpg_mag_render_cover_vote( $id ); ?>
    <?php vpg_mag_render_puzzle( $id ); ?>
    <?php vpg_mag_render_classifieds( $id ); ?>
    <?php vpg_mag_render_letters( $id ); ?>

    <?php // 0186 metrics beacon (fire once per view) ?>
    <script>
    (function(){
      try{ if(!sessionStorage.getItem('vpg_seen_<?php echo (int) $id; ?>')){ sessionStorage.setItem('vpg_seen_<?php echo (int) $id; ?>','1');
        var fd=new FormData();fd.append('action','vpg_mag_view');fd.append('issue','<?php echo (int) $id; ?>');
        navigator.sendBeacon(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,fd);
      }}catch(e){}
      // read-depth: report max scroll on unload
      var maxd=0;window.addEventListener('scroll',function(){var d=document.documentElement,m=d.scrollHeight-innerHeight;if(m>0)maxd=Math.max(maxd,Math.round(scrollY/m*100));},{passive:true});
      window.addEventListener('pagehide',function(){try{var fd=new FormData();fd.append('action','vpg_mag_depth');fd.append('issue','<?php echo (int) $id; ?>');fd.append('depth',maxd);navigator.sendBeacon(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,fd);}catch(e){}});
    })();
    </script>
    <?php
}

/* 0177 · cover voting block */
function vpg_mag_render_cover_vote( $id ) {
    if ( get_post_meta( $id, '_vpg_cover_voting', true ) !== '1' ) return;
    $opts = array_values( array_filter( array_map( 'intval', (array) get_post_meta( $id, '_vpg_cover_options', true ) ) ) );
    if ( count( $opts ) < 2 ) return;
    $votes = (array) get_post_meta( $id, '_vpg_cover_votes', true );
    $mine  = get_current_user_id() && isset( $votes[ get_current_user_id() ] ) ? (int) $votes[ get_current_user_id() ] : -1;
    $tally = array_count_values( array_map( 'intval', $votes ) );
    $total = max( 1, array_sum( $tally ) );
    ?>
    <section class="vpg-section vpg-section--surface"><div class="vpg-wrap--mag" style="text-align:center">
      <p class="vpg-caps">— <?php esc_html_e( 'Pick the cover', 'vpg-v2' ); ?></p>
      <p style="color:var(--vpg-muted,#6A6A6A);margin:6px 0 16px"><?php esc_html_e( 'Members choose the face of this issue.', 'vpg-v2' ); ?></p>
      <div style="display:grid;grid-template-columns:repeat(<?php echo count( $opts ); ?>,1fr);gap:12px;max-width:720px;margin:0 auto">
        <?php foreach ( $opts as $i => $oid ) : $u = wp_get_attachment_image_url( $oid, 'large' ); if ( ! $u ) continue; $pct = round( ( $tally[ $i ] ?? 0 ) / $total * 100 ); ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
            <?php wp_nonce_field( 'vpg_cover_vote' ); ?>
            <input type="hidden" name="action" value="vpg_cover_vote"><input type="hidden" name="issue" value="<?php echo (int) $id; ?>"><input type="hidden" name="opt" value="<?php echo $i; ?>">
            <button type="submit"<?php echo is_user_logged_in() ? '' : ' disabled'; ?> style="border:<?php echo $mine === $i ? '3px solid var(--g-red)' : '1px solid var(--g-line)'; ?>;padding:0;background:none;cursor:pointer;width:100%">
              <img src="<?php echo esc_url( $u ); ?>" alt="" style="width:100%;aspect-ratio:3/4;object-fit:cover;display:block">
            </button>
            <?php if ( $mine !== -1 ) : ?><div style="height:6px;background:var(--g-line);margin-top:4px"><div style="height:6px;width:<?php echo (int) $pct; ?>%;background:var(--g-red)"></div></div><span style="font-size:11px"><?php echo (int) $pct; ?>%</span><?php endif; ?>
          </form>
        <?php endforeach; ?>
      </div>
      <?php if ( ! is_user_logged_in() ) : ?><p style="font-size:12px;color:var(--vpg-muted);margin-top:10px"><?php esc_html_e( 'Sign in to vote.', 'vpg-v2' ); ?></p><?php endif; ?>
    </div></section>
    <?php
}

/* 0169 · puzzle page */
function vpg_mag_render_puzzle( $id ) {
    $img = (int) get_post_meta( $id, '_vpg_issue_puzzle_img', true );
    $ans = get_post_meta( $id, '_vpg_issue_puzzle_answer', true );
    if ( ! $img ) return;
    $u = wp_get_attachment_image_url( $img, 'large' );
    ?>
    <section class="vpg-section"><div class="vpg-wrap--narrow" style="text-align:center">
      <p class="vpg-caps">— <?php esc_html_e( 'Where is this?', 'vpg-v2' ); ?></p>
      <?php if ( $u ) : ?><img src="<?php echo esc_url( $u ); ?>" alt="" style="width:100%;max-width:560px;margin:12px auto;display:block"><?php endif; ?>
      <?php if ( $ans ) : ?>
        <details style="margin-top:10px"><summary style="cursor:pointer;font-weight:700"><?php esc_html_e( 'Reveal the answer', 'vpg-v2' ); ?></summary><p style="margin-top:8px"><?php echo esc_html( $ans ); ?></p></details>
      <?php endif; ?>
    </div></section>
    <?php
}

/* 0195 · classifieds — the charming last page */
function vpg_mag_render_classifieds( $id ) {
    $ads = array_filter( (array) get_option( 'vpg_classifieds', [] ), fn( $a ) => ! empty( $a['ok'] ) );
    ?>
    <section class="vpg-section vpg-section--surface"><div class="vpg-wrap--mag">
      <p class="vpg-caps">— <?php esc_html_e( 'Classifieds · member flea market', 'vpg-v2' ); ?></p>
      <?php if ( $ads ) : ?>
      <div style="columns:2;column-gap:28px;margin-top:12px;font-size:14px">
        <?php foreach ( array_slice( array_reverse( $ads ), 0, 20 ) as $ad ) : ?>
          <p style="break-inside:avoid;margin:0 0 12px"><strong><?php echo esc_html( $ad['title'] ?? '' ); ?></strong> — <?php echo esc_html( $ad['body'] ?? '' ); ?> <span style="color:var(--vpg-muted,#6A6A6A)">· <?php echo esc_html( $ad['by'] ?? '' ); ?></span></p>
        <?php endforeach; ?>
      </div>
      <?php else : ?><p style="color:var(--vpg-muted,#6A6A6A)"><?php esc_html_e( 'Nothing on the market this issue.', 'vpg-v2' ); ?></p><?php endif; ?>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px">
        <?php wp_nonce_field( 'vpg_classified' ); ?>
        <input type="hidden" name="action" value="vpg_classified">
        <input type="text" name="title" maxlength="60" required placeholder="<?php esc_attr_e( 'For sale / wanted…', 'vpg-v2' ); ?>" style="flex:1;min-width:160px;padding:8px;border:1px solid var(--g-line)">
        <input type="text" name="body" maxlength="160" required placeholder="<?php esc_attr_e( 'One line', 'vpg-v2' ); ?>" style="flex:2;min-width:200px;padding:8px;border:1px solid var(--g-line)">
        <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Post ad (reviewed)', 'vpg-v2' ); ?></button>
      </form>
      <?php endif; ?>
    </div></section>
    <?php
}

/* 0168 · letters to the editor */
function vpg_mag_render_letters( $id ) {
    $letters = array_filter( (array) get_post_meta( $id, '_vpg_letters', true ), fn( $l ) => ! empty( $l['ok'] ) );
    ?>
    <section class="vpg-section"><div class="vpg-wrap--narrow">
      <p class="vpg-caps">— <?php esc_html_e( 'Letters', 'vpg-v2' ); ?></p>
      <?php if ( $letters ) : ?>
        <?php foreach ( array_slice( array_reverse( $letters ), 0, 8 ) as $l ) : ?>
          <blockquote style="border-left:3px solid var(--g-line,#E6E5E1);padding-left:14px;margin:14px 0;font-size:14px"><?php echo esc_html( $l['body'] ?? '' ); ?><footer style="color:var(--vpg-muted,#6A6A6A);font-size:12px;margin-top:4px">— <?php echo esc_html( $l['by'] ?? '' ); ?></footer></blockquote>
        <?php endforeach; ?>
      <?php endif; ?>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px">
        <?php wp_nonce_field( 'vpg_letter' ); ?>
        <input type="hidden" name="action" value="vpg_letter"><input type="hidden" name="issue" value="<?php echo (int) $id; ?>">
        <textarea name="body" rows="2" maxlength="400" required placeholder="<?php esc_attr_e( 'A letter to the editors about this issue…', 'vpg-v2' ); ?>" style="flex:1;min-width:240px;padding:8px;border:1px solid var(--g-line)"></textarea>
        <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Send (curated back into the issue)', 'vpg-v2' ); ?></button>
      </form>
      <?php endif; ?>
    </div></section>
    <?php
}

/* ════════════════════════════════════════════════════════════════ */
/*  Handlers · votes, letters, classifieds, pitches, metrics          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_cover_vote', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_cover_vote' );
    $id = (int) ( $_POST['issue'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_magazine' ) wp_die( 'Not found', 404 );
    $votes = (array) get_post_meta( $id, '_vpg_cover_votes', true );
    $votes[ get_current_user_id() ] = max( 0, min( 2, (int) ( $_POST['opt'] ?? 0 ) ) );
    update_post_meta( $id, '_vpg_cover_votes', $votes );
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );

add_action( 'admin_post_vpg_letter', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_letter' );
    $id = (int) ( $_POST['issue'] ?? 0 );
    if ( get_post_type( $id ) !== 'vpg_magazine' ) wp_die( 'Not found', 404 );
    $u = wp_get_current_user();
    $ls = (array) get_post_meta( $id, '_vpg_letters', true );
    $ls[] = [ 'u' => $u->ID, 'by' => $u->display_name, 'body' => sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) ), 'ok' => 0, 't' => time() ];
    update_post_meta( $id, '_vpg_letters', array_slice( $ls, -100 ) );
    wp_safe_redirect( get_permalink( $id ) . '?vpg_status=letter_sent' ); exit;
} );

add_action( 'admin_post_vpg_classified', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_classified' );
    $u = wp_get_current_user();
    $ads = (array) get_option( 'vpg_classifieds', [] );
    $ads[] = [ 'u' => $u->ID, 'by' => $u->display_name, 'title' => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ), 'body' => sanitize_text_field( wp_unslash( $_POST['body'] ?? '' ) ), 'ok' => 0, 't' => time() ];
    update_option( 'vpg_classifieds', array_slice( $ads, -200 ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

add_action( 'admin_post_vpg_mag_pitch', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_mag_pitch' );
    $u = wp_get_current_user();
    $p = (array) get_option( 'vpg_mag_pitches', [] );
    $p[] = [ 'u' => $u->ID, 'by' => $u->display_name, 'theme' => sanitize_text_field( wp_unslash( $_POST['theme'] ?? '' ) ), 'why' => sanitize_textarea_field( wp_unslash( $_POST['why'] ?? '' ) ), 'picked' => 0, 't' => time() ];
    update_option( 'vpg_mag_pitches', array_slice( $p, -200 ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );
add_shortcode( 'vpg_pitch_board', function () {
    if ( ! is_user_logged_in() ) return '<p>' . esc_html__( 'Sign in to pitch an issue theme.', 'vpg-v2' ) . '</p>';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap">
      <?php wp_nonce_field( 'vpg_mag_pitch' ); ?><input type="hidden" name="action" value="vpg_mag_pitch">
      <input type="text" name="theme" maxlength="60" required placeholder="<?php esc_attr_e( 'A theme for a future issue', 'vpg-v2' ); ?>" style="flex:1;min-width:200px;padding:9px;border:1px solid var(--g-line)">
      <input type="text" name="why" maxlength="180" placeholder="<?php esc_attr_e( 'Why it matters (optional)', 'vpg-v2' ); ?>" style="flex:2;min-width:200px;padding:9px;border:1px solid var(--g-line)">
      <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Pitch it', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );

/* 0186 · metrics beacons */
add_action( 'wp_ajax_vpg_mag_view', 'vpg_mag_beacon_view' );
add_action( 'wp_ajax_nopriv_vpg_mag_view', 'vpg_mag_beacon_view' );
function vpg_mag_beacon_view() {
    $id = (int) ( $_POST['issue'] ?? 0 );
    if ( get_post_type( $id ) === 'vpg_magazine' ) update_post_meta( $id, '_vpg_issue_views', (int) get_post_meta( $id, '_vpg_issue_views', true ) + 1 );
    wp_die();
}
add_action( 'wp_ajax_vpg_mag_depth', 'vpg_mag_beacon_depth' );
add_action( 'wp_ajax_nopriv_vpg_mag_depth', 'vpg_mag_beacon_depth' );
function vpg_mag_beacon_depth() {
    $id = (int) ( $_POST['issue'] ?? 0 );
    $d  = max( 0, min( 100, (int) ( $_POST['depth'] ?? 0 ) ) );
    if ( get_post_type( $id ) === 'vpg_magazine' && $d ) {
        $agg = (array) get_post_meta( $id, '_vpg_read_depth', true );
        $agg['sum'] = ( $agg['sum'] ?? 0 ) + $d; $agg['n'] = ( $agg['n'] ?? 0 ) + 1;
        update_post_meta( $id, '_vpg_read_depth', $agg );
    }
    wp_die();
}

/* 0200 · publication handover ritual — on first publish */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
    if ( $post->post_type !== 'vpg_magazine' || $new !== 'publish' || $old === 'publish' ) return;
    if ( get_post_meta( $post->ID, '_vpg_published_at', true ) ) return;
    update_post_meta( $post->ID, '_vpg_published_at', time() );
    $subj = sprintf( __( 'The new issue is out · %s', 'vpg-v2' ), get_the_title( $post ) );
    $url  = get_permalink( $post );
    $body = sprintf( __( 'A fresh issue has landed: %1$s. Read it here: %2$s', 'vpg-v2' ), get_the_title( $post ), $url );
    // email the newsletter list
    if ( function_exists( 'vpg_newsletter_list' ) ) {
        foreach ( array_slice( array_filter( (array) vpg_newsletter_list() ), 0, 2000 ) as $entry ) {
            $email = is_array( $entry ) ? ( $entry['email'] ?? '' ) : $entry;
            if ( is_email( $email ) ) wp_mail( $email, $subj, $body );
        }
    }
    // push to subscribed members
    if ( function_exists( 'vpg_push_send' ) ) {
        foreach ( get_users( [ 'fields' => 'ID', 'meta_key' => '_vpg_push_subs' ] ) as $uid ) {
            vpg_push_send( (int) $uid, $subj, get_the_excerpt( $post ) ?: __( 'A new issue is out.', 'vpg-v2' ), $url );
        }
    }
}, 10, 3 );

/* ════════════════════════════════════════════════════════════════ */
/*  0196 · PDF versioning — record every build in a history list      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'updated_post_meta', function ( $mid, $pid, $key, $val ) {
    if ( $key !== '_vpg_pdf_url' || get_post_type( $pid ) !== 'vpg_magazine' || ! $val ) return;
    $hist = (array) get_post_meta( $pid, '_vpg_pdf_versions', true );
    if ( ! empty( $hist ) && end( $hist )['url'] === $val ) return;
    $hist[] = [ 'url' => $val, 'at' => current_time( 'mysql' ), 'by' => get_current_user_id() ];
    update_post_meta( $pid, '_vpg_pdf_versions', array_slice( $hist, -20 ) );
}, 10, 4 );
add_action( 'added_post_meta', function ( $mid, $pid, $key, $val ) {
    if ( $key === '_vpg_pdf_url' && get_post_type( $pid ) === 'vpg_magazine' && $val ) {
        update_post_meta( $pid, '_vpg_pdf_versions', [ [ 'url' => $val, 'at' => current_time( 'mysql' ), 'by' => get_current_user_id() ] ] );
    }
}, 10, 4 );

/* ════════════════════════════════════════════════════════════════ */
/*  0180 · sRGB normalisation (best-effort, Imagick) — desk action    */
/* ════════════════════════════════════════════════════════════════ */
function vpg_srgb_normalize_attachment( $aid ) {
    if ( ! class_exists( 'Imagick' ) ) return false;
    $path = get_attached_file( $aid );
    if ( ! $path || ! file_exists( $path ) ) return false;
    try {
        $im = new Imagick( $path );
        if ( method_exists( $im, 'transformImageColorspace' ) ) $im->transformImageColorspace( Imagick::COLORSPACE_SRGB );
        $im->stripImage();
        $im->writeImage( $path );
        $im->clear();
        return true;
    } catch ( \Exception $e ) { return false; }
}

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · Magazine desk — columns, budget, pitches, letters,        */
/*         classifieds, metrics, sRGB, dramaturgy new-issue           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-magazine', __( 'Editorial desk', 'vpg-v2' ), '🖉 ' . __( 'Editorial desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-mag-desk', 'vpg_mag_desk_page' );
}, 30 );

function vpg_mag_desk_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    $sections = function_exists( 'vpg_mag_sections' ) ? vpg_mag_sections() : [];

    if ( isset( $_POST['vpg_mag_desk'] ) && check_admin_referer( 'vpg_mag_desk' ) ) {
        $cols = []; $bud = [];
        foreach ( $sections as $sv => $sl ) {
            $c = sanitize_text_field( wp_unslash( $_POST['col'][ $sv ] ?? '' ) ); if ( $c ) $cols[ $sv ] = $c;
            $b = (int) ( $_POST['bud'][ $sv ] ?? 0 ); if ( $b ) $bud[ $sv ] = $b;
        }
        update_option( 'vpg_mag_columns', $cols, false );
        update_option( 'vpg_mag_budget', $bud, false );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    // approvals
    if ( isset( $_GET['approve_letter'], $_GET['issue'] ) && check_admin_referer( 'vpg_mag_approve' ) ) {
        $iid = (int) $_GET['issue']; $k = (int) $_GET['approve_letter'];
        $ls = (array) get_post_meta( $iid, '_vpg_letters', true ); if ( isset( $ls[ $k ] ) ) { $ls[ $k ]['ok'] = 1; update_post_meta( $iid, '_vpg_letters', $ls ); }
    }
    if ( isset( $_GET['approve_ad'] ) && check_admin_referer( 'vpg_mag_approve' ) ) {
        $ads = (array) get_option( 'vpg_classifieds', [] ); $k = (int) $_GET['approve_ad']; if ( isset( $ads[ $k ] ) ) { $ads[ $k ]['ok'] = 1; update_option( 'vpg_classifieds', $ads, false ); }
    }
    if ( isset( $_GET['srgb'] ) && check_admin_referer( 'vpg_mag_approve' ) ) {
        $iid = (int) $_GET['srgb']; $n = 0;
        foreach ( function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $iid ) : [] as $a ) if ( ! empty( $a['image_id'] ) && vpg_srgb_normalize_attachment( (int) $a['image_id'] ) ) $n++;
        echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Normalised %d images to sRGB.', 'vpg-v2' ), $n ) ) . '</p></div>';
    }

    $cols = (array) get_option( 'vpg_mag_columns', [] );
    $bud  = (array) get_option( 'vpg_mag_budget', [] );
    ?>
    <div class="wrap"><h1>🖉 <?php esc_html_e( 'Editorial desk', 'vpg-v2' ); ?></h1>

      <form method="post"><?php wp_nonce_field( 'vpg_mag_desk' ); ?>
        <h2><?php esc_html_e( '0165 · Column slots · 0183 · Contribution budget', 'vpg-v2' ); ?></h2>
        <table class="widefat" style="max-width:640px"><thead><tr><th><?php esc_html_e( 'Section', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Recurring voice', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Target / issue', 'vpg-v2' ); ?></th></tr></thead><tbody>
          <?php foreach ( $sections as $sv => $sl ) : ?>
            <tr><td><strong><?php echo esc_html( $sl ); ?></strong></td>
              <td><input type="text" name="col[<?php echo esc_attr( $sv ); ?>]" value="<?php echo esc_attr( $cols[ $sv ] ?? '' ); ?>" style="width:100%"></td>
              <td><input type="number" min="0" name="bud[<?php echo esc_attr( $sv ); ?>]" value="<?php echo esc_attr( $bud[ $sv ] ?? '' ); ?>" style="width:70px"></td></tr>
          <?php endforeach; ?>
        </tbody></table>
        <p><button name="vpg_mag_desk" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>

      <h2 style="margin-top:20px"><?php esc_html_e( '0185 · Theme pitch board', 'vpg-v2' ); ?></h2>
      <?php $pitches = array_reverse( (array) get_option( 'vpg_mag_pitches', [] ) ); if ( $pitches ) : ?>
        <ul><?php foreach ( array_slice( $pitches, 0, 20 ) as $p ) : ?><li><strong><?php echo esc_html( $p['theme'] ?? '' ); ?></strong> — <?php echo esc_html( $p['why'] ?? '' ); ?> <em><?php echo esc_html( $p['by'] ?? '' ); ?></em></li><?php endforeach; ?></ul>
      <?php else : ?><p class="description"><?php esc_html_e( 'No pitches yet. Embed [vpg_pitch_board] on a page to collect them.', 'vpg-v2' ); ?></p><?php endif; ?>

      <h2 style="margin-top:20px"><?php esc_html_e( '0168 · Letters awaiting curation', 'vpg-v2' ); ?></h2>
      <?php
      $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 20, 'post_status' => 'any' ] );
      $any = false;
      foreach ( $issues as $iss ) {
          foreach ( (array) get_post_meta( $iss->ID, '_vpg_letters', true ) as $k => $l ) {
              if ( ! empty( $l['ok'] ) ) continue; $any = true;
              $link = wp_nonce_url( admin_url( 'admin.php?page=vpg-mag-desk&approve_letter=' . $k . '&issue=' . $iss->ID ), 'vpg_mag_approve' );
              echo '<p>“' . esc_html( $l['body'] ?? '' ) . '” — ' . esc_html( $l['by'] ?? '' ) . ' <em>(' . esc_html( get_the_title( $iss ) ) . ')</em> <a class="button button-small" href="' . esc_url( $link ) . '">' . esc_html__( 'Publish', 'vpg-v2' ) . '</a></p>';
          }
      }
      if ( ! $any ) echo '<p class="description">' . esc_html__( 'No pending letters.', 'vpg-v2' ) . '</p>';
      ?>

      <h2 style="margin-top:20px"><?php esc_html_e( '0195 · Classifieds awaiting review', 'vpg-v2' ); ?></h2>
      <?php $ads = (array) get_option( 'vpg_classifieds', [] ); $anyad = false;
      foreach ( $ads as $k => $ad ) { if ( ! empty( $ad['ok'] ) ) continue; $anyad = true;
          $link = wp_nonce_url( admin_url( 'admin.php?page=vpg-mag-desk&approve_ad=' . $k ), 'vpg_mag_approve' );
          echo '<p><strong>' . esc_html( $ad['title'] ?? '' ) . '</strong> — ' . esc_html( $ad['body'] ?? '' ) . ' <em>' . esc_html( $ad['by'] ?? '' ) . '</em> <a class="button button-small" href="' . esc_url( $link ) . '">' . esc_html__( 'Approve', 'vpg-v2' ) . '</a></p>';
      }
      if ( ! $anyad ) echo '<p class="description">' . esc_html__( 'No pending ads.', 'vpg-v2' ) . '</p>';
      ?>

      <h2 style="margin-top:20px"><?php esc_html_e( '0186 · Issue metrics', 'vpg-v2' ); ?></h2>
      <table class="widefat" style="max-width:640px"><thead><tr><th><?php esc_html_e( 'Issue', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Views', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Avg. read depth', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'PDF', 'vpg-v2' ); ?></th><th></th></tr></thead><tbody>
        <?php foreach ( $issues as $iss ) : $dp = (array) get_post_meta( $iss->ID, '_vpg_read_depth', true ); $avg = ! empty( $dp['n'] ) ? round( $dp['sum'] / $dp['n'] ) : 0; ?>
          <tr><td><?php echo esc_html( get_the_title( $iss ) ); ?></td>
            <td><?php echo (int) get_post_meta( $iss->ID, '_vpg_issue_views', true ); ?></td>
            <td><?php echo (int) $avg; ?>%</td>
            <td><?php echo (int) get_post_meta( $iss->ID, '_vpg_pdf_hits', true ); ?></td>
            <td><a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=vpg-mag-desk&srgb=' . $iss->ID ), 'vpg_mag_approve' ) ); ?>"><?php esc_html_e( '0180 · sRGB images', 'vpg-v2' ); ?></a></td></tr>
        <?php endforeach; ?>
      </tbody></table>

      <h2 style="margin-top:20px"><?php esc_html_e( '0166 · Start a dramaturgy', 'vpg-v2' ); ?></h2>
      <p class="description"><?php esc_html_e( 'Seed a new draft issue with a rise / peak / close three-act scaffold.', 'vpg-v2' ); ?></p>
      <p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_mag_dramaturgy' ), 'vpg_mag_dramaturgy' ) ); ?>"><?php esc_html_e( 'New issue · three-act scaffold', 'vpg-v2' ); ?></a></p>
    </div>
    <?php
}

/* 0166 · dramaturgy scaffold → a new draft with three shaped article rows */
add_action( 'admin_post_vpg_mag_dramaturgy', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_mag_dramaturgy' );
    $articles = [
        [ 'title' => __( 'The rise', 'vpg-v2' ),  'author' => '', 'body' => __( 'Open quietly. Establish the place and the mood.', 'vpg-v2' ), 'image_id' => 0, 'page_break_after' => true, 'section' => 'feature' ],
        [ 'title' => __( 'The peak', 'vpg-v2' ),  'author' => '', 'body' => __( 'The strongest images sit here — the emotional high.', 'vpg-v2' ), 'image_id' => 0, 'page_break_after' => true, 'section' => 'feature' ],
        [ 'title' => __( 'The close', 'vpg-v2' ), 'author' => '', 'body' => __( 'Resolve. A last, calmer frame to leave on.', 'vpg-v2' ), 'image_id' => 0, 'page_break_after' => false, 'section' => 'journal' ],
    ];
    $id = wp_insert_post( [ 'post_type' => 'vpg_magazine', 'post_status' => 'draft', 'post_title' => __( 'New issue (dramaturgy)', 'vpg-v2' ) ] );
    if ( $id && ! is_wp_error( $id ) ) {
        update_post_meta( $id, '_vpg_articles', wp_json_encode( $articles ) );
        wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $id ) ); exit;
    }
    wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine' ) ); exit;
} );

/* pdf hit counter — bump when the built PDF file is requested via the issue */
add_action( 'template_redirect', function () {
    if ( is_singular( 'vpg_magazine' ) && isset( $_GET['pdf'] ) ) {
        update_post_meta( get_the_ID(), '_vpg_pdf_hits', (int) get_post_meta( get_the_ID(), '_vpg_pdf_hits', true ) + 1 );
        $u = get_post_meta( get_the_ID(), '_vpg_pdf_url', true );
        if ( $u ) { wp_redirect( $u ); exit; }
    }
} );

/* 0190 · serif accent for pull-quotes in the reader (once per issue page) */
add_action( 'wp_head', function () {
    if ( ! is_singular( 'vpg_magazine' ) ) return;
    echo '<style>.vpg-mag-article__body blockquote{font-family:var(--vpg-font-serif,Georgia),serif;font-style:italic;font-size:1.15em;border-left:3px solid var(--g-red,#E5341F);padding-left:16px;margin:20px 0;color:var(--vpg-ink,#2C2C2C)}.vpg-fn-ref a{text-decoration:none;color:var(--g-red,#E5341F);font-weight:700}</style>';
} );

/* let editors add a "swap page" and "reprint" section value (0194 / 0176) */
add_filter( 'vpg_mag_sections', function ( $s ) {
    $s['swap']    = __( 'Partner swap-page', 'vpg-v2' );
    $s['archive'] = __( 'From the archive', 'vpg-v2' );
    return $s;
} );
