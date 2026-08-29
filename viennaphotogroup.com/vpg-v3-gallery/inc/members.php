<?php
/**
 * VPG v2 — Member features.
 *
 *   - /members/{username}/ profile pages
 *   - Issue bookmarks · user_meta `_vpg_bookmarks` · array of post IDs
 *   - Cross-CPT search · /?s= scans all 7 CPTs by default
 *
 * Each feature is a small subsystem · all live in one file to keep the
 * theme's inc/ directory short and discoverable.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  Member profile pages                                            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'init', function () {
    add_rewrite_rule( '^members/([^/]+)/?$', 'index.php?vpg_member=$matches[1]', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'vpg_member'; return $v; } );

add_filter( 'template_include', function ( $template ) {
    if ( ! get_query_var( 'vpg_member' ) ) return $template;
    $custom = locate_template( 'templates/member-profile.php' );
    return $custom ?: $template;
} );

/* Member profile template loader → renders inline if no file exists */
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'vpg_member' ) ) return;
    $username = sanitize_user( get_query_var( 'vpg_member' ) );
    $user     = get_user_by( 'login', $username ) ?: get_user_by( 'slug', $username );

    if ( ! $user ) {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        return;
    }

    if ( locate_template( 'templates/member-profile.php' ) ) return;

    get_header();
    ?>
    <?php
    $works_count = count_user_posts( $user->ID, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'post' ], true );
    $founding    = (int) date( 'Y', strtotime( $user->user_registered ) ) <= 2019;
    $insta       = get_user_meta( $user->ID, '_vpg_instagram', true );
    ?>
    <main id="vpg-main">
        <header class="vpg-page-hero">
            <?php echo get_avatar( $user->ID, 120, '', esc_attr( $user->display_name ), [ 'style' => 'display:block;width:120px;height:120px;object-fit:cover;margin:0 auto 1.4rem' ] ); ?>
            <div style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;margin-bottom:.6rem">
                <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> <?php
                    echo esc_html( function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $user->ID )['label'] : __( 'Member', 'vpg-v2' ) );
                ?></span>
                <?php if ( $founding ) : ?>
                    <span class="vpg-chip"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Founding member', 'vpg-v2' ); ?></span>
                <?php endif; ?>
                <?php if ( function_exists( 'vpg_trust_level' ) && vpg_trust_level( $user->ID ) >= 2 ) : ?>
                    <span class="vpg-chip"><span class="vpg-chip__dot"></span> <?php echo esc_html( vpg_trust_label( vpg_trust_level( $user->ID ) ) ); ?></span>
                <?php endif; ?>
            </div>
            <h1><?php echo esc_html( $user->display_name ); ?></h1>
            <?php if ( $user->description ) : ?>
                <p class="vpg-lede"><?php echo esc_html( $user->description ); ?></p>
            <?php endif; ?>
            <p class="vpg-caps" style="margin-top:1.5rem">
                — <?php esc_html_e( 'Member since', 'vpg-v2' ); ?> <?php echo esc_html( date_i18n( 'F Y', strtotime( $user->user_registered ) ) ); ?>
                · <?php printf( esc_html( _n( '%d work', '%d works', (int) $works_count, 'vpg-v2' ) ), (int) $works_count ); ?>
            </p>
            <?php if ( $user->user_url || $insta ) : ?>
            <p class="vpg-caps" style="margin-top:.6rem">
                <?php if ( $user->user_url ) : ?><a href="<?php echo esc_url( $user->user_url ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Website', 'vpg-v2' ); ?> ↗</a><?php endif; ?>
                <?php if ( $user->user_url && $insta ) echo ' · '; ?>
                <?php if ( $insta ) : ?><a href="<?php echo esc_url( 'https://www.instagram.com/' . rawurlencode( $insta ) . '/' ); ?>" rel="noopener" target="_blank">@<?php echo esc_html( $insta ); ?> ↗</a><?php endif; ?>
            </p>
            <?php endif; ?>
        </header>
        <span class="vpg-asterism vpg-asterism--mark"></span>

        <?php
        // Portfolio wall · frames the member picked and ordered themselves
        $pf_ids = function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $user->ID ) : [];
        $pf     = [];
        foreach ( $pf_ids as $aid ) {
            $full  = wp_get_attachment_image_url( $aid, 'large' );
            $thumb = wp_get_attachment_image_url( $aid, 'medium_large' );
            if ( ! $full || ! $thumb ) continue;
            $pf[] = [
                'full'    => $full,
                'thumb'   => $thumb,
                'caption' => get_the_title( $aid ),
                'exif'    => function_exists( 'vpg_photo_exif_label' ) ? vpg_photo_exif_label( $aid ) : '',
            ];
        }
        if ( $pf ) :
        ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap">
                <div class="vpg-section-head">
                    <div><p class="vpg-caps">— <?php esc_html_e( 'Portfolio', 'vpg-v2' ); ?></p>
                    <h2><?php printf( esc_html( _n( '%d frame', '%d frames', count( $pf ), 'vpg-v2' ) ), count( $pf ) ); ?></h2></div>
                </div>
                <div id="vpg-pf-wall" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:22px">
                    <?php foreach ( $pf as $i => $p ) : ?>
                    <figure style="margin:0">
                        <button type="button" data-pf="<?php echo (int) $i; ?>" style="display:block;width:100%;aspect-ratio:4/5;overflow:hidden;border:0;padding:0;background:#EDECE8;cursor:zoom-in">
                            <img src="<?php echo esc_url( $p['thumb'] ); ?>" alt="<?php echo esc_attr( $p['caption'] ); ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block">
                        </button>
                        <?php if ( $p['caption'] ) : ?><figcaption class="vpg-caps" style="margin-top:8px"><?php echo esc_html( $p['caption'] ); ?> — <?php echo esc_html( $user->display_name ); ?></figcaption><?php endif; ?>
                    </figure>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Lightbox · keyboard-first, EXIF wall label -->
            <div id="vpg-pf-lb" hidden role="dialog" aria-modal="true" aria-label="Portfolio" style="position:fixed;inset:0;z-index:9999;background:rgba(8,8,8,.96);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px">
                <img id="vpg-pf-lb-img" role="img" src="" alt="" style="max-width:92vw;max-height:78vh;object-fit:contain">
                <div id="vpg-pf-lb-cap" style="color:#9C9A95;font-size:11px;letter-spacing:.16em;text-transform:uppercase;margin-top:16px;text-align:center"></div>
                <button type="button" id="vpg-pf-lb-x" aria-label="Close" style="position:absolute;top:18px;right:22px;background:none;border:0;color:#fff;font-size:30px;cursor:pointer">×</button>
                <button type="button" id="vpg-pf-lb-prev" aria-label="Previous" style="position:absolute;left:14px;top:50%;background:none;border:0;color:#fff;font-size:34px;cursor:pointer">‹</button>
                <button type="button" id="vpg-pf-lb-next" aria-label="Next" style="position:absolute;right:14px;top:50%;background:none;border:0;color:#fff;font-size:34px;cursor:pointer">›</button>
            </div>
            <script>
            (function () {
                var data = <?php echo wp_json_encode( array_map( function ( $p ) use ( $user ) {
                    return [ 'full' => $p['full'], 'cap' => trim( $p['caption'] . ' — ' . $user->display_name, ' —' ), 'exif' => $p['exif'] ];
                }, $pf ) ); ?>;
                var lb = document.getElementById('vpg-pf-lb');
                var img = document.getElementById('vpg-pf-lb-img');
                var cap = document.getElementById('vpg-pf-lb-cap');
                var cur = 0;
                function show(i) {
                    cur = (i + data.length) % data.length;
                    img.src = data[cur].full;
                    cap.textContent = data[cur].cap + (data[cur].exif ? '   ·   ' + data[cur].exif : '');
                    lb.hidden = false;
                    document.body.style.overflow = 'hidden';
                }
                function close() { lb.hidden = true; document.body.style.overflow = ''; }
                document.getElementById('vpg-pf-wall').addEventListener('click', function (e) {
                    var b = e.target.closest('[data-pf]');
                    if (b) show(parseInt(b.getAttribute('data-pf'), 10));
                });
                document.getElementById('vpg-pf-lb-x').addEventListener('click', close);
                document.getElementById('vpg-pf-lb-prev').addEventListener('click', function () { show(cur - 1); });
                document.getElementById('vpg-pf-lb-next').addEventListener('click', function () { show(cur + 1); });
                lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
                document.addEventListener('keydown', function (e) {
                    if (lb.hidden) return;
                    if (e.key === 'Escape') close();
                    if (e.key === 'ArrowLeft') show(cur - 1);
                    if (e.key === 'ArrowRight') show(cur + 1);
                });
            }());
            </script>
        </section>
        <?php endif; ?>

        <?php
        // ─── In the magazine · issues this member appears in ─────────
        // Compiled issues store their articles as JSON in _vpg_articles;
        // an author match (interview, featured artist, photo credit)
        // surfaces the issue on the member's portfolio automatically.
        $in_issues = get_posts( [
            'post_type'      => 'vpg_magazine',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'meta_query'     => [ [
                'key'     => '_vpg_articles',
                'value'   => $user->display_name,
                'compare' => 'LIKE',
            ] ],
        ] );
        if ( $in_issues ) : ?>
        <section class="vpg-section vpg-section--tight" style="background:var(--g-ink,#0B0B0B);color:#fff">
            <div class="vpg-wrap">
                <div class="vpg-section-head">
                    <div><p class="vpg-caps" style="color:var(--g-red,#E5341F)">— <?php esc_html_e( 'In the magazine', 'vpg-v2' ); ?></p>
                    <h2 style="color:#fff"><?php printf( esc_html( _n( '%d issue', '%d issues', count( $in_issues ), 'vpg-v2' ) ), count( $in_issues ) ); ?></h2></div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:18px">
                    <?php foreach ( $in_issues as $iss ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $iss ) ); ?>" style="display:block;color:#fff">
                            <span style="display:block;aspect-ratio:3/4;background:#1A1A1A;overflow:hidden">
                                <?php echo get_the_post_thumbnail( $iss, 'medium', [ 'style' => 'width:100%;height:100%;object-fit:cover' ] ); ?>
                            </span>
                            <span style="display:block;margin-top:8px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9C9A95"><?php echo esc_html( get_post_meta( $iss->ID, '_vpg_issue_number', true ) ?: get_the_date( 'F Y', $iss ) ); ?></span>
                            <span style="display:block;font-weight:700;font-size:14px;line-height:1.3"><?php echo esc_html( $iss->post_title ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php
        // ─── Words · published journal writing by this member ────────
        $words = get_posts( [
            'author'         => $user->ID,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
        ] );
        if ( $words ) : ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap">
                <div class="vpg-section-head">
                    <div><p class="vpg-caps">— <?php esc_html_e( 'Words', 'vpg-v2' ); ?></p>
                    <h2><?php printf( esc_html( _n( '%d story', '%d stories', count( $words ), 'vpg-v2' ) ), count( $words ) ); ?></h2></div>
                </div>
                <div style="display:grid;gap:0">
                    <?php foreach ( $words as $w ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $w ) ); ?>" style="display:flex;justify-content:space-between;gap:20px;align-items:baseline;padding:14px 0;border-top:1px solid var(--g-line,#E6E5E1);font-weight:600">
                            <span><?php echo esc_html( $w->post_title ); ?></span>
                            <span style="flex:none;font-size:12px;color:var(--g-mid,#6A6A6A)"><?php echo esc_html( get_the_date( '', $w ) ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap">
                <?php
                $contribs = new WP_Query( [
                    'author'         => $user->ID,
                    'post_type'      => function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'vpg_location' ],
                    'posts_per_page' => 24,
                    'post_status'    => 'publish',
                ] );
                if ( $contribs->have_posts() ) :
                ?>
                    <div class="vpg-section-head">
                        <div><p class="vpg-caps">— <?php esc_html_e( 'Contributions', 'vpg-v2' ); ?></p>
                        <h2><?php printf( esc_html( _n( '%d entry', '%d entries', $contribs->found_posts, 'vpg-v2' ) ), $contribs->found_posts ); ?></h2></div>
                    </div>
                    <div class="vpg-grid vpg-grid--auto">
                        <?php while ( $contribs->have_posts() ) : $contribs->the_post(); ?>
                            <article class="vpg-card">
                                <a href="<?php the_permalink(); ?>">
                                    <?php vpg_chip( get_post_type() ); ?>
                                    <h3 style="margin-top:.8rem"><?php the_title(); ?></h3>
                                    <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                                </a>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php else : ?>
                    <div style="text-align:center;padding:4rem 0">
                        <p class="vpg-caps" style="margin-bottom:1.2rem">— <?php esc_html_e( 'No public contributions yet.', 'vpg-v2' ); ?></p>
                        <?php if ( get_current_user_id() === $user->ID ) : ?>
                            <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Submit your first piece', 'vpg-v2' ); ?> →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php
        // Where this member photographs · their published place pins
        $place_pins = [];
        if ( function_exists( 'vpg_get_coords' ) ) {
            $places = get_posts( [
                'author'         => $user->ID,
                'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ],
                'post_status'    => 'publish',
                'posts_per_page' => 100,
            ] );
            foreach ( $places as $pl ) {
                $coords = vpg_get_coords( $pl->ID );
                if ( ! $coords ) continue;
                $place_pins[] = [
                    'id'    => (int) $pl->ID,
                    'lat'   => $coords[0],
                    'lng'   => $coords[1],
                    'title' => get_the_title( $pl ),
                    'url'   => get_permalink( $pl ),
                    'type'  => str_replace( 'vpg_', '', get_post_type( $pl ) ),
                ];
            }
        }
        if ( $place_pins ) :
        ?>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap">
                <div class="vpg-section-head">
                    <div><p class="vpg-caps">— <?php esc_html_e( 'On the map', 'vpg-v2' ); ?></p>
                    <h2><?php printf( esc_html( _n( '%d pin', '%d pins', count( $place_pins ), 'vpg-v2' ) ), count( $place_pins ) ); ?></h2></div>
                </div>
                <div class="vpg-map" data-pins="<?php echo esc_attr( wp_json_encode( $place_pins ) ); ?>" style="height:380px"></div>
            </div>
        </section>
        <?php endif; ?>
    </main>
    <?php
    get_footer();
    exit;
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Issue bookmarks · "save for later"                              */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_vpg_toggle_bookmark', function () {
    if ( ! is_user_logged_in() ) wp_send_json_error( 'login required', 403 );
    check_ajax_referer( 'vpg_bookmark' );

    $post_id = (int) ( $_POST['post_id'] ?? 0 );
    if ( ! $post_id ) wp_send_json_error( 'missing post_id' );

    $uid   = get_current_user_id();
    $marks = (array) get_user_meta( $uid, '_vpg_bookmarks', true );
    $marks = array_filter( $marks, 'is_numeric' );

    if ( in_array( $post_id, $marks, true ) ) {
        $marks = array_diff( $marks, [ $post_id ] );
        $state = 'removed';
    } else {
        $marks[] = $post_id;
        $state   = 'added';
    }
    update_user_meta( $uid, '_vpg_bookmarks', array_values( array_unique( array_map( 'intval', $marks ) ) ) );

    wp_send_json_success( [ 'state' => $state, 'count' => count( $marks ) ] );
} );

function vpg_user_bookmarks( $uid = null ) {
    $uid = $uid ?: get_current_user_id();
    return array_filter( (array) get_user_meta( $uid, '_vpg_bookmarks', true ), 'is_numeric' );
}

function vpg_is_bookmarked( $post_id, $uid = null ) {
    return in_array( (int) $post_id, array_map( 'intval', vpg_user_bookmarks( $uid ) ), true );
}

/* Render a bookmark toggle anywhere · usage: echo vpg_bookmark_button( $post->ID ); */
function vpg_bookmark_button( $post_id ) {
    if ( ! is_user_logged_in() ) return '';
    $active = vpg_is_bookmarked( $post_id );
    $nonce  = wp_create_nonce( 'vpg_bookmark' );
    $label  = $active ? __( 'Saved', 'vpg-v2' ) : __( 'Save for later', 'vpg-v2' );
    return sprintf(
        '<button type="button" class="vpg-bookmark%s" data-vpg-bookmark="%d" data-nonce="%s" aria-pressed="%s">★ <span>%s</span></button>',
        $active ? ' is-active' : '',
        (int) $post_id,
        esc_attr( $nonce ),
        $active ? 'true' : 'false',
        esc_html( $label )
    );
}

add_action( 'wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    ?>
    <script>
    document.querySelectorAll('[data-vpg-bookmark]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id    = btn.getAttribute('data-vpg-bookmark');
            var nonce = btn.getAttribute('data-nonce');
            var fd    = new FormData();
            fd.append('action', 'vpg_toggle_bookmark');
            fd.append('_ajax_nonce', nonce);
            fd.append('post_id', id);
            fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', credentials: 'same-origin', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data.success) {
                        btn.classList.toggle('is-active', data.data.state === 'added');
                        btn.setAttribute('aria-pressed', data.data.state === 'added' ? 'true' : 'false');
                        var lbl = btn.querySelector('span');
                        if (lbl) lbl.textContent = data.data.state === 'added' ? '<?php echo esc_js( __( 'Saved', 'vpg-v2' ) ); ?>' : '<?php echo esc_js( __( 'Save for later', 'vpg-v2' ) ); ?>';
                    }
                })
                .catch(function () {});
        });
    });
    </script>
    <style>
        .vpg-bookmark {
            display: inline-flex; align-items: center; gap: .4rem;
            background: transparent; border: 1px solid var(--vpg-rule-strong);
            padding: .4rem 1rem; border-radius: var(--vpg-radius-pill);
            font-family: var(--vpg-font-mono); font-size: var(--vpg-fs-xs);
            letter-spacing: var(--vpg-tr-caps); text-transform: uppercase;
            color: var(--vpg-muted); cursor: pointer;
            transition: background var(--vpg-t-fast), color var(--vpg-t-fast), border-color var(--vpg-t-fast);
        }
        .vpg-bookmark:hover { border-color: var(--vpg-accent); color: var(--vpg-accent); }
        .vpg-bookmark.is-active { background: var(--vpg-accent); color: var(--vpg-bg); border-color: var(--vpg-accent); }
    </style>
    <?php
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Cross-CPT search · /?s= scans every VPG content type            */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) return;
    $q->set( 'post_type', [ 'post', 'page', 'vpg_magazine', 'vpg_event', 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ] );
} );
