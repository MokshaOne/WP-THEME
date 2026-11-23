<?php
/**
 * VPG v2 — PDF generator (mPDF wrapper).
 *
 * Renders a vpg_magazine issue (title + cover + lede + articles repeater)
 * into a print-ready PDF stored in /wp-content/uploads/vpg-pdf/.
 * URL is then saved to post_meta `_vpg_pdf_url` so the list view + the
 * single template can link to it.
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
    $title     = $issue->post_title;
    $lede      = $issue->post_excerpt;
    $issue_no  = get_post_meta( $issue_id, '_vpg_issue_number', true );
    $issue_dt  = get_post_meta( $issue_id, '_vpg_issue_date',   true );
    $cover_id  = (int) get_post_thumbnail_id( $issue_id );
    $cover_path = $cover_id ? get_attached_file( $cover_id ) : '';
    $articles  = vpg_get_articles( $issue_id );

    // ── Build HTML for mPDF ──
    ob_start();
    ?>
    <style>
        body { font-family: 'DejaVu Serif', Georgia, serif; color: #0F0A04; line-height: 1.6; }
        h1, h2, h3 { font-family: 'DejaVu Serif', Georgia, serif; font-weight: 700; line-height: 1.1; margin: 0; }
        .vpg-cover { text-align: center; padding: 40mm 0; }
        .vpg-cover img { max-width: 70%; margin-bottom: 20mm; }
        .vpg-cover h1 { font-size: 36pt; letter-spacing: -1pt; }
        .vpg-cover .issue { font-family: 'DejaVu Sans Mono', monospace; font-size: 10pt; letter-spacing: 2pt; text-transform: uppercase; color: #6B5A48; margin-bottom: 8mm; }
        .vpg-cover .lede  { font-style: italic; font-size: 14pt; color: #3A2C1E; margin: 12mm auto 0; max-width: 60%; }
        .vpg-toc { padding: 20mm 15mm; }
        .vpg-toc h2 { font-size: 18pt; margin-bottom: 10mm; border-bottom: 1pt solid #C8601A; padding-bottom: 4mm; }
        .vpg-toc ol { list-style: none; padding: 0; }
        .vpg-toc li { display: table; width: 100%; margin: 4mm 0; font-size: 11pt; }
        .vpg-toc li > span:first-child { display: table-cell; font-family: 'DejaVu Sans Mono', monospace; color: #C8601A; width: 18mm; vertical-align: top; }
        .vpg-toc li strong { display: table-cell; font-weight: 600; vertical-align: top; }
        .vpg-toc li em     { display: table-cell; font-style: italic; color: #6B5A48; padding-left: 6mm; vertical-align: top; }
        .vpg-article { padding: 15mm 10mm; }
        .vpg-article header { margin-bottom: 8mm; padding-bottom: 4mm; border-bottom: 1pt solid #C8601A; }
        .vpg-article .ar-num   { font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; letter-spacing: 2pt; text-transform: uppercase; color: #C8601A; }
        .vpg-article h2        { font-size: 22pt; margin: 3mm 0 2mm; }
        .vpg-article .ar-author{ font-style: italic; color: #6B5A48; font-size: 10pt; }
        .vpg-article .body p   { margin: 0 0 4mm; text-align: justify; hyphens: auto; }
        .vpg-article img       { display: block; max-width: 100%; margin: 6mm auto; }
        .vpg-foot { text-align: center; font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; color: #9A8770; padding: 12mm 0 0; border-top: 0.5pt solid #C5B6A0; margin-top: 12mm; }
    </style>

    <!-- COVER PAGE -->
    <section class="vpg-cover">
        <?php if ( $cover_path && file_exists( $cover_path ) ) : ?>
            <img src="<?php echo esc_attr( $cover_path ); ?>" alt="">
        <?php endif; ?>
        <?php if ( $issue_no ) : ?>
            <p class="issue"><?php echo esc_html( $issue_no ); ?>  ·  <?php echo esc_html( $issue_dt ); ?></p>
        <?php endif; ?>
        <h1><?php echo esc_html( $title ); ?></h1>
        <?php if ( $lede ) : ?>
            <p class="lede"><?php echo esc_html( $lede ); ?></p>
        <?php endif; ?>
    </section>

    <pagebreak />

    <!-- TABLE OF CONTENTS -->
    <?php if ( $articles ) : ?>
    <section class="vpg-toc">
        <h2>Contents</h2>
        <ol>
            <?php foreach ( $articles as $i => $a ) : ?>
                <li>
                    <span><?php printf( '%02d', $i + 1 ); ?></span>
                    <strong><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></strong>
                    <em><?php echo $a['author'] ? esc_html( '— ' . $a['author'] ) : ''; ?></em>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
    <pagebreak />
    <?php endif; ?>

    <!-- ARTICLES -->
    <?php foreach ( $articles as $i => $a ) :
        $img_path = ! empty( $a['image_id'] ) ? get_attached_file( (int) $a['image_id'] ) : '';
    ?>
    <section class="vpg-article">
        <header>
            <span class="ar-num">Article <?php printf( '%02d', $i + 1 ); ?></span>
            <h2><?php echo esc_html( $a['title'] ?: 'Untitled' ); ?></h2>
            <?php if ( ! empty( $a['author'] ) ) : ?>
                <p class="ar-author">— <?php echo esc_html( $a['author'] ); ?></p>
            <?php endif; ?>
        </header>
        <?php if ( $img_path && file_exists( $img_path ) ) : ?>
            <img src="<?php echo esc_attr( $img_path ); ?>" alt="">
        <?php endif; ?>
        <div class="body">
            <?php echo wpautop( wp_kses_post( $a['body'] ?? '' ) ); ?>
        </div>
    </section>
    <?php if ( ! empty( $a['page_break_after'] ) ) : ?>
        <pagebreak />
    <?php endif; ?>
    <?php endforeach; ?>

    <!-- COLOPHON -->
    <p class="vpg-foot">
        Vienna Photo Group · <?php echo esc_html( $issue_no ?: 'Issue' ); ?> · <?php echo esc_html( $issue_dt ?: gmdate( 'F Y' ) ); ?><br>
        © <?php echo esc_html( gmdate( 'Y' ) ); ?>  viennaphotogroup.com — member-run, ad-free.
    </p>
    <?php
    $html = ob_get_clean();

    // ── Configure mPDF ──
    $upload_dir = wp_upload_dir();
    $pdf_dir    = $upload_dir['basedir'] . '/vpg-pdf';
    $pdf_url    = $upload_dir['baseurl'] . '/vpg-pdf';
    wp_mkdir_p( $pdf_dir );
    wp_mkdir_p( $pdf_dir . '/tmp' );   // mPDF tempDir · must exist before constructor

    $filename = sanitize_title( $title ) . '-' . $issue_id . '.pdf';
    $file_path = $pdf_dir . '/' . $filename;

    try {
        $mpdf = new \Mpdf\Mpdf( [
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_left'       => 18,
            'margin_right'      => 18,
            'margin_top'        => 20,
            'margin_bottom'     => 20,
            'default_font_size' => 11,
            'default_font'      => 'dejavuserif',
            'tempDir'           => $upload_dir['basedir'] . '/vpg-pdf/tmp',
        ] );
        $mpdf->SetTitle( $title );
        $mpdf->SetAuthor( 'Vienna Photo Group' );
        $mpdf->SetCreator( 'VPG v2' );
        $mpdf->WriteHTML( $html );
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
