# Bauhaus

Black + yellow + uppercase + wave contour lines. A rebuild of the Growla digital-agency direction in pure PHP/CSS/JS.

A from-scratch WordPress theme. Pure PHP, CSS and JavaScript. **No Elementor, no page builder, no premium plugin dependencies.**

## Install

1. Upload $slug.zip via **wp-admin → Appearance → Themes → Add New → Upload Theme**.
2. Activate.
3. Open **wp-admin → Appearance → Customize → Bauhaus · Site content**.

## Customize

Every piece of homepage content is editable in the Customizer panel:

| Section      | Edits                                                                  |
|--------------|------------------------------------------------------------------------|
| Identity     | Email, location, booking status, founded year, sites count, reply hrs |
| Hero         | Eyebrow · headline lines · lede · CTA label + URL                     |
| Principles   | Repeater · one per line · oman | text                              |
| Services     | Repeater · one per line · 
ame | description                        |
| Work         | Repeater · one per line · year | title | description                |
| Pricing      | Repeater · one per line · 
ame | price | sub | feat;feat;… | 0/1    |
| FAQ          | Repeater · one per line · question | answer                          |
| Contact      | (uses Identity values)                                                 |
| Palette      | Background · text · accent color pickers                              |

## Work as a CPT

The Customizer "Work" repeater is the simplest path, but for real projects with images use the **Projects** CPT (admin sidebar). Each project supports title, excerpt, featured image, and two custom fields (Year + One-line description) shown in the side meta box. CPT entries automatically override the Customizer "Work" list once at least one is published.

## Subpages without reload

The homepage has six in-page tabs (HOME / ABOUT / WORK / PRICING / FAQ / CONTACT) wired via data-skin-link ↔ data-skin-page. Clicking a tab swaps which [data-skin-page] container is visible — no reload, scrolls smooth to top. localStorage remembers your last view across reloads.

## Files

\\\
bauhaus/
  style.css                  WP theme header
  functions.php              Setup · enqueues · nav menus · palette overrides
  header.php · footer.php    Standard chrome
  front-page.php             Homepage in the Bauhaus design language
  page.php · single.php · archive.php · 404.php · search.php · index.php
  inc/
    helpers.php              Content getters (Customizer-backed, CPT-aware)
    customizer.php           Adds the "Bauhaus · Site content" panel
    cpts.php                 Registers the Projects CPT + meta box
  assets/
    css/main.css             All theme styles (base + skin layout)
    js/main.js               Subpage router · smooth scroll · reveal-on-scroll
\\\

## License

GPL v2 or later. Built by on1.agency.
