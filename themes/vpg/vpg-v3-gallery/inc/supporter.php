<?php
/**
 * VPG v3 — Cluster 25 · Supporter & Nachhaltigkeit  (the final cluster).
 *
 * Later, voluntary, never a paywall. Reuses the tier meta (_vpg_tier),
 * the poll system and the no-paywall promise — adds the honest supporter
 * surface (external payment links, no in-theme checkout), a thank-you wall,
 * cancellation & pause dignity, a supporter survey, an annual report, a
 * sealed ten-year letter, and a sustainability & governance desk.
 *
 *   0961/0964/0965 support links · 0963 cost transparency · 0966 thanks wall
 *   0967/0995 free & ad-free promise · 0997/0998 cancel & pause dignity
 *   0996 survey · 0982 annual report · 1000 ten-year letter · 0983–0994 desk
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
 * public pages under path-matched routes (no rewrite, no flush)
 * ================================================================ */
add_action( 'template_redirect', function () {
    $path = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ?: '', '/' );
    switch ( $path ) {
        case 'unterstuetzen': case 'supporter': vpg_supporter_page(); exit;
        case 'jahresbericht': vpg_annual_report_page(); exit;
        case 'zehn-jahre': vpg_tenyear_page(); exit;
        case 'danke-wand': case 'thanks-wall': vpg_thanks_wall(); exit;
    }
}, 9 );

function vpg_sup_head( $title ) { status_header( 200 ); get_header(); echo '<main id="vpg-main" class="g-wrap" style="max-width:760px;margin:40px auto;padding:0 20px"><h1>' . esc_html( $title ) . '</h1>'; }
function vpg_sup_foot() { echo '</main>'; get_footer(); }

/* ---- 0961/0963/0964/0965/0967/0995 · the supporter page ---- */
function vpg_supporter_page() {
    $once  = get_option( 'vpg_donate_once', '' );
    $month = get_option( 'vpg_donate_monthly', '' );
    $bank  = get_option( 'vpg_donate_bank', '' );
    $costs = (array) get_option( 'vpg_costs', [] );
    vpg_sup_head( __( 'Support Vienna Photo Group', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'Free to use, run by members, kept alive by the people who love it. Support is later, voluntary, and never a paywall.', 'vpg-v2' ) . '</p>';

    // 0967 / 0995 · the promise, up front
    echo '<div style="border:2px solid var(--g-ink,#0B0B0B);padding:14px 18px;margin:20px 0">'
       . '<strong>' . esc_html__( 'Our promise', 'vpg-v2' ) . '</strong><br>'
       . esc_html__( 'Every feature that is free today stays free — forever. No ads, no tracking, no investors. Supporter perks only ever add; they never take anything away.', 'vpg-v2' )
       . '</div>';

    // 0964 / 0965 / 0961 · how to give (external, pay-what-you-want)
    echo '<h2>' . esc_html__( 'Give what feels right', 'vpg-v2' ) . '</h2>';
    if ( $once || $month || $bank ) {
        echo '<p style="display:flex;flex-wrap:wrap;gap:10px">';
        if ( $once )  echo '<a class="g-btn" href="' . esc_url( $once ) . '" rel="nofollow noopener">' . esc_html__( 'Give once', 'vpg-v2' ) . '</a>';
        if ( $month ) echo '<a class="g-btn" href="' . esc_url( $month ) . '" rel="nofollow noopener">' . esc_html__( 'Give monthly', 'vpg-v2' ) . '</a>';
        echo '</p>';
        if ( $bank ) echo '<p style="font-size:13px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Prefer a bank transfer?', 'vpg-v2' ) . ' ' . esc_html( $bank ) . '</p>';
        echo '<p style="font-size:13px">' . esc_html__( 'Pay what you want — there are no price tiers, no pressure, no minimum.', 'vpg-v2' ) . '</p>';
    } else {
        echo '<p class="description">' . esc_html__( 'Supporting is being prepared — it is not open yet. When it opens, it will be pay-what-you-want, cancellable in one click, and pausable any time.', 'vpg-v2' ) . '</p>';
    }

    // 0968 · give in kind
    echo '<h2>' . esc_html__( 'Or give in kind', 'vpg-v2' ) . '</h2><ul style="list-style:disc;padding-left:22px;line-height:1.7">';
    echo '<li>' . esc_html__( 'Time — help at a walk, an exhibition, the archive.', 'vpg-v2' ) . '</li>';
    echo '<li>' . esc_html__( 'Skills — tax advice, design, legal, pro bono.', 'vpg-v2' ) . '</li>';
    echo '<li>' . esc_html__( 'Gear — an old camera passed to a beginner; a repair before a new buy.', 'vpg-v2' ) . '</li>';
    echo '</ul>';

    // 0963 · cost transparency
    if ( $costs ) {
        echo '<h2>' . esc_html__( 'What it actually costs', 'vpg-v2' ) . '</h2><table class="vpg-cardify" style="width:100%;max-width:420px">';
        foreach ( [ 'hosting' => __( 'Hosting (year)', 'vpg-v2' ), 'domains' => __( 'Domains (year)', 'vpg-v2' ), 'print' => __( 'Print (per issue)', 'vpg-v2' ), 'other' => __( 'Everything else (year)', 'vpg-v2' ) ] as $k => $label ) {
            if ( '' === ( $costs[ $k ] ?? '' ) ) continue;
            echo '<tr><td data-label="' . esc_attr__( 'Item', 'vpg-v2' ) . '">' . esc_html( $label ) . '</td><td>€ ' . esc_html( $costs[ $k ] ) . '</td></tr>';
        }
        echo '</table>';
    }

    // 0999 · legacy / memorial
    echo '<h2>' . esc_html__( 'Give in someone’s memory', 'vpg-v2' ) . '</h2>';
    echo '<p>' . esc_html__( 'You can support VPG in memory of someone who loved photography. Write to us and we’ll handle it with care.', 'vpg-v2' ) . ' <a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Get in touch', 'vpg-v2' ) . '</a></p>';

    // 0996 · supporter survey — ask before building
    echo '<h2>' . esc_html__( 'Before we build: what would you want?', 'vpg-v2' ) . '</h2>';
    if ( isset( $_GET['danke'] ) ) {
        echo '<p role="status" style="border-left:3px solid var(--g-red,#E5341F);padding-left:12px">' . esc_html__( 'Thank you — that helps us shape supporting the right way.', 'vpg-v2' ) . '</p>';
    }
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-vpg-keep="supporter-survey" style="display:grid;gap:12px;max-width:520px">';
    echo '<input type="hidden" name="action" value="vpg_supporter_survey">';
    echo wp_nonce_field( 'vpg_srv', '_vpg_srv', true, false );
    echo '<label>' . esc_html__( 'What would make supporting feel worth it to you?', 'vpg-v2' ) . '<br><textarea name="want" rows="3" class="g-input" style="width:100%"></textarea></label>';
    echo '<label>' . esc_html__( 'What could you imagine giving, if anything?', 'vpg-v2' ) . '<br><input type="text" name="amount" class="g-input" placeholder="' . esc_attr__( 'e.g. €5/month, once a year, in kind…', 'vpg-v2' ) . '" style="width:100%"></label>';
    echo '<p><button class="g-btn" type="submit">' . esc_html__( 'Send', 'vpg-v2' ) . '</button></p></form>';

    echo '<p style="margin-top:24px"><a href="' . esc_url( home_url( '/danke-wand/' ) ) . '">' . esc_html__( 'See the wall of thanks →', 'vpg-v2' ) . '</a></p>';
    vpg_sup_foot();
}

/* ---- 0966 · opt-in wall of thanks ---- */
function vpg_thanks_wall() {
    vpg_sup_head( __( 'Wall of thanks', 'vpg-v2' ) );
    echo '<p class="g-lede">' . esc_html__( 'The people who keep VPG going — named only because they chose to be.', 'vpg-v2' ) . '</p>';
    $supporters = get_users( [ 'meta_key' => '_vpg_supporter_visible', 'meta_value' => '1', 'number' => 500 ] );
    if ( $supporters ) {
        echo '<p style="line-height:2.2;font-size:17px">';
        foreach ( $supporters as $u ) echo '<span style="white-space:nowrap">' . esc_html( $u->display_name ) . '</span> &nbsp;·&nbsp; ';
        echo '</p>';
    } else {
        echo '<p class="description">' . esc_html__( 'No names yet — supporters appear here only if they opt in.', 'vpg-v2' ) . '</p>';
    }
    echo '<p style="font-size:13px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Every supporter is invisible by default. Naming is a choice, made in your dashboard, revocable any time.', 'vpg-v2' ) . '</p>';
    vpg_sup_foot();
}

/* ---- 0982 · annual report ---- */
function vpg_annual_report_page() {
    $year = isset( $_GET['jahr'] ) ? (int) $_GET['jahr'] : (int) wp_date( 'Y' );
    $narrative = get_option( 'vpg_annual_report_' . $year, '' );
    $members = (int) count_users()['total_users'];
    $posts = (int) ( wp_count_posts( 'post' )->publish ?? 0 );
    $events = (int) ( wp_count_posts( 'vpg_event' )->publish ?? 0 );
    $locations = (int) ( wp_count_posts( 'vpg_location' )->publish ?? 0 );
    vpg_sup_head( sprintf( __( 'Annual report %s', 'vpg-v2' ), $year ) );
    echo '<p class="g-lede">' . esc_html__( 'An honest look back — what we built, what it cost, where we stand.', 'vpg-v2' ) . '</p>';
    echo '<div style="display:flex;flex-wrap:wrap;gap:24px;margin:20px 0">';
    foreach ( [ __( 'Members', 'vpg-v2' ) => $members, __( 'Journal posts', 'vpg-v2' ) => $posts, __( 'Events', 'vpg-v2' ) => $events, __( 'Map locations', 'vpg-v2' ) => $locations ] as $label => $n ) {
        echo '<div><div style="font-size:34px;font-weight:900;color:var(--g-red,#E5341F)">' . esc_html( number_format_i18n( $n ) ) . '</div><div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--g-mid,#6A6A6A)">' . esc_html( $label ) . '</div></div>';
    }
    echo '</div>';
    if ( $narrative ) echo '<div class="g-prose">' . wp_kses_post( wpautop( $narrative ) ) . '</div>';
    else echo '<p class="description">' . esc_html__( 'The written retrospective for this year is still to come.', 'vpg-v2' ) . '</p>';
    vpg_sup_foot();
}

/* ---- 1000 · the ten-year letter (sealed until 2036) ---- */
function vpg_tenyear_page() {
    $letter = get_option( 'vpg_tenyear_letter', '' );
    $reveal = (int) get_option( 'vpg_tenyear_reveal', strtotime( '2036-01-01' ) );
    vpg_sup_head( __( 'The ten-year letter', 'vpg-v2' ) );
    if ( ! $letter ) {
        echo '<p class="g-lede">' . esc_html__( 'A letter to our future selves is being written.', 'vpg-v2' ) . '</p>';
    } elseif ( time() < $reveal ) {
        echo '<p class="g-lede">' . esc_html__( 'Sealed. A letter written today, to be opened when VPG turns older.', 'vpg-v2' ) . '</p>';
        echo '<div style="border:2px dashed var(--g-line,#E6E5E1);padding:40px;text-align:center;margin:24px 0">'
           . '<div style="font-size:48px">✉️🔒</div>'
           . '<p>' . esc_html( sprintf( __( 'Opens on %s.', 'vpg-v2' ), date_i18n( 'j. F Y', $reveal ) ) ) . '</p></div>';
    } else {
        echo '<p class="g-lede">' . esc_html__( 'Written years ago. Opened now.', 'vpg-v2' ) . '</p>';
        echo '<div class="g-prose" style="font-size:17px;line-height:1.8">' . wp_kses_post( wpautop( $letter ) ) . '</div>';
    }
    vpg_sup_foot();
}

/* ================================================================
 * 0966 · supporter visibility · 0997/0998 · cancel & pause dignity
 * ================================================================ */
add_action( 'vpg_profile_sections', function ( $user ) {
    if ( ! ( $user instanceof WP_User ) || $user->ID !== get_current_user_id() ) return;
    $tier = function_exists( 'vpg_member_tier' ) ? vpg_member_tier( $user->ID ) : get_user_meta( $user->ID, '_vpg_tier', true );
    if ( ! in_array( $tier, [ 'supporter', 'sustaining' ], true ) ) return; // only supporters see this

    if ( isset( $_POST['_vpg_sup'] ) && wp_verify_nonce( $_POST['_vpg_sup'], 'vpg_sup' ) ) {
        update_user_meta( $user->ID, '_vpg_supporter_visible', empty( $_POST['visible'] ) ? '' : '1' );
        if ( ! empty( $_POST['pause'] ) )  { update_user_meta( $user->ID, '_vpg_tier_status', 'paused' ); echo '<p role="status" style="color:var(--g-red,#E5341F)">' . esc_html__( 'Your support is paused — welcome back any time.', 'vpg-v2' ) . '</p>'; }
        if ( ! empty( $_POST['resume'] ) ) { update_user_meta( $user->ID, '_vpg_tier_status', 'active' ); }
        if ( ! empty( $_POST['end'] ) )    { update_user_meta( $user->ID, '_vpg_tier_status', 'ending' ); echo '<p role="status">' . esc_html__( 'Understood — your support will end. Thank you for everything.', 'vpg-v2' ) . '</p>'; }
        else echo '<p role="status" style="color:var(--g-red,#E5341F)">' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p>';
    }
    $status = get_user_meta( $user->ID, '_vpg_tier_status', true ) ?: 'active';
    echo '<section class="vpg-profile-sec"><h3>' . esc_html__( 'Your support', 'vpg-v2' ) . '</h3><form method="post">';
    echo wp_nonce_field( 'vpg_sup', '_vpg_sup', true, false );
    echo '<label><input type="checkbox" name="visible" value="1" ' . checked( get_user_meta( $user->ID, '_vpg_supporter_visible', true ), '1', false ) . '> ' . esc_html__( 'Name me on the public wall of thanks (off by default)', 'vpg-v2' ) . '</label>';
    echo '<p style="margin-top:12px">';
    // 0998 · pause / resume, 0997 · one-click end with dignity
    if ( 'paused' === $status ) echo '<button class="g-btn" name="resume" value="1">' . esc_html__( 'Resume support', 'vpg-v2' ) . '</button> ';
    else echo '<button class="g-btn" name="pause" value="1">' . esc_html__( 'Pause support', 'vpg-v2' ) . '</button> ';
    echo '<button class="g-btn" name="end" value="1" style="background:none;border:1px solid var(--g-line,#E6E5E1)">' . esc_html__( 'End support', 'vpg-v2' ) . '</button>';
    echo ' <button class="g-btn" name="save" value="1" style="background:none;border:1px solid var(--g-line,#E6E5E1)">' . esc_html__( 'Save', 'vpg-v2' ) . '</button></p>';
    echo '<p style="font-size:12px;color:var(--g-mid,#6A6A6A)">' . esc_html__( 'Pausing keeps your place without charge. Ending is one click, no dark patterns, no guilt — and thank you, sincerely.', 'vpg-v2' ) . '</p>';
    echo '</form></section>';
}, 36 );

/* ================================================================
 * 0996 · supporter survey (asked before building)
 * ================================================================ */
add_action( 'admin_post_vpg_supporter_survey', function () {
    if ( ! is_user_logged_in() ) { wp_safe_redirect( home_url() ); exit; }
    if ( ! isset( $_POST['_vpg_srv'] ) || ! wp_verify_nonce( $_POST['_vpg_srv'], 'vpg_srv' ) ) { wp_safe_redirect( home_url( '/unterstuetzen/' ) ); exit; }
    $r = (array) get_option( 'vpg_supporter_survey', [] );
    $r[] = [ 'want' => sanitize_text_field( wp_unslash( $_POST['want'] ?? '' ) ), 'amount' => sanitize_text_field( wp_unslash( $_POST['amount'] ?? '' ) ), 't' => time() ];
    update_option( 'vpg_supporter_survey', array_slice( $r, -500 ), false );
    wp_safe_redirect( home_url( '/unterstuetzen/?danke=1' ) );
    exit;
} );

/* ================================================================
 * Sustainability & governance desk
 * ================================================================ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-hub', __( 'Sustainability & support', 'vpg-v2' ), '🌱 ' . __( 'Sustainability', 'vpg-v2' ), 'manage_options', 'vpg-sustain', 'vpg_sustain_desk' );
} );
function vpg_sustain_desk() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    if ( isset( $_POST['_vpg_sd'] ) && wp_verify_nonce( $_POST['_vpg_sd'], 'vpg_sd' ) ) {
        update_option( 'vpg_donate_once', esc_url_raw( wp_unslash( $_POST['donate_once'] ?? '' ) ) );
        update_option( 'vpg_donate_monthly', esc_url_raw( wp_unslash( $_POST['donate_monthly'] ?? '' ) ) );
        update_option( 'vpg_donate_bank', sanitize_text_field( wp_unslash( $_POST['donate_bank'] ?? '' ) ) );
        update_option( 'vpg_costs', [
            'hosting' => sanitize_text_field( wp_unslash( $_POST['cost_hosting'] ?? '' ) ),
            'domains' => sanitize_text_field( wp_unslash( $_POST['cost_domains'] ?? '' ) ),
            'print'   => sanitize_text_field( wp_unslash( $_POST['cost_print'] ?? '' ) ),
            'other'   => sanitize_text_field( wp_unslash( $_POST['cost_other'] ?? '' ) ),
        ] );
        update_option( 'vpg_reserve_target', sanitize_text_field( wp_unslash( $_POST['reserve_target'] ?? '' ) ) );
        update_option( 'vpg_reserve_have', sanitize_text_field( wp_unslash( $_POST['reserve_have'] ?? '' ) ) );
        update_option( 'vpg_tenyear_letter', sanitize_textarea_field( wp_unslash( $_POST['tenyear'] ?? '' ) ) );
        update_option( 'vpg_annual_report_' . (int) wp_date( 'Y' ), sanitize_textarea_field( wp_unslash( $_POST['annual'] ?? '' ) ) );
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
    }
    $c = (array) get_option( 'vpg_costs', [] );
    $survey = (array) get_option( 'vpg_supporter_survey', [] );
    ?>
    <div class="wrap"><h1>🌱 <?php esc_html_e( 'Sustainability & support', 'vpg-v2' ); ?></h1>
      <p><a href="<?php echo esc_url( home_url( '/unterstuetzen/' ) ); ?>" target="_blank"><?php esc_html_e( 'Supporter page', 'vpg-v2' ); ?></a> ·
         <a href="<?php echo esc_url( home_url( '/jahresbericht/' ) ); ?>" target="_blank"><?php esc_html_e( 'Annual report', 'vpg-v2' ); ?></a> ·
         <a href="<?php echo esc_url( home_url( '/zehn-jahre/' ) ); ?>" target="_blank"><?php esc_html_e( 'Ten-year letter', 'vpg-v2' ); ?></a></p>

      <form method="post">
        <?php wp_nonce_field( 'vpg_sd', '_vpg_sd' ); ?>

        <h2><?php esc_html_e( '0961/0964/0965 · Support links (external — never a paywall)', 'vpg-v2' ); ?></h2>
        <p><label><?php esc_html_e( 'One-time link', 'vpg-v2' ); ?><br><input type="url" name="donate_once" class="regular-text" value="<?php echo esc_attr( get_option( 'vpg_donate_once', '' ) ); ?>"></label></p>
        <p><label><?php esc_html_e( 'Monthly link', 'vpg-v2' ); ?><br><input type="url" name="donate_monthly" class="regular-text" value="<?php echo esc_attr( get_option( 'vpg_donate_monthly', '' ) ); ?>"></label></p>
        <p><label><?php esc_html_e( 'Bank details (shown as text)', 'vpg-v2' ); ?><br><input type="text" name="donate_bank" class="large-text" value="<?php echo esc_attr( get_option( 'vpg_donate_bank', '' ) ); ?>"></label></p>

        <h2><?php esc_html_e( '0963 · Cost transparency (€)', 'vpg-v2' ); ?></h2>
        <p><?php esc_html_e( 'Hosting/yr', 'vpg-v2' ); ?> <input type="text" name="cost_hosting" value="<?php echo esc_attr( $c['hosting'] ?? '' ); ?>" size="8">
           <?php esc_html_e( 'Domains/yr', 'vpg-v2' ); ?> <input type="text" name="cost_domains" value="<?php echo esc_attr( $c['domains'] ?? '' ); ?>" size="8">
           <?php esc_html_e( 'Print/issue', 'vpg-v2' ); ?> <input type="text" name="cost_print" value="<?php echo esc_attr( $c['print'] ?? '' ); ?>" size="8">
           <?php esc_html_e( 'Other/yr', 'vpg-v2' ); ?> <input type="text" name="cost_other" value="<?php echo esc_attr( $c['other'] ?? '' ); ?>" size="8"></p>

        <h2><?php esc_html_e( '0993 · Reserves (six months as a cushion)', 'vpg-v2' ); ?></h2>
        <p><?php esc_html_e( 'Target €', 'vpg-v2' ); ?> <input type="text" name="reserve_target" value="<?php echo esc_attr( get_option( 'vpg_reserve_target', '' ) ); ?>" size="8">
           <?php esc_html_e( 'Have €', 'vpg-v2' ); ?> <input type="text" name="reserve_have" value="<?php echo esc_attr( get_option( 'vpg_reserve_have', '' ) ); ?>" size="8"></p>

        <h2><?php esc_html_e( '0982 · Annual report — this year’s retrospective', 'vpg-v2' ); ?></h2>
        <p><textarea name="annual" rows="5" class="large-text"><?php echo esc_textarea( get_option( 'vpg_annual_report_' . (int) wp_date( 'Y' ), '' ) ); ?></textarea></p>

        <h2><?php esc_html_e( '1000 · The ten-year letter (sealed until 2036)', 'vpg-v2' ); ?></h2>
        <p><textarea name="tenyear" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'Where should VPG stand in 2036? Write it today.', 'vpg-v2' ); ?>"><?php echo esc_textarea( get_option( 'vpg_tenyear_letter', '' ) ); ?></textarea></p>

        <p><button class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
      </form>

      <?php if ( $survey ) { echo '<h2>' . esc_html__( '0996 · Supporter survey', 'vpg-v2' ) . '</h2><p>' . esc_html( sprintf( __( '%s responses collected.', 'vpg-v2' ), number_format_i18n( count( $survey ) ) ) ) . '</p>'; } ?>

      <h2><?php esc_html_e( 'The Notfallordner — govern, endure, hand over', 'vpg-v2' ); ?></h2>
      <ol style="padding-left:20px;line-height:1.8">
        <li><?php esc_html_e( '0970/0971 Legal form & charitable status — clarify the Verein for the bank account and liability, check donation deductibility.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0972/0973 Funding — systematically scan City of Vienna culture funds and EU Creative Europe.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0974/0991/0992 Sponsorship rules, an exclusion list (with whom never), and an independence clause (no funder over 20%).', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0979/0980/0981 Succession: keys, domains, accounts in a sealed emergency folder; every critical thing known to at least two people.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0978 What happens to money if the collective ever ends (avoid the dead hand).', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0983/0984/0985 Yearly footprint of server & print; recycled paper as a rule; ask the host about green power.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0986/0987/0988/0989/0990 Gear-donation bridge, repair before replace, a fair-price code, and time & skill donations as equal currency.', 'vpg-v2' ); ?></li>
        <li><?php esc_html_e( '0994 Compare hosting alternatives yearly against price shocks; keep the six-month cushion.', 'vpg-v2' ); ?></li>
      </ol>
    </div>
    <?php
}
