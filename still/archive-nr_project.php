<?php
/**
 * Work archive — a HORIZONTAL filmstrip of projects (scroll down → move
 * sideways). Reverts to a vertical stack on phones / reduced-motion.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="hx">
	<div class="hx__sticky"><div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php esc_html_e( 'Portfolio', 'still' ); ?></span>
				<h1 class="page-title"><?php is_tax() ? single_term_title() : post_type_archive_title(); ?></h1>
				<?php $still_desc = get_the_archive_description(); if ( $still_desc ) : ?>
					<div class="page-lead"><?php echo wp_kses_post( $still_desc ); ?></div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
			$still_terms = get_the_terms( get_the_ID(), 'nr_project_cat' );
			$still_cat   = ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : '';
			?>
			<a class="hx-panel hx-panel--media work-card" href="<?php the_permalink(); ?>">
				<figure>
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'still-hero', array( 'alt' => esc_attr( get_the_title() ) ) );
					} else {
						echo '<div style="width:56vh;height:74vh;background:#141416;border:1px solid var(--line)"></div>';
					}
					?>
					<figcaption><?php the_title(); ?><?php echo $still_cat ? ' &nbsp;·&nbsp; ' . esc_html( $still_cat ) : ''; ?></figcaption>
				</figure>
			</a>
		<?php endwhile; else : ?>
			<section class="hx-panel"><p class="page-lead"><?php esc_html_e( 'No work yet. Add your first project under Work → Add New.', 'still' ); ?></p></section>
		<?php endif; ?>

	</div></div>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>

<?php get_footer(); ?>
