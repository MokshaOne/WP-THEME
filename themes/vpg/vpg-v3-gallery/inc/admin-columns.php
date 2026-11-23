<?php
/**
 * VPG v3 — Admin list columns · make every CPT list answer its real question.
 *
 *   Events        → when, where, how many coming (sortable by event date)
 *   Locations/Studios/Shops → district + has-pin check
 *   Magazine      → issue number, article count, PDF built?
 *   Trails        → stop count · Competitions → entry count
 *
 * Taxonomy columns (type, kind, gear, level) come from the taxonomies
 * themselves via show_admin_column; the dashboard "At a Glance" widget
 * learns the main content types.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Pin-carrying types share the column pair · keys per type ───── */
const VPG_COL_GEO = [
    'vpg_location' => [ 'lat' => 'location_lat', 'district' => 'location_district' ],
    'vpg_studio'   => [ 'lat' => 'studio_lat',   'district' => 'location_district' ],
    'vpg_shop'     => [ 'lat' => 'shop_lat',     'district' => 'shop_district' ],
];

add_action( 'admin_init', function () {
    foreach ( array_keys( VPG_COL_GEO ) as $type ) {
        add_filter( "manage_{$type}_posts_columns", function ( $cols ) {
            $date = $cols['date'] ?? null;
            unset( $cols['date'] );
            $cols['vpg_district'] = __( 'District', 'vpg-v2' );
            $cols['vpg_pin']      = __( 'Pin', 'vpg-v2' );
            if ( $date ) $cols['date'] = $date;
            return $cols;
        } );
        add_action( "manage_{$type}_posts_custom_column", function ( $col, $id ) use ( $type ) {
            $keys = VPG_COL_GEO[ $type ];
            if ( $col === 'vpg_district' ) {
                echo esc_html( get_post_meta( $id, $keys['district'], true ) ?: '—' );
            }
            if ( $col === 'vpg_pin' ) {
                echo get_post_meta( $id, $keys['lat'], true )
                    ? '<span style="color:#00a32a" title="' . esc_attr__( 'On the map', 'vpg-v2' ) . '">✓</span>'
                    : '<span style="color:#d63638" title="' . esc_attr__( 'No coordinates yet', 'vpg-v2' ) . '">—</span>';
            }
        }, 10, 2 );
    }
} );

/* ─── Events · date + venue + RSVPs, sortable by the actual date ── */
add_filter( 'manage_vpg_event_posts_columns', function ( $cols ) {
    $date = $cols['date'] ?? null;
    unset( $cols['date'] );
    $cols['vpg_when']  = __( 'Event date', 'vpg-v2' );
    $cols['vpg_venue'] = __( 'Venue', 'vpg-v2' );
    $cols['vpg_rsvp']  = __( 'RSVPs', 'vpg-v2' );
    if ( $date ) $cols['date'] = $date;
    return $cols;
} );
add_action( 'manage_vpg_event_posts_custom_column', function ( $col, $id ) {
    if ( $col === 'vpg_when' ) {
        $d  = get_post_meta( $id, '_vpg_event_date', true );
        $ts = $d ? strtotime( $d ) : false;
        if ( ! $ts ) { echo '—'; return; }
        $past = $ts < strtotime( 'today' );
        echo '<span style="' . ( $past ? 'color:#8c8f94' : 'font-weight:600' ) . '">' . esc_html( date_i18n( 'D, j. M Y', $ts ) ) . '</span>';
    }
    if ( $col === 'vpg_venue' ) echo esc_html( get_post_meta( $id, '_vpg_event_venue', true ) ?: '—' );
    if ( $col === 'vpg_rsvp' )  echo (int) count( function_exists( 'vpg_event_rsvps' ) ? vpg_event_rsvps( $id ) : [] );
}, 10, 2 );
add_filter( 'manage_edit-vpg_event_sortable_columns', function ( $cols ) {
    $cols['vpg_when'] = 'vpg_when';
    return $cols;
} );
add_action( 'pre_get_posts', function ( $q ) {
    if ( ! is_admin() || ! $q->is_main_query() ) return;
    if ( $q->get( 'orderby' ) === 'vpg_when' ) {
        $q->set( 'meta_key', '_vpg_event_date' );
        $q->set( 'orderby', 'meta_value' );
    }
} );

/* ─── Magazine issues · number, articles, PDF state ─────────────── */
add_filter( 'manage_vpg_magazine_posts_columns', function ( $cols ) {
    $date = $cols['date'] ?? null;
    unset( $cols['date'] );
    $cols['vpg_issue']    = __( 'Issue', 'vpg-v2' );
    $cols['vpg_articles'] = __( 'Articles', 'vpg-v2' );
    $cols['vpg_pdf']      = __( 'PDF', 'vpg-v2' );
    if ( $date ) $cols['date'] = $date;
    return $cols;
} );
add_action( 'manage_vpg_magazine_posts_custom_column', function ( $col, $id ) {
    if ( $col === 'vpg_issue' ) echo esc_html( get_post_meta( $id, '_vpg_issue_number', true ) ?: '—' );
    if ( $col === 'vpg_articles' ) {
        $a = json_decode( (string) get_post_meta( $id, '_vpg_articles', true ), true );
        echo is_array( $a ) ? count( $a ) : 0;
    }
    if ( $col === 'vpg_pdf' ) {
        $url = get_post_meta( $id, '_vpg_pdf_url', true );
        echo $url
            ? '<a href="' . esc_url( $url ) . '" target="_blank">✓ ' . esc_html__( 'open', 'vpg-v2' ) . '</a>'
            : '<span style="color:#8c8f94">—</span>';
    }
}, 10, 2 );

/* ─── Trails · stop count · Competitions · entry count ──────────── */
add_filter( 'manage_vpg_trail_posts_columns', function ( $cols ) {
    $cols['vpg_stops'] = __( 'Stops', 'vpg-v2' );
    return $cols;
} );
add_action( 'manage_vpg_trail_posts_custom_column', function ( $col, $id ) {
    if ( $col === 'vpg_stops' ) {
        echo function_exists( 'vpg_trail_stops' ) ? count( vpg_trail_stops( $id ) ) : '—';
    }
}, 10, 2 );

add_filter( 'manage_vpg_competition_posts_columns', function ( $cols ) {
    $cols['vpg_entries'] = __( 'Entries', 'vpg-v2' );
    return $cols;
} );
add_action( 'manage_vpg_competition_posts_custom_column', function ( $col, $id ) {
    if ( $col === 'vpg_entries' ) {
        echo function_exists( 'vpg_competition_entries' ) ? count( vpg_competition_entries( $id ) ) : '—';
    }
}, 10, 2 );

/* ─── List filters · type on locations, kind on events ──────────── */
add_action( 'restrict_manage_posts', function ( $post_type ) {
    $map = [ 'vpg_location' => 'location_type', 'vpg_event' => 'event_kind' ];
    if ( empty( $map[ $post_type ] ) ) return;
    $tax = get_taxonomy( $map[ $post_type ] );
    if ( ! $tax ) return;
    wp_dropdown_categories( [
        'taxonomy'        => $tax->name,
        'name'            => $tax->name,
        'value_field'     => 'slug',
        'selected'        => sanitize_key( $_GET[ $tax->name ] ?? '' ),
        'show_option_all' => $tax->labels->all_items,
        'hide_empty'      => true,
        'hierarchical'    => true,
    ] );
} );

/* ─── "At a Glance" · the dashboard learns the main content types ── */
add_filter( 'dashboard_glance_items', function ( $items ) {
    foreach ( [ 'vpg_location', 'vpg_event', 'vpg_magazine' ] as $type ) {
        $obj = get_post_type_object( $type );
        $n   = (int) ( wp_count_posts( $type )->publish ?? 0 );
        if ( ! $obj || ! $n ) continue;
        $items[] = sprintf(
            '<a class="%s-count" href="%s">%s %s</a>',
            esc_attr( $type ),
            esc_url( admin_url( 'edit.php?post_type=' . $type ) ),
            $n,
            esc_html( $obj->labels->name )
        );
    }
    return $items;
} );
