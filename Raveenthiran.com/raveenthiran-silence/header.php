<?php
/**
 * Header — wordmark, quiet text nav, availability. Everything chrome
 * carries .sl-ui so it can fall silent on idle.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$sl_studio  = sl_opt( 'sl_studio', 'raveenthiran' );
$sl_tagline = sl_opt( 'sl_tagline', '' );
$sl_avail   = sl_opt( 'sl_availability', '' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="#0A0A0B">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'sl' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'raveenthiran-silence' ); ?></a>

<header class="sl-top sl-ui" role="banner">
	<a class="sl-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php echo esc_html( $sl_studio ); ?><?php if ( $sl_tagline ) : ?><span><?php echo esc_html( $sl_tagline ); ?></span><?php endif; ?>
	</a>

	<nav class="sl-nav" aria-label="<?php esc_attr_e( 'Primary', 'raveenthiran-silence' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'fallback_cb' => false ] );
		} else {
			$items = [
				[ __( 'Work', 'raveenthiran-silence' ),    get_post_type_archive_link( sl_pt() ) ?: home_url( '/work' ),    is_post_type_archive( sl_pt() ) || is_singular( sl_pt() ) ],
				[ __( 'Journal', 'raveenthiran-silence' ), get_post_type_archive_link( sl_jt() ) ?: home_url( '/journal' ), is_post_type_archive( sl_jt() ) || is_singular( sl_jt() ) ],
				[ __( 'About', 'raveenthiran-silence' ),   sl_template_page_url( 'page-about.php', 'about' ),                    is_page_template( 'page-about.php' ) ],
				[ __( 'Enquire', 'raveenthiran-silence' ), sl_template_page_url( 'page-enquire.php', 'enquire' ),                is_page_template( 'page-enquire.php' ) ],
			];
			foreach ( $items as $it ) {
				printf( '<a href="%s"%s>%s</a>', esc_url( $it[1] ), $it[2] ? ' aria-current="page"' : '', esc_html( $it[0] ) );
			}
		}
		?>
	</nav>

	<?php if ( $sl_avail ) : ?>
		<span class="sl-avail"><?php echo esc_html( $sl_avail ); ?></span>
	<?php endif; ?>
</header>

<main id="main" class="sl-main">
