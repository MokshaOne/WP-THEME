<?php
/**
 * Template Name: About
 * VOID studio — Philosophy. Two-column: doctrine copy + verified portrait.
 * Copy comes from the shared Theme Settings (nr_about_*).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'about';
get_header();
the_post();

$title   = nr_opt( 'nr_about_title', __( 'A practice of looking, slowly.', 'raveenthiran' ) );
$lede    = nr_opt( 'nr_about_lede', '' );
$bio     = nr_opt( 'nr_about_bio', '' );
$name    = nr_opt( 'nr_about_name', get_bloginfo( 'name' ) );
$role    = nr_opt( 'nr_about_role', 'Photographer · Vienna' );
$eyebrow = nr_opt( 'nr_about_eyebrow', 'DOCTRINE // §03' );
$portrait_id = (int) nr_opt( 'nr_about_portrait_id', 0 );
if ( ! $portrait_id && has_post_thumbnail() ) $portrait_id = (int) get_post_thumbnail_id();

$stats = array_filter( [
	'PROJECTS' => nr_opt( 'nr_stats_proj', '' ),
	'COUNTRIES' => nr_opt( 'nr_stats_cnty', '' ),
	'PUBLICATIONS' => nr_opt( 'nr_stats_pubs', '' ),
] );
?>

<section class="void-screen void-about-screen">
	<div class="void-scanline-beam" aria-hidden="true"></div>

	<div class="void-about">
		<div class="void-about-copy">
			<p class="void-eyebrow"><span class="void-rule"></span><?php echo esc_html( $eyebrow ); ?></p>
			<h1 class="void-h1-huge void-h1-uncap"><?php echo wp_kses( $title, [ 'em' => [], 'span' => [ 'class' => [] ] ] ); ?></h1>
			<?php if ( $lede ) : ?><p><?php echo esc_html( $lede ); ?></p><?php endif; ?>
			<?php if ( $bio ) : ?><p class="void-about-copy-2"><?php echo nl2br( esc_html( $bio ) ); ?></p><?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="void-about-page-content void-prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $stats ) : ?>
			<div class="void-about-stats">
				<?php foreach ( $stats as $k => $v ) : ?>
					<div class="void-stat-box">
						<div class="void-stat-box-top"><span><?php echo esc_html( $k ); ?></span><span class="void-dot-gold"></span></div>
						<div class="void-stat-box-value"><?php echo esc_html( $v ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="void-about-right">
			<figure class="void-about-portrait">
				<?php
				if ( $portrait_id && function_exists( 'nr_image_or_placeholder' ) ) nr_image_or_placeholder( get_the_ID(), 'nr-card', $name );
				elseif ( $portrait_id ) echo wp_get_attachment_image( $portrait_id, 'nr-card', false, [ 'alt' => $name ] );
				else echo function_exists( 'nr_placeholder' ) ? nr_placeholder( strtolower( $name ), false, '3/4' ) : '';
				?>
				<span class="void-badge-verified"><?php esc_html_e( 'VERIFIED', 'raveenthiran' ); ?></span>
			</figure>
			<div class="void-frame-coords" style="position:static;margin-top:14px;display:inline-block">
				<span class="void-mono"><?php echo esc_html( $role ); ?></span>
				<p><?php echo esc_html( $name ); ?></p>
			</div>
		</div>
	</div>

	<?php /* ── Recognition — Awards & Press (nr_recognition_list) ─────── */ ?>
	<?php
	$mk_awards = function_exists( 'nr_recognition_list' ) ? nr_recognition_list( 'nr_awards_list' ) : [];
	$mk_press  = function_exists( 'nr_recognition_list' ) ? nr_recognition_list( 'nr_press_list' )  : [];
	// ACF options-page repeaters, if present, take precedence / augment
	if ( function_exists( 'get_field' ) ) {
		foreach ( (array) get_field( 'awards_list', 'option' ) as $r ) {
			$t = trim( (string) ( $r['award_title'] ?? '' ) ); if ( $t === '' ) continue;
			$mk_awards[] = [ 'year' => $r['year'] ?? '', 'title' => $t, 'org' => $r['organisation'] ?? '', 'url' => $r['award_url'] ?? '' ];
		}
		foreach ( (array) get_field( 'press_list', 'option' ) as $r ) {
			$t = trim( (string) ( $r['pr_title'] ?? '' ) ); $o = trim( (string) ( $r['medium'] ?? '' ) ); if ( $t === '' && $o === '' ) continue;
			$mk_press[] = [ 'year' => $r['year'] ?? '', 'title' => $t, 'org' => $o, 'url' => $r['pr_url'] ?? '' ];
		}
	}
	$mk_reco = array_filter( [
		[ 'label' => __( 'AWARDS', 'raveenthiran' ), 'items' => $mk_awards ],
		[ 'label' => __( 'PRESS',  'raveenthiran' ), 'items' => $mk_press ],
	], fn( $g ) => ! empty( $g['items'] ) );
	if ( $mk_reco ) : ?>
	<div class="mk-reco">
		<?php foreach ( $mk_reco as $g ) : ?>
			<div class="mk-reco__col">
				<span class="void-label-luxury"><?php echo esc_html( $g['label'] ); ?></span>
				<ul class="mk-reco__list">
					<?php foreach ( $g['items'] as $row ) :
						$title = trim( $row['title'] ?: $row['org'] );
						$sub   = ( $row['title'] && $row['org'] ) ? $row['org'] : '';
						$open  = ! empty( $row['url'] ); ?>
						<li class="mk-reco__item">
							<span class="mk-reco__year"><?php echo esc_html( $row['year'] ); ?></span>
							<span class="mk-reco__body">
								<?php if ( $open ) : ?><a href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $title ); ?> <span aria-hidden="true">↗</span></a>
								<?php else : ?><span class="mk-reco__title"><?php echo esc_html( $title ); ?></span><?php endif; ?>
								<?php if ( $sub ) : ?><span class="mk-reco__org"><?php echo esc_html( $sub ); ?></span><?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
