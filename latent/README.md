# Latent — a block (FSE) theme for a photographer's portfolio

Obsidian canvas, gilt-gold accents, editorial serif display. **Everything is
edited visually** in the WordPress **Site Editor** (Appearance → Editor) —
templates, header, footer, colours and type. No page builder, no ACF.

## Why this theme (vs. the old approach)
The design lives in `theme.json` + block templates, not in locked PHP + ACF
fields. You can restyle the whole site, rearrange any template, and build new
pages **without touching code** — the freedom you were missing.

## Content engine (fresh + minimal)
- **Work** — a custom post type for the portfolio. Add a piece under
  **Work → Add New**: title, a **featured image** (the cover), a **Category**
  (`work_category`), and write anything else in the block editor. That's it —
  no ACF fields to fill.
- **Posts** — used for the **Journal**.
- **Pages** — About, Contact, legal, etc. Two extra page templates ship:
  *Canvas (full width)* and *Page (no title)*.

## First-run setup
1. **Appearance → Themes** → activate **Latent**. (Rewrite rules flush
   automatically; if `/work` 404s, save **Settings → Permalinks** once.)
2. **Settings → Reading → Your homepage displays → A static page** → create a
   page called *Home* and select it. The front page uses the built-in
   `front-page` template (hero → work grid → studio → CTA).
3. **Appearance → Menus** (or edit the Header in the Site Editor) → build the
   primary navigation. Until you do, the header links point at `/work`,
   `/about`, `/journal`, `/contact`.
4. **Work → Add New** a few pieces with featured images — they populate the
   homepage grid and the `/work` archive automatically.
5. Edit any copy directly in **Appearance → Editor → Patterns / Templates**.

## Design tokens (edit in Appearance → Editor → Styles)
- Colours: Obsidian `#0a0a0a`, Void `#050505`, Ink `#e7e5e0`, **Gilt `#f2ca50`**.
- Type: Display (serif) for headings, system sans for body, mono for labels.
  To use bespoke fonts, add them under **Styles → Typography** or drop
  `@font-face` files into `assets/fonts/` and register them in `theme.json`.

## Patterns included (Inserter → Latent)
`Hero`, `Featured work grid`, `Studio statement`, `Closing call to action`.

## Rename the theme
It's just a name: edit `Theme Name` in `style.css` and rename the folder.
