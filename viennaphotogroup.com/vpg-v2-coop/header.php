<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<meta name="theme-color" content="#0F0A04" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="VPG" />
<link rel="manifest" href="<?php echo esc_url( get_template_directory_uri() ); ?>/manifest.json" />
<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/icons/icon-192.png" />

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="vpg-skip" href="#vpg-main"><?php esc_html_e( 'Skip to content', 'vpg-v2' ); ?></a>

<header class="vpg-header" role="banner">
    <div class="vpg-header__in">

        <?php
        // Custom logo · render as its own link (WP wraps it in <a> already, so don't nest).
        // Falls back to the wordmark if no custom logo is set.
        if ( has_custom_logo() ) {
            echo '<div class="vpg-header__logo">';
            the_custom_logo();
            echo '</div>';
        } else {
            ?>
            <a class="vpg-header__brand" href="<?php echo esc_url( home_url('/') ); ?>" rel="home">
                <span>VPG</span><em>·</em><span><?php bloginfo( 'name' ); ?></span>
            </a>
            <?php
        }
        ?>

        <nav role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'vpg-v2' ); ?>">
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'vpg-nav',
                    'depth'          => 1,
                ] );
            } else {
                echo '<ul class="vpg-nav">';
                foreach ( [
                    home_url('/')                                  => __( 'Home',     'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_magazine' )   => __( 'Magazine', 'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_event' )      => __( 'Events',   'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_location' )   => __( 'Map',      'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_studio' )     => __( 'Studios',  'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_shop' )       => __( 'Shops',    'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_review' )     => __( 'Buying',   'vpg-v2' ),
                    get_post_type_archive_link( 'vpg_tutorial' )   => __( 'Tutorials','vpg-v2' ),
                ] as $url => $label ) {
                    if ( ! $url ) continue;
                    echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
                }
                echo '</ul>';
            }
            ?>
        </nav>

        <div class="vpg-header__actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a class="vpg-header__cta vpg-header__cta--ghost" href="<?php echo esc_url( home_url('/dashboard/') ); ?>"><?php esc_html_e( 'Dashboard', 'vpg-v2' ); ?></a>
                <a class="vpg-header__cta" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Logout', 'vpg-v2' ); ?></a>
            <?php else : ?>
                <a class="vpg-header__cta vpg-header__cta--ghost" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login', 'vpg-v2' ); ?></a>
                <a class="vpg-header__cta" href="<?php echo esc_url( home_url('/join/') ); ?>"><?php esc_html_e( 'Join VPG', 'vpg-v2' ); ?> →</a>
            <?php endif; ?>
            <button class="vpg-header__toggle" id="vpg-menu-toggle" aria-controls="vpg-mobile-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'vpg-v2' ); ?>"><?php esc_html_e( 'Menu', 'vpg-v2' ); ?></button>
        </div>
    </div>
</header>
