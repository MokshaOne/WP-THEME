<?php
/**
 * M1O Transmission · index fallback
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<main id="main" class="page-shell">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<div class="eyebrow">// <?php echo esc_html( get_post_type() ); ?></div>
			<h1><?php the_title(); ?><em>.</em></h1>
			<div class="prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; else : ?>
		<div class="eyebrow">// <?php esc_html_e( 'No signal', 'm1o' ); ?></div>
		<h1><?php esc_html_e( 'Nothing found', 'm1o' ); ?><em>.</em></h1>
		<div class="prose"><p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">&#8592; <?php esc_html_e( 'Back to the console', 'm1o' ); ?></a></p></div>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
