# raveenthiran — Catalogue Noir

A single-screen, no-scroll photography portfolio theme. Stoic minimal.

Version **3.0.0** · Author: Nishuthan Raveenthiran

---

## Visual language

- **Canvas:** warm near-black `#0B0C10`
- **Ink:** bone-white `#F2EFE9`
- **Accent:** sodium-amber `#F2A03D` *(configurable in Theme Settings)*
- **Display + body type:** Inter Tight (300 / 500 / 700)
- **Catalog chrome type:** JetBrains Mono (400 / 500)
- One accent only. No italic display serif. Used as active-state marker — never as decoration.

---

## Requirements

- WordPress 6.0+
- PHP 8.0+
- *Optional:* **Advanced Custom Fields** — every meta read via `nr_field()` falls back to native post meta, so ACF Pro is not required
- *Optional:* **LatePoint** booking plugin — the booking modal auto-detects the `[latepoint_book_button]` shortcode and uses a styled inline form as fallback

---

## Install

1. Drop the `raveenthiran-catalogue-noir/` folder into `wp-content/themes/`. (Or upload the `raveenthiran-catalogue-noir.zip` via **Appearance → Themes → Add New → Upload Theme**.)
2. Activate it under **Appearance → Themes**.
3. Open **Appearance → Theme Settings** and fill in studio details, social URLs, stats, and the booking URL.
4. (Optional) Create the menus:
   - **Primary** — top bar
   - **Social** — sidebar social rail
5. Create the special pages and assign their templates:

   | Title | Template |
   |---|---|
   | About | About |
   | Booking | Booking |
   | Contact | Contact |
   | FAQ | FAQ |

6. (Optional) Pin a static front page (the theme uses `front-page.php` automatically).

---

## Theme Settings (Appearance → Theme Settings)

Full editorial control without touching code. Grouped into collapsible sections:

- **§ Branding** — wordmark text + sub-label, **accent color (color picker)**, "Book a shoot" CTA label
- **§ Studio** — address, location, coordinates, email, phone, Instagram / Behance / Vimeo / LinkedIn, availability toggle + text, booking URL
- **§ Stats** — Projects / Countries / Publications / Awards (shown on About)
- **§ Showcase** — hero eyebrow text
- **§ Work** — portfolio eyebrow + title (supports `<em>` for bold emphasis)
- **§ Studio (about)** — eyebrow, title, lede, bio
- **§ Booking** — eyebrow, title, three step titles + descriptions
- **§ Hello (contact)** — eyebrow, title, intro paragraph
- **§ FAQ** — eyebrow, title (Q/A rows live in the FAQ template)
- **§ Visual effects** — toggle each on/off independently:
  - Custom cursor (amber-blob)
  - Film grain + scan-line overlay
  - Page-wipe transitions between pages
  - Ken Burns scale on active hero image
  - Big faded section anchors (§01-§06)

Every value persists as its own `wp_options` row so `nr_opt('nr_logo_text')`, `nr_opt('nr_accent')`, etc. work everywhere with no special API.

---

## Adding work

- **Projects** — `Projects → Add New`. Title, category, featured image, plus ACF fields:
  - `project_number`, `project_year`, `project_client`, `project_location`, `project_frames`, `project_gallery`, `featured_on_homepage` *(used to feature on the home slider)*
  - *Optional:* `project_format`, `project_edition` — surfaced in the hero corner meta if set
- **Services** — `service_number`, `service_price`, `service_duration`, `service_includes`
- **Journal** — `journal_cat`, `journal_read`

ACF compatibility is preserved from v2.x: all existing meta keys continue to render. The `nr_field()` helper reads ACF first, then falls back to native post meta. `inc/acf-polyfill.php` covers sites without ACF installed.

---

## Custom Post Types

- `nr_project` (taxonomy: `nr_project_cat`) — portfolio
- `nr_service` — service cards
- `nr_journal` — journal entries
- `nr_testimonial` — testimonials (data only)

---

## File map

```
raveenthiran-catalogue-noir/
├── style.css                  Theme metadata
├── functions.php              Setup, enqueue, CPTs, helpers
├── header.php                 Top bar + wordmark + cursor + sidebar + booking modal slot
├── footer.php                 Page-wipe + mobile tabs + status banner
├── index.php                  Archive fallback
├── page.php                   Generic page fallback (used by Legal pages)
├── 404.php                    Not-found
├── front-page.php             Fullscreen hero slider w/ dot-constellation nav
├── archive-nr_project.php     Portfolio horizontal rail w/ filter chips
├── single-nr_project.php      Project detail — split (meta left + plate rail right)
├── page-about.php             Template: About (Studio)
├── page-contact.php           Template: Hello (Contact)
├── page-booking.php           Template: Booking
├── page-faq.php               Template: FAQ
├── page-{impressum,agb,datenschutz}.php   German legal stubs
├── parts/
│   ├── booking-modal.php      Booking modal (LatePoint host or fallback form)
│   ├── mobile-tabs.php        Bottom tab bar
│   ├── inquiry-modal.php      Quick-inquiry modal
│   └── cookie-notice.php      GDPR cookie notice
├── inc/
│   ├── theme-settings.php     Admin → Theme Settings (full control)
│   ├── functions-additions.php
│   ├── acf-fields.php
│   ├── acf-polyfill.php
│   ├── performance.php
│   └── seo.php
└── assets/
    ├── css/theme.css          All visual styles (Catalogue Noir)
    └── js/theme.js            Cursor, slider, modals, page-wipe, FAQ accordion
```

---

## Design tokens

```css
--bg:        #0B0C10   /* near-black */
--ink:       #F2EFE9   /* bone */
--ink-2:     rgba(242,239,233,.62)
--ink-3:     rgba(242,239,233,.32)
--ink-4:     rgba(242,239,233,.12)
--amber:     #F2A03D   /* accent — settable in Theme Settings */
--hair:      rgba(242,239,233,.14)
--pad:       40px
--bar-h:     64px
--strip-h:   64px
```

---

## Behaviour

- **Hero slider** — cycles through up to 6 featured projects (`featured_on_homepage = 1`). Arrow keys + dot constellation + prev/next. Image cross-fades while title fades + slides; subtle Ken Burns on active image.
- **Page transitions** — internal navigation triggers a 420ms amber-wipe overlay before navigating.
- **Custom cursor** — small bone circle on idle; grows to amber pill with "view" / "go" / "send" label over targets. Auto-disabled on touch / coarse pointer.
- **Booking modal** — opens via any link with `data-modal="nr-booking"`. Embeds your booking URL in an iframe if set; otherwise renders LatePoint shortcode if active; otherwise renders the inline brief form.
- **Mobile (≤900px)** — top nav collapses to a hamburger that opens a left slide-in. Bottom tab bar (Home / Work / Book / Hello) shows persistently.

---

## Migration from v2.x (Deep Noir)

Existing content carries over without changes:

- All CPTs use the same slugs and taxonomies
- All ACF field keys are preserved
- All `wp_options` prefixed `nr_*` (studio name, location, social URLs, stats) are honored

If you upgraded and the new Theme Settings page is empty, your prior values are still in the database — the new page just surfaces them. Click Save once to apply the new defaults to any field you left blank.

---

— *Built around the single-screen design exploration of the [raveenthiran.com](https://raveenthiran.com) portfolio theme.*
