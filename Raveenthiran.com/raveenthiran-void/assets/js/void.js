/* VOID — HUD behaviours for the Obsidian & Gilt presentation layer.
 * Live core-temp readout, local clock, mobile nav, reservation calendar +
 * service cards. The custom cursor and all heavy motion live in magic.js /
 * webgl-hero.js; this file only drives the tactical HUD chrome. */
(function () {
	'use strict';

	/* live core-temp readout (home singularity) */
	var temp = document.getElementById( 'void-core-temp' );
	if ( temp ) {
		setInterval( function () {
			temp.textContent = ( Math.random() * 0.6 + 2.6 ).toFixed( 3 ) + 'K';
		}, 2200 );
	}

	/* archive local clock */
	var clock = document.getElementById( 'void-clock' );
	if ( clock ) {
		var pad = function ( n ) { return String( n ).padStart( 2, '0' ); };
		var tick = function () {
			var d = new Date();
			clock.textContent = pad( d.getHours() ) + ':' + pad( d.getMinutes() ) + ':' + pad( d.getSeconds() );
		};
		tick();
		setInterval( tick, 1000 );
	}

	/* mobile nav toggle */
	var burger = document.getElementById( 'void-nav-burger' );
	var navLinks = document.querySelector( '.void-nav-links' );
	if ( burger && navLinks ) {
		burger.addEventListener( 'click', function () {
			var open = navLinks.classList.toggle( 'is-open' );
			burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}

	/* reservation: calendar day selection */
	var days = document.querySelectorAll( '.void-cal-day' );
	if ( days.length ) {
		var dateInput = document.getElementById( 'void_date_input' );
		var markLabel = document.getElementById( 'void-mark-date' );
		var select = function ( el ) {
			days.forEach( function ( d ) { d.classList.remove( 'is-selected' ); } );
			el.classList.add( 'is-selected' );
			if ( dateInput ) dateInput.value = el.getAttribute( 'data-date' ) || '';
			if ( markLabel ) markLabel.textContent = el.getAttribute( 'data-label' ) || '—';
		};
		days.forEach( function ( d ) { d.addEventListener( 'click', function () { select( d ); } ); } );
		select( document.querySelector( '.void-cal-day.is-today' ) || days[ 0 ] );
	}

	/* reservation: service card active state */
	var services = document.querySelectorAll( '.void-service' );
	services.forEach( function ( s ) {
		s.addEventListener( 'click', function () {
			services.forEach( function ( o ) { o.classList.remove( 'is-active' ); } );
			s.classList.add( 'is-active' );
		} );
	} );
})();
