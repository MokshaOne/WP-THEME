<?php
/**
 * Bottom tab bar — visible only on small viewports (CSS-controlled).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$icons = [
	'home'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 11l9-8 9 8M5 9.5V21h14V9.5"/></svg>',
	'portfolio' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
	'booking'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>',
	'contact'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>',
];

$tabs = [
	[ 'home',      nr_opt( 'nr_tab_1_label', __( 'Home',  'raveenthiran' ) ), home_url( '/' ),                                                              $icons['home']      ],
	[ 'portfolio', nr_opt( 'nr_tab_2_label', __( 'Work',  'raveenthiran' ) ), get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ),       $icons['portfolio'] ],
	[ 'booking',   nr_opt( 'nr_tab_3_label', __( 'Book',  'raveenthiran' ) ), function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-booking.php', 'booking' ) : home_url( '/booking' ), $icons['booking']   ],
	[ 'contact',   nr_opt( 'nr_tab_4_label', __( 'Hello', 'raveenthiran' ) ), function_exists( 'nr_template_page_url' ) ? nr_template_page_url( 'page-contact.php', 'contact' ) : home_url( '/contact' ), $icons['contact']   ],
];

$current = $nr_current ?? '';
?>
<nav class="nr-tabbar" aria-label="<?php esc_attr_e( 'Mobile', 'raveenthiran' ); ?>">
	<?php foreach ( $tabs as $t ) :
		$on = $current === $t[0]; ?>
		<a href="<?php echo esc_url( $t[2] ); ?>" class="nr-tab<?php echo $on ? ' is-on' : ''; ?>"<?php echo $on ? ' aria-current="page"' : ''; ?>>
			<span class="nr-tab-glyph"><?php echo $t[3]; /* trusted inline SVG */ ?></span>
			<span class="nr-tab-label"><?php echo esc_html( $t[1] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
