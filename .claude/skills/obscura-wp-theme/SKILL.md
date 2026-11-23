---
name: obscura-wp-theme
description: >-
  Build, extend, optimize, and ship the "Obscura" bespoke WordPress portfolio
  theme (Raveenthiran.com/raveenthiran-obscura) and similar hand-built classic
  PHP themes in this repo. Use when adding theme features, fixing template/CSS/JS
  bugs, doing PageSpeed/Lighthouse performance work, wiring email (SMTP/SPF/DKIM/
  DMARC), WebP/image delivery, accessibility, or cutting a new installable theme
  ZIP release. Encodes the conventions, the ship workflow, and the performance &
  deliverability playbooks proven on this project.
---

# Obscura WordPress theme — build & ship skill

A classic (non-block) PHP theme for a Vienna photographer's portfolio, hosted on
**easyname shared hosting** behind **free Cloudflare**. Plugin-averse: prefer
self-hosted, theme-built solutions over plugins. Ships as an installable ZIP.

## Where things live
- Theme root: `Raveenthiran.com/raveenthiran-obscura/`
- Feature modules: `inc/*.php` (loaded via the `$includes` array in `functions.php`)
- Assets: `assets/css/theme.css`, `assets/css/fonts.css`, `assets/js/theme.js`,
  `assets/fonts/*.woff2` (+ `*.ttf` for GD text), `assets/js/webgl-hero.js`
- Docs: `docs/IDEAS-200.md` (the live backlog — 200 items in 5 batches, tick ✅ with
  version), `docs/ROADMAP-ARCHIVE.md` (completed history), `docs/OBSCURA-PLAYBOOK.md`
  (method), `SETUP.md` (owner setup checklist)
- Release ZIPs: repo **root**, named `raveenthiran-obscura-vX.Y.Z.zip`
- Work branch: `claude/obscura-rebuild` → **PR #7** (draft). Repo `mokshaone/wp-theme`.

## Core conventions
- **CPTs:** `nr_project` (tax `nr_project_cat`, `nr_project_tag`, `nr_project_series`),
  `nr_journal`, `nr_testimonial`, private `nr_enquiry` (auto-logged on form submit).
- **Settings:** every option is a `wp_options` row read by `nr_opt('nr_*', $default)`.
  Defaults + whitelist live in `nr_settings_defaults()` (inc/theme-settings.php).
  Toggles render with `nr_field_toggle`; **add new toggle keys to `$toggle_keys`**
  (or name them `nr_fx_*`) so "off" persists. Body classes (`nr-has-*`) gate JS/CSS.
- **ACF optional:** `inc/acf-polyfill.php` stubs ACF; read fields via `nr_field()`.
- **Design tokens:** injected inline in `header.php` `:root` (--bg #0B0C10, --ink
  #F2EFE9 bone, --amber #F2A03D, Inter Tight + JetBrains Mono, self-hosted woff2).
- **Layout:** desktop = fixed single-screen (`.nr-fullscreen` position:fixed);
  mobile (≤900px) = scrolling, bottom tab bar. Hero = `.nr-hero__frame` plates.
- **Module pattern:** new feature → new `inc/<name>.php` guarded by
  `if ( ! defined( 'ABSPATH' ) ) exit;`, appended to the `$includes` array.

## The golden rule: risky features are OPT-IN, default OFF
Anything that can change the live look or break (WebGL hero, View-Transition morph,
SVG hover-distortion, line-reveal headings, interface sound, generative favicon,
non-blocking/async CSS) ships behind a **Theme Settings toggle defaulting to `'0'`**,
with graceful fallback (reduced-motion, no-WebGL, no-JS, ≤900px). The owner flips
it on, previews live, and keeps it only if they like it. This avoided every
regression on a live site.

## Ship workflow (do this for EVERY change)
1. Edit. Match surrounding code style (tabs, naming, comment density).
2. **Lint:** `php -l` every changed/created `*.php`; `node --check assets/js/*.js`;
   for CSS verify brace balance (`grep -c '{' == grep -c '}'`).
3. For anything testable without WP (PDF bytes, WebP output, DOM transforms),
   **write a tiny stub harness** and actually run it (`define('ABSPATH',...)` +
   stub the few WP funcs). Verify structure, don't assume.
4. **Bump version** in `functions.php` (`NR_THEME_VERSION`) and `style.css`
   (`Version:`). Patch for fixes, minor for features.
5. **Build ZIP** at repo root:
   `cd Raveenthiran.com && zip -rq ../raveenthiran-obscura-vX.Y.Z.zip raveenthiran-obscura -x '*.DS_Store' -x '*/.git/*' -x '*.zip'`
6. `git add -A`, `git rm --cached` the old ZIP, commit (concise body + the
   session footer line), `git push -u origin claude/obscura-rebuild` (retry on
   network error with backoff).
7. **Deliver the ZIP** to the user with `SendUserFile` + a short "what changed /
   how to test" note.
8. Mark the idea ✅ in `docs/IDEAS-200.md` (with the version); update `SETUP.md` if owner action is needed.

## Performance playbook (measure first, then in this order)
Diagnose with a fresh **pagespeed.web.dev** mobile run. Read the **LCP breakdown**
(`TTFB + load delay + load time + render delay`). Don't guess from stale numbers.
1. **Render-blocking CSS** (was the #1 mobile lever, ~2,070 ms):
   - Inline the tiny `fonts.css` (@font-face only) into `<head>` with **absolute**
     font URLs (rewrite `../fonts/`). Safe, no FOUC.
   - Big `theme.css`: offer a **`nr_perf_async_css` opt-in** (preload+onload +
     `<noscript>`). **Do NOT inline the whole theme.css** — that was tried (v4.19)
     and bloated HTML, worsening FCP. Lesson logged.
3. **Third-party JS:** remove Google Tag Manager / `gpt.js` / Site Kit (176 KiB
   unused). Owner-side (plugin/snippet). Suggest Cloudflare Web Analytics instead.
4. **Images / WebP** (`inc/webp.php`): owner uploads AVIF originals but the server
   writes **JPEG sub-sizes** → heavy. Theme makes a `.webp` twin per jpg/png
   sub-size (at upload + on-demand capped 8/req + a **Tools → Generate WebP** bulk
   AJAX page) and wraps `wp_get_attachment_image()` output centrally in
   `<picture><source type=image/webp>`. Cache-safe (`picture{display:contents}`),
   covers cards/plates/hero (get_the_post_thumbnail routes through the same filter).
   Make the hero **preload** WebP-aware (`type=image/webp`) to avoid double-download.
   Fix oversized `sizes` (`100vw` → `(max-width:900px) 96vw, 46vw`) for the
   `object-fit:contain` hero.
5. **TTFB** on shared hosting: a page cache (W3TC **Disk: Enhanced**, or LiteSpeed
   Cache if the host is LiteSpeed). Keep **Minify OFF** (breaks JS/CSS; Brotli
   handles size). Exclude `enquire`, `nr-sw.js`, `nr-manifest.json`, `nr-og/.*`.
- After any image/cache change: **purge W3TC + Cloudflare**, reload twice incognito,
  re-test. Confirm with DevTools → Network (file name + type + size), not assumptions.
- **Stop at ~90 mobile / ~95+ desktop.** The rest (minify ~8 KiB, unused CSS,
  retina/DPR "oversize") is diminishing returns — say so honestly.

## Email deliverability playbook (Google Workspace)
- `inc/smtp.php`: `phpmailer_init` SMTP. Settings: host `smtp.gmail.com`, port 587
  STARTTLS, **Username = the real login mailbox** (e.g. `hq@m1o.at`, NOT the alias),
  **App Password** (or `NR_SMTP_PASS` constant), **From = a verified "Send mail as"
  alias** (e.g. `office@raveenthiran.com`). Password kept out of the kses bag
  (`pre_update_option` preserve-if-blank). Includes a "send test email" button.
- DNS (in **Cloudflare**): SPF `v=spf1 include:_spf.google.com include:spf.easyname.com ~all`
  (exactly ONE spf record), **DKIM** generated in Admin Console → Apps → Gmail →
  Authenticate email → click **"Start authentication"** (else Google signs with the
  generic `gappssmtp.com` key and DMARC fails alignment), DMARC on `_dmarc`
  `v=DMARC1; p=none; rua=...`. Verify with **port25 `check-auth@verifier.port25.com`**
  (unlimited) or learndmarc.com — want `dkim=pass header.d=<your-domain>`.
- Common trap: duplicate DKIM/SPF TXT records → temperror/permerror. There must be
  exactly one of each.

## Self-built (no-plugin) capabilities already in the theme
Bulk project importer (ZIP, `inc/importer.php`), PWA/offline (`inc/pwa.php`,
virtual `/nr-sw.js` + `/nr-manifest.json` via `template_redirect`), dynamic OG
share cards (GD, `/nr-og/<id>.jpg`, `inc/og-cards.php`, needs a bundled TTF),
dependency-free PDF estimate writer (`inc/pdf.php`, standard Helvetica, encode
text to Windows-1252 for €/umlauts), Turnstile spam shield (`inc/security.php`),
Leaflet map (`inc/map.php`, `[nr_map]` + coords meta box), before/after
(`[nr_compare]`), enquiry attribution + insights dashboard (`inc/insights.php`),
keyword tags + multi-filter, series, video gallery plates.

## Gotchas learned
- Edit tool fails on tab/whitespace mismatch → Read first, or use a Python
  string-replace with exact `\t` indentation. Read-tool line numbers ≠ file indent.
- A WP-dependent PHP file `exit`s under `if(!defined('ABSPATH'))` in a bare harness
  — `define('ABSPATH','/tmp/')` and stub `add_filter`/`add_action` first.
- pypdf/cryptography is broken in the sandbox; validate PDFs with a tiny pure-Python
  xref parser instead.
- The owner's DNS is on **Cloudflare**, not easyname. Polish/Mirage/Image Resizing
  are **Cloudflare Pro (paid)** — don't recommend them; do format work in the theme.
- Keep the model identifier out of commits/PRs/code — chat only.
