<?php
/**
 * Template Name: FAQ
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'default';
get_header();

$faqs = [
	[
		__( 'How far in advance should I book?', 'raveenthiran' ),
		__( 'For portrait sessions I recommend booking 2–4 weeks in advance. Editorial commissions and weddings require a minimum of 6–8 weeks, depending on the scope and travel involved.', 'raveenthiran' ),
	],
	[
		__( 'Do you travel for shoots?', 'raveenthiran' ),
		__( 'Yes. I am based in Vienna but regularly work across Europe and beyond. Travel costs are invoiced separately at cost. For international projects, a travel day rate applies.', 'raveenthiran' ),
	],
	[
		__( 'What is your editing style?', 'raveenthiran' ),
		__( 'I work in a quiet, analogue-feeling style — warm shadows, restrained contrast, a sense of stillness. I do not over-retouch. The aim is that the photographs still look like photographs.', 'raveenthiran' ),
	],
	[
		__( 'How are images delivered?', 'raveenthiran' ),
		__( 'Selects are delivered via a private online gallery (Pixieset) within 5–7 business days. Files are delivered as high-resolution JPEGs. Print-ready TIFFs and RAW files are available on request.', 'raveenthiran' ),
	],
	[
		__( 'Can I use the images for commercial purposes?', 'raveenthiran' ),
		__( 'Usage rights are specified in the contract. Personal portrait sessions include personal use only. Editorial commissions include publication rights for the agreed outlet. Broader commercial licensing is available separately.', 'raveenthiran' ),
	],
	[
		__( 'What if I need to reschedule?', 'raveenthiran' ),
		__( 'Rescheduling is possible without penalty up to 7 days before the shoot date. Cancellations within 48 hours of the session are subject to the full session fee.', 'raveenthiran' ),
	],
];
?>
<section class="nr-page nr-fullscreen nr-faq">
	<div class="nr-page__head">
		<div>
			<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_faq_eyebrow', __( 'FAQ · §05', 'raveenthiran' ) ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo wp_kses( nr_opt( 'nr_faq_title', __( 'Common <em>questions.</em>', 'raveenthiran' ) ), [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		</div>
		<a class="nr-btn nr-btn--primary nr-book-trigger" href="<?php echo esc_url( nr_opt( 'nr_booking_url', home_url( '/booking' ) ) ); ?>" data-modal="nr-booking">
			<span><?php echo esc_html( nr_opt( 'nr_book_label', __( 'Book a shoot', 'raveenthiran' ) ) ); ?></span> <span>→</span>
		</a>
	</div>

	<div class="nr-faq__list">
		<?php foreach ( $faqs as $i => $faq ) : ?>
			<div class="nr-faq__item<?php echo $i === 0 ? ' is-open' : ''; ?>">
				<span class="nr-faq__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div>
					<div class="nr-faq__q"><?php echo esc_html( $faq[0] ); ?></div>
					<div class="nr-faq__a"><?php echo esc_html( $faq[1] ); ?></div>
				</div>
				<button type="button" class="nr-faq__toggle" aria-label="<?php esc_attr_e( 'Toggle answer', 'raveenthiran' ); ?>">+</button>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php get_footer(); ?>
