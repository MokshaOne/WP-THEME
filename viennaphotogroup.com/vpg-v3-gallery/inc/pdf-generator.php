<?php
/**
 * VPG v3 — PDF generator (mPDF wrapper) · Gallery edition.
 *
 * Renders a vpg_magazine issue (title + cover + lede + articles repeater)
 * into a print-ready PDF stored in /wp-content/uploads/vpg-pdf/.
 * URL is then saved to post_meta `_vpg_pdf_url` so the list view + the
 * single template can link to it.
 *
 * Typography: the theme ships Archivo (assets/fonts/*.ttf, regular +
 * expanded display cuts), embedded into the PDF so the issue carries the
 * Gallery look — white ground, near-black ink, one red, hairlines, wall-
 * label captions. If the font files are missing, mPDF falls back to
 * DejaVu Sans and the layout still holds.
 *
 * Requirements:
 *   composer require mpdf/mpdf   (from the theme root)
 *   /vendor/autoload.php must exist
 *
 * Graceful fallback: if mPDF is not installed, links the user to a
 * browser-print page that uses the print stylesheet baked into base.css.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_post_vpg_generate_pdf', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_generate_pdf' );

    $issue_id = (int) ( $_GET['issue'] ?? 0 );
    $issue    = $issue_id ? get_post( $issue_id ) : null;
    if ( ! $issue || $issue->post_type !== 'vpg_magazine' ) wp_die( 'Issue not found' );

    $autoload = VPG_V2_DIR . '/vendor/autoload.php';

    // ── Fallback path · no mPDF installed ──
    if ( ! file_exists( $autoload ) ) {
        $print_url = add_query_arg( 'vpg_print', '1', get_permalink( $issue_id ) );
        wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine&pdf=fallback&issue=' . $issue_id . '&print=' . urlencode( $print_url ) ) );
        exit;
    }

    require_once $autoload;

    // ── Gather issue data ──
    $title      = $issue->post_title;
    $lede       = $issue->post_excerpt;
    $issue_no   = get_post_meta( $issue_id, '_vpg_issue_number', true );
    $issue_dt   = get_post_meta( $issue_id, '_vpg_issue_date',   true );
    $cover_id   = (int) get_post_thumbnail_id( $issue_id );
    $cover_path = $cover_id ? get_attached_file( $cover_id ) : '';
    $articles   = vpg_get_articles( $issue_id );

    /* ── Gallery stylesheet · tokens from gallery.css, in pt/mm ── */
    $css = <<<'CSS'
    body { font-family: 'archivo', sans-serif; color: #0B0B0B; line-height: 1.55; font-size: 10.5pt; }
    h1, h2, h3 { margin: 0; line-height: 1.05; }
    .g-kicker  { font-family: 'archivo', sans-serif; font-weight: bold; font-size: 7.5pt; letter-spacing: 2pt; text-transform: uppercase; color: #E5341F; }
    .g-meta    { font-family: 'archivo', sans-serif; font-size: 7pt; letter-spacing: 1.6pt; text-transform: uppercase; color: #6A6A6A; }
    .g-rule    { border-bottom: 0.4pt solid #E6E5E1; margin: 0; }
    .g-rule--ink { border-bottom: 1.2pt solid #0B0B0B; margin: 0; }

    /* ── Cover ── */
    .vpg-cover { text-align: left; padding: 8mm 0 0; }
    .vpg-cover .strip { font-family: 'archivo'; font-size: 7pt; letter-spacing: 1.8pt; text-transform: uppercase; color: #6A6A6A; padding-bottom: 3mm; border-bottom: 1.2pt solid #0B0B0B; }
    .vpg-cover .redsq { color: #E5341F; font-size: 10pt; }
    .vpg-cover h1 { font-family: 'archivoexp'; font-weight: bold; font-size: 42pt; letter-spacing: -0.5pt; text-transform: uppercase; line-height: 0.92; margin: 10mm 0 6mm; }
    .vpg-cover h1 .dot { color: #E5341F; }
    .vpg-cover .lede { font-size: 12pt; line-height: 1.5; color: #2C2C2C; margin: 0 0 8mm; }
    .vpg-cover img { width: 100%; margin: 2mm 0 3mm; }
    .vpg-cover .caption { font-size: 7pt; letter-spacing: 1.6pt; text-transform: uppercase; color: #6A6A6A; }

    /* ── Contents · catalogue index ── */
    .vpg-toc { padding: 4mm 0 0; }
    .vpg-toc .head { font-family: 'archivoexp'; font-weight: bold; font-size: 20pt; text-transform: uppercase; letter-spacing: -0.3pt; padding-bottom: 4mm; border-bottom: 1.2pt solid #0B0B0B; margin-bottom: 2mm; }
    .vpg-toc table { width: 100%; border-collapse: collapse; }
    .vpg-toc td { padding: 4mm 0; border-bottom: 0.4pt solid #E6E5E1; vertical-align: baseline; }
    .vpg-toc .num    { width: 14mm; font-weight: bold; font-size: 9pt; color: #E5341F; }
    .vpg-toc .title  { font-family: 'archivoexp'; font-weight: bold; font-size: 12pt; text-transform: uppercase; letter-spacing: -0.2pt; }
    .vpg-toc .author { width: 45mm; font-size: 7pt; letter-spacing: 1.4pt; text-transform: uppercase; color: #6A6A6A; text-align: right; }

    /* ── Articles ── */
    .vpg-article { padding: 2mm 0 0; }
    .vpg-article .ar-kicker { font-weight: bold; font-size: 7.5pt; letter-spacing: 2pt; text-transform: uppercase; color: #E5341F; margin: 0 0 3mm; }
    .vpg-article h2 { font-family: 'archivoexp'; font-weight: bold; font-size: 24pt; text-transform: uppercase; letter-spacing: -0.4pt; line-height: 0.95; margin: 0 0 3mm; }
    .vpg-article .ar-author { font-size: 7.5pt; letter-spacing: 1.6pt; text-transform: uppercase; color: #6A6A6A; padding-bottom: 4mm; border-bottom: 1.2pt solid #0B0B0B; margin: 0 0 5mm; }
    .vpg-article .lead-img { width: 100%; margin: 0 0 2mm; }
    .vpg-article .body p { margin: 0 0 3.5mm; }
    .vpg-article .body a { color: #0B0B0B; text-decoration: none; border-bottom: 0.6pt solid #E5341F; }
    .vpg-article .body img { width: 100%; margin: 5mm 0 1.5mm; }
    .vpg-article .body figure { margin: 5mm 0; padding: 0; }
    .vpg-article .body figure.plate { page-break-before: always; page-break-after: always; margin: 0; }
    .vpg-article .body .pair figure { page-break-inside: avoid; }
    .vpg-article .body figcaption { font-size: 7pt; letter-spacing: 1.4pt; text-transform: uppercase; color: #6A6A6A; margin: 1.5mm 0 0; }
    .vpg-article .body blockquote { margin: 5mm 0; padding: 0 0 0 5mm; border-left: 1.2pt solid #E5341F; font-style: italic; color: #2C2C2C; }
    .vpg-article .body ul, .vpg-article .body ol { margin: 0 0 3.5mm 5mm; }

    /* ── Colophon ── */
    .vpg-foot-page { text-align: center; padding: 30mm 0 0; }
    .vpg-foot-page .brand { font-family: 'archivoexp'; font-weight: bold; font-size: 16pt; text-transform: uppercase; }
    .vpg-foot-page .brand .dot { color: #E5341F; }
    .vpg-foot-page .line { font-size: 7pt; letter-spacing: 1.8pt; text-transform: uppercase; color: #6A6A6A; margin-top: 4mm; }
CSS;

    /* ── Cover page HTML ── */
    ob_start();
    ?>
    <div class="vpg-cover">
        <p class="strip"><span class="redsq">■</span>&nbsp;&nbsp;VIENNAPHOTOGROUP<span style="color:#E5341F">.</span>&nbsp;&nbsp;&nbsp;<?php echo esc_html( trim( ( $issue_no ?: '' ) . ( $issue_dt ? ' · ' . $issue_dt : '' ), ' ·' ) ); ?></p>
        <h1><?php echo esc_html( $title ); ?><span class="dot">.</span></h1>
        <?php if ( $lede ) : ?>
            <p class="lede"><?php echo esc_html( $lede ); ?></p>
        <?php endif; ?>
        <?php if ( $cover_path && file_exists( $cover_path ) ) : ?>
            <img src="<?php echo esc_attr( $cover_path ); ?>" alt="">
            <p class="caption">Cover · <?php echo esc_html( $issue_no ?: $title ); ?></p>
        <?php endif; ?>
    </div>
    <?php
    $cover_html = ob_get_clean();

    /* ── Editorial + contents + articles + colophon HTML ── */
    ob_start();
    $editorial = get_post_meta( $issue_id, '_vpg_editorial', true );
    if ( $editorial ) : // 0163 · the editor's letter opens the issue ?>
    <div style="padding:18mm 6mm 0">
        <p style="font-size:8pt;font-weight:800;letter-spacing:2.4pt;text-transform:uppercase;color:#E5341F">● Editorial</p>
        <div style="font-size:11.5pt;line-height:1.75;margin-top:6mm;max-width:130mm"><?php echo wpautop( esc_html( $editorial ) ); ?></div>
    </div>
    <pagebreak />
    <?php endif;

    if ( $articles ) :
        // 0164 · contents grouped by section — the issue's skeleton visible
        $sections = function_exists( 'vpg_mag_sections' ) ? vpg_mag_sections() : [];
        $grouped  = [];
        foreach ( $articles as $i => $a ) $grouped[ $a['section'] ?? '' ][] = $i;
    ?>
    <div class="vpg-toc">
        <p class="head">Contents</p>
        <?php foreach ( $grouped as $sec => $idxs ) : ?>
            <?php if ( $sec !== '' && isset( $sections[ $sec ] ) ) : ?>
                <p style="font-size:7.5pt;font-weight:800;letter-spacing:2.2pt;text-transform:uppercase;color:#E5341F;margin:5mm 0 1.5mm"><?php echo esc_html( $sections[ $sec ] ); ?></p>
            <?php endif; ?>
            <table>
                <?php foreach ( $idxs as $i ) : $a = $articles[ $i ]; ?>
                <tr>
                    <td class="num"><?php printf( '%02d', $i + 1 ); ?></td>
                    <td class="title"><a href="#art-<?php echo (int) $i; ?>" style="text-decoration:none;color:#0B0B0B"><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></a></td>
                    <td class="author"><?php echo esc_html( $a['author'] ?? '' ); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    </div>
    <pagebreak />
    <?php endif;

    foreach ( $articles as $i => $a ) :
        $img_path = ! empty( $a['image_id'] ) ? get_attached_file( (int) $a['image_id'] ) : '';
        $body     = (string) ( $a['body'] ?? '' );
        // Photo spreads carry their images in the body · don't repeat the lead image.
        $body_has_lead = $img_path && str_contains( $body, (string) wp_get_attachment_image_url( (int) $a['image_id'], 'large' ) );
    ?>
    <div class="vpg-article">
        <a name="art-<?php echo (int) $i; ?>"></a><bookmark content="<?php echo esc_attr( $a['title'] ?: 'Untitled' ); ?>" />
        <p class="ar-kicker"><?php printf( '%02d', $i + 1 ); ?> — <?php
            $sec_l = function_exists( 'vpg_mag_sections' ) ? ( vpg_mag_sections()[ $a['section'] ?? '' ] ?? '' ) : '';
            echo esc_html( trim( ( $sec_l && ( $a['section'] ?? '' ) !== '' ? $sec_l . ' · ' : '' ) . ( $a['author'] ?: 'Article' ) ) );
        ?></p>
        <bookmark content="<?php echo esc_attr( $a['title'] ?: 'Untitled' ); ?>"></bookmark>
        <h2><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></h2>
        <p class="ar-author"><?php echo esc_html( trim( ( $a['author'] ?? '' ) . ' · Vienna Photo Group', ' ·' ) ); ?></p>
        <?php if ( $img_path && ! $body_has_lead && file_exists( $img_path ) ) : ?>
            <img class="lead-img" src="<?php echo esc_attr( $img_path ); ?>" alt="">
        <?php endif; ?>
        <div class="body">
            <?php echo wpautop( wp_kses_post( $body ) ); ?>
        </div>
    </div>
    <?php if ( ! empty( $a['page_break_after'] ) || $i < count( $articles ) - 1 ) : ?>
        <pagebreak />
    <?php endif; ?>
    <?php endforeach; ?>

    <?php
    // 0171 · contributors, collected from the articles themselves
    $contributors = array_values( array_unique( array_filter( array_map(
        fn( $a ) => trim( (string) ( $a['author'] ?? '' ) ), $articles
    ) ) ) );
    sort( $contributors );
    // 0172 · the issue in numbers
    $word_count  = array_sum( array_map( fn( $a ) => str_word_count( wp_strip_all_tags( (string) ( $a['body'] ?? '' ) ) ), $articles ) );
    $photo_count = array_sum( array_map( fn( $a ) => substr_count( (string) ( $a['body'] ?? '' ), '<img' ) + ( empty( $a['image_id'] ) ? 0 : 1 ), $articles ) );
    // 0173 · what the next issue brings
    $next_teaser = get_post_meta( $issue_id, '_vpg_next_teaser', true );
    ?>
    <div class="vpg-foot-page">
        <p class="brand">Viennaphotogroup<span class="dot">.</span></p>
        <p class="line"><?php echo esc_html( trim( ( $issue_no ?: 'Issue' ) . ' · ' . ( $issue_dt ?: gmdate( 'F Y' ) ), ' ·' ) ); ?></p>
        <?php if ( $contributors ) : ?>
            <p class="line" style="margin-top:6mm"><strong>Contributors</strong> — <?php echo esc_html( implode( ' · ', $contributors ) ); ?></p>
        <?php endif; ?>
        <p class="line"><?php printf( '%d articles · %d photographs · %s words', count( $articles ), (int) $photo_count, number_format_i18n( $word_count ) ); ?></p>
        <?php if ( $next_teaser ) : ?>
            <p class="line" style="margin-top:6mm"><strong>In the next issue</strong> — <?php echo esc_html( $next_teaser ); ?></p>
        <?php endif; ?>
        <p class="line">© <?php echo esc_html( gmdate( 'Y' ) ); ?> viennaphotogroup.com — a member-run photography magazine · Wien</p>
        <p class="line">Photographs remain the property of their photographers · credited by name</p>
    </div>
    <?php
    $rest_html = ob_get_clean();

    // ── Configure mPDF ──
    $upload_dir = wp_upload_dir();
    $pdf_dir    = $upload_dir['basedir'] . '/vpg-pdf';
    $pdf_url    = $upload_dir['baseurl'] . '/vpg-pdf';
    wp_mkdir_p( $pdf_dir );
    wp_mkdir_p( $pdf_dir . '/tmp' );   // mPDF tempDir · must exist before constructor

    $filename  = sanitize_title( $title ) . '-' . $issue_id . '.pdf';
    $file_path = $pdf_dir . '/' . $filename;

    try {
        // Embed the theme's Archivo cuts when present · DejaVu Sans otherwise.
        $font_config  = ( new \Mpdf\Config\FontVariables() )->getDefaults();
        $font_data    = $font_config['fontdata'];
        $font_dirs    = ( new \Mpdf\Config\ConfigVariables() )->getDefaults()['fontDir'];
        $vpg_fonts    = VPG_V2_DIR . '/assets/fonts';
        $default_font = 'dejavusans';

        if ( file_exists( $vpg_fonts . '/Archivo-400.ttf' ) ) {
            $font_dirs[]          = $vpg_fonts;
            $font_data['archivo'] = [
                'R' => 'Archivo-400.ttf',
                'B' => file_exists( $vpg_fonts . '/Archivo-700.ttf' ) ? 'Archivo-700.ttf' : 'Archivo-400.ttf',
            ];
            $default_font = 'archivo';
            if ( file_exists( $vpg_fonts . '/ArchivoExpanded-900.ttf' ) ) {
                $font_data['archivoexp'] = [
                    'R' => 'ArchivoExpanded-900.ttf',
                    'B' => 'ArchivoExpanded-900.ttf',
                ];
            } else {
                $font_data['archivoexp'] = $font_data['archivo'];
            }
        }

        $mpdf = new \Mpdf\Mpdf( [
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_left'       => 16,
            'margin_right'      => 16,
            'margin_top'        => 16,
            'margin_bottom'     => 20,
            'default_font_size' => 10.5,
            'default_font'      => $default_font,
            'fontDir'           => $font_dirs,
            'fontdata'          => $font_data,
            'tempDir'           => $upload_dir['basedir'] . '/vpg-pdf/tmp',
        ] );
        $mpdf->SetTitle( $title );
        $mpdf->SetAuthor( 'Vienna Photo Group' );
        $mpdf->SetCreator( 'VPG v3' );
        // 0197 · every bit of structure a shared-hosting PDF can carry:
        // document language, subject metadata, and a bookmark outline.
        $mpdf->SetSubject( __( 'A free, member-run photography magazine — Vienna', 'vpg-v2' ) );
        $mpdf->SetKeywords( 'photography, Vienna, Wien, magazine, community' );

        // Cover carries no folio · the footer is set only after the cover
        // page is closed (AddPage), so it starts with the contents page.
        $mpdf->WriteHTML( $css, \Mpdf\HTMLParserMode::HEADER_CSS );
        $mpdf->WriteHTML( $cover_html, \Mpdf\HTMLParserMode::HTML_BODY );
        $mpdf->AddPage();
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: archivo; font-size: 6.5pt; letter-spacing: 1.6pt; text-transform: uppercase; color: #9C9A95; border-top: 0.4pt solid #E6E5E1; padding-top: 2mm;"><tr>' .
            '<td width="50%">Viennaphotogroup<span style="color:#E5341F">.</span> · ' . esc_html( $issue_no ?: gmdate( 'Y' ) ) . '</td>' .
            '<td width="50%" align="right">{PAGENO}</td>' .
            '</tr></table>'
        );
        $mpdf->WriteHTML( $rest_html, \Mpdf\HTMLParserMode::HTML_BODY );

        $mpdf->Output( $file_path, \Mpdf\Output\Destination::FILE );

        update_post_meta( $issue_id, '_vpg_pdf_url', $pdf_url . '/' . $filename );
        update_post_meta( $issue_id, '_vpg_pdf_built_at', current_time( 'mysql' ) );

        wp_safe_redirect( admin_url( 'admin.php?page=vpg-magazine&pdf=ok&issue=' . $issue_id ) );
        exit;
    } catch ( \Throwable $e ) {
        wp_die( 'PDF generation failed: ' . esc_html( $e->getMessage() ) );
    }
} );

/* ─── Public · render an issue HTML inside the front-end (used by ?vpg_print=1) ── */
add_action( 'template_redirect', function () {
    if ( ! is_singular( 'vpg_magazine' ) || empty( $_GET['vpg_print'] ) ) return;
    add_filter( 'body_class', function ( $c ) { $c[] = 'vpg-print-mode'; return $c; } );
} );

/* ════════════════════════════════════════════════════════════════ */
/*  Map guide PDF · every published location, grouped by district    */
/*  Editors build it (button on the map-guide page); everyone        */
/*  downloads it once built.                                         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_mapguide_pdf', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_mapguide_pdf' );

    $autoload = VPG_V2_DIR . '/vendor/autoload.php';
    if ( ! file_exists( $autoload ) ) wp_die( 'mPDF not installed (composer install).' );
    require_once $autoload;

    // Gather locations grouped by district
    $items = get_posts( [ 'post_type' => 'vpg_location', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ] );
    $groups = [];
    foreach ( $items as $p ) {
        $district = get_post_meta( $p->ID, 'location_district', true ) ?: __( 'Unsorted', 'vpg-v2' );
        $coords   = function_exists( 'vpg_get_coords' ) ? vpg_get_coords( $p->ID ) : null;
        $groups[ $district ][] = [
            'title'  => get_the_title( $p ),
            'lede'   => wp_trim_words( get_the_excerpt( $p ), 28 ),
            'best'   => get_post_meta( $p->ID, 'location_best_time', true ),
            'coords' => $coords ? sprintf( '%.5F, %.5F', $coords[0], $coords[1] ) : '',
        ];
    }
    ksort( $groups );

    $css = <<<'CSS'
    body { font-family: 'archivo', sans-serif; color: #0B0B0B; font-size: 10pt; line-height: 1.5; }
    h1 { font-family: 'archivoexp'; font-weight: bold; font-size: 34pt; text-transform: uppercase; letter-spacing: -0.5pt; line-height: 0.92; margin: 8mm 0 4mm; }
    h1 .dot { color: #E5341F; }
    .strip { font-size: 7pt; letter-spacing: 1.8pt; text-transform: uppercase; color: #6A6A6A; padding-bottom: 3mm; border-bottom: 1.2pt solid #0B0B0B; }
    .lede { font-size: 11pt; color: #2C2C2C; margin: 0 0 6mm; }
    h2 { font-family: 'archivoexp'; font-weight: bold; font-size: 16pt; text-transform: uppercase; border-bottom: 1.2pt solid #0B0B0B; padding-bottom: 2mm; margin: 8mm 0 3mm; }
    .loc { padding: 3mm 0; border-bottom: 0.4pt solid #E6E5E1; }
    .loc .t { font-weight: bold; font-size: 11pt; text-transform: uppercase; }
    .loc .m { font-size: 7pt; letter-spacing: 1.4pt; text-transform: uppercase; color: #E5341F; margin: 1mm 0; }
    .loc .d { color: #2C2C2C; margin: 1mm 0 0; }
CSS;

    ob_start();
    ?>
    <p class="strip"><span style="color:#E5341F">■</span>&nbsp;&nbsp;VIENNAPHOTOGROUP<span style="color:#E5341F">.</span>&nbsp;&nbsp;&nbsp;<?php echo esc_html( gmdate( 'F Y' ) ); ?></p>
    <h1><?php esc_html_e( 'The Map', 'vpg-v2' ); ?><span class="dot">.</span></h1>
    <p class="lede"><?php printf( esc_html__( '%d curated Vienna locations — light notes, best times, coordinates. Member-curated, printed to take along.', 'vpg-v2' ), count( $items ) ); ?></p>
    <?php foreach ( $groups as $district => $rows ) : ?>
        <h2><?php echo esc_html( $district ); ?></h2>
        <?php foreach ( $rows as $r ) : ?>
        <div class="loc">
            <div class="t"><?php echo esc_html( $r['title'] ); ?></div>
            <div class="m"><?php echo esc_html( trim( ( $r['best'] ? 'Best: ' . $r['best'] . ' · ' : '' ) . $r['coords'], ' ·' ) ); ?></div>
            <?php if ( $r['lede'] ) : ?><div class="d"><?php echo esc_html( $r['lede'] ); ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php
    $html = ob_get_clean();

    $upload_dir = wp_upload_dir();
    $pdf_dir    = $upload_dir['basedir'] . '/vpg-pdf';
    wp_mkdir_p( $pdf_dir );
    wp_mkdir_p( $pdf_dir . '/tmp' );
    $file = $pdf_dir . '/vpg-map-guide.pdf';

    try {
        $font_config  = ( new \Mpdf\Config\FontVariables() )->getDefaults();
        $font_data    = $font_config['fontdata'];
        $font_dirs    = ( new \Mpdf\Config\ConfigVariables() )->getDefaults()['fontDir'];
        $vpg_fonts    = VPG_V2_DIR . '/assets/fonts';
        $default_font = 'dejavusans';
        if ( file_exists( $vpg_fonts . '/Archivo-400.ttf' ) ) {
            $font_dirs[]             = $vpg_fonts;
            $font_data['archivo']    = [ 'R' => 'Archivo-400.ttf', 'B' => 'Archivo-700.ttf' ];
            $font_data['archivoexp'] = [ 'R' => 'ArchivoExpanded-900.ttf', 'B' => 'ArchivoExpanded-900.ttf' ];
            $default_font = 'archivo';
        }
        $mpdf = new \Mpdf\Mpdf( [
            'mode' => 'utf-8', 'format' => 'A5',
            'margin_left' => 12, 'margin_right' => 12, 'margin_top' => 12, 'margin_bottom' => 16,
            'default_font_size' => 10, 'default_font' => $default_font,
            'fontDir' => $font_dirs, 'fontdata' => $font_data,
            'tempDir' => $pdf_dir . '/tmp',
        ] );
        $mpdf->SetTitle( 'VPG · The Map' );
        $mpdf->SetHTMLFooter( '<table width="100%" style="font-family: archivo; font-size: 6.5pt; letter-spacing: 1.4pt; text-transform: uppercase; color: #9C9A95; border-top: 0.4pt solid #E6E5E1; padding-top: 2mm;"><tr><td>viennaphotogroup.com/locations</td><td align="right">{PAGENO}</td></tr></table>' );
        $mpdf->WriteHTML( $css, \Mpdf\HTMLParserMode::HEADER_CSS );
        $mpdf->WriteHTML( $html, \Mpdf\HTMLParserMode::HTML_BODY );
        $mpdf->Output( $file, \Mpdf\Output\Destination::FILE );

        update_option( 'vpg_mapguide_pdf', $upload_dir['baseurl'] . '/vpg-pdf/vpg-map-guide.pdf' );
        update_option( 'vpg_mapguide_pdf_built', current_time( 'mysql' ) );
        wp_safe_redirect( add_query_arg( 'vpg_status', 'ok', wp_get_referer() ?: home_url( '/map-guide/' ) ) );
        exit;
    } catch ( \Throwable $e ) {
        wp_die( 'Map guide PDF failed: ' . esc_html( $e->getMessage() ) );
    }
} );


/* ─── 0334 · Portfolio PDF · a member's application-ready booklet ── */
add_action( 'admin_post_vpg_portfolio_pdf', function () {
    if ( ! is_user_logged_in() ) wp_die( 'Members only.', 403 );
    check_admin_referer( 'vpg_portfolio_pdf' );

    $autoload = VPG_V2_DIR . '/vendor/autoload.php';
    if ( ! file_exists( $autoload ) ) wp_die( 'mPDF not installed (composer install).' );
    require_once $autoload;

    $uid  = get_current_user_id();
    $user = wp_get_current_user();
    $ids  = function_exists( 'vpg_get_portfolio' ) ? vpg_get_portfolio( $uid ) : [];
    if ( ! $ids ) wp_die( esc_html__( 'Curate your portfolio in the dashboard first.', 'vpg-v2' ) );

    $font_config = ( new \Mpdf\Config\FontVariables() )->getDefaults();
    $font_data   = $font_config['fontdata'];
    $font_dirs   = ( new \Mpdf\Config\ConfigVariables() )->getDefaults()['fontDir'];
    $vpg_fonts   = VPG_V2_DIR . '/assets/fonts';
    $default     = 'dejavusans';
    if ( file_exists( $vpg_fonts . '/Archivo-400.ttf' ) ) {
        $font_dirs[]          = $vpg_fonts;
        $font_data['archivo'] = [ 'R' => 'Archivo-400.ttf', 'B' => file_exists( $vpg_fonts . '/Archivo-700.ttf' ) ? 'Archivo-700.ttf' : 'Archivo-400.ttf' ];
        $default = 'archivo';
    }

    $mpdf = new \Mpdf\Mpdf( [
        'tempDir'      => get_temp_dir(),
        'fontDir'      => $font_dirs,
        'fontdata'     => $font_data,
        'default_font' => $default,
        'margin_left'  => 14, 'margin_right' => 14, 'margin_top' => 16, 'margin_bottom' => 18,
    ] );

    // Cover
    $mpdf->WriteHTML(
        '<div style="margin-top:70mm">' .
        '<p style="font-size:9pt;font-weight:bold;letter-spacing:3pt;text-transform:uppercase;color:#E5341F">Portfolio</p>' .
        '<h1 style="font-size:34pt;text-transform:uppercase;letter-spacing:-0.5pt;margin:4mm 0 6mm">' . esc_html( $user->display_name ) . '<span style="color:#E5341F">.</span></h1>' .
        ( $user->description ? '<p style="font-size:11pt;color:#6A6A6A;max-width:120mm">' . esc_html( $user->description ) . '</p>' : '' ) .
        '<p style="font-size:9pt;color:#9C9A95;margin-top:10mm">Vienna Photo Group · ' . esc_html( home_url( '/members/' . $user->user_nicename . '/' ) ) . '</p>' .
        '</div>'
    );

    foreach ( array_slice( $ids, 0, 24 ) as $aid ) {
        $file = get_attached_file( $aid );
        if ( ! $file || ! file_exists( $file ) ) continue;
        $exif = function_exists( 'vpg_photo_exif_label' ) ? vpg_photo_exif_label( $aid ) : '';
        $mpdf->AddPage();
        $mpdf->WriteHTML(
            '<div style="text-align:center"><img src="' . esc_attr( $file ) . '" style="max-width:180mm;max-height:230mm"></div>' .
            '<p style="font-size:8pt;letter-spacing:1.6pt;text-transform:uppercase;color:#9C9A95;text-align:center;margin-top:4mm">' .
            esc_html( trim( get_the_title( $aid ) . ( $exif ? ' · ' . $exif : '' ) ) ) . ' — ' . esc_html( $user->display_name ) . '</p>'
        );
    }

    nocache_headers();
    $mpdf->Output( sanitize_title( $user->display_name ) . '-portfolio.pdf', \Mpdf\Output\Destination::DOWNLOAD );
    exit;
} );
