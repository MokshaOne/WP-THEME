<?php
/**
 * M1O Transmission · front page
 * Hero → marquee → channel index (ledger) → social row.
 * CTA band + footer live in footer.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$id        = m1o_get_identity();
$index     = m1o_index_channels();
$social    = m1o_social_channels();
$show_leds = m1o_opt( 'm1o_show_leds', '1' ) !== '0';
$show_strip = m1o_opt( 'm1o_show_strip', '1' ) !== '0';
$embed     = m1o_music_embed_src( $id['music_url'] );

// Marquee items = index channel first words + Music (when embed set).
$marquee = array_map( fn( $ch ) => strtok( (string) ( $ch['host'] ?? $ch['title'] ), ' ·' ), $index );
if ( $embed ) $marquee[] = __( 'Music', 'm1o' );
if ( empty( $marquee ) ) $marquee = [ 'Photography', 'Community', 'Music' ];
$marquee_half = '';
for ( $r = 0; $r < 4; $r++ ) {
	foreach ( $marquee as $w ) {
		$marquee_half .= '<span>' . esc_html( $w ) . '</span><i>&#10022;</i>';
	}
}
$status_classes = [ 'active' => '', 'idle' => 'idle', 'live' => '', 'current' => '' ];
$status_lbls    = m1o_status_labels();
?>

<main id="main">

	<!-- HERO -->
	<section class="hero">
		<?php if ( m1o_motion_on() ) : ?><canvas id="m1o-field" aria-hidden="true"></canvas><?php endif; ?>
		<div class="rail"><?php echo esc_html( 'est. ' . $id['est'] . ' · 48.2082° N · 16.3738° E · ' . strtok( $id['location'], ',' ) ); ?></div>
		<div class="in">
			<div class="eyebrow rv" id="m1o-eb" data-scramble>// <?php echo esc_html( sprintf( __( 'One signal · %d channels', 'm1o' ), count( $index ) + ( $embed ? 1 : 0 ) ) ); ?></div>
			<h1 class="wordmark" id="m1o-wm">
				<span class="line"><span class="l1">MOKSHA</span></span>
				<span class="line"><span class="l2" id="m1o-l2">1ONE<span class="period">.</span></span></span>
			</h1>
			<div class="foot rv">
				<p class="statement"><?php echo esc_html( $id['tagline'] ); ?></p>
				<a class="chip mag" href="mailto:<?php echo esc_attr( $id['email'] ); ?>">
					<span class="k">&#9654; <?php echo esc_html( $id['cta'] ); ?></span>
					<span class="v"><?php echo esc_html( $id['email'] ); ?></span>
				</a>
			</div>
		</div>
		<div class="marquee" aria-hidden="true">
			<div class="track">
				<div class="half"><?php echo $marquee_half; // escaped above ?></div>
				<div class="half"><?php echo $marquee_half; // escaped above ?></div>
			</div>
		</div>
	</section>

	<!-- CHANNEL INDEX -->
	<section class="index">
		<div class="head rv xh">
			<span data-scramble>// <?php esc_html_e( 'Channels', 'm1o' ); ?></span>
			<span><?php echo esc_html( sprintf( '%02d', count( $index ) + ( $embed ? 1 : 0 ) ) ); ?></span>
		</div>

		<?php foreach ( $index as $i => $ch ) :
			$num    = sprintf( '%02d', $i + 1 );
			$status = $ch['status'] ?? 'active';
			$host   = wp_parse_url( $ch['url'] ?? '', PHP_URL_HOST );
		?>
		<a class="row rv sk" href="<?php echo esc_url( $ch['url'] ?? '#' ); ?>" target="_blank" rel="noopener">
			<div class="main">
				<span class="num"><?php echo esc_html( $num ); ?></span>
				<div>
					<div class="name"><?php echo esc_html( $ch['title'] ?? '' ); ?></div>
					<?php if ( ! empty( $ch['host'] ) ) : ?>
						<div class="sub" data-scramble><?php echo esc_html( $ch['host'] ); ?></div>
					<?php endif; ?>
				</div>
				<div class="side">
					<?php if ( $show_leds ) : ?>
						<span class="status <?php echo esc_attr( $status_classes[ $status ] ?? '' ); ?>"><span class="led"></span><?php echo esc_html( trim( $status_lbls[ $status ] ?? $status, '●○▶ ' ) ); ?></span>
					<?php endif; ?>
					<?php if ( $host ) : ?><span class="domain"><?php echo esc_html( $host ); ?></span><?php endif; ?>
				</div>
				<span class="arrow">&#8599;</span>
			</div>
			<?php if ( $show_strip && $i === 0 && has_post_thumbnail( get_option( 'page_on_front' ) ) ) : ?>
			<div class="attach">
				<div class="strip strip--img">
					<?php echo get_the_post_thumbnail( get_option( 'page_on_front' ), 'large', [ 'loading' => 'lazy' ] ); ?>
				</div>
			</div>
			<?php elseif ( $show_strip && $i === 0 ) : ?>
			<div class="attach">
				<div class="strip"><div class="a"></div><div class="b"></div><div class="c"></div></div>
			</div>
			<?php endif; ?>
		</a>
		<?php endforeach; ?>

		<?php if ( $embed ) : ?>
		<div class="row rv sk">
			<div class="main">
				<span class="num"><?php echo esc_html( sprintf( '%02d', count( $index ) + 1 ) ); ?></span>
				<div>
					<div class="name"><?php esc_html_e( 'Music', 'm1o' ); ?></div>
					<?php if ( $id['music_title'] ) : ?>
						<div class="sub" data-scramble><?php echo esc_html( $id['music_title'] ); ?></div>
					<?php endif; ?>
				</div>
				<div class="side">
					<?php if ( $show_leds ) : ?>
						<span class="status"><span class="led"></span><?php esc_html_e( 'Live', 'm1o' ); ?></span>
					<?php endif; ?>
				</div>
				<span class="arrow">&#8594;</span>
			</div>
			<div class="attach">
				<div class="embed embed--<?php echo esc_attr( $embed[1] ); ?>">
					<iframe
						src="<?php echo esc_url( $embed[0] ); ?>"
						title="<?php echo esc_attr( $id['music_title'] ?: __( 'Music player', 'm1o' ) ); ?>"
						loading="lazy"
						allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
						allowfullscreen
						referrerpolicy="strict-origin-when-cross-origin"></iframe>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $social ) ) : ?>
		<div class="social rv">
			<span class="lbl" data-scramble>// <?php esc_html_e( 'Social', 'm1o' ); ?></span>
			<div class="links">
				<?php foreach ( $social as $s ) : ?>
					<a href="<?php echo esc_url( $s['url'] ?? '#' ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $s['host'] ?: $s['title'] ); ?> &#8599;</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</section>

</main>

<?php get_footer(); ?>
