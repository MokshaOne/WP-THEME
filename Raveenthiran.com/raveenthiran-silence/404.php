<?php
/**
 * 404 — nothing here. Fittingly silent.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<section class="nr-sheet nr-404">
	<div class="nr-sheet__inner">
		<h1>404</h1>
		<p><?php esc_html_e( 'Nothing here — only silence.', 'raveenthiran-silence' ); ?></p>
		<p><a class="nr-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">← <?php esc_html_e( 'Back to the work', 'raveenthiran-silence' ); ?></a></p>
	</div>
</section>

<?php get_footer(); ?>
