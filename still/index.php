<?php
/**
 * Fallback index — used for search results and any archive without a more
 * specific template. Mirrors the journal list.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php is_search() ? esc_html_e( 'Search', 'still' ) : esc_html_e( 'Index', 'still' ); ?></span>
		<h1 class="page-title"><?php
			if ( is_search() ) {
				printf( /* translators: %s: search query */ esc_html__( 'Results for “%s”', 'still' ), esc_html( get_search_query() ) );
			} else {
				single_post_title( '', true );
			}
		?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="journal-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<a class="journal-item" href="<?php the_permalink(); ?>">
					<h2><?php the_title(); ?></h2>
					<span class="date"><?php echo esc_html( get_the_date() ); ?></span>
				</a>
			<?php endwhile; ?>
		</div>
		<?php still_pagination(); ?>
	<?php else : ?>
		<p class="page-lead"><?php esc_html_e( 'Nothing found.', 'still' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
