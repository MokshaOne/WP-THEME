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
	<?php if ( $still_home_intro ) : ?>
	<script>
	/* Intro fail-safe: never trap the visitor behind a black overlay.
	   main.js clears this timer when it initialises; if the script is missing
	   or stale, the intro is revealed-away after a few seconds instead. */
	document.documentElement.className += ' js';
	window.__stillFail = setTimeout(function () { document.documentElement.className += ' still-fallback'; }, 6000);
	</script>
	<noscript><style>#intro{display:none!important}body.intro #home{position:relative!important;transform:none!important;height:auto!important;overflow:visible!important}body.intro,body.intro.entered{overflow:auto!important}</style></noscript>
	<?php endif; ?>
	<?php wp_head(); ?>
</head>
<body <?php body_class( $still_home_intro ? 'intro' : '' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'still' ); ?></a>

<?php if ( $still_home_intro ) :
	$still_intro_vid = function_exists( 'still_intro_video' ) ? still_intro_video() : '';
	?>
<div id="intro" aria-hidden="true"<?php echo $still_intro_vid ? ' data-mode="video"' : ''; ?>>
	<?php if ( $still_intro_vid ) : ?>
		<video id="introvid" class="intro-vid" autoplay muted playsinline preload="auto">
			<source src="<?php echo esc_url( $still_intro_vid ); ?>">
		</video>
		<div class="intro-veil" aria-hidden="true"></div>
		<div class="titlewrap show decoded" id="titlewrap">
			<h1 class="bigtitle" id="bigtitle" data-text="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		</div>
	<?php else : ?>
		<div class="intro__in">
			<div class="boot" id="boot">
				<div class="boot__row"><span><?php esc_html_e( 'Initializing', 'still' ); ?></span><span id="pct">0%</span></div>
				<div class="bar"><div class="bar__fill" id="fill"></div></div>
			</div>
			<div class="term" id="term"></div>
			<div class="titlewrap" id="titlewrap">
				<h1 class="bigtitle" id="bigtitle" data-text="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			</div>
		</div>
	<?php endif; ?>
	<div class="enter-big" id="enterbig"><span><?php esc_html_e( 'Scroll right', 'still' ); ?></span><span class="chev"></span></div>
</div>
<?php endif; ?>

<a class="brand-fixed" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
