<?php
/**
 * Template Name: Impressum
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();

$nr_name    = nr_opt( 'nr_legal_name', '' ) ?: wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
$nr_addr    = nr_opt( 'nr_studio', 'Gumpendorfer Str. 81' );
$nr_loc     = nr_opt( 'nr_location', '1060 Wien, Österreich' );
$nr_email   = nr_opt( 'nr_email', '' );
$nr_phone   = nr_opt( 'nr_phone', '' );
$nr_uid     = nr_opt( 'nr_uid', '' );
$nr_gewerbe = nr_opt( 'nr_gewerbe', 'Berufsfotografie (freies Gewerbe)' );
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<div class="nr-legal">
			<span class="nr-legal__eyebrow"><?php esc_html_e( '§ Rechtliches · Offenlegung', 'raveenthiran' ); ?></span>
			<h1><?php esc_html_e( 'Impressum', 'raveenthiran' ); ?></h1>

			<?php if ( have_posts() ) : the_post(); the_content(); else : ?>

			<p class="nr-legal__lede"><?php esc_html_e( 'Offenlegung gemäß § 5 E-Commerce-Gesetz (ECG), § 14 Unternehmensgesetzbuch (UGB) und § 25 Mediengesetz.', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( 'Medieninhaber & Diensteanbieter', 'raveenthiran' ); ?></h2>
			<p>
				<strong><?php echo esc_html( $nr_name ); ?></strong><br>
				<?php echo esc_html( $nr_addr ); ?><br>
				<?php echo esc_html( $nr_loc ); ?>
			</p>

			<h2><?php esc_html_e( 'Kontakt', 'raveenthiran' ); ?></h2>
			<p>
				<?php if ( $nr_email ) : ?>E-Mail: <a href="mailto:<?php echo esc_attr( $nr_email ); ?>"><?php echo esc_html( $nr_email ); ?></a><br><?php endif; ?>
				<?php if ( $nr_phone ) : ?>Telefon: <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $nr_phone ) ); ?>"><?php echo esc_html( $nr_phone ); ?></a><?php endif; ?>
			</p>

			<h2><?php esc_html_e( 'Unternehmensgegenstand', 'raveenthiran' ); ?></h2>
			<p><?php echo esc_html( $nr_gewerbe ); ?><?php esc_html_e( ' — professionelle Fotografie (Editorial, Porträt, Architektur, Event).', 'raveenthiran' ); ?></p>

			<h2><?php esc_html_e( 'Umsatzsteuer-Identifikationsnummer', 'raveenthiran' ); ?></h2>
			<p>
				<?php esc_html_e( 'UID gemäß § 27a Umsatzsteuergesetz:', 'raveenthiran' ); ?>
				<strong><?php echo $nr_uid ? esc_html( $nr_uid ) : esc_html__( 'auf Anfrage', 'raveenthiran' ); ?></strong>
			</p>

			<h2><?php esc_html_e( 'Gewerbe & Aufsichtsbehörde', 'raveenthiran' ); ?></h2>
			<p>
				<?php esc_html_e( 'Mitglied der Wirtschaftskammer Wien, Fachgruppe der Berufsfotografen.', 'raveenthiran' ); ?><br>
				<?php esc_html_e( 'Gewerbebehörde: Magistratisches Bezirksamt der Stadt Wien.', 'raveenthiran' ); ?><br>
				<?php esc_html_e( 'Anwendbare Rechtsvorschrift: Gewerbeordnung (GewO) —', 'raveenthiran' ); ?>
				<a href="https://www.ris.bka.gv.at" target="_blank" rel="noopener">ris.bka.gv.at</a>
			</p>

			<h2><?php esc_html_e( 'Online-Streitbeilegung', 'raveenthiran' ); ?></h2>
			<p>
				<?php esc_html_e( 'Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:', 'raveenthiran' ); ?>
				<a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">ec.europa.eu/consumers/odr</a>.
				<?php esc_html_e( 'Wir sind nicht verpflichtet und nicht bereit, an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.', 'raveenthiran' ); ?>
			</p>

			<h2><?php esc_html_e( 'Urheberrecht & Haftung', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'Sämtliche Inhalte, insbesondere Fotografien und Texte, unterliegen dem österreichischen Urheberrecht (UrhG). Jede Verwertung außerhalb der gesetzlich zulässigen Fälle bedarf der vorherigen schriftlichen Zustimmung. Die Inhalte wurden mit größter Sorgfalt erstellt; für Richtigkeit, Vollständigkeit und Aktualität wird keine Gewähr übernommen. Für die Inhalte verlinkter externer Seiten ist ausschließlich deren Betreiber verantwortlich.', 'raveenthiran' ); ?></p>

			<hr>
			<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>
			<a class="nr-legal__back" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>←</span> <span><?php esc_html_e( 'Zurück', 'raveenthiran' ); ?></span></a>

			<?php endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
