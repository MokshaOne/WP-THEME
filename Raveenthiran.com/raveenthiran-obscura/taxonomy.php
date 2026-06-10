<?php
/**
 * Taxonomy archives — series / tags / project categories / journal categories.
 * One template for all theme taxonomies: term intro + the matching card rail
 * (work cards for project terms, index cards for journal terms).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_term       = get_queried_object();
$nr_is_journal = $nr_term && $nr_term->taxonomy === 'nr_journal_cat';
$nr_current    = $nr_is_journal ? 'journal' : 'portfolio';
get_header();

$nr_tax_labels = [
	'nr_project_series' => __( 'Series', 'raveenthiran' ),
	'nr_project_tag'    => __( 'Tag', 'raveenthiran' ),
	'nr_project_cat'    => __( 'Category', 'raveenthiran' ),
	'nr_journal_cat'    => __( 'Journal', 'raveenthiran' ),
];
$nr_kind  = $nr_term ? ( $nr_tax_labels[ $nr_term->taxonomy ] ?? __( 'Archive', 'raveenthiran' ) ) : __( 'Archive', 'raveenthiran' );
$nr_count = $nr_term ? (int) $nr_term->count : 0;

$nr_card_orient = function ( $post_id, $default = 'portrait' ) {
	$tid = get_post_thumbnail_id( $post_id );
	if ( ! $tid ) return $default;
	$m = wp_get_attachment_metadata( $tid );
	if ( ! is_array( $m ) || empty( $m['width'] ) || empty( $m['height'] ) ) return $default;
	return ( (int) $m['width'] > (int) $m['height'] ) ? 'landscape' : 'portrait';
};
?>
<section class="nr-page nr-fullscreen nr-portfolio-archive">
	<div class="nr-page__head nr-page__head--stack">
		<div class="nr-page__head-text">
			<span class="nr-eyebrow"><?php echo esc_html( $nr_kind . ' · ' . sprintf( _n( '%d entry', '%d entries', $nr_count, 'raveenthiran' ), $nr_count ) ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php echo esc_html( $nr_term ? $nr_term->name : get_the_archive_title() ); ?></h1>
			<?php if ( $nr_term && term_description( $nr_term ) ) : ?>
				<p class="nr-page__head-desc"><?php echo wp_kses_post( term_description( $nr_term ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="nr-chips nr-chips--center">
			<a class="nr-chip" href="<?php echo esc_url( $nr_is_journal ? ( function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' ) ) : ( get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ) ); ?>">
				← <?php echo $nr_is_journal ? esc_html__( 'All entries', 'raveenthiran' ) : esc_html__( 'All work', 'raveenthiran' ); ?>
			</a>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="nr-portfolio-rail<?php echo $nr_is_journal ? ' nr-journal-rail' : ''; ?>" data-h-rail>
			<?php $i = 0; while ( have_posts() ) : the_post();
				$orient = $nr_card_orient( get_the_ID(), $nr_is_journal ? 'landscape' : 'portrait' );
				if ( $nr_is_journal ) : ?>
					<a class="nr-card nr-card--journal is-<?php echo esc_attr( $orient ); ?>" href="<?php the_permalink(); ?>" style="--i:<?php echo (int) $i; ?>">
						<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true ); ?>
						<div class="nr-card__shade"></div>
						<div class="nr-card__head"><span><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span></div>
						<div class="nr-card__foot">
							<span class="nr-card__cat"><?php echo esc_html( $nr_term->name ); ?></span>
							<span class="nr-card__title<?php echo ( $i % 3 === 1 ) ? ' is-em' : ''; ?>"><?php the_title(); ?></span>
							<span class="nr-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></span>
						</div>
					</a>
				<?php else :
					$m = function_exists( 'nr_project_meta' ) ? nr_project_meta() : [ 'yr' => get_the_date( 'Y' ), 'cat' => '' ]; ?>
					<a class="nr-card is-<?php echo esc_attr( $orient ); ?>" href="<?php the_permalink(); ?>" style="--i:<?php echo (int) $i; ?>">
						<?php nr_image_or_placeholder( get_the_ID(), 'nr-card', strtolower( get_the_title() ), true ); ?>
						<div class="nr-card__shade"></div>
						<span class="nr-card__no" aria-hidden="true">PL—<?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div class="nr-card__head"><span><?php echo esc_html( $nr_term->name ); ?></span><span><?php echo esc_html( $m['yr'] ); ?></span></div>
						<div class="nr-card__foot">
							<span class="nr-card__cat"><?php echo esc_html( $m['cat'] ?: 'Editorial' ); ?></span>
							<span class="nr-card__title<?php echo ( $i % 3 === 1 ) ? ' is-em' : ''; ?>"><?php the_title(); ?></span>
						</div>
					</a>
				<?php endif; $i++;
			endwhile; ?>
		</div>
	<?php else : ?>
		<div class="nr-portfolio-rail"><p class="nr-journal__empty"><?php esc_html_e( 'Nothing here yet.', 'raveenthiran' ); ?></p></div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
