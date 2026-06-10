# Changelog — Obscura (raveenthiran)

## 4.54.0 — Studio workflow (enquiry-based; no payments)
New `inc/studio-ops.php`.
- **#65 Mini-CRM pipeline** — a stage on every enquiry (New → Quoted → Booked →
  Delivered → Lost): a colour badge column in the enquiry list + a **Pipeline**
  board (Enquiries → Pipeline) where a per-card dropdown moves it along.
- **#66 Follow-up reminders** — a daily cron emails **you** (never the client) a
  digest of enquiries still "New" and unanswered after N days (Theme Settings →
  `nr_followup_days`, default 3).
- **#151 Auto-tag suggestions** — a "Suggested tags" box on projects proposes
  keywords from the title/content; tick the ones you want and they're added on save.
- **#59 Proofing** — on the (password-protected) Delivery page, clients ♥ their
  favourites and hit "Send my selection"; you get the chosen filenames by email.
  Selection persists per delivery, no login.

Marked #171 (bilingual DE/EN) won't-do — the site stays single-language.

## 4.53.0 — Batch 6: GPU transitions (#2 + #3)
- **#2 displacement page transition** — verified as already shipped: the WebGL
  hero (`nr_fx_webgl`, assets/js/webgl-hero.js) dissolves between project hero
  slides with a displacement + chromatic "melt" shader, with the native crossfade
  as a guaranteed fallback. Ticked.
- **#3 particle dispersion on hover** — added to `assets/js/gpu-fx.js`: hovering a
  portfolio card lets its image "assemble" from a coarse particle grid (~520ms,
  one-shot). Gated by the GPU-effects toggle + cinematic level, desktop+hover only.
  Same-origin uploads only; any canvas taint or error silently removes the overlay
  and restores the image (hard 1.2s safety timeout). Opt-in, zero effect when off.

179/200 · 3 skipped. Remaining: #1 OGL grid, #13 drag-canvas, #14 scrollytelling,
#123 scroll-video hero, the commerce/client block, #151, #171.

## 4.51.0 — Batch 6 r2: security & infrastructure (7 items)
New `inc/infra.php`. All off by default.
- **#176 CSP builder** — Theme Settings → Security & infrastructure → off /
  **report-only** (never blocks, logs violations to `nr_csp_log`) / enforce. Sensible
  default policy for the theme's own CDNs (Leaflet, Turnstile, Google Calendar),
  plus an "extra hosts" box; violations beacon to `/nr/v1/csp-report`.
- **#180 GDPR export/erase** — registers WP's native Personal-Data exporter +
  eraser for the theme's enquiries & subscribers (Tools → Export/Erase Personal Data).
- **#91 HTTP 103 Early Hints** — real `headers_send_early_hints()` for the two hero
  fonts where the SAPI supports it; silent no-op otherwise.
- **#106 self-hosted analytics** — cookieless, PII-free logged-out pageview counts
  (opt-in) → "Obscura — analytics" dashboard widget (7-day total + top paths).
- **#93 service worker** — root-scoped, served from `/nr-sw.js` (with
  `Service-Worker-Allowed: /`): offline fallback page + stale-while-revalidate for
  theme assets (opt-in).
- **#94 offline enquiry queue** — submitting the brief while offline stores it and
  auto-replays via fetch on reconnect.
- **#88 virtualised rails** — `content-visibility:auto` on big archives (≥24 cards)
  so off-screen plates skip layout/paint (opt-in).

171/200. Batch 6 remaining = the GPU page-transitions (#1 #2 #3 #13 #14 #123) and
the commerce/client block (#50 #52 #53 #59 #63 #64 #65 #66 #76 #78 #79) + #151 #171.

## 4.50.0 — Batch 6 r1: GPU/canvas effects (4 items)
New **"GPU effects"** toggle (default OFF) loading a separate `assets/js/gpu-fx.js`
only when enabled — zero cost otherwise. All effects respect reduced-motion,
Save-Data, the calm/standard/cinematic switch, and mobile.
- **#4 heat-haze idle shader** — real WebGL fragment shader; after 18 s of
  inactivity a faint heat shimmer rises over the lower hero; any input stops it.
  No WebGL → silently skipped.
- **#5 animated dither field** — a slow 4×4-Bayer dither pattern drifts over the
  canvas at ~11 fps (the "printed" look; intentionally low-fi).
- **#8 metaball cursor trail** — amber blobs merge/dissolve behind the cursor
  (canvas blur+contrast threshold; cinematic level, desktop only).
- **#9 aperture-iris reveal** — the page opens through a camera-iris circle once
  per session.

**How to test:** Theme Settings → Visual effects → **GPU effects** ON (+ Cinematic
motion ON, level *cinematic*). Reload → iris reveal; move the cursor → blob trail;
leave the homepage untouched 18 s → heat shimmer; look closely at the background →
drifting dither. Honesty note: #9 uses clip-path (visually identical, far cheaper
than a WebGL pass); #8 is canvas 2D rather than a fluid sim — same look, fraction
of the cost.

## 4.49.0 — IDEAS-200 Medium tier r2 (19 items) — Medium tier complete
New `inc/mediumwins2.php`, `page-delivery.php` template, JS/CSS.

**Conversion:** multi-step **enquire wizard** (#42, opt-in toggle), **A/B hero CTA**
with per-variant view/click counters (#48), PDF-estimate email **verified already
shipped** in the auto-reply (#51), **Delivery page template** — native page password
+ gallery + per-file download + expiry (#60), newsletter **double opt-in** with
confirm + welcome email (#72).
**Editorial:** case-study section (#62), per-attachment **focal point** applied to
crops (#127), **story mode** plate rhythm (#131), **colour-mood filter** chips
Warm/Cool/Mono computed client-side (#135, opt-in), **lookbook PDF per series** —
memory-safe DCTDecode passthrough, one JPEG at a time, at `/nr-lookbook/<slug>.pdf`
with a download button on the series cover (#124).
**Perf/typography:** art-directed `<picture>` — portrait crop on narrow portrait
screens (#82); **variable-font support** — drop `inter-tight-var.woff2` into
assets/fonts and it takes over all weights (#83) and enables the hero
**weight-morph** at cinematic level (#10). Lighthouse job is now a **real gate**
(#118, removed `|| true`).
**Admin/ops:** **bulk alt-text editor** (Tools → Alt texts, #149), **editorial
calendar** under Journal (#153), **sample-content button** in Theme Settings (#160),
daily **self-test cron** that emails on state change (#187), settings import now
shows a **diff preview** before applying (#191).

**Skipped with rationale:** #34 orchestrator refactor (touching 15 working effects
for an internal clock isn't worth the regression risk) · #87 modulepreload/islands
(theme.js already loads deferred in the footer; an ESM split is build tooling we
deliberately rejected earlier).

**Medium tier complete** (r1+r2). 160/200 shipped overall; what remains is Batch 6
(heavyweight: GPU/WebGL, commerce/portal, offline infra, bilingual) + 4 small skips.

## 4.48.0 — Simplified admin (toggleable)
Hides Posts/Comments/ACF menus + Theme File Editor, removes the stock dashboard
widgets, merges the four theme widgets into one collapsible "Obscura — health &
metrics" widget, trims Feature flags / Tag clusters / Series grid out of the
Appearance menu (quick links instead). Toggle: Theme Settings → "Simplified admin".

## 4.47.0 — IDEAS-200 "Medium" tier, release 1 (24 items)
The low-risk, self-contained half of the Medium tier (the heavier subsystems —
booking wizard, PDF-email, delivery, double-opt-in, importer, calendar, AVIF
art-direction — come in r2). New module `inc/mediumwins.php` + JS/CSS + configs.

**Motion (cinematic-gated):** layered hero parallax (#17), spotlight cursor (#26),
immersive lightbox with pan-zoom + keyboard (#31), video scrubber (#32), film-strip
nav (#33), conservative inertial smooth-scroll (#11), reduced-motion "elegant static"
audit (#39).
**Editorial:** diptych/triptych plate rhythm (#121), cover aspect presets (#129),
series chapter prev/next (#132), `[nr_timeline]` (#133), `[nr_map_all]` (#134),
video transcript block (#170).
**A11y:** forced-colors / Windows-HC polish (#164), WCAG 2.2 target sizes (#167),
cognitive-load flatten under "calm" (#168), RTL stylesheet pass (#174).
**Perf/SEO:** AVIF twins + `<source type=image/avif>` injection (#81), Core Web
Vitals field collection + dashboard readout (#107), conversion funnel counters (#108),
404 redirect-map admin UI with 301/410 (#111).
**Dev:** PHPCS (WordPress) config (#198), Playwright visual-regression spec (#200),
German `de_DE.po` (#172 — compile to `.mo` on the host / via Loco Translate).

Fields added: plate rhythm, cover aspect, video transcript.
**Medium r2 (next):** #10 #42 #48 #51 #60 #62 #72 #82 #83 #87 #118 #124 #127 #131
#135 #149 #153 #160 #187 #191 #34.

## 4.46.0 — IDEAS-200 "Small" tier (49 in one go)
New module `inc/smallwins.php` + JS/CSS, two admin pages, dev/CI configs. Built
and cut with the new one-command build (`bin/nr-build.sh`).

**Motion (cinematic-gated):** click shockwave (#6), idle hero "screensaver" (#19),
spring-release magnetic (#21), mobile section-snap (#20), sound waveform (#30),
compare-slider keyboard + before/after labels (#38).
**Conversion:** lead-score column on enquiries (#43), "similar work" on Enquire
(#45), .ics "add to calendar" (#54), VAT note + rate setting (#55), quote add-ons
verified (#56), "request testimonial" row action (#61), brief autosave/restore
(#80), hold-two-dates (#75 via the date helper).
**Editorial:** project credits (#140), per-project mini-map from coords (#139),
treatment presets (#130), hero focal point (#128), first-plate full-bleed (#122),
contact-sheet toggle (#126), captions toggle (#157), journal kicker/dek (#145),
`[nr_compare]` (#125), `[nr_onthisday]` (#137), `[fn]` footnotes (#143), glossary
tooltips (#144), `[nr_howto]` schema (#115), `[md]` (#147).
**A11y:** SR live-region (#161), landmark/skip hygiene (#162), `?` shortcuts overlay
(#163), `prefers-reduced-transparency` (#165), unified `:focus-visible` ring (#166),
alt-text linter (#150).
**SEO/Perf:** idle link-prefetch (#95), outbound broken-link checker (#112),
`unicode-range` font subsetting confirmed present (#84).
**Ops/Sec/Dev:** tag-clusters page (#152), tokenised draft-preview links (#155),
series consistency grid (#159), spam-strike escalation (#178), settings audit log
(#179), consent counter (#181), self-test dashboard widget (#186), theme error-log
viewer (#189), settings-migration scaffold (#190), feature-flag registry page (#194),
**one-command build** `bin/nr-build.sh` (#195), axe-core workflow job (#169),
off-site-backup note (#192, docs/OPS-NOTES.md).

Settings added: `nr_vat_rate`, `nr_glossary`.
**Not done (still open):** #67 abandoned-quote (needs capture+cron), #70 trusted-by
counts, #85 SVG sprite (icons are text — low value), #182 export-encryption (helper
`nr_encrypt_blob()` shipped, not yet wired into the CSV export).

## 4.45.0 — IDEAS-200 quick wins (25, in one go)
Small, additive, low-risk wins. New module `inc/quickwins.php` + a few hooks,
two reading aids, a print sheet, dev configs and ops docs.

**SEO / feeds:** `og:updated_time` (#103), sitemap **index** `/sitemap-index.xml`
(#99), per-series RSS link + feed query (#138), hreflang-alternates filter hook
for future translations (#173), decoding=async backstop on images (#89).
**Editorial / UX:** related-by-tag **#tag chips** on projects (#136), **setup/gear**
meta field (#156), **reading time + scroll-progress bar** and **auto table of
contents** on journal posts (#141/#142), **pull-quote** styling on projects (#146),
a **print stylesheet** (#148), **"quote of the day"** testimonial on 404 / empty
search (#158).
**Security / ops:** **Subresource Integrity** on Leaflet (#177), **upload size
validation** (#184, 25 MB cap, filterable), a **honeytoken** bait path that soft-bans
scanners (#185), **autoloaded-options size** readout in the health widget (#188),
opt-in **clean-uninstall** (#193, default off).
**Dev / CI:** structured-data lint job in the manual quality workflow (#109), an
**auto-release-on-tag** workflow (#196), a **`bin/nr-bump.sh`** version-bump script
(#197, used to cut this release), and **ESLint/Prettier/Stylelint/EditorConfig**
configs (#199).
**Docs (owner actions, no code completes them):** `docs/OPS-NOTES.md` — Brotli
verification (#86), Cloudflare cache-rules + APO recipe (#92), login-hardening
checklist (#183).

Locale-aware date helper `nr_i18n_date()` added (#175). 25 items ticked in
`docs/IDEAS-200.md`.

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
