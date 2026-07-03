<?php
/**
 * Template Name: About
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$about   = nr_opt( 'nr_about_lede', '' );
	$socials = array_filter( [
		'Instagram' => nr_opt( 'nr_instagram' ),
		'Behance'   => nr_opt( 'nr_behance' ),
		'Vimeo'     => nr_opt( 'nr_vimeo' ),
		'LinkedIn'  => nr_opt( 'nr_linkedin' ),
	] );
?>

<article class="nr-sheet nr-about">
	<div class="nr-sheet__inner nr-prose">
		<h1 class="nr-eyebrow"><?php the_title(); ?></h1>
		<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'nr-index', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
		<?php if ( $about ) : ?><p class="nr-lede"><?php echo esc_html( $about ); ?></p><?php endif; ?>
		<?php the_content(); ?>

		<?php if ( $socials ) : ?>
			<h2 class="nr-eyebrow"><?php esc_html_e( 'Elsewhere', 'raveenthiran-silence' ); ?></h2>
			<p class="nr-links">
				<?php foreach ( $socials as $label => $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?> ↗</a>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
