# Changelog — Obscura (raveenthiran)

## 4.41.0
- Fixed: enquire-form date picker icon is now reliably visible — replaced the
  native glyph with a white SVG (the v4.38 `color-scheme:dark` + `invert(1)`
  combination cancelled out to black).
- IDEAS-NEXT batch (all visitor-facing features opt-in, default off):
  - Conversion: recently-viewed strip, footer newsletter capture (local
    Subscribers CPT + optional Brevo forward), rotating testimonials band,
    "next open dates" line.
  - SEO: aggregateRating + reviews from Testimonials, IndexNow ping on publish
    (+ virtual key file), branded `/feed` description and a dedicated
    `/journal/feed`, Speculation Rules prerender-on-hover, OG audit
    (`article:published_time`/`modified_time`/`section`/`tag`).
  - Editorial: project "Behind the frame" process section, client-logos strip,
    series cover pages (statement + cover image term meta).
  - A11y: `prefers-contrast` + Save-Data / `prefers-reduced-data` handling,
    Tab focus-trap for the ⌘K palette and contact-sheet dialogs.
  - Ops: Theme Settings JSON import/export, content-health dashboard widget
    (missing thumbnails / empty galleries / uncategorised + backup reminder),
    Studio Assistant role.
  - Quality scaffolding: Playwright smoke test + Lighthouse CI config behind a
    manual `workflow_dispatch` action (they need a live WP runtime, so they
    don't gate PRs).
- Deferred with rationale (see docs/ROADMAP-ARCHIVE.md): lookbook PDF export,
  scroll-scrubbed video hero, per-plate diptych variants.

## 4.40.0
- Theme professionalism: `load_theme_textdomain` + `languages/` scaffold,
  block-editor styles (dark Obscura look), minimal `theme.json` (palette/fonts),
  `readme.txt`, this changelog, and a branded `screenshot.png`.

## 4.39.0
- Final review batch: opt-in footer marquee, Studio signature, divider utility,
  self-referencing hreflang, `[nr_faq]` shortcode + FAQPage schema.

## 4.33.0–4.38.0
- 50-item review (see docs/IMPROVEMENTS-50.md): journal redesign as a card rail
  with desktop arrows + fixed two-pane single post; taxonomy.php + search.php;
  journal sitemap/Article/Breadcrumb schema; plate numbers, ghost hero numeral,
  chip counts, pull-quotes/drop-cap, "More notes"; journal OG cards; archive
  meta-descriptions; ⌘K journal search; admin UX (journal columns, theme-health
  dashboard, enquiry CSV export, importer dedupe-by-hash, reset-to-defaults);
  eager LCP card, lazy Leaflet, cache headers; white date-picker icon.

## 4.20.0–4.32.0
- Bulk importer, PWA, Turnstile, before/after slider, WebGL hero, keyword tags +
  multi-filter, video plates, series, OG share cards, PDF estimate, view-transition
  morph, Leaflet map, auto-interlinking, enquiry insights, Google Workspace SMTP,
  theme-side WebP, built-out legal pages, journal redesign.

## 4.0.0
- Clean rebuild as "Obscura": removed dead code + LatePoint, uncropped hero,
  merged Enquire funnel.
