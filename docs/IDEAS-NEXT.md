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

## 🎯 Conversion & audience
6. **"Recently viewed projects"** strip (localStorage) on the portfolio/home — gentle re-engagement, no tracking.
7. **Newsletter / "new work" email capture** — a single-field owned-audience box (footer or enquire), stored as a CPT or piped to a provider.
8. **Testimonials on the homepage** — a quiet rotating quote band (the CPT exists; only used on About + schema today).
9. **Availability calendar / "next open dates"** — surface bookable windows from a simple settings field or Google Calendar feed.
10. **Lookbook / portfolio PDF export** — "download this series as a PDF" using the existing dependency-free PDF writer.

## 📈 SEO & distribution
11. **AggregateRating schema** from testimonials → star snippets on the LocalBusiness/Person graph.
12. **Per-project "last updated" + freshness** signals; ping IndexNow (Bing/Yandex) on publish.
13. **RSS/JSON feed polish** — branded `/feed` description + a dedicated `/journal/feed`.
14. **Speculation Rules API** (prerender-on-hover) to replace the manual prefetch — instant nav on modern browsers.
15. **Social/OG audit per template** — verify cards for taxonomy/search/legal, add `article:published_time` etc. on journal.

## 🖼 Editorial depth (Awwwards lever)
16. **Project "process" / behind-the-scenes** field + a dedicated section on the project page (juries love it).
17. **Scroll-scrubbed video hero** option for a flagship project (opt-in).
18. **Diptych / full-bleed layout variants** per plate (let a project mix grid rhythms).
19. **Client logos / "trusted by" strip** (separate from testimonials).
20. **Series cover pages** — a curated intro screen per series (hero + statement) before the rail.

## ♿ A11y & resilience
21. **`prefers-contrast` + reduced-data** handling (skip heavy effects/images on Save-Data).
22. **Keyboard-complete command palette & lightbox audit** (focus trap, roving tabindex).
23. **Automated Lighthouse CI** in the GitHub Action (perf/a11y budget, fails the PR on regression).
24. **Visual smoke test** — a tiny Playwright check that the hero, rails and forms render.

## ⚙️ Ops & admin
25. **Settings import/export** (JSON) — move config between staging and live.
26. **Backup reminder / one-click DB+uploads export** hook into the health widget.
27. **Broken-link & missing-featured-image report** across projects/journal in the dashboard.
28. **Role for an editor/assistant** with access limited to Projects/Journal/Enquiries.

---

**Recommended first pick:** the audit quick-wins (1–5) are a tidy "theme professionalism" release; then #6/#8 (recently-viewed + homepage testimonials) for conversion. Say which to build.
