/* Cal.com embed orchestrator — self-hosted.
 *
 * Origin comes from NR_CAL (wp_localize_script); the official embed.js is
 * loaded from THAT origin (never cal.com), and only when needed:
 *   • [data-nr-cal-inline] → loaded when scrolled near (IntersectionObserver);
 *     PHP reserves min-height so there is no layout shift.
 *   • [data-nr-cal-popup]  → embed.js loaded on first interaction
 *     (hover-intent / focus / click), then opened as a modal.
 */
(function () {
	'use strict';
	var cfg    = window.NR_CAL || {};
	var origin = ( cfg.origin || 'https://cal.m1o.at' ).replace( /\/+$/, '' );
	var booted = false;

	/* Lazy bootstrap of the official Cal queue + script, pinned to our origin. */
	function boot() {
		if ( booted ) { return window.Cal; }
		booted = true;
		( function ( C, A, L ) {
			var p = function ( a, ar ) { a.q.push( ar ); };
			var d = C.document;
			C.Cal = C.Cal || function () {
				var cal = C.Cal, ar = arguments;
				if ( ! cal.loaded ) { cal.ns = {}; cal.q = cal.q || []; d.head.appendChild( d.createElement( 'script' ) ).src = A; cal.loaded = true; }
				if ( ar[0] === L ) {
					var api = function () { p( api, arguments ); };
					var ns = ar[1]; api.q = api.q || [];
					if ( typeof ns === 'string' ) { cal.ns[ ns ] = cal.ns[ ns ] || api; p( cal.ns[ ns ], ar ); p( cal, [ 'initNamespace', ns ] ); }
					else { p( cal, ar ); }
					return;
				}
				p( cal, ar );
			};
		} )( window, origin + '/embed/embed.js', 'init' );
		window.Cal( 'init', { origin: origin } );
		return window.Cal;
	}

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) { fn(); }
		else { document.addEventListener( 'DOMContentLoaded', fn ); }
	}

	ready( function () {
		/* Inline embeds — mount when near the viewport (no CLS). */
		var inlines = Array.prototype.slice.call( document.querySelectorAll( '[data-nr-cal-inline]' ) );
		if ( inlines.length ) {
			var mount = function ( el ) {
				if ( el.getAttribute( 'data-mounted' ) ) { return; }
				var link = el.getAttribute( 'data-cal-link' );
				if ( ! link ) { return; }
				el.setAttribute( 'data-mounted', '1' );
				boot()( 'inline', { elementOrSelector: el, calLink: link, config: { theme: 'dark' } } );
			};
			if ( 'IntersectionObserver' in window ) {
				var io = new IntersectionObserver( function ( entries, obs ) {
					entries.forEach( function ( e ) { if ( e.isIntersecting ) { mount( e.target ); obs.unobserve( e.target ); } } );
				}, { rootMargin: '200px' } );
				inlines.forEach( function ( el ) { io.observe( el ); } );
			} else {
				inlines.forEach( mount );
			}
		}

		/* Popup buttons — load embed.js on first interaction, then open modal. */
		Array.prototype.slice.call( document.querySelectorAll( '[data-nr-cal-popup]' ) ).forEach( function ( btn ) {
			var link = btn.getAttribute( 'data-cal-link' );
			if ( ! link ) { return; }
			var prime = function () { boot(); };              // hover/focus preload
			btn.addEventListener( 'mouseenter', prime, { once: true } );
			btn.addEventListener( 'focus', prime, { once: true } );
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				boot()( 'modal', { calLink: link, config: { theme: 'dark' } } );
			} );
		} );
	} );
})();
