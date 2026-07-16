<?php
/**
 * VOID single specimen — a technical dossier. Three-column analysis header
 * (meta · clipped frame with coordinates/watermark · HUD readouts), then the
 * full gallery as a stack of framed plates. Data via the shared nr_* engine.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'portfolio';
get_header();
the_post();

$m       = function_exists( 'nr_project_meta' ) ? nr_project_meta() : [];
$gallery = function_exists( 'nr_field' ) ? nr_field( 'project_gallery' ) : [];
if ( ! is_array( $gallery ) ) $gallery = [];

/* collect plate image IDs */
$plates = [];
foreach ( $gallery as $g ) {
	$id = is_array( $g ) ? (int) ( $g['ID'] ?? $g['id'] ?? 0 ) : (int) $g;
	if ( $id ) $plates[] = $id;
}
$hero_id = has_post_thumbnail() ? (int) get_post_thumbnail_id() : ( $plates[0] ?? 0 );
$num  = ! empty( $m['n'] ) ? $m['n'] : str_pad( (string) get_the_ID(), 3, '0', STR_PAD_LEFT );
$lede = get_the_excerpt();

$vf = function_exists( 'nr_field' ) ? function ( $k, $d = '' ) { $v = nr_field( $k ); return ( $v === null || $v === '' || $v === false ) ? $d : $v; } : function ( $k, $d = '' ) { return $d; };

$classification = $vf( 'void_classification', $m['cat'] ?? 'DATA_STREAM' );
$coords         = $vf( 'void_coords', $m['loc'] ?? nr_opt( 'nr_void_lat', '48.2082° N' ) );
$certified      = $vf( 'void_certified', '1' ) !== '0';
$bars = [
	[ 'FIDELITY', (int) $vf( 'void_fidelity', 96 ) ],
	[ 'CLARITY',  (int) $vf( 'void_clarity', 88 ) ],
	[ 'DEPTH',    (int) $vf( 'void_depth', 74 ) ],
];

$rows = array_filter( [
	'CLIENT'     => $m['client'] ?? '',
	'YEAR'       => (string) ( $m['yr'] ?? '' ),
	'DISCIPLINE' => $m['cat'] ?? '',
	'LOCATION'   => $m['loc'] ?? '',
	'FRAMES'     => $vf( 'project_frames' ),
	'MATERIAL'   => $vf( 'void_material' ),
] );

$next = get_adjacent_post( false, '', false );
$prev = get_adjacent_post( false, '', true );
?>

<section class="void-screen void-single-screen">
	<div class="void-scanline-beam" aria-hidden="true"></div>

	<div class="void-single">

		<!-- LEFT · identity + meta + nav -->
		<div class="void-single-left">
			<div>
				<nav class="void-mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'raveenthiran' ); ?>">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ); ?>">ARCHIVE</a> / SPECIMEN_<?php echo esc_html( $num ); ?>
				</nav>
				<h1 class="void-h1-huge void-h1-uncap" style="margin-top:16px"><?php the_title(); ?></h1>

				<div class="void-meta-rows">
					<?php foreach ( $rows as $k => $v ) : ?>
						<div class="void-meta-row"><span><?php echo esc_html( $k ); ?></span><span class="void-gold-70"><?php echo esc_html( $v ); ?></span></div>
					<?php endforeach; ?>
				</div>

				<?php if ( $lede ) : ?>
				<div class="void-note-box">
					<span class="void-label-luxury"><?php esc_html_e( 'ANALYSIS', 'raveenthiran' ); ?></span>
					<p><?php echo esc_html( $lede ); ?></p>
				</div>
				<?php endif; ?>

				<a class="void-btn void-btn-gold void-btn-wide" style="margin-top:20px" href="<?php echo esc_url( add_query_arg( [ 'service' => sanitize_title( $m['cat'] ?? 'editorial' ), 'ref' => get_the_title() ], function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ) ); ?>"><?php echo esc_html( nr_opt( 'nr_cta_commission', __( 'COMMISSION SIMILAR', 'raveenthiran' ) ) ); ?></a>
			</div>

			<div class="void-single-nav">
				<?php if ( $prev ) : ?>
				<a class="void-single-nav-item" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
					<span class="void-nav-line"></span>
					<span><p class="void-nav-item-label"><?php esc_html_e( 'PREV', 'raveenthiran' ); ?></p><p class="void-nav-item-title"><?php echo esc_html( get_the_title( $prev ) ); ?></p></span>
				</a>
				<?php endif; ?>
				<?php if ( $next ) : ?>
				<a class="void-single-nav-item" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
					<span class="void-nav-line"></span>
					<span><p class="void-nav-item-label"><?php esc_html_e( 'NEXT', 'raveenthiran' ); ?></p><p class="void-nav-item-title"><?php echo esc_html( get_the_title( $next ) ); ?></p></span>
				</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- CENTER · primary frame -->
		<div class="void-single-center">
			<span class="void-floating-tag void-floating-tag-tr"><?php echo esc_html( strtoupper( $classification ) ); ?></span>
			<div class="void-frame">
				<?php
				if ( $hero_id && function_exists( 'nr_image_or_placeholder' ) ) nr_image_or_placeholder( get_the_ID(), 'nr-hero', get_the_title() );
				elseif ( $hero_id ) echo wp_get_attachment_image( $hero_id, 'nr-hero', false, [ 'alt' => get_the_title(), 'class' => 'void-hero-img' ] );
				?>
				<div class="void-frame-coords">
					<span class="void-mono"><?php esc_html_e( 'VECTOR', 'raveenthiran' ); ?></span>
					<p><?php echo esc_html( $coords ); ?></p>
				</div>
				<?php if ( $certified ) : ?>
				<div class="void-frame-watermark">0x<?php echo esc_html( $num ); ?><span><?php esc_html_e( 'VOID CERTIFIED', 'raveenthiran' ); ?></span></div>
				<?php endif; ?>
			</div>
		</div>

		<!-- RIGHT · HUD readouts -->
		<div class="void-single-right">
			<div class="void-hud-head"><h3><?php esc_html_e( 'TECHNICAL', 'raveenthiran' ); ?></h3><span>REV_3.1</span></div>
			<?php foreach ( $bars as $b ) : ?>
				<div class="void-hud-bar">
					<div class="void-hud-bar-top"><?php echo esc_html( $b[0] ); ?></div>
					<div class="void-bar-track"><span class="void-bar-fill" style="width:<?php echo (int) $b[1]; ?>%"></span></div>
					<div class="void-hud-bar-bottom"><span>0</span><span><?php echo (int) $b[1]; ?>%</span></div>
				</div>
			<?php endforeach; ?>
			<div class="void-geo-array">
				<span class="void-geo-label"><?php esc_html_e( 'GEO_ARRAY', 'raveenthiran' ); ?></span>
				<div class="void-geo-diamond"><div></div></div>
			</div>
			<ul class="void-checklist">
				<li><span class="void-dot-gold"></span><?php esc_html_e( 'COLOR PROFILE LOCKED', 'raveenthiran' ); ?></li>
				<li><span class="void-dot-gold"></span><?php esc_html_e( 'METADATA VERIFIED', 'raveenthiran' ); ?></li>
				<li><span class="void-dot-outline"></span><?php esc_html_e( 'ARCHIVE SYNCED', 'raveenthiran' ); ?></li>
			</ul>
		</div>

	</div>

	<?php /* full gallery — remaining plates as framed specimens */ ?>
	<?php if ( count( $plates ) > 1 ) : ?>
	<div class="void-plate-stack">
		<?php foreach ( array_slice( $plates, 1 ) as $k => $pid ) : ?>
			<figure class="void-frame void-frame--plate">
				<?php echo wp_get_attachment_image( $pid, 'nr-hero', false, [ 'alt' => get_the_title() . ' — plate ' . ( $k + 2 ), 'loading' => 'lazy', 'decoding' => 'async' ] ); ?>
				<div class="void-frame-watermark"><?php echo esc_html( str_pad( (string) ( $k + 2 ), 2, '0', STR_PAD_LEFT ) ); ?><span><?php esc_html_e( 'PLATE', 'raveenthiran' ); ?></span></div>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

</section>

<?php get_footer(); ?>
