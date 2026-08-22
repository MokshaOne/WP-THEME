# Changelog — Raveenthiran

The version + build date shown in the site footer matches the top entry here.
Frontend version = `package.json`; WordPress theme version = `style.css` header.
Both are bumped together on each meaningful update.

## 4.0.2 — 2026-08-22 · One voice across every page

- **Navigation is now three items: Work · About · Enquiry** (Series leaves the
  menu; series pages remain reachable from project credits). DE toggle follows.
- **Work and About now speak the home's grammar**: mono accent-index kicker
  (`01 — THE ARCHIVE`), colossal title with the accent full stop, mono frame
  count — the same opener DNA as the homepage and the calculator. Frontend-only.

## 4.0.1 — 2026-08-22 · Usage: TFP zeroes the balance, commercial pays for it

- **Usage now has three modes.** TFP — a collaborative shoot: the ledger shows
  the full breakdown, then a "TFP collaboration" line (in the accent) negates
  it and the balance reads **0 €**, with a note that TFP is offered at the
  photographer's discretion. Private — unchanged. **Commercial** — the licence
  plus a new **commercial surcharge** (default 250 €, editable in Site
  settings → Price calculator) — e.g. 150 + 250 = 400 € on top.
- All three paths verified interactively (534 / 934 / 0 € on the same
  configuration). Theme + zip at 4.0.1 (new `commercial_extra` field).

## 4.0.0 — 2026-08-22 · The flagship rebuild — dark, epic, one voice

The package rebuilt around the chosen dark "Poster" direction. One signature
site, no layout modes; a backend cut to what actually runs it.

- **Home** — an epic sequence: the full-bleed poster hero with the giant
  wordmark → kinetic marquee → **Three Frames** (one photograph owns the
  viewport under a huge outlined `001`, then an asymmetric `002/003` pair) →
  a colossal **statement wall** (No presets. No shortcuts. Just the frame.) →
  The Index → Recognition → CTA. EXIF-style mono captions throughout.
- **Work** — the filter is now a mono list (brutalist, not pill-y) with the
  accent underlining the active album; mono captions on the tiles.
- **Studio** — proof added: a stats wall in display type (projects, years,
  frames, base) and a "Seen at · worked with" names row, both editable in
  WordPress (Studio → stats / clients).
- **Enquiry calculator — the real pricing model.** No price list: session ×
  duration × **distance from Vienna** (round trip × per-km) + **add-ons** +
  **usage licence** (private/TFP vs commercial). Six steps, an itemized
  ledger, a sticky orientation panel, and the full breakdown lands in both
  emails. The backend handler now also captures hours, add-ons, distance and
  usage (and a legacy field-name mismatch that dropped date/location/message
  from enquiry mails is fixed).
- **Backend reduced to essentials** (theme 4.0.0): the home-layout selector,
  the modular-home repeater, the Sections/blocks system and the redundant
  "Project types" field are gone — 46 fields remain, each driving a live
  feature. No stored WordPress data is deleted; removed fields simply no
  longer appear.
- Sourced-parts note: Envato was consulted for reference only (licensed
  generic templates — rejected per the design law); the 21st component server
  was unavailable this session. Everything shipped is hand-built.

## 3.12.0 — 2026-08-22 · "Poster" dark direction (Option B) — full site

- The chosen redesign direction from the design canvas, applied site-wide:
  - **Truer black + paper** palette (`#0B0B0B` / `#F4F2ED`); one **signal accent**
    `#FF3B1D` used only for state (active nav, hovers, links, the section index).
  - **Space Mono** on every technical label — nav, captions, section kickers,
    index/credits meta, footer — for an EXIF / instrument feel (self-hosted, GDPR).
  - **Poster hero** on the home: a full-bleed darkened frame with a giant Anton
    `RAVEENTHIRAN` wordmark (mix-blend), an EXIF-style caption, the section index
    in the accent, and the slideshow chrome — keeps the auto-advance + nav.
- Built on the v3.11 editorial layout (gutters, airy rhythm, de-boxed sections).
- Frontend-only; WordPress theme unchanged (no re-upload needed). This is a
  preview of the direction — easy to tune (accent amount, wordmark scale) or
  revert.

## 3.11.0 — 2026-08-22 · Editorial restructure (structure, not skin)

- Same design language (Anton · Archivo, mono, hairlines) — a rebuilt **layout
  system** so the site reads editorial and expensive instead of template-flat:
  - **Content sits in a column with real margins**, not edge-to-edge: a single
    scaling gutter token (`--gx`, up to 132px) drives every section, so the page
    has asymmetric breathing room instead of a uniform full-width band.
  - **Section heads are a small kicker over a large heading**, left-aligned and
    held in a column — no more symmetric left/right bar.
  - **Airy, variable rhythm**: big scaling section spacing (`--sp`) with generous
    negative space; headings and rows scaled up.
  - **The boxed grids are gone.** The Press wall is wordmarks over a rule with
    open pull-quotes; Packages are columns split by a single hairline; the
    Availability calendar is a light single-line grid; the Testimonial is an open
    asymmetric split — no more table-like 1px cells.
- Applied across home, work, project, studio, enquire, journal and the blocks.
  Frontend-only (CSS) — WordPress theme unchanged (no re-upload needed).

## 3.10.0 — 2026-08-22 · Optional content blocks (backend-toggleable)

- **Four new sections you switch on from WordPress** (Site Control → Site
  settings → **Sections**), each with a **placement** dropdown — Home top/bottom,
  Enquire top/bottom, Studio bottom, or **Off**:
  - **Press wall** — an "As featured in" logo row + pull-quotes (repeater).
  - **Packages** — session tiers with what each includes and a price (repeater).
  - **Testimonial spotlight** — one large quote + portrait (falls back to the
    first Home testimonial when its own quote is blank).
  - **Availability** — a booking calendar. Source = **WordPress** renders a month
    from the booked date ranges you enter; **cal.m1o.at** links out to your
    external calendar instead. A "Request a date" button points at Enquire.
- Every block defaults to **Off**, so nothing changes until you place it. The
  frontend renders whatever is assigned to each slot — a block can go anywhere,
  and the same slot system extends to new blocks later.
- WordPress theme + `raveenthiran-headless.zip` at 3.10.0 (new ACF **Sections**
  tab + REST `blocks`); frontend at 3.10.0.

## 3.9.0 — 2026-08-21 · Cinematic transitions + distortion everywhere

- **Cinematic project→project transition**: navigating between pages now
  dissolves with a brief scale + blur, and where a project cover is shared it
  morphs across on a softer curve — moving between projects reads as one
  continuous image. Pure CSS View-Transitions; fully off under reduced motion.
- **Hover distortion on every rail**: the WebGL ripple/RGB-split effect now also
  covers the home "Selected work" rail and project galleries, not just the grid.
- **Safe cross-origin textures**: textures load from the already-decoded image
  same-origin, or via a CORS fetch cross-origin — the visible `<img>` never
  carries a crossorigin attribute, so it can never break; the effect just
  activates once CORS is present.
- **Docs**: `docs/nas-cors.md` — copy-paste Apache/nginx/Cloudflare snippets to
  enable CORS on the NAS media so the effect lights up on the live images.
- Frontend-only; WordPress theme unchanged (no re-upload needed).

## 3.8.0 — 2026-08-21 · WebGL hover distortion + signature polish

- **WebGL hover distortion (Awwwards tier)**: hovering a Work/Series grid image
  now ripples it toward the cursor with a subtle RGB split, rendered on a shared
  WebGL2 canvas exactly over the real photo. Pure progressive enhancement — on
  any failure (no WebGL2, a cross-origin/tainted texture, weak hardware via a
  frame-time watchdog) it silently does nothing and the plain image shows; off
  under reduced motion / touch. (For the effect on the live cross-origin images,
  WordPress/NAS must send CORS headers for the media; otherwise it safely no-ops.)
- **Shared-element morph**: clicking a grid card now morphs its image into the
  project cover (View Transitions), on both Work and Series.
- **Structured data**: Journal posts emit BlogPosting JSON-LD; Journal and
  project pages emit a BreadcrumbList — richer results and sitelinks.
- **Accessibility**: a keyboard focus ring on the enquiry inputs (previously
  `outline:none`); global `:focus-visible` already covers nav/buttons/toggle.
- Frontend-only; WordPress theme unchanged (no re-upload needed).

## 3.7.0 — 2026-08-21 · Image delivery, SEO, EN/DE, magnetic cursor

- **Responsive images (Core Web Vitals)**: featured images on projects and
  journal posts now build a `srcset` from WordPress's generated sizes, and
  every content image carries `srcset` + `sizes` + intrinsic `width`/`height`
  (no layout shift). AVIF is served through as-is. LCP heroes get
  `fetchpriority=high`. (For multiple widths the WordPress media library must
  generate intermediate sizes for the upload — otherwise a single size is used,
  which still works.)
- **Original crop on the project hero**: no more force-cropping — the full
  photograph is shown, matted by the panel, so a portrait keeps its head.
- **Per-project SEO**: unique meta/OG descriptions (from the statement, or
  composed from the project's own data), a `CreativeWork` + `ImageObject`
  JSON-LD block per project (creator, credit, copyright, location, date), and a
  new `/image-sitemap.xml` (Google image extension) referenced from robots.txt.
- **EN/DE interface toggle**: a header switch swaps the site chrome
  (navigation, buttons, CTAs, labels) between English and German; WordPress
  content is untouched. Remembered per visitor, re-applied on every navigation,
  and paired with `hreflang` (en/de/x-default) + `og:locale` for SEO.
- **Magnetic cursor + bespoke easing**: the cursor ring snaps to and wraps the
  hovered control, buttons drift toward the pointer and spring back on a
  signature ease-out curve. Off under reduced-motion / touch; native cursor
  stays visible.
- Frontend-only; WordPress theme unchanged (no re-upload needed).

## 3.6.1 — 2026-08-21 · Softer film grain

- The full-page film-grain atmosphere was too heavy (`opacity .5` with an
  overlay blend, made more visible by the animated WebGL layer). Dialed both
  the static SVG grain and the WebGL grain down to `opacity .18`, so it now
  reads as a subtle texture instead of visible noise over the photographs.
  Vignette and the reduced-motion / touch opt-outs are unchanged.

## 3.6.0 — 2026-08-21 · Quick wins — share images, sticky headings, CI guard

- **Open Graph share images everywhere**: every page now emits a real, absolute
  `og:image` / `twitter:image` — a page's own photo where it has one, otherwise
  the first featured photograph — so links to any page (Work, Studio, Enquire,
  legal, 404…) unfurl with a proper large image card instead of a bare summary.
  Adds `og:image:alt`; JSON-LD uses the same resolved image.
- **Sticky section headings**: on the home page, the **SELECTED WORK** and **THE
  INDEX** titles pin just under the header while their section scrolls past — an
  editorial touch, pure CSS, no motion (unaffected by reduced-motion).
- **Visual-check CI**: a new GitHub Actions workflow builds the site, type-checks
  it, then loads every key route (desktop + mobile) in a real headless Chromium.
  It **fails the check on any uncaught JS error or missing page content** — the
  exact class of bug that used to make the site look broken — and uploads
  full-page screenshots as an artifact for a visual diff on each pull request.
  `@astrojs/check` + TypeScript are now dev-dependencies so the type-check runs.
- Frontend-only; WordPress theme unchanged (no re-upload needed).

## 3.5.0 — 2026-08-21 · WP Admin redesign — Site Control as the home

- **Windows-8 / Metro tile board**: the WordPress **Dashboard now redirects to
  Site Control**, shown as a grid of flat colour tiles (Projects, Enquiries,
  Journal with live counts; Homepage, Site settings, Media, Mail, Security; and
  Appearance/Plugins/Users/Tools/WP Settings/ACF) — everything reachable from
  one screen, plus a big **Publish now** tile.
- **Icon-only sidebar**: the admin menu is collapsed to icons by default (native
  fly-out submenus on hover), for a calm, uncluttered admin.
- **Site Control hub**: Projects, Journal, Media and Enquiries are grouped as
  its sub-menu; the toolbar keeps the **▲ Publish** shortcut on every screen.
- Backend-only; version bumped to 3.5.0 for parity.

## 3.4.0 — 2026-08-21 · One-click publishing from WordPress

- **Publish changes now** button in **Site Control**: triggers the GitHub
  Actions deploy from inside WordPress, so edits go live on demand without
  waiting for the nightly rebuild and without touching GitHub.
- The GitHub token is entered in **Site settings → Publishing** (a new field —
  no `wp-config.php` editing) and is used **server-side only**; it never reaches
  the browser. `wp-config.php`'s `RVN_GH_TOKEN` still overrides it if preferred.
  Repo / branch / workflow are configurable, with the right defaults pre-filled.
- Backend-only change; version bumped to 3.4.0 for parity.

## 3.3.0 — 2026-08-21 · Batches 11 / 12 / 14 / 16 (signature tier)

- **Batch 11 — motion physics**: inertial smooth scroll (dependency-free
  mini-Lenis; fine-pointer only, off under reduced-motion/touch) and marquee
  speed that eases with scroll velocity. (Keeps the existing View-Transitions
  cover-morph, letter reveals and parallax.)
- **Batch 12 — WebGL layer**: an animated GPU film-grain on a shared WebGL2
  canvas, replacing the static SVG grain when supported. Feature-gated with a
  frame-time watchdog that auto-disables and restores the SVG grain on weak
  hardware; fully off under reduced-motion.
- **Batch 14 (partial) — content ops**: a per-project **focal-point picker**
  (Projects → Focal point X/Y %) that drives `object-position` on the project
  hero and home slides, so the subject stays framed across every crop.
- **Batch 16 (partial) — engineering**: a ready-but-**disabled** Content-
  Security-Policy in `.htaccess` (commented, with enable/verify instructions —
  a wrong CSP can blank the site, so it never auto-applies) alongside the live
  HSTS; plus an accessibility pass (decorative marquee + section numerals
  hidden from screen readers).
- **Fix**: a latent temporal-dead-zone bug in the cursor loop (`peekOn`) that
  could throw and disable the whole motion engine.
- Deferred (need your accounts/assets): OG-image generator, Sentry/monitoring,
  image CDN, visual-regression CI, and the content items (real case studies,
  client logos, b-roll video, self-portrait).
- Frontend + WordPress theme/zip at 3.3.0.

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
