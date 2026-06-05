# VPG · v3 — Gallery

The finalized Vienna Photo Group WordPress theme: **all of the v2 backend** with
the new **Gallery** design (crisp white, image-led, Archivo grotesque, one red
accent `#E5341F`, English, magazine-issue framing).

This is a working WordPress theme, not a static prototype. The approved static
prototype is preserved for reference in [`/prototype`](./prototype).

## How the re-skin works

v2 was cleanly layered — all logic lives in `inc/`, all presentation consumes
`--vpg-*` design tokens. So v3 keeps **100 % of the v2 backend** and changes only
the presentation:

| Layer | What happened |
|---|---|
| **Signature screens** | Rebuilt to exact Gallery markup (`g-` classes + `assets/css/gallery.css`): `header.php`, `footer.php`, `front-page.php`, `index.php` (journal), `single.php` (article), `page.php`, `search.php`, `404.php`. |
| **Design tokens** | `assets/css/tokens.css` remapped to Gallery values (white / near-black / red / Archivo / square / hairlines) — **keeping the v2 variable names**. Because every v2 sheet and template reads these tokens, the entire inner UI (7 CPT archives + singles, 15 page templates, dashboard, forms, map) inherits the Gallery look untouched. |
| **Fonts / enqueue** | `inc/enqueue.php` now loads Archivo (variable) and `gallery.css` globally; all Leaflet, page-sheet and PWA logic is unchanged. |
| **Backend** | `inc/*` (CPTs, gating, membership, magazine + PDF, SEO, customizer, setup wizard, submission queue), `assets/js`, `assets/vendor/leaflet`, `manifest.json`, `composer.json` — copied verbatim from v2. |

Nothing from v2 was removed — every CPT, the Leaflet map, member dashboards,
submission flows, the magazine PDF generator and the PWA all still work.

## Still on the v2 structure (re-themed, not yet rebuilt to exact Gallery)

These render on-brand via the token remap, but their **markup** is still v2 — a
good next pass would rebuild them to the exact prototype layouts:

- CPT archives: `archive-vpg_{magazine,location,event,review,tutorial,studio,shop}.php`
  (the location archive carries the live Leaflet map — re-skin carefully).
- CPT singles: `single-vpg_*.php`.
- Page templates: `templates/page-*.php` (about, join, contact, team, faq,
  dashboard, membership, submit, map-guide, legal pages…).

The prototype pages in `/prototype` (magazine, map, journal, about, join) are the
design targets for that pass.

## Install

Copy `vpg-v3-gallery/` into `wp-content/themes/` and activate. Run the bundled
setup wizard (Appearance → it self-prompts) to create pages, menus and rewrites,
exactly as v2. Optionally `composer install` for the PDF generator.

## Notes

- JS-free chrome: mobile nav (`:checked`), FAQ (`<details>`), hovers are CSS-only;
  `prefers-reduced-motion` respected. `assets/js/main.js` adds progressive
  enhancements only and is null-safe.
- Homepage pulls live data: current magazine issue (hero + cover story), recent
  journal posts, and live location / member / article counts in the stats row.
