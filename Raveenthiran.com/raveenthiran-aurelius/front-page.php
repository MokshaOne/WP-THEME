<?php
/**
 * Front page — Studio: a scrolling editorial landing.
 *   hero (full-bleed featured frame) · statement · selected work ·
 *   approach · clients · journal · a single voice.
 * Pulls from the nr_project / nr_journal / nr_testimonial CPTs; falls
 * back to samples so a fresh install still reads correctly.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$nr_current = 'home';

/* ── LCP: preload the hero (first featured project) image ───────── */
$nr_lcp_q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'meta_key' => 'featured_on_homepage', 'meta_value' => '1', 'fields' => 'ids', 'no_found_rows' => true ] );
if ( ! $nr_lcp_q->have_posts() ) {
	$nr_lcp_q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true ] );
}
$nr_lcp_id = $nr_lcp_q->have_posts() ? (int) get_post_thumbnail_id( $nr_lcp_q->posts[0] ) : 0;
wp_reset_postdata();
if ( $nr_lcp_id ) {
	add_action( 'wp_head', function () use ( $nr_lcp_id ) {
		$src = wp_get_attachment_image_url( $nr_lcp_id, 'nr-hero' );
		if ( ! $src ) return;
		$srcset = wp_get_attachment_image_srcset( $nr_lcp_id, 'nr-hero' );
		$type = '';
		if ( function_exists( 'nr_webp_swap_srcset' ) ) {
			$w_set = $srcset ? nr_webp_swap_srcset( $srcset ) : '';
			$w_src = function_exists( 'nr_webp_twin_url' ) ? nr_webp_twin_url( $src ) : '';
			if ( $w_set ) { $srcset = $w_set; $type = ' type="image/webp"'; if ( $w_src ) $src = $w_src; }
			elseif ( $w_src ) { $src = $w_src; $type = ' type="image/webp"'; }
		}
		printf(
			'<link rel="preload" as="image" href="%s"%s imagesizes="100vw"%s fetchpriority="high">' . "\n",
			esc_url( $src ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			$type
		);
	}, 2 );
}

get_header();

/* ── gather featured / recent projects ─────────────────────────── */
$nr_work_max = max( 3, min( 12, (int) nr_opt( 'nr_hero_max', 6 ) ) );
$q = new WP_Query( [
	'post_type'      => 'nr_project',
	'posts_per_page' => $nr_work_max,
	'meta_key'       => 'featured_on_homepage',
	'meta_value'     => '1',
	'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
] );
if ( ! $q->have_posts() ) {
	$q = new WP_Query( [
		'post_type'      => 'nr_project',
		'posts_per_page' => $nr_work_max,
		'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
	] );
}

$works = [];
if ( $q->have_posts() ) {
	while ( $q->have_posts() ) {
		$q->the_post();
		$m = nr_project_meta();
		$works[] = [
			'id'    => get_the_ID(),
			'title' => html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink(),
			'cat'   => $m['cat'] ?: __( 'Editorial', 'raveenthiran' ),
			'yr'    => $m['yr'],
			'loc'   => $m['loc'],
			'tid'   => has_post_thumbnail() ? (int) get_post_thumbnail_id() : 0,
		];
	}
	wp_reset_postdata();
} else {
	foreach ( [
		[ 'Nachtdienst', 'Editorial', 2024, 'Wien' ],
		[ 'A House in Mariahilf', 'Architecture', 2024, 'Wien' ],
		[ 'The Vienna Notebook', 'Portrait', 2024, 'Wien' ],
		[ 'Naschmarkt at Dawn', 'Street', 2023, 'Wien' ],
		[ 'Belvedere After Hours', 'Architecture', 2023, 'Wien' ],
		[ 'Rote Bar', 'Event', 2023, 'Wien' ],
	] as $s ) {
		$works[] = [ 'id' => 0, 'title' => $s[0], 'url' => get_post_type_archive_link( 'nr_project' ) ?: '#', 'cat' => $s[1], 'yr' => $s[2], 'loc' => $s[3], 'tid' => 0 ];
	}
}

$hero      = $works[0] ?? [];
$nr_studio = nr_opt( 'nr_studio', 'Vienna · worldwide' );
$nr_name   = nr_opt( 'nr_logo_text', 'Raveenthiran' );
$statement = nr_opt( 'nr_home_statement', __( 'A Vienna photographer making <em>quiet, exact pictures</em> — editorial, portrait and architecture, for people who care how a room felt.', 'raveenthiran' ) );
$intro_p   = nr_opt( 'nr_home_intro', __( 'Available for commissions and personal work across Europe. Selected projects below; the full archive lives in Work.', 'raveenthiran' ) );
?>

<?php /* ═══ HERO ═══════════════════════════════════════════════ */ ?>
<section class="st-hero" aria-label="<?php esc_attr_e( 'Featured work', 'raveenthiran' ); ?>">
	<div class="st-hero__media">
		<?php if ( ! empty( $hero['tid'] ) ) :
			echo wp_get_attachment_image( $hero['tid'], 'nr-hero', false, [
				'alt'           => $hero['title'],
				'sizes'         => '100vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'class'         => 'st-hero__img',
			] );
		else :
			echo nr_placeholder( strtolower( $hero['title'] ?? 'featured' ), false, 'auto' );
		endif; ?>
		<div class="st-hero__veil"></div>
	</div>

	<div class="st-hero__head">
		<span class="st-eyebrow st-eyebrow--light"><?php echo esc_html( nr_opt( 'nr_hero_eyebrow', __( 'Selected work', 'raveenthiran' ) ) . ' · ' . date_i18n( 'Y' ) ); ?></span>
	</div>

	<div class="st-wrap st-hero__foot">
		<a class="st-hero__caption" href="<?php echo esc_url( $hero['url'] ?? '#' ); ?>">
			<span class="st-hero__caption-t"><?php echo esc_html( $hero['title'] ?? '' ); ?></span>
			<span class="st-hero__caption-m"><?php echo esc_html( ( $hero['cat'] ?? '' ) . ' · ' . ( $hero['loc'] ?? '' ) . ' · ' . ( $hero['yr'] ?? '' ) ); ?></span>
		</a>
		<span class="st-hero__scroll" aria-hidden="true"><?php esc_html_e( 'Scroll', 'raveenthiran' ); ?><i></i></span>
	</div>
</section>

<?php /* ═══ STATEMENT ══════════════════════════════════════════ */ ?>
<section class="st-intro" data-reveal>
	<div class="st-wrap st-intro__grid">
		<p class="st-intro__lead"><?php echo wp_kses( $statement, [ 'em' => [], 'br' => [] ] ); ?></p>
		<div class="st-intro__side">
			<p><?php echo esc_html( $intro_p ); ?></p>
			<a class="st-link" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ); ?>"><?php esc_html_e( 'View all work', 'raveenthiran' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
</section>

<?php /* ═══ SELECTED WORK ══════════════════════════════════════ */ ?>
<section class="st-work" aria-label="<?php esc_attr_e( 'Selected projects', 'raveenthiran' ); ?>">
	<div class="st-wrap">
		<header class="st-sec-head st-sec-head--row" data-reveal>
			<div>
				<span class="st-eyebrow"><?php esc_html_e( 'Projects', 'raveenthiran' ); ?></span>
				<h2 class="st-sec-title"><?php echo wp_kses( nr_opt( 'nr_home_work_title', __( 'Selected <em>work</em>', 'raveenthiran' ) ), [ 'em' => [] ] ); ?></h2>
			</div>
			<span class="st-count">01 — <?php echo esc_html( str_pad( (string) count( $works ), 2, '0', STR_PAD_LEFT ) ); ?></span>
		</header>

		<div class="st-work__grid" data-reveal-stagger>
			<?php foreach ( $works as $i => $w ) :
				// asymmetric rhythm: every 3rd item is wide, others alternate offset
				$wide = ( $i % 3 === 0 );
				$cls  = 'st-work__item' . ( $wide ? ' is-wide' : ( $i % 2 ? ' is-low' : '' ) );
			?>
				<a class="<?php echo esc_attr( $cls ); ?>" href="<?php echo esc_url( $w['url'] ); ?>">
					<div class="st-work__frame">
						<?php if ( $w['tid'] ) :
							echo wp_get_attachment_image( $w['tid'], $wide ? 'nr-hero' : 'nr-card', false, [
								'alt'      => $w['title'],
								'sizes'    => $wide ? '(max-width:900px) 100vw, 66vw' : '(max-width:900px) 100vw, 42vw',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'class'    => 'st-work__img',
							] );
						else :
							echo nr_placeholder( strtolower( $w['title'] ), false, $wide ? '3/2' : '4/5' );
						endif; ?>
					</div>
					<div class="st-work__cap">
						<span class="st-work__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="st-work__t"><?php echo esc_html( $w['title'] ); ?></span>
						<span class="st-work__m"><?php echo esc_html( $w['cat'] . ' · ' . $w['yr'] ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="st-work__more" data-reveal>
			<a class="st-btn st-btn--line" href="<?php echo esc_url( get_post_type_archive_link( 'nr_project' ) ?: home_url( '/portfolio' ) ); ?>"><?php esc_html_e( 'The complete portfolio', 'raveenthiran' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
</section>

<?php /* ═══ APPROACH ═══════════════════════════════════════════ */ ?>
<?php
$approach = [
	[ nr_opt( 'nr_home_ap1_t', __( 'Editorial', 'raveenthiran' ) ), nr_opt( 'nr_home_ap1_d', __( 'Stories with a point of view — magazines, brands and books that want a real photographer, not a stock look.', 'raveenthiran' ) ) ],
	[ nr_opt( 'nr_home_ap2_t', __( 'Portrait', 'raveenthiran' ) ),  nr_opt( 'nr_home_ap2_d', __( 'People at ease. Studio or on location, always calm on set and exact in the edit.', 'raveenthiran' ) ) ],
	[ nr_opt( 'nr_home_ap3_t', __( 'Architecture', 'raveenthiran' ) ), nr_opt( 'nr_home_ap3_d', __( 'Spaces and light. Interiors, buildings and the hours of the day that make them.', 'raveenthiran' ) ) ],
];
?>
<section class="st-approach" aria-label="<?php esc_attr_e( 'Approach', 'raveenthiran' ); ?>">
	<div class="st-wrap">
		<header class="st-sec-head" data-reveal>
			<span class="st-eyebrow"><?php esc_html_e( 'What I do', 'raveenthiran' ); ?></span>
			<h2 class="st-sec-title"><?php echo wp_kses( nr_opt( 'nr_home_ap_title', __( 'Three ways to <em>work together</em>', 'raveenthiran' ) ), [ 'em' => [] ] ); ?></h2>
		</header>
		<div class="st-approach__grid" data-reveal-stagger>
			<?php foreach ( $approach as $i => $a ) : ?>
				<div class="st-approach__item">
					<span class="st-approach__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3 class="st-approach__t"><?php echo esc_html( $a[0] ); ?></h3>
					<p class="st-approach__d"><?php echo esc_html( $a[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php /* ═══ CLIENTS marquee ════════════════════════════════════ */ ?>
<?php
$nr_clients_raw = (string) nr_opt( 'nr_clients_list', '' );
$nr_clients     = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $nr_clients_raw ) ) ) );
if ( empty( $nr_clients ) ) {
	$nr_clients = [ 'SZ Magazin', 'Apartamento', 'NYT Magazine', 'Hotel Sacher', 'Belvedere', 'Monocle', 'Der Greif', "It's Nice That" ];
}
?>
<section class="nr-clients st-clients" aria-label="<?php esc_attr_e( 'Selected clients', 'raveenthiran' ); ?>">
	<span class="nr-clients__label"><?php esc_html_e( 'Trusted by', 'raveenthiran' ); ?></span>
	<div class="nr-clients__track">
		<?php foreach ( [ 0, 1 ] as $copy ) {
			foreach ( $nr_clients as $name ) {
				printf( '<span class="nr-clients__item"%s>%s</span>', $copy === 1 ? ' aria-hidden="true"' : '', esc_html( $name ) );
			}
		} ?>
	</div>
</section>

<?php /* ═══ JOURNAL teaser ═════════════════════════════════════ */ ?>
<?php
$jq = new WP_Query( [ 'post_type' => 'nr_journal', 'posts_per_page' => 3, 'no_found_rows' => true ] );
if ( $jq->have_posts() ) : ?>
<section class="st-journal" aria-label="<?php esc_attr_e( 'From the journal', 'raveenthiran' ); ?>">
	<div class="st-wrap">
		<header class="st-sec-head st-sec-head--row" data-reveal>
			<div>
				<span class="st-eyebrow"><?php esc_html_e( 'Journal', 'raveenthiran' ); ?></span>
				<h2 class="st-sec-title"><?php echo wp_kses( nr_opt( 'nr_home_journal_title', __( 'Notes &amp; <em>field work</em>', 'raveenthiran' ) ), [ 'em' => [] ] ); ?></h2>
			</div>
			<a class="st-link" href="<?php echo esc_url( function_exists( 'nr_journal_url' ) ? nr_journal_url() : home_url( '/journal' ) ); ?>"><?php esc_html_e( 'All entries', 'raveenthiran' ); ?> <span aria-hidden="true">→</span></a>
		</header>
		<div class="st-journal__grid" data-reveal-stagger>
			<?php while ( $jq->have_posts() ) : $jq->the_post();
				$jcat = wp_get_post_terms( get_the_ID(), 'nr_journal_cat', [ 'fields' => 'names' ] );
				$jcat = ( ! is_wp_error( $jcat ) && $jcat ) ? $jcat[0] : __( 'Journal', 'raveenthiran' ); ?>
				<a class="st-journal__item" href="<?php the_permalink(); ?>">
					<div class="st-journal__frame">
						<?php if ( has_post_thumbnail() ) :
							echo get_the_post_thumbnail( get_the_ID(), 'nr-card', [ 'alt' => get_the_title(), 'loading' => 'lazy', 'decoding' => 'async', 'class' => 'st-journal__img', 'sizes' => '(max-width:900px) 100vw, 32vw' ] );
						else :
							echo nr_placeholder( strtolower( get_the_title() ), false, '4/3' );
						endif; ?>
					</div>
					<span class="st-journal__meta"><?php echo esc_html( get_the_date( 'M Y' ) . ' · ' . $jcat ); ?></span>
					<h3 class="st-journal__t"><?php the_title(); ?></h3>
					<p class="st-journal__x"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
				</a>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php endif; wp_reset_postdata(); ?>

<?php /* ═══ ONE VOICE ══════════════════════════════════════════ */ ?>
<?php
$vq = new WP_Query( [ 'post_type' => 'nr_testimonial', 'posts_per_page' => 1, 'orderby' => 'rand', 'no_found_rows' => true ] );
$voice = null;
if ( $vq->have_posts() ) { $vq->the_post();
	$qt = trim( wp_strip_all_tags( get_the_content() ) );
	if ( $qt !== '' ) $voice = [ 'quote' => $qt, 'cite' => get_the_title() ];
	wp_reset_postdata();
}
if ( ! $voice ) $voice = [ 'quote' => __( 'Calm on set, exact in the edit. The pictures still feel like the room felt.', 'raveenthiran' ), 'cite' => __( 'Art Director · SZ Magazin', 'raveenthiran' ) ];
?>
<section class="st-voice" data-reveal>
	<div class="st-wrap">
		<blockquote class="st-voice__q">“<?php echo esc_html( $voice['quote'] ); ?>”</blockquote>
		<cite class="st-voice__cite"><?php echo esc_html( $voice['cite'] ); ?></cite>
	</div>
</section>

<?php get_footer(); ?>
