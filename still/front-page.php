<?php
/**
 * Front page — the cinematic home. After the intro (header.php), a vertical
 * snap-scroll of monumental TEASER panels, one per menu item. Each is a single
 * giant word with a ghost numeral behind it, and links OUT to its real page —
 * it never replaces it.
 *
 * @package Still
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$still_items = still_nav_items();
$still_sub = array(
	'work'    => __( 'Selected work', 'still' ),
	'studio'  => __( 'About', 'still' ),
	'journal' => __( 'Writing', 'still' ),
	'contact' => __( 'Enquire', 'still' ),
	'enquire' => __( 'Commissions', 'still' ),
);
$still_cta = array(
	'work'    => __( 'Open portfolio', 'still' ),
	'studio'  => __( 'Read more', 'still' ),
	'journal' => __( 'Latest entries', 'still' ),
	'contact' => __( 'Get in touch', 'still' ),
	'enquire' => __( 'Start a project', 'still' ),
);
$still_i = 0;
?>
<div id="home">
<?php foreach ( $still_items as $it ) : $still_i++; ?>
	<section class="panel" id="panel-<?php echo esc_attr( $it['key'] ); ?>" data-key="<?php echo esc_attr( $it['key'] ); ?>">
		<span class="ghost" aria-hidden="true"><?php echo esc_html( still_pad( $still_i ) ); ?></span>
		<div class="panel__in">
			<div class="panel__lbl label"><?php echo esc_html( $still_sub[ $it['key'] ] ?? '' ); ?></div>
			<h2 class="panel__name"><?php echo esc_html( $it['label'] ); ?></h2>
			<p class="panel__desc"><?php echo esc_html( $it['desc'] ); ?></p>
			<a class="panel__go" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $still_cta[ $it['key'] ] ?? __( 'Open', 'still' ) ); ?></a>
		</div>
	</section>
<?php endforeach; ?>
</div>
<?php get_footer(); ?>
