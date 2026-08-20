/* Minimal dependency-free lightbox for the project gallery.
   Click a figure → full-screen; ← → / swipe to navigate; Esc closes. */
(function () {
	'use strict';
	var gallery = document.querySelector('[data-gallery]');
	var lb = document.querySelector('[data-lb]');
	if (!gallery || !lb) return;

	var figures = [].slice.call(gallery.querySelectorAll('figure'));
	var stage = lb.querySelector('[data-lb-stage]');
	var count = lb.querySelector('[data-lb-count]');
	var i = 0, lastFocus = null;

	function render() {
		var f = figures[i];
		var src = f.getAttribute('data-src');
		var label = f.getAttribute('data-label') || '';
		stage.innerHTML = src
			? '<img class="lb__img" src="' + src + '" alt="">'
			: '<div class="lb__ph">' + (label || (i + 1)) + '</div>';
		count.textContent = (i + 1) + ' / ' + figures.length;
		// preload neighbours
		[i + 1, i - 1].forEach(function (n) {
			var g = figures[(n + figures.length) % figures.length];
			var s = g && g.getAttribute('data-src');
			if (s) { var im = new Image(); im.src = s; }
		});
	}
	function open(n) {
		i = n; lastFocus = document.activeElement;
		render(); lb.classList.add('is-open'); lb.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden'; document.body.classList.add('lb-open');
	}
	function close() {
		lb.classList.remove('is-open'); lb.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = ''; document.body.classList.remove('lb-open');
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}
	function step(d) { i = (i + d + figures.length) % figures.length; render(); }

	figures.forEach(function (f, n) {
		f.setAttribute('tabindex', '0');
		f.addEventListener('click', function () { open(n); });
		f.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(n); } });
	});
	lb.querySelector('[data-lb-close]').addEventListener('click', close);
	lb.querySelector('[data-lb-prev]').addEventListener('click', function () { step(-1); });
	lb.querySelector('[data-lb-next]').addEventListener('click', function () { step(1); });
	lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
	document.addEventListener('keydown', function (e) {
		if (!lb.classList.contains('is-open')) return;
		if (e.key === 'Escape') close();
		else if (e.key === 'ArrowLeft') step(-1);
		else if (e.key === 'ArrowRight') step(1);
	});
	// touch swipe
	var x0 = null;
	lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
	lb.addEventListener('touchend', function (e) {
		if (x0 === null) return;
		var dx = e.changedTouches[0].clientX - x0;
		if (Math.abs(dx) > 40) step(dx < 0 ? 1 : -1);
		x0 = null;
	}, { passive: true });
})();
