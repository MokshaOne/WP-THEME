<?php
/** Template Name: Year in Review
 * 0371 · your twelve months, generated — private, per member. */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

if ( ! is_user_logged_in() ) : ?>
<main id="vpg-main"><section class="g-section"><div class="g-wrap" style="text-align:center">
  <h1 class="g-display" style="font-size:clamp(32px,6vw,64px)"><?php echo wp_kses_post( __( 'Your year is <em>yours</em>.', 'vpg-v2' ) ); ?></h1>
  <p class="g-lede" style="margin:16px auto 0"><a class="g-link" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Log in to see it', 'vpg-v2' ); ?></a></p>
</div></section></main>
<?php get_footer(); return; endif;

$uid   = get_current_user_id();
$u     = wp_get_current_user();
$year  = (int) ( $_GET['y'] ?? 0 );
$year  = ( $year >= 2019 && $year <= (int) wp_date( 'Y' ) ) ? $year : (int) wp_date( 'Y' );
$types = function_exists( 'vpg_submittable_types' ) ? vpg_submittable_types() : [ 'post' ];

$mine = get_posts( [
    'author'         => $uid,
    'post_type'      => $types,
    'post_status'    => 'publish',
    'posts_per_page' => 200,
    'date_query'     => [ [ 'year' => $year ] ],
] );

$by_type = [];
$views   = 0;
$thanks  = 0;
$best    = null;
$best_v  = -1;
foreach ( $mine as $p ) {
    $by_type[ $p->post_type ] = ( $by_type[ $p->post_type ] ?? 0 ) + 1;
    $v       = (int) get_post_meta( $p->ID, '_vpg_views', true );
    $views  += $v;
    $thanks += count( array_filter( (array) get_post_meta( $p->ID, '_vpg_thanks', true ) ) );
    if ( $v > $best_v ) { $best_v = $v; $best = $p; }
}

// Walks attended · RSVP lists of the year's events
$walked = 0;
foreach ( get_posts( [ 'post_type' => 'vpg_event', 'post_status' => 'publish', 'posts_per_page' => 100,
    'meta_query' => [ [ 'key' => '_vpg_event_date', 'value' => [ $year . '-01-01', $year . '-12-31' ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ] ] ) as $ev ) {
    $rsvps = function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( $ev->ID ) : array_filter( array_map( 'intval', (array) get_post_meta( $ev->ID, '_vpg_rsvps', true ) ) );
    if ( in_array( $uid, $rsvps, true ) ) $walked++;
}

$rank = function_exists( 'vpg_member_rank' ) ? vpg_member_rank( $uid ) : null;
?>
<main id="vpg-main">

  <section class="g-phero" style="background:var(--g-ink,#0B0B0B);color:#fff"><div class="g-wrap"><div class="g-phero__grid">
    <div>
      <p class="g-kicker" style="margin-bottom:16px;color:var(--g-red,#E5341F)">● <?php printf( esc_html__( '%d · your year', 'vpg-v2' ), $year ); ?></p>
      <h1 class="g-display g-phero__title" style="color:#fff"><?php echo esc_html( $u->display_name ); ?><span style="color:var(--g-red)">.</span></h1>
      <p class="g-lede g-phero__lede" style="color:rgba(255,255,255,.72)"><?php esc_html_e( 'Twelve months, counted honestly. Only you see this page.', 'vpg-v2' ); ?></p>
    </div>
    <dl class="g-phero__aside" style="color:#fff">
      <dt style="color:rgba(255,255,255,.5)"><?php esc_html_e( 'Other years', 'vpg-v2' ); ?></dt>
      <dd><?php for ( $y = (int) wp_date( 'Y' ); $y >= max( 2019, (int) wp_date( 'Y' ) - 4 ); $y-- ) : ?>
        <a href="<?php echo esc_url( add_query_arg( 'y', $y, get_permalink() ) ); ?>" style="margin-right:10px;<?php echo $y === $year ? 'color:var(--g-red);font-weight:800' : ''; ?>"><?php echo (int) $y; ?></a>
      <?php endfor; ?></dd>
    </dl>
  </div></div></section>

  <section class="g-section"><div class="g-wrap">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0 32px">
      <?php
      $stats = [
          [ count( $mine ), __( 'pieces published', 'vpg-v2' ) ],
          [ $views,         __( 'times your work was seen', 'vpg-v2' ) ],
          [ $thanks,        __( 'quiet thank-yous received', 'vpg-v2' ) ],
          [ $walked,        __( 'walks you joined', 'vpg-v2' ) ],
      ];
      foreach ( $stats as [ $n, $label ] ) : ?>
        <div style="border-top:2px solid var(--g-ink);padding:18px 0">
          <span class="g-display" style="display:block;font-size:clamp(40px,6vw,72px);line-height:1"><?php echo (int) $n; ?></span>
          <span class="g-meta" style="font-size:11px"><?php echo esc_html( $label ); ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php // 1028 · a shareable postcard — drawn in the browser, opt-in ?>
    <div style="margin-top:36px">
      <button type="button" class="g-btn g-btn--ghost" id="vpg-card-btn"><?php esc_html_e( '↓ Save this year as a postcard', 'vpg-v2' ); ?></button>
      <canvas id="vpg-card" width="1200" height="630" style="display:none"></canvas>
    </div>
    <script>
    (function () {
      var data = <?php echo wp_json_encode( [
          'name'  => $u->display_name,
          'year'  => $year,
          'site'  => get_bloginfo( 'name' ),
          'stats' => array_map( fn( $s ) => [ (int) $s[0], (string) $s[1] ], $stats ),
      ] ); ?>;
      var btn = document.getElementById('vpg-card-btn'), cv = document.getElementById('vpg-card');
      btn.addEventListener('click', function () {
        var g = cv.getContext('2d');
        g.fillStyle = '#0B0B0B'; g.fillRect(0, 0, 1200, 630);
        g.fillStyle = '#E5341F'; g.fillRect(0, 0, 1200, 10);
        g.fillStyle = '#9C9A95'; g.font = '600 22px -apple-system,system-ui,sans-serif';
        g.fillText(data.site.toUpperCase() + ' · ' + data.year, 64, 96);
        g.fillStyle = '#F5F4F1'; g.font = '800 92px -apple-system,system-ui,sans-serif';
        g.fillText(data.name, 60, 200);
        g.fillStyle = '#E5341F'; g.fillText('.', 60 + g.measureText(data.name).width, 200);
        var x = 64, y = 320;
        data.stats.forEach(function (s, i) {
          if (i === 2) { x = 64; y = 470; }
          g.fillStyle = '#F5F4F1'; g.font = '800 84px -apple-system,system-ui,sans-serif';
          g.fillText(String(s[0]), x, y + 70);
          g.fillStyle = '#A5A29C'; g.font = '600 20px -apple-system,system-ui,sans-serif';
          g.fillText(s[1], x, y + 104);
          x += 560;
        });
        g.fillStyle = '#6A6A6A'; g.font = '600 18px -apple-system,system-ui,sans-serif';
        g.fillText('viennaphotogroup.com', 64, 600);
        cv.toBlob(function (blob) {
          var a = document.createElement('a');
          a.href = URL.createObjectURL(blob);
          a.download = 'vpg-' + data.year + '.png';
          document.body.appendChild(a); a.click(); a.remove();
          setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
        }, 'image/png');
      });
    })();
    </script>

    <?php if ( $by_type ) : ?>
    <div style="margin-top:40px;display:flex;gap:10px;flex-wrap:wrap">
      <?php foreach ( $by_type as $tt => $n ) : $to = get_post_type_object( $tt ); if ( ! $to ) continue; ?>
        <span style="border:1px solid var(--g-line);padding:8px 14px;font-size:12px;font-weight:700"><?php echo (int) $n; ?> × <?php echo esc_html( $to->labels->singular_name ); ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( $best && $best_v > 0 ) : ?>
    <div style="margin-top:48px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:32px;align-items:center">
      <div>
        <span class="g-kicker"><?php esc_html_e( 'Your most-seen piece', 'vpg-v2' ); ?></span>
        <h2 style="font-size:clamp(22px,3vw,34px);margin:10px 0"><a href="<?php echo esc_url( get_permalink( $best ) ); ?>" style="text-decoration:none"><?php echo esc_html( $best->post_title ); ?></a></h2>
        <p class="g-meta"><?php printf( esc_html__( '%d views this year', 'vpg-v2' ), $best_v ); ?></p>
      </div>
      <?php if ( has_post_thumbnail( $best ) ) : ?>
        <a href="<?php echo esc_url( get_permalink( $best ) ); ?>"><?php echo get_the_post_thumbnail( $best, 'large', [ 'style' => 'width:100%;height:auto;display:block' ] ); ?></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ( $rank ) : ?>
    <div style="margin-top:48px;border:1px solid var(--g-ink);padding:24px 28px;display:flex;gap:24px;align-items:center;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <span class="g-kicker"><?php esc_html_e( 'Where you stand', 'vpg-v2' ); ?></span>
        <p class="g-display" style="font-size:clamp(24px,4vw,40px);margin:6px 0 0"><?php echo esc_html( $rank['label'] ); ?><span style="color:var(--g-red)">.</span></p>
      </div>
      <?php if ( ! empty( $rank['next'] ) ) : ?>
        <p class="g-meta" style="max-width:300px"><?php printf( esc_html__( 'Next: %1$s — %2$d of %3$d on the current milestone.', 'vpg-v2' ), esc_html( $rank['next'] ), (int) ( $rank['next_have'] ?? 0 ), (int) ( $rank['next_need'] ?? 0 ) ); ?></p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ( ! $mine && ! $walked ) : ?>
      <p class="g-lede" style="margin-top:40px"><?php esc_html_e( 'A quiet year on the site — the city was still out there. Next year counts from January.', 'vpg-v2' ); ?></p>
    <?php endif; ?>
  </div></section>

</main>
<?php get_footer();
