# M1O v2 — changelog

Evolution of the `moksha1one` master theme (v0.13.3 → **v2.0.0**), the "let's do magic" pass.
Base preserved: the whole `nr_*` content engine (CPTs, ACF fields, shortcodes, Theme Settings,
WebGL, PWA, SMTP, OG cards, PDF estimates) is **untouched** — v2 is additive and reversible.

## ✅ Shipped in this pass

| Area | Change | Files |
|------|--------|-------|
| **Type** | Wired **Avoiste Laverta** as `--ff-hero`; applied to the homepage hero + page titles at its natural 400 weight with relaxed tracking. Syne remains `--ff-display` for everything smaller. The `@font-face` + preload are emitted from `functions.php` **only when the font is present**, so it's a clean local drop-in (see below). | `assets/css/theme.css`, `assets/css/moksha.css`, `functions.php`, `assets/fonts/AVOISTE-README.md` |
| **Design** | **Obsidian film-grain** overlay — a static SVG-noise data-URI layer (no external asset, ~0 network cost) over the dark canvas. Token `--grain-opacity` (default `.038`). | `assets/css/moksha.css` |
| **Authoring** | **Canvas (free build)** page template — write raw HTML/CSS/JS in the editor, renders verbatim inside the site chrome (wpautop stripped). | `page-canvas.php`, `assets/css/moksha.css` |
| **Authoring** | **Blank Canvas (zero chrome)** page template — you own the whole `<body>`; a hand-coded page with a WP URL + CMS behind it. | `page-blank.php` |
| **A11y** | **Reduced-motion hardening** — belt-and-suspenders CSS over the existing JS guards (orb, scroll hint, marquees, horizontal track all neutralised under `prefers-reduced-motion`). | `assets/css/moksha.css` |
| **Identity** | Theme header → "M1O v2", version `2.0.0`, updated description/tags; `NR_THEME_VERSION` bumped for cache-busting. | `style.css`, `functions.php` |
| **Docs** | Retitled the stale "Obscura" setup doc to M1O v2 and added a "New in v2" section. | `SETUP.md` |

**The "build freely in WordPress" problem you raised is solved by the two Canvas templates** — no
new PHP file per page, no deploy; the freedom of a static HTML file, inside WordPress.

### Note on the Avoiste font (licensed asset, public repo)
The font files are **not committed** — this repo is public, and licensed Envato assets shouldn't be
redistributed through it. The wiring is present and graceful: drop `avoiste-laverta.woff2` (+ optional
`.woff`) into `assets/fonts/` on your install and the hero type activates automatically; without them
the hero falls back to Syne with no errors. See `assets/fonts/AVOISTE-README.md`.

## ⏳ Deferred (blocked in this environment, trivial to finish)
- **Self-host Leaflet** (GDPR + perf) — the agent proxy blocks `unpkg.com`, so the two library
  files couldn't be fetched here. Drop `leaflet.js` + `leaflet.css` into `assets/vendor/leaflet/`
  and repoint `inc/map.php` (currently still registers the unpkg URLs). One-line change once the
  files exist.

## 🗂️ Queued — needs your asset downloads (from the manifest shortlist)
Wiring is ready; these just need the files dropped into the theme:
- **3D hero model (GLB)** → load into `webgl-hero.js` (e.g. "Inflated Abstract Sphere" / reflective abstract shape) with the existing mobile/reduced-motion fallback.
- **Grain/texture PNG** → optional swap for the SVG-noise layer if you want photographic grain.
- **UI sounds** → hook the "UI Sci-Fi hover click" set into the existing Interface-sound toggle.

## 💡 Queued — design harvest (needs a direction from you, per source)
From your own themes (cleanest to lift) + the collected templates:
- **Tableau** — cinematic 21:9 hero (strong fit for a photographer).
- **VPG Gallery** — image-led project grid.
- Uploaded HTML templates (Drex/Selfie/Tank/JohnBlack) — specific effects: video-hero, PhotoSwipe lightbox, typed-text, custom cursor, page transitions.
Say which effect from which source and it gets rebuilt natively in this clean codebase.
