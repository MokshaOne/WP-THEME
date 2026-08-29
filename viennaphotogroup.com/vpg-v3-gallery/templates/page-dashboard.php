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
        'post_type'      => vpg_submittable_types(),
        'author'         => $u->ID,
        'post_status'    => 'pending',
        'posts_per_page' => 10,
    ] );
    $published = new WP_Query( [
        'post_type'      => vpg_submittable_types(),
        'author'         => $u->ID,
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
    $drafts = new WP_Query( [
        'post_type'      => vpg_submittable_types(),
        'author'         => $u->ID,
        'post_status'    => 'draft',
        'posts_per_page' => 10,
    ] );
?>

  <?php $rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $u->ID ) : null; ?>
  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php echo esc_html( $rank ? $rank['label'] : ucfirst( $tier ) ); ?></p>
          <h1 class="g-display g-phero__title"><?php printf( wp_kses_post( __( 'Welcome, <em>%s</em>.', 'vpg-v2' ) ), esc_html( $u->display_name ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php
            printf( esc_html__( 'Member since %s.', 'vpg-v2' ), esc_html( $member_since ) );
            if ( $rank && $rank['next'] ) {
                echo ' ' . esc_html( sprintf(
                    /* translators: 1: next rank name, 2: current milestone count, 3: needed count, 4: milestone kind (e.g. "map entries") */
                    __( 'On the way to %1$s: %2$d of %3$d %4$s.', 'vpg-v2' ),
                    $rank['next'],
                    $rank['next_have'],
                    $rank['next_need'],
                    $rank['next_goal']
                ) );
            }
          ?></p>
          <?php if ( $rank && $rank['next'] ) : ?>
            <div class="g-ladder" style="margin:22px 0 0;max-width:420px;padding:10px 16px">
              <span class="g-ladder__bar"><i style="width:<?php echo esc_attr( min( 100, round( $rank['next_have'] / max( 1, $rank['next_need'] ) * 100 ) ) ); ?>%"></i></span>
              <span class="g-ladder__next"><?php echo esc_html( $rank['next_have'] . ' / ' . $rank['next_need'] ); ?></span>
            </div>
          <?php endif; ?>
        </div>
        <?php
        $priv_line = '';
        if ( $rank && function_exists( 'vpg_rank_privileges' ) ) {
            $priv = vpg_rank_privileges( $u->ID );
            if ( $priv['edit_live'] && in_array( 'post', $priv['instant'], true ) ) {
                $priv_line = __( 'Everything publishes instantly · edit live', 'vpg-v2' );
            } elseif ( $priv['edit_live'] ) {
                $priv_line = __( 'Publishes instantly (journal reviewed) · edit live', 'vpg-v2' );
            } elseif ( $priv['instant'] ) {
                $priv_line = __( 'Map entries publish instantly', 'vpg-v2' );
            } elseif ( $rank['level'] >= 1 ) {
                $priv_line = __( 'Privileges paused — confirm your email / open reports', 'vpg-v2' );
            } else {
                $priv_line = __( 'Via the review desk — 25 locations unlock Contributor', 'vpg-v2' );
            }
        }
        ?>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Rank', 'vpg-v2' ); ?></dt><dd><?php echo esc_html( $rank ? $rank['label'] : ucfirst( $tier ) ); ?></dd>
          <?php if ( $priv_line ) : ?>
            <dt><?php esc_html_e( 'Privileges', 'vpg-v2' ); ?></dt>
            <dd><?php echo esc_html( $priv_line );
                if ( get_page_by_path( 'ranks' ) ) {
                    echo ' — <a href="' . esc_url( home_url( '/ranks/' ) ) . '">' . esc_html__( 'the ladder', 'vpg-v2' ) . '</a>';
                }
            ?></dd>
          <?php endif; ?>
          <dt><?php esc_html_e( 'Profile', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( home_url( '/members/' . $u->user_login . '/' ) ); ?>"><?php esc_html_e( 'View public page', 'vpg-v2' ); ?></a><?php
            $pviews = (int) get_user_meta( $u->ID, '_vpg_profile_views', true );
            if ( $pviews ) printf( ' · %s', esc_html( sprintf( _n( '%d view', '%d views', $pviews, 'vpg-v2' ), $pviews ) ) );
          ?></dd>
          <dt><?php esc_html_e( 'Session', 'vpg-v2' ); ?></dt><dd><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Log out', 'vpg-v2' ); ?></a></dd>
        </dl>
      </div>
    </div>
  </section>

  <?php // 0346 · today, one year ago — your own memory
  $memory = get_posts( [
      'post_type'      => vpg_submittable_types(),
      'post_status'    => 'publish',
      'author'         => $u->ID,
      'posts_per_page' => 1,
      'date_query'     => [ [
          'after'  => gmdate( 'Y-m-d', strtotime( '-1 year -14 days' ) ),
          'before' => gmdate( 'Y-m-d', strtotime( '-1 year +14 days' ) ),
      ] ],
  ] );
  if ( $memory ) : ?>
  <section class="g-section--alt g-section--tight">
    <div class="g-wrap" style="padding-top:16px;padding-bottom:16px;font-size:13px;font-weight:600;color:var(--g-mid)">
      ◷ <?php printf( wp_kses_post( __( 'Around this time last year you published <a href="%1$s">%2$s</a>.', 'vpg-v2' ) ),
          esc_url( get_permalink( $memory[0] ) ), esc_html( $memory[0]->post_title ) ); ?>
    </div>
  </section>
  <?php endif; ?>

  <?php // Cluster 10 · promotion / comeback / anniversary notices
  if ( function_exists( 'vpg_recognition_notices' ) ) vpg_recognition_notices(); ?>

  <!-- Section jump nav · the dashboard is long, the way around it short -->
  <nav class="g-secnav" aria-label="<?php esc_attr_e( 'Dashboard sections', 'vpg-v2' ); ?>">
    <div class="g-wrap">
      <a href="#work"><?php esc_html_e( 'Your work', 'vpg-v2' ); ?></a>
      <a href="#wall"><?php esc_html_e( 'The wall', 'vpg-v2' ); ?></a>
      <a href="#potw"><?php esc_html_e( 'Vote', 'vpg-v2' ); ?></a>
      <a href="#interview"><?php esc_html_e( 'Interview', 'vpg-v2' ); ?></a>
      <a href="#profile"><?php esc_html_e( 'Profile', 'vpg-v2' ); ?></a>
      <a href="#bookmarks"><?php esc_html_e( 'Saved', 'vpg-v2' ); ?></a>
    </div>
  </nav>

  <?php
  // Onboarding checklist · shown until every step is done
  $ob_verified  = ! function_exists( 'vpg_is_verified' ) || vpg_is_verified();
  $ob_bio       = (bool) $u->description;
  $ob_submitted = ( $pending->found_posts + $published->found_posts ) > 0;
  $ob_bookmark  = ! empty( $bookmarks );
  $ob_steps     = [
      [ $ob_verified,  __( 'Confirm your email', 'vpg-v2' ),        home_url( '/dashboard/' ) ],
      [ $ob_bio,       __( 'Write a short bio', 'vpg-v2' ),         '#profile' ],
      [ $ob_submitted, __( 'Submit your first piece', 'vpg-v2' ),   home_url( '/submit/' ) ],
      [ $ob_bookmark,  __( 'Save an article for later', 'vpg-v2' ), home_url( '/' ) ],
  ];
  $ob_done = count( array_filter( array_column( $ob_steps, 0 ) ) );
  if ( $ob_done < count( $ob_steps ) ) :
  ?>
  <section class="g-section g-section--tight" style="padding-bottom:0">
    <div class="g-wrap">
      <div style="border:1px solid var(--g-ink);padding:22px 26px">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:16px;flex-wrap:wrap;margin-bottom:14px">
          <span class="g-kicker"><?php esc_html_e( 'Getting started', 'vpg-v2' ); ?></span>
          <span class="g-meta"><?php printf( esc_html__( '%1$d of %2$d done', 'vpg-v2' ), (int) $ob_done, count( $ob_steps ) ); ?></span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px 24px">
          <?php foreach ( $ob_steps as $step ) : ?>
            <?php if ( $step[0] ) : ?>
              <span style="font-size:13px;font-weight:600;color:var(--g-mid)"><span style="color:var(--g-red)">✓</span> <s style="text-decoration-color:var(--g-line-2)"><?php echo esc_html( $step[1] ); ?></s></span>
            <?php else : ?>
              <a style="font-size:13px;font-weight:700" href="<?php echo esc_url( $step[2] ); ?>">□ <?php echo esc_html( $step[1] ); ?> →</a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

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

  <?php
  // Notification center · unread first
  $notes  = function_exists( 'vpg_get_notifications' ) ? vpg_get_notifications( $u->ID ) : [];
  $unread = count( array_filter( $notes, function ( $n ) { return empty( $n['read'] ); } ) );
  if ( $notes ) :
  ?>
  <section class="g-section g-section--tight" style="padding-bottom:0">
    <div class="g-wrap">
      <div style="border:1px solid var(--g-line-2);padding:20px 26px">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:16px;flex-wrap:wrap;margin-bottom:12px">
          <span class="g-kicker"><?php esc_html_e( 'Notifications', 'vpg-v2' ); ?><?php if ( $unread ) : ?> · <span style="color:var(--g-red)"><?php echo (int) $unread; ?> <?php esc_html_e( 'new', 'vpg-v2' ); ?></span><?php endif; ?></span>
          <?php if ( $unread ) : ?>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
            <?php wp_nonce_field( 'vpg_notifications_read' ); ?>
            <input type="hidden" name="action" value="vpg_notifications_read">
            <button type="submit" class="g-link" style="background:none;border:0;cursor:pointer"><?php esc_html_e( 'Mark all read', 'vpg-v2' ); ?></button>
          </form>
          <?php endif; ?>
        </div>
        <div style="display:grid;gap:8px">
          <?php foreach ( array_slice( $notes, 0, 6 ) as $n ) : ?>
            <div style="display:flex;gap:12px;align-items:baseline;font-size:14px;<?php echo empty( $n['read'] ) ? 'font-weight:700' : 'color:var(--g-mid)'; ?>">
              <span style="width:8px;height:8px;flex:none;background:<?php echo empty( $n['read'] ) ? 'var(--g-red)' : 'var(--g-line-2)'; ?>"></span>
              <?php if ( ! empty( $n['url'] ) ) : ?>
                <a href="<?php echo esc_url( $n['url'] ); ?>"><?php echo esc_html( $n['text'] ); ?></a>
              <?php else : ?>
                <span><?php echo esc_html( $n['text'] ); ?></span>
              <?php endif; ?>
              <span class="g-meta" style="margin-left:auto;white-space:nowrap"><?php echo esc_html( human_time_diff( (int) $n['time'] ) ); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
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

  <?php // 0138 · attendance history — walks this member RSVP'd, most recent first
  $vpg_attended = get_posts( [
      'post_type'      => 'vpg_event',
      'post_status'    => 'publish',
      'posts_per_page' => 12,
      'orderby'        => 'meta_value',
      'meta_key'       => '_vpg_event_date',
      'order'          => 'DESC',
      'meta_query'     => [ [ 'key' => '_vpg_rsvps', 'value' => '"' . $u->ID . '"', 'compare' => 'LIKE' ] ],
  ] );
  $vpg_walk_n = (int) get_user_meta( $u->ID, '_vpg_walks_attended', true );
  if ( $vpg_attended ) : ?>
  <section class="g-section g-section--tight" id="walks">
    <div class="g-wrap">
      <div class="g-head"><div>
        <span class="g-kicker"><?php esc_html_e( 'Your walks', 'vpg-v2' ); ?></span>
        <h2 class="g-head__t"><?php echo (int) max( $vpg_walk_n, count( $vpg_attended ) ); ?> <em><?php esc_html_e( 'walks', 'vpg-v2' ); ?></em></h2>
      </div></div>
      <div class="g-list">
        <?php foreach ( $vpg_attended as $ev ) : ?>
          <a class="g-row" href="<?php echo esc_url( get_permalink( $ev ) ); ?>">
            <span style="font-weight:700;color:var(--g-mid)"><?php echo esc_html( get_post_meta( $ev->ID, '_vpg_event_date', true ) ); ?></span>
            <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $ev ) ); ?></h3>
            <span class="g-row__when"><?php echo esc_html( get_post_meta( $ev->ID, '_vpg_event_venue', true ) ); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Quick actions -->
  <section class="g-section g-section--tight" id="work">
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
          <p class="g-row__lede"><?php esc_html_e( 'Pin a new shooting spot right in the form — map click, address search or photo GPS.', 'vpg-v2' ); ?></p>
        </a>
        <a class="g-card" href="<?php echo esc_url( get_post_type_archive_link( 'vpg_magazine' ) ); ?>">
          <span class="g-cat"><?php esc_html_e( 'Magazine', 'vpg-v2' ); ?></span>
          <h3 class="g-card__title"><?php esc_html_e( 'Latest issue', 'vpg-v2' ); ?></h3>
          <p class="g-row__lede"><?php esc_html_e( 'PDF download available for your tier · forever-keepable.', 'vpg-v2' ); ?></p>
        </a>
        <a class="g-card" href="#profile">
          <span class="g-cat"><?php esc_html_e( 'Profile', 'vpg-v2' ); ?></span>
          <h3 class="g-card__title"><?php esc_html_e( 'Edit profile', 'vpg-v2' ); ?></h3>
          <p class="g-row__lede"><?php esc_html_e( 'Bio, social links, avatar · shown on your public member page.', 'vpg-v2' ); ?></p>
        </a>
      </div>
    </div>
  </section>

  <!-- Empty state · nothing submitted yet -->
  <?php if ( ! $pending->have_posts() && ! $published->have_posts() ) : ?>
  <section class="g-section g-section--alt g-section--tight">
    <div class="g-wrap" style="text-align:center;padding-top:24px;padding-bottom:24px">
      <span class="g-kicker"><?php esc_html_e( 'Nothing on your wall yet', 'vpg-v2' ); ?></span>
      <h2 class="g-head__t" style="margin:14px auto 18px"><?php echo wp_kses_post( __( 'Your first frame is <em>waiting</em>.', 'vpg-v2' ) ); ?></h2>
      <p class="g-lede" style="margin:0 auto 28px;text-align:center;max-width:52ch"><?php esc_html_e( 'Submit a location, a review or a tutorial — with your photos. Editorial reviews within 72 hours, and your name goes under it.', 'vpg-v2' ); ?></p>
      <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Submit your first piece', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>
  <?php endif; ?>

  <!-- Drafts · unfinished submissions -->
  <?php if ( $drafts->have_posts() ) : ?>
  <section class="g-section g-section--tight">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Drafts', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo (int) $drafts->found_posts; ?> <em><?php esc_html_e( 'unfinished', 'vpg-v2' ); ?></em></h2>
        </div>
      </div>
      <div class="g-list">
        <?php while ( $drafts->have_posts() ) : $drafts->the_post(); ?>
          <div class="g-row" style="grid-template-columns:auto 1fr auto">
            <?php vpg_chip( get_post_type() ); ?>
            <h3 class="g-row__title" style="margin:0"><?php the_title(); ?></h3>
            <a class="g-link" href="<?php echo esc_url( add_query_arg( 'edit', get_the_ID(), home_url( '/submit/' ) ) ); ?>"><?php esc_html_e( 'Finish & submit', 'vpg-v2' ); ?> <span class="a">→</span></a>
          </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

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
              <div style="display:flex;align-items:center;gap:8px;margin-top:10px" aria-hidden="true">
                <span style="width:9px;height:9px;background:var(--g-ink);flex:none"></span>
                <span style="flex:1;max-width:60px;height:1px;background:var(--g-ink)"></span>
                <span style="width:9px;height:9px;background:var(--g-red);flex:none"></span>
                <span style="flex:1;max-width:60px;height:1px;background:var(--g-line-2)"></span>
                <span style="width:9px;height:9px;border:1px solid var(--g-line-2);flex:none"></span>
                <span class="g-meta" style="margin-left:6px"><?php esc_html_e( 'Submitted → in review → live', 'vpg-v2' ); ?></span>
              </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px">
              <span class="g-row__when" style="color:var(--g-red)"><?php esc_html_e( '⌛ Reviewing', 'vpg-v2' ); ?></span>
              <a class="g-link" href="<?php echo esc_url( add_query_arg( 'edit', get_the_ID(), home_url( '/submit/' ) ) ); ?>"><?php esc_html_e( 'Edit', 'vpg-v2' ); ?> <span class="a">→</span></a>
            </div>
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
      <?php $can_edit_live = function_exists( 'vpg_can_edit_live' ) && vpg_can_edit_live( $u->ID ); ?>
      <div class="g-grid3">
        <?php while ( $published->have_posts() ) : $published->the_post(); ?>
          <div class="g-card">
            <a href="<?php the_permalink(); ?>" style="display:block">
              <?php vpg_chip( get_post_type() ); ?>
              <h3 class="g-card__title"><?php the_title(); ?></h3>
              <p class="g-row__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
            </a>
            <?php if ( $can_edit_live ) : ?>
              <a class="g-link" style="font-size:12px;margin-top:10px" href="<?php echo esc_url( add_query_arg( 'edit', get_the_ID(), home_url( '/submit/' ) ) ); ?>"><?php esc_html_e( 'Edit live', 'vpg-v2' ); ?> <span class="a">→</span></a>
            <?php endif; ?>
          </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php
  // Activity feed · what changed since the member's last visit
  $last_seen = (int) get_user_meta( $u->ID, '_vpg_last_seen', true );
  update_user_meta( $u->ID, '_vpg_last_seen', time() );
  $since = $last_seen ?: strtotime( '-7 days' );
  $fresh = get_posts( [
      'post_type'      => [ 'post', 'vpg_location', 'vpg_event', 'vpg_magazine', 'vpg_review', 'vpg_tutorial' ],
      'post_status'    => 'publish',
      'posts_per_page' => 6,
      'date_query'     => [ [ 'after' => gmdate( 'Y-m-d H:i:s', $since ) ] ],
  ] );
  if ( $fresh ) :
  ?>
  <section class="g-section g-section--tight" id="wall">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Since your last visit', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'New on the <em>wall</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
      </div>
      <div class="g-list">
        <?php foreach ( $fresh as $fp ) : ?>
          <a class="g-row" style="grid-template-columns:auto 1fr auto" href="<?php echo esc_url( get_permalink( $fp ) ); ?>">
            <?php vpg_chip( $fp->post_type ); ?>
            <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $fp ) ); ?></h3>
            <span class="g-row__when"><?php echo esc_html( get_the_date( 'M j', $fp ) ); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php
  // Photo of the week · one vote per member per week
  $potw = function_exists( 'vpg_potw_candidates' ) ? vpg_potw_candidates( 8 ) : [];
  if ( $potw ) :
      $week_key  = vpg_potw_week_key();
      $voted_for = (int) get_user_meta( $u->ID, '_' . $week_key, true );
      $votes_map = get_option( $week_key, [] );
  ?>
  <section class="g-section g-section--alt g-section--tight" id="potw">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Photo of the week', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Pick <em>one</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php echo $voted_for ? esc_html__( 'You voted this week', 'vpg-v2' ) : esc_html__( 'One vote per week', 'vpg-v2' ); ?></div>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px" id="vpg-potw">
        <?php foreach ( $potw as $ph ) :
            $thumb = wp_get_attachment_image_url( $ph->ID, 'medium' );
            if ( ! $thumb ) continue;
            $n = (int) ( $votes_map[ $ph->ID ] ?? 0 );
        ?>
        <figure style="margin:0">
          <div style="aspect-ratio:1;overflow:hidden;background:var(--g-bg-2)"><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover"></div>
          <figcaption style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
            <span class="g-meta"><?php echo esc_html( get_the_author_meta( 'display_name', $ph->post_author ) ); ?></span>
            <?php if ( $voted_for === (int) $ph->ID ) : ?>
              <span style="font-size:11px;font-weight:800;color:var(--g-red)">★ <span data-votes><?php echo $n; ?></span></span>
            <?php elseif ( $voted_for ) : ?>
              <span class="g-meta" data-votes><?php echo $n; ?></span>
            <?php else : ?>
              <button type="button" class="g-link" data-potw="<?php echo (int) $ph->ID; ?>" style="background:none;border:0;cursor:pointer;font-size:11px"><?php esc_html_e( 'Vote', 'vpg-v2' ); ?> (<span data-votes><?php echo $n; ?></span>)</button>
            <?php endif; ?>
          </figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
      <script>
      (function () {
          var grid = document.getElementById('vpg-potw');
          if (!grid) return;
          grid.addEventListener('click', function (e) {
              var btn = e.target.closest('[data-potw]');
              if (!btn) return;
              var fd = new FormData();
              fd.append('action', 'vpg_potw_vote');
              fd.append('_ajax_nonce', '<?php echo esc_js( wp_create_nonce( 'vpg_potw' ) ); ?>');
              fd.append('photo', btn.getAttribute('data-potw'));
              fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', { method: 'POST', credentials: 'same-origin', body: fd })
                  .then(function (r) { return r.json(); })
                  .then(function (res) {
                      if (res && res.success) {
                          btn.outerHTML = '<span style="font-size:11px;font-weight:800;color:var(--g-red)">★ ' + res.data.votes + '</span>';
                          grid.querySelectorAll('[data-potw]').forEach(function (b) { b.disabled = true; b.style.opacity = .4; });
                      }
                  }).catch(function () {});
          });
      }());
      </script>
    </div>
  </section>
  <?php endif; ?>

  <!-- Interview · answers feed the magazine's featured-artist section -->
  <?php
  $iv_questions = function_exists( 'vpg_interview_questions' ) ? vpg_interview_questions() : [];
  $iv_answers   = get_user_meta( $u->ID, '_vpg_interview', true );
  $iv_answers   = is_array( $iv_answers ) ? $iv_answers : [];
  $iv_invited   = (int) get_user_meta( $u->ID, '_vpg_interview_invited', true );
  $iv_done      = function_exists( 'vpg_get_interview' ) ? count( vpg_get_interview( $u->ID ) ) : 0;
  if ( $iv_questions ) :
  ?>
  <section class="g-section g-section--tight" id="interview">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Your interview', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'In your own <em>words</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php
          echo esc_html( sprintf( __( '%1$d of %2$d answered', 'vpg-v2' ), $iv_done, count( $iv_questions ) ) );
        ?></div>
      </div>

      <?php if ( $iv_invited && ! $iv_done ) : ?>
        <p style="border-left:3px solid var(--g-red);padding:10px 16px;background:var(--g-bg-2);font-weight:600"><?php esc_html_e( 'Editorial wants to feature you as an upcoming featured artist — your answers below become the interview in the magazine.', 'vpg-v2' ); ?></p>
      <?php else : ?>
        <p class="g-lede" style="font-size:16px;color:var(--g-mid);max-width:60ch"><?php esc_html_e( 'When editorial features you in an issue, these answers become your interview. Answer what you like, skip what you don’t — short and honest beats polished.', 'vpg-v2' ); ?></p>
      <?php endif; ?>

      <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:none;margin-top:22px">
        <input type="hidden" name="action" value="vpg_save_interview">
        <?php wp_nonce_field( 'vpg_save_interview' ); ?>
        <?php foreach ( $iv_questions as $iv_i => $iv_q ) : ?>
          <div class="g-field">
            <label class="g-label" for="iv-q<?php echo (int) $iv_i; ?>"><?php echo esc_html( $iv_q ); ?></label>
            <textarea class="g-textarea" id="iv-q<?php echo (int) $iv_i; ?>" name="interview[<?php echo (int) $iv_i; ?>]" rows="3"><?php echo esc_textarea( $iv_answers[ $iv_i ] ?? '' ); ?></textarea>
          </div>
        <?php endforeach; ?>
        <div><button class="g-btn" type="submit"><?php esc_html_e( 'Save interview', 'vpg-v2' ); ?></button></div>
      </form>
    </div>
  </section>
  <?php endif; ?>

  <?php // 0401 · found a project room — Documentarian and up
  if ( $rank && $rank['level'] >= 2 ) : ?>
  <section class="g-section g-section--tight" style="padding-bottom:0">
    <div class="g-wrap">
      <div style="border:1px solid var(--g-line-2);padding:18px 24px">
        <span class="g-kicker"><?php esc_html_e( 'Project rooms', 'vpg-v2' ); ?></span>
        <p style="margin:6px 0 12px;font-size:13px;color:var(--g-mid)"><?php esc_html_e( 'Found a shared series — members join, hang their works, and the best rooms become magazine features.', 'vpg-v2' ); ?></p>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:10px;flex-wrap:wrap">
          <?php wp_nonce_field( 'vpg_project_create' ); ?>
          <input type="hidden" name="action" value="vpg_project_create">
          <input class="g-input" type="text" name="title" required placeholder="<?php esc_attr_e( 'Project title — e.g. “Wien bei Regen”', 'vpg-v2' ); ?>" style="flex:2;min-width:220px">
          <input class="g-input" type="text" name="about" placeholder="<?php esc_attr_e( 'One line on the idea', 'vpg-v2' ); ?>" style="flex:3;min-width:260px">
          <label style="display:flex;gap:8px;align-items:center;font-size:12px;font-weight:700"><input type="checkbox" name="circle" value="1"> <?php esc_html_e( 'Critique circle (max. 6)', 'vpg-v2' ); ?></label>
          <button class="g-btn" type="submit"><?php esc_html_e( 'Open the room', 'vpg-v2' ); ?></button>
        </form>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- 0779/0780/0607 · Security & signals -->
  <section class="g-section g-section--tight" id="security" style="padding-bottom:0">
    <div class="g-wrap">
      <div style="border:1px solid var(--g-line-2);padding:18px 24px;display:grid;gap:20px">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Account security', 'vpg-v2' ); ?></span>
          <?php $totp_on = get_user_meta( $u->ID, '_vpg_totp_on', true ) === '1';
                $totp_pending = get_user_meta( $u->ID, '_vpg_totp_pending', true ); ?>
          <?php if ( $totp_on ) : ?>
            <p style="margin:6px 0 10px;font-size:13px"><strong style="color:var(--g-red)">●</strong> <?php esc_html_e( 'Two-factor is on — codes from your authenticator app protect every login.', 'vpg-v2' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:10px;flex-wrap:wrap">
              <?php wp_nonce_field( 'vpg_totp' ); ?>
              <input type="hidden" name="action" value="vpg_totp_disable">
              <input class="g-input" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="000000" style="max-width:120px">
              <button class="g-btn g-btn--ghost" type="submit"><?php esc_html_e( 'Turn 2FA off', 'vpg-v2' ); ?></button>
            </form>
            <?php // 1017 · recovery codes
            $bk_show = get_transient( 'vpg_backup_show_' . $u->ID );
            $bk_left = function_exists( 'vpg_backup_codes_left' ) ? vpg_backup_codes_left( $u->ID ) : 0;
            if ( $bk_show ) : delete_transient( 'vpg_backup_show_' . $u->ID ); ?>
              <div style="border:1px solid var(--g-red);padding:12px 16px;margin-top:12px">
                <p style="margin:0 0 8px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--g-red)"><?php esc_html_e( 'Save these now — shown once', 'vpg-v2' ); ?></p>
                <div style="font-family:ui-monospace,monospace;font-size:15px;line-height:1.9;letter-spacing:.06em">
                  <?php foreach ( $bk_show as $bc ) echo esc_html( $bc ) . '<br>'; ?>
                </div>
                <p style="margin:8px 0 0;font-size:12px;color:var(--g-mid)"><?php esc_html_e( 'Each works once, in place of an app code, if you lose your phone.', 'vpg-v2' ); ?></p>
              </div>
            <?php else : ?>
              <p style="margin:10px 0 0;font-size:12px;color:var(--g-mid)"><?php printf( esc_html( _n( '%d backup code left.', '%d backup codes left.', (int) $bk_left, 'vpg-v2' ) ), (int) $bk_left ); ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                  <?php wp_nonce_field( 'vpg_totp' ); ?>
                  <input type="hidden" name="action" value="vpg_totp_backup">
                  <button type="submit" style="background:none;border:0;padding:0;cursor:pointer;color:var(--g-red);font-weight:700;font-size:12px"><?php echo $bk_left ? esc_html__( '· regenerate', 'vpg-v2' ) : esc_html__( '· generate backup codes', 'vpg-v2' ); ?></button>
                </form>
              </p>
            <?php endif; ?>
          <?php elseif ( $totp_pending ) : ?>
            <p style="margin:6px 0 10px;font-size:13px"><?php esc_html_e( 'Add this secret to your authenticator app (or open the link on your phone), then confirm with one code:', 'vpg-v2' ); ?></p>
            <p style="font-family:ui-monospace,monospace;font-size:15px;letter-spacing:.12em;border:1px dashed var(--g-line);display:inline-block;padding:8px 14px"><?php echo esc_html( trim( chunk_split( $totp_pending, 4, ' ' ) ) ); ?></p>
            <p style="margin:4px 0 10px"><a class="g-link" style="font-size:12px" href="<?php echo esc_url( 'otpauth://totp/' . rawurlencode( 'VPG:' . $u->user_login ) . '?secret=' . $totp_pending . '&issuer=' . rawurlencode( get_bloginfo( 'name' ) ) ); ?>"><?php esc_html_e( 'Open in authenticator app →', 'vpg-v2' ); ?></a></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:10px;flex-wrap:wrap">
              <?php wp_nonce_field( 'vpg_totp' ); ?>
              <input type="hidden" name="action" value="vpg_totp_confirm">
              <input class="g-input" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="000000" style="max-width:120px" required>
              <button class="g-btn g-btn--red" type="submit"><?php esc_html_e( 'Confirm & arm 2FA', 'vpg-v2' ); ?></button>
            </form>
          <?php else : ?>
            <p style="margin:6px 0 10px;font-size:13px;color:var(--g-mid)"><?php esc_html_e( 'Two-factor auth: a six-digit code from your phone on every login. Editors: this is expected of you.', 'vpg-v2' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
              <?php wp_nonce_field( 'vpg_totp' ); ?>
              <input type="hidden" name="action" value="vpg_totp_setup">
              <button class="g-btn" type="submit"><?php esc_html_e( 'Set up 2FA', 'vpg-v2' ); ?></button>
            </form>
          <?php endif; ?>
        </div>

        <div style="border-top:1px solid var(--g-line);padding-top:16px">
          <span class="g-kicker"><?php esc_html_e( 'Passkeys', 'vpg-v2' ); ?></span>
          <p style="margin:6px 0 10px;font-size:13px;color:var(--g-mid)"><?php esc_html_e( 'Sign in with Face ID, fingerprint or your device PIN — no password typed, nothing to phish.', 'vpg-v2' ); ?></p>
          <?php $pks = function_exists( 'vpg_passkeys' ) ? vpg_passkeys( $u->ID ) : []; ?>
          <?php foreach ( $pks as $pk ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-flex;gap:8px;align-items:center;margin:0 14px 8px 0">
              <?php wp_nonce_field( 'vpg_passkey_remove' ); ?>
              <input type="hidden" name="action" value="vpg_pk_remove">
              <input type="hidden" name="key_id" value="<?php echo esc_attr( $pk['id'] ); ?>">
              <span style="font-size:12px;font-weight:700">🔑 <?php echo esc_html( $pk['label'] ); ?></span>
              <button type="submit" style="background:none;border:0;padding:0;cursor:pointer;color:var(--g-mid);font-size:11px">✕</button>
            </form>
          <?php endforeach; ?>
          <p style="margin:4px 0 0"><button class="g-btn g-btn--ghost" type="button" id="vpg-pk-add"><?php esc_html_e( '+ Add a passkey', 'vpg-v2' ); ?></button>
          <span id="vpg-pk-add-msg" class="g-meta"></span></p>
          <script>
          (function () {
            var btn = document.getElementById('vpg-pk-add'), msg = document.getElementById('vpg-pk-add-msg');
            if (!window.PublicKeyCredential) { btn.hidden = true; return; }
            function b2b(s) { s = s.replace(/-/g, '+').replace(/_/g, '/'); var b = atob(s), a = new Uint8Array(b.length); for (var i = 0; i < b.length; i++) a[i] = b.charCodeAt(i); return a.buffer; }
            function b2u(b) { var a = new Uint8Array(b), s = ''; for (var i = 0; i < a.length; i++) s += String.fromCharCode(a[i]); return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''); }
            var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, nonce = <?php echo wp_json_encode( wp_create_nonce( 'vpg_passkey' ) ); ?>;
            btn.addEventListener('click', function () {
              msg.textContent = '…';
              fetch(ajax + '?action=vpg_pk_reg_options&_ajax_nonce=' + nonce).then(function (r) { return r.json(); }).then(function (res) {
                var o = res.data;
                o.challenge = b2b(o.challenge);
                o.user.id = b2b(o.user.id);
                o.excludeCredentials = (o.excludeCredentials || []).map(function (c) { return { type: c.type, id: b2b(c.id) }; });
                return navigator.credentials.create({ publicKey: o });
              }).then(function (cred) {
                var body = new URLSearchParams();
                body.set('action', 'vpg_pk_reg_verify'); body.set('_ajax_nonce', nonce);
                body.set('clientDataJSON', b2u(cred.response.clientDataJSON));
                body.set('attestationObject', b2u(cred.response.attestationObject));
                body.set('label', navigator.platform || 'Device');
                return fetch(ajax, { method: 'POST', body: body, credentials: 'same-origin' });
              }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success) location.reload();
                else msg.textContent = (res && res.data) || <?php echo wp_json_encode( __( 'Could not save the passkey.', 'vpg-v2' ) ); ?>;
              }).catch(function () { msg.textContent = <?php echo wp_json_encode( __( 'Cancelled.', 'vpg-v2' ) ); ?>; });
            });
          })();
          </script>
        </div>

        <div style="border-top:1px solid var(--g-line);padding-top:16px">
          <span class="g-kicker"><?php esc_html_e( 'Push notifications', 'vpg-v2' ); ?></span>
          <p style="margin:6px 0 10px;font-size:13px;color:var(--g-mid)"><?php esc_html_e( 'Event reminders and replies on your lockscreen — opt-in, this device only, off with one tap.', 'vpg-v2' ); ?></p>
          <p style="margin:0"><button class="g-btn g-btn--ghost" type="button" id="vpg-push-btn"><?php esc_html_e( 'Enable on this device', 'vpg-v2' ); ?></button>
          <span id="vpg-push-msg" class="g-meta"></span></p>
          <script>
          (function () {
            var btn = document.getElementById('vpg-push-btn'), msg = document.getElementById('vpg-push-msg');
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) { btn.hidden = true; return; }
            var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
                nonce = <?php echo wp_json_encode( wp_create_nonce( 'vpg_push' ) ); ?>,
                vapid = <?php echo wp_json_encode( function_exists( 'vpg_vapid_keys' ) ? ( vpg_vapid_keys()['pub'] ?? '' ) : '' ); ?>;
            function keyBuf(s) { s = s.replace(/-/g, '+').replace(/_/g, '/'); var b = atob(s), a = new Uint8Array(b.length); for (var i = 0; i < b.length; i++) a[i] = b.charCodeAt(i); return a; }
            btn.addEventListener('click', function () {
              msg.textContent = '…';
              Notification.requestPermission().then(function (perm) {
                if (perm !== 'granted') { msg.textContent = <?php echo wp_json_encode( __( 'Blocked by the browser.', 'vpg-v2' ) ); ?>; return; }
                navigator.serviceWorker.ready.then(function (reg) {
                  return reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBuf(vapid) });
                }).then(function (sub) {
                  var body = new URLSearchParams();
                  body.set('action', 'vpg_push_subscribe'); body.set('_ajax_nonce', nonce);
                  body.set('sub', JSON.stringify(sub));
                  return fetch(ajax, { method: 'POST', body: body, credentials: 'same-origin' });
                }).then(function (r) { return r.json(); }).then(function (res) {
                  msg.textContent = res && res.success ? <?php echo wp_json_encode( __( '✓ On — this device now hears the collective.', 'vpg-v2' ) ); ?> : <?php echo wp_json_encode( __( 'Failed to register.', 'vpg-v2' ) ); ?>;
                }).catch(function () { msg.textContent = <?php echo wp_json_encode( __( 'Failed to register.', 'vpg-v2' ) ); ?>; });
              });
            });
          })();
          </script>
          <?php // 1026 · send a test push; 1018 · per-kind + quiet hours
          $pp = function_exists( 'vpg_push_prefs' ) ? vpg_push_prefs( $u->ID ) : [];
          $pp_off = (array) ( $pp['off'] ?? [] ); ?>
          <p style="margin:10px 0 0"><button class="g-btn g-btn--ghost" type="button" id="vpg-push-test"><?php esc_html_e( 'Send a test push', 'vpg-v2' ); ?></button>
          <span id="vpg-push-test-msg" class="g-meta"></span></p>
          <script>
          (function () {
            var b = document.getElementById('vpg-push-test'), m = document.getElementById('vpg-push-test-msg');
            var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, nonce = <?php echo wp_json_encode( wp_create_nonce( 'vpg_push' ) ); ?>;
            b.addEventListener('click', function () {
              m.textContent = '…';
              var body = new URLSearchParams(); body.set('action', 'vpg_push_test'); body.set('_ajax_nonce', nonce);
              fetch(ajax, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
                m.textContent = res && res.success ? <?php echo wp_json_encode( __( 'Sent — check your device.', 'vpg-v2' ) ); ?> : <?php echo wp_json_encode( __( 'Enable push on this device first.', 'vpg-v2' ) ); ?>;
              });
            });
          })();
          </script>
          <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:14px 0 0;display:grid;gap:6px">
            <?php wp_nonce_field( 'vpg_push_prefs' ); ?>
            <input type="hidden" name="action" value="vpg_push_prefs">
            <span class="g-meta" style="font-size:11px"><?php esc_html_e( 'Silence by kind:', 'vpg-v2' ); ?></span>
            <?php foreach ( [ 'event' => __( 'Event reminders', 'vpg-v2' ), 'review' => __( 'Submission verdicts', 'vpg-v2' ), 'circle' => __( 'Critique circles', 'vpg-v2' ), 'general' => __( 'Everything else', 'vpg-v2' ) ] as $k => $lbl ) : ?>
              <label style="font-size:12px;display:flex;gap:8px;align-items:center"><input type="checkbox" name="off[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $pp_off, true ) ); ?>> <?php echo esc_html( $lbl ); ?></label>
            <?php endforeach; ?>
            <label style="font-size:12px;display:flex;gap:8px;align-items:center;margin-top:4px"><input type="checkbox" name="quiet" value="1" <?php checked( ! empty( $pp['quiet'] ) ); ?>> <?php esc_html_e( 'Quiet hours · nothing 22:00–07:00', 'vpg-v2' ); ?></label>
            <p style="margin:6px 0 0"><button class="g-btn g-btn--ghost" type="submit"><?php esc_html_e( 'Save push preferences', 'vpg-v2' ); ?></button></p>
          </form>
        </div>

        <div style="border-top:1px solid var(--g-line);padding-top:16px">
          <span class="g-kicker"><?php esc_html_e( 'Your year', 'vpg-v2' ); ?></span>
          <p style="margin:6px 0 0;font-size:13px"><a class="g-link" href="<?php echo esc_url( home_url( '/year/' ) ); ?>"><?php esc_html_e( 'Open your personal year in review →', 'vpg-v2' ); ?></a></p>
        </div>
      </div>
    </div>
  </section>

  <!-- 0435 · Idea box · anonymous by design -->
  <section class="g-section--alt g-section--tight">
    <div class="g-wrap" style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;padding-top:22px;padding-bottom:22px">
      <div style="flex:1;min-width:240px">
        <span class="g-kicker"><?php esc_html_e( 'Idea box', 'vpg-v2' ); ?></span>
        <p style="margin:6px 0 0;font-size:13px;color:var(--g-mid)"><?php esc_html_e( 'Drop a thought — it reaches editorial with no name attached. Anonymity is the point.', 'vpg-v2' ); ?></p>
      </div>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="flex:2;min-width:280px;display:flex;gap:10px">
        <?php wp_nonce_field( 'vpg_idea_box' ); ?>
        <input type="hidden" name="action" value="vpg_idea_box">
        <input class="g-input" type="text" name="idea" required placeholder="<?php esc_attr_e( 'What should the collective try?', 'vpg-v2' ); ?>" style="flex:1">
        <button class="g-btn" type="submit"><?php esc_html_e( 'Drop it in', 'vpg-v2' ); ?></button>
      </form>
    </div>
  </section>

  <?php // 0573 · saved searches · watchlist with remove links
  $saved_s = array_filter( (array) get_user_meta( $u->ID, '_vpg_saved_searches', true ), 'is_array' );
  if ( $saved_s ) : ?>
  <section class="g-section g-section--tight" style="padding-bottom:0">
    <div class="g-wrap">
      <div style="border:1px solid var(--g-line-2);padding:18px 24px">
        <span class="g-kicker"><?php esc_html_e( 'Saved searches', 'vpg-v2' ); ?></span>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
          <?php foreach ( $saved_s as $ss ) : ?>
            <span style="display:inline-flex;gap:8px;align-items:center;border:1px solid var(--g-line);padding:6px 12px;font-size:13px;font-weight:600">
              <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $ss['term'] ) ) ); ?>"><?php echo esc_html( $ss['term'] ); ?></a>
              <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_drop_search&term=' . rawurlencode( $ss['term'] ) ), 'vpg_drop_search' ) ); ?>" style="color:var(--g-red);font-weight:800" aria-label="<?php esc_attr_e( 'Remove', 'vpg-v2' ); ?>">×</a>
            </span>
          <?php endforeach; ?>
        </div>
        <p class="g-form__note" style="margin-top:8px"><?php esc_html_e( 'You get a notification when something new matches.', 'vpg-v2' ); ?></p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Profile editor · everything self-service, no wp-admin -->
  <section class="g-section g-section--alt g-section--tight" id="profile">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Your profile', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'How you appear on the <em>wall</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><a class="g-link" href="<?php echo esc_url( home_url( '/members/' . $u->user_login . '/' ) ); ?>"><?php esc_html_e( 'Preview', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
      </div>

      <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width:none">
        <?php wp_nonce_field( 'vpg_save_profile' ); ?>
        <input type="hidden" name="action" value="vpg_save_profile">

        <div style="display:grid;grid-template-columns:96px 1fr;gap:24px;align-items:start;margin-bottom:18px">
          <div>
            <?php echo get_avatar( $u->ID, 96, '', '', [ 'style' => 'display:block;width:96px;height:96px;object-fit:cover' ] ); ?>
          </div>
          <div class="g-field" style="margin:0">
            <label for="pf-avatar"><?php esc_html_e( 'Avatar · JPG/PNG/WebP, max 4 MB', 'vpg-v2' ); ?></label>
            <input class="g-input" id="pf-avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <p class="g-form__note" style="margin-top:6px"><?php esc_html_e( 'Stored on this site — no Gravatar, no third parties.', 'vpg-v2' ); ?></p>
          </div>
        </div>

        <div class="g-field--row">
          <div class="g-field">
            <label for="pf-name"><?php esc_html_e( 'Display name', 'vpg-v2' ); ?></label>
            <input class="g-input" id="pf-name" type="text" name="display_name" required value="<?php echo esc_attr( $u->display_name ); ?>">
          </div>
          <div class="g-field">
            <label for="pf-website"><?php esc_html_e( 'Website', 'vpg-v2' ); ?></label>
            <input class="g-input" id="pf-website" type="url" name="website" inputmode="url" value="<?php echo esc_attr( $u->user_url ); ?>" placeholder="https://">
          </div>
        </div>

        <div class="g-field--row">
          <div class="g-field">
            <label for="pf-insta"><?php esc_html_e( 'Instagram', 'vpg-v2' ); ?></label>
            <input class="g-input" id="pf-insta" type="text" name="instagram" value="<?php echo esc_attr( get_user_meta( $u->ID, '_vpg_instagram', true ) ); ?>" placeholder="@handle">
          </div>
          <div class="g-field">
            <label><?php esc_html_e( 'Email', 'vpg-v2' ); ?></label>
            <input class="g-input" type="email" value="<?php echo esc_attr( $u->user_email ); ?>" disabled>
          </div>
        </div>

        <div class="g-field">
          <label for="pf-bio"><?php esc_html_e( 'Bio · shown on your public page', 'vpg-v2' ); ?></label>
          <textarea class="g-textarea" id="pf-bio" name="bio" rows="4" placeholder="<?php esc_attr_e( 'What you photograph, and how you look at the city.', 'vpg-v2' ); ?>"><?php echo esc_textarea( $u->description ); ?></textarea>
        </div>

        <div class="g-field" style="display:grid;gap:10px">
          <label><?php esc_html_e( 'Emails & visibility', 'vpg-v2' ); ?></label>
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:500">
            <input type="checkbox" name="pref_feedback" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_pref_feedback', true ) !== '0' ); ?>>
            <?php esc_html_e( 'Email me when my submissions are approved or get feedback', 'vpg-v2' ); ?>
          </label>
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:500">
            <input type="checkbox" name="pref_digest" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_pref_digest', true ) !== '0' ); ?>>
            <?php esc_html_e( 'Send me the member digest when an issue ships', 'vpg-v2' ); ?>
          </label>
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:600">
            <input type="checkbox" name="pref_coffee" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_pref_coffee', true ) === '1' ); ?>>
            <?php esc_html_e( 'Random coffee ☕ · pair me with another member once a month', 'vpg-v2' ); ?>
          </label>
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:600">
            <input type="checkbox" name="pref_event" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_pref_event', true ) !== '0' ); ?>>
            <?php esc_html_e( 'Event reminders · mail me the day before a walk I RSVP’d to', 'vpg-v2' ); ?>
          </label>
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:500">
            <input type="checkbox" name="directory_optin" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_directory_optin', true ) === '1' ); ?>>
            <?php esc_html_e( 'List me in the public members directory', 'vpg-v2' ); ?>
          </label>
        </div>

        <div class="g-field">
          <label for="pf-buddy"><?php esc_html_e( 'Photowalk buddies · optional', 'vpg-v2' ); ?></label>
          <label class="g-label" for="pf-theme" style="font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--g-mid)"><?php esc_html_e( 'Profile look', 'vpg-v2' ); ?></label>
          <select class="g-select" id="pf-theme" name="profile_theme" style="margin-bottom:14px">
            <option value="" <?php selected( get_user_meta( $u->ID, '_vpg_profile_theme', true ), '' ); ?>><?php esc_html_e( 'Gallery white (default)', 'vpg-v2' ); ?></option>
            <option value="dark" <?php selected( get_user_meta( $u->ID, '_vpg_profile_theme', true ), 'dark' ); ?>><?php esc_html_e( 'Darkroom black', 'vpg-v2' ); ?></option>
            <option value="paper" <?php selected( get_user_meta( $u->ID, '_vpg_profile_theme', true ), 'paper' ); ?>><?php esc_html_e( 'Soft paper', 'vpg-v2' ); ?></option>
          </select>
          <select class="g-select" id="pf-buddy" name="buddy_role">
            <?php $buddy = get_user_meta( $u->ID, '_vpg_buddy_role', true ) ?: 'off'; ?>
            <option value="off" <?php selected( $buddy, 'off' ); ?>><?php esc_html_e( 'Not now', 'vpg-v2' ); ?></option>
            <option value="mentor" <?php selected( $buddy, 'mentor' ); ?>><?php esc_html_e( 'I\'ll show newcomers around', 'vpg-v2' ); ?></option>
            <option value="looking" <?php selected( $buddy, 'looking' ); ?>><?php esc_html_e( 'I\'d like someone to walk with', 'vpg-v2' ); ?></option>
          </select>
          <p class="g-form__note" style="margin-top:6px"><?php esc_html_e( 'Shown to logged-in members on the directory page, nowhere else.', 'vpg-v2' ); ?></p>
        </div>

        <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Save profile', 'vpg-v2' ); ?> <span class="a">→</span></button>
      </form>

      <!-- Portfolio curator · pick and order the frames on your public wall -->
      <?php
      $my_photos = get_posts( [
          'post_type'      => 'attachment',
          'post_status'    => 'inherit',
          'post_mime_type' => 'image',
          'author'         => $u->ID,
          'posts_per_page' => 36,
          'orderby'        => 'date',
          'order'          => 'DESC',
      ] );
      $curated = function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $u->ID ) : [];
      if ( $my_photos ) :
      ?>
      <div style="margin-top:36px;border-top:1px solid var(--g-line);padding-top:26px">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:16px;flex-wrap:wrap;margin-bottom:6px">
          <span class="g-kicker"><?php esc_html_e( 'Your portfolio wall', 'vpg-v2' ); ?></span>
          <span class="g-meta"><?php esc_html_e( 'Click to hang · click again to take down · order = click order · max 24', 'vpg-v2' ); ?></span>
        </div>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
          <?php wp_nonce_field( 'vpg_save_portfolio' ); ?>
          <input type="hidden" name="action" value="vpg_save_portfolio">
          <p style="margin:0 0 14px"><a class="g-link" style="font-size:12px" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_portfolio_pdf' ), 'vpg_portfolio_pdf' ) ); ?>">⎙ <?php esc_html_e( 'Export portfolio as PDF', 'vpg-v2' ); ?></a></p>
          <input type="hidden" name="portfolio" id="vpg-portfolio-order" value="<?php echo esc_attr( implode( ',', $curated ) ); ?>">
          <div id="vpg-portfolio-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin:14px 0 18px">
            <?php foreach ( $my_photos as $ph ) :
                $thumb = wp_get_attachment_image_url( $ph->ID, 'thumbnail' );
                if ( ! $thumb ) continue;
                $pos = array_search( $ph->ID, $curated, true );
            ?>
            <button type="button" data-pid="<?php echo (int) $ph->ID; ?>" style="position:relative;aspect-ratio:1;border:2px solid <?php echo $pos !== false ? 'var(--g-red)' : 'var(--g-line)'; ?>;background:none;padding:0;cursor:pointer">
              <img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block">
              <span data-badge style="position:absolute;top:4px;left:4px;background:var(--g-red);color:#fff;font-size:11px;font-weight:800;min-width:20px;height:20px;display:<?php echo $pos !== false ? 'flex' : 'none'; ?>;align-items:center;justify-content:center"><?php echo $pos !== false ? (int) $pos + 1 : ''; ?></span>
            </button>
            <?php endforeach; ?>
          </div>
          <button class="g-btn g-btn--red" type="submit"><?php esc_html_e( 'Save portfolio', 'vpg-v2' ); ?> <span class="a">→</span></button>
        </form>
        <script>
        (function () {
            var grid  = document.getElementById('vpg-portfolio-grid');
            var input = document.getElementById('vpg-portfolio-order');
            if (!grid || !input) return;
            var order = input.value ? input.value.split(',').filter(Boolean) : [];
            function paint() {
                grid.querySelectorAll('[data-pid]').forEach(function (btn) {
                    var idx = order.indexOf(btn.getAttribute('data-pid'));
                    var badge = btn.querySelector('[data-badge]');
                    btn.style.borderColor = idx !== -1 ? 'var(--g-red)' : 'var(--g-line)';
                    badge.style.display = idx !== -1 ? 'flex' : 'none';
                    badge.textContent = idx !== -1 ? (idx + 1) : '';
                });
                input.value = order.join(',');
            }
            grid.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-pid]');
                if (!btn) return;
                var pid = btn.getAttribute('data-pid');
                var idx = order.indexOf(pid);
                if (idx !== -1) order.splice(idx, 1);
                else if (order.length < 24) order.push(pid);
                paint();
            });
            paint();
        }());
        </script>
      </div>
      <?php endif; ?>

      <!-- Danger zone · GDPR self-service -->
      <details style="margin-top:36px;border-top:1px solid var(--g-line);padding-top:22px">
        <summary style="cursor:pointer;font-size:11px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:var(--g-mid)"><?php esc_html_e( 'Delete account', 'vpg-v2' ); ?></summary>
        <form class="g-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width:none;margin-top:18px" onsubmit="return confirm('<?php echo esc_js( __( 'Delete your account permanently? This cannot be undone.', 'vpg-v2' ) ); ?>')">
          <?php wp_nonce_field( 'vpg_delete_account' ); ?>
          <input type="hidden" name="action" value="vpg_delete_account">
          <div class="g-field" style="display:grid;gap:10px">
            <label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;font-weight:500">
              <input type="radio" name="delete_mode" value="keep" checked style="margin-top:3px">
              <span><?php esc_html_e( 'Delete my account, keep my published work on the site (byline passes to editorial)', 'vpg-v2' ); ?></span>
            </label>
            <label style="display:flex;gap:10px;align-items:flex-start;font-size:14px;font-weight:500">
              <input type="radio" name="delete_mode" value="erase" style="margin-top:3px">
              <span><?php esc_html_e( 'Delete my account and everything I contributed — photos included', 'vpg-v2' ); ?></span>
            </label>
          </div>
          <div class="g-field">
            <label for="del-pw"><?php esc_html_e( 'Confirm with your password', 'vpg-v2' ); ?></label>
            <input class="g-input" id="del-pw" type="password" name="password" required autocomplete="current-password">
          </div>
          <button class="g-btn" type="submit" style="background:transparent;color:var(--g-red);border-color:var(--g-red)"><?php esc_html_e( 'Delete my account permanently', 'vpg-v2' ); ?></button>
        </form>
      </details>
    </div>
  </section>

  <!-- Bookmarks · saved for later -->
  <section class="g-section g-section--tight" id="bookmarks">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Bookmarks', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Saved for <em>later</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><?php echo count( $bookmarks ); ?></div>
      </div>
      <?php if ( $bookmarks ) :
          $marked = get_posts( [ 'post__in' => array_map( 'intval', $bookmarks ), 'post_type' => 'any', 'posts_per_page' => 12, 'orderby' => 'post__in' ] );
      ?>
      <div class="g-list">
        <?php foreach ( $marked as $bp ) : ?>
          <a class="g-row" style="grid-template-columns:auto 1fr auto" href="<?php echo esc_url( get_permalink( $bp ) ); ?>">
            <?php vpg_chip( $bp->post_type ); ?>
            <h3 class="g-row__title" style="margin:0"><?php echo esc_html( get_the_title( $bp ) ); ?></h3>
            <span class="g-row__when"><?php echo esc_html( get_the_date( 'M j, Y', $bp ) ); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php else : ?>
      <p class="g-lede" style="color:var(--g-mid)"><?php esc_html_e( 'Nothing saved yet — every article has a “Save for later” button. Whatever you star lands here.', 'vpg-v2' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Done for now?', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:14ch"><?php echo wp_kses_post( __( 'See you <em>soon</em>.', 'vpg-v2' ) ); ?></h2>
      <a class="g-btn g-btn--lg g-btn--on-dark" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Log out', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>

  <?php // Cluster 09 · the fuller artist page fields
  if ( function_exists( 'vpg_profile_extra_form' ) ) vpg_profile_extra_form(); ?>

<?php endif; ?>

</main>
<?php get_footer();
