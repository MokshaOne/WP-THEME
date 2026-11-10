<?php
/**
 * VPG v3 — Platform · trust and plumbing.
 *
 *   - Privacy analytics · Plausible or Matomo (self-hosted), configured in
 *     the Customizer; join-funnel events fired without cookies
 *   - schema.org JSON-LD · Organization, Article, Event, Place
 *   - XML sitemap pointer in robots.txt (WP core sitemap does the rest)
 *   - Consent-free embeds · youtube → youtube-nocookie, lazy iframes
 *   - REST hardening · gated CPTs and the users endpoint need a login
 *   - DE/EN groundwork · ?lang= switcher via cookie once a .mo exists
 *   - WP-CLI · wp vpg <digest|flush-pins|unverified>
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Privacy analytics · Plausible / Matomo, no cookies, no consent    */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'customize_register', function ( $wp_customize ) {
    $wp_customize->add_setting( 'vpg_analytics_kind', [ 'default' => '', 'sanitize_callback' => function ( $v ) {
        return in_array( $v, [ '', 'plausible', 'matomo' ], true ) ? $v : '';
    } ] );
    $wp_customize->add_setting( 'vpg_analytics_url',  [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
    $wp_customize->add_setting( 'vpg_analytics_site', [ 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ] );

    $wp_customize->add_section( 'vpg_analytics', [ 'title' => __( 'VPG · Analytics', 'vpg-v2' ), 'priority' => 200 ] );
    $wp_customize->add_control( 'vpg_analytics_kind', [ 'section' => 'vpg_analytics', 'label' => __( 'Provider (self-hosted)', 'vpg-v2' ), 'type' => 'select', 'choices' => [ '' => __( 'Off', 'vpg-v2' ), 'plausible' => 'Plausible', 'matomo' => 'Matomo' ] ] );
    $wp_customize->add_control( 'vpg_analytics_url',  [ 'section' => 'vpg_analytics', 'label' => __( 'Instance URL', 'vpg-v2' ), 'type' => 'url' ] );
    $wp_customize->add_control( 'vpg_analytics_site', [ 'section' => 'vpg_analytics', 'label' => __( 'Site domain / Matomo site id', 'vpg-v2' ), 'type' => 'text' ] );
} );

add_action( 'wp_footer', function () {
    if ( is_user_logged_in() && current_user_can( 'edit_others_posts' ) ) return; // don't count editorial
    $kind = get_theme_mod( 'vpg_analytics_kind', '' );
    $url  = rtrim( (string) get_theme_mod( 'vpg_analytics_url', '' ), '/' );
    $site = (string) get_theme_mod( 'vpg_analytics_site', '' );
    if ( ! $kind || ! $url || ! $site ) return;

    if ( $kind === 'plausible' ) {
        printf(
            '<script defer data-domain="%s" src="%s/js/script.tagged-events.js"></script>' . "\n",
            esc_attr( $site ), esc_url( $url )
        );
    } else {
        ?>
        <script>
        var _paq = window._paq = window._paq || [];
        _paq.push(['disableCookies']); _paq.push(['trackPageView']); _paq.push(['enableLinkTracking']);
        (function() {
            var u = <?php echo wp_json_encode( $url . '/' ); ?>;
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', <?php echo wp_json_encode( $site ); ?>]);
            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true; g.src = u + 'matomo.js'; s.parentNode.insertBefore(g, s);
        })();
        </script>
        <?php
    }

    // Join-funnel events · works for both providers via a tiny shim
    ?>
    <script>
    (function () {
        function track(name) {
            if (window.plausible) window.plausible(name);
            if (window._paq) window._paq.push(['trackEvent', 'funnel', name]);
        }
        var status = new URLSearchParams(window.location.search).get('vpg_status');
        if (status === 'welcome')  track('join-completed');
        if (status === 'verified') track('email-verified');
        if (status === 'ok' && window.location.pathname.indexOf('/submit') !== -1) track('submission-sent');
        document.querySelectorAll('a[href*="/join"]').forEach(function (a) {
            a.addEventListener('click', function () { track('join-click'); });
        });
    }());
    </script>
    <?php
}, 20 );

/* ════════════════════════════════════════════════════════════════ */
/*  schema.org JSON-LD                                                */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_head', function () {
    if ( defined( 'VPG_DISABLE_SEO' ) && VPG_DISABLE_SEO ) return;
    $data = null;

    if ( is_front_page() ) {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url( '/' ),
            'description' => get_bloginfo( 'description' ),
        ];
    } elseif ( is_singular( [ 'post', 'vpg_tutorial', 'vpg_review' ] ) ) {
        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => 'Article',
            'headline'      => get_the_title(),
            'datePublished' => get_the_date( 'c' ),
            'dateModified'  => get_the_modified_date( 'c' ),
            'author'        => [ '@type' => 'Person', 'name' => get_the_author() ],
            'publisher'     => [ '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ],
            'mainEntityOfPage' => get_permalink(),
        ];
        if ( has_post_thumbnail() ) $data['image'] = get_the_post_thumbnail_url( null, 'large' );
    } elseif ( is_singular( 'vpg_event' ) ) {
        $date  = get_post_meta( get_the_ID(), '_vpg_event_date', true );
        $venue = get_post_meta( get_the_ID(), '_vpg_event_venue', true );
        $data  = [
            '@context'  => 'https://schema.org',
            '@type'     => 'Event',
            'name'      => get_the_title(),
            'url'       => get_permalink(),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        ];
        if ( $date )  $data['startDate'] = gmdate( 'Y-m-d', strtotime( $date ) ?: time() );
        if ( $venue ) $data['location']  = [ '@type' => 'Place', 'name' => $venue, 'address' => 'Wien, AT' ];
    } elseif ( is_singular( [ 'vpg_location', 'vpg_studio', 'vpg_shop' ] ) ) {
        $coords = function_exists( 'vpg_get_coords' ) ? vpg_get_coords( get_the_ID() ) : null;
        $data   = [
            '@context' => 'https://schema.org',
            '@type'    => 'Place',
            'name'     => get_the_title(),
            'url'      => get_permalink(),
        ];
        if ( $coords ) $data['geo'] = [ '@type' => 'GeoCoordinates', 'latitude' => $coords[0], 'longitude' => $coords[1] ];
    }

    if ( $data ) {
        echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
}, 6 );

/* ════════════════════════════════════════════════════════════════ */
/*  Sitemap pointer · WP core serves /wp-sitemap.xml since 5.5        */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'robots_txt', function ( $output ) {
    if ( strpos( $output, 'Sitemap:' ) === false ) {
        $output .= "\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
    }
    return $output;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Consent-free embeds · youtube → nocookie, iframes lazy            */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'the_content', function ( $content ) {
    if ( strpos( $content, 'youtube' ) !== false ) {
        $content = str_replace(
            [ 'www.youtube.com/embed/', 'youtube.com/embed/' ],
            [ 'www.youtube-nocookie.com/embed/', 'www.youtube-nocookie.com/embed/' ],
            $content
        );
    }
    if ( strpos( $content, '<iframe' ) !== false && strpos( $content, 'loading=' ) === false ) {
        $content = str_replace( '<iframe ', '<iframe loading="lazy" ', $content );
    }
    return $content;
}, 20 );

/* ════════════════════════════════════════════════════════════════ */
/*  REST hardening                                                    */
/* ════════════════════════════════════════════════════════════════ */
add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
    if ( is_user_logged_in() ) return $result;
    $route = $request->get_route();

    // Users endpoint · no member enumeration for guests
    if ( preg_match( '#^/wp/v2/users#', $route ) ) {
        return new WP_Error( 'rest_forbidden', __( 'Members only.', 'vpg-v2' ), [ 'status' => 401 ] );
    }

    // Gated CPTs stay gated in REST too
    $gated = function_exists( 'vpg_gated_cpts' ) ? vpg_gated_cpts() : [];
    foreach ( $gated as $cpt ) {
        $obj  = get_post_type_object( $cpt );
        $base = $obj && ! empty( $obj->rest_base ) ? $obj->rest_base : $cpt;
        if ( preg_match( '#^/wp/v2/' . preg_quote( $base, '#' ) . '(/|$)#', $route ) ) {
            return new WP_Error( 'rest_forbidden', __( 'Members only.', 'vpg-v2' ), [ 'status' => 401 ] );
        }
    }
    return $result;
}, 10, 3 );

/* ════════════════════════════════════════════════════════════════ */
/*  DE/EN groundwork · cookie-based locale switch (?lang=de|en)       */
/*  Takes effect for strings that have a translation file in          */
/*  /languages (see languages/README.md for the workflow).            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    if ( isset( $_GET['lang'] ) ) {
        $lang = in_array( $_GET['lang'], [ 'de', 'en' ], true ) ? $_GET['lang'] : 'en';
        setcookie( 'vpg_lang', $lang, time() + YEAR_IN_SECONDS, '/', '', is_ssl(), true );
        $_COOKIE['vpg_lang'] = $lang;
    }
}, 1 );

add_filter( 'locale', function ( $locale ) {
    if ( is_admin() ) return $locale;
    $lang = $_COOKIE['vpg_lang'] ?? '';
    if ( $lang === 'de' ) return 'de_AT';
    if ( $lang === 'en' ) return 'en_US';
    return $locale;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  WP-CLI · wp vpg <command>                                         */
/* ════════════════════════════════════════════════════════════════ */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'vpg', function ( $args ) {
        $cmd = $args[0] ?? '';
        switch ( $cmd ) {
            case 'digest':
                do_action( 'vpg_monthly_digest' );
                WP_CLI::success( 'Monthly digest triggered.' );
                break;
            case 'flush-pins':
                delete_transient( 'vpg_location_pins_v2' );
                delete_transient( 'vpg_location_districts' );
                WP_CLI::success( 'Map pin caches flushed.' );
                break;
            case 'unverified':
                $users = get_users( [ 'meta_key' => '_vpg_email_verified', 'meta_value' => '0' ] );
                foreach ( $users as $u ) WP_CLI::line( $u->ID . "\t" . $u->user_email . "\t" . $u->user_registered );
                WP_CLI::success( count( $users ) . ' unverified member(s).' );
                break;
            default:
                WP_CLI::line( 'Usage: wp vpg <digest|flush-pins|unverified>' );
        }
    } );
}

/* ════════════════════════════════════════════════════════════════ */
/*  Faceted search · ?s= plus type / year / district filters          */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;

    $type = sanitize_key( $_GET['stype'] ?? '' );
    $ok   = [ 'post', 'vpg_magazine', 'vpg_event', 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'vpg_trail' ];
    if ( $type && in_array( $type, $ok, true ) ) {
        $q->set( 'post_type', $type );
    }

    $year = (int) ( $_GET['syear'] ?? 0 );
    if ( $year > 2000 && $year < 2100 ) {
        $q->set( 'year', $year );
    }

    $district = sanitize_text_field( wp_unslash( $_GET['sdistrict'] ?? '' ) );
    if ( $district ) {
        $q->set( 'meta_query', [
            'relation' => 'OR',
            [ 'key' => 'location_district', 'value' => $district, 'compare' => 'LIKE' ],
            [ 'key' => 'shop_district',     'value' => $district, 'compare' => 'LIKE' ],
        ] );
    }
}, 20 );
