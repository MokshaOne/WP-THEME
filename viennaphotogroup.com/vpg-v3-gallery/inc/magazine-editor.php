<?php
/**
 * VPG v2 — Magazine Editor
 *
 * Custom admin tool for assembling magazine issues. Replaces the
 * generic vpg_magazine post editor with an issue-aware workflow:
 *
 *   📖 Magazine          → list of issues (status, cover, articles count, PDF link)
 *   📖 ↳ New Issue       → create / edit an issue (cover, lede, articles repeater)
 *   📖 ↳ Generate PDF    → renders the issue via mPDF (vendor/) into uploads/vpg-pdf/
 *
 * Storage model · everything lives on the vpg_magazine post:
 *   post_title         → issue title
 *   post_excerpt       → issue lede
 *   _thumbnail_id      → cover image
 *   _vpg_issue_number  → "Vol. III · No. 09" etc.
 *   _vpg_issue_date    → publication date (string, free format)
 *   _vpg_articles      → JSON array of articles
 *                        [{ title, author, body, image_id, page_break_after }, …]
 *   _vpg_pdf_url       → set after PDF is generated
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─── Admin menu ─────────────────────────────────────────────────── */
/* The editor screens set their headlines in the magazine's own face. */
add_action( 'admin_enqueue_scripts', function () {
    if ( strpos( sanitize_key( $_GET['page'] ?? '' ), 'vpg-magazine' ) === 0 ) {
        wp_enqueue_style( 'vpg-archivo-admin', 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&display=swap', [], null );
    }
} );

add_action( 'admin_menu', function () {
    add_menu_page(
        '📖 ' . __( 'Magazine', 'vpg-v2' ),
        '📖 ' . __( 'Magazine', 'vpg-v2' ),
        'edit_others_posts',
        'vpg-magazine',
        'vpg_magazine_list_page',
        'dashicons-book-alt',
        18
    );
    add_submenu_page( 'vpg-magazine', __( 'All Issues', 'vpg-v2' ), __( 'All Issues', 'vpg-v2' ), 'edit_others_posts', 'vpg-magazine', 'vpg_magazine_list_page' );
    add_submenu_page( 'vpg-magazine', __( 'New Issue', 'vpg-v2' ), __( '+ New Issue', 'vpg-v2' ), 'edit_others_posts', 'vpg-magazine-new', 'vpg_magazine_edit_page' );
    add_submenu_page( 'vpg-magazine', __( 'Duplicate Issue', 'vpg-v2' ), __( '↻ Duplicate Last', 'vpg-v2' ), 'edit_others_posts', 'vpg-magazine-duplicate', 'vpg_magazine_duplicate_handler' );

    // hidden edit page (linked from list)
    // pass an empty string for parent slug (PHP 8.1+ deprecates null here)
    add_submenu_page( '', __( 'Edit Issue', 'vpg-v2' ), '', 'edit_others_posts', 'vpg-magazine-edit', 'vpg_magazine_edit_page' );
}, 12 );

/* ─── List page · all issues ─────────────────────────────────────── */
function vpg_magazine_list_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    $issues = get_posts( [
        'post_type'      => 'vpg_magazine',
        'post_status'    => [ 'publish', 'draft', 'future', 'private' ],
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
    ?>
    <div class="wrap vpg-mag-admin">
        <h1 class="wp-heading-inline">📖 <?php esc_html_e( 'Magazine', 'vpg-v2' ); ?></h1>
        <a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-magazine-new' ) ); ?>">
            <?php esc_html_e( '+ New Issue', 'vpg-v2' ); ?>
        </a>

        <?php if ( ! $issues ) : ?>
            <div class="vpg-mag-empty">
                <h2><?php esc_html_e( 'No issues yet.', 'vpg-v2' ); ?></h2>
                <p><?php esc_html_e( 'Start the first issue of the Vienna Photo Group magazine.', 'vpg-v2' ); ?></p>
                <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-magazine-new' ) ); ?>">
                    <?php esc_html_e( 'Create the first issue →', 'vpg-v2' ); ?>
                </a>
            </div>
        <?php else : ?>
            <table class="widefat striped vpg-mag-table" style="margin-top:1rem">
                <thead>
                    <tr>
                        <th style="width:80px"><?php esc_html_e( 'Cover', 'vpg-v2' ); ?></th>
                        <th><?php esc_html_e( 'Issue', 'vpg-v2' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Articles', 'vpg-v2' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Status', 'vpg-v2' ); ?></th>
                        <th style="width:140px"><?php esc_html_e( 'Date', 'vpg-v2' ); ?></th>
                        <th style="width:200px"><?php esc_html_e( 'Actions', 'vpg-v2' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $issues as $issue ) :
                        $articles  = vpg_get_articles( $issue->ID );
                        $issue_no  = get_post_meta( $issue->ID, '_vpg_issue_number', true );
                        $cover_url = get_the_post_thumbnail_url( $issue->ID, 'thumbnail' );
                        $pdf_url   = get_post_meta( $issue->ID, '_vpg_pdf_url', true );
                        $edit_url  = admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $issue->ID );
                    ?>
                    <tr>
                        <td>
                            <div class="vpg-mag-thumb" style="<?php echo $cover_url ? 'background-image:url(' . esc_url( $cover_url ) . ')' : ''; ?>">
                                <?php if ( ! $cover_url ) echo '⁕'; ?>
                            </div>
                        </td>
                        <td>
                            <strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $issue->post_title ?: '(untitled)' ); ?></a></strong>
                            <?php if ( $issue_no ) : ?>
                                <div style="opacity:.55;font-family:ui-monospace,monospace;font-size:11px;letter-spacing:.18em;text-transform:uppercase;margin-top:.2em"><?php echo esc_html( $issue_no ); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo count( $articles ); ?></td>
                        <td>
                            <span class="vpg-mag-status vpg-mag-status--<?php echo esc_attr( $issue->post_status ); ?>">
                                <?php echo esc_html( $issue->post_status ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( mysql2date( 'M j, Y', $issue->post_date ) ); ?></td>
                        <td>
                            <a class="button button-small" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'vpg-v2' ); ?></a>
                            <a class="button button-small" href="<?php echo esc_url( get_permalink( $issue ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'vpg-v2' ); ?></a>
                            <?php if ( $pdf_url ) : ?>
                                <a class="button button-small" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank">PDF ↓</a>
                            <?php else : ?>
                                <a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_generate_pdf&issue=' . $issue->ID ), 'vpg_generate_pdf' ) ); ?>"><?php esc_html_e( 'Build PDF', 'vpg-v2' ); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <style>
        /* Gallery identity · white museum, hairline, one red */
        .vpg-mag-admin { font-family: 'Archivo', -apple-system, sans-serif; }
        .vpg-mag-admin h1 { font-weight: 900; font-stretch: 118%; text-transform: uppercase; letter-spacing: .01em; }
        .vpg-mag-admin .vpg-mag-thumb {
            width: 60px; height: 80px; border-radius: 0;
            background: #0B0B0B; background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center;
            color: #E5341F; font-size: 24px; font-weight: 900;
        }
        .vpg-mag-status { padding: .2rem .6rem; border-radius: 0; font-size: 10px; letter-spacing: .14em; text-transform: uppercase; font-weight: 700; border: 1px solid #E6E5E1; }
        .vpg-mag-status--publish { background: #0B0B0B; color: #fff; border-color: #0B0B0B; }
        .vpg-mag-status--draft   { background: #F5F4F1; color: #6A6A6A; }
        .vpg-mag-status--future  { background: #fff; color: #E5341F; border-color: #E5341F; }
        .vpg-mag-empty { background: #fff; border: 1px solid #E6E5E1; padding: 4rem 2rem; text-align: center; margin-top: 2rem; border-radius: 0; }
        .vpg-mag-empty h2 { font-size: 1.6rem; margin: 0 0 1rem; }
        .vpg-mag-empty p  { color: #6A6A6A; margin: 0 0 2rem; }
        .vpg-mag-table th { font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: .1em; }
    </style>
    <?php
}

/* ─── Edit page · single issue ────────────────────────────────── */
function vpg_magazine_edit_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    $issue_id = isset( $_GET['issue'] ) ? (int) $_GET['issue'] : 0;
    $issue    = $issue_id ? get_post( $issue_id ) : null;

    if ( $issue && $issue->post_type !== 'vpg_magazine' ) $issue = null;

    $title     = $issue ? $issue->post_title   : '';
    $lede      = $issue ? $issue->post_excerpt : '';
    $issue_no  = $issue ? get_post_meta( $issue_id, '_vpg_issue_number', true ) : '';
    $issue_dt  = $issue ? get_post_meta( $issue_id, '_vpg_issue_date',   true ) : '';
    $teaser    = $issue ? get_post_meta( $issue_id, '_vpg_next_teaser',  true ) : '';
    $cover_id  = $issue ? (int) get_post_thumbnail_id( $issue_id ) : 0;
    $cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'medium' ) : '';
    $articles  = $issue ? vpg_get_articles( $issue_id ) : [];
    $status    = $issue ? $issue->post_status : 'draft';

    wp_enqueue_media();
    wp_enqueue_editor();
    ?>
    <div class="wrap vpg-mag-edit">
        <h1>📖 <?php echo $issue ? esc_html( $issue->post_title ?: '(untitled)' ) : esc_html__( 'New Issue', 'vpg-v2' ); ?></h1>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="vpg-mag-form">
            <?php wp_nonce_field( 'vpg_save_issue' ); ?>
            <input type="hidden" name="action"   value="vpg_save_issue" />
            <input type="hidden" name="issue_id" value="<?php echo esc_attr( $issue_id ); ?>" />

            <div class="vpg-mag-grid">

                <!-- ─── Issue metadata ─── -->
                <section class="vpg-mag-panel">
                    <h2><?php esc_html_e( 'Issue metadata', 'vpg-v2' ); ?></h2>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'Title', 'vpg-v2' ); ?></span>
                        <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" required style="font-size:1.2rem;font-weight:600">
                    </label>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'Issue number / volume', 'vpg-v2' ); ?></span>
                        <input type="text" name="issue_number" value="<?php echo esc_attr( $issue_no ); ?>" placeholder="Vol. III · No. 09">
                    </label>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'Publication date (free text)', 'vpg-v2' ); ?></span>
                        <input type="text" name="issue_date" value="<?php echo esc_attr( $issue_dt ); ?>" placeholder="June MMXXVI">
                    </label>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'Lede (1-2 sentence intro)', 'vpg-v2' ); ?></span>
                        <textarea name="lede" rows="3" placeholder="A short editorial preface that previews the issue."><?php echo esc_textarea( $lede ); ?></textarea>
                    </label>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'In the next issue (teaser)', 'vpg-v2' ); ?></span>
                        <textarea name="next_teaser" rows="2" placeholder="<?php esc_attr_e( 'One or two lines on what the next issue brings.', 'vpg-v2' ); ?>"><?php echo esc_textarea( $teaser ); ?></textarea>
                    </label>

                    <label class="vpg-mag-row">
                        <span><?php esc_html_e( 'Status', 'vpg-v2' ); ?></span>
                        <select name="status">
                            <option value="draft"   <?php selected( $status, 'draft' ); ?>>Draft</option>
                            <option value="publish" <?php selected( $status, 'publish' ); ?>>Published</option>
                            <option value="future"  <?php selected( $status, 'future' ); ?>>Scheduled</option>
                            <option value="private" <?php selected( $status, 'private' ); ?>>Members-only</option>
                        </select>
                    </label>
                </section>

                <!-- ─── Cover ─── -->
                <section class="vpg-mag-panel">
                    <h2><?php esc_html_e( 'Cover', 'vpg-v2' ); ?></h2>
                    <input type="hidden" name="cover_id" id="vpg-cover-id" value="<?php echo esc_attr( $cover_id ); ?>">
                    <div class="vpg-cover-pick" id="vpg-cover-pick">
                        <?php if ( $cover_url ) : ?>
                            <img src="<?php echo esc_url( $cover_url ); ?>" alt="" class="vpg-cover-img">
                        <?php else : ?>
                            <div class="vpg-cover-placeholder">⁕<br><small>Click to pick a cover</small></div>
                        <?php endif; ?>
                    </div>
                    <p>
                        <button type="button" class="button" id="vpg-cover-btn"><?php esc_html_e( 'Choose cover', 'vpg-v2' ); ?></button>
                        <button type="button" class="button-link-delete" id="vpg-cover-remove"><?php esc_html_e( 'Remove', 'vpg-v2' ); ?></button>
                    </p>
                </section>
            </div>

            <!-- ─── Articles repeater ─── -->
            <section class="vpg-mag-articles">
                <div class="vpg-mag-articles-head">
                    <h2><?php esc_html_e( 'Articles', 'vpg-v2' ); ?></h2>
                    <div class="vpg-mag-compile">
                        <span><?php esc_html_e( 'Add from site:', 'vpg-v2' ); ?></span>
                        <button type="button" class="button" data-vpg-pick="journal">📰 <?php esc_html_e( 'Journal article', 'vpg-v2' ); ?></button>
                        <button type="button" class="button" data-vpg-pick="artist">👤 <?php esc_html_e( 'Featured artist', 'vpg-v2' ); ?></button>
                        <button type="button" class="button" data-vpg-pick="event">📅 <?php esc_html_e( 'Event', 'vpg-v2' ); ?></button>
                        <button type="button" class="button" data-vpg-pick="photos">🖼 <?php esc_html_e( 'Photo spread', 'vpg-v2' ); ?></button>
                        <button type="button" class="button button-primary" id="vpg-add-article"><?php esc_html_e( '+ Blank article', 'vpg-v2' ); ?></button>
                    </div>
                </div>
                <p class="description"><?php esc_html_e( 'Compile the issue from what the site already has — journal writing, a member as featured artist, events, photo spreads from member uploads — or start a blank article. Drag the handle to reorder. Each article becomes a section in the published issue and a chapter in the PDF.', 'vpg-v2' ); ?></p>

                <ol class="vpg-mag-list" id="vpg-mag-list">
                    <?php foreach ( $articles as $i => $a ) : ?>
                        <?php vpg_render_article_row( $i, $a ); ?>
                    <?php endforeach; ?>
                </ol>

                <template id="vpg-article-template">
                    <?php vpg_render_article_row( '__INDEX__', [ 'title' => '', 'author' => '', 'body' => '', 'image_id' => 0, 'page_break_after' => 0 ] ); ?>
                </template>
            </section>

            <p style="margin-top:2rem">
                <button type="submit" class="button button-primary button-large" name="save_action" value="save"><?php esc_html_e( 'Save issue', 'vpg-v2' ); ?></button>
                <?php if ( $issue ) : ?>
                    <a class="button button-large" href="<?php echo esc_url( get_permalink( $issue_id ) ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'vpg-v2' ); ?></a>
                    <a class="button button-large" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vpg_generate_pdf&issue=' . $issue_id ), 'vpg_generate_pdf' ) ); ?>"><?php esc_html_e( 'Save & build PDF', 'vpg-v2' ); ?></a>
                <?php endif; ?>
                <a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=vpg-magazine' ) ); ?>"><?php esc_html_e( 'Back', 'vpg-v2' ); ?></a>
            </p>
        </form>

        <!-- ─── Compile picker overlay ─── -->
        <div class="vpg-pick-overlay" id="vpg-pick-overlay" hidden>
            <div class="vpg-pick-modal" role="dialog" aria-modal="true" aria-labelledby="vpg-pick-title">
                <div class="vpg-pick-head">
                    <h2 id="vpg-pick-title"><?php esc_html_e( 'Pick content', 'vpg-v2' ); ?></h2>
                    <button type="button" class="vpg-pick-close" id="vpg-pick-close" aria-label="Close">×</button>
                </div>
                <div class="vpg-pick-search">
                    <input type="search" id="vpg-pick-search" placeholder="<?php esc_attr_e( 'Filter…', 'vpg-v2' ); ?>" autocomplete="off">
                </div>
                <div class="vpg-pick-list" id="vpg-pick-list"><p class="vpg-pick-loading"><?php esc_html_e( 'Loading…', 'vpg-v2' ); ?></p></div>
                <div class="vpg-pick-foot" id="vpg-pick-foot" hidden>
                    <button type="button" class="button button-primary" id="vpg-pick-add-selected"><?php esc_html_e( 'Add selected as photo spread', 'vpg-v2' ); ?></button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .vpg-mag-edit .vpg-mag-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
        .vpg-mag-compile { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
        .vpg-mag-compile > span { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .12em; color: #646970; }
        .vpg-pick-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 100000; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .vpg-pick-overlay[hidden] { display: none; } /* author display:flex beats the UA's [hidden] rule — restore it */
        .vpg-pick-modal { background: #fff; border-radius: 8px; width: 640px; max-width: 100%; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; }
        .vpg-pick-head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #ccd0d4; }
        .vpg-pick-head h2 { margin: 0; font-size: 1.05rem; }
        .vpg-pick-search { padding: .7rem 1.25rem; border-bottom: 1px solid #ccd0d4; }
        .vpg-pick-search input { width: 100%; padding: .5rem .7rem; border: 1px solid #ccd0d4; border-radius: 4px; font-size: 13px; }
        .vpg-pick-close { background: none; border: 0; font-size: 22px; cursor: pointer; color: #646970; line-height: 1; }
        .vpg-pick-list { overflow-y: auto; padding: .5rem 0; }
        .vpg-pick-loading { padding: 2rem; text-align: center; color: #646970; }
        .vpg-pick-item { display: grid; grid-template-columns: 48px 1fr auto; gap: .8rem; align-items: center; padding: .55rem 1.25rem; border-bottom: 1px solid #f0f0f1; }
        .vpg-pick-item:hover { background: #f6f7f7; }
        .vpg-pick-item__thumb { width: 48px; height: 48px; border-radius: 4px; background: #f0f0f1 center/cover; display: flex; align-items: center; justify-content: center; color: #bbb; }
        .vpg-pick-item__title { font-weight: 600; }
        .vpg-pick-item__meta { color: #646970; font-size: 12px; margin-top: .15em; }
        .vpg-pick-foot { padding: 1rem 1.25rem; border-top: 1px solid #ccd0d4; text-align: right; }
        .vpg-pick-check { width: 18px; height: 18px; }
        @media (max-width: 900px) { .vpg-mag-edit .vpg-mag-grid { grid-template-columns: 1fr; } }
        .vpg-mag-panel { background: #fff; border: 1px solid #ccd0d4; padding: 1.5rem; border-radius: 8px; }
        .vpg-mag-panel h2 { font-size: 1.05rem; margin: 0 0 1rem; }
        .vpg-mag-row { display: block; margin-bottom: 1rem; }
        .vpg-mag-row > span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .12em; color: #646970; margin-bottom: .4rem; }
        .vpg-mag-row input, .vpg-mag-row textarea, .vpg-mag-row select { width: 100%; padding: .55rem .7rem; font-size: 14px; border: 1px solid #ccd0d4; border-radius: 4px; }
        .vpg-cover-pick { aspect-ratio: 3/4; background: #f0f0f0; border-radius: 4px; overflow: hidden; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .vpg-cover-pick img.vpg-cover-img { width: 100%; height: 100%; object-fit: cover; }
        .vpg-cover-placeholder { color: #aaa; text-align: center; font-size: 48px; line-height: 1.4; padding: 1rem; }
        .vpg-cover-placeholder small { font-size: 12px; display: block; margin-top: .4rem; }
        .vpg-mag-articles { margin-top: 2rem; background: #fff; border: 1px solid #ccd0d4; padding: 1.5rem; border-radius: 8px; }
        .vpg-mag-articles-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .vpg-mag-articles-head h2 { margin: 0; font-size: 1.05rem; }
        .vpg-mag-list { list-style: none; padding: 0; margin: 1rem 0 0; }
        .vpg-mag-article { background: #fafafa; border: 1px solid #ccd0d4; border-radius: 6px; padding: 1rem; margin-bottom: 1rem; }
        .vpg-mag-article__head { display: grid; grid-template-columns: 30px 1fr auto auto; gap: .8rem; align-items: center; margin-bottom: .8rem; }
        .vpg-mag-article__handle { cursor: grab; color: #999; font-size: 14px; user-select: none; }
        .vpg-mag-article__title-input { font-weight: 600; font-size: 1rem; padding: .4rem .6rem !important; }
        .vpg-mag-article__remove { color: #b32d2e; cursor: pointer; background: none; border: 0; }
        .vpg-mag-article__body { width: 100%; min-height: 140px; padding: .6rem; border: 1px solid #ccd0d4; border-radius: 4px; font: 14px/1.6 'Inter', system-ui, sans-serif; }
        .vpg-mag-article__snippets { display: flex; gap: .4rem; align-items: center; margin: 0 0 .4rem; }
        .vpg-mag-article__snippets span { font-size: 11px; color: #646970; text-transform: uppercase; letter-spacing: .1em; }
        .vpg-mag-article__snippets button { font-size: 11px; padding: .2rem .6rem; border: 1px solid #ccd0d4; background: #fff; border-radius: 3px; cursor: pointer; }
        .vpg-mag-article__snippets button:hover { border-color: #2271b1; color: #2271b1; }
        .vpg-mag-article__meta { display: grid; grid-template-columns: 1fr 1fr auto auto; gap: .8rem; align-items: center; margin-top: .8rem; }
        .vpg-mag-article__meta input { padding: .4rem .6rem; }
        .vpg-mag-article__img { width: 60px; height: 60px; background: #f0f0f0; border-radius: 4px; background-size: cover; background-position: center; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; color: #999; }
        .vpg-mag-article.is-dragging { opacity: .4; }
        .vpg-mag-article.is-over { border-color: #2271b1; background: #f0f6fc; }
        .vpg-mag-article__pb { font-size: 12px; color: #646970; display: inline-flex; gap: .3rem; align-items: center; }

        /* ── Gallery identity · the editor wears the magazine's own look ──
           White museum: sharp corners, hairline #E6E5E1, ink #0B0B0B,
           exactly one red #E5341F. Overrides the WP defaults above. */
        .vpg-mag-edit { font-family: 'Archivo', -apple-system, sans-serif; }
        .vpg-mag-edit h1 { font-weight: 900; font-stretch: 118%; text-transform: uppercase; letter-spacing: .01em; }
        .vpg-mag-panel, .vpg-mag-articles { border-radius: 0; border-color: #E6E5E1; box-shadow: none; }
        .vpg-mag-panel h2, .vpg-mag-articles-head h2 { font-size: 11px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: #0B0B0B; }
        .vpg-mag-panel h2::before, .vpg-mag-articles-head h2::before { content: '● '; color: #E5341F; font-size: 9px; vertical-align: 2px; }
        .vpg-mag-row > span { color: #6A6A6A; font-weight: 700; letter-spacing: .14em; }
        .vpg-mag-row input, .vpg-mag-row textarea, .vpg-mag-row select,
        .vpg-mag-article__body, .vpg-mag-article__meta input, .vpg-pick-search input { border-radius: 0; border-color: #E6E5E1; }
        .vpg-mag-row input:focus, .vpg-mag-row textarea:focus, .vpg-mag-article__body:focus,
        .vpg-mag-article__title-input:focus, .vpg-pick-search input:focus { border-color: #E5341F; box-shadow: 0 0 0 1px #E5341F; outline: none; }
        .vpg-cover-pick { border-radius: 0; background: #F5F4F1; border: 1px solid #E6E5E1; }
        .vpg-cover-placeholder { color: #9C9A95; }
        .vpg-mag-article { background: #fff; border-radius: 0; border-color: #E6E5E1; border-left: 3px solid #E6E5E1; transition: border-color .15s; }
        .vpg-mag-article:hover { border-left-color: #0B0B0B; }
        .vpg-mag-article.is-over { border-color: #E5341F; background: #fff; }
        .vpg-mag-article__handle { color: #9C9A95; }
        .vpg-mag-article__handle:hover { color: #E5341F; }
        .vpg-mag-article__title-input { border-radius: 0; border-color: #E6E5E1; }
        .vpg-mag-article__img { border-radius: 0; background-color: #F5F4F1; border: 1px solid #E6E5E1; }
        .vpg-mag-article__snippets button { border-radius: 0; border-color: #E6E5E1; text-transform: uppercase; letter-spacing: .06em; font-weight: 600; }
        .vpg-mag-article__snippets button:hover { border-color: #E5341F; color: #E5341F; }
        .vpg-mag-compile .button { border-radius: 0; text-transform: uppercase; font-size: 11px; letter-spacing: .08em; font-weight: 700; border-color: #0B0B0B; color: #0B0B0B; background: #fff; }
        .vpg-mag-compile .button:hover { border-color: #E5341F; color: #E5341F; }
        .vpg-mag-compile .button-primary, .vpg-mag-edit .button-primary { background: #E5341F; border-color: #E5341F; color: #fff; border-radius: 0; text-shadow: none; }
        .vpg-mag-compile .button-primary:hover, .vpg-mag-edit .button-primary:hover { background: #BE2410; border-color: #BE2410; color: #fff; }
        .vpg-mag-edit .button-large { border-radius: 0; }
        .vpg-pick-modal { border-radius: 0; border: 2px solid #0B0B0B; font-family: 'Archivo', -apple-system, sans-serif; }
        .vpg-pick-head { border-bottom-color: #E6E5E1; }
        .vpg-pick-head h2 { font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        .vpg-pick-head h2::before { content: '● '; color: #E5341F; font-size: 9px; vertical-align: 2px; }
        .vpg-pick-close:hover { color: #E5341F; }
        .vpg-pick-item:hover { background: #F5F4F1; }
        .vpg-pick-item__thumb { border-radius: 0; }
        .vpg-pick-foot { border-top-color: #E6E5E1; }
    </style>

    <script>
    (function () {
        var coverBtn   = document.getElementById('vpg-cover-btn');
        var coverPick  = document.getElementById('vpg-cover-pick');
        var coverInput = document.getElementById('vpg-cover-id');
        var coverRm    = document.getElementById('vpg-cover-remove');

        function openMediaFrame(cb) {
            var f = wp.media({ title: 'Select image', button: { text: 'Use this image' }, multiple: false });
            f.on('select', function () {
                var att = f.state().get('selection').first().toJSON();
                cb({ id: att.id, url: att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url });
            });
            f.open();
        }

        if (coverBtn) coverBtn.addEventListener('click', function () {
            openMediaFrame(function (a) {
                coverInput.value = a.id;
                coverPick.innerHTML = '<img src="' + a.url + '" alt="" class="vpg-cover-img">';
            });
        });
        if (coverPick && coverBtn) coverPick.addEventListener('click', function () { coverBtn.click(); });
        if (coverRm) coverRm.addEventListener('click', function (e) {
            e.preventDefault();
            coverInput.value = '';
            coverPick.innerHTML = '<div class="vpg-cover-placeholder">⁕<br><small>Click to pick a cover</small></div>';
        });

        // ─── Article rows ───
        var list = document.getElementById('vpg-mag-list');
        var tpl  = document.getElementById('vpg-article-template');
        var addBtn = document.getElementById('vpg-add-article');

        function reindex() {
            list.querySelectorAll('.vpg-mag-article').forEach(function (row, i) {
                row.querySelectorAll('[data-name]').forEach(function (el) {
                    var name = el.getAttribute('data-name');
                    el.setAttribute('name', 'articles[' + i + '][' + name + ']');
                });
            });
        }

        function bindRow(row) {
            // Image picker
            var imgBtn = row.querySelector('.vpg-mag-article__img');
            var imgIn  = row.querySelector('[data-name="image_id"]');
            imgBtn.addEventListener('click', function () {
                openMediaFrame(function (a) {
                    imgIn.value = a.id;
                    imgBtn.style.backgroundImage = 'url(' + a.url + ')';
                    imgBtn.textContent = '';
                });
            });

            // Snippet buttons · insert layout blocks at the cursor
            var SNIPPETS = {
                pull:  '\n<blockquote class="pull">The line worth pulling out of the text.</blockquote>\n',
                plate: '\n<figure class="plate"><img src="IMAGE-URL" alt=""><figcaption>Caption — Photographer</figcaption></figure>\n',
                pair:  '\n<div class="pair"><figure><img src="IMAGE-URL" alt=""><figcaption>Left — Photographer</figcaption></figure><figure><img src="IMAGE-URL" alt=""><figcaption>Right — Photographer</figcaption></figure></div>\n'
            };
            var bodyTa = row.querySelector('[data-name="body"]');
            row.querySelectorAll('[data-snippet]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var snip = SNIPPETS[btn.getAttribute('data-snippet')] || '';
                    var s = bodyTa.selectionStart || bodyTa.value.length;
                    bodyTa.value = bodyTa.value.slice(0, s) + snip + bodyTa.value.slice(bodyTa.selectionEnd || s);
                    bodyTa.focus();
                    bodyTa.selectionStart = bodyTa.selectionEnd = s + snip.length;
                });
            });

            // Remove
            row.querySelector('.vpg-mag-article__remove').addEventListener('click', function () {
                if (confirm('Remove this article?')) { row.remove(); reindex(); }
            });

            // Drag-to-reorder (native HTML5)
            row.setAttribute('draggable', 'true');
            row.addEventListener('dragstart', function () { row.classList.add('is-dragging'); });
            row.addEventListener('dragend',   function () { row.classList.remove('is-dragging'); reindex(); });
            row.addEventListener('dragover',  function (e) { e.preventDefault(); row.classList.add('is-over'); });
            row.addEventListener('dragleave', function () { row.classList.remove('is-over'); });
            row.addEventListener('drop',      function (e) {
                e.preventDefault();
                row.classList.remove('is-over');
                var dragging = list.querySelector('.is-dragging');
                if (dragging && dragging !== row) {
                    var rect = row.getBoundingClientRect();
                    var after = (e.clientY - rect.top) > (rect.height / 2);
                    list.insertBefore(dragging, after ? row.nextSibling : row);
                }
            });
        }

        list.querySelectorAll('.vpg-mag-article').forEach(bindRow);

        function addRow(prefill) {
            var html = tpl.innerHTML.replace(/__INDEX__/g, list.children.length);
            var wrap = document.createElement('div');
            wrap.innerHTML = html;
            var row = wrap.firstElementChild;
            list.appendChild(row);
            bindRow(row);
            if (prefill) {
                row.querySelector('[data-name="title"]').value  = prefill.title  || '';
                row.querySelector('[data-name="author"]').value = prefill.author || '';
                row.querySelector('[data-name="body"]').value   = prefill.body   || '';
                row.querySelector('[data-name="image_id"]').value = prefill.image_id || '';
                if (prefill.image_url) {
                    var imgBtn = row.querySelector('.vpg-mag-article__img');
                    imgBtn.style.backgroundImage = 'url(' + prefill.image_url + ')';
                    imgBtn.textContent = '';
                }
            }
            reindex();
            row.querySelector('.vpg-mag-article__title-input').focus();
            return row;
        }

        addBtn.addEventListener('click', function () { addRow(null); });

        // ─── Compile picker ───
        var ajaxUrl   = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
        var pickNonce = <?php echo wp_json_encode( wp_create_nonce( 'vpg_mag_pick' ) ); ?>;
        var overlay   = document.getElementById('vpg-pick-overlay');
        var pickList  = document.getElementById('vpg-pick-list');
        var pickFoot  = document.getElementById('vpg-pick-foot');
        var pickTitle = document.getElementById('vpg-pick-title');
        var pickKind  = '';

        var kindTitles = {
            journal: <?php echo wp_json_encode( __( 'Journal · pick an article', 'vpg-v2' ) ); ?>,
            artist:  <?php echo wp_json_encode( __( 'Members · pick the featured artist', 'vpg-v2' ) ); ?>,
            event:   <?php echo wp_json_encode( __( 'Events · pick an event', 'vpg-v2' ) ); ?>,
            photos:  <?php echo wp_json_encode( __( 'Photos · tick the plates for the spread', 'vpg-v2' ) ); ?>
        };

        var pickSearch = document.getElementById('vpg-pick-search');
        pickSearch.addEventListener('input', function () {
            var q = pickSearch.value.trim().toLowerCase();
            pickList.querySelectorAll('.vpg-pick-item').forEach(function (row) {
                row.style.display = (!q || row.textContent.toLowerCase().indexOf(q) !== -1) ? '' : 'none';
            });
        });

        function closePicker() { overlay.hidden = true; pickList.innerHTML = ''; pickSearch.value = ''; }
        document.getElementById('vpg-pick-close').addEventListener('click', closePicker);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closePicker(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !overlay.hidden) closePicker(); });

        function fetchJSON(url) {
            return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
        }

        function addFromSource(kind, id, ids) {
            var url = ajaxUrl + '?action=vpg_mag_pick_item&_ajax_nonce=' + pickNonce + '&kind=' + kind;
            if (id)  url += '&id=' + id;
            (ids || []).forEach(function (x) { url += '&ids[]=' + x; });
            fetchJSON(url).then(function (res) {
                if (res && res.success) { addRow(res.data); closePicker(); }
                else { alert('Could not load that item.'); }
            });
        }

        function openPicker(kind) {
            pickKind = kind;
            pickTitle.textContent = kindTitles[kind] || 'Pick content';
            pickFoot.hidden = kind !== 'photos';
            overlay.hidden = false;
            pickList.innerHTML = '<p class="vpg-pick-loading">…</p>';
            fetchJSON(ajaxUrl + '?action=vpg_mag_pick_list&_ajax_nonce=' + pickNonce + '&kind=' + kind).then(function (res) {
                if (!res || !res.success) { pickList.innerHTML = '<p class="vpg-pick-loading">Failed to load.</p>'; return; }
                if (!res.data.length)     { pickList.innerHTML = '<p class="vpg-pick-loading">Nothing found yet.</p>'; return; }
                pickList.innerHTML = '';
                res.data.forEach(function (it) {
                    var row = document.createElement('div');
                    row.className = 'vpg-pick-item';
                    var thumb = it.thumb
                        ? '<span class="vpg-pick-item__thumb" style="background-image:url(' + it.thumb + ')"></span>'
                        : '<span class="vpg-pick-item__thumb">⁕</span>';
                    var action = (kind === 'photos')
                        ? '<input type="checkbox" class="vpg-pick-check" value="' + it.id + '">'
                        : '<button type="button" class="button button-small vpg-pick-add" data-id="' + it.id + '">Add</button>';
                    if (kind === 'artist' && !it.interviewed) {
                        action += ' <button type="button" class="button button-small vpg-pick-invite" data-id="' + it.id + '" title="<?php echo esc_attr__( 'Mail + notify this member to answer the interview questions', 'vpg-v2' ); ?>"><?php echo esc_js( __( 'Request interview', 'vpg-v2' ) ); ?></button>';
                    }
                    row.innerHTML = thumb +
                        '<span><span class="vpg-pick-item__title"></span><div class="vpg-pick-item__meta"></div></span>' +
                        action;
                    row.querySelector('.vpg-pick-item__title').textContent = it.title;
                    row.querySelector('.vpg-pick-item__meta').textContent  = it.meta;
                    if (it.vip) {
                        var vip = document.createElement('span');
                        vip.textContent = '★ VIP';
                        vip.style.cssText = 'margin-left:8px;font-size:10px;font-weight:800;letter-spacing:.08em;color:#fff;background:#E5341F;padding:2px 7px;border-radius:3px;vertical-align:1px';
                        row.querySelector('.vpg-pick-item__title').appendChild(vip);
                    }
                    pickList.appendChild(row);
                });
                pickList.querySelectorAll('.vpg-pick-add').forEach(function (btn) {
                    btn.addEventListener('click', function () { addFromSource(pickKind, btn.getAttribute('data-id'), null); });
                });
                pickList.querySelectorAll('.vpg-pick-invite').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        btn.disabled = true;
                        fetchJSON(ajaxUrl + '?action=vpg_interview_invite&_ajax_nonce=' + pickNonce + '&user=' + btn.getAttribute('data-id')).then(function (res) {
                            btn.textContent = (res && res.success) ? <?php echo wp_json_encode( __( 'Invited ✓', 'vpg-v2' ) ); ?> : <?php echo wp_json_encode( __( 'Failed — retry', 'vpg-v2' ) ); ?>;
                            btn.disabled = !!(res && res.success);
                        });
                    });
                });
            });
        }

        document.querySelectorAll('[data-vpg-pick]').forEach(function (btn) {
            btn.addEventListener('click', function () { openPicker(btn.getAttribute('data-vpg-pick')); });
        });

        document.getElementById('vpg-pick-add-selected').addEventListener('click', function () {
            var ids = Array.prototype.map.call(pickList.querySelectorAll('.vpg-pick-check:checked'), function (c) { return c.value; });
            if (!ids.length) { alert('Tick at least one photo.'); return; }
            addFromSource('photos', null, ids);
        });
    })();
    </script>
    <?php
}

/* ─── Single article row · used in editor + template ─────────────── */
function vpg_render_article_row( $i, $a ) {
    $title    = $a['title']    ?? '';
    $author   = $a['author']   ?? '';
    $body     = $a['body']     ?? '';
    $image_id = (int) ( $a['image_id'] ?? 0 );
    $pb       = ! empty( $a['page_break_after'] );
    $img_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
    ?>
    <li class="vpg-mag-article">
        <div class="vpg-mag-article__head">
            <span class="vpg-mag-article__handle">⋮⋮</span>
            <input type="text" data-name="title" class="vpg-mag-article__title-input" value="<?php echo esc_attr( $title ); ?>" placeholder="Article title">
            <span style="font-family:ui-monospace,monospace;font-size:11px;color:#999;letter-spacing:.1em">#<?php echo is_numeric( $i ) ? ( (int) $i + 1 ) : '0'; ?></span>
            <button type="button" class="vpg-mag-article__remove" title="Remove">×</button>
        </div>

        <div class="vpg-mag-article__snippets">
            <span><?php esc_html_e( 'Insert:', 'vpg-v2' ); ?></span>
            <button type="button" data-snippet="pull"><?php esc_html_e( 'Pull quote', 'vpg-v2' ); ?></button>
            <button type="button" data-snippet="plate"><?php esc_html_e( 'Full-page plate', 'vpg-v2' ); ?></button>
            <button type="button" data-snippet="pair"><?php esc_html_e( 'Image pair', 'vpg-v2' ); ?></button>
        </div>
        <textarea data-name="body" class="vpg-mag-article__body" placeholder="Article body · plain text or basic HTML (paragraphs, links, &lt;em&gt;, &lt;strong&gt;)"><?php echo esc_textarea( $body ); ?></textarea>

        <div class="vpg-mag-article__meta">
            <input type="text" data-name="author" value="<?php echo esc_attr( $author ); ?>" placeholder="Author / byline">
            <input type="hidden" data-name="image_id" value="<?php echo esc_attr( $image_id ); ?>">
            <span class="vpg-mag-article__img" title="Article image" style="<?php echo $img_url ? 'background-image:url(' . esc_url( $img_url ) . ')' : ''; ?>"><?php echo $img_url ? '' : '+'; ?></span>
            <label class="vpg-mag-article__pb">
                <input type="checkbox" data-name="page_break_after" value="1" <?php checked( $pb ); ?>>
                <?php esc_html_e( 'Page break after', 'vpg-v2' ); ?>
            </label>
        </div>
    </li>
    <?php
}

/* ─── Save handler ───────────────────────────────────────────────── */
add_action( 'admin_post_vpg_save_issue', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_save_issue' );

    $issue_id     = (int) ( $_POST['issue_id'] ?? 0 );
    $title        = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $lede         = wp_kses_post( wp_unslash( $_POST['lede'] ?? '' ) );
    $issue_no     = sanitize_text_field( wp_unslash( $_POST['issue_number'] ?? '' ) );
    $issue_dt     = sanitize_text_field( wp_unslash( $_POST['issue_date'] ?? '' ) );
    $cover_id     = (int) ( $_POST['cover_id'] ?? 0 );
    $status_in    = sanitize_key( wp_unslash( $_POST['status'] ?? '' ) );
    $status       = in_array( $status_in, [ 'draft', 'publish', 'future', 'private' ], true ) ? $status_in : 'draft';

    // Articles (sanitized)
    $articles = [];
    if ( isset( $_POST['articles'] ) && is_array( $_POST['articles'] ) ) {
        foreach ( $_POST['articles'] as $row ) {
            if ( ! is_array( $row ) ) continue;
            $articles[] = [
                'title'            => sanitize_text_field( wp_unslash( $row['title']  ?? '' ) ),
                'author'           => sanitize_text_field( wp_unslash( $row['author'] ?? '' ) ),
                'body'             => wp_kses_post(       wp_unslash( $row['body']   ?? '' ) ),
                'image_id'         => (int) ( $row['image_id'] ?? 0 ),
                'page_break_after' => ! empty( $row['page_break_after'] ),
            ];
        }
    }

    $postarr = [
        'post_type'    => 'vpg_magazine',
        'post_title'   => $title,
        'post_excerpt' => $lede,
        'post_status'  => $status,
        'post_content' => '', // articles are stored as meta, post_content stays empty
    ];
    if ( $issue_id ) {
        $postarr['ID'] = $issue_id;
        wp_update_post( $postarr );
    } else {
        $issue_id = wp_insert_post( $postarr );
    }

    if ( $cover_id ) set_post_thumbnail( $issue_id, $cover_id ); else delete_post_thumbnail( $issue_id );
    update_post_meta( $issue_id, '_vpg_issue_number', $issue_no );
    update_post_meta( $issue_id, '_vpg_issue_date',   $issue_dt );
    update_post_meta( $issue_id, '_vpg_next_teaser',  sanitize_textarea_field( wp_unslash( $_POST['next_teaser'] ?? '' ) ) );
    update_post_meta( $issue_id, '_vpg_articles',     wp_json_encode( $articles ) );

    $redirect = isset( $_POST['save_action'] ) && $_POST['save_action'] === 'save'
        ? admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $issue_id . '&saved=1' )
        : admin_url( 'admin.php?page=vpg-magazine&saved=' . $issue_id );

    wp_safe_redirect( $redirect );
    exit;
} );

/* ─── Helper · read articles ─────────────────────────────────────── */
function vpg_get_articles( $issue_id ) {
    $json = get_post_meta( $issue_id, '_vpg_articles', true );
    if ( ! $json ) return [];
    $decoded = json_decode( $json, true );
    return is_array( $decoded ) ? $decoded : [];
}

/* ─── Duplicate previous issue · pre-fills a new draft with the ToC ── */
function vpg_magazine_duplicate_handler() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    $latest = get_posts( [ 'post_type' => 'vpg_magazine', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ] );
    if ( ! $latest ) {
        wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine&dup=empty' ) );
        exit;
    }

    $src = $latest[0];
    $articles = vpg_get_articles( $src->ID );

    // Strip bodies/images · keep titles + authors as scaffolding
    foreach ( $articles as &$a ) {
        $a['body']     = '';
        $a['image_id'] = 0;
    }
    unset( $a );

    $new_id = wp_insert_post( [
        'post_type'    => 'vpg_magazine',
        'post_title'   => $src->post_title . ' · ' . __( '(copy)', 'vpg-v2' ),
        'post_excerpt' => '',
        'post_status'  => 'draft',
        'post_content' => '',
    ] );

    update_post_meta( $new_id, '_vpg_articles',     wp_json_encode( $articles ) );
    update_post_meta( $new_id, '_vpg_issue_number', '' );
    update_post_meta( $new_id, '_vpg_issue_date',   '' );

    wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $new_id . '&dup=ok' ) );
    exit;
}

/* ════════════════════════════════════════════════════════════════ */
/*  Compile from site content                                        */
/*                                                                   */
/*  The photo magazine is assembled from what the site already has:  */
/*  featured artists (members + their work), journal articles,       */
/*  events, and photo spreads from member uploads. Two AJAX          */
/*  endpoints feed the picker in the issue editor; the picked item   */
/*  arrives as a prefilled article row (title/author/body/image),    */
/*  so the storage model, PDF generator and reader templates stay    */
/*  untouched.                                                       */
/* ════════════════════════════════════════════════════════════════ */

/* ─── List sources for the picker ────────────────────────────────── */
add_action( 'wp_ajax_vpg_mag_pick_list', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error( 'forbidden', 403 );
    check_ajax_referer( 'vpg_mag_pick' );

    $kind  = sanitize_key( $_GET['kind'] ?? '' );
    $items = [];

    if ( $kind === 'journal' ) {
        $q = new WP_Query( [
            'post_type'      => [ 'post', 'vpg_tutorial', 'vpg_review' ],
            'post_status'    => 'publish',
            'posts_per_page' => 30,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
        foreach ( $q->posts as $p ) {
            $items[] = [
                'id'    => $p->ID,
                'title' => $p->post_title,
                'meta'  => get_post_type_object( $p->post_type )->labels->singular_name
                           . ' · ' . get_the_author_meta( 'display_name', $p->post_author )
                           . ' · ' . mysql2date( 'M j, Y', $p->post_date ),
                'thumb' => get_the_post_thumbnail_url( $p, 'thumbnail' ) ?: '',
            ];
        }
    } elseif ( $kind === 'artist' ) {
        $users = get_users( [ 'role__in' => [ 'vpg_member', 'administrator', 'editor', 'author' ], 'number' => 60 ] );
        foreach ( $users as $u ) {
            $works       = count_user_posts( $u->ID, [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'post' ], true );
            $interviewed = function_exists( 'vpg_get_interview' ) && vpg_get_interview( $u->ID );
            $items[] = [
                'id'          => $u->ID,
                'title'       => $u->display_name,
                'meta'        => sprintf( _n( '%d published work', '%d published works', (int) $works, 'vpg-v2' ), (int) $works )
                                 . ' · ' . __( 'member since', 'vpg-v2' ) . ' ' . mysql2date( 'M Y', $u->user_registered )
                                 . ' · ' . ( $interviewed ? __( 'Interview ✓', 'vpg-v2' ) : __( 'no interview yet', 'vpg-v2' ) ),
                'thumb'       => get_avatar_url( $u->ID, [ 'size' => 96 ] ),
                'interviewed' => (bool) $interviewed,
            ];
        }
    } elseif ( $kind === 'event' ) {
        $q = new WP_Query( [
            'post_type'      => 'vpg_event',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
        foreach ( $q->posts as $p ) {
            $date  = get_post_meta( $p->ID, '_vpg_event_date',  true );
            $venue = get_post_meta( $p->ID, '_vpg_event_venue', true );
            $items[] = [
                'id'    => $p->ID,
                'title' => $p->post_title,
                'meta'  => trim( ( $date ?: '' ) . ( $venue ? ' · ' . $venue : '' ), ' ·' ) ?: __( 'Event', 'vpg-v2' ),
                'thumb' => get_the_post_thumbnail_url( $p, 'thumbnail' ) ?: '',
            ];
        }
    } elseif ( $kind === 'photos' ) {
        $q = new WP_Query( [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => 48,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
        // Residents are VIP · their photos are flagged and float to the top.
        $vip_cache = [];
        foreach ( $q->posts as $p ) {
            $aid = (int) $p->post_author;
            if ( ! isset( $vip_cache[ $aid ] ) ) {
                $vip_cache[ $aid ] = function_exists( 'vpg_member_rank' ) && vpg_member_rank( $aid )['level'] >= 3;
            }
            $items[] = [
                'id'    => $p->ID,
                'title' => $p->post_title ?: basename( get_attached_file( $p->ID ) ),
                'meta'  => get_the_author_meta( 'display_name', $p->post_author )
                           . ' · ' . mysql2date( 'M j, Y', $p->post_date )
                           . ( $vip_cache[ $aid ] ? ' · ' . __( 'Resident', 'vpg-v2' ) : '' ),
                'thumb' => wp_get_attachment_image_url( $p->ID, 'thumbnail' ) ?: '',
                'vip'   => $vip_cache[ $aid ],
            ];
        }
        usort( $items, fn( $a, $b ) => (int) $b['vip'] <=> (int) $a['vip'] ); // stable · VIP first, dates keep order
    } else {
        wp_send_json_error( 'unknown kind' );
    }

    wp_send_json_success( $items );
} );

/* ─── Build a prefilled article from one source ──────────────────── */
add_action( 'wp_ajax_vpg_mag_pick_item', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_send_json_error( 'forbidden', 403 );
    check_ajax_referer( 'vpg_mag_pick' );

    $kind = sanitize_key( $_GET['kind'] ?? '' );
    $id   = (int) ( $_GET['id'] ?? 0 );
    $ids  = array_filter( array_map( 'intval', (array) ( $_GET['ids'] ?? [] ) ) );

    $article = null;

    if ( $kind === 'journal' && $id ) {
        $p = get_post( $id );
        if ( $p && $p->post_status === 'publish' ) {
            $article = [
                'title'    => $p->post_title,
                'author'   => get_the_author_meta( 'display_name', $p->post_author ),
                'body'     => $p->post_content,
                'image_id' => (int) get_post_thumbnail_id( $p ),
            ];
        }
    } elseif ( $kind === 'artist' && $id ) {
        $u = get_userdata( $id );
        if ( $u ) {
            $works = get_posts( [
                'author'         => $u->ID,
                'post_type'      => [ 'vpg_location', 'vpg_studio', 'vpg_shop', 'vpg_review', 'vpg_tutorial', 'post' ],
                'post_status'    => 'publish',
                'posts_per_page' => 8,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );
            $body  = '';
            if ( $u->description ) {
                $body .= '<p><em>' . esc_html( $u->description ) . '</em></p>' . "\n";
            }
            // Interview answers from the dashboard become the article's Q&A spine.
            if ( function_exists( 'vpg_get_interview' ) ) {
                foreach ( vpg_get_interview( $u->ID ) as $qa ) {
                    $body .= '<p><strong>' . esc_html( $qa['q'] ) . '</strong></p>' . "\n"
                           . '<p>' . esc_html( $qa['a'] ) . '</p>' . "\n";
                }
            }
            $first_img = 0;
            foreach ( $works as $w ) {
                $tid = (int) get_post_thumbnail_id( $w );
                if ( ! $tid ) continue;
                if ( ! $first_img ) $first_img = $tid;
                $url = wp_get_attachment_image_url( $tid, 'large' );
                if ( ! $url ) continue;
                $body .= '<figure><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $w->post_title ) . '">'
                       . '<figcaption>' . esc_html( $w->post_title ) . ' — ' . esc_html( $u->display_name ) . '</figcaption></figure>' . "\n";
            }
            if ( $works && ! $body ) {
                $body = '<p>' . esc_html__( 'Selected works:', 'vpg-v2' ) . '</p><ul>';
                foreach ( $works as $w ) $body .= '<li>' . esc_html( $w->post_title ) . '</li>';
                $body .= '</ul>';
            }
            $article = [
                'title'    => sprintf( __( 'Featured artist — %s', 'vpg-v2' ), $u->display_name ),
                'author'   => $u->display_name,
                'body'     => $body,
                'image_id' => $first_img,
            ];
        }
    } elseif ( $kind === 'event' && $id ) {
        $p = get_post( $id );
        if ( $p && $p->post_type === 'vpg_event' ) {
            $date  = get_post_meta( $p->ID, '_vpg_event_date',  true );
            $venue = get_post_meta( $p->ID, '_vpg_event_venue', true );
            $lead  = trim( ( $date ?: '' ) . ( $venue ? ' · ' . $venue : '' ), ' ·' );
            $body  = ( $lead ? '<p><strong>' . esc_html( $lead ) . '</strong></p>' . "\n" : '' ) . $p->post_content;
            $article = [
                'title'    => $p->post_title,
                'author'   => __( 'Events', 'vpg-v2' ),
                'body'     => $body,
                'image_id' => (int) get_post_thumbnail_id( $p ),
            ];
        }
    } elseif ( $kind === 'photos' && $ids ) {
        $body      = '';
        $first_img = 0;
        foreach ( array_slice( $ids, 0, 12 ) as $aid ) {
            if ( ! wp_attachment_is_image( $aid ) ) continue;
            if ( ! $first_img ) $first_img = $aid;
            $url = wp_get_attachment_image_url( $aid, 'large' );
            if ( ! $url ) continue;
            $att     = get_post( $aid );
            $caption = wp_get_attachment_caption( $aid ) ?: ( $att->post_title ?? '' );
            $credit  = $att ? get_the_author_meta( 'display_name', $att->post_author ) : '';
            $body   .= '<figure><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $caption ) . '">'
                     . '<figcaption>' . esc_html( trim( $caption . ( $credit ? ' — ' . $credit : '' ), ' —' ) ) . '</figcaption></figure>' . "\n";
        }
        $article = [
            'title'    => __( 'Plates', 'vpg-v2' ),
            'author'   => __( 'Members of VPG', 'vpg-v2' ),
            'body'     => $body,
            'image_id' => $first_img,
        ];
    }

    if ( ! $article ) wp_send_json_error( 'not found' );

    $article['image_url'] = $article['image_id'] ? ( wp_get_attachment_image_url( $article['image_id'], 'thumbnail' ) ?: '' ) : '';
    wp_send_json_success( $article );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Editorial calendar · issues and journal by month, one screen     */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-magazine', __( 'Calendar', 'vpg-v2' ), __( '📅 Calendar', 'vpg-v2' ), 'edit_others_posts', 'vpg-magazine-calendar', 'vpg_magazine_calendar_page' );
}, 13 );

function vpg_magazine_calendar_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );

    // Everything editorial, 14 months back, grouped by month
    $items = get_posts( [
        'post_type'      => [ 'vpg_magazine', 'post', 'vpg_event' ],
        'post_status'    => [ 'publish', 'draft', 'future', 'pending', 'private' ],
        'posts_per_page' => 300,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'date_query'     => [ [ 'after' => '-14 months' ] ],
    ] );
    $months = [];
    foreach ( $items as $p ) {
        $months[ get_the_date( 'Y-m', $p ) ][] = $p;
    }
    ?>
    <div class="wrap">
        <h1>📅 <?php esc_html_e( 'Editorial calendar', 'vpg-v2' ); ?></h1>
        <p class="description"><?php esc_html_e( 'Issues, journal posts and events by month. One click drafts a new issue from a month\'s content.', 'vpg-v2' ); ?></p>
        <?php foreach ( $months as $ym => $list ) :
            $label     = mysql2date( 'F Y', $ym . '-01 00:00:00' );
            $has_issue = (bool) array_filter( $list, function ( $p ) { return $p->post_type === 'vpg_magazine'; } );
            $draft_url = wp_nonce_url( admin_url( 'admin-post.php?action=vpg_issue_from_month&month=' . $ym ), 'vpg_issue_from_month' );
        ?>
        <h2 style="margin:1.6em 0 .4em;display:flex;align-items:center;gap:12px">
            <?php echo esc_html( $label ); ?>
            <?php if ( ! $has_issue ) : ?>
                <a class="button button-small" href="<?php echo esc_url( $draft_url ); ?>"><?php esc_html_e( '↳ Draft issue from this month', 'vpg-v2' ); ?></a>
            <?php endif; ?>
        </h2>
        <table class="widefat striped">
            <tbody>
            <?php foreach ( $list as $p ) : ?>
                <tr>
                    <td style="width:110px"><code><?php echo esc_html( str_replace( 'vpg_', '', $p->post_type ) ); ?></code></td>
                    <td><a href="<?php echo esc_url( $p->post_type === 'vpg_magazine' ? admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $p->ID ) : get_edit_post_link( $p->ID ) ); ?>"><strong><?php echo esc_html( $p->post_title ?: '(untitled)' ); ?></strong></a></td>
                    <td style="width:110px"><span class="vpg-mag-status vpg-mag-status--<?php echo esc_attr( $p->post_status ); ?>"><?php echo esc_html( $p->post_status ); ?></span></td>
                    <td style="width:130px"><?php echo esc_html( get_the_date( 'M j, Y', $p ) ); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>
    </div>
    <?php
}

/* ─── One-click month draft · journal + events + plates of a month ── */
add_action( 'admin_post_vpg_issue_from_month', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_issue_from_month' );

    $ym = preg_match( '/^\d{4}-\d{2}$/', $_GET['month'] ?? '' ) ? $_GET['month'] : gmdate( 'Y-m', strtotime( 'last month' ) );
    [ $year, $month ] = array_map( 'intval', explode( '-', $ym ) );
    $label = mysql2date( 'F Y', $ym . '-01 00:00:00' );

    $articles = [];

    // Journal writing of the month
    $posts = get_posts( [
        'post_type'      => [ 'post', 'vpg_tutorial', 'vpg_review' ],
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'date_query'     => [ [ 'year' => $year, 'month' => $month ] ],
        'orderby'        => 'date',
        'order'          => 'ASC',
    ] );
    foreach ( $posts as $p ) {
        $articles[] = [
            'title'            => $p->post_title,
            'author'           => get_the_author_meta( 'display_name', $p->post_author ),
            'body'             => $p->post_content,
            'image_id'         => (int) get_post_thumbnail_id( $p ),
            'page_break_after' => true,
        ];
    }

    // Events of the month → one round-up chapter
    $events = get_posts( [
        'post_type'      => 'vpg_event',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'date_query'     => [ [ 'year' => $year, 'month' => $month ] ],
    ] );
    if ( $events ) {
        $body = '';
        foreach ( $events as $e ) {
            $venue = get_post_meta( $e->ID, '_vpg_event_venue', true );
            $body .= '<p><strong>' . esc_html( $e->post_title ) . '</strong>'
                   . ( $venue ? ' — ' . esc_html( $venue ) : '' ) . '<br>'
                   . esc_html( wp_trim_words( $e->post_content, 30 ) ) . '</p>' . "\n";
        }
        $articles[] = [ 'title' => sprintf( __( 'Events — %s', 'vpg-v2' ), $label ), 'author' => __( 'Events', 'vpg-v2' ), 'body' => $body, 'image_id' => 0, 'page_break_after' => true ];
    }

    // Plates · member photos uploaded that month
    $photos = get_posts( [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 8,
        'date_query'     => [ [ 'year' => $year, 'month' => $month ] ],
    ] );
    if ( $photos ) {
        $body = ''; $first = 0;
        foreach ( $photos as $ph ) {
            $url = wp_get_attachment_image_url( $ph->ID, 'large' );
            if ( ! $url ) continue;
            if ( ! $first ) $first = $ph->ID;
            $credit = get_the_author_meta( 'display_name', $ph->post_author );
            $body  .= '<figure class="plate"><img src="' . esc_url( $url ) . '" alt="">'
                    . '<figcaption>' . esc_html( trim( ( $ph->post_title ?: '' ) . ( $credit ? ' — ' . $credit : '' ), ' —' ) ) . '</figcaption></figure>' . "\n";
        }
        if ( $body ) $articles[] = [ 'title' => __( 'Plates', 'vpg-v2' ), 'author' => __( 'Members of VPG', 'vpg-v2' ), 'body' => $body, 'image_id' => $first, 'page_break_after' => false ];
    }

    $issue_id = wp_insert_post( [
        'post_type'    => 'vpg_magazine',
        'post_title'   => $label,
        'post_excerpt' => '',
        'post_status'  => 'draft',
        'post_content' => '',
    ] );
    update_post_meta( $issue_id, '_vpg_articles', wp_json_encode( $articles ) );
    update_post_meta( $issue_id, '_vpg_issue_date', $label );
    update_post_meta( $issue_id, '_vpg_issue_number', '' );

    wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine-edit&issue=' . $issue_id . '&saved=1' ) );
    exit;
} );
