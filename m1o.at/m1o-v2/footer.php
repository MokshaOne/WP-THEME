<?php
/**
 * VOID footer — fixed tactical HUD bar + preserved feature parts.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_lat  = nr_opt( 'nr_void_lat', '48.2082° N' );
$nr_long = nr_opt( 'nr_void_long', '16.3738° E' );
?>
</main><?php /* /#main */ ?>

<footer class="void-footer" role="contentinfo">
	<div class="void-footer-left">
		<span class="void-footer-brand">VOID ARCHIVE // <?php bloginfo( 'name' ); ?></span>
		<span class="void-dot-row"><span></span><span></span><span></span></span>
	</div>
	<div class="void-footer-right">
		<span><?php echo esc_html( $nr_lat ); ?></span>
		<span><?php echo esc_html( $nr_long ); ?></span>
		<span class="void-status-pill"><span class="void-status-dot"></span><?php esc_html_e( 'SYSTEM: OPERATIONAL', 'raveenthiran' ); ?></span>
	</div>
</footer>

<?php /* preserved global features — modals / cookie / quote popover */ ?>
<?php get_template_part( 'parts/cookie-notice' ); ?>
<?php get_template_part( 'parts/inquiry-modal' ); ?>
<?php get_template_part( 'parts/quote-popover' ); ?>

<?php
/* form submission status banner */
if ( isset( $_GET['nr_sent'] ) ) :
	$ok  = $_GET['nr_sent'] === '1';
	$msg = $ok ? __( 'Transmission received — reply within 24 hours.', 'raveenthiran' ) : __( 'Transmission failed. Email directly.', 'raveenthiran' );
	?>
	<div class="nr-status <?php echo $ok ? 'nr-status--ok' : 'nr-status--error'; ?>" role="status" aria-live="polite"><?php echo esc_html( $msg ); ?></div>
	<script>setTimeout(function(){var s=document.querySelector('.nr-status');if(s)s.remove();},5000);</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
