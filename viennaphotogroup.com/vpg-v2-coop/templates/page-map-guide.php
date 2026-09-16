<?php
/**
 * Template Name: Location map (full screen)
 *
 * Full-screen Leaflet map. Pulls geo-coded pins from THREE CPTs at once
 * (locations + studios + shops) with type-specific markers and a filter
 * bar above the map. Members can hide/show categories.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

/* ── Collect geo-coded pins from every relevant CPT ─────────── */
$pins        = [];
$counts      = [ 'location' => 0, 'studio' => 0, 'shop' => 0 ];
$cpt_for_type = [ 'location' => 'vpg_location', 'studio' => 'vpg_studio', 'shop' => 'vpg_shop' ];
foreach ( $cpt_for_type as $type => $cpt ) {
    $items = get_posts( [ 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'publish' ] );
    foreach ( $items as $p ) {
        $coords = vpg_get_coords( $p->ID );
        if ( ! $coords ) continue;
        $pins[] = [
            'lat'   => $coords[0],
            'lng'   => $coords[1],
            'title' => get_the_title( $p ),
            'url'   => get_permalink( $p ),
            'lede'  => wp_trim_words( get_the_excerpt( $p ), 14 ),
            'type'  => $type,
        ];
        $counts[ $type ]++;
    }
}
$total = count( $pins );
?>

<main id="vpg-main">

<header class="vpg-page-hero">
    <span class="vpg-chip vpg-chip--loc"><span class="vpg-chip__dot"></span> <?php esc_html_e( 'Map', 'vpg-v2' ); ?></span>
    <h1><?php esc_html_e( 'Vienna,', 'vpg-v2' ); ?> <em><?php esc_html_e( 'pinned', 'vpg-v2' ); ?></em>.</h1>
    <p class="vpg-lede"><?php esc_html_e( 'The full interactive map. Locations, studios and shops on one canvas — toggle any layer.', 'vpg-v2' ); ?></p>
</header>

<section class="vpg-section vpg-section--tight vpg-section--map">
    <div class="vpg-wrap">

        <div class="vpg-map-filter" data-target="#vpg-map" role="toolbar" aria-label="<?php esc_attr_e( 'Filter map markers', 'vpg-v2' ); ?>">
            <span class="vpg-map-filter__label">— <?php esc_html_e( 'Show', 'vpg-v2' ); ?></span>
            <button type="button" data-type="all"      class="is-active"><?php esc_html_e( 'All', 'vpg-v2' ); ?> · <?php echo (int) $total; ?></button>
            <button type="button" data-type="location" class="is-active"><?php esc_html_e( 'Locations', 'vpg-v2' ); ?> · <?php echo (int) $counts['location']; ?></button>
            <button type="button" data-type="studio"   class="is-active"><?php esc_html_e( 'Studios', 'vpg-v2' ); ?> · <?php echo (int) $counts['studio']; ?></button>
            <button type="button" data-type="shop"     class="is-active"><?php esc_html_e( 'Shops', 'vpg-v2' ); ?> · <?php echo (int) $counts['shop']; ?></button>
        </div>

        <div id="vpg-map" class="vpg-map vpg-map--tall vpg-map-count"
             data-pins="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>"
             data-count="<?php echo (int) $total; ?> <?php esc_attr_e( 'pinned', 'vpg-v2' ); ?>"></div>

    </div>
</section>

</main>

<?php get_footer();
