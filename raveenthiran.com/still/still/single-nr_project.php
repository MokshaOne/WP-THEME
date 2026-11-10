<?php
/**
 * Single legacy project (nr_project) — HORIZONTAL: title/meta → cover → gallery
 * plates → nav. Reads your existing ACF fields (project_cover, project_gallery,
 * project_client, project_year, project_location, project_link) via the ported
 * nr_field() reader, so pre-existing content displays without the old engine.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$still_id     = get_the_ID();
	$still_terms  = function_exists( 'wp_get_post_terms' ) ? wp_get_post_terms( $still_id, 'nr_project_cat', array( 'fields' => 'names' ) ) : array();
	$still_cat    = ( is_array( $still_terms ) && $still_terms ) ? $still_terms[0] : '';
	$still_year   = nr_field( 'project_year' ) ? nr_field( 'project_year' ) : get_the_date( 'Y' );
	$still_client = nr_field( 'project_client' );
	$still_loc    = nr_field( 'project_location' );
	$still_link   = nr_field( 'project_link' );
	$still_cover  = still_project_cover( $still_id );
	$still_plates = still_project_gallery( $still_id );
	// don't repeat the cover if it also opens the gallery
	$still_plates = array_values( array_filter( $still_plates, function ( $pid ) use ( $still_cover ) { return (int) $pid !== (int) $still_cover; } ) );
	$still_meta = array();
	if ( $still_client ) { $still_meta[ __( 'Client', 'still' ) ]   = $still_client; }
	if ( $still_year )   { $still_meta[ __( 'Year', 'still' ) ]     = $still_year; }
	if ( $still_loc )    { $still_meta[ __( 'Location', 'still' ) ] = $still_loc; }
	?>
	<main id="main" class="hx">
		<div class="hx__track">

			<section class="hx-panel hx-panel--head">
				<div class="page-head">
					<span class="label"><?php echo esc_html( $still_cat ? $still_cat : __( 'Project', 'still' ) ); ?></span>
					<h1 class="page-title"><?php the_title(); ?></h1>
					<div class="single-meta">
						<span><?php echo esc_html( $still_year ); ?></span>
						<?php if ( $still_cat ) : ?><span><?php echo esc_html( $still_cat ); ?></span><?php endif; ?>
					</div>
				</div>
			</section>

			<?php if ( $still_cover ) : ?>
				<div class="hx-panel hx-panel--media"><figure><?php echo wp_get_attachment_image( $still_cover, 'large', false, array( 'alt' => esc_attr( get_the_title() ) ) ); ?></figure></div>
			<?php endif; ?>

			<?php if ( $still_meta || $still_link || trim( get_the_content() ) !== '' ) : ?>
				<section class="hx-panel hx-panel--wide">
					<?php if ( trim( get_the_content() ) !== '' ) : ?>
						<div class="prose" style="margin-bottom:2.5rem"><?php the_content(); ?></div>
					<?php endif; ?>
					<?php if ( $still_meta || $still_link ) : ?>
						<dl class="contact-list" style="max-width:520px">
							<?php foreach ( $still_meta as $k => $v ) : ?>
								<div class="row"><span class="k"><?php echo esc_html( $k ); ?></span><span><?php echo esc_html( $v ); ?></span></div>
							<?php endforeach; ?>
							<?php if ( $still_link ) : ?>
								<div class="row"><span class="k"><?php esc_html_e( 'Link', 'still' ); ?></span><a href="<?php echo esc_url( $still_link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $still_link ) ) ); ?></a></div>
							<?php endif; ?>
						</dl>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php foreach ( $still_plates as $still_k => $still_pid ) : ?>
				<div class="hx-panel hx-panel--media"><figure><?php echo wp_get_attachment_image( $still_pid, 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( get_the_title() . ' — ' . ( $still_k + 1 ) ) ) ); ?></figure></div>
			<?php endforeach; ?>

			<section class="hx-panel hx-panel--end">
				<nav class="entry-nav">
					<a href="<?php echo esc_url( still_work_url() ); ?>">← <?php esc_html_e( 'All work', 'still' ); ?></a>
					<a href="<?php echo esc_url( still_page_url( 'enquire', 'enquire' ) ); ?>"><?php esc_html_e( 'Enquire about a shoot →', 'still' ); ?></a>
				</nav>
			</section>

		</div>
		<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
	</main>
<?php endwhile; ?>

<?php get_footer(); ?>
