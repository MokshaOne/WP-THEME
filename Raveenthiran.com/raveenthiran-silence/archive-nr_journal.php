<?php
/**
 * Journal — a dated list, nothing more.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<section class="nr-sheet">
	<div class="nr-sheet__inner">
		<h1 class="nr-eyebrow"><?php post_type_archive_title(); ?></h1>
		<ol class="nr-journal">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<li>
					<a href="<?php the_permalink(); ?>">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y · m' ) ); ?></time>
						<span><?php the_title(); ?></span>
					</a>
				</li>
			<?php endwhile; else : ?>
				<li><?php esc_html_e( 'Nothing here yet.', 'raveenthiran-silence' ); ?></li>
			<?php endif; ?>
		</ol>
		<?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] ); ?>
	</div>
</section>

<?php get_footer(); ?>
