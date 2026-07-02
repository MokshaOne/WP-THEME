<?php
/**
 * Template Name: About
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$about   = sl_opt( 'sl_about', '' );
	$socials = array_filter( [
		'Instagram' => sl_opt( 'sl_instagram' ),
		'Behance'   => sl_opt( 'sl_behance' ),
		'Vimeo'     => sl_opt( 'sl_vimeo' ),
		'LinkedIn'  => sl_opt( 'sl_linkedin' ),
	] );
?>

<article class="sl-sheet sl-about">
	<div class="sl-sheet__inner sl-prose">
		<h1 class="sl-eyebrow"><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'sl-index', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
		<?php if ( $about ) : ?><p class="sl-lede"><?php echo esc_html( $about ); ?></p><?php endif; ?>
		<?php the_content(); ?>

		<?php if ( $socials ) : ?>
			<h2 class="sl-eyebrow"><?php esc_html_e( 'Elsewhere', 'raveenthiran-silence' ); ?></h2>
			<p class="sl-links">
				<?php foreach ( $socials as $label => $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?> ↗</a>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
