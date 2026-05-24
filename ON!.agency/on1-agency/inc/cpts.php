<?php
/**
 * on1.agency · v4 · cpts.php
 *
 * Project = a single case study. Featured image is the cover image
 * shown on the homepage and the case detail page.
 *
 * Custom meta per project:
 *   _on1_lede        : italic short lede shown under the title
 *   _on1_year        : MMXXVI / 2026 / etc
 *   _on1_for         : "Eelam Tamil poet, AT"
 *   _on1_live_url    : the live URL of the case
 *   _on1_tag         : "Live" / "Case study" / etc.
 *   _on1_handle      : "[ Kavithai · v2.1 · Relief ]"
 *   _on1_featured    : '1' if shown as featured on the homepage
 *   _on1_meta        : array of [label, value] rows for the case meta
 */
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'init', function() {
    register_post_type( 'project', [
        'labels' => [
            'name'          => __( 'Case studies', 'on1-agency' ),
            'singular_name' => __( 'Case study', 'on1-agency' ),
            'add_new'       => __( 'New case', 'on1-agency' ),
            'add_new_item'  => __( 'Add a new case', 'on1-agency' ),
            'edit_item'     => __( 'Edit case', 'on1-agency' ),
            'all_items'     => __( 'All cases', 'on1-agency' ),
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => [ 'slug' => 'work', 'with_front' => false ],
        'menu_icon'     => 'dashicons-portfolio',
        'menu_position' => 5,
        'supports'      => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'show_in_rest'  => true,
    ] );
} );


/* ─── Meta boxes for case meta ───────────────────────────── */
add_action( 'add_meta_boxes', function() {
    add_meta_box( 'on1_project_meta', __( 'Case study details', 'on1-agency' ), 'on1_project_meta_cb', 'project', 'normal', 'high' );
} );

function on1_project_meta_cb( $post ) {
    wp_nonce_field( 'on1_project_meta', 'on1_project_nonce' );
    $lede     = get_post_meta( $post->ID, '_on1_lede', true );
    $year     = get_post_meta( $post->ID, '_on1_year', true );
    $for      = get_post_meta( $post->ID, '_on1_for', true );
    $live_url = get_post_meta( $post->ID, '_on1_live_url', true );
    $tag      = get_post_meta( $post->ID, '_on1_tag', true );
    $handle   = get_post_meta( $post->ID, '_on1_handle', true );
    $featured = get_post_meta( $post->ID, '_on1_featured', true );
    $meta     = get_post_meta( $post->ID, '_on1_meta', true );
    if ( ! is_array( $meta ) ) $meta = [];

    $field = function( $label, $name, $value, $type = 'text', $help = '' ) {
        $value = esc_attr( $value );
        echo '<p style="margin:14px 0 4px;font-size:13px;color:#888;letter-spacing:0.04em;">' . esc_html( $label ) . '</p>';
        if ( $type === 'textarea' ) {
            echo '<textarea name="' . esc_attr( $name ) . '" style="width:100%;padding:8px;font-family:Inter,system-ui,sans-serif;font-size:14px;min-height:60px;" rows="3">' . esc_textarea( $value ) . '</textarea>';
        } elseif ( $type === 'checkbox' ) {
            echo '<label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . ( $value === '1' ? ' checked' : '' ) . '> ' . esc_html( $help ) . '</label>';
            return;
        } else {
            echo '<input type="text" name="' . esc_attr( $name ) . '" value="' . $value . '" style="width:100%;padding:8px;font-family:Inter,system-ui,sans-serif;font-size:14px;">';
        }
        if ( $help ) echo '<small style="color:#666;font-style:italic;">' . on1_em( $help ) . '</small>';
    };
    ?>
    <style>
    #on1_project_meta .inside { padding: 8px 16px 16px; }
    #on1_project_meta input, #on1_project_meta textarea { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; }
    .on1-meta-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; margin-bottom: 6px; align-items: center; }
    .on1-meta-row input { padding: 6px 8px !important; font-size: 13px !important; }
    .on1-meta-add { margin-top: 6px; }
    </style>
    <?php
    $field( 'Italic lede (1 sentence)', 'on1_lede', $lede, 'textarea', 'Allows &lt;em&gt;…&lt;/em&gt; for italic accents.' );
    $field( 'Year', 'on1_year', $year, 'text', 'Free-form, e.g. "MMXXVI" or "2026".' );
    $field( 'Client / For', 'on1_for', $for );
    $field( 'Live URL', 'on1_live_url', $live_url );
    $field( 'Tag (corner badge)', 'on1_tag', $tag, 'text', 'e.g. "Live", "WIP", "Case study"' );
    $field( 'Handle (image overlay text)', 'on1_handle', $handle, 'text', 'e.g. "[ Kavithai · v2.1 · Relief ]"' );
    $field( 'Featured on homepage?', 'on1_featured', $featured, 'checkbox', 'Tick to feature as the lead case study.' );
    ?>

    <p style="margin: 20px 0 6px; font-size: 13px; color: #888; letter-spacing: 0.04em;">Meta rows (label / value)</p>
    <small style="color: #666; font-style: italic; display: block; margin-bottom: 10px;">Up to 6 rows. Both fields support &lt;em&gt;…&lt;/em&gt;. Empty rows are dropped on save.</small>
    <div id="on1-meta-rows">
        <?php for ( $i = 0; $i < 6; $i++ ) :
            $row = $meta[ $i ] ?? [ 'label' => '', 'value' => '' ];
        ?>
        <div class="on1-meta-row">
            <input type="text" name="on1_meta_label[]" placeholder="Label (e.g., For, Built in, Outcome)" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>">
            <input type="text" name="on1_meta_value[]" placeholder="Value (em allowed)" value="<?php echo esc_attr( $row['value'] ?? '' ); ?>">
            <span style="color: #aaa; font-family: monospace; font-size: 11px;">#<?php echo $i + 1; ?></span>
        </div>
        <?php endfor; ?>
    </div>
    <?php
}

add_action( 'save_post_project', function( $post_id ) {
    if ( ! isset( $_POST['on1_project_nonce'] ) || ! wp_verify_nonce( $_POST['on1_project_nonce'], 'on1_project_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $text_keys = [ 'on1_year', 'on1_for', 'on1_tag', 'on1_handle' ];
    foreach ( $text_keys as $k ) {
        if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, '_' . $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
    }
    if ( isset( $_POST['on1_lede'] ) ) {
        update_post_meta( $post_id, '_on1_lede', sanitize_textarea_field( wp_unslash( $_POST['on1_lede'] ) ) );
    }
    if ( isset( $_POST['on1_live_url'] ) ) {
        update_post_meta( $post_id, '_on1_live_url', esc_url_raw( wp_unslash( $_POST['on1_live_url'] ) ) );
    }
    update_post_meta( $post_id, '_on1_featured', isset( $_POST['on1_featured'] ) ? '1' : '0' );

    // Meta rows
    $rows = [];
    if ( isset( $_POST['on1_meta_label'], $_POST['on1_meta_value'] ) && is_array( $_POST['on1_meta_label'] ) ) {
        $count = count( $_POST['on1_meta_label'] );
        for ( $i = 0; $i < $count; $i++ ) {
            $label = sanitize_text_field( wp_unslash( $_POST['on1_meta_label'][ $i ] ?? '' ) );
            $value = sanitize_text_field( wp_unslash( $_POST['on1_meta_value'][ $i ] ?? '' ) );
            if ( $label === '' && $value === '' ) continue;
            $rows[] = [ 'label' => $label, 'value' => $value ];
        }
    }
    update_post_meta( $post_id, '_on1_meta', $rows );
} );
