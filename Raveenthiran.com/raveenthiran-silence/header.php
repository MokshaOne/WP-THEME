<?php
/**
 * Header — wordmark, quiet text nav, availability. Everything chrome
 * carries .nr-ui so it can fall silent on idle.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_studio  = nr_opt( 'nr_logo_text', 'raveenthiran' );
$nr_tagline = nr_opt( 'nr_tagline', '' );
$nr_avail   = nr_opt( 'nr_avail_text', '' );
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

<header class="nr-top nr-ui" role="banner">
	<a class="nr-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<em><?php echo esc_html( $nr_studio ); ?></em><?php if ( $nr_tagline ) : ?><span><?php echo esc_html( $nr_tagline ); ?></span><?php endif; ?>
	</a>

	<nav class="nr-nav" aria-label="<?php esc_attr_e( 'Primary', 'raveenthiran-silence' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'fallback_cb' => false ] );
		} else {
			$items = [
				[ __( 'Work', 'raveenthiran-silence' ),    get_post_type_archive_link( 'nr_project' ) ?: home_url( '/work' ),    is_post_type_archive( 'nr_project' ) || is_singular( 'nr_project' ) ],
				[ __( 'Journal', 'raveenthiran-silence' ), get_post_type_archive_link( 'nr_journal' ) ?: home_url( '/journal' ), is_post_type_archive( 'nr_journal' ) || is_singular( 'nr_journal' ) ],
				[ __( 'About', 'raveenthiran-silence' ),   nr_template_page_url( 'page-about.php', 'about' ),                    is_page_template( 'page-about.php' ) ],
				[ __( 'Enquire', 'raveenthiran-silence' ), nr_template_page_url( 'page-enquire.php', 'enquire' ),                is_page_template( 'page-enquire.php' ) ],
			];
			foreach ( $items as $it ) {
				printf( '<a href="%s"%s>%s</a>', esc_url( $it[1] ), $it[2] ? ' aria-current="page"' : '', esc_html( $it[0] ) );
			}
		}
		?>
	</nav>

	<?php if ( $nr_avail ) : ?>
		<span class="nr-avail"><?php echo esc_html( $nr_avail ); ?></span>
	<?php endif; ?>
</header>

<main id="main" class="nr-main">
