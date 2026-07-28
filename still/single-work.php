<?php
/**
 * Single project — the featured image as hero, then the post content, which is
 * where the gallery lives (add a native Gallery block, or images, in the
 * editor). Unlimited images per project.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
<?php while ( have_posts() ) : the_post();
	$still_terms = get_the_terms( get_the_ID(), 'work_category' ); ?>
	<article>
		<header class="page-head">
			<span class="label"><?php echo esc_html( ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : __( 'Project', 'still' ) ); ?></span>
			<h1 class="page-title"><?php the_title(); ?></h1>
			<div class="single-meta">
				<span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span>
				<?php if ( $still_terms && ! is_wp_error( $still_terms ) ) : ?>
					<span><?php echo esc_html( join( ', ', wp_list_pluck( $still_terms, 'name' ) ) ); ?></span>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-hero"><?php the_post_thumbnail( 'still-hero' ); ?></div>
		<?php endif; ?>

		<div class="prose">
			<?php the_content(); ?>
		</div>

		<nav class="entry-nav">
			<a href="<?php echo esc_url( still_work_url() ); ?>">← <?php esc_html_e( 'All work', 'still' ); ?></a>
			<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire about a shoot →', 'still' ); ?></a>
		</nav>
	</article>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
