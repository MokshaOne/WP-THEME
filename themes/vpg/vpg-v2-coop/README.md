# VPG · v2

**Vienna Photo Group · co-production of Raveenthiran × on1.agency**

A from-scratch editorial-magazine WordPress theme. Pure PHP, CSS and vanilla
JavaScript. **No Elementor, no page builder, no premium-plugin dependency.**

---

## What's inside

```
vpg-v2-coop/
  style.css                       WP theme header
  functions.php                   Bootstrap · loads inc/* + Composer
  composer.json                   Composer manifest · pulls in mPDF

  header.php · footer.php         Site chrome
  index.php · page.php            Default WP templates
  single.php · search.php · 404.php

  front-page.php                  Editorial magazine homepage (9 sections)

  archive-vpg_magazine.php        Cover-wall archive of all issues
  archive-vpg_event.php           Events grid
  archive-vpg_location.php        Map + grid (Leaflet)
  archive-vpg_studio.php          Studios grid
  archive-vpg_shop.php            Shops grid
  archive-vpg_review.php          Buying guide · scores
  archive-vpg_tutorial.php        Tutorials grid

  single-vpg_magazine.php         Full editorial reading experience (cover + TOC + articles)
  single-vpg_event.php            Single event
  single-vpg_location.php         Single location · with map snippet
  single-vpg_studio.php           Single studio · with map snippet
  single-vpg_shop.php             Single shop
  single-vpg_review.php           Single review · with scores
  single-vpg_tutorial.php         Single tutorial

  templates/                      Custom page templates (15 of them)
    page-about.php                About / Editorial
    page-contact.php              Contact form
    page-join.php                 Membership signup
    page-login.php                Member login
    page-dashboard.php            Member dashboard
    page-submit.php               Member submission form
    page-imprint.php              Imprint
    page-privacy.php              Privacy
    page-terms.php                Terms
    page-membership.php           Membership tiers
    page-faq.php                  FAQ
    page-team.php                 Editorial team
    page-newsletter.php           Newsletter signup
    page-archive.php              Full archive view
    page-map-guide.php            Full-screen Leaflet map

  inc/
    theme-setup.php               Theme supports · image sizes · menus
    enqueue.php                   CSS + JS bundles · fonts · Leaflet
    helpers.php                   vpg_em · vpg_identity · vpg_roman · …
    cpts.php                      7 CPTs + taxonomies
    customizer.php                Palette + identity overrides
    security.php                  Light hardening
    magazine-editor.php           📖 admin tool · create issues + articles
    pdf-generator.php             mPDF wrapper · server-side issue PDFs

  assets/
    css/tokens.css                Design tokens
    css/base.css                  Reset · global elements
    css/layout.css                Containers · header · footer · grids
    css/components.css            Buttons · cards · chips · hero · cover · faq · stats · marquee
    css/pages/magazine.css        Magazine reading experience
    css/pages/dashboard.css       Dashboard layout
    css/pages/map.css             Leaflet container
    css/pages/forms.css           Contact · join · login · submit
    js/main.js                    Mobile nav · reveal · marquee · current-link
    js/map-engine.js              Leaflet bootstrap from data-pins attribute
    js/service-worker.js          PWA caching · carried over from v1
    vendor/leaflet/               Leaflet 1.9.4 (CSS, JS, marker icons)
    icons/                        PWA app icons

  manifest.json                   PWA manifest
```

---

## Magazine editor 📖

The headline feature. Find it under **Magazine** in the WP admin sidebar.

- **All Issues** · grid of every issue with status, cover, article count, PDF link.
- **+ New Issue** · the editor:
  - Issue metadata · title, issue number (e.g. _Vol. III · No. 09_), publication date, lede, status.
  - Cover image · media-library picker.
  - Articles repeater · drag-to-reorder, each row has title, author, body (basic HTML), image and a "page break after" checkbox.
- **Save & build PDF** · runs the PDF generator (requires `composer install`).

Articles are stored as a JSON array in `post_meta['_vpg_articles']` on the
`vpg_magazine` post. The single-magazine template reads that array and
renders each article as its own editorial block.

---

## PDF generation (mPDF)

`inc/pdf-generator.php` is a thin wrapper around [mPDF](https://mpdf.github.io/).
Install once on the server:

```bash
cd /wp-content/themes/vpg-v2-coop
composer install
```

That writes `vendor/autoload.php`. The generator wakes up and the "Build PDF"
button starts working. PDFs land in `/wp-content/uploads/vpg-pdf/` and the URL
is saved to `post_meta['_vpg_pdf_url']`.

If mPDF is **not** installed the magazine editor still works — the "Build PDF"
button falls back to a browser-print link.

---

## Map (Leaflet)

Bundled locally in `assets/vendor/leaflet/` (1.9.4). The map auto-loads on:

- `archive-vpg_location.php`
- `single-vpg_location.php` (when ACF fields `location_lat` + `location_lng` are set)
- `templates/page-map-guide.php` (full-screen variant)

The map reads pins from a `data-pins` attribute on `#vpg-map` — a JSON array
of `{ lat, lng, title, url, lede }` objects. Tiles are CartoDB Voyager (free).

---

## Customizer

**Appearance → Customize → VPG v2 · Theme**:

- **Identity** · email, location, booking status, founded year, social links.
- **Palette** · 7 color tokens (bg, surface, ink, accent, oxblood, gold, member).
- **Brand image** · logo upload.

Palette overrides are injected as inline `<style id="vpg-palette-overrides">`
in `<head>`, only writing variables that differ from the shipped defaults.

---

## Token system

All visual values flow from `assets/css/tokens.css`:

- Surface stack (`--vpg-bg`, `--vpg-surface`, `--vpg-card`, `--vpg-card-deep`)
- Ink scale (`--vpg-ink` → `--vpg-faint`)
- Accents (`--vpg-accent` burnt sienna · `--vpg-oxblood` declarative red · `--vpg-gold` highlight)
- Member identifier (`--vpg-member` forest green)
- 7 category palettes (shop, studio, location, event, review, tutorial, magazine)
- Type stack · Playfair Display · Inter · JetBrains Mono
- Display scale up to `--vpg-fs-cover` (clamp 64 → 168 px) for magazine covers
- Spacing · radius · motion tokens

Change one variable, everything follows.

---

## Page templates · how to assign

For each page-template file in `templates/`, WordPress lists it under
**Page Attributes → Template** when you edit a page in admin.

Suggested page setup:

| Slug         | Title          | Template               |
|--------------|----------------|------------------------|
| `/`          | (front page)   | _Front Page is `vpg_magazine` archive or a static page · set in Reading Settings_ |
| `/about/`    | About          | About / Editorial      |
| `/contact/`  | Contact        | Contact                |
| `/join/`     | Join VPG       | Join VPG               |
| `/login/`    | Login          | Member login           |
| `/dashboard/`| Dashboard      | Dashboard              |
| `/submit/`   | Submit         | Submit content         |
| `/imprint/`  | Imprint        | Imprint                |
| `/privacy/`  | Privacy        | Privacy                |
| `/terms/`    | Terms          | Terms                  |
| `/membership/` | Membership   | Membership tiers       |
| `/faq/`      | FAQ            | FAQ                    |
| `/team/`     | Team           | Editorial team         |
| `/newsletter/` | Newsletter   | Newsletter signup      |
| `/archive/`  | Archive        | Full archive           |
| `/map/`      | Map            | Location map (full screen) |

---

## Install

1. Upload `vpg-v2-coop.zip` via **wp-admin → Appearance → Themes → Add New → Upload Theme**.
2. Activate.
3. (Optional, for PDF generation) SSH or use a host-side terminal:
   ```bash
   cd /wp-content/themes/vpg-v2-coop
   composer install
   ```
4. Create the 7 CPTs by going to **WP admin → Tools → Permalinks** and clicking Save (flushes rewrite rules).
5. Create your pages (see assignment table above) and set the templates.
6. Visit **📖 Magazine** to start the first issue.

---

## License

GPL v2 or later. Free to use, free to fork.
