<?php
/**
 * VPG v3 — Cluster 12 · Wettbewerbe & Challenges.
 *
 * Extends the vpg_competition CPT (entries wall, editor's winner) with the
 * whole tournament culture: categories (beginner / analogue / mobile /
 * series), jury modes (editor / blind / public+jury), a fair deadline, a
 * submission limit, material prizes, a no-AI rule, prompt formats (colour,
 * square, macro, prop, perspective lottery, sound-to-image, text-to-image,
 * weather roulette, double exposure), public voting, a best-caption award,
 * a winner interview, per-entry feedback, statistics, a challenge calendar,
 * community theme suggestions, a hall of fame and the sponsoring principles.
 *
 *   0442 mini task · 0443 blind jury · 0444 rotation · 0445 public prize
 *   0446 suggestions · 0447 series · 0448 beginner · 0449 analogue
 *   0450 mobile · 0451 sprint · 0452 square · 0453 colour · 0454 macro
 *   0456 winner interview · 0458 entry limit · 0459 deadline · 0460 honesty
 *   0461 cross-collective · 0462 into the issue · 0463 prop · 0464 perspective
 *   0465 sound · 0466 text · 0467 weather · 0468 sustainable · 0469 double-exp
 *   0470 no AI · 0471 entry feedback · 0472 calendar · 0473 spontaneous
 *   0474 team · 0475 return match · 0476 material prizes · 0477 sponsoring
 *   0478 best caption · 0479 statistics · 0480 hall of fame
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function vpg_comp_categories() {
    return [ 'open' => __( 'Open', 'vpg-v2' ), 'beginner' => __( 'First-year class', 'vpg-v2' ), 'analogue' => __( 'Analogue', 'vpg-v2' ), 'mobile' => __( 'Phone', 'vpg-v2' ), 'series' => __( 'Series (3 images)', 'vpg-v2' ), 'team' => __( 'District teams', 'vpg-v2' ) ];
}
function vpg_comp_meta( $id ) {
    return [
        'category'   => get_post_meta( $id, '_vpg_comp_category', true ) ?: 'open',
        'jury'       => get_post_meta( $id, '_vpg_comp_jury', true ) ?: 'editor',
        'deadline'   => get_post_meta( $id, '_vpg_comp_deadline', true ),
        'prize'      => get_post_meta( $id, '_vpg_comp_prize', true ),
        'max'        => (int) ( get_post_meta( $id, '_vpg_comp_max', true ) ?: 2 ),
        'prompt'     => get_post_meta( $id, '_vpg_comp_prompt', true ),
        'prop'       => get_post_meta( $id, '_vpg_comp_prop', true ),
        'sound'      => get_post_meta( $id, '_vpg_comp_sound', true ),
        'textline'   => get_post_meta( $id, '_vpg_comp_textline', true ),
        'weather'    => get_post_meta( $id, '_vpg_comp_weather', true ) === '1',
        'perspective'=> get_post_meta( $id, '_vpg_comp_perspective', true ) === '1',
        'caption'    => get_post_meta( $id, '_vpg_comp_caption_award', true ) === '1',
        'noai'       => get_post_meta( $id, '_vpg_comp_noai', true ) !== '0',
    ];
}

/* ── Metabox ──────────────────────────────────────────────────────── */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-comp', '🏆 ' . __( 'Challenge setup', 'vpg-v2' ), 'vpg_render_comp_box', 'vpg_competition', 'normal', 'high' );
} );
function vpg_render_comp_box( $post ) {
    wp_nonce_field( 'vpg_comp', 'vpg_comp_nonce' );
    $m = vpg_comp_meta( $post->ID );
    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px">';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Category', 'vpg-v2' ) . '</label><br><select name="c_category">';
    foreach ( vpg_comp_categories() as $k => $l ) echo '<option value="' . esc_attr( $k ) . '"' . selected( $m['category'], $k, false ) . '>' . esc_html( $l ) . '</option>';
    echo '</select></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Jury mode', 'vpg-v2' ) . '</label><br><select name="c_jury">';
    foreach ( [ 'editor' => __( 'Editor picks', 'vpg-v2' ), 'blind' => __( 'Blind jury (names hidden)', 'vpg-v2' ), 'public' => __( 'Public + jury prize', 'vpg-v2' ) ] as $k => $l ) echo '<option value="' . esc_attr( $k ) . '"' . selected( $m['jury'], $k, false ) . '>' . esc_html( $l ) . '</option>';
    echo '</select></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Deadline', 'vpg-v2' ) . '</label><br><input type="date" name="c_deadline" value="' . esc_attr( $m['deadline'] ) . '"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Prize (material, not money)', 'vpg-v2' ) . '</label><br><input type="text" name="c_prize" value="' . esc_attr( $m['prize'] ) . '" style="width:100%" placeholder="' . esc_attr__( 'A roll of film, a book…', 'vpg-v2' ) . '"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Max entries per member', 'vpg-v2' ) . '</label><br><input type="number" min="1" max="5" name="c_max" value="' . esc_attr( $m['max'] ) . '" style="width:80px"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Prop that must appear', 'vpg-v2' ) . '</label><br><input type="text" name="c_prop" value="' . esc_attr( $m['prop'] ) . '" style="width:100%"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Sound to interpret (URL)', 'vpg-v2' ) . '</label><br><input type="url" name="c_sound" value="' . esc_attr( $m['sound'] ) . '" style="width:100%"></div>';
    echo '<div><label style="font-weight:600">' . esc_html__( 'Journal line to answer', 'vpg-v2' ) . '</label><br><input type="text" name="c_textline" value="' . esc_attr( $m['textline'] ) . '" style="width:100%"></div>';
    echo '</div>';
    echo '<p style="margin-top:10px">';
    foreach ( [ 'c_weather' => [ __( 'Weather roulette (task revealed with the forecast)', 'vpg-v2' ), $m['weather'] ], 'c_perspective' => [ __( 'Perspective lottery', 'vpg-v2' ), $m['perspective'] ], 'c_caption_award' => [ __( 'Best-caption award', 'vpg-v2' ), $m['caption'] ], 'c_noai' => [ __( 'AI images excluded', 'vpg-v2' ), $m['noai'] ] ] as $f => $pair ) {
        echo '<label style="display:block"><input type="checkbox" name="' . esc_attr( $f ) . '" value="1"' . checked( $pair[1], true, false ) . '> ' . esc_html( $pair[0] ) . '</label>';
    }
    echo '</p>';
    echo '<p><label style="font-weight:600">' . esc_html__( 'The task / rules (one sentence is enough)', 'vpg-v2' ) . '</label><br><textarea name="c_prompt" rows="2" style="width:100%">' . esc_textarea( $m['prompt'] ) . '</textarea></p>';
}
add_action( 'save_post_vpg_competition', function ( $id ) {
    if ( ! isset( $_POST['vpg_comp_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_comp_nonce'], 'vpg_comp' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;
    update_post_meta( $id, '_vpg_comp_category', sanitize_key( $_POST['c_category'] ?? 'open' ) );
    update_post_meta( $id, '_vpg_comp_jury', sanitize_key( $_POST['c_jury'] ?? 'editor' ) );
    update_post_meta( $id, '_vpg_comp_deadline', sanitize_text_field( wp_unslash( $_POST['c_deadline'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_comp_prize', sanitize_text_field( wp_unslash( $_POST['c_prize'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_comp_max', max( 1, min( 5, (int) ( $_POST['c_max'] ?? 2 ) ) ) );
    update_post_meta( $id, '_vpg_comp_prompt', sanitize_textarea_field( wp_unslash( $_POST['c_prompt'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_comp_prop', sanitize_text_field( wp_unslash( $_POST['c_prop'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_comp_sound', esc_url_raw( wp_unslash( $_POST['c_sound'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_comp_textline', sanitize_text_field( wp_unslash( $_POST['c_textline'] ?? '' ) ) );
    foreach ( [ 'c_weather' => '_vpg_comp_weather', 'c_perspective' => '_vpg_comp_perspective', 'c_caption_award' => '_vpg_comp_caption_award' ] as $f => $mk ) update_post_meta( $id, $mk, empty( $_POST[ $f ] ) ? '' : '1' );
    update_post_meta( $id, '_vpg_comp_noai', empty( $_POST['c_noai'] ) ? '0' : '1' );
} );

/* ── Header (rules, category, deadline, prize, prompts) ───────────── */
function vpg_competition_header( $id ) {
    $m = vpg_comp_meta( $id );
    $persp = [ __( 'ground level', 'vpg-v2' ), __( 'hip height', 'vpg-v2' ), __( 'above the head', 'vpg-v2' ) ];
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap" style="max-width:820px">
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px">
        <span style="font:700 11px/1 'Archivo',sans-serif;background:var(--g-ink,#0B0B0B);color:#fff;padding:6px 12px"><?php echo esc_html( vpg_comp_categories()[ $m['category'] ] ?? '' ); ?></span>
        <?php if ( $m['deadline'] ) : ?><span style="font:700 11px/1 'Archivo',sans-serif;border:1px solid var(--g-line);padding:6px 12px">⏱ <?php echo esc_html( sprintf( __( 'Deadline %s (Sunday midnight)', 'vpg-v2' ), $m['deadline'] ) ); ?></span><?php endif; ?>
        <?php if ( $m['prize'] ) : ?><span style="font:700 11px/1 'Archivo',sans-serif;border:1px solid var(--g-line);padding:6px 12px">🎁 <?php echo esc_html( $m['prize'] ); ?></span><?php endif; ?>
        <span style="font:700 11px/1 'Archivo',sans-serif;border:1px solid var(--g-line);padding:6px 12px"><?php echo esc_html( sprintf( __( 'Max %d entries', 'vpg-v2' ), $m['max'] ) ); ?></span>
        <?php if ( $m['noai'] ) : ?><span style="font:700 11px/1 'Archivo',sans-serif;border:1px solid var(--g-red);color:var(--g-red);padding:6px 12px">⌀ <?php esc_html_e( 'No AI images', 'vpg-v2' ); ?></span><?php endif; ?>
      </div>
      <?php if ( $m['prompt'] ) : ?><p style="font-size:18px;font-weight:700"><?php echo esc_html( $m['prompt'] ); ?></p><?php endif; ?>
      <?php if ( $m['prop'] ) : ?><p style="font-size:14px;color:var(--g-mid,#6A6A6A)">🎭 <?php echo esc_html( sprintf( __( 'This object must appear in every frame: %s', 'vpg-v2' ), $m['prop'] ) ); ?></p><?php endif; ?>
      <?php if ( $m['sound'] ) : ?><p style="font-size:14px"><a href="<?php echo esc_url( $m['sound'] ); ?>" target="_blank" rel="noopener">♫ <?php esc_html_e( 'Interpret this piece of music', 'vpg-v2' ); ?></a></p><?php endif; ?>
      <?php if ( $m['textline'] ) : ?><p style="font-size:14px;color:var(--g-mid,#6A6A6A)">✎ <?php echo esc_html( sprintf( __( 'Answer this line with a photograph: “%s”', 'vpg-v2' ), $m['textline'] ) ); ?></p><?php endif; ?>
      <?php if ( $m['perspective'] && is_user_logged_in() ) : ?><p style="font-size:14px">🎲 <?php echo esc_html( sprintf( __( 'Your drawn viewpoint: %s', 'vpg-v2' ), $persp[ get_current_user_id() % 3 ] ) ); ?></p><?php endif; ?>
      <?php if ( $m['weather'] ) : $wx = function_exists( 'vpg_weather' ) ? vpg_weather( 48.21, 16.37 ) : null;
        if ( $wx ) : ?><p style="font-size:14px">☂ <?php echo esc_html( sprintf( __( 'Weather roulette — right now it’s %1$s, %2$s. Shoot to that.', 'vpg-v2' ), $wx['temp'], $wx['label'] ) ); ?></p><?php endif; endif; ?>
    </div></section>
    <?php
}

/* ── Public voting (0445) + best caption (0478) + interview + stats ─ */
function vpg_comp_public_winner( $id ) {
    $best = 0; $bn = 0;
    foreach ( vpg_competition_entries( $id ) as $e ) { $n = count( array_filter( (array) get_post_meta( $e->ID, '_vpg_entry_votes', true ) ) ); if ( $n > $bn ) { $bn = $n; $best = $e->ID; } }
    return $best;
}
function vpg_competition_extras( $id, $entries ) {
    $m = vpg_comp_meta( $id );
    $pub = ( $m['jury'] === 'public' ) ? vpg_comp_public_winner( $id ) : 0;
    $voters = [];
    foreach ( $entries as $e ) foreach ( (array) get_post_meta( $e->ID, '_vpg_entry_votes', true ) as $v ) $voters[ $v ] = 1;
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap">
      <?php if ( $pub && wp_attachment_is_image( $pub ) ) : ?>
        <p class="g-kicker">● <?php esc_html_e( 'People’s choice', 'vpg-v2' ); ?></p>
        <figure style="margin:10px 0;max-width:520px"><img src="<?php echo esc_url( wp_get_attachment_image_url( $pub, 'large' ) ); ?>" alt="" style="width:100%"><figcaption class="g-meta" style="margin-top:8px"><?php echo esc_html( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $pub ) ) ); ?></figcaption></figure>
      <?php endif; ?>
      <p style="font-size:13px;color:var(--g-mid,#6A6A6A)">📊 <?php echo esc_html( sprintf( __( '%1$d entries · %2$d members voted.', 'vpg-v2' ), count( $entries ), count( $voters ) ) ); ?></p>
      <?php // 0456 winner interview
      $winner = (int) get_post_meta( $id, '_vpg_comp_winner', true );
      $wa = get_post_meta( $id, '_vpg_comp_interview', true );
      if ( $winner ) {
        $wauthor = (int) get_post_field( 'post_author', $winner );
        if ( is_array( $wa ) && $wa ) {
          echo '<p class="g-kicker" style="margin-top:16px">● ' . esc_html__( 'Three questions to the winner', 'vpg-v2' ) . '</p>';
          foreach ( $wa as $qa ) echo '<p style="margin:8px 0"><strong>' . esc_html( $qa['q'] ?? '' ) . '</strong><br>' . esc_html( $qa['a'] ?? '' ) . '</p>';
        } elseif ( get_current_user_id() === $wauthor ) {
          echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:16px;display:grid;gap:8px;max-width:560px">' . wp_nonce_field( 'vpg_comp_interview', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="vpg_comp_interview"><input type="hidden" name="competition" value="' . (int) $id . '">';
          echo '<p class="g-kicker">● ' . esc_html__( 'You won — the three questions', 'vpg-v2' ) . '</p>';
          foreach ( [ __( 'What were you after?', 'vpg-v2' ), __( 'The hardest part?', 'vpg-v2' ), __( 'One tip for the next entrant?', 'vpg-v2' ) ] as $i => $q ) echo '<label>' . esc_html( $q ) . '<input type="hidden" name="q[' . $i . ']" value="' . esc_attr( $q ) . '"><input type="text" name="a[' . $i . ']" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>';
          echo '<button class="g-btn g-btn--red" style="font-size:12px;justify-self:start">' . esc_html__( 'Publish answers', 'vpg-v2' ) . '</button></form>';
        }
      }
      ?>
    </div></section>
    <?php
}

/* ── Handlers ─────────────────────────────────────────────────────── */
add_action( 'admin_post_vpg_comp_vote', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_comp_vote' );
    $entry = (int) ( $_POST['entry'] ?? 0 );
    if ( get_post_meta( $entry, '_vpg_competition', true ) === '' ) wp_die( 'No entry', 404 );
    $comp = (int) get_post_meta( $entry, '_vpg_competition', true );
    // one vote per member per competition
    foreach ( vpg_competition_entries( $comp ) as $e ) {
        $v = array_filter( (array) get_post_meta( $e->ID, '_vpg_entry_votes', true ), fn( $x ) => (int) $x !== get_current_user_id() );
        update_post_meta( $e->ID, '_vpg_entry_votes', array_values( $v ) );
    }
    $v = (array) get_post_meta( $entry, '_vpg_entry_votes', true );
    $v[] = get_current_user_id();
    update_post_meta( $entry, '_vpg_entry_votes', array_values( array_unique( $v ) ) );
    wp_safe_redirect( get_permalink( $comp ) . '#entries' ); exit;
} );
add_action( 'admin_post_vpg_comp_interview', function () {
    check_admin_referer( 'vpg_comp_interview' );
    $id = (int) ( $_POST['competition'] ?? 0 );
    $winner = (int) get_post_meta( $id, '_vpg_comp_winner', true );
    if ( get_current_user_id() !== (int) get_post_field( 'post_author', $winner ) ) wp_die( 'Winner only', 403 );
    $qa = [];
    foreach ( (array) ( $_POST['q'] ?? [] ) as $i => $q ) $qa[] = [ 'q' => sanitize_text_field( wp_unslash( $q ) ), 'a' => sanitize_text_field( wp_unslash( $_POST['a'][ $i ] ?? '' ) ) ];
    update_post_meta( $id, '_vpg_comp_interview', $qa );
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );
/* 0471 · a reaction to every entry (editors) */
add_action( 'admin_post_vpg_comp_react', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden', 403 );
    check_admin_referer( 'vpg_comp_react' );
    $entry = (int) ( $_POST['entry'] ?? 0 );
    update_post_meta( $entry, '_vpg_entry_feedback', sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ) );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* ── Community theme suggestions (0446) + endpoints ───────────────── */
add_shortcode( 'vpg_challenge_suggest', function () {
    if ( ! is_user_logged_in() ) return '';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap">
      <?php wp_nonce_field( 'vpg_ch_suggest' ); ?><input type="hidden" name="action" value="vpg_ch_suggest">
      <input type="text" name="theme" maxlength="80" required placeholder="<?php esc_attr_e( 'A challenge theme', 'vpg-v2' ); ?>" style="flex:1;min-width:220px;padding:9px;border:1px solid var(--g-line)">
      <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Suggest', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_ch_suggest', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_ch_suggest' );
    $s = (array) get_option( 'vpg_challenge_suggestions', [] );
    $s[] = [ 'by' => wp_get_current_user()->display_name, 'theme' => sanitize_text_field( wp_unslash( $_POST['theme'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_challenge_suggestions', array_slice( $s, -200 ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

add_action( 'init', function () {
    add_rewrite_rule( '^challenge-kalender/?$', 'index.php?vpg_chcal=1', 'top' );
    add_rewrite_rule( '^challenge-ruhm/?$', 'index.php?vpg_chhall=1', 'top' );
    add_rewrite_rule( '^challenge-regeln/?$', 'index.php?vpg_chrules=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_chcal', 'vpg_chhall', 'vpg_chrules' ); return $v; } );
add_action( 'template_redirect', function () {
    if ( get_query_var( 'vpg_chcal' ) ) {
        $cal = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_challenge_calendar', '' ) ) ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Planned ahead', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The challenge <em>calendar</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:640px"><div class="g-list">
          <?php foreach ( $cal as $line ) { $p = array_map( 'trim', explode( '—', $line, 2 ) ); echo '<div class="g-row" style="cursor:default;grid-template-columns:140px 1fr"><span style="font-weight:700;color:var(--g-mid)">' . esc_html( $p[0] ) . '</span><span>' . esc_html( $p[1] ?? '' ) . '</span></div>'; } ?>
        </div><?php if ( shortcode_exists( 'vpg_challenge_suggest' ) ) { echo '<div style="margin-top:20px"><p class="g-kicker">● ' . esc_html__( 'Suggest a theme', 'vpg-v2' ) . '</p>' . do_shortcode( '[vpg_challenge_suggest]' ) . '</div>'; } ?></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_chhall' ) ) {
        $comps = get_posts( [ 'post_type' => 'vpg_competition', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_vpg_comp_winner' ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Hall of fame', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Every <em>winner</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div data-vpg-gallery style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
          <?php foreach ( $comps as $c ) { $w = (int) get_post_meta( $c->ID, '_vpg_comp_winner', true ); $u = wp_get_attachment_image_url( $w, 'medium' ); $f = wp_get_attachment_image_url( $w, 'full' ); if ( ! $u ) continue; echo '<figure style="margin:0"><img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover"><figcaption class="g-meta" style="margin-top:6px">' . esc_html( get_the_title( $c ) . ' · ' . get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $w ) ) ) . '</figcaption></figure>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_chrules' ) ) {
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'How our challenges work', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Fair by <em>rule</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <h3><?php esc_html_e( 'One deadline, always', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'Every challenge closes Sunday at midnight. Same day, every time.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Curate, don’t flood', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'A submission limit per member. Choosing your best is part of the exercise.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'No AI images', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'These are photographs. AI-generated pictures are excluded — the rule is visible on every challenge.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Prizes, not money', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'A roll of film, a book. Small, material, kind.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Sponsoring: yes, influence: no', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'A sponsor may give a prize. A sponsor never shapes the theme or the jury. This is documented.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Honest consolation', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'No participation medals — but real, named mentions. Every entry gets a reaction.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'The return match', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'The winning theme sets the next challenge’s task.', 'vpg-v2' ); ?></p>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* ── Admin · challenge desk (calendar, suggestions, cross-collective) ─ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_event', __( 'Challenge desk', 'vpg-v2' ), '🏆 ' . __( 'Challenge desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-challenges', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_ch'] ) && check_admin_referer( 'vpg_ch' ) ) {
            update_option( 'vpg_challenge_calendar', sanitize_textarea_field( wp_unslash( $_POST['calendar'] ?? '' ) ), false );
            update_option( 'vpg_cross_collective', sanitize_textarea_field( wp_unslash( $_POST['cross'] ?? '' ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        echo '<div class="wrap"><h1>🏆 ' . esc_html__( 'Challenge desk', 'vpg-v2' ) . '</h1>';
        echo '<p class="description">' . esc_html__( 'Pages: /challenge-kalender/ · /challenge-ruhm/ · /challenge-regeln/', 'vpg-v2' ) . '</p>';
        echo '<form method="post">'; wp_nonce_field( 'vpg_ch' );
        echo '<h2>0472 · ' . esc_html__( 'Calendar (one per line: “Month — theme”)', 'vpg-v2' ) . '</h2><textarea name="calendar" rows="6" style="width:100%;max-width:720px">' . esc_textarea( get_option( 'vpg_challenge_calendar', '' ) ) . '</textarea>';
        echo '<h2>0461 · ' . esc_html__( 'Cross-collective battles (Graz, Linz…)', 'vpg-v2' ) . '</h2><textarea name="cross" rows="3" style="width:100%;max-width:720px">' . esc_textarea( get_option( 'vpg_cross_collective', '' ) ) . '</textarea>';
        echo '<p><button name="vpg_ch" class="button button-primary">' . esc_html__( 'Save', 'vpg-v2' ) . '</button></p></form>';
        $sug = array_reverse( (array) get_option( 'vpg_challenge_suggestions', [] ) );
        echo '<h2>0446 · ' . esc_html__( 'Theme suggestions', 'vpg-v2' ) . '</h2>';
        if ( $sug ) { echo '<ul>'; foreach ( array_slice( $sug, 0, 40 ) as $s ) echo '<li><strong>' . esc_html( $s['theme'] ) . '</strong> — <em>' . esc_html( $s['by'] ) . '</em></li>'; echo '</ul>'; }
        else echo '<p class="description">' . esc_html__( 'None yet. Embed [vpg_challenge_suggest].', 'vpg-v2' ) . '</p>';
        echo '</div>';
    } );
}, 21 );
