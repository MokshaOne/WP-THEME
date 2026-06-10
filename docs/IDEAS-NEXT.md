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
