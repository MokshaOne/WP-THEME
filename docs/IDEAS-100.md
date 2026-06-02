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
29. **404 page with random project suggestions.** _UX_
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
54. **True line-split heading reveals** (SplitText-style masks). _Awww_
55. **Shared-element morph via named view-transitions.** _Awww_
56. ✅ **Pointer/scroll parallax depth layers** on the hero. _Awww_
57. **Custom easing system + refined magnetic interactions.** _Awww_
58. **Opt-in sound design** (hover ticks, slide whoosh) + mute toggle. _Awww_
59. **Animated / generative favicon or logo mark.** _Awww_

### Content / features
60. ✅ **Journal / blog** CPT + archive + single. _Content_
61. ✅ **Timeline ("by year") archive view.** _Content_
62. **Map archive view** of project locations. _Content_
63. **Series / collections** grouping of projects. _Content_
64. **Tag / keyword taxonomy + multi-filter.** _Content_
65. ✅ **Instant client-side search** across projects. _Content/UX_
66. **Video plates** in galleries (mp4 / Vimeo). _Content_
67. **Before/after retouch slider.** _Content_ ✅ v4.21.0 — [nr_compare before="" after=""] shortcode
68. ✅ **Downloadable press kit / one-pager.** _Content_
69. **Dynamic per-project OG share cards** (composited image). _SEO/Content_

### Conversion
70. **Quote → branded PDF estimate** emailed to the visitor. _Conv_
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
80. **Automated internal-linking** between related projects. _SEO_

---

## 🔴 Tier 3 — Hard (81–100)

81. **WebGL image transitions** (displacement / morph between projects) — the Awwwards lever. _Awww_ ✅ v4.22.0 — opt-in shader dissolve on the hero (Theme Settings → WebGL hero transitions)
82. **Full shared-element page-morph choreography.** _Awww_
83. **WebGL hover-distortion grid.** _Awww_
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
