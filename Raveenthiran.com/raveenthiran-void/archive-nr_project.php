<?php
/**
 * VOID system archive — specimen grid (nr_project). Category chips preserve the
 * shared theme.js filter (.nr-chip[data-filter] + .nr-card[data-cats]).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();

$cats = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => true ] );
$count = (int) ( wp_count_posts( 'nr_project' )->publish ?? 0 );
?>

<section class="void-screen void-archive">

	<div class="void-scanline-beam" aria-hidden="true"></div>

	<div class="void-archive-head">
		<div>
			<p class="void-tech-label"><?php echo esc_html( nr_opt( 'nr_work_eyebrow', 'CLASSIFICATION: FIELD ARCHIVE' ) ); ?></p>
			<h1 class="void-h1"><?php echo esc_html( nr_opt( 'nr_void_archive_title', 'SYSTEM ARCHIVE 0x4F' ) ); ?></h1>
		</div>
		<div class="void-archive-head-meta">
			<div class="void-tech-label void-gold">SCAN_INDEX: <?php echo esc_html( str_pad( (string) $count, 3, '0', STR_PAD_LEFT ) ); ?></div>
			<div class="void-tech-label">SENSOR_STATE: NOMINAL</div>
			<div class="void-tech-label">ENCRYPTION: <?php echo esc_html( nr_opt( 'nr_void_encryption', 'AES-256' ) ); ?></div>
		</div>
	</div>

	<?php if ( ! is_wp_error( $cats ) && count( $cats ) > 1 ) : ?>
	<div class="void-chip-row void-archive-filter" role="tablist" aria-label="<?php esc_attr_e( 'Filter specimens', 'raveenthiran' ); ?>">
		<button class="nr-chip void-chip is-on" data-filter="*" data-group="main"><?php esc_html_e( 'ALL', 'raveenthiran' ); ?></button>
		<?php foreach ( $cats as $c ) : ?>
			<button class="nr-chip void-chip" data-filter="<?php echo esc_attr( $c->slug ); ?>" data-group="main"><?php echo esc_html( strtoupper( $c->name ) ); ?></button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="void-specimen-grid">
		<?php if ( have_posts() ) : $i = 0; while ( have_posts() ) : the_post(); $i++;
			$m    = function_exists( 'nr_project_meta' ) ? nr_project_meta() : [];
			$slugs = wp_get_post_terms( get_the_ID(), 'nr_project_cat', [ 'fields' => 'slugs' ] );
			$slugs = is_wp_error( $slugs ) ? [] : $slugs;
			$num  = ! empty( $m['n'] ) ? $m['n'] : str_pad( (string) $i, 3, '0', STR_PAD_LEFT );
		?>
			<a href="<?php the_permalink(); ?>" class="void-specimen-card nr-card" data-cats="<?php echo esc_attr( implode( ',', $slugs ) ); ?>">
				<span class="void-card-tag void-card-tag-left"><?php echo esc_html( $m['cat'] ?? 'DATA_STREAM' ); ?></span>
				<span class="void-card-tag void-card-tag-right">SPECIMEN_<?php echo esc_html( $num ); ?></span>
				<div class="void-card-media">
					<?php
					if ( function_exists( 'nr_image_or_placeholder' ) ) {
						nr_image_or_placeholder( get_the_ID(), 'nr-card', get_the_title() );
					} elseif ( has_post_thumbnail() ) {
						the_post_thumbnail( 'nr-card', [ 'alt' => get_the_title() ] );
					}
					?>
				</div>
				<div class="void-card-footer">
					<h3><?php the_title(); ?></h3>
					<span class="void-card-dot"></span>
				</div>
			</a>
		<?php endwhile; else : ?>
			<p class="void-empty"><?php esc_html_e( 'No specimens catalogued yet.', 'raveenthiran' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="void-archive-status">
		<div class="void-status-group">
			<span class="void-tech-label void-gold"><span class="void-dot-ping"></span>ARCHIVE_STREAM_ACTIVE</span>
			<span class="void-tech-label">SECURITY: <span class="void-green">MAXIMUM</span></span>
		</div>
		<div class="void-status-group">
			<span class="void-tech-label">COORD: <?php echo esc_html( nr_opt( 'nr_void_lat', '48.2082° N' ) ); ?></span>
			<span class="void-tech-label">LOCAL_TIME: <span id="void-clock">00:00:00</span></span>
		</div>
	</div>

	<?php the_posts_pagination( [ 'prev_text' => '← PREV', 'next_text' => 'NEXT →', 'class' => 'void-pagination' ] ); ?>

</section>

<?php get_footer(); ?>
