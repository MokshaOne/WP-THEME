<?php
/**
 * Fallback — quiet list of whatever the query holds.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>

<section class="sl-sheet">
	<div class="sl-sheet__inner">
		<?php if ( have_posts() ) : ?>
			<ol class="sl-journal">
				<?php while ( have_posts() ) : the_post(); ?>
					<li>
						<a href="<?php the_permalink(); ?>">
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y · m' ) ); ?></time>
							<span><?php the_title(); ?></span>
						</a>
					</li>
				<?php endwhile; ?>
			</ol>
			<?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] ); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'raveenthiran-silence' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
