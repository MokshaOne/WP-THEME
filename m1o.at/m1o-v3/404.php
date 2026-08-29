<?php
/**
 * M1O Transmission · 404
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="main" class="page-shell">
	<div class="eyebrow">// <?php esc_html_e( 'Signal lost', 'm1o' ); ?></div>
	<div class="err-code" aria-hidden="true">404</div>
	<h1><?php esc_html_e( 'No transmission on this frequency', 'm1o' ); ?><em>.</em></h1>
	<div class="prose">
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">&#8592; <?php esc_html_e( 'Back to the console', 'm1o' ); ?></a></p>
	</div>
</main>

<?php get_footer(); ?>
