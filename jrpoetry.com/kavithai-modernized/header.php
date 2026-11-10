<?php
/**
 * Header — masthead band + nav with seal.
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main">உள்ளடக்கம்</a>

<!-- Masthead band -->
<div class="masthead">
    <?php
    $poem_count_obj = wp_count_posts( 'kavithai_gedicht' );
    $poem_count     = (int) ( $poem_count_obj->publish ?? 0 );
    ?>
    <div class="masthead__left">
        <?php if ( $poem_count > 0 ) : ?>
            Nº <?php echo $poem_count; ?> · <span class="la-italic">Akaval</span>
        <?php else : ?>
            <span class="la-italic">Pavalarmani</span>
        <?php endif; ?>
    </div>
    <div class="masthead__center">க</div>
    <div class="masthead__right">MMXXVI · <?php echo esc_html( kv_location() ); ?></div>
</div>

<!-- Nav -->
<nav class="nav">
    <div class="nav__in">
        <a class="nav__seal" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="முகப்பு">
            <?php echo kv_seal_set(); ?>
        </a>

        <div class="nav__name"><?php echo esc_html( kv_name() ); ?></div>

        <button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="மெனு">☰</button>

        <ul class="nav__links" id="primary-nav" role="list">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => function() {
                    $links = [
                        get_post_type_archive_link( 'kavithai_gedicht' ) => 'கவிதைகள்',
                        get_post_type_archive_link( 'kavithai_nul' )     => 'நூல்கள்',
                        home_url( '/ennai-parri/' )                      => 'என்னை பற்றி',
                        home_url( '/thodarbu/' )                         => 'தொடர்பு',
                    ];
                    foreach ( $links as $url => $label ) {
                        echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
                    }
                },
            ] );
            ?>
        </ul>
    </div>
</nav>

<main id="main">
