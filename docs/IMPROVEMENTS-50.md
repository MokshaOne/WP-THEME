# Obscura — 50 improvements & update log

Living backlog from the v4.32 review. Items marked ✅ ship with the version noted.
Grouped A (bugs/gaps) · B (design) · C (perf) · D (SEO) · E (admin/robustness).

## Update log
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
23. ✅ v4.35.0 — OG share cards for journal (`/nr-og/<id>.jpg` now serves `nr_journal`).
24. `fetchpriority`/`loading` audit across new templates (jpost, taxonomy, search).
25. WebP bulk: persistent resume after an interrupted run.
26. ✅ v4.35.0 — Verified: new templates use WP image fns (width/height emitted).
27. Lazy-init WebGL/distortion only when toggle ON and in viewport.
28. Split `theme.js` into modules; load per-page.
29. Critical CSS for legal/journal (different above-the-fold than the hero).
30. `rel=preconnect` to Cloudflare/font origin in `<head>`.
31. Defer the Leaflet map until scrolled into view.
32. Sane cache headers / TTL for `/nr-og/` + `/projects.json`.

## 🔍 D — SEO & content
33. ✅ v4.34.0 — Breadcrumb schema on journal + all taxonomy archives.
34. ✅ v4.35.0 — Meta-description fallback for archives + taxonomy/term pages.
35. `rel=prev/next` / canonical on paginated archives.
36. ✅ v4.35.0 — Journal added to /projects.json and the ⌘K command palette.
37. ✅ v4.34.0 — Auto "Related journal" (shared category, recent fallback) = the More-notes strip.
38. Sitemap `lastmod` from `post_modified` (done for journal; audit projects).
39. `hreflang` groundwork (currently clean `og:locale` only).
40. FAQ schema on guide-type journal posts.

## ⚙️ E — Admin UX & robustness
41. ✅ v4.36.0 — Journal admin columns: category + featured-image check.
42. Featured/Credits section (link collection — awaiting the curated list).
43. ✅ v4.36.0 — Settings normalisation: UID uppercased/trimmed, URL fields get a scheme.
44. ✅ v4.36.0 — Importer reuses an existing attachment when the image MD5 matches.
45. ✅ v4.36.0 — "Theme health" dashboard widget (permalinks, SMTP, WebP, Site Icon, pages…).
46. Per-section reset-to-defaults button in settings.
47. ✅ v4.36.0 — Enquiry CSV export (UTF-8 BOM) from the insights widget.
48. ✅ v4.35.0 — Cookie notice skipped server-side when an nr_consent cookie exists.
49. Test the `NR_DISABLE_FEATURES` fallback path.
50. Post-activation onboarding notice (pages · permalinks · settings).
