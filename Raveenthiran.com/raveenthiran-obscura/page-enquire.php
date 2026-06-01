<?php
/**
 * Template Name: Enquire
 *
 * Merged Booking + Contact experience — a splitscreen window.
 * Left: atmosphere (hero frame + 3-step process + studio facts).
 * Right: the unified inquiry form, with a price-check popover trigger.
 *
 * Posts to admin-post.php → nr_contact_send (see functions.php).
 * Accepts ?service=<slug>&est=<amount> to pre-fill from the calculator.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'enquire';
add_action( 'wp_head', function () {
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids', 'no_found_rows' => true ] );
	if ( $q->have_posts() ) {
		$id = (int) get_post_thumbnail_id( $q->posts[0] );
		$src = $id ? wp_get_attachment_image_url( $id, 'nr-hero' ) : '';
		if ( $src ) { $ss = wp_get_attachment_image_srcset( $id, 'nr-hero' );
			printf( '<link rel="preload" as="image" href="%s"%s imagesizes="(max-width:900px) 100vw, 45vw" fetchpriority="high">' . "\n", esc_url( $src ), $ss ? ' imagesrcset="' . esc_attr( $ss ) . '"' : '' ); }
	}
	wp_reset_postdata();
}, 2 );
get_header();

$quote   = function_exists( 'nr_quote_data' ) ? nr_quote_data() : [ 'types' => [], 'currency' => '€' ];
$cur     = $quote['currency'];

// Pre-fill from the calculator (or any deep link).
$sel  = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
$est  = isset( $_GET['est'] ) ? preg_replace( '/[^0-9.]/', '', wp_unslash( $_GET['est'] ) ) : '';
$ref  = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';

// Left-panel showcase — latest 6 projects, images only (no titles/meta),
// crossfaded like the home hero.
$aside_ids = [];
$hq = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids', 'no_found_rows' => true ] );
foreach ( $hq->posts as $pid ) {
	if ( has_post_thumbnail( $pid ) ) $aside_ids[] = (int) get_post_thumbnail_id( $pid );
}
wp_reset_postdata();

// Chips align with the calculator's shooting types so prefill maps by slug.
$chips = [];
foreach ( $quote['types'] as $t ) $chips[] = [ 'slug' => $t['slug'], 'label' => $t['label'] ];
$chips[] = [ 'slug' => 'other', 'label' => __( 'Other', 'raveenthiran' ) ];
$sel = $sel ?: ( $chips[0]['slug'] ?? 'other' );

?>
<section class="nr-page nr-fullscreen nr-enquire">
	<div class="nr-enquire__split">

		<!-- LEFT · atmosphere -->
		<aside class="nr-enquire__aside">
			<div class="nr-enquire__frame" data-enquire-slider>
				<span class="nr-reg tl"></span><span class="nr-reg tr"></span>
				<span class="nr-reg bl"></span><span class="nr-reg br"></span>
				<?php if ( $aside_ids ) : ?>
					<?php foreach ( $aside_ids as $k => $aid ) : ?>
						<div class="nr-enquire__plate<?php echo $k === 0 ? ' is-active' : ''; ?>" aria-hidden="<?php echo $k === 0 ? 'false' : 'true'; ?>">
							<?php echo wp_get_attachment_image( $aid, 'nr-hero', false, [ 'alt' => '', 'sizes' => '(max-width:900px) 100vw, 45vw', 'loading' => $k === 0 ? 'eager' : 'lazy', 'decoding' => 'async' ] ); ?>
						</div>
					<?php endforeach; ?>
				<?php elseif ( function_exists( 'nr_placeholder' ) ) : ?>
					<?php echo nr_placeholder( 'studio', true, 'auto' ); ?>
				<?php endif; ?>
				<div class="nr-enquire__frame-grad"></div>
			</div>

		</aside>

		<!-- RIGHT · the unified form -->
		<div class="nr-enquire__main">
			<header class="nr-enquire__head">
				<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_enquire_eyebrow', __( 'Enquire · §04', 'raveenthiran' ) ) ); ?></span>
				<h1 class="nr-display nr-display--md"><?php echo wp_kses( nr_opt( 'nr_enquire_title', __( "Let's start a <em>project.</em>", 'raveenthiran' ) ), [ 'em' => [], 'b' => [], 'strong' => [] ] ); ?></h1>
				<p class="nr-enquire__lede"><?php echo esc_html( nr_opt( 'nr_enquire_lede', __( 'Booking and saying hello live in one place. Tell me what you have in mind — or check pricing first.', 'raveenthiran' ) ) ); ?></p>
			</header>

			<form class="nr-form nr-enquire__form" data-enquire-form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nr_contact_send">
				<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
				<input type="text" name="nr_company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="nr-hp">

				<div class="nr-form__row">
					<label><span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
						<input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Your full name', 'raveenthiran' ); ?>">
					</label>
					<label><span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
						<input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required placeholder="<?php esc_attr_e( 'you@example.com', 'raveenthiran' ); ?>">
					</label>
				</div>

				<fieldset class="nr-form__chips">
					<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Project type', 'raveenthiran' ); ?></legend>
					<?php foreach ( $chips as $c ) : $on = $c['slug'] === $sel; ?>
						<label class="nr-chip<?php echo $on ? ' is-on' : ''; ?>" data-chip="<?php echo esc_attr( $c['slug'] ); ?>">
							<input type="radio" name="project_type" value="<?php echo esc_attr( $c['label'] ); ?>" <?php checked( $on ); ?> hidden><?php echo esc_html( $c['label'] ); ?>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<div class="nr-form__row">
					<label><span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Preferred date', 'raveenthiran' ); ?></span>
						<input type="date" name="preferred_date">
					</label>
					<label class="nr-enquire__estimate-wrap">
						<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Your estimate', 'raveenthiran' ); ?></span>
						<span class="nr-enquire__estimate">
							<output data-enquire-estimate><?php echo $est ? esc_html( $cur . number_format_i18n( (float) $est ) ) : '—'; ?></output>
							<button type="button" class="nr-enquire__price-link" data-modal="nr-quote"><?php esc_html_e( 'Check pricing', 'raveenthiran' ); ?> →</button>
						</span>
						<input type="hidden" name="estimate" data-enquire-estimate-input value="<?php echo $est ? esc_attr( $cur . $est ) : ''; ?>">
					</label>
				</div>

				<label class="nr-form__wide"><span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Tell me about the project', 'raveenthiran' ); ?></span>
					<textarea name="notes" rows="4" placeholder="<?php esc_attr_e( 'A few sentences — a place, a person, an hour of the day.', 'raveenthiran' ); ?>"><?php echo $ref ? esc_textarea( sprintf( __( 'Re: %s — I saw this project and would like something in the same spirit.', 'raveenthiran' ), $ref ) ) : ''; ?></textarea>
				</label>

				<?php if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field(); ?>

				<div class="nr-form__foot">
					<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Typical response · < 24h', 'raveenthiran' ); ?></span>
					<?php $nr_bk = nr_opt( 'nr_booking_url', '' ); if ( $nr_bk ) : ?><a class="nr-btn nr-btn--ghost" href="<?php echo esc_url( $nr_bk ); ?>" target="_blank" rel="noopener"><span><?php esc_html_e( 'Book a time', 'raveenthiran' ); ?></span> <span>&rarr;</span></a><?php endif; ?>
						<?php $nr_wa = nr_opt( 'nr_whatsapp', '' ); if ( $nr_wa ) : ?><a class="nr-btn nr-btn--ghost" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $nr_wa ) ); ?>" target="_blank" rel="noopener"><span><?php esc_html_e( 'WhatsApp', 'raveenthiran' ); ?></span></a><?php endif; ?>
					<button type="submit" class="nr-btn nr-btn--primary">
						<span><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send enquiry', 'raveenthiran' ) ) ); ?></span> <span>→</span>
					</button>
				</div>
			</form>

			<section class="nr-enquire__faq" id="faq">
				<span class="nr-eyebrow nr-eyebrow--plain"><?php echo esc_html( nr_opt( 'nr_faq_eyebrow', __( 'FAQ · §05', 'raveenthiran' ) ) ); ?></span>
				<div class="nr-faq__list nr-faq__list--compact">
					<?php foreach ( ( function_exists( 'nr_faq_items' ) ? nr_faq_items() : [] ) as $i => $f ) : ?>
						<div class="nr-faq__item">
							<span class="nr-faq__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div>
								<div class="nr-faq__q"><?php echo esc_html( $f['q'] ); ?></div>
								<div class="nr-faq__a"><?php echo esc_html( $f['a'] ); ?></div>
							</div>
							<button type="button" class="nr-faq__toggle" aria-label="<?php esc_attr_e( 'Toggle answer', 'raveenthiran' ); ?>">+</button>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
	</div>
</section>

<?php get_footer(); ?>
