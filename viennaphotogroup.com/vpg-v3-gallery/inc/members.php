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
    <main id="vpg-main">
        <header class="vpg-page-hero">
            <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Member', 'vpg-v2' ); ?></span>
            <h1><?php echo esc_html( $user->display_name ); ?></h1>
            <?php if ( $user->description ) : ?>
                <p class="vpg-lede"><?php echo esc_html( $user->description ); ?></p>
            <?php endif; ?>
            <p class="vpg-caps" style="margin-top:1.5rem">— <?php esc_html_e( 'Member since', 'vpg-v2' ); ?> <?php echo esc_html( date_i18n( 'F Y', strtotime( $user->user_registered ) ) ); ?></p>
        </header>
        <span class="vpg-asterism vpg-asterism--mark"></span>
        <section class="vpg-section vpg-section--tight">
            <div class="vpg-wrap">
                <?php
                $contribs = new WP_Query( [
                    'author'         => $user->ID,
                    'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ],
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
