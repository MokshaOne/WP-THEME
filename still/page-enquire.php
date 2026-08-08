<?php
/**
 * Enquire — contact details, a live price-estimate form, and an FAQ.
 * Auto-applies to the Page with slug "enquire" (created on theme activation).
 * Vertical layout, matching the rest of the theme. The form posts to
 * admin-post.php (still_enquiry); the estimate is computed client-side.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_q     = still_quote_data();
$still_cur   = $still_q['currency'];
$still_faq   = function_exists( 'still_faq_items' ) ? still_faq_items() : array();
$still_email = get_option( 'admin_email' );
$still_sent  = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : '';
?>
<main id="main" class="page-wrap enquire">
	<header class="page-head">
		<span class="label"><?php esc_html_e( 'Enquire', 'still' ); ?></span>
		<h1 class="page-title"><?php esc_html_e( 'Start a project', 'still' ); ?></h1>
		<p class="page-lead"><?php esc_html_e( 'Tell me what you have in mind. Get an instant estimate below — a firm quote follows within 24 hours.', 'still' ); ?></p>
	</header>

	<?php if ( 'sent' === $still_sent ) : ?>
		<div class="form-note ok"><?php esc_html_e( 'Thank you — your enquiry has been sent. I’ll be in touch shortly.', 'still' ); ?></div>
	<?php elseif ( 'error' === $still_sent ) : ?>
		<div class="form-note bad"><?php printf( wp_kses( __( 'Something went wrong. Please email me directly at <a href="mailto:%1$s">%1$s</a>.', 'still' ), array( 'a' => array( 'href' => array() ) ) ), esc_attr( $still_email ) ); ?></div>
	<?php endif; ?>

	<div class="enquire-grid">

		<form class="still-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-price-engine data-currency="<?php echo esc_attr( $still_cur ); ?>">
			<input type="hidden" name="action" value="still_enquiry">
			<?php wp_nonce_field( 'still_enquiry', 'still_nonce' ); ?>
			<input type="hidden" name="estimate" value="" data-estimate-input>
			<input type="hidden" name="breakdown" value="" data-breakdown>
			<!-- honeypot -->
			<div class="hp" aria-hidden="true"><label><?php esc_html_e( 'Company', 'still' ); ?><input type="text" name="still_company" tabindex="-1" autocomplete="off"></label></div>

			<div class="field">
				<span class="field-label"><?php esc_html_e( 'Project type', 'still' ); ?></span>
				<div class="chips">
					<?php foreach ( $still_q['types'] as $i => $t ) : ?>
						<label class="chip"><input type="radio" name="project_type" value="<?php echo esc_attr( $t['label'] ); ?>" data-base="<?php echo esc_attr( $t['base'] ); ?>" <?php checked( 0, $i ); ?>><span><?php echo esc_html( $t['label'] ); ?> · <?php echo esc_html( $still_cur . number_format_i18n( (float) $t['base'] ) ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( ! empty( $still_q['extras'] ) ) : ?>
			<div class="field">
				<span class="field-label"><?php esc_html_e( 'Add-ons', 'still' ); ?></span>
				<div class="chips">
					<?php foreach ( $still_q['extras'] as $x ) : ?>
						<label class="chip"><input type="checkbox" name="addons[]" value="<?php echo esc_attr( $x['label'] ); ?>" data-price="<?php echo esc_attr( $x['price'] ); ?>"><span><?php echo esc_html( $x['label'] ); ?> · +<?php echo esc_html( $still_cur . number_format_i18n( (float) $x['price'] ) ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<div class="field field--inline">
				<?php if ( ! empty( $still_q['license'] ) ) : ?>
					<label class="chip"><input type="checkbox" name="license" value="1" data-price="<?php echo esc_attr( $still_q['license'] ); ?>"><span><?php printf( esc_html__( 'Commercial licence · +%s', 'still' ), esc_html( $still_cur . number_format_i18n( (float) $still_q['license'] ) ) ); ?></span></label>
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
			<div class="field"><label for="f-notes"><?php esc_html_e( 'Tell me about it', 'still' ); ?></label><textarea id="f-notes" name="notes" rows="5" required></textarea></div>

			<button type="submit" class="form-submit"><?php esc_html_e( 'Send enquiry', 'still' ); ?> →</button>
		</form>

		<aside class="enquire-side">
			<div class="side-estimate" aria-hidden="true">
				<span class="estimate__k"><?php esc_html_e( 'Estimate', 'still' ); ?></span>
				<span class="estimate__v" data-estimate>—</span>
				<span class="estimate__note"><?php esc_html_e( 'Updates as you choose. Final quote by email.', 'still' ); ?></span>
			</div>
			<div class="contact-list">
				<div class="row"><span class="k"><?php esc_html_e( 'Email', 'still' ); ?></span><a href="mailto:<?php echo esc_attr( $still_email ); ?>"><?php echo esc_html( $still_email ); ?></a></div>
				<div class="row"><span class="k"><?php esc_html_e( 'Studio', 'still' ); ?></span><span><?php esc_html_e( 'Vienna, AT', 'still' ); ?></span></div>
				<div class="row"><span class="k"><?php esc_html_e( 'Response', 'still' ); ?></span><span><?php esc_html_e( 'Within 24 hours', 'still' ); ?></span></div>
			</div>

			<?php if ( trim( get_the_content() ) !== '' ) : ?>
				<div class="prose enquire-note"><?php while ( have_posts() ) { the_post(); the_content(); } ?></div>
			<?php endif; ?>
		</aside>
	</div>

	<?php if ( $still_faq ) : ?>
	<section class="faq" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'still' ); ?>">
		<h2 class="faq-title"><?php esc_html_e( 'Questions', 'still' ); ?></h2>
		<?php foreach ( $still_faq as $f ) : ?>
			<details><summary><?php echo esc_html( $f['q'] ); ?></summary><div class="a"><?php echo wp_kses_post( wpautop( $f['a'] ) ); ?></div></details>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
