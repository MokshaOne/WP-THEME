<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
$arc_url = get_post_type_archive_link( 'nr_project' ) ?: home_url( '/' );
?>
<section class="void-screen void-404">
	<div class="void-scanline-beam" aria-hidden="true"></div>
	<div class="void-404-inner">
		<p class="void-eyebrow void-eyebrow-center"><span class="void-rule"></span><?php esc_html_e( 'SIGNAL LOST', 'raveenthiran' ); ?><span class="void-rule"></span></p>
		<h1 class="void-display-hero">4<span class="void-gold-ital">0</span>4</h1>
		<p class="void-empty"><?php esc_html_e( 'This coordinate returns no specimen. The vector has collapsed into the void.', 'raveenthiran' ); ?></p>
		<a class="void-btn void-btn-gold" href="<?php echo esc_url( $arc_url ); ?>"><?php esc_html_e( 'RETURN TO ARCHIVE', 'raveenthiran' ); ?></a>
	</div>
</section>
<?php get_footer();
