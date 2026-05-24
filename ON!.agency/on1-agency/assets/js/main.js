/* on1.agency · v4 · main.js
 * - FAQ accordion
 * - Design preset picker (with localStorage persistence)
 */
(function () {
  'use strict';

  // ─── FAQ accordion ──────────────────────────────────────
  document.querySelectorAll('.faq__item').forEach(function (item) {
    item.addEventListener('click', function () {
      item.classList.toggle('is-open');
    });
  });

  // ─── Design preset picker ───────────────────────────────
  var PRESET_KEY = 'on1_preset';
  // 11 design variants of on1.agency for showcase. 4 from sister sites +
  // 7 from x-folder reference themes. Default (no preset) = magenta on1.
  // Click an active preset again to return to default.
  var PRESETS = [
    // sister sites
    { id: 'kavithai',     label: 'Kavithai',     swatch: 'kavithai'     },
    { id: 'm1o',          label: 'M1O',          swatch: 'm1o'          },
    { id: 'raveenthiran', label: 'Raveenthiran', swatch: 'raveenthiran' },
    { id: 'vpg',          label: 'VPG',          swatch: 'vpg'          },
    // x-folder reference themes
    { id: 'darkyn',       label: 'Darkyn',       swatch: 'darkyn'       },
    { id: 'gaudy',        label: 'Gaudy',        swatch: 'gaudy'        },
    { id: 'geroz',        label: 'Geroz',        swatch: 'geroz'        },
    { id: 'growla',       label: 'Growla',       swatch: 'growla'       },
    { id: 'maxreach',     label: 'Maxreach',     swatch: 'maxreach'     },
    { id: 'pixelon',      label: 'Pixelon',      swatch: 'pixelon'      },
    { id: 'revolix',      label: 'Revolix',      swatch: 'revolix'      }
  ];

  function applyPreset(id) {
    if (id) {
      document.body.setAttribute('data-preset', id);
    } else {
      document.body.removeAttribute('data-preset');
    }
    try { localStorage.setItem(PRESET_KEY, id); } catch (e) {}
    document.querySelectorAll('.design-picker__opt').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-preset') === id);
    });
  }

  // Restore saved preset
  var saved = '';
  try { saved = localStorage.getItem(PRESET_KEY) || ''; } catch (e) {}
  if (saved) applyPreset(saved);

  // Build the widget (always-visible sticky bar)
  var picker = document.getElementById('on1-design-picker');
  if (picker) {
    var list = picker.querySelector('.design-picker__list');

    list.innerHTML = PRESETS.map(function (p) {
      var active = (p.id === saved) ? ' is-active' : '';
      return (
        '<button type="button" class="design-picker__opt' + active + '" data-preset="' + p.id + '" aria-label="Switch to ' + p.label + ' preset">' +
          '<span class="design-picker__swatch design-picker__swatch--' + p.swatch + '"></span>' +
          '<span>' + p.label + '</span>' +
        '</button>'
      );
    }).join('');

    list.querySelectorAll('.design-picker__opt').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-preset');
        // Click the active one again to return to on1.agency default
        if (btn.classList.contains('is-active')) id = '';
        applyPreset(id);
      });
    });
  }
})();
