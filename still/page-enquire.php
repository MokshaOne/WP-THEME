<?php
/**
 * Enquire — the single "get in touch" page: studio contact details, a live
 * price estimate (engine: nr_quote_data), the booking form (→ nr_contact_send)
 * and the FAQ. Replaces the standalone Contact page. Auto-applies to slug
 * "enquire".
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_eyebrow = nr_opt( 'nr_enquire_eyebrow', __( 'Enquire', 'still' ) );
$still_title   = nr_opt( 'nr_enquire_title', "Let's start a <em>project.</em>" );
$still_lede    = nr_opt( 'nr_enquire_lede', __( 'Tell me what you have in mind — a place, a person, an hour of the day. Estimate below; a reply within 24 hours.', 'still' ) );

/* contact details (this page is now also the contact page) */
$still_email = nr_opt( 'nr_email', '' );
$still_phone = nr_opt( 'nr_phone', '' );
$still_wa    = nr_opt( 'nr_whatsapp', '' );
$still_loc   = nr_opt( 'nr_studio', nr_opt( 'nr_location', 'Vienna' ) );
$still_socials = array_filter( array(
	'Instagram' => nr_opt( 'nr_instagram', '' ),
	'LinkedIn'  => nr_opt( 'nr_linkedin', '' ),
	'Behance'   => nr_opt( 'nr_behance', '' ),
	'Vimeo'     => nr_opt( 'nr_vimeo', '' ),
) );

/* price engine */
$still_q   = function_exists( 'nr_quote_data' ) ? nr_quote_data() : array( 'types' => array(), 'extras' => array(), 'license' => 0, 'per_km' => 0, 'currency' => '€' );
$still_cur = $still_q['currency'] ?? '€';
$still_faq = function_exists( 'nr_faq_items' ) ? nr_faq_items() : array();
$still_sent = isset( $_GET['nr_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['nr_sent'] ) ) : null;
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
		<aside>
			<dl class="contact-list">
				<?php if ( $still_email ) : ?><div class="row"><dt><?php esc_html_e( 'Email', 'still' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $still_email ); ?>"><?php echo esc_html( $still_email ); ?></a></dd></div><?php endif; ?>
				<?php if ( $still_phone ) : ?><div class="row"><dt><?php esc_html_e( 'Phone', 'still' ); ?></dt><dd><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $still_phone ) ); ?>"><?php echo esc_html( $still_phone ); ?></a></dd></div><?php endif; ?>
				<?php if ( $still_wa ) : ?><div class="row"><dt>WhatsApp</dt><dd><a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $still_wa ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $still_wa ); ?></a></dd></div><?php endif; ?>
				<div class="row"><dt><?php esc_html_e( 'Studio', 'still' ); ?></dt><dd><?php echo esc_html( $still_loc ); ?></dd></div>
				<?php if ( $still_socials ) : ?><div class="row"><dt><?php esc_html_e( 'Elsewhere', 'still' ); ?></dt><dd><?php
					$still_out = array();
					foreach ( $still_socials as $k => $u ) { $still_out[] = '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( $k ) . '</a>'; }
					echo wp_kses_post( implode( ' &nbsp;·&nbsp; ', $still_out ) );
				?></dd></div><?php endif; ?>
				<div class="row"><dt><?php esc_html_e( 'Response', 'still' ); ?></dt><dd><?php esc_html_e( 'Within 24 hours', 'still' ); ?></dd></div>
			</dl>
		</aside>

		<form class="still-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-price-engine data-currency="<?php echo esc_attr( $still_cur ); ?>">
			<input type="hidden" name="action" value="nr_contact_send">
			<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
			<input type="hidden" name="nr_ref" value="" data-breakdown>
			<input type="hidden" name="nr_service" value="" data-slug>
			<input type="hidden" name="nr_referrer" value="" data-nr-referrer>
			<input type="hidden" name="estimate" value="" data-estimate-input>
			<div class="hp" aria-hidden="true"><label>Company<input type="text" name="nr_company" tabindex="-1" autocomplete="off"></label></div>

			<?php if ( ! empty( $still_q['types'] ) ) : ?>
			<div class="field"><label><?php esc_html_e( 'Project', 'still' ); ?></label>
				<div class="chips">
					<?php foreach ( $still_q['types'] as $i => $t ) : ?>
						<label><input type="radio" name="project_type" value="<?php echo esc_attr( $t['label'] ); ?>" data-base="<?php echo esc_attr( $t['base'] ); ?>" data-slug="<?php echo esc_attr( $t['slug'] ); ?>" <?php checked( 0, $i ); ?>><?php echo esc_html( $t['label'] ); ?> · <?php echo esc_html( $still_cur . number_format_i18n( (float) $t['base'] ) ); ?></label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $still_q['extras'] ) ) : ?>
			<div class="field"><label><?php esc_html_e( 'Add-ons', 'still' ); ?></label>
				<div class="chips">
					<?php foreach ( $still_q['extras'] as $x ) : ?>
						<label><input type="checkbox" name="addons[]" value="<?php echo esc_attr( $x['label'] ); ?>" data-price="<?php echo esc_attr( $x['price'] ); ?>"><?php echo esc_html( $x['label'] ); ?> · +<?php echo esc_html( $still_cur . number_format_i18n( (float) $x['price'] ) ); ?></label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="field field--inline">
				<?php if ( ! empty( $still_q['license'] ) ) : ?>
				<label class="toggle"><input type="checkbox" name="license" value="1" data-price="<?php echo esc_attr( $still_q['license'] ); ?>"> <?php printf( esc_html__( 'Commercial license · +%s', 'still' ), esc_html( $still_cur . number_format_i18n( (float) $still_q['license'] ) ) ); ?></label>
				<?php endif; ?>
				<?php if ( ! empty( $still_q['per_km'] ) ) : ?>
				<label class="km"><span><?php esc_html_e( 'Travel (km)', 'still' ); ?></span><input type="number" name="travel_km" min="0" step="1" value="0" data-per-km="<?php echo esc_attr( $still_q['per_km'] ); ?>"></label>
				<?php endif; ?>
			</div>

			<div class="estimate">
				<span class="estimate__k"><?php esc_html_e( 'Estimate', 'still' ); ?></span>
				<output class="estimate__v" data-estimate>—</output>
				<span class="estimate__note"><?php esc_html_e( 'Indicative — final quote confirmed by email.', 'still' ); ?></span>
			</div>

			<div class="field"><label for="f-name"><?php esc_html_e( 'Name', 'still' ); ?></label><input id="f-name" type="text" name="name" required></div>
			<div class="field"><label for="f-email"><?php esc_html_e( 'Email', 'still' ); ?></label><input id="f-email" type="email" name="email" required></div>
			<div class="field"><label for="f-date"><?php esc_html_e( 'Preferred date', 'still' ); ?></label><input id="f-date" type="date" name="preferred_date"></div>
			<div class="field"><label for="f-notes"><?php esc_html_e( 'Tell me about it', 'still' ); ?></label><textarea id="f-notes" name="notes" required></textarea></div>

			<button type="submit" class="form-submit"><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send enquiry', 'still' ) ) ); ?> →</button>
		</form>
	</div>

	<?php if ( $still_faq ) : ?>
	<section class="faq" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'still' ); ?>">
		<?php foreach ( $still_faq as $f ) : ?>
			<details><summary><?php echo esc_html( $f['q'] ); ?></summary><div class="a"><?php echo wp_kses_post( wpautop( $f['a'] ) ); ?></div></details>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
