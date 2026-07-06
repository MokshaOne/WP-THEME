<?php
/**
 * Template Name: Enquire
 * Studio — a two-column enquiry page: atmosphere + studio facts on the
 * left, the unified inquiry form on the right, FAQ below. Posts to
 * admin-post.php → nr_contact_send. All form hooks are unchanged so
 * theme.js (chips, estimate, price popover) and the mailer keep working.
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

$quote = function_exists( 'nr_quote_data' ) ? nr_quote_data() : [ 'types' => [], 'currency' => '€' ];
$cur   = $quote['currency'];

$sel = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
$est = isset( $_GET['est'] ) ? preg_replace( '/[^0-9.]/', '', wp_unslash( $_GET['est'] ) ) : '';
$ref = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';

$lead_id = 0;
$hq = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids', 'no_found_rows' => true ] );
if ( $hq->have_posts() ) $lead_id = (int) get_post_thumbnail_id( $hq->posts[0] );
wp_reset_postdata();

$chips = [];
foreach ( $quote['types'] as $t ) $chips[] = [ 'slug' => $t['slug'], 'label' => $t['label'] ];
$chips[] = [ 'slug' => 'other', 'label' => __( 'Other', 'raveenthiran' ) ];
$sel = $sel ?: ( $chips[0]['slug'] ?? 'other' );

$email = nr_opt( 'nr_email', '' );
?>
<section class="st-page nr-enquire st-enquire">
	<div class="st-wrap st-enquire__grid">

		<aside class="st-enquire__aside">
			<div class="st-enquire__frame">
				<?php if ( $lead_id ) :
					echo wp_get_attachment_image( $lead_id, 'nr-hero', false, [ 'alt' => '', 'sizes' => '(max-width:900px) 100vw, 42vw', 'loading' => 'eager', 'decoding' => 'async', 'class' => 'st-enquire__img' ] );
				else :
					echo nr_placeholder( 'studio', false, '4/5' );
				endif; ?>
			</div>
			<dl class="st-enquire__facts">
				<div><dt><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></dt><dd><?php echo esc_html( nr_opt( 'nr_studio', nr_opt( 'nr_location', 'Wien' ) ) ); ?></dd></div>
				<?php if ( $email ) : ?><div><dt><?php esc_html_e( 'Email', 'raveenthiran' ); ?></dt><dd><button type="button" class="st-foot__email" data-copy="<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></button></dd></div><?php endif; ?>
				<div><dt><?php esc_html_e( 'Response', 'raveenthiran' ); ?></dt><dd><?php esc_html_e( 'Within 24 hours', 'raveenthiran' ); ?></dd></div>
			</dl>
		</aside>

		<div class="st-enquire__main">
			<header class="st-enquire__head">
				<span class="st-eyebrow"><?php echo esc_html( nr_opt( 'nr_enquire_eyebrow', __( 'Enquire', 'raveenthiran' ) ) ); ?></span>
				<h1 class="st-enquire__title"><?php echo wp_kses( nr_opt( 'nr_enquire_title', __( "Let's start a <em>project</em>", 'raveenthiran' ) ), [ 'em' => [], 'b' => [], 'strong' => [] ] ); ?></h1>
				<p class="st-enquire__lede"><?php echo esc_html( nr_opt( 'nr_enquire_lede', __( 'Tell me what you have in mind — a place, a person, an hour of the day. Or check pricing first.', 'raveenthiran' ) ) ); ?></p>
			</header>

			<form class="st-form nr-form nr-enquire__form" data-enquire-form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="nr_contact_send">
				<?php wp_nonce_field( 'nr_contact', '_nr_nonce' ); ?>
				<input type="text" name="nr_company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="nr-hp">
				<input type="hidden" name="nr_ref" value="<?php echo esc_attr( $ref ); ?>">
				<input type="hidden" name="nr_service" value="<?php echo esc_attr( $sel ); ?>">
				<input type="hidden" name="nr_referrer" value="" data-nr-referrer>

				<div class="st-form__row">
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Name', 'raveenthiran' ); ?></span>
						<input type="text" name="name" autocomplete="name" required placeholder="<?php esc_attr_e( 'Your full name', 'raveenthiran' ); ?>">
					</label>
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Email', 'raveenthiran' ); ?></span>
						<input type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" required placeholder="<?php esc_attr_e( 'you@example.com', 'raveenthiran' ); ?>">
					</label>
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
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Preferred date', 'raveenthiran' ); ?></span>
						<input type="date" name="preferred_date">
					</label>
					<label class="st-field"><span class="st-field__l"><?php esc_html_e( 'Your estimate', 'raveenthiran' ); ?></span>
						<span class="st-estimate">
							<output data-enquire-estimate><?php echo $est ? esc_html( $cur . number_format_i18n( (float) $est ) ) : '—'; ?></output>
							<button type="button" class="st-estimate__link" data-modal="nr-quote"><?php esc_html_e( 'Check pricing', 'raveenthiran' ); ?> →</button>
						</span>
						<input type="hidden" name="estimate" data-enquire-estimate-input value="<?php echo $est ? esc_attr( $cur . $est ) : ''; ?>">
					</label>
				</div>

				<label class="st-field st-field--wide"><span class="st-field__l"><?php esc_html_e( 'Tell me about the project', 'raveenthiran' ); ?></span>
					<textarea name="notes" rows="5" placeholder="<?php esc_attr_e( 'A few sentences — a place, a person, an hour of the day.', 'raveenthiran' ); ?>"><?php echo $ref ? esc_textarea( sprintf( __( 'Re: %s — I saw this project and would like something in the same spirit.', 'raveenthiran' ), $ref ) ) : ''; ?></textarea>
				</label>

				<?php if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field(); ?>

				<div class="st-form__foot">
					<span class="st-muted"><?php esc_html_e( 'Typical response · under 24h', 'raveenthiran' ); ?></span>
					<div class="st-form__actions">
						<?php $nr_wa = nr_opt( 'nr_whatsapp', '' ); if ( $nr_wa ) : ?><a class="st-btn st-btn--ghost" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $nr_wa ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'raveenthiran' ); ?></a><?php endif; ?>
						<button type="submit" class="st-btn st-btn--primary"><?php echo esc_html( nr_opt( 'nr_cta_send', __( 'Send enquiry', 'raveenthiran' ) ) ); ?> →</button>
					</div>
				</div>
			</form>

			<section class="st-faq" id="faq">
				<span class="st-eyebrow"><?php echo esc_html( nr_opt( 'nr_faq_eyebrow', __( 'FAQ', 'raveenthiran' ) ) ); ?></span>
				<div class="nr-faq__list nr-faq__list--compact st-faq__list">
					<?php foreach ( ( function_exists( 'nr_faq_items' ) ? nr_faq_items() : [] ) as $i => $f ) : ?>
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
			</section>
		</div>
	</div>
</section>
<?php get_footer(); ?>
