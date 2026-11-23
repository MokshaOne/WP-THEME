<?php
/**
 * Admin-wide CSS — v2.1 Relief.
 * Restyles WP admin within its existing chrome so the poet keeps her
 * muscle memory (same menus, same widgets, same screens) but everything
 * is rendered in the front-end's paper + ink + oxblood + ochre palette
 * with Noto Serif Tamil throughout.
 *
 * Palette (matches style.css):
 *   --paper        #F0E7CC
 *   --paper-warm   #F4ECD4
 *   --paper-deep   #E6DBBC
 *   --ink          #1A0E04
 *   --ink-soft     #3A2A18
 *   --ink-mute     #5C4530   (AAA on paper at body sizes)
 *   --oxblood      #6B1414
 *   --ochre        #8B5A0F
 *   --rule         rgba(110, 86, 56, 0.22)
 *   --rule-soft    rgba(110, 86, 56, 0.10)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Bring EB Garamond into the admin too — for numerals, dates, small caps.
 */
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style(
        'kavithai-admin-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Serif+Tamil:wght@300;400;500;600&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap',
        [], '2.1.0'
    );
} );

// ─────────────────────────────────────────────  GLOBAL ADMIN BASE
add_action( 'admin_head', function() { ?>
    <style>
    :root {
        --kv-paper:      #F0E7CC;
        --kv-paper-warm: #F4ECD4;
        --kv-paper-deep: #E6DBBC;
        --kv-ink:        #1A0E04;
        --kv-ink-soft:   #3A2A18;
        --kv-ink-mute:   #5C4530;
        --kv-oxblood:    #6B1414;
        --kv-ochre:      #8B5A0F;
        --kv-rule:       rgba(110, 86, 56, 0.22);
        --kv-rule-soft:  rgba(110, 86, 56, 0.10);
        --kv-serif-ta:   'Noto Serif Tamil', 'EB Garamond', Georgia, serif;
        --kv-serif-la:   'EB Garamond', Georgia, serif;
    }

    /* Page chrome */
    #wpwrap, #wpcontent, #wpbody-content { background: var(--kv-paper) !important; }
    body.wp-admin { color: var(--kv-ink); }
    .wrap h1, .wrap h2 { font-family: var(--kv-serif-ta) !important; font-weight: 400 !important; color: var(--kv-ink) !important; letter-spacing: -0.01em; }
    .wrap h1 { font-size: 28px !important; }
    .wrap p { font-family: var(--kv-serif-ta); }

    /* Left menu — keep WP structure, restyle */
    #adminmenuwrap, #adminmenuback, #adminmenu { background: var(--kv-ink) !important; }
    #adminmenu li.menu-top { border-bottom: 1px solid rgba(139, 90, 15, 0.08); }
    #adminmenu li.menu-top:hover > a,
    #adminmenu li.opensub > a.menu-top,
    #adminmenu li > a.menu-top:focus,
    #adminmenu li.menu-top.current > a,
    #adminmenu li.menu-top.wp-has-current-submenu > a {
        background: rgba(139, 90, 15, 0.18) !important;
        color: var(--kv-paper) !important;
        box-shadow: inset 2px 0 var(--kv-ochre);
    }
    #adminmenu .wp-menu-name {
        font-family: var(--kv-serif-ta) !important;
        font-size: 15px !important;
        color: rgba(240, 231, 204, 0.85) !important;
        font-weight: 400 !important;
    }
    #adminmenu .wp-submenu a {
        font-family: var(--kv-serif-ta) !important;
        font-size: 14px !important;
        color: rgba(240, 231, 204, 0.7) !important;
    }
    #adminmenu .wp-submenu a:hover,
    #adminmenu .wp-submenu a:focus,
    #adminmenu .wp-submenu .current a,
    #adminmenu .wp-submenu li.current a {
        color: var(--kv-ochre) !important;
        background: rgba(139, 90, 15, 0.08) !important;
    }
    #adminmenu div.wp-menu-image::before {
        color: var(--kv-ochre) !important;
        opacity: 0.7;
    }
    #adminmenu li.menu-top.current div.wp-menu-image::before,
    #adminmenu li.menu-top:hover div.wp-menu-image::before {
        color: var(--kv-paper) !important;
        opacity: 1;
    }

    /* Admin bar — match palette */
    #wpadminbar { background: var(--kv-ink) !important; border-bottom: 1px solid var(--kv-ochre); }
    #wpadminbar .ab-item, #wpadminbar a.ab-item { color: rgba(240, 231, 204, 0.85) !important; font-family: var(--kv-serif-la) !important; }
    #wpadminbar .ab-item:hover { color: var(--kv-ochre) !important; }

    /* Buttons */
    .button-primary, .wp-core-ui .button-primary {
        background: var(--kv-oxblood) !important;
        border-color: var(--kv-oxblood) !important;
        color: var(--kv-paper) !important;
        font-family: var(--kv-serif-la) !important;
        font-size: 12px !important;
        letter-spacing: 0.18em !important;
        text-transform: uppercase !important;
        font-weight: 500 !important;
        text-shadow: none !important;
        box-shadow: none !important;
        padding: 0.55em 1.4em !important;
        height: auto !important;
    }
    .button-primary:hover, .wp-core-ui .button-primary:hover {
        background: #4A0808 !important;
        border-color: #4A0808 !important;
    }
    .button, .wp-core-ui .button {
        font-family: var(--kv-serif-la) !important;
        font-size: 12px !important;
        letter-spacing: 0.18em !important;
        text-transform: uppercase !important;
        font-weight: 500 !important;
        border-color: var(--kv-rule) !important;
        color: var(--kv-ink) !important;
        background: var(--kv-paper-warm) !important;
        border-radius: 0 !important;
        text-shadow: none !important;
        box-shadow: none !important;
        padding: 0.55em 1.2em !important;
        height: auto !important;
    }
    .button:hover {
        background: var(--kv-ink) !important;
        color: var(--kv-paper) !important;
        border-color: var(--kv-ink) !important;
    }

    /* Links */
    .wrap a, #wpbody-content a { color: var(--kv-oxblood); }
    .wrap a:hover { color: var(--kv-ink); }

    /* Tables (list views, post lists) */
    .wp-list-table {
        font-family: var(--kv-serif-ta);
        background: var(--kv-paper-warm) !important;
        border: 1px solid var(--kv-rule) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
    .wp-list-table thead th, .wp-list-table tfoot th {
        background: var(--kv-paper-deep) !important;
        color: var(--kv-ink-mute) !important;
        font-family: var(--kv-serif-la) !important;
        font-size: 11px !important;
        letter-spacing: 0.22em !important;
        text-transform: uppercase !important;
        font-weight: 500 !important;
    }
    .wp-list-table tbody td, .wp-list-table tbody th {
        font-family: var(--kv-serif-ta) !important;
        font-size: 14px !important;
        color: var(--kv-ink) !important;
        border-bottom: 1px solid var(--kv-rule-soft) !important;
    }
    .wp-list-table .row-title { color: var(--kv-ink) !important; font-weight: 500; }
    .wp-list-table .row-title:hover { color: var(--kv-oxblood) !important; }

    /* Form inputs */
    input[type=text], input[type=email], input[type=url], input[type=password],
    input[type=number], input[type=search], textarea, select {
        font-family: var(--kv-serif-ta) !important;
        background: var(--kv-paper) !important;
        border-color: var(--kv-rule) !important;
        color: var(--kv-ink) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }
    input[type=text]:focus, input[type=email]:focus, input[type=url]:focus,
    input[type=number]:focus, input[type=search]:focus, textarea:focus, select:focus {
        border-color: var(--kv-oxblood) !important;
        box-shadow: 0 0 0 1px var(--kv-oxblood) !important;
        outline: none !important;
    }

    /* Notices */
    .notice {
        background: var(--kv-paper-warm) !important;
        border-left-color: var(--kv-ochre) !important;
        color: var(--kv-ink-soft) !important;
        font-family: var(--kv-serif-ta) !important;
        box-shadow: none !important;
    }
    .notice.notice-success { border-left-color: #1A6B3A !important; }
    .notice.notice-error { border-left-color: var(--kv-oxblood) !important; }
    .notice p { font-family: var(--kv-serif-ta) !important; font-size: 14px !important; }
    </style>
<?php } );

// ─────────────────────────────────────────────  DASHBOARD-SPECIFIC
add_action( 'admin_head', function() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || $screen->id !== 'dashboard' ) return;
    ?>
    <style>
    /* Postbox cards */
    #dashboard-widgets-wrap { margin-top: 0; }
    #dashboard-widgets .postbox {
        border: 1px solid var(--kv-rule) !important;
        border-radius: 0 !important;
        background: var(--kv-paper-warm) !important;
        box-shadow: none !important;
    }
    #dashboard-widgets .postbox .hndle {
        background: var(--kv-paper-deep) !important;
        border-bottom: 1px solid var(--kv-rule) !important;
        padding: 14px 20px !important;
        cursor: default !important;
    }
    #dashboard-widgets .postbox h2, #dashboard-widgets .postbox h3 {
        font-family: var(--kv-serif-ta) !important;
        font-size: 17px !important;
        font-weight: 400 !important;
        color: var(--kv-ink) !important;
        letter-spacing: -0.005em;
    }
    #dashboard-widgets .postbox .inside { padding: 20px !important; margin: 0 !important; }
    .postbox-header button.handlediv, .postbox .hndle-settings { display: none !important; }

    /* Stat grid */
    .kv-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 4px; }
    .kv-stat {
        background: var(--kv-paper); border: 1px solid var(--kv-rule-soft);
        padding: 18px 14px; text-align: center;
    }
    .kv-stat .n {
        font-family: var(--kv-serif-la); font-size: 36px; font-weight: 400;
        color: var(--kv-ink); line-height: 1; margin-bottom: 6px;
        font-feature-settings: "lnum", "tnum";
    }
    .kv-stat .l {
        font-family: var(--kv-serif-la);
        font-size: 11px; letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--kv-ink-mute);
    }
    .kv-stat .icon { display: none; } /* drop emoji icons */
    .kv-stat.accent { background: var(--kv-ink); border-color: var(--kv-ink); }
    .kv-stat.accent .n { color: var(--kv-paper); }
    .kv-stat.accent .l { color: var(--kv-ochre); }

    /* Quick action buttons */
    .kv-btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
    .kv-qbtn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 22px; text-decoration: none;
        font-family: var(--kv-serif-la); font-size: 11px;
        letter-spacing: 0.22em; text-transform: uppercase;
        font-weight: 500;
        background: var(--kv-ink) !important; color: var(--kv-paper) !important;
        border-radius: 0 !important;
        flex: 1; min-width: 170px; justify-content: center;
        transition: background .2s;
        border: 1px solid var(--kv-ink);
    }
    .kv-qbtn:hover { background: var(--kv-oxblood) !important; border-color: var(--kv-oxblood); color: var(--kv-paper) !important; }

    /* Greeting */
    .kv-greeting {
        display: flex; align-items: center; gap: 16px;
        margin-bottom: 22px; padding-bottom: 18px;
        border-bottom: 1px solid var(--kv-rule);
    }
    .kv-greeting .avatar {
        width: 56px; height: 56px; border-radius: 50%;
        background: transparent;
        border: 1px solid var(--kv-ochre);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--kv-serif-ta); font-size: 28px;
        color: var(--kv-ochre); flex-shrink: 0;
    }
    .kv-greeting .avatar::before { content: 'க'; }
    .kv-greeting .avatar > * { display: none; }  /* hide emoji content */
    .kv-greeting .text h3 {
        font-family: var(--kv-serif-ta) !important;
        font-size: 20px !important; font-weight: 400 !important;
        color: var(--kv-ink) !important; margin: 0 0 4px !important;
    }
    .kv-greeting .text p {
        font-family: var(--kv-serif-la); font-size: 12px;
        color: var(--kv-ink-mute); margin: 0;
        letter-spacing: 0.18em; text-transform: uppercase;
    }

    /* Recent list */
    .kv-recent-list { list-style: none; padding: 0; margin: 0; }
    .kv-recent-list li {
        display: flex; align-items: center;
        padding: 12px 0; gap: 12px; flex-wrap: wrap;
        border-bottom: 1px solid var(--kv-rule-soft);
        font-family: var(--kv-serif-ta);
    }
    .kv-recent-list li:last-child { border-bottom: none; }
    .kv-recent-list .thumb { display: inline-block; width: 40px; height: 40px; }
    .kv-recent-list .title {
        font-size: 15px; color: var(--kv-ink); text-decoration: none;
        flex: 1; min-width: 120px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .kv-recent-list .title:hover { color: var(--kv-oxblood); }
    .kv-recent-list .meta {
        font-family: var(--kv-serif-la); font-size: 11px;
        color: var(--kv-ink-mute); letter-spacing: 0.12em;
        white-space: nowrap;
        font-feature-settings: "lnum", "tnum";
    }
    .kv-recent-list .actions {
        display: flex; gap: 12px; white-space: nowrap; margin-left: auto;
    }
    .kv-recent-list .actions a {
        font-family: var(--kv-serif-la); font-size: 10px;
        letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--kv-oxblood); text-decoration: none;
    }
    .kv-recent-list .actions a:hover { color: var(--kv-ink); }
    .kv-badge {
        font-family: var(--kv-serif-la); font-size: 9px;
        letter-spacing: 0.22em; text-transform: uppercase;
        padding: 2px 8px; margin-left: 6px;
        border: 1px solid var(--kv-rule);
    }
    .kv-badge.draft { color: var(--kv-oxblood); border-color: var(--kv-oxblood); }
    .kv-badge.pub   { color: var(--kv-ink-mute); border-color: var(--kv-rule); }
    .kv-badge.sched { color: var(--kv-ochre); border-color: var(--kv-ochre); }

    /* Views list */
    .kv-views-list { list-style: none; padding: 0; margin: 0; }
    .kv-views-list li {
        display: flex; justify-content: space-between;
        padding: 10px 0; border-bottom: 1px solid var(--kv-rule-soft);
        font-family: var(--kv-serif-ta); font-size: 14px;
    }
    .kv-views-list li:last-child { border-bottom: none; }
    .kv-views-list .page-name {
        color: var(--kv-ink); overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; flex: 1; text-decoration: none;
    }
    .kv-views-list .page-name:hover { color: var(--kv-oxblood); }
    .kv-views-list .count {
        font-family: var(--kv-serif-la); color: var(--kv-oxblood);
        font-weight: 500; margin-left: 10px;
        font-feature-settings: "lnum", "tnum";
        letter-spacing: 0.05em;
    }

    /* Upload widget */
    .kv-upload-drop {
        display: block; border: 2px dashed var(--kv-ochre);
        padding: 22px; text-align: center;
        background: var(--kv-paper);
        text-decoration: none !important;
        color: var(--kv-ink) !important;
        transition: border-color .2s, background .2s;
    }
    .kv-upload-drop:hover {
        border-color: var(--kv-oxblood);
        background: var(--kv-paper-warm);
    }
    .kv-upload-drop .icon {
        font-family: var(--kv-serif-ta); font-size: 28px;
        color: var(--kv-ochre); margin-bottom: 8px;
    }
    .kv-upload-drop p {
        font-family: var(--kv-serif-ta); font-size: 15px;
        color: var(--kv-ink); margin: 0 0 4px !important;
    }
    .kv-upload-drop small {
        font-family: var(--kv-serif-la); font-size: 11px;
        color: var(--kv-ink-mute); letter-spacing: 0.15em;
        text-transform: uppercase;
    }

    /* Notices in widgets */
    .kv-notice {
        font-family: var(--kv-serif-ta); font-size: 14px;
        color: var(--kv-ink-soft);
        margin: 14px 0 0; padding: 12px 16px;
        background: var(--kv-paper);
        border-left: 3px solid var(--kv-ochre);
        line-height: 1.6;
    }
    .kv-notice a { color: var(--kv-oxblood); font-weight: 500; }

    .kv-empty {
        font-family: var(--kv-serif-ta);
        font-size: 14px; color: var(--kv-ink-mute);
        font-style: italic; padding: 8px 0;
    }
    .kv-sub-label {
        font-family: var(--kv-serif-la); font-size: 10px;
        color: var(--kv-ochre); letter-spacing: 0.28em;
        text-transform: uppercase;
        margin: 0 0 8px;
    }
    .kv-link-row {
        margin: 14px 0 0 !important;
        padding-top: 10px;
        border-top: 1px solid var(--kv-rule-soft);
    }
    .kv-link-row a {
        font-family: var(--kv-serif-la) !important;
        font-size: 11px !important; letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--kv-oxblood) !important;
        text-decoration: none;
    }
    .kv-link-row a:hover { color: var(--kv-ink) !important; }
    </style>
    <?php
} );

// ─────────────────────────────────────────────  EDITOR (POEM / BOOK / AWARD)
add_action( 'admin_head', function() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen ) return;
    if ( ! in_array( $screen->post_type ?? '', [ 'kavithai_gedicht', 'kavithai_nul', 'kavithai_virudu' ] ) ) return;
    ?>
    <style>
    /* Block editor body — bigger, Tamil-friendly */
    .editor-styles-wrapper,
    .block-editor-writing-flow,
    .wp-block-paragraph,
    .wp-block {
        font-size: 19px !important;
        font-family: var(--kv-serif-ta) !important;
        line-height: 2 !important;
        color: var(--kv-ink) !important;
    }
    .edit-post-visual-editor { font-size: 19px; }
    .editor-post-title__input {
        font-family: var(--kv-serif-ta) !important;
        font-size: 34px !important;
        font-weight: 300 !important;
        color: var(--kv-ink) !important;
        letter-spacing: -0.015em;
    }

    /* Classic editor */
    #content {
        font-size: 19px !important;
        font-family: var(--kv-serif-ta) !important;
        line-height: 2.1 !important;
        padding: 16px !important;
        background: var(--kv-paper) !important;
        color: var(--kv-ink) !important;
        border-color: var(--kv-rule) !important;
    }
    #title {
        font-family: var(--kv-serif-ta) !important;
        font-size: 28px !important;
        padding: 14px !important;
        background: var(--kv-paper) !important;
        color: var(--kv-ink) !important;
        border-color: var(--kv-rule) !important;
        font-weight: 300 !important;
    }

    /* Sidebar metaboxes */
    #postbox-container-1 .postbox h2,
    #postbox-container-2 .postbox h2 {
        font-family: var(--kv-serif-ta) !important;
        font-size: 14px !important;
        color: var(--kv-ink) !important;
    }
    .postbox {
        background: var(--kv-paper-warm) !important;
        border: 1px solid var(--kv-rule) !important;
        box-shadow: none !important;
    }

    /* Featured-image, focal-picker */
    #postimagediv .inside { background: var(--kv-paper-warm); }
    </style>
    <?php
} );

// ─────────────────────────────────────────────  HOUSEKEEPING
// Remove unnecessary admin menu items for a cleaner UI.
add_action( 'admin_menu', function() {
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'tools.php' );
}, 999 );

// Hide WP nag screens from non-admin contexts.
add_action( 'admin_head', function() {
    if ( ! current_user_can( 'install_plugins' ) ) {
        echo '<style>.update-nag, .updated, .notice-warning { display: none !important; }</style>';
    }
} );
