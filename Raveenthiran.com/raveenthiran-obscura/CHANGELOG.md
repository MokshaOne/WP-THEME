# Changelog — Obscura (raveenthiran)

## 4.44.0 — IDEAS-200 Batch 2 + 3 (the lightweight majority)
A large additive release. Two new modules — `inc/seo-extra.php` and
`inc/conversion-extra.php` — plus settings, CSS and JS. Nothing changes the
front-end look unless you opt in; the SEO/feed/schema work is all `<head>`,
feeds and sitemaps.

**SEO / distribution / schema (Batch 3):**
- ImageObject + VideoObject schema for project media, `og:video` for motion (#96/#97/#102).
- Person `@id` + WebPage author/dates + `speakable` for Discover & assistants (#104/#116/#117).
- Canonical consolidation — `noindex,follow` on filtered/sorted archive URLs (#105).
- `en-AT` hreflang alongside en + x-default (#120).
- **JSON Feed 1.1** at `/feed/json` and an **image sitemap** at `/sitemap-images.xml` (#101/#98).
- **WebSub** ping on publish so feeds push instantly (#100).
- Token **`<title>` templates** for projects (`%title% %cat% %year% %site%`) (#114).
- **robots.txt**: both sitemaps + an opt-in **AI-crawler block** (GPTBot/CCBot/…) (#119).
- **Search-term capture** — the ⌘K palette logs queries to `nr_search_log` (#110).
- **IndexNow bulk re-ping** button in the content-health widget (#113).
- **Generalised LCP preload** on single projects/journal/pages (#90).

**Conversion (Batch 2):**
- **Visitor shortlist** — ♥ projects into a tray, send as a pre-filled brief (#41, opt-in).
- **Exit-intent** soft prompt (desktop, once/session, opt-in) (#44).
- **Trust line** + **availability** shown on the Enquire form (#47/#49).
- **Pre-filled brief** from a project (the `?ref` prefill, surfaced) (#46).
- **Referral capture** — `?via=CODE` rides along into the brief (#68).
- **`[nr_packages]`** comparison table + **`[nr_press]`** press wall shortcodes (#69/#71).
- **Seasonal banner** — dismissible, date-bounded (#73).
- **Share** button on projects (Web Share → clipboard) + WhatsApp project ref (#77/#74).

**How to test:** Theme Settings → new **§ Conversion & SEO extras** section (toggles +
packages/banner/title-template/AI-block). Visit `/feed/json` and `/sitemap-images.xml`.
Drop `[nr_packages]` / `[nr_press]` on a page. View a project's source for the new
JSON-LD and `og:video`. Turn on the shortlist/exit-intent toggles and reload a
portfolio page.

**Held for the next pass (deliberately — integration-heavy / asset- or owner-bound):**
booking wizard (#42), lead scoring (#43), A/B CTA (#48), PDF-estimate email (#51),
.ics + VAT + add-ons (#54/#55/#56), delivery page (#60), testimonial request (#61),
case-study (#62), abandoned-quote (#67), newsletter double-opt-in (#72), resume link
(#80); and the perf items that need build tooling / assets / host config: variable
font (#83), subsetting (#84), SVG sprite (#85), Brotli (#86), modulepreload (#87),
CF guide (#92), SW prefetch (#95), AVIF (#81), art-directed picture (#82), CWV
endpoint (#107), funnel (#108), SD-lint CI (#109), redirect-map UI (#111), outbound
link check (#112), HowTo (#115), LHCI gate (#118).

## 4.43.0 — Batch 1 part 2 (lightweight motion) + a v4.42 fix
**Fix:** in v4.42 the motion level (`data-nr-motion`) was written to `<html>` while
the `.nr-cinematic` class is on `<body>`, so the compound CSS selectors never
matched and the tilt / chromatic-aberration / skew / divider styles were inert.
The attribute now lives on `<body>` too — those effects work as intended.

**New lightweight effects** (same opt-in gate: "Cinematic motion" master toggle +
the calm/standard/cinematic switch):
- **Viewfinder bracket** flashes where you press a card/button (#23, standard+).
- **Loading skeleton** shimmer sized to each card until its image loads (#37).
- **Elastic overscroll** rubber-bands the rails past their ends (#15, cinematic).
- **Snap carousel** — gentle scroll-snap on the rails (#16, cinematic).
- **Contextual cursor** shows "drag" over scrollable rails (#28).
- **Directional link underlines** draw in from the side you approach (#27, standard+).
- **Wordmark reveal** wipes in on first load (#25, standard+).
- **Drift-grain** — the film grain slowly translates so it never looks frozen (#35).

**How to test:** Theme Settings → Visual effects → "Cinematic motion" on → open a
portfolio page → press a card (bracket), watch images shimmer-load, scroll a rail to
its end (rubber-band) and notice the snap, hover a prose link (underline draws),
reload (wordmark wipes), and watch the grain drift. The bottom-left switch dials it
calm/standard/cinematic. Items shipped: #15, #16, #23, #25, #27, #28, #35, #37.

**Docs:** `docs/IDEAS-200.md` reorganised — all heavyweight items (every `L`, plus
GPU/WebGL effects, commerce/payment/portal subsystems, offline/edge infra, and large
content/i18n/security systems) moved to a new **Batch 6 (build last)**; IDs stay stable.

## 4.42.0 — IDEAS-200 Batch 1, part 1 (cinematic motion layer)
First slice of the motion/GPU craft batch. **All of it is behind one master
toggle (default OFF)** plus a per-visitor motion switch, so the live site is
unchanged until you opt in.

**How to test**
1. WP admin → Appearance → Theme Settings → **§ Visual effects** → turn on
   **"Cinematic motion"** → Save.
2. Open a **portfolio / archive** page on desktop. A small **"◐ motion"** chip
   appears bottom-left — it sets the level (defaults to *cinematic*, or *calm*
   if your OS has reduce-motion on). Switch between calm / standard / cinematic.
3. Verify, at *cinematic*:
   - **Cards tilt in 3D** with a soft moving glare as you move the cursor (#24).
   - **Hover a card image** → subtle red/blue **chromatic aberration** (#7).
   - **Scroll a horizontal rail fast** → the images **shear** with the motion
     and settle (#12).
   - The mono **eyebrow labels decode/scramble** into place when scrolled in (#22).
   - Any **`[nr_divider]`** hairline **draws in** left-to-right on scroll (#36).
   - On the **Studio** page the **stat numbers count up** split-flap style (#29).
   - A **film-frame counter** (`042 / 100`) sits bottom-right and tracks scroll
     on scrolling pages (#18).
4. Set the switch to **calm** (or enable OS reduce-motion) → the new effects go
   quiet; **standard** keeps the subtle ones (dividers, counter, split-flap) and
   drops the flashy ones. Turn the master toggle off → everything reverts.

Items shipped: #7, #12, #18, #22, #24, #29, #36, #40 (see docs/IDEAS-200.md).
Next sub-batches will tackle the heavier WebGL items (#1 OGL grid, #2 displacement
transition, #14 scrollytelling) with the same opt-in + fallback discipline.

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
