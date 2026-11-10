<?php
/**
 * 404.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap" style="min-height:70vh;display:flex;flex-direction:column;justify-content:center">
	<span class="label"><?php esc_html_e( 'Error 404', 'still' ); ?></span>
	<h1 class="page-title" style="margin-top:1rem"><?php esc_html_e( "This frame doesn't exist.", 'still' ); ?></h1>
	<p class="page-lead"><?php esc_html_e( 'The page you were looking for has moved or was never here.', 'still' ); ?></p>
	<p style="margin-top:2rem"><a class="panel__go" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home →', 'still' ); ?></a></p>
</main>

<?php get_footer(); ?>
