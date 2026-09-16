<?php
/**
 * Admin features for the modernized theme:
 * 1. Auto-save indicator (visible "saved!" toast every 30s)
 * 2. Schedule poem publishing (friendly UI helper)
 * 3. Full-text poem search across body
 * 4. Bulk edit year/category
 * 5. Trash + 30-day restore safety net
 * 6. Print-friendly proof view before publishing
 * 7. Friendlier media upload page
 * 8. Focal-point picker for featured images
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// ═══════════════════════════════════════════════════════════
// 1. AUTOSAVE INDICATOR — friendly "saved" toast
// ═══════════════════════════════════════════════════════════
add_action('admin_footer-post.php',     'kv_autosave_indicator');
add_action('admin_footer-post-new.php', 'kv_autosave_indicator');
function kv_autosave_indicator() {
    $screen = get_current_screen();
    if ( ! in_array($screen->post_type ?? '', ['kavithai_gedicht','kavithai_nul','kavithai_virudu','page']) ) return;
    ?>
    <div id="kv-saved-toast" style="
        position:fixed; bottom:24px; right:24px; z-index:10000;
        background:#1A6B3A; color:#fff;
        font-family:'Noto Serif Tamil',Georgia,serif; font-size:15px;
        padding:12px 22px; border-radius:999px;
        box-shadow:0 8px 24px rgba(0,0,0,0.18);
        opacity:0; transform:translateY(20px);
        transition:opacity .35s, transform .35s;
        pointer-events:none;">
        <span style="margin-right:8px;">✓</span><span id="kv-saved-msg">சேமிக்கப்பட்டது</span>
    </div>
    <script>
    (function(){
        var toast = document.getElementById('kv-saved-toast');
        var msg   = document.getElementById('kv-saved-msg');
        function show(text){
            msg.textContent = text;
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
            clearTimeout(window._kvToastT);
            window._kvToastT = setTimeout(function(){
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
            }, 2200);
        }
        // Block editor save events
        if (window.wp && wp.data && wp.data.subscribe) {
            var wasSaving = false;
            wp.data.subscribe(function(){
                var saving = wp.data.select('core/editor').isSavingPost();
                var auto   = wp.data.select('core/editor').isAutosavingPost();
                var saved  = wp.data.select('core/editor').didPostSaveRequestSucceed();
                if (wasSaving && !saving && saved) {
                    show(auto ? 'தானாக சேமிக்கப்பட்டது' : 'சேமிக்கப்பட்டது ✓');
                }
                wasSaving = saving;
            });
        }
        // Classic editor heartbeat-based autosave detection
        if (window.jQuery) {
            jQuery(document).on('heartbeat-tick.autosave', function(e, data){
                if (data && data['wp-refresh-post-lock']) return;
                show('தானாக சேமிக்கப்பட்டது');
            });
            jQuery('#publish, #save-post').on('click', function(){
                setTimeout(function(){ show('சேமிக்கப்பட்டது ✓'); }, 800);
            });
        }
    })();
    </script>
    <?php
}

// ═══════════════════════════════════════════════════════════
// 2. SCHEDULE HELPER — make scheduled date easier to find
// (WP supports this natively; we add a visible card in the side)
// ═══════════════════════════════════════════════════════════
add_action('add_meta_boxes', function() {
    add_meta_box('kv_schedule_help', '📅 எப்போது வெளியிட', 'kv_schedule_help_cb', 'kavithai_gedicht', 'side', 'high');
});
function kv_schedule_help_cb($post) {
    $is_future = ($post->post_status === 'future');
    $date = $is_future ? get_the_date('d.m.Y · H:i', $post) : '';
    ?>
    <div style="font-family:'Noto Serif Tamil',Georgia,serif;font-size:14px;color:#3B2A1A;line-height:1.7;">
        <p style="margin:0 0 10px;">இந்த கவிதையை எதிர்காலத்தில் <strong>தானாக வெளியிட</strong> விரும்புகிறீர்களா?</p>
        <p style="margin:0 0 10px;color:#7D6448;font-size:13px;font-style:italic;">
            வலதுபுறம் "Publish" பெட்டியில் <strong>"Publish on: ... Edit"</strong> கிளிக் செய்து தேதியை மாற்றுங்கள். குறிப்பிட்ட நேரத்தில் தானாக வெளியாகும்.
        </p>
        <?php if ($is_future): ?>
        <div style="background:#E8F0F8;border-left:3px solid #1A3A5C;padding:10px 14px;border-radius:0 4px 4px 0;">
            <strong style="color:#1A3A5C;">⏰ திட்டமிடப்பட்டுள்ளது:</strong><br>
            <?php echo esc_html($date); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// ═══════════════════════════════════════════════════════════
// 3. FULL-TEXT POEM SEARCH PAGE
// ═══════════════════════════════════════════════════════════
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=kavithai_gedicht',
        'கவிதை வரிகள் தேடு',
        '🔍 வரிகள் தேடு',
        'edit_posts',
        'kv-poem-search',
        'kv_poem_search_page'
    );
});

function kv_poem_search_page() {
    $q = sanitize_text_field($_GET['q'] ?? '');
    $results = [];
    if ($q !== '') {
        // Search across title, content, dedication, backstory, year
        global $wpdb;
        $like = '%' . $wpdb->esc_like($q) . '%';
        $ids = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
            WHERE p.post_type = 'kavithai_gedicht'
              AND p.post_status IN ('publish','draft','future','private')
              AND (
                p.post_title LIKE %s
                OR p.post_content LIKE %s
                OR (m.meta_key IN ('_kv_dedication','_kv_backstory','_kv_origin_loc','_kv_note','_kv_year') AND m.meta_value LIKE %s)
              )
            ORDER BY p.post_modified DESC
            LIMIT 100
        ", $like, $like, $like));
        $results = $ids ?: [];
    }
    ?>
    <style>
    .kv-search-wrap { max-width:900px; margin:30px auto; font-family:'Noto Serif Tamil',Georgia,serif; }
    .kv-search-wrap h1 { font-size:26px; font-weight:400; margin-bottom:6px; }
    .kv-search-form { background:#fff; border:1px solid rgba(125,100,72,.2); border-radius:6px; padding:24px; margin-bottom:24px; }
    .kv-search-form input[type=text] { width:100%; padding:14px 18px; font-size:18px; font-family:inherit; border:1px solid rgba(125,100,72,.3); background:#FBF6E9; border-radius:4px; box-sizing:border-box; margin-bottom:12px; }
    .kv-search-form input[type=text]:focus { outline:none; border-color:#8B2020; }
    .kv-search-form button { background:#8B2020; color:#fff; border:none; padding:12px 28px; font-size:16px; font-family:inherit; cursor:pointer; border-radius:999px; }
    .kv-search-result { background:#fff; border:1px solid rgba(125,100,72,.15); border-radius:6px; padding:20px 24px; margin-bottom:12px; }
    .kv-search-result h3 { font-size:18px; font-weight:500; margin:0 0 6px; }
    .kv-search-result h3 a { color:#1A0E06; text-decoration:none; }
    .kv-search-result h3 a:hover { color:#8B2020; }
    .kv-search-result .meta { font-size:13px; color:#7D6448; margin-bottom:10px; }
    .kv-search-result .excerpt { font-size:15px; line-height:1.8; color:#3B2A1A; font-style:italic; }
    .kv-search-result mark { background:#FCE9A8; padding:1px 3px; border-radius:2px; }
    .kv-search-empty { text-align:center; padding:40px; color:#7D6448; font-style:italic; font-size:15px; }
    </style>
    <div class="kv-search-wrap">
        <h1>🔍 கவிதை வரிகள் தேடு</h1>
        <p style="color:#7D6448;margin-bottom:20px;font-size:15px;">தலைப்பு, உள்ளடக்கம், அர்ப்பணம், கதை, இடம், ஆண்டு — அனைத்திலும் தேடப்படும்.</p>

        <form method="GET" class="kv-search-form">
            <input type="hidden" name="post_type" value="kavithai_gedicht">
            <input type="hidden" name="page" value="kv-poem-search">
            <input type="text" name="q" value="<?php echo esc_attr($q); ?>" placeholder="ஒரு வார்த்தை, ஒரு வரி, ஒரு ஆண்டு..." autofocus>
            <button type="submit">தேடு</button>
        </form>

        <?php if ($q !== ''): ?>
            <p style="margin-bottom:16px;color:#3B2A1A;">"<strong><?php echo esc_html($q); ?></strong>" — <?php echo count($results); ?> கவிதை<?php echo count($results)===1?'':'கள்'; ?> கிடைத்தன</p>

            <?php if (empty($results)): ?>
                <div class="kv-search-empty">எதுவும் கிடைக்கவில்லை.</div>
            <?php else: ?>
                <?php foreach ($results as $id):
                    $post = get_post($id);
                    if (!$post) continue;
                    $year = get_post_meta($id,'_kv_year',true);
                    $status = get_post_status($id);
                    $excerpt = wp_strip_all_tags($post->post_content);

                    // Find matching snippet
                    $needle_pos = mb_stripos($excerpt, $q);
                    if ($needle_pos !== false) {
                        $start = max(0, $needle_pos - 60);
                        $excerpt = ($start > 0 ? '…' : '') . mb_substr($excerpt, $start, 220) . '…';
                    } else {
                        $excerpt = wp_trim_words($excerpt, 30, '…');
                    }
                    // Highlight
                    $excerpt = preg_replace('/(' . preg_quote($q,'/') . ')/iu', '<mark>$1</mark>', esc_html($excerpt));
                ?>
                <div class="kv-search-result">
                    <h3><a href="<?php echo esc_url(get_edit_post_link($id)); ?>"><?php echo esc_html($post->post_title); ?></a></h3>
                    <div class="meta">
                        <?php if ($year) echo esc_html($year) . ' · '; ?>
                        <?php
                        $status_labels = ['publish'=>'வெளியிட்டது','draft'=>'வரைவு','future'=>'திட்டம்','private'=>'தனிப்பட்டது'];
                        echo esc_html($status_labels[$status] ?? $status);
                        ?>
                    </div>
                    <div class="excerpt"><?php echo $excerpt; ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

// ═══════════════════════════════════════════════════════════
// 4. BULK EDIT — year + category in the WP bulk edit dropdown
// ═══════════════════════════════════════════════════════════
add_action('bulk_edit_custom_box', function($column, $post_type) {
    if ($post_type !== 'kavithai_gedicht') return;
    if ($column !== 'kv_year_col') return;
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label class="inline-edit-group">
                <span class="title">ஆண்டு</span>
                <input type="number" name="kv_bulk_year" value="" min="1900" max="2099" style="width:90px;font-family:'Noto Serif Tamil',serif;font-size:15px;padding:4px;">
                <span style="font-size:12px;color:#666;font-family:'Noto Serif Tamil',serif;margin-left:8px;">(மாற்றம் இல்லையெனில் காலியாக விடுங்கள்)</span>
            </label>
        </div>
    </fieldset>
    <?php
}, 10, 2);

// Save bulk edits
add_action('save_post', function($post_id) {
    if (!current_user_can('edit_post',$post_id)) return;
    if (isset($_REQUEST['kv_bulk_year']) && $_REQUEST['kv_bulk_year'] !== '') {
        update_post_meta($post_id, '_kv_year', absint($_REQUEST['kv_bulk_year']));
    }
});

// Add year column to poem list
add_filter('manage_kavithai_gedicht_posts_columns', function($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['kv_year_col'] = 'ஆண்டு';
            $new['kv_cat_col']  = 'வகை';
            $new['kv_status_col'] = 'நிலை';
        }
    }
    return $new;
});
add_action('manage_kavithai_gedicht_posts_custom_column', function($col, $post_id) {
    if ($col === 'kv_year_col') {
        echo esc_html(get_post_meta($post_id,'_kv_year',true) ?: '—');
    } elseif ($col === 'kv_cat_col') {
        $terms = get_the_terms($post_id, 'kavithai_cat');
        if ($terms && !is_wp_error($terms)) {
            echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
        } else { echo '—'; }
    } elseif ($col === 'kv_status_col') {
        $views = (int) get_post_meta($post_id,'_kv_views',true);
        $reactions = get_post_meta($post_id,'_kv_reactions',true) ?: [];
        $bits = [];
        if ($views) $bits[] = '👁 ' . number_format($views);
        if ($reactions) $bits[] = '💬 ' . count($reactions);
        echo $bits ? implode(' · ', $bits) : '<span style="color:#bbb;">—</span>';
    }
}, 10, 2);
// Make year column sortable
add_filter('manage_edit-kavithai_gedicht_sortable_columns', function($cols) {
    $cols['kv_year_col'] = 'kv_year_col';
    return $cols;
});
add_action('pre_get_posts', function($q) {
    if (!is_admin() || !$q->is_main_query()) return;
    if ($q->get('orderby') === 'kv_year_col') {
        $q->set('meta_key', '_kv_year');
        $q->set('orderby', 'meta_value_num');
    }
});

// ═══════════════════════════════════════════════════════════
// 5. TRASH SAFETY NET — keep trashed items 30 days
// Default WP is 30 days already (EMPTY_TRASH_DAYS = 30), but we
// add a friendly "Trash" widget + restore link surface
// ═══════════════════════════════════════════════════════════
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=kavithai_gedicht',
        'அழிக்கப்பட்ட கவிதைகள்',
        '🗑 குப்பை',
        'edit_posts',
        'kv-trash',
        'kv_trash_page'
    );
});

function kv_trash_page() {
    // Handle restore — verify nonce AND per-object capability (hardened in CP6).
    if ( isset( $_GET['restore'] ) ) {
        $rid = absint( $_GET['restore'] );
        if ( $rid && check_admin_referer( 'kv_restore_' . $_GET['restore'] ) && current_user_can( 'edit_post', $rid ) ) {
            wp_untrash_post( $rid );
            // Redirect to clean URL so refresh doesn't re-fire the restore.
            wp_safe_redirect( remove_query_arg( [ 'restore', '_wpnonce' ] ) );
            exit;
        } elseif ( $rid ) {
            echo '<div class="notice notice-error" style="font-family:\'Noto Serif Tamil\',serif;font-size:15px;"><p>அனுமதி இல்லை.</p></div>';
        }
    }

    $q = new WP_Query([
        'post_type'      => ['kavithai_gedicht','kavithai_nul','kavithai_virudu'],
        'post_status'    => 'trash',
        'posts_per_page' => 50,
        'no_found_rows'  => true,
    ]);
    ?>
    <style>
    .kv-trash-wrap { max-width:900px; margin:30px auto; font-family:'Noto Serif Tamil',Georgia,serif; }
    .kv-trash-wrap h1 { font-size:26px; font-weight:400; margin-bottom:6px; }
    .kv-trash-wrap p.intro { color:#7D6448; margin-bottom:24px; font-size:15px; }
    .kv-trash-item { background:#fff; border:1px solid rgba(125,100,72,.18); border-radius:6px; padding:18px 22px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; }
    .kv-trash-item .title { font-size:17px; color:#1A0E06; flex:1; min-width:200px; }
    .kv-trash-item .meta { font-size:13px; color:#7D6448; }
    .kv-trash-item .actions { display:flex; gap:10px; }
    .kv-trash-item .actions a { padding:7px 16px; border-radius:999px; font-size:14px; text-decoration:none; }
    .kv-trash-item .restore { background:#1A6B3A; color:#fff; }
    .kv-trash-item .delete  { background:transparent; color:#8B2020; border:1px solid rgba(139,32,32,.3); }
    .kv-trash-empty { text-align:center; padding:60px; background:#fff; border-radius:6px; color:#7D6448; font-style:italic; font-size:16px; }
    .kv-trash-empty .icon { font-size:48px; margin-bottom:12px; }
    .kv-trash-note { font-size:13px; color:#7D6448; margin-top:20px; padding:14px 18px; background:#FBF6E9; border-left:3px solid #C9952E; border-radius:0 4px 4px 0; }
    </style>
    <div class="kv-trash-wrap">
        <h1>🗑 அழிக்கப்பட்ட படைப்புகள்</h1>
        <p class="intro">அழிக்கப்பட்ட படைப்புகள் 30 நாட்கள் இங்கே இருக்கும். அதன் பிறகு தானாக நிரந்தரமாக நீக்கப்படும். தவறுதலாக அழித்திருந்தால் "மீட்க" அழுத்தவும்.</p>

        <?php if ($q->have_posts()):
            while ($q->have_posts()) { $q->the_post();
                $id = get_the_ID();
                $type = get_post_type();
                $type_labels = [
                    'kavithai_gedicht' => 'கவிதை',
                    'kavithai_nul'     => 'நூல்',
                    'kavithai_virudu'  => 'விருது',
                ];
                // Fix in CP6: $post is not in function scope; use get_post_modified_time.
                $modified  = (int) get_post_modified_time( 'U', false, $id );
                $days_left = max( 0, 30 - (int) floor( ( time() - $modified ) / 86400 ) );
            ?>
            <div class="kv-trash-item">
                <div class="title">
                    <strong><?php the_title(); ?></strong>
                    <div class="meta"><?php echo esc_html($type_labels[$type] ?? $type); ?> · <?php echo $days_left; ?> நாட்களில் நிரந்தரமாக நீக்கப்படும்</div>
                </div>
                <div class="actions">
                    <a class="restore" href="<?php echo esc_url(wp_nonce_url(add_query_arg('restore',$id), 'kv_restore_'.$id)); ?>">↶ மீட்க</a>
                    <a class="delete" href="<?php echo esc_url(get_delete_post_link($id, '', true)); ?>" onclick="return confirm('நிரந்தரமாக நீக்க வேண்டுமா?');">✕ இப்போதே நீக்கு</a>
                </div>
            </div>
            <?php } wp_reset_postdata();
        else: ?>
            <div class="kv-trash-empty">
                <div class="icon">✨</div>
                <p>குப்பை காலியாக உள்ளது.</p>
            </div>
        <?php endif; ?>

        <p class="kv-trash-note">💡 அழித்த கவிதைகள் 30 நாட்களுக்குப் பின்னர் தானாக நிரந்தரமாக நீக்கப்படும். அதற்கு முன் "மீட்க" அழுத்துவதன் மூலம் திரும்ப கொண்டுவரலாம்.</p>
    </div>
    <?php
}

// ═══════════════════════════════════════════════════════════
// 6. PRINT-FRIENDLY PROOF VIEW
// ═══════════════════════════════════════════════════════════
add_filter('post_row_actions', function($actions, $post) {
    if ($post->post_type !== 'kavithai_gedicht') return $actions;
    $proof_url = add_query_arg([
        'kv_proof' => '1',
        'p'        => $post->ID,
    ], home_url('/'));
    $actions['kv_proof'] = '<a href="' . esc_url($proof_url) . '" target="_blank" style="color:#8B2020;">🖨 அச்சு பார்வை</a>';
    return $actions;
}, 10, 2);

// Render proof view
add_action('template_redirect', function() {
    if ( ! isset($_GET['kv_proof']) ) return;
    $id = absint($_GET['p'] ?? 0);
    if ( ! $id ) return;
    $post = get_post($id);
    if ( ! $post || $post->post_type !== 'kavithai_gedicht' ) return;
    if ( ! current_user_can('edit_post', $id) ) wp_die('அனுமதி இல்லை.');

    $year  = get_post_meta($id,'_kv_year',true);
    $loc   = get_post_meta($id,'_kv_origin_loc',true);
    $ded   = get_post_meta($id,'_kv_dedication',true);
    $back  = get_post_meta($id,'_kv_backstory',true);
    $cats  = get_the_terms($id,'kavithai_cat');
    $cat   = ($cats && !is_wp_error($cats)) ? $cats[0]->name : '';
    ?><!DOCTYPE html>
    <html lang="ta"><head>
        <meta charset="UTF-8">
        <title>அச்சு பார்வை — <?php echo esc_html($post->post_title); ?></title>
        <link rel="stylesheet" href="<?php echo esc_url( get_template_directory_uri() . '/assets/fonts.css' ); ?>">
        <style>
            @page { margin: 2.5cm; size: A4; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: 'Noto Serif Tamil', Georgia, serif; color: #1A120A; background:#F6EFDD; padding: 3cm 2cm; max-width: 21cm; margin: 0 auto; font-size: 16pt; line-height: 2; }
            @media print { body { background: #fff; padding: 0; } .no-print { display: none !important; } }
            .toolbar { position: fixed; top: 18px; right: 18px; background: #1A120A; color:#fff; padding: 12px 18px; border-radius: 999px; box-shadow: 0 6px 20px rgba(0,0,0,0.25); display: flex; gap: 14px; align-items:center; z-index: 100; }
            .toolbar button, .toolbar a { background:#fff; color:#1A120A; border:none; font-family: inherit; font-size: 14pt; padding: 8px 18px; border-radius: 999px; cursor: pointer; text-decoration:none; }
            .toolbar a.close { background:transparent; color:#fff; padding: 8px; }
            .tag { font-style: italic; font-size: 12pt; color: #6E5638; margin-bottom: 12pt; }
            .draft-stamp { display:inline-block; padding:4pt 12pt; border:1pt solid #8B2020; color:#8B2020; font-size:10pt; letter-spacing:0.15em; transform: rotate(-3deg); margin-bottom: 18pt; }
            h1 { font-size: 32pt; font-weight: 400; line-height: 1.1; margin-bottom: 18pt; color: #1A120A; }
            .meta { font-size: 12pt; color: #6E5638; padding-bottom: 14pt; margin-bottom: 24pt; border-bottom: 1pt solid #D4C4A8; }
            .meta span + span::before { content: ' · '; color: #c0a780; }
            .ded { border-left: 3pt solid #A6741E; padding: 6pt 14pt; margin-bottom: 24pt; color: #A6741E; font-style: italic; font-size: 13pt; }
            .body { font-size: 16pt; line-height: 2.3; color: #3B2A1A; white-space: pre-line; }
            .body p { margin-bottom: 16pt; }
            .back { margin-top: 36pt; padding: 16pt 20pt; border-left: 3pt solid #C9952E; background: #FBF6E9; font-size: 12pt; line-height: 1.9; }
            .back .label { color: #6E5638; font-style: italic; margin-bottom: 6pt; }
            .footer { margin-top: 40pt; padding-top: 14pt; border-top: 1pt solid #D4C4A8; font-size: 11pt; color: #6E5638; font-style: italic; text-align: center; }
        </style>
    </head><body>
        <div class="toolbar no-print">
            <a class="close" href="<?php echo esc_url(get_edit_post_link($id)); ?>">← திரும்பு</a>
            <button onclick="window.print()">🖨 அச்சிடு / PDF</button>
        </div>

        <p class="tag">📝 அச்சு பார்வை — <?php
            $labels = ['publish'=>'வெளியிட்டது','draft'=>'வரைவு','future'=>'திட்டமிடப்பட்டது','private'=>'தனிப்பட்டது','trash'=>'அழிக்கப்பட்டது'];
            echo esc_html($labels[$post->post_status] ?? $post->post_status);
        ?></p>
        <?php if ($post->post_status === 'draft'): ?>
            <span class="draft-stamp">வரைவு · இன்னும் வெளியிடப்படவில்லை</span>
        <?php endif; ?>

        <h1><?php echo esc_html($post->post_title ?: '(தலைப்பு இல்லை)'); ?></h1>

        <div class="meta">
            <?php if ($year) echo '<span>' . esc_html($year) . '</span>'; ?>
            <?php if ($loc)  echo '<span>📍 ' . esc_html($loc) . '</span>'; ?>
            <?php if ($cat)  echo '<span>' . esc_html($cat) . '</span>'; ?>
            <span><?php echo esc_html(kv_name()); ?></span>
        </div>

        <?php if ($ded): ?>
            <div class="ded">💝 <?php echo esc_html($ded); ?></div>
        <?php endif; ?>

        <div class="body"><?php echo wp_kses_post(wpautop($post->post_content)); ?></div>

        <?php if ($back): ?>
            <div class="back">
                <div class="label">இந்த கவிதையின் கதை</div>
                <?php echo nl2br(esc_html($back)); ?>
            </div>
        <?php endif; ?>

        <div class="footer">
            <?php echo esc_html(kv_name()); ?> · <?php echo esc_html(kv_location()); ?>
        </div>
    </body></html>
    <?php
    exit;
});

// ═══════════════════════════════════════════════════════════
// 7. FRIENDLIER MEDIA UPLOAD PAGE
// ═══════════════════════════════════════════════════════════
add_action('admin_head-media-new.php', function() {
    ?>
    <style>
    /* Make the upload page much friendlier */
    .wrap > h1.wp-heading-inline { font-family:'Noto Serif Tamil',Georgia,serif !important; font-size:28px !important; font-weight:400 !important; color:#1A120A !important; }
    .wrap > h1 + .page-title-action { display:none; }
    #plupload-upload-ui { background:#FBF6E9 !important; border:3px dashed #C9952E !important; border-radius:8px !important; padding:60px 30px !important; }
    #plupload-upload-ui:hover { border-color:#8B2020 !important; }
    #drag-drop-area { min-height: 280px !important; }
    .drag-drop-inside p { font-family:'Noto Serif Tamil',Georgia,serif !important; font-size:18px !important; color:#3B2A1A !important; }
    .drag-drop-inside p.drag-drop-info { font-size:24px !important; color:#1A120A !important; margin-bottom:14px !important; }
    .drag-drop-buttons .button { background:#8B2020 !important; color:#fff !important; border:none !important; font-size:16px !important; padding:12px 28px !important; height:auto !important; border-radius:999px !important; font-family:'Noto Serif Tamil',serif !important; }
    .uploader-inline-content .max-upload-size { font-size:14px !important; color:#7D6448 !important; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Rewrite default text labels to Tamil
        var dropArea = document.querySelector('.drag-drop-inside');
        if (dropArea) {
            var labels = dropArea.querySelectorAll('p');
            if (labels[0]) labels[0].innerHTML = '🖼 படங்களை இங்கே இழுத்து விடுங்கள்';
            if (labels[1]) labels[1].textContent = 'அல்லது';
            if (labels[2]) {
                var btn = labels[2].querySelector('input,button');
                if (btn) btn.value = 'கணினியிலிருந்து தேர்வு செய்யவும்';
            }
        }
    });
    </script>
    <?php
});

// Pre-fill alt text suggestion when uploading
add_filter('attachment_fields_to_edit', function($fields, $post) {
    if (isset($fields['post_excerpt'])) {
        $fields['post_excerpt']['label'] = 'விளக்கம் (alt text)';
        $fields['post_excerpt']['helps'] = 'பார்வையற்றவர்களுக்கு உதவும்.';
    }
    if (isset($fields['post_title'])) {
        $fields['post_title']['label'] = 'தலைப்பு';
    }
    return $fields;
}, 10, 2);

// ═══════════════════════════════════════════════════════════
// 8. FOCAL-POINT PICKER for featured images on poems
// (A small selector in the Featured Image metabox that lets
//  her click where the face/focus is, persisted in post meta.)
// ═══════════════════════════════════════════════════════════
add_action('admin_footer-post.php',     'kv_focal_picker');
add_action('admin_footer-post-new.php', 'kv_focal_picker');
function kv_focal_picker() {
    $screen = get_current_screen();
    if ( ! in_array($screen->post_type ?? '', ['kavithai_gedicht','kavithai_nul','kavithai_virudu','page']) ) return;
    $post_id = (int)($_GET['post'] ?? 0);
    $fx = (int) get_post_meta($post_id, '_kv_focal_x', true) ?: 50;
    $fy = (int) get_post_meta($post_id, '_kv_focal_y', true) ?: 50;
    ?>
    <style>
    #kv-focal-box { padding:10px 12px 12px; border-top:1px solid rgba(125,100,72,.15); margin-top:8px; font-family:'Noto Serif Tamil',Georgia,serif; }
    #kv-focal-box .label { font-size:13px; color:#5C4530; margin-bottom:8px; }
    #kv-focal-canvas { position:relative; width:100%; aspect-ratio:16/9; background:#1A120A center/cover no-repeat; cursor:crosshair; border-radius:4px; overflow:hidden; }
    #kv-focal-canvas.empty { display:flex; align-items:center; justify-content:center; color:rgba(246,239,221,0.45); font-style:italic; font-size:13px; padding:0 10px; text-align:center; }
    #kv-focal-dot { position:absolute; width:24px; height:24px; border-radius:50%; background:#fff; border:3px solid #8B2020; box-shadow:0 0 0 1px rgba(0,0,0,0.2), 0 4px 10px rgba(0,0,0,0.3); transform:translate(-50%,-50%); pointer-events:none; transition: left .15s, top .15s; }
    #kv-focal-hint { font-size:12px; color:#7D6448; margin-top:8px; font-style:italic; }
    </style>
    <script>
    (function(){
        function init() {
            var metabox = document.getElementById('postimagediv');
            if (!metabox || document.getElementById('kv-focal-box')) return;
            var inside = metabox.querySelector('.inside') || metabox;
            var box = document.createElement('div');
            box.id = 'kv-focal-box';
            box.innerHTML = '<div class="label">🎯 புகைப்பட மையம் — முகம் எங்கே இருக்கிறது?</div>'
                + '<div id="kv-focal-canvas" class="empty">முதலில் "Set featured image" அழுத்தி ஒரு படத்தை சேர்க்கவும்.<div id="kv-focal-dot" style="display:none;"></div></div>'
                + '<div id="kv-focal-hint">படத்தில் கிளிக் செய்து முகம்/மையம் எங்கே என்று குறிக்கவும். கவிதைப் பக்கத்தில் இந்த இடம் காட்டப்படும்.</div>'
                + '<input type="hidden" name="kv_focal_x" id="kv_focal_x" value="<?php echo $fx; ?>">'
                + '<input type="hidden" name="kv_focal_y" id="kv_focal_y" value="<?php echo $fy; ?>">';
            inside.appendChild(box);

            var canvas = document.getElementById('kv-focal-canvas');
            var dot    = document.getElementById('kv-focal-dot');

            function refresh() {
                var img = metabox.querySelector('#set-post-thumbnail img');
                if (img && img.src) {
                    canvas.classList.remove('empty');
                    canvas.style.backgroundImage = 'url("' + img.src + '")';
                    var fx = parseInt(document.getElementById('kv_focal_x').value)||50;
                    var fy = parseInt(document.getElementById('kv_focal_y').value)||50;
                    canvas.style.backgroundPosition = fx + '% ' + fy + '%';
                    dot.style.display = 'block';
                    dot.style.left = fx + '%';
                    dot.style.top  = fy + '%';
                } else {
                    canvas.classList.add('empty');
                    canvas.style.backgroundImage = '';
                    dot.style.display = 'none';
                }
            }
            canvas.addEventListener('click', function(e) {
                if (canvas.classList.contains('empty')) return;
                var rect = canvas.getBoundingClientRect();
                var x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
                var y = Math.round(((e.clientY - rect.top)  / rect.height) * 100);
                x = Math.max(0, Math.min(100, x));
                y = Math.max(0, Math.min(100, y));
                document.getElementById('kv_focal_x').value = x;
                document.getElementById('kv_focal_y').value = y;
                dot.style.left = x + '%';
                dot.style.top  = y + '%';
                canvas.style.backgroundPosition = x + '% ' + y + '%';
            });

            // Watch for thumbnail changes
            var obs = new MutationObserver(refresh);
            obs.observe(metabox, { childList: true, subtree: true });
            refresh();
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
        else init();
    })();
    </script>
    <?php
}

// Save focal-point on post save — piggybacks on the kv_meta nonce.
add_action( 'save_post', function( $id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['kv_nonce'] ) || ! wp_verify_nonce( $_POST['kv_nonce'], 'kv_meta' ) ) return;
    if ( ! current_user_can( 'edit_post', $id ) ) return;
    if ( isset( $_POST['kv_focal_x'] ) ) update_post_meta( $id, '_kv_focal_x', max( 0, min( 100, (int) $_POST['kv_focal_x'] ) ) );
    if ( isset( $_POST['kv_focal_y'] ) ) update_post_meta( $id, '_kv_focal_y', max( 0, min( 100, (int) $_POST['kv_focal_y'] ) ) );
} );

// Helper to use focal point on frontend
function kv_focal_style($post_id) {
    $fx = (int) get_post_meta($post_id, '_kv_focal_x', true) ?: 50;
    $fy = (int) get_post_meta($post_id, '_kv_focal_y', true) ?: 50;
    return "object-position: {$fx}% {$fy}%; --focal-x:{$fx}%; --focal-y:{$fy}%;";
}
