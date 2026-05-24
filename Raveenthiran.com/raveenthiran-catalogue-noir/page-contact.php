<?php
/**
 * Template Name: Contact
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'contact';
get_header();

$email = nr_opt( 'nr_email', 'studio@raveenthiran.at' );
$phone = nr_opt( 'nr_phone', '+43 1 555 0142' );
$loc   = nr_opt( 'nr_studio', 'Gumpendorfer Str. 81, 1060 Wien' );
?>
<section class="nr-page nr-fullscreen nr-contact">

	<div class="nr-contact__form-wrap">
		<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_contact_eyebrow', __( 'Contact · §06', 'raveenthiran' ) ) ); ?></span>
		<h1 class="nr-display nr-display--md"><?php echo wp_kses( nr_opt( 'nr_contact_title', __( "Let's work <em>together.</em>", 'raveenthiran' ) ), [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		<?php $intro = nr_opt( 'nr_contact_intro', '' ); if ( $intro ) : ?>
			<p class="nr-contact__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>

		<?php
		// Use CF7 / WPForms shortcode if set; otherwise render a plain form posting to admin-post.
		$form_shortcode = nr_opt( 'nr_contact_form_shortcode' );
		if ( $form_shortcode ) :
			echo do_shortcode( $form_shortcode );
		else : ?>
			<form class="nr-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nr_contact_send">
				<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
				<label><span class="nr-eyebrow"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
					<input type="text" name="name" autocomplete="name" required>
				</label>
				<label><span class="nr-eyebrow"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
					<input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required>
				</label>
				<fieldset class="nr-form__chips">
					<legend class="nr-eyebrow"><?php esc_html_e( 'Project type', 'raveenthiran' ); ?></legend>
					<?php foreach ( [ 'Editorial', 'Portrait', 'Event', 'Commercial', 'Other' ] as $i => $c ) : ?>
						<label class="nr-chip<?php echo $i === 0 ? ' is-on' : ''; ?>"><input type="radio" name="project_type" value="<?php echo esc_attr( $c ); ?>" <?php checked( $i, 0 ); ?> hidden><?php echo esc_html( $c ); ?></label>
					<?php endforeach; ?>
				</fieldset>
				<label class="nr-form__wide"><span class="nr-eyebrow"><?php esc_html_e( 'Tell me about the project', 'raveenthiran' ); ?></span>
					<textarea name="notes" placeholder="<?php esc_attr_e( 'A few sentences — a place, a person, an hour of the day.', 'raveenthiran' ); ?>" rows="4"></textarea>
				</label>
				<div class="nr-form__foot">
					<span class="nr-eyebrow"><?php esc_html_e( 'Typical response · < 24h', 'raveenthiran' ); ?></span>
					<button type="submit" class="nr-btn nr-btn--primary"><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send brief', 'raveenthiran' ) ) ); ?>
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</button>
				</div>
			</form>
		<?php endif; ?>
	</div>

	<aside class="nr-contact__side">
		<dl class="nr-contact__info">
			<div><dt><?php esc_html_e( 'Email', 'raveenthiran' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd></div>
			<div><dt><?php esc_html_e( 'Phone', 'raveenthiran' ); ?></dt><dd><?php echo esc_html( $phone ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></dt><dd><?php echo esc_html( $loc ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Hours', 'raveenthiran' ); ?></dt><dd><?php esc_html_e( 'Mon–Fri · 10:00–18:00', 'raveenthiran' ); ?></dd></div>
		</dl>
	</aside>
</section>

<?php get_footer(); ?>
