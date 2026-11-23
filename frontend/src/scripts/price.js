/* Enquire — live price estimate. Reads data-* on the form and writes a running
   total into [data-estimate]; also fills hidden estimate/breakdown fields. */
(function () {
	'use strict';
	var form = document.querySelector('[data-price-engine]');
	if (!form) return;
	var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
	var cur = form.getAttribute('data-currency') || '€';
	var outs = [].slice.call(document.querySelectorAll('[data-estimate]'));
	function money(n) { return cur + Math.round(n).toLocaleString('en-US'); }
	function num(el, attr) { return el ? (parseFloat(el.getAttribute(attr)) || 0) : 0; }

	var shown = 0, raf = null;
	function paint(v) { var t = money(v); outs.forEach(function (o) { o.textContent = t; }); }
	function animateTo(total) {
		if (reduce || shown === total) { shown = total; paint(total); return; }
		if (raf) cancelAnimationFrame(raf);
		var from = shown, delta = total - from, t0 = performance.now(), dur = 420;
		(function step(now) {
			var p = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - p, 3);
			paint(from + delta * e);
			if (p < 1) raf = requestAnimationFrame(step); else { shown = total; paint(total); }
		})(t0);
	}
	function compute() {
		var parts = [], total = 0;
		var type = form.querySelector('input[name="project_type"]:checked');
		if (type) { total += num(type, 'data-base'); parts.push(type.value); }
		[].forEach.call(form.querySelectorAll('input[name="addons[]"]:checked'), function (x) { total += num(x, 'data-price'); parts.push(x.value); });
		var lic = form.querySelector('input[name="license"]');
		if (lic && lic.checked) { total += num(lic, 'data-price'); parts.push('Commercial licence'); }
		var km = form.querySelector('input[name="travel_km"]');
		if (km) { var k = parseFloat(km.value) || 0; if (k > 0) { total += k * num(km, 'data-per-km'); parts.push(Math.round(k) + ' km'); } }
		animateTo(total);
		var hid = form.querySelector('[data-estimate-input]'); if (hid) hid.value = money(total);
		var bd = form.querySelector('[data-breakdown]'); if (bd) bd.value = parts.join(' · ');
	}
	form.addEventListener('change', compute);
	form.addEventListener('input', function (e) { if (e.target && e.target.name === 'travel_km') compute(); });
	compute();

	/* compose a pre-filled email on submit (no WP backend needed) */
	var to = form.getAttribute('data-mailto');
	if (to) form.addEventListener('submit', function (e) {
		e.preventDefault();
		var g = function (n) { var el = form.querySelector('[name="' + n + '"]'); return el ? el.value : ''; };
		var type = (form.querySelector('input[name="project_type"]:checked') || {}).value || '';
		var addons = [].slice.call(form.querySelectorAll('input[name="addons[]"]:checked')).map(function (x) { return x.value; }).join(', ');
		var body = ['Name: ' + g('name'), 'Email: ' + g('email'), 'Preferred date: ' + g('preferred_date'), 'Project type: ' + type, 'Add-ons: ' + addons, 'Estimate: ' + g('estimate'), '', g('notes')].join('\n');
		window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent('Project enquiry — ' + (g('name') || '')) + '&body=' + encodeURIComponent(body);
	});
})();
