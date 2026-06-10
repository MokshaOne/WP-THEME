<?php
/**
 * Enquiry insights — a self-hosted measurement loop (no third-party analytics).
 *
 * Adds an "Enquiry insights" dashboard widget that answers the question that
 * actually matters for a portfolio: *which projects drive enquiries?* It reads
 * the attribution meta saved with each nr_enquiry (_nr_ref = the project the
 * visitor came from, _nr_type = shoot type, _nr_referrer = external source) and
 * shows counts over time + the top performers. Fully private, in your control.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_dashboard_setup', function () {
	if ( ! post_type_exists( 'nr_enquiry' ) || ! current_user_can( 'edit_posts' ) ) return;
	wp_add_dashboard_widget( 'nr_enquiry_insights', __( 'Enquiry insights', 'raveenthiran' ), 'nr_enquiry_insights_render' );
} );

function nr_enquiry_count_since( $days ) {
	$q = new WP_Query( [
		'post_type'      => 'nr_enquiry',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'date_query'     => $days ? [ [ 'after' => $days . ' days ago' ] ] : [],
	] );
	return (int) $q->found_posts;
}

function nr_enquiry_insights_render() {
	$c7  = nr_enquiry_count_since( 7 );
	$c30 = nr_enquiry_count_since( 30 );
	$c90 = nr_enquiry_count_since( 90 );
	$all = (int) ( wp_count_posts( 'nr_enquiry' )->publish ?? 0 );

	// Pull the last 90 days for breakdowns.
	$ids = get_posts( [
		'post_type'      => 'nr_enquiry',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'date_query'     => [ [ 'after' => '90 days ago' ] ],
	] );

	$by_ref = []; $by_type = []; $by_src = [];
	foreach ( $ids as $id ) {
		$ref  = trim( (string) get_post_meta( $id, '_nr_ref', true ) );
		$type = trim( (string) get_post_meta( $id, '_nr_type', true ) );
		$src  = trim( (string) get_post_meta( $id, '_nr_referrer', true ) );
		if ( $ref )  $by_ref[ $ref ]   = ( $by_ref[ $ref ]   ?? 0 ) + 1;
		if ( $type ) $by_type[ $type ] = ( $by_type[ $type ] ?? 0 ) + 1;
		if ( $src )  $by_src[ $src ]   = ( $by_src[ $src ]   ?? 0 ) + 1;
	}
	arsort( $by_ref ); arsort( $by_type ); arsort( $by_src );

	$pill = function ( $label, $n ) {
		return '<div style="flex:1;min-width:70px;text-align:center;padding:8px 6px;background:#f6f7f7;border-radius:4px">'
			. '<div style="font-size:22px;font-weight:600;line-height:1">' . (int) $n . '</div>'
			. '<div style="font-size:11px;color:#646970;text-transform:uppercase;letter-spacing:.04em;margin-top:2px">' . esc_html( $label ) . '</div></div>';
	};
	$list = function ( $rows, $empty ) {
		if ( ! $rows ) return '<p style="color:#646970;margin:4px 0 0">' . esc_html( $empty ) . '</p>';
		$max = max( $rows ); $out = '<ul style="margin:6px 0 0;list-style:none;padding:0">';
		$i = 0;
		foreach ( $rows as $label => $n ) {
			if ( $i++ >= 5 ) break;
			$pct = $max ? round( $n / $max * 100 ) : 0;
			$out .= '<li style="display:flex;align-items:center;gap:8px;margin:3px 0;font-size:13px">'
				. '<span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' . esc_html( $label ) . '</span>'
				. '<span style="flex:0 0 80px;height:6px;background:#eee;border-radius:3px;overflow:hidden"><span style="display:block;height:100%;width:' . $pct . '%;background:#F2A03D"></span></span>'
				. '<strong style="flex:0 0 24px;text-align:right">' . (int) $n . '</strong></li>';
		}
		return $out . '</ul>';
	};

	echo '<div style="display:flex;gap:8px;margin-bottom:14px">'
		. $pill( __( '7 days', 'raveenthiran' ), $c7 )
		. $pill( __( '30 days', 'raveenthiran' ), $c30 )
		. $pill( __( '90 days', 'raveenthiran' ), $c90 )
		. $pill( __( 'all time', 'raveenthiran' ), $all )
		. '</div>';

	echo '<p style="margin:0 0 2px;font-weight:600">' . esc_html__( 'Top projects driving enquiries (90d)', 'raveenthiran' ) . '</p>';
	echo $list( $by_ref, __( 'No project attribution yet — enquiries from a project’s “Commission similar” button will show here.', 'raveenthiran' ) );

	echo '<p style="margin:14px 0 2px;font-weight:600">' . esc_html__( 'By shoot type (90d)', 'raveenthiran' ) . '</p>';
	echo $list( $by_type, __( 'No type data yet.', 'raveenthiran' ) );

	if ( $by_src ) {
		echo '<p style="margin:14px 0 2px;font-weight:600">' . esc_html__( 'Top external sources (90d)', 'raveenthiran' ) . '</p>';
		echo $list( $by_src, '' );
	}

	$export = function_exists( 'nr_enquiry_export_url' )
		? sprintf( ' <a href="%s" class="button button-small">%s</a>', esc_url( nr_enquiry_export_url() ), esc_html__( 'Export CSV', 'raveenthiran' ) )
		: '';
	printf(
		'<p style="margin:14px 0 0"><a href="%s" class="button button-small">%s</a>%s</p>',
		esc_url( admin_url( 'edit.php?post_type=nr_enquiry' ) ),
		esc_html__( 'All enquiries', 'raveenthiran' ),
		$export
	);
}
