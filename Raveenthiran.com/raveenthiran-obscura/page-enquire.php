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
get_header();

$quote   = function_exists( 'nr_quote_data' ) ? nr_quote_data() : [ 'types' => [], 'currency' => '€' ];
$cur     = $quote['currency'];
$email   = nr_opt( 'nr_email', 'studio@raveenthiran.at' );
$loc     = nr_opt( 'nr_studio', 'Gumpendorfer Str. 81, 1060 Wien' );
$avail   = nr_opt( 'nr_avail_text', 'Currently booking · Q3/Q4 2026' );

// Pre-fill from the calculator (or any deep link).
$sel  = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
$est  = isset( $_GET['est'] ) ? preg_replace( '/[^0-9.]/', '', wp_unslash( $_GET['est'] ) ) : '';
$ref  = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';

// Left-panel hero — first featured project, else any project.
$hero_url = '';
$hq = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1', 'fields' => 'ids' ] );
if ( ! $hq->have_posts() ) {
	$hq = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'fields' => 'ids' ] );
}
if ( $hq->have_posts() ) {
	$hero_url = get_the_post_thumbnail_url( $hq->posts[0], 'nr-hero' );
}
wp_reset_postdata();

// Chips align with the calculator's shooting types so prefill maps by slug.
$chips = [];
foreach ( $quote['types'] as $t ) $chips[] = [ 'slug' => $t['slug'], 'label' => $t['label'] ];
$chips[] = [ 'slug' => 'other', 'label' => __( 'Other', 'raveenthiran' ) ];
$sel = $sel ?: ( $chips[0]['slug'] ?? 'other' );

$steps = [
	[ '01', nr_opt( 'nr_step_1_t', __( 'Signal', 'raveenthiran' ) ),         nr_opt( 'nr_step_1_d', __( 'Send your project details and a preferred window.', 'raveenthiran' ) ) ],
	[ '02', nr_opt( 'nr_step_2_t', __( 'Briefing', 'raveenthiran' ) ),       nr_opt( 'nr_step_2_d', __( 'A short call to align on vision, location, and logistics.', 'raveenthiran' ) ) ],
	[ '03', nr_opt( 'nr_step_3_t', __( 'Shoot & deliver', 'raveenthiran' ) ), nr_opt( 'nr_step_3_d', __( 'We meet, we create. Edits delivered within 5–7 days.', 'raveenthiran' ) ) ],
];
?>
<section class="nr-page nr-fullscreen nr-enquire">
	<div class="nr-enquire__split">

		<!-- LEFT · atmosphere -->
		<aside class="nr-enquire__aside">
			<div class="nr-enquire__frame">
				<span class="nr-reg tl"></span><span class="nr-reg tr"></span>
				<span class="nr-reg bl"></span><span class="nr-reg br"></span>
				<?php if ( $hero_url ) : ?>
					<img src="<?php echo esc_url( $hero_url ); ?>" alt="" decoding="async" loading="lazy">
				<?php elseif ( function_exists( 'nr_placeholder' ) ) : ?>
					<?php echo nr_placeholder( 'studio', true, 'auto' ); ?>
				<?php endif; ?>
				<div class="nr-enquire__frame-grad"></div>
			</div>

			<div class="nr-enquire__aside-body">
				<span class="nr-eyebrow"><?php echo esc_html( $avail ); ?></span>
				<ol class="nr-steps">
					<?php foreach ( $steps as $s ) : ?>
						<li>
							<span class="nr-steps__n"><?php echo esc_html( $s[0] ); ?></span>
							<div>
								<span class="nr-steps__t"><?php echo esc_html( $s[1] ); ?></span>
								<span class="nr-steps__d"><?php echo esc_html( $s[2] ); ?></span>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
				<dl class="nr-enquire__facts">
					<div><dt><?php esc_html_e( 'Email', 'raveenthiran' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></dd></div>
					<div><dt><?php esc_html_e( 'Response', 'raveenthiran' ); ?></dt><dd>&lt; 24h</dd></div>
					<div><dt><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></dt><dd><?php echo esc_html( $loc ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Travel', 'raveenthiran' ); ?></dt><dd><?php esc_html_e( 'Available worldwide', 'raveenthiran' ); ?></dd></div>
				</dl>
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

				<div class="nr-form__foot">
					<span class="nr-eyebrow nr-eyebrow--plain"><?php esc_html_e( 'Typical response · < 24h', 'raveenthiran' ); ?></span>
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
