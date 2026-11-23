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

    // Google Fonts · one request, three families
    wp_enqueue_style(
        'vpg-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap',
        [],
        VPG_V2_VERSION
    );

    // CSS · modular load order
    wp_enqueue_style( 'vpg-tokens',     VPG_V2_URI . '/assets/css/tokens.css',     [ 'vpg-fonts' ],   $ver( '/assets/css/tokens.css' ) );
    wp_enqueue_style( 'vpg-base',       VPG_V2_URI . '/assets/css/base.css',       [ 'vpg-tokens' ], $ver( '/assets/css/base.css' ) );
    wp_enqueue_style( 'vpg-layout',     VPG_V2_URI . '/assets/css/layout.css',     [ 'vpg-base' ],   $ver( '/assets/css/layout.css' ) );
    wp_enqueue_style( 'vpg-components', VPG_V2_URI . '/assets/css/components.css', [ 'vpg-layout' ], $ver( '/assets/css/components.css' ) );

    // Page-scoped sheets · only enqueue when needed
    if ( is_singular( 'vpg_magazine' ) || is_post_type_archive( 'vpg_magazine' ) ) {
        wp_enqueue_style( 'vpg-magazine', VPG_V2_URI . '/assets/css/pages/magazine.css', [ 'vpg-components' ], $ver( '/assets/css/pages/magazine.css' ) );
    }
    if ( is_post_type_archive( 'vpg_location' ) || is_singular( 'vpg_location' ) || is_singular( 'vpg_studio' ) || is_singular( 'vpg_shop' ) || is_page_template( 'templates/page-map-guide.php' ) ) {
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
