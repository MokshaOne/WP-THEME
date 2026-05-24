<?php
/**
 * Template Name: Impressum
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();
?>
<section class="nr-page nr-fullscreen">
	<div class="nr-static">
		<h1><?php esc_html_e( 'Impressum', 'raveenthiran' ); ?></h1>

		<?php if ( have_posts() ) : the_post(); the_content(); else : ?>

		<h2><?php esc_html_e( 'Angaben gemäß § 5 ECG', 'raveenthiran' ); ?></h2>
		<p>
			<strong><?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong><br>
			<?php echo esc_html( nr_opt( 'nr_studio', 'Gumpendorfer Str. 81' ) ); ?><br>
			<?php echo esc_html( nr_opt( 'nr_location', '1060 Wien, Austria' ) ); ?>
		</p>

		<h2><?php esc_html_e( 'Kontakt', 'raveenthiran' ); ?></h2>
		<p>
			<?php $email = nr_opt( 'nr_email', 'studio@raveenthiran.at' ); ?>
			<?php $phone = nr_opt( 'nr_phone', '' ); ?>
			<?php if ( $email ) : ?>
				E-Mail: <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><br>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
				Tel: <?php echo esc_html( $phone ); ?>
			<?php endif; ?>
		</p>

		<h2><?php esc_html_e( 'Umsatzsteuer-ID', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Gemäß § 27a Umsatzsteuergesetz: auf Anfrage.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( 'Haftungsausschluss', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Die Inhalte dieser Website wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte kann jedoch keine Gewähr übernommen werden.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( 'Urheberrecht', 'raveenthiran' ); ?></h2>
		<p><?php esc_html_e( 'Die durch den Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem österreichischen Urheberrecht.', 'raveenthiran' ); ?></p>

		<hr>
		<p><small><?php esc_html_e( 'Stand:', 'raveenthiran' ); ?> <?php echo esc_html( date_i18n( 'F Y' ) ); ?></small></p>

		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
