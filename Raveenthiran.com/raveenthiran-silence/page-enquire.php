<?php
/**
 * Template Name: Enquire
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$sent = isset( $_GET['sent'] ) ? sanitize_text_field( $_GET['sent'] ) : '';
?>

<article class="sl-sheet sl-enquire">
	<div class="sl-sheet__inner">
		<h1 class="sl-eyebrow"><?php the_title(); ?></h1>

		<?php if ( get_the_content() ) : ?>
			<div class="sl-prose"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( $sent === '1' ) : ?>
			<p class="sl-note" role="status"><?php esc_html_e( 'Thank you — your enquiry was sent. I reply within a day.', 'raveenthiran-silence' ); ?></p>
		<?php elseif ( $sent === '0' ) : ?>
			<p class="sl-note sl-note--err" role="alert"><?php esc_html_e( 'Something went wrong — please check the fields and try again.', 'raveenthiran-silence' ); ?></p>
		<?php endif; ?>

		<form class="sl-form" method="post" action="">
			<?php wp_nonce_field( 'sl_enquiry', 'sl_enquiry_nonce' ); ?>
			<p class="sl-hp"><label><?php esc_html_e( 'Website', 'raveenthiran-silence' ); ?><input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

			<label>
				<span><?php esc_html_e( 'Name', 'raveenthiran-silence' ); ?></span>
				<input type="text" name="name" required autocomplete="name">
			</label>
			<label>
				<span><?php esc_html_e( 'Email', 'raveenthiran-silence' ); ?></span>
				<input type="email" name="email" required autocomplete="email">
			</label>
			<label>
				<span><?php esc_html_e( 'Message', 'raveenthiran-silence' ); ?></span>
				<textarea name="message" rows="6" required></textarea>
			</label>

			<?php if ( function_exists( 'sl_turnstile_field' ) ) sl_turnstile_field(); ?>

			<button type="submit"><?php esc_html_e( 'Send', 'raveenthiran-silence' ); ?> —</button>
		</form>
	</div>
</article>

<?php endwhile; get_footer(); ?>
