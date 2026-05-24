# on1.agency — WordPress theme

A custom theme for **on1.agency**. Editorial × technical: paper-warm or inverted-ink palette, serif display + mono body, numbered sections like a printed monograph.

Everything visible on the site — the wordmark, every label, every section number, the capacity slots, the brief questions — is editable in `wp-admin`. The theme ships with sensible defaults so an empty install still looks like the design.

---

## Install

1. Copy this folder to `wp-content/themes/on1-agency`.
2. Activate **on1.agency** under *Appearance → Themes*.
3. Install + activate **Advanced Custom Fields PRO** (required).
4. Optional: install + activate **WooCommerce** if you want a shop later.
5. Go to `wp-admin → Settings → Permalinks`, click **Save** once. This flushes rewrites so `/work/` resolves.

---

## Where to edit things

### `on1 Settings → Identity`
The visual system. Switching these updates the whole site instantly.

| Setting | What it does |
|---|---|
| **Paper tone** | Picks the palette preset (Inverted Ink, Warm Cream, Cool Stone, Soft Bone). |
| **Type pairing** | Picks the display × mono font pair (Instrument × JBM, Newsreader × JBM, Spectral × Space Mono, Plex Serif × Plex Mono). Google Fonts are loaded automatically. |
| **Accent stamp** | The single accent colour. Used sparingly. |
| **Body type scale** | Bumps the base font-size by ±px. |
| **Layout max-width** | px width of the content area. |
| **Brand mark** | The wordmark. Wrap one character in `<em>` to colour it with the accent. |
| **Header status** | The pulsing dot + label in the top-right. |
| **Contact email / Studio location / Reply window** | Reused everywhere. |
| **External links** | Free-form list. |

Add a new palette or type pairing by hooking `on1_palette_presets` / `on1_type_pairings` from a child theme — or just edit `inc/tokens.php`.

### `on1 Settings → Homepage`
Tab-by-tab control of every section: opening statement, contents grid, work section labels, services section labels, brief questions and copy.

Most text fields accept `<em>…</em>` for accent italic. Look for "(em allowed)" in the field help.

### `on1 Settings → Capacity`
The capacity meter. Add/remove slots and stats. Each slot has a state: **Booked**, **Current (open now)**, or **Open**.

### `on1 Settings → Footer`
Tagline, columns (each with a heading + a repeater of links), copyright line, legal links.

### `Projects` (CPT)
Each project = one full-bleed case study on the homepage (if "Featured on homepage" is on) and a long-form detail page at `/work/{slug}/`.

Fields per project, grouped into tabs:

- **Meta**: number, category line, year, live URL, featured toggle, invert toggle, reverse toggle.
- **Story**: case title, italic lede, repeatable story blocks (label + body).
- **Metrics**: repeatable label + value. Values accept `<em>`, `<span class="small">`, `<span class="arrow">`. Example value: `31 <span class="arrow">→</span> 96`.
- **Image**: featured image is preferred. If empty, the four caption corners and italic handle are used as a typographic placeholder.

The first four featured projects appear on the homepage. Adjust the count in `on1 Settings → Homepage → 03 · Work → Projects shown on homepage`.

If you don't toggle invert/reverse manually, they alternate by index automatically.

### `Services` (CPT)
Each service = one row in the practice menu. Number, italic-friendly name, description, comma-separated tags, optional URL. Order via the "Order" attribute on the post edit screen.

### `Briefs` (CPT, private)
Every submission via the brief form is saved here as a private post. Also emailed to the **Identity → Contact email**.

---

## File map

```
on1-agency/
├── style.css                 Theme header only
├── functions.php             Bootstrap + enqueue
├── inc/
│   ├── tokens.php            Palette + type presets, CSS-var generator
│   ├── cpts.php              Project / Service / on1_brief CPTs
│   ├── helpers.php           Brand, edition, image, form handler
│   └── acf-fields.php        All ACF field groups
├── template-parts/
│   ├── section-opening.php
│   ├── section-index.php
│   ├── section-work.php
│   ├── section-services.php
│   ├── section-capacity.php
│   └── section-brief.php
├── front-page.php            Composes the homepage from parts
├── single-project.php        Case study detail page
├── archive-project.php       All projects
├── page.php / index.php / 404.php / search.php
└── assets/
    ├── css/main.css          All styles. Tokens injected from PHP into <head>.
    └── js/main.js            Reveal observer + mobile menu
```

---

## How the tokens flow

Tokens are not hard-coded in CSS. They flow:

1. Editor picks **palette** + **type pairing** + **accent** in *Identity*.
2. `inc/tokens.php → on1_resolve_tokens()` reads ACF.
3. `header.php → on1_render_tokens_style()` echoes `<style id="on1-tokens">` with `--paper`, `--ink`, `--accent`, `--ff-display`, `--ff-mono` etc.
4. `assets/css/main.css` consumes those variables — no preset values inside the CSS.

Want a new palette? Add to `on1_palette_presets()` (filterable). Same for fonts: `on1_type_pairings()`.

---

## Brief form

Posts to `admin-post.php?action=on1_brief`. The handler:

1. Verifies the nonce.
2. Sanitises every field.
3. Saves a private `on1_brief` post.
4. Emails the address in *Identity → Contact email*.
5. Redirects back to `/#contact?brief=sent`.

Replace with Contact Form 7 / Fluent Forms by editing `template-parts/section-brief.php` if you prefer.

---

## Adding a section

1. Add the field group in `inc/acf-fields.php` (or to the existing Homepage group).
2. Drop a new file in `template-parts/`, e.g. `section-journal.php`.
3. Add one line to `front-page.php`:
   `get_template_part( 'template-parts/section', 'journal' );`
4. Add styles for it in `assets/css/main.css` using the existing tokens (`--paper`, `--ink`, `--accent`, etc.) — never hard-code colours.

---

## Notes

- Requires PHP 8+, WordPress 6+.
- No build step. CSS and JS are plain files.
- All copy strings are i18n-ready (`__()`, `_e()`).
- Default content is shipped so the site doesn't look broken before you fill in ACF.
- The "Inverted" toggle on a project locally swaps `--paper` ↔ `--ink` via CSS `color-mix`, so a case study panel works against any palette.

— Nishuthan
