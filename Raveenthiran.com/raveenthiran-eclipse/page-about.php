<?php
/**
 * Template Name: About
 * Studio — scrolling editorial: portrait + bio, stats, clients,
 * recognition, testimonials. Data resolution unchanged from before.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'about';
add_action( 'wp_head', function () {
	$id = get_post_thumbnail_id( get_queried_object_id() );
	if ( ! $id ) return;
	$src = wp_get_attachment_image_url( $id, 'nr-card' );
	if ( ! $src ) return;
	$ss = wp_get_attachment_image_srcset( $id, 'nr-card' );
	printf( '<link rel="preload" as="image" href="%s"%s fetchpriority="high">' . "\n", esc_url( $src ), $ss ? ' imagesrcset="' . esc_attr( $ss ) . '"' : '' );
}, 2 );
get_header();
the_post();

$has_excerpt = trim( get_the_excerpt() ) !== '' && has_excerpt();
$portrait_id = get_post_thumbnail_id( get_the_ID() );

$nr_about_eb    = trim( (string) get_option( 'nr_about_eyebrow', '' ) );
$nr_about_title = trim( (string) get_option( 'nr_about_title', '' ) );
$rendered_content = trim( wp_strip_all_tags( apply_filters( 'the_content', get_the_content() ) ) );
$legacy_bio       = function_exists( 'nr_about_field' ) ? trim( (string) nr_about_field( 'about_bio', '' ) ) : '';
$settings_lede    = trim( (string) nr_opt( 'nr_about_lede', '' ) );
$settings_bio     = trim( (string) nr_opt( 'nr_about_bio', '' ) );
$settings_name    = trim( (string) get_option( 'nr_about_name', '' ) );
$settings_role    = trim( (string) get_option( 'nr_about_role', '' ) );
$lede = $has_excerpt ? get_the_excerpt() : $settings_lede;

$stats = [
	[ nr_opt( 'nr_stats_proj', '142' ), __( 'Projects', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_cnty', '18' ),  __( 'Countries', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_pubs', '36' ),  __( 'Publications', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_awd', '11' ),   __( 'Awards', 'raveenthiran' ) ],
];
?>
<section class="st-page nr-about">
	<div class="st-wrap st-about__top">
		<div class="st-about__intro" data-reveal>
			<?php if ( $nr_about_eb !== '' ) : ?><span class="st-eyebrow"><?php echo esc_html( $nr_about_eb ); ?></span><?php else : ?><span class="st-eyebrow"><?php esc_html_e( 'Studio', 'raveenthiran' ); ?></span><?php endif; ?>
			<?php if ( $nr_about_title !== '' ) : ?>
				<h1 class="st-about__title"><?php echo wp_kses( $nr_about_title, [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
			<?php else : ?>
				<h1 class="st-about__title"><?php echo esc_html( nr_opt( 'nr_logo_text', 'Raveenthiran' ) ); ?> — <em><?php esc_html_e( 'photographer', 'raveenthiran' ); ?></em></h1>
			<?php endif; ?>
			<?php if ( $lede ) : ?><p class="st-about__lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
			<?php if ( $settings_name !== '' || $settings_role !== '' ) : ?>
				<div class="st-about__byline">
					<?php if ( $settings_name !== '' ) : ?><span class="st-about__byline-name"><?php echo esc_html( $settings_name ); ?></span><?php endif; ?>
					<?php if ( $settings_role !== '' ) : ?><span class="st-about__byline-role"><?php echo esc_html( $settings_role ); ?></span><?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<figure class="st-about__portrait" data-reveal>
			<?php if ( $portrait_id ) :
				echo wp_get_attachment_image( $portrait_id, 'nr-card', false, [ 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'class' => 'st-about__portrait-img' ] );
			else :
				echo nr_placeholder( 'self portrait', false, '4/5' );
			endif; ?>
			<figcaption><?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?> · <?php echo esc_html( date_i18n( 'Y' ) ); ?></figcaption>
		</figure>
	</div>

	<?php /* bio */ ?>
	<div class="st-wrap st-about__bio" data-reveal>
		<?php if ( $settings_bio !== '' ) : ?>
			<?php echo wp_kses_post( wpautop( $settings_bio ) ); ?>
		<?php elseif ( $rendered_content !== '' ) : ?>
			<?php the_content(); ?>
		<?php elseif ( $legacy_bio !== '' ) : ?>
			<?php echo wp_kses_post( wpautop( $legacy_bio ) ); ?>
		<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
			<p class="st-muted"><?php printf( esc_html__( 'No bio yet. Add one in %1$s or %2$s.', 'raveenthiran' ), '<a href="' . esc_url( admin_url( 'themes.php?page=nr-theme-settings' ) ) . '">' . esc_html__( 'Theme Settings', 'raveenthiran' ) . '</a>', '<a href="' . esc_url( get_edit_post_link() ) . '">' . esc_html__( 'this page', 'raveenthiran' ) . '</a>' ); ?></p>
		<?php endif; ?>
	</div>

	<?php /* stats */ ?>
	<div class="st-wrap">
		<div class="st-stats" data-reveal-stagger>
			<?php foreach ( $stats as $s ) : ?>
				<div class="st-stats__item"><span class="st-stats__n"><?php echo esc_html( $s[0] ); ?></span><span class="st-stats__l"><?php echo esc_html( $s[1] ); ?></span></div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php /* clients marquee */ ?>
	<?php
	$nr_clients_raw = (string) nr_opt( 'nr_clients_list', '' );
	$nr_clients     = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $nr_clients_raw ) ) ) );
	if ( empty( $nr_clients ) ) $nr_clients = [ 'SZ Magazin', 'Apartamento', 'NYT Magazine', 'Hotel Sacher', 'Belvedere', 'Monocle', 'Der Greif', "It's Nice That" ];
	?>
	<section class="nr-clients st-clients" aria-label="<?php esc_attr_e( 'Selected clients', 'raveenthiran' ); ?>">
		<span class="nr-clients__label"><?php esc_html_e( 'Clients', 'raveenthiran' ); ?></span>
		<div class="nr-clients__track">
			<?php foreach ( [ 0, 1 ] as $copy ) foreach ( $nr_clients as $name ) printf( '<span class="nr-clients__item"%s>%s</span>', $copy === 1 ? ' aria-hidden="true"' : '', esc_html( $name ) ); ?>
		</div>
	</section>

	<?php /* recognition */ ?>
	<?php
	$awards = nr_recognition_list( 'nr_awards_list' );
	$press  = nr_recognition_list( 'nr_press_list' );
	if ( empty( $awards ) ) $awards = [ [ 'year' => '2024', 'title' => 'Sony World, finalist', 'org' => 'Sony World Photography', 'url' => '' ], [ 'year' => '2023', 'title' => 'Lucie · editorial silver', 'org' => 'Lucie Awards', 'url' => '' ], [ 'year' => '2022', 'title' => 'LensCulture · long-list', 'org' => 'LensCulture', 'url' => '' ] ];
	if ( empty( $press ) )  $press  = [ [ 'year' => '2025', 'title' => 'Profile', 'org' => 'Monocle', 'url' => '' ], [ 'year' => '2024', 'title' => 'Featured project', 'org' => "It's Nice That", 'url' => '' ], [ 'year' => '2023', 'title' => 'Feature', 'org' => 'Der Greif', 'url' => '' ] ];
	$groups = [ [ 'label' => __( 'Awards', 'raveenthiran' ), 'items' => $awards ], [ 'label' => __( 'Press', 'raveenthiran' ), 'items' => $press ] ];
	?>
	<div class="st-wrap st-recognition" data-reveal>
		<?php foreach ( $groups as $g ) : if ( empty( $g['items'] ) ) continue; ?>
			<div class="st-recognition__col">
				<span class="st-eyebrow"><?php echo esc_html( $g['label'] ); ?></span>
				<ul class="st-recognition__list">
					<?php foreach ( $g['items'] as $row ) :
						$label = trim( $row['title'] ?: $row['org'] );
						$secondary = ( $row['title'] && $row['org'] ) ? $row['org'] : ''; ?>
						<li class="st-recognition__item">
							<span class="st-recognition__year"><?php echo esc_html( $row['year'] ); ?></span>
							<span class="st-recognition__body">
								<?php if ( $row['url'] ) : ?><a class="st-recognition__title" href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?> ↗</a>
								<?php else : ?><span class="st-recognition__title"><?php echo esc_html( $label ); ?></span><?php endif; ?>
								<?php if ( $secondary ) : ?><span class="st-recognition__org"><?php echo esc_html( $secondary ); ?></span><?php endif; ?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( current_user_can( 'edit_theme_options' ) && ! get_option( 'nr_awards_list' ) && ! get_option( 'nr_press_list' ) ) : ?>
		<p class="st-wrap st-muted"><?php printf( esc_html__( 'Showing sample awards / press. Edit in %s.', 'raveenthiran' ), '<a href="' . esc_url( admin_url( 'themes.php?page=nr-theme-settings' ) ) . '">' . esc_html__( 'Theme Settings → Awards & Press', 'raveenthiran' ) . '</a>' ); ?></p>
	<?php endif; ?>

	<?php /* testimonials */ ?>
	<?php
	$voices = [];
	$nr_tq = new WP_Query( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 4, 'no_found_rows' => true ] );
	while ( $nr_tq->have_posts() ) { $nr_tq->the_post(); $qc = trim( wp_strip_all_tags( get_the_content() ) ); if ( $qc !== '' ) $voices[] = [ 'quote' => $qc, 'cite' => get_the_title() ]; }
	wp_reset_postdata();
	$nr_voices_sample = empty( $voices );
	if ( $nr_voices_sample ) $voices = [ [ 'quote' => 'Calm on set, exact in the edit. The pictures still feel like the room felt.', 'cite' => 'Art Director · SZ Magazin' ], [ 'quote' => 'We booked one shoot and rebooked before the gallery even landed.', 'cite' => 'Brand Lead · Hotel Sacher' ] ];
	?>
	<section class="st-wrap st-words" aria-label="<?php esc_attr_e( 'What clients say', 'raveenthiran' ); ?>" data-reveal>
		<span class="st-eyebrow"><?php esc_html_e( 'Words', 'raveenthiran' ); ?></span>
		<ul class="st-words__list" data-reveal-stagger>
			<?php foreach ( $voices as $v ) : ?>
				<li class="st-words__item"><blockquote>“<?php echo esc_html( $v['quote'] ); ?>”</blockquote><?php if ( $v['cite'] ) : ?><cite><?php echo esc_html( $v['cite'] ); ?></cite><?php endif; ?></li>
			<?php endforeach; ?>
		</ul>
		<?php if ( $nr_voices_sample && current_user_can( 'edit_posts' ) ) : ?>
			<p class="st-muted"><?php printf( esc_html__( 'Showing sample testimonials. Add real ones under %s.', 'raveenthiran' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=nr_testimonial' ) ) . '">' . esc_html__( 'Testimonials', 'raveenthiran' ) . '</a>' ); ?></p>
		<?php endif; ?>
	</section>

	<?php if ( function_exists( 'nr_instagram_grid' ) ) : ?><div class="st-wrap st-ig-wrap"><?php nr_instagram_grid(); ?></div><?php endif; ?>
</section>

<?php get_footer(); ?>
