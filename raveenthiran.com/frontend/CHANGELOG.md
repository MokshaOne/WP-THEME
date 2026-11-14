# Changelog — Raveenthiran

The version + build date shown in the site footer matches the top entry here.
Frontend version = `package.json`; WordPress theme version = `style.css` header.
Both are bumped together on each meaningful update.

## 5.4.0 — 2026-11-13 · Cleanup: About wired to WordPress, admin skin v5.3 (theme 5.4.0)

Theme upload needed (`raveenthiran-headless.zip`, 5.4.0).

- **About page reads WordPress again.** `Studio — bio` and `Studio — data
  sheet` (the old `spec` repeater) render on `/about/`; the previous fixed
  copy stays as the fallback while the fields are empty.
- **Dead content fields removed** from the REST payload, `src/lib/wp.ts` and
  the ACF group: `studio_lede`, `home_hero_lines`, `home_marquee`,
  `travel_open`, `contact_response`. `contact_location` stays (estimate PDF).
  Field labels/instructions now say where each field appears.
- **Dead frontend code removed.** `ui.js`: cinema hero, rail, chip filter and
  the unused hero hooks (counter, progress, prev/next); `theme.css`: ~25 unused
  rule groups (`.manifest`, `.whois`, `.chips`, `.rail`, `.peek`, …);
  `format.ts`: `softenAll`.
- **Admin skin follows the site.** Cream `#F7F5F0` / ink `#111010`, DM Sans +
  Instrument Serif Italic (self-hosted in `fonts/`); black instead of red
  buttons; `#8A1C1C` only for errors and to-dos. `admin-dark.css` is no
  longer loaded. Site Control cells link to the bio / hero sub-copy fields.
- **Theme backend.** Unreachable "Enquiry insights" dashboard widget and the
  front-end toolbar style removed (Site Control replaces the dashboard; the
  theme renders no front end). Estimate PDF rule is ink instead of gold.

## 5.3.0 — 2026-11-13 · Refine: cursor, grain, transitions, grid (theme unchanged, 4.8.0)

Frontend only — no theme upload needed. Polishes v5.2 without touching the
foundation; `src/lib/wp.ts`, `scripts/og.mjs`, routes and deploy unchanged.

- **Page transitions.** `<ClientRouter />` (Astro 5's name for
  `<ViewTransitions />`) now runs a plain 250 ms fade on every page
  (`transition:animate={fade({ duration: '250ms' })}` on `<html>`).
- **Cursor.** A 24px hairline ring (`#cursor`, `mix-blend-mode: exclusion`,
  60 % opacity) follows the mouse; scales 1.6× over links/buttons, 2.5× over
  work photographs. Fine pointers only — touch devices keep the native one.
- **Grain.** `body::after` carries an SVG `feTurbulence` noise
  (baseFrequency .65, 3 octaves) at 2.5 % opacity — inline data URI, no load.
- **Work grid.** Every tile is a fixed aspect-ratio box (4:3 / 3:4 / 4:3) on
  a warm `#E8E3DA` placeholder, so a missing or loading photograph never
  leaves an empty cream hole. Hover shows a veil (rgba(10,9,8,.4)) with the
  project name in Instrument Serif Italic, 24px.
- **Statement.** `clamp(52px, 11vw, 160px)`, one line per row with hairlines
  between; line 2 indented 8vw, line 3 1vw.
- **Recognition.** Real table rows: year left (DM Sans 200, 11px, 40 %), name
  right (300, 12px); hover dims the name to 60 % and darkens the row.
- **About · Seen at.** Two columns, one name per line, 12px, .3em tracking
  (one column on phones).
- **Navigation.** The current page is marked only by a 0.5px underline
  (offset 4px).

## 5.2.0 — 2026-11-13 · Vogue × Dazed (theme unchanged, 4.8.0)

Frontend only — no theme upload needed. A third design pass: every page is an
exhibition — photographs dominate, text only where it must be, the rest is
space. Data layer (`src/lib/wp.ts`), routes, `scripts/og.mjs` and the deploy
pipeline are unchanged.

- **Dual-tone.** The home hero is the one dark, photographic surface; every
  other surface is warm cream `#F7F5F0` with ink `#111010` (secondary text at
  60 %, hairlines at 8 %). Work, About and all inner pages are light.
- **Type.** DM Sans 200/300 (self-hosted, `@fontsource/dm-sans`) replaces Space
  Mono for all UI and body text: labels uppercase 10–12px, tracking .2–.3em;
  body 15–16px, line-height 1.8. Instrument Serif Italic only for display
  headlines. Space Mono + Anton are no longer loaded on the site (OG only).
- **Header.** Fixed, transparent, `mix-blend-mode: difference`: RAVEENTHIRAN
  left, WORK · STUDIO · ENQUIRE right — no button, no box, no underline, no
  clock. Hides on scroll-down, returns on scroll-up.
- **Hero.** 100vw × 100vh photograph, no overlay; a slow 3s cross-fade, quiet
  dots bottom-right (pause stays keyboard-reachable). Project name + category
  sit below the photograph on the cream ground.
- **Work.** Filter pills removed. Asymmetric spread without gaps: 65 % left →
  45 % right overlapping by −15vw → 70 % centred, repeating; same grid on the
  home page. Caption "001 — NAME · CATEGORY · YEAR" at 10px, 50 % opacity;
  "→" cursor and `scale(1.015)` on hover. `?series=` / `?cat=` deep links still
  narrow the grid.
- **Project page.** Italic serif title (~110px), one fact line "Category ·
  Location · Year", cover full width, gallery stacked vertically 8vw apart
  (rail/slider removed; lightbox kept).
- **About.** Portrait left half, full height; right: RAVEENTHIRAN, the
  headline "Portrait & Fashion Photographer, Vienna.", a hairline, a three-cell
  data sheet (Based in / Available for / Since) and a short statement. Three
  steps with "— 01 —" counters.
- **Home.** Reduced to hero → selected work → statement → recognition → CTA
  (manifest, road dates, peek index and photographer teaser removed).
- **Footer.** Copyright (+ quiet Impressum / Datenschutz links, legally required
  in AT) left, coordinates right, one hairline above.
- **Motion.** Only an opacity fade on load (0.6s ease-out) and a 20px rise on
  scroll. Removed: preloader, clip reveals, parallax, hover-peek, inertial
  scroll, drag rails, pulse.

## 5.1.0 — 2026-11-13 · Vienna Monochrome (theme unchanged, 4.8.0)

Frontend only — no theme upload needed. A second design pass that replaces the
generic "serif + sans + terracotta" look with a contact-sheet character. Data
layer (`src/lib/wp.ts`), routes, `scripts/og.mjs` and the deploy pipeline are
unchanged.

- **Type.** Space Mono (self-hosted, 400/700) now sets everything below display
  size: navigation, body, labels, metadata, buttons, forms and footer — labels
  in upper case, 11px, `.15em` tracking. Instrument Serif is reserved for one
  display headline per section (≥ 80px on desktop, max. 1–2 per page).
  Archivo is removed (import and `@fontsource-variable/archivo` dependency).
- **Colour.** Canvas `#0A0908`, ink `#E8E3DA`, grey steps as opacities of the
  ink. The terracotta accent is gone — no accent colour at all; the
  photographs are the only colour. Single exception: the estimate total in the
  Enquire calculator is pure `#FFFFFF`.
- **Home hero.** The photograph fills the viewport edge to edge; the only
  overlay is a light bottom fade (transparent → `rgba(10,9,8,.4)`). The only
  text is a mono label bottom-left: "001 — PROJECT" / "CATEGORY · VIENNA ·
  YEAR". Headline, lede, buttons, progress line and slide controls are gone
  (pause stays as a focus-only control for keyboard users). Header sits
  directly on the photograph.
- **Statement.** "NO PRESETS. / NO SHORTCUTS. / JUST THE FRAME." as flush-left
  Instrument Serif lines (≈ 128px desktop, 52px mobile) with hairlines between
  them and no label.
- **Work grid** (home + /work/). Asymmetric: first frame ~74% wide on the left,
  second 55% on the right, then varying widths and heights. Every frame has one
  mono caption "001 NAME — CATEGORY · YEAR"; hover is `scale(1.02)` only.
- **About.** Portrait fills the left half at full viewport height, bleeding to
  the left, top and bottom edges; the studio facts sit on the right as a
  label/value data sheet. Process steps use large mono counters "— 01 —".
- **Recognition.** A table: year (opacity .5) | name | type, hairlines between
  rows, no bullets.
- **Footer.** Coordinates "48.2082° N, 16.3738° E" lead the footer; the large
  decorative wordmark is removed.
- Square, mono, upper-case buttons, chips and controls instead of pills.
  Theme colour, manifest and icons updated to the new canvas.

## 5.0.0 — 2026-11-13 · Editorial (theme unchanged, 4.8.0)

Frontend only — no theme upload needed. Complete visual redesign; data layer
(`src/lib/wp.ts`), routes and deploy pipeline are unchanged.

- **Colour first.** Photographs are shown in full colour everywhere (no more
  default black-and-white). Warm dark palette: background `#121110`, warm
  ivory text `#ede8e0`, a quiet terracotta accent `#d9825b`.
- **Two typefaces.** Instrument Serif for display (roman + one italic gesture
  per headline) and Archivo variable for text, labels and UI. Anton/Space Mono
  are no longer loaded by the site (kept only for the OG-image script).
  Sentence case instead of all caps; all-caps CMS copy is softened on render.
- **Design system.** Tokens for colour, type scale, spacing, easing and
  duration in `theme.css`; reusable classes (`.label`, `.btn`, `.rbtn`,
  `.link`, `.tag`, `.media`) and new components `Header`, `Footer`,
  `PageIntro`, `SectionHead`, `ProjectCard`, `CtaBand`. Inline styles removed
  from all pages (only JS-toggled `display:none` remains).
- **Home.** Full-bleed colour hero with slow crossfade, progress line, working
  pause button, linked caption; arrow keys only act while the hero has focus;
  the slider stops off-screen and in hidden tabs.
- **Pages.** Work archive with pill filters and natural-ratio masonry; project
  page with breadcrumb, italic title line, fact row, gallery rail, statement
  + credits; About with sticky portrait; Enquire calculator in numbered
  blocks with a sticky orientation card; journal, legal, 404 and offline
  pages on the same intro/article system.
- **Fewer, better effects.** Removed grain/vignette, WebGL hero morph and
  grain, RGB hover distortion, velocity skew, text scramble, letter reveals,
  scroll marquee, film-strip progress bar and the cursor ring. Kept: a short
  preloader, soft intro stagger, scroll reveals, clip-path image reveals,
  hide-on-scroll header, lightbox, hover peek, drag rails, smooth wheel
  scroll. Everything respects `prefers-reduced-motion`.
- **Responsive.** Breakpoints at 1100 / 900 / 640 px, sticky elements released
  on small screens, 44 px touch targets.

## 4.9.0 — 2026-11-11 · From looking to asking (theme unchanged, 4.8.0)

Frontend only — no theme upload needed.

- **Booking bridge.** Every project ends with "Want frames like these?". The
  button opens the calculator on the matching session (`?session=portrait`)
  with the project attached as reference (`?ref=<slug>`). The reference shows
  as a removable chip and travels inside the enquiry message, so it reaches
  the stored enquiry and both mails without a backend change.
- **Enquiry draft.** Configuration and typed details survive a reload or a
  detour to a project page (this tab only, sessionStorage). Cleared on send.
- **Preferred date** opens the native date picker; no past dates.
- **Gallery rail controls.** Arrow buttons, a frame counter (03 / 05) and
  left/right keys; captions count frames instead of repeating the title.
- **Fixed:** project meta labels sat above the wrong values when a project
  has no client; cookie notice was squeezed and cut off on phones.
- **Small archive:** with one or two projects the Work grid uses the full
  width instead of leaving a third column empty.
- **Home:** the photographer block carries the name, focus/base/since and a
  real button; Recognition rows run the full width; the hero sub-copy is
  readable over bright frames.
- **Enquiry FAQ** gets a heading and lines up with the page gutter.
- **Contrast:** secondary greys raised so small labels pass 4.5:1.

## 4.8.0 — 2026-11-10 · The whole panel

The skin from Site Control now covers the rest of wp-admin. Frontend unchanged
apart from the footer version. **Needs the new theme zip uploaded in WP → Themes.**

- **Dark skin everywhere:** sidebar, toolbar, list tables, forms, buttons,
  notices, meta boxes, ACF fields (tabs, repeaters, switches), media, login.
  Same palette and type as the live site; no rounded corners.
- **How:** `assets/admin-dark.css` is generated — every colour in the stock
  admin + ACF stylesheets remapped onto the site palette
  (`tools/build-admin-dark.py`; re-run after a major WordPress/ACF update).
  `assets/admin.css` is the hand-written layer on top.
- **Leaner menu:** Site Control, Work, Journal (was "Posts"), Media first;
  Dashboard and Comments removed (Dashboard already redirected; the static
  site has no comments). Updates moved to Site Control → WordPress.
- **Sidebar shows its labels again** instead of being forced to icons-only.
- **Toolbar:** WordPress logo and comments bubble removed.
- **Login:** wordmark instead of the WordPress logo, same skin.
- Admin colour schemes are pinned to the default (the skin replaces them).
- **Not skinned on purpose:** the block editor. It keeps the stock look.

## 4.7.0 — 2026-11-10 · Site Control, rebuilt

The WordPress start screen (theme `raveenthiran-headless`) now speaks the live
site's language and says what to do next. Frontend unchanged apart from the
version in the footer. **Needs the new theme zip uploaded in WP → Themes.**

- **Publish knows what is waiting.** The theme keeps a change log (projects,
  journal posts, albums/services/series, Site settings) since the last
  publish. Headline: "2 changes to publish" with the list, or "Everything is
  live". The toolbar button shows the count. Deploy result (triggered / no
  token / failed with the HTTP code) is the headline, not a notice.
- **Work shows what is unfinished.** Each project as a frame with its real
  featured image (cropped at its focal point) and a data sheet: year/place,
  gallery count, homepage flag, SEO description. Missing pieces are flagged.
- **Enquiries on the start screen.** Total, last 30 days, estimated pipeline,
  split by session — the dashboard widget's numbers, which the redirect to
  Site Control had made unreachable.
- **Settings as eight cells**, one per tab, with status where it can be
  checked (contact email, SMTP, deploy token, Turnstile). Replaces the
  separate setup checklist; Turnstile is a new check.
- **Look:** Anton / Archivo / Space Mono self-hosted in the theme (OFL), dark
  canvas, one accent, no rounded corners. Rainbow tiles are gone.
- Code: screen moved to `inc/site-control.php` + `assets/site-control.css`.

## 4.6.3 — 2026-11-10 · Honest with little content

The live site holds two projects and no honors yet; this release makes that
state look intended and makes it impossible to ship placeholders.

- **Deploys refuse sample data.** `WP_REQUIRE_LIVE=1` in the deploy workflow:
  if WordPress does not answer, the build fails instead of uploading sample
  projects. The nightly run tolerates it (site keeps its current build).
- **No invented claims.** Stats, clients, logos, honors and testimonials show
  only what WordPress holds; Recognition disappears when both lists are empty,
  the journal shows an empty note instead of sample posts.
- **Sparse layouts.** Heading counts the frames it shows (one / two / three),
  a single second frame no longer leaves half a row empty; on phones the hero
  kicker no longer collides with the slide arrows and the EXIF chip no longer
  covers the frame title.
- **Enquiry on phones:** the running estimate sits directly above SEND.
- **Accessibility:** every page has exactly one `h1`, all form fields are
  labelled, the decorative hover preview has an empty alt.
- **Lighter:** self-portrait fallback is WebP (656 KB → 19 KB).
- **Dependencies:** `npm audit fix` — 13 advisories → 5 (the rest need Astro 7).

## 4.6.2 — 2026-08-27 · One folder per domain

The repository is reorganised: `raveenthiran.com/` now holds everything for
this site (the Astro frontend, the headless backend theme + zip, and the
legacy themes Still / R-E / Catalogue-Noir-child plus interim HTML and design
scratches). `viennaphotogroup.com/`, `m1o.at/` and `jrpoetry.com/` hold their
themes. Deploy + visual-check workflows follow the new frontend path; nothing
inside any theme changed. Smoke 14/14 from the new location.

## 4.6.1 — 2026-08-23 · Launch prep (theme 4.2.1)

- The ▲ Publish button's default deploy branch is now `Theme-folder` — the
  branch the site lives on after the launch merge. (A custom value in Site
  settings → Publishing still wins.) Theme-only; zip rebuilt.

## 4.6.0 — 2026-08-23 · Trust layer II — traveling strip + logo wall (theme 4.2.0)

The two backend-driven pieces from the award sweep. Theme 4.2.0 (new zip)
adds three Site-settings fields; the frontend renders them when filled.

- **ON THE ROAD strip** (diaurra's best idea, adapted): Site settings →
  Traveling holds date rows (`12.–14. September · Berlin`) and an
  "open for traveling" toggle. Home shows a mono strip between the marquee
  and Three Frames; the calculator's DISTANCE step lists the same dates with
  ASK ABOUT A SLOT. Deliberately NO fallback to sample dates: a live backend
  without the field (or with no rows) hides the strip entirely rather than
  showing fabricated availability.
- **Client logo wall** (Kremser): Site settings → Studio can now hold
  client logos (name + image). With logos, About renders a logo wall —
  white silhouettes on the dark canvas (`brightness(0) invert(1)`), hover
  restores full presence; without, the text row stays exactly as before.
  Nothing was removed — the text list remains the fallback.
- Theme 4.2.0: ACF fields + REST payload (`studio.client_logos`, `travel`),
  php -l clean. Frontend verified: strip + hint render from sample, About
  falls back to the text row when no logos exist. Smoke 14/14.

## 4.5.0 — 2026-08-23 · Trust layer I — distilled from an Awwwards-top-20 sweep

Eight peer/award sites analysed (three Vienna photographers, five award-tier
references, all supplied as saved copies). Frontend-only; the backend pieces
(traveling strip, logo wall) wait for the next theme update.

- **Quick entries on the enquiry page**: PRIVATE SESSION / BRANDS &
  COMMERCIAL preconfigure the instrument via `/enquire/?for=…` — brands
  selects the editorial session and commercial usage in one click (verified:
  620 € on the sample config).
- **THE PHOTOGRAPHER teaser on home** (06) — portrait, one line, ABOUT THE
  STUDIO →. Every Vienna peer shows the person; now the poster does too,
  without breaking its grammar.
- **Viewfinder overlay on the hero** — corner brackets + centre cross, the
  camera's own chrome over the frame. Stays up during the WebGL morph, like
  a HUD should.
- **Film-strip scroll progress** — the top progress line is now segmented
  like film frames.
- Smoke 14/14; no JS errors.

## 4.4.2 — 2026-08-23 · WHAT HAPPENS NEXT on the enquiry page

Nobody should submit into a void: a three-step strip (01 YOU SEND THE BRIEF ·
02 FIRM OFFER — 24H · 03 WE SHOOT) now sits between the form and the FAQ,
in the About page's process grammar. Idea adapted from how booking-flow
expectations are set on peer portfolio sites. Smoke 14/14.

## 4.4.1 — 2026-08-23 · The EN/DE toggle leaves

A label-only translator is a façade, and the owner called it: it translated
navigation and buttons but never the content. Removed entirely — the DE
button in the header, `i18n.js`, the `.lang` styles, and the `hreflang`
en/de/x-default alternates plus `og:locale:alternate` (one language, one
honest signal). If a real German version ever happens, it will be proper
`/de/` pages with translated content. Smoke 14/14.

## 4.4.0 — 2026-08-23 · Gallery lightbox, shareable filters, branded OG cards

Frontend + deploy workflow. Three improvements in one release:

- **The lightbox is a real gallery now.** Every image in the same rail/grid
  becomes a navigable set: ←/→ arrows, arrow keys, swipe, an `02 / 04`
  counter, neighbour preloading, per-image captions. Accessible from the
  ground up — `role="dialog"`, focus trapped between the controls, Escape
  closes, and focus returns to the trigger. Backdrop click still closes
  (swipes don't count as clicks).
- **Filter states are shareable URLs.** Category chips write `?cat=fashion`
  (series mode keeps `?series=…`) via `replaceState`, and the archive
  restores the filter on load — send a client a link to just their kind of
  work.
- **Branded OG share cards.** A new build step (`scripts/og.mjs`) renders a
  1200×630 card per project with the site's own grammar — graded photo,
  wordmark, Anton title with the accent full stop, mono EXIF footer — via
  headless Chromium in CI, into `/og/<slug>.jpg`; project pages point
  `og:image` there. Titles that overflow shrink until they fit.
- Verified interactively: counter/keys/trap/Escape on the lightbox, chip →
  URL → reload round-trip on the filters, 9/9 cards generated. Smoke 14/14.

## 4.3.0 — 2026-08-23 · Series are clickable — the archive filters to a series

Frontend-only. Building on 4.2.0 (series shown in-post):

- **Series names link to the archive filtered to that series.** The SERIES
  credit on a project and a new "VIEW THE WHOLE SERIES →" link open
  `/work/?series=<slug>` — the archive then shows only that series's frames,
  with an injected pressed chip (`SERIES — VIECC`) in the filter row and the
  frame count updated. Clicking any category chip returns to the normal
  archive; clicking the series chip re-applies the series.
- Verified interactively: deep link shows exactly the series's projects
  (02 frames), chip pressed, ALL restores the full archive. Smoke 14/14.

## 4.2.1 — 2026-08-23 · The gallery rail scrolls sideways again

Three separate causes, all fixed (frontend-only):

- **The inertial smooth scroll swallowed every wheel event** and turned it
  into page scroll — the project gallery rail never saw one. The rail now
  owns the gesture while it can still move: sideways trackpad deltas and the
  vertical wheel both drive the rail; at its end the page takes over.
- **`touch-action: pan-y` on the rail blocked horizontal swiping on phones**
  entirely — removed.
- **Native image drag could hijack the pointer mid-drag** — suppressed via
  `dragstart` on the rails.
- Verified with real input: wheel over the rail moves it sideways, drag
  continues from there, page scroll resumes once the rail is at its end.
  Smoke 14/14.

## 4.2.0 — 2026-08-23 · Series live in the post; the archive always opens the gallery

Frontend-only (theme stays 4.1.1 — the Series taxonomy in WordPress is
untouched and still feeds the data).

- **Series pages are gone; series membership shows inside the project.** The
  credits list a plain `SERIES — <name>` line, and a new **FROM THE SERIES**
  section lists the other frames of the same series as index rows (hover-peek
  preview included), each linking straight to its project page. Sample data:
  the two VIECC shoots now share the VIECC series so the block is visible.
- **The Work archive always opens the gallery.** Clicking a card's image used
  to open a single-image lightbox while only the caption led to the project —
  now the whole card navigates to the project page. The lightbox remains on
  the gallery images inside a project, where it belongs.
- Smoke suite now covers 14 checks (7 routes × 2 viewports — `/series/` is no
  longer a route); archive image click verified to navigate, in-post lightbox
  verified to still open.

## 4.1.1 — 2026-08-22 · Backend hardening (theme 4.1.1)

WordPress theme pass — same perfection bar as the frontend. Frontend version
bumped in sync, no frontend behaviour change.

- **Enquiry endpoint rate-limited**: max 5 submissions per IP per hour
  (transient-based, no plugin). Each submission stores a post and sends two
  emails, and Turnstile is optional — without a cap the endpoint was a mail-
  bombing / backscatter vector. Over the limit → HTTP 429 with a polite note.
- **Input length caps** on every enquiry field (name 200, message 5000, …) —
  multibyte-safe, so oversized payloads can't bloat the database or emails.
- **Dashboard redirect guarded**: only users who can open Site Control are
  redirected to it; subscribers keep the stock dashboard instead of hitting a
  permissions error.
- **Rewrite-flush timing fixed**: `after_switch_theme` fires before `init`, so
  the activation flush ran before the Work CPT existed and `/work` stayed dead
  until Permalinks were saved manually. Now flagged and flushed after `init`.
- **CORS hygiene**: `Vary: Origin` on every REST response (never only on
  allowed ones — shared caches must not replay origin-specific responses),
  plus `Access-Control-Max-Age: 600` for cheaper preflights.
- Verified: `php -l` clean; rate limiter and clipping stub-tested (5-then-429
  per IP, other IPs unaffected, multibyte clip exact).

## 4.1.0 — 2026-08-22 · The dev-limit layer — site-wide

Frontend-only (theme stays 4.0.1). Four techniques most portfolio sites don't
attempt, each feature-gated with a full CSS fallback and off under
`prefers-reduced-motion`:

- **Raw WebGL2 hero displacement morph** (no three.js): every slide change on
  the home hero runs a fractal-noise tear between the outgoing and incoming
  frame — per-slide focal points, the site's own grade baked into the shader,
  quintic easing. On any failure the CSS crossfade still runs. Hero slides now
  load eagerly (lazy images inside fully clipped slides never load — that
  starved both the morph textures and the fallback).
- **Scroll-driven CSS animations** (`animation-timeline: view()`, zero JS) on
  every page: the home statement wall and the About statement + stats wipe in
  line by line, ghost numerals and process numbers drift against the scroll,
  the Work archive gets an alternating column parallax, and the calculator's
  step labels wipe in as the instrument scrolls.
- **Scramble decode**: mono labels (EXIF lines, eyebrows, section hints, the
  calculator steps) decode left-to-right through a technical charset on hover,
  then restore exactly.
- **Instrument boot preloader**: once per session on whichever page is entered
  first — masked wordmark reveal, a typed boot readout (`RVN / OS — BOOT`),
  and the accent rail. Timer-armed (not rAF) so throttled tabs still resolve;
  sits above every other overlay.
- **GL hover distortion covers the About portrait** too, with new
  object-fit-cover mapping in the shader so overscanned crops render the same
  framing as the real image.

## 4.0.3 — 2026-08-22 · TFP has no extras; no events or weddings

- **TFP locks the add-ons step**: choosing TFP clears any picked add-ons and
  disables the list with a mono note ("not available on TFP collaborations");
  switching back re-enables it. Balance stays 0 €.
- **Events and weddings leave the offer**: sessions are now Portrait and
  Fashion / Editorial, and every positioning line (hero, marquee, focus, bio,
  work intro, sample honors) reads portrait · fashion · editorial. Existing
  archive projects are untouched.

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
