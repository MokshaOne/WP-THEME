<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<meta name="theme-color" content="#0B0B0B" />
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

<a class="g-skip" href="#vpg-main"><?php esc_html_e( 'Skip to content', 'vpg-v2' ); ?></a>

<?php
// Journal = native posts. Prefer an assigned "Posts page", else /journal/.
$vpg_journal_url = get_option( 'page_for_posts' )
    ? get_permalink( (int) get_option( 'page_for_posts' ) )
    : home_url( '/journal/' );
$vpg_here = function_exists( 'vpg_current_url' ) ? vpg_current_url() : '';
?>

<header class="g-top" role="banner">
    <div class="g-top__in">

        <?php if ( has_custom_logo() ) : ?>
            <div class="g-brand"><?php the_custom_logo(); ?></div>
        <?php else : ?>
            <a class="g-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — home"><?php bloginfo( 'name' ); ?><b>.</b></a>
        <?php endif; ?>

        <input type="checkbox" id="vpg-nav-toggle" class="g-navtoggle" tabindex="-1" aria-hidden="true" />

        <?php
        if ( has_nav_menu( 'primary' ) ) {
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'g-nav',
                'depth'          => 1,
            ] );
        } else {
            $vpg_nav = [
                home_url( '/' )                                => __( 'Home', 'vpg-v2' ),
                get_post_type_archive_link( 'vpg_magazine' )   => __( 'Magazine', 'vpg-v2' ),
                get_post_type_archive_link( 'vpg_location' )   => __( 'The Map', 'vpg-v2' ),
                $vpg_journal_url                               => __( 'Journal', 'vpg-v2' ),
                home_url( '/about/' )                          => __( 'About', 'vpg-v2' ),
            ];
            echo '<ul class="g-nav">';
            foreach ( $vpg_nav as $url => $label ) {
                if ( ! $url ) continue;
                $current = ( $vpg_here && untrailingslashit( $url ) === untrailingslashit( $vpg_here ) ) ? ' aria-current="page"' : '';
                echo '<li><a href="' . esc_url( $url ) . '"' . $current . '>' . esc_html( $label ) . '</a></li>';
            }
            echo '</ul>';
        }
        ?>

        <div class="g-top__actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Dashboard', 'vpg-v2' ); ?></a>
            <?php else : ?>
                <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join VPG', 'vpg-v2' ); ?></a>
            <?php endif; ?>
            <button type="button" id="vpg-cmdk-open" class="g-burger" style="display:inline-flex" aria-label="<?php esc_attr_e( 'Search (Cmd+K)', 'vpg-v2' ); ?>">⌕</button>
            <label class="g-burger" for="vpg-nav-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'vpg-v2' ); ?>"><?php esc_html_e( 'Menu', 'vpg-v2' ); ?></label>
        </div>
    </div>
</header>

<div class="g-strip">
    <div class="g-strip__in">
        <span>Wien · <b><?php esc_html_e( 'est. 2019', 'vpg-v2' ); ?></b></span>
        <span class="hide-sm"><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'The quiet magazine for photographers who look', 'vpg-v2' ) ); ?></span>
        <span>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Logout', 'vpg-v2' ); ?></a>
            <?php else : ?>
                <a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Login', 'vpg-v2' ); ?></a>
            <?php endif; ?>
        </span>
    </div>
</div>
