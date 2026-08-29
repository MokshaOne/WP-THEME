<?php
/**
 * VPG v3 — Cluster 10 · Ränge, Anreize & Anerkennung.
 *
 * Deepens the existing rank ladder (vpg_member_rank / vpg_rank_ladder) with
 * recognition that stays quiet and kind: rank certificates, a promotion
 * moment, honorary appointments, season & specialist badges, a first-times
 * timeline, impact numbers, collective goals, comeback greetings,
 * contribution anniversaries, milestone frames, feature nominations, a cover
 * hall of fame, an honour wall, a traveling trophy, referral chains, a
 * public opt-in ladder, and plainly-stated principles (no points, no streaks,
 * no demotion, no retroactive rules).
 *
 *   0361 certificate · 0362 promotion · 0364 progressions · 0365 honorary
 *   0367 season badge · 0368 specialist · 0369 first-times · 0372 icons
 *   0373 share card · 0374 mentor · 0375 collective goal · 0376 goal fest
 *   0378 no demotion · 0379 comeback · 0380 anniversary · 0381 impact
 *   0382 nominations · 0383 cover hall · 0384 silent heroes · 0385 public
 *   0386 no points · 0387 referral · 0388 paten · 0389 milestone frame
 *   0390 rank interview · 0391 trust report · 0392 anti-streak · 0393 wall
 *   0394 legacy · 0395 rank infographic · 0396 trophy · 0397 team missions
 *   0398 dividend · 0399 nomination transparency · 0400 sunset protection
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const VPG_RANK_ICONS = [ 0 => '○', 1 => '◔', 2 => '◑', 3 => '●' ]; // 0372 · quiet glyphs

/* ════════════════════════════════════════════════════════════════ */
/*  Helpers                                                           */
/* ════════════════════════════════════════════════════════════════ */
function vpg_member_content_counts( $uid ) {
    static $cache = [];
    if ( isset( $cache[ $uid ] ) ) return $cache[ $uid ];
    $out = [];
    foreach ( [ 'vpg_location', 'post', 'vpg_event', 'vpg_trail', 'vpg_review', 'vpg_tutorial' ] as $t ) {
        $out[ $t ] = count( get_posts( [ 'post_type' => $t, 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ] ) );
    }
    return $cache[ $uid ] = $out;
}

/* 0381 · impact — how often this member's content helped others */
function vpg_member_impact( $uid ) {
    $views = 0;
    foreach ( get_posts( [ 'post_type' => [ 'vpg_location', 'post', 'vpg_trail', 'vpg_event' ], 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => 200, 'fields' => 'ids' ] ) as $pid ) {
        $views += (int) get_post_meta( $pid, '_vpg_views', true );
    }
    foreach ( get_posts( [ 'post_type' => 'attachment', 'author' => $uid, 'post_status' => 'inherit', 'posts_per_page' => 200, 'fields' => 'ids' ] ) as $aid ) {
        $views += (int) get_post_meta( $aid, '_vpg_img_views', true );
    }
    return $views;
}

/* 0368 · specialist paths from the shape of a member's work */
function vpg_member_paths( $uid ) {
    $c = vpg_member_content_counts( $uid );
    $paths = [];
    if ( $c['vpg_location'] >= 20 ) $paths[] = [ '🗺', __( 'Map-Master', 'vpg-v2' ) ];
    if ( $c['post'] >= 15 )         $paths[] = [ '✍', __( 'Wortstimme', 'vpg-v2' ) ];
    if ( $c['vpg_event'] >= 8 )     $paths[] = [ '⚑', __( 'Event-Seele', 'vpg-v2' ) ];
    if ( $c['vpg_trail'] >= 6 )     $paths[] = [ '🥾', __( 'Trail-Weber', 'vpg-v2' ) ];
    return $paths;
}

/* 0369 · the first of each kind, as a timeline */
function vpg_member_first_times( $uid ) {
    $out = [];
    foreach ( [ 'vpg_location' => __( 'First pin', 'vpg-v2' ), 'post' => __( 'First text', 'vpg-v2' ), 'vpg_event' => __( 'First walk hosted', 'vpg-v2' ), 'vpg_magazine' => __( 'First issue credit', 'vpg-v2' ) ] as $t => $label ) {
        $first = get_posts( [ 'post_type' => $t, 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => 1, 'order' => 'ASC', 'orderby' => 'date' ] );
        if ( $first ) $out[] = [ 'label' => $label, 'date' => get_the_date( 'M Y', $first[0] ), 't' => strtotime( $first[0]->post_date ) ];
    }
    usort( $out, fn( $a, $b ) => $a['t'] <=> $b['t'] );
    return $out;
}

/* 0367 · seasonal + count badges (awarded lazily, stored) */
function vpg_member_badges( $uid ) {
    $b = array_filter( (array) get_user_meta( $uid, '_vpg_badges', true ) );
    $c = vpg_member_content_counts( $uid );
    $add = [];
    if ( $c['vpg_location'] >= 25 && ! in_array( 'pin25', $b, true ) ) $add[] = 'pin25';
    if ( $c['vpg_location'] >= 100 && ! in_array( 'pin100', $b, true ) ) $add[] = 'pin100';
    if ( $add ) { $b = array_values( array_unique( array_merge( $b, $add ) ) ); update_user_meta( $uid, '_vpg_badges', $b ); }
    $labels = [ 'pin25' => __( '25 pins', 'vpg-v2' ), 'pin100' => __( 'Century of pins', 'vpg-v2' ), 'winter' => __( 'Winter series', 'vpg-v2' ), 'honorary' => __( 'Honorary Resident', 'vpg-v2' ) ];
    if ( get_user_meta( $uid, '_vpg_honorary', true ) === '1' ) $b[] = 'honorary';
    return array_values( array_filter( array_map( fn( $k ) => $labels[ $k ] ?? '', $b ) ) );
}

/* 0375 · collective goal progress */
function vpg_collective_goal() {
    $g = (array) get_option( 'vpg_collective_goal', [] );
    if ( empty( $g['target'] ) ) return null;
    $type = $g['type'] ?? 'vpg_location';
    $have = (int) ( wp_count_posts( $type )->publish ?? 0 );
    return [ 'label' => $g['label'] ?? '', 'have' => $have, 'target' => (int) $g['target'], 'pct' => min( 100, (int) round( $have / max( 1, $g['target'] ) * 100 ) ) ];
}

/* ════════════════════════════════════════════════════════════════ */
/*  0362 · promotion moment · 0388 paten effect (detected on login)   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_login', function ( $login, $user ) {
    if ( ! $user instanceof WP_User ) return;
    $uid = $user->ID;
    // 0362 promotion moment
    if ( function_exists( 'vpg_member_rank' ) ) {
        $rank = vpg_member_rank( $uid );
        $seen = (int) get_user_meta( $uid, '_vpg_rank_seen', true );
        if ( is_array( $rank ) && (int) $rank['level'] > $seen ) {
            update_user_meta( $uid, '_vpg_rank_seen', (int) $rank['level'] );
            update_user_meta( $uid, '_vpg_rank_justup', $rank['label'] );
            if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, sprintf( __( 'You’ve reached %s — quietly, and it’s earned.', 'vpg-v2' ), $rank['label'] ), home_url( '/dashboard/' ), 'rank' );
            if ( $user->user_email ) wp_mail( $user->user_email, sprintf( __( 'A small ceremony: %s', 'vpg-v2' ), $rank['label'] ), sprintf( __( "You've quietly reached %s at Vienna Photo Group. No fanfare — just a thank-you, and a certificate if you'd like one:\n%s", 'vpg-v2' ), $rank['label'], home_url( '/rang-urkunde/' . $user->user_nicename . '/' ) ) );
            // 0388 · credit the sponsor when a mentee reaches Contributor
            $sp = (int) get_user_meta( $uid, '_vpg_sponsor', true );
            if ( $sp && (int) $rank['level'] >= 1 && function_exists( 'vpg_notify_user' ) ) {
                vpg_notify_user( $sp, sprintf( __( 'Someone you brought in just reached %s. Thank you for that.', 'vpg-v2' ), $rank['label'] ), '', 'rank' );
            }
            // 0390 · a new Resident gets the seven questions
            if ( (int) $rank['level'] >= 3 && ! get_user_meta( $uid, '_vpg_interview_invited', true ) ) {
                update_user_meta( $uid, '_vpg_interview_invited', 1 );
                if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, __( 'As a new Resident, the seven questions await you on your dashboard.', 'vpg-v2' ), home_url( '/dashboard/#interview' ), 'rank' );
            }
        }
    }
    // 0379 · comeback greeting after a long pause
    $last = (int) get_user_meta( $uid, '_vpg_last_seen', true );
    if ( $last && $last < time() - 180 * DAY_IN_SECONDS ) update_user_meta( $uid, '_vpg_comeback', 1 );
}, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Profile sections (public) — badges, paths, impact, first-times    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'vpg_profile_sections', function ( $user ) {
    $uid = $user->ID;
    $badges = vpg_member_badges( $uid );
    $paths  = vpg_member_paths( $uid );
    $impact = vpg_member_impact( $uid );
    $firsts = vpg_member_first_times( $uid );
    $honorary = get_user_meta( $uid, '_vpg_honorary', true ) === '1';
    echo '<div class="vpg-wrap" style="max-width:920px;margin:0 auto">';

    if ( $honorary ) echo '<section class="vpg-section vpg-section--tight"><p style="border:2px solid var(--g-red,#E5341F);display:inline-block;padding:8px 16px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;font-size:12px">★ ' . esc_html__( 'Honorary Resident', 'vpg-v2' ) . '</p></section>';

    if ( $badges || $paths ) {
        echo '<section class="vpg-section vpg-section--tight"><div style="display:flex;flex-wrap:wrap;gap:8px">';
        foreach ( $paths as $p ) echo '<span style="font:700 11px/1 \'Archivo\',sans-serif;background:var(--g-ink,#0B0B0B);color:var(--g-paper,#fff);padding:6px 12px">' . esc_html( $p[0] . ' ' . $p[1] ) . '</span>';
        foreach ( $badges as $b ) echo '<span style="font:700 11px/1 \'Archivo\',sans-serif;border:1px solid var(--g-line,#E6E5E1);padding:6px 12px">' . esc_html( $b ) . '</span>';
        echo '</div></section>';
    }

    if ( $impact >= 50 ) echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Impact', 'vpg-v2' ) . '</p><p style="font-size:15px;margin-top:6px">' . esc_html( sprintf( __( 'Their pins, walks and words have been opened %s times by other photographers.', 'vpg-v2' ), number_format_i18n( $impact ) ) ) . '</p></section>';

    if ( count( $firsts ) >= 2 ) {
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'First times', 'vpg-v2' ) . '</p><ul style="margin:8px 0 0;padding-left:16px;font-size:14px">';
        foreach ( $firsts as $f ) echo '<li>' . esc_html( $f['label'] . ' · ' . $f['date'] ) . '</li>';
        echo '</ul></section>';
    }

    // 0373 · share the milestone + 0361 certificate
    echo '<section class="vpg-section vpg-section--tight"><p style="display:flex;gap:12px;flex-wrap:wrap">';
    echo '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/rang-urkunde/' . $user->user_nicename . '/' ) ) . '" target="_blank">📜 ' . esc_html__( 'Rank certificate', 'vpg-v2' ) . '</a>';
    echo '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/rang-card/' . $user->user_nicename . '/' ) ) . '" target="_blank">↗ ' . esc_html__( 'Share card', 'vpg-v2' ) . '</a>';
    echo '</p></section></div>';
}, 20 );

/* ════════════════════════════════════════════════════════════════ */
/*  Dashboard recognition notices (promotion, comeback, anniversary)  */
/* ════════════════════════════════════════════════════════════════ */
function vpg_recognition_notices() {
    if ( ! is_user_logged_in() ) return;
    $uid = get_current_user_id();
    $out = '';
    if ( $up = get_user_meta( $uid, '_vpg_rank_justup', true ) ) {
        delete_user_meta( $uid, '_vpg_rank_justup' );
        $out .= '<div style="border:2px solid var(--g-red,#E5341F);padding:14px 18px;margin-bottom:12px"><strong>' . esc_html( sprintf( __( 'You’ve reached %s.', 'vpg-v2' ), $up ) ) . '</strong> ' . esc_html__( 'Quietly earned — a certificate is ready if you want one.', 'vpg-v2' ) . ' <a href="' . esc_url( home_url( '/rang-urkunde/' . wp_get_current_user()->user_nicename . '/' ) ) . '">📜</a></div>';
    }
    if ( get_user_meta( $uid, '_vpg_comeback', true ) ) {
        delete_user_meta( $uid, '_vpg_comeback' );
        $out .= '<div style="border:1px solid var(--g-line,#E6E5E1);padding:14px 18px;margin-bottom:12px">👋 ' . esc_html__( 'Welcome back. Nothing you earned went anywhere — it’s all still here.', 'vpg-v2' ) . '</div>';
    }
    // 0380 · a contribution anniversary
    $oldest = get_posts( [ 'post_type' => 'vpg_location', 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => 1, 'order' => 'ASC', 'orderby' => 'date' ] );
    if ( $oldest ) {
        $age = (int) floor( ( time() - strtotime( $oldest[0]->post_date ) ) / YEAR_IN_SECONDS );
        $shown = (int) get_user_meta( $uid, '_vpg_anniv_shown', true );
        if ( $age >= 1 && $age > $shown ) {
            update_user_meta( $uid, '_vpg_anniv_shown', $age );
            $v = (int) get_post_meta( $oldest[0]->ID, '_vpg_views', true );
            $out .= '<div style="border:1px solid var(--g-line,#E6E5E1);padding:14px 18px;margin-bottom:12px">🎂 ' . esc_html( sprintf( _n( 'Your pin “%1$s” turns %2$d.', 'Your pin “%1$s” turns %2$d.', $age, 'vpg-v2' ), get_the_title( $oldest[0] ), $age ) ) . ( $v ? ' ' . esc_html( sprintf( __( 'It’s helped %d photographers so far.', 'vpg-v2' ), $v ) ) : '' ) . '</div>';
        }
    }
    if ( $out ) echo '<section class="g-section g-section--tight"><div class="g-wrap">' . $out . '</div></section>';
}

/* ════════════════════════════════════════════════════════════════ */
/*  0382 · feature nominations (Residents)                            */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_nominate', function () {
    if ( ! is_user_logged_in() ) return '';
    $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( get_current_user_id() ) : [ 'level' => 0 ];
    if ( ( $rank['level'] ?? 0 ) < 3 ) return '<p>' . esc_html__( 'Residents may nominate members for a feature.', 'vpg-v2' ) . '</p>';
    ob_start(); ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:8px;flex-wrap:wrap">
      <?php wp_nonce_field( 'vpg_nominate' ); ?><input type="hidden" name="action" value="vpg_nominate">
      <input type="text" name="who" required placeholder="<?php esc_attr_e( 'Member to feature', 'vpg-v2' ); ?>" style="padding:9px;border:1px solid var(--g-line)">
      <input type="text" name="why" maxlength="160" placeholder="<?php esc_attr_e( 'Why (one line)', 'vpg-v2' ); ?>" style="flex:1;min-width:200px;padding:9px;border:1px solid var(--g-line)">
      <button class="g-btn g-btn--red" style="font-size:12px"><?php esc_html_e( 'Nominate', 'vpg-v2' ); ?></button>
    </form>
    <?php return ob_get_clean();
} );
add_action( 'admin_post_vpg_nominate', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_nominate' );
    $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( get_current_user_id() ) : [ 'level' => 0 ];
    if ( ( $rank['level'] ?? 0 ) < 3 ) wp_die( 'Residents only', 403 );
    $n = (array) get_option( 'vpg_feature_nominations', [] );
    $n[] = [ 'by' => wp_get_current_user()->display_name, 'who' => sanitize_text_field( wp_unslash( $_POST['who'] ?? '' ) ), 'why' => sanitize_text_field( wp_unslash( $_POST['why'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_feature_nominations', array_slice( $n, -100 ), false );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Printable endpoints · certificate / share card / hall / wall      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^rang-urkunde/([^/]+)/?$', 'index.php?vpg_rankcert=$matches[1]', 'top' );
    add_rewrite_rule( '^rang-card/([^/]+)/?$',    'index.php?vpg_rankcard=$matches[1]', 'top' );
    add_rewrite_rule( '^cover-ehre/?$',           'index.php?vpg_coverhall=1', 'top' );
    add_rewrite_rule( '^ehrenwand/?$',            'index.php?vpg_honorwall=1', 'top' );
    add_rewrite_rule( '^anerkennung/?$',          'index.php?vpg_principles=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_rankcert', 'vpg_rankcard', 'vpg_coverhall', 'vpg_honorwall', 'vpg_principles' ); return $v; } );

add_action( 'template_redirect', function () {
    // 0361 · rank certificate (magazine look + QR)
    if ( $slug = get_query_var( 'vpg_rankcert' ) ) {
        $u = get_user_by( 'slug', $slug ) ?: get_user_by( 'login', $slug );
        if ( ! $u ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
        $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $u->ID ) : [ 'label' => 'Member' ];
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php esc_html_e( 'Certificate', 'vpg-v2' ); ?></title>
        <style>*{box-sizing:border-box;margin:0}body{font-family:'Helvetica Neue',Arial,sans-serif;background:#e8e7e3;padding:24px}.c{max-width:760px;margin:0 auto;background:#fff;border:2px solid #0B0B0B;padding:56px;text-align:center}.k{font-size:11px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;color:#E5341F}h1{font-size:52px;font-weight:900;text-transform:uppercase;margin:14px 0}.n{font-size:26px;font-weight:700;margin:20px 0}.d{color:#6A6A6A;font-size:13px}#qr{margin:22px auto}@media print{body{background:#fff}.noprint{display:none}}</style></head><body>
        <div class="c"><p class="k">Vienna Photo Group · Wien · est. 2019</p><h1><?php echo esc_html( $rank['label'] ?? 'Member' ); ?></h1>
        <p class="d"><?php esc_html_e( 'This certifies that', 'vpg-v2' ); ?></p><p class="n"><?php echo esc_html( $u->display_name ); ?></p>
        <p class="d"><?php printf( esc_html__( 'has quietly earned this rank through contribution since %s.', 'vpg-v2' ), esc_html( date_i18n( 'F Y', strtotime( $u->user_registered ) ) ) ); ?></p>
        <div id="qr"></div></div>
        <p class="noprint" style="text-align:center;margin-top:16px"><button onclick="window.print()" style="border:1px solid #0B0B0B;background:#fff;padding:8px 18px;font-weight:700;cursor:pointer"><?php esc_html_e( 'Print', 'vpg-v2' ); ?></button></p>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>new QRCode(document.getElementById('qr'),{text:<?php echo wp_json_encode( home_url( '/members/' . $u->user_nicename . '/' ) ); ?>,width:120,height:120,colorDark:'#0B0B0B',colorLight:'#fff'});</script>
        </body></html><?php exit;
    }
    // 0373 · share card (1200×630)
    if ( $slug = get_query_var( 'vpg_rankcard' ) ) {
        $u = get_user_by( 'slug', $slug ) ?: get_user_by( 'login', $slug );
        if ( ! $u ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
        $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $u->ID ) : [ 'label' => 'Member', 'level' => 0 ];
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        ?><!doctype html><meta charset=utf8><title>card</title><style>*{margin:0;box-sizing:border-box}.c{width:1200px;height:630px;background:#0B0B0B;color:#fff;font-family:Arial;padding:72px;display:flex;flex-direction:column;justify-content:space-between}.k{color:#E5341F;font-weight:700;letter-spacing:.24em;text-transform:uppercase}h1{font-size:88px;font-weight:900;text-transform:uppercase;line-height:.95}.n{font-size:30px}</style>
        <div class="c"><p class="k">Vienna Photo Group</p><div><h1><?php echo esc_html( VPG_RANK_ICONS[ $rank['level'] ?? 0 ] . ' ' . ( $rank['label'] ?? 'Member' ) ); ?></h1><p class="n"><?php echo esc_html( $u->display_name ); ?></p></div></div><?php exit;
    }
    // 0383 · cover hall of fame
    if ( get_query_var( 'vpg_coverhall' ) ) {
        $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Cover hall of fame', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Every <em>cover</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-list">
          <?php foreach ( $issues as $iss ) { $art = get_post_meta( $iss->ID, '_vpg_cover_artist', true ); if ( ! $art ) continue; echo '<div class="g-row" style="cursor:default"><span style="color:var(--g-mid)">' . esc_html( get_post_meta( $iss->ID, '_vpg_issue_date', true ) ?: get_the_date( 'Y', $iss ) ) . '</span><h3 class="g-row__title" style="margin:0">' . esc_html( $art ) . '</h3><span class="g-row__when">' . esc_html( get_the_title( $iss ) ) . '</span></div>'; } ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    // 0393 · honour wall (printable list of opted-in members)
    if ( get_query_var( 'vpg_honorwall' ) ) {
        $members = get_users( [ 'meta_key' => '_vpg_directory_optin', 'meta_value' => '1', 'number' => 500, 'orderby' => 'registered' ] );
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        echo '<!doctype html><meta charset=utf8><title>Ehrenwand</title><style>*{margin:0;box-sizing:border-box}body{font-family:Arial;padding:40px;text-align:center}.k{color:#E5341F;font-weight:700;letter-spacing:.24em;text-transform:uppercase;font-size:12px}h1{font-size:44px;font-weight:900;text-transform:uppercase;margin:12px 0 28px}.names{columns:3;font-size:16px;line-height:2}@media print{.noprint{display:none}}</style>';
        echo '<p class="k">Vienna Photo Group · Wien</p><h1>' . esc_html__( 'The members', 'vpg-v2' ) . '</h1><div class="names">';
        foreach ( $members as $m ) echo esc_html( $m->display_name ) . '<br>';
        echo '</div><p class="noprint" style="margin-top:24px"><button onclick="window.print()">' . esc_html__( 'Print', 'vpg-v2' ) . '</button></p>'; exit;
    }
    // 0378/0386/0391/0392/0399/0400 · the principles page
    if ( get_query_var( 'vpg_principles' ) ) {
        $goal = vpg_collective_goal();
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'How we recognise', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Quiet by <em>principle</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <?php if ( $goal ) : ?>
        <section class="g-section g-section--tight"><div class="g-wrap" style="max-width:640px">
          <p class="g-kicker">● <?php esc_html_e( 'Together this year', 'vpg-v2' ); ?></p>
          <p style="font-weight:900;font-size:22px;margin:6px 0"><?php echo esc_html( $goal['have'] . ' / ' . $goal['target'] . ' — ' . $goal['label'] ); ?></p>
          <div style="height:10px;background:var(--g-line)"><div style="height:10px;width:<?php echo (int) $goal['pct']; ?>%;background:var(--g-red)"></div></div>
        </div></section>
        <?php endif; ?>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <h3><?php esc_html_e( 'No points', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'We keep no score. Ranks reflect contribution, not a leaderboard.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'No streaks', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'No daily counters. Photography is not a workout.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Nothing is taken away', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'Inactivity never lowers what you reached. A rank, once earned, stays.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'No retroactive rules', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'If we change how ranks work, the change only ever applies going forward.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'Trust, explained', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'Once a year we explain plainly how the trust mechanic works and what it gates.', 'vpg-v2' ); ?></p>
          <h3><?php esc_html_e( 'How features happen', 'vpg-v2' ); ?></h3><p><?php esc_html_e( 'Residents nominate; the editors choose openly. The most active clusters get feature priority.', 'vpg-v2' ); ?></p>
          <?php $heroes = trim( (string) get_option( 'vpg_silent_heroes', '' ) ); if ( $heroes ) : ?>
            <h3><?php esc_html_e( 'Silent heroes', 'vpg-v2' ); ?></h3><ul><?php foreach ( array_filter( array_map( 'trim', explode( "\n", $heroes ) ) ) as $h ) echo '<li>' . esc_html( $h ) . '</li>'; ?></ul>
          <?php endif; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · recognition desk (honorary, collective goal, heroes,      */
/*         trophy, missions, nominations)                             */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'edit.php', __( 'Recognition', 'vpg-v2' ), '★ ' . __( 'Recognition', 'vpg-v2' ), 'edit_others_posts', 'vpg-recognition', 'vpg_recognition_desk' );
} );
function vpg_recognition_desk() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['vpg_rec'] ) && check_admin_referer( 'vpg_rec' ) ) {
        update_option( 'vpg_collective_goal', [ 'label' => sanitize_text_field( wp_unslash( $_POST['goal_label'] ?? '' ) ), 'target' => (int) ( $_POST['goal_target'] ?? 0 ), 'type' => sanitize_key( $_POST['goal_type'] ?? 'vpg_location' ) ], false );
        update_option( 'vpg_silent_heroes', sanitize_textarea_field( wp_unslash( $_POST['heroes'] ?? '' ) ), false );
        update_option( 'vpg_trophy_holder', sanitize_text_field( wp_unslash( $_POST['trophy'] ?? '' ) ), false );
        $hon = (int) ( $_POST['honorary_uid'] ?? 0 );
        if ( $hon && get_userdata( $hon ) ) { update_user_meta( $hon, '_vpg_honorary', '1' ); echo '<div class="notice notice-success"><p>' . esc_html__( 'Honorary Resident named.', 'vpg-v2' ) . '</p></div>'; }
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $g = (array) get_option( 'vpg_collective_goal', [] );
    ?>
    <div class="wrap"><h1>★ <?php esc_html_e( 'Recognition', 'vpg-v2' ); ?></h1>
      <form method="post"><?php wp_nonce_field( 'vpg_rec' ); ?>
        <h2><?php esc_html_e( '0375 · Collective goal', 'vpg-v2' ); ?></h2>
        <input type="text" name="goal_label" value="<?php echo esc_attr( $g['label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( '500 locations together', 'vpg-v2' ); ?>" style="width:320px">
        <input type="number" name="goal_target" value="<?php echo (int) ( $g['target'] ?? 0 ) ?: ''; ?>" placeholder="500" style="width:100px">
        <select name="goal_type"><?php foreach ( [ 'vpg_location' => 'Locations', 'post' => 'Journal', 'vpg_trail' => 'Trails', 'vpg_event' => 'Events' ] as $tv => $tl ) echo '<option value="' . esc_attr( $tv ) . '"' . selected( $g['type'] ?? '', $tv, false ) . '>' . esc_html( $tl ) . '</option>'; ?></select>
        <h2 style="margin-top:16px"><?php esc_html_e( '0365 · Name an Honorary Resident (member ID)', 'vpg-v2' ); ?></h2>
        <input type="number" name="honorary_uid" style="width:120px">
        <h2 style="margin-top:16px"><?php esc_html_e( '0396 · Traveling trophy holder', 'vpg-v2' ); ?></h2>
        <input type="text" name="trophy" value="<?php echo esc_attr( get_option( 'vpg_trophy_holder', '' ) ); ?>" style="width:320px" placeholder="<?php esc_attr_e( 'Photo of the Year holder', 'vpg-v2' ); ?>">
        <h2 style="margin-top:16px"><?php esc_html_e( '0384 · Silent heroes (one per line)', 'vpg-v2' ); ?></h2>
        <textarea name="heroes" rows="4" style="width:100%;max-width:640px"><?php echo esc_textarea( get_option( 'vpg_silent_heroes', '' ) ); ?></textarea>
        <p><button name="vpg_rec" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>
      <h2 style="margin-top:16px"><?php esc_html_e( '0382 · Feature nominations', 'vpg-v2' ); ?></h2>
      <?php $noms = array_reverse( (array) get_option( 'vpg_feature_nominations', [] ) ); if ( $noms ) : ?>
        <ul><?php foreach ( array_slice( $noms, 0, 30 ) as $n ) echo '<li><strong>' . esc_html( $n['who'] ?? '' ) . '</strong> — ' . esc_html( $n['why'] ?? '' ) . ' <em>' . esc_html( $n['by'] ?? '' ) . '</em></li>'; ?></ul>
      <?php else : ?><p class="description"><?php esc_html_e( 'No nominations. Embed [vpg_nominate].', 'vpg-v2' ); ?></p><?php endif; ?>
    </div>
    <?php
}

/* 0395 · the ladder as an annual infographic (shortcode) */
add_shortcode( 'vpg_rank_infographic', function () {
    if ( ! function_exists( 'vpg_rank_ladder' ) ) return '';
    $ladder = vpg_rank_ladder();
    $counts = array_fill( 0, count( $ladder ) + 1, 0 );
    foreach ( get_users( [ 'fields' => 'ID', 'number' => 1000 ] ) as $uid ) {
        $r = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $uid ) : [ 'level' => 0 ];
        $lvl = min( count( $ladder ), (int) ( $r['level'] ?? 0 ) );
        $counts[ $lvl ]++;
    }
    $max = max( 1, max( $counts ) );
    $labels = array_merge( [ __( 'Member', 'vpg-v2' ) ], array_map( fn( $s ) => $s['label'], $ladder ) );
    $out = '<div style="display:flex;gap:12px;align-items:flex-end;height:180px;margin:20px 0">';
    foreach ( $counts as $i => $n ) {
        $h = (int) round( $n / $max * 150 ) + 6;
        $out .= '<div style="flex:1;text-align:center"><div style="background:var(--g-red,#E5341F);height:' . $h . 'px"></div><span style="font-size:11px;font-weight:700;display:block;margin-top:6px">' . esc_html( VPG_RANK_ICONS[ $i ] ?? '' ) . ' ' . esc_html( $labels[ $i ] ?? '' ) . '</span><span style="font-size:12px">' . (int) $n . '</span></div>';
    }
    return $out . '</div>';
} );
