<?php
/**
 * Journal single — Studio: full-bleed lead image + centred article.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();
the_post();

$nr_cat = '';
$nr_tn  = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
if ( ! is_wp_error( $nr_tn ) && $nr_tn ) $nr_cat = $nr_tn[0];
$nr_jurl = function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' );
?>
<article class="st-jpost">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="st-jpost__hero">
			<?php echo get_the_post_thumbnail( get_the_ID(), 'nr-hero', [ 'alt' => get_the_title(), 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '100vw', 'class' => 'st-jpost__hero-img' ] ); ?>
		</div>
	<?php endif; ?>

	<div class="st-wrap st-jpost__body">
		<a class="st-link st-jpost__back" href="<?php echo esc_url( $nr_jurl ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran' ); ?></a>
		<span class="st-eyebrow"><?php echo esc_html( ( $nr_cat ?: nr_opt( 'nr_journal_eyebrow', __( 'Journal', 'raveenthiran' ) ) ) . ' · ' . get_the_date() ); ?></span>
		<h1 class="st-jpost__title"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?><p class="st-jpost__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>

		<div class="st-prose"><?php the_content(); ?></div>

		<div class="st-jpost__foot">
			<a class="st-btn st-btn--line" href="<?php echo esc_url( $nr_jurl ); ?>"><?php esc_html_e( 'All entries', 'raveenthiran' ); ?></a>
			<a class="st-btn st-btn--primary" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><?php esc_html_e( 'Start a project', 'raveenthiran' ); ?> →</a>
		</div>
	</div>
</article>
<?php get_footer(); ?>
