<?php
/**
 * on1.agency · v4 · header.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$id = on1_get_identity();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main">Skip to content</a>

<nav class="nav">
    <a class="nav__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $id['name'] ); ?> <sup>®</sup></a>
    <ul class="nav__links" id="primary-nav" role="list">
        <?php
        if ( has_nav_menu( 'primary' ) ) {
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
            ] );
        } else {
            $proj_count = (int) ( wp_count_posts( 'project' )->publish ?? 0 );
            ?>
            <li><a href="#work">Work<?php if ( $proj_count > 0 ) echo '<sup>' . $proj_count . '</sup>'; ?></a></li>
            <li><a href="#practice">Practice</a></li>
            <li><a href="#pricing">Pricing</a></li>
            <li><a href="#faq">FAQ</a></li>
            <li><a href="#brief">Brief</a></li>
            <?php
        }
        ?>
    </ul>
    <div class="nav__cta-row">
        <a href="#brief" class="pill pill--sm pill--accent">
            Let's talk
            <span class="pill__arrow">↗</span>
        </a>
    </div>
</nav>
