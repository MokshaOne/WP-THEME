<?php
/**
 * Footer — a single quiet line. Socials live on About/Enquire, not here.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
</main>

<footer class="nr-foot nr-ui" role="contentinfo">
	<span>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( nr_opt( 'nr_logo_text', 'raveenthiran' ) ); ?></span>
	<span><?php echo esc_html( nr_opt( 'nr_location', '' ) ); ?></span>
</footer>

<?php wp_footer(); ?>
</body>
</html>
