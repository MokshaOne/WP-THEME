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
			if ( 'work' === $it['key'] && ( is_post_type_archive( 'nr_project' ) || is_singular( 'nr_project' ) || is_tax( 'nr_project_cat' ) || is_tax( 'nr_project_tag' ) || is_tax( 'nr_project_series' ) ) ) {
				$active = ' active';
			} elseif ( 'journal' === $it['key'] && ( is_post_type_archive( 'nr_journal' ) || is_singular( 'nr_journal' ) || is_tax( 'nr_journal_cat' ) || is_home() || is_singular( 'post' ) ) ) {
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

<div id="cookie-bar" role="dialog" aria-label="<?php esc_attr_e( 'Cookie notice', 'still' ); ?>">
	<p><?php
		printf(
			/* translators: %s: link to the privacy page */
			esc_html__( 'This site uses minimal cookies for basic functionality and, if you enquire, to handle your message. See %s.', 'still' ),
			'<a href="' . esc_url( still_page_url( 'datenschutz', 'datenschutz' ) ) . '">' . esc_html__( 'Privacy', 'still' ) . '</a>'
		);
	?></p>
	<div class="cookie-actions">
		<button type="button" class="decline"><?php esc_html_e( 'Decline', 'still' ); ?></button>
		<button type="button" class="accept"><?php esc_html_e( 'Accept', 'still' ); ?></button>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
