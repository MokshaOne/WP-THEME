<?php
/**
 * PDF book export — generates a printable HTML the browser
 * can save as PDF via the print dialog.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function() {
    add_submenu_page('kavithai-settings','PDF ஏற்றுமதி','📄 PDF ஏற்றுமதி','edit_posts','kv-pdf-export','kv_pdf_export_page');
}, 30);

function kv_pdf_export_page() {
    if ( isset( $_POST['kv_do_export'] ) && check_admin_referer( 'kv_pdf_export' ) && current_user_can( 'edit_posts' ) ) {
        $cat_id = absint( $_POST['kv_export_cat'] ?? 0 );
        kv_generate_pdf_export( $cat_id );
        exit;
    }

    $cats  = get_terms(['taxonomy'=>'kavithai_cat','hide_empty'=>true]);
    $total = (int) wp_count_posts('kavithai_gedicht')->publish;
    ?>
    <style>
    .kv-pdf-wrap { max-width:700px; margin:30px auto; font-family:'Noto Serif Tamil',Georgia,serif; }
    .kv-pdf-wrap h1 { font-size:26px; font-weight:400; color:#1A0E06; margin-bottom:6px; }
    .kv-pdf-wrap .intro { color:#7D6448; font-size:15px; margin-bottom:28px; line-height:1.8; }
    .kv-pdf-card { background:#fff; border:1px solid rgba(125,100,72,.18); padding:30px; border-radius:6px; }
    .kv-pdf-card label { display:block; font-size:14px; color:#5C4530; margin-bottom:8px; }
    .kv-pdf-card select { width:100%; padding:12px 14px; font-size:16px; font-family:inherit; border:1px solid rgba(125,100,72,.3); background:#FBF6E9; border-radius:4px; margin-bottom:22px; }
    .kv-pdf-card button { width:100%; background:#8B2020; color:#fff; border:none; padding:16px; font-size:17px; font-family:inherit; cursor:pointer; border-radius:8px; transition:opacity .2s; }
    .kv-pdf-card button:hover { opacity:.88; }
    .kv-pdf-tip { font-size:13px; color:#7D6448; margin-top:18px; padding:12px 16px; background:#FBF6E9; border-left:3px solid #C9952E; border-radius:0 4px 4px 0; line-height:1.7; }
    </style>
    <div class="kv-pdf-wrap">
        <h1>📄 PDF ஏற்றுமதி</h1>
        <p class="intro">எல்லா கவிதைகளையும், அல்லது ஒரு வகையை மட்டும், PDF ஆக ஏற்றுமதி செய்யுங்கள்.</p>
        <div class="kv-pdf-card">
            <form method="POST">
                <?php wp_nonce_field('kv_pdf_export'); ?>
                <label>எந்த கவிதைகள் ஏற்றுமதி செய்ய வேண்டும்?</label>
                <select name="kv_export_cat">
                    <option value="0">எல்லாமும் (<?php echo $total; ?> கவிதைகள்)</option>
                    <?php foreach ($cats as $c): ?>
                    <option value="<?php echo $c->term_id; ?>"><?php echo esc_html($c->name); ?> (<?php echo $c->count; ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="kv_do_export">📥 PDF உருவாக்கு</button>
            </form>
            <p class="kv-pdf-tip">💡 அடுத்த பக்கத்தில் ஒரு பெரிய "🖨 அச்சிடு" பொத்தான் தோன்றும். அதை அழுத்தினால் browser print dialog திறக்கும். அங்கே "PDF" தேர்வு செய்து சேமிக்கவும்.</p>
        </div>
    </div>
    <?php
}

/**
 * CP4: Per-book PDF download.
 *
 * Hook on /?kv_book_pdf=N to render a printable HTML page of every
 * poem in that book. Reader hits "Save as PDF" in the browser print
 * dialog (same UX as the global PDF export). Also serves as the
 * "View as PDF" target.
 */
add_action( 'template_redirect', function() {
    if ( ! isset( $_GET['kv_book_pdf'] ) ) return;
    $book_id = absint( $_GET['kv_book_pdf'] );
    if ( ! $book_id ) return;
    $book = get_post( $book_id );
    if ( ! $book || $book->post_type !== 'kavithai_nul' ) return;
    if ( $book->post_status !== 'publish' && ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'அனுமதி இல்லை.' );
    }
    kv_generate_book_pdf_view( $book_id );
    exit;
} );

function kv_generate_book_pdf_view( $book_id ) {
    $book_title = get_the_title( $book_id );
    $book_year  = get_post_meta( $book_id, '_kv_year', true );
    $book_type  = get_post_meta( $book_id, '_kv_type', true );
    $type_labels = [
        'kavithai' => 'கவிதை தொகுப்பு',
        'kathai'   => 'சிறுகதைகள்',
        'novel'    => 'நாவல்',
        'katture'  => 'கட்டுரைகள்',
        'other'    => 'மற்றவை',
    ];
    $type_label = $type_labels[ $book_type ] ?? 'நூல்';

    $poems = new WP_Query( [
        'post_type'      => 'kavithai_gedicht',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [ [ 'key' => '_kv_book_title', 'value' => $book_title ] ],
        'meta_key'       => '_kv_book_num',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
        'no_found_rows'  => true,
    ] );

    $poet     = kv_name();
    $location = kv_location();
    $filename = sanitize_title( $poet . '-' . $book_title ) . '-' . ( $book_year ?: date( 'Y' ) ) . '.html';

    header( 'Content-Type: text/html; charset=utf-8' );
    header( 'Content-Disposition: inline; filename="' . $filename . '"' );

    echo '<!DOCTYPE html><html lang="ta"><head><meta charset="UTF-8">';
    echo '<title>' . esc_html( $book_title . ' — ' . $poet ) . '</title>';
    echo '<link rel="stylesheet" href="' . esc_url( get_template_directory_uri() . '/assets/fonts.css' ) . '">';
    echo '<style>
        @page { margin: 2.5cm; size: A4; }
        @media print {
            .no-print { display: none; }
            .poem-page { page-break-after: always; }
            .poem-page:last-child { page-break-after: avoid; }
            body { background: #fff !important; }
        }
        * { box-sizing: border-box; }
        body { font-family: "Noto Serif Tamil", Georgia, serif; color: #1A0E04; background: #F0E7CC; font-size: 13pt; line-height: 1.9; margin: 0; padding: 0; }
        .container { max-width: 21cm; margin: 0 auto; padding: 2cm; background: #fff; min-height: 100vh; }
        @media print { .container { padding: 0; box-shadow: none; max-width: 100%; } }
        .cover { text-align: center; padding: 6cm 2cm 4cm; border-bottom: 2pt solid #6B1414; margin-bottom: 3cm; }
        .cover h1 { font-size: 36pt; font-weight: 300; margin-bottom: 0.6cm; line-height: 1.1; color: #1A0E04; letter-spacing: -0.01em; }
        .cover .subtitle { font-size: 14pt; color: #5C4530; margin-bottom: 2.5cm; font-style: italic; }
        .cover .author { font-size: 20pt; font-weight: 400; color: #6B1414; }
        .cover .meta { font-size: 11pt; color: #8B5A0F; margin-top: 0.6cm; letter-spacing: 0.2em; text-transform: uppercase; }
        .toc { margin-bottom: 2.5cm; page-break-after: always; }
        .toc h2 { font-size: 18pt; font-weight: 400; border-bottom: 1pt solid #5C4530; padding-bottom: 0.4cm; margin-bottom: 1cm; }
        .toc-item { display: flex; justify-content: space-between; padding: 0.25cm 0; font-size: 12pt; border-bottom: 1px dotted #B58B47; }
        .poem-page { padding-top: 1.5cm; }
        .poem-num { font-size: 10pt; color: #8B5A0F; letter-spacing: 0.05em; margin-bottom: 0.4cm; }
        .poem-title { font-size: 24pt; font-weight: 400; margin-bottom: 0.4cm; border-left: 3pt solid #6B1414; padding-left: 0.6cm; line-height: 1.2; }
        .poem-meta { font-size: 11pt; color: #5C4530; margin-bottom: 1cm; }
        .poem-meta span + span::before { content: " · "; color: #B58B47; }
        .poem-dedication { border-left: 2pt solid #8B5A0F; padding: 6pt 14pt; margin-bottom: 24pt; color: #8B5A0F; font-style: italic; font-size: 12pt; }
        .poem-body { font-size: 14pt; line-height: 2.4; color: #3A2A18; white-space: pre-line; }
        .poem-body p { margin-bottom: 1cm; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #6B1414; color: #fff; border: none; padding: 14px 28px; font-size: 13pt; font-family: inherit; cursor: pointer; border-radius: 0; z-index: 999; box-shadow: 0 8px 24px rgba(0,0,0,0.25); letter-spacing: 0.2em; text-transform: uppercase; }
        .print-btn:hover { opacity: 0.9; }
    </style></head><body>';

    echo '<button class="no-print print-btn" onclick="window.print()">🖨 அச்சிடு / PDF சேமி</button>';
    echo '<div class="container">';

    // Cover
    echo '<div class="cover">';
    echo '<h1>' . esc_html( $book_title ) . '</h1>';
    echo '<div class="subtitle">' . esc_html( $type_label ) . '</div>';
    echo '<div class="author">' . esc_html( $poet ) . '</div>';
    echo '<div class="meta">' . esc_html( $location );
    if ( $book_year ) echo ' · ' . esc_html( $book_year );
    if ( $poems->found_posts > 0 ) echo ' · ' . (int) $poems->found_posts . ' கவிதைகள்';
    echo '</div>';
    echo '</div>';

    // TOC
    if ( $poems->have_posts() ) {
        echo '<div class="toc"><h2>உள்ளடக்கம்</h2>';
        $i = 1;
        while ( $poems->have_posts() ) { $poems->the_post();
            echo '<div class="toc-item"><span>' . esc_html( get_the_title() ) . '</span><span>' . $i . '</span></div>';
            $i++;
        }
        echo '</div>';
        wp_reset_postdata();

        // Poems
        $q2 = new WP_Query( [
            'post_type'      => 'kavithai_gedicht',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => [ [ 'key' => '_kv_book_title', 'value' => $book_title ] ],
            'meta_key'       => '_kv_book_num',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ] );
        $i = 1;
        $total = $q2->found_posts;
        while ( $q2->have_posts() ) { $q2->the_post();
            $year = get_post_meta( get_the_ID(), '_kv_year', true );
            $loc  = get_post_meta( get_the_ID(), '_kv_origin_loc', true );
            $ded  = get_post_meta( get_the_ID(), '_kv_dedication', true );

            echo '<div class="poem-page">';
            echo '<div class="poem-num">' . $i . ' / ' . $total . '</div>';
            echo '<h2 class="poem-title">' . esc_html( get_the_title() ) . '</h2>';
            $meta_parts = [];
            if ( $year ) $meta_parts[] = '<span>' . esc_html( $year ) . '</span>';
            if ( $loc )  $meta_parts[] = '<span>' . esc_html( $loc ) . '</span>';
            if ( $meta_parts ) echo '<div class="poem-meta">' . implode( '', $meta_parts ) . '</div>';
            if ( $ded ) echo '<div class="poem-dedication">' . esc_html( $ded ) . '</div>';
            echo '<div class="poem-body">' . wp_kses_post( wpautop( get_the_content() ) ) . '</div>';
            echo '</div>';
            $i++;
        }
        wp_reset_postdata();
    }

    echo '</div></body></html>';
}

function kv_generate_pdf_export( $cat_id = 0 ) {
    if ( ! current_user_can( 'edit_posts' ) ) { wp_die( 'அனுமதி இல்லை.' ); }
    $args = [
        'post_type'      => 'kavithai_gedicht',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'meta_value_num',
        'meta_key'       => '_kv_year',
        'order'          => 'ASC',
    ];
    if ($cat_id) $args['tax_query'] = [['taxonomy'=>'kavithai_cat','field'=>'term_id','terms'=>$cat_id]];
    $q = new WP_Query($args);

    $poet     = kv_name();
    $location = kv_location();
    $cat_name = $cat_id ? get_term($cat_id,'kavithai_cat')->name : 'அனைத்து கவிதைகள்';
    $filename = sanitize_title($poet . '-' . $cat_name) . '-' . date('Y') . '.html';

    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');

    echo '<!DOCTYPE html><html lang="ta"><head><meta charset="UTF-8">';
    echo '<title>' . esc_html($poet . ' — ' . $cat_name) . '</title>';
    echo '<link rel="stylesheet" href="' . esc_url( get_template_directory_uri() . '/assets/fonts.css' ) . '">';
    echo '<style>
        @page { margin: 2.5cm; size: A4; }
        @media print { .no-print { display:none; } .poem-page { page-break-after: always; } .poem-page:last-child { page-break-after: avoid; } body { background:#fff !important; } }
        * { box-sizing: border-box; }
        body { font-family:"Noto Serif Tamil",Georgia,serif; color:#1A120A; background:#F6EFDD; font-size:13pt; line-height:1.9; margin:0; padding:0; }
        .container { max-width:21cm; margin:0 auto; padding:2cm; background:#fff; min-height:100vh; }
        @media print { .container { padding:0; box-shadow:none; max-width:100%; } }
        .cover { text-align:center; padding:6cm 2cm 4cm; border-bottom:2pt solid #8B2020; margin-bottom:3cm; }
        .cover h1 { font-size:36pt; font-weight:300; margin-bottom:0.6cm; line-height:1.1; color:#1A120A; }
        .cover .subtitle { font-size:16pt; color:#6E5638; margin-bottom:2.5cm; font-style:italic; }
        .cover .author { font-size:20pt; font-weight:400; color:#8B2020; }
        .cover .meta { font-size:12pt; color:#A6741E; margin-top:0.6cm; }
        .toc { margin-bottom:2.5cm; page-break-after: always; }
        .toc h2 { font-size:18pt; font-weight:400; border-bottom:1pt solid #D4C4A8; padding-bottom:0.4cm; margin-bottom:1cm; }
        .toc-item { display:flex; justify-content:space-between; padding:0.25cm 0; font-size:12pt; border-bottom:1px dotted #ddd; }
        .poem-page { padding-top:1.5cm; }
        .poem-num { font-size:10pt; color:#A6741E; letter-spacing:.05em; margin-bottom:0.4cm; }
        .poem-title { font-size:24pt; font-weight:400; margin-bottom:0.4cm; border-left:3pt solid #8B2020; padding-left:0.6cm; line-height:1.2; }
        .poem-meta { font-size:11pt; color:#6E5638; margin-bottom:1cm; }
        .poem-meta span+span::before { content: " · "; color:#bbb; }
        .poem-dedication { border-left:2pt solid #A6741E; padding:6pt 14pt; margin-bottom:24pt; color:#A6741E; font-style:italic; font-size:12pt; }
        .poem-body { font-size:14pt; line-height:2.4; color:#3B2A1A; white-space:pre-line; }
        .poem-body p { margin-bottom:1cm; }
        .poem-backstory { font-size:11pt; color:#6E5638; margin-top:1cm; padding:0.6cm 0.8cm; border-left:2pt solid #C9952E; background:#FBF6E9; font-style:italic; line-height:1.9; }
        .print-btn { position:fixed; bottom:20px; right:20px; background:#8B2020; color:#fff; border:none; padding:14px 28px; font-size:17px; font-family:inherit; cursor:pointer; border-radius:999px; z-index:999; box-shadow:0 8px 24px rgba(0,0,0,0.25); }
        .print-btn:hover { opacity:.9; }
    </style></head><body>';

    echo '<button class="no-print print-btn" onclick="window.print()">🖨 அச்சிடு / PDF சேமி</button>';
    echo '<div class="container">';

    // Cover
    echo '<div class="cover">';
    echo '<h1>' . esc_html($cat_name) . '</h1>';
    echo '<div class="subtitle">தமிழ் கவிதைகள் தொகுப்பு</div>';
    echo '<div class="author">' . esc_html($poet) . '</div>';
    echo '<div class="meta">' . esc_html($location) . ' · ' . date('Y') . ' · ' . $q->found_posts . ' கவிதைகள்</div>';
    echo '</div>';

    // TOC
    echo '<div class="toc"><h2>உள்ளடக்கம்</h2>';
    $i = 1;
    while ($q->have_posts()) { $q->the_post();
        echo '<div class="toc-item"><span>' . esc_html(get_the_title()) . '</span><span>' . $i . '</span></div>';
        $i++;
    }
    echo '</div>';
    wp_reset_postdata();

    // Poems
    $q2 = new WP_Query($args);
    $i = 1;
    $total = $q2->found_posts;
    while ($q2->have_posts()) { $q2->the_post();
        $year = get_post_meta(get_the_ID(),'_kv_year',true);
        $loc  = get_post_meta(get_the_ID(),'_kv_origin_loc',true);
        $ded  = get_post_meta(get_the_ID(),'_kv_dedication',true);
        $back = get_post_meta(get_the_ID(),'_kv_backstory',true);
        $cats = get_the_terms(get_the_ID(),'kavithai_cat');
        $cat  = ($cats && !is_wp_error($cats)) ? $cats[0]->name : '';

        echo '<div class="poem-page">';
        echo '<div class="poem-num">' . $i . ' / ' . $total . ($cat ? ' · ' . esc_html($cat) : '') . '</div>';
        echo '<h2 class="poem-title">' . esc_html(get_the_title()) . '</h2>';
        $meta_parts = [];
        if ($year) $meta_parts[] = '<span>' . esc_html($year) . '</span>';
        if ($loc)  $meta_parts[] = '<span>📍 ' . esc_html($loc) . '</span>';
        if ($meta_parts) echo '<div class="poem-meta">' . implode('',$meta_parts) . '</div>';
        if ($ded) echo '<div class="poem-dedication">💝 ' . esc_html($ded) . '</div>';
        echo '<div class="poem-body">' . wp_kses_post(wpautop(get_the_content())) . '</div>';
        if ($back) echo '<div class="poem-backstory">' . esc_html($back) . '</div>';
        echo '</div>';
        $i++;
    }
    wp_reset_postdata();

    echo '</div></body></html>';
}
