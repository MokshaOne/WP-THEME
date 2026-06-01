<?php
/**
 * Template Name: About
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'about';
get_header();
the_post();

$has_excerpt = trim( get_the_excerpt() ) !== '' && has_excerpt();
$portrait    = get_the_post_thumbnail_url( get_the_ID(), 'nr-card' );

$stats = [
	[ nr_opt( 'nr_stats_proj', '142' ), __( 'Projects', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_cnty', '18' ),  __( 'Countries', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_pubs', '36' ),  __( 'Publications', 'raveenthiran' ) ],
	[ nr_opt( 'nr_stats_awd', '11' ),   __( 'Awards', 'raveenthiran' ) ],
];
?>
<section class="nr-page nr-fullscreen nr-about">
	<div class="nr-about__portrait">
		<?php if ( $portrait ) : ?>
			<img src="<?php echo esc_url( $portrait ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="1200" height="1600" loading="eager" fetchpriority="high" decoding="async">
		<?php else : ?>
			<?php echo nr_placeholder( 'self portrait', true, 'auto' ); ?>
		<?php endif; ?>
		<div class="nr-about__portrait-foot">
			<span>Studio · <?php echo esc_html( nr_opt( 'nr_location', 'Wien' ) ); ?> · <?php echo esc_html( date_i18n( 'Y' ) ); ?></span>
			<span><?php esc_html_e( 'Self portrait', 'raveenthiran' ); ?></span>
		</div>
	</div>

	<div class="nr-about__body">
		<?php
		// Read raw DB values (NOT nr_opt) so a blank value means "hide", not
		// "fall back to WP page title". This stops the WP "page title" set in
		// the WP admin (Pages → Studio) from leaking through to the front-end.
		$nr_about_eb    = trim( (string) get_option( 'nr_about_eyebrow', '' ) );
		$nr_about_title = trim( (string) get_option( 'nr_about_title', '' ) );
		?>
		<?php if ( $nr_about_eb !== '' ) : ?>
			<span class="nr-eyebrow"><?php echo esc_html( $nr_about_eb ); ?></span>
		<?php endif; ?>
		<?php if ( $nr_about_title !== '' ) : ?>
			<h1 class="nr-display nr-display--md"><?php echo wp_kses( $nr_about_title, [ 'em' => [], 'br' => [], 'strong' => [], 'b' => [] ] ); ?></h1>
		<?php endif; ?>

		<?php
		$rendered_content = trim( wp_strip_all_tags( apply_filters( 'the_content', get_the_content() ) ) );
		$legacy_bio       = function_exists( 'nr_about_field' ) ? trim( (string) nr_about_field( 'about_bio', '' ) ) : '';
		$settings_lede    = trim( (string) nr_opt( 'nr_about_lede', '' ) );
		$settings_bio     = trim( (string) nr_opt( 'nr_about_bio', '' ) );
		$settings_name    = trim( (string) get_option( 'nr_about_name', '' ) );
		$settings_role    = trim( (string) get_option( 'nr_about_role', '' ) );

		$lede = $has_excerpt ? get_the_excerpt() : $settings_lede;
		if ( $lede ) : ?>
			<p class="nr-about__lede"><?php echo esc_html( $lede ); ?></p>
		<?php endif; ?>

		<?php if ( $settings_name !== '' || $settings_role !== '' ) : ?>
			<div class="nr-about__byline">
				<?php if ( $settings_name !== '' ) : ?>
					<span class="nr-about__byline-name"><?php echo esc_html( $settings_name ); ?></span>
				<?php endif; ?>
				<?php if ( $settings_role !== '' ) : ?>
					<span class="nr-about__byline-role"><?php echo esc_html( $settings_role ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		/* Bio resolution chain — Theme Settings wins.
		   Order (v3.4.1+): nr_about_bio (Theme Settings) → WP page content
		   (Pages → Studio) → legacy ACF options_about_bio → empty-state hint.
		   Flipped from the previous order so the admin can manage everything
		   from one place. Blank the Bio field in Theme Settings to fall back
		   to the WP page editor. */
		?>
		<?php if ( $settings_bio !== '' ) : ?>
			<div class="nr-about__sub"><?php echo wp_kses_post( wpautop( $settings_bio ) ); ?></div>
		<?php elseif ( $rendered_content !== '' ) : ?>
			<div class="nr-about__sub"><?php the_content(); ?></div>
		<?php elseif ( $legacy_bio !== '' ) : ?>
			<div class="nr-about__sub"><?php echo wp_kses_post( wpautop( $legacy_bio ) ); ?></div>
		<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
			<div class="nr-about__sub nr-about__sub--empty">
				<p><?php
					printf(
						/* translators: 1: settings link, 2: edit-page link */
						esc_html__( 'No bio yet. Add one in %1$s or %2$s.', 'raveenthiran' ),
						'<a href="' . esc_url( admin_url( 'themes.php?page=nr-theme-settings' ) ) . '">' . esc_html__( 'Theme Settings', 'raveenthiran' ) . '</a>',
						'<a href="' . esc_url( get_edit_post_link() ) . '">' . esc_html__( 'this page', 'raveenthiran' ) . '</a>'
					);
				?></p>
			</div>
		<?php endif; ?>

		<div class="nr-stats">
			<?php foreach ( $stats as $s ) : ?>
				<div>
					<div class="nr-stats__n"><?php echo esc_html( $s[0] ); ?></div>
					<div class="nr-stats__l"><?php echo esc_html( $s[1] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php
		/* Clients — horizontal auto-scrolling marquee of client names.
		   Lives between stats and recognition. Edited via Theme Settings →
		   "Clients" (one name per line). Falls back to a default set so
		   the design is visible on a fresh install. */
		$nr_clients_raw = (string) nr_opt( 'nr_clients_list', '' );
		$nr_clients     = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $nr_clients_raw ) ) ) );
		if ( empty( $nr_clients ) ) {
			$nr_clients = [
				'SZ Magazin', 'Apartamento', 'NYT Magazine', 'Hotel Sacher',
				'Belvedere', 'Monocle', 'Der Greif', "It's Nice That",
			];
		}
		?>
		<section class="nr-clients" aria-label="<?php esc_attr_e( 'Selected clients', 'raveenthiran' ); ?>">
			<span class="nr-clients__label"><?php esc_html_e( 'Clients', 'raveenthiran' ); ?></span>
			<div class="nr-clients__track">
				<?php
				// Render each name TWICE so translateX(-50%) loops seamlessly.
				foreach ( [ 0, 1 ] as $copy ) {
					foreach ( $nr_clients as $i => $name ) {
						printf(
							'<span class="nr-clients__item"%s>%s</span>',
							$copy === 1 ? ' aria-hidden="true"' : '',
							esc_html( $name )
						);
					}
				}
				?>
			</div>
		</section>


		<?php
		$awards = nr_recognition_list( 'nr_awards_list' );
		$press  = nr_recognition_list( 'nr_press_list' );

		// Fallback samples so the section is visible on a fresh install —
		// replaced as soon as the admin fills in either list under
		// Appearance → Theme Settings → Awards & Press.
		if ( empty( $awards ) ) {
			$awards = [
				[ 'year' => '2024', 'title' => 'Sony World, finalist',     'org' => 'Sony World Photography', 'url' => '' ],
				[ 'year' => '2023', 'title' => 'Lucie · editorial silver', 'org' => 'Lucie Awards',           'url' => '' ],
				[ 'year' => '2022', 'title' => 'LensCulture · long-list',  'org' => 'LensCulture',            'url' => '' ],
			];
		}
		if ( empty( $press ) ) {
			$press = [
				[ 'year' => '2025', 'title' => 'Profile',          'org' => 'Monocle',         'url' => '' ],
				[ 'year' => '2024', 'title' => 'Featured project', 'org' => "It's Nice That",  'url' => '' ],
				[ 'year' => '2023', 'title' => 'Feature',          'org' => 'Der Greif',       'url' => '' ],
			];
		}

		$nr_recognition_groups = [
			[ 'label' => __( 'Awards', 'raveenthiran' ), 'items' => $awards ],
			[ 'label' => __( 'Press',  'raveenthiran' ), 'items' => $press  ],
		];
		?>
		<div class="nr-about__recognition">
			<?php foreach ( $nr_recognition_groups as $g ) :
				if ( empty( $g['items'] ) ) continue; ?>
				<div class="nr-recognition">
					<span class="nr-eyebrow nr-eyebrow--xs"><?php echo esc_html( $g['label'] ); ?></span>
					<ul class="nr-recognition__list">
						<?php foreach ( $g['items'] as $row ) :
							$label = trim( $row['title'] ?: $row['org'] );
							$secondary = ( $row['title'] && $row['org'] ) ? $row['org'] : ''; ?>
							<li class="nr-recognition__item">
								<span class="nr-recognition__year"><?php echo esc_html( $row['year'] ); ?></span>
								<?php if ( $row['url'] ) : ?>
									<a class="nr-recognition__title" href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?> <span aria-hidden="true">↗</span></a>
								<?php else : ?>
									<span class="nr-recognition__title"><?php echo esc_html( $label ); ?></span>
								<?php endif; ?>
								<?php if ( $secondary ) : ?>
									<span class="nr-recognition__org"><?php echo esc_html( $secondary ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( current_user_can( 'edit_theme_options' ) && ! get_option( 'nr_awards_list' ) && ! get_option( 'nr_press_list' ) ) : ?>
			<p class="nr-admin-hint">
				<?php
				printf(
					/* translators: %s: theme settings link */
					esc_html__( 'Showing sample awards / press. Edit in %s.', 'raveenthiran' ),
					'<a href="' . esc_url( admin_url( 'themes.php?page=nr-theme-settings' ) ) . '">' . esc_html__( 'Theme Settings → Awards & Press', 'raveenthiran' ) . '</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
