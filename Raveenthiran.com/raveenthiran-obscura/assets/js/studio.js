/* =========================================================
   Studio — scroll interactions for the light editorial theme.
   Dependency-free. Shared behaviours (mobile drawer, modals,
   data-copy, portfolio filter, quote, compare, map) stay in
   theme.js; this only adds what the scrolling layout needs:
     · header solid-on-scroll
     · IntersectionObserver reveals
     · scroll-to-top
   ========================================================= */
(function () {
  'use strict';
  var doc = document;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- header: solid + hide-on-scroll-down ---------- */
  var head = doc.querySelector('[data-head]');
  if (head) {
    var lastY = window.pageYOffset || 0;
    var solidAt = head.classList.contains('st-head--over') ? 40 : 8;
    var onScroll = function () {
      var y = window.pageYOffset || doc.documentElement.scrollTop || 0;
      head.classList.toggle('is-solid', y > solidAt);
      // hide when scrolling down past the hero, show when scrolling up
      if (y > 320 && y > lastY + 4) head.classList.add('is-hidden');
      else if (y < lastY - 4 || y < 320) head.classList.remove('is-hidden');
      lastY = y;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- reveals ----------
     Any [data-reveal], plus a sensible default set, fades/rises in
     once. Staggered children via [data-reveal-stagger]. */
  var targets = [].slice.call(doc.querySelectorAll(
    '[data-reveal], .st-work__item, .st-feature, .st-approach__item, ' +
    '.st-journal__item, .st-strip__item, .nr-card, .st-project__figure'
  ));
  targets.forEach(function (el) { el.classList.add('st-r'); });

  if (reduce || !('IntersectionObserver' in window)) {
    targets.forEach(function (el) { el.classList.add('is-in'); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        var stagger = el.parentElement && el.parentElement.hasAttribute('data-reveal-stagger');
        if (stagger) {
          var kids = [].slice.call(el.parentElement.children).indexOf(el);
          el.style.transitionDelay = Math.min(kids * 70, 420) + 'ms';
        }
        el.classList.add('is-in');
        io.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    targets.forEach(function (el) { io.observe(el); });
  }

  /* ---------- scroll to top ---------- */
  doc.addEventListener('click', function (e) {
    var t = e.target.closest && e.target.closest('[data-scroll-top]');
    if (!t) return;
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
  });

  /* ---------- mark ready (enables CSS transitions post-load) ---------- */
  requestAnimationFrame(function () { doc.documentElement.classList.add('st-ready'); });
})();
