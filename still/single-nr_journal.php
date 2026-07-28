<?php
/**
 * Single journal entry (nr_journal CPT).
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
			<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire →', 'still' ); ?></a>
		</nav>
	</article>
<?php endwhile; ?>
</main>

<?php get_footer(); ?>
