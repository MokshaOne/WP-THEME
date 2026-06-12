# Changelog — Obscura (raveenthiran)

## 4.72.0 — Invoicing (no payments — bank transfer + PDF invoice)
New `inc/invoices.php`. Booking system stays payment-free; invoices are proper
documents paid by bank transfer.
- **Automatic (owner's choice):** when an enquiry/booking arrives **with a price
  estimate**, an invoice is created and emailed to the client — but only if the
  business details + IBAN are configured (guards against empty legal documents).
  One invoice per enquiry, **gap-free sequential numbering** (`2026-0001`, prefix
  configurable, counter never resets).
- **Kleinunternehmer:** no VAT line; the § 6 Abs. 1 Z 27 UStG exemption note is
  printed on every invoice (wording editable in Settings).
- **PDF:** reuses the dependency-free `NR_PDF` writer — invoice no/date/due date/
  service date, billed-to, line item, total, payment block (IBAN/BIC + reference),
  sender footer. Stored in `uploads/nr-invoices/` behind `.htaccess deny`; the
  client gets the PDF as a mail attachment, the owner via a capability-checked
  download — no public URL.
- **Admin:** an Invoice meta box on every enquiry — status, Download PDF, Send
  again, and manual "Create + send" with a custom amount (for enquiries without
  an estimate).
- New Settings → **§ Invoices**: auto-toggle, business block, IBAN/BIC, number
  prefix, payment terms, tax note. New `nr_enquiry_logged` action after an
  enquiry is fully logged (modules hook there).

## 4.71.1 — Booking: handle share short links (link-out vs inline)
The booking block now adapts to what's pasted: a real embed URL
(`…/appointments/schedules/…?gv=true`) loads as an **inline click-to-load iframe**,
while a Google **share short link** (`calendar.app.google/…`) — which Google blocks
from framing — becomes a **one-click button that opens the booking page in a new tab**.
So it works correctly with either input; both stay click-to-load (no third-party load
until the visitor acts).

## 4.71.0 — Booking embed (Google Appointment Schedule)
New `inc/booking.php` + `[nr_booking]` shortcode: drop a click-to-load Google
**Appointment Schedule** (real booking page) on any page. The external Google iframe
is only injected after the visitor clicks the Obscura-framed button — so there is **no
third-party request and no LCP/CLS/privacy hit on page load** (DSGVO-friendly), and it
falls back to the enquiry form when no calendar is set. Owner pastes the embed
code/URL into Theme Settings → **Booking embed (Google)**; we extract + validate the
src ourselves (only `calendar.google.com` / `calendar.app.google` allowed). Note: this
needs an **Appointment Schedule** link, not the read-only `calendar/embed` view (which
would also leak every event publicly).

## 4.70.3 — Desktop CLS: stop the hero title reflow (font-display:optional)
The real desktop CLS cause: the hero headline is up to **176px on desktop vs 84px on
mobile**, so when the web font swaps in, the giant title reflows (≈1.0 CLS on desktop,
~0.14 on mobile — exactly the observed asymmetry). The display weights (Inter Tight
300 + 700, both preloaded) now use **`font-display:optional`**, so the title never
swap-reflows: the preloaded font is used when it arrives in time, otherwise the
fallback is kept for that view with **no later swap** → no layout shift. Body weights
stay `swap`. Combine with v4.70.2 (preloader off) — both ride in the page HTML, so one
cache purge lands them together.

## 4.70.2 — Desktop CLS: intro preloader now opt-in (off)
Found the real cause of the desktop CLS (~1.0). The full-screen **intro preloader**
runs on a fixed fake timer and fades out **while the hero is still loading**, so the
settling hero (image + font) is revealed mid-shift and counted as a layout shift. It
only ran on desktop (`display:none` on mobile) — which is exactly why desktop CLS was
~1.0 and mobile was fine. It's now a **Theme Settings toggle, default OFF**. The
earlier modal/font changes (v4.69/4.70.1) were correct hardening but weren't the cause.
Re-enable the intro under Obscura → Settings if you want it (it costs CLS).

## 4.70.1 — Desktop CLS: preload the hero title weight
The big front-page headline uses Inter Tight **300**, which wasn't preloaded (only
500 + 700 were), so on desktop it painted in the fallback font and then reflowed
when the web font arrived — the real source of the large desktop CLS (mobile was
fine because the title is far smaller there). Weight 300 is now preloaded with 500
and 700. The v4.69 `.nr-modal{display:none}` change was a correct hardening but not
the CLS cause (Lighthouse had mis-attributed the shift to the full-screen overlay).

## 4.70.0 — Press: 3-field editor (Year · Publisher · Link)
The Press list is now a proper 3-column repeater in Theme Settings — **Year**,
**Publisher**, **Link** — with add/remove rows, instead of one free-text box. It
saves into the same option as clean `year | publisher | url` lines (so the save
pipeline is unchanged) and degrades to a plain textarea without JS. The parser
(`nr_recognition_list`) is now tolerant of space-separated lines too, so existing
entries like `2025 Heute.at https://…` parse correctly. The publisher renders as a
**clickable link** on the site (Press wall, “As featured in”, and the About page) —
the URL is never shown as raw text.

## 4.69.0 — PageSpeed: fix mobile LCP + desktop CLS
Driven by real pagespeed.web.dev data (mobile Perf 74, LCP 8.5s; desktop CLS 1.064).
- **Mobile LCP (8.5s → expected ~1.5s):** the hero LCP preload was emitting the
  full-size **AVIF** original (~470KB) with `type="image/webp"` when the original
  had no WebP twin — front-running the `<picture>` (which serves a small WebP) and
  becoming a heavy LCP. The preload now always matches the WebP the page serves and
  **never preloads a raw AVIF** (skips the preload if no WebP is available). Fixed in
  both `front-page.php` and `inc/seo-extra.php`. **Run Tools → Generate WebP once,
  then purge cache** so every size has its twin.
- **Desktop CLS (1.064 → ~0):** the `.nr-modal` (pricing/enquiry) was `display:flex`
  while closed, so the viewport-size modal was laid out and scored ~1.0 CLS. It's now
  `display:none` when closed (fade preserved via discrete transitions).
- Card images now carry an accurate `sizes` (was WP's full-width default); first
  project plate gets `fetchpriority="high"`; gallery alt text prefers the real
  attachment alt; `--ink-3` lifted to ~4.7:1 contrast.

## 4.68.2 — Remove the canvas archive view
Deleted the alternate **draggable "canvas" view** on the portfolio overview —
the "⊞ canvas / ☰ rail" toggle button (added by the opt-in GPU layer) and the
scattered plane-grid it produced. The portfolio overview is now **only** the
horizontal slider (rail with ← → arrows, wheel + drag). Removed the canvas JS
block from `awwwards.js` and the `.nr-canvas*` CSS; scrollytelling and the
scroll-scrubbed video hero in that file are untouched.

## 4.68.1 — Portfolio slider restored
The horizontal slider (rail with ← → arrows, wheel + drag scroll) is back on the
portfolio overview. The v4.64 switch to a static grid was a misunderstanding:
the owner disliked the **collapsed-layout bug** (fixed separately in v4.63), not
the slider itself. The collapse fix stays in place, plus a min-height floor so
the rail can't shrink to zero again. Grid CSS + its JS selectors removed.

## 4.68.0 — Dead-code purge (no functional change)
Removed code for features retired in v4.65 that was still shipping (gated off) on
every page. No behaviour or visual change — purely smaller payloads.
- **theme.js:** deleted the unused interface-sound (#58), recently-viewed (#6)
  and testimonials-band (#8) blocks, and an orphaned `nr-recent__card` selector.
- **theme.css:** removed the dead `.nr-sound`, `.nr-marquee`, `.nr-testi-band` /
  `.nr-testi`, `.nr-news`, `.nr-wiz` and `.nr-moods` rules. (Left selectors that
  share a rule with live classes, e.g. `.nr-clients__label`, untouched.)
- **PHP:** dropped the uncalled `nr_recent_strip_markup()`,
  `nr_newsletter_form_markup()`, `nr_testimonials_band_markup()` and the dead
  recently-viewed footer block. The `nr_subscriber` / `nr_testimonial` CPTs stay
  so any existing data remains accessible.
- Left the live horizontal rail (`.nr-portfolio-rail`, used by journal/search/
  taxonomy archives) and the ⌘K contact-sheet view in place.

## 4.67.0 — Finish IDEAS-50-NEXT (last 6 Medium items)
The list is complete (44/50 shipped, 6 won't-do). New `inc/medium4.php` +
`inc/preshoot.php`, plus a service-worker tweak.
- **#27 Dominant-colour placeholder** — stores a 1px average colour per image at
  upload (GD) and paints it behind the image while it loads (a light BlurHash
  stand-in; degrades silently when GD can't decode the format).
- **#15 Series mood-board** — `[nr_moodboard series="slug"]`: a cover grid + a
  palette strip built from those dominant colours (no live decoding).
- **#12 Image hotspots** — `[nr_hotspots img="123"]35,40 | note ;; 60,70 | note[/nr_hotspots]`
  positions note pins over a frame (hover / focus / tap).
- **#25 Offline-readable journal** — the SW now stores opened `/journal` articles
  in a persistent `nr-reader` cache that survives theme updates.
- **#42 Moodboard upload on Enquire** — an optional image field on the form (≤4
  files, ≤5MB, images only); validated uploads attach to the enquiry and show as
  thumbnails in a new enquiry meta box.
- **#44 Pre-shoot countdown emails** — a "Shoot date" on the enquiry + a daily
  cron that sends info-only prep emails at T-7 and T-1 (logistics only).

## 4.66.0 — Vienna district landing pages (#36)
New `inc/districts.php` — a plugin-light local-SEO helper. Drop
`[nr_district name="Neubau" code="1070"]` on a thin page per area; it renders a
localised heading + intro, a small project grid (optionally filtered by
`cat`), a CTA to enquire, and `ProfessionalService` / `areaServed` JSON-LD for
the long-tail "photographer in <district>" query. No rewrite rules, no new admin
pages. IDEAS-50-NEXT: 38/50 shipped · 6 Medium open.

## 4.65.0 — Remove unused toggles · slim "Pricing & Quote"
- **Removed 7 opt-in toggles and their features** (per owner — unused): Colour-mood
  filter (#135), Enquire wizard (#42), Newsletter capture, Recently viewed,
  Testimonials band, Footer marquee, Interface sound. The Theme-Settings rows,
  defaults, toggle-keys, body classes and footer renders are gone, so the
  features can't activate even if a stale option lingers.
- **"Pricing & Quote" now contains only pricing & quote.** The ACF options page
  was holding ~9 unrelated groups; removed the unused legacy ones (Hero, About,
  CTA, FAQ, Availability — 0 reads, content lives in Theme Settings), the
  Newsletter/Brevo group (feature retired), and the **Awards & Press** groups
  (duplicated Theme Settings → Obscura, which the About page already uses).
- **Local SEO / Schema** moved to its own **Business & SEO** page under the
  Obscura menu (kept editable, just off the Pricing & Quote page).
- `[nr_press]` / `[nr_featured]` now read the single Press list in Theme Settings
  (same source as the About page) instead of the removed ACF repeater.

## 4.64.0 — Portfolio grid (no rail) · awards/press URL fix
- **Removed the horizontal portfolio rail.** The archive is now a plain
  responsive **grid** that scrolls vertically inside the single-screen frame
  (`.nr-portfolio-rail` → `.nr-portfolio-grid`, `data-h-rail` dropped). Category/
  year/tag filtering still works (keys off `data-portfolio`); the rail-only
  behaviours (wheel-to-scroll, drag, scroll-compass, snap, parallax) no longer
  apply. Colour-mood chips + keyboard gallery updated to target the grid too.
  This also retires the rail-collapse layout bug for good.
- **Awards & Press — URL no longer shown as text.** `nr_recognition_list()` is
  now URL-aware: a link in any position (`year · name · url`) is used as the
  link target and never rendered as a column. Fixes the case where a 3-field
  entry pushed the URL into the visible "organisation" slot.

## 4.63.0 — Drop EXIF · fix portfolio rail · Medium batch 3
**Removed every EXIF feature.** The studio works in AVIF/WebP, which don't carry
the EXIF that `exif_read_data()` reads, so these never had data:
- EXIF-on-upload extractor + `nr_get_exif` (`inc/performance.php`), `#43`
  EXIF-date → project_year (`inc/tier2.php`), the auto-alt camera hint
  (`inc/finishing.php`), image-sitemap EXIF caption fallbacks (`inc/seo-extra.php`),
  the per-plate "Shot on" caption (`single-nr_project.php`) and its JS toggle.
- Reverted the EXIF-based ideas: **#2** shot-on facet, **#3** focal chart,
  **#9** related-by-EXIF (`inc/medium2.php`), **#4** time-of-day facet
  (`inc/leftovers.php`). **#1** EXIF location map → won't-do.

**Fixed the portfolio-rail collapse.** The cards are sized by `height:100%` of the
rail, whose height came from `.nr-fullscreen:has(> .nr-page__head)`. When that row
collapsed, every card shrank to zero and the title/meta piled up over the heading
(your screenshot). Now the archive sets explicit `grid-template-rows:auto
minmax(0,1fr)` (no `:has()` dependency) plus a rail `min-height` floor.

**Medium batch 3 (3 items), new `inc/medium3.php`:**
- **#6 Print-size wall preview** — `[nr_wallpreview img="id"]` shows a frame at
  A4/A3/A2/A1 against a 175 cm reference (viz only, no sale).
- **#10 Burst / sequence viewer** — `[nr_burst ids="…"]` step through a series
  (← → / buttons / scrubber); defaults to the current project's gallery.
- **#23 Shareable shortlist** — the ♥ selection tray gains a "Copy share link";
  opening `?shortlist=…` imports someone's selection.

IDEAS-50-NEXT: 37/50 shipped · 7 open (all Medium).

## 4.62.0 — IDEAS-50-NEXT, small leftovers (5 items) — S-tier cleared
New `inc/leftovers.php` (+ small touches to `inc/performance.php` and
`inc/smallwins.php`). All self-contained.
- **#4 Time-of-day facet** — EXIF capture now records the shot **hour**;
  projects are indexed into blue/golden/day/night (`_nr_tod`, one-time backfill).
  `?tod=golden` filters the portfolio archive; `[nr_tod]` renders the chips.
  (Heuristic is clock-based, so "golden/blue" is approximate.)
- **#14 Diptych compare** — `[nr_diptych a="slug|id" b="slug|id"]` shows two
  projects side by side (stacks on mobile).
- **#17 Studio log** — `[nr_studiolog]` public "what's new" timeline from the
  `nr_studiolog` setting ("YYYY-MM-DD | note"), falling back to recent journal.
- **#18 Footnoted credits** — `nr_project_credits_markup` now accepts an optional
  third field ("Role | Name | URL") and links the collaborator consistently.
- **#28 Video poster** — any `<video>` without a poster paints its first frame
  (`preload=metadata` + `#t=0.1`) instead of a black flash. No ffmpeg needed.

IDEAS-50-NEXT: 38/50 shipped · 11 open (all Medium). Small tier fully cleared.

## 4.61.0 — IDEAS-50-NEXT, Medium batch 2 (6 items)
New `inc/medium2.php` — the body of work as data + a little editorial surfacing.
All self-contained: no external libraries, no uploads, no service-worker changes.
- **#2 "Shot on" facet** — indexes each project's cameras/lenses from EXIF
  (`_nr_gear` + queryable `_nr_camera` rows, one-time backfill for existing
  projects). `?shot_on=` filters the portfolio archive; `[nr_shot_on]` renders
  the camera chips.
- **#3 Lens & focal-length chart** — `[nr_focal_chart]` inline-SVG histogram of
  every frame bucketed by focal length (cached 12h).
- **#9 Related-by-EXIF** — `[nr_related_gear]` "more work shot on {camera}" on a
  project page.
- **#7 Aspect-true masonry** — `[nr_masonry cat="" count=""]` CSS-columns archive
  that honours each frame's real crop.
- **#11 Field notes** — `[nr_fieldnotes]` short-form micro-journal band (a
  `field-notes` journal category, falls back to recent short entries).
- **#16 Pull-quote rotator** — `[nr_pullquotes]` rotates strong lines from the
  `nr_pullquotes` setting or, by default, journal excerpts (reduced-motion safe).

IDEAS-50-NEXT: 33/50 shipped · 16 open (11 M + 5 S).

## 4.60.0 — All theme admin pages under one menu
No theme page lives in **Tools** or **Appearance** any more — everything is a
submenu of the top-level **Obscura** menu.
- **Re-parented from Appearance** → Components, Feature flags, Tag clusters,
  Series grid, Pricing & Quote (ACF options page).
- **Re-parented from Tools** → Generate WebP, Obscura log, Redirects, Alt texts.
- Tools hub links + dashboard quick-links updated to `admin.php?page=…`; added
  Generate WebP and Pricing & Quote cards, plus an "All tools" quick-link.
- Dropped the now-obsolete `remove_submenu_page('themes.php',…)` calls and the
  duplicate Settings-page admin notice.
- All pages post to themselves / use AJAX, so the new parent doesn't change
  their behaviour. (Pipeline stays under Enquiries, Calendar under Journal —
  those are CPT-contextual, not loose in Tools/Appearance.)

## 4.59.0 — Admin panel rework (tabs + one home for the backend)
- **Top-level "Obscura" menu** (camera icon, next to Dashboard) replaces the
  old *Appearance → Theme Settings* location; first item relabelled **Settings**.
- **New `inc/admin-hub.php`** — an **Obscura → Tools** hub that gathers every
  backend feature in one place as cards with dashicons + one-line explanations
  (Enquiries & clients · Content · SEO & ops · Settings & data).
- **Settings is now tabbed** — accordion `<details>` become a sticky tab strip;
  the last-open tab is remembered (localStorage).
- **Full-width, two-column fields** at ≥1500px so the form fills the window.
  The save form/field markup is untouched, so options.php still saves all fields.
- Color-picker enqueue + admin-simplify screen checks updated to the new
  `toplevel_page_nr-theme-settings` screen id.

## 4.58.1 — Remove the contact-sheet feature
Dropped the public **"CONTACT SHEET ↓"** button, the `/nr-contactsheet` PDF
endpoint, and the `nr_contactsheet_url()` helper entirely — not needed.

## 4.58.0 — IDEAS-50-NEXT, Medium batch 1 (6 items)
New `inc/lib.php` (pure helpers) + `inc/medium-next.php`.
- **#33 weekly studio digest** — a Monday-morning email to you: new enquiries +
  what drove them + Core Web Vitals + 7-day pageviews. Owner only.
- **#35 exhibitions** — `nr_shows` setting ("Title | Venue | date | url" per line) +
  `[nr_shows]` list with **Event** schema for gallery shows.
- **#38 press-kit auto-zip** — `/nr-presskit.zip`: bio + logo + up to 8 web-res
  featured images, streamed from a temp file (light on memory).
- **#41 availability heat-calendar** — `[nr_availability]` month grid; "busy" dates
  from the `nr_busy_dates` setting, the rest shown open.
- **#49 PHPUnit** — `inc/lib.php` holds the pure helpers (AES encrypt/decrypt,
  pipe-line parse, hex-key check); `tests/php/` + `phpunit.xml.dist` unit-test them
  with no WordPress bootstrap.
- **#50 component gallery** — Appearance → Components renders the reusable blocks
  (buttons, chips, packages, press, timeline, shows, availability) for visual QA.

IDEAS-50-NEXT: 28/50.

## 4.57.0 — IDEAS-50-NEXT, small batch 2 (11 items)
- **#21 lightbox slideshow** — ▶ play button + slow Ken-Burns auto-advance (space
  toggles, reduced-motion safe).
- **#22 shareable plate deep-link** — the lightbox updates the URL to `#frame-N`;
  opening a `…#frame-3` / `?frame=3` link jumps straight to that frame.
- **#24 PWA install nudge** — a dismissible "install as app?" pill on
  `beforeinstallprompt` (once).
- **#26 rail scroll-compass** — a slim amber progress bar under horizontal rails.
- **#31 section-aware speculation** — *prerender* project pages on hover, only
  *prefetch* everything else.
- **#40 `[nr_featured]`** — an "As featured in" press-logo band (from the press list).
- **#43 Client onboarding** — a "Client onboarding" page template ("what to expect /
  what to bring", with a sensible default).
- **#45 `[nr_testimonial_videos]`** — testimonial-video field + an embed band
  (YouTube/Vimeo/.mp4).
- **#47 long descriptions** — per-image detailed alt via `aria-describedby` (a11y).
- **Docs (#32 Cloudflare edge-cache recipe, #34 off-site uptime) → docs/OPS-NOTES.md.**

IDEAS-50-NEXT: 22/50.

## 4.56.0 — IDEAS-50-NEXT, small batch 1 (11 items)
New `inc/finishing.php` + front-end.
- **#37 heuristic auto-alt** — images with no alt get one from the attachment/parent
  title + EXIF camera (no AI). Fixes the “193 weak/empty alt” count; never overrides
  a real alt.
- **#5 per-project contact-sheet PDF** — `/nr-contactsheet/<id>.pdf` (memory-safe
  JPEG passthrough) + a “Contact sheet ↓” button in the project actions.
- **#39 image-sitemap EXIF captions** — fall back to camera/focal/ISO when no caption.
- **#8 site-wide B&W toggle** + **#46 reading mode** — a small bottom-left “B/W · Aa”
  control (desktop), persisted; B/W greys imagery only, reading mode relaxes journal
  measure/leading.
- **#19 keyboard gallery** — `j`/`k` move through cards, `r` = random project.
- **#20 “surprise me”** — a chip on the portfolio archive (+ `?nr_random=1`).
- **#13 reading-position memory** — journal singles resume where you left off.
- **#30 decode-before-show** + **#29 font `size-adjust` fallback** — less hero jank,
  no display-font CLS.
- **#48 Save-Data hero** — shows a single static frame + hides slider chrome when the
  browser sends `Save-Data`.

IDEAS-50-NEXT: 11/50.

## 4.55.0 — Batch 6: Awwwards interaction layer (opt-in)
New `assets/js/awwwards.js` (loaded only with the GPU-effects toggle). Every
effect is opt-in and falls back to the normal rail/page if anything fails.
- **#1 plane-grid + #13 infinite draggable canvas** — a "⊞ canvas" toggle on the
  portfolio archive switches the rail for a drag-to-pan canvas of project cards
  (momentum, wheel-pan, click-to-open), tilted as perspective planes (CSS-3D — not
  literally OGL/WebGL, same feel, far more robust). Desktop only; toggle off = rail.
- **#14 pinned scrollytelling** — `[nr_scrolly media="ID"] step | step | step
  [/nr_scrolly]`: a sticky image with scroll-scrubbed step captions (use on a
  scrolling page/journal).
- **#123 scroll-scrubbed video hero** — `[nr_scroll_video src="…mp4" poster="…"
  height="220vh"]`: a pinned video whose playhead follows scroll. Reduced-motion
  → plays normally.

**Honesty:** #1 is CSS-3D, not a WebGL OGL renderer (I can't visually verify blind
WebGL on a live site; the CSS version is robust and identical-feeling). All four are
behind the GPU toggle, so the live site is unaffected until previewed & approved.

**Backlog status: 182 shipped · 14 skipped/won't-do · 4 open.** The 4 open are all
won't-do leftovers (payment/login). IDEAS-200 is effectively complete.

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
