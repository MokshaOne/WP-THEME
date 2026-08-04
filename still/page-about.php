<?php
/**
 * Studio / About — pulls the engine's about settings (Theme Settings) + stats,
 * plus any page content and an optional awards/press list (ACF option fields).
 * Auto-applies to a Page with slug "about".
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
<main id="main" class="page-wrap">
	<header class="page-head">
		<span class="label"><?php echo esc_html( $still_eyebrow ); ?></span>
		<h1 class="page-title"><?php echo wp_kses( $still_title, array( 'em' => array() ) ); ?></h1>
		<?php if ( $still_lede ) : ?><p class="page-lead"><?php echo esc_html( $still_lede ); ?></p><?php endif; ?>
	</header>

	<div class="two-col">
		<div class="prose">
			<?php
			if ( $still_bio ) {
				echo wp_kses_post( wpautop( $still_bio ) );
			}
			while ( have_posts() ) { the_post(); the_content(); }
			?>
			<p class="label" style="margin-top:2rem"><?php echo esc_html( $still_name ); ?> — <?php echo esc_html( $still_role ); ?></p>
		</div>
		<?php if ( $still_portrait ) : ?>
			<div class="about-portrait"><?php echo wp_get_attachment_image( $still_portrait, 'still-card', false, array( 'style' => 'width:100%;height:auto', 'alt' => esc_attr( $still_name ) ) ); ?></div>
		<?php endif; ?>
	</div>

	<?php if ( array_filter( $still_stats ) ) : ?>
	<div class="stats">
		<?php foreach ( $still_stats as $k => $v ) : if ( '' === $v ) continue; ?>
			<div><div class="n"><?php echo esc_html( $v ); ?></div><div class="k"><?php echo esc_html( $k ); ?></div></div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<footer class="about-footer">
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
</main>
<?php get_footer(); ?>
