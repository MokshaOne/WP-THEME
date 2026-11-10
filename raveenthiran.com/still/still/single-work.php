<?php
/**
 * Single project — HORIZONTAL: title → hero photograph → write-up → nav.
 * Scroll / swipe sideways. The write-up panel (which holds the in-editor
 * gallery) scrolls vertically inside itself. Reduced-motion → vertical stack.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="hx">
<?php while ( have_posts() ) : the_post();
	$still_terms = get_the_terms( get_the_ID(), 'work_category' );
	$still_has_content = trim( get_the_content() ) !== '';
	?>
	<div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php echo esc_html( ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : __( 'Project', 'still' ) ); ?></span>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<div class="single-meta">
					<span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span>
					<?php if ( $still_terms && ! is_wp_error( $still_terms ) ) : ?>
						<span><?php echo esc_html( join( ', ', wp_list_pluck( $still_terms, 'name' ) ) ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="hx-panel hx-panel--media"><figure><?php the_post_thumbnail( 'still-hero', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></figure></div>
		<?php endif; ?>

		<?php $still_meta = function_exists( 'still_project_meta' ) ? still_project_meta() : array(); if ( $still_meta ) : ?>
			<section class="hx-panel hx-panel--contact">
				<dl class="contact-list">
					<?php foreach ( $still_meta as $key => $f ) : ?>
						<div class="row"><span class="k"><?php echo esc_html( $f['label'] ); ?></span>
						<?php if ( 'website' === $key ) : ?>
							<a href="<?php echo esc_url( $f['value'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $f['value'] ) ) ); ?></a>
						<?php else : ?>
							<span><?php echo esc_html( $f['value'] ); ?></span>
						<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</dl>
			</section>
		<?php endif; ?>

		<?php if ( $still_has_content ) : ?>
			<section class="hx-panel hx-panel--wide"><div class="prose"><?php the_content(); ?></div></section>
		<?php endif; ?>

		<section class="hx-panel hx-panel--end">
			<nav class="entry-nav">
				<a href="<?php echo esc_url( still_work_url() ); ?>">← <?php esc_html_e( 'All work', 'still' ); ?></a>
				<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire about a shoot →', 'still' ); ?></a>
			</nav>
		</section>

	</div>
<?php endwhile; ?>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>

<?php get_footer(); ?>
