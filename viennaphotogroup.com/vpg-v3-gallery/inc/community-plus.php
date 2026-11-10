<?php
/**
 * VPG v3 — Cluster 11 · Community & Zusammenarbeit.
 *
 * The neighbourhood becomes a workshop: shared boards (help, lending,
 * rideshare, craft-swap, watchlist, memories, relay), a skill directory, a
 * lightweight Q&A with a discreet second-opinion flag, collective votes &
 * quarterly surveys, a living community handbook (code of conduct, statutes,
 * conflict guide, feedback-language guide, crisis contacts, team roles,
 * handover rituals, transparency log), member status (pause / alumni), a
 * welcome committee, a birthday feature, rotating home curators, neighbourhood
 * groups and an availability address book.
 *
 *   0403 four-eyes · 0404 relay · 0405 archive · 0406 Q&A · 0407 help
 *   0408 lending · 0409 rideshare · 0410 skills · 0411 translation
 *   0412 second opinion · 0413 group series · 0414 rotating curators
 *   0415 stammtisch · 0416 welcome · 0417 votes · 0418 transparency
 *   0419 conflict · 0420 pause · 0421 alumni · 0422 birthday · 0424 duel
 *   0425 watchlist · 0426 neighbourhood · 0427 craft-swap · 0428 workshop day
 *   0429 memories · 0430 address book · 0431 guest programme · 0432 code
 *   0433 feedback guide · 0434 surveys · 0436 statutes · 0437 roles
 *   0438 handover · 0439 crisis · 0440 assembly
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Shared boards — one mechanism, many pinboards                     */
/* ════════════════════════════════════════════════════════════════ */
function vpg_board_defs() {
    return [
        'help'      => [ __( 'Help wanted', 'vpg-v2' ),      __( '“Looking for an assistant on Saturday”', 'vpg-v2' ) ], // 0407
        'lending'   => [ __( 'Lending exchange', 'vpg-v2' ), __( 'Lenses & tripods between members', 'vpg-v2' ) ],       // 0408
        'rideshare' => [ __( 'Ride board', 'vpg-v2' ),       __( 'Share a car to spots beyond the U-Bahn', 'vpg-v2' ) ], // 0409
        'craftswap' => [ __( 'Craft swap', 'vpg-v2' ),       __( 'Photography for bookbinding for screenprint', 'vpg-v2' ) ], // 0427
        'watchlist' => [ __( 'Shared watchlist', 'vpg-v2' ), __( 'Exhibitions to see together', 'vpg-v2' ) ],            // 0425
        'memories'  => [ __( 'Memory threads', 'vpg-v2' ),   __( '“Remember the fog walk?”', 'vpg-v2' ) ],               // 0429
        'relay'     => [ __( 'Relay & edit partners', 'vpg-v2' ), __( 'Pass a motif on · find a four-eyes edit partner', 'vpg-v2' ) ], // 0404 / 0403
        'archive'   => [ __( 'Community archive calls', 'vpg-v2' ), __( 'Shared city-theme collections to build together', 'vpg-v2' ) ], // 0405
    ];
}
add_shortcode( 'vpg_board', function ( $atts ) {
    $slug = sanitize_key( $atts['slug'] ?? '' );
    $defs = vpg_board_defs();
    if ( ! isset( $defs[ $slug ] ) ) return '';
    $items = array_filter( (array) get_option( 'vpg_board_' . $slug, [] ), fn( $i ) => empty( $i['done'] ) );
    ob_start(); ?>
    <div class="vpg-board">
      <p class="g-kicker">● <?php echo esc_html( $defs[ $slug ][0] ); ?></p>
      <p style="color:var(--g-mid,#6A6A6A);font-size:13px;margin:2px 0 12px"><?php echo esc_html( $defs[ $slug ][1] ); ?></p>
      <?php if ( $items ) : ?>
      <ul style="list-style:none;padding:0;margin:0 0 12px">
        <?php foreach ( array_slice( array_reverse( $items ), 0, 30 ) as $it ) : ?>
          <li style="border-top:1px solid var(--g-line,#E6E5E1);padding:8px 0;font-size:14px"><?php echo esc_html( $it['text'] ?? '' ); ?> <span style="color:var(--g-mid,#6A6A6A);font-size:12px">— <?php echo esc_html( $it['by'] ?? '' ); ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px">
        <?php wp_nonce_field( 'vpg_board_post' ); ?><input type="hidden" name="action" value="vpg_board_post"><input type="hidden" name="board" value="<?php echo esc_attr( $slug ); ?>">
        <input type="text" name="text" maxlength="200" required placeholder="<?php esc_attr_e( 'Post to the board…', 'vpg-v2' ); ?>" style="flex:1;padding:8px;border:1px solid var(--g-line)">
        <button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Pin it', 'vpg-v2' ); ?></button>
      </form>
      <?php endif; ?>
    </div>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_board_post', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_board_post' );
    $slug = sanitize_key( $_POST['board'] ?? '' );
    if ( ! isset( vpg_board_defs()[ $slug ] ) ) wp_die( 'Bad board', 400 );
    $items = (array) get_option( 'vpg_board_' . $slug, [] );
    $items[] = [ 'u' => get_current_user_id(), 'by' => wp_get_current_user()->display_name, 'text' => sanitize_text_field( wp_unslash( $_POST['text'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_board_' . $slug, array_slice( $items, -200 ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* a hub page /pinnwand/ with every board */
add_action( 'init', function () {
    add_rewrite_rule( '^pinnwand/?$', 'index.php?vpg_boards=1', 'top' );
    add_rewrite_rule( '^handbuch/?$', 'index.php?vpg_handbook=1', 'top' );
    add_rewrite_rule( '^koennen/?$',  'index.php?vpg_skills=1', 'top' );
    add_rewrite_rule( '^adressbuch/?$','index.php?vpg_addressbook=1', 'top' );
    add_rewrite_rule( '^nachbarschaft/(1\d{2}0)/?$', 'index.php?vpg_hood=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_boards', 'vpg_handbook', 'vpg_skills', 'vpg_addressbook', 'vpg_hood' ); return $v; } );

/* ════════════════════════════════════════════════════════════════ */
/*  0406 Q&A + 0412 second opinion                                    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    register_post_type( 'vpg_qa', [
        'label' => __( 'Q&A', 'vpg-v2' ), 'public' => true, 'has_archive' => 'fragen',
        'show_in_menu' => 'edit.php', 'supports' => [ 'title', 'editor', 'author', 'comments' ],
        'rewrite' => [ 'slug' => 'frage' ], 'menu_icon' => 'dashicons-format-chat',
    ] );
}, 11 );
add_shortcode( 'vpg_ask', function () {
    if ( ! is_user_logged_in() ) return '<p>' . esc_html__( 'Sign in to ask.', 'vpg-v2' ) . '</p>';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:8px;max-width:560px">
      <?php wp_nonce_field( 'vpg_ask' ); ?><input type="hidden" name="action" value="vpg_ask">
      <input type="text" name="title" required maxlength="140" placeholder="<?php esc_attr_e( 'Your question', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)">
      <textarea name="body" rows="3" placeholder="<?php esc_attr_e( 'Details (optional)', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)"></textarea>
      <label style="font-size:13px"><input type="checkbox" name="second" value="1"> <?php esc_html_e( '0412 · A discreet second opinion (members only)', 'vpg-v2' ); ?></label>
      <button class="g-btn g-btn--red" style="font-size:12px;justify-self:start"><?php esc_html_e( 'Ask the room', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_ask', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_ask' );
    $id = wp_insert_post( [ 'post_type' => 'vpg_qa', 'post_status' => empty( $_POST['second'] ) ? 'publish' : 'private', 'post_author' => get_current_user_id(), 'post_title' => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ), 'post_content' => sanitize_textarea_field( wp_unslash( $_POST['body'] ?? '' ) ) ] );
    if ( $id && ! empty( $_POST['second'] ) ) update_post_meta( $id, '_vpg_second_opinion', 1 );
    wp_safe_redirect( ( $id && ! is_wp_error( $id ) ) ? get_permalink( $id ) : ( wp_get_referer() ?: home_url() ) ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0417 votes + 0434 surveys — admin-created polls                   */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_polls', function () {
    $polls = array_filter( (array) get_option( 'vpg_polls', [] ), fn( $p ) => ! empty( $p['open'] ) );
    if ( ! $polls ) return '<p>' . esc_html__( 'No open votes right now.', 'vpg-v2' ) . '</p>';
    ob_start();
    foreach ( $polls as $pid => $poll ) {
        $votes = (array) ( $poll['votes'] ?? [] );
        $mine = get_current_user_id() && isset( $votes[ get_current_user_id() ] );
        $tally = array_count_values( array_map( 'strval', $votes ) );
        $total = max( 1, array_sum( $tally ) );
        echo '<div style="border:1px solid var(--g-line,#E6E5E1);padding:16px;margin-bottom:12px"><p style="font-weight:800;margin:0 0 10px">' . esc_html( $poll['q'] ?? '' ) . '</p>';
        foreach ( (array) ( $poll['opts'] ?? [] ) as $i => $opt ) {
            $pct = round( ( $tally[ $i ] ?? 0 ) / $total * 100 );
            if ( $mine || ! is_user_logged_in() ) {
                echo '<div style="margin-bottom:6px"><div style="display:flex;justify-content:space-between;font-size:13px"><span>' . esc_html( $opt ) . '</span><span>' . (int) $pct . '%</span></div><div style="height:6px;background:var(--g-line)"><div style="height:6px;width:' . (int) $pct . '%;background:var(--g-red)"></div></div></div>';
            } else {
                echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">' . wp_nonce_field( 'vpg_poll_vote', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="vpg_poll_vote"><input type="hidden" name="poll" value="' . esc_attr( $pid ) . '"><input type="hidden" name="opt" value="' . $i . '"><button class="g-btn g-btn--ghost" style="font-size:12px;margin:0 6px 6px 0">' . esc_html( $opt ) . '</button></form>';
            }
        }
        echo '</div>';
    }
    return ob_get_clean();
} );
add_action( 'admin_post_vpg_poll_vote', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_poll_vote' );
    $polls = (array) get_option( 'vpg_polls', [] );
    $pid = sanitize_key( $_POST['poll'] ?? '' );
    if ( ! isset( $polls[ $pid ] ) ) wp_die( 'No poll', 404 );
    $polls[ $pid ]['votes'][ get_current_user_id() ] = (int) ( $_POST['opt'] ?? 0 );
    update_option( 'vpg_polls', $polls, false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0410 skills + 0411 translation · directory + member field         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_save_skills', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_skills' );
    $s = sanitize_text_field( wp_unslash( $_POST['skills'] ?? '' ) );
    $s !== '' ? update_user_meta( get_current_user_id(), '_vpg_skills', $s ) : delete_user_meta( get_current_user_id(), '_vpg_skills' );
    update_user_meta( get_current_user_id(), '_vpg_can_translate', empty( $_POST['translate'] ) ? '' : '1' );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/koennen/' ) ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Member status (0420 pause / 0421 alumni) + skills form + status   */
/*  rendered on the profile                                           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'vpg_profile_sections', function ( $user ) {
    $status = get_user_meta( $user->ID, '_vpg_status', true );
    $skills = get_user_meta( $user->ID, '_vpg_skills', true );
    if ( $status === 'pause' ) echo '<div class="vpg-wrap" style="max-width:920px;margin:0 auto"><section class="vpg-section vpg-section--tight"><p style="border:1px solid var(--g-line,#E6E5E1);padding:10px 14px;font-size:13px">⏸ ' . esc_html__( 'On a break — still a member, just resting.', 'vpg-v2' ) . '</p></section></div>';
    elseif ( $status === 'alumni' ) echo '<div class="vpg-wrap" style="max-width:920px;margin:0 auto"><section class="vpg-section vpg-section--tight"><p style="border:1px solid var(--g-line,#E6E5E1);padding:10px 14px;font-size:13px">🎓 ' . esc_html__( 'Alumnus — reading along from afar.', 'vpg-v2' ) . '</p></section></div>';
    if ( $skills ) echo '<div class="vpg-wrap" style="max-width:920px;margin:0 auto"><section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Can help with', 'vpg-v2' ) . '</p><p style="margin-top:6px">' . esc_html( $skills ) . ( get_user_meta( $user->ID, '_vpg_can_translate', true ) === '1' ? ' · ' . esc_html__( 'translation', 'vpg-v2' ) : '' ) . '</p></section></div>';
}, 30 );

/* status saved from the dashboard extra form (0420/0421) */
add_action( 'admin_post_vpg_save_status', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_status' );
    $s = sanitize_key( $_POST['status'] ?? '' );
    in_array( $s, [ 'pause', 'alumni' ], true ) ? update_user_meta( get_current_user_id(), '_vpg_status', $s ) : delete_user_meta( get_current_user_id(), '_vpg_status' );
    // 0430 · weekly availability
    $av = sanitize_text_field( wp_unslash( $_POST['availability'] ?? '' ) );
    $av !== '' ? update_user_meta( get_current_user_id(), '_vpg_availability', $av ) : delete_user_meta( get_current_user_id(), '_vpg_availability' );
    // 0426 · home district
    $hd = preg_replace( '/\D/', '', wp_unslash( $_POST['home_district'] ?? '' ) );
    ( $hd && function_exists( 'vpg_district_name' ) && vpg_district_name( $hd ) ) ? update_user_meta( get_current_user_id(), '_vpg_home_district', $hd ) : delete_user_meta( get_current_user_id(), '_vpg_home_district' );
    // 0416 · welcome committee opt-in
    update_user_meta( get_current_user_id(), '_vpg_welcomer', empty( $_POST['welcomer'] ) ? '' : '1' );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0416 welcome committee — greet each newcomer                      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'user_register', function ( $uid ) {
    $welcomers = get_users( [ 'meta_key' => '_vpg_welcomer', 'meta_value' => '1', 'number' => 20, 'fields' => 'ID' ] );
    foreach ( $welcomers as $w ) {
        if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( (int) $w, __( 'A new member just joined — say hello and make them welcome.', 'vpg-v2' ), home_url( '/members/' ), 'community' );
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0422 birthday feature — a member's join-anniversary on the home    */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_birthdays', function () {
    $today = gmdate( 'm-d' );
    $users = get_users( [ 'number' => 1000, 'fields' => [ 'ID', 'display_name', 'user_registered', 'user_nicename' ] ] );
    $out = '';
    foreach ( $users as $u ) {
        if ( gmdate( 'm-d', strtotime( $u->user_registered ) ) !== $today ) continue;
        $yrs = (int) floor( ( time() - strtotime( $u->user_registered ) ) / YEAR_IN_SECONDS );
        if ( $yrs < 1 ) continue;
        $out .= '<a href="' . esc_url( home_url( '/members/' . $u->user_nicename . '/' ) ) . '" style="display:inline-block;margin-right:14px;font-weight:700">🎂 ' . esc_html( $u->display_name ) . ' · ' . esc_html( sprintf( _n( '%d year', '%d years', $yrs, 'vpg-v2' ), $yrs ) ) . '</a>';
    }
    return $out ? '<p style="font-size:13px">' . $out . '</p>' : '';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Public pages — boards hub, handbook, skills, addressbook, hood     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    if ( get_query_var( 'vpg_boards' ) ) {
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'The workshop', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>pinboard</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:820px;display:grid;gap:28px">
          <?php foreach ( array_keys( vpg_board_defs() ) as $slug ) echo do_shortcode( '[vpg_board slug="' . $slug . '"]' ); ?>
        </div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_skills' ) ) { // 0410 directory
        $members = get_users( [ 'meta_key' => '_vpg_skills', 'number' => 300 ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Who can what', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>skill</em> directory.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-list">
          <?php foreach ( $members as $m ) { $sk = get_user_meta( $m->ID, '_vpg_skills', true ); if ( ! $sk ) continue; echo '<a class="g-row" href="' . esc_url( home_url( '/members/' . $m->user_nicename . '/' ) ) . '"><span style="font-weight:700;min-width:160px">' . esc_html( $m->display_name ) . '</span><span>' . esc_html( $sk ) . ( get_user_meta( $m->ID, '_vpg_can_translate', true ) === '1' ? ' · ' . esc_html__( 'translation', 'vpg-v2' ) : '' ) . '</span><span></span></a>'; } ?>
        </div>
        <?php if ( is_user_logged_in() ) : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:20px;display:flex;gap:8px;flex-wrap:wrap">
          <?php wp_nonce_field( 'vpg_skills' ); ?><input type="hidden" name="action" value="vpg_save_skills">
          <input type="text" name="skills" value="<?php echo esc_attr( get_user_meta( get_current_user_id(), '_vpg_skills', true ) ); ?>" placeholder="<?php esc_attr_e( 'Retouching, analogue developing…', 'vpg-v2' ); ?>" style="flex:1;min-width:240px;padding:9px;border:1px solid var(--g-line)">
          <label style="align-self:center;font-size:13px"><input type="checkbox" name="translate" value="1"<?php checked( get_user_meta( get_current_user_id(), '_vpg_can_translate', true ), '1' ); ?>> <?php esc_html_e( 'I can translate', 'vpg-v2' ); ?></label>
          <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Save my skills', 'vpg-v2' ); ?></button>
        </form>
        <?php endif; ?>
        </div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_addressbook' ) ) { // 0430
        $members = get_users( [ 'meta_key' => '_vpg_availability', 'number' => 300 ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Who’s around when', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>address book</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-list">
          <?php foreach ( $members as $m ) { $av = get_user_meta( $m->ID, '_vpg_availability', true ); if ( ! $av ) continue; echo '<div class="g-row" style="cursor:default"><span style="font-weight:700;min-width:160px">' . esc_html( $m->display_name ) . '</span><span>' . esc_html( $av ) . '</span><span></span></div>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( $code = get_query_var( 'vpg_hood' ) ) { // 0426 neighbourhood
        $members = get_users( [ 'meta_key' => '_vpg_home_district', 'meta_value' => $code, 'number' => 100 ] );
        $reads = function_exists( 'vpg_district_reads' ) ? vpg_district_reads( $code ) : [];
        $name = function_exists( 'vpg_district_name' ) ? vpg_district_name( $code ) : $code;
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Neighbourhood', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo esc_html( $code . ' · ' . $name ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap">
          <?php if ( $members ) : ?><p class="g-kicker">● <?php esc_html_e( 'Members here', 'vpg-v2' ); ?></p><p style="margin:8px 0 20px"><?php foreach ( $members as $m ) echo '<a href="' . esc_url( home_url( '/members/' . $m->user_nicename . '/' ) ) . '" style="margin-right:12px">' . esc_html( $m->display_name ) . '</a>'; ?></p><?php endif; ?>
          <p style="font-size:13px"><a href="<?php echo esc_url( home_url( '/bezirk/' . $code . '/' ) ); ?>"><?php esc_html_e( 'The district page', 'vpg-v2' ); ?> →</a></p>
        </div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_handbook' ) ) { // 0418/0419/0432/0433/0436/0437/0438/0439
        $doc = (array) get_option( 'vpg_handbook', [] );
        $sections = [
            'code'     => __( '0432 · Code of conduct', 'vpg-v2' ),
            'feedback' => __( '0433 · How we talk about pictures', 'vpg-v2' ),
            'statutes' => __( '0436 · Statutes (a living document)', 'vpg-v2' ),
            'conflict' => __( '0419 · How we disagree', 'vpg-v2' ),
            'roles'    => __( '0437 · Who’s responsible for what', 'vpg-v2' ),
            'handover' => __( '0438 · Handover rituals', 'vpg-v2' ),
            'crisis'   => __( '0439 · Crisis contacts', 'vpg-v2' ),
            'log'      => __( '0418 · Decisions, on the record', 'vpg-v2' ),
        ];
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'How we work together', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>handbook</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <?php foreach ( $sections as $k => $label ) : $txt = trim( (string) ( $doc[ $k ] ?? '' ) ); if ( ! $txt ) continue; ?>
            <h3><?php echo esc_html( $label ); ?></h3><?php echo wp_kses_post( wpautop( $txt ) ); ?>
          <?php endforeach; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · community desk (handbook, polls, roles, welcomers,        */
/*         rotating curator, guest programme, assembly)               */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'users.php', __( 'Community desk', 'vpg-v2' ), '🤝 ' . __( 'Community desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-community', 'vpg_community_desk' );
} );
function vpg_community_desk() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['vpg_cd'] ) && check_admin_referer( 'vpg_cd' ) ) {
        $doc = [];
        foreach ( [ 'code', 'feedback', 'statutes', 'conflict', 'roles', 'handover', 'crisis', 'log' ] as $k ) $doc[ $k ] = sanitize_textarea_field( wp_unslash( $_POST[ 'hb_' . $k ] ?? '' ) );
        update_option( 'vpg_handbook', $doc, false );
        update_option( 'vpg_home_curator', sanitize_text_field( wp_unslash( $_POST['curator'] ?? '' ) ), false );
        update_option( 'vpg_guest_programme', sanitize_textarea_field( wp_unslash( $_POST['guests'] ?? '' ) ), false );
        // create a poll
        if ( ! empty( $_POST['poll_q'] ) ) {
            $polls = (array) get_option( 'vpg_polls', [] );
            $polls[ 'p' . time() ] = [ 'q' => sanitize_text_field( wp_unslash( $_POST['poll_q'] ) ), 'opts' => array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", wp_unslash( $_POST['poll_opts'] ?? '' ) ) ) ) ), 'open' => 1, 'votes' => [] ];
            update_option( 'vpg_polls', $polls, false );
        }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $doc = (array) get_option( 'vpg_handbook', [] );
    ?>
    <div class="wrap"><h1>🤝 <?php esc_html_e( 'Community desk', 'vpg-v2' ); ?></h1>
      <p class="description"><?php printf( esc_html__( 'Pages: %1$s · %2$s · %3$s · %4$s', 'vpg-v2' ), '<a href="' . esc_url( home_url( '/pinnwand/' ) ) . '">/pinnwand/</a>', '<a href="' . esc_url( home_url( '/handbuch/' ) ) . '">/handbuch/</a>', '<a href="' . esc_url( home_url( '/koennen/' ) ) . '">/koennen/</a>', '<a href="' . esc_url( home_url( '/adressbuch/' ) ) . '">/adressbuch/</a>' ); ?></p>
      <form method="post"><?php wp_nonce_field( 'vpg_cd' ); ?>
        <h2><?php esc_html_e( 'Handbook', 'vpg-v2' ); ?></h2>
        <?php foreach ( [ 'code' => 'Code of conduct', 'feedback' => 'Feedback-language guide', 'statutes' => 'Statutes', 'conflict' => 'Conflict guide', 'roles' => 'Team roles', 'handover' => 'Handover rituals', 'crisis' => 'Crisis contacts', 'log' => 'Transparency log' ] as $k => $lbl ) : ?>
          <p><label style="font-weight:600"><?php echo esc_html( $lbl ); ?></label><br><textarea name="hb_<?php echo esc_attr( $k ); ?>" rows="3" style="width:100%;max-width:760px"><?php echo esc_textarea( $doc[ $k ] ?? '' ); ?></textarea></p>
        <?php endforeach; ?>
        <h2><?php esc_html_e( '0414 · This month’s home curator', 'vpg-v2' ); ?></h2>
        <input type="text" name="curator" value="<?php echo esc_attr( get_option( 'vpg_home_curator', '' ) ); ?>" style="width:320px">
        <h2 style="margin-top:12px"><?php esc_html_e( '0431 · Guest programme (one per line)', 'vpg-v2' ); ?></h2>
        <textarea name="guests" rows="3" style="width:100%;max-width:760px"><?php echo esc_textarea( get_option( 'vpg_guest_programme', '' ) ); ?></textarea>
        <h2 style="margin-top:12px"><?php esc_html_e( '0417/0434 · New vote / survey', 'vpg-v2' ); ?></h2>
        <input type="text" name="poll_q" placeholder="<?php esc_attr_e( 'Question', 'vpg-v2' ); ?>" style="width:100%;max-width:760px">
        <textarea name="poll_opts" rows="3" placeholder="<?php esc_attr_e( 'One option per line', 'vpg-v2' ); ?>" style="width:100%;max-width:760px"></textarea>
        <p><button name="vpg_cd" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>
    </div>
    <?php
}

/* the community fields for the dashboard (status, availability, welcomer, skills) */
function vpg_community_dashboard_form() {
    if ( ! is_user_logged_in() ) return;
    $uid = get_current_user_id();
    ?>
    <section class="g-section g-section--tight"><div class="g-wrap" style="max-width:720px">
      <div class="g-head"><div><span class="g-kicker"><?php esc_html_e( 'Community', 'vpg-v2' ); ?></span><h2 class="g-head__t"><?php esc_html_e( 'Your <em>place</em> in it', 'vpg-v2' ); ?></h2></div></div>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:12px">
        <?php wp_nonce_field( 'vpg_status' ); ?><input type="hidden" name="action" value="vpg_save_status">
        <label><?php esc_html_e( 'Status', 'vpg-v2' ); ?><br>
          <select name="status" style="padding:8px;border:1px solid var(--g-line)">
            <?php $st = get_user_meta( $uid, '_vpg_status', true ); foreach ( [ '' => __( 'Active', 'vpg-v2' ), 'pause' => __( 'On a break', 'vpg-v2' ), 'alumni' => __( 'Alumnus', 'vpg-v2' ) ] as $sv => $sl ) echo '<option value="' . esc_attr( $sv ) . '"' . selected( $st, $sv, false ) . '>' . esc_html( $sl ) . '</option>'; ?>
          </select></label>
        <label><?php esc_html_e( 'When you’re usually around', 'vpg-v2' ); ?><br><input type="text" name="availability" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_availability', true ) ); ?>" placeholder="<?php esc_attr_e( 'Weekends, evenings…', 'vpg-v2' ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        <label><?php esc_html_e( 'Your home district', 'vpg-v2' ); ?><br><input type="text" name="home_district" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_home_district', true ) ); ?>" placeholder="1070" style="width:120px;padding:8px;border:1px solid var(--g-line)"></label>
        <label style="font-size:13px"><input type="checkbox" name="welcomer" value="1"<?php checked( get_user_meta( $uid, '_vpg_welcomer', true ), '1' ); ?>> <?php esc_html_e( '0416 · I’ll help welcome newcomers', 'vpg-v2' ); ?></label>
        <p><button class="g-btn g-btn--red"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>
    </div></section>
    <?php
}
