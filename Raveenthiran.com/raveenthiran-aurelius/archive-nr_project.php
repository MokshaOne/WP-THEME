<?php
/**
 * Work archive — Studio: scrolling editorial grid with category / year /
 * keyword filters. Filter chips + cards keep the theme.js hooks
 * (.nr-chip[data-filter], [data-portfolio] .nr-card[data-cats]).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();

$cats  = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => false ] );
$total = wp_count_posts( 'nr_project' )->publish ?? 0;

$nr_card_orient = function ( $post_id ) {
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumb_id ) return 'portrait';
	$meta = wp_get_attachment_metadata( $thumb_id );
	if ( ! is_array( $meta ) || empty( $meta['width'] ) || empty( $meta['height'] ) ) return 'portrait';
	return ( (int) $meta['width'] > (int) $meta['height'] ) ? 'landscape' : 'portrait';
};
?>
<section class="st-page nr-portfolio-archive">
	<header class="st-wrap st-arc-head">
		<div class="st-arc-head__text">
			<span class="st-eyebrow"><?php echo esc_html( nr_opt( 'nr_work_eyebrow', __( 'Portfolio', 'raveenthiran' ) ) . ' · ' . sprintf( _n( '%d work', '%d works', (int) $total, 'raveenthiran' ), (int) $total ) ); ?></span>
			<h1 class="st-arc-title"><?php echo wp_kses( nr_opt( 'nr_work_title', __( 'The complete <em>portfolio</em>', 'raveenthiran' ) ), [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		</div>

		<div class="st-filters">
			<div class="nr-chips" data-group="main" role="tablist" aria-label="<?php esc_attr_e( 'Filter by category', 'raveenthiran' ); ?>">
				<button type="button" class="nr-chip is-on" data-filter="all" role="tab" aria-selected="true"><?php esc_html_e( 'All', 'raveenthiran' ); ?></button>
				<?php foreach ( $cats as $c ) : ?>
					<button type="button" class="nr-chip" data-filter="<?php echo esc_attr( $c->slug ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $c->name ); ?></button>
				<?php endforeach; ?>
				<?php $nr_years = function_exists( 'nr_project_years' ) ? nr_project_years() : []; foreach ( $nr_years as $yy ) : ?>
					<button type="button" class="nr-chip nr-chip--year" data-filter="year-<?php echo esc_attr( $yy ); ?>" role="tab" aria-selected="false"><?php echo esc_html( $yy ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php
			$nr_tags = get_terms( [ 'taxonomy' => 'nr_project_tag', 'hide_empty' => true ] );
			if ( ! is_wp_error( $nr_tags ) && $nr_tags ) : ?>
				<div class="nr-chips nr-chips--tags" data-group="tags" aria-label="<?php esc_attr_e( 'Filter by keyword (combine freely)', 'raveenthiran' ); ?>">
					<?php foreach ( $nr_tags as $t ) : ?>
						<button type="button" class="nr-chip nr-chip--tag" data-filter="tag-<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false"><?php echo esc_html( $t->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<div class="st-wrap">
		<div class="st-grid" data-portfolio data-reveal-stagger>
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
					$terms      = wp_get_post_terms( get_the_ID(), 'nr_project_cat', [ 'fields' => 'slugs' ] );
					$tags       = wp_get_post_terms( get_the_ID(), 'nr_project_tag', [ 'fields' => 'slugs' ] );
					$tag_tokens = is_array( $tags ) ? array_map( function ( $s ) { return 'tag-' . $s; }, $tags ) : [];
					$orient     = $nr_card_orient( get_the_ID() );
					?>
					<a class="nr-card st-card is-<?php echo esc_attr( $orient ); ?>" href="<?php the_permalink(); ?>" data-cats="<?php echo esc_attr( trim( implode( ' ', $terms ?: [] ) . ' year-' . $m['yr'] . ' ' . implode( ' ', $tag_tokens ) ) ); ?>" style="view-transition-name:vt-project-<?php the_ID(); ?>">
						<div class="st-card__frame">
							<?php if ( has_post_thumbnail() ) :
								echo get_the_post_thumbnail( get_the_ID(), 'nr-card', [ 'alt' => get_the_title(), 'loading' => 'lazy', 'decoding' => 'async', 'class' => 'st-card__img', 'sizes' => '(max-width:600px) 100vw, (max-width:1000px) 50vw, 32vw' ] );
							else :
								echo nr_placeholder( strtolower( get_the_title() ), false, $orient === 'landscape' ? '5/4' : '4/5' );
							endif; ?>
						</div>
						<div class="st-card__cap">
							<span class="st-card__t"><?php the_title(); ?></span>
							<span class="st-card__m"><?php echo esc_html( ( $m['cat'] ?: 'Editorial' ) . ' · ' . $m['yr'] ); ?></span>
						</div>
					</a>
					<?php $i++;
				endwhile;
				wp_reset_postdata();
			else :
				$samples = [
					[ 'Nachtdienst','Editorial',2024,'portrait' ],
					[ 'A House in Mariahilf','Architecture',2024,'landscape' ],
					[ 'The Vienna Notebook','Portrait',2024,'portrait' ],
					[ 'Naschmarkt at Dawn','Street',2023,'landscape' ],
					[ 'Belvedere After Hours','Architecture',2023,'portrait' ],
					[ 'Rote Bar','Event',2023,'landscape' ],
					[ 'Salzkammergut','Editorial',2023,'portrait' ],
					[ 'Studio · NK','Portrait',2022,'portrait' ],
				];
				foreach ( $samples as $i => $s ) : ?>
					<a class="nr-card st-card is-<?php echo esc_attr( $s[3] ); ?>" href="#" data-cats="<?php echo esc_attr( strtolower( $s[1] ) . ' year-' . $s[2] ); ?>">
						<div class="st-card__frame"><?php echo nr_placeholder( strtolower( $s[0] ), false, $s[3] === 'landscape' ? '5/4' : '4/5' ); ?></div>
						<div class="st-card__cap">
							<span class="st-card__t"><?php echo esc_html( $s[0] ); ?></span>
							<span class="st-card__m"><?php echo esc_html( $s[1] . ' · ' . $s[2] ); ?></span>
						</div>
					</a>
				<?php endforeach;
			endif;
			wp_reset_postdata();
			?>
		</div>

		<?php
		if ( isset( $pq ) && $pq instanceof WP_Query && $pq->max_num_pages > 1 ) :
			$base = trailingslashit( get_post_type_archive_link( 'nr_project' ) );
			$prev = $nr_paged > 1 ? user_trailingslashit( $base . 'page/' . ( $nr_paged - 1 ) . '/' ) : '';
			$next = $nr_paged < $pq->max_num_pages ? user_trailingslashit( $base . 'page/' . ( $nr_paged + 1 ) . '/' ) : '';
			?>
			<nav class="st-pager" aria-label="<?php esc_attr_e( 'Portfolio pages', 'raveenthiran' ); ?>">
				<span class="st-pager__count"><?php printf( esc_html__( 'Page %1$s of %2$s', 'raveenthiran' ), '<b>' . esc_html( str_pad( (string) $nr_paged, 2, '0', STR_PAD_LEFT ) ) . '</b>', esc_html( str_pad( (string) $pq->max_num_pages, 2, '0', STR_PAD_LEFT ) ) ); ?></span>
				<span class="st-pager__nav">
					<?php if ( $prev ) : ?><a class="st-btn st-btn--line" href="<?php echo esc_url( $prev ); ?>">← <?php esc_html_e( 'Newer', 'raveenthiran' ); ?></a><?php endif; ?>
					<?php if ( $next ) : ?><a class="st-btn st-btn--line" href="<?php echo esc_url( $next ); ?>"><?php esc_html_e( 'Older', 'raveenthiran' ); ?> →</a><?php endif; ?>
				</span>
			</nav>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
