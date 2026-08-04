<?php
/**
 * Studio / About — HORIZONTAL: intro → bio → portrait → stats → footer,
 * scrolled sideways. Reverts to vertical on phones / reduced-motion.
 * Data from the engine's Theme Settings. Auto-applies to slug "about".
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_eyebrow  = nr_opt( 'nr_about_eyebrow', __( 'Studio', 'still' ) );
$still_title    = nr_opt( 'nr_about_title', 'A practice of <em>looking,</em> slowly.' );
$still_lede     = nr_opt( 'nr_about_lede', '' );
$still_bio      = nr_opt( 'nr_about_bio', '' );
$still_name     = nr_opt( 'nr_about_name', get_bloginfo( 'name' ) );
$still_role     = nr_opt( 'nr_about_role', __( 'Photographer · Vienna', 'still' ) );
$still_portrait = (int) nr_opt( 'nr_about_portrait_id', 0 );
$still_stats = array(
	__( 'Projects', 'still' )     => nr_opt( 'nr_stats_proj', '' ),
	__( 'Countries', 'still' )    => nr_opt( 'nr_stats_cnty', '' ),
	__( 'Publications', 'still' ) => nr_opt( 'nr_stats_pubs', '' ),
	__( 'Awards', 'still' )       => nr_opt( 'nr_stats_awd', '' ),
);
?>
<main id="main" class="hx">
	<div class="hx__sticky"><div class="hx__track">

		<section class="hx-panel hx-panel--head">
			<div class="page-head">
				<span class="label"><?php echo esc_html( $still_eyebrow ); ?></span>
				<h1 class="page-title"><?php echo wp_kses( $still_title, array( 'em' => array() ) ); ?></h1>
				<?php if ( $still_lede ) : ?><p class="page-lead"><?php echo esc_html( $still_lede ); ?></p><?php endif; ?>
			</div>
		</section>

		<section class="hx-panel hx-panel--wide">
			<div class="prose">
				<?php
				if ( $still_bio ) { echo wp_kses_post( wpautop( $still_bio ) ); }
				while ( have_posts() ) { the_post(); the_content(); }
				?>
				<p class="label" style="margin-top:2rem"><?php echo esc_html( $still_name ); ?> — <?php echo esc_html( $still_role ); ?></p>
			</div>
		</section>

		<?php if ( $still_portrait ) : ?>
			<div class="hx-panel hx-panel--media"><figure><?php echo wp_get_attachment_image( $still_portrait, 'still-hero', false, array( 'alt' => esc_attr( $still_name ) ) ); ?></figure></div>
		<?php endif; ?>

		<?php if ( array_filter( $still_stats ) ) : ?>
			<section class="hx-panel">
				<div class="stats" style="border:0;padding:0;margin:0">
					<?php foreach ( $still_stats as $k => $v ) : if ( '' === $v ) continue; ?>
						<div><div class="n"><?php echo esc_html( $v ); ?></div><div class="k"><?php echo esc_html( $k ); ?></div></div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="hx-panel hx-panel--head">
			<footer class="about-footer" style="border:0;padding:0;margin:0;flex-direction:column;align-items:flex-start;gap:1.4rem">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $still_name ); ?></span>
				<div class="legal">
					<?php
					foreach ( array( 'Instagram' => nr_opt( 'nr_instagram', '' ), 'LinkedIn' => nr_opt( 'nr_linkedin', '' ), 'Behance' => nr_opt( 'nr_behance', '' ), 'Vimeo' => nr_opt( 'nr_vimeo', '' ) ) as $k => $u ) {
						if ( $u ) { echo '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( $k ) . '</a>'; }
					}
					?>
					<a href="<?php echo esc_url( still_page_url( 'impressum', 'impressum' ) ); ?>"><?php esc_html_e( 'Impressum', 'still' ); ?></a>
					<a href="<?php echo esc_url( still_page_url( 'agb', 'agb' ) ); ?>"><?php esc_html_e( 'AGB', 'still' ); ?></a>
					<a href="<?php echo esc_url( still_page_url( 'datenschutz', 'datenschutz' ) ); ?>"><?php esc_html_e( 'Datenschutz', 'still' ); ?></a>
				</div>
				<span><?php echo esc_html( nr_opt( 'nr_location', 'Vienna, AT' ) ); ?></span>
			</footer>
		</section>

	</div></div>
	<span class="hx-cue"><?php esc_html_e( 'Scroll', 'still' ); ?> <i></i></span>
</main>
<?php get_footer(); ?>
