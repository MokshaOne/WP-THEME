# Obscura — 200 improvements & ideas (5 batches)

The live, forward-looking backlog for **raveenthiran.com** (Obscura theme · classic
WordPress on easyname shared hosting behind free Cloudflare · ~200 projects × ~14
photos · English/international · plugin-light, ships as an installable ZIP).

These are **new** — they don't repeat the ~178 items already shipped across
IDEAS-100 → ROADMAP-V2 → IMPROVEMENTS-50 → IDEAS-NEXT (see `docs/ROADMAP-ARCHIVE.md`).
A handful deliberately revisit the three items deferred in v4.41 (lookbook PDF,
scroll-video hero, diptych) — now scoped to be done *properly*.

**Convention:** each item is `**title** — why`. Tags: `Awww` (Awwwards-edge) ·
`Conv` (conversion) · `SEO` · `Perf` · `UX` · `A11y` · `Ops` · `Content` · `Sec` ·
`Dev`. Effort: _S_ small · _M_ medium · _L_ large. Visitor-facing / risky features
ship **opt-in, default off** per the playbook.

> How to use this: pick a batch, build it in versioned sub-batches, tick items with
> the version that shipped them, and keep an update log — exactly as the archived
> lists were run.

---

## Batch 1 — Motion, GPU & interaction craft (1–40)
*The "$10K experience" layer. The theme has a WebGL hero + View-Transitions morph;
this batch builds the surrounding choreography. Everything is reduced-motion- and
WebGL-fallback-safe.*

### GPU & shaders
1. **OGL plane-grid portfolio** — render the work grid as textured planes (OGL ~16 KB), images as GPU textures with a real fallback `<img>`. _Awww · L_
2. **Per-pixel displacement transition** — dissolve between projects via a displacement map instead of a crossfade. _Awww · M_
3. **Particle dispersion on hover** — a thumbnail scatters into points and reforms. _Awww · M_
4. **Heat-haze / refraction idle shader** — subtle GPU refraction over the hero when idle. _Awww · M_
5. **Animated dithering/noise background** — a slow Bayer-dither field as canvas texture (richer than the static grain). _Awww · M_
6. **Fullscreen "shockwave" on click** — a GPU ripple emanates from the pointer on nav. _Awww · S_
7. **RGB-split / chromatic aberration on hover** — desktop-only thumbnail treatment. _Awww · S_
8. **Fluid/metaball cursor trail** — a GPU fluid sim trailing the custom cursor. _Awww · M_
9. **WebGL aperture-iris page reveal** — an iris wipe (camera shutter) as the load transition. _Awww · M_
10. **Variable-font axis morph** — animate Inter Tight weight/optical-size on load and slide-change (true variable font, see #106). _Awww · M_

### Scroll & physics
11. **Lenis smooth-scroll (opt-in)** — inertial scrolling on the scrolling pages, reduced-motion-aware. _Awww · M_
12. **Scroll-velocity image skew** — rail cards shear slightly with scroll speed, settle on stop. _Awww · S_
13. **Infinite draggable canvas** — a kinetic, momentum-based free-pan portfolio view as an alternate archive mode. _Awww · L_
14. **Pinned scrollytelling section** — a flagship project told through pinned, scrubbed steps. _Awww · L_
15. **Elastic overscroll on rails** — rubber-band the horizontal rails at their ends. _UX · S_
16. **Snap carousel with momentum** — physics-based snap between plates. _UX · S_
17. **Layered hero parallax** — separate fore/mid/back depth layers (beyond the current pointer parallax). _Awww · M_
18. **Scroll progress as a film-frame counter** — replace the scrollbar with a 35mm frame counter. _Awww · S_
19. **Idle "screensaver" auto-cycle** — after inactivity, the hero slowly cycles like a slideshow. _UX · S_
20. **Section-snap on mobile** — full-height snap between sub-page sections. _UX · S_

### Micro-interaction & feedback
21. **Spring-physics magnetic buttons** — upgrade the current magnetic hover to a real spring solver. _Awww · S_
22. **Text scramble/decode reveal** — headings resolve from noise on enter. _Awww · S_
23. **Viewfinder bracket tap feedback** — a camera AF-bracket animates on card tap. _Awww · S_
24. **3D tilt + glare on cards** — perspective tilt with a moving specular highlight. _Awww · S_
25. **SVG line-draw monogram** — the wordmark/monogram draws itself on first load. _Awww · S_
26. **Spotlight mask cursor** — the cursor reveals a second image layer (e.g. B&W→colour) through a soft mask. _Awww · M_
27. **Animated link underlines** — draw-on underlines with directional awareness. _UX · S_
28. **Contextual cursor states** — expand drag/view/close/sound cursor variants into a small state machine. _UX · S_
29. **Split-flap stat counters** — airport departure-board flip for the stats row. _Awww · S_
30. **Hover sound waveform** — when sound is on, show a tiny reactive waveform. _Awww · S_

### Lightbox, players & orchestration
31. **Pointer-lock immersive lightbox** — fullscreen pan/zoom with arrow/WASD, frame counter, EXIF. _UX · M_
32. **Video plate timeline scrubber** — hover-preview thumbnails on the motion-plate scrubber. _UX · M_
33. **Film-strip sequence player** — a horizontal scrubbable strip for reportage sequences. _Content · M_
34. **Transition orchestrator refactor** — one timeline manager so effects share a clock (no jank when stacked). _Dev · M_
35. **Drift-grain over time** — make the existing grain slowly translate so it never looks static. _Awww · S_
36. **Dividers that draw on scroll-in** — animate the amber hairline divider utility. _Awww · S_
37. **Loading skeletons matched to aspect** — shimmer placeholders sized to each card's ratio (no layout shift). _Perf · S_
38. **Compare-slider polish** — labels, keyboard, and haptic tick on the before/after handle. _UX · S_
39. **Reduced-motion "elegant static" audit** — define a tasteful frozen variant of every effect, not just "off". _A11y · M_
40. **Motion settings panel (front-end)** — a visitor-facing "calm / standard / cinematic" motion switch persisted locally. _A11y · S_

---

## Batch 2 — Conversion, client lifecycle & business (41–80)
*Turn a beautiful portfolio into booked work. Larger client-portal items are flagged;
start with the small high-leverage ones.*

### Capture & qualify
41. **Visitor shortlist / "my selection"** — heart frames across the site, persisted, then email the list as a brief. _Conv · M_
42. **Multi-step booking wizard** — type → date → budget → details, with progress + save/resume. _Conv · M_
43. **Lead scoring in insights** — weight enquiries by budget/date/type and sort the dashboard. _Conv · S_
44. **Exit-intent lookbook offer** — gentle, opt-in: offer the PDF lookbook as the cursor leaves. _Conv · S_
45. **"Similar budget" project hints** — on Enquire, surface work that fits the chosen package. _Conv · S_
46. **Pre-filled brief from a project** — extend "Commission similar" to seed the brief text, not just the type. _Conv · S_
47. **Conversion microcopy + trust audit** — reassurance, response-time, privacy near every form field. _Conv · S_
48. **A/B hero CTA copy** — a lightweight built-in splitter logging to insights. _Conv · M_

### Booking, payment & contracts
49. **Availability calendar on Enquire** — native date-picker showing open shoot windows (or Cal.com embed). _Conv · M_
50. **Deposit via Stripe Payment Link** — collect a retainer to confirm a booking. _Conv · M_
51. **Branded PDF estimate by email** — the quote calculator emails the visitor a real estimate + notifies you. _Conv · M_
52. **Invoice generator** — turn an accepted estimate into a numbered invoice PDF. _Ops · M_
53. **Lightweight e-sign** — "I agree" + timestamp + IP, stored on the enquiry, for simple shoot agreements. _Ops · M_
54. **Booking .ics invite** — attach a calendar invite to the confirmation email. _Conv · S_
55. **VAT-aware estimates (AT 20%)** — show net/gross + reverse-charge note for EU business clients. _Conv · S_
56. **Quote add-ons / upsells** — retouching, rush delivery, extra looks as line items. _Conv · S_
57. **Multi-currency display** — geo or manual toggle for prices. _Conv · M_
58. **Gift voucher** — sell a code, redeem against a booking. _Conv · M_

### Retention & social proof
59. **Client proofing galleries** — private, password-protected delivery with favourites + download (keeps clients on-domain). _Conv · L_
60. **Delivery page with download-all + expiry** — a clean handover page per shoot. _Conv · M_
61. **Post-delivery testimonial request** — auto-email that feeds the Testimonials CPT + aggregateRating. _Conv · S_
62. **Case-study template** — problem → approach → result for selected projects. _Content · M_
63. **Print sales** — limited-edition prints per project (Stripe + simple stock). _Conv · L_
64. **Image licensing flow** — request usage/territory/term per image, priced. _Conv · M_
65. **Mini-CRM kanban** — enquiry board: new → quoted → booked → delivered. _Ops · M_
66. **Automated follow-up drip** — a cron sequence for un-answered enquiries (opt-in). _Conv · M_
67. **Abandoned-quote recovery** — if email was captured, nudge unfinished estimates. _Conv · S_
68. **Referral code for past clients** — track word-of-mouth in insights. _Conv · S_

### Presentation of value
69. **Packages comparison table** — transparent pricing tiers with feature ticks. _Conv · S_
70. **"Trusted by" with link-out + counts** — extend the client-logos strip with animated counts. _Conv · S_
71. **Press wall** — manual press list rendered with logos + links + dates. _Content · S_
72. **Newsletter double opt-in + welcome + archive** — confirm subscribers, send a welcome, publish past issues. _Conv · M_
73. **Seasonal campaign banner scheduler** — date-bounded promo bar, auto on/off. _Conv · S_
74. **WhatsApp Business deep-link** — prefilled message with the project reference. _Conv · S_
75. **"Hold two dates" request** — soft-reserve flow before a firm booking. _Conv · S_
76. **Client portal login** — delivery + invoices + messages behind a login. _Conv · L_
77. **Share-card generator for clients** — "shot by" card a client can post. _Conv · S_
78. **Referral/affiliate landing pages** — per-partner tracked URLs. _Conv · M_
79. **SMS shoot reminder (opt-in)** — provider-based reminder before the date. _Conv · M_
80. **Saved enquiry resume link** — tokenised link to continue a brief later. _Conv · S_

---

## Batch 3 — SEO, performance, distribution & analytics (81–120)
*Squeeze the technical advantage. Measure first (PageSpeed + field data) before any
perf change — see the playbook.*

### Images & assets
81. **AVIF twins** — add AVIF alongside the existing WebP pipeline, `<picture>` with AVIF→WebP→JPEG. _Perf · M_
82. **Art-directed `<picture>`** — different crops for portrait vs landscape viewports, not just resize. _Perf · M_
83. **Variable font swap** — one Inter Tight variable file instead of three static weights. _Perf · M_
84. **Font subsetting + unicode-range** — Latin subset, split rare glyphs. _Perf · S_
85. **SVG icon sprite** — inline one sprite instead of many icon requests. _Perf · S_
86. **Precompressed Brotli/gzip static assets** — verify the host/Cloudflare serves `.br`. _Perf · S_
87. **`modulepreload` + JS islands** — lazy-hydrate non-critical interactivity. _Perf · M_
88. **Virtualised rails** — render only on-screen cards on 200-project archives. _Perf · M_
89. **Exhaustive `loading`/`decoding`/`fetchpriority` re-audit** — across all templates after the recent additions. _Perf · S_
90. **Generalised LCP preload** — auto-detect and preload the LCP element on every template, not just front-page. _Perf · M_

### Edge & delivery
91. **HTTP 103 Early Hints** — push the hero preload via Cloudflare Early Hints. _Perf · M_
92. **Cloudflare cache-rules + APO guide** — a documented, copy-paste edge config for this theme. _Perf · S_
93. **Service-worker offline page** — extend the PWA with a branded offline fallback + SWR for assets. _Perf · M_
94. **Background-sync enquiry submit** — queue a submission made offline, send when back. _Perf · M_
95. **Periodic background prefetch** — warm the journal index in the background (where supported). _Perf · S_

### Structured data & feeds
96. **Per-plate ImageObject schema** — creator, caption, license, EXIF on each image. _SEO · M_
97. **VideoObject schema** — duration + thumbnail for motion plates. _SEO · S_
98. **XML image sitemap** — separate, with captions + geo. _SEO · S_
99. **Sitemap index splitting** — paginate the sitemap at 200+ URLs. _SEO · S_
100. **WebSub / PubSubHubbub ping** — instant feed push to subscribers/readers. _SEO · S_
101. **JSON Feed 1.1 endpoint** — modern reader support alongside RSS. _SEO · S_
102. **`og:video` + X player card** — for projects with motion. _SEO · S_
103. **Pinterest rich pins** — article/product meta for save-driven discovery. _SEO · S_
104. **Google Discover optimisation for journal** — large images + freshness + author markup. _SEO · M_
105. **Canonical consolidation** — collapse filtered/sorted archive permutations to one canonical. _SEO · S_

### Analytics & quality gates
106. **Self-hosted pageview analytics** — extend `inc/insights.php` to privacy-first pageviews + funnels. _Ops · M_
107. **Core Web Vitals field collection** — `web-vitals.js` → a theme endpoint, charted in the dashboard. _Perf · M_
108. **Conversion funnel report** — view → enquire-start → submit, per source/project. _Conv · M_
109. **Structured-data lint in CI** — validate JSON-LD in the manual quality workflow. _SEO · S_
110. **Search-term capture** — log the ⌘K palette queries to find content gaps. _Content · S_
111. **404/410 + redirect-map UI** — manage redirects for deleted/renamed projects in the dashboard. _SEO · M_
112. **Outbound broken-link checker** — extend content-health to external links. _SEO · S_
113. **On-demand IndexNow bulk resubmit** — a button to re-ping all URLs after a big change. _SEO · S_
114. **Per-project unique meta + title templates** — token-based (`%title% — %cat% photography, Vienna`). _SEO · S_
115. **FAQ + HowTo schema on relevant journal posts** — earn rich results on guides. _SEO · S_
116. **Author/Person entity consolidation** — single `@id` referenced everywhere (Knowledge-Panel hygiene). _SEO · S_
117. **`speakable` schema on key pages** — voice-assistant surfacing. _SEO · S_
118. **Lighthouse budget enforcement** — wire the existing `lighthouserc.json` into a real gate when a staging URL exists. _Perf · M_
119. **Robots/AI-crawler policy** — explicit allow/deny for GPTBot/CCBot etc., owner's choice. _SEO · S_
120. **hreflang region tuning** — en, en-AT, x-default refinement for the DACH market. _SEO · S_

---

## Batch 4 — Editorial depth, content tooling & art direction (121–160)
*The art-direction layer juries reward, plus the admin tooling that makes 200
projects maintainable.*

### Plate & layout craft
121. **Diptych / triptych plates (done right)** — per-image layout field driving paired/tripled rhythms in the rail. _Awww · M_
122. **Full-bleed cinematic plate** — an edge-to-edge variant for a hero frame. _Awww · S_
123. **Scroll-scrubbed video hero (opt-in)** — flagship-only, decode-budget-aware. _Awww · L_
124. **Lookbook PDF per series (memory-safe)** — stream images one at a time so it survives shared-hosting limits. _Conv · M_
125. **Inline before/after in a plate** — embed the compare slider as a gallery item. _Content · S_
126. **Per-project contact-sheet view** — all frames as a grid, toggle from the rail. _Content · S_
127. **Focal-point picker** — set a smart-crop anchor per image for art-directed crops. _Content · M_
128. **Featured-frame / hero-crop picker** — choose the exact hero crop per project. _Content · S_
129. **Cover cropper with aspect presets** — crop covers to 4:5 / 5:4 / 16:9 in admin. _Content · M_
130. **Duotone/treatment presets** — optional CSS-filter "house look" per project. _Awww · S_

### Story & navigation
131. **Story/diary mode** — captioned, sequential reportage layout. _Content · M_
132. **Series chapters + intra-series nav** — prev/next within a series, chaptered. _Content · M_
133. **Year-timeline archive view** — browse 200 projects by year. _UX · M_
134. **Map archive view** — every shoot on one Leaflet map (uses existing coords). _UX · M_
135. **Colour-palette filter** — extract a palette per project, filter "by mood/colour". _Awww · M_
136. **Related-by-tag suggestions** — not just category; richer cross-linking. _SEO · S_
137. **"On this day" resurfacing** — gently surface older work by anniversary. _Content · S_
138. **Per-series RSS** — a feed per series for niche followers. _SEO · S_
139. **Location map per project** — a mini-map tied to the project's GPS. _Content · S_
140. **Credits block** — stylist / MUA / agency / talent with links. _Content · S_

### Journal & long-form
141. **Reading time + progress bar** — on journal essays. _UX · S_
142. **Auto table of contents** — for long journal posts. _UX · S_
143. **Footnotes / margin notes** — editorial annotations in essays. _Content · S_
144. **Inline glossary tooltips** — define terms on hover/tap. _Content · S_
145. **Editorial kicker/dek fields** — proper headline + standfirst structure. _Content · S_
146. **Pull-quotes on projects** — bring the journal's pull-quote styling to project text. _Content · S_
147. **Markdown import for essays** — paste/import Markdown into journal. _Dev · S_
148. **Print stylesheet for projects** — a clean printable layout. _UX · S_

### Admin content tooling
149. **Bulk caption/alt editor** — one screen to fix alts across the library. _Ops · M_
150. **Alt-text quality linter** — warn when alt equals the filename. _A11y · S_
151. **Auto-tagging on save** — keyword/term extraction suggestions. _Ops · M_
152. **Tag clustering view** — see and merge near-duplicate tags. _Ops · S_
153. **Editorial calendar** — schedule + status board for journal. _Ops · M_
154. **Reusable content blocks library** — saved patterns for essays. _Content · S_
155. **Tokenised draft preview links** — share unpublished work with clients/editors. _Ops · S_
156. **Gear/setup metadata block** — bodies, lenses, lighting per project. _Content · S_
157. **Captions toggle (front-end)** — show/hide all EXIF captions. _UX · S_
158. **"Quote of the day" empty states** — testimonials on 404 / empty search. _Content · S_
159. **Series consistency grid** — compare frames across a series to spot outliers. _Ops · S_
160. **Sample-content importer** — one-click demo projects/journal for a fresh install. _Ops · M_

---

## Batch 5 — A11y, i18n, security, ops & developer experience (161–200)
*The unglamorous layer that keeps a live site trustworthy and the codebase shippable.*

### Accessibility
161. **SR live-region announcements** — announce slider/palette/state changes to screen readers. _A11y · S_
162. **Landmark + skip-link audit** — complete roles, multiple skip targets. _A11y · S_
163. **Keyboard shortcuts help overlay** — press `?` for a discoverable cheat-sheet. _A11y · S_
164. **Forced-colors (Windows High Contrast) polish** — verify every component. _A11y · M_
165. **`prefers-reduced-transparency` handling** — solidify glassy surfaces when requested. _A11y · S_
166. **`focus-visible` consistency audit** — one coherent focus ring system. _A11y · S_
167. **WCAG 2.2 target-size + dragging alternatives** — 24px targets, non-drag fallbacks for sliders. _A11y · M_
168. **Cognitive-load mode** — a "plain" layout that strips motion + density. _A11y · M_
169. **axe-core in the quality workflow** — automated a11y assertions on staging. _A11y · S_
170. **Caption/transcript support for video plates** — `<track>` + transcript block. _A11y · M_

### Internationalisation
171. **Theme-native bilingual DE/EN** — language switcher + per-post translations without a heavy plugin. _Content · L_
172. **Complete `.pot` + ship `de_DE`** — finish string extraction and provide a German translation. _Dev · M_
173. **hreflang sync with the switcher** — keep alternates correct as content is translated. _SEO · S_
174. **RTL stylesheet pass** — logical properties + an RTL sheet for future clients. _A11y · M_
175. **Locale-aware dates/numbers** — format per locale in templates. _Dev · S_

### Security & privacy
176. **CSP header builder** — nonce-based Content-Security-Policy with a report-only ramp. _Sec · M_
177. **Subresource Integrity** — SRI on any external script (Leaflet, Turnstile). _Sec · S_
178. **Repeat-spam IP throttle + banlist** — extend the honeypot/rate-limit with persistence. _Sec · S_
179. **Admin settings audit log** — record who changed which setting, when. _Sec · S_
180. **GDPR export/erase** — data subject requests for enquiries + subscribers. _Sec · M_
181. **Consent log + cookieless analytics** — record consent events; keep analytics cookie-free. _Sec · S_
182. **Enquiry-export encryption** — optional passphrase on the CSV export. _Sec · S_
183. **Login hardening doc + hooks** — app-password rotation reminder, 2FA guidance. _Sec · S_
184. **Uploads MIME/dimension validation** — reject oversized or mistyped uploads early. _Sec · S_
185. **Honeytoken admin page** — a trap URL that bans scanners. _Sec · S_

### Ops & resilience
186. **Theme self-test health check** — verify GD, fonts, writable uploads, SMTP, cron. _Ops · S_
187. **SMTP/uptime self-test cron + alert** — email the owner if mail or the site looks down. _Ops · M_
188. **Autoload-options audit** — flag bloated autoloaded options hurting every request. _Perf · S_
189. **Error-log viewer (theme-scoped)** — surface theme errors in the dashboard. _Ops · S_
190. **Settings migration on version change** — versioned option schema with safe upgrades. _Dev · S_
191. **Staging↔live settings diff** — show what differs before importing. _Ops · M_
192. **Backup-to-cloud hook + doc** — B2/S3 reminder wired into the health widget. _Ops · S_
193. **Opt-in uninstall cleanup** — remove options/CPT data on theme removal if chosen. _Ops · S_
194. **Feature-flag registry page** — one screen auditing every `nr_fx_*` toggle + state. _Ops · S_

### Developer experience
195. **One-command build** — `composer run build` = lint + bump + ZIP + checksum. _Dev · S_
196. **Auto GitHub Release on tag** — attach the ZIP to a release automatically. _Dev · S_
197. **Version-bump script** — sync `functions.php` + `style.css` + `readme.txt` in one go. _Dev · S_
198. **PHPCS (WordPress standard) + PHPStan** — advisory static analysis on `inc/`. _Dev · M_
199. **ESLint + Prettier + Stylelint** — lint `assets/js` and `theme.css`, pre-commit hooks. _Dev · S_
200. **Visual regression snapshots** — Playwright screenshot diffs of hero/rails/forms in the workflow. _Dev · M_

---

## How to sequence (recommendation)
- **Highest leverage first:** Batch 2 (#41 shortlist, #46 pre-filled brief, #49 availability, #51 PDF estimate) — direct revenue, mostly small/medium.
- **Cheapest wins:** Batch 3 image/asset items (#81 AVIF, #83 variable font, #89 audit) and Batch 5 dev-experience (#195–#197) — low risk, compounding.
- **The Awwwards swing:** Batch 1 #1/#2/#14 — high effort, the real differentiator; gate behind toggles and a fallback.
- **Don't do all at once.** Run them like the archived lists: versioned sub-batches, lint → ZIP → ship → tick, honest about diminishing returns.

_Backlog opened after v4.41.0. History: `docs/ROADMAP-ARCHIVE.md`. Method: `docs/OBSCURA-PLAYBOOK.md`._
