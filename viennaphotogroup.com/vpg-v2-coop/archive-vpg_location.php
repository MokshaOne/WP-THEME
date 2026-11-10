<?php
/**
 * VPG v2 — Unified Map archive.
 * /locations/                → all three CPTs on map + grid
 * /locations/?type=location  → locations only
 * /locations/?type=studio    → studios only
 * /locations/?type=shop      → shops only
 *
 * Map shows ALL types always; the type-filter chips control which set is
 * highlighted (via JS) AND which set populates the grid below (via PHP).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

$paged           = max( 1, get_query_var( 'paged' ) );
$type_filter     = isset( $_GET['type'] )     ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'all';
$filter_district = isset( $_GET['district'] ) ? sanitize_text_field( wp_unslash( $_GET['district'] ) ) : '';
$valid_types     = [ 'all', 'location', 'studio', 'shop' ];
if ( ! in_array( $type_filter, $valid_types, true ) ) $type_filter = 'all';

/* ─── Build grid query · CPT depends on type filter ─── */
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
// District filter applies to ALL types · locations/studios use location_district,
// shops use shop_district, so OR both meta keys.
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
            // Capture district from whichever meta key the CPT uses
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

/* Apply district filter to the pins BEFORE rendering · so the map also
   reflects the active district. */
$pins_filtered = $pins;
if ( $filter_district ) {
    $pins_filtered = array_values( array_filter( $pins, function ( $p ) use ( $filter_district ) {
        return isset( $p['district'] ) && $p['district'] === $filter_district;
    } ) );
}
foreach ( $pins_filtered as $p ) { if ( isset( $counts[ $p['type'] ] ) ) $counts[ $p['type'] ]++; }
$total_pins = count( $pins_filtered );

/* ─── Districts list · pulled from BOTH meta keys (location_district + shop_district) ─── */
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

/* ─── Helper · build a URL with a swapped/added query arg, no district leak ─── */
$base_url = get_post_type_archive_link( 'vpg_location' );
$mk_url   = function ( $type ) use ( $base_url ) {
    if ( $type === 'all' ) return $base_url;
    return add_query_arg( 'type', $type, $base_url );
};

/* ─── Per-type hero copy ─── */
$hero_copy = [
    'all'      => [ 'chip' => 'loc',    'h' => 'Wien, <em>pinned</em>.',         'lede' => __( 'Locations, studios and shops on one canvas. Member-curated — light + access notes — district + transit context. Toggle the layers below.', 'vpg-v2' ) ],
    'location' => [ 'chip' => 'loc',    'h' => 'The <em>map</em>.',              'lede' => __( 'A member-curated map of shooting locations across Wien — light, access notes, optimal time of day, distance to a U-Bahn stop.', 'vpg-v2' ) ],
    'studio'   => [ 'chip' => 'studio', 'h' => 'Studio <em>directory</em>.',     'lede' => __( 'Rental photography studios across Vienna — cycloramas, daylight spaces, full strobe kits and vehicle access. Curated by members.', 'vpg-v2' ) ],
    'shop'     => [ 'chip' => 'shop',   'h' => 'Shop <em>guide</em>.',           'lede' => __( 'Camera shops, film suppliers, labs and gear retailers in Vienna — quietly reviewed by photographers who actually walked in.', 'vpg-v2' ) ],
];
$h = $hero_copy[ $type_filter ];
?>

<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--<?php echo esc_attr( $h['chip'] ); ?>"><span class="vpg-chip__dot"></span>
        <?php echo esc_html( $type_filter === 'all' ? __( 'The map', 'vpg-v2' ) : ucfirst( $type_filter . 's' ) ); ?>
    </span>
    <h1><?php echo vpg_em( $h['h'] ); ?></h1>
    <p class="vpg-lede"><?php echo esc_html( $h['lede'] ); ?></p>
    <p class="vpg-caps" style="margin-top:1.5rem">
        <?php printf( esc_html__( '%d pins · ', 'vpg-v2' ), $total_pins ); ?>
        <?php printf( '%d locations · %d studios · %d shops', $counts['location'], $counts['studio'], $counts['shop'] ); ?>

        <?php if ( current_user_can( 'manage_options' ) && $total_pins === 0 ) : ?>
            <br><span style="color:var(--vpg-warning)"><?php esc_html_e( 'Admin · 0 pins. Open Tools → VPG · Setup → run the diagnostic scan + migrate coordinates.', 'vpg-v2' ); ?></span>
        <?php endif; ?>
    </p>
</header>

<section class="vpg-section vpg-section--tight vpg-section--map">
    <div class="vpg-wrap">

        <!-- Type filter · controls BOTH the map (via JS) and the grid (via URL navigation) -->
        <div class="vpg-map-filter" data-target="#vpg-map" role="toolbar" aria-label="<?php esc_attr_e( 'Filter map markers', 'vpg-v2' ); ?>">
            <span class="vpg-map-filter__label">— <?php esc_html_e( 'Show', 'vpg-v2' ); ?></span>
            <a href="<?php echo esc_url( $mk_url( 'all' ) ); ?>"      class="<?php if ( $type_filter === 'all' )      echo 'is-active'; ?>"><button type="button" data-type="all"><?php esc_html_e( 'All', 'vpg-v2' ); ?> · <?php echo (int) $total_pins; ?></button></a>
            <a href="<?php echo esc_url( $mk_url( 'location' ) ); ?>" class="<?php if ( $type_filter === 'location' ) echo 'is-active'; ?>"><button type="button" data-type="location"><?php esc_html_e( 'Locations', 'vpg-v2' ); ?> · <?php echo (int) $counts['location']; ?></button></a>
            <a href="<?php echo esc_url( $mk_url( 'studio' ) ); ?>"   class="<?php if ( $type_filter === 'studio' )   echo 'is-active'; ?>"><button type="button" data-type="studio"><?php esc_html_e( 'Studios', 'vpg-v2' ); ?> · <?php echo (int) $counts['studio']; ?></button></a>
            <a href="<?php echo esc_url( $mk_url( 'shop' ) ); ?>"     class="<?php if ( $type_filter === 'shop' )     echo 'is-active'; ?>"><button type="button" data-type="shop"><?php esc_html_e( 'Shops', 'vpg-v2' ); ?> · <?php echo (int) $counts['shop']; ?></button></a>
        </div>

        <div id="vpg-map" class="vpg-map vpg-map--tall" data-pins="<?php echo esc_attr( wp_json_encode( $pins_filtered ) ); ?>"></div>
    </div>
</section>

<span class="vpg-asterism vpg-asterism--mark"></span>

<section class="vpg-section vpg-section--tight">
    <div class="vpg-wrap">
        <div class="vpg-section-head">
            <div>
                <p class="vpg-caps">— <?php
                    if ( $type_filter === 'all' )          esc_html_e( 'Index', 'vpg-v2' );
                    elseif ( $type_filter === 'location' ) esc_html_e( 'Locations index', 'vpg-v2' );
                    elseif ( $type_filter === 'studio' )   esc_html_e( 'Studios', 'vpg-v2' );
                    else                                    esc_html_e( 'Shops', 'vpg-v2' );
                ?></p>
                <h2><?php printf( esc_html( _n( '%d entry', '%d entries', $total, 'vpg-v2' ) ), $total ); ?><?php if ( $filter_district ) echo ' · <em style="font-style:italic;color:var(--vpg-accent)">' . esc_html( $filter_district ) . '</em>'; ?></h2>
            </div>
            <div class="vpg-section-head__meta">
                <a href="<?php echo esc_url( home_url('/submit/') ); ?>"><?php esc_html_e( 'Submit', 'vpg-v2' ); ?> →</a>
            </div>
        </div>

        <?php if ( $districts ) :
            // Build URL helpers that preserve the active type filter while changing district
            $type_url = $mk_url( $type_filter );
            $clear_dist_url = remove_query_arg( 'district', $type_url );
        ?>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:2rem">
            <span class="vpg-caps" style="display:inline-flex;align-items:center;color:var(--vpg-muted);margin-right:.5rem">— <?php esc_html_e( 'District', 'vpg-v2' ); ?></span>
            <a class="vpg-chip<?php echo ! $filter_district ? ' vpg-chip--loc' : ''; ?>" href="<?php echo esc_url( $clear_dist_url ); ?>"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'All', 'vpg-v2' ); ?></a>
            <?php foreach ( $districts as $d ) : ?>
                <a class="vpg-chip<?php echo $filter_district === $d ? ' vpg-chip--loc' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'district', urlencode( $d ), $type_url ) ); ?>"><span class="vpg-chip__dot"></span> <?php echo esc_html( $d ); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ( $q->have_posts() ) : ?>
            <div class="vpg-grid vpg-grid--auto">
                <?php while ( $q->have_posts() ) : $q->the_post();
                    $thumb     = get_the_post_thumbnail_url( get_the_ID(), 'vpg-card' );
                    $post_type = get_post_type();
                    $cls_map   = [ 'vpg_location' => 'loc', 'vpg_studio' => 'studio', 'vpg_shop' => 'shop' ];
                    $cls       = $cls_map[ $post_type ] ?? 'loc';
                    $label_map = [ 'vpg_location' => __( 'Location', 'vpg-v2' ), 'vpg_studio' => __( 'Studio', 'vpg-v2' ), 'vpg_shop' => __( 'Shop', 'vpg-v2' ) ];
                    $label     = $label_map[ $post_type ];

                    // Per-type meta line on the card
                    $meta_left = '';
                    if ( $post_type === 'vpg_location' ) {
                        $d = vpg_field( 'location_district' );
                        if ( $d ) $meta_left = esc_html( $d );
                    } elseif ( $post_type === 'vpg_studio' ) {
                        $rate = vpg_field( 'studio_hourly_rate' );
                        $size = vpg_field( 'studio_size' );
                        $meta_left = trim( ( $size ? esc_html( $size . 'm²' ) : '' ) . ( $size && $rate ? ' · ' : '' ) . ( $rate ? esc_html( $rate ) : '' ) );
                    } elseif ( $post_type === 'vpg_shop' ) {
                        $h = vpg_field( 'shop_hours' );
                        $f = vpg_field( 'shop_focus' );
                        $meta_left = $f ? esc_html( wp_trim_words( $f, 6 ) ) : ( $h ? esc_html( $h ) : '' );
                    }
                ?>
                    <article class="vpg-card">
                        <a href="<?php the_permalink(); ?>">
                            <div class="vpg-card__media vpg-card__media--landscape <?php echo $thumb ? '' : 'vpg-card__media--placeholder'; ?>">
                                <?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"><?php endif; ?>
                            </div>
                            <span class="vpg-chip vpg-chip--<?php echo esc_attr( $cls ); ?>"><span class="vpg-chip__dot"></span> <?php echo esc_html( $label ); ?></span>
                            <h3 style="margin-top:.8rem"><?php the_title(); ?></h3>
                            <p class="vpg-card__lede"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                            <?php if ( $meta_left ) : ?>
                                <div class="vpg-card__meta" style="display:flex;justify-content:space-between;margin-top:1.2rem;border-top:1px solid var(--vpg-rule);padding-top:1rem">
                                    <span><?php echo $meta_left; ?></span>
                                    <span style="font-family:var(--vpg-font-display);color:var(--vpg-accent);font-size:var(--vpg-fs-lg)">→</span>
                                </div>
                            <?php endif; ?>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
            <nav class="vpg-pagination" style="margin-top:4rem;text-align:center"><?php the_posts_pagination( [ 'prev_text' => '← Previous', 'next_text' => 'Next →', 'mid_size' => 2 ] ); ?></nav>
        <?php else : ?>
            <div class="vpg-quote" style="margin:6rem auto"><blockquote><?php esc_html_e( 'Nothing here yet for this filter.', 'vpg-v2' ); ?></blockquote></div>
        <?php endif; ?>
    </div>
</section>

</main>

<style>
    /* Make the filter chips wrap an anchor so URL navigation works · same look */
    .vpg-map-filter > a { display:inline-flex; text-decoration:none; }
    .vpg-map-filter > a.is-active button { background: var(--vpg-ink); color: var(--vpg-bg); border-color: var(--vpg-ink); }
</style>

<?php wp_reset_postdata(); get_footer();
