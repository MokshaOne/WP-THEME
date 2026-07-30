<?php
/**
 * Contact — studio details (from Theme Settings) + a short message form that
 * reuses the engine's nr_contact_send handler. Auto-applies to a Page with
 * slug "contact".
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_eyebrow = nr_opt( 'nr_contact_eyebrow', __( 'Contact', 'still' ) );
$still_title   = nr_opt( 'nr_contact_title', 'Get in touch<em>.</em>' );
$still_email   = nr_opt( 'nr_email', '' );
$still_phone   = nr_opt( 'nr_phone', '' );
$still_wa      = nr_opt( 'nr_whatsapp', '' );
$still_studio  = nr_opt( 'nr_studio', '' );
$still_loc     = nr_opt( 'nr_location', 'Vienna' );
$still_socials = array_filter( array(
	'Instagram' => nr_opt( 'nr_instagram', '' ),
	'LinkedIn'  => nr_opt( 'nr_linkedin', '' ),
	'Behance'   => nr_opt( 'nr_behance', '' ),
	'Vimeo'     => nr_opt( 'nr_vimeo', '' ),
) );
$still_sent = isset( $_GET['nr_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['nr_sent'] ) ) : null;
?>
<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php echo esc_html( $still_eyebrow ); ?></span>
		<h1 class="page-title"><?php echo wp_kses( $still_title, array( 'em' => array() ) ); ?></h1>
	</header>

	<?php if ( '1' === $still_sent ) : ?>
		<div class="form-note ok"><?php esc_html_e( 'Thank you — your message has been sent.', 'still' ); ?></div>
	<?php elseif ( '0' === $still_sent ) : ?>
		<div class="form-note"><?php esc_html_e( 'Something went wrong. Please email directly.', 'still' ); ?></div>
	<?php endif; ?>

	<div class="two-col">
		<dl class="contact-list">
			<?php if ( $still_studio || $still_loc ) : ?><div class="row"><dt><?php esc_html_e( 'Studio', 'still' ); ?></dt><dd><?php echo esc_html( $still_studio ?: $still_loc ); ?></dd></div><?php endif; ?>
			<?php if ( $still_email ) : ?><div class="row"><dt><?php esc_html_e( 'Email', 'still' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $still_email ); ?>"><?php echo esc_html( $still_email ); ?></a></dd></div><?php endif; ?>
			<?php if ( $still_phone ) : ?><div class="row"><dt><?php esc_html_e( 'Phone', 'still' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $still_phone ) ); ?>"><?php echo esc_html( $still_phone ); ?></a></dd></div><?php endif; ?>
			<?php if ( $still_wa ) : ?><div class="row"><dt>WhatsApp</dt><dd><a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $still_wa ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $still_wa ); ?></a></dd></div><?php endif; ?>
			<?php if ( $still_socials ) : ?><div class="row"><dt><?php esc_html_e( 'Elsewhere', 'still' ); ?></dt><dd><?php
				$still_out = array();
				foreach ( $still_socials as $k => $u ) { $still_out[] = '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( $k ) . '</a>'; }
				echo wp_kses_post( implode( ' &nbsp;·&nbsp; ', $still_out ) );
			?></dd></div><?php endif; ?>
		</dl>

		<form class="still-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nr_contact_send">
			<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
			<input type="hidden" name="nr_ref" value=""><input type="hidden" name="nr_service" value=""><input type="hidden" name="nr_referrer" value="" data-nr-referrer><input type="hidden" name="project_type" value="Message"><input type="hidden" name="estimate" value="">
			<div class="hp" aria-hidden="true"><label>Company<input type="text" name="nr_company" tabindex="-1" autocomplete="off"></label></div>
			<div class="field"><label for="c-name"><?php esc_html_e( 'Name', 'still' ); ?></label><input id="c-name" type="text" name="name" required></div>
			<div class="field"><label for="c-email"><?php esc_html_e( 'Email', 'still' ); ?></label><input id="c-email" type="email" name="email" required></div>
			<div class="field"><label for="c-msg"><?php esc_html_e( 'Message', 'still' ); ?></label><textarea id="c-msg" name="notes" required></textarea></div>
			<button type="submit" class="form-submit"><?php esc_html_e( 'Send message', 'still' ); ?> →</button>
		</form>
	</div>
</main>
<?php get_footer(); ?>
