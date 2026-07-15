<?php
/**
 * VOID header — tactical HUD: scanline · wordmark · mono nav · access CTA.
 * Uses the shared nr_* helpers so content/menus match Obscura/Aurelius.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_accent    = nr_opt( 'nr_accent', '#f2ca50' );
$nr_color_bg  = nr_opt( 'nr_color_bg', '#0a0a0a' );
$nr_color_ink = nr_opt( 'nr_color_ink', '#e2e2e2' );
$nr_current   = $nr_current ?? '';
$nr_wordmark  = nr_opt( 'nr_logo_text', 'raveenthiran' );
$nr_word_sub  = nr_opt( 'nr_void_sub', '// VOID' );
$nr_cta_label = nr_opt( 'nr_void_cta', __( 'Enquire', 'raveenthiran' ) );
$nr_enquire   = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
$nr_lat       = nr_opt( 'nr_void_lat', '48.2082° N' );
$nr_long      = nr_opt( 'nr_void_long', '16.3738° E' );

$nr_nav_items = [
	[ __( 'Archive',   'raveenthiran' ), get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ), 'portfolio' ],
	[ __( 'Studio',    'raveenthiran' ), function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-about.php', 'about' ) : home_url( '/about' ), 'about' ],
	[ __( 'Chronicle', 'raveenthiran' ), function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' ), 'journal' ],
];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="<?php echo esc_attr( $nr_color_bg ); ?>">
	<style>
	:root{
		--accent:<?php echo esc_attr( $nr_accent ); ?>;--amber:<?php echo esc_attr( $nr_accent ); ?>;
		--void-gold:<?php echo esc_attr( $nr_accent ); ?>;
	}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'void nr nr-page-' . esc_attr( $nr_current ?: 'default' ) ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'raveenthiran' ); ?></a>

<?php /* HUD scanline overlay (static texture is in CSS body::before; this is the tint layer) */ ?>
<div class="void-hud-scanline" aria-hidden="true"></div>

<nav class="void-nav" role="banner">
	<div class="void-nav-brand">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="void-wordmark" aria-label="<?php bloginfo( 'name' ); ?>">
			<?php echo esc_html( $nr_wordmark ); ?><span class="void-wordmark-sub"><?php echo esc_html( $nr_word_sub ); ?></span>
		</a>
	</div>
	<div class="void-nav-links" aria-label="<?php esc_attr_e( 'Primary', 'raveenthiran' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [ 'theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'fallback_cb' => false, 'link_before' => '', 'menu_class' => '' ] );
		} else {
			foreach ( $nr_nav_items as $it ) {
				$cls = 'void-nav-link' . ( $nr_current === $it[2] ? ' is-active' : '' );
				printf( '<a class="%s" href="%s">%s</a>', esc_attr( $cls ), esc_url( $it[1] ), esc_html( $it[0] ) );
			}
		}
		?>
	</div>
	<a class="void-btn void-btn-primary void-nav-cta" href="<?php echo esc_url( $nr_enquire ); ?>"><?php echo esc_html( $nr_cta_label ); ?></a>
	<button class="void-nav-burger" id="void-nav-burger" aria-label="<?php esc_attr_e( 'Menu', 'raveenthiran' ); ?>" aria-expanded="false"><span></span><span></span><span></span></button>
</nav>

<main id="main" class="void-main">
