<?php
/**
 * Template Name: AGB
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();

$nr_name = nr_opt( 'nr_legal_name', '' ) ?: wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<div class="nr-legal">
			<span class="nr-legal__eyebrow"><?php esc_html_e( '§ Rechtliches · Geschäftsbedingungen', 'raveenthiran' ); ?></span>
			<h1><?php esc_html_e( 'Allgemeine Geschäftsbedingungen', 'raveenthiran' ); ?></h1>

			<?php if ( have_posts() ) the_post(); ?>
			<?php if ( trim( get_the_content() ) !== '' ) : the_content(); else : ?>

			<p class="nr-legal__lede"><?php printf( esc_html__( 'Diese Bedingungen regeln die fotografischen Leistungen von %s (im Folgenden „der Fotograf") gegenüber Auftraggebern.', 'raveenthiran' ), '<strong>' . esc_html( $nr_name ) . '</strong>' ); ?></p>

			<h2><?php esc_html_e( '1. Geltungsbereich', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Diese Allgemeinen Geschäftsbedingungen gelten für alle Angebote, Aufträge und Verträge über fotografische Leistungen. Abweichende Bedingungen des Auftraggebers gelten nur, wenn sie schriftlich bestätigt wurden.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '2. Vertragsabschluss', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Ein Vertrag kommt zustande, sobald der Auftraggeber den übermittelten Kostenvoranschlag schriftlich (auch per E-Mail) bestätigt. Kostenvoranschläge sind freibleibend und 30 Tage gültig. Zur Terminreservierung kann eine Anzahlung in Höhe von 30 % des vereinbarten Honorars vereinbart werden.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '3. Leistungsumfang', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Art und Umfang der Leistung ergeben sich aus dem Angebot. Der Fotograf gestaltet die Aufnahmen nach eigenem künstlerischem Ermessen im Rahmen des vereinbarten Auftrags. Die Auswahl der zu liefernden Bilder erfolgt durch den Fotografen, sofern nicht anders vereinbart.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '4. Honorar & Zahlung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Es gilt das im Angebot genannte Honorar zzgl. allfälliger Umsatzsteuer und vereinbarter Nebenkosten (z. B. Reise, Material, Miete). Der Restbetrag ist binnen 14 Tagen nach Rechnungslegung ohne Abzug fällig. Bei Zahlungsverzug werden Verzugszinsen nach den gesetzlichen Bestimmungen verrechnet.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '5. Nutzungsrechte', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Mit vollständiger Bezahlung erhält der Auftraggeber die im Vertrag bezeichneten Nutzungsrechte (Umfang, Dauer, Medium, geografischer Raum). Ohne ausdrückliche Vereinbarung wird ein einfaches, nicht übertragbares Nutzungsrecht für den vereinbarten Zweck eingeräumt. Eine Weitergabe an Dritte, Bearbeitung oder Nutzung über den vereinbarten Umfang hinaus bedarf der vorherigen schriftlichen Zustimmung.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '6. Urheberrecht & Namensnennung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Alle Werke sind nach dem österreichischen Urheberrechtsgesetz (UrhG) geschützt; das Urheberrecht verbleibt beim Fotografen. Bei Veröffentlichung ist der Fotograf als Urheber zu nennen, sofern nichts anderes vereinbart ist. Der Fotograf ist berechtigt, die Aufnahmen zur Eigenwerbung (Portfolio, Website, Social Media) zu verwenden, sofern der Auftraggeber dem nicht aus berechtigten Gründen widerspricht.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '7. Termine, Stornierung & höhere Gewalt', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Bei Stornierung durch den Auftraggeber weniger als 14 Tage vor dem Aufnahmetermin wird eine geleistete Anzahlung als Ausfallhonorar einbehalten; bei Stornierung innerhalb von 48 Stunden vor dem Termin wird das vollständige Honorar fällig. Kann ein Termin aus Gründen höherer Gewalt (Krankheit, Wetter bei Außenaufnahmen u. Ä.) nicht stattfinden, wird ein Ersatztermin vereinbart.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '8. Gewährleistung & Haftung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Der Fotograf erbringt seine Leistung mit der Sorgfalt eines ordentlichen Unternehmers. Eine Haftung besteht nur bei Vorsatz und grober Fahrlässigkeit. Für leichte Fahrlässigkeit, technische Störungen, Datenverlust oder Mängel an überlassenem Material wird keine Haftung übernommen. Geschmacks- bzw. Auffassungsunterschiede in der künstlerischen Gestaltung begründen keinen Mangel.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( '9. Datenschutz', 'raveenthiran' ); ?></h2>
			<p><?php printf( esc_html__( 'Die Verarbeitung personenbezogener Daten richtet sich nach unserer %s.', 'raveenthiran' ), '<a href="' . esc_url( home_url( '/datenschutz' ) ) . '">' . esc_html__( 'Datenschutzerklärung', 'raveenthiran' ) . '</a>' ); ?></p>

			<h2><?php esc_html_e( '10. Schlussbestimmungen', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Es gilt österreichisches Recht unter Ausschluss der Verweisungsnormen. Gerichtsstand ist — soweit zulässig — Wien. Sollte eine Bestimmung unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt; an die Stelle der unwirksamen Bestimmung tritt eine dem wirtschaftlichen Zweck am nächsten kommende Regelung.', 'raveenthiran' ); ?></p>

			<hr>
			<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>
			<a class="nr-legal__back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>←</span> <span><?php esc_html_e( 'Zurück', 'raveenthiran' ); ?></span></a>

			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
