<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$nr_enq = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
?>
<section class="st-page st-404">
	<div class="st-wrap">
		<span class="st-eyebrow">404</span>
		<h1 class="st-404__title"><?php esc_html_e( 'Page not', 'raveenthiran' ); ?> <em><?php esc_html_e( 'found', 'raveenthiran' ); ?></em></h1>
		<p class="st-404__lede"><?php esc_html_e( 'The page you are looking for has moved or never existed. Try the portfolio, or start a project.', 'raveenthiran' ); ?></p>
		<div class="st-404__actions">
			<a class="st-btn st-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'raveenthiran' ); ?> →</a>
			<a class="st-btn st-btn--line" href="<?php echo esc_url( $nr_enq ); ?>"><?php esc_html_e( 'Enquire', 'raveenthiran' ); ?></a>
		</div>

		<?php
		$nr_sugg = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => 3, 'orderby' => 'rand' ] );
		if ( $nr_sugg ) : ?>
			<div class="st-404__suggest">
				<span class="st-eyebrow"><?php esc_html_e( 'Maybe one of these', 'raveenthiran' ); ?></span>
				<div class="st-grid st-grid--three">
					<?php foreach ( $nr_sugg as $p ) : ?>
						<a class="st-card" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
							<div class="st-card__frame"><?php nr_image_or_placeholder( $p->ID, 'nr-card', strtolower( get_the_title( $p ) ), false ); ?></div>
							<div class="st-card__cap"><span class="st-card__t"><?php echo esc_html( get_the_title( $p ) ); ?></span></div>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
