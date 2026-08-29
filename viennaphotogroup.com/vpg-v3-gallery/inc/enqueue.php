<?php
/**
 * VPG v2 — enqueue · CSS + JS bundles, fonts, Leaflet on map pages.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_enqueue_scripts', function () {

    $ver = function ( $rel ) {
        $p = VPG_V2_DIR . $rel;
        return file_exists( $p ) ? (string) filemtime( $p ) : VPG_V2_VERSION;
    };

    // Fonts · self-hosted variable Archivo (assets/fonts/web) — no CDN,
    // no third-party request, preloaded below for a fast first paint.
    wp_enqueue_style( 'vpg-fonts', VPG_V2_URI . '/assets/css/fonts.css', [], $ver( '/assets/css/fonts.css' ) );

    // CSS · modular load order.
    // tokens.css (the design tokens every sheet reads) is inlined into <head>
    // so the first paint has its variables without an extra request.
    wp_register_style( 'vpg-tokens', false, [ 'vpg-fonts' ], $ver( '/assets/css/tokens.css' ) );
    wp_enqueue_style( 'vpg-tokens' );
    $tokens_file = VPG_V2_DIR . '/assets/css/tokens.css';
    if ( file_exists( $tokens_file ) && filesize( $tokens_file ) < 30000 ) {
        wp_add_inline_style( 'vpg-tokens', (string) file_get_contents( $tokens_file ) );
    } else {
        wp_deregister_style( 'vpg-tokens' );
        wp_enqueue_style( 'vpg-tokens', VPG_V2_URI . '/assets/css/tokens.css', [ 'vpg-fonts' ], $ver( '/assets/css/tokens.css' ) );
    }
    wp_enqueue_style( 'vpg-base',       VPG_V2_URI . '/assets/css/base.css',       [ 'vpg-tokens' ], $ver( '/assets/css/base.css' ) );
    wp_enqueue_style( 'vpg-layout',     VPG_V2_URI . '/assets/css/layout.css',     [ 'vpg-base' ],   $ver( '/assets/css/layout.css' ) );
    wp_enqueue_style( 'vpg-components', VPG_V2_URI . '/assets/css/components.css', [ 'vpg-layout' ], $ver( '/assets/css/components.css' ) );

    // Gallery system · chrome (header/footer), front page, and inner-page
    // components. Loaded last/globally so the `g-` classes are available
    // everywhere and win the cascade for the signature screens.
    wp_enqueue_style( 'vpg-gallery', VPG_V2_URI . '/assets/css/gallery.css', [ 'vpg-components' ], $ver( '/assets/css/gallery.css' ) );

    // Page-scoped sheets · only enqueue when needed
    if ( is_singular( 'vpg_magazine' ) || is_post_type_archive( 'vpg_magazine' ) ) {
        wp_enqueue_style( 'vpg-magazine', VPG_V2_URI . '/assets/css/pages/magazine.css', [ 'vpg-components' ], $ver( '/assets/css/pages/magazine.css' ) );
    }
    if ( is_post_type_archive( 'vpg_location' ) || is_singular( 'vpg_location' ) || is_singular( 'vpg_studio' ) || is_singular( 'vpg_shop' ) || is_singular( 'vpg_trail' ) || is_singular( 'vpg_event' ) || is_page_template( 'templates/page-map-guide.php' ) || is_page_template( 'templates/page-submit.php' ) || get_query_var( 'vpg_member' ) ) {
        // Leaflet core
        wp_enqueue_style( 'leaflet',  VPG_V2_URI . '/assets/vendor/leaflet/leaflet.css', [], '1.9.4' );
        wp_enqueue_script( 'leaflet', VPG_V2_URI . '/assets/vendor/leaflet/leaflet.js',  [], '1.9.4', true );

        // Marker clustering plugin (only on the archive / page-map where many pins matter)
        if ( is_post_type_archive( 'vpg_location' ) || is_page_template( 'templates/page-map-guide.php' ) ) {
            wp_enqueue_style( 'leaflet-cluster',         VPG_V2_URI . '/assets/vendor/leaflet/MarkerCluster.css',         [ 'leaflet' ], '1.5.3' );
            wp_enqueue_style( 'leaflet-cluster-default', VPG_V2_URI . '/assets/vendor/leaflet/MarkerCluster.Default.css', [ 'leaflet-cluster' ], '1.5.3' );
            wp_enqueue_script( 'leaflet-cluster',        VPG_V2_URI . '/assets/vendor/leaflet/leaflet.markercluster.js',  [ 'leaflet' ], '1.5.3', true );
        }

        wp_enqueue_style( 'vpg-map', VPG_V2_URI . '/assets/css/pages/map.css', [ 'vpg-components', 'leaflet' ], $ver( '/assets/css/pages/map.css' ) );
        wp_enqueue_script( 'vpg-map-engine', VPG_V2_URI . '/assets/js/map-engine.js', [ 'leaflet' ], $ver( '/assets/js/map-engine.js' ), true );
    }
    if ( is_page_template( 'templates/page-dashboard.php' ) || is_page_template( 'templates/page-dashboard-edit.php' ) ) {
        wp_enqueue_style( 'vpg-dashboard', VPG_V2_URI . '/assets/css/pages/dashboard.css', [ 'vpg-components' ], $ver( '/assets/css/pages/dashboard.css' ) );
    }
    if ( is_page() && ( is_page_template( 'templates/page-contact.php' ) || is_page_template( 'templates/page-submit.php' ) || is_page_template( 'templates/page-join.php' ) || is_page_template( 'templates/page-login.php' ) ) ) {
        wp_enqueue_style( 'vpg-forms', VPG_V2_URI . '/assets/css/pages/forms.css', [ 'vpg-components' ], $ver( '/assets/css/pages/forms.css' ) );
    }

    // Main JS bundle
    wp_enqueue_script( 'vpg-main', VPG_V2_URI . '/assets/js/main.js', [], $ver( '/assets/js/main.js' ), true );
} );

/* Preload the two variable font files · they style every glyph on the page */
add_action( 'wp_head', function () {
    foreach ( [ 'archivo-var-latin.woff2', 'archivo-var-latin-italic.woff2' ] as $f ) {
        if ( file_exists( VPG_V2_DIR . '/assets/fonts/web/' . $f ) ) {
            printf(
                '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
                esc_url( VPG_V2_URI . '/assets/fonts/web/' . $f )
            );
        }
    }
}, 2 );

/* PWA: ensure service-worker is allowed at /-relative scope by passing scope=/ */
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    $sw = VPG_V2_URI . '/assets/js/service-worker.js';
    ?>
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?php echo esc_js( $sw ); ?>', { scope: '/' })
                .catch(function (e) { console.warn('VPG SW:', e); });
        });
    }
    </script>
    <?php
} );
