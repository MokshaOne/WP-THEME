<?php
/**
 * Template Name: Enquire
 * moksha1one — Book me (HORIZONTAL): atmosphere → the unified enquiry form →
 * FAQ. Vertical wheel travels sideways on desktop; on mobile the panels stack
 * and scroll naturally so the form is fully usable. Every form hook is
 * preserved (admin-post.php → nr_contact_send; theme.js chips / estimate /
 * price popover; turnstile), so the mailer keeps working unchanged.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'enquire';
get_header();

$quote = function_exists( 'nr_quote_data' ) ? nr_quote_data() : [ 'types' => [], 'currency' => '€' ];
$cur   = $quote['currency'];
$sel   = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
$est   = isset( $_GET['est'] ) ? preg_replace( '/[^0-9.]/', '', wp_unslash( $_GET['est'] ) ) : '';
$ref   = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
$email = nr_opt( 'nr_email', '' );

$chips = [];
foreach ( $quote['types'] as $t ) $chips[] = [ 'slug' => $t['slug'], 'label' => $t['label'] ];
$chips[] = [ 'slug' => 'other', 'label' => __( 'Other', 'raveenthiran' ) ];
$sel = $sel ?: ( $chips[0]['slug'] ?? 'other' );

$faq = function_exists( 'nr_faq_items' ) ? nr_faq_items() : [];
?>

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<?php /* ── atmosphere / intro ───────────────────────── */ ?>
		<section class="mk-panel mk-panel--intro">
			<span class="mk-panel__index">01 / 03</span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( nr_opt( 'nr_enquire_eyebrow', __( 'Book me', 'raveenthiran' ) ) ); ?></p>
			<h1 class="void-display-hero" style="text-align:left"><?php echo wp_kses( nr_opt( 'nr_enquire_title', __( "Let's start a <span class='void-gold-ital'>project.</span>", 'raveenthiran' ) ), [ 'em' => [], 'span' => [ 'class' => [] ] ] ); ?></h1>
			<p class="void-mono" style="margin-top:18px;max-width:48ch;text-transform:none;letter-spacing:0;line-height:1.6"><?php echo esc_html( nr_opt( 'nr_enquire_lede', __( 'Tell me what you have in mind — a place, a person, an hour of the day. Response within 24 hours.', 'raveenthiran' ) ) ); ?></p>
			<dl class="mk-contact__grid" style="margin-top:28px;max-width:640px">
				<div><dt class="void-label-luxury"><?php esc_html_e( 'STUDIO', 'raveenthiran' ); ?></dt><dd class="mk-contact__val"><?php echo esc_html( nr_opt( 'nr_studio', nr_opt( 'nr_location', 'Wien' ) ) ); ?></dd></div>
				<?php if ( $email ) : ?><div><dt class="void-label-luxury"><?php esc_html_e( 'EMAIL', 'raveenthiran' ); ?></dt><dd><button type="button" class="mk-contact__link st-foot__email" data-copy="<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></button></dd></div><?php endif; ?>
			</dl>
		</section>

		<?php /* ── the form (all hooks preserved) ────────────── */ ?>
		<section class="mk-panel mk-panel--form">
			<span class="mk-panel__index">02 / 03</span>
			<form class="st-form nr-form nr-enquire__form mk-form" data-enquire-form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nr_contact_send">
				<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
				<input type="text" name="nr_company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="nr-hp">
				<input type="hidden" name="nr_ref" value="<?php echo esc_attr( $ref ); ?>">
				<input type="hidden" name="nr_service" value="<?php echo esc_attr( $sel ); ?>">
				<input type="hidden" name="nr_referrer" value="" data-nr-referrer>

				<div class="st-form__row">
					<label class="st-field st-float"><input type="text" name="name" autocomplete="name" required placeholder=" "><span class="st-field__l"><?php esc_html_e( 'Full name', 'raveenthiran' ); ?></span></label>
					<label class="st-field st-float"><input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required placeholder=" "><span class="st-field__l"><?php esc_html_e( 'Email address', 'raveenthiran' ); ?></span></label>
				</div>

				<fieldset class="nr-form__chips st-chips">
					<legend class="st-field__l"><?php esc_html_e( 'Project type', 'raveenthiran' ); ?></legend>
					<?php foreach ( $chips as $c ) : $on = $c['slug'] === $sel; ?>
						<label class="nr-chip<?php echo $on ? ' is-on' : ''; ?>" data-chip="<?php echo esc_attr( $c['slug'] ); ?>">
							<input type="radio" name="project_type" value="<?php echo esc_attr( $c['label'] ); ?>" <?php checked( $on ); ?> hidden><?php echo esc_html( $c['label'] ); ?>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<div class="st-form__row">
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Preferred date', 'raveenthiran' ); ?></span><input type="date" name="preferred_date"></label>
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Your estimate', 'raveenthiran' ); ?></span>
						<span class="st-estimate">
							<output data-enquire-estimate><?php echo $est ? esc_html( $cur . number_format_i18n( (float) $est ) ) : '—'; ?></output>
							<button type="button" class="st-estimate__link" data-modal="nr-quote"><?php esc_html_e( 'Check pricing', 'raveenthiran' ); ?> →</button>
						</span>
						<input type="hidden" name="estimate" data-enquire-estimate-input value="<?php echo $est ? esc_attr( $cur . $est ) : ''; ?>">
					</label>
				</div>

				<label class="st-field st-field--wide st-float">
					<textarea name="notes" rows="3" placeholder=" "><?php echo $ref ? esc_textarea( sprintf( __( 'Re: %s — I saw this project and would like something in the same spirit.', 'raveenthiran' ), $ref ) ) : ''; ?></textarea>
					<span class="st-field__l"><?php esc_html_e( 'Project scope & details', 'raveenthiran' ); ?></span>
				</label>

				<?php if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field(); ?>

				<div class="st-form__foot">
					<span class="st-muted"><?php esc_html_e( 'Typical response · under 24h', 'raveenthiran' ); ?></span>
					<div class="st-form__actions">
						<?php $nr_wa = nr_opt( 'nr_whatsapp', '' ); if ( $nr_wa ) : ?><a class="st-btn st-btn--ghost" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $nr_wa ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'raveenthiran' ); ?></a><?php endif; ?>
						<button type="submit" class="st-btn st-btn--primary void-btn void-btn-gold"><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send enquiry', 'raveenthiran' ) ) ); ?> →</button>
					</div>
				</div>
			</form>
		</section>

		<?php /* ── FAQ ───────────────────────────────────────── */ ?>
		<?php if ( $faq ) : ?>
		<section class="mk-panel mk-panel--faq">
			<span class="mk-panel__index">03 / 03</span>
			<div style="width:100%;max-width:60ch">
				<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( nr_opt( 'nr_faq_eyebrow', __( 'FAQ', 'raveenthiran' ) ) ); ?></p>
				<div class="nr-faq__list nr-faq__list--compact st-faq__list">
					<?php foreach ( $faq as $i => $f ) : ?>
						<div class="nr-faq__item st-faq__item">
							<span class="nr-faq__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<div>
								<div class="nr-faq__q"><?php echo esc_html( $f['q'] ); ?></div>
								<div class="nr-faq__a"><?php echo esc_html( $f['a'] ); ?></div>
							</div>
							<button type="button" class="nr-faq__toggle" aria-label="<?php esc_attr_e( 'Toggle answer', 'raveenthiran' ); ?>">+</button>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
