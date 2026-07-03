<?php
/**
 * Template Name: Enquire
 * The full booking flow — enquiry form + live price calculator + FAQ.
 * The calculator reads the same pricing config as Obscura (ACF options
 * page "Pricing & Quote", with built-in defaults), pre-fills from
 * ?service=<slug>, and the submitted estimate is recomputed server-side
 * and answered with a branded PDF (inc/enquiry.php + inc/pdf.php).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$sent    = isset( $_GET['sent'] ) ? sanitize_text_field( $_GET['sent'] ) : '';
	$quote   = function_exists( 'nr_quote_data' ) ? nr_quote_data() : [ 'types' => [], 'extras' => [], 'per_km' => 0, 'currency' => '€' ];
	$faq     = function_exists( 'nr_faq_items' ) ? nr_faq_items() : [];
	$prefill = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
	if ( $prefill && ! in_array( $prefill, wp_list_pluck( $quote['types'], 'slug' ), true ) ) $prefill = '';
	$first_slug = $prefill ?: ( $quote['types'][0]['slug'] ?? '' );
?>

<article class="nr-sheet nr-enquire">
	<div class="nr-sheet__inner nr-sheet__inner--wide">

		<header class="nr-enquire__head">
			<h1><?php esc_html_e( 'Enquire', 'raveenthiran-silence' ); ?> <em><?php esc_html_e( '— tell me about the shoot', 'raveenthiran-silence' ); ?></em></h1>
			<?php if ( get_the_content() ) : ?><div class="nr-prose"><?php the_content(); ?></div><?php endif; ?>
		</header>

		<?php if ( $sent === '1' ) : ?>
			<p class="nr-note" role="status"><?php esc_html_e( 'Thank you — your enquiry was sent. If you used the calculator, a PDF estimate is in your inbox. I reply within a day.', 'raveenthiran-silence' ); ?></p>
		<?php elseif ( $sent === '0' ) : ?>
			<p class="nr-note nr-note--err" role="alert"><?php esc_html_e( 'Something went wrong — please check the fields and try again.', 'raveenthiran-silence' ); ?></p>
		<?php endif; ?>

		<form class="nr-enquire__grid" method="post" action="" data-quote data-per-km="<?php echo esc_attr( $quote['per_km'] ); ?>" data-currency="<?php echo esc_attr( $quote['currency'] ); ?>">
			<?php wp_nonce_field( 'nr_enquiry', 'nr_enquiry_nonce' ); ?>
			<p class="nr-hp"><label><?php esc_html_e( 'Website', 'raveenthiran-silence' ); ?><input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>

			<?php /* ── left: the conversation ─────────────────────── */ ?>
			<div class="nr-enquire__col">
				<h2 class="nr-eyebrow">01 — <?php esc_html_e( 'The conversation', 'raveenthiran-silence' ); ?></h2>
				<div class="nr-form">
					<label>
						<span><?php esc_html_e( 'Name', 'raveenthiran-silence' ); ?></span>
						<input type="text" name="name" required autocomplete="name">
					</label>
					<label>
						<span><?php esc_html_e( 'Email', 'raveenthiran-silence' ); ?></span>
						<input type="email" name="email" required autocomplete="email">
					</label>
					<label>
						<span><?php esc_html_e( 'Preferred date (optional)', 'raveenthiran-silence' ); ?></span>
						<input type="date" name="shoot_date">
					</label>
					<label>
						<span><?php esc_html_e( 'About the shoot', 'raveenthiran-silence' ); ?></span>
						<textarea name="message" rows="7" required placeholder="<?php esc_attr_e( 'Location, mood, references, how you found me …', 'raveenthiran-silence' ); ?>"></textarea>
					</label>
					<?php if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field(); ?>
				</div>
			</div>

			<?php /* ── right: the estimate ─────────────────────────── */ ?>
			<aside class="nr-enquire__col nr-quote">
				<h2 class="nr-eyebrow">02 — <?php esc_html_e( 'The estimate', 'raveenthiran-silence' ); ?> <i>(<?php esc_html_e( 'optional', 'raveenthiran-silence' ); ?>)</i></h2>

				<?php if ( $quote['types'] ) : ?>
				<fieldset class="nr-quote__types">
					<legend class="nr-quote__label"><?php esc_html_e( 'Shooting type', 'raveenthiran-silence' ); ?></legend>
					<?php foreach ( $quote['types'] as $t ) : ?>
						<label class="nr-quote__type">
							<input type="radio" name="quote_type" value="<?php echo esc_attr( $t['slug'] ); ?>"
								data-base="<?php echo esc_attr( $t['base'] ); ?>" <?php checked( $t['slug'], $first_slug ); ?>>
							<span class="nr-quote__type-t"><?php echo esc_html( $t['label'] ); ?></span>
							<span class="nr-quote__type-m"><?php echo esc_html( $quote['currency'] . number_format_i18n( $t['base'] ) ); ?> · <?php echo esc_html( $t['hours'] ); ?>h</span>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<?php if ( $quote['extras'] ) : ?>
				<fieldset class="nr-quote__extras">
					<legend class="nr-quote__label"><?php esc_html_e( 'Extras', 'raveenthiran-silence' ); ?></legend>
					<?php foreach ( $quote['extras'] as $x ) : ?>
						<label class="nr-quote__extra">
							<input type="checkbox" name="quote_extras[]" value="<?php echo esc_attr( $x['slug'] ); ?>" data-price="<?php echo esc_attr( $x['price'] ); ?>">
							<span><?php echo esc_html( $x['label'] ); ?></span>
							<em>+<?php echo esc_html( $quote['currency'] . number_format_i18n( $x['price'] ) ); ?></em>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<?php endif; ?>

				<label class="nr-quote__km">
					<span class="nr-quote__label"><?php esc_html_e( 'Travel outside Vienna (km)', 'raveenthiran-silence' ); ?></span>
					<input type="number" name="quote_km" min="0" step="10" value="0" inputmode="numeric">
				</label>

				<div class="nr-quote__total">
					<span class="nr-quote__label"><?php esc_html_e( 'Estimated investment', 'raveenthiran-silence' ); ?></span>
					<output class="nr-quote__sum" data-quote-total aria-live="polite"><?php echo esc_html( $quote['currency'] ); ?>0</output>
					<small><?php esc_html_e( 'Non-binding — the final quote follows a short call. A PDF of this estimate lands in your inbox with the reply.', 'raveenthiran-silence' ); ?></small>
				</div>
				<?php endif; ?>
			</aside>

			<div class="nr-enquire__send">
				<button type="submit"><?php esc_html_e( 'Send the enquiry', 'raveenthiran-silence' ); ?> —</button>
			</div>
		</form>

		<?php if ( $faq ) : ?>
		<section class="nr-faq">
			<h2 class="nr-eyebrow"><?php esc_html_e( 'Questions, answered', 'raveenthiran-silence' ); ?></h2>
			<?php foreach ( $faq as $item ) : ?>
				<details class="nr-faq__item">
					<summary><?php echo esc_html( $item['q'] ); ?><i aria-hidden="true">+</i></summary>
					<p><?php echo esc_html( $item['a'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</section>
		<?php endif; ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
