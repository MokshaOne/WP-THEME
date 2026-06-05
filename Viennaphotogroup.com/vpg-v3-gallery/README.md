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

## Coverage — the whole theme is now exact Gallery

Every template has been rebuilt to the Gallery `g-` system:

- **Signature screens:** `header.php`, `footer.php`, `front-page.php`,
  `index.php`, `single.php`, `page.php`, `search.php`, `404.php`.
- **CPT archives:** magazine (cover lead + back-issue grid), location/map
  (`g-phero` + Gallery cards over the **live Leaflet map** — the map, filter
  toolbar and `#vpg-map[data-pins]` hooks are preserved), event, review,
  tutorial. (shop/studio archives stay 301 redirects to the unified map.)
- **CPT singles:** event, review, tutorial, location, studio, shop (the three
  place types keep their single-pin `#vpg-map`).
- **Page templates (all 15):** about, team, faq, contact, membership, join,
  submit, newsletter, login, dashboard, imprint, privacy, terms, archive,
  map-guide. All forms keep their `method`/`action`, `wp_nonce_field`, hidden
  `action` field and every field `name`; dashboard gating, member queries,
  `wp_login_form()` and the map-guide map hooks are intact.

Every theme PHP file passes `php -l`.

### One documented exception

`single-vpg_magazine.php` (the issue **reader**) keeps its bespoke layout. It is
tied to the page-scoped `assets/css/pages/magazine.css` and the sticky table-of-
contents JS (`#vpg-mag-toc`, `#vpg-mag-sticky-toc`, `.vpg-mag-article[id]`,
`data-vpg-sticky`). It re-themes on-brand through the token remap (white / red /
Archivo) and its action buttons were swapped to `g-btn`. Rebuilding it fully to
`g-` markup would mean porting that scoped CSS + retargeting the JS — a deliberate
follow-up, kept out of this pass to avoid breaking the reader.

The static prototype pages in `/prototype` remain the design reference.

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
