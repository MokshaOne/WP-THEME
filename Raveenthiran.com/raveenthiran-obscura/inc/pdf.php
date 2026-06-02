<?php
/**
 * #70 — Quote → branded PDF estimate.
 *
 * A tiny, dependency-free single-page PDF writer (uses the PDF standard
 * Helvetica fonts, so nothing is embedded and it runs on any shared host).
 * When an enquiry arrives with a calculated estimate, functions.php calls
 * nr_quote_pdf_attach() to generate a branded, non-binding estimate and
 * attach it to the auto-reply the visitor receives.
 *
 * Document is paper-on-light (printer-friendly) with the studio's accent.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build the estimate PDF, write it to a temp file, and return its path
 * (or '' on failure). Caller is responsible for unlinking after wp_mail().
 */
function nr_quote_pdf_file( $args ) {
	$a = wp_parse_args( $args, [
		'name'     => '',
		'type'     => '',
		'date'     => '',
		'estimate' => '',
		'ref'      => '',
	] );

	$studio  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$mark    = (string) nr_opt( 'nr_logo_text', 'raveenthiran' );
	$addr    = (string) nr_opt( 'nr_studio', '' );
	$email   = (string) nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$accent  = nr_hex_rgb01( nr_opt( 'nr_accent', '#F2A03D' ) );

	$pdf = new NR_PDF();
	$ink   = [ 0.043, 0.047, 0.063 ];   // #0B0C10
	$muted = [ 0.40, 0.40, 0.38 ];

	// Header
	$pdf->text( 72, 770, strtoupper( $mark ), 'B', 22, $ink );
	$pdf->text( 523, 772, 'ESTIMATE', 'B', 11, $muted, 'right' );
	$pdf->rect( 72, 752, 451, 2, $accent );

	// Meta block
	$y = 712;
	$rows = [
		[ __( 'Prepared for', 'raveenthiran' ), $a['name'] ?: '—' ],
		[ __( 'Date', 'raveenthiran' ),         $a['date'] ?: date_i18n( 'j M Y' ) ],
		[ __( 'Project type', 'raveenthiran' ), $a['type'] ?: '—' ],
	];
	if ( $a['ref'] ) $rows[] = [ __( 'Reference', 'raveenthiran' ), $a['ref'] ];
	foreach ( $rows as $r ) {
		$pdf->text( 72, $y, strtoupper( $r[0] ), 'B', 9, $muted );
		$pdf->text( 200, $y, (string) $r[1], '', 12, $ink );
		$y -= 26;
	}

	// Headline amount
	$pdf->text( 72, $y - 36, __( 'Estimated investment', 'raveenthiran' ), 'B', 10, $muted );
	$pdf->text( 72, $y - 86, $a['estimate'] ?: '—', 'B', 44, $ink );

	// Note (wrapped)
	$note = __( 'This is a non-binding estimate based on the brief you submitted. The final quote is confirmed after a short call to align on scope, locations, usage rights, and delivery. Valid for 30 days.', 'raveenthiran' );
	$ny = $y - 140;
	foreach ( nr_pdf_wrap( $note, 92 ) as $line ) {
		$pdf->text( 72, $ny, $line, '', 11, $muted );
		$ny -= 17;
	}

	// Footer
	$pdf->rect( 72, 96, 451, 1, [ 0.85, 0.84, 0.80 ] );
	$pdf->text( 72, 78, $studio, 'B', 9, $ink );
	if ( $addr )  $pdf->text( 72, 64, $addr, '', 9, $muted );
	if ( $email ) $pdf->text( 72, 50, $email, '', 9, $muted );

	$bytes = $pdf->output();
	if ( ! $bytes ) return '';

	$tmp = wp_tempnam( 'nr-estimate.pdf' );
	if ( ! $tmp ) return '';
	$path = $tmp . '.pdf';
	if ( ! @rename( $tmp, $path ) ) { $path = $tmp; }
	if ( @file_put_contents( $path, $bytes ) === false ) { @unlink( $path ); return ''; }
	return $path;
}

/* rough greedy wrap by character count (Helvetica ~0.5em average) */
function nr_pdf_wrap( $text, $max ) {
	$out = []; $cur = '';
	foreach ( preg_split( '/\s+/', trim( $text ) ) as $w ) {
		$try = $cur === '' ? $w : $cur . ' ' . $w;
		if ( strlen( $try ) > $max && $cur !== '' ) { $out[] = $cur; $cur = $w; }
		else { $cur = $try; }
	}
	if ( $cur !== '' ) $out[] = $cur;
	return $out;
}

function nr_hex_rgb01( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( strlen( $hex ) === 3 ) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) $hex = 'F2A03D';
	return [ hexdec( substr( $hex, 0, 2 ) ) / 255, hexdec( substr( $hex, 2, 2 ) ) / 255, hexdec( substr( $hex, 4, 2 ) ) / 255 ];
}

/**
 * Minimal single-page A4 PDF builder (Helvetica / Helvetica-Bold).
 */
class NR_PDF {
	private $ops = [];

	private function esc( $s ) {
		$s = wp_specialchars_decode( (string) $s, ENT_QUOTES );
		// PDF WinAnsi: keep it ASCII-safe; translate common accents conservatively.
		$s = remove_accents( $s );
		return str_replace( [ '\\', '(', ')' ], [ '\\\\', '\\(', '\\)' ], $s );
	}

	public function text( $x, $y, $str, $weight = '', $size = 12, $rgb = [ 0, 0, 0 ], $align = 'left' ) {
		$font = $weight === 'B' ? '/F2' : '/F1';
		if ( $align === 'right' ) $x -= $this->width( $str, $size, $weight );
		$this->ops[] = sprintf( '%.3f %.3f %.3f rg', $rgb[0], $rgb[1], $rgb[2] );
		$this->ops[] = sprintf( 'BT %s %d Tf %.2f %.2f Td (%s) Tj ET', $font, $size, $x, $y, $this->esc( $str ) );
	}

	public function rect( $x, $y, $w, $h, $rgb ) {
		$this->ops[] = sprintf( '%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f', $rgb[0], $rgb[1], $rgb[2], $x, $y, $w, $h );
	}

	/* crude Helvetica advance for right-alignment (avg 0.52em) */
	private function width( $str, $size, $weight ) {
		return strlen( remove_accents( (string) $str ) ) * $size * ( $weight === 'B' ? 0.56 : 0.52 );
	}

	public function output() {
		$content = implode( "\n", $this->ops );
		$objs = [];
		$objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
		$objs[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
		$objs[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
			. '/Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>';
		$objs[4] = "<< /Length " . strlen( $content ) . " >>\nstream\n" . $content . "\nendstream";
		$objs[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
		$objs[6] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

		$pdf = "%PDF-1.4\n";
		$offsets = [];
		foreach ( $objs as $n => $body ) {
			$offsets[ $n ] = strlen( $pdf );
			$pdf .= $n . " 0 obj\n" . $body . "\nendobj\n";
		}
		$xref_pos = strlen( $pdf );
		$count    = count( $objs ) + 1;
		$pdf .= "xref\n0 " . $count . "\n";
		$pdf .= "0000000000 65535 f \n";
		for ( $i = 1; $i < $count; $i++ ) {
			$pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
		}
		$pdf .= "trailer\n<< /Size " . $count . " /Root 1 0 R >>\n";
		$pdf .= "startxref\n" . $xref_pos . "\n%%EOF";
		return $pdf;
	}
}
