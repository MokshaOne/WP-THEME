<?php
/**
 * Template Name: Contact (no-scroll)
 * moksha1one — a single locked viewport: the essential coordinates for
 * reaching the studio. Scroll mode 'none' (see mk_scroll_mode).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'contact';
get_header();
the_post();

$email   = nr_opt( 'nr_email', '' );
$phone   = nr_opt( 'nr_phone', '' );
$studio  = nr_opt( 'nr_studio', nr_opt( 'nr_location', 'Vienna' ) );
$wa      = nr_opt( 'nr_whatsapp', '' );
$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
$socials = array_filter( [
	'Instagram' => nr_opt( 'nr_instagram' ),
	'Behance'   => nr_opt( 'nr_behance' ),
	'Vimeo'     => nr_opt( 'nr_vimeo' ),
	'LinkedIn'  => nr_opt( 'nr_linkedin' ),
] );
?>

<section class="void-screen void-contact mk-contact">
	<div class="void-scanline-beam" aria-hidden="true"></div>

	<div class="mk-contact__in">
		<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( nr_opt( 'nr_contact_eyebrow', 'DIRECT LINE' ) ); ?></p>
		<h1 class="void-display-hero" style="text-align:left"><?php echo wp_kses( nr_opt( 'nr_contact_title', 'Reach the <span class="void-gold-ital">studio.</span>' ), [ 'span' => [ 'class' => [] ], 'em' => [] ] ); ?></h1>

		<?php if ( get_the_content() ) : ?><div class="mk-contact__lede void-prose"><?php the_content(); ?></div><?php endif; ?>

		<dl class="mk-contact__grid">
			<?php if ( $email ) : ?>
			<div><dt class="void-label-luxury"><?php esc_html_e( 'EMAIL', 'raveenthiran' ); ?></dt>
				<dd><button type="button" class="mk-contact__link st-foot__email" data-copy="<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></button></dd></div>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
			<div><dt class="void-label-luxury"><?php esc_html_e( 'PHONE', 'raveenthiran' ); ?></dt>
				<dd><a class="mk-contact__link" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></dd></div>
			<?php endif; ?>
			<div><dt class="void-label-luxury"><?php esc_html_e( 'STUDIO', 'raveenthiran' ); ?></dt>
				<dd class="mk-contact__val"><?php echo esc_html( $studio ); ?></dd></div>
			<div><dt class="void-label-luxury"><?php esc_html_e( 'COORDINATES', 'raveenthiran' ); ?></dt>
				<dd class="mk-contact__val"><?php echo esc_html( nr_opt( 'nr_void_lat', '48.2082° N' ) . ' / ' . nr_opt( 'nr_void_long', '16.3738° E' ) ); ?></dd></div>
		</dl>

		<div class="mk-contact__actions">
			<a class="void-btn void-btn-gold" href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'BOOK A SESSION', 'raveenthiran' ); ?> →</a>
			<?php if ( $wa ) : ?><a class="void-btn void-btn-outline" href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $wa ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WHATSAPP', 'raveenthiran' ); ?></a><?php endif; ?>
		</div>

		<?php if ( $socials ) : ?>
		<div class="mk-contact__social">
			<?php foreach ( $socials as $label => $url ) printf( '<a href="%s" target="_blank" rel="noopener">%s ↗</a>', esc_url( $url ), esc_html( $label ) ); ?>
		</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
