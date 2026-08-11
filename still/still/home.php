<?php
/**
 * Journal — the blog posts index (assign a Page as the Posts page under
 * Settings → Reading and name it "Journal"). Paginated, unlimited entries.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$still_posts_page = (int) get_option( 'page_for_posts' );
?>

<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php esc_html_e( 'Writing', 'still' ); ?></span>
		<h1 class="page-title"><?php echo esc_html( $still_posts_page ? get_the_title( $still_posts_page ) : __( 'Journal', 'still' ) ); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="journal-list">
			<?php while ( have_posts() ) : the_post(); ?>
				<a class="journal-item" href="<?php the_permalink(); ?>">
					<h2><?php the_title(); ?></h2>
					<span class="date"><?php echo esc_html( get_the_date() ); ?></span>
					<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
						<span class="ex"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26 ) ); ?></span>
					<?php endif; ?>
				</a>
			<?php endwhile; ?>
		</div>
		<?php still_pagination(); ?>
	<?php else : ?>
		<p class="page-lead"><?php esc_html_e( 'No entries yet.', 'still' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
