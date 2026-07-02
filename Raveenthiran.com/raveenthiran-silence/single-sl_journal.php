<?php
/**
 * Journal entry — a measured reading column.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
?>

<article class="sl-sheet">
	<div class="sl-sheet__inner sl-prose">
		<a class="sl-back" href="<?php echo esc_url( get_post_type_archive_link( sl_jt() ) ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran-silence' ); ?></a>
		<h1><?php the_title(); ?></h1>
		<p class="sl-sheet__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<?php if ( has_post_thumbnail() ) the_post_thumbnail( 'sl-index', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
		<?php the_content(); ?>
	</div>
</article>

<?php endwhile; get_footer(); ?>
