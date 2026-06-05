<?php
/**
 * Template Name: Datenschutz
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();

$nr_name  = nr_opt( 'nr_legal_name', '' ) ?: wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
$nr_addr  = nr_opt( 'nr_studio', 'Gumpendorfer Str. 81' );
$nr_loc   = nr_opt( 'nr_location', '1060 Wien, Österreich' );
$nr_email = nr_opt( 'nr_email', '' );
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<div class="nr-legal">
			<span class="nr-legal__eyebrow"><?php esc_html_e( '§ Rechtliches · DSGVO', 'raveenthiran' ); ?></span>
			<h1><?php esc_html_e( 'Datenschutz', 'raveenthiran' ); ?></h1>

			<?php if ( have_posts() ) : the_post(); the_content(); else : ?>

			<p class="nr-legal__lede"><?php esc_html_e( 'Der Schutz Ihrer personenbezogenen Daten ist uns wichtig. Diese Erklärung informiert Sie gemäß der Datenschutz-Grundverordnung (DSGVO) und dem österreichischen Datenschutzgesetz (DSG) über Art, Umfang und Zweck der Verarbeitung.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '1. Verantwortlicher', 'raveenthiran' ); ?></h2>
			<p>
				<strong><?php echo esc_html( $nr_name ); ?></strong><br>
				<?php echo esc_html( $nr_addr ); ?>, <?php echo esc_html( $nr_loc ); ?><br>
				<?php if ( $nr_email ) : ?>E-Mail: <a href="mailto:<?php echo esc_attr( $nr_email ); ?>"><?php echo esc_html( $nr_email ); ?></a><?php endif; ?>
			</p>

			<h2><?php esc_html_e( '2. Ihre Rechte', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Ihnen stehen hinsichtlich Ihrer Daten folgende Rechte zu:', 'raveenthiran' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Auskunft über die verarbeiteten Daten (Art. 15 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Berichtigung unrichtiger Daten (Art. 16 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Löschung („Recht auf Vergessenwerden", Art. 17 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Einschränkung der Verarbeitung (Art. 18 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Datenübertragbarkeit (Art. 20 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Widerruf einer erteilten Einwilligung mit Wirkung für die Zukunft', 'raveenthiran' ); ?></li>
			</ul>
			<p><?php esc_html_e( 'Wenden Sie sich dafür an die oben genannte Adresse. Sie haben zudem das Recht auf Beschwerde bei der Aufsichtsbehörde:', 'raveenthiran' ); ?> <strong><?php esc_html_e( 'Österreichische Datenschutzbehörde', 'raveenthiran' ); ?></strong> (<a href="https://www.dsb.gv.at" target="_blank" rel="noopener">dsb.gv.at</a>).</p>

			<h2><?php esc_html_e( '3. Hosting & Server-Logfiles', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Diese Website wird bei einem Provider in der EU (easyname GmbH, Österreich) gehostet. Beim Aufruf erfasst der Server automatisch sogenannte Server-Logfiles (IP-Adresse, Datum und Uhrzeit, abgerufene Datei, übertragene Datenmenge, Referrer, Browser/Betriebssystem). Diese Daten dienen ausschließlich der Sicherstellung eines störungsfreien Betriebs und der Sicherheit. Rechtsgrundlage ist unser berechtigtes Interesse (Art. 6 Abs. 1 lit. f DSGVO).', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '4. Content Delivery Network (Cloudflare)', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Zur sicheren, schnellen Auslieferung nutzen wir Cloudflare (Cloudflare, Inc., USA). Dabei wird der Datenverkehr über Server von Cloudflare geleitet; Ihre IP-Adresse wird verarbeitet, um Angriffe abzuwehren und Inhalte aus dem nächstgelegenen Rechenzentrum bereitzustellen. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO. Eine Übermittlung in die USA erfolgt auf Grundlage der EU-Standardvertragsklauseln. Details:', 'raveenthiran' ); ?> <a href="https://www.cloudflare.com/privacypolicy/" target="_blank" rel="noopener">cloudflare.com/privacypolicy</a>.</p>

			<h2><?php esc_html_e( '5. Cookies & Einwilligung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Diese Website setzt standardmäßig nur technisch notwendige Cookies, die für den Betrieb erforderlich sind (u. a. ein Cookie zur Speicherung Ihrer Cookie-Entscheidung). Optionale Dienste werden erst nach Ihrer ausdrücklichen Einwilligung über das Cookie-Banner geladen. Ihre Einwilligung können Sie jederzeit mit Wirkung für die Zukunft widerrufen.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '6. Anfrage-/Kontaktformular', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Wenn Sie das Anfrageformular nutzen, verarbeiten wir die von Ihnen angegebenen Daten (Name, E-Mail-Adresse, Art des Projekts, Wunschtermin sowie Ihre Nachricht), um Ihre Anfrage zu beantworten und das Projekt vorzubereiten. Rechtsgrundlage ist die Anbahnung bzw. Erfüllung eines Vertrags (Art. 6 Abs. 1 lit. b DSGVO) sowie unser berechtigtes Interesse an der Beantwortung (lit. f). Die Anfrage wird zur Bearbeitung gespeichert und nach Wegfall des Zwecks bzw. Ablauf gesetzlicher Aufbewahrungsfristen gelöscht.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '7. E-Mail-Versand', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Für den Versand von Benachrichtigungen und Antworten (z. B. die automatische Eingangsbestätigung) nutzen wir Google Workspace (Google Ireland Ltd.). Die Verarbeitung dient der Kommunikation mit Ihnen; Rechtsgrundlage ist Art. 6 Abs. 1 lit. b und lit. f DSGVO.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '8. Spam-Schutz', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Zum Schutz des Formulars vor Missbrauch kann Cloudflare Turnstile eingesetzt werden — ein datensparsames Verfahren ohne Tracking-Cookies, das technische Merkmale der Anfrage prüft. Rechtsgrundlage ist unser berechtigtes Interesse an der Abwehr von Spam (Art. 6 Abs. 1 lit. f DSGVO).', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '9. Schriftarten', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Alle Schriftarten werden lokal von unserem eigenen Server ausgeliefert. Es besteht keine Verbindung zu Servern Dritter (z. B. Google Fonts); es werden dafür keine Daten an Dritte übertragen.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '10. Reichweitenmessung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Standardmäßig setzen wir keine Tracking- oder Werbe-Dienste ein. Sollte eine cookielose, datenschutzfreundliche Reichweitenmessung aktiviert sein, erfolgt diese ohne personenbezogene Profilbildung; andernfalls wird eine etwaige Analyse erst nach Ihrer Einwilligung geladen.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '11. Datensicherheit', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Diese Website nutzt eine TLS-/SSL-Verschlüsselung (https). Übertragene Daten können von Dritten nicht mitgelesen werden.', 'raveenthiran' ); ?></p>

			<hr>
			<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>
			<a class="nr-legal__back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>←</span> <span><?php esc_html_e( 'Zurück', 'raveenthiran' ); ?></span></a>

			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
