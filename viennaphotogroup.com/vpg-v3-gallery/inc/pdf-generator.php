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

    /* ── Contents + articles + colophon HTML ── */
    ob_start();
    if ( $articles ) : ?>
    <div class="vpg-toc">
        <p class="head">Contents</p>
        <table>
            <?php foreach ( $articles as $i => $a ) : ?>
            <tr>
                <td class="num"><?php printf( '%02d', $i + 1 ); ?></td>
                <td class="title"><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></td>
                <td class="author"><?php echo esc_html( $a['author'] ?? '' ); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
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
        <p class="ar-kicker"><?php printf( '%02d', $i + 1 ); ?> — <?php echo esc_html( $a['author'] ?: 'Article' ); ?></p>
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

    <div class="vpg-foot-page">
        <p class="brand">Viennaphotogroup<span class="dot">.</span></p>
        <p class="line"><?php echo esc_html( trim( ( $issue_no ?: 'Issue' ) . ' · ' . ( $issue_dt ?: gmdate( 'F Y' ) ), ' ·' ) ); ?></p>
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
