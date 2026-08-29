<?php
/**
 * VPG v3 — Cluster 14 · Onboarding & Wachstum.
 *
 * Helping people arrive and stay: a first-week mail series, reactivation and
 * abandon-rescue nudges, referral links that thank both sides, a test-submit
 * mode, a read-only preview, a social-proof bar, an early language switch, a
 * GIF welcome tour, a welcome gift, printable name badges and signup cards,
 * an exit interview, family memberships, editor-curated press kit / founder
 * story / positioning / numbers pages, a partner directory, and an internal
 * growth dashboard (joins, activation, retention, cohorts, first-pin quote,
 * time-to-aha, A/B notes).
 *
 *   0522 first week · 0523 welcome walk · 0524 first-submit help · 0525 test
 *   0526 referral · 0527 landing · 0528 uni · 0529 VHS · 0530 flyer · 0531 shop
 *   0532 IG exit · 0533 tourism · 0534 press kit · 0535 founder · 0536 numbers
 *   0537 referral thanks · 0538 preview · 0539 join polish · 0540 social proof
 *   0541 abandon rescue · 0542 first-login tour · 0543 language · 0544 students
 *   0545 walk funnel · 0546 guest funnel · 0547 waitlist · 0548 reactivation
 *   0549 exit interview · 0550 growth dash · 0551 cohorts · 0552 A/B · 0553 pin quote
 *   0554 time-to-aha · 0555 GIF tour · 0556 family · 0557 a11y · 0558 offline card
 *   0559 name badge · 0560 welcome gift
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Referral (0526 / 0537) — capture ?ref, thank both sides           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( isset( $_GET['ref'] ) && ! is_user_logged_in() && ! headers_sent() ) {
        setcookie( 'vpg_ref', sanitize_user( wp_unslash( $_GET['ref'] ) ), time() + 30 * DAY_IN_SECONDS, COOKIEPATH ?: '/' );
    }
} );
add_action( 'user_register', function ( $uid ) {
    $ref = isset( $_COOKIE['vpg_ref'] ) ? sanitize_user( wp_unslash( $_COOKIE['vpg_ref'] ) ) : '';
    $referrer = $ref ? ( get_user_by( 'login', $ref ) ?: get_user_by( 'slug', $ref ) ) : null;
    if ( $referrer && $referrer->ID !== $uid ) {
        update_user_meta( $uid, '_vpg_referrer', $referrer->ID );
        if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $referrer->ID, __( 'Someone you invited just joined — thank you for growing the collective.', 'vpg-v2' ), home_url( '/members/' ), 'growth' );
    }
    // 0560 welcome gift + 0522 first-week series + 0541 abandon rescue
    update_user_meta( $uid, '_vpg_welcome_gift', 1 );
    for ( $day = 1; $day <= 5; $day++ ) wp_schedule_single_event( time() + $day * DAY_IN_SECONDS, 'vpg_firstweek_mail', [ $uid, $day ] );
    wp_schedule_single_event( time() + 3 * DAY_IN_SECONDS, 'vpg_abandon_check', [ $uid ] );
} );

add_action( 'vpg_firstweek_mail', function ( $uid, $day ) {
    $u = get_userdata( $uid ); if ( ! $u || ! $u->user_email ) return;
    $lines = [
        1 => __( 'Welcome. Start with one pin — a place in Wien you love to photograph.', 'vpg-v2' ),
        2 => __( 'Your archive is yours here. Add a few frames to your portfolio wall.', 'vpg-v2' ),
        3 => __( 'Come to a photowalk — the easiest way to meet the others.', 'vpg-v2' ),
        4 => __( 'Read a tutorial, then go and shoot the exercise. No exams, ever.', 'vpg-v2' ),
        5 => __( 'Say hello in the community. This is a workshop, not a feed.', 'vpg-v2' ),
    ];
    wp_mail( $u->user_email, sprintf( __( 'Your first week · day %d', 'vpg-v2' ), $day ), ( $lines[ $day ] ?? '' ) . "\n\n" . home_url( '/dashboard/' ) );
}, 10, 2 );

add_action( 'vpg_abandon_check', function ( $uid ) {
    $u = get_userdata( $uid ); if ( ! $u ) return;
    $has = get_posts( [ 'author' => $uid, 'post_type' => [ 'vpg_location', 'post' ], 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ] );
    if ( ! $has && $u->user_email ) wp_mail( $u->user_email, __( 'Still there? Your first pin is waiting', 'vpg-v2' ), __( 'You joined but haven’t added anything yet — no rush, but if you got stuck, just reply. One pin is a lovely start.', 'vpg-v2' ) . "\n\n" . home_url( '/submit/' ) );
} );

/* 0548 · reactivation — weekly sweep of silent accounts (3 mails, then quiet) */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_reactivation' ) ) wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', 'vpg_reactivation' );
} );
add_action( 'vpg_reactivation', function () {
    foreach ( get_users( [ 'number' => 80, 'meta_key' => '_vpg_last_seen' ] ) as $u ) {
        $last = (int) get_user_meta( $u->ID, '_vpg_last_seen', true );
        $sent = (int) get_user_meta( $u->ID, '_vpg_react_sent', true );
        if ( $last && $last < time() - 120 * DAY_IN_SECONDS && $sent < 3 ) {
            update_user_meta( $u->ID, '_vpg_react_sent', $sent + 1 );
            if ( $u->user_email ) wp_mail( $u->user_email, __( 'Wien is still waiting for your eye', 'vpg-v2' ), __( 'It’s been a while. No pressure — but a new season is good light. Here’s what’s new:', 'vpg-v2' ) . "\n\n" . home_url( '/' ) );
        }
    }
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_reactivation' ); } );
/* reset the reactivation counter when a member returns */
add_action( 'wp_login', function ( $l, $u ) { if ( $u instanceof WP_User ) delete_user_meta( $u->ID, '_vpg_react_sent' ); }, 10, 2 );

/* ════════════════════════════════════════════════════════════════ */
/*  Shortcodes · referral link, social proof, badge/gift on dashboard */
/* ════════════════════════════════════════════════════════════════ */
add_shortcode( 'vpg_referral_link', function () {
    if ( ! is_user_logged_in() ) return '';
    $url = add_query_arg( 'ref', wp_get_current_user()->user_login, home_url( '/join/' ) );
    $count = count( get_users( [ 'meta_key' => '_vpg_referrer', 'meta_value' => get_current_user_id() ] ) );
    return '<p style="font-size:13px">' . esc_html__( 'Your invitation link:', 'vpg-v2' ) . '<br><input readonly value="' . esc_attr( $url ) . '" onclick="this.select()" style="width:100%;max-width:420px;padding:8px;border:1px solid var(--g-line)"><br><span style="color:var(--g-mid,#6A6A6A)">' . esc_html( sprintf( _n( '%d member joined through you.', '%d members joined through you.', $count, 'vpg-v2' ), $count ) ) . '</span></p>';
} );

add_shortcode( 'vpg_social_proof', function () {
    $recent = get_users( [ 'number' => 5, 'orderby' => 'registered', 'order' => 'DESC', 'fields' => [ 'user_registered' ] ] );
    if ( ! $recent ) return '';
    $names = array_map( fn( $u ) => human_time_diff( strtotime( $u->user_registered ) ), $recent );
    return '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">● ' . esc_html( sprintf( __( 'Someone joined %s ago — and %d others this month.', 'vpg-v2' ), $names[0], count( $recent ) ) ) . '</p>';
} );

/* welcome gift + tour card on the dashboard */
function vpg_onboarding_dashboard() {
    if ( ! is_user_logged_in() ) return;
    $uid = get_current_user_id();
    if ( get_user_meta( $uid, '_vpg_welcome_gift', true ) ) {
        $latest = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => 1 ] );
        $pdf = $latest ? get_post_meta( $latest[0]->ID, '_vpg_pdf_url', true ) : '';
        echo '<section class="g-section g-section--tight"><div class="g-wrap"><div style="border:2px solid var(--g-red,#E5341F);padding:16px 20px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center"><span>🎁 <strong>' . esc_html__( 'Welcome gift', 'vpg-v2' ) . '</strong> — ' . esc_html__( 'the latest issue is yours to download.', 'vpg-v2' ) . '</span>';
        if ( $pdf ) echo '<a class="g-btn g-btn--red" style="font-size:12px" href="' . esc_url( $pdf ) . '" target="_blank">' . esc_html__( 'Download', 'vpg-v2' ) . '</a>';
        echo '<a href="' . esc_url( admin_url( 'admin-post.php?action=vpg_dismiss_gift&_wpnonce=' . wp_create_nonce( 'vpg_gift' ) ) ) . '" style="font-size:12px;color:var(--g-mid)">' . esc_html__( 'dismiss', 'vpg-v2' ) . '</a></div>';
        // 0524/0542 first-submission checklist for brand-new members
        echo '<div style="border:1px solid var(--g-line,#E6E5E1);padding:16px 20px;margin-top:10px"><p style="font-weight:700;margin:0 0 8px">' . esc_html__( 'Your first three steps', 'vpg-v2' ) . '</p><ol style="margin:0;padding-left:18px;font-size:14px"><li>' . esc_html__( 'Add one pin on the map.', 'vpg-v2' ) . '</li><li>' . esc_html__( 'Put three frames on your wall.', 'vpg-v2' ) . '</li><li>' . esc_html__( 'Come to a photowalk.', 'vpg-v2' ) . '</li></ol></div>';
        echo '</div></section>';
    }
}
add_action( 'admin_post_vpg_dismiss_gift', function () {
    if ( ! is_user_logged_in() ) wp_die( 'no', 403 );
    check_admin_referer( 'vpg_gift' );
    delete_user_meta( get_current_user_id(), '_vpg_welcome_gift' );
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) ); exit;
} );

/* 0557 · keyboard-friendly focus for onboarding forms */
add_action( 'wp_head', function () {
    echo '<style>.g-form :focus-visible,.g-input:focus-visible,button:focus-visible{outline:3px solid var(--g-red,#E5341F);outline-offset:2px}</style>';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0525 · test-submit mode                                           */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    if ( isset( $_GET['test'] ) && is_page_template( 'templates/page-submit.php' ) ) {
        add_action( 'wp_footer', function () {
            echo '<div style="position:fixed;bottom:0;left:0;right:0;background:#996800;color:#fff;text-align:center;padding:8px;font-weight:700;z-index:900">' . esc_html__( '⚠ Test mode — nothing you submit here is saved. Practise freely.', 'vpg-v2' ) . '</div>';
        } );
    }
} );
/* discard a test entry before it is stored */
add_action( 'admin_post_vpg_submit', function () {
    if ( isset( $_POST['vpg_test'] ) ) { wp_safe_redirect( add_query_arg( [ 'test' => 1, 'vpg_status' => 'test_ok' ], home_url( '/submit/' ) ) ); exit; }
}, 1 );

/* ════════════════════════════════════════════════════════════════ */
/*  Pages · welcome tour, preview, press kit, founder, numbers,       */
/*  partners, name badge, signup card, exit interview                 */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^willkommen/?$', 'index.php?vpg_welcome=1', 'top' );
    add_rewrite_rule( '^vorschau/?$', 'index.php?vpg_preview=1', 'top' );
    add_rewrite_rule( '^presse/?$', 'index.php?vpg_doc=press', 'top' );
    add_rewrite_rule( '^warum/?$', 'index.php?vpg_doc=founder', 'top' );
    add_rewrite_rule( '^zahlen/?$', 'index.php?vpg_doc=numbers', 'top' );
    add_rewrite_rule( '^partner/?$', 'index.php?vpg_doc=partners', 'top' );
    add_rewrite_rule( '^namensschild/([^/]+)/?$', 'index.php?vpg_badge=$matches[1]', 'top' );
    add_rewrite_rule( '^anmeldekarte/?$', 'index.php?vpg_signupcard=1', 'top' );
    add_rewrite_rule( '^abschied/?$', 'index.php?vpg_exit=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { array_push( $v, 'vpg_welcome', 'vpg_preview', 'vpg_doc', 'vpg_badge', 'vpg_signupcard', 'vpg_exit' ); return $v; } );

add_action( 'template_redirect', function () {
    // 0559 name badge
    if ( $slug = get_query_var( 'vpg_badge' ) ) {
        $u = get_user_by( 'slug', $slug ) ?: get_user_by( 'login', $slug );
        if ( ! $u ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
        $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $u->ID ) : [ 'label' => 'Member' ];
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        echo '<!doctype html><meta charset=utf8><title>Badge</title><style>@page{size:90mm 55mm;margin:0}*{margin:0;box-sizing:border-box}body{font-family:Arial;padding:20px}.b{width:90mm;height:55mm;border:2px solid #0B0B0B;padding:8mm;display:flex;flex-direction:column;justify-content:space-between}.k{font:700 9px/1 Arial;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}.n{font-size:26px;font-weight:900;text-transform:uppercase}.r{font-size:13px;color:#6A6A6A}@media print{.noprint{display:none}}</style>';
        echo '<div class="b"><p class="k">Vienna Photo Group · Photowalk</p><div><p class="n">' . esc_html( $u->display_name ) . '</p><p class="r">' . esc_html( $rank['label'] ?? 'Member' ) . '</p></div></div><p class="noprint" style="margin-top:14px"><button onclick="window.print()">Print</button></p>'; exit;
    }
    // 0558 offline signup card
    if ( get_query_var( 'vpg_signupcard' ) ) {
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        echo '<!doctype html><meta charset=utf8><title>Anmeldekarte</title><style>@page{size:A6}*{margin:0;box-sizing:border-box}body{font-family:Arial;padding:12mm;max-width:420px;margin:0 auto}.k{font:700 9px/1 Arial;letter-spacing:.2em;text-transform:uppercase;color:#E5341F}h1{font-size:20px;font-weight:900;text-transform:uppercase;margin:6px 0 14px}.f{border-bottom:1px solid #0B0B0B;margin:16px 0;height:22px}.l{font-size:10px;color:#6A6A6A}@media print{.noprint{display:none}}</style>';
        echo '<p class="k">Vienna Photo Group</p><h1>' . esc_html__( 'Join at the walk', 'vpg-v2' ) . '</h1>';
        foreach ( [ __( 'Name', 'vpg-v2' ), __( 'E-mail', 'vpg-v2' ), __( 'A place you love to shoot', 'vpg-v2' ) ] as $lbl ) echo '<p class="l">' . esc_html( $lbl ) . '</p><div class="f"></div>';
        echo '<p class="l" style="margin-top:12px">' . esc_html__( 'Finish online:', 'vpg-v2' ) . ' viennaphotogroup.com/join</p><p class="noprint"><button onclick="window.print()">Print</button></p>'; exit;
    }
    // 0555 welcome tour (GIF steps)
    if ( get_query_var( 'vpg_welcome' ) ) {
        $steps = array_filter( array_map( 'intval', explode( ',', (string) get_option( 'vpg_welcome_steps', '' ) ) ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Welcome', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'A quick <em>tour</em>.', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px">
          <?php if ( $steps && shortcode_exists( 'vpg_steps' ) ) : echo do_shortcode( '[vpg_steps ids="' . implode( ',', $steps ) . '"]' ); else : ?>
            <ol class="g-prose"><li><?php esc_html_e( 'Add a pin — tap the map, drop a place, describe the light.', 'vpg-v2' ); ?></li><li><?php esc_html_e( 'Curate your wall — pick your strongest frames.', 'vpg-v2' ); ?></li><li><?php esc_html_e( 'Join a walk — the fastest way to belong.', 'vpg-v2' ); ?></li></ol>
          <?php endif; ?>
          <p style="margin-top:16px"><a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Start', 'vpg-v2' ); ?> →</a></p>
        </div></section></main>
        <?php get_footer(); exit;
    }
    // 0538 read-only preview
    if ( get_query_var( 'vpg_preview' ) ) {
        $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => 3 ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'A look inside', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The members’ <em>side</em>.', 'vpg-v2' ) ); ?></h1><p class="g-lede g-phero__lede"><?php esc_html_e( 'A read-only peek — no account needed. When it feels right, join (free).', 'vpg-v2' ); ?></p><p><a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a></p></div></section>
        <section class="g-section"><div class="g-wrap"><div class="g-grid3"><?php foreach ( $issues as $iss ) { echo '<a class="g-card" href="' . esc_url( get_permalink( $iss ) ) . '">'; if ( has_post_thumbnail( $iss ) ) echo '<div class="g-fig g-fig--3x2">' . get_the_post_thumbnail( $iss, 'medium_large' ) . '</div>'; echo '<h3 class="g-card__title">' . esc_html( get_the_title( $iss ) ) . '</h3></a>'; } ?></div></div></section></main>
        <?php get_footer(); exit;
    }
    // 0534/0535/0536/0533/0532/0547 doc pages
    if ( $doc = get_query_var( 'vpg_doc' ) ) {
        $opt = [ 'press' => 'vpg_press_kit', 'founder' => 'vpg_founder_story', 'numbers' => 'vpg_numbers_note', 'partners' => 'vpg_partners' ][ $doc ] ?? '';
        $titles = [ 'press' => __( 'Press kit', 'vpg-v2' ), 'founder' => __( 'Why we exist', 'vpg-v2' ), 'numbers' => __( 'The numbers', 'vpg-v2' ), 'partners' => __( 'Partners & funnels', 'vpg-v2' ) ];
        $txt = (string) get_option( $opt, '' );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php echo esc_html( $titles[ $doc ] ?? '' ); ?></p><h1 class="g-display g-phero__title"><?php echo esc_html( $titles[ $doc ] ?? '' ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <?php if ( $doc === 'numbers' ) { echo '<p>' . esc_html( sprintf( __( '%1$d members · %2$d locations · %3$d issues · member-run, ad-free, since 2019.', 'vpg-v2' ), count_users()['total_users'] ?? 0, (int) ( wp_count_posts( 'vpg_location' )->publish ?? 0 ), (int) ( wp_count_posts( 'vpg_magazine' )->publish ?? 0 ) ) ) . '</p>'; }
          echo $txt ? wp_kses_post( wpautop( $txt ) ) : '<p>' . esc_html__( 'Coming soon.', 'vpg-v2' ) . '</p>'; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }
    // 0549 exit interview
    if ( get_query_var( 'vpg_exit' ) ) {
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Before you go', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'One last <em>word</em>?', 'vpg-v2' ) ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:560px">
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:10px"><?php wp_nonce_field( 'vpg_exit' ); ?><input type="hidden" name="action" value="vpg_exit_feedback">
            <p><?php esc_html_e( 'Leaving is fine — your work stays online in dignity. If you’d like, tell us why (optional, anonymous).', 'vpg-v2' ); ?></p>
            <textarea name="why" rows="3" style="padding:9px;border:1px solid var(--g-line)"></textarea>
            <button class="g-btn g-btn--ghost" style="font-size:12px;justify-self:start"><?php esc_html_e( 'Send & sign out', 'vpg-v2' ); ?></button>
          </form>
        </div></section></main>
        <?php get_footer(); exit;
    }
} );
add_action( 'admin_post_vpg_exit_feedback', function () {
    check_admin_referer( 'vpg_exit' );
    $f = (array) get_option( 'vpg_exit_feedback', [] );
    $f[] = [ 'why' => sanitize_textarea_field( wp_unslash( $_POST['why'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_exit_feedback', array_slice( $f, -200 ), false );
    wp_safe_redirect( home_url( '/' ) ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · growth desk (metrics, cohorts, A/B, content, partners)    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'users.php', __( 'Growth desk', 'vpg-v2' ), '📈 ' . __( 'Growth desk', 'vpg-v2' ), 'edit_others_posts', 'vpg-growth', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_gr'] ) && check_admin_referer( 'vpg_gr' ) ) {
            foreach ( [ 'vpg_press_kit' => 'press', 'vpg_founder_story' => 'founder', 'vpg_numbers_note' => 'numbers', 'vpg_partners' => 'partners', 'vpg_ab_notes' => 'ab' ] as $opt => $f ) update_option( $opt, sanitize_textarea_field( wp_unslash( $_POST[ $f ] ?? '' ) ), false );
            update_option( 'vpg_welcome_steps', preg_replace( '/[^0-9,]/', '', wp_unslash( $_POST['welcome_steps'] ?? '' ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        // metrics: joins + activation per month, first-pin quote, time-to-aha
        $months = [];
        $users = get_users( [ 'number' => 2000, 'fields' => [ 'ID', 'user_registered' ] ] );
        $activated = 0; $aha_days = []; $total_new = 0;
        foreach ( $users as $u ) {
            $m = gmdate( 'Y-m', strtotime( $u->user_registered ) );
            $months[ $m ]['join'] = ( $months[ $m ]['join'] ?? 0 ) + 1;
            $first = get_posts( [ 'author' => $u->ID, 'post_type' => [ 'vpg_location', 'post' ], 'posts_per_page' => 1, 'order' => 'ASC', 'orderby' => 'date', 'post_status' => 'any' ] );
            if ( $first ) { $months[ $m ]['act'] = ( $months[ $m ]['act'] ?? 0 ) + 1; $activated++; $aha_days[] = max( 0, (int) round( ( strtotime( $first[0]->post_date ) - strtotime( $u->user_registered ) ) / DAY_IN_SECONDS ) ); }
            if ( strtotime( $u->user_registered ) > strtotime( '-90 days' ) ) $total_new++;
        }
        krsort( $months );
        $quote = count( $users ) ? round( $activated / count( $users ) * 100 ) : 0;
        $aha = $aha_days ? round( array_sum( $aha_days ) / count( $aha_days ), 1 ) : 0;
        ?>
        <div class="wrap"><h1>📈 <?php esc_html_e( 'Growth desk', 'vpg-v2' ); ?></h1>
          <p><strong><?php echo (int) $quote; ?>%</strong> <?php esc_html_e( 'first-pin quote (0553)', 'vpg-v2' ); ?> · <strong><?php echo esc_html( $aha ); ?></strong> <?php esc_html_e( 'avg. days to first publish (0554)', 'vpg-v2' ); ?></p>
          <h2><?php esc_html_e( '0550/0551 · Joins & activation per month (cohorts)', 'vpg-v2' ); ?></h2>
          <table class="widefat" style="max-width:520px"><thead><tr><th><?php esc_html_e( 'Month', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Joins', 'vpg-v2' ); ?></th><th><?php esc_html_e( 'Activated', 'vpg-v2' ); ?></th></tr></thead><tbody>
            <?php $i = 0; foreach ( $months as $m => $d ) { if ( $i++ > 12 ) break; echo '<tr><td>' . esc_html( $m ) . '</td><td>' . (int) ( $d['join'] ?? 0 ) . '</td><td>' . (int) ( $d['act'] ?? 0 ) . '</td></tr>'; } ?>
          </tbody></table>
          <form method="post"><?php wp_nonce_field( 'vpg_gr' ); ?>
            <h2><?php esc_html_e( '0552 · Onboarding A/B notes', 'vpg-v2' ); ?></h2><textarea name="ab" rows="3" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_ab_notes', '' ) ); ?></textarea>
            <h2><?php esc_html_e( '0534 · Press kit', 'vpg-v2' ); ?></h2><textarea name="press" rows="4" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_press_kit', '' ) ); ?></textarea>
            <h2><?php esc_html_e( '0535 · Founder story', 'vpg-v2' ); ?></h2><textarea name="founder" rows="4" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_founder_story', '' ) ); ?></textarea>
            <h2><?php esc_html_e( '0532/0533/0536 · Positioning & numbers note', 'vpg-v2' ); ?></h2><textarea name="numbers" rows="3" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_numbers_note', '' ) ); ?></textarea>
            <h2><?php esc_html_e( '0528–0531/0544–0546 · Partners & funnels', 'vpg-v2' ); ?></h2><textarea name="partners" rows="4" style="width:100%;max-width:720px"><?php echo esc_textarea( get_option( 'vpg_partners', '' ) ); ?></textarea>
            <h2><?php esc_html_e( '0555 · Welcome tour step images (attachment IDs)', 'vpg-v2' ); ?></h2><input type="text" name="welcome_steps" value="<?php echo esc_attr( get_option( 'vpg_welcome_steps', '' ) ); ?>" style="width:100%;max-width:720px">
            <p><button name="vpg_gr" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
          </form>
          <h2><?php esc_html_e( '0549 · Exit feedback', 'vpg-v2' ); ?></h2>
          <?php $ex = array_reverse( (array) get_option( 'vpg_exit_feedback', [] ) ); if ( $ex ) { echo '<ul>'; foreach ( array_slice( $ex, 0, 30 ) as $e ) if ( ! empty( $e['why'] ) ) echo '<li>' . esc_html( $e['why'] ) . '</li>'; echo '</ul>'; } else echo '<p class="description">' . esc_html__( 'None yet.', 'vpg-v2' ) . '</p>'; ?>
        </div>
        <?php
    } );
} );
