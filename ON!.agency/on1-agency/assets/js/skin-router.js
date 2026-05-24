/* on1.agency · skin router
 * - Picker: builds pills from window.ON1_SKINS (set by PHP)
 * - Visibility: toggles which .on1-skin is visible based on body[data-preset]
 * - Subpage nav: in-skin routing via [data-skin-link] / [data-skin-page]
 * - localStorage persistence
 */
(function () {
  'use strict';

  var KEY      = 'on1_preset';
  var PRESETS  = (window.ON1_SKINS && window.ON1_SKINS.length) ? window.ON1_SKINS : [];

  // ─── Visibility ─────────────────────────────────────────────
  // When body[data-preset="X"] is set, show .on1-skin--X and hide
  // .on1-default. Otherwise show .on1-default.

  function applyPreset(id) {
    if (id) document.body.setAttribute('data-preset', id);
    else    document.body.removeAttribute('data-preset');

    try { localStorage.setItem(KEY, id); } catch (e) {}

    document.querySelectorAll('.design-picker__opt').forEach(function (b) {
      b.classList.toggle('is-active', b.getAttribute('data-preset') === id);
    });
  }

  // Restore on load (so the choice persists across pages)
  var saved = '';
  try { saved = localStorage.getItem(KEY) || ''; } catch (e) {}
  if (saved) document.body.setAttribute('data-preset', saved);

  // ─── Picker pills ───────────────────────────────────────────

  var picker = document.getElementById('on1-design-picker');
  if (picker && PRESETS.length) {
    var list = picker.querySelector('.design-picker__list');
    if (list) {
      list.innerHTML = PRESETS.map(function (p) {
        var active = (p.id === saved) ? ' is-active' : '';
        // Swatch = pick 1-3 colors, render as a small dot/gradient
        var swatch = '';
        if (p.swatch && p.swatch.length) {
          if (p.swatch.length === 1) {
            swatch = 'background:' + p.swatch[0];
          } else if (p.swatch.length === 2) {
            swatch = 'background:linear-gradient(135deg,' + p.swatch[0] + ' 0%,' + p.swatch[1] + ' 100%)';
          } else {
            swatch = 'background:linear-gradient(135deg,' + p.swatch[0] + ' 0%,' + p.swatch[1] + ' 50%,' + p.swatch[2] + ' 100%)';
          }
        }
        return (
          '<button type="button" class="design-picker__opt' + active +
          '" data-preset="' + p.id + '" aria-label="Switch to ' + p.label + '">' +
            '<span class="design-picker__swatch" style="' + swatch + '"></span>' +
            '<span>' + p.label + '</span>' +
          '</button>'
        );
      }).join('');

      list.querySelectorAll('.design-picker__opt').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = btn.getAttribute('data-preset');
          // Clicking the active pill returns to on1 default
          if (btn.classList.contains('is-active')) id = '';
          applyPreset(id);
        });
      });
    }
  }

  // ─── In-skin subpage routing ────────────────────────────────
  // Any .on1-skin containing [data-skin-link] nav + [data-skin-page]
  // sections gets click-to-switch behaviour. Scoped per skin.

  document.querySelectorAll('.on1-skin').forEach(function (skin) {
    var links = skin.querySelectorAll('[data-skin-link]');
    var pages = skin.querySelectorAll('[data-skin-page]');
    if (!links.length || !pages.length) return;

    function show(page) {
      pages.forEach(function (p) {
        p.classList.toggle('is-active', p.getAttribute('data-skin-page') === page);
      });
      links.forEach(function (a) {
        a.classList.toggle('is-active', a.getAttribute('data-skin-link') === page);
      });
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    links.forEach(function (a) {
      a.style.cursor = 'pointer';
      a.addEventListener('click', function (e) {
        e.preventDefault();
        show(a.getAttribute('data-skin-link'));
      });
    });
  });

  // ─── FAQ accordion (legacy default theme behaviour) ─────────
  document.querySelectorAll('.faq__item').forEach(function (item) {
    item.addEventListener('click', function () {
      item.classList.toggle('is-open');
    });
  });
})();
