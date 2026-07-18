/* moksha1one — scroll-mode engine.
 *   none : home / contact — a single locked viewport, no scroll.
 *   h    : portfolio / journal / about / enquire — vertical wheel drives a
 *          smooth HORIZONTAL track (desktop scroll-hijack); native swipe on
 *          touch. A progress rail + section counter track position.
 *   v    : single project / journal + everything else — native vertical.
 * Everything degrades gracefully: no JS, reduced-motion, or touch all stay
 * usable. Pairs with .mk-h / .mk-h__track / .mk-panel markup in the templates.
 */
(function () {
	'use strict';
	var MK   = window.MK || { mode: 'v' };
	var root = document.documentElement;
	var mq   = function ( q ) { return window.matchMedia && window.matchMedia( q ).matches; };
	var reduce  = mq( '(prefers-reduced-motion: reduce)' );
	var isTouch = mq( '(pointer: coarse)' ) || 'ontouchstart' in window;
	var lerp = function ( a, b, n ) { return ( 1 - n ) * a + n * b; };
	var clamp = function ( v, a, b ) { return Math.max( a, Math.min( b, v ) ); };
	var raf = window.requestAnimationFrame.bind( window );

	root.classList.add( 'mk-ready', 'mk-m-' + MK.mode );

	/* ---------- Magic Control → root flags + intensity var ---------- */
	var fx = MK.fx || {};
	if ( fx.orb === false )   root.classList.add( 'mk-no-orb' );
	if ( fx.hint === false )  root.classList.add( 'mk-no-hint' );
	if ( fx.tilt === false )  root.classList.add( 'mk-no-tilt' );
	var intensity = typeof fx.intensity === 'number' ? fx.intensity : 100;
	root.style.setProperty( '--mk-i', ( intensity / 100 ).toFixed( 2 ) );

	/* ---------- NO-SCROLL (home / contact) ---------- */
	if ( MK.mode === 'none' ) {
		root.classList.add( 'mk-lock' );
		return;
	}

	/* ---------- HORIZONTAL ---------- */
	if ( MK.mode === 'h' ) {
		var wrap  = document.querySelector( '[data-mk-h]' );
		var track = wrap && wrap.querySelector( '.mk-h__track' );
		if ( ! wrap || ! track ) return;

		// Touch / reduced-motion / narrow → leave the panels as a natural
		// vertical stack (CSS default, matches the 901px breakpoint). Best UX
		// on phones, and forms stay fully scrollable.
		if ( isTouch || reduce || window.innerWidth <= 900 ) return;

		root.classList.add( 'mk-h-on' );
		var progress = document.createElement( 'div' );
		progress.className = 'mk-progress';
		document.body.appendChild( progress );

		var maxX = 0, cur = 0;
		function size() {
			maxX = Math.max( 0, track.scrollWidth - window.innerWidth );
			// body height mirrors the track length so the native scrollbar maps 1:1
			document.body.style.height = ( maxX + window.innerHeight ) + 'px';
		}
		size();
		window.addEventListener( 'resize', size );
		if ( 'ResizeObserver' in window ) new ResizeObserver( size ).observe( track );
		window.addEventListener( 'load', size );
		setTimeout( size, 400 ); // re-measure once media/fonts settle

		// keyboard + anchor helpers: map a panel index to a scroll position
		var panels = [].slice.call( track.querySelectorAll( '.mk-panel' ) );
		wrap.addEventListener( 'click', function ( e ) {
			var jump = e.target.closest( '[data-mk-goto]' );
			if ( ! jump ) return;
			var i = parseInt( jump.getAttribute( 'data-mk-goto' ), 10 ) || 0;
			var p = panels[ i ];
			if ( p ) window.scrollTo( { top: clamp( p.offsetLeft, 0, maxX ), behavior: 'smooth' } );
		} );

		// stoic 3D depth: panels ease through space as they pass the centre —
		// a restrained rotateY + scale + dim, scaled by the Magic intensity.
		var depthOn = ! reduce && ( intensity > 0 );
		( function frame() {
			raf( frame );
			var y = window.scrollY || window.pageYOffset || 0;
			cur = lerp( cur, clamp( y, 0, maxX ), 0.1 );
			if ( Math.abs( cur - y ) < 0.4 ) cur = y;
			track.style.transform = 'translate3d(' + ( -cur ) + 'px,0,0)';
			progress.style.transform = 'scaleX(' + ( maxX ? cur / maxX : 0 ) + ')';

			if ( depthOn ) {
				var I = intensity / 100, vw = window.innerWidth;
				for ( var pi = 0; pi < panels.length; pi++ ) {
					var pnl = panels[ pi ];
					var d = clamp( ( pnl.offsetLeft - cur + pnl.offsetWidth / 2 - vw / 2 ) / vw, -1, 1 );
					var ad = Math.abs( d );
					// stoic ceilings: ≤5° turn, ≤5% shrink, ≤42% dim — calm, not flashy
					pnl.style.transform = 'perspective(1600px) rotateY(' + ( -d * 5 * I ).toFixed( 2 ) + 'deg) scale(' + ( 1 - ad * 0.05 * I ).toFixed( 3 ) + ')';
					pnl.style.opacity = ( 1 - ad * 0.42 ).toFixed( 3 );
				}
			}
		} )();
		return;
	}

	/* ---------- VERTICAL (default) — nothing to do ---------- */
})();
