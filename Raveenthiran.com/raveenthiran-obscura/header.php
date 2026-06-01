<?php
/**
 * Header — top bar · custom cursor · sidebar slot · booking modal slot.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_avail       = nr_opt( 'nr_available', '1' ) === '1';
$nr_avail_text  = nr_opt( 'nr_avail_text', 'Available · 2026' );
$nr_booking_url = nr_opt( 'nr_booking_url', home_url( '/booking' ) );
$nr_logo_text   = nr_opt( 'nr_logo_text', 'raveenthiran' );
$nr_logo_sub    = nr_opt( 'nr_logo_sub', 'studio · ' . date_i18n( 'Y' ) );
$nr_book_label  = nr_opt( 'nr_book_label', __( 'Book a shoot', 'raveenthiran' ) );
$nr_accent      = nr_opt( 'nr_accent', '#F2A03D' );
$nr_color_bg    = nr_opt( 'nr_color_bg', '#0B0C10' );
$nr_color_ink   = nr_opt( 'nr_color_ink', '#F2EFE9' );
$nr_current     = $nr_current ?? '';

// hex → "r,g,b" so we can derive ink-2/3/4/hair as rgba() with the right hue
if ( ! function_exists( 'nr_hex_to_rgb_string' ) ) {
	function nr_hex_to_rgb_string( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( strlen( $hex ) === 3 ) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) return '242,239,233';
		return hexdec( substr( $hex, 0, 2 ) ) . ',' . hexdec( substr( $hex, 2, 2 ) ) . ',' . hexdec( substr( $hex, 4, 2 ) );
	}
}
$ink_rgb = nr_hex_to_rgb_string( $nr_color_ink );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="#0B0C10">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<style>
	:root{
		--amber:<?php echo esc_attr( $nr_accent ); ?>;
		--amber-soft:rgba(242,160,61,.18);
		--bg:<?php echo esc_attr( $nr_color_bg ); ?>;
		--ink:<?php echo esc_attr( $nr_color_ink ); ?>;
		--ink-2:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.62);
		--ink-3:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.42);
		--ink-4:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.12);
		--ink-5:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.06);
		--hair:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.14);
		--hair-soft:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.06);
	}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'nr nr-page-' . esc_attr( $nr_current ?: 'default' ) ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'raveenthiran' ); ?></a>

<?php /* ── custom cursor (desktop only) ─────────────────────── */ ?>
<div class="nr-cur" aria-hidden="true"><span class="nr-cur__lbl">view</span></div>

<?php /* ── top bar ───────────────────────────────────────────── */ ?>
<header class="nr-topbar" role="banner">
	<a class="nr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>"
	   data-logo-sub="<?php echo esc_attr( $nr_logo_sub ); ?>"
	   style="--logo-sub:'<?php echo esc_attr( $nr_logo_sub ); ?>'">
		<?php echo esc_html( $nr_logo_text ); ?>
	</a>

	<nav class="nr-menu" aria-label="<?php esc_attr_e( 'Primary', 'raveenthiran' ); ?>">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 1,
				'fallback_cb'    => false,
			] );
		} else {
			$nr_tpl_url = function ( $tpl, $slug ) {
				return function_exists( 'nr_template_page_url' ) ? nr_template_page_url( $tpl, $slug ) : home_url( '/' . $slug );
			};
			$items = [
				'home'      => [ __( 'Showcase',  'raveenthiran' ), home_url( '/' ) ],
				'portfolio' => [ __( 'Work',      'raveenthiran' ), get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ],
				'about'     => [ __( 'Studio',    'raveenthiran' ), $nr_tpl_url( 'page-about.php',   'about' ) ],
				'enquire'   => [ __( 'Enquire',   'raveenthiran' ), function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ],
			];
			foreach ( $items as $key => $it ) {
				$cls = $nr_current === $key ? ' aria-current="page"' : '';
				printf( '<a href="%s"%s>%s</a>', esc_url( $it[1] ), $cls, esc_html( $it[0] ) );
			}
		}
		?>
	</nav>

	<div class="nr-topbar-right">
		<?php if ( $nr_avail ) : ?>
			<span class="nr-avail" aria-label="<?php echo esc_attr( $nr_avail_text ); ?>">
				<span class="nr-avail-dot" aria-hidden="true"></span>
				<?php echo esc_html( $nr_avail_text ); ?>
			</span>
		<?php endif; ?>

		<a class="nr-book-trigger" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : $nr_booking_url ); ?>">
			<span><?php echo esc_html( $nr_book_label ); ?> →</span>
		</a>

		<button type="button" class="nr-hamb" aria-label="<?php esc_attr_e( 'Menu', 'raveenthiran' ); ?>" data-sidebar-open>
			<span></span><span></span>
		</button>
	</div>
</header>

<?php /* ── sidebar (mobile primary nav) ─────────────────────── */ ?>
<div class="nr-sidebar-backdrop" data-sidebar-close></div>
<aside class="nr-sidebar" aria-label="<?php esc_attr_e( 'Mobile navigation', 'raveenthiran' ); ?>">
	<button type="button" class="nr-sidebar__close" data-sidebar-close aria-label="<?php esc_attr_e( 'Close menu', 'raveenthiran' ); ?>">✕</button>
	<nav class="nr-sidebar__nav" aria-label="<?php esc_attr_e( 'Primary mobile', 'raveenthiran' ); ?>">
		<?php
		$mobile_items = [
			[ __( 'Showcase', 'raveenthiran' ), home_url( '/' ) ],
			[ __( 'Work',     'raveenthiran' ), get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ],
			[ __( 'Studio',   'raveenthiran' ), function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-about.php',   'about' )   : home_url( '/about' ) ],
			[ __( 'Enquire',  'raveenthiran' ), function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ],
		];
		foreach ( $mobile_items as $it ) {
			printf( '<a href="%s">%s</a>', esc_url( $it[1] ), esc_html( $it[0] ) );
		}
		?>
	</nav>
	<div class="nr-sidebar__group">
		<h4><?php esc_html_e( '— Elsewhere', 'raveenthiran' ); ?></h4>
		<?php
		$socials = [
			'Instagram' => nr_opt( 'nr_instagram' ),
			'Behance'   => nr_opt( 'nr_behance' ),
			'Vimeo'     => nr_opt( 'nr_vimeo' ),
			'LinkedIn'  => nr_opt( 'nr_linkedin' ),
		];
		foreach ( $socials as $label => $url ) {
			if ( $url ) printf( '<a href="%s" target="_blank" rel="noopener">%s ↗</a>', esc_url( $url ), esc_html( $label ) );
		}
		?>
	</div>
</aside>

<main id="main" class="nr-main">
