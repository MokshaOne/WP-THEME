<?php
/**
 * Journal single — scrollable long-form article (#60).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'journal';
get_header();
the_post();
?>
<section class="nr-page nr-journal-single">
	<article class="nr-journal__article">
		<header class="nr-journal__article-head">
			<a class="nr-journal__back" href="<?php echo esc_url( nr_journal_url() ); ?>">← <?php esc_html_e( 'Journal', 'raveenthiran' ); ?></a>
			<span class="nr-eyebrow"><?php echo esc_html( get_the_date() ); ?></span>
			<h1 class="nr-display nr-display--lg"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="nr-journal__lede"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
		</header>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="nr-journal__cover"><?php the_post_thumbnail( 'nr-hero', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ] ); ?></div>
		<?php endif; ?>
		<div class="nr-journal__body"><?php the_content(); ?></div>
		<footer class="nr-journal__article-foot">
			<a class="nr-btn" href="<?php echo esc_url( nr_journal_url() ); ?>"><span><?php esc_html_e( 'All entries', 'raveenthiran' ); ?></span></a>
			<a class="nr-btn nr-btn--primary" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>"><span><?php esc_html_e( 'Start a project', 'raveenthiran' ); ?></span> <span>→</span></a>
		</footer>
	</article>
</section>
<?php get_footer(); ?>
