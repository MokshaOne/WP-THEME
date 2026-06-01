<?php
/**
 * Template Name: Datenschutz
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<h1><?php esc_html_e( 'Datenschutz', 'raveenthiran' ); ?></h1>

		<?php if ( have_posts() ) : the_post(); the_content(); else : ?>

		<h2><?php esc_html_e( '1. Verantwortlicher', 'raveenthiran' ); ?></h2>
		<p>
			<?php echo esc_html( get_bloginfo( 'name' ) ); ?><br>
			<?php echo esc_html( nr_opt( 'nr_studio', 'Gumpendorfer Str. 81, 1060 Wien' ) ); ?><br>
			<?php echo esc_html( nr_opt( 'nr_email', 'studio@raveenthiran.at' ) ); ?>
		</p>

		<h2><?php esc_html_e( '2. Erhebung und Verarbeitung personenbezogener Daten', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Diese Website erhebt und verarbeitet personenbezogene Daten nur, soweit dies zur Bereitstellung einer funktionsfähigen Website sowie unserer Inhalte und Leistungen erforderlich ist.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '3. Kontaktformular', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem Anfrageformular zur Bearbeitung der Anfrage und für den Fall von Anschlussfragen bei uns gespeichert.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '4. Cookies', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Diese Website verwendet nur technisch notwendige Cookies. Es werden keine Tracking- oder Werbe-Cookies eingesetzt.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '5. Ihre Rechte', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Sie haben jederzeit das Recht auf Auskunft über die bei uns gespeicherten personenbezogenen Daten sowie das Recht auf Berichtigung, Sperrung oder Löschung dieser Daten.', 'raveenthiran' ); ?></p>

		<hr>
		<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>

		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
