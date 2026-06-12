# Obscura — Project Handbook & Changelog

> **Read this first.** This file is the single source of truth for the Obscura theme:
> everything you need to resume work from a cold start — what it is, how it's built,
> how to ship & deploy it, the hard-won gotchas, and the current state — followed by
> the full version history. If you only have this file, you have the project.

**Current version: v4.71.0** · **98 releases** · Branch `claude/obscura-rebuild` · PR
[#13](https://github.com/MokshaOne/WP-THEME/pull/13) (draft) · Repo `mokshaone/wp-theme`.

---

## 1. What this is

A **bespoke, classic (non-block) PHP WordPress theme** for **raveenthiran.com** — a
Vienna photographer's **enquiry-based** portfolio.

- **Hosting:** easyname **shared hosting**, behind **free Cloudflare** (DNS on Cloudflare).
- **Philosophy:** **plugin-light** — prefer self-hosted, theme-built solutions over plugins.
- **Ships as:** an installable **ZIP** (`raveenthiran-obscura-vX.Y.Z.zip`) at the repo root.
- **Language:** English only (no bilingual).
- **Images:** the studio works in **AVIF / WebP** (no readable EXIF — all EXIF features removed).

**Hard NOs (owner decisions — do not build these):** payment system, client logins /
proofing galleries, bilingual DE/EN, dark/light toggle, Instagram auto-feed, and **never
recommend paid Cloudflare features** (Polish/Mirage/Image Resizing) — do image work in the theme.

## 2. Where things live

- **Theme root:** `Raveenthiran.com/raveenthiran-obscura/`
- **Templates (root):** `front-page.php` (fullscreen hero), `archive-nr_project.php`
  (portfolio overview = horizontal slider), `single-nr_project.php`, `archive-nr_journal.php`,
  `single-nr_journal.php`, `taxonomy.php`, `search.php`, `index.php`, `404.php`,
  `page-enquire.php` (booking+contact+price estimator+FAQ), `page-about.php`, legal pages,
  `header.php`, `footer.php`.
- **Feature modules:** `inc/*.php`, loaded in order by a `foreach` array in `functions.php`
  (~line 344). **`lib.php` first**; order matters. Add a new feature as a new `inc/<name>.php`
  (guard with `if ( ! defined( 'ABSPATH' ) ) exit;`) and append it to that array.
- **Assets:** `assets/css/theme.css` (main, ~150KB), `assets/css/fonts.css` (@font-face only,
  **inlined into `<head>`**), `assets/js/theme.js` (main), `assets/js/gpu-fx.js` +
  `assets/js/awwwards.js` (opt-in GPU bundle), `assets/js/webgl-hero.js` (opt-in),
  `assets/fonts/*.woff2` (Inter Tight 300/400/500/600/700 + JetBrains Mono 400/500 — **all in use**).
- **Build:** `bin/nr-build.sh` (lint + version bump + ZIP), `bin/nr-bump.sh`.
- **Docs:** this file (root `CHANGELOG.md`) + theme `CHANGELOG.md` (mirror, theme-relative).

## 3. Architecture & conventions

- **CPTs:** `nr_project` (taxonomies `nr_project_cat`, `nr_project_tag`, `nr_project_series`),
  `nr_journal` (`nr_journal_cat`), `nr_testimonial`, private `nr_enquiry` (auto-logged on form
  submit), private `nr_subscriber`.
- **Settings:** every option is a `wp_options` row read via **`nr_opt('nr_*', $default)`**.
  Defaults + whitelist live in **`nr_settings_defaults()`** (`inc/theme-settings.php`). Saved via
  `options.php` + `settings_fields('nr_theme_settings_group')`; sanitised by
  `nr_settings_sanitize_field` (allows `<em><br><strong><b>`).
- **Toggles:** render with `nr_field_toggle`. A toggle persists "off" if its key is prefixed
  **`nr_fx_*`** OR listed in `$toggle_keys`. Body classes (`nr-has-*`, `nr-fx-*`) gate JS/CSS.
- **ACF optional:** `inc/acf-polyfill.php` stubs ACF; read fields via **`nr_field()`**
  (`get_field` → `get_post_meta`). Option-page fields fall back to `get_option('options_'.$key)`.
- **Design tokens** (inlined `:root` in `header.php` + `theme.css`): `--bg #0B0C10`,
  `--ink #F2EFE9` (bone), `--amber #F2A03D`; `--ink-3` is muted text at **.50 opacity ≈ 4.7:1**
  (WCAG AA). Fonts: **Inter Tight** (display/body) + **JetBrains Mono** (chrome), self-hosted woff2.
- **Layout model:** **desktop = fixed single-screen** (`.nr-fullscreen` / `.nr-hero` are
  `position:fixed; inset`), **no scroll**; **mobile (≤900px) = normal scrolling** with a bottom
  tab bar. This desktop/mobile split is the source of most "works on mobile, breaks on desktop"
  asymmetries (e.g. the CLS saga below).
- **The golden rule:** anything that can change the live look or break (WebGL hero, View
  Transitions, GPU/iris effects, async CSS, intro preloader, line-reveal headings) ships behind a
  **Theme-Settings toggle defaulting to `'0'`**, with graceful fallback (reduced-motion / no-WebGL
  / no-JS / ≤900px). The owner flips it on, previews, keeps it only if they like it.

### Module map (`inc/`, load order)
`lib.php` (helpers — load first) · `acf-polyfill` · `functions-additions` · `acf-fields` ·
`performance` (WebP/LQIP on upload) · `seo` (schema/sitemap/robots) · `theme-settings` ·
`quote` (pricing + Business&SEO option pages) · `tier1`/`tier2` · `medium`/`medium-next`/
`medium2`/`medium3`/`medium4` · `importer` (ZIP project import) · `security` (Turnstile) · `pwa`
(virtual `/nr-sw.js` + manifest) · `compare` (`[nr_compare]`) · `og-cards` (GD share cards) ·
`pdf` (estimate writer) · `series` · `interlink` · `map` (Leaflet `[nr_map]`) · `smtp` · `insights`
(enquiry dashboard) · `webp` (`<picture>` twins + bulk Tools page) · `admin-extras`/`admin-hub`/
`admin-simplify` (Obscura admin menu) · `ideas-next` · `seo-extra` (ImageObject/preload) ·
`conversion-extra` (`[nr_press]`) · `quickwins`/`smallwins`/`mediumwins`/`mediumwins2` · `infra` ·
`studio-ops` · `finishing` (`[nr_featured]`) · `leftovers` · `districts` (`[nr_district]` local SEO) ·
`preshoot` (shoot-date cron). Press/Awards rendering helper: **`nr_recognition_list()`** in `functions.php`.

## 4. Build & ship workflow (every change)

1. **Edit**, matching surrounding style (tabs, naming, comment density). The Edit tool fails on
   tab/space mismatch — Read first, or use a Python string-replace with exact `\t`.
2. **Lint:** `php -l` every changed `.php`; `node --check` every changed `.js`; CSS brace balance
   (`grep -c '{' == grep -c '}'`).
3. For testable-without-WP logic, write a tiny stub harness and actually run it.
4. **Build:** `bash bin/nr-build.sh X.Y.Z` — bumps `NR_THEME_VERSION` (functions.php),
   `Version:` (style.css), `Stable tag` (readme.txt); lints; writes
   `raveenthiran-obscura-vX.Y.Z.zip` at repo root.
5. **Update both changelogs** (this file + theme `CHANGELOG.md`).
6. **Commit** (concise body), **push** `git push -u origin claude/obscura-rebuild` (retry w/ backoff
   on network error), keep PR #13 current.
7. **Deliver the ZIP** to the owner with a short "what changed / how to test".

> **Patch** = fixes, **minor** = features. Keep the model identifier out of commits/PRs/code.

## 5. Deploying to the LIVE site — READ THIS (cost us a whole debugging saga)

- **Deploy via WP-Admin → Appearance → Themes → Add New → Upload Theme → ZIP → "Replace active
  with uploaded".** This overwrites **all** files atomically.
- **Do NOT deploy by FTP file-by-file.** Partial/failed FTP uploads were the root cause of a long
  CLS hunt: `style.css` got replaced (so Themes showed the new version) while `functions.php`,
  `theme.css`, and `header.php` stayed old — so **none of the fixes were actually live** even though
  the version number looked right.
- **Cache purge order after deploy:** **1) W3TC "Purge All Caches" (origin) → 2) Cloudflare "Purge
  Everything" (edge).** Purging only Cloudflare is useless — it just re-pulls the still-cached page
  from the origin. Keep **W3TC Minify OFF** (it breaks JS/CSS; Brotli handles size).
- **Verification (5-second check):** view source and confirm assets load as
  **`theme.css?ver=<the version you just shipped>`**. The `?ver` comes from `NR_THEME_VERSION` in
  `functions.php` — if it shows an **older** version than the Themes page, your upload was **partial**
  (PHP not replaced). A query-string URL like `raveenthiran.com/?x=1` bypasses W3TC + is a fresh
  Cloudflare key → shows the true current origin output.

## 6. Performance playbook (measured, not guessed)

Diagnose with a fresh **pagespeed.web.dev** run; read the **filmstrip** + the **"Layout shift
culprits"** / **LCP** breakdowns. Test 2–3× (the run right after a purge is cold/worst).

- **Images / LCP (the big mobile lever).** Originals are **AVIF**; the shared host **cannot resize
  AVIF**, so WordPress writes **JPEG sub-sizes** and the theme makes a **`.webp` twin** of each
  (`inc/webp.php`: at upload + on-demand capped 8/req + a **Obscura → Generate WebP** bulk page).
  Delivery wraps `wp_get_attachment_image()` in `<picture><source type=image/webp>`.
  - **Run "Generate WebP" once + purge** after adding media, so every size has a twin.
  - **The LCP preload must point at the WebP the page serves — never the raw full-size AVIF**
    (that bug = 8.5s mobile LCP). `front-page.php` + `inc/seo-extra.php` now derive the preload from
    the WebP srcset and skip the preload entirely if no WebP exists. Result: mobile **74→95**, LCP **8.5s→2.7s**.
  - `nr-hero` image size = 2400×1600 (uncropped, `object-fit:contain`). Cards/plates carry accurate
    `sizes`; first hero/plate gets `fetchpriority="high"`.
- **CLS.** The fixed-desktop layout + the giant hero title are the usual suspects. **All Inter Tight
  weights are in use** (don't drop any → FOUT). The `.nr-modal` (pricing/enquiry) is `display:none`
  when closed so a viewport-size modal can't score ~1.0 CLS. Hero title display weights (300/700,
  preloaded) use `font-display:optional`. **If desktop CLS is "stuck" at a byte-stable ~1.0 across
  several fixes, suspect a stale/partial deploy (§5) before more code.**
- **Fonts:** `fonts.css` is inlined; weights 300/500/700 preloaded with `crossorigin`. `font-display:
  swap` for body, `optional` for the display weights.
- **JS/CSS:** deferred; no third-party analytics. Opt-in bundles (`gpu-fx.js`+`awwwards.js` via
  `nr_fx_gpu`, `webgl-hero.js` via `nr_fx_webgl`) only load when toggled on. The **"round" full-screen
  reveal on load = the aperture-iris from `nr_fx_gpu`** — turn that toggle off to remove it.
- **Stop at ~90 mobile / ~95 desktop.** Minify (~8KB post-Brotli), unused-CSS splitting, preconnect
  (everything is same-origin) are diminishing returns — say so honestly.

## 7. Email deliverability (Google Workspace, `inc/smtp.php`)

`phpmailer_init` SMTP. Settings: host `smtp.gmail.com`, port 587 STARTTLS, **Username = the real
login mailbox** (e.g. `hq@m1o.at`, NOT the alias), **App Password** (or `NR_SMTP_PASS` constant),
**From = a verified "Send mail as" alias** (`office@raveenthiran.com`). Includes a "send test email"
button. DNS in **Cloudflare**: one SPF TXT (`v=spf1 include:_spf.google.com include:spf.easyname.com
~all`), **DKIM** generated in Google Admin (Apps → Gmail → Authenticate email → Start authentication —
else Google signs with the generic `gappssmtp.com` key and DMARC fails alignment), DMARC on `_dmarc`.
Verify with port25 `check-auth@verifier.port25.com`. **Exactly one** SPF/DKIM record each.
**Security:** a previously-exposed Application Password must be treated as **compromised — rotate it.**

## 8. Self-built (no-plugin) capabilities

Bulk ZIP project importer · PWA/offline (virtual SW + manifest, persistent `nr-reader` cache) ·
dynamic OG share cards (GD, needs a bundled TTF) · dependency-free PDF estimate writer · Turnstile
spam shield (fails open if unconfigured) · Leaflet map · before/after `[nr_compare]` · enquiry
attribution + insights dashboard · keyword tags + multi-filter · series · video gallery plates ·
Vienna district local-SEO pages (`[nr_district]`) · reference-image upload on the Enquire form ·
pre-shoot T-7/T-1 info emails (cron) · Press list as a 3-field repeater (`[nr_press]` / `[nr_featured]`).

## 9. Gotchas

- **Partial FTP deploys** (see §5) — the #1 time-sink. Prefer the WP ZIP uploader.
- **Cache layering** — purge W3TC (origin) before Cloudflare (edge); `?x=1` bypasses both.
- **Lighthouse mis-attribution** — it blamed a fullscreen `#nr-quote` overlay for a shift that was
  really elsewhere; trust the **mobile-vs-desktop asymmetry** more than the named element.
- **AVIF can't be resized** on this host → rely on JPEG sub-sizes + WebP twins, not AVIF sub-sizes.
- **Edit tool tab/space** mismatches — Read first or Python-replace with exact `\t`.
- Sandbox: GD/AVIF + pypdf are limited; validate with tiny stub harnesses.

## 10. Current state & open items

- **Mobile: ~95** (LCP 2.7s, CLS ~0.06) — done.
- **Desktop:** LCP 0.9s (great); **CLS** fix (`.nr-modal{display:none}`) is correct and, as of the
  **full** v4.70.3 deploy, finally live — **pending the owner's purge + re-measure** (expected
  CLS ~1.0 → ~0.06, score → ~95).
- A11y & SEO already score **100**.
- **Backlogs complete:** IDEAS-200 and IDEAS-50-NEXT both fully shipped/closed.
- **Possible next polish (not started):** Awards as a multi-field editor (like Press); font
  subsetting (~260KB of woff2 → could roughly halve); email deliverability (SPF/DKIM/DMARC) — needs
  DNS access; rotate the compromised App Password.

---

## Version history

Every shipped version of **raveenthiran-obscura**, newest first.

---

## v4.71.0 — 2026-06-12
Booking embed: new `inc/booking.php` + `[nr_booking]` shortcode embeds a Google Calendar **Appointment Schedule** (real booking page) with **click-to-load** — the Google iframe loads only after the visitor clicks, so no third-party request / LCP / CLS / privacy hit on page load (DSGVO-friendly); falls back to the enquiry form when unset. Owner pastes the embed code/URL into Theme Settings → "Booking embed (Google)"; only `calendar.google.com` / `calendar.app.google` hosts are accepted. Needs an Appointment Schedule link, not the read-only `calendar/embed` view.

## v4.70.3 — 2026-06-12
Desktop CLS: the hero headline is up to 176px on desktop vs 84px on mobile, so the web-font swap reflowed the giant title (~1.0 desktop, ~0.14 mobile — the observed asymmetry). The display weights (Inter Tight 300 + 700, both preloaded) now use font-display:optional → the title never swap-reflows (preloaded font used if it arrives in the block window, else fallback kept with no later swap). Body weights stay swap. Rides in the inlined font CSS, so it lands with v4.70.2 (preloader off) on one cache purge.

## v4.70.2 — 2026-06-12
Real fix for the desktop CLS (~1.0): the full-screen intro preloader runs on a fixed fake timer and fades out while the hero is still loading, revealing the settling hero (image+font) mid-shift = counted as layout shift. It only ran on desktop (display:none on mobile) — exactly matching the asymmetry (desktop CLS ~1.0, mobile fine). Now a Theme Settings toggle, default OFF. The v4.69 modal and v4.70.1 font-preload changes were correct hardening but not the cause. Re-enable the intro under Obscura → Settings if desired.

## v4.70.1 — 2026-06-11
Desktop CLS fix: the big front-page hero headline uses Inter Tight weight 300, which wasn't preloaded (only 500/700 were) — it painted in the fallback font then reflowed on swap (large desktop CLS; mobile unaffected because the title is much smaller). Weight 300 is now preloaded. Note: the v4.69 `.nr-modal{display:none}` change was correct hardening but not the CLS cause — Lighthouse had mis-attributed the shift to the full-screen overlay.

## v4.70.0 — 2026-06-11
Press list is now a 3-column repeater (Year · Publisher · Link) with add/remove rows instead of one free-text box. Saves into the same option as canonical `year | publisher | url` lines (save pipeline unchanged); degrades to a textarea without JS. `nr_recognition_list` made tolerant of space-separated lines so existing entries (`2025 Heute.at https://…`) parse correctly. Publisher renders as a clickable link on the site (Press wall, “As featured in”, About page); the URL is never shown as raw text.

## v4.69.0 — 2026-06-11
PageSpeed fixes from real field data (mobile LCP 8.5s, desktop CLS 1.064). **Mobile LCP:** the hero preload was emitting the full-size AVIF original (~470KB) with `type=image/webp` when no WebP twin existed, front-running the `<picture>` (which serves a small WebP) → heavy LCP. Preload now always matches the served WebP and never preloads a raw AVIF (front-page.php + seo-extra.php). **Desktop CLS:** `.nr-modal` was `display:flex` while closed → a viewport-size modal scored ~1.0 CLS; now `display:none` when closed (fade kept via discrete transitions). Plus accurate card `sizes`, first project plate `fetchpriority=high`, real attachment alt on gallery plates, `--ink-3` contrast to ~4.7:1. Owner: run Tools → Generate WebP once + purge cache.

## v4.68.2 — 2026-06-11
Removed the alternate draggable "canvas" view on the portfolio overview — the "⊞ canvas / ☰ rail" toggle (added by the opt-in GPU layer) and the scattered plane-grid it produced. The overview is now only the horizontal slider (rail + arrows + wheel/drag). Canvas JS removed from `awwwards.js`, `.nr-canvas*` CSS removed; scrollytelling + scroll-video hero untouched.


## v4.68.1 — 2026-06-11
Restored the horizontal slider (rail + arrows + wheel/drag) on the portfolio overview — the v4.64 grid switch was a misunderstanding (the owner disliked the collapse bug, which v4.63 had already fixed, not the slider). Collapse fix + a min-height floor stay; grid CSS/JS selectors removed.


## v4.68.0 — 2026-06-11
Dead-code purge — removed code for the features retired in v4.65 that was still shipping (gated off) on every page: the interface-sound, recently-viewed and testimonials-band JS blocks; the `.nr-sound`/`.nr-marquee`/`.nr-testi-band`/`.nr-news`/`.nr-wiz`/`.nr-moods` CSS; and the uncalled `nr_recent_strip_markup()`/`nr_newsletter_form_markup()`/`nr_testimonials_band_markup()` PHP + the dead recently-viewed footer block. No functional or visual change — smaller JS/CSS payloads. The live rail and ⌘K contact-sheet view were left intact.


## v4.67.0 — 2026-06-11
Finished IDEAS-50-NEXT (44/50 shipped, 6 won't-do). New `inc/medium4.php` + `inc/preshoot.php`: dominant-colour image placeholders (#27, a light BlurHash stand-in via GD at upload), `[nr_moodboard series=""]` cover grid + palette (#15), `[nr_hotspots img=""]x,y | note ;; …[/nr_hotspots]` annotated pins (#12), persistent `nr-reader` service-worker cache so opened journal articles survive updates (#25), optional reference-image upload on the Enquire form attached to the enquiry (#42), and a "Shoot date" + daily cron sending info-only T-7/T-1 prep emails (#44).


## v4.66.0 — 2026-06-11
IDEAS-50-NEXT #36 — Vienna district landing pages. New `inc/districts.php` adds a `[nr_district name="Neubau" code="1070" cat="" intro="" count="6"]` shortcode: localised heading + intro, a small project grid, an enquire CTA, and ProfessionalService/areaServed JSON-LD for the long-tail local query. Plugin-light (no rewrite rules, no new admin pages). 38/50 shipped, 6 Medium open.


## v4.65.0 — 2026-06-11
Removed 7 unused opt-in toggles + their features (Colour-mood filter, Enquire wizard, Newsletter capture, Recently viewed, Testimonials band, Footer marquee, Interface sound) — settings rows, defaults, body classes and footer renders all gone. Slimmed the ACF "Pricing & Quote" page to only Quote + License: deleted the unused legacy groups (Hero/About/CTA/FAQ/Availability), the Newsletter group, and the Awards/Press groups (duplicated Theme Settings); moved Local SEO to its own "Business & SEO" page. `[nr_press]`/`[nr_featured]` now read the single Theme Settings press list.


## v4.64.0 — 2026-06-11
Replaced the horizontal portfolio rail with a plain responsive grid that scrolls vertically (`.nr-portfolio-rail` → `.nr-portfolio-grid`, `data-h-rail` dropped; filtering, colour-mood chips and keyboard gallery still work; rail-collapse bug retired). Awards & Press: `nr_recognition_list()` is now URL-aware — a link in any position is used as the link target and never shown as text (fixes the URL leaking into the "organisation" column on `year · name · url` entries).


## v4.63.0 — 2026-06-11
Removed every EXIF feature — the studio works in AVIF/WebP, which carry no readable EXIF (so the EXIF-on-upload extractor, #43 EXIF→year, auto-alt camera hint, sitemap caption fallback, per-plate "Shot on" caption, and the EXIF-based ideas #2/#3/#4/#9 are gone; #1 → won't-do). Fixed the portfolio-rail collapse (cards sized off a rail row that collapsed when `:has()` didn't resolve → explicit `grid-template-rows` + rail min-height). Medium batch 3, new `inc/medium3.php`: `[nr_wallpreview]` print-size wall preview (#6), `[nr_burst]` sequence viewer (#10), shareable shortlist via "Copy share link" + `?shortlist=` import (#23). 37/50 shipped, 7 Medium open.


## v4.62.0 — 2026-06-11
IDEAS-50-NEXT small leftovers (5 items), new `inc/leftovers.php`: time-of-day facet from EXIF hour + `[nr_tod]` + `?tod=` archive filter (#4), `[nr_diptych]` side-by-side compare (#14), `[nr_studiolog]` public studio-log timeline (#17), footnoted credits now link collaborators via an optional "Role | Name | URL" (#18), and a no-ffmpeg video first-frame poster (#28). S-tier fully cleared — 38/50 shipped, 11 Medium open.


## v4.61.0 — 2026-06-10
IDEAS-50-NEXT Medium batch 2 (6 items), new `inc/medium2.php`: "Shot on" EXIF facet + portfolio filter + `[nr_shot_on]` (#2), `[nr_focal_chart]` focal-length histogram (#3), `[nr_related_gear]` related-by-camera (#9), `[nr_masonry]` aspect-true archive (#7), `[nr_fieldnotes]` micro-journal (#11), `[nr_pullquotes]` rotator (#16). All self-contained; one-time gear backfill for existing projects. 33/50 shipped, 16 open.


## v4.60.0 — 2026-06-10
Admin consolidation: every theme-added admin page is now a submenu of the top-level **Obscura** menu — nothing left in Tools or Appearance. Re-parented Components, Feature flags, Tag clusters, Series grid, Pricing & Quote (from Appearance) and Generate WebP, Obscura log, Redirects, Alt texts (from Tools). Dropped the obsolete `remove_submenu_page('themes.php',…)` calls and the duplicate Settings-page notice; dashboard quick-links + Tools hub links point at the new `admin.php?page=` URLs.


## v4.59.0 — 2026-06-10
Admin panel rework: Theme Settings moved out of Appearance into a top-level **Obscura** menu (dashicons-camera, next to Dashboard). New `inc/admin-hub.php` adds an **Obscura → Tools** hub grouping every backend feature as cards with icons + one-line explanations. Settings upgraded from accordion `<details>` to a sticky tab strip with full-width, two-column fields (≥1500px), active tab persisted in localStorage. Save markup untouched.


## v4.58.1 — 2026-06-10
Removed the per-project contact-sheet feature entirely — the public "CONTACT SHEET ↓" button, the `/nr-contactsheet` PDF endpoint, and the `nr_contactsheet_url()` helper. Owner doesn't need it.


## v4.58.0 — 2026-06-10
IDEAS-50-NEXT Medium batch 1: weekly studio digest email (#33), exhibitions setting + `[nr_shows]` with Event schema (#35), `/nr-presskit.zip` auto-zip (#38), `[nr_availability]` heat-calendar (#41), PHPUnit + pure-helper `inc/lib.php` (#49), Appearance → Components gallery (#50). Repo CHANGELOG refreshed.


## v4.57.0 — 2026-06-10
IDEAS-50-NEXT small batch 2: lightbox slideshow + plate deep-link (#21/#22), PWA nudge (#24), rail compass (#26), section-aware speculation (#31), [nr_featured] (#40), onboarding template (#43), testimonial videos (#45), long-descriptions (#47); CF edge-cache + uptime docs (#32/#34)


## v4.56.0 — 2026-06-10
IDEAS-50-NEXT small batch 1: auto-alt (#37), contact-sheet PDF (#5), sitemap EXIF captions (#39), B&W + reading mode (#8/#46), keyboard gallery + surprise (#19/#20), reading position (#13), decode + font size-adjust (#30/#29), save-data hero (#48)


## v4.55.0 — 2026-06-10
Awwwards layer: canvas/plane-grid archive (#1/#13), scrollytelling shortcode (#14), scroll-video shortcode (#123). Opt-in via GPU toggle.


## v4.54.0 — 2026-06-10
studio workflow: mini-CRM pipeline (#65), follow-up reminders (#66), auto-tag suggestions (#151), delivery proofing (#59). #171 bilingual won't-do.


## v4.53.0 — 2026-06-10
Batch 6 GPU: #3 particle dispersion on card hover; #2 verified (webgl-hero displacement). 179/200.


## v4.52.0 — 2026-06-10
small TODO remnants

#67 abandoned-quote recovery (localStorage resume bar on Enquire), #70 trusted-by
link-out + animated count badge, #154 Obscura block patterns (packages/press/
timeline/map/on-this-day/pull-quote), #182 encrypted enquiry export (AES-256-CBC
.enc + passphrase form). #75 was already shipped (alternative-date field) — ticked.
#85 SVG sprite skipped with rationale (theme uses typographic glyphs by design).

177/200 shipped · 3 skipped (#34/#85/#87).


## v4.51.7 — 2026-06-10
journal prose in 2 columns on wide screens (>=1200px), headings/figures kept intact


## v4.51.6 — 2026-06-10
enquire FAQ as popover behind a button under the form


## v4.51.5 — 2026-06-10
Studio: stats as a 2x2 block beside the bio text


## v4.51.4 — 2026-06-10
Studio: stats row pulled up under the bio (more air below)


## v4.51.3 — 2026-06-10
dither toned way down + no empty portrait frame

The #5 dither field rendered as a loud checkerboard over content (alpha 18 +
opacity .5) — now alpha 7 + opacity .12, a barely-there texture as intended.
About page: when no featured image is set the portrait frame no longer renders
as an empty bordered box; the text column gets the space.


## v4.51.2 — 2026-06-10
breadcrumbs read as content, not a second header bar

Dropped the toolbar bottom-border on .nr-project__crumbs and raised the top
padding (30px) so the crumb row sits clearly below the top bar instead of
looking glued to it. Background transparent (project already starts at
top:var(--bar-h), so no overlap).


## v4.51.1 — 2026-06-10
fix single-project layout + Array output

- Left panel (.nr-project__body) now scrolls (overflow-y:auto) instead of
  clipping the new sections; bottom padding for the CTA.
- Breadcrumbs get breathing room + solid background below the top bar.
- Film-strip nav (#33) pinned to the bottom centre of the rail instead of
  floating over the top; hidden on mobile.
- New nr_field_str() flattens array field values — fixes the literal 'Array'
  printed in the meta panel (gear/credits/frames/format now array-safe).


## v4.51.0 — 2026-06-10
Batch 6 r2: security & infrastructure (7 items)

New inc/infra.php (all off by default):
CSP builder w/ report-only + enforce + violation log (#176), GDPR export/erase
via WP's native tools (#180), HTTP 103 Early Hints for hero fonts (#91),
self-hosted cookieless pageview analytics + widget (#106), root-scoped service
worker /nr-sw.js with offline page + SWR (#93), offline enquiry queue with
fetch-replay on reconnect (#94), virtualised rails via content-visibility (#88).

171/200 shipped.


## v4.50.0 — 2026-06-10
Batch 6 r1: GPU/canvas effects (opt-in)

New nr_fx_gpu toggle (default off) loading assets/js/gpu-fx.js only when on:
heat-haze idle WebGL shader on the hero (#4), animated Bayer-dither field (#5),
metaball cursor trail (#8), aperture-iris page reveal (#9). All gated by
reduced-motion, Save-Data, motion level and viewport; WebGL absence skips
silently. 164/200 shipped.


## v4.49.0 — 2026-06-10
IDEAS-200 Medium tier r2 (19 items, tier complete)

inc/mediumwins2.php + page-delivery.php + JS/CSS.
Conversion: enquire wizard (#42, opt-in), A/B hero CTA + counters (#48), PDF
estimate verified shipped (#51), delivery page template (#60), newsletter
double opt-in (#72).
Editorial: case study (#62), focal point (#127), story mode (#131), colour-mood
filter (#135), memory-safe lookbook PDF /nr-lookbook/<slug>.pdf (#124).
Perf/type: art-directed picture (#82), variable-font support + hero weight
morph (#83/#10), Lighthouse job now a real gate (#118).
Admin/ops: bulk alt editor (#149), editorial calendar (#153), sample content
(#160), daily self-test cron + alert (#187), settings-import diff preview (#191).
Skipped w/ rationale: #34, #87.

Medium tier complete — 160/200 shipped.


## v4.48.0 — 2026-06-10
simplified admin (toggleable)

inc/admin-simplify.php: hides Posts/Comments/ACF menus + Theme File Editor,
removes the stock dashboard widgets, merges the four theme widgets (Theme
health · Content health · Self-test · Field metrics) into one collapsible
'Obscura — health & metrics' widget, and trims Feature flags / Tag clusters /
Series grid out of the Appearance menu (still reachable via quick links).

Controlled by the new 'Simplified admin' toggle (nr_admin_simplify, default
on; turn off in Theme Settings to restore the stock admin).


## v4.47.0 — 2026-06-10
IDEAS-200 Medium tier r1 (24 items)

New inc/mediumwins.php + JS/CSS + configs.
Motion: parallax (#17), spotlight (#26), lightbox (#31), video scrubber (#32),
film-strip (#33), inertial scroll (#11), reduced-motion audit (#39).
Editorial: diptych rhythm (#121), cover aspect (#129), series chapters (#132),
[nr_timeline] (#133), [nr_map_all] (#134), video transcript (#170).
A11y: forced-colors (#164), target sizes (#167), cognitive-load (#168), RTL (#174).
Perf/SEO: AVIF twins (#81), CWV field data (#107), funnel (#108), redirect UI (#111).
Dev: PHPCS config (#198), visual-regression spec (#200), de_DE.po (#172).

Medium r2 holds the heavier subsystems. 141/200 shipped.


## v4.46.0 — 2026-06-10
IDEAS-200 'Small' tier (49 in one go)

New inc/smallwins.php + JS/CSS, two admin pages, dev/CI configs. Cut with the
new one-command build (bin/nr-build.sh, #195).

Motion: shockwave (#6), idle screensaver (#19), spring-release (#21), mobile
section-snap (#20), sound waveform (#30), compare keyboard+labels (#38).
Conversion: lead-score column (#43), similar work (#45), .ics (#54), VAT (#55),
add-ons verified (#56), request-testimonial action (#61), brief autosave (#80),
hold-two-dates (#75).
Editorial: credits (#140), per-project map (#139), treatments (#130), hero focus
(#128), full-bleed (#122), contact-sheet (#126), captions toggle (#157), journal
kicker/dek (#145), [nr_compare] (#125), [nr_onthisday] (#137), [fn] (#143),
glossary (#144), [nr_howto] (#115), [md] (#147).
A11y: SR live-region (#161), landmarks (#162), ? overlay (#163), reduced-
transparency (#165), focus-visible (#166), alt-text linter (#150).
SEO/Perf: idle prefetch (#95), outbound link checker (#112), unicode-range
confirmed (#84).
Ops/Sec/Dev: tag clusters (#152), draft-preview tokens (#155), consistency grid
(#159), spam escalation (#178), audit log (#179), consent counter (#181), self-
test widget (#186), error-log viewer (#189), migration scaffold (#190), feature-
flag registry (#194), axe job (#169), backup note (#192).

Not done: #67, #70, #85, #182 (see CHANGELOG). 117/200 shipped.


## v4.45.0 — 2026-06-10
IDEAS-200 quick wins (25 in one go)

New inc/quickwins.php + hooks, two journal reading aids, a print sheet,
dev configs and ops docs.

SEO/feeds: og:updated_time (#103), /sitemap-index.xml (#99), per-series RSS
(#138), hreflang-alternates filter (#173), decoding=async backstop (#89).
Editorial/UX: tag chips (#136), gear field (#156), reading time + progress +
auto TOC on journal (#141/142), project pull-quotes (#146), print stylesheet
(#148), quote-of-the-day empty states (#158).
Security/ops: Leaflet SRI (#177), upload size validation (#184), honeytoken
soft-ban (#185), autoload-size readout (#188), opt-in clean-uninstall (#193).
Dev/CI: structured-data lint job (#109), auto-release-on-tag workflow (#196),
bin/nr-bump.sh (#197, used to cut this), ESLint/Prettier/Stylelint/EditorConfig
(#199). Locale date helper nr_i18n_date() (#175).
Docs (owner actions): docs/OPS-NOTES.md — Brotli (#86), Cloudflare rules (#92),
login hardening (#183).

68/200 shipped.


## v4.44.0 — 2026-06-10
IDEAS-200 Batch 2+3 (lightweight majority)

Two new modules (inc/seo-extra.php, inc/conversion-extra.php) + settings, CSS, JS.

SEO/distribution/schema (Batch 3): ImageObject/VideoObject + og:video (#96/97/102),
Person @id + WebPage author/dates + speakable (#104/116/117), canonical
consolidation (#105), en-AT hreflang (#120), JSON Feed /feed/json (#101), image
sitemap /sitemap-images.xml (#98), WebSub ping (#100), token <title> templates
(#114), robots.txt sitemaps + opt-in AI-crawler block (#119), ⌘K search-term
capture (#110), IndexNow bulk re-ping (#113), generalised LCP preload (#90).

Conversion (Batch 2): visitor shortlist tray (#41), exit-intent (#44), trust line +
availability on Enquire (#47/49), pre-filled brief (#46), referral capture (#68),
[nr_packages] + [nr_press] shortcodes (#69/71), seasonal banner (#73), share button
+ WhatsApp ref (#77/74). All visitor-facing features opt-in / shortcode-driven.

Integration-heavy and asset/owner-bound items (payments-adjacent flows, AVIF,
variable font, build tooling, redirect UI, CWV/funnel endpoints, etc.) are held
for the next pass — see CHANGELOG. Marked 27 items in docs/IDEAS-200.md.


## v4.43.0 — 2026-06-10
Batch 1 part 2 (lightweight motion) + v4.42 fix + Batch 6 reorg

Fix: data-nr-motion is now set on <body> (not <html>), so the v4.42
compound selectors .nr-cinematic[data-nr-motion=...] match — the tilt,
chromatic-aberration, skew and divider styles were inert before.

Lightweight effects (same opt-in gate as part 1): viewfinder bracket on
press (#23), aspect-matched loading skeleton (#37), elastic rail overscroll
(#15), scroll-snap rails (#16), contextual 'drag' cursor (#28), directional
draw-on link underlines (#27), wordmark reveal on load (#25), drift-grain (#35).

Docs: docs/IDEAS-200.md reorganised — heavyweight items across all batches
(every L, GPU/WebGL effects, commerce/payment/portal subsystems, offline/edge
infra, large content/i18n/security systems) collected into Batch 6 (build last);
IDs kept stable.


## v4.42.0 — 2026-06-10
IDEAS-200 Batch 1 part 1: cinematic motion layer (opt-in)

Behind one master toggle (nr_fx_cinematic, default off) + a per-visitor
calm/standard/cinematic switch (#40). Effects gate on the motion level and
honor prefers-reduced-motion, so the live look only changes when both opt in.

Shipped: 3D card tilt + glare (#24), chromatic-aberration card hover via an
injected SVG filter (#7), scroll-velocity image shear on rails (#12),
decode/scramble reveal on mono eyebrow labels (#22), draw-in dividers (#36),
split-flap stat counters (#29), film-frame scroll counter (#18).

Marked the 8 items in docs/IDEAS-200.md; CHANGELOG carries a step-by-step
test log.


## v4.41.0 — 2026-06-10
fix date picker + IDEAS-NEXT batch (25/28)

Date picker: replace the native calendar glyph with a white SVG so it's
reliably visible on the dark enquire form (the v4.38 color-scheme:dark +
invert(1) combination cancelled to black).

IDEAS-NEXT (all visitor-facing features opt-in, default off):
- Conversion: recently-viewed strip, footer newsletter capture (nr_subscriber
  CPT + optional Brevo), rotating testimonials band, next-open-dates line.
- SEO: aggregateRating/reviews, IndexNow ping + virtual key file, branded feed
  + /journal/feed, Speculation Rules, OG article:* audit.
- Editorial: project process section, client-logos strip, series cover pages.
- A11y: prefers-contrast + Save-Data/reduced-data, palette/sheet focus trap.
- Ops: settings JSON import/export, content-health dashboard widget, Studio
  Assistant role.
- Quality scaffolding: Playwright smoke test + Lighthouse CI behind a manual
  workflow_dispatch (no PR gate; needs a live runtime).

Deferred with rationale: lookbook PDF, scroll-video hero, per-plate diptych.


## v4.40.0 — 2026-06-10
theme professionalism (audit quick-wins 1–5)

- screenshot.png: branded 1200×900 preview (GD, theme fonts). Replace with a
  real hero shot anytime.
- load_theme_textdomain('raveenthiran') + languages/ scaffold (raveenthiran.pot
  + README) so the theme is translatable without code changes.
- Block-editor styles: add_theme_support editor-styles/dark-editor-style +
  assets/css/editor-style.css (dark canvas, Inter Tight/JetBrains Mono, amber).
- Minimal theme.json (v2): Obscura palette + font families + content/wide sizes
  for the block editor.
- readme.txt + CHANGELOG.md.
- docs/IDEAS-NEXT.md: quick-wins 1–5 marked ✅.


## v4.39.0 — 2026-06-10
final review batch (last items)

Built:
- #14 Opt-in footer marquee ticker (toggle + text in Theme Settings).
- #19 Handwritten-style signature on the Studio page (nr_signature setting).
- #22 .nr-divider amber hairline utility (+ marquee/footer hairlines).
- #39 Self-referencing hreflang (en + x-default) on every canonical.
- #40 [nr_faq] shortcode — accordion + FAQPage schema for journal/pages.

Verified (already satisfied): #25 WebP bulk resumes (skips existing twins),
#27 WebGL gated/paused + distortion binds cheaply, #49 NR_DISABLE_FEATURES.

Intentionally skipped with rationale (logged): #28 JS-split, #29 critical-CSS —
build-tooling refactors, high risk / low gain on HTTP/2 + Cloudflare + Brotli;
the async-CSS opt-in already addresses render-block.

docs/IMPROVEMENTS-50.md: all 50 addressed (48 ✅, 2 ⏭).


## v4.38.0 — 2026-06-10
white calendar icon + review batch 6

- Date field: white calendar picker icon on the dark UI (color-scheme:dark +
  inverted -webkit-calendar-picker-indicator) on enquire/contact forms.
- #10 index.php fallback rebuilt as the standard card rail (matches portfolio).
- #15 verified: single-project plate EXIF caption already reveals on hover.
- #35 rel=prev/next link tags on paginated archives / search.
- #46 "Reset all settings to defaults" button (confirm + success notice).

docs/IMPROVEMENTS-50.md updated. 38/50 done.


## v4.37.1 — 2026-06-10
fix hero layout broken by the ghost numeral (#13)

Batch 2 added `.nr-hero__center{position:relative}`, which overrode the
existing `.nr-hero__center{position:absolute;bottom:48px}` and made the giant
title flow from the top and overlap the slider. Removed the override — the
already-absolute center is its own containing block for the ghost number.


## v4.37.0 — 2026-06-10
review batch 5 (perf + onboarding)

- #24 First card in every rail loads eager + fetchpriority=high (LCP on
  archive/taxonomy/search); rest stay lazy. nr_image_or_placeholder gained an
  $eager arg.
- #31 Leaflet map now loads lazily via IntersectionObserver when scrolled near —
  map-less pages no longer pay the ~40KB. Footer loader injects CSS/JS on demand.
- #32 Cache-Control: public, max-age=3600 on /projects.json and the sitemap.
- #38 Verified: sitemap lastmod uses post_modified (projects + journal).
- #50 Dismissible post-activation onboarding admin notice (pages, permalinks,
  settings, WebP/health) via after_switch_theme.

docs/IMPROVEMENTS-50.md updated (batch 5 ✅). 34/50 done.


## v4.36.0 — 2026-06-10
review batch 4 (admin UX & robustness)

New inc/admin-extras.php:
- #41 Journal list columns: category + a featured-image presence check.
- #45 "Theme health" dashboard widget — permalinks, SMTP, GD WebP, Site Icon,
  Turnstile, and required pages (enquire/about/legal) at a glance.
- #47 Enquiry CSV export (UTF-8 BOM, all attribution fields) wired into the
  insights widget.
- #43 Light settings normalisation — UID uppercased/space-stripped; URL fields
  get an https:// scheme if missing.
- #44 (importer.php) Duplicate guard: reuse an existing attachment when an
  image's MD5 already exists, so re-runs don't create copies.

docs/IMPROVEMENTS-50.md updated (batch 4 ✅ + log). 29/50 done.


## v4.35.0 — 2026-06-10
review batch 3 (journal OG, archive SEO, ⌘K, cookie)

- #23 OG share cards now serve nr_journal too (endpoint + seo.php og:image),
  eyebrow uses journal category + date.
- #34 Meta-description fallback for taxonomy/term pages and the project/journal
  archives (term description → sensible default).
- #36 Journal entries added to /projects.json and surfaced in the ⌘K command
  palette (labelled "Journal").
- #48 Cookie notice no longer rendered server-side once an nr_consent cookie is
  present (no markup/JS for returning deciders).
- #26 verified: new templates emit width/height via WP image functions.
- docs/IMPROVEMENTS-50.md updated (batch 3 ✅ + log).


## v4.34.0 — 2026-06-10
review batch 2 (catalogue polish + journal depth + SEO)

- #13 Hero ghost numeral: oversized outlined slide number behind the title,
  synced to the slider (JS updates [data-hero-ghost]).
- #17 Filter chips show counts (category / tag / journal-category).
- #18 Journal prose: drop-cap on the first paragraph + styled pull-quotes.
- #20/#37 "More notes" strip at the end of a journal post — related entries by
  shared category (recent fallback) + All entries link.
- #21 confirmed: all .nr-card already share the hover-frame.
- #33 Breadcrumb schema extended to single journal, journal archive, and every
  taxonomy archive (series / tag / project-cat / journal-cat).
- docs/IMPROVEMENTS-50.md updated (batch 2 ✅ + log).


## v4.33.0 — 2026-06-10
review batch 1 (journal/catalogue fixes + polish)

A — bugs/gaps:
- Journal rail no longer capped at 10 (pre_get_posts → 60; project taxonomies → 48).
- Fixed footer overlap at the end of the single journal article (padding-bottom).
- Journal entries + /journal added to the sitemap.
- Article schema (headline/date/author/image) on single journal posts.
- New taxonomy.php: series / tag / project-cat / journal-cat archives as a
  card rail (work cards or journal index cards) with term intro.
- New search.php: mixed projects/journal/pages results as a card rail.
- Stronger bottom shade on journal cards so the excerpt stays AA on bright photos.

Design:
- Plate numbers on work cards (PL—07, vertical mono).
- Journal cards restyled as index cards (amber top-rule, big mono date, subdued image).

Docs: docs/IMPROVEMENTS-50.md — full 50-item backlog + update log.


## v4.32.0 — 2026-06-05
journal redesigned to match portfolio (no-scroll)

- Journal archive: horizontal card rail (.nr-portfolio-rail + .nr-card) inside
  a fixed .nr-fullscreen layout, so it gets the same cards, scroll-snap, and the
  desktop prev/next arrows (theme.js targets .nr-portfolio-rail) as the portfolio.
  Added category chips (nr_journal_cat) + a card excerpt.
- Single journal post: rebuilt as a fixed two-pane .nr-jpost (image + scrollable
  article column), mirroring the single-project / about layout, so it fits the
  no-scroll model instead of a cut-off scrolling article.
- Removed the journal no-scroll opt-out and the old list/article CSS; added the
  rail-card + fixed single-post styles (mobile falls back to stacked/scroll).


## v4.31.1 — 2026-06-05
fix legal pages showing no body text

The designed fallback was in the else of if(have_posts()), which is always true
for a real page, so an empty page rendered only the title. Now the fallback
shows when the page CONTENT is empty (trim(get_the_content())==='') — and the
owner's own content still overrides it.


## v4.31.0 — 2026-06-05
built-out legal pages (Impressum, Datenschutz, AGB)

- Designed .nr-static / .nr-legal styling: scrollable long-form reading layout
  in the Obscura look (eyebrow, lede, section hierarchy, amber accents, mobile
  flow). Previously .nr-static had no CSS.
- Full, Austria-correct fallback content pulling from settings:
  · Impressum — § 5 ECG / § 14 UGB / § 25 MedienG, UID, Gewerbe/WKO, EU-ODR.
  · Datenschutz — DSGVO sections reflecting the ACTUAL processing: easyname
    hosting + logs, Cloudflare CDN (US/SCC), consent cookies, enquiry form
    (Art 6 1b/f), Google Workspace mail, Turnstile, self-hosted fonts, rights +
    Datenschutzbehörde complaint.
  · AGB — photography terms: estimate+30% deposit, usage rights, copyright +
    credit, cancellation/force majeure, liability, Austrian law / Vienna venue.
- New settings: nr_legal_name, nr_uid, nr_gewerbe (Studio section).
- Owner pastes own page content to override; otherwise the designed fallback shows.


## v4.30.1 — 2026-06-04
bulk "Generate WebP" tool (Tools → Generate WebP)

On-demand WebP is capped per request, so galleries got partial <source>
srcsets (only the first couple of sizes). New AJAX-batched admin page bakes a
.webp twin for every jpg/png sub-size of every image attachment (incl. AVIF
originals' JPEG sub-sizes), skipping ones already done — so every <picture>
source becomes complete. Then purge cache.


## v4.30.0 — 2026-06-04
theme-side WebP delivery (no plugin)

Fixes the remaining image bytes: AVIF originals + a server that can't write
AVIF sub-sizes meant WordPress served heavy JPEG sub-sizes.

- inc/webp.php: makes a .webp twin for every jpg/png sub-size (at upload via
  wp_generate_attachment_metadata, and on-demand for existing media, capped 8
  per request) and centrally wraps every wp_get_attachment_image /
  get_the_post_thumbnail output in <picture><source type=image/webp>. Cache-
  safe, real fallback, zero template changes (picture{display:contents}).
- front-page hero preload made WebP-aware (type=image/webp) so it matches the
  rendered <picture> and doesn't double-download the JPEG.
- Verified: GD imagewebp produces a valid, smaller twin.


## v4.29.1 — 2026-06-04
a11y contrast + hero sizes

- Fix Lighthouse contrast failures: mobile tab labels ink-3 -> ink-2 (~6.2:1),
  cookie-notice text ink-2 -> ink (full bone).
- Hero sizes/imagesizes: "100vw" -> "(max-width:900px) 96vw, 46vw" so the
  browser stops over-fetching a full-viewport candidate for the letterboxed
  (object-fit:contain) desktop hero.


## v4.29.0 — 2026-06-04
cut render-blocking CSS (inline fonts + opt-in async stylesheet)

PageSpeed showed ~2,070ms of render-blocking CSS as the mobile LCP driver
(images were already fine — AVIF 400-900KB).

- Inline fonts.css (~2.5KB @font-face) into <head> with absolute font URLs,
  dropping one render-blocking request. Falls back to external enqueue if the
  file can't be read. Font preloads unchanged.
- New opt-in nr_perf_async_css (Theme Settings, default off): loads theme.css
  via preload+onload (non-render-blocking) with a <noscript> fallback — the
  full render-block win, gated because it can flash unstyled content.


## v4.28.0 — 2026-06-04
enquiry attribution + insights dashboard (measurement loop)

A self-hosted measurement loop (no third-party analytics, privacy-clean):
- Capture which project/source drove each enquiry: hidden nr_ref / nr_service
  fields + JS-filled external referrer on the Enquire form; saved as
  _nr_ref / _nr_service / _nr_referrer on the nr_enquiry entry.
- New inc/insights.php "Enquiry insights" dashboard widget: counts over
  7/30/90 days + all time, top projects driving enquiries, breakdown by shoot
  type, and top external sources — answering "which work converts".


## v4.27.1 — 2026-06-04
PDF estimate polish (currency + filename)

Spotted in a Port25 auth report (mail now fully SPF+DKIM aligned):
- Render € / ä-ö-ü / accents correctly by encoding text to Windows-1252 for
  the standard Helvetica/WinAnsi font, instead of stripping them (was "E850").
- Clean attachment filename "Estimate-<Studio>.pdf" via get_temp_dir +
  wp_unique_filename (was "nr-estimate-XXXX.tmp.pdf").


## v4.27.0 — 2026-06-04
built-in SMTP (Google Workspace) for reliable mail

- New inc/smtp.php: routes wp_mail() through an authenticated SMTP server via
  phpmailer_init. Theme Settings → § Mail (SMTP): enable, host, port,
  encryption, username (real mailbox), masked App Password (or NR_SMTP_PASS
  constant), From (verified send-as alias) + From name. Sets envelope Sender
  for SPF alignment; wp_mail_from/from_name kept in sync.
- Password stored separately (not kses-filtered), preserved when the field is
  left blank; nr_smtp_enable added to the toggle-persist list.
- "Send a test email to me" button in settings to verify config.
- SETUP.md: step-by-step Google Workspace setup (App Password, Send-mail-as
  alias, Cloudflare SPF/DKIM/DMARC).


## v4.26.0 — 2026-06-02
remove rail skew, add visible prev/next arrows

- Removed the momentum "skew" on horizontal rails — plates no longer tilt
  while scrolling (this was the slanted look on the portfolio + project rails).
- Added desktop prev/next arrow buttons on .nr-portfolio-rail and
  .nr-project__rail-track so a mouse can navigate without wheel/swipe; arrows
  auto-hide when the rail doesn't overflow and dim at each end. Touch keeps
  swipe; keyboard ←/→ still works.


## v4.25.3 — 2026-06-02
fix footer overlap on the Studio (About) page

The Studio page is a fixed two-column layout with its own portrait caption
pinned bottom-left; the global fixed .nr-footer collided with it. Hidden the
global footer on body.nr-page-about.


## v4.25.2 — 2026-06-02
hide the footer "Start a project" CTA on the Enquire page

nr_footer_cta() links to /enquire, so on the Enquire page it was a redundant
self-link overlapping the slider. Now suppressed on that template/page.


## v4.25.1 — 2026-06-02
remove the "Book a time" link from the Enquire form

The Enquire page is the single funnel; it no longer surfaces an outbound
booking-URL button (WhatsApp + Send enquiry remain).


## v4.25.0 — 2026-06-02
remaining polish features, all opt-in (default off)

Every new effect is gated by a Theme Settings toggle so it can't change the
live site until switched on, and each degrades cleanly.

- #83 Card hover distortion: SVG feDisplacementMap ramped on hover (desktop,
  motion-on). Toggle nr_fx_distort.
- #54 Line-reveal headings: flattens .nr-display into per-line clip masks that
  rise on view; restores original markup on any error. Toggle nr_fx_lines;
  also excluded from the simple rise reveal when on.
- #58 Interface sound: WebAudio ticks + a mute control that starts muted and
  only plays after opt-in. Toggle nr_fx_sound.
- #59 Generative favicon: runtime canvas monogram in the accent. Toggle
  nr_fx_favicon.
- #80 Auto internal linking: DOM-based the_content filter (never touches
  headings/existing links/attributes), longest-title-first, capped. Toggle
  nr_fx_interlink. Verified against sample content.
- #62 Map archive: [nr_map] shortcode + per-project "Location (map)" meta box
  (lat/lng), Leaflet/OSM (CARTO dark tiles) loaded only where the shortcode
  is used.
- #82 Morph choreography: scale/fade root cross-dissolve layered on the #55
  view-transition.
- Docs: SETUP.md + IDEAS-100 (#54/#58/#59/#62/#80/#82/#83 ✅).


## v4.24.0 — 2026-06-02
OG cards (#69), PDF estimate (#70), series (#63), view-transition morph (#55)

The full "decision needed" set, each with a clean shared-host fallback.

- #69 Dynamic OG share cards: composited 1200×630 card per project at the
  virtual /nr-og/<id>.jpg (GD; photo cover-fit + gradient + title/cat·year +
  wordmark), cached to uploads, cache-busted on edit. seo.php points
  og:image at it for single projects. Falls back to the raw featured image
  if GD/TTF is missing. Bundles inter-tight-500/700.ttf (converted from the
  theme's existing woff2) for GD text.
- #70 Quote → PDF estimate: dependency-free single-page PDF writer (standard
  Helvetica, no embedding) — a branded non-binding estimate attached to the
  enquirer auto-reply when the brief carried a calculated price. xref/object
  table validated.
- #63 Series/collections: nr_project_series taxonomy (+ /series archive),
  a "Series" meta row and a "More from this series" nav on the project page.
- #55 Shared-element morph: opt-in (nr_fx_viewtrans, default off) cross-doc
  View Transitions; matching view-transition-name on the portfolio card and
  the project's first plate. Browsers without support navigate normally.
- Docs: SETUP.md usage + IDEAS-100 (#55/#63/#69/#70 ✅).


## v4.23.0 — 2026-06-02
keyword tags + multi-filter (#64), video plates (#66)

- #64 Keyword tags: new flat `nr_project_tag` taxonomy (admin column, REST).
  Portfolio archive gains a second chip row that MULTI-selects and ANDs with
  the single-select category/year row. Filter JS rewritten to a state model
  (main filter + tag set); cards carry `tag-<slug>` tokens in data-cats.
- #66 Video plates: a video attachment dropped into a project Gallery renders
  inline as a muted/loop/playsinline autoplay <video> (with poster from the
  attachment's featured image + a "Motion" badge). Gallery field already
  accepts all mime types.
- #29 confirmed already shipped (random project suggestions on 404) — marked.
- Docs: SETUP.md (tags + video usage), IDEAS-100 (#29/#64/#66 ✅).


## v4.22.0 — 2026-06-02
WebGL hero transitions (#81), opt-in

The Awwwards lever, built as safe progressive enhancement.

- New assets/js/webgl-hero.js: raw WebGL (no deps) overlay on .nr-hero__frame
  that dissolves between slides with a displacement + chromatic "melt" shader
  and procedural noise (no extra image requests). Matches the native plate
  look: contain framing + the same contrast/saturate/brightness grade.
- Driven by a new `nr:hero` CustomEvent fired from theme.js go(), so it stays
  in sync with thumbnails, auto-advance, swipe and keyboard nav.
- Off by default behind Theme Settings → § Visual effects → "WebGL hero
  transitions" (nr_fx_webgl); enqueued only on the front page when enabled.
- Never degrades the hero: bails on reduced-motion, ≤900px, no-WebGL, or any
  shader/link/texture failure (incl. tainted canvas) — leaving the native CSS
  crossfade. Plates stay visible (native LCP) until slide 0 is uploaded and
  drawn, then we swap to the canvas seamlessly. DPR capped at 2; rAF only
  during transitions.
- Docs: SETUP.md + IDEAS-100 (#81 marked done).


## v4.21.0 — 2026-06-01
PWA install/offline (#46), Turnstile spam shield (#74), before/after slider (#67)

- #46 PWA: dynamic web-app manifest + service worker served from site root
  (intercepted on template_redirect, no rewrite flush). Cache-first for theme
  assets, network-first navigations with offline shell fallback. Installable
  via Site Icon; apple-touch-icon + theme-color in head.
- #74 Cloudflare Turnstile: Site/Secret keys under Theme Settings → § Security;
  widget on the Enquire form; server-side siteverify in nr_handle_contact_send
  (fails open if unconfigured or on network error; honeypot + rate-limit kept).
- #67 [nr_compare] shortcode: before/after drag slider (IDs or URLs, labels,
  start %); interaction + styling folded into existing theme.js / theme.css.
- Fix: literal backslash-quotes on the Enquire WhatsApp button markup.
- Docs: SETUP.md setup steps, IDEAS-100 marked #46/#67/#74 done.


## v4.20.0 — 2026-06-01
bulk project importer (#41), replaces Immich/Media-Picker

Projects → Import: upload a .zip where each top-level folder is a project.
Folder name -> title, first image -> featured cover, all images -> gallery
(filename order), year auto-filled from EXIF (via the add_attachment hook).
Sideloads straight into the WP media library so the Immich + Media Picker
plugin can be dropped. Admin-only, nonce-protected, cleans up its temp dir,
skips already-existing titles, handles a single wrapper folder, raises
time/memory limits, and reports projects/images/skipped. inc/importer.php.
Bump to v4.20.0.


## v4.19.1 — 2026-06-01
revert CSS inlining (it worsened FCP)

Full-inline (v4.19.0) eliminated the render-block but bloated the HTML and
pushed mobile FCP 2.9s -> 4.4s (score 71 -> 65). Reverted to external,
Cloudflare-compressed stylesheets (the better-scoring v4.18.1 behavior).
The remaining mobile ceiling is the hero image (LCP ~6s / 'improve image
delivery'), which is content-side (regenerate thumbnails / smaller hero /
Cloudflare Polish), not CSS.


## v4.19.0 — 2026-06-01
inline CSS to kill mobile render-blocking

Mobile PageSpeed: 'render-blocking requests, est. 1,520 ms' = the external
theme/fonts stylesheets sit in the critical path; over Slow-4G the round-trip
to fetch them blocks first paint even when Cloudflare-compressed.

Fix: inline fonts.css (@font-face, absolute URLs) + theme.css directly into
<head> via nr_inline_css() and drop the external enqueues. First paint now needs
zero CSS requests — eliminates the render-block with NO FOUC (full styles
present at paint). Falls back to an external <link> if files are unreadable.
Cloudflare Brotli compresses the inline CSS in the HTML (~12 KB on the wire).
Bump to v4.19.0.


## v4.18.1 — 2026-06-01
drop the empty render-blocking style.css enqueue

style.css holds only theme metadata (no rules — all CSS is in theme.css), yet
it was enqueued on the front end as a render-blocking request. Removed it: one
fewer blocking round-trip on mobile. The real fix for the 1,520 ms render-block
is server gzip (htaccess-snippet.txt) / Cloudflare.


## v4.18.0 — 2026-06-01
cleanup pass + bundled setup/perf docs

- #10 Dead-CSS strip: removed the now-unused Contact/Booking page styles, the
  booking-modal intro/host rules, nr-quick-form, contact__intro, and their
  mobile @media overrides (~4.8 KB). Braces verified balanced; live rules
  (.nr-form, .nr-steps, .nr-modal__panel, .nr-quote, journal) all intact.
- #7/#5: bundled htaccess-snippet.txt (gzip + far-future cache) — the safe way
  to get the transfer-size win; in-place JS/CSS minify intentionally skipped to
  keep the source maintainable (gzip does more, zero risk).
- Added SETUP.md (page/template + content + permalinks checklist) to the theme.
- Bump to v4.18.0.


## v4.17.1 — 2026-06-01
drop redundant 'Enquire' nav item (keep the CTA button)

The amber 'Book a shoot' button already points to Enquire, so 'Enquire' in the
primary nav + sidebar was a duplicate. Removed it: desktop nav is now
Showcase · Work · Studio · Journal, with the button as the single CTA. Mobile
keeps Enquire in the bottom tab bar.


## v4.17.0 — 2026-06-01
medium batch in one release (Journal, year filter, more)

- #60 Journal/blog: nr_journal CPT + nr_journal_cat taxonomy, scrollable
  archive-nr_journal.php + single-nr_journal.php, added to primary nav + sidebar
  (journal pages opt out of the no-scroll model via body.nr-page-journal).
- #61 Timeline/year filter: year chips on the portfolio archive (token-based,
  reuses the existing filter — no JS change) + cards tagged year-YYYY.
- #72 Testimonials rotation on the About 'Words' block (measured, fades).
- #73 Related-project hover-preview thumbnails on the single project page.
- #75 Consent-gated analytics: tracking script now only loads after the cookie
  notice is accepted (server-readable nr_consent cookie).
- inc/medium.php; new templates; one-time rewrite flush covers /journal.
- Bump to v4.17.0.


## v4.16.0 — 2026-06-01
settings UI + drag-reorder + bulk feature + wiring

- Theme Settings: new fields for WhatsApp (#25), Instagram grid (#26), footer
  CTA label (#27), press-kit URL (#68); booking URL (#71) already existed —
  all now editable + wired (enquire Book-a-time + WhatsApp, footer press-kit,
  Studio IG grid, footer CTA).
- #42 drag-to-reorder projects in admin (jQuery UI sortable -> menu_order ajax).
- #44 bulk feature/unfeature action on the Projects list.
- Hamburger hidden on mobile — the bottom tab bar already covers primary nav.
- Bump to v4.16.0.


## v4.15.0 — 2026-06-01
Tier 2 (safe subset); rest moved to Tier 3

Built: #51 command palette (⌘K), #52 contact-sheet index + #65 search (both fed
by /projects.json), #56 hero pointer parallax, #43 EXIF→project_year on upload,
#45 admin dashboard widget, #76 login-attempt limiter, #78 Review schema from
testimonials (no fabricated ratings), #79 ImageGallery schema. New inc/tier2.php.
Per request, the difficult / settings-field Tier 2 items (#41-42,44,46-50,53-55,
57-64,66-75,77,80) are moved to Tier 3. Bump to v4.15.0.


## v4.14.0 — 2026-06-01
Tier 1 batch (#1-4,6,8-9,11-40; #5/#7/#10 moved to Tier 3)

inc/tier1.php + wiring across templates:
- Perf: #1 LQIP blur-up on every image, #2 prefetch project on hover, #3 LCP
  preload on About+Enquire, #4/#8 img dims/decoding audit, #6 content-visibility
  on long lists. (#9: all bundled font weights are in use — no-op.)
- SEO: #11 related projects, #12 meta description, #13 ImageObject caption,
  #14 /projects.json feed, #15 og:image:alt, #16 sitemap lastmod, #17 auto
  alt-text, #18 full sameAs, #19 paged noindex, #20 verification (existing).
- Conversion: #21 auto-reply email, #22 enquiries saved as CPT + admin list,
  #23 honeypot + rate-limit, #24 click-to-copy email + toast, #25 WhatsApp,
  #26 curated Instagram grid, #27 footer CTA, #28 auto availability text.
- UX/A11y/admin: #29 404 suggestions, #30 LQIP skeleton, #31 tab-away title,
  #32 maps link, #33/#35 keyboard rails, #34 active-nav, #36 focus-visible,
  #37 alt via #17, #38 caption scrim, #39 reduced-motion, #40 admin columns.
- One-time rewrite flush on version change (projects.json/sitemap).
- Tier 3 now also holds #5 minify, #7 .htaccess cache, #10 dead-CSS strip.
Bump to v4.14.0.


## v4.13.1 — 2026-06-01
drop dead Instagram API scaffolding

Meta deprecated the Instagram Basic Display API (Dec 2024), so the
instagram_access_token / show_feed / post_count ACF fields could never work —
removed them. Updated IDEAS-100 #26 to a curated grid / per-post embeds (no API).
Bump to v4.13.1.


## v4.13.0 — 2026-06-01
remove the light/dark toggle (keep view transitions)

Per request: removed the light/dark theme toggle entirely — the top-bar and
mobile-sidebar buttons, the toggle JS, the early <html> class script, and the
html.nr-light palette + toggle CSS I added in v4.12.0. Cross-document view
transitions (#2) are kept. Bilingual (#14) and client proofing (#7) are out of
scope per request. Bump to v4.13.0.


## v4.12.0 — 2026-06-01
Obscura v4.12.0 (fix) — add light/dark toggle to the mobile sidebar too


## v4.11.0 — 2026-06-01
kinetic headings + cinematic intro (library-free)

- #5 Kinetic type: display headings (.nr-display) and page-head text now rise
  in on view (reuses the safe IntersectionObserver reveal); hero title words
  rise + stagger on load and on each slide change.
- #3 Cinematic intro: preloader wordmark + counter now reveal in sequence.
- All reduced-motion-safe; no libraries (PageSpeed unaffected).
- Bump to v4.11.0.


## v4.10.0 — 2026-06-01
testimonials on About + recognition ghost-row fix

- #8 Testimonials: new 'Words' section on the About page renders the
  nr_testimonial CPT (title = source, content = quote), with a sample fallback
  + admin hint when none exist yet. (Clients marquee #9 and awards/press
  recognition already existed on About.)
- Fix: nr_recognition_list() now skips blank/punctuation-only lines, so an empty
  Awards (or Press) list hides cleanly instead of rendering a stray dash.
- Bump to v4.10.0.


## v4.9.0 — 2026-06-01
Enquire left column is now full-bleed image

Removed the block under the Enquire image slider (the 01/02/03 process steps +
email/studio facts) — it duplicated info already on the form/FAQ and left an
awkward gap. The latest-6 rotating image now fills the entire left column.
Cleaned the now-unused vars (steps/email/loc/avail) and dead CSS
(.nr-enquire__aside-body / __facts). Bump to v4.9.0.


## v4.8.0 — 2026-06-01
remove lightbox; Enquire aside = latest-6 crossfade

- Removed the plate lightbox entirely (plates already display full-size):
  overlay markup, the lightbox JS module + its swipe/keyboard handlers, the
  plate <button> wrappers (plates are now plain figures), all data-lightbox-*
  attributes, and the now-dead CSS. EXIF 'shot on' relocated from the lightbox
  caption to a subtle on-hover caption on each plate (.nr-plate-cap).
- Enquire left panel now shows the latest 6 projects as an image-only crossfade
  (hero-style, no titles/meta) instead of one static image; auto-rotates,
  reduced-motion-safe.
- Tidied stale 'lightbox' comments.
- Bump to v4.8.0.


## v4.7.0 — 2026-06-01
small-batch features (#6, #17, #15)

- #6 Project -> Enquire pre-fill: 'Commission similar' now passes the project
  title (ref=) and pre-fills the enquiry message, in addition to the type chip.
- #17 EXIF 'shot on' caption: project plates expose camera/focal/aperture/
  shutter/ISO (captured on upload) as a line in the lightbox caption. Auto-shows
  only when EXIF exists; hidden otherwise.
- #15 Per-project SEO: VisualArtwork schema now emits richer ImageObject
  entries (contentUrl + caption + creator) instead of bare image URLs.

Bump to v4.7.0.


## v4.6.0 — 2026-06-01
Tier 3 rail momentum-skew + 20 feature ideas doc

- Tier 3 (safe, library-free): horizontal rails (portfolio + project plates)
  lean into fast swipes/scrolls and settle — a GPU-transform momentum effect,
  reduced-motion-safe. (Full raw-WebGL transitions intentionally deferred: they
  need on-device tuning and shouldn't ship to a live site unverified.)
- docs/FEATURE-IDEAS.md: 20 prioritised features/ideas (Awwwards / conversion /
  SEO-perf tiers) with effort + rationale.
- Bump to v4.6.0.


## v4.5.0 — 2026-06-01
Tier 2 motion (library-free) + tab-bar nav consistency

Tier 2 motion (no GSAP/Lenis — keeps the PageSpeed wins intact):
- Intro preloader: 0->100 counter + amber progress bar, wordmark; shows once
  per session, skipped under prefers-reduced-motion.
- Magnetic buttons + smooth lerp-follow custom cursor (was snap-to-pointer).
- Scroll reveals: cards / FAQ items / process steps rise in on view via
  IntersectionObserver (progressive enhancement — visible if JS/animation off).

Fix: mobile bottom tab bar showed a stale 'Contact' label after the Booking+
Contact->Enquire merge. Tab 3/4 now read new option keys (nr_tab_studio_label /
nr_tab_enquire_label) so old saved labels can't resurface; nav is consistent
across top bar, sidebar, and tab bar.

Bump to v4.5.0.


## v4.4.0 — 2026-06-01
unified mobile layout, swipe, mobile LCP preload

Mobile (<=900px): one unified scroll model. The desktop fixed single-screen
layout switches to a normal scrolling document so every template (hero, project,
about, contact, booking, enquire, confirmed, archive) reflows identically and
nothing is clipped. Sticky top bar; body padding clears the fixed tab bar;
removed the one-off enquire mobile block (folded into the unified layer).

Swipeable: hero slider now responds to touch swipe (left/right -> prev/next);
the plate lightbox swipes between images; horizontal rails (project plates,
portfolio cards, hero thumbs) get momentum + scroll-snap + overscroll-contain.

Mobile performance: preload the LCP hero image with a responsive imagesrcset +
fetchpriority=high in <head> — the main lever on mobile Largest Contentful Paint.

Bump to v4.4.0.


## v4.3.0 — 2026-06-01
Tier 1 performance

- Self-hosted fonts: bundled Inter Tight 300/400/500/600/700 + JetBrains Mono
  400/500 (latin subset woff2 in assets/fonts/), new assets/css/fonts.css.
  Removed the render-blocking Google Fonts <link> and gstatic preconnects;
  preload the two above-the-fold weights for LCP.
- Responsive images: front-page hero and single-project plates now emit
  srcset+sizes via wp_get_attachment_image (was a single full-res src) — phones
  no longer download 2400px images. Graceful <img> fallback when no attachment id.
- Image sitemap: sitemap.xml now declares the image namespace and lists each
  project's featured + gallery images (<image:image>). Static URLs updated to
  /enquire (booking/contact/faq merged).
- Bump to v4.3.0.


## v4.2.1 — 2026-06-01
redesign the price-check calculator popover

The popover was inheriting the old booking-modal's two-column grid panel,
which split the heading beside the form, overlapped the close button, and
overflowed. Reworked it into a single-column dialog:
- header (fixed) · scrollable body · sticky footer with the live total + actions
- max-width 520px, capped height, proper close-button clearance
- refined type cards, custom add-on checkboxes, tidy travel row
Markup restructured (head / body / foot); calculator JS unchanged.


## v4.2.0 — 2026-06-01
merge FAQ into the Enquire page

- FAQ now renders as a collapsed accordion inside page-enquire.php (no more
  standalone page showing six open questions at once).
- New nr_faq_items() helper (inc/quote.php) holds the Q&A (ACF faq_items
  override -> defaults); shared by the page and the FAQPage schema.
- inc/seo.php: FAQPage JSON-LD now emits on the Enquire page via nr_faq_items().
- Removed page-faq.php; /faq (and the FAQ template) now 301-redirect to /enquire.
- Compact FAQ styles; bump to v4.2.0.


## v4.1.0 — 2026-06-01
merge Booking + Contact into one Enquire page + interactive calculator

- New page-enquire.php: splitscreen (atmosphere left, unified inquiry form right).
  Replaces the redundant Booking + Contact pages.
- New inc/quote.php: registers the orphaned nr-site-settings ACF options page,
  nr_quote_data() (ACF -> option -> defaults), and 301-redirects legacy
  /booking and /contact to /enquire once the Enquire page exists.
- New parts/quote-popover.php: interactive price-check calculator (shooting
  type + add-ons + license + travel -> live estimate), opens from any
  [data-modal=nr-quote] trigger; 'Use this in my enquiry' prefills the form.
- theme.js: calculator logic + form-chip state.
- Nav merged everywhere: top bar, primary nav, mobile tabs (Book/Hello ->
  Studio/Enquire), FAQ CTA, and single-project 'Commission similar' (now deep-
  links ?service=). Removed the orphaned booking modal + page-booking.php +
  page-contact.php.
- Contact form handler now records project type, preferred date, and estimate.
- Bump to v4.1.0; swap build artifact to v4.1.0 zip.


## v4.0.0 — 2026-06-01
Add installable build: raveenthiran-obscura v4.0.0 zip (gate-1 staging artifact)
