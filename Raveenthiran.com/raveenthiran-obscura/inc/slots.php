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

/** Open, future slots (ascending). */
function nr_slots_open( $limit = 60 ) {
	return get_posts( [
		'post_type'      => 'nr_slot',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'meta_query'     => [
			'relation' => 'AND',
			[ 'key' => '_nr_slot_status', 'value' => 'open' ],
			[ 'key' => '_nr_slot_start', 'value' => time(), 'compare' => '>=', 'type' => 'NUMERIC' ],
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
		echo '<fieldset class="nr-slots__grid"><legend class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Available times', 'raveenthiran' ) . '</legend>';
		foreach ( $slots as $i => $s ) {
			echo '<label class="nr-slots__slot"><input type="radio" name="slot" value="' . (int) $s->ID . '"' . checked( $i, 0, false ) . ' required><span>' . esc_html( nr_slot_human( $s->ID ) ) . '</span></label>';
		}
		echo '</fieldset>';
		echo '<div class="nr-slots__fields">'
			. '<label><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Name', 'raveenthiran' ) . '</span><input type="text" name="name" autocomplete="name" required></label>'
			. '<label><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Email', 'raveenthiran' ) . '</span><input type="email" name="email" autocomplete="email" required></label>'
			. '<label class="nr-slots__notes"><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html__( 'Anything I should know? (optional)', 'raveenthiran' ) . '</span><textarea name="notes" rows="3"></textarea></label>'
			. '</div>';
		if ( function_exists( 'nr_turnstile_field' ) ) nr_turnstile_field();
		echo '<button type="submit" class="nr-btn nr-btn--primary"><span>' . esc_html__( 'Book this time', 'raveenthiran' ) . '</span> <span>→</span></button>';
		echo '</form>';
	}
	$body = ob_get_clean();

	$intro = '<div class="nr-slots__intro"><span class="nr-eyebrow nr-eyebrow--plain">' . esc_html( $a['title'] ) . '</span>'
		. ( $a['intro'] ? '<p>' . esc_html( $a['intro'] ) . '</p>' : '' ) . '</div>';

	if ( ! $popover ) {
		return '<div class="nr-slots">' . $intro . $notice . $body . '</div>';
	}

	// Popover: a trigger button on the page; the picker lives in the theme modal
	// (reuses the global .nr-modal open/close + focus-trap wiring in theme.js).
	$bid = $id . '-btn';
	$out  = '<div class="nr-booking-trigger">' . $notice
		. '<button type="button" id="' . esc_attr( $bid ) . '" class="nr-btn nr-btn--primary" data-modal="' . esc_attr( $id ) . '"><span>' . esc_html( $a['cta'] ) . '</span> <span>→</span></button></div>';
	$out .= '<div id="' . esc_attr( $id ) . '" class="nr-modal nr-slots-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="' . esc_attr( wp_strip_all_tags( $a['title'] ) ) . '">'
		. '<div class="nr-modal__panel nr-slots__panel">'
		. '<button type="button" class="nr-modal__close" data-modal-close aria-label="' . esc_attr__( 'Close', 'raveenthiran' ) . '">✕</button>'
		. '<div class="nr-slots nr-slots--inmodal">' . $intro . $body . '</div>'
		. '</div></div>';
	// Relocate to <body> so position:fixed is viewport-relative (no transformed
	// ancestor breaks it); re-open via the trigger after a "just taken" bounce.
	$out .= '<script>(function(){var m=document.getElementById(' . wp_json_encode( $id ) . ');if(m){document.body.appendChild(m);}'
		. ( $state === 'taken' ? 'window.addEventListener("load",function(){var b=document.getElementById(' . wp_json_encode( $bid ) . ');if(b)b.click();});' : '' )
		. '})();</script>';
	return $out;
} );

/* ── Booking handler ───────────────────────────────────────────────────── */
function nr_book_slot_handle() {
	$ref = wp_get_referer() ?: home_url( '/' );
	if ( ! isset( $_POST['_nr_bnonce'] ) || ! wp_verify_nonce( $_POST['_nr_bnonce'], 'nr_book_slot' ) ) {
		wp_safe_redirect( add_query_arg( 'nr_booked', '0', $ref ) ); exit;
	}
	if ( ! empty( $_POST['nr_hp'] ) ) { wp_safe_redirect( $ref ); exit; }               // honeypot
	if ( function_exists( 'nr_turnstile_passes' ) && ! nr_turnstile_passes() ) {
		wp_safe_redirect( add_query_arg( 'nr_booked', '0', $ref ) ); exit;
	}

	$sid   = (int) ( $_POST['slot'] ?? 0 );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$notes = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );

	// Validate slot is real, future and still open (re-check → no double-booking).
	if ( ! $sid || get_post_type( $sid ) !== 'nr_slot' || nr_slot_status( $sid ) !== 'open'
		|| nr_slot_start( $sid ) < time() || ! is_email( $email ) || $name === '' ) {
		wp_safe_redirect( add_query_arg( 'nr_booked', 'taken', $ref ) ); exit;
	}

	// Reserve the slot first (so a racing request sees it as taken).
	update_post_meta( $sid, '_nr_slot_status', 'booked' );

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
		}
	}

	// Owner notification.
	$to = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	$ob = "New booking\n\nName: {$name}\nEmail: {$email}\nWhen: {$when}\nSlot: " . nr_slot_label( $sid );
	if ( $notes ) $ob .= "\n\nNotes:\n{$notes}";
	wp_mail( $to, sprintf( '[%s] New booking — %s', $site, $when ), $ob, [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ] );

	// Client confirmation + .ics invite.
	$ics = nr_slot_ics_file( $sid, $name, $email );
	$cb  = sprintf(
		/* translators: 1: name, 2: date/time, 3: studio */
		__( "Hi %1\$s,\n\nyour booking is confirmed for %2\$s. A calendar invite is attached.\n\nIf you need to change anything, just reply to this email.\n\n— %3\$s", 'raveenthiran' ),
		$name ?: __( 'there', 'raveenthiran' ), $when, $site
	);
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
		<p style="max-width:680px"><?php esc_html_e( 'Add the times you’re open for bookings. Visitors pick a free slot on the [nr_booking_slots] block; it’s booked instantly and logged as an enquiry. No third party, no payment.', 'raveenthiran' ); ?></p>

		<h2><?php esc_html_e( 'Add slots', 'raveenthiran' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="card" style="padding:14px 16px;max-width:680px">
			<input type="hidden" name="action" value="nr_add_slots">
			<?php wp_nonce_field( 'nr_add_slots' ); ?>
			<table class="form-table" role="presentation">
				<tr><th><label for="nr_d"><?php esc_html_e( 'Date', 'raveenthiran' ); ?></label></th>
					<td><input type="date" id="nr_d" name="date" required></td></tr>
				<tr><th><label for="nr_t"><?php esc_html_e( 'Times', 'raveenthiran' ); ?></label></th>
					<td><input type="text" id="nr_t" name="times" placeholder="10:00, 14:00, 16:30" class="regular-text" required>
						<p class="description"><?php esc_html_e( 'One or more start times, comma-separated.', 'raveenthiran' ); ?></p></td></tr>
				<tr><th><label for="nr_dur"><?php esc_html_e( 'Duration (min)', 'raveenthiran' ); ?></label></th>
					<td><input type="number" id="nr_dur" name="dur" value="60" min="5" step="5" style="width:90px"></td></tr>
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
				echo '<tr><td>' . esc_html( wp_date( 'D j M Y · H:i', nr_slot_start( $s->ID ) ) ) . '</td>'
					. '<td>' . esc_html( nr_slot_label( $s->ID ) ?: '—' ) . '</td>'
					. '<td>' . ( $booked
						? '<strong style="color:#b32d2e">' . esc_html__( 'Booked', 'raveenthiran' ) . '</strong>'
							. ( $who ? ' · ' . ( $eid ? '<a href="' . esc_url( get_edit_post_link( $eid ) ) . '">' . esc_html( $who ) . '</a>' : esc_html( $who ) ) : '' )
						: '<span style="color:#1a7f37">' . esc_html__( 'Open', 'raveenthiran' ) . '</span>' ) . '</td>'
					. '<td><a class="button-link" style="color:#b32d2e" href="' . esc_url( $del ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this slot?', 'raveenthiran' ) ) . '\')">' . esc_html__( 'Delete', 'raveenthiran' ) . '</a></td></tr>';
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
	$dur   = max( 5, (int) ( $_POST['dur'] ?? 60 ) );
	$label = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
	$times = array_filter( array_map( 'trim', explode( ',', (string) wp_unslash( $_POST['times'] ?? '' ) ) ) );
	$tz    = wp_timezone();
	$made  = 0;
	foreach ( $times as $t ) {
		if ( ! preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $t ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) continue;
		try { $dt = new DateTime( $date . ' ' . $t, $tz ); } catch ( Exception $e ) { continue; }
		$start = $dt->getTimestamp();
		$id = wp_insert_post( [ 'post_type' => 'nr_slot', 'post_status' => 'publish', 'post_title' => wp_date( 'Y-m-d H:i', $start ) . ( $label ? ' · ' . $label : '' ) ] );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_nr_slot_start', $start );
			update_post_meta( $id, '_nr_slot_dur', $dur );
			update_post_meta( $id, '_nr_slot_label', $label );
			update_post_meta( $id, '_nr_slot_status', 'open' );
			$made++;
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
