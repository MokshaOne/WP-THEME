<?php
/**
 * M1O Hub · header
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$id             = m1o_get_identity();
$show_systembar = m1o_opt( 'm1o_show_systembar', '1' ) !== '0';
$show_leds      = m1o_opt( 'm1o_show_leds', '1' ) !== '0';
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
<body <?php body_class( $show_leds ? '' : 'no-leds' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main">Skip to content</a>

<?php if ( $show_systembar ) :
    $status_label = trim( strtok( $id['status'], '·' ) );
?>
<div class="systembar">
    <div class="systembar__left">M1O.HUB · <span class="signal">●</span> <?php echo esc_html( strtoupper( $status_label ) ); ?></div>
    <div class="systembar__center"><?php echo esc_html( strtoupper( $id['location'] ) ); ?></div>
    <div class="systembar__right"><?php echo esc_html( date( 'd.m.Y' ) ); ?> <span class="cursor"></span></div>
</div>
<?php endif; ?>
