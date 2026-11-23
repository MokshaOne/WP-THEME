/* Booking picker — variable configurator wired to the Cal.com inline embed.
 *
 * Pick shooting type → hours (bounded per type) → add-on packages → live price
 * (rate × hours + packages + licence + travel). "Pick a time" mounts the
 * matching per-type Cal calendar via window.nrCal.mountInline(), passing the
 * chosen hours as the booking duration (hours × 60 min) and the full scope as
 * notes + metadata so it carries through Cal → n8n → CRM.
 *
 * Depends on cal-embed.js (window.nrCal). Re-mounts the calendar when the scope
 * changes while it is open (duration/availability and the carried scope stay in
 * sync); a short debounce keeps that from thrashing.
 */
(function () {
	'use strict';

	function init( root ) {
		var cur   = root.getAttribute( 'data-currency' ) || '€';
		var sumEl = root.querySelector( '[data-cp-sum]' );
		var bdEl  = root.querySelector( '[data-cp-breakdown]' );
		var hoursEl = root.querySelector( '[data-cp-hours]' );
		var rangeEl = root.querySelector( '[data-cp-range]' );
		var goBtn = root.querySelector( '[data-cp-go]' );
		var hintEl = root.querySelector( '[data-cp-hint]' );
		var host  = root.querySelector( '[data-cp-host]' );
		var open  = false;
		var remountTimer = null;

		function fmt( n ) { return cur + Math.round( n ).toLocaleString(); }
		function selType() { return root.querySelector( 'input[name="nr_cp_type"]:checked' ); }
		function hours() { return parseInt( hoursEl.textContent, 10 ) || 1; }
		function num( el, attr ) { return el ? ( parseFloat( el.getAttribute( attr ) ) || 0 ) : 0; }

		function setHours( h ) {
			var t = selType();
			var min = t ? ( parseInt( t.getAttribute( 'data-min' ), 10 ) || 1 ) : 1;
			var max = t ? ( parseInt( t.getAttribute( 'data-max' ), 10 ) || 10 ) : 10;
			h = Math.max( min, Math.min( max, h ) );
			hoursEl.textContent = String( h );
			if ( rangeEl ) { rangeEl.min = min; rangeEl.max = max; rangeEl.value = h; }
		}

		/* Scope as line items [{l,a}] — also the source of the breakdown + notes. */
		function items() {
			var out = [], t = selType();
			if ( t ) {
				var rate = parseFloat( t.getAttribute( 'data-rate' ) ) || 0, h = hours();
				out.push( { l: ( t.getAttribute( 'data-label' ) || 'Session' ) + ' · ' + h + 'h', a: rate * h } );
			}
			root.querySelectorAll( 'input[name="nr_cp_extra"]:checked' ).forEach( function ( x ) {
				out.push( { l: x.getAttribute( 'data-label' ) || 'Add-on', a: parseFloat( x.getAttribute( 'data-price' ) ) || 0 } );
			} );
			var lic = root.querySelector( 'input[name="nr_cp_license"]' );
			if ( lic && lic.checked ) out.push( { l: lic.getAttribute( 'data-label' ) || 'Usage license', a: parseFloat( lic.getAttribute( 'data-price' ) ) || 0 } );
			var km = root.querySelector( 'input[name="nr_cp_km"]' );
			if ( km && parseFloat( km.value ) > 0 ) {
				var n = parseFloat( km.value ), rkm = parseFloat( km.getAttribute( 'data-per-km' ) ) || 0;
				out.push( { l: 'Travel ' + Math.round( n ) + ' km', a: n * rkm } );
			}
			return out;
		}

		function total() { return items().reduce( function ( s, i ) { return s + i.a; }, 0 ); }

		function compute() {
			var list = items(), sum = list.reduce( function ( s, i ) { return s + i.a; }, 0 );
			if ( sumEl ) sumEl.textContent = fmt( sum );
			if ( bdEl ) bdEl.textContent = list.map( function ( i ) { return i.l + ' ' + fmt( i.a ); } ).join( '  +  ' );
			var t = selType(), bookable = !!( t && t.getAttribute( 'data-cal-link' ) );
			goBtn.disabled = ! bookable;
			if ( hintEl ) {
				if ( bookable ) { hintEl.hidden = true; }
				else { hintEl.hidden = false; hintEl.textContent = goBtn.getAttribute( 'data-msg-nobook' ) || 'This type is enquiry-only — use the form below.'; }
			}
			return sum;
		}

		/* Build the Cal config from the current scope. */
		function calConfig() {
			var list = items(), h = hours(), t = selType();
			var notes = list.map( function ( i ) { return i.l + ' = ' + fmt( i.a ); } ).join( '; ' ) + '  →  ' + fmt( total() ) + ' est.';
			return {
				duration: String( Math.max( 1, h ) * 60 ),
				notes: notes,
				metadata: {
					nr_type:     t ? t.value : '',
					nr_label:    t ? ( t.getAttribute( 'data-label' ) || '' ) : '',
					nr_hours:    String( h ),
					nr_packages: Array.prototype.slice.call( root.querySelectorAll( 'input[name="nr_cp_extra"]:checked' ) ).map( function ( x ) { return x.value; } ).join( ',' ),
					nr_estimate: cur + Math.round( total() ),
					nr_currency: cur
				}
			};
		}

		function mount() {
			var t = selType(); if ( ! t ) return;
			var link = t.getAttribute( 'data-cal-link' ); if ( ! link ) return;
			if ( ! window.nrCal || ! window.nrCal.mountInline ) return;
			window.nrCal.mountInline( host, link, calConfig() );
		}

		function reveal() {
			if ( goBtn.disabled ) return;
			open = true;
			host.hidden = false;
			goBtn.setAttribute( 'aria-expanded', 'true' );
			mount();
			host.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}

		/* Re-sync an already-open calendar when the scope changes (debounced). */
		function syncOpen() {
			if ( ! open ) return;
			if ( remountTimer ) clearTimeout( remountTimer );
			remountTimer = setTimeout( mount, 350 );
		}

		root.addEventListener( 'change', function ( e ) {
			if ( e.target.name === 'nr_cp_type' ) {
				root.querySelectorAll( '.nr-cal-picker__type' ).forEach( function ( l ) { l.classList.remove( 'is-on' ); } );
				var lab = e.target.closest( '.nr-cal-picker__type' ); if ( lab ) lab.classList.add( 'is-on' );
				setHours( hours() );      // clamp to the new type's bounds
			}
			compute();
			syncOpen();
		} );
		root.addEventListener( 'input', function ( e ) {
			if ( e.target === rangeEl ) { hoursEl.textContent = String( e.target.value ); compute(); syncOpen(); }
			else if ( e.target.name === 'nr_cp_km' ) { compute(); syncOpen(); }
		} );
		root.querySelectorAll( '[data-cp-step]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { setHours( hours() + ( parseInt( btn.getAttribute( 'data-cp-step' ), 10 ) || 0 ) ); compute(); syncOpen(); } );
		} );
		goBtn.addEventListener( 'click', reveal );

		setHours( hours() );
		compute();
	}

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) { fn(); }
		else { document.addEventListener( 'DOMContentLoaded', fn ); }
	}
	ready( function () {
		Array.prototype.slice.call( document.querySelectorAll( '[data-nr-cal-picker]' ) ).forEach( init );
	} );
})();
