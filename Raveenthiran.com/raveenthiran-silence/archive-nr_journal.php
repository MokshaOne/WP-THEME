<?php
/**
 * Journal — Silence III. A dated serif index; hovering an entry floats
 * its cover plate beside the cursor (same mechanism as the Work index).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$total = (int) $GLOBALS['wp_query']->found_posts;
$count = 0;
?>

<section class="nr-index nr-journal-index" data-index>

	<div class="nr-index__float" data-index-float aria-hidden="true"><img src="" alt="" decoding="async"></div>

	<div class="nr-index__inner">
		<header class="nr-index__head">
			<span class="nr-ghost nr-ghost--head" aria-hidden="true"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="nr-eyebrow"><?php esc_html_e( 'Notes from the field', 'raveenthiran-silence' ); ?></span>
			<h1 class="nr-index__display"><?php esc_html_e( 'Journal', 'raveenthiran-silence' ); ?> <em>(<?php echo esc_html( $total ); ?>)</em></h1>
		</header>

		<ol class="nr-index__list nr-jlist" data-index-list>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				$count++;
				$img = get_the_post_thumbnail_url( get_the_ID(), 'nr-index' );
				$cat = get_the_terms( get_the_ID(), 'nr_journal_cat' );
				$cat = ( $cat && ! is_wp_error( $cat ) ) ? $cat[0]->name : '';
			?>
				<li class="nr-rise" style="transition-delay:<?php echo (int) ( min( $count, 12 ) * 45 ); ?>ms">
					<a href="<?php the_permalink(); ?>" data-preview="<?php echo esc_url( $img ?: '' ); ?>">
						<span class="nr-index__n"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y · m' ) ); ?></time></span>
						<?php if ( $img ) : ?>
							<span class="nr-index__thumb" aria-hidden="true"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" decoding="async"></span>
						<?php endif; ?>
						<span class="nr-index__t"><?php the_title(); ?></span>
						<span class="nr-index__m"><?php echo esc_html( $cat ); ?></span>
						<span class="nr-index__go" aria-hidden="true">→</span>
					</a>
				</li>
			<?php endwhile; else : ?>
				<li class="nr-index__empty"><?php esc_html_e( 'Nothing written yet.', 'raveenthiran-silence' ); ?></li>
			<?php endif; ?>
		</ol>

		<?php the_posts_pagination( [ 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ] ); ?>
	</div>
</section>

<?php get_footer(); ?>
