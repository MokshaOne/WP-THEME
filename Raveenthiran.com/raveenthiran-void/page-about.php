<?php
/**
 * Template Name: About
 * VOID studio — Philosophy. Two-column: doctrine copy + verified portrait.
 * Copy comes from the shared Theme Settings (nr_about_*).
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
	'PROJECTS' => nr_opt( 'nr_stats_proj', '' ),
	'COUNTRIES' => nr_opt( 'nr_stats_cnty', '' ),
	'PUBLICATIONS' => nr_opt( 'nr_stats_pubs', '' ),
] );
?>

<section class="void-screen void-about-screen">
	<div class="void-scanline-beam" aria-hidden="true"></div>

	<div class="void-about">
		<div class="void-about-copy">
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="void-h1-huge void-h1-uncap"><?php echo wp_kses( $title, [ 'em' => [], 'span' => [ 'class' => [] ] ] ); ?></h1>
			<?php if ( $lede ) : ?><p><?php echo esc_html( $lede ); ?></p><?php endif; ?>
			<?php if ( $bio ) : ?><p class="void-about-copy-2"><?php echo nl2br( esc_html( $bio ) ); ?></p><?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="void-about-page-content void-prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $stats ) : ?>
			<div class="void-about-stats">
				<?php foreach ( $stats as $k => $v ) : ?>
					<div class="void-stat-box">
						<div class="void-stat-box-top"><span><?php echo esc_html( $k ); ?></span><span class="void-dot-gold"></span></div>
						<div class="void-stat-box-value"><?php echo esc_html( $v ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="void-about-right">
			<figure class="void-about-portrait">
				<?php
				if ( $portrait_id && function_exists( 'nr_image_or_placeholder' ) ) nr_image_or_placeholder( get_the_ID(), 'nr-card', $name );
				elseif ( $portrait_id ) echo wp_get_attachment_image( $portrait_id, 'nr-card', false, [ 'alt' => $name ] );
				else echo function_exists( 'nr_placeholder' ) ? nr_placeholder( strtolower( $name ), false, '3/4' ) : '';
				?>
				<span class="void-badge-verified"><?php esc_html_e( 'VERIFIED', 'raveenthiran' ); ?></span>
			</figure>
			<div class="void-frame-coords" style="position:static;margin-top:14px;display:inline-block">
				<span class="void-mono"><?php echo esc_html( $role ); ?></span>
				<p><?php echo esc_html( $name ); ?></p>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
