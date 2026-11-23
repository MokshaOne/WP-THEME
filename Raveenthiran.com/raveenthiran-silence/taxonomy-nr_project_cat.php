<?php
/**
 * Work — the Index, Silence III. Full editorial head with ghost
 * numeral, giant serif rows that stagger in, turn italic, and step
 * forward on hover while a preview plate trails the cursor. Rows carry
 * inline thumbnails on mobile (where the float is hidden). Built for
 * large collections; filters run client-side and renumber.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$terms = get_terms( [ 'taxonomy' => 'nr_project_cat', 'hide_empty' => true ] );
$count = 0;
$total = (int) $GLOBALS['wp_query']->found_posts;
?>

<section class="nr-index" data-index>

	<?php /* cursor-trailing preview plate (desktop, motion-on) */ ?>
	<div class="nr-index__float" data-index-float aria-hidden="true"><img src="" alt="" decoding="async"></div>

	<div class="nr-index__inner">
		<header class="nr-index__head">
			<span class="nr-ghost nr-ghost--head" aria-hidden="true"><?php echo esc_html( str_pad( (string) $total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="nr-eyebrow"><?php esc_html_e( 'The collection', 'raveenthiran-silence' ); ?> — <?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?></span>
			<h1 class="nr-index__display"><?php esc_html_e( 'Index', 'raveenthiran-silence' ); ?> <em>(<?php echo esc_html( $total ); ?>)</em></h1>
			<?php if ( $terms && ! is_wp_error( $terms ) && count( $terms ) > 1 ) : ?>
				<nav class="nr-filters" aria-label="<?php esc_attr_e( 'Filter by category', 'raveenthiran-silence' ); ?>">
					<button type="button" class="is-on" data-filter=""><?php esc_html_e( 'All', 'raveenthiran-silence' ); ?> <i><?php echo esc_html( $total ); ?></i></button>
					<?php foreach ( $terms as $t ) : ?>
						<button type="button" data-filter="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?> <i><?php echo esc_html( $t->count ); ?></i></button>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<ol class="nr-index__list" data-index-list>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				$count++;
				$m     = nr_project_meta();
				$cats  = get_the_terms( get_the_ID(), 'nr_project_cat' );
				$slugs = ( $cats && ! is_wp_error( $cats ) ) ? implode( ' ', wp_list_pluck( $cats, 'slug' ) ) : '';
				$img   = get_the_post_thumbnail_url( get_the_ID(), 'nr-index' );
				$n     = count( nr_project_gallery( get_the_ID() ) );
			?>
				<li data-cats="<?php echo esc_attr( $slugs ); ?>" class="nr-rise" style="transition-delay:<?php echo (int) ( min( $count, 12 ) * 45 ); ?>ms">
					<a href="<?php the_permalink(); ?>" data-preview="<?php echo esc_url( $img ?: '' ); ?>">
						<span class="nr-index__n"><?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( $img ) : ?>
							<span class="nr-index__thumb" aria-hidden="true"><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy" decoding="async"></span>
						<?php endif; ?>
						<span class="nr-index__t"><?php the_title(); ?></span>
						<span class="nr-index__m">
							<?php echo esc_html( trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'] ] ) ), ' ·' ) ); ?>
							<?php if ( $n > 1 ) : ?><em><?php echo esc_html( $n ); ?> <?php esc_html_e( 'plates', 'raveenthiran-silence' ); ?></em><?php endif; ?>
						</span>
						<span class="nr-index__go" aria-hidden="true">→</span>
					</a>
				</li>
			<?php endwhile; else : ?>
				<li class="nr-index__empty"><?php esc_html_e( 'No projects yet — add some under Projects in the admin.', 'raveenthiran-silence' ); ?></li>
			<?php endif; ?>
		</ol>
	</div>
</section>

<?php get_footer(); ?>
