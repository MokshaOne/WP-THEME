<?php
/**
 * Template Name: About
 * The studio page — serif statement, portrait plate, stats row,
 * clients wall, testimonials (nr_testimonial), bio, socials.
 * Reads the same nr_* options and testimonial entries Obscura uses.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
	$lede    = nr_opt( 'nr_about_lede', '' );
	$bio     = nr_opt( 'nr_about_bio', '' );
	$studio  = nr_opt( 'nr_studio', '' );
	$stats   = array_filter( [
		__( 'Projects', 'raveenthiran-silence' )     => nr_opt( 'nr_stats_proj', '' ),
		__( 'Countries', 'raveenthiran-silence' )    => nr_opt( 'nr_stats_cnty', '' ),
		__( 'Publications', 'raveenthiran-silence' ) => nr_opt( 'nr_stats_pubs', '' ),
		__( 'Awards', 'raveenthiran-silence' )       => nr_opt( 'nr_stats_awd', '' ),
	] );
	$clients = array_filter( array_map( 'trim', explode( "\n", (string) nr_opt( 'nr_clients_list', '' ) ) ) );
	$socials = array_filter( [
		'Instagram' => nr_opt( 'nr_instagram' ),
		'Behance'   => nr_opt( 'nr_behance' ),
		'Vimeo'     => nr_opt( 'nr_vimeo' ),
		'LinkedIn'  => nr_opt( 'nr_linkedin' ),
	] );
	$quotes  = get_posts( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 3, 'orderby' => 'menu_order date', 'order' => 'ASC' ] );
?>

<article class="nr-sheet nr-about">
	<div class="nr-sheet__inner nr-sheet__inner--wide">

		<header class="nr-about__head">
			<span class="nr-eyebrow"><?php esc_html_e( 'The studio', 'raveenthiran-silence' ); ?></span>
			<h1><?php the_title(); ?></h1>
		</header>

		<div class="nr-about__grid">
			<div class="nr-about__text">
				<?php if ( $lede ) : ?><p class="nr-lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
				<?php if ( $bio ) : ?><div class="nr-prose"><p><?php echo nl2br( esc_html( $bio ) ); ?></p></div><?php endif; ?>
				<?php if ( get_the_content() ) : ?><div class="nr-prose"><?php the_content(); ?></div><?php endif; ?>
				<?php if ( $studio ) : ?><p class="nr-about__addr"><?php echo esc_html( $studio ); ?></p><?php endif; ?>
			</div>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="nr-about__portrait">
					<?php the_post_thumbnail( 'nr-index', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
					<figcaption class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_logo_text', 'raveenthiran' ) . ' · ' . nr_opt( 'nr_location', 'Wien' ) ); ?></figcaption>
				</figure>
			<?php endif; ?>
		</div>

		<?php if ( $stats ) : ?>
			<dl class="nr-about__stats">
				<?php foreach ( $stats as $label => $value ) : ?>
					<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>

		<?php if ( $quotes ) : ?>
			<section class="nr-about__quotes">
				<h2 class="nr-eyebrow"><?php esc_html_e( 'Said about the work', 'raveenthiran-silence' ); ?></h2>
				<?php foreach ( $quotes as $q ) : ?>
					<blockquote class="nr-quotecard">
						<p><?php echo esc_html( wp_strip_all_tags( $q->post_content ) ); ?></p>
						<cite>— <?php echo esc_html( get_the_title( $q ) ); ?></cite>
					</blockquote>
				<?php endforeach; ?>
			</section>
		<?php endif; ?>

		<?php if ( $clients ) : ?>
			<section class="nr-about__clients">
				<h2 class="nr-eyebrow"><?php esc_html_e( 'Selected clients', 'raveenthiran-silence' ); ?></h2>
				<ul>
					<?php foreach ( $clients as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<footer class="nr-about__foot">
			<?php if ( $socials ) : ?>
				<p class="nr-links">
					<?php foreach ( $socials as $label => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?> ↗</a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
			<a class="nr-about__cta" href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>">
				<?php esc_html_e( 'Let’s make something', 'raveenthiran-silence' ); ?> <em>— <?php esc_html_e( 'enquire', 'raveenthiran-silence' ); ?></em> →
			</a>
		</footer>
	</div>
</article>

<?php endwhile; get_footer(); ?>
