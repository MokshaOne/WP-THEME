<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$nr_enq = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
?>
<section class="nr-page nr-404">
	<span class="nr-eyebrow">404</span>
	<h1 class="nr-display nr-display--xl"><?php esc_html_e( 'Page not', 'raveenthiran' ); ?> <em><?php esc_html_e( 'found.', 'raveenthiran' ); ?></em></h1>
	<p class="nr-404__lede">
		<?php esc_html_e( 'The page you are looking for has moved or never existed. Try the portfolio, or start a project.', 'raveenthiran' ); ?>
	</p>
	<div class="nr-404__actions">
		<a class="nr-btn nr-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span><?php esc_html_e( 'Back home', 'raveenthiran' ); ?></span> <span>→</span></a>
		<a class="nr-btn" href="<?php echo esc_url( $nr_enq ); ?>"><span><?php esc_html_e( 'Enquire', 'raveenthiran' ); ?></span></a>
	</div>

	<?php
	// #29 — suggest a few projects so a dead end still leads somewhere.
	$nr_sugg = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 3, 'orderby' => 'rand' ] );
	if ( $nr_sugg ) : ?>
		<div class="nr-404__suggest">
			<span class="nr-eyebrow nr-eyebrow--xs"><?php esc_html_e( 'Maybe one of these', 'raveenthiran' ); ?></span>
			<div class="nr-404__cards">
				<?php foreach ( $nr_sugg as $p ) : ?>
					<a class="nr-card is-portrait" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<?php nr_image_or_placeholder( $p->ID, 'nr-card', strtolower( get_the_title( $p ) ), true ); ?>
						<div class="nr-card__shade"></div>
						<div class="nr-card__foot">
							<span class="nr-card__title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
