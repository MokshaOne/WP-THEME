<?php
/**
 * VPG v3 — Cluster 18 · SEO & Reichweite.
 *
 * Complements the existing OG/JSON-LD/sitemap layer (inc/seo.php,
 * inc/platform.php) without duplicating its Organization/Place/Event nodes:
 * breadcrumbs, FAQ and TouristAttraction schema, a per-page meta description,
 * canonical discipline on parameter URLs, a 404 log, a redirect manager,
 * per-type OG cards, full-text RSS with images, short URLs, an llms.txt AI
 * policy, a direct-visit metric, and an SEO desk (descriptions, redirects,
 * evergreen/seasonal/search-console/competitor/ethics/backlink notes).
 *
 *   0682 TouristAttraction · 0684 internal links · 0685 breadcrumb · 0686 titles
 *   0687 meta desc · 0688 404 log · 0689 redirects · 0690 canonical · 0691 FAQ
 *   0693 Person · 0694–0699 outreach docs · 0700 seasonal · 0701 evergreen
 *   0702 intent · 0703 competitor · 0704 CWV · 0706 OG cards · 0707 preview
 *   0708 short URLs · 0709/0710/0716 entity · 0711 RSS · 0712 feed dirs
 *   0713 archive.org · 0714 stable URLs · 0715 search console · 0717 niche dirs
 *   0718 ethics · 0719 AI crawler policy · 0720 direct-visit metric
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Structured data · breadcrumb, FAQ, TouristAttraction, Person      */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_head', function () {
    $nodes = [];

    // 0685 · BreadcrumbList on any singular
    if ( is_singular() ) {
        $items = [ [ 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ] ];
        $pt = get_post_type_object( get_post_type() );
        if ( $pt && $pt->has_archive ) $items[] = [ 'name' => $pt->labels->name, 'url' => get_post_type_archive_link( get_post_type() ) ?: home_url( '/' ) ];
        $items[] = [ 'name' => wp_strip_all_tags( get_the_title() ), 'url' => get_permalink() ];
        $el = [];
        foreach ( $items as $i => $it ) $el[] = [ '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['name'], 'item' => $it['url'] ];
        $nodes[] = [ '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $el ];
    }

    // 0682 · TouristAttraction on a location
    if ( is_singular( [ 'vpg_location' ] ) ) {
        $c = function_exists( 'vpg_get_coords' ) ? vpg_get_coords( get_the_ID() ) : null;
        $n = [ '@context' => 'https://schema.org', '@type' => 'TouristAttraction', 'name' => wp_strip_all_tags( get_the_title() ), 'url' => get_permalink(), 'description' => wp_strip_all_tags( get_the_excerpt() ) ];
        if ( $c ) $n['geo'] = [ '@type' => 'GeoCoordinates', 'latitude' => $c[0], 'longitude' => $c[1] ];
        if ( has_post_thumbnail() ) $n['image'] = get_the_post_thumbnail_url( get_the_ID(), 'large' );
        $nodes[] = $n;
    }

    // 0691 · FAQPage on the glossary / Q&A archive
    if ( is_page_template( 'templates/page-glossary.php' ) && function_exists( 'vpg_glossary_terms' ) ) {
        $qa = [];
        foreach ( array_slice( (array) vpg_glossary_terms(), 0, 30 ) as $term => $def ) $qa[] = [ '@type' => 'Question', 'name' => $term, 'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $def ] ];
        if ( $qa ) $nodes[] = [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $qa ];
    }

    // 0693 · Person on a member profile page (/members/{slug}/)
    if ( get_query_var( 'vpg_member' ) ) {
        $u = get_user_by( 'slug', sanitize_user( get_query_var( 'vpg_member' ) ) ) ?: get_user_by( 'login', sanitize_user( get_query_var( 'vpg_member' ) ) );
        if ( $u ) {
            $links = array_values( array_filter( (array) get_user_meta( $u->ID, '_vpg_links', true ) ) );
            if ( $u->user_url ) $links[] = $u->user_url;
            $nodes[] = array_filter( [ '@context' => 'https://schema.org', '@type' => 'Person', 'name' => $u->display_name, 'url' => home_url( '/members/' . $u->user_nicename . '/' ), 'description' => $u->description, 'sameAs' => $links ?: null, 'memberOf' => [ '@type' => 'Organization', 'name' => 'Vienna Photo Group' ] ] );
        }
    }

    foreach ( $nodes as $n ) echo '<script type="application/ld+json">' . wp_json_encode( $n, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 6 );

/* 0687 · meta description (per-post field, else excerpt) — only if none yet */
add_action( 'wp_head', function () {
    $desc = '';
    if ( is_singular() ) $desc = get_post_meta( get_the_ID(), '_vpg_meta_desc', true ) ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 30 );
    elseif ( is_front_page() ) $desc = get_bloginfo( 'description' );
    if ( $desc ) echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
}, 2 );

/* 0687 admin · meta description field */
add_action( 'add_meta_boxes', function () {
    foreach ( [ 'post', 'page', 'vpg_location', 'vpg_magazine', 'vpg_trail', 'vpg_event' ] as $pt ) {
        add_meta_box( 'vpg-metadesc', '🔎 ' . __( 'Meta description', 'vpg-v2' ), function ( $post ) {
            wp_nonce_field( 'vpg_metadesc', 'vpg_metadesc_nonce' );
            echo '<textarea name="vpg_meta_desc" rows="2" style="width:100%" maxlength="180">' . esc_textarea( get_post_meta( $post->ID, '_vpg_meta_desc', true ) ) . '</textarea><p class="description">' . esc_html__( '≤160 chars, click-honest. Leave blank to auto-generate.', 'vpg-v2' ) . '</p>';
        }, $pt, 'side' );
    }
} );
add_action( 'save_post', function ( $id ) {
    if ( ! isset( $_POST['vpg_metadesc_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_metadesc_nonce'], 'vpg_metadesc' ) ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;
    $v = sanitize_text_field( wp_unslash( $_POST['vpg_meta_desc'] ?? '' ) );
    $v !== '' ? update_post_meta( $id, '_vpg_meta_desc', $v ) : delete_post_meta( $id, '_vpg_meta_desc' );
} );

/* 0690 · canonical discipline — strip tracking params on canonical */
add_filter( 'get_canonical_url', function ( $url ) {
    return $url ? remove_query_arg( [ 'utm_source', 'utm_medium', 'utm_campaign', 'fbclid', 'gclid', 'ref' ], $url ) : $url;
} );

/* 0706 · per-type OG image (falls back to type cover generators) */
add_filter( 'vpg_og_image', function ( $img ) {
    if ( is_singular( 'vpg_trail' ) ) return home_url( '/print/cover/' . get_the_ID() . '/' );
    return $img;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0688 404 log · 0689 redirects · 0720 direct-visit metric          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'template_redirect', function () {
    // 0689 redirects (checked before 404 logging)
    $rules = (array) get_option( 'vpg_redirects', [] );
    if ( $rules ) {
        $path = untrailingslashit( parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) );
        if ( isset( $rules[ $path ] ) ) { wp_redirect( $rules[ $path ], 301 ); exit; }
    }
    // These two write to the DB, so throttle them to at most once per IP per
    // minute — a hammering bot (404s especially) can’t turn a pageview into a
    // per-hit option write, and the metric stays good enough for a small site.
    $ip_gate = 'vpg_seo_gate_' . md5( $_SERVER['REMOTE_ADDR'] ?? '0' );
    if ( is_404() ) {
        $uri = sanitize_text_field( $_SERVER['REQUEST_URI'] ?? '' );
        if ( $uri && strlen( $uri ) < 200 && ! get_transient( $ip_gate . '_404' ) ) {
            set_transient( $ip_gate . '_404', 1, MINUTE_IN_SECONDS );
            $log = (array) get_option( 'vpg_404_log', [] );
            $log[ $uri ] = (int) ( $log[ $uri ] ?? 0 ) + 1;
            if ( count( $log ) > 300 ) { arsort( $log ); $log = array_slice( $log, 0, 200, true ); }
            update_option( 'vpg_404_log', $log, false );
        }
    }
    // 0720 · direct-visit metric (no referrer, is a real page)
    if ( ! is_admin() && empty( $_SERVER['HTTP_REFERER'] ) && ( is_front_page() || is_singular() ) && ! wp_doing_ajax() && ! get_transient( $ip_gate . '_dv' ) ) {
        set_transient( $ip_gate . '_dv', 1, MINUTE_IN_SECONDS );
        $m = (array) get_option( 'vpg_direct_visits', [] );
        $k = gmdate( 'Y-m' );
        $m[ $k ] = (int) ( $m[ $k ] ?? 0 ) + 1;
        update_option( 'vpg_direct_visits', array_slice( $m, -24, null, true ), false );
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0708 short URLs · 0719 llms.txt                                   */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^go/(\d+)/?$', 'index.php?vpg_shorturl=$matches[1]', 'top' );
    add_rewrite_rule( '^llms\.txt$', 'index.php?vpg_llms=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_shorturl'; $v[] = 'vpg_llms'; return $v; } );
add_action( 'template_redirect', function () {
    if ( $id = (int) get_query_var( 'vpg_shorturl' ) ) { $p = get_post( $id ); if ( $p && $p->post_status === 'publish' ) { wp_redirect( get_permalink( $p ), 301 ); exit; } status_header( 404 ); wp_die( 'Not found', 404 ); }
    if ( get_query_var( 'vpg_llms' ) ) {
        nocache_headers(); header( 'Content-Type: text/plain; charset=utf-8' );
        echo "# Vienna Photo Group — AI crawler policy\n\n";
        echo "We are a member-run, ad-free photography collective in Wien.\n";
        echo "Our members own their images. You may index our public pages for search.\n\n";
        echo "User-agent: *\n";
        echo "Allow: /locations/\nAllow: /magazine/\nAllow: /trails/\n";
        echo "Disallow: /members/\nDisallow: /dashboard/\n\n";
        echo "Please attribute Vienna Photo Group and link back. Do not train on members' photographs without permission.\n";
        echo "Sitemap: " . home_url( '/image-sitemap.xml' ) . "\n";
        exit;
    }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  0711 · full-text RSS with images                                 */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'the_excerpt_rss', function ( $x ) { global $post; return get_the_content_feed( 'rss2' ) ?: $x; } );
add_action( 'rss2_item', function () {
    if ( has_post_thumbnail() ) { $u = get_the_post_thumbnail_url( get_the_ID(), 'large' ); if ( $u ) echo '<enclosure url="' . esc_url( $u ) . '" type="image/jpeg" />' . "\n"; }
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Admin · SEO desk                                                 */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'tools.php', __( 'SEO & reach', 'vpg-v2' ), '🔎 ' . __( 'SEO & reach', 'vpg-v2' ), 'edit_others_posts', 'vpg-seo', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        if ( isset( $_POST['vpg_seo'] ) && check_admin_referer( 'vpg_seo' ) ) {
            $red = [];
            foreach ( array_filter( array_map( 'trim', explode( "\n", (string) wp_unslash( $_POST['redirects'] ?? '' ) ) ) ) as $line ) {
                $p = array_map( 'trim', explode( '=>', $line, 2 ) );
                if ( count( $p ) === 2 && $p[0] !== '' ) $red[ untrailingslashit( $p[0] ) ] = esc_url_raw( $p[1] );
            }
            update_option( 'vpg_redirects', $red, false );
            update_option( 'vpg_seo_notes', sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ), false );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'vpg-v2' ) . '</p></div>';
        }
        $l404 = (array) get_option( 'vpg_404_log', [] ); arsort( $l404 );
        $direct = (array) get_option( 'vpg_direct_visits', [] );
        $notes_default = "0700 seasonal: Christmas-market guide online by October\n0701 evergreen: refresh top pages yearly\n0702 intent: each core page names its target question\n0703 competitor: what others do better\n0704 CWV: watch field values, not just lab\n0709/0716 entity: name/address/profiles identical everywhere\n0712 feed dirs: listed in Feedly & co.\n0713 archive.org: Wayback snapshots kept\n0715 search console: monthly checklist\n0717 niche dirs: photo-community lists claimed\n0718 ethics: no clickbait — editorial rule\n0694/0695 Wikipedia/Wikimedia: relevance path + free-licence donations\n0696/0697/0698/0699 outreach: backlinks, guest spots, newsletter swaps, local press";
        ?>
        <div class="wrap"><h1>🔎 <?php esc_html_e( 'SEO & reach', 'vpg-v2' ); ?></h1>
          <p><strong><?php echo (int) array_sum( $direct ); ?></strong> <?php esc_html_e( 'direct visits tracked (0720 — the real goal). llms.txt:', 'vpg-v2' ); ?> <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank">/llms.txt</a></p>
          <div style="display:flex;gap:40px;flex-wrap:wrap">
            <div><h2><?php esc_html_e( '0688 · Top 404s', 'vpg-v2' ); ?></h2><ol style="max-width:420px"><?php $i = 0; foreach ( $l404 as $u => $n ) { if ( $i++ > 20 ) break; echo '<li><code>' . esc_html( $u ) . '</code> — ' . (int) $n . '</li>'; } ?></ol></div>
            <div style="flex:1;min-width:300px"><h2><?php esc_html_e( '0720 · Direct visits / month', 'vpg-v2' ); ?></h2><ul><?php foreach ( array_reverse( $direct, true ) as $m => $n ) echo '<li>' . esc_html( $m ) . ' — ' . (int) $n . '</li>'; ?></ul></div>
          </div>
          <form method="post"><?php wp_nonce_field( 'vpg_seo' ); ?>
            <h2><?php esc_html_e( '0689 · Redirects (one per line: /old-path => https://…/new)', 'vpg-v2' ); ?></h2>
            <textarea name="redirects" rows="5" style="width:100%;max-width:760px;font-family:monospace"><?php echo esc_textarea( implode( "\n", array_map( fn( $k, $v ) => $k . ' => ' . $v, array_keys( (array) get_option( 'vpg_redirects', [] ) ), (array) get_option( 'vpg_redirects', [] ) ) ) ); ?></textarea>
            <h2><?php esc_html_e( 'Reach checklist & outreach notes', 'vpg-v2' ); ?></h2>
            <textarea name="notes" rows="12" style="width:100%;max-width:760px;font-family:monospace"><?php echo esc_textarea( get_option( 'vpg_seo_notes', $notes_default ) ); ?></textarea>
            <p><button name="vpg_seo" class="button button-primary"><?php esc_html_e( 'Save', 'vpg-v2' ); ?></button></p>
          </form>
          <p class="description"><?php esc_html_e( 'Title formulas (0686): “{Title} · {Type} — Vienna Photo Group”. Preview cards at opengraph.xyz before publishing (0707).', 'vpg-v2' ); ?></p>
        </div>
        <?php
    } );
} );
