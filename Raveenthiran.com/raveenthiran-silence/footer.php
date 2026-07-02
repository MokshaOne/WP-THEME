<?php
/**
 * Footer — a single quiet line. Socials live on About/Enquire, not here.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main>

<footer class="sl-foot sl-ui" role="contentinfo">
	<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( sl_opt( 'sl_studio', 'raveenthiran' ) ); ?></span>
	<span><?php echo esc_html( sl_opt( 'sl_location', '' ) ); ?></span>
</footer>

<?php wp_footer(); ?>
</body>
</html>
