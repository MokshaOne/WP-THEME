<?php
/**
 * Portfolio archive — single-screen filter chips + grid.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();

$cats = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => false ] );
$total = wp_count_posts( 'nr_project' )->publish ?? 0;

/**
 * Decide a project's card orientation from its featured image dimensions.
 * Portrait → 4:5 cell, landscape → 5:4 cell. Defaults to portrait when
 * dimensions can't be read (matches the editorial portfolio default).
 */
$nr_card_orient = function ( $post_id ) {
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumb_id ) return 'portrait';
	$meta = wp_get_attachment_metadata( $thumb_id );
	if ( ! is_array( $meta ) || empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return 'portrait';
	}
	return ( (int) $meta['width'] > (int) $meta['height'] ) ? 'landscape' : 'portrait';
};
?>
<section class="nr-page nr-fullscreen nr-portfolio-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_work_eyebrow', __( 'Portfolio', 'raveenthiran' ) ) . ' · ' . sprintf( _n( '%d work', '%d works', (int) $total, 'raveenthiran' ), (int) $total ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo wp_kses( nr_opt( 'nr_work_title', __( 'The complete <em>portfolio.</em>', 'raveenthiran' ) ), [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		</div>

		<div class="nr-chips nr-chips--center" data-group="main" role="tablist" aria-label="<?php esc_attr_e( 'Filter by category', 'raveenthiran' ); ?>">
			<button type="button" class="nr-chip is-on" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'All', 'raveenthiran' ); ?></button>
			<?php foreach ( $cats as $c ) : ?>
				<button type="button" class="nr-chip" data-filter="<?php echo esc_attr( $c->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $c->name ); ?><sup class="nr-chip__n"><?php echo (int) $c->count; ?></sup></button>
			<?php endforeach; ?>
			<?php $nr_years = function_exists( 'nr_project_years' ) ? nr_project_years() : []; foreach ( $nr_years as $yy ) : ?>
				<button type="button" class="nr-chip nr-chip--year" data-filter="year-<?php echo esc_attr( $yy ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $yy ); ?></button>
			<?php endforeach; ?>
		</div>

		<?php
		// #64 — keyword tags: multi-select chips that AND with the category/year above.
		$nr_tags = get_terms( [ 'taxonomy' => 'nr_project_tag', 'hide_empty' => true ] );
		if ( ! is_wp_error( $nr_tags ) && $nr_tags ) : ?>
			<div class="nr-chips nr-chips--center nr-chips--tags" data-group="tags" aria-label="<?php esc_attr_e( 'Filter by keyword (combine freely)', 'raveenthiran' ); ?>">
				<?php foreach ( $nr_tags as $t ) : ?>
					<button type="button" class="nr-chip nr-chip--tag" data-filter="tag-<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false"><?php echo esc_html( $t->name ); ?><sup class="nr-chip__n"><?php echo (int) $t->count; ?></sup></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="nr-portfolio-rail" data-portfolio data-h-rail>
		<?php
		$nr_per_page = max( 4, min( 96, (int) nr_opt( 'nr_work_per_page', 24 ) ) );
		$nr_paged    = max( 1, (int) get_query_var( 'paged', 1 ) );
		if ( $nr_paged === 1 ) $nr_paged = max( 1, (int) get_query_var( 'page', 1 ) );

		$pq = new WP_Query( [
			'post_type'      => 'nr_project',
			'posts_per_page' => $nr_per_page,
			'paged'          => $nr_paged,
			'orderby'        => 'menu_order date',
			'order'          => 'DESC',
		] );

		if ( $pq->have_posts() ) :
			$i = 0;
			while ( $pq->have_posts() ) : $pq->the_post(); $m = nr_project_meta();
				$terms  = wp_get_post_terms( get_the_ID(), 'nr_project_cat', [ 'fields' => 'slugs' ] );
				$tags   = wp_get_post_terms( get_the_ID(), 'nr_project_tag', [ 'fields' => 'slugs' ] );
				$tag_tokens = is_array( $tags ) ? array_map( function ( $s ) { return 'tag-' . $s; }, $tags ) : [];
				$orient = $nr_card_orient( get_the_ID() );
				$gallery = nr_field( 'project_gallery', get_the_ID() );
				$plate_count = is_array( $gallery ) ? count( $gallery ) : 0;
				if ( ! $plate_count && has_post_thumbnail() ) $plate_count = 1;
				$plate_label = sprintf( _n( '%d plate', '%d plates', $plate_count, 'raveenthiran' ), $plate_count );
				?>
				<a class="nr-card is-<?php echo esc_attr( $orient ); ?>" href="<?php the_permalink(); ?>" data-cats="<?php echo esc_attr( trim( implode( ' ', $terms ?: [] ) . ' year-' . $m['yr'] . ' ' . implode( ' ', $tag_tokens ) ) ); ?>" style="--i:<?php echo (int) $i; ?>;view-transition-name:vt-project-<?php the_ID(); ?>">
					<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true ); ?>
					<div class="nr-card__shade"></div>
					<span class="nr-card__no" aria-hidden="true">PL—<?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<div class="nr-card__head"><span><?php echo esc_html( $plate_label ); ?></span><span><?php echo esc_html( $m['yr'] ); ?></span></div>
					<div class="nr-card__foot">
						<span class="nr-card__cat"><?php echo esc_html( $m['cat'] ?: 'Editorial' ); ?></span>
						<span class="nr-card__title<?php echo ( $i % 3 === 1 ) ? ' is-em' : ''; ?>"><?php the_title(); ?></span>
					</div>
				</a>
				<?php $i++;
			endwhile;
			wp_reset_postdata();
		else :
			// 8 placeholders so the layout reads correctly on empty installs
			$samples = [
				[ '24 plates','Nachtdienst','Editorial',2024,'portrait' ],
				[ '12 plates','A House in Mariahilf','Editorial',2024,'landscape' ],
				[ '18 plates','The Vienna Notebook','Portrait',2024,'portrait' ],
				[ '9 plates', 'Naschmarkt at Dawn','Street',2023,'landscape' ],
				[ '14 plates','Belvedere After Hours','Architecture',2023,'portrait' ],
				[ '6 plates', 'Rote Bar','Event',2023,'landscape' ],
				[ '32 plates','Salzkammergut','Editorial',2023,'portrait' ],
				[ '8 plates', 'Studio · NK','Portrait',2022,'portrait' ],
			];
			foreach ( $samples as $i => $s ) : ?>
				<a class="nr-card is-<?php echo esc_attr( $s[4] ); ?>" href="#" data-cats="<?php echo esc_attr( strtolower( $s[2] ) ); ?>" style="--i:<?php echo (int) $i; ?>">
					<?php echo nr_placeholder( strtolower( $s[1] ), true, 'auto' ); ?>
					<div class="nr-card__shade"></div>
					<div class="nr-card__head"><span><?php echo esc_html( $s[0] ); ?></span><span><?php echo esc_html( $s[3] ); ?></span></div>
					<div class="nr-card__foot">
						<span class="nr-card__cat"><?php echo esc_html( $s[2] ); ?></span>
						<span class="nr-card__title<?php echo ( $i % 3 === 1 ) ? ' is-em' : ''; ?>"><?php echo esc_html( $s[1] ); ?></span>
					</div>
				</a>
			<?php endforeach;
		endif;
		wp_reset_postdata();
		?>
	</div>

	<?php
	// Pager — only shown when more than one page of results exists.
	if ( isset( $pq ) && $pq instanceof WP_Query && $pq->max_num_pages > 1 ) :
		$base = trailingslashit( get_post_type_archive_link( 'nr_project' ) );
		$prev = $nr_paged > 1 ? user_trailingslashit( $base . 'page/' . ( $nr_paged - 1 ) . '/' ) : '';
		$next = $nr_paged < $pq->max_num_pages ? user_trailingslashit( $base . 'page/' . ( $nr_paged + 1 ) . '/' ) : '';
		?>
		<nav class="nr-work-pager" aria-label="<?php esc_attr_e( 'Portfolio pages', 'raveenthiran' ); ?>">
			<span class="nr-work-pager__count">
				<?php
				/* translators: 1: current page, 2: total pages */
				printf( esc_html__( 'Page %1$s of %2$s', 'raveenthiran' ),
					'<b>' . esc_html( str_pad( (string) $nr_paged, 2, '0', STR_PAD_LEFT ) ) . '</b>',
					esc_html( str_pad( (string) $pq->max_num_pages, 2, '0', STR_PAD_LEFT ) )
				);
				?>
			</span>
			<span class="nr-work-pager__nav">
				<?php if ( $prev ) : ?><a class="nr-icon-btn nr-icon-btn--fill" href="<?php echo esc_url( $prev ); ?>" aria-label="<?php esc_attr_e( 'Previous page', 'raveenthiran' ); ?>"><span>←</span></a><?php endif; ?>
				<?php if ( $next ) : ?><a class="nr-icon-btn nr-icon-btn--fill" href="<?php echo esc_url( $next ); ?>" aria-label="<?php esc_attr_e( 'Next page', 'raveenthiran' ); ?>"><span>→</span></a><?php endif; ?>
			</span>
		</nav>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
