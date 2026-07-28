<?php
/**
 * Journal archive — the nr_journal CPT listing (paginated, unlimited).
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php esc_html_e( 'Writing', 'still' ); ?></span>
		<h1 class="page-title"><?php is_tax() ? single_term_title() : post_type_archive_title(); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="journal-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<a class="journal-item" href="<?php the_permalink(); ?>">
					<h2><?php the_title(); ?></h2>
					<span class="date"><?php echo esc_html( get_the_date() ); ?></span>
					<?php if ( get_the_excerpt() ) : ?>
						<span class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endwhile; ?>
		</div>
		<?php still_pagination(); ?>
	<?php else : ?>
		<p class="page-lead"><?php esc_html_e( 'No entries yet. Add one under Journal → Add New.', 'still' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
