<?php
/**
 * Enquire — the booking form + FAQ, in Still's minimal look. Reuses the engine's
 * nr_contact_send handler (server-side email/PDF/enquiry-CPT) and nr_faq_items()
 * (ACF faq_items on this page, else sensible defaults). Auto-applies to a Page
 * with slug "enquire".
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_eyebrow = nr_opt( 'nr_enquire_eyebrow', __( 'Enquire', 'still' ) );
$still_title   = nr_opt( 'nr_enquire_title', "Let's start a <em>project.</em>" );
$still_lede    = nr_opt( 'nr_enquire_lede', __( 'Tell me what you have in mind — a place, a person, an hour of the day. A reply within 24 hours.', 'still' ) );
$still_email   = nr_opt( 'nr_email', '' );
$still_studio  = nr_opt( 'nr_studio', nr_opt( 'nr_location', 'Vienna' ) );
$still_services = array( 'Portrait', 'Editorial', 'Architecture', 'Wedding', 'Commercial', 'Other' );
$still_faq     = function_exists( 'nr_faq_items' ) ? nr_faq_items() : array();
$still_sent    = isset( $_GET['nr_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['nr_sent'] ) ) : null;
?>
<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php echo esc_html( $still_eyebrow ); ?></span>
		<h1 class="page-title"><?php echo wp_kses( $still_title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h1>
		<?php if ( $still_lede ) : ?><p class="page-lead"><?php echo esc_html( $still_lede ); ?></p><?php endif; ?>
	</header>

	<?php if ( '1' === $still_sent ) : ?>
		<div class="form-note ok"><?php esc_html_e( 'Thank you — your enquiry has been sent. I will reply within 24 hours.', 'still' ); ?></div>
	<?php elseif ( '0' === $still_sent ) : ?>
		<div class="form-note"><?php esc_html_e( 'Something went wrong. Please try again, or email directly.', 'still' ); ?></div>
	<?php endif; ?>

	<div class="two-col">
		<div>
			<?php if ( $still_email ) : ?><p class="page-lead" style="margin-top:0"><a href="mailto:<?php echo esc_attr( $still_email ); ?>"><?php echo esc_html( $still_email ); ?></a></p><?php endif; ?>
			<dl class="detail-list">
				<div class="row"><dt><?php esc_html_e( 'Response', 'still' ); ?></dt><dd><?php esc_html_e( 'Within 24 hours', 'still' ); ?></dd></div>
				<div class="row"><dt><?php esc_html_e( 'Based in', 'still' ); ?></dt><dd><?php echo esc_html( $still_studio ?: 'Vienna' ); ?></dd></div>
				<div class="row"><dt><?php esc_html_e( 'Available', 'still' ); ?></dt><dd><?php esc_html_e( 'Worldwide · Commissions 2026', 'still' ); ?></dd></div>
			</dl>
		</div>

		<form class="still-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="nr_contact_send">
			<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
			<input type="hidden" name="nr_ref" value="">
			<input type="hidden" name="nr_service" value="">
			<input type="hidden" name="nr_referrer" value="" data-nr-referrer>
			<input type="hidden" name="estimate" value="">
			<div class="hp" aria-hidden="true"><label>Company<input type="text" name="nr_company" tabindex="-1" autocomplete="off"></label></div>

			<div class="field"><label for="f-name"><?php esc_html_e( 'Name', 'still' ); ?></label><input id="f-name" type="text" name="name" required></div>
			<div class="field"><label for="f-email"><?php esc_html_e( 'Email', 'still' ); ?></label><input id="f-email" type="email" name="email" required></div>

			<div class="field"><label><?php esc_html_e( 'Project', 'still' ); ?></label>
				<div class="chips">
					<?php foreach ( $still_services as $i => $s ) : ?>
						<label><input type="radio" name="project_type" value="<?php echo esc_attr( $s ); ?>" <?php checked( 0, $i ); ?>><?php echo esc_html( $s ); ?></label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="field"><label for="f-date"><?php esc_html_e( 'Preferred date', 'still' ); ?></label><input id="f-date" type="date" name="preferred_date"></div>
			<div class="field"><label for="f-notes"><?php esc_html_e( 'Tell me about it', 'still' ); ?></label><textarea id="f-notes" name="notes" required></textarea></div>

			<button type="submit" class="form-submit"><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send enquiry', 'still' ) ) ); ?> →</button>
		</form>
	</div>

	<?php if ( $still_faq ) : ?>
	<section class="faq" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'still' ); ?>">
		<?php foreach ( $still_faq as $f ) : ?>
			<details>
				<summary><?php echo esc_html( $f['q'] ); ?></summary>
				<div class="a"><?php echo wp_kses_post( wpautop( $f['a'] ) ); ?></div>
			</details>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
