<?php
/**
 * VPG v3 — CPT custom fields.
 *
 * One schema, two renderers:
 *   • If Advanced Custom Fields is active → registered as ACF local field
 *     groups (rich UI, import/export, conditional logic ready).
 *   • If ACF is NOT installed → a compact native meta box renders the same
 *     inputs and saves to the same meta keys.
 *
 * Either way the values live in plain post meta under the keys the CPT
 * templates already read via vpg_field(), so the front end is identical.
 *
 * NOT defined here (owned elsewhere, to avoid a double UI):
 *   • location / studio / shop position + district → inc/location-meta.php
 *     (the Leaflet pin-picker meta box).
 *   • magazine issue number / date / PDF → inc/magazine-editor.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── The single source of truth for CPT fields ──────────────────────── */
function vpg_cpt_field_schema() {
    return [
        'vpg_event' => [
            'title'  => __( 'Event details', 'vpg-v2' ),
            'fields' => [
                [ 'key' => '_vpg_event_date',  'label' => __( 'Event date', 'vpg-v2' ),        'type' => 'date',   'instructions' => __( 'When it happens.', 'vpg-v2' ) ],
                [ 'key' => '_vpg_event_cap',   'label' => __( 'Capacity', 'vpg-v2' ),          'type' => 'number', 'instructions' => __( 'Max participants · 0 = unlimited. Full events run a waitlist.', 'vpg-v2' ) ],
                [ 'key' => '_vpg_event_venue', 'label' => __( 'Venue', 'vpg-v2' ),             'type' => 'text',   'instructions' => __( 'e.g. Spittelberg, 7. Bezirk', 'vpg-v2' ) ],
                [ 'key' => '_vpg_event_url',   'label' => __( 'Tickets / RSVP URL', 'vpg-v2' ), 'type' => 'url' ],
            ],
        ],
        'vpg_review' => [
            'title'  => __( 'Review scores', 'vpg-v2' ),
            'fields' => [
                [ 'key' => 'score_design',      'label' => __( 'Design (0–10)', 'vpg-v2' ),      'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1 ],
                [ 'key' => 'score_performance', 'label' => __( 'Performance (0–10)', 'vpg-v2' ), 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1 ],
                [ 'key' => 'score_price',       'label' => __( 'Value (0–10)', 'vpg-v2' ),       'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1 ],
            ],
        ],
        'vpg_location' => [
            'title'  => __( 'Location details', 'vpg-v2' ),
            'fields' => [
                [ 'key' => 'location_best_time', 'label' => __( 'Best time / light', 'vpg-v2' ), 'type' => 'text', 'instructions' => __( 'e.g. Golden hour · 06:00, or Blue hour in winter', 'vpg-v2' ) ],
            ],
        ],
        'vpg_studio' => [
            'title'  => __( 'Studio details', 'vpg-v2' ),
            'fields' => [
                [ 'key' => 'studio_hourly_rate', 'label' => __( 'Hourly rate', 'vpg-v2' ), 'type' => 'text',   'instructions' => __( 'e.g. €45 / h', 'vpg-v2' ) ],
                [ 'key' => 'studio_size',        'label' => __( 'Size (m²)', 'vpg-v2' ),    'type' => 'number', 'min' => 0 ],
            ],
        ],
        'vpg_shop' => [
            'title'  => __( 'Shop details', 'vpg-v2' ),
            'fields' => [
                [ 'key' => 'shop_hours', 'label' => __( 'Opening hours', 'vpg-v2' ),      'type' => 'text', 'instructions' => __( 'e.g. Mon–Fri 10–18', 'vpg-v2' ) ],
                [ 'key' => 'shop_focus', 'label' => __( 'Focus / specialty', 'vpg-v2' ),  'type' => 'text', 'instructions' => __( 'e.g. Film, labs, large format', 'vpg-v2' ) ],
            ],
        ],
        'vpg_tutorial' => [
            'title'  => __( 'Tutorial details', 'vpg-v2' ),
            'fields' => [
                [ 'key' => 'tutorial_minutes', 'label' => __( 'Read time (minutes)', 'vpg-v2' ), 'type' => 'number', 'min' => 0 ],
            ],
        ],
    ];
}

/* ════════════════════════════════════════════════════════════════════
 * A · ACF local field groups (only when the ACF plugin is active)
 * ══════════════════════════════════════════════════════════════════ */
add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    $type_map = [ 'date' => 'date_picker', 'text' => 'text', 'url' => 'url', 'number' => 'number' ];

    foreach ( vpg_cpt_field_schema() as $cpt => $group ) {
        $fields = [];
        foreach ( $group['fields'] as $f ) {
            $af = [
                'key'          => 'field_vpg_' . trim( $f['key'], '_' ),
                'label'        => $f['label'],
                'name'         => $f['key'],
                'type'         => $type_map[ $f['type'] ] ?? 'text',
                'instructions' => $f['instructions'] ?? '',
            ];
            if ( $f['type'] === 'date' )   { $af['return_format'] = 'Y-m-d'; $af['display_format'] = 'd.m.Y'; $af['first_day'] = 1; }
            if ( $f['type'] === 'number' ) { foreach ( [ 'min', 'max', 'step' ] as $k ) if ( isset( $f[ $k ] ) ) $af[ $k ] = $f[ $k ]; }
            $fields[] = $af;
        }
        acf_add_local_field_group( [
            'key'      => 'group_' . $cpt,
            'title'    => $group['title'],
            'fields'   => $fields,
            'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => $cpt ] ] ],
            'position' => 'normal',
            'style'    => 'default',
            'active'   => true,
        ] );
    }
} );

/* ════════════════════════════════════════════════════════════════════
 * B · Native meta-box fallback (only when ACF is NOT active)
 * ══════════════════════════════════════════════════════════════════ */
add_action( 'add_meta_boxes', function () {
    if ( vpg_acf_active() ) return;
    foreach ( vpg_cpt_field_schema() as $cpt => $group ) {
        add_meta_box( 'vpg_fields_' . $cpt, $group['title'], 'vpg_render_field_box', $cpt, 'normal', 'high', [ 'cpt' => $cpt ] );
    }
} );

function vpg_render_field_box( $post, $box ) {
    $cpt    = $box['args']['cpt'];
    $schema = vpg_cpt_field_schema();
    if ( empty( $schema[ $cpt ] ) ) return;
    wp_nonce_field( 'vpg_fields_save', 'vpg_fields_nonce' );
    echo '<div style="display:grid;gap:14px;padding:6px 0">';
    foreach ( $schema[ $cpt ]['fields'] as $f ) {
        $val  = get_post_meta( $post->ID, $f['key'], true );
        $id   = 'vpgf_' . trim( $f['key'], '_' );
        $type = $f['type'] === 'date' ? 'date' : ( $f['type'] === 'url' ? 'url' : ( $f['type'] === 'number' ? 'number' : 'text' ) );
        echo '<div style="display:grid;gap:4px">';
        echo '<label for="' . esc_attr( $id ) . '" style="font-weight:600">' . esc_html( $f['label'] ) . '</label>';
        printf(
            '<input type="%s" id="%s" name="vpg_fields[%s]" value="%s" %s style="max-width:420px;padding:6px 8px" />',
            esc_attr( $type ),
            esc_attr( $id ),
            esc_attr( $f['key'] ),
            esc_attr( $val ),
            $f['type'] === 'number' ? 'step="' . esc_attr( $f['step'] ?? 1 ) . '" min="' . esc_attr( $f['min'] ?? 0 ) . '"' : ''
        );
        if ( ! empty( $f['instructions'] ) ) {
            echo '<span style="color:#646970;font-size:12px">' . esc_html( $f['instructions'] ) . '</span>';
        }
        echo '</div>';
    }
    echo '</div>';
}

add_action( 'save_post', function ( $post_id ) {
    if ( vpg_acf_active() ) return;
    if ( ! isset( $_POST['vpg_fields_nonce'] ) || ! wp_verify_nonce( $_POST['vpg_fields_nonce'], 'vpg_fields_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $cpt    = get_post_type( $post_id );
    $schema = vpg_cpt_field_schema();
    if ( empty( $schema[ $cpt ] ) || empty( $_POST['vpg_fields'] ) || ! is_array( $_POST['vpg_fields'] ) ) return;

    $allowed = wp_list_pluck( $schema[ $cpt ]['fields'], 'type', 'key' );
    foreach ( $_POST['vpg_fields'] as $key => $raw ) {
        if ( ! isset( $allowed[ $key ] ) ) continue;
        $raw = wp_unslash( $raw );
        switch ( $allowed[ $key ] ) {
            case 'url':    $clean = esc_url_raw( $raw ); break;
            case 'number': $clean = $raw === '' ? '' : (string) floatval( $raw ); break;
            default:       $clean = sanitize_text_field( $raw );
        }
        if ( $clean === '' ) { delete_post_meta( $post_id, $key ); }
        else { update_post_meta( $post_id, $key, $clean ); }
    }
} );
