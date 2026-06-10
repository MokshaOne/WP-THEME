# Obscura — roadmap archive (shipped history)

All the earlier idea/roadmap lists, merged into one file. They are kept for the
record: each tracked a generation of work and is now **complete** (or carried
into the next list). The **live** backlog is `docs/IDEAS-200.md`; the build
method is `docs/OBSCURA-PLAYBOOK.md`.

| Source list | Scope | Status |
|---|---|---|
| IDEAS-100 | original 100 ideas (tiered easy/medium/hard) | ✅ complete |
| ROADMAP-V2 | 50 second-gen items (post-v4.32) | ✅ complete |
| IMPROVEMENTS-50 | structured 50-item review (v4.33–4.39) | ✅ 48 shipped / 2 skipped |
| IDEAS-NEXT | 28 post-review ideas (v4.41) | ✅ 25 shipped / 3 deferred |
| FEATURE-IDEAS | 20 curated impact-to-effort features | ↗ folded into the above |
| AWWWARDS-ROADMAP | strategic gap analysis vs award sites | 📐 reference (strategy) |

Lineage: **IDEAS-100 → ROADMAP-V2 → IMPROVEMENTS-50 → IDEAS-NEXT → IDEAS-200.**

---

<!-- ====================================================== -->
# ◆ Merged from: IDEAS-100.md

# Obscura — 100 ideas to improve the website

For **raveenthiran.com** (Obscura theme · WordPress on easyname · ~200 projects × ~14 photos · international/English).

**Tiers:** 🟢 **Tier 1 — Easy** (minutes → few hours, low risk) · 🟡 **Tier 2 — Medium** (a day → a few, moderate) · 🔴 **Tier 3 — Hard** (multi-day / project-scale / higher risk).
**★** = fast because the code already exists in the theme (just needs wiring).
**Tags:** Perf · SEO · Conv (conversion) · UX · Ops (managing projects) · A11y · Awww (Awwwards-edge) · Content · Sec (security).

> Out of scope per your call: bilingual DE/EN, client proofing galleries. Listed nowhere below.

---

## 🟢 Tier 1 — Easy (1–40)

### Performance
1. **Wire LQIP blur-up into portfolio cards** — `inc/performance.php` already generates `_nr_lqip`; cards don't use it yet. ★ _Perf_
2. **Prefetch a project page on card hover / touchstart** — clicks feel instant. _Perf/UX_
3. **Preload the LCP image on About & Enquire** (like front-page already does). _Perf_
4. **Explicit width/height on every `<img>`** to eliminate layout shift. _Perf_
5. **Minify the shipped `theme.css` / `theme.js`** (build step). _Perf_ → **moved to Tier 3** (build-time / breakage risk)
6. **`content-visibility:auto`** on offscreen rail items. _Perf_
7. **Far-future cache headers** for `/assets` via `.htaccess`. _Perf_ → **moved to Tier 3** (build-time / breakage risk)
8. **Audit `loading`/`decoding`/`fetchpriority`** across all templates. _Perf_
9. **Drop any genuinely-unused font weight** after a usage check. _Perf_
10. **Strip dead CSS selectors** (post-refactor sweep). _Perf_ → **moved to Tier 3** (build-time / breakage risk)

### SEO
11. **Related projects (same category)** block on the single-project page. _SEO/UX_
12. **Unique meta description per project** (from excerpt/first line). _SEO_
13. **ImageObject caption from EXIF** — extend the VisualArtwork schema. ★ _SEO_
14. **JSON / RSS feed** of latest projects. _SEO_
15. **`og:image:alt` + per-page OG title/description** audit. _SEO_
16. **Sitemap polish** — add About/Enquire/legal `lastmod` + priorities. ★ _SEO_
17. **Auto alt-text from project title** when an image alt is empty. _SEO/A11y_
18. **`sameAs` social profiles in schema** (the social settings already exist). ★ _SEO_
19. **Canonical / noindex audit** for paginated archives. _SEO_
20. **Search-console verify + ping sitemap** (verification field already exists). ★ _SEO_

### Conversion
21. **Auto-reply email to the enquirer** — today only the studio is notified; the client gets nothing. _Conv_
22. **Store enquiries as a CPT + admin list** (not just email — nothing is logged today). _Conv/Ops_
23. **Honeypot + simple rate-limit** on the Enquire form. _Conv/Sec_
24. **Click-to-copy email** with a "copied" toast. _Conv/UX_
25. ✅ **WhatsApp / Signal quick-contact** button. _Conv_
26. ✅ **Instagram — curated grid** — Meta deprecated the Basic Display API (Dec 2024), so auto-feeds no longer work; use an admin-managed image+link grid (or per-post embeds). _Conv_
27. ✅ **Footer CTA** ("Start a project →") on content pages. _Conv_
28. **Auto "Currently booking Q_/Q_"** derived from a date setting. _Conv_

### UX / polish
29. **404 page with random project suggestions.** _UX_ ✅ v4.23.0 — random project suggestions on 404
30. **Loading skeletons on the rails.** _UX_
31. **Tab-away title message** ("Come back —") when the tab is inactive. _UX/Awww_
32. **Studio address → maps link + copy button.** _UX_
33. **Keyboard: ←/→ scroll rails, Esc closes overlays** (audit + finish). _UX/A11y_
34. **Correct active-nav state on every template.** _UX_
35. **"Back to start" control** on long rails. _UX_

### Accessibility / quality
36. **Consistent `:focus-visible` rings** on all interactive elements. _A11y_
37. **Publish-time alt-text nudge** for project images. _A11y_
38. **Caption scrim** so text stays legible over bright images. _A11y_
39. **Final `prefers-reduced-motion` audit** across the new motion. _A11y_

### Admin
40. **Projects admin column** showing plate count + "featured" flag. _Ops_

---

## 🟡 Tier 2 — Medium (41–80)

> **Built in v4.17.0:** #60 Journal/blog, #61 year/timeline filter, #72 testimonials rotation, #73 related hover-preview, #75 consent-gated analytics.

> **Built in v4.15.0:** #43, #45, #51, #52, #56, #65, #76, #78, #79. The remaining Tier 2 items were **moved to Tier 3** per request (they need a settings UI, a content model, external keys, or carry build risk).
> **Built in v4.16.0:** Settings-UI fields (WhatsApp #25, Instagram grid #26, footer-CTA #27, press-kit #68, booking link #71), #42 drag-to-reorder projects, #44 bulk feature action. Hamburger hidden on mobile (tab bar covers nav).

### Ops & scale (your 200-project pain)
41. **Bulk project importer** — one project per image folder / ZIP / CSV. ★ _Ops_
42. ✅ **Drag-to-reorder projects** (sets `menu_order`) in admin. _Ops_
43. ✅ **Auto-map EXIF → project fields** (year/camera) on upload. ★ _Ops_
44. ✅ **Bulk "feature on homepage" toggle + rotation scheduling.** _Ops_
45. ✅ **Admin dashboard widget** — recent enquiries, counts, quick links. _Ops_

### Performance / infra
46. **PWA / service-worker shell precache** — near-instant repeat navigation, works offline. _Perf_ ✅ v4.21.0
47. **Critical-CSS inline + async** the main stylesheet (mobile FCP). _Perf_
48. **Cloudflare front + full-page edge cache** rules for easyname. _Perf_
49. **`<picture>` AVIF→WebP→JPEG** wiring (if not already plugin-handled). _Perf_
50. **Virtualized rendering** for very long galleries. _Perf_

### Awwwards-edge (no WebGL)
51. ✅ **Command palette (⌘K)** to jump to any project/page. _Awww/UX_
52. ✅ **"Contact sheet" index overlay** — all projects in a darkroom grid (your *Catalogue Noir* concept). _Awww_
53. **Cursor-peek image preview** on nav hover. _Awww_
54. **True line-split heading reveals** (SplitText-style masks). _Awww_ ✅ v4.25.0 — opt-in (Theme Settings → Line-reveal headings)
55. **Shared-element morph via named view-transitions.** _Awww_ ✅ v4.24.0 — opt-in card→project morph (Theme Settings → Shared-element page morph)
56. ✅ **Pointer/scroll parallax depth layers** on the hero. _Awww_
57. **Custom easing system + refined magnetic interactions.** _Awww_
58. **Opt-in sound design** (hover ticks, slide whoosh) + mute toggle. _Awww_ ✅ v4.25.0 — opt-in, starts muted (Theme Settings → Interface sound)
59. **Animated / generative favicon or logo mark.** _Awww_ ✅ v4.25.0 — opt-in runtime monogram favicon (Theme Settings → Generative favicon)

### Content / features
60. ✅ **Journal / blog** CPT + archive + single. _Content_
61. ✅ **Timeline ("by year") archive view.** _Content_
62. **Map archive view** of project locations. _Content_ ✅ v4.25.0 — [nr_map] shortcode + per-project coords (Leaflet/OSM)
63. **Series / collections** grouping of projects. _Content_ ✅ v4.24.0 — nr_project_series taxonomy + "More from this series" nav
64. **Tag / keyword taxonomy + multi-filter.** _Content_ ✅ v4.23.0 — nr_project_tag taxonomy + multi-select chips (AND with category)
65. ✅ **Instant client-side search** across projects. _Content/UX_
66. **Video plates** in galleries (mp4 / Vimeo). _Content_ ✅ v4.23.0 — video attachments in the gallery render as autoplay loops
67. **Before/after retouch slider.** _Content_ ✅ v4.21.0 — [nr_compare before="" after=""] shortcode
68. ✅ **Downloadable press kit / one-pager.** _Content_
69. **Dynamic per-project OG share cards** (composited image). _SEO/Content_ ✅ v4.24.0 — composited 1200×630 card at /nr-og/<id>.jpg (GD, cached)

### Conversion
70. **Quote → branded PDF estimate** emailed to the visitor. _Conv_ ✅ v4.24.0 — dependency-free PDF attached to the auto-reply when an estimate is present
71. ✅ **Booking calendar** (Cal.com embed or native slots). _Conv_
72. ✅ **Testimonials rotation** with client logos + ratings. _Conv_
73. ✅ **Related projects with hover thumbnails.** _Conv/UX_

### Security / compliance
74. **Turnstile / hCaptcha** on forms. _Sec_ ✅ v4.21.0 — Cloudflare Turnstile, Theme Settings → § Security
75. ✅ **Consent-gated analytics** (GDPR). _Sec_
76. ✅ **Limit login attempts / login hardening.** _Sec_
77. **Two-factor auth for admin.** _Sec_

### SEO advanced
78. ✅ **Review / AggregateRating schema** from testimonials. _SEO_
79. ✅ **ImageGallery schema** per project. _SEO_
80. **Automated internal-linking** between related projects. _SEO_ ✅ v4.25.0 — opt-in DOM-safe the_content linker (Theme Settings → Auto internal linking)

---

## 🔴 Tier 3 — Hard (81–100)

81. **WebGL image transitions** (displacement / morph between projects) — the Awwwards lever. _Awww_ ✅ v4.22.0 — opt-in shader dissolve on the hero (Theme Settings → WebGL hero transitions)
82. **Full shared-element page-morph choreography.** _Awww_ ✅ v4.25.0 — scale/fade root choreography on the #55 morph
83. **WebGL hover-distortion grid.** _Awww_ ✅ v4.25.0 — opt-in SVG displacement on card hover (Theme Settings → Card hover distortion)
84. **Headless WordPress + Next.js / R3F front-end** (re-platform; keeps WP admin). _Architecture_
85. **R3F immersive 3D project mode.** _Awww_
86. **Scroll-scrubbed video hero.** _Awww_
87. **Cursor-driven fluid / particle hero simulation.** _Awww_
88. **Print / licensing e-commerce checkout.** _Business_
89. **Real-time availability sync** (Google Calendar → booking). _Conv_
90. **AI auto-tagging / auto-curation** of the 200-project archive. _Ops/AI_
91. **Personalized project recommendations.** _AI_
92. **Generative cover art per project.** _AI/Design_
93. **Multi-user roles / editorial workflow.** _Ops_
94. **Full installable PWA** with offline galleries. _Perf_
95. **Edge ISR / static export** of all 200 project pages. _Architecture_
96. **Interactive scrollytelling case study** for flagship projects. _Awww_
97. **On-the-fly image pipeline / CDN** (e.g. imgix-style transforms). _Perf_
98. **Membership / gated premium series.** _Business_
99. **AI concierge** that answers enquiry questions + pre-qualifies leads. _Conv/AI_
100. **Native mobile companion app.** _Business_

---

## Recommended next 5 (safe, high-impact, no WebGL)
**#21 auto-reply** · **#1 LQIP on cards (dormant code)** · **#2 prefetch-on-hover** · **#13 EXIF in schema (dormant code)** · **#52 contact-sheet index** (your one signature move).

---

<!-- ====================================================== -->
# ◆ Merged from: ROADMAP-V2.md

# Obscura — Roadmap v2 (50 items)

Second-generation improvement list, drafted after the v4.32.0 review (the first
100-idea roadmap in `IDEAS-100.md` is complete). Items get checked off with the
version that shipped them. Update log at the bottom.

## A — Bugs & real gaps
1. ✅ v4.33.0 — Journal rail capped at 10 entries (pre_get_posts now loads up to 60; project tax rails 48)
2. ✅ v4.33.0 — Footer overlapped article end on journal single (body now clears the fixed footer)
3. ✅ v4.33.0 — Journal missing from sitemap.xml (entries + /journal archive added)
4. ✅ v4.33.0 — Article schema on journal posts (headline/date/author/image)
5. ✅ v4.33.0 — Series archive template (/series/…) — was generic index.php fallback
6. ✅ v4.33.0 — Tag archive template (/tag/…) — one shared taxonomy.php covers 5–7
7. ✅ v4.33.0 — Journal-category archive template (/journal-category/…)
8. ✅ v4.33.0 — search.php: designed mixed results (work + journal + pages) with search form
9. ✅ v4.33.0 — Card excerpt contrast hardened (stronger bottom shade, full-ink excerpt)
10. ⬜ index.php fallback upgraded to reuse the card rail

## B — Design / catalogue identity
11. ✅ v4.33.0 — Plate numbers on work cards (`PL—07`, vertical mono stamp, amber on hover)
12. 
---

<!-- ====================================================== -->
# ◆ Merged from: IMPROVEMENTS-50.md

# Obscura — 50 improvements & update log

Living backlog from the v4.32 review. **All 50 addressed** (48 shipped ✅, 2 intentionally skipped ⏭). Items marked ✅ ship with the version noted.
Grouped A (bugs/gaps) · B (design) · C (perf) · D (SEO) · E (admin/robustness).

## Update log
- **v4.39.0** — Batch 7 (final): D14 opt-in footer marquee, D19 signature on the
  Studio page, D22 amber hairline divider utility, SEO39 self-referencing hreflang
  (en + x-default), SEO40 [nr_faq] shortcode + FAQPage schema. Verified: C25 WebP
  bulk already resumes (skips existing twins), C27 WebGL gated+paused / distortion
  binds cheaply, E49 NR_DISABLE_FEATURES guard. Intentionally NOT done: C28 JS-split
  and C29 critical-CSS — build-tooling refactors with high risk and little gain on
  HTTP/2 + Cloudflare + Brotli (the async-CSS opt-in already covers render-block).
- **v4.38.0** — Batch 6: white calendar icon on the date field (dark color-scheme),
  C10 index.php fallback rebuilt as a card rail, D15 verified (plate EXIF caption
  already hover-only), SEO35 rel=prev/next on paginated archives, E46 "reset all
  settings to defaults" button.
- **v4.37.0** — Batch 5 (perf + onboarding): C24 first rail card loads eager
  (fetchpriority high), C31 Leaflet loads lazily on scroll-in (IntersectionObserver),
  C32 Cache-Control on /projects.json + sitemap, SEO38 verified (sitemap lastmod =
  post_modified), E50 post-activation onboarding admin notice.
- **v4.36.0** — Batch 4 (admin UX): E41 journal admin columns (category + image
  check), E43 light settings normalisation (UID + URL fields), E44 importer
  duplicate-guard by image MD5, E45 "Theme health" dashboard widget, E47 enquiry
  CSV export from the insights widget.
- **v4.35.0** — Batch 3: C23 OG cards for journal, C26 verified (WP image fns emit
  width/height in new templates), SEO34 meta-description fallback for archives +
  taxonomies, SEO36 journal entries added to /projects.json + the ⌘K palette,
  E48 cookie notice skipped server-side once an nr_consent cookie exists.
- **v4.34.0** — Batch 2: D13 hero ghost numeral, D17 chip counts (cat/tag/journal),
  D18 journal pull-quotes + drop-cap, D20+D37 "More notes" related-journal strip,
  D21 confirmed (all cards already share `.nr-hover-frame`), SEO33 breadcrumb
  schema extended to journal + all taxonomy archives.
- **v4.33.0** — Batch 1: A1 journal rail limit (pre_get_posts 60), A2 footer
  overlap on single journal (padding), A3 journal in sitemap, A4 Article schema,
  A5–A7 taxonomy.php (series/tag/cat + journal-cat archives), A8 search.php,
  A9 journal-card contrast shade. Design D11 plate numbers (`PL—07`), D12 journal
  index-card style, D16 confirmed (selection already amber + scrollbar).

---

## 🔴 A — Bugs & gaps
1. ✅ v4.33.0 — Journal rail showed only 10 entries → `pre_get_posts` raises it to 60.
2. ✅ v4.33.0 — Footer overlapped single-journal article end → `padding-bottom`.
3. ✅ v4.33.0 — Journal missing from sitemap → entries + `/journal` added.
4. ✅ v4.33.0 — No Article schema on journal posts → added (headline/date/author/image).
5. ✅ v4.33.0 — No `taxonomy-nr_project_series` → unified `taxonomy.php` (rail layout).
6. ✅ v4.33.0 — No tag archive template → covered by `taxonomy.php`.
7. ✅ v4.33.0 — No journal-category template → covered by `taxonomy.php`.
8. ✅ v4.33.0 — No `search.php` → designed mixed-results rail added.
9. ✅ v4.33.0 — `.nr-card__excerpt` contrast on bright photos → stronger bottom shade.
10. ✅ v4.38.0 — index.php fallback rebuilt as the card rail.

## 📐 B — Design / catalogue identity
11. ✅ v4.33.0 — Plate numbers on work cards (`PL—07`, vertical mono).
12. ✅ v4.33.0 — Journal cards as index cards (amber top-rule, big mono date, subdued image).
13. ✅ v4.34.0 — Ghost numerals in the hero (outlined slide number behind the title).
14. ✅ v4.39.0 — Opt-in footer marquee ticker (Theme Settings → Footer marquee).
15. ✅ v4.38.0 — Verified: plate EXIF caption already reveals on hover (desktop).
16. ✅ v4.33.0 — `::selection` amber + scrollbar polish (verified present).
17. ✅ v4.34.0 — Filter chips with counts (category / tag / journal).
18. ✅ v4.34.0 — Pull-quotes + drop-cap in journal body.
19. ✅ v4.39.0 — Handwritten-style signature on the Studio page (Settings → Signature).
20. ✅ v4.34.0 — "More notes" strip at the journal post end (2 cards + `All entries ↗`).
21. ✅ v4.34.0 — Hover-frame already shared by all `.nr-card` (journal/series/search).
22. ✅ v4.39.0 — `.nr-divider` amber hairline utility + marquee/footer hairlines.

## 🚀 C — Performance & technical
23. ✅ v4.35.0 — OG share cards for journal (`/nr-og/<id>.jpg` now serves `nr_journal`).
24. ✅ v4.37.0 — First rail card eager + fetchpriority=high; rest stay lazy.
25. ✅ v4.39.0 — Verified: the bulk skips already-generated twins, so a re-run resumes.
26. ✅ v4.35.0 — Verified: new templates use WP image fns (width/height emitted).
27. ✅ v4.39.0 — Verified: WebGL enqueued only when on (front page) + pauses offscreen; distortion binds cheaply, rAF only on hover.
28. ⏭ Skipped — not worth it: one Brotli-compressed deferred file beats many requests on HTTP/2; splitting adds complexity/risk.
29. ⏭ Skipped — true critical-CSS needs a build step (FOUC risk); the opt-in async-CSS toggle already removes render-block.
30. `rel=preconnect` to Cloudflare/font origin in `<head>`.
31. ✅ v4.37.0 — Leaflet loaded lazily on scroll-in (no cost on map-less visits).
32. ✅ v4.37.0 — Cache-Control on /projects.json + sitemap (/nr-og already cached).

## 🔍 D — SEO & content
33. ✅ v4.34.0 — Breadcrumb schema on journal + all taxonomy archives.
34. ✅ v4.35.0 — Meta-description fallback for archives + taxonomy/term pages.
35. ✅ v4.38.0 — rel=prev/next emitted on paginated archives/search.
36. ✅ v4.35.0 — Journal added to /projects.json and the ⌘K command palette.
37. ✅ v4.34.0 — Auto "Related journal" (shared category, recent fallback) = the More-notes strip.
38. ✅ v4.37.0 — Verified: sitemap lastmod uses post_modified for projects + journal.
39. ✅ v4.39.0 — Self-referencing hreflang (en + x-default) on every canonical.
40. ✅ v4.39.0 — [nr_faq] shortcode (accordion) + FAQPage schema for journal/pages.

## ⚙️ E — Admin UX & robustness
41. ✅ v4.36.0 — Journal admin columns: category + featured-image check.
42. Featured/Credits section (link collection — awaiting the curated list).
43. ✅ v4.36.0 — Settings normalisation: UID uppercased/trimmed, URL fields get a scheme.
44. ✅ v4.36.0 — Importer reuses an existing attachment when the image MD5 matches.
45. ✅ v4.36.0 — "Theme health" dashboard widget (permalinks, SMTP, WebP, Site Icon, pages…).
46. ✅ v4.38.0 — "Reset all settings to defaults" button (with confirm).
47. ✅ v4.36.0 — Enquiry CSV export (UTF-8 BOM) from the insights widget.
48. ✅ v4.35.0 — Cookie notice skipped server-side when an nr_consent cookie exists.
49. ✅ v4.39.0 — Verified: NR_DISABLE_FEATURES skips all inc modules; core templates still render.
50. ✅ v4.37.0 — Dismissible onboarding admin notice after theme activation.

---

<!-- ====================================================== -->
# ◆ Merged from: IDEAS-NEXT.md

# Obscura — next ideas (post-50 review)

Fresh, forward-looking backlog after the 50-item review (all shipped/closed in
v4.33–4.39). These are NOT rehashes — they're new directions, grouped by theme.
Nothing here is started yet.

## ⚡ Quick wins found in the v4.39 audit — ✅ shipped v4.40.0
1. ✅ **`screenshot.png`** — the theme has no preview thumbnail in Appearance → Themes. Add a 1200×900 hero shot.
2. ✅ **`load_theme_textdomain()` + `languages/raveenthiran.pot`** — the text domain is declared but never loaded, so all `__()` strings aren't actually translatable. (Low urgency for an English-only site, but it's a correctness gap.)
3. ✅ **Editor styles (`add_editor_style`)** — the block editor for Journal/pages is light + system-font; an `editor-style.css` would mirror the dark Obscura look so authoring matches the front end.
4. ✅ **`readme.txt` + `CHANGELOG.md`** — proper theme metadata + a human changelog (currently only the per-version commit log).
5. ✅ **Minimal `theme.json`** — even for a classic theme, a small `theme.json` exposes the Obscura palette/fonts to the block editor color pickers.

## 🎯 Conversion & audience — ✅ shipped v4.41.0
6. ✅ **"Recently viewed projects"** strip (localStorage, no tracking) — footer-extras on scrolling pages. Opt-in (`nr_fx_recent`).
7. ✅ **Newsletter / "new work" email capture** — single-field footer box → private `nr_subscriber` CPT + optional Brevo forward. Opt-in (`nr_fx_newsletter`).
8. ✅ **Testimonials band** — quiet rotating quote (uses the CPT). Opt-in (`nr_fx_testi_band`).
9. ✅ **"Next open dates"** — `nr_avail_dates` settings field rendered as an availability line.
10. ⏭ **Lookbook / portfolio PDF export** — deferred. The dependency-free PDF writer handles a one-page estimate fine, but compositing a multi-image series PDF is memory-heavy on easyname shared hosting (risk of OOM on large series). Revisit if a slimmer image-embed path proves safe.

## 📈 SEO & distribution — ✅ shipped v4.41.0
11. ✅ **AggregateRating + reviews** from the Testimonials CPT → on the Person/LocalBusiness graph.
12. ✅ **Freshness + IndexNow** — `transition_post_status` pings Bing/Yandex on publish; virtual `/<key>.txt` ownership file; `nr_indexnow_key` setting.
13. ✅ **Feed polish** — branded `/feed` description + dedicated `/feed/journal-feed/` + `<link rel=alternate>`.
14. ✅ **Speculation Rules API** (prerender-on-hover, moderate eagerness). Opt-in (`nr_fx_speculation`).
15. ✅ **OG audit** — `article:published_time` / `modified_time` / `section` / `tag` / `author` on journal + projects.

## 🖼 Editorial depth (Awwwards lever)
16. ✅ **Project "process" / behind-the-scenes** — `project_process` ACF field + "Behind the frame" section.
17. ⏭ **Scroll-scrubbed video hero** — deferred. Niche (one flagship), heavy JS, and decoding a scrubbed video is rough on mobile/shared hosting; low ROI vs. risk.
18. ⏭ **Diptych / full-bleed per-plate variants** — deferred. Needs a per-image layout field and a gallery-model rework that would touch the whole rail; out of scope for an additive batch.
19. ✅ **Client logos / "trusted by" strip** — `nr_clients_logos` settings field ("url | name" per line).
20. ✅ **Series cover pages** — `nr_project_series` term meta (statement + cover image) shown atop `taxonomy.php`.

## ♿ A11y & resilience — ✅ shipped v4.41.0
21. ✅ **`prefers-contrast` + Save-Data / `prefers-reduced-data`** — body class from the `Save-Data` header (+ JS fallback), contrast/reduced-data media queries.
22. ✅ **Palette & dialog focus audit** — Tab focus-trap for the ⌘K palette and contact-sheet (Escape already closed them).
23. ✅ **Lighthouse CI** — `lighthouserc.json` budget + manual `workflow_dispatch` job (needs a live URL, so it doesn't gate PRs).
24. ✅ **Visual smoke test** — Playwright `tests/smoke.spec.js` (hero, rail, enquire form + visible date picker), same manual workflow.

## ⚙️ Ops & admin — ✅ shipped v4.41.0
25. ✅ **Settings import/export** (JSON) in Theme Settings → Backup & migration.
26. ✅ **Backup reminder** — folded into the new content-health dashboard widget.
27. ✅ **Content-health report** — missing featured images / empty galleries / uncategorised projects, with edit links.
28. ✅ **Studio Assistant role** — manages Projects/Journal/Testimonials, no settings/plugins/users.

---

**Status:** v4.41.0 shipped 25 of 28 items. Deferred with rationale: #10 (lookbook PDF — memory on shared hosting), #17 (scroll-video hero — niche/risky), #18 (per-plate diptych — needs gallery rework). Next worthwhile direction: revisit #10/#18 only if a flagship project genuinely needs them.

---

<!-- ====================================================== -->
# ◆ Merged from: FEATURE-IDEAS.md

# Obscura — 20 features & ideas worth building

Curated for **raveenthiran.com** (photography portfolio, ~200 projects × ~14 photos,
WordPress on easyname). Ordered by impact-to-effort. Each notes the *why*, rough
effort, and whether it helps **Awwwards (A)**, **conversion/clients (C)**, or
**SEO/perf (S)**.

---

## Tier A — the "$10K experience" layer (do these for Awwwards)

1. **WebGL image transitions between projects** *(A · large)*
   GPU displacement/morph when opening a project — images as textures on planes
   (OGL ~16 KB). The single biggest jury differentiator. Needs on-device tuning;
   ship behind a feature flag with the current `<img>` as guaranteed fallback.

2. **Shared-element page transition** *(A · large)*
   The clicked card flies into the project hero (View Transitions API on same-doc,
   or a FLIP clone). Turns navigation into choreography. Pairs with #1.

3. **Cinematic intro on first load** *(A · medium)*
   Upgrade the preloader into a 2–3s branded title sequence (wordmark draw-in,
   counter, first hero reveal). Sets "this is crafted" in the first seconds.

4. **Custom WebGL hover-distortion on the grid** *(A · medium)*
   Ripple / RGB-shift / liquid warp on portfolio thumbnails under the cursor.
   Desktop-only, reduced-motion-safe.

5. **Kinetic / variable-weight type on the hero** *(A · medium)*
   Animate Inter Tight weight + letter-spacing on load and slide-change
   (line-mask reveals). Type that *moves* reads premium.

---

## Tier C — turn visitors into paying clients

6. **Project inquiry from the project page** *(C · small)*
   "Commission similar" already deep-links to Enquire with the type pre-filled —
   extend it to also pass the project title so the brief starts pre-written.

7. **Client galleries / proofing** *(C · large)*
   Private, password-protected delivery galleries (per client) with favourites +
   download. Replaces Pixieset; keeps clients on your domain. Big retention lever.

8. **Testimonials / press strip** *(C · small)*
   The `nr_testimonial` CPT already exists but isn't surfaced — show a rotating
   quote + client logos on Home/About. Social proof anchors the $10K price.

9. **"Selected clients" + recognition wall** *(C · small)*
   Logos (SZ, NYT Mag, Belvedere…) + awards. The recognition parser already
   exists in `functions.php` — give it a dedicated section.

10. **Multi-step quote → PDF estimate** *(C · medium)*
    Extend the calculator: email the visitor a branded PDF estimate and notify
    you. Moves "price check" into a real lead.

11. **Availability / booking calendar** *(C · medium)*
    A real date-picker on Enquire (Cal.com embed via the iframe slot, or native)
    showing open shoot windows — reduces back-and-forth.

---

## Tier S — SEO, performance, reach

12. **Critical-CSS + async stylesheet** *(S · medium)*
    Inline above-the-fold CSS, load the 74 KB `theme.css` non-blocking. The main
    remaining mobile **FCP** win. Needs care (FOUC) — do with on-device checks.

13. **AVIF images + Cloudflare in front** *(S · small)*
    Add AVIF to the existing WebP pipeline; put Cloudflare (free) before easyname
    for edge caching → lower TTFB + LCP on mobile.

14. **Bilingual DE/EN with hreflang** *(S · medium)*
    You ship German legal pages — go fully bilingual with `hreflang`. Doubles
    organic reach in the DACH market.

15. **Per-project rich SEO** *(S · small)*
    Unique meta titles/descriptions per project + `ImageObject` schema with
    captions/EXIF (camera, lens). Photography-specific SERP wins.

16. **Journal / editorial blog** *(S · medium)*
    A `nr_journal` CPT for behind-the-scenes essays — the content engine that
    earns long-tail search and gives returning visitors a reason.

---

## Tier X — distinctive touches

17. **EXIF "shot on" overlay** *(A/C · small)*
    The EXIF capture already runs on upload — surface camera/lens/ISO as an
    optional caption on plates. Photographers and clients love it.

18. **Sound design (opt-in)** *(A · small)*
    Subtle UI ticks on hover/slide-change with a persistent mute toggle. A
    common Awwwards signature; ship muted by default.

19. **Light/day theme variant** *(A · medium)*
    A bone-white inverse of Catalogue Noir, toggled or time-of-day aware. The
    color system already supports it (tokens are configurable).

20. **Filterable, map, or year-timeline archive views** *(A/C · medium)*
    Beyond the grid: a "by year" timeline or a shoot-location map for 200
    projects — makes a large body of work feel authored, not dumped.

---

### Suggested next three
**#1 WebGL transitions** (the Awwwards lever) → **#12 critical-CSS + #13 Cloudflare**
(lock in mobile speed) → **#7 client galleries** (the feature clients pay for).

---

<!-- ====================================================== -->
# ◆ Merged from: AWWWARDS-ROADMAP.md

# From "nice WordPress theme" to a $10K Awwwards-grade site

A grounded audit of **raveenthiran — Catalogue Noir** against the reference set, and an
honest, phased roadmap to close the gap.

Reference set analysed:
`ibrahemghareib.com` · `kookie-kollective.com` · `victorfuruya.com` · `02px.com`
· `fourmula.ai` · `detroit.paris` (LV Grasse) · `cynx.io` · `boazwalma.com` · `podium.global`

> First diagnostic fact: **every one of those sites returned HTTP 403 to an automated
> fetch.** They're SPAs behind Cloudflare, hydrated by JavaScript, serving WebGL canvases —
> there is no server-rendered HTML document to scrape. Your theme returns a clean,
> crawlable HTML page. That single difference is the whole story in miniature: they are
> *applications that render an experience*; yours is *a document that displays content*.

---

## 1. The honest verdict

Your theme is **not bad** — that's the important part. It is a genuinely well-built
WordPress theme: coherent design system, real performance plumbing (WebP conversion, LQIP
blur-up, EXIF, retina image sizes), strong technical SEO (schema graph, OG/Twitter,
sitemap, breadcrumbs, REST hardening), full editorial control panel. On a scale of
WordPress photography themes it sits in the top ~10%.

But "top 10% of WordPress themes" and "Awwwards Site of the Day" are **different leagues
measured on different axes.** You are not losing on engineering hygiene. You are losing on
the two things Awwwards juries actually reward:

1. **GPU-driven motion & interaction** (the craft-code layer) — you have *none*.
2. **A bespoke creative concept + world-class content** (the art-direction layer).

Everything below makes that concrete.

---

## 2. What the $10K sites have that you don't

These sites share a near-identical technical DNA. None of it is in your codebase today
(verified: `grep` for `webgl|canvas|three|gsap|lenis` in `assets/js/theme.js` → zero hits).

| Layer | What the reference sites do | What you do today |
|---|---|---|
| **Rendering** | Images are **WebGL textures on planes** (Three.js / OGL / R3F). Enables shader transitions, hover distortion, RGB-shift, scroll-curl. | Plain `<img>` tags in the DOM. |
| **Image transitions** | GPU **displacement / morph** between projects (the signature Awwwards move). | CSS cross-fade + Ken Burns. The "nice theme" version. |
| **Scroll** | **Lenis / Locomotive** inertia smooth-scroll + GSAP **ScrollTrigger** choreography (parallax, pin, scrub). | Native browser scroll. Reads as "default". |
| **Page transitions** | **Shared-element morphs** — the clicked image flies into the next page's hero (FLIP / View Transitions / Barba). | A flat amber **wipe** overlay. |
| **Typography** | **Line/char reveal masks** (SplitText), kinetic type, variable-weight animation on scroll. | Static type (alternate-word em-bold is a nice touch, but it doesn't *move*). |
| **Intro** | A **preloader**: 0→100 counter, logo draw-in, orchestrated first paint. Sets the tone in 2s. | None — page just appears. |
| **Micro-interaction** | Magnetic buttons, cursor that *reacts to content*, marquees, sticky-skew, custom easing curves. | Custom cursor exists (good!) but it's label-swap only, not magnetic/physics. |
| **Concept** | A **singular creative idea** per site + art-directed, rule-breaking grid. | One reusable template applied uniformly to every project (a *catalogue*, by design). |
| **Stack** | Next.js / Nuxt / SvelteKit + headless CMS, on Vercel/Netlify edge. | Classic PHP WordPress theme. |

**The uncomfortable 50%:** Awwwards' public scoring is **Design 40 / Usability 30 /
Creativity 20 / Content 10**. Roughly half the score is *concept + content + photography* —
things no theme can supply. `detroit.paris`'s Louis Vuitton Grasse page wins because it's a
bespoke immersive narrative built around extraordinary assets, not because of a slider
widget. **A reusable theme is, by definition, the opposite of a bespoke concept.** This is
the deepest reason a templated site struggles to win SOTD.

---

## 3. Concrete findings in *your* code (the fixable list)

Real issues found reading the `Theme-folder` branch — these are the gap, made specific:

1. **Zero GPU layer.** `assets/js/theme.js` (497 lines) is vanilla DOM/IO/rAF. No Three.js,
   no shaders, no GSAP, no Lenis. This is the #1 thing standing between you and Awwwards.
2. **No `srcset` on the heaviest images.** `front-page.php` hero and `single-nr_project.php`
   plates output a single-resolution `<img src>`. A phone downloads a 2400×1600 hero. (Your
   archive cards *do* get srcset via `wp_get_attachment_image` — so the fix is to use that
   helper everywhere, or hand-build `srcset`.) Direct LCP win.
3. **Render-blocking fonts.** You load **6 weights of Inter Tight + 2 of JetBrains Mono**
   from Google Fonts. Self-host as `woff2`, subset to Latin, preload the 2 above-the-fold
   weights, drop the rest. Saves 200–400ms to first paint.
4. **Dead duplication.** `next-level/assets/theme.css` and `theme.js` are **byte-identical**
   copies of `assets/`. Either it's the real build target (then wire it up) or it's dead
   weight shipping to users. Remove or consolidate.
5. **Page-wipe ≠ transition.** The amber wipe in `footer.php` hides a full page reload. It
   *feels* like a transition but it's a curtain over a hard navigation. Real sites keep the
   canvas alive across routes.
6. **Sitemap has no images.** `inc/seo.php` emits a clean URL sitemap but no
   `<image:image>` entries — a missed image-SEO win for a *photography* site with 200×14
   photos. Add an image sitemap.
7. **Placeholder/sample data baked into templates.** `front-page.php` and
   `archive-nr_project.php` ship hardcoded "Nachtdienst / Naschmarkt" samples. Fine as
   empty-state, but a tell that the *real* hero content isn't driving the design yet.
8. **Hero images are double-cropped — original composition is lost.** The front-page hero is
   cropped *twice*: (a) on upload, `add_image_size('nr-hero', 2400, 1600, true)` — that
   `true` **hard-crops every photo to 3:2**, chopping portraits/squares; (b) in CSS,
   `.nr-hero__plate img{ object-fit:cover }` crops *again* to fill the frame. The result: the
   displayed picture is not shown in its true crop ("not in frame"). **Fix = serve an
   uncropped source + `object-fit:contain` (or size the frame to each image's real aspect
   ratio).** See Phase 4 task. For a photography site, showing the photographer's *actual*
   composition is not optional — it's the product.

None of these are embarrassing. #1 is the league change; #2–#4 and #8 are quick wins.

---

## 4. Where you already meet the bar (keep these — they're assets)

- **A real, restrained design system.** Catalogue Noir (warm near-black, one amber accent,
  Inter Tight + JetBrains Mono) shows taste. Most failed Awwwards entries lack exactly this.
- **Performance plumbing.** WebP auto-convert, LQIP blur-up, EXIF capture, retina image
  sizes. Genuinely above-average.
- **Technical SEO.** schema.org `@graph`, `VisualArtwork` per project, FAQPage, breadcrumbs,
  OG/Twitter, robots, REST hardening. This is *better* than most agency builds.
- **Editorial control.** 730-line Theme Settings panel = non-technical content management.
- **Accessibility basics.** Skip link, ARIA roles, focus management, reduced-motion respect.

You're not starting from zero. You're starting from a strong *foundation* that's missing its
*show layer*.

---

## 5. The architecture decision (this is the real fork)

You said: *changing from WordPress is fine, as long as managing ~200 projects × 14 photos
stays easy.* That constraint is the deciding factor. Three honest paths:

### Option A — Headless WordPress + Next.js front-end ⭐ recommended
Keep WordPress as the **admin/CMS only** (you keep the media library, ACF, the 200×14
workflow you already know). Expose content via **WPGraphQL**. Build a new front-end in
**Next.js + React-Three-Fiber + GSAP + Lenis**, deployed on **Vercel** edge.
- ✅ Content management stays exactly as easy as today (WP admin).
- ✅ Framework-grade motion, WebGL, shared-element transitions, edge performance.
- ✅ This is *literally how cynx / podium / fourmula-class sites are built.*
- ⚠️ Two systems to run; a real front-end build, not a weekend.
- **Best fit for you. Easy content + Awwwards ceiling.**

### Option B — Stay full WordPress, bolt on the WebGL layer
Add Three.js/OGL + Lenis + GSAP into the existing theme; build the preloader, smooth-scroll,
shader image transitions inside `theme.js`.
- ✅ Smallest migration, one system, keep everything you built.
- ⚠️ You fight WP's full-page-reload model for transitions (the canvas dies on navigation).
- ⚠️ TTFB/perf ceiling is lower than a static/edge front-end.
- **Can reach "Honorable Mention / nominee." Hard to reach SOTD.**

### Option C — Static (Astro/Nuxt) + headless CMS (Sanity/Storyblok)
Cleanest performance; structured content modeling for 200×14 is excellent in Sanity.
- ✅ Best Lighthouse/Core Web Vitals; great DX.
- ⚠️ You abandon the WP admin you know; content re-modeling + migration of 200 projects.

**Recommendation: Option A.** It's the only path that satisfies *both* of your constraints —
"managing 200×14 stays easy" (WordPress admin) **and** "win on Awwwards" (Next + WebGL front
end). Option B is the pragmatic stepping-stone if you want to validate the motion first
without re-platforming.

---

## 6. The roadmap — phased, prioritised

### Phase 0 — Concept & content *(do this first; it's 50% of the score)*
- [ ] Define **one creative concept** for the site (a verb, a metaphor, a constraint). Not "a
      portfolio" — e.g. "a darkroom you scroll through", "contact sheets that develop on
      hover". Awwwards rewards the *idea*.
- [ ] Curate ruthlessly. **8–12 hero projects**, not 200, drive the design. The 200 live in a
      deeper archive. Juries judge the first screen and 3 clicks.
- [ ] Get the photography to hero grade (sequencing, retouch, consistent color). No motion
      saves weak images.
- [ ] Write editorial copy with a voice. Captions, project intros, an about with a point of view.

### Phase 1 — Motion foundation *(works on current WordPress — Option B start)*
- [ ] Add **Lenis** smooth-scroll.
- [ ] Add **GSAP + ScrollTrigger**; scroll-linked reveals, parallax depth, pinned sections.
- [ ] **SplitText** line/char reveal masks on every heading.
- [ ] Build a **preloader** (0→100, logo draw-in, gate first paint).
- [ ] Upgrade the cursor to **magnetic** (lerp toward target, scale on hover, snap to CTAs).
- [ ] Replace `ease` with bespoke cubic-beziers / custom easing. Motion identity.

### Phase 2 — The WebGL layer *(the league change)*
- [ ] Render hero + grid images as **WebGL textures** (OGL is lightest; R3F if on Next).
- [ ] **Displacement/morph transition** between projects (the signature move).
- [ ] **Hover distortion** (ripple / RGB-shift / curl) on grid images.
- [ ] **Scroll-velocity skew** on the grid (images lean into momentum).
- [ ] Lazy-init WebGL; static `<img>` fallback for no-WebGL / reduced-motion / crawlers.

### Phase 3 — Choreographed navigation
- [ ] **Shared-element page transition** — clicked card morphs into the project hero (FLIP /
      View Transitions API on same-doc, or framework router transition on Next).
- [ ] Keep the WebGL canvas **alive across routes** (the reason Option A wins here).
- [ ] Retire the amber wipe (or keep only as reduced-motion fallback).

### Phase 4 — Performance & SEO hardening *(the Awwwards "develop" score + Google)*
- [ ] **Self-host fonts** (woff2, Latin subset, `font-display:swap`, preload 2 weights).
- [ ] **`srcset` + `sizes` on hero and project plates** (fix finding #2). Serve **AVIF**, WebP
      fallback.
- [ ] **Show hero images in their original crop** (fix finding #8). Stop force-cropping: change
      the hero size to a soft fit — `add_image_size('nr-hero', 2400, 1600, false)` (or serve
      `full`) — and set `.nr-hero__plate img{ object-fit:contain }`, letterboxing against the
      near-black canvas. Optionally drive the frame's `aspect-ratio` from each image's real
      dimensions so portrait heroes get a portrait frame. The photographer's composition must
      survive to the screen intact.
- [ ] Inline **critical CSS**; defer the rest. Defer/async all non-critical JS.
- [ ] Put **Cloudflare** in front; full-page edge cache (WP needs a cache plugin; headless
      gets it free).
- [ ] Targets: **LCP < 2.0s · INP < 200ms · CLS < 0.05.** Test on throttled mobile.
- [ ] **Image sitemap** with `<image:image>` per project (fix finding #6).
- [ ] Per-project unique titles + meta descriptions; `ImageObject` schema with caption/EXIF.
- [ ] `hreflang` if DE/EN (you ship German legal pages — you're likely bilingual).

### Phase 5 — Re-platform *(if going for SOTD — Option A)*
- [ ] Stand up **WPGraphQL** on the existing WordPress.
- [ ] Scaffold **Next.js + R3F + GSAP + Lenis**; map CPTs (`nr_project`, gallery, ACF) to
      queries.
- [ ] Migrate Phases 1–3 motion into the framework (where it's *easier*, not harder).
- [ ] Deploy on **Vercel**; point the domain; keep WP admin private as the CMS.

### Phase 6 — Win the award (it's a process, not an accident)
- [ ] Polish the **first 5 seconds** above all — that's what juries score first.
- [ ] **Submit to Awwwards** ($15–30/site). Aim Honorable Mention → Developer/Designer Award →
      Site of the Day. Submitting *is* the marketing; nominees get a profile + backlinks.
- [ ] Also submit to **FWA, CSSDA, GSAP "site of the day", Httpster, Land-book**.
- [ ] **Dribbble is a different game** — it rewards *shots*, not live sites. Post: the loading
      sequence as a video, a transition GIF, a type-system board, the cursor interaction.
      Motion shots outperform statics 5:1. Build a 15–30s **case-study reel**.
- [ ] Write a **process case study** (concept → wireframe → WebGL → result). This is what
      turns "nice site" into "$10K commission" — clients buy the *thinking*, not the pixels.

---

## 7. So… would someone pay $10K for it?

Today: it's a **$1.5–3K premium WordPress portfolio** — clean, fast, well-built, but
templated and DOM-rendered.

The $10K tier is bought for one of two things, and you need at least one:
1. **A bespoke, award-credentialed experience** (Awwwards/FWA badge = instant price anchor), or
2. **A named designer's process and taste** (the case study, the reel, the story).

The roadmap above is the path to *both*. The single highest-leverage move is **Phase 2 (the
WebGL layer)** layered on **Phase 0 (a real concept + curated, world-class images)**.
Everything else is multipliers on those two.

---

### TL;DR
- Your engineering is strong; your **motion and concept layers are missing** — that's the gap.
- The reference sites are **WebGL applications**, yours is an **HTML document**.
- Quick wins now: `srcset` on hero/plates, self-host fonts, kill the `next-level/` duplicate.
- League change: **Lenis + GSAP + WebGL image transitions + a preloader + shared-element nav.**
- Architecture: **headless WordPress + Next.js/R3F** keeps content easy *and* unlocks SOTD.
- The award is a *process*: curate to 8–12 heroes, nail the first 5 seconds, submit, and ship
  a Dribbble motion reel + written case study.

---
