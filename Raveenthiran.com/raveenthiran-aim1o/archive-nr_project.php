<?php
/**
 * aim1o — Portfolio (HORIZONTAL scroll). One sweeping filmstrip: an intro
 * panel, one panel per specimen, and a closing CTA. Vertical wheel drives the
 * horizontal track (mk-scroll.js); native swipe on touch. Data via nr_*.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();

$count   = (int) ( wp_count_posts( 'nr_project' )->publish ?? 0 );
$arc_ttl = nr_opt( 'nr_void_archive_title', 'THE ARCHIVE' );
$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
?>

<div class="mk-h" data-mk-h>
	<div class="mk-h__track">

		<?php /* ── intro panel ──────────────────────────────── */ ?>
		<section class="mk-panel mk-panel--intro">
			<span class="mk-panel__index">00 / <?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( nr_opt( 'nr_work_eyebrow', 'FIELD ARCHIVE' ) ); ?></p>
			<h1 class="void-display-hero" style="text-align:left"><?php echo esc_html( $arc_ttl ); ?></h1>
			<p class="void-mono" style="margin-top:18px;max-width:44ch"><?php echo esc_html( nr_opt( 'nr_work_intro', 'A moving archive of light. Scroll sideways to travel the collection — each frame a coordinate in the void.' ) ); ?></p>
		</section>

		<?php /* ── one panel per specimen ───────────────────── */ ?>
		<?php if ( have_posts() ) : $i = 0; while ( have_posts() ) : the_post(); $i++;
			$m    = function_exists( 'nr_project_meta' ) ? nr_project_meta() : [];
			$num  = ! empty( $m['n'] ) ? $m['n'] : str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
			$tags = wp_get_post_terms( get_the_ID(), 'nr_project_tag', [ 'fields' => 'names' ] );
			$tags = is_wp_error( $tags ) ? [] : $tags;
		?>
			<section class="mk-panel">
				<span class="mk-panel__index"><?php echo esc_html( $num ); ?> / <?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
				<a class="mk-work" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
					<div class="mk-work__frame">
						<?php
						if ( function_exists( 'nr_image_or_placeholder' ) ) nr_image_or_placeholder( get_the_ID(), 'nr-card', get_the_title() );
						elseif ( has_post_thumbnail() ) the_post_thumbnail( 'nr-card', [ 'alt' => get_the_title() ] );
						?>
					</div>
					<div class="mk-work__meta">
						<span class="void-label-luxury"><?php echo esc_html( strtoupper( $m['cat'] ?? 'SPECIMEN' ) ); ?></span>
						<h2 class="mk-work__title"><?php the_title(); ?></h2>
						<span class="void-mono"><?php echo esc_html( trim( ( $m['loc'] ?? '' ) . ' · ' . ( $m['yr'] ?? '' ), ' ·' ) ); ?></span>
						<?php if ( $tags ) : ?>
							<span class="mk-work__tags"><?php foreach ( array_slice( $tags, 0, 4 ) as $t ) printf( '<span class="mk-work__tag">%s</span>', esc_html( $t ) ); ?></span>
						<?php endif; ?>
						<span class="void-btn void-btn-outline" style="margin-top:24px"><?php esc_html_e( 'OPEN SPECIMEN', 'raveenthiran' ); ?> →</span>
					</div>
				</a>
			</section>
		<?php endwhile; else : ?>
			<section class="mk-panel"><p class="void-empty"><?php esc_html_e( 'No specimens catalogued yet.', 'raveenthiran' ); ?></p></section>
		<?php endif; ?>

		<?php /* ── closing CTA panel ────────────────────────── */ ?>
		<section class="mk-panel mk-panel--intro">
			<p class="void-eyebrow"><span class="void-rule"></span><?php esc_html_e( 'END OF STREAM', 'raveenthiran' ); ?></p>
			<h2 class="void-h1-huge void-h1-uncap"><?php esc_html_e( 'Begin your own', 'raveenthiran' ); ?><br><span class="void-gold-ital"><?php esc_html_e( 'exposure.', 'raveenthiran' ); ?></span></h2>
			<a class="void-btn void-btn-gold" style="margin-top:28px;align-self:flex-start" href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'INITIATE ENQUIRY', 'raveenthiran' ); ?> →</a>
		</section>

	</div>
</div>

<span class="mk-hint"><?php esc_html_e( 'SCROLL TO TRAVEL', 'raveenthiran' ); ?> <i></i></span>

<?php get_footer(); ?>
