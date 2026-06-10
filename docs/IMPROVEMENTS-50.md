# Obscura — 50 improvements & update log

Living backlog from the v4.32 review. Items marked ✅ ship with the version noted.
Grouped A (bugs/gaps) · B (design) · C (perf) · D (SEO) · E (admin/robustness).

## Update log
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
10. Upgrade `index.php` fallback to reuse the card rail (currently a plain stopgap).

## 📐 B — Design / catalogue identity
11. ✅ v4.33.0 — Plate numbers on work cards (`PL—07`, vertical mono).
12. ✅ v4.33.0 — Journal cards as index cards (amber top-rule, big mono date, subdued image).
13. ✅ v4.34.0 — Ghost numerals in the hero (outlined slide number behind the title).
14. Marquee ticker above the footer (Available · Vienna · International · Est.) — opt-in.
15. EXIF captions on hover (not permanent) — developer-note feel.
16. ✅ v4.33.0 — `::selection` amber + scrollbar polish (verified present).
17. ✅ v4.34.0 — Filter chips with counts (category / tag / journal).
18. ✅ v4.34.0 — Pull-quotes + drop-cap in journal body.
19. Handwritten signature SVG on the Studio page.
20. ✅ v4.34.0 — "More notes" strip at the journal post end (2 cards + `All entries ↗`).
21. ✅ v4.34.0 — Hover-frame already shared by all `.nr-card` (journal/series/search).
22. Amber hairline section dividers (hero → footer strip).

## 🚀 C — Performance & technical
23. OG share cards for journal (`/nr-og/<id>.jpg` also for `nr_journal`).
24. `fetchpriority`/`loading` audit across new templates (jpost, taxonomy, search).
25. WebP bulk: persistent resume after an interrupted run.
26. Explicit `width`/`height` on all `<img>` in new templates (CLS).
27. Lazy-init WebGL/distortion only when toggle ON and in viewport.
28. Split `theme.js` into modules; load per-page.
29. Critical CSS for legal/journal (different above-the-fold than the hero).
30. `rel=preconnect` to Cloudflare/font origin in `<head>`.
31. Defer the Leaflet map until scrolled into view.
32. Sane cache headers / TTL for `/nr-og/` + `/projects.json`.

## 🔍 D — SEO & content
33. ✅ v4.34.0 — Breadcrumb schema on journal + all taxonomy archives.
34. Meta-description fallback for archives/taxonomies (currently singular only).
35. `rel=prev/next` / canonical on paginated archives.
36. Journal in `/projects.json` (or `/journal.json`) so ⌘K search finds posts.
37. ✅ v4.34.0 — Auto "Related journal" (shared category, recent fallback) = the More-notes strip.
38. Sitemap `lastmod` from `post_modified` (done for journal; audit projects).
39. `hreflang` groundwork (currently clean `og:locale` only).
40. FAQ schema on guide-type journal posts.

## ⚙️ E — Admin UX & robustness
41. Journal admin column: category + "has featured image?".
42. Featured/Credits section (link collection — awaiting the curated list).
43. Settings validation (UID / phone / URLs) with inline hints.
44. Importer duplicate-guard by image hash (not just title).
45. "Theme health" dashboard (WebP generated? SMTP on? permalinks flushed?).
46. Per-section reset-to-defaults button in settings.
47. Enquiry CSV export from the insights widget.
48. Cookie notice: don't render once a choice is stored.
49. Test the `NR_DISABLE_FEATURES` fallback path.
50. Post-activation onboarding notice (pages · permalinks · settings).
