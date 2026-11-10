# WP-THEME

All web properties in one repository — **one folder per domain**.

| Domain | Contents | Status |
|---|---|---|
| [`raveenthiran.com/`](raveenthiran.com/) | Photography portfolio, **headless**: `frontend/` (Astro static site) + `raveenthiran-headless/` (WordPress backend theme, installable zip at the folder root). Legacy themes (Still, still-rework, Latent, R-E, Catalogue-Noir child) kept for reference. | **Live** |
| [`viennaphotogroup.com/`](viennaphotogroup.com/) | `vpg-v2-coop`, `vpg-v3-gallery` — classic WordPress themes for the Vienna Photo Group. | Active |
| [`m1o.at/`](m1o.at/) | `m1o-hub` (current) and `m1o-v2/` (the v2 evolution — licensed hero font is a per-install drop-in, see `assets/fonts/AVOISTE-README.md`). | Active |
| [`jrpoetry.com/`](jrpoetry.com/) | `kavithai-modernized` — poetry site theme. | Active |

`docs/` holds cross-project notes (NAS/CORS setup, legacy audits, previews).

## raveenthiran.com — how it ships

WordPress on a private NAS is a pure CMS behind a normalized REST
contract; the Astro frontend builds from it and deploys to easyname over FTPS.

- **Deploy**: pushing changes under `raveenthiran.com/frontend/` to
  `Theme-folder` runs `.github/workflows/deploy-frontend.yml` (build → OG-card
  generation → FTPS upload). A nightly rebuild picks up new WordPress content;
  the **▲ Publish** button in the WordPress admin triggers the same workflow.
- **CI**: `visual-check.yml` runs the smoke suite (all routes, two viewports,
  fails on any JS error) with screenshots on every PR touching the frontend.

### Frontend dev

```bash
cd raveenthiran.com/frontend
npm install
npm run dev        # live against the WordPress backend (sample fallback offline)
npm run build      # static output in dist/
node scripts/smoke.mjs      # smoke suite against the built dist/
node scripts/og.mjs         # branded OG share cards into dist/og/
node scripts/wp-import.mjs <folder> --title "…"   # folder → draft Work post
```

The backend theme ships as `raveenthiran.com/raveenthiran-headless.zip`
(WordPress → Appearance → Themes → Add New). Its changelog lives in
`raveenthiran.com/frontend/CHANGELOG.md`.
