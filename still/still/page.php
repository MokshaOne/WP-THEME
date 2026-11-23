<?php
/**
 * Generic page — used for Studio (About) and any other page. HORIZONTAL:
 * a title panel, then the content as a prose panel that scrolls vertically
 * inside itself. Reduced-motion → vertical stack (see CSS).
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="hx">
<?php while ( have_posts() ) : the_post(); ?>
	<div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php echo esc_html( get_the_title() ); ?></span>
				<h1 class="page-title"><?php the_title(); ?></h1>
			</div>
		</section>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="hx-panel hx-panel--media"><figure><?php the_post_thumbnail( 'still-hero', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></figure></div>
		<?php endif; ?>

		<?php if ( trim( get_the_content() ) !== '' ) : ?>
			<section class="hx-panel hx-panel--wide"><div class="prose"><?php the_content(); ?></div></section>
		<?php endif; ?>

	</div>
<?php endwhile; ?>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>

<?php get_footer(); ?>
