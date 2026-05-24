/* Bauhaus · main.js
 * - Subpage router: any [data-skin-link] toggles which [data-skin-page] is visible
 * - Smooth scroll for in-page #anchor links
 * - Accordion (native <details>) — no JS needed but we add open-by-default for first FAQ item if requested
 * - localStorage persistence for the active page so deep-links survive a reload
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'bauhaus_active_page';

  function applyPage(page) {
    var pages = document.querySelectorAll('[data-skin-page]');
    var links = document.querySelectorAll('[data-skin-link]');
    if (!pages.length || !links.length) return;

    pages.forEach(function (p) {
      p.classList.toggle('is-active', p.getAttribute('data-skin-page') === page);
    });
    links.forEach(function (a) {
      a.classList.toggle('is-active', a.getAttribute('data-skin-link') === page);
    });
    try { localStorage.setItem(STORAGE_KEY, page); } catch (e) {}
  }

  // Restore last viewed page (or default to 'home')
  var saved = '';
  try { saved = localStorage.getItem(STORAGE_KEY) || ''; } catch (e) {}
  if (saved && document.querySelector('[data-skin-page="' + saved + '"]')) {
    applyPage(saved);
  }

  // Wire link clicks
  document.querySelectorAll('[data-skin-link]').forEach(function (a) {
    a.style.cursor = 'pointer';
    a.addEventListener('click', function (e) {
      e.preventDefault();
      applyPage(a.getAttribute('data-skin-link'));
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // Smooth scroll for #-anchor links that aren't skin-page routers
  document.querySelectorAll('a[href^="#"]:not([data-skin-link])').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href').slice(1);
      if (!id) return;
      var target = document.getElementById(id);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Tiny reveal-on-scroll for sections (intersection observer)
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-revealed');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '-10% 0px', threshold: 0.05 });
    document.querySelectorAll('section, header, footer').forEach(function (el) {
      el.classList.add('reveal-init');
      io.observe(el);
    });
  }

  // Legacy FAQ-accordion (.faq__item · for the on1-style markup) + native <details> are both supported
  document.querySelectorAll('.faq__item').forEach(function (item) {
    item.addEventListener('click', function () { item.classList.toggle('is-open'); });
  });
})();
