# Changelog — Raveenthiran

The version + build date shown in the site footer matches the top entry here.
Frontend version = `package.json`; WordPress theme version = `style.css` header.
Both are bumped together on each meaningful update.

## 3.2.1 — 2026-08-21 · Fix: site reverting to old version (service worker)

- The PWA service worker (added in 2.6.0) was caching the app shell and serving
  stale builds, so the live site kept "reverting" to an old version. Root cause:
  `sw.js` was covered by the immutable `*.js` cache rule, pinning the old worker
  in browsers for up to a year.
- **Fix**: `sw.js` is now a kill-switch that deletes all caches, unregisters the
  worker and reloads open tabs; `ui.js` also proactively unregisters any worker
  and clears caches; `.htaccess` serves `sw.js` as `no-cache` so it can never be
  pinned again. Net effect: no service worker controls the site — the browser
  always fetches the freshest files. (Frontend-only; no theme re-upload.)

## 3.2.0 — 2026-08-21 · Site Control admin + polish

- **Site Control** — a single branded WordPress admin hub (top-level menu) to run
  the whole site instead of hunting the default dashboard: live stats (projects,
  enquiries, journal, theme version, active home layout), quick-launch cards to
  every area, a "how changes go live" panel, and a setup checklist. **Site
  settings** now nest under it as a sub-page, and **Enquiries** move under it too.
- **Fix** — the `[rvn_compare]` before/after slider lost its styling in the v3
  redesign; restored its CSS and rebuilt the drag/keyboard behaviour so it works
  again in journal/project content.
- **Polish** — type-checked (0 errors/0 warnings), removed a dead variable, and
  marked the JSON-LD / speculation-rules data scripts `is:inline`.
- Frontend + WordPress theme/zip at 3.2.0.

## 3.1.0 — 2026-08-21 · Home layouts + selector, batch polish

- **Five selectable home layouts**, chosen in WordPress (Site settings → Home →
  Layout) — all share the same content:
  - **Raveenthiran** — split hero (default)
  - **Monument** — giant wordmark + one full-width hero + serif tagline
  - **Index** — archive-first: serif statement + horizontal rail + full index
  - **Editorial** — offset portraits under a centred serif headline + issue TOC
  - **Cinema** — full-screen Ken-Burns crossfade slider (kicker + serif title)
  - A system serif carries the editorial accents (no extra webfont, GDPR-safe).
- **RAW / FINAL** removed from the home page (per request).
- **Batch polish** (from the plan's signature tier, the safe/high-value parts):
  FAQ rich-results schema (FAQPage JSON-LD) on Enquire, Speculation-Rules
  hover-prefetch for instant navigation, tabular numerals on counters/prices,
  a print stylesheet (clean catalogue sheets), and HSTS.
- WordPress theme + zip at 3.1.0; ACF gains the Home → Layout select.

## 3.0.0 — 2026-08-21 · "Poster Brutalism" redesign

A ground-up frontend redesign — the approved Claude Design direction, built into
the live Astro site. This consolidates the redesign batches (foundation → polish).

- **Design system** — new `theme.css`: monochrome palette (no gold), self-hosted
  **Anton + Archivo** (GDPR), 1px hairlines, sharp corners, **dark-only**. Images
  sit duotone at rest and bloom to colour on hover. Old Playfair/Montserrat/Roboto
  fonts and the light/dark toggle removed.
- **Motion engine** (`fx.js`, vanilla, no deps) — preloader (home, once/session),
  intro stagger, scroll reveals, parallax, scroll-velocity marquee skew, custom
  cursor (fine-pointer only), letter-stagger headings, click-zoom lightbox,
  hover-peek previews, drag-to-scroll rails, live Vienna clock, film-grain +
  vignette atmosphere, scroll-progress hairline, cookie notice. All motion is
  suppressed under `prefers-reduced-motion`.
- **Home** — split hero (mix-blend headline over a `featured_home` slide stack
  with clip-path wipes + counter), kinetic marquee, drag "Selected work" rail,
  **The Index** (all projects, hover-invert + floating peek), **RAW / FINAL**
  drag compare, **Recognition** (awards + rotating testimonials), giant CTA band.
- **Work** — huge "THE WORK" head, instant client-side category filter chips
  (dynamic from WordPress), 3-col masonry, duotone → colour, click-zoom.
- **Project** — split hero (stacked Anton title + parallax plate), drag gallery
  rail, statement + credits, giant "Next project" band.
- **Studio / Enquire / Series / Journal / 404 / Offline** — all restyled in the
  new language. Enquire keeps the live calculator + real backend (honeypot +
  Turnstile + PDF auto-reply) with a graceful mail fallback.
- **New** — Impressum / Datenschutz / AGB legal pages (Austrian ECG + GDPR
  scaffolds; owner fills the specifics), new footer (ghost stretched wordmark,
  GPS, social, legal), header with numbered nav + live clock + mobile overlay.
- **PWA** — service-worker cache busted to `rvn-v3`, new "R" icon, dark manifest.
- The offline/first-run SAMPLE set now uses the photographer's real frames, so
  the site looks finished even when the WordPress API is unreachable at build.
- **Fully WordPress-editable** (Site settings): a new **Home** tab drives the
  hero headline + sub-copy, the marquee text, the Recognition awards and the
  testimonials; Studio gains an editable statement + spec table; the Enquire
  calculator reads its **Sessions** from WordPress; Behance/LinkedIn added to
  Contact. Every field falls back to the built-in defaults, so nothing is blank
  before it's filled in. WordPress theme + `raveenthiran-headless.zip` at 3.0.0.
- **Cursor**: the native cursor stays visible everywhere; an elegant trailing
  ring accent follows it on fine-pointer devices (no more hidden cursor).

## 2.2.0 — 2026-08-21 · Turnstile spam shield

- Cloudflare **Turnstile** on the enquiry form (adapted from Obscura): a
  no-puzzle, privacy-friendly CAPTCHA. Configure the keys under **Site
  settings → Security**; the secret can also live in wp-config.php
  (RVN_TURNSTILE_SECRET). When unset, the honeypot still applies — nothing
  breaks without setup.

## 2.1.1 — 2026-08-21 · Enquiry form fix (frontend only)

- The enquiry form's `action` now targets the HTTPS enquiry endpoint
  instead of `mailto:`, so Chrome no longer shows "This form is not secure.
  Autofill has been turned off." Autofill works again, and the form now also
  submits without JavaScript (native POST → the same endpoint). JS behaviour
  and the mailto fallback are unchanged. (No theme re-upload needed.)

## 2.1.0 — 2026-08-21 · SMTP mail

- **SMTP delivery** for enquiry emails (adapted from the Obscura theme).
  Configure under **Site settings → Mail (SMTP)** — host, port, TLS/SSL,
  username, password (or the `RVN_SMTP_PASS` constant in wp-config.php),
  from address + name. When off, the server's default PHP mail() is used.
  This makes the enquiry confirmation + studio notification actually arrive.

## 2.0.0 — 2026-08-21 · "Master" launch

The complete, production-ready site.

- **Design** — bespoke editorial theme adapted from Opta (CocoBasic): Playfair
  Display + Montserrat + Roboto, gold accent, light/dark, self-hosted fonts.
- **Home** — cinematic full-screen hero (Ken Burns, slide counter, prev/next,
  scroll cue) + editorial lede + staggered "Selected work" gallery.
- **Work** — album filter + grid/index toggle; **Project** — contained cover
  print, label/value meta, credits, gallery with click-zoom lightbox, and a
  "Next project" link.
- **Journal** — editorial blog from native WordPress posts (+ RSS feed).
- **Studio** — bio, portrait, count-up stats, clients, script flourish.
- **Enquire** — live price calculator + FAQ + real enquiry backend (stored in
  WordPress, emails studio + client, mailto fallback).
- **Identity** — "Raveenthiran" wordmark (header, footer, preloader), serif "R"
  favicon; cinematic overlay menu; gold scroll-progress bar.
- **Languages** — EN/DE interface switch.
- **SEO / perf** — canonical, Open Graph + Twitter, JSON-LD, sitemap, RSS,
  LCP preload, CLS-safe images, web manifest.
- **Hardening** — `.htaccess` (404, gzip, caching, security headers, HTTPS),
  full keyboard accessibility (focus traps, aria-live).
- **Docs** — `frontend/README.md` handbook.

<!-- Add the next release above this line, newest first. -->
