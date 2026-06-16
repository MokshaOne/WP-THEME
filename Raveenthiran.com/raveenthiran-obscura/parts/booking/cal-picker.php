<?php
/**
 * Booking picker — variable configurator + inline Cal.com calendar.
 * Rendered via nr_cal_picker() / [nr_cal_picker].
 *
 * Flow: pick a shooting type → set hours (bounded per type) → add packages →
 * live price (rate × hours + packages + licence + travel). "Pick a time"
 * mounts the matching per-type Cal inline calendar with the chosen hours as the
 * booking duration (hours × 60 min) and the full scope passed as notes/metadata
 * so it flows on to Cal → n8n → CRM.
 *
 * Each type is bookable only when its slug matches a Cal event key
 * (Obscura → Buchung → Event types). Types without a match still price up but
 * show an editor hint instead of a calendar.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$q = nr_quote_data();
if ( empty( $q['types'] ) ) return;
$cur     = $q['currency'];
$events  = nr_cal_events();
$can_edit = current_user_can( 'edit_theme_options' );

// First type that has a Cal event becomes the default selection.
$default_i = 0;
foreach ( $q['types'] as $i => $t ) {
	if ( isset( $events[ $t['slug'] ] ) ) { $default_i = $i; break; }
}
$d = $q['types'][ $default_i ];
?>
<div class="nr-cal-picker" data-nr-cal-picker
	data-currency="<?php echo esc_attr( $cur ); ?>">

	<div class="nr-cal-picker__config">

		<fieldset class="nr-cal-picker__group">
			<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Shooting type', 'raveenthiran' ); ?></legend>
			<div class="nr-cal-picker__types" role="radiogroup" aria-label="<?php esc_attr_e( 'Shooting type', 'raveenthiran' ); ?>">
				<?php foreach ( $q['types'] as $i => $t ) :
					$ev   = $events[ $t['slug'] ] ?? null;
					$link = $ev ? $ev['link'] : '';
				?>
					<label class="nr-cal-picker__type<?php echo $i === $default_i ? ' is-on' : ''; ?><?php echo $link ? '' : ' is-unbookable'; ?>">
						<input type="radio" name="nr_cp_type" value="<?php echo esc_attr( $t['slug'] ); ?>"
							data-label="<?php echo esc_attr( $t['label'] ); ?>"
							data-rate="<?php echo esc_attr( $t['rate'] ); ?>"
							data-min="<?php echo esc_attr( $t['min'] ); ?>"
							data-max="<?php echo esc_attr( $t['max'] ); ?>"
							data-cal-link="<?php echo esc_attr( $link ); ?>"
							<?php checked( $i, $default_i ); ?> hidden>
						<span class="nr-cal-picker__type-name"><?php echo esc_html( $t['label'] ); ?></span>
						<span class="nr-cal-picker__type-meta"><?php
							echo esc_html( $cur . number_format_i18n( $t['rate'] ) . '/h' );
							if ( ! $link && $can_edit ) echo ' · ' . esc_html__( 'no Cal event', 'raveenthiran' );
						?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="nr-cal-picker__group nr-cal-picker__group--hours">
			<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Hours on set', 'raveenthiran' ); ?></legend>
			<div class="nr-cal-picker__stepper">
				<button type="button" class="nr-cal-picker__step" data-cp-step="-1" aria-label="<?php esc_attr_e( 'Fewer hours', 'raveenthiran' ); ?>">−</button>
				<output class="nr-cal-picker__hours" data-cp-hours aria-live="polite"><?php echo esc_html( (string) (int) $d['min'] ); ?></output>
				<span class="nr-cal-picker__hours-unit"><?php esc_html_e( 'h', 'raveenthiran' ); ?></span>
				<button type="button" class="nr-cal-picker__step" data-cp-step="1" aria-label="<?php esc_attr_e( 'More hours', 'raveenthiran' ); ?>">+</button>
			</div>
			<input type="range" class="nr-cal-picker__range" data-cp-range
				min="<?php echo esc_attr( $d['min'] ); ?>" max="<?php echo esc_attr( $d['max'] ); ?>"
				step="1" value="<?php echo esc_attr( $d['min'] ); ?>"
				aria-label="<?php esc_attr_e( 'Hours on set', 'raveenthiran' ); ?>">
		</fieldset>

		<?php if ( ! empty( $q['extras'] ) ) : ?>
		<fieldset class="nr-cal-picker__group">
			<legend class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Add-on packages', 'raveenthiran' ); ?></legend>
			<div class="nr-cal-picker__extras">
				<?php foreach ( $q['extras'] as $x ) : ?>
					<label class="nr-cal-picker__extra">
						<input type="checkbox" name="nr_cp_extra" value="<?php echo esc_attr( $x['slug'] ); ?>"
							data-price="<?php echo esc_attr( $x['price'] ); ?>"
							data-label="<?php echo esc_attr( $x['label'] ); ?>">
						<span class="nr-cal-picker__extra-box" aria-hidden="true"></span>
						<span class="nr-cal-picker__extra-name"><?php echo esc_html( $x['label'] ); ?></span>
						<span class="nr-cal-picker__extra-price">+<?php echo esc_html( $cur . number_format_i18n( $x['price'] ) ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<?php endif; ?>

		<?php if ( $q['license'] > 0 || $q['per_km'] > 0 ) : ?>
		<fieldset class="nr-cal-picker__group nr-cal-picker__group--inline">
			<?php if ( $q['license'] > 0 ) : ?>
			<label class="nr-cal-picker__extra nr-cal-picker__extra--wide">
				<input type="checkbox" name="nr_cp_license" data-price="<?php echo esc_attr( $q['license'] ); ?>" data-label="<?php esc_attr_e( 'Commercial usage license', 'raveenthiran' ); ?>">
				<span class="nr-cal-picker__extra-box" aria-hidden="true"></span>
				<span class="nr-cal-picker__extra-name"><?php esc_html_e( 'Commercial usage license', 'raveenthiran' ); ?></span>
				<span class="nr-cal-picker__extra-price">+<?php echo esc_html( $cur . number_format_i18n( $q['license'] ) ); ?></span>
			</label>
			<?php endif; ?>
			<?php if ( $q['per_km'] > 0 ) : ?>
			<label class="nr-cal-picker__travel">
				<span class="nr-cal-picker__extra-name"><?php esc_html_e( 'Travel', 'raveenthiran' ); ?></span>
				<span class="nr-cal-picker__travel-input">
					<input type="number" name="nr_cp_km" min="0" step="5" inputmode="numeric"
						data-per-km="<?php echo esc_attr( $q['per_km'] ); ?>" placeholder="0">
					<span class="nr-cal-picker__travel-unit"><?php
						printf( esc_html__( 'km · %s/km', 'raveenthiran' ), esc_html( $cur . rtrim( rtrim( number_format( $q['per_km'], 2 ), '0' ), '.' ) ) );
					?></span>
				</span>
			</label>
			<?php endif; ?>
		</fieldset>
		<?php endif; ?>

	</div><?php /* /config */ ?>

	<footer class="nr-cal-picker__foot">
		<div class="nr-cal-picker__total-row">
			<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Estimated from', 'raveenthiran' ); ?></span>
			<output class="nr-cal-picker__sum" data-cp-sum><?php echo esc_html( $cur . number_format_i18n( $d['rate'] * $d['min'] ) ); ?></output>
		</div>
		<p class="nr-cal-picker__note" data-cp-breakdown></p>
		<button type="button" class="nr-btn nr-btn--primary nr-cal-picker__go" data-cp-go aria-expanded="false"
			data-msg-nobook="<?php esc_attr_e( 'This type is enquiry-only — use the form below.', 'raveenthiran' ); ?>">
			<span><?php esc_html_e( 'Pick a time', 'raveenthiran' ); ?></span> <span aria-hidden="true">→</span>
		</button>
		<p class="nr-cal-picker__hint" data-cp-hint hidden></p>
	</footer>

	<div class="nr-cal-picker__cal nr-cal-inline" data-cp-host data-loading="<?php esc_attr_e( 'Loading calendar…', 'raveenthiran' ); ?>" role="region" aria-label="<?php esc_attr_e( 'Choose a date and time', 'raveenthiran' ); ?>" hidden></div>

	<noscript>
		<ul class="nr-cal-picker__fallback">
			<?php foreach ( $q['types'] as $t ) :
				$ev = $events[ $t['slug'] ] ?? null; if ( ! $ev ) continue; ?>
				<li><a href="<?php echo esc_url( nr_cal_origin() . '/' . ltrim( $ev['link'], '/' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $t['label'] ); ?> ↗</a></li>
			<?php endforeach; ?>
		</ul>
	</noscript>
</div>
