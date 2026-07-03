<?php
/**
 * Template Name: Enquire
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$sent = isset( $_GET['sent'] ) ? sanitize_text_field( $_GET['sent'] ) : '';
?>

<article class="nr-sheet nr-enquire">
	<div class="nr-sheet__inner">
		<h1 class="nr-eyebrow"><?php the_title(); ?></h1>

		<?php if ( get_the_content() ) : ?>
			<div class="nr-prose"><?php the_content(); ?></div>
		<?php endif; ?>

		<?php if ( $sent === '1' ) : ?>
			<p class="nr-note" role="status"><?php esc_html_e( 'Thank you — your enquiry was sent. I reply within a day.', 'raveenthiran-silence' ); ?></p>
		<?php elseif ( $sent === '0' ) : ?>
			<p class="nr-note nr-note--err" role="alert"><?php esc_html_e( 'Something went wrong — please check the fields and try again.', 'raveenthiran-silence' ); ?></p>
		<?php endif; ?>

		<form class="nr-form" method="post" action="">
			<?php wp_nonce_field( 'nr_enquiry', 'nr_enquiry_nonce' ); ?>
			<p class="nr-hp"><label><?php esc_html_e( 'Website', 'raveenthiran-silence' ); ?><input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

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

			<?php if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field(); ?>

			<button type="submit"><?php esc_html_e( 'Send', 'raveenthiran-silence' ); ?> —</button>
		</form>
	</div>
</article>

<?php endwhile; get_footer(); ?>
