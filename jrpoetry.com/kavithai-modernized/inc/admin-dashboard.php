<?php
/**
 * Admin dashboard widgets — modernized
 * Clean, readable, focused on what a poet needs at a glance.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup', function() {

    // Remove default WP widgets
    remove_meta_box('dashboard_quick_press',   'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_primary',       'dashboard', 'side');
    remove_meta_box('dashboard_activity',      'dashboard', 'normal');
    remove_meta_box('dashboard_right_now',     'dashboard', 'normal');
    remove_meta_box('dashboard_site_health',   'dashboard', 'normal');

    wp_add_dashboard_widget('kv_welcome', 'வணக்கம்',         'kv_widget_welcome',  null, null, 'normal', 'high');
    wp_add_dashboard_widget('kv_stats',   'என் படைப்புகள்',    'kv_widget_stats',    null, null, 'normal', 'high');
    wp_add_dashboard_widget('kv_recent',  'சமீபத்திய கவிதைகள்', 'kv_widget_recent',   null, null, 'normal', 'high');
    wp_add_dashboard_widget('kv_views',   'தள அழைப்புகள்',    'kv_widget_views',    null, null, 'side', 'high');
    wp_add_dashboard_widget('kv_upload',  'கோப்பு இறக்குமதி',   'kv_widget_upload',   null, null, 'side', 'high');
    wp_add_dashboard_widget('kv_sched',   'திட்டமிட்ட கவிதைகள்', 'kv_widget_scheduled', null, null, 'side', 'core');
} );

// ─────────────────────────────────────────────  WELCOME
function kv_widget_welcome() {
    $name = kv_name();
    $hour = (int) current_time('G');
    if      ($hour < 12) $greet = 'காலை வணக்கம்';
    elseif  ($hour < 17) $greet = 'மதிய வணக்கம்';
    elseif  ($hour < 21) $greet = 'மாலை வணக்கம்';
    else                 $greet = 'இரவு வணக்கம்';

    echo '<div class="kv-greeting">';
    echo   '<div class="avatar">🌸</div>';
    echo   '<div class="text">';
    echo     '<h3>' . esc_html($name) . ' அவர்களுக்கு ' . esc_html($greet) . '</h3>';
    echo     '<p>' . current_time('d.m.Y') . ' · ' . esc_html( kv_location() ) . '</p>';
    echo   '</div>';
    echo '</div>';

    echo '<p style="font-size:15px;color:#4A3420;margin:0 0 14px;font-family:\'Noto Serif Tamil\',Georgia,serif;">இன்று என்ன எழுத விரும்புகிறீர்கள்?</p>';
    echo '<div class="kv-btn-row">';
    echo   '<a href="' . admin_url('post-new.php?post_type=kavithai_gedicht') . '" class="kv-qbtn" style="background:#8B2020;">✍ புதிய கவிதை</a>';
    echo   '<a href="' . admin_url('post-new.php?post_type=kavithai_nul') . '"     class="kv-qbtn" style="background:#2B1A0C;">📚 புதிய நூல்</a>';
    echo   '<a href="' . admin_url('admin.php?page=kavithai-import') . '"          class="kv-qbtn" style="background:#1A3A5C;">📥 Word கோப்பு</a>';
    echo   '<a href="' . admin_url('admin.php?page=kv-poem-search') . '"           class="kv-qbtn" style="background:#1A4A14;">🔍 கவிதை தேடல்</a>';
    echo '</div>';
}

// ─────────────────────────────────────────────  STATS
function kv_widget_stats() {
    $poems  = wp_count_posts('kavithai_gedicht');
    $books  = wp_count_posts('kavithai_nul');
    $awards = wp_count_posts('kavithai_virudu');
    $p_pub   = (int)($poems->publish   ?? 0);
    $p_draft = (int)($poems->draft     ?? 0);
    $p_sched = (int)($poems->future    ?? 0);
    $b_pub   = (int)($books->publish   ?? 0);
    $a_pub   = (int)($awards->publish  ?? 0);
    $cats    = wp_count_terms(['taxonomy'=>'kavithai_cat','hide_empty'=>false]);
    $cats    = is_wp_error($cats) ? 0 : (int)$cats;

    echo '<div class="kv-stat-grid">';

    echo '<div class="kv-stat">';
    echo   '<div class="icon">✍</div>';
    echo   '<div class="n">' . ($p_pub + $p_draft + $p_sched) . '</div>';
    echo   '<div class="l">கவிதைகள்<br><span style="color:#2A6B2A">' . $p_pub . ' வெளியிட்டது</span>';
    if ($p_draft) echo ' · <span style="color:#8B2020">' . $p_draft . ' வரைவு</span>';
    if ($p_sched) echo ' · <span style="color:#1A3A5C">' . $p_sched . ' திட்டமிடப்பட்டது</span>';
    echo   '</div></div>';

    echo '<div class="kv-stat">';
    echo   '<div class="icon">📚</div>';
    echo   '<div class="n">' . $b_pub . '</div>';
    echo   '<div class="l">நூல்கள்</div></div>';

    echo '<div class="kv-stat">';
    echo   '<div class="icon">🏆</div>';
    echo   '<div class="n">' . $a_pub . '</div>';
    echo   '<div class="l">விருதுகள்</div></div>';

    echo '<div class="kv-stat">';
    echo   '<div class="icon">🗂</div>';
    echo   '<div class="n">' . $cats . '</div>';
    echo   '<div class="l">வகைகள்</div></div>';

    $total = $p_pub + $p_draft + $b_pub;
    echo '<div class="kv-stat accent">';
    echo   '<div class="icon">🌟</div>';
    echo   '<div class="n">' . $total . '</div>';
    echo   '<div class="l">மொத்த படைப்புகள்</div></div>';

    echo '</div>';

    if ($p_draft > 0) {
        echo '<p class="kv-notice">';
        echo '📝 ' . $p_draft . ' வரைவுகள் வெளியிடாமல் உள்ளன. ';
        echo '<a href="' . admin_url('edit.php?post_type=kavithai_gedicht&post_status=draft') . '">பார்க்கவும் →</a>';
        echo '</p>';
    }
}

// ─────────────────────────────────────────────  RECENT POEMS
function kv_widget_recent() {
    $q = new WP_Query([
        'post_type'      => 'kavithai_gedicht',
        'posts_per_page' => 8,
        'post_status'    => ['publish','draft','future'],
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);

    if ( ! $q->have_posts() ) {
        echo '<p class="kv-empty">இன்னும் கவிதைகள் இல்லை. மேலே "புதிய கவிதை" அழுத்துங்கள்.</p>';
        return;
    }

    echo '<ul class="kv-recent-list">';
    while ( $q->have_posts() ) { $q->the_post();
        $year   = get_post_meta(get_the_ID(),'_kv_year',true);
        $status = get_post_status();
        $cats   = get_the_terms(get_the_ID(),'kavithai_cat');
        $cat    = ($cats && !is_wp_error($cats)) ? $cats[0]->name : '';

        // Thumbnail in admin list
        $thumb = get_the_post_thumbnail( null, [40,40], ['style'=>'width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0;'] );

        echo '<li>';
        if ($thumb) echo '<span class="thumb">' . $thumb . '</span>';
        echo   '<a class="title" href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a>';
        echo   '<span class="meta">';
        if ($cat) echo '<span style="color:#7D6448;">' . esc_html($cat) . '</span> · ';
        echo     esc_html($year ?: get_the_modified_date('d.m.Y'));
        if ($status==='draft')  echo '<span class="kv-badge draft">வரைவு</span>';
        elseif ($status==='future') echo '<span class="kv-badge sched">திட்டம்</span>';
        else                    echo '<span class="kv-badge pub">✓</span>';
        echo   '</span>';
        echo   '<span class="actions">';
        echo     '<a href="' . esc_url(get_edit_post_link()) . '">திருத்து</a>';
        if ($status==='publish') echo '<a href="' . esc_url(get_permalink()) . '" target="_blank">காண்க</a>';
        echo   '</span>';
        echo '</li>';
    }
    echo '</ul>';
    wp_reset_postdata();

    echo '<p class="kv-link-row">';
    echo   '<a href="' . admin_url('edit.php?post_type=kavithai_gedicht') . '">எல்லா கவிதைகளும் →</a>';
    echo '</p>';
}

// ─────────────────────────────────────────────  VIEWS
function kv_widget_views() {
    $top_posts = new WP_Query([
        'post_type'      => 'kavithai_gedicht',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'meta_key'       => '_kv_views',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);
    $total_views = (int) get_option('kv_total_views', 0);

    echo '<div class="kv-stat-grid" style="grid-template-columns:1fr 1fr;margin-bottom:14px;">';
    echo   '<div class="kv-stat"><div class="icon">👁</div><div class="n">' . number_format($total_views) . '</div><div class="l">மொத்த அழைப்புகள்</div></div>';
    echo   '<div class="kv-stat"><div class="icon">✍</div><div class="n">' . (int)wp_count_posts('kavithai_gedicht')->publish . '</div><div class="l">வெளியிட்ட கவிதைகள்</div></div>';
    echo '</div>';

    if ($top_posts->have_posts()) {
        echo '<p class="kv-sub-label">அதிகம் படிக்கப்பட்டவை:</p>';
        echo '<ul class="kv-views-list">';
        while ($top_posts->have_posts()) { $top_posts->the_post();
            $views = (int) get_post_meta(get_the_ID(),'_kv_views',true);
            echo '<li>';
            echo   '<a class="page-name" href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a>';
            echo   '<span class="count">' . number_format($views) . '</span>';
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata();
    } else {
        echo '<p class="kv-empty">தளம் வெளியான பிறகு அழைப்புகள் இங்கே தோன்றும்.</p>';
    }
}

// ─────────────────────────────────────────────  UPLOAD
function kv_widget_upload() {
    echo '<a href="' . admin_url('admin.php?page=kavithai-import') . '" class="kv-upload-drop">';
    echo   '<div class="icon">📄</div>';
    echo   '<p>Word கோப்பு இறக்குமதி</p>';
    echo   '<small>.docx — ஒரு கவிதை அல்லது முழு நூல்</small>';
    echo '</a>';

    echo '<a href="' . admin_url('media-new.php') . '" class="kv-upload-drop" style="margin-top:10px;">';
    echo   '<div class="icon">🖼</div>';
    echo   '<p>புகைப்படம் சேர்க்க</p>';
    echo   '<small>JPG, PNG · drag & drop</small>';
    echo '</a>';
}

// ─────────────────────────────────────────────  SCHEDULED POEMS (new!)
function kv_widget_scheduled() {
    $q = new WP_Query([
        'post_type'      => 'kavithai_gedicht',
        'post_status'    => 'future',
        'posts_per_page' => 5,
        'orderby'        => 'date', 'order' => 'ASC',
        'no_found_rows'  => true,
    ]);

    if ( ! $q->have_posts() ) {
        echo '<p class="kv-empty">திட்டமிட்ட கவிதைகள் இல்லை.</p>';
        echo '<p style="font-size:13px;color:#7D6448;margin:10px 0 0;font-family:\'Noto Serif Tamil\',serif;">';
        echo 'ஒரு கவிதையை எதிர்காலத்தில் தானாக வெளியிட விரும்புகிறீர்களா? கவிதை திருத்தும்போது "Publish on:" தேதியை மாற்றுங்கள்.';
        echo '</p>';
        return;
    }

    echo '<ul class="kv-recent-list">';
    while ($q->have_posts()) { $q->the_post();
        $date = get_the_date('d.m.Y · H:i');
        echo '<li>';
        echo   '<a class="title" href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a>';
        echo   '<span class="meta" style="color:#1A3A5C;">' . esc_html($date) . '</span>';
        echo '</li>';
    }
    echo '</ul>';
    wp_reset_postdata();
}
