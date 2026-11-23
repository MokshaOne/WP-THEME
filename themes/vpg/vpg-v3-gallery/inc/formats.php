<?php
/**
 * VPG v3 — Q9 · more ways to hold the magazine.
 *
 *   0198  EPUB export · the issue as a reflowable e-book
 *   0202  Zine · an A6 pocket zine PDF from any issue
 *   0201  Annual · a year's issues bound into one PDF
 *   0199  Audio · browser text-to-speech "listen" on issues & journal
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════ */
/*  0198 · EPUB · a valid EPUB 3 is a ZIP of XHTML — pure PHP         */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_vpg_epub', function () {
    $issue_id = (int) ( $_GET['issue'] ?? 0 );
    $issue    = $issue_id ? get_post( $issue_id ) : null;
    if ( ! $issue || $issue->post_type !== 'vpg_magazine' ) wp_die( 'Issue not found' );
    // Members read issues; the file is the same one they can already open.
    if ( ! is_user_logged_in() && $issue->post_status !== 'publish' ) wp_die( 'Members only.', 403 );
    if ( ! class_exists( 'ZipArchive' ) ) wp_die( 'EPUB needs the PHP zip extension.' );

    $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $issue_id ) : [];
    $title    = $issue->post_title;
    $uid      = 'urn:uuid:' . md5( home_url() . $issue_id );

    $chapters = [];
    $editorial = get_post_meta( $issue_id, '_vpg_editorial', true );
    if ( $editorial ) {
        $chapters[] = [ 'title' => __( 'Editorial', 'vpg-v2' ), 'html' => wpautop( wp_kses_post( $editorial ) ) ];
    }
    foreach ( $articles as $a ) {
        $chapters[] = [
            'title' => $a['title'] ?? __( 'Untitled', 'vpg-v2' ),
            'html'  => ( ! empty( $a['author'] ) ? '<p class="by">' . esc_html( $a['author'] ) . '</p>' : '' ) . wp_kses_post( $a['body'] ?? '' ),
        ];
    }
    if ( ! $chapters ) wp_die( 'This issue has no content yet.' );

    $tmp = wp_tempnam( 'vpg-epub' );
    $zip = new ZipArchive();
    if ( $zip->open( $tmp, ZipArchive::OVERWRITE ) !== true ) wp_die( 'Could not build EPUB.' );

    // mimetype must be first and stored (uncompressed)
    $zip->addFromString( 'mimetype', 'application/epub+zip' );
    $zip->setCompressionName( 'mimetype', ZipArchive::CM_STORE );
    $zip->addFromString( 'META-INF/container.xml',
        '<?xml version="1.0"?><container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container"><rootfiles><rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/></rootfiles></container>' );

    $css = 'body{font-family:Georgia,serif;line-height:1.6;margin:1em}h1{font-family:sans-serif;font-size:1.4em}p.by{color:#666;font-style:italic;font-family:sans-serif}figure{margin:1em 0}img{max-width:100%}';
    $zip->addFromString( 'OEBPS/style.css', $css );

    $manifest = $spine = $nav = '';
    foreach ( $chapters as $i => $c ) {
        $fn   = sprintf( 'ch%03d.xhtml', $i );
        $html = '<?xml version="1.0" encoding="utf-8"?>' . "\n"
              . '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"><head>'
              . '<meta charset="utf-8"/><title>' . esc_html( $c['title'] ) . '</title><link rel="stylesheet" href="style.css"/></head><body>'
              . '<h1>' . esc_html( $c['title'] ) . '</h1>' . vpg_epub_clean( $c['html'] ) . '</body></html>';
        $zip->addFromString( 'OEBPS/' . $fn, $html );
        $manifest .= '<item id="ch' . $i . '" href="' . $fn . '" media-type="application/xhtml+xml"/>';
        $spine    .= '<itemref idref="ch' . $i . '"/>';
        $nav      .= '<li><a href="' . $fn . '">' . esc_html( $c['title'] ) . '</a></li>';
    }

    $navdoc = '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops"><head><meta charset="utf-8"/><title>' . esc_html( $title ) . '</title></head><body><nav epub:type="toc" id="toc"><h1>' . esc_html__( 'Contents', 'vpg-v2' ) . '</h1><ol>' . $nav . '</ol></nav></body></html>';
    $zip->addFromString( 'OEBPS/nav.xhtml', $navdoc );

    $opf = '<?xml version="1.0" encoding="utf-8"?>'
         . '<package xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="pub-id"><metadata xmlns:dc="http://purl.org/dc/elements/1.1/">'
         . '<dc:identifier id="pub-id">' . esc_html( $uid ) . '</dc:identifier>'
         . '<dc:title>' . esc_html( $title ) . '</dc:title>'
         . '<dc:language>' . esc_html( substr( get_bloginfo( 'language' ), 0, 2 ) ) . '</dc:language>'
         . '<dc:creator>' . esc_html( get_bloginfo( 'name' ) ) . '</dc:creator>'
         . '<meta property="dcterms:modified">' . gmdate( 'Y-m-d\TH:i:s\Z' ) . '</meta></metadata>'
         . '<manifest><item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/><item id="css" href="style.css" media-type="text/css"/>' . $manifest . '</manifest>'
         . '<spine>' . $spine . '</spine></package>';
    $zip->addFromString( 'OEBPS/content.opf', $opf );
    $zip->close();

    $data = file_get_contents( $tmp );
    @unlink( $tmp );
    nocache_headers();
    header( 'Content-Type: application/epub+zip' );
    header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $title ) . '.epub"' );
    header( 'Content-Length: ' . strlen( $data ) );
    echo $data;
    exit;
} );

/** Strip figures down to what EPUB readers accept; keep it well-formed. */
function vpg_epub_clean( $html ) {
    $html = preg_replace( '/\s(style|class|id|data-[\w-]+)="[^"]*"/i', '', (string) $html );
    $html = preg_replace( '/<(img[^>]*[^\/])>/i', '<$1/>', $html );      // self-close imgs
    $html = str_replace( '&nbsp;', '&#160;', $html );
    if ( strip_tags( $html ) === $html || stripos( $html, '<p' ) === false ) {
        $html = wpautop( $html );
    }
    return $html;
}

/* Listen/EPUB buttons under a published issue */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'vpg_magazine' ) || ! in_the_loop() || ! is_main_query() ) return $content;
    $id = get_the_ID();
    ob_start(); ?>
    <p style="display:flex;gap:12px;flex-wrap:wrap;margin:24px 0">
      <?php if ( class_exists( 'ZipArchive' ) ) : ?>
        <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_epub&issue=' . $id ) ); ?>">📖 <?php esc_html_e( 'EPUB', 'vpg-v2' ); ?></a>
      <?php endif; ?>
      <a class="g-btn g-btn--ghost" style="font-size:12px" href="<?php echo esc_url( admin_url( 'admin-post.php?action=vpg_zine&issue=' . $id ) ); ?>">🗞 <?php esc_html_e( 'Pocket zine PDF', 'vpg-v2' ); ?></a>
      <button type="button" class="g-btn g-btn--ghost vpg-listen" style="font-size:12px">🔊 <?php esc_html_e( 'Listen', 'vpg-v2' ); ?></button>
    </p>
    <?php
    return ob_get_clean() . $content;
}, 9 );

/* ════════════════════════════════════════════════════════════════ */
/*  0199 · Audio · browser SpeechSynthesis · no server, no tracking  */
/* ════════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', function () {
    if ( ! is_singular( [ 'vpg_magazine', 'post', 'vpg_tutorial' ] ) ) return;
    ?>
    <script>
    (function () {
      if (!('speechSynthesis' in window)) {
        document.querySelectorAll('.vpg-listen').forEach(function (b) { b.hidden = true; });
        return;
      }
      var LBL = { listen: '🔊 <?php echo esc_js( __( 'Listen', 'vpg-v2' ) ); ?>', stop: '⏹ <?php echo esc_js( __( 'Stop', 'vpg-v2' ) ); ?>', pause: '⏸ <?php echo esc_js( __( 'Pause', 'vpg-v2' ) ); ?>', resume: '▶ <?php echo esc_js( __( 'Resume', 'vpg-v2' ) ); ?>' };
      var speaking = false, els = [], offsets = [], hl = null;
      function collect() {
        var main = document.getElementById('vpg-main') || document.body;
        els = []; offsets = []; var parts = [], pos = 0;
        main.querySelectorAll('h1, h2, p, li, figcaption').forEach(function (el) {
          if (el.closest('.g-foot, nav, form')) return;
          var t = el.textContent.trim();
          if (t) { els.push(el); offsets.push(pos); parts.push(t); pos += t.length + 2; }
        });
        return parts.join('. ');
      }
      function clearHL() { if (hl) { hl.style.background = ''; hl = null; } }
      // 1038 · highlight the sentence/block currently being read
      function highlightAt(idx) {
        var i = 0; for (var k = 0; k < offsets.length; k++) { if (offsets[k] <= idx) i = k; else break; }
        if (els[i] && els[i] !== hl) { clearHL(); hl = els[i]; hl.style.background = 'rgba(229,52,31,.14)'; }
      }
      document.querySelectorAll('.vpg-listen').forEach(function (btn) {
        // 1038 · a Pause/Resume button alongside the Listen/Stop toggle
        var pauseBtn = document.createElement('button');
        pauseBtn.type = 'button'; pauseBtn.className = btn.className; pauseBtn.style.cssText = btn.style.cssText; pauseBtn.hidden = true;
        pauseBtn.textContent = LBL.pause; pauseBtn.style.marginLeft = '6px';
        btn.after(pauseBtn);
        function reset() { speaking = false; clearHL(); btn.textContent = LBL.listen; pauseBtn.hidden = true; pauseBtn.textContent = LBL.pause; }
        btn.addEventListener('click', function () {
          if (speaking) { speechSynthesis.cancel(); reset(); return; }
          var u = new SpeechSynthesisUtterance(collect());
          u.lang = (document.documentElement.lang || 'en'); u.rate = 0.98;
          u.onboundary = function (e) { if (e.name === 'sentence' || e.charIndex != null) highlightAt(e.charIndex); };
          u.onend = reset;
          speechSynthesis.cancel(); speechSynthesis.speak(u);
          speaking = true; btn.textContent = LBL.stop; pauseBtn.hidden = false;
        });
        pauseBtn.addEventListener('click', function () {
          if (speechSynthesis.paused) { speechSynthesis.resume(); pauseBtn.textContent = LBL.pause; }
          else { speechSynthesis.pause(); pauseBtn.textContent = LBL.resume; }
        });
      });
    })();
    </script>
    <?php
} );

/* Add a Listen button on journal + tutorial singles too */
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( [ 'post', 'vpg_tutorial' ] ) || ! in_the_loop() || ! is_main_query() ) return $content;
    return '<p style="margin:0 0 20px"><button type="button" class="g-btn g-btn--ghost vpg-listen" style="font-size:12px">🔊 ' . esc_html__( 'Listen', 'vpg-v2' ) . '</button></p>' . $content;
}, 7 );

/* ════════════════════════════════════════════════════════════════ */
/*  0202 · Pocket zine · 8 panels on one A4, folded to A6            */
/*  0201 · Annual · a year of issues in one PDF                       */
/*  Both reuse the mPDF stack the issue PDF already ships.            */
/* ════════════════════════════════════════════════════════════════ */
function vpg_mpdf_ready() {
    return file_exists( VPG_V2_DIR . '/vendor/autoload.php' );
}

add_action( 'admin_post_vpg_zine', function () {
    $issue_id = (int) ( $_GET['issue'] ?? 0 );
    $issue    = $issue_id ? get_post( $issue_id ) : null;
    if ( ! $issue || $issue->post_type !== 'vpg_magazine' ) wp_die( 'Issue not found' );
    if ( ! vpg_mpdf_ready() ) wp_safe_redirect( get_permalink( $issue_id ) ); // graceful
    if ( ! vpg_mpdf_ready() ) exit;
    require_once VPG_V2_DIR . '/vendor/autoload.php';

    $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $issue_id ) : [];
    $cover_id = (int) get_post_thumbnail_id( $issue_id );
    $cover    = $cover_id ? wp_get_attachment_image_url( $cover_id, 'large' ) : '';

    // Eight A6 panels · cover + up to 6 excerpts + colophon
    $panels   = [];
    $panels[] = '<div class="p cover">' . ( $cover ? '<img src="' . esc_url( $cover ) . '">' : '' ) . '<h1>' . esc_html( $issue->post_title ) . '</h1><p class="k">' . esc_html( get_bloginfo( 'name' ) ) . '</p></div>';
    foreach ( array_slice( $articles, 0, 6 ) as $a ) {
        $panels[] = '<div class="p"><h2>' . esc_html( $a['title'] ?? '' ) . '</h2><p class="by">' . esc_html( $a['author'] ?? '' ) . '</p><p>' . esc_html( wp_trim_words( wp_strip_all_tags( $a['body'] ?? '' ), 55 ) ) . '</p></div>';
    }
    while ( count( $panels ) < 7 ) $panels[] = '<div class="p"></div>';
    $panels[] = '<div class="p end"><p class="k">' . esc_html__( 'Fold along the lines · a pocket edition.', 'vpg-v2' ) . '</p><p>viennaphotogroup.com</p></div>';

    $css = 'body{font-family:sans-serif;margin:0}.sheet{display:table;width:100%}.row{display:table-row}.p{display:table-cell;width:25%;height:105mm;border:0.2pt dashed #bbb;padding:6mm;vertical-align:top;font-size:8pt;line-height:1.4}.p h1{font-size:13pt}.p h2{font-size:10pt;margin:0 0 2mm}.p .by{color:#666;font-style:italic;margin:0 0 3mm}.p .k{font-size:6.5pt;letter-spacing:1.5pt;text-transform:uppercase;color:#E5341F}.cover img,.p img{max-width:100%;max-height:40mm}';
    // Two rows of four panels each, filling an A4 landscape sheet.
    $sheet = '<div class="sheet"><div class="row">' . implode( '', array_slice( $panels, 0, 4 ) ) . '</div><div class="row">' . implode( '', array_slice( $panels, 4, 4 ) ) . '</div></div>';

    $mpdf = new \Mpdf\Mpdf( [ 'mode' => 'utf-8', 'format' => 'A4-L', 'margin_left' => 6, 'margin_right' => 6, 'margin_top' => 6, 'margin_bottom' => 6, 'tempDir' => wp_upload_dir()['basedir'] . '/vpg-pdf/tmp' ] );
    @wp_mkdir_p( wp_upload_dir()['basedir'] . '/vpg-pdf/tmp' );
    $mpdf->WriteHTML( $css, \Mpdf\HTMLParserMode::HEADER_CSS );
    $mpdf->WriteHTML( $sheet, \Mpdf\HTMLParserMode::HTML_BODY );
    $mpdf->Output( sanitize_file_name( $issue->post_title ) . '-zine.pdf', \Mpdf\Output\Destination::DOWNLOAD );
    exit;
} );

add_action( 'admin_post_vpg_annual', function () {
    if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
    check_admin_referer( 'vpg_annual' );
    $year = (int) ( $_GET['year'] ?? gmdate( 'Y' ) );
    if ( ! vpg_mpdf_ready() ) wp_die( 'mPDF not installed (composer install).' );
    require_once VPG_V2_DIR . '/vendor/autoload.php';

    $issues = get_posts( [ 'post_type' => 'vpg_magazine', 'post_status' => 'publish', 'posts_per_page' => 24, 'orderby' => 'date', 'order' => 'ASC', 'date_query' => [ [ 'year' => $year ] ] ] );
    if ( ! $issues ) wp_die( 'No published issues that year.' );

    $mpdf = new \Mpdf\Mpdf( [ 'mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 18, 'margin_bottom' => 20, 'tempDir' => wp_upload_dir()['basedir'] . '/vpg-pdf/tmp' ] );
    @wp_mkdir_p( wp_upload_dir()['basedir'] . '/vpg-pdf/tmp' );
    $mpdf->SetTitle( sprintf( __( 'Vienna Photo Group — the year %d', 'vpg-v2' ), $year ) );

    // 1035 · an optional chosen cover image for the bound year
    $cover_img = '';
    $cover_id  = (int) get_option( 'vpg_annual_cover_' . $year, 0 );
    if ( $cover_id && ( $src = wp_get_attachment_image_url( $cover_id, 'full' ) ) ) {
        $cover_img = '<div style="text-align:center;margin-bottom:8mm"><img src="' . esc_url( $src ) . '" style="max-width:120mm;max-height:130mm"></div>';
    }
    $cover = '<div style="text-align:center;padding-top:' . ( $cover_img ? '20mm' : '70mm' ) . '">' . $cover_img . '<p style="font-size:9pt;letter-spacing:4pt;text-transform:uppercase;color:#E5341F">' . esc_html( get_bloginfo( 'name' ) ) . '</p><h1 style="font-size:64pt;margin:6mm 0">' . (int) $year . '<span style="color:#E5341F">.</span></h1><p style="color:#666">' . esc_html( sprintf( _n( '%d issue', '%d issues', count( $issues ), 'vpg-v2' ), count( $issues ) ) ) . '</p></div>';
    $mpdf->WriteHTML( $cover, \Mpdf\HTMLParserMode::HTML_BODY );

    foreach ( $issues as $iss ) {
        $mpdf->AddPage();
        $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $iss->ID ) : [];
        $mpdf->Bookmark( $iss->post_title, 0 );
        $html = '<p style="font-size:8pt;letter-spacing:2pt;text-transform:uppercase;color:#E5341F">' . esc_html( get_the_date( 'F Y', $iss ) ) . '</p><h1 style="font-size:30pt;margin:2mm 0 6mm">' . esc_html( $iss->post_title ) . '</h1>';
        $ed = get_post_meta( $iss->ID, '_vpg_editorial', true );
        if ( $ed ) $html .= '<div style="font-style:italic;color:#333">' . wp_kses_post( wpautop( $ed ) ) . '</div>';
        foreach ( $articles as $a ) {
            $html .= '<h2 style="font-size:14pt;margin:6mm 0 1mm">' . esc_html( $a['title'] ?? '' ) . '</h2>';
            if ( ! empty( $a['author'] ) ) $html .= '<p style="font-size:8pt;color:#666;margin:0 0 2mm">' . esc_html( $a['author'] ) . '</p>';
            $html .= '<div>' . wp_kses_post( $a['body'] ?? '' ) . '</div>';
        }
        $mpdf->WriteHTML( $html, \Mpdf\HTMLParserMode::HTML_BODY );
    }
    $mpdf->Output( 'vpg-annual-' . $year . '.pdf', \Mpdf\Output\Destination::DOWNLOAD );
    exit;
} );

/* Annual builder link in the magazine admin cluster */
add_action( 'admin_menu', function () {
    add_submenu_page( 'vpg-magazine', __( 'Annual (PDF)', 'vpg-v2' ), __( '📚 Annual (PDF)', 'vpg-v2' ), 'edit_others_posts', 'vpg-annual', function () {
        if ( ! current_user_can( 'edit_others_posts' ) ) wp_die( 'Forbidden' );
        // 1035 · save a chosen cover (attachment ID) per year
        if ( isset( $_POST['_vpg_annual_cover'] ) && wp_verify_nonce( $_POST['_vpg_annual_cover'], 'vpg_annual_cover' ) ) {
            $y = (int) $_POST['cover_year']; $cid = (int) $_POST['cover_id'];
            $cid ? update_option( 'vpg_annual_cover_' . $y, $cid ) : delete_option( 'vpg_annual_cover_' . $y );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Cover saved.', 'vpg-v2' ) . '</p></div>';
        }
        $years = $GLOBALS['wpdb']->get_col( "SELECT DISTINCT YEAR(post_date) FROM {$GLOBALS['wpdb']->posts} WHERE post_type='vpg_magazine' AND post_status='publish' ORDER BY 1 DESC" );
        echo '<div class="wrap"><h1>📚 ' . esc_html__( 'Annual (PDF)', 'vpg-v2' ) . '</h1><p class="description">' . esc_html__( 'Bind a year of published issues — cover to colophon — into one PDF book.', 'vpg-v2' ) . '</p><ul>';
        if ( ! $years ) echo '<li>' . esc_html__( 'No published issues yet.', 'vpg-v2' ) . '</li>';
        foreach ( $years as $y ) {
            $url = wp_nonce_url( admin_url( 'admin-post.php?action=vpg_annual&year=' . (int) $y ), 'vpg_annual' );
            $cid = (int) get_option( 'vpg_annual_cover_' . (int) $y, 0 );
            echo '<li style="margin:10px 0"><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html( sprintf( __( 'Build %d annual →', 'vpg-v2' ), (int) $y ) ) . '</a> ';
            echo '<form method="post" style="display:inline-flex;gap:6px;align-items:center;margin-left:8px">' . wp_nonce_field( 'vpg_annual_cover', '_vpg_annual_cover', true, false );
            echo '<input type="hidden" name="cover_year" value="' . (int) $y . '"><input type="number" name="cover_id" value="' . ( $cid ?: '' ) . '" placeholder="' . esc_attr__( 'cover image ID', 'vpg-v2' ) . '" style="width:120px"> <button class="button">' . esc_html__( 'Set cover', 'vpg-v2' ) . '</button>';
            if ( $cid ) echo ' ' . wp_get_attachment_image( $cid, [ 34, 34 ] );
            echo '</form></li>';
        }
        echo '</ul><p class="description">' . esc_html__( 'The cover image ID is the attachment ID from the media library (shown in its URL).', 'vpg-v2' ) . '</p></div>';
    } );
}, 14 );
