<?php
/**
 * Studio (About) — HORIZONTAL: intro → statement → stats. Reads your existing
 * Theme-Settings options (nr_about_lede, nr_about_bio, nr_stats_*) via nr_opt(),
 * so the Studio content you already entered displays. Auto-applies to slug
 * "about". Falls back to the page's own editor content.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
the_post();

$still_lede  = function_exists( 'nr_opt' ) ? nr_opt( 'nr_about_lede', '' ) : '';
$still_bio   = function_exists( 'nr_opt' ) ? nr_opt( 'nr_about_bio', '' ) : '';
$still_stats = array(
	__( 'Projects', 'still' )     => function_exists( 'nr_opt' ) ? nr_opt( 'nr_stats_proj', '' ) : '',
	__( 'Countries', 'still' )    => function_exists( 'nr_opt' ) ? nr_opt( 'nr_stats_cnty', '' ) : '',
	__( 'Publications', 'still' ) => function_exists( 'nr_opt' ) ? nr_opt( 'nr_stats_pubs', '' ) : '',
	__( 'Awards', 'still' )       => function_exists( 'nr_opt' ) ? nr_opt( 'nr_stats_awd', '' ) : '',
);
$still_has_stats = (bool) array_filter( $still_stats, function ( $v ) { return '' !== $v && null !== $v; } );
$still_body = trim( get_the_content() );
?>
<main id="main" class="hx">
	<div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php esc_html_e( 'Studio', 'still' ); ?></span>
				<h1 class="page-title"><?php the_title(); ?></h1>
				<?php if ( $still_lede ) : ?><p class="page-lead"><?php echo esc_html( $still_lede ); ?></p><?php endif; ?>
			</div>
		</section>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="hx-panel hx-panel--media"><figure><?php the_post_thumbnail( 'still-hero', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></figure></div>
		<?php endif; ?>

		<?php if ( $still_bio || $still_body ) : ?>
			<section class="hx-panel hx-panel--wide">
				<div class="prose">
					<?php
					if ( $still_bio )  { echo wp_kses_post( wpautop( $still_bio ) ); }
					if ( $still_body ) { the_content(); }
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $still_has_stats ) : ?>
			<section class="hx-panel hx-panel--contact">
				<dl class="contact-list">
					<?php foreach ( $still_stats as $k => $v ) : if ( '' === $v || null === $v ) continue; ?>
						<div class="row"><span class="k"><?php echo esc_html( $k ); ?></span><span><?php echo esc_html( $v ); ?></span></div>
					<?php endforeach; ?>
				</dl>
			</section>
		<?php endif; ?>

	</div>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>

<?php get_footer(); ?>
