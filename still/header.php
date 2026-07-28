<?php
/**
 * Head, body open, fixed brand, and — on the front page only — the cinematic
 * intro overlay (boot → terminal → decoded name → scroll cue).
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$still_home_intro = ( is_front_page() && ! is_paged() );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( $still_home_intro ? 'intro' : '' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'still' ); ?></a>

<?php if ( $still_home_intro ) : ?>
<div id="intro" aria-hidden="true">
	<div class="intro__in">
		<div class="boot" id="boot">
			<div class="boot__row"><span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span><span id="pct">0</span></div>
			<div class="bar"><div class="bar__fill" id="fill"></div></div>
		</div>
		<div class="term" id="term"></div>
		<div class="titlewrap" id="titlewrap">
			<h1 class="bigtitle" id="bigtitle" data-text="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		</div>
	</div>
	<div class="enter-big" id="enterbig"><span><?php esc_html_e( 'Scroll', 'still' ); ?></span><span class="chev"></span></div>
</div>
<?php endif; ?>

<a class="brand-fixed" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
