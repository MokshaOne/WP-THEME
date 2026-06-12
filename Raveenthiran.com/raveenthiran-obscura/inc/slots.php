<?php
/**
 * Native booking — self-hosted time-slot booking, no third party, no payment.
 *
 * The owner creates open time slots (Obscura → Booking). A visitor picks a free
 * slot + name/email on the [nr_booking_slots] block and it's booked instantly:
 *   • the slot is marked booked (re-checked server-side → no double-booking),
 *   • the booking is logged as a normal nr_enquiry (so the Enquiry-details box,
 *     invoicing etc. all apply — _nr_type = "Booking", _nr_date = the slot),
 *   • owner + client get a confirmation email; the client's one carries an
 *     .ics calendar invite (Apple / Google / Outlook).
 *
 * Plugin-light, DSGVO-clean (data stays in WordPress), Turnstile-guarded when
 * configured. Times use the site timezone; .ics is emitted in UTC.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── CPT: one post per bookable slot ───────────────────────────────────── */
add_action( 'init', function () {
	register_post_type( 'nr_slot', [
		'label'           => __( 'Booking slots', 'raveenthiran' ),
		'public'          => false,
		'show_ui'         => false,            // managed via the Obscura → Booking page
		'show_in_menu'    => false,
		'supports'        => [ 'title' ],
		'capability_type' => 'post',
	] );
} );

/* ── Helpers ───────────────────────────────────────────────────────────── */
function nr_slot_start( $id ) { return (int) get_post_meta( $id, '_nr_slot_start', true ); }
function nr_slot_dur( $id )   { return max( 5, (int) get_post_meta( $id, '_nr_slot_dur', true ) ?: 60 ); }
function nr_slot_label( $id ) { return (string) get_post_meta( $id, '_nr_slot_label', true ); }
function nr_slot_status( $id ){ return get_post_meta( $id, '_nr_slot_status', true ) ?: 'open'; }

/** Earliest timestamp a visitor may book (now + lead-time hours from Settings). */
function nr_book_cutoff() {
	$lead = max( 0, (int) nr_opt( 'nr_book_lead', 0 ) );
	return time() + $lead * HOUR_IN_SECONDS;
}

/** Open, future slots (ascending). */
function nr_slots_open( $limit = 60 ) {
	return get_posts( [
		'post_type'      => 'nr_slot',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => '_nr_slot_status', 'value' => 'open' ],
			[ 'key' => '_nr_slot_start', 'value' => nr_book_cutoff(), 'compare' => '>=', 'type' => 'NUMERIC' ],
		],
		'meta_key'       => '_nr_slot_start',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
	] );
}

/** Format a slot for humans, e.g. "Fri 4 Jul · 14:00–15:00". */
function nr_slot_human( $id ) {
	$start = nr_slot_start( $id );
	$end   = $start + nr_slot_dur( $id ) * 60;
	$lbl   = nr_slot_label( $id );
	$txt   = wp_date( 'D j M · H:i', $start ) . '–' . wp_date( 'H:i', $end );
	return $lbl ? $lbl . ' — ' . $txt : $txt;
}

/* ── .ics calendar invite (dependency-free) ────────────────────────────── */
function nr_slot_ics_file( $id, $attendee_name, $attendee_email ) {
	$start = nr_slot_start( $id );
	if ( ! $start ) return '';
	$end   = $start + nr_slot_dur( $id ) * 60;
	$host  = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
	$studio = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$from   = (string) nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$loc    = (string) ( nr_opt( 'nr_book_location', '' ) ?: nr_opt( 'nr_studio', '' ) );
	$summary = nr_slot_label( $id ) ?: sprintf( __( 'Shoot with %s', 'raveenthiran' ), $studio );

	$esc = function ( $s ) { return str_replace( [ '\\', ';', ',', "\n" ], [ '\\\\', '\\;', '\\,', '\\n' ], wp_strip_all_tags( (string) $s ) ); };
	$utc = function ( $ts ) { return gmdate( 'Ymd\THis\Z', $ts ); };

	$lines = [
		'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//raveenthiran//booking//EN', 'METHOD:REQUEST', 'CALSCALE:GREGORIAN',
		'BEGIN:VEVENT',
		'UID:nr-slot-' . $id . '@' . $host,
		'DTSTAMP:' . $utc( time() ),
		'DTSTART:' . $utc( $start ),
		'DTEND:' . $utc( $end ),
		'SUMMARY:' . $esc( $summary ),
	];
	if ( $loc )  $lines[] = 'LOCATION:' . $esc( $loc );
	if ( $from ) $lines[] = 'ORGANIZER;CN=' . $esc( $studio ) . ':mailto:' . $from;
	if ( $attendee_email ) $lines[] = 'ATTENDEE;CN=' . $esc( $attendee_name ) . ';RSVP=TRUE:mailto:' . $attendee_email;
	$lines[] = 'STATUS:CONFIRMED';
	$lines[] = 'END:VEVENT';
	$lines[] = 'END:VCALENDAR';

	$dir  = trailingslashit( get_temp_dir() );
	$path = $dir . wp_unique_filename( $dir, 'booking.ics' );
	if ( @file_put_contents( $path, implode( "\r\n", $lines ) ) === false ) return '';
	return $path;
}

/* ── Front-end: [nr_booking_slots] (inline or popover) ─────────────────── */
add_shortcode( 'nr_booking_slots', function ( $atts ) {
	static $n = 0; $n++;
	$a = shortcode_atts( [
		'title' => __( 'Book a time', 'raveenthiran' ),
		'intro' => (string) nr_opt( 'nr_book_intro', __( 'Pick an open slot — you’ll get a confirmation with a calendar invite by email.', 'raveenthiran' ) ),
		'mode'  => 'inline',                       // 'inline' | 'popover'
		'cta'   => __( 'Book a time', 'raveenthiran' ),
	], $atts, 'nr_booking_slots' );

	$slots   = nr_slots_open();
	$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire/' );
	$popover = ( $a['mode'] === 'popover' || $a['mode'] === 'modal' );
	$id      = 'nr-booking-modal-' . $n;

	// Post-redirect notice.
	$state  = isset( $_GET['nr_booked'] ) ? sanitize_key( $_GET['nr_booked'] ) : '';
	$notice = '';
	if ( $state === '1' )         $notice = '<p class="nr-slots__ok">' . esc_html__( 'Booked — check your inbox for the confirmation and calendar invite.', 'raveenthiran' ) . '</p>';
	elseif ( $state === 'taken' ) $notice = '<p class="nr-slots__err">' . esc_html__( 'Sorry — that slot was just taken. Please pick another.', 'raveenthiran' ) . '</p>';
	elseif ( $state === '0' )     $notice = '<p class="nr-slots__err">' . esc_html__( 'Could not complete — please try again.', 'raveenthiran' ) . '</p>';
	if ( isset( $_GET['nr_cancelled'] ) ) {
		$notice .= ( $_GET['nr_cancelled'] === '1' )
			? '<p class="nr-slots__ok">' . esc_html__( 'Your booking was cancelled — that time is open again.', 'raveenthiran' ) . '</p>'
			: '<p class="nr-slots__err">' . esc_html__( 'That cancellation link is no longer valid (the booking may have already passed).', 'raveenthiran' ) . '</p>';
	}

	// The picker (form) or the empty state.
	ob_start();
	if ( ! $slots ) {
		echo '<p class="nr-slots__empty">' . esc_html__( 'No open slots right now — ', 'raveenthiran' )
			. '<a href="' . esc_url( $enquire ) . '">' . esc_html__( 'send an enquiry', 'raveenthiran' ) . '</a>'
			. esc_html__( ' and we’ll find a time.', 'raveenthiran' ) . '</p>';
	} else {
		echo '<form class="nr-slots__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="nr_book_slot">';
		echo wp_nonce_field( 'nr_book_slot', '_nr_bnonce', true, false );
		echo '<input type="text" name="nr_hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="nr-hp">';
		echo '<fieldset class="nr-slots__sets"><legend class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Available times', 'raveenthiran' ) . '</legend>';
		$cur_day = '';
		foreach ( $slots as $i => $s ) {
			$st = nr_slot_start( $s->ID ); $en = $st + nr_slot_dur( $s->ID ) * 60;
			$day = wp_date( 'l, j F', $st );
			if ( $day !== $cur_day ) {
				if ( $cur_day !== '' ) echo '</div>';
				echo '<div class="nr-slots__dayhead">' . esc_html( $day ) . '</div><div class="nr-slots__grid">';
				$cur_day = $day;
			}
			$lbl = nr_slot_label( $s->ID );
			$tt  = wp_date( 'H:i', $st ) . '–' . wp_date( 'H:i', $en );
			echo '<label class="nr-slots__slot"><input type="radio" name="slot" value="' . (int) $s->ID . '"' . checked( $i, 0, false ) . ' required aria-label="' . esc_attr( nr_slot_human( $s->ID ) ) . '"><span>' . esc_html( $lbl ? $lbl . ' · ' . $tt : $tt ) . '</span></label>';
		}
		if ( $cur_day !== '' ) echo '</div>';
		echo '</fieldset>';
		// Price calculator (optional) — same pricing source as the quote modal.
		if ( function_exists( 'nr_quote_data' ) ) {
			$q = nr_quote_data();
			if ( ! empty( $q['types'] ) ) {
				$cur = $q['currency'];
				echo '<fieldset class="nr-slots__calc" data-bk-calc data-cur="' . esc_attr( $cur ) . '"><legend class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Estimate (optional)', 'raveenthiran' ) . '</legend>';
				echo '<div class="nr-slots__types">';
				foreach ( $q['types'] as $t ) {
					echo '<label class="nr-slots__type"><input type="radio" name="nr_q_type" value="' . esc_attr( $t['slug'] ) . '" data-price="' . esc_attr( (float) $t['base'] ) . '"><span>' . esc_html( $t['label'] ) . ' · ' . esc_html( $cur . number_format_i18n( (float) $t['base'] ) ) . '</span></label>';
				}
				echo '</div>';
				if ( ! empty( $q['extras'] ) || (float) $q['license'] > 0 ) {
					echo '<div class="nr-slots__extras">';
					foreach ( (array) ( $q['extras'] ?? [] ) as $x ) {
						echo '<label class="nr-slots__extra"><input type="checkbox" name="nr_q_extra[]" value="' . esc_attr( $x['slug'] ) . '" data-price="' . esc_attr( (float) $x['price'] ) . '"><span>' . esc_html( $x['label'] ) . ' +' . esc_html( $cur . number_format_i18n( (float) $x['price'] ) ) . '</span></label>';
					}
					if ( (float) $q['license'] > 0 ) {
						echo '<label class="nr-slots__extra"><input type="checkbox" name="nr_q_license" value="1" data-price="' . esc_attr( (float) $q['license'] ) . '"><span>' . esc_html__( 'Commercial usage license', 'raveenthiran' ) . ' +' . esc_html( $cur . number_format_i18n( (float) $q['license'] ) ) . '</span></label>';
					}
					echo '</div>';
				}
				echo '<p class="nr-slots__total">' . esc_html__( 'Estimate', 'raveenthiran' ) . ' <b data-bk-total>—</b></p>';
				echo '</fieldset>';
			}
		}
		echo '<div class="nr-slots__fields">'
			. '<label><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Name', 'raveenthiran' ) . '</span><input type="text" name="name" autocomplete="name" required></label>'
			. '<label><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Email', 'raveenthiran' ) . '</span><input type="email" name="email" autocomplete="email" required></label>'
			. '<label class="nr-slots__notes"><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Anything I should know? (optional)', 'raveenthiran' ) . '</span><textarea name="notes" rows="3"></textarea></label>'
			. '</div>';
		echo '<button type="submit" class="nr-btn nr-btn--primary"><span>' . esc_html__( 'Book this time', 'raveenthiran' ) . '</span> <span>→</span></button>';
		echo '</form>';
	}
	$body = ob_get_clean();

	$intro = '<div class="nr-slots__intro"><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html( $a['title'] ) . '</span>'
		. ( $a['intro'] ? '<p>' . esc_html( $a['intro'] ) . '</p>' : '' ) . '</div>';

	// Live-total calculator JS — printed once, drives any [data-bk-calc] form.
	static $calc_printed = false;
	$calc_js = '';
	if ( ! $calc_printed ) {
		$calc_printed = true;
		$calc_js = <<<'JS'
<script>(function(){
	function upd(f){
		var c=f.querySelector('[data-bk-calc]'); if(!c) return;
		var cur=c.getAttribute('data-cur')||'€', t=0, any=false;
		var ty=f.querySelector('input[name="nr_q_type"]:checked'); if(ty){ t+=parseFloat(ty.getAttribute('data-price'))||0; any=true; }
		f.querySelectorAll('input[name="nr_q_extra[]"]:checked, input[name="nr_q_license"]:checked').forEach(function(x){ t+=parseFloat(x.getAttribute('data-price'))||0; any=true; });
		var o=f.querySelector('[data-bk-total]'); if(o) o.textContent = any ? cur+Math.round(t).toLocaleString() : '—';
	}
	document.querySelectorAll('.nr-slots__form').forEach(function(f){
		if(!f.querySelector('[data-bk-calc]')) return;
		f.addEventListener('change', function(){ upd(f); });
		upd(f);
	});
})();</script>
JS;
	}

	if ( ! $popover ) {
		return '<div class="nr-slots">' . $intro . $notice . $body . '</div>' . $calc_js;
	}

	// Popover: a trigger button on the page; the picker lives in a self-contained
	// modal with its own open/close (no dependency on the global modal JS).
	$bid  = $id . '-btn';
	$open = ( $state === 'taken' || $state === '0' );   // re-open after a failed attempt
	$out  = '<div class="nr-booking-trigger">' . $notice
		. '<button type="button" id="' . esc_attr( $bid ) . '" class="nr-btn nr-btn--primary"><span>' . esc_html( $a['cta'] ) . '</span> <span>→</span></button></div>';
	$out .= '<div id="' . esc_attr( $id ) . '" class="nr-modal nr-slots-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="' . esc_attr( wp_strip_all_tags( $a['title'] ) ) . '">'
		. '<div class="nr-modal__panel nr-slots__panel">'
		. '<button type="button" class="nr-modal__close" data-modal-close aria-label="' . esc_attr__( 'Close', 'raveenthiran' ) . '">✕</button>'
		. '<div class="nr-slots nr-slots--inmodal">' . $intro . $body . '</div>'
		. '</div></div>';
	$out .= '<script>(function(){'
		. 'var b=document.getElementById(' . wp_json_encode( $bid ) . '),m=document.getElementById(' . wp_json_encode( $id ) . ');'
		. 'if(!b||!m)return;if(m.parentNode!==document.body)document.body.appendChild(m);'
		. 'function o(){m.classList.add("is-on");m.setAttribute("aria-hidden","false");document.body.classList.add("is-modal-open");}'
		. 'function c(){m.classList.remove("is-on");m.setAttribute("aria-hidden","true");document.body.classList.remove("is-modal-open");}'
		. 'b.addEventListener("click",function(e){e.preventDefault();o();});'
		. 'm.addEventListener("click",function(e){if(e.target===m||(e.target.closest&&e.target.closest("[data-modal-close]"))){e.preventDefault();c();}});'
		. 'document.addEventListener("keydown",function(e){if(e.key==="Escape")c();});'
		. ( $open ? 'o();' : '' )
		. '})();</script>';
	return $out . $calc_js;
} );

/* ── Booking handler ───────────────────────────────────────────────────── */
function nr_book_slot_handle() {
	$ref = wp_get_referer() ?: home_url( '/' );
	if ( ! isset( $_POST['_nr_bnonce'] ) || ! wp_verify_nonce( $_POST['_nr_bnonce'], 'nr_book_slot' ) ) {
		wp_safe_redirect( add_query_arg( 'nr_booked', '0', $ref ) ); exit;
	}
	if ( ! empty( $_POST['nr_hp'] ) ) { wp_safe_redirect( $ref ); exit; }               // honeypot

	$sid   = (int) ( $_POST['slot'] ?? 0 );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$notes = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

	// Optional price estimate from the embedded calculator — recomputed here from
	// nr_quote_data() (never trust posted prices), stored so the invoice can use it.
	$est = ''; $bd = '';
	if ( function_exists( 'nr_quote_data' ) ) {
		$q = nr_quote_data();
		if ( ! empty( $q['types'] ) ) {
			$cur = $q['currency']; $total = 0; $rows = [];
			$ts  = sanitize_text_field( wp_unslash( $_POST['nr_q_type'] ?? '' ) );
			foreach ( $q['types'] as $t ) { if ( $t['slug'] === $ts ) { $total += (float) $t['base']; $rows[] = $t['label'] . ' | ' . (float) $t['base']; break; } }
			$ex = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['nr_q_extra'] ?? [] ) );
			foreach ( (array) ( $q['extras'] ?? [] ) as $x ) { if ( in_array( $x['slug'], $ex, true ) ) { $total += (float) $x['price']; $rows[] = $x['label'] . ' | ' . (float) $x['price']; } }
			if ( ! empty( $_POST['nr_q_license'] ) && (float) $q['license'] > 0 ) { $total += (float) $q['license']; $rows[] = __( 'Commercial usage license', 'raveenthiran' ) . ' | ' . (float) $q['license']; }
			if ( $total > 0 ) { $est = $cur . round( $total ); $bd = implode( ' ;; ', $rows ); }
		}
	}

	// Validate slot is real, future and still open (re-check → no double-booking).
	if ( ! $sid || get_post_type( $sid ) !== 'nr_slot' || nr_slot_status( $sid ) !== 'open'
		|| nr_slot_start( $sid ) < nr_book_cutoff() || ! is_email( $email ) || $name === '' ) {
		wp_safe_redirect( add_query_arg( 'nr_booked', 'taken', $ref ) ); exit;
	}

	// Reserve the slot first (so a racing request sees it as taken).
	update_post_meta( $sid, '_nr_slot_status', 'booked' );
	$token = wp_generate_password( 20, false );
	update_post_meta( $sid, '_nr_slot_token', $token );

	$when = wp_date( 'D j M Y · H:i', nr_slot_start( $sid ) );
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	// Log as a normal enquiry so invoicing + the details box apply.
	$eid = 0;
	if ( post_type_exists( 'nr_enquiry' ) ) {
		$eid = wp_insert_post( [
			'post_type'   => 'nr_enquiry',
			'post_status' => 'publish',
			'post_title'  => $name ?: $email,
			'post_content'=> $notes,
		] );
		if ( $eid && ! is_wp_error( $eid ) ) {
			update_post_meta( $eid, '_nr_email', $email );
			update_post_meta( $eid, '_nr_type',  __( 'Booking', 'raveenthiran' ) );
			update_post_meta( $eid, '_nr_date',  $when );
			update_post_meta( $eid, '_nr_slot',  $sid );
			update_post_meta( $sid, '_nr_slot_enquiry', (int) $eid );
			update_post_meta( $sid, '_nr_slot_name',    $name );
			update_post_meta( $sid, '_nr_slot_email',   $email );
			if ( $est ) update_post_meta( $eid, '_nr_est', $est );
			if ( $bd )  update_post_meta( $eid, '_nr_breakdown', $bd );
		}
	}

	// Owner notification.
	$to = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$ob = "New booking\n\nName: {$name}\nEmail: {$email}\nWhen: {$when}\nSlot: " . nr_slot_label( $sid );
	if ( $est ) $ob .= "\nEstimate: {$est}";
	if ( $notes ) $ob .= "\n\nNotes:\n{$notes}";
	wp_mail( $to, sprintf( '[%s] New booking — %s', $site, $when ), $ob, [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ] );

	// Client confirmation + .ics invite + self-service cancel link.
	$ics    = nr_slot_ics_file( $sid, $name, $email );
	$loc    = (string) ( nr_opt( 'nr_book_location', '' ) ?: nr_opt( 'nr_studio', '' ) );
	$cancel = add_query_arg( [ 'action' => 'nr_cancel_booking', 'slot' => $sid, 't' => $token ], admin_url( 'admin-post.php' ) );
	$cb  = sprintf(
		/* translators: 1: name, 2: date/time, 3: studio */
		__( "Hi %1\$s,\n\nyour booking is confirmed for %2\$s. A calendar invite is attached.\n\n— %3\$s", 'raveenthiran' ),
		$name ?: __( 'there', 'raveenthiran' ), $when, $site
	);
	if ( $loc )    $cb .= "\n\n" . sprintf( __( 'Where: %s', 'raveenthiran' ), $loc );
	$cb .= "\n\n" . sprintf( __( 'Need to cancel? %s', 'raveenthiran' ), $cancel );
	$headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $to ];
	wp_mail( $email, sprintf( __( 'Booking confirmed — %s', 'raveenthiran' ), $when ), $cb, $headers, $ics ? [ $ics ] : [] );
	if ( $ics ) @unlink( $ics );

	wp_safe_redirect( add_query_arg( 'nr_booked', '1', $ref ) ); exit;
}
add_action( 'admin_post_nr_book_slot', 'nr_book_slot_handle' );
add_action( 'admin_post_nopriv_nr_book_slot', 'nr_book_slot_handle' );

/* ── Admin: Obscura → Booking (add slots + list) ───────────────────────── */
add_action( 'admin_menu', function () {
	add_submenu_page( 'nr-theme-settings', __( 'Booking', 'raveenthiran' ), __( 'Booking', 'raveenthiran' ), 'manage_options', 'nr-booking', 'nr_booking_admin_page' );
} );

function nr_booking_admin_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Booking slots', 'raveenthiran' ); ?></h1>
		<?php if ( isset( $_GET['nr_added'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php printf( esc_html( _n( '%d slot added.', '%d slots added.', (int) $_GET['nr_added'], 'raveenthiran' ) ), (int) $_GET['nr_added'] ); ?></p></div>
		<?php endif; ?>
		<p style="max-width:680px"><?php esc_html_e( 'Add the times you’re open for bookings. Visitors pick a free slot on the [nr_booking_slots] block; it’s booked instantly and logged as an enquiry. No third party, no payment.', 'raveenthiran' ); ?></p>

		<?php if ( isset( $_GET['nr_saved'] ) ) : ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Booking settings saved.', 'raveenthiran' ); ?></p></div><?php endif; ?>

		<h2><?php esc_html_e( 'Booking settings', 'raveenthiran' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="card" style="padding:14px 16px;max-width:680px">
			<input type="hidden" name="action" value="nr_book_settings">
			<?php wp_nonce_field( 'nr_book_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr><th><label for="nr_lead"><?php esc_html_e( 'Lead time (hours)', 'raveenthiran' ); ?></label></th>
					<td><input type="number" id="nr_lead" name="lead" min="0" step="1" value="<?php echo esc_attr( (int) nr_opt( 'nr_book_lead', 0 ) ); ?>" style="width:90px">
						<p class="description"><?php esc_html_e( 'Minimum notice — visitors can’t book a slot starting sooner than this many hours from now. 0 = no limit.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_loc"><?php esc_html_e( 'Location', 'raveenthiran' ); ?></label></th>
					<td><input type="text" id="nr_loc" name="loc" class="regular-text" value="<?php echo esc_attr( (string) nr_opt( 'nr_book_location', '' ) ); ?>" placeholder="<?php echo esc_attr( (string) nr_opt( 'nr_studio', '' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Shown in the confirmation email and the calendar invite. Empty = the studio address from Settings.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_intro"><?php esc_html_e( 'Intro text', 'raveenthiran' ); ?></label></th>
					<td><input type="text" id="nr_intro" name="intro" class="large-text" value="<?php echo esc_attr( (string) nr_opt( 'nr_book_intro', '' ) ); ?>" placeholder="<?php esc_attr_e( 'Pick an open slot — you’ll get a confirmation with a calendar invite by email.', 'raveenthiran' ); ?>">
						<p class="description"><?php esc_html_e( 'Shown above the slot picker.', 'raveenthiran' ); ?></p></td></tr>
			</table>
			<p><button class="button"><?php esc_html_e( 'Save settings', 'raveenthiran' ); ?></button></p>
		</form>

		<h2><?php esc_html_e( 'Add slots', 'raveenthiran' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="card" style="padding:14px 16px;max-width:680px">
			<input type="hidden" name="action" value="nr_add_slots">
			<?php wp_nonce_field( 'nr_add_slots' ); ?>
			<table class="form-table" role="presentation">
				<tr><th><label for="nr_d"><?php esc_html_e( 'Date', 'raveenthiran' ); ?></label></th>
					<td><input type="date" id="nr_d" name="date" required>
						<p class="description"><?php esc_html_e( 'Start date (or the single day).', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_u"><?php esc_html_e( 'Repeat until', 'raveenthiran' ); ?></label></th>
					<td><input type="date" id="nr_u" name="until">
						<p class="description"><?php esc_html_e( 'Optional. Leave empty for a single day. If set, slots are created on every chosen weekday from the start date up to (and including) this date.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Weekdays', 'raveenthiran' ); ?></th>
					<td><?php
						$nr_wd = [ 1 => __( 'Mon', 'raveenthiran' ), 2 => __( 'Tue', 'raveenthiran' ), 3 => __( 'Wed', 'raveenthiran' ), 4 => __( 'Thu', 'raveenthiran' ), 5 => __( 'Fri', 'raveenthiran' ), 6 => __( 'Sat', 'raveenthiran' ), 7 => __( 'Sun', 'raveenthiran' ) ];
						foreach ( $nr_wd as $nr_n => $nr_lbl ) {
							echo '<label style="display:inline-block;margin:0 12px 4px 0"><input type="checkbox" name="wd[]" value="' . (int) $nr_n . '"> ' . esc_html( $nr_lbl ) . '</label>';
						}
					?><p class="description"><?php esc_html_e( 'Only used with “Repeat until”. None ticked = every day in the range.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_st"><?php esc_html_e( 'Start time', 'raveenthiran' ); ?></label></th>
					<td><input type="time" id="nr_st" name="start_t" required></td></tr>
				<tr><th><label for="nr_et"><?php esc_html_e( 'End time', 'raveenthiran' ); ?></label></th>
					<td><input type="time" id="nr_et" name="end_t">
						<p class="description"><?php esc_html_e( 'Slots are generated from start to end, back-to-back by the duration below (e.g. 14:00–18:00 at 60 min → 14:00, 15:00, 16:00, 17:00). Leave empty for a single slot at the start time.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_dur"><?php esc_html_e( 'Duration (min)', 'raveenthiran' ); ?></label></th>
					<td><input type="number" id="nr_dur" name="dur" value="60" min="5" step="5" style="width:90px"></td></tr>
				<tr><th><label for="nr_buf"><?php esc_html_e( 'Buffer (min)', 'raveenthiran' ); ?></label></th>
					<td><input type="number" id="nr_buf" name="buffer" value="0" min="0" step="5" style="width:90px">
						<p class="description"><?php esc_html_e( 'Optional gap between back-to-back slots (e.g. 15 to reset between sessions).', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_lbl"><?php esc_html_e( 'Label', 'raveenthiran' ); ?></label></th>
					<td><input type="text" id="nr_lbl" name="label" placeholder="<?php esc_attr_e( 'e.g. Portrait mini-session', 'raveenthiran' ); ?>" class="regular-text"></td></tr>
			</table>
			<p><button class="button button-primary"><?php esc_html_e( 'Add slots', 'raveenthiran' ); ?></button></p>
		</form>

		<h2 style="margin-top:30px"><?php esc_html_e( 'Upcoming slots', 'raveenthiran' ); ?></h2>
		<?php
		$slots = get_posts( [
			'post_type' => 'nr_slot', 'posts_per_page' => 200, 'post_status' => 'publish',
			'meta_query' => [ [ 'key' => '_nr_slot_start', 'value' => time() - DAY_IN_SECONDS, 'compare' => '>=', 'type' => 'NUMERIC' ] ],
			'meta_key' => '_nr_slot_start', 'orderby' => 'meta_value_num', 'order' => 'ASC',
		] );
		if ( ! $slots ) {
			echo '<p>' . esc_html__( 'No upcoming slots yet.', 'raveenthiran' ) . '</p>';
		} else {
			echo '<table class="widefat striped" style="max-width:760px"><thead><tr>'
				. '<th>' . esc_html__( 'When', 'raveenthiran' ) . '</th><th>' . esc_html__( 'Label', 'raveenthiran' ) . '</th>'
				. '<th>' . esc_html__( 'Status', 'raveenthiran' ) . '</th><th></th></tr></thead><tbody>';
			foreach ( $slots as $s ) {
				$booked = nr_slot_status( $s->ID ) === 'booked';
				$eid    = (int) get_post_meta( $s->ID, '_nr_slot_enquiry', true );
				$who    = (string) get_post_meta( $s->ID, '_nr_slot_name', true );
				$del    = wp_nonce_url( admin_url( 'admin-post.php?action=nr_del_slot&slot=' . $s->ID ), 'nr_del_slot_' . $s->ID );
				$rel    = wp_nonce_url( admin_url( 'admin-post.php?action=nr_release_slot&slot=' . $s->ID ), 'nr_release_' . $s->ID );
				echo '<tr><td>' . esc_html( wp_date( 'D j M Y · H:i', nr_slot_start( $s->ID ) ) ) . '</td>'
					. '<td>' . esc_html( nr_slot_label( $s->ID ) ?: '—' ) . '</td>'
					. '<td>' . ( $booked
						? '<strong style="color:#b32d2e">' . esc_html__( 'Booked', 'raveenthiran' ) . '</strong>'
							. ( $who ? ' · ' . ( $eid ? '<a href="' . esc_url( get_edit_post_link( $eid ) ) . '">' . esc_html( $who ) . '</a>' : esc_html( $who ) ) : '' )
						: '<span style="color:#1a7f37">' . esc_html__( 'Open', 'raveenthiran' ) . '</span>' ) . '</td>'
					. '<td>'
					. ( $booked ? '<a class="button-link" href="' . esc_url( $rel ) . '" onclick="return confirm(\'' . esc_js( __( 'Cancel this booking and reopen the slot? The client is emailed.', 'raveenthiran' ) ) . '\')">' . esc_html__( 'Release', 'raveenthiran' ) . '</a> &nbsp; ' : '' )
					. '<a class="button-link" style="color:#b32d2e" href="' . esc_url( $del ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this slot?', 'raveenthiran' ) ) . '\')">' . esc_html__( 'Delete', 'raveenthiran' ) . '</a></td></tr>';
			}
			echo '</tbody></table>';
		}
		?>
	</div>
	<?php
}

/* Create slots from the admin form. */
add_action( 'admin_post_nr_add_slots', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_add_slots' ) ) wp_die( 'Denied.' );
	$date  = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );
	$until = sanitize_text_field( wp_unslash( $_POST['until'] ?? '' ) );
	$dur   = max( 5, (int) ( $_POST['dur'] ?? 60 ) );
	$buffer = max( 0, (int) ( $_POST['buffer'] ?? 0 ) );
	$label = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
	// Generate the per-day start times from a start→end range, stepped by the
	// duration (back-to-back). No end time → a single slot at the start time.
	$start_t = sanitize_text_field( wp_unslash( $_POST['start_t'] ?? '' ) );
	$end_t   = sanitize_text_field( wp_unslash( $_POST['end_t'] ?? '' ) );
	$times   = [];
	if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $start_t ) ) {
		if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $end_t ) ) {
			list( $sh, $sm ) = array_map( 'intval', explode( ':', $start_t ) );
			list( $eh, $em ) = array_map( 'intval', explode( ':', $end_t ) );
			$cur = $sh * 60 + $sm; $stop = $eh * 60 + $em; $guard = 0;
			while ( $cur + $dur <= $stop && $guard++ < 50 ) {
				$times[] = sprintf( '%02d:%02d', intdiv( $cur, 60 ), $cur % 60 );
				$cur += $dur + $buffer;
			}
		} else {
			$times = [ $start_t ];                                    // single slot
		}
	}
	$wds   = array_values( array_filter( array_map( 'intval', (array) ( $_POST['wd'] ?? [] ) ), function ( $n ) { return $n >= 1 && $n <= 7; } ) );
	$tz    = wp_timezone();

	if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		wp_safe_redirect( add_query_arg( 'nr_added', 0, admin_url( 'admin.php?page=nr-booking' ) ) ); exit;
	}

	// Build the list of dates: a single day, or every chosen weekday in a range.
	$dates = [];
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $until ) && $until >= $date ) {
		try {
			$cur = new DateTime( $date, $tz ); $end = new DateTime( $until, $tz ); $end->setTime( 23, 59, 59 );
		} catch ( Exception $e ) { $cur = null; }
		$guard = 0;
		while ( $cur && $cur <= $end && $guard++ < 370 ) {           // ~1 year cap
			if ( ! $wds || in_array( (int) $cur->format( 'N' ), $wds, true ) ) $dates[] = $cur->format( 'Y-m-d' );
			$cur->modify( '+1 day' );
		}
	} else {
		$dates = [ $date ];
	}

	$made = 0;
	foreach ( $dates as $d ) {
		foreach ( $times as $t ) {
			if ( $made >= 500 ) break 2;                              // hard safety cap per run
			if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $t ) ) continue;
			try { $dt = new DateTime( $d . ' ' . $t, $tz ); } catch ( Exception $e ) { continue; }
			$start = $dt->getTimestamp();
			// Skip if a slot already exists at this exact start (re-running is safe).
			$dupe = get_posts( [ 'post_type' => 'nr_slot', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_nr_slot_start', 'meta_value' => $start ] );
			if ( $dupe ) continue;
			$id = wp_insert_post( [ 'post_type' => 'nr_slot', 'post_status' => 'publish', 'post_title' => wp_date( 'Y-m-d H:i', $start ) . ( $label ? ' · ' . $label : '' ) ] );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_nr_slot_start', $start );
				update_post_meta( $id, '_nr_slot_dur', $dur );
				update_post_meta( $id, '_nr_slot_label', $label );
				update_post_meta( $id, '_nr_slot_status', 'open' );
				$made++;
			}
		}
	}
	wp_safe_redirect( add_query_arg( 'nr_added', $made, admin_url( 'admin.php?page=nr-booking' ) ) );
	exit;
} );

/* Delete a slot. */
add_action( 'admin_post_nr_del_slot', function () {
	$sid = (int) ( $_GET['slot'] ?? 0 );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_del_slot_' . $sid ) ) wp_die( 'Denied.' );
	if ( get_post_type( $sid ) === 'nr_slot' ) wp_delete_post( $sid, true );
	wp_safe_redirect( admin_url( 'admin.php?page=nr-booking' ) );
	exit;
} );

/* ── Cancellation: client self-service (token) + owner release ─────────── */

/** Reopen a booked slot and notify the other party. $by = 'client' | 'owner'. */
function nr_slot_release( $sid, $by = 'owner' ) {
	$sid = (int) $sid;
	if ( get_post_type( $sid ) !== 'nr_slot' ) return false;
	$eid   = (int) get_post_meta( $sid, '_nr_slot_enquiry', true );
	$name  = (string) get_post_meta( $sid, '_nr_slot_name', true );
	$email = (string) get_post_meta( $sid, '_nr_slot_email', true );
	$when  = wp_date( 'D j M Y · H:i', nr_slot_start( $sid ) );
	$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$owner = nr_opt( 'nr_email', get_option( 'admin_email' ) );

	// Reopen for others; drop the booking link + token.
	update_post_meta( $sid, '_nr_slot_status', 'open' );
	delete_post_meta( $sid, '_nr_slot_token' );
	delete_post_meta( $sid, '_nr_slot_enquiry' );
	delete_post_meta( $sid, '_nr_slot_name' );
	delete_post_meta( $sid, '_nr_slot_email' );

	// Keep the enquiry as a record, marked cancelled.
	if ( $eid && get_post_type( $eid ) === 'nr_enquiry' ) {
		update_post_meta( $eid, '_nr_cancelled', current_time( 'mysql' ) );
		update_post_meta( $eid, '_nr_type', __( 'Booking (cancelled)', 'raveenthiran' ) );
	}

	$h = [ 'Content-Type: text/plain; charset=UTF-8' ];
	if ( $by === 'client' && $owner ) {
		wp_mail( $owner, sprintf( '[%s] Booking cancelled — %s', $site, $when ),
			sprintf( "%s cancelled their booking for %s.\nThe slot is open again.", $name ?: $email, $when ), $h );
	} elseif ( $by === 'owner' && is_email( $email ) ) {
		wp_mail( $email, sprintf( __( 'Booking cancelled — %s', 'raveenthiran' ), $when ),
			sprintf( __( "Hi %1\$s,\n\nyour booking on %2\$s has been cancelled. Sorry for any inconvenience — just reply and we'll find another time.\n\n— %3\$s", 'raveenthiran' ), $name ?: __( 'there', 'raveenthiran' ), $when, $site ),
			array_merge( $h, [ 'Reply-To: ' . $owner ] ) );
	}
	return true;
}

/* Client cancels via the tokenised link from their confirmation email. */
function nr_cancel_booking_handle() {
	$sid    = (int) ( $_GET['slot'] ?? 0 );
	$tok    = sanitize_text_field( wp_unslash( $_GET['t'] ?? '' ) );
	$dest   = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/' );
	$stored = (string) get_post_meta( $sid, '_nr_slot_token', true );
	$ok = $sid && get_post_type( $sid ) === 'nr_slot' && nr_slot_status( $sid ) === 'booked'
		&& $tok !== '' && $stored !== '' && hash_equals( $stored, $tok )
		&& nr_slot_start( $sid ) > time();
	if ( $ok ) nr_slot_release( $sid, 'client' );
	wp_safe_redirect( add_query_arg( 'nr_cancelled', $ok ? '1' : '0', $dest ) );
	exit;
}
add_action( 'admin_post_nr_cancel_booking', 'nr_cancel_booking_handle' );
add_action( 'admin_post_nopriv_nr_cancel_booking', 'nr_cancel_booking_handle' );

/* Owner releases a booked slot from the Booking admin list. */
add_action( 'admin_post_nr_release_slot', function () {
	$sid = (int) ( $_GET['slot'] ?? 0 );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'nr_release_' . $sid ) ) wp_die( 'Denied.' );
	nr_slot_release( $sid, 'owner' );
	wp_safe_redirect( admin_url( 'admin.php?page=nr-booking' ) );
	exit;
} );

/* Save booking settings (lead time, location, intro). */
add_action( 'admin_post_nr_book_settings', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'nr_book_settings' ) ) wp_die( 'Denied.' );
	update_option( 'nr_book_lead', max( 0, (int) ( $_POST['lead'] ?? 0 ) ), false );
	update_option( 'nr_book_location', sanitize_text_field( wp_unslash( $_POST['loc'] ?? '' ) ), false );
	update_option( 'nr_book_intro', sanitize_text_field( wp_unslash( $_POST['intro'] ?? '' ) ), false );
	wp_safe_redirect( add_query_arg( 'nr_saved', '1', admin_url( 'admin.php?page=nr-booking' ) ) );
	exit;
} );
