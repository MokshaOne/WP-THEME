<?php
/**
 * VPG v3 — Unified Map archive · "Gallery" skin.
 * /locations/                → all three CPTs on map + grid
 * /locations/?type=location|studio|shop → filtered set
 *
 * The live Leaflet map and its filter toolbar are preserved exactly
 * (#vpg-map[data-pins] + .vpg-map-filter[data-target] + button[data-type]
 * are read by map-engine.js / styled by map.css). Only the page chrome,
 * district chips and the index grid are re-skinned to the Gallery system.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged           = max( 1, get_query_var( 'paged' ) );
$type_filter     = isset( $_GET['type'] )     ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'all';
$filter_district = isset( $_GET['district'] ) ? sanitize_text_field( wp_unslash( $_GET['district'] ) ) : '';
$valid_types     = [ 'all', 'location', 'studio', 'shop' ];
if ( ! in_array( $type_filter, $valid_types, true ) ) $type_filter = 'all';

$cpts_for = [
    'all'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop' ],
    'location' => [ 'vpg_location' ],
    'studio'   => [ 'vpg_studio' ],
    'shop'     => [ 'vpg_shop' ],
];
$query_args = [
    'post_type'      => $cpts_for[ $type_filter ],
    'posts_per_page' => 24,
    'post_status'    => 'publish',
    'paged'          => $paged,
];
if ( $filter_district ) {
    $query_args['meta_query'] = [
        'relation' => 'OR',
        [ 'key' => 'location_district', 'value' => $filter_district, 'compare' => '=' ],
        [ 'key' => 'shop_district',     'value' => $filter_district, 'compare' => '=' ],
    ];
}
$q     = new WP_Query( $query_args );
$total = (int) $q->found_posts;

/* ─── Build pins for ALL three CPTs (map shows everything; filter is visual) ─── */
$nocache = isset( $_GET['nocache'] );
$pins    = $nocache ? false : get_transient( 'vpg_location_pins' );
$counts  = [ 'location' => 0, 'studio' => 0, 'shop' => 0 ];

if ( false === $pins || ! is_array( $pins ) || empty( $pins ) ) {
    $pins = [];
    foreach ( [ 'location' => 'vpg_location', 'studio' => 'vpg_studio', 'shop' => 'vpg_shop' ] as $t => $cpt ) {
        $items = get_posts( [ 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'publish' ] );
        foreach ( $items as $p ) {
            $coords = vpg_get_coords( $p->ID );
            if ( ! $coords ) continue;
            $d_meta = ( $cpt === 'vpg_shop' ) ? 'shop_district' : 'location_district';
            $pins[] = [
                'lat'      => $coords[0],
                'lng'      => $coords[1],
                'title'    => get_the_title( $p ),
                'url'      => get_permalink( $p ),
                'lede'     => wp_trim_words( get_the_excerpt( $p ), 14 ),
                'type'     => $t,
                'district' => (string) get_post_meta( $p->ID, $d_meta, true ),
            ];
        }
    }
    if ( $pins ) set_transient( 'vpg_location_pins', $pins, HOUR_IN_SECONDS );
}

$pins_filtered = $pins;
if ( $filter_district ) {
    $pins_filtered = array_values( array_filter( $pins, function ( $p ) use ( $filter_district ) {
        return isset( $p['district'] ) && $p['district'] === $filter_district;
    } ) );
}
foreach ( $pins_filtered as $p ) { if ( isset( $counts[ $p['type'] ] ) ) $counts[ $p['type'] ]++; }
$total_pins = count( $pins_filtered );

/* The map markers must honour the active TYPE too (the toolbar links reload
   with ?type=…). Counts above stay across all types so the toolbar tallies are
   right; $map_pins is what actually renders on the map. */
$map_pins = $pins_filtered;
if ( $type_filter !== 'all' ) {
    $map_pins = array_values( array_filter( $pins_filtered, function ( $p ) use ( $type_filter ) {
        return isset( $p['type'] ) && $p['type'] === $type_filter;
    } ) );
}

/* ─── Districts list ─── */
$districts = get_transient( 'vpg_location_districts' );
if ( false === $districts ) {
    global $wpdb;
    $districts = $wpdb->get_col(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta}
           WHERE meta_key IN ('location_district', 'shop_district')
             AND meta_value <> ''
           ORDER BY meta_value ASC"
    );
    set_transient( 'vpg_location_districts', $districts, HOUR_IN_SECONDS );
}

$base_url = get_post_type_archive_link( 'vpg_location' );
$mk_url   = function ( $type ) use ( $base_url ) {
    if ( $type === 'all' ) return $base_url;
    return add_query_arg( 'type', $type, $base_url );
};

$hero_copy = [
    'all'      => [ 'h' => 'Wien, <em>pinned</em>.',     'lede' => __( 'Locations, studios and shops on one canvas. Member-curated — light + access notes, district + transit context. Toggle the layers below.', 'vpg-v2' ) ],
    'location' => [ 'h' => 'The <em>map</em>.',          'lede' => __( 'A member-curated map of shooting locations across Wien — light, access notes, optimal time of day, distance to a U-Bahn stop.', 'vpg-v2' ) ],
    'studio'   => [ 'h' => 'Studio <em>directory</em>.', 'lede' => __( 'Rental photography studios across Vienna — cycloramas, daylight spaces, full strobe kits and vehicle access. Curated by members.', 'vpg-v2' ) ],
    'shop'     => [ 'h' => 'Shop <em>guide</em>.',       'lede' => __( 'Camera shops, film suppliers, labs and gear retailers in Vienna — quietly reviewed by photographers who actually walked in.', 'vpg-v2' ) ],
];
$h = $hero_copy[ $type_filter ];
?>

<main id="vpg-main">

  <section class="g-phero">
    <div class="g-wrap">
      <div class="g-phero__grid">
        <div>
          <p class="g-kicker" style="margin-bottom:18px">● <?php echo esc_html( $type_filter === 'all' ? __( 'The Map', 'vpg-v2' ) : ucfirst( $type_filter . 's' ) ); ?></p>
          <h1 class="g-display g-phero__title"><?php echo vpg_em( $h['h'] ); ?></h1>
          <p class="g-lede g-phero__lede"><?php echo esc_html( $h['lede'] ); ?></p>
        </div>
        <dl class="g-phero__aside">
          <dt><?php esc_html_e( 'Pinned', 'vpg-v2' ); ?></dt><dd><?php echo (int) $total_pins; ?> <?php esc_html_e( 'on the map', 'vpg-v2' ); ?></dd>
          <dt><?php esc_html_e( 'Locations', 'vpg-v2' ); ?></dt><dd><?php echo (int) $counts['location']; ?></dd>
          <dt><?php esc_html_e( 'Studios', 'vpg-v2' ); ?></dt><dd><?php echo (int) $counts['studio']; ?></dd>
          <dt><?php esc_html_e( 'Shops', 'vpg-v2' ); ?></dt><dd><?php echo (int) $counts['shop']; ?></dd>
        </dl>
      </div>
      <?php if ( current_user_can( 'manage_options' ) && $total_pins === 0 ) : ?>
        <p class="g-kicker" style="color:var(--g-red);margin-top:20px"><?php esc_html_e( 'Admin · 0 pins. Tools → VPG · Setup → run the diagnostic scan + migrate coordinates.', 'vpg-v2' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- ── LIVE MAP (preserved hooks: #vpg-map / .vpg-map-filter / button[data-type]) ── -->
  <section class="g-section g-section--tight">
    <div class="g-wrap">
      <div class="vpg-map-filter" data-target="#vpg-map" role="toolbar" aria-label="<?php esc_attr_e( 'Filter map markers', 'vpg-v2' ); ?>">
        <span class="vpg-map-filter__label">— <?php esc_html_e( 'Show', 'vpg-v2' ); ?></span>
        <a href="<?php echo esc_url( $mk_url( 'all' ) ); ?>"      class="<?php if ( $type_filter === 'all' )      echo 'is-active'; ?>"><button type="button" data-type="all"><?php esc_html_e( 'All', 'vpg-v2' ); ?> · <?php echo (int) $total_pins; ?></button></a>
        <a href="<?php echo esc_url( $mk_url( 'location' ) ); ?>" class="<?php if ( $type_filter === 'location' ) echo 'is-active'; ?>"><button type="button" data-type="location"><?php esc_html_e( 'Locations', 'vpg-v2' ); ?> · <?php echo (int) $counts['location']; ?></button></a>
        <a href="<?php echo esc_url( $mk_url( 'studio' ) ); ?>"   class="<?php if ( $type_filter === 'studio' )   echo 'is-active'; ?>"><button type="button" data-type="studio"><?php esc_html_e( 'Studios', 'vpg-v2' ); ?> · <?php echo (int) $counts['studio']; ?></button></a>
        <a href="<?php echo esc_url( $mk_url( 'shop' ) ); ?>"     class="<?php if ( $type_filter === 'shop' )     echo 'is-active'; ?>"><button type="button" data-type="shop"><?php esc_html_e( 'Shops', 'vpg-v2' ); ?> · <?php echo (int) $counts['shop']; ?></button></a>
      </div>

      <div id="vpg-map" class="vpg-map vpg-map--tall" data-pins="<?php echo esc_attr( wp_json_encode( $map_pins ) ); ?>"></div>
    </div>
  </section>

  <!-- ── INDEX GRID ── -->
  <section class="g-section g-section--alt">
    <div class="g-wrap">
      <div class="g-head">
        <div>
          <span class="g-kicker"><?php
            if ( $type_filter === 'all' )          esc_html_e( 'Index', 'vpg-v2' );
            elseif ( $type_filter === 'location' ) esc_html_e( 'Locations index', 'vpg-v2' );
            elseif ( $type_filter === 'studio' )   esc_html_e( 'Studios', 'vpg-v2' );
            else                                    esc_html_e( 'Shops', 'vpg-v2' );
          ?></span>
          <h2 class="g-head__t"><?php printf( esc_html( _n( '%d entry', '%d entries', $total, 'vpg-v2' ) ), $total ); ?><?php if ( $filter_district ) echo ' <em style="color:var(--g-red)">· ' . esc_html( $filter_district ) . '</em>'; ?></h2>
        </div>
        <a class="g-link" href="<?php echo esc_url( home_url( '/submit/' ) ); ?>"><?php esc_html_e( 'Submit a place', 'vpg-v2' ); ?> <span class="a">→</span></a>
      </div>

      <?php if ( $districts ) :
        $type_url       = $mk_url( $type_filter );
        $clear_dist_url = remove_query_arg( 'district', $type_url );
      ?>
      <div class="g-chips">
        <a class="g-chip<?php echo ! $filter_district ? ' g-chip--on' : ''; ?>" href="<?php echo esc_url( $clear_dist_url ); ?>"><?php esc_html_e( 'All districts', 'vpg-v2' ); ?></a>
        <?php foreach ( $districts as $d ) : ?>
          <a class="g-chip<?php echo $filter_district === $d ? ' g-chip--on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'district', urlencode( $d ), $type_url ) ); ?>"><?php echo esc_html( $d ); ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ( $q->have_posts() ) : ?>
        <div class="g-locgrid">
          <?php while ( $q->have_posts() ) : $q->the_post();
            $post_type = get_post_type();
            $label_map = [ 'vpg_location' => __( 'Location', 'vpg-v2' ), 'vpg_studio' => __( 'Studio', 'vpg-v2' ), 'vpg_shop' => __( 'Shop', 'vpg-v2' ) ];
            $label     = $label_map[ $post_type ] ?? __( 'Location', 'vpg-v2' );

            // Per-type meta rows
            $meta_a_k = $meta_a_v = $meta_b_k = $meta_b_v = '';
            if ( $post_type === 'vpg_location' ) {
                $d = vpg_field( 'location_district' );
                $meta_a_k = __( 'District', 'vpg-v2' ); $meta_a_v = $d ?: '—';
            } elseif ( $post_type === 'vpg_studio' ) {
                $rate = vpg_field( 'studio_hourly_rate' ); $size = vpg_field( 'studio_size' );
                $meta_a_k = __( 'Size', 'vpg-v2' ); $meta_a_v = $size ? $size . ' m²' : '—';
                $meta_b_k = __( 'Rate', 'vpg-v2' ); $meta_b_v = $rate ?: '—';
            } elseif ( $post_type === 'vpg_shop' ) {
                $h2 = vpg_field( 'shop_hours' ); $f = vpg_field( 'shop_focus' );
                $meta_a_k = __( 'Focus', 'vpg-v2' ); $meta_a_v = $f ? wp_trim_words( $f, 4 ) : '—';
                $meta_b_k = __( 'Hours', 'vpg-v2' ); $meta_b_v = $h2 ?: '—';
            }
          ?>
            <a class="g-loc" href="<?php the_permalink(); ?>">
              <div class="g-fig"><?php if ( has_post_thumbnail() ) the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?></div>
              <div class="g-loc__body">
                <span class="g-cat"><?php echo esc_html( $label ); ?></span>
                <h3 class="g-loc__title"><?php the_title(); ?></h3>
                <div class="g-loc__meta">
                  <?php if ( $meta_a_k ) : ?><div><span><?php echo esc_html( $meta_a_k ); ?></span><b><?php echo esc_html( $meta_a_v ); ?></b></div><?php endif; ?>
                  <?php if ( $meta_b_k ) : ?><div><span><?php echo esc_html( $meta_b_k ); ?></span><b><?php echo esc_html( $meta_b_v ); ?></b></div><?php endif; ?>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:clamp(36px,5vw,56px)">
          <?php the_posts_pagination( [ 'prev_text' => __( '← Previous', 'vpg-v2' ), 'next_text' => __( 'Next →', 'vpg-v2' ), 'mid_size' => 2 ] ); ?>
        </div>
      <?php else : ?>
        <p class="g-lede"><?php esc_html_e( 'Nothing here yet for this filter.', 'vpg-v2' ); ?></p>
      <?php endif; ?>
    </div>
  </section>

</main>

<style>
  /* Filter chips wrap an anchor so URL navigation works · keep the active look */
  .vpg-map-filter > a { display:inline-flex; text-decoration:none; }
  .vpg-map-filter > a.is-active button { background: var(--vpg-ink); color: var(--vpg-bg); border-color: var(--vpg-ink); }
</style>

<?php wp_reset_postdata(); get_footer();
