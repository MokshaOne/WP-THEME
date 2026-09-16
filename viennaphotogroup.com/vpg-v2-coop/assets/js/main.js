/* VPG v2 · main.js
 * Mobile nav · scroll reveal · marquee pause · current-link · accordions
 */
(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {

    // Mobile nav toggle
    var toggle = document.getElementById('vpg-menu-toggle');
    var nav    = document.querySelector('.vpg-nav');
    if (toggle && nav) {
      toggle.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.textContent = open ? 'Close' : 'Menu';
      });
    }

    // Mark current nav item
    var current = window.location.pathname.replace(/\/$/, '');
    document.querySelectorAll('.vpg-nav a, .vpg-footer__col a').forEach(function (a) {
      try {
        var p = new URL(a.href, window.location.origin).pathname.replace(/\/$/, '');
        if (p && p === current) a.classList.add('is-current');
      } catch (e) {}
    });

    // Pause marquee on hover
    document.querySelectorAll('.vpg-marquee').forEach(function (m) {
      var t = m.querySelector('.vpg-marquee__track');
      if (!t) return;
      m.addEventListener('mouseenter', function () { t.style.animationPlayState = 'paused'; });
      m.addEventListener('mouseleave', function () { t.style.animationPlayState = 'running'; });
    });

    // Auto-flag common patterns as reveal targets
    document.querySelectorAll('.vpg-section .vpg-card, .vpg-section .vpg-stat, .vpg-hero, .vpg-quote, .vpg-section-head, .vpg-mag-toc__list li').forEach(function (el) {
      if (!el.classList.contains('vpg-reveal')) el.classList.add('vpg-reveal');
    });

    // Reveal-on-scroll
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add('is-revealed'); io.unobserve(e.target); }
        });
      }, { threshold: 0.06, rootMargin: '0px 0px -48px 0px' });
      document.querySelectorAll('.vpg-reveal').forEach(function (el) { io.observe(el); });
    } else {
      document.querySelectorAll('.vpg-reveal').forEach(function (el) { el.classList.add('is-revealed'); });
    }

    // Smooth scroll for in-page anchor links
    document.querySelectorAll('a[href^="#"]:not([data-skin-link])').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href').slice(1);
        if (!id) return;
        var t = document.getElementById(id);
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth', block: 'start' }); history.pushState(null, '', '#' + id); }
      });
    });

    // FAQ accordion announce (a11y)
    document.querySelectorAll('.vpg-faq details').forEach(function (d) {
      d.addEventListener('toggle', function () { d.style.transition = 'background var(--vpg-t-fast)'; });
    });

    // ── Sticky-TOC for single-magazine ──
    // Shows the floating mini-TOC once you scroll past the main ToC section.
    // Highlights the article currently in view via IntersectionObserver.
    var sticky = document.getElementById('vpg-mag-sticky-toc');
    var mainToc = document.getElementById('vpg-mag-toc');
    if (sticky && mainToc) {
      // Toggle visibility based on whether the main ToC is in view
      var showSticky = function () {
        var rect = mainToc.getBoundingClientRect();
        var pastMain = rect.bottom < 80;
        sticky.classList.toggle('is-visible', pastMain);
        sticky.setAttribute('aria-hidden', pastMain ? 'false' : 'true');
      };
      showSticky();
      window.addEventListener('scroll', showSticky, { passive: true });

      // Highlight the article currently being read
      var articles = document.querySelectorAll('.vpg-mag-article[id]');
      if ('IntersectionObserver' in window && articles.length) {
        var observer = new IntersectionObserver(function (entries) {
          entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            var id = e.target.id;
            sticky.querySelectorAll('a').forEach(function (a) {
              a.classList.toggle('is-current', a.getAttribute('data-vpg-sticky') === id);
            });
          });
        }, { rootMargin: '-40% 0px -50% 0px', threshold: 0 });
        articles.forEach(function (a) { observer.observe(a); });
      }
    }
  });
}());
