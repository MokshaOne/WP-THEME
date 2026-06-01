<?php
/**
 * Price-check popover — interactive estimate calculator.
 * Opens from any [data-modal="nr-quote"] trigger. Reuses the global
 * .nr-modal open/close + focus-trap wiring in assets/js/theme.js.
 * Pricing comes from nr_quote_data() (ACF → option → defaults).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$q = nr_quote_data();
if ( empty( $q['types'] ) ) return;
$cur = $q['currency'];
?>
<div id="nr-quote" class="nr-modal nr-quote" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="nr-quote-title">
	<div class="nr-modal__panel nr-quote__panel">
		<button type="button" class="nr-modal__close" data-modal-close aria-label="<?php esc_attr_e( 'Close', 'raveenthiran' ); ?>">✕</button>

		<div class="nr-quote__head">
			<span class="nr-eyebrow"><?php esc_html_e( 'Price check', 'raveenthiran' ); ?></span>
			<h2 id="nr-quote-title"><?php esc_html_e( 'Estimate a', 'raveenthiran' ); ?> <em><?php esc_html_e( 'shoot.', 'raveenthiran' ); ?></em></h2>
			<p><?php esc_html_e( 'A guide, not a final quote — every commission is tailored. Pick a type, add what you need, see a number.', 'raveenthiran' ); ?></p>
		</div>

		<form class="nr-quote__form" data-quote-form
			data-currency="<?php echo esc_attr( $cur ); ?>"
			data-enquire-url="<?php echo esc_url( nr_enquire_url() ); ?>">

			<fieldset class="nr-quote__group">
				<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Shooting type', 'raveenthiran' ); ?></legend>
				<div class="nr-quote__types">
					<?php foreach ( $q['types'] as $i => $t ) : ?>
						<label class="nr-quote__type<?php echo $i === 0 ? ' is-on' : ''; ?>">
							<input type="radio" name="nr_q_type" value="<?php echo esc_attr( $t['slug'] ); ?>"
								data-base="<?php echo esc_attr( $t['base'] ); ?>"
								data-label="<?php echo esc_attr( $t['label'] ); ?>"
								data-hours="<?php echo esc_attr( $t['hours'] ); ?>"
								<?php checked( $i, 0 ); ?> hidden>
							<span class="nr-quote__type-name"><?php echo esc_html( $t['label'] ); ?></span>
							<span class="nr-quote__type-meta">
								<?php echo esc_html( $cur . number_format_i18n( $t['base'] ) ); ?><?php
								if ( $t['hours'] ) echo ' · ' . esc_html( rtrim( rtrim( number_format( $t['hours'], 1 ), '0' ), '.' ) ) . 'h'; ?>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<?php if ( ! empty( $q['extras'] ) ) : ?>
			<fieldset class="nr-quote__group">
				<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Add-ons', 'raveenthiran' ); ?></legend>
				<div class="nr-quote__extras">
					<?php foreach ( $q['extras'] as $x ) : ?>
						<label class="nr-quote__extra">
							<input type="checkbox" name="nr_q_extra" value="<?php echo esc_attr( $x['slug'] ); ?>"
								data-price="<?php echo esc_attr( $x['price'] ); ?>"
								data-label="<?php echo esc_attr( $x['label'] ); ?>">
							<span class="nr-quote__extra-box" aria-hidden="true"></span>
							<span class="nr-quote__extra-name"><?php echo esc_html( $x['label'] ); ?></span>
							<span class="nr-quote__extra-price">+<?php echo esc_html( $cur . number_format_i18n( $x['price'] ) ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php endif; ?>

			<fieldset class="nr-quote__group nr-quote__group--inline">
				<?php if ( $q['license'] > 0 ) : ?>
				<label class="nr-quote__extra nr-quote__extra--wide">
					<input type="checkbox" name="nr_q_license" data-price="<?php echo esc_attr( $q['license'] ); ?>" data-label="<?php esc_attr_e( 'Commercial usage license', 'raveenthiran' ); ?>">
					<span class="nr-quote__extra-box" aria-hidden="true"></span>
					<span class="nr-quote__extra-name"><?php esc_html_e( 'Commercial usage license', 'raveenthiran' ); ?></span>
					<span class="nr-quote__extra-price">+<?php echo esc_html( $cur . number_format_i18n( $q['license'] ) ); ?></span>
				</label>
				<?php endif; ?>

				<?php if ( $q['per_km'] > 0 ) : ?>
				<label class="nr-quote__travel">
					<span class="nr-quote__extra-name"><?php esc_html_e( 'Travel', 'raveenthiran' ); ?></span>
					<span class="nr-quote__travel-input">
						<input type="number" name="nr_q_km" min="0" step="5" inputmode="numeric"
							data-per-km="<?php echo esc_attr( $q['per_km'] ); ?>" placeholder="0">
						<span class="nr-quote__travel-unit"><?php
							printf( esc_html__( 'km · %s/km', 'raveenthiran' ), esc_html( $cur . rtrim( rtrim( number_format( $q['per_km'], 2 ), '0' ), '.' ) ) );
						?></span>
					</span>
				</label>
				<?php endif; ?>
			</fieldset>

			<div class="nr-quote__total">
				<div class="nr-quote__total-row">
					<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Estimated from', 'raveenthiran' ); ?></span>
					<output class="nr-quote__sum" data-quote-sum><?php echo esc_html( $cur . number_format_i18n( $q['types'][0]['base'] ) ); ?></output>
				</div>
				<p class="nr-quote__note" data-quote-breakdown></p>
			</div>

			<div class="nr-quote__actions">
				<button type="button" class="nr-btn nr-btn--primary nr-btn--block" data-quote-apply>
					<span><?php esc_html_e( 'Use this in my enquiry', 'raveenthiran' ); ?></span> <span>→</span>
				</button>
				<button type="button" class="nr-btn nr-btn--ghost nr-btn--block" data-modal-close>
					<span><?php esc_html_e( 'Close', 'raveenthiran' ); ?></span>
				</button>
			</div>
		</form>
	</div>
</div>
