<?php
/**
 * Work archive — a HORIZONTAL filmstrip: a head panel, then one full-height
 * photograph per project. Scroll / swipe sideways. Category (work_category)
 * archives fall through here too. Reduced-motion → vertical stack (see CSS).
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="hx">
	<div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php esc_html_e( 'Portfolio', 'still' ); ?></span>
				<h1 class="page-title"><?php is_tax() ? single_term_title() : post_type_archive_title(); ?></h1>
				<?php $still_desc = get_the_archive_description(); if ( $still_desc ) : ?>
					<div class="page-lead"><?php echo wp_kses_post( $still_desc ); ?></div>
				<?php endif; ?>
			</div>
		</section>

		<?php if ( have_posts() ) : $still_n = ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * (int) get_option( 'posts_per_page' );
			while ( have_posts() ) : the_post(); $still_n++;
				$still_terms = get_the_terms( get_the_ID(), 'work_category' );
				$still_cat   = ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : '';
			?>
			<a class="hx-panel hx-panel--media" href="<?php the_permalink(); ?>">
				<figure>
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'still-hero', array( 'alt' => esc_attr( get_the_title() ) ) );
					} else {
						echo '<span style="display:grid;place-items:center;width:56vh;height:74vh;background:#0d0d0d;border:1px solid var(--line);font-family:var(--mono);font-size:.6rem;letter-spacing:.2em;color:#3a3a3a">' . esc_html__( 'No image', 'still' ) . '</span>';
					}
					?>
					<figcaption><span class="idx"><?php echo esc_html( still_pad( $still_n ) ); ?></span><span><?php the_title(); ?></span><?php echo $still_cat ? '<span>·&nbsp; ' . esc_html( $still_cat ) . '</span>' : ''; ?></figcaption>
				</figure>
			</a>
			<?php endwhile; else : ?>
			<section class="hx-panel">
				<p class="page-lead"><?php esc_html_e( 'No work yet. Add your first project under Work → Add New — set a featured image, pick a category, and add the gallery in the editor.', 'still' ); ?></p>
			</section>
		<?php endif; ?>

	</div>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>

<?php get_footer(); ?>
