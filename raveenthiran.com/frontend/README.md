# Raveenthiran — headless photography site

The frontend for **raveenthiran.com**: a static [Astro](https://astro.build)
site that pulls its content from a headless WordPress at build time and is
deployed to **easyname** shared hosting.

```
WordPress (private NAS)   →  Astro build (fetch REST)  →  static dist/  →  easyname
   content + admin only            your machine / CI            fast, offline-proof
```

Because the site is **static (SSG)**, WordPress is only queried while building.
The published site is plain HTML/CSS/JS — fast, and it keeps working even when
the NAS is offline. If the API is unreachable at build time, every data function
falls back to built-in sample data (`src/lib/wp.ts`), so the build never breaks.

---

## Everyday: how do I change the site?

Almost everything is edited in **WordPress**, then it appears after the next build/deploy.

| I want to change… | Where | Notes |
|---|---|---|
| Projects / albums | Work → add a *Work* post | Cover = featured image; extra photos = ACF **Gallery**; album = **work_category** |
| Which projects headline the home | Work post → **featured_home** toggle | Fills the cinematic hero slider + home grid |
| Bio, stats, clients, portrait | **Site settings → Studio** | |
| Contact email, location, Instagram | **Site settings → Contact** | Also drives the footer + enquiry replies |
| Price calculator (types, add-ons, licence, km) | **Site settings → Price calculator** | The estimate updates live on Enquire |
| FAQ | **Site settings → FAQ** | Shown under the enquiry form |
| Journal articles | Posts → add a **Post** | Native WordPress posts; appear under `/journal/` |

After editing WordPress, trigger a rebuild (a push, the nightly cron, or a manual
run of the deploy workflow) to publish the changes.

---

## Local development

```bash
cd frontend
npm install
npm run dev      # http://localhost:4321 — live content from the WordPress backend
npm run build    # → dist/  (static output)
npm run preview  # serve dist/ locally
```

Override the WordPress endpoint when building:

```bash
WP_BASE=https://<wordpress-host>/wp-json/wp/v2 npm run build
```

---

## Architecture / file map

```
src/
  layouts/Base.astro     shell: <head> + SEO/JSON-LD, wordmark header,
                         overlay menu, cinematic-hero-aware header,
                         signature footer, EN/DE switch, preloader
  lib/wp.ts              data layer — getProjects/getProject, getSite,
                         getPosts/getPost, with sample-data fallbacks
  i18n.ts                EN/DE dictionary for the interface strings
  styles/theme.css       the whole design system (Opta-derived): Playfair +
                         Montserrat + Roboto, gold accent, light/dark tokens
  scripts/ui.js          all behaviour (theme, i18n, overlay, cinematic hero,
                         work filter, lightbox+zoom, price engine, count-up,
                         magnetic, focus traps) — re-inits on view transitions
  pages/
    index.astro          home: cinematic hero + lede + staggered gallery
    work/index.astro     filterable / index-toggle gallery
    work/[slug].astro    project: contained cover + meta + gallery + next
    journal/index.astro  journal list
    journal/[slug].astro article
    about.astro          studio
    enquire.astro        price calculator + FAQ + enquiry form
    404.astro            bespoke not-found
    rss.xml.ts           journal RSS feed
public/
  .htaccess              Apache: 404, gzip, caching, security headers, HTTPS
  robots.txt, site.webmanifest, icon.svg
```

Fonts are **self-hosted** via Fontsource (no Google Fonts → GDPR-friendly).

---

## Language (DE / EN)

The interface toggles between English (default) and German via the **EN/DE**
switch in the header and menu; the choice is saved per visitor.

- UI strings live in `src/i18n.ts` (`dict.en` / `dict.de`) and are applied to
  any element carrying `data-i18n="key"`.
- To add a string: give the element `data-i18n="some.key"` and add `some.key`
  to both `en` and `de`.
- **Scope:** this translates the *interface*. WordPress-authored *content*
  (project titles, bio, FAQ, pricing labels) shows as written. For fully
  translated content, add a WP multilingual plugin (Polylang / WPML).

---

## Enquiry form

The Enquire page posts to `POST /wp-json/rvn/v1/enquiry` (registered in the
theme's `functions.php`). Each enquiry is stored as a private **Enquiries**
post in WP and emailed to the studio, with an auto-reply to the client.

- **CORS** is allowed for the `raveenthiran.com` origins.
- If the endpoint is unreachable, the form falls back to a `mailto:` link, so
  nothing is lost.
- **Reliable email needs SMTP** configured in WordPress (e.g. the *WP Mail SMTP*
  plugin). Without it enquiries are still stored, but mail may not send.

---

## Deploy

`.github/workflows/deploy-frontend.yml` builds the site (pulling live WordPress
content) and uploads `dist/` to easyname over FTPS. It runs on push to the
working branches, on a manual dispatch, and nightly (so new WordPress content
goes live daily).

Set these repository secrets (Settings → Secrets and variables → Actions):

| Secret | Purpose |
|---|---|
| `FTP_SERVER` | easyname FTP host |
| `FTP_USERNAME` / `FTP_PASSWORD` | easyname FTP login |
| `FTP_SERVER_DIR` | target dir (default `./`) |
| `WP_BASE` *(optional)* | override the WordPress REST base |

The upload step is skipped (job stays green) until `FTP_SERVER` is set.

**After a deploy, purge the Cloudflare cache** (Caching → Purge Everything) or
the edge will keep serving the previous version.

---

## Swapping in a real logo

The logo is a Playfair wordmark (`.wordmark` in `Base.astro` / `theme.css`).
To use a supplied logo, replace the wordmark markup with an inline `<svg>` (or
`<img>`) in the header + footer, and update the preloader + favicon. It is a
single class, so the change is contained.
