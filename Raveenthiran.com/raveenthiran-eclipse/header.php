<?php
/**
 * Header — Studio: sticky editorial header + mobile drawer.
 * Light, scrolling multipage. The header sits transparent over the
 * front-page hero and turns solid on scroll (studio.js adds .is-solid).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nr_avail       = nr_opt( 'nr_available', '1' ) === '1';
$nr_avail_text  = nr_opt( 'nr_avail_text', 'Available for 2026' );
$nr_logo_text   = nr_opt( 'nr_logo_text', 'Raveenthiran' );
$nr_logo_sub    = nr_opt( 'nr_logo_sub', 'Photography · Wien' );
$nr_book_label  = nr_opt( 'nr_book_label', __( 'Enquire', 'raveenthiran' ) );
$nr_accent      = nr_opt( 'nr_accent', '#E6C65A' );
$nr_color_bg    = nr_opt( 'nr_color_bg', '#0a0a0a' );
$nr_color_ink   = nr_opt( 'nr_color_ink', '#E7E4DE' );
$nr_current     = $nr_current ?? '';

// hex → "r,g,b" so we can derive ink-2/3/4/line as rgba() with the right hue
if ( ! function_exists( 'nr_hex_to_rgb_string' ) ) {
	function nr_hex_to_rgb_string( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( strlen( $hex ) === 3 ) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) return '228,226,225';
		return hexdec( substr( $hex, 0, 2 ) ) . ',' . hexdec( substr( $hex, 2, 2 ) ) . ',' . hexdec( substr( $hex, 4, 2 ) );
	}
}
$ink_rgb = nr_hex_to_rgb_string( $nr_color_ink );

// The enquire URL (falls back gracefully when the helper is absent).
$nr_enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );

// Primary nav items used by both the top bar and the mobile drawer.
$nr_nav_items = [
	[ __( 'Work',    'raveenthiran' ), get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ), 'portfolio' ],
	[ __( 'Studio',  'raveenthiran' ), function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-about.php', 'about' ) : home_url( '/about' ), 'about' ],
	[ __( 'Journal', 'raveenthiran' ), function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' ), 'journal' ],
	[ __( 'Enquire', 'raveenthiran' ), $nr_enquire, 'enquire' ],
];
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="theme-color" content="<?php echo esc_attr( $nr_color_bg ); ?>">
	<style>
	:root{
		--accent:<?php echo esc_attr( $nr_accent ); ?>;
		--amber:<?php echo esc_attr( $nr_accent ); ?>;
		--accent-soft:rgba(230,200,90,.12);
		--amber-soft:rgba(230,200,90,.12);
		--paper:<?php echo esc_attr( $nr_color_bg ); ?>;
		--bg:<?php echo esc_attr( $nr_color_bg ); ?>;
		--ink:<?php echo esc_attr( $nr_color_ink ); ?>;
		--ink-2:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.66);
		--ink-3:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.46);
		--ink-4:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.26);
		--ink-5:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.08);
		--line:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.16);
		--line-soft:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.09);
		--hair:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.16);
		--hair-soft:rgba(<?php echo esc_attr( $ink_rgb ); ?>,.09);
	}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'nr st nr-page-' . esc_attr( $nr_current ?: 'default' ) ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'raveenthiran' ); ?></a>

<?php /* ── header ─────────────────────────────────────────────── */ ?>
<header class="st-head<?php echo is_front_page() ? ' st-head--over' : ''; ?>" data-head role="banner">
	<div class="st-head__in">
		<a class="st-mark" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo( 'name' ); ?>">
			<span class="st-mark__name"><?php echo esc_html( $nr_logo_text ); ?></span>
			<?php if ( $nr_logo_sub ) : ?><span class="st-mark__sub"><?php echo esc_html( $nr_logo_sub ); ?></span><?php endif; ?>
		</a>

		<nav class="st-nav" aria-label="<?php esc_attr_e( 'Primary', 'raveenthiran' ); ?>">
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
				foreach ( $nr_nav_items as $it ) {
					$cur = $nr_current === $it[2] ? ' aria-current="page"' : '';
					printf( '<a href="%s"%s>%s</a>', esc_url( $it[1] ), $cur, esc_html( $it[0] ) );
				}
			}
			?>
		</nav>

		<div class="st-head__right">
			<?php if ( $nr_avail ) : ?>
				<span class="st-avail"><span class="st-avail__dot" aria-hidden="true"></span><?php echo esc_html( $nr_avail_text ); ?></span>
			<?php endif; ?>
			<a class="st-head__cta" href="<?php echo esc_url( $nr_enquire ); ?>"><?php echo esc_html( $nr_book_label ); ?></a>
			<button type="button" class="nr-hamb" aria-label="<?php esc_attr_e( 'Menu', 'raveenthiran' ); ?>" data-sidebar-open>
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</header>

<?php /* ── mobile drawer (driven by theme.js: .nr-sidebar / data-sidebar-*) ── */ ?>
<div class="nr-sidebar-backdrop" data-sidebar-close></div>
<aside class="nr-sidebar" aria-label="<?php esc_attr_e( 'Mobile navigation', 'raveenthiran' ); ?>">
	<button type="button" class="nr-sidebar__close" data-sidebar-close aria-label="<?php esc_attr_e( 'Close menu', 'raveenthiran' ); ?>">✕</button>
	<nav class="nr-sidebar__nav" aria-label="<?php esc_attr_e( 'Primary mobile', 'raveenthiran' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'raveenthiran' ); ?></a>
		<?php foreach ( $nr_nav_items as $it ) printf( '<a href="%s">%s</a>', esc_url( $it[1] ), esc_html( $it[0] ) ); ?>
	</nav>
	<div class="nr-sidebar__group">
		<h4><?php esc_html_e( 'Elsewhere', 'raveenthiran' ); ?></h4>
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

<main id="main" class="nr-main st-main">
