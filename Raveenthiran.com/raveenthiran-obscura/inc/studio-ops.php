<?php
/**
 * inc/studio-ops.php — enquiry workflow for an enquiry-based studio (v4.54.0).
 * #65 mini-CRM pipeline · #66 follow-up reminders (to the owner, never the client) ·
 * #151 auto-tag suggestions · #59 proofing selection on the delivery page.
 * No payments, no client logins — pure workflow + delivery polish.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const NR_STAGES = [ 'new' => 'New', 'quoted' => 'Quoted', 'booked' => 'Booked', 'done' => 'Delivered', 'lost' => 'Lost' ];

/* ── #65 — stage column in the enquiry list + a Pipeline board ── */
add_filter( 'manage_nr_enquiry_posts_columns', function ( $c ) {
	$add = [ 'nr_stage' => __( 'Stage', 'raveenthiran' ) ];
	return array_slice( $c, 0, 2, true ) + $add + array_slice( $c, 2, null, true );
} );
add_action( 'manage_nr_enquiry_posts_custom_column', function ( $col, $id ) {
	if ( $col !== 'nr_stage' ) return;
	$s = get_post_meta( $id, '_nr_stage', true ) ?: 'new';
	$tint = [ 'new' => '#e0a000', 'quoted' => '#3a86ff', 'booked' => '#2a7', 'done' => '#888', 'lost' => '#c0392b' ];
	printf( '<span style="font-weight:600;color:%s">%s</span>', $tint[ $s ] ?? '#888', esc_html( NR_STAGES[ $s ] ?? $s ) );
}, 10, 2 );

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=nr_enquiry', __( 'Pipeline', 'raveenthiran' ), __( 'Pipeline', 'raveenthiran' ), 'edit_posts', 'nr-pipeline', 'nr_pipeline_page' );
} );
function nr_pipeline_page() {
	$ids = get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 300, 'post_status' => 'publish', 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC' ] );
	$cols = array_fill_keys( array_keys( NR_STAGES ), [] );
	foreach ( $ids as $id ) { $s = get_post_meta( $id, '_nr_stage', true ) ?: 'new'; if ( ! isset( $cols[ $s ] ) ) $s = 'new'; $cols[ $s ][] = $id; }
	echo '<div class="wrap"><h1>' . esc_html__( 'Enquiry pipeline', 'raveenthiran' ) . '</h1>';
	echo '<style>.nr-kb{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:16px}.nr-kb__col{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:10px;min-height:120px}.nr-kb__h{font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:#646970;margin:0 0 8px;display:flex;justify-content:space-between}.nr-kb__card{border:1px solid #e2e4e7;border-radius:5px;padding:8px;margin-bottom:8px;font-size:13px}.nr-kb__card b{display:block}.nr-kb__card small{color:#787c82}.nr-kb__card select{width:100%;margin-top:6px;font-size:12px}</style>';
	echo '<div class="nr-kb">';
	foreach ( NR_STAGES as $key => $label ) {
		echo '<div class="nr-kb__col"><p class="nr-kb__h"><span>' . esc_html( $label ) . '</span><span>' . count( $cols[ $key ] ) . '</span></p>';
		foreach ( $cols[ $key ] as $id ) {
			$email = get_post_meta( $id, '_nr_email', true );
			$score = function_exists( 'nr_enquiry_score' ) ? nr_enquiry_score( $id ) : 0;
			echo '<div class="nr-kb__card"><b><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ?: $email ) . '</a></b>';
			echo '<small>' . esc_html( get_the_date( 'j M', $id ) . ' · ' . ( get_post_meta( $id, '_nr_type', true ) ?: '—' ) . ( $score >= 5 ? ' · ★' : '' ) ) . '</small>';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="nr_stage"><input type="hidden" name="e" value="' . (int) $id . '">' . wp_nonce_field( 'nr_stage_' . $id, '_wpnonce', true, false );
			echo '<select name="stage" onchange="this.form.submit()">';
			foreach ( NR_STAGES as $sk => $sl ) printf( '<option value="%s"%s>%s</option>', esc_attr( $sk ), selected( $key, $sk, false ), esc_html( $sl ) );
			echo '</select></form></div>';
		}
		echo '</div>';
	}
	echo '</div></div>';
}
add_action( 'admin_post_nr_stage', function () {
	$id = (int) ( $_POST['e'] ?? 0 );
	if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( $_POST['_wpnonce'] ?? '', 'nr_stage_' . $id ) ) wp_die( 'no' );
	$s = sanitize_key( $_POST['stage'] ?? 'new' );
	if ( isset( NR_STAGES[ $s ] ) ) update_post_meta( $id, '_nr_stage', $s );
	wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=nr_enquiry&page=nr-pipeline' ) );
	exit;
} );

/* ── #66 — follow-up reminders to the OWNER (never the client) ── */
add_action( 'init', function () { if ( ! wp_next_scheduled( 'nr_followup_scan' ) ) wp_schedule_event( time() + 2 * HOUR_IN_SECONDS, 'daily', 'nr_followup_scan' ); } );
add_action( 'nr_followup_scan', function () {
	$days = max( 1, (int) nr_opt( 'nr_followup_days', 3 ) );
	$cut  = time() - $days * DAY_IN_SECONDS;
	$ids  = get_posts( [ 'post_type' => 'nr_enquiry', 'posts_per_page' => 50, 'post_status' => 'publish', 'fields' => 'ids',
		'date_query' => [ [ 'before' => gmdate( 'Y-m-d H:i:s', $cut ) ] ],
		'meta_query' => [ 'relation' => 'OR', [ 'key' => '_nr_stage', 'value' => 'new' ], [ 'key' => '_nr_stage', 'compare' => 'NOT EXISTS' ] ] ] );
	if ( ! $ids ) return;
	$lines = [];
	foreach ( $ids as $id ) $lines[] = '• ' . get_the_title( $id ) . ' (' . get_post_meta( $id, '_nr_email', true ) . ') — ' . get_the_date( 'j M', $id ) . ' — ' . admin_url( 'post.php?post=' . $id . '&action=edit' );
	wp_mail(
		nr_opt( 'nr_email', get_option( 'admin_email' ) ),
		sprintf( __( '[%s] %d enquiries waiting > %d days', 'raveenthiran' ), get_bloginfo( 'name' ), count( $ids ), $days ),
		__( "Still marked “New” and unanswered:\n\n", 'raveenthiran' ) . implode( "\n", $lines ) . "\n\n" . __( 'Move them along in the Pipeline once handled.', 'raveenthiran' )
	);
} );

/* ── #151 — auto-tag suggestions (meta box; you confirm before applying) ── */
function nr_suggest_tags( $id, $max = 8 ) {
	$text = mb_strtolower( get_the_title( $id ) . ' ' . wp_strip_all_tags( get_post_field( 'post_content', $id ) ) . ' ' . get_post_field( 'post_excerpt', $id ) );
	$text = preg_replace( '/[^a-zäöüß0-9\s-]/u', ' ', $text );
	$stop = array_flip( explode( ' ', 'the a an and or of to in on at for with from this that was were is are be it as by we i you he she they but not no all your our their more most some any one two three new shot work project image images photo photos vienna wien der die das und oder von zu in den dem ein eine mit für auf ist sind war' ) );
	$freq = [];
	foreach ( preg_split( '/\s+/', $text ) as $w ) {
		$w = trim( $w, '-' );
		if ( mb_strlen( $w ) < 4 || isset( $stop[ $w ] ) || is_numeric( $w ) ) continue;
		$freq[ $w ] = ( $freq[ $w ] ?? 0 ) + 1;
	}
	arsort( $freq );
	$existing = array_map( 'sanitize_title', wp_get_post_terms( $id, 'nr_project_tag', [ 'fields' => 'names' ] ) ?: [] );
	$out = [];
	foreach ( array_keys( $freq ) as $w ) {
		if ( in_array( sanitize_title( $w ), $existing, true ) ) continue;
		$out[] = $w;
		if ( count( $out ) >= $max ) break;
	}
	return $out;
}
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'nr_autotag', __( 'Suggested tags', 'raveenthiran' ), function ( $post ) {
		$sugg = nr_suggest_tags( $post->ID );
		wp_nonce_field( 'nr_autotag', 'nr_autotag_nonce' );
		if ( ! $sugg ) { echo '<p class="description">' . esc_html__( 'No new suggestions — add some text or tags first.', 'raveenthiran' ) . '</p>'; return; }
		echo '<p class="description">' . esc_html__( 'Tick to add as tags when you update:', 'raveenthiran' ) . '</p>';
		foreach ( $sugg as $w ) printf( '<label style="display:inline-block;margin:0 8px 6px 0"><input type="checkbox" name="nr_autotag[]" value="%s"> %s</label>', esc_attr( $w ), esc_html( $w ) );
	}, 'nr_project', 'side' );
} );
add_action( 'save_post_nr_project', function ( $id ) {
	if ( ! isset( $_POST['nr_autotag_nonce'] ) || ! wp_verify_nonce( $_POST['nr_autotag_nonce'], 'nr_autotag' ) ) return;
	if ( empty( $_POST['nr_autotag'] ) || ! current_user_can( 'edit_post', $id ) ) return;
	$tags = array_map( 'sanitize_text_field', (array) $_POST['nr_autotag'] );
	wp_set_post_terms( $id, $tags, 'nr_project_tag', true );
} );

/* ── #59 — proofing: client picks favourites on the delivery page ── */
/* Front-end behaviour is in theme.js; the studio gets the picks by email. */
add_action( 'admin_post_nopriv_nr_proof', 'nr_proof_handler' );
add_action( 'admin_post_nr_proof', 'nr_proof_handler' );
function nr_proof_handler() {
	$back  = wp_get_referer() ?: home_url( '/' );
	$page  = (int) ( $_POST['page'] ?? 0 );
	$picks = array_map( 'sanitize_text_field', (array) ( $_POST['picks'] ?? [] ) );
	$picks = array_slice( array_filter( $picks ), 0, 200 );
	if ( $page && $picks ) {
		$to   = nr_opt( 'nr_email', get_option( 'admin_email' ) );
		$body = sprintf( __( "Client selection for: %s\n%s\n\nFavourites (%d):\n", 'raveenthiran' ), get_the_title( $page ), get_permalink( $page ), count( $picks ) )
			. implode( "\n", array_map( function ( $p ) { return '• ' . $p; }, $picks ) );
		wp_mail( $to, sprintf( __( '[%s] Proofing selection — %s', 'raveenthiran' ), get_bloginfo( 'name' ), get_the_title( $page ) ), $body );
	}
	wp_safe_redirect( add_query_arg( 'nr_proof', '1', $back ) );
	exit;
}
