<?php
/**
 * #80 — Automated internal linking between projects (opt-in).
 *
 * On a single project, links the first mention of another project's title in
 * the body text to that project — good for SEO and discovery. Opt-in via
 * Theme Settings (nr_fx_interlink). DOM-based (not regex on HTML), so it only
 * ever touches bare text nodes: never headings, never existing links, never
 * tag attributes. Caps the number of links so prose stays readable.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'the_content', 'nr_interlink_content', 12 );

function nr_interlink_content( $content ) {
	if ( nr_opt( 'nr_fx_interlink', '0' ) !== '1' ) return $content;
	if ( ! is_singular( 'nr_project' ) || ! in_the_loop() || ! is_main_query() ) return $content;
	if ( trim( $content ) === '' || ! class_exists( 'DOMDocument' ) ) return $content;

	$map = nr_interlink_map( get_the_ID() );
	if ( ! $map ) return $content;

	$max  = 4;       // total links added
	$made = 0;

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8"?><div id="nr-il-root">' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	$xpath = new DOMXPath( $dom );

	// text nodes that are NOT inside a link or a heading
	$nodes = $xpath->query( '//text()[not(ancestor::a) and not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4)]' );
	if ( ! $nodes ) return $content;

	foreach ( $nodes as $node ) {
		if ( $made >= $max ) break;
		$text = $node->nodeValue;
		if ( trim( $text ) === '' ) continue;

		foreach ( $map as $title => $url ) {
			if ( $made >= $max ) break;
			$pos = stripos( $text, $title );
			if ( $pos === false ) continue;
			// whole-word-ish: avoid matching inside a longer word
			$before = $pos > 0 ? $text[ $pos - 1 ] : ' ';
			$afterI = $pos + strlen( $title );
			$after  = $afterI < strlen( $text ) ? $text[ $afterI ] : ' ';
			if ( ctype_alnum( $before ) || ctype_alnum( $after ) ) continue;

			$frag    = $dom->createDocumentFragment();
			$matched = substr( $text, $pos, strlen( $title ) );
			$frag->appendChild( $dom->createTextNode( substr( $text, 0, $pos ) ) );
			$a = $dom->createElement( 'a', htmlspecialchars( $matched, ENT_QUOTES ) );
			$a->setAttribute( 'href', $url );
			$a->setAttribute( 'class', 'nr-interlink' );
			$frag->appendChild( $a );
			$frag->appendChild( $dom->createTextNode( substr( $text, $afterI ) ) );
			$node->parentNode->replaceChild( $frag, $node );

			unset( $map[ $title ] ); // one link per project max
			$made++;
			break; // move to next text node after a hit
		}
	}

	if ( ! $made ) return $content;

	// With LIBXML_HTML_NOIMPLIED the wrapper div is the document element.
	$root = $dom->documentElement;
	$out  = '';
	if ( $root ) {
		foreach ( $root->childNodes as $child ) $out .= $dom->saveHTML( $child );
	}
	return $out !== '' ? $out : $content;
}

/* title => permalink for published projects (cached, current post excluded). */
function nr_interlink_map( $exclude_id ) {
	$cache = wp_cache_get( 'nr_interlink_map' );
	if ( $cache === false ) {
		$cache = [];
		$ids = get_posts( [
			'post_type'      => 'nr_project',
			'posts_per_page' => 300,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		foreach ( $ids as $id ) {
			$t = html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' );
			if ( mb_strlen( $t ) >= 4 ) $cache[ $t ] = [ 'id' => $id, 'url' => get_permalink( $id ) ];
		}
		// longest titles first so "The Vienna Notebook" wins over "Vienna"
		uksort( $cache, function ( $a, $b ) { return mb_strlen( $b ) - mb_strlen( $a ); } );
		wp_cache_set( 'nr_interlink_map', $cache, '', 300 );
	}

	$out = [];
	foreach ( $cache as $title => $data ) {
		if ( (int) $data['id'] === (int) $exclude_id ) continue;
		$out[ $title ] = $data['url'];
	}
	return $out;
}
