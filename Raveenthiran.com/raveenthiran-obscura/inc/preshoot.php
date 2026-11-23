<?php
/**
 * inc/preshoot.php — IDEAS-50-NEXT #44 (+ #42 admin view) (v4.67.0).
 *  - An enquiry meta box: a confirmed "Shoot date" + any reference images the
 *    client attached on the Enquire form (#42).
 *  - A once-daily cron that sends info-only prep emails to the client at T-7 and
 *    T-1 before the shoot date. No payments, no marketing — logistics only.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── enquiry meta box: shoot date + reference images ───────────────────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nr_preshoot', __( 'Shoot date & references', 'raveenthiran' ), 'nr_preshoot_box', 'nr_enquiry', 'side' );
} );

function nr_preshoot_box( $post ) {
	wp_nonce_field( 'nr_preshoot_save', 'nr_preshoot_nonce' );
	$date = get_post_meta( $post->ID, '_nr_shoot_date', true );
	echo '<p><label style="display:block;font-weight:600;margin-bottom:4px">' . esc_html__( 'Confirmed shoot date', 'raveenthiran' ) . '</label>';
	echo '<input type="date" name="nr_shoot_date" value="' . esc_attr( $date ) . '" style="width:100%"></p>';
	echo '<p class="description">' . esc_html__( 'When set, the client gets info-only prep emails 7 days and 1 day before.', 'raveenthiran' ) . '</p>';

	$imgs = get_post_meta( $post->ID, '_nr_ref_images', true );
	if ( is_array( $imgs ) && $imgs ) {
		echo '<p style="font-weight:600;margin:12px 0 4px">' . esc_html__( 'Reference images', 'raveenthiran' ) . '</p><div style="display:flex;flex-wrap:wrap;gap:6px">';
		foreach ( $imgs as $aid ) {
			$thumb = wp_get_attachment_image( (int) $aid, [ 70, 70 ], false, [ 'style' => 'border-radius:4px;object-fit:cover' ] );
			$full  = wp_get_attachment_url( (int) $aid );
			if ( $thumb && $full ) echo '<a href="' . esc_url( $full ) . '" target="_blank" rel="noopener">' . $thumb . '</a>';
		}
		echo '</div>';
	}
}

add_action( 'save_post_nr_enquiry', function ( $post_id ) {
	if ( ! isset( $_POST['nr_preshoot_nonce'] ) || ! wp_verify_nonce( $_POST['nr_preshoot_nonce'], 'nr_preshoot_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$d = sanitize_text_field( wp_unslash( $_POST['nr_shoot_date'] ?? '' ) );
	if ( $d !== '' && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d ) ) {
		// a changed date re-arms both reminders
		if ( get_post_meta( $post_id, '_nr_shoot_date', true ) !== $d ) {
			delete_post_meta( $post_id, '_nr_preshoot_7' );
			delete_post_meta( $post_id, '_nr_preshoot_1' );
		}
		update_post_meta( $post_id, '_nr_shoot_date', $d );
	} else {
		delete_post_meta( $post_id, '_nr_shoot_date' );
	}
} );

/* ── daily cron: T-7 / T-1 prep emails ─────────────────────────────────── */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'nr_preshoot_cron' ) ) {
		wp_schedule_event( time() + 3600, 'daily', 'nr_preshoot_cron' );
	}
} );

add_action( 'nr_preshoot_cron', 'nr_preshoot_run' );
function nr_preshoot_run() {
	$today = current_time( 'Y-m-d' );
	$q = new WP_Query( [
		'post_type'      => 'nr_enquiry',
		'posts_per_page' => 200,
		'no_found_rows'  => true,
		'meta_query'     => [ [ 'key' => '_nr_shoot_date', 'compare' => 'EXISTS' ] ],
	] );
	foreach ( $q->posts as $p ) {
		$date  = get_post_meta( $p->ID, '_nr_shoot_date', true );
		$email = get_post_meta( $p->ID, '_nr_email', true );
		if ( ! $date || ! $email || ! is_email( $email ) ) continue;
		$days = (int) round( ( strtotime( $date ) - strtotime( $today ) ) / DAY_IN_SECONDS );
		if ( $days === 7 && ! get_post_meta( $p->ID, '_nr_preshoot_7', true ) ) {
			if ( nr_preshoot_email( $email, get_the_title( $p ), $date, 7 ) ) update_post_meta( $p->ID, '_nr_preshoot_7', 1 );
		} elseif ( $days === 1 && ! get_post_meta( $p->ID, '_nr_preshoot_1', true ) ) {
			if ( nr_preshoot_email( $email, get_the_title( $p ), $date, 1 ) ) update_post_meta( $p->ID, '_nr_preshoot_1', 1 );
		}
	}
	wp_reset_postdata();
}

function nr_preshoot_email( $to, $name, $date, $days ) {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$loc  = nr_opt( 'nr_location', 'Vienna' );
	$when = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
	$subject = $days === 1
		? sprintf( __( 'Tomorrow: our shoot — %s', 'raveenthiran' ), $site )
		: sprintf( __( 'One week to our shoot — %s', 'raveenthiran' ), $site );
	$lead = $days === 1
		? __( "We're shooting tomorrow — a few last notes so it runs smoothly:", 'raveenthiran' )
		: __( "Our shoot is a week away. A little prep makes the day effortless:", 'raveenthiran' );
	$body = sprintf(
		__( "Hi %1\$s,\n\n%2\$s\n\n• Date: %3\$s\n• Base: %4\$s — I'll confirm the exact meeting point a day ahead.\n• Bring: any wardrobe options and references you'd like to try.\n• Timing: arrive a few minutes early so we start relaxed.\n\nThis is an information-only note — no reply needed. If anything changed on your side, just reply and let me know.\n\n— %5\$s", 'raveenthiran' ),
		$name ?: __( 'there', 'raveenthiran' ), $lead, $when, $loc, $site
	);
	$from = nr_opt( 'nr_email', get_option( 'admin_email' ) );
	return wp_mail( $to, $subject, $body, [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $from ] );
}

/* Clear the schedule if the theme is switched away. */
add_action( 'switch_theme', function () {
	$ts = wp_next_scheduled( 'nr_preshoot_cron' );
	if ( $ts ) wp_unschedule_event( $ts, 'nr_preshoot_cron' );
} );
