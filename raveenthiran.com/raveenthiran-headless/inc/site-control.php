<?php
/**
 * Site Control — the branded admin start screen.
 *
 * One page that answers, in order: is the site live? what is unfinished in the
 * work? what came in? what is not configured? It keeps a small change log
 * (what was saved since the last publish) so the headline can say how many
 * changes are waiting instead of a blind "Publish" button.
 *
 * Styles: assets/site-control.css (enqueued in functions.php).
 *
 * @package RaveenthiranHeadless
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ── Change log since the last publish ──────────────────────────────────── */

/** Saved-but-not-yet-published changes as [key => [t, label]], oldest first. */
function rvn_changes() {
	$c = get_option( 'rvn_changes', array() );
	return is_array( $c ) ? $c : array();
}

/** Record one change. The same item saved twice stays one row (latest wins). */
function rvn_change_log( $key, $label ) {
	$c = rvn_changes();
	unset( $c[ $key ] );
	$c[ $key ] = array( 't' => time(), 'label' => (string) $label );
	if ( count( $c ) > 50 ) { $c = array_slice( $c, -50, null, true ); }
	update_option( 'rvn_changes', $c, false );
}

/* Projects + journal posts: anything entering, leaving or changing while public. */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( ! in_array( $post->post_type, array( 'work', 'post' ), true ) ) { return; }
	if ( $new !== 'publish' && $old !== 'publish' ) { return; }
	$kind = ( $post->post_type === 'work' ) ? 'Project' : 'Journal';
	$what = ( $new !== 'publish' ) ? 'unpublished' : ( ( $old !== 'publish' ) ? 'published' : 'edited' );
	$name = ( $post->post_title !== '' ) ? $post->post_title : '(untitled)';
	rvn_change_log( 'p' . $post->ID, $kind . ' / ' . $name . ' / ' . $what );
}, 10, 3 );

/* Site settings (the ACF options page). */
add_action( 'acf/save_post', function ( $post_id ) {
	if ( $post_id === 'options' ) { rvn_change_log( 'options', 'Site settings / saved' ); }
}, 20 );

/* Albums, services, series. */
add_action( 'saved_term', function ( $term_id, $tt_id, $taxonomy ) {
	if ( ! in_array( $taxonomy, array( 'work_category', 'work_service', 'work_series' ), true ) ) { return; }
	$term = get_term( $term_id, $taxonomy );
	$name = ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
	$kind = array( 'work_category' => 'Album', 'work_service' => 'Service', 'work_series' => 'Series' );
	rvn_change_log( 't' . $term_id, $kind[ $taxonomy ] . ' / ' . $name . ' / saved' );
}, 10, 3 );

/* ── Small helpers ──────────────────────────────────────────────────────── */

/** "Today 21:40" / "8 Nov 19:05" in the site timezone. */
function rvn_when( $t ) {
	$t = (int) $t;
	if ( $t <= 0 ) { return '—'; }
	return ( wp_date( 'Y-m-d', $t ) === wp_date( 'Y-m-d' ) ) ? 'Today ' . wp_date( 'H:i', $t ) : wp_date( 'j M H:i', $t );
}

/** Number of gallery images the frontend would get — same rule as the REST `gallery` field. */
function rvn_gallery_count( $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		$g = get_field( 'gallery', $post_id );
		if ( is_array( $g ) && $g ) { return count( $g ); }
	}
	$featured = (int) get_post_thumbnail_id( $post_id );
	$n = 0;
	foreach ( get_attached_media( 'image', $post_id ) as $img ) {
		if ( (int) $img->ID !== $featured ) { $n++; }
	}
	return $n;
}

/** Enquiry numbers: total, last 30 days, estimated pipeline, count per session type. */
function rvn_enquiry_stats() {
	$ids = get_posts( array(
		'post_type'      => 'rvn_enquiry',
		'post_status'    => array( 'private', 'publish' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	$since = strtotime( '-30 days' );
	$recent = 0; $types = array(); $sum = 0.0; $cur = '';
	foreach ( $ids as $id ) {
		if ( get_post_time( 'U', true, $id ) >= $since ) { $recent++; }
		$t = get_post_meta( $id, '_rvn_type', true );
		if ( $t ) { $types[ $t ] = ( $types[ $t ] ?? 0 ) + 1; }
		$e = (string) get_post_meta( $id, '_rvn_estimate', true );
		if ( $e !== '' ) {
			if ( $cur === '' && preg_match( '/^\D+/', $e, $m ) ) { $cur = trim( $m[0] ); }
			$sum += (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '', $e ) );
		}
	}
	arsort( $types );
	return array( 'total' => count( $ids ), 'recent' => $recent, 'sum' => $sum, 'cur' => $cur, 'types' => $types );
}

/* ── The page ───────────────────────────────────────────────────────────── */

function rvn_site_control_page() {
	$ver      = wp_get_theme()->get( 'Version' );
	$settings = admin_url( 'admin.php?page=rvn-site' );
	$self     = admin_url( 'admin.php?page=site-control' );
	$acf      = function_exists( 'get_field' );

	/* Publish state. */
	list( $repo, $branch, $wf ) = rvn_deploy_target();
	$has_token = rvn_gh_token() !== '';
	$pub_url   = wp_nonce_url( admin_url( 'admin-post.php?action=rvn_publish' ), 'rvn_publish' );
	$token_url = $settings . '#acf-field_rvn_deploy_token';
	$changes   = array_reverse( rvn_changes(), true ); // newest first
	$n_changes = count( $changes );
	$last      = (int) get_option( 'rvn_last_publish', 0 );
	$dep       = isset( $_GET['rvn_deploy'] ) ? sanitize_key( wp_unslash( $_GET['rvn_deploy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( $dep === 'ok' ) { $state = 'sent'; }
	elseif ( $dep === 'notoken' || ! $has_token ) { $state = 'notoken'; }
	elseif ( $dep !== '' ) { $state = 'error'; }
	else { $state = $n_changes > 0 ? 'ready' : 'clean'; }

	$err = '';
	if ( $state === 'error' ) {
		$err = preg_match( '/^http(\d{3})$/', $dep, $m ) ? 'HTTP ' . $m[1] : 'No connection';
	}

	/* Work. */
	$work_ids = get_posts( array(
		'post_type'      => 'work',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	$projects = array();
	$incomplete = 0;
	foreach ( $work_ids as $id ) {
		$thumb   = (int) get_post_thumbnail_id( $id );
		$gallery = rvn_gallery_count( $id );
		$seo     = trim( (string) rvn_field( $id, 'seo_description' ) ) !== '';
		$ok      = $thumb && $gallery > 0 && $seo;
		if ( ! $ok ) { $incomplete++; }
		if ( count( $projects ) < 7 ) {
			$projects[] = array(
				'id'      => $id,
				'title'   => get_the_title( $id ),
				'img'     => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'medium_large' ) : '',
				'focal'   => rvn_focal( $id ),
				'year'    => (string) rvn_field( $id, 'year', '_still_year' ),
				'place'   => (string) rvn_field( $id, 'location', '_still_location' ),
				'gallery' => $gallery,
				'home'    => (bool) ( $acf ? get_field( 'featured_home', $id ) : false ),
				'seo'     => $seo,
				'ok'      => $ok,
			);
		}
	}
	$work_n = count( $work_ids );
	if ( $work_n === 0 ) { $work_head = 'No projects yet.'; }
	else {
		$work_head  = $work_n . ( $work_n === 1 ? ' project. ' : ' projects. ' );
		$work_head .= $incomplete ? $incomplete . ' incomplete.' : 'All complete.';
	}

	/* Enquiries + journal. */
	$enq     = rvn_enquiry_stats();
	$posts_n = (int) wp_count_posts( 'post' )->publish;

	/* Settings — one cell per tab of the ACF options page. Status: [todo?, label]. */
	$email = (string) rvn_site_opt( 'contact_email', '' );
	$smtp  = (int) rvn_site_opt( 'smtp_enable', 0 ) === 1;
	$turn  = trim( (string) rvn_site_opt( 'turnstile_site', '' ) ) !== '';
	$cells = array(
		array( 'Home',             'Hero, marquee, recognition, testimonials, travel dates',    'field_rvn_home_hero_lines', null ),
		array( 'Studio',           'Lede, bio, portrait, statement, spec, stats, clients',      'field_rvn_studio_lede',     null ),
		array( 'Contact',          'Email, location, response time, social links',              'field_rvn_contact_email',   array( $email === '', $email === '' ? 'No email' : 'Email set' ) ),
		array( 'Price calculator', 'Currency, sessions, add-ons, licence, travel per km',       'field_rvn_currency',        null ),
		array( 'FAQ',              'Questions and answers shown on the site',                   'field_rvn_faq',             null ),
		array( 'Mail',             'SMTP delivery for enquiry emails and the PDF auto-reply',   'field_rvn_smtp_enable',     array( ! $smtp, $smtp ? 'SMTP on' : 'SMTP off' ) ),
		array( 'Publishing',       'GitHub token, repository, branch, workflow file',           'field_rvn_deploy_token',    array( ! $has_token, $has_token ? 'Token set' : 'No token' ) ),
		array( 'Security',         'Cloudflare Turnstile spam shield for the enquiry form',     'field_rvn_turnstile_site',  array( ! $turn, $turn ? 'Turnstile on' : 'No site key' ) ),
	);
	$todo = 0;
	foreach ( $cells as $c ) { if ( $c[3] && $c[3][0] ) { $todo++; } }

	$tag = function ( $todo, $label ) {
		printf( '<span class="rvn-tag %s">%s</span>', $todo ? 'rvn-tag--todo' : 'rvn-tag--ok', esc_html( $label ) );
	};
	$target = function ( $first_label, $first_value ) use ( $repo, $branch, $wf ) {
		?>
		<dl class="rvn-list rvn-split__side">
			<div><dt><?php echo esc_html( $first_label ); ?></dt><dd><?php echo esc_html( $first_value ); ?></dd></div>
			<div><dt>Target</dt><dd><?php echo esc_html( $repo . ' @ ' . $branch ); ?></dd></div>
			<div><dt>Workflow</dt><dd><?php echo esc_html( $wf ); ?></dd></div>
		</dl>
		<?php
	};
	?>
	<div class="wrap rvn-sc">
		<hr class="wp-header-end">

		<header class="rvn-top">
			<div class="rvn-in rvn-top__in">
				<div class="rvn-top__l"><span class="rvn-wm">RAVEENTHIRAN</span><span>Site Control</span></div>
				<div class="rvn-top__r"><span>Theme v<?php echo esc_html( $ver ); ?></span><a href="https://raveenthiran.com" target="_blank" rel="noopener">raveenthiran.com ↗</a></div>
			</div>
		</header>

		<div class="rvn-pad"><div class="rvn-in">

			<section class="rvn-sec rvn-sec--first" aria-label="Publish"<?php echo ( $state === 'error' ) ? ' role="alert"' : ( ( $state === 'sent' ) ? ' role="status"' : '' ); ?>>
				<?php if ( $state === 'ready' ) : ?>
					<div class="rvn-lab"><span>01 — Publish</span><?php $tag( true, 'Not live' ); ?></div>
					<h1 class="rvn-h1"><?php echo esc_html( $n_changes . ( $n_changes === 1 ? ' change' : ' changes' ) . ' to publish' ); ?></h1>
					<div class="rvn-split">
						<ul class="rvn-list rvn-split__main">
							<?php $i = 0; foreach ( $changes as $c ) : if ( $i++ >= 5 ) { break; } ?>
								<li><span><?php echo esc_html( $c['label'] ); ?></span><span class="rvn-dim"><?php echo esc_html( rvn_when( $c['t'] ) ); ?></span></li>
							<?php endforeach; ?>
							<?php if ( $n_changes > 5 ) : ?>
								<li><span class="rvn-dim">+ <?php echo esc_html( $n_changes - 5 ); ?> more</span></li>
							<?php endif; ?>
						</ul>
						<?php $target( 'Last publish', $last ? rvn_when( $last ) : 'Not recorded' ); ?>
					</div>
					<a class="rvn-bar" href="<?php echo esc_url( $pub_url ); ?>"><span>Publish now</span><span aria-hidden="true">→</span></a>

				<?php elseif ( $state === 'clean' ) : ?>
					<div class="rvn-lab"><span>01 — Publish</span><?php $tag( false, 'Live' ); ?></div>
					<h1 class="rvn-h1">Everything is live</h1>
					<div class="rvn-split">
						<p class="rvn-lede rvn-split__main">Nothing has changed in WordPress since the last publish.</p>
						<?php $target( 'Last publish', $last ? rvn_when( $last ) : 'Not recorded' ); ?>
					</div>
					<a class="rvn-bar rvn-bar--ghost" href="<?php echo esc_url( $pub_url ); ?>"><span>Publish anyway</span><span aria-hidden="true">→</span></a>

				<?php elseif ( $state === 'sent' ) : ?>
					<div class="rvn-lab"><span>01 — Publish</span><span class="rvn-tag rvn-tag--inv">Deploy triggered</span></div>
					<h1 class="rvn-h1">Publishing…</h1>
					<div class="rvn-split">
						<p class="rvn-lede rvn-split__main">Live in about two minutes. Then hard-refresh the site: Ctrl/Cmd + Shift + R.</p>
						<?php $target( 'Triggered', rvn_when( $last ) ); ?>
					</div>
					<div class="rvn-bars">
						<a class="rvn-bar rvn-bar--ghost" href="https://raveenthiran.com" target="_blank" rel="noopener"><span>View live site</span><span aria-hidden="true">↗</span></a>
						<a class="rvn-bar rvn-bar--small" href="<?php echo esc_url( $self ); ?>">Dismiss</a>
					</div>

				<?php elseif ( $state === 'notoken' ) : ?>
					<div class="rvn-lab"><span>01 — Publish</span><?php $tag( true, 'Setup needed' ); ?></div>
					<h1 class="rvn-h1">No deploy token</h1>
					<p class="rvn-lede">Add a GitHub token once. After that every publish is one click. The token needs Actions: Read &amp; write on <?php echo esc_html( $repo ); ?>.</p>
					<a class="rvn-bar" href="<?php echo esc_url( $token_url ); ?>"><span>Add a GitHub token</span><span aria-hidden="true">→</span></a>

				<?php else : ?>
					<div class="rvn-lab"><span>01 — Publish</span><?php $tag( true, $err ); ?></div>
					<h1 class="rvn-h1">Deploy failed</h1>
					<div class="rvn-split">
						<p class="rvn-lede rvn-split__main">Nothing was published. Your changes are safe in WordPress. Check the token has Actions: Read &amp; write, and that branch and workflow exist.</p>
						<?php $target( 'Last publish', $last ? rvn_when( $last ) : 'Not recorded' ); ?>
					</div>
					<div class="rvn-bars">
						<a class="rvn-bar" href="<?php echo esc_url( $pub_url ); ?>"><span>Try again</span><span aria-hidden="true">→</span></a>
						<a class="rvn-bar rvn-bar--small" href="<?php echo esc_url( $token_url ); ?>">Publishing settings</a>
					</div>
				<?php endif; ?>
			</section>

			<section class="rvn-sec" aria-label="Work">
				<div class="rvn-lab"><span>02 — Work</span><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=work' ) ); ?>">All projects →</a></div>
				<h2 class="rvn-h2"><?php echo esc_html( $work_head ); ?></h2>
				<div class="rvn-frames">
					<?php foreach ( $projects as $k => $p ) : ?>
						<a class="rvn-frame" href="<?php echo esc_url( (string) get_edit_post_link( $p['id'], 'raw' ) ); ?>">
							<div class="rvn-frame__img">
								<?php if ( $p['img'] ) : ?>
									<img src="<?php echo esc_url( $p['img'] ); ?>" alt="" loading="lazy" decoding="async" style="object-position:<?php echo esc_attr( $p['focal'] ); ?>">
								<?php else : ?>
									<span class="rvn-frame__none">No featured image</span>
								<?php endif; ?>
								<span class="rvn-frame__no"><?php echo esc_html( sprintf( '%02d', $k + 1 ) ); ?></span>
							</div>
							<div class="rvn-frame__head">
								<span class="rvn-frame__title"><?php echo esc_html( $p['title'] ); ?></span>
								<?php $tag( ! $p['ok'], $p['ok'] ? 'OK' : 'Fix' ); ?>
							</div>
							<ul class="rvn-list rvn-list--tight">
								<li><span class="rvn-dim">Year / place</span><span><?php echo esc_html( trim( $p['year'] . ' / ' . $p['place'], ' /' ) ?: '—' ); ?></span></li>
								<li><span class="rvn-dim">Gallery</span><span<?php echo $p['gallery'] ? '' : ' class="rvn-miss"'; ?>><?php echo esc_html( $p['gallery'] ? $p['gallery'] . ( $p['gallery'] === 1 ? ' image' : ' images' ) : 'Empty' ); ?></span></li>
								<li><span class="rvn-dim">Homepage</span><span><?php echo $p['home'] ? 'Featured' : '—'; ?></span></li>
								<li><span class="rvn-dim">SEO description</span><span<?php echo $p['seo'] ? '' : ' class="rvn-miss"'; ?>><?php echo $p['seo'] ? 'Set' : 'Missing'; ?></span></li>
							</ul>
						</a>
					<?php endforeach; ?>
					<a class="rvn-frame rvn-frame--add" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=work' ) ); ?>">
						<div class="rvn-frame__img">
							<span class="rvn-frame__no"><?php echo esc_html( sprintf( '%02d', count( $projects ) + 1 ) ); ?></span>
							<span class="rvn-frame__add"><span>Add project</span><span aria-hidden="true">+</span></span>
						</div>
					</a>
				</div>
			</section>

			<section class="rvn-sec" aria-label="Enquiries">
				<div class="rvn-lab"><span>03 — Enquiries</span><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=rvn_enquiry' ) ); ?>">Open enquiries →</a></div>
				<div class="rvn-cells">
					<div class="rvn-cell"><span class="rvn-cell__k">Total</span><span class="rvn-num"><?php echo esc_html( $enq['total'] ); ?></span></div>
					<div class="rvn-cell"><span class="rvn-cell__k">Last 30 days</span><span class="rvn-num"><?php echo esc_html( $enq['recent'] ); ?></span></div>
					<div class="rvn-cell"><span class="rvn-cell__k">Pipeline, estimated</span><span class="rvn-num"><?php echo esc_html( $enq['sum'] > 0 ? $enq['cur'] . number_format( $enq['sum'] ) : '—' ); ?></span></div>
					<div class="rvn-cell">
						<span class="rvn-cell__k">By session</span>
						<?php if ( $enq['types'] ) : ?>
							<ul class="rvn-bars-list">
								<?php $i = 0; foreach ( $enq['types'] as $t => $n ) : if ( $i++ >= 4 ) { break; } ?>
									<li><span class="rvn-bars-list__row"><span><?php echo esc_html( $t ); ?></span><span><?php echo esc_html( $n ); ?></span></span><span class="rvn-meter" aria-hidden="true"><span style="width:<?php echo esc_attr( round( 100 * $n / max( 1, $enq['total'] ) ) ); ?>%"></span></span></li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<span class="rvn-dim rvn-mono">Nothing yet</span>
						<?php endif; ?>
					</div>
				</div>
			</section>

			<section class="rvn-sec rvn-sec--row" aria-label="Journal">
				<div class="rvn-row__l"><span class="rvn-lab__t">04 — Journal</span><span class="rvn-h3"><?php echo esc_html( $posts_n . ( $posts_n === 1 ? ' post' : ' posts' ) ); ?></span></div>
				<div class="rvn-row__r">
					<a href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>">New post +</a>
					<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">All posts →</a>
				</div>
			</section>

			<section class="rvn-sec" aria-label="Site settings">
				<div class="rvn-lab"><span>05 — Site settings</span><?php
					if ( ! $acf ) { $tag( true, 'ACF Pro inactive' ); }
					elseif ( $todo ) { $tag( true, $todo . ' to do' ); }
					else { $tag( false, 'All set' ); }
				?></div>
				<div class="rvn-cells">
					<?php foreach ( $cells as $k => $c ) : ?>
						<a class="rvn-cell rvn-cell--link" href="<?php echo esc_url( $settings . '#acf-' . $c[2] ); ?>">
							<span class="rvn-cell__k"><span><?php echo esc_html( chr( 65 + $k ) ); ?></span><?php if ( $c[3] ) { $tag( $c[3][0], $c[3][1] ); } ?></span>
							<span class="rvn-cell__b"><span class="rvn-cell__t"><?php echo esc_html( $c[0] ); ?></span><span class="rvn-dim"><?php echo esc_html( $c[1] ); ?></span></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<nav class="rvn-wp" aria-label="WordPress">
				<span class="rvn-wp__t">06 — WordPress</span>
				<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">Media</a>
				<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>">Appearance</a>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">Plugins</a>
				<a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>">Users</a>
				<a href="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">Tools</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">WP Settings</a>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=acf-field-group' ) ); ?>">Fields (ACF)</a>
			</nav>

			<p class="rvn-foot">After activating a new theme version: ACF Sync if prompted, then save Settings → Permalinks once.</p>

		</div></div>
	</div>
	<?php
}
