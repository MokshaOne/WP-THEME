<?php
/**
 * Single journal entry.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(); ?>

<main id="main" class="page-wrap">
<?php while ( have_posts() ) : the_post(); ?>
	<article>
		<header class="page-head">
			<span class="label"><?php echo esc_html( get_the_date() ); ?></span>
			<h1 class="page-title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="single-hero"><?php the_post_thumbnail( 'still-hero' ); ?></div>
		<?php endif; ?>

		<div class="prose">
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</div>

		<nav class="entry-nav">
			<a href="<?php echo esc_url( still_journal_url() ); ?>">← <?php esc_html_e( 'All entries', 'still' ); ?></a>
			<?php $still_next = get_next_post(); if ( $still_next ) : ?>
				<a href="<?php echo esc_url( get_permalink( $still_next ) ); ?>"><?php esc_html_e( 'Next →', 'still' ); ?></a>
			<?php endif; ?>
		</nav>
	</article>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
