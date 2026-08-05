<?php
/**
 * Global dock navigation + document close.
 * The dock is the site's primary nav on every page. On the front page JS keeps
 * it hidden until the intro finishes, then reveals it and makes its items
 * smooth-scroll to the matching teaser panel; on inner pages the items link
 * straight to the real pages.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$still_items = still_nav_items();
?>
<nav id="dock" aria-label="<?php esc_attr_e( 'Primary', 'still' ); ?>">
	<?php foreach ( $still_items as $it ) :
		$active = '';
		if ( ! is_front_page() ) {
			if ( 'work' === $it['key'] && ( is_post_type_archive( 'work' ) || is_singular( 'work' ) || is_tax( 'work_category' ) ) ) {
				$active = ' active';
			} elseif ( 'journal' === $it['key'] && ( is_home() || is_singular( 'post' ) || is_category() || is_tag() ) ) {
				$active = ' active';
			} elseif ( 'studio' === $it['key'] && is_page( array( 'about', 'studio' ) ) ) {
				$active = ' active';
			} elseif ( is_page( $it['key'] ) ) {
				$active = ' active';
			}
		}
		printf(
			'<a href="%s" data-key="%s" class="%s">%s</a>',
			esc_url( $it['url'] ),
			esc_attr( $it['key'] ),
			esc_attr( trim( $active ) ),
			esc_html( $it['label'] )
		);
	endforeach; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>
