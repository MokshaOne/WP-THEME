<?php
/**
 * Single project — featured image (or first gallery plate) as hero, the write-up,
 * then the ACF project_gallery rendered as a restrained masonry. Unlimited images.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$still_terms = get_the_terms( get_the_ID(), 'nr_project_cat' );

	/* ACF gallery → plate attachment IDs (works via the engine's ACF polyfill too) */
	$still_gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : array();
	if ( ! is_array( $still_gallery ) ) $still_gallery = array();
	$still_plates = array();
	foreach ( $still_gallery as $g ) {
		$id = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : (int) $g;
		if ( $id ) $still_plates[] = $id;
	}
	$still_hero = has_post_thumbnail() ? (int) get_post_thumbnail_id() : (int) ( $still_plates[0] ?? 0 );
	?>
	<main id="main" class="page-wrap">
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

			<?php if ( $still_hero ) : ?>
				<div class="single-hero"><?php echo wp_get_attachment_image( $still_hero, 'still-hero', false, array( 'alt' => esc_attr( get_the_title() ) ) ); ?></div>
			<?php endif; ?>

			<?php if ( trim( get_the_content() ) !== '' ) : ?>
				<div class="prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php
			/* gallery plates — skip whichever we used as the hero */
			$still_rest = array();
			foreach ( $still_plates as $pid ) {
				if ( $pid !== $still_hero ) $still_rest[] = $pid;
			}
			if ( $still_rest ) : ?>
				<div class="project-gallery">
					<?php foreach ( $still_rest as $k => $pid ) : ?>
						<figure><?php echo wp_get_attachment_image( $pid, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( get_the_title() . ' — ' . ( $k + 1 ) ) ) ); ?></figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<nav class="entry-nav">
				<a href="<?php echo esc_url( still_work_url() ); ?>">← <?php esc_html_e( 'All work', 'still' ); ?></a>
				<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire about a shoot →', 'still' ); ?></a>
			</nav>
		</article>
	</main>
<?php endwhile; ?>

<?php get_footer(); ?>
