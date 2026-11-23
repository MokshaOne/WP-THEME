<?php
/**
 * VPG v3 — Cluster 09 · Profile & Portfolio.
 *
 * Turns each member page into a small artist website, on top of the existing
 * /members/{slug}/ renderer (inc/members.php), _vpg_portfolio and the rank
 * ladder. Adds a statement, gear vitrine, availability chips, a structured
 * link set, a milestone timeline, year highlights, an activity calendar,
 * portfolio series, a guestbook, collaboration + duet links, an absence note,
 * a completion ring, a favourite-spots map, publications, pronouns, a
 * signature, a second-language bio, self-chosen style tags, a memorial mode,
 * a sketchbook, an anniversary badge, a reference flag, an auto-CV, a health
 * check, plus /card, /cv, /export and /private sub-pages.
 *
 *   0321 slug · 0323 statement · 0324 gear · 0325 available · 0326 links
 *   0327 QR card · 0328 milestones · 0329 highlights · 0330 calendar
 *   0331 preview · 0332 sort · 0333 series · 0335 card · 0336 guestbook
 *   0337 collab · 0339 absence · 0340 completion · 0342 fav map · 0343 tools
 *   0344 publications · 0347 name history · 0348 pronouns · 0349 signature
 *   0350 private area · 0351 bio EN · 0352 style tags · 0353 export
 *   0354 memorial · 0355 duet · 0356 sketchbook · 0357 anniversary
 *   0358 reference · 0359 auto CV · 0360 health check
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Field schema                                                     */
/* ════════════════════════════════════════════════════════════════ */
function vpg_profile_link_types() {
    return [ 'website' => 'Website', 'instagram' => 'Instagram', 'fediverse' => 'Fediverse', 'flickr' => 'Flickr', 'behance' => 'Behance', 'vsco' => 'VSCO' ];
}
function vpg_profile_available_opts() {
    return [ 'collab' => __( 'Collaboration', 'vpg-v2' ), 'commissions' => __( 'Commissions', 'vpg-v2' ), 'mentoring' => __( 'Mentoring', 'vpg-v2' ), 'prints' => __( 'Print sales', 'vpg-v2' ) ];
}

/* 0340 · completion ring — which fields are filled */
function vpg_profile_completion( $uid ) {
    $checks = [
        'avatar'    => (bool) get_user_meta( $uid, '_vpg_avatar', true ),
        'bio'       => (bool) get_userdata( $uid )->description,
        'statement' => (bool) get_user_meta( $uid, '_vpg_statement', true ),
        'portfolio' => (bool) ( function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $uid ) : [] ),
        'links'     => (bool) ( get_user_meta( $uid, '_vpg_links', true ) || get_userdata( $uid )->user_url ),
        'style'     => (bool) get_user_meta( $uid, '_vpg_style_tags', true ),
        'gear'      => (bool) get_user_meta( $uid, '_vpg_gear', true ),
    ];
    $have = count( array_filter( $checks ) );
    return [ 'pct' => (int) round( $have / count( $checks ) * 100 ), 'have' => $have, 'total' => count( $checks ), 'fields' => $checks ];
}

/* ════════════════════════════════════════════════════════════════ */
/*  Front-end · profile sections (hooked into inc/members.php)        */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'vpg_profile_sections', function ( $user ) {
    $uid = $user->ID;
    $memorial = get_user_meta( $uid, '_vpg_memorial', true ) === '1';
    $pronouns = get_user_meta( $uid, '_vpg_pronouns', true );
    $statement = get_user_meta( $uid, '_vpg_statement', true );
    $absence  = get_user_meta( $uid, '_vpg_absence', true );
    $avail    = array_filter( (array) get_user_meta( $uid, '_vpg_available', true ) );
    $style    = array_filter( array_map( 'trim', explode( ',', (string) get_user_meta( $uid, '_vpg_style_tags', true ) ) ) );
    $gear     = array_filter( array_map( 'trim', explode( "\n", (string) get_user_meta( $uid, '_vpg_gear', true ) ) ) );
    $toolhist = array_filter( array_map( 'trim', explode( "\n", (string) get_user_meta( $uid, '_vpg_gear_history', true ) ) ) );
    $links    = (array) get_user_meta( $uid, '_vpg_links', true );
    $pubs     = array_filter( array_map( 'trim', explode( "\n", (string) get_user_meta( $uid, '_vpg_publications', true ) ) ) );
    $highs    = array_filter( array_map( 'intval', (array) get_user_meta( $uid, '_vpg_year_highlights', true ) ) );
    $sketch   = array_filter( array_map( 'intval', (array) get_user_meta( $uid, '_vpg_sketchbook', true ) ) );
    $fav      = array_filter( array_map( 'intval', (array) get_user_meta( $uid, '_vpg_fav_spots', true ) ) );
    $duet     = (int) get_user_meta( $uid, '_vpg_duet', true );
    $bio_en   = get_user_meta( $uid, '_vpg_bio_en', true );
    $cmp      = vpg_profile_completion( $uid );
    $years    = ( time() - strtotime( $user->user_registered ) ) / YEAR_IN_SECONDS;

    echo '<div class="vpg-wrap" style="max-width:920px;margin:0 auto">';

    if ( $memorial ) {
        echo '<section class="vpg-section vpg-section--tight"><p style="border:1px solid var(--g-line,#E6E5E1);background:var(--g-off,#F5F4F1);padding:14px 18px;font-size:14px">🕊 ' . esc_html__( 'In memoriam — this profile is kept as a lasting tribute.', 'vpg-v2' ) . '</p></section>';
    }

    // badges row: pronouns · anniversary · reference · available
    $badges = [];
    if ( $pronouns ) $badges[] = esc_html( $pronouns );
    if ( $years >= 5 ) $badges[] = '★ ' . esc_html( sprintf( _n( '%d year member', '%d years member', floor( $years ), 'vpg-v2' ), floor( $years ) ) ); // 0357
    if ( get_user_meta( $uid, '_vpg_reference', true ) === '1' ) $badges[] = esc_html__( 'Available as a reference', 'vpg-v2' ); // 0358
    foreach ( $avail as $a ) $badges[] = esc_html( vpg_profile_available_opts()[ $a ] ?? $a ); // 0325
    foreach ( $style as $s ) $badges[] = '#' . esc_html( $s ); // 0352
    if ( $badges ) {
        echo '<section class="vpg-section vpg-section--tight"><div style="display:flex;flex-wrap:wrap;gap:8px">';
        foreach ( $badges as $b ) echo '<span style="font:700 11px/1 \'Archivo\',sans-serif;letter-spacing:.08em;border:1px solid var(--g-line,#E6E5E1);padding:6px 12px">' . $b . '</span>';
        echo '</div></section>';
    }

    if ( $absence ) echo '<section class="vpg-section vpg-section--tight"><p style="font-size:13px;color:var(--g-mid,#6A6A6A)">🌴 ' . esc_html( $absence ) . '</p></section>'; // 0339

    if ( $statement ) echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Statement', 'vpg-v2' ) . '</p><blockquote style="font-size:clamp(18px,2.6vw,26px);line-height:1.4;font-style:italic;margin:8px 0 0">' . esc_html( $statement ) . '</blockquote></section>'; // 0323

    if ( $bio_en ) echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— EN</p><p style="font-size:15px;color:var(--g-mid,#6A6A6A)">' . esc_html( $bio_en ) . '</p></section>'; // 0351

    // 0328 · milestone bar
    if ( function_exists( 'vpg_member_rank' ) ) {
        $r = vpg_member_rank( $uid );
        if ( is_array( $r ) && isset( $r['level'] ) ) {
            $ladder = function_exists( 'vpg_rank_ladder' ) ? vpg_rank_ladder() : [];
            echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'The journey', 'vpg-v2' ) . '</p><div style="display:flex;gap:0;margin-top:10px">';
            $i = 0;
            foreach ( $ladder as $lv => $label ) {
                $on = $i <= (int) $r['level'];
                echo '<div style="flex:1;text-align:center"><div style="height:6px;background:' . ( $on ? 'var(--g-red,#E5341F)' : 'var(--g-line,#E6E5E1)' ) . '"></div><span style="font-size:11px;font-weight:700;color:' . ( $on ? 'var(--g-ink,#0B0B0B)' : 'var(--g-mid,#6A6A6A)' ) . '">' . esc_html( is_array( $label ) ? ( $label['name'] ?? $lv ) : $label ) . '</span></div>';
                $i++;
            }
            echo '</div></section>';
        }
    }

    // 0329 · year highlights
    if ( $highs ) {
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Highlights of the year', 'vpg-v2' ) . '</p><div data-vpg-gallery style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:10px">';
        foreach ( array_slice( $highs, 0, 6 ) as $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); $f = wp_get_attachment_image_url( $id, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">'; }
        echo '</div></section>';
    }

    // 0333 · portfolio series
    $series = (array) get_user_meta( $uid, '_vpg_pf_series', true );
    foreach ( array_slice( $series, 0, 6 ) as $grp ) {
        $ids = array_filter( array_map( 'intval', (array) ( $grp['ids'] ?? [] ) ) );
        if ( ! $ids ) continue;
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Series', 'vpg-v2' ) . '</p><h3 style="margin:6px 0">' . esc_html( $grp['title'] ?? '' ) . '</h3>';
        if ( ! empty( $grp['text'] ) ) echo '<p style="color:var(--g-mid,#6A6A6A);max-width:60ch">' . esc_html( $grp['text'] ) . '</p>';
        echo '<div data-vpg-gallery style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:10px">';
        foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); $f = wp_get_attachment_image_url( $id, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">'; }
        echo '</div></section>';
    }

    // 0356 · sketchbook
    if ( $sketch ) {
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Sketchbook · work in progress', 'vpg-v2' ) . '</p><div data-vpg-gallery style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:10px;opacity:.85">';
        foreach ( array_slice( $sketch, 0, 8 ) as $id ) { $u = wp_get_attachment_image_url( $id, 'medium' ); $f = wp_get_attachment_image_url( $id, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover;filter:grayscale(.3)">'; }
        echo '</div></section>';
    }

    // 0330 · creation calendar (last 12 months upload matrix)
    $months = [];
    foreach ( get_posts( [ 'post_type' => 'attachment', 'author' => $uid, 'posts_per_page' => 400, 'post_status' => 'inherit', 'fields' => 'ids' ] ) as $aid ) {
        $m = get_the_date( 'Y-m', $aid ); $months[ $m ] = ( $months[ $m ] ?? 0 ) + 1;
    }
    if ( $months ) {
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'When they shoot', 'vpg-v2' ) . '</p><div style="display:flex;gap:3px;margin-top:10px;align-items:flex-end;height:56px">';
        for ( $i = 11; $i >= 0; $i-- ) { $key = gmdate( 'Y-m', strtotime( "-$i months" ) ); $n = $months[ $key ] ?? 0; $h = min( 48, 6 + $n * 6 ); echo '<div title="' . esc_attr( $key . ' · ' . $n ) . '" style="flex:1;background:' . ( $n ? 'var(--g-red,#E5341F)' : 'var(--g-line,#E6E5E1)' ) . ';height:' . $h . 'px"></div>'; }
        echo '</div></section>';
    }

    // 0324 gear vitrine · 0343 tool history
    if ( $gear || $toolhist ) {
        echo '<section class="vpg-section vpg-section--tight"><div style="display:flex;gap:40px;flex-wrap:wrap">';
        if ( $gear ) { echo '<div><p class="vpg-caps">— ' . esc_html__( 'In the bag', 'vpg-v2' ) . '</p><ul style="margin:8px 0 0;padding-left:16px;font-size:14px">'; foreach ( $gear as $g ) echo '<li>' . esc_html( $g ) . '</li>'; echo '</ul></div>'; }
        if ( $toolhist ) { echo '<div><p class="vpg-caps">— ' . esc_html__( 'Gear biography', 'vpg-v2' ) . '</p><ul style="margin:8px 0 0;padding-left:16px;font-size:14px;color:var(--g-mid,#6A6A6A)">'; foreach ( $toolhist as $g ) echo '<li>' . esc_html( $g ) . '</li>'; echo '</ul></div>'; }
        echo '</div></section>';
    }

    // 0342 · favourite spots map
    if ( $fav ) {
        $pins = [];
        foreach ( $fav as $lid ) { $c = function_exists( 'vpg_get_coords' ) ? vpg_get_coords( $lid ) : null; if ( $c ) $pins[] = [ 'lat' => $c[0], 'lng' => $c[1], 'title' => get_the_title( $lid ), 'url' => get_permalink( $lid ), 'type' => 'location' ]; }
        if ( $pins ) echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Their Vienna', 'vpg-v2' ) . '</p><div id="vpg-map" class="vpg-map" data-pins="' . esc_attr( wp_json_encode( $pins ) ) . '" style="height:320px;margin-top:10px"></div></section>';
    }

    // 0326 links · 0344 publications
    if ( $links || $pubs || $user->user_url ) {
        echo '<section class="vpg-section vpg-section--tight"><div style="display:flex;gap:40px;flex-wrap:wrap">';
        echo '<div><p class="vpg-caps">— ' . esc_html__( 'Elsewhere', 'vpg-v2' ) . '</p><p style="margin-top:8px;display:flex;gap:12px;flex-wrap:wrap">';
        if ( $user->user_url ) echo '<a href="' . esc_url( $user->user_url ) . '" rel="me nofollow">Website ↗</a>';
        foreach ( vpg_profile_link_types() as $k => $lbl ) { $v = $links[ $k ] ?? ''; if ( $v ) echo '<a href="' . esc_url( $v ) . '" rel="me nofollow">' . esc_html( $lbl ) . ' ↗</a>'; }
        echo '</p></div>';
        if ( $pubs ) { echo '<div><p class="vpg-caps">— ' . esc_html__( 'Published in', 'vpg-v2' ) . '</p><ul style="margin:8px 0 0;padding-left:16px;font-size:14px">'; foreach ( $pubs as $p ) echo '<li>' . wp_kses_post( make_clickable( esc_html( $p ) ) ) . '</li>'; echo '</ul></div>'; }
        echo '</div></section>';
    }

    // 0337 collab · 0355 duet
    $collabs = array_filter( array_map( 'intval', (array) get_user_meta( $uid, '_vpg_collabs', true ) ) );
    if ( $collabs || $duet ) {
        echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Together with', 'vpg-v2' ) . '</p><p style="margin-top:8px;display:flex;gap:14px;flex-wrap:wrap">';
        if ( $duet && ( $du = get_userdata( $duet ) ) ) echo '<a href="' . esc_url( home_url( '/members/' . $du->user_nicename . '/' ) ) . '" style="font-weight:700">🤝 ' . esc_html( $du->display_name ) . '</a>';
        foreach ( $collabs as $cu ) { $c = get_userdata( $cu ); if ( $c ) echo '<a href="' . esc_url( home_url( '/members/' . $c->user_nicename . '/' ) ) . '">' . esc_html( $c->display_name ) . '</a>'; }
        echo '</p></section>';
    }

    // 0359 auto CV + 0327/0335 card links + 0353 export (owner)
    echo '<section class="vpg-section vpg-section--tight"><p style="display:flex;gap:12px;flex-wrap:wrap">';
    echo '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/members/' . $user->user_nicename . '/cv/' ) ) . '">📄 ' . esc_html__( 'Auto CV', 'vpg-v2' ) . '</a>';
    echo '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/members/' . $user->user_nicename . '/card/' ) ) . '" target="_blank">🪪 ' . esc_html__( 'Profile card (QR)', 'vpg-v2' ) . '</a>';
    if ( get_current_user_id() === $uid ) echo '<a class="g-btn g-btn--ghost" style="font-size:12px" href="' . esc_url( home_url( '/members/' . $user->user_nicename . '/export/' ) ) . '">⬇ ' . esc_html__( 'Export my content', 'vpg-v2' ) . '</a>';
    echo '</p></section>';

    // 0336 · guestbook (moderated)
    $guest = array_filter( (array) get_user_meta( $uid, '_vpg_guestbook', true ), fn( $g ) => ! empty( $g['ok'] ) );
    echo '<section class="vpg-section vpg-section--tight"><p class="vpg-caps">— ' . esc_html__( 'Guestbook', 'vpg-v2' ) . '</p>';
    foreach ( array_slice( array_reverse( $guest ), 0, 8 ) as $g ) echo '<blockquote style="border-left:3px solid var(--g-line,#E6E5E1);padding-left:14px;margin:12px 0;font-size:14px">' . esc_html( $g['msg'] ?? '' ) . '<footer style="color:var(--g-mid,#6A6A6A);font-size:12px">— ' . esc_html( $g['by'] ?? '' ) . '</footer></blockquote>';
    if ( is_user_logged_in() && get_current_user_id() !== $uid && ! $memorial ) {
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:flex;gap:8px;margin-top:10px">' . wp_nonce_field( 'vpg_guestbook', '_wpnonce', true, false );
        echo '<input type="hidden" name="action" value="vpg_guestbook"><input type="hidden" name="uid" value="' . (int) $uid . '">';
        echo '<input type="text" name="msg" maxlength="200" required placeholder="' . esc_attr__( 'Leave a kind word (moderated)…', 'vpg-v2' ) . '" style="flex:1;padding:8px;border:1px solid var(--g-line)"><button class="g-btn g-btn--ghost" style="font-size:12px">' . esc_html__( 'Send', 'vpg-v2' ) . '</button></form>';
    }
    echo '</section>';

    // 0340 completion ring + 0360 health check (owner only)
    if ( get_current_user_id() === $uid ) {
        echo '<section class="vpg-section vpg-section--tight"><div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">';
        $deg = $cmp['pct'] * 3.6;
        echo '<div style="width:64px;height:64px;border-radius:50%;background:conic-gradient(var(--g-red,#E5341F) ' . $deg . 'deg,var(--g-line,#E6E5E1) 0);display:flex;align-items:center;justify-content:center"><span style="width:48px;height:48px;border-radius:50%;background:var(--g-paper,#fff);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:13px">' . (int) $cmp['pct'] . '%</span></div>';
        echo '<div><p style="font-weight:700;margin:0">' . esc_html__( 'Profile completeness', 'vpg-v2' ) . '</p><p style="font-size:12px;color:var(--g-mid,#6A6A6A);margin:2px 0 0">' . esc_html__( 'Only you see this. Fill what feels right.', 'vpg-v2' ) . '</p></div>';
        echo '</div>';
        $dead = get_user_meta( $uid, '_vpg_dead_profile_links', true );
        if ( is_array( $dead ) && $dead ) echo '<p style="color:var(--g-red,#E5341F);font-size:12px;margin-top:10px">⚠ ' . esc_html( sprintf( __( '%d link on your profile looks dead — check Elsewhere & Published in.', 'vpg-v2' ), count( $dead ) ) ) . '</p>';
        echo '</section>';
    }

    echo '</div>';
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Save the extra profile fields (own dashboard form)               */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_save_profile_extra', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_profile_extra' );
    $uid = get_current_user_id();
    $text = [ '_vpg_statement' => 'statement', '_vpg_absence' => 'absence', '_vpg_pronouns' => 'pronouns', '_vpg_bio_en' => 'bio_en', '_vpg_style_tags' => 'style_tags' ];
    foreach ( $text as $mk => $f ) { $v = sanitize_text_field( wp_unslash( $_POST[ $f ] ?? '' ) ); $v !== '' ? update_user_meta( $uid, $mk, $v ) : delete_user_meta( $uid, $mk ); }
    foreach ( [ '_vpg_gear' => 'gear', '_vpg_gear_history' => 'gear_history', '_vpg_publications' => 'publications' ] as $mk => $f ) {
        $v = sanitize_textarea_field( wp_unslash( $_POST[ $f ] ?? '' ) ); $v !== '' ? update_user_meta( $uid, $mk, $v ) : delete_user_meta( $uid, $mk );
    }
    // 0321 custom slug (unique)
    $slug = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
    if ( $slug ) {
        $taken = get_users( [ 'meta_key' => '_vpg_slug', 'meta_value' => $slug, 'exclude' => [ $uid ], 'number' => 1 ] );
        if ( ! $taken && ! get_user_by( 'slug', $slug ) ) update_user_meta( $uid, '_vpg_slug', $slug );
    } else { delete_user_meta( $uid, '_vpg_slug' ); }
    // available chips + links + flags
    $av = array_values( array_intersect( array_keys( vpg_profile_available_opts() ), (array) ( $_POST['available'] ?? [] ) ) );
    $av ? update_user_meta( $uid, '_vpg_available', $av ) : delete_user_meta( $uid, '_vpg_available' );
    $links = [];
    foreach ( vpg_profile_link_types() as $k => $lbl ) { $v = esc_url_raw( wp_unslash( $_POST['links'][ $k ] ?? '' ) ); if ( $v ) $links[ $k ] = $v; }
    $links ? update_user_meta( $uid, '_vpg_links', $links ) : delete_user_meta( $uid, '_vpg_links' );
    update_user_meta( $uid, '_vpg_reference', empty( $_POST['reference'] ) ? '' : '1' );
    // 0329 highlights + 0356 sketchbook (comma ids)
    foreach ( [ '_vpg_year_highlights' => 'highlights', '_vpg_sketchbook' => 'sketchbook', '_vpg_fav_spots' => 'fav_spots' ] as $mk => $f ) {
        $ids = array_slice( array_filter( array_map( 'intval', explode( ',', (string) ( $_POST[ $f ] ?? '' ) ) ) ), 0, 12 );
        $ids ? update_user_meta( $uid, $mk, $ids ) : delete_user_meta( $uid, $mk );
    }
    // 0349 signature attachment
    $sig = (int) ( $_POST['signature'] ?? 0 );
    $sig ? update_user_meta( $uid, '_vpg_signature', $sig ) : delete_user_meta( $uid, '_vpg_signature' );
    // 0355 duet · 0337 collaborators
    $duet = (int) ( $_POST['duet'] ?? 0 );
    ( $duet && get_userdata( $duet ) ) ? update_user_meta( $uid, '_vpg_duet', $duet ) : delete_user_meta( $uid, '_vpg_duet' );
    $collabs = array_slice( array_filter( array_map( 'intval', explode( ',', (string) ( $_POST['collabs'] ?? '' ) ) ) ), 0, 20 );
    $collabs ? update_user_meta( $uid, '_vpg_collabs', $collabs ) : delete_user_meta( $uid, '_vpg_collabs' );
    // 0350 private area
    $priv = array_slice( array_filter( array_map( 'intval', explode( ',', (string) ( $_POST['private_series'] ?? '' ) ) ) ), 0, 40 );
    $priv ? update_user_meta( $uid, '_vpg_private_series', $priv ) : delete_user_meta( $uid, '_vpg_private_series' );
    $ppw = sanitize_text_field( wp_unslash( $_POST['private_pw'] ?? '' ) );
    $ppw !== '' ? update_user_meta( $uid, '_vpg_private_pw', $ppw ) : delete_user_meta( $uid, '_vpg_private_pw' );
    // 0333 portfolio series ("Title | id,id" per line)
    $groups = [];
    foreach ( array_filter( array_map( 'trim', explode( "\n", (string) wp_unslash( $_POST['pf_series'] ?? '' ) ) ) ) as $line ) {
        $p = array_map( 'trim', explode( '|', $line, 3 ) );
        $ids = array_filter( array_map( 'intval', explode( ',', $p[1] ?? '' ) ) );
        if ( $p[0] !== '' && $ids ) $groups[] = [ 'title' => sanitize_text_field( $p[0] ), 'ids' => array_values( $ids ), 'text' => sanitize_text_field( $p[2] ?? '' ) ];
    }
    $groups ? update_user_meta( $uid, '_vpg_pf_series', $groups ) : delete_user_meta( $uid, '_vpg_pf_series' );
    wp_safe_redirect( ( wp_get_referer() ?: home_url( '/dashboard/' ) ) . '#profile-extra' ); exit;
} );

/* 0347 · keep a name history when the display name changes */
add_action( 'profile_update', function ( $uid, $old ) {
    $new = get_userdata( $uid );
    if ( $new && $old->display_name && $new->display_name !== $old->display_name ) {
        $hist = (array) get_user_meta( $uid, '_vpg_name_history', true );
        $hist[] = [ 'name' => $old->display_name, 't' => time() ];
        update_user_meta( $uid, '_vpg_name_history', array_slice( $hist, -10 ) );
    }
}, 10, 2 );

/* 0336 · guestbook post (moderated → editor approves) */
add_action( 'admin_post_vpg_guestbook', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only', 403 );
    check_admin_referer( 'vpg_guestbook' );
    $uid = (int) ( $_POST['uid'] ?? 0 );
    if ( ! get_userdata( $uid ) ) wp_die( 'Not found', 404 );
    $gb = (array) get_user_meta( $uid, '_vpg_guestbook', true );
    $gb[] = [ 'by' => wp_get_current_user()->display_name, 'from' => get_current_user_id(), 'msg' => sanitize_text_field( wp_unslash( $_POST['msg'] ?? '' ) ), 'ok' => 0, 't' => time() ];
    update_user_meta( $uid, '_vpg_guestbook', array_slice( $gb, -100 ) );
    if ( function_exists( 'vpg_notify_user' ) ) vpg_notify_user( $uid, __( 'A new guestbook note awaits your OK', 'vpg-v2' ), home_url( '/dashboard/' ), 'profile' );
    wp_safe_redirect( wp_get_referer() ?: home_url() ); exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Sub-pages · /members/{slug}/(card|cv|export|private)              */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^members/([^/]+)/(card|cv|export|private)/?$', 'index.php?vpg_member=$matches[1]&vpg_profile_sub=$matches[2]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_profile_sub'; return $v; } );

add_action( 'template_redirect', function () {
    $sub = get_query_var( 'vpg_profile_sub' );
    if ( ! $sub || ! get_query_var( 'vpg_member' ) ) return;
    $slug = sanitize_user( get_query_var( 'vpg_member' ) );
    $user = get_user_by( 'login', $slug ) ?: get_user_by( 'slug', $slug );
    if ( ! $user ) { status_header( 404 ); wp_die( 'Not found', 404 ); }
    $uid = $user->ID;
    $profile_url = home_url( '/members/' . $user->user_nicename . '/' );

    if ( $sub === 'card' ) { // 0327 / 0335
        nocache_headers(); header( 'Content-Type: text/html; charset=utf-8' );
        $style = array_filter( array_map( 'trim', explode( ',', (string) get_user_meta( $uid, '_vpg_style_tags', true ) ) ) );
        ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html( $user->display_name ); ?> · Card</title>
        <style>*{box-sizing:border-box;margin:0}body{font-family:'Helvetica Neue',Arial,sans-serif;background:#e8e7e3;padding:24px;display:flex;flex-direction:column;align-items:center;gap:14px}.card{width:85mm;height:55mm;background:#fff;border:1px solid #0B0B0B;padding:8mm;display:flex;justify-content:space-between}.l span{width:16px;height:16px;background:#E5341F;display:block}.n{font-weight:900;font-size:16px;text-transform:uppercase}.m{font-size:10px;color:#6A6A6A}#qr{margin-top:2mm}.noprint button{border:1px solid #0B0B0B;background:#fff;padding:8px 16px;font-weight:700;cursor:pointer}@media print{body{background:#fff}.noprint{display:none}}</style></head><body>
        <div class="card"><div class="l"><span></span><div style="margin-top:auto"><p class="n"><?php echo esc_html( $user->display_name ); ?></p><p class="m">Vienna Photo Group<?php echo $style ? ' · ' . esc_html( implode( ' · ', array_slice( $style, 0, 3 ) ) ) : ''; ?></p></div></div><div style="text-align:right"><div id="qr"></div></div></div>
        <p class="noprint"><button onclick="window.print()">Print</button></p>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>new QRCode(document.getElementById('qr'),{text:<?php echo wp_json_encode( $profile_url ); ?>,width:120,height:120,colorDark:'#0B0B0B',colorLight:'#fff'});</script>
        </body></html><?php exit;
    }

    if ( $sub === 'cv' ) { // 0359 · auto CV from magazine credits + events + exhibitions
        $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => -1 ] );
        $credits = [];
        foreach ( $issues as $iss ) { foreach ( (array) ( function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $iss->ID ) : [] ) as $a ) { if ( ! empty( $a['author'] ) && $a['author'] === $user->display_name ) $credits[] = [ get_the_title( $iss ), $a['title'] ?? '' ]; } }
        $hosted = get_posts( [ 'post_type' => 'vpg_event', 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => 40 ] );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Curriculum vitae', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo esc_html( $user->display_name ); ?></h1><p class="g-lede g-phero__lede"><?php printf( esc_html__( 'Member of Vienna Photo Group since %s.', 'vpg-v2' ), esc_html( date_i18n( 'Y', strtotime( $user->user_registered ) ) ) ); ?></p></div></section>
        <section class="g-section"><div class="g-wrap" style="max-width:720px"><div class="g-prose">
          <?php if ( $user->description ) : ?><p><?php echo esc_html( $user->description ); ?></p><?php endif; ?>
          <?php if ( $credits ) : ?><h3><?php esc_html_e( 'Publications', 'vpg-v2' ); ?></h3><ul><?php foreach ( $credits as $c ) echo '<li>' . esc_html( $c[1] . ' — ' . $c[0] ) . '</li>'; ?></ul><?php endif; ?>
          <?php if ( $hosted ) : ?><h3><?php esc_html_e( 'Led photowalks', 'vpg-v2' ); ?></h3><ul><?php foreach ( $hosted as $e ) echo '<li>' . esc_html( get_the_title( $e ) . ' · ' . get_post_meta( $e->ID, '_vpg_event_date', true ) ) . '</li>'; ?></ul><?php endif; ?>
          <?php $pubs = array_filter( array_map( 'trim', explode( "\n", (string) get_user_meta( $uid, '_vpg_publications', true ) ) ) );
          if ( $pubs ) : ?><h3><?php esc_html_e( 'External mentions', 'vpg-v2' ); ?></h3><ul><?php foreach ( $pubs as $p ) echo '<li>' . esc_html( $p ) . '</li>'; ?></ul><?php endif; ?>
        </div></div></section></main>
        <?php get_footer(); exit;
    }

    if ( $sub === 'export' ) { // 0353 · own-content ZIP
        if ( get_current_user_id() !== $uid ) wp_die( 'Only the owner can export.', 403 );
        if ( ! class_exists( 'ZipArchive' ) ) wp_die( 'ZIP unavailable on this host.', 500 );
        $tmp = wp_tempnam( 'vpg-export' );
        $zip = new ZipArchive();
        if ( $zip->open( $tmp, ZipArchive::OVERWRITE ) !== true ) wp_die( 'Could not create archive.', 500 );
        $profile = [ 'name' => $user->display_name, 'bio' => $user->description, 'statement' => get_user_meta( $uid, '_vpg_statement', true ), 'links' => get_user_meta( $uid, '_vpg_links', true ), 'registered' => $user->user_registered ];
        $zip->addFromString( 'profile.json', wp_json_encode( $profile, JSON_PRETTY_PRINT ) );
        foreach ( (array) ( function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $uid ) : [] ) as $aid ) {
            $f = get_attached_file( $aid );
            if ( $f && file_exists( $f ) ) $zip->addFile( $f, 'portfolio/' . basename( $f ) );
        }
        $zip->close();
        nocache_headers();
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="vpg-' . $user->user_nicename . '.zip"' );
        header( 'Content-Length: ' . filesize( $tmp ) );
        readfile( $tmp );
        @unlink( $tmp );
        exit;
    }

    if ( $sub === 'private' ) { // 0350 · password-protected series
        $pw  = get_user_meta( $uid, '_vpg_private_pw', true );
        $ids = array_filter( array_map( 'intval', (array) get_user_meta( $uid, '_vpg_private_series', true ) ) );
        $ok  = get_current_user_id() === $uid || ( $pw && isset( $_GET['pw'] ) && hash_equals( $pw, (string) $_GET['pw'] ) );
        get_header(); ?>
        <main id="vpg-main"><section class="g-phero"><div class="g-wrap"><p class="g-kicker" style="margin-bottom:16px">● <?php esc_html_e( 'Private series', 'vpg-v2' ); ?></p><h1 class="g-display g-phero__title"><?php echo esc_html( $user->display_name ); ?></h1></div></section>
        <section class="g-section"><div class="g-wrap">
          <?php if ( ! $ok ) : ?>
            <form method="get" style="display:flex;gap:8px;max-width:360px"><input type="password" name="pw" placeholder="<?php esc_attr_e( 'Password', 'vpg-v2' ); ?>" style="flex:1;padding:9px;border:1px solid var(--g-line)"><button class="g-btn g-btn--red"><?php esc_html_e( 'Enter', 'vpg-v2' ); ?></button></form>
          <?php elseif ( $ids ) : ?>
            <div data-vpg-gallery style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px"><?php foreach ( $ids as $id ) { $u = wp_get_attachment_image_url( $id, 'large' ); $f = wp_get_attachment_image_url( $id, 'full' ); if ( $u ) echo '<img src="' . esc_url( $u ) . '" data-full="' . esc_url( $f ) . '" alt="" style="width:100%;aspect-ratio:1;object-fit:cover">'; } ?></div>
          <?php else : ?><p class="g-lede"><?php esc_html_e( 'Nothing here yet.', 'vpg-v2' ); ?></p><?php endif; ?>
        </div></section></main>
        <?php get_footer(); exit;
    }
} );

/* 0360 · weekly profile link health check */
add_action( 'init', function () {
    if ( ! wp_next_scheduled( 'vpg_profile_healthcheck' ) ) wp_schedule_event( time() + 2 * HOUR_IN_SECONDS, 'weekly', 'vpg_profile_healthcheck' );
} );
add_action( 'vpg_profile_healthcheck', function () {
    foreach ( get_users( [ 'meta_key' => '_vpg_links', 'number' => 100 ] ) as $u ) {
        $urls = array_values( array_filter( (array) get_user_meta( $u->ID, '_vpg_links', true ) ) );
        if ( $u->user_url ) $urls[] = $u->user_url;
        $dead = [];
        foreach ( array_slice( $urls, 0, 8 ) as $url ) {
            $r = wp_remote_head( $url, [ 'timeout' => 8, 'redirection' => 3 ] );
            $code = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
            if ( $code === 0 || $code >= 400 ) $dead[] = $url;
        }
        $dead ? update_user_meta( $u->ID, '_vpg_dead_profile_links', $dead ) : delete_user_meta( $u->ID, '_vpg_dead_profile_links' );
    }
} );
add_action( 'switch_theme', function () { wp_clear_scheduled_hook( 'vpg_profile_healthcheck' ); } );

/* admin · approve guestbook + toggle memorial */
add_action( 'admin_post_vpg_guestbook_ok', function () {
    if ( ! current_user_can( 'edit_users' ) && get_current_user_id() !== (int) ( $_GET['uid'] ?? 0 ) ) wp_die( 'Forbidden', 403 );
    check_admin_referer( 'vpg_guestbook_ok' );
    $uid = (int) $_GET['uid']; $k = (int) $_GET['k'];
    $gb = (array) get_user_meta( $uid, '_vpg_guestbook', true );
    if ( isset( $gb[ $k ] ) ) { $gb[ $k ]['ok'] = 1; update_user_meta( $uid, '_vpg_guestbook', $gb ); }
    wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) ); exit;
} );

/* the extra-profile dashboard form (rendered by the dashboard template) */
function vpg_profile_extra_form() {
    if ( ! is_user_logged_in() ) return;
    $uid = get_current_user_id();
    $links = (array) get_user_meta( $uid, '_vpg_links', true );
    $avail = array_filter( (array) get_user_meta( $uid, '_vpg_available', true ) );
    ?>
    <section class="g-section g-section--tight" id="profile-extra"><div class="g-wrap" style="max-width:720px">
      <div class="g-head"><div><span class="g-kicker"><?php esc_html_e( 'Artist page', 'vpg-v2' ); ?></span><h2 class="g-head__t"><?php esc_html_e( 'The <em>fuller</em> profile', 'vpg-v2' ); ?></h2></div></div>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:grid;gap:12px">
        <?php wp_nonce_field( 'vpg_profile_extra' ); ?><input type="hidden" name="action" value="vpg_save_profile_extra">
        <label><?php esc_html_e( 'Custom profile URL', 'vpg-v2' ); ?><br><input type="text" name="slug" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_slug', true ) ); ?>" placeholder="mein-name" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        <label><?php esc_html_e( 'Pronouns', 'vpg-v2' ); ?><br><input type="text" name="pronouns" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_pronouns', true ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        <label><?php esc_html_e( 'Statement (five sentences)', 'vpg-v2' ); ?><br><textarea name="statement" rows="3" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( get_user_meta( $uid, '_vpg_statement', true ) ); ?></textarea></label>
        <label><?php esc_html_e( 'Bio · English', 'vpg-v2' ); ?><br><textarea name="bio_en" rows="2" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( get_user_meta( $uid, '_vpg_bio_en', true ) ); ?></textarea></label>
        <label><?php esc_html_e( 'Style tags (comma-separated, your words)', 'vpg-v2' ); ?><br><input type="text" name="style_tags" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_style_tags', true ) ); ?>" placeholder="quiet, urban, analogue" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        <fieldset style="border:1px solid var(--g-line);padding:10px"><legend><?php esc_html_e( 'Available for', 'vpg-v2' ); ?></legend>
          <?php foreach ( vpg_profile_available_opts() as $k => $lbl ) : ?><label style="margin-right:14px;font-weight:400"><input type="checkbox" name="available[]" value="<?php echo esc_attr( $k ); ?>"<?php checked( in_array( $k, $avail, true ) ); ?>> <?php echo esc_html( $lbl ); ?></label><?php endforeach; ?>
          <br><label style="font-weight:400"><input type="checkbox" name="reference" value="1"<?php checked( get_user_meta( $uid, '_vpg_reference', true ), '1' ); ?>> <?php esc_html_e( 'Contactable as a reference', 'vpg-v2' ); ?></label>
        </fieldset>
        <fieldset style="border:1px solid var(--g-line);padding:10px"><legend><?php esc_html_e( 'Links elsewhere', 'vpg-v2' ); ?></legend>
          <?php foreach ( vpg_profile_link_types() as $k => $lbl ) : if ( $k === 'website' ) continue; ?><label style="display:block;margin-bottom:6px"><?php echo esc_html( $lbl ); ?><br><input type="url" name="links[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $links[ $k ] ?? '' ); ?>" style="width:100%;padding:6px;border:1px solid var(--g-line)"></label><?php endforeach; ?>
        </fieldset>
        <label><?php esc_html_e( 'In the bag (one per line)', 'vpg-v2' ); ?><br><textarea name="gear" rows="3" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( get_user_meta( $uid, '_vpg_gear', true ) ); ?></textarea></label>
        <label><?php esc_html_e( 'Gear biography (one per line)', 'vpg-v2' ); ?><br><textarea name="gear_history" rows="2" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( get_user_meta( $uid, '_vpg_gear_history', true ) ); ?></textarea></label>
        <label><?php esc_html_e( 'Published in / mentions (one per line)', 'vpg-v2' ); ?><br><textarea name="publications" rows="2" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php echo esc_textarea( get_user_meta( $uid, '_vpg_publications', true ) ); ?></textarea></label>
        <label><?php esc_html_e( 'Away note', 'vpg-v2' ); ?><br><input type="text" name="absence" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_absence', true ) ); ?>" placeholder="<?php esc_attr_e( 'On holiday until…', 'vpg-v2' ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
          <label><?php esc_html_e( 'Year highlights (attachment IDs)', 'vpg-v2' ); ?><br><input type="text" name="highlights" value="<?php echo esc_attr( implode( ',', (array) get_user_meta( $uid, '_vpg_year_highlights', true ) ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
          <label><?php esc_html_e( 'Sketchbook (IDs)', 'vpg-v2' ); ?><br><input type="text" name="sketchbook" value="<?php echo esc_attr( implode( ',', (array) get_user_meta( $uid, '_vpg_sketchbook', true ) ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
          <label><?php esc_html_e( 'Favourite spots (location IDs)', 'vpg-v2' ); ?><br><input type="text" name="fav_spots" value="<?php echo esc_attr( implode( ',', (array) get_user_meta( $uid, '_vpg_fav_spots', true ) ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        </div>
        <label><?php esc_html_e( 'Signature (attachment ID for print credits)', 'vpg-v2' ); ?><br><input type="number" name="signature" value="<?php echo (int) get_user_meta( $uid, '_vpg_signature', true ) ?: ''; ?>" style="width:120px;padding:8px;border:1px solid var(--g-line)"></label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <label><?php esc_html_e( 'Duet partner (member ID)', 'vpg-v2' ); ?><br><input type="number" name="duet" value="<?php echo (int) get_user_meta( $uid, '_vpg_duet', true ) ?: ''; ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
          <label><?php esc_html_e( 'Collaborators (member IDs)', 'vpg-v2' ); ?><br><input type="text" name="collabs" value="<?php echo esc_attr( implode( ',', (array) get_user_meta( $uid, '_vpg_collabs', true ) ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        </div>
        <label><?php esc_html_e( 'Portfolio series — one per line: “Title | id,id,id | short text”', 'vpg-v2' ); ?><br>
          <textarea name="pf_series" rows="3" style="width:100%;padding:8px;border:1px solid var(--g-line)"><?php
            $g = (array) get_user_meta( $uid, '_vpg_pf_series', true );
            echo esc_textarea( implode( "\n", array_map( fn( $x ) => ( $x['title'] ?? '' ) . ' | ' . implode( ',', (array) ( $x['ids'] ?? [] ) ) . ' | ' . ( $x['text'] ?? '' ), $g ) ) );
          ?></textarea></label>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:10px">
          <label><?php esc_html_e( 'Private series (attachment IDs)', 'vpg-v2' ); ?><br><input type="text" name="private_series" value="<?php echo esc_attr( implode( ',', (array) get_user_meta( $uid, '_vpg_private_series', true ) ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
          <label><?php esc_html_e( 'Private password', 'vpg-v2' ); ?><br><input type="text" name="private_pw" value="<?php echo esc_attr( get_user_meta( $uid, '_vpg_private_pw', true ) ); ?>" style="width:100%;padding:8px;border:1px solid var(--g-line)"></label>
        </div>
        <div id="vpg-pp" style="border:1px dashed var(--g-line);padding:12px;font-size:13px;color:var(--g-mid,#6A6A6A)"><strong><?php esc_html_e( 'Live preview', 'vpg-v2' ); ?>:</strong> <span id="vpg-pp-out"></span></div>
        <p><button class="g-btn g-btn--red"><?php esc_html_e( 'Save artist page', 'vpg-v2' ); ?></button></p>
      </form>
    </div></section>
    <script>
    (function(){var f=document.querySelector('form[action*="admin-post"] textarea[name="statement"]');if(!f)return;
      var pn=document.querySelector('input[name="pronouns"]'),st=document.querySelector('input[name="style_tags"]'),out=document.getElementById('vpg-pp-out');
      function up(){out.textContent=(pn&&pn.value?pn.value+' · ':'')+(f.value||'—')+(st&&st.value?' · #'+st.value.split(',').join(' #'):'');}
      [f,pn,st].forEach(function(el){if(el)el.addEventListener('input',up);});up();
    })();
    </script>
    <?php
}

/* 0354 · memorial mode — admin toggle on the user edit screen */
add_action( 'edit_user_profile', 'vpg_memorial_field' );
add_action( 'show_user_profile', 'vpg_memorial_field' );
function vpg_memorial_field( $user ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    ?><h2><?php esc_html_e( 'VPG memorial', 'vpg-v2' ); ?></h2>
    <table class="form-table"><tr><th><?php esc_html_e( 'Memorial mode', 'vpg-v2' ); ?></th><td>
      <label><input type="checkbox" name="vpg_memorial" value="1"<?php checked( get_user_meta( $user->ID, '_vpg_memorial', true ), '1' ); ?>> <?php esc_html_e( 'Freeze this profile as a dignified tribute', 'vpg-v2' ); ?></label>
    </td></tr></table><?php
}
add_action( 'edit_user_profile_update', 'vpg_memorial_save' );
add_action( 'personal_options_update', 'vpg_memorial_save' );
function vpg_memorial_save( $uid ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    update_user_meta( $uid, '_vpg_memorial', empty( $_POST['vpg_memorial'] ) ? '' : '1' );
}
