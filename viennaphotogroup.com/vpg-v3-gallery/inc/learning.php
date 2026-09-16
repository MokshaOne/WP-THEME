<?php
/**
 * VPG v3 — Cluster 13 · Lernen & Tutorials.
 *
 * Extends the vpg_tutorial CPT with a real learning layer: interactive tools
 * (histogram, exposure triangle, focal comparison), per-tutorial exercises
 * with a submission link, ungraded quizzes, printable A6 cheat sheets, step
 * sequences, a big-print mode, community proofreading, an actuality-review
 * date, mark-as-complete progress, learning paths, tutorial bounties, mentor
 * matching, a searchable Q&A knowledge base, camera profiles, an external
 * courses map, a library list, an error gallery and the exam-free principle.
 *
 *   0483 exercises · 0484 submission link · 0486 camera profiles
 *   0487 histogram · 0488 triangle · 0489 focal · 0490 dev recipes
 *   0491 analogue · 0492 scan · 0493 print · 0494 mentor · 0495 office hours
 *   0496 tandems · 0497 quiz · 0498 error gallery · 0499 master analysis
 *   0500 image law · 0501 archiving · 0502 colour · 0503 composition
 *   0504 reading light · 0505 steps · 0506 cheat sheet · 0507 progress
 *   0508 actuality · 0509 proofreading · 0510 kids · 0511 senior · 0512 bilingual
 *   0513 external courses · 0514 libraries · 0515 teach walks · 0516 exam-free
 *   0517 donation ritual · 0518 Q&A search · 0519 learn newsletter · 0520 bounties
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function () {
    if ( is_admin() ) return;
    if ( is_singular( 'vpg_tutorial' ) || is_page() || get_query_var( 'vpg_learn' ) ) {
        $v = file_exists( VPG_V2_DIR . '/assets/js/learn-tools.js' ) ? (string) filemtime( VPG_V2_DIR . '/assets/js/learn-tools.js' ) : VPG_V2_VERSION;
        wp_enqueue_script( 'vpg-learn', VPG_V2_URI . '/assets/js/learn-tools.js', [], $v, true );
    }
}, 20 );

/* ── Interactive tool shortcodes ─────────────────────────────────── */
add_shortcode( 'vpg_histogram', function ( $a ) {
    $src = ! empty( $a['id'] ) ? wp_get_attachment_image_url( (int) $a['id'], 'medium' ) : ( $a['src'] ?? '' );
    return $src ? '<div data-vpg-histogram="' . esc_url( $src ) . '" style="margin:16px 0"></div>' : '';
} );
add_shortcode( 'vpg_exposure_triangle', fn() => '<div data-vpg-triangle style="margin:16px 0"></div>' );
add_shortcode( 'vpg_focal_compare', function ( $a ) {
    $ids = array_filter( array_map( 'intval', explode( ',', (string) ( $a['ids'] ?? '' ) ) ) );
    $urls = array_map( fn( $id ) => wp_get_attachment_image_url( $id, 'large' ), $ids );
    if ( ! array_filter( $urls ) ) return '';
    return '<div data-vpg-focal="' . esc_attr( implode( '|', $urls ) ) . '" data-labels="' . esc_attr( $a['labels'] ?? '24,35,50,85,135' ) . '" style="margin:16px 0"></div>';
} );
add_shortcode( 'vpg_steps', function ( $a ) { // 0505 GIF/step sequence
    $ids = array_filter( array_map( 'intval', explode( ',', (string) ( $a['ids'] ?? '' ) ) ) );
    if ( ! $ids ) return '';
    $out = '<ol class="vpg-steps" style="list-style:none;counter-reset:s;padding:0;margin:16px 0">';
    foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'large' ); if ( ! $u ) continue; $out .= '<li style="counter-increment:s;position:relative;margin:0 0 14px"><span style="position:absolute;left:0;top:0;background:#0B0B0B;color:#fff;font:700 12px/1 sans-serif;padding:6px 9px;z-index:1">Step </span><img src="' . esc_url( $u ) . '" alt="" style="width:100%;display:block"></li>'; }
    return $out . '</ol>';
} );

/* ── Tutorial metabox ────────────────────────────────────────────── */
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'vpg-tut', '🎓 ' . __( 'Tutorial extras', 'vpg-v2' ), function ( $post ) {
        wp_nonce_field( 'vpg_tut', 'vpg_tut_nonce' );
        $ex = get_post_meta( $post->ID, '_vpg_tut_exercise', true );
        $quiz = get_post_meta( $post->ID, '_vpg_tut_quiz', true );
        $rev = get_post_meta( $post->ID, '_vpg_tut_reviewed', true );
        $tr  = (int) get_post_meta( $post->ID, '_vpg_translation', true );
        echo '<p><label style="font-weight:600">' . esc_html__( '0483 · Exercise (go out and do)', 'vpg-v2' ) . '</label><br><textarea name="tut_exercise" rows="2" style="width:100%">' . esc_textarea( $ex ) . '</textarea></p>';
        echo '<p><label style="font-weight:600">' . esc_html__( '0497 · Quiz — one “Question || Answer” per line', 'vpg-v2' ) . '</label><br><textarea name="tut_quiz" rows="4" style="width:100%">' . esc_textarea( $quiz ) . '</textarea></p>';
        echo '<p><label style="font-weight:600">' . esc_html__( '0508 · Last reviewed', 'vpg-v2' ) . '</label> <input type="date" name="tut_reviewed" value="' . esc_attr( $rev ) . '"> ';
        echo '<label style="font-weight:600;margin-left:12px">' . esc_html__( '0512 · Translation (post ID)', 'vpg-v2' ) . '</label> <input type="number" name="tut_translation" value="' . ( $tr ?: '' ) . '" style="width:90px"></p>';
    }, 'vpg_tutorial', 'normal', 'default' );
} );
add_action( 'save_post_vpg_tutorial', function ( $id ) {
    if ( ! isset( $_POST['vpg_tut_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_tut_nonce'], 'vpg_tut' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;
    update_post_meta( $id, '_vpg_tut_exercise', sanitize_textarea_field( wp_unslash( $_POST['tut_exercise'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_tut_quiz', sanitize_textarea_field( wp_unslash( $_POST['tut_quiz'] ?? '' ) ) );
    update_post_meta( $id, '_vpg_tut_reviewed', sanitize_text_field( wp_unslash( $_POST['tut_reviewed'] ?? '' ) ) );
    $tr = (int) ( $_POST['tut_translation'] ?? 0 );
    $tr ? update_post_meta( $id, '_vpg_translation', $tr ) : delete_post_meta( $id, '_vpg_translation' );
} );

/* ── Tutorial single extras (appended to content) ────────────────── */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'vpg_tutorial' ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $id = get_the_ID();
    $ex = trim( (string) get_post_meta( $id, '_vpg_tut_exercise', true ) );
    $quiz = trim( (string) get_post_meta( $id, '_vpg_tut_quiz', true ) );
    $rev = get_post_meta( $id, '_vpg_tut_reviewed', true );
    $tr  = (int) get_post_meta( $id, '_vpg_translation', true );
    $extra = '';

    if ( $tr && get_post_status( $tr ) === 'publish' ) $extra .= '<p style="margin:16px 0"><a href="' . esc_url( get_permalink( $tr ) ) . '" style="font-weight:700">🌐 ' . esc_html__( 'This tutorial in the other language', 'vpg-v2' ) . '</a></p>';

    if ( $ex ) {
        $extra .= '<aside style="border:1px solid var(--g-line,#E6E5E1);padding:16px;margin:22px 0"><p style="font:700 11px/1 sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--g-red,#E5341F);margin:0 0 8px">● ' . esc_html__( '0483 · Exercise', 'vpg-v2' ) . '</p><p style="margin:0 0 10px">' . esc_html( $ex ) . '</p><a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/submit/?tutorial=' . $id ) ) . '">' . esc_html__( 'Submit your result', 'vpg-v2' ) . '</a></aside>';
    }

    if ( $quiz ) {
        $extra .= '<div style="margin:22px 0"><p style="font:700 11px/1 sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--g-red,#E5341F)">● ' . esc_html__( 'Self-test (ungraded)', 'vpg-v2' ) . '</p>';
        foreach ( array_filter( array_map( 'trim', explode( "\n", $quiz ) ) ) as $line ) {
            $p = array_map( 'trim', explode( '||', $line, 2 ) );
            $extra .= '<details style="border-top:1px solid var(--g-line,#E6E5E1);padding:8px 0"><summary style="cursor:pointer;font-weight:600">' . esc_html( $p[0] ) . '</summary><p style="margin:6px 0 0;color:var(--g-mid,#6A6A6A)">' . esc_html( $p[1] ?? '' ) . '</p></details>';
        }
        $extra .= '</div>';
    }

    // links: cheat sheet, big print, mark complete, proofread
    $done = in_array( $id, array_map( 'intval', (array) get_user_meta( get_current_user_id(), '_vpg_completed_tutorials', true ) ), true );
    $extra .= '<div style="display:flex;gap:12px;flex-wrap:wrap;margin:20px 0;align-items:center">';
    $extra .= '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/spick/' . $id . '/' ) ) . '" target="_blank">🖨 ' . esc_html__( 'A6 cheat sheet', 'vpg-v2' ) . '</a>';
    $extra .= '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( add_query_arg( 'big', 1 ) ) . '">🔠 ' . esc_html__( 'Large print', 'vpg-v2' ) . '</a>';
    if ( is_user_logged_in() ) {
        $extra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin:0">' . wp_nonce_field( 'vpg_tut_done', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="vpg_tut_done"><input type="hidden" name="id" value="' . $id . '"><button class="g-btn g-btn--ghost" style="font-size:12px">' . ( $done ? esc_html__( '✓ Completed', 'vpg-v2' ) : esc_html__( 'Mark as done', 'vpg-v2' ) ) . '</button></form>';
    }
    $extra .= '</div>';
    if ( $rev ) $extra .= '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html( sprintf( __( 'Last reviewed for accuracy: %s', 'vpg-v2' ), $rev ) ) . '</p>';

    // 0509 proofreading
    if ( is_user_logged_in() ) {
        $extra .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:flex;gap:8px;margin-top:12px">' . wp_nonce_field( 'vpg_tut_fix', '_wpnonce', true, false ) . '<input type="hidden" name="action" value="vpg_tut_fix"><input type="hidden" name="id" value="' . $id . '"><input type="text" name="note" maxlength="200" placeholder="' . esc_attr__( 'Spotted an error or improvement? Tell the editors…', 'vpg-v2' ) . '" style="flex:1;padding:8px;border:1px solid var(--g-line)"><button class="g-btn g-btn--ghost" style="font-size:12px">' . esc_html__( 'Suggest a fix', 'vpg-v2' ) . '</button></form>';
    }
    return $content . $extra;
}, 22 );

/* big-print mode (0511) */
add_action( 'wp_head', function () {
    if ( is_singular( 'vpg_tutorial' ) && isset( $_GET['big'] ) ) echo '<style>.g-prose{font-size:1.3rem;line-height:1.9}.g-prose h2,.g-prose h3{font-size:1.6em}</style>';
} );

/* handlers */
add_action( 'admin_post_vpg_tut_done', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_tut_done' );
    $id = (int) ( $_POST['id'] ?? 0 );
    $list = array_map( 'intval', (array) get_user_meta( get_current_user_id(), '_vpg_completed_tutorials', true ) );
    if ( in_array( $id, $list, true ) ) $list = array_values( array_diff( $list, [ $id ] ) ); else $list[] = $id;
    update_user_meta( get_current_user_id(), '_vpg_completed_tutorials', $list );
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );
add_action( 'admin_post_vpg_tut_fix', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_tut_fix' );
    $id = (int) ( $_POST['id'] ?? 0 );
    $fixes = (array) get_post_meta( $id, '_vpg_tut_fixes', true );
    $fixes[] = [ 'by' => wp_get_current_user()->display_name, 'note' => sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ), 't' => time() ];
    update_post_meta( $id, '_vpg_tut_fixes', array_slice( $fixes, -50 ) );
    wp_safe_redirect( get_permalink( $id ) ); exit;
} );

/* ── 0520 tutorial bounties ──────────────────────────────────────── */
add_shortcode( 'vpg_bounties', function () {
    $b = (array) get_option( 'vpg_tut_bounties', [] );
    uasort( $b, fn( $x, $y ) => count( (array) ( $y['votes'] ?? [] ) ) <=> count( (array) ( $x['votes'] ?? [] ) ) );
    ob_start(); ?>
    <div>
      <?php foreach ( array_slice( $b, 0, 20, true ) as $bid => $bt ) : $n = count( (array) ( $bt['votes'] ?? [] ) ); $voted = in_array( get_current_user_id(), (array) ( $bt['votes'] ?? [] ), true ); ?>
        <div style="display:flex;justify-content:space-between;gap:10px;border-top:1px solid var(--g-line,#E6E5E1);padding:8px 0">
          <span><?php echo esc_html( $bt['theme'] ?? '' ); ?></span>
          <?php if ( is_user_logged_in() ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0"><?php wp_nonce_field( 'vpg_bounty_vote' ); ?><input type="hidden" name="action" value="vpg_bounty_vote"><input type="hidden" name="bid" value="<?php echo esc_attr( $bid ); ?>"><button style="background:none;border:0;color:var(--g-red);cursor:pointer;font-weight:700"><?php echo $voted ? '✓' : '▲'; ?> <?php echo (int) $n; ?></button></form>
          <?php else : ?><span style="color:var(--g-mid,#6A6A6A)"><?php echo (int) $n; ?></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if ( is_user_logged_in() ) : ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;margin-top:12px"><?php wp_nonce_field( 'vpg_bounty_add' ); ?><input type="hidden" name="action" value="vpg_bounty_add"><input type="text" name="theme" maxlength="90" required placeholder="<?php esc_attr_e( 'A tutorial you wish existed…', 'vpg-v2' ); ?>" style="flex:1;padding:8px;border:1px solid var(--g-line)"><button class="g-btn g-btn--ghost" style="font-size:12px"><?php esc_html_e( 'Request it', 'vpg-v2' ); ?></button></form>
      <?php endif; ?>
    </div>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_bounty_add', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_bounty_add' );
    $b = (array) get_option( 'vpg_tut_bounties', [] );
    $b[ 'b' . time() ] = [ 'theme' => sanitize_text_field( wp_unslash( $_POST['theme'] ?? '' ) ), 'by' => get_current_user_id(), 'votes' => [ get_current_user_id() ] ];
    update_option( 'vpg_tut_bounties', array_slice( $b, -100, null, true ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/lernen/' ) ); exit;
} );
add_action( 'admin_post_vpg_bounty_vote', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_bounty_vote' );
    $b = (array) get_option( 'vpg_tut_bounties', [] );
    $bid = sanitize_text_field( $_POST['bid'] ?? '' );
    if ( isset( $b[ $bid ] ) ) {
        $v = (array) ( $b[ $bid ]['votes'] ?? [] );
        $v = in_array( get_current_user_id(), $v, true ) ? array_diff( $v, [ get_current_user_id() ] ) : array_merge( $v, [ get_current_user_id() ] );
        $b[ $bid ]['votes'] = array_values( $v );
        update_option( 'vpg_tut_bounties', $b, false );
    }
    wp_safe_redirect( wp_get_referer() ?: home_url( '/lernen/' ) ); exit;
} );

/* ── 0494 mentor request ─────────────────────────────────────────── */
add_shortcode( 'vpg_mentor_request', function () {
    if ( ! is_user_logged_in() ) return '';
    $mentors = get_users( [ 'meta_key' => '_vpg_buddy_role', 'meta_value' => 'mentor', 'number' => 50 ] );
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap">
      <?php wp_nonce_field( 'vpg_mentor_req' ); ?><input type="hidden" name="action" value="vpg_mentor_req">
      <input type="text" name="wish" maxlength="140" required placeholder="<?php esc_attr_e( 'What do you want to learn?', 'vpg-v2' ); ?>" style="flex:1;min-width:220px;padding:9px;border:1px solid var(--g-line)">
      <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Find a mentor', 'vpg-v2' ); ?></button>
    </form>
    <p style="font-size:12px;color:var(--g-mid,#6A6A6A);margin-top:6px"><?php printf( esc_html( _n( '%d member offers mentoring.', '%d members offer mentoring.', count( $mentors ), 'vpg-v2' ) ), count( $mentors ) ); ?></p>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_mentor_req', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_mentor_req' );
    $wish = sanitize_text_field( wp_unslash( $_POST['wish'] ?? '' ) );
    foreach ( get_users( [ 'meta_key' => '_vpg_buddy_role', 'meta_value' => 'mentor', 'number' => 30, 'fields' => 'ID' ] ) as $mid ) {
        if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( (int) $mid, sprintf( __( 'A member is looking to learn: “%s”', 'vpg-v2' ), $wish ), home_url( '/members/' ), 'learning' );
    }
    wp_safe_redirect( ( wp_get_referer() ?: home_url( '/lernen/' ) ) . '?vpg_status=mentor_sent' ); exit;
} );

/* ── /lernen/ hub · /spick/{id}/ cheat sheet · directories ───────── */
add_action( 'init', function () {
    add_rewrite_rule( '^lernen/?$', 'index.php?vpg_learn=1', 'top' );
    add_rewrite_rule( '^spick/(\d+)/?$', 'index.php?vpg_cheat=$matches[1]', 'top' );
    add_rewrite_rule( '^kameras/?$', 'index.php?vpg_cameras=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_learn', 'vpg_cheat', 'vpg_cameras' ); return $v; } );
add_action( 'template_redirect', function () {
    if ( $cid = (int) get_query_var( 'vpg_cheat' ) ) { // 0506
        if ( get_post_type( $cid ) !== 'vpg_tutorial' ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
        $ex = get_post_meta( $cid, '_vpg_tut_exercise', true );
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><meta charset=utf8><title><?php echo esc_html( get_the_title( $cid ) ); ?></title>
        <style>@page{size:A6;margin:8mm}*{box-sizing:border-box;margin:0}body{font-family:'Helvetica Neue',Arial,sans-serif;padding:12mm;max-width:420px;margin:0 auto}.k{font-size:9px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}h1{font-size:20px;font-weight:900;text-transform:uppercase;margin:6px 0 10px}.b{font-size:12px;line-height:1.5}@media print{.noprint{display:none}}</style>
        <p class="k">Vienna Photo Group · Cheat sheet</p><h1><?php echo esc_html( get_the_title( $cid ) ); ?></h1>
        <div class="b"><?php echo wp_kses_post( wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $cid ) ), 90 ) ); ?><?php if ( $ex ) echo '<p style="margin-top:8px"><strong>Übung:</strong> ' . esc_html( $ex ) . '</p>'; ?></div>
        <p class="noprint" style="margin-top:14px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:8px 16px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print A6', 'vpg-v2' ); ?></button></p>
        <?php exit;
    }
    if ( get_query_var( 'vpg_cameras' ) ) { // 0486
        $profiles = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_camera_profiles', '' ) ) ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Bodies & settings', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Camera <em>profiles</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <?php foreach ( $profiles as $line ) { $p = array_map( 'trim', explode( '|', $line, 2 ) ); echo '<h3>' . esc_html( $p[0] ) . '</h3><p>' . esc_html( $p[1] ?? '' ) . '</p>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    if ( get_query_var( 'vpg_learn' ) ) {
        $paths = get_terms( [ 'taxonomy' => 'vpg_series', 'hide_empty' => true ] );
        $courses = get_posts( [ 'post_type' => 'vpg_tutorial', 'post_status' => 'publish', 'posts_per_page' => 40 ] );
        $ext = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_external_courses', '' ) ) ) );
        $libs = array_filter( array_map( 'trim', explode( "\n", (string) get_option( 'vpg_libraries', '' ) ) ) );
        $errors = array_filter( array_map( 'intval', explode( ',', (string) get_option( 'vpg_error_gallery', '' ) ) ) );
        $doneN = is_user_logged_in() ? count( (array) get_user_meta( get_current_user_id(), '_vpg_completed_tutorials', true ) ) : 0;
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Learn', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The <em>school</em>.', 'vpg-v2' ) ); ?></h1><p class="g-lede g-phero__lede"><?php esc_html_e( 'No exams, no certificate theatre — just craft, shared. Read, then go and make.', 'vpg-v2' ); ?></p>
        <?php if ( $doneN ) : ?><p class="g-kicker" style="color:var(--g-red)">● <?php printf( esc_html( _n( '%d tutorial completed', '%d tutorials completed', $doneN, 'vpg-v2' ) ), $doneN ); ?></p><?php endif; ?>
        </div></section>
        <section class="g-section g-section--tight"><div class="g-wrap">
          <p class="g-kicker">● <?php esc_html_e( 'Tutorials', 'vpg-v2' ); ?></p>
          <div class="g-list"><?php foreach ( $courses as $c ) { $lvl = get_the_terms( $c->ID, 'tutorial_level' ); echo '<a class="g-row" href="' . esc_url( get_permalink( $c ) ) . '"><span style="min-width:120px;color:var(--g-mid)">' . esc_html( $lvl && ! is_wp_error( $lvl ) ? $lvl[0]->name : '' ) . '</span><h3 class="g-row__title" style="margin:0">' . esc_html( get_the_title( $c ) ) . '</h3><span></span></a>'; } ?></div>
        </div></section>
        <section class="g-section g-section--tight"><div class="g-wrap" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px">
          <div><p class="g-kicker">● <?php esc_html_e( 'Tools', 'vpg-v2' ); ?></p><ul style="font-size:14px;margin:8px 0 0;padding-left:16px"><li><?php esc_html_e( 'Interactive histogram', 'vpg-v2' ); ?></li><li><?php esc_html_e( 'Exposure triangle', 'vpg-v2' ); ?></li><li><?php esc_html_e( 'Focal comparison', 'vpg-v2' ); ?></li></ul><div data-vpg-triangle style="margin-top:10px"></div></div>
          <div><p class="g-kicker">● <?php esc_html_e( 'Mentor matching', 'vpg-v2' ); ?></p><?php echo do_shortcode( '[vpg_mentor_request]' ); ?></div>
          <div><p class="g-kicker">● <?php esc_html_e( 'Wished-for tutorials', 'vpg-v2' ); ?></p><?php echo do_shortcode( '[vpg_bounties]' ); ?></div>
        </div></section>
        <?php if ( $errors ) : ?><section class="g-section g-section--tight"><div class="g-wrap"><p class="g-kicker">● <?php esc_html_e( '0498 · Learn from mistakes', 'vpg-v2' ); ?></p><div data-vpg-gallery style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-top:10px"><?php foreach ( $errors as $eid ) { $u = wp_get_attachment_image_url( $eid, 'medium' ); $f = wp_get_attachment_image_url( $eid, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" data-cap="' . esc_attr( get_the_excerpt( $eid ) ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">'; } ?></div></div></section><?php endif; ?>
        <?php if ( $ext || $libs ) : ?><section class="g-section g-section--tight"><div class="g-wrap" style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
          <?php if ( $ext ) : ?><div><p class="g-kicker">● <?php esc_html_e( '0513 · External courses', 'vpg-v2' ); ?></p><ul style="font-size:14px"><?php foreach ( $ext as $l ) echo '<li>' . esc_html( $l ) . '</li>'; ?></ul></div><?php endif; ?>
          <?php if ( $libs ) : ?><div><p class="g-kicker">● <?php esc_html_e( '0514 · Photo libraries in Wien', 'vpg-v2' ); ?></p><ul style="font-size:14px"><?php foreach ( $libs as $l ) echo '<li>' . esc_html( $l ) . '</li>'; ?></ul></div><?php endif; ?>
        </div></section><?php endif; ?>
        <section class="g-section g-section--tight"><div class="g-wrap"><p style="font-size:13px;color:var(--g-mid,#6A6A6A);max-width:60ch">🎓 <?php esc_html_e( 'Every Documentarian writes one tutorial — knowledge is a donation, not a possession. And we learn without exams: the point is the picture, not a certificate.', 'vpg-v2' ); ?></p>
          <p style="font-size:13px;margin-top:8px"><a href="<?php echo esc_url( home_url( '/?post_type=vpg_qa&s=' ) ); ?>"><?php esc_html_e( 'Search the answered-questions knowledge base', 'vpg-v2' ); ?> →</a></p>
        </div></section>
        </main>
        <?php get_footer(); exit;
    }
} );

/* ── Admin · learning desk ───────────────────────────────────────── */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php?post_type=vpg_tutorial', __( 'Learning desk', 'vpg-v2' ), '🎓 ' . __( 'Learning desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-learning', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_ld'] ) && check_admin_referer( 'vpg_ld' ) ) {
            foreach ( [ 'vpg_camera_profiles' => 'cameras', 'vpg_external_courses' => 'ext', 'vpg_libraries' => 'libs', 'vpg_error_gallery' => 'errors' ] as $opt => $f ) update_option( $opt, sanitize_textarea_field( wp_unslash( $_POST[ $f ] ?? '' ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        // stale tutorials (0508)
        $stale = get_posts( [ 'post_type' => 'vpg_tutorial', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        echo '<div class="wrap"><h1>🎓 ' . esc_html__( 'Learning desk', 'vpg-v2' ) . '</h1>';
        echo '<p class="description"><a href="' . esc_url( home_url( '/lernen/' ) ) . '">/lernen/</a> · <a href="' . esc_url( home_url( '/kameras/' ) ) . '">/kameras/</a></p>';
        echo '<form method="post">'; wp_nonce_field( 'vpg_ld' );
        echo '<h2>0486 · ' . esc_html__( 'Camera profiles (one per line: “Model | community settings”)', 'vpg-v2' ) . '</h2><textarea name="cameras" rows="5" style="width:100%;max-width:720px">' . esc_textarea( get_option( 'vpg_camera_profiles', '' ) ) . '</textarea>';
        echo '<h2>0513 · ' . esc_html__( 'External courses (one per line)', 'vpg-v2' ) . '</h2><textarea name="ext" rows="4" style="width:100%;max-width:720px">' . esc_textarea( get_option( 'vpg_external_courses', '' ) ) . '</textarea>';
        echo '<h2>0514 · ' . esc_html__( 'Libraries (one per line)', 'vpg-v2' ) . '</h2><textarea name="libs" rows="3" style="width:100%;max-width:720px">' . esc_textarea( get_option( 'vpg_libraries', '' ) ) . '</textarea>';
        echo '<h2>0498 · ' . esc_html__( 'Error gallery (attachment IDs, comma-separated)', 'vpg-v2' ) . '</h2><input type="text" name="errors" value="' . esc_attr( get_option( 'vpg_error_gallery', '' ) ) . '" style="width:100%;max-width:720px">';
        echo '<p><button name="vpg_ld" class="button button-primary">' . esc_html__( 'Save', 'vpg-v2' ) . '</button></p></form>';
        echo '<h2>0508 · ' . esc_html__( 'Tutorials needing a review (>1 year)', 'vpg-v2' ) . '</h2><ul>';
        foreach ( $stale as $t ) { $rev = get_post_meta( $t->ID, '_vpg_tut_reviewed', true ); $ts = $rev ? strtotime( $rev ) : strtotime( $t->post_modified ); if ( $ts < strtotime( '-1 year' ) ) echo '<li><a href="' . esc_url( get_edit_post_link( $t->ID ) ) . '">' . esc_html( get_the_title( $t ) ) . '</a> — ' . esc_html( $rev ?: date_i18n( 'M Y', $ts ) ) . '</li>'; }
        echo '</ul>';
        // proofreading suggestions
        echo '<h2>0509 · ' . esc_html__( 'Proofreading suggestions', 'vpg-v2' ) . '</h2>';
        $any = false;
        foreach ( $stale as $t ) foreach ( (array) get_post_meta( $t->ID, '_vpg_tut_fixes', true ) as $fx ) { $any = true; echo '<p><strong>' . esc_html( get_the_title( $t ) ) . '</strong>: ' . esc_html( $fx['note'] ?? '' ) . ' <em>' . esc_html( $fx['by'] ?? '' ) . '</em></p>'; }
        if ( ! $any ) echo '<p class="description">' . esc_html__( 'None pending.', 'vpg-v2' ) . '</p>';
        echo '</div>';
    } );
}, 21 );
