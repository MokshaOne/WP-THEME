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
    $drafts = new WP_Query( [
        'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial' ],
        'author'         => $u->ID,
        'post_status'    => 'draft',
        'posts_per_page' => 10,
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
          <label style="display:flex;gap:10px;align-items:center;font-size:14px;font-weight:500">
            <input type="checkbox" name="directory_optin" value="1" <?php checked( get_user_meta( $u->ID, '_vpg_directory_optin', true ) === '1' ); ?>>
            <?php esc_html_e( 'List me in the public members directory', 'vpg-v2' ); ?>
          </label>
        </div>

        <button class="g-btn g-btn--lg g-btn--red" type="submit"><?php esc_html_e( 'Save profile', 'vpg-v2' ); ?> <span class="a">→</span></button>
      </form>

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
  <section class="g-section g-section--tight">
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

<?php endif; ?>

</main>
<?php get_footer();
