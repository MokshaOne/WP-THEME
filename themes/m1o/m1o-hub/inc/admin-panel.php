<?php
/**
 * M1O Hub · Admin · v2.0
 * Single-screen control panel for the link list.
 *
 * Edits:
 *   - Identity (name, tagline, location, status, est, email, CTA text)
 *   - Channels (group, title, host, URL, status; drag to reorder; add/delete)
 *   - Display toggles (system bar, group heads, status LEDs, CTA)
 *
 * Data:
 *   - Identity fields live as individual wp_options (m1o_identity_*)
 *   - Channels stored as a serialized array in wp_option 'm1o_channels'
 *   - Toggles stored as wp_options (m1o_show_*)
 */
if ( ! defined( 'ABSPATH' ) ) exit;


// ─────────────────────────────────────────────────────────────
// 1. ADD MENU
// ─────────────────────────────────────────────────────────────
add_action( 'admin_menu', function() {
    add_menu_page(
        'M1O Hub',
        'M1O Hub',
        'manage_options',
        'm1o-hub',
        'm1o_render_admin_page',
        'dashicons-shield',  // dark-themed icon
        3
    );
} );


// ─────────────────────────────────────────────────────────────
// 2. HANDLE POST
// ─────────────────────────────────────────────────────────────
add_action( 'admin_init', function() {
    if ( ! isset( $_POST['m1o_save'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( ! isset( $_POST['m1o_nonce'] ) || ! wp_verify_nonce( $_POST['m1o_nonce'], 'm1o_save' ) ) return;

    // Identity
    $ident_keys = [
        'm1o_identity_name', 'm1o_identity_tagline', 'm1o_identity_location',
        'm1o_identity_status', 'm1o_identity_est', 'm1o_identity_email', 'm1o_cta_text',
    ];
    foreach ( $ident_keys as $k ) {
        if ( isset( $_POST[ $k ] ) ) {
            $val = wp_unslash( $_POST[ $k ] );
            if ( $k === 'm1o_identity_email' ) {
                $val = sanitize_email( $val );
            } elseif ( $k === 'm1o_identity_tagline' ) {
                $val = sanitize_textarea_field( $val );
            } else {
                $val = sanitize_text_field( $val );
            }
            update_option( $k, $val );
        }
    }

    // Toggles
    foreach ( [ 'm1o_show_systembar', 'm1o_show_groups', 'm1o_show_leds', 'm1o_show_cta' ] as $t ) {
        update_option( $t, isset( $_POST[ $t ] ) ? '1' : '0' );
    }

    // Channels — array of rows.
    $channels = [];
    if ( isset( $_POST['ch_title'] ) && is_array( $_POST['ch_title'] ) ) {
        $count = count( $_POST['ch_title'] );
        for ( $i = 0; $i < $count; $i++ ) {
            $title = sanitize_text_field( wp_unslash( $_POST['ch_title'][ $i ] ?? '' ) );
            $url   = esc_url_raw( wp_unslash( $_POST['ch_url'][ $i ] ?? '' ) );
            if ( $title === '' || $url === '' ) continue; // skip empty rows
            $channels[] = [
                'group'  => sanitize_text_field( $_POST['ch_group'][ $i ] ?? 'work' ),
                'title'  => $title,
                'host'   => sanitize_text_field( wp_unslash( $_POST['ch_host'][ $i ] ?? '' ) ),
                'url'    => $url,
                'status' => sanitize_text_field( $_POST['ch_status'][ $i ] ?? 'active' ),
            ];
        }
    }
    update_option( 'm1o_channels', $channels );

    // Redirect back so refresh doesn't re-submit.
    wp_safe_redirect( add_query_arg( 'm1o_saved', '1', remove_query_arg( 'm1o_saved' ) ) );
    exit;
} );


// ─────────────────────────────────────────────────────────────
// 3. ENQUEUE FONTS FOR OUR ADMIN PAGE ONLY
// ─────────────────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'toplevel_page_m1o-hub' ) return;
    wp_enqueue_style(
        'm1o-admin-fonts',
        'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@400;500;700&display=swap',
        [], M1O_VERSION
    );
} );


// ─────────────────────────────────────────────────────────────
// 4. RENDER PAGE
// ─────────────────────────────────────────────────────────────
function m1o_render_admin_page() {
    $id          = m1o_get_identity();
    $channels    = m1o_get_channels();
    $group_lbls  = m1o_group_labels();
    $status_lbls = m1o_status_labels();
    $just_saved  = isset( $_GET['m1o_saved'] );

    $show_systembar = m1o_opt( 'm1o_show_systembar', '1' ) !== '0';
    $show_groups    = m1o_opt( 'm1o_show_groups',    '1' ) !== '0';
    $show_leds      = m1o_opt( 'm1o_show_leds',      '1' ) !== '0';
    $show_cta       = m1o_opt( 'm1o_show_cta',       '1' ) !== '0';
    ?>

    <style>
    /* ─────  Override WP admin chrome on our page  ───── */
    :root {
        --m1o-bg: #0A0A0A;
        --m1o-bg-soft: #131313;
        --m1o-bg-input: #1A1A1A;
        --m1o-fg: #FFFFFF;
        --m1o-mute: rgba(255, 255, 255, 0.50);
        --m1o-faint: rgba(255, 255, 255, 0.25);
        --m1o-rule: rgba(255, 255, 255, 0.12);
        --m1o-rule-strong: rgba(255, 255, 255, 0.30);
        --m1o-signal: #00FF66;
        --m1o-signal-d: #00B348;
        --m1o-danger: #FF3344;
        --m1o-mono: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        --m1o-display: 'Space Grotesk', system-ui, sans-serif;
    }

    .toplevel_page_m1o-hub #wpwrap,
    .toplevel_page_m1o-hub #wpcontent,
    .toplevel_page_m1o-hub #wpbody-content { background: var(--m1o-bg) !important; }
    .toplevel_page_m1o-hub .wrap { color: var(--m1o-fg); padding-bottom: 120px; }
    .toplevel_page_m1o-hub .update-nag,
    .toplevel_page_m1o-hub .notice,
    .toplevel_page_m1o-hub .updated { display: none !important; }

    .m1o-admin {
        max-width: 1080px;
        font-family: var(--m1o-mono);
        font-size: 14px;
        color: var(--m1o-fg);
    }
    .m1o-admin a { color: var(--m1o-fg); }
    .m1o-admin ::selection { background: var(--m1o-signal); color: var(--m1o-bg); }

    @keyframes m1o-blink { 50% { opacity: 0; } }
    .m1o-cursor::after { content: '_'; color: var(--m1o-signal); animation: m1o-blink 1s steps(2) infinite; }
    .m1o-signal { color: var(--m1o-signal); }
    .m1o-mute { color: var(--m1o-mute); }

    .m1o-page-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 2rem;
        flex-wrap: wrap;
        padding: 1rem 0 1.5rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--m1o-rule);
    }
    .m1o-page-head__title {
        font-family: var(--m1o-display);
        font-size: clamp(2rem, 5vw, 2.6rem);
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1;
        margin: 0;
        color: var(--m1o-fg);
    }
    .m1o-page-head__title small {
        display: block;
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.32em;
        text-transform: uppercase;
        color: var(--m1o-signal);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    .m1o-page-head__meta {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--m1o-mute);
        text-align: right;
    }
    .m1o-page-head__meta strong { display: block; color: var(--m1o-signal); font-weight: 500; }

    .m1o-saved-notice {
        background: rgba(0, 255, 102, 0.08);
        border: 1px solid var(--m1o-signal);
        color: var(--m1o-signal);
        padding: 0.8rem 1.2rem;
        margin-bottom: 2rem;
        font-family: var(--m1o-mono);
        font-size: 11px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
    }
    .m1o-saved-notice::before { content: '● '; }

    .m1o-panel {
        border: 1px solid var(--m1o-rule);
        background: var(--m1o-bg-soft);
        margin-bottom: 2rem;
    }
    .m1o-panel__head {
        display: flex;
        align-items: baseline;
        gap: 1rem;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid var(--m1o-rule);
    }
    .m1o-panel__label {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.32em;
        text-transform: uppercase;
        color: var(--m1o-fg);
        font-weight: 500;
    }
    .m1o-panel__label .m1o-signal { margin-right: 0.5em; }
    .m1o-panel__rule { flex: 1; height: 1px; background: var(--m1o-rule); }
    .m1o-panel__count {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--m1o-mute);
    }
    .m1o-panel__body { padding: 1.4rem; }

    .m1o-fields {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 0;
    }
    .m1o-field > label {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--m1o-mute);
        padding: 0.95rem 0.8rem 0.95rem 0;
        border-bottom: 1px dotted var(--m1o-rule);
        display: flex;
        align-items: center;
        font-weight: 500;
    }
    .m1o-field > label::before { content: '> '; color: var(--m1o-signal); margin-right: 0.4em; }
    .m1o-field > .m1o-input-wrap {
        border-bottom: 1px dotted var(--m1o-rule);
        padding: 0.45rem 0;
    }
    .m1o-input, .m1o-textarea, .m1o-select {
        width: 100%;
        font-family: var(--m1o-mono);
        font-size: 14px;
        background: transparent;
        border: none;
        color: var(--m1o-fg) !important;
        padding: 0.5rem 0;
        outline: none;
        box-shadow: none !important;
        letter-spacing: 0.02em;
        min-height: 0;
    }
    .m1o-input::placeholder, .m1o-textarea::placeholder { color: var(--m1o-faint); }
    .m1o-input:focus, .m1o-textarea:focus, .m1o-select:focus { color: var(--m1o-signal) !important; }
    .m1o-textarea { resize: vertical; min-height: 70px; line-height: 1.6; }
    .m1o-select {
        cursor: pointer;
        appearance: none;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 12px;
        padding-right: 2rem;
        background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%2300FF66' stroke-width='1.2' fill='none' stroke-linecap='square'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
    }
    .m1o-select option { background: var(--m1o-bg); color: var(--m1o-fg); }

    /* Channel rows */
    .m1o-channels-head {
        display: grid;
        grid-template-columns: 28px 56px 110px 1fr 1fr 100px 28px;
        gap: 0.6rem;
        padding: 0.6rem 0.4rem;
        font-family: var(--m1o-mono);
        font-size: 9px;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--m1o-mute);
        border-bottom: 1px solid var(--m1o-rule);
    }
    .m1o-channel-row {
        display: grid;
        grid-template-columns: 28px 56px 110px 1fr 1fr 100px 28px;
        gap: 0.6rem;
        padding: 0.5rem 0.4rem;
        border-bottom: 1px dotted var(--m1o-rule);
        align-items: center;
        transition: background .12s;
    }
    .m1o-channel-row:hover { background: rgba(0, 255, 102, 0.03); }
    .m1o-channel-row.dragging { opacity: 0.4; }
    .m1o-channel-row.drag-over { box-shadow: inset 0 2px 0 var(--m1o-signal); }

    .m1o-drag {
        font-family: var(--m1o-mono);
        font-size: 14px;
        color: var(--m1o-faint);
        cursor: grab;
        text-align: center;
        user-select: none;
    }
    .m1o-drag:hover { color: var(--m1o-signal); }
    .m1o-drag:active { cursor: grabbing; }

    .m1o-ch-num {
        font-family: var(--m1o-mono);
        font-size: 11px;
        letter-spacing: 0.14em;
        color: var(--m1o-signal);
        text-align: center;
    }
    .m1o-ch-input {
        background: transparent;
        border: none;
        font-family: var(--m1o-mono);
        font-size: 13px;
        color: var(--m1o-fg);
        padding: 0.4rem 0.3rem;
        outline: none;
        box-shadow: none !important;
        width: 100%;
        letter-spacing: 0.02em;
        min-height: 0;
    }
    .m1o-ch-input:focus {
        background: rgba(0, 255, 102, 0.05);
        color: var(--m1o-signal);
    }
    .m1o-ch-input::placeholder { color: var(--m1o-faint); }
    .m1o-ch-input--title { font-family: var(--m1o-display); font-size: 14px; font-weight: 500; }

    .m1o-ch-select {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        background: transparent;
        border: 1px solid var(--m1o-rule);
        color: var(--m1o-fg);
        padding: 0.35rem 1.6rem 0.35rem 0.5rem;
        outline: none;
        box-shadow: none !important;
        appearance: none;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L5 5L9 1' stroke='%2300FF66' stroke-width='1.2' fill='none' stroke-linecap='square'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.4rem center;
        min-height: 0;
    }
    .m1o-ch-select option { background: var(--m1o-bg); color: var(--m1o-fg); }

    .m1o-delete-btn {
        background: transparent;
        border: 1px solid transparent;
        color: var(--m1o-mute);
        font-family: var(--m1o-mono);
        font-size: 14px;
        cursor: pointer;
        padding: 0.3rem;
        text-align: center;
        width: 100%;
        transition: color .12s, border-color .12s;
        box-shadow: none !important;
    }
    .m1o-delete-btn:hover {
        color: var(--m1o-danger);
        border-color: var(--m1o-danger);
    }

    .m1o-add-row {
        margin-top: 1rem;
        width: 100%;
        font-family: var(--m1o-mono);
        font-size: 11px;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        background: transparent;
        border: 1px dashed var(--m1o-rule);
        color: var(--m1o-mute);
        padding: 1rem;
        cursor: pointer;
        transition: all .15s;
        box-shadow: none !important;
    }
    .m1o-add-row:hover {
        border-color: var(--m1o-signal);
        color: var(--m1o-signal);
        border-style: solid;
    }
    .m1o-add-row::before { content: '+ '; color: var(--m1o-signal); }

    /* Toggles */
    .m1o-toggle-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: center;
        padding: 0.9rem 0;
        border-bottom: 1px dotted var(--m1o-rule);
    }
    .m1o-toggle-row:last-child { border-bottom: none; }
    .m1o-toggle-row__label {
        font-family: var(--m1o-mono);
        font-size: 13px;
        color: var(--m1o-fg);
    }
    .m1o-toggle-row__label small {
        display: block;
        font-size: 10px;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--m1o-mute);
        margin-top: 0.2rem;
        font-weight: 400;
    }
    .m1o-toggle {
        position: relative;
        width: 44px; height: 22px;
        background: var(--m1o-bg-input);
        border: 1px solid var(--m1o-rule);
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }
    .m1o-toggle::after {
        content: '';
        position: absolute;
        top: 1px; left: 1px;
        width: 18px; height: 18px;
        background: var(--m1o-mute);
        transition: left .18s, background .18s;
    }
    .m1o-toggle.is-on {
        background: rgba(0, 255, 102, 0.1);
        border-color: var(--m1o-signal);
    }
    .m1o-toggle.is-on::after {
        left: 23px;
        background: var(--m1o-signal);
        box-shadow: 0 0 6px var(--m1o-signal);
    }
    .m1o-toggle input { display: none; }

    /* Save bar */
    .m1o-save-bar {
        position: fixed;
        bottom: 0;
        left: 160px; /* WP admin sidebar width */
        right: 0;
        background: var(--m1o-bg);
        border-top: 1px solid var(--m1o-signal);
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        z-index: 90;
        flex-wrap: wrap;
    }
    @media (max-width: 960px) {
        .m1o-save-bar { left: 36px; }
    }
    @media (max-width: 782px) {
        .m1o-save-bar { left: 0; }
    }
    .m1o-save-bar__status {
        font-family: var(--m1o-mono);
        font-size: 10px;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--m1o-mute);
    }
    .m1o-save-bar__status .m1o-signal { color: var(--m1o-signal); }
    .m1o-btn {
        font-family: var(--m1o-mono);
        font-size: 11px;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        background: transparent;
        border: 1px solid var(--m1o-rule);
        color: var(--m1o-fg);
        padding: 0.85rem 1.4rem;
        cursor: pointer;
        transition: all .15s;
        box-shadow: none !important;
        text-decoration: none;
        display: inline-block;
    }
    .m1o-btn:hover { border-color: var(--m1o-fg); color: var(--m1o-fg); }
    .m1o-btn--primary {
        background: var(--m1o-signal);
        color: var(--m1o-bg) !important;
        border-color: var(--m1o-signal);
    }
    .m1o-btn--primary::before { content: '▶ '; }
    .m1o-btn--primary:hover {
        background: var(--m1o-signal-d);
        border-color: var(--m1o-signal-d);
        color: var(--m1o-bg) !important;
    }

    @media (max-width: 900px) {
        .m1o-fields { grid-template-columns: 1fr; }
        .m1o-field > label { padding-bottom: 0.3rem; border-bottom: none; }
        .m1o-field > .m1o-input-wrap { padding: 0 0 0.6rem; border-bottom: 1px dotted var(--m1o-rule); margin-bottom: 0.5rem; }
        .m1o-channels-head { display: none; }
        .m1o-channel-row { grid-template-columns: 28px 1fr 28px; gap: 0.4rem; }
        .m1o-ch-num, .m1o-ch-select, .m1o-ch-input--host { display: none; }
    }
    </style>

    <div class="wrap m1o-admin">

        <header class="m1o-page-head">
            <h1 class="m1o-page-head__title">
                <small>// Control<span class="m1o-cursor"></span></small>
                Hub Settings.
            </h1>
            <div class="m1o-page-head__meta">
                Theme<br>
                <strong>M1O.HUB · v<?php echo esc_html( M1O_VERSION ); ?></strong>
            </div>
        </header>

        <?php if ( $just_saved ) : ?>
            <div class="m1o-saved-notice">Settings saved · transmission confirmed</div>
        <?php endif; ?>

        <form method="POST" action="" id="m1o-form">
            <?php wp_nonce_field( 'm1o_save', 'm1o_nonce' ); ?>

            <!-- IDENTITY -->
            <section class="m1o-panel">
                <div class="m1o-panel__head">
                    <span class="m1o-panel__label"><span class="m1o-signal">●</span> Identity</span>
                    <span class="m1o-panel__rule"></span>
                    <span class="m1o-panel__count">07 fields</span>
                </div>
                <div class="m1o-panel__body">
                    <div class="m1o-fields">
                        <div class="m1o-field">
                            <label for="f-name">Name</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-name" name="m1o_identity_name" type="text" value="<?php echo esc_attr( $id['name'] ); ?>"></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-tagline">Tagline</label>
                            <div class="m1o-input-wrap"><textarea class="m1o-textarea" id="f-tagline" name="m1o_identity_tagline" rows="3"><?php echo esc_textarea( $id['tagline'] ); ?></textarea></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-location">Location</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-location" name="m1o_identity_location" type="text" value="<?php echo esc_attr( $id['location'] ); ?>"></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-status">Status</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-status" name="m1o_identity_status" type="text" value="<?php echo esc_attr( $id['status'] ); ?>" placeholder="Online · Available"></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-est">Est. since</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-est" name="m1o_identity_est" type="text" value="<?php echo esc_attr( $id['est'] ); ?>" placeholder="MMXVIII"></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-email">Email</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-email" name="m1o_identity_email" type="email" value="<?php echo esc_attr( $id['email'] ); ?>"></div>
                        </div>
                        <div class="m1o-field">
                            <label for="f-cta">CTA text</label>
                            <div class="m1o-input-wrap"><input class="m1o-input" id="f-cta" name="m1o_cta_text" type="text" value="<?php echo esc_attr( $id['cta'] ); ?>" placeholder="Send a signal"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CHANNELS -->
            <section class="m1o-panel">
                <div class="m1o-panel__head">
                    <span class="m1o-panel__label"><span class="m1o-signal">●</span> Channels</span>
                    <span class="m1o-panel__rule"></span>
                    <span class="m1o-panel__count"><span id="m1o-ch-count"><?php echo sprintf( '%02d', count( $channels ) ); ?></span> routed · drag to reorder</span>
                </div>
                <div class="m1o-panel__body">
                    <div class="m1o-channels-head">
                        <span></span><span>Nº</span><span>Group</span><span>Title</span><span>Host</span><span>Status</span><span></span>
                    </div>

                    <div id="m1o-ch-rows">
                        <?php foreach ( $channels as $i => $ch ) :
                            $num = $i + 1;
                            $g   = $ch['group'] ?? 'work';
                            $st  = $ch['status'] ?? 'active';
                        ?>
                        <div class="m1o-channel-row" draggable="true">
                            <span class="m1o-drag" title="Drag to reorder">⋮⋮</span>
                            <span class="m1o-ch-num">CH <?php echo sprintf( '%02d', $num ); ?></span>
                            <select class="m1o-ch-select" name="ch_group[]">
                                <?php foreach ( $group_lbls as $k => $lbl ) : ?>
                                    <option value="<?php echo esc_attr( $k ); ?>"<?php selected( $g, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input class="m1o-ch-input m1o-ch-input--title" name="ch_title[]" type="text" value="<?php echo esc_attr( $ch['title'] ?? '' ); ?>" placeholder="Title">
                            <input class="m1o-ch-input m1o-ch-input--host" name="ch_host[]" type="text" value="<?php echo esc_attr( $ch['host'] ?? '' ); ?>" placeholder="Host or descriptor">
                            <select class="m1o-ch-select" name="ch_status[]">
                                <?php foreach ( $status_lbls as $k => $lbl ) : ?>
                                    <option value="<?php echo esc_attr( $k ); ?>"<?php selected( $st, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="ch_url[]" value="<?php echo esc_attr( $ch['url'] ?? '' ); ?>" class="m1o-ch-url-hidden">
                            <button type="button" class="m1o-delete-btn" data-delete title="Remove">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- URL editor row appears below the row table since URL doesn't fit visually -->
                    <p style="font-family: var(--m1o-mono); font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--m1o-mute); margin-top: 1rem;">
                        > Click a row's title to also edit its URL. Each channel's URL is stored separately.
                    </p>

                    <button type="button" class="m1o-add-row" id="m1o-add-row">Add channel</button>
                </div>
            </section>

            <!-- DISPLAY TOGGLES -->
            <section class="m1o-panel">
                <div class="m1o-panel__head">
                    <span class="m1o-panel__label"><span class="m1o-signal">●</span> Display</span>
                    <span class="m1o-panel__rule"></span>
                    <span class="m1o-panel__count">04 toggles</span>
                </div>
                <div class="m1o-panel__body">
                    <?php
                    $toggles = [
                        [ 'key' => 'm1o_show_systembar', 'label' => 'System bar', 'desc' => 'Top status strip with location + date', 'on' => $show_systembar ],
                        [ 'key' => 'm1o_show_groups',    'label' => 'Group headers', 'desc' => '"// Work" labels between channel groups', 'on' => $show_groups ],
                        [ 'key' => 'm1o_show_leds',      'label' => 'Status LEDs', 'desc' => '● Active / ○ Idle dots on every channel', 'on' => $show_leds ],
                        [ 'key' => 'm1o_show_cta',       'label' => 'Contact CTA', 'desc' => '"▶ Send a signal" block at the bottom', 'on' => $show_cta ],
                    ];
                    foreach ( $toggles as $t ) : ?>
                        <div class="m1o-toggle-row">
                            <div class="m1o-toggle-row__label">
                                <?php echo esc_html( $t['label'] ); ?>
                                <small><?php echo esc_html( $t['desc'] ); ?></small>
                            </div>
                            <label class="m1o-toggle <?php echo $t['on'] ? 'is-on' : ''; ?>" data-toggle="<?php echo esc_attr( $t['key'] ); ?>">
                                <input type="checkbox" name="<?php echo esc_attr( $t['key'] ); ?>" value="1" <?php checked( $t['on'] ); ?>>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- SAVE BAR -->
            <div class="m1o-save-bar">
                <div class="m1o-save-bar__status">
                    <span class="m1o-signal">●</span> Ready · <?php echo sprintf( '%02d', count( $channels ) ); ?> channels · v<?php echo esc_html( M1O_VERSION ); ?>
                </div>
                <div style="display: flex; gap: 0.6rem;">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="m1o-btn">View hub ↗</a>
                    <button type="submit" name="m1o_save" value="1" class="m1o-btn m1o-btn--primary">Save now</button>
                </div>
            </div>
        </form>
    </div>

    <!-- A modal-style URL editor for editing each channel's URL -->
    <div id="m1o-url-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 999; align-items: center; justify-content: center; padding: 2rem;">
        <div style="background: var(--m1o-bg-soft); border: 1px solid var(--m1o-signal); padding: 2rem; max-width: 540px; width: 100%; font-family: var(--m1o-mono);">
            <p style="font-size: 10px; letter-spacing: 0.32em; text-transform: uppercase; color: var(--m1o-mute); margin-bottom: 1rem;">> Edit URL · <span id="m1o-url-modal-title" style="color: var(--m1o-fg);"></span></p>
            <input id="m1o-url-modal-input" type="url" placeholder="https://" style="width: 100%; font-family: var(--m1o-mono); font-size: 14px; background: var(--m1o-bg); color: var(--m1o-fg); border: 1px solid var(--m1o-rule); padding: 1rem; outline: none; margin-bottom: 1.2rem;">
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button type="button" class="m1o-btn" id="m1o-url-modal-cancel">Cancel</button>
                <button type="button" class="m1o-btn m1o-btn--primary" id="m1o-url-modal-save">Save URL</button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const groupLbls = <?php echo wp_json_encode( $group_lbls ); ?>;
        const statusLbls = <?php echo wp_json_encode( $status_lbls ); ?>;
        const rowsEl = document.getElementById('m1o-ch-rows');
        const addBtn = document.getElementById('m1o-add-row');
        const countEl = document.getElementById('m1o-ch-count');
        const modal = document.getElementById('m1o-url-modal');
        const modalInput = document.getElementById('m1o-url-modal-input');
        const modalTitle = document.getElementById('m1o-url-modal-title');
        const modalCancel = document.getElementById('m1o-url-modal-cancel');
        const modalSave = document.getElementById('m1o-url-modal-save');
        let activeUrlRow = null;

        function updateNumbers() {
            const rows = rowsEl.querySelectorAll('.m1o-channel-row');
            rows.forEach((row, i) => {
                row.querySelector('.m1o-ch-num').textContent = 'CH ' + String(i + 1).padStart(2, '0');
            });
            if (countEl) countEl.textContent = String(rows.length).padStart(2, '0');
        }

        function attachRowEvents(row) {
            // Delete
            row.querySelector('[data-delete]').addEventListener('click', () => {
                if (confirm('Remove this channel?')) {
                    row.remove();
                    updateNumbers();
                }
            });

            // Open URL editor when clicking title field's host area (hidden URL)
            const titleInput = row.querySelector('.m1o-ch-input--title');
            const urlHidden = row.querySelector('.m1o-ch-url-hidden');
            titleInput.addEventListener('dblclick', () => {
                openUrlModal(row, titleInput.value || 'new channel', urlHidden.value);
            });
            // Also via host field double-click
            const hostInput = row.querySelector('.m1o-ch-input--host');
            if (hostInput) hostInput.addEventListener('dblclick', () => {
                openUrlModal(row, titleInput.value || 'new channel', urlHidden.value);
            });

            // Drag-and-drop
            row.addEventListener('dragstart', (e) => {
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            row.addEventListener('dragend', () => {
                row.classList.remove('dragging');
                rowsEl.querySelectorAll('.m1o-channel-row').forEach(r => r.classList.remove('drag-over'));
                updateNumbers();
            });
            row.addEventListener('dragover', (e) => {
                e.preventDefault();
                row.classList.add('drag-over');
            });
            row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
            row.addEventListener('drop', (e) => {
                e.preventDefault();
                row.classList.remove('drag-over');
                const dragging = rowsEl.querySelector('.dragging');
                if (dragging && dragging !== row) {
                    const allRows = Array.from(rowsEl.querySelectorAll('.m1o-channel-row'));
                    const draggingIdx = allRows.indexOf(dragging);
                    const targetIdx = allRows.indexOf(row);
                    if (draggingIdx < targetIdx) {
                        row.parentNode.insertBefore(dragging, row.nextSibling);
                    } else {
                        row.parentNode.insertBefore(dragging, row);
                    }
                }
            });
        }

        function openUrlModal(row, title, url) {
            activeUrlRow = row;
            modalTitle.textContent = title;
            modalInput.value = url || '';
            modal.style.display = 'flex';
            setTimeout(() => modalInput.focus(), 50);
        }
        function closeUrlModal() {
            modal.style.display = 'none';
            activeUrlRow = null;
        }
        modalCancel.addEventListener('click', closeUrlModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeUrlModal(); });
        modalSave.addEventListener('click', () => {
            if (activeUrlRow) {
                activeUrlRow.querySelector('.m1o-ch-url-hidden').value = modalInput.value;
            }
            closeUrlModal();
        });
        modalInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); modalSave.click(); }
            if (e.key === 'Escape') closeUrlModal();
        });

        // Attach to existing rows
        rowsEl.querySelectorAll('.m1o-channel-row').forEach(attachRowEvents);

        // Add a new row
        addBtn.addEventListener('click', () => {
            const groupOptions = Object.entries(groupLbls).map(([k, lbl]) => `<option value="${k}">${lbl}</option>`).join('');
            const statusOptions = Object.entries(statusLbls).map(([k, lbl]) => `<option value="${k}">${lbl}</option>`).join('');
            const row = document.createElement('div');
            row.className = 'm1o-channel-row';
            row.draggable = true;
            row.innerHTML = `
                <span class="m1o-drag" title="Drag to reorder">⋮⋮</span>
                <span class="m1o-ch-num"></span>
                <select class="m1o-ch-select" name="ch_group[]">${groupOptions}</select>
                <input class="m1o-ch-input m1o-ch-input--title" name="ch_title[]" type="text" placeholder="Title">
                <input class="m1o-ch-input m1o-ch-input--host" name="ch_host[]" type="text" placeholder="Host">
                <select class="m1o-ch-select" name="ch_status[]">${statusOptions}</select>
                <input type="hidden" name="ch_url[]" value="" class="m1o-ch-url-hidden">
                <button type="button" class="m1o-delete-btn" data-delete>×</button>
            `;
            rowsEl.appendChild(row);
            attachRowEvents(row);
            updateNumbers();
            row.querySelector('.m1o-ch-input--title').focus();
        });

        // Toggles — visual + checkbox state
        document.querySelectorAll('[data-toggle]').forEach(t => {
            t.addEventListener('click', (e) => {
                e.preventDefault();
                const cb = t.querySelector('input[type=checkbox]');
                cb.checked = !cb.checked;
                t.classList.toggle('is-on', cb.checked);
            });
        });

        // Auto-fill the URL prompt for new rows whose URL is empty: open modal on first focus
        rowsEl.addEventListener('focusin', (e) => {
            const row = e.target.closest('.m1o-channel-row');
            if (!row) return;
            const urlHidden = row.querySelector('.m1o-ch-url-hidden');
            const title = row.querySelector('.m1o-ch-input--title');
            if (!urlHidden.value && title === e.target && !row.dataset.promptShown) {
                row.dataset.promptShown = '1';
                // delay a touch so the user can type the title first
            }
        });

        updateNumbers();
    })();
    </script>

    <?php
}
