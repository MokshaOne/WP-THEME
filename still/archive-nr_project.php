<?php
/**
 * Work archive — the full portfolio. A masonry grid of every project, with
 * pagination, so it scales to unlimited galleries. Category & series taxonomy
 * archives (work_category) fall through to this template too.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php esc_html_e( 'Portfolio', 'still' ); ?></span>
		<h1 class="page-title"><?php
			if ( is_tax() ) { single_term_title(); } else { post_type_archive_title(); }
		?></h1>
		<?php $still_desc = get_the_archive_description(); if ( $still_desc ) : ?>
			<div class="page-lead"><?php echo wp_kses_post( $still_desc ); ?></div>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="work-grid">
			<?php while ( have_posts() ) : the_post();
				$still_terms = get_the_terms( get_the_ID(), 'nr_project_cat' );
				$still_cat   = ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : '';
			?>
			<div class="item tilt-wrap">
				<a class="work-card tilt-el" href="<?php the_permalink(); ?>">
					<span class="frame"><?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'large', array( 'alt' => esc_attr( get_the_title() ) ) );
						} else {
							echo '<span class="ph" style="display:grid;place-items:center;aspect-ratio:4/5;font-family:var(--mono);font-size:.6rem;letter-spacing:.2em;color:#3a3a3a">' . esc_html__( 'No image', 'still' ) . '</span>';
						}
					?></span>
					<span class="m">
						<h3><?php the_title(); ?></h3>
						<?php if ( $still_cat ) : ?><span class="cat"><?php echo esc_html( $still_cat ); ?></span><?php endif; ?>
					</span>
				</a>
			</div>
			<?php endwhile; ?>
		</div>
		<?php still_pagination(); ?>
	<?php else : ?>
		<p class="page-lead"><?php esc_html_e( 'No work yet. Add your first project under Work → Add New — set a featured image, pick a category, and add the gallery in the editor.', 'still' ); ?></p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
