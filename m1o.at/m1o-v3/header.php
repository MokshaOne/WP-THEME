<?php
/**
 * M1O Transmission · header
 * System bar + (front page) preloader gate, grain, cursor layers.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$m1o_id         = m1o_get_identity();
$show_systembar = m1o_opt( 'm1o_show_systembar', '1' ) !== '0';
$status_label   = trim( strtok( $m1o_id['status'], '·' ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'm1o' ); ?></a>

<div class="grain" aria-hidden="true"></div>

<?php if ( m1o_motion_on() ) : ?>
	<div class="glow" id="m1o-glow" aria-hidden="true"></div>
	<div class="dot" id="m1o-dot" aria-hidden="true"></div>
	<?php if ( is_front_page() ) : ?>
	<div class="loader" id="m1o-loader" aria-hidden="true">
		<div class="mark">M1O<em>.</em></div>
		<div class="bar"><i id="m1o-lbar"></i></div>
		<div class="row"><span data-scramble><?php esc_html_e( 'Acquiring signal', 'm1o' ); ?></span><span class="n" id="m1o-lnum">00</span></div>
	</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ( $show_systembar ) : ?>
<div class="sysbar">
	<div><span>M1O.AT</span>&nbsp; <span class="led"></span> <span class="au" data-scramble><?php echo esc_html( strtoupper( $status_label ) ); ?></span></div>
	<div class="mid" data-scramble><?php echo esc_html( $m1o_id['location'] ); ?></div>
	<div class="right"><?php echo esc_html( date_i18n( 'd.m.Y' ) ); ?> <span class="blink">_</span></div>
</div>
<?php endif; ?>
