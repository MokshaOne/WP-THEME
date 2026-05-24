<?php
/**
 * on1.agency · skin admin editor
 *
 * Adds a "Skins" submenu under the On1 Hub admin page.
 * For each of the 17 skins you can:
 *   - Enable / disable (show in picker?)
 *   - Rename the picker label
 *   - Override every color token
 *   - Re-order the picker pills (drag-to-reorder list)
 *
 * All edits write to wp_options['on1_skins'][slug].
 * Reset-to-defaults link per skin discards admin overrides for that skin.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Menu registration ─────────────────────────────────────── */
add_action( 'admin_menu', function () {
    add_submenu_page(
        'on1-hub',           // parent slug (On1 Hub root)
        'Skins',             // page title
        '🎨 Skins',          // menu label
        'manage_options',
        'on1-skins',
        'on1_render_skin_admin'
    );
}, 30 );

/* ─── Save handler ──────────────────────────────────────────── */
add_action( 'admin_post_on1_save_skins', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'on1_save_skins' );

    $registry = on1_skin_registry();
    $saved    = [];

    if ( isset( $_POST['skins'] ) && is_array( $_POST['skins'] ) ) {
        $i = 0;
        foreach ( $_POST['skins'] as $slug => $row ) {
            if ( ! isset( $registry[ $slug ] ) ) continue;

            $tokens = [];
            if ( isset( $row['tokens'] ) && is_array( $row['tokens'] ) ) {
                foreach ( $row['tokens'] as $k => $v ) {
                    $v = trim( wp_unslash( (string) $v ) );
                    if ( $v !== '' ) $tokens[ sanitize_key( $k ) ] = sanitize_text_field( $v );
                }
            }

            $swatch_csv = isset( $row['swatch'] ) ? trim( wp_unslash( (string) $row['swatch'] ) ) : '';
            $swatch     = $swatch_csv !== ''
                ? array_values( array_filter( array_map( 'trim', explode( ',', $swatch_csv ) ) ) )
                : $registry[ $slug ]['swatch'];

            $saved[ $slug ] = [
                'enabled' => ! empty( $row['enabled'] ),
                'label'   => sanitize_text_field( wp_unslash( $row['label'] ?? '' ) ),
                'swatch'  => $swatch,
                'tokens'  => $tokens,
                'order'   => $i * 10,
            ];
            $i++;
        }
    }

    update_option( 'on1_skins', $saved );

    wp_safe_redirect( admin_url( 'admin.php?page=on1-skins&saved=1' ) );
    exit;
} );

/* ─── Reset one skin to defaults ────────────────────────────── */
add_action( 'admin_post_on1_reset_skin', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'on1_reset_skin' );

    $slug    = sanitize_key( $_GET['slug'] ?? '' );
    $current = get_option( 'on1_skins', [] );
    if ( $slug && isset( $current[ $slug ] ) ) {
        unset( $current[ $slug ] );
        update_option( 'on1_skins', $current );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=on1-skins&reset=' . $slug ) );
    exit;
} );

/* ─── Export config as JSON ─────────────────────────────────── */
add_action( 'admin_post_on1_export_skins', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'on1_export_skins' );

    $skins = on1_get_skins();
    $json  = wp_json_encode( $skins, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=on1-skins-' . gmdate( 'Y-m-d' ) . '.json' );
    echo $json;
    exit;
} );

/* ─── Import config from JSON ───────────────────────────────── */
add_action( 'admin_post_on1_import_skins', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'on1_import_skins' );

    if ( empty( $_FILES['file']['tmp_name'] ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=on1-skins&import=fail' ) );
        exit;
    }

    $raw  = file_get_contents( $_FILES['file']['tmp_name'] );
    $data = json_decode( $raw, true );
    if ( ! is_array( $data ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=on1-skins&import=fail' ) );
        exit;
    }

    $registry = on1_skin_registry();
    $saved    = [];
    foreach ( $data as $slug => $row ) {
        if ( ! isset( $registry[ $slug ] ) ) continue;
        $saved[ $slug ] = [
            'enabled' => ! empty( $row['enabled'] ),
            'label'   => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '',
            'swatch'  => isset( $row['swatch'] ) && is_array( $row['swatch'] ) ? $row['swatch'] : [],
            'tokens'  => isset( $row['tokens'] ) && is_array( $row['tokens'] ) ? $row['tokens'] : [],
            'order'   => isset( $row['order'] ) ? (int) $row['order'] : 0,
        ];
    }
    update_option( 'on1_skins', $saved );

    wp_safe_redirect( admin_url( 'admin.php?page=on1-skins&import=ok' ) );
    exit;
} );

/* ─── The admin page itself ─────────────────────────────────── */
function on1_render_skin_admin() {
    $skins = on1_get_skins();
    ?>
    <div class="wrap on1-skin-admin">
        <h1>🎨 Skin manager <small style="opacity:.55;font-weight:400">— 17 visitor-facing design variants</small></h1>

        <?php if ( isset( $_GET['saved'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Saved.</strong></p></div>
        <?php elseif ( isset( $_GET['reset'] ) ) : ?>
            <div class="notice notice-info is-dismissible"><p><strong><?php echo esc_html( $_GET['reset'] ); ?></strong> reset to defaults.</p></div>
        <?php elseif ( isset( $_GET['import'] ) ) : ?>
            <div class="notice notice-<?php echo $_GET['import'] === 'ok' ? 'success' : 'error'; ?> is-dismissible">
                <p><strong>Import <?php echo esc_html( $_GET['import'] ); ?>.</strong></p>
            </div>
        <?php endif; ?>

        <p style="max-width:780px;line-height:1.55">
            Toggle each skin on/off (appears in the visitor-facing design picker), rename its label,
            override any color token, and drag rows to re-order. Reset returns a skin to its shipped defaults.
        </p>

        <div class="on1-skin-admin__bar">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:1rem">
                <?php wp_nonce_field( 'on1_export_skins' ); ?>
                <input type="hidden" name="action" value="on1_export_skins">
                <button class="button">⬇ Export JSON</button>
            </form>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="display:inline-block">
                <?php wp_nonce_field( 'on1_import_skins' ); ?>
                <input type="hidden" name="action" value="on1_import_skins">
                <input type="file" name="file" accept="application/json" required>
                <button class="button">⬆ Import JSON</button>
            </form>
        </div>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'on1_save_skins' ); ?>
            <input type="hidden" name="action" value="on1_save_skins">

            <table class="widefat striped on1-skin-table">
                <thead>
                    <tr>
                        <th style="width:24px"></th>
                        <th style="width:120px">Slug</th>
                        <th>Label in picker</th>
                        <th>Swatch</th>
                        <th>Color tokens</th>
                        <th style="width:50px">On</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="on1-skin-rows">
                    <?php foreach ( $skins as $slug => $s ) : ?>
                        <tr data-slug="<?php echo esc_attr( $slug ); ?>">
                            <td class="on1-drag" title="Drag to reorder">⋮⋮</td>
                            <td>
                                <code><?php echo esc_html( $slug ); ?></code>
                                <div style="font-size:11px;opacity:.55;margin-top:.2em"><?php echo esc_html( $s['group'] ); ?></div>
                            </td>
                            <td>
                                <input type="text" name="skins[<?php echo esc_attr( $slug ); ?>][label]"
                                       value="<?php echo esc_attr( $s['label'] ); ?>" style="width:100%">
                            </td>
                            <td style="vertical-align:top">
                                <div class="on1-swatch-row">
                                    <?php foreach ( $s['swatch'] as $col ) : ?>
                                        <span class="on1-swatch-chip" style="background:<?php echo esc_attr( $col ); ?>"
                                              title="<?php echo esc_attr( $col ); ?>"></span>
                                    <?php endforeach; ?>
                                </div>
                                <input type="text" name="skins[<?php echo esc_attr( $slug ); ?>][swatch]"
                                       value="<?php echo esc_attr( implode( ',', $s['swatch'] ) ); ?>"
                                       style="width:100%;font-family:ui-monospace,monospace;font-size:11px"
                                       placeholder="#hex,#hex,#hex">
                            </td>
                            <td>
                                <details>
                                    <summary>Edit <?php echo count( $s['tokens'] ); ?> tokens</summary>
                                    <div class="on1-tokens-grid">
                                        <?php foreach ( $s['tokens'] as $k => $v ) : ?>
                                            <label>
                                                <span><?php echo esc_html( $k ); ?></span>
                                                <input type="text"
                                                       name="skins[<?php echo esc_attr( $slug ); ?>][tokens][<?php echo esc_attr( $k ); ?>]"
                                                       value="<?php echo esc_attr( $v ); ?>">
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </details>
                            </td>
                            <td style="text-align:center">
                                <label class="on1-toggle">
                                    <input type="checkbox"
                                           name="skins[<?php echo esc_attr( $slug ); ?>][enabled]"
                                           value="1" <?php checked( $s['enabled'] ); ?>>
                                </label>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( wp_nonce_url(
                                    admin_url( 'admin-post.php?action=on1_reset_skin&slug=' . $slug ),
                                    'on1_reset_skin'
                                ) ); ?>" onclick="return confirm('Reset <?php echo esc_js( $slug ); ?> to defaults?')" class="button-link-delete">Reset</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:1.5rem">
                <button type="submit" class="button button-primary button-large">Save all changes</button>
            </p>
        </form>
    </div>

    <style>
        .on1-skin-admin h1 small { font-size: 0.6em; }
        .on1-skin-admin__bar { margin:1.5rem 0; padding:1rem; background:#fff; border:1px solid #ccd0d4; }
        .on1-skin-admin__bar form input[type=file] { margin-right:.5rem; }
        .on1-skin-table { margin-top:1rem; }
        .on1-skin-table td { vertical-align: top; padding:.8rem; }
        .on1-drag { cursor: grab; text-align:center; opacity:.4; font-size:14px; user-select:none; }
        .on1-drag:hover { opacity:.9; }
        tr.on1-dragging { opacity:.4; }
        tr.on1-drag-over { background:#e7f3ff; }
        .on1-swatch-row { display:flex; gap:4px; margin-bottom:.4rem; }
        .on1-swatch-chip { width:18px; height:18px; border-radius:50%; border:1px solid rgba(0,0,0,0.15); display:inline-block; }
        .on1-tokens-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; margin-top:.6rem; padding:.6rem; background:#f6f7f7; border-radius:4px; }
        .on1-tokens-grid label { display:flex; flex-direction:column; gap:.2rem; font-size:11px; }
        .on1-tokens-grid label span { font-family:ui-monospace,monospace; color:#646970; }
        .on1-tokens-grid label input { font-family:ui-monospace,monospace; font-size:11px; padding:.3rem .4rem; }
        .on1-toggle input { width:20px; height:20px; cursor:pointer; }
    </style>

    <script>
    // Drag-to-reorder rows (HTML5 native)
    (function () {
        var tbody = document.getElementById('on1-skin-rows');
        if (!tbody) return;
        Array.prototype.forEach.call(tbody.querySelectorAll('tr'), function (row) {
            row.draggable = true;
            row.addEventListener('dragstart', function (e) {
                row.classList.add('on1-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.dataset.slug);
            });
            row.addEventListener('dragend', function () {
                row.classList.remove('on1-dragging');
                Array.prototype.forEach.call(tbody.querySelectorAll('tr'), function (r) { r.classList.remove('on1-drag-over'); });
            });
            row.addEventListener('dragover', function (e) { e.preventDefault(); row.classList.add('on1-drag-over'); });
            row.addEventListener('dragleave', function () { row.classList.remove('on1-drag-over'); });
            row.addEventListener('drop', function (e) {
                e.preventDefault();
                var dragging = tbody.querySelector('.on1-dragging');
                if (dragging && dragging !== row) {
                    var rect = row.getBoundingClientRect();
                    var after = (e.clientY - rect.top) > (rect.height / 2);
                    tbody.insertBefore(dragging, after ? row.nextSibling : row);
                }
            });
        });
    })();
    </script>
    <?php
}
