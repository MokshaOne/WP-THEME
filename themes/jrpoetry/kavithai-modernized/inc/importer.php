<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ═══════════════════════════════════════════════════════════
// KAVITHAI IMPORTER — v3
// Fixes: bold-fragmentation, category select/create, robust title parsing
// ═══════════════════════════════════════════════════════════

add_action( 'admin_menu', function() {
    add_menu_page(
        'இறக்குமதி',
        '📥 இறக்குமதி',
        'edit_posts',
        'kavithai-import',
        'kv_importer_page',
        'dashicons-download',
        4
    );
}, 5 );

// ── AJAX: create new category on-the-fly ──────────────────
add_action( 'wp_ajax_kv_create_cat', function() {
    check_ajax_referer('kv_create_cat_nonce','nonce');
    if ( ! current_user_can('manage_categories') ) wp_send_json_error('Permission denied');
    $name = sanitize_text_field($_POST['name'] ?? '');
    if ( ! $name ) wp_send_json_error('No name');
    $existing = get_term_by('name', $name, 'kavithai_cat');
    if ($existing) { wp_send_json_success(['id'=>$existing->term_id,'name'=>$existing->name]); return; }
    $r = wp_insert_term($name, 'kavithai_cat');
    if (is_wp_error($r)) wp_send_json_error($r->get_error_message());
    wp_send_json_success(['id'=>$r['term_id'],'name'=>$name]);
});

// ── MAIN PAGE ─────────────────────────────────────────────
function kv_importer_page() {
    $result = null;
    $error  = null;

    if ( isset($_POST['kv_import_docx']) && check_admin_referer('kv_import') ) {
        if ( ! empty($_FILES['kv_docx_file']['tmp_name']) ) {
            $mode = sanitize_text_field($_POST['kv_import_mode'] ?? 'single');
            $cats = array_map('absint', (array)($_POST['kv_categories'] ?? []));
            $result = $mode === 'book'
                ? kv_import_docx_as_book($_FILES['kv_docx_file'], $cats)
                : kv_import_docx_single($_FILES['kv_docx_file'], $cats);
            if (is_wp_error($result)) { $error = $result->get_error_message(); $result = null; }
        } else {
            $error = 'கோப்பு தேர்வு செய்யவில்லை.';
        }
    }

    if ( isset($_POST['kv_import_gdocs']) && check_admin_referer('kv_import') ) {
        $url  = esc_url_raw(trim($_POST['kv_gdocs_url'] ?? ''));
        $mode = sanitize_text_field($_POST['kv_gdocs_mode'] ?? 'single');
        $cats = array_map('absint', (array)($_POST['kv_categories_g'] ?? []));
        if ($url) {
            $result = $mode === 'book'
                ? kv_import_gdocs_as_book($url, $cats)
                : kv_import_gdocs_single($url, $cats);
            if (is_wp_error($result)) { $error = $result->get_error_message(); $result = null; }
        } else {
            $error = 'இணைப்பு இல்லை.';
        }
    }

    // All existing categories
    $all_cats = get_terms(['taxonomy'=>'kavithai_cat','hide_empty'=>false,'orderby'=>'name']);
    ?>
    <style>
    .kv-imp{max-width:820px;margin:28px auto;font-family:'Noto Serif Tamil',Georgia,serif}
    .kv-imp h1{font-size:26px;font-weight:400;color:#1A0E06;margin-bottom:6px}
    .kv-sub{color:#7D6448;font-size:15px;margin-bottom:30px;line-height:1.8}
    .kv-card{background:#fff;border:1px solid rgba(125,100,72,.2);padding:28px 32px;margin-bottom:20px;border-radius:4px}
    .kv-card h2{font-size:18px;font-weight:400;color:#2B1A0C;margin:0 0 20px;padding-bottom:12px;border-bottom:1px solid rgba(125,100,72,.12);display:flex;align-items:center;gap:10px}
    .kv-label{display:block;font-size:14px;color:#7D6448;margin:0 0 6px}
    .kv-hint{display:block;font-size:12px;color:#bbb;margin-top:2px}
    .kv-input,.kv-textarea{width:100%;padding:10px 14px;font-size:16px;font-family:inherit;border:1px solid rgba(125,100,72,.3);background:#FAF5E8;color:#1A0E06;border-radius:4px;box-sizing:border-box;margin-bottom:16px}
    .kv-input:focus,.kv-textarea:focus{outline:none;border-color:#8B2020}
    .kv-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .kv-btn{width:100%;background:#8B2020;color:#fff;border:none;padding:15px;font-size:16px;font-family:inherit;cursor:pointer;border-radius:4px;letter-spacing:.03em}
    .kv-btn:hover{opacity:.88}
    .kv-btn-green{background:#1A6B3A}
    .kv-mode{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
    .kv-mode label{display:flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid rgba(125,100,72,.3);cursor:pointer;font-size:15px;background:#FAF5E8;border-radius:4px;flex:1;min-width:160px;transition:border-color .2s,background .2s}
    .kv-mode label:has(input:checked){border-color:#8B2020;background:#FDF5F0}
    .kv-drop{border:2px dashed rgba(125,100,72,.3);padding:28px;text-align:center;background:#FAF5E8;margin-bottom:16px;border-radius:4px;cursor:pointer}
    .kv-drop:hover{border-color:#8B2020}
    .kv-drop .icon{font-size:32px;margin-bottom:8px}
    .kv-drop .main{font-size:15px;color:#4A3420;margin:0 0 4px}
    .kv-drop .small{font-size:13px;color:#7D6448;margin:0}
    .kv-fname{font-size:14px;color:#8B2020;margin-top:8px;font-weight:500;min-height:20px}

    /* Category widget */
    .kv-cat-box{border:1px solid rgba(125,100,72,.25);border-radius:4px;background:#FAF5E8;margin-bottom:16px;overflow:hidden}
    .kv-cat-list{max-height:160px;overflow-y:auto;padding:10px 14px}
    .kv-cat-list label{display:flex;align-items:center;gap:8px;padding:5px 0;font-size:15px;color:#2B1A0C;cursor:pointer;border-bottom:1px solid rgba(125,100,72,.08)}
    .kv-cat-list label:last-child{border-bottom:none}
    .kv-cat-list input[type=checkbox]{width:16px;height:16px;cursor:pointer}
    .kv-cat-add{display:flex;border-top:1px solid rgba(125,100,72,.2);background:#F0E9D4}
    .kv-cat-add input{flex:1;border:none;background:transparent;padding:10px 14px;font-size:15px;font-family:inherit;outline:none;color:#1A0E06}
    .kv-cat-add button{background:#8B2020;color:#fff;border:none;padding:10px 16px;cursor:pointer;font-size:14px;font-family:inherit;white-space:nowrap}
    .kv-cat-add button:hover{opacity:.88}

    .kv-info{background:#F0F8F4;border:1px solid rgba(42,106,58,.2);padding:14px 18px;margin-bottom:18px;border-radius:4px}
    .kv-info p{font-size:14px;color:#1A4A2A;margin:0 0 6px;font-weight:500}
    .kv-info ol{font-size:14px;color:#2A6A3A;margin:0;padding-left:18px;line-height:2}
    .success-box{background:#F0FEF4;border-left:4px solid #2A6B2A;padding:20px 24px;margin-bottom:24px;border-radius:0 4px 4px 0}
    .success-box p{font-size:17px;color:#1A4A1A;margin-bottom:8px}
    .success-box .meta{font-size:14px;color:#3A8A3A;margin-bottom:14px}
    .success-btns{display:flex;gap:10px;flex-wrap:wrap}
    .success-btns a{display:inline-block;padding:11px 20px;font-size:15px;text-decoration:none;border-radius:4px;color:#fff}
    .err-box{background:#FEF0F0;border-left:4px solid #8B2020;padding:16px 20px;margin-bottom:24px;font-size:15px;color:#6B1414;border-radius:0 4px 4px 0}
    .bulk-box{background:#F0FEF4;border-left:4px solid #2A6B2A;padding:20px 24px;margin-bottom:24px;border-radius:0 4px 4px 0}
    .bulk-box h3{font-size:17px;color:#1A4A1A;margin-bottom:10px}
    .bulk-list{list-style:none;padding:0;margin:0 0 16px}
    .bulk-list li{font-size:14px;color:#2A6A3A;padding:5px 0;border-bottom:1px solid rgba(42,106,58,.12);display:flex;justify-content:space-between}
    .bulk-list a{color:#8B2020;font-size:13px}
    @media(max-width:600px){.kv-row2{grid-template-columns:1fr}.kv-card{padding:18px}}
    </style>

    <script>
    function kvAddCat(suffix) {
        var input = document.getElementById('kv_new_cat_' + suffix);
        var list  = document.getElementById('kv_cat_list_' + suffix);
        var name  = input.value.trim();
        if (!name) return;
        input.disabled = true;

        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'action=kv_create_cat&nonce=<?php echo wp_create_nonce('kv_create_cat_nonce'); ?>&name=' + encodeURIComponent(name)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var id   = data.data.id;
                var lbl  = document.createElement('label');
                lbl.innerHTML = '<input type="checkbox" name="kv_categories' +
                    (suffix==='g' ? '_g' : '') +
                    '[]" value="' + id + '" checked> ' + data.data.name;
                list.insertBefore(lbl, list.firstChild);
                input.value = '';
            } else {
                alert('பிழை: ' + data.data);
            }
            input.disabled = false;
        });
    }
    </script>

    <div class="kv-imp">
        <h1>📥 கவிதை இறக்குமதி</h1>
        <p class="kv-sub">
            ஒரு கவிதை அல்லது முழு நூல் — இரண்டையும் இறக்குமதி செய்யலாம்.<br>
            Word கோப்பு அல்லது Google Docs — இரண்டும் வேலை செய்யும்.
        </p>

        <?php if ($error) : ?>
        <div class="err-box">⚠ <?php echo esc_html($error); ?></div>
        <?php endif; ?>

        <?php if ($result) : ?>
            <?php if (isset($result['bulk'])) : ?>
            <div class="bulk-box">
                <h3>✓ <?php echo $result['count']; ?> கவிதைகள் இறக்குமதி செய்யப்பட்டன!</h3>
                <p style="font-size:14px;color:#2A6A3A;margin-bottom:12px;">
                    நூல்: <strong><?php echo esc_html($result['book_title']); ?></strong>
                    <?php if (!empty($result['cats'])) echo ' · வகை: ' . esc_html(implode(', ', $result['cats'])); ?>
                </p>
                <ul class="bulk-list">
                    <?php foreach (array_slice($result['poems'],0,10) as $p) : ?>
                    <li><span><?php echo esc_html($p['title']); ?></span><a href="<?php echo esc_url(get_edit_post_link($p['id'])); ?>">திருத்து →</a></li>
                    <?php endforeach; ?>
                    <?php if ($result['count']>10) echo '<li style="color:#7D6448;font-style:italic;font-size:13px;">...மேலும் '.($result['count']-10).' கவிதைகள்</li>'; ?>
                </ul>
                <div class="success-btns">
                    <a href="<?php echo admin_url('edit.php?post_type=kavithai_gedicht'); ?>" style="background:#8B2020;">எல்லா கவிதைகளும் →</a>
                    <a href="<?php echo admin_url('admin.php?page=kavithai-import'); ?>" style="background:#4A3420;">மேலும் இறக்குமதி</a>
                </div>
            </div>
            <?php else : ?>
            <div class="success-box">
                <p>✓ வெற்றிகரமாக இறக்குமதி!</p>
                <div class="meta">
                    <strong><?php echo esc_html($result['title']); ?></strong> ·
                    <?php echo esc_html($result['type_label']); ?> ·
                    <?php echo $result['lines']; ?> வரிகள்
                    <?php if (!empty($result['cats'])) echo ' · வகை: ' . esc_html(implode(', ', $result['cats'])); ?>
                </div>
                <div class="success-btns">
                    <a href="<?php echo esc_url(get_edit_post_link($result['post_id'])); ?>" style="background:#8B2020;">✎ திருத்தவும்</a>
                    <a href="<?php echo esc_url(get_permalink($result['post_id'])); ?>" target="_blank" style="background:#2B1A0C;">👁 காண்க</a>
                    <a href="<?php echo admin_url('admin.php?page=kavithai-import'); ?>" style="background:#4A3420;">+ மேலும்</a>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ══ CARD 1: WORD ══ -->
        <div class="kv-card">
            <h2>📄 Word கோப்பு (.docx)</h2>
            <form method="POST" enctype="multipart/form-data">
                <?php wp_nonce_field('kv_import'); ?>

                <label class="kv-label" style="margin-bottom:10px;font-size:15px;color:#2B1A0C;font-weight:500;">இந்த கோப்பு என்ன?</label>
                <div class="kv-mode">
                    <label><input type="radio" name="kv_import_mode" value="single" checked>
                        ✍ ஒரு கவிதை / கதை<br><span style="font-size:12px;color:#7D6448;margin-left:22px;">ஒரே ஒரு படைப்பு</span>
                    </label>
                    <label><input type="radio" name="kv_import_mode" value="book">
                        📚 முழு நூல் (பல கவிதைகள்)<br><span style="font-size:12px;color:#7D6448;margin-left:22px;">உள்ளடக்கத்துடன் முழு நூல்</span>
                    </label>
                </div>

                <div class="kv-row2">
                    <div>
                        <label class="kv-label">ஆண்டு <span class="kv-hint">விருப்பம்</span></label>
                        <input class="kv-input" type="number" name="kv_docx_year" min="1940" max="2099" placeholder="<?php echo date('Y'); ?>">
                    </div>
                    <div>
                        <label class="kv-label">குறிப்பு <span class="kv-hint">விருப்பம்</span></label>
                        <input class="kv-input" type="text" name="kv_docx_note" placeholder="உ.தா.: முதல் தொகுப்பு">
                    </div>
                </div>

                <!-- Category selector -->
                <?php kv_render_cat_selector($all_cats, ''); ?>

                <div class="kv-drop" onclick="document.getElementById('kv_fi').click()">
                    <div class="icon">📁</div>
                    <p class="main">இங்கே கிளிக் செய்யுங்கள்</p>
                    <p class="small">.docx கோப்பை தேர்வு செய்யவும்</p>
                    <p class="kv-fname" id="kv_fn"></p>
                </div>
                <input type="file" id="kv_fi" name="kv_docx_file" accept=".docx" style="display:none"
                       onchange="document.getElementById('kv_fn').textContent=this.files[0]?.name||''">

                <button type="submit" name="kv_import_docx" class="kv-btn">📥 இறக்குமதி செய்யுங்கள்</button>
            </form>
        </div>

        <!-- ══ CARD 2: GOOGLE DOCS ══ -->
        <div class="kv-card">
            <h2>📝 Google Docs</h2>
            <div class="kv-info">
                <p>📋 எப்படி பயன்படுத்துவது:</p>
                <ol>
                    <li>Google Docs-ல் கோப்பை திறக்கவும்</li>
                    <li><strong>பகிர் (Teilen)</strong> → <strong>இணைப்பை நகலெடு</strong></li>
                    <li><em>"யாரும் பார்க்கலாம்" (Jeder mit dem Link)</em> என அமைக்கவும்</li>
                    <li>இணைப்பை கீழே ஒட்டவும்</li>
                </ol>
            </div>
            <form method="POST">
                <?php wp_nonce_field('kv_import'); ?>

                <div class="kv-mode">
                    <label><input type="radio" name="kv_gdocs_mode" value="single" checked>
                        ✍ ஒரு கவிதை / கதை<br><span style="font-size:12px;color:#7D6448;margin-left:22px;">ஒரே ஒரு படைப்பு</span>
                    </label>
                    <label><input type="radio" name="kv_gdocs_mode" value="book">
                        📚 முழு நூல்<br><span style="font-size:12px;color:#7D6448;margin-left:22px;">பல கவிதைகள் ஒரே கோப்பில்</span>
                    </label>
                </div>

                <div class="kv-row2">
                    <div>
                        <label class="kv-label">ஆண்டு</label>
                        <input class="kv-input" type="number" name="kv_gdocs_year" min="1940" max="2099" placeholder="<?php echo date('Y'); ?>">
                    </div>
                    <div>
                        <label class="kv-label">குறிப்பு</label>
                        <input class="kv-input" type="text" name="kv_gdocs_note" placeholder="விருப்பம்">
                    </div>
                </div>

                <?php kv_render_cat_selector($all_cats, 'g'); ?>

                <label class="kv-label">Google Docs இணைப்பு</label>
                <input class="kv-input" type="url" name="kv_gdocs_url"
                       placeholder="https://docs.google.com/document/d/..."
                       style="font-family:monospace;font-size:14px;">

                <button type="submit" name="kv_import_gdocs" class="kv-btn kv-btn-green">📥 இறக்குமதி செய்யுங்கள்</button>
            </form>
        </div>

        <!-- ══ RECENT ══ -->
        <?php
        $recent = new WP_Query(['post_type'=>['kavithai_gedicht','kavithai_nul'],'posts_per_page'=>6,'meta_key'=>'_kv_imported','meta_value'=>'1','no_found_rows'=>true,'orderby'=>'date','order'=>'DESC']);
        if ($recent->have_posts()) : ?>
        <div style="background:#FAF5E8;border:1px solid rgba(125,100,72,.15);padding:20px 24px;border-radius:4px;">
            <h3 style="font-size:15px;font-weight:400;color:#4A3420;margin:0 0 14px;border-bottom:1px solid rgba(125,100,72,.15);padding-bottom:10px;">🕐 சமீபத்திய இறக்குமதிகள்</h3>
            <?php while ($recent->have_posts()) : $recent->the_post(); ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(125,100,72,.08);">
                <span style="font-size:15px;color:#2B1A0C;"><?php the_title(); ?></span>
                <div style="display:flex;gap:12px;">
                    <a href="<?php echo esc_url(get_edit_post_link()); ?>" style="font-size:13px;color:#8B2020;">திருத்து</a>
                    <a href="<?php echo esc_url(get_permalink()); ?>" target="_blank" style="font-size:13px;color:#7D6448;">காண்க</a>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// ── CATEGORY SELECTOR WIDGET ──────────────────────────────
function kv_render_cat_selector($cats, $suffix) {
    $field = 'kv_categories' . ($suffix ? '_'.$suffix : '');
    $uid   = 'kv_cat_' . ($suffix ?: 'd'); // unique id prefix
    ?>
    <div style="margin-bottom:16px;">
        <label class="kv-label">வகை <span class="kv-hint">ஒன்று அல்லது அதிகம் தேர்வு செய்யலாம். புதியதை உருவாக்கலாம்.</span></label>
        <div class="kv-cat-box">
            <div class="kv-cat-list" id="kv_cat_list_<?php echo esc_attr($suffix ?: ''); ?>">
                <?php if (empty($cats)) : ?>
                <div style="font-size:14px;color:#7D6448;padding:8px 0;font-style:italic;">
                    இன்னும் வகைகள் இல்லை. கீழே புதிதாக உருவாக்கவும்.
                </div>
                <?php else : foreach ($cats as $cat) : ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($field); ?>[]" value="<?php echo esc_attr($cat->term_id); ?>">
                    <?php echo esc_html($cat->name); ?>
                    <span style="font-size:12px;color:#bbb;margin-left:4px;">(<?php echo $cat->count; ?>)</span>
                </label>
                <?php endforeach; endif; ?>
            </div>
            <div class="kv-cat-add">
                <input type="text" id="kv_new_cat_<?php echo esc_attr($suffix ?: ''); ?>"
                       placeholder="புதிய வகை பெயர்..."
                       onkeydown="if(event.key==='Enter'){event.preventDefault();kvAddCat('<?php echo esc_js($suffix ?: ''); ?>')}">
                <button type="button" onclick="kvAddCat('<?php echo esc_js($suffix ?: ''); ?>')">+ சேர்</button>
            </div>
        </div>
    </div>
    <?php
}

// ═══════════════════════════════════════════════════════════
// DOCX HELPERS
// ═══════════════════════════════════════════════════════════
function kv_validate_docx($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return new WP_Error('upload','Upload fehlgeschlagen.');
    if (strtolower(pathinfo($file['name'],PATHINFO_EXTENSION)) !== 'docx') return new WP_Error('format','.docx கோப்பு மட்டும்.');
    if (!class_exists('ZipArchive')) return new WP_Error('zip','ZipArchive extension இல்லை.');
    return true;
}

function kv_read_docx_xml($file) {
    $v = kv_validate_docx($file);
    if (is_wp_error($v)) return $v;
    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== true) return new WP_Error('zip','கோப்பை திறக்க முடியவில்லை.');
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    if (!$xml) return new WP_Error('xml','உள்ளடக்கம் இல்லை.');
    return $xml;
}

// ═══════════════════════════════════════════════════════════
// XML → STRUCTURED TEXT
// Key insight: Word often splits bold runs with ** ** ** ** fragments.
// We read paragraph-by-paragraph, check if ANY run in the paragraph is bold,
// and treat the whole paragraph text as bold.
// Also handles Heading1/2 styles as titles.
// ═══════════════════════════════════════════════════════════
function kv_xml_to_paragraphs($xml) {
    // Normalize namespace prefixes
    $xml = preg_replace('/\s+xmlns(?::[a-z0-9]+)?="[^"]*"/i', '', $xml);
    $xml = preg_replace('/<(\/?)w:([a-zA-Z]+)/i', '<$1w_$2', $xml);

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadXML($xml);
    libxml_clear_errors();

    $paragraphs = [];
    $body = $dom->getElementsByTagName('body')->item(0);
    if (!$body) return $paragraphs;

    foreach ($body->childNodes as $node) {
        if ($node->nodeName !== 'w_p') continue;

        $text    = '';
        $is_bold = false;
        $is_heading = false;

        // Check paragraph style
        foreach ($node->childNodes as $child) {
            if ($child->nodeName === 'w_pPr') {
                foreach ($child->childNodes as $ppc) {
                    if ($ppc->nodeName === 'w_pStyle') {
                        $val = strtolower($ppc->getAttribute('w:val') ?: '');
                        if (!$val) $val = strtolower($ppc->attributes->getNamedItem('val')?->nodeValue ?? '');
                        if (strpos($val,'heading') !== false || strpos($val,'berschrift') !== false) {
                            $is_heading = true;
                            $is_bold    = true;
                        }
                    }
                }
            }
        }

        // Collect runs
        foreach ($node->getElementsByTagName('w_r') as $run) {
            $run_bold = false;
            foreach ($run->childNodes as $rc) {
                if ($rc->nodeName === 'w_rPr') {
                    foreach ($rc->childNodes as $rpc) {
                        if ($rpc->nodeName === 'w_b') { $run_bold = true; $is_bold = true; }
                    }
                }
            }
            foreach ($run->getElementsByTagName('w_t') as $t) {
                $text .= $t->nodeValue;
            }
        }

        $text = trim($text);
        if ($text === '') { $paragraphs[] = ['text'=>'','bold'=>false,'heading'=>false]; continue; }

        $paragraphs[] = ['text'=>$text, 'bold'=>$is_bold, 'heading'=>$is_heading];
    }

    return $paragraphs;
}

// ═══════════════════════════════════════════════════════════
// BOOK PARSER — understands many title formats:
//   "தமிழ் --- 1"         bold with dash-number
//   "தமிழ் 1"             bold with just number
//   "1. தமிழ்"            numbered list style
//   "தமிழ்"              heading style paragraph
//   "கண்ணீர் அஞ்சலி --- 21,22,23"  grouped poems
// ═══════════════════════════════════════════════════════════
function kv_parse_book_paragraphs($paragraphs) {
    $poems       = [];
    $current     = null;
    $book_title  = '';
    $in_toc      = false;
    $content_started = false;

    foreach ($paragraphs as $para) {
        $text = $para['text'];
        $bold = $para['bold'];

        // Empty line = stanza break inside poem
        if ($text === '') {
            if ($current !== null) $current['lines'][] = '';
            continue;
        }

        // Detect TOC heading
        if ($bold && preg_match('/உள்ளடக்கம்/u', $text)) {
            $in_toc = true; continue;
        }

        // TOC items (lines starting with - or tab, or short lines in toc block)
        if ($in_toc) {
            // Exit TOC when we hit first bold poem title with number/dash
            if ($bold && kv_looks_like_poem_title($text)) {
                $in_toc = true; // will be exited below
            } else {
                continue; // still in TOC
            }
        }

        // Book-level title: first bold text before any poem
        if ($bold && empty($poems) && empty($book_title) && !$in_toc && !kv_looks_like_poem_title($text)) {
            $book_title = kv_clean_bold_fragments($text);
            continue;
        }

        // Subtitle / meter description: short non-bold line at top
        if (!$bold && empty($poems) && strlen($text) < 50 && !$content_started) {
            continue;
        }

        // ── POEM HEADING DETECTION ──────────────────────────
        $is_poem_title = $bold && kv_looks_like_poem_title($text);

        // Also detect numbered list style: "1. தமிழ்" or "1) தமிழ்"
        if (!$is_poem_title && preg_match('/^\d+[\.\)]\s+\S/u', $text)) {
            $is_poem_title = true;
            $bold = true;
        }

        if ($is_poem_title) {
            $in_toc = false;
            $content_started = true;

            if ($current !== null) $poems[] = kv_finalize_poem($current);

            $parsed = kv_parse_title_text( kv_clean_bold_fragments($text) );
            $current = ['title'=>$parsed['title'], 'number'=>$parsed['number'], 'lines'=>[]];
            continue;
        }

        // Regular content
        if ($current !== null) {
            if (preg_match('/^\d+$/', $text)) continue; // skip lone page numbers
            $current['lines'][] = $text;
            $content_started = true;
        }
    }

    if ($current !== null) $poems[] = kv_finalize_poem($current);
    return ['book_title'=>$book_title, 'poems'=>$poems];
}

// Does this bold text look like a poem heading?
function kv_looks_like_poem_title($text) {
    // Has dashes: "தமிழ் --- 1" or "போர் --– 6"
    if (preg_match('/[-–—]{2,}/u', $text)) return true;
    // Ends with numbers: "தமிழ் 1" or "கவிதை 9,10"
    if (preg_match('/\d+[\,\d\s]*$/u', $text) && mb_strlen($text) > 2) return true;
    // Starts with number+dot: "1. தமிழ்"
    if (preg_match('/^\d+[\.\)]/u', $text)) return true;
    // Is a heading style paragraph
    return false;
}

// Word sometimes splits "தமிழ் --- 1" into separate bold runs joined as
// "தமிழ்   ---  1" with extra spaces — clean that up
function kv_clean_bold_fragments($text) {
    // Collapse multiple spaces
    $text = preg_replace('/\s{2,}/', ' ', $text);
    // Normalize dashes (various unicode dashes → ---)
    $text = preg_replace('/[-–—]{2,}/u', '---', $text);
    return trim($text);
}

// Parse "தமிழ் --- 1" or "1. தமிழ்" into [title, number]
function kv_parse_title_text($text) {
    // Pattern: Title --- Number
    if (preg_match('/^(.+?)---(.+)$/u', $text, $m)) {
        return ['title'=>trim($m[1]), 'number'=>trim($m[2])];
    }
    // Pattern: Number. Title
    if (preg_match('/^(\d+)[\.\)]\s+(.+)$/u', $text, $m)) {
        return ['title'=>trim($m[2]), 'number'=>trim($m[1])];
    }
    // Pattern: Title (number) at end
    if (preg_match('/^(.+?)\s+\(?([\d,\s]+)\)?$/u', $text, $m)) {
        return ['title'=>trim($m[1]), 'number'=>trim($m[2])];
    }
    return ['title'=>$text, 'number'=>''];
}

function kv_finalize_poem($block) {
    $lines = $block['lines'];
    while (!empty($lines) && trim($lines[0])            === '') array_shift($lines);
    while (!empty($lines) && trim($lines[count($lines)-1]) === '') array_pop($lines);
    return ['title'=>$block['title'], 'number'=>$block['number'], 'content'=>implode("\n",$lines)];
}

// ═══════════════════════════════════════════════════════════
// IMPORT FUNCTIONS
// ═══════════════════════════════════════════════════════════
function kv_import_docx_single($file, $cats=[]) {
    $xml = kv_read_docx_xml($file);
    if (is_wp_error($xml)) return $xml;
    $paras = kv_xml_to_paragraphs($xml);
    // Single: first bold/heading = title, rest = content
    $title = ''; $lines = []; $found = false;
    foreach ($paras as $p) {
        if (!$found && $p['text'] !== '') { $title = $p['text']; $found = true; continue; }
        if ($found) $lines[] = $p['text'];
    }
    while (!empty($lines) && $lines[0]==='') array_shift($lines);
    while (!empty($lines) && $lines[count($lines)-1]==='') array_pop($lines);
    $parsed = ['title'=>$title, 'content'=>implode("\n",$lines)];
    return kv_save_single($parsed,
        sanitize_text_field($_POST['kv_docx_type'] ?? 'kavithai_gedicht'),
        sanitize_text_field($_POST['kv_docx_year'] ?? ''),
        sanitize_text_field($_POST['kv_docx_note'] ?? ''),
        $file['name'], $cats);
}

function kv_import_docx_as_book($file, $cats=[]) {
    $xml = kv_read_docx_xml($file);
    if (is_wp_error($xml)) return $xml;
    $paras  = kv_xml_to_paragraphs($xml);
    $parsed = kv_parse_book_paragraphs($paras);
    return kv_save_book($parsed,
        sanitize_text_field($_POST['kv_docx_year'] ?? ''),
        sanitize_text_field($_POST['kv_docx_note'] ?? ''),
        $file['name'], $cats);
}

function kv_import_gdocs_single($url, $cats=[]) {
    $text = kv_fetch_gdoc($url);
    if (is_wp_error($text)) return $text;
    $lines = explode("\n", $text);
    $title = ''; $body = []; $found = false;
    foreach ($lines as $l) {
        $l = trim($l);
        if (!$found && $l!=='') { $title=$l; $found=true; continue; }
        if ($found) $body[] = $l;
    }
    while (!empty($body) && $body[0]==='') array_shift($body);
    while (!empty($body) && $body[count($body)-1]==='') array_pop($body);
    return kv_save_single(['title'=>$title,'content'=>implode("\n",$body)],
        sanitize_text_field($_POST['kv_gdocs_type'] ?? 'kavithai_gedicht'),
        sanitize_text_field($_POST['kv_gdocs_year'] ?? ''),
        sanitize_text_field($_POST['kv_gdocs_note'] ?? ''),
        'Google Docs', $cats);
}

function kv_import_gdocs_as_book($url, $cats=[]) {
    $text = kv_fetch_gdoc($url);
    if (is_wp_error($text)) return $text;
    // Convert plain text to fake paragraphs (no bold info available)
    // Use heading detection by line: short all-caps or followed by number
    $lines  = explode("\n", $text);
    $paras  = [];
    foreach ($lines as $l) {
        $t = trim($l);
        $bold = kv_looks_like_poem_title($t) || (mb_strlen($t)<60 && preg_match('/---/u',$t));
        $paras[] = ['text'=>$t,'bold'=>$bold,'heading'=>false];
    }
    $parsed = kv_parse_book_paragraphs($paras);
    return kv_save_book($parsed,
        sanitize_text_field($_POST['kv_gdocs_year'] ?? ''),
        sanitize_text_field($_POST['kv_gdocs_note'] ?? ''),
        'Google Docs', $cats);
}

// ── SAVE SINGLE ───────────────────────────────────────────
function kv_save_single($parsed, $post_type, $year, $note, $source, $cats=[]) {
    $title   = $parsed['title'] ?: '['.date('d.m.Y').']';
    $content = $parsed['content'] ?: '';
    $lines   = count(array_filter(explode("\n",$content), fn($l)=>trim($l)!==''));

    $id = wp_insert_post(['post_title'=>sanitize_text_field($title),'post_content'=>kv_to_blocks($content),'post_excerpt'=>mb_substr($content,0,150),'post_status'=>'draft','post_type'=>$post_type],true);
    if (is_wp_error($id)) return $id;

    if ($year) update_post_meta($id,'_kv_year',$year);
    if ($note) update_post_meta($id,'_kv_note',$note);
    update_post_meta($id,'_kv_imported','1');
    update_post_meta($id,'_kv_import_source',$source);

    $cat_names = [];
    if (!empty($cats)) {
        wp_set_object_terms($id,$cats,'kavithai_cat');
        foreach ($cats as $cid) { $t=get_term($cid,'kavithai_cat'); if($t&&!is_wp_error($t)) $cat_names[]=$t->name; }
    }

    $types=['kavithai_gedicht'=>'கவிதை','kavithai_nul'=>'நூல்'];
    return ['post_id'=>$id,'title'=>$title,'type_label'=>$types[$post_type]??$post_type,'lines'=>$lines,'cats'=>$cat_names];
}

// ── SAVE BOOK ─────────────────────────────────────────────
function kv_save_book($parsed, $year, $note, $source, $cats=[]) {
    if (empty($parsed['poems'])) return new WP_Error('no_poems','கவிதைகள் கண்டுபிடிக்கவில்லை. கோப்பு வடிவம் வேறாக இருக்கலாம்.');

    $book_title = $parsed['book_title'] ?: basename($source,'.docx');

    // Create book entry
    $book_id = wp_insert_post(['post_title'=>sanitize_text_field($book_title),'post_content'=>count($parsed['poems']).' கவிதைகள்.','post_status'=>'draft','post_type'=>'kavithai_nul']);
    if ($book_id && !is_wp_error($book_id)) {
        if ($year) update_post_meta($book_id,'_kv_year',$year);
        update_post_meta($book_id,'_kv_imported','1');
        if (!empty($cats)) wp_set_object_terms($book_id,$cats,'kavithai_cat');

        // CP4: keep the original .docx attached to the book post so the
        // reader can download the source file alongside the split poems.
        if ( ! empty( $_FILES['kv_docx_file']['tmp_name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            // Allow .docx for this upload even if WP's default filter blocks it.
            $allow_docx = function( $mimes ) {
                $mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                return $mimes;
            };
            add_filter( 'upload_mimes', $allow_docx, 999 );
            $att_id = media_handle_upload( 'kv_docx_file', $book_id, [
                'post_title'   => $book_title,
                'post_excerpt' => 'Original .docx source for ' . $book_title,
            ] );
            remove_filter( 'upload_mimes', $allow_docx, 999 );
            if ( $att_id && ! is_wp_error( $att_id ) ) {
                update_post_meta( $book_id, '_kv_book_docx_id', $att_id );
            }
        }
    }

    $saved=[]; $cat_names=[];
    if (!empty($cats)) foreach ($cats as $cid) { $t=get_term($cid,'kavithai_cat'); if($t&&!is_wp_error($t)) $cat_names[]=$t->name; }

    foreach ($parsed['poems'] as $poem) {
        if (empty(trim($poem['content']))) continue;
        $id = wp_insert_post(['post_title'=>sanitize_text_field($poem['title']),'post_content'=>kv_to_blocks($poem['content']),'post_excerpt'=>mb_substr($poem['content'],0,120),'post_status'=>'draft','post_type'=>'kavithai_gedicht']);
        if ($id && !is_wp_error($id)) {
            if ($year) update_post_meta($id,'_kv_year',$year);
            if ($note) update_post_meta($id,'_kv_note',$note);
            if ($poem['number']) update_post_meta($id,'_kv_book_num',$poem['number']);
            update_post_meta($id,'_kv_book_title',$book_title);
            update_post_meta($id,'_kv_imported','1');
            update_post_meta($id,'_kv_import_source',$source);
            if (!empty($cats)) wp_set_object_terms($id,$cats,'kavithai_cat');
            $saved[]=['id'=>$id,'title'=>$poem['title']];
        }
    }

    return ['bulk'=>true,'book_title'=>$book_title,'count'=>count($saved),'poems'=>$saved,'cats'=>$cat_names];
}

// ── GOOGLE DOCS FETCH ─────────────────────────────────────
function kv_fetch_gdoc($url) {
    if (!preg_match('#/document/d/([a-zA-Z0-9_-]+)#',$url,$m)) return new WP_Error('url','Google Docs இணைப்பு சரியாக இல்லை.');
    $resp=wp_remote_get("https://docs.google.com/document/d/{$m[1]}/export?format=txt",['timeout'=>20,'user-agent'=>'Mozilla/5.0 (Kavithai WP)']);
    if (is_wp_error($resp)) return new WP_Error('fetch','அணுக முடியவில்லை: '.$resp->get_error_message());
    $code=wp_remote_retrieve_response_code($resp);
    if ($code===403||$code===401) return new WP_Error('access','"யாரும் பார்க்கலாம்" என Google Docs-ல் அமைக்கவும்.');
    if ($code!==200) return new WP_Error('http',"HTTP பிழை: {$code}");
    $text=ltrim(wp_remote_retrieve_body($resp),"\xEF\xBB\xBF\xFE\xFF");
    $text=str_replace(["\r\n","\r"],"\n",$text);
    if (empty(trim($text))) return new WP_Error('empty','ஆவணம் காலியாக உள்ளது.');
    return $text;
}

// ── CONTENT FORMATTER ─────────────────────────────────────
// Single \n → <br> within stanza, double \n\n → new paragraph
function kv_to_blocks($text) {
    $stanzas=preg_split('/\n{2,}/',trim($text));
    $out=[];
    foreach ($stanzas as $s) {
        $s=trim($s); if ($s==='') continue;
        $out[]='<p>'.implode("<br>\n",array_map('esc_html',explode("\n",$s))).'</p>';
    }
    return implode("\n\n",$out);
}
