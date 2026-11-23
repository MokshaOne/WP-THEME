<?php
/**
 * 'என் தளம்' settings page — single screen for everything she needs
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function() {
    add_menu_page(
        'என் தளம்',
        '🌸 என் தளம்',
        'manage_options',
        'kavithai-settings',
        'kv_settings_page',
        'dashicons-admin-customizer',
        3
    );
}, 5 );

function kv_settings_page() {
    if ( isset( $_POST['kv_save'] ) && check_admin_referer( 'kv_settings_nonce' ) && current_user_can( 'manage_options' ) ) {
        $text_fields = [
            'kv_name','kv_subname','kv_location','kv_tagline','kv_email',
            'kv_bio_text','kv_diaspora_quote','kv_hero_verse',
        ];
        foreach ( $text_fields as $f ) {
            set_theme_mod( $f, sanitize_textarea_field( $_POST[$f] ?? '' ) );
        }
        if ( isset($_POST['kv_fontsize']) ) set_theme_mod( 'kv_fontsize', absint($_POST['kv_fontsize']) );
        foreach ( ['kv_primary','kv_bg'] as $c ) {
            if ( isset($_POST[$c]) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST[$c]) ) {
                set_theme_mod( $c, $_POST[$c] );
            }
        }
        if ( isset($_POST['kv_portrait_id']) ) set_theme_mod('kv_portrait_id', absint($_POST['kv_portrait_id']));
        if ( isset($_POST['kv_portrait_focal_x']) ) set_theme_mod('kv_portrait_focal_x', max(0,min(100,(int)$_POST['kv_portrait_focal_x'])));
        if ( isset($_POST['kv_portrait_focal_y']) ) set_theme_mod('kv_portrait_focal_y', max(0,min(100,(int)$_POST['kv_portrait_focal_y'])));

        $section_keys = ['kv_show_hero','kv_show_daily','kv_show_about','kv_show_timeline','kv_show_recent','kv_show_contact'];
        foreach ($section_keys as $sk) {
            update_option($sk, isset($_POST[$sk]) ? '1' : '0');
        }

        echo '<div class="kv-save-notice">✓ சேமிக்கப்பட்டது!</div>';
    }

    $g = fn($k,$d='') => get_theme_mod($k,$d);
    $portrait_id  = (int) get_theme_mod('kv_portrait_id', 0);
    $portrait_url = $portrait_id ? wp_get_attachment_image_url($portrait_id, 'poet-portrait') : '';
    $fx = (int) get_theme_mod('kv_portrait_focal_x', 50);
    $fy = (int) get_theme_mod('kv_portrait_focal_y', 35);

    // Need media uploader scripts
    wp_enqueue_media();
    ?>
    <style>
    .kv-settings { max-width:820px; margin:30px auto; font-family:'Noto Serif Tamil',Georgia,serif; }
    .kv-settings h1 { font-size:28px; font-weight:400; color:#1A0E06; margin-bottom:8px; }
    .kv-settings .kv-intro { color:#7D6448; font-size:15px; margin-bottom:32px; line-height:1.8; }
    .kv-card { background:#fff; border:1px solid rgba(125,100,72,.18); padding:28px 32px; margin-bottom:20px; border-radius:6px; }
    .kv-card h2 { font-size:20px; font-weight:400; color:#1A0E06; margin:0 0 22px; padding-bottom:14px; border-bottom:1px solid rgba(125,100,72,.12); display:flex; align-items:center; gap:10px; }
    .kv-label { display:block; font-size:14px; color:#5C4530; margin:0 0 6px; }
    .kv-hint  { display:inline-block; font-size:12px; color:#9C8466; margin-left:6px; font-style:italic; }
    .kv-input, .kv-textarea {
        width:100%; padding:12px 14px; font-size:16px; font-family:'Noto Serif Tamil',Georgia,serif;
        border:1px solid rgba(125,100,72,.3); background:#FBF6E9; color:#1A0E06;
        border-radius:4px; box-sizing:border-box; margin-bottom:18px; transition:border-color .2s;
    }
    .kv-input:focus, .kv-textarea:focus { outline:none; border-color:#8B2020; }
    .kv-textarea { min-height:110px; resize:vertical; line-height:1.9; }
    .kv-textarea-tall { min-height:160px; }
    .kv-row2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .kv-color-row { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
    .kv-color-input { width:54px; height:42px; border:1px solid rgba(125,100,72,.3); padding:2px; cursor:pointer; border-radius:4px; background:#fff; }
    .kv-color-label { font-size:15px; color:#3B2A1A; }
    .kv-save { width:100%; background:#8B2020; color:#fff; border:none; padding:18px; font-size:18px; font-family:inherit; cursor:pointer; border-radius:8px; margin-top:8px; transition:opacity .2s; }
    .kv-save:hover { opacity:.88; }
    .kv-fs-row { display:flex; align-items:center; gap:16px; margin-bottom:18px; }
    .kv-fs-row input[type=range] { flex:1; accent-color:#8B2020; }
    .kv-fs-val { font-size:18px; font-weight:500; color:#8B2020; min-width:48px; }
    .kv-save-notice { background:#F0FEF4; border-left:4px solid #1A6B3A; padding:14px 22px; margin:16px 0 24px; font-size:16px; color:#1A4A1A; border-radius:0 6px 6px 0; max-width:820px; }

    /* Portrait picker */
    .kv-portrait-wrap { display:grid; grid-template-columns:280px 1fr; gap:24px; align-items:start; }
    .kv-portrait-preview { aspect-ratio:4/5; background:linear-gradient(150deg,#4A2E18 0%,#2A1B0C 60%,#150B05 100%); position:relative; overflow:hidden; border-radius:6px; cursor:crosshair; box-shadow:0 12px 30px -12px rgba(0,0,0,0.3); }
    .kv-portrait-preview .empty-msg { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; text-align:center; color:rgba(246,239,221,0.5); font-style:italic; font-size:14px; padding:0 16px; }
    .kv-portrait-preview img { width:100%; height:100%; object-fit:cover; display:block; }
    .kv-portrait-dot { position:absolute; width:28px; height:28px; border-radius:50%; background:#fff; border:3px solid #8B2020; box-shadow:0 0 0 1px rgba(0,0,0,0.2), 0 6px 14px rgba(0,0,0,0.3); transform:translate(-50%,-50%); pointer-events:none; display:none; }
    .kv-portrait-actions { margin-top:14px; display:flex; gap:8px; }
    .kv-portrait-btn { padding:10px 18px; border:1px solid rgba(125,100,72,.3); background:#FBF6E9; color:#3B2A1A; font-family:inherit; font-size:14px; border-radius:999px; cursor:pointer; }
    .kv-portrait-btn:hover { background:#8B2020; color:#fff; border-color:#8B2020; }
    .kv-portrait-btn.remove { color:#8B2020; }
    .kv-portrait-help h3 { font-size:16px; font-weight:500; margin-bottom:10px; color:#1A0E06; }
    .kv-portrait-help p { font-size:14px; color:#5C4530; line-height:1.8; margin-bottom:12px; }
    .kv-portrait-help ul { list-style:none; padding:0; }
    .kv-portrait-help li { font-size:14px; color:#3B2A1A; padding:6px 0 6px 22px; position:relative; }
    .kv-portrait-help li::before { content:'✓'; position:absolute; left:0; color:#1A6B3A; font-weight:600; }

    @media(max-width:700px){
        .kv-row2{grid-template-columns:1fr;}
        .kv-card{padding:22px;}
        .kv-portrait-wrap{grid-template-columns:1fr;}
    }
    </style>

    <div class="kv-settings">
        <h1>🌸 என் தளம்</h1>
        <p class="kv-intro">
            இங்கே எல்லா தகவல்களையும் ஒரே இடத்தில் மாற்றலாம்.<br>
            கீழே "சேமி" அழுத்தியதும் தளத்தில் தோன்றும்.
        </p>

        <form method="POST">
            <?php wp_nonce_field('kv_settings_nonce'); ?>

            <!-- ─ PORTRAIT (NEW) ─ -->
            <div class="kv-card">
                <h2>📷 உங்கள் புகைப்படம்</h2>
                <div class="kv-portrait-wrap">
                    <div>
                        <div class="kv-portrait-preview" id="kv-portrait-preview">
                            <?php if ($portrait_url): ?>
                                <img src="<?php echo esc_url($portrait_url); ?>" style="object-position:<?php echo $fx; ?>% <?php echo $fy; ?>%;">
                            <?php else: ?>
                                <span class="empty-msg">இன்னும் புகைப்படம் சேர்க்கப்படவில்லை</span>
                            <?php endif; ?>
                            <span class="kv-portrait-dot" id="kv-portrait-dot" style="<?php echo $portrait_url ? "display:block;left:{$fx}%;top:{$fy}%;" : ''; ?>"></span>
                        </div>
                        <input type="hidden" name="kv_portrait_id" id="kv_portrait_id" value="<?php echo $portrait_id; ?>">
                        <input type="hidden" name="kv_portrait_focal_x" id="kv_portrait_focal_x" value="<?php echo $fx; ?>">
                        <input type="hidden" name="kv_portrait_focal_y" id="kv_portrait_focal_y" value="<?php echo $fy; ?>">
                        <div class="kv-portrait-actions">
                            <button type="button" class="kv-portrait-btn" id="kv-portrait-pick">📷 படத்தை தேர்வு செய்</button>
                            <?php if ($portrait_url): ?>
                            <button type="button" class="kv-portrait-btn remove" id="kv-portrait-remove">✕ அகற்று</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="kv-portrait-help">
                        <h3>இந்த புகைப்படம் எங்கே காட்டப்படும்?</h3>
                        <p>முகப்புப் பக்கத்தில் (Hero பகுதி), வலதுபுறம்.</p>
                        <ul>
                            <li>4:5 விகிதம் சிறந்தது (உதா: 720 × 900px)</li>
                            <li>படத்தில் <strong>கிளிக் செய்து முகம் எங்கே</strong> என்பதை குறிக்கவும்</li>
                            <li>அந்த இடம் எப்போதும் தெரியுமாறு படம் வெட்டப்படும்</li>
                            <li>எந்த அளவிலான திரையிலும் சரியாக காட்டப்படும்</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ─ POET INFO ─ -->
            <div class="kv-card">
                <h2>👤 உங்களை பற்றி</h2>

                <label class="kv-label">உங்கள் பெயர்</label>
                <input class="kv-input" type="text" name="kv_name" value="<?php echo esc_attr($g('kv_name','கமலா செல்வராசா')); ?>">

                <label class="kv-label">துணை பெயர் / தலைப்பு <span class="kv-hint">(விருப்பம்)</span></label>
                <input class="kv-input" type="text" name="kv_subname" value="<?php echo esc_attr($g('kv_subname')); ?>" placeholder="உதா: கவிஞர் · சிறுகதை ஆசிரியர்">

                <div class="kv-row2">
                    <div>
                        <label class="kv-label">இருப்பிடம்</label>
                        <input class="kv-input" type="text" name="kv_location" value="<?php echo esc_attr($g('kv_location','யாழ்ப்பாணம்')); ?>">
                    </div>
                    <div>
                        <label class="kv-label">மின்னஞ்சல்</label>
                        <input class="kv-input" type="email" name="kv_email" value="<?php echo esc_attr($g('kv_email')); ?>">
                    </div>
                </div>

                <label class="kv-label">மேற்கோள் <span class="kv-hint">(footer-இல் காட்டப்படும்)</span></label>
                <input class="kv-input" type="text" name="kv_tagline" value="<?php echo esc_attr($g('kv_tagline','வார்த்தைகளில் வாழுங்கள்')); ?>">
            </div>

            <!-- ─ TEXTS ─ -->
            <div class="kv-card">
                <h2>✍ தளத்தில் காட்டப்படும் வாக்கியங்கள்</h2>

                <label class="kv-label">முகப்பில் கவிதை வரிகள் <span class="kv-hint">(Hero பகுதியில்)</span></label>
                <textarea class="kv-textarea kv-textarea-tall" name="kv_hero_verse" placeholder="நான் விட்டுவந்த மண் —&#10;அதன் நிறம் இன்னும் என் கைகளில்."><?php echo esc_textarea($g('kv_hero_verse')); ?></textarea>

                <label class="kv-label">என்னை பற்றி <span class="kv-hint">(About பக்கத்தில்)</span></label>
                <textarea class="kv-textarea kv-textarea-tall" name="kv_bio_text" placeholder="உங்கள் வாழ்க்கை கதையை இங்கே எழுதுங்கள்..."><?php echo esc_textarea($g('kv_bio_text')); ?></textarea>

                <label class="kv-label">சிறப்பு மேற்கோள் <span class="kv-hint">(முகப்பில் pull quote)</span></label>
                <textarea class="kv-textarea" name="kv_diaspora_quote" placeholder="இரண்டு மண்கள் என்னை வளர்த்தன —&#10;ஒன்று என்னைப் பெற்றது,&#10;மற்றொன்று என்னை வளர்த்தது."><?php echo esc_textarea($g('kv_diaspora_quote')); ?></textarea>
            </div>

            <!-- ─ DESIGN ─ -->
            <div class="kv-card">
                <h2>🎨 தோற்றம்</h2>

                <label class="kv-label">எழுத்து அளவு</label>
                <div class="kv-fs-row">
                    <span style="font-size:13px;color:#aaa;">சிறியது</span>
                    <input type="range" name="kv_fontsize" min="16" max="24" step="1"
                           value="<?php echo esc_attr($g('kv_fontsize',19)); ?>"
                           oninput="document.getElementById('kv_fs_val').textContent=this.value+'px'">
                    <span style="font-size:13px;color:#aaa;">பெரியது</span>
                    <span class="kv-fs-val" id="kv_fs_val"><?php echo esc_html($g('kv_fontsize',19)); ?>px</span>
                </div>

                <label class="kv-label" style="margin-bottom:10px;">வண்ணங்கள்</label>
                <div class="kv-color-row">
                    <input type="color" class="kv-color-input" name="kv_primary" value="<?php echo esc_attr($g('kv_primary','#6B1414')); ?>">
                    <span class="kv-color-label">முக்கிய நிறம் (சிவப்பு, தலைப்புகள், இணைப்புகள்)</span>
                </div>
                <div class="kv-color-row">
                    <input type="color" class="kv-color-input" name="kv_bg" value="<?php echo esc_attr($g('kv_bg','#F0E7CC')); ?>">
                    <span class="kv-color-label">பின்னணி நிறம் (க்ரீம்)</span>
                </div>
            </div>

            <!-- ─ SECTIONS ─ -->
            <div class="kv-card">
                <h2>📐 பக்க பிரிவுகள் <span style="font-size:14px;color:#9C8466;font-weight:400;">(என்னென்ன காட்ட வேண்டும்?)</span></h2>
                <?php
                $sections = [
                    'kv_show_hero'     => ['label' => 'முகப்பு பகுதி (Hero)',        'default' => '1'],
                    'kv_show_daily'    => ['label' => 'இன்றைய கவிதை',                'default' => '1'],
                    'kv_show_about'    => ['label' => 'என்னை பற்றி பகுதி',           'default' => '1'],
                    'kv_show_timeline' => ['label' => 'வாழ்க்கை காலவரிசை',          'default' => '1'],
                    'kv_show_recent'   => ['label' => 'சமீபத்திய படைப்புகள்',       'default' => '1'],
                    'kv_show_contact'  => ['label' => 'தொடர்பு பகுதி',               'default' => '1'],
                ];
                foreach ($sections as $key => $s):
                    $checked = get_option($key, $s['default']) !== '0';
                ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(125,100,72,.1);">
                    <span style="font-size:15px;color:#3B2A1A;"><?php echo esc_html($s['label']); ?></span>
                    <label class="kv-toggle-switch" style="position:relative;display:inline-block;width:48px;height:26px;">
                        <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php echo $checked ? 'checked' : ''; ?> style="opacity:0;width:0;height:0;position:absolute;">
                        <span style="position:absolute;inset:0;background:<?php echo $checked ? '#8B2020' : '#ccc'; ?>;border-radius:26px;cursor:pointer;transition:.3s;" class="kv-tslider"></span>
                        <span style="position:absolute;width:20px;height:20px;background:#fff;border-radius:50%;top:3px;left:<?php echo $checked ? '25px' : '3px'; ?>;transition:.3s;pointer-events:none;" class="kv-tball"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="kv_save" class="kv-save">💾 சேமி</button>
        </form>
    </div>

    <script>
    (function(){
        var pickBtn   = document.getElementById('kv-portrait-pick');
        var removeBtn = document.getElementById('kv-portrait-remove');
        var preview   = document.getElementById('kv-portrait-preview');
        var dot       = document.getElementById('kv-portrait-dot');
        var hiddenId  = document.getElementById('kv_portrait_id');
        var fxField   = document.getElementById('kv_portrait_focal_x');
        var fyField   = document.getElementById('kv_portrait_focal_y');

        var frame;
        pickBtn.addEventListener('click', function() {
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'உங்கள் புகைப்படத்தை தேர்வு செய்யவும்',
                button: { text: 'இதை பயன்படுத்து' },
                library: { type: 'image' },
                multiple: false
            });
            frame.on('select', function() {
                var attach = frame.state().get('selection').first().toJSON();
                hiddenId.value = attach.id;
                preview.innerHTML = '<img src="' + attach.url + '" style="object-position:' + fxField.value + '% ' + fyField.value + '%;">'
                    + '<span class="kv-portrait-dot" id="kv-portrait-dot" style="display:block;left:' + fxField.value + '%;top:' + fyField.value + '%;"></span>';
                bindCanvas();
            });
            frame.open();
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                hiddenId.value = 0;
                preview.innerHTML = '<span class="empty-msg">இன்னும் புகைப்படம் சேர்க்கப்படவில்லை</span>';
                removeBtn.remove();
            });
        }

        function bindCanvas() {
            var canvas = document.getElementById('kv-portrait-preview');
            var dotEl  = document.getElementById('kv-portrait-dot');
            canvas.addEventListener('click', function(e) {
                var img = canvas.querySelector('img');
                if (!img) return;
                var rect = canvas.getBoundingClientRect();
                var x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
                var y = Math.round(((e.clientY - rect.top)  / rect.height) * 100);
                x = Math.max(0,Math.min(100,x));
                y = Math.max(0,Math.min(100,y));
                fxField.value = x;
                fyField.value = y;
                img.style.objectPosition = x + '% ' + y + '%';
                if (dotEl) { dotEl.style.left = x + '%'; dotEl.style.top = y + '%'; dotEl.style.display = 'block'; }
            });
        }
        bindCanvas();
    })();

    document.querySelectorAll('.kv-tslider').forEach(function(slider) {
        var inp = slider.previousElementSibling;
        var ball = slider.nextElementSibling;
        inp.addEventListener('change', function() {
            slider.style.background = this.checked ? '#8B2020' : '#ccc';
            ball.style.left = this.checked ? '25px' : '3px';
        });
    });
    </script>
    <?php
}
