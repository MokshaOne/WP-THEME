<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="nr-page nr-404">
	<span class="nr-eyebrow">404</span>
	<h1 class="nr-display nr-display--xl"><?php esc_html_e( 'Page not', 'raveenthiran' ); ?> <em><?php esc_html_e( 'found.', 'raveenthiran' ); ?></em></h1>
	<p class="nr-404__lede">
		<?php esc_html_e( 'The page you are looking for has moved or never existed. Try the portfolio, or send a note from the contact page.', 'raveenthiran' ); ?>
	</p>
	<div class="nr-404__actions">
		<a class="nr-btn nr-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back home', 'raveenthiran' ); ?></span> <span>→</span></a>
		<a class="nr-btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><span><?php esc_html_e( 'Contact', 'raveenthiran' ); ?></span></a>
	</div>
</section>
<?php get_footer(); ?>
