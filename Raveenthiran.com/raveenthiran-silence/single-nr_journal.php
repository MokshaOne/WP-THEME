<?php
/**
 * Journal entry — Silence III. A full-bleed cover, a serif masthead
 * with the date, a drop-capped reading column, a scroll-progress
 * hairline, and a handoff to the next entry.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$cat  = get_the_terms( get_the_ID(), 'nr_journal_cat' );
	$cat  = ( $cat && ! is_wp_error( $cat ) ) ? $cat[0]->name : '';
	$next = get_previous_post(); // older entry (reads as "next" chronologically forward is get_next_post; older feels natural here)
	if ( ! $next ) {
		$more = get_posts( [ 'post_type' => 'nr_journal', 'posts_per_page' => 1, 'post__not_in' => [ get_the_ID() ], 'orderby' => 'rand' ] );
		$next = $more[0] ?? null;
	}
?>

<article class="nr-entry">
	<span class="nr-bar" aria-hidden="true"><i data-page-bar></i></span>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="nr-entry__cover" data-parallax>
			<?php the_post_thumbnail( 'nr-plate', [ 'loading' => 'eager', 'decoding' => 'async', 'sizes' => '100vw' ] ); ?>
			<div class="nr-entry__cover-shade"></div>
		</div>
	<?php endif; ?>

	<header class="nr-entry__head<?php echo has_post_thumbnail() ? ' nr-entry__head--over' : ''; ?>">
		<a class="nr-back nr-ui" href="<?php echo esc_url( get_post_type_archive_link( 'nr_journal' ) ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran-silence' ); ?></a>
		<span class="nr-entry__meta">
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			<?php if ( $cat ) : ?> · <?php echo esc_html( $cat ); ?><?php endif; ?>
		</span>
		<h1 class="nr-entry__title"><?php the_title(); ?></h1>
	</header>

	<div class="nr-entry__body nr-prose">
		<?php the_content(); ?>
	</div>

	<?php if ( $next ) : ?>
		<a class="nr-project__next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
			<span class="nr-eyebrow"><?php esc_html_e( 'Keep reading', 'raveenthiran-silence' ); ?></span>
			<span class="nr-project__next-t"><?php echo esc_html( get_the_title( $next ) ); ?> →</span>
		</a>
	<?php endif; ?>
</article>

<?php endwhile; get_footer(); ?>
