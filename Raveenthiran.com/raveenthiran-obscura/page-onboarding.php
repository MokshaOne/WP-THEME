<?php
/**
 * Template Name: Client onboarding
 * #43 — "what to expect / what to bring" for booked clients. Link it from the
 * booking confirmation. Content comes from the page editor; falls back to a
 * sensible default checklist.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'about';
get_header();
the_post();
$body = trim( get_the_content() );
?>
<section class="nr-page nr-page-generic nr-onboard">
	<span class="nr-eyebrow"><?php esc_html_e( 'Before the shoot', 'raveenthiran' ); ?></span>
	<h1><?php the_title(); ?></h1>
	<?php if ( $body !== '' ) : ?>
		<div class="nr-prose"><?php the_content(); ?></div>
	<?php else : ?>
		<div class="nr-prose">
			<h2><?php esc_html_e( 'What to expect', 'raveenthiran' ); ?></h2>
			<p><?php esc_html_e( 'A short call first to align on vision, location and timing. On the day we shoot calmly — no rush, no crowd. Edits land within 5–7 business days.', 'raveenthiran' ); ?></p>
			<h2><?php esc_html_e( 'What to bring', 'raveenthiran' ); ?></h2>
			<ul>
				<li><?php esc_html_e( 'Two or three wardrobe options (structured + soft).', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Any references or a moodboard — even rough ones.', 'raveenthiran' ); ?></li>
				<li><?php esc_html_e( 'Yourself, unhurried. Water. Comfortable shoes for location moves.', 'raveenthiran' ); ?></li>
			</ul>
			<p><?php printf( esc_html__( 'Questions before then? Just reply to your confirmation email or write to %s.', 'raveenthiran' ), esc_html( nr_opt( 'nr_email', '' ) ) ); ?></p>
		</div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
