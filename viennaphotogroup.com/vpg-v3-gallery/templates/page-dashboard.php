<?php
/** Template Name: Dashboard */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<main id="vpg-main">

<?php if ( ! is_user_logged_in() ) : ?>

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Members area', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'Members <em>only</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'Sign in to access your dashboard, submissions and member-only resources.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Reader access', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Free · no signup needed', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Not a member?', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Members only', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Please <em>log in</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="color:rgba(255,255,255,.8);margin:0 auto 32px;text-align:center"><?php esc_html_e( 'Your dashboard, submissions and member-only resources live behind the door.', 'vpg-v2' ); ?></p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log in', 'vpg-v2' ); ?> <span class="a">→</span></a>
        <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join free', 'vpg-v2' ); ?></a>
      </div>
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

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php echo esc_html( ucfirst( $tier ) ); ?></p>
          <h1 class="g-display g-phero__title"><?php printf( wp_kses_post( __( 'Welcome, <em>%s</em>.', 'vpg-v2' ) ), esc_html( $u->display_name ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php printf( esc_html__( 'Member since %s.', 'vpg-v2' ), esc_html( $member_since ) ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Tier', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( ucfirst( $tier ) ); ?></dd>
          <dt><?php esc_html_e( 'Profile', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/members/' . $u->user_login . '/' ) ); ?>"><?php esc_html_e( 'View public page', 'vpg-v2' ); ?></a></dd>
          <dt><?php esc_html_e( 'Session', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Log out', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div>
    </div>
  </section>

  <?php if ( function_exists( 'vpg_is_verified' ) && ! vpg_is_verified() ) : ?>
  <!-- Email not yet confirmed -->
  <section class="g-section--alt g-section--tight" style="border-top:2px solid var(--g-red)">
    <div class="g-wrap" style="display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;padding-top:20px;padding-bottom:20px">
      <p style="margin:0;font-weight:600"><?php esc_html_e( 'Confirm your email to unlock submissions — we sent you a link when you joined.', 'vpg-v2' ); ?></p>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
        <?php wp_nonce_field( 'vpg_resend_verify' ); ?>
        <input type="hidden" name="action" value="vpg_resend_verify">
        <button class="g-btn" type="submit"><?php esc_html_e( 'Resend the email', 'vpg-v2' ); ?></button>
      </form>
    </div>
  </section>
  <?php endif; ?>

  <!-- Quick stats row -->
  <section class="g-section g-section--tight">
    <div class="g-wrap">
      <div class="g-stats">
        <div class="g-stat">
          <div class="g-stat__n"><?php echo (int) $published->found_posts; ?></div>
          <div class="g-stat__l"><?php esc_html_e( 'Published', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-stat">
          <div class="g-stat__n"><em><?php echo (int) $pending->found_posts; ?></em></div>
          <div class="g-stat__l"><?php esc_html_e( 'In review', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-stat">
          <div class="g-stat__n"><?php echo count( $bookmarks ); ?></div>
          <div class="g-stat__l"><?php esc_html_e( 'Bookmarks', 'vpg-v2' ); ?></div>
        </div>
        <div class="g-stat">
          <div class="g-stat__n"><em><?php echo esc_html( ucfirst( $tier ) ); ?></em></div>
          <div class="g-stat__l"><?php esc_html_e( 'Tier', 'vpg-v2' ); ?></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick actions -->
  <section class="g-section g-section--tight">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Quick actions', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Feed the <em>matrix</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><a class="g-link" href="<?php echo esc_url( home_url( '/members/' . $u->user_login . '/' ) ); ?>"><?php esc_html_e( 'Your public profile', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
      </div>

      <div class="g-grid3">
        <a class="g-card" href="<?php echo esc_url( home_url( '/submit/' ) ); ?>">
          <span class="g-cat"><?php esc_html_e( 'Submit', 'vpg-v2' ); ?></span>
          <h3 class="g-card__title"><?php esc_html_e( 'Add a location', 'vpg-v2' ); ?></h3>
          <p class="g-row__lede"><?php esc_html_e( 'Pin a new shooting spot on the map. Coordinates auto-detected from address.', 'vpg-v2' ); ?></p>
        </a>
        <a class="g-card" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>">
          <span class="g-cat"><?php esc_html_e( 'Magazine', 'vpg-v2' ); ?></span>
          <h3 class="g-card__title"><?php esc_html_e( 'Latest issue', 'vpg-v2' ); ?></h3>
          <p class="g-row__lede"><?php esc_html_e( 'PDF download available for your tier · forever-keepable.', 'vpg-v2' ); ?></p>
        </a>
        <a class="g-card" href="<?php echo esc_url( get_edit_user_link() ); ?>">
          <span class="g-cat"><?php esc_html_e( 'Profile', 'vpg-v2' ); ?></span>
          <h3 class="g-card__title"><?php esc_html_e( 'Edit profile', 'vpg-v2' ); ?></h3>
          <p class="g-row__lede"><?php esc_html_e( 'Bio, social links, avatar · shown on your public member page.', 'vpg-v2' ); ?></p>
        </a>
      </div>
    </div>
  </section>

  <!-- Submissions in review -->
  <?php if ( $pending->have_posts() ) : ?>
  <section class="g-section g-section--alt g-section--tight">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'In editorial review', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo (int) $pending->found_posts; ?> <em><?php esc_html_e( 'pending', 'vpg-v2' ); ?></em></h2>
        </div>
        <div class="g-meta"><?php esc_html_e( '72h turnaround', 'vpg-v2' ); ?></div>
      </div>
      <div class="g-list">
        <?php while ( $pending->have_posts() ) : $pending->the_post(); ?>
          <div class="g-row" style="grid-template-columns:auto 1fr auto">
            <?php vpg_chip( get_post_type() ); ?>
            <div>
              <h3 class="g-row__title" style="margin:0"><?php the_title(); ?></h3>
              <div class="g-byline"><span><?php echo esc_html( get_the_date( 'M j, Y · H:i' ) ); ?></span></div>
            </div>
            <span class="g-row__when" style="color:var(--g-red)"><?php esc_html_e( '⌛ Reviewing', 'vpg-v2' ); ?></span>
          </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Recently published -->
  <?php if ( $published->have_posts() ) : ?>
  <section class="g-section g-section--tight">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Your published work', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo (int) $published->found_posts; ?> <em><?php esc_html_e( 'live', 'vpg-v2' ); ?></em></h2>
        </div>
        <div class="g-meta"><a class="g-link" href="<?php echo esc_url( home_url( '/members/' . $u->user_login . '/' ) ); ?>"><?php esc_html_e( 'All on your profile', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
      </div>
      <div class="g-grid3">
        <?php while ( $published->have_posts() ) : $published->the_post(); ?>
          <a class="g-card" href="<?php the_permalink(); ?>">
            <?php vpg_chip( get_post_type() ); ?>
            <h3 class="g-card__title"><?php the_title(); ?></h3>
            <p class="g-row__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Done for now?', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:14ch"><?php echo wp_kses_post( __( 'See you <em>soon</em>.', 'vpg-v2' ) ); ?></h2>
      <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Log out', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>

<?php endif; ?>

</main>
<?php get_footer();
