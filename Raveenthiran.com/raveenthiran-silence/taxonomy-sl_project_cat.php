<?php
/**
 * Work — the Index. A typographic list of every project (built for
 * large collections: the list scrolls inside the fixed shell), with
 * a full-bleed preview plate that fades in behind on hover.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$terms = get_terms( [ 'taxonomy' => sl_tax(), 'hide_empty' => true ] );
$count = 0;
?>

<section class="sl-index" data-index>

	<div class="sl-index__preview" aria-hidden="true" data-index-preview></div>

	<div class="sl-index__inner">
		<header class="sl-index__head sl-ui">
			<h1 class="sl-eyebrow"><?php post_type_archive_title(); ?></h1>
			<?php if ( $terms && ! is_wp_error( $terms ) && count( $terms ) > 1 ) : ?>
				<nav class="sl-filters" aria-label="<?php esc_attr_e( 'Filter by category', 'raveenthiran-silence' ); ?>">
					<button type="button" class="is-on" data-filter=""><?php esc_html_e( 'All', 'raveenthiran-silence' ); ?></button>
					<?php foreach ( $terms as $t ) : ?>
						<button type="button" data-filter="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></button>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</header>

		<ol class="sl-index__list" data-index-list>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				$count++;
				$m     = sl_project_meta();
				$cats  = get_the_terms( get_the_ID(), sl_tax() );
				$slugs = ( $cats && ! is_wp_error( $cats ) ) ? implode( ' ', wp_list_pluck( $cats, 'slug' ) ) : '';
				$img   = get_the_post_thumbnail_url( get_the_ID(), 'sl-index' );
				$n     = count( sl_project_gallery( get_the_ID() ) );
			?>
				<li data-cats="<?php echo esc_attr( $slugs ); ?>">
					<a href="<?php the_permalink(); ?>" data-preview="<?php echo esc_url( $img ?: '' ); ?>">
						<span class="sl-index__n"><?php echo esc_html( str_pad( (string) $count, 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="sl-index__t"><?php the_title(); ?></span>
						<span class="sl-index__m">
							<?php echo esc_html( trim( implode( ' · ', array_filter( [ $m['cat'], $m['yr'] ] ) ), ' ·' ) ); ?>
							<?php if ( $n > 1 ) : ?><em><?php echo esc_html( $n ); ?> <?php esc_html_e( 'plates', 'raveenthiran-silence' ); ?></em><?php endif; ?>
						</span>
					</a>
				</li>
			<?php endwhile; else : ?>
				<li class="sl-index__empty"><?php esc_html_e( 'No projects yet — add some under Projects in the admin.', 'raveenthiran-silence' ); ?></li>
			<?php endif; ?>
		</ol>
	</div>
</section>

<?php get_footer(); ?>
