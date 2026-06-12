<?php
/**
 * Invoicing — no payment processing, just proper invoices (bank transfer).
 *
 * Austrian Kleinunternehmer setup: no VAT line, mandatory exemption note
 * (§ 6 Abs. 1 Z 27 UStG — the wording is a Theme Setting).
 *
 * Flow (MANUAL): every enquiry gets an Invoice meta box — the owner reviews
 * the booking, sets/confirms the amount (pre-filled from the estimate when
 * present) and clicks "Create + send": gap-free sequential number, PDF via
 * the NR_PDF writer (inc/pdf.php), emailed to the client. Download + re-send
 * any time. One invoice per enquiry (idempotent).
 *
 * PDFs are stored in uploads/nr-invoices/ behind an .htaccess deny; the owner
 * downloads via a capability-checked admin endpoint, the client receives the
 * PDF as an email attachment (no public URL exists).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Config helpers ────────────────────────────────────────────────────── */

/** True when the legally required sender details are configured. */
function nr_invoice_ready() {
	return trim( (string) nr_opt( 'nr_inv_business', '' ) ) !== ''
		&& trim( (string) nr_opt( 'nr_inv_iban', '' ) ) !== '';
}

/** Parse "€1,200" / "EUR 1200.50" → float (0.0 when no usable number). */
function nr_invoice_amount( $est ) {
	$n = preg_replace( '/[^\d.,]/', '', (string) $est );
	if ( $n === '' ) return 0.0;
	// 1.200,50 → 1200.50 · 1,200.50 → 1200.50 · 1200,5 → 1200.5
	if ( preg_match( '/,\d{1,2}$/', $n ) ) { $n = str_replace( '.', '', $n ); $n = str_replace( ',', '.', $n ); }
	else { $n = str_replace( ',', '', $n ); }
	return max( 0.0, (float) $n );
}

/** Next gap-free invoice number, e.g. "2026-0001". Increments the counter. */
function nr_invoice_next_no() {
	$prefix = trim( (string) nr_opt( 'nr_inv_prefix', '' ) );
	if ( $prefix === '' ) $prefix = date_i18n( 'Y' );
	$n = max( 1, (int) get_option( 'nr_inv_next', 1 ) );
	update_option( 'nr_inv_next', $n + 1, false );
	return $prefix . '-' . str_pad( (string) $n, 4, '0', STR_PAD_LEFT );
}

/** Private storage dir for invoice PDFs (created on demand, access denied). */
function nr_invoice_dir() {
	$up  = wp_get_upload_dir();
	$dir = trailingslashit( $up['basedir'] ) . 'nr-invoices';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		@file_put_contents( $dir . '/.htaccess', "Deny from all\n" );
		@file_put_contents( $dir . '/index.html', '' );
	}
	return $dir;
}

/* ── PDF ───────────────────────────────────────────────────────────────── */

/**
 * Render the invoice PDF and return the file path ('' on failure).
 * $inv: no, date, due, service_date, name, email, desc, amount (float).
 */
function nr_invoice_pdf_file( $inv ) {
	if ( ! class_exists( 'NR_PDF' ) ) return '';

	$mark     = (string) nr_opt( 'nr_logo_text', 'raveenthiran' );
	$business = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) nr_opt( 'nr_inv_business', '' ) ) ) ) );
	$iban     = (string) nr_opt( 'nr_inv_iban', '' );
	$bic      = (string) nr_opt( 'nr_inv_bic', '' );
	$note     = (string) nr_opt( 'nr_inv_note', 'Umsatzsteuerfrei gemäß § 6 Abs. 1 Z 27 UStG (Kleinunternehmerregelung).' );
	$terms    = max( 1, (int) nr_opt( 'nr_inv_terms', 14 ) );
	$accent   = nr_hex_rgb01( nr_opt( 'nr_accent', '#F2A03D' ) );
	$ink      = [ 0.043, 0.047, 0.063 ];
	$muted    = [ 0.40, 0.40, 0.38 ];
	$amount   = '€' . number_format( (float) $inv['amount'], 2, '.', ',' );

	$pdf = new NR_PDF();

	// Header
	$pdf->text( 72, 770, strtoupper( $mark ), 'B', 22, $ink );
	$pdf->text( 523, 772, 'INVOICE', 'B', 11, $muted, 'right' );
	$pdf->rect( 72, 752, 451, 2, $accent );

	// Sender block (right) + bill-to (left)
	$y = 716;
	$pdf->text( 72, $y, 'BILLED TO', 'B', 9, $muted );
	$pdf->text( 72, $y - 16, (string) $inv['name'], '', 11, $ink );
	if ( ! empty( $inv['email'] ) ) $pdf->text( 72, $y - 31, (string) $inv['email'], '', 10, $muted );
	$by = $y;
	$pdf->text( 523, $by, 'FROM', 'B', 9, $muted, 'right' );
	$by -= 16;
	foreach ( array_slice( $business, 0, 4 ) as $line ) {
		$pdf->text( 523, $by, $line, '', 10, $ink, 'right' );
		$by -= 14;
	}

	// Meta rows
	$y = 640;
	foreach ( [
		[ 'Invoice no.',  $inv['no'] ],
		[ 'Invoice date', $inv['date'] ],
		[ 'Service date', $inv['service_date'] ],
		[ 'Due date',     $inv['due'] ],
	] as $r ) {
		$pdf->text( 72, $y, strtoupper( $r[0] ), 'B', 9, $muted );
		$pdf->text( 200, $y, (string) $r[1], '', 11, $ink );
		$y -= 22;
	}

	// Line items — the whole calculation when available, else one summary line.
	$y -= 18;
	$pdf->rect( 72, $y + 12, 451, 1, [ 0.85, 0.84, 0.80 ] );
	$items = ! empty( $inv['items'] ) && is_array( $inv['items'] ) ? $inv['items'] : [];
	if ( $items ) {
		foreach ( $items as $it ) {
			$pdf->text( 72, $y - 8, (string) $it[0], '', 11, $ink );
			$pdf->text( 523, $y - 8, '€' . number_format( (float) $it[1], 2, '.', ',' ), '', 11, $ink, 'right' );
			$y -= 20;
		}
	} else {
		$pdf->text( 72, $y - 8, (string) $inv['desc'], '', 11, $ink );
		$pdf->text( 523, $y - 8, $amount, '', 11, $ink, 'right' );
		$y -= 20;
	}
	$pdf->rect( 72, $y - 4, 451, 1, [ 0.85, 0.84, 0.80 ] );

	// Total
	$pdf->text( 72, $y - 44, 'TOTAL DUE', 'B', 10, $muted );
	$pdf->text( 72, $y - 86, $amount, 'B', 38, $ink );
	$y -= 0;

	// Legal note (Kleinunternehmer)
	$ny = $y - 122;
	foreach ( nr_pdf_wrap( $note, 92 ) as $line ) {
		$pdf->text( 72, $ny, $line, '', 10, $muted );
		$ny -= 15;
	}

	// Payment block
	$ny -= 18;
	$pdf->text( 72, $ny, 'PAYMENT', 'B', 9, $muted );
	$ny -= 16;
	$pdf->text( 72, $ny, sprintf( 'By bank transfer within %d days.', $terms ), '', 10, $ink );
	$ny -= 15;
	if ( $iban ) { $pdf->text( 72, $ny, 'IBAN  ' . $iban . ( $bic ? '   ·   BIC  ' . $bic : '' ), '', 10, $ink ); $ny -= 15; }
	$pdf->text( 72, $ny, 'Reference  ' . $inv['no'], '', 10, $ink );

	// Footer
	$pdf->rect( 72, 96, 451, 1, [ 0.85, 0.84, 0.80 ] );
	$pdf->text( 72, 78, implode( ' · ', array_slice( $business, 0, 2 ) ), '', 9, $muted );
	$email = (string) nr_opt( 'nr_email', get_option( 'admin_email' ) );
	if ( $email ) $pdf->text( 72, 64, $email, '', 9, $muted );

	$bytes = $pdf->output();
	if ( ! $bytes ) return '';

	$dir  = trailingslashit( nr_invoice_dir() );
	$name = sanitize_file_name( 'Invoice-' . $inv['no'] . '-' . wp_generate_password( 8, false ) . '.pdf' );
	$path = $dir . $name;
	if ( @file_put_contents( $path, $bytes ) === false ) return '';
	return $path;
}

/* ── Create + send ─────────────────────────────────────────────────────── */

/**
 * Create the invoice for an enquiry (idempotent). Returns invoice meta array
 * or WP_Error. $amount_override lets the manual path set a custom amount.
 */
function nr_invoice_create( $eid, $amount_override = null ) {
	$eid = (int) $eid;
	if ( get_post_type( $eid ) !== 'nr_enquiry' ) return new WP_Error( 'nr_inv', 'Not an enquiry.' );
	if ( get_post_meta( $eid, '_nr_inv_no', true ) ) return new WP_Error( 'nr_inv', 'Already invoiced.' );
	if ( ! nr_invoice_ready() ) return new WP_Error( 'nr_inv', 'Business details / IBAN missing in Settings.' );

	$amount = $amount_override !== null ? (float) $amount_override : nr_invoice_amount( get_post_meta( $eid, '_nr_est', true ) );
	if ( $amount <= 0 ) return new WP_Error( 'nr_inv', 'No usable amount.' );

	$type  = (string) get_post_meta( $eid, '_nr_type', true );
	$sdate = (string) get_post_meta( $eid, '_nr_date', true );
	$terms = max( 1, (int) nr_opt( 'nr_inv_terms', 14 ) );

	// Whole calculation from the quote (label | amount ;; …). Used as invoice
	// line items only when it still sums to the billed amount (the owner may
	// have overridden the amount — then a single summary line is correct).
	$items = function_exists( 'nr_breakdown_items' ) ? nr_breakdown_items( get_post_meta( $eid, '_nr_breakdown', true ) ) : [];
	if ( $items ) {
		$sum = 0.0;
		foreach ( $items as $it ) $sum += $it[1];
		if ( abs( $sum - $amount ) > 0.01 ) $items = [];
	}

	$inv = [
		'no'           => nr_invoice_next_no(),
		'date'         => date_i18n( 'j M Y' ),
		'due'          => date_i18n( 'j M Y', time() + $terms * DAY_IN_SECONDS ),
		'service_date' => $sdate ?: date_i18n( 'j M Y' ),
		'name'         => get_the_title( $eid ),
		'email'        => (string) get_post_meta( $eid, '_nr_email', true ),
		'desc'         => trim( ( $type ?: 'Photography' ) . ' — photography session' ),
		'amount'       => $amount,
		'items'        => $items,
	];

	$path = nr_invoice_pdf_file( $inv );
	if ( ! $path ) return new WP_Error( 'nr_inv', 'PDF generation failed.' );

	update_post_meta( $eid, '_nr_inv_no',     $inv['no'] );
	update_post_meta( $eid, '_nr_inv_date',   $inv['date'] );
	update_post_meta( $eid, '_nr_inv_amount', $amount );
	update_post_meta( $eid, '_nr_inv_path',   $path );
	return $inv;
}

/** Email the stored invoice PDF to the client. Returns bool. */
function nr_invoice_send( $eid ) {
	$eid   = (int) $eid;
	$no    = (string) get_post_meta( $eid, '_nr_inv_no', true );
	$path  = (string) get_post_meta( $eid, '_nr_inv_path', true );
	$email = (string) get_post_meta( $eid, '_nr_email', true );
	if ( ! $no || ! $path || ! file_exists( $path ) || ! is_email( $email ) ) return false;

	$site   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$terms  = max( 1, (int) nr_opt( 'nr_inv_terms', 14 ) );
	$amount = '€' . number_format( (float) get_post_meta( $eid, '_nr_inv_amount', true ), 2, '.', ',' );
	$name   = get_the_title( $eid );

	$subject = sprintf( __( 'Invoice %1$s — %2$s', 'raveenthiran' ), $no, $site );
	$body    = sprintf(
		/* translators: 1: name, 2: invoice no, 3: amount, 4: days, 5: studio */
		__( "Hi %1\$s,\n\nthank you for your booking. Please find invoice %2\$s (%3\$s) attached.\n\nPayment is by bank transfer to the account stated on the invoice, within %4\$d days, using the invoice number as reference.\n\nLooking forward to the shoot!\n\n— %5\$s", 'raveenthiran' ),
		$name ?: __( 'there', 'raveenthiran' ), $no, $amount, $terms, $site
	);
	$from    = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $from ];
	$ok      = wp_mail( $email, $subject, $body, $headers, [ $path ] );
	if ( $ok ) update_post_meta( $eid, '_nr_inv_sent', current_time( 'mysql' ) );
	return (bool) $ok;
}

/* ── Admin: meta box on enquiries (status · download · resend · create) ── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nr-invoice', __( 'Invoice', 'raveenthiran' ), 'nr_invoice_metabox', 'nr_enquiry', 'side', 'high' );
} );

function nr_invoice_metabox( $post ) {
	$no = (string) get_post_meta( $post->ID, '_nr_inv_no', true );
	if ( $no ) {
		$sent = (string) get_post_meta( $post->ID, '_nr_inv_sent', true );
		$amt  = '€' . number_format( (float) get_post_meta( $post->ID, '_nr_inv_amount', true ), 2, '.', ',' );
		echo '<p><strong>' . esc_html( $no ) . '</strong> · ' . esc_html( $amt ) . '<br>'
			. esc_html( (string) get_post_meta( $post->ID, '_nr_inv_date', true ) )
			. ( $sent ? '<br>' . esc_html__( 'Sent:', 'raveenthiran' ) . ' ' . esc_html( $sent ) : '<br>' . esc_html__( 'Not sent yet.', 'raveenthiran' ) )
			. '</p>';
		$dl = wp_nonce_url( admin_url( 'admin-post.php?action=nr_inv_dl&eid=' . $post->ID ), 'nr_inv_' . $post->ID );
		$rs = wp_nonce_url( admin_url( 'admin-post.php?action=nr_inv_send&eid=' . $post->ID ), 'nr_inv_' . $post->ID );
		echo '<p><a class="button" href="' . esc_url( $dl ) . '">' . esc_html__( 'Download PDF', 'raveenthiran' ) . '</a> '
			. '<a class="button" href="' . esc_url( $rs ) . '">' . esc_html__( 'Send again', 'raveenthiran' ) . '</a></p>';
	} elseif ( ! nr_invoice_ready() ) {
		echo '<p>' . esc_html__( 'Fill in business details + IBAN under Obscura → Settings → Invoices first.', 'raveenthiran' ) . '</p>';
	} else {
		$est = nr_invoice_amount( get_post_meta( $post->ID, '_nr_est', true ) );
		$mk  = wp_nonce_url( admin_url( 'admin-post.php?action=nr_inv_make&eid=' . $post->ID ), 'nr_inv_' . $post->ID );
		echo '<p>' . esc_html__( 'No invoice yet.', 'raveenthiran' ) . '</p>';
		echo '<form method="get" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
			. '<input type="hidden" name="action" value="nr_inv_make">'
			. '<input type="hidden" name="eid" value="' . (int) $post->ID . '">'
			. wp_nonce_field( 'nr_inv_' . $post->ID, '_wpnonce', true, false )
			. '<p><label>' . esc_html__( 'Amount (€)', 'raveenthiran' ) . ' '
			. '<input type="number" step="0.01" min="1" name="amount" value="' . esc_attr( $est > 0 ? $est : '' ) . '" style="width:90px"></label> '
			. '<button class="button button-primary">' . esc_html__( 'Create + send', 'raveenthiran' ) . '</button></p></form>';
	}
}

/* Admin endpoints: download / resend / create. */
add_action( 'admin_post_nr_inv_dl', function () {
	$eid = (int) ( $_GET['eid'] ?? 0 );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_inv_' . $eid ) ) wp_die( 'Denied.' );
	$path = (string) get_post_meta( $eid, '_nr_inv_path', true );
	if ( ! $path || ! file_exists( $path ) ) wp_die( 'File missing.' );
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="Invoice-' . sanitize_file_name( (string) get_post_meta( $eid, '_nr_inv_no', true ) ) . '.pdf"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

add_action( 'admin_post_nr_inv_send', function () {
	$eid = (int) ( $_GET['eid'] ?? 0 );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_inv_' . $eid ) ) wp_die( 'Denied.' );
	nr_invoice_send( $eid );
	wp_safe_redirect( get_edit_post_link( $eid, 'url' ) ?: admin_url() );
	exit;
} );

add_action( 'admin_post_nr_inv_make', function () {
	$eid = (int) ( $_GET['eid'] ?? 0 );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_inv_' . $eid ) ) wp_die( 'Denied.' );
	$amount = isset( $_GET['amount'] ) && $_GET['amount'] !== '' ? (float) $_GET['amount'] : null;
	$inv = nr_invoice_create( $eid, $amount );
	if ( ! is_wp_error( $inv ) ) nr_invoice_send( $eid );
	wp_safe_redirect( get_edit_post_link( $eid, 'url' ) ?: admin_url() );
	exit;
} );
