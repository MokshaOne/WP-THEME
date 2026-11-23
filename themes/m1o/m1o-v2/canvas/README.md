# Free-build canvas pages (no ACF)

Ready-to-paste HTML pages for the **Blank Canvas** / **Canvas** page templates.
These prove the point: you can build and ship new pages with **zero ACF, zero
`nr_opt`, zero loop** — just HTML.

## How to use `home-v1.html`
1. WordPress → **Pages → Add New**.
2. **Page → Attributes → Template → "Blank Canvas (zero chrome)"** (or "Canvas (free build)" if you want the site header/footer around it).
3. In the editor, add a **Custom HTML block** (or open the **Code editor**) and paste the entire contents of `home-v1.html`.
4. Publish. To set it as the site homepage: **Settings → Reading → Your homepage displays → A static page → Home**.

## Making it real
- Each grey `.plate` is a placeholder — replace it with your own `<img …>` (or a WordPress image block). Keep the `aspect-ratio` for the layout to hold.
- Edit the copy inline (headline, lede, project titles, statement, footer links).
- Fonts are inherited from the theme on the live site (Avoiste Laverta / Syne / JetBrains Mono). Drop the Avoiste files into `assets/fonts/` to get the couture headline face (see `../assets/fonts/AVOISTE-README.md`).

## Why this matters
No ACF field to define, no PHP template to deploy — the design lives in the page.
Keep ACF for structured, repeating content (projects, journal); use these canvas
pages for anything you want to art-direct freely.
