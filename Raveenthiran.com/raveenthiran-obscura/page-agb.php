<?php
/**
 * Template Name: AGB
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<h1><?php esc_html_e( 'Allgemeine Geschäftsbedingungen', 'raveenthiran' ); ?></h1>

		<?php if ( have_posts() ) : the_post(); the_content(); else : ?>

		<h2><?php esc_html_e( '1. Geltungsbereich', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Diese Allgemeinen Geschäftsbedingungen gelten für alle Leistungen von Nishuthan Raveenthiran (im Folgenden „Fotograf") gegenüber seinen Auftraggebern.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '2. Vertragsabschluss', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Ein Vertrag kommt zustande, sobald der Auftraggeber den Kostenvoranschlag schriftlich bestätigt und eine Anzahlung von 30 % des vereinbarten Honorars geleistet hat.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '3. Urheberrecht', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Alle Fotografien sind urheberrechtlich geschützt. Die Nutzungsrechte werden im Einzelvertrag geregelt. Eine Weitergabe an Dritte bedarf der schriftlichen Zustimmung des Fotografen.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '4. Stornierung', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Bei Stornierung weniger als 14 Tage vor dem Aufnahmedatum wird die Anzahlung einbehalten. Bei Stornierung weniger als 48 Stunden vor dem Termin ist das vollständige Honorar fällig.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '5. Haftung', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Der Fotograf haftet nur für grobe Fahrlässigkeit und Vorsatz. Eine Haftung für technische Fehler, höhere Gewalt oder Datenverlust ist ausgeschlossen.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( '6. Gerichtsstand', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Gerichtsstand ist Wien. Es gilt österreichisches Recht.', 'raveenthiran' ); ?></p>

		<hr>
		<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>

		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
