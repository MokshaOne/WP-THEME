<?php
/**
 * Journal entry — a measured reading column.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
?>

<article class="nr-sheet">
	<div class="nr-sheet__inner nr-prose">
		<a class="nr-back" href="<?php echo esc_url( get_post_type_archive_link( 'nr_journal' ) ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran-silence' ); ?></a>
		<h1><?php the_title(); ?></h1>
		<p class="nr-sheet__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'nr-index', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
		<?php the_content(); ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
