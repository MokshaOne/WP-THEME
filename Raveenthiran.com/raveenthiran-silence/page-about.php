<?php
/**
 * Template Name: About — Silence III
 * The studio page as an editorial spread: an italic pull-quote that
 * scroll-scrubs into focus, a sticky portrait beside the bio, count-up
 * stats, testimonials, awards, press, and the clients wall.
 *
 * Reads the same data both themes share: ACF option fields when present
 * (about_statement, stat_projects/countries/since, awards_list,
 * press_list — via get_field(...,'option')), falling back to the nr_*
 * settings so it works with or without ACF Pro.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

/** ACF option field with a settings fallback. */
function nr_about_val( $acf_key, $opt_key, $default = '' ) {
	$v = function_exists( 'get_field' ) ? get_field( $acf_key, 'option' ) : null;
	if ( is_scalar( $v ) && (string) $v !== '' ) return (string) $v;
	return nr_opt( $opt_key, $default );
}

while ( have_posts() ) : the_post();
	$statement = nr_about_val( 'about_statement', 'nr_about_lede', '' );
	$bio       = nr_opt( 'nr_about_bio', '' );
	$studio    = nr_opt( 'nr_studio', '' );

	$stats = array_filter( [
		__( 'Projects', 'raveenthiran-silence' )     => nr_about_val( 'stat_projects',  'nr_stats_proj', '' ),
		__( 'Countries', 'raveenthiran-silence' )    => nr_about_val( 'stat_countries', 'nr_stats_cnty', '' ),
		__( 'Publications', 'raveenthiran-silence' ) => nr_opt( 'nr_stats_pubs', '' ),
		__( 'Since', 'raveenthiran-silence' )        => nr_about_val( 'stat_since',     'nr_stats_awd', '' ),
	] );

	$clients = array_filter( array_map( 'trim', explode( "\n", (string) nr_opt( 'nr_clients_list', '' ) ) ) );
	$socials = array_filter( [
		'Instagram' => nr_opt( 'nr_instagram' ),
		'Behance'   => nr_opt( 'nr_behance' ),
		'Vimeo'     => nr_opt( 'nr_vimeo' ),
		'LinkedIn'  => nr_opt( 'nr_linkedin' ),
	] );
	$quotes  = get_posts( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 3, 'orderby' => 'menu_order date', 'order' => 'ASC' ] );

	// ACF repeaters: awards_list (award_title/award_url), press_list
	$awards = function_exists( 'get_field' ) ? get_field( 'awards_list', 'option' ) : null;
	$press  = function_exists( 'get_field' ) ? get_field( 'press_list', 'option' ) : null;
?>

<article class="nr-about">

	<?php /* 1 · opening statement — italic pull-quote, scroll-scrubbed */ ?>
	<header class="nr-about__open">
		<span class="nr-eyebrow"><?php esc_html_e( 'The studio', 'raveenthiran-silence' ); ?> — <?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?></span>
		<div class="nr-statement nr-about__statement">
			<p><?php echo $statement ? esc_html( $statement ) : esc_html__( 'I photograph people and places quietly — warm shadows, restrained contrast, a sense of stillness. The aim is that the pictures still look like photographs.', 'raveenthiran-silence' ); ?></p>
		</div>
	</header>

	<?php /* 2 · bio beside a sticky portrait */ ?>
	<section class="nr-about__spread">
		<div class="nr-about__bio nr-rise">
			<h1 class="nr-about__name"><?php the_title(); ?></h1>
			<?php if ( $bio ) : ?><div class="nr-prose"><p><?php echo nl2br( esc_html( $bio ) ); ?></p></div><?php endif; ?>
			<?php if ( get_the_content() ) : ?><div class="nr-prose"><?php the_content(); ?></div><?php endif; ?>
			<?php if ( $studio ) : ?><p class="nr-about__addr"><?php echo esc_html( $studio ); ?></p><?php endif; ?>
			<?php if ( $socials ) : ?>
				<p class="nr-links">
					<?php foreach ( $socials as $label => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?> ↗</a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="nr-about__portrait">
				<div class="nr-about__portrait-inner">
					<?php the_post_thumbnail( 'nr-plate', [ 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
				</div>
				<figcaption class="nr-eyebrow"><?php echo esc_html( nr_opt( 'nr_logo_text', 'raveenthiran' ) . ' · ' . nr_opt( 'nr_location', 'Wien' ) ); ?></figcaption>
			</figure>
		<?php endif; ?>
	</section>

	<?php /* 3 · stats — count up on entry */ ?>
	<?php if ( $stats ) : ?>
		<section class="nr-about__stats nr-rise" data-countup>
			<?php foreach ( $stats as $label => $value ) : ?>
				<div>
					<dd><span data-count-to="<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $value ) ); ?>" data-count-raw="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></span></dd>
					<dt><?php echo esc_html( $label ); ?></dt>
				</div>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php /* 4 · testimonials */ ?>
	<?php if ( $quotes ) : ?>
		<section class="nr-about__quotes">
			<h2 class="nr-eyebrow nr-rise"><?php esc_html_e( 'Said about the work', 'raveenthiran-silence' ); ?></h2>
			<?php foreach ( $quotes as $q ) :
				$role = function_exists( 'get_field' ) ? get_field( 'client_role', $q->ID ) : '';
			?>
				<blockquote class="nr-quotecard nr-rise">
					<p><?php echo esc_html( wp_strip_all_tags( $q->post_content ) ); ?></p>
					<cite>— <?php echo esc_html( get_the_title( $q ) ); ?><?php if ( $role ) : ?>, <?php echo esc_html( $role ); ?><?php endif; ?></cite>
				</blockquote>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php /* 5 · awards + press (ACF repeaters, shown when present) */ ?>
	<?php if ( ( is_array( $awards ) && $awards ) || ( is_array( $press ) && $press ) ) : ?>
		<section class="nr-about__recognition">
			<?php if ( is_array( $awards ) && $awards ) : ?>
				<div class="nr-about__col nr-rise">
					<h2 class="nr-eyebrow"><?php esc_html_e( 'Awards', 'raveenthiran-silence' ); ?></h2>
					<ul class="nr-about__reclist">
						<?php foreach ( $awards as $a ) :
							$t = is_array( $a ) ? ( $a['award_title'] ?? '' ) : '';
							$u = is_array( $a ) ? ( $a['award_url'] ?? '' ) : '';
							if ( ! $t ) continue;
						?>
							<li><?php echo $u ? '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener">' . esc_html( $t ) . ' ↗</a>' : esc_html( $t ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if ( is_array( $press ) && $press ) : ?>
				<div class="nr-about__col nr-rise">
					<h2 class="nr-eyebrow"><?php esc_html_e( 'Press', 'raveenthiran-silence' ); ?></h2>
					<ul class="nr-about__reclist">
						<?php foreach ( $press as $pz ) :
							$t = is_array( $pz ) ? ( $pz['press_title'] ?? reset( $pz ) ) : $pz;
							if ( ! $t ) continue;
						?>
							<li><?php echo esc_html( $t ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php /* 6 · clients wall */ ?>
	<?php if ( $clients ) : ?>
		<section class="nr-about__clients nr-rise">
			<h2 class="nr-eyebrow"><?php esc_html_e( 'Selected clients', 'raveenthiran-silence' ); ?></h2>
			<ul>
				<?php foreach ( $clients as $c ) : ?><li><?php echo esc_html( $c ); ?></li><?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php /* 7 · CTA */ ?>
	<section class="nr-cta">
		<a href="<?php echo esc_url( function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ) ); ?>">
			<span class="nr-cta__t"><?php esc_html_e( 'Let’s make something.', 'raveenthiran-silence' ); ?></span>
			<span class="nr-cta__sub"><?php esc_html_e( 'Enquire', 'raveenthiran-silence' ); ?> →</span>
		</a>
	</section>
</article>

<?php endwhile; get_footer(); ?>
