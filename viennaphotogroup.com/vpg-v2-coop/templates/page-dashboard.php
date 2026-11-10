<?php
/** Template Name: Dashboard */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<?php if ( ! is_user_logged_in() ) : ?>

    <header class="vpg-page-hero">
        <span class="vpg-chip"><span class="vpg-chip__dot"></span> Members area</span>
        <h1>Members <em>only</em>.</h1>
        <p class="vpg-lede">Sign in to access your dashboard, submissions and member-only resources.</p>
    </header>
    <section class="vpg-section vpg-section--tight" style="text-align:center">
        <div class="vpg-wrap--narrow">
            <a class="vpg-btn vpg-btn--accent vpg-btn--lg" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">Log in <span class="vpg-btn__arrow">→</span></a>
            <p style="margin-top:1.5rem;color:var(--vpg-muted)">Not a member? <a href="<?php echo esc_url( home_url('/membership/') ); ?>">Join the wait-list →</a></p>
        </div>
    </section>

<?php else :
    $u         = wp_get_current_user();
    $tier      = get_user_meta( $u->ID, '_vpg_tier', true ) ?: 'reader';
    $bookmarks = function_exists( 'vpg_user_bookmarks' ) ? vpg_user_bookmarks( $u->ID ) : [];
    $member_since = date_i18n( 'F Y', strtotime( $u->user_registered ) );

    // Pending submissions by this user
    $pending = new WP_Query( [
        'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ],
        'author'         => $u->ID,
        'post_status'    => 'pending',
        'posts_per_page' => 10,
    ] );
    $published = new WP_Query( [
        'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ],
        'author'         => $u->ID,
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
?>

    <header class="vpg-page-hero" style="padding-bottom:var(--vpg-sp-5)">
        <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> <?php echo esc_html( ucfirst( $tier ) ); ?></span>
        <h1>Welcome, <em><?php echo esc_html( $u->display_name ); ?></em>.</h1>
        <p class="vpg-caps" style="margin-top:1rem">— Member since <?php echo esc_html( $member_since ); ?></p>
    </header>

    <span class="vpg-asterism vpg-asterism--mark"></span>

    <!-- Quick stats row -->
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap">
            <div class="vpg-stats">
                <div class="vpg-stat">
                    <div class="vpg-stat__num"><?php echo (int) $published->found_posts; ?></div>
                    <div class="vpg-stat__label">Published</div>
                </div>
                <div class="vpg-stat">
                    <div class="vpg-stat__num"><em><?php echo (int) $pending->found_posts; ?></em></div>
                    <div class="vpg-stat__label">In review</div>
                </div>
                <div class="vpg-stat">
                    <div class="vpg-stat__num"><?php echo count( $bookmarks ); ?></div>
                    <div class="vpg-stat__label">Bookmarks</div>
                </div>
                <div class="vpg-stat">
                    <div class="vpg-stat__num"><em><?php echo esc_html( ucfirst( $tier ) ); ?></em></div>
                    <div class="vpg-stat__label">Tier</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick actions -->
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap">
            <div class="vpg-section-head">
                <div>
                    <p class="vpg-caps">— Quick actions</p>
                    <h2>Feed the <em>matrix</em>.</h2>
                </div>
                <div class="vpg-section-head__meta"><a href="<?php echo esc_url( home_url('/members/' . $u->user_login . '/') ); ?>">Your public profile →</a></div>
            </div>

            <div class="vpg-grid vpg-grid--3">
                <a class="vpg-card" href="<?php echo esc_url( home_url('/submit/') ); ?>">
                    <span class="vpg-chip vpg-chip--loc"><span class="vpg-chip__dot"></span> Submit</span>
                    <h3 style="margin-top:.8rem">Add a location</h3>
                    <p class="vpg-card__lede">Pin a new shooting spot on the map. Coordinates auto-detected from address.</p>
                </a>
                <a class="vpg-card" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>">
                    <span class="vpg-chip vpg-chip--mag"><span class="vpg-chip__dot"></span> Magazine</span>
                    <h3 style="margin-top:.8rem">Latest issue</h3>
                    <p class="vpg-card__lede">PDF download available for your tier · forever-keepable.</p>
                </a>
                <a class="vpg-card" href="<?php echo esc_url( get_edit_user_link() ); ?>">
                    <span class="vpg-chip vpg-chip--member"><span class="vpg-chip__dot"></span> Profile</span>
                    <h3 style="margin-top:.8rem">Edit profile</h3>
                    <p class="vpg-card__lede">Bio, social links, avatar · shown on your public member page.</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Submissions in review -->
    <?php if ( $pending->have_posts() ) : ?>
    <section class="vpg-section vpg-section--surface vpg-section--tight">
        <div class="vpg-wrap">
            <div class="vpg-section-head">
                <div>
                    <p class="vpg-caps">— In editorial review</p>
                    <h2><?php echo (int) $pending->found_posts; ?> <em>pending</em></h2>
                </div>
                <div class="vpg-section-head__meta">72h turnaround</div>
            </div>
            <ul style="list-style:none;padding:0;margin:0">
                <?php while ( $pending->have_posts() ) : $pending->the_post(); ?>
                    <li style="display:grid;grid-template-columns:auto 1fr auto;gap:1rem;align-items:baseline;padding:1rem 0;border-bottom:1px solid var(--vpg-rule)">
                        <?php vpg_chip( get_post_type() ); ?>
                        <span><strong><?php the_title(); ?></strong><br><small style="color:var(--vpg-muted)"><?php echo esc_html( get_the_date( 'M j, Y · H:i' ) ); ?></small></span>
                        <span class="vpg-caps" style="color:var(--vpg-warning)">⌛ Reviewing</span>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recently published -->
    <?php if ( $published->have_posts() ) : ?>
    <section class="vpg-section vpg-section--tight">
        <div class="vpg-wrap">
            <div class="vpg-section-head">
                <div>
                    <p class="vpg-caps">— Your published work</p>
                    <h2><?php echo (int) $published->found_posts; ?> <em>live</em></h2>
                </div>
                <div class="vpg-section-head__meta"><a href="<?php echo esc_url( home_url('/members/' . $u->user_login . '/') ); ?>">All on your profile →</a></div>
            </div>
            <div class="vpg-grid vpg-grid--auto">
                <?php while ( $published->have_posts() ) : $published->the_post(); ?>
                    <article class="vpg-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php vpg_chip( get_post_type() ); ?>
                            <h3 style="margin-top:.8rem"><?php the_title(); ?></h3>
                            <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
                        </a>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="vpg-section vpg-section--tight" style="text-align:center;padding-bottom:var(--vpg-sp-10)">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="vpg-caps" style="text-decoration:underline;color:var(--vpg-muted)">→ Log out</a>
    </section>

<?php endif; ?>

</main>
<?php get_footer();
