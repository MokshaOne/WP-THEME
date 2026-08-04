<?php
/**
 * Single project — HORIZONTAL: title → hero → write-up → gallery plates → nav,
 * scrolled sideways. Reverts to vertical on phones / reduced-motion.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$still_terms = get_the_terms( get_the_ID(), 'nr_project_cat' );
	$still_gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : array();
	if ( ! is_array( $still_gallery ) ) $still_gallery = array();
	$still_plates = array();
	foreach ( $still_gallery as $g ) {
		$id = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : (int) $g;
		if ( $id ) $still_plates[] = $id;
	}
	$still_hero = has_post_thumbnail() ? (int) get_post_thumbnail_id() : (int) ( $still_plates[0] ?? 0 );
	?>
	<main id="main" class="hx">
		<div class="hx__sticky"><div class="hx__track">

			<section class="hx-panel hx-panel--head">
				<div class="page-head">
					<span class="label"><?php echo esc_html( ( $still_terms && ! is_wp_error( $still_terms ) ) ? $still_terms[0]->name : __( 'Project', 'still' ) ); ?></span>
					<h1 class="page-title"><?php the_title(); ?></h1>
					<div class="single-meta">
						<span><?php echo esc_html( get_the_date( 'Y' ) ); ?></span>
						<?php if ( $still_terms && ! is_wp_error( $still_terms ) ) : ?><span><?php echo esc_html( join( ', ', wp_list_pluck( $still_terms, 'name' ) ) ); ?></span><?php endif; ?>
					</div>
				</div>
			</section>

			<?php if ( $still_hero ) : ?>
				<div class="hx-panel hx-panel--media"><figure><?php echo wp_get_attachment_image( $still_hero, 'still-hero', false, array( 'alt' => esc_attr( get_the_title() ) ) ); ?></figure></div>
			<?php endif; ?>

			<?php if ( trim( get_the_content() ) !== '' ) : ?>
				<section class="hx-panel hx-panel--wide"><div class="prose"><?php the_content(); ?></div></section>
			<?php endif; ?>

			<?php
			$still_rest = array();
			foreach ( $still_plates as $pid ) { if ( $pid !== $still_hero ) $still_rest[] = $pid; }
			foreach ( $still_rest as $k => $pid ) : ?>
				<div class="hx-panel hx-panel--media"><figure><?php echo wp_get_attachment_image( $pid, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( get_the_title() . ' — ' . ( $k + 1 ) ) ) ); ?></figure></div>
			<?php endforeach; ?>

			<section class="hx-panel">
				<nav class="entry-nav" style="border:0;margin:0;padding:0;flex-direction:column;gap:1.4rem;align-items:flex-start">
					<a href="<?php echo esc_url( still_work_url() ); ?>">← <?php esc_html_e( 'All work', 'still' ); ?></a>
					<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire about a shoot →', 'still' ); ?></a>
				</nav>
			</section>

		</div></div>
		<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
	</main>
<?php endwhile; ?>

<?php get_footer(); ?>
