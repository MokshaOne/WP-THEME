<?php
/**
 * Template Name: About
 * moksha1one — Studio (HORIZONTAL scroll): identity → philosophy → stats →
 * recognition (awards/press) → portrait. Vertical wheel travels sideways
 * (mk-scroll.js); native swipe on touch. Copy from the shared nr_* settings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'about';
get_header();
the_post();

$title   = nr_opt( 'nr_about_title', __( 'A practice of looking, slowly.', 'raveenthiran' ) );
$lede    = nr_opt( 'nr_about_lede', '' );
$bio     = nr_opt( 'nr_about_bio', '' );
$name    = nr_opt( 'nr_about_name', get_bloginfo( 'name' ) );
$role    = nr_opt( 'nr_about_role', 'Photographer · Vienna' );
$eyebrow = nr_opt( 'nr_about_eyebrow', 'DOCTRINE // §03' );
$portrait_id = (int) nr_opt( 'nr_about_portrait_id', 0 );
if ( ! $portrait_id && has_post_thumbnail() ) $portrait_id = (int) get_post_thumbnail_id();

$stats = array_filter( [
	'PROJECTS'     => nr_opt( 'nr_stats_proj', '' ),
	'COUNTRIES'    => nr_opt( 'nr_stats_cnty', '' ),
	'PUBLICATIONS' => nr_opt( 'nr_stats_pubs', '' ),
	'AWARDS'       => nr_opt( 'nr_stats_awd', '' ),
] );

/* recognition — text lists + ACF option repeaters */
$awards = function_exists( 'nr_recognition_list' ) ? nr_recognition_list( 'nr_awards_list' ) : [];
$press  = function_exists( 'nr_recognition_list' ) ? nr_recognition_list( 'nr_press_list' )  : [];
if ( function_exists( 'get_field' ) ) {
	foreach ( (array) get_field( 'awards_list', 'option' ) as $r ) { $t = trim( (string) ( $r['award_title'] ?? '' ) ); if ( $t !== '' ) $awards[] = [ 'year' => $r['year'] ?? '', 'title' => $t, 'org' => $r['organisation'] ?? '', 'url' => $r['award_url'] ?? '' ]; }
	foreach ( (array) get_field( 'press_list', 'option' ) as $r ) { $t = trim( (string) ( $r['pr_title'] ?? '' ) ); $o = trim( (string) ( $r['medium'] ?? '' ) ); if ( $t !== '' || $o !== '' ) $press[] = [ 'year' => $r['year'] ?? '', 'title' => $t, 'org' => $o, 'url' => $r['pr_url'] ?? '' ]; }
}
$reco = array_filter( [
	[ 'label' => __( 'AWARDS', 'raveenthiran' ), 'items' => $awards ],
	[ 'label' => __( 'PRESS',  'raveenthiran' ), 'items' => $press ],
], fn( $g ) => ! empty( $g['items'] ) );

/* ACF-driven content so every field is surfaced: pull-quote, testimonials, CTA */
$statement = function_exists( 'get_field' ) ? trim( (string) get_field( 'about_statement', 'option' ) ) : '';
$tstms     = function_exists( 'nr_testimonials' ) ? nr_testimonials( 6 ) : [];
$cta       = [];
if ( function_exists( 'get_field' ) ) {
	$ch = trim( (string) get_field( 'cta_headline', 'option' ) );
	if ( $ch !== '' ) $cta = [
		'over'   => (string) get_field( 'cta_overline', 'option' ),
		'head'   => $ch,
		'sub'    => (string) get_field( 'cta_subline', 'option' ),
		'blabel' => (string) get_field( 'cta_btn_label', 'option' ),
		'burl'   => (string) get_field( 'cta_btn_url', 'option' ),
		'ltext'  => (string) get_field( 'cta_link_text', 'option' ),
	];
}

$idx = 0;
$total = 2 + ( $statement ? 1 : 0 ) + ( $bio ? 1 : 0 ) + ( $stats ? 1 : 0 ) + ( $tstms ? 1 : 0 ) + count( $reco ) + ( $cta ? 1 : 0 );
$ix  = function () use ( &$idx, $total ) { return str_pad( (string) $idx++, 2, '0', STR_PAD_LEFT ) . ' / ' . str_pad( (string) $total, 2, '0', STR_PAD_LEFT ); };
?>

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<?php /* identity */ ?>
		<section class="mk-panel mk-panel--intro">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="void-display-hero" style="text-align:left"><?php echo wp_kses( $title, [ 'em' => [], 'span' => [ 'class' => [] ] ] ); ?></h1>
			<?php if ( $lede ) : ?><p class="void-mono" style="margin-top:18px;max-width:52ch;text-transform:none;letter-spacing:0;line-height:1.6"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
		</section>

		<?php /* pull-quote / statement (ACF about_statement) */ ?>
		<?php if ( $statement ) : ?>
		<section class="mk-panel mk-panel--intro mk-statement">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'STATEMENT', 'raveenthiran' ); ?></p>
			<blockquote class="mk-statement__q"><?php echo esc_html( $statement ); ?></blockquote>
		</section>
		<?php endif; ?>

		<?php /* portrait */ ?>
		<section class="mk-panel">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<div class="mk-work" style="grid-template-columns:auto min(40ch,42vw)">
				<figure class="mk-work__frame void-about-portrait" style="aspect-ratio:4/5;filter:grayscale(1) contrast(1.06)">
					<?php
					if ( $portrait_id && function_exists( 'nr_image_or_placeholder' ) ) nr_image_or_placeholder( get_the_ID(), 'nr-card', $name );
					elseif ( $portrait_id ) echo wp_get_attachment_image( $portrait_id, 'nr-card', false, [ 'alt' => $name ] );
					elseif ( function_exists( 'nr_placeholder' ) ) echo nr_placeholder( strtolower( $name ), false, '4/5' );
					?>
					<span class="void-badge-verified"><?php esc_html_e( 'VERIFIED', 'raveenthiran' ); ?></span>
				</figure>
				<div class="mk-work__meta">
					<span class="void-label-luxury"><?php echo esc_html( $role ); ?></span>
					<h2 class="mk-work__title"><?php echo esc_html( $name ); ?></h2>
				</div>
			</div>
		</section>

		<?php /* philosophy / bio */ ?>
		<?php if ( $bio ) : ?>
		<section class="mk-panel">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<div style="max-width:64ch">
				<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'THE PRACTICE', 'raveenthiran' ); ?></p>
				<div class="void-prose" style="font-size:clamp(16px,1.5vw,22px);line-height:1.7;color:var(--void-ink-2)"><?php echo wpautop( esc_html( $bio ) ); ?></div>
			</div>
		</section>
		<?php endif; ?>

		<?php /* stats */ ?>
		<?php if ( $stats ) : ?>
		<section class="mk-panel">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'BY THE NUMBERS', 'raveenthiran' ); ?></p>
			<div class="mk-stats">
				<?php foreach ( $stats as $k => $v ) : ?>
					<div class="mk-stat"><span class="mk-stat__v"><?php echo esc_html( $v ); ?></span><span class="void-label-luxury"><?php echo esc_html( $k ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php /* testimonials (nr_testimonial CPT + ACF) */ ?>
		<?php if ( $tstms ) : ?>
		<section class="mk-panel mk-tstm-panel">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'VOICES', 'raveenthiran' ); ?></p>
			<div class="mk-tstm">
				<?php foreach ( array_slice( $tstms, 0, 4 ) as $tp ) : $t = nr_testimonial_data( $tp ); if ( $t['quote'] === '' ) continue; ?>
					<figure class="mk-tstm__item">
						<div class="mk-tstm__stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', max( 1, min( 5, $t['rating'] ) ) ) ); ?></div>
						<blockquote class="mk-tstm__q"><?php echo esc_html( $t['quote'] ); ?></blockquote>
						<figcaption class="mk-tstm__by">
							<?php if ( $t['avatar'] ) echo wp_get_attachment_image( $t['avatar'], 'nr-thumb', false, [ 'class' => 'mk-tstm__av', 'alt' => $t['name'] ] ); ?>
							<span><span class="mk-tstm__name"><?php echo esc_html( $t['name'] ); ?></span><?php if ( $t['role'] ) : ?><span class="mk-tstm__role"><?php echo esc_html( $t['role'] ); ?></span><?php endif; ?></span>
						</figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php /* recognition — one panel per group */ ?>
		<?php foreach ( $reco as $g ) : ?>
		<section class="mk-panel">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<div style="max-width:52ch;width:100%">
				<span class="void-label-luxury"><?php echo esc_html( $g['label'] ); ?></span>
				<ul class="mk-reco__list" style="margin-top:16px">
					<?php foreach ( $g['items'] as $row ) :
						$rt = trim( $row['title'] ?: $row['org'] ); $sub = ( $row['title'] && $row['org'] ) ? $row['org'] : ''; ?>
						<li class="mk-reco__item">
							<span class="mk-reco__year"><?php echo esc_html( $row['year'] ); ?></span>
							<span class="mk-reco__body">
								<?php if ( ! empty( $row['url'] ) ) : ?><a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $rt ); ?> ↗</a>
								<?php else : ?><span class="mk-reco__title"><?php echo esc_html( $rt ); ?></span><?php endif; ?>
								<?php if ( $sub ) : ?><span class="mk-reco__org"><?php echo esc_html( $sub ); ?></span><?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php endforeach; ?>

		<?php /* CTA block (ACF cta_*) */ ?>
		<?php if ( $cta ) : ?>
		<section class="mk-panel mk-panel--intro mk-cta">
			<span class="mk-panel__index"><?php echo esc_html( $ix() ); ?></span>
			<?php if ( $cta['over'] ) : ?><p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( $cta['over'] ); ?></p><?php endif; ?>
			<h2 class="void-display-hero" style="text-align:left"><?php echo wp_kses( $cta['head'], [ 'em' => [], 'span' => [ 'class' => [] ] ] ); ?></h2>
			<?php if ( $cta['sub'] ) : ?><p class="void-mono" style="margin-top:16px;max-width:52ch;text-transform:none;letter-spacing:0;line-height:1.6"><?php echo esc_html( $cta['sub'] ); ?></p><?php endif; ?>
			<div class="mk-contact__actions" style="margin-top:26px">
				<?php if ( $cta['blabel'] ) : ?><a class="void-btn void-btn-gold" href="<?php echo esc_url( $cta['burl'] ?: ( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ) ); ?>"><?php echo esc_html( $cta['blabel'] ); ?> →</a><?php endif; ?>
				<?php if ( $cta['ltext'] ) : ?><a class="void-btn void-btn-outline" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><?php echo esc_html( $cta['ltext'] ); ?></a><?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
