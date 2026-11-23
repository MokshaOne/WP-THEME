<?php
/**
 * M1O Transmission · footer
 * (front page) reverse marquee + CTA band, then colophon.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$id       = m1o_get_identity();
$show_cta = m1o_opt( 'm1o_show_cta', '1' ) !== '0';

if ( is_front_page() && $show_cta && $id['email'] ) :
	$rev = '';
	for ( $r = 0; $r < 4; $r++ ) {
		$rev .= '<span>' . esc_html( $id['cta'] ) . '</span><i>&#10022;</i><span>' . esc_html( $id['email'] ) . '</span><i>&#10022;</i><span>' . esc_html( strtok( $id['location'], ',' ) ) . '</span><i>&#10022;</i>';
	}
?>
<div class="marquee rev" aria-hidden="true">
	<div class="track">
		<div class="half"><?php echo $rev; // escaped above ?></div>
		<div class="half"><?php echo $rev; // escaped above ?></div>
	</div>
</div>

<section class="cta">
	<div class="rv">
		<div class="eyebrow" data-scramble>&#9654; <?php esc_html_e( 'Open frequency', 'm1o' ); ?></div>
		<h2><?php echo esc_html( $id['cta'] ); ?><em>.</em></h2>
	</div>
	<a class="chip mag rv" href="mailto:<?php echo esc_attr( $id['email'] ); ?>">
		<span class="v"><?php echo esc_html( $id['email'] ); ?></span>
		<span class="k arr">&#8594;</span>
	</a>
</section>
<?php endif; ?>

<footer class="colophon" role="contentinfo">
	<div class="col rv">
		<span><span class="au">&#9679;</span> M1O.AT &#183; <?php esc_html_e( 'Built in Vienna', 'm1o' ); ?> &#183; &#169; <?php echo esc_html( date_i18n( 'Y' ) ); ?></span>
		<span class="dim">
			<?php
			if ( has_nav_menu( 'legal' ) ) {
				wp_nav_menu( [
					'theme_location' => 'legal',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'walker'         => new class extends Walker_Nav_Menu {
						public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
							$output .= '<a href="' . esc_url( $item->url ) . '">' . esc_html( $item->title ) . '</a> &middot; ';
						}
						public function end_el( &$output, $item, $depth = 0, $args = null ) {}
					},
				] );
			}
			?>
			<?php esc_html_e( 'Created by', 'm1o' ); ?> <a class="au" href="https://on1.agency" target="_blank" rel="noopener">on1.agency</a>
		</span>
	</div>
	<span class="rv" data-scramble><?php esc_html_e( 'End of transmission', 'm1o' ); ?></span>
</footer>

<?php wp_footer(); ?>
</body>
</html>
