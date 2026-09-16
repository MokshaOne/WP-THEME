# Still — minimal cinematic photographer theme

Pure black, thin white type, small tasteful 3D. The homepage boots like a
terminal, the name decodes in, it slides into a dock-navigated home of **teaser
panels** — and every panel links out to a real, full page. Classic PHP, no page
builder, **no ACF**. Built for unlimited galleries and journal entries.

## The model
```
Home (front-page.php)  ── intro plays once per load ──
  teaser panels → link out to the real pages (never replace them):
    Work     → /work       archive-work.php   (masonry, paginated, unlimited)
    Studio   → /about       page.php
    Journal  → /journal     home.php           (posts index, paginated)
    Contact  → /contact     page.php
    Enquire  → /enquire     page.php
  single project → single-work.php  (featured image + gallery from the editor)
  single entry   → single.php
```

## First-run setup
1. **Appearance → Themes → activate Still.** (Rewrites flush automatically; if
   `/work` 404s, save **Settings → Permalinks** once.)
2. **Settings → Reading → Your homepage displays → A static page:**
   - **Homepage:** create/select a page named *Home* (its content is ignored — the
     intro + teaser panels render automatically).
   - **Posts page:** create/select a page named *Journal*.
3. **Create pages** with these exact slugs so the dock + teasers link correctly:
   `about` (Studio), `contact`, `enquire`. Build their content in the editor.
4. **Work → Add New** projects: title, a **featured image** (the cover), a
   **Category**, and add the photo **Gallery** in the editor (native Gallery
   block). New projects appear on the Work teaser + the `/work` archive
   automatically — add as many as you like, forever.
5. Optional: **Appearance → Menus** → build a *Primary (dock)* menu to override
   the default dock items.

## Navigation
The **dock** (bottom-center) is the site nav on every page. On the home it
smooth-scrolls to each teaser panel; on inner pages it links to the real pages.

## Design notes
- Colours/type are CSS variables at the top of `assets/css/main.css`
  (`--bg`, `--fg`, `--mut`, `--gold`… fonts in `--sans` / `--mono`).
- The intro respects `prefers-reduced-motion` (fast-forwards) and degrades
  without JS (the intro simply isn't shown; content is reachable).
- The decoded name uses your **Site Title** (Settings → General).

## Rename
It's just a name — edit `Theme Name` in `style.css` and rename the folder.
