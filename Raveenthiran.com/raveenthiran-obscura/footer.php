<?php
/**
 * Footer — page-wipe slot + mobile tab bar + status banner.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main><?php /* /main */ ?>

<?php
$nr_footer_text = nr_opt( 'nr_footer_text', '' );
$nr_legal = [
	'datenschutz' => nr_opt( 'nr_footer_legal_dz', 'Datenschutz' ),
	'agb'         => nr_opt( 'nr_footer_legal_ag', 'AGB' ),
	'impressum'   => nr_opt( 'nr_footer_legal_im', 'Impressum' ),
];
$nr_ig = nr_opt( 'nr_instagram', '' );
$nr_show_footer = $nr_footer_text || $nr_legal['datenschutz'] || $nr_legal['agb'] || $nr_legal['impressum'];
?>
<?php if ( $nr_show_footer && ! is_front_page() && ! is_singular( 'nr_project' ) && ! is_post_type_archive( 'nr_project' ) ) : ?>
<footer class="nr-footer" role="contentinfo">
	<span><?php echo esc_html( $nr_footer_text ); ?></span>
	<?php if ( function_exists( 'nr_footer_cta' ) ) nr_footer_cta(); ?>
	<nav class="nr-footer__links">
		<?php $nr_fe = nr_opt( 'nr_email', '' ); if ( $nr_fe ) : ?><button type="button" class="nr-footer__email" data-copy="<?php echo esc_attr( $nr_fe ); ?>"><?php echo esc_html( $nr_fe ); ?></button><?php endif; ?>
		<?php if ( $nr_ig ) : ?><a href="<?php echo esc_url( $nr_ig ); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
		<?php $nr_pk = nr_opt( 'nr_presskit_url', '' ); if ( $nr_pk ) : ?><a href="<?php echo esc_url( $nr_pk ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Press kit', 'raveenthiran' ); ?></a><?php endif; ?>
		<?php if ( $nr_legal['datenschutz'] ) : ?><a href="<?php echo esc_url( home_url( '/datenschutz' ) ); ?>"><?php echo esc_html( $nr_legal['datenschutz'] ); ?></a><?php endif; ?>
		<?php if ( $nr_legal['agb']         ) : ?><a href="<?php echo esc_url( home_url( '/agb' ) ); ?>"        ><?php echo esc_html( $nr_legal['agb'] ); ?></a><?php endif; ?>
		<?php if ( $nr_legal['impressum']   ) : ?><a href="<?php echo esc_url( home_url( '/impressum' ) ); ?>"  ><?php echo esc_html( $nr_legal['impressum'] ); ?></a><?php endif; ?>
	</nav>
</footer>
<?php endif; ?>

<?php /* ── page-transition amber wipe ──────────────────────── */ ?>
<div class="nr-wipe" aria-hidden="true"></div>


<?php /* ── mobile bottom tab bar ───────────────────────────── */ ?>
<?php get_template_part( 'parts/mobile-tabs' ); ?>

<?php /* ── global feature parts ─────────────────────────────── */ ?>
<?php get_template_part( 'parts/cookie-notice' ); ?>
<?php get_template_part( 'parts/inquiry-modal' ); ?>
<?php get_template_part( 'parts/quote-popover' ); ?>

<?php
/* form submission status banner — set by admin_post_nr_contact_send redirect */
if ( isset( $_GET['nr_sent'] ) ) :
	$ok    = $_GET['nr_sent'] === '1';
	$class = $ok ? 'nr-status--ok' : 'nr-status--error';
	$msg   = $ok
		? __( 'Sent — I’ll reply within 24 hours.', 'raveenthiran' )
		: __( 'Sending failed. Email me directly.', 'raveenthiran' );
	?>
	<div class="nr-status <?php echo esc_attr( $class ); ?>" role="status" aria-live="polite"><?php echo esc_html( $msg ); ?></div>
	<script>setTimeout(function(){var s=document.querySelector('.nr-status');if(s)s.remove();},5000);</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
