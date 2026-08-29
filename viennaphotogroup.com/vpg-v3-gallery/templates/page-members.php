<?php
/** Template Name: Members directory */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

// Opt-in only · a member appears here after ticking the box in their profile
$members = get_users( [
    'role__in'   => [ 'vpg_member', 'administrator', 'editor', 'author' ],
    'meta_key'   => '_vpg_directory_optin',
    'meta_value' => '1',
    'orderby'    => 'registered',
    'order'      => 'ASC',
    'number'     => 200,
] );
?>
<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php esc_html_e( 'Members', 'vpg-v2' ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo wp_kses_post( __( 'The people on the <em>wall</em>.', 'vpg-v2' ) ); ?></h1>
          <p class="g-lede g-phero__lede"><?php esc_html_e( 'The photographers who keep this running — everyone here chose to be listed. Click through to their work.', 'vpg-v2' ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Listed', 'vpg-v2' ); ?></dt><dd><?php echo count( $members ); ?></dd>
          <dt><?php esc_html_e( 'Visibility', 'vpg-v2' ); ?></dt><dd><?php esc_html_e( 'Opt-in · set in your profile', 'vpg-v2' ); ?></dd>
        </dl>
      </div>
    </div>
  </section>

  <section class="g-section">
    <div class="g-wrap">
      <?php if ( $members ) : ?>
        <div class="g-grid3">
          <?php foreach ( $members as $m ) :
              $works = count_user_posts( $m->ID, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'post' ], true );
          ?>
          <a class="g-card" href="<?php echo esc_url( home_url( '/members/' . $m->user_nicename . '/' ) ); ?>">
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px">
              <?php echo get_avatar( $m->ID, 64, '', esc_attr( $m->display_name ), [ 'style' => 'display:block;width:64px;height:64px;object-fit:cover' ] ); ?>
              <div>
                <h3 class="g-card__title" style="margin:0"><?php echo esc_html( $m->display_name ); ?></h3>
                <div class="g-byline"><span><?php printf( esc_html__( 'since %s', 'vpg-v2' ), esc_html( mysql2date( 'M Y', $m->user_registered ) ) ); ?></span><span>·</span><span><?php printf( esc_html( _n( '%d work', '%d works', (int) $works, 'vpg-v2' ) ), (int) $works ); ?></span><?php
                if ( function_exists( 'vpg_member_rank' ) ) {
                    $mr = vpg_member_rank( $m->ID );
                    if ( $mr['level'] >= 1 ) echo '<span>·</span><span style="color:var(--g-red);font-weight:700">' . esc_html( $mr['label'] ) . '</span>';
                }
                ?></div>
              </div>
            </div>
            <?php if ( $m->description ) : ?>
              <p class="g-row__lede"><?php echo esc_html( wp_trim_words( $m->description, 18 ) ); ?></p>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <div style="text-align:center;padding:3rem 0">
          <p class="g-lede" style="margin:0 auto 24px;text-align:center;max-width:52ch"><?php esc_html_e( 'Nobody is listed yet. Members choose to appear here from their dashboard profile — be the first.', 'vpg-v2' ); ?></p>
          <a class="g-btn g-btn--red" href="<?php echo esc_url( home_url( '/dashboard/#profile' ) ); ?>"><?php esc_html_e( 'Open your profile', 'vpg-v2' ); ?> →</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if ( is_user_logged_in() ) :
      $mentors = get_users( [ 'meta_key' => '_vpg_buddy_role', 'meta_value' => 'mentor',  'number' => 30 ] );
      $looking = get_users( [ 'meta_key' => '_vpg_buddy_role', 'meta_value' => 'looking', 'number' => 30 ] );
      if ( $mentors || $looking ) :
  ?>
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php esc_html_e( 'Photowalk buddies · members only', 'vpg-v2' ); ?></span>
          <h2 class="g-head__t"><?php echo wp_kses_post( __( 'Nobody walks <em>alone</em>.', 'vpg-v2' ) ); ?></h2>
        </div>
        <div class="g-meta"><a class="g-link" href="<?php echo esc_url( home_url( '/dashboard/#profile' ) ); ?>"><?php esc_html_e( 'Join in', 'vpg-v2' ); ?> <span class="a">→</span></a></div>
      </div>
      <div class="g-twocol">
        <div>
          <p class="g-kicker g-kicker--ink" style="margin-bottom:14px"><?php esc_html_e( 'Showing newcomers around', 'vpg-v2' ); ?></p>
          <?php if ( $mentors ) : foreach ( $mentors as $b ) : ?>
            <p style="margin:0 0 10px"><a href="<?php echo esc_url( home_url( '/members/' . $b->user_nicename . '/' ) ); ?>" style="font-weight:700"><?php echo esc_html( $b->display_name ); ?></a></p>
          <?php endforeach; else : ?>
            <p class="g-meta"><?php esc_html_e( 'Nobody yet — be the first.', 'vpg-v2' ); ?></p>
          <?php endif; ?>
        </div>
        <div>
          <p class="g-kicker g-kicker--ink" style="margin-bottom:14px"><?php esc_html_e( 'Looking for company', 'vpg-v2' ); ?></p>
          <?php if ( $looking ) : foreach ( $looking as $b ) : ?>
            <p style="margin:0 0 10px"><a href="<?php echo esc_url( home_url( '/members/' . $b->user_nicename . '/' ) ); ?>" style="font-weight:700"><?php echo esc_html( $b->display_name ); ?></a></p>
          <?php endforeach; else : ?>
            <p class="g-meta"><?php esc_html_e( 'Nobody waiting right now.', 'vpg-v2' ); ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; endif; ?>

  <section class="g-section--dark g-section">
    <div class="g-wrap" style="text-align:center">
      <span class="g-kicker"><?php esc_html_e( 'Membership · free', 'vpg-v2' ); ?></span>
      <h2 class="g-display g-cta__title" style="margin:18px auto 22px;max-width:16ch"><?php echo wp_kses_post( __( 'Your name belongs <em>here</em>.', 'vpg-v2' ) ); ?></h2>
      <a class="g-btn g-btn--lg g-btn--red" href="<?php echo esc_url( home_url( '/join/' ) ); ?>"><?php esc_html_e( 'Join the collective', 'vpg-v2' ); ?> <span class="a">→</span></a>
    </div>
  </section>

</main>
<?php get_footer();
