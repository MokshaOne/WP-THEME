/* Work archive: client-side category filter + grid/index view toggle.
   Data is baked into the page at build time, so this is instant and offline. */
(function () {
	'use strict';
	var root = document.querySelector('[data-work]');
	if (!root) return;

	var filterBtns = [].slice.call(root.querySelectorAll('.filter-btn'));
	var viewBtns = [].slice.call(root.querySelectorAll('.view-toggle button'));
	var grid = root.querySelector('[data-grid]');
	var index = root.querySelector('[data-index]');
	var empty = root.querySelector('[data-empty]');
	var countEl = root.querySelector('[data-count]');
	var current = '*';

	function apply() {
		var shown = 0;
		[grid, index].forEach(function (container) {
			[].slice.call(container.children).forEach(function (item) {
				var match = current === '*' || item.getAttribute('data-cat') === current;
				item.hidden = !match;
				if (match && container === grid) shown++;
			});
		});
		if (countEl) countEl.textContent = shown;
		if (empty) empty.hidden = shown !== 0;
	}

	filterBtns.forEach(function (b) {
		b.addEventListener('click', function () {
			current = b.getAttribute('data-filter');
			filterBtns.forEach(function (x) { x.classList.toggle('is-on', x === b); });
			apply();
			try { history.replaceState(null, '', current === '*' ? location.pathname : '#' + current); } catch (e) {}
		});
	});

	viewBtns.forEach(function (b) {
		b.addEventListener('click', function () {
			var v = b.getAttribute('data-view');
			viewBtns.forEach(function (x) {
				var on = x === b;
				x.classList.toggle('is-on', on);
				x.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			grid.hidden = v !== 'grid';
			index.hidden = v !== 'index';
		});
	});

	// deep-link: /work/#editorial preselects a category
	var hash = (location.hash || '').replace('#', '');
	if (hash) {
		var match = filterBtns.find(function (b) { return b.getAttribute('data-filter') === hash; });
		if (match) match.click();
	}
})();
