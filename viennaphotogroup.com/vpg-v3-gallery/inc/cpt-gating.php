<?php
/**
 * VPG v2 — CPT-level gating.
 *
 * Certain CPTs are visible to logged-in users only · guests get a member-
 * gate page that shows the title + a teaser excerpt + a log-in CTA, rather
 * than a 404 or a redirect. This way the gated URLs still look reachable
 * (good for SEO + sharing) but the body is locked behind login.
 *
 * Gated CPT list lives in `vpg_gated_cpts()` · add/remove there to change
 * what gets blocked. Archives + single-post pages of these CPTs are gated.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Which CPTs require login to view full content + archive */
function vpg_gated_cpts() {
    /**
     * Override via:
     *   add_filter( 'vpg_gated_cpts', function ( $list ) { return $list; } );
     */
    return (array) apply_filters( 'vpg_gated_cpts', [
        'vpg_magazine',
        'vpg_review',
        'vpg_tutorial',
        'vpg_event',
    ] );
}

/* Hide gated CPTs from REST + search for guests */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || is_user_logged_in() ) return;
    if ( ! $q->is_main_query() ) return;
    if ( $q->is_search() ) {
        // Remove gated CPTs from the search post_type list
        $types = (array) $q->get( 'post_type' );
        if ( ! $types ) $types = [ 'post', 'page' ];
        $types = array_diff( $types, vpg_gated_cpts() );
        $q->set( 'post_type', $types );
    }
} );

/* Hide gated entries from front-page widgets · this filters get_posts() too */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || is_user_logged_in() ) return;
    // Only act on secondary queries that explicitly ask for a gated CPT
    $type = $q->get( 'post_type' );
    if ( is_string( $type ) && in_array( $type, vpg_gated_cpts(), true ) && ! $q->is_main_query() ) {
        // Don't outright kill them · front-page hero shows cover + title, but
        // body is gated when user clicks through. Leave secondary queries alone.
    }
} );

/* Replace the template for gated CPT views with our member-gate */
add_action( 'template_redirect', function () {
    if ( is_user_logged_in() ) return;

    $gated   = vpg_gated_cpts();
    $is_gate = false;
    $title   = '';
    $excerpt = '';
    $type    = '';

    if ( is_singular( $gated ) ) {
        $is_gate = true;
        $title   = get_the_title();
        $excerpt = wp_trim_words( get_the_excerpt(), 32 );
        $type    = get_post_type();
    } elseif ( is_post_type_archive( $gated ) ) {
        $is_gate = true;
        $type    = get_query_var( 'post_type' );
        $labels  = [
            'vpg_magazine' => [ __( 'Magazine',     'vpg-v2' ), __( 'Every monthly issue of the VPG editorial magazine — cover to colophon.', 'vpg-v2' ) ],
            'vpg_review'   => [ __( 'Buying guide', 'vpg-v2' ), __( 'Gear reviews scored on Design, Performance, and Price by members who actually own the kit.', 'vpg-v2' ) ],
            'vpg_tutorial' => [ __( 'Tutorials',    'vpg-v2' ), __( 'Member-written walkthroughs for lighting, develop, scan, archive, print.', 'vpg-v2' ) ],
            'vpg_event'    => [ __( 'Events',       'vpg-v2' ), __( 'Workshops, walks, openings, meetups across Vienna.', 'vpg-v2' ) ],
        ];
        $title   = $labels[ $type ][0] ?? '';
        $excerpt = $labels[ $type ][1] ?? '';
    }

    if ( ! $is_gate ) return;

    vpg_render_gate_page( $type, $title, $excerpt );
    exit;
} );

/* Render the gate page (in the theme chrome · header + footer wrap it) */
function vpg_render_gate_page( $type, $title, $excerpt ) {
    $chip_map = [
        'vpg_magazine' => [ 'mag',    __( 'Magazine',     'vpg-v2' ) ],
        'vpg_review'   => [ 'review', __( 'Buying guide', 'vpg-v2' ) ],
        'vpg_tutorial' => [ 'tut',    __( 'Tutorial',     'vpg-v2' ) ],
        'vpg_event'    => [ 'event',  __( 'Event',        'vpg-v2' ) ],
    ];
    list( $cls, $chip_label ) = $chip_map[ $type ] ?? [ 'member', __( 'Member', 'vpg-v2' ) ];
    $login_url = wp_login_url( home_url( $_SERVER['REQUEST_URI'] ?? '/' ) );

    get_header();
    ?>
    <main id="vpg-main">

        <header class="vpg-page-hero">
            <span class="vpg-chip vpg-chip--<?php echo esc_attr( $cls ); ?>"><span class="vpg-chip__dot"></span> <?php echo esc_html( $chip_label ); ?></span>
            <h1><?php echo esc_html( $title ); ?></h1>
            <?php if ( $excerpt ) : ?>
                <p class="vpg-lede"><?php echo esc_html( $excerpt ); ?></p>
            <?php endif; ?>
        </header>

        <span class="vpg-asterism vpg-asterism--mark"></span>

        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap--narrow">
                <div class="vpg-gate vpg-gate--blocked" style="padding:var(--vpg-sp-9) var(--vpg-sp-6);min-height:auto">
                    <div class="vpg-gate__overlay" style="max-width:48ch">
                        <p class="vpg-caps">— <?php esc_html_e( 'Members only', 'vpg-v2' ); ?></p>
                        <p class="vpg-gate__msg" style="font-size:var(--vpg-fs-xl);max-width:none">
                            <?php esc_html_e( 'VPG is in open beta. The magazine, buying guide, tutorials and events are reserved for logged-in members while we finalise the platform.', 'vpg-v2' ); ?>
                        </p>
                        <p style="margin:1rem 0 2rem;color:var(--vpg-muted);font-size:var(--vpg-fs-md)">
                            <?php esc_html_e( 'The location index, studios and shops stay public — explore the map while you wait.', 'vpg-v2' ); ?>
                        </p>
                        <p class="vpg-gate__actions">
                            <a class="vpg-btn vpg-btn--accent" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Log in', 'vpg-v2' ); ?> →</a>
                            <a class="vpg-btn vpg-btn--ghost" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- What's still open · public-content teaser -->
        <section class="vpg-section vpg-section--surface vpg-section--tight">
            <div class="vpg-wrap">
                <div class="vpg-section-head">
                    <div>
                        <p class="vpg-caps">— <?php esc_html_e( 'Open during beta', 'vpg-v2' ); ?></p>
                        <h2 style="font-size:var(--vpg-fs-h3)"><?php esc_html_e( 'Explore these', 'vpg-v2' ); ?> <em><?php esc_html_e( 'while you wait', 'vpg-v2' ); ?></em>.</h2>
                    </div>
                </div>
                <div class="vpg-grid vpg-grid--3">
                    <a class="vpg-card" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_location' ) ); ?>">
                        <span class="vpg-chip vpg-chip--loc"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Locations', 'vpg-v2' ); ?></span>
                        <h3 style="margin-top:.6rem"><?php esc_html_e( 'The map', 'vpg-v2' ); ?></h3>
                        <p class="vpg-card__lede"><?php esc_html_e( 'Curated shooting spots across Wien.', 'vpg-v2' ); ?></p>
                    </a>
                    <a class="vpg-card" href="<?php echo esc_url( add_query_arg( 'type', 'studio', get_post_type_archive_link( 'vpg_location' ) ) ); ?>">
                        <span class="vpg-chip vpg-chip--studio"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Studios', 'vpg-v2' ); ?></span>
                        <h3 style="margin-top:.6rem"><?php esc_html_e( 'Studio directory', 'vpg-v2' ); ?></h3>
                        <p class="vpg-card__lede"><?php esc_html_e( 'Rental studios + studio-share.', 'vpg-v2' ); ?></p>
                    </a>
                    <a class="vpg-card" href="<?php echo esc_url( add_query_arg( 'type', 'shop', get_post_type_archive_link( 'vpg_location' ) ) ); ?>">
                        <span class="vpg-chip vpg-chip--shop"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Shops', 'vpg-v2' ); ?></span>
                        <h3 style="margin-top:.6rem"><?php esc_html_e( 'Shop guide', 'vpg-v2' ); ?></h3>
                        <p class="vpg-card__lede"><?php esc_html_e( 'Cameras, film, labs.', 'vpg-v2' ); ?></p>
                    </a>
                </div>
            </div>
        </section>

    </main>
    <?php
    get_footer();
}

/* ─── front-page · still shows magazine COVER + title (no body) ────
   So the homepage stays alive looking even for guests · only the
   click-through to the single page hits the gate. */
